<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\BlogDemoPostDefinitions;

/**
 * The demo blog posts: each has its own comment and a picture that ships
 * with Desiderio, and none of them is one the seeder retires.
 */
final class BlogDemoPostDefinitionsTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../../';

    public function testEveryPostHasABundledPictureWithAltText(): void
    {
        foreach (BlogDemoPostDefinitions::demoPosts() as $post) {
            self::assertFileExists(self::ROOT . $post['image']['file'], $post['slug']);
            self::assertStringStartsWith('Resources/Public/', $post['image']['file'], $post['slug']);
            self::assertNotSame('', trim($post['image']['alternative']), $post['slug']);
        }
    }

    public function testCommentsDifferFromEachOtherAndFromTheOldSharedOne(): void
    {
        $texts = array_map(static fn(array $post): string => $post['comment']['text'], BlogDemoPostDefinitions::demoPosts());
        $authors = array_map(static fn(array $post): string => $post['comment']['email'], BlogDemoPostDefinitions::demoPosts());

        self::assertSame($texts, array_values(array_unique($texts)));
        self::assertSame($authors, array_values(array_unique($authors)));
        self::assertNotContains(BlogDemoPostDefinitions::LEGACY_EXAMPLE_COMMENT, $texts);
        foreach ($authors as $email) {
            self::assertStringEndsWith('@example.com', $email);
        }
    }

    public function testNoDemoPostUsesARetiredSlug(): void
    {
        $slugs = array_column(BlogDemoPostDefinitions::demoPosts(), 'slug');

        self::assertSame([], array_values(array_intersect($slugs, BlogDemoPostDefinitions::retiredSlugs())));
    }
}
