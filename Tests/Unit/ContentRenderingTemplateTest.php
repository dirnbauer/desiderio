<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ContentRenderingTemplateTest extends TestCase
{
    public function testCoreContentTemplatesRequiredByTypoScriptConventionExist(): void
    {
        $templateDirectory = __DIR__ . '/../../Resources/Private/FluidStyledContent/Templates';
        $partialDirectory = __DIR__ . '/../../Resources/Private/FluidStyledContent/Partials';
        $layoutDirectory = __DIR__ . '/../../Resources/Private/FluidStyledContent/Layouts';
        $classicTemplates = [
            'Bullets',
            'Div',
            'Generic',
            'Header',
            'Html',
            'Image',
            'Shortcut',
            'Table',
            'Text',
            'Textmedia',
            'Textpic',
            'Uploads',
        ];
        $coreMenuTemplates = [
            'MenuAbstract',
            'MenuCategorizedContent',
            'MenuCategorizedPages',
            'MenuPages',
            'MenuRecentlyUpdated',
            'MenuRelatedPages',
            'MenuSection',
            'MenuSectionPages',
            'MenuSitemap',
            'MenuSitemapPages',
            'MenuSubpages',
        ];

        self::assertFileExists($layoutDirectory . '/Default.fluid.html');
        foreach (['Header', 'RichText', 'Media', 'Menu', 'FileList'] as $partialName) {
            self::assertFileExists($partialDirectory . '/' . $partialName . '.fluid.html');
        }

        foreach ($classicTemplates as $templateName) {
            self::assertFileExists($templateDirectory . '/' . $templateName . '.fluid.html');
        }

        foreach ($coreMenuTemplates as $templateName) {
            self::assertFileExists($templateDirectory . '/' . $templateName . '.fluid.html');
        }
    }

    public function testContentTypoScriptUsesCTypeBasedTemplateResolution(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');

        self::assertStringContainsString('field = CType', $typoScript);
        self::assertStringContainsString('case = uppercamelcase', $typoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/FluidStyledContent/Templates/', $typoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/FluidStyledContent/Partials/', $typoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/FluidStyledContent/Layouts/', $typoScript);
        self::assertStringContainsString('dataProcessing.1421884800 = record-transformation', $typoScript);
        self::assertStringContainsString('tt_content.default =< lib.contentElement', $typoScript);
        self::assertStringContainsString('tt_content.stdWrap.wrapContentElementsWithVeWrapper = 1', $typoScript);
        self::assertStringContainsString('tt_content.textmedia =< lib.contentElement', $typoScript);
        self::assertStringContainsString('tt_content.uploads =< lib.contentElement', $typoScript);
        self::assertStringContainsString('tt_content.bullets =< lib.desiderioContentWithBullets', $typoScript);
        self::assertStringContainsString('tt_content.table =< lib.desiderioContentWithTable', $typoScript);
        self::assertStringContainsString('lib.desiderioShortcutRecords = RECORDS', $typoScript);
        self::assertStringContainsString('dataProcessing.10 = files', $typoScript);
        self::assertStringContainsString('dataProcessing.10.references.fieldName = assets', $typoScript);
        self::assertStringContainsString('dataProcessing.10.references.fieldName = image', $typoScript);
        self::assertStringContainsString('references.fieldName = media', $typoScript);
        self::assertStringNotContainsString('lib.desiderioContentWithImages', $typoScript);
        self::assertStringNotContainsString('lib.desiderioContentWithFiles', $typoScript);
        self::assertStringContainsString('dataProcessing.20 = split', $typoScript);
        self::assertStringContainsString('dataProcessing.20 = comma-separated-value', $typoScript);
    }

    public function testMenuPagesUsesCoreMenuProcessorAndShadcnStyleClasses(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');
        $template = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/MenuPages.fluid.html');
        $partial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/Menu.fluid.html');
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');

        self::assertStringContainsString('tt_content.menu_pages =< lib.desiderioMenuSelectedPages', $typoScript);
        self::assertStringContainsString('dataProcessing.20 = menu', $typoScript);
        self::assertStringContainsString('special = list', $typoScript);
        self::assertStringContainsString('special.value.field = pages', $typoScript);
        self::assertStringContainsString('arguments="{record: record, items: menu, fallbackPages: record.pages}"', $template);

        foreach (['ce-fsc-menu', 'ce-fsc-menu__grid', 'ce-fsc-menu__link', 'ce-fsc-menu__title'] as $className) {
            self::assertStringContainsString($className, $partial);
            self::assertStringContainsString('.' . $className, $css);
        }

        foreach (['<dc:layout.grid', '<dc:molecule.card', '<dc:molecule.cardContent'] as $componentTag) {
            self::assertStringContainsString($componentTag, $partial);
        }

        foreach (['var(--card)', 'var(--border)', 'var(--ring)', 'var(--muted-foreground)'] as $token) {
            self::assertStringContainsString($token, $css);
        }
    }

    public function testPageChromeProvidesWebsiteNavigationControlsAndFooterMenus(): void
    {
        $pageTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/page.typoscript');
        $header = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/Header.fluid.html');
        $footer = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/Footer.fluid.html');
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/desiderio.js');
        $settings = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');
        $settingDefinitions = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.definitions.yaml');
        $iconRegistry = (string) file_get_contents(__DIR__ . '/../../Classes/Icon/IconRegistry.php');

        self::assertStringContainsString('as = footerMenu', $pageTypoScript);
        self::assertStringContainsString('as = legalMenu', $pageTypoScript);
        self::assertStringContainsString('if.isTrue = {$desiderio.footer.legalPageIds}', $pageTypoScript);
        self::assertStringContainsString('special.value = {$desiderio.footer.legalPageIds}', $pageTypoScript);
        self::assertStringContainsString('includeNotInMenu = 1', $pageTypoScript);

        foreach ([
            'data-d-site-header',
            'data-d-primary-nav',
            'role="search"',
            'languageNavigation',
            '<details',
            'data-d-theme-switch',
            'data-d-theme-summary',
            'data-d-theme-current-icon="system"',
            'data-d-theme-option="light"',
            'data-d-theme-option="dark"',
            'data-d-theme-option="system"',
        ] as $needle) {
            self::assertStringContainsString($needle, $header);
        }

        foreach (['name="search"', 'name="globe"', 'name="menu"', 'name="sun"', 'name="moon"', 'name="monitor"'] as $icon) {
            self::assertStringContainsString($icon, $header);
        }
        foreach (["'search'", "'globe'", "'menu'", "'sun'", "'moon'", "'monitor'"] as $iconKey) {
            self::assertStringContainsString($iconKey, $iconRegistry);
        }

        foreach (['footerMenu', 'legalMenu', 'desiderio-footer__copyright', 'footer.legal.imprint', 'footer.legal.privacy', 'footer.legal.accessibility'] as $needle) {
            self::assertStringContainsString($needle, $footer);
        }

        foreach (['.desiderio-header__search', '.desiderio-theme-switch__summary', '.desiderio-theme-switch__list', '.desiderio-footer__legal-list'] as $className) {
            self::assertStringContainsString($className, $css);
        }

        self::assertStringContainsString("document.querySelectorAll('[data-d-theme-option]')", $javascript);
        self::assertStringContainsString("details[data-d-close-on-outside][open]", $javascript);
        self::assertStringContainsString('footer:', $settings);
        self::assertStringContainsString('search:', $settings);
        self::assertStringContainsString('desiderio.footer.legalPageIds', $settingDefinitions);
        self::assertStringContainsString('desiderio.search.targetPageId', $settingDefinitions);
    }

    public function testProductionWebsiteDefaultsAreConnected(): void
    {
        $layout = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Layouts/Pages/Default.fluid.html');
        $seoMeta = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/SeoMeta.fluid.html');
        $structuredData = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/StructuredData.fluid.html');
        $consent = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/Consent.fluid.html');
        $robotsTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/seo.typoscript');
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/desiderio.js');
        $settings = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');
        $settingDefinitions = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.definitions.yaml');
        $backendLayouts = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/backend_layouts.xlf');
        $structuredDataViewHelper = (string) file_get_contents(__DIR__ . '/../../Classes/ViewHelpers/StructuredDataViewHelper.php');

        foreach (['Pages/SeoMeta', 'Pages/StructuredData', 'Pages/Consent'] as $partialName) {
            self::assertStringContainsString('partial="' . $partialName . '"', $layout);
        }

        foreach (['defaultDescription', 'defaultRobots', 'defaultImage', 'structuredDataEnabled', 'robotsTxtEnabled', 'sitemapPath'] as $settingName) {
            self::assertArrayHasKey($settingName, $settings['desiderio']['seo']);
        }
        self::assertArrayHasKey('enabled', $settings['desiderio']['tracking']);
        self::assertArrayHasKey('message', $settings['desiderio']['consent']);
        self::assertArrayHasKey('privacyPageId', $settings['desiderio']['consent']);

        foreach (['desiderio.seo.defaultDescription', 'desiderio.seo.robotsTxtEnabled', 'desiderio.tracking.enabled', 'desiderio.consent.privacyPageId'] as $definitionKey) {
            self::assertStringContainsString($definitionKey, $settingDefinitions);
        }

        foreach (['property="description"', 'property="og:title"', 'property="og:url"', 'property="twitter:card"', 'property="robots"'] as $needle) {
            self::assertStringContainsString($needle, $seoMeta);
        }

        self::assertStringContainsString('<di:structuredData', $structuredData);
        self::assertStringContainsString('data-desiderio-structured-data', $structuredDataViewHelper);
        self::assertStringContainsString('SearchAction', $structuredDataViewHelper);
        self::assertStringContainsString('JSON_HEX_TAG', $structuredDataViewHelper);

        self::assertStringContainsString('typeNum = 201', $robotsTypoScript);
        self::assertStringContainsString('Sitemap: {site:base}{$desiderio.seo.sitemapPath}', $robotsTypoScript);

        foreach (['data-d-consent', 'data-d-consent-accept', 'data-d-consent-decline'] as $needle) {
            self::assertStringContainsString($needle, $consent);
            self::assertStringContainsString($needle, $javascript);
        }
        self::assertStringContainsString('.desiderio-consent', $css);
        self::assertStringContainsString('.desiderio-system-page', $css);

        foreach (['DesiderioSearch', 'DesiderioError'] as $templateName) {
            self::assertFileExists(__DIR__ . '/../../Configuration/BackendLayouts/' . $templateName . '.tsconfig');
            self::assertFileExists(__DIR__ . '/../../Resources/Private/Templates/Pages/' . $templateName . '.fluid.html');
        }
        self::assertStringContainsString('backend_layout.desiderio_search.title', $backendLayouts);
        self::assertStringContainsString('backend_layout.desiderio_error.title', $backendLayouts);
    }

    public function testFluidStyledContentUsesPresetAwareShadcnSources(): void
    {
        $layout = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Layouts/Default.fluid.html');
        $header = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/Header.fluid.html');
        $tailwind = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Tailwind/desiderio.css');
        $settings = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');

        self::assertStringContainsString('xmlns:dc="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"', $layout);
        self::assertStringContainsString('dc:layout.section', $layout);
        self::assertStringContainsString('dc:atom.typography', $header);
        self::assertStringContainsString('@source "../FluidStyledContent";', $tailwind);
        self::assertStringContainsString('templateRootPath: EXT:desiderio/Resources/Private/FluidStyledContent/Templates/', $settings);
    }

    public function testFluidStyledContentMediaTemplatesUseFilesProcessorFileObjects(): void
    {
        $partial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/Media.fluid.html');
        $textmediaTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Textmedia.fluid.html');
        $textpicTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Textpic.fluid.html');
        $imageTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Image.fluid.html');
        $uploadsTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Uploads.fluid.html');

        self::assertStringContainsString('<f:argument name="files" type="iterable" optional="true"/>', $partial);
        self::assertStringContainsString('<f:argument name="position" type="string" optional="true"/>', $partial);
        self::assertStringContainsString('<f:argument name="maxWidth" type="integer" optional="true" default="1200"/>', $partial);
        self::assertStringContainsString('<f:image image="{file}"', $partial);
        self::assertStringNotContainsString('<img ', $partial);
        self::assertStringNotContainsString('src="{file.', $partial);
        self::assertStringNotContainsString('treatIdAsReference', $partial);
        self::assertStringNotContainsString('name="images"', $partial);
        self::assertStringContainsString('files: files', $textmediaTemplate);
        self::assertStringContainsString('files: files', $textpicTemplate);
        self::assertStringContainsString('files: files', $imageTemplate);
        self::assertStringContainsString('files: files', $uploadsTemplate);
        self::assertStringNotContainsString('files: record.', $textmediaTemplate . $textpicTemplate . $imageTemplate . $uploadsTemplate);
    }

    public function testEditableTextViewHelperIsNotRenderedInsideHtmlAttributes(): void
    {
        $templateRoots = [
            'ContentBlocks',
            'Resources/Private',
        ];
        $invalidAttributes = [];

        foreach ($templateRoots as $templateRoot) {
            $directory = realpath(__DIR__ . '/../../' . $templateRoot);
            self::assertIsString($directory);

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                if (!str_ends_with($path, '.html') && !str_ends_with($path, '.fluid.html')) {
                    continue;
                }

                $template = (string) file_get_contents($path);
                if (preg_match_all('/(?:alt|src|href|title|aria-label)="[^"\n]*\{[^"\n]*->\s*f:render\.text\(/', $template, $matches, PREG_OFFSET_CAPTURE) === false) {
                    continue;
                }

                foreach ($matches[0] as $match) {
                    $line = substr_count(substr($template, 0, $match[1]), "\n") + 1;
                    $invalidAttributes[] = str_replace(__DIR__ . '/../../', '', $path) . ':' . $line;
                }
            }
        }

        self::assertSame([], $invalidAttributes);
    }

    public function testPricingSliderTemplateIsConnectedToSharedRuntime(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/pricing-slider/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/desiderio.js');

        self::assertStringContainsString('data-d-pricing-slider', $template);
        self::assertStringContainsString('data-d-pricing-slider-range', $template);
        self::assertStringContainsString('data-d-pricing-slider-tier', $template);
        self::assertStringContainsString('value="0"', $template);
        self::assertStringContainsString("document.querySelectorAll('[data-d-pricing-slider]')", $javascript);
        self::assertStringContainsString("range.addEventListener('input', activate)", $javascript);
        self::assertStringContainsString('pricing-slider__tier--active', $javascript);
        self::assertStringContainsString("range.setAttribute('aria-valuetext'", $javascript);
    }

    public function testCounterTemplatesAreConnectedToSharedRuntime(): void
    {
        $counterTemplate = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/counter/templates/frontend.html');
        $statsCounterTemplate = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/stats-counter/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/astro.js');

        self::assertStringContainsString('data-astro-counter', $counterTemplate);
        self::assertStringContainsString('data-astro-target="{item.target_value}"', $counterTemplate);
        self::assertStringContainsString('{item.target_value}</span>', $counterTemplate);
        self::assertStringContainsString('data-astro-counter', $statsCounterTemplate);
        self::assertStringContainsString("scope.querySelectorAll('[data-astro-counter], [data-d-counter]')", $javascript);
        self::assertStringContainsString('window.requestAnimationFrame(step)', $javascript);
    }

    public function testCodeBlockTemplateIsConnectedToAstroHighlightRuntime(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/code-block/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/astro.js');
        $css = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/code-block/assets/frontend.css');

        self::assertStringContainsString('data-astro-highlight', $template);
        self::assertStringContainsString('data-astro-language="{data.language}"', $template);
        self::assertStringContainsString('data-astro-copy', $template);
        self::assertStringContainsString('AstroRuntime.prototype.initHighlight', $javascript);
        self::assertStringContainsString('function highlightPhp(source)', $javascript);
        self::assertStringContainsString('.astro-token--keyword', $css);
        self::assertStringContainsString('.astro-token--string', $css);
    }

    public function testGenericChartTemplateIsConnectedToLegendAndAnimationRuntime(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/chart/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/charts.js');

        self::assertStringContainsString('data-chart-type', $template);
        self::assertStringContainsString('data-show-legend', $template);
        self::assertStringContainsString('data-legend-position', $template);
        self::assertStringContainsString('data-show-values', $template);
        self::assertStringContainsString('chart__legend', $template);
        self::assertStringContainsString('renderLegend(root, values)', $javascript);
        self::assertStringContainsString('drawGenericBarChart(svg, values', $javascript);
        self::assertStringContainsString('animateChart(svg)', $javascript);
    }

    public function testTabsTemplateRendersCollectionPanelContentInline(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/tabs/templates/frontend.html');

        self::assertStringContainsString('data-d-tabs-content', $template);
        self::assertStringContainsString('data-value="tab-{data.uid}-{iter.index}"', $template);
        self::assertStringContainsString("{item -> f:render.text(field: 'tab_content')}", $template);
        self::assertStringContainsString('{data.items.0.tab_content}', $template);
        self::assertStringNotContainsString('<d:molecule.tabsContent', $template);
    }

    public function testIntroHeadingSpacingOnlyAppliesWhenMutedTextFollows(): void
    {
        $timelineCss = (string)file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/timeline/assets/frontend.css');
        $textmediaCss = (string)file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/textmedia/assets/frontend.css');

        self::assertStringContainsString(
            '.timeline__intro [data-variant="h2"] + [data-variant="muted"]',
            $timelineCss
        );
        self::assertStringContainsString('margin-block-start: var(--d-spacing-sm);', $timelineCss);
        self::assertStringContainsString(
            '.textmedia__content [data-variant="h2"] + [data-variant="muted"]',
            $textmediaCss
        );
        self::assertStringContainsString('margin-block-start: var(--d-spacing-sm);', $textmediaCss);
    }

    public function testTimelineListUsesContinuousRailAndSeparatedCards(): void
    {
        $timelineCss = (string)file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/timeline/assets/frontend.css');

        self::assertStringContainsString('.timeline__list::before', $timelineCss);
        self::assertStringContainsString('gap: var(--d-spacing-lg);', $timelineCss);
        self::assertStringContainsString('.timeline__line', $timelineCss);
        self::assertStringContainsString('display: none;', $timelineCss);
        self::assertStringContainsString('.timeline__content', $timelineCss);
        self::assertStringContainsString('border-radius: var(--d-radius-lg);', $timelineCss);
        self::assertStringContainsString('padding: var(--d-spacing-lg);', $timelineCss);
    }

    public function testExtensionIntegrationSiteSetsAreBundledWithBaseSet(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $solrSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioSolr/config.yaml');
        $newsSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioNews/config.yaml');
        $blogSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/config.yaml');
        $solrTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioSolr/setup.typoscript');
        $newsTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioNews/setup.typoscript');
        $blogTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/setup.typoscript');

        self::assertIsArray($baseSet);
        self::assertIsArray($solrSet);
        self::assertIsArray($newsSet);
        self::assertIsArray($blogSet);

        $baseOptionalDependencies = $baseSet['optionalDependencies'] ?? [];
        $solrOptionalDependencies = $solrSet['optionalDependencies'] ?? [];
        $newsOptionalDependencies = $newsSet['optionalDependencies'] ?? [];
        $blogOptionalDependencies = $blogSet['optionalDependencies'] ?? [];
        self::assertIsArray($baseOptionalDependencies);
        self::assertIsArray($solrOptionalDependencies);
        self::assertIsArray($newsOptionalDependencies);
        self::assertIsArray($blogOptionalDependencies);

        self::assertContains('webconsulting/desiderio-solr', $baseOptionalDependencies);
        self::assertContains('webconsulting/desiderio-news', $baseOptionalDependencies);
        self::assertContains('webconsulting/desiderio-blog', $baseOptionalDependencies);

        self::assertSame('webconsulting/desiderio-solr', $solrSet['name']);
        self::assertTrue($solrSet['hidden']);
        self::assertArrayNotHasKey('dependencies', $solrSet);
        self::assertContains('apache-solr-for-typo3/solr', $solrOptionalDependencies);
        self::assertStringContainsString('plugin.tx_solr', $solrTypoScript);
        self::assertStringContainsString('templateRootPaths.100 = EXT:desiderio/Resources/Private/Solr/Templates/', $solrTypoScript);
        self::assertStringContainsString('partialRootPaths.100 = EXT:desiderio/Resources/Private/Solr/Partials/', $solrTypoScript);
        self::assertStringContainsString('layoutRootPaths.100 = EXT:desiderio/Resources/Private/Solr/Layouts/', $solrTypoScript);

        self::assertSame('webconsulting/desiderio-news', $newsSet['name']);
        self::assertTrue($newsSet['hidden']);
        self::assertContains('georgringer/news', $newsOptionalDependencies);
        self::assertStringContainsString('plugin.tx_news', $newsTypoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Templates/', $newsTypoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Partials/', $newsTypoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Layouts/', $newsTypoScript);

        self::assertSame('webconsulting/desiderio-blog', $blogSet['name']);
        self::assertTrue($blogSet['hidden']);
        self::assertContains('t3g/blog', $blogOptionalDependencies);
        self::assertStringContainsString('plugin.tx_blog', $blogTypoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Templates/', $blogTypoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Partials/', $blogTypoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Layouts/', $blogTypoScript);
        self::assertStringNotContainsString('lib.dynamicContent', $blogTypoScript);
    }

    public function testDesiderioBlogTemplatesUseShadcnComponentsAndTypedFluidArguments(): void
    {
        $requiredFiles = [
            'Resources/Private/Extensions/Blog/Layouts/Default.html',
            'Resources/Private/Extensions/Blog/Layouts/Post.html',
            'Resources/Private/Extensions/Blog/Layouts/Widget.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogList.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogPost.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Header.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Footer.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Authors.html',
            'Resources/Private/Extensions/Blog/Templates/Post/RelatedPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListByDemand.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListLatestPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListRecentPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByAuthor.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByCategory.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByDate.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByTag.html',
            'Resources/Private/Extensions/Blog/Templates/Comment/Comments.html',
            'Resources/Private/Extensions/Blog/Templates/Comment/Form.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/RecentPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Categories.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Tags.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Archive.html',
            'Resources/Private/Extensions/Blog/Partials/List.html',
            'Resources/Private/Extensions/Blog/Partials/TeaserList.html',
            'Resources/Private/Extensions/Blog/Partials/List/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Teaser/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Pagination/Pagination.html',
            'Resources/Private/Extensions/Blog/Partials/Comments/Comment.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Author.html',
            'Resources/Private/Extensions/Blog/Partials/General/FeaturedImage.html',
            'Resources/Private/Extensions/Blog/Partials/General/SocialIcons.html',
        ];

        foreach ($requiredFiles as $relativePath) {
            self::assertFileExists(__DIR__ . '/../../' . $relativePath, "{$relativePath} must exist");
        }

        $shadcnBackedTemplates = [
            'Resources/Private/Extensions/Blog/Layouts/Post.html',
            'Resources/Private/Extensions/Blog/Layouts/Widget.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogList.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogPost.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Header.html',
            'Resources/Private/Extensions/Blog/Templates/Comment/Comments.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Categories.html',
            'Resources/Private/Extensions/Blog/Partials/List.html',
            'Resources/Private/Extensions/Blog/Partials/List/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Teaser/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Comments/Comment.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Author.html',
        ];
        foreach ($shadcnBackedTemplates as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template, "{$relativePath} should declare the Desiderio component namespace");
            self::assertMatchesRegularExpression('/<d:(atom|molecule|layout)\\./', $template, "{$relativePath} should render with shadcn <d:…> components");
        }

        $typedPartials = [
            'Resources/Private/Extensions/Blog/Partials/List.html',
            'Resources/Private/Extensions/Blog/Partials/TeaserList.html',
            'Resources/Private/Extensions/Blog/Partials/SimpleList.html',
            'Resources/Private/Extensions/Blog/Partials/List/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Teaser/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Pagination/Pagination.html',
            'Resources/Private/Extensions/Blog/Partials/Meta/Rendering/Group.html',
            'Resources/Private/Extensions/Blog/Partials/Meta/Rendering/Item.html',
            'Resources/Private/Extensions/Blog/Partials/Comments/Comment.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Author.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Meta.html',
            'Resources/Private/Extensions/Blog/Partials/General/FeaturedImage.html',
            'Resources/Private/Extensions/Blog/Partials/List/Author.html',
            'Resources/Private/Extensions/Blog/Partials/List/Category.html',
            'Resources/Private/Extensions/Blog/Partials/List/Tag.html',
            'Resources/Private/Extensions/Blog/Partials/List/Archive.html',
        ];
        foreach ($typedPartials as $relativePath) {
            $partial = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertMatchesRegularExpression('/<f:argument\\s+name="[^"]+"\\s+type="[^"]+"/', $partial, "{$relativePath} must declare typed <f:argument> for Fluid 5.3 strict typing");
        }

        $blogPageTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/page.tsconfig');
        self::assertStringContainsString('mod.web_layout.tt_content.preview', $blogPageTsConfig);
    }

    public function testDesiderioBlogPageTemplatesAreVisualEditorReady(): void
    {
        $blogPageTemplates = [
            'Resources/Private/Extensions/Blog/Templates/Page/BlogList.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogPost.html',
        ];

        foreach ($blogPageTemplates as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);

            self::assertStringContainsString(
                '<f:render.contentArea contentArea="{blogContentAreas.content}"',
                $template,
                "{$relativePath} must expose the Blog PAGEVIEW content area for Visual Editor add/move/delete support",
            );
            self::assertStringContainsString(
                'tt_content.{listType}.20',
                $template,
                "{$relativePath} must render blog plugin slots directly through the Extbase plugin path",
            );
            self::assertStringNotContainsString('lib.dynamicContent', $template, "{$relativePath} must not render legacy dynamic content");
            self::assertStringNotContainsString('contentListOptions', $template, "{$relativePath} must not build synthetic tt_content rows");
            self::assertStringNotContainsString('contentObjectData', $template, "{$relativePath} must not pass synthetic tt_content data");
            self::assertStringNotContainsString('table="tt_content"', $template, "{$relativePath} must not trigger record transformation for synthetic tt_content rows");
        }
    }

    public function testContentBlockSiteSetsAreBundledBehindSingleDesiderioSet(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $contentElementsSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioContentElements/config.yaml');
        $userTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/user.tsconfig');
        self::assertIsArray($baseSet);
        self::assertIsArray($contentElementsSet);

        $contentBlockNames = [];
        foreach (glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/config.yaml') ?: [] as $configFile) {
            $contentBlock = Yaml::parseFile($configFile);
            $contentBlockNames[] = (string) $contentBlock['name'];
        }
        sort($contentBlockNames);

        $setDependencies = $contentElementsSet['optionalDependencies'] ?? [];
        sort($setDependencies);

        preg_match_all('/options\\.sites\\.hideSets := addToList\\(([^)]+)\\)/', $userTsConfig, $matches);
        $hiddenSetNames = [];
        foreach ($matches[1] as $setList) {
            $hiddenSetNames = array_merge($hiddenSetNames, explode(',', $setList));
        }
        sort($hiddenSetNames);

        $baseOptionalDependencies = $baseSet['optionalDependencies'] ?? [];
        $contentElementsDependencies = $contentElementsSet['dependencies'] ?? [];
        self::assertIsArray($baseOptionalDependencies);
        self::assertIsArray($contentElementsDependencies);

        self::assertNotContains('webconsulting/desiderio-content-elements', $baseOptionalDependencies);
        self::assertTrue($baseSet['hidden']);
        self::assertSame('webconsulting/desiderio-content-elements', $contentElementsSet['name']);
        self::assertSame('Desiderio Content Elements', $contentElementsSet['label']);
        self::assertTrue($contentElementsSet['hidden']);
        self::assertContains('webconsulting/desiderio', $contentElementsDependencies);
        self::assertSame($contentBlockNames, $setDependencies);
        self::assertSame($contentBlockNames, $hiddenSetNames);
    }

    public function testVisibleDesiderioSiteSetsAreOneClickSitePackages(): void
    {
        $expectedSitePackages = [
            'Corporate' => [
                'directory' => 'DesiderioSitePackageCorporate',
                'name' => 'webconsulting/site-package-desiderio-corporate',
                'preset' => 'webconsulting/desiderio-preset-corporate',
            ],
            'Dashboard' => [
                'directory' => 'DesiderioSitePackageDashboard',
                'name' => 'webconsulting/site-package-desiderio-dashboard',
                'preset' => 'webconsulting/desiderio-preset-dashboard',
            ],
            'Editorial' => [
                'directory' => 'DesiderioSitePackageEditorial',
                'name' => 'webconsulting/site-package-desiderio-editorial',
                'preset' => 'webconsulting/desiderio-preset-editorial',
            ],
            'Portfolio' => [
                'directory' => 'DesiderioSitePackagePortfolio',
                'name' => 'webconsulting/site-package-desiderio-portfolio',
                'preset' => 'webconsulting/desiderio-preset-portfolio',
            ],
            'Saas' => [
                'directory' => 'DesiderioSitePackageSaas',
                'name' => 'webconsulting/site-package-desiderio-saas',
                'preset' => 'webconsulting/desiderio-preset-saas',
            ],
        ];

        $visibleDesiderioSets = [];
        foreach (glob(__DIR__ . '/../../Configuration/Sets/*/config.yaml') ?: [] as $configFile) {
            $set = Yaml::parseFile($configFile);
            self::assertIsArray($set);

            $name = $set['name'] ?? '';
            self::assertIsString($name);
            if (!str_starts_with($name, 'webconsulting/desiderio') && !str_starts_with($name, 'webconsulting/site-package-desiderio')) {
                continue;
            }

            if (($set['hidden'] ?? false) === true) {
                continue;
            }

            $visibleDesiderioSets[] = $name;
        }
        sort($visibleDesiderioSets);

        $expectedVisibleSets = array_column($expectedSitePackages, 'name');
        sort($expectedVisibleSets);
        self::assertSame($expectedVisibleSets, $visibleDesiderioSets);

        foreach ($expectedSitePackages as $variant => $sitePackage) {
            $set = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/' . $sitePackage['directory'] . '/config.yaml');
            self::assertIsArray($set);

            $dependencies = $set['dependencies'] ?? [];
            self::assertIsArray($dependencies);
            self::assertSame($sitePackage['name'], $set['name']);
            self::assertStringStartsWith('Site Package: Desiderio ', (string) $set['label']);
            self::assertContains('webconsulting/desiderio-content-elements', $dependencies, "{$variant} site package must import the full editor catalog");
            self::assertContains($sitePackage['preset'], $dependencies, "{$variant} site package must select exactly one archetype preset");
        }
    }

    public function testShadcnUiPageTemplateSiteSetRegistersBlogAndExtensionTemplates(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $templateSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/config.yaml');
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/setup.typoscript');
        $pageTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/page.tsconfig');
        $backendLayoutLabels = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/backend_layouts.xlf');

        self::assertIsArray($baseSet);
        self::assertIsArray($templateSet);

        $baseOptionalDependencies = $baseSet['optionalDependencies'] ?? [];
        self::assertIsArray($baseOptionalDependencies);

        self::assertContains('webconsulting/desiderio-shadcnui-templates', $baseOptionalDependencies);
        self::assertSame('webconsulting/desiderio-shadcnui-templates', $templateSet['name']);
        self::assertTrue($templateSet['hidden']);
        self::assertStringContainsString('paths.20 = EXT:desiderio/Resources/Private/ShadcnUi/Templates/', $typoScript);
        self::assertStringContainsString("EXT:desiderio/Configuration/BackendLayouts/ShadcnUi/*.tsconfig", $pageTsConfig);
        self::assertStringContainsString('backend_layout.desiderio_blog.title', $backendLayoutLabels);
        self::assertStringContainsString('backend_layout.desiderio_extension.title', $backendLayoutLabels);
        self::assertStringContainsString('backend_layout.desiderio_news.title', $backendLayoutLabels);

        $requiredFiles = [
            'Configuration/BackendLayouts/ShadcnUi/DesiderioBlog.tsconfig',
            'Configuration/BackendLayouts/ShadcnUi/DesiderioExtension.tsconfig',
            'Configuration/BackendLayouts/ShadcnUi/DesiderioNews.tsconfig',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioBlog.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioExtension.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioNews.fluid.html',
        ];

        foreach ($requiredFiles as $relativePath) {
            self::assertFileExists(__DIR__ . '/../../' . $relativePath, "{$relativePath} must exist");
        }

        foreach ([
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioBlog.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioExtension.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioNews.fluid.html',
        ] as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);

            self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template);
            self::assertStringContainsString('<d:layout.section', $template);
            self::assertStringContainsString('<d:layout.container', $template);
            self::assertStringContainsString('<d:layout.stack', $template);
            self::assertStringContainsString('contentArea="{content.stage}"', $template);
            self::assertStringContainsString('contentArea="{content.main}"', $template);
            self::assertStringContainsString('contentArea="{content.sidebar}"', $template);
        }
    }

    public function testScenarioPresetSiteSetsOverridePageviewTemplates(): void
    {
        $presets = [
            'Corporate' => 'DesiderioPresetCorporate',
            'Dashboard' => 'DesiderioPresetDashboard',
            'Editorial' => 'DesiderioPresetEditorial',
            'Portfolio' => 'DesiderioPresetPortfolio',
            'Saas' => 'DesiderioPresetSaas',
        ];
        $sharedPartials = [
            'ContentArea' => 'contentArea="{content}"',
            'Stage' => 'contentArea="{content}"',
            'DashboardRail' => 'desiderio-dashboard-template__rail',
            'ErrorHomeLink' => 'a11y.nav.home',
            'SystemHeader' => '<f:argument name="summaryTag"',
        ];

        foreach ($sharedPartials as $partialName => $expectedMarkup) {
            $partialPath = 'Resources/Private/Templates/Partials/Presets/' . $partialName . '.fluid.html';
            $absolutePartialPath = __DIR__ . '/../../' . $partialPath;

            self::assertFileExists($absolutePartialPath, "{$partialPath} must exist for reusable preset chrome");
            self::assertStringContainsString($expectedMarkup, (string) file_get_contents($absolutePartialPath));
        }

        foreach ($presets as $presetDirectory => $setDirectory) {
            $presetSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/' . $setDirectory . '/config.yaml');
            $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/' . $setDirectory . '/setup.typoscript');
            $cssPath = __DIR__ . '/../../Resources/Public/Css/preset-' . strtolower($presetDirectory) . '.css';
            if ($presetDirectory === 'Saas') {
                $cssPath = __DIR__ . '/../../Resources/Public/Css/preset-saas.css';
            }
            $templateNames = [
                'DesiderioStartpage',
                'DesiderioContentpage',
                'DesiderioContentpageSidebar',
                'DesiderioSearch',
                'DesiderioError',
            ];

            self::assertStringContainsString(
                'lib.fluidPage.paths.30 = EXT:desiderio/Resources/Private/Presets/' . $presetDirectory . '/Templates/',
                $typoScript,
                $setDirectory . ' must register a PAGEVIEW override path',
            );
            self::assertIsArray($presetSet);
            self::assertTrue($presetSet['hidden']);
            self::assertFileExists($cssPath);

            foreach ($templateNames as $templateName) {
                $templatePath = 'Resources/Private/Presets/' . $presetDirectory . '/Templates/Pages/' . $templateName . '.fluid.html';
                $absoluteTemplatePath = __DIR__ . '/../../' . $templatePath;

                self::assertFileExists($absoluteTemplatePath, "{$templatePath} must exist so {$setDirectory} is a complete page archetype");

                $template = (string) file_get_contents($absoluteTemplatePath);
                self::assertStringContainsString('Pages/Default', $template);
                self::assertStringContainsString('partial="Presets/ContentArea"', $template);

                if (in_array($templateName, ['DesiderioStartpage', 'DesiderioContentpage', 'DesiderioContentpageSidebar'], true)) {
                    self::assertStringContainsString('partial="Presets/Stage"', $template);
                }
            }

            $sidebarTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Presets/' . $presetDirectory . '/Templates/Pages/DesiderioContentpageSidebar.fluid.html');
            self::assertStringContainsString('content: content.sidebar', $sidebarTemplate);
        }
    }

    public function testSolrAndNewsOverrideTemplatesFollowUpstreamStructureAndUseDesiderioComponents(): void
    {
        $requiredFiles = [
            'Resources/Private/Solr/Layouts/Split.html',
            'Resources/Private/Solr/Templates/Search/Results.html',
            'Resources/Private/Solr/Partials/Search/Form.html',
            'Resources/Private/Solr/Partials/Result/Document.html',
            'Resources/Private/Solr/Partials/Facets/Options.html',
            'Resources/Private/Extensions/News/Layouts/General.html',
            'Resources/Private/Extensions/News/Layouts/Detail.html',
            'Resources/Private/Extensions/News/Templates/News/List.html',
            'Resources/Private/Extensions/News/Templates/News/Detail.html',
            'Resources/Private/Extensions/News/Templates/News/MagazineList.html',
            'Resources/Private/Extensions/News/Partials/List/Item.html',
            'Resources/Private/Extensions/News/Partials/List/Pagination.html',
            'Resources/Private/Extensions/News/Partials/List/LoadMore.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaContainer.html',
            'Resources/Private/Extensions/News/Partials/Detail/Opengraph.html',
            'Resources/Private/Extensions/News/Partials/Detail/Shariff.html',
        ];

        foreach ($requiredFiles as $relativePath) {
            $path = __DIR__ . '/../../' . $relativePath;
            self::assertFileExists($path, "{$relativePath} must exist");
        }

        foreach ([
            'Resources/Private/Extensions/News/Templates/News/List.html',
            'Resources/Private/Extensions/News/Templates/News/Detail.html',
            'Resources/Private/Extensions/News/Partials/List/Item.html',
        ] as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template, "{$relativePath} should use Desiderio Fluid components");
        }

        $solrDocumentPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Partials/Result/Document.html');
        $solrLayout = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Layouts/Split.html');
        $solrResultsTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Templates/Search/Results.html');
        $solrFormPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Solr/Partials/Search/Form.html');

        self::assertStringContainsString('data-shadcn-pattern="create-shell"', $solrLayout);
        self::assertStringContainsString('d-solr-create__sidebar', $solrLayout);
        self::assertStringContainsString('data-shadcn-pattern="search-results"', $solrResultsTemplate);
        self::assertStringContainsString('data-shadcn-pattern="command-palette"', $solrFormPartial);
        self::assertStringContainsString('data-slot="input"', $solrFormPartial);
        self::assertStringContainsString('data-slot="button"', $solrFormPartial);
        self::assertStringContainsString('<di:icon name="search"', $solrFormPartial);
        self::assertStringContainsString('Webconsulting/Desiderio/ViewHelpers', $solrDocumentPartial);
        self::assertStringContainsString('<d:searchSnippet text="{document.title}"', $solrDocumentPartial);
        self::assertStringContainsString('<d:searchSnippet text="{document.content}"', $solrDocumentPartial);
        self::assertStringContainsString('maxCharacters="200"', $solrDocumentPartial);
        self::assertStringContainsString('data-slot="card"', $solrDocumentPartial);
        self::assertStringContainsString('d-solr-result-card', $solrDocumentPartial);
    }

    public function testEveryLabelFileIsXliff20(): void
    {
        $directories = [
            __DIR__ . '/../../Resources/Private/Language',
            __DIR__ . '/../../ContentBlocks/ContentElements',
        ];

        $files = [];
        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'xlf') {
                    $files[] = $file->getPathname();
                }
            }
        }

        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            $relative = str_replace(dirname(__DIR__, 2) . '/', '', $file);
            self::assertStringContainsString('urn:oasis:names:tc:xliff:document:2.0', $contents, "{$relative} must be XLIFF 2.0");
            self::assertStringContainsString('<unit ', $contents, "{$relative} must use XLIFF 2.0 <unit> elements");
            self::assertStringNotContainsString('<trans-unit ', $contents, "{$relative} must not use legacy <trans-unit> elements");
        }
    }

    public function testNewsLocallangUsesIcuMessageFormatForPlurals(): void
    {
        $english = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/locallang.xlf');
        $german = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/de.locallang.xlf');

        self::assertStringContainsString('plural,', $english, 'locallang.xlf must use ICU MessageFormat plural rules');
        self::assertStringContainsString('plural,', $german, 'de.locallang.xlf must use ICU MessageFormat plural rules');

        foreach (['news.loadMore.status', 'news.magazine.items', 'news.comments.count', 'news.tags.count', 'news.categories.count'] as $unitId) {
            self::assertStringContainsString('<unit id="' . $unitId . '">', $english, "{$unitId} must exist in locallang.xlf");
            self::assertStringContainsString('<unit id="' . $unitId . '">', $german, "{$unitId} must exist in de.locallang.xlf");
        }
    }

    public function testNewsAndSolrAndFluidStyledContentPartialsDeclareTypedFluidArguments(): void
    {
        $partials = [
            'Resources/Private/Extensions/News/Partials/List/Item.html',
            'Resources/Private/Extensions/News/Partials/List/Pagination.html',
            'Resources/Private/Extensions/News/Partials/List/LoadMore.html',
            'Resources/Private/Extensions/News/Partials/Category/Items.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaContainer.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaImage.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaVideo.html',
            'Resources/Private/Extensions/News/Partials/Detail/Opengraph.html',
            'Resources/Private/Extensions/News/Partials/Detail/Shariff.html',
            'Resources/Private/Solr/Partials/Search/Form.html',
            'Resources/Private/Solr/Partials/Search/FrequentlySearched.html',
            'Resources/Private/Solr/Partials/Search/LastSearches.html',
            'Resources/Private/Solr/Partials/Result/Document.html',
            'Resources/Private/Solr/Partials/Result/Pagination.html',
            'Resources/Private/Solr/Partials/Result/Facets.html',
            'Resources/Private/Solr/Partials/Result/FacetsActive.html',
            'Resources/Private/Solr/Partials/Result/Sorting.html',
            'Resources/Private/Solr/Partials/Result/PerPage.html',
            'Resources/Private/Solr/Partials/Facets/Options.html',
            'Resources/Private/FluidStyledContent/Partials/Header.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/RichText.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/Media.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/Menu.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/FileList.fluid.html',
            'Resources/Private/Partials/List/Pagination.html',
            'Resources/Private/Partials/Pagination/Pagination.html',
            'Resources/Private/Partials/Pagination.html',
        ];

        foreach ($partials as $relativePath) {
            $partial = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertMatchesRegularExpression(
                '/<f:argument\\s+name="[^"]+"\\s+type="[^"]+"/',
                $partial,
                "{$relativePath} must declare typed <f:argument> for Fluid 5.3 strict typing"
            );
        }
    }

    public function testPageLayoutShipsAccessibilityPrimitives(): void
    {
        $defaultLayout = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Layouts/Pages/Default.fluid.html');
        $headerPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/Header.fluid.html');
        $componentsCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/components.css');
        $english = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/locallang.xlf');
        $german = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/de.locallang.xlf');

        // Page layout must expose a skip link, a #main-content target, and a focusable <main>.
        self::assertStringContainsString('class="d-skip-link"', $defaultLayout);
        self::assertStringContainsString('href="#main-content"', $defaultLayout);
        self::assertStringContainsString('id="main-content"', $defaultLayout);
        self::assertStringContainsString('tabindex="-1"', $defaultLayout);
        self::assertStringContainsString('a11y.skipToContent', $defaultLayout);

        // Header must announce active nav links + interactive controls.
        self::assertStringContainsString('aria-controls="desiderio-main-nav"', $headerPartial);
        self::assertStringContainsString("aria-current: \\'page\\'", $headerPartial, 'Active nav links must declare aria-current="page" via additionalAttributes.');
        self::assertStringContainsString('aria-pressed="false"', $headerPartial);
        self::assertStringContainsString('<d:atom.icon', $headerPartial);
        self::assertStringContainsString('a11y.menu.toggle', $headerPartial);
        self::assertStringContainsString('a11y.theme.switch', $headerPartial);
        self::assertStringContainsString('a11y.nav.language', $headerPartial);
        self::assertMatchesRegularExpression(
            '/<ul[^>]*role="list"/',
            $headerPartial,
            'Main nav <ul> must carry role="list" for VoiceOver/Safari list semantics.'
        );

        // Accessibility CSS primitives must be in components.css.
        self::assertStringContainsString('.d-skip-link', $componentsCss);
        self::assertStringContainsString('.sr-only', $componentsCss);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $componentsCss);

        // a11y.* labels must exist in both locallang files.
        $a11yLabels = [
            'a11y.skipToContent',
            'a11y.nav.main',
            'a11y.nav.footer',
            'a11y.nav.language',
            'a11y.menu.toggle',
            'a11y.theme.toggle',
        ];
        foreach ($a11yLabels as $unitId) {
            self::assertStringContainsString('<unit id="' . $unitId . '">', $english, "{$unitId} must exist in locallang.xlf");
            self::assertStringContainsString('<unit id="' . $unitId . '">', $german, "{$unitId} must exist in de.locallang.xlf");
        }
    }

    public function testStrippedListSemanticsAreRestoredAcrossOverrides(): void
    {
        $files = [
            'Resources/Private/Extensions/News/Partials/List/Pagination.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/RecentPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Categories.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Tags.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Archive.html',
            'Resources/Private/Solr/Partials/Result/Pagination.html',
            'Resources/Private/Solr/Partials/Search/FrequentlySearched.html',
            'Resources/Private/Solr/Partials/Search/LastSearches.html',
        ];
        foreach ($files as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertMatchesRegularExpression(
                '/<ul\\b[^>]*\\brole="list"/',
                $template,
                "{$relativePath} must add role=\"list\" to <ul> elements that strip native list semantics via Tailwind."
            );
        }
    }
}
