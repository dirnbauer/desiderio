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

        // Style-agnostic: validate that whatever shadcn preset/style/icon is CONFIGURED is a
        // supported option and that the enums still offer every supported choice — never that a
        // specific style such as radix-lyra is the active one. Switching styles via
        // shadcn/create must not break the suite.
        $configuredPreset = $shadcnSettings['preset'] ?? null;
        $configuredStyle = $shadcnSettings['style'] ?? null;
        $configuredIconLibrary = $shadcnSettings['iconLibrary'] ?? null;
        self::assertIsString($configuredPreset);
        self::assertIsString($configuredStyle);
        self::assertIsString($configuredIconLibrary);
        self::assertSame('preset', $typographySettings['fontSans'] ?? null);
        self::assertSame('preset', $layoutSettings['radius'] ?? null);

        $settingDefinitions = $definitions['settings'] ?? null;
        self::assertIsArray($settingDefinitions);

        $presetDefinition = $settingDefinitions['desiderio.shadcn.preset'] ?? null;
        self::assertIsArray($presetDefinition);
        $presetEnum = $presetDefinition['enum'] ?? null;
        self::assertIsArray($presetEnum);
        self::assertArrayHasKey($presetDefinition['default'] ?? '', $presetEnum, 'preset default must be a supported enum option');
        self::assertArrayHasKey($configuredPreset, $presetEnum, 'configured shadcn.preset must be a supported enum option');
        foreach (['b4hb38Fyj', 'b0', 'b3IWPgRwnI', 'b6G5977cw', 'custom'] as $presetKey) {
            self::assertArrayHasKey($presetKey, $presetEnum);
        }

        $styleDefinition = $settingDefinitions['desiderio.shadcn.style'] ?? null;
        self::assertIsArray($styleDefinition);
        $styleEnum = $styleDefinition['enum'] ?? null;
        self::assertIsArray($styleEnum);
        self::assertArrayHasKey($configuredStyle, $styleEnum, 'configured shadcn.style must be a supported enum option');
        foreach (['radix-nova', 'radix-mira', 'radix-lyra', 'custom'] as $styleKey) {
            self::assertArrayHasKey($styleKey, $styleEnum);
        }

        $iconLibraryDefinition = $settingDefinitions['desiderio.shadcn.iconLibrary'] ?? null;
        self::assertIsArray($iconLibraryDefinition);
        $iconLibraryEnum = $iconLibraryDefinition['enum'] ?? null;
        self::assertIsArray($iconLibraryEnum);
        self::assertArrayHasKey($iconLibraryDefinition['default'] ?? '', $iconLibraryEnum, 'iconLibrary default must be a supported enum option');
        self::assertArrayHasKey($configuredIconLibrary, $iconLibraryEnum, 'configured shadcn.iconLibrary must be a supported enum option');
        self::assertArrayHasKey('lucide', $iconLibraryEnum);
        self::assertArrayHasKey('tabler', $iconLibraryEnum);
        self::assertArrayHasKey('phosphor', $iconLibraryEnum);

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
        self::assertStringContainsString('Resources/Public/Js/astro.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/desiderio.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/charts.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/styleguide.js', $typoScript);
        self::assertStringContainsString('data-shadcn-preset="{$desiderio.shadcn.preset}"', $typoScript);
        self::assertStringContainsString('data-shadcn-style="{$desiderio.shadcn.style}"', $typoScript);
        self::assertStringContainsString('data-icon-library="{$desiderio.shadcn.iconLibrary}"', $typoScript);
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

        $componentsCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/components.css');
        self::assertStringContainsString('body[data-icon-library="lucide"] .d-icon[data-icon-library="lucide"]', $componentsCss);
        self::assertStringContainsString('body[data-icon-library="tabler"] .d-icon[data-icon-library="tabler"]', $componentsCss);
        self::assertStringContainsString('body[data-icon-library="phosphor"] .d-icon[data-icon-library="phosphor"]', $componentsCss);
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
        $shadcnSettings = self::shadcnSettings();
        self::assertSame($shadcnSettings['style'] ?? null, $componentsJson['style'] ?? null, 'components.json style must match the configured shadcn.style in settings.yaml');
        self::assertSame($shadcnSettings['iconLibrary'] ?? null, $componentsJson['iconLibrary'] ?? null, 'components.json iconLibrary must match the configured shadcn.iconLibrary in settings.yaml');
        self::assertSame('Resources/Private/Tailwind/desiderio.css', $tailwindConfig['css'] ?? null);
        self::assertArrayNotHasKey('@shadcn', $registries, 'The shadcn CLI provides @shadcn as a built-in registry and rejects overriding it.');
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
        self::assertSame('php Build/Scripts/sync-shadcn-fluid-primitives.php', $scripts['shadcn:sync-fluid'] ?? null);
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
        $lastSearchesTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Partials/Search/LastSearches.html');
        $desiderioCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');

        self::assertStringContainsString('d-solr-suggest d-solr-suggest--facet', $facetTemplate);
        self::assertStringContainsString('d-solr-suggest__option', $facetTemplate);
        self::assertStringContainsString('d-solr-suggest__count', $facetTemplate);
        self::assertStringContainsString('d-solr-suggest d-solr-suggest--facet', $frequentlySearchedTemplate);
        self::assertStringContainsString('d-solr-facet__list', $frequentlySearchedTemplate);
        self::assertStringContainsString('d-solr-suggest__option', $frequentlySearchedTemplate);
        self::assertStringContainsString('d-solr-suggest d-solr-suggest--facet', $lastSearchesTemplate);
        self::assertStringContainsString('d-solr-facet__list', $lastSearchesTemplate);
        self::assertStringContainsString('.d-solr-suggest--facet', $desiderioCss);
        self::assertStringContainsString('.d-solr-facet__list', $desiderioCss);
        self::assertStringContainsString('.d-solr-create__sidebar .d-solr-suggest__option', $desiderioCss);
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
        self::assertStringContainsString('.d-solr-result-card :where(a, a *)', $css);
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
     * @return array<string, mixed> the configured desiderio.shadcn settings block
     */
    private static function shadcnSettings(): array
    {
        $settings = self::parseYamlFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');
        $siteSettings = $settings['desiderio'] ?? null;
        self::assertIsArray($siteSettings);
        $shadcn = $siteSettings['shadcn'] ?? null;
        self::assertIsArray($shadcn);

        /** @var array<string, mixed> $shadcn */
        return $shadcn;
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
