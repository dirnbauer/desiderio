<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ShadcnThemeTest extends TestCase
{
    public function testSiteSettingsExposeSupportedShadcnPresets(): void
    {
        $definitions = self::parseYamlFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.definitions.yaml');
        $settings = self::parseYamlFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');

        $siteSettings = $settings['desiderio'] ?? null;
        self::assertIsArray($siteSettings);
        $shadcnSettings = $siteSettings['shadcn'] ?? null;
        self::assertIsArray($shadcnSettings);
        $typographySettings = $siteSettings['typography'] ?? null;
        self::assertIsArray($typographySettings);
        $layoutSettings = $siteSettings['layout'] ?? null;
        self::assertIsArray($layoutSettings);

        self::assertSame('b6G5977cw', $shadcnSettings['preset'] ?? null);
        self::assertSame('radix-lyra', $shadcnSettings['style'] ?? null);
        self::assertSame('preset', $typographySettings['fontSans'] ?? null);
        self::assertSame('preset', $layoutSettings['radius'] ?? null);

        $settingDefinitions = $definitions['settings'] ?? null;
        self::assertIsArray($settingDefinitions);

        $presetDefinition = $settingDefinitions['desiderio.shadcn.preset'] ?? null;
        self::assertIsArray($presetDefinition);
        $presetEnum = $presetDefinition['enum'] ?? null;
        self::assertIsArray($presetEnum);
        self::assertSame('b6G5977cw', $presetDefinition['default'] ?? null);
        self::assertArrayHasKey('b4hb38Fyj', $presetEnum);
        self::assertArrayHasKey('b0', $presetEnum);
        self::assertArrayHasKey('b3IWPgRwnI', $presetEnum);
        self::assertArrayHasKey('b6G5977cw', $presetEnum);
        self::assertArrayHasKey('custom', $presetEnum);

        $styleDefinition = $settingDefinitions['desiderio.shadcn.style'] ?? null;
        self::assertIsArray($styleDefinition);
        $styleEnum = $styleDefinition['enum'] ?? null;
        self::assertIsArray($styleEnum);
        self::assertArrayHasKey('radix-lyra', $styleEnum);

        $radiusDefinition = $settingDefinitions['desiderio.layout.radius'] ?? null;
        self::assertIsArray($radiusDefinition);
        $radiusEnum = $radiusDefinition['enum'] ?? null;
        self::assertIsArray($radiusEnum);
        self::assertSame('preset', $radiusDefinition['default'] ?? null);
        self::assertArrayHasKey('preset', $radiusEnum);

        $fontDefinition = $settingDefinitions['desiderio.typography.fontSans'] ?? null;
        self::assertIsArray($fontDefinition);
        $fontEnum = $fontDefinition['enum'] ?? null;
        self::assertIsArray($fontEnum);
        self::assertSame('preset', $fontDefinition['default'] ?? null);
        self::assertArrayHasKey('preset', $fontEnum);
    }

    public function testTypoScriptIncludesShadcnAssetsAndBodyAttributes(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/setup.typoscript');

        self::assertStringContainsString('Resources/Public/Css/shadcn-theme.css', $typoScript);
        self::assertStringContainsString('Resources/Public/Css/desiderio-tailwind.css', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/alpine.min.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/desiderio.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/charts.js', $typoScript);
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
        $componentsJson = self::decodeJsonFile(__DIR__ . '/../../components.json');
        $packageJson = self::decodeJsonFile(__DIR__ . '/../../package.json');

        self::assertStringContainsString('@import "tailwindcss";', $tailwindCss);
        self::assertStringContainsString('@import "shadcn/tailwind.css";', $tailwindCss);
        self::assertStringContainsString('@source "../Components";', $tailwindCss);
        self::assertStringContainsString('@source "../Solr";', $tailwindCss);
        self::assertStringContainsString('@source "../Templates";', $tailwindCss);
        self::assertStringContainsString('@source "../Extensions";', $tailwindCss);
        self::assertStringContainsString('@source "../ShadcnUi";', $tailwindCss);
        self::assertStringContainsString('@source "../../../ContentBlocks";', $tailwindCss);
        self::assertStringContainsString('@custom-variant dark', $tailwindCss);
        self::assertStringContainsString('@theme inline', $tailwindCss);
        self::assertStringContainsString('.ce-bodytext', $tailwindCss);
        self::assertStringContainsString('.desiderio-content-element', $tailwindCss);
        self::assertStringContainsString('.results-highlight', $tailwindCss);
        self::assertStringContainsString('#tx-solr-facets-in-use :where(a)', $tailwindCss);

        $tailwindConfig = $componentsJson['tailwind'] ?? null;
        self::assertIsArray($tailwindConfig);
        $registries = $componentsJson['registries'] ?? null;
        self::assertIsArray($registries);
        self::assertSame('radix-lyra', $componentsJson['style'] ?? null);
        self::assertSame('tabler', $componentsJson['iconLibrary'] ?? null);
        self::assertSame('Resources/Private/Tailwind/desiderio.css', $tailwindConfig['css'] ?? null);
        self::assertSame('Resources/Public/ShadcnRegistry/{name}.json', $registries['@desiderio'] ?? null);

        $dependencies = $packageJson['dependencies'] ?? null;
        self::assertIsArray($dependencies);
        $scripts = $packageJson['scripts'] ?? null;
        self::assertIsArray($scripts);
        self::assertSame('^4.2.4', $dependencies['tailwindcss'] ?? null);
        self::assertSame('^4.2.4', $dependencies['@tailwindcss/cli'] ?? null);
        self::assertSame('^5.2.7', $dependencies['@fontsource-variable/nunito-sans'] ?? null);
        self::assertSame('^4.5.0', $dependencies['shadcn'] ?? null);
        self::assertSame('shadcn info --json', $scripts['shadcn:info'] ?? null);
        self::assertSame('shadcn build --output Resources/Public/ShadcnRegistry', $scripts['registry:build'] ?? null);
    }

    public function testShadcnCliContextAndRegistryAreConfigured(): void
    {
        $tsconfig = self::decodeJsonFile(__DIR__ . '/../../tsconfig.json');
        $registry = self::decodeJsonFile(__DIR__ . '/../../registry.json');

        $compilerOptions = $tsconfig['compilerOptions'] ?? null;
        self::assertIsArray($compilerOptions);
        $paths = $compilerOptions['paths'] ?? null;
        self::assertIsArray($paths);
        self::assertSame('.', $compilerOptions['baseUrl'] ?? null);
        self::assertSame(['./.shadcn/scratch/*'], $paths['@/*'] ?? null);

        self::assertSame('https://ui.shadcn.com/schema/registry.json', $registry['$schema'] ?? null);
        self::assertSame('desiderio', $registry['name'] ?? null);
        $items = $registry['items'] ?? null;
        self::assertIsArray($items);
        self::assertNotEmpty($items);

        $itemNames = [];
        foreach ($items as $item) {
            self::assertIsArray($item);
            $itemName = $item['name'] ?? null;
            self::assertIsString($itemName);
            $itemNames[] = $itemName;
        }
        self::assertContains('desiderio-shadcn-theme', $itemNames);
        self::assertContains('desiderio-content-element-runtime', $itemNames);
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

    public function testSolrSuggestDropdownUsesContentTypeLabels(): void
    {
        $formTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Partials/Search/Form.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/desiderio.js');

        self::assertStringContainsString("data-d-solr-type-label-pages=\"{f:translate(key: 'solr.contentType.pages'", $formTemplate);
        self::assertStringContainsString("data-d-solr-type-label-news=\"{f:translate(key: 'solr.contentType.news'", $formTemplate);
        self::assertStringContainsString("data-d-solr-type-label-addresses=\"{f:translate(key: 'solr.contentType.addresses'", $formTemplate);
        self::assertStringContainsString('tx_news_domain_model_news: input.dataset.dSolrTypeLabelNews', $javascript);
        self::assertStringContainsString('tt_address: input.dataset.dSolrTypeLabelAddresses', $javascript);
        self::assertStringContainsString('resultType: this.getContentTypeLabel(document.type)', $javascript);
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

    /**
     * @return array<string, mixed>
     */
    private static function parseYamlFile(string $path): array
    {
        $data = Yaml::parseFile($path);
        self::assertIsArray($data);

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeJsonFile(string $path): array
    {
        $data = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($data);

        /** @var array<string, mixed> $data */
        return $data;
    }
}
