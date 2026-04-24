<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ContentRenderingTemplateTest extends TestCase
{
    public function testCoreContentTemplatesRequiredByTypoScriptConventionExist(): void
    {
        $templateDirectory = __DIR__ . '/../../Resources/Private/Templates/Content';
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

        self::assertFileExists($templateDirectory . '/Text.fluid.html');
        self::assertFileExists($templateDirectory . '/Textpic.fluid.html');
        self::assertFileExists(__DIR__ . '/../../Resources/Private/Templates/Partials/Content/Textpic.fluid.html');
        self::assertFileExists(__DIR__ . '/../../Resources/Private/Templates/Partials/Content/Menu.fluid.html');

        foreach ($coreMenuTemplates as $templateName) {
            self::assertFileExists($templateDirectory . '/' . $templateName . '.fluid.html');
        }
    }

    public function testContentTypoScriptUsesCTypeBasedTemplateResolution(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');

        self::assertStringContainsString('field = CType', $typoScript);
        self::assertStringContainsString('case = uppercamelcase', $typoScript);
        self::assertStringContainsString('dataProcessing.1421884800 = record-transformation', $typoScript);
        self::assertStringContainsString('tt_content.default =< lib.contentElement', $typoScript);
    }

    public function testMenuPagesUsesCoreMenuProcessorAndShadcnStyleClasses(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');
        $template = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Content/MenuPages.fluid.html');
        $partial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Content/Menu.fluid.html');
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');

        self::assertStringContainsString('tt_content.menu_pages =< lib.contentElement', $typoScript);
        self::assertStringContainsString('dataProcessing.10 = menu', $typoScript);
        self::assertStringContainsString('special = list', $typoScript);
        self::assertStringContainsString('special.value.field = pages', $typoScript);
        self::assertStringContainsString('arguments="{record: record, items: menu, fallbackPages: record.pages}"', $template);

        foreach (['ce-menu', 'ce-menu__list', 'ce-menu__link', 'ce-menu__title'] as $className) {
            self::assertStringContainsString($className, $partial);
            self::assertStringContainsString('.' . $className, $css);
        }

        foreach (['var(--card)', 'var(--border)', 'var(--ring)', 'var(--muted-foreground)'] as $token) {
            self::assertStringContainsString($token, $css);
        }
    }
}
