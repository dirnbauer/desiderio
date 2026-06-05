<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use TYPO3\CMS\Core\Database\ConnectionPool;

final class DatabaseSchemaHelper
{
    /** @var array<string, array<string, true>> */
    private array $tableColumnsCache = [];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

    /**
     * @return array<string, true>
     */
    public function getColumnNames(string $table): array
    {
        if (isset($this->tableColumnsCache[$table])) {
            return $this->tableColumnsCache[$table];
        }

        $columns = [];
        try {
            foreach ($this->connectionPool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table) as $column) {
                $columns[$column->getName()] = true;
            }
        } catch (\Throwable) {
            $columns = [];
        }

        $this->tableColumnsCache[$table] = $columns;
        return $columns;
    }

    public function tableHasColumn(string $table, string $column): bool
    {
        return isset($this->getColumnNames($table)[$column]);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, true> $columns
     * @return array<string, mixed>
     */
    public function filterRow(array $row, array $columns): array
    {
        return array_intersect_key($row, $columns);
    }
}
