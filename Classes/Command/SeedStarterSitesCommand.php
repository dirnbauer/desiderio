<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\ContentElementSeeder;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\DesiderioContentCleaner;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\SeedPageUpserter;
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

    private ?SeedPageUpserter $pageUpserter = null;
    private ?DesiderioContentCleaner $contentCleaner = null;
    private ?ContentElementSeeder $contentElementSeeder = null;
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
        $additionalDeleteCTypes = $contentDeleteScope === self::CONTENT_DELETE_SCOPE_STARTER
            ? self::REPLACEABLE_CORE_CTYPES
            : [];
        $createdPages = 0;
        $updatedPages = 0;
        $hiddenUnmanagedChildren = 0;
        $seededContentElements = 0;
        $now = time();

        $pageUpserter = $this->getPageUpserter();

        $starterIndex = 0;
        foreach ($selectedPresets as $presetSlug => $starter) {
            $starterIndex++;
            $mappedRootUid = $rootMap[$presetSlug] ?? null;
            $rootSlug = $mappedRootUid === null ? $starter['rootSlug'] : '/';
            $rootAttributes = $this->buildStarterPageAttributes(
                $starter['rootNavTitle'],
                $starter['abstract'],
                $starter['home']['layout'],
                false
            );

            if ($mappedRootUid !== null) {
                $rootUid = $mappedRootUid;
                $pageUpserter->update(
                    $rootUid,
                    $starter['rootTitle'],
                    $rootSlug,
                    $starterIndex * 1024,
                    $now,
                    $pageColumns,
                    $rootAttributes
                );
                $updatedPages++;
            } else {
                $rootUid = $pageUpserter->findExistingPageUid($parentPid, $starter['rootTitle'], $rootSlug, $pageColumns);
                if ($rootUid === null) {
                    $rootUid = $pageUpserter->create($parentPid, $starter['rootTitle'], $rootSlug, $starterIndex * 1024, $now, $pageColumns, $rootAttributes);
                    $createdPages++;
                } else {
                    $pageUpserter->update($rootUid, $starter['rootTitle'], $rootSlug, $starterIndex * 1024, $now, $pageColumns, $rootAttributes);
                    $updatedPages++;
                }
            }

            $seededContentElements += $this->seedPageContent(
                $rootUid,
                $starter['home']['content'],
                $now,
                $contentColumns,
                $additionalDeleteCTypes
            );

            $managedChildPageUids = [];
            foreach (array_values($starter['subpages']) as $pageIndex => $page) {
                $childSlug = $this->buildChildPageSlug($rootSlug, $page['slug']);
                $childAttributes = $this->buildStarterPageAttributes(
                    $page['navTitle'],
                    $page['abstract'],
                    $page['layout'],
                    (bool)($page['navHidden'] ?? false)
                );

                $pageUid = $pageUpserter->findExistingPageUid($rootUid, $page['title'], $childSlug, $pageColumns);
                if ($pageUid === null) {
                    $pageUid = $pageUpserter->create($rootUid, $page['title'], $childSlug, ($pageIndex + 1) * 256, $now, $pageColumns, $childAttributes);
                    $createdPages++;
                } else {
                    $pageUpserter->update($pageUid, $page['title'], $childSlug, ($pageIndex + 1) * 256, $now, $pageColumns, $childAttributes);
                    $updatedPages++;
                }
                $managedChildPageUids[] = $pageUid;

                $seededContentElements += $this->seedPageContent(
                    $pageUid,
                    $page['content'],
                    $now,
                    $contentColumns,
                    $additionalDeleteCTypes
                );
            }

            if ($mappedRootUid !== null && $hideUnmanagedChildren) {
                $hiddenUnmanagedChildren += $pageUpserter->hideUnmanagedChildPages($rootUid, $managedChildPageUids, $now);
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

    /**
     * @param array<int, StarterBlock> $contentBlocks
     * @param array<string, true> $contentColumns
     * @param list<string> $additionalDeleteCTypes
     */
    private function seedPageContent(
        int $pageUid,
        array $contentBlocks,
        int $now,
        array $contentColumns,
        array $additionalDeleteCTypes,
    ): int {
        $this->getContentCleaner()->softDeleteSeededContent($pageUid, $now, $additionalDeleteCTypes);
        $created = 0;

        foreach (array_values($contentBlocks) as $index => $block) {
            $contentData = $this->getStarterContentBuilder()->buildContentInsert(
                $pageUid,
                $block,
                ($index + 1) * 256,
                $now,
                $contentColumns
            );
            $this->getContentElementSeeder()->insert($pageUid, $now, $contentData);
            $created++;
        }

        return $created;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildStarterPageAttributes(
        string $navTitle,
        string $abstract,
        string $backendLayout,
        bool $navHidden,
    ): array {
        return [
            'nav_title' => $navTitle,
            'abstract' => $abstract,
            'description' => $abstract,
            'backend_layout' => $this->buildBackendLayoutIdentifier($backendLayout),
            'backend_layout_next_level' => $this->buildBackendLayoutIdentifier('DesiderioContentpage'),
            'nav_hide' => $navHidden ? 1 : 0,
        ];
    }

    private function buildBackendLayoutIdentifier(string $backendLayout): string
    {
        if ($backendLayout === '' || str_starts_with($backendLayout, 'pagets__')) {
            return $backendLayout;
        }

        return 'pagets__' . $backendLayout;
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

    private function getPageUpserter(): SeedPageUpserter
    {
        return $this->pageUpserter ??= new SeedPageUpserter(
            $this->connectionPool,
            $this->databaseSchema,
            new LiveWorkspaceQueryHelper($this->databaseSchema),
        );
    }

    private function getContentCleaner(): DesiderioContentCleaner
    {
        if ($this->contentCleaner === null) {
            $liveWorkspaceQueryHelper = new LiveWorkspaceQueryHelper($this->databaseSchema);
            $this->contentCleaner = new DesiderioContentCleaner(
                $this->connectionPool,
                $liveWorkspaceQueryHelper,
                new CollectionCleanupService($this->connectionPool, $this->databaseSchema, $liveWorkspaceQueryHelper),
                new ContentBlockCollectionMap(),
            );
        }

        return $this->contentCleaner;
    }

    private function getContentElementSeeder(): ContentElementSeeder
    {
        return $this->contentElementSeeder ??= new ContentElementSeeder(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            self::STARTER_FAL_FOLDER,
            1777100241,
        );
    }

    private function getStarterContentBuilder(): StarterContentBuilder
    {
        return $this->starterContentBuilder ??= new StarterContentBuilder($this->databaseSchema);
    }
}
