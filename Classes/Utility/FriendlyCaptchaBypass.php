<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Utility;

use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Single source of truth for "should the Friendly Captcha be bypassed?".
 *
 * Rules, in order:
 *  1. Production never bypasses, regardless of any setting.
 *  2. desiderio.forms.friendlyCaptchaForceReal forces the real captcha even
 *     in Development (e.g. to test real keys on a ddev site).
 *  3. Development context (ddev and friends) bypasses by default — forms work
 *     out of the box without captcha keys.
 *  4. Any other non-production context bypasses only when the explicit
 *     desiderio.forms.friendlyCaptchaTestMode setting is enabled.
 */
final class FriendlyCaptchaBypass
{
    public const TEST_MODE_SETTING = 'desiderio.forms.friendlyCaptchaTestMode';
    public const FORCE_REAL_SETTING = 'desiderio.forms.friendlyCaptchaForceReal';

    public static function isEnabled(Site $site, ?ApplicationContext $context = null): bool
    {
        $context ??= self::resolveContext();

        if ($context !== null && $context->isProduction()) {
            return false;
        }
        if (SiteSettingsBoolean::isEnabled($site, self::FORCE_REAL_SETTING)) {
            return false;
        }
        if ($context !== null && $context->isDevelopment()) {
            return true;
        }

        return SiteSettingsBoolean::isEnabled($site, self::TEST_MODE_SETTING);
    }

    private static function resolveContext(): ?ApplicationContext
    {
        try {
            return Environment::getContext();
        } catch (\Error) {
            // Environment is not bootstrapped (plain unit test runs); fall back
            // to the explicit-setting behaviour.
            return null;
        }
    }
}
