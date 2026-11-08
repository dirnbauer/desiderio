<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * The TYPO3 trademark notice (desiderio.footer.trademarkNotice): off unless a
 * site turns it on, words only, and the same statement in every language.
 * The TYPO3 Association owns the word mark; the logo is not registered, so
 * the notice never claims it and never shows it.
 */
final class FooterTrademarkNoticeTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../..';
    private const string SOURCE = 'TYPO3 is a registered trademark of the TYPO3 Association. This site is independent and not affiliated with or endorsed by the TYPO3 Association or TYPO3 GmbH.';
    private const string GERMAN = 'TYPO3 ist eine eingetragene Marke der TYPO3 Association. Diese Website ist unabhängig und weder mit der TYPO3 Association noch mit der TYPO3 GmbH verbunden oder von ihnen unterstützt.';

    public function testTheSettingIsOffUntilASiteTurnsItOn(): void
    {
        $definitions = Yaml::parseFile(self::ROOT . '/Configuration/Sets/Desiderio/settings.definitions.yaml');
        self::assertIsArray($definitions);
        $setting = $definitions['settings']['desiderio.footer.trademarkNotice'] ?? null;

        self::assertIsArray($setting);
        self::assertSame('bool', $setting['type'] ?? null);
        self::assertFalse($setting['default'] ?? null);
        self::assertSame('desiderio.footer', $setting['category'] ?? null);
    }

    public function testTheFooterRendersOneLineOfWordsBehindTheSetting(): void
    {
        $template = (string)file_get_contents(self::ROOT . '/Resources/Private/Components/Organism/SiteFooter/SiteFooter.fluid.html');

        self::assertMatchesRegularExpression(
            '#<f:if condition="\{site\.configuration\.settings\.desiderio\.footer\.trademarkNotice\}">\s*<p class="desiderio-footer__trademark"><small>\{f:translate\(extensionName: \'desiderio\', key: \'footer\.trademarkNotice\'\)\}</small></p>\s*</f:if>#',
            $template
        );
        // Inside the footer element, after the legal navigation.
        $footer = (int)strpos($template, '<footer');
        $notice = (int)strpos($template, 'desiderio-footer__trademark');
        self::assertGreaterThan(strpos($template, '</nav>'), $notice);
        self::assertLessThan(strpos($template, '</footer>'), $notice);
        self::assertGreaterThan($footer, $notice);
        self::assertStringContainsString('desiderio-footer__trademark{', (string)file_get_contents(self::ROOT . '/Resources/Public/Css/desiderio.css'), 'Run node Build/Scripts/build-desiderio-css.mjs.');
    }

    public function testEveryLanguageHasTheLabel(): void
    {
        self::assertSame(self::SOURCE, $this->segment('locallang.xlf')['source']);

        $german = $this->segment('de.locallang.xlf');
        self::assertSame(self::SOURCE, $german['source']);
        self::assertSame(self::GERMAN, $german['target']);
        self::assertSame('final', $german['state']);

        // Chinese and Hungarian keep the English text until they are
        // translated: "initial" is XLIFF 2.0's "needs translation", and TYPO3
        // falls back to the English source for it.
        foreach (['zh', 'hu'] as $language) {
            $segment = $this->segment($language . '.locallang.xlf');
            self::assertSame(self::SOURCE, $segment['source'], $language);
            self::assertContains($segment['state'], ['initial', 'translated', 'reviewed', 'final'], $language);
            self::assertNotSame('', $segment['target'], $language);
        }
    }

    public function testTheNoticeClaimsTheWordMarkOnly(): void
    {
        self::assertStringNotContainsString('logo', strtolower(self::SOURCE));
        self::assertStringNotContainsStringIgnoringCase('Logo', self::GERMAN);
    }

    /**
     * @return array{source: string, target: string, state: string}
     */
    private function segment(string $file): array
    {
        $xml = simplexml_load_file(self::ROOT . '/Resources/Private/Language/' . $file);
        self::assertNotFalse($xml, $file);
        $xml->registerXPathNamespace('x', 'urn:oasis:names:tc:xliff:document:2.0');
        $segments = $xml->xpath('//x:unit[@id="footer.trademarkNotice"]/x:segment');
        self::assertIsArray($segments, $file);
        self::assertCount(1, $segments, $file);
        $segment = $segments[0];
        $segment->registerXPathNamespace('x', 'urn:oasis:names:tc:xliff:document:2.0');
        $source = $segment->xpath('x:source');
        $target = $segment->xpath('x:target');

        return [
            'source' => is_array($source) && isset($source[0]) ? (string)$source[0] : '',
            'target' => is_array($target) && isset($target[0]) ? (string)$target[0] : '',
            'state' => (string)($segment['state'] ?? ''),
        ];
    }
}
