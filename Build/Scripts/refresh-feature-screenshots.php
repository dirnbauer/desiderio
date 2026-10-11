#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Refresh existing feature screenshot references below the fixed live roots
 * 505 (Desiderio) and 69 (TYPO3 v14 blog), including their translations.
 *
 * php packages/desiderio/Build/Scripts/refresh-feature-screenshots.php \
 *   --manifest=var/screenshot-refresh-20261012/captures.json --report=var/references.json
 *
 * A copied script can use --bootstrap-root=/var/www/html --assets=/tmp/screenshots.
 * Assets may be in that folder directly or its Frontend/Backend subfolders.
 * The manifest may contain any nonempty subset of ALLOWED_SHOTS. Every requested
 * shot must match at least one existing live reference before any FAL import or
 * DataHandler write. Default is read-only; --apply also works in Production.
 * Existing reference IDs, order, content relationships and alternatives remain
 * intact unless the manifest explicitly supplies alternatives per language.
 * No old assets are overwritten or deleted, and no content is reseeded.
 */

use TYPO3\CMS\Core\Authentication\CommandLineUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

const SITE_ROOTS = [505, 69];
const ALLOWED_SHOTS = [
    'frontend-feature-desiderio-home-hero',
    'frontend-feature-desiderio-chapter-pricing',
    'frontend-feature-astryx-home',
    'frontend-feature-astryx-pricing-matcha',
    'frontend-feature-blog-list',
    'frontend-feature-blog-post',
    'frontend-feature-blog-comments',
    'frontend-feature-skillflow-catalogue',
    'frontend-feature-solr-results-facets',
    'frontend-feature-solr-active-filter',
    'frontend-feature-solr-dark-mode',
    'feature-visual-editor-field-chooser',
    'feature-visual-editor-rte-toolbar',
    'feature-visual-editor-link-helper',
    'feature-visual-editor-element-library',
    'feature-visual-editor-search',
    'feature-docx-editor-word-menu',
    'backend-visual-editor',
    'backend-page-module-hero',
    'feature-desiderio-page-module',
    'backend-content-wizard',
    'feature-desiderio-content-wizard',
];

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('This screenshot refresh must run from the command line.');
}
$options = getopt('', ['manifest:', 'bootstrap-root:', 'assets:', 'report:', 'apply']);
if (!isset($options['manifest']) || !is_string($options['manifest'])) {
    fwrite(STDERR, "Required: --manifest=<captures.json> [--bootstrap-root=<TYPO3 project>] [--assets=<WebP folder>] [--report=<json>] [--apply]\n");
    exit(2);
}
$packageRoot = dirname(__DIR__, 2);
$bootstrapRoot = realpath($options['bootstrap-root'] ?? dirname($packageRoot, 2));
$assets = realpath($options['assets'] ?? $packageRoot . '/Resources/Public/Styleguide');
if ($bootstrapRoot === false || !is_file($bootstrapRoot . '/vendor/autoload.php')) {
    throw new RuntimeException('The bootstrap root must contain vendor/autoload.php.');
}
if ($assets === false || !is_dir($assets)) {
    throw new RuntimeException('The screenshot assets directory does not exist.');
}
if (!is_file($options['manifest']) || !is_readable($options['manifest'])) {
    throw new RuntimeException('The capture manifest does not exist or is not readable.');
}
$manifest = json_decode((string)file_get_contents($options['manifest']), true, flags: JSON_THROW_ON_ERROR);
if (!is_array($manifest) || !array_is_list($manifest) || $manifest === [] || count($manifest) > count(ALLOWED_SHOTS)) {
    throw new RuntimeException('The manifest must list a nonempty subset of the ' . count(ALLOWED_SHOTS) . ' allowed screenshots.');
}
$sources = [];
foreach ($manifest as $shot) {
    if (!is_array($shot)) {
        throw new RuntimeException('Each screenshot manifest entry must be an object.');
    }
    $name = $shot['name'] ?? null;
    $file = $shot['file'] ?? null;
    if (!is_string($name) || !in_array($name, ALLOWED_SHOTS, true) || isset($sources[$name])
        || !is_string($file) || !preg_match('/^' . preg_quote($name, '/') . '-([0-9a-f]{8})\.webp$/D', $file, $match)
    ) {
        throw new RuntimeException('The manifest contains a duplicate or unsupported screenshot.');
    }
    $source = $assets . '/' . $file;
    if (!is_file($source)) {
        $source = $assets . '/' . (str_starts_with($name, 'frontend-') ? 'Frontend' : 'Backend') . '/' . $file;
    }
    if (!is_file($source) || !is_readable($source)
        || substr((string)hash_file('sha256', $source), 0, 8) !== $match[1]
        || (@getimagesize($source)['mime'] ?? '') !== 'image/webp'
    ) {
        throw new RuntimeException('Missing, invalid or incorrectly hashed WebP: ' . $file);
    }
    if (isset($shot['alternatives']) && !is_array($shot['alternatives'])) {
        throw new RuntimeException('Screenshot alternatives must map language IDs to text.');
    }
    foreach ($shot['alternatives'] ?? [] as $language => $alternative) {
        if (!ctype_digit((string)$language) || !is_string($alternative) || trim($alternative) === '') {
            throw new RuntimeException('Invalid language ID or empty screenshot alternative.');
        }
    }
    $sources[$name] = $source;
}

