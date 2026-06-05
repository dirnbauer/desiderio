<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Utility;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;

final class SiteSettingsBoolean
{
    public static function isEnabled(Site $site, string $settingIdentifier, bool $default = false): bool
    {
        if ($site->getSettings()->isEmpty()) {
            return $default;
        }

        return self::isTruthy($site->getSettings()->get($settingIdentifier, $default));
    }

    public static function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }
}
