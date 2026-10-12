#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Refresh the five Easy Workspace screenshots already referenced on site 505.
 *
 * ddev exec php packages/desiderio/Build/Scripts/refresh-easy-workspace-screenshots.php \
 *   --manifest=var/easy-workspace/captures.json --report=var/easy-workspace/references.json
 *
 * Add --apply to import files through FAL and update existing live references
 * through DataHandler. This also works in Production. To run a copied script:
 *   php /tmp/refresh-easy-workspace-screenshots.php --bootstrap-root=/var/www/html \
 *     --assets=/tmp/easy-workspace --manifest=/tmp/easy-workspace/captures.json \
 *     --report=/tmp/easy-workspace/references.json --apply
 *
 * The capture manifest must contain each of the five allowed names once, with
 * its content-hashed WebP filename and optional alternatives keyed by language
 * ID. No records are created or reseeded, no old assets are overwritten or
 * deleted, and references outside the existing live site tree are untouched.
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

const SITE_ROOT = 505;
const ALLOWED_SHOTS = [
    'feature-easy-workspace-dropdown',
    'feature-easy-workspace-toolbar-badge',
    'feature-easy-workspace-languages',
    'feature-easy-workspace-selection',
    'feature-easy-workspace-published',
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
$assets = realpath($options['assets'] ?? $packageRoot . '/Resources/Public/Styleguide/Backend');
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
if (!is_array($manifest) || !array_is_list($manifest) || count($manifest) !== count(ALLOWED_SHOTS)) {
    throw new RuntimeException('The manifest must list exactly the five Easy Workspace screenshots.');
}
$seen = [];
foreach ($manifest as $shot) {
    $name = $shot['name'] ?? null;
    $file = $shot['file'] ?? null;
    if (!is_string($name) || !in_array($name, ALLOWED_SHOTS, true) || isset($seen[$name])
        || !is_string($file) || !preg_match('/^' . preg_quote($name, '/') . '-([0-9a-f]{8})\.webp$/D', $file, $match)
    ) {
        throw new RuntimeException('The manifest contains a duplicate or unsupported screenshot.');
    }
    $seen[$name] = true;
    $source = $assets . '/' . $file;
    if (!is_file($source) || !is_readable($source)
        || substr((string)hash_file('sha256', $source), 0, 8) !== $match[1]
        || (getimagesize($source)['mime'] ?? '') !== 'image/webp'
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
}

$reportPath = $options['report'] ?? null;
if ($reportPath !== null && (!is_string($reportPath) || !is_dir(dirname($reportPath)) || !is_writable(dirname($reportPath)))) {
    throw new RuntimeException('The report directory must exist and be writable.');
}
$apply = isset($options['apply']);
$classLoader = require $bootstrapRoot . '/vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
if ($apply) {
    Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
    Bootstrap::initializeBackendAuthentication();
    if ($GLOBALS['BE_USER']->workspace !== 0) {
        throw new RuntimeException('The CLI backend user must be in the live workspace.');
    }
}

$pool = GeneralUtility::makeInstance(ConnectionPool::class);
$pages = $pool->getConnectionForTable('pages')->select(
    ['uid', 'pid'],
    'pages',
    ['deleted' => 0, 't3ver_wsid' => 0],
)->fetchAllAssociative();
if (!in_array(SITE_ROOT, array_map(static fn(array $page): int => (int)$page['uid'], $pages), true)) {
    throw new RuntimeException('The live site root 505 does not exist.');
}
$sitePages = [SITE_ROOT => true];
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
$factory = $apply ? GeneralUtility::makeInstance(ResourceFactory::class) : null;
$changes = [];
$datamap = [];
$matched = array_fill_keys(ALLOWED_SHOTS, 0);
foreach ($manifest as $shot) {
    foreach ($files as $oldFile) {
        if (!preg_match('/^' . preg_quote($shot['name'], '/') . '(?:-[0-9a-f]{8})?\.(?:png|webp)$/D', $oldFile['name'])) {
            continue;
        }
        $references = $referencesConnection->select(
            ['uid', 'pid', 'uid_local', 'uid_foreign', 'tablenames', 'sys_language_uid', 'alternative'],
            'sys_file_reference',
            ['uid_local' => (int)$oldFile['uid'], 'deleted' => 0, 't3ver_wsid' => 0],
        )->fetchAllAssociative();
        $references = array_filter($references, static fn(array $reference): bool => isset($sitePages[(int)($reference['tablenames'] === 'pages' ? $reference['uid_foreign'] : $reference['pid'])]));
        $matched[$shot['name']] += count($references);
        $replacementUid = $oldFile['name'] === $shot['file'] ? (int)$oldFile['uid'] : null;
        foreach ($references as $reference) {
            $alternative = $shot['alternatives'][(int)$reference['sys_language_uid']] ?? $reference['alternative'];
            if ($oldFile['name'] === $shot['file'] && $reference['alternative'] === $alternative) {
                continue;
            }
            if ($apply && $replacementUid === null) {
                $folder = $factory->getFileObject((int)$oldFile['uid'])->getParentFolder();
                $replacement = $folder->getFile($shot['file']);
                if (!$replacement instanceof File) {
                    $temporary = GeneralUtility::tempnam('easy_workspace_screenshot_');
                    try {
                        if (!copy($assets . '/' . $shot['file'], $temporary)) {
                            throw new RuntimeException('Could not stage the screenshot for FAL.');
                        }
                        $replacement = $folder->addFile($temporary, $shot['file']);
                    } finally {
                        if (is_file($temporary)) {
                            unlink($temporary);
                        }
                    }
                }
                if ($replacement->getSha1() !== sha1_file($assets . '/' . $shot['file'])) {
                    throw new RuntimeException('An existing FAL asset has different contents: ' . $shot['file']);
                }
                $replacementUid = $replacement->getUid();
            }
            $changes[] = [
                'reference' => $reference,
                'from' => $oldFile['name'],
                'to' => $shot['file'],
                'replacementUid' => $replacementUid,
                'alternative' => $alternative,
            ];
            if ($apply) {
                $datamap['sys_file_reference'][(int)$reference['uid']] = ['uid_local' => $replacementUid, 'alternative' => $alternative];
            }
        }
    }
}

if ($datamap !== []) {
    $handler = GeneralUtility::makeInstance(DataHandler::class);
    $handler->start($datamap, []);
    $handler->process_datamap();
    if ($handler->errorLog !== []) {
        throw new RuntimeException(implode("\n", $handler->errorLog));
    }
    foreach ($datamap['sys_file_reference'] as $uid => $expected) {
        $actual = $referencesConnection->select(['uid_local', 'alternative'], 'sys_file_reference', ['uid' => $uid, 'deleted' => 0, 't3ver_wsid' => 0])->fetchAssociative();
        if (!$actual || (int)$actual['uid_local'] !== $expected['uid_local'] || $actual['alternative'] !== $expected['alternative']) {
            throw new RuntimeException('Screenshot reference verification failed for UID ' . $uid);
        }
    }
}
$report = ['applied' => $apply, 'root' => SITE_ROOT, 'context' => (string)Environment::getContext(), 'matched' => $matched, 'changes' => $changes];
if ($reportPath !== null && file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n") === false) {
    throw new RuntimeException('Could not write the screenshot refresh report.');
}
printf("%s %d existing Easy Workspace screenshot references on site %d.\n", $apply ? 'Updated' : 'Would update', count($changes), SITE_ROOT);
