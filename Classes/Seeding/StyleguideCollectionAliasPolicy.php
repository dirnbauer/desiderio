<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * Styleguide-specific field alias resolution for Content Block collection seeding.
 */
final class StyleguideCollectionAliasPolicy
{
    public function __construct(
        private readonly DatabaseSchemaHelper $databaseSchema,
    ) {}

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
                $this->collectionHasField($collection, 'link_1')
                || $this->collectionHasField($collection, 'link_1_label')
                || $this->collectionHasField($collection, 'child_1_link')
                || $this->collectionHasField($collection, 'child_1_label')
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
        $fields = $collection['fields'] ?? null;
        if (is_array($fields) && isset($fields[$field])) {
            return $field;
        }

        foreach ($this->getChildFieldAliases()[$field] ?? [] as $candidate) {
            if (is_array($fields) && isset($fields[$candidate])) {
                return $candidate;
            }
        }

        if ($field === 'title') {
            foreach (['label', 'name'] as $candidate) {
                if (is_array($fields) && isset($fields[$candidate])) {
                    return $candidate;
                }
            }
        }

        if (is_scalar($value) && $field === 'link') {
            foreach (['url', 'button_link'] as $candidate) {
                if (is_array($fields) && isset($fields[$candidate])) {
                    return $candidate;
                }
            }
        }

        if ($this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $field)) {
            return $field;
        }

        foreach ($this->getChildFieldAliases()[$field] ?? [] as $candidate) {
            if ($this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $candidate)) {
                return $candidate;
            }
        }

        if ($field === 'title') {
            foreach (['label', 'name'] as $candidate) {
                if ($this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $candidate)) {
                    return $candidate;
                }
            }
        }

        if (is_scalar($value) && $field === 'link') {
            foreach (['url', 'button_link'] as $candidate) {
                if ($this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
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
                static fn (string $item): bool => trim($item) !== ''
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

    /**
     * @param array<string, mixed> $collection
     */
    private function collectionHasField(array $collection, string $field): bool
    {
        $fields = $collection['fields'] ?? null;
        return (is_array($fields) && isset($fields[$field]))
            || $this->databaseSchema->tableHasColumn($this->getCollectionTable($collection), $field);
    }

    /**
     * @param array<string, mixed> $collection
     */
    private function getCollectionTable(array $collection): string
    {
        $table = $collection['table'] ?? '';
        return is_string($table) ? $table : '';
    }
}
