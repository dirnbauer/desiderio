<?php

declare(strict_types=1);

/**
 * Classify plain Textarea fields across all Content Blocks and convert the
 * safe candidates to RTE (enableRichtext: true).
 *
 * A field is converted ONLY when every gate passes; anything uncertain keeps
 * the current plain-textarea solution:
 *   - identifier is on the prose allowlist (description/content/answer/...)
 *   - rows >= 2 (rows: 1 fields are titles, labels, button copy)
 *   - identifier is not on the data denylist (json, code, points, icon, ...)
 *   - the frontend template renders it exclusively via f:render.text() in
 *     block context — never inside an HTML attribute, f:split, <pre>/<code>,
 *     or as raw {data.x} output
 *
 * Usage:
 *   php scripts/convert-textarea-to-rte.php              # analyze, human report
 *   php scripts/convert-textarea-to-rte.php --json       # analyze, JSON report
 *   php scripts/convert-textarea-to-rte.php --apply      # rewrite config.yaml,
 *       fixture.json, frontend templates + element CSS, write the DB manifest
 *   php scripts/convert-textarea-to-rte.php --manifest=path/to/manifest.json
 *
 * --apply emits scripts/rte-conversion-manifest.json which feeds the DB
 * migration command: vendor/bin/typo3 desiderio:migrate-rte-content
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Rte\RteHtmlConverter;

$apply = in_array('--apply', $argv, true);
$asJson = in_array('--json', $argv, true);
$manifestPath = __DIR__ . '/rte-conversion-manifest.json';
foreach ($argv as $arg) {
    if (str_starts_with($arg, '--manifest=')) {
        $manifestPath = substr($arg, strlen('--manifest='));
    }
}

$repoRoot = dirname(__DIR__);
$elementsDir = $repoRoot . '/ContentBlocks/ContentElements';

/**
 * Identifiers (exact) that hold editorial prose and benefit from the RTE.
 * Suffix matches cover collection children like card_description.
 */
const ALLOWLIST_EXACT = [
    'description', 'content', 'body', 'bodytext', 'summary', 'intro',
    'intro_text', 'answer', 'step_content', 'message', 'excerpt', 'details',
    'paragraph', 'about_text', 'bio', 'text',
];
const ALLOWLIST_SUFFIX = ['_description', '_content', '_summary', '_intro', '_body'];

/**
 * Identifiers that must never become RTE: machine-parsed payloads, code,
 * line-format data, identifiers, quotes (kept plain by design decision).
 */
const DENYLIST_PATTERN = '/(json|code|snippet|embed|icon|svg|csv|points|chart|data$|_id$|key$|quote|address|label|caption|alt(_|$)|aria|url|slug|time|date|price|badge|eyebrow|meta)/';

if (!is_dir($elementsDir)) {
    fwrite(STDERR, "Content elements directory not found: {$elementsDir}\n");
    exit(1);
}

/**
 * Walk the field tree (top-level + Collection children, recursive) and
 * return every Textarea as [path, fieldArray, collectionTable|null].
 */
function collectTextareaFields(array $fields, string $pathPrefix = '', ?string $collectionTable = null): array
{
    $result = [];
    foreach ($fields as $field) {
        if (!is_array($field) || !isset($field['identifier'])) {
            continue;
        }
        $path = $pathPrefix === '' ? $field['identifier'] : $pathPrefix . '.' . $field['identifier'];
        $type = $field['type'] ?? null;
        if ($type === 'Textarea') {
            $result[] = ['path' => $path, 'field' => $field, 'collectionTable' => $collectionTable];
        }
        if ($type === 'Collection' && isset($field['fields']) && is_array($field['fields'])) {
            $childTable = is_string($field['table'] ?? null) ? $field['table'] : null;
            $result = array_merge($result, collectTextareaFields($field['fields'], $path, $childTable));
        }
    }
    return $result;
}

function isAllowlisted(string $identifier): bool
{
    if (in_array($identifier, ALLOWLIST_EXACT, true)) {
        return true;
    }
    foreach (ALLOWLIST_SUFFIX as $suffix) {
        if (str_ends_with($identifier, $suffix)) {
            return true;
        }
    }
    return false;
}

