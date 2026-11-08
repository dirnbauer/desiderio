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

    /**
     * An editor may name an element (tt_content.tx_desiderio_anchor, for
     * example "pricing"), so links can say #pricing instead of #c345. The
     * name follows the c<uid> anchor, escaped, and only when it is set.
     */
    public function testANamedAnchorFollowsTheCoreAnchor(): void
    {
        $typoScript = (string)file_get_contents(self::ROOT . '/Configuration/Sets/Desiderio/TypoScript/content.typoscript');

        self::assertMatchesRegularExpression(
            '/lib\.contentBlock\.stdWrap\.prepend\.append = TEXT\s+lib\.contentBlock\.stdWrap\.prepend\.append \{\s+field = tx_desiderio_anchor\s+htmlSpecialChars = 1\s+wrap = <a id="\|"><\/a>\s+if\.isTrue\.field = tx_desiderio_anchor\s+\}/',
            $typoScript
        );
    }

    /**
     * The name is the same in every language (l10n_mode exclude), so one link
     * with the anchor works on each translation; the form accepts only
     * characters an id and a URL fragment take as they are.
     */
    public function testTheAnchorFieldIsSharedByEveryLanguage(): void
    {
        $tca = (string)file_get_contents(self::ROOT . '/Configuration/TCA/Overrides/tt_content.php');

        self::assertMatchesRegularExpression(
            "/\\\$GLOBALS\['TCA'\]\['tt_content'\]\['columns'\]\['tx_desiderio_anchor'\] = \[\s+'exclude' => true,\s+'l10n_mode' => 'exclude',/",
            $tca
        );
        self::assertStringContainsString("'eval' => 'trim,lower,alphanum_x',", $tca);
        self::assertStringContainsString("'max' => 64,", $tca);
        self::assertMatchesRegularExpression("/addFieldsToPalette\(\s+'tt_content',\s+'desiderio_frames_palette',\s+'tx_desiderio_anchor',/", $tca);
        // The palette exists: it is the one the Appearance basic declares.
        self::assertStringContainsString('identifier: desiderio_frames_palette', (string)file_get_contents(self::ROOT . '/ContentBlocks/Basics/Appearance.yaml'));
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
