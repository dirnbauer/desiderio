<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Data\BlogDemoPostDefinitions;

final readonly class BlogPageTreeSeeder
{
    /** "Desiderio: Blog – Classic": a journal with a sidebar. */
    public const string CLASSIC_BACKEND_LAYOUT = 'pagets__DesiderioBlogClassic';

    /** "Desiderio: Blog – Modern": a magazine with a card grid. */
    public const string MODERN_BACKEND_LAYOUT = 'pagets__DesiderioBlogModern';

    /**
     * The single blog template before Classic and Modern ("Blog (previous
     * version)"). Still rendered for pages that use it, never seeded.
     */
    public const string PREVIOUS_BACKEND_LAYOUT = 'pagets__DesiderioBlog';

    /** What a blog gets when nobody chose a template for it. */
    public const string DEFAULT_BACKEND_LAYOUT = self::CLASSIC_BACKEND_LAYOUT;

    public const int BLOG_POST_DOKTYPE = 137;

    /**
     * EXT:blog types sys_category records (Constants::CATEGORY_TYPE_BLOG) and
     * its repositories only see categories of this record_type.
     */
    public const int BLOG_CATEGORY_RECORD_TYPE = 100;

    private const array LEGACY_DEFAULT_TAG_TITLES = ['Accessibility', 'TYPO3'];
    /**
     * Tags the seeder removes once no live post uses them: an old default and
     * the tags of the retired lifestyle demo posts.
     */
    private const array REMOVABLE_LEGACY_TAG_TITLES = ['Accessibility', 'Easter', 'Spring', 'Connection', 'Family', 'Decor'];

    /** Where the demo posts' featured images land in the default storage. */
    public const string FAL_FOLDER = 'desiderio-blog';

    /**
     * @param ExtensionFalSeeder|null $falSeeder Imports the demo posts' featured
     *        images; without one the posts are seeded without pictures.
     */
    public function __construct(
        private ConnectionPool $connectionPool,
        private ?ExtensionFalSeeder $falSeeder = null,
    ) {}

    /**
     * The two blog page templates a blog can choose.
     *
     * @return list<string>
     */
    public static function templateLayouts(): array
    {
        return [self::CLASSIC_BACKEND_LAYOUT, self::MODERN_BACKEND_LAYOUT];
    }

    /**
     * `classic` and `modern` name the two templates; anything else is taken
     * as a backend layout identifier as it is.
     */
    public static function resolveLayout(string $layout): string
    {
        return match (strtolower(trim($layout))) {
            'classic' => self::CLASSIC_BACKEND_LAYOUT,
            'modern' => self::MODERN_BACKEND_LAYOUT,
            default => trim($layout),
        };
    }

    /**
     * The blog template a blog root already uses (for its subpages, or for
     * itself), or null when it has none of the two — no layout yet, or the
     * previous single blog template.
     */
    public function currentTemplateLayout(int $rootUid): ?string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $row = $queryBuilder
            ->select('backend_layout', 'backend_layout_next_level')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchAssociative();
        if (!is_array($row)) {
            return null;
        }
        foreach (['backend_layout_next_level', 'backend_layout'] as $field) {
            $value = $row[$field] ?? null;
            if (is_string($value) && in_array($value, self::templateLayouts(), true)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @return array{posts: int, contentElements: int}
     */
    public function seedDemoContent(int $folderUid, string $layout): array
    {
        $categoryTitles = ['Blog', 'TYPO3'];
        $tagTitles = ['TYPO3', 'shadcn UI', 'Fluid'];
        foreach (BlogDemoPostDefinitions::demoPosts() as $post) {
            $categoryTitles = array_merge($categoryTitles, $post['categories']);
            $tagTitles = array_merge($tagTitles, $post['tags']);
        }

        $categoryUids = $this->ensureCategories($folderUid, array_values(array_unique($categoryTitles)));
        $tagUids = $this->ensureTags($folderUid, array_values(array_unique($tagTitles)));
        $authorUid = $this->ensureAuthor($folderUid);

        $seededPostUids = [];
        $contentElements = 0;
        foreach (BlogDemoPostDefinitions::demoPosts() as $index => $post) {
            $postUid = $this->ensureDemoPost($folderUid, $layout, $post, $index);
            if ($postUid <= 0) {
                continue;
            }

            $seededPostUids[] = $postUid;
            $this->replacePostRelations(
                $postUid,
                $this->mapTitlesToUids($post['categories'], $categoryUids),
                $this->mapTitlesToUids($post['tags'], $tagUids),
                $authorUid
            );
            $this->ensureComment($postUid, $post['comment']);
            $this->ensureFeaturedImage($postUid, $folderUid, $post['title'], $post['image']);

            foreach ($post['content'] as $contentIndex => $content) {
                if ($this->ensureContentElement($postUid, $content['header'], $content['body'], ($contentIndex + 1) * 100)) {
                    ++$contentElements;
                }
            }
        }

        $this->fillExistingPostDefaults(
            $folderUid,
            $layout,
            $categoryUids['Blog'] ?? 0,
            array_values(array_filter(
                [$tagUids['TYPO3'] ?? 0, $tagUids['shadcn UI'] ?? 0],
                static fn(int $tagUid): bool => $tagUid > 0
            )),
            $authorUid
        );
        // Retire first, so the retired posts no longer count as using a tag.
        $this->retireDemoPosts($folderUid);
        $this->removeLegacyExampleComments($folderUid);
        $this->removeOrphanedLegacyTags($folderUid);

        return [
            'posts' => count(array_unique($seededPostUids)),
            'contentElements' => $contentElements,
        ];
    }

    /**
     * @param list<string> $titles
     * @return array<string, int>
     */
    public function ensureCategories(int $folderUid, array $titles): array
    {
        $uids = [];
        foreach ($titles as $title) {
            $uids[$title] = $this->ensureCategory($folderUid, $title);
        }

        return $uids;
    }

    public function ensureCategory(int $folderUid, string $title): int
    {
        $slug = $this->slugify($title);
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $queryBuilder->getRestrictions()->removeAll();

        $uid = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        $connection = $this->connectionPool->getConnectionForTable('sys_category');
        if (is_numeric($uid)) {
            // Heal categories created without the blog record type or slug —
            // EXT:blog repositories and the category route enhancer would not
            // work with them otherwise.
            $connection->update('sys_category', [
                'tstamp' => $now,
                'record_type' => self::BLOG_CATEGORY_RECORD_TYPE,
                'slug' => $slug,
            ], ['uid' => (int)$uid]);
            return (int)$uid;
        }

        $connection->insert('sys_category', [
            'pid' => $folderUid,
            'tstamp' => $now,
            'crdate' => $now,
            'title' => $title,
            'parent' => 0,
            'record_type' => self::BLOG_CATEGORY_RECORD_TYPE,
            'slug' => $slug,
            'sorting' => $this->nextSorting('sys_category', $folderUid),
        ]);

        return (int)$connection->lastInsertId();
    }

    /**
     * @param list<string> $titles
     * @return array<string, int>
     */
    public function ensureTags(int $folderUid, array $titles): array
    {
        $uids = [];
        foreach ($titles as $title) {
            $uids[$title] = $this->ensureTag($folderUid, $title);
        }

        return $uids;
    }

    public function ensureTag(int $folderUid, string $title): int
    {
        $slug = $this->slugify($title);
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_blog_domain_model_tag');
        $queryBuilder->getRestrictions()->removeAll();

        $uid = $queryBuilder
            ->select('uid')
            ->from('tx_blog_domain_model_tag')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title)),
                    $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug))
                ),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        $connection = $this->connectionPool->getConnectionForTable('tx_blog_domain_model_tag');
        if (is_numeric($uid)) {
            $connection->update('tx_blog_domain_model_tag', [
                'tstamp' => $now,
                'title' => $title,
                'slug' => $slug,
            ], ['uid' => (int)$uid]);
            return (int)$uid;
        }

        $connection->insert('tx_blog_domain_model_tag', [
            'pid' => $folderUid,
            'tstamp' => $now,
            'crdate' => $now,
            'title' => $title,
            'slug' => $slug,
        ]);

        return (int)$connection->lastInsertId();
    }

    public function ensureAuthor(int $folderUid): int
    {
        $slug = 'webconsulting-typo3-team';
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_blog_domain_model_author');
        $queryBuilder->getRestrictions()->removeAll();

        $uid = $queryBuilder
            ->select('uid')
            ->from('tx_blog_domain_model_author')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        $data = [
            'tstamp' => $now,
            'name' => 'Webconsulting TYPO3 Team',
            'slug' => $slug,
            'title' => 'TYPO3 integrators',
            'website' => 'https://webconsulting.at/',
            'email' => 'office@webconsulting.at',
            'location' => 'Austria',
            'twitter' => '',
            'linkedin' => '',
            'xing' => '',
            'instagram' => '',
            'profile' => '',
            'bio' => 'The TYPO3 team at webconsulting maintains the Desiderio blog templates.',
            'posts' => '',
            'details_page' => 0,
        ];

        $connection = $this->connectionPool->getConnectionForTable('tx_blog_domain_model_author');
        if (is_numeric($uid)) {
            $connection->update('tx_blog_domain_model_author', $data, ['uid' => (int)$uid]);
            return (int)$uid;
        }

        $connection->insert('tx_blog_domain_model_author', array_merge($data, [
            'pid' => $folderUid,
            'crdate' => $now,
        ]));

        return (int)$connection->lastInsertId();
    }

    /**
     * @param array{
     *   slug: string,
     *   title: string,
     *   subtitle: string,
     *   abstract: string,
     *   description: string,
     *   date: string,
     *   categories: list<string>,
     *   tags: list<string>,
     *   content: list<array{header: string, body: string}>
     * } $post
     */
    public function ensureDemoPost(int $folderUid, string $layout, array $post, int $index): int
    {
        $postUid = $this->findPostBySlugOrTitle($folderUid, $post['slug'], $post['title']);
        $publishDate = $this->timestampFromDate($post['date']);
        $now = time();
        $data = [
            'tstamp' => $now,
            'SYS_LASTCHANGED' => $now,
            'doktype' => self::BLOG_POST_DOKTYPE,
            'title' => $post['title'],
            'slug' => $post['slug'],
            'subtitle' => $post['subtitle'],
            'abstract' => $post['abstract'],
            'description' => $post['description'],
            'seo_title' => $post['title'],
            'og_title' => $post['title'],
            'og_description' => $post['description'],
            'twitter_title' => $post['title'],
            'twitter_description' => $post['description'],
            'publish_date' => $publishDate,
            'archive_date' => 0,
            'crdate_month' => (int)date('n', $publishDate),
            'crdate_year' => (int)date('Y', $publishDate),
            'comments_active' => 1,
            'backend_layout' => $layout,
            'backend_layout_next_level' => $layout,
            'hidden' => 0,
            'deleted' => 0,
            'no_index' => 0,
            'no_follow' => 0,
            'author' => 'Webconsulting TYPO3 Team',
            'author_email' => 'office@webconsulting.at',
        ];

        $connection = $this->connectionPool->getConnectionForTable('pages');
        if ($postUid > 0) {
            $connection->update('pages', $data, ['uid' => $postUid]);
            return $postUid;
        }

        $connection->insert('pages', array_merge($data, [
            'pid' => $folderUid,
            'crdate' => $publishDate,
            'sorting' => 1000 + ($index * 100),
            'sys_language_uid' => 0,
            'l10n_parent' => 0,
            'perms_userid' => 1,
            'perms_groupid' => 0,
            'perms_user' => 31,
            'perms_group' => 27,
            'perms_everybody' => 0,
        ]));

        return (int)$connection->lastInsertId();
    }

    public function findPostBySlugOrTitle(int $folderUid, string $slug, string $title): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $uid = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
                    $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title))
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return is_numeric($uid) ? (int)$uid : 0;
    }

    /**
     * @param array<string, int> $uidsByTitle
     * @param list<string> $titles
     * @return list<int>
     */
    public function mapTitlesToUids(array $titles, array $uidsByTitle): array
    {
        $uids = [];
        foreach ($titles as $title) {
            $uid = $uidsByTitle[$title] ?? 0;
            if ($uid > 0) {
                $uids[] = $uid;
            }
        }

        return array_values(array_unique($uids));
    }

    /**
     * @param list<int> $categoryUids
     * @param list<int> $tagUids
     */
    public function replacePostRelations(int $postUid, array $categoryUids, array $tagUids, int $authorUid): void
    {
        $this->replaceCategoryRelations($postUid, $categoryUids);
        $this->replaceTagRelations($postUid, $tagUids);
        $this->replaceAuthorRelations($postUid, $authorUid > 0 ? [$authorUid] : []);
    }

    /**
     * @param list<int> $categoryUids
     */
    public function replaceCategoryRelations(int $postUid, array $categoryUids): void
    {
        $connection = $this->connectionPool->getConnectionForTable('sys_category_record_mm');
        $connection->delete('sys_category_record_mm', [
            'uid_foreign' => $postUid,
            'tablenames' => 'pages',
            'fieldname' => 'categories',
        ]);

        foreach (array_values(array_unique($categoryUids)) as $index => $categoryUid) {
            $connection->insert('sys_category_record_mm', [
                'uid_local' => $categoryUid,
                'uid_foreign' => $postUid,
                'tablenames' => 'pages',
                'fieldname' => 'categories',
                'sorting' => $index,
            ]);
        }

        $this->connectionPool->getConnectionForTable('pages')->update('pages', [
            'categories' => count($categoryUids),
        ], ['uid' => $postUid]);
    }

    /**
     * @param list<int> $tagUids
     */
    public function replaceTagRelations(int $postUid, array $tagUids): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_blog_tag_pages_mm');
        $connection->delete('tx_blog_tag_pages_mm', ['uid_local' => $postUid]);

        foreach (array_values(array_unique($tagUids)) as $index => $tagUid) {
            $connection->insert('tx_blog_tag_pages_mm', [
                'uid_local' => $postUid,
                'uid_foreign' => $tagUid,
                'sorting' => $index + 1,
                'sorting_foreign' => 0,
            ]);
        }

        $this->connectionPool->getConnectionForTable('pages')->update('pages', [
            'tags' => count($tagUids),
        ], ['uid' => $postUid]);
    }

    /**
     * @param list<int> $authorUids
     */
    public function replaceAuthorRelations(int $postUid, array $authorUids): void
    {
        $connection = $this->connectionPool->getConnectionForTable('tx_blog_post_author_mm');
        $connection->delete('tx_blog_post_author_mm', ['uid_local' => $postUid]);

        foreach (array_values(array_unique($authorUids)) as $index => $authorUid) {
            $connection->insert('tx_blog_post_author_mm', [
                'uid_local' => $postUid,
                'uid_foreign' => $authorUid,
                'sorting' => $index + 1,
                'sorting_foreign' => 0,
            ]);
        }

        $this->connectionPool->getConnectionForTable('pages')->update('pages', [
            'authors' => count($authorUids),
        ], ['uid' => $postUid]);
    }

    /**
     * One comment per demo post, each with its own author and text: the
     * "Recent comments" widget lists them side by side.
     *
     * @param array{name: string, email: string, text: string} $comment
     */
    public function ensureComment(int $postUid, array $comment): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_blog_domain_model_comment');
        $queryBuilder->getRestrictions()->removeAll();

        $uid = $queryBuilder
            ->select('uid')
            ->from('tx_blog_domain_model_comment')
            ->where(
                $queryBuilder->expr()->eq('parentid', $queryBuilder->createNamedParameter($postUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('parenttable', $queryBuilder->createNamedParameter('pages')),
                // Earlier seeds signed the one shared demo comment with an
                // office address; finding it by those too turns it into this
                // post's own comment instead of adding a second one.
                $queryBuilder->expr()->in('email', $queryBuilder->createNamedParameter(
                    [$comment['email'], 'office@webconsulting.at', 'team@webconsulting.at'],
                    ArrayParameterType::STRING
                )),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        $data = [
            'tstamp' => $now,
            'name' => $comment['name'],
            'url' => '',
            'email' => $comment['email'],
            'comment' => $comment['text'],
            'parentid' => $postUid,
            'parenttable' => 'pages',
            'post_language_id' => 0,
            'hp' => '',
            'status' => 10,
        ];

        $connection = $this->connectionPool->getConnectionForTable('tx_blog_domain_model_comment');
        if (is_numeric($uid)) {
            $connection->update('tx_blog_domain_model_comment', $data, ['uid' => (int)$uid]);
            return;
        }

        $connection->insert('tx_blog_domain_model_comment', array_merge($data, [
            'pid' => $postUid,
            'crdate' => $now,
        ]));
    }

    /**
     * Gives a demo post its picture when it has none. A picture an editor
     * chose stays: the seeder only fills the gap, so every card of the Modern
     * template's grid has an image.
     *
     * @param array{file: string, alternative: string} $image
     */
    public function ensureFeaturedImage(int $postUid, int $folderUid, string $title, array $image): void
    {
        if (!$this->falSeeder instanceof ExtensionFalSeeder) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->getRestrictions()->removeAll();
        $existing = (int)$queryBuilder
            ->count('uid')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter('pages')),
                $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter('featured_image')),
                $queryBuilder->expr()->eq('uid_foreign', $queryBuilder->createNamedParameter($postUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchOne();
        if ($existing > 0) {
            return;
        }

        $now = time();
        $this->falSeeder->seedFileReferences('pages', $postUid, $folderUid, $now, [
            'featured_image' => [[
                'file' => $image['file'],
                'title' => $title,
                'alternative' => $image['alternative'],
                'description' => 'Illustrative photo, generated.',
                'source' => '',
            ]],
        ]);
        $this->connectionPool->getConnectionForTable('pages')->update('pages', ['featured_image' => 1, 'tstamp' => $now], ['uid' => $postUid]);
    }

    /**
     * Soft-deletes the demo posts earlier versions seeded and the definitions
     * no longer have, with their content elements, comments and file references.
     */
    public function retireDemoPosts(int $folderUid): void
    {
        // Retired earlier or not: what is still live of such a post goes now.
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $postUids = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->in('slug', $queryBuilder->createNamedParameter(BlogDemoPostDefinitions::retiredSlugs(), ArrayParameterType::STRING))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        $now = time();
        $live = ['deleted' => 0];
        foreach (array_map('intval', $postUids) as $postUid) {
            $this->softDeleteFileReferences('pages', $this->uidsWhere('pages', 'l10n_parent', $postUid, [$postUid]), $now);
            $this->softDeleteFileReferences('tt_content', $this->uidsWhere('tt_content', 'pid', $postUid), $now);
            // The post and its translations.
            $this->connectionPool->getConnectionForTable('pages')->update('pages', ['deleted' => 1, 'tstamp' => $now], ['uid' => $postUid] + $live);
            $this->connectionPool->getConnectionForTable('pages')->update('pages', ['deleted' => 1, 'tstamp' => $now], ['l10n_parent' => $postUid] + $live);
            $this->connectionPool->getConnectionForTable('tt_content')->update('tt_content', ['deleted' => 1, 'tstamp' => $now], ['pid' => $postUid] + $live);
            $this->connectionPool->getConnectionForTable('tx_blog_domain_model_comment')->update(
                'tx_blog_domain_model_comment',
                ['deleted' => 1, 'tstamp' => $now],
                ['parentid' => $postUid, 'parenttable' => 'pages'] + $live
            );
        }
    }

    /**
     * Uids of a table where a column has a value, deleted or not, plus the
     * given extra uids.
     *
     * @param list<int> $extra
     * @return list<int>
     */
    private function uidsWhere(string $table, string $column, int $value, array $extra = []): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $uids = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where($queryBuilder->expr()->eq($column, $queryBuilder->createNamedParameter($value, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchFirstColumn();

        return array_values(array_unique([...$extra, ...array_map('intval', $uids)]));
    }

    /**
     * @param list<int> $foreignUids
     */
    private function softDeleteFileReferences(string $table, array $foreignUids, int $now): void
    {
        if ($foreignUids === []) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder
            ->update('sys_file_reference')
            ->set('deleted', 1)
            ->set('tstamp', $now)
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($table)),
                $queryBuilder->expr()->in('uid_foreign', $queryBuilder->createNamedParameter($foreignUids, ArrayParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeStatement();
    }

    /**
     * Soft-deletes the shared example comment earlier versions left on every
     * post of this blog — on an editor's post too, where it never belonged.
     */
    public function removeLegacyExampleComments(int $folderUid): void
    {
        $pages = $this->connectionPool->getQueryBuilderForTable('pages');
        $pages->getRestrictions()->removeAll();
        $postUids = array_map('intval', $pages
            ->select('uid')
            ->from('pages')
            ->where($pages->expr()->eq('pid', $pages->createNamedParameter($folderUid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchFirstColumn());
        if ($postUids === []) {
            return;
        }

        $comments = $this->connectionPool->getQueryBuilderForTable('tx_blog_domain_model_comment');
        $comments->getRestrictions()->removeAll();
        $comments
            ->update('tx_blog_domain_model_comment')
            ->set('deleted', 1)
            ->set('tstamp', time())
            ->where(
                $comments->expr()->in('parentid', $comments->createNamedParameter($postUids, ArrayParameterType::INTEGER)),
                $comments->expr()->eq('parenttable', $comments->createNamedParameter('pages')),
                $comments->expr()->eq('comment', $comments->createNamedParameter(BlogDemoPostDefinitions::LEGACY_EXAMPLE_COMMENT)),
                $comments->expr()->eq('deleted', $comments->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeStatement();
    }

    public function ensureContentElement(int $postUid, string $header, string $bodytext, int $sorting): bool
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        $uid = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($postUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('header', $queryBuilder->createNamedParameter($header)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        $data = [
            'tstamp' => $now,
            'CType' => 'text',
            'header' => $header,
            'bodytext' => $bodytext,
            'sorting' => $sorting,
            'colPos' => 0,
            'hidden' => 0,
        ];

        $connection = $this->connectionPool->getConnectionForTable('tt_content');
        if (is_numeric($uid)) {
            $connection->update('tt_content', $data, ['uid' => (int)$uid]);
            return true;
        }

        $connection->insert('tt_content', array_merge($data, [
            'pid' => $postUid,
            'crdate' => $now,
            'sys_language_uid' => 0,
        ]));

        return true;
    }

    /**
     * @param list<int> $defaultTagUids
     */
    public function fillExistingPostDefaults(int $folderUid, string $layout, int $defaultCategoryUid, array $defaultTagUids, int $authorUid): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid', 'title', 'subtitle', 'abstract', 'description', 'seo_title', 'og_title', 'og_description', 'twitter_title', 'twitter_description', 'publish_date', 'categories', 'tags', 'authors')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('doktype', $queryBuilder->createNamedParameter(self::BLOG_POST_DOKTYPE, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('publish_date', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();

        $now = time();
        foreach ($rows as $index => $row) {
            $postUid = DbRowValues::integer($row, 'uid');
            if ($postUid <= 0) {
                continue;
            }

            $title = DbRowValues::nonEmptyString($row['title'] ?? null, 'Blog post');
            $description = DbRowValues::nonEmptyString($row['description'] ?? null, '');
            if ($description === '') {
                $description = sprintf('Sample description for "%s". Replace it in the page properties.', $title);
            }

            $publishDate = DbRowValues::integer($row, 'publish_date');
            if ($publishDate <= 0) {
                $publishDate = $now - (($index + 1) * 86400);
            }

            // Only empty fields are filled: an editor's (or a content
            // payload's) summary and SEO texts stay as they are.
            $this->connectionPool->getConnectionForTable('pages')->update('pages', [
                'tstamp' => $now,
                'SYS_LASTCHANGED' => $now,
                'subtitle' => DbRowValues::nonEmptyString($row['subtitle'] ?? null, ''),
                'abstract' => DbRowValues::nonEmptyString($row['abstract'] ?? null, 'Sample summary for the blog list. Replace it in the page properties.'),
                'description' => $description,
                'seo_title' => DbRowValues::nonEmptyString($row['seo_title'] ?? null, $title),
                'og_title' => DbRowValues::nonEmptyString($row['og_title'] ?? null, $title),
                'og_description' => DbRowValues::nonEmptyString($row['og_description'] ?? null, $description),
                'twitter_title' => DbRowValues::nonEmptyString($row['twitter_title'] ?? null, $title),
                'twitter_description' => DbRowValues::nonEmptyString($row['twitter_description'] ?? null, $description),
                'publish_date' => $publishDate,
                'crdate_month' => (int)date('n', $publishDate),
                'crdate_year' => (int)date('Y', $publishDate),
                'comments_active' => 1,
                'backend_layout' => $layout,
                'backend_layout_next_level' => $layout,
            ], ['uid' => $postUid]);

            if ($defaultCategoryUid > 0 && DbRowValues::integer($row, 'categories') <= 0) {
                $this->replaceCategoryRelations($postUid, [$defaultCategoryUid]);
            }
            $hasLegacyDefaultTags = $this->isSameTitleSet($this->findPostTagTitles($postUid), self::LEGACY_DEFAULT_TAG_TITLES);
            if ($defaultTagUids !== [] && (DbRowValues::integer($row, 'tags') <= 0 || $hasLegacyDefaultTags)) {
                $this->replaceTagRelations($postUid, $defaultTagUids);
            }
            if ($authorUid > 0 && DbRowValues::integer($row, 'authors') <= 0) {
                $this->replaceAuthorRelations($postUid, [$authorUid]);
            }
            // No example comment here: seedDemoContent() gives one to its own
            // demo posts, and an editor's post should not get a made-up one.
        }
    }

    /**
     * @return list<string>
     */
    public function findPostTagTitles(int $postUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_blog_tag_pages_mm');
        $queryBuilder->getRestrictions()->removeAll();

        $titles = $queryBuilder
            ->select('tag.title')
            ->from('tx_blog_tag_pages_mm', 'relation')
            ->join(
                'relation',
                'tx_blog_domain_model_tag',
                'tag',
                (string)$queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('tag.uid', $queryBuilder->quoteIdentifier('relation.uid_foreign')),
                    $queryBuilder->expr()->eq('tag.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
                )
            )
            ->where($queryBuilder->expr()->eq('relation.uid_local', $queryBuilder->createNamedParameter($postUid, ParameterType::INTEGER)))
            ->orderBy('relation.sorting')
            ->executeQuery()
            ->fetchFirstColumn();

        $tagTitles = [];
        foreach ($titles as $title) {
            if (is_string($title) && trim($title) !== '') {
                $tagTitles[] = trim($title);
            }
        }

        return $tagTitles;
    }

    /**
     * @param list<string> $left
     * @param list<string> $right
     */
    public function isSameTitleSet(array $left, array $right): bool
    {
        $left = array_values(array_unique($left));
        $right = array_values(array_unique($right));
        sort($left, SORT_STRING);
        sort($right, SORT_STRING);

        return $left === $right;
    }

    public function removeOrphanedLegacyTags(int $folderUid): void
    {
        foreach (self::REMOVABLE_LEGACY_TAG_TITLES as $tagTitle) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_blog_domain_model_tag');
            $queryBuilder->getRestrictions()->removeAll();

            $tagUids = DbRowValues::integers($queryBuilder
                ->select('uid')
                ->from('tx_blog_domain_model_tag')
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($tagTitle)),
                    $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
                )
                ->executeQuery()
                ->fetchFirstColumn());

            foreach ($tagUids as $tagUid) {
                if ($this->countActiveTagRelations($tagUid) > 0) {
                    continue;
                }

                $this->connectionPool->getConnectionForTable('tx_blog_domain_model_tag')->update('tx_blog_domain_model_tag', [
                    'tstamp' => time(),
                    'deleted' => 1,
                ], ['uid' => $tagUid]);
            }
        }
    }

    public function countActiveTagRelations(int $tagUid): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_blog_tag_pages_mm');
        $queryBuilder->getRestrictions()->removeAll();

        $count = $queryBuilder
            ->count('*')
            ->from('tx_blog_tag_pages_mm', 'relation')
            ->join(
                'relation',
                'pages',
                'post',
                (string)$queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('post.uid', $queryBuilder->quoteIdentifier('relation.uid_local')),
                    $queryBuilder->expr()->eq('post.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
                )
            )
            ->where($queryBuilder->expr()->eq('relation.uid_foreign', $queryBuilder->createNamedParameter($tagUid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }

    public function nextSorting(string $table, int $pid): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $sorting = $queryBuilder
            ->selectLiteral('MAX(sorting)')
            ->from($table)
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchOne();

        return (is_numeric($sorting) ? (int)$sorting : 0) + 100;
    }

    public function timestampFromDate(string $date): int
    {
        $dateTime = new \DateTimeImmutable($date, new \DateTimeZone('Europe/Vienna'));
        return $dateTime->getTimestamp();
    }

    public function slugify(string $value): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if (!is_string($ascii) || $ascii === '') {
            $ascii = $value;
        }

        $slug = strtolower((string)preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii));
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'item';
    }

}
