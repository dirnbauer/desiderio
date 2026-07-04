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
     * Record type definitions keyed by their table name.
     *
     * @var array<string, array{table: string, fields: array<string, array<string, mixed>>}>|null
     */
    private static ?array $recordTypeDefinitions = null;

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
        self::$recordTypeDefinitions = null;
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

        $configFields = $config['fields'] ?? [];
        if (!is_array($configFields)) {
            $configFields = [];
        }

        foreach ($configFields as $field) {
            if (!is_array($field) || !isset($field['identifier'])) {
                continue;
            }
            $field = self::normalizeStringKeyedArray($field);
            if (!is_string($field['identifier'] ?? null)) {
                continue;
            }

            $identifier = $field['identifier'];
            if (($field['type'] ?? '') === 'Relation') {
                $pool = self::getRecordTypeDefinitionByTable(
                    is_string($field['allowed'] ?? null) ? $field['allowed'] : ''
                );
                if ($pool !== null) {
                    // Shared record pool: expose the relation as a collection-shaped
                    // definition (pool table + pool fields) so fixture resolution and
                    // demo completion reuse the collection machinery. The `relation`
                    // flag makes the seeder store a uid CSV on the parent column and
                    // reuse existing pool records instead of inserting children.
                    $definition['collections'][$identifier] = [
                        'table' => $pool['table'],
                        'column' => self::resolveRootFieldStorageIdentifier($config, $field, $identifier),
                        'fields' => $pool['fields'],
                        'collections' => [],
                        'minItems' => self::getConfiguredInteger($field, 'minitems')
                            ?? self::getConfiguredInteger($field, 'minItems')
                            ?? 1,
                        'maxItems' => self::getConfiguredInteger($field, 'maxitems')
                            ?? self::getConfiguredInteger($field, 'maxItems'),
                        'relation' => true,
                    ];
                    continue;
                }
            }
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
     * @return array{table: string, fields: array<string, array<string, mixed>>}|null
     */
    public static function getRecordTypeDefinitionByTable(string $table): ?array
    {
        if ($table === '') {
            return null;
        }
        return self::getRecordTypeDefinitions()[$table] ?? null;
    }

    /**
     * @return array<string, array{table: string, fields: array<string, array<string, mixed>>}>
     */
    private static function getRecordTypeDefinitions(): array
    {
        if (self::$recordTypeDefinitions !== null) {
            return self::$recordTypeDefinitions;
        }

        $definitions = [];
        // Resolved relative to this file so the loader also works in
        // standalone CLI scripts that run without a booted TYPO3
        // (e.g. scripts/fill-content-element-fixtures.php).
        $basePath = dirname(__DIR__, 2) . '/ContentBlocks/RecordTypes';
        $directories = is_dir($basePath) ? scandir($basePath) : false;
        foreach ($directories === false ? [] : $directories as $directory) {
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
            $table = is_string($config['table'] ?? null) ? $config['table'] : '';
            if ($table === '') {
                continue;
            }

            $fields = [];
            foreach (is_array($config['fields'] ?? null) ? $config['fields'] : [] as $field) {
                if (!is_array($field)) {
                    continue;
                }
                $field = self::normalizeStringKeyedArray($field);
                $identifier = $field['identifier'] ?? null;
                if (is_string($identifier) && $identifier !== '' && ($field['type'] ?? '') !== 'Collection') {
                    $fields[$identifier] = $field;
                }
            }
            $definitions[$table] = ['table' => $table, 'fields' => $fields];
        }

        self::$recordTypeDefinitions = $definitions;
        return $definitions;
    }

    /**
     * @param array<string, mixed> $field
     * @return array{table: string, column: string, fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>, minItems: int, maxItems: int|null}
     */
    private static function buildCollectionDefinition(array $field, string $fallbackIdentifier, ?string $column = null): array
    {
        $childFields = [];
        $childCollections = [];

        $nestedFields = $field['fields'] ?? [];
        if (!is_array($nestedFields)) {
            $nestedFields = [];
        }

        foreach ($nestedFields as $childField) {
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
            if (($collection['relation'] ?? false) === true) {
                // Relation pools resolve natively through Content Blocks; the
                // frontend collection fallback must not touch them.
                continue;
            }
            $nestedCollections = $collection['collections'] ?? [];
            if (!is_array($nestedCollections)) {
                $nestedCollections = [];
            }

            $mapped[$identifier] = [
                'table' => is_string($collection['table'] ?? null) ? $collection['table'] : $identifier,
                'fields' => is_array($collection['fields'] ?? null) ? $collection['fields'] : [],
                'collections' => self::mapRuntimeCollections(self::normalizeStringKeyedNestedCollections($nestedCollections)),
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

    /**
     * @param array<mixed> $collections
     * @return array<string, array<string, mixed>>
     */
    private static function normalizeStringKeyedNestedCollections(array $collections): array
    {
        $normalized = [];
        foreach ($collections as $key => $collection) {
            if (is_string($key) && is_array($collection)) {
                $normalized[$key] = self::normalizeStringKeyedArray($collection);
            }
        }

        return $normalized;
    }
}
