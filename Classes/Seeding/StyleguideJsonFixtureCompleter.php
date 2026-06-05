<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Icon\IconRegistry;

/**
 * Completes Content Block fixture.json files with deterministic demo values.
 */
final class StyleguideJsonFixtureCompleter
{
    public function __construct(
        private readonly StyleguideDemoValueGenerator $demoValueGenerator = new StyleguideDemoValueGenerator(),
    ) {}

    /**
     * @param array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $definition
     * @param array<string, mixed> $fixture
     * @return array<string, mixed>
     */
    public function complete(string $ctype, string $name, array $definition, array $fixture): array
    {
        foreach ($definition['fields'] as $field => $fieldConfig) {
            if (!is_string($field) || !is_array($fieldConfig)) {
                continue;
            }
            $fieldConfig = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig);
            if (($fieldConfig['type'] ?? '') === 'File') {
                if (!$this->hasFileFixtureValue($fixture[$field] ?? null)) {
                    $fixture[$field] = StyleguideDemoAssets::buildFileFixtureValue(
                        $field,
                        $fieldConfig,
                        0,
                        $this->demoValueGenerator,
                    );
                }
                continue;
            }

            if (!array_key_exists($field, $fixture) || $this->isEmptyValue($fixture[$field])) {
                $default = $this->demoValueGenerator->buildFixtureBackedFieldValue($field, $fixture)
                    ?? $this->buildDefaultFieldValue($ctype, $name, $field, $fieldConfig, 0);
                if ($default !== StyleguideDemoValueGenerator::FIELD_SKIP) {
                    $fixture[$field] = $default;
                }
            }
        }

        foreach ($definition['collections'] as $field => $collection) {
            if (!is_string($field) || !is_array($collection)) {
                continue;
            }
            $collection = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($collection);
            $existingItems = $fixture[$field] ?? [];
            if (!is_array($existingItems)) {
                $existingItems = [];
            }

            $targetItemCount = $this->getTargetCollectionItemCount($collection, count($existingItems));
            $items = [];

            for ($index = 0; $index < $targetItemCount; $index++) {
                $item = $existingItems[$index] ?? [];
                $item = is_array($item) ? ContentBlockDefinitionRegistry::normalizeStringKeyedArray($item) : [];
                $completedItem = $this->completeCollectionItem($ctype, $name, $collection, $item, $index);
                if ($completedItem !== []) {
                    $items[] = $completedItem;
                }
            }

            if ($items !== []) {
                $fixture[$field] = $items;
            }
        }

        if ($this->demoValueGenerator->normalizeIdentifier($ctype) === 'desideriotabs') {
            $fixture['default_tab'] = $this->demoValueGenerator->normalizeTabsDefaultTab($fixture['default_tab'] ?? 0);
        }

        return $fixture;
    }

    /**
     * @param array<string, mixed> $collection
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function completeCollectionItem(
        string $ctype,
        string $name,
        array $collection,
        array $item,
        int $index,
    ): array {
        $collectionFields = $collection['fields'] ?? [];
        if (!is_array($collectionFields)) {
            $collectionFields = [];
        }

        foreach ($collectionFields as $field => $fieldConfig) {
            if (!is_string($field) || !is_array($fieldConfig)) {
                continue;
            }
            $fieldConfig = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig);
            if (($fieldConfig['type'] ?? '') === 'File') {
                if (!$this->hasFileFixtureValue($item[$field] ?? null)) {
                    $item[$field] = StyleguideDemoAssets::buildFileFixtureValue(
                        $field,
                        $fieldConfig,
                        $index,
                        $this->demoValueGenerator,
                    );
                }
                continue;
            }

            if (!array_key_exists($field, $item) || $this->isEmptyValue($item[$field])) {
                $default = $this->buildDefaultFieldValue($ctype, $name, $field, $fieldConfig, $index);
                if ($default !== StyleguideDemoValueGenerator::FIELD_SKIP) {
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
            $existingItems = $item[$field] ?? [];
            if (!is_array($existingItems)) {
                $existingItems = [];
            }

            $targetItemCount = $this->getTargetCollectionItemCount($nestedCollection, count($existingItems));
            $items = [];

            for ($nestedIndex = 0; $nestedIndex < $targetItemCount; $nestedIndex++) {
                $nestedItem = $existingItems[$nestedIndex] ?? [];
                $nestedItem = is_array($nestedItem) ? ContentBlockDefinitionRegistry::normalizeStringKeyedArray($nestedItem) : [];
                $completedItem = $this->completeCollectionItem($ctype, $name, $nestedCollection, $nestedItem, $nestedIndex);
                if ($completedItem !== []) {
                    $items[] = $completedItem;
                }
            }

            if ($items !== []) {
                $item[$field] = $items;
            }
        }

        return $item;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    private function buildDefaultFieldValue(
        string $ctype,
        string $name,
        string $field,
        array $fieldConfig,
        int $index,
    ): mixed {
        if (StyleguideDemoAssets::isIconSelectField($field, $fieldConfig)) {
            return $this->demoValueGenerator->pickDemoString(IconRegistry::demoKeys(), $name . '-' . $field, $index);
        }

        return $this->demoValueGenerator->buildDefaultFieldValue($ctype, $name, $field, $fieldConfig, $index);
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

    private function hasFileFixtureValue(mixed $value): bool
    {
        if ($this->isEmptyValue($value)) {
            return false;
        }

        if (is_string($value) && trim($value) !== '') {
            return true;
        }

        if (is_array($value) && isset($value['file']) && is_string($value['file']) && trim($value['file']) !== '') {
            return true;
        }

        if (is_array($value) && array_is_list($value)) {
            foreach ($value as $item) {
                if ($this->hasFileFixtureValue($item)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isEmptyValue(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        return is_array($value) && $value === [];
    }
}
