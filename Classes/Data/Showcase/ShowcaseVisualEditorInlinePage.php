<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * A disposable, page-scoped collection playground. Existing fixtures supply real
 * content/media; prices always come from the current shared pricing source.
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 */
final class ShowcaseVisualEditorInlinePage
{
    public const string SLUG = '/content-types/visual-editor-inline';

    /** @return ShowcasePage */
    public static function page(): array
    {
        return [
            'title' => 'Inline records in the Visual Editor',
            'navTitle' => 'Inline records',
            'slug' => self::SLUG,
            'parentSlug' => 'content-types',
            'abstract' => 'Try collection entries in cards, compact lists and nested tables. Rich text, shared child tables and media use the same records as backend forms.',
            'description' => 'Try Visual Editor collections in cards, lists, tables and nested pricing plans. Includes rich text, shared records and media examples.',
            'hideInNav' => true,
            'noSearch' => true,
            'pageTsConfig' => "tx_visualeditorenhancements.inlineRecords.enabled = 1\nTCAdefaults.feature_cards_items.title = New card\nRTE.config.tt_content.bodytext.types.text.preset = desiderio",
            'content' => self::content(),
        ];
    }

    /** @return list<ShowcaseBlock> */
    public static function content(): array
    {
        $blocks = [ShowcaseBlocks::block('text', [
            'header' => 'Inline records in the Visual Editor',
            'header_layout' => 2,
            'bodytext' => '<p>Enable Edit collection entries in your Visual Editor user settings. Then use Add entry in an element’s toolbar, or open Entries to rename, reorder, show or hide its entries.</p>'
                . '<p>Adding, removing and moving save immediately; Reset does not undo them. Renaming and visibility save with the page. New entries start hidden; show them with the eye, then Save.</p>'
                . '<p>Use the backend form for required media and for removing entries with nested lists. This page also covers translations and shared tables. '
                . '<a href="{{page:content-types/rte-combinations}}">Explore more rich-text combinations.</a></p>',
        ])];
        // Ordered matrix: cards, RTE, compact/spacious lists, shared tables,
        // two-level tables/pricing and media-only or required-file fallbacks.
        $matrix = [
            ['feature-cards', 'items', 3], ['accordion', 'items', 3],
            ['faq', 'items', 4], ['pricing-faq', 'question_items', 3],
            ['tabs', 'items', 3], ['timeline', 'items', 4],
            ['stats', 'stats', 4], ['stats-inline', 'stats', 3],
            ['product-specs', 'specs', 5], ['definition-list', 'items', 4],
            ['feature-checklist', 'items', 5], ['feature-list', 'items', 3],
            ['changelog', 'items', 3], ['data-table', 'rows', 3],
            ['comparison-table', 'comparison_feature_items', 3],
            ['pricing-three-tier', 'plans', 3], ['team', 'members', 3],
            ['testimonial-grid', 'testimonials', 3], ['gallery', 'items', 3],
            ['logo-cloud', 'logo_items', 4], ['legal-links', 'link_items', 3],
            ['office-hours', 'hours_items', 5],
        ];
        foreach ($matrix as [$name, $field, $count]) {
            $fields = self::fixture($name);
            $items = $fields[$field] ?? [];
            if (!is_array($items)) {
                throw new \RuntimeException('Missing fixture collection: ' . $name . '.' . $field);
            }
            $fields[$field] = array_slice($items, 0, $count);
            switch ($name) {
                case 'definition-list':
                    $fields['header'] = 'Four terms you will meet in Desiderio';
                    break;
                case 'logo-cloud':
                    $fields['header'] = 'Four fictional example clients';
                    $fields['columns'] = '4';
                    break;
                case 'product-specs':
                    $fields['image'] = ShowcaseBlocks::screenshot('backend-visual-editor-7f9bffd8.webp', 'The Visual Editor', 'The TYPO3 Visual Editor with a Desiderio element open.');
                    break;
                case 'data-table':
                    $fields['column_definitions'] = array_slice($fields['column_definitions'], 0, 3);
                    foreach ($fields['rows'] as &$row) {
                        $row['cells'] = array_slice($row['cells'], 0, 3);
                    }
                    unset($row);
                    break;
                case 'comparison-table':
                    $fields['column_items'] = array_slice($fields['column_items'], 0, 2);
                    foreach ($fields['comparison_feature_items'] as &$row) {
                        $row['tier_values'] = array_slice($row['tier_values'], 0, 2);
                    }
                    unset($row);
                    break;
                case 'pricing-three-tier':
                    $fields['plans'] = array_map(static fn(array $plan): array => [
                        'name' => $plan['name'], 'price' => $plan['price'], 'billing_period' => $plan['period'],
                        'description' => '<p>' . $plan['for'] . '</p>',
                        'features' => array_map(static fn(string $text): array => ['text' => $text], $plan['features']),
                        'is_recommended' => $plan['featured'], 'button_text' => $plan['button']['text'], 'button_link' => $plan['button']['link'],
                    ], array_slice(ShowcasePricing::plans(), 0, 3));
                    break;
                case 'legal-links':
                    $fields['link_items'] = [
                        ['label' => 'Privacy', 'link' => '{{page:privacy}}'],
                        ['label' => 'Terms', 'link' => '{{page:terms}}'],
                        ['label' => 'Imprint', 'link' => '{{page:imprint}}'],
                    ];
                    break;
                case 'office-hours':
                    $fields['hours_items'] = array_map(static fn(string $day): array => ['day' => $day, 'time' => $day === 'Friday' ? '8:00–16:00' : '8:00–18:00'], ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
                    break;
            }
            $blocks[] = ShowcaseBlocks::block('desiderio_' . str_replace('-', '', $name), $fields);
        }
        return $blocks;
    }

    /** @return array<string, mixed> */
    private static function fixture(string $name): array
    {
        $path = __DIR__ . '/../../../ContentBlocks/ContentElements/' . $name . '/fixture.json';
        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException('Cannot read showcase fixture: ' . $path);
        }
        $fixture = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($fixture)) {
            throw new \RuntimeException('Invalid showcase fixture: ' . $path);
        }
        unset($fixture['_type']);
        return $fixture;
    }
}
