<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;

/**
 * @phpstan-import-type StarterSite from StarterSiteDefinitions
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 */
#[AsCommand(
    name: 'desiderio:starter:seed',
    description: 'Create or update useful Desiderio starter sites for the scenario presets.'
)]
final class SeedStarterSitesCommand extends Command
{
    private const FILE_REFERENCES_KEY = '__fileReferences';
    private const NESTED_COLLECTIONS_KEY = '__collections';
    private const STARTER_FAL_FOLDER = 'desiderio-starter';
    private const CONTENT_DELETE_SCOPE_DESIDERIO = 'desiderio';
    private const CONTENT_DELETE_SCOPE_STARTER = 'starter';
    private const REPLACEABLE_CORE_CTYPES = [
        'text',
        'textpic',
        'textmedia',
        'header',
        'bullets',
        'table',
        'uploads',
        'html',
        'menu_pages',
        'menu_subpages',
        'menu_sitemap_pages',
    ];

    /** @var array<string, array<string, true>> */
    private array $tableColumnsCache = [];

    /**
     * @var array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}>|null
     */
    private ?array $contentBlockDefinitions = null;

    /** @var array<string, list<array<string, mixed>>>|null */
    private ?array $collectionsByParentTable = null;

    /** @var array<string, File> */
    private array $starterFiles = [];

