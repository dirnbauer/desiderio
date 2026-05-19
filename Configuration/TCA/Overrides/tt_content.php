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
