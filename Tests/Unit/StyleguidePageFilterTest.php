<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Seeding\StyleguidePageFilter;

/**
 * The selection of `desiderio:styleguide:seed --pages=…`: which page uids
 * and slug forms an operator may type, and what the run needs to exist.
 */
final class StyleguidePageFilterTest extends TestCase
{
    private const array PLAN = [
        '/',
        '/content-types',
        '/content-types/hero-landing-intros',
        '/pricing',
        '/pricing/how-we-earn',
        '/404',
    ];

    private const array EXISTING_UIDS = [
        '/' => 505,
        '/content-types' => 1012,
        '/content-types/hero-landing-intros' => 1013,
        '/pricing' => 1100,
        '/pricing/how-we-earn' => null,
        '/404' => 1200,
    ];

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function slugFormProvider(): iterable
    {
        yield 'as the seeder writes it' => ['/pricing/how-we-earn', '/pricing/how-we-earn'];
        yield 'without the leading slash' => ['pricing/how-we-earn', '/pricing/how-we-earn'];
        yield 'with a trailing slash' => ['/pricing/how-we-earn/', '/pricing/how-we-earn'];
        yield 'upper case and spaces' => ['  Pricing/How-We-Earn ', '/pricing/how-we-earn'];
        yield 'a URL with query and fragment' => ['https://example.org/pricing/how-we-earn/?x=1#faq', '/pricing/how-we-earn'];
        yield 'home' => ['home', '/'];
        yield 'a single slash' => ['/', '/'];
        yield 'a numeric-looking slug' => ['404', '/404'];
    }

    #[DataProvider('slugFormProvider')]
    public function testNormalizeSlugGivesTheSeederSlug(string $selector, string $expected): void
    {
        self::assertSame($expected, StyleguidePageFilter::normalizeSlug($selector));
    }

    public function testSlugsAndUidsSelectPlannedPagesInPlanOrder(): void
    {
        $filter = StyleguidePageFilter::fromSelectors('pricing/how-we-earn, 1013,home,/pricing/how-we-earn/', self::PLAN, self::EXISTING_UIDS);

        self::assertSame(['/', '/content-types/hero-landing-intros', '/pricing/how-we-earn'], $filter->slugs());
        self::assertTrue($filter->includes('/content-types/hero-landing-intros'));
        self::assertFalse($filter->includes('/content-types'));
    }

    public function testTheRootPageUidSelectsHome(): void
    {
        self::assertSame(['/'], StyleguidePageFilter::fromSelectors('505', self::PLAN, self::EXISTING_UIDS)->slugs());
    }

    public function testAPathWithTheSiteBasePathMatches(): void
    {
        $filter = StyleguidePageFilter::fromSelectors(
            'https://typo3-lab.example/desiderio/pricing/how-we-earn/,/desiderio/',
            self::PLAN,
            self::EXISTING_UIDS,
            '/desiderio/'
        );

        self::assertSame(['/', '/pricing/how-we-earn'], $filter->slugs());
    }

    public function testAUidSelectorWinsOverTheSlugOfADigitOnlySlug(): void
    {
        // "404" is read as a uid; the slug form "/404" still selects the page.
        self::assertSame(['/404'], StyleguidePageFilter::fromSelectors('/404', self::PLAN, self::EXISTING_UIDS)->slugs());
        self::assertSame(['/404'], StyleguidePageFilter::fromSelectors('1200', self::PLAN, self::EXISTING_UIDS)->slugs());
    }

    public function testAnUnknownSlugFailsAndListsTheValidSlugs(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No page of the styleguide plan matches /no-such-page. Give a uid of an existing seeded page or one of these slugs: /, /content-types, /content-types/hero-landing-intros, /pricing, /pricing/how-we-earn, /404');

        StyleguidePageFilter::fromSelectors('/pricing,/no-such-page', self::PLAN, self::EXISTING_UIDS);
    }

    public function testAUidOfNoPlannedPageFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No page of the styleguide plan matches uid 999.');

        StyleguidePageFilter::fromSelectors('999', self::PLAN, self::EXISTING_UIDS);
    }

    public function testAnEmptySelectionFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1793875202);

        StyleguidePageFilter::fromSelectors(' , ', self::PLAN, self::EXISTING_UIDS);
    }

    public function testMissingParentsAndLinkTargetsThatAreNotSelectedAreReported(): void
    {
        $parents = [
            '/' => null,
            '/content-types' => '/',
            '/content-types/hero-landing-intros' => '/content-types',
            '/pricing' => '/',
            '/pricing/how-we-earn' => '/pricing',
            '/404' => null,
        ];
        $existing = ['/content-types' => null, '/pricing/how-we-earn' => null, '/404' => null] + self::EXISTING_UIDS;

        $chapterOnly = StyleguidePageFilter::fromSelectors('/content-types/hero-landing-intros', self::PLAN, $existing);
        self::assertSame(['/content-types', '/pricing/how-we-earn'], $chapterOnly->missingDependencies($parents, $existing, ['/pricing/how-we-earn', '/pricing', '/not-planned', '/404']));

        // "/404" stands for a page another package owns (no planned parent):
        // the seeder never creates it, so a link to it is no dependency.
        // A selected page is created by the run itself, so it never counts.
        $withParent = StyleguidePageFilter::fromSelectors('/content-types,/content-types/hero-landing-intros,/pricing/how-we-earn', self::PLAN, $existing);
        self::assertSame([], $withParent->missingDependencies($parents, $existing, ['/pricing/how-we-earn', '/404']));
    }
}
