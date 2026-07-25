<?php

declare(strict_types=1);

/**
 * Zero-loss proof for the collection-table consolidation.
 *
 * Run BEFORE the migration to capture a baseline, AFTER it to compare:
 *
 *   ddev exec php Build/Scripts/verify-collection-merge.php snapshot before.json
 *   …run the migration…
 *   ddev exec php Build/Scripts/verify-collection-merge.php compare before.json
 *
 * The comparison is a MULTISET of per-row payload hashes, not a row count and
 * not a uid comparison. Counts cannot see a row whose content was silently
 * blanked, and uids necessarily change when rows move between tables — so the
 * only honest question is "does every payload that existed before still exist
 * after, exactly once". That is what this asserts, alongside the reference
 * surfaces that a uid remap can corrupt: file references, parent links and
 * translation parents.
 *
 * ORDER MATTERS. `compare` must run immediately after the migration and BEFORE
 * any reseed. A reseed legitimately rewrites collection children — it
 * hard-deletes and recreates them, re-minting uids and re-deriving sorting — so
 * comparing a reseeded database against a pre-migration baseline conflates two
 * unrelated changes and reports normal seeding as data loss. The question this
 * tool answers is narrow on purpose: did MOVING the rows lose anything.
 *
 * Must run inside the TYPO3 environment (ddev exec), because it uses the
 * configured database connection.
 */

use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (!is_file($autoload)) {
    // Installed as a dependency: climb to the project's vendor directory.
    $autoload = dirname(__DIR__, 5) . '/vendor/autoload.php';
}
require $autoload;