$reportPath = $options['report'] ?? null;
if ($reportPath !== null && (!is_string($reportPath) || !is_dir(dirname($reportPath)) || !is_writable(dirname($reportPath)))) {
    throw new RuntimeException('The report directory must exist and be writable.');
}
$apply = isset($options['apply']);
$classLoader = require $bootstrapRoot . '/vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);

$pool = GeneralUtility::makeInstance(ConnectionPool::class);
$pages = $pool->getConnectionForTable('pages')->select(
    ['uid', 'pid'],
    'pages',
    ['deleted' => 0, 't3ver_wsid' => 0],
)->fetchAllAssociative();
$pageIds = array_map(static fn(array $page): int => (int)$page['uid'], $pages);
foreach (SITE_ROOTS as $root) {
    if (!in_array($root, $pageIds, true)) {
        throw new RuntimeException('The live site root ' . $root . ' does not exist.');
    }
}
$sitePages = array_fill_keys(SITE_ROOTS, true);
do {
    $before = count($sitePages);
    foreach ($pages as $page) {
        if (isset($sitePages[(int)$page['pid']])) {
            $sitePages[(int)$page['uid']] = true;
        }
    }
} while (count($sitePages) !== $before);

$files = $pool->getConnectionForTable('sys_file')->select(['uid', 'name'], 'sys_file', [])->fetchAllAssociative();
$referencesConnection = $pool->getConnectionForTable('sys_file_reference');
$changes = [];
$matched = array_fill_keys(array_keys($sources), 0);
// Build the complete plan before creating files or changing reference records.
foreach ($manifest as $shot) {
    foreach ($files as $oldFile) {
        if (!preg_match('/^' . preg_quote($shot['name'], '/') . '(?:-[0-9a-f]{8})?\.(?:png|webp)$/D', $oldFile['name'])) {
            continue;
        }
        $references = $referencesConnection->select(
            ['uid', 'pid', 'uid_local', 'uid_foreign', 'tablenames', 'fieldname', 'sorting_foreign', 'sys_language_uid', 'alternative'],
            'sys_file_reference',
            ['uid_local' => (int)$oldFile['uid'], 'deleted' => 0, 't3ver_wsid' => 0],
        )->fetchAllAssociative();
        $references = array_filter($references, static fn(array $reference): bool => isset($sitePages[(int)($reference['tablenames'] === 'pages' ? $reference['uid_foreign'] : $reference['pid'])]));
        $matched[$shot['name']] += count($references);
        foreach ($references as $reference) {
            $alternative = $shot['alternatives'][(int)$reference['sys_language_uid']] ?? $reference['alternative'];
            if ($oldFile['name'] === $shot['file'] && $reference['alternative'] === $alternative) {
                continue;
            }
            $changes[] = [
                'name' => $shot['name'],
                'reference' => $reference,
                'from' => $oldFile['name'],
                'to' => $shot['file'],
                'replacementUid' => $oldFile['name'] === $shot['file'] ? (int)$oldFile['uid'] : null,
                'alternative' => $alternative,
            ];
        }
    }
}
$unmatched = array_keys(array_filter($matched, static fn(int $count): bool => $count === 0));
if ($unmatched !== []) {
    throw new RuntimeException('No live screenshot references found within roots 505 and 69 for: ' . implode(', ', $unmatched));
}

