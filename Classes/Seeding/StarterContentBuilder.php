<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;

/**
 * Turns one starter-site block definition into the rows the seeder inserts:
 * the tt_content row, its collection children and its file references.
 */
final readonly class StarterContentBuilder
{
    private CollectionSchema $schema;

    public function __construct(
        private DatabaseSchemaHelper $databaseSchema,
        private FixtureFieldNormalizer $fieldNormalizer = new FixtureFieldNormalizer(),
    ) {
        $this->schema = new CollectionSchema($databaseSchema);
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
     * @param array{ctype: string, colPos: int, fields: array<string, mixed>} $block
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    public function buildContentInsert(int $pid, array $block, int $sorting, int $now, array $columns): array
    {
        $ctype = $block['ctype'];
        $fixture = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($block['fields']);
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
            $row[$collection['column']] = count($collection['items']);
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
    private function resolveFixtureFields(string $ctype, array $fixture): array
    {
        $definition = ContentBlockDefinitionRegistry::getDefinition($ctype);
        if ($definition === null) {
            $row = [];
            foreach ($fixture as $field => $value) {
                if (!is_array($value)) {
                    $row[$field] = $this->fieldNormalizer->normalizeScalarValue($value);
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
                        'table' => $this->schema->table($collection),
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

            $storageField = $this->resolveFieldStorageIdentifier($field, $fieldConfig);
            if ($this->fieldNormalizer->isFileField($fieldConfig)) {
                $references = $this->fieldNormalizer->buildFileReferenceFixturesFromFixtureValue($value, [], 'Starter asset');
                if ($references !== []) {
                    $fileReferences[$storageField] = $references;
                }
                continue;
            }

            $resolvedFields[$storageField] = is_array($value)
                ? $this->fieldNormalizer->normalizeStarterArrayForScalarField($value)
                : $this->fieldNormalizer->normalizeStarterFieldValue($value, $fieldConfig);
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
        foreach ($items as $index => $item) {
            $normalizedItem = $this->normalizeCollectionItem($item, $collection, (int)$index);
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
    private function normalizeCollectionItem(mixed $item, array $collection, int $index = 0): array
    {
        if (!is_array($item)) {
            $textField = $this->findPreferredTextField($collection);
            return $textField === null ? [] : [$textField => $this->fieldNormalizer->normalizeScalarValue($item)];
        }

        $item = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($item);
        $normalizedItem = [];
        $fileReferences = [];
        $nestedCollections = [];

        foreach ($item as $field => $value) {
            $nestedCollection = $this->getNestedCollection($collection, $field);
            if ($nestedCollection !== null && is_array($value)) {
                $items = $this->normalizeCollectionItems($value, $nestedCollection);
                if ($items !== []) {
                    $nestedCollections[$field] = [
                        'table' => $this->schema->table($nestedCollection),
                        'column' => $this->getCollectionColumn($nestedCollection, $field),
                        'items' => $items,
                    ];
                    $normalizedItem[$field] = count($items);
                }
                continue;
            }

            $fieldConfig = $this->schema->fieldConfig($collection, $field);
            if ($fieldConfig === null) {
                continue;
            }

            if ($this->fieldNormalizer->isFileField($fieldConfig)) {
                $references = $this->fieldNormalizer->buildFileReferenceFixturesFromFixtureValue($value, [], 'Starter asset');
                if ($references !== []) {
                    $fileReferences[$field] = $references;
                    $normalizedItem[$field] = count($references);
                }
                continue;
            }

            $normalizedItem[$field] = is_array($value)
                ? $this->fieldNormalizer->normalizeStarterArrayForScalarField($value)
                : $this->fieldNormalizer->normalizeStarterFieldValue($value, $fieldConfig);
        }

        $memberName = $this->fieldNormalizer->stringFromMixed($normalizedItem['name'] ?? $item['name'] ?? '');
        $collectionFields = $collection['fields'] ?? null;
        if (!is_array($collectionFields)) {
            $collectionFields = [];
        }
        foreach ($collectionFields as $fieldName => $fieldConfig) {
            if (!is_string($fieldName) || !is_array($fieldConfig) || isset($fileReferences[$fieldName])) {
                continue;
            }
            $fieldConfig = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig);
            if (!$this->fieldNormalizer->isFileField($fieldConfig) || !StyleguidePortraitAssets::isPortraitField($fieldName, $fieldConfig)) {
                continue;
            }

            $reference = StyleguidePortraitAssets::fileReferenceForMember($memberName, $index);
            if ($reference['file'] === '') {
                continue;
            }

            $fileReferences[$fieldName] = [$reference];
            $normalizedItem[$fieldName] = 1;
        }

        if ($fileReferences !== []) {
            $normalizedItem[SeedingPayloadKeys::FILE_REFERENCES] = $fileReferences;
        }
        if ($nestedCollections !== []) {
            $normalizedItem[SeedingPayloadKeys::NESTED_COLLECTIONS] = $nestedCollections;
        }

        return $normalizedItem;
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function findPreferredTextField(array $collection): ?string
    {
        foreach (['title', 'label', 'name', 'text', 'value', 'question', 'answer', 'description'] as $field) {
            if ($this->schema->fieldConfig($collection, $field) !== null) {
                return $field;
            }
        }

        return null;
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
        return is_array($nestedCollection) ? ContentBlockDefinitionRegistry::normalizeStringKeyedArray($nestedCollection) : null;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function resolveFieldStorageIdentifier(string $field, array $fieldConfig): string
    {
        $storageIdentifier = $fieldConfig['storageIdentifier'] ?? null;
        return is_string($storageIdentifier) && $storageIdentifier !== '' ? $storageIdentifier : $field;
    }
}
