<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

/**
 * Idempotent per-CType upsert of element library demo records: every catalog
 * element gets exactly one tt_content row in the library storage folder.
 * Existing rows are updated IN PLACE (the uid is what pickers and preview
 * URLs reference, it must stay stable across re-runs); their collection
 * children and file references are rebuilt.
 *
 * Both Desiderio and Innesto elements are filled by the (neutral) demo value
 * generator rather than their fixture.json: the picker preview must read like a
 * believable, generic example the editor can keep or edit, not a slide that
 * promotes the design system. Desiderio definitions come from the registry,
 * Innesto definitions are built from their config.yaml.
 */
final class LibraryElementUpserter
{
    private const FAL_FOLDER = 'desiderio-element-library';

    private readonly ExtensionFalSeeder $falSeeder;
    private readonly CollectionRecordSeeder $collectionRecordSeeder;

    /** @var array<string, list<array<string, mixed>>>|null */
    private ?array $collectionsByParentTable = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        StorageRepository $storageRepository,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly StyleguideFixtureResolver $fixtureResolver,
        private readonly CollectionCleanupService $collectionCleanupService,
        private readonly ElementCatalogDefinitions $catalogDefinitions,
    ) {
        $this->falSeeder = new ExtensionFalSeeder(
            $connectionPool,
            $storageRepository,
            $databaseSchema,
            self::FAL_FOLDER,
            1777200002,
        );
        $this->collectionRecordSeeder = new CollectionRecordSeeder(
            $connectionPool,
            $databaseSchema,
            $this->falSeeder,
        );
    }

    /**
     * @param array{cType: string, name: string, hostExtension: string, config: array<string, mixed>, fixture: array<string, mixed>} $element
     * @return array{0: 'created'|'updated', 1: int} status and tt_content uid
     */
    public function upsert(int $folderPid, array $element, int $sorting, int $now): array
    {
        $columns = $this->databaseSchema->getColumnNames('tt_content');
        $contentData = $this->buildContentData($folderPid, $element, $sorting, $now, $columns);

        $existingUid = $this->findExistingUid($folderPid, $element['cType']);
        if ($existingUid === null) {
            $connection = $this->connectionPool->getConnectionForTable('tt_content');
            $connection->insert('tt_content', $contentData['row']);
            $contentUid = CollectionRecordSeeder::normalizeLastInsertId($connection->lastInsertId());
            $this->seedChildren($contentUid, $folderPid, $now, $contentData);
            return ['created', $contentUid];
        }

        // uid-stable update: clear children + file references, update the row, reseed
        $this->collectionCleanupService->deleteFileReferencesForRecords('tt_content', [$existingUid]);
        $this->collectionCleanupService->deleteCollectionRowsForParentUids(
            [$existingUid],
            'tt_content',
            $this->getCollectionsByParentTable()
        );

        $row = $contentData['row'];
        unset($row['pid'], $row['crdate']);
        $this->connectionPool->getConnectionForTable('tt_content')->update('tt_content', $row, ['uid' => $existingUid]);
        $this->seedChildren($existingUid, $folderPid, $now, $contentData);

        return ['updated', $existingUid];
    }

    /**
     * Soft-deletes library records whose CType is no longer part of the catalog.
     *
     * @param list<string> $knownCTypes
     */
    public function removeObsolete(int $folderPid, array $knownCTypes, int $now): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderPid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            );
        if ($knownCTypes !== []) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->notIn(
                    'CType',
                    $queryBuilder->createNamedParameter($knownCTypes, \Doctrine\DBAL\ArrayParameterType::STRING)
                )
            );
        }
        return $queryBuilder->executeStatement();
    }

    /**
     * @param array{cType: string, name: string, hostExtension: string, config: array<string, mixed>, fixture: array<string, mixed>} $element
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column?: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function buildContentData(int $pid, array $element, int $sorting, int $now, array $columns): array
    {
        if ($element['hostExtension'] === 'desiderio') {
            // Empty fixture on purpose: the resolver completes every field from
            // the registry definition with the neutral demo value generator, so
            // the library record carries generic example content instead of the
            // promotional copy that ships in each element's fixture.json.
            return $this->fixtureResolver->buildContentInsert(
                $pid,
                $element['cType'],
                $element['name'],
                [],
                $sorting,
                $now,
                $columns
            );
        }

        // Foreign host extension (innesto): no fixture, definition built from config.yaml
        $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig($element['config']);
        [$resolvedFields, $collections, $fileReferences] = $this->fixtureResolver->completeResolvedFixtureData(
            $element['cType'],
            $element['name'],
            $definition,
            [],
            [],
            $element['fixture']
        );

        $row = [
            'pid' => $pid,
            'CType' => $element['cType'],
            'colPos' => 0,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ];
        foreach ($resolvedFields as $field => $value) {
            $row[$field] = $value;
        }
        foreach ($fileReferences as $field => $references) {
            $row[$field] = count($references);
        }
        foreach ($collections as $field => $collection) {
            $row[$collection['column'] ?? $field] = count($collection['items']);
        }

        return [
            'row' => $this->databaseSchema->filterRow($row, $columns),
            'collections' => $collections,
            'fileReferences' => $fileReferences,
        ];
    }

    /**
     * @param array{collections: array<string, array{table: string, column?: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>} $contentData
     */
    private function seedChildren(int $contentUid, int $pageUid, int $now, array $contentData): void
    {
        $this->falSeeder->seedFileReferences('tt_content', $contentUid, $pageUid, $now, $contentData['fileReferences']);
        $this->collectionRecordSeeder->seed($contentUid, $pageUid, $now, $contentData['collections']);
    }

    private function findExistingUid(int $folderPid, string $cType): ?int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $uid = $queryBuilder
            ->select('uid')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderPid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($cType)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('uid')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        return is_numeric($uid) ? (int)$uid : null;
    }

    /**
     * Collections of ALL catalog elements (desiderio + innesto), keyed by
     * parent table - the desiderio-only ContentBlockCollectionMap would miss
     * innesto child tables during cleanup.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function getCollectionsByParentTable(): array
    {
        return $this->collectionsByParentTable ??= $this->catalogDefinitions->getCollectionsByParentTable();
    }
}
