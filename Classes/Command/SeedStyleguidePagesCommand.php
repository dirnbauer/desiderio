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
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\DataHandling\IconItemsProcessor;
use Webconsulting\Desiderio\Icon\IconRegistry;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;

#[AsCommand(
    name: 'desiderio:styleguide:seed',
    description: 'Create or update shadcn styled Desiderio content element test pages below a parent page.'
)]
final class SeedStyleguidePagesCommand extends Command
{
    public const DEFAULT_PARENT_PID = 505;
    private const FIELD_SKIP = '__skip__';
    private const FILE_REFERENCES_KEY = '__fileReferences';
    private const NESTED_COLLECTIONS_KEY = '__collections';
    private const STYLEGUIDE_FAL_FOLDER = 'desiderio-styleguide';

    /** @var array<string, list<array<string, mixed>>>|null */
    private ?array $collectionsByParentTable = null;

    /** @var array<string, File> */
    private array $styleguideFiles = [];

    private ?Folder $styleguideFalFolder = null;

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

    private function deleteCollectionRowsForPage(int $pageUid): void
    {
        foreach ($this->getCollectionTableNames() as $table) {
            if (!$this->databaseSchema->tableHasColumn($table, 'pid')) {
                continue;
            }

            $collectionUids = $this->findCollectionRowUidsByPid($table, $pageUid);
            $this->deleteFileReferencesForRecords($table, $collectionUids);

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $queryBuilder
                ->delete($table)
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                    ...$this->buildLiveWorkspaceConstraints($queryBuilder, $table)
                )
                ->executeStatement();
        }
    }

    /**
     * @return list<int>
     */
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

    /**
     * @param list<int> $parentUids
     */
    private function deleteCollectionRowsForParentUids(array $parentUids, string $parentTable): void
    {
        if ($parentUids === []) {
            return;
        }

        foreach ($this->getCollectionsByParentTable()[$parentTable] ?? [] as $collection) {
            $table = $collection['table'] ?? null;
            if (!is_string($table) || $table === '') {
                continue;
            }
            if (!$this->databaseSchema->tableHasColumn($table, 'foreign_table_parent_uid')) {
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

        return array_map(
            static fn (mixed $uid): int => (int)$uid,
            $uids
        );
    }

    /**
     * @return list<int>
     */
    private function findCollectionRowUidsByPid(string $table, int $pageUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $uids = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                ...$this->buildLiveWorkspaceConstraints($queryBuilder, $table)
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return array_map(
            static fn (mixed $uid): int => (int)$uid,
            $uids
        );
    }

    /**
     * @param list<int> $recordUids
     */
    private function deleteFileReferencesForRecords(string $table, array $recordUids): void
    {
        if ($recordUids === [] || !$this->databaseSchema->tableHasColumn('sys_file_reference', 'uid_foreign')) {
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
     * Restrict destructive styleguide cleanup to live rows. TYPO3 stores
     * workspace versions in the same table, so queries with restrictions
     * removed must add the live workspace predicates explicitly.
     *
     * @return list<string>
     */
    private function buildLiveWorkspaceConstraints(QueryBuilder $queryBuilder, string $table): array
    {
        $constraints = [];
        if ($this->databaseSchema->tableHasColumn($table, 't3ver_wsid')) {
            $constraints[] = $queryBuilder->expr()->eq(
                't3ver_wsid',
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            );
        }
        if ($this->databaseSchema->tableHasColumn($table, 't3ver_oid')) {
            $constraints[] = $queryBuilder->expr()->eq(
                't3ver_oid',
                $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)
            );
        }

        return $constraints;
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
        $row = [
            'pid' => $pid,
            'CType' => $ctype,
            'colPos' => 0,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ];

        [$resolvedFields, $collections, $fileReferences] = $this->resolveFixtureFields($ctype, $fixture, $name);

        foreach ($resolvedFields as $field => $value) {
            $row[$field] = $value;
        }

        foreach ($fileReferences as $field => $references) {
            $row[$field] = count($references);
        }

        foreach ($collections as $field => $collection) {
            $row[$collection['column'] ?? $field] = count($collection['items']);
        }

        return [
            'row' => $this->databaseSchema->filterRow($row, $columns),
            'collections' => $collections,
            'fileReferences' => $fileReferences,
        ];
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, 2: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function resolveFixtureFields(string $ctype, array $fixture, string $name = ''): array
    {
        $definition = ContentBlockDefinitionRegistry::getDefinition($ctype);
        if ($definition === null) {
            $row = [];
            foreach ($fixture as $field => $value) {
                if ($field === '_type' || $field === 'CType' || $field === 'ctype' || is_array($value)) {
                    continue;
                }
                $row[(string)$field] = $this->normalizeScalarValue($value);
            }

            return [$row, [], []];
        }

        $resolvedFields = [];
        $collections = [];

        foreach ($fixture as $field => $value) {
            $field = (string)$field;
            if ($field === '_type' || $field === 'CType' || $field === 'ctype') {
                continue;
            }

            if (is_array($value)) {
                $collectionField = $this->resolveCollectionField($field, $value, $definition);
                if ($collectionField !== null) {
                    $items = $this->normalizeCollectionItems($value, $definition['collections'][$collectionField]);
                    if ($items !== []) {
                        $collectionTable = $definition['collections'][$collectionField]['table'] ?? null;
                        $collectionColumn = $definition['collections'][$collectionField]['column'] ?? $collectionField;
                        if (!is_string($collectionTable) || !is_string($collectionColumn)) {
                            continue;
                        }
                        $collections[$collectionField] = [
                            'table' => $collectionTable,
                            'column' => $collectionColumn,
                            'items' => $items,
                        ];
                    }
                    continue;
                }

                $scalarField = $this->resolveScalarField($field, $definition['fields']);
                if ($scalarField !== null) {
                    $normalized = $this->normalizeArrayForScalarField($value, $scalarField);
                    if ($normalized !== self::FIELD_SKIP) {
                        $resolvedFields[$scalarField] = $this->normalizeFieldValue(
                            $normalized,
                            $definition['fields'][$scalarField]
                        );
                    }
                }
                continue;
            }

            $scalarField = $this->resolveScalarField($field, $definition['fields']);
            if ($scalarField === null) {
                continue;
            }
            $resolvedFields[$scalarField] = $this->demoValueGenerator->normalizeResolvedFixtureFieldValue(
                $ctype,
                $scalarField,
                $this->normalizeFieldValue($value, $definition['fields'][$scalarField])
            );
        }

        return $this->completeResolvedFixtureData($ctype, $name !== '' ? $name : $ctype, $definition, $resolvedFields, $collections, $fixture);
    }

    /**
     * @param array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $definition
     * @param array<string, mixed> $resolvedFields
     * @param array<string, array{table: string, column: string, items: list<array<string, mixed>>}> $collections
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, 2: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function completeResolvedFixtureData(
        string $ctype,
        string $name,
        array $definition,
        array $resolvedFields,
        array $collections,
        array $fixture = [],
    ): array {
        $fileReferences = [];

        foreach ($definition['fields'] as $field => $fieldConfig) {
            if ($this->isFileField($fieldConfig)) {
                $explicitReferences = $this->buildFileReferenceFixturesFromFixtureValue($fixture[$field] ?? null, $fieldConfig);
                unset($resolvedFields[$field]);
                $fileReferences[$field] = $explicitReferences !== []
                    ? $explicitReferences
                    : $this->buildFileReferenceFixtures($name . '-' . $field, $fieldConfig, 0);
                continue;
            }

            if (!array_key_exists($field, $resolvedFields) || $this->isEmptySeedValue($resolvedFields[$field])) {
                $default = $this->demoValueGenerator->buildFixtureBackedFieldValue($field, $fixture)
                    ?? $this->demoValueGenerator->buildDefaultFieldValue($ctype, $name, $field, $fieldConfig, 0);
                if ($default !== self::FIELD_SKIP) {
                    $resolvedFields[$field] = $default;
                }
            }
        }

        foreach ($definition['collections'] as $field => $collection) {
            $existingItems = $collections[$field]['items'] ?? [];
            $targetItemCount = $this->getTargetCollectionItemCount($collection, count($existingItems));
            $items = [];

            for ($index = 0; $index < $targetItemCount; $index++) {
            $item = $existingItems[$index] ?? [];
            $item = is_array($item) ? ContentBlockDefinitionRegistry::normalizeStringKeyedArray($item) : [];
            $completedItem = $this->completeCollectionItem($ctype, $name, $field, $collection, $item, $index);
                if ($completedItem !== []) {
                    $items[] = $completedItem;
                }
            }

            if ($items !== []) {
                $collectionTable = $collection['table'] ?? null;
                $collectionColumn = $collection['column'] ?? $field;
                if (!is_string($collectionTable) || !is_string($collectionColumn)) {
                    continue;
                }
                $collections[$field] = [
                    'table' => $collectionTable,
                    'column' => $collectionColumn,
                    'items' => $items,
                ];
            }
        }

        return [$resolvedFields, $collections, $fileReferences];
    }

    /**
     * @param array<string, mixed> $collection
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function completeCollectionItem(
        string $ctype,
        string $name,
        string $collectionField,
        array $collection,
        array $item,
        int $index,
    ): array {
        $fileReferences = [];
        $nestedCollections = [];
        if (isset($item[self::NESTED_COLLECTIONS_KEY]) && is_array($item[self::NESTED_COLLECTIONS_KEY])) {
            $nestedCollections = $item[self::NESTED_COLLECTIONS_KEY];
            unset($item[self::NESTED_COLLECTIONS_KEY]);
        }

        $collectionFields = $collection['fields'] ?? [];
        if (!is_array($collectionFields)) {
            $collectionFields = [];
        }

        foreach ($collectionFields as $field => $fieldConfig) {
            if (!is_string($field) || !is_array($fieldConfig)) {
                continue;
            }
            $fieldConfig = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig);
            if ($this->isFileField($fieldConfig)) {
                $explicitReferences = $this->buildFileReferenceFixturesFromFixtureValue($item[$field] ?? null, $fieldConfig);
                unset($item[$field]);
                $fileReferences[$field] = $explicitReferences !== []
                    ? $explicitReferences
                    : $this->buildFileReferenceFixtures($name . '-' . $collectionField . '-' . $field, $fieldConfig, $index);
                $item[$field] = count($fileReferences[$field]);
                continue;
            }

            if (!array_key_exists($field, $item) || $this->isEmptySeedValue($item[$field])) {
                $default = $this->demoValueGenerator->buildDefaultFieldValue($ctype, $name, $field, $fieldConfig, $index);
                if ($default !== self::FIELD_SKIP) {
                    $item[$field] = $default;
                }
            }
        }

        $nestedCollectionDefinitions = $collection['collections'] ?? [];
        if (!is_array($nestedCollectionDefinitions)) {
            $nestedCollectionDefinitions = [];
        }

        foreach ($nestedCollectionDefinitions as $field => $nestedCollection) {
            if (!is_string($field) || !is_array($nestedCollection)) {
                continue;
            }
            $nestedCollection = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($nestedCollection);
            $existingNestedCollection = $nestedCollections[$field] ?? [];
            $existingItems = is_array($existingNestedCollection) && is_array($existingNestedCollection['items'] ?? null)
                ? $existingNestedCollection['items']
                : [];
            $targetItemCount = $this->getTargetCollectionItemCount($nestedCollection, count($existingItems));
            $items = [];

            for ($nestedIndex = 0; $nestedIndex < $targetItemCount; $nestedIndex++) {
                $nestedItem = $existingItems[$nestedIndex] ?? [];
                $nestedItem = is_array($nestedItem) ? ContentBlockDefinitionRegistry::normalizeStringKeyedArray($nestedItem) : [];
                $completedItem = $this->completeCollectionItem($ctype, $name, $field, $nestedCollection, $nestedItem, $nestedIndex);
                if ($completedItem !== []) {
                    $items[] = $completedItem;
                }
            }

            if ($items !== []) {
                $nestedTable = $nestedCollection['table'] ?? null;
                if (!is_string($nestedTable) || $nestedTable === '') {
                    continue;
                }
                $nestedCollections[$field] = [
                    'table' => $nestedTable,
                    'items' => $items,
                ];
                $item[$field] = count($items);
            }
        }

        if ($fileReferences !== []) {
            $item[self::FILE_REFERENCES_KEY] = $fileReferences;
        }
        if ($nestedCollections !== []) {
            $item[self::NESTED_COLLECTIONS_KEY] = $nestedCollections;
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function getTargetCollectionItemCount(array $collection, int $existingItemCount): int
    {
        $minimum = max(1, is_int($collection['minItems'] ?? null) ? $collection['minItems'] : 1);
        $target = max(3, $minimum, $existingItemCount);
        $maximum = $collection['maxItems'] ?? null;

        if (is_int($maximum)) {
            $target = min($target, max(1, $maximum));
        }

        return $target;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    private function buildFileReferenceFixtures(string $field, array $fieldConfig, int $index): array
    {
        $maxItems = ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxitems')
            ?? ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxItems')
            ?? 1;
        $count = max(1, min(3, $maxItems));
        $assets = $this->isAudioFileField($field, $fieldConfig)
            ? $this->getStyleguideAudioAssets()
            : $this->getStyleguideImageAssets();
        $references = [];

        for ($offset = 0; $offset < $count; $offset++) {
            $assetIndex = (int)(abs(crc32($field . ':' . ($index + $offset))) % count($assets));
            $asset = $assets[$assetIndex];
            $references[] = [
                'file' => $asset['file'],
                'title' => $asset['title'],
                'alternative' => $asset['alt'],
                'description' => $asset['credit'],
                'source' => $asset['source'],
            ];
        }

        return $references;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    private function buildFileReferenceFixturesFromFixtureValue(mixed $value, array $fieldConfig): array
    {
        if ($this->isEmptySeedValue($value)) {
            return [];
        }

        $maxItems = ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxitems')
            ?? ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxItems')
            ?? 1;
        $maxItems = max(1, $maxItems);
        $items = [];

        if (is_string($value)) {
            $items[] = ['file' => $value];
        } elseif (is_array($value) && isset($value['file'])) {
            $items[] = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($value);
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                if (is_string($item)) {
                    $items[] = ['file' => $item];
                    continue;
                }
                if (is_array($item) && isset($item['file'])) {
                    $items[] = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($item);
                }
            }
        }

        $references = [];
        foreach (array_slice($items, 0, $maxItems) as $item) {
            $file = trim((string)($item['file'] ?? ''));
            if ($file === '') {
                continue;
            }
            if (str_starts_with($file, 'EXT:desiderio/')) {
                $file = substr($file, strlen('EXT:desiderio/'));
            }

            $fallbackTitle = $this->demoValueGenerator->buildReadableFileTitle(pathinfo($file, PATHINFO_FILENAME));
            $source = trim((string)($item['source'] ?? $item['link'] ?? ''));

            $references[] = [
                'file' => $file,
                'title' => trim((string)($item['title'] ?? $fallbackTitle)),
                'alternative' => trim((string)($item['alternative'] ?? $item['alt'] ?? $fallbackTitle)),
                'description' => trim((string)($item['description'] ?? $item['credit'] ?? $source)),
                'source' => $source,
            ];
        }

        return $references;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function isAudioFileField(string $field, array $fieldConfig): bool
    {
        $identifier = $fieldConfig['identifier'] ?? '';
        $label = $fieldConfig['label'] ?? '';
        $allowed = $fieldConfig['allowed'] ?? '';
        $haystack = $field
            . ' ' . (is_scalar($identifier) ? (string)$identifier : '')
            . ' ' . (is_scalar($label) ? (string)$label : '');
        $normalized = $this->demoValueGenerator->normalizeIdentifier($haystack);
        $allowedTypes = is_scalar($allowed) ? strtolower((string)$allowed) : '';

        return str_contains($normalized, 'audio')
            || str_contains($allowedTypes, 'audio');
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function getStyleguideAudioAssets(): array
    {
        return [
            [
                'file' => 'Resources/Public/Styleguide/Audio/editorial-brief.wav',
                'title' => 'Editorial brief audio',
                'alt' => 'Short generated audio tone for the Audio Player styleguide fixture.',
                'credit' => 'Generated demo audio for Desiderio styleguide seeding.',
                'source' => 'EXT:desiderio/Resources/Public/Styleguide/Audio/editorial-brief.wav',
            ],
        ];
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function getStyleguideImageAssets(): array
    {
        return [
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/workspace-marvin-meyer.jpg',
                'title' => 'Collaborative workspace',
                'alt' => 'People working together around laptops in a collaborative workspace.',
                'credit' => 'Copyright/credit: Photo by Marvin Meyer on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/people-sitting-down-near-table-with-assorted-laptop-computers-SYTO3xs06fU',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/laptop-mimi-thian.jpg',
                'title' => 'Laptop work session',
                'alt' => 'A laptop open on a person\'s lap during a focused work session.',
                'credit' => 'Copyright/credit: Photo by Mimi Thian on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/macbook-on-womans-lap-i5cd_SlY8XY',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/laptop-glenn-carstens-peters.jpg',
                'title' => 'Planning on a laptop',
                'alt' => 'Hands using a laptop while planning work on a wooden desk.',
                'credit' => 'Copyright/credit: Photo by Glenn Carstens-Peters on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/person-using-macbook-pro-npxXWgQ33ZQ',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/forest-marvin-meyer.jpg',
                'title' => 'Forest path',
                'alt' => 'Tall green trees lining a quiet forest path in daylight.',
                'credit' => 'Copyright/credit: Photo by Marvin Meyer on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/green-trees-on-forest-during-daytime-qLTsA_plc1k',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/river-marvin-meyer.jpg',
                'title' => 'City river walk',
                'alt' => 'People walking beside a city river with buildings in the distance.',
                'credit' => 'Copyright/credit: Photo by Marvin Meyer on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/people-walking-beside-river-WpCviXDvoyQ',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/office-turquo-cabbit.jpg',
                'title' => 'Modern office atrium',
                'alt' => 'A modern multi-level office atrium with glass railings and warm light.',
                'credit' => 'Copyright/credit: Photo by Turquo Cabbit on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/modern-office-building-interior-with-multiple-floors-QkGDA4Q4Vdk',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/workspace-david-kristianto.jpg',
                'title' => 'Organized product workspace',
                'alt' => 'A modern organized workspace with a laptop, design tools, and warm task lighting.',
                'credit' => 'Copyright/credit: Photo by David Kristianto on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/a-modern-organized-workspace-with-a-laptop-aN8yRTfGYXY',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/dashboard-neil-fernandez.jpg',
                'title' => 'Dark product dashboard',
                'alt' => 'A laptop displaying a dark modern dashboard interface.',
                'credit' => 'Copyright/credit: Photo by Neil Fernandez on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/a-modern-laptop-displaying-a-dark-themed-dashboard-6-0ajRI1cgs',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/office-e-vos.jpg',
                'title' => 'Glass office walkways',
                'alt' => 'A modern office interior with glass walls, walkways, and open communal space.',
                'credit' => 'Copyright/credit: Photo by E Vos on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/modern-office-interior-with-glass-walls-and-walkways-V_yQ8IyCmYY',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/facade-fabian-kleiser.jpg',
                'title' => 'Geometric glass facade',
                'alt' => 'A blue glass office facade with geometric reflections and evening light.',
                'credit' => 'Copyright/credit: Photo by Fabian Kleiser on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/glass-facade-of-a-modern-office-building-V5vF94h52r0',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/office-deliberate-directions.jpg',
                'title' => 'Glass-walled modern office',
                'alt' => 'A bright modern office with glass walls, teal accents, and clean work areas.',
                'credit' => 'Copyright/credit: Photo by Deliberate Directions on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/modern-office-space-with-glass-walls-and-light-decor-wlHBYkK2y4k',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/whiteboard-vitaly-gariev.jpg',
                'title' => 'Strategy whiteboard session',
                'alt' => 'A modern team reviewing a whiteboard strategy session in a creative office.',
                'credit' => 'Copyright/credit: Photo by Vitaly Gariev on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/team-collaborating-around-a-whiteboard-in-an-office-CdTQI-Nh7J4',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/desk-logan-weaver.jpg',
                'title' => 'Minimal product desk',
                'alt' => 'A refined modern desk setup with laptop, keyboard, books, and warm task light.',
                'credit' => 'Copyright/credit: Photo by LOGAN WEAVER | @LGNWVR on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/a-modern-desk-setup-with-laptop-and-books-xjyHDnA93Pk',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/planning-blue-sky.jpg',
                'title' => 'Agile planning board',
                'alt' => 'A team discussing tasks at a whiteboard during an agile planning session.',
                'credit' => 'Copyright/credit: Photo by blue sky on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/four-men-gathered-around-a-whiteboard-with-sticky-notes-MLWk6FFWURU',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function isFileField(array $fieldConfig): bool
    {
        return ($fieldConfig['type'] ?? '') === 'File';
    }

    private function isEmptySeedValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }



    /**
     * @param array<string, array<string, mixed>> $fields
     */
    private function resolveScalarField(string $field, array $fields): ?string
    {
        if (isset($fields[$field])) {
            return $field;
        }

        foreach ($this->collectionAliasPolicy->getScalarFieldAliases()[$field] ?? [] as $candidate) {
            if (isset($fields[$candidate])) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<int|string, mixed> $value
     * @param array{collections: array<string, array<string, mixed>>} $definition
     */
    private function resolveCollectionField(string $field, array $value, array $definition): ?string
    {
        if (isset($definition['collections'][$field])) {
            return $field;
        }

        if ($field === 'headers' && isset($definition['collections']['column_definitions'])) {
            return 'column_definitions';
        }

        $bestField = null;
        $bestScore = 0.0;
        $runnerUp = 0.0;

        foreach ($definition['collections'] as $identifier => $collection) {
            $score = $this->scoreCollectionCandidate($field, $value, $identifier, $collection);
            if ($score > $bestScore) {
                $runnerUp = $bestScore;
                $bestScore = $score;
                $bestField = $identifier;
                continue;
            }
            if ($score > $runnerUp) {
                $runnerUp = $score;
            }
        }

        if ($bestScore <= 0.0 || $bestScore === $runnerUp) {
            return null;
        }

        return $bestField;
    }

    /**
     * @param array<int|string, mixed> $value
     * @param array<string, mixed> $collection
     */
    private function scoreCollectionCandidate(string $field, array $value, string $identifier, array $collection): float
    {
        $score = 0.0;
        $normalizedField = $this->demoValueGenerator->normalizeIdentifier($field);
        $normalizedIdentifier = $this->demoValueGenerator->normalizeIdentifier($identifier);

        if ($normalizedField === $normalizedIdentifier) {
            $score += 6.0;
        }
        if ($this->singularize($normalizedField) === $this->singularize($normalizedIdentifier)) {
            $score += 4.0;
        }
        if (str_contains($normalizedIdentifier, $this->singularize($normalizedField))) {
            $score += 2.0;
        }
        if ($field === 'headers' && $identifier === 'column_definitions') {
            $score += 10.0;
        }
        if ($field === 'columns' && str_contains($normalizedIdentifier, 'column')) {
            $score += 4.0;
        }
        if ($field === 'links' && (str_contains($normalizedIdentifier, 'link') || str_contains($normalizedIdentifier, 'nav'))) {
            $score += 4.0;
        }

        if ($this->isListOfScalars($value)) {
            foreach (['label', 'title', 'name', 'feature_name', 'row_label', 'text', 'value', 'question', 'row_data', 'links', 'features', 'features_list'] as $candidate) {
                if (isset($collection['fields'][$candidate]) || $this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $candidate)) {
                    $score += 1.0;
                    break;
                }
            }

            return $score;
        }

        $matches = 0;
        $total = 0;
        foreach ($value as $item) {
            if (!is_array($item)) {
                continue;
            }
            foreach (array_keys($item) as $itemKey) {
                $total++;
                if (
                    $this->collectionAliasPolicy->resolveNestedCollectionField((string)$itemKey, $item[(string)$itemKey], $collection) !== null
                    || $this->collectionAliasPolicy->resolveChildField((string)$itemKey, $item[(string)$itemKey], $collection) !== null
                ) {
                    $matches++;
                }
            }
        }

        if ($total > 0) {
            $score += ($matches / $total) * 5.0;
        }

        return $score;
    }

    /**
     * @param array<int|string, mixed> $items
     * @param array<string, mixed> $collection
     * @return list<array<string, mixed>>
     */
    private function normalizeCollectionItems(array $items, array $collection): array
    {
        if ($items === []) {
            return [];
        }

        if ($collection['table'] === 'column_definitions' && $this->isListOfScalars($items)) {
            $definitions = [];
            foreach ($items as $header) {
                $label = trim((string)$header);
                if ($label === '') {
                    continue;
                }
                $definition = [
                    'column_label' => $label,
                ];
                if (isset($collection['fields']['column_key'])) {
                    $definition['column_key'] = $this->demoValueGenerator->buildColumnKey($label);
                }
                $definition['column_align'] = 'left';

                $definitions[] = $definition;
            }

            return $definitions;
        }

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
            $targetField = $this->findPreferredTextField($collection);
            if ($targetField === null) {
                return [];
            }

            $fieldConfig = $this->getCollectionFieldConfig($collection, $targetField);

            return [
                $targetField => $fieldConfig !== null
                    ? $this->normalizeFieldValue($item, $fieldConfig)
                    : $this->normalizeScalarValue($item),
            ];
        }

        if ($item === []) {
            return [];
        }

        $normalizedItem = [];
        foreach ($item as $field => $value) {
            $field = (string)$field;
            $nestedCollectionField = $this->collectionAliasPolicy->resolveNestedCollectionField($field, $value, $collection);
            if ($nestedCollectionField !== null) {
                $items = $this->normalizeCollectionItems(
                    $this->collectionAliasPolicy->normalizeCollectionSourceItems($value, $field),
                    $collection['collections'][$nestedCollectionField]
                );
                if ($items !== []) {
                    $normalizedItem[self::NESTED_COLLECTIONS_KEY][$nestedCollectionField] = [
                        'table' => $collection['collections'][$nestedCollectionField]['table'],
                        'items' => $items,
                    ];
                    $normalizedItem[$nestedCollectionField] = count($items);
                }
                continue;
            }

            if ($this->collectionAliasPolicy->shouldSkipLegacyStructuredListField($field, $collection)) {
                continue;
            }

            $resolvedField = $this->collectionAliasPolicy->resolveChildField($field, $value, $collection);
            if ($resolvedField === null) {
                continue;
            }

            if (is_array($value)) {
                $normalized = $this->normalizeArrayForCollectionField($value, $resolvedField, $collection);
                if ($normalized === self::FIELD_SKIP) {
                    continue;
                }
                $normalizedItem[$resolvedField] = isset($collection['fields'][$resolvedField])
                    ? $this->normalizeFieldValue($normalized, $collection['fields'][$resolvedField])
                    : $normalized;
                continue;
            }

            $normalizedItem[$resolvedField] = isset($collection['fields'][$resolvedField])
                ? $this->normalizeFieldValue($value, $collection['fields'][$resolvedField])
                : $this->normalizeScalarValue($value);
        }

        $normalizedItem = $this->populateFixedLinkSlots($normalizedItem, $item, $collection);

        if ($normalizedItem === [] && $this->collectionAliasPolicy->collectionHasNestedCollection($collection, 'cells')) {
            $values = array_values($item);
            if (!$this->containsNestedArray($values)) {
                $cellItems = $this->normalizeCollectionItems($values, $collection['collections']['cells']);
                if ($cellItems !== []) {
                    if (isset($collection['fields']['row_label'])) {
                        $normalizedItem['row_label'] = $this->normalizeScalarValue($values[0] ?? '');
                    }
                    $normalizedItem[self::NESTED_COLLECTIONS_KEY]['cells'] = [
                        'table' => $collection['collections']['cells']['table'],
                        'items' => $cellItems,
                    ];
                    $normalizedItem['cells'] = count($cellItems);
                }
            }
        }

        if ($normalizedItem === [] && ($this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), 'row_data') || isset($collection['fields']['row_data']))) {
            $values = array_values($item);
            if (!$this->containsNestedArray($values)) {
                $normalizedItem['row_data'] = implode('|', array_map(static fn (mixed $value): string => trim((string)$value), $values));
                foreach ($values as $index => $value) {
                    $columnName = 'col' . ($index + 1);
                    if ($this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $columnName)) {
                        $normalizedItem[$columnName] = $this->normalizeScalarValue($value);
                    }
                }
            }
        }

        return $normalizedItem;
    }

    /**
     * @param array<string, mixed> $normalizedItem
     * @param array<string, mixed> $sourceItem
     * @param array<string, mixed> $collection
     * @return array<string, mixed>
     */
    private function populateFixedLinkSlots(array $normalizedItem, array $sourceItem, array $collection): array
    {
        $normalizedItem = $this->populateNumberedLinkSlots(
            $normalizedItem,
            $sourceItem['links'] ?? null,
            $collection,
            'link_%d_label',
            'link_%d'
        );

        return $this->populateNumberedLinkSlots(
            $normalizedItem,
            $sourceItem['children'] ?? null,
            $collection,
            'child_%d_label',
            'child_%d_link'
        );
    }

    /**
     * @param array<string, mixed> $normalizedItem
     * @param array<string, mixed> $collection
     * @return array<string, mixed>
     */
    private function populateNumberedLinkSlots(
        array $normalizedItem,
        mixed $sourceLinks,
        array $collection,
        string $labelPattern,
        string $linkPattern,
    ): array {
        if ($sourceLinks === null || $sourceLinks === '') {
            return $normalizedItem;
        }

        if (is_string($sourceLinks)) {
            $splitLinks = preg_split('/\R/', $sourceLinks);
            $sourceLinks = is_array($splitLinks) ? $splitLinks : [];
        }

        if (!is_array($sourceLinks)) {
            return $normalizedItem;
        }

        $slot = 1;
        foreach ($sourceLinks as $sourceLink) {
            $labelField = sprintf($labelPattern, $slot);
            $linkField = sprintf($linkPattern, $slot);
            if (!$this->collectionHasField($collection, $labelField) && !$this->collectionHasField($collection, $linkField)) {
                break;
            }

            [$label, $link] = $this->normalizeLinkFixture($sourceLink);
            if ($label !== '' && $this->collectionHasField($collection, $labelField) && $this->isEmptySeedValue($normalizedItem[$labelField] ?? null)) {
                $normalizedItem[$labelField] = $label;
            }
            if ($link !== '' && $this->collectionHasField($collection, $linkField) && $this->isEmptySeedValue($normalizedItem[$linkField] ?? null)) {
                $normalizedItem[$linkField] = $link;
            }

            $slot++;
        }

        return $normalizedItem;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function normalizeLinkFixture(mixed $sourceLink): array
    {
        if (is_array($sourceLink)) {
            $label = trim((string)($sourceLink['label'] ?? $sourceLink['title'] ?? $sourceLink['text'] ?? $sourceLink['name'] ?? ''));
            $link = trim((string)($sourceLink['link'] ?? $sourceLink['url'] ?? $sourceLink['href'] ?? ''));

            return [$label, $link !== '' ? $link : $this->demoValueGenerator->buildDemoUrl($label)];
        }

        $value = trim((string)$sourceLink);
        if ($value === '') {
            return ['', ''];
        }

        if (str_contains($value, '|')) {
            [$label, $link] = array_pad(array_map('trim', explode('|', $value, 2)), 2, '');

            return [$label, $link !== '' ? $link : $this->demoValueGenerator->buildDemoUrl($label)];
        }

        return [$value, $this->demoValueGenerator->buildDemoUrl($value)];
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function collectionHasField(array $collection, string $field): bool
    {
        return isset($collection['fields'][$field])
            || $this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $field);
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
     * @return array<string, mixed>|null
     */
    private function getCollectionFieldConfig(array $collection, string $field): ?array
    {
        $fields = $collection['fields'] ?? [];
        if (!is_array($fields)) {
            return null;
        }

        $fieldConfig = $fields[$field] ?? null;
        if (!is_array($fieldConfig)) {
            return null;
        }

        return ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig);
    }

    /**
     * @param array{collections?: array<string, array<string, mixed>>} $collection
     */

    /**
     * @param array<int, mixed> $value
     */
    private function normalizeArrayForScalarField(array $value, string $field): mixed
    {
        if ($value === []) {
            return '';
        }

        if ($field === 'row_data' && !$this->containsNestedArray($value)) {
            return $this->formatFlatScalarList($value, '|');
        }

        if (!$this->containsNestedArray($value)) {
            return $this->formatFlatScalarList($value, "\n");
        }

        return self::FIELD_SKIP;
    }

    /**
     * @param array<int, mixed> $values
     */
    private function formatFlatScalarList(array $values, string $separator): string
    {
        $items = [];
        foreach ($values as $value) {
            $normalized = $this->normalizeScalarValue($value);
            if (is_scalar($normalized)) {
                $items[] = trim((string)$normalized);
            }
        }

        return implode($separator, $items);
    }

    /**
     * @param array<int, mixed> $value
     * @param array<string, mixed> $collection
     */
    private function normalizeArrayForCollectionField(array $value, string $field, array $collection): mixed
    {
        if ($value === []) {
            return '';
        }

        $fieldConfig = $this->getCollectionFieldConfig($collection, $field);
        if ($fieldConfig !== null && $this->isFileField($fieldConfig)) {
            return $value;
        }

        if ($field === 'row_data' && !$this->containsNestedArray($value)) {
            return $this->formatFlatScalarList($value, '|');
        }

        if (!$this->containsNestedArray($value)) {
            return $this->formatFlatScalarList($value, "\n");
        }

        return self::FIELD_SKIP;
    }

    /**
     * @param array<string, array{table: string, column?: string, items: list<array<string, mixed>>}> $collections
     */
    private function seedCollectionRecords(int $contentUid, int $pageUid, int $now, array $collections): void
    {
        foreach ($collections as $collection) {
            $table = $collection['table'];
            $columns = $this->databaseSchema->getColumnNames($table);
            $connection = $this->connectionPool->getConnectionForTable($table);

            foreach ($collection['items'] as $index => $item) {
                if ($item === []) {
                    continue;
                }

                $fileReferences = [];
                if (isset($item[self::FILE_REFERENCES_KEY]) && is_array($item[self::FILE_REFERENCES_KEY])) {
                    $fileReferences = $item[self::FILE_REFERENCES_KEY];
                    unset($item[self::FILE_REFERENCES_KEY]);
                }
                $nestedCollections = [];
                if (isset($item[self::NESTED_COLLECTIONS_KEY]) && is_array($item[self::NESTED_COLLECTIONS_KEY])) {
                    $nestedCollections = $this->normalizeCollectionPayloads($item[self::NESTED_COLLECTIONS_KEY]);
                    unset($item[self::NESTED_COLLECTIONS_KEY]);
                }

                $row = $this->databaseSchema->filterRow([
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
                $collectionRowUid = (int)$connection->lastInsertId();
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
                    $normalizedItems[] = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($item);
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
     * @param array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>> $fileReferences
     */
    private function seedFileReferences(string $table, int $uid, int $pid, int $now, array $fileReferences): void
    {
        if ($fileReferences === []) {
            return;
        }

        $columns = $this->databaseSchema->getColumnNames('sys_file_reference');
        $connection = $this->connectionPool->getConnectionForTable('sys_file_reference');

        foreach ($fileReferences as $fieldName => $references) {
            foreach ($references as $index => $reference) {
                $file = $this->ensureStyleguideFalFile($reference);
                if ($file === null) {
                    continue;
                }

                $row = $this->databaseSchema->filterRow([
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
                ], $columns);

                $connection->insert('sys_file_reference', $row);
            }
        }
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string} $reference
     */
    private function ensureStyleguideFalFile(array $reference): ?File
    {
        $relativeFilePath = $reference['file'];
        if (isset($this->styleguideFiles[$relativeFilePath])) {
            return $this->styleguideFiles[$relativeFilePath];
        }

        $sourcePath = GeneralUtility::getFileAbsFileName('EXT:desiderio/' . $relativeFilePath);
        if ($sourcePath === '' || !is_file($sourcePath)) {
            return null;
        }

        $folder = $this->getStyleguideFalFolder();
        $fileName = basename($relativeFilePath);
        $file = $folder->getFile($fileName);
        if (!$file instanceof File) {
            $file = $folder->addFile($sourcePath, $fileName);
        }

        $this->styleguideFiles[$relativeFilePath] = $file;
        $this->upsertFileMetadata($file, $reference);

        return $file;
    }

    private function getStyleguideFalFolder(): Folder
    {
        if ($this->styleguideFalFolder instanceof Folder) {
            return $this->styleguideFalFolder;
        }

        $storage = $this->storageRepository->getDefaultStorage();
        if ($storage === null) {
            throw new \RuntimeException('No default FAL storage is configured for Desiderio styleguide seeding.', 1777100143);
        }

        $rootFolder = $storage->getRootLevelFolder(false);
        $this->styleguideFalFolder = $rootFolder->hasFolder(self::STYLEGUIDE_FAL_FOLDER)
            ? $rootFolder->getSubfolder(self::STYLEGUIDE_FAL_FOLDER)
            : $rootFolder->createFolder(self::STYLEGUIDE_FAL_FOLDER);

        return $this->styleguideFalFolder;
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string} $reference
     */
    private function upsertFileMetadata(File $file, array $reference): void
    {
        $columns = $this->databaseSchema->getColumnNames('sys_file_metadata');
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

        $now = time();
        $row = $this->databaseSchema->filterRow([
            'file' => $file->getUid(),
            'pid' => 0,
            'tstamp' => $now,
            'crdate' => $now,
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
     * @param array<string, mixed> $fieldConfig
     */
    private function normalizeFieldValue(mixed $value, array $fieldConfig): mixed
    {
        $normalized = $this->normalizeScalarValue($value);
        $type = $fieldConfig['type'] ?? '';
        if (!is_string($type)) {
            $type = '';
        }

        if (in_array($type, ['Date', 'DateTime'], true)) {
            return $this->normalizeDateTimeFieldValue($normalized);
        }

        if ($type !== 'Select') {
            return $normalized;
        }

        return $this->normalizeSelectValue($normalized, $fieldConfig);
    }

    private function normalizeDateTimeFieldValue(mixed $value): int|string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int)$value;
        }

        if (is_string($value)) {
            $trimmedValue = trim($value);
            if ($trimmedValue === '') {
                return '';
            }

            if (preg_match('/^-?\d+$/', $trimmedValue) === 1) {
                return (int)$trimmedValue;
            }

            $timestamp = strtotime($trimmedValue);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function normalizeSelectValue(mixed $value, array $fieldConfig): mixed
    {
        if (!is_scalar($value)) {
            return $this->demoValueGenerator->buildDefaultSelectValue($fieldConfig);
        }

        if ($this->demoValueGenerator->usesIconItemsProcessor($fieldConfig)) {
            $normalizedIcon = IconRegistry::normalizeKey((string)$value);
            foreach ($this->demoValueGenerator->getSelectItemValues($fieldConfig) as $itemValue) {
                if ((string)$itemValue === $normalizedIcon) {
                    return $itemValue;
                }
            }
        }

        foreach ($this->demoValueGenerator->getSelectItemValues($fieldConfig) as $itemValue) {
            if ((string)$itemValue === (string)$value) {
                return $itemValue;
            }
        }

        return $this->demoValueGenerator->buildDefaultSelectValue($fieldConfig);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<scalar>
     */

    /**
     * @return list<string>
     */
    private function getCollectionTableNames(): array
    {
        $tables = [];
        foreach (ContentBlockDefinitionRegistry::getDefinitions() as $definition) {
            $this->collectCollectionTableNames($definition['collections'], $tables);
        }

        return array_keys($tables);
    }

    /**
     * @param array<int|string, mixed> $collections
     * @param array<string, true> $tables
     */
    private function collectCollectionTableNames(array $collections, array &$tables): void
    {
        foreach ($collections as $collection) {
            if (!is_array($collection)) {
                continue;
            }
            $table = $collection['table'] ?? null;
            if (is_string($table) && $table !== '') {
                $tables[$table] = true;
            }
            $nestedCollections = $collection['collections'] ?? [];
            if (is_array($nestedCollections)) {
                $this->collectCollectionTableNames($nestedCollections, $tables);
            }
        }
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
        foreach (ContentBlockDefinitionRegistry::getDefinitions() as $definition) {
            $this->collectCollectionsByParentTable('tt_content', $definition['collections'], $map);
        }

        $this->collectionsByParentTable = $map;
        return $map;
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function findPreferredTextField(array $collection): ?string
    {
        foreach (['label', 'title', 'name', 'feature_name', 'row_label', 'text', 'value', 'question', 'row_data', 'links', 'features_list', 'description'] as $candidate) {
            if (isset($collection['fields'][$candidate]) || $this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<int|string, mixed> $collections
     * @param array<string, list<array<string, mixed>>> $map
     */
    private function collectCollectionsByParentTable(string $parentTable, array $collections, array &$map): void
    {
        foreach ($collections as $collection) {
            if (!is_array($collection)) {
                continue;
            }

            $collectionConfig = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($collection);
            $table = $collectionConfig['table'] ?? null;
            if (!is_string($table) || $table === '') {
                continue;
            }

            $nestedCollections = $collectionConfig['collections'] ?? [];
            if (!is_array($nestedCollections)) {
                $nestedCollections = [];
            }

            $map[$parentTable][] = $collectionConfig;
            $this->collectCollectionsByParentTable($table, $nestedCollections, $map);
        }
    }

    private function normalizeScalarValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if ($value === null) {
            return '';
        }

        return $value;
    }



    /**
     * @param array<string, mixed> $row
     */
    private function hasPayloadBeyondSystemFields(array $row): bool
    {
        $systemFields = [
            'pid' => true,
            'sorting' => true,
            'hidden' => true,
            'sys_language_uid' => true,
            'crdate' => true,
            'tstamp' => true,
            'foreign_table_parent_uid' => true,
        ];

        foreach ($row as $field => $_value) {
            if (!isset($systemFields[$field])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int|string, mixed> $value
     */
    private function isListOfScalars(array $value): bool
    {
        if (!array_is_list($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (is_array($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, mixed> $values
     */
    private function containsNestedArray(array $values): bool
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }


    private function singularize(string $value): string
    {
        return match (true) {
            str_ends_with($value, 'ies') => substr($value, 0, -3) . 'y',
            str_ends_with($value, 's') => substr($value, 0, -1),
            default => $value,
        };
    }

}
