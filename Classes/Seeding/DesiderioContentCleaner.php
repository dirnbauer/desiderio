<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Expression\CompositeExpression;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

/**
 * Soft-deletes previously seeded Desiderio content elements on a page and
 * removes their file references and collection child rows. Only live
 * workspace rows are touched.
 */
final class DesiderioContentCleaner
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper,
        private readonly CollectionCleanupService $collectionCleanupService,
        private readonly ContentBlockCollectionMap $contentBlockCollectionMap,
    ) {}

    /**
     * @param list<string> $additionalCTypes Core CTypes that should be replaced as well
     */
    public function softDeleteSeededContent(
        int $pageUid,
        int $now,
        array $additionalCTypes = [],
        bool $cleanupPageCollections = false,
    ): void {
        $existingContentUids = $this->findSeededContentUids($pageUid, $additionalCTypes);
        if ($existingContentUids !== []) {
            $this->collectionCleanupService->deleteFileReferencesForRecords('tt_content', $existingContentUids);
            $this->collectionCleanupService->deleteCollectionRowsForParentUids(
                $existingContentUids,
                'tt_content',
                $this->contentBlockCollectionMap->getCollectionsByParentTable(),
            );
        }
        if ($cleanupPageCollections) {
            $this->collectionCleanupService->deleteCollectionRowsForPage(
                $pageUid,
                $this->contentBlockCollectionMap->getCollectionTableNames(),
            );
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                ...$this->buildCTypeConstraints($queryBuilder, $additionalCTypes),
                ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, 'tt_content')
            )
            ->executeStatement();
    }

    /**
     * @param list<string> $additionalCTypes
     * @return list<int>
     */
    private function findSeededContentUids(int $pageUid, array $additionalCTypes): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        $uids = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                ...$this->buildCTypeConstraints($queryBuilder, $additionalCTypes),
                ...$this->liveWorkspaceQueryHelper->buildLiveWorkspaceConstraints($queryBuilder, 'tt_content')
            )
            ->executeQuery()
            ->fetchFirstColumn();

        $integers = [];
        foreach ($uids as $uid) {
            if (is_numeric($uid)) {
                $integers[] = (int)$uid;
            }
        }

        return $integers;
    }

    /**
     * @param list<string> $additionalCTypes
     * @return list<string|CompositeExpression>
     */
    private function buildCTypeConstraints(QueryBuilder $queryBuilder, array $additionalCTypes): array
    {
        $desiderioConstraint = $queryBuilder->expr()->like(
            'CType',
            $queryBuilder->createNamedParameter('desiderio_%')
        );
        if ($additionalCTypes === []) {
            return [$desiderioConstraint];
        }

        return [
            $queryBuilder->expr()->or(
                $desiderioConstraint,
                $queryBuilder->expr()->in(
                    'CType',
                    $queryBuilder->createNamedParameter($additionalCTypes, ArrayParameterType::STRING)
                )
            ),
        ];
    }
}
