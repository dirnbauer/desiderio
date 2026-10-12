#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Repoint only existing backend screenshot references in a development site.
 *
 * ddev exec php packages/desiderio/Build/Scripts/refresh-backend-screenshot-references.php \
 *   --manifest=var/preview-refresh/captures.json --root=505 [--apply] [--report=var/preview-refresh/references.json]
 *
 * The manifest comes from capture-feature-screenshots.mjs --manifest. Source
 * assets are imported through FAL into the same folder as each previous image;
 * DataHandler updates the existing references, preserving their order, language
 * and record relationships. Optional alternatives map TYPO3 language IDs to
 * refreshed image descriptions. No content is reseeded or deleted.
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

$options = getopt('', ['manifest:', 'root:', 'report:', 'apply']);
if (!isset($options['manifest'], $options['root']) || (int)$options['root'] < 1) {
    fwrite(STDERR, "Required: --manifest=<capture manifest.json> --root=<site root uid> [--apply] [--report=<json>]\n");
    exit(2);
}
$packageRoot = dirname(__DIR__, 2);
$classLoader = require dirname($packageRoot, 2) . '/vendor/autoload.php';
SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($classLoader);
if (!Environment::getContext()->isDevelopment()) {
    throw new RuntimeException('This screenshot refresh is restricted to development sites.');
}
Bootstrap::initializeBackendUser(CommandLineUserAuthentication::class);
Bootstrap::initializeBackendAuthentication();

$manifest = json_decode((string)file_get_contents($options['manifest']), true, flags: JSON_THROW_ON_ERROR);
$allowed = ['backend-page-module-hero', 'backend-content-wizard', 'feature-desiderio-page-module', 'feature-desiderio-content-wizard'];
foreach ($manifest as $shot) {
    if (!in_array($shot['name'], $allowed, true) || !preg_match('/^' . preg_quote($shot['name'], '/') . '-[0-9a-f]{8}\.webp$/D', $shot['file'])) {
        throw new RuntimeException('Manifest contains an unsupported screenshot.');
    }
    if (!is_file($packageRoot . '/Resources/Public/Styleguide/Backend/' . $shot['file'])) {
        throw new RuntimeException('Capture is missing: ' . $shot['file']);
    }
}

$pool = GeneralUtility::makeInstance(ConnectionPool::class);
$pages = $pool->getConnectionForTable('pages')->select(['uid', 'pid'], 'pages', ['deleted' => 0])->fetchAllAssociative();
$sitePages = [(int)$options['root'] => true];
do {
    $before = count($sitePages);
    foreach ($pages as $page) {
        if (isset($sitePages[(int)$page['pid']])) {
            $sitePages[(int)$page['uid']] = true;
        }
    }
} while (count($sitePages) !== $before);

$files = $pool->getConnectionForTable('sys_file')->select(['uid', 'name'], 'sys_file', [])->fetchAllAssociative();
$factory = GeneralUtility::makeInstance(ResourceFactory::class);
$changes = [];
$datamap = [];
$apply = isset($options['apply']);
foreach ($manifest as $shot) {
    foreach ($files as $oldFile) {
        if ($oldFile['name'] === $shot['file'] || !preg_match('/^' . preg_quote($shot['name'], '/') . '(?:-[0-9a-f]{8})?\.(?:png|webp)$/D', $oldFile['name'])) {
            continue;
        }
        $references = $pool->getConnectionForTable('sys_file_reference')->select(
            ['uid', 'pid', 'uid_local', 'uid_foreign', 'tablenames', 'sys_language_uid', 'alternative'],
            'sys_file_reference',
            ['uid_local' => (int)$oldFile['uid'], 'deleted' => 0, 't3ver_wsid' => 0],
        )->fetchAllAssociative();
        $references = array_filter($references, static fn(array $reference): bool => isset($sitePages[(int)($reference['tablenames'] === 'pages' ? $reference['uid_foreign'] : $reference['pid'])]));
        if ($references === []) {
            continue;
        }
        $replacementUid = null;
        if ($apply) {
            $folder = $factory->getFileObject((int)$oldFile['uid'])->getParentFolder();
            $replacement = $folder->getFile($shot['file']);
            if (!$replacement instanceof File) {
                $temporary = GeneralUtility::tempnam('desiderio_screenshot_');
                try {
                    if (!copy($packageRoot . '/Resources/Public/Styleguide/Backend/' . $shot['file'], $temporary)) {
                        throw new RuntimeException('Could not stage the screenshot for FAL.');
                    }
                    $replacement = $folder->addFile($temporary, $shot['file']);
                } finally {
                    if (is_file($temporary)) {
                        unlink($temporary);
                    }
                }
            }
            $replacementUid = $replacement->getUid();
        }
        foreach ($references as $reference) {
            $changes[] = ['reference' => $reference, 'from' => $oldFile['name'], 'to' => $shot['file'], 'replacementUid' => $replacementUid];
            if ($apply) {
                $datamap['sys_file_reference'][(int)$reference['uid']] = ['uid_local' => $replacementUid];
                $alternative = $shot['alternatives'][(int)$reference['sys_language_uid']] ?? null;
                if (is_string($alternative) && $alternative !== '') {
                    $datamap['sys_file_reference'][(int)$reference['uid']]['alternative'] = $alternative;
                }
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
}
if (isset($options['report'])) {
    file_put_contents($options['report'], json_encode(['applied' => $apply, 'root' => (int)$options['root'], 'changes' => $changes], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
}
printf("%s %d existing screenshot references on site %d.\n", $apply ? 'Updated' : 'Would update', count($changes), (int)$options['root']);
