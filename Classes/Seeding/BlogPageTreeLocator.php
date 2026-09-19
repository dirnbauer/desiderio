<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Finds the blog page trees of an installation and the pages inside them.
 *
 * Read-only except for applyBackendLayout(): everything here answers "which
 * pages are we talking about?", which is what the blog seeder command needs on
 * its own for a dry run, before any record is written.
 *
 * Deleted rows are deliberately visible (every query drops the restrictions),
 * because a re-run must recognise and revive what an earlier run soft-deleted.
 */
final readonly class BlogPageTreeLocator
{
    private const array BLOG_LIST_CTYPES = [
        'blog_posts',
        'blog_category',
        'blog_tag',
        'blog_authorposts',
        'blog_archive',
        'blog_demandedposts',
    ];

    public function __construct(
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * @return list<array{rootUid: int, folderUid: int}>
     */
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
            $rootUid = DbRowValues::integer($row, 'pid');
            $folderUid = DbRowValues::integer($row, 'uid');
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

        return DbRowValues::integers($rows);
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

        return DbRowValues::integers($rows);
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

        return DbRowValues::integers($rows);
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

        return DbRowValues::integers($rows);
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
     * @param array<int> $pageUids
     * @return list<int>
     */
    public function normalizePageUids(array $pageUids): array
    {
        $pageUids = array_values(array_unique(array_filter(
            $pageUids,
            static fn(int $pageUid): bool => $pageUid > 0
        )));
        sort($pageUids);

        return $pageUids;
    }
}
