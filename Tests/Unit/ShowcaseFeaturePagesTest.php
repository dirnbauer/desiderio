<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeaturePages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseSoftwareDefinitions;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseFeaturePagesTest extends TestCase
{
    public function testTheShowcaseSeedsTheFeaturePages(): void
    {
        $slugs = array_column(StyleguideShowcasePages::subpages(), 'slug');

        foreach (ShowcaseFeaturePages::pages() as $page) {
            self::assertContains($page['slug'], $slugs);
        }
    }

    /**
     * @return iterable<string, array{0: array<int, ShowcasePage>, 1: string}>
     */
    public static function sections(): iterable
    {
        yield 'features' => [ShowcaseFeaturePages::websitePages(), '/features'];
        yield 'ai' => [ShowcaseFeaturePages::aiPages(), '/ai'];
        yield 'software' => [ShowcaseFeaturePages::softwarePages(), '/software'];
    }

    /**
     * @param array<int, ShowcasePage> $pages
     */
    #[DataProvider('sections')]
    public function testEachHubListsItsFeaturePagesOnce(array $pages, string $hubSlug): void
    {
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

        self::assertSame($hubSlug, $hub['slug']);
        self::assertSame('desiderio_hero', $hub['content'][0]['ctype'] ?? null, 'The hub opens with the sales hero.');
        self::assertCount(count(array_unique($featureSlugs)), $featureSlugs, 'Feature slugs are unique.');
        self::assertSame($featureSlugs, $linked, 'The hub links every feature page once, in page order.');
        foreach ($featureSlugs as $slug) {
            self::assertStringStartsWith($hubSlug . '/', $slug);
        }
    }

    public function testTheSplitKeepsThe28FeaturesAndTheFourProducts(): void
    {
        self::assertCount(1 + 17, ShowcaseFeaturePages::websitePages());
        self::assertCount(1 + 11, ShowcaseFeaturePages::aiPages());
        self::assertCount(1 + 4, ShowcaseFeaturePages::softwarePages());
    }

    public function testTheSoftwareHubShowsItsFourProductsInTwoColumns(): void
    {
        $teasers = array_values(array_filter(
            ShowcaseFeaturePages::softwarePages()[0]['content'],
            static fn(array $block): bool => $block['ctype'] === 'desiderio_blogteasers',
        ));

        self::assertCount(1, $teasers);
        self::assertSame('2', $teasers[0]['fields']['columns'] ?? null);
        self::assertSame('3', ShowcaseFeaturePages::websitePages()[0]['content'][1]['fields']['columns'] ?? null, 'Other hubs keep three columns.');
    }

    /**
     * A hub's own sections sit between its category grids and the closing
     * call to action.
     */
    public function testTheHubSectionsComeBeforeTheCallToAction(): void
    {
        foreach (['ai' => [ShowcaseFeatureDefinitions::aiHub(), ShowcaseFeaturePages::aiPages()[0]], 'software' => [ShowcaseSoftwareDefinitions::hub(), ShowcaseFeaturePages::softwarePages()[0]]] as $section => [$hub, $page]) {
            $sections = $hub['sections'] ?? [];
            self::assertNotSame([], $sections, $section);
            $content = array_values($page['content']);
            self::assertSame($sections, array_slice($content, -1 - count($sections), count($sections)), $section);
            self::assertSame('desiderio_ctabanner', $content[count($content) - 1]['ctype'], $section);
        }
    }

    public function testTheAiPagesRedirectFromTheirFormerFeatureUrls(): void
    {
        foreach (array_slice(ShowcaseFeaturePages::aiPages(), 1) as $page) {
            self::assertSame(['/features/' . substr($page['slug'], strlen('/ai/'))], $page['formerSlugs'] ?? null, $page['slug']);
        }
        foreach ([...ShowcaseFeaturePages::websitePages(), ...ShowcaseFeaturePages::softwarePages()] as $page) {
            self::assertArrayNotHasKey('formerSlugs', $page, $page['slug']);
        }
    }

    public function testEveryFeaturePageFollowsTheSameOutline(): void
    {
        $credited = [];
        $installed = [];
        $sections = [];
        $opening = [];
        foreach (['features' => ShowcaseFeatureDefinitions::categories(), 'ai' => ShowcaseFeatureDefinitions::aiCategories(), 'software' => ShowcaseSoftwareDefinitions::categories()] as $section => $categories) {
            foreach ($categories as $category) {
                foreach ($category['features'] as $feature) {
                    $slug = '/' . $section . '/' . $feature['slug'];
                    $credited[$slug] = isset($feature['credits']);
                    $installed[$slug] = $feature['install'] !== null;
                    $sections[$slug] = array_map(static fn(array $block): string => $block['ctype'], $feature['sections'] ?? []);
                    // The problem, the tour and the benefits, unless the
                    // page's own sections replace them (null).
                    $opening[$slug] = [
                        ...($feature['problem'] !== null ? ['desiderio_contenthighlight'] : []),
                        ...($feature['tour'] !== null ? ['desiderio_gallery'] : []),
                        ...($feature['benefits'] !== null ? ['desiderio_benefitcards'] : []),
                    ];
                }
            }
        }

        $featurePages = [...array_slice(ShowcaseFeaturePages::websitePages(), 1), ...array_slice(ShowcaseFeaturePages::aiPages(), 1), ...array_slice(ShowcaseFeaturePages::softwarePages(), 1)];
        self::assertCount(count($sections), $featurePages);
        foreach ($featurePages as $page) {
            $ctypes = array_map(static fn(array $block): string => $block['ctype'], $page['content']);
            // The page's own sections, as declared, between benefits and questions.
            $expected = ['desiderio_herosaas', ...$opening[$page['slug']], ...$sections[$page['slug']], 'desiderio_faq'];
            if ($installed[$page['slug']] ?? false) {
                $expected[] = 'desiderio_codeblock';
            }
            if ($credited[$page['slug']] ?? false) {
                // The thank-you to the people behind a third-party extension.
                $expected[] = 'desiderio_contenthighlight';
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
        // frontend-dev-companion-* is a diagram, not a capture; its hash is
        // the start of its SHA-256 like the captures'.
        self::assertMatchesRegularExpression('/^((frontend-)?feature-[a-z0-9-]+|frontend-dev-companion-[a-z0-9-]+|hero-[a-z]+)-[0-9a-f]{8}\.webp$/', $name);
    }

    public function testTheCopyNamesNoRealContactData(): void
    {
        $copy = json_encode([ShowcaseFeatureDefinitions::hub(), ShowcaseFeatureDefinitions::categories(), ShowcaseFeatureDefinitions::aiHub(), ShowcaseFeatureDefinitions::aiCategories(), ShowcaseSoftwareDefinitions::hub(), ShowcaseSoftwareDefinitions::categories()], JSON_THROW_ON_ERROR);

        self::assertDoesNotMatchRegularExpression('/[\w.+-]+@(?!example\.(com|org|net)\b)[\w-]+\.[a-z]{2,}/i', $copy, 'Addresses use example.com.');
        self::assertDoesNotMatchRegularExpression('/\+\d{2}[\s\d]{7,}/', $copy, 'No phone numbers.');
    }
}
