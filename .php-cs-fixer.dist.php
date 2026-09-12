<?php

declare(strict_types=1);

use TYPO3\CodingStandards\CsFixerConfig;

/*
 * Coding standards of the TYPO3 community (typo3/coding-standards), applied to
 * the extension's own PHP: Classes, Tests, Configuration and the root files.
 *
 *   vendor/bin/php-cs-fixer fix            fix in place
 *   vendor/bin/php-cs-fixer fix --dry-run --diff   report only (CI)
 */

$config = CsFixerConfig::create();
$config->getFinder()
    ->in(__DIR__ . '/Classes')
    ->in(__DIR__ . '/Configuration')
    ->in(__DIR__ . '/Tests')
    ->append([
        __FILE__,
        __DIR__ . '/ext_emconf.php',
        __DIR__ . '/ext_localconf.php',
        __DIR__ . '/rector.php',
    ]);

return $config;
