<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * Spreads a fixture's list of links over the numbered link slots a collection
 * declares.
 *
 * Fixtures write links as a list (`links: ["Docs|/docs", "Blog"]`), but the
 * shared collection tables store them in fixed slots (`link_1_label`,
 * `link_1`, `link_2_label`, …). This fills the slots that exist and are still
 * empty, and stops at the first slot the collection does not declare.
 */
final readonly class FixtureLinkSlots
{
    public function __construct(
        private CollectionSchema $schema,
        private FixtureFieldNormalizer $fieldNormalizer,
        private StyleguideDemoValueGenerator $demoValueGenerator,
    ) {}

    /**
     * @param array<string, mixed> $normalizedItem
     * @param array<string, mixed> $sourceItem
     * @param array<string, mixed> $collection
     * @return array<string, mixed>
     */
    public function populate(array $normalizedItem, array $sourceItem, array $collection): array
    {
        $normalizedItem = $this->fill($normalizedItem, $sourceItem['links'] ?? null, $collection, 'link_%d_label', 'link_%d');

        return $this->fill($normalizedItem, $sourceItem['children'] ?? null, $collection, 'child_%d_label', 'child_%d_link');
    }

    /**
     * @param array<string, mixed> $normalizedItem
     * @param array<string, mixed> $collection
     * @return array<string, mixed>
     */
    private function fill(array $normalizedItem, mixed $sourceLinks, array $collection, string $labelPattern, string $linkPattern): array
    {
        if ($sourceLinks === null || $sourceLinks === '') {
            return $normalizedItem;
        }

        if (is_string($sourceLinks)) {
            $splitLinks = preg_split('/\R/', $sourceLinks);
            $sourceLinks = is_array($splitLinks) ? $splitLinks : [];
        }

        if (!is_array($sourceLinks)) {
            return $normalizedItem;
        }

        $slot = 1;
        foreach ($sourceLinks as $sourceLink) {
            $labelField = sprintf($labelPattern, $slot);
            $linkField = sprintf($linkPattern, $slot);
            if (!$this->schema->hasField($collection, $labelField) && !$this->schema->hasField($collection, $linkField)) {
                break;
            }

            [$label, $link] = $this->split($sourceLink);
            if ($label !== '' && $this->schema->hasField($collection, $labelField) && $this->fieldNormalizer->isEmptySeedValue($normalizedItem[$labelField] ?? null)) {
                $normalizedItem[$labelField] = $label;
            }
            if ($link !== '' && $this->schema->hasField($collection, $linkField) && $this->fieldNormalizer->isEmptySeedValue($normalizedItem[$linkField] ?? null)) {
                $normalizedItem[$linkField] = $link;
            }

            $slot++;
        }

        return $normalizedItem;
    }

    /**
     * One fixture link as a label/target pair. A link without a target gets a
     * demo URL derived from its label, so no slot renders as a dead anchor.
     *
     * @return array{0: string, 1: string}
     */
    private function split(mixed $sourceLink): array
    {
        if (is_array($sourceLink)) {
            $label = trim((string)($sourceLink['label'] ?? $sourceLink['title'] ?? $sourceLink['text'] ?? $sourceLink['name'] ?? ''));
            $link = trim((string)($sourceLink['link'] ?? $sourceLink['url'] ?? $sourceLink['href'] ?? ''));

            return [$label, $link !== '' ? $link : $this->demoValueGenerator->buildDemoUrl($label)];
        }

        $value = trim((string)$sourceLink);
        if ($value === '') {
            return ['', ''];
        }

        if (str_contains($value, '|')) {
            [$label, $link] = array_pad(array_map(trim(...), explode('|', $value, 2)), 2, '');

            return [$label, $link !== '' ? $link : $this->demoValueGenerator->buildDemoUrl($label)];
        }

        return [$value, $this->demoValueGenerator->buildDemoUrl($value)];
    }
}
