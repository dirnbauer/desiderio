<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Canonical loader for Content Block YAML definitions.
 * Used by seed commands and the frontend collection processor.
 */
final class ContentBlockDefinitionRegistry
{
    /**
     * @var array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}>|null
     */
    private static ?array $definitions = null;

    /**
     * @var array<string, array{collections: array<string, array<string, mixed>>}>|null
     */
    private static ?array $runtimeCollectionDefinitions = null;

    /**
     * @param array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}> $definitions
     */
    public static function setDefinitionsForTesting(array $definitions): void
    {
        self::$definitions = $definitions;
        self::$runtimeCollectionDefinitions = null;
    }

    public static function resetCache(): void
    {
        self::$definitions = null;
        self::$runtimeCollectionDefinitions = null;
    }

    /**
     * @param array<string, mixed> $config
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}
     */
    public static function buildDefinitionFromConfig(array $config): array
    {
        return self::buildContentBlockDefinition(self::normalizeStringKeyedArray($config));
    }

    /**
     * @return array<string, array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}>
     */
    public static function getDefinitions(): array
    {
        if (self::$definitions !== null) {
            return self::$definitions;
        }

        $basePath = GeneralUtility::getFileAbsFileName('EXT:desiderio/ContentBlocks/ContentElements');
        if ($basePath === '' || !is_dir($basePath)) {
            self::$definitions = [];
            return self::$definitions;
        }

        $definitions = [];
        $directories = scandir($basePath);
        if ($directories === false) {
            self::$definitions = [];
            return self::$definitions;
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
            $config = self::normalizeStringKeyedArray($config);

            $configuredTypeName = $config['typeName'] ?? null;
            $typeName = is_string($configuredTypeName) && $configuredTypeName !== ''
                ? $configuredTypeName
                : 'desiderio_' . str_replace('-', '', $directory);
            $definitions[$typeName] = self::buildContentBlockDefinition($config);
        }

        self::$definitions = $definitions;
        return self::$definitions;
    }

    /**
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}|null
     */
    public static function getDefinition(string $ctype): ?array
    {
        return self::getDefinitions()[$ctype] ?? null;
    }

    /**
     * @return array<string, array{collections: array<string, array<string, mixed>>}>
     */
    public static function getRuntimeCollectionDefinitions(): array
    {
        if (self::$runtimeCollectionDefinitions !== null) {
            return self::$runtimeCollectionDefinitions;
        }

        $definitions = [];
        foreach (self::getDefinitions() as $ctype => $definition) {
            if ($definition['collections'] === []) {
                continue;
            }

            $definitions[$ctype] = [
                'collections' => self::mapRuntimeCollections($definition['collections']),
            ];
        }

        self::$runtimeCollectionDefinitions = $definitions;
        return self::$runtimeCollectionDefinitions;
    }

    /**
     * @param array<string, mixed> $config
     * @return array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>}
     */
    private static function buildContentBlockDefinition(array $config): array
    {
        $definition = [
            'fields' => [],
            'collections' => [],
        ];

        foreach (($config['fields'] ?? []) as $field) {
            if (!is_array($field) || !isset($field['identifier'])) {
                continue;
            }
            $field = self::normalizeStringKeyedArray($field);
            if (!is_string($field['identifier'] ?? null)) {
                continue;
            }

            $identifier = $field['identifier'];
            if (($field['type'] ?? '') !== 'Collection') {
                $definition['fields'][$identifier] = $field;
                continue;
            }

            $definition['collections'][$identifier] = self::buildCollectionDefinition(
                $field,
                $identifier,
                self::resolveRootFieldStorageIdentifier($config, $field, $identifier)
            );
        }

        return $definition;
    }