/**
 * Is the byte offset inside an HTML tag (i.e. an attribute position)?
 */
function insideTag(string $html, int $offset): bool
{
    $lastOpen = strrpos(substr($html, 0, $offset), '<');
    $lastClose = strrpos(substr($html, 0, $offset), '>');
    return $lastOpen !== false && ($lastClose === false || $lastOpen > $lastClose);
}

/**
 * If inside a tag, return the attribute name the offset sits in, or null.
 */
function attributeNameAt(string $html, int $offset): ?string
{
    $before = substr($html, 0, $offset);
    if (preg_match('/([a-zA-Z0-9:_-]+)\s*=\s*"[^"]*$/', $before, $m)) {
        return $m[1];
    }
    return null;
}

function insidePreOrCode(string $html, int $offset): bool
{
    foreach (['pre', 'code'] as $tag) {
        $open = strripos(substr($html, 0, $offset), '<' . $tag);
        if ($open === false) {
            continue;
        }
        $close = strripos(substr($html, 0, $offset), '</' . $tag);
        if ($close === false || $close < $open) {
            return true;
        }
    }
    return false;
}

/**
 * Inspect every usage of the identifier in the frontend templates.
 * Returns [verdict(null = ok), reason, renderTextOffsets[]].
 */
function analyzeTemplateUsage(string $identifier, array $templates): array
{
    $renderTextSeen = false;
    foreach ($templates as $file => $html) {
        // 1) f:render.text(field: 'id') occurrences — required, block context only.
        $pattern = '/f:render\.text\(\s*field:\s*\'' . preg_quote($identifier, '/') . '\'\s*\)/';
        if (preg_match_all($pattern, $html, $m, PREG_OFFSET_CAPTURE)) {
            foreach ($m[0] as [$match, $offset]) {
                $renderTextSeen = true;
                if (insidePreOrCode($html, $offset)) {
                    return ['keep', 'rendered inside <pre>/<code>', []];
                }
                // render.text inside a tag means attribute embedding — unsafe.
                // The expression itself starts at the surrounding "{", walk back.
                $exprStart = strrpos(substr($html, 0, $offset), '{');
                if ($exprStart !== false && insideTag($html, $exprStart)) {
                    return ['keep', 'f:render.text used inside an HTML attribute', []];
                }
            }
        }
        // 2) Any other access: {data.id}, {item.id ->}, f:split, variables...
        $accessPattern = '/\{?[a-zA-Z_][a-zA-Z0-9_]*\.' . preg_quote($identifier, '/') . '\b/';
        if (preg_match_all($accessPattern, $html, $m2, PREG_OFFSET_CAPTURE)) {
            foreach ($m2[0] as [$match, $offset]) {
                if (insideTag($html, $offset)) {
                    $attr = attributeNameAt($html, $offset);
                    if ($attr === 'condition') {
                        continue; // <f:if condition="{data.id}"> stays valid
                    }
                    return ['keep', sprintf('used in attribute "%s" (%s)', $attr ?? '?', basename($file)), []];
                }
                // Text-node context: raw {data.id} output or inline expression.
                // After conversion this would print escaped HTML — unsafe.
                return ['keep', sprintf('raw fluid output/expression usage (%s)', basename($file)), []];
            }
        }
    }
    if (!$renderTextSeen) {
        return ['keep', 'not rendered via f:render.text in frontend templates', []];
    }
    return [null, '', []];
}

/**
 * Rewrite the immediate wrapper of a converted field so RTE block output
 * stays valid HTML:
 *   <p ...>{x -> f:render.text(field: 'id')}</p>      -> <div ... d-richtext>
 *   <d:atom.typography ...>{...}</d:atom.typography>  -> + tag="div"
 * Returns [newHtml, status] where status is rewritten|already-block|manual.
 */
