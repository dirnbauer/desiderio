<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class CollectionCleanupService
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper,
    ) {}

    /**
     * @param list<int> $parentUids
     * @param array<string, list<array<string, mixed>>> $collectionsByParentTable
     */
    public function deleteCollectionRowsForParentUids(array $parentUids, string $parentTable, array $collectionsByParentTable): void
    {
        if ($parentUids === []) {
            return;
        }

        foreach ($collectionsByParentTable[$parentTable] ?? [] as $collection) {
            $table = $collection['table'] ?? null;
            if (!is_string($table) || $table === '') {
                continue;
            }
            if (!$this->databaseSchema->tableHasColumn($table, 'foreign_table_parent_uid')) {
                continue;
            }

            $collectionUids = $this->findCollectionRowUids($table, $parentUids);
            $this->deleteCollectionRowsForParentUids($collectionUids, $table, $collectionsByParentTable);
            $this->deleteFileReferencesForRecords($table, $collectionUids);

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $queryBuilder
                ->delete($table)
                ->where(
                    $queryBuilder->expr()->in(
                        'foreign_table_parent_uid',
                        $queryBuilder->createNamedParameter($parentUids, ArrayParameterType::INTEGER)
                    ),
                    ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, $table)
                )
                ->executeStatement();
        }
    }

    /**
     * @param list<string> $collectionTableNames
     */
    public function deleteCollectionRowsForPage(int $pageUid, array $collectionTableNames): void
    {
        foreach ($collectionTableNames as $table) {
            if (!$this->databaseSchema->tableHasColumn($table, 'pid')) {
                continue;
            }

            $collectionUids = $this->findCollectionRowUidsByPid($table, $pageUid);
            $this->deleteFileReferencesForRecords($table, $collectionUids);

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $queryBuilder
                ->delete($table)
                ->where(
                    $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                    ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, $table)
                )
                ->executeStatement();
        }
    }

    /**
     * @param list<int> $recordUids
     */
    public function deleteFileReferencesForRecords(string $table, array $recordUids): void
    {
        if ($recordUids === [] || !$this->databaseSchema->tableHasColumn('sys_file_reference', 'uid_foreign')) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_reference');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->delete('sys_file_reference')
            ->where(
                $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($table)),
                $queryBuilder->expr()->in(
                    'uid_foreign',
                    $queryBuilder->createNamedParameter($recordUids, ArrayParameterType::INTEGER)
                ),
                ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, 'sys_file_reference')
            )
            ->executeStatement();
    }

    /**
     * @param list<int> $parentUids
     * @return list<int>
     */
    public function findCollectionRowUids(string $table, array $parentUids): array
    {
        if ($parentUids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $uids = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->in(
                    'foreign_table_parent_uid',
                    $queryBuilder->createNamedParameter($parentUids, ArrayParameterType::INTEGER)
                ),
                ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, $table)
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return self::normalizeIntegerList($uids);
    }

    /**
     * @return list<int>
     */
    public function findCollectionRowUidsByPid(string $table, int $pageUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();

        $uids = $queryBuilder
            ->select('uid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, $table)
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return self::normalizeIntegerList($uids);
    }

    /**
     * @param list<mixed> $values
     * @return list<int>
     */
    private static function normalizeIntegerList(array $values): array
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
}
