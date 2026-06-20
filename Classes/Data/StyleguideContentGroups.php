<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Library\CoreContentElements;

/**
 * Static list of Desiderio content element groups for the styleguide page.
 * Source: Resources/Private/Data/styleguide-content-groups.json
 *
 * Fixture data is loaded from individual fixture.json files inside each
 * Content Block directory.
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

        /** @var list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string}>}> $groups */
        $groups = self::loadJson('EXT:desiderio/Resources/Private/Data/styleguide-content-groups.json');

        // The classic TYPO3 core content elements get their own styleguide page,
        // derived from the single CoreContentElements manifest (plugins excluded:
        // they need a configured form/flexform and Powermail has its own seeder).
        $coreElements = array_map(
            static fn (array $element): array => ['name' => $element['name'], 'ctype' => $element['cType']],
            CoreContentElements::styleguideElements(),
        );
        if ($coreElements !== []) {
            $groups[] = [
                'groupId' => 'core',
                'groupTitle' => 'TYPO3 Core Elements',
                'elements' => $coreElements,
            ];
        }

        self::$cache = $groups;
        return self::$cache;
    }

    /**
     * @return list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, fixture: array<string, mixed>}>}>
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

        /** @var list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, fixture: array<string, mixed>}>}> $groups */
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
        // Native core elements carry their fixture in the PHP manifest, keyed by
        // the bare core cType (e.g. "bullets", "textmedia").
        foreach (CoreContentElements::styleguideElements() as $element) {
            $fixtures[$element['cType']] = $element['fixture'];
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
