<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseHeroPhotos;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricing;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * The plans, prices and first-level heroes of the showcase: one price scheme
 * everywhere, no retired offer left in any copy, and every first-level page
 * opening with the same hero and a photo in the size the hero shows.
 */
final class ShowcasePricingTest extends TestCase
{
    public function testThePlansComeInOrderWithAgencyRecommended(): void
    {
        $plans = ShowcasePricing::plans();

        self::assertSame(['community', 'studio', 'agency', 'partner'], array_column($plans, 'key'));
        self::assertSame(['€0', '€590', '€1,990', '€4,900'], array_column($plans, 'price'));
        self::assertSame(['agency'], array_values(array_column(array_filter($plans, static fn(array $plan): bool => $plan['featured']), 'key')));
        foreach ($plans as $plan) {
            if ($plan['key'] !== 'community') {
                self::assertSame(ShowcasePricing::ORDER_LINK, $plan['button']['link'], $plan['key'] . ' is ordered on the order page.');
            }
        }
    }

    public function testTheComparisonMatchesThePlans(): void
    {
        $rows = ShowcasePricing::comparison();
        foreach ($rows as $row) {
            self::assertCount(count(ShowcasePricing::plans()), $row['values'], $row['feature']);
        }
        self::assertSame('Price', $rows[0]['feature']);
        self::assertSame(['€0', '€590 a year', '€1,990 a year', '€4,900 a year'], $rows[0]['values']);
    }

    public function testTheLaunchPackAddsUpToItsParts(): void
    {
        $pack = ShowcasePricing::launchPack();
        $parts = array_map(static fn(array $item): int => (int)str_replace(['€', ','], '', $item['price']), $pack['items']);

        // The editor training (the last part) is the included bonus.
        self::assertSame((int)str_replace(['€', ','], '', $pack['price']), array_sum(array_slice($parts, 0, -1)));
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function retiredOffers(): iterable
    {
        // Prices and promises of the scheme before 2026-09-27.
        yield 'Pro plan price' => ['/€\s?49\b(?! a month)/u'];
        yield 'old Agency price' => ['/€\s?149\b/u'];
        yield 'old custom element price' => ['/€\s?390\b/u'];
        yield 'old upgrade price' => ['/€\s?2,400\b/u'];
        yield 'old Launch bundle' => ['/€\s?1,790\b/u'];
        yield 'discount code' => ['/DESIDERIO20/'];
        yield 'Creator Care' => ['/Creator Care/'];
        yield 'hosting offer' => ['/managed hosting/i'];
        yield 'unlimited projects' => ['/unlimited (client )?projects/i'];
        yield 'four-hour answers' => ['/4 business hours/i'];
        yield 'installation service' => ['/installation service/i'];
        // Every release is free; the plans sell support (owner decision 2026-09-27).
        yield 'paid early access' => ['/early[- ]access/i'];
    }

    #[DataProvider('retiredOffers')]
    public function testNoCopyNamesARetiredOffer(string $pattern): void
    {
        $copy = json_encode([
            StyleguideShowcasePages::homeContent(),
            StyleguideShowcasePages::subpages(),
            StyleguideShowcasePages::adoptedHeroes(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        self::assertDoesNotMatchRegularExpression($pattern, $copy);
    }

    public function testEveryFirstLevelPageOpensWithTheSalesHero(): void
    {
        $firstLevel = array_filter(
            StyleguideShowcasePages::subpages(),
            static fn(array $page): bool => $page['parentSlug'] === null && !($page['hideInNav'] ?? false) && !in_array($page['slug'], ['/imprint', '/privacy', '/accessibility', '/404'], true),
        );

        self::assertSame(['/product', '/features', '/ai', '/target-groups', '/pricing', '/resources'], array_values(array_column($firstLevel, 'slug')));
        $heroes = [
            StyleguideShowcasePages::homeContent()[0],
            ...array_map(static fn(array $page): array => $page['content'][0], array_values($firstLevel)),
            ...array_values(StyleguideShowcasePages::adoptedHeroes()),
        ];
        foreach ($heroes as $index => $hero) {
            self::assertSame('desiderio_hero', $hero['ctype'], (string)$index);
            self::assertSame('split', $hero['fields']['variant'], (string)$index);
            self::assertIsArray($hero['fields']['hero_image']);
            self::assertStringStartsWith('Resources/Public/Styleguide/Heroes/hero-', $hero['fields']['hero_image']['file'], (string)$index);
        }
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function heroPhotos(): iterable
    {
        foreach (ShowcaseHeroPhotos::pages() as $page) {
            yield $page => [$page];
        }
    }

    /**
     * The split hero crops to 16:10 and renders up to 720 px wide, 1440 px on
     * sharp screens: the photos are exactly that.
     */
    #[DataProvider('heroPhotos')]
    public function testEveryHeroPhotoFitsTheHero(string $page): void
    {
        $photo = ShowcaseHeroPhotos::for($page);
        $path = dirname(__DIR__, 2) . '/' . $photo['file'];

        self::assertFileExists($path);
        self::assertMatchesRegularExpression('/^hero-[a-z]+-[0-9a-f]{8}\.webp$/', basename($path));
        $size = getimagesize($path);
        self::assertIsArray($size);
        self::assertSame([1440, 900], [$size[0], $size[1]]);
        self::assertNotSame('', $photo['alternative']);
    }
}
