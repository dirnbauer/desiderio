<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Corporate starter site for the desiderio:starter:seed command.
 *
 * The starter is a real page tree: one homepage plus at least ten subpages.
 * Content intentionally uses existing Desiderio Content Blocks, not isolated
 * placeholder records, so generated sites are useful review targets.
 *
 * @phpstan-type StarterBlock array{ctype: string, colPos: int, fields: array<string, mixed>}
 * @phpstan-type StarterHome array{layout: string, content: array<int, StarterBlock>}
 * @phpstan-type StarterPage array{title: string, navTitle: string, slug: string, layout: string, abstract: string, navHidden: bool, content: array<int, StarterBlock>}
 * @phpstan-type StarterSite array{label: string, slug: string, rootSlug: string, rootTitle: string, rootNavTitle: string, purpose: string, abstract: string, home: StarterHome, subpages: array<int, StarterPage>}
 * @phpstan-type CorporatePageCopy array{
 *     abstract: string,
 *     eyebrow: string,
 *     headline: string,
 *     intro: string,
 *     overview: array{header: string, lead: string, text: string},
 *     priorities: array{header: string, eyebrow: string, lead: string, items: list<array{title: string, description: string}>},
 *     faq: array{header: string, lead: string, items: list<array{question: string, answer: string}>},
 *     cta: array{header: string, text: string, label: string, badge: string}
 * }
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
        $subpages = self::withTopNavigation(
            self::corporateSubpages(),
            ['advisory-services', 'implementation-office', 'managed-improvement', 'sector-playbooks', 'case-studies', 'contact']
        );
        $subpages = array_merge($subpages, self::supportPages('Northstar Advisory Group', 'contact'));

        return [
            'label' => 'Corporate starter',
            'slug' => 'corporate',
            'rootSlug' => '/desiderio-corporate-starter',
            'rootTitle' => 'Northstar Advisory Group',
            'rootNavTitle' => 'Home',
            'purpose' => 'Help enterprise buyers understand the offer, trust the delivery model, and start a qualified procurement-safe conversation.',
            'abstract' => 'Northstar Advisory Group helps companies change how they operate, with clear governance, proof of results and support for procurement.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
                    self::heroStats('Operational change, delivered with a clear plan', 'Northstar Advisory Group', 'We help executive teams replace fragile internal workflows. Advice, delivery and ongoing improvement, with clear governance and support for procurement.', 'Book a call', '#contact', [
                        ['value' => '42%', 'label' => 'Less manual handover', 'stat_description' => 'After replacing regional spreadsheet workflows.'],
                        ['value' => '11', 'label' => 'Departments onboarded', 'stat_description' => 'Legal, finance, operations, support and regional teams.'],
                        ['value' => '24h', 'label' => 'Escalation response', 'stat_description' => 'Our contracted response time for production issues.'],
                        ['value' => '99.9%', 'label' => 'Uptime of critical processes', 'stat_description' => 'Across client portals and reporting workflows.'],
                    ]),
                    self::navTabs('On this site', [
                        ['label' => 'Services', 'link' => '#advisory-services'],
                        ['label' => 'Results', 'link' => '#case-studies'],
                        ['label' => 'Governance', 'link' => '#governance'],
                        ['label' => 'Procurement', 'link' => '#procurement'],
                        ['label' => 'Contact', 'link' => '#contact'],
                    ]),
                    self::headerSection('Find what you need to decide', 'For buyers', 'This site follows the questions buyers ask: what we do, how we deliver, who leads the work, what results we have and how to buy.'),
                    self::kpiCards('Results across our programmes', 'Client results', 'Averages across client programmes since 2019.', [
                        ['value' => '27', 'label' => 'Programmes delivered', 'detail_text' => 'In finance, health, manufacturing and public services.', 'trend' => 'positive'],
                        ['value' => '6 weeks', 'label' => 'To the first release', 'detail_text' => 'Average time from kickoff to the first live change.', 'trend' => 'positive'],
                        ['value' => '94%', 'label' => 'Clients who renew', 'detail_text' => 'Share of clients who continue with managed improvement.', 'trend' => 'positive'],
                        ['value' => '4.7/5', 'label' => 'Stakeholder rating', 'detail_text' => 'From surveys at the end of each programme.', 'trend' => 'neutral'],
                    ]),
                    self::featureCards('Answers for every stakeholder', 'Who we help', 'Each group gets the information it needs to decide.', [
                        ['title' => 'For executives', 'description' => 'Outcomes in plain language, commercial fit and results that lower the risk of buying.'],
                        ['title' => 'For delivery teams', 'description' => 'Service lines, the governance rhythm and clear handover rules.'],
                        ['title' => 'For procurement', 'description' => 'Legal, compliance, references and contacts, ready for review.'],
                    ]),
                    self::pricingThreeTier('Three ways to work with us', 'Engagement models', 'Compare the options before we talk. Prices exclude VAT.', [
                        ['name' => 'Advisory Sprint', 'price' => 'EUR 18k', 'billing_period' => 'fixed scope', 'description' => 'For teams that need a diagnosis, priorities and a plan for the board.', 'features' => ['Review of the current state', 'Map of risks and dependencies', 'Recommendation for the board'], 'is_recommended' => false, 'button_text' => 'Discuss advisory', 'button_link' => '#contact'],
                        ['name' => 'Implementation Office', 'price' => 'Custom', 'billing_period' => 'programme', 'description' => 'For portal, workflow and reporting programmes that need a senior owner.', 'features' => ['Delivery governance', 'Product and engineering team', 'Stakeholder reporting'], 'is_recommended' => true, 'button_text' => 'Scope delivery', 'button_link' => '#contact'],
                        ['name' => 'Managed Improvement', 'price' => 'Retainer', 'billing_period' => 'monthly', 'description' => 'For organisations that want steady improvement after launch.', 'features' => ['Monthly roadmap', 'Reliability reviews', 'Operations dashboards'], 'is_recommended' => false, 'button_text' => 'Plan a retainer', 'button_link' => '#contact'],
                    ]),
                    self::caseStudyGrid('Selected results', 'Case studies', [
                        ['client_name' => 'Nordline Finance', 'summary' => 'Combined five regional onboarding flows into one portal.', 'result' => '42% faster activation', 'link' => '#case-studies'],
                        ['client_name' => 'Helio Health', 'summary' => 'Modernised patient services without disrupting frontline teams.', 'result' => '18k requests a month', 'link' => '#case-studies'],
                        ['client_name' => 'Mason Works', 'summary' => 'Built executive reporting that matches daily operations.', 'result' => '9 weekly reports retired', 'link' => '#case-studies'],
                    ]),
                    self::testimonialGrid('Why clients stay', 'Client voices', [
                        ['quote' => 'They turned a fragile internal process into a service our leadership trusts.', 'author_name' => 'Amelia Grant', 'author_title' => 'COO, Nordline Finance'],
                        ['quote' => 'We got a clear plan, a calm rollout and fewer surprises than in any earlier project.', 'author_name' => 'Markus Renner', 'author_title' => 'VP Operations, Helio Health'],
                        ['quote' => 'Their meeting rhythm made complex work easy to follow for non-technical colleagues.', 'author_name' => 'Priya Shah', 'author_title' => 'Transformation Lead, Mason Works'],
                    ]),
                    self::companyValues('How we work', 'Our principles', 'Each principle is a promise we keep in every programme.', [
                        ['title' => 'Make work visible', 'description' => 'Every programme has a decision log, a named owner for each task and a record of evidence.', 'icon' => 'list-checks'],
                        ['title' => 'Remove friction', 'description' => 'We cut the handovers and reporting loops that slow frontline teams down.', 'icon' => 'workflow'],
                        ['title' => 'Stay accountable after launch', 'description' => 'Improvement continues through regular reviews, not only until handover.', 'icon' => 'shield-check'],
                    ]),
                    self::teamGridMinimal('Our leadership', 'Meet the senior people who lead your programme.', [
                        self::teamMember('Mara Stein', 'Managing Partner, Advisory', 0),
                        self::teamMember('Jonas Feld', 'Delivery Principal', 1),
                        self::teamMember('Priya Nair', 'Technical Director', 2),
                        self::teamMember('Elena Vogt', 'Client Operations Lead', 3),
                    ]),
                    self::resourceLibrary('Guides and templates', 'Downloads', [
                        ['title' => 'Readiness checklist', 'type_label' => 'Checklist', 'description' => 'Questions to answer before you scope a programme.', 'link' => '#resources'],
                        ['title' => 'Governance meeting template', 'type_label' => 'Template', 'description' => 'A short agenda for weekly evidence, risk and decision reviews.', 'link' => '#governance'],
                        ['title' => 'Service portal brief', 'type_label' => 'Brief', 'description' => 'A one-page outline for replacing fragile internal workflows.', 'link' => '#advisory-services'],
                    ]),
                    self::ctaCard('Ready to plan the first step?', 'Book a 45-minute call. You leave with a first outline of the work and the decisions ahead.', 'Book a call', '#contact', 'Next step'),
                    self::sitemapGrid('Explore Northstar', [
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
                ],
            ],
            'subpages' => $subpages,
        ];
    }

    /**
     * The eleven content pages below the home page. Every page has the same
     * five blocks (header section, text-media, feature list, FAQ, CTA card),
     * each with its own copy.
     *
     * @return list<StarterPage>
     */
    private static function corporateSubpages(): array
    {
        return [
            self::corporatePage('Advisory Services', 'advisory-services', [
                'abstract' => 'The Advisory Sprint for EUR 18k: a review of how your operations work today, a map of risks and a recommendation for your board.',
                'eyebrow' => 'Advisory Sprint',
                'headline' => 'Know what to change before you commit budget',
                'intro' => 'The Advisory Sprint is a fixed-scope review for EUR 18k. You get a clear diagnosis, agreed priorities and a plan your board can approve.',
                'overview' => [
                    'header' => 'How the Advisory Sprint works',
                    'lead' => 'Mara Stein, our Managing Partner for advisory, leads every sprint with a small senior team.',
                    'text' => 'We interview your leaders and frontline teams, review workflows and data, and share findings every week. The sprint ends with a board session.',
                ],
                'priorities' => [
                    'header' => 'What you get from the sprint',
                    'eyebrow' => 'Deliverables',
                    'lead' => 'Three results you can take to your board.',
                    'items' => [
                        ['title' => 'Review of the current state', 'description' => 'How work flows today, where it stalls and what that costs, based on interviews and your own data.'],
                        ['title' => 'Map of risks and dependencies', 'description' => 'Which systems, teams and contracts each change depends on, and what could delay it.'],
                        ['title' => 'Recommendation for the board', 'description' => 'The options with cost, effort and expected results, and the one we recommend.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about the Advisory Sprint',
                    'lead' => 'Answers on price, timing and what happens next.',
                    'items' => [
                        ['question' => 'What does the Advisory Sprint cost?', 'answer' => 'EUR 18k, excluding VAT. The scope is fixed, so the price does not change. It includes the interviews, the analysis, all documents and the board session.'],
                        ['question' => 'How long does the sprint take?', 'answer' => 'Four weeks from kickoff to the board session. Your team takes part in the interviews and in one short review each week.'],
                        ['question' => 'Do we have to hire you afterwards?', 'answer' => 'No. The recommendation is yours, and any team can deliver it. If you continue with us, the Implementation Office starts from the sprint results, so no work is repeated.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Start with a 45-minute call',
                    'text' => 'Tell us what you want to change. Together we check whether a sprint is the right first step.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Implementation Office', 'implementation-office', [
                'abstract' => 'Portal, workflow and reporting programmes with one senior owner, clear governance and the first live change after 6 weeks on average.',
                'eyebrow' => 'Programme delivery',
                'headline' => 'One senior owner from plan to live service',
                'intro' => 'We deliver portal, workflow and reporting programmes with our own product and engineering team. On average, the first change goes live 6 weeks after kickoff.',
                'overview' => [
                    'header' => 'How the Implementation Office works',
                    'lead' => 'Jonas Feld, our Delivery Principal, oversees every programme. A named delivery lead runs it day to day.',
                    'text' => 'Your people join our product and engineering team. We release in small steps, report every week and hand everything over before we leave.',
                ],
                'priorities' => [
                    'header' => 'What the office runs for you',
                    'eyebrow' => 'Scope',
                    'lead' => 'Three areas we own for the whole programme.',
                    'items' => [
                        ['title' => 'Delivery governance', 'description' => 'A plan, a budget and a risk register with named owners, reviewed with you every week.'],
                        ['title' => 'Product and engineering team', 'description' => 'Designers, engineers and testers who build and release your portal, workflow or reports.'],
                        ['title' => 'Handover to your team', 'description' => 'Training, documentation and time working side by side, so your people can run the service without us.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about delivery',
                    'lead' => 'Answers on timing, price and working with your own team.',
                    'items' => [
                        ['question' => 'How soon will we see a live change?', 'answer' => 'On average, 6 weeks after kickoff. We release in small steps, so your teams use the first changes while we build the rest.'],
                        ['question' => 'How is the price set?', 'answer' => 'We agree the price for each programme once the scope is clear, usually after an Advisory Sprint. Every monthly report shows spending against the budget.'],
                        ['question' => 'Can our own developers work with you?', 'answer' => 'Yes, and we recommend it. Your developers join the team from the start, so they know the system well when we hand it over.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Talk to us about your programme',
                    'text' => 'Tell us which portal, workflow or report you want to change. We outline the team and the first release with you.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Managed Improvement', 'managed-improvement', [
                'abstract' => 'A monthly retainer that keeps your portals and workflows improving after launch, with a monthly roadmap, reliability reviews and a 24-hour response.',
                'eyebrow' => 'Monthly retainer',
                'headline' => 'Keep improving after launch',
                'intro' => 'Managed Improvement is a monthly retainer for portals, workflows and reports after launch. 94% of our clients continue with it after their programme.',
                'overview' => [
                    'header' => 'How the retainer works',
                    'lead' => 'Elena Vogt, our Client Operations Lead, runs every retainer and is your first contact for reviews and escalations.',
                    'text' => 'Each month we agree the next improvements with you, ship them and measure the result. Production issues get a response within 24 hours.',
                ],
                'priorities' => [
                    'header' => 'What the retainer includes',
                    'eyebrow' => 'Included',
                    'lead' => 'Three things you get every month.',
                    'items' => [
                        ['title' => 'Monthly roadmap', 'description' => 'We rank the next improvements with you, based on usage data and feedback from your teams.'],
                        ['title' => 'Reliability reviews', 'description' => 'We check uptime, errors and slow steps, and keep critical processes at 99.9% uptime.'],
                        ['title' => 'Operations dashboards', 'description' => 'One view of volumes, response times and open issues, shared with your managers.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about the retainer',
                    'lead' => 'Answers on response times, services built by others and price.',
                    'items' => [
                        ['question' => 'What happens when something breaks?', 'answer' => 'You report it through the agreed channel. We respond to production issues within 24 hours and keep you updated until the fix is live.'],
                        ['question' => 'Can you support a service others built?', 'answer' => 'Yes. We start with a review of the code, the documentation and the open issues. Then we agree with you what the retainer covers.'],
                        ['question' => 'How is the retainer priced?', 'answer' => 'A fixed monthly fee, based on the services in scope. Prices exclude VAT. Each monthly report shows the work done for the fee.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Talk to us about ongoing support',
                    'text' => 'Tell us which services you run and where they slow your teams down. We suggest a first scope during the call.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Sector Playbooks', 'sector-playbooks', [
                'abstract' => 'Lessons from 27 programmes in finance, health, manufacturing and public services, written up as one playbook for each sector.',
                'eyebrow' => 'By sector',
                'headline' => 'Start from what has worked in your sector',
                'intro' => 'We have delivered 27 programmes in finance, health, manufacturing and public services. Each playbook sums up the rules, risks and roles that shape change in one sector.',
                'overview' => [
                    'header' => 'How we use the playbooks',
                    'lead' => 'A playbook is the starting point for an Advisory Sprint, not a fixed recipe.',
                    'text' => 'It lists the rules that apply, the teams that must agree and the mistakes we have seen. We adapt it to your organisation.',
                ],
                'priorities' => [
                    'header' => 'What the playbooks cover',
                    'eyebrow' => 'Examples',
                    'lead' => 'Each pattern comes from a client programme.',
                    'items' => [
                        ['title' => 'Regulated onboarding', 'description' => 'For banks and insurers: identity checks, approvals and audit trails in one flow. First used with Nordline Finance.'],
                        ['title' => 'Service operations', 'description' => 'For health and public services: request handling that frontline teams can rely on. First used with Helio Health.'],
                        ['title' => 'Executive visibility', 'description' => 'For manufacturers: reports built from operational data instead of spreadsheets. First used with Mason Works.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about sector experience',
                    'lead' => 'Answers on fit, regulation and client data.',
                    'items' => [
                        ['question' => 'Have you worked in our sector?', 'answer' => 'Our 27 programmes cover finance, health, manufacturing and public services. If your sector is not on the list, we tell you in the first call whether a playbook still applies.'],
                        ['question' => 'Do playbooks replace our compliance checks?', 'answer' => 'No. They show where rules usually affect the work. Your compliance and legal teams still approve each change, and we plan time for their reviews.'],
                        ['question' => 'Do playbooks contain other clients\' data?', 'answer' => 'No. They contain patterns and lessons, never client data. We name a client only with their approval.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Talk to someone who knows your sector',
                    'text' => 'Tell us your sector and the process you want to change. We bring the matching playbook to the first call.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Case Studies', 'case-studies', [
                'abstract' => 'How Nordline Finance, Helio Health and Mason Works changed the way they operate, from 42% faster activation to 9 weekly reports retired.',
                'eyebrow' => 'Client results',
                'headline' => 'What changed for our clients',
                'intro' => 'Each case study shows the starting point, the decisions we made together and the result. The figures come from the clients\' own reports.',
                'overview' => [
                    'header' => 'How we write case studies',
                    'lead' => 'Every case study follows the same order: the problem, the constraints, the decisions and the measured result.',
                    'text' => 'Clients review each case study before we publish it. On request, we arrange a reference call with the client team.',
                ],
                'priorities' => [
                    'header' => 'Three client programmes',
                    'eyebrow' => 'Selected work',
                    'lead' => 'One programme each from finance, health and manufacturing.',
                    'items' => [
                        ['title' => 'Nordline Finance', 'description' => 'Combined five regional onboarding flows into one portal. New customers are activated 42% faster.'],
                        ['title' => 'Helio Health', 'description' => 'Modernised patient services without disrupting frontline teams. The service now handles 18k requests a month.'],
                        ['title' => 'Mason Works', 'description' => 'Built executive reporting that matches daily operations. The management team retired 9 weekly reports.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about our results',
                    'lead' => 'Answers on references, measurement and confidentiality.',
                    'items' => [
                        ['question' => 'Can we speak to one of these clients?', 'answer' => 'Yes. After a first call, we arrange a reference call with a client in your sector, with their consent.'],
                        ['question' => 'How do you measure results?', 'answer' => 'We agree the measures with you at kickoff, record a starting value and report against it every month. Case studies only use figures the client has approved.'],
                        ['question' => 'Will our programme become a case study?', 'answer' => 'Only if you agree. We ask at the end of the programme, and you approve every word and figure before we publish anything.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Ask us about a similar project',
                    'text' => 'Tell us which case study is closest to your situation. We explain what we would repeat and what we would change.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Governance', 'governance', [
                'abstract' => 'How we govern every programme: a weekly evidence review, a shared risk register, a decision log and escalation with a 24-hour response.',
                'eyebrow' => 'Operating rhythm',
                'headline' => 'Every decision has an owner and a record',
                'intro' => 'Every programme follows the same rhythm: a weekly evidence review, a monthly steering group and an open decision log. Your executives see progress without chasing it.',
                'overview' => [
                    'header' => 'How governance works in practice',
                    'lead' => 'It takes little of your time and shows problems early.',
                    'text' => 'The weekly review looks at evidence, not status reports. Risks and decisions go into shared records that your auditors can read.',
                ],
                'priorities' => [
                    'header' => 'What we use in every programme',
                    'eyebrow' => 'Tools',
                    'lead' => 'Your team can see all three from the first day.',
                    'items' => [
                        ['title' => 'Weekly evidence review', 'description' => 'A one-hour meeting where we show working results, test data and user feedback instead of slides.'],
                        ['title' => 'Risk register', 'description' => 'Every risk has an owner, a date and a next action. We review it every week.'],
                        ['title' => 'Decision log', 'description' => 'Who decided what, when and why. New team members and auditors can follow the history.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about governance',
                    'lead' => 'Answers on time, escalation and your own model.',
                    'items' => [
                        ['question' => 'How much of our time does this take?', 'answer' => 'One hour a week for the evidence review. The steering group meets once a month with your sponsor and your budget owner.'],
                        ['question' => 'What happens when something goes wrong?', 'answer' => 'The owner of the risk escalates it the same day. Production issues get a response within 24 hours, and the next steering group reviews the cause.'],
                        ['question' => 'Can we keep our own governance model?', 'answer' => 'Yes. We fit our records to your steering structure and report format. The decision log and risk register stay, because they keep the work visible.'],
                    ],
                ],
                'cta' => [
                    'header' => 'See how we would govern your programme',
                    'text' => 'Bring your current steering setup. We show where our rhythm fits and what it could replace.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Leadership', 'leadership', [
                'abstract' => 'Meet the four people who lead Northstar programmes: Mara Stein, Jonas Feld, Priya Nair and Elena Vogt, and what each of them is responsible for.',
                'eyebrow' => 'Senior team',
                'headline' => 'Senior people who stay on your programme',
                'intro' => 'Four leaders share responsibility for every programme, from the first call to the reviews after launch.',
                'overview' => [
                    'header' => 'How our leaders divide the work',
                    'lead' => 'Each leader owns one part of the programme, so you always know who to call.',
                    'text' => 'They meet every week to review all active programmes. Escalations reach them directly, not through account managers.',
                ],
                'priorities' => [
                    'header' => 'Who is responsible for what',
                    'eyebrow' => 'Roles',
                    'lead' => 'Four leaders, three areas of responsibility.',
                    'items' => [
                        ['title' => 'Advice: Mara Stein', 'description' => 'As Managing Partner for advisory, Mara leads every Advisory Sprint and presents the recommendation to your board.'],
                        ['title' => 'Delivery: Jonas Feld and Priya Nair', 'description' => 'Jonas, our Delivery Principal, owns plans and budgets. Priya, our Technical Director, owns architecture and technical quality.'],
                        ['title' => 'Operations: Elena Vogt', 'description' => 'As Client Operations Lead, Elena runs retainers, reviews and escalations once your service is live.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about our leadership',
                    'lead' => 'Answers on access, continuity and who runs your programme.',
                    'items' => [
                        ['question' => 'Will we work with these people directly?', 'answer' => 'Yes. At least one of them joins every steering group, and you have their direct contact details from the first day.'],
                        ['question' => 'What happens if a leader leaves?', 'answer' => 'Each programme has a named deputy who knows it well. We tell you about any change in writing and introduce the new lead in person.'],
                        ['question' => 'Can we meet the team before we sign?', 'answer' => 'Yes. You meet the leader and the delivery lead who would run your programme. We do not change them after signing without your agreement.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Meet the people who would lead your work',
                    'text' => 'Book a call with Mara Stein or Jonas Feld. We discuss your goals and who from our team fits them.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Procurement', 'procurement', [
                'abstract' => 'What procurement teams need to buy from Northstar: prices, contract documents, our security packet and client references, ready before the first call.',
                'eyebrow' => 'Buyer support',
                'headline' => 'Start your supplier review before we talk',
                'intro' => 'We send prices, draft contracts, our security packet and references on request. Your review can start while the business case is still being written.',
                'overview' => [
                    'header' => 'How buying from us works',
                    'lead' => 'Three steps: a scoping call, a written proposal and a contract on your terms or ours.',
                    'text' => 'Advisory Sprints use a short fixed-price agreement. Implementation and retainer work use a framework contract with a statement of work for each phase.',
                ],
                'priorities' => [
                    'header' => 'Documents you can request',
                    'eyebrow' => 'Documents',
                    'lead' => 'Ask for them at any point in your review.',
                    'items' => [
                        ['title' => 'Security packet', 'description' => 'How we handle data, access, subcontractors and incidents, with our policies and a draft data processing agreement.'],
                        ['title' => 'Reference process', 'description' => 'A call with a client in your sector, arranged after your first conversation with us.'],
                        ['title' => 'Contract documents', 'description' => 'Our standard terms, a draft framework contract and a named contact for legal questions.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions from procurement',
                    'lead' => 'Answers on price, terms and security reviews.',
                    'items' => [
                        ['question' => 'Are your prices fixed?', 'answer' => 'The Advisory Sprint is fixed at EUR 18k. Implementation Office work is priced per programme, and Managed Improvement is a monthly retainer. All prices exclude VAT.'],
                        ['question' => 'Can you work under our supplier terms?', 'answer' => 'Yes, in most cases. Send us your terms early. Our legal contact reviews them and lists any changes we need in one document.'],
                        ['question' => 'Do you fill in security questionnaires?', 'answer' => 'Yes. Our security packet already answers most questions. We complete your questionnaire as well and name a contact for follow-up questions.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Request the documents you need',
                    'text' => 'Tell us which documents your process needs. We send them with a named contact for questions.',
                    'label' => 'Contact us',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Resources', 'resources', [
                'abstract' => 'Free guides and templates for teams planning operational change: a readiness checklist, a governance meeting template and a service portal brief.',
                'eyebrow' => 'Downloads',
                'headline' => 'Tools we use in our own programmes',
                'intro' => 'These guides and templates come from our client work. Use them to prepare a programme, with or without us.',
                'overview' => [
                    'header' => 'How to use these resources',
                    'lead' => 'Start with the readiness checklist. It lists the questions to answer before you scope a programme.',
                    'text' => 'The governance template and the portal brief help once the scope is clear. All three are short, and you can edit them.',
                ],
                'priorities' => [
                    'header' => 'Three documents to start with',
                    'eyebrow' => 'Start here',
                    'lead' => 'Each one answers a question our clients ask early in a programme.',
                    'items' => [
                        ['title' => 'Readiness checklist', 'description' => 'Questions on goals, owners, budget and data to answer before you scope a programme.'],
                        ['title' => 'Governance meeting template', 'description' => 'A short agenda for weekly evidence, risk and decision reviews.'],
                        ['title' => 'Service portal brief', 'description' => 'A one-page outline for replacing fragile internal workflows with one service portal.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about the resources',
                    'lead' => 'Answers on access, reuse and support.',
                    'items' => [
                        ['question' => 'Do we need to register to download?', 'answer' => 'No. The documents are free, and we do not ask for your email address.'],
                        ['question' => 'Can we adapt the templates for our team?', 'answer' => 'Yes. Change them as you need. If you publish an adapted version, please name Northstar Advisory Group as the source.'],
                        ['question' => 'Can you help us use them?', 'answer' => 'Yes. Clients often fill in the readiness checklist first, then book an Advisory Sprint to work through the answers with us.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Work through the checklist with us',
                    'text' => 'Send us your answers to the readiness checklist. We use them to prepare a focused first call.',
                    'label' => 'Contact us',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Careers', 'careers', [
                'abstract' => 'Senior roles at Northstar Advisory Group for consultants, product engineers and delivery managers who want planned, focused work with clear ownership.',
                'eyebrow' => 'Open roles',
                'headline' => 'Senior work at a steady pace',
                'intro' => 'We hire experienced people and give them time to do the work well. Every role has clear ownership and a named lead.',
                'overview' => [
                    'header' => 'What working here is like',
                    'lead' => 'Small teams, clear ownership and the same governance rhythm we offer our clients.',
                    'text' => 'Consultants work on one programme at a time. We plan capacity every month, so overtime stays the exception.',
                ],
                'priorities' => [
                    'header' => 'Roles we are hiring for',
                    'eyebrow' => 'Hiring now',
                    'lead' => 'Open roles in advisory, delivery and engineering.',
                    'items' => [
                        ['title' => 'Senior consultant', 'description' => 'Runs interviews and analysis in Advisory Sprints and presents findings to client executives.'],
                        ['title' => 'Product engineer', 'description' => 'Builds and releases portals, workflows and reports as part of the Implementation Office.'],
                        ['title' => 'Delivery manager', 'description' => 'Owns the plan, budget and risk register of one programme and reports to Jonas Feld.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions from candidates',
                    'lead' => 'Answers on hiring, pay and travel.',
                    'items' => [
                        ['question' => 'How does the hiring process work?', 'answer' => 'Three conversations: an introduction call, a case discussion based on a past programme and a meeting with the team you would join. We give feedback after each step.'],
                        ['question' => 'Do you publish salary ranges?', 'answer' => 'Yes. Every job advert states the salary range for the role.'],
                        ['question' => 'How much travel does the job involve?', 'answer' => 'Most work is remote. You travel for kickoffs, steering groups and workshops with clients.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Ask us about a role',
                    'text' => 'Send your CV and a short note on the work you want to do. We reply to every application.',
                    'label' => 'Contact us',
                    'badge' => 'Next step',
                ],
            ]),
            self::corporatePage('Contact', 'contact', [
                'abstract' => 'Contact Northstar Advisory Group about a new programme, a partnership, support for a live service or a procurement question.',
                'eyebrow' => 'Talk to us',
                'headline' => 'Choose the right contact for your question',
                'intro' => 'New projects, partnerships, support and procurement each have their own contact. Your message reaches someone who can answer it.',
                'overview' => [
                    'header' => 'What happens after you contact us',
                    'lead' => 'For new projects, we suggest a 45-minute call with a senior member of our team.',
                    'text' => 'Bring the problem, your constraints and the names of the people who decide. You leave with a first outline of the next step.',
                ],
                'priorities' => [
                    'header' => 'Where to send your message',
                    'eyebrow' => 'Contact routes',
                    'lead' => 'Procurement teams find documents and contacts on our procurement page.',
                    'items' => [
                        ['title' => 'New projects', 'description' => 'Book a 45-minute call about an Advisory Sprint, a programme or a retainer.'],
                        ['title' => 'Partnerships', 'description' => 'For technology partners and firms that want to deliver programmes with us.'],
                        ['title' => 'Support for live services', 'description' => 'Clients with a retainer use their agreed channel. Production issues get a response within 24 hours.'],
                    ],
                ],
                'faq' => [
                    'header' => 'Questions about contacting us',
                    'lead' => 'Answers on reply times, cost and confidentiality.',
                    'items' => [
                        ['question' => 'How quickly will you reply?', 'answer' => 'We reply to new enquiries within one working day. Clients with a retainer get a response to production issues within 24 hours.'],
                        ['question' => 'Is the first call free?', 'answer' => 'Yes. The 45-minute call is free and does not commit you to anything.'],
                        ['question' => 'Can we sign an NDA before we talk?', 'answer' => 'Yes. Send us your NDA and we sign it before the first call. You can also use ours.'],
                    ],
                ],
                'cta' => [
                    'header' => 'Book a 45-minute call',
                    'text' => 'Tell us what you want to change. A senior member of our team replies within one working day.',
                    'label' => 'Book a call',
                    'badge' => 'Next step',
                ],
            ]),
        ];
    }

    /**
     * @param CorporatePageCopy $copy
     * @return StarterPage
     */
    private static function corporatePage(string $title, string $slug, array $copy): array
    {
        $priorities = $copy['priorities']['items'];

        return self::page($title, $slug, $copy['abstract'], [
            self::headerSection($copy['headline'], $copy['eyebrow'], $copy['intro'], 'left'),
            self::textMedia($copy['overview']['header'], $copy['overview']['lead'], $copy['overview']['text'], 'media-right'),
            self::featureList($copy['priorities']['header'], $copy['priorities']['eyebrow'], $copy['priorities']['lead'], array_map(
                static fn(array $item, int $index): array => ['icon' => ['shield-check', 'settings', 'users'][$index % 3], 'title' => $item['title'], 'description' => $item['description']],
                $priorities,
                array_keys($priorities)
            )),
            self::faq($copy['faq']['header'], $copy['faq']['lead'], $copy['faq']['items']),
            self::ctaCard($copy['cta']['header'], $copy['cta']['text'], $copy['cta']['label'], '#contact', $copy['cta']['badge']),
        ]);
    }

    /**
     * @param array<int, StarterBlock> $content
     * @return StarterPage
     */
    private static function page(string $title, string $slug, string $abstract, array $content, string $layout = 'DesiderioContentpage', bool $navHidden = false): array
    {
        return [
            'title' => $title,
            'navTitle' => $title,
            'slug' => $slug,
            'layout' => $layout,
            'abstract' => $abstract,
            'navHidden' => $navHidden,
            'content' => $content,
        ];
    }

    /**
     * @param list<StarterPage> $pages
     * @param list<string> $visibleSlugs
     * @return list<StarterPage>
     */
    private static function withTopNavigation(array $pages, array $visibleSlugs): array
    {
        $visibleSlugMap = array_fill_keys($visibleSlugs, true);

        return array_map(
            static function (array $page) use ($visibleSlugMap): array {
                $page['navHidden'] = !isset($visibleSlugMap[$page['slug']]);

                return $page;
            },
            $pages
        );
    }

    /**
     * @return list<StarterPage>
     */
    private static function supportPages(string $brand, string $contactSlug): array
    {
        return [
            self::page('Search', 'search', 'Search the ' . $brand . ' site for services, guides, case studies and details on how we deliver our work.', [
                self::searchHeader('', '', '', '/search', 'Search services, results and guides'),
            ], 'DesiderioSearch', true),
            self::page('404', '404', 'This page is not available. Go back to the homepage, search the site or contact us if you expected a page here.', [
                self::headerSection('This page is not available', '404', 'The link may be out of date, or the page has moved.', 'center'),
                self::ctaCard('Find what you were looking for', 'Go back to the homepage or search the site. If a page should exist here, contact us.', 'Go home', '/', 'Page not found'),
            ], 'DesiderioError', true),
            self::page('Imprint', 'imprint', 'Company details and publishing responsibility, for legal and procurement review. ' . $brand . ' is a fictional company.', [
                self::headerSection($brand . ' imprint', 'Legal', 'The company, the person responsible for the content and lasting contact details, for legal review. The company is fictional, so this page has no real data.', 'left'),
                self::textMedia('Company information', 'A real company adds verified legal data here before launch.', 'This means the registered name, business address, responsible editor and a contact route. Keep it short, so procurement teams can check it fast.', 'media-above'),
            ], 'DesiderioContentpage', true),
            self::page('Privacy', 'privacy', 'What visitors and buyers can expect on privacy, how to make a request and how data is handled. ' . $brand . ' is a fictional company.', [
                self::headerSection('Privacy at ' . $brand, 'Trust', 'The privacy policy, request handling and data processing, in plain language. ' . $brand . ' is a fictional company, so these answers describe what a real policy covers.', 'left'),
                self::faq('Privacy questions', 'Align these answers with reviewed legal text before publishing.', [
                    ['question' => 'What does the privacy policy list?', 'answer' => 'It lists the types of data, why they are processed and how long they are kept. It also names subprocessors, contact routes and the rights users have.'],
                    ['question' => 'Who owns the policy?', 'answer' => 'A named person in legal or operations. The page also gives a privacy contact address that stays valid over time.'],
                    ['question' => 'Where do privacy requests go?', 'answer' => 'To an inbox or form that someone checks. Every form links there, and the page says when to expect a reply.'],
                ]),
            ], 'DesiderioContentpage', true),
            self::page('Accessibility', 'accessibility', 'Accessibility commitments, known limitations and how visitors can report a barrier. ' . $brand . ' is a fictional company.', [
                self::headerSection('Accessibility statement', 'Service quality', 'This statement covers the standard, current status, known limitations and feedback, in plain language. ' . $brand . ' is a fictional company, so it shows the structure only.', 'left'),
                self::featureList('Points to review before launch', 'Checklist', 'Use this list as a practical audit.', [
                    ['icon' => 'keyboard', 'title' => 'Keyboard paths', 'description' => 'Check navigation, forms, menus, tabs and modal dialogs without a mouse.'],
                    ['icon' => 'contrast', 'title' => 'Readable contrast', 'description' => 'Check text, buttons, chart labels and focus indicators in every theme preset.'],
                    ['icon' => 'message-square', 'title' => 'Feedback route', 'description' => 'Give visitors a way to report barriers and ask for fixes. Make sure someone reads what they send.'],
                ]),
                self::ctaCard('Report an accessibility issue', 'Tell us the page, your device and browser, any assistive technology you use and the barrier you found.', 'Contact us', '#' . $contactSlug, 'Feedback'),
            ], 'DesiderioContentpage', true),
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
                static fn(array $tab, int $index): array => [
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
     * @param list<array{name: string, role: string, image?: array{file: string, title: string, alternative: string, description: string, source: string}}> $members
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
     * @return array{name: string, role: string, image: array{file: string, title: string, alternative: string, description: string, source: string}}
     */
    private static function teamMember(string $name, string $role, int $portraitIndex): array
    {
        return [
            'name' => $name,
            'role' => $role,
            'image' => StyleguidePortraitAssets::fileReferenceForMember($name, $portraitIndex),
        ];
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
                static fn(array $plan): array => [
                    ...$plan,
                    'features' => array_map(static fn(string $feature): array => ['text' => $feature], $plan['features']),
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
                static fn(array $item): array => ['question' => $item['question'], 'answer' => '<p>' . $item['answer'] . '</p>'],
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

}
