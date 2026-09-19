<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Utility;

use TYPO3\CMS\Core\Site\Entity\Site;

/**
 * Typed reads of Desiderio's site settings.
 *
 * Site settings arrive as whatever YAML, the site module or a TypoScript
 * constant put there — `true`, `"1"`, `"on"`, `0`, `"42"` — so every consumer
 * used to repeat its own coercion. These two readers are that coercion, once.
 */
final class SiteSettings
{
    public static function isEnabled(Site $site, string $settingIdentifier, bool $default = false): bool
    {
        if ($site->getSettings()->isEmpty()) {
            return $default;
        }

        return self::isTruthy($site->getSettings()->get($settingIdentifier, $default));
    }

    /**
     * A numeric setting, or the default when it is absent or not a number.
     */
    public static function integer(Site $site, string $settingIdentifier, int $default = 0): int
    {
        $configured = $site->getSettings()->get($settingIdentifier, $default);

        return is_numeric($configured) ? (int)$configured : $default;
    }

    private static function isTruthy(mixed $value): bool
    {
        return match (true) {
            is_bool($value) => $value,
            is_int($value) => $value === 1,
            is_string($value) => in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true),
            default => false,
        };
    }
}
