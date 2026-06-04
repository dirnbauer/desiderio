<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Preset-specific starter sites for the desiderio:starter:seed command.
 *
 * Each starter is a real page tree: one homepage plus at least ten subpages.
 * Content intentionally uses existing Desiderio Content Blocks, not isolated
 * placeholder records, so generated sites are useful review targets.
 *
 * @phpstan-type StarterBlock array{ctype: string, colPos: int, fields: array<string, mixed>}
 * @phpstan-type StarterHome array{layout: string, content: array<int, StarterBlock>}
 * @phpstan-type StarterPage array{title: string, navTitle: string, slug: string, layout: string, abstract: string, content: array<int, StarterBlock>}
 * @phpstan-type StarterSite array{label: string, slug: string, rootSlug: string, rootTitle: string, rootNavTitle: string, abstract: string, home: StarterHome, subpages: array<int, StarterPage>}
 */
final class StarterSiteDefinitions
{
    /**
     * @return array<string, StarterSite>
     */
    public static function all(): array
    {
        return [
            'corporate' => self::corporate(),
            'dashboard' => self::dashboard(),
            'editorial' => self::editorial(),
            'portfolio' => self::portfolio(),
            'saas' => self::saas(),
        ];
    }

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return StarterSite|null
     */
    public static function get(string $slug): ?array
    {
        return self::all()[strtolower(trim($slug))] ?? null;
    }

