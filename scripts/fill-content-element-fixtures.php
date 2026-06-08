<?php

declare(strict_types=1);

/**
 * Fill missing keys in Content Block fixture.json files.
 *
 * Usage:
 *   php scripts/fill-content-element-fixtures.php
 *   php scripts/fill-content-element-fixtures.php --dry-run
 *   php scripts/fill-content-element-fixtures.php /path/to/repo
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Seeding\StyleguideJsonFixtureCompleter;

$dryRun = in_array('--dry-run', $argv, true);
$rootArgument = null;
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with((string)$argument, '--')) {
        continue;
    }
    $rootArgument = (string)$argument;
    break;
}
$root = rtrim($rootArgument ?? dirname(__DIR__), '/');
$elementsDir = $root . '/ContentBlocks/ContentElements';

if (!is_dir($elementsDir)) {
    fwrite(STDERR, "ContentBlocks/ContentElements not found at $elementsDir\n");
    exit(2);
}

$completer = new StyleguideJsonFixtureCompleter();
$updated = 0;
$skipped = 0;

foreach (scandir($elementsDir) as $entry) {
    if ($entry === '.' || $entry === '..') {
        continue;
    }

    $dir = $elementsDir . '/' . $entry;
    if (!is_dir($dir)) {
        continue;
    }

    $configPath = $dir . '/config.yaml';
    $fixturePath = $dir . '/fixture.json';
    if (!is_file($configPath) || !is_file($fixturePath)) {
        $skipped++;
        continue;
    }

    $config = Yaml::parseFile($configPath);
    if (!is_array($config)) {
        $skipped++;
        continue;
    }

    $fixture = json_decode((string)file_get_contents($fixturePath), true);
    if (!is_array($fixture)) {
        $skipped++;
        continue;
    }

    $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig(
        ContentBlockDefinitionRegistry::normalizeStringKeyedArray($config),
    );
    $typeName = is_string($config['typeName'] ?? null) && $config['typeName'] !== ''
        ? $config['typeName']
        : 'desiderio_' . str_replace('-', '', $entry);

    $completed = $completer->complete($typeName, $entry, $definition, $fixture);
    if ($completed === $fixture) {
        continue;
    }

    $encoded = json_encode($completed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
    if (!$dryRun) {
        file_put_contents($fixturePath, $encoded);
    }
    $updated++;
}

fwrite(STDOUT, json_encode([
    'dryRun' => $dryRun,
    'updated' => $updated,
    'skipped' => $skipped,
], JSON_PRETTY_PRINT) . "\n");
