<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

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
        self::assertStringContainsString('tt_content.textmedia =< lib.desiderioContentWithImages', $typoScript);
        self::assertStringContainsString('tt_content.uploads =< lib.desiderioContentWithFiles', $typoScript);
        self::assertStringContainsString('tt_content.bullets =< lib.desiderioContentWithBullets', $typoScript);
        self::assertStringContainsString('tt_content.table =< lib.desiderioContentWithTable', $typoScript);
        self::assertStringContainsString('lib.desiderioShortcutRecords = RECORDS', $typoScript);
        self::assertStringContainsString('dataProcessing.20 = files', $typoScript);
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
}
