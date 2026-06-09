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
final class SeedPageUpserter
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper,
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
