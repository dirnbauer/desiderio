<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;
use Webconsulting\Desiderio\Seeding\BlogPageTreeSeeder;

/**
 * With EXT:blog installed the styleguide seeder turns the success stories
 * into a real blog section: the stories become doktype 137 posts carrying
 * categories and tags, the parent page becomes the blog list, and hidden
 * category/tag listing pages are added for the metadata badges.
 */
final class SeedStyleguidePagesCommandBlogFunctionalTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
        'workspaces',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/desiderio',
        't3g/blog',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SeedingBase.csv');
    }

    public function testSeedTurnsSuccessStoriesIntoBlogPostsWithCategoriesAndTags(): void
    {
        $tester = $this->createCommandTester();
        self::assertSame(
            Command::SUCCESS,
            $tester->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]),
            $tester->getDisplay()
        );

        $listPage = $this->fetchPageBySlug('/success-stories');
        self::assertSame(1, $this->intValue($listPage, 'doktype'), 'The blog list page stays a standard page.');
        self::assertSame(BlogPageTreeSeeder::DEFAULT_BACKEND_LAYOUT, $listPage['backend_layout']);
        self::assertSame(BlogPageTreeSeeder::DEFAULT_BACKEND_LAYOUT, $listPage['backend_layout_next_level']);
        self::assertNotSame('', trim($this->stringValue($listPage, 'subtitle')));
        $listPageUid = $this->intValue($listPage, 'uid');

        // The list page leads with the paginated blog_posts plugin.
        self::assertSame(
            1,
            $this->countRows('tt_content', "deleted = 0 AND pid = " . $listPageUid . " AND CType = 'blog_posts'")
        );

        $blogPages = $this->blogShowcasePages();
        self::assertCount(15, $blogPages, 'Fifteen success stories are seeded as blog posts.');

        $allCategories = [];
        $allTags = [];
        foreach ($blogPages as $page) {
            $slug = $page['slug'];
            $meta = $page['blog'];
            $post = $this->fetchPageBySlug($slug);
            self::assertSame(BlogPageTreeSeeder::BLOG_POST_DOKTYPE, $this->intValue($post, 'doktype'), $slug);
            self::assertSame($listPageUid, $this->intValue($post, 'pid'), $slug);
            self::assertGreaterThan(0, $this->intValue($post, 'publish_date'), $slug);
            self::assertSame(BlogPageTreeSeeder::DEFAULT_BACKEND_LAYOUT, $post['backend_layout'], $slug);

            $expectedCategories = $meta['categories'];
            sort($expectedCategories, SORT_STRING);
            self::assertSame($expectedCategories, $this->fetchCategoryTitles($this->intValue($post, 'uid')), $slug);

            $expectedTags = $meta['tags'];
            sort($expectedTags, SORT_STRING);
            self::assertSame($expectedTags, $this->fetchTagTitles($this->intValue($post, 'uid')), $slug);

            $allCategories = array_merge($allCategories, $meta['categories']);
            $allTags = array_merge($allTags, $meta['tags']);
        }

        $distinctCategories = count(array_unique($allCategories));
        $distinctTags = count(array_unique($allTags));

        // Categories and tags are stored on the list page itself; categories
        // must carry the blog record type and a slug or EXT:blog will not see
        // them / the category route enhancer cannot build URLs.
        self::assertSame($distinctCategories, $this->countRows('sys_category', 'deleted = 0 AND pid = ' . $listPageUid));
        self::assertSame($distinctCategories, $this->countRows(
            'sys_category',
            "deleted = 0 AND pid = " . $listPageUid
            . ' AND record_type = ' . BlogPageTreeSeeder::BLOG_CATEGORY_RECORD_TYPE
            . " AND slug <> ''"
        ));
        self::assertSame($distinctTags, $this->countRows('tx_blog_domain_model_tag', 'deleted = 0 AND pid = ' . $listPageUid));

        // Hidden helper pages carry the category/tag listing plugins.
        foreach (['/success-stories/category' => 'blog_category', '/success-stories/tag' => 'blog_tag'] as $slug => $ctype) {
            $helperPage = $this->fetchPageBySlug($slug);
            self::assertSame($listPageUid, $this->intValue($helperPage, 'pid'), $slug);
            self::assertSame(1, $this->intValue($helperPage, 'nav_hide'), $slug);
            self::assertSame(
                1,
                $this->countRows('tt_content', sprintf("deleted = 0 AND pid = %d AND CType = '%s'", $this->intValue($helperPage, 'uid'), $ctype)),
                $slug
            );
        }
    }

    public function testReseedingKeepsBlogRecordsAndRelationsStable(): void
    {
        $firstRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $firstRun->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]));

        $secondRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $secondRun->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]));
        self::assertStringContainsString('(0 new)', $secondRun->getDisplay());

        $expectedPages = count(StyleguideContentGroups::getGroupsWithFixtures())
            + count(StyleguideShowcasePages::subpages())
            + count(StyleguideShowcasePages::blogSupportPages());
        self::assertSame($expectedPages, $this->countRows('pages', 'uid <> 1 AND deleted = 0'));

        $allCategories = [];
        $allTags = [];
        $anthropic = null;
        foreach ($this->blogShowcasePages() as $page) {
            $allCategories = array_merge($allCategories, $page['blog']['categories']);
            $allTags = array_merge($allTags, $page['blog']['tags']);
            if ($page['slug'] === '/success-stories/anthropic') {
                $anthropic = $page;
            }
        }
        self::assertIsArray($anthropic);
        self::assertSame(count(array_unique($allCategories)), $this->countRows('sys_category', 'deleted = 0'));
        self::assertSame(count(array_unique($allTags)), $this->countRows('tx_blog_domain_model_tag', 'deleted = 0'));

        $post = $this->fetchPageBySlug('/success-stories/anthropic');
        self::assertSame(count($anthropic['blog']['categories']), $this->countRows('sys_category_record_mm', sprintf(
            "uid_foreign = %d AND tablenames = 'pages' AND fieldname = 'categories'",
            $this->intValue($post, 'uid')
        )));
        self::assertSame(count($anthropic['blog']['tags']), $this->countRows('tx_blog_tag_pages_mm', 'uid_local = ' . $this->intValue($post, 'uid')));
        // Helper pages and the list keep exactly one live plugin element each.
        self::assertSame(3, $this->countRows('tt_content', "deleted = 0 AND CType IN ('blog_posts', 'blog_category', 'blog_tag')"));
    }

    /**
     * @return list<array{slug: string, blog: array{publishDate: string, categories: list<string>, tags: list<string>}}>
     */
    private function blogShowcasePages(): array
    {
        $pages = [];
        foreach (StyleguideShowcasePages::subpages() as $page) {
            if (isset($page['blog'])) {
                $pages[] = ['slug' => $page['slug'], 'blog' => $page['blog']];
            }
        }

        return $pages;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPageBySlug(string $slug): array
    {
        $row = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT * FROM pages WHERE deleted = 0 AND slug = ? LIMIT 1', [$slug])
            ->fetchAssociative();

        self::assertIsArray($row, 'Missing page with slug ' . $slug);

        return $row;
    }

    /**
     * @return list<string>
     */
    private function fetchCategoryTitles(int $postUid): array
    {
        $titles = $this->getConnectionPool()
            ->getConnectionForTable('sys_category')
            ->executeQuery(
                'SELECT sys_category.title FROM sys_category'
                . ' JOIN sys_category_record_mm ON sys_category_record_mm.uid_local = sys_category.uid'
                . " WHERE sys_category_record_mm.uid_foreign = ? AND sys_category_record_mm.tablenames = 'pages'"
                . " AND sys_category_record_mm.fieldname = 'categories' AND sys_category.deleted = 0",
                [$postUid]
            )
            ->fetchFirstColumn();

        $titles = $this->stringList($titles);
        sort($titles, SORT_STRING);

        return $titles;
    }

    /**
     * @return list<string>
     */
    private function fetchTagTitles(int $postUid): array
    {
        $titles = $this->getConnectionPool()
            ->getConnectionForTable('tx_blog_domain_model_tag')
            ->executeQuery(
                'SELECT tag.title FROM tx_blog_domain_model_tag tag'
                . ' JOIN tx_blog_tag_pages_mm mm ON mm.uid_foreign = tag.uid'
                . ' WHERE mm.uid_local = ? AND tag.deleted = 0',
                [$postUid]
            )
            ->fetchFirstColumn();

        $titles = $this->stringList($titles);
        sort($titles, SORT_STRING);

        return $titles;
    }

    private function createCommandTester(): CommandTester
    {
        $command = $this->get(SeedStyleguidePagesCommand::class);
        self::assertInstanceOf(SeedStyleguidePagesCommand::class, $command);

        return new CommandTester($command);
    }


    /**
     * @param array<string, mixed> $row
     */
    private function intValue(array $row, string $key): int
    {
        $value = $row[$key] ?? null;
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function stringValue(array $row, string $key): string
    {
        $value = $row[$key] ?? null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<mixed> $values
     * @return list<string>
     */
    private function stringList(array $values): array
    {
        $strings = [];
        foreach ($values as $value) {
            if (is_string($value)) {
                $strings[] = $value;
            }
        }

        return $strings;
    }

    private function countRows(string $table, string $where = '1=1'): int
    {
        $count = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }
}
