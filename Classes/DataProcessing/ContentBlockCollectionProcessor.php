<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\DataProcessing;

use Doctrine\DBAL\ArrayParameterType;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockData;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;

final class ContentBlockCollectionProcessor implements DataProcessorInterface
{
    /**
     * @var array<string, array{collections: array<string, array<string, mixed>>}>|null
     */
    private ?array $definitions = null;

    /** @var array<string, array<string, true>> */
    private array $tableColumns = [];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly FileRepository $fileRepository,
        private readonly RecordFactory $recordFactory,
    ) {}

    /**
     * @param array<mixed> $contentObjectConfiguration
     * @param array<mixed> $processorConfiguration
     * @param array<mixed> $processedData
     * @return array<mixed>
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        $ifConfiguration = $processorConfiguration['if.'] ?? null;
        if (is_array($ifConfiguration) && !$cObj->checkIf($ifConfiguration)) {
            return $processedData;
        }

        $data = $processedData['data'] ?? $cObj->data;
        if ($data instanceof ContentBlockData) {
            return $this->processContentBlockData($data, $processedData);
        }

        if (!is_array($data)) {
            return $processedData;
        }

        $ctype = $this->stringValue($data['CType'] ?? null);
        $definition = $this->getDefinitions()[$ctype] ?? null;
        if ($definition === null || $definition['collections'] === []) {
            return $processedData;
        }

        $uid = $this->intValue($data['uid'] ?? null);
        if ($uid <= 0) {
            return $processedData;
        }

        foreach ($definition['collections'] as $field => $collection) {
            $items = $this->loadCollectionItems($uid, $collection);
            if ($items !== []) {
                $data[$field] = $items;
            }
        }

        $processedData['data'] = $data;
        return $processedData;
    }

    /**
     * @param array<mixed> $processedData
     * @return array<mixed>
     */
    private function processContentBlockData(ContentBlockData $data, array $processedData): array
    {
        $ctype = (string)$data->getRecordType();
        $definition = $this->getDefinitions()[$ctype] ?? null;
        if ($definition === null || $definition['collections'] === []) {
            return $processedData;
        }

        $values = [];
        foreach ($definition['collections'] as $field => $collection) {
            $items = $this->loadCollectionItems($data->getUid(), $collection);
            if ($items !== []) {
                $values[$field] = $items;
            }
        }

        if ($values !== []) {
            $this->overrideProcessedProperties($data, $values);
        }

        return $processedData;
    }

    /**
     * Content Blocks stores resolved fields in protected state. The collection
     * fallback deliberately replaces only the affected fields.
     *
     * @param array<string, mixed> $values
     */
    private function overrideProcessedProperties(ContentBlockData $data, array $values): void
    {
        $reflectionProperty = new \ReflectionProperty($data, '_processed');
        $processed = $reflectionProperty->getValue($data);
        if (!is_array($processed)) {
            $processed = [];
        }

        foreach ($values as $field => $value) {
            $processed[$field] = $value;
        }

        $reflectionProperty->setValue($data, $processed);
    }

    /**
     * @return array<string, array{collections: array<string, array<string, mixed>>}>
     */
    private function getDefinitions(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $basePath = GeneralUtility::getFileAbsFileName('EXT:desiderio/ContentBlocks/ContentElements');
        if ($basePath === '' || !is_dir($basePath)) {
            $this->definitions = [];
            return $this->definitions;
        }

        $definitions = [];
        $directories = scandir($basePath);
        if ($directories === false) {
            $this->definitions = [];
            return $this->definitions;
        }

        foreach ($directories as $directory) {
            if ($directory === '.' || $directory === '..') {
                continue;
            }
            $configPath = $basePath . '/' . $directory . '/config.yaml';
            if (!is_readable($configPath)) {
                continue;
            }

            $config = $this->normalizeStringKeyArray(Yaml::parseFile($configPath));
            if ($config === null) {
                continue;
            }

            $ctype = $this->stringValue($config['typeName'] ?? null);
            if ($ctype === '') {
                continue;
            }

            $definitions[$ctype] = [
                'collections' => $this->extractCollections($config['fields'] ?? []),
            ];
        }

        $this->definitions = $definitions;
        return $this->definitions;
    }

    /**
     * @param mixed $fields
     * @return array<string, array<string, mixed>>
     */
    private function extractCollections(mixed $fields): array
    {
        if (!is_array($fields)) {
            return [];
        }

        $collections = [];
        foreach ($fields as $field) {
            $fieldConfig = $this->normalizeStringKeyArray($field);
            if ($fieldConfig === null) {
                continue;
            }

            $identifier = $this->stringValue($fieldConfig['identifier'] ?? null);
            if ($identifier === '' || $this->stringValue($fieldConfig['type'] ?? null) !== 'Collection') {
                continue;
            }

            $table = $this->stringValue($fieldConfig['table'] ?? null);
            $collections[$identifier] = [
                'table' => $table !== '' ? $table : $identifier,
                'fields' => $this->indexFields($fieldConfig['fields'] ?? []),
                'collections' => $this->extractCollections($fieldConfig['fields'] ?? []),
            ];
        }

        return $collections;
    }

    /**
     * @param mixed $fields
     * @return array<string, array<string, mixed>>
     */
    private function indexFields(mixed $fields): array
    {
        if (!is_array($fields)) {
            return [];
        }

        $indexed = [];
        foreach ($fields as $field) {
            $fieldConfig = $this->normalizeStringKeyArray($field);
            if ($fieldConfig === null) {
                continue;
            }
            $identifier = $this->stringValue($fieldConfig['identifier'] ?? null);
            if ($identifier !== '') {
                $indexed[$identifier] = $fieldConfig;
            }
        }

        return $indexed;
    }

    /**
     * @param array<string, mixed> $collection
     * @return list<ContentBlockData>
     */
    private function loadCollectionItems(int $parentUid, array $collection): array
    {
        $table = $this->stringValue($collection['table'] ?? null);
        if ($table === '' || !$this->tableHasColumn($table, 'foreign_table_parent_uid')) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder
            ->select('*')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq(
                    'foreign_table_parent_uid',
                    $queryBuilder->createNamedParameter($parentUid)
                )
            )
            ->orderBy('sorting', 'ASC');

        if ($this->tableHasColumn($table, 'sys_language_uid')) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->in(
                    'sys_language_uid',
                    $queryBuilder->createNamedParameter([-1, 0], ArrayParameterType::INTEGER)
                )
            );
        }

        /** @var list<array<string, mixed>> $rows */
        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();
        $items = [];

        foreach ($rows as $row) {
            $rowUid = $this->intValue($row['uid'] ?? null);
            $item = $row;
            foreach ($this->normalizeStringKeyArrayMap($collection['fields'] ?? null) as $field => $fieldConfig) {
                $type = $this->stringValue($fieldConfig['type'] ?? null);
                if ($type === 'File') {
                    $item[$field] = $this->fileRepository->findByRelation($table, $field, $rowUid);
                } elseif ($type === 'Link' && isset($item[$field]) && is_scalar($item[$field])) {
                    $item[$field] = new TypolinkParameter((string)$item[$field]);
                }
            }

            foreach ($this->normalizeStringKeyArrayMap($collection['collections'] ?? null) as $field => $nestedCollection) {
                $item[$field] = $this->loadCollectionItems($rowUid, $nestedCollection);
            }

            $record = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $row);
            if ($record instanceof Record) {
                $items[] = new ContentBlockData($record, 'desiderio/' . $table, null, $item);
            }
        }

        return $items;
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }

    private function intValue(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int)$value;
        }
        return 0;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function normalizeStringKeyArray(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $normalized[$key] = $item;
            }
        }

        return $normalized;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function normalizeStringKeyArrayMap(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $map = [];
        foreach ($value as $key => $item) {
            $normalized = $this->normalizeStringKeyArray($item);
            if (is_string($key) && $normalized !== null) {
                $map[$key] = $normalized;
            }
        }

        return $map;
    }

    private function tableHasColumn(string $table, string $column): bool
    {
        if (!isset($this->tableColumns[$table])) {
            $columns = [];
            foreach ($this->connectionPool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table) as $tableColumn) {
                $columns[$tableColumn->getName()] = true;
            }
            $this->tableColumns[$table] = $columns;
        }

        return isset($this->tableColumns[$table][$column]);
    }
}
