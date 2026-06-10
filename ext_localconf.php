<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// The extensionLoaded("<key>") TypoScript condition used by the site set is
// registered via Configuration/ExpressionLanguage.php (TYPO3 v12+ mechanism).

// CSS, JS and all frontend configuration is provided via the site set in
// Configuration/Sets/Desiderio/. Page TSconfig is auto-loaded from
// Configuration/page.tsconfig in TYPO3 14.

// The bundled Desiderio forms declare a "Friendlycaptcha" element. When
// studiomitte/friendlycaptcha is not installed, map that element to an inert
// placeholder widget and an always-passing validator so the forms keep
// rendering and submitting. When it IS installed, only the rendering partial
// is overridden so the Desiderio bypass applies (placeholder + environment
// note in Development context/test mode, real widget otherwise).
if (!ExtensionManagementUtility::isLoaded('friendlycaptcha_official')) {
    ExtensionManagementUtility::addTypoScriptSetup('
plugin.tx_form.settings.yamlConfigurations.1777100143 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml
module.tx_form.settings.yamlConfigurations.1777100143 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml
');
} else {
    ExtensionManagementUtility::addTypoScriptSetup('
plugin.tx_form.settings.yamlConfigurations.1777100144 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFormPartialOverride.yaml
module.tx_form.settings.yamlConfigurations.1777100144 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFormPartialOverride.yaml
');
}
