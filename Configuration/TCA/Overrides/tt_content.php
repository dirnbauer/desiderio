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

// The classic TYPO3 core content elements get Desiderio's custom v14 icons and
// richer descriptions (wizard + page module) from CoreContentElements. That is
// applied in \Webconsulting\Desiderio\EventListener\CoreContentElementIcons on
// AfterTcaCompilationEvent — NOT here — because some CType items (e.g. felogin's
// "login") are registered by extensions that load after Desiderio, so a static
// override at this point would silently miss them.
