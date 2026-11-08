<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

use Symfony\Component\Yaml\Yaml;

/**
 * A Collection field of a Content Block as config.yaml declares it, with the
 * TCA column Content Blocks derives for it. The column is what f:mark.item
 * expects as `collection`: the Visual Editor matches markers to the parent's
 * inline field, and the ViewHelper refuses a name that is no inline column
 * holding the child table.
 *
 * Naming follows Content Blocks' UniqueIdentifierCreator: a root field with
 * prefixing on (block `prefixFields`, field `prefixField`, not
 * `useExistingField`) becomes `<vendor>_<name>_<identifier>` with dashes
 * removed (prefixType full) or `<vendor>_<identifier>` (prefixType vendor);
 * fields of a Collection's own table are never prefixed.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class CollectionField
{
    /** @var list<CollectionField> */
    public array $children = [];

    /**
     * File fields of the entry that hold at most one file: a loop over one of
     * them renders its body once or not at all.
     *
     * @var list<string>
     */
    public array $singleFileFields = [];

    public function __construct(
        public readonly string $identifier,
        public readonly string $column,
        public readonly string $table,
        public readonly string $path,
        public readonly ?CollectionField $parent,
    ) {}

    /**
     * The root Collection fields of a Content Block (nested ones as children).
     *
     * @return list<CollectionField>
     */
    public static function fromConfigFile(string $configFile): array
    {
        $yaml = Yaml::parseFile($configFile);
        if (!is_array($yaml)) {
            return [];
        }
        $name = is_string($yaml['name'] ?? null) ? $yaml['name'] : '';
        $fields = is_array($yaml['fields'] ?? null) ? $yaml['fields'] : [];

        return self::collect($fields, $yaml, $name, null);
    }

    /**
     * @param array<mixed> $fields
     * @param array<mixed> $block
     * @return list<CollectionField>
     */
    private static function collect(array $fields, array $block, string $name, ?CollectionField $parent): array
    {
        $collections = [];
        foreach ($fields as $field) {
            if (!is_array($field) || ($field['type'] ?? null) !== 'Collection' || !is_string($field['identifier'] ?? null)) {
                continue;
            }
            $identifier = $field['identifier'];
            $table = $field['table'] ?? $field['foreign_table'] ?? $identifier;
            $collection = new self(
                $identifier,
                $parent === null ? self::rootColumn($identifier, $field, $block, $name) : $identifier,
                is_string($table) ? $table : $identifier,
                $parent === null ? $identifier : $parent->path . '.' . $identifier,
                $parent,
            );
            $children = is_array($field['fields'] ?? null) ? $field['fields'] : [];
            $collection->children = self::collect($children, $block, $name, $collection);
            foreach ($children as $child) {
                if (is_array($child) && ($child['type'] ?? null) === 'File' && (int)($child['maxitems'] ?? 0) === 1 && is_string($child['identifier'] ?? null)) {
                    $collection->singleFileFields[] = $child['identifier'];
                }
            }
            $collections[] = $collection;
        }
        return $collections;
    }

    /**
     * @param array<mixed> $field
     * @param array<mixed> $block
     */
    private static function rootColumn(string $identifier, array $field, array $block, string $name): string
    {
        if ((bool)($field['useExistingField'] ?? false)) {
            return $identifier;
        }
        $prefix = array_key_exists('prefixField', $field) ? (bool)$field['prefixField'] : (bool)($block['prefixFields'] ?? true);
        if (!$prefix || !str_contains($name, '/')) {
            return $identifier;
        }
        [$vendor, $element] = explode('/', $name, 2);
        $vendorPrefix = str_replace('-', '', is_string($block['vendorPrefix'] ?? null) ? $block['vendorPrefix'] : $vendor);
        $type = $field['prefixType'] ?? $block['prefixType'] ?? 'full';

        return $type === 'vendor'
            ? $vendorPrefix . '_' . $identifier
            : $vendorPrefix . '_' . str_replace('-', '', $element) . '_' . $identifier;
    }
}
