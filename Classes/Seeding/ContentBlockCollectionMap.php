<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

final class ContentBlockCollectionMap
{
    /** @var array<string, list<array<string, mixed>>>|null */
    private ?array $collectionsByParentTable = null;

    /** @var list<string>|null */
    private ?array $collectionTableNames = null;

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function getCollectionsByParentTable(): array
    {
        if ($this->collectionsByParentTable !== null) {
            return $this->collectionsByParentTable;
        }

        $map = [];
        foreach (ContentBlockDefinitionRegistry::getDefinitions() as $definition) {
            $this->collectCollectionsByParentTable('tt_content', $definition['collections'], $map);
        }

        $this->collectionsByParentTable = $map;

        return $map;
    }

    /**
     * @return list<string>
     */
    public function getCollectionTableNames(): array
    {
        if ($this->collectionTableNames !== null) {
            return $this->collectionTableNames;
        }

        $tables = [];
        foreach (ContentBlockDefinitionRegistry::getDefinitions() as $definition) {
            $this->collectCollectionTableNames($definition['collections'], $tables);
        }

        $this->collectionTableNames = array_keys($tables);

        return $this->collectionTableNames;
    }

    /**
     * @param array<int|string, mixed> $collections
     * @param array<string, true> $tables
     */
    private function collectCollectionTableNames(array $collections, array &$tables): void
    {
        foreach ($collections as $collection) {
            if (!is_array($collection) || ($collection['relation'] ?? false) === true) {
                continue;
            }
            $table = $collection['table'] ?? null;
            if (is_string($table) && $table !== '') {
                $tables[$table] = true;
            }
            $nestedCollections = $collection['collections'] ?? [];
            if (is_array($nestedCollections)) {
                $this->collectCollectionTableNames($nestedCollections, $tables);
            }
        }
    }

    /**
     * @param array<int|string, mixed> $collections
     * @param array<string, list<array<string, mixed>>> $map
     */
    private function collectCollectionsByParentTable(string $parentTable, array $collections, array &$map): void
    {
        foreach ($collections as $collection) {
            if (!is_array($collection) || ($collection['relation'] ?? false) === true) {
                continue;
            }

            $collectionConfig = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($collection);
            $table = $collectionConfig['table'] ?? null;
            if (!is_string($table) || $table === '') {
                continue;
            }

            $nestedCollections = $collectionConfig['collections'] ?? [];
            if (!is_array($nestedCollections)) {
                $nestedCollections = [];
            }

            $map[$parentTable][] = $collectionConfig;
            $this->collectCollectionsByParentTable($table, $nestedCollections, $map);
        }
    }
}
