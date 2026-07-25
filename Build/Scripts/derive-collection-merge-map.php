<?php

declare(strict_types=1);

/**
 * Derives the collection-consolidation map from ContentBlocks/ContentElements/
 * config.yaml — never from a live database, so the answer is the same on any
 * installation.
 *
 * Two collections may share a table only when their CHILD FIELD DEFINITIONS are
 * identical, because `foreign_table:` makes each sharer's own `fields:` inert
 * and exactly one definition wins for all of them. Grouping by SQL column type
 * (the obvious shortcut) massively overstates what is mergeable: it cannot see
 * `required`, `enableRichtext`, Select `items` or labels, and those are what
 * actually differ.
 *
 * Usage: php Build/Scripts/derive-collection-merge-map.php
 */
require __DIR__ . '/../../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

$root = dirname(__DIR__, 2);
$dir  = $root . '/ContentBlocks/ContentElements';

/**
 * Walk a config's fields and yield every Collection with an explicit `table:`,
 * at any nesting depth, recording its parent so the migration can order writes.
 */
function collectCollections(array $fields, string $element, ?string $parentTable, array &$out, array $rootConfig): void
{
    foreach ($fields as $field) {
        if (!is_array($field) || ($field['type'] ?? '') !== 'Collection') {
            // Palettes inline their children into the same level.
            if (is_array($field) && ($field['type'] ?? '') === 'Palette' && isset($field['fields'])) {
                collectCollections($field['fields'], $element, $parentTable, $out, $rootConfig);
            }
            continue;
        }
        $table = $field['table'] ?? null;
        if (!is_string($table) || $table === '') {
            continue;
        }

        // The signature is the child field set: identifier + Content Blocks type.
        $sig = [];
        foreach ($field['fields'] ?? [] as $child) {
            if (!is_array($child) || !isset($child['identifier'])) continue;
            $type = $child['type'] ?? ($child['useExistingField'] ?? false ? 'UseExisting' : 'Text');
            if ($type === 'Collection') { $type = 'Collection:' . ($child['table'] ?? '?'); }
            $sig[$child['identifier']] = $type;
        }
        ksort($sig);

        $out[$table] = [
            'table' => $table,
            'element' => $element,
            'identifier' => $field['identifier'],
            'parentTable' => $parentTable,
            'prefixField' => $field['prefixField'] ?? null,
            'prefixFields' => $rootConfig['prefixFields'] ?? true,
            'minItems' => $field['minItems'] ?? null,
            'maxItems' => $field['maxItems'] ?? null,
            'signature' => $sig,
            'signatureKey' => implode(',', array_map(fn($k, $v) => "$k:$v", array_keys($sig), $sig)),
            'fields' => $field['fields'] ?? [],
        ];

        collectCollections($field['fields'] ?? [], $element, $table, $out, $rootConfig);
    }
}

$all = [];
foreach (scandir($dir) as $entry) {
    if ($entry === '.' || $entry === '..' || !is_dir("$dir/$entry")) continue;
    $cfgPath = "$dir/$entry/config.yaml";
    if (!is_readable($cfgPath)) continue;
    $cfg = Yaml::parseFile($cfgPath);
    if (!is_array($cfg)) continue;
    collectCollections($cfg['fields'] ?? [], $entry, null, $all, $cfg);
}

echo "collections with an explicit table: " . count($all) . "\n";
$nested = array_filter($all, fn($c) => $c['parentTable'] !== null);
echo "  nested (parent is another collection): " . count($nested) . "\n\n";

// Group by exact signature.
$groups = [];
foreach ($all as $c) { $groups[$c['signatureKey']][] = $c['table']; }
uasort($groups, fn($a, $b) => count($b) <=> count($a));

$multi = array_filter($groups, fn($g) => count($g) >= 2);
echo "identical-signature groups with >=2 tables: " . count($multi) . "\n";
echo "tables inside them: " . array_sum(array_map('count', $multi)) . "\n\n";

foreach ($multi as $sig => $tables) {
    printf("%2d  %-58s\n", count($tables), $sig === '' ? '(no child fields)' : substr($sig, 0, 58));
    foreach ($tables as $t) {
        $c = $all[$t];
        printf("      %-42s %s%s\n", $t, $c['element'], $c['parentTable'] ? "  [nested under {$c['parentTable']}]" : '');
    }
}

file_put_contents($root . '/Build/Data/collection-signatures.json', json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
