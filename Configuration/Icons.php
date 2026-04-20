<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    // ==============================================
    // Hero Variant Icons
    // ==============================================
    'desiderio-hero-centered' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/ContentElements/hero-centered.svg',
    ],
    'desiderio-hero-split' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/ContentElements/hero-split.svg',
    ],
    'desiderio-hero-background' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/ContentElements/hero-background.svg',
    ],

    // ==============================================
    // Card Variant Icons
    // ==============================================
    'desiderio-card-basic' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/card-basic.svg',
    ],
    'desiderio-card-image' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/card-image.svg',
    ],
    'desiderio-card-horizontal' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/card-horizontal.svg',
    ],

    // ==============================================
    // Alert Variant Icons
    // ==============================================
    'desiderio-alert-default' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/alert-default.svg',
    ],
    'desiderio-alert-info' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/alert-info.svg',
    ],
    'desiderio-alert-success' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/alert-success.svg',
    ],
    'desiderio-alert-warning' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/alert-warning.svg',
    ],
    'desiderio-alert-destructive' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/Variants/alert-destructive.svg',
    ],
];