function rewriteWrapper(string $html, string $identifier): array
{
    $expr = '\{[a-zA-Z_][a-zA-Z0-9_]*\s*->\s*f:render\.text\(\s*field:\s*\'' . preg_quote($identifier, '/') . '\'\s*\)\}';

    // Case A: <p ...>{expr}</p>
    $pA = '/<p([^>]*)>(\s*' . $expr . '\s*)<\/p>/';
    if (preg_match($pA, $html)) {
        $html = preg_replace_callback($pA, static function (array $m): string {
            $attrs = $m[1];
            if (preg_match('/class="([^"]*)"/', $attrs)) {
                $attrs = preg_replace('/class="([^"]*)"/', 'class="$1 d-richtext"', $attrs, 1);
            } else {
                $attrs .= ' class="d-richtext"';
            }
            return '<div' . $attrs . '>' . $m[2] . '</div>';
        }, $html);
        return [$html, 'rewritten'];
    }

    // Case B: typography atom defaulting to or explicitly using tag="p" -> tag="div"
    $pB = '/<d:atom\.typography((?:(?!tag=)[^>])*)>(\s*' . $expr . '\s*)<\/d:atom\.typography>/';
    $pBExplicit = '/<d:atom\.typography\s+tag="p"([^>]*)>(\s*' . $expr . '\s*)<\/d:atom\.typography>/';
    if (preg_match($pBExplicit, $html)) {
        $pB = $pBExplicit;
    }
    if (preg_match($pB, $html)) {
        $html = preg_replace_callback($pB, static function (array $m): string {
            $attrs = $m[1];
            if (preg_match('/class="([^"]*)"/', $attrs)) {
                $attrs = preg_replace('/class="([^"]*)"/', 'class="$1 d-richtext"', $attrs, 1);
            } else {
                $attrs .= ' class="d-richtext"';
            }
            return '<d:atom.typography tag="div"' . $attrs . '>' . $m[2] . '</d:atom.typography>';
        }, $html);
        return [$html, 'rewritten'];
    }

    // Case C: already inside a block container (div/blockquote/...): valid as-is,
    // but flag so the element CSS / d-richtext class can be reviewed manually.
    return [$html, 'already-block'];
}

const RICHTEXT_CSS_MARKER = '/* d-richtext (generated by scripts/convert-textarea-to-rte.php) */';
const RICHTEXT_CSS = RICHTEXT_CSS_MARKER . "\n"
    . ".d-richtext > :first-child { margin-block-start: 0; }\n"
    . ".d-richtext > :last-child { margin-block-end: 0; }\n"
    . ".d-richtext p { margin-block: 0 var(--d-spacing-sm); }\n"
    . ".d-richtext ul, .d-richtext ol { margin-block: var(--d-spacing-sm) 0; padding-inline-start: var(--d-spacing-lg); }\n"
    . ".d-richtext ul { list-style: disc; }\n"
    . ".d-richtext ol { list-style: decimal; }\n"
    . ".d-richtext li { margin-block-end: var(--d-spacing-2xs); }\n"
    . ".d-richtext a { color: var(--primary); text-decoration: underline; text-underline-offset: 2px; }\n";

/**
 * Insert "enableRichtext: true" after the "type: Textarea" line of the field
 * whose identifier matches, counting Textarea occurrences of that identifier
 * in file order so collection children resolve to the right block.
 */
function patchConfigYaml(string $yaml, string $identifier, int $occurrence): ?string
{
    $lines = explode("\n", $yaml);
    $seen = 0;
    $count = count($lines);
    for ($i = 0; $i < $count; $i++) {
        if (!preg_match('/^(\s*)identifier:\s*' . preg_quote($identifier, '/') . '\s*$/', $lines[$i], $m)) {
            continue;
        }
        $indent = $m[1];
        // The type: line lives in the same mapping block (same indentation).
        for ($j = $i + 1; $j < $count; $j++) {
            $line = $lines[$j];
            if (preg_match('/^\s*-\s*$/', $line) || preg_match('/^' . $indent . 'identifier:/', $line)) {
                break; // next field reached, no type matched
            }
            if (preg_match('/^' . $indent . 'type:\s*Textarea\s*$/', $line)) {
                if ($seen === $occurrence) {
                    // Already enabled? Look ahead within the block.
                    for ($k = $j + 1; $k < $count; $k++) {
                        if (preg_match('/^\s*-\s*$/', $lines[$k]) || !str_starts_with($lines[$k], $indent)) {
                            break;
                        }
                        if (preg_match('/^' . $indent . 'enableRichtext:/', $lines[$k])) {
                            return null;
                        }
                    }
                    array_splice($lines, $j + 1, 0, [
                        $indent . 'enableRichtext: true',
                        $indent . 'richtextConfiguration: desiderio',
                    ]);
                    return implode("\n", $lines);
                }
                $seen++;
                break;
            }
        }
    }
    return null;
}

