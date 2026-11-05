<?php

declare(strict_types=1);

// Read-only gate. Run from the installed TYPO3 project root, before changing YAML limits.
// Counts hidden and translated live rows too; excludes deleted/workspace rows.
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

$loader = require getcwd() . '/vendor/autoload.php';
SystemEnvironmentBuilder::run(1, SystemEnvironmentBuilder::REQUESTTYPE_CLI);
Bootstrap::init($loader);
Bootstrap::loadExtTables();
$pool = GeneralUtility::makeInstance(ConnectionPool::class);
$reports = [];
$walk = function (array $collections, string $parentTable, ?string $ctype = null, array $yamlFields = []) use (&$walk, &$reports, $pool): void {
    foreach ($collections as $collection) {
        $field = $collection['column'];
        $yaml = array_find($yamlFields, static fn(array $candidate): bool => ($candidate['table'] ?? $candidate['foreign_table'] ?? $candidate['identifier'] ?? '') === $collection['table']) ?? [];
        $config = $GLOBALS['TCA'][$parentTable]['columns'][$field]['config'] ?? [];
        $table = $collection['table'];
        if (($config['type'] ?? '') !== 'inline' || empty($config['foreign_field'])) {
            throw new RuntimeException("Unresolved relation $parentTable.$field");
        }
        $connection = $pool->getConnectionForTable($parentTable);
        $q = $connection->quoteIdentifier(...);
        $join = ['c.' . $q($config['foreign_field']) . ' = p.uid', 'c.deleted = 0', 'c.t3ver_wsid = 0'];
        if (!empty($config['foreign_table_field'])) {
            $join[] = 'c.' . $q($config['foreign_table_field']) . ' = ' . $connection->quote($parentTable);
        }
        foreach ($config['foreign_match_fields'] ?? [] as $key => $value) {
            $join[] = 'c.' . $q($key) . ' = ' . $connection->quote((string)$value);
        }
        $where = 'p.deleted = 0 AND p.t3ver_wsid = 0';
        if ($ctype !== null) {
            $where .= ' AND p.CType = ' . $connection->quote($ctype);
        }
        $sql = 'SELECT p.uid, p.pid, p.sys_language_uid, COUNT(c.uid) AS children FROM ' . $q($parentTable)
            . ' p LEFT JOIN ' . $q($table) . ' c ON ' . implode(' AND ', $join)
            . ' WHERE ' . $where . ' GROUP BY p.uid, p.pid, p.sys_language_uid';
        $rows = $connection->executeQuery($sql)->fetchAllAssociative();
        $min = (int)($yaml['minItems'] ?? $yaml['minitems'] ?? 0);
        $max = (int)($yaml['maxItems'] ?? $yaml['maxitems'] ?? 0);
        $violations = array_values(array_filter($rows, static fn(array $row): bool => (int)$row['children'] < $min || ($max > 0 && (int)$row['children'] > $max)));
        $reports[] = ['parentTable' => $parentTable, 'ctype' => $ctype, 'field' => $field, 'table' => $table,
            'min' => $min, 'max' => $max, 'parents' => count($rows),
            'languages' => array_count_values(array_column($rows, 'sys_language_uid')), 'violations' => $violations, 'sql' => $sql];
        $walk($collection['collections'], $table, null, $yaml['fields'] ?? []);
    }
};
// Required File fields use minitems as well; `required` was ignored by the File parser.
$auditFiles = function (array $definition, string $table, ?string $ctype = null) use (&$auditFiles, &$reports, $pool): void {
    foreach ($definition['fields'] as $name => $field) {
        $min = (int)($field['minitems'] ?? (!empty($field['required']) ? 1 : 0));
        if (($field['type'] ?? '') !== 'File' || $min === 0) {
            continue;
        }
        $column = $field['storageIdentifier'] ?? $name;
        $connection = $pool->getConnectionForTable($table);
        $sql = 'SELECT p.uid,p.pid,p.sys_language_uid,COUNT(f.uid) AS children FROM ' . $connection->quoteIdentifier($table)
            . ' p LEFT JOIN sys_file_reference f ON f.uid_foreign=p.uid AND f.tablenames=' . $connection->quote($table)
            . ' AND f.fieldname=' . $connection->quote($column) . ' AND f.deleted=0 AND f.t3ver_wsid=0'
            . ' WHERE p.deleted=0 AND p.t3ver_wsid=0' . ($ctype !== null ? ' AND p.CType=' . $connection->quote($ctype) : '')
            . ' GROUP BY p.uid,p.pid,p.sys_language_uid';
        $rows = $connection->executeQuery($sql)->fetchAllAssociative();
        $max = (int)($field['maxitems'] ?? 0);
        $reports[] = ['kind' => 'file', 'parentTable' => $table, 'ctype' => $ctype, 'field' => $column, 'table' => 'sys_file_reference',
            'min' => $min, 'max' => $max, 'parents' => count($rows), 'languages' => array_count_values(array_column($rows, 'sys_language_uid')),
            'violations' => array_values(array_filter($rows, static fn(array $row): bool => (int)$row['children'] < $min || ($max > 0 && (int)$row['children'] > $max))), 'sql' => $sql];
    }
    foreach ($definition['collections'] as $child) {
        $auditFiles($child, $child['table']);
    }
};
foreach (glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/config.yaml') as $path) {
    $yaml = Yaml::parseFile($path);
    $ctype = $yaml['typeName'] ?? 'desiderio_' . str_replace('-', '', basename(dirname($path)));
    $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig($yaml);
    $walk($definition['collections'], 'tt_content', $ctype, $yaml['fields'] ?? []);
    $auditFiles($definition, 'tt_content', $ctype);
}
echo json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
exit(array_any($reports, static fn(array $report): bool => $report['violations'] !== []) ? 1 : 0);
