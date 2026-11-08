<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$position = 'before:default';

foreach ([
    'content' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.content',
    'conversion' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.conversion',
    'data' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.data',
    'features' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.features',
    'footer' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.footer',
    'hero' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.hero',
    'navigation' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.navigation',
    'pricing' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.pricing',
    'social-proof' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.socialProof',
    'team' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:contentElementGroup.team',
] as $group => $label) {
    ExtensionManagementUtility::addTcaSelectItemGroup(
        'tt_content',
        'CType',
        $group,
        $label,
        $position,
    );
    $position = 'after:' . $group;
}

// A readable anchor for links to one element (t3://page?uid=12#pricing), next
// to the c<uid> anchor every element already gets: lib.contentBlock renders it
// as <a id="pricing"></a> before the element (Sets/Desiderio/TypoScript/
// content.typoscript). It is the same in every language, so one link with the
// anchor works in all of them. The field sits in the Appearance tab of every
// element with the Desiderio/Appearance basic; Content Blocks builds that
// palette before the TCA overrides run (BeforeTcaOverridesEvent).
$GLOBALS['TCA']['tt_content']['columns']['tx_desiderio_anchor'] = [
    'exclude' => true,
    'l10n_mode' => 'exclude',
    'label' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:appearance.anchor',
    'description' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:appearance.anchor.description',
    'config' => [
        'type' => 'input',
        'size' => 30,
        'max' => 64,
        'eval' => 'trim,lower,alphanum_x',
        'default' => '',
    ],
];
ExtensionManagementUtility::addFieldsToPalette(
    'tt_content',
    'desiderio_frames_palette',
    'tx_desiderio_anchor',
    'after:frame_class',
);

// The classic TYPO3 core content elements get Desiderio's custom v14 icons and
// richer descriptions (wizard + page module) from CoreContentElements. That is
// applied in \Webconsulting\Desiderio\EventListener\CoreContentElementIcons on
// AfterTcaCompilationEvent — NOT here — because some CType items (e.g. felogin's
// "login") are registered by extensions that load after Desiderio, so a static
// override at this point would silently miss them.