    /**
     * @return StarterSite
     */
    private static function corporate(): array
    {
        $subpages = [
            self::corporatePage('Advisory Services', 'advisory-services', 'Decision support, operating model design, and executive alignment before a transformation starts.', 'Advisory offer', ['Board-ready diagnosis', 'Operating model map', 'Decision memo']),
            self::corporatePage('Implementation Office', 'implementation-office', 'A delivery page for migration, portal, and workflow programs that need senior ownership.', 'Delivery office', ['Program governance', 'Platform delivery', 'Enablement plan']),
            self::corporatePage('Managed Improvement', 'managed-improvement', 'Ongoing optimization for service portals, reporting cadences, and cross-team operations.', 'Retained model', ['Monthly backlog review', 'Reliability improvements', 'Stakeholder reporting']),
            self::corporatePage('Sector Playbooks', 'sector-playbooks', 'Industry-specific proof paths for finance, healthcare, manufacturing, and public service teams.', 'Industry use', ['Regulated onboarding', 'Service operations', 'Executive visibility']),
            self::corporatePage('Case Studies', 'case-studies', 'Outcome stories that connect constraints, delivery decisions, and measurable operational change.', 'Proof library', ['Claims portal rebuild', 'Partner onboarding hub', 'Service desk transformation']),
            self::corporatePage('Governance', 'governance', 'How steering groups, escalation paths, and delivery rituals keep complex work legible.', 'Operating cadence', ['Weekly evidence review', 'Risk register', 'Decision log']),
            self::corporatePage('Leadership', 'leadership', 'Client partners, delivery principals, and technical leads with clear responsibilities.', 'Leadership team', ['Client partner', 'Delivery principal', 'Technical director']),
            self::corporatePage('Procurement', 'procurement', 'Commercial models, compliance material, and buying information for serious evaluation.', 'Buyer support', ['Security packet', 'Reference process', 'Contracting path']),
            self::corporatePage('Resources', 'resources', 'Guides, checklists, and briefings for enterprise teams planning operational change.', 'Resource center', ['Migration checklist', 'Governance template', 'Service review guide']),
            self::corporatePage('Careers', 'careers', 'Roles, candidate expectations, and how the organization supports calm senior work.', 'Hiring', ['Senior consultant', 'Product engineer', 'Delivery manager']),
            self::corporatePage('Contact', 'contact', 'Routes for new business, partner, support, and procurement conversations.', 'Contact desk', ['Sales inquiry', 'Partnership request', 'Support escalation']),
        ];
        $subpages = array_merge($subpages, self::supportPages('Northstar Advisory Group', 'contact'));

        return [
            'label' => 'Corporate starter',
            'slug' => 'corporate',
            'rootSlug' => '/desiderio-corporate-starter',
            'rootTitle' => 'Northstar Advisory Group',
            'rootNavTitle' => 'Home',
            'abstract' => 'A complete corporate starter for a senior B2B service firm: positioning, procurement proof, delivery governance, people, resources, hiring, and contact paths.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
                    self::heroStats('Digital operations work that survives procurement and rollout', 'Northstar Advisory Group', 'A corporate starter for service firms that need buyers to understand the offer, trust the delivery model, and find proof without a sales deck.', 'Book a working session', '#contact', [
                        ['value' => '42%', 'label' => 'Less manual handoff', 'stat_description' => 'Measured after replacing regional spreadsheet flows.'],
                        ['value' => '11', 'label' => 'Departments onboarded', 'stat_description' => 'Legal, finance, operations, support, and regional leads.'],
                        ['value' => '24h', 'label' => 'Escalation response', 'stat_description' => 'Contracted production-facing response window.'],
                        ['value' => '99.9%', 'label' => 'Critical uptime', 'stat_description' => 'Tracked across portals and reporting workflows.'],
                    ]),
                    self::navTabs('Corporate starter map', [
                        ['label' => 'Services', 'link' => '#advisory-services'],
                        ['label' => 'Proof', 'link' => '#case-studies'],
                        ['label' => 'Governance', 'link' => '#governance'],
                        ['label' => 'Procurement', 'link' => '#procurement'],
                        ['label' => 'Contact', 'link' => '#contact'],
                    ]),
                    self::headerSection('Useful corporate content starts with buying questions', 'Starter strategy', 'The page stack is organized around evaluation: what you do, how delivery is governed, who leads it, what proof exists, and how procurement moves forward.'),
                    self::kpiCards('Operating proof in one screen', 'Board-level signals', 'Use this row as the first proof check after the masthead.', [
                        ['value' => '38%', 'label' => 'Lower manual handoffs', 'detail_text' => 'Measured after replacing six spreadsheet-led workflows.', 'trend' => 'positive'],
                        ['value' => '11', 'label' => 'Departments onboarded', 'detail_text' => 'Legal, finance, operations, support, and regional teams.', 'trend' => 'positive'],
                        ['value' => '24h', 'label' => 'Escalation response', 'detail_text' => 'Contracted response window for production-facing issues.', 'trend' => 'neutral'],
                        ['value' => '99.9%', 'label' => 'Critical process uptime', 'detail_text' => 'Tracked across service portals and reporting workflows.', 'trend' => 'positive'],
                    ]),
                    self::featureCards('A corporate starter that works past the hero', 'What visitors need', 'Every block answers a procurement, leadership, or delivery question.', [
                        ['title' => 'For executives', 'description' => 'Plain-language outcomes, commercial fit, and proof that reduces buying risk.'],
                        ['title' => 'For delivery teams', 'description' => 'Service lines, governance cadence, and clear handoff expectations.'],
                        ['title' => 'For procurement', 'description' => 'Legal, compliance, references, and contact paths ready for review.'],
                    ]),
                    self::pricingThreeTier('Engagement models buyers can compare', 'Commercial clarity', 'Corporate sites need enough packaging for buyers to understand fit before a call.', [
                        ['name' => 'Advisory Sprint', 'price' => 'EUR 18k', 'billing_period' => 'fixed scope', 'description' => 'For teams that need diagnosis, prioritization, and an executive-ready plan.', 'features' => ['Current-state review', 'Risk and dependency map', 'Board-ready recommendation'], 'is_recommended' => false, 'button_text' => 'Discuss advisory', 'button_link' => '#contact'],
                        ['name' => 'Implementation Office', 'price' => 'Custom', 'billing_period' => 'program', 'description' => 'For portal, workflow, and reporting programs that require senior delivery ownership.', 'features' => ['Delivery governance', 'Product and engineering team', 'Stakeholder reporting'], 'is_recommended' => true, 'button_text' => 'Scope delivery', 'button_link' => '#contact'],
                        ['name' => 'Managed Improvement', 'price' => 'Retainer', 'billing_period' => 'monthly', 'description' => 'For organizations that want continuous improvement after launch.', 'features' => ['Monthly roadmap', 'Reliability reviews', 'Operational dashboards'], 'is_recommended' => false, 'button_text' => 'Plan retention', 'button_link' => '#contact'],
                    ]),
                    self::caseStudyGrid('Representative proof points', 'Selected outcomes', [
                        ['client_name' => 'Nordline Finance', 'summary' => 'Unified five regional onboarding flows into one governed portal.', 'result' => '42% faster activation', 'link' => '#case-studies'],
                        ['client_name' => 'Helio Health', 'summary' => 'Modernized patient service operations without interrupting frontline teams.', 'result' => '18k requests/month', 'link' => '#case-studies'],
                        ['client_name' => 'Mason Works', 'summary' => 'Built executive reporting that finally matched operational reality.', 'result' => '9 weekly reports retired', 'link' => '#case-studies'],
                    ]),
                    self::testimonialGrid('Why clients stay', 'Client confidence', [
                        ['quote' => 'The team turned a fragile internal process into a service our leadership can actually trust.', 'author_name' => 'Amelia Grant', 'author_title' => 'COO, Nordline Finance'],
                        ['quote' => 'We got a clear plan, a calmer rollout, and fewer surprises than any previous modernization effort.', 'author_name' => 'Markus Renner', 'author_title' => 'VP Operations, Helio Health'],
                        ['quote' => 'Their governance rhythm made complex work feel legible to non-technical stakeholders.', 'author_name' => 'Priya Shah', 'author_title' => 'Transformation Lead, Mason Works'],
                    ]),
                    self::companyValues('How Northstar works', 'Operating principles', 'Values are written as delivery promises so they are useful in sales, hiring, and procurement contexts.', [
                        ['title' => 'Make work legible', 'description' => 'Every program has a visible decision log, owner map, and evidence trail.', 'icon' => 'list-checks'],
                        ['title' => 'Reduce operational drag', 'description' => 'The team removes handoffs and reporting loops that slow frontline teams down.', 'icon' => 'workflow'],
                        ['title' => 'Stay accountable after launch', 'description' => 'Improvements continue through managed reviews instead of vanishing at handoff.', 'icon' => 'shield-check'],
                    ]),
                    self::teamGridMinimal('Client-facing leadership', 'Seeded people content makes the corporate starter feel credible before final team profiles are added.', [
                        ['name' => 'Mara Stein', 'role' => 'Managing Partner, Advisory'],
                        ['name' => 'Jonas Feld', 'role' => 'Delivery Principal'],
                        ['name' => 'Priya Nair', 'role' => 'Technical Director'],
                        ['name' => 'Elena Vogt', 'role' => 'Client Operations Lead'],
                    ]),
                    self::resourceLibrary('Procurement-ready resources', 'Useful downloads', [
                        ['title' => 'Transformation readiness checklist', 'type_label' => 'Checklist', 'description' => 'Questions buyers should answer before scoping a service program.', 'link' => '#resources'],
                        ['title' => 'Governance meeting template', 'type_label' => 'Template', 'description' => 'A compact structure for weekly evidence, risk, and decision review.', 'link' => '#governance'],
                        ['title' => 'Service portal modernization brief', 'type_label' => 'Brief', 'description' => 'A one-page outline for replacing fragile internal workflows.', 'link' => '#advisory-services'],
                    ]),
                    self::ctaCard('Ready to map the first decision?', 'Book a 45-minute fit call and leave with a practical outline of the pages, proof, and workflows your site needs.', 'Plan the site', '#contact', 'Next step'),
                    self::sitemapGrid('Explore Northstar Advisory', [
                        [
                            'title' => 'Services',
                            'pages' => [
                                ['label' => 'Advisory Services', 'link' => '#advisory-services'],
                                ['label' => 'Implementation Office', 'link' => '#implementation-office'],
                                ['label' => 'Managed Improvement', 'link' => '#managed-improvement'],
                            ],
                        ],
                        [
                            'title' => 'Proof',
                            'pages' => [
                                ['label' => 'Case Studies', 'link' => '#case-studies'],
                                ['label' => 'Sector Playbooks', 'link' => '#sector-playbooks'],
                                ['label' => 'Resources', 'link' => '#resources'],
                            ],
                        ],
                        [
                            'title' => 'Company',
                            'pages' => [
                                ['label' => 'Leadership', 'link' => '#leadership'],
                                ['label' => 'Careers', 'link' => '#careers'],
                                ['label' => 'Contact', 'link' => '#contact'],
                            ],
                        ],
                        [
                            'title' => 'Legal',
                            'pages' => [
                                ['label' => 'Imprint', 'link' => '#imprint'],
                                ['label' => 'Privacy', 'link' => '#privacy'],
                                ['label' => 'Accessibility', 'link' => '#accessibility'],
                            ],
                        ],
                    ]),
                    self::footerBrand('Northstar Advisory Group', 'Operational change, made legible.', [
                        ['label' => 'Services', 'link' => '#advisory-services'],
                        ['label' => 'Case Studies', 'link' => '#case-studies'],
                        ['label' => 'Contact', 'link' => '#contact'],
                    ]),
                ],
            ],
            'subpages' => $subpages,
        ];
    }

    /**
     * @return StarterSite
     */
    private static function dashboard(): array
    {
        $dashboardPages = [
            ['Revenue Dashboard', 'revenue', 'ARR, expansion, churn, and invoice risk in one operating view.', 'Revenue movement', 'k EUR', [['New MRR', 'EUR 84k', '+12%', 'up'], ['Expansion', 'EUR 31k', '+7%', 'up'], ['Churn risk', 'EUR 9k', '-3%', 'down']]],
            ['Product Analytics', 'product-analytics', 'Activation, feature adoption, and release impact for product teams.', 'Activation rate', '%', [['Activated users', '41%', '+5%', 'up'], ['Feature adoption', '68%', '+9%', 'up'], ['Drop-off', '14%', '-4%', 'down']]],
            ['Customer Health', 'customer-health', 'Accounts that need attention before renewal or escalation.', 'Health score', 'pts', [['Healthy accounts', '182', '+11', 'up'], ['Watch list', '23', '-4', 'down'], ['Renewals due', '17', '+2', 'neutral']]],
            ['Sales Pipeline', 'pipeline', 'Qualified pipeline, stage conversion, and sales cycle signals.', 'Pipeline value', 'k EUR', [['Open pipeline', 'EUR 1.8M', '+18%', 'up'], ['Win rate', '31%', '+2%', 'up'], ['Stalled deals', '12', '-5', 'down']]],
            ['Experiment Results', 'experiments', 'A/B test outcomes, confidence, and next decisions.', 'Experiment lift', '%', [['Winning tests', '7', '+3', 'up'], ['Median lift', '8.4%', '+1.2%', 'up'], ['Inconclusive', '4', '-1', 'down']]],
            ['Support Operations', 'support', 'Queues, SLA health, and recurring customer pain points.', 'Support load', 'tickets', [['Open tickets', '128', '-14', 'down'], ['SLA met', '96%', '+2%', 'up'], ['Escalations', '6', '0', 'neutral']]],
            ['Operations Monitor', 'operations', 'Internal workflow throughput, bottlenecks, and staffing pressure.', 'Throughput', 'jobs', [['Completed jobs', '2.4k', '+6%', 'up'], ['Blocked tasks', '18', '-7', 'down'], ['Automation rate', '63%', '+4%', 'up']]],
            ['Finance Control', 'finance', 'Budget variance, cash timing, and spend categories.', 'Budget use', '%', [['Budget used', '64%', '+3%', 'neutral'], ['Forecast gap', 'EUR 42k', '-8%', 'down'], ['Approved spend', 'EUR 710k', '+5%', 'up']]],
            ['System Health', 'system-health', 'Availability, latency, incidents, and release safety.', 'Platform health', 'score', [['Uptime', '99.98%', '+0.01%', 'up'], ['P95 latency', '182ms', '-24ms', 'down'], ['Incidents', '1', '-2', 'down']]],
            ['Reports', 'reports', 'Saved executive, product, and operational reports.', 'Report usage', 'views', [['Weekly exec', '428', '+21%', 'up'], ['Product review', '213', '+8%', 'up'], ['CSV exports', '96', '-12%', 'down']]],
            ['Settings', 'settings', 'Workspace roles, notification rules, and connected data sources.', 'Configuration', 'items', [['Data sources', '9', '+1', 'up'], ['Active roles', '14', '0', 'neutral'], ['Pending invites', '6', '+2', 'neutral']]],
        ];
        $supportPages = self::supportPages('SignalRoom', 'settings');

        return [
            'label' => 'Dashboard starter',
            'slug' => 'dashboard',
            'rootSlug' => '/desiderio-dashboard-starter',
            'rootTitle' => 'SignalRoom',
            'rootNavTitle' => 'Overview',
            'abstract' => 'A dummy analytics application with realistic dashboards, operational pages, data tables, charts, alerts, roles, pricing, and settings.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
                    self::heroSaas('Run the weekly operating room from one dashboard', 'SignalRoom demo workspace', 'A dashboard starter with populated dummy data across revenue, product, customer health, pipeline, experiments, support, finance, and system health.', 'Open reports', '#reports', 'Review settings', '#settings', ['RevOps', 'Product', 'Success', 'Finance']),
                    self::navTabs('Dashboard workspace sections', [
                        ['label' => 'Revenue', 'link' => '#revenue'],
                        ['label' => 'Product', 'link' => '#product-analytics'],
                        ['label' => 'Customers', 'link' => '#customer-health'],
                        ['label' => 'Reports', 'link' => '#reports'],
                        ['label' => 'Settings', 'link' => '#settings'],
                    ]),
                    self::metricDashboard('Executive overview', 'The homepage is a useful dummy dashboard instead of an empty admin shell.', [
                        ['label' => 'ARR', 'value' => 'EUR 4.8M', 'change' => '+14% vs last quarter', 'trend' => 'up'],
                        ['label' => 'Active accounts', 'value' => '312', 'change' => '+28 this month', 'trend' => 'up'],
                        ['label' => 'Open risks', 'value' => '17', 'change' => '-6 after review', 'trend' => 'down'],
                        ['label' => 'SLA health', 'value' => '96%', 'change' => '+2 points', 'trend' => 'up'],
                    ], 'Operating score', 'pts'),
                    self::chartLine('Pipeline trend', 'Dummy data shows the dashboard canvas, chart labels, and responsive behavior.', 'Pipeline value', 'k EUR'),
                    self::dataTable('Priority accounts', ['Account', 'Owner', 'Stage', 'Signal'], [
                        ['Northstar Labs', 'Mara', 'Renewal', 'Expansion likely'],
                        ['Helio Systems', 'Sofia', 'Onboarding', 'Training overdue'],
                        ['Studio Atlas', 'Jonas', 'Active', 'New integration'],
                    ]),
                    self::featureCards('Dummy dashboards that are useful for review', 'Workspace design', 'Each dashboard page has enough populated metrics, chart data, table rows, and alert copy to test density and navigation.', [
                        ['title' => 'Executive-ready summary', 'description' => 'High-level scores, risks, and movement across the operating model.'],
                        ['title' => 'Role-specific pages', 'description' => 'Revenue, product, success, support, operations, finance, and system health pages are all seeded.'],
                        ['title' => 'Data-state coverage', 'description' => 'Tables, charts, KPI cards, alerts, settings, and reports make empty-state design unnecessary.'],
                    ]),
                    self::pricingThreeTier('Workspace packages', 'Commercial demo', 'Dashboard starters often need plan and access examples for sales demos and internal tools.', [
                        ['name' => 'Team', 'price' => 'EUR 49', 'billing_period' => '/seat', 'description' => 'For one team reviewing weekly operating metrics.', 'features' => ['8 dashboards', 'CSV exports', 'Email summaries'], 'is_recommended' => false, 'button_text' => 'Start team plan', 'button_link' => '#settings'],
                        ['name' => 'Company', 'price' => 'EUR 99', 'billing_period' => '/seat', 'description' => 'For cross-functional reporting and executive review.', 'features' => ['Unlimited dashboards', 'Role permissions', 'Scheduled reports'], 'is_recommended' => true, 'button_text' => 'Open company plan', 'button_link' => '#settings'],
                        ['name' => 'Enterprise', 'price' => 'Custom', 'billing_period' => '', 'description' => 'For security, data warehouse, and governance requirements.', 'features' => ['SSO', 'Audit logs', 'Dedicated onboarding'], 'is_recommended' => false, 'button_text' => 'Request review', 'button_link' => '#settings'],
                    ]),
                    self::testimonialGrid('Operators using the workspace', 'Internal proof', [
                        ['quote' => 'The dummy workspace shows enough density to judge whether the dashboard rail and tables are actually useful.', 'author_name' => 'Mara Klein', 'author_title' => 'Executive Sponsor'],
                        ['quote' => 'Every dashboard page has a metric set, trend chart, and detail table, so reviewers are not staring at placeholders.', 'author_name' => 'Sofia Urban', 'author_title' => 'Customer Health Owner'],
                        ['quote' => 'Settings, reports, and system health make this feel like a real app shell instead of a homepage mockup.', 'author_name' => 'Jonas Meier', 'author_title' => 'Revenue Operations'],
                    ]),
                    self::teamGridMinimal('Workspace owners', 'Team content gives the dashboard starter realistic permission and ownership context.', [
                        ['name' => 'Mara Klein', 'role' => 'Executive Sponsor'],
                        ['name' => 'Sofia Urban', 'role' => 'Customer Health Owner'],
                        ['name' => 'Jonas Meier', 'role' => 'Revenue Operations'],
                        ['name' => 'Nora Stein', 'role' => 'Data Quality Lead'],
                    ]),
                    self::resourceLibrary('Saved report library', 'Operational assets', [
                        ['title' => 'Monday executive brief', 'type_label' => 'Report', 'description' => 'ARR, risk, pipeline, SLA, and action owners for leadership review.', 'link' => '#reports'],
                        ['title' => 'Customer health watchlist', 'type_label' => 'Dashboard', 'description' => 'Accounts needing renewal, onboarding, or escalation attention.', 'link' => '#customer-health'],
                        ['title' => 'Experiment decision log', 'type_label' => 'Log', 'description' => 'Recent tests, confidence, and next actions for product teams.', 'link' => '#experiments'],
                    ]),
                    self::alert('Live workspace note', 'success', 'The starter includes dummy dashboards for every navigation section so product teams can review density, charts, and tables immediately.'),
                    self::ctaCard('Review a populated dashboard set', 'Start with revenue, customer health, reports, and settings to see charts, tables, alerts, ownership, and utility pages working together.', 'Open reports', '#reports', 'Dashboard demo'),
                    self::sitemapGrid('Open dashboard areas', [
                        [
                            'title' => 'Dashboards',
                            'pages' => [
                                ['label' => 'Revenue Dashboard', 'link' => '#revenue'],
                                ['label' => 'Product Analytics', 'link' => '#product-analytics'],
                                ['label' => 'Customer Health', 'link' => '#customer-health'],
                            ],
                        ],
                        [
                            'title' => 'Operations',
                            'pages' => [
                                ['label' => 'Sales Pipeline', 'link' => '#pipeline'],
                                ['label' => 'Support Operations', 'link' => '#support'],
                                ['label' => 'Finance Control', 'link' => '#finance'],
                            ],
                        ],
                        [
                            'title' => 'System',
                            'pages' => [
                                ['label' => 'System Health', 'link' => '#system-health'],
                                ['label' => 'Reports', 'link' => '#reports'],
                                ['label' => 'Settings', 'link' => '#settings'],
                            ],
                        ],
                        [
                            'title' => 'Legal',
                            'pages' => [
                                ['label' => 'Privacy', 'link' => '#privacy'],
                                ['label' => 'Accessibility', 'link' => '#accessibility'],
                            ],
                        ],
                    ]),
                    self::footerBrand('SignalRoom', 'Dummy operating dashboards with real review density.', [
                        ['label' => 'Reports', 'link' => '#reports'],
                        ['label' => 'Settings', 'link' => '#settings'],
                        ['label' => 'System Health', 'link' => '#system-health'],
                    ]),
                ],
            ],
            'subpages' => array_merge(array_map(
                static fn (array $page): array => self::dashboardPage(...$page),
                $dashboardPages
            ), $supportPages),
        ];
    }

    /**
     * @return StarterSite
     */
    private static function editorial(): array
    {
        $subpages = [
            self::editorialPage('Front Page', 'front-page', 'Fresh dispatches, short notes, and newly published analysis organized for repeat readers.', 'Front page desk', ['Weekend brief: AI procurement pressure', 'How teams edit policy pages', 'A quieter release note pattern']),
            self::editorialPage('Analysis', 'analysis', 'Deeper interpretation for strategy, design, content, and technology decisions.', 'Analysis desk', ['The cost of generic dashboards', 'What content debt really signals', 'Why governance pages convert']),
            self::editorialPage('Field Guides', 'field-guides', 'Practical playbooks readers can act on after one session.', 'Guide shelf', ['Homepage evidence checklist', 'Dashboard page planning', 'Editorial taxonomy starter']),
            self::editorialPage('Features', 'features', 'Long-form reporting built for immersive reading and strong article packages.', 'Feature package', ['Inside a regional service rebuild', 'A field guide to migration calm', 'How design systems earn trust']),
            self::editorialPage('Interviews', 'interviews', 'Conversations with operators, editors, founders, and technical leads.', 'Interview room', ['A CTO on measuring adoption', 'An editor on reusable briefs', 'A COO on service design']),
            self::editorialPage('Reviews', 'reviews', 'Structured reviews of tools, patterns, launches, and public examples.', 'Review desk', ['Dashboard onboarding review', 'Pricing page teardown', 'Article template audit']),
            self::editorialPage('Data Room', 'data-room', 'Charts, benchmarks, source material, and lightweight research artifacts.', 'Research shelf', ['Reader survey results', 'Pricing pattern index', 'Dashboard density samples']),
            self::editorialPage('Events', 'events', 'Panels, workshops, live teardowns, and subscriber Q and A sessions.', 'Live program', ['Editorial systems roundtable', 'Dashboard critique clinic', 'Launch page teardown']),
            self::editorialPage('Newsletter', 'newsletter', 'A subscription page with proof, archive links, cadence, and reader expectations.', 'Newsletter offer', ['Tuesday briefing', 'Monthly teardown', 'Subscriber Q and A']),
            self::editorialPage('Advertise', 'advertise', 'Sponsor fit, audience profile, packages, and editorial boundaries.', 'Sponsor desk', ['Audience profile', 'Package types', 'Editorial guardrails']),
            self::editorialPage('About', 'about', 'Mission, editorial standards, masthead, correction policy, and contribution paths.', 'About the publication', ['Editorial promise', 'Standards and corrections', 'How to pitch']),
        ];
        $subpages = array_merge($subpages, self::supportPages('Field Ledger', 'advertise'));

        return [
            'label' => 'Editorial starter',
            'slug' => 'editorial',
            'rootSlug' => '/desiderio-editorial-starter',
            'rootTitle' => 'Field Ledger',
            'rootNavTitle' => 'Home',
            'abstract' => 'A full editorial starter for a serious publication: sections, story packages, resource shelves, sponsor context, newsletter prompts, standards, and article-like subpages.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
                    self::heroStats('A publication starter built around recurring editorial work', 'Field Ledger', 'Use the generated page tree as a first editorial calendar: anchor stories, guides, data posts, interviews, events, newsletter, sponsor information, and standards.', 'Browse the issue', '#front-page', [
                        ['value' => '6', 'label' => 'Editorial desks', 'stat_description' => 'Analysis, guides, interviews, reviews, data, and events.'],
                        ['value' => '11', 'label' => 'Launch pages', 'stat_description' => 'A complete publication structure beyond the homepage.'],
                        ['value' => '3', 'label' => 'Reader paths', 'stat_description' => 'Brief, deep read, and practical resource routes.'],
                        ['value' => '1', 'label' => 'Standards page', 'stat_description' => 'About, corrections, and contribution context included.'],
                    ]),
                    self::navTabs('Editorial sections', [
                        ['label' => 'Front Page', 'link' => '#front-page'],
                        ['label' => 'Analysis', 'link' => '#analysis'],
                        ['label' => 'Guides', 'link' => '#field-guides'],
                        ['label' => 'Newsletter', 'link' => '#newsletter'],
                        ['label' => 'Advertise', 'link' => '#advertise'],
                    ], 'underline'),
                    self::headerSection('A front page needs an editorial operating model', 'Starter strategy', 'The content is organized for repeat publishing: what is new, what is evergreen, what is evidence, and what deserves a subscription prompt.'),
                    self::articleGrid('Top stories to publish first', 'Front page', [
                        ['title' => 'The homepage proof stack that actually helps buyers', 'category' => 'Analysis', 'description' => 'A practical teardown of claims, evidence, and next steps on B2B homepages.', 'link' => '#analysis'],
                        ['title' => 'Designing dashboards readers can scan in under one minute', 'category' => 'Guide', 'description' => 'A field guide to metrics, chart hierarchy, and executive summaries.', 'link' => '#guides'],
                        ['title' => 'Why editorial sites need better resource pages', 'category' => 'Opinion', 'description' => 'Resource libraries fail when taxonomy, freshness, and context are treated as afterthoughts.', 'link' => '#resources'],
                    ]),
                    self::featureList('Editorial lanes', 'Useful structure', 'The starter separates timely posts, deep features, practical guides, and subscription flows.', [
                        ['icon' => 'book-open', 'title' => 'Reported features', 'description' => 'Long reads with decks, bylines, and related content blocks.'],
                        ['icon' => 'clock', 'title' => 'Fast briefings', 'description' => 'Short posts for news, release notes, and weekly digests.'],
                        ['icon' => 'database', 'title' => 'Reusable resources', 'description' => 'Evergreen libraries, downloads, and checklist pages.'],
                    ]),
                    self::metricDashboard('Editorial operating dashboard', 'Dummy editorial metrics help the publication starter feel managed instead of decorative.', [
                        ['label' => 'Issue readiness', 'value' => '82%', 'change' => '+14 points', 'trend' => 'up'],
                        ['label' => 'Evergreen updates', 'value' => '18', 'change' => '+5 this month', 'trend' => 'up'],
                        ['label' => 'Sponsor slots', 'value' => '4', 'change' => '2 reserved', 'trend' => 'neutral'],
                        ['label' => 'Reader replies', 'value' => '146', 'change' => '+23%', 'trend' => 'up'],
                    ], 'Editorial readiness', '%'),
                    self::resourceLibrary('Reader tools', 'Useful downloads', [
                        ['title' => 'Editorial launch checklist', 'type_label' => 'Checklist', 'description' => 'A practical order of operations for launching a publication.', 'link' => '#guides'],
                        ['title' => 'Homepage evidence planner', 'type_label' => 'Worksheet', 'description' => 'Map proof, objections, and conversion prompts before writing copy.', 'link' => '#analysis'],
                        ['title' => 'Issue calendar model', 'type_label' => 'Template', 'description' => 'A simple planning structure for recurring packages and newsletters.', 'link' => '#newsletter'],
                    ]),
                    self::pricingThreeTier('Reader and sponsor offers', 'Revenue paths', 'Editorial starters should make subscription and sponsorship pages concrete enough to evaluate.', [
                        ['name' => 'Free Reader', 'price' => '$0', 'billing_period' => '', 'description' => 'Weekly public stories and monthly resource updates.', 'features' => ['Front page access', 'Public archive', 'Monthly digest'], 'is_recommended' => false, 'button_text' => 'Read latest', 'button_link' => '#front-page'],
                        ['name' => 'Member', 'price' => '$12', 'billing_period' => '/month', 'description' => 'Full archive, teardown issues, and subscriber Q and A.', 'features' => ['Premium guides', 'Event replays', 'Subscriber notes'], 'is_recommended' => true, 'button_text' => 'Join readers', 'button_link' => '#newsletter'],
                        ['name' => 'Sponsor', 'price' => 'Custom', 'billing_period' => '', 'description' => 'Clearly labeled sponsorship aligned with the publication audience.', 'features' => ['Audience profile', 'Package options', 'Editorial boundaries'], 'is_recommended' => false, 'button_text' => 'Review sponsor fit', 'button_link' => '#advertise'],
                    ]),
                    self::testimonialGrid('Reader signals', 'Audience proof', [
                        ['quote' => 'The guides feel written for people who need to make decisions this week, not someday.', 'author_name' => 'Clara Moss', 'author_title' => 'Product Marketing Lead'],
                        ['quote' => 'The data room and corrections page make the publication feel more trustworthy than a normal content hub.', 'author_name' => 'Rafael Stone', 'author_title' => 'Operations Director'],
                        ['quote' => 'Sponsor information is clear without blurring the editorial line.', 'author_name' => 'Nina Brand', 'author_title' => 'Partnerships Manager'],
                    ]),
                    self::companyValues('Editorial standards readers can trust', 'Standards', 'Values are seeded as editorial promises, not vague brand adjectives.', [
                        ['title' => 'Useful before clever', 'description' => 'Every article should help a reader make a better decision or ask a sharper question.', 'icon' => 'book-open'],
                        ['title' => 'Sources over vibes', 'description' => 'Research pages, interviews, and claims need visible evidence trails.', 'icon' => 'search-check'],
                        ['title' => 'Clear corrections', 'description' => 'The about page includes standards, corrections, and contribution routes.', 'icon' => 'badge-check'],
                    ]),
                    self::teamGridMinimal('Masthead snapshot', 'People content makes the editorial starter feel like a real publication with owners and standards.', [
                        ['name' => 'Lena Hart', 'role' => 'Editor in Chief'],
                        ['name' => 'Marco Weiss', 'role' => 'Analysis Editor'],
                        ['name' => 'Tara Iqbal', 'role' => 'Research Lead'],
                        ['name' => 'Noah Brandt', 'role' => 'Newsletter Producer'],
                    ]),
                    self::ctaCard('Build the first issue', 'Use the generated pages as the first editorial calendar: publish one anchor story, two guides, and a newsletter issue.', 'Plan the issue', '#newsletter', 'Editorial workflow'),
                    self::sitemapGrid('Browse Field Ledger', [
                        [
                            'title' => 'Read',
                            'pages' => [
                                ['label' => 'Front Page', 'link' => '#front-page'],
                                ['label' => 'Analysis', 'link' => '#analysis'],
                                ['label' => 'Features', 'link' => '#features'],
                            ],
                        ],
                        [
                            'title' => 'Use',
                            'pages' => [
                                ['label' => 'Field Guides', 'link' => '#field-guides'],
                                ['label' => 'Data Room', 'link' => '#data-room'],
                                ['label' => 'Reviews', 'link' => '#reviews'],
                            ],
                        ],
                        [
                            'title' => 'Join',
                            'pages' => [
                                ['label' => 'Newsletter', 'link' => '#newsletter'],
                                ['label' => 'Events', 'link' => '#events'],
                                ['label' => 'Advertise', 'link' => '#advertise'],
                            ],
                        ],
                        [
                            'title' => 'Publication',
                            'pages' => [
                                ['label' => 'About', 'link' => '#about'],
                                ['label' => 'Privacy', 'link' => '#privacy'],
                                ['label' => 'Accessibility', 'link' => '#accessibility'],
                            ],
                        ],
                    ]),
                    self::footerBrand('Field Ledger', 'Useful analysis, practical guides, and evidence-rich editorial systems.', [
                        ['label' => 'Newsletter', 'link' => '#newsletter'],
                        ['label' => 'About', 'link' => '#about'],
                        ['label' => 'Advertise', 'link' => '#advertise'],
                    ]),
                ],
            ],
            'subpages' => $subpages,
        ];
    }

    /**
     * @return StarterSite
     */
    private static function portfolio(): array
    {
        $subpages = [
            self::portfolioPage('Selected Work', 'selected-work', 'A focused index of the projects the studio wants buyers to inspect first.', 'Work index', ['Commerce relaunch', 'Analytics platform', 'Editorial identity']),
            self::portfolioPage('Case Studies', 'case-studies', 'Project stories that connect brief, constraints, process, and measurable outcome.', 'Case study archive', ['Revenue cockpit', 'Patient portal', 'Membership relaunch']),
            self::portfolioPage('Capabilities', 'capabilities', 'Offer structure for product strategy, interface systems, launch pages, and implementation support.', 'Studio capabilities', ['Product strategy', 'Interface systems', 'Launch execution']),
            self::portfolioPage('Process', 'process', 'How the studio works from first call through critique, prototype, build, and handoff.', 'Working model', ['Audit', 'Prototype', 'Build', 'Handoff']),
            self::portfolioPage('Sectors', 'sectors', 'Where the studio has useful context: SaaS, commerce, editorial, public service, and B2B operations.', 'Sector fit', ['SaaS scaleups', 'Editorial brands', 'B2B services']),
            self::portfolioPage('Results', 'results', 'Metrics, before-after notes, and qualitative proof that the studio can explain.', 'Outcome proof', ['Activation lift', 'Design system reuse', 'Launch clarity']),
            self::portfolioPage('Team', 'team', 'Senior team, collaborators, operating principles, and the people clients meet first.', 'Studio team', ['Creative direction', 'Product design', 'Frontend implementation']),
            self::portfolioPage('Collaborators', 'collaborators', 'Specialist partners for engineering, motion, editorial, analytics, and research.', 'Partner bench', ['Engineering partner', 'Research lead', 'Motion designer']),
            self::portfolioPage('Journal', 'journal', 'Thinking behind recent work, design observations, and launch retrospectives.', 'Studio journal', ['Interface density', 'Portfolio proof', 'Launch rituals']),
            self::portfolioPage('Contact', 'contact', 'Project inquiry, availability, and fit criteria.', 'Project inquiry', ['Budget range', 'Timeline', 'Decision group']),
            self::portfolioPage('Project Brief', 'project-brief', 'A guided intake page that helps leads write a useful first request.', 'Brief builder', ['Goal', 'Audience', 'Constraints']),
        ];
        $subpages = array_merge($subpages, self::supportPages('Studio Halden', 'project-brief'));

        return [
            'label' => 'Portfolio starter',
            'slug' => 'portfolio',
            'rootSlug' => '/desiderio-portfolio-starter',
            'rootTitle' => 'Studio Halden',
            'rootNavTitle' => 'Home',
            'abstract' => 'A portfolio starter for a senior digital studio: selected work, case studies, capabilities, process, proof, team, collaborators, journal, and project brief flow.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
                    self::heroProduct('Interfaces with a point of view', 'Studio Halden', 'A portfolio starter for studios that need buyers to understand taste, judgment, operating model, proof, and project fit before a call.', 'Project slots from EUR 24k', 'Start a project brief', '#project-brief', ['Senior product UI', 'Design systems that ship', 'Launch pages with evidence']),
                    self::navTabs('Portfolio starter map', [
                        ['label' => 'Work', 'link' => '#selected-work'],
                        ['label' => 'Capabilities', 'link' => '#capabilities'],
                        ['label' => 'Process', 'link' => '#process'],
                        ['label' => 'Results', 'link' => '#results'],
                        ['label' => 'Brief', 'link' => '#project-brief'],
                    ]),
                    self::headerSection('A portfolio should help buyers judge fit, not just browse images', 'Starter strategy', 'The content gives context, constraints, decisions, proof, services, process, people, and a practical inquiry path.'),
                    self::caseStudyGrid('Selected projects', 'Recent work', [
                        ['client_name' => 'Aster Commerce', 'summary' => 'Rebuilt product discovery and checkout guidance for a catalog with 18k SKUs.', 'result' => '+22% product detail engagement', 'link' => '#selected-work'],
                        ['client_name' => 'Civic Ledger', 'summary' => 'Designed a dense reporting workspace for non-technical municipal teams.', 'result' => '6 dashboards shipped', 'link' => '#case-studies'],
                        ['client_name' => 'Field Notes Media', 'summary' => 'Created a publication system for issues, long reads, and sponsor packages.', 'result' => '3x editorial throughput', 'link' => '#journal'],
                    ]),
                    self::featureCards('What the studio is good for', 'Services', 'A portfolio starter should help a buyer decide whether the studio fits the project.', [
                        ['title' => 'Positioning through interface', 'description' => 'Turn a fuzzy product story into concrete screens, flows, and proof.'],
                        ['title' => 'Design systems that ship', 'description' => 'Reusable components, documentation, and production-ready implementation support.'],
                        ['title' => 'Launch pages with evidence', 'description' => 'Case studies, service pages, and conversion paths that make the work inspectable.'],
                    ]),
                    self::metricDashboard('Studio proof snapshot', 'Dummy performance data gives the portfolio starter more than a visual gallery.', [
                        ['label' => 'Projects shipped', 'value' => '47', 'change' => '+9 this year', 'trend' => 'up'],
                        ['label' => 'Repeat clients', 'value' => '68%', 'change' => '+6 points', 'trend' => 'up'],
                        ['label' => 'Systems adopted', 'value' => '14', 'change' => '+4 launches', 'trend' => 'up'],
                        ['label' => 'Open slots', 'value' => '2', 'change' => 'next quarter', 'trend' => 'neutral'],
                    ], 'Studio proof', 'score'),
                    self::pricingThreeTier('Project entry points', 'Engagement models', 'A useful portfolio starter gives buyers enough commercial context to decide if they should inquire.', [
                        ['name' => 'Strategy Sprint', 'price' => 'EUR 12k', 'billing_period' => 'fixed', 'description' => 'For founders and product teams that need clarity before design production.', 'features' => ['Positioning review', 'Interface audit', 'Prototype direction'], 'is_recommended' => false, 'button_text' => 'Scope a sprint', 'button_link' => '#project-brief'],
                        ['name' => 'Launch System', 'price' => 'from EUR 24k', 'billing_period' => 'project', 'description' => 'For sites, product pages, and conversion systems that need to ship.', 'features' => ['Page system', 'Design components', 'Frontend handoff'], 'is_recommended' => true, 'button_text' => 'Brief a launch', 'button_link' => '#project-brief'],
                        ['name' => 'Product UI', 'price' => 'Custom', 'billing_period' => '', 'description' => 'For application interfaces, dashboards, and design systems with product teams.', 'features' => ['Workflow design', 'Component library', 'Engineering support'], 'is_recommended' => false, 'button_text' => 'Discuss product work', 'button_link' => '#contact'],
                    ]),
                    self::testimonialGrid('Client notes', 'Proof', [
                        ['quote' => 'They made our product feel obvious without sanding away what made it different.', 'author_name' => 'Elena Ross', 'author_title' => 'Founder, Aster Commerce'],
                        ['quote' => 'The final system gave our engineers structure and our editors room to move.', 'author_name' => 'Theo Martin', 'author_title' => 'Product Lead, Field Notes Media'],
                        ['quote' => 'Every review had a point. No decorative work, no vague rationale.', 'author_name' => 'Iris Beck', 'author_title' => 'Director, Civic Ledger'],
                    ]),
                    self::companyValues('How the studio makes decisions', 'Studio principles', 'Portfolio values are written as working rules a buyer can evaluate.', [
                        ['title' => 'Show the constraint', 'description' => 'Every case study names the audience, business pressure, and production limits.', 'icon' => 'frame'],
                        ['title' => 'Design for handoff', 'description' => 'Screens, tokens, components, and rationale are built for the team that owns the product next.', 'icon' => 'package-check'],
                        ['title' => 'Keep critique useful', 'description' => 'Reviews are direct, specific, and tied to the outcome rather than taste alone.', 'icon' => 'messages-square'],
                    ]),
                    self::teamGridMinimal('Studio team', 'Seeded team profiles help the portfolio feel inspectable and credible before final bios are written.', [
                        ['name' => 'Nina Halden', 'role' => 'Creative Director'],
                        ['name' => 'Oskar Fink', 'role' => 'Product Designer'],
                        ['name' => 'Mira Vogel', 'role' => 'Frontend Lead'],
                        ['name' => 'Sam Berger', 'role' => 'Research Partner'],
                    ]),
                    self::resourceLibrary('Project planning resources', 'Useful intake', [
                        ['title' => 'Project brief worksheet', 'type_label' => 'Worksheet', 'description' => 'A short guide for writing a useful first inquiry.', 'link' => '#project-brief'],
                        ['title' => 'Portfolio proof checklist', 'type_label' => 'Checklist', 'description' => 'What to include when turning project work into a convincing case study.', 'link' => '#case-studies'],
                        ['title' => 'Launch system map', 'type_label' => 'Template', 'description' => 'A content and component map for campaign or product launches.', 'link' => '#capabilities'],
                    ]),
                    self::ctaCard('Send a project worth discussing', 'The inquiry page asks for goals, constraints, timing, and the decision group so the first call starts with useful context.', 'Start a brief', '#project-brief', 'Availability'),
                    self::sitemapGrid('Explore Studio Halden', [
                        [
                            'title' => 'Work',
                            'pages' => [
                                ['label' => 'Selected Work', 'link' => '#selected-work'],
                                ['label' => 'Case Studies', 'link' => '#case-studies'],
                                ['label' => 'Results', 'link' => '#results'],
                            ],
                        ],
                        [
                            'title' => 'Studio',
                            'pages' => [
                                ['label' => 'Capabilities', 'link' => '#capabilities'],
                                ['label' => 'Process', 'link' => '#process'],
                                ['label' => 'Team', 'link' => '#team'],
                            ],
                        ],
                        [
                            'title' => 'Start',
                            'pages' => [
                                ['label' => 'Project Brief', 'link' => '#project-brief'],
                                ['label' => 'Contact', 'link' => '#contact'],
                                ['label' => 'Journal', 'link' => '#journal'],
                            ],
                        ],
                        [
                            'title' => 'Legal',
                            'pages' => [
                                ['label' => 'Privacy', 'link' => '#privacy'],
                                ['label' => 'Accessibility', 'link' => '#accessibility'],
                            ],
                        ],
                    ]),
                    self::footerBrand('Studio Halden', 'Senior interface work for product, launch, and editorial teams.', [
                        ['label' => 'Selected Work', 'link' => '#selected-work'],
                        ['label' => 'Project Brief', 'link' => '#project-brief'],
                        ['label' => 'Contact', 'link' => '#contact'],
                    ]),
                ],
            ],
            'subpages' => $subpages,
        ];
    }

    /**
     * @return StarterSite
     */
    private static function saas(): array
    {
        $subpages = [
            self::saasPage('Product Tour', 'product', 'Core product tour with jobs-to-be-done, workflows, and outcomes.', 'Product overview', ['Command center', 'Workflow builder', 'Evidence library']),
            self::saasPage('Use Cases', 'use-cases', 'Specific buying contexts for teams evaluating the product.', 'Use case library', ['Revenue operations', 'Customer success', 'Product marketing']),
            self::saasPage('Pricing', 'pricing', 'Plan choices, buyer objections, and frequently asked billing questions.', 'Pricing decision', ['Starter', 'Growth', 'Scale']),
            self::saasPage('Integrations', 'integrations', 'Connected tools, data sources, and API entry points.', 'Integration catalog', ['CRM sync', 'Warehouse export', 'Webhooks']),
            self::saasPage('Security', 'security', 'Security, compliance, data handling, and procurement proof.', 'Trust center', ['SSO', 'Audit logs', 'Data retention']),
            self::saasPage('Customers', 'customers', 'Customer proof, outcomes, and role-specific stories.', 'Customer proof', ['Scaleup teams', 'Enterprise pilots', 'Agency partners']),
            self::saasPage('Resources', 'resources', 'Guides, templates, and comparison content for demand capture.', 'Resource hub', ['Buyer guide', 'ROI worksheet', 'Migration plan']),
            self::saasPage('Docs', 'docs', 'Documentation landing page for implementation and product education.', 'Documentation', ['Getting started', 'API basics', 'Release notes']),
            self::saasPage('Changelog', 'changelog', 'Product updates, launch notes, and roadmap trust signals.', 'Product updates', ['Workflow rules', 'Dashboard exports', 'Permission presets']),
            self::saasPage('Contact Sales', 'contact-sales', 'Enterprise contact path with qualification content and next steps.', 'Sales route', ['Requirements call', 'Security review', 'Pilot scope']),
            self::saasPage('Start Trial', 'start-trial', 'Signup-oriented page with activation expectations and support promises.', 'Trial path', ['14-day trial', 'Guided setup', 'Success review']),
        ];
        $subpages = array_merge($subpages, self::supportPages('LaunchLayer', 'contact-sales'));

        return [
            'label' => 'SaaS starter',
            'slug' => 'saas',
            'rootSlug' => '/desiderio-saas-starter',
            'rootTitle' => 'LaunchLayer',
            'rootNavTitle' => 'Home',
            'abstract' => 'A SaaS starter with product narrative, use cases, pricing, integrations, security, customers, resources, docs, changelog, sales, trial, team, and utility pages.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
                    self::heroSaas('Launch workflows your team can measure', 'LaunchLayer', 'A SaaS starter for product-led teams that need a clear product story, proof, pricing, integrations, security, docs, changelog, sales, and trial paths from day one.', 'Start trial', '#start-trial', 'Contact sales', '#contact-sales', ['Northstar Labs', 'Helio Systems', 'Aster Commerce', 'Studio Atlas']),
                    self::navTabs('SaaS evaluation path', [
                        ['label' => 'Product', 'link' => '#product'],
                        ['label' => 'Use Cases', 'link' => '#use-cases'],
                        ['label' => 'Pricing', 'link' => '#pricing'],
                        ['label' => 'Security', 'link' => '#security'],
                        ['label' => 'Trial', 'link' => '#start-trial'],
                    ]),
                    self::headerSection('A SaaS starter should answer the full buyer journey', 'Starter strategy', 'The page tree supports product-led browsing, sales-led procurement, documentation, launch notes, trust review, and expansion proof.'),
                    self::metricDashboard('Product traction snapshot', 'Dummy SaaS metrics give the starter a useful product feel immediately.', [
                        ['label' => 'Activated teams', 'value' => '1,284', 'change' => '+19% in 30 days', 'trend' => 'up'],
                        ['label' => 'Workflow runs', 'value' => '82k', 'change' => '+31% this quarter', 'trend' => 'up'],
                        ['label' => 'Time saved', 'value' => '14h', 'change' => 'per team weekly', 'trend' => 'neutral'],
                        ['label' => 'Expansion intent', 'value' => '46%', 'change' => '+8 points', 'trend' => 'up'],
                    ], 'Activation', '%'),
                    self::featureCards('What the product replaces', 'Core value', 'Use the homepage to make the switching argument concrete.', [
                        ['title' => 'Scattered status docs', 'description' => 'Centralize plan, owner, evidence, and decision history in one workspace.'],
                        ['title' => 'Manual reporting', 'description' => 'Turn recurring updates into live dashboards and scheduled summaries.'],
                        ['title' => 'Unclear ownership', 'description' => 'Route reviews, approvals, and blockers to the person who can act.'],
                    ]),
                    self::resourceLibrary('Buyer enablement library', 'Useful resources', [
                        ['title' => 'Launch workflow buyer guide', 'type_label' => 'Guide', 'description' => 'A practical evaluation guide for operations, product, and revenue teams.', 'link' => '#resources'],
                        ['title' => 'Integration checklist', 'type_label' => 'Checklist', 'description' => 'Data sources, permissions, webhooks, and warehouse questions before setup.', 'link' => '#integrations'],
                        ['title' => 'Security review packet', 'type_label' => 'Trust', 'description' => 'SSO, audit logs, retention, subprocessors, and procurement contact route.', 'link' => '#security'],
                    ]),
                    self::pricingThreeTier('Simple plans for realistic demos', 'Pricing', 'Starter content includes actual plan rows and nested feature lists.', [
                        ['name' => 'Starter', 'price' => '$29', 'billing_period' => '/user/month', 'description' => 'For small teams replacing manual status reporting.', 'features' => ['3 workspaces', 'Core dashboards', 'Email support'], 'is_recommended' => false, 'button_text' => 'Start trial', 'button_link' => '#start-trial'],
                        ['name' => 'Growth', 'price' => '$79', 'billing_period' => '/user/month', 'description' => 'For growing teams coordinating multiple departments.', 'features' => ['Unlimited workspaces', 'Advanced automations', 'Priority onboarding'], 'is_recommended' => true, 'button_text' => 'Start trial', 'button_link' => '#start-trial'],
                        ['name' => 'Scale', 'price' => 'Custom', 'billing_period' => '', 'description' => 'For organizations with procurement, security, and data requirements.', 'features' => ['SSO and audit logs', 'Dedicated success plan', 'Security review support'], 'is_recommended' => false, 'button_text' => 'Contact sales', 'button_link' => '#contact-sales'],
                    ]),
                    self::testimonialGrid('Teams replacing launch chaos', 'Customer proof', [
                        ['quote' => 'LaunchLayer gave every launch owner the same operating picture without adding another reporting ritual.', 'author_name' => 'Helen Brooks', 'author_title' => 'VP Product, Northstar Labs'],
                        ['quote' => 'The security and integration pages answered the procurement questions before our first sales call.', 'author_name' => 'Marco Silva', 'author_title' => 'IT Lead, Helio Systems'],
                        ['quote' => 'The trial path sets expectations clearly enough for product-led onboarding.', 'author_name' => 'Sara Yu', 'author_title' => 'Growth Lead, Aster Commerce'],
                    ]),
                    self::faq('Buying questions', 'Answer practical objections before the visitor reaches sales.', [
                        ['question' => 'Can we use this starter as a real SaaS site skeleton?', 'answer' => 'Yes. The page tree covers the common evaluation path: product, use cases, pricing, integrations, security, resources, docs, changelog, sales, and trial.'],
                        ['question' => 'Does it include realistic content elements?', 'answer' => 'Yes. It uses real Desiderio Content Blocks with collection data, nested pricing features, metrics, charts, tables, and CTAs.'],
                        ['question' => 'Can editors replace everything later?', 'answer' => 'Yes. The command seeds normal TYPO3 pages and content records, so editors can revise, reorder, or delete blocks.'],
                    ]),
                    self::companyValues('Product promises', 'Operating principles', 'These are written as buyer-facing product principles that make the SaaS offer easier to trust.', [
                        ['title' => 'Every workflow has an owner', 'description' => 'Tasks, approvals, and blockers are visible before they become status meetings.', 'icon' => 'user-check'],
                        ['title' => 'Reports stay close to work', 'description' => 'Dashboards, summaries, and evidence are connected to the workflow that produced them.', 'icon' => 'bar-chart-3'],
                        ['title' => 'Procurement is not an afterthought', 'description' => 'Security, integration, and sales paths are ready for serious evaluation.', 'icon' => 'shield-check'],
                    ]),
                    self::teamGridMinimal('LaunchLayer team', 'A useful SaaS starter includes people and ownership content for about, trust, and sales pages.', [
                        ['name' => 'Ava Keller', 'role' => 'Founder and CEO'],
                        ['name' => 'Ben Ortega', 'role' => 'Product Lead'],
                        ['name' => 'Mina Shah', 'role' => 'Customer Success'],
                        ['name' => 'Leo Frank', 'role' => 'Security Engineering'],
                    ]),
                    self::ctaCard('Start with a measurable launch workflow', 'Use the seeded product, pricing, security, docs, changelog, and trial pages as the first pass of a serious SaaS website.', 'Start trial', '#start-trial', 'Product-led path'),
                    self::sitemapGrid('Explore LaunchLayer', [
                        [
                            'title' => 'Product',
                            'pages' => [
                                ['label' => 'Product Tour', 'link' => '#product'],
                                ['label' => 'Use Cases', 'link' => '#use-cases'],
                                ['label' => 'Integrations', 'link' => '#integrations'],
                            ],
                        ],
                        [
                            'title' => 'Buy',
                            'pages' => [
                                ['label' => 'Pricing', 'link' => '#pricing'],
                                ['label' => 'Customers', 'link' => '#customers'],
                                ['label' => 'Contact Sales', 'link' => '#contact-sales'],
                            ],
                        ],
                        [
                            'title' => 'Learn',
                            'pages' => [
                                ['label' => 'Resources', 'link' => '#resources'],
                                ['label' => 'Docs', 'link' => '#docs'],
                                ['label' => 'Changelog', 'link' => '#changelog'],
                            ],
                        ],
                        [
                            'title' => 'Trust',
                            'pages' => [
                                ['label' => 'Security', 'link' => '#security'],
                                ['label' => 'Privacy', 'link' => '#privacy'],
                                ['label' => 'Accessibility', 'link' => '#accessibility'],
                            ],
                        ],
                    ]),
                    self::footerBrand('LaunchLayer', 'Launch workflows, report progress, and keep ownership visible.', [
                        ['label' => 'Product', 'link' => '#product'],
                        ['label' => 'Pricing', 'link' => '#pricing'],
                        ['label' => 'Start Trial', 'link' => '#start-trial'],
                    ]),
                ],
            ],
            'subpages' => $subpages,
        ];
    }

    /**
     * @param list<string> $topics
     * @return StarterPage
     */
    private static function corporatePage(string $title, string $slug, string $abstract, string $eyebrow, array $topics): array
    {
        return self::page($title, $slug, $abstract, [
            self::headerSection($title, $eyebrow, $abstract, 'left'),
            self::textMedia($title . ' overview', $abstract, 'This page gives a buyer enough context to understand the offer, compare it with internal needs, and choose a next step without asking for a brochure.', 'media-right'),
            self::featureList($title . ' priorities', $eyebrow, 'The first content pass should answer the questions a serious stakeholder brings to this page.', array_map(
                static fn (string $topic, int $index): array => ['icon' => ['shield-check', 'settings', 'users'][$index % 3], 'title' => $topic, 'description' => 'Explain scope, ownership, and proof for ' . strtolower($topic) . '.'],
                $topics,
                array_keys($topics)
            )),
            self::faq($title . ' questions', 'Keep high-friction answers close to the decision.', [
                ['question' => 'Who is this page for?', 'answer' => 'For stakeholders who need a precise overview before they commit to a call, download, or internal discussion.'],
                ['question' => 'What should editors replace first?', 'answer' => 'Replace the examples with client-specific outcomes, sector language, and the strongest available proof.'],
                ['question' => 'Where should this page send visitors next?', 'answer' => 'Send them to a case study, a service detail page, or the contact route that matches their buying stage.'],
            ]),
            self::ctaCard('Discuss ' . strtolower($title), 'Use this page as the first draft, then tailor the claims and proof to the organization.', 'Talk to the team', '#contact', 'Corporate starter'),
        ]);
    }

    /**
     * @param list<array{0: string, 1: string, 2: string, 3: string}> $metrics
     * @return StarterPage
     */
    private static function dashboardPage(string $title, string $slug, string $abstract, string $seriesLabel, string $unit, array $metrics): array
    {
        $items = array_map(
            static fn (array $metric): array => ['label' => $metric[0], 'value' => $metric[1], 'change' => $metric[2], 'trend' => $metric[3]],
            $metrics
        );

        return self::page($title, $slug, $abstract, [
            self::headerSection($title, 'Dashboard', $abstract, 'left'),
            self::metricDashboard($title, $abstract, $items, $seriesLabel, $unit),
            self::chartLine($seriesLabel . ' trend', 'Dummy trend data for layout review and chart behavior.', $seriesLabel, $unit),
            self::dataTable($title . ' detail', ['Metric', 'Current', 'Target', 'Owner'], [
                [$metrics[0][0], $metrics[0][1], 'Improve', 'Mara'],
                [$metrics[1][0], $metrics[1][1], 'Hold', 'Jonas'],
                [$metrics[2][0], $metrics[2][1], 'Review', 'Sofia'],
            ]),
            self::alert($title . ' review note', 'info', 'This dummy dashboard page is intentionally populated so rail navigation, canvas density, and table states can be reviewed immediately.'),
        ], 'DesiderioContentpage');
    }

    /**
     * @param list<string> $storyTitles
     * @return StarterPage
     */
    private static function editorialPage(string $title, string $slug, string $abstract, string $eyebrow, array $storyTitles): array
    {
        return self::page($title, $slug, $abstract, [
            self::headerSection($title, $eyebrow, $abstract, 'left'),
            self::articleGrid($title . ' package', $eyebrow, array_map(
                static fn (string $story, int $index): array => ['title' => $story, 'category' => $title, 'description' => 'A realistic seeded story entry for the ' . strtolower($title) . ' section, ready for editors to replace with a real article.', 'link' => '#story-' . ($index + 1)],
                $storyTitles,
                array_keys($storyTitles)
            )),
            self::textMedia('Section promise', $abstract, 'Use this page to define what belongs in the section, who it serves, and how often it should be refreshed.', 'media-above'),
            self::resourceLibrary($title . ' resources', 'Further reading', [
                ['title' => $title . ' planning notes', 'type_label' => 'Brief', 'description' => 'Internal notes that explain the section angle and recurring formats.', 'link' => '#'],
                ['title' => $title . ' source list', 'type_label' => 'Reference', 'description' => 'People, reports, and examples the section can cite later.', 'link' => '#'],
                ['title' => $title . ' newsletter slot', 'type_label' => 'Newsletter', 'description' => 'How stories from this section should be summarized for subscribers.', 'link' => '#'],
            ]),
        ], 'DesiderioContentpageSidebar');
    }

    /**
     * @param list<string> $topics
     * @return StarterPage
     */
    private static function portfolioPage(string $title, string $slug, string $abstract, string $eyebrow, array $topics): array
    {
        return self::page($title, $slug, $abstract, [
            self::headerSection($title, $eyebrow, $abstract, 'left'),
            self::textMedia($title . ' overview', $abstract, 'Use this page to show judgment, not volume. Explain the context, decision constraints, and why this work matters.', 'media-left'),
            self::caseStudyGrid($title . ' examples', $eyebrow, array_map(
                static fn (string $topic, int $index): array => ['client_name' => $topic, 'summary' => 'A seeded portfolio proof point for ' . strtolower($topic) . '.', 'result' => ['Launch clarity', 'Reusable system', 'Sharper conversion'][$index % 3], 'link' => '#'],
                $topics,
                array_keys($topics)
            )),
            self::featureCards('How to read this page', 'Buyer context', 'Portfolio pages should help a buyer evaluate fit, taste, and operating reliability.', [
                ['title' => 'Context', 'description' => 'Name the constraints, audience, and business reason behind the work.'],
                ['title' => 'Judgment', 'description' => 'Explain the choices, tradeoffs, and what the team refused to do.'],
                ['title' => 'Outcome', 'description' => 'Connect visuals to measurable or observable change.'],
            ]),
            self::ctaCard('Start a project conversation', 'Bring the problem, constraints, timeline, and decision group. The project brief page turns that into a useful first call.', 'Open project brief', '#project-brief', 'Portfolio starter'),
        ]);
    }

    /**
     * @param list<string> $topics
     * @return StarterPage
     */
    private static function saasPage(string $title, string $slug, string $abstract, string $eyebrow, array $topics): array
    {
        $content = [
            self::headerSection($title, $eyebrow, $abstract, 'left'),
            self::textMedia($title . ' overview', $abstract, 'The copy should help a buyer understand what the product does, why it matters, and what they should inspect next.', 'media-right'),
            self::featureList($title . ' essentials', $eyebrow, 'Each item is seeded with concrete product context so the page is useful before final copywriting.', array_map(
                static fn (string $topic, int $index): array => ['icon' => ['rocket', 'database', 'settings'][$index % 3], 'title' => $topic, 'description' => 'Show how ' . strtolower($topic) . ' helps teams evaluate, adopt, or scale the product.'],
                $topics,
                array_keys($topics)
            )),
        ];

        if ($slug === 'pricing') {
            $content[] = self::pricingThreeTier('Plans for every rollout stage', 'Pricing', 'Keep pricing close to the evaluation questions that matter.', [
                ['name' => 'Starter', 'price' => '$29', 'billing_period' => '/user/month', 'description' => 'For a focused team proving the workflow.', 'features' => ['Core workspace', 'Three dashboards', 'Email support'], 'is_recommended' => false, 'button_text' => 'Start trial', 'button_link' => '#start-trial'],
                ['name' => 'Growth', 'price' => '$79', 'billing_period' => '/user/month', 'description' => 'For cross-functional teams standardizing reporting.', 'features' => ['Unlimited dashboards', 'Automation rules', 'Priority onboarding'], 'is_recommended' => true, 'button_text' => 'Start trial', 'button_link' => '#start-trial'],
                ['name' => 'Scale', 'price' => 'Custom', 'billing_period' => '', 'description' => 'For security, procurement, and advanced data needs.', 'features' => ['SSO', 'Audit logs', 'Dedicated success'], 'is_recommended' => false, 'button_text' => 'Contact sales', 'button_link' => '#contact-sales'],
            ]);
        } elseif ($slug === 'security') {
            $content[] = self::dataTable('Trust controls', ['Control', 'Starter', 'Growth', 'Scale'], [
                ['SSO', 'No', 'Optional', 'Included'],
                ['Audit logs', 'Basic', '90 days', 'Custom retention'],
                ['Security review', 'Self-serve', 'Assisted', 'Dedicated'],
            ]);
        } else {
            $content[] = self::metricDashboard($title . ' signals', 'Dummy product metrics make the page feel like a working SaaS site.', [
                ['label' => $topics[0] ?? 'Activation', 'value' => '74%', 'change' => '+8%', 'trend' => 'up'],
                ['label' => $topics[1] ?? 'Adoption', 'value' => '2.1k', 'change' => '+19%', 'trend' => 'up'],
                ['label' => $topics[2] ?? 'Risk', 'value' => '4', 'change' => '-2', 'trend' => 'down'],
            ], $title, '%');
        }

        $content[] = self::ctaCard('Move forward with ' . strtolower($title), 'Choose the next step that matches the buyer stage: self-serve trial, sales call, docs, or a resource.', 'Continue', '#start-trial', 'SaaS starter');

        return self::page($title, $slug, $abstract, $content);
    }

    /**
     * @param array<int, StarterBlock> $content
     * @return StarterPage
     */
    private static function page(string $title, string $slug, string $abstract, array $content, string $layout = 'DesiderioContentpage'): array
    {
        return [
            'title' => $title,
            'navTitle' => $title,
            'slug' => $slug,
            'layout' => $layout,
            'abstract' => $abstract,
            'content' => $content,
        ];
    }

    /**
     * @return list<StarterPage>
     */
    private static function supportPages(string $brand, string $contactSlug): array
    {
        return [
            self::page('Search', 'search', 'Find pages, guides, proof, and operational details across the starter site.', [
                self::searchHeader('Search ' . $brand, 'Site search', 'Use this page to test search presentation, empty states, and result density.', '/search', 'Search pages, proof, and resources'),
            ], 'DesiderioSearch'),
            self::page('404', '404', 'A useful error page that routes visitors back into the starter instead of ending the session.', [
                self::headerSection('This page is not available', '404', 'Route visitors to high-value pages instead of leaving them at a dead end.', 'center'),
                self::ctaCard('Return to a useful path', 'Go back to the homepage, search the site, or contact the team if something should exist here.', 'Go home', '/', 'Utility page'),
            ], 'DesiderioError'),
            self::page('Imprint', 'imprint', 'Company details and publishing responsibility for procurement, legal, and trust review.', [
                self::headerSection($brand . ' imprint', 'Legal', 'Seeded company information for review and replacement before launch.', 'left'),
                self::textMedia('Company information', 'Replace this with verified legal data before launch.', 'Registered company, business address, responsible editor, and contact route belong here. Keep the copy short and procurement-friendly.', 'media-above'),
                self::footerMinimal($brand . ' legal links'),
            ]),
            self::page('Privacy', 'privacy', 'Privacy expectations, request routes, and data handling notes for visitors and buyers.', [
                self::headerSection('Privacy at ' . $brand, 'Trust', 'A practical placeholder for privacy policy content, request handling, and data processing context.', 'left'),
                self::faq('Privacy questions', 'Replace these answers with reviewed legal copy before publishing.', [
                    ['question' => 'What should be listed here?', 'answer' => 'Data categories, processing purposes, retention periods, subprocessors, contact routes, and user rights.'],
                    ['question' => 'Who owns the policy?', 'answer' => 'Assign a legal or operations owner and include a durable privacy contact address.'],
                    ['question' => 'Where should forms link?', 'answer' => 'Route privacy requests to a monitored inbox or form with clear response expectations.'],
                ]),
                self::footerMinimal($brand . ' legal links'),
            ]),
            self::page('Accessibility', 'accessibility', 'Accessibility commitments, known limitations, and feedback routes for visitors.', [
                self::headerSection('Accessibility statement', 'Service quality', 'State the standard, current status, known limitations, and feedback route in plain language.', 'left'),
                self::featureList('Accessibility review points', 'Checklist', 'Use this starter page as a practical audit prompt before launch.', [
                    ['icon' => 'keyboard', 'title' => 'Keyboard paths', 'description' => 'Check navigation, forms, menus, tabs, and modal states without a mouse.'],
                    ['icon' => 'contrast', 'title' => 'Readable contrast', 'description' => 'Validate text, buttons, chart labels, and focus indicators across presets.'],
                    ['icon' => 'message-square', 'title' => 'Feedback route', 'description' => 'Give visitors a monitored channel for barriers and correction requests.'],
                ]),
                self::ctaCard('Report an accessibility issue', 'Describe the page, device, browser, assistive technology, and the barrier you found.', 'Contact the team', '#' . $contactSlug, 'Feedback'),
            ]),
        ];
    }

    /**
     * @param array<string, mixed> $fields
     * @return StarterBlock
     */
    private static function block(string $ctype, array $fields, int $colPos = 0): array
    {
        return [
            'ctype' => $ctype,
            'colPos' => $colPos,
            'fields' => $fields,
        ];
    }

    /**
     * @param list<array{value: string, label: string, stat_description: string}> $stats
     * @return StarterBlock
     */
    private static function heroStats(string $header, string $eyebrow, string $subheadline, string $buttonText, string $buttonLink, array $stats): array
    {
        return self::block('desiderio_herostats', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'primary_button_text' => $buttonText,
            'primary_button_link' => $buttonLink,
            'stats' => $stats,
        ]);
    }

    /**
     * @param list<string> $logos
     * @return StarterBlock
     */
    private static function heroSaas(string $header, string $badge, string $subheadline, string $primaryText, string $primaryLink, string $secondaryText, string $secondaryLink, array $logos): array
    {
        return self::block('desiderio_herosaas', [
            'header' => $header,
            'badge_text' => $badge,
            'subheadline' => $subheadline,
            'primary_button_text' => $primaryText,
            'primary_button_link' => $primaryLink,
            'secondary_button_text' => $secondaryText,
            'secondary_button_link' => $secondaryLink,
            'logo_items' => array_map(static fn (string $name): array => ['name' => $name], $logos),
        ]);
    }

    /**
     * @param list<string> $features
     * @return StarterBlock
     */
    private static function heroProduct(string $header, string $badge, string $subheadline, string $price, string $buttonText, string $buttonLink, array $features): array
    {
        return self::block('desiderio_heroproduct', [
            'header' => $header,
            'badge_text' => $badge,
            'subheadline' => $subheadline,
            'price' => $price,
            'primary_button_text' => $buttonText,
            'primary_button_link' => $buttonLink,
            'feature_items' => array_map(static fn (string $label): array => ['label' => $label], $features),
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function headerSection(string $header, string $eyebrow, string $subheadline, string $variant = 'center'): array
    {
        return self::block('desiderio_headersection', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'variant' => $variant,
        ]);
    }

    /**
     * @param list<array{label: string, link: string, active?: bool}> $tabs
     * @return StarterBlock
     */
    private static function navTabs(string $header, array $tabs, string $variant = 'pill'): array
    {
        return self::block('desiderio_navtabs', [
            'header' => $header,
            'variant' => $variant,
            'tabs' => array_map(
                static fn (array $tab, int $index): array => [
                    'label' => $tab['label'],
                    'link' => $tab['link'],
                    'active' => $tab['active'] ?? $index === 0,
                ],
                $tabs,
                array_keys($tabs)
            ),
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function searchHeader(string $header, string $eyebrow, string $subheadline, string $formAction, string $placeholder): array
    {
        return self::block('desiderio_searchheader', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'form_action' => $formAction,
            'placeholder' => $placeholder,
            'button_text' => 'Search',
        ]);
    }

    /**
     * @param list<array{title: string, pages: list<array{label: string, link: string}>}> $groups
     * @return StarterBlock
     */
    private static function sitemapGrid(string $header, array $groups): array
    {
        return self::block('desiderio_sitemapgrid', [
            'header' => $header,
            'columns' => '4',
            'groups' => $groups,
        ]);
    }

    /**
     * @param list<array{label: string, link: string}> $links
     * @return StarterBlock
     */
    private static function footerBrand(string $brand, string $tagline, array $links): array
    {
        return self::block('desiderio_footerbrand', [
            'header' => $brand . ' footer',
            'brand' => $brand,
            'tagline' => $tagline,
            'link_items' => $links,
            'copyright' => '(c) 2026 ' . $brand . '. Starter content for review.',
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function footerMinimal(string $header): array
    {
        return self::block('desiderio_footerminimal', [
            'header' => $header,
            'copyright' => '(c) 2026 Desiderio starter site.',
            'link_items' => [
                ['label' => 'Imprint', 'link' => '#imprint'],
                ['label' => 'Privacy', 'link' => '#privacy'],
                ['label' => 'Accessibility', 'link' => '#accessibility'],
            ],
        ]);
    }

    /**
     * @param list<array{name: string, role: string}> $members
     * @return StarterBlock
     */
    private static function teamGridMinimal(string $header, string $subheadline, array $members): array
    {
        return self::block('desiderio_teamgridminimal', [
            'header' => $header,
            'subheadline' => $subheadline,
            'columns' => '4',
            'members' => $members,
        ]);
    }

    /**
     * @param list<array{title: string, description: string, icon: string}> $values
     * @return StarterBlock
     */
    private static function companyValues(string $header, string $eyebrow, string $subheadline, array $values): array
    {
        return self::block('desiderio_companyvalues', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'value_items' => $values,
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function textMedia(string $header, string $subheadline, string $content, string $layout): array
    {
        return self::block('desiderio_textmedia', [
            'header' => $header,
            'subheadline' => $subheadline,
            'content' => '<p>' . $content . '</p>',
            'shadcn_layout' => $layout,
            'button_text' => 'Review details',
            'button_link' => '#',
        ]);
    }

    /**
     * @param list<array{icon: string, title: string, description: string}> $items
     * @return StarterBlock
     */
    private static function featureList(string $header, string $eyebrow, string $subheadline, array $items): array
    {
        return self::block('desiderio_featurelist', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'shadcn_layout' => 'two-columns',
            'items' => $items,
        ]);
    }

    /**
     * @param list<array{title: string, description: string}> $items
     * @return StarterBlock
     */
    private static function featureCards(string $header, string $eyebrow, string $subheadline, array $items): array
    {
        return self::block('desiderio_featurecards', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'items' => $items,
        ]);
    }

    /**
     * @param list<array{value: string, label: string, detail_text: string, trend: string}> $items
     * @return StarterBlock
     */
    private static function kpiCards(string $header, string $eyebrow, string $subheadline, array $items): array
    {
        return self::block('desiderio_kpicards', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'columns' => '4',
            'items' => $items,
        ]);
    }

    /**
     * @param list<array{label: string, value: string, change: string, trend: string}> $items
     * @return StarterBlock
     */
    private static function metricDashboard(string $header, string $description, array $items, string $seriesLabel, string $unit): array
    {
        return self::block('desiderio_metricdashboard', [
            'header' => $header,
            'description' => $description,
            'items' => $items,
            'chart_data' => self::chartJson([42, 58, 63, 79, 88, 96]),
            'chart_series_label' => $seriesLabel,
            'chart_unit' => $unit,
        ]);
    }

    /**
     * @param list<string> $headers
     * @param list<list<string>> $rows
     * @return StarterBlock
     */
    private static function dataTable(string $header, array $headers, array $rows): array
    {
        return self::block('desiderio_datatable', [
            'header' => $header,
            'show_header_row' => true,
            'column_definitions' => array_map(
                static fn (string $label): array => ['column_label' => $label, 'column_align' => 'left'],
                $headers
            ),
            'rows' => array_map(
                static fn (array $row): array => [
                    'row_label' => $row[0] ?? '',
                    'cells' => array_map(static fn (string $value): array => ['value' => $value], $row),
                ],
                $rows
            ),
            'show_caption' => true,
            'caption_text' => 'Seeded starter data for layout and content review.',
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function chartLine(string $header, string $description, string $seriesLabel, string $unit): array
    {
        return self::block('desiderio_chartline', [
            'header' => $header,
            'description' => $description,
            'series_label' => $seriesLabel,
            'unit' => $unit,
            'data_points' => "Jan 42\nFeb 58\nMar 63\nApr 79\nMay 88\nJun 96",
            'chart_data' => self::chartJson([42, 58, 63, 79, 88, 96]),
            'color_variant' => 'primary',
            'show_dots' => true,
            'smooth' => true,
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function alert(string $header, string $variant, string $description): array
    {
        return self::block('desiderio_alert', [
            'header' => $header,
            'variant' => $variant,
            'description' => '<p>' . $description . '</p>',
            'icon' => $variant === 'success' ? 'check-circle' : 'info',
            'show_close_button' => false,
        ]);
    }

    /**
     * @param list<array{client_name: string, summary: string, result: string, link: string}> $cases
     * @return StarterBlock
     */
    private static function caseStudyGrid(string $header, string $eyebrow, array $cases): array
    {
        return self::block('desiderio_casestudygrid', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'columns' => '3',
            'cases' => $cases,
        ]);
    }

    /**
     * @param list<array{quote: string, author_name: string, author_title: string}> $testimonials
     * @return StarterBlock
     */
    private static function testimonialGrid(string $header, string $eyebrow, array $testimonials): array
    {
        return self::block('desiderio_testimonialgrid', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'columns' => '3',
            'testimonials' => $testimonials,
        ]);
    }

    /**
     * @param list<array{title: string, category: string, description: string, link: string}> $items
     * @return StarterBlock
     */
    private static function articleGrid(string $header, string $eyebrow, array $items): array
    {
        return self::block('desiderio_articlegrid', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'items' => $items,
        ]);
    }

    /**
     * @param list<array{title: string, type_label: string, description: string, link: string}> $items
     * @return StarterBlock
     */
    private static function resourceLibrary(string $header, string $eyebrow, array $items): array
    {
        return self::block('desiderio_resourcelibrary', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'items' => $items,
        ]);
    }

    /**
     * @param list<array{name: string, price: string, billing_period: string, description: string, features: list<string>, is_recommended: bool, button_text: string, button_link: string}> $plans
     * @return StarterBlock
     */
    private static function pricingThreeTier(string $header, string $eyebrow, string $subheadline, array $plans): array
    {
        return self::block('desiderio_pricingthreetier', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'plans' => array_map(
                static fn (array $plan): array => [
                    ...$plan,
                    'features' => array_map(static fn (string $feature): array => ['text' => $feature], $plan['features']),
                ],
                $plans
            ),
        ]);
    }

    /**
     * @param list<array{question: string, answer: string}> $items
     * @return StarterBlock
     */
    private static function faq(string $header, string $subheadline, array $items): array
    {
        return self::block('desiderio_faq', [
            'header' => $header,
            'subheadline' => $subheadline,
            'items' => array_map(
                static fn (array $item): array => ['question' => $item['question'], 'answer' => '<p>' . $item['answer'] . '</p>'],
                $items
            ),
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function ctaCard(string $header, string $description, string $ctaText, string $ctaLink, string $badge): array
    {
        return self::block('desiderio_ctacard', [
            'header' => $header,
            'description' => $description,
            'cta_text' => $ctaText,
            'cta_link' => $ctaLink,
            'badge' => $badge,
        ]);
    }

    /**
     * @param list<int> $values
     */
    private static function chartJson(array $values): string
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $points = [];
        foreach ($values as $index => $value) {
            $points[] = [
                'label' => $labels[$index] ?? 'M' . ($index + 1),
                'value' => $value,
            ];
        }

        $json = json_encode($points, JSON_UNESCAPED_SLASHES);
        return is_string($json) ? $json : '[]';
    }
}
