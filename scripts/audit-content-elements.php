<?php

declare(strict_types=1);

/**
 * Deep audit for Desiderio Content Blocks.
 *
 * For each element under ContentBlocks/ContentElements/<name>:
 *  - Parse config.yaml schema (top-level fields + Collection child fields).
 *  - Parse fixture.json (top-level keys + Collection child keys).
 *  - Parse templates/frontend.html for {data.X} and {<alias>.X} refs and
 *    f:render.text(field: '...') with context-aware data/alias detection.
 *  - Cross-check the catalog against these standards:
 *
 *      missing_table_key          Collection field without an explicit `table:`
 *                                 → unrelated elements silently share an
 *                                 auto-derived table; renames lose data.
 *      fixture_missing_field      Configured field not in fixture.json.
 *                                 (Soft: SeedStyleguidePagesCommand fills with
 *                                 a default. Useful to spot demo-content gaps.)
 *      fixture_extra_field        Fixture key not declared in config.yaml.
 *                                 The seed silently drops it → dead data.
 *      collection_child_seed_gap  Collection child field configured but never
 *                                 seeded in any row.
 *      template_unused_field      Configured top-level field never referenced
 *                                 in templates/frontend.html.
 *      template_undeclared_field  {data.X} referenced in template but X is
 *                                 not configured.
 *      variant_field_inert        `variant` Select with no <f:case>, no
 *                                 BEM suffix usage, and no data-attribute.
 *      hardcoded_inline_style     style="..." attribute not bound to data.
 *      missing_default_select     Select field without a default and not
 *                                 required.
 *      hardcoded_color            Raw #hex, oklch(), rgb(), or hsl() in
 *                                 element CSS / templates outside the
 *                                 `var(--token, fallback)` icon contract.
 *
 * Usage:
 *   php scripts/audit-content-elements.php
 *   php scripts/audit-content-elements.php /path/to/repo > /tmp/audit.json
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;

$root = rtrim($argv[1] ?? dirname(__DIR__), '/');
$elementsDir = $root . '/ContentBlocks/ContentElements';

if (!is_dir($elementsDir)) {
    fwrite(STDERR, "ContentBlocks/ContentElements not found at $elementsDir\n");
    exit(2);
}

function parseYamlSimple(string $path): array
{
    $r = Yaml::parseFile($path);
    return is_array($r) ? $r : [];
}

function collectFieldDefs(array $fields, string $prefix = ''): array
{
    $out = [];
    foreach ($fields as $f) {
        if (!is_array($f) || empty($f['identifier'])) continue;
        $id = $f['identifier'];
        $type = $f['type'] ?? '';
        $key = $prefix === '' ? $id : "$prefix.$id";
        $out[$key] = $f;
        if ($type === 'Collection' && isset($f['fields']) && is_array($f['fields'])) {
            $out += collectFieldDefs($f['fields'], $key);
        }
        if ($type === 'Palette' && isset($f['fields']) && is_array($f['fields'])) {
            $out += collectFieldDefs($f['fields'], $prefix);
        }
    }
    return $out;
}

function flattenFixtureKeys(array $fixture, string $prefix = ''): array
{
    $out = [];
    foreach ($fixture as $k => $v) {
        if (str_starts_with((string)$k, '_')) continue;
        $key = $prefix === '' ? (string)$k : "$prefix.$k";
        $out[$key] = $v;
        if (is_array($v) && array_is_list($v) && isset($v[0]) && is_array($v[0])) {
            foreach ($v as $row) {
                if (!is_array($row)) continue;
                foreach ($row as $ck => $cv) {
                    if (str_starts_with((string)$ck, '_')) continue;
                    $out["$key.$ck"] = true;
                }
            }
        }
    }
    return $out;
}

function extractTemplateVarRefs(string $tpl): array
{
    $refs = ['data' => [], 'aliases' => []];
    if (preg_match_all('/\{([a-zA-Z][a-zA-Z0-9_]*)\.([a-zA-Z_][a-zA-Z0-9_.]*)\}/', $tpl, $m, PREG_SET_ORDER)) {
        foreach ($m as $hit) {
            $alias = $hit[1];
            $field = explode('.', $hit[2])[0];
            if ($alias === 'data') { $refs['data'][$field] = true; }
            else { $refs['aliases'][$alias][$field] = true; }
        }
    }
    if (preg_match_all("/f:render\.text\s*\(\s*field:\s*['\"]([a-zA-Z_][a-zA-Z0-9_]*)['\"]/", $tpl, $rm, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
        foreach ($rm as $hit) {
            $field = $hit[1][0];
            $offset = $hit[0][1];
            $window = substr($tpl, max(0, $offset - 80), 80);
            if (preg_match('/\b([a-zA-Z][a-zA-Z0-9_]*)\s*->\s*$/', $window, $am)) {
                $alias = $am[1];
                if ($alias === 'data') { $refs['data'][$field] = true; }
                else { $refs['aliases'][$alias][$field] = true; }
            } else {
                $refs['data'][$field] = true;
            }
        }
    }
    // {data.X} (Fluid context) — exclude any inside <script>...</script> blocks
    // because JS often uses a local `data` variable name unrelated to Fluid.
    $tplStripped = preg_replace('/<script\b[^>]*>.*?<\/script>/s', '', $tpl) ?? $tpl;
    if (preg_match_all("/\bdata\.([a-zA-Z_][a-zA-Z0-9_]*)/", $tplStripped, $rm3)) {
        foreach ($rm3[1] as $n) { $refs['data'][$n] = true; }
    }
    return $refs;
}

function findVariantBranches(string $tpl): array
{
    $branches = [];
    if (preg_match_all('/<f:case\s+value=["\']([^"\']+)["\']/', $tpl, $m)) {
        foreach ($m[1] as $v) $branches[$v] = true;
    }
    if (preg_match('/(?:--|-)\{data\.variant[^}]*\}/', $tpl)) {
        $branches['__suffix_class'] = true;
    }
    if (preg_match('/data-variant=["\']?\{data\.variant/', $tpl)) {
        $branches['__data_attr'] = true;
    }
    return $branches;
}

$problems = [];
$summary = [
    'total' => 0,
    'missing_table_key' => 0,
    'fixture_missing_field' => 0,
    'fixture_extra_field' => 0,
    'collection_child_seed_gap' => 0,
    'template_unused_field' => 0,
    'template_undeclared_field' => 0,
    'variant_field_inert' => 0,
    'hardcoded_inline_style' => 0,
    'missing_default_select' => 0,
    'hardcoded_color' => 0,
    'no_template_at_all' => 0,
];

foreach (scandir($elementsDir) as $entry) {
    if ($entry === '.' || $entry === '..') continue;
    $dir = "$elementsDir/$entry";
    if (!is_dir($dir)) continue;
    $cfgPath = "$dir/config.yaml";
    if (!is_file($cfgPath)) continue;

    $summary['total']++;
    $cfg = parseYamlSimple($cfgPath);
    $defs = collectFieldDefs($cfg['fields'] ?? []);
    $topDefs = [];
    foreach ($defs as $key => $def) {
        if (!str_contains($key, '.')) { $topDefs[$key] = $def; }
    }

    $fixturePath = "$dir/fixture.json";
    $fixture = is_file($fixturePath) ? (json_decode((string)file_get_contents($fixturePath), true) ?: []) : [];
    $fixtureKeys = flattenFixtureKeys($fixture);

    $tplPath = "$dir/templates/frontend.html";
    if (!is_file($tplPath)) {
        $problems[$entry][] = ['type' => 'no_template_at_all'];
        $summary['no_template_at_all']++;
        continue;
    }
    $tpl = (string)file_get_contents($tplPath);
    $refs = extractTemplateVarRefs($tpl);

    foreach ($defs as $key => $def) {
        if (($def['type'] ?? '') === 'Collection' && !str_contains($key, '.') && empty($def['table'])) {
            $problems[$entry][] = ['type' => 'missing_table_key', 'field' => $key];
            $summary['missing_table_key']++;
        }
    }

    foreach ($topDefs as $key => $def) {
        $type = $def['type'] ?? '';
        if (in_array($type, ['Tab', 'Linebreak', 'Palette'], true)) continue;
        if (!array_key_exists($key, $fixtureKeys)) {
            $problems[$entry][] = ['type' => 'fixture_missing_field', 'field' => $key, 'fieldType' => $type];
            $summary['fixture_missing_field']++;
        }
    }

    foreach ($defs as $key => $def) {
        if (!str_contains($key, '.')) continue;
        [$parent, $child] = explode('.', $key, 2);
        if (str_contains($child, '.')) continue;
        $childRows = $fixture[$parent] ?? null;
        if (!is_array($childRows) || !$childRows) continue;
        $hasAny = false;
        foreach ($childRows as $row) {
            if (is_array($row) && array_key_exists($child, $row)) { $hasAny = true; break; }
        }
        if (!$hasAny) {
            $problems[$entry][] = ['type' => 'collection_child_seed_gap', 'field' => $key];
            $summary['collection_child_seed_gap']++;
        }
    }

    foreach ($fixture as $k => $_) {
        if (str_starts_with((string)$k, '_')) continue;
        if (!isset($topDefs[$k])) {
            $problems[$entry][] = ['type' => 'fixture_extra_field', 'field' => (string)$k];
            $summary['fixture_extra_field']++;
        }
    }

    $tplDataRefs = $refs['data'];
    foreach ($topDefs as $key => $def) {
        $type = $def['type'] ?? '';
        if (in_array($type, ['Tab', 'Linebreak', 'Palette'], true)) continue;
        $isUseExisting = !empty($def['useExistingField']);
        if ($isUseExisting && in_array($key, ['CType', 'sys_language_uid'], true)) continue;
        if (!isset($tplDataRefs[$key])) {
            $problems[$entry][] = ['type' => 'template_unused_field', 'field' => $key, 'fieldType' => $type];
            $summary['template_unused_field']++;
        }
    }

    foreach ($tplDataRefs as $field => $_) {
        if (in_array($field, ['uid', 'pid', 'header', 'header_position', 'header_link', 'header_layout', 'subheader', 'CType', 'tx_desiderio'], true)) continue;
        if (!isset($topDefs[$field])) {
            $problems[$entry][] = ['type' => 'template_undeclared_field', 'field' => $field];
            $summary['template_undeclared_field']++;
        }
    }

    if (isset($topDefs['variant'])) {
        $variantDef = $topDefs['variant'];
        $type = $variantDef['type'] ?? '';
        if ($type === 'Select') {
            $branches = findVariantBranches($tpl);
            if (count($branches) === 0) {
                $problems[$entry][] = ['type' => 'variant_field_inert', 'field' => 'variant'];
                $summary['variant_field_inert']++;
            } else {
                $items = array_map(static fn ($it) => $it['value'] ?? null, $variantDef['items'] ?? []);
                $items = array_filter($items, static fn ($v) => $v !== null);
                $hasReal = false;
                foreach ($branches as $b => $_) {
                    if (str_starts_with($b, '__')) { $hasReal = true; break; }
                    if (in_array($b, $items, true)) { $hasReal = true; break; }
                }
                if (!$hasReal) {
                    $problems[$entry][] = ['type' => 'variant_field_inert', 'field' => 'variant', 'reason' => 'no branch matches configured options'];
                    $summary['variant_field_inert']++;
                }
            }
        }
    }

    if (preg_match_all('/style=("|\')([^"\']*)\1/', $tpl, $sm)) {
        foreach ($sm[2] as $styleVal) {
            if (str_contains($styleVal, '{')) continue;
            if (preg_match('/^\s*display:\s*none\s*;?\s*$/', $styleVal)) continue;
            $problems[$entry][] = ['type' => 'hardcoded_inline_style', 'value' => $styleVal];
            $summary['hardcoded_inline_style']++;
        }
    }

    foreach ($topDefs as $key => $def) {
        $type = $def['type'] ?? '';
        if ($type !== 'Select') continue;
        if (!isset($def['default']) && empty($def['required'])) {
            $problems[$entry][] = ['type' => 'missing_default_select', 'field' => $key];
            $summary['missing_default_select']++;
        }
    }

    foreach ([$tplPath, "$dir/assets/frontend.css"] as $f) {
        if (!is_file($f)) continue;
        $content = (string)file_get_contents($f);
        if (preg_match_all('/(?<!var\()\b(?:#[0-9a-fA-F]{3,8}\b|oklch\([^)]+\)|rgba?\([^)]+\)|hsla?\([^)]+\))/', $content, $cm)) {
            foreach ($cm[0] as $hit) {
                $pos = strpos($content, $hit);
                if ($pos !== false) {
                    $context = substr($content, max(0, $pos - 30), 60);
                    if (preg_match('/var\(--[\w-]+\s*,\s*#[0-9a-fA-F]{3,8}\)/', $context)) continue;
                }
                $problems[$entry][] = ['type' => 'hardcoded_color', 'file' => basename($f), 'value' => $hit];
                $summary['hardcoded_color']++;
            }
        }
    }
}

echo json_encode([
    'summary' => $summary,
    'problems' => $problems,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
