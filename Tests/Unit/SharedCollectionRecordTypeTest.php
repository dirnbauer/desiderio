<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Guards the shared collection record types.
 *
 * The failure mode these prevent is quiet: a Collection pointing at a shared
 * table without both share flags does not error, it just makes every sharer's
 * children appear in every other sharer's element. And a sharer whose field
 * definitions drift from the record type does not error either — the record
 * type simply wins, and the element silently loses its own labels, its
 * `required` flag or its RTE configuration.
 *
 * See Documentation/Developer/CollectionTableConsolidation.md for why only 12
 * of the 23 identical-column groups were merged.
 */
final class SharedCollectionRecordTypeTest extends TestCase
{
    private const ELEMENTS_DIR = __DIR__ . '/../../ContentBlocks/ContentElements';
    private const RECORD_TYPES_DIR = __DIR__ . '/../../ContentBlocks/RecordTypes';
    private const MAP = __DIR__ . '/../../Build/Data/collection-merge-map.json';

    /** @return array<string, array<string, mixed>> table => record type config */
    private function recordTypes(): array
    {
        $paths = glob(self::RECORD_TYPES_DIR . '/*/config.yaml');
        $types = [];
        foreach ($paths === false ? [] : $paths as $path) {
            $config = Yaml::parseFile($path);
            self::assertIsArray($config, "Unparseable record type: $path");
            $table = $config['table'] ?? null;
            self::assertIsString($table, "Record type without a table: $path");

            $normalized = [];
            foreach ($config as $key => $value) {
                $normalized[(string)$key] = $value;
            }
            $types[$table] = $normalized;
        }

        return $types;
    }

    /** @return list<array{element: string, identifier: string, table: string, field: array<string, mixed>}> */
    private function sharedCollections(): array
    {
        $paths = glob(self::ELEMENTS_DIR . '/*/config.yaml');
        $found = [];
        foreach ($paths === false ? [] : $paths as $path) {
            $config = Yaml::parseFile($path);
            if (!is_array($config)) {
                continue;
            }
            $element = basename(dirname($path));

            $walk = function (array $fields) use (&$walk, &$found, $element): void {
                foreach ($fields as $field) {
                    if (!is_array($field)) {
                        continue;
                    }
                    $foreignTable = $field['foreign_table'] ?? null;
                    $identifier = $field['identifier'] ?? null;
                    if (($field['type'] ?? '') === 'Collection' && is_string($foreignTable) && is_string($identifier)) {
                        $normalized = [];
                        foreach ($field as $key => $value) {
                            $normalized[(string)$key] = $value;
                        }
                        $found[] = [
                            'element' => $element,
                            'identifier' => $identifier,
                            'table' => $foreignTable,
                            'field' => $normalized,
                        ];
                    }
                    if (is_array($field['fields'] ?? null)) {
                        $walk($field['fields']);
                    }
                }
            };
            $walk(is_array($config['fields'] ?? null) ? $config['fields'] : []);
        }

        return $found;
    }

    public function testEveryForeignTableResolvesToARecordType(): void
    {
        $types = $this->recordTypes();
        $unknown = [];
        foreach ($this->sharedCollections() as $entry) {
            if (!isset($types[$entry['table']])) {
                $unknown[] = $entry['element'] . '.' . $entry['identifier'] . ' -> ' . $entry['table'];
            }
        }

        self::assertSame([], $unknown, 'foreign_table without a matching record type: ' . implode(', ', $unknown));
    }

    public function testEverySharerSetsBothShareFlags(): void
    {
        $missing = [];
        foreach ($this->sharedCollections() as $entry) {
            $field = $entry['field'];
            $label = $entry['element'] . '.' . $entry['identifier'];
            if (($field['shareAcrossTables'] ?? false) !== true) {
                $missing[] = "$label: shareAcrossTables";
            }
            if (($field['shareAcrossFields'] ?? false) !== true) {
                $missing[] = "$label: shareAcrossFields";
            }
        }

        // Content Blocks is explicit that both are required on EVERY sharer.
        // Omit one and the children show up twice in the backend, with no error.
        self::assertSame([], $missing, 'Shared collections missing a share flag: ' . implode(', ', $missing));
    }

    public function testASharedCollectionDeclaresNoInlineFields(): void
    {
        $stale = [];
        foreach ($this->sharedCollections() as $entry) {
            if (($entry['field']['fields'] ?? null) !== null) {
                $stale[] = $entry['element'] . '.' . $entry['identifier'];
            }
        }

        // `foreign_table:` makes them inert, so leaving them in place is a trap:
        // an editor changes one and nothing happens.
        self::assertSame([], $stale, 'Shared collections still carrying inert inline fields: ' . implode(', ', $stale));
    }

    public function testRecordTypesDoNotPrefixTheirColumns(): void
    {
        $prefixed = [];
        foreach ($this->recordTypes() as $table => $config) {
            if (($config['prefixFields'] ?? true) !== false) {
                $prefixed[] = $table;
            }
        }
        sort($prefixed);

        // With prefixing on, `question` would become `desiderio_qaitem_question`:
        // every column renamed, every template reading {item.question} broken,
        // and the migration turned into a mapping exercise instead of a copy.
        self::assertSame([], $prefixed, 'Record types must set prefixFields: false — ' . implode(', ', $prefixed));
    }

    public function testTheFrozenMapMatchesTheConfigs(): void
    {
        $map = json_decode((string)file_get_contents(self::MAP), true);
        self::assertIsArray($map);
        self::assertIsArray($map['sources'] ?? null);

        $declared = [];
        foreach ($this->sharedCollections() as $entry) {
            $declared[$entry['element'] . '.' . $entry['identifier']] = $entry['table'];
        }

        $expected = [];
        foreach ($map['sources'] as $source) {
            self::assertIsArray($source);
            $element = $source['element'] ?? null;
            $identifier = $source['identifier'] ?? null;
            $target = $source['target'] ?? null;
            self::assertIsString($element);
            self::assertIsString($identifier);
            self::assertIsString($target);
            $expected[$element . '.' . $identifier] = $target;
        }

        ksort($declared);
        ksort($expected);

        // The migration reads the map; the runtime reads the configs. If they
        // disagree, rows move somewhere the TCA will never look for them.
        self::assertSame($expected, $declared, 'collection-merge-map.json and the element configs disagree.');
    }

    public function testNoTwoCollectionsOnOneElementShareATable(): void
    {
        $byElement = [];
        foreach ($this->sharedCollections() as $entry) {
            $byElement[$entry['element']][] = $entry['table'];
        }

        $clashes = [];
        foreach ($byElement as $element => $tables) {
            $duplicates = array_diff_assoc($tables, array_unique($tables));
            if ($duplicates !== []) {
                $clashes[] = $element . ': ' . implode(', ', array_unique($duplicates));
            }
        }

        // Two fields of ONE element sharing a table is the case where ownership
        // must be resolved by `fieldname` alone on an identical parent uid.
        // CollectionCleanupService handles it, but nothing exercises it today,
        // so keep the situation from arising unnoticed.
        self::assertSame([], $clashes, 'Element with two collections on the same shared table: ' . implode(' | ', $clashes));
    }
}
