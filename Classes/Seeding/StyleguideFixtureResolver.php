<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class StyleguideFixtureResolver
{
    public const FIELD_SKIP = '__skip__';

    public function __construct(
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly StyleguideDemoValueGenerator $demoValueGenerator,
        private readonly StyleguideCollectionAliasPolicy $collectionAliasPolicy,
        private readonly FixtureFieldNormalizer $fieldNormalizer = new FixtureFieldNormalizer(),
    ) {}
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
    public function resolveFixtureFields(string $ctype, array $fixture, string $name = ''): array
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
    public function buildFileReferenceFixtures(string $field, array $fieldConfig, int $index): array
    {
        $maxItems = ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxitems')
            ?? ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxItems')
            ?? 1;
        $count = max(1, min(3, $maxItems));
        $assets = $this->isAudioFileField($field, $fieldConfig)
            ? $this->getStyleguideAudioAssets()
            : (StyleguidePortraitAssets::isPortraitField($field, $fieldConfig)
                ? $this->getStyleguidePortraitAssets()
                : $this->getStyleguideImageAssets());
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
    public function buildFileReferenceFixturesFromFixtureValue(mixed $value, array $fieldConfig): array
    {
        return $this->fieldNormalizer->buildFileReferenceFixturesFromFixtureValue($value, $fieldConfig, 'Styleguide image');
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function isAudioFileField(string $field, array $fieldConfig): bool
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
    public function getStyleguideAudioAssets(): array
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
    public function getStyleguidePortraitAssets(): array
    {
        $assets = [];
        foreach (StyleguidePortraitAssets::teamGridPortraitFiles() as $index => $file) {
            $reference = StyleguidePortraitAssets::fileReferenceForIndex($index);
            if ($reference['file'] === '') {
                continue;
            }

            $assets[] = [
                'file' => $reference['file'],
                'title' => $reference['title'],
                'alt' => $reference['alternative'],
                'credit' => $reference['description'],
                'source' => $reference['source'],
            ];
        }

        return $assets !== [] ? $assets : $this->getStyleguideImageAssets();
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    public function getStyleguideImageAssets(): array
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
    public function isFileField(array $fieldConfig): bool
    {
        return $this->fieldNormalizer->isFileField($fieldConfig);
    }

    public function isEmptySeedValue(mixed $value): bool
    {
        return $this->fieldNormalizer->isEmptySeedValue($value);
    }



    /**
     * @param array<string, array<string, mixed>> $fields
     */
    public function resolveScalarField(string $field, array $fields): ?string
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
    public function resolveCollectionField(string $field, array $value, array $definition): ?string
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
    public function scoreCollectionCandidate(string $field, array $value, string $identifier, array $collection): float
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
    public function normalizeCollectionItems(array $items, array $collection): array
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
    public function normalizeCollectionItem(mixed $item, array $collection): array
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
                    $normalizedItem[SeedingPayloadKeys::NESTED_COLLECTIONS]['cells'] = [
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
    public function populateFixedLinkSlots(array $normalizedItem, array $sourceItem, array $collection): array
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
    public function populateNumberedLinkSlots(
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
    public function normalizeLinkFixture(mixed $sourceLink): array
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
    public function collectionHasField(array $collection, string $field): bool
    {
        return isset($collection['fields'][$field])
            || $this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $field);
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function getCollectionTable(array $collection): string
    {
        $table = $collection['table'] ?? '';
        return is_string($table) ? $table : '';
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>|null
     */
    public function getCollectionFieldConfig(array $collection, string $field): ?array
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
    public function normalizeArrayForScalarField(array $value, string $field): mixed
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
    public function formatFlatScalarList(array $values, string $separator): string
    {
        return $this->fieldNormalizer->formatFlatScalarList($values, $separator);
    }

    /**
     * @param array<int, mixed> $value
     * @param array<string, mixed> $collection
     */
    public function normalizeArrayForCollectionField(array $value, string $field, array $collection): mixed
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

    public function normalizeFieldValue(mixed $value, array $fieldConfig): mixed
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

    public function normalizeDateTimeFieldValue(mixed $value): int|string
    {
        return $this->fieldNormalizer->normalizeDateTimeFieldValue($value);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function normalizeSelectValue(mixed $value, array $fieldConfig): mixed
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
    public function findPreferredTextField(array $collection): ?string
    {
        foreach (['label', 'title', 'name', 'feature_name', 'row_label', 'text', 'value', 'question', 'row_data', 'links', 'features_list', 'description'] as $candidate) {
            if (isset($collection['fields'][$candidate]) || $this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function normalizeScalarValue(mixed $value): int|string
    {
        return $this->fieldNormalizer->normalizeScalarValue($value);
    }


    /**
     * @param array<int|string, mixed> $value
     */
    public function isListOfScalars(array $value): bool
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
    public function containsNestedArray(array $values): bool
    {
        return $this->fieldNormalizer->containsNestedArray($values);
    }


    public function singularize(string $value): string
    {
        return match (true) {
            str_ends_with($value, 'ies') => substr($value, 0, -3) . 'y',
            str_ends_with($value, 's') => substr($value, 0, -1),
            default => $value,
        };
    }

}
