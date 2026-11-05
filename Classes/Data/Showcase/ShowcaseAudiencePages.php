<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Target-group showcase pages: the audience overview plus one page per
 * audience (agency, in-house team, freelancer).
 *
 * Every page follows the order of the sales model: the reader's problem,
 * what Desiderio does, what is free, and what costs money. Each audience page
 * has the same outline: header, the story, the benefits, the path with
 * Desiderio (four steps), what you pay and what you get, the business case,
 * a testimonial and the next step. Every price comes from ShowcasePricing;
 * the business cases also name the reader's own internal rates (€75, €90 and
 * €120 an hour), which are not our prices.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 * @phpstan-import-type PricingPlan from ShowcasePricing
 */
final class ShowcaseAudiencePages
{
    /** The model page: why the software is free and what costs money. */
    private const string MODEL_PAGE_LINK = '{{page:pricing/how-we-earn}}';

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
     * Overview page above the three audience pages: the model in four cards
     * and one link to the model page, then one condensed pitch per target group, each linking to the full
     * story on its subpage.
     *
     * @return ShowcasePage
     */
    private static function targetGroupsPage(): array
    {
        return [
            'title' => 'Solutions',
            'navTitle' => 'For your team',
            'slug' => '/target-groups',
            'seoTitle' => 'For agencies, in-house teams and freelancers',
            'abstract' => 'Desiderio is built for agencies, in-house teams and freelancers. This page gives the short version for each group and links to its own page.',
            'description' => 'What Desiderio does for agencies, in-house marketing teams and freelancers, with the plan we recommend and a link to each group’s own page.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::salesHero(
                    'For every team',
                    'Built for the teams that ship TYPO3',
                    'Agencies deliver fixed-price projects faster, in-house teams publish without tickets and freelancers get a full design system, all for free.',
                    ['text' => 'See agency details', 'link' => '{{page:target-groups/for-agencies}}'],
                    ['text' => 'See pricing', 'link' => '{{page:pricing}}'],
                    ShowcaseHeroPhotos::for('solutions'),
                ),
                // The model in the four ways of the price source. Feature
                // cards carry no links, so the inline CTA below leads to the
                // model page, as on the home page.
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Open source',
                    'header' => 'Free software, paid care',
                    'subheadline' => ShowcasePricing::modelSentence(),
                    'items' => array_map(
                        static fn(array $way): array => ['title' => $way['title'], 'description' => $way['description']],
                        ShowcasePricing::ways(),
                    ),
                ]),
                ShowcaseBlocks::block('desiderio_ctainline', [
                    'header' => 'How we earn with free software',
                    'description_text' => 'Why it is free, what always stays free and where the money goes.',
                    'cta_text' => 'How we earn',
                    'cta_link' => self::MODEL_PAGE_LINK,
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Agencies deliver fixed-price projects faster',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Agencies and integrators',
                    'content' => '<p><strong>Every hour your team spends on templates comes out of the margin of a fixed-price TYPO3 project.</strong> Desiderio brings 244 finished and audited elements, so your quote covers content modelling and integration instead.</p>'
                        . sprintf('<p>The software is free on every client site, and the Agency plan adds answers by the next business day for %s.</p>', self::yearly('agency')),
                    'media_rounded' => 1,
                    'button_text' => 'See agency details',
                    'button_link' => '{{page:target-groups/for-agencies}}',
                    'media' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset in the site settings', 'TYPO3 site settings with the Desiderio theme preset list open.'),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'In-house teams build their own campaign pages',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'In-house marketing and product teams',
                    'content' => '<p><strong>Today every new campaign page is a developer ticket, and it waits in the queue.</strong> With Desiderio, editors build pages from 244 elements with backend previews and publish them the same day.</p>'
                        . sprintf('<p>The software is free; we recommend Studio at %s and an editor training for %s.</p>', self::yearly('studio'), self::rate('Editor training')),
                    'media_rounded' => 1,
                    'button_text' => 'See in-house details',
                    'button_link' => '{{page:target-groups/for-inhouse-teams}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor-7f9bffd8.webp', 'Editing a page in the Visual Editor', 'The TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Freelancers get a full design system for ' . self::plan('community')['price'],
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Freelancers and solo developers',
                    'content' => '<p><strong>On a fixed-price quote, the design and template phase is the part that overruns and goes unpaid.</strong> Desiderio gives you a design system with 244 elements and 15 theme presets, so that phase becomes an afternoon of content modelling.</p>'
                        . sprintf('<p>Start on the free Community plan and move to Studio at %s when your first client site goes live.</p>', self::yearly('studio')),
                    'media_rounded' => 1,
                    'button_text' => 'See freelancer details',
                    'button_link' => '{{page:target-groups/for-freelancers}}',
                    'media' => ShowcaseBlocks::screenshot('frontend-hero-lagoon-e69362e6.png', 'Published Desiderio site', 'The Desiderio demo site in the Lagoon theme preset.'),
                ]),
                // The page ends on a next step, like every other hub.
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Not sure which plan fits?',
                    'description' => 'Compare the free Community plan with the Studio, Agency and Partner support plans side by side.',
                    'cta_text' => 'Compare the plans',
                    'cta_link' => '{{page:pricing}}',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function agencyPage(): array
    {
        $agency = self::plan('agency');

        return [
            'title' => 'For agencies and integrators',
            'navTitle' => 'For agencies and integrators',
            'slug' => '/target-groups/for-agencies',
            'abstract' => 'How agencies use Desiderio: finished elements shorten fixed-price projects, and live theme demos help in pitches. The Agency plan adds direct support.',
            'description' => sprintf('Desiderio for TYPO3 agencies: 244 finished elements, live theme demos and one installation for many brands. The Agency plan costs %s.', self::yearly('agency')),
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For agencies',
                    'header' => 'Deliver fixed-price TYPO3 projects faster',
                    'subheadline' => '244 finished elements replace most of the template work, so more of the budget goes to content and integration. Desiderio is free on every client site.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Show three designs in the kickoff meeting',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Theme presets switch live, with the same content.',
                    'content' => '<p><strong>Switch theme presets while the client watches.</strong> The content stays the same, and you need no preparation.</p><ul><li><strong>Lagoon</strong>: a calm corporate look.</li><li><strong>Midnight</strong>: a dark look for a product launch.</li><li><strong>The client’s own design</strong>: made on the shadcn/ui create page, for the brand pitch.</li></ul><p>Each page tree can use its own preset, so one installation runs every sub-brand. A multi-brand site becomes a setting, not a separate budget line.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'Read about theming',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset in the site settings', 'TYPO3 site settings with the Desiderio theme preset list open.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for your agency',
                    'eyebrow' => 'The business case',
                    'subheadline' => 'Your team spends fewer unpaid hours on templates and more hours on work you can bill.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Reliable estimates', 'description' => 'Your estimate covers content modelling and integration. The 244 elements are already built and audited.', 'link' => '{{page:content-types}}'],
                        ['icon' => 'users', 'title' => 'Easier handovers', 'description' => 'Editors get backend previews, a clear content wizard and inline editing. Expect fewer support questions from the first week.', 'link' => '{{page:features/visual-editor}}'],
                        ['icon' => 'sparkles', 'title' => 'White-label adaptation', 'description' => 'We build Brand Themes and custom elements at fixed prices, and you deliver them as your own work.', 'link' => '{{page:pricing}}'],
                        ['icon' => 'shield-check', 'title' => 'Direct support', 'description' => 'The Agency plan adds an answer by the next business day, three named developers and a yearly onboarding call.', 'link' => '{{page:pricing}}'],
                    ],
                ]),
                self::path('You start for free. Each later step says what it costs.', [
                    ['title' => 'Pitch with the free demo', 'description' => 'Seed the demo site and switch three theme presets live in the kickoff meeting. It costs nothing, on any number of sites.'],
                    ['title' => 'Second client signs: the Agency plan', 'description' => sprintf('Agency costs %s for up to 25 live sites, with answers by the next business day and a yearly onboarding call.', self::yearly('agency'))],
                    ['title' => 'Your brand and elements, under your name', 'description' => sprintf('A Brand Theme costs %s and custom elements start at %s. We build them, and you deliver them as your agency’s work.', self::rate('Brand Theme'), self::rate('Custom element S'))],
                    ['title' => 'Care for clients who want one partner', 'description' => sprintf('We run the client’s site for %s, billed to you: hosting, updates, monitoring and backups. We never contact your clients.', self::care())],
                ]),
                self::payAndGet('agency', [
                    sprintf('Agency plan: %s for up to 25 live sites', self::yearly('agency')),
                    sprintf('Brand Theme: %s per client brand', self::rate('Brand Theme')),
                    sprintf('Custom elements: %s (S) or %s (M)', self::rate('Custom element S'), self::rate('Custom element M')),
                    sprintf('Care: %s, billed to you', self::care()),
                    sprintf('Sponsored feature: %s, released for everyone', ShowcasePricing::sponsoredFeature()['price']),
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'What the Agency plan costs per project',
                    'price' => $agency['price'],
                    'billing_period' => self::billingPeriod('agency'),
                    'description' => '<p>Building a client-ready element set by hand takes 60–100 developer hours per project. At an internal cost of €75 per hour, that is €4,500 to €7,500. With Desiderio, it takes a preset and a brand pass. At eight projects a year, the plan costs €249 per project.</p>'
                        . self::modelLine(),
                    'features' => [
                        ['text' => 'Up to 25 live sites: at 8 projects a year, the plan costs €249 per project'],
                        ['text' => 'Template work drops from about 80 to 20 hours per project, which saves about €4,500 at €75 per hour'],
                        ['text' => 'An answer by the next business day (CET) and a yearly onboarding call for your team'],
                        ['text' => 'Staging and local installs are free; only live sites count'],
                        ['text' => 'Three live theme demos in the kickoff meeting, with no preparation'],
                    ],
                    'button_text' => 'See pricing',
                    'button_link' => '{{page:pricing}}',
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'We showed the client three themes in the kickoff meeting by switching presets live. The project came in 30% under our usual template budget.',
                    'author_name' => 'Hannah Vogel',
                    'author_title' => 'Lead integrator (example)',
                    'author_company' => 'TYPO3 agency',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::person('hannah-vogel-57005ef4.jpg', 'Hannah Vogel', 'A woman in her fifties with short silver-grey hair, a black blazer and a grey T-shirt.'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Use Desiderio in your next pitch',
                    'description' => 'Start free on your current project. Until 31 March 2027, the first 20 agencies on a paid plan keep their price while it runs.',
                    'cta_text' => 'Get started free',
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
        $studio = self::plan('studio');

        return [
            'title' => 'For in-house marketing and product teams',
            'navTitle' => 'For in-house marketing and product teams',
            'slug' => '/target-groups/for-inhouse-teams',
            'abstract' => 'How in-house teams use Desiderio: editors build campaign pages without developer tickets, and design tokens keep them on brand. Studio adds guaranteed support.',
            'description' => 'Desiderio for in-house teams: editors build campaign pages from 244 elements with backend previews, and design tokens keep every page on brand.',
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For in-house teams',
                    'header' => 'Campaign pages without developer tickets',
                    'subheadline' => 'Developers build the site once, and the software is free. After that, marketing publishes campaign pages from 244 elements, on brand and without code.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'A backend that shows what editors publish',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Previews, a content wizard and inline editing.',
                    'content' => '<p>Editors see every element before they publish it.</p><ul><li><strong>Previews</strong>: all 244 elements have a preview in the page module.</li><li><strong>Content wizard</strong>: the elements are sorted into ten groups with plain names.</li><li><strong>Inline editing</strong>: the Visual Editor changes text directly on the page.</li><li><strong>Themes per campaign</strong>: each page tree can use its own preset, and design tokens prevent off-brand colours.</li></ul>'
                        . '<p>Why plain names matter to editors: <a href="{{page:usability-slogans}}">the usability talk</a>.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See technical details',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor-7f9bffd8.webp', 'Editing a page in the Visual Editor', 'The TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for your team',
                    'eyebrow' => 'The operations case',
                    'subheadline' => 'Fewer handoffs between teams, faster campaigns and simpler accessibility reviews.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'rocket', 'title' => 'Faster campaign pages', 'description' => 'Editors build pages from finished elements and check the live preview. Developers can work on features again instead of landing pages.', 'link' => '{{page:chapter-hero}}'],
                        ['icon' => 'globe', 'title' => 'Consistent branding', 'description' => 'Design tokens set colours, typography and spacing on every page. Campaign themes stay within the presets you approve.', 'link' => '{{page:themes}}'],
                        ['icon' => 'shield-check', 'title' => 'Audit-ready accessibility', 'description' => 'The build checks WCAG 2.2 AA contrast for every theme. Screen-reader labels are translated, and the markup is semantic.', 'link' => '{{page:technical-features}}'],
                        ['icon' => 'users', 'title' => 'Training for your team', 'description' => 'Editors learn the system in a 3-hour training, and developers in one Kickstart workshop. Both have a fixed price.', 'link' => '{{page:pricing}}'],
                    ],
                ]),
                self::path('You start for free. Each later step says what it costs.', [
                    ['title' => 'Developers build the site once, free', 'description' => 'Install Desiderio, pick a theme preset and build the page tree. Every release and every fix stays free.'],
                    ['title' => 'First campaign: Studio and a training', 'description' => sprintf('Studio costs %s: answers within 2 business days and the maintenance promise. An editor training costs %s once.', self::yearly('studio'), self::rate('Editor training'))],
                    ['title' => 'Your brand as a theme preset', 'description' => sprintf('A Brand Theme costs %s: your brand as a preset, checked for WCAG 2.2 AA contrast and ready 10 business days after the assets arrive.', self::rate('Brand Theme'))],
                    ['title' => 'Care when no one runs TYPO3 in-house', 'description' => sprintf('We run your site for %s: hosting on your own Coolify instance in the EU, updates, monitoring and daily backups.', self::care())],
                ]),
                self::payAndGet('studio', [
                    sprintf('Studio plan: %s for up to 5 live sites', self::yearly('studio')),
                    sprintf('Editor training: %s once, for up to 12 editors', self::rate('Editor training')),
                    sprintf('Kickstart workshop: %s once, for your developers', self::rate('Kickstart workshop')),
                    sprintf('Brand Theme: %s for your brand as a preset', self::rate('Brand Theme')),
                    sprintf('Care: %s', self::care()),
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'Studio plan with an editor training',
                    'price' => $studio['price'],
                    'billing_period' => self::billingPeriod('studio'),
                    'description' => '<p>We recommend Studio. An agency-built landing page costs €1,200–2,400, for 10–20 hours at €120 per hour, and it waits in a queue. With Desiderio, your editors publish it the same day. One campaign page a year pays for the plan.</p>'
                        . self::modelLine(),
                    'features' => [
                        ['text' => sprintf('Studio, %s: support for up to 5 live sites and the maintenance promise', self::yearly('studio'))],
                        ['text' => 'Answers from the maintainers within 2 business days'],
                        ['text' => sprintf('Editor training, %s once: afterwards the whole team publishes without tickets', self::rate('Editor training'))],
                        ['text' => sprintf('Kickstart workshop, %s once: your developers learn the system in one session', self::rate('Kickstart workshop'))],
                    ],
                    'button_text' => 'See pricing',
                    'button_link' => '{{page:pricing}}',
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'Our editors build campaign pages themselves now. The backend previews show them what they publish, so our brand team rarely has to step in.',
                    'author_name' => 'Jonas Klein',
                    'author_title' => 'Head of digital (example)',
                    'author_company' => 'in-house brand team',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::person('jonas-klein-59ee66a4.jpg', 'Jonas Klein', 'A young man with short dark-brown hair and a dark shirt, looking slightly to one side.'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Start with Studio and a training',
                    'description' => 'Install Desiderio for free. Add Studio and an editor training when your first campaign starts, and your editors publish without tickets.',
                    'cta_text' => 'See pricing',
                    'cta_link' => '{{page:pricing}}',
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
        $community = self::plan('community');

        return [
            'title' => 'For freelancers and solo developers',
            'navTitle' => 'For freelancers and solo developers',
            'slug' => '/target-groups/for-freelancers',
            'abstract' => 'How freelancers use Desiderio: a free design system with quality checks keeps fixed-price projects profitable. Studio adds support when you need it.',
            'description' => 'Desiderio for freelancers: a free design system with 244 elements and CI quality checks, so fixed-price TYPO3 projects stay profitable.',
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For freelancers',
                    'header' => 'A full design system for solo developers',
                    'subheadline' => 'The package is free under GPL-2.0. It contains 244 elements, 62 components and the quality checks that run in its CI.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Fixed-price projects that stay profitable',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Less time on design and templates.',
                    'content' => '<p><strong>The design and template phase makes fixed-price quotes risky.</strong> It has no clear end, depends on opinions and goes unpaid when it overruns. Desiderio shortens it.</p><ul><li><strong>Pick a preset</strong>, or let the client choose one on ui.shadcn.com/create.</li><li><strong>Seed the demo</strong> and show the client 244 real elements instead of wireframes.</li><li><strong>Save weeks</strong>: three weeks of template work becomes an afternoon of content modelling.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'See all elements',
                    'button_link' => '{{page:chapter-hero}}',
                    'media' => ShowcaseBlocks::screenshot('frontend-hero-lagoon-e69362e6.png', 'Published Desiderio site', 'The Desiderio demo site in the Lagoon theme preset.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for a one-person studio',
                    'eyebrow' => 'The freelancer case',
                    'subheadline' => 'The package covers design, quality checks and demos, so you can take on larger projects alone.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'Design included', 'description' => 'You get shadcn/ui components, checked colour contrast, dark mode and 5 icon libraries. Clients see a finished design system.', 'link' => '{{page:themes}}'],
                        ['icon' => 'book-open', 'title' => 'Quality checks included', 'description' => 'PHPStan level 8, more than 900 automated tests and a template audit come with the package. Clients get the checks of a large agency.', 'link' => '{{page:technical-features}}'],
                        ['icon' => 'zap', 'title' => 'Live demos for prospects', 'description' => 'Seed the demo site on a test DDEV project and send the prospect a link. A working demo shows more than a PDF offer.', 'link' => '{{page:downloads}}'],
                        ['icon' => 'users', 'title' => 'Support when needed', 'description' => 'Studio adds answers from the maintainers within 2 business days and the maintenance promise, from your first live client site.', 'link' => '{{page:pricing}}'],
                    ],
                ]),
                self::path('You start for free. Each later step says what it costs.', [
                    ['title' => 'Install, seed, show 244 real elements', 'description' => 'Install the free package on a test DDEV site, seed the demo and send the client a link instead of wireframes.'],
                    ['title' => 'First live client site: Studio', 'description' => sprintf('Studio costs %s for up to 5 live sites, with answers within 2 business days. Staging and local installs are free.', self::yearly('studio'))],
                    ['title' => 'Say thanks when it pays off', 'description' => sprintf('Become a Supporter for %s %s: your name or logo on our How we earn page and in the release notes.', ShowcasePricing::supporter()['price'], ShowcasePricing::supporter()['period'])],
                    ['title' => 'Care for a client who wants it run', 'description' => sprintf('We run the site for %s: hosting on its own Coolify instance in the EU, updates, monitoring and daily backups.', self::care())],
                ]),
                self::payAndGet('studio', [
                    sprintf('Studio plan: %s for up to 5 live sites', self::yearly('studio')),
                    sprintf('Custom elements: %s (S) or %s (M)', self::rate('Custom element S'), self::rate('Custom element M')),
                    sprintf('Support hours: %s for %s, for code reviews', self::rate('Support hours'), self::unit('Support hours')),
                    sprintf('Supporter: %s %s, recognition only', ShowcasePricing::supporter()['price'], ShowcasePricing::supporter()['period']),
                    sprintf('Care: %s', self::care()),
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => sprintf('Start with Community for %s', $community['price']),
                    'price' => $community['price'],
                    'billing_period' => $community['period'],
                    'description' => sprintf('<p>Building this library yourself would take more than 600 unpaid hours: 244 audited, accessible elements at 2–3 hours each. On a €10,000 fixed-price site, the template phase drops from about 80 to 20 hours. At €90 per hour, that is about €5,400 of margin. Studio costs %s, less than seven billable hours.</p>', self::yearly('studio'))
                        . self::modelLine(),
                    'features' => [
                        ['text' => sprintf('%s forever: all 244 elements and 62 components, about €55,000 of work at freelance rates', $community['price'])],
                        ['text' => 'Around 60 hours saved per fixed-price project, about €5,400 at €90 per hour'],
                        ['text' => sprintf('Studio, %s or %s: answers within 2 business days and the maintenance promise', self::yearly('studio'), self::monthly('studio'))],
                        ['text' => 'A live demo site from one command, for pitches against agencies'],
                    ],
                    'button_text' => 'Get started free',
                    'button_link' => ShowcaseBlocks::REPO_URL,
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'As a freelancer I can offer a full design system at one-person prices. Clients compare my demos with agency pitches, and I often win.',
                    'author_name' => 'Mara Weiss',
                    'author_title' => 'Freelance TYPO3 developer (example)',
                    'author_company' => 'independent studio',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::person('mara-weiss-fd46b34a.jpg', 'Mara Weiss', 'A woman in her forties with a dark chin-length bob and a dark green knit sweater, smiling.'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Install Desiderio from GitHub',
                    'description' => 'Add the GitHub repository to Composer and require the package. It is free under GPL-2.0, on every client site you build.',
                    'cta_text' => 'Get started free',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * "Your path with Desiderio": four numbered steps from free use to Care.
     *
     * @param list<array{title: string, description: string}> $steps
     * @return ShowcaseBlock
     */
    private static function path(string $subheadline, array $steps): array
    {
        return ShowcaseBlocks::block('desiderio_featurenumbered', [
            'header' => 'Your path with Desiderio',
            'subheadline' => $subheadline,
            'items' => array_map(
                static fn(array $step): array => ['title' => $step['title'], 'description' => '<p>' . $step['description'] . '</p>'],
                $steps,
            ),
        ]);
    }

    /**
     * "What you pay, what you get": what is always free (the same list on
     * every page) and what the audience pays for, with prices, as two equal
     * tiers. The paid tier shows the plan we recommend to the audience; its
     * list names the other paid items, some cheaper, so the tier states the
     * plan's yearly price rather than a "from" price.
     *
     * @param list<string> $paid
     * @return ShowcaseBlock
     */
    private static function payAndGet(string $planKey, array $paid): array
    {
        $plan = self::plan($planKey);

        return ShowcasePricingPages::freeAndPaid(
            'Free and paid',
            'What you pay, what you get',
            ShowcasePricing::modelSentence(),
            self::yearly($planKey),
            sprintf('We recommend %s. Everything else is our time, at a fixed net price.', $plan['name']),
            $paid,
        );
    }

    /**
     * The closing line of every business case: one link to the model page.
     */
    private static function modelLine(): string
    {
        return '<p>How we earn with free software: <a href="' . self::MODEL_PAGE_LINK . '">the full explanation</a>.</p>';
    }

    /**
     * @return PricingPlan
     */
    private static function plan(string $key): array
    {
        foreach (ShowcasePricing::plans() as $plan) {
            if ($plan['key'] === $key) {
                return $plan;
            }
        }

        throw new \LogicException(sprintf('ShowcasePricing has no plan "%s".', $key), 1762300801);
    }

    /**
     * A plan's yearly price: "€590 a year".
     */
    private static function yearly(string $key): string
    {
        return sprintf('%s a year', self::plan($key)['price']);
    }

    /**
     * A plan's monthly price from its period: "€59 a month".
     */
    private static function monthly(string $key): string
    {
        $period = self::plan($key)['period'];
        if (preg_match('/€[0-9][0-9,]* a month/u', $period, $match) !== 1) {
            throw new \LogicException(sprintf('Plan "%s" has no monthly price.', $key), 1762300802);
        }

        return $match[0];
    }

    /**
     * The billing line of the pricing block: "/year, or €59 a month".
     */
    private static function billingPeriod(string $key): string
    {
        return str_replace('per year', '/year', self::plan($key)['period']);
    }

    /**
     * Care for one site: "€390 a month per live site".
     */
    private static function care(): string
    {
        $care = ShowcasePricing::care();

        return sprintf('%s %s', $care['price'], $care['period']);
    }

    /**
     * A fixed-price service's rate: "€2,490".
     */
    private static function rate(string $title): string
    {
        return self::service($title)['rate'];
    }

    /**
     * A fixed-price service's unit: "5 hours".
     */
    private static function unit(string $title): string
    {
        return self::service($title)['unit'];
    }

    /**
     * @return array{title: string, rate: string, unit: string, description: string}
     */
    private static function service(string $title): array
    {
        return ShowcasePricing::service($title);
    }
}
