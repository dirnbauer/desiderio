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
 * Page composition follows one rule: plain sections and panels (stats,
 * steps, case studies, testimonials) alternate, every grid holds a number of
 * cards it can show in whole rows, and every page ends with a next step.
 *
 * Links inside the content name their target page as "#<slug>" of a starter
 * page, so every link points at a page this definition creates.
 *
 * Contact data is made up so that it reaches no one (facts.desiderio.demo_contact
 * in the content canon): a street name that exists nowhere, a Bundesnetzagentur
 * drama number and addresses on the reserved example.com domain.
 *
 * @phpstan-type StarterBlock array{ctype: string, colPos: int, fields: array<string, mixed>}
 * @phpstan-type StarterHome array{layout: string, content: array<int, StarterBlock>}
 * @phpstan-type StarterPage array{title: string, navTitle: string, slug: string, layout: string, abstract: string, navHidden: bool, content: array<int, StarterBlock>}
 * @phpstan-type StarterSite array{label: string, slug: string, rootSlug: string, rootTitle: string, rootNavTitle: string, purpose: string, abstract: string, home: StarterHome, subpages: array<int, StarterPage>}
 * @phpstan-type StarterImage array{file: string, title: string, alternative: string, description: string, source: string}
 * @phpstan-type StarterQuestion array{question: string, answer: string}
 */
final class StarterSiteDefinitions
{
    private const string BRAND = 'Northstar Advisory Group';
    private const string EMAIL = 'northstar@example.com';
    private const string PHONE = '+49 30 23125 400';
    private const string PHONE_LINK = 'tel:+493023125400';
    private const string STREET = 'Beispielstraße 8';
    private const string CITY = '10117 Berlin';

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
        $subpages = array_merge($subpages, self::supportPages());

