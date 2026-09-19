<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * Styleguide-specific field alias resolution for Content Block collection seeding.
 */
final readonly class StyleguideCollectionAliasPolicy
{
    private CollectionSchema $schema;

    public function __construct(
        private DatabaseSchemaHelper $databaseSchema,
    ) {
        $this->schema = new CollectionSchema($databaseSchema);
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function collectionHasNestedCollection(array $collection, string $field): bool
    {
        $collections = $collection['collections'] ?? null;
        return is_array($collections) && isset($collections[$field]);
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function resolveNestedCollectionField(string $field, mixed $value, array $collection): ?string
    {
        if (!is_array($value) && !is_string($value)) {
            return null;
        }

        if ($this->collectionHasNestedCollection($collection, $field)) {
            return $field;
        }

        foreach ($this->getNestedCollectionFieldAliases()[$field] ?? [] as $candidate) {
            if ($this->collectionHasNestedCollection($collection, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function shouldSkipLegacyStructuredListField(string $field, array $collection): bool
    {
        if (
            in_array($field, ['links', 'children'], true)
            && (
                $this->schema->hasField($collection, 'link_1')
                || $this->schema->hasField($collection, 'link_1_label')
                || $this->schema->hasField($collection, 'child_1_link')
                || $this->schema->hasField($collection, 'child_1_label')
            )
        ) {
            return true;
        }

        foreach ($this->getNestedCollectionFieldAliases()[$field] ?? [] as $candidate) {
            if ($this->collectionHasNestedCollection($collection, $candidate)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $collection
     */
    public function resolveChildField(string $field, mixed $value, array $collection): ?string
    {
        $declaredFields = $collection['fields'] ?? null;
        $declaredFields = is_array($declaredFields) ? $declaredFields : [];
        $table = $this->schema->table($collection);
        $candidates = $this->childFieldCandidates($field, $value);

        // A field the Content Block declares always wins over a column the
        // shared collection table happens to carry, so the whole candidate
        // list is tried against the definition before the schema.
        foreach ([
            static fn(string $candidate): bool => isset($declaredFields[$candidate]),
            fn(string $candidate): bool => $this->databaseSchema->tableHasColumn($table, $candidate),
        ] as $accepts) {
            foreach ($candidates as $candidate) {
                if ($accepts($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /**
     * The field name itself, then its configured aliases, then the two
     * well-known renames, in the order they should be tried.
     *
     * @return list<string>
     */
    private function childFieldCandidates(string $field, mixed $value): array
    {
        return [
            $field,
            ...($this->getChildFieldAliases()[$field] ?? []),
            ...($field === 'title' ? ['label', 'name'] : []),
            ...(is_scalar($value) && $field === 'link' ? ['url', 'button_link'] : []),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function normalizeCollectionSourceItems(mixed $value, string $field = ''): array
    {
        if (is_string($value)) {
            $separator = match ($field) {
                'tier_values', 'values' => '/\s*,\s*/',
                'row_data', 'cells' => '/\s*\|\s*/',
                default => '/\R/',
            };

            return array_values(array_filter(
                is_array($parts = preg_split($separator, $value)) ? $parts : [],
                static fn(string $item): bool => trim($item) !== ''
            ));
        }

        if (is_array($value)) {
            return array_values($value);
        }

        return [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getScalarFieldAliases(): array
    {
        return [
            'description' => ['description', 'subheadline', 'content', 'description_text', 'info_text', 'body', 'bodytext'],
            'content' => ['content', 'body', 'bodytext', 'subheadline'],
            'logo' => ['brand'],
            'badge' => ['badge_text', 'eyebrow', 'right_badge'],
            'primaryButton' => ['primary_button_text', 'button_text', 'left_button_text', 'cta_text', 'submit_text'],
            'secondaryButton' => ['secondary_button_text', 'right_button_text'],
            'primary_button_link' => ['primary_button_link', 'button_link', 'left_button_link', 'cta_link'],
            'secondary_button_link' => ['secondary_button_link', 'right_button_link'],
            'button_link' => ['button_link', 'primary_button_link', 'cta_link', 'left_button_link', 'right_button_link'],
            'author' => ['author_name', 'author'],
            'role' => ['author_title', 'role', 'position'],
            'company' => ['author_company', 'company_name', 'affiliation'],
            'quote' => ['quote_text', 'quote'],
            'copyright' => ['copyright', 'description_text', 'info_text'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getChildFieldAliases(): array
    {
        return [
            'period' => ['billing_period', 'price_period'],
            'button' => ['button_text'],
            'button_text' => ['button_text', 'button', 'cta_text'],
            'button_link' => ['button_link', 'url', 'link'],
            'url' => ['button_link', 'url', 'link'],
            'link' => ['button_link', 'url', 'link'],
            'features' => ['features_list'],
            'Feature' => ['feature_name', 'name', 'label'],
            'Capability' => ['feature_name', 'name', 'label'],
            'featured' => ['is_featured', 'featured', 'highlighted', 'is_recommended'],
            'is_recommended' => ['is_recommended', 'is_featured', 'featured', 'highlighted'],
            'company' => ['company_name', 'affiliation', 'author_company'],
            'author' => ['author_name', 'name'],
            'role' => ['author_title', 'role', 'position'],
            'quote' => ['quote_text', 'quote'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public function getNestedCollectionFieldAliases(): array
    {
        return [
            'features_list' => ['features', 'feature_items'],
            'feature_list' => ['features', 'feature_items'],
            'features' => ['features', 'feature_items'],
            'specs_text' => ['specs'],
            'specs' => ['specs'],
            'members' => ['members'],
            'people' => ['people'],
            'pages' => ['pages'],
            'tier_values' => ['tier_values'],
            'values' => ['tier_values'],
            'row_data' => ['cells'],
            'cells' => ['cells'],
        ];
    }
}
