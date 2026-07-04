<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Library\ElementCatalog;

/**
 * Collection map across ALL element catalog hosts (desiderio + innesto).
 * ContentBlockCollectionMap only knows Desiderio's own content blocks; the
 * element library seeder also has to clean up child tables of foreign host
 * extensions, so this builds the same parent-table map from the catalog.
 */
final class ElementCatalogDefinitions
{
    public function __construct(
        private readonly ElementCatalog $elementCatalog,
    ) {}

    /**
     * @return array<string, list<array<string, mixed>>>
     */
    public function getCollectionsByParentTable(): array
    {
        $map = [];
        foreach ($this->elementCatalog->getElements() as $element) {
            $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig($element['config']);
            $this->collect('tt_content', $definition['collections'], $map);
        }
        return $map;
    }

    /**
     * @param array<int|string, mixed> $collections
     * @param array<string, list<array<string, mixed>>> $map
     */
    private function collect(string $parentTable, array $collections, array &$map): void
    {
        foreach ($collections as $collection) {
            if (!is_array($collection)) {
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
            $this->collect($table, $nestedCollections, $map);
        }
    }
}
