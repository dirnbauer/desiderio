#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$iconFiles = collectIconFiles($root);

$lightSurface = '#ffffff';
$darkSurface = '#24242a';
$lightPrimary = '#88919b';
$darkPrimary = '#f9fafb';
$minimumContrast = 3.0;

$errors = [];

foreach ($iconFiles as $file) {
    $relative = substr($file, strlen($root) + 1);
    $svg = (string)file_get_contents($file);
    $lower = strtolower($svg);

    assertContains($svg, 'icon-root', $relative, 'is missing the icon-root class', $errors);
    assertContains($svg, '--icon-color-primary', $relative, 'is missing the TYPO3 primary icon variable fallback', $errors);
    assertContains($svg, '--icon-color-accent', $relative, 'is missing the TYPO3 accent icon variable', $errors);
    assertContains($svg, 'prefers-color-scheme:dark', $relative, 'is missing a dark-mode external SVG fallback', $errors);
    assertContains($svg, $lightPrimary, $relative, 'is missing the light-mode primary fallback color', $errors);
    assertContains($svg, $darkPrimary, $relative, 'is missing the dark-mode primary fallback color', $errors);
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

$lightContrast = contrastRatio($lightPrimary, $lightSurface);
$baseDarkContrast = contrastRatio($lightPrimary, $darkSurface);
$darkContrast = contrastRatio($darkPrimary, $darkSurface);

if ($lightContrast < $minimumContrast) {
    $errors[] = sprintf(
        'Light icon fallback %s has contrast %.2f against %s, expected at least %.1f',
        $lightPrimary,
        $lightContrast,
        $lightSurface,
        $minimumContrast
    );
}

if ($baseDarkContrast < $minimumContrast) {
    $errors[] = sprintf(
        'Base external icon fallback %s has contrast %.2f against %s, expected at least %.1f',
        $lightPrimary,
        $baseDarkContrast,
        $darkSurface,
        $minimumContrast
    );
}

if ($darkContrast < $minimumContrast) {
    $errors[] = sprintf(
        'Dark icon fallback %s has contrast %.2f against %s, expected at least %.1f',
        $darkPrimary,
        $darkContrast,
        $darkSurface,
        $minimumContrast
    );
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

printf(
    "Checked %d SVG icons. Base light contrast %.2f:1, base dark contrast %.2f:1, enhanced dark contrast %.2f:1.%s",
    count($iconFiles),
    $lightContrast,
    $baseDarkContrast,
    $darkContrast,
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

function contrastRatio(string $foreground, string $background): float
{
    $fg = relativeLuminance(hexToRgb($foreground));
    $bg = relativeLuminance(hexToRgb($background));
    $lighter = max($fg, $bg);
    $darker = min($fg, $bg);

    return ($lighter + 0.05) / ($darker + 0.05);
}

/**
 * @return array{0:int,1:int,2:int}
 */
function hexToRgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2)),
    ];
}

/**
 * @param array{0:int,1:int,2:int} $rgb
 */
function relativeLuminance(array $rgb): float
{
    [$r, $g, $b] = array_map(static function (int $channel): float {
        $value = $channel / 255;

        return $value <= 0.03928
            ? $value / 12.92
            : (($value + 0.055) / 1.055) ** 2.4;
    }, $rgb);

    return (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
}
