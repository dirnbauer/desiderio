<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

// Per-page theme preset override. Empty inherits from the parent page
// (levelfield slide in the body tag cObject) or the site setting.
$presetItems = [
    [
        'label' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:pages.shadcnPreset.inherit',
        'value' => '',
    ],
];

foreach ([
    'b4hb38Fyj' => 'b4hb38Fyj — Olive product system',
    'b0' => 'b0 — Default neutral',
    'b3IWPgRwnI' => 'b3IWPgRwnI — Mist dashboard',
    'b6G5977cw' => 'b6G5977cw — Lyra mono olive',
    'b27GcrRo' => 'b27GcrRo — Rhea modern neutral',
    'aurora' => 'Aurora — violet',
    'marine' => 'Marine — blue',
    'forest' => 'Forest — emerald',
    'ember' => 'Ember — orange',
    'bloom' => 'Bloom — rose',
    'lagoon' => 'Lagoon — teal',
    'gold' => 'Gold — amber',
    'midnight' => 'Midnight — indigo',
    'blossom' => 'Blossom — pink',
    'citrus' => 'Citrus — lime mono',
    'custom' => 'Custom (extend shadcn-theme.css)',
] as $value => $label) {
    $presetItems[] = ['label' => $label, 'value' => $value];
}

$GLOBALS['TCA']['pages']['columns']['tx_desiderio_shadcn_preset'] = [
    'exclude' => true,
    'label' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:pages.shadcnPreset',
    'description' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:pages.shadcnPreset.description',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => $presetItems,
        'default' => '',
    ],
];

ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'layout',
    'tx_desiderio_shadcn_preset',
    'after:backend_layout_next_level',
);

// Per-page control for the automatic page-title <h1> rendered by the page
// templates (Pages/PageTitle partial). The heading is visible by default;
// enabling this renders it screen-reader-only, for pages whose first content
// element (a hero or header) already shows the title and would otherwise
// duplicate the heading.
$GLOBALS['TCA']['pages']['columns']['tx_desiderio_h1_sronly'] = [
    'exclude' => true,
    'label' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:pages.h1SrOnly',
    'description' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:pages.h1SrOnly.description',
    'config' => [
        'type' => 'check',
        'renderType' => 'checkboxToggle',
        'default' => 0,
        'items' => [
            ['label' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:pages.h1SrOnly.toggle'],
        ],
    ],
];

ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'title',
    'tx_desiderio_h1_sronly',
    'after:title',
);