    private ?Folder $starterFalFolder = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
        private readonly StorageRepository $storageRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'Parent page uid below which starter roots are created.',
                (string)SeedStyleguidePagesCommand::DEFAULT_PARENT_PID
            )
            ->addOption(
                'preset',
                null,
                InputOption::VALUE_REQUIRED,
                'Preset starter to seed: all, corporate, dashboard, editorial, portfolio, saas. Comma-separated values are allowed.',
                'all'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Only print the planned starter sites, pages, and content element counts.'
            )
            ->addOption(
                'root-map',
                null,
                InputOption::VALUE_REQUIRED,
                'Seed selected presets into existing root pages instead of creating roots. Format: corporate:740,dashboard:741,editorial:742,portfolio:743,saas:744.',
                ''
            )
            ->addOption(
                'replace-content',
                null,
                InputOption::VALUE_REQUIRED,
                'Content cleanup scope before reseeding: desiderio deletes only Desiderio blocks; starter also removes simple Fluid Styled Content starter copy.',
                self::CONTENT_DELETE_SCOPE_DESIDERIO
            )
            ->addOption(
                'hide-unmanaged-children',
                null,
                InputOption::VALUE_NONE,
                'When root-map is used, hide existing direct child pages that are not part of the selected starter definition.'
            )
            ->addOption(
                'allow-production',
                null,
                InputOption::VALUE_NONE,
                'Run even when Application Context is Production. Required to seed against production data.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentPid = $this->getIntegerInputOption($input, 'parent');
        $dryRun = $this->getBooleanInputOption($input, 'dry-run');
        $allowProduction = $this->getBooleanInputOption($input, 'allow-production');
        $hideUnmanagedChildren = $this->getBooleanInputOption($input, 'hide-unmanaged-children');
        $selectedPresets = $this->resolveSelectedPresets($this->getStringInputOption($input, 'preset'), $io);
        $rootMap = $this->resolveRootMap($this->getStringInputOption($input, 'root-map'), $io);
        $contentDeleteScope = $this->resolveContentDeleteScope($this->getStringInputOption($input, 'replace-content'), $io);

        if ($selectedPresets === null || $rootMap === null || $contentDeleteScope === null) {
            return self::FAILURE;
        }

        $workspaceId = $this->getLiveWorkspaceId();
        if ($workspaceId !== 0) {
            $io->error(sprintf(
                'Refusing to seed inside workspace #%d. The starter seeder writes live records and bypasses workspace overlays. Switch to the live workspace before running this command.',
                $workspaceId
            ));

            return self::FAILURE;
        }

        if (!$allowProduction && Environment::getContext()->isProduction()) {
            $io->error('Refusing to run in Production application context. Pass --allow-production to override (and only do so on a sandbox).');

            return self::FAILURE;
        }

        if ($dryRun) {
            $io->title('Desiderio starter site seed dry run');
            $io->listing(array_map(
                static fn (array $starter): string => sprintf(
                    '%s: 1 homepage, %d subpages, %d content elements',
                    $starter['label'],
                    count($starter['subpages']),
                    self::countStarterContentElements($starter)
                ),
                $selectedPresets
            ));
            $io->success(sprintf(
                'Would create or update %d starter site%s below page uid %d.',
                count($selectedPresets),
                count($selectedPresets) === 1 ? '' : 's',
                $parentPid
            ));

            return self::SUCCESS;
        }

        $pageColumns = $this->getColumnNames('pages');
        $contentColumns = $this->getColumnNames('tt_content');
        $createdPages = 0;
        $updatedPages = 0;
        $hiddenUnmanagedChildren = 0;
        $seededContentElements = 0;
        $now = time();

        $starterIndex = 0;
        foreach ($selectedPresets as $presetSlug => $starter) {
            $starterIndex++;
            $mappedRootUid = $rootMap[$presetSlug] ?? null;
            $rootSlug = $mappedRootUid === null ? $starter['rootSlug'] : '/';

            if ($mappedRootUid !== null) {
                $rootUid = $mappedRootUid;
                $this->updateStarterPage(
                    $rootUid,
                    $starter['rootTitle'],
                    $starter['rootNavTitle'],
                    $rootSlug,
                    $starter['home']['layout'],
                    $starter['abstract'],
                    false,
                    $starterIndex * 1024,
                    $now,
                    $pageColumns
                );
                $updatedPages++;
            } else {
                $rootUid = $this->findOrCreateStarterPage(
                    $parentPid,
                    $starter['rootTitle'],
                    $starter['rootNavTitle'],
                    $rootSlug,
                    $starter['home']['layout'],
                    $starter['abstract'],
                    false,
                    $starterIndex * 1024,
                    $now,
                    $pageColumns,
                    $createdPages,
                    $updatedPages
                );
            }

            $seededContentElements += $this->seedPageContent(
                $rootUid,
                $starter['home']['content'],
                $now,
                $contentColumns,
                $contentDeleteScope
            );

            $managedChildPageUids = [];
            foreach (array_values($starter['subpages']) as $pageIndex => $page) {
                $childSlug = $this->buildChildPageSlug($rootSlug, $page['slug']);
                $pageUid = $this->findOrCreateStarterPage(
                    $rootUid,
                    $page['title'],
                    $page['navTitle'],
                    $childSlug,
                    $page['layout'],
                    $page['abstract'],
                    (bool)($page['navHidden'] ?? false),
                    ($pageIndex + 1) * 256,
                    $now,
                    $pageColumns,
                    $createdPages,
                    $updatedPages
                );
                $managedChildPageUids[] = $pageUid;

                $seededContentElements += $this->seedPageContent(
                    $pageUid,
                    $page['content'],
                    $now,
                    $contentColumns,
                    $contentDeleteScope
                );
            }

            if ($mappedRootUid !== null && $hideUnmanagedChildren) {
                $hiddenUnmanagedChildren += $this->hideUnmanagedChildPages($rootUid, $managedChildPageUids, $now);
            }
        }

        $io->success(sprintf(
            'Created %d and updated %d starter pages below page uid %d. Inserted %d Desiderio content elements for %d starter site%s. Hid %d unmanaged child page%s.',
            $createdPages,
            $updatedPages,
            $parentPid,
            $seededContentElements,
            count($selectedPresets),
            count($selectedPresets) === 1 ? '' : 's',
            $hiddenUnmanagedChildren,
            $hiddenUnmanagedChildren === 1 ? '' : 's'
        ));

        return self::SUCCESS;
    }

    private function getIntegerInputOption(InputInterface $input, string $name): int
    {
        $value = $input->getOption($name);
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    private function getStringInputOption(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);
        return is_scalar($value) ? (string)$value : '';
    }

    private function getBooleanInputOption(InputInterface $input, string $name): bool
    {
        $value = $input->getOption($name);
        return is_bool($value) ? $value : $value === '1';
    }

    private function getLiveWorkspaceId(): int
    {
        $workspaceId = $this->context->getPropertyFromAspect('workspace', 'id', 0);
        if (is_int($workspaceId)) {
            return $workspaceId;
        }
        if (is_string($workspaceId) && is_numeric($workspaceId)) {
            return (int)$workspaceId;
        }

        return 0;
    }

    private function normalizeLastInsertId(int|string|false $lastInsertId): int
    {
        return is_numeric($lastInsertId) ? (int)$lastInsertId : 0;
    }

    /**
     * @param list<mixed> $values
     * @return list<int>
     */
    private function normalizeIntegerList(array $values): array
    {
        $integers = [];
        foreach ($values as $value) {
            if (is_numeric($value)) {
                $integers[] = (int)$value;
            }
        }

        return $integers;
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function getCollectionTable(array $collection): string
    {
        $table = $collection['table'] ?? '';
        return is_string($table) ? $table : '';
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function getCollectionColumn(array $collection, string $fallback): string
    {
        $column = $collection['column'] ?? $fallback;
        return is_string($column) ? $column : $fallback;
    }

    /**
     * @return array<string, StarterSite>|null
     */
    private function resolveSelectedPresets(string $presetOption, SymfonyStyle $io): ?array
    {
        $available = StarterSiteDefinitions::all();
        $requested = array_values(array_filter(array_map(
            static fn (string $value): string => strtolower(trim($value)),
            explode(',', $presetOption)
        ), static fn (string $value): bool => $value !== ''));

        if ($requested === [] || $requested === ['all']) {
            return $available;
        }

        $selected = [];
        foreach ($requested as $preset) {
            if (!isset($available[$preset])) {
                $io->error(sprintf(
                    'Unknown starter preset "%s". Available presets: all, %s.',
                    $preset,
                    implode(', ', array_keys($available))
                ));

                return null;
            }
            $selected[$preset] = $available[$preset];
        }

        return $selected;
    }

    /**
     * @return array<string, int>|null
     */
    private function resolveRootMap(string $rootMapOption, SymfonyStyle $io): ?array
    {
        $rootMapOption = trim($rootMapOption);
        if ($rootMapOption === '') {
            return [];
        }

        $availablePresets = array_fill_keys(StarterSiteDefinitions::slugs(), true);
        $rootMap = [];
        foreach (explode(',', $rootMapOption) as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }
            $parts = array_map('trim', explode(':', $pair, 2));
            if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
                $io->error(sprintf('Invalid root-map entry "%s". Expected format is preset:uid.', $pair));

                return null;
            }

            $preset = strtolower($parts[0]);
            if (!isset($availablePresets[$preset])) {
                $io->error(sprintf(
                    'Unknown root-map preset "%s". Available presets: %s.',
                    $preset,
                    implode(', ', array_keys($availablePresets))
                ));

                return null;
            }
            if (!is_numeric($parts[1]) || (int)$parts[1] <= 0) {
                $io->error(sprintf('Invalid root page uid "%s" for preset "%s".', $parts[1], $preset));

                return null;
            }

            $rootMap[$preset] = (int)$parts[1];
        }

        return $rootMap;
    }

    private function resolveContentDeleteScope(string $scope, SymfonyStyle $io): ?string
    {
        $scope = strtolower(trim($scope));
        if ($scope === '') {
            return self::CONTENT_DELETE_SCOPE_DESIDERIO;
        }
        if (in_array($scope, [self::CONTENT_DELETE_SCOPE_DESIDERIO, self::CONTENT_DELETE_SCOPE_STARTER], true)) {
            return $scope;
        }

        $io->error(sprintf(
            'Invalid replace-content scope "%s". Expected "%s" or "%s".',
            $scope,
            self::CONTENT_DELETE_SCOPE_DESIDERIO,
            self::CONTENT_DELETE_SCOPE_STARTER
        ));

        return null;
    }

    private function buildChildPageSlug(string $rootSlug, string $pageSlug): string
    {
        if ($rootSlug === '/' || $rootSlug === '') {
            return '/' . ltrim($pageSlug, '/');
        }

        return rtrim($rootSlug, '/') . '/' . ltrim($pageSlug, '/');
    }

    /**
     * @param StarterSite $starter
     */
    private static function countStarterContentElements(array $starter): int
    {
        $count = count($starter['home']['content']);
        foreach ($starter['subpages'] as $page) {
            $count += count($page['content']);
        }

        return $count;
    }

    /**
     * @param array<string, true> $columns
     */
    private function findOrCreateStarterPage(
        int $parentPid,
        string $title,
        string $navTitle,
        string $slug,
        string $backendLayout,
        string $abstract,
        bool $navHidden,
        int $sorting,
        int $now,
        array $columns,
        int &$createdPages,
        int &$updatedPages,
    ): int {
        $existingPageUid = $this->findExistingStarterPageUid($parentPid, $title, $slug, $columns);
        if ($existingPageUid !== null) {
            $this->updateStarterPage($existingPageUid, $title, $navTitle, $slug, $backendLayout, $abstract, $navHidden, $sorting, $now, $columns);
            $updatedPages++;

            return $existingPageUid;
        }

        $createdPages++;
        return $this->createStarterPage($parentPid, $title, $navTitle, $slug, $backendLayout, $abstract, $navHidden, $sorting, $now, $columns);
    }

    /**
     * @param array<string, true> $columns
     */
    private function findExistingStarterPageUid(int $parentPid, string $title, string $slug, array $columns): ?int
    {
        $where = [
            'pid = :parentPid',
            'deleted = 0',
            '(title = :title OR slug = :slug)',
        ];
        $parameters = [
            'parentPid' => $parentPid,
            'title' => $title,
            'slug' => $slug,
        ];
        $types = [
            'parentPid' => ParameterType::INTEGER,
            'title' => ParameterType::STRING,
            'slug' => ParameterType::STRING,
        ];

        if (isset($columns['sys_language_uid'])) {
            $where[] = 'sys_language_uid = :languageUid';
            $parameters['languageUid'] = 0;
            $types['languageUid'] = ParameterType::INTEGER;
        }
        if (isset($columns['t3ver_wsid'])) {
            $where[] = 't3ver_wsid = :workspaceId';
            $parameters['workspaceId'] = 0;
            $types['workspaceId'] = ParameterType::INTEGER;
        }
        if (isset($columns['t3ver_oid'])) {
            $where[] = 't3ver_oid = :workspaceOriginalUid';
            $parameters['workspaceOriginalUid'] = 0;
            $types['workspaceOriginalUid'] = ParameterType::INTEGER;
        }

        $existingUid = $this->connectionPool
            ->getConnectionForTable('pages')
            ->executeQuery(
                'SELECT uid FROM pages WHERE ' . implode(' AND ', $where) . ' ORDER BY hidden ASC, uid DESC LIMIT 1',
                $parameters,
                $types
            )
            ->fetchOne();

        return is_numeric($existingUid) ? (int)$existingUid : null;
    }

    /**
     * @param array<string, true> $columns
     */
    private function updateStarterPage(
        int $pageUid,
        string $title,
        string $navTitle,
        string $slug,
        string $backendLayout,
        string $abstract,
        bool $navHidden,
        int $sorting,
        int $now,
        array $columns,
    ): void {
        $this->connectionPool->getConnectionForTable('pages')->update(
            'pages',
            $this->filterRow([
                'title' => $title,
                'nav_title' => $navTitle,
                'slug' => $slug,
                'abstract' => $abstract,
                'description' => $abstract,
                'backend_layout' => $this->buildBackendLayoutIdentifier($backendLayout),
                'backend_layout_next_level' => $this->buildBackendLayoutIdentifier('DesiderioContentpage'),
                'nav_hide' => $navHidden ? 1 : 0,
                'hidden' => 0,
                'sorting' => $sorting,
                'tstamp' => $now,
            ], $columns),
            ['uid' => $pageUid]
        );
    }

    /**
     * @param array<string, true> $columns
     */
    private function createStarterPage(
        int $parentPid,
        string $title,
        string $navTitle,
        string $slug,
        string $backendLayout,
        string $abstract,
        bool $navHidden,
        int $sorting,
        int $now,
        array $columns,
    ): int {
        $connection = $this->connectionPool->getConnectionForTable('pages');
        $connection->insert('pages', $this->filterRow([
            'pid' => $parentPid,
            'title' => $title,
            'nav_title' => $navTitle,
            'doktype' => 1,
            'slug' => $slug,
            'abstract' => $abstract,
            'description' => $abstract,
            'backend_layout' => $this->buildBackendLayoutIdentifier($backendLayout),
            'backend_layout_next_level' => $this->buildBackendLayoutIdentifier('DesiderioContentpage'),
            'nav_hide' => $navHidden ? 1 : 0,
            'hidden' => 0,
            'sorting' => $sorting,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns));

        return $this->normalizeLastInsertId($connection->lastInsertId());
    }

    private function buildBackendLayoutIdentifier(string $backendLayout): string
    {
        if ($backendLayout === '' || str_starts_with($backendLayout, 'pagets__')) {
            return $backendLayout;
        }

        return 'pagets__' . $backendLayout;
    }

    /**
     * @param array<int, StarterBlock> $contentBlocks
     * @param array<string, true> $contentColumns
     */
    private function seedPageContent(int $pageUid, array $contentBlocks, int $now, array $contentColumns, string $contentDeleteScope): int
    {
        $this->markExistingStarterContentAsDeleted($pageUid, $now, $contentDeleteScope);
        $created = 0;

        foreach (array_values($contentBlocks) as $index => $block) {
            $contentData = $this->buildContentInsert($pageUid, $block, ($index + 1) * 256, $now, $contentColumns);
            $connection = $this->connectionPool->getConnectionForTable('tt_content');
            $connection->insert('tt_content', $contentData['row']);
            $contentUid = $this->normalizeLastInsertId($connection->lastInsertId());
            $this->seedFileReferences('tt_content', $contentUid, $pageUid, $now, $contentData['fileReferences']);
            $this->seedCollectionRecords($contentUid, $pageUid, $now, $contentData['collections']);
            $created++;
        }

        return $created;
    }

    private function markExistingStarterContentAsDeleted(int $pageUid, int $now, string $contentDeleteScope): void
    {
        $existingContentUids = $this->findExistingStarterContentUids($pageUid, $contentDeleteScope);
        if ($existingContentUids !== []) {
            $this->deleteFileReferencesForRecords('tt_content', $existingContentUids);
            $this->deleteCollectionRowsForParentUids($existingContentUids, 'tt_content');
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                ...$this->buildStarterContentDeletionConstraints($queryBuilder, $contentDeleteScope),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, 'tt_content')
            )
            ->executeStatement();
    }

    /**
     * @return list<int>
     */
    private function findExistingStarterContentUids(int $pageUid, string $contentDeleteScope): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        $uids = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                ...$this->buildStarterContentDeletionConstraints($queryBuilder, $contentDeleteScope),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, 'tt_content')
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->normalizeIntegerList($uids);
    }

    /**
     * @return list<string>
     */
    private function buildStarterContentDeletionConstraints(QueryBuilder $queryBuilder, string $contentDeleteScope): array
    {
        $desiderioConstraint = $queryBuilder->expr()->like(
            'CType',
            $queryBuilder->createNamedParameter('desiderio_%')
        );
        if ($contentDeleteScope !== self::CONTENT_DELETE_SCOPE_STARTER) {
            return [$desiderioConstraint];
        }

        return [
            $queryBuilder->expr()->or(
                $desiderioConstraint,
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter(self::REPLACEABLE_CORE_CTYPES, ArrayParameterType::STRING)
                )
            ),
        ];
    }

    /**
     * @param list<int> $managedChildPageUids
     */
    private function hideUnmanagedChildPages(int $rootUid, array $managedChildPageUids, int $now): int
    {
        if ($managedChildPageUids === []) {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder
            ->update('pages')
            ->set('hidden', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->notIn(
                    'uid',
                    $queryBuilder->createNamedParameter($managedChildPageUids, ArrayParameterType::INTEGER)
                ),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, 'pages')
            );

        return $queryBuilder->executeStatement();
    }

    /**
     * @param list<int> $parentUids
     */
    private function deleteCollectionRowsForParentUids(array $parentUids, string $parentTable): void
    {
        if ($parentUids === []) {
            return;
        }

        foreach ($this->getCollectionsByParentTable()[$parentTable] ?? [] as $collection) {
            $table = $this->getCollectionTable($collection);
            if ($table === '') {
                continue;
            }
            if (!$this->tableHasColumn($table, 'foreign_table_parent_uid')) {
                continue;
            }

            $collectionUids = $this->findCollectionRowUids($table, $parentUids);
            $this->deleteCollectionRowsForParentUids($collectionUids, $table);
            $this->deleteFileReferencesForRecords($table, $collectionUids);

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $queryBuilder
                ->delete($table)
                ->where(
                    $queryBuilder->expr()->in(
                        'foreign_table_parent_uid',
                        $queryBuilder->createNamedParameter($parentUids, ArrayParameterType::INTEGER)
                    ),
                    ...$this->buildLiveWorkspaceConstraints($queryBuilder, $table)
                )
                ->executeStatement();
        }
    }

    /**
     * @param list<int> $parentUids
     * @return list<int>
     */
    private function findCollectionRowUids(string $table, array $parentUids): array
    {
        if ($parentUids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $uids = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->in(
                    'foreign_table_parent_uid',
                    $queryBuilder->createNamedParameter($parentUids, ArrayParameterType::INTEGER)
                ),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, $table)
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->normalizeIntegerList($uids);
    }

    /**
     * @param list<int> $recordUids
     */
    private function deleteFileReferencesForRecords(string $table, array $recordUids): void
    {
        if ($recordUids === [] || !$this->tableHasColumn('sys_file_reference', 'uid_foreign')) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->delete('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($table)),
                $queryBuilder->expr()->in(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($recordUids, ArrayParameterType::INTEGER)
                ),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, 'sys_file_reference')
            )
            ->executeStatement();
    }

    /**
     * @return list<string>
     */
    private function buildLiveWorkspaceConstraints(QueryBuilder $queryBuilder, string $table): array
    {
        $constraints = [];
        if ($this->tableHasColumn($table, 't3ver_wsid')) {
            $constraints[] = $queryBuilder->expr()->eq(
                't3ver_wsid',
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            );
        }
        if ($this->tableHasColumn($table, 't3ver_oid')) {
            $constraints[] = $queryBuilder->expr()->eq(
                't3ver_oid',
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            );
        }

        return $constraints;
    }

    /**
     * @param StarterBlock $block
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function buildContentInsert(int $pid, array $block, int $sorting, int $now, array $columns): array
    {
        $ctype = $block['ctype'];
        $fixture = $this->normalizeStringKeyedArray($block['fields']);
        $row = [
            'pid' => $pid,
            'CType' => $ctype,
            'colPos' => $block['colPos'],
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ];

        [$fields, $collections, $fileReferences] = $this->resolveFixtureFields($ctype, $fixture);

        foreach ($fields as $field => $value) {
            $row[$field] = $value;
        }
        foreach ($fileReferences as $field => $references) {
            $row[$field] = count($references);
        }
        foreach ($collections as $field => $collection) {
            $row[$collection['column'] ?? $field] = count($collection['items']);
        }

        return [
            'row' => $this->filterRow($row, $columns),
            'collections' => $collections,
            'fileReferences' => $fileReferences,
        ];
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, 2: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function resolveFixtureFields(string $ctype, array $fixture): array
    {
        $definition = $this->getContentBlockDefinition($ctype);
        if ($definition === null) {
            $row = [];
            foreach ($fixture as $field => $value) {
                if (!is_array($value)) {
                    $row[$field] = $this->normalizeScalarValue($value);
                }
            }

            return [$row, [], []];
        }

        $resolvedFields = [];
        $collections = [];
        $fileReferences = [];

        foreach ($fixture as $field => $value) {
            $collection = $definition['collections'][$field] ?? null;
            if (is_array($collection) && is_array($value)) {
                $items = $this->normalizeCollectionItems($value, $collection);
                if ($items !== []) {
                    $collections[$field] = [
                        'table' => $this->getCollectionTable($collection),
                        'column' => $this->getCollectionColumn($collection, $field),
                        'items' => $items,
                    ];
                }
                continue;
            }

            $fieldConfig = $definition['fields'][$field] ?? null;
            if (!is_array($fieldConfig)) {
                continue;
            }

            if ($this->isFileField($fieldConfig)) {
                $references = $this->buildFileReferenceFixturesFromFixtureValue($value);
                if ($references !== []) {
                    $fileReferences[$field] = $references;
                }
                continue;
            }

            $resolvedFields[$field] = is_array($value)
                ? $this->normalizeArrayForScalarField($value)
                : $this->normalizeFieldValue($value, $fieldConfig);
        }

        return [$resolvedFields, $collections, $fileReferences];
    }

    /**
     * @param array<int|string, mixed> $items
     * @param array<string, mixed> $collection
     * @return list<array<string, mixed>>
     */
    private function normalizeCollectionItems(array $items, array $collection): array
    {
        $normalizedItems = [];
        foreach ($items as $item) {
            $normalizedItem = $this->normalizeCollectionItem($item, $collection);
            if ($normalizedItem !== []) {
                $normalizedItems[] = $normalizedItem;
            }
        }

        return $normalizedItems;
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>
     */
    private function normalizeCollectionItem(mixed $item, array $collection): array
    {
        if (!is_array($item)) {
            $textField = $this->findPreferredTextField($collection);
            return $textField === null ? [] : [$textField => $this->normalizeScalarValue($item)];
        }

        $item = $this->normalizeStringKeyedArray($item);
        $normalizedItem = [];
        $fileReferences = [];
        $nestedCollections = [];

        foreach ($item as $field => $value) {
            $nestedCollection = $this->getNestedCollection($collection, $field);
            if ($nestedCollection !== null && is_array($value)) {
                $items = $this->normalizeCollectionItems($value, $nestedCollection);
                if ($items !== []) {
                    $nestedCollections[$field] = [
                        'table' => $this->getCollectionTable($nestedCollection),
                        'column' => $this->getCollectionColumn($nestedCollection, $field),
                        'items' => $items,
                    ];
                    $normalizedItem[$field] = count($items);
                }
                continue;
            }

            $fieldConfig = $this->getCollectionFieldConfig($collection, $field);
            if ($fieldConfig === null) {
                continue;
            }

            if ($this->isFileField($fieldConfig)) {
                $references = $this->buildFileReferenceFixturesFromFixtureValue($value);
                if ($references !== []) {
                    $fileReferences[$field] = $references;
                    $normalizedItem[$field] = count($references);
                }
                continue;
            }

            $normalizedItem[$field] = is_array($value)
                ? $this->normalizeArrayForScalarField($value)
                : $this->normalizeFieldValue($value, $fieldConfig);
        }

        if ($fileReferences !== []) {
            $normalizedItem[self::FILE_REFERENCES_KEY] = $fileReferences;
        }
        if ($nestedCollections !== []) {
            $normalizedItem[self::NESTED_COLLECTIONS_KEY] = $nestedCollections;
        }

        return $normalizedItem;
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function findPreferredTextField(array $collection): ?string
    {
        foreach (['title', 'label', 'name', 'text', 'value', 'question', 'answer', 'description'] as $field) {
            if ($this->getCollectionFieldConfig($collection, $field) !== null) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>|null
     */
    private function getCollectionFieldConfig(array $collection, string $field): ?array
    {
        $fields = $collection['fields'] ?? null;
        if (!is_array($fields)) {
            return null;
        }

        $fieldConfig = $fields[$field] ?? null;
        return is_array($fieldConfig) ? $this->normalizeStringKeyedArray($fieldConfig) : null;
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>|null
     */
    private function getNestedCollection(array $collection, string $field): ?array
    {
        $collections = $collection['collections'] ?? null;
        if (!is_array($collections)) {
            return null;
        }

        $nestedCollection = $collections[$field] ?? null;
        return is_array($nestedCollection) ? $this->normalizeStringKeyedArray($nestedCollection) : null;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function normalizeFieldValue(mixed $value, array $fieldConfig): int|string
    {
        $type = is_string($fieldConfig['type'] ?? null) ? $fieldConfig['type'] : '';
        if ($type === 'Checkbox') {
            return $this->normalizeBooleanValue($value) ? 1 : 0;
        }
        if (in_array($type, ['Date', 'DateTime'], true)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->getTimestamp();
            }
            if (is_scalar($value)) {
                $timestamp = strtotime((string)$value);
                return $timestamp === false ? (string)$value : $timestamp;
            }
        }

        return $this->normalizeScalarValue($value);
    }

    private function normalizeBooleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value !== 0;
        }
        if (is_string($value)) {
            return !in_array(strtolower(trim($value)), ['', '0', 'false', 'no'], true);
        }

        return false;
    }

    /**
     * @param array<int|string, mixed> $value
     */
    private function normalizeArrayForScalarField(array $value): string
    {
        if ($value === []) {
            return '';
        }

        $scalars = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $scalars[] = trim((string)$item);
            }
        }

        return implode("\n", array_filter($scalars, static fn (string $item): bool => $item !== ''));
    }

    private function normalizeScalarValue(mixed $value): int|string
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (string)$value;
        }
        if ($value === null) {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }

        return '';
    }

    private function stringFromMixed(mixed $value): string
    {
        return is_scalar($value) ? trim((string)$value) : '';
    }

    /**
     * @param array<string, array{table: string, column?: string, items: list<array<string, mixed>>}> $collections
     */
    private function seedCollectionRecords(int $contentUid, int $pageUid, int $now, array $collections): void
    {
        foreach ($collections as $collection) {
            $table = $collection['table'];
            $columns = $this->getColumnNames($table);
            $connection = $this->connectionPool->getConnectionForTable($table);

            foreach ($collection['items'] as $index => $item) {
                $fileReferences = $this->normalizeFileReferencePayloads($item[self::FILE_REFERENCES_KEY] ?? []);
                unset($item[self::FILE_REFERENCES_KEY]);
                $nestedCollections = is_array($item[self::NESTED_COLLECTIONS_KEY] ?? null)
                    ? $this->normalizeCollectionPayloads($item[self::NESTED_COLLECTIONS_KEY])
                    : [];
                unset($item[self::NESTED_COLLECTIONS_KEY]);

                $row = $this->filterRow([
                    'pid' => $pageUid,
                    'sorting' => $index + 1,
                    'hidden' => 0,
                    'sys_language_uid' => 0,
                    'crdate' => $now,
                    'tstamp' => $now,
                    'foreign_table_parent_uid' => $contentUid,
                ] + $item, $columns);

                if (!$this->hasPayloadBeyondSystemFields($row)) {
                    continue;
                }

                $connection->insert($table, $row);
                $collectionRowUid = $this->normalizeLastInsertId($connection->lastInsertId());
                $this->seedFileReferences($table, $collectionRowUid, $pageUid, $now, $fileReferences);
                $this->seedCollectionRecords($collectionRowUid, $pageUid, $now, $nestedCollections);
            }
        }
    }

    /**
     * @param array<mixed, mixed> $collections
     * @return array<string, array{table: string, column?: string, items: list<array<string, mixed>>}>
     */
    private function normalizeCollectionPayloads(array $collections): array
    {
        $normalizedCollections = [];
        foreach ($collections as $field => $collection) {
            if (!is_string($field) || !is_array($collection)) {
                continue;
            }
            $table = $collection['table'] ?? null;
            $items = $collection['items'] ?? null;
            if (!is_string($table) || !is_array($items)) {
                continue;
            }
            $normalizedItems = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    $normalizedItems[] = $this->normalizeStringKeyedArray($item);
                }
            }
            $normalizedCollections[$field] = [
                'table' => $table,
                'items' => $normalizedItems,
            ];
            if (is_string($collection['column'] ?? null)) {
                $normalizedCollections[$field]['column'] = $collection['column'];
            }
        }

        return $normalizedCollections;
    }

    /**
     * @return array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>
     */
    private function normalizeFileReferencePayloads(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $normalized = [];
        foreach ($payload as $fieldName => $references) {
            if (!is_string($fieldName) || !is_array($references)) {
                continue;
            }

            foreach ($references as $reference) {
                if (!is_array($reference)) {
                    continue;
                }
                $file = $this->stringFromMixed($reference['file'] ?? '');
                if ($file === '') {
                    continue;
                }
                $normalized[$fieldName][] = [
                    'file' => $file,
                    'title' => $this->stringFromMixed($reference['title'] ?? ''),
                    'alternative' => $this->stringFromMixed($reference['alternative'] ?? ''),
                    'description' => $this->stringFromMixed($reference['description'] ?? ''),
                    'source' => $this->stringFromMixed($reference['source'] ?? ''),
                ];
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>> $fileReferences
     */
    private function seedFileReferences(string $table, int $uid, int $pid, int $now, array $fileReferences): void
    {
        if ($fileReferences === []) {
            return;
        }

        $columns = $this->getColumnNames('sys_file_reference');
        $connection = $this->connectionPool->getConnectionForTable('sys_file_reference');

        foreach ($fileReferences as $fieldName => $references) {
            foreach ($references as $index => $reference) {
                $file = $this->ensureStarterFalFile($reference);
                if ($file === null) {
                    continue;
                }

                $connection->insert('sys_file_reference', $this->filterRow([
                    'pid' => $pid,
                    'tstamp' => $now,
                    'crdate' => $now,
                    'hidden' => 0,
                    'deleted' => 0,
                    'sys_language_uid' => 0,
                    'uid_local' => $file->getUid(),
                    'uid_foreign' => $uid,
                    'tablenames' => $table,
                    'fieldname' => $fieldName,
                    'table_local' => 'sys_file',
                    'sorting' => $index + 1,
                    'sorting_foreign' => $index + 1,
                    'title' => $reference['title'],
                    'alternative' => $reference['alternative'],
                    'description' => $reference['description'],
                    'link' => $reference['source'],
                ], $columns));
            }
        }
    }

    /**
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    private function buildFileReferenceFixturesFromFixtureValue(mixed $value): array
    {
        if ($value === null || $value === '' || $value === []) {
            return [];
        }

        $items = [];
        if (is_string($value)) {
            $items[] = ['file' => $value];
        } elseif (is_array($value) && isset($value['file'])) {
            $items[] = $this->normalizeStringKeyedArray($value);
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                if (is_string($item)) {
                    $items[] = ['file' => $item];
                } elseif (is_array($item) && isset($item['file'])) {
                    $items[] = $this->normalizeStringKeyedArray($item);
                }
            }
        }

        $references = [];
        foreach ($items as $item) {
            $file = $this->stringFromMixed($item['file'] ?? '');
            if ($file === '') {
                continue;
            }
            if (str_starts_with($file, 'EXT:desiderio/')) {
                $file = substr($file, strlen('EXT:desiderio/'));
            }
            $title = $this->stringFromMixed($item['title'] ?? '');
            if ($title === '') {
                $title = $this->buildReadableFileTitle(pathinfo($file, PATHINFO_FILENAME));
            }
            $source = $this->stringFromMixed($item['source'] ?? $item['link'] ?? '');
            $references[] = [
                'file' => $file,
                'title' => $title,
                'alternative' => $this->stringFromMixed($item['alternative'] ?? $item['alt'] ?? $title),
                'description' => $this->stringFromMixed($item['description'] ?? $item['credit'] ?? $source),
                'source' => $source,
            ];
        }

        return $references;
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string} $reference
     */
    private function ensureStarterFalFile(array $reference): ?File
    {
        $relativeFilePath = $reference['file'];
        if (isset($this->starterFiles[$relativeFilePath])) {
            return $this->starterFiles[$relativeFilePath];
        }

        $sourcePath = GeneralUtility::getFileAbsFileName('EXT:desiderio/' . $relativeFilePath);
        if ($sourcePath === '' || !is_file($sourcePath)) {
            return null;
        }

        $folder = $this->getStarterFalFolder();
        $fileName = basename($relativeFilePath);
        $file = $folder->getFile($fileName);
        if (!$file instanceof File) {
            $file = $folder->addFile($sourcePath, $fileName);
        }

        $this->starterFiles[$relativeFilePath] = $file;
        $this->upsertFileMetadata($file, $reference);

        return $file;
    }

    private function getStarterFalFolder(): Folder
    {
        if ($this->starterFalFolder instanceof Folder) {
            return $this->starterFalFolder;
        }

        $storage = $this->storageRepository->getDefaultStorage();
        if ($storage === null) {
            throw new \RuntimeException('No default FAL storage is configured for Desiderio starter seeding.', 1777100241);
        }

        $rootFolder = $storage->getRootLevelFolder(false);
        $this->starterFalFolder = $rootFolder->hasFolder(self::STARTER_FAL_FOLDER)
            ? $rootFolder->getSubfolder(self::STARTER_FAL_FOLDER)
            : $rootFolder->createFolder(self::STARTER_FAL_FOLDER);

        return $this->starterFalFolder;
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string} $reference
     */
    private function upsertFileMetadata(File $file, array $reference): void
    {
        $columns = $this->getColumnNames('sys_file_metadata');
        if (!isset($columns['file'])) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_metadata');
        $where = [
            $queryBuilder->expr()->eq('file', $queryBuilder->createNamedParameter($file->getUid())),
        ];
        if (isset($columns['sys_language_uid'])) {
            $where[] = $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0));
        }

        $existingUid = $queryBuilder
            ->select('uid')
            ->from('sys_file_metadata')
            ->where(...$where)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $row = $this->filterRow([
            'file' => $file->getUid(),
            'pid' => 0,
            'tstamp' => time(),
            'crdate' => time(),
            'sys_language_uid' => 0,
            'title' => $reference['title'],
            'alternative' => $reference['alternative'],
            'description' => $reference['description'],
        ], $columns);

        $connection = $this->connectionPool->getConnectionForTable('sys_file_metadata');
        if (is_numeric($existingUid)) {
            $connection->update('sys_file_metadata', $row, ['uid' => (int)$existingUid]);
            return;
        }

        $connection->insert('sys_file_metadata', $row);
    }

    /**
     * @return array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}>
     */
    private function getContentBlockDefinitions(): array
    {
        if ($this->contentBlockDefinitions !== null) {
            return $this->contentBlockDefinitions;
        }

        $basePath = GeneralUtility::getFileAbsFileName('EXT:desiderio/ContentBlocks/ContentElements');
        if ($basePath === '' || !is_dir($basePath)) {
            $this->contentBlockDefinitions = [];
            return $this->contentBlockDefinitions;
        }

        $definitions = [];
        $directories = scandir($basePath);
        if ($directories === false) {
            $this->contentBlockDefinitions = [];
            return $this->contentBlockDefinitions;
        }

        foreach ($directories as $directory) {
            if ($directory === '.' || $directory === '..') {
                continue;
            }
            $configPath = $basePath . '/' . $directory . '/config.yaml';
            if (!is_readable($configPath)) {
                continue;
            }
            $config = Yaml::parseFile($configPath);
            if (!is_array($config)) {
                continue;
            }
            $config = $this->normalizeStringKeyedArray($config);
            $typeName = is_string($config['typeName'] ?? null)
                ? $config['typeName']
                : 'desiderio_' . str_replace('-', '', $directory);
            $definitions[$typeName] = $this->buildContentBlockDefinition($config);
        }

        $this->contentBlockDefinitions = $definitions;
        return $this->contentBlockDefinitions;
    }

    /**
     * @param array<string, mixed> $config
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}
     */
    private function buildContentBlockDefinition(array $config): array
    {
        $definition = [
            'fields' => [],
            'collections' => [],
        ];

        $fields = $config['fields'] ?? [];
        if (!is_array($fields)) {
            return $definition;
        }

        foreach ($fields as $field) {
            if (!is_array($field) || !isset($field['identifier'])) {
                continue;
            }
            $field = $this->normalizeStringKeyedArray($field);
            if (!is_string($field['identifier'] ?? null)) {
                continue;
            }

            $identifier = $field['identifier'];
            if (($field['type'] ?? '') !== 'Collection') {
                $definition['fields'][$identifier] = $field;
                continue;
            }

            $definition['collections'][$identifier] = $this->buildCollectionDefinition(
                $field,
                $identifier,
                $this->resolveRootFieldStorageIdentifier($config, $field, $identifier)
            );
        }

        return $definition;
    }

    /**
     * @param array<string, mixed> $field
     * @return array{table: string, column: string, fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>, minItems: int, maxItems: int|null}
     */
    private function buildCollectionDefinition(array $field, string $fallbackIdentifier, ?string $column = null): array
    {
        $childFields = [];
        $childCollections = [];

        $fields = $field['fields'] ?? [];
        if (!is_array($fields)) {
            $fields = [];
        }

        foreach ($fields as $childField) {
            if (!is_array($childField) || !isset($childField['identifier'])) {
                continue;
            }
            $childField = $this->normalizeStringKeyedArray($childField);
            if (!is_string($childField['identifier'] ?? null)) {
                continue;
            }
            $childIdentifier = $childField['identifier'];
            if (($childField['type'] ?? '') === 'Collection') {
                $childCollections[$childIdentifier] = $this->buildCollectionDefinition($childField, $childIdentifier);
                continue;
            }

            $childFields[$childIdentifier] = $childField;
        }

        return [
            'table' => $this->resolveCollectionTable($field, $fallbackIdentifier),
            'column' => $column ?? $fallbackIdentifier,
            'fields' => $childFields,
            'collections' => $childCollections,
            'minItems' => $this->getConfiguredInteger($field, 'minItems')
                ?? $this->getConfiguredInteger($field, 'minitems')
                ?? 1,
            'maxItems' => $this->getConfiguredInteger($field, 'maxItems')
                ?? $this->getConfiguredInteger($field, 'maxitems'),
        ];
    }

    /**
     * @param array<string, mixed> $field
     */
    private function resolveCollectionTable(array $field, string $fallbackIdentifier): string
    {
        $configuredTableValue = $field['table'] ?? $field['foreign_table'] ?? null;
        return is_string($configuredTableValue) && $configuredTableValue !== ''
            ? $configuredTableValue
            : $fallbackIdentifier;
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $field
     */
    private function resolveRootFieldStorageIdentifier(array $config, array $field, string $identifier): string
    {
        if (($field['useExistingField'] ?? false) === true) {
            return $identifier;
        }

        $prefixEnabled = array_key_exists('prefixField', $field)
            ? (bool)$field['prefixField']
            : (bool)($config['prefixFields'] ?? true);
        if (!$prefixEnabled) {
            return $identifier;
        }

        $prefix = $this->resolveContentBlockPrefix($config);
        return $prefix !== '' ? $prefix . '_' . $identifier : $identifier;
    }

    /**
     * @param array<string, mixed> $config
     */
    private function resolveContentBlockPrefix(array $config): string
    {
        $name = is_string($config['name'] ?? null) ? $config['name'] : '';
        if (!str_contains($name, '/')) {
            return '';
        }

        $parts = explode('/', $name, 2);
        $vendorPrefix = is_string($config['vendorPrefix'] ?? null) && $config['vendorPrefix'] !== ''
            ? $config['vendorPrefix']
            : $parts[0];
        $prefixType = is_string($config['prefixType'] ?? null) ? $config['prefixType'] : 'full';

        if ($prefixType === 'vendor') {
            return str_replace('-', '', $vendorPrefix);
        }

        return str_replace('-', '', $vendorPrefix) . '_' . str_replace('-', '', $parts[1]);
    }

    /**
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}|null
     */
    private function getContentBlockDefinition(string $ctype): ?array
    {
        return $this->getContentBlockDefinitions()[$ctype] ?? null;
    }

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    private function getCollectionsByParentTable(): array
    {
        if ($this->collectionsByParentTable !== null) {
            return $this->collectionsByParentTable;
        }

        $map = [];
        foreach ($this->getContentBlockDefinitions() as $definition) {
            foreach ($definition['collections'] as $collection) {
                $this->registerCollectionForParentTable('tt_content', $collection, $map);
            }
        }

        $this->collectionsByParentTable = $map;
        return $this->collectionsByParentTable;
    }

    /**
     * @param array<string, mixed> $collection
     * @param array<string, list<array<string, mixed>>> $map
     */
    private function registerCollectionForParentTable(string $parentTable, array $collection, array &$map): void
    {
        $map[$parentTable][] = $collection;
        $nestedCollections = $collection['collections'] ?? [];
        if (!is_array($nestedCollections)) {
            return;
        }

        $table = $this->getCollectionTable($collection);
        if ($table === '') {
            return;
        }

        foreach ($nestedCollections as $nestedCollection) {
            if (is_array($nestedCollection)) {
                $this->registerCollectionForParentTable($table, $this->normalizeStringKeyedArray($nestedCollection), $map);
            }
        }
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function isFileField(array $fieldConfig): bool
    {
        return ($fieldConfig['type'] ?? '') === 'File';
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getConfiguredInteger(array $config, string $key): ?int
    {
        if (!isset($config[$key]) || !is_numeric($config[$key])) {
            return null;
        }

        return (int)$config[$key];
    }

    /**
     * @param array<mixed, mixed> $array
     * @return array<string, mixed>
     */
    private function normalizeStringKeyedArray(array $array): array
    {
        $normalized = [];
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, true> $columns
     * @return array<string, mixed>
     */
    private function filterRow(array $row, array $columns): array
    {
        return array_intersect_key($row, $columns);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hasPayloadBeyondSystemFields(array $row): bool
    {
        $systemFields = [
            'pid' => true,
            'uid' => true,
            'sorting' => true,
            'hidden' => true,
            'deleted' => true,
            'sys_language_uid' => true,
            'crdate' => true,
            'tstamp' => true,
            'foreign_table_parent_uid' => true,
        ];

        return array_diff_key($row, $systemFields) !== [];
    }

    /**
     * @return array<string, true>
     */
    private function getColumnNames(string $table): array
    {
        if (isset($this->tableColumnsCache[$table])) {
            return $this->tableColumnsCache[$table];
        }

        $columns = [];
        try {
            foreach ($this->connectionPool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table) as $column) {
                $columns[$column->getName()] = true;
            }
        } catch (\Throwable) {
            $columns = [];
        }

        $this->tableColumnsCache[$table] = $columns;
        return $columns;
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return isset($this->getColumnNames($table)[$column]);
    }

    private function buildReadableFileTitle(string $value): string
    {
        $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? $value;
        $value = trim($value);

        return $value !== '' ? ucwords(strtolower($value)) : 'Starter asset';
    }
}
