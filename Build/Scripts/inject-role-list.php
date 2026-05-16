<?php

declare(strict_types=1);

/**
 * Idempotently add role="list" to <ul> tags whose visual style strips the
 * native list role (Tailwind list-style:none via flex/grid/divide). Without
 * the explicit role, Safari/VoiceOver remove list semantics and the items
 * stop announcing as "list of N items".
 *
 * Skips tags that already have role= or that begin with <ul>/<ol> on a line
 * we cannot identify (multi-attribute pre-existing role).
 */

$root = '/Users/dirnbauer/projects/desiderio';

$candidatePatterns = [
    'flex flex-wrap',
    'flex flex-col',
    'grid gap',
    'divide-y',
    'list-disc',
    'inline-flex flex-wrap',
];

$files = [];
foreach ([
    "$root/Resources/Private/Extensions/Blog",
    "$root/Resources/Private/Extensions/News",
    "$root/Resources/Private/Extensions/Solr",
    "$root/Resources/Private/Solr",
] as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && in_array($file->getExtension(), ['html'], true)) {
            $files[] = $file->getPathname();
        }
    }
}

sort($files);
$updated = 0;

foreach ($files as $file) {
    $contents = (string) file_get_contents($file);
    $original = $contents;

    $contents = preg_replace_callback(
        '/<ul\b([^>]*?)>/',
        static function (array $match) use ($candidatePatterns): string {
            $attrs = $match[1];
            if (str_contains($attrs, 'role=')) {
                return $match[0];
            }
            $matchesPattern = false;
            foreach ($candidatePatterns as $pattern) {
                if (str_contains($attrs, $pattern)) {
                    $matchesPattern = true;
                    break;
                }
            }
            if (!$matchesPattern) {
                return $match[0];
            }
            return '<ul role="list"' . $attrs . '>';
        },
        $contents
    ) ?? $contents;

    if ($contents !== $original) {
        file_put_contents($file, $contents);
        $updated++;
        echo "Patched: $file\n";
    }
}

echo "\nTotal files patched: $updated\n";
