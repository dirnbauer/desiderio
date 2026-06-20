<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$icons = [
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

// ==============================================
// Core Content Element Icons
// ==============================================
// Custom v14 line-art icons for the classic TYPO3 core content elements, so
// they match Desiderio's Content Blocks in the New Content Element Wizard, the
// page module and the visual-editor picker. Identifier: desiderio-ce-<slug>,
// file: Resources/Public/Icons/ContentElements/core-<slug>.svg. Wired to each
// CType in Configuration/TCA/Overrides/tt_content.php from CoreContentElements.
foreach ([
    'header', 'text', 'textpic', 'textmedia', 'image',
    'bullets', 'table', 'uploads',
    'divider', 'html', 'shortcut',
    'menu-pages', 'menu-subpages', 'menu-sitemap', 'menu-sitemap-pages',
    'menu-section', 'menu-section-pages', 'menu-abstract',
    'menu-recently-updated', 'menu-related',
    'menu-categorized-pages', 'menu-categorized-content',
    'form', 'login', 'powermail',
] as $coreIconSlug) {
    $icons['desiderio-ce-' . $coreIconSlug] = [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:desiderio/Resources/Public/Icons/ContentElements/core-' . $coreIconSlug . '.svg',
    ];
}

return $icons;
