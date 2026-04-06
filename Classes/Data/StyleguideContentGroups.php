<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Static list of shadcn2fluid content element groups for the styleguide page.
 * Source: Resources/Private/Data/styleguide-content-groups.json
 */
final class StyleguideContentGroups
{
    /** @var list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string}>}>|null */
    private static ?array $cache = null;

    /**
     * @return list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string}>}>
     */
    public static function getGroups(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = GeneralUtility::getFileAbsFileName(
            'EXT:desiderio/Resources/Private/Data/styleguide-content-groups.json'
        );
        if ($path === '' || !is_readable($path)) {
            self::$cache = [];
            return self::$cache;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            self::$cache = [];
            return self::$cache;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            self::$cache = [];
            return self::$cache;
        }

        self::$cache = $decoded;
        return self::$cache;
    }
}
