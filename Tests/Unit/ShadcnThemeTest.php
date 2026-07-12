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
        self::assertSame('tabler', $shadcnSettings['iconLibrary'] ?? null);
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
        foreach (['radix-vega', 'radix-nova', 'radix-maia', 'radix-lyra', 'radix-mira', 'radix-luma', 'radix-sera', 'radix-rhea'] as $style) {
            self::assertArrayHasKey($style, $styleEnum);
        }

        $iconLibraryDefinition = $settingDefinitions['desiderio.shadcn.iconLibrary'] ?? null;
        self::assertIsArray($iconLibraryDefinition);
        $iconLibraryEnum = $iconLibraryDefinition['enum'] ?? null;
        self::assertIsArray($iconLibraryEnum);
        self::assertSame('tabler', $iconLibraryDefinition['default'] ?? null);
        foreach (['lucide', 'tabler', 'hugeicons', 'phosphor', 'remixicon'] as $iconLibrary) {
            self::assertArrayHasKey($iconLibrary, $iconLibraryEnum);
        }

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
        self::assertStringNotContainsString('alpine', strtolower($typoScript));
        self::assertStringContainsString('Resources/Public/Js/astro.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/desiderio.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/charts.js', $typoScript);
        self::assertStringContainsString('Resources/Public/Js/styleguide.js', $typoScript);
        self::assertStringContainsString('data-shadcn-preset="', $typoScript);
        self::assertStringContainsString('levelfield:-1, tx_desiderio_shadcn_preset, slide', $typoScript);
        self::assertStringContainsString('ifEmpty = {$desiderio.shadcn.preset}', $typoScript);
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
        // Create presets carry their own radius; b6G5977cw follows the
        // shadcn/create default (0.625rem) — a plain "--radius: 0;" square
        // override was upstream drift and must not return.
        self::assertStringContainsString('--radius: 0.45rem;', $themeCss);
        self::assertStringNotContainsString('--radius: 0;', $themeCss);
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
        self::assertStringContainsString('body[data-icon-library="hugeicons"] .d-icon[data-icon-library="hugeicons"]', $componentsCss);
        self::assertStringContainsString('body[data-icon-library="phosphor"] .d-icon[data-icon-library="phosphor"]', $componentsCss);
        self::assertStringContainsString('body[data-icon-library="remixicon"] .d-icon[data-icon-library="remixicon"]', $componentsCss);
    }

    public function testControlShapeTokensSwitchPerPreset(): void
    {
        // Approach D: component shape (radius + control density) follows preset-switchable
        // tokens, so switching the shadcn preset re-cascades shape at runtime.
        $tailwindSource = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Tailwind/desiderio.css');
        foreach (['@utility d-control-h', '@utility d-control-text', '@utility d-control-px'] as $utility) {
            self::assertStringContainsString($utility, $tailwindSource);
        }
        self::assertStringContainsString('var(--d-control-h', $tailwindSource);

        // Token VALUES live per preset in shadcn-theme.css: default profile in :root,
        // compact profile in the flat radix-lyra preset block.
        $themeCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/shadcn-theme.css');
        self::assertStringContainsString('--d-control-h: 2.25rem;', $themeCss);
        self::assertStringContainsString('--d-control-h: 2rem;', $themeCss);
        self::assertStringContainsString('--d-control-text: 0.875rem;', $themeCss);

        // The generated atoms reference the tokens instead of frozen literals.
        $input = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Components/Atom/Input/Input.fluid.html');
        self::assertStringContainsString('d-control-h', $input);
        self::assertStringContainsString('rounded-md', $input);
        self::assertStringContainsString('d-control-text', $input);
        self::assertStringNotContainsString('rounded-none', $input);

        // Radio buttons must stay circular regardless of preset.
        $shadcnClass = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/ShadcnClass.html');
        self::assertStringContainsString('rounded-full', $shadcnClass);

        // Focus-ring width and card elevation are tokenized too.
        self::assertStringContainsString('ring-(length:--d-ring-width)', $input);
        $card = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Components/Molecule/Card/Card.fluid.html');
        self::assertStringContainsString('shadow-[var(--d-surface-shadow)]', $card);
        self::assertStringContainsString('--d-ring-width: 3px;', $themeCss);
        self::assertStringContainsString('--d-surface-shadow: 0 0 #0000;', $themeCss);
        // The flat radix-lyra default keeps a 1px ring.
        self::assertMatchesRegularExpression('/body\[data-shadcn-preset="b6G5977cw"\][^}]*--d-ring-width: 1px;/s', $themeCss);

        // The custom utilities compile and resolve their per-preset variables.
        $tailwindCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio-tailwind.css');
        self::assertStringContainsString('height:var(--d-control-h', $tailwindCss);
        self::assertStringContainsString('font-size:var(--d-control-text', $tailwindCss);
        self::assertStringContainsString('var(--d-ring-width)', $tailwindCss);
        self::assertStringContainsString('var(--d-surface-shadow)', $tailwindCss);
    }

    public function testHousePresetsAreSelectableAndThemed(): void
    {
        $housePresets = ['aurora', 'marine', 'forest', 'ember', 'bloom', 'lagoon', 'gold', 'midnight', 'blossom', 'citrus'];

        // Each house preset is offered in the site-configuration dropdown.
        $definitions = self::parseYamlFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.definitions.yaml');
        $settingDefinitions = $definitions['settings'] ?? null;
        self::assertIsArray($settingDefinitions);
        $presetDefinition = $settingDefinitions['desiderio.shadcn.preset'] ?? null;
        self::assertIsArray($presetDefinition);
        $presetEnum = $presetDefinition['enum'] ?? null;
        self::assertIsArray($presetEnum);
        foreach ($housePresets as $preset) {
            self::assertArrayHasKey($preset, $presetEnum);
        }

        // Each house preset has a light + dark token block keyed on the body attribute.
        $themeCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/shadcn-theme.css');
        foreach ($housePresets as $preset) {
            self::assertStringContainsString('body[data-shadcn-preset="' . $preset . '"]', $themeCss);
            self::assertStringContainsString('.dark body[data-shadcn-preset="' . $preset . '"]', $themeCss);
        }

        // House presets re-theme the accent and radius; the mono preset is also compact.
        // The exact lightness is solved per hue for WCAG 2.2 contrast, so only
        // chroma and hue are pinned here.
        self::assertMatchesRegularExpression('/--primary: oklch\([\d.]+ 0\.2 293\)/', $themeCss);
        self::assertMatchesRegularExpression('/body\[data-shadcn-preset="citrus"\][^}]*--d-control-h: 2rem;/s', $themeCss);

        // House presets also vary density, focus-ring width, and surface elevation.
        self::assertMatchesRegularExpression('/body\[data-shadcn-preset="marine"\][^}]*--d-control-h: 2\.5rem;/s', $themeCss);
        self::assertMatchesRegularExpression('/body\[data-shadcn-preset="marine"\][^}]*--d-ring-width: 2px;/s', $themeCss);
        self::assertMatchesRegularExpression('/body\[data-shadcn-preset="marine"\][^}]*--d-surface-shadow: var\(--shadow-md\);/s', $themeCss);
        self::assertMatchesRegularExpression('/body\[data-shadcn-preset="lagoon"\][^}]*--d-ring-width: 1px;/s', $themeCss);
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
        // Scoped to the Fluid templates on purpose: scanning the whole
        // ContentBlocks tree turned utility-looking words in config.yaml
        // descriptions and XLF keyword catalogs into dead CSS candidates.
        self::assertStringContainsString('@source "../../../ContentBlocks/ContentElements/*/templates";', $tailwindCss);
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

    public function testShadcnFluidSyncSupportsAllCreateStylesAndIconLibraries(): void
    {
        $script = (string) file_get_contents(__DIR__ . '/../../Build/Scripts/sync-shadcn-fluid-primitives.php');

        foreach (['radix-vega', 'radix-nova', 'radix-maia', 'radix-lyra', 'radix-mira', 'radix-luma', 'radix-sera', 'radix-rhea'] as $style) {
            self::assertStringContainsString($style, $script);
        }

        foreach (['lucide', 'tabler', 'hugeicons', 'phosphor', 'remixicon'] as $iconLibrary) {
            self::assertStringContainsString($iconLibrary, $script);
        }

        self::assertStringContainsString('--icon-library=', $script);
        self::assertStringContainsString('Resources/Private/Components/Atom/ControlClass/ControlClass.fluid.html', $script);
        self::assertStringContainsString('Resources/Private/Extensions/Powermail/Partials/Form/ShadcnClass.html', $script);
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

        // The bundle is minified at build time (build-desiderio-css.mjs):
        // whitespace around commas and trailing semicolons are collapsed.
        self::assertStringContainsString('.d-solr-suggest :where(a,a *)', $css);
        self::assertStringContainsString('#tx-solr-facets-in-use :where(a,a *)', $css);
        self::assertStringContainsString('#tx-solr-sorting :where(a,a *,summary,summary *)', $css);
        self::assertStringContainsString('text-decoration-line: none', $css);
    }

    public function testStyleguidePageListsEveryElementOverview(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Pages/DesiderioStyleguide.fluid.html');
        $contentBlockConfigs = glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/config.yaml');
        self::assertIsArray($contentBlockConfigs);
        $contentBlockCount = count($contentBlockConfigs);

        self::assertStringContainsString('All content elements', $template);
        self::assertStringContainsString('docs__overview-card', $template);
        self::assertStringContainsString('d:styleguideFixtureSummary', $template);
        self::assertStringContainsString("{$contentBlockCount} production-ready Desiderio Content Blocks", $template);
        self::assertStringNotContainsString('255 production-ready content elements', $template);
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
