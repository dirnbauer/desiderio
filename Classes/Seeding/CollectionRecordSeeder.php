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
     * @param string $parentTable Table the rows hang off. Only meaningful for a
     *                            SHARED child table, where `foreign_table_parent_uid`
     *                            alone no longer identifies the owner.
     */
    public function seed(int $contentUid, int $pageUid, int $now, array $collections, string $parentTable = 'tt_content'): void
    {
        foreach ($collections as $collection) {
            $table = $collection['table'];
            $columns = $this->databaseSchema->getColumnNames($table);
            $connection = $this->connectionPool->getConnectionForTable($table);

            // On a shared table Content Blocks matches children by
            // (tablenames, fieldname, foreign_table_parent_uid). Writing only
            // the parent uid would make every sharer's rows indistinguishable,
            // so they would all show up in every element that uses the table.
            // filterRow() drops both keys on unshared tables, which have
            // neither column.
            $ownership = [];
            if ($this->databaseSchema->tableHasColumn($table, 'tablenames')) {
                $ownership['tablenames'] = $parentTable;
            }
            if ($this->databaseSchema->tableHasColumn($table, 'fieldname') && is_string($collection['column'] ?? null)) {
                $ownership['fieldname'] = $collection['column'];
            }
            if (isset($ownership['fieldname']) && $ownership['fieldname'] === '') {
                // An empty fieldname on a shared table is unrecoverable: the
                // TCA cannot match the row and no cleanup can find it again.
                throw new \RuntimeException(
                    sprintf('Refusing to seed into shared table %s without a fieldname.', $table),
                    1784982001
                );
            }

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
                ] + $ownership + $item, $columns);

                if (!self::hasPayloadBeyondSystemFields($row)) {
                    continue;
                }

                $connection->insert($table, $row);
                $collectionRowUid = self::normalizeLastInsertId($connection->lastInsertId());
                $this->falSeeder->seedFileReferences($table, $collectionRowUid, $pageUid, $now, $fileReferences);
                // A nested collection hangs off THIS table, not tt_content.
                $this->seed($collectionRowUid, $pageUid, $now, $nestedCollections, $table);
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

            // `column` is the Content Blocks uniqueIdentifier and becomes the
            // shared table's `fieldname`. Root collections carry it explicitly
            // (it is the prefixed tt_content column); NESTED collections never
            // do, because their uniqueIdentifier IS the bare identifier — which
            // is exactly this array key. Without the fallback, nested rows were
            // written with an empty fieldname, so the TCA could no longer find
            // them and the next reseed could not clean them up.
            $normalizedCollections[$field]['column'] = is_string($collection['column'] ?? null)
                ? $collection['column']
                : $field;
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
            // Ownership columns on a shared table are bookkeeping, never
            // content: a row carrying only these is still an empty row.
            'tablenames' => true,
            'fieldname' => true,
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
