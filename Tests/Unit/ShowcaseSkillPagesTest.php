<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeaturePages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseSkillPages;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * The ten skill pages below /ai/agentic-skills share one outline, so they
 * read alike and the films seeder finds the same intro on each.
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseSkillPagesTest extends TestCase
{
    /**
     * Position 7 holds the screens: a gallery for two or more, one
     * text-and-media block for a single screen (a gallery shows a single
     * image twice, large and as a lone thumbnail).
     */
    private const array OUTLINE = [
        'desiderio_headersection',
        'desiderio_featurecards',
        'desiderio_featurechecklist',
        'desiderio_featurenumbered',
        'desiderio_codeblock',
        'desiderio_quote',
        'screens',
        'desiderio_ctabanner',
    ];

    private const array SKILLS = [
        'typo3-upgrade-run',
        'typo3-wcag22-aa-agentic',
        'typo3-webmcp',
        'typo3-structured-data',
        'typo3-backend-rights',
        'typo3-content-blocks',
        'typo3-shadcn-content-elements',
        'typo3-visual-editor',
        'typo3-rector',
        'typo3-records-list-types',
    ];

    public function testThereAreTenSkillPagesBelowTheAgenticSkillsPage(): void
    {
        $pages = ShowcaseSkillPages::pages();

        self::assertSame(
            array_map(static fn(string $skill): string => '/ai/agentic-skills/' . $skill, self::SKILLS),
            array_column($pages, 'slug'),
        );
        foreach ($pages as $page) {
            self::assertSame('ai/agentic-skills', $page['parentSlug'], $page['slug']);
            self::assertArrayNotHasKey('formerSlugs', $page, $page['slug'] . ' is new and redirects nothing.');
        }
    }

    public function testTheShowcaseSeedsThemAfterTheirParent(): void
    {
        $slugs = array_column(StyleguideShowcasePages::subpages(), 'slug');
        $parent = array_search('/ai/agentic-skills', $slugs, true);

        self::assertIsInt($parent, 'The Agentic skills page is seeded.');
        foreach (ShowcaseSkillPages::pages() as $page) {
            $position = array_search($page['slug'], $slugs, true);
            self::assertIsInt($position, $page['slug']);
            self::assertGreaterThan($parent, $position, $page['slug'] . ' comes after its parent.');
        }
        self::assertContains('/ai/agentic-skills', array_column(ShowcaseFeaturePages::aiPages(), 'slug'));
    }

    public function testEverySkillPageFollowsTheSameOutline(): void
    {
        foreach (ShowcaseSkillPages::pages() as $page) {
            $ctypes = array_map(static fn(array $block): string => $block['ctype'], $page['content']);
            $screens = $ctypes[6] ?? '';
            self::assertContains($screens, ['desiderio_gallery', 'desiderio_textmedia'], $page['slug']);
            $ctypes[6] = 'screens';
            self::assertSame(self::OUTLINE, $ctypes, $page['slug']);
            $header = $page['content'][0]['fields'];
            self::assertSame('Skill', $header['eyebrow'] ?? null, $page['slug']);
            $skill = substr($page['slug'], strlen('/ai/agentic-skills/'));
            self::assertNotSame($skill, $header['header'] ?? null, $page['slug'] . ': the page title already names the skill; the header section opens with its promise.');
            self::assertTrue(str_starts_with($page['title'], $skill), $page['slug'] . ': the page title names the skill.');
            self::assertNotSame('', trim((string)($header['header'] ?? '')), $page['slug']);
            self::assertCount(3, self::items($page, 'desiderio_featurecards'), $page['slug']);
            $checklist = count(self::items($page, 'desiderio_featurechecklist'));
            self::assertGreaterThanOrEqual(4, $checklist, $page['slug']);
            self::assertLessThanOrEqual(5, $checklist, $page['slug']);
            self::assertCount(4, self::items($page, 'desiderio_featurenumbered'), $page['slug']);
            if ($screens === 'desiderio_gallery') {
                $gallery = count(self::items($page, 'desiderio_gallery'));
                self::assertGreaterThanOrEqual(2, $gallery, $page['slug'] . ': a gallery only for two or more screens.');
                self::assertLessThanOrEqual(3, $gallery, $page['slug']);
            } else {
                self::assertIsArray(self::block($page, 'desiderio_textmedia')['media'] ?? null, $page['slug'] . ': the single screen.');
            }
            self::assertStringStartsWith('SKILL.md, line', (string)(self::block($page, 'desiderio_quote')['role'] ?? ''), $page['slug'] . ': the quote names its source line.');
            self::assertSame(ShowcaseSkillPages::REPO_URL, self::block($page, 'desiderio_ctabanner')['cta_link'] ?? null, $page['slug']);
        }
    }

    public function testMenuTextsFitTheMenu(): void
    {
        foreach (ShowcaseSkillPages::pages() as $page) {
            self::assertNotSame('', trim($page['navTitle']), $page['slug']);
            self::assertLessThanOrEqual(42, mb_strlen($page['navTitle']), $page['slug']);
            self::assertNotSame('', trim($page['abstract']), $page['slug']);
            self::assertLessThanOrEqual(160, mb_strlen($page['abstract']), $page['slug']);
            self::assertLessThanOrEqual(160, mb_strlen($page['description']), $page['slug']);
        }
    }

    public function testTheOverviewLinksEverySkillPageOnce(): void
    {
        $cards = ShowcaseSkillPages::cards();

        self::assertSame(self::SKILLS, array_column($cards, 'slug'));
        $overview = array_values(array_filter(ShowcaseFeaturePages::aiPages(), static fn(array $page): bool => $page['slug'] === '/ai/agentic-skills'))[0];
        $links = [];
        array_walk_recursive($overview['content'], static function (mixed $value, int|string $key) use (&$links): void {
            if ($key === 'link' && is_string($value) && str_starts_with($value, '{{page:ai/agentic-skills/')) {
                $links[] = $value;
            }
        });
        foreach (self::SKILLS as $skill) {
            self::assertContains('{{page:ai/agentic-skills/' . $skill . '}}', $links, $skill);
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function screenshots(): iterable
    {
        $files = [];
        foreach (ShowcaseSkillPages::pages() as $page) {
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

    #[DataProvider('screenshots')]
    public function testEveryScreenshotExists(string $file, string $name): void
    {
        self::assertFileExists(dirname(__DIR__, 2) . '/' . $file);
        self::assertMatchesRegularExpression('/^frontend-feature-skills-[a-z0-9-]+-[0-9a-f]{8}\.webp$/', $name);
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
     * @param ShowcasePage $page
     * @return array<mixed>
     */
    private static function items(array $page, string $ctype): array
    {
        $items = self::block($page, $ctype)['items'] ?? null;
        self::assertIsArray($items);

        return $items;
    }
}
