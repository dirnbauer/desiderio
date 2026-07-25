<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Updates;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\ChattyInterface;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Moves collection child rows into the shared record-type tables.
 *
 * 29 collections whose child field definitions were byte-identical now share 12
 * tables (see Documentation/Developer/CollectionTableConsolidation.md for how
 * that set was chosen, and Build/Data/collection-merge-map.json for the map
 * itself). The rows have to follow, and every uid they are referenced by has to
 * follow with them.
 *
 * What makes this safe to run, and safe to re-run:
 *
 * - Rows are INSERTed, never UPDATEd in place, so a new uid is minted and the
 *   old row is left untouched until the operator drops it. A failure halfway
 *   through loses nothing.
 * - Every (old table, old uid) -> new uid pair is recorded in
 *   tx_desiderio_collection_uid_map, which is what makes the reference remap
 *   possible and the whole thing idempotent: a second run sees the mapping and
 *   skips.
 * - References are remapped from that map, not recomputed: sys_file_reference
 *   (both uid_foreign and tablenames), and the self-referencing l10n_parent /
 *   l10n_source / t3ver_oid columns.
 *
 * Deliberately NOT done here: dropping the source tables. They are the only
 * copy of the pre-migration state, so removing them is a separate, human
 * decision taken after Build/Scripts/verify-collection-merge.php passes.
 */
#[UpgradeWizard('desiderioSharedCollectionTables')]
final class MigrateSharedCollectionTablesUpdate implements UpgradeWizardInterface, ChattyInterface
{
    private const MAP_TABLE = 'tx_desiderio_collection_uid_map';

    private ?OutputInterface $output = null;

    /** @var array<string, array{source: string, target: string, fieldname: string, tablenames: string}>|null */
    private ?array $map = null;

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getTitle(): string
    {
        return 'Desiderio: move collection children into shared record-type tables';
    }

    public function getDescription(): string
    {
        $pending = $this->countPendingRows();

        return sprintf(
            'Moves %d collection child row(s) from 29 per-element tables into 12 shared tables, '
            . 'remapping file references, translation parents and workspace originals. '
            . 'Source tables are left in place; drop them only after '
            . 'Build/Scripts/verify-collection-merge.php reports PASS.',
            $pending
        );
    }

    public function getPrerequisites(): array
    {
        return [];
    }

    public function updateNecessary(): bool
    {
        return $this->countPendingRows() > 0;
    }

    public function executeUpdate(): bool
    {
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $this->ensureMapTable($pool);

        $movedTotal = 0;
        foreach ($this->getMap() as $source => $entry) {
            if (!$this->tableExists($pool, $source) || !$this->tableExists($pool, $entry['target'])) {
                continue;
            }

            $moved = $this->migrateTable($pool, $source, $entry);
            $movedTotal += $moved;
            if ($moved > 0) {
                $this->say(sprintf('  %-42s -> %-28s %d row(s)', $source, $entry['target'], $moved));
            }
        }

        $references = $this->remapFileReferences($pool);
        $selfRefs = $this->remapSelfReferences($pool);

        $this->say(sprintf(
            'Moved %d row(s); remapped %d file reference(s) and %d self-reference(s).',
            $movedTotal,
            $references,
            $selfRefs
        ));

        return true;
    }

    /**
     * @param array{source: string, target: string, fieldname: string, tablenames: string} $entry
     */
    private function migrateTable(ConnectionPool $pool, string $source, array $entry): int
    {
        $target = $entry['target'];
        $sourceColumns = $this->columnNames($pool, $source);
        $targetColumns = $this->columnNames($pool, $target);

        // Copy every column the target also has, EXCEPT uid (a new one is
        // minted) and the ownership columns (set explicitly below). Anything
        // the target lacks would be silent data loss, so bail loudly instead.
        $copy = array_values(array_diff(array_intersect($sourceColumns, $targetColumns), ['uid', 'tablenames', 'fieldname']));
        $dropped = array_diff($sourceColumns, $targetColumns, ['uid']);
        if ($dropped !== []) {
            throw new \RuntimeException(sprintf(
                'Refusing to migrate %s: target %s has no column(s) %s, which would silently drop content.',
                $source,
                $target,
                implode(', ', $dropped)
            ), 1784980001);
        }

        $already = $this->existingMappings($pool, $source);

        $read = $pool->getQueryBuilderForTable($source);
        $read->getRestrictions()->removeAll();
        $rows = $read->select('*')->from($source)->executeQuery()->fetchAllAssociative();

        $connection = $pool->getConnectionForTable($target);
        $moved = 0;
        foreach ($rows as $row) {
            $oldUid = self::toInt($row['uid'] ?? null);
            if (isset($already[$oldUid])) {
                continue;
            }

            $insert = [];
            foreach ($copy as $column) {
                $insert[$column] = $row[$column] ?? null;
            }
            $insert['tablenames'] = $entry['tablenames'];
            $insert['fieldname'] = $entry['fieldname'];

            $connection->insert($target, $insert);
            $newUid = self::toInt($connection->lastInsertId());

            $pool->getConnectionForTable(self::MAP_TABLE)->insert(self::MAP_TABLE, [
                'source_table' => $source,
                'source_uid' => $oldUid,
                'target_table' => $target,
                'target_uid' => $newUid,
            ]);
            $moved++;
        }

        return $moved;
    }

