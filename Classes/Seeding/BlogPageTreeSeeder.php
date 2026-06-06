<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Data\BlogDemoPostDefinitions;

final class BlogPageTreeSeeder
{
    public const DEFAULT_BACKEND_LAYOUT = 'pagets__DesiderioBlog';
    private const BLOG_POST_DOKTYPE = 137;

    private const BLOG_LIST_CTYPES = [
        'blog_posts',
        'blog_category',
        'blog_tag',
        'blog_authorposts',
        'blog_archive',
        'blog_demandedposts',
    ];
    private const LEGACY_DEFAULT_TAG_TITLES = ['Accessibility', 'TYPO3'];
    private const REMOVABLE_LEGACY_TAG_TITLES = ['Accessibility'];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    public function findBlogSetups(?int $rootFilter): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $conditions = [
            $queryBuilder->expr()->eq('module', $queryBuilder->createNamedParameter('blog')),
            $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            $queryBuilder->expr()->gt('pid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
        ];

        if ($rootFilter !== null) {
            $conditions[] = $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($rootFilter, ParameterType::INTEGER));
        }

        $rows = $queryBuilder
            ->select('uid', 'pid')
            ->from('pages')
            ->where(...$conditions)
            ->orderBy('pid')
            ->addOrderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $setups = [];
        foreach ($rows as $row) {
            $rootUid = $this->getIntegerRowValue($row, 'pid');
            $folderUid = $this->getIntegerRowValue($row, 'uid');
            if ($rootUid <= 0 || $folderUid <= 0) {
                continue;
            }

            if (isset($setups[$rootUid])) {
                continue;
            }
            $setups[$rootUid] = [
                'rootUid' => $rootUid,
                'folderUid' => $folderUid,
            ];
        }

