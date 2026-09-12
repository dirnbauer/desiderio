<?php

declare(strict_types=1);

/*
 * Automated upgrade rules for the code this extension owns.
 *
 *   vendor/bin/rector process --dry-run   report only
 *   vendor/bin/rector process             apply
 *
 * The TYPO3 level set is pinned to the version the extension supports, so a
 * future core bump surfaces the deprecations it introduces instead of silently
 * rewriting code for an untested target.
 */

use Rector\Config\RectorConfig;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Rector\Set\ValueObject\LevelSetList;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/Classes',
        __DIR__ . '/Configuration',
        __DIR__ . '/Tests',
    ])
    ->withRootFiles()
    ->withPhpSets(php84: true)
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        Typo3LevelSetList::UP_TO_TYPO3_14,
    ])
    ->withImportNames(importShortClasses: false)
    ->withSkip([
        // Generated or fixture-like payloads: rewriting them would fight the
        // generators that own the files.
        __DIR__ . '/Tests/Functional/Seeding/Fixtures/*',
        // Tests that assert a literal class name in configuration must keep
        // comparing the string, not a compile-time constant.
        StringClassNameToClassConstantRector::class,
        // Nullable arguments of string functions are handled explicitly where
        // it matters; the blanket rewrite only adds casts.
        NullToStrictStringFuncCallArgRector::class,
    ]);
