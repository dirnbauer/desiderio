<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

/**
 * Reads the shape of one Content Block collection: which table it stores into,
 * which fields it accepts and how they are configured.
 *
 * A field counts as present when the Content Block declares it OR when the
 * shared collection table has a column of that name — collections merged into
 * a shared record type carry columns their own definition does not repeat.
 */
final readonly class CollectionSchema
{
    public function __construct(
        private DatabaseSchemaHelper $databaseSchema,
    ) {}

    /**
     * @param array<string, mixed> $collection
     */
    public function table(array $collection): string
    {
        $table = $collection['table'] ?? '';

        return is_string($table) ? $table : '';
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function hasField(array $collection, string $field): bool
    {
        return isset($collection['fields'][$field])
            || $this->databaseSchema->tableHasColumn($this->table($collection), $field);
    }

    /**
     * @param array<string, mixed> $collection
     * @return array<string, mixed>|null
     */
    public function fieldConfig(array $collection, string $field): ?array
    {
        $fields = $collection['fields'] ?? [];
        if (!is_array($fields)) {
            return null;
        }

        $fieldConfig = $fields[$field] ?? null;

        return is_array($fieldConfig) ? ContentBlockDefinitionRegistry::normalizeStringKeyedArray($fieldConfig) : null;
    }
}
