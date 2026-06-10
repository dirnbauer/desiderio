<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// CSS, JS and all frontend configuration is provided via the site set in
// Configuration/Sets/Desiderio/. Page TSconfig is auto-loaded from
// Configuration/page.tsconfig in TYPO3 14.

// The bundled Desiderio forms declare a "Friendlycaptcha" element. When
// studiomitte/friendlycaptcha is not installed, map that element to an inert
// placeholder widget and an always-passing validator so the forms keep
// rendering and submitting.
if (!ExtensionManagementUtility::isLoaded('friendlycaptcha_official')) {
    ExtensionManagementUtility::addTypoScriptSetup('
plugin.tx_form.settings.yamlConfigurations.1777100143 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml
module.tx_form.settings.yamlConfigurations.1777100143 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml
');
}
