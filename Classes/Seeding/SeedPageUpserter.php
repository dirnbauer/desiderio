<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

/**
 * Shared find-or-create/update logic for seeded pages. Matching is done by
 * parent + (title or slug) on live, default-language rows only.
 */
final readonly class SeedPageUpserter
{
    public function __construct(
        private ConnectionPool $connectionPool,
        private DatabaseSchemaHelper $databaseSchema,
        private LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper,
    ) {}

    /**
     * @param array<string, true> $columns
     */
    public function findExistingPageUid(int $parentPid, string $title, string $slug, array $columns): ?int
    {
        $where = [
            'pid = :parentPid',
            'deleted = 0',
            '(title = :title OR slug = :slug)',
        ];
        $parameters = [
            'parentPid' => $parentPid,
            'title' => $title,
            'slug' => $slug,
        ];
        $types = [
            'parentPid' => ParameterType::INTEGER,
            'title' => ParameterType::STRING,
            'slug' => ParameterType::STRING,
        ];

        if (isset($columns['sys_language_uid'])) {
            $where[] = 'sys_language_uid = :languageUid';
            $parameters['languageUid'] = 0;
            $types['languageUid'] = ParameterType::INTEGER;
        }
        if (isset($columns['t3ver_wsid'])) {
            $where[] = 't3ver_wsid = :workspaceId';
            $parameters['workspaceId'] = 0;
            $types['workspaceId'] = ParameterType::INTEGER;
        }
        if (isset($columns['t3ver_oid'])) {
            $where[] = 't3ver_oid = :workspaceOriginalUid';
            $parameters['workspaceOriginalUid'] = 0;
            $types['workspaceOriginalUid'] = ParameterType::INTEGER;
        }

        $existingUid = $this->connectionPool
            ->getConnectionForTable('pages')
            ->executeQuery(
                'SELECT uid FROM pages WHERE ' . implode(' AND ', $where) . ' ORDER BY hidden ASC, uid DESC LIMIT 1',
                $parameters,
                $types
            )
            ->fetchOne();

        return is_numeric($existingUid) ? (int)$existingUid : null;
    }

    /**
     * A live, default-language page with this slug anywhere below the root.
     *
     * A page keeps its slug when the menu moves it to another parent (the
     * themes page below Product, the Powermail Lab below Resources), so the
     * seeders find it there instead of creating it again at its old place.
     *
     * @param array<string, true> $columns
     */
    public function findPageBySlugBelow(int $rootPid, string $slug, array $columns): ?int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $constraints = [
            $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
            $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, 'pages'),
        ];
        if (isset($columns['sys_language_uid'])) {
            $constraints[] = $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER));
        }
        $rows = $queryBuilder
            ->select('uid', 'pid')
            ->from('pages')
            ->where(...$constraints)
            ->orderBy('hidden')
            ->addOrderBy('uid', 'DESC')
            ->executeQuery()
            ->fetchAllAssociative();
        foreach ($rows as $row) {
            $uid = is_numeric($row['uid'] ?? null) ? (int)$row['uid'] : 0;
            $pid = is_numeric($row['pid'] ?? null) ? (int)$row['pid'] : 0;
            if ($uid > 0 && $this->isPageBelow($pid, $rootPid)) {
                return $uid;
            }
        }

        return null;
    }

    /**
     * Moves a page and its translations to another parent. The uids stay, so
     * the content, the translations and every link to the page stay too.
     *
     * @param array<string, true> $columns
     */
    public function move(int $pageUid, int $parentPid, int $sorting, int $now, array $columns): void
    {
        $connection = $this->connectionPool->getConnectionForTable('pages');
        $connection->update(
            'pages',
            $this->databaseSchema->filterRow(['pid' => $parentPid, 'sorting' => $sorting, 'tstamp' => $now], $columns),
            ['uid' => $pageUid]
        );
        if (isset($columns['l10n_parent'])) {
            $connection->update(
                'pages',
                $this->databaseSchema->filterRow(['pid' => $parentPid, 'sorting' => $sorting, 'tstamp' => $now], $columns),
                ['l10n_parent' => $pageUid]
            );
        }
    }

    /**
     * Gives the translations of a renamed page its new slug, where they still
     * carry the old one (the translation seeder copies the default slug).
     *
     * @param array<string, true> $columns
     */
    public function renameTranslationSlugs(int $pageUid, string $formerSlug, string $slug, array $columns): void
    {
        if (!isset($columns['l10n_parent'])) {
            return;
        }
        $this->connectionPool->getConnectionForTable('pages')->update(
            'pages',
            ['slug' => $slug],
            ['l10n_parent' => $pageUid, 'slug' => $formerSlug]
        );
    }

    /**
     * Gives the translations of a page the page template just written to it.
     * A translation keeps its own copy of backend_layout, and that copy is
     * what its language renders: the success stories moved to Blog – Classic
     * in English and stayed on the previous blog template in German, Chinese
     * and Hungarian.
     *
     * @param array<string, mixed> $attributes The columns written to the default-language page
     * @param array<string, true> $columns
     */
    public function syncTranslationLayout(int $pageUid, array $attributes, array $columns): void
    {
        if (!isset($columns['l10n_parent'])) {
            return;
        }
        $layout = $this->databaseSchema->filterRow(
            array_intersect_key($attributes, ['backend_layout' => true, 'backend_layout_next_level' => true]),
            $columns
        );
        if ($layout === []) {
            return;
        }
        $this->connectionPool->getConnectionForTable('pages')->update('pages', $layout, ['l10n_parent' => $pageUid]);
    }

    /**
     * The parent of a live page, hidden or not, or null for an unknown page.
     */
    public function parentOf(int $pageUid): ?int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $pid = $queryBuilder
            ->select('pid')
            ->from('pages')
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pageUid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchOne();

        return is_numeric($pid) ? (int)$pid : null;
    }

    /**
     * Whether $pid is $rootPid or one of its descendants (walks up at most
     * 20 levels, the depth of any seeded tree). Hidden pages count: the
     * restrictions are removed, as Connection::select() would apply them.
     */
    private function isPageBelow(int $pid, int $rootPid): bool
    {
        for ($level = 0; $pid > 0 && $level < 20; $level++) {
            if ($pid === $rootPid) {
                return true;
            }
            $pid = $this->parentOf($pid) ?? 0;
        }

        return false;
    }

    /**
     * @param array<string, true> $columns
     * @param array<string, mixed> $attributes Additional page columns (nav_title, abstract, backend_layout, ...)
     */
    public function update(int $pageUid, string $title, string $slug, int $sorting, int $now, array $columns, array $attributes = []): void
    {
        $this->connectionPool->getConnectionForTable('pages')->update(
            'pages',
            $this->databaseSchema->filterRow([
                'title' => $title,
                'slug' => $slug,
                'hidden' => 0,
                'sorting' => $sorting,
                'tstamp' => $now,
                ...$attributes,
            ], $columns),
            ['uid' => $pageUid]
        );
    }

    /**
     * @param array<string, true> $columns
     * @param array<string, mixed> $attributes Additional page columns (nav_title, abstract, backend_layout, ...)
     */
    public function create(int $parentPid, string $title, string $slug, int $sorting, int $now, array $columns, array $attributes = []): int
    {
        $connection = $this->connectionPool->getConnectionForTable('pages');
        $connection->insert('pages', $this->databaseSchema->filterRow([
            'pid' => $parentPid,
            'title' => $title,
            'doktype' => 1,
            'slug' => $slug,
            'hidden' => 0,
            'sorting' => $sorting,
            'crdate' => $now,
            'tstamp' => $now,
            ...$attributes,
        ], $columns));

        return CollectionRecordSeeder::normalizeLastInsertId($connection->lastInsertId());
    }

    /**
     * Hides direct child pages of a root that are not part of the managed set.
     *
     * @param list<int> $managedChildPageUids
     * @return int Number of hidden pages
     */
    public function hideUnmanagedChildPages(int $rootUid, array $managedChildPageUids, int $now): int
    {
        if ($managedChildPageUids === []) {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder
            ->update('pages')
            ->set('hidden', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->notIn(
                    'uid',
                    $queryBuilder->createNamedParameter($managedChildPageUids, ArrayParameterType::INTEGER)
                ),
                ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, 'pages')
            );

        return $queryBuilder->executeStatement();
    }
}