/** Convert fixture values for a dotted field path, recursing into item arrays. */
function convertFixtureValue(array &$fixture, array $pathSegments): int
{
    $segment = array_shift($pathSegments);
    $changed = 0;
    if ($pathSegments === []) {
        if (isset($fixture[$segment]) && is_string($fixture[$segment]) && $fixture[$segment] !== ''
            && !RteHtmlConverter::looksLikeHtml($fixture[$segment])) {
            $fixture[$segment] = RteHtmlConverter::convert($fixture[$segment]);
            $changed++;
        }
        return $changed;
    }
    if (isset($fixture[$segment]) && is_array($fixture[$segment])) {
        foreach ($fixture[$segment] as &$item) {
            if (is_array($item)) {
                $changed += convertFixtureValue($item, $pathSegments);
            }
        }
        unset($item);
    }
    return $changed;
}

// ---------------------------------------------------------------------------

$report = [];
$manifest = [];
$stats = ['elements' => 0, 'textareas' => 0, 'already_rte' => 0, 'convert' => 0, 'keep' => 0];

$elementDirs = glob($elementsDir . '/*', GLOB_ONLYDIR) ?: [];
sort($elementDirs);

foreach ($elementDirs as $dir) {
    $element = basename($dir);
    $configFile = $dir . '/config.yaml';
    if (!is_file($configFile)) {
        continue;
    }
    $stats['elements']++;
    $config = Yaml::parseFile($configFile);
    $ctype = $config['typeName'] ?? ('desiderio_' . str_replace('-', '_', $element));
    $textareas = collectTextareaFields($config['fields'] ?? []);

    // Frontend templates only — backend previews already stripTags() safely.
    $templates = [];
    foreach (glob($dir . '/templates/**/*.html') ?: [] as $tpl) {
        $templates[$tpl] = (string)file_get_contents($tpl);
    }
    $frontendFile = $dir . '/templates/frontend.html';
    if (is_file($frontendFile)) {
        $templates[$frontendFile] = (string)file_get_contents($frontendFile);
    }
    unset($templates[$dir . '/templates/backend-preview.fluid.html']);

    $identifierUseCount = [];
    foreach ($textareas as $entry) {
        $identifierUseCount[$entry['field']['identifier']] =
            ($identifierUseCount[$entry['field']['identifier']] ?? 0) + 1;
    }

    $occurrenceIndex = [];
    foreach ($textareas as $entry) {
        $stats['textareas']++;
        $field = $entry['field'];
        $identifier = $field['identifier'];
        $path = $entry['path'];
        $occurrence = $occurrenceIndex[$identifier] ?? 0;
        $occurrenceIndex[$identifier] = $occurrence + 1;

        $row = [
            'element' => $element, 'ctype' => $ctype, 'path' => $path,
            'rows' => $field['rows'] ?? null, 'verdict' => 'keep', 'reason' => '',
        ];

        if (($field['enableRichtext'] ?? false) === true) {
            $row['verdict'] = 'already-rte';
            $stats['already_rte']++;
            $report[] = $row;
            continue;
        }
        if (($field['rows'] ?? 1) < 2) {
            $row['reason'] = 'rows < 2 — single-line micro copy';
            $stats['keep']++;
            $report[] = $row;
            continue;
        }
        if (preg_match(DENYLIST_PATTERN, $identifier)) {
            $row['reason'] = 'denylisted identifier (data/code/quote/meta semantics)';
            $stats['keep']++;
            $report[] = $row;
            continue;
        }
        if (!isAllowlisted($identifier)) {
            $row['reason'] = 'not on prose allowlist — keep current solution';
            $stats['keep']++;
            $report[] = $row;
            continue;
        }
        if ($identifierUseCount[$identifier] > 1) {
            $row['reason'] = 'ambiguous: identifier appears multiple times in element';
            $stats['keep']++;
            $report[] = $row;
            continue;
        }
        [$verdict, $reason] = analyzeTemplateUsage($identifier, $templates);
        if ($verdict === 'keep') {
            $row['reason'] = $reason;
            $stats['keep']++;
            $report[] = $row;
            continue;
        }

        $row['verdict'] = 'convert';
        $stats['convert']++;

        if ($apply) {
            // 1) config.yaml
            $yamlSource = (string)file_get_contents($configFile);
            $patched = patchConfigYaml($yamlSource, $identifier, $occurrence);
            if ($patched === null) {
                $row['verdict'] = 'keep';
                $row['reason'] = 'config.yaml patch failed — manual review';
                $stats['convert']--;
                $stats['keep']++;
                $report[] = $row;
                continue;
            }
            file_put_contents($configFile, $patched);

            // 2) frontend template wrapper
            $wrapperStatus = 'no-template';
            if (is_file($frontendFile)) {
                $tplSource = (string)file_get_contents($frontendFile);
                [$newTpl, $wrapperStatus] = rewriteWrapper($tplSource, $identifier);
                if ($newTpl !== $tplSource) {
                    file_put_contents($frontendFile, $newTpl);
                }
            }
            $row['wrapper'] = $wrapperStatus;

            // 3) element CSS for d-richtext (only when the class was injected)
            if ($wrapperStatus === 'rewritten') {
                $cssFile = $dir . '/assets/frontend.css';
                $css = is_file($cssFile) ? (string)file_get_contents($cssFile) : '';
                if (!str_contains($css, RICHTEXT_CSS_MARKER)) {
                    file_put_contents($cssFile, rtrim($css) . "\n\n" . RICHTEXT_CSS);
                }
            }

            // 4) fixture.json
            $fixtureFile = $dir . '/fixture.json';
            if (is_file($fixtureFile)) {
                $fixture = json_decode((string)file_get_contents($fixtureFile), true);
                if (is_array($fixture) && convertFixtureValue($fixture, explode('.', $path)) > 0) {
                    file_put_contents(
                        $fixtureFile,
                        json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n"
                    );
                    $row['fixture'] = 'converted';
                }
            }
        }

        // DB manifest entry: tt_content columns are shared across CTypes
        // (prefixFields: false), so tt_content entries MUST filter by CType.
        $segments = explode('.', $path);
        if (count($segments) === 1) {
            $manifest[] = ['table' => 'tt_content', 'column' => $identifier, 'ctype' => $ctype, 'element' => $element];
        } elseif ($entry['collectionTable'] !== null) {
            $manifest[] = ['table' => $entry['collectionTable'], 'column' => $identifier, 'ctype' => null, 'element' => $element];
        } else {
            $row['manifest'] = 'skipped: collection without explicit table';
        }

        $report[] = $row;
    }
}

