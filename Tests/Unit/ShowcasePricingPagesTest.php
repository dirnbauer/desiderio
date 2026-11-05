<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricing;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricingPages;

/**
 * The pricing page, the model page ("How we earn") and the order page: the
 * model page keeps its outline, every link goes to a page of the site or a
 * full URL, Care is one block shown once on each sales page, and no price is
 * typed outside ShowcasePricing.
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcasePricingPagesTest extends TestCase
{
    /**
     * The model page from the hero to the closing banner. The films seeder
     * adds the model film after the hero; it is not part of the page data.
     */
    private const array MODEL_OUTLINE = [
        'desiderio_hero',
        'desiderio_benefitcards',
        'desiderio_textmedia',
        'desiderio_compare',
        'desiderio_stats',
        'desiderio_featuretimeline',
        'desiderio_pricingsimple',
        'desiderio_benefitcards',
        'desiderio_trustbadges',
        'desiderio_faq',
        'desiderio_ctabanner',
    ];

    public function testThePricingPageHasTheModelPageAndTheOrderPageBelowIt(): void
    {
        $pages = ShowcasePricingPages::pages();

        self::assertSame(['/pricing', '/pricing/how-we-earn', '/pricing/order'], array_column($pages, 'slug'));
        self::assertSame([null, 'pricing', 'pricing'], array_column($pages, 'parentSlug'));
        self::assertSame('How we earn', $pages[1]['navTitle']);
        self::assertFalse($pages[1]['hideInNav'] ?? false, 'The model page is in the Pricing flyout.');
        self::assertTrue($pages[2]['hideInNav'] ?? false, 'The order page stays hidden.');
    }

    public function testTheModelPageKeepsItsOutline(): void
    {
        self::assertSame(self::MODEL_OUTLINE, array_column(self::page('/pricing/how-we-earn')['content'], 'ctype'));
    }

    public function testThePricingPageLinksTheModelPageFromItsHero(): void
    {
        $hero = self::page('/pricing')['content'][0];

        self::assertSame('desiderio_hero', $hero['ctype']);
        self::assertSame('{{page:pricing/how-we-earn}}', $hero['fields']['secondary_button_link']);
    }

    public function testCareIsOneBlockShownOnceOnEachSalesPage(): void
    {
        $care = ShowcasePricing::care();
        $blocks = [];
        foreach (['/pricing', '/pricing/how-we-earn'] as $slug) {
            $found = array_values(array_filter(
                self::page($slug)['content'],
                static fn(array $block): bool => ($block['fields']['header'] ?? null) === $care['header'],
            ));
            self::assertCount(1, $found, $slug);
            self::assertSame($care['price'], $found[0]['fields']['price'], $slug);
            $blocks[] = $found[0];
        }

        self::assertSame($blocks[0], $blocks[1], 'Both pages show the same Care block.');
    }

    public function testEveryLinkGoesToAPageOfTheSiteOrAFullUrl(): void
    {
        $links = [];
        foreach (ShowcasePricingPages::pages() as $page) {
            array_walk_recursive($page['content'], static function (mixed $value, int|string $key) use (&$links, $page): void {
                if (!is_string($value)) {
                    return;
                }
                if (is_string($key) && preg_match('/(^|_)link$/', $key) === 1 && $value !== '') {
                    $links[] = [$page['slug'], $value];
                }
                if (preg_match_all('/href="([^"]*)"/', $value, $matches) > 0) {
                    foreach ($matches[1] as $href) {
                        $links[] = [$page['slug'], $href];
                    }
                }
            });
        }

        self::assertNotEmpty($links);
        $plain = array_filter(
            $links,
            static fn(array $link): bool => preg_match('#^(\{\{page:[a-z0-9/-]+\}\}|\{\{site:[^}]+\}\}|https://\S+|mailto:\S+|t3://\S+)$#', $link[1]) !== 1,
        );
        self::assertSame([], array_values($plain));
    }

    public function testTheModelPageAndThePricingFaqNameTheSeller(): void
    {
        foreach (['/pricing', '/pricing/how-we-earn', '/pricing/order'] as $slug) {
            $copy = json_encode(self::page($slug)['content'], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            self::assertStringContainsString(ShowcasePricing::SELLER_NAME, $copy, $slug);
            self::assertStringContainsString(ShowcasePricing::SELLER_IMPRINT_URL, $copy, $slug);
            self::assertStringContainsString(ShowcasePricing::SELLER_TERMS_URL, $copy, $slug);
        }
    }

    /**
     * A price on these pages is a price of the price source: the pages never
     * type one themselves.
     */
    public function testEveryPriceOnThePagesComesFromThePriceSource(): void
    {
        $source = json_encode([
            ShowcasePricing::plans(),
            ShowcasePricing::comparison(),
            ShowcasePricing::promises(),
            ShowcasePricing::services(),
            ShowcasePricing::launchPack(),
            ShowcasePricing::foundingOffer(),
            ShowcasePricing::faq(),
            ShowcasePricing::care(),
            ShowcasePricing::supporter(),
            ShowcasePricing::sponsoredFeature(),
            ShowcasePricing::ways(),
            ShowcasePricing::alwaysFree(),
            ShowcasePricing::costsMoney(),
            ShowcasePricing::modelFaq(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $pages = json_encode(ShowcasePricingPages::pages(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        preg_match_all('/€[0-9][0-9,]*/u', $pages, $matches);
        $prices = array_values(array_unique(array_map(static fn(string $price): string => rtrim($price, ','), $matches[0])));

        self::assertContains('€390', $prices);
        self::assertContains('€190', $prices);
        foreach ($prices as $price) {
            self::assertMatchesRegularExpression('/' . preg_quote($price, '/') . '(?![0-9])/u', $source, $price . ' is typed on a page, not taken from ShowcasePricing.');
        }
    }

    /**
     * @return ShowcasePage
     */
    private static function page(string $slug): array
    {
        foreach (ShowcasePricingPages::pages() as $page) {
            if ($page['slug'] === $slug) {
                return $page;
            }
        }

        self::fail('No page ' . $slug);
    }
}
