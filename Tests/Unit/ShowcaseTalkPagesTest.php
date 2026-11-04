<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseTalkPages;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * The usability-slogans talk page: one hidden page below the home page that
 * hosts the long cut of the talk. The film seeder places the film right after
 * the header section, so the header section opens the page.
 */
final class ShowcaseTalkPagesTest extends TestCase
{
    private const array OUTLINE = [
        'desiderio_headersection',
        'desiderio_featurenumbered',
        'desiderio_contenthighlight',
        'desiderio_ctabanner',
    ];

    public function testThereIsOneHiddenTalkPageBelowTheHomePage(): void
    {
        $pages = ShowcaseTalkPages::pages();

        self::assertCount(1, $pages);
        $page = $pages[0];
        self::assertSame('/usability-slogans', $page['slug']);
        self::assertSame('Usability slogans: the full talk', $page['title']);
        self::assertSame('Usability slogans', $page['navTitle']);
        self::assertNull($page['parentSlug']);
        self::assertTrue($page['hideInNav'] ?? false, 'The talk page stays out of every menu.');
        self::assertArrayNotHasKey('formerSlugs', $page, 'The page is new and redirects nothing.');
        self::assertNotSame('', trim($page['abstract']));
        self::assertLessThanOrEqual(160, mb_strlen($page['abstract']));
        self::assertLessThanOrEqual(160, mb_strlen($page['description']));
    }

    public function testTheShowcaseSeedsIt(): void
    {
        self::assertContains('/usability-slogans', array_column(StyleguideShowcasePages::subpages(), 'slug'));
    }

    public function testThePageFollowsTheTalkOutline(): void
    {
        $content = ShowcaseTalkPages::pages()[0]['content'];

        self::assertSame(self::OUTLINE, array_map(static fn(array $block): string => $block['ctype'], $content));
        self::assertSame('From the founder', $content[0]['fields']['eyebrow'] ?? null);

        $slogans = $content[1]['fields']['items'] ?? null;
        self::assertIsArray($slogans);
        self::assertCount(10, $slogans, 'The talk has ten slogans.');

        $source = $content[2]['fields'];
        self::assertSame('muted', $source['variant'] ?? null);
        self::assertSame(ShowcaseTalkPages::NIELSEN_URL, $source['link'] ?? null);
        self::assertIsString($source['content'] ?? null);
        self::assertStringContainsString('Jakob Nielsen, Usability Engineering, Academic Press 1993, chapter 1.3', $source['content']);
        self::assertStringContainsString('AI avatar (HeyGen)', $source['content']);

        self::assertSame(ShowcaseTalkPages::ARTICLE_URL, $content[3]['fields']['cta_link'] ?? null);
    }
}
