<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

final class CollectionRecordSeeder
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly ExtensionFalSeeder $falSeeder,
    ) {}

    /**
     * @param array<string, array{table: string, column?: string, items: list<array<string, mixed>>}> $collections
     */
    public function seed(int $contentUid, int $pageUid, int $now, array $collections): void
    {
        foreach ($collections as $collection) {
            $table = $collection['table'];
            $columns = $this->databaseSchema->getColumnNames($table);
            $connection = $this->connectionPool->getConnectionForTable($table);

            foreach ($collection['items'] as $index => $item) {
                if ($item === []) {
                    continue;
                }

                $fileReferences = self::normalizeFileReferencePayloads($item[SeedingPayloadKeys::FILE_REFERENCES] ?? []);
                unset($item[SeedingPayloadKeys::FILE_REFERENCES]);
                $nestedCollections = is_array($item[SeedingPayloadKeys::NESTED_COLLECTIONS] ?? null)
                    ? self::normalizeCollectionPayloads($item[SeedingPayloadKeys::NESTED_COLLECTIONS])
                    : [];
                unset($item[SeedingPayloadKeys::NESTED_COLLECTIONS]);

                $row = $this->databaseSchema->filterRow([
                    'pid' => $pageUid,
                    'sorting' => $index + 1,
                    'hidden' => 0,
                    'sys_language_uid' => 0,
                    'crdate' => $now,
                    'tstamp' => $now,
                    'foreign_table_parent_uid' => $contentUid,
                ] + $item, $columns);

                if (!self::hasPayloadBeyondSystemFields($row)) {
                    continue;
                }

                $connection->insert($table, $row);
                $collectionRowUid = self::normalizeLastInsertId($connection->lastInsertId());
                $this->falSeeder->seedFileReferences($table, $collectionRowUid, $pageUid, $now, $fileReferences);
                $this->seed($collectionRowUid, $pageUid, $now, $nestedCollections);
            }
        }
    }

    /**
     * @param array<mixed, mixed> $collections
     * @return array<string, array{table: string, column?: string, items: list<array<string, mixed>>}>
     */
    public static function normalizeCollectionPayloads(array $collections): array
    {
        $normalizedCollections = [];
        foreach ($collections as $field => $collection) {
            if (!is_string($field) || !is_array($collection)) {
                continue;
            }

            $table = $collection['table'] ?? null;
            $items = $collection['items'] ?? null;
            if (!is_string($table) || !is_array($items)) {
                continue;
            }

            $normalizedItems = [];
            foreach ($items as $item) {
                if (is_array($item)) {
                    $normalizedItems[] = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($item);
                }
            }

            $normalizedCollections[$field] = [
                'table' => $table,
                'items' => $normalizedItems,
            ];

            if (is_string($collection['column'] ?? null)) {
                $normalizedCollections[$field]['column'] = $collection['column'];
            }
        }

        return $normalizedCollections;
    }

    /**
     * @return array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>
     */
    public static function normalizeFileReferencePayloads(mixed $payload): array
    {
        if (!is_array($payload)) {
            return [];
        }

        $normalized = [];
        foreach ($payload as $fieldName => $references) {
            if (!is_string($fieldName) || !is_array($references)) {
                continue;
            }

            foreach ($references as $reference) {
                if (!is_array($reference)) {
                    continue;
                }
                $file = self::stringFromMixed($reference['file'] ?? '');
                if ($file === '') {
                    continue;
                }
                $normalized[$fieldName][] = [
                    'file' => $file,
                    'title' => self::stringFromMixed($reference['title'] ?? ''),
                    'alternative' => self::stringFromMixed($reference['alternative'] ?? ''),
                    'description' => self::stringFromMixed($reference['description'] ?? ''),
                    'source' => self::stringFromMixed($reference['source'] ?? ''),
                ];
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function hasPayloadBeyondSystemFields(array $row): bool
    {
        $systemFields = [
            'pid' => true,
            'sorting' => true,
            'hidden' => true,
            'sys_language_uid' => true,
            'crdate' => true,
            'tstamp' => true,
            'foreign_table_parent_uid' => true,
        ];

        foreach ($row as $field => $_value) {
            if (!isset($systemFields[$field])) {
                return true;
            }
        }

        return false;
    }

    public static function normalizeLastInsertId(int|string|false $lastInsertId): int
    {
        if (is_int($lastInsertId)) {
            return $lastInsertId;
        }
        if (is_string($lastInsertId) && is_numeric($lastInsertId)) {
            return (int)$lastInsertId;
        }

        return 0;
    }

    private static function stringFromMixed(mixed $value): string
    {
        return is_scalar($value) ? trim((string)$value) : '';
    }
}
