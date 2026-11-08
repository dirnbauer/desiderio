<?php

declare(strict_types=1);

/**
 * Row markers for the Visual Editor in every Content Block template.
 *
 * Every <f:for> over a Collection field whose body renders exactly one root
 * element per entry gets the f:mark.item ViewHelper of Visual Editor
 * Enhancements on that root, so the editor can find the entry ("row") on the
 * page. Outside edit mode the ViewHelper renders nothing: published pages
 * stay byte-identical. The rules live in Classes/Templates/CollectionMarker
 * (shared with Tests/Unit/ContentBlockCollectionMarkerTest.php, which is the
 * source of truth for the counts); the loops that cannot carry a marker are
 * listed with their reasons in CollectionMarkerExceptions.
 *
 * Usage:
 *   php Build/Scripts/codemod-collection-markers.php             add missing markers
 *   php Build/Scripts/codemod-collection-markers.php --dry-run   show what would change
 *   php Build/Scripts/codemod-collection-markers.php --check     exit 1 on unmarked, unlisted loops
 *   options: --block=<name> (one Content Block), --path=<dir> (default ContentBlocks/ContentElements)
 *
 * Idempotent: a second run changes nothing.
 */

use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionLoop;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionLoopAnalyzer;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionMarkerExceptions;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionMarkerRewriter;

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

$options = getopt('', ['check', 'dry-run', 'block:', 'path:', 'help']);
if (isset($options['help'])) {
    fwrite(STDOUT, "php Build/Scripts/codemod-collection-markers.php [--check|--dry-run] [--block=<name>] [--path=<dir>]\n");
    exit(0);
}
$check = isset($options['check']);
$dryRun = isset($options['dry-run']);
$path = is_string($options['path'] ?? null) ? $options['path'] : $root . '/ContentBlocks/ContentElements';
$onlyBlock = is_string($options['block'] ?? null) ? $options['block'] : null;

$directories = glob(rtrim($path, '/') . '/*', GLOB_ONLYDIR) ?: [];
$analyzer = new CollectionLoopAnalyzer();
$exceptions = new CollectionMarkerExceptions();
$rewriter = new CollectionMarkerRewriter();

$counts = ['blocks' => 0, 'loops' => 0, 'nested' => 0, 'marked' => 0, 'exceptions' => 0, 'added' => 0, 'files' => 0];
$rules = [];
$failures = [];
$usedExceptions = [];

foreach ($directories as $directory) {
    $block = basename($directory);
    if ($onlyBlock !== null && $block !== $onlyBlock) {
        continue;
    }
    if (!is_file($directory . '/config.yaml') || !is_file($directory . '/templates/frontend.html')) {
        continue;
    }
    $analysis = $analyzer->analyzeBlock($directory, $exceptions);
    if ($analysis->loops === []) {
        continue;
    }
    ++$counts['blocks'];
    $template = $directory . '/templates/frontend.html';

    if (!$check) {
        $result = $rewriter->rewrite($analysis);
        foreach ($result['errors'] as $error) {
            $failures[] = $block . ': ' . $error;
        }
        if ($result['edits'] !== []) {
            ++$counts['files'];
            $counts['added'] += count($result['edits']);
            fwrite(STDOUT, ($dryRun ? '[dry-run] ' : '') . $block . '/templates/frontend.html: +' . count($result['edits']) . " marker(s)\n");
            foreach ($result['edits'] as $note) {
                fwrite(STDOUT, '    ' . $note . "\n");
            }
            if ($dryRun) {
                fwrite(STDOUT, lineDiff($analysis->document->source, $result['source']));
            } else {
                file_put_contents($template, $result['source']);
            }
            // Re-read what was (or would be) written: the rewrite must leave a
            // template the parser reads cleanly and in which every loop passes.
            $analysis = $analyzer->analyze($block, $result['source'], $analysis->fields, $exceptions);
        }
    }

    foreach ($analysis->failures() as $failure) {
        $failures[] = $block . ': ' . $failure;
    }
    foreach ($analysis->loops as $loop) {
        ++$counts['loops'];
        if ($loop->field->parent !== null) {
            ++$counts['nested'];
        }
        if ($loop->exception !== null) {
            ++$counts['exceptions'];
            $usedExceptions[$loop->key() . '#' . $loop->occurrence] = true;
            $usedExceptions[$loop->key()] = true;
            continue;
        }
        if ($loop->isMarked()) {
            ++$counts['marked'];
            foreach ($loop->roots as $rootNode) {
                $rule = CollectionLoop::ruleFor($rootNode);
                if ($rule === CollectionLoop::RULE_COMPONENT && str_contains((string)$rootNode->attribute('attributes'), 'f:merge(')) {
                    $rule .= ' (merged)';
                }
                $rules[$rule] = ($rules[$rule] ?? 0) + 1;
            }
        }
    }
}

if ($onlyBlock === null) {
    foreach (array_keys($exceptions->all()) as $key) {
        if (!isset($usedExceptions[$key])) {
            $failures[] = 'CollectionMarkerExceptions: "' . $key . '" matches no Collection loop (stale entry)';
        }
    }
}

ksort($rules);
fwrite(STDOUT, sprintf(
    "%d Collection loops in %d Content Blocks (%d over nested Collections): %d marked, %d listed exceptions%s\n",
    $counts['loops'],
    $counts['blocks'],
    $counts['nested'],
    $counts['marked'],
    $counts['exceptions'],
    $check ? '' : sprintf('; %s %d marker(s) in %d file(s)', $dryRun ? 'would add' : 'added', $counts['added'], $counts['files']),
));
foreach ($rules as $rule => $count) {
    fwrite(STDOUT, sprintf("    marked roots, %s: %d\n", $rule, $count));
}
if ($failures !== []) {
    fwrite(STDERR, count($failures) . " problem(s):\n  " . implode("\n  ", $failures) . "\n");
    exit(1);
}
exit(0);

/**
 * The changed lines, "-" before and "+" after (the rewrite never adds or
 * removes a line, so lines pair up by number).
 */
function lineDiff(string $before, string $after): string
{
    $old = explode("\n", $before);
    $new = explode("\n", $after);
    $out = '';
    foreach ($new as $index => $line) {
        if (($old[$index] ?? null) !== $line) {
            $out .= sprintf("    %5d - %s\n    %5d + %s\n", $index + 1, trim((string)($old[$index] ?? '')), $index + 1, trim($line));
        }
    }
    return $out;
}
