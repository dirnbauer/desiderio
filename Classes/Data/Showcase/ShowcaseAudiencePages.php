<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Target-group showcase pages: the audience overview plus one page per
 * audience (agency, in-house team, freelancer).
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseAudiencePages
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [
            self::targetGroupsPage(),
            self::agencyPage(),
            self::inhousePage(),
            self::freelancerPage(),
        ];
    }

    /**
     * Overview page above the three audience pages: one condensed pitch per
     * target group, each linking to the full story on its subpage.
     *
     * @return ShowcasePage
     */
    private static function targetGroupsPage(): array
    {
        return [
            'title' => 'Target groups',
            'navTitle' => 'Target groups',
            'slug' => '/target-groups',
            'abstract' => 'Desiderio is built for three kinds of TYPO3 teams: agencies & integrators, in-house marketing & product teams, and freelancers & solo developers. This overview tells the short version of each story and links to the full pitch.',
            'description' => 'Agencies, in-house teams, freelancers: the three target groups Desiderio is built for — each story in short form, the full pitch one click away.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Target groups',
                    'header' => 'Built for three kinds of TYPO3 teams',
                    'subheadline' => 'Agencies win pitches with it, in-house teams ship campaigns with it, freelancers scale with it. Here is the short version of each story — the full pitch is one click further.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Win the pitch. Keep the margin.',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Agencies & integrators',
                    'content' => '<p><strong>Every fixed-price TYPO3 project has the same enemy: template hours — Desiderio deletes them.</strong> 244 finished, audited elements mean your quote covers content modeling and integration, not weeks of template construction.</p><p>Demo three theme presets live in the kickoff meeting, run every sub-brand from one install, and hand over a backend your clients\' editors actually enjoy. The Agency tier (€149/month, unlimited projects) adds a priority line to the maintainers — answers within 4 business hours (CET workdays).</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The full agency story',
                    'button_link' => '{{page:target-groups/for-agencies}}',
                    'media' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme switching in site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown.'),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Ship the campaign. Skip the ticket queue.',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'In-house marketing & product teams',
                    'content' => '<p><strong>Your developers built the site once. With Desiderio, marketing runs it every day after.</strong> Backend previews for all 244 elements, a plain-language wizard, and inline editing in the Visual Editor turn campaign pages into an afternoon task — not a dev ticket.</p><p>Design tokens keep every page on brand, per-campaign themes stay curated freedom, and WCAG 2.2-checked contrast keeps the accessibility audit calm. Managed hosting from €99/month takes the servers off your plate.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The full in-house story',
                    'button_link' => '{{page:target-groups/for-inhouse-teams}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Inline editing in the Visual Editor', 'TYPO3 Visual Editor editing a Desiderio hero element inline.'),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Look like a team of ten. Bill like one.',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Freelancers & solo developers',
                    'content' => '<p><strong>A complete design system, 244 elements, and a CI-grade quality pipeline — for exactly €0.</strong> The open-ended design-and-template phase that kills fixed-price quotes becomes an afternoon of content modelling.</p><p>Seed the styleguide on a throwaway ddev site and send the prospect a link: a living demo beats a PDF offer every single time. Pro at €49/month is the safety net when the client list grows.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The full freelancer story',
                    'button_link' => '{{page:target-groups/for-freelancers}}',
                    'media' => ShowcaseBlocks::screenshot('frontend-hero-lagoon.png', 'A seeded Desiderio frontend', 'Desiderio styleguide frontend in the Lagoon theme preset.'),
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function agencyPage(): array
    {
        return [
            'title' => 'For agencies & integrators',
            'navTitle' => 'For agencies',
            'slug' => '/target-groups/for-agencies',
            'abstract' => 'Why TYPO3 agencies quote less, deliver faster, and win more pitches with Desiderio: reusable elements, live theme demos, multi-brand per-page themes, and an Agency tier with a direct line to the maintainers.',
            'description' => 'Win TYPO3 pitches with live theme demos and 244 finished elements. Desiderio deletes template hours from every fixed-price quote — Agency tier from €149/month.',
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Target group 1 — agencies & integrators',
                    'header' => 'Win the pitch. Keep the margin.',
                    'subheadline' => 'Every fixed-price TYPO3 project has the same enemy: template hours. Desiderio deletes them. Agency tier: €149/month, unlimited projects.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Demo three designs in the kickoff meeting',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Your new sales superpower',
                    'content' => '<p><strong>Switch theme presets live while the client watches</strong> — same content, three designs, zero prep.</p><ul><li><strong>Lagoon</strong> — the calm corporate look.</li><li><strong>Midnight</strong> — the product launch.</li><li><strong>Their own design</strong> — straight from the shadcn/ui create page for the brand pitch.</li></ul><p>Set a different preset per page subtree and run every sub-brand from one install. Multi-brand used to be a budget line; now it is a dropdown.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'How theming works',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme switching in site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for your agency',
                    'eyebrow' => 'The business case',
                    'subheadline' => 'Less unbillable groundwork, more billable strategy.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Quote with confidence', 'description' => '244 finished, audited elements mean your estimate covers content modeling and integration — not weeks of template construction.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'Hand over without fear', 'description' => 'Editors get previews, a clean wizard, and inline editing. Your support inbox notices the difference in week one.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'White-label adaptation', 'description' => 'Need a fully custom preset for a flagship client? Brand adaptation from €1,990, custom elements from €390 — built by the creators, delivered as your work.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'A direct line when it matters', 'description' => 'The Agency tier (€149/month or €1,490/year, unlimited projects) includes answers within 4 business hours (CET workdays) and quarterly editor onboarding for your clients.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'The Agency plan, in agency math',
                    'price' => '€149',
                    'billing_period' => '/month · €1,490/year ≈ €124/month',
                    'description' => 'Hand-building a client-ready element set costs 60–100 dev-hours per project — €4,500 to €7,500 at a €75/h internal cost. Desiderio cuts that to a preset and a brand pass. At eight projects a year the licence works out to €186 per project, so it pays for itself the first afternoon it saves.',
                    'features' => [
                        ['text' => 'Unlimited client projects — at 8 a year the licence costs €186 per project'],
                        ['text' => 'Template groundwork drops from ~80 to ~15 hours per build: ≈ €4,900 margin recovered per project (at €75/h internal)'],
                        ['text' => 'Priority answers within 4 business hours (CET workdays) + quarterly editor onboarding for your clients'],
                        ['text' => 'White-label: ship a custom preset as your own work — brand adaptation from €1,990, custom elements from €390'],
                        ['text' => 'Demo three themes live in the kickoff — one extra won pitch pays for years of the plan'],
                    ],
                    'button_text' => 'Compare all plans',
                    'button_link' => '{{page:chapter-pricing}}',
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'We demoed three themes in the kickoff by switching presets live. The client signed that afternoon — and the project closed 30% under our usual template budget.',
                    'author_name' => 'Hannah Vogel',
                    'author_title' => 'Lead Integrator',
                    'author_company' => 'TYPO3 agency',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::portrait('portrait-hannah-vogel.jpg', 'Hannah Vogel'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Put Desiderio in your next pitch',
                    'description' => 'Start free on the project today — go Agency (€149/month) when the second client signs. Code DESIDERIO20 takes 20% off the first year.',
                    'cta_text' => 'Start free now',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function inhousePage(): array
    {
        return [
            'title' => 'For in-house marketing & product teams',
            'navTitle' => 'For in-house teams',
            'slug' => '/target-groups/for-inhouse-teams',
            'abstract' => 'Why in-house teams ship campaigns without dev tickets: editor previews, per-campaign themes, brand governance through tokens, managed hosting, and Pro support with guaranteed LTS updates.',
            'description' => 'Ship campaign pages without dev tickets: backend previews for all 244 Desiderio elements, per-campaign themes, and brand governance enforced by design tokens.',
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Target group 2 — in-house teams',
                    'header' => 'Ship the campaign. Skip the ticket queue.',
                    'subheadline' => 'Your developers built the site once. With Desiderio, marketing runs it every day after — on brand, on time, without touching code.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'The backend your editors keep asking for',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Editing, with eyes open',
                    'content' => '<p>The backend your editors keep asking for — everything visible, nothing published blind.</p><ul><li><strong>Real previews</strong> — all 244 elements preview in the page module.</li><li><strong>Plain-language wizard</strong> — elements sorted into ten clear groups.</li><li><strong>Inline editing</strong> — the Visual Editor changes text right on the page.</li><li><strong>Per-campaign themes</strong> — a preset per page tree, while tokens make off-brand colours impossible.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'Technical details',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Inline editing in the Visual Editor', 'TYPO3 Visual Editor editing a Desiderio hero element inline.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for your team',
                    'eyebrow' => 'The operations case',
                    'subheadline' => 'Fewer handoffs, faster campaigns, calmer compliance reviews.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'rocket', 'title' => 'Campaign pages in hours', 'description' => 'Compose from finished elements with live previews. The dev queue is for features again, not for landing pages.', 'link' => '{{page:chapter-hero}}'],
                        ['icon' => 'globe', 'title' => 'Brand governance built in', 'description' => 'Design tokens enforce the palette, typography, and spacing everywhere. Per-page themes are curated freedom — not chaos.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Accessibility for the audit', 'description' => 'WCAG 2.2-checked contrast on every theme, translated screen-reader labels, semantic markup. Bring the report to legal with confidence.', 'link' => ''],
                        ['icon' => 'clock', 'title' => 'Run it without running servers', 'description' => 'Managed hosting from €99/month (Business €179 with staging and 99.9% SLA) and the Creator Care retainer at €490/month keep updates, backups, and LTS upgrades off your plate.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'Pro + Creator Care, in CFO math',
                    'price' => '€638',
                    'billing_period' => '/month all-in — Pro €49 + Creator Care €490 + hosting €99',
                    'description' => 'One agency-built landing page costs €1,200–2,400 (10–20 hours at €120/h) and waits in a queue. With Desiderio your editors ship it the same day. At two campaign pages a month that is €2,400–4,800 you no longer buy — and the complete stack costs less than a single one of those pages.',
                    'features' => [
                        ['text' => 'Break-even: the full €638/month stack pays for itself with the first campaign page each month'],
                        ['text' => 'Pro €49/month: guaranteed LTS updates — one avoided upgrade day (≈ €900) covers 18 months of Pro'],
                        ['text' => 'Creator Care: 4 senior dev-hours at €122.50/h — agency-rate work without the agency queue'],
                        ['text' => 'Managed hosting €99/month — EU datacenter, daily backups, monitoring, no servers to run'],
                        ['text' => 'Editor workshop €690 once, and the whole team publishes without tickets'],
                    ],
                    'button_text' => 'Compare all plans',
                    'button_link' => '{{page:chapter-pricing}}',
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'Our editors build campaign pages themselves now. The backend previews mean nobody publishes blind anymore — and our brand team finally sleeps at night.',
                    'author_name' => 'Jonas Klein',
                    'author_title' => 'Head of Digital',
                    'author_company' => 'in-house brand team',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::portrait('advisor-jonas-klein.jpg', 'Jonas Klein'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Give marketing its independence back',
                    'description' => 'Pro at €49/month buys 2-day support and guaranteed LTS updates. Add managed hosting from €99/month and the whole stack is someone else\'s pager.',
                    'cta_text' => 'Talk to the creators',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function freelancerPage(): array
    {
        return [
            'title' => 'For freelancers & solo developers',
            'navTitle' => 'For freelancers',
            'slug' => '/target-groups/for-freelancers',
            'abstract' => 'Why solo developers deliver agency-grade TYPO3 sites with Desiderio: a complete free design system, quality pipeline included, fixed-price projects that stay profitable, and Pro support as the safety net.',
            'description' => 'Agency-grade TYPO3 sites from a studio of one: Desiderio gives freelancers a complete free design system, 244 elements, and a CI-grade quality pipeline for €0.',
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Target group 3 — freelancers & solo devs',
                    'header' => 'Look like a team of ten. Bill like one.',
                    'subheadline' => 'A complete design system, 244 elements, and a CI-grade quality pipeline — for exactly €0. Your one-person studio just got an unfair advantage.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Fixed-price projects that stay profitable',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The solo math',
                    'content' => '<p><strong>The design-and-template phase is what kills a fixed-price quote</strong> — open-ended, opinion-driven, unbillable when it overruns. Desiderio closes it.</p><ul><li><strong>Pick a preset</strong> — or let the client choose on ui.shadcn.com/create.</li><li><strong>Seed the demo</strong> — then walk the client through 244 real elements, not wireframes.</li><li><strong>Afternoon, not weeks</strong> — three weeks of template work becomes an afternoon of content modelling.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'See the element library',
                    'button_link' => '{{page:chapter-hero}}',
                    'media' => ShowcaseBlocks::screenshot('frontend-hero-lagoon.png', 'A seeded Desiderio frontend', 'Desiderio styleguide frontend in the Lagoon theme preset.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for your studio of one',
                    'eyebrow' => 'The freelancer case',
                    'subheadline' => 'Enterprise output, solo overhead.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'A design team in a package', 'description' => 'shadcn-quality components, solved color contrast, dark mode, five icon libraries. Nobody asks whether you have a designer on staff.', 'link' => ''],
                        ['icon' => 'book-open', 'title' => 'A QA department in CI', 'description' => 'PHPStan max, 170+ tests, and the 11-category template audit ship with the package. Your clients get big-agency quality gates for free.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Demos that close deals', 'description' => 'Seed the styleguide on a throwaway ddev site and send the prospect a link. A living demo beats a PDF offer every single time.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'Backup when you need it', 'description' => 'Pro at €49/month is your safety net: 2-day support from the maintainers, guaranteed LTS updates, early access to new drops. Custom elements from €390 when a client wants the impossible.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'Community + Pro, in freelancer math',
                    'price' => '€0',
                    'billing_period' => 'forever · GPL-2.0',
                    'description' => 'Rebuilding this library yourself — 244 audited, accessible elements at 2–3 hours each — is 600+ hours nobody pays for. On a €10,000 fixed-price site the template phase shrinks from ~80 to ~20 hours: about €5,400 of margin back in your pocket at €90/h. Pro costs €588 a year — six and a half billable hours.',
                    'features' => [
                        ['text' => '€0 forever: all 244 elements + 53 components — a €55,000 library at freelance rates'],
                        ['text' => '~60 hours saved per fixed-price project ≈ €5,400 margin recovered (at €90/h)'],
                        ['text' => 'Pro €49/month = 6.5 billable hours a year, buys 2-day response and guaranteed LTS updates'],
                        ['text' => 'Custom elements from €390 — cheaper than five hours of your own time'],
                        ['text' => 'One-command living demo — pitch against agencies and win'],
                    ],
                    'button_text' => 'Get started free',
                    'button_link' => ShowcaseBlocks::REPO_URL,
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'As a freelancer I quote design-system quality at one-person prices. Clients compare my demos with agency pitches — and I win.',
                    'author_name' => 'Lena Hoffmann',
                    'author_title' => 'Freelance TYPO3 Developer',
                    'author_company' => 'independent studio',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::portrait('portrait-lena-hoffmann.jpg', 'Lena Hoffmann'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Your unfair advantage is one command away',
                    'description' => 'composer require webconsulting/desiderio — free forever under GPL-2.0. Go Pro for €49/month when the client list grows. DESIDERIO20 takes 20% off the first year.',
                    'cta_text' => 'Get started free',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
