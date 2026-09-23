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
    /** @var list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, keywords?: list<string>}>}>|null */
    private static ?array $cache = null;

    /** @var array<string, array<string, mixed>>|null */
    private static ?array $fixtureCache = null;

    /**
     * @return list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, keywords?: list<string>}>}>
     */
    public static function getGroups(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        /** @var list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, keywords?: list<string>}>}> $groups */
        $groups = self::loadJson('EXT:desiderio/Resources/Private/Data/styleguide-content-groups.json');

        // The classic TYPO3 core content elements get their own styleguide page,
        // derived from the single CoreContentElements manifest (plugins excluded:
        // they need a configured form/flexform and Powermail has its own seeder).
        $coreElements = array_map(
            static fn(array $element): array => ['name' => $element['name'], 'ctype' => $element['cType']],
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
     * @return list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, keywords?: list<string>, fixture: array<string, mixed>}>}>
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

        /** @var list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, keywords?: list<string>, fixture: array<string, mixed>}>}> $groups */
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
     * Benefit-led intro for a chapter page, seeded above the element demos.
     * Returns a desiderio_headersection fixture, or null for groups without one.
     *
     * @return array{eyebrow: string, header: string, subheadline: string, variant: string}|null
     */
    public static function chapterIntro(string $groupId): ?array
    {
        $intros = [
            'hero' => ['21 hero elements', 'Heroes for the first screen of a page', 'Split, video, countdown and stats layouts. Each hero takes its colours from the active theme preset.'],
            'navigation' => ['23 navigation elements', 'Menus, breadcrumbs and pagination', 'Navigation bars, mega menus, breadcrumbs and pagination patterns. All of them follow the active theme and are accessible.'],
            'content' => ['24 content elements', 'Layouts for articles and text pages', 'Text, media, quotes, tabs, timelines and callouts. Editors arrange them on their own, and every element has a backend preview.'],
            'features' => ['25 feature elements', 'Ways to explain what you offer', 'Grids, bento layouts, comparisons, tabs and timelines make a product easy to scan. They already match your theme preset.'],
            'pricing' => ['25 pricing elements', 'Pricing tables, toggles and calculators', 'Plan tables, monthly and yearly toggles, calculators and order summaries. Building these by hand often takes a full sprint.'],
            'social-proof' => ['25 trust elements', 'Testimonials, logos and case studies', 'Testimonial walls, logo clouds, case studies, awards and ratings. Each one follows the active theme preset.'],
            'team' => ['24 team elements', 'Introduce the people behind the work', 'Team grids, organisation charts, founder stories and advisor boards. They share one set of design tokens, so no layout work is needed.'],
            'data' => ['29 data elements', 'Charts that render on the server', 'KPI cards, charts, status boards and changelogs, with nine chart types. They render on the server, without React, and include accessible data tables.'],
            'conversion' => ['25 conversion elements', 'Turn visits into leads without a developer', 'Calls to action, forms, lead magnets and pricing prompts. Every form is a real TYPO3 form with validation, spam protection and CRM sync.'],
            'footer' => ['23 footer elements', 'The parts every site needs', 'Footers, cookie banners, legal sections, breadcrumbs and utility bars. They follow your theme and are accessible from the start.'],
        ];
        if (!isset($intros[$groupId])) {
            return null;
        }
        [$eyebrow, $header, $subheadline] = $intros[$groupId];

        return ['eyebrow' => $eyebrow, 'header' => $header, 'subheadline' => $subheadline, 'variant' => 'center'];
    }

    /**
     * Closing conversion banner for a chapter page. The conversion chapter
     * already demos CTA elements aplenty and gets none.
     *
     * @return array{header: string, description: string, cta_text: string, cta_link: string, bg_style: string}|null
     */
    public static function chapterCta(string $groupId): ?array
    {
        $ctas = [
            'hero' => ['All 21 heroes are in the free package', 'Split, video, countdown and stats heroes, under GPL-2.0. Paid plans add support, not features.'],
            'navigation' => ['All 23 navigation elements are free', 'Navigation bars, mega menus and breadcrumbs that follow your theme. Free under GPL-2.0.'],
            'content' => ['All 24 content elements are free', 'Text, media, quotes, tabs and timelines for your articles. Free under GPL-2.0.'],
            'features' => ['All 25 feature elements are free', 'Grids, bento layouts, comparisons and timelines to explain what you offer. Free under GPL-2.0.'],
            'pricing' => ['All 25 pricing elements are free', 'Plan tables, toggles, calculators and order summaries. Free under GPL-2.0.'],
            'social-proof' => ['All 25 trust elements are free', 'Testimonial walls, logo clouds, case studies and ratings. Free under GPL-2.0.'],
            'team' => ['All 24 team elements are free', 'Team grids, organisation charts, founder stories and advisor boards. Free under GPL-2.0.'],
            'data' => ['29 data elements, no JavaScript framework', 'KPI cards, charts and status boards, rendered on the server with accessible tables. Free under GPL-2.0.'],
            'footer' => ['All 23 footer elements are free', 'Footers, cookie banners, legal sections and utility bars. Free under GPL-2.0.'],
        ];
        if (!isset($ctas[$groupId])) {
            return null;
        }
        [$header, $description] = $ctas[$groupId];

        return [
            'header' => $header,
            'description' => $description,
            'cta_text' => 'Get started free',
            'cta_link' => 'https://github.com/dirnbauer/desiderio',
            'bg_style' => 'primary',
        ];
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
