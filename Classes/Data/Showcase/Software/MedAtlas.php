<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase\Software;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;

/**
 * /software/medatlas: MedAtlas Austria, an independent research and
 * information project on Austria's public medicine registers.
 *
 * Plan: docs/plans/2026-11-04-software-ai-features-plan.md in the lab, section
 * 4.4 (page copy), 3.2 (title, nav title, abstract), 1.4 (what the page may
 * claim, mandatory disclaimers) and 5.4 (screenshots). Product-specific blocks
 * (research project, guidance for parents, the statistics band, open data)
 * go into 'sections'.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 */
final class MedAtlas
{
    /**
     * @return FeatureDefinition
     */
    public static function definition(): array
    {
        return [
            'slug' => 'medatlas',
            'product' => 'MedAtlas Austria',
            'badge' => 'MedAtlas Austria',
            'title' => 'MedAtlas Austria: medicines and shortages',
            'navTitle' => 'MedAtlas (Medikamente)',
            'description' => 'MedAtlas Austria shows every medicine in Austria, whether it is available and what shares the same substance. A research project on open data.',
            'abstract' => 'Every medicine in Austria, whether it is available, and which products share the same substance. A research project on open data.',
            'hero' => [
                'header' => 'Every medicine in Austria, and its availability',
                'subheadline' => 'Which medicines are authorised, which are in short supply, and what contains the same substance. Built on the public registers, updated every night.',
                'primaryButton' => ['text' => 'Book a demo', 'link' => '{{page:desiderio-powermail/callback}}'],
                'secondaryButton' => ['text' => 'See the strategy', 'link' => '{{page:typo3-v14-strategy}}'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-medatlas-statistics-dark-5dad0c48.webp', 'The data report', 'MedAtlas Austria statistics page in dark mode with current shortage figures and a monthly column chart.', 'Screenshot of a local MedAtlas Austria copy with public register data.'),
            ],
            'problem' => ['header' => 'The pharmacy says: not available', 'content' => '<p>A parent stands at the counter with a prescription for a child. The register knows about the shortage, but nobody says what else contains the same substance or when supply returns.</p>'],
            'tour' => [
                'header' => 'From the substance to the next step',
                'subheadline' => 'Screens from a local MedAtlas Austria copy with public register data.',
                'shots' => [
                    [
                        'tab' => 'Finder',
                        'title' => 'Grouped by availability',
                        'description' => 'Search a product or a substance. Results are grouped into available, limited and not available, with filters for form and strength.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-medatlas-finder-0be95a29.webp', 'Grouped by availability', 'MedAtlas Austria substance finder with results grouped by availability and filters for dosage form and strength.', 'Screenshot of a local MedAtlas Austria copy with public register data.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What MedAtlas Austria answers',
                'items' => [
                    ['icon' => 'search', 'title' => 'Finds what contains the same substance', 'description' => 'Products with the same active substance, grouped into available, limited and not available, with filters for form and strength.'],
                    ['icon' => 'database', 'title' => 'Says where the data comes from', 'description' => 'The BASG shortage feed, the ELGA availability list, EMA leaflets and city open data. Nothing is called available without a signal.'],
                    ['icon' => 'compass', 'title' => 'Tells you the next step', 'description' => 'Call a pharmacy with the registration number, check the stock apps, and let doctor or pharmacist decide.'],
                    ['icon' => 'chart-column', 'title' => 'Reports the big picture', 'description' => 'The data report counts shortages, reasons, export bans and resupply delays after every nightly import.'],
                ],
            ],
            'sections' => [],
            'faq' => [
                'header' => 'Questions about MedAtlas Austria',
                'subheadline' => 'Status, stock, advice and data.',
                'items' => [
                    ['question' => 'Is this official?', 'answer' => 'No. MedAtlas Austria is an independent research project of webconsulting. Every page names its sources; there is no partnership with BASG.'],
                    ['question' => 'Does it know my pharmacy’s stock?', 'answer' => 'No. The availability is nationwide and says nothing about one pharmacy’s stock. The stock apps are linked.'],
                    ['question' => 'Is it medical advice?', 'answer' => 'No. Independent research and information project. Not medical advice. Whether a substitute suits you is for your doctor or pharmacist to decide.'],
                    ['question' => 'Can developers use the data?', 'answer' => 'Yes. A REST API with OpenAPI documentation and CSV exports, with rate limits.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'See MedAtlas on your own data questions',
                'description' => 'We show you the finder and the data report. Like everything we build, it follows the five points of our strategy.',
                'text' => 'Book a demo',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }
}
