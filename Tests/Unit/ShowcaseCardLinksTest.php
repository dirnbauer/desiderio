<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * A card grid links all its cards or none: a "Read more" on one card and not
 * on its neighbours reads as a mistake, and every link needs a page behind it.
 */
final class ShowcaseCardLinksTest extends TestCase
{
    public function testEveryCardGridLinksAllItsCardsOrNone(): void
    {
        $pages = [['slug' => '/', 'content' => StyleguideShowcasePages::homeContent()], ...StyleguideShowcasePages::subpages()];
        $mixed = [];
        foreach ($pages as $page) {
            foreach ($page['content'] as $index => $block) {
                foreach ($block['fields'] as $field => $items) {
                    if (!is_array($items) || !array_is_list($items)) {
                        continue;
                    }
                    $withLink = array_filter($items, static fn(mixed $item): bool => is_array($item) && array_key_exists('link', $item));
                    $linked = array_filter($withLink, static fn(array $item): bool => is_string($item['link']) && $item['link'] !== '');
                    if ($linked !== [] && count($linked) < count($withLink)) {
                        $mixed[] = sprintf('%s: block %d %s.%s links %d of %d cards', $page['slug'], $index, $block['ctype'], $field, count($linked), count($withLink));
                    }
                }
            }
        }

        self::assertSame([], $mixed);
    }
}
