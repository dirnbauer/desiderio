<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Imports extension-bundled assets into a dedicated fileadmin folder and
 * writes sys_file_reference rows for seeded content.
 */
final class ExtensionFalSeeder
{
    /** @var array<string, File> */
    private array $importedFiles = [];

    private ?Folder $falFolder = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly StorageRepository $storageRepository,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly string $falFolderName,
        private readonly int $missingStorageExceptionCode,
    ) {}

    /**
     * @param array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>> $fileReferences
     */
    public function seedFileReferences(string $table, int $uid, int $pid, int $now, array $fileReferences): void
    {
        if ($fileReferences === []) {
            return;
        }

        $columns = $this->databaseSchema->getColumnNames('sys_file_reference');
        $connection = $this->connectionPool->getConnectionForTable('sys_file_reference');

        foreach ($fileReferences as $fieldName => $references) {
            foreach ($references as $index => $reference) {
                $file = $this->ensureExtensionFile($reference);
                if ($file === null) {
                    continue;
                }

                $connection->insert('sys_file_reference', $this->databaseSchema->filterRow([
                    'pid' => $pid,
                    'tstamp' => $now,
                    'crdate' => $now,
                    'hidden' => 0,
                    'deleted' => 0,
                    'sys_language_uid' => 0,
                    'uid_local' => $file->getUid(),
                    'uid_foreign' => $uid,
                    'tablenames' => $table,
                    'fieldname' => $fieldName,
                    'table_local' => 'sys_file',
                    'sorting' => $index + 1,
                    'sorting_foreign' => $index + 1,
                    'title' => $reference['title'],
                    'alternative' => $reference['alternative'],
                    'description' => $reference['description'],
                    'link' => $reference['source'],
                ], $columns));
            }
        }
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string} $reference
     */
    public function ensureExtensionFile(array $reference): ?File
    {
        $relativeFilePath = $reference['file'];
        if (isset($this->importedFiles[$relativeFilePath])) {
            return $this->importedFiles[$relativeFilePath];
        }

        $sourcePath = GeneralUtility::getFileAbsFileName('EXT:desiderio/' . $relativeFilePath);
        if ($sourcePath === '' || !is_file($sourcePath)) {
            return null;
        }

        $folder = $this->getFalFolder();
        $fileName = basename($relativeFilePath);
        $file = $folder->getFile($fileName);
        if (!$file instanceof File) {
            // Folder::addFile() moves its source. Import a temporary copy so
            // the extension-bundled asset stays in place.
            $temporaryPath = GeneralUtility::tempnam('desiderio_seed_');
            if (!@copy($sourcePath, $temporaryPath)) {
                @unlink($temporaryPath);

                return null;
            }
            try {
                $file = $folder->addFile($temporaryPath, $fileName);
            } finally {
                if (is_file($temporaryPath)) {
                    @unlink($temporaryPath);
                }
            }
        }

        $this->importedFiles[$relativeFilePath] = $file;
        $this->upsertFileMetadata($file, $reference);

        return $file;
    }

    private function getFalFolder(): Folder
    {
        if ($this->falFolder instanceof Folder) {
            return $this->falFolder;
        }

        $storage = $this->storageRepository->getDefaultStorage();
        if ($storage === null) {
            throw new \RuntimeException(
                sprintf('No default FAL storage is configured for Desiderio seeding (%s).', $this->falFolderName),
                $this->missingStorageExceptionCode
            );
        }

        $rootFolder = $storage->getRootLevelFolder(false);
        $this->falFolder = $rootFolder->hasFolder($this->falFolderName)
            ? $rootFolder->getSubfolder($this->falFolderName)
            : $rootFolder->createFolder($this->falFolderName);

        return $this->falFolder;
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string} $reference
     */
    private function upsertFileMetadata(File $file, array $reference): void
    {
        $columns = $this->databaseSchema->getColumnNames('sys_file_metadata');
        if (!isset($columns['file'])) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_file_metadata');
        $where = [
            $queryBuilder->expr()->eq('file', $queryBuilder->createNamedParameter($file->getUid())),
        ];
        if (isset($columns['sys_language_uid'])) {
            $where[] = $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0));
        }

        $existingUid = $queryBuilder
            ->select('uid')
            ->from('sys_file_metadata')
            ->where(...$where)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        $now = time();
        $row = $this->databaseSchema->filterRow([
            'file' => $file->getUid(),
            'pid' => 0,
            'tstamp' => $now,
            'crdate' => $now,
            'sys_language_uid' => 0,
            'title' => $reference['title'],
            'alternative' => $reference['alternative'],
            'description' => $reference['description'],
        ], $columns);

        $connection = $this->connectionPool->getConnectionForTable('sys_file_metadata');
        if (is_numeric($existingUid)) {
            $connection->update('sys_file_metadata', $row, ['uid' => (int)$existingUid]);

            return;
        }

        $connection->insert('sys_file_metadata', $row);
    }
}
