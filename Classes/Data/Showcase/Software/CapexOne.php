<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase\Software;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseStrategyPages;

/**
 * /software/capex-one: CapexOne Plus, capital-project steering with gate
 * packs and a business case checked live.
 *
 * Plan: docs/plans/2026-11-04-software-ai-features-plan.md in the lab, section
 * 4.5 (page copy, taken from capex-one.com only, never from the outdated promo
 * site), 3.2 (title, nav title, abstract), 1.4 (what the page may claim), D14
 * (shared savings) and 5.5 (screenshots). Every screen shows the local demo:
 * the company ACME Foods and all figures on it are fictional.
 *
 * Numbers and where they come from:
 *  - 30–50 hours and €3,000–5,000 per gate review, 90–150 hours for a project
 *    with three gates, and the portfolio example (700 projects, 3 gates, 40 h,
 *    €100 an hour = €8.4 million, 84,000 hours, about 49 people at 1,700 hours
 *    a year, €4,000 per gate): the savings section of capex-one.com. They are
 *    estimates, so the page calls them that.
 *  - 3 % / 1 % / 2 %: the shared-savings model (D14). A goal and a pricing
 *    approach, never a measured result or a guarantee, and kept in its own
 *    block apart from the product figures.
 *  - 173 endpoints in 26 categories, API keys as Bearer tokens, rate limits:
 *    the API reference on capex-one.com, marked "Developer preview" there.
 *  - 11 read-only tools of the in-app agent: the product repository.
 *  - The Rauch quote: his X post of 12 February 2026, as the capex-one.com
 *    landing page quoted it from February to September 2026.
 *
 * @phpstan-import-type FeatureDefinition from \Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions
 */
