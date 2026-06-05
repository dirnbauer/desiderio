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
        $subpages = self::withTopNavigation([
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
        ], ['advisory-services', 'implementation-office', 'managed-improvement', 'sector-playbooks', 'case-studies', 'contact']);
        $subpages = array_merge($subpages, self::supportPages('Northstar Advisory Group', 'contact'));

        return [
            'label' => 'Corporate starter',
            'slug' => 'corporate',
            'rootSlug' => '/desiderio-corporate-starter',
            'rootTitle' => 'Northstar Advisory Group',
            'rootNavTitle' => 'Home',
            'purpose' => 'Help enterprise buyers understand the offer, trust the delivery model, and start a qualified procurement-safe conversation.',
            'abstract' => 'A senior B2B service firm for companies that need operational change delivered with visible governance, procurement-ready proof, and accountable delivery.',
            'home' => [
                'layout' => 'DesiderioStartpage',
                'content' => [
                    self::heroStats('Digital operations work that survives procurement and rollout', 'Northstar Advisory Group', 'For executive teams replacing fragile internal workflows: advisory, implementation, and managed improvement with proof, governance, and procurement support in one clear path.', 'Book a working session', '#contact', [
                        ['value' => '42%', 'label' => 'Less manual handoff', 'stat_description' => 'Measured after replacing regional spreadsheet flows.'],
                        ['value' => '11', 'label' => 'Departments onboarded', 'stat_description' => 'Legal, finance, operations, support, and regional leads.'],
                        ['value' => '24h', 'label' => 'Escalation response', 'stat_description' => 'Contracted production-facing response window.'],
                        ['value' => '99.9%', 'label' => 'Critical uptime', 'stat_description' => 'Tracked across portals and reporting workflows.'],
                    ]),
                    self::navTabs('Corporate buyer path', [
                        ['label' => 'Services', 'link' => '#advisory-services'],
                        ['label' => 'Proof', 'link' => '#case-studies'],
                        ['label' => 'Governance', 'link' => '#governance'],
                        ['label' => 'Procurement', 'link' => '#procurement'],
                        ['label' => 'Contact', 'link' => '#contact'],
                    ]),
                    self::headerSection('A corporate site must make the buying case legible', 'Buyer pathway', 'The page stack is organized around evaluation: what you do, how delivery is governed, who leads it, what proof exists, and how procurement moves forward.'),
                    self::kpiCards('Operating proof in one screen', 'Board-level signals', 'Use this row as the first proof check after the masthead.', [
                        ['value' => '38%', 'label' => 'Lower manual handoffs', 'detail_text' => 'Measured after replacing six spreadsheet-led workflows.', 'trend' => 'positive'],
                        ['value' => '11', 'label' => 'Departments onboarded', 'detail_text' => 'Legal, finance, operations, support, and regional teams.', 'trend' => 'positive'],
                        ['value' => '24h', 'label' => 'Escalation response', 'detail_text' => 'Contracted response window for production-facing issues.', 'trend' => 'neutral'],
                        ['value' => '99.9%', 'label' => 'Critical process uptime', 'detail_text' => 'Tracked across service portals and reporting workflows.', 'trend' => 'positive'],
                    ]),
                    self::featureCards('What enterprise buyers need before they talk to sales', 'Evaluation content', 'Every section answers a procurement, leadership, or delivery question.', [
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
                    self::teamGridMinimal('Client-facing leadership', 'Senior owners are visible before a buyer asks who will actually guide the work.', [
                        self::teamMember('Mara Stein', 'Managing Partner, Advisory', 0),
                        self::teamMember('Jonas Feld', 'Delivery Principal', 1),
                        self::teamMember('Priya Nair', 'Technical Director', 2),
                        self::teamMember('Elena Vogt', 'Client Operations Lead', 3),
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
            self::featureList($title . ' priorities', $eyebrow, 'These priorities answer the questions a serious stakeholder brings to this page.', array_map(
                static fn (string $topic, int $index): array => ['icon' => ['shield-check', 'settings', 'users'][$index % 3], 'title' => $topic, 'description' => 'Explain scope, ownership, and proof for ' . strtolower($topic) . '.'],
                $topics,
                array_keys($topics)
            )),
            self::faq($title . ' questions', 'Keep high-friction answers close to the decision.', [
                ['question' => 'Who is this page for?', 'answer' => 'For stakeholders who need a precise overview before they commit to a call, download, or internal discussion.'],
                ['question' => 'What should this page prove?', 'answer' => 'It should connect the offer to client-specific outcomes, sector language, and the strongest available proof.'],
                ['question' => 'Where should this page send visitors next?', 'answer' => 'Send them to a case study, a service detail page, or the contact route that matches their buying stage.'],
            ]),
            self::ctaCard('Discuss ' . strtolower($title), 'Bring the challenge, current constraints, decision group, and success measure. The team will map the right next step.', 'Talk to the team', '#contact', 'Corporate pathway'),
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
            self::page('Search', 'search', 'Find pages, guides, proof, and operational details across the site.', [
                self::searchHeader('', '', '', '/search', 'Search pages, proof, and resources'),
            ], 'DesiderioSearch', true),
            self::page('404', '404', 'A useful error page that routes visitors back into high-value content instead of ending the session.', [
                self::headerSection('This page is not available', '404', 'Route visitors to high-value pages instead of leaving them at a dead end.', 'center'),
                self::ctaCard('Return to a useful path', 'Go back to the homepage, search the site, or contact the team if something should exist here.', 'Go home', '/', 'Utility page'),
            ], 'DesiderioError', true),
            self::page('Imprint', 'imprint', 'Company details and publishing responsibility for procurement, legal, and trust review.', [
                self::headerSection($brand . ' imprint', 'Legal', 'Company information, publishing responsibility, and durable contact details for legal review.', 'left'),
                self::textMedia('Company information', 'Verified legal data belongs here before launch.', 'Registered company, business address, responsible editor, and contact route belong here. Keep the copy short and procurement-friendly.', 'media-above'),
            ], 'DesiderioContentpage', true),
            self::page('Privacy', 'privacy', 'Privacy expectations, request routes, and data handling notes for visitors and buyers.', [
                self::headerSection('Privacy at ' . $brand, 'Trust', 'Privacy policy content, request handling, and data processing context in plain language.', 'left'),
                self::faq('Privacy questions', 'These answers should be aligned with reviewed legal copy before publishing.', [
                    ['question' => 'What should be listed here?', 'answer' => 'Data categories, processing purposes, retention periods, subprocessors, contact routes, and user rights.'],
                    ['question' => 'Who owns the policy?', 'answer' => 'Assign a legal or operations owner and include a durable privacy contact address.'],
                    ['question' => 'Where should forms link?', 'answer' => 'Route privacy requests to a monitored inbox or form with clear response expectations.'],
                ]),
            ], 'DesiderioContentpage', true),
            self::page('Accessibility', 'accessibility', 'Accessibility commitments, known limitations, and feedback routes for visitors.', [
                self::headerSection('Accessibility statement', 'Service quality', 'State the standard, current status, known limitations, and feedback route in plain language.', 'left'),
                self::featureList('Accessibility review points', 'Checklist', 'Use this page as a practical audit prompt before launch.', [
                    ['icon' => 'keyboard', 'title' => 'Keyboard paths', 'description' => 'Check navigation, forms, menus, tabs, and modal states without a mouse.'],
                    ['icon' => 'contrast', 'title' => 'Readable contrast', 'description' => 'Validate text, buttons, chart labels, and focus indicators across presets.'],
                    ['icon' => 'message-square', 'title' => 'Feedback route', 'description' => 'Give visitors a monitored channel for barriers and correction requests.'],
                ]),
                self::ctaCard('Report an accessibility issue', 'Describe the page, device, browser, assistive technology, and the barrier you found.', 'Contact the team', '#' . $contactSlug, 'Feedback'),
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
            'button_text' => 'View details',
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

}
