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
            self::corporatePage('Solutions', 'solutions', 'Service lines grouped by the business problem they solve.', 'Solution portfolio', ['Operating model design', 'Platform modernization', 'Customer experience systems']),
            self::corporatePage('Services', 'services', 'A practical view of retained, project, and advisory engagement models.', 'Delivery model', ['Discovery sprint', 'Implementation team', 'Managed improvement']),
            self::corporatePage('Industries', 'industries', 'Sector-specific entry points for regulated and operationally complex organizations.', 'Industry focus', ['Financial services', 'Healthcare operations', 'Manufacturing platforms']),
            self::corporatePage('Case Studies', 'case-studies', 'Proof stories that connect measurable outcomes with the work behind them.', 'Evidence library', ['Claims portal rebuild', 'Partner onboarding hub', 'Service desk transformation']),
            self::corporatePage('About', 'about', 'Company principles, operating cadence, and why clients trust the team.', 'Company profile', ['Clear ownership', 'Calm governance', 'Long-term maintainability']),
            self::corporatePage('Leadership', 'leadership', 'Decision makers, delivery leads, and the roles clients meet first.', 'Leadership team', ['Client partner', 'Delivery principal', 'Technical director']),
            self::corporatePage('Careers', 'careers', 'Open roles, candidate expectations, and how the organization supports strong work.', 'Hiring', ['Senior consultant', 'Product engineer', 'Delivery manager']),
            self::corporatePage('Resources', 'resources', 'Guides, checklists, and briefings for enterprise teams planning change.', 'Resource center', ['Migration checklist', 'Governance template', 'Service review guide']),
            self::corporatePage('Insights', 'insights', 'Opinionated articles on operations, technology decisions, and service design.', 'Field notes', ['Modernization budget risks', 'Vendor handoff patterns', 'Executive reporting']),
            self::corporatePage('Contact', 'contact', 'Routes for new business, press, support, and partner conversations.', 'Contact desk', ['Sales inquiry', 'Partnership request', 'Support escalation']),
            self::corporatePage('Legal', 'legal', 'Imprint, privacy, and compliance entry points for procurement reviews.', 'Legal center', ['Company details', 'Privacy request', 'Terms summary']),
        ];

        return [
            'label' => 'Corporate starter',
            'slug' => 'corporate',
            'rootSlug' => '/desiderio-corporate-starter',
            'rootTitle' => 'Operations That Hold Up Under Pressure',
            'rootNavTitle' => 'Home',
            'abstract' => 'A complete corporate site for a B2B service organization: clear positioning, proof, governance, resources, hiring, and contact paths.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
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
                    self::ctaCard('Ready to map the first decision?', 'Book a 45-minute fit call and leave with a practical outline of the pages, proof, and workflows your site needs.', 'Plan the site', '#contact', 'Next step'),
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

        return [
            'label' => 'Dashboard starter',
            'slug' => 'dashboard',
            'rootSlug' => '/desiderio-dashboard-starter',
            'rootTitle' => 'Command Center',
            'rootNavTitle' => 'Overview',
            'abstract' => 'A dummy analytics application with realistic dashboards, operational pages, data tables, charts, alerts, and settings.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
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
                    self::alert('Live workspace note', 'success', 'The starter includes dummy dashboards for every navigation section so product teams can review density, charts, and tables immediately.'),
                ],
            ],
            'subpages' => array_map(
                static fn (array $page): array => self::dashboardPage(...$page),
                $dashboardPages
            ),
        ];
    }

    /**
     * @return StarterSite
     */
    private static function editorial(): array
    {
        $subpages = [
            self::editorialPage('Latest', 'latest', 'Fresh dispatches, short notes, and newly published analysis.', 'Latest stories', ['Weekend brief: AI procurement pressure', 'How teams edit policy pages', 'A quieter release note pattern']),
            self::editorialPage('Analysis', 'analysis', 'Deeper interpretation for strategy, design, and technology decisions.', 'Analysis desk', ['The cost of generic dashboards', 'What content debt really signals', 'Why governance pages convert']),
            self::editorialPage('Features', 'features', 'Long-form reporting built for immersive reading and strong article pages.', 'Feature package', ['Inside a regional service rebuild', 'A field guide to migration calm', 'How design systems earn trust']),
            self::editorialPage('Interviews', 'interviews', 'Conversations with operators, editors, founders, and technical leads.', 'Interview room', ['A CTO on measuring adoption', 'An editor on reusable briefs', 'A COO on service design']),
            self::editorialPage('Guides', 'guides', 'Practical playbooks readers can act on after one session.', 'Guide shelf', ['Homepage evidence checklist', 'Dashboard page planning', 'Editorial taxonomy starter']),
            self::editorialPage('Opinion', 'opinion', 'Pointed essays that create a recognizable editorial voice.', 'Opinion column', ['Stop hiding pricing context', 'The false economy of thin case studies', 'Search pages deserve editorial care']),
            self::editorialPage('Reviews', 'reviews', 'Structured reviews of tools, patterns, launches, and public examples.', 'Review desk', ['Dashboard onboarding review', 'Pricing page teardown', 'Article template audit']),
            self::editorialPage('Culture', 'culture', 'Human stories that make the publication more than a resource database.', 'Culture notes', ['How remote editorial rituals work', 'The meeting after publication', 'A glossary people actually use']),
            self::editorialPage('Podcasts', 'podcasts', 'Audio episodes, show notes, and transcripts for recurring programs.', 'Audio desk', ['The Content Ops Brief', 'Release Notes Live', 'The Governance Room']),
            self::editorialPage('Newsletter', 'newsletter', 'A subscription page with proof, archive links, and reader expectations.', 'Newsletter offer', ['Tuesday briefing', 'Monthly teardown', 'Subscriber Q and A']),
            self::editorialPage('About', 'about', 'Mission, editorial standards, masthead, and contribution paths.', 'About the publication', ['Editorial promise', 'Standards and corrections', 'How to pitch']),
        ];

        return [
            'label' => 'Editorial starter',
            'slug' => 'editorial',
            'rootSlug' => '/desiderio-editorial-starter',
            'rootTitle' => 'The Useful Index',
            'rootNavTitle' => 'Home',
            'abstract' => 'A full editorial starter with sections, story packages, resource shelves, newsletter prompts, and article-like subpages.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
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
                    self::resourceLibrary('Reader tools', 'Useful downloads', [
                        ['title' => 'Editorial launch checklist', 'type_label' => 'Checklist', 'description' => 'A practical order of operations for launching a publication.', 'link' => '#guides'],
                        ['title' => 'Homepage evidence planner', 'type_label' => 'Worksheet', 'description' => 'Map proof, objections, and conversion prompts before writing copy.', 'link' => '#analysis'],
                        ['title' => 'Issue calendar model', 'type_label' => 'Template', 'description' => 'A simple planning structure for recurring packages and newsletters.', 'link' => '#newsletter'],
                    ]),
                    self::ctaCard('Build the first issue', 'Use the generated pages as the first editorial calendar: publish one anchor story, two guides, and a newsletter issue.', 'Plan the issue', '#newsletter', 'Editorial workflow'),
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
            self::portfolioPage('Case Studies', 'case-studies', 'Longer project stories that connect brief, process, and outcome.', 'Case study archive', ['Revenue cockpit', 'Patient portal', 'Membership relaunch']),
            self::portfolioPage('Services', 'services', 'Offer structure for strategy, design systems, product UI, and launch work.', 'Studio services', ['Product strategy', 'Interface systems', 'Launch execution']),
            self::portfolioPage('Process', 'process', 'How the studio works from first call through shipped work.', 'Working model', ['Audit', 'Prototype', 'Build', 'Handoff']),
            self::portfolioPage('About', 'about', 'Studio story, principles, operating model, and what makes the team credible.', 'Studio profile', ['Small senior team', 'Clear critique', 'Production fluency']),
            self::portfolioPage('Studio', 'studio', 'People, collaborators, location, and the creative operating environment.', 'Studio life', ['Core team', 'Specialist network', 'Review rituals']),
            self::portfolioPage('Clients', 'clients', 'Client mix, testimonials, sectors, and proof signals.', 'Client proof', ['SaaS scaleups', 'Editorial brands', 'B2B services']),
            self::portfolioPage('Awards', 'awards', 'Recognition, press, talks, and external validation.', 'Recognition', ['Design annual', 'Launch award', 'Conference talk']),
            self::portfolioPage('Journal', 'journal', 'Thinking behind recent work, methods, and design observations.', 'Studio journal', ['Interface density', 'Portfolio proof', 'Launch rituals']),
            self::portfolioPage('Contact', 'contact', 'Project inquiry, availability, and fit criteria.', 'Project inquiry', ['Budget range', 'Timeline', 'Decision group']),
            self::portfolioPage('Project Brief', 'project-brief', 'A guided intake page that helps leads write a useful first request.', 'Brief builder', ['Goal', 'Audience', 'Constraints']),
        ];

        return [
            'label' => 'Portfolio starter',
            'slug' => 'portfolio',
            'rootSlug' => '/desiderio-portfolio-starter',
            'rootTitle' => 'Interfaces With a Point of View',
            'rootNavTitle' => 'Home',
            'abstract' => 'A portfolio starter for a senior studio: selected work, project proof, service model, process, clients, journal, and inquiry flow.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
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
                    self::testimonialGrid('Client notes', 'Proof', [
                        ['quote' => 'They made our product feel obvious without sanding away what made it different.', 'author_name' => 'Elena Ross', 'author_title' => 'Founder, Aster Commerce'],
                        ['quote' => 'The final system gave our engineers structure and our editors room to move.', 'author_name' => 'Theo Martin', 'author_title' => 'Product Lead, Field Notes Media'],
                        ['quote' => 'Every review had a point. No decorative work, no vague rationale.', 'author_name' => 'Iris Beck', 'author_title' => 'Director, Civic Ledger'],
                    ]),
                    self::ctaCard('Send a project worth discussing', 'The inquiry page asks for goals, constraints, timing, and the decision group so the first call starts with useful context.', 'Start a brief', '#project-brief', 'Availability'),
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
            self::saasPage('Product', 'product', 'Core product tour with jobs-to-be-done, workflows, and outcomes.', 'Product overview', ['Command center', 'Workflow builder', 'Evidence library']),
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

        return [
            'label' => 'SaaS starter',
            'slug' => 'saas',
            'rootSlug' => '/desiderio-saas-starter',
            'rootTitle' => 'Launch Workflows Your Team Can Measure',
            'rootNavTitle' => 'Home',
            'abstract' => 'A SaaS starter with product narrative, use cases, pricing, integrations, trust pages, resources, docs, changelog, sales, and trial flows.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
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
                    self::pricingThreeTier('Simple plans for realistic demos', 'Pricing', 'Starter content includes actual plan rows and nested feature lists.', [
                        ['name' => 'Starter', 'price' => '$29', 'billing_period' => '/user/month', 'description' => 'For small teams replacing manual status reporting.', 'features' => ['3 workspaces', 'Core dashboards', 'Email support'], 'is_recommended' => false, 'button_text' => 'Start trial', 'button_link' => '#start-trial'],
                        ['name' => 'Growth', 'price' => '$79', 'billing_period' => '/user/month', 'description' => 'For growing teams coordinating multiple departments.', 'features' => ['Unlimited workspaces', 'Advanced automations', 'Priority onboarding'], 'is_recommended' => true, 'button_text' => 'Start trial', 'button_link' => '#start-trial'],
                        ['name' => 'Scale', 'price' => 'Custom', 'billing_period' => '', 'description' => 'For organizations with procurement, security, and data requirements.', 'features' => ['SSO and audit logs', 'Dedicated success plan', 'Security review support'], 'is_recommended' => false, 'button_text' => 'Contact sales', 'button_link' => '#contact-sales'],
                    ]),
                    self::faq('Buying questions', 'Answer practical objections before the visitor reaches sales.', [
                        ['question' => 'Can we use this starter as a real SaaS site skeleton?', 'answer' => 'Yes. The page tree covers the common evaluation path: product, use cases, pricing, integrations, security, resources, docs, changelog, sales, and trial.'],
                        ['question' => 'Does it include realistic content elements?', 'answer' => 'Yes. It uses real Desiderio Content Blocks with collection data, nested pricing features, metrics, charts, tables, and CTAs.'],
                        ['question' => 'Can editors replace everything later?', 'answer' => 'Yes. The command seeds normal TYPO3 pages and content records, so editors can revise, reorder, or delete blocks.'],
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
