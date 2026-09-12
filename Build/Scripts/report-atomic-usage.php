#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Read-only report on how the content elements use the atomic component
 * library. Prints
 *
 *   1. a histogram of <d:…> components per content element (which atoms,
 *      molecules and layouts each CE composes, and which components are
 *      used by how many CEs), and
 *   2. class-attribute "signatures" (the sorted set of Tailwind utilities of
 *      one element) that recur in at least N content elements — the raw
 *      markup that keeps being copy-pasted and is therefore a molecule
 *      candidate.
 *
 * Usage: php Build/Scripts/report-atomic-usage.php [--min=8] [--json]
 */

$root = dirname(__DIR__, 2);
$minRecurrence = 8;
$asJson = false;
foreach (array_slice($argv, 1) as $argument) {
    if (preg_match('/^--min=(\d+)$/', $argument, $match) === 1) {
        $minRecurrence = (int)$match[1];
    } elseif ($argument === '--json') {
        $asJson = true;
    }
}

$templates = glob($root . '/ContentBlocks/ContentElements/*/templates/frontend.html') ?: [];
sort($templates);

$perElement = [];
$componentUsage = [];
$signatures = [];

foreach ($templates as $template) {
    $element = basename(dirname(dirname($template)));
    $source = (string)file_get_contents($template);
    $source = (string)preg_replace('/<f:comment>.*?<\/f:comment>/s', '', $source);

    preg_match_all('/<d:([a-z]+\.[a-zA-Z]+)/', $source, $matches);
    $counts = array_count_values($matches[1]);
    ksort($counts);
    $perElement[$element] = $counts;
    foreach (array_keys($counts) as $component) {
        $componentUsage[$component] = ($componentUsage[$component] ?? 0) + 1;
    }

    // Raw HTML elements (not d: components) with a class attribute: signature = tag + sorted utilities.
    preg_match_all('/<([a-z][a-z0-9]*)\b[^>]*?\bclass="([^"{}]+)"/', $source, $rawMatches, PREG_SET_ORDER);
    $seenInElement = [];
    foreach ($rawMatches as $rawMatch) {
        $classes = preg_split('/\s+/', trim($rawMatch[2])) ?: [];
        $classes = array_values(array_unique(array_filter($classes, static fn(string $class): bool => $class !== '' && !str_contains($class, '__'))));
        if (count($classes) < 3) {
            continue;
        }
        sort($classes);
        $signature = $rawMatch[1] . ' ' . implode(' ', $classes);
        if (isset($seenInElement[$signature])) {
            continue;
        }
        $seenInElement[$signature] = true;
        $signatures[$signature][] = $element;
    }
}

arsort($componentUsage);
$recurring = array_filter($signatures, static fn(array $elements): bool => count($elements) >= $minRecurrence);
uasort($recurring, static fn(array $a, array $b): int => count($b) <=> count($a));

if ($asJson) {
    echo json_encode([
        'elements' => count($templates),
        'componentUsage' => $componentUsage,
        'perElement' => $perElement,
        'recurringSignatures' => array_map(static fn(array $elements): array => ['count' => count($elements), 'elements' => $elements], $recurring),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    exit(0);
}

printf("Content elements: %d\n\n", count($templates));
printf("Component usage (number of content elements using it):\n");
foreach ($componentUsage as $component => $count) {
    printf("  %-28s %4d\n", 'd:' . $component, $count);
}
$without = array_keys(array_filter($perElement, static fn(array $counts): bool => $counts === []));
printf("\nContent elements without any d: component: %d%s\n", count($without), $without === [] ? '' : ' (' . implode(', ', $without) . ')');

printf("\nRaw class signatures recurring in >= %d content elements (molecule candidates):\n", $minRecurrence);
foreach ($recurring as $signature => $elements) {
    printf("  %3d  %s\n       e.g. %s\n", count($elements), $signature, implode(', ', array_slice($elements, 0, 6)));
}
if ($recurring === []) {
    echo "  none\n";
}
