<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;

/**
 * @phpstan-import-type StarterSite from StarterSiteDefinitions
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 */
final class StarterSiteDefinitionsTest extends TestCase
{
    public function testPresetStartersHaveUsefulPageTrees(): void
    {
        $starters = StarterSiteDefinitions::all();

        self::assertSame(['corporate', 'dashboard', 'editorial', 'portfolio', 'saas'], array_keys($starters));

        foreach ($starters as $slug => $starter) {
            self::assertIsString($starter['label'] ?? null, $slug . ' needs a label');
            self::assertIsString($starter['rootTitle'] ?? null, $slug . ' needs a homepage title');
            self::assertIsString($starter['rootSlug'] ?? null, $slug . ' needs a root slug');
            self::assertIsArray($starter['home'] ?? null, $slug . ' needs homepage data');
            self::assertIsArray($starter['subpages'] ?? null, $slug . ' needs subpages');
            self::assertGreaterThanOrEqual(10, count($starter['subpages']), $slug . ' must ship at least ten subpages');
            self::assertNotEmpty($starter['home']['content'] ?? [], $slug . ' homepage needs content elements');

            $serialized = json_encode($starter, JSON_THROW_ON_ERROR);
            self::assertStringNotContainsStringIgnoringCase('lorem', $serialized, $slug . ' must not seed lorem ipsum content');

            foreach ($starter['subpages'] as $page) {
                self::assertIsArray($page);
                self::assertIsString($page['title'] ?? null);
                self::assertIsString($page['slug'] ?? null);
                self::assertNotSame('', trim((string)$page['abstract']));
                self::assertNotEmpty($page['content'] ?? [], $page['title'] . ' needs content elements');
            }
        }
    }

    public function testStarterContentUsesExistingContentBlocks(): void
    {
        $knownCtypes = $this->loadKnownContentBlockTypeNames();

        foreach (StarterSiteDefinitions::all() as $starter) {
            foreach ($this->collectContentBlocks($starter) as $block) {
                self::assertIsString($block['ctype'] ?? null);
                self::assertArrayHasKey($block['ctype'], $knownCtypes, $block['ctype'] . ' must be a real Desiderio Content Block');
                self::assertIsArray($block['fields'] ?? null);
            }
        }
    }

    public function testDashboardStarterAddsDummyDashboardsToEveryPage(): void
    {
        $dashboard = StarterSiteDefinitions::all()['dashboard'];

        $pages = array_merge([$dashboard['home']], $dashboard['subpages']);
        self::assertGreaterThanOrEqual(11, count($pages));

        foreach ($pages as $page) {
            self::assertIsArray($page);
            $ctypes = array_map(
                static fn (array $block): string => (string)$block['ctype'],
                $page['content']
            );

            self::assertContains('desiderio_metricdashboard', $ctypes, 'Every dashboard starter page needs a metric dashboard block');
            self::assertContains('desiderio_datatable', $ctypes, 'Every dashboard starter page needs a data table block');
        }
    }

    public function testStarterSeedCommandIsRegisteredWithPresetOption(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/SeedStarterSitesCommand.php');

        self::assertStringContainsString("name: 'desiderio:starter:seed'", $source);
        self::assertStringContainsString("'preset'", $source);
        self::assertStringContainsString('StarterSiteDefinitions::all()', $source);
        self::assertStringContainsString('findOrCreateStarterPage', $source);
    }

    /**
     * @param StarterSite $starter
     * @return list<StarterBlock>
     */
    private function collectContentBlocks(array $starter): array
    {
        $blocks = [];
        foreach (array_merge([$starter['home']], $starter['subpages']) as $page) {
            foreach ($page['content'] as $block) {
                $blocks[] = $block;
            }
        }

        return $blocks;
    }

    /**
     * @return array<string, true>
     */
    private function loadKnownContentBlockTypeNames(): array
    {
        $knownCtypes = [];
        $configPaths = glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/config.yaml');
        if ($configPaths === false) {
            return [];
        }

        foreach ($configPaths as $configPath) {
            $config = Yaml::parseFile($configPath);
            if (!is_array($config)) {
                continue;
            }
            $typeName = $config['typeName'] ?? null;
            if (is_string($typeName) && $typeName !== '') {
                $knownCtypes[$typeName] = true;
            }
        }

        return $knownCtypes;
    }
}
