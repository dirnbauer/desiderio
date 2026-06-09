<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;

/**
 * Inserts a prepared tt_content row together with its file references and
 * collection child records. One instance is bound to a FAL target folder
 * (per seeder command), so files land in the right fileadmin subfolder.
 */
final class ContentElementSeeder
{
    private readonly ExtensionFalSeeder $falSeeder;
    private readonly CollectionRecordSeeder $collectionRecordSeeder;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        StorageRepository $storageRepository,
        DatabaseSchemaHelper $databaseSchema,
        string $falFolderName,
        int $missingStorageExceptionCode,
    ) {
        $this->falSeeder = new ExtensionFalSeeder(
            $connectionPool,
            $storageRepository,
            $databaseSchema,
            $falFolderName,
            $missingStorageExceptionCode,
        );
        $this->collectionRecordSeeder = new CollectionRecordSeeder(
            $connectionPool,
            $databaseSchema,
            $this->falSeeder,
        );
    }

    /**
     * @param array{
     *     row: array<string, mixed>,
     *     collections: array<string, array{table: string, column?: string, items: list<array<string, mixed>>}>,
     *     fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>
     * } $contentData
     * @return int The uid of the inserted tt_content row
     */
    public function insert(int $pageUid, int $now, array $contentData): int
    {
        $connection = $this->connectionPool->getConnectionForTable('tt_content');
        $connection->insert('tt_content', $contentData['row']);
        $contentUid = CollectionRecordSeeder::normalizeLastInsertId($connection->lastInsertId());

        $this->falSeeder->seedFileReferences('tt_content', $contentUid, $pageUid, $now, $contentData['fileReferences']);
        $this->collectionRecordSeeder->seed($contentUid, $pageUid, $now, $contentData['collections']);

        return $contentUid;
    }
}
