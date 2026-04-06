<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Static list of shadcn2fluid content element groups for the styleguide page.
 * Source: Resources/Private/Data/styleguide-content-groups.json
 *
 * Fixture data is loaded from individual fixture.json files inside each
 * Content Block directory (shadcn2fluid-templates/ContentBlocks/ContentElements/{name}/fixture.json).
 * Falls back to the monolithic Resources/Private/Data/styleguide-fixtures.json if present.
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
     * Loads fixture data keyed by ctype.
     *
     * Strategy:
     *  1. Scan ContentBlocks/ContentElements/{name}/fixture.json inside shadcn2fluid-templates.
     *  2. Fall back to the monolithic JSON in desiderio's Data/ directory.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getFixtures(): array
    {
        if (self::$fixtureCache !== null) {
            return self::$fixtureCache;
        }

        // Load individual fixture.json files from Content Block directories
        $fixtures = self::loadFixturesFromContentBlocks();

        // Merge with monolithic fallback for any ctypes not found individually
        $monolithic = self::loadJson('EXT:desiderio/Resources/Private/Data/styleguide-fixtures.json');
        if (is_array($monolithic)) {
            foreach ($monolithic as $ctype => $data) {
                if (!isset($fixtures[$ctype])) {
                    $fixtures[$ctype] = $data;
                }
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
            'EXT:shadcn2fluid_templates/ContentBlocks/ContentElements'
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
            // Key by ctype: shadcn2fluid_{dirname}
            $ctype = 'shadcn2fluid_' . $dir;
            $fixtures[$ctype] = $decoded;
        }

        return $fixtures;
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
