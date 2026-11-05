<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseHubPages;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricing;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * The home page sells Desiderio; the lab, the other sites and the usability
 * talk live on the Lab and resources page (open-source round, 2026-11-05).
 * Fifteen sections in seventeen elements: an inline CTA follows two of them,
 * the four-ways cards (which carry no links) and the strategy checklist.
 */
final class StyleguideHomeContentTest extends TestCase
{
    private const array HEADERS = [
        'Ship TYPO3 client sites in days, not weeks',
        'Desiderio in 90 seconds',
        'Six problems Desiderio solves',
        'Change the theme in three steps',
        'Free software, paid care',
        'How we earn with free software',
        '244 content elements in 10 groups',
        'Eight forms, ready to use',
        'Five elements to start with',
        'Built for three kinds of teams',
        'Free to use. Paid plans add support.',
        'Five points for all our software',
        'Not only TYPO3',
        'Everything in this lab',
        'Set up a themed site in four steps',
        'How teams could use Desiderio',
        'Build your next TYPO3 site with Desiderio',
    ];

    public function testTheHomePageHasFifteenSectionsInSeventeenElements(): void
    {
        $content = StyleguideShowcasePages::homeContent();

        self::assertSame(self::HEADERS, array_map(static fn(array $block): mixed => $block['fields']['header'] ?? null, $content));
        self::assertSame('desiderio_hero', $content[0]['ctype']);
        $inline = array_keys(array_column($content, 'ctype'), 'desiderio_ctainline', true);
        self::assertSame(['Free software, paid care', 'Five points for all our software'], array_map(static fn(int $index): mixed => $content[$index - 1]['fields']['header'] ?? null, $inline));
        self::assertSame(15, count($content) - count($inline), 'Fifteen sections.');
    }

    public function testThereIsOneFilmOnTheHomePage(): void
    {
        $ctypes = array_column(StyleguideShowcasePages::homeContent(), 'ctype');

        self::assertCount(1, array_keys($ctypes, 'desiderio_featurevideo', true));
    }

    public function testTheLabAndTheTalkLeftTheHomePage(): void
    {
        $copy = json_encode(StyleguideShowcasePages::homeContent(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        foreach (['Free software, paid care', 'Five points for all our software', 'Everything in this lab'] as $header) {
            self::assertStringContainsString($header, $copy);
        }
        foreach (['More sites on this TYPO3 installation', 'Ten usability slogans', 'Built from 62 typed components', 'Fast to run, edit and launch', 'Five reasons to build on Desiderio', 'Templates for the extensions you use'] as $header) {
            self::assertStringNotContainsString($header, $copy);
        }
    }

    public function testTheFourWaysComeFromThePriceSource(): void
    {
        $block = self::blockWithHeader(StyleguideShowcasePages::homeContent(), 'Free software, paid care');
        $ways = ShowcasePricing::ways();

        self::assertSame('desiderio_featurecards', $block['ctype']);
        self::assertSame(array_column($ways, 'title'), array_column($block['fields']['items'], 'title'));
        self::assertSame(array_column($ways, 'description'), array_column($block['fields']['items'], 'description'));

        $cta = self::blockWithHeader(StyleguideShowcasePages::homeContent(), 'How we earn with free software');
        self::assertSame('{{page:pricing/how-we-earn}}', $cta['fields']['cta_link'] ?? null);
    }

    public function testThePricesOnTheHomePageComeFromThePriceSource(): void
    {
        $teams = self::blockWithHeader(StyleguideShowcasePages::homeContent(), 'Built for three kinds of teams');
        $descriptions = implode(' ', array_column($teams['fields']['items'], 'description'));
        $prices = array_column(ShowcasePricing::plans(), 'price', 'key');
        self::assertStringContainsString($prices['agency'], $descriptions);
        self::assertStringContainsString($prices['studio'], $descriptions);

        $pricing = self::blockWithHeader(StyleguideShowcasePages::homeContent(), 'Free to use. Paid plans add support.');
        self::assertStringContainsString(ShowcasePricing::care()['price'], $pricing['fields']['subheadline']);
    }

    public function testTheStrategyCtaLeadsToTheStrategyPage(): void
    {
        $cta = self::blockWithHeader(StyleguideShowcasePages::homeContent(), 'Not only TYPO3');

        self::assertSame('desiderio_ctainline', $cta['ctype']);
        self::assertSame('Watch the film', $cta['fields']['cta_text'] ?? null);
        self::assertSame('{{page:typo3-v14-strategy}}', $cta['fields']['cta_link'] ?? null);
    }

    public function testTheHomePageAndTheLabPageShowTheSameLabCards(): void
    {
        $home = self::blockWithHeader(StyleguideShowcasePages::homeContent(), 'Everything in this lab');
        $lab = self::blockWithHeader(ShowcaseHubPages::resourcesPage()['content'], 'Everything in this lab');

        self::assertSame($home['fields']['items'], $lab['fields']['items']);
    }

    public function testTheLabPageCarriesTheLabTheOtherSitesAndTheTalk(): void
    {
        $page = ShowcaseHubPages::resourcesPage();

        self::assertSame('Lab and resources', $page['title']);
        self::assertSame('Lab and resources', $page['navTitle']);
        self::assertSame('/resources', $page['slug']);
        self::assertSame('desiderio_hero', $page['content'][0]['ctype'], 'The films seeder places the lab film after the hero.');
        self::assertSame(
            ['Everything in this lab', 'More sites on this TYPO3 installation', 'Where to go next', 'Questions before you start?'],
            array_map(static fn(array $block): mixed => $block['fields']['header'] ?? null, array_slice($page['content'], 1)),
        );
        $next = self::blockWithHeader($page['content'], 'Where to go next');
        self::assertContains('{{page:usability-slogans}}', array_column($next['fields']['items'], 'link'));
        self::assertSame('3', $next['fields']['columns'] ?? null);
        self::assertSame(0, count($next['fields']['items']) % 3, 'Three columns, no orphan row.');
    }

    /**
     * @param array<int, array{ctype: string, colPos: int, fields: array<string, mixed>}> $blocks
     * @return array{ctype: string, colPos: int, fields: array<string, mixed>}
     */
    private static function blockWithHeader(array $blocks, string $header): array
    {
        foreach ($blocks as $block) {
            if (($block['fields']['header'] ?? null) === $header) {
                return $block;
            }
        }

        self::fail(sprintf('No block with the header "%s".', $header));
    }
}
