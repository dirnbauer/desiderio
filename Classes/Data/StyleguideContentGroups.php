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

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $fixtureCache = null;

    /**
     * @return list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string}>}>
     */
    public static function getGroups(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        self::$cache = self::loadJson('EXT:desiderio/Resources/Private/Data/styleguide-content-groups.json');
        return self::$cache;
    }

    /**
     * @return array<string, array<string, mixed>> Fixture data keyed by ctype
     */
    public static function getFixtures(): array
    {
        if (self::$fixtureCache !== null) {
            return self::$fixtureCache;
        }

        $decoded = self::loadJson('EXT:desiderio/Resources/Private/Data/styleguide-fixtures.json');
        self::$fixtureCache = is_array($decoded) ? $decoded : [];
        return self::$fixtureCache;
    }

    /**
     * @return array<mixed>
     */
    private static function loadJson(string $extensionPath): array
    {
        $path = GeneralUtility::getFileAbsFileName($extensionPath);
        if ($path === '' || !is_readable($path)) {
            return [];
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}
