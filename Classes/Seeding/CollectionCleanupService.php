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

            // `column` IS the Content Blocks uniqueIdentifier, which is what
            // lands in the shared table's `fieldname`. On a shared table this
            // is what separates one collection's rows from a sibling's on the
            // very same parent record; without it, reseeding one field deletes
            // the other's content.
            $fieldName = is_string($collection['column'] ?? null) ? $collection['column'] : null;

            $collectionUids = $this->findCollectionRowUids($table, $parentUids, $parentTable, $fieldName);
            $this->deleteCollectionRowsForParentUids($collectionUids, $table, $collectionsByParentTable);
            $this->deleteFileReferencesForRecords($table, $collectionUids);

            if ($collectionUids === []) {
                continue;
            }

            // Delete by the uids just resolved rather than re-running the
            // parent predicate: the lookup above is the single place that knows
            // how ownership is decided, so the two cannot drift apart.
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
            $queryBuilder
                ->delete($table)
                ->where(
                    $queryBuilder->expr()->in(
                        'uid',
                        $queryBuilder->createNamedParameter($collectionUids, ArrayParameterType::INTEGER)
                    )
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
    public function findCollectionRowUids(string $table, array $parentUids, ?string $parentTable = null, ?string $fieldName = null): array
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
                ...$this->buildOwnershipConstraints($queryBuilder, $table, $parentTable, $fieldName),
                ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, $table)
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return self::normalizeIntegerList($uids);
    }

    /**
     * Narrows a child lookup to the collection field that actually owns the row.
     *
     * On a table used by exactly one collection, `foreign_table_parent_uid`
     * alone identifies the children. On a table SHARED between collections
     * (`shareAcrossTables` / `shareAcrossFields`) it does not: two fields on the
     * same parent record produce rows with the same parent uid, so reseeding
     * one field would delete the other field's rows. `tablenames` and
     * `fieldname` are the columns Content Blocks adds for exactly this, and
     * they are only applied when the table actually has them — unshared tables
     * keep the original behaviour.
     *
     * @return list<\TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression|string>
     */
    private function buildOwnershipConstraints(
        \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder,
        string $table,
        ?string $parentTable,
        ?string $fieldName,
    ): array {
        $constraints = [];
        if ($parentTable !== null && $parentTable !== '' && $this->databaseSchema->tableHasColumn($table, 'tablenames')) {
            $constraints[] = $queryBuilder->expr()->eq('tablenames', $queryBuilder->createNamedParameter($parentTable));
        }
        if ($fieldName !== null && $fieldName !== '' && $this->databaseSchema->tableHasColumn($table, 'fieldname')) {
            $constraints[] = $queryBuilder->expr()->eq('fieldname', $queryBuilder->createNamedParameter($fieldName));
        }

        return $constraints;
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