    /**
     * sys_file_reference points at a child row by (tablenames, uid_foreign).
     * Both change, so both are rewritten from the map.
     */
    private function remapFileReferences(ConnectionPool $pool): int
    {
        $connection = $pool->getConnectionForTable('sys_file_reference');
        $updated = 0;

        foreach ($this->allMappings($pool) as $mapping) {
            $updated += $connection->update(
                'sys_file_reference',
                ['tablenames' => $mapping['target_table'], 'uid_foreign' => $mapping['target_uid']],
                ['tablenames' => $mapping['source_table'], 'uid_foreign' => $mapping['source_uid']]
            );
        }

        return $updated;
    }

    /**
     * l10n_parent, l10n_source and t3ver_oid point at another row in the SAME
     * table, so their values are old uids that no longer exist in the target.
     */
    private function remapSelfReferences(ConnectionPool $pool): int
    {
        $updated = 0;
        $byTarget = [];
        foreach ($this->allMappings($pool) as $mapping) {
            $byTarget[$mapping['target_table']][$mapping['source_uid']] = $mapping['target_uid'];
        }

        foreach ($byTarget as $target => $lookup) {
            if (!$this->tableExists($pool, $target)) {
                continue;
            }
            $connection = $pool->getConnectionForTable($target);
            $columns = array_intersect(['l10n_parent', 'l10n_source', 't3ver_oid'], $this->columnNames($pool, $target));

            foreach ($columns as $column) {
                $read = $pool->getQueryBuilderForTable($target);
                $read->getRestrictions()->removeAll();
                $rows = $read->select('uid', $column)->from($target)
                    ->where($read->expr()->gt($column, 0))
                    ->executeQuery()->fetchAllAssociative();

                foreach ($rows as $row) {
                    $old = self::toInt($row[$column] ?? null);
                    // Already remapped (points at a live row here) or points
                    // outside the moved set: leave it alone either way.
                    if (!isset($lookup[$old])) {
                        continue;
                    }
                    $updated += $connection->update($target, [$column => $lookup[$old]], ['uid' => self::toInt($row['uid'] ?? null)]);
                }
            }
        }

        return $updated;
    }

    private function countPendingRows(): int
    {
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);
        $pending = 0;
        foreach ($this->getMap() as $source => $entry) {
            if (!$this->tableExists($pool, $source) || !$this->tableExists($pool, $entry['target'])) {
                continue;
            }
            $already = count($this->existingMappings($pool, $source));

            $qb = $pool->getQueryBuilderForTable($source);
            $qb->getRestrictions()->removeAll();
            $total = self::toInt($qb->count('uid')->from($source)->executeQuery()->fetchOne());
            $pending += max(0, $total - $already);
        }

        return $pending;
    }

    /** @return array<int, int> source uid => target uid */
    private function existingMappings(ConnectionPool $pool, string $source): array
    {
        if (!$this->tableExists($pool, self::MAP_TABLE)) {
            return [];
        }
        $qb = $pool->getQueryBuilderForTable(self::MAP_TABLE);
        $rows = $qb->select('source_uid', 'target_uid')->from(self::MAP_TABLE)
            ->where($qb->expr()->eq('source_table', $qb->createNamedParameter($source)))
            ->executeQuery()->fetchAllAssociative();

        $map = [];
        foreach ($rows as $row) {
            $map[self::toInt($row['source_uid'] ?? null)] = self::toInt($row['target_uid'] ?? null);
        }

        return $map;
    }

    /** @return list<array{source_table: string, source_uid: int, target_table: string, target_uid: int}> */
    private function allMappings(ConnectionPool $pool): array
    {
        if (!$this->tableExists($pool, self::MAP_TABLE)) {
            return [];
        }
        $qb = $pool->getQueryBuilderForTable(self::MAP_TABLE);
        $rows = $qb->select('source_table', 'source_uid', 'target_table', 'target_uid')
            ->from(self::MAP_TABLE)->executeQuery()->fetchAllAssociative();

        $mappings = [];
        foreach ($rows as $row) {
            $mappings[] = [
                'source_table' => self::toStr($row['source_table'] ?? null),
                'source_uid' => self::toInt($row['source_uid'] ?? null),
                'target_table' => self::toStr($row['target_table'] ?? null),
                'target_uid' => self::toInt($row['target_uid'] ?? null),
            ];
        }

        return $mappings;
    }

    private function ensureMapTable(ConnectionPool $pool): void
    {
        if (!$this->tableExists($pool, self::MAP_TABLE)) {
            throw new \RuntimeException(
                self::MAP_TABLE . ' is missing — run the database analyser (extension:setup) first.',
                1784980002
            );
        }
    }

    /** @return array<string, array{source: string, target: string, fieldname: string, tablenames: string}> */
    private function getMap(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }
        $path = dirname(__DIR__, 2) . '/Build/Data/collection-merge-map.json';
        $decoded = json_decode((string)@file_get_contents($path), true);

        /** @var array<string, array{source: string, target: string, fieldname: string, tablenames: string}> $sources */
        $sources = is_array($decoded) && is_array($decoded['sources'] ?? null) ? $decoded['sources'] : [];

        return $this->map = $sources;
    }

    /** @return list<string> */
    private function columnNames(ConnectionPool $pool, string $table): array
    {
        $columns = $pool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table);

        return array_values(array_map(static fn($column): string => $column->getName(), $columns));
    }

    private function tableExists(ConnectionPool $pool, string $table): bool
    {
        return $pool->getConnectionForTable($table)->createSchemaManager()->tablesExist([$table]);
    }

    /** DBAL returns array<string, mixed>; narrow rather than blind-cast. */
    private static function toInt(mixed $value): int
    {
        return is_scalar($value) ? (int)$value : 0;
    }

    private static function toStr(mixed $value): string
    {
        return is_scalar($value) ? (string)$value : '';
    }

    private function say(string $message): void
    {
        $this->output?->writeln($message);
    }
}
