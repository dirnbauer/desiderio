<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;

/**
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 */
final class StarterContentBuilder
{
    public function __construct(
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly FixtureFieldNormalizer $fieldNormalizer = new FixtureFieldNormalizer(),
    ) {}

    public function getCollectionTable(array $collection): string
    {
        $table = $collection['table'] ?? '';
        return is_string($table) ? $table : '';
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function getCollectionColumn(array $collection, string $fallback): string
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
    public function resolveFixtureFields(string $ctype, array $fixture): array
    {
        $definition = ContentBlockDefinitionRegistry::getDefinition($ctype);
        if ($definition === null) {
            $row = [];
            foreach ($fixture as $field => $value) {
                if (!is_array($value)) {
                    $row[$field] = $this->normalizeScalarValue($value);
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
                        'table' => $this->getCollectionTable($collection),
                        'column' => $this->getCollectionColumn($collection, $field),
                        'items' => $items,
                    ];
                    if (($collection['relation'] ?? false) === true) {
                        $collections[$field]['relation'] = true;
                    }
                }
                continue;
            }

            $fieldConfig = $definition['fields'][$field] ?? null;
            if (!is_array($fieldConfig)) {
                continue;
            }

            if ($this->isFileField($fieldConfig)) {
                $references = $this->buildFileReferenceFixturesFromFixtureValue($value);
                if ($references !== []) {
                    $fileReferences[$field] = $references;
                }
                continue;
            }

            $resolvedFields[$field] = is_array($value)
                ? $this->normalizeArrayForScalarField($value)
                : $this->normalizeFieldValue($value, $fieldConfig);
        }

        return [$resolvedFields, $collections, $fileReferences];
    }

    /**
     * @param array<int|string, mixed> $items
     * @param array<string, mixed> $collection
     * @return list<array<string, mixed>>
     */
    public function normalizeCollectionItems(array $items, array $collection): array
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
    public function normalizeCollectionItem(mixed $item, array $collection, int $index = 0): array
    {
        if (!is_array($item)) {
            $textField = $this->findPreferredTextField($collection);
            return $textField === null ? [] : [$textField => $this->normalizeScalarValue($item)];
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
                        'table' => $this->getCollectionTable($nestedCollection),
                        'column' => $this->getCollectionColumn($nestedCollection, $field),
                        'items' => $items,
                    ];
                    $normalizedItem[$field] = count($items);
                }
                continue;
            }

            $fieldConfig = $this->getCollectionFieldConfig($collection, $field);
            if ($fieldConfig === null) {
                continue;
            }

            if ($this->isFileField($fieldConfig)) {
                $references = $this->buildFileReferenceFixturesFromFixtureValue($value);
                if ($references !== []) {
                    $fileReferences[$field] = $references;
                    $normalizedItem[$field] = count($references);
                }
                continue;
            }

            $normalizedItem[$field] = is_array($value)
                ? $this->normalizeArrayForScalarField($value)
                : $this->normalizeFieldValue($value, $fieldConfig);
        }

        $memberName = $this->stringFromMixed($normalizedItem['name'] ?? $item['name'] ?? '');
        $collectionFields = $collection['fields'] ?? null;
        if (!is_array($collectionFields)) {
            $collectionFields = [];
        }
        foreach ($collectionFields as $fieldName => $fieldConfig) {
            if (!is_string($fieldName) || !is_array($fieldConfig) || isset($fileReferences[$fieldName])) {
                continue;
            }
            $fieldConfig = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig);
            if (!$this->isFileField($fieldConfig) || !StyleguidePortraitAssets::isPortraitField($fieldName, $fieldConfig)) {
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
    public function findPreferredTextField(array $collection): ?string
    {
        foreach (['title', 'label', 'name', 'text', 'value', 'question', 'answer', 'description'] as $field) {
            if ($this->getCollectionFieldConfig($collection, $field) !== null) {
                return $field;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>|null
     */
    public function getCollectionFieldConfig(array $collection, string $field): ?array
    {
        $fields = $collection['fields'] ?? null;
        if (!is_array($fields)) {
            return null;
        }

        $fieldConfig = $fields[$field] ?? null;
        return is_array($fieldConfig) ? ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig) : null;
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>|null
     */
    public function getNestedCollection(array $collection, string $field): ?array
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
    public function normalizeFieldValue(mixed $value, array $fieldConfig): int|string
    {
        return $this->fieldNormalizer->normalizeStarterFieldValue($value, $fieldConfig);
    }

    /**
     * @param array<int|string, mixed> $value
     */
    public function normalizeArrayForScalarField(array $value): string
    {
        return $this->fieldNormalizer->normalizeStarterArrayForScalarField($value);
    }

    public function normalizeScalarValue(mixed $value): int|string
    {
        return $this->fieldNormalizer->normalizeScalarValue($value);
    }

    public function stringFromMixed(mixed $value): string
    {
        return $this->fieldNormalizer->stringFromMixed($value);
    }

    /**
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    public function buildFileReferenceFixturesFromFixtureValue(mixed $value): array
    {
        return $this->fieldNormalizer->buildFileReferenceFixturesFromFixtureValue($value, [], 'Starter asset');
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function isFileField(array $fieldConfig): bool
    {
        return $this->fieldNormalizer->isFileField($fieldConfig);
    }
}
