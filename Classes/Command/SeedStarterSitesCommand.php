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
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\CollectionRecordSeeder;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\ExtensionFalSeeder;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\StarterContentBuilder;

/**
 * @phpstan-import-type StarterSite from StarterSiteDefinitions
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 */
#[AsCommand(
    name: 'desiderio:starter:seed',
    description: 'Create or update the useful Desiderio corporate starter site.'
)]
final class SeedStarterSitesCommand extends Command
{
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

    private ?ExtensionFalSeeder $starterFalSeeder = null;
    private ?CollectionRecordSeeder $collectionRecordSeeder = null;
    private ?CollectionCleanupService $collectionCleanupService = null;
    private ?ContentBlockCollectionMap $contentBlockCollectionMap = null;
    private ?LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper = null;
    private ?StarterContentBuilder $starterContentBuilder = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
        private readonly StorageRepository $storageRepository,
        private readonly DatabaseSchemaHelper $databaseSchema,
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
                'Starter to seed: all or corporate. Comma-separated values are allowed for forwards-compatible scripts.',
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
                'Seed the corporate starter into an existing root page instead of creating a root. Format: corporate:740.',
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

        $pageColumns = $this->databaseSchema->getColumnNames('pages');
        $contentColumns = $this->databaseSchema->getColumnNames('tt_content');
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
        return CollectionRecordSeeder::normalizeLastInsertId($lastInsertId);
    }

    private function getLiveWorkspaceQueryHelper(): LiveWorkspaceQueryHelper
    {
        return $this->liveWorkspaceQueryHelper ??= new LiveWorkspaceQueryHelper($this->databaseSchema);
    }

    private function getContentBlockCollectionMap(): ContentBlockCollectionMap
    {
        return $this->contentBlockCollectionMap ??= new ContentBlockCollectionMap();
    }

    private function getStarterFalSeeder(): ExtensionFalSeeder
    {
        return $this->starterFalSeeder ??= new ExtensionFalSeeder(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            self::STARTER_FAL_FOLDER,
            1777100241,
        );
    }

    private function getCollectionRecordSeeder(): CollectionRecordSeeder
    {
        return $this->collectionRecordSeeder ??= new CollectionRecordSeeder(
            $this->connectionPool,
            $this->databaseSchema,
            $this->getStarterFalSeeder(),
        );
    }

    private function getCollectionCleanupService(): CollectionCleanupService
    {
        return $this->collectionCleanupService ??= new CollectionCleanupService(
            $this->connectionPool,
            $this->databaseSchema,
            $this->getLiveWorkspaceQueryHelper(),
        );
    }

    private function getStarterContentBuilder(): StarterContentBuilder
    {
        return $this->starterContentBuilder ??= new StarterContentBuilder($this->databaseSchema);
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
            $this->databaseSchema->filterRow([
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
        $connection->insert('pages', $this->databaseSchema->filterRow([
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
            $this->getStarterFalSeeder()->seedFileReferences('tt_content', $contentUid, $pageUid, $now, $contentData['fileReferences']);
            $this->getCollectionRecordSeeder()->seed($contentUid, $pageUid, $now, $contentData['collections']);
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
     * @return list<string|CompositeExpression>
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
        $this->getCollectionCleanupService()->deleteCollectionRowsForParentUids(
            $parentUids,
            $parentTable,
            $this->getContentBlockCollectionMap()->getCollectionsByParentTable(),
        );
    }

    /**
     * @param list<int> $recordUids
     */
    private function deleteFileReferencesForRecords(string $table, array $recordUids): void
    {
        $this->getCollectionCleanupService()->deleteFileReferencesForRecords($table, $recordUids);
    }

    /**
     * @return list<string>
     */
    private function buildLiveWorkspaceConstraints(QueryBuilder $queryBuilder, string $table): array
    {
        return $this->getLiveWorkspaceQueryHelper()->buildLiveWorkspaceConstraints($queryBuilder, $table);
    }

    /**
     * @param StarterBlock $block
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function buildContentInsert(int $pid, array $block, int $sorting, int $now, array $columns): array
    {
        return $this->getStarterContentBuilder()->buildContentInsert($pid, $block, $sorting, $now, $columns);
    }
}
