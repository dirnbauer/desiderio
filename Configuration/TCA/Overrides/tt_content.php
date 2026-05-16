<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

// Content Blocks keeps non-shareable inline relation options on the base column.
// Re-apply them per CType because Desiderio intentionally reuses names like "items".
$contentElementsPath = GeneralUtility::getFileAbsFileName('EXT:desiderio/ContentBlocks/ContentElements');
if ($contentElementsPath !== '' && is_dir($contentElementsPath)) {
    foreach (glob($contentElementsPath . '/*/config.yaml') ?: [] as $configPath) {
        $config = Yaml::parseFile($configPath);
        if (!is_array($config)) {
            continue;
        }

        $typeName = (string)($config['typeName'] ?? '');
        if ($typeName === '' || !isset($GLOBALS['TCA']['tt_content']['types'][$typeName])) {
            continue;
        }

        foreach (($config['fields'] ?? []) as $field) {
            if (!is_array($field) || ($field['type'] ?? '') !== 'Collection') {
                continue;
            }

            $identifier = (string)($field['identifier'] ?? '');
            if ($identifier === '') {
                continue;
            }

            $relationConfig = [
                'foreign_table' => (string)($field['foreign_table'] ?? $field['table'] ?? $identifier),
            ];

            if (!isset($field['MM'])) {
                $relationConfig['foreign_field'] = (string)($field['foreign_field'] ?? 'foreign_table_parent_uid');
            }

            if (isset($field['foreign_table_field'])) {
                $relationConfig['foreign_table_field'] = (string)$field['foreign_table_field'];
            } elseif (($field['shareAcrossTables'] ?? false) === true) {
                $relationConfig['foreign_table_field'] = 'tablenames';
            }

            $foreignMatchFields = is_array($field['foreign_match_fields'] ?? null)
                ? $field['foreign_match_fields']
                : [];
            if (($field['shareAcrossFields'] ?? false) === true) {
                $foreignMatchFields['fieldname'] = $identifier;
            }
            if ($foreignMatchFields !== []) {
                $relationConfig['foreign_match_fields'] = $foreignMatchFields;
            }

            foreach ($relationConfig as $option => $value) {
                $GLOBALS['TCA']['tt_content']['types'][$typeName]['columnsOverrides'][$identifier]['config'][$option] = $value;
            }
        }
    }
}
