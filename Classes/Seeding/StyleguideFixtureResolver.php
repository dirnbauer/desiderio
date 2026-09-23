<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class StyleguideFixtureResolver
{
    public const string FIELD_SKIP = '__skip__';

    /**
     * Native tt_content FAL columns. For core CTypes (no Content Block
     * definition) an array fixture value on one of these is turned into real
     * sys_file_reference rows instead of being dropped.
     *
     * @var list<string>
     */
    private const array NATIVE_FILE_COLUMNS = ['assets', 'image', 'media'];

    /**
     * Set only by the element library seeder. When present, file fields are
     * resolved by semantic role instead of by hashing the field name into one
     * shared photo pool - see LibraryImageAssetProvider for why. Left null for
     * the styleguide, whose fixtures name their own files anyway and whose
     * output must not change.
     */
    private ?LibraryImageAssetProvider $imageAssetProvider = null;

    private readonly CollectionSchema $schema;

    private readonly FixtureLinkSlots $linkSlots;

    public function __construct(
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly StyleguideDemoValueGenerator $demoValueGenerator,
        private readonly StyleguideCollectionAliasPolicy $collectionAliasPolicy,
        private readonly FixtureFieldNormalizer $fieldNormalizer = new FixtureFieldNormalizer(),
    ) {
        $this->schema = new CollectionSchema($databaseSchema);
        $this->linkSlots = new FixtureLinkSlots($this->schema, $this->fieldNormalizer, $demoValueGenerator);
    }

    public function useRoleBasedImageAssets(): void
    {
        $this->imageAssetProvider = new LibraryImageAssetProvider();
    }

    /**
     * @param array<string, mixed> $fixture
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    public function buildContentInsert(
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
    public function resolveFixtureFields(string $ctype, array $fixture, string $name = ''): array
    {
        $definition = ContentBlockDefinitionRegistry::getDefinition($ctype);
        if ($definition === null) {
            // Native CType (core content element): copy scalar fixture keys
            // straight into their tt_content columns; route the known native FAL
            // columns (assets/image/media) to real file references; drop any other
            // nested structure we can't map without a definition.
            $row = [];
            $fileReferences = [];
            foreach ($fixture as $field => $value) {
                $field = (string)$field;
                if ($field === '_type' || $field === 'CType' || $field === 'ctype') {
                    continue;
                }
                if (in_array($field, self::NATIVE_FILE_COLUMNS, true)) {
                    $references = $this->buildFileReferenceFixturesFromFixtureValue($value, []);
                    if ($references !== []) {
                        $fileReferences[$field] = $references;
                    }
                    continue;
                }
                if (is_array($value)) {
                    continue;
                }
                $row[$field] = $this->fieldNormalizer->normalizeScalarValue($value);
            }

            return [$row, [], $fileReferences];
        }

        $resolvedFields = [];
        $collections = [];

        foreach ($fixture as $field => $value) {
            $field = (string)$field;
            if ($field === '_type' || $field === 'CType' || $field === 'ctype') {
                continue;
            }

            if (is_array($value)) {
                $collectionField = $this->resolveCollectionField($field, $value, $definition, $fixture);
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
    public function completeResolvedFixtureData(
        string $ctype,
        string $name,
        array $definition,
        array $resolvedFields,
        array $collections,
        array $fixture = [],
    ): array {
        $fileReferences = [];

        foreach ($definition['fields'] as $field => $fieldConfig) {
            if ($this->fieldNormalizer->isFileField($fieldConfig)) {
                $explicitReferences = $this->buildFileReferenceFixturesFromFixtureValue($fixture[$field] ?? null, $fieldConfig);
                unset($resolvedFields[$field]);
                $fileReferences[$field] = $explicitReferences !== []
                    ? $explicitReferences
                    : $this->buildFileReferenceFixtures($name . '-' . $field, $fieldConfig, 0);
                continue;
            }

            if (!array_key_exists($field, $resolvedFields) || $this->fieldNormalizer->isEmptySeedValue($resolvedFields[$field])) {
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

        return [
            $this->mapResolvedFieldsToStorageIdentifiers($resolvedFields, $definition['fields']),
            $collections,
            $this->mapFileReferencesToStorageIdentifiers($fileReferences, $definition['fields']),
        ];
    }

    /**
     * @param array<string, mixed> $resolvedFields
     * @param array<string, array<string, mixed>> $fieldDefinitions
     * @return array<string, mixed>
     */
    private function mapResolvedFieldsToStorageIdentifiers(array $resolvedFields, array $fieldDefinitions): array
    {
        $mappedFields = [];
        foreach ($resolvedFields as $field => $value) {
            $field = (string)$field;
            $mappedFields[$this->resolveFieldStorageIdentifier($field, $fieldDefinitions[$field] ?? [])] = $value;
        }
        return $mappedFields;
    }

    /**
     * @param array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>> $fileReferences
     * @param array<string, array<string, mixed>> $fieldDefinitions
     * @return array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>
     */
    private function mapFileReferencesToStorageIdentifiers(array $fileReferences, array $fieldDefinitions): array
    {
        $mappedReferences = [];
        foreach ($fileReferences as $field => $references) {
            $field = (string)$field;
            $mappedReferences[$this->resolveFieldStorageIdentifier($field, $fieldDefinitions[$field] ?? [])] = $references;
        }
        return $mappedReferences;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function resolveFieldStorageIdentifier(string $field, array $fieldConfig): string
    {
        $storageIdentifier = $fieldConfig['storageIdentifier'] ?? null;
        return is_string($storageIdentifier) && $storageIdentifier !== '' ? $storageIdentifier : $field;
    }

    /**
     * @param array<string, mixed> $collection
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function completeCollectionItem(
        string $ctype,
        string $name,
        string $collectionField,
        array $collection,
        array $item,
        int $index,
    ): array {
        $fileReferences = [];
        $nestedCollections = [];
        if (isset($item[SeedingPayloadKeys::NESTED_COLLECTIONS]) && is_array($item[SeedingPayloadKeys::NESTED_COLLECTIONS])) {
            $nestedCollections = $item[SeedingPayloadKeys::NESTED_COLLECTIONS];
            unset($item[SeedingPayloadKeys::NESTED_COLLECTIONS]);
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
            if ($this->fieldNormalizer->isFileField($fieldConfig)) {
                $explicitReferences = $this->buildFileReferenceFixturesFromFixtureValue($item[$field] ?? null, $fieldConfig);
                unset($item[$field]);
                $fileReferences[$field] = $explicitReferences !== []
                    ? $explicitReferences
                    : $this->buildFileReferenceFixtures($name . '-' . $collectionField . '-' . $field, $fieldConfig, $index);
                $item[$field] = count($fileReferences[$field]);
                continue;
            }

            if (!array_key_exists($field, $item) || $this->fieldNormalizer->isEmptySeedValue($item[$field])) {
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
            $item[SeedingPayloadKeys::FILE_REFERENCES] = $fileReferences;
        }
        if ($nestedCollections !== []) {
            $item[SeedingPayloadKeys::NESTED_COLLECTIONS] = $nestedCollections;
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function getTargetCollectionItemCount(array $collection, int $existingItemCount): int
    {
        $minimum = max(1, is_int($collection['minItems'] ?? null) ? $collection['minItems'] : 1);
        // Items the fixture lists are what its author wanted: two app-store
        // badges stay two. Only a collection the fixture leaves empty is
        // filled to three, so the element still shows as a list.
        $target = $existingItemCount > 0
            ? max($minimum, $existingItemCount)
            : max(3, $minimum);
        $maximum = $collection['maxItems'] ?? null;

        if (is_int($maximum)) {
            $target = min($target, max(1, $maximum));
        }

        return $target;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    public function buildFileReferenceFixtures(string $field, array $fieldConfig, int $index): array
    {
        $maxItems = ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxitems')
            ?? ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxItems')
            ?? 1;
        $count = max(1, min(3, $maxItems));

        if ($this->imageAssetProvider !== null) {
            return $this->imageAssetProvider->references($field, $fieldConfig, $index, $count);
        }

        $assets = $this->isAudioFileField($field, $fieldConfig)
            ? StyleguideDemoAssets::seederAudioAssets()
            : (StyleguidePortraitAssets::isPortraitField($field, $fieldConfig)
                ? StyleguideDemoAssets::seederPortraitAssets()
                : StyleguideDemoAssets::seederImageAssets());
        $references = [];

        for ($offset = 0; $offset < $count; $offset++) {
            $assetIndex = abs(crc32($field . ':' . ($index + $offset))) % count($assets);
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
        return $this->fieldNormalizer->buildFileReferenceFixturesFromFixtureValue($value, $fieldConfig, 'Styleguide image');
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
     * The collection a fixture key feeds: its own identifier, the one legacy
     * rename, or - for keys the definition does not know - the best fuzzy match.
     *
     * The fuzzy match is only a fallback for legacy keys, so it never applies
     * to a key that names a declared field or to a collection whose own key is
     * in the fixture. A root File field is the case that broke: its fixture
     * value is a list of file objects ({file, alternative, title}), `title`
     * matches the collection's `title` column, and with a single collection
     * nothing competes - so the image entry replaced the real items and the
     * seeder padded the lone empty row with generated filler.
     *
     * @param array<int|string, mixed> $value
     * @param array{fields?: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $definition
     * @param array<int|string, mixed> $fixture
     */
    private function resolveCollectionField(string $field, array $value, array $definition, array $fixture = []): ?string
    {
        if (isset($definition['collections'][$field])) {
            return $field;
        }

        if (isset($definition['fields'][$field])) {
            return null;
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

        $ownItems = $bestField !== null ? ($fixture[$bestField] ?? null) : null;
        if (is_array($ownItems) && $ownItems !== []) {
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
                if (isset($collection['fields'][$candidate]) || $this->databaseSchema->tableHasColumn($this->schema->table($collection), $candidate)) {
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

            $fieldConfig = $this->schema->fieldConfig($collection, $targetField);

            return [
                $targetField => $fieldConfig !== null
                    ? $this->normalizeFieldValue($item, $fieldConfig)
                    : $this->fieldNormalizer->normalizeScalarValue($item),
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
                    $normalizedItem[SeedingPayloadKeys::NESTED_COLLECTIONS][$nestedCollectionField] = [
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
                $fieldConfig = $this->schema->fieldConfig($collection, $resolvedField);
                if (is_array($normalized) && $fieldConfig !== null && $this->fieldNormalizer->isFileField($fieldConfig)) {
                    // Keep explicit file fixtures (file/title/alternative/…) in their
                    // array shape: completeCollectionItem() turns them into
                    // sys_file_reference rows. normalizeFieldValue() would collapse
                    // the array to '' and silently fall back to the demo image pool.
                    $normalizedItem[$resolvedField] = $normalized;
                    continue;
                }
                $normalizedItem[$resolvedField] = isset($collection['fields'][$resolvedField])
                    ? $this->normalizeFieldValue($normalized, $collection['fields'][$resolvedField])
                    : $normalized;
                continue;
            }

            $normalizedItem[$resolvedField] = isset($collection['fields'][$resolvedField])
                ? $this->normalizeFieldValue($value, $collection['fields'][$resolvedField])
                : $this->fieldNormalizer->normalizeScalarValue($value);
        }

        $normalizedItem = $this->linkSlots->populate($normalizedItem, $item, $collection);

        if ($normalizedItem === [] && $this->collectionAliasPolicy->collectionHasNestedCollection($collection, 'cells')) {
            $values = array_values($item);
            if (!$this->fieldNormalizer->containsNestedArray($values)) {
                $cellItems = $this->normalizeCollectionItems($values, $collection['collections']['cells']);
                if ($cellItems !== []) {
                    if (isset($collection['fields']['row_label'])) {
                        $normalizedItem['row_label'] = $this->fieldNormalizer->normalizeScalarValue($values[0] ?? '');
                    }
                    $normalizedItem[SeedingPayloadKeys::NESTED_COLLECTIONS]['cells'] = [
                        'table' => $collection['collections']['cells']['table'],
                        'items' => $cellItems,
                    ];
                    $normalizedItem['cells'] = count($cellItems);
                }
            }
        }

        if ($normalizedItem === [] && ($this->databaseSchema->tableHasColumn($this->schema->table($collection), 'row_data') || isset($collection['fields']['row_data']))) {
            $values = array_values($item);
            if (!$this->fieldNormalizer->containsNestedArray($values)) {
                $normalizedItem['row_data'] = implode('|', array_map(static fn(mixed $value): string => trim((string)$value), $values));
                foreach ($values as $index => $value) {
                    $columnName = 'col' . ($index + 1);
                    if ($this->databaseSchema->tableHasColumn($this->schema->table($collection), $columnName)) {
                        $normalizedItem[$columnName] = $this->fieldNormalizer->normalizeScalarValue($value);
                    }
                }
            }
        }

        return $normalizedItem;
    }

    /**
     * @param array<int, mixed> $value
     */
    private function normalizeArrayForScalarField(array $value, string $field): mixed
    {
        if ($value === []) {
            return '';
        }

        if ($field === 'row_data' && !$this->fieldNormalizer->containsNestedArray($value)) {
            return $this->fieldNormalizer->formatFlatScalarList($value, '|');
        }

        if (!$this->fieldNormalizer->containsNestedArray($value)) {
            return $this->fieldNormalizer->formatFlatScalarList($value, "\n");
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

        $fieldConfig = $this->schema->fieldConfig($collection, $field);
        if ($fieldConfig !== null && $this->fieldNormalizer->isFileField($fieldConfig)) {
            return $value;
        }

        if ($field === 'row_data' && !$this->fieldNormalizer->containsNestedArray($value)) {
            return $this->fieldNormalizer->formatFlatScalarList($value, '|');
        }

        if (!$this->fieldNormalizer->containsNestedArray($value)) {
            return $this->fieldNormalizer->formatFlatScalarList($value, "\n");
        }

        return self::FIELD_SKIP;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function normalizeFieldValue(mixed $value, array $fieldConfig): mixed
    {
        $normalized = $this->fieldNormalizer->normalizeScalarValue($value);
        $type = $fieldConfig['type'] ?? '';
        if (!is_string($type)) {
            $type = '';
        }

        if (in_array($type, ['Date', 'DateTime'], true)) {
            return $this->fieldNormalizer->normalizeDateTimeFieldValue($normalized);
        }

        if ($type !== 'Select') {
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
     * @param array<string, mixed> $collection
     */
    private function findPreferredTextField(array $collection): ?string
    {
        foreach (['label', 'title', 'name', 'feature_name', 'row_label', 'text', 'value', 'question', 'row_data', 'links', 'features_list', 'description'] as $candidate) {
            if (isset($collection['fields'][$candidate]) || $this->databaseSchema->tableHasColumn($this->schema->table($collection), $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<int|string, mixed> $value
     */
    private function isListOfScalars(array $value): bool
    {
        if (!array_is_list($value)) {
            return false;
        }
        return array_all($value, fn($item) => !is_array($item));
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