    /**
     * @param array<string, mixed> $field
     * @return array{table: string, column: string, fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>, minItems: int, maxItems: int|null}
     */
    private static function buildCollectionDefinition(array $field, string $fallbackIdentifier, ?string $column = null): array
    {
        $childFields = [];
        $childCollections = [];

        foreach (($field['fields'] ?? []) as $childField) {
            if (!is_array($childField) || !isset($childField['identifier'])) {
                continue;
            }
            $childField = self::normalizeStringKeyedArray($childField);
            if (!is_string($childField['identifier'] ?? null)) {
                continue;
            }

            $childIdentifier = $childField['identifier'];
            if (($childField['type'] ?? '') === 'Collection') {
                $childCollections[$childIdentifier] = self::buildCollectionDefinition($childField, $childIdentifier);
                continue;
            }

            $childFields[$childIdentifier] = $childField;
        }

        return [
            'table' => self::resolveCollectionTable($field, $fallbackIdentifier),
            'column' => $column ?? $fallbackIdentifier,
            'fields' => $childFields,
            'collections' => $childCollections,
            'minItems' => self::getConfiguredInteger($field, 'minItems')
                ?? self::getConfiguredInteger($field, 'minitems')
                ?? 1,
            'maxItems' => self::getConfiguredInteger($field, 'maxItems')
                ?? self::getConfiguredInteger($field, 'maxitems'),
        ];
    }

    /**
     * @param array<string, array<string, mixed>> $collections
     * @return array<string, array<string, mixed>>
     */
    private static function mapRuntimeCollections(array $collections): array
    {
        $mapped = [];
        foreach ($collections as $identifier => $collection) {
            $nestedCollections = $collection['collections'] ?? [];
            if (!is_array($nestedCollections)) {
                $nestedCollections = [];
            }

            $mapped[$identifier] = [
                'table' => is_string($collection['table'] ?? null) ? $collection['table'] : $identifier,
                'fields' => is_array($collection['fields'] ?? null) ? $collection['fields'] : [],
                'collections' => self::mapRuntimeCollections($nestedCollections),
            ];
        }

        return $mapped;
    }

    /**
     * @param array<string, mixed> $field
     */
    private static function resolveCollectionTable(array $field, string $fallbackIdentifier): string
    {
        $configuredTableValue = $field['table'] ?? $field['foreign_table'] ?? null;
        return is_string($configuredTableValue) && $configuredTableValue !== ''
            ? $configuredTableValue
            : $fallbackIdentifier;
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function resolveContentBlockPrefix(array $config): string
    {
        $name = is_string($config['name'] ?? null) ? $config['name'] : '';
        if (!str_contains($name, '/')) {
            return '';
        }

        $parts = explode('/', $name, 2);
        $vendorPrefix = is_string($config['vendorPrefix'] ?? null) && $config['vendorPrefix'] !== ''
            ? $config['vendorPrefix']
            : $parts[0];
        $prefixType = is_string($config['prefixType'] ?? null) ? $config['prefixType'] : 'full';

        if ($prefixType === 'vendor') {
            return str_replace('-', '', $vendorPrefix);
        }

        return str_replace('-', '', $vendorPrefix) . '_' . str_replace('-', '', $parts[1]);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $field
     */
    private static function resolveRootFieldStorageIdentifier(array $config, array $field, string $identifier): string
    {
        if (($field['useExistingField'] ?? false) === true) {
            return $identifier;
        }

        $prefixEnabled = array_key_exists('prefixField', $field)
            ? (bool)$field['prefixField']
            : (bool)($config['prefixFields'] ?? true);
        if (!$prefixEnabled) {
            return $identifier;
        }

        $prefix = self::resolveContentBlockPrefix([
            ...$config,
            'prefixType' => $field['prefixType'] ?? ($config['prefixType'] ?? 'full'),
        ]);

        return $prefix !== '' ? $prefix . '_' . $identifier : $identifier;
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function getConfiguredInteger(array $config, string $key): ?int
    {
        if (!isset($config[$key]) || !is_numeric($config[$key])) {
            return null;
        }

        return (int)$config[$key];
    }

    /**
     * @param array<mixed, mixed> $array
     * @return array<string, mixed>
     */
    public static function normalizeStringKeyedArray(array $array): array
    {
        $normalized = [];
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }
}