if ($apply && $changes !== []) {
    Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
    Bootstrap::initializeBackendAuthentication();
    if ($GLOBALS['BE_USER']->workspace !== 0) {
        throw new RuntimeException('The CLI backend user must be in the live workspace.');
    }
    $factory = GeneralUtility::makeInstance(ResourceFactory::class);
    $replacements = [];
    $datamap = [];
    foreach ($changes as &$change) {
        $oldUid = (int)$change['reference']['uid_local'];
        $key = $oldUid . ':' . $change['to'];
        if ($change['replacementUid'] === null && !isset($replacements[$key])) {
            $folder = $factory->getFileObject($oldUid)->getParentFolder();
            $replacement = $folder->getFile($change['to']);
            if (!$replacement instanceof File) {
                $temporary = GeneralUtility::tempnam('feature_screenshot_');
                try {
                    if (!copy($sources[$change['name']], $temporary)) {
                        throw new RuntimeException('Could not stage the screenshot for FAL.');
                    }
                    $replacement = $folder->addFile($temporary, $change['to']);
                } finally {
                    if (is_file($temporary)) {
                        unlink($temporary);
                    }
                }
            }
            if ($replacement->getSha1() !== sha1_file($sources[$change['name']])) {
                throw new RuntimeException('An existing FAL asset has different contents: ' . $change['to']);
            }
            $replacements[$key] = $replacement->getUid();
        }
        $change['replacementUid'] ??= $replacements[$key];
        $datamap['sys_file_reference'][(int)$change['reference']['uid']] = [
            'uid_local' => $change['replacementUid'],
            'alternative' => $change['alternative'],
        ];
    }
    unset($change);
    $handler = GeneralUtility::makeInstance(DataHandler::class);
    $handler->start($datamap, []);
    $handler->process_datamap();
    if ($handler->errorLog !== []) {
        throw new RuntimeException(implode("\n", $handler->errorLog));
    }
    foreach ($changes as $change) {
        $reference = $change['reference'];
        $actual = $referencesConnection->select(
            ['uid_local', 'alternative', 'pid', 'uid_foreign', 'tablenames', 'fieldname', 'sorting_foreign', 'sys_language_uid'],
            'sys_file_reference',
            ['uid' => (int)$reference['uid'], 'deleted' => 0, 't3ver_wsid' => 0],
        )->fetchAssociative();
        if (!$actual || (int)$actual['uid_local'] !== $change['replacementUid'] || $actual['alternative'] !== $change['alternative']) {
            throw new RuntimeException('Screenshot reference verification failed for UID ' . $reference['uid']);
        }
        foreach (['pid', 'uid_foreign', 'tablenames', 'fieldname', 'sorting_foreign', 'sys_language_uid'] as $field) {
            if ((string)$actual[$field] !== (string)$reference[$field]) {
                throw new RuntimeException('Screenshot reference relationship changed for UID ' . $reference['uid'] . ': ' . $field);
            }
        }
    }
}
$report = ['applied' => $apply, 'roots' => SITE_ROOTS, 'context' => (string)Environment::getContext(), 'matched' => $matched, 'changes' => $changes];
if ($reportPath !== null && file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n") === false) {
    throw new RuntimeException('Could not write the screenshot refresh report.');
}
printf("%s %d existing screenshot references within roots 505 and 69 (%d requested shots).\n", $apply ? 'Updated' : 'Would update', count($changes), count($manifest));
