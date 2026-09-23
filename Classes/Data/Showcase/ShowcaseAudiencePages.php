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
            'abstract' => 'Desiderio is built for agencies, in-house teams and freelancers. This page gives the short version for each group and links to its own page.',
            'description' => 'What Desiderio does for agencies, in-house marketing teams and freelancers, with the plan we recommend and a link to each group\'s own page.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Target groups',
                    'header' => 'Built for three kinds of TYPO3 teams',
                    'subheadline' => 'Agencies, in-house teams and freelancers use Desiderio for different reasons. Each section names the main benefit and the plan we recommend.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Agencies deliver fixed-price projects faster',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Agencies and integrators',
                    'content' => '<p><strong>Template hours lower the margin of every fixed-price TYPO3 project.</strong> With 244 finished and audited elements, your quote covers content modelling and integration instead of weeks of template building.</p><p>Show three theme presets in the kickoff meeting and run every sub-brand from one installation. We recommend the Agency plan: €149 per month for unlimited projects, with answers within 4 business hours (CET).</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See agency details',
                    'button_link' => '{{page:target-groups/for-agencies}}',
                    'media' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset in the site settings', 'TYPO3 site settings with the Desiderio theme preset list open.'),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'In-house teams build their own campaign pages',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'In-house marketing and product teams',
                    'content' => '<p><strong>Developers build the site once. After that, marketing runs it every day without developer tickets.</strong> All 244 elements have a backend preview, the content wizard uses plain names, and the Visual Editor edits text inline.</p><p>Design tokens keep every page on brand, and each campaign can use its own preset. The build checks WCAG 2.2 contrast for every preset. We recommend Pro at €49 per month, and managed hosting from €99 per month if you don\'t want to run servers.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See in-house details',
                    'button_link' => '{{page:target-groups/for-inhouse-teams}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Editing a page in the Visual Editor', 'The TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Freelancers get a full design system for €0',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Freelancers and solo developers',
                    'content' => '<p><strong>You get a complete design system, 244 elements and the quality checks from its CI, for €0.</strong> The design and template phase, which makes fixed-price quotes risky, becomes an afternoon of content modelling.</p><p>Seed the demo on a test DDEV site and send the prospect a link. We recommend the free Community plan, and Pro at €49 per month once your client list grows.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See freelancer details',
                    'button_link' => '{{page:target-groups/for-freelancers}}',
                    'media' => ShowcaseBlocks::screenshot('frontend-hero-lagoon.png', 'Published Desiderio site', 'The Desiderio demo site in the Lagoon theme preset.'),
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
            'title' => 'For agencies and integrators',
            'navTitle' => 'For agencies',
            'slug' => '/target-groups/for-agencies',
            'abstract' => 'How agencies use Desiderio: finished elements shorten fixed-price projects, and live theme demos help in pitches. The Agency plan adds direct support from the maintainers.',
            'description' => 'Desiderio for TYPO3 agencies: 244 finished elements, live theme demos and one installation for many brands. The Agency plan costs €149 per month.',
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For agencies',
                    'header' => 'Deliver fixed-price TYPO3 projects faster',
                    'subheadline' => 'Finished elements replace most of the template work, so more of the budget goes to content and integration. The Agency plan costs €149 per month, for unlimited projects.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Show three designs in the kickoff meeting',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Theme presets switch live, with the same content.',
                    'content' => '<p><strong>Switch theme presets while the client watches.</strong> The content stays the same, and you need no preparation.</p><ul><li><strong>Lagoon</strong>: a calm corporate look.</li><li><strong>Midnight</strong>: a dark look for a product launch.</li><li><strong>The client\'s own design</strong>: made on the shadcn/ui create page, for the brand pitch.</li></ul><p>Each page tree can use its own preset, so one installation runs every sub-brand. A multi-brand site becomes a setting, not a separate budget line.</p>',
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
                        ['icon' => 'zap', 'title' => 'Reliable estimates', 'description' => 'Your estimate covers content modelling and integration. The 244 elements are already built and audited.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'Easier handovers', 'description' => 'Editors get backend previews, a clear content wizard and inline editing. Expect fewer support questions from the first week.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'White-label adaptation', 'description' => 'Brand adaptation costs from €1,990 and custom elements from €390. We build them, and you deliver them as your own work.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Direct support', 'description' => 'The Agency plan costs €149 per month or €1,490 per year, for unlimited projects. It adds answers within 4 business hours (CET) and quarterly editor onboarding.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'What the Agency plan costs per project',
                    'price' => '€149',
                    'billing_period' => '/month, or €1,490/year (about €124/month)',
                    'description' => 'Building a client-ready element set by hand takes 60–100 developer hours per project. At an internal cost of €75 per hour, that is €4,500 to €7,500. With Desiderio, it takes a preset and a brand pass. At eight projects a year, the plan costs €186 per project.',
                    'features' => [
                        ['text' => 'Unlimited client projects: at 8 a year, the plan costs €186 per project'],
                        ['text' => 'Template work drops from about 80 to 15 hours per project, which saves about €4,900 at €75 per hour'],
                        ['text' => 'Answers within 4 business hours (CET) and quarterly editor onboarding for your clients'],
                        ['text' => 'White-label presets: brand adaptation from €1,990, custom elements from €390'],
                        ['text' => 'Three live theme demos in the kickoff meeting, with no preparation'],
                    ],
                    'button_text' => 'See pricing',
                    'button_link' => '{{page:chapter-pricing}}',
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'We showed the client three themes in the kickoff meeting by switching presets live. The project came in 30% under our usual template budget.',
                    'author_name' => 'Hannah Vogel',
                    'author_title' => 'Lead integrator (example)',
                    'author_company' => 'TYPO3 agency',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::portrait('portrait-hannah-vogel.jpg', 'Hannah Vogel'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Use Desiderio in your next pitch',
                    'description' => 'Start with the free version on your current project. Switch to Agency at €149 per month when the second client signs. Code DESIDERIO20 gives 20% off the first year.',
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
        return [
            'title' => 'For in-house marketing and product teams',
            'navTitle' => 'For in-house teams',
            'slug' => '/target-groups/for-inhouse-teams',
            'abstract' => 'How in-house teams use Desiderio: editors build campaign pages without developer tickets, and design tokens keep every page on brand. Pro adds support and LTS updates.',
            'description' => 'Desiderio for in-house teams: editors build campaign pages from 244 elements with backend previews, and design tokens keep every page on brand.',
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For in-house teams',
                    'header' => 'Campaign pages without developer tickets',
                    'subheadline' => 'Developers build the site once. After that, marketing creates and changes pages itself, on brand and without touching code.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'A backend that shows what editors publish',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Previews, a content wizard and inline editing.',
                    'content' => '<p>Editors see every element before they publish it.</p><ul><li><strong>Previews</strong>: all 244 elements have a preview in the page module.</li><li><strong>Content wizard</strong>: the elements are sorted into ten groups with plain names.</li><li><strong>Inline editing</strong>: the Visual Editor changes text directly on the page.</li><li><strong>Themes per campaign</strong>: each page tree can use its own preset, and design tokens prevent off-brand colours.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'See technical details',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Editing a page in the Visual Editor', 'The TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for your team',
                    'eyebrow' => 'The operations case',
                    'subheadline' => 'Fewer handoffs between teams, faster campaigns and simpler accessibility reviews.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'rocket', 'title' => 'Faster campaign pages', 'description' => 'Editors build pages from finished elements and check the live preview. Developers can work on features again instead of landing pages.', 'link' => '{{page:chapter-hero}}'],
                        ['icon' => 'globe', 'title' => 'Consistent branding', 'description' => 'Design tokens set colours, typography and spacing on every page. Campaign themes stay within the presets you approve.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Audit-ready accessibility', 'description' => 'The build checks WCAG 2.2 contrast for every theme. Screen-reader labels are translated, and the markup is semantic.', 'link' => ''],
                        ['icon' => 'clock', 'title' => 'Managed operations', 'description' => 'Managed hosting costs from €99 per month, or €179 for Business with staging and a 99.9% SLA. Creator Care, at €490 per month, handles updates, backups and LTS upgrades.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'Pro plan with Creator Care and hosting',
                    'price' => '€638',
                    'billing_period' => '/month in total: Pro €49, Creator Care €490, hosting €99',
                    'description' => 'We recommend Pro. An agency-built landing page costs €1,200–2,400, for 10–20 hours at €120 per hour, and it waits in a queue. With Desiderio, your editors publish it the same day. Two campaign pages a month save you €2,400–4,800. The full stack costs less than one of those pages.',
                    'features' => [
                        ['text' => 'The full stack pays for itself with the first campaign page each month'],
                        ['text' => 'Pro, €49 per month: LTS compatibility updates. One saved upgrade day, about €900, covers 18 months of Pro'],
                        ['text' => 'Creator Care: 4 senior developer hours for €490, which is €122.50 per hour, without an agency queue'],
                        ['text' => 'Managed hosting, €99 per month: EU data centre, daily backups and monitoring'],
                        ['text' => 'Editor workshop, €690 once: afterwards the whole team publishes without tickets'],
                    ],
                    'button_text' => 'See pricing',
                    'button_link' => '{{page:chapter-pricing}}',
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'Our editors build campaign pages themselves now. The backend previews show them what they publish, so our brand team rarely has to step in.',
                    'author_name' => 'Jonas Klein',
                    'author_title' => 'Head of digital (example)',
                    'author_company' => 'in-house brand team',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::portrait('advisor-jonas-klein.jpg', 'Jonas Klein'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Start with Pro and managed hosting',
                    'description' => 'Pro costs €49 per month, with email support within 2 days and LTS compatibility updates. Add managed hosting from €99 per month, and we run the servers.',
                    'cta_text' => 'View on GitHub',
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
            'title' => 'For freelancers and solo developers',
            'navTitle' => 'For freelancers',
            'slug' => '/target-groups/for-freelancers',
            'abstract' => 'How freelancers use Desiderio: a free design system with quality checks keeps fixed-price projects profitable. Pro adds support when you need it.',
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
                    'media' => ShowcaseBlocks::screenshot('frontend-hero-lagoon.png', 'Published Desiderio site', 'The Desiderio demo site in the Lagoon theme preset.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What changes for a one-person studio',
                    'eyebrow' => 'The freelancer case',
                    'subheadline' => 'The package covers design, quality checks and demos, so you can take on larger projects alone.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'Design included', 'description' => 'You get shadcn/ui components, checked colour contrast, dark mode and 5 icon libraries. Clients see a finished design system.', 'link' => ''],
                        ['icon' => 'book-open', 'title' => 'Quality checks included', 'description' => 'PHPStan level 8, more than 170 tests and a template audit come with the package. Your clients get the quality checks of a large agency.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Live demos for prospects', 'description' => 'Seed the demo site on a test DDEV project and send the prospect a link. A working demo shows more than a PDF offer.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'Support when needed', 'description' => 'Pro costs €49 per month: answers from the maintainers within 2 days, LTS compatibility updates and early access to new elements. Custom elements cost from €390.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_pricingsimple', [
                    'header' => 'Start with Community for €0',
                    'price' => '€0',
                    'billing_period' => 'forever · GPL-2.0',
                    'description' => 'Building this library yourself would take more than 600 unpaid hours: 244 audited, accessible elements at 2–3 hours each. On a €10,000 fixed-price site, the template phase drops from about 80 to 20 hours. At €90 per hour, that is about €5,400 of margin. Pro costs €490 a year, less than six billable hours.',
                    'features' => [
                        ['text' => '€0 forever: all 244 elements and 62 components, worth about €55,000 at freelance rates'],
                        ['text' => 'Around 60 hours saved per fixed-price project, about €5,400 at €90 per hour'],
                        ['text' => 'Pro, €49 per month or €490 per year: answers within 2 days and LTS compatibility updates'],
                        ['text' => 'Custom elements from €390, less than five hours of your own time'],
                        ['text' => 'A live demo site from one command, for pitches against agencies'],
                    ],
                    'button_text' => 'Get started free',
                    'button_link' => ShowcaseBlocks::REPO_URL,
                ]),
                ShowcaseBlocks::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'As a freelancer I can offer a full design system at one-person prices. Clients compare my demos with agency pitches, and I often win.',
                    'author_name' => 'Lena Hoffmann',
                    'author_title' => 'Freelance TYPO3 developer (example)',
                    'author_company' => 'independent studio',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => ShowcaseBlocks::portrait('portrait-lena-hoffmann.jpg', 'Lena Hoffmann'),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Install Desiderio with one command',
                    'description' => 'Run composer require webconsulting/desiderio. It is free under GPL-2.0. Switch to Pro at €49 per month when your client list grows. Code DESIDERIO20 gives 20% off the first year.',
                    'cta_text' => 'Get started free',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