final class CapexOne
{
    private const string CALLBACK = '{{page:desiderio-powermail/callback}}';
    private const string RAUCH_POST = 'https://x.com/rauchg/status/2022050269262151783';
    private const string SHOT_SOURCE = 'Screenshot of the CapexOne Plus demo. The company ACME Foods and all figures are fictional.';

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
            'description' => 'CapexOne Plus checks every capital project against its business case, live, and builds the pack for every gate review. Shown with fictional demo data.',
            'abstract' => 'Six-phase stage-gate, gate packs, a business case checked live, a financial ledger with four-eyes approval, and an API agents can read.',
            'hero' => [
                'header' => 'Decide on live numbers, not last month’s slides.',
                'subheadline' => 'CapexOne Plus checks each capital project against its business case, live, and builds the pack for every gate review. That saves an estimated 30–50 hours per gate.',
                'primaryButton' => ['text' => 'Book a demo', 'link' => self::CALLBACK],
                'secondaryButton' => ['text' => 'See all software', 'link' => '{{page:software}}'],
                'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-value-portfolio-451057e9.webp', 'Where the capital goes', 'CapexOne Plus value view: NPV, value at risk, capex forecast, portfolio IRR and ROCE, and gates waiting for a decision.', self::SHOT_SOURCE),
            ],
            'problem' => [
                'header' => 'What gate preparation costs',
                'content' => '<p>A gate review takes a project team an estimated 30 to 50 hours of exports, chasing and slides: €3,000 to €5,000 at €100 an hour.</p>',
            ],
            'tour' => [
                'header' => 'Six screens from the running product',
                'subheadline' => 'Screens from the CapexOne Plus demo. The company ACME Foods and every figure on them are fictional.',
                'shots' => [
                    [
                        'tab' => 'Planner',
                        'title' => 'Describe the investment in your own words',
                        'description' => 'A palletising cell in plain words, with the cost and benefit found in it. Fixed rules read the figures; the AI produces no number.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-planner-1004c895.webp', 'Describe the investment in your own words', 'CapexOne Plus Planner, step Describe: a robot palletizing cell in plain text, with the cost and benefit found in it.', self::SHOT_SOURCE),
                    ],
                    [
                        'tab' => 'Phases',
                        'title' => 'Six phases, each closed by a gate',
                        'description' => 'Three gates passed, execution at 60 %. A gate passes with two approving votes and approved documents, then the next phase opens.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-phases-26f5e747.webp', 'Six phases, each closed by a gate', 'CapexOne Plus phases and gates of a fictional project: three phases completed, execution in progress at 60 %.', self::SHOT_SOURCE),
                    ],
                    [
                        'tab' => 'Gate pack',
                        'title' => 'The gate pack builds itself',
                        'description' => 'One of two approving votes so far, and the required deliverables. The gate pack comes as a versioned PDF and ZIP with a readiness score.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-gate-pack-c6e1c51d.webp', 'The gate pack builds itself', 'CapexOne Plus gate review: decision, reviewers, required deliverables and two versions of the gate pack with PDF and ZIP.', self::SHOT_SOURCE),
                    ],
                    [
                        'tab' => 'Value',
                        'title' => 'Checked against the approved business case',
                        'description' => 'NPV, IRR, ROI, ROCE and payback against the version approved at gate 3. This project is off track, and the value bridge shows why.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-project-value-62b8b80a.webp', 'Checked against the approved business case', 'CapexOne Plus project value: off-track notice, NPV, IRR, ROI, ROCE and payback against the approved case, value bridge.', self::SHOT_SOURCE),
                    ],
                    [
                        'tab' => 'Invoices',
                        'title' => 'Technical check, arithmetic check, four eyes',
                        'description' => 'Each invoice passes a technical check, an arithmetic check and the approval matrix before it is handed to the ERP.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-ledger-invoices-abe5cbcc.webp', 'Technical check, arithmetic check, four eyes', 'CapexOne Plus financial ledger, Invoices tab: budget, committed, invoiced, paid, and an invoice waiting for approval.', self::SHOT_SOURCE),
                    ],
                    [
                        'tab' => 'Audit trail',
                        'title' => 'Signed events on a hash chain',
                        'description' => 'The audit trail checks its hash chain and filters the signed events, here every export of the financial ledger.',
                        'image' => ShowcaseBlocks::screenshot('frontend-feature-capexone-audit-7e4f6a7d.webp', 'Signed events on a hash chain', 'CapexOne Plus audit trail: hash chain intact with 371 signed events verified, filtered to export events.', self::SHOT_SOURCE),
                    ],
                ],
            ],
            'benefits' => [
                'header' => 'How CapexOne Plus works',
                'items' => [
                    ['icon' => 'list-checks', 'title' => 'Six phases, six gates', 'description' => 'Every project follows the same stage-gate path, from incubation to evaluation. A phase closes when its gate passes.'],
                    ['icon' => 'users', 'title' => 'Decisions on the record', 'description' => 'A gate needs two approving votes from admins or executives. One rejection sends it back, and every vote and comment stays in the history.'],
                    ['icon' => 'database', 'title' => 'One source of truth', 'description' => 'Budget items, actuals, change orders, risks, action points and documents sit next to their phase. Reports come from the data the team works with.'],
                    ['icon' => 'sparkles', 'title' => 'AI where it saves time', 'description' => 'An assistant, document extraction and risk suggestions take care of busywork. Requests go to EU-based models without data retention, and people decide.'],
                ],
            ],
            'sections' => [
                // 1. The five things, as capex-one.com lists them.
                // A sequence of five steps as a timeline: the numbered grid
                // has three columns and left an empty sixth slot.
                ShowcaseBlocks::block('desiderio_featuretimeline', [
                    'header' => 'From the first plan to the proven return',
                    'subheadline' => 'Five things CapexOne Plus does for every project, from the idea to the benefits review.',
                    'items' => [
                        [
                            'step' => '01',
                            'title' => 'Plan in minutes, calibrated on your track record',
                            'description' => '<p><strong>Plan · Planner.</strong> Describe the investment in your own words. A rule-based engine builds phases, gates, costs, cash flow, risks and the business case.</p>'
                                . '<ul><li>Durations and costs corrected by your own finished projects</li><li>P50 and P80 for cost, dates and NPV from a seeded simulation</li><li>One click turns the plan into a project</li></ul>',
                        ],
                        [
                            'step' => '02',
                            'title' => 'The gate pack builds itself',
                            'description' => '<p><strong>Decide · Gate packs.</strong> The pack is compiled from live data: business case, budget against actuals, risks, change orders and evidence for every deliverable.</p>'
                                . '<ul><li>A readiness score shows what is missing before reviewers see it</li><li>Versioned PDF plus an evidence bundle for the meeting</li><li>A recommendation from go to no go, with its reasons</li></ul>',
                        ],
                        [
                            'step' => '03',
                            'title' => 'Every report is checked against the business case',
                            'description' => '<p><strong>Track · Business case.</strong> NPV, IRR, ROI and ROCE are recalculated whenever costs, forecasts, dates or measured benefits change.</p>'
                                . '<ul><li>Approved baseline, current forecast and actuals side by side</li><li>On track, at risk or off track by your own thresholds</li><li>One set of formulas for every report, pack and chart</li></ul>',
                        ],
                        [
                            'step' => '04',
                            'title' => 'Live from project to division to company',
                            'description' => '<p><strong>Report · Live reports.</strong> Every invoice, commitment and forecast rolls up the moment it is booked.</p>'
                                . '<ul><li>Budget, commitments, actuals and forecast at completion</li><li>Roll-ups by division, site and category</li><li>Export to PDF, Excel or PowerPoint</li></ul>',
                        ],
                        [
                            'step' => '05',
                            'title' => 'The CFO always knows where the capital goes',
                            'description' => '<p><strong>Oversee · CFO view.</strong> One view of the portfolio: approved, committed, spent and forecast, and the value at risk.</p>'
                                . '<ul><li>Portfolio value and value at risk, always current</li><li>Gate decisions waiting, each with its pack</li><li>Early warnings when budgets, forecasts or risks move</li></ul>',
                        ],
                    ],
                ]),

                // 2. The savings: only the estimates the product site gives.
                ShowcaseBlocks::block('desiderio_datatable', [
                    'header' => 'One small project with three gates',
                    'show_header_row' => true,
                    'column_definitions' => [
                        ['column_label' => 'Gate', 'column_align' => 'left'],
                        ['column_label' => 'Preparation', 'column_align' => 'right'],
                        ['column_label' => 'Internal cost', 'column_align' => 'right'],
                    ],
                    'rows' => [
                        ['row_label' => 'Gate 1', 'cells' => [['value' => '30–50 h'], ['value' => '€3,000–5,000']]],
                        ['row_label' => 'Gate 2', 'cells' => [['value' => '30–50 h'], ['value' => '€3,000–5,000']]],
                        ['row_label' => 'Gate 3', 'cells' => [['value' => '30–50 h'], ['value' => '€3,000–5,000']]],
                        ['row_label' => 'One project', 'cells' => [['value' => '90–150 h'], ['value' => '€9,000–15,000']]],
                    ],
                    'show_caption' => true,
                    'caption_text' => 'Estimates from the product site, at an internal rate of €100 an hour.',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Multiplied by a portfolio',
                    'description' => 'An example from the savings calculator: 700 projects a year, 3 gates each, 40 hours per gate, €100 an hour. Your numbers vary.',
                    'items' => [
                        ['value' => '€8.4 million', 'label' => 'Gate preparation per year', 'description_text' => 'Team time spent building packs'],
                        ['value' => '84,000', 'label' => 'Team hours', 'description_text' => 'Per year, across the portfolio'],
                        ['value' => '≈ 49', 'label' => 'Full-time people', 'description_text' => 'A full-time year counted as 1,700 hours'],
                        ['value' => '€4,000', 'label' => 'Per gate', 'description_text' => 'At 40 hours and €100 an hour'],
                    ],
                ]),

                // 3. Processing and settlement in the financial ledger.
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Invoices and postings',
                    'header' => 'Processing and settlement, built in',
                    'subheadline' => 'Each project has its own financial ledger. Your ERP stays the accounting system of record.',
                    'items' => [
                        ['title' => 'One work package per posting', 'description' => 'Every posting belongs to exactly one work package, so project figures are always the exact sum of their work packages.'],
                        ['title' => 'Thresholds configured, not coded', 'description' => 'The approval matrix sets amount thresholds, roles and four-eyes rules. An overrun needs permission and a reason.'],
                        ['title' => 'Claims become change orders', 'description' => 'An agreed claim creates the change order and its ledger posting in one transaction.'],
                        ['title' => 'Invoices read by AI, checked by people', 'description' => 'The AI reads amount, vendor, date and early-payment discount from an invoice. A person reviews the fields before they are saved.'],
                    ],
                ]),

                // 4. Integrations, as the product site describes them.
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Integrations',
                    'header' => 'Fits the systems you already run',
                    'subheadline' => 'Nothing has to be replaced. The capital projects and their value live in CapexOne Plus.',
                    'items' => [
                        ['title' => 'ERP', 'description' => 'Budgets, commitments and actuals can come from SAP and other ERP systems. CapexOne Plus adds the project view, the business case and the gates.'],
                        ['title' => 'Single sign-on', 'description' => 'SSO with your identity provider and SCIM provisioning for users and groups. Roles decide who reviews, edits or only reads.'],
                        ['title' => 'REST API', 'description' => '173 documented endpoints and API keys for BI, reporting and automation.'],
                        ['title' => 'AI in the EU', 'description' => 'Invoice reading, risk suggestions and project chat run on EU-based models without data retention. People make the decisions.'],
                    ],
                ]),

                // 5. Do it for the agents: Guillermo Rauch's post, as the
                // capex-one.com landing page quoted it from February to
                // September 2026, and what the product does about it.
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => 'Do it for the agents',
                    'quote_text' => 'If you’re starting now, focus on the API. Do it for the agents.',
                    'author' => 'Guillermo Rauch',
                    'role' => 'CEO, Vercel, on X, 12 February 2026',
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Software is becoming invisible',
                    'content' => '<p>The same post begins with two paragraphs:</p>'
                        . '<blockquote><p>“Software is now free to build. But when you sit down to vibe code your next app with no mistakes, that app will sit on the shoulders of giants.”</p>'
                        . '<p>“Your agent will read Markdown. Then, it will run CLIs and call MCP tools. Your software will make API calls to other services. And software will become more and more invisible. Computers talking to other computers to get you an answer, even before you ask.”</p></blockquote>'
                        . '<p>Our reading: more and more people who use CapexOne Plus will send an agent instead of opening the app. So the API came first, with the same rules for people and agents.</p>',
                    'variant' => 'bordered',
                    'alignment' => 'center',
                    'link' => self::RAUCH_POST,
                    'link_text' => 'Read the post',
                ]),
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Built API-first',
                    'header' => 'What we did about it',
                    'subheadline' => 'There is no MCP server yet. Agents use the documented REST API, under the same rules as people.',
                    'items' => [
                        ['title' => 'The API came first', 'description' => 'REST API v1 with 173 documented endpoints in 26 categories, API keys as Bearer tokens and hourly rate limits. It is a developer preview.'],
                        ['title' => 'Typed, predictable data', 'description' => 'Every answer comes in the same JSON envelope. Projects, phases, gates, deliverables, budget, risks and documents are resources of their own.'],
                        ['title' => 'Agents under the same rules', 'description' => 'Each API key carries the role of its user. The in-app agent reads through 11 tools and never writes. Gates still need two human votes.'],
                    ],
                ]),

                // 6. The offer, as the product site states it. No prices.
                ShowcaseBlocks::block('desiderio_pricingtwotier', [
                    'eyebrow' => 'The offer',
                    'header' => 'Start with one project, then the portfolio',
                    'subheadline' => 'Try the product for 14 days. When you are ready, we tailor the plan to your projects, sites and integrations.',
                    'plans' => [
                        [
                            'title' => 'Free trial',
                            'price' => '14 days',
                            'description' => '<p>No credit card. Take one project through its first gate, with the whole product switched on.</p>',
                            'features' => [
                                ['text' => 'Planner, gate packs and the live business case check'],
                                ['text' => 'Live reports on project, division and company level'],
                                ['text' => 'Six-phase stage-gate workflow with approvals'],
                                ['text' => 'AI for invoices, risks and documents'],
                            ],
                            'featured' => false,
                            'button_text' => 'Request a trial',
                            'button_link' => self::CALLBACK,
                        ],
                        [
                            'title' => 'Portfolio plan',
                            'price' => 'Tailored',
                            'description' => '<p>For capital programmes. A 30-minute call with a live demo on your numbers and a transparent quote.</p>',
                            'features' => [
                                ['text' => 'Priced by projects, sites and integrations'],
                                ['text' => 'Your phases, gate criteria and templates set up with you'],
                                ['text' => 'ERP connection, single sign-on and data migration'],
                                ['text' => 'Training for project teams and executives'],
                                ['text' => 'Monthly or annual, on-premise on request'],
                            ],
                            'featured' => true,
                            'button_text' => 'Book a call',
                            'button_link' => self::CALLBACK,
                        ],
                    ],
                ]),

                // 7. Shared savings (D14): the goal of an engagement, kept apart
                // from the product figures in section 2.
                // Three figures in one row of three: the feature-stats grid
                // has two columns beside its intro and left the third alone.
                ShowcaseBlocks::block('desiderio_stats', [
                    'header' => 'Our approach: shared savings',
                    'subheadline' => 'The target model of a CapexOne Plus engagement: a goal and a pricing approach, not a measured result and not a guarantee. We aim to save you 3 % of your capital-project cost and are paid a third of that saving.',
                    'columns' => '3',
                    'stats' => [
                        ['value' => '3 %', 'label' => 'The saving we aim for', 'description' => '<p>Of your capital-project cost</p>'],
                        ['value' => '1 %', 'label' => 'Our share', 'description' => '<p>One third of the saving</p>'],
                        ['value' => '2 %', 'label' => 'Your share', 'description' => '<p>Two thirds of the saving stay with you</p>'],
                    ],
                ]),
            ],
            'faq' => [
                'header' => 'What CFOs and PMOs ask first',
                'subheadline' => 'Gate packs, the Planner, your ERP and the AI.',
                'items' => [
                    ['question' => 'How does the gate pack come together?', 'answer' => 'It reads what the gate needs from the project: business case, budget against actuals, change orders, risks, open actions and deliverables. Documents are matched to each requirement, and a readiness score shows what is missing. Reviewers get a versioned PDF with an evidence bundle. The AI draft of the summary is checked against the figures first.'],
                    ['question' => 'Is the Planner’s AI a black box?', 'answer' => 'No. It only answers fixed questions about your description, each with a confidence score, and asks you when it is unsure. Every duration, cost and cash flow comes from a documented rule set and your own finished projects. The same inputs always give the same plan.'],
                    ['question' => 'Do we have to replace our ERP?', 'answer' => 'No. CapexOne Plus works alongside it. Budgets, commitments and actuals can come from SAP or other ERP systems, and CapexOne Plus adds the project view, the business case and the gates.'],
                    ['question' => 'Where does the AI run?', 'answer' => 'On EU-based models without data retention. It reads invoices, suggests risks, drafts summaries and answers questions about a project. People make the decisions.'],
                ],
            ],
            'install' => null,
            'cta' => [
                'header' => 'Walk into the meeting with the pack done',
                'description' => ShowcaseStrategyPages::standardLine('CapexOne Plus'),
                'text' => 'Book a demo',
                'link' => self::CALLBACK,
            ],
        ];
    }
}
