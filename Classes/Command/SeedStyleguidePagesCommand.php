<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\CollectionRecordSeeder;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\ExtensionFalSeeder;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;
use Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;

#[AsCommand(
    name: 'desiderio:styleguide:seed',
    description: 'Create or update shadcn styled Desiderio content element test pages below a parent page.'
)]
final class SeedStyleguidePagesCommand extends Command
{
    public const DEFAULT_PARENT_PID = 505;
    private const STYLEGUIDE_FAL_FOLDER = 'desiderio-styleguide';

    private ?LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper = null;
    private ?ContentBlockCollectionMap $contentBlockCollectionMap = null;
    private ?ExtensionFalSeeder $styleguideFalSeeder = null;
    private ?CollectionRecordSeeder $collectionRecordSeeder = null;
    private ?CollectionCleanupService $collectionCleanupService = null;
    private ?StyleguideFixtureResolver $fixtureResolver = null;

    private readonly StyleguideCollectionAliasPolicy $collectionAliasPolicy;
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
        private readonly StorageRepository $storageRepository,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private ?PowermailDemoSeeder $powermailDemoSeeder = null,
        private readonly StyleguideDemoValueGenerator $demoValueGenerator = new StyleguideDemoValueGenerator(),
        ?StyleguideCollectionAliasPolicy $collectionAliasPolicy = null,
    ) {
        parent::__construct();
        $this->collectionAliasPolicy = $collectionAliasPolicy ?? new StyleguideCollectionAliasPolicy($this->databaseSchema);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'Parent page uid for the generated content element test pages.',
                (string)self::DEFAULT_PARENT_PID
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Only print the planned pages and content element count.'
            )
            ->addOption(
                'allow-production',
                null,
                InputOption::VALUE_NONE,
                'Run even when Application Context is Production. Required to seed against production data.'
            )
            ->addOption(
                'skip-powermail',
                null,
                InputOption::VALUE_NONE,
                'Do not create the optional powermail demo form pages, even when powermail is installed.'
            )
            ->addOption(
                'powermail-storage-pid',
                null,
                InputOption::VALUE_REQUIRED,
                'Storage page uid for generated powermail form records. Defaults to the generated Desiderio Powermail Lab page.',
                '0'
            )
            ->addOption(
                'powermail-german-language',
                null,
                InputOption::VALUE_REQUIRED,
                'sys_language_uid used for German powermail demo translations.',
                '1'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentPid = (int)$input->getOption('parent');
        $dryRun = (bool)$input->getOption('dry-run');
        $allowProduction = (bool)$input->getOption('allow-production');
        $skipPowermail = (bool)$input->getOption('skip-powermail');
        $powermailStoragePid = $this->getIntegerInputOption($input, 'powermail-storage-pid');
        $powermailGermanLanguageUid = $this->getIntegerInputOption($input, 'powermail-german-language');

        $workspaceId = (int)$this->context->getPropertyFromAspect('workspace', 'id', 0);
        if ($workspaceId !== 0) {
            $io->error(sprintf(
                'Refusing to seed inside workspace #%d. The seeder writes live records and bypasses workspace overlays. Switch to the live workspace before running this command.',
                $workspaceId
            ));

            return self::FAILURE;
        }

        if (!$allowProduction && Environment::getContext()->isProduction()) {
            $io->error('Refusing to run in Production application context. Pass --allow-production to override (and only do so on a sandbox).');

            return self::FAILURE;
        }
        $groups = StyleguideContentGroups::getGroupsWithFixtures();
        $totalElements = array_sum(array_map(
            static fn (array $group): int => count($group['elements']),
            $groups
        ));

        if ($dryRun) {
            $io->title('Desiderio styleguide seed dry run');
            $io->listing(array_map(
                static fn (array $group): string => sprintf('%s: %d elements', $group['groupTitle'], count($group['elements'])),
                $groups
            ));
            if (!$skipPowermail) {
                $powermailForms = $this->getPowermailDemoSeeder()->getDemoForms();
                $io->listing(array_map(
                    static fn (array $form): string => sprintf('Powermail demo: %s', $form['pageTitleEn']),
                    $powermailForms
                ));
            }
            $io->success(sprintf(
                'Would create or update %d styleguide pages and %d content elements below page uid %d%s.',
                count($groups),
                $totalElements,
                $parentPid,
                $skipPowermail ? '' : sprintf(', plus %d powermail demo forms with EN/DE pages if powermail tables are available', count($this->getPowermailDemoSeeder()->getDemoForms()))
            ));

            return self::SUCCESS;
        }

        $pageColumns = $this->databaseSchema->getColumnNames('pages');
        $contentColumns = $this->databaseSchema->getColumnNames('tt_content');
        $createdPages = 0;
        $createdContentElements = 0;
        $now = time();

        foreach ($groups as $index => $group) {
            $pageUid = $this->findOrCreatePage(
                $parentPid,
                (string)$group['groupTitle'],
                (string)$group['groupId'],
                ($index + 1) * 256,
                $now,
                $pageColumns,
                $createdPages
            );

            $this->markExistingDesiderioContentAsDeleted($pageUid, $now);

            foreach ($group['elements'] as $elementIndex => $element) {
                $contentData = $this->buildContentInsert(
                    $pageUid,
                    (string)$element['ctype'],
                    (string)$element['name'],
                    $element['fixture'],
                    ($elementIndex + 1) * 256,
                    $now,
                    $contentColumns
                );

                $connection = $this->connectionPool->getConnectionForTable('tt_content');
                $connection->insert('tt_content', $contentData['row']);
                $contentUid = (int)$connection->lastInsertId();
                $this->seedFileReferences('tt_content', $contentUid, $pageUid, $now, $contentData['fileReferences']);
                $this->seedCollectionRecords($contentUid, $pageUid, $now, $contentData['collections']);
                $createdContentElements++;
            }
        }

        $powermailSummary = ['pages' => 0, 'forms' => 0, 'skipped' => true];
        if (!$skipPowermail) {
            $powermailSummary = $this->getPowermailDemoSeeder()->seed(
                $parentPid,
                $powermailStoragePid,
                $powermailGermanLanguageUid,
                $now,
                $io
            );
        }

        $io->success(sprintf(
            'Created or updated %d styleguide pages (%d new) and inserted %d Desiderio content elements below page uid %d%s.',
            count($groups),
            $createdPages,
            $createdContentElements,
            $parentPid,
            $powermailSummary['skipped'] ? '' : sprintf(' Added %d powermail demo forms across %d EN/DE pages.', $powermailSummary['forms'], $powermailSummary['pages'])
        ));

        return self::SUCCESS;
    }

    private function getPowermailDemoSeeder(): PowermailDemoSeeder
    {
        if ($this->powermailDemoSeeder === null) {
            $this->powermailDemoSeeder = GeneralUtility::makeInstance(PowermailDemoSeeder::class);
        }

        return $this->powermailDemoSeeder;
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

    /**
     * @param array<string, true> $columns
     */
    private function findOrCreatePage(
        int $parentPid,
        string $groupTitle,
        string $groupId,
        int $sorting,
        int $now,
        array $columns,
        int &$createdPages,
    ): int {
        $title = $groupTitle;
        $slug = $this->buildStyleguidePageSlug($groupId);
        $existingPageUid = $this->findExistingStyleguidePageUid($parentPid, $title, $slug, $columns);

        if ($existingPageUid !== null) {
            $this->updateStyleguidePage($existingPageUid, $title, $slug, $sorting, $now, $columns);
            return $existingPageUid;
        }

        $createdPages++;
        return $this->createStyleguidePage($parentPid, $title, $slug, $sorting, $now, $columns);
    }

    private function buildStyleguidePageSlug(string $groupId): string
    {
        return '/desiderio-' . $groupId;
    }

    /**
     * @param array<string, true> $columns
     */
    private function findExistingStyleguidePageUid(int $parentPid, string $title, string $slug, array $columns): ?int
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

        if ($existingUid === false) {
            return null;
        }

        return (int)$existingUid;
    }

    /**
     * @param array<string, true> $columns
     */
    private function updateStyleguidePage(
        int $pageUid,
        string $title,
        string $slug,
        int $sorting,
        int $now,
        array $columns,
    ): void {
        $this->connectionPool->getConnectionForTable('pages')->update(
            'pages',
            $this->databaseSchema->filterRow([
                'title' => $title,
                'slug' => $slug,
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
    private function createStyleguidePage(
        int $parentPid,
        string $title,
        string $slug,
        int $sorting,
        int $now,
        array $columns,
    ): int {
        $connection = $this->connectionPool->getConnectionForTable('pages');
        $connection->insert('pages', $this->databaseSchema->filterRow([
            'pid' => $parentPid,
            'title' => $title,
            'doktype' => 1,
            'slug' => $slug,
            'hidden' => 0,
            'sorting' => $sorting,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns));

        return (int)$connection->lastInsertId();
    }

    private function markExistingDesiderioContentAsDeleted(int $pageUid, int $now): void
    {
        $existingContentUids = $this->findExistingDesiderioContentUids($pageUid);
        if ($existingContentUids !== []) {
            $this->deleteFileReferencesForRecords('tt_content', $existingContentUids);
            $this->deleteCollectionRowsForParentUids($existingContentUids, 'tt_content');
        }
        $this->deleteCollectionRowsForPage($pageUid);

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('desiderio_%')),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, 'tt_content')
            )
            ->executeStatement();
    }

    private function findExistingDesiderioContentUids(int $pageUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        $uids = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('desiderio_%')),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, 'tt_content')
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return array_map(
            static fn (mixed $uid): int => (int)$uid,
            $uids
        );
    }

    private function getLiveWorkspaceQueryHelper(): LiveWorkspaceQueryHelper
    {
        return $this->liveWorkspaceQueryHelper ??= new LiveWorkspaceQueryHelper($this->databaseSchema);
    }

    private function getContentBlockCollectionMap(): ContentBlockCollectionMap
    {
        return $this->contentBlockCollectionMap ??= new ContentBlockCollectionMap();
    }

    private function getStyleguideFalSeeder(): ExtensionFalSeeder
    {
        return $this->styleguideFalSeeder ??= new ExtensionFalSeeder(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            self::STYLEGUIDE_FAL_FOLDER,
            1777100143,
        );
    }

    private function getCollectionRecordSeeder(): CollectionRecordSeeder
    {
        return $this->collectionRecordSeeder ??= new CollectionRecordSeeder(
            $this->connectionPool,
            $this->databaseSchema,
            $this->getStyleguideFalSeeder(),
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

    private function getFixtureResolver(): StyleguideFixtureResolver
    {
        return $this->fixtureResolver ??= new StyleguideFixtureResolver(
            $this->databaseSchema,
            $this->demoValueGenerator,
            $this->collectionAliasPolicy,
        );
    }

    private function deleteCollectionRowsForPage(int $pageUid): void
    {
        $this->getCollectionCleanupService()->deleteCollectionRowsForPage(
            $pageUid,
            $this->getCollectionTableNames(),
        );
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
     * Restrict destructive styleguide cleanup to live rows. TYPO3 stores
     * workspace versions in the same table, so queries with restrictions
     * removed must add the live workspace predicates explicitly.
     *
     * @return list<string>
     */
    private function buildLiveWorkspaceConstraints(QueryBuilder $queryBuilder, string $table): array
    {
        return $this->getLiveWorkspaceQueryHelper()->buildLiveWorkspaceConstraints($queryBuilder, $table);
    }

    /**
     * @param array<string, mixed> $fixture
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function buildContentInsert(
        int $pid,
        string $ctype,
        string $name,
        array $fixture,
        int $sorting,
        int $now,
        array $columns,
    ): array {
        return $this->getFixtureResolver()->buildContentInsert($pid, $ctype, $name, $fixture, $sorting, $now, $columns);
    }

    /**
     * @return list<string>
     */
    private function getCollectionTableNames(): array
    {
        return $this->getContentBlockCollectionMap()->getCollectionTableNames();
    }

    /**
     * @param array<string, array{table: string, column?: string, items: list<array<string, mixed>>}> $collections
     */
    private function seedCollectionRecords(int $contentUid, int $pageUid, int $now, array $collections): void
    {
        $this->getCollectionRecordSeeder()->seed($contentUid, $pageUid, $now, $collections);
    }

    /**
     * @param array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>> $fileReferences
     */
    private function seedFileReferences(string $table, int $uid, int $pid, int $now, array $fileReferences): void
    {
        $this->getStyleguideFalSeeder()->seedFileReferences($table, $uid, $pid, $now, $fileReferences);
    }

}