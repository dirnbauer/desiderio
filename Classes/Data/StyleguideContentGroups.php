<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Static list of Desiderio content element groups for the styleguide page.
 * Source: Resources/Private/Data/styleguide-content-groups.json
 *
 * Fixture data is loaded from individual fixture.json files inside each
 * Content Block directory. Falls back to the monolithic
 * Resources/Private/Data/styleguide-fixtures.json if present.
 */
final class StyleguideContentGroups
{
    /** @var list<array{groupId: string, groupTitle: string, pageTitle?: string, elements: list<array{name: string, ctype: string}>}>|null */
    private static ?array $cache = null;

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $fixtureCache = null;

    /**
     * @return list<array{groupId: string, groupTitle: string, pageTitle?: string, elements: list<array{name: string, ctype: string}>}>
     */
    public static function getGroups(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        /** @var list<array{groupId: string, groupTitle: string, pageTitle?: string, elements: list<array{name: string, ctype: string}>}> $groups */
        $groups = self::loadJson('EXT:desiderio/Resources/Private/Data/styleguide-content-groups.json');
        self::$cache = $groups;
        return self::$cache;
    }

    /**
     * @return list<array{groupId: string, groupTitle: string, pageTitle?: string, elements: list<array{name: string, ctype: string, fixture: array<string, mixed>}>}>
     */
    public static function getGroupsWithFixtures(): array
    {
        $fixtures = self::getFixtures();
        $groups = self::getGroups();

        foreach ($groups as &$group) {
            foreach ($group['elements'] as &$element) {
                $ctype = $element['ctype'];
                $element['fixture'] = $fixtures[$ctype] ?? [];
            }
        }
        unset($group, $element);

        /** @var list<array{groupId: string, groupTitle: string, pageTitle?: string, elements: list<array{name: string, ctype: string, fixture: array<string, mixed>}>}> $groups */
        return $groups;
    }

    /**
     * Loads fixture data keyed by ctype.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getFixtures(): array
    {
        if (self::$fixtureCache !== null) {
            return self::$fixtureCache;
        }

        $fixtures = self::loadFixturesFromContentBlocks();

        /** @var array<string, array<string, mixed>> $monolithic */
        $monolithic = self::loadJson('EXT:desiderio/Resources/Private/Data/styleguide-fixtures.json');
        foreach ($monolithic as $ctype => $data) {
            $normalizedCtype = self::normalizeCtype((string)$ctype);
            if (!isset($fixtures[$normalizedCtype])) {
                $fixtures[$normalizedCtype] = $data;
            }
        }

        self::$fixtureCache = $fixtures;
        return self::$fixtureCache;
    }

    /**
     * Scan Content Block directories for individual fixture.json files.
     *
     * @return array<string, array<string, mixed>>
     */
    private static function loadFixturesFromContentBlocks(): array
    {
        $basePath = GeneralUtility::getFileAbsFileName(
            'EXT:desiderio/ContentBlocks/ContentElements'
        );
        if ($basePath === '' || !is_dir($basePath)) {
            return [];
        }

        $fixtures = [];
        $dirs = scandir($basePath);
        if ($dirs === false) {
            return [];
        }

        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $fixturePath = $basePath . '/' . $dir . '/fixture.json';
            if (!is_readable($fixturePath)) {
                continue;
            }
            $raw = file_get_contents($fixturePath);
            if ($raw === false) {
                continue;
            }
            $decoded = json_decode($raw, true);
            if (!is_array($decoded)) {
                continue;
            }
            $ctype = 'desiderio_' . str_replace('-', '', $dir);
            /** @var array<string, mixed> $decoded */
            $fixtures[$ctype] = $decoded;
        }

        return $fixtures;
    }

    private static function normalizeCtype(string $ctype): string
    {
        if (str_starts_with($ctype, 'shadcn2fluid_')) {
            return 'desiderio_' . str_replace('-', '', substr($ctype, strlen('shadcn2fluid_')));
        }

        return $ctype;
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
