<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase\Software;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;

/**
 * /software/capex-one: CapexOne Plus, capital-project steering with gate
 * packs and a business case checked live.
 *
 * Plan: docs/plans/2026-11-04-software-ai-features-plan.md in the lab, section
 * 4.5 (page copy, taken from capex-one.com only), 3.2 (title, nav title,
 * abstract), 1.4 (what the page may claim), D14 (shared savings) and 5.5
 * (screenshots). Product-specific blocks (the five things, the savings,
 * processing, integrations, the API section, the offer, shared savings) go
 * into 'sections'.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 */
final class CapexOne
{
    /**
     * @return FeatureDefinition
     */
    public static function definition(): array
    {
        return [
            'slug' => 'capex-one',
            'product' => 'CapexOne Plus',
            'badge' => 'CapexOne Plus',
            'title' => 'CapexOne Plus: capital projects, governed',
            'navTitle' => 'CapexOne Plus',
            'description' => 'CapexOne Plus checks every capital project against its business case, live, and builds the pack for every gate review.',
            'abstract' => 'Six-phase stage-gate, gate packs, a business case checked live, a financial ledger with four-eyes approval, and an API agents can read.',
            'hero' => [
                'header' => 'Decide on live numbers, not last month’s slides',
                'subheadline' => 'CapexOne Plus checks every capital project against its business case, live from project to company, and builds the pack for every gate.',
                'primaryButton' => ['text' => 'Book a demo', 'link' => '{{page:desiderio-powermail/callback}}'],
                'secondaryButton' => ['text' => 'See all software', 'link' => '{{page:software}}'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-value-portfolio-451057e9.webp', 'Where the capital goes', 'CapexOne Plus portfolio value view with approved, committed and forecast amounts for fictional capital projects.', 'Screenshot of the CapexOne Plus demo with fictional data.'),
            ],
            'problem' => ['header' => 'What gate preparation really costs', 'content' => '<p>Every gate review ties up a project team for 30 to 50 hours: exporting numbers, updating the business case, chasing documents, building slides. At €100 an hour that is €3,000 to €5,000 per gate.</p>'],
            'tour' => [
                'header' => 'From the first plan to the proven return',
                'subheadline' => 'Screens from the CapexOne Plus demo with fictional data.',
                'shots' => [
                    [
                        'tab' => 'Gate pack',
                        'title' => 'The gate pack builds itself',
                        'description' => 'Business case, budget against actuals, risks and evidence for every deliverable, compiled from live data with a readiness score.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-gate-pack-c6e1c51d.webp', 'The gate pack builds itself', 'CapexOne Plus gate pack with a readiness score and evidence per deliverable for a fictional project.', 'Screenshot of the CapexOne Plus demo with fictional data.'),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'How CapexOne Plus works',
                'items' => [
                    ['icon' => 'list-checks', 'title' => 'Six phases, six gates', 'description' => 'Every project follows the same stage-gate path. A phase closes when its gate passes.'],
                    ['icon' => 'users', 'title' => 'Decisions on the record', 'description' => 'A gate needs two approving votes, and one rejection sends it back. Every vote and comment stays in the history.'],
                    ['icon' => 'database', 'title' => 'One source of truth', 'description' => 'Budget items, actuals, change orders, risks and documents sit next to their phase. Reports come from the data the team works with.'],
                    ['icon' => 'sparkles', 'title' => 'AI where it saves time', 'description' => 'An assistant, document extraction and risk suggestions on EU-based models without data retention. People make the decisions.'],
                ],
            ],
            'sections' => [],
            'faq' => [
                'header' => 'What CFOs and PMOs ask first',
                'subheadline' => 'Gate packs, the Planner, your ERP and the AI.',
                'items' => [
                    ['question' => 'How does the gate pack come together?', 'answer' => 'It reads what the gate needs from the project: business case, budget against actuals, change orders, risks and deliverables. The result is a versioned PDF with an evidence bundle.'],
                    ['question' => 'Is the Planner’s AI a black box?', 'answer' => 'No. It answers fixed questions about your description, each with a confidence score, and asks when unsure. Durations and costs come from a documented rule set.'],
                    ['question' => 'Do we have to replace our ERP?', 'answer' => 'No. Budgets, commitments and actuals can come from SAP or other ERP systems. CapexOne Plus adds the project view, the business case and the gates.'],
                    ['question' => 'Where does the AI run?', 'answer' => 'On EU-based models without data retention. It reads invoices, suggests risks, drafts summaries and answers questions about a project. People make the decisions.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'Walk into the meeting, pack already done',
                'description' => 'See CapexOne Plus on demo data. Like everything we build, it follows the five points of our strategy.',
                'text' => 'Book a demo',
                'link' => '{{page:desiderio-powermail/callback}}',
            ],
        ];
    }
}
