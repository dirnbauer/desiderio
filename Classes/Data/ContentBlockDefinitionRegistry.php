<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Library\ElementCatalog;

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
        self::$recordTypeFields = null;
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

        $definitions = [];

        // Every host's elements, not only this extension's. The seeding cleanup
        // derives the list of collection child tables from these definitions,
        // so an extension missing here has its child rows left behind on every
        // reseed — they accumulate silently, still "live", attached to content
        // elements that were deleted long ago.
        foreach (self::getContentElementHosts() as $extensionKey) {
            $basePath = GeneralUtility::getFileAbsFileName('EXT:' . $extensionKey . '/ContentBlocks/ContentElements');
            if ($basePath === '' || !is_dir($basePath)) {
                continue;
            }

            $directories = scandir($basePath);
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

                $configuredTypeName = $config['typeName'] ?? null;
                if (is_string($configuredTypeName) && $configuredTypeName !== '') {
                    $typeName = $configuredTypeName;
                } elseif ($extensionKey === 'desiderio') {
                    // Only this extension's own directory names are known to
                    // follow the desiderio_<name> convention. Guessing a CType
                    // for someone else's element would invent a wrong key.
                    $typeName = 'desiderio_' . str_replace('-', '', $directory);
                } else {
                    continue;
                }

                $definitions[$typeName] = self::buildContentBlockDefinition($config);
            }
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
            if (($field['type'] ?? '') !== 'Collection') {
                $field['storageIdentifier'] = self::resolveRootFieldStorageIdentifier($config, $field, $identifier);
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

        // A shared collection declares `foreign_table:` and carries no `fields:`
        // of its own — the field list lives in the RecordType it points at.
        // Without this the seeders would see an empty child definition and
        // silently write rows with no content at all.
        $nestedFields = $field['fields'] ?? [];
        if (!is_array($nestedFields) || $nestedFields === []) {
            $nestedFields = self::getRecordTypeFields(self::resolveCollectionTable($field, $fallbackIdentifier));
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
    /** @var array<string, list<array<string, mixed>>>|null table => fields */
    private static ?array $recordTypeFields = null;

    /**
     * Field lists of the shared Record Types, keyed by the table they define.
     *
     * These live in ContentBlocks/RecordTypes/ rather than ContentElements/ and
     * are what a Collection using `foreign_table:` resolves to. Scanned lazily
     * because most callers never touch a shared collection.
     *
     * @return list<array<string, mixed>>
     */
    private static function getRecordTypeFields(string $table): array
    {
        if (self::$recordTypeFields === null) {
            self::$recordTypeFields = [];
            foreach (self::getRecordTypeBasePaths() as $basePath) {
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
                    if (!is_array($config) || !is_string($config['table'] ?? null)) {
                        continue;
                    }
                    $fields = $config['fields'] ?? [];
                    $normalized = [];
                    foreach (is_array($fields) ? $fields : [] as $recordField) {
                        if (is_array($recordField)) {
                            $normalized[] = self::normalizeStringKeyedArray($recordField);
                        }
                    }
                    self::$recordTypeFields[$config['table']] = $normalized;
                }
            }
        }

        return self::$recordTypeFields[$table] ?? [];
    }

    /**
     * Extension keys whose ContentElements/ directories make up the registry.
     *
     * The same list the element library uses, so an extension that registers
     * itself as a library host is automatically known here too — one
     * registration, not three.
     *
     * @return list<string>
     */
    private static function getContentElementHosts(): array
    {
        return ElementCatalog::hostExtensions();
    }

    /**
     * Every directory that may hold shared RecordTypes.
     *
     * Desiderio's own comes first, and any extension building on this engine
     * adds its own by appending an ABSOLUTE path to
     * $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['desiderio']['recordTypePaths']
     * in ext_localconf.php — the same convention as libraryHostExtensions.
     *
     * Absolute, not EXT:, on purpose. Paths are resolved from this file rather
     * than through GeneralUtility because getRecordTypeFields() is reached from
     * buildDefinitionFromConfig(), which is pure and is called from unit tests
     * with no TYPO3 bootstrap; EXT: resolution here would make the whole
     * registry require a booted framework.
     *
     * Before this existed, a downstream extension's shared collections resolved
     * to an empty field list, and the seeders wrote child rows whose file
     * fields were silently left at 0 — every portrait and logo in a shared
     * collection came out blank.
     *
     * @return list<string>
     */
    private static function getRecordTypeBasePaths(): array
    {
        $paths = [dirname(__DIR__, 2) . '/ContentBlocks/RecordTypes'];

        $registered = $GLOBALS['TYPO3_CONF_VARS'] ?? null;
        foreach (['EXTENSIONS', 'desiderio', 'recordTypePaths'] as $key) {
            $registered = is_array($registered) ? ($registered[$key] ?? null) : null;
        }
        foreach (is_array($registered) ? $registered : [] as $path) {
            if (is_string($path) && $path !== '') {
                $paths[] = rtrim($path, '/');
            }
        }

        return array_values(array_unique($paths));
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
