<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Links to a content element (t3://page?uid=12#c345) need an element with
 * id="c345". Content Block types get it from lib.contentBlock; a template
 * that set the same id itself would put it on the page twice.
 */
final class ContentElementAnchorTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../..';

    public function testContentBlocksGetTheCoreAnchorBeforeEachElement(): void
    {
        $typoScript = (string)file_get_contents(self::ROOT . '/Configuration/Sets/Desiderio/TypoScript/content.typoscript');

        self::assertStringContainsString('lib.contentBlock.stdWrap.prepend = TEXT', $typoScript);
        self::assertStringContainsString('lib.contentBlock.stdWrap.prepend.dataWrap = <a id="c{field:uid}"></a>', $typoScript);
    }

    public function testNoElementTemplateRendersTheAnchorIdItself(): void
    {
        $found = glob(self::ROOT . '/ContentBlocks/ContentElements/*/templates/frontend.html');
        $templates = $found === false ? [] : $found;
        self::assertNotEmpty($templates);

        $offenders = [];
        foreach ($templates as $template) {
            $html = (string)file_get_contents($template);
            if (preg_match('/id="c\{data\.uid\}"|id: \'c\{data\.uid\}\'/', $html) === 1) {
                $offenders[] = basename(dirname($template, 2));
            }
        }

        self::assertSame([], $offenders, 'These templates render id="c{uid}", which lib.contentBlock already prepends.');
    }
}