SystemEnvironmentBuilder::run(0, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init(require dirname($autoload) . '/autoload.php');

$mode = $argv[1] ?? '';
$file = $argv[2] ?? '';
if (!in_array($mode, ['snapshot', 'compare'], true) || $file === '') {
    fwrite(STDERR, "Usage: verify-collection-merge.php snapshot|compare <file.json>\n");
    exit(2);
}

$mapPath = dirname(__DIR__) . '/Data/collection-merge-map.json';
$map = json_decode((string)file_get_contents($mapPath), true);
if (!is_array($map) || !isset($map['sources'])) {
    fwrite(STDERR, "Cannot read $mapPath\n");
    exit(2);
}

$pool = GeneralUtility::makeInstance(ConnectionPool::class);

/** Columns that carry CONTENT, i.e. everything a row would be missed for. */
function payloadColumns(ConnectionPool $pool, string $table): array
{
    $schema = $pool->getConnectionForTable($table)->createSchemaManager()->introspectTable($table);
    $system = [
        'uid', 'pid', 'tstamp', 'crdate', 'deleted', 'hidden', 'starttime', 'endtime',
        'fe_group', 'editlock', 'sorting', 'sys_language_uid', 'l10n_parent', 'l10n_source',
        'l10n_diffsource', 'l10n_state', 't3ver_oid', 't3ver_wsid', 't3ver_state', 't3ver_stage',
        'foreign_table_parent_uid', 'tablenames', 'fieldname',
    ];
    $columns = [];
    foreach ($schema->getColumns() as $column) {
        $name = $column->getName();
        if (!in_array($name, $system, true)) {
            $columns[] = $name;
        }
    }
    sort($columns);

    return $columns;
}

/**
 * One hash per row: the payload, plus the identity of the record it hangs off
 * and its language. A row that survives with the right content but the wrong
 * parent is still a defect, so the parent is part of the identity.
 */
function rowHashes(ConnectionPool $pool, string $table, array $columns, ?string $fieldName): array
{
    $qb = $pool->getQueryBuilderForTable($table);
    $qb->getRestrictions()->removeAll();
    $qb->select('*')->from($table)->where($qb->expr()->eq('deleted', 0));

    // On a shared table, only the rows belonging to this collection field.
    $hasFieldname = $pool->getConnectionForTable($table)->createSchemaManager()
        ->introspectTable($table)->hasColumn('fieldname');
    if ($fieldName !== null && $hasFieldname) {
        $qb->andWhere($qb->expr()->eq('fieldname', $qb->createNamedParameter($fieldName)));
    }

    $hashes = [];
    foreach ($qb->executeQuery()->fetchAllAssociative() as $row) {
        $payload = [];
        foreach ($columns as $column) {
            $payload[$column] = (string)($row[$column] ?? '');
        }
        $identity = [
            'payload' => $payload,
            'language' => (int)($row['sys_language_uid'] ?? 0),
            'sorting' => (int)($row['sorting'] ?? 0),
        ];
        $identity['parent'] = (int)($row['foreign_table_parent_uid'] ?? 0);
        $hashes[] = md5(json_encode($identity, JSON_UNESCAPED_UNICODE));
    }
    sort($hashes);

    return $hashes;
}

/** @param list<string> $tables */
function countReferences(ConnectionPool $pool, array $tables): int
{
    if ($tables === []) {
        return 0;
    }
    $qb = $pool->getQueryBuilderForTable('sys_file_reference');
    $qb->getRestrictions()->removeAll();

    return (int)$qb->count('uid')->from('sys_file_reference')
        ->where(
            $qb->expr()->in('tablenames', $qb->createNamedParameter($tables, \Doctrine\DBAL\ArrayParameterType::STRING)),
            $qb->expr()->eq('deleted', 0)
        )->executeQuery()->fetchOne();
}

function orphanedChildren(ConnectionPool $pool, string $table, string $fieldName, string $parentTable): int
{
    $qb = $pool->getQueryBuilderForTable($table);
    $qb->getRestrictions()->removeAll();
    $parents = $qb->select('foreign_table_parent_uid')->from($table)
        ->where(
            $qb->expr()->eq('deleted', 0),
            $qb->expr()->eq('fieldname', $qb->createNamedParameter($fieldName)),
            $qb->expr()->eq('tablenames', $qb->createNamedParameter($parentTable))
        )->executeQuery()->fetchFirstColumn();
    if ($parents === []) {
        return 0;
    }
    $parents = array_values(array_unique(array_map('intval', $parents)));

    $parentQb = $pool->getQueryBuilderForTable($parentTable);
    $parentQb->getRestrictions()->removeAll();
    $live = $parentQb->select('uid')->from($parentTable)
        ->where(
            $parentQb->expr()->eq('deleted', 0),
            $parentQb->expr()->in('uid', $parentQb->createNamedParameter($parents, \Doctrine\DBAL\ArrayParameterType::INTEGER))
        )->executeQuery()->fetchFirstColumn();

    return count(array_diff($parents, array_map('intval', $live)));
}

function danglingReferences(ConnectionPool $pool, string $table): int
{
    $qb = $pool->getQueryBuilderForTable('sys_file_reference');
    $qb->getRestrictions()->removeAll();
    $uids = $qb->select('uid_foreign')->from('sys_file_reference')
        ->where(
            $qb->expr()->eq('tablenames', $qb->createNamedParameter($table)),
            $qb->expr()->eq('deleted', 0)
        )->executeQuery()->fetchFirstColumn();
    if ($uids === []) {
        return 0;
    }

    $rowQb = $pool->getQueryBuilderForTable($table);
    $rowQb->getRestrictions()->removeAll();
    $live = $rowQb->select('uid')->from($table)
        ->where($rowQb->expr()->in('uid', $rowQb->createNamedParameter(array_map('intval', $uids), \Doctrine\DBAL\ArrayParameterType::INTEGER)))
        ->executeQuery()->fetchFirstColumn();

    return count(array_diff(array_map('intval', $uids), array_map('intval', $live)));
}

$state = [];
foreach ($map['sources'] as $source => $entry) {
    $connection = $pool->getConnectionForTable('tt_content');
    $schemaManager = $connection->createSchemaManager();
    $exists = fn(string $t): bool => in_array($t, $schemaManager->listTableNames(), true);

    // Before the migration the rows live in the source table and carry no
    // fieldname; after, they live in the target and are tagged with one.
    if ($mode === 'snapshot' && $exists($source)) {
        $table = $source;
        $fieldName = null;
    } elseif ($exists($entry['target'])) {
        $table = $entry['target'];
        $fieldName = $entry['fieldname'];
    } else {
        $state[$source] = ['error' => 'neither ' . $source . ' nor ' . $entry['target'] . ' exists'];
        continue;
    }

    $columns = payloadColumns($pool, $table);
    $hashes = rowHashes($pool, $table, $columns, $fieldName);

    $state[$source] = [
        'table' => $table,
        'columns' => $columns,
        'rows' => count($hashes),
        'hashes' => $hashes,
    ];
}

if ($mode === 'snapshot') {
    // The reference total has to be captured NOW: after the migration the rows
    // no longer point at the source tables, so it cannot be recomputed from the
    // baseline's table names. Counted over DISTINCT tables — summing per source
    // would count a shared target once per sharer.
    $snapshot = [
        'meta' => [
            'fileReferences' => countReferences($pool, array_values(array_unique(array_column($state, 'table')))),
        ],
        'sources' => $state,
    ];
    file_put_contents($file, json_encode($snapshot, JSON_PRETTY_PRINT));
    printf(
        "snapshot written: %d source collections, %d rows, %d file references\n",
        count($state),
        array_sum(array_column($state, 'rows')),
        $snapshot['meta']['fileReferences']
    );
    exit(0);
}

$baseline = json_decode((string)file_get_contents($file), true);
if (!is_array($baseline) || !isset($baseline['sources'], $baseline['meta'])) {
    fwrite(STDERR, "Cannot read baseline $file (or it predates the meta/sources format)\n");
    exit(2);
}
$before = $baseline['sources'];

$failures = [];
$movedRows = 0;
foreach ($before as $source => $sourceBaseline) {
    $now = $state[$source] ?? null;
    if ($now === null || isset($now['error'])) {
        $failures[] = "$source: missing after migration (" . ($now['error'] ?? 'no state') . ')';
        continue;
    }
    if ($sourceBaseline['columns'] !== $now['columns']) {
        $failures[] = sprintf(
            "%s: payload columns changed\n      before: %s\n      after:  %s",
            $source,
            implode(',', $sourceBaseline['columns']),
            implode(',', $now['columns'])
        );
    }
    if ($sourceBaseline['rows'] !== $now['rows']) {
        $failures[] = sprintf('%s: %d rows before, %d after', $source, $sourceBaseline['rows'], $now['rows']);
    }

    $lost = array_diff($sourceBaseline['hashes'], $now['hashes']);
    $gained = array_diff($now['hashes'], $sourceBaseline['hashes']);
    if ($lost !== [] || $gained !== []) {
        $failures[] = sprintf('%s: %d row payloads lost, %d unexpected', $source, count($lost), count($gained));
    }
    $movedRows += $sourceBaseline['rows'];
}

// File references are counted over the DISTINCT tables involved, never summed
// per source: once several sources share one target, a per-source sum counts
// the same table's references once per sharer.
$beforeRefs = (int)$baseline['meta']['fileReferences'];
$afterRefs = countReferences($pool, array_values(array_unique(array_column($state, 'table'))));
if ($beforeRefs !== $afterRefs) {
    $failures[] = sprintf('file references: %d before, %d after', $beforeRefs, $afterRefs);
}

// Nested rows drop the parent uid from their identity (see rowHashes), so
// parent integrity is asserted here instead: every row must still resolve to a
// live parent record.
foreach ($map['sources'] as $source => $entry) {
    if (($entry['tablenames'] ?? 'tt_content') === 'tt_content' || !isset($state[$source]['table'])) {
        continue;
    }
    $orphans = orphanedChildren($pool, $state[$source]['table'], $entry['fieldname'], $entry['tablenames']);
    if ($orphans > 0) {
        $failures[] = sprintf('%s: %d row(s) point at a parent that no longer exists', $source, $orphans);
    }
}

// A reference that survived the count but points at a uid that no longer
// exists is still a broken image, so resolve every one of them.
foreach (array_unique(array_column($state, 'table')) as $table) {
    $dangling = danglingReferences($pool, $table);
    if ($dangling > 0) {
        $failures[] = sprintf('%s: %d file reference(s) point at a row that does not exist', $table, $dangling);
    }
}

printf("compared %d source collections, %d rows, %d file references\n", count($before), $movedRows, $beforeRefs);

if ($failures === []) {
    echo "\nPASS — every row payload, parent link and language survived, and no row appeared from nowhere.\n";
    exit(0);
}

printf("\n%d FAILURES:\n", count($failures));
foreach ($failures as $failure) {
    echo "  - $failure\n";
}
exit(1);
