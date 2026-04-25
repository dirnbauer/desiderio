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

    /** @var array<string, array<string, true>> */
    private array $tableColumnsCache = [];

    /**
     * @var array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array{table: string, fields: array<string, array<string, mixed>>}>}>|null
     */
    private ?array $contentBlockDefinitions = null;

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
            $this->deleteCollectionRowsForParentUids($existingContentUids);
        }

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
    private function deleteCollectionRowsForParentUids(array $parentUids): void
    {
        foreach ($this->getCollectionTableNames() as $table) {
            if (!$this->tableHasColumn($table, 'foreign_table_parent_uid')) {
                continue;
            }

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
     * @param array<string, mixed> $fixture
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, items: list<array<string, mixed>>}>}
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
            'header' => $fixture['header'] ?? $name,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ];

        [$resolvedFields, $collections] = $this->resolveFixtureFields($ctype, $fixture);

        foreach ($resolvedFields as $field => $value) {
            $row[$field] = $value;
        }

        foreach ($collections as $field => $collection) {
            $row[$field] = count($collection['items']);
        }

        return [
            'row' => $this->filterRow($row, $columns),
            'collections' => $collections,
        ];
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, items: list<array<string, mixed>>}>}
     */
    private function resolveFixtureFields(string $ctype, array $fixture): array
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

            return [$row, []];
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
                        $resolvedFields[$scalarField] = $normalized;
                    }
                }
                continue;
            }

            $scalarField = $this->resolveScalarField($field, $definition['fields']);
            if ($scalarField === null) {
                continue;
            }
            $resolvedFields[$scalarField] = $this->normalizeScalarValue($value);
        }

        return [$resolvedFields, $collections];
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
     * @param array{collections: array<string, array{table: string, fields: array<string, array<string, mixed>>}>} $definition
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
     * @param array{table: string, fields: array<string, array<string, mixed>>} $collection
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
            foreach (['label', 'title', 'name', 'text', 'value', 'question', 'row_data', 'links', 'features_list'] as $candidate) {
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
                if ($this->resolveChildField((string)$itemKey, $item[(string)$itemKey], $collection) !== null) {
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
     * @param array{table: string, fields: array<string, array<string, mixed>>} $collection
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
                $definitions[] = [
                    'column_label' => $label,
                    'column_key' => $this->buildColumnKey($label),
                    'column_align' => 'left',
                ];
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
     * @param array{table: string, fields: array<string, array<string, mixed>>} $collection
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
                $targetField => $this->normalizeScalarValue($item),
            ];
        }

        if ($item === []) {
            return [];
        }

        $normalizedItem = [];
        foreach ($item as $field => $value) {
            $field = (string)$field;
            $resolvedField = $this->resolveChildField($field, $value, $collection);
            if ($resolvedField === null) {
                continue;
            }

            if (is_array($value)) {
                $normalized = $this->normalizeArrayForCollectionField($value, $resolvedField, $collection);
                if ($normalized === self::FIELD_SKIP) {
                    continue;
                }
                $normalizedItem[$resolvedField] = $normalized;
                continue;
            }

            $normalizedItem[$resolvedField] = $this->normalizeScalarValue($value);
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
     * @param array{table: string, fields: array<string, array<string, mixed>>} $collection
     */
    private function resolveChildField(string $field, mixed $value, array $collection): ?string
    {
        if (isset($collection['fields'][$field]) || $this->tableHasColumn($collection['table'], $field)) {
            return $field;
        }

        foreach ($this->getChildFieldAliases()[$field] ?? [] as $candidate) {
            if (isset($collection['fields'][$candidate]) || $this->tableHasColumn($collection['table'], $candidate)) {
                return $candidate;
            }
        }

        if ($field === 'title') {
            foreach (['label', 'name'] as $candidate) {
                if (isset($collection['fields'][$candidate]) || $this->tableHasColumn($collection['table'], $candidate)) {
                    return $candidate;
                }
            }
        }

        if (is_scalar($value) && $field === 'link') {
            foreach (['url', 'button_link'] as $candidate) {
                if (isset($collection['fields'][$candidate]) || $this->tableHasColumn($collection['table'], $candidate)) {
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
     * @param array{table: string, fields: array<string, array<string, mixed>>} $collection
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
            }
        }
    }

    /**
     * @return array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array{table: string, fields: array<string, array<string, mixed>>}>}>
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
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array{table: string, fields: array<string, array<string, mixed>>}>}
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

            $childFields = [];
            foreach (($field['fields'] ?? []) as $childField) {
                if (!is_array($childField) || !isset($childField['identifier'])) {
                    continue;
                }
                $childFields[(string)$childField['identifier']] = $childField;
            }

            $definition['collections'][$identifier] = [
                'table' => (string)($field['table'] ?? $field['foreign_table'] ?? $identifier),
                'fields' => $childFields,
            ];
        }

        return $definition;
    }

    /**
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array{table: string, fields: array<string, array<string, mixed>>}>}|null
     */
    private function getContentBlockDefinition(string $ctype): ?array
    {
        return $this->getContentBlockDefinitions()[$ctype] ?? null;
    }

    /**
     * @return list<string>
     */
    private function getCollectionTableNames(): array
    {
        $tables = [];
        foreach ($this->getContentBlockDefinitions() as $definition) {
            foreach ($definition['collections'] as $collection) {
                $tables[$collection['table']] = true;
            }
        }

        return array_keys($tables);
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        return isset($this->getColumnNames($table)[$column]);
    }

    /**
     * @param array{table: string, fields: array<string, array<string, mixed>>} $collection
     */
    private function findPreferredTextField(array $collection): ?string
    {
        foreach (['label', 'title', 'name', 'text', 'value', 'question', 'row_data', 'links', 'features_list', 'description'] as $candidate) {
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
            'featured' => ['is_featured', 'featured', 'highlighted', 'is_recommended'],
            'company' => ['company_name', 'affiliation', 'author_company'],
            'author' => ['author_name', 'name'],
            'role' => ['author_title', 'role', 'position'],
            'quote' => ['quote_text', 'quote'],
        ];
    }
}