if ($apply) {
    file_put_contents(
        $manifestPath,
        json_encode(['generated_by' => 'scripts/convert-textarea-to-rte.php', 'fields' => $manifest], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
    );
}

if ($asJson) {
    echo json_encode(['stats' => $stats, 'fields' => $report], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

printf(
    "Elements: %d | Textarea fields: %d | already RTE: %d | CONVERT: %d | keep plain: %d\n\n",
    $stats['elements'],
    $stats['textareas'],
    $stats['already_rte'],
    $stats['convert'],
    $stats['keep']
);
echo "=== CONVERT ===\n";
foreach ($report as $row) {
    if ($row['verdict'] === 'convert') {
        printf("  %-32s %-28s rows:%s%s\n", $row['element'], $row['path'], $row['rows'] ?? '-', isset($row['wrapper']) ? ' wrapper:' . $row['wrapper'] : '');
    }
}
echo "\n=== KEEP (by reason) ===\n";
$byReason = [];
foreach ($report as $row) {
    if ($row['verdict'] === 'keep') {
        $byReason[$row['reason']][] = $row['element'] . ':' . $row['path'];
    }
}
uasort($byReason, static fn(array $a, array $b): int => count($b) <=> count($a));
foreach ($byReason as $reason => $fields) {
    printf("  %4d  %s\n", count($fields), $reason);
}
if ($apply) {
    echo "\nManifest written to {$manifestPath}\n";
    echo "Next: vendor/bin/typo3 desiderio:migrate-rte-content (dry-run by default)\n";
}
exit(0);
