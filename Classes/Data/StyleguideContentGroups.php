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
     * Benefit-led intro for a chapter page, seeded above the element demos.
     * Returns a desiderio_headersection fixture, or null for groups without one.
     *
     * @return array{eyebrow: string, header: string, subheadline: string, variant: string}|null
     */
    public static function chapterIntro(string $groupId): ?array
    {
        $intros = [
            'hero' => ['Hero & Landing Intros · 21 elements', 'Win the first screen', 'Split, video, countdown, stats — 21 heroes that make the first impression land, each themed by the active preset.'],
            'navigation' => ['Navigation & Wayfinding · 23 elements', 'Get people where they are going', '23 navbars, mega-menus, breadcrumbs and pagination patterns — wayfinding that stays on-brand and accessible.'],
            'content' => ['Content & Editorial · 24 elements', 'Publish like a newsroom, not a ticket queue', 'Text, media, quotes, tabs, timelines and callouts — 24 editorial elements your writers arrange themselves, each with a backend preview so nobody publishes blind.'],
            'features' => ['Features & Benefits · 25 elements', 'Explain what you do, in whatever shape fits', '25 grids, bento layouts, comparisons, tabs and timelines. Whatever the argument, there is a block that makes it scannable — and it already matches your theme.'],
            'pricing' => ['Plans & Pricing · 25 elements', 'The blocks that close the deal', '25 pricing tables, toggles, calculators and order summaries — monetisation patterns that usually cost a sprint.'],
            'social-proof' => ['Trust & Social Proof · 25 elements', 'Show the proof before you ask for the sale', '25 testimonial walls, logo clouds, case studies, awards and rating displays. Assemble credibility at a glance — every one styled by the active theme preset.'],
            'team' => ['People & Team · 24 elements', 'Put a face to the company', '24 team grids, org charts, founder stories and advisor boards. Introduce the people behind the work without wrestling a layout — 24 ways, all on one token contract.'],
            'data' => ['Data & Dashboards · 29 elements', 'Numbers that render server-side and stay accessible', '29 KPI cards, charts, status boards and changelogs across nine chart types — drawn on the server with a slim vanilla layer. No React, no hydration, no drift.'],
            'conversion' => ['Leads & Conversion · 25 elements', 'Turn a visit into a lead — without a developer', '25 CTAs, forms, lead magnets and pricing prompts. Every form is a real ext:form with validation, spam protection and CRM sync, so marketing ships the funnel itself.'],
            'footer' => ['Footers & Utility Areas · 23 elements', 'The unglamorous 80%, already finished', '23 footers, cookie banners, legal blocks, breadcrumbs and utility bars — the parts every site needs and nobody enjoys building. Accessible and on-brand out of the box.'],
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
            'hero' => ['All 21 hero elements ship in the free package', 'Split, video, countdown and stats heroes — GPL-2.0, no feature gates. Install and make the first screen land.'],
            'navigation' => ['All 23 navigation elements ship free', 'Navbars, mega-menus and breadcrumbs that inherit your theme automatically. Free under GPL-2.0.'],
            'content' => ['The whole editorial toolkit, already built', 'All 24 content & editorial elements — text, media, quotes, tabs, timelines — ship free under GPL-2.0.'],
            'features' => ['All 25 feature blocks ship in the free package', 'Grids, bento layouts, comparisons and timelines to explain what you do. Install and start building.'],
            'pricing' => ['All 25 pricing elements ship free', 'Tier tables, toggles, calculators and order summaries — the blocks that close, free under GPL-2.0.'],
            'social-proof' => ['All 25 trust elements ship free', 'Testimonial walls, logo clouds, case studies and rating displays. Install and start building today.'],
            'team' => ['All 24 people elements ship free', 'Team grids, org charts, founder stories and advisor boards — free under GPL-2.0.'],
            'data' => ['29 data elements. Nine chart types. Zero JS framework.', 'KPI cards, charts and status boards, server-rendered and accessible. Free under GPL-2.0.'],
            'footer' => ['The unglamorous 80%, already done', '23 footers, cookie banners, legal blocks and utility bars — the parts nobody enjoys building. Free under GPL-2.0.'],
        ];
        if (!isset($ctas[$groupId])) {
            return null;
        }
        [$header, $description] = $ctas[$groupId];

        return [
            'header' => $header,
            'description' => $description,
            'cta_text' => 'Get Desiderio free',
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
