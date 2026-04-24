<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ContentRenderingTemplateTest extends TestCase
{
    public function testCoreContentTemplatesRequiredByTypoScriptConventionExist(): void
    {
        $templateDirectory = __DIR__ . '/../../Resources/Private/Templates/Content';

        self::assertFileExists($templateDirectory . '/Text.fluid.html');
        self::assertFileExists($templateDirectory . '/Textpic.fluid.html');
        self::assertFileExists(__DIR__ . '/../../Resources/Private/Templates/Partials/Content/Textpic.fluid.html');
    }

    public function testContentTypoScriptUsesCTypeBasedTemplateResolution(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');

        self::assertStringContainsString('field = CType', $typoScript);
        self::assertStringContainsString('case = uppercamelcase', $typoScript);
        self::assertStringContainsString('dataProcessing.1421884800 = record-transformation', $typoScript);
        self::assertStringContainsString('tt_content.default =< lib.contentElement', $typoScript);
    }
}