        return array_values($setups);
    }

    /**
     * @return list<int>
     */
    public function findLayoutPageUids(int $rootUid, int $folderUid): array
    {
        $folderPages = $this->findBlogFolderPageUids($rootUid);
        $rootAndListPages = $this->findRootAndListPageUids($rootUid);
        $postPages = $this->findPostPageUids($folderUid);

        $pageUids = $this->normalizePageUids(array_merge([$rootUid, $folderUid], $folderPages, $rootAndListPages, $postPages));
        return $this->normalizePageUids(array_merge($pageUids, $this->findTranslationPageUids($pageUids)));
    }

    /**
     * @return list<int>
     */
    public function findBlogFolderPageUids(int $rootUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('module', $queryBuilder->createNamedParameter('blog')),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @return list<int>
     */
    public function findRootAndListPageUids(int $rootUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('pages.uid')
            ->from('pages')
            ->join(
                'pages',
                'tt_content',
                'content',
                (string)$queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('content.pid', $queryBuilder->quoteIdentifier('pages.uid')),
                    $queryBuilder->expr()->eq('content.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                    $queryBuilder->expr()->in(
                        'content.CType',
                        $queryBuilder->createNamedParameter(self::BLOG_LIST_CTYPES, ArrayParameterType::STRING)
                    )
                )
            )
            ->where(
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('pages.uid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('pages.pid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER))
                )
            )
            ->groupBy('pages.uid')
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @param list<int> $pageUids
     * @return list<int>
     */
    public function findTranslationPageUids(array $pageUids): array
    {
        if ($pageUids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in(
                    'l10n_parent',
                    $queryBuilder->createNamedParameter($pageUids, ArrayParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @return list<int>
     */
    public function findPostPageUids(int $folderUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @param list<int> $pageUids
     */
    public function applyBackendLayout(array $pageUids, string $layout): int
    {
        if ($pageUids === []) {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->update('pages')
            ->set('backend_layout', $layout)
            ->set('backend_layout_next_level', $layout)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($pageUids, ArrayParameterType::INTEGER)
                )
            )
            ->executeStatement();
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
            $this->ensureComment($postUid);

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
                static fn (int $tagUid): bool => $tagUid > 0
            )),
            $authorUid
        );
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

        if (is_numeric($uid)) {
            return (int)$uid;
        }

        $now = time();
        $connection = $this->connectionPool->getConnectionForTable('sys_category');
        $connection->insert('sys_category', [
            'pid' => $folderUid,
            'tstamp' => $now,
            'crdate' => $now,
            'title' => $title,
            'parent' => 0,
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
            'email' => 'team@webconsulting.at',
            'location' => 'Austria',
            'twitter' => '',
            'linkedin' => '',
            'xing' => '',
            'instagram' => '',
            'profile' => '',
            'bio' => 'TYPO3 implementation team maintaining the Desiderio shadcn Blog templates.',
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
            'author_email' => 'team@webconsulting.at',
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

    public function ensureComment(int $postUid): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_blog_domain_model_comment');
        $queryBuilder->getRestrictions()->removeAll();

        $uid = $queryBuilder
            ->select('uid')
            ->from('tx_blog_domain_model_comment')
            ->where(
                $queryBuilder->expr()->eq('parentid', $queryBuilder->createNamedParameter($postUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('parenttable', $queryBuilder->createNamedParameter('pages')),
                $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter('team@webconsulting.at')),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        $data = [
            'tstamp' => $now,
            'name' => 'Webconsulting QA',
            'url' => 'https://webconsulting.at/',
            'email' => 'team@webconsulting.at',
            'comment' => 'Seeded comment for checking the Blog comment counter, comment list, and shadcn metadata links.',
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
            ->select('uid', 'title', 'subtitle', 'abstract', 'description', 'publish_date', 'categories', 'tags', 'authors')
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
            $postUid = $this->getIntegerRowValue($row, 'uid');
            if ($postUid <= 0) {
                continue;
            }

            $title = $this->nonEmptyString($row['title'] ?? null, 'Blog post');
            $description = $this->nonEmptyString($row['description'] ?? null, '');
            if ($description === '') {
                $description = sprintf('Seeded fallback metadata for "%s" so the shadcn Blog list and detail templates can show a complete article preview.', $title);
            }

            $publishDate = $this->getIntegerRowValue($row, 'publish_date');
            if ($publishDate <= 0) {
                $publishDate = $now - (($index + 1) * 86400);
            }

            $this->connectionPool->getConnectionForTable('pages')->update('pages', [
                'tstamp' => $now,
                'SYS_LASTCHANGED' => $now,
                'subtitle' => $this->nonEmptyString($row['subtitle'] ?? null, 'Blog template coverage entry'),
                'abstract' => $this->nonEmptyString($row['abstract'] ?? null, 'Complete sample metadata for the Desiderio Blog list template.'),
                'description' => $description,
                'seo_title' => $title,
                'og_title' => $title,
                'og_description' => $description,
                'twitter_title' => $title,
                'twitter_description' => $description,
                'publish_date' => $publishDate,
                'crdate_month' => (int)date('n', $publishDate),
                'crdate_year' => (int)date('Y', $publishDate),
                'comments_active' => 1,
                'backend_layout' => $layout,
                'backend_layout_next_level' => $layout,
            ], ['uid' => $postUid]);

            if ($defaultCategoryUid > 0 && $this->getIntegerRowValue($row, 'categories') <= 0) {
                $this->replaceCategoryRelations($postUid, [$defaultCategoryUid]);
            }
            $hasLegacyDefaultTags = $this->isSameTitleSet($this->findPostTagTitles($postUid), self::LEGACY_DEFAULT_TAG_TITLES);
            if ($defaultTagUids !== [] && ($this->getIntegerRowValue($row, 'tags') <= 0 || $hasLegacyDefaultTags)) {
                $this->replaceTagRelations($postUid, $defaultTagUids);
            }
            if ($authorUid > 0 && $this->getIntegerRowValue($row, 'authors') <= 0) {
                $this->replaceAuthorRelations($postUid, [$authorUid]);
            }

            $this->ensureComment($postUid);
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

            $tagUids = $this->mapIntegerColumn($queryBuilder
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

    public function nonEmptyString(mixed $value, string $fallback): string
    {
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        return $fallback;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function getIntegerRowValue(array $row, string $key): int
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
     * @param array<mixed> $values
     * @return list<int>
     */
    public function mapIntegerColumn(array $values): array
    {
        $integers = [];
        foreach ($values as $value) {
            if (is_int($value)) {
                $integers[] = $value;
                continue;
            }

            if (is_string($value) && is_numeric($value)) {
                $integers[] = (int)$value;
            }
        }

        return $integers;
    }

    /**
     * @param array<int> $pageUids
     * @return list<int>
     */
    public function normalizePageUids(array $pageUids): array
    {
        $pageUids = array_values(array_unique(array_filter(
            $pageUids,
            static fn (int $pageUid): bool => $pageUid > 0
        )));
        sort($pageUids);

        return $pageUids;
    }
}
