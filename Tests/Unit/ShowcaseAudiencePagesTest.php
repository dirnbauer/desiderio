<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseAudiencePages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricing;

/**
 * The target-group hub and its three audience pages: the model block on the
 * hub, one outline on every audience page (the path with Desiderio and what
 * is free next to what you pay for, after the benefits), links the seeder can
 * resolve, and no price that is not in the price source.
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseAudiencePagesTest extends TestCase
{
    private const array SUBPAGES = [
        '/target-groups/for-agencies',
        '/target-groups/for-inhouse-teams',
        '/target-groups/for-freelancers',
    ];

    private const array OUTLINE = [
        'desiderio_headersection',
        'desiderio_textmedia',
        'desiderio_benefitcards',
        'desiderio_featurenumbered',
        'desiderio_pricingtwotier',
        'desiderio_pricingsimple',
        'desiderio_testimonial',
        'desiderio_ctabanner',
    ];

    /**
     * Amounts in the business cases that are the reader's own figures, not
     * our prices: internal hourly rates (€75 agency, €120 landing page, €90
     * freelancer), the costs they lead to (€1,200–2,400 a landing page,
     * €4,500 to €7,500 an element set, €4,500 and €5,400 saved, €55,000 of
     * work), a €10,000 fixed-price site, and the Agency plan per project at
     * eight projects (€249).
     */
    private const array READER_FIGURES = ['€75', '€120', '€90', '€1,200', '€4,500', '€7,500', '€5,400', '€10,000', '€55,000', '€249'];

    public function testTheHubAndItsThreeAudiencePages(): void
    {
        $pages = ShowcaseAudiencePages::pages();

        self::assertSame(['/target-groups', ...self::SUBPAGES], array_column($pages, 'slug'));
        self::assertNull($pages[0]['parentSlug']);
        foreach (array_slice($pages, 1) as $page) {
            self::assertSame('target-groups', $page['parentSlug'], $page['slug']);
        }
    }

    public function testTheHubShowsTheModelAndItsLinkRightAfterItsHero(): void
    {
        $hub = ShowcaseAudiencePages::pages()[0];
        $ctypes = array_column($hub['content'], 'ctype');

        self::assertSame('desiderio_hero', $ctypes[0]);
        self::assertSame('desiderio_featurecards', $ctypes[1]);
        self::assertSame('desiderio_ctainline', $ctypes[2]);
        self::assertSame(['desiderio_textmedia', 'desiderio_textmedia', 'desiderio_textmedia', 'desiderio_ctabanner'], array_slice($ctypes, 3));
        $model = $hub['content'][1]['fields'];
        self::assertSame('Free software, paid care', $model['header']);
        self::assertSame(ShowcasePricing::modelSentence(), $model['subheadline']);
        self::assertSame(array_column(ShowcasePricing::ways(), 'title'), array_column(self::list($model, 'items'), 'title'));
        $link = $hub['content'][2]['fields'];
        self::assertSame('How we earn with free software', $link['header']);
        self::assertSame('{{page:pricing/how-we-earn}}', $link['cta_link']);
    }

    public function testEveryAudiencePageFollowsTheSameOutline(): void
    {
        foreach (self::subpages() as $page) {
            self::assertSame(self::OUTLINE, array_column($page['content'], 'ctype'), $page['slug']);

            $path = self::block($page, 'desiderio_featurenumbered');
            self::assertSame('Your path with Desiderio', $path['header'], $page['slug']);
            self::assertCount(4, self::list($path, 'items'), $page['slug']);

            $tiers = self::block($page, 'desiderio_pricingtwotier');
            self::assertSame('What you pay, what you get', $tiers['header'], $page['slug']);
            $plans = self::list($tiers, 'plans');
            self::assertCount(2, $plans, $page['slug']);
            [$free, $paidTier] = $plans;
            self::assertIsArray($free);
            self::assertIsArray($paidTier);
            self::assertSame(['Always free', 'Our time'], [$free['title'], $paidTier['title']], $page['slug']);
            self::assertSame(ShowcasePricing::alwaysFree(), array_column(self::list($free, 'features'), 'text'), $page['slug']);
            self::assertSame(ShowcaseBlocks::REPO_URL, $free['button_link'], $page['slug']);
            self::assertFalse($free['featured'], $page['slug']);
            self::assertSame('{{page:pricing}}', $paidTier['button_link'], $page['slug']);
            self::assertTrue($paidTier['featured'], $page['slug']);
            self::assertStringStartsWith('from €', self::text($paidTier, 'price'), $page['slug']);
            $paid = array_column(self::list($paidTier, 'features'), 'text');
            foreach ($paid as $point) {
                self::assertIsString($point);
                self::assertStringContainsString('€', $point, $page['slug'] . ': every paid item names its price.');
            }

            self::assertStringContainsString(
                'href="{{page:pricing/how-we-earn}}"',
                self::text(self::block($page, 'desiderio_pricingsimple'), 'description'),
                $page['slug'] . ': the business case links the model page.',
            );
            self::assertStringContainsString('(example)', self::text(self::block($page, 'desiderio_testimonial'), 'author_title'), $page['slug']);
        }
    }

    public function testEveryLinkIsOneTheSeederResolves(): void
    {
        $links = [];
        $pages = ShowcaseAudiencePages::pages();
        array_walk_recursive($pages, static function (mixed $value, int|string $key) use (&$links): void {
            if (is_string($key) && str_ends_with($key, 'link') && is_string($value)) {
                $links[] = $value;
            }
            if (is_string($value) && preg_match_all('/href="([^"]*)"/', $value, $matches) > 0) {
                array_push($links, ...$matches[1]);
            }
        });

        self::assertNotEmpty($links);
        foreach ($links as $link) {
            self::assertMatchesRegularExpression('#^(\{\{(page|site):[a-z0-9/_-]+\}\}|https://\S+|t3://\S+)$#', $link);
        }
    }

    public function testEveryPriceComesFromThePriceSource(): void
    {
        $source = json_encode([
            ShowcasePricing::plans(),
            ShowcasePricing::services(),
            ShowcasePricing::care(),
            ShowcasePricing::supporter(),
            ShowcasePricing::sponsoredFeature(),
            ShowcasePricing::launchPack(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $copy = json_encode(ShowcaseAudiencePages::pages(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        preg_match_all('/€[0-9]+(?:,[0-9]{3})*/u', $source, $known);
        preg_match_all('/€[0-9]+(?:,[0-9]{3})*/u', $copy, $used);
        $ours = array_values(array_diff(array_unique($used[0]), self::READER_FIGURES));

        self::assertNotEmpty($ours);
        foreach ($ours as $price) {
            self::assertContains($price, $known[0], $price . ' is not in ShowcasePricing: take it from there.');
        }
    }

    public function testMenuTextsFitTheMenu(): void
    {
        foreach (ShowcaseAudiencePages::pages() as $page) {
            self::assertLessThanOrEqual($page['parentSlug'] === null ? 20 : 42, mb_strlen($page['navTitle']), $page['slug']);
            self::assertLessThanOrEqual(160, mb_strlen($page['abstract']), $page['slug']);
            self::assertLessThanOrEqual(160, mb_strlen($page['description']), $page['slug']);
        }
    }

    /**
     * @return list<ShowcasePage>
     */
    private static function subpages(): array
    {
        return array_values(array_filter(
            ShowcaseAudiencePages::pages(),
            static fn(array $page): bool => in_array($page['slug'], self::SUBPAGES, true),
        ));
    }

    /**
     * @param ShowcasePage $page
     * @return array<string, mixed>
     */
    private static function block(array $page, string $ctype): array
    {
        foreach ($page['content'] as $block) {
            if ($block['ctype'] === $ctype) {
                return $block['fields'];
            }
        }
        self::fail($page['slug'] . ' has no ' . $ctype);
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<mixed>
     */
    private static function list(array $fields, string $field): array
    {
        $value = $fields[$field] ?? null;
        self::assertIsArray($value, $field);

        return $value;
    }

    /**
     * @param array<string, mixed> $fields
     */
    private static function text(array $fields, string $field): string
    {
        $value = $fields[$field] ?? null;
        self::assertIsString($value, $field);

        return $value;
    }
}
