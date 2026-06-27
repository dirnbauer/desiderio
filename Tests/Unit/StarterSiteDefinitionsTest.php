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

        self::assertSame(['corporate'], array_keys($starters));

        foreach ($starters as $slug => $starter) {
            self::assertIsString($starter['label'] ?? null, $slug . ' needs a label');
            self::assertIsString($starter['rootTitle'] ?? null, $slug . ' needs a homepage title');
            self::assertIsString($starter['rootSlug'] ?? null, $slug . ' needs a root slug');
            self::assertIsString($starter['purpose'] ?? null, $slug . ' needs a clear template purpose');
            self::assertNotSame('', trim($starter['purpose']), $slug . ' purpose must not be empty');
            self::assertIsArray($starter['home'] ?? null, $slug . ' needs homepage data');
            self::assertIsArray($starter['subpages'] ?? null, $slug . ' needs subpages');
            self::assertGreaterThanOrEqual(10, count($starter['subpages']), $slug . ' must ship at least ten subpages');
            self::assertNotEmpty($starter['home']['content'] ?? [], $slug . ' homepage needs content elements');
            $visibleNavPages = array_values(array_filter(
                $starter['subpages'],
                static fn (array $page): bool => !($page['navHidden'] ?? false)
            ));
            self::assertGreaterThanOrEqual(4, count($visibleNavPages), $slug . ' needs useful primary navigation');
            self::assertLessThanOrEqual(7, count($visibleNavPages), $slug . ' must keep primary navigation scannable');

            $serialized = json_encode($starter, JSON_THROW_ON_ERROR);
            self::assertStringNotContainsStringIgnoringCase('lorem', $serialized, $slug . ' must not seed lorem ipsum content');

            foreach ($starter['subpages'] as $page) {
                self::assertIsArray($page);
                self::assertIsString($page['title'] ?? null);
                self::assertIsString($page['slug'] ?? null);
                self::assertNotSame('', trim((string)$page['abstract']));
                self::assertIsBool($page['navHidden'] ?? null);
                self::assertNotEmpty($page['content'] ?? [], $page['title'] . ' needs content elements');
            }
        }
    }

    public function testStarterHomepagesKeepTemplatePurposeSpecific(): void
    {
        $requiredTerms = [
            'corporate' => ['procurement', 'governance', 'proof', 'contact'],
        ];

        foreach (StarterSiteDefinitions::all() as $slug => $starter) {
            $copy = strtolower($this->flattenStrings([
                $starter['purpose'],
                $starter['abstract'],
                $starter['home']['content'],
            ]));

            foreach ($requiredTerms[$slug] as $term) {
                self::assertStringContainsString($term, $copy, $slug . ' homepage must cover ' . $term);
            }
        }
    }

    public function testVisitorCopyDoesNotLeakInternalSeedInstructions(): void
    {
        $bannedPhrases = [
            'seeded story',
            'seeded starter',
            'dummy dashboard',
            'dummy product',
            'replace this',
            'first draft',
            'starter content',
            'layout review',
            'homepage mockup',
        ];

        foreach (StarterSiteDefinitions::all() as $slug => $starter) {
            $copy = strtolower($this->flattenStrings([
                $starter['purpose'],
                $starter['abstract'],
                $starter['home']['content'],
                $starter['subpages'],
            ]));

            foreach ($bannedPhrases as $phrase) {
                self::assertStringNotContainsString($phrase, $copy, $slug . ' leaks internal copy: ' . $phrase);
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

    public function testStarterContentDoesNotSeedFooterBlocksIntoPageBodies(): void
    {
        $footerCtypes = ['desiderio_footerbrand', 'desiderio_footerminimal'];

        foreach (StarterSiteDefinitions::all() as $slug => $starter) {
            $ctypes = array_map(
                static fn (array $block): string => (string)$block['ctype'],
                $this->collectContentBlocks($starter)
            );

            foreach ($footerCtypes as $ctype) {
                self::assertNotContains($ctype, $ctypes, $slug . ' must use the page template footer, not a footer content block');
            }
        }
    }

    public function testStartpageTemplatesOnlyRenderContentAreasAndTemplateChrome(): void
    {
        $templatePaths = [
            'corporate' => __DIR__ . '/../../Resources/Private/Presets/Corporate/Templates/Pages/DesiderioStartpage.fluid.html',
        ];

        $bannedTemplateContent = [
            'page.pageRecord.abstract',
            '<dl',
            'd:atom.button',
            'corporate.start.',
        ];

        foreach ($templatePaths as $slug => $path) {
            self::assertFileExists($path);
            $template = (string)file_get_contents($path);

            self::assertSame(1, substr_count($template, 'content.main'), $slug . ' startpage must render the main content area exactly once');
            self::assertStringNotContainsString('content.stage', $template, $slug . ' startpage must not render the removed stage content area');
            self::assertSame(
                1,
                substr_count($template, '<h1 class="sr-only">{page.pageRecord.title}</h1>'),
                $slug . ' startpage must render exactly one screen-reader-only h1 band title (heroes render h2)'
            );
            self::assertSame(1, substr_count($template, '<h1'), $slug . ' startpage must not render additional h1 elements');

            foreach ($bannedTemplateContent as $needle) {
                self::assertStringNotContainsString($needle, $template, $slug . ' startpage must not hard-code page content: ' . $needle);
            }
        }
    }

    public function testStarterHomepagesUseAdaptedContentFromSourceShowcaseGroups(): void
    {
        $sourceGroups = [
            664 => ['desiderio_articlegrid', 'desiderio_resourcelibrary'],
            665 => ['desiderio_ctacard'],
            666 => ['desiderio_metricdashboard', 'desiderio_kpicards', 'desiderio_datatable'],
            667 => ['desiderio_featurecards', 'desiderio_featurelist'],
            668 => ['desiderio_footerbrand', 'desiderio_sitemapgrid'],
            669 => ['desiderio_herostats', 'desiderio_herosaas', 'desiderio_heroproduct'],
            670 => ['desiderio_headersection', 'desiderio_navtabs'],
            671 => ['desiderio_pricingthreetier'],
            672 => ['desiderio_casestudygrid', 'desiderio_testimonialgrid'],
            673 => ['desiderio_companyvalues', 'desiderio_teamgridminimal'],
        ];

        foreach (StarterSiteDefinitions::all() as $slug => $starter) {
            $homepageCtypes = array_map(
                static fn (array $block): string => (string)$block['ctype'],
                $starter['home']['content']
            );

            foreach ($sourceGroups as $sourcePageUid => $ctypes) {
                self::assertNotSame(
                    [],
                    array_values(array_intersect($homepageCtypes, $ctypes)),
                    sprintf('%s homepage must include adapted content from source showcase page %d', $slug, $sourcePageUid)
                );
            }
        }
    }

    public function testStarterSeedCommandIsRegisteredWithPresetOption(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/SeedStarterSitesCommand.php');

        self::assertStringContainsString("name: 'desiderio:starter:seed'", $source);
        self::assertStringContainsString("'preset'", $source);
        self::assertStringContainsString("'root-map'", $source);
        self::assertStringContainsString("'replace-content'", $source);
        self::assertStringContainsString('StarterSiteDefinitions::all()', $source);
        self::assertStringContainsString('findExistingPageUid', $source);
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
     * @param mixed $value
     */
    private function flattenStrings(mixed $value): string
    {
        if (is_string($value)) {
            return $value . ' ';
        }

        if (!is_array($value)) {
            return '';
        }

        $copy = '';
        foreach ($value as $child) {
            $copy .= $this->flattenStrings($child);
        }

        return $copy;
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
