<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ShadcnThemeTest extends TestCase
{
    public function testSiteSettingsExposeSupportedShadcnPresets(): void
    {
        $definitions = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.definitions.yaml');
        $settings = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');

        self::assertSame('b6G5977cw', $settings['desiderio']['shadcn']['preset'] ?? null);
        self::assertSame('radix-lyra', $settings['desiderio']['shadcn']['style'] ?? null);
        self::assertSame('preset', $settings['desiderio']['typography']['fontSans'] ?? null);
        self::assertSame('preset', $settings['desiderio']['layout']['radius'] ?? null);

        $presetDefinition = $definitions['settings']['desiderio.shadcn.preset'] ?? [];
        self::assertSame('b6G5977cw', $presetDefinition['default'] ?? null);
        self::assertArrayHasKey('b4hb38Fyj', $presetDefinition['enum'] ?? []);
        self::assertArrayHasKey('b0', $presetDefinition['enum'] ?? []);
        self::assertArrayHasKey('b3IWPgRwnI', $presetDefinition['enum'] ?? []);
        self::assertArrayHasKey('b6G5977cw', $presetDefinition['enum'] ?? []);
        self::assertArrayHasKey('custom', $presetDefinition['enum'] ?? []);

        $styleDefinition = $definitions['settings']['desiderio.shadcn.style'] ?? [];
        self::assertArrayHasKey('radix-lyra', $styleDefinition['enum'] ?? []);

        $radiusDefinition = $definitions['settings']['desiderio.layout.radius'] ?? [];
        self::assertSame('preset', $radiusDefinition['default'] ?? null);
        self::assertArrayHasKey('preset', $radiusDefinition['enum'] ?? []);

        $fontDefinition = $definitions['settings']['desiderio.typography.fontSans'] ?? [];
        self::assertSame('preset', $fontDefinition['default'] ?? null);
        self::assertArrayHasKey('preset', $fontDefinition['enum'] ?? []);
    }

    public function testTypoScriptIncludesShadcnAssetsAndBodyAttributes(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/setup.typoscript');

        self::assertStringContainsString('Resources/Public/Css/shadcn-theme.css', $typoScript);
        self::assertStringContainsString('Resources/Public/Css/desiderio-tailwind.css', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/alpine.min.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/desiderio.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/styleguide.js', $typoScript);
        self::assertStringContainsString('data-shadcn-preset="{$desiderio.shadcn.preset}"', $typoScript);
        self::assertStringContainsString('data-shadcn-style="{$desiderio.shadcn.style}"', $typoScript);
    }

    public function testThemeCssContainsLightDarkAndPresetTokens(): void
    {
        $themeCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/shadcn-theme.css');

        foreach (['--background', '--foreground', '--card', '--primary', '--border', '--ring', '--chart-1', '--sidebar'] as $token) {
            self::assertStringContainsString($token . ':', $themeCss);
        }

        self::assertStringContainsString(':root', $themeCss);
        self::assertStringContainsString('.dark', $themeCss);
        self::assertStringContainsString('body[data-shadcn-preset="b4hb38Fyj"]', $themeCss);
        self::assertStringContainsString('.dark body[data-shadcn-preset="b4hb38Fyj"]', $themeCss);
        self::assertStringContainsString('body[data-shadcn-preset="b3IWPgRwnI"]', $themeCss);
        self::assertStringContainsString('.dark body[data-shadcn-preset="b3IWPgRwnI"]', $themeCss);
        self::assertStringContainsString('body[data-shadcn-preset="b6G5977cw"]', $themeCss);
        self::assertStringContainsString('.dark body[data-shadcn-preset="b6G5977cw"]', $themeCss);
        self::assertStringContainsString('--radius: 0;', $themeCss);
        self::assertStringContainsString('JetBrains Mono Variable', $themeCss);
        self::assertStringContainsString('Nunito Sans Variable', $themeCss);
        self::assertStringContainsString('--d-font-sans', $themeCss);
        self::assertStringContainsString('--d-shadow-sm', $themeCss);
        self::assertStringContainsString('--d-info:', $themeCss);
        self::assertStringContainsString('--d-success:', $themeCss);
        self::assertStringContainsString('--d-warning:', $themeCss);
        self::assertStringContainsString('--d-danger:', $themeCss);
    }

    public function testTailwindBuildScansFluidComponentsAndContentBlocks(): void
    {
        $tailwindCss = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Tailwind/desiderio.css');
        $componentsJson = json_decode((string) file_get_contents(__DIR__ . '/../../components.json'), true);
        $packageJson = json_decode((string) file_get_contents(__DIR__ . '/../../package.json'), true);

        self::assertStringContainsString('@import "tailwindcss";', $tailwindCss);
        self::assertStringContainsString('@import "shadcn/tailwind.css";', $tailwindCss);
        self::assertStringContainsString('@source "../Components";', $tailwindCss);
        self::assertStringContainsString('@source "../Solr";', $tailwindCss);
        self::assertStringContainsString('@source "../Templates";', $tailwindCss);
        self::assertStringContainsString('@source "../../../ContentBlocks";', $tailwindCss);
        self::assertStringContainsString('@custom-variant dark', $tailwindCss);
        self::assertStringContainsString('@theme inline', $tailwindCss);
        self::assertStringContainsString('.ce-bodytext', $tailwindCss);
        self::assertStringContainsString('.desiderio-content-element', $tailwindCss);
        self::assertStringContainsString('.results-highlight', $tailwindCss);
        self::assertStringContainsString('#tx-solr-facets-in-use :where(a)', $tailwindCss);

        self::assertIsArray($componentsJson);
        self::assertSame('radix-lyra', $componentsJson['style'] ?? null);
        self::assertSame('tabler', $componentsJson['iconLibrary'] ?? null);
        self::assertSame('Resources/Private/Tailwind/desiderio.css', $componentsJson['tailwind']['css'] ?? null);

        self::assertIsArray($packageJson);
        self::assertSame('^4.2.4', $packageJson['dependencies']['tailwindcss'] ?? null);
        self::assertSame('^4.2.4', $packageJson['dependencies']['@tailwindcss/cli'] ?? null);
        self::assertSame('^5.2.7', $packageJson['dependencies']['@fontsource-variable/nunito-sans'] ?? null);
        self::assertSame('^4.5.0', $packageJson['dependencies']['shadcn'] ?? null);
    }

    public function testSolrFacetTemplateReusesSuggestStyles(): void
    {
        $facetTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Partials/Facets/Options.html');
        $frequentlySearchedTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Partials/Search/FrequentlySearched.html');
        $desiderioCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');

        self::assertStringContainsString('d-solr-suggest d-solr-suggest--facet', $facetTemplate);
        self::assertStringContainsString('d-solr-suggest__option', $facetTemplate);
        self::assertStringContainsString('d-solr-suggest__count', $facetTemplate);
        self::assertStringContainsString('d-solr-suggest d-solr-suggest--facet', $frequentlySearchedTemplate);
        self::assertStringContainsString('d-solr-facet__list', $frequentlySearchedTemplate);
        self::assertStringContainsString('d-solr-suggest__option', $frequentlySearchedTemplate);
        self::assertStringContainsString('.d-solr-suggest--facet', $desiderioCss);
        self::assertStringContainsString('.d-solr-facet__list', $desiderioCss);
    }

    public function testGeneratedTailwindCssContainsShadcnUtilities(): void
    {
        $generatedCssPath = __DIR__ . '/../../Resources/Public/Css/desiderio-tailwind.css';
        self::assertFileExists($generatedCssPath, 'Run npm run build:css after changing Fluid class recipes.');

        $generatedCss = (string) file_get_contents($generatedCssPath);
        foreach (['.bg-card', '.text-card-foreground', '.rounded-lg', '.border-border', '.data-active\\:bg-background', '.results-highlight'] as $class) {
            self::assertStringContainsString($class, $generatedCss);
        }

        preg_match_all('/url\\(\\.\\/files\\/([^\\)]+)\\)/', $generatedCss, $matches);
        self::assertNotEmpty($matches[1], 'Generated CSS should include local font asset references.');
        foreach (array_unique($matches[1]) as $fontFile) {
            self::assertFileExists(__DIR__ . '/../../Resources/Public/Css/files/' . $fontFile);
        }
    }

    public function testLoadedDesiderioCssKeepsSolrControlsFromInheritingProseLinkUnderlines(): void
    {
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');

        self::assertStringContainsString('.d-solr-suggest :where(a, a *)', $css);
        self::assertStringContainsString('#tx-solr-facets-in-use :where(a, a *)', $css);
        self::assertStringContainsString('#tx-solr-sorting :where(a, a *, summary, summary *)', $css);
        self::assertStringContainsString('text-decoration-line: none;', $css);
    }

    public function testStyleguidePageListsEveryElementOverview(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Pages/DesiderioStyleguide.fluid.html');

        self::assertStringContainsString('All content elements', $template);
        self::assertStringContainsString('docs__overview-card', $template);
        self::assertStringContainsString('d:styleguideFixtureSummary', $template);
        self::assertStringContainsString('255 production-ready content elements', $template);
        self::assertStringNotContainsString('250 production-ready content elements', $template);
    }
}
