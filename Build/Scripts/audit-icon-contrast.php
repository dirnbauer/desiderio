#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$iconFiles = collectIconFiles($root);

$errors = [];

foreach ($iconFiles as $file) {
    $relative = substr($file, strlen($root) + 1);
    $svg = (string)file_get_contents($file);
    $lower = strtolower($svg);

    assertContains($svg, 'icon-root', $relative, 'is missing the icon-root class', $errors);
    assertContains($svg, 'color:var(--icon-color-primary,currentColor)', $relative, 'must use the TYPO3 primary icon variable without a hard-coded fallback', $errors);
    assertContains($svg, '--icon-color-accent', $relative, 'is missing the TYPO3 accent icon variable', $errors);
    assertContains($svg, 'currentColor', $relative, 'does not preserve currentColor inheritance', $errors);

    foreach (['#000', '#fff', 'black', 'white'] as $forbidden) {
        if (str_contains($lower, $forbidden)) {
            $errors[] = sprintf('%s contains forbidden fixed color "%s"', $relative, $forbidden);
        }
    }

    if (preg_match_all('/(?:fill|stroke)="(#[0-9a-fA-F]{3,8})"/', $svg, $matches)) {
        foreach (array_unique($matches[1]) as $hex) {
            $errors[] = sprintf('%s contains fixed SVG paint "%s"', $relative, $hex);
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

printf(
    "Checked %d SVG icons. All icon colors inherit TYPO3/shadcn tokens without fixed fallbacks.%s",
    count($iconFiles),
    PHP_EOL
);

/**
 * @return list<string>
 */
function collectIconFiles(string $root): array
{
    $files = [];
    foreach (glob($root . '/ContentBlocks/ContentElements/*/assets/icon.svg') ?: [] as $file) {
        $files[] = $file;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/Resources/Public/Icons')) as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'svg') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);
    return array_values(array_unique($files));
}

/**
 * @param list<string> $errors
 */
function assertContains(string $haystack, string $needle, string $file, string $message, array &$errors): void
{
    if (!str_contains($haystack, $needle)) {
        $errors[] = sprintf('%s %s', $file, $message);
    }
}
