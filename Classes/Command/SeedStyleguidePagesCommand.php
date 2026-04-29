<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ArrayParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;

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

    /** @var array<string, array<string, true>> */
    private array $tableColumnsCache = [];

    /**
     * @var array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}>|null
     */
    private ?array $contentBlockDefinitions = null;

    /** @var array<string, list<array<string, mixed>>>|null */
    private ?array $collectionsByParentTable = null;

    /** @var array<string, File> */
    private array $styleguideFiles = [];

    private ?Folder $styleguideFalFolder = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
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
                'Parent page uid for the generated content element test pages.',
                (string)self::DEFAULT_PARENT_PID
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Only print the planned pages and content element count.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentPid = (int)$input->getOption('parent');
        $dryRun = (bool)$input->getOption('dry-run');
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
            $io->success(sprintf(
                'Would create or update %d pages and %d content elements below page uid %d.',
                count($groups),
                $totalElements,
                $parentPid
            ));

            return self::SUCCESS;
        }

        $pageColumns = $this->getColumnNames('pages');
        $contentColumns = $this->getColumnNames('tt_content');
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

        $io->success(sprintf(
            'Created or updated %d styleguide pages (%d new) and inserted %d Desiderio content elements below page uid %d.',
            count($groups),
            $createdPages,
            $createdContentElements,
            $parentPid
        ));

        return self::SUCCESS;
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
        foreach ($this->connectionPool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table) as $column) {
            $columns[$column->getName()] = true;
        }

        $this->tableColumnsCache[$table] = $columns;
        return $columns;
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
        $title = 'Desiderio ' . $groupTitle;
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $existing = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentPid)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (is_array($existing) && isset($existing['uid'])) {
            return (int)$existing['uid'];
        }

        $row = $this->filterRow([
            'pid' => $parentPid,
            'title' => $title,
            'doktype' => 1,
            'slug' => '/desiderio-' . $groupId,
            'hidden' => 0,
            'sorting' => $sorting,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);

        $connection = $this->connectionPool->getConnectionForTable('pages');
        $connection->insert('pages', $row);
        $createdPages++;

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
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('desiderio_%'))
            )
            ->executeStatement();
    }

    private function deleteCollectionRowsForPage(int $pageUid): void
    {
        foreach ($this->getCollectionTableNames() as $table) {
            if (!$this->tableHasColumn($table, 'pid')) {
                continue;
            }

            $collectionUids = $this->findCollectionRowUidsByPid($table, $pageUid);
            $this->deleteFileReferencesForRecords($table, $collectionUids);

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $queryBuilder
                ->delete($table)
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid))
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
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('desiderio_%'))
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
            $table = (string)$collection['table'];
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
                    )
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
                )
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
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid))
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
                )
            )
            ->executeStatement();
    }

    /**
     * @param array<string, mixed> $fixture
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
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
            $row[$field] = count($collection['items']);
        }

        return [
            'row' => $this->filterRow($row, $columns),
            'collections' => $collections,
            'fileReferences' => $fileReferences,
        ];
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, items: list<array<string, mixed>>}>, 2: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function resolveFixtureFields(string $ctype, array $fixture, string $name = ''): array
    {
        $definition = $this->getContentBlockDefinition($ctype);
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
                        $collections[$collectionField] = [
                            'table' => $definition['collections'][$collectionField]['table'],
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
            $resolvedFields[$scalarField] = $this->normalizeFieldValue($value, $definition['fields'][$scalarField]);
        }

        return $this->completeResolvedFixtureData($ctype, $name !== '' ? $name : $ctype, $definition, $resolvedFields, $collections, $fixture);
    }

    /**
     * @param array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $definition
     * @param array<string, mixed> $resolvedFields
     * @param array<string, array{table: string, items: list<array<string, mixed>>}> $collections
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, items: list<array<string, mixed>>}>, 2: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
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
                unset($resolvedFields[$field]);
                $fileReferences[$field] = $this->buildFileReferenceFixtures($field, $fieldConfig, 0);
                continue;
            }

            if (!array_key_exists($field, $resolvedFields) || $this->isEmptySeedValue($resolvedFields[$field])) {
                $default = $this->buildFixtureBackedFieldValue($field, $fixture)
                    ?? $this->buildDefaultFieldValue($ctype, $name, $field, $fieldConfig, 0);
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
                $completedItem = $this->completeCollectionItem($ctype, $name, $field, $collection, $item, $index);
                if ($completedItem !== []) {
                    $items[] = $completedItem;
                }
            }

            if ($items !== []) {
                $collections[$field] = [
                    'table' => $collection['table'],
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

        foreach ($collection['fields'] as $field => $fieldConfig) {
            if ($this->isFileField($fieldConfig)) {
                unset($item[$field]);
                $fileReferences[$field] = $this->buildFileReferenceFixtures($collectionField . '-' . $field, $fieldConfig, $index);
                $item[$field] = count($fileReferences[$field]);
                continue;
            }

            if (!array_key_exists($field, $item) || $this->isEmptySeedValue($item[$field])) {
                $default = $this->buildDefaultFieldValue($ctype, $name, $field, $fieldConfig, $index);
                if ($default !== self::FIELD_SKIP) {
                    $item[$field] = $default;
                }
            }
        }

        foreach (($collection['collections'] ?? []) as $field => $nestedCollection) {
            $existingItems = $nestedCollections[$field]['items'] ?? [];
            $targetItemCount = $this->getTargetCollectionItemCount($nestedCollection, count($existingItems));
            $items = [];

            for ($nestedIndex = 0; $nestedIndex < $targetItemCount; $nestedIndex++) {
                $nestedItem = $existingItems[$nestedIndex] ?? [];
                $completedItem = $this->completeCollectionItem($ctype, $name, (string)$field, $nestedCollection, $nestedItem, $nestedIndex);
                if ($completedItem !== []) {
                    $items[] = $completedItem;
                }
            }

            if ($items !== []) {
                $nestedCollections[$field] = [
                    'table' => $nestedCollection['table'],
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
    private function buildDefaultFieldValue(string $ctype, string $name, string $field, array $fieldConfig, int $index): mixed
    {
        $type = (string)($fieldConfig['type'] ?? 'Textarea');

        return match ($type) {
            'Checkbox' => 1,
            'Date' => strtotime('2026-05-' . str_pad((string)min(28, $index + 1), 2, '0', STR_PAD_LEFT)) ?: time(),
            'DateTime' => strtotime('2026-05-' . str_pad((string)min(28, $index + 1), 2, '0', STR_PAD_LEFT) . ' 09:00:00') ?: time(),
            'File' => self::FIELD_SKIP,
            'Link' => $this->buildDefaultLinkValue($field, $index),
            'Number' => $this->buildDefaultNumberValue($field, $fieldConfig, $index),
            'Select' => $this->buildDefaultSelectValue($fieldConfig),
            default => $this->buildDefaultTextValue($ctype, $name, $field, $index),
        };
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function buildDefaultNumberValue(string $field, array $fieldConfig, int $index): int
    {
        if (isset($fieldConfig['default']) && is_numeric($fieldConfig['default'])) {
            return (int)$fieldConfig['default'];
        }

        $normalizedField = $this->normalizeIdentifier($field);

        return match (true) {
            str_contains($normalizedField, 'rating') => 5,
            str_contains($normalizedField, 'columns') => 3,
            str_contains($normalizedField, 'duration') => 45,
            str_contains($normalizedField, 'interval') => 5000,
            str_contains($normalizedField, 'percent') => 92,
            default => ($index + 1) * 10,
        };
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function buildDefaultSelectValue(array $fieldConfig): mixed
    {
        $itemValues = $this->getSelectItemValues($fieldConfig);

        if (array_key_exists('default', $fieldConfig) && $fieldConfig['default'] !== '') {
            if ($itemValues === []) {
                return $fieldConfig['default'];
            }

            foreach ($itemValues as $itemValue) {
                if ((string)$itemValue === (string)$fieldConfig['default']) {
                    return $itemValue;
                }
            }
        }

        if ($itemValues !== []) {
            return $itemValues[0];
        }

        return '';
    }

    /**
     * @param array<string, mixed> $fixture
     */
    private function buildFixtureBackedFieldValue(string $field, array $fixture): mixed
    {
        if ($field === 'chart_data') {
            return $this->buildChartDataJsonFromFixture($fixture);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $fixture
     */
    private function buildChartDataJsonFromFixture(array $fixture): ?string
    {
        $stats = $fixture['stats'] ?? null;
        if (!is_array($stats)) {
            return null;
        }

        $points = [];
        foreach ($stats as $stat) {
            if (!is_array($stat)) {
                continue;
            }

            $label = trim((string)($stat['label'] ?? $stat['title'] ?? $stat['name'] ?? ''));
            $value = $this->parseFixtureChartNumber($stat['value'] ?? $stat['amount'] ?? $stat['number'] ?? null);
            if ($label === '' || $value === null) {
                continue;
            }

            $points[] = [
                'label' => $label,
                'value' => $value,
            ];
        }

        if ($points === []) {
            return null;
        }

        $json = json_encode($points, JSON_UNESCAPED_SLASHES);

        return is_string($json) ? $json : null;
    }

    private function parseFixtureChartNumber(mixed $value): int|float|null
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (!is_scalar($value)) {
            return null;
        }

        $normalized = str_replace([',', ' '], '', (string)$value);
        if (!preg_match('/-?\d+(?:\.\d+)?/', $normalized, $matches)) {
            return null;
        }

        $number = (float)$matches[0];

        return floor($number) === $number ? (int)$number : $number;
    }

    private function buildDefaultTextValue(string $ctype, string $name, string $field, int $index): string
    {
        $normalizedField = $this->normalizeIdentifier($field);
        $subject = $this->buildDemoSubject($name, $index);
        $fieldLabel = $this->buildReadableLabel($field);

        return match (true) {
            preg_match('/^(?:link|child)(\d+)label$/', $normalizedField, $matches) === 1 => $this->getDefaultLinkLabel((int)$matches[1]),
            $normalizedField === 'header' || str_contains($normalizedField, 'headline') || str_contains($normalizedField, 'title') => $subject,
            str_contains($normalizedField, 'eyebrow') || str_contains($normalizedField, 'badge') || str_contains($normalizedField, 'kicker') || str_contains($normalizedField, 'status') => 'Styleguide',
            str_contains($normalizedField, 'alt') || str_contains($normalizedField, 'alternative') => 'Accessible demo image for ' . $subject . '.',
            str_contains($normalizedField, 'copyright') => 'Images are credited on their Unsplash file references.',
            str_contains($normalizedField, 'credit') || str_contains($normalizedField, 'source') || str_contains($normalizedField, 'photographer') => 'Photo source: Unsplash demo image with photographer credit stored on the file reference.',
            str_contains($normalizedField, 'description') || str_contains($normalizedField, 'subheadline') || str_contains($normalizedField, 'content') || str_contains($normalizedField, 'body') || str_contains($normalizedField, 'copy') => 'Complete demo content for ' . $subject . ' with shadcn inspired spacing, contrast, and content hierarchy.',
            str_contains($normalizedField, 'ctatext') || str_contains($normalizedField, 'buttontext') || str_contains($normalizedField, 'submittext') => 'Explore ' . $this->buildReadableLabel($name),
            str_contains($normalizedField, 'feature') || str_contains($normalizedField, 'points') || str_contains($normalizedField, 'specs') => "Fast onboarding\nAccessible components\nProduction ready styling",
            str_contains($normalizedField, 'links') || str_contains($normalizedField, 'pages') || str_contains($normalizedField, 'children') => "Overview\nDocs\nSupport",
            str_contains($normalizedField, 'members') || str_contains($normalizedField, 'people') => "Mara Weiss|Product Lead\nJonas Klein|Design Systems\nSofia Berg|Customer Success",
            $normalizedField === 'chartdata' || (str_contains($normalizedField, 'chart') && str_contains($normalizedField, 'data')) => '[{"label":"Jan","value":12},{"label":"Feb","value":18},{"label":"Mar","value":14},{"label":"Apr","value":24}]',
            str_contains($normalizedField, 'rowdata') => 'Starter|Active|99%',
            str_contains($normalizedField, 'tiervalues') => 'Included,Included,Priority',
            str_contains($normalizedField, 'columnkey') => $this->buildColumnKey($subject . ' ' . ($index + 1)),
            str_contains($normalizedField, 'columnlabel') => $fieldLabel . ' ' . ($index + 1),
            str_contains($normalizedField, 'align') => 'left',
            str_contains($normalizedField, 'name') || str_contains($normalizedField, 'author') => $subject,
            str_contains($normalizedField, 'role') || str_contains($normalizedField, 'position') => 'Product Strategist',
            str_contains($normalizedField, 'company') || str_contains($normalizedField, 'brand') => 'Desiderio Labs',
            str_contains($normalizedField, 'email') => 'hello@example.com',
            str_contains($normalizedField, 'phone') || str_contains($normalizedField, 'tel') => '+43 1 555 010' . ($index + 1),
            str_contains($normalizedField, 'address') || str_contains($normalizedField, 'location') => 'Vienna, Austria',
            str_contains($normalizedField, 'price') => '$' . (($index + 1) * 19),
            str_contains($normalizedField, 'period') || str_contains($normalizedField, 'billing') => '/month',
            str_contains($normalizedField, 'size') => '2.4 MB',
            str_contains($normalizedField, 'icon') => ['sparkles', 'shield-check', 'chart-no-axes-combined'][$index % 3],
            str_contains($normalizedField, 'color') => '#2563eb',
            default => $fieldLabel . ' for ' . $subject,
        };
    }

    private function buildDefaultLinkValue(string $field, int $index): string
    {
        $normalizedField = $this->normalizeIdentifier($field);
        if (preg_match('/^(?:link|child)(\d+)(?:link)?$/', $normalizedField, $matches) === 1) {
            return $this->buildDemoUrl($this->getDefaultLinkLabel((int)$matches[1]));
        }

        return 'https://example.com/desiderio/' . $this->buildColumnKey($field . '-' . ($index + 1));
    }

    private function getDefaultLinkLabel(int $slot): string
    {
        return ['Overview', 'Docs', 'Support', 'Pricing', 'Contact'][max(0, min(4, $slot - 1))];
    }

    private function buildDemoUrl(string $label): string
    {
        return 'https://example.com/desiderio/' . $this->buildColumnKey($label !== '' ? $label : 'link');
    }

    private function buildDemoSubject(string $name, int $index): string
    {
        $label = $this->buildReadableLabel($name);

        return $index > 0 ? $label . ' Item ' . ($index + 1) : $label;
    }

    private function buildReadableLabel(string $value): string
    {
        $value = preg_replace('/^desiderio[_-]?/', '', $value) ?? $value;
        $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value) ?? $value;
        $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? $value;
        $value = trim($value);

        return $value !== '' ? ucwords(strtolower($value)) : 'Demo';
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    private function buildFileReferenceFixtures(string $field, array $fieldConfig, int $index): array
    {
        $maxItems = $this->getConfiguredInteger($fieldConfig, 'maxitems')
            ?? $this->getConfiguredInteger($fieldConfig, 'maxItems')
            ?? 1;
        $count = max(1, min(3, $maxItems));
        $assets = $this->getStyleguideImageAssets();
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
     * @param array<string, array<string, mixed>> $fields
     */
    private function resolveScalarField(string $field, array $fields): ?string
    {
        if (isset($fields[$field])) {
            return $field;
        }

        foreach ($this->getScalarFieldAliases()[$field] ?? [] as $candidate) {
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
        $normalizedField = $this->normalizeIdentifier($field);
        $normalizedIdentifier = $this->normalizeIdentifier($identifier);

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
                if (isset($collection['fields'][$candidate]) || $this->tableHasColumn($collection['table'], $candidate)) {
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
                    $this->resolveNestedCollectionField((string)$itemKey, $item[(string)$itemKey], $collection) !== null
                    || $this->resolveChildField((string)$itemKey, $item[(string)$itemKey], $collection) !== null
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
                    $definition['column_key'] = $this->buildColumnKey($label);
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

            return [
                $targetField => isset($collection['fields'][$targetField])
                    ? $this->normalizeFieldValue($item, $collection['fields'][$targetField])
                    : $this->normalizeScalarValue($item),
            ];
        }

        if ($item === []) {
            return [];
        }

        $normalizedItem = [];
        foreach ($item as $field => $value) {
            $field = (string)$field;
            $nestedCollectionField = $this->resolveNestedCollectionField($field, $value, $collection);
            if ($nestedCollectionField !== null) {
                $items = $this->normalizeCollectionItems(
                    $this->normalizeCollectionSourceItems($value, $field),
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

            if ($this->shouldSkipLegacyStructuredListField($field, $collection)) {
                continue;
            }

            $resolvedField = $this->resolveChildField($field, $value, $collection);
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

        if ($normalizedItem === [] && $this->collectionHasNestedCollection($collection, 'cells')) {
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

        if ($normalizedItem === [] && ($this->tableHasColumn($collection['table'], 'row_data') || isset($collection['fields']['row_data']))) {
            $values = array_values($item);
            if (!$this->containsNestedArray($values)) {
                $normalizedItem['row_data'] = implode('|', array_map(static fn (mixed $value): string => trim((string)$value), $values));
                foreach ($values as $index => $value) {
                    $columnName = 'col' . ($index + 1);
                    if ($this->tableHasColumn($collection['table'], $columnName)) {
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
            $sourceLinks = preg_split('/\R/', $sourceLinks) ?: [];
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

            return [$label, $link !== '' ? $link : $this->buildDemoUrl($label)];
        }

        $value = trim((string)$sourceLink);
        if ($value === '') {
            return ['', ''];
        }

        if (str_contains($value, '|')) {
            [$label, $link] = array_pad(array_map('trim', explode('|', $value, 2)), 2, '');

            return [$label, $link !== '' ? $link : $this->buildDemoUrl($label)];
        }

        return [$value, $this->buildDemoUrl($value)];
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function collectionHasField(array $collection, string $field): bool
    {
        return isset($collection['fields'][$field]) || $this->tableHasColumn($collection['table'], $field);
    }

    /**
     * @param array{collections?: array<string, array<string, mixed>>} $collection
     */
    private function collectionHasNestedCollection(array $collection, string $field): bool
    {
        return isset($collection['collections'][$field]);
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function resolveNestedCollectionField(string $field, mixed $value, array $collection): ?string
    {
        if (!is_array($value) && !is_string($value)) {
            return null;
        }

        if ($this->collectionHasNestedCollection($collection, $field)) {
            return $field;
        }

        foreach ($this->getNestedCollectionFieldAliases()[$field] ?? [] as $candidate) {
            if ($this->collectionHasNestedCollection($collection, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @return array<int, mixed>
     */
    private function normalizeCollectionSourceItems(mixed $value, string $field = ''): array
    {
        if (is_string($value)) {
            $separator = match ($field) {
                'tier_values', 'values' => '/\s*,\s*/',
                'row_data', 'cells' => '/\s*\|\s*/',
                default => '/\R/',
            };

            return array_values(array_filter(
                preg_split($separator, $value) ?: [],
                static fn (mixed $item): bool => trim((string)$item) !== ''
            ));
        }

        if (is_array($value)) {
            return $value;
        }

        return [];
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function shouldSkipLegacyStructuredListField(string $field, array $collection): bool
    {
        if (
            in_array($field, ['links', 'children'], true)
            && (
                $this->collectionHasField($collection, 'link_1')
                || $this->collectionHasField($collection, 'link_1_label')
                || $this->collectionHasField($collection, 'child_1_link')
                || $this->collectionHasField($collection, 'child_1_label')
            )
        ) {
            return true;
        }

        foreach ($this->getNestedCollectionFieldAliases()[$field] ?? [] as $candidate) {
            if ($this->collectionHasNestedCollection($collection, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function resolveChildField(string $field, mixed $value, array $collection): ?string
    {
        if (isset($collection['fields'][$field])) {
            return $field;
        }

        foreach ($this->getChildFieldAliases()[$field] ?? [] as $candidate) {
            if (isset($collection['fields'][$candidate])) {
                return $candidate;
            }
        }

        if ($field === 'title') {
            foreach (['label', 'name'] as $candidate) {
                if (isset($collection['fields'][$candidate])) {
                    return $candidate;
                }
            }
        }

        if (is_scalar($value) && $field === 'link') {
            foreach (['url', 'button_link'] as $candidate) {
                if (isset($collection['fields'][$candidate])) {
                    return $candidate;
                }
            }
        }

        if ($this->tableHasColumn($collection['table'], $field)) {
            return $field;
        }

        foreach ($this->getChildFieldAliases()[$field] ?? [] as $candidate) {
            if ($this->tableHasColumn($collection['table'], $candidate)) {
                return $candidate;
            }
        }

        if ($field === 'title') {
            foreach (['label', 'name'] as $candidate) {
                if ($this->tableHasColumn($collection['table'], $candidate)) {
                    return $candidate;
                }
            }
        }

        if (is_scalar($value) && $field === 'link') {
            foreach (['url', 'button_link'] as $candidate) {
                if ($this->tableHasColumn($collection['table'], $candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * @param array<int, mixed> $value
     */
    private function normalizeArrayForScalarField(array $value, string $field): mixed
    {
        if ($value === []) {
            return '';
        }

        if ($field === 'row_data' && !$this->containsNestedArray($value)) {
            return implode('|', array_map(static fn (mixed $item): string => trim((string)$item), $value));
        }

        if (!$this->containsNestedArray($value)) {
            return implode("\n", array_map(static fn (mixed $item): string => trim((string)$item), $value));
        }

        return self::FIELD_SKIP;
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

        if ($field === 'row_data' && !$this->containsNestedArray($value)) {
            return implode('|', array_map(static fn (mixed $item): string => trim((string)$item), $value));
        }

        if (!$this->containsNestedArray($value)) {
            return implode("\n", array_map(static fn (mixed $item): string => trim((string)$item), $value));
        }

        return self::FIELD_SKIP;
    }

    /**
     * @param array<string, array{table: string, items: list<array<string, mixed>>}> $collections
     */
    private function seedCollectionRecords(int $contentUid, int $pageUid, int $now, array $collections): void
    {
        foreach ($collections as $collection) {
            $table = $collection['table'];
            $columns = $this->getColumnNames($table);
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
                    $nestedCollections = $item[self::NESTED_COLLECTIONS_KEY];
                    unset($item[self::NESTED_COLLECTIONS_KEY]);
                }

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
                $collectionRowUid = (int)$connection->lastInsertId();
                $this->seedFileReferences($table, $collectionRowUid, $pageUid, $now, $fileReferences);
                $this->seedCollectionRecords($collectionRowUid, $pageUid, $now, $nestedCollections);
            }
        }
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
                $file = $this->ensureStyleguideFalFile($reference);
                if ($file === null) {
                    continue;
                }

                $row = $this->filterRow([
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

        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storage = $storageRepository->getDefaultStorage();
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

        $now = time();
        $row = $this->filterRow([
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

            $definitions['desiderio_' . str_replace('-', '', $directory)] = $this->buildContentBlockDefinition($config);
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

        foreach (($config['fields'] ?? []) as $field) {
            if (!is_array($field) || !isset($field['identifier'])) {
                continue;
            }

            $identifier = (string)$field['identifier'];
            if (($field['type'] ?? '') !== 'Collection') {
                $definition['fields'][$identifier] = $field;
                continue;
            }

            $definition['collections'][$identifier] = $this->buildCollectionDefinition($field, $identifier);
        }

        return $definition;
    }

    /**
     * @param array<string, mixed> $field
     * @return array{table: string, fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>, minItems: int, maxItems: int|null}
     */
    private function buildCollectionDefinition(array $field, string $fallbackIdentifier): array
    {
        $childFields = [];
        $childCollections = [];

        foreach (($field['fields'] ?? []) as $childField) {
            if (!is_array($childField) || !isset($childField['identifier'])) {
                continue;
            }

            $childIdentifier = (string)$childField['identifier'];
            if (($childField['type'] ?? '') === 'Collection') {
                $childCollections[$childIdentifier] = $this->buildCollectionDefinition($childField, $childIdentifier);
                continue;
            }

            $childFields[$childIdentifier] = $childField;
        }

        return [
            'table' => $this->resolveCollectionTable($field, $fallbackIdentifier),
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
        $configuredTable = (string)($field['table'] ?? $field['foreign_table'] ?? $fallbackIdentifier);
        if ($configuredTable === $fallbackIdentifier || $this->tableExists($configuredTable)) {
            return $configuredTable;
        }

        // Some existing Content Blocks share identifiers like "plans" across
        // elements. Content Blocks keeps the shared physical table in those
        // cases, even when newer configs specify a table override. Seed into
        // the generated table that actually exists, then keep nested tables
        // element-specific.
        if ($this->tableExists($fallbackIdentifier)) {
            return $fallbackIdentifier;
        }

        return $configuredTable;
    }

    /**
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}|null
     */
    private function getContentBlockDefinition(string $ctype): ?array
    {
        return $this->getContentBlockDefinitions()[$ctype] ?? null;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function normalizeFieldValue(mixed $value, array $fieldConfig): mixed
    {
        $normalized = $this->normalizeScalarValue($value);

        if (($fieldConfig['type'] ?? '') !== 'Select') {
            return $normalized;
        }

        return $this->normalizeSelectValue($normalized, $fieldConfig);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function normalizeSelectValue(mixed $value, array $fieldConfig): mixed
    {
        if (!is_scalar($value)) {
            return $this->buildDefaultSelectValue($fieldConfig);
        }

        foreach ($this->getSelectItemValues($fieldConfig) as $itemValue) {
            if ((string)$itemValue === (string)$value) {
                return $itemValue;
            }
        }

        return $this->buildDefaultSelectValue($fieldConfig);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<mixed>
     */
    private function getSelectItemValues(array $fieldConfig): array
    {
        $values = [];

        foreach (($fieldConfig['items'] ?? []) as $item) {
            if (!is_array($item) || !array_key_exists('value', $item)) {
                continue;
            }

            $values[] = $item['value'];
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    private function getCollectionTableNames(): array
    {
        $tables = [];
        foreach ($this->getContentBlockDefinitions() as $definition) {
            $this->collectCollectionTableNames($definition['collections'], $tables);
        }

        return array_keys($tables);
    }

    /**
     * @param array<string, array<string, mixed>> $collections
     * @param array<string, true> $tables
     */
    private function collectCollectionTableNames(array $collections, array &$tables): void
    {
        foreach ($collections as $collection) {
            $tables[(string)$collection['table']] = true;
            $this->collectCollectionTableNames($collection['collections'] ?? [], $tables);
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
        foreach ($this->getContentBlockDefinitions() as $definition) {
            $this->collectCollectionsByParentTable('tt_content', $definition['collections'], $map);
        }

        $this->collectionsByParentTable = $map;
        return $map;
    }

    /**
     * @param array<string, array<string, mixed>> $collections
     * @param array<string, list<array<string, mixed>>> $map
     */
    private function collectCollectionsByParentTable(string $parentTable, array $collections, array &$map): void
    {
        foreach ($collections as $collection) {
            $map[$parentTable][] = $collection;
            $this->collectCollectionsByParentTable((string)$collection['table'], $collection['collections'] ?? [], $map);
        }
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return isset($this->getColumnNames($table)[$column]);
    }

    private function tableExists(string $table): bool
    {
        return $this->getColumnNames($table) !== [];
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function findPreferredTextField(array $collection): ?string
    {
        foreach (['label', 'title', 'name', 'feature_name', 'row_label', 'text', 'value', 'question', 'row_data', 'links', 'features_list', 'description'] as $candidate) {
            if (isset($collection['fields'][$candidate]) || $this->tableHasColumn($collection['table'], $candidate)) {
                return $candidate;
            }
        }

        return null;
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
     * @param array<string, true> $columns
     * @return array<string, mixed>
     */
    private function filterRow(array $row, array $columns): array
    {
        return array_filter(
            $row,
            static fn (string $column): bool => isset($columns[$column]),
            ARRAY_FILTER_USE_KEY
        );
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

    private function buildColumnKey(string $label): string
    {
        $key = strtolower(trim($label));
        $key = preg_replace('/[^a-z0-9]+/', '_', $key) ?? '';
        $key = trim($key, '_');

        return $key !== '' ? $key : 'column';
    }

    private function normalizeIdentifier(string $value): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '', $value) ?? '');
    }

    private function singularize(string $value): string
    {
        return match (true) {
            str_ends_with($value, 'ies') => substr($value, 0, -3) . 'y',
            str_ends_with($value, 's') => substr($value, 0, -1),
            default => $value,
        };
    }

    /**
     * @return array<string, list<string>>
     */
    private function getScalarFieldAliases(): array
    {
        return [
            'description' => ['description', 'subheadline', 'content', 'description_text', 'info_text', 'body', 'bodytext'],
            'content' => ['content', 'body', 'bodytext', 'subheadline'],
            'logo' => ['brand'],
            'badge' => ['badge_text', 'eyebrow', 'right_badge'],
            'primaryButton' => ['primary_button_text', 'button_text', 'left_button_text', 'cta_text', 'submit_text'],
            'secondaryButton' => ['secondary_button_text', 'right_button_text'],
            'author' => ['author_name', 'author'],
            'role' => ['author_title', 'role', 'position'],
            'company' => ['author_company', 'company_name', 'affiliation'],
            'quote' => ['quote_text', 'quote'],
            'copyright' => ['copyright', 'description_text', 'info_text'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function getChildFieldAliases(): array
    {
        return [
            'period' => ['billing_period', 'price_period'],
            'button' => ['button_text'],
            'features' => ['features_list'],
            'Feature' => ['feature_name', 'name', 'label'],
            'Capability' => ['feature_name', 'name', 'label'],
            'featured' => ['is_featured', 'featured', 'highlighted', 'is_recommended'],
            'company' => ['company_name', 'affiliation', 'author_company'],
            'author' => ['author_name', 'name'],
            'role' => ['author_title', 'role', 'position'],
            'quote' => ['quote_text', 'quote'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function getNestedCollectionFieldAliases(): array
    {
        return [
            'features_list' => ['features', 'feature_items'],
            'feature_list' => ['features', 'feature_items'],
            'features' => ['features', 'feature_items'],
            'specs_text' => ['specs'],
            'specs' => ['specs'],
            'members' => ['members'],
            'people' => ['people'],
            'pages' => ['pages'],
            'tier_values' => ['tier_values'],
            'values' => ['tier_values'],
            'row_data' => ['cells'],
            'cells' => ['cells'],
        ];
    }
}
