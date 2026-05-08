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
     * @param array<string, mixed> $contentObjectConfiguration
     * @param array<string, mixed> $processorConfiguration
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ): array {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        $data = $processedData['data'] ?? $cObj->data;
        if ($data instanceof ContentBlockData) {
            return $this->processContentBlockData($data, $processedData);
        }

        if (!is_array($data)) {
            return $processedData;
        }

        $ctype = (string)($data['CType'] ?? '');
        $definition = $this->getDefinitions()[$ctype] ?? null;
        if ($definition === null || $definition['collections'] === []) {
            return $processedData;
        }

        $uid = (int)($data['uid'] ?? 0);
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
     * @param array<string, mixed> $processedData
     * @return array<string, mixed>
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

            $config = Yaml::parseFile($configPath);
            if (!is_array($config)) {
                continue;
            }

            $ctype = (string)($config['typeName'] ?? '');
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
            if (!is_array($field)) {
                continue;
            }

            $identifier = (string)($field['identifier'] ?? '');
            if ($identifier === '' || (string)($field['type'] ?? '') !== 'Collection') {
                continue;
            }

            $collections[$identifier] = [
                'table' => (string)($field['table'] ?? $identifier),
                'fields' => $this->indexFields($field['fields'] ?? []),
                'collections' => $this->extractCollections($field['fields'] ?? []),
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
            if (!is_array($field)) {
                continue;
            }
            $identifier = (string)($field['identifier'] ?? '');
            if ($identifier !== '') {
                $indexed[$identifier] = $field;
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
        $table = (string)($collection['table'] ?? '');
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

        $rows = $queryBuilder->executeQuery()->fetchAllAssociative();
        $items = [];

        foreach ($rows as $row) {
            $item = $row;
            foreach (($collection['fields'] ?? []) as $field => $fieldConfig) {
                if (!is_string($field) || !is_array($fieldConfig)) {
                    continue;
                }
                $type = (string)($fieldConfig['type'] ?? '');
                if ($type === 'File') {
                    $item[$field] = $this->fileRepository->findByRelation($table, $field, (int)$row['uid']);
                } elseif ($type === 'Link' && isset($item[$field]) && is_scalar($item[$field])) {
                    $item[$field] = new TypolinkParameter((string)$item[$field]);
                }
            }

            foreach (($collection['collections'] ?? []) as $field => $nestedCollection) {
                if (is_string($field) && is_array($nestedCollection)) {
                    $item[$field] = $this->loadCollectionItems((int)$row['uid'], $nestedCollection);
                }
            }

            $record = $this->recordFactory->createResolvedRecordFromDatabaseRow($table, $row);
            if ($record instanceof Record) {
                $items[] = new ContentBlockData($record, 'desiderio/' . $table, null, $item);
            }
        }

        return $items;
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