        return [
            'label' => 'Corporate starter',
            'slug' => 'corporate',
            'rootSlug' => '/desiderio-corporate-starter',
            'rootTitle' => self::BRAND,
            'rootNavTitle' => 'Home',
            'purpose' => 'Help enterprise buyers understand the offer, trust the delivery model, and start a qualified procurement-safe conversation.',
            'abstract' => 'Northstar Advisory Group helps companies change how they operate, with clear governance, proof of results and support for procurement.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => self::corporateHome(),
            ],
            'subpages' => $subpages,
        ];
    }

    /**
     * The homepage: promise and figures first, then who it is for, the
     * offer, proof, the people and one clear next step. Plain sections and
     * panels (figures, case studies, client voices) take turns.
     *
     * @return list<StarterBlock>
     */
    private static function corporateHome(): array
    {
        return [
            self::heroStats('Operational change with a clear plan', self::BRAND, 'We help executive teams replace fragile internal workflows. One senior team plans the change, delivers it and keeps improving it after launch.', 'Book a call', '#contact', [
                ['value' => '42%', 'label' => 'Less manual handover', 'stat_description' => 'After regional spreadsheet workflows moved into one portal.'],
                ['value' => '11', 'label' => 'Departments in one workflow', 'stat_description' => 'Legal, finance, operations, support and regional teams at one client.'],
                ['value' => '24h', 'label' => 'Escalation response', 'stat_description' => 'Contracted for production issues in every service we run.'],
                ['value' => '99.9%', 'label' => 'Uptime of critical processes', 'stat_description' => 'Across client portals and reporting workflows.'],
            ]),
            self::headerSection('Advisers who stay until the change works', 'Who we are', 'Northstar Advisory Group plans, delivers and runs operational change. The senior people you meet in the first call lead the work until it runs without us.'),
            self::kpiCards('Results across our programmes', 'Track record', 'Counted across every client programme since 2019.', [
                ['value' => '27', 'label' => 'Programmes delivered', 'detail_text' => 'In finance, health, manufacturing and public services.', 'trend' => 'positive'],
                ['value' => '6 weeks', 'label' => 'To the first release', 'detail_text' => 'Average time from kickoff to the first live change.', 'trend' => 'positive'],
                ['value' => '94%', 'label' => 'Clients who stay', 'detail_text' => 'They continue with Managed Improvement after their programme.', 'trend' => 'positive'],
                ['value' => '4.7/5', 'label' => 'Stakeholder rating', 'detail_text' => 'From surveys at the end of each programme.', 'trend' => 'positive'],
            ]),
            self::featureCards('What each stakeholder gets', 'Who we help', 'Change needs executives, delivery teams and procurement to agree. Each group gets what it needs to decide.', [
                ['title' => 'For executives', 'description' => 'A plan your board can approve, with cost, effort and expected results for each option.'],
                ['title' => 'For delivery teams', 'description' => 'Senior people who work beside your team, release in small steps and hand everything over.'],
                ['title' => 'For procurement', 'description' => 'Fixed prices, draft contracts, a security packet and references, ready before the first call.'],
            ]),
            self::pricingThreeTier('Three ways to work with us', 'Engagement models', 'Compare the options before we talk. All prices exclude VAT.', [
                ['name' => 'Advisory Sprint', 'price' => 'EUR 18k', 'billing_period' => 'fixed scope', 'description' => 'For teams that need a diagnosis, agreed priorities and a plan for the board.', 'features' => ['Review of the current state', 'Map of risks and dependencies', 'Recommendation for the board'], 'is_recommended' => false, 'button_text' => 'Discuss a sprint', 'button_link' => '#contact'],
                ['name' => 'Implementation Office', 'price' => 'Custom', 'billing_period' => 'per programme', 'description' => 'For portal, workflow and reporting programmes that need one senior owner.', 'features' => ['Delivery governance', 'Product and engineering team', 'Handover to your team'], 'is_recommended' => true, 'button_text' => 'Scope a programme', 'button_link' => '#contact'],
                ['name' => 'Managed Improvement', 'price' => 'Fixed fee', 'billing_period' => 'per month', 'description' => 'For organisations that want steady improvement after launch.', 'features' => ['Monthly roadmap', 'Reliability reviews', 'Operations dashboards'], 'is_recommended' => false, 'button_text' => 'Plan a retainer', 'button_link' => '#contact'],
            ]),
            self::caseStudyGrid('Selected results', 'Case studies', [
                ['client_name' => 'Nordline Finance', 'summary' => 'Combined five regional onboarding flows into one portal.', 'result' => '42% faster activation', 'image' => self::caseStudyImage('nordline'), 'link' => '#case-studies'],
                ['client_name' => 'Helio Health', 'summary' => 'Modernised patient services without disrupting frontline teams.', 'result' => '18k requests a month', 'image' => self::caseStudyImage('helio'), 'link' => '#case-studies'],
                ['client_name' => 'Mason Works', 'summary' => 'Built executive reporting from the same data as daily operations.', 'result' => '9 weekly reports retired', 'image' => self::caseStudyImage('mason'), 'link' => '#case-studies'],
            ]),
            self::companyValues('How we work', 'Our principles', 'Three promises we keep in every programme.', [
                ['title' => 'Make work visible', 'description' => 'Every programme keeps a decision log, a named owner for each task and a record of evidence.', 'icon' => 'list-checks'],
                ['title' => 'Remove friction', 'description' => 'We cut the handovers and reporting loops that slow frontline teams down.', 'icon' => 'zap'],
                ['title' => 'Stay accountable after launch', 'description' => 'Improvement continues through regular reviews, not only until handover.', 'icon' => 'shield-check'],
            ]),
            self::teamGridMinimal('Our leadership', 'The senior people who lead your programme.', [
                self::teamMember('Mara Stein', 'Managing Partner, Advisory'),
                self::teamMember('Jonas Feld', 'Delivery Principal'),
                self::teamMember('Priya Nair', 'Technical Director'),
                self::teamMember('Elena Vogt', 'Client Operations Lead'),
            ]),
            self::testimonialGrid('Why clients stay', 'Client voices', [
                ['quote' => 'They turned a fragile internal process into a service our leadership trusts.', 'author_name' => 'Amelia Grant', 'author_title' => 'COO, Nordline Finance'],
                ['quote' => 'We got a clear plan, a calm rollout and fewer surprises than in any earlier project.', 'author_name' => 'Markus Renner', 'author_title' => 'VP Operations, Helio Health'],
                ['quote' => 'Their meeting rhythm made complex work easy to follow for non-technical colleagues.', 'author_name' => 'Priya Shah', 'author_title' => 'Transformation Lead, Mason Works'],
            ]),
            self::resourceLibrary('Guides and templates', 'Free resources', [
                ['title' => 'Readiness checklist', 'type_label' => 'Checklist', 'description' => 'Questions to answer before you scope a programme.', 'link' => '#resources'],
                ['title' => 'Governance meeting template', 'type_label' => 'Template', 'description' => 'A short agenda for weekly evidence, risk and decision reviews.', 'link' => '#resources'],
                ['title' => 'Service portal brief', 'type_label' => 'Brief', 'description' => 'A one-page outline for replacing fragile internal workflows.', 'link' => '#resources'],
            ]),
            self::ctaCard('Ready to plan the first step?', 'Book a 45-minute call. You leave with a first outline of the work and the decisions ahead.', 'Book a call', '#contact', 'Next step'),
            self::sitemapGrid('Explore Northstar', [
                ...self::sitemapGroups(),
                [
                    'title' => 'Legal',
                    'pages' => [
                        ['label' => 'Imprint', 'link' => '#imprint'],
                        ['label' => 'Privacy', 'link' => '#privacy'],
                        ['label' => 'Accessibility', 'link' => '#accessibility'],
                    ],
                ],
            ]),
        ];
    }

    /**
     * The eleven content pages below the homepage. Each page opens with its
     * promise next to a photo and a first action, alternates plain sections
     * with one panel (figures or steps), answers the usual questions and
     * ends with a next step. Service pages share one order so buyers can
     * compare them; the other pages use the elements their content needs.
     *
     * @return list<StarterPage>
     */
    private static function corporateSubpages(): array
    {
        return [
            self::page('Advisory Services', 'advisory-services', 'The Advisory Sprint for EUR 18k: a review of how your operations work today, a map of risks and a recommendation for your board.', [
                self::textMedia(
                    'Know what to change before you commit budget',
                    'The Advisory Sprint is a fixed-scope review for EUR 18k. You get a clear diagnosis, agreed priorities and a plan your board can approve.',
                    'Mara Stein, our Managing Partner for advisory, leads every sprint with a small senior team. The sprint ends with a session for your board.',
                    'media-right',
                    self::generatedPhoto('Heroes/hero-solutions-8e3c28e0.webp', 'An interview at a meeting table', 'Three people at a meeting table with laptops. A man in a wheelchair presents while two colleagues listen.'),
                    'Book a call',
                    '#contact'
                ),
                self::statsInline('The sprint at a glance', [
                    ['value' => 'EUR 18k', 'label' => 'Fixed price'],
                    ['value' => '4 weeks', 'label' => 'To the board session'],
                    ['value' => '3', 'label' => 'Results for your board'],
                ]),
                self::featureGrid3('What you get from the sprint', 'Three results you can take to your board.', [
                    ['icon' => 'search', 'title' => 'Review of the current state', 'description' => 'How work flows today, where it stalls and what that costs, based on interviews and your own data.'],
                    ['icon' => 'git-branch', 'title' => 'Map of risks and dependencies', 'description' => 'Which systems, teams and contracts each change depends on, and what could delay it.'],
                    ['icon' => 'file-text', 'title' => 'Recommendation for the board', 'description' => 'The options with cost, effort and expected results, and the one we recommend.'],
                ]),
                self::featureTimeline('How the four weeks run', 'We share our findings every week, so the board session holds no surprises.', [
                    ['step' => 'Week 1', 'title' => 'Interviews', 'description' => 'We talk to your leaders and frontline teams and collect the data we need.'],
                    ['step' => 'Week 2', 'title' => 'Workflow and data review', 'description' => 'We trace how work moves between teams and systems, and where it stalls.'],
                    ['step' => 'Week 3', 'title' => 'Options and costs', 'description' => 'We turn the findings into options with cost, effort and expected results.'],
                    ['step' => 'Week 4', 'title' => 'Board session', 'description' => 'We present our recommendation to your board and hand over every document.'],
                ]),
                self::quote('The sprint gave our board three options with the costs attached. We approved one in the same meeting.', 'Priya Shah', 'Transformation Lead, Mason Works'),
                self::faq('Questions about the Advisory Sprint', 'Answers on price, timing and what happens next.', [
                    ['question' => 'What does the Advisory Sprint cost?', 'answer' => 'EUR 18k, excluding VAT. The scope is fixed, so the price does not change. It includes the interviews, the analysis, all documents and the board session.'],
                    ['question' => 'How long does the sprint take?', 'answer' => 'Four weeks from kickoff to the board session. Your team takes part in the interviews and in one short review each week.'],
                    ['question' => 'Do we have to hire you afterwards?', 'answer' => 'No. The recommendation is yours, and any team can deliver it. If you continue with us, the Implementation Office starts from the sprint results, so no work is repeated.'],
                ]),
                self::ctaCard('Start with a 45-minute call', 'Tell us what you want to change. Together we check whether a sprint is the right first step.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Implementation Office', 'implementation-office', 'Portal, workflow and reporting programmes with one senior owner, clear governance and the first live change after 6 weeks on average.', [
                self::textMedia(
                    'One senior owner from plan to live service',
                    'We deliver portal, workflow and reporting programmes with our own product and engineering team. On average, the first change goes live 6 weeks after kickoff.',
                    'Jonas Feld, our Delivery Principal, oversees every programme. A named delivery lead runs it day to day and reports to you every week.',
                    'media-right',
                    self::generatedPhoto('Heroes/hero-home-e44c53bc.webp', 'A delivery team reviews a design', 'Four colleagues of different ages and backgrounds gather around a large monitor and review a design together.'),
                    'Book a call',
                    '#contact'
                ),
                self::statsInline('The office at a glance', [
                    ['value' => '6 weeks', 'label' => 'To the first release'],
                    ['value' => '1', 'label' => 'Senior owner'],
                    ['value' => 'Weekly', 'label' => 'Progress report'],
                ]),
                self::featureGrid3('What the office runs for you', 'Three areas we own for the whole programme.', [
                    ['icon' => 'list-checks', 'title' => 'Delivery governance', 'description' => 'A plan, a budget and a risk register with named owners, reviewed with you every week.'],
                    ['icon' => 'users', 'title' => 'Product and engineering team', 'description' => 'Designers, engineers and testers who build and release your portal, workflow or reports.'],
                    ['icon' => 'handshake', 'title' => 'Handover to your team', 'description' => 'Training, documentation and time side by side, so your people can run the service without us.'],
                ]),
                self::featureNumbered('How a programme runs', 'Every programme has the same three phases. Your scope sets the dates.', [
                    ['title' => 'Plan and set up', 'description' => 'In the first week we agree the plan, budget and risk register with your sponsor.'],
                    ['title' => 'Build and release', 'description' => 'We build in short cycles and show working results in every weekly review.'],
                    ['title' => 'Hand over', 'description' => 'We stay until your team runs the service on its own. Managed Improvement can follow if you want it.'],
                ]),
                self::quote('Our developers were part of the team from day one. When Northstar left, nobody had to learn the system from scratch.', 'Amelia Grant', 'COO, Nordline Finance'),
                self::faq('Questions about delivery', 'Answers on timing, price and working with your own team.', [
                    ['question' => 'How soon will we see a live change?', 'answer' => 'On average, 6 weeks after kickoff. We release in small steps, so your teams use the first changes while we build the rest.'],
                    ['question' => 'How is the price set?', 'answer' => 'We agree the price for each programme once the scope is clear, usually after an Advisory Sprint. Every monthly report shows spending against the budget.'],
                    ['question' => 'Can our own developers work with you?', 'answer' => 'Yes, and we recommend it. Your developers join the team from the start, so they know the system well when we hand it over.'],
                ]),
                self::ctaCard('Talk to us about your programme', 'Tell us which portal, workflow or report you want to change. We outline the team and the first release with you.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Managed Improvement', 'managed-improvement', 'A monthly retainer that keeps your portals and workflows improving after launch, with a monthly roadmap, reliability reviews and a 24-hour response.', [
                self::textMedia(
                    'Keep improving after launch',
                    'Managed Improvement is a monthly retainer for portals, workflows and reports after launch. 94% of our clients continue with it after their programme.',
                    'Elena Vogt, our Client Operations Lead, runs every retainer. She is your first contact for reviews and escalations.',
                    'media-right',
                    self::generatedPhoto('Heroes/hero-features-a2db80eb.webp', 'Improving a live service together', 'A woman works on a web page on her laptop while a colleague next to her points at the screen.'),
                    'Book a call',
                    '#contact'
                ),
                self::statsInline('The retainer at a glance', [
                    ['value' => '24h', 'label' => 'Response to incidents'],
                    ['value' => '99.9%', 'label' => 'Uptime target'],
                    ['value' => 'Monthly', 'label' => 'Roadmap and report'],
                ]),
                self::featureGrid3('What the retainer includes', 'Three things you get every month.', [
                    ['icon' => 'calendar', 'title' => 'Monthly roadmap', 'description' => 'We rank the next improvements with you, based on usage data and feedback from your teams.'],
                    ['icon' => 'shield-check', 'title' => 'Reliability reviews', 'description' => 'We check uptime, errors and slow steps, and keep critical processes at 99.9% uptime.'],
                    ['icon' => 'chart', 'title' => 'Operations dashboards', 'description' => 'One view of volumes, response times and open issues, shared with your managers.'],
                ]),
                self::featureNumbered('How each month runs', 'The same cycle every month, with one report at the end.', [
                    ['title' => 'Agree the priorities', 'description' => 'We review usage data and feedback with you and choose the next improvements.'],
                    ['title' => 'Ship the improvements', 'description' => 'We build, test and release them in small steps, at times that suit your teams.'],
                    ['title' => 'Report the results', 'description' => 'The monthly report shows what changed, what it achieved and the work done for the fee.'],
                ]),
                self::quote('Every month we see what changed and what it achieved. When something breaks, we know who answers.', 'Markus Renner', 'VP Operations, Helio Health'),
                self::faq('Questions about the retainer', 'Answers on response times, services built by others and price.', [
                    ['question' => 'What happens when something breaks?', 'answer' => 'You report it through the agreed channel. We respond to production issues within 24 hours and keep you updated until the fix is live.'],
                    ['question' => 'Can you support a service others built?', 'answer' => 'Yes. We start with a review of the code, the documentation and the open issues. Then we agree with you what the retainer covers.'],
                    ['question' => 'How is the retainer priced?', 'answer' => 'A fixed monthly fee, based on the services in scope. Prices exclude VAT. Each monthly report shows the work done for the fee.'],
                ]),
                self::ctaCard('Talk to us about ongoing support', 'Tell us which services you run and where they slow your teams down. We suggest a first scope during the call.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Sector Playbooks', 'sector-playbooks', 'Lessons from 27 programmes in finance, health, manufacturing and public services, written up as one playbook for each sector.', [
                self::textMedia(
                    'Start from what has worked in your sector',
                    'We have delivered 27 programmes in finance, health, manufacturing and public services. Each playbook sums up the rules, risks and roles that shape change in one sector.',
                    'A playbook is the starting point for an Advisory Sprint, not a fixed recipe. We adapt it to your organisation.',
                    'media-right',
                    self::generatedPhoto('Heroes/hero-resources-a6eff766.webp', 'Reading a sector playbook', 'A woman with silver hair and reading glasses reads on a tablet in a quiet room with bookshelves.'),
                    'Book a call',
                    '#contact'
                ),
                self::featureGrid3('What the playbooks cover', 'Each pattern comes from a client programme.', [
                    ['icon' => 'scale', 'title' => 'Regulated onboarding', 'description' => 'For banks and insurers: identity checks, approvals and audit trails in one flow. First used with Nordline Finance.'],
                    ['icon' => 'messages', 'title' => 'Service operations', 'description' => 'For health and public services: request handling that frontline teams can rely on. First used with Helio Health.'],
                    ['icon' => 'chart', 'title' => 'Executive visibility', 'description' => 'For manufacturers: reports built from operational data instead of spreadsheets. First used with Mason Works.'],
                ]),
                self::featureNumbered('How we use a playbook', 'The playbook shapes the sprint. Your own experts still decide.', [
                    ['title' => 'Match the playbook', 'description' => 'In the first call we choose the playbook closest to your sector and process.'],
                    ['title' => 'Adapt it in the sprint', 'description' => 'During the Advisory Sprint we fit its rules, roles and risks to your organisation.'],
                    ['title' => 'Check it with your experts', 'description' => 'Your compliance and legal teams review each change, and we plan time for their reviews.'],
                ]),
                self::faq('Questions about sector experience', 'Answers on fit, regulation and client data.', [
                    ['question' => 'Have you worked in our sector?', 'answer' => 'Our 27 programmes cover finance, health, manufacturing and public services. If your sector is not on the list, we tell you in the first call whether a playbook still applies.'],
                    ['question' => 'Do playbooks replace our compliance checks?', 'answer' => 'No. They show where rules usually affect the work. Your compliance and legal teams still approve each change, and we plan time for their reviews.'],
                    ['question' => 'Do playbooks contain other clients’ data?', 'answer' => 'No. They contain patterns and lessons, never client data. We name a client only with their approval.'],
                ]),
                self::ctaCard('Talk to someone who knows your sector', 'Tell us your sector and the process you want to change. We bring the matching playbook to the first call.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Case Studies', 'case-studies', 'How Nordline Finance, Helio Health and Mason Works changed the way they operate, from 42% faster activation to 9 weekly reports retired.', [
                self::textMedia(
                    'What changed for our clients',
                    'Each case study shows the starting point, the decisions we made together and the result. The figures come from the clients’ own reports.',
                    'Clients review each case study before we publish it. On request, we arrange a reference call with the client team.',
                    'media-right',
                    self::generatedPhoto('Strategy/strategy-why-now-e77b0626.webp', 'Case study drafts on a desk', 'Printed page layouts, a pencil and a closed laptop on a wooden desk.', 'AI-generated image.'),
                    'Request a reference',
                    '#contact'
                ),
                self::caseStudyGrid('Three client programmes', 'Selected work', [
                    ['client_name' => 'Nordline Finance', 'summary' => 'Five regional onboarding flows became one portal, with identity checks and approvals in one place.', 'result' => '42% faster activation', 'image' => self::caseStudyImage('nordline')],
                    ['client_name' => 'Helio Health', 'summary' => 'Patient services were modernised step by step, without disrupting frontline teams.', 'result' => '18k requests a month', 'image' => self::caseStudyImage('helio')],
                    ['client_name' => 'Mason Works', 'summary' => 'Executive reports now come from the same data as daily operations, not from spreadsheets.', 'result' => '9 weekly reports retired', 'image' => self::caseStudyImage('mason')],
                ]),
                self::featureGrid3('How we measure results', 'Every figure in a case study is checked twice: by us and by the client.', [
                    ['icon' => 'handshake', 'title' => 'Agreed at kickoff', 'description' => 'We agree the measures with you before work starts and record a starting value.'],
                    ['icon' => 'calendar', 'title' => 'Reported every month', 'description' => 'Each monthly report compares the current value with the starting value.'],
                    ['icon' => 'badge-check', 'title' => 'Approved by the client', 'description' => 'Case studies use only figures the client has checked and approved.'],
                ]),
                self::faq('Questions about our results', 'Answers on references, confidentiality and your own programme.', [
                    ['question' => 'Can we speak to one of these clients?', 'answer' => 'Yes. After a first call, we arrange a reference call with a client in your sector, with their consent.'],
                    ['question' => 'Can you share results under an NDA?', 'answer' => 'Yes. For confidential programmes we share anonymised results under an NDA, once the client has agreed.'],
                    ['question' => 'Will our programme become a case study?', 'answer' => 'Only if you agree. We ask at the end of the programme, and you approve every word and figure before we publish anything.'],
                ]),
                self::ctaCard('Ask us about a similar project', 'Tell us which case study is closest to your situation. We explain what we would repeat and what we would change.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Governance', 'governance', 'How we govern every programme: a weekly evidence review, a shared risk register, a decision log and escalation with a 24-hour response.', [
                self::textMedia(
                    'Every decision has an owner and a record',
                    'Every programme follows the same rhythm: a weekly evidence review, a monthly steering group and an open decision log. Your executives see progress without chasing it.',
                    'The weekly review looks at evidence, not status reports. Risks and decisions go into shared records that your auditors can read.',
                    'media-right',
                    self::generatedPhoto('Strategy/strategy-layer-review-8b07a8c0.webp', 'Work under review', 'Hands tick printed pages next to a wooden stamp, as in a review before anything goes live.', 'AI-generated image.'),
                    'Book a call',
                    '#contact'
                ),
                self::statsInline('Governance at a glance', [
                    ['value' => '1 hour', 'label' => 'Weekly evidence review'],
                    ['value' => 'Monthly', 'label' => 'Steering group'],
                    ['value' => '24h', 'label' => 'Response to incidents'],
                ]),
                self::featureGrid3('What we use in every programme', 'Your team can see all three from the first day.', [
                    ['icon' => 'calendar', 'title' => 'Weekly evidence review', 'description' => 'A one-hour meeting where we show working results, test data and user feedback instead of slides.'],
                    ['icon' => 'alert-triangle', 'title' => 'Risk register', 'description' => 'Every risk has an owner, a date and a next action. We review it every week.'],
                    ['icon' => 'history', 'title' => 'Decision log', 'description' => 'Who decided what, when and why. New team members and auditors can follow the history.'],
                ]),
                self::faq('Questions about governance', 'Answers on time, escalation and your own model.', [
                    ['question' => 'How much of our time does this take?', 'answer' => 'One hour a week for the evidence review. The steering group meets once a month with your sponsor and your budget owner.'],
                    ['question' => 'What happens when something goes wrong?', 'answer' => 'The owner of the risk escalates it the same day. Production issues get a response within 24 hours, and the next steering group reviews the cause.'],
                    ['question' => 'Can we keep our own governance model?', 'answer' => 'Yes. We fit our records to your steering structure and report format. The decision log and risk register stay, because they keep the work visible.'],
                ]),
                self::ctaCard('See how we would govern your programme', 'Bring your current steering setup. We show where our rhythm fits and what it could replace.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Leadership', 'leadership', 'Meet the four people who lead Northstar programmes: Mara Stein, Jonas Feld, Priya Nair and Elena Vogt, and what each of them is responsible for.', [
                self::teamWithBio('Senior people who stay on your programme', 'Senior team', 'Four leaders share responsibility for every programme, from the first call to the reviews after launch. Each of them owns one part of it.', [
                    ['name' => 'Mara Stein', 'role' => 'Managing Partner, Advisory', 'bio' => 'Mara leads every Advisory Sprint and presents the recommendation to your board. She also chairs the weekly review of all active programmes.'],
                    ['name' => 'Jonas Feld', 'role' => 'Delivery Principal', 'bio' => 'Jonas owns the plans and budgets of every Implementation Office programme. Our delivery managers report to him.'],
                    ['name' => 'Priya Nair', 'role' => 'Technical Director', 'bio' => 'Priya owns architecture and technical quality, from the first design review to the handover to your team.'],
                    ['name' => 'Elena Vogt', 'role' => 'Client Operations Lead', 'bio' => 'Elena runs every retainer, the monthly reviews and any escalation once your service is live.'],
                ]),
                self::founderQuote('Why we lead programmes ourselves', 'We only take on programmes one of us can lead in person. That is why you meet us in the first call.', 'Mara Stein', 'Managing Partner, ' . self::BRAND),
                self::faq('Questions about our leadership', 'Answers on access, continuity and who runs your programme.', [
                    ['question' => 'Will we work with these people directly?', 'answer' => 'Yes. At least one of them joins every steering group, and you have their direct contact details from the first day.'],
                    ['question' => 'What happens if a leader leaves?', 'answer' => 'Each programme has a named deputy who knows it well. We tell you about any change in writing and introduce the new lead in person.'],
                    ['question' => 'Can we meet the team before we sign?', 'answer' => 'Yes. You meet the leader and the delivery lead who would run your programme. We do not change them after signing without your agreement.'],
                ]),
                self::ctaCard('Meet the people who would lead your work', 'Book a call with Mara Stein or Jonas Feld. We discuss your goals and who from our team fits them.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Procurement', 'procurement', 'What procurement teams need to buy from Northstar: prices, contract documents, our security packet and client references, ready before the first call.', [
                self::textMedia(
                    'Start your supplier review before we talk',
                    'We send prices, draft contracts, our security packet and references on request. Your review can start while the business case is still being written.',
                    'Advisory Sprints use a short fixed-price agreement. Implementation and retainer work use a framework contract with a statement of work for each phase.',
                    'media-right',
                    self::generatedPhoto('Heroes/hero-pricing-08a48e08.webp', 'Reviewing a proposal', 'A woman with curly red hair and a man with grey hair review a proposal on a laptop, with a printed page beside it.'),
                    'Request documents',
                    '#contact'
                ),
                self::featureNumbered('How buying from us works', 'Three steps from the first call to a signed contract.', [
                    ['title' => 'Scoping call', 'description' => 'A 45-minute call with a senior member of our team about the problem, constraints and timing.'],
                    ['title' => 'Written proposal', 'description' => 'Scope, team, price and timeline in one document, ready for your internal review.'],
                    ['title' => 'Contract', 'description' => 'On your supplier terms or ours. Our legal contact lists any changes we need in one document.'],
                ]),
                self::featureList('Documents you can request', 'Documents', 'Ask for them at any point in your review.', [
                    ['icon' => 'file-text', 'title' => 'Price list', 'description' => 'The fixed price of the Advisory Sprint and how we price programmes and retainers, excluding VAT.'],
                    ['icon' => 'shield-check', 'title' => 'Security packet', 'description' => 'How we handle data, access, subcontractors and incidents, with a draft data processing agreement.'],
                    ['icon' => 'scale', 'title' => 'Contract documents', 'description' => 'Our standard terms, a draft framework contract and a named contact for legal questions.'],
                    ['icon' => 'users', 'title' => 'Client references', 'description' => 'A call with a client in your sector, arranged after your first conversation with us.'],
                ]),
                self::faq('Questions from procurement', 'Answers on price, terms and security reviews.', [
                    ['question' => 'Are your prices fixed?', 'answer' => 'The Advisory Sprint is fixed at EUR 18k. Implementation Office work is priced per programme, and Managed Improvement is a monthly retainer. All prices exclude VAT.'],
                    ['question' => 'Can you work under our supplier terms?', 'answer' => 'Yes, in most cases. Send us your terms early. Our legal contact reviews them and lists any changes we need in one document.'],
                    ['question' => 'Do you fill in security questionnaires?', 'answer' => 'Yes. Our security packet already answers most questions. We complete your questionnaire as well and name a contact for follow-up questions.'],
                ]),
                self::ctaCard('Request the documents you need', 'Tell us which documents your process needs. We send them with a named contact for questions.', 'Request documents', '#contact', 'Next step'),
            ]),
            self::page('Resources', 'resources', 'Free guides and templates for teams planning operational change: a readiness checklist, a governance meeting template and a service portal brief.', [
                self::textMedia(
                    'Tools we use in our own programmes',
                    'These guides and templates come from our client work. Use them to prepare a programme, with or without us.',
                    'Start with the readiness checklist. The governance template and the portal brief help once the scope is clear.',
                    'media-right',
                    self::generatedPhoto('Strategy/strategy-layer-tools-31203f33.webp', 'Tools in their place', 'Hand tools hang in painted outlines on a pegboard, each in its own place.', 'AI-generated image.'),
                    'Request the documents',
                    '#contact'
                ),
                self::resourceLibrary('Three documents to start with', 'Start here', [
                    ['title' => 'Readiness checklist', 'type_label' => 'Checklist', 'description' => 'Questions on goals, owners, budget and data to answer before you scope a programme.'],
                    ['title' => 'Governance meeting template', 'type_label' => 'Template', 'description' => 'A short agenda for weekly evidence, risk and decision reviews.'],
                    ['title' => 'Service portal brief', 'type_label' => 'Brief', 'description' => 'A one-page outline for replacing fragile internal workflows with one service portal.'],
                ]),
                self::featureNumbered('How clients use them', 'Most clients start with the checklist and bring their answers to a first call.', [
                    ['title' => 'Fill in the checklist', 'description' => 'Answer the questions on goals, owners, budget and data with your own team.'],
                    ['title' => 'Send us your answers', 'description' => 'We read them before the first call, so the conversation starts with your real questions.'],
                    ['title' => 'Use the templates in the sprint', 'description' => 'The governance template and the portal brief become working documents in the Advisory Sprint.'],
                ]),
                self::faq('Questions about the resources', 'Answers on access, reuse and support.', [
                    ['question' => 'How do we get the documents?', 'answer' => 'Ask through our contact page. We send all three as editable files, free of charge, and do not add you to a mailing list.'],
                    ['question' => 'Can we adapt the templates for our team?', 'answer' => 'Yes. Change them as you need. If you publish an adapted version, please name Northstar Advisory Group as the source.'],
                    ['question' => 'Can you help us use them?', 'answer' => 'Yes. Clients often fill in the readiness checklist first, then book an Advisory Sprint to work through the answers with us.'],
                ]),
                self::ctaCard('Work through the checklist with us', 'Send us your answers to the readiness checklist. We use them to prepare a focused first call.', 'Book a call', '#contact', 'Next step'),
            ]),
            self::page('Careers', 'careers', 'Senior roles at Northstar Advisory Group for consultants, product engineers and delivery managers who want planned, focused work with clear ownership.', [
                self::textMedia(
                    'Senior work at a steady pace',
                    'We hire experienced people and give them time to do the work well. Every role has clear ownership and a named lead.',
                    'Consultants work on one programme at a time, and we plan capacity every month, so overtime stays the exception. Most work is remote.',
                    'media-right',
                    self::generatedPhoto('Heroes/hero-downloads-179cee0f.webp', 'Working from a home office', 'A woman smiles at a standing desk with two monitors in a bright home office.')
                ),
                self::careerListing('Roles we are hiring for', 'Open roles', 'Open roles in advisory, delivery and engineering. Every advert states the salary range.', [
                    ['title' => 'Senior consultant', 'department' => 'Advisory', 'location' => 'Berlin or remote', 'type' => 'Full-time', 'apply_link' => '#contact'],
                    ['title' => 'Product engineer', 'department' => 'Implementation Office', 'location' => 'Remote, CET ±2 hours', 'type' => 'Full-time', 'apply_link' => '#contact'],
                    ['title' => 'Delivery manager', 'department' => 'Implementation Office', 'location' => 'Berlin or remote', 'type' => 'Full-time', 'apply_link' => '#contact'],
                ]),
                self::featureNumbered('How hiring works', 'Three conversations, with feedback after each step.', [
                    ['title' => 'Introduction call', 'description' => 'A short call about your experience and the work you want to do next.'],
                    ['title' => 'Case discussion', 'description' => 'We talk through a programme you worked on: the problem, your decisions and the result.'],
                    ['title' => 'Meet the team', 'description' => 'You meet the people you would work with and can ask them anything.'],
                ]),
                self::faq('Questions from candidates', 'Answers on pay, travel and replies.', [
                    ['question' => 'Do you publish salary ranges?', 'answer' => 'Yes. Every job advert states the salary range for the role.'],
                    ['question' => 'How much travel does the job involve?', 'answer' => 'Most work is remote. You travel for kickoffs, steering groups and workshops with clients.'],
                    ['question' => 'Will I hear back after applying?', 'answer' => 'Yes. We reply to every application, including when we cannot offer you a role.'],
                ]),
                self::ctaCard('Ask us about a role', 'Send your CV and a short note on the work you want to do. We reply to every application.', 'Send your CV', '#contact', 'Next step'),
            ]),
            self::page('Contact', 'contact', 'Contact Northstar Advisory Group about a new programme, a partnership, support for a live service or a procurement question.', [
                self::contactInfo('How to reach us', 'New enquiries get a reply within one working day. Retainer clients use their agreed support channel.', [
                    ['icon' => 'mail', 'label' => 'Email', 'value' => self::EMAIL, 'link' => 'mailto:' . self::EMAIL],
                    ['icon' => 'phone', 'label' => 'Phone', 'value' => self::PHONE, 'link' => self::PHONE_LINK],
                    ['icon' => 'map-pin', 'label' => 'Office', 'value' => self::STREET . ', ' . self::CITY],
                    ['icon' => 'clock', 'label' => 'Office hours', 'value' => 'Mon–Fri 8:30–17:30 CET'],
                ]),
                self::statsInline('Reply times', [
                    ['value' => '1 day', 'label' => 'Reply to new enquiries'],
                    ['value' => '24h', 'label' => 'Response to incidents'],
                    ['value' => '45 min', 'label' => 'Free first call'],
                ]),
                self::featureGrid3('Where to send your message', 'Procurement teams find documents and contacts on our procurement page.', [
                    ['icon' => 'briefcase', 'title' => 'New projects', 'description' => 'Book a 45-minute call about an Advisory Sprint, a programme or a retainer.'],
                    ['icon' => 'handshake', 'title' => 'Partnerships', 'description' => 'For technology partners and firms that want to deliver programmes with us.'],
                    ['icon' => 'shield-check', 'title' => 'Support for live services', 'description' => 'Clients with a retainer use their agreed channel. Production issues get a response within 24 hours.'],
                ]),
                self::textMedia(
                    'What happens after you contact us',
                    'For new projects, we suggest a 45-minute call with a senior member of our team.',
                    'Bring the problem, your constraints and the names of the people who decide. You leave with a first outline of the next step.',
                    'media-left',
                    self::generatedPhoto('Strategy/strategy-planning-call-7fe89208.webp', 'A first call at a table', 'Two people plan at a table with a notebook sketch and a laptop, as in a first call.')
                ),
                self::faq('Questions about contacting us', 'Answers on reply times, cost and confidentiality.', [
                    ['question' => 'How quickly will you reply?', 'answer' => 'We reply to new enquiries within one working day. Clients with a retainer get a response to production issues within 24 hours.'],
                    ['question' => 'Is the first call free?', 'answer' => 'Yes. The 45-minute call is free and does not commit you to anything.'],
                    ['question' => 'Can we sign an NDA before we talk?', 'answer' => 'Yes. Send us your NDA and we sign it before the first call. You can also use ours.'],
                ]),
            ]),
        ];
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
     * Search, the error page and the legal pages. The legal pages use the
     * dedicated imprint, privacy and accessibility elements, filled with the
     * made-up company data and a plain statement that the company is fictional.
     *
     * @return list<StarterPage>
     */
    private static function supportPages(): array
    {
        return [
            self::page('Search', 'search', 'Search the ' . self::BRAND . ' site for services, guides, case studies and details on how we deliver our work.', [
                self::searchHeader('', '', '', '#search', 'Search services, case studies and guides'),
            ], 'DesiderioSearch', true),
            self::page('404', '404', 'The link may be out of date, or the page has moved. Try one of the pages below, or go back to the homepage.', [
                self::sitemapGrid('Popular pages', self::sitemapGroups(), '3'),
            ], 'DesiderioError', true),
            self::page('Imprint', 'imprint', 'Company details of ' . self::BRAND . ': address, contact, register entry and the person responsible for the content. The company is fictional.', [
                self::imprint(
                    'Company details',
                    self::BRAND . ' GmbH',
                    self::STREET . "\n" . self::CITY . "\nGermany",
                    self::EMAIL,
                    self::PHONE,
                    "Register court: Amtsgericht Charlottenburg (Berlin)\nRegister number: HRB 000000 B\nManaging director: Mara Stein",
                    'DE000000000',
                    '<p>Responsible for the content under § 18 (2) MStV: Mara Stein, address as above.</p><p>' . self::BRAND . ' is a fictional company. Every name, address, number and register entry on this site is an example.</p>'
                ),
            ], 'DesiderioContentpage', true),
            self::page('Privacy', 'privacy', 'How ' . self::BRAND . ' handles personal data on this site and in client programmes, and how to make a privacy request. The company is fictional.', [
                self::privacyNotice(
                    'How we handle your data',
                    'This notice covers this website, enquiries and the data we process in client programmes. ' . self::BRAND . ' is fictional, so it shows the structure, not reviewed legal text.',
                    '1 October 2026',
                    [
                        ['title' => 'What we collect', 'content' => '<p>Your name, email address and message when you contact us. On each visit, technical data such as your IP address and browser type.</p>'],
                        ['title' => 'Why we use it', 'content' => '<p>To answer your enquiry, prepare a proposal and keep this site secure. We do not sell personal data or use it for advertising.</p>'],
                        ['title' => 'Data in client programmes', 'content' => '<p>When we work with your data, a data processing agreement sets out what we may do with it. Our security packet lists every subprocessor.</p>'],
                        ['title' => 'How long we keep it', 'content' => '<p>Enquiries for two years, contracts for as long as the law requires. You can ask us to delete your data earlier.</p>'],
                        ['title' => 'Your rights', 'content' => '<p>You can see, correct, export or delete your data. Write to privacy@example.com, and we reply within 30 days.</p>'],
                    ]
                ),
            ], 'DesiderioContentpage', true),
            self::page('Accessibility', 'accessibility', 'How accessible this site is, the standard we test against, known limitations and how to report a barrier. ' . self::BRAND . ' is fictional.', [
                self::accessibilityStatement(
                    'Accessibility statement',
                    'aa',
                    '<p>We aim to meet WCAG 2.2 at level AA on this site. ' . self::BRAND . ' is a fictional company, so this statement shows the structure. A real statement rests on an audit of the live site.</p>'
                    . '<h3>What we test</h3><ul><li>Keyboard access to navigation, menus, forms and dialogs.</li><li>Contrast of text, buttons and focus indicators in light and dark mode.</li><li>Headings, landmarks and alternative text for images.</li></ul>'
                    . '<h3>Known limitations</h3><p>We list every barrier we know of here, with the date we plan to fix it. If a page does not work for you, we send its content in another format within five working days.</p>',
                    'accessibility@example.com',
                    '1 October 2026'
                ),
            ], 'DesiderioContentpage', true),
        ];
    }

    /**
     * The three page groups of the sitemap grid, shared by the homepage and
     * the error page. Together with the legal group they link every content
     * page, including the five the main navigation leaves out.
     *
     * @return list<array{title: string, pages: list<array{label: string, link: string}>}>
     */
    private static function sitemapGroups(): array
    {
        return [
            [
                'title' => 'Services',
                'pages' => [
                    ['label' => 'Advisory Services', 'link' => '#advisory-services'],
                    ['label' => 'Implementation Office', 'link' => '#implementation-office'],
                    ['label' => 'Managed Improvement', 'link' => '#managed-improvement'],
                    ['label' => 'Sector Playbooks', 'link' => '#sector-playbooks'],
                ],
            ],
            [
                'title' => 'Proof',
                'pages' => [
                    ['label' => 'Case Studies', 'link' => '#case-studies'],
                    ['label' => 'Governance', 'link' => '#governance'],
                    ['label' => 'Leadership', 'link' => '#leadership'],
                    ['label' => 'Resources', 'link' => '#resources'],
                ],
            ],
            [
                'title' => 'Company',
                'pages' => [
                    ['label' => 'Procurement', 'link' => '#procurement'],
                    ['label' => 'Careers', 'link' => '#careers'],
                    ['label' => 'Contact', 'link' => '#contact'],
                ],
            ],
        ];
    }

    /**
     * Generated photos from Resources/Public/Styleguide (Heroes, Strategy):
     * one style, real working situations, people who do not exist.
     *
     * @return StarterImage
     */
    private static function generatedPhoto(string $path, string $title, string $alternative, string $description = 'AI-generated image; the people shown do not exist.'): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/' . $path,
            'title' => $title,
            'alternative' => $alternative,
            'description' => $description,
            'source' => '',
        ];
    }

    /**
     * One photo per client, matching its sector, instead of a portrait pool.
     *
     * @return StarterImage
     */
    private static function caseStudyImage(string $client): array
    {
        [$file, $title, $alternative] = match ($client) {
            'nordline' => ['client-vertex-financial.jpg', 'Onboarding paperwork', 'Printed forms, a pen and a calculator on a white desk.'],
            'helio' => ['client-lumen-health.jpg', 'A doctor with a smartphone', 'A doctor in a white coat with a stethoscope holds a smartphone in both hands.'],
            default => ['customer-engineering.jpg', 'Engineering at a production line', 'An engineer works on a laptop in a lab full of machines and cables.'],
        };

        return [
            'file' => 'Resources/Public/Styleguide/Unsplash/' . $file,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Photo: Unsplash.',
            'source' => 'https://unsplash.com',
        ];
    }

    /**
     * The four leaders keep one face each on every page. The portraits come
     * from the generated cast in Resources/Public/Styleguide/People/: one
     * studio style, faces centred for round crops. No description: the team
     * element prints it as a credit line under every portrait.
     *
     * @return StarterImage
     */
    private static function leaderPortrait(string $name): array
    {
        [$file, $alternative] = match ($name) {
            'Mara Stein' => ['miriam-cohen-b66ddf81.jpg', 'A woman in her late forties with dark curly hair, a charcoal blazer and a grey top.'],
            'Jonas Feld' => ['victor-novak-feda14b0.jpg', 'A man in his forties with dark hair greying at the temples, a grey blazer and a black T-shirt.'],
            'Priya Nair' => ['priya-nair-033fd302.jpg', 'A woman in her thirties with long dark hair, a grey blazer and a black top.'],
            default => ['elena-petrova-b059d17f.jpg', 'A woman around sixty with a dark-blonde bob and glasses, wearing a plum cardigan.'],
        };

        return [
            'file' => 'Resources/Public/Styleguide/People/' . $file,
            'title' => $name . ' portrait',
            'alternative' => $alternative,
            'description' => '',
            'source' => '',
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
    private static function sitemapGrid(string $header, array $groups, string $columns = '4'): array
    {
        return self::block('desiderio_sitemapgrid', [
            'header' => $header,
            'columns' => $columns,
            'groups' => $groups,
        ]);
    }

    /**
     * @param list<array{name: string, role: string, image: StarterImage}> $members
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
     * @return array{name: string, role: string, image: StarterImage}
     */
    private static function teamMember(string $name, string $role): array
    {
        return [
            'name' => $name,
            'role' => $role,
            'image' => self::leaderPortrait($name),
        ];
    }

    /**
     * @param list<array{name: string, role: string, bio: string}> $members
     * @return StarterBlock
     */
    private static function teamWithBio(string $header, string $eyebrow, string $subheadline, array $members): array
    {
        return self::block('desiderio_teamwithbio', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'members' => array_map(
                static fn(array $member): array => [
                    'name' => $member['name'],
                    'role' => $member['role'],
                    'bio' => $member['bio'],
                    'image' => self::leaderPortrait($member['name']),
                ],
                $members
            ),
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function founderQuote(string $header, string $quote, string $name, string $title): array
    {
        return self::block('desiderio_founderquote', [
            'header' => $header,
            'quote' => $quote,
            'name' => $name,
            'title' => $title,
            'image' => self::leaderPortrait($name),
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
     * Text next to a photo, with an optional button. Without a photo the
     * text would leave half the row empty, so every starter page passes one.
     *
     * @param StarterImage|null $media
     * @return StarterBlock
     */
    private static function textMedia(string $header, string $subheadline, string $content, string $layout, ?array $media = null, string $buttonText = '', string $buttonLink = ''): array
    {
        $fields = [
            'header' => $header,
            'subheadline' => $subheadline,
            'content' => '<p>' . $content . '</p>',
            'shadcn_layout' => $layout,
        ];
        if ($media !== null) {
            $fields['media'] = $media;
            $fields['media_rounded'] = true;
        }
        if ($buttonText !== '' && $buttonLink !== '') {
            $fields['button_text'] = $buttonText;
            $fields['button_link'] = $buttonLink;
        }

        return self::block('desiderio_textmedia', $fields);
    }

    /**
     * @param list<array{value: string, label: string}> $stats
     * @return StarterBlock
     */
    private static function statsInline(string $header, array $stats): array
    {
        return self::block('desiderio_statsinline', [
            'header' => $header,
            'show_separator' => true,
            'stats' => $stats,
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
     * Three icon cards in one row.
     *
     * @param list<array{icon: string, title: string, description: string}> $items
     * @return StarterBlock
     */
    private static function featureGrid3(string $header, string $subheadline, array $items): array
    {
        return self::block('desiderio_featuregrid3', [
            'header' => $header,
            'subheadline' => $subheadline,
            'items' => $items,
        ]);
    }

    /**
     * Numbered steps on a panel.
     *
     * @param list<array{title: string, description: string}> $items
     * @return StarterBlock
     */
    private static function featureNumbered(string $header, string $subheadline, array $items): array
    {
        return self::block('desiderio_featurenumbered', [
            'header' => $header,
            'subheadline' => $subheadline,
            'items' => $items,
        ]);
    }

    /**
     * Dated steps on a panel; the step label sits in the timeline dot, so it
     * stays short ("Week 1").
     *
     * @param list<array{step: string, title: string, description: string}> $items
     * @return StarterBlock
     */
    private static function featureTimeline(string $header, string $subheadline, array $items): array
    {
        return self::block('desiderio_featuretimeline', [
            'header' => $header,
            'subheadline' => $subheadline,
            'items' => $items,
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function quote(string $quote, string $author, string $role): array
    {
        return self::block('desiderio_quote', [
            'quote_text' => $quote,
            'author' => $author,
            'role' => $role,
            'variant' => 'large',
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
     * @param list<array{client_name: string, summary: string, result: string, image: StarterImage, link?: string}> $cases
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
     * @param list<array{title: string, type_label: string, description: string, link?: string}> $items
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
     * @param list<array{title: string, department: string, location: string, type: string, apply_link: string}> $jobs
     * @return StarterBlock
     */
    private static function careerListing(string $header, string $eyebrow, string $subheadline, array $jobs): array
    {
        return self::block('desiderio_careerlisting', [
            'header' => $header,
            'eyebrow' => $eyebrow,
            'subheadline' => $subheadline,
            'jobs' => $jobs,
        ]);
    }

    /**
     * @param list<array{icon: string, label: string, value: string, link?: string}> $items
     * @return StarterBlock
     */
    private static function contactInfo(string $header, string $subheadline, array $items): array
    {
        return self::block('desiderio_contactinfo', [
            'header' => $header,
            'subheadline' => $subheadline,
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
     * @param list<StarterQuestion> $items
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

    /**
     * @return StarterBlock
     */
    private static function imprint(string $header, string $companyName, string $address, string $email, string $phone, string $registryInfo, string $vatId, string $additionalInfo): array
    {
        return self::block('desiderio_imprint', [
            'header' => $header,
            'company_name' => $companyName,
            'address' => $address,
            'contact_email' => $email,
            'contact_phone' => $phone,
            'registry_info' => $registryInfo,
            'vat_id' => $vatId,
            'additional_info' => $additionalInfo,
        ]);
    }

    /**
     * @param list<array{title: string, content: string}> $sections
     * @return StarterBlock
     */
    private static function privacyNotice(string $header, string $intro, string $lastUpdated, array $sections): array
    {
        return self::block('desiderio_privacynotice', [
            'header' => $header,
            'intro' => '<p>' . $intro . '</p>',
            'last_updated' => $lastUpdated,
            'sections' => $sections,
        ]);
    }

    /**
     * @return StarterBlock
     */
    private static function accessibilityStatement(string $header, string $conformanceLevel, string $content, string $email, string $lastUpdated): array
    {
        return self::block('desiderio_accessibilitystatement', [
            'header' => $header,
            'conformance_level' => $conformanceLevel,
            'content' => $content,
            'contact_email' => $email,
            'last_updated' => $lastUpdated,
        ]);
    }
}
