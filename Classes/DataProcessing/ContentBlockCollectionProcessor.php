<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\DataProcessing;

use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\ContentBlocks\DataProcessing\ContentBlockData;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\LinkHandling\TypolinkParameter;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\ContentObject\DataProcessorInterface;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;

final class ContentBlockCollectionProcessor implements DataProcessorInterface
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly FileRepository $fileRepository,
        private readonly RecordFactory $recordFactory,
        private readonly DatabaseSchemaHelper $databaseSchema,
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
        $definition = ContentBlockDefinitionRegistry::getRuntimeCollectionDefinitions()[$ctype] ?? null;
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
        $definition = ContentBlockDefinitionRegistry::getRuntimeCollectionDefinitions()[$ctype] ?? null;
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
     * @param array<string, mixed> $collection
     * @return list<ContentBlockData>
     */
    private function loadCollectionItems(int $parentUid, array $collection): array
    {
        $table = $this->stringValue($collection['table'] ?? null);
        if ($table === '' || !$this->databaseSchema->tableHasColumn($table, 'foreign_table_parent_uid')) {
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

        if ($this->databaseSchema->tableHasColumn($table, 'sys_language_uid')) {
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
            foreach ($this->normalizeStringKeyArray($collection['fields'] ?? null) as $field => $fieldConfig) {
                if (!is_array($fieldConfig)) {
                    continue;
                }
                $type = $this->stringValue($fieldConfig['type'] ?? null);
                if ($type === 'File') {
                    // The column holds the reference count; only resolve file
                    // references when there is something to load. This avoids
                    // one sys_file_reference query per row and empty field.
                    $item[$field] = $this->intValue($row[$field] ?? null) > 0
                        ? $this->fileRepository->findByRelation($table, $field, $rowUid)
                        : [];
                } elseif ($type === 'Link' && isset($item[$field]) && is_scalar($item[$field])) {
                    $item[$field] = new TypolinkParameter((string)$item[$field]);
                }
            }

            foreach ($this->normalizeStringKeyArray($collection['collections'] ?? null) as $field => $nestedCollection) {
                if (!is_array($nestedCollection)) {
                    continue;
                }
                $item[$field] = $this->loadCollectionItems($rowUid, ContentBlockDefinitionRegistry::normalizeStringKeyedArray($nestedCollection));
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
     * @return array<string, mixed>
     */
    private function normalizeStringKeyArray(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $normalized[$key] = $item;
            }
        }

        return $normalized;
    }
}
