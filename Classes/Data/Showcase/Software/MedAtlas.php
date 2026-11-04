<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase\Software;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseStrategyPages;

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
 * Every figure comes from the MedAtlas data report after the import of
 * 4 November 2026, 10:10, and says so: the numbers age, and a reseed does not
 * refresh them. The app has no feature for children; the parents' section is
 * guidance built on the features every visitor has. The page never calls the
 * project official, never claims pharmacy stock and never gives medical
 * advice, and it carries the app's own disclaimers word for word.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 * @phpstan-import-type ShowcaseBlock from \Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks
 * @phpstan-import-type ShowcaseMedia from \Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks
 */
final class MedAtlas
{
    private const string SOURCE = 'Screenshot of a local MedAtlas Austria copy with public register data of 4 November 2026.';

    /**
     * The statistics blocks sit on the primary surface, the dark band the
     * house presets paint in light mode (in dark mode the band turns light).
     * On that surface the chart blocks draw in the band's ink, not in the
     * preset's chart colours (chart-bar and chart-donut frontend.css), so the
     * bars read in both modes.
     */
    private const string SURFACE = 'primary';

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
                'header' => 'Every medicine in Austria and its supply status',
                'subheadline' => 'Which medicines are authorised, which are in short supply, and what contains the same substance. Shortage and availability data are updated every night.',
                'primaryButton' => ['text' => 'Book a demo', 'link' => '{{page:desiderio-powermail/callback}}'],
                'secondaryButton' => ['text' => 'See the strategy', 'link' => '{{page:typo3-v14-strategy}}'],
                'image' => self::shot('frontend-feature-medatlas-statistics-dark-5dad0c48.webp', 'Shortages in Austria, in figures', 'MedAtlas data report in dark mode: 622 active shortages, 287 substances, a 24-month column chart and the AGES yearly table.'),
            ],
            'problem' => [
                'header' => 'The pharmacy says: not available',
                'content' => '<p>A parent with a prescription for a child hears: not available. Nobody says what else contains the same substance, or when supply returns.</p>',
            ],
            'tour' => [
                'header' => 'From the substance to the next step',
                'subheadline' => 'Screens from a local MedAtlas Austria copy with public register data of 4 November 2026. The interface is in German.',
                'shots' => [
                    [
                        'tab' => 'Start',
                        'title' => 'Today’s supply status on the start page',
                        'description' => 'Search by product, substance, registration number or PZN. Beside it: 622 active shortages and 287 substances affected on 4 November 2026.',
                        'image' => self::shot('frontend-feature-medatlas-home-a00945dc.webp', 'Today’s supply status on the start page', 'MedAtlas start page with a search field and the supply status of 4 November 2026: 622 shortages, 287 substances, 140 reports.'),
                    ],
                    [
                        'tab' => 'Finder',
                        'title' => 'Grouped by availability',
                        'description' => 'Ibuprofen, filtered to oral suspensions: 6 of 113 products, 4 of them available. Chips narrow the list by form and strength.',
                        'image' => self::shot('frontend-feature-medatlas-finder-0be95a29.webp', 'Grouped by availability', 'Substance finder for ibuprofen, filtered to oral suspensions: 4 available products, with filters for form and strength.'),
                    ],
                    [
                        'tab' => 'Shortage',
                        'title' => 'Status, resupply date and alternatives',
                        'description' => 'Limited supply with an export ban since 12 December 2025. A timeline shows the expected resupply. Available products with the same substance follow.',
                        'image' => self::shot('frontend-feature-medatlas-shortage-detail-2064ac9f.webp', 'Status, resupply date and alternatives', 'Shortage report for Tamiflu oral suspension with status, export ban, a resupply timeline and available alternatives.'),
                    ],
                    [
                        'tab' => 'Next steps',
                        'title' => 'Call, check, then ask',
                        'description' => 'Products that are not available, the availability note and the Next steps box: call a pharmacy, check ApoApp or ApoScout, ask a doctor or pharmacist.',
                        'image' => self::shot('frontend-feature-medatlas-next-steps-1d250fdd.webp', 'Call, check, then ask', 'Finder results that are not available, the availability note and the Next steps box with pharmacy and stock app links.'),
                    ],
                    [
                        'tab' => 'Alerts',
                        'title' => 'An email when the status changes',
                        'description' => 'Watch a product or a substance. After each nightly import, MedAtlas compares the status and sends one email with every change.',
                        'image' => self::shot('frontend-feature-medatlas-alerts-cb4974fb.webp', 'An email when the status changes', 'Alerts page explaining the three steps: watch a product or substance, the nightly comparison, one email per change.'),
                    ],
                    [
                        'tab' => 'My medicines',
                        'title' => 'A list for the doctor',
                        'description' => 'Your medicines on one list, each with today’s supply status, and a PDF for the next appointment. The screen shows the example list.',
                        'image' => self::shot('frontend-feature-medatlas-my-medications-b8e749d8.webp', 'A list for the doctor', 'My medicines example list with three products and their supply status, next to a preview of the PDF.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'What MedAtlas Austria answers',
                'items' => [
                    ['icon' => 'search', 'title' => 'Finds what contains the same substance', 'description' => 'Products with the same active substance, grouped into available, limited and not available, with filters for form and strength.'],
                    ['icon' => 'database', 'title' => 'Says where the data comes from', 'description' => 'The BASG shortage feed, the ELGA availability list, EMA leaflets and city open data. Nothing is called available without a signal.'],
                    ['icon' => 'compass', 'title' => 'Tells you the next step', 'description' => 'Call a pharmacy with the registration number, check the stock apps, and let the doctor or pharmacist decide.'],
                    ['icon' => 'chart-column', 'title' => 'Reports the big picture', 'description' => 'The data report counts shortages, reasons, export bans and resupply delays after every nightly import.'],
                ],
            ],
            'sections' => [...self::researchSection(), ...self::parentsSection(), ...self::statisticsSection(), self::openDataSection()],
            'faq' => [
                'header' => 'Questions about MedAtlas Austria',
                'subheadline' => 'Status, stock, advice and data.',
                'items' => [
                    ['question' => 'Is MedAtlas Austria official?', 'answer' => 'No. It is an independent research and information project by webconsulting. Every page names its sources. There is no partnership with BASG, AGES or ELGA.'],
                    ['question' => 'Does it know my pharmacy’s stock?', 'answer' => 'No. The availability comes from the ELGA list and the BASG shortage register. It applies to all of Austria and says nothing about one pharmacy’s stock. The finder links the stock apps ApoApp and ApoScout.'],
                    ['question' => 'Is this medical advice?', 'answer' => 'No. Independent research and information project. Not medical advice. Whether a substitute suits you is for your doctor or pharmacist to decide. Texts written with AI carry a label.'],
                    ['question' => 'Can developers use the data?', 'answer' => 'MedAtlas has a REST API with 14 read endpoints, OpenAPI 3.1 documentation and CSV or XLSX exports, with rate limits. Book a demo and we show it to you.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'See the finder and the data report',
                'description' => ShowcaseStrategyPages::standardLine('MedAtlas Austria'),
                'text' => 'Book a demo',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }

    /**
     * The research question behind the project and how a report is made.
     *
     * @return list<ShowcaseBlock>
     */
    private static function researchSection(): array
    {
        return [
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => 'Research project',
                'header' => 'Not only a list of what is missing',
                'subheadline' => 'It asks how shortages develop, which causes studies name and how Austria compares in the EU. The first reports are in progress: AI research, cited sources, a human check.',
                'variant' => 'center',
            ]),
        ];
    }

    /**
     * Guidance for parents, built on the features every visitor has: the app
     * has no view for children (plan, Q6). The callout repeats the app's own
     * disclaimer and availability note word for word.
     *
     * @return list<ShowcaseBlock>
     */
    private static function parentsSection(): array
    {
        return [
            ShowcaseBlocks::block('desiderio_featurenumbered', [
                'header' => 'What a parent can do tonight',
                'subheadline' => 'MedAtlas has no special view for children. These four steps use the features every visitor has.',
                'items' => [
                    ['title' => 'Search the substance, not the brand', 'description' => '<p>Type the active substance, for example ibuprofen. Then filter by a form a child can take, such as an oral suspension or suppositories.</p>'],
                    ['title' => 'Call a pharmacy with the number', 'description' => '<p>Ask for a product from the available group. Its registration number helps the pharmacy find it.</p>'],
                    ['title' => 'Check the stock apps', 'description' => '<p>ApoApp and ApoScout show whether a pharmacy has a pack in stock. Not every pharmacy takes part.</p>'],
                    ['title' => 'Ask before you switch', 'description' => '<p>Ask the doctor or pharmacist whether the alternative suits your child.</p>'],
                ],
            ]),
            ShowcaseBlocks::block('desiderio_callout', [
                'header' => 'Not medical advice',
                'content' => '<p>Whether a substitute suits you is for your doctor or pharmacist to decide. Independent research and information project. Not medical advice.</p>'
                    . '<p>The availability data comes from the ELGA ASP list and the BASG shortage register. It applies to all of Austria and says nothing about whether a single pharmacy has the pack in stock.</p>',
                'variant' => 'info',
            ]),
        ];
    }

    /**
     * The data report as the page's showpiece: the four figures of the day,
     * three charts and the screens they come from, all on the primary surface.
     * Figures: MedAtlas /de/statistics after the import of 4 November 2026,
     * 10:10 (866 listed reports).
     *
     * @return list<ShowcaseBlock>
     */
    private static function statisticsSection(): array
    {
        $months = ['January' => 15, 'February' => 32, 'March' => 24, 'April' => 38, 'May' => 19, 'June' => 30, 'July' => 35, 'August' => 116, 'September' => 118, 'October' => 133];
        $reasons = ['Manufacturing delay' => 197, 'Manufacturing capacity' => 182, 'Increased demand' => 142];

        return [
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => 'The data report',
                'header' => 'Counted every night from the public registers',
                'subheadline' => 'Shortages, reasons, export bans and resupply delays, as the registers list them. Every chart has a table view and a CSV download in MedAtlas.',
                'variant' => 'center',
                'frame_class' => self::SURFACE,
            ]),
            ShowcaseBlocks::block('desiderio_statscounter', [
                'eyebrow' => '4 November 2026',
                'header' => 'The situation on one day',
                'columns' => '4',
                'counters' => [
                    ['counter_target' => '622', 'suffix' => '', 'label' => 'Active shortages', 'description' => '<p>520 not available and 102 limited.</p>'],
                    ['counter_target' => '287', 'suffix' => '', 'label' => 'Substances affected', 'description' => '<p>Active substances with at least one active shortage.</p>'],
                    ['counter_target' => '63', 'suffix' => '%', 'label' => 'Packages deliverable', 'description' => '<p>13,510 of 21,339 packages in the ELGA list.</p>'],
                    ['counter_target' => '272', 'suffix' => '', 'label' => 'Days of planned outage', 'description' => '<p>The median from start to expected resupply, across 360 reports with both dates.</p>'],
                ],
                'frame_class' => self::SURFACE,
            ]),
            ShowcaseBlocks::block('desiderio_chartbar', [
                'header' => 'Shortage reports per month, 2026',
                'description' => '<p>Figures from the public registers, counted as listed on 4 November 2026. Entries BASG removes drop out of the count, so older months look smaller.</p>',
                ...self::bars($months),
                'unit' => 'reports',
                'series_name' => 'Reports by month of report, as listed on 4 November 2026',
                'orientation' => 'vertical',
                'color_variant' => 'primary',
                'show_grid' => 1,
                'frame_class' => self::SURFACE,
            ]),
            ShowcaseBlocks::block('desiderio_chartbar', [
                'header' => 'The three most common reasons',
                'description' => '<p>Reasons given in the 866 reports listed on 4 November 2026. Manufacturing delay leads with 22.7 %.</p>',
                ...self::bars($reasons),
                'unit' => 'reports',
                'series_name' => 'Reports by reason, as listed on 4 November 2026',
                'orientation' => 'horizontal',
                'color_variant' => 'primary',
                'show_grid' => 1,
                'frame_class' => self::SURFACE,
            ]),
            ShowcaseBlocks::block('desiderio_chartdonut', [
                'header' => 'Reports with an export ban',
                'description' => '<p>413 of the 866 reports listed on 4 November 2026 carry a ban on parallel export.</p>',
                'segments' => [
                    ['label' => 'With an export ban', 'value' => 413, 'unit' => 'reports'],
                    ['label' => 'Without an export ban', 'value' => 453, 'unit' => 'reports'],
                ],
                'center_value' => '47.7 %',
                'center_label' => 'export ban',
                'show_legend' => 1,
                'frame_class' => self::SURFACE,
            ]),
            ShowcaseBlocks::block('desiderio_gallery', [
                'header' => 'The data report in MedAtlas',
                'subheadline' => 'The statistics page in dark and light mode, with the figures of 4 November 2026.',
                'columns' => '4',
                'items' => [
                    [
                        'title' => 'The figures of the day',
                        'description' => 'Four figures of the day, the reports per month over 24 months and the AGES yearly figures as of 1 June 2026.',
                        'link' => '',
                        'image' => self::shot('frontend-feature-medatlas-statistics-dark-5dad0c48.webp', 'The figures of the day', 'MedAtlas data report in dark mode: 622 active shortages, 287 substances, a 24-month column chart and the AGES yearly table.'),
                    ],
                    [
                        'title' => 'Reports per month',
                        'description' => '15 to 133 reports per month since August 2026, with a table view and a CSV download.',
                        'link' => '',
                        'image' => self::shot('frontend-feature-medatlas-statistics-chart-dark-10b6c4c7.webp', 'Reports per month', 'Column chart of shortage reports per month over 24 months, in dark mode, rising to 133 in October 2026.'),
                    ],
                    [
                        'title' => 'Reasons, export bans and delays',
                        'description' => 'Reasons, the share with an export ban, the median planned outage of 272 days and 53 overdue resupply dates.',
                        'link' => '',
                        'image' => self::shot('frontend-feature-medatlas-statistics-reasons-dark-4e772802.webp', 'Reasons, export bans and delays', 'Data report panels in dark mode: reasons for shortages, export-ban share of 47.7 %, median outage and overdue resupply.'),
                    ],
                    [
                        'title' => 'The same page in light mode',
                        'description' => 'The data report follows the light or dark setting of the device.',
                        'link' => '',
                        'image' => self::shot('frontend-feature-medatlas-statistics-light-c8d71d2d.webp', 'The same page in light mode', 'MedAtlas data report in light mode with the figures of the day, the monthly column chart and the AGES yearly table.'),
                    ],
                ],
                'frame_class' => self::SURFACE,
            ]),
        ];
    }

    /**
     * The sources, with their terms, in place of a thank-you block: the
     * thank-you is for third-party extensions (plan, 4.4).
     *
     * @return ShowcaseBlock
     */
    private static function openDataSection(): array
    {
        return ShowcaseBlocks::block('desiderio_featurecards', [
            'eyebrow' => 'Open data',
            'header' => 'Where the data comes from',
            'subheadline' => 'Three sources are read every night; the leaflets load when a medicine page needs them. Every page in MedAtlas names its source.',
            'items' => [
                ['title' => 'BASG shortage feed', 'description' => 'Shortage reports and export bans of the Federal Office for Safety in Health Care, read every night. MedAtlas is not an official publication.'],
                ['title' => 'ELGA availability list', 'description' => 'The ASP list marks each package as deliverable or not. It is the only signal that lets MedAtlas say available, read every night.'],
                ['title' => 'City open data', 'description' => 'Pharmacy addresses from the open data of Vienna, Graz, Linz and Innsbruck under CC BY 4.0. They contain no stock data.'],
                ['title' => 'EMA leaflets', 'description' => 'German package leaflets of the European Medicines Agency for medicines authorised across the EU, used with acknowledgement.'],
            ],
        ]);
    }

    /**
     * The two fields a bar chart reads: "Label | Value" rows for the data
     * table and the same values as JSON for the drawing.
     *
     * @param array<string, int> $values
     * @return array{bars: string, chart_data: string}
     */
    private static function bars(array $values): array
    {
        $rows = [];
        $data = [];
        foreach ($values as $label => $value) {
            $rows[] = $label . ' | ' . $value;
            $data[] = ['label' => $label, 'value' => $value];
        }

        return ['bars' => implode("\n", $rows), 'chart_data' => json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE)];
    }

    /**
     * @return ShowcaseMedia
     */
    private static function shot(string $filename, string $title, string $alternative): array
    {
        return ShowcaseBlocks::screenshot($filename, $title, $alternative, self::SOURCE);
    }
}
