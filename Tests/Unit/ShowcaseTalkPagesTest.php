<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseTalkPages;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * The usability-slogans talk page below Lab and resources. The films seeder
 * places the long cut right after the header section, so the header section
 * opens the page.
 */
final class ShowcaseTalkPagesTest extends TestCase
{
    private const array OUTLINE = [
        'desiderio_headersection',
        'desiderio_featurenumbered',
        'desiderio_textmedia',
        'desiderio_ctabanner',
    ];

    public function testThereIsOneTalkPageBelowTheLabPage(): void
    {
        $pages = ShowcaseTalkPages::pages();

        self::assertCount(1, $pages);
        $page = $pages[0];
        self::assertSame('/usability-slogans', $page['slug']);
        self::assertSame('resources', $page['parentSlug']);
        self::assertSame('Ten usability slogans', $page['title']);
        self::assertSame('Usability slogans (talk)', $page['navTitle']);
        self::assertSame('Ten usability slogans, explained by Kurt Dirnbauer', $page['seoTitle'] ?? null);
        self::assertFalse($page['hideInNav'] ?? false, 'The talk is in the menu below Lab and resources.');
        self::assertArrayNotHasKey('formerSlugs', $page, 'The page is new and redirects nothing.');
        self::assertNotSame('', trim($page['abstract']));
        self::assertLessThanOrEqual(160, mb_strlen($page['abstract']));
        self::assertLessThanOrEqual(160, mb_strlen($page['description']));
    }

    public function testTheShowcaseSeedsItRightAfterItsParent(): void
    {
        $slugs = array_column(StyleguideShowcasePages::subpages(), 'slug');
        $parent = array_search('/resources', $slugs, true);
        $talk = array_search('/usability-slogans', $slugs, true);

        self::assertIsInt($parent);
        self::assertSame($parent + 1, $talk);
    }

    public function testThePageFollowsTheTalkOutline(): void
    {
        $content = ShowcaseTalkPages::pages()[0]['content'];

        self::assertSame(self::OUTLINE, array_map(static fn(array $block): string => $block['ctype'], $content));
        self::assertSame('From the founder', $content[0]['fields']['eyebrow'] ?? null);
        self::assertSame('Ten usability slogans, explained by Kurt Dirnbauer', $content[0]['fields']['header'] ?? null);

        $slogans = $content[1]['fields']['items'] ?? null;
        self::assertIsArray($slogans);
        self::assertCount(10, $slogans, 'The talk has ten slogans.');
        self::assertSame(ShowcaseTalkPages::slogans(), $slogans);

        $editors = $content[2]['fields']['content'] ?? null;
        self::assertIsString($editors);
        self::assertStringContainsString('Jakob Nielsen', $editors);
        self::assertStringContainsString('(1993), chapter 1.3', $editors);
        self::assertStringContainsString('href="' . ShowcaseTalkPages::NIELSEN_URL . '"', $editors);
        self::assertStringContainsString('href="' . ShowcaseTalkPages::ARTICLE_URL . '"', $editors);

        self::assertSame('{{page:target-groups/for-inhouse-teams}}', $content[3]['fields']['cta_link'] ?? null);
    }

    public function testEveryLinkGoesToAShowcasePageOrAFullUrl(): void
    {
        $slugs = array_column(StyleguideShowcasePages::subpages(), 'slug');
        $content = ShowcaseTalkPages::pages()[0]['content'];
        $links = [];
        array_walk_recursive($content, static function (mixed $value, int|string $key) use (&$links): void {
            if (!is_string($value)) {
                return;
            }
            if (is_string($key) && preg_match('/(^|_)link$/', $key) === 1 && $value !== '') {
                $links[] = $value;
            }
            if (preg_match_all('/href="([^"]*)"/', $value, $matches) > 0) {
                array_push($links, ...$matches[1]);
            }
        });

        self::assertCount(3, $links);
        foreach ($links as $link) {
            if (preg_match('/^\{\{page:([a-z0-9\/-]+)\}\}$/', $link, $match) === 1) {
                self::assertContains('/' . $match[1], $slugs, $link);
                continue;
            }
            self::assertMatchesRegularExpression('#^https://\S+$#', $link);
        }
    }
}
