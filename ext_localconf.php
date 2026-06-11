<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die();

// The extensionLoaded("<key>") TypoScript condition used by the site set is
// registered via Configuration/ExpressionLanguage.php (TYPO3 v12+ mechanism).

// CSS, JS and all frontend configuration is provided via the site set in
// Configuration/Sets/Desiderio/. Page TSconfig is auto-loaded from
// Configuration/page.tsconfig in TYPO3 14.

// Desiderio RTE preset: Default toolbar plus textPartLanguage (span lang) and
// the custom abbreviation plugin (abbr title). Assigned per field via
// richtextConfiguration: desiderio in the Content Block config.yaml files.
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['desiderio'] = 'EXT:desiderio/Configuration/RTE/Desiderio.yaml';

// Powermail double-opt-in and disclaimer mails link with the record uid and a
// sha256 hash in the route path; the appended ?cHash= adds ~70 characters and
// breaks the link whenever the cHash salt changes. The hash already authorizes
// the request, so exclude the powermail params from cacheHash calculation —
// the same hardening powermail documents for opt-in links.
if (ExtensionManagementUtility::isLoaded('powermail')) {
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = array_values(array_unique(array_merge(
        $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] ?? [],
        [
            'tx_powermail_pi1[hash]',
            'tx_powermail_pi1[mail]',
            'tx_powermail_pi1[action]',
            'tx_powermail_pi1[controller]',
        ]
    )));
}

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
