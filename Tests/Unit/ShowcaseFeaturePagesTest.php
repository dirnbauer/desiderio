<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeaturePages;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

final class ShowcaseFeaturePagesTest extends TestCase
{
    public function testTheShowcaseSeedsTheFeaturePages(): void
    {
        $slugs = array_column(StyleguideShowcasePages::subpages(), 'slug');

        foreach (ShowcaseFeaturePages::pages() as $page) {
            self::assertContains($page['slug'], $slugs);
        }
    }

    public function testTheHubListsEveryFeaturePageOnce(): void
    {
        $pages = ShowcaseFeaturePages::pages();
        $hub = $pages[0];
        $featureSlugs = array_map(static fn(array $page): string => $page['slug'], array_slice($pages, 1));
        $linked = [];
        foreach ($hub['content'] as $block) {
            if ($block['ctype'] !== 'desiderio_blogteasers') {
                continue;
            }
            self::assertIsArray($block['fields']['posts']);
            foreach ($block['fields']['posts'] as $post) {
                self::assertIsArray($post);
                self::assertIsString($post['link']);
                $linked[] = '/' . substr($post['link'], strlen('{{page:'), -2);
            }
        }

        self::assertSame('/features', $hub['slug']);
        self::assertCount(count(array_unique($featureSlugs)), $featureSlugs, 'Feature slugs are unique.');
        self::assertSame($featureSlugs, $linked, 'The hub links every feature page once, in page order.');
    }

    public function testEveryFeaturePageFollowsTheSameOutline(): void
    {
        foreach (array_slice(ShowcaseFeaturePages::pages(), 1) as $page) {
            $ctypes = array_map(static fn(array $block): string => $block['ctype'], $page['content']);
            $expected = ['desiderio_herosaas', 'desiderio_contenthighlight', 'desiderio_gallery', 'desiderio_benefitcards', 'desiderio_faq'];
            if (in_array('desiderio_codeblock', $ctypes, true)) {
                $expected[] = 'desiderio_codeblock';
            }
            $expected[] = 'desiderio_ctabanner';

            self::assertSame($expected, $ctypes, $page['slug']);
            foreach ($page['content'] as $block) {
                if ($block['ctype'] !== 'desiderio_gallery') {
                    continue;
                }
                self::assertIsArray($block['fields']['items']);
                foreach ($block['fields']['items'] as $item) {
                    self::assertIsArray($item);
                    self::assertIsString($item['description']);
                    self::assertSame(strip_tags($item['description']), $item['description'], $page['slug'] . ': gallery captions are plain text.');
                }
            }
            self::assertSame('', $page['seoTitle'] ?? null, $page['slug'] . ' clears older SEO titles.');
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function screenshots(): iterable
    {
        $files = [];
        foreach (ShowcaseFeaturePages::pages() as $page) {
            array_walk_recursive($page['content'], static function (mixed $value, int|string $key) use (&$files): void {
                if ($key === 'file' && is_string($value)) {
                    $files[$value] = true;
                }
            });
        }
        foreach (array_keys($files) as $file) {
            yield basename($file) => [$file, basename($file)];
        }
    }

    /**
     * Captures come from Build/Scripts/capture-feature-screenshots.mjs, which
     * names every file with a content hash, so a missing file means a capture
     * that never ran or a reference that was not relinked.
     */
    #[DataProvider('screenshots')]
    public function testEveryScreenshotExists(string $file, string $name): void
    {
        self::assertFileExists(dirname(__DIR__, 2) . '/' . $file);
        self::assertMatchesRegularExpression('/^(frontend-)?feature-[a-z0-9-]+-[0-9a-f]{8}\.webp$/', $name);
    }

    public function testTheCopyNamesNoRealContactData(): void
    {
        $copy = json_encode([ShowcaseFeatureDefinitions::hub(), ShowcaseFeatureDefinitions::categories()], JSON_THROW_ON_ERROR);

        self::assertDoesNotMatchRegularExpression('/[\w.+-]+@(?!example\.(com|org|net)\b)[\w-]+\.[a-z]{2,}/i', $copy, 'Addresses use example.com.');
        self::assertDoesNotMatchRegularExpression('/\+\d{2}[\s\d]{7,}/', $copy, 'No phone numbers.');
    }
}
