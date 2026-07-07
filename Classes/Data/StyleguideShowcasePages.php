<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Marketing showcase seeded by desiderio:styleguide:seed in addition to the
 * element chapters: managed homepage content on the styleguide root page plus
 * subpages (technical deep dive, a target groups overview with one subpage
 * per target audience, legal demo pages, a 404 page, a GEO explainer, an
 * agentic TYPO3 v14 strategy page, and illustrative success stories).
 *
 * Internal links use the placeholder syntax {{page:<slug>}}; the seeder
 * replaces them with t3://page?uid=N once the target pages exist. The special
 * slug {{page:home}} resolves to the styleguide root page. Pages with a
 * parentSlug are created below that showcase page instead of the root.
 *
 * The success stories double as a small t3g/blog section: the parent page is
 * the blog list (blogList), the stories carry blog metadata (publish date,
 * categories, tags) and seed as doktype 137 posts when EXT:blog is installed.
 * Their blog post header renders title, abstract, and metadata badges, so the
 * story pages deliberately ship without an article hero element.
 *
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 * @phpstan-type ShowcaseBlogMeta array{publishDate: string, categories: list<string>, tags: list<string>}
 * @phpstan-type ShowcasePage array{title: string, navTitle: string, slug: string, abstract: string, description: string, parentSlug: string|null, subtitle?: string, blogList?: bool, blog?: ShowcaseBlogMeta, hideInNav?: bool, content: array<int, StarterBlock>}
 */
final class StyleguideShowcasePages
{
    private const REPO_URL = 'https://github.com/dirnbauer/desiderio';
    private const CREATE_URL = 'https://ui.shadcn.com/create';

    /**
     * @return array<int, StarterBlock>
     */
    public static function homeContent(): array
    {
        return [
            self::block('desiderio_hero', [
                'variant' => 'split',
                'badge_text' => 'shadcn/ui for TYPO3 14',
                'header' => 'The TYPO3 design system your editors will brag about',
                'subheadline' => 'Desiderio puts 244 shadcn-styled content elements, 49 typed Fluid components, and 15 runtime-switchable themes into one composer package. Install it before lunch. Re-theme it before the coffee gets cold.',
                'primary_button_text' => 'Start free — €0 forever',
                'primary_button_link' => self::REPO_URL,
                'primary_button_variant' => 'default',
                'secondary_button_text' => 'See the technical facts',
                'secondary_button_link' => '{{page:technical-features}}',
                'hero_image' => self::screenshot('backend-visual-editor.png', 'Editing a page in the Visual Editor', 'TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
                'image_position' => 'right',
                'overlay_opacity' => '0.5',
            ]),

            // ------------------------------------------------- the problem
            self::block('desiderio_featurecards', [
                'eyebrow' => 'The problem Desiderio solves',
                'header' => 'Running a TYPO3 site in 2026 shouldn\'t hurt this much',
                'subheadline' => 'Six things that slow every CMS team down — and the specific answer Desiderio ships for each. Written for the people who feel them: editors, developers, and whoever signs the invoice.',
                'items' => [
                    ['title' => 'Every layout change is a developer ticket', 'description' => 'Editors build finished pages from a ten-group wizard, with a real backend preview for all 244 elements and inline editing in the Visual Editor — the dev queue goes back to being about features, not landing pages.'],
                    ['title' => 'One edit and the whole layout breaks', 'description' => 'Every element is assembled from typed Fluid components on one token contract. Spacing, colour and type come from the design system, so off-brand pages and broken grids are impossible to create by accident.'],
                    ['title' => 'Your stack is fifteen plugins holding hands', 'description' => 'Desiderio is one Composer package. News, Solr, Blog, Powermail and the Form Framework all render through the same shadcn-styled templates — one design system, one security model, one thing to update.'],
                    ['title' => 'A redesign means a rebuild', 'description' => 'Theming is pure OKLCH tokens switched at runtime: zero rebuilds, no Node or Vite pipeline. Repaint the whole site, or just one page tree, from site settings — no deployment, no cache anxiety.'],
                    ['title' => 'The accessibility audit is coming — the EAA is now law', 'description' => 'WCAG 2.2 contrast is enforced by the build, markup is semantic with proper landmarks, and screen-reader labels are translated. You bring the report to legal instead of retrofitting 244 elements by hand.'],
                    ['title' => 'AI search quotes everyone but you', 'description' => 'Semantic HTML, question-shaped elements (FAQ, how-to, definition lists) and clean per-page metadata make your content extraction-friendly for AI Overviews, ChatGPT and Perplexity — GEO as a side effect of doing HTML properly.'],
                ],
            ]),

            // ------------------------------------- the create page on ui.shadcn.com
            self::block('desiderio_headersection', [
                'eyebrow' => 'The principle behind the whole project',
                'header' => 'From ui.shadcn.com/create to a live TYPO3 theme',
                'subheadline' => 'Desiderio is built on one radical idea: you should design your site where the best design tooling lives — and run it where your content lives. Pick a design on the create page on ui.shadcn.com. Paste one preset code into TYPO3. Done.',
                'variant' => 'center',
            ]),
            self::block('desiderio_featurealternating', [
                'header' => 'Three steps. No build pipeline. No agency invoice.',
                'subheadline' => 'The create page on ui.shadcn.com is the official theme designer of the shadcn/ui ecosystem. Desiderio mirrors its token model one to one — which makes it your TYPO3 theme editor.',
                'items' => [
                    [
                        'title' => '1 · Design on ui.shadcn.com/create',
                        'description' => 'On the shadcn/ui create page you compose a complete design system in the browser: base color and neutral palette, font pairing, border radius, and one of eight structural style recipes — Vega, Nova, Maia, Lyra, Mira, Luma, Sera, or Rhea — that define focus rings, transitions, and control shapes. Every choice compiles down to design tokens, and the whole result exports as one small preset code.',
                        'image' => self::screenshot('frontend-shadcn-create.png', 'The create page on ui.shadcn.com', 'The shadcn/ui create page on ui.shadcn.com with palette, font, and style recipe controls.', 'Screenshot of the shadcn/ui create page, the theme designer Desiderio imports presets from.'),
                        'link' => self::CREATE_URL,
                    ],
                    [
                        'title' => '2 · Paste the preset into Desiderio',
                        'description' => 'Drop the preset code into the Desiderio site settings and the entire site repaints — colors, radius, typography, control density. Desiderio speaks the same OKLCH token language as shadcn/ui, so nothing gets lost in translation. Five curated create presets and ten house presets ship ready to use; switching needs no Tailwind rebuild, no deployment, no cache anxiety.',
                        'image' => self::screenshot('backend-page-properties-theme.png', 'Per-page theme preset field', 'TYPO3 page properties with the Desiderio per-page theme preset selection.'),
                        'link' => '{{page:technical-features}}',
                    ],
                    [
                        'title' => '3 · Give every page tree its own look',
                        'description' => 'Since version 2.6 any page can carry its own preset, inherited down the whole subtree. Campaign microsite in Midnight, product pages in Marine, the blog in Forest — one TYPO3 install, one content pool, as many looks as your marketing calendar demands. This styleguide proves it: every chapter page below runs a different theme.',
                        'image' => self::screenshot('frontend-pricing-midnight-dark.png', 'Dark pricing page in the Midnight preset', 'Desiderio pricing page rendered in the dark Midnight theme preset.'),
                        'link' => '{{page:chapter-hero}}',
                    ],
                ],
            ]),
            self::block('desiderio_quote', [
                'header' => 'Why shadcn/ui, in their own words',
                'quote_text' => 'A set of beautifully designed components that you can customize, extend, and build on. Open Source. Open Code.',
                'author' => 'shadcn/ui',
                'role' => 'ui.shadcn.com',
                'variant' => 'large',
            ]),
            self::block('desiderio_howtosteps', [
                'header' => 'Your first themed site in four commands',
                'description' => 'This is the whole onboarding. No starter kit purchase, no license key, no setup wizard with nine screens.',
                'items' => [
                    [
                        'title' => 'composer require webconsulting/desiderio',
                        'content' => 'One package brings the elements, the components, and the theme layer. TYPO3 14.3+, PHP 8.3+, done.',
                        'image' => self::unsplash('desk-logan-weaver.jpg', 'Developer desk during installation', 'A tidy developer desk with a laptop, where a single composer command installs Desiderio.'),
                    ],
                    [
                        'title' => 'vendor/bin/typo3 desiderio:styleguide:seed',
                        'content' => 'Seeds 244 living examples — this very site — so you evaluate with real content instead of an empty page tree.',
                        'image' => self::screenshot('backend-page-module-hero.png', 'Seeded page in the TYPO3 page module', 'TYPO3 page module showing a seeded Desiderio hero element with backend preview.'),
                    ],
                    [
                        'title' => 'Pick a theme preset',
                        'content' => 'Choose one of 15 presets in the site settings, or design your own on the create page on ui.shadcn.com and paste the code.',
                        'image' => self::screenshot('backend-site-settings-theme.png', 'Theme preset in the TYPO3 site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown opened.'),
                    ],
                    [
                        'title' => 'Publish',
                        'content' => 'Editors build pages from the wizard, preview every element in the backend, and edit inline in the Visual Editor.',
                        'image' => self::screenshot('frontend-hero-lagoon.png', 'Published Desiderio frontend', 'The published Desiderio demo site rendered in the Lagoon theme preset.'),
                    ],
                ],
            ]),

            // ----------------------------------------------------- atomic design
            self::block('desiderio_headersection', [
                'eyebrow' => 'Architecture',
                'header' => 'Atomic design, the way it was meant to work',
                'subheadline' => 'Brad Frost wrote the book; Desiderio wired it into Fluid 5. Small parts compose into bigger parts, and nothing is ever styled twice.',
                'variant' => 'center',
            ]),
            self::block('desiderio_featurecards', [
                'eyebrow' => 'From atom to organism',
                'header' => 'One token contract from the smallest button to the biggest page',
                'subheadline' => 'Every layer is a real, typed Fluid 5 component in the d: namespace — not a naming convention, an enforced API.',
                'items' => [
                    ['title' => '17 atoms', 'description' => 'Button, Badge, Input, Icon, Avatar, Typography and friends. Each one token-only: change a preset and every atom follows. Typed f:argument contracts catch wrong usage at render time, not in production.'],
                    ['title' => '28 molecules', 'description' => 'Card, Accordion, Tabs, Table, Alert, form fields — atoms composed into reusable patterns with slots. Your custom elements get the same building blocks the 244 shipped ones use.'],
                    ['title' => '4 layout primitives', 'description' => 'Section, Container, Grid, Stack carry spacing, density, and container queries. Consistent rhythm across every element without a single hand-written margin.'],
                    ['title' => '244 organisms', 'description' => 'The content elements themselves: heroes, pricing tables, dashboards, footers. Built exclusively from the layers below — which is why an 11-category audit can verify all of them, on every commit.'],
                ],
            ]),

            // ----------------------------------------------------- speed
            self::block('desiderio_featurestats', [
                'header' => 'Fast where it counts: your site, your team, your timeline',
                'description' => 'Speed is not one number. Desiderio is engineered for three: runtime performance, editorial velocity, and project delivery time.',
                'items' => [
                    ['value' => '0', 'label' => 'Rebuilds for a redesign', 'description_text' => 'Theme switching is pure CSS tokens at runtime. No Tailwind rebuild, no deployment, no release window.'],
                    ['value' => '100%', 'label' => 'Static CSS, no JS framework', 'description_text' => 'No React, no Vue runtime in the frontend. Charts render server-side with a slim vanilla enhancement layer.'],
                    ['value' => '1', 'label' => 'Command to a full demo site', 'description_text' => 'The seeder builds 244 living examples in seconds — workspaces-safe and idempotent, run it as often as you like.'],
                    ['value' => '30', 'label' => 'Minutes from install to themed', 'description_text' => 'Composer install, seed, pick a preset, publish. Or book the creators and skip even that: installation service €890.'],
                ],
            ]),

            // ----------------------------------------------------- 244 elements
            self::block('desiderio_headersection', [
                'eyebrow' => 'The library',
                'header' => '244 content elements. Ready to use. Today.',
                'subheadline' => 'Not a UI kit you still have to assemble — finished, editor-friendly content elements with backend previews, demo fixtures, and accessibility built in.',
                'variant' => 'center',
            ]),
            self::block('desiderio_featurecards', [
                'eyebrow' => 'Ten groups, zero gaps',
                'header' => 'Whatever the page needs, the wizard already has it',
                'subheadline' => 'Every element appears in the new-content wizard with a real preview — browse the chapters of this styleguide to see all of them live, each chapter in its own theme.',
                'items' => [
                    ['title' => '21 heroes & landing intros', 'description' => 'Split, animated, countdown, video, stats, product — the first screen of every campaign, ready in minutes.'],
                    ['title' => '25 feature & benefit blocks', 'description' => 'Grids, bento layouts, comparisons, timelines, tabs. Explain any product without briefing a designer.'],
                    ['title' => '25 pricing & product elements', 'description' => 'Tier tables, calculators, sliders, order summaries — monetization patterns that usually cost a sprint.'],
                    ['title' => '29 data & dashboard elements', 'description' => 'Nine chart types, KPI cards, changelogs, status boards — all server-rendered with accessible data tables.'],
                    ['title' => '49 trust & people elements', 'description' => 'Testimonials, case studies, logo walls, team grids — social proof in every shape your sales team can dream up.'],
                    ['title' => '95 more for everything else', 'description' => 'Navigation, footers, legal, forms, editorial content. The unglamorous 80% of every site — already done.'],
                ],
            ]),

            // ------------------------------------------------------------ forms
            self::block('desiderio_featurecards', [
                'eyebrow' => 'Forms',
                'header' => 'Real forms, without the form-building',
                'subheadline' => 'Drop in a styled block — Contact, Newsletter, Demo, Booking, Callback or a gated Download — and the form inside is a genuine TYPO3 Form Framework definition. Eight ready-to-use forms, each with server-side validation, a Brevo CRM finisher, privacy-friendly Friendly Captcha, and a built-in GDPR data-request form. No form plugin to buy, no markup to hand-build.',
                'items' => [
                    ['title' => 'On brand in seconds', 'description' => 'Pick a form block and it already matches your active theme preset. No styling drift, no ticket to a developer, no third-party form widget breaking your design.'],
                    ['title' => 'Leads land in your CRM', 'description' => 'Every submission syncs to Brevo through the built-in finisher — with double opt-in on the newsletter — so there is no manual export, and marketing owns the funnel.'],
                    ['title' => 'GDPR-credible out of the box', 'description' => 'Privacy-friendly Friendly Captcha (no tracking cookies) plus a ready-made data-request form for exports, deletions and corrections. Consent copy stays editable per block.'],
                    ['title' => 'It is genuinely ext:form', 'description' => 'Standard Form Framework YAML: core validators, finishers, multi-step pages, file uploads and PSR-14 events. Nothing proprietary to learn or maintain.'],
                    ['title' => 'Behaviour and design, cleanly split', 'description' => 'The form YAML owns validation and finishers; the Content Block owns markup and CSS. Restyle a form without touching its logic, or change fields without touching a template.'],
                    ['title' => 'Less to attack, less to break', 'description' => 'Validation, CSRF, honeypot and secure email finishers come from TYPO3 core, not hand-rolled code — a far smaller attack surface than a bespoke form plugin.'],
                ],
            ]),

            // ---------------------------------------------------------- gallery
            self::block('desiderio_gallery', [
                'header' => 'Five elements worth the detour',
                'subheadline' => '244 elements is a lot to scroll — so start with these five. Real screenshots from this very site, captured live. Click any to open its category and see it in motion.',
                'columns' => '3',
                'items' => [
                    ['title' => 'Parallax Hero', 'description' => 'A scroll-driven hero — the most cinematic first impression in the set, and one of 21 hero layouts.', 'link' => '{{page:chapter-hero}}', 'image' => self::screenshot('frontend-gallery-parallax-hero.png', 'Parallax Hero element', 'Desiderio Parallax Hero content element rendered with the Forest theme preset.')],
                    ['title' => 'Bento Feature Grid', 'description' => 'Mixed-size cards in the bento layout every modern product site wants, from the 25-strong features group.', 'link' => '{{page:chapter-features}}', 'image' => self::screenshot('frontend-gallery-bento-features.png', 'Bento Feature Grid element', 'Desiderio Bento Feature Grid content element with mixed-size feature tiles.')],
                    ['title' => 'Toggle Pricing', 'description' => 'Monthly and annual pricing with a highlighted plan — the block that quietly closes deals.', 'link' => '{{page:chapter-pricing}}', 'image' => self::screenshot('frontend-gallery-toggle-pricing.png', 'Toggle Pricing element', 'Desiderio Toggle Pricing content element with a monthly and yearly billing switch.')],
                    ['title' => 'Testimonial Wall', 'description' => 'A dense masonry wall of quotes: social proof you take in at a glance.', 'link' => '{{page:chapter-social-proof}}', 'image' => self::screenshot('frontend-gallery-testimonial-wall.png', 'Testimonial Wall element', 'Desiderio Testimonial Wall content element showing a masonry grid of customer quotes.')],
                    ['title' => 'Demo Request — a real ext:form', 'description' => 'A styled lead-capture section that renders a genuine TYPO3 form, where the gallery meets the forms story.', 'link' => '{{page:chapter-conversion}}', 'image' => self::screenshot('frontend-gallery-demo-request.png', 'Demo Request element', 'Desiderio Demo Request content element rendering a real TYPO3 Form Framework form.')],
                ],
            ]),

            // ----------------------------------------------------- target groups
            self::block('desiderio_headersection', [
                'eyebrow' => 'Who it is for',
                'header' => 'Three teams, one unfair advantage',
                'subheadline' => 'Desiderio pays off differently depending on who you are. We wrote a page for each of you.',
                'variant' => 'center',
            ]),
            self::block('desiderio_usecasegrid', [
                'eyebrow' => 'Pick your story',
                'header' => 'Where Desiderio earns its keep',
                'subheadline' => 'Same package, three different superpowers.',
                'items' => [
                    [
                        'title' => 'TYPO3 agencies & integrators',
                        'description' => 'Quote less, deliver more. 244 finished elements and per-page themes turn every fixed-price project into margin — and every client review into a yes.',
                        'link' => '{{page:target-groups/for-agencies}}',
                    ],
                    [
                        'title' => 'In-house marketing & product teams',
                        'description' => 'Ship campaigns without filing a dev ticket. Editors compose pages from previews, switch themes per campaign, and stay on brand automatically.',
                        'link' => '{{page:target-groups/for-inhouse-teams}}',
                    ],
                    [
                        'title' => 'Freelancers & solo developers',
                        'description' => 'Look like a team of ten. A complete design system, quality pipeline included, for €0 — so your one-person studio ships agency-grade sites.',
                        'link' => '{{page:target-groups/for-freelancers}}',
                    ],
                ],
            ]),

            // ----------------------------------------------------- advantages
            self::block('desiderio_featurechecklist', [
                'eyebrow' => 'The advantages, in one list',
                'header' => 'Why teams pick Desiderio over building it themselves',
                'items' => [
                    ['title' => 'A redesign without the redesign budget', 'description_text' => '15 presets plus your own designs from the create page on ui.shadcn.com, switchable at runtime — per site or per page subtree.'],
                    ['title' => 'Editors who stop opening tickets', 'description_text' => 'Backend previews for all 244 elements, inline editing via Visual Editor, and a wizard organized in ten clear groups.'],
                    ['title' => 'Accessibility you can show the auditor', 'description_text' => 'WCAG 2.2-checked contrast on every preset (enforced by the build), landmarks, focus rings, and translated screen-reader labels.'],
                    ['title' => 'Quality that is measured, not promised', 'description_text' => 'PHPStan at level max, 170+ unit and functional tests on PHP 8.3/8.4, and an 11-category template audit at zero findings.'],
                    ['title' => 'Integrations already wired', 'description_text' => 'News, Powermail, Solr, Blog, Form Framework with 8 ready forms, Friendly Captcha with a dev-friendly bypass, Brevo double opt-in.'],
                    ['title' => 'Light and dark mode, both first-class', 'description_text' => 'Every preset ships a light and a dark token set with WCAG 2.2-checked contrast. Visitors get their system preference automatically; a header toggle lets them override it.'],
                    ['title' => 'A free core you can bet on', 'description_text' => 'GPL-2.0, full source on GitHub, no feature gates. Paid tiers buy speed and the creators\' time — never basic functionality.'],
                ],
            ]),

            // ----------------------------------------------------- integrations
            self::block('desiderio_headersection', [
                'eyebrow' => 'Beyond content elements',
                'header' => 'Styled templates for the extensions you already run',
                'subheadline' => 'Desiderio does not stop at its own elements: the TYPO3 extensions your site depends on get the same shadcn treatment — light and dark mode included.',
                'variant' => 'center',
            ]),
            self::block('desiderio_featurecards', [
                'eyebrow' => 'Integration template sets',
                'header' => 'One design system across your whole stack',
                'subheadline' => 'Each set follows the active theme preset automatically — switch the site to Midnight and your news list, search results, and forms switch with it.',
                'items' => [
                    ['title' => 'Solr search', 'description' => 'Complete shadcn-styled search UI for Apache Solr: results, facets, sorting, per-page switcher, suggest dropdown in the header, and accessible pagination.'],
                    ['title' => 'Powermail', 'description' => 'All field types of in2code/powermail restyled with the Desiderio form partials, six seeded demo forms with thank-you flows, and Friendly Captcha (Studio Mitte) wired in.'],
                    ['title' => 'News', 'description' => 'Teaser cards, detail views, magazine lists, and pagination for georgringer/news — with equal-height cards and proper time semantics.'],
                    ['title' => 'Blog', 'description' => 'Post lists, sidebars, and a styled comment form for t3g/blog, captcha-protected and dark-mode ready.'],
                    ['title' => 'Form Framework', 'description' => 'Eight ready form definitions — contact, newsletter, booking, downloads and more — with Brevo double-opt-in and the captcha bypass for ddev.'],
                    ['title' => 'Visual Editor & core content', 'description' => 'Inline frontend editing supported across the kit, plus shadcn overrides for the fluid-styled-content core elements.'],
                ],
            ]),

            // ----------------------------------------------------- pricing
            self::block('desiderio_pricingthreetier', [
                'eyebrow' => 'Pricing',
                'header' => 'Free forever. Faster with the creators.',
                'subheadline' => 'The package is GPL and complete at €0. Pro and Agency buy guarantees, priority, and the people who built all 244 elements. Yearly billing adds two months free — and code DESIDERIO20 takes 20% off the first year.',
                'plans' => [
                    ['name' => 'Community', 'price' => '€0', 'billing_period' => 'forever', 'description' => 'The full package under GPL-2.0 — all elements, all themes, all integrations.', 'features' => ['All 244 content elements', '15 theme presets + per-page themes', 'One-command demo seeding', 'Community support on GitHub'], 'is_recommended' => false, 'button_text' => 'Install for free', 'button_link' => self::REPO_URL],
                    ['name' => 'Pro', 'price' => '€49', 'billing_period' => 'per month · €490/year', 'description' => 'For teams shipping client or production sites on a deadline.', 'features' => ['Priority email support, 2-day response', 'Guaranteed LTS compatibility updates', 'Early access to element & preset drops', 'Minor-version upgrade assistance'], 'is_recommended' => true, 'button_text' => 'Go Pro', 'button_link' => self::REPO_URL],
                    ['name' => 'Agency', 'price' => '€149', 'billing_period' => 'per month · €1,490/year', 'description' => 'Unlimited projects and a direct line to the maintainers.', 'features' => ['Everything in Pro, unlimited projects', 'Answers within 4 business hours (CET)', 'Quarterly editor onboarding session', 'Custom preset review by the creators'], 'is_recommended' => false, 'button_text' => 'Choose Agency', 'button_link' => self::REPO_URL],
                ],
            ]),

            // ----------------------------------------------------- proof + CTA
            self::block('desiderio_testimonialgrid', [
                'eyebrow' => 'Word gets around',
                'header' => 'Teams talk about Desiderio',
                'columns' => '3',
                'testimonials' => [
                    ['quote' => 'We demoed three themes in the kickoff meeting by switching presets live. The client signed that afternoon.', 'author_name' => 'Hannah Vogel', 'author_title' => 'Lead Integrator'],
                    ['quote' => 'Our editors build campaign pages themselves now. The backend previews mean nobody publishes blind anymore.', 'author_name' => 'Jonas Klein', 'author_title' => 'Head of Digital'],
                    ['quote' => 'As a freelancer I quote design-system quality at one-person prices. Desiderio is my unfair advantage.', 'author_name' => 'Lena Hoffmann', 'author_title' => 'Freelance TYPO3 Developer'],
                ],
            ]),
            self::block('desiderio_ctabanner', [
                'header' => 'Your next TYPO3 site could look like this one',
                'description' => 'This whole demo — homepage, ten themed chapters, 244 elements — was seeded with one command. Install Desiderio for free, or have the creators set it up for €890.',
                'cta_text' => 'Get Desiderio on GitHub',
                'cta_link' => self::REPO_URL,
                'bg_style' => 'primary',
            ]),
        ];
    }

    /**
     * @return array<int, ShowcasePage>
     */
    public static function subpages(): array
    {
        return [
            self::technicalFeaturesPage(),
            self::contentTypesHubPage(),
            self::featuresOverviewPage(),
            self::featureRecordsListPage(),
            self::featureMcpServerPage(),
            self::featureEasyWorkspacePage(),
            self::featureBlogPage(),
            self::featureDesiderioPage(),
            self::featureSolrPage(),
            self::featureWorkosPage(),
            self::featurePowermailPage(),
            self::featureX402PaywallPage(),
            self::featureApiCapabilityBridgePage(),
            self::featureAgentationPage(),
            self::featureSgApicorePage(),
            self::featureSkillflowPage(),
            self::targetGroupsPage(),
            self::agencyPage(),
            self::inhousePage(),
            self::freelancerPage(),
            self::geoAiSearchPage(),
            self::typo3V14StrategyPage(),
            self::successStoriesPage(),
            self::successStoryAiLabPage(),
            self::successStorySpaceCompanyPage(),
            self::successStoryAiResearchLabPage(),
            self::successStorySpaceAgencyPage(),
            self::successStoryStreamingPlatformPage(),
            self::successStoryGameStudioPage(),
            self::successStoryToyMakerPage(),
            self::successStoryFurnitureRetailerPage(),
            self::successStoryMusicServicePage(),
            self::successStoryPaymentsCompanyPage(),
            self::successStoryLanguageAppPage(),
            self::successStoryNonprofitEncyclopediaPage(),
            self::successStoryEnergyDrinkBrandPage(),
            self::successStoryPhysicsLabPage(),
            self::successStoryAnimationStudioPage(),
            self::imprintPage(),
            self::privacyPage(),
            self::accessibilityPage(),
            self::notFoundPage(),
        ];
    }

    public static function contentElementCount(): int
    {
        $count = count(self::homeContent());
        foreach (self::subpages() as $page) {
            $count += count($page['content']);
        }

        return $count;
    }

    /**
     * @return ShowcasePage
     */
    private static function technicalFeaturesPage(): array
    {
        return [
            'title' => 'Technical features',
            'navTitle' => 'Tech facts',
            'slug' => '/technical-features',
            'abstract' => 'Every main technical feature of Desiderio in one engineering-grade list: Fluid 5.3 components, ICU translations, theme engine, CSS architecture, integrations, and the quality pipeline.',
            'description' => 'Desiderio under the hood: Fluid 5.3 typed components, a runtime OKLCH theme engine, Content Blocks 2.2, and a PHPStan-max quality pipeline — all verifiable on GitHub.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'For the people who read changelogs',
                    'header' => 'Every claim, verifiable in the repository',
                    'subheadline' => 'The marketing said "magic". This page says how. Every claim below is verifiable in the public repository on GitHub.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_contentgrid', [
                    'header' => 'The three pillars',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'Fluid 5.3 component system', 'content' => 'Forty-nine typed Fluid 5.3 components make up the d: namespace, registered through a ComponentCollection. Each one declares its arguments with f:argument contracts and composes through f:slot, so the wrong type fails loudly at render — never silently in production. Your templates get a real API, not a naming convention everyone has to remember.', 'link' => self::REPO_URL],
                        ['title' => 'Runtime theme engine', 'content' => 'Fifteen shadcn presets, each a pure set of OKLCH design tokens on body[data-shadcn-preset], switch per site and per page subtree with no rebuild and no deployment. A contrast solver computes every palette to satisfy WCAG 2.2 in both light and dark mode — and refuses to emit CSS that would fail.', 'link' => ''],
                        ['title' => 'Measured quality pipeline', 'content' => 'Nothing here is promised, everything is enforced: PHPStan at level max, 170+ unit and functional tests on PHP 8.3 and 8.4, an eleven-category template audit at zero tolerance, and a CI job that fails the build the moment the Tailwind bundle drifts from the templates.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Credit where it is due',
                    'header' => 'Two upstream projects made all of this possible',
                    'subheadline' => 'Almost nothing on this page is our invention. Desiderio is a design system layered onto two remarkable pieces of TYPO3 engineering — and the people behind them have earned a direct thank-you.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_contentgrid', [
                    'header' => 'Standing on excellent foundations',
                    'columns' => '2',
                    'items' => [
                        ['title' => 'Fluid 5 — thank you, Simon Praetorius', 'content' => 'Every one of the 49 Desiderio components is built on Fluid, and it is a genuine pleasure to work with. Simon Praetorius turned Fluid into a modern, component-based template engine where the (var-)typing is excellent: arguments are declared, checked, and the wrong type fails at render instead of in production. A fantastic template engine — we simply painted a design system on top of it.', 'link' => 'https://github.com/s2b'],
                        ['title' => 'Content Blocks — thank you, Nikita Hovratov', 'content' => 'All 244 Desiderio elements are Content Blocks, and describing one is a joy. Nikita Hovratov built a fantastic way to explain a content element to TYPO3 — a single declarative schema, automatic database columns, a backend preview — and it works flawlessly. It let us ship 244 elements instead of hand-writing TCA for each. Great work, and thank you.', 'link' => 'https://github.com/nhovratov'],
                    ],
                ]),
                self::block('desiderio_headersection', [
                    'eyebrow' => 'More gratitude',
                    'header' => 'And the rest of the stack we did not build',
                    'subheadline' => 'Fluid and Content Blocks are the foundation; the toolchain and integrations around them are just as much other people\'s excellent work. A direct thank-you to each — because none of it is ours.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'The toolchain, the integrations, and the AI layer',
                    'eyebrow' => 'More gratitude',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'shadcn/ui — shadcn', 'description' => 'The entire design language — components, tokens, the OKLCH theme model, and the create-page preset format — is shadcn/ui by shadcn. Desiderio speaks it one to one. Open Source, Open Code. Thank you.', 'link' => 'https://ui.shadcn.com'],
                        ['icon' => 'zap', 'title' => 'Tailwind CSS — Tailwind Labs', 'description' => 'Tailwind v4 with @source scanning and cascade layers compiles the whole bundle, with no runtime cost in the frontend. Thank you, Tailwind Labs.', 'link' => 'https://tailwindcss.com'],
                        ['icon' => 'message-circle', 'title' => 'The AI layer — Netresearch', 'description' => 'nr_llm, nr_mcp_agent, nr_vault and t3_cowriter supply the whole AI layer: a shared LLM foundation, a backend agent, an encrypted vault and an AI cowriter. Thank you, Netresearch DTT GmbH.', 'link' => 'https://github.com/netresearch'],
                        ['icon' => 'book-open', 'title' => 'The integration extensions', 'description' => 'The shadcn template sets wrap real extensions: Powermail by in2code, News by Georg Ringer, Blog by TYPO3 GmbH, and Apache Solr for TYPO3 by dkd. We only paint on top — thank you all.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Friendly Captcha — Studio Mitte', 'description' => 'Privacy-first, proof-of-work bot protection with no tracking, wrapped for TYPO3 by Studio Mitte. Thank you — and thanks to Friendly Captcha for the service behind it.', 'link' => 'https://friendlycaptcha.com'],
                        ['icon' => 'monitor', 'title' => 'Prism & highlight.js', 'description' => 'Code blocks are highlighted by Prism (Lea Verou and contributors), with a highlight.js autodetect fallback for unlabelled snippets. Thank you to both projects.', 'link' => 'https://prismjs.com'],
                        ['icon' => 'settings', 'title' => 'Alpine.js — Caleb Porzio', 'description' => 'The few interactive touches — dismissables and disclosures — ride on Alpine.js, a tiny, dependency-free sprinkle rather than a framework. Thank you, Caleb Porzio.', 'link' => 'https://alpinejs.dev'],
                        ['icon' => 'file', 'title' => 'Fonts & icon sets', 'description' => 'Geist by Vercel, Inter by Rasmus Andersson and JetBrains Mono by JetBrains, plus the Lucide, Phosphor, Tabler, Remix and HugeIcons icon libraries. Thank you to every maintainer.', 'link' => ''],
                        ['icon' => 'database', 'title' => 'TYPO3 CMS — the community', 'description' => 'And of course TYPO3 itself: the permissions, records, workspaces, localization and FAL that make everything above worth building on. Thank you to the whole TYPO3 community.', 'link' => 'https://typo3.org'],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'The complete technical feature list',
                    'allow_multiple' => 1,
                    'items' => [
                        [
                            'title' => 'Fluid 5.3 — typed components, slots, ICU',
                            'content' => '<p>Components declare typed <code>f:argument</code> contracts and compose via <code>f:slot</code>.</p><ul><li><strong>Typed API</strong> — string, bool, object, with defaults and optionality; wrong types fail at render.</li><li><strong>First-class namespace</strong> — d:atom.button, d:molecule.card, d:layout.section via a ComponentCollection.</li><li><strong>ICU built in</strong> — plurals and dates localise correctly, e.g. \'Page 3 of 12\'.</li></ul>',
                            'open_by_default' => 1,
                        ],
                        [
                            'title' => 'Translations — XLIFF 2.0, English + German, translated ARIA',
                            'content' => '<p>Every user-facing string runs through <code>f:translate</code> with XLIFF 2.0 catalogues.</p><ul><li><strong>Even assistive text</strong> — screen-reader labels, carousel controls, dismiss buttons, pagination.</li><li><strong>Shipped languages</strong> — English and German complete; element-local labels in five locales.</li><li><strong>Stable keys</strong> — switching icon libraries or themes never rewrites stored records.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Content Blocks 2.2 — schema-first elements with previews',
                            'content' => '<p>All 244 elements are TYPO3 Content Blocks.</p><ul><li><strong>Declarative schemas</strong> — config.yaml with automatic database columns.</li><li><strong>Backend previews</strong> — and collection child records with explicit table mappings.</li><li><strong>Seed-ready</strong> — per-element demo fixtures build this whole styleguide in one command.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Theme engine — OKLCH tokens, per-page presets, solved contrast',
                            'content' => '<p>15 presets (five from ui.shadcn.com, ten house) define the look as OKLCH custom properties.</p><ul><li><strong>Full token set</strong> — accent, radius, typography, density, focus-ring width, elevation.</li><li><strong>Solved contrast</strong> — the generator hits WCAG 2.2 (4.5:1 text, 3:1 UI) and refuses failing CSS; a unit test re-checks the bundle.</li><li><strong>Per-page</strong> — presets inherit down the rootline via TypoScript slide.</li><li><strong>Runtime switches</strong> — dark mode, five icon libraries, density, radius, and font.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'CSS architecture — Tailwind v4, container queries, BEM',
                            'content' => '<p>Tailwind v4 with <code>@source</code> scanning, cascade layers, and tw-animate.</p><ul><li><strong>Per-element BEM</strong> — element styles in BEM files concatenated by manifest.</li><li><strong>Container queries</strong> — elements respond to their actual width, not just the viewport.</li><li><strong>Zero runtime cost</strong> — no CSS-in-JS, no framework, no hydration.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Integrations — News, Powermail, Solr, Blog, Forms, Captcha, Brevo',
                            'content' => '<p>shadcn-styled template sets for georgringer/news, in2code/powermail, Apache Solr, and t3g/blog.</p><ul><li><strong>Forms</strong> — eight Form Framework definitions with a Brevo double-opt-in finisher.</li><li><strong>Friendly Captcha</strong> — real in production, auto-bypass in Development, force-real switch for DDEV.</li><li><strong>Visual Editor</strong> — inline editing supported throughout.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Seeding & CLI — idempotent, workspaces-safe demo content',
                            'content' => '<p>Three idempotent commands, each safe to re-run.</p><ul><li><strong>styleguide:seed</strong> — this site.</li><li><strong>starter:seed</strong> — a complete corporate starter.</li><li><strong>blog:seed-pages</strong> — normalises a Blog tree.</li></ul><p>Reseeding soft-deletes the previous generation; all refuse to run in a workspace or Production without a flag.</p>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Security & platform — TYPO3 14.3+, PHP 8.3+, CSP-friendly',
                            'content' => '<p>Security treated as a platform feature on TYPO3 14.3+ / PHP 8.3+.</p><ul><li><strong>Hardened queries</strong> — strict types and QueryBuilder with named parameters.</li><li><strong>CSP-ready</strong> — nonce-aware asset rendering.</li><li><strong>Safe by default</strong> — middleware that logs and refuses the captcha bypass in production; schema-filtered seeder inserts.</li></ul>',
                            'open_by_default' => 0,
                        ],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'The entire installation',
                    'language' => 'Bash',
                    'filename' => 'install.sh',
                    'code' => "composer require webconsulting/desiderio\nvendor/bin/typo3 desiderio:styleguide:seed\n# pick a theme preset in the site settings — done.",
                ]),
                self::block('desiderio_definitionlist', [
                    'header' => 'Speak Desiderio in five terms',
                    'shadcn_layout' => 'default',
                    'items' => [
                        ['term' => 'Preset', 'definition' => 'A complete design decision set — colors, radius, fonts, density — expressed as OKLCH tokens and switchable at runtime. Created on ui.shadcn.com/create or shipped as one of ten house designs.'],
                        ['term' => 'Token', 'definition' => 'A named CSS custom property (e.g. --primary, --radius) that every component consumes. Change the token, change the system.'],
                        ['term' => 'Atom / Molecule / Layout primitive', 'definition' => 'The three Fluid 5 component layers (17 + 28 + 4) that all 244 content elements are built from — typed, slotted, token-only.'],
                        ['term' => 'Content Block', 'definition' => 'A TYPO3 content element defined by a declarative schema with automatic database columns and a backend preview. Desiderio ships 244 of them.'],
                        ['term' => 'ICU MessageFormat', 'definition' => 'The localization syntax behind plurals and date patterns, used so "1 result" and "12 results" are both grammatically correct in every shipped language.'],
                    ],
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Convinced by the facts?',
                    'description' => 'Install the free package, or skip the setup entirely: the creators install and configure Desiderio on your TYPO3 for €890 — brand adaptation from €1,990.',
                    'cta_text' => 'Start with the free core',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'muted',
                ]),
            ],
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
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Target groups',
                    'header' => 'Built for three kinds of TYPO3 teams',
                    'subheadline' => 'Agencies win pitches with it, in-house teams ship campaigns with it, freelancers scale with it. Here is the short version of each story — the full pitch is one click further.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Win the pitch. Keep the margin.',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Agencies & integrators',
                    'content' => '<p><strong>Every fixed-price TYPO3 project has the same enemy: template hours — Desiderio deletes them.</strong> 244 finished, audited elements mean your quote covers content modeling and integration, not weeks of template construction.</p><p>Demo three theme presets live in the kickoff meeting, run every sub-brand from one install, and hand over a backend your clients\' editors actually enjoy. The Agency tier (€149/month, unlimited projects) adds a priority line to the maintainers — answers within 4 business hours (CET workdays).</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The full agency story',
                    'button_link' => '{{page:target-groups/for-agencies}}',
                    'media' => self::screenshot('backend-site-settings-theme.png', 'Theme switching in site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown.'),
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Ship the campaign. Skip the ticket queue.',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'In-house marketing & product teams',
                    'content' => '<p><strong>Your developers built the site once. With Desiderio, marketing runs it every day after.</strong> Backend previews for all 244 elements, a plain-language wizard, and inline editing in the Visual Editor turn campaign pages into an afternoon task — not a dev ticket.</p><p>Design tokens keep every page on brand, per-campaign themes stay curated freedom, and WCAG 2.2-checked contrast keeps the accessibility audit calm. Managed hosting from €99/month takes the servers off your plate.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The full in-house story',
                    'button_link' => '{{page:target-groups/for-inhouse-teams}}',
                    'media' => self::screenshot('backend-visual-editor.png', 'Inline editing in the Visual Editor', 'TYPO3 Visual Editor editing a Desiderio hero element inline.'),
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Look like a team of ten. Bill like one.',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Freelancers & solo developers',
                    'content' => '<p><strong>A complete design system, 244 elements, and a CI-grade quality pipeline — for exactly €0.</strong> The open-ended design-and-template phase that kills fixed-price quotes becomes an afternoon of content modelling.</p><p>Seed the styleguide on a throwaway ddev site and send the prospect a link: a living demo beats a PDF offer every single time. Pro at €49/month is the safety net when the client list grows.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The full freelancer story',
                    'button_link' => '{{page:target-groups/for-freelancers}}',
                    'media' => self::screenshot('frontend-hero-lagoon.png', 'A seeded Desiderio frontend', 'Desiderio styleguide frontend in the Lagoon theme preset.'),
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
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Target group 1 — agencies & integrators',
                    'header' => 'Win the pitch. Keep the margin.',
                    'subheadline' => 'Every fixed-price TYPO3 project has the same enemy: template hours. Desiderio deletes them. Agency tier: €149/month, unlimited projects.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Demo three designs in the kickoff meeting',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Your new sales superpower',
                    'content' => '<p><strong>Switch theme presets live while the client watches</strong> — same content, three designs, zero prep.</p><ul><li><strong>Lagoon</strong> — the calm corporate look.</li><li><strong>Midnight</strong> — the product launch.</li><li><strong>Their own design</strong> — straight from the shadcn/ui create page for the brand pitch.</li></ul><p>Set a different preset per page subtree and run every sub-brand from one install. Multi-brand used to be a budget line; now it is a dropdown.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'How theming works',
                    'button_link' => '{{page:technical-features}}',
                    'media' => self::screenshot('backend-site-settings-theme.png', 'Theme switching in site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown.'),
                ]),
                self::block('desiderio_benefitcards', [
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
                self::block('desiderio_pricingsimple', [
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
                self::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'We demoed three themes in the kickoff by switching presets live. The client signed that afternoon — and the project closed 30% under our usual template budget.',
                    'author_name' => 'Hannah Vogel',
                    'author_title' => 'Lead Integrator',
                    'author_company' => 'TYPO3 agency',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => self::portrait('portrait-hannah-vogel.jpg', 'Hannah Vogel'),
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Put Desiderio in your next pitch',
                    'description' => 'Start free on the project today — go Agency (€149/month) when the second client signs. Code DESIDERIO20 takes 20% off the first year.',
                    'cta_text' => 'Start free now',
                    'cta_link' => self::REPO_URL,
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
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Target group 2 — in-house teams',
                    'header' => 'Ship the campaign. Skip the ticket queue.',
                    'subheadline' => 'Your developers built the site once. With Desiderio, marketing runs it every day after — on brand, on time, without touching code.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'The backend your editors keep asking for',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Editing, with eyes open',
                    'content' => '<p>The backend your editors keep asking for — everything visible, nothing published blind.</p><ul><li><strong>Real previews</strong> — all 244 elements preview in the page module.</li><li><strong>Plain-language wizard</strong> — elements sorted into ten clear groups.</li><li><strong>Inline editing</strong> — the Visual Editor changes text right on the page.</li><li><strong>Per-campaign themes</strong> — a preset per page tree, while tokens make off-brand colours impossible.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'Technical details',
                    'button_link' => '{{page:technical-features}}',
                    'media' => self::screenshot('backend-visual-editor.png', 'Inline editing in the Visual Editor', 'TYPO3 Visual Editor editing a Desiderio hero element inline.'),
                ]),
                self::block('desiderio_benefitcards', [
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
                self::block('desiderio_pricingsimple', [
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
                self::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'Our editors build campaign pages themselves now. The backend previews mean nobody publishes blind anymore — and our brand team finally sleeps at night.',
                    'author_name' => 'Jonas Klein',
                    'author_title' => 'Head of Digital',
                    'author_company' => 'in-house brand team',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => self::portrait('advisor-jonas-klein.jpg', 'Jonas Klein'),
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Give marketing its independence back',
                    'description' => 'Pro at €49/month buys 2-day support and guaranteed LTS updates. Add managed hosting from €99/month and the whole stack is someone else\'s pager.',
                    'cta_text' => 'Talk to the creators',
                    'cta_link' => self::REPO_URL,
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
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Target group 3 — freelancers & solo devs',
                    'header' => 'Look like a team of ten. Bill like one.',
                    'subheadline' => 'A complete design system, 244 elements, and a CI-grade quality pipeline — for exactly €0. Your one-person studio just got an unfair advantage.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Fixed-price projects that stay profitable',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The solo math',
                    'content' => '<p><strong>The design-and-template phase is what kills a fixed-price quote</strong> — open-ended, opinion-driven, unbillable when it overruns. Desiderio closes it.</p><ul><li><strong>Pick a preset</strong> — or let the client choose on ui.shadcn.com/create.</li><li><strong>Seed the demo</strong> — then walk the client through 244 real elements, not wireframes.</li><li><strong>Afternoon, not weeks</strong> — three weeks of template work becomes an afternoon of content modelling.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'See the element library',
                    'button_link' => '{{page:chapter-hero}}',
                    'media' => self::screenshot('frontend-hero-lagoon.png', 'A seeded Desiderio frontend', 'Desiderio styleguide frontend in the Lagoon theme preset.'),
                ]),
                self::block('desiderio_benefitcards', [
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
                self::block('desiderio_pricingsimple', [
                    'header' => 'Community + Pro, in freelancer math',
                    'price' => '€0',
                    'billing_period' => 'forever · GPL-2.0',
                    'description' => 'Rebuilding this library yourself — 244 audited, accessible elements at 2–3 hours each — is 600+ hours nobody pays for. On a €10,000 fixed-price site the template phase shrinks from ~80 to ~20 hours: about €5,400 of margin back in your pocket at €90/h. Pro costs €588 a year — six and a half billable hours.',
                    'features' => [
                        ['text' => '€0 forever: all 244 elements + 49 components — a €55,000 library at freelance rates'],
                        ['text' => '~60 hours saved per fixed-price project ≈ €5,400 margin recovered (at €90/h)'],
                        ['text' => 'Pro €49/month = 6.5 billable hours a year, buys 2-day response and guaranteed LTS updates'],
                        ['text' => 'Custom elements from €390 — cheaper than five hours of your own time'],
                        ['text' => 'One-command living demo — pitch against agencies and win'],
                    ],
                    'button_text' => 'Get started free',
                    'button_link' => self::REPO_URL,
                ]),
                self::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'As a freelancer I quote design-system quality at one-person prices. Clients compare my demos with agency pitches — and I win.',
                    'author_name' => 'Lena Hoffmann',
                    'author_title' => 'Freelance TYPO3 Developer',
                    'author_company' => 'independent studio',
                    'rating' => 5,
                    'header' => '',
                    'author_image' => self::portrait('portrait-lena-hoffmann.jpg', 'Lena Hoffmann'),
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Your unfair advantage is one command away',
                    'description' => 'composer require webconsulting/desiderio — free forever under GPL-2.0. Go Pro for €49/month when the client list grows. DESIDERIO20 takes 20% off the first year.',
                    'cta_text' => 'Get started free',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function geoAiSearchPage(): array
    {
        return [
            'title' => 'GEO — visibility in AI search',
            'navTitle' => 'GEO & AI search',
            'slug' => '/geo-ai-search',
            'abstract' => 'An honest look at Generative Engine Optimization for Desiderio sites: what AI Overviews and assistant citations change, where the real chances are, where the risks lie, and what the package gives you out of the box.',
            'description' => 'GEO without the hype: how Desiderio\'s semantic markup, heading discipline, FAQ elements, and meta support prepare a TYPO3 site for AI Overviews and assistant citations.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'SEO, meet GEO',
                    'header' => 'When the search result is an answer, not a link',
                    'subheadline' => 'Google AI Overviews, ChatGPT, and Perplexity increasingly answer queries directly and cite the pages they pulled from. Generative Engine Optimization (GEO) is the craft of being the page that gets pulled — and this is what it means for a Desiderio site, without the snake oil.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_contenthighlight', [
                    'header' => 'What actually changed',
                    'content' => '<p>AI Overviews and assistant answers sit above the classic result list, and they are volatile: SEO practitioners such as Lily Ray have documented repeatedly how often Google changes which queries trigger an overview and which sources it cites. Nobody can promise you a citation. What you can control is extraction-friendliness: machines quote pages whose structure makes the answer easy to lift — clear headings, semantic markup, fast responses, and self-contained passages that answer one question each.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '',
                    'link_text' => '',
                ]),
                self::block('desiderio_featurecards', [
                    'eyebrow' => 'The chances',
                    'header' => 'Why structured sites win in generative search',
                    'subheadline' => 'Everything that helps an LLM extract your content is a property of markup and performance — exactly the layer Desiderio controls.',
                    'items' => [
                        ['title' => 'Semantic HTML, by construction', 'description' => 'Landmarks, native elements, and one logical heading hierarchy per page are baked into all 244 elements. Extractors do not have to guess where the answer starts.'],
                        ['title' => 'Question-shaped content elements', 'description' => 'FAQ, accordion, how-to steps, and definition lists map one to one onto the question-answering format generative engines assemble their responses from.'],
                        ['title' => 'Clean metadata out of the box', 'description' => 'Meta descriptions, Open Graph, and Twitter cards per page, plus schema-friendly markup — the signals engines use to title and attribute their citations.'],
                        ['title' => 'Fast, static pages', 'description' => 'No client-side rendering between a crawler and your content: server-rendered Fluid, static CSS tokens, no JS framework. What the bot fetches is what the user reads.'],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'The honest part: risks and open questions',
                    'allow_multiple' => 1,
                    'items' => [
                        [
                            'title' => 'Zero-click loss is real',
                            'content' => '<p><strong>Zero-click loss is real</strong> — when the answer shows in the overview, fewer people click through, and publishers report falling click-through rates on AI-answered queries.</p><ul><li><strong>Convert harder</strong> — make the pages that do get visited work harder.</li><li><strong>Own your channels</strong> — treat newsletters, communities, and direct traffic as first-class.</li></ul>',
                            'open_by_default' => 1,
                        ],
                        [
                            'title' => 'Attribution is uncertain',
                            'content' => '<p><strong>Attribution is uncertain</strong> — assistants cite inconsistently, sometimes paraphrase without a link, and analytics still struggle to separate AI referrals.</p><ul><li><strong>Measure what you can</strong> — referral domains like chatgpt.com and perplexity.ai.</li><li><strong>Stay skeptical</strong> — of anyone selling guaranteed AI rankings.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'AI Overviews are volatile',
                            'content' => '<p><strong>AI Overviews are volatile</strong> — which queries trigger one changes constantly; analyses like Lily Ray\'s show large swings within weeks.</p><ul><li><strong>Build for durability</strong> — invest in extraction quality, not individual snapshots.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'What Desiderio gives you out of the box',
                            'content' => '<p>GEO-readiness as a side effect of doing HTML properly.</p><ul><li><strong>Semantic structure</strong> — landmark markup and heading discipline in every element.</li><li><strong>Per-page meta</strong> — meta and Open Graph support.</li><li><strong>Question-shaped content</strong> — FAQ and how-to elements.</li><li><strong>Machine-readable</strong> — translated screen-reader labels, plus server-rendered performance.</li></ul>',
                            'open_by_default' => 0,
                        ],
                    ],
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Build pages machines can quote and humans enjoy',
                    'description' => 'The same markup discipline that earns citations earns accessibility audits and Core Web Vitals. Install Desiderio for free and get all three.',
                    'cta_text' => 'Get Desiderio on GitHub',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function typo3V14StrategyPage(): array
    {
        return [
            'title' => 'TYPO3 v14 Agentic Strategy',
            'navTitle' => 'TYPO3 v14 Strategy',
            'slug' => '/typo3-v14-strategy',
            'abstract' => 'The results of webconsulting\'s long-running TYPO3 v14 analysis: an agentic CMS platform blueprint for editors, agencies and enterprises, built on TYPO3 practice since 2002.',
            'description' => 'The results of webconsulting\'s long-running TYPO3 v14 analysis: an agentic CMS platform blueprint for editors, agencies and enterprises, built on TYPO3 practice since 2002.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'webconsulting lab results',
                    'header' => 'TYPO3 v14 Agentic Platform Strategy',
                    'subheadline' => 'After a long-running TYPO3 v14 analysis, webconsulting presents the operating model serious teams need next: one CMS for editors, developers and autonomous agents. We have worked with TYPO3 since 2002, across 25 calendar years of project practice, and this is the version-14 blueprint we would put in front of decision makers.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    'From research to a sellable platform',
                    'media-above',
                    'This is the lab result: a sellable operating model, not a trend slide.',
                    '<p>This page condenses the TYPO3 v14 strategy work already proven in the lab: Desiderio, MCP Server, sg_apicore, Skillflow, Agentation, Agent Nexus, OpenTag Bridge, Flue, the Abilities registry, llms.txt, x402, Solr, Powermail, WorkOS, workspaces and shadcn UI patterns tested against real pages, records, files, language overlays and cache behavior. The market has moved too: analysts now call the category the agentic DXP, and every major CMS vendor is racing toward it.</p><ul><li><strong>Already running</strong> - a TYPO3 stack that agents can inspect, write to and review through governed tools.</li><li><strong>Already concrete</strong> - the MCP registry exposes 51 tools (verified live), including the first one generated from the new abilities registry, and the lab speaks the wider agent-protocol family: A2UI, AG-UI, A2A, UCP and AP2.</li><li><strong>Already sellable</strong> - every chapter maps to something a client can understand: speed, control, lower delivery cost and safer automation.</li></ul><p>The cool part is that TYPO3 does not need to pretend it is a toy AI app. Its historic strengths - permissions, records, workspaces, localization, extensibility and auditability - become exactly the strengths an agentic CMS needs.</p>',
                    self::mcpStrategyImage('v14-00-analysis-results-source-803d947909f14720.png', 'webconsulting TYPO3 v14 strategy analysis table with modular platform architecture', 'webconsulting TYPO3 v14 strategy analysis table with modular platform architecture')
                ),
                self::v14StrategyTextmedia(
                    '1. Dual-audience design',
                    'media-right',
                    'One source now serves editors, APIs and agents.',
                    '<p>TYPO3 already has the hard foundation: governed content, page trees, records, file references, translations and workspaces. Desiderio adds typed, semantic Content Blocks on top, so the same element can look polished in the browser and stay clean enough for APIs, search systems and AI assistants.</p><ul><li><strong>Cool now</strong> - backend previews and frontend markup come from the same element definition.</li><li><strong>Cool for buyers</strong> - one editorial workflow can feed websites, headless views, AI search and copilots.</li><li><strong>Cool for teams</strong> - accessibility, semantic HTML and structured content improve human UX and machine extraction at the same time.</li></ul><p>This is the strongest v14 story: stop building a human website first and a machine interface later. Build one governed content surface that works for both from day one.</p>',
                    self::mcpStrategyImage('v14-01-dual-audience-source-9dd58581c0bb5a84.png', 'human editor and autonomous agent views connected to one CMS source', 'human editor and autonomous agent views connected to one CMS source')
                ),
                self::v14StrategyTextmedia(
                    '2. Identity management for agents',
                    'media-left',
                    'No shared admin logins. Every agent needs an owner, scope and trail.',
                    '<p>Agentic CMS work only becomes enterprise-ready when every automated action has an identity. TYPO3 already understands users, groups, permissions, workspaces and backend access; the lab has moved this from concept to practice with real credentials for real agents.</p><ul><li><strong>Already here</strong> - backend permissions, workspace ownership, personal access tokens, OAuth with PKCE on the MCP server, per-tool capability manifests and WorkOS-style enterprise identity surfaces.</li><li><strong>What v14 should still formalize</strong> - first-class agent users with expiry, delegation records and consent trails, so an agent identity is as auditable as an employee account.</li><li><strong>Why it sells</strong> - a client can approve a translation agent or SEO agent without handing over the whole backend.</li></ul><p>The business value is simple: automation can move faster because authority is explicit. Security stops being the blocker and becomes the reason TYPO3 is credible.</p>',
                    self::mcpStrategyImage('v14-02-agent-identity-source-c43c33de79ceada3.png', 'secure agent identity model with scoped permissions and audit trails', 'secure agent identity model with scoped permissions and audit trails')
                ),
                self::v14StrategyTextmedia(
                    '3. MCP: the right tools, not every endpoint',
                    'media-right',
                    'The live lab has 51 registered MCP tools - governed, named and inspectable.',
                    '<p>The strategy is not to expose every TYPO3 endpoint and hope an LLM behaves. The lab proves the better pattern: a deliberate MCP toolbox with narrow operations for pages, content, records, files, tables, workspaces, site settings, Solr, payments and diagnostics. MCP itself has grown up: it now lives under the Linux Foundation, and the 2026 specification adds Tasks for long-running work, Extensions and hardened authorization.</p><ul><li><strong>Current count</strong> - 51 registered tools in this lab, from <code>GetPageTree</code> and <code>ContentAudit</code> to <code>x402_stats</code> and the first tool projected from the abilities registry.</li><li><strong>Cool detail</strong> - each tool has a job name, input contract and output shape an agent can reason about.</li><li><strong>Next moves</strong> - execute the written 2026-spec adoption plan (Tasks map directly onto durable jobs, pillar 16), list the server in the official MCP registry, and generate more tools from the capability registry (pillar 19) instead of maintaining them by hand.</li></ul><p>The number matters less than the discipline: tools should be useful enough to ship work and small enough to govern. That is where TYPO3 can beat generic automation platforms.</p>',
                    self::mcpStrategyImage('v14-03-mcp-tools-source-7c8f3c4124bab359.png', 'compact governed MCP toolbox connected to a CMS core', 'compact governed MCP toolbox connected to a CMS core')
                ),
                self::v14StrategyTextmedia(
                    '4. Agentic skills',
                    'media-left',
                    'Turn senior TYPO3 delivery into reusable, versioned workflows.',
                    '<p>Prompts are not a process. Skills are. The lab already shows how TYPO3 know-how can become reusable instructions: create a landing page, review accessibility, rewrite metadata, check a workspace, import content, prepare translations or validate a launch.</p><ul><li><strong>Already here</strong> - Skillflow manages SKILL.md-style instructions as TYPO3 records, syncs them from repositories and makes them searchable.</li><li><strong>Cool detail</strong> - skills can be assigned to workspace stages so review becomes part of the editorial pipeline.</li><li><strong>Why it sells</strong> - agencies can package their best delivery practice instead of depending on whoever remembers the checklist.</li></ul><p>This is the product layer above AI tooling: not one-off answers, but repeatable TYPO3 work that gets better over time.</p>',
                    self::mcpStrategyImage('v14-04-agentic-skills-source-e4920a8a296a4350.png', 'reusable agentic skill library for repeatable TYPO3 workflows', 'reusable agentic skill library for repeatable TYPO3 workflows')
                ),
                self::v14StrategyTextmedia(
                    '5. LLM-agnostic libraries',
                    'media-right',
                    'Use the best model for the job without rewiring the CMS.',
                    '<p>The model market will keep moving. TYPO3 projects need an architecture that can move with it: shared interfaces for generation, embeddings, tool use, logging, cost metadata and provider configuration, instead of hard-coding one vendor into every feature.</p><ul><li><strong>Already in the lab</strong> - Netresearch\'s <strong>nr_llm</strong> is exactly this layer, installed and running: one shared AI/LLM foundation for TYPO3 with centralized provider management, encrypted API keys and ready-made services for chat, translation, vision and embeddings.</li><li><strong>Secrets handled properly</strong> - their <strong>nr_vault</strong> keeps provider credentials and tokens under envelope encryption with access control and an audit log, so keys never sit in plain configuration.</li><li><strong>Buyer benefit</strong> - procurement keeps leverage on price, data residency and compliance because the CMS integration stays stable while models are swapped underneath it.</li></ul><p>The sellable message is freedom: TYPO3 owns the workflow; models are replaceable engines behind it — and that swap layer is a real, installed extension, not a promise.</p>',
                    self::mcpStrategyImage('v14-05-llm-agnostic-source-8ce0578695dbc362.png', 'LLM-agnostic AI adapter connecting TYPO3 to multiple model engines', 'LLM-agnostic AI adapter connecting TYPO3 to multiple model engines')
                ),
                self::v14StrategyTextmedia(
                    '6. Token billing and usage economics',
                    'media-left',
                    'AI becomes a product when usage is visible, priced and capped.',
                    '<p>Every serious AI feature creates variable cost - and machine readership is becoming revenue. The lab already has the mindset and pieces for commercial control: x402 payment flows, usage-oriented tools, transaction stats and a platform pattern where costly operations are measured instead of hidden. The payment rails themselves stay plural: x402 has the most production traction, while AP2, Stripe\'s agentic sessions and the retail checkout protocols compete - so the metering core matters more than any single rail.</p><ul><li><strong>Already here</strong> - x402 page and API gating, payment statistics, AP2 mandate demos in Agent Nexus, and MCP tools for paid-content visibility.</li><li><strong>What v14 can add</strong> - token metering per user, model, task, workspace and customer account - plus the inbound direction: charging AI crawlers for content the way Cloudflare\'s pay-per-crawl now normalizes.</li><li><strong>Why it sells</strong> - agencies can price AI-assisted translation, enrichment, QA and migration as visible services, and publishers can turn lost clicks into licensed machine access.</li></ul><p>This turns AI from an unpredictable cost center into a managed revenue line. Buyers understand budgets; they do not want a mystery invoice from a model provider.</p>',
                    self::mcpStrategyImage('v14-06-token-billing-source-ba738535fee79b21.png', 'AI token billing meters with budget controls and usage flows', 'AI token billing meters with budget controls and usage flows')
                ),
                self::v14StrategyTextmedia(
                    '7. CLI: deterministic, agent-friendly and powerful',
                    'media-above',
                    'Agents trust commands that validate input and return stable output.',
                    '<p>Browser automation is fragile. Deterministic TYPO3 commands are not. The lab already uses CLI surfaces for seeding, diagnostics and MCP inspection; the MCP tool list itself can be exported as JSON and counted without touching the backend UI.</p><ul><li><strong>Already here</strong> - commands for styleguide seeding, MCP tool inspection, Solr indexing, Skillflow sync and operational checks.</li><li><strong>Cool detail</strong> - JSON output, clear exit codes and idempotent operations make agent work reviewable by humans.</li><li><strong>v14 opportunity</strong> - promote CLI as an official automation surface for content, schema, cache, workspace and health operations.</li></ul><p>This is not glamorous, but it is what makes autonomous work reliable. A command that can be repeated, logged and rolled back is a product feature.</p>',
                    self::mcpStrategyImage('v14-07-deterministic-cli-source-24312cffc0f95704.png', 'deterministic command console for TYPO3 automation and agent operations', 'deterministic command console for TYPO3 automation and agent operations')
                ),
                self::block('desiderio_codeblock', [
                    'header' => 'Proof point: CLI output an agent can trust',
                    'code' => "# Describe the installation as structured JSON\n$ typo3 describe --format-json\n{\n  \"site\": \"example\",\n  \"typo3_version\": \"14.3.2\",\n  \"extensions\": [ ... ],\n  \"sites\": [ ... ],\n  \"languages\": [ ... ],\n  \"workspaces\": [ ... ]\n}\n\n# Agent-ready operating commands\n$ typo3 schema:export      # full TCA and Content Blocks schema\n$ typo3 content:apply      # idempotent content changes from a file\n$ typo3 site:diagnose      # health and misconfiguration report\n$ typo3 workspace:preview  # shareable preview of staged changes",
                    'language' => 'bash',
                    'filename' => 'typo3-agent-ops.sh',
                ]),
                self::v14StrategyTextmedia(
                    '8. Backend: headless by design',
                    'media-right',
                    'Editors keep the UI. Agents get the same backend as programmable operations.',
                    '<p>The TYPO3 backend should remain the place where editors understand and control the site. The v14 shift is underneath it: page trees, content elements, files, forms, workspaces and records need typed operations that do not depend on screen scraping.</p><ul><li><strong>Already here</strong> - Content Blocks, backend previews, Visual Editor support, workspaces and command-oriented extension surfaces.</li><li><strong>Cool detail</strong> - Agentation can point at a real page element and carry selector-level feedback into an AI workflow.</li><li><strong>Buyer benefit</strong> - human approval and programmable delivery can share the same content model.</li></ul><p>That is the difference between AI bolted onto a CMS and a CMS ready for agentic operations. The UI stays useful because the platform below it becomes stronger.</p>',
                    self::mcpStrategyImage('v14-08-headless-backend-source-d0146a4caa5464a8.png', 'programmable headless TYPO3 backend with structured API lanes', 'programmable headless TYPO3 backend with structured API lanes')
                ),
                self::v14StrategyTextmedia(
                    '9. APIs like tRPC, built for PHP realities',
                    'media-left',
                    'One typed procedure can serve PHP, JavaScript, REST, MCP and external clients.',
                    '<p>The lab already has the core idea in sg_apicore: define operations with attributes, generate OpenAPI documentation, enforce scopes and expose selected capabilities through structured interfaces. The capability-manifest extension adds the policy side: which operation may run where, at what risk tier.</p><ul><li><strong>Already here</strong> - attribute-driven routes, OpenAPI docs, token/session auth, scopes, auto-CRUD resources and capability manifests with a policy checker.</li><li><strong>Cool detail</strong> - the same business operation can be documented for developers and exposed safely to agents.</li><li><strong>Why it sells</strong> - fewer duplicate controllers, fewer inconsistent integrations and less hidden logic in backend modules.</li></ul><p>TYPO3 does not need to become a Node app to learn from tRPC. It needs one contract for an operation and several safe ways to call it - which is exactly the capability-registry bet pillar 19 doubles down on.</p>',
                    self::mcpStrategyImage('v14-09-typed-php-api-source-d6f9236e51205bc3.png', 'typed PHP procedure contracts connected to web, mobile and agent clients', 'typed PHP procedure contracts connected to web, mobile and agent clients')
                ),
                self::v14StrategyTextmedia(
                    '10. Simple interfaces for AI work',
                    'media-right',
                    'Point at the issue. Capture the context. Let the agent work with precision.',
                    '<p>The best AI interface for CMS work is often not a chat field. It is a visual annotation on the actual page: this headline is weak, this spacing is off, this component needs alt text, this legal block must not change.</p><ul><li><strong>Already here</strong> - Agentation captures page-element feedback with selector and context for tools such as Claude Code, Cursor or MCP agents.</li><li><strong>Cool detail</strong> - the system can carry DOM context, styles, page URL and human intent together.</li><li><strong>Buyer benefit</strong> - editors stay in the page, while technical agents receive the exact context they need to act.</li></ul><p>This removes the worst part of AI collaboration: explaining where the problem is. TYPO3 can turn editorial feedback into executable context.</p>',
                    self::mcpStrategyImage('v14-10-ai-feedback-source-a886d8e44543124f.png', 'in-context AI feedback interface with annotations on a web page preview', 'in-context AI feedback interface with annotations on a web page preview')
                ),
                self::v14StrategyTextmedia(
                    '11. MCP-based chatbot and editorial assistant',
                    'media-above',
                    'Chat is useful when it calls the same governed tools as everything else.',
                    '<p>A CMS assistant becomes serious when it is not a parallel system. The lab runs Netresearch\'s <strong>nr_mcp_agent</strong> — an AI chat assistant that lives in the TYPO3 backend and talks to the same MCP toolbox everything else uses — so chat becomes one interface to the same page, content, file, workspace and diagnostic tools other agents call.</p><ul><li><strong>Already here</strong> - nr_mcp_agent, built on nr_llm and the MCP server, reads pages, imports content, writes records, attaches media and reviews workspaces.</li><li><strong>Cool detail</strong> - an editor can ask for a summary or draft while the platform still respects permissions and preview workflows.</li><li><strong>Why it sells</strong> - chat feels fast, but governance stays in TYPO3 instead of disappearing into a black-box assistant.</li></ul><p>The assistant should not be magic. It should be a friendly command surface over a controlled CMS operating layer.</p>',
                    self::mcpStrategyImage('v14-11-mcp-chatbot-source-4b1f82b53165e55e.png', 'MCP-based editorial assistant connected to governed CMS tools', 'MCP-based editorial assistant connected to governed CMS tools')
                ),
                self::v14StrategyTextmedia(
                    '12. AI-optimized codebase',
                    'media-left',
                    'Code that agents can understand is code teams can maintain.',
                    '<p>The Desiderio codebase already demonstrates the discipline v14 needs: typed Fluid components, focused data classes, Content Block definitions, tests, predictable templates and shadcn-style design tokens. That makes the system easier for humans and LLMs to inspect.</p><ul><li><strong>Already here</strong> - typed template arguments, reusable components, unit tests, accessibility checks and deterministic seed data.</li><li><strong>Cool detail</strong> - the element library is structured enough for agents to select the right block instead of guessing from screenshots.</li><li><strong>Buyer benefit</strong> - cleaner architecture lowers the cost of upgrades, automation and future feature work.</li></ul><p>The strategic point is blunt: messy extensions can be prompted. Clear extensions can be operated, tested and sold.</p>',
                    self::mcpStrategyImage('v14-12-ai-codebase-source-5f36db22835849de.png', 'AI-optimized TYPO3 codebase shown as maintainable architecture modules', 'AI-optimized TYPO3 codebase shown as maintainable architecture modules')
                ),
                self::v14StrategyTextmedia(
                    'Installed in this lab: the TYPO3 AI stack already running',
                    'media-right',
                    'This is not hypothetical: the pieces are already visible in the lab.',
                    '<p>The lab already combines the pieces a serious agentic TYPO3 story needs: the Desiderio design system, content and media operations, MCP tools, API capability patterns, Skillflow, Agentation, x402 payment flows, search, forms, enterprise auth concepts and workspace-based review — with the AI layer resting on Netresearch\'s nr_llm, nr_mcp_agent, nr_vault and t3_cowriter. The newest wave widens the story from tools to protocols and channels: <strong>Agent Nexus</strong> (A2UI, AG-UI, A2A, UCP and AP2 playgrounds plus frontend plugins), <strong>OpenTag Bridge</strong> (steer TYPO3 from Slack with approvals and an audit ledger), <strong>Flue</strong> (a durable agent runtime with TYPO3 as control plane), <strong>Abilities</strong> (the typed capability registry with policy gate, traces and MCP projection), <strong>llms.txt</strong> (machine-readable site surfaces per site), a capability manifest, a DOCX media editor, modern Records views and a Vercel deployment starter.</p><ul><li><strong>Demo value</strong> - clients can see real content, real backend modules and real operations instead of abstract architecture diagrams.</li><li><strong>Implementation value</strong> - the stack uses TYPO3 concepts: pages, records, Content Blocks, FAL, site settings and workspaces.</li><li><strong>Roadmap value</strong> - what is already installed makes the v14 platform direction concrete enough to sell and refine.</li></ul><p>This matters commercially. Buyers do not need another AI keynote. They need to see which parts can be installed, governed, extended and connected to actual editorial work.</p>',
                    self::mcpStrategyImage('v14-13-installed-stack-source-d2f467651a18dbc9.png', 'Installed TYPO3 AI lab stack', 'Installed TYPO3 AI lab stack with LLM, MCP, vault and monitoring modules.')
                ),
                self::v14StrategyTextmedia(
                    'Built on Netresearch\'s AI foundation',
                    'media-above',
                    'Credit where it is due — four open-source extensions carry the AI layer.',
                    '<p>The AI layer of this lab runs on four open-source extensions by Netresearch DTT GmbH: <strong>nr_llm</strong> (the shared LLM foundation), <strong>nr_mcp_agent</strong> (the backend assistant), <strong>nr_vault</strong> (encrypted secrets) and <strong>t3_cowriter</strong> (an AI writing partner for editors). Thank you.</p>'
                ),
                self::v14StrategyTextmedia(
                    'The full MCP toolbox',
                    'media-above',
                    '51 registered tools prove the operating surface is real.',
                    '<p>The current lab registry exposes 51 MCP tools. They cover the daily operating lanes TYPO3 projects actually need: pages, content, records, file handling, table schemas, language-aware imports, site settings, workspaces, logs, Solr, paid content and x402 payments.</p><ul><li><strong>Content lane</strong> - <code>GetPage</code>, <code>GetPageTree</code>, <code>ImportContent</code>, <code>BulkWrite</code>, <code>AttachImage</code> and <code>RenderRecord</code>.</li><li><strong>Operations lane</strong> - <code>WorkspaceReview</code>, <code>PublishWorkspace</code>, <code>RollbackWorkspace</code>, <code>GetSystemLog</code>, <code>SafeCli</code> and <code>SolrIndexQueue</code>.</li><li><strong>Commercial lane</strong> - <code>GetPaidContent</code>, <code>GetPaymentStats</code>, <code>x402_stats</code>, <code>x402_transactions</code> and related probe tools.</li></ul><p>The cool part is not the raw count. It is that TYPO3 work becomes a named toolbox: inspectable, permissionable and teachable to agents.</p>',
                    self::mcpStrategyImage('v14-14-full-toolbox-source-ec3c4504e7475b2a.png', 'complete MCP toolbox matrix for structured TYPO3 content operations', 'complete MCP toolbox matrix for structured TYPO3 content operations')
                ),
                self::v14StrategyTextmedia(
                    'How the MCP stays secure',
                    'media-above',
                    'Powerful tools need deliberate fences, not wishful thinking.',
                    '<p>The MCP surface can create pages, write records, upload files and affect caches, so it must be treated like an operations interface. The lab keeps that security stance explicit and implemented: OAuth with PKCE and personal access tokens for authentication, capability manifests that decide which tool may run in which environment, FAL-bounded file tooling and human approval for risky work.</p><ul><li><strong>Already sensible</strong> - file tooling is designed around FAL and controlled storage, not arbitrary server paths; the capability manifest is enforced at runtime, not documented and forgotten.</li><li><strong>Governance pattern</strong> - read tools, write tools, publish tools and payment tools deserve different scopes and review rules.</li><li><strong>Buyer confidence</strong> - webconsulting can define which tools are allowed in local dev, staging, production and managed operations.</li></ul><p>This is where TYPO3 has a natural advantage: it already thinks in permissions, records and workflows. MCP should amplify that discipline, not bypass it.</p>'
                ),
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Agentic readiness beyond the demo',
                    'header' => 'The operating layer the analysis exposed',
                    'subheadline' => 'The first twelve pillars make TYPO3 understandable to agents. The deeper conclusion from the lab is that serious customers will also ask how agent work is observed, governed, resumed and reviewed. That is the layer that turns AI from a feature into a platform.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '13. AgentOps: traces, evals and rollback',
                    'media-right',
                    'Autonomy needs traces, evals, cost visibility and rollback.',
                    '<p>A production CMS cannot trust an agent because one demo looked good. Every meaningful run needs evidence: prompt, retrieved context, selected tool, input payload, output, changed records, cost, reviewer, result and rollback path.</p><ul><li><strong>Already available signals</strong> - TYPO3 logs, workspace history, DataHandler records, MCP tool outputs, x402/payment stats and Skillflow run reports with verdict and score.</li><li><strong>First bricks laid</strong> - OpenTag Bridge already writes a full audit ledger for every channel-agent run, and the abilities registry traces every execution attempt including denials; the v14 layer unifies those lanes into one trace store with eval sets and regression checks across all agents.</li><li><strong>Buyer benefit</strong> - teams can prove where automation saves time, where it fails and which tasks are safe to scale.</li></ul><p>This is the difference between demo automation and managed automation. The platform should know what happened, why it happened and how to undo it.</p>',
                    self::mcpStrategyImage('14-agentops-source-dd06b2ff43dcd398.png', 'AgentOps control room', 'Agent operations control room with evaluation checkpoints, traces, cost meters and approval signals for autonomous TYPO3 workflows.')
                ),
                self::v14StrategyTextmedia(
                    '14. Context fabric: trusted knowledge, not random retrieval',
                    'media-right',
                    'TYPO3 already holds the knowledge agents need. Package it safely.',
                    '<p>TYPO3 installations are full of useful context: page trees, records, TCA schemas, file metadata, redirects, language overlays, access rules, forms, extension settings and editorial history. The v14 opportunity is to turn that into a permission-aware context fabric. Honest status: this is the pillar with the least code behind it - and TYPO3\'s own SEAL search abstraction is the natural vehicle to build it on.</p><ul><li><strong>Already here</strong> - structured records, FAL metadata, Solr indexing, table schemas, page routing and workspace overlays.</li><li><strong>The build ahead</strong> - a semantic index over records, files and history that respects permissions at query time, so agents receive source-backed context instead of random retrieval snippets.</li><li><strong>Buyer benefit</strong> - legacy TYPO3 knowledge becomes reusable AI infrastructure, not tribal memory trapped in old projects.</li></ul><p>The quality of agent work depends on context quality. TYPO3 can make context trustworthy because it already knows where content lives, who may see it and how it relates.</p>',
                    self::mcpStrategyImage('15-context-fabric-source-3e3720be00f77da1.png', 'Context fabric knowledge graph', 'Permission-aware context fabric connecting CMS pages, records, files, vector search, provenance and access controls.')
                ),
                self::v14StrategyTextmedia(
                    '15. Governance: policy, consent and human review',
                    'media-right',
                    'Let agents move fast inside explicit boundaries.',
                    '<p>Identity answers who may act. Governance answers when the system may act without a person, when it must ask, and what proof is required before publication. TYPO3 already has workspaces, review flows, permissions, language controls and audit-friendly records to build on.</p><ul><li><strong>Already strong</strong> - workspace review, page permissions, backend roles, content history and controlled publishing.</li><li><strong>First bricks laid</strong> - OpenTag Bridge runs every channel-agent action through a policy gate and a human approve tap, and the capability manifest enforces deny/review rules per tool; the v14 layer promotes those policies from configuration files to governed TYPO3 records with risk tiers.</li><li><strong>Buyer benefit</strong> - a well-governed installation can automate more, not less, because the boundaries are explicit.</li></ul><p>This is the part buyers will care about after the first AI excitement fades. Speed is easy to promise; controlled speed is what enterprises buy.</p>',
                    self::mcpStrategyImage('16-governance-source-04c1d1026572f360.png', 'Governance approval gates', 'Human governance workflow with risk tiers, approval gates, policy cards, consent checkpoints and audit trail controls.')
                ),
                self::v14StrategyTextmedia(
                    '16. Durable runtime: jobs that survive real life',
                    'media-right',
                    'Real agent work needs state, queues, retries, approvals and handoffs.',
                    '<p>Useful agent tasks rarely finish in one chat reply. They import files, enrich records, wait for approval, retry failed services, create workspace previews, pause for legal review and resume after feedback. TYPO3 v14 needs a durable runtime for that work.</p><ul><li><strong>Already useful pieces</strong> - CLI commands, Scheduler patterns, workspace previews, Skillflow run records and DataHandler-backed changes.</li><li><strong>First brick laid</strong> - the Flue bridge runs a real durable agent runtime with TYPO3 as control plane: skills exported, MCP tools connected, runs mirrored back into the backend - and the 2026 MCP spec adds Tasks as the standard shape for exactly this work.</li><li><strong>Buyer benefit</strong> - migrations, launches, localization and bulk QA become managed workflows instead of fragile prompt sessions.</li></ul><p>The platform wins when long-running work can survive real life: failures, waiting, humans, approvals and Monday morning handoffs.</p>',
                    self::mcpStrategyImage('17-durable-runtime-source-620da710d6dd8b1f.png', 'Durable agent runtime', 'Durable agent runtime with queues, retries, workflow lanes, state hub and handoff stations for autonomous TYPO3 jobs.')
                ),
                self::block('desiderio_headersection', [
                    'eyebrow' => 'The third layer',
                    'header' => 'The agentic web: where the strategy goes next',
                    'subheadline' => 'The first twelve pillars made TYPO3 operable by agents. Pillars 13 to 16 made that operation manageable. The 2026 analysis adds the outward-facing layer: an installation that speaks the agent protocols, publishes machine-readable surfaces, proves provenance, stays sovereign - and shapes the TYPO3 ecosystem instead of waiting for it.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '17. Agent protocols beyond MCP: A2UI, AG-UI, A2A, UCP and AP2',
                    'media-above',
                    'MCP connects tools. The agentic web also needs UI, delegation and commerce lanes.',
                    '<p>Browser agents went mainstream in 2026: sites are now visited by software that wants to ask, delegate and buy. MCP covers the tool lane; the wider protocol family covers the rest - agent-to-UI (A2UI), agent-to-user streaming with approval gates (AG-UI), agent-to-agent delegation (A2A), agent-to-merchant discovery (UCP) and signed payment mandates (AP2).</p><ul><li><strong>Already here</strong> - Agent Nexus demos all five protocols in this lab: a backend field guide, five playgrounds and five frontend plugins, with human authorization gates on every write and every payment.</li><li><strong>Cool detail</strong> - every run is provenance-labelled: a real model answer via nr_llm is marked as live, the deterministic fallback as scripted, so nobody mistakes a demo for autonomy.</li><li><strong>Why it sells</strong> - TYPO3 becomes a CMS that can receive agents - inquiries, delegated tasks, shopping carts - instead of merely hosting content they scrape.</li></ul><p>The strategy is not to bet on one protocol. It is to keep the governed core and treat protocols as replaceable adapters - the same discipline that made the MCP toolbox work.</p>'
                ),
                self::v14StrategyTextmedia(
                    '18. Channel operations: steer TYPO3 from Slack and Co.',
                    'media-above',
                    'Editors delegate from the tools they already live in - with an approve tap.',
                    '<p>An editor writes "draft a teaser about the summer opening on page 12" in a Slack thread. TYPO3 drafts the copy, replies with a summary, and publishes only after an explicit approve tap. OpenTag Bridge makes this real: the LLM, every tool, all permissions, budgets, the human gate and the audit ledger live inside TYPO3 - the chat connector is a thin, swappable sidecar that holds no secrets.</p><ul><li><strong>Already here</strong> - a full guarded pipeline per action: token guard, rate limiter, identity mapping to real backend users, policy gate, human approval and a ledger entry.</li><li><strong>Cool detail</strong> - the same pattern extends to Discord, Telegram or Teams, because the agent backend is TYPO3, not the messenger.</li><li><strong>Why it sells</strong> - off-the-shelf AI bots are per-seat, cloud-hosted and blind to the CMS; this is the opposite: your model, your permissions, your audit log.</li></ul><p>Channel operations are where agentic CMS work meets editors where they actually are. The governance built in pillars 13 and 15 is what makes that safe enough to sell.</p>'
                ),
                self::v14StrategyTextmedia(
                    '19. A capability registry, not hand-rolled endpoints',
                    'media-above',
                    'One typed registry of what the CMS can do - now shipped in this lab as working code.',
                    '<p>WordPress proved the architecture lesson of 2026: its core Abilities API is one typed, permissioned registry of what the CMS can do, and the official MCP adapter simply projects it. This lab now ships that bet: the abilities registry declares each capability once - name, JSON-Schema contract, scopes, risk tier and side effects - and runs every call through one governed pipeline of policy gate, validation, scopes and permissions.</p><ul><li><strong>Already here</strong> - abilities as PHP attributes, a deny/review policy with human-in-the-loop, CLI and MCP projections: the first registry-generated tool already sits in the live MCP tool list beside the native ones.</li><li><strong>Cool detail</strong> - governance attaches to the capability, not the transport: a deny rule or review requirement follows the operation into every protocol automatically, and every execution attempt, including denials, lands in a trace table.</li><li><strong>Why it sells</strong> - clients stop paying for the same integration five times, and audits cover one registry instead of five surfaces.</li></ul><p>Protocols will keep churning; capabilities will not. Next: the REST projection, policy records in TCA - and proposing the pattern to the TYPO3 AI initiative (pillar 23).</p>'
                ),
                self::v14StrategyTextmedia(
                    '20. The machine-readable, monetizable site',
                    'media-above',
                    'The zero-click web is measured reality. This lab now publishes for machines by default.',
                    '<p>AI answer engines cut outbound clicks by roughly forty percent where they appear, and infrastructure providers now meter AI crawlers by default. The response is not to hide but to publish deliberately: structured surfaces for machines, and a price tag where content has value.</p><ul><li><strong>Already here</strong> - schema.org JSON-LD, semantic markup, question-shaped content elements, x402 gating - and llms.txt plus agents.md, generated per site from the page tree, the way large commerce platforms now ship them by default.</li><li><strong>Cool detail</strong> - the files list only visible, indexable pages, and agents.md advertises the MCP endpoint, the abilities registry and the payment lane, each detected at runtime; a site can opt out with one setting.</li><li><strong>Why it sells</strong> - publishers turn lost clicks into licensed machine access, and marketing teams get cited by AI answers instead of silently summarized.</li></ul><p>Dual-audience design (pillar 1) was the inward half of this story. The outward half is live in this lab: a site agents can discover, quote, attribute - and pay for. Next: pair crawler tiers with the x402 lane.</p>'
                ),
                self::v14StrategyTextmedia(
                    '21. Trust, provenance and AI-Act compliance',
                    'media-above',
                    'From August 2026, AI transparency is law in the EU - and the CMS is where it gets done.',
                    '<p>Article 50 of the EU AI Act applies from August 2026: users must know when they interact with AI, and generative output needs machine-readable marking, with penalties reaching into the millions. The CMS is exactly where those duties become practical - and TYPO3\'s file and record architecture is the natural home for them.</p><ul><li><strong>The build ahead</strong> - C2PA Content Credentials read, verified and preserved in FAL metadata; AI-disclosure patterns for assistants and generated content; marking wired into the AI pipelines themselves.</li><li><strong>Already in the lab</strong> - inbound media forensics that analyze images, video and audio for manipulation, and an agent audit trail (pillar 13) that doubles as the compliance log.</li><li><strong>Why it sells</strong> - compliance stops being a scramble and becomes a recurring service line: provenance audits, disclosure reviews and accessibility checks under the same contract.</li></ul><p>Trust is the product here. A CMS that can prove where its content came from - human or machine - is worth more than one that merely publishes fast.</p>'
                ),
                self::v14StrategyTextmedia(
                    '22. European sovereignty as a product',
                    'media-above',
                    'Your models, your audit log, your data - a positioning US SaaS cannot copy.',
                    '<p>The consolidation wave is real: the biggest independent headless CMS now belongs to a US hyperscaler\'s agent platform. Meanwhile the EU Data Act pushes switchability and safeguards against non-EU government access. For European enterprises, where the agentic CMS runs is no longer a detail - it is procurement policy.</p><ul><li><strong>Already here</strong> - the whole lab stack self-hosts: TYPO3, the MCP server, the agent bridges, the audit ledger, and an LLM layer (nr_llm plus nr_vault) that swaps providers or goes local without rewiring the CMS - documented as a full reference architecture with three trust tiers, from own inference to EU APIs to opted-in non-EU models.</li><li><strong>Cool detail</strong> - model choice becomes a configuration decision per task: premium models for high-value content, EU-hosted or local models for sensitive workflows.</li><li><strong>Why it sells</strong> - "agentic CMS on EU-sovereign infrastructure" wins exactly the deals where US SaaS platforms are disqualified before the demo.</li></ul><p>TYPO3\'s European DNA used to be a footnote. In the agentic era it becomes a moat: open source, self-hosted, auditable - and now agent-ready.</p>'
                ),
                self::v14StrategyTextmedia(
                    '23. Standardize and upstream: shape the TYPO3 AI initiative',
                    'media-above',
                    'A lead is worth most as ecosystem leadership, not as a private fork.',
                    '<p>Drupal institutionalized its AI push with dozens of backing organizations and a public roadmap. TYPO3\'s official AI initiative is younger and still interface-stage - which is the opportunity: the patterns proven in this lab can become the de-facto TYPO3 standard if they are contributed now, while the field is open.</p><ul><li><strong>What to upstream</strong> - the MCP server patterns, the capability-registry proposal, workspace-staged agent writes and the skills format, offered to the initiative as working reference implementations.</li><li><strong>Cool detail</strong> - the lab\'s head start over core is roughly a release cycle; contributed early, it compounds into ecosystem position instead of eroding into parity.</li><li><strong>Why it sells</strong> - clients buy the agency that wrote the standard, and the whole TYPO3 ecosystem gets an answer to the agentic DXP wave.</li></ul><p>The endgame is not a secret toolbox. It is TYPO3 competing credibly in the agentic era - with webconsulting\'s patterns at the center of how it does.</p>'
                ),
                self::v14StrategyTextmedia(
                    'Readiness check for TYPO3 v14+',
                    'media-right',
                    'Use these questions to qualify a real agentic CMS project.',
                    '<p>A TYPO3 installation is ready for agentic work when it can answer these questions without hand-waving:</p><ul><li>Which agent changed which record, with which permission, and why?</li><li>Can a failed content operation be rolled back or replayed safely?</li><li>Does the agent receive context that is current, scoped and source-backed?</li><li>Can humans review high-risk changes before publication?</li><li>Are AI costs visible enough to price, cap and report?</li><li>Can long-running work pause, resume and hand off between people and systems?</li><li>Does the site disclose AI interactions and mark generated output the way Article 50 demands?</li><li>Can media provenance be proven and manipulation be detected?</li><li>Can an external agent discover what this site offers - and can you charge it for access?</li><li>Which agent protocols can the installation speak beyond MCP?</li><li>Can the whole stack run on EU-sovereign infrastructure with exchangeable models?</li></ul><p>The cool part is that TYPO3 already has many of the primitives: records, roles, workspaces, files, logs, scheduler patterns and extension APIs. The v14 opportunity is to connect them into one operating model and sell it as controlled automation, not AI decoration.</p>'
                ),
                self::v14StrategyTextmedia(
                    'Conclusion: the platform webconsulting would sell',
                    'media-above',
                    'TYPO3 can become the governed operating system for content and agents.',
                    '<p>The platform webconsulting would sell is not a CMS with a few AI buttons. It is a governed operating layer where editors, APIs and agents work from the same content model, with clear permissions, structured tools, workspace review and measurable economics - at the exact moment the market renames the category to agentic DXP and buyers, past the first hype, reward governance and punish decoration.</p><ul><li><strong>For agencies</strong> - strategy, implementation, migration, compliance and managed AI operations become billable packages.</li><li><strong>For enterprises</strong> - automation arrives without throwing away governance, accountability or European sovereignty.</li><li><strong>For TYPO3</strong> - the platform competes in the agentic era by leaning into what it has always done well, and by standardizing what this lab has proven.</li></ul><p>The cool thing is the fit: agentic CMS work needs exactly the boring-serious infrastructure TYPO3 already has. The years to the next LTS are the window to turn that into a modern growth story.</p>',
                    self::mcpStrategyImage('v14-15-conclusion-source-6178b41759412e71.png', 'final TYPO3 v14 agentic platform operating model presented as analysis results', 'final TYPO3 v14 agentic platform operating model presented as analysis results')
                ),
                self::block('desiderio_ctabanner', [
                    'header' => 'Plan your TYPO3 v14 agentic platform with webconsulting',
                    'cta_text' => 'Start the TYPO3 v14 strategy',
                    'cta_link' => '/contact',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoriesPage(): array
    {
        return [
            'title' => 'Success stories',
            'navTitle' => 'Success stories',
            'slug' => '/success-stories',
            'abstract' => 'Fifteen clearly illustrative enterprise scenarios that ask one question with a straight face: what would happen if the most ambitious organizations on the planet ran their websites on TYPO3 with Desiderio?',
            'description' => 'What if an AI lab, a space company, or a toy maker ran on TYPO3? Fifteen playful what-if Desiderio showcase scenarios about multi-brand theming and editor velocity.',
            'parentSlug' => null,
            'subtitle' => 'Fifteen what-if scenarios, played straight — what would happen if the most ambitious kinds of organisation ran their whole web presence on TYPO3 and Desiderio?',
            'blogList' => true,
            // The seeder prepends a paginated blog_posts list plugin when
            // EXT:blog is installed; the highlight stays below the list.
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'Why these scenarios work',
                    'content' => '<p><strong>Swap the famous logos for your clients and every argument still holds.</strong></p><ul><li><strong>Multi-brand</strong> — many sites from one install via per-page themes.</li><li><strong>No tickets</strong> — editors publish without waiting on developers.</li><li><strong>Procurement-proof</strong> — open-source licensing and self-hosting.</li></ul><p>The companies are illustrative guests — the capabilities are shipping today.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => self::REPO_URL,
                    'link_text' => 'See the real package',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryAiLabPage(): array
    {
        return [
            'title' => 'What if an AI lab ran on TYPO3?',
            'navTitle' => 'An AI lab',
            'slug' => '/success-stories/an-ai-lab',
            'abstract' => 'A clearly illustrative showcase scenario: an AI safety lab publishes interpretability papers, model cards, and policy posts at a pace that breaks most CMS workflows. In this invented universe, the lab solves it the boring way — open-source TYPO3, Desiderio elements, and editors who never wait for a deploy.',
            'description' => 'Fictional showcase: the AI lab\'s research blog on TYPO3 — Desiderio editor previews for fast publishing, per-page themes per product line, and self-hosted open source.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'AI & Research'],
                'tags' => ['AI lab', 'AI research', 'Editorial workflow', 'Self-hosting'],
            ],
            'content' => [
                self::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: research velocity without a web team in the loop',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why our imagined AI lab picks TYPO3 + Desiderio',
                    'content' => '<p>In this scenario the lab\'s researchers write, and the CMS keeps up.</p><ul><li><strong>Fast publishing</strong> — every paper lands as article hero + FAQ + charts, previewed before publish.</li><li><strong>A preset per team</strong> — the safety team runs calm Lagoon, product pages run Midnight.</li><li><strong>Culture-fit</strong> — a GPL design system, self-hosted under the lab\'s own keys.</li></ul><p>The company is invented; every capability ships in Desiderio today.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The real technical facts',
                    'button_link' => '{{page:technical-features}}',
                    'media' => self::screenshot('backend-visual-editor.png', 'Inline editing in the Visual Editor', 'TYPO3 Visual Editor editing a Desiderio element inline.'),
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics, plausible physics: what a research-heavy site gains when publishing stops depending on deployments.',
                    'items' => [
                        ['value' => '57 min', 'label' => 'Paper to published page', 'description_text' => 'Fictional median — article hero, key-findings FAQ, and charts composed from existing elements.'],
                        ['value' => '3', 'label' => 'Brand worlds, one install', 'description_text' => 'Research, product, and policy subtrees each carry their own theme preset in this scenario.'],
                        ['value' => '0', 'label' => 'Deploys per publication', 'description_text' => 'Editors compose and publish; the imagined web platform team reviews tokens, not tickets.'],
                        ['value' => '100%', 'label' => 'Self-hosted', 'description_text' => 'Open-source CMS, open-source design system, infrastructure under the lab\'s own control.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We interpret neural networks for a living. Our CMS should not be the most mysterious system in the building — with the token layer, at least the website is fully interpretable.',
                    'author' => 'Head of Web Platform',
                    'role' => 'invented persona — no real the AI lab statement',
                    'variant' => 'large',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Your research team is real. The workflow can be too.',
                    'description' => 'Everything in this story except the company ships in the free package: article elements, per-page themes, backend previews, one-command seeding.',
                    'cta_text' => 'Install Desiderio for free',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStorySpaceCompanyPage(): array
    {
        return [
            'title' => 'What if a private space company ran on TYPO3?',
            'navTitle' => 'A space company',
            'slug' => '/success-stories/a-space-company',
            'abstract' => 'A clearly illustrative showcase scenario: a company that launches rockets weekly cannot wait for a website rebuild between missions. In this invented universe, every mission gets its own TYPO3 subtree, its own Desiderio preset, and a countdown hero that the comms team configures over coffee.',
            'description' => 'Fictional showcase: the space company mission microsites on TYPO3 — every launch a Desiderio-themed page subtree with countdown heroes, status boards, and zero rebuilds.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 10:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Aerospace'],
                'tags' => ['Space company', 'Mission microsites', 'Per-page themes', 'Countdown hero'],
            ],
            'content' => [
                self::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: a microsite per mission, a preset per brand',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Why our imagined space company picks TYPO3 + Desiderio',
                    'content' => '<p>A microsite per mission, a preset per brand — one install, one content pool.</p><ul><li><strong>Themed subtrees</strong> — flagship missions in Ember, crewed flights in Marine, night launches in Midnight.</li><li><strong>Mission elements</strong> — countdown heroes for T-minus, stats for the booster fleet, a status board for range weather.</li><li><strong>No redeploys</strong> — when a launch scrubs, an editor just reschedules the countdown.</li></ul><p>The company is fiction; the per-page theme engine and countdown and dashboard elements are stock Desiderio.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See per-page themes explained',
                    'button_link' => '{{page:technical-features}}',
                    'media' => self::screenshot('frontend-pricing-midnight-dark.png', 'Dark Midnight preset page', 'A Desiderio page rendered in the dark Midnight theme preset.'),
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Telemetry from the alternate timeline',
                    'description' => 'Made-up numbers with believable trajectories: what launch-cadence publishing looks like when the CMS is not the bottleneck.',
                    'items' => [
                        ['value' => '14', 'label' => 'Mission microsites live', 'description_text' => 'One TYPO3 install, fourteen themed subtrees in this illustrative fleet.'],
                        ['value' => '45 min', 'label' => 'Scrub to rescheduled site', 'description_text' => 'Countdown retargeted, status board updated, hero re-published — editors only.'],
                        ['value' => '0', 'label' => 'Rebuilds between launches', 'description_text' => 'Theme presets switch at runtime; the imagined launch tempo never waits for CI.'],
                        ['value' => '99.9%', 'label' => 'Uptime target met', 'description_text' => 'Server-rendered pages with no JS framework survive every illustrative traffic spike.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We reuse boosters because rebuilding them for every flight would be absurd. Rebuilding the website for every mission was the same absurdity — so we stopped.',
                    'author' => 'Director of Mission Communications',
                    'role' => 'invented persona — no real the space company statement',
                    'variant' => 'large',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Launch cadence for your content team',
                    'description' => 'Countdown heroes, status boards, per-subtree themes, and a seeder that builds the whole demo in one command — all in the free core.',
                    'cta_text' => 'Start your countdown on GitHub',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryAiResearchLabPage(): array
    {
        return [
            'title' => 'What if an AI research lab ran on TYPO3?',
            'navTitle' => 'An AI research lab',
            'slug' => '/success-stories/an-ai-research-lab',
            'abstract' => 'A clearly illustrative showcase scenario: when a company ships a new product line every quarter, the website becomes the slowest model in the lineup. In this invented universe, the web team trades rebuilds for re-theming — one content pool, one Desiderio preset per product family, launch pages assembled before the keynote ends.',
            'description' => 'Fictional showcase: the AI research lab product launches on TYPO3 — one Desiderio content pool, a theme preset per product family, and pricing pages editors update themselves.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 12:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'AI & Research'],
                'tags' => ['AI research lab', 'Product launches', 'Pricing pages', 'Theme presets'],
            ],
            'content' => [
                self::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: launch pages at model speed',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why our imagined AI research lab picks TYPO3 + Desiderio',
                    'content' => '<p>Each product family runs its own preset, inherited down its subtree from a single install.</p><ul><li><strong>On-brand families</strong> — a calm neutral for the platform, something warmer for consumer apps.</li><li><strong>Stock monetization</strong> — pricing tables, comparison matrices, and usage calculators ship ready.</li><li><strong>Minutes, not tickets</strong> — marketing updates tiers right after a pricing call.</li><li><strong>No lock-in</strong> — the procurement answer is a GPL license and a composer.json.</li></ul><p>The company is borrowed; the elements and theme engine are real.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'Browse the pricing elements',
                    'button_link' => '{{page:chapter-pricing}}',
                    'media' => self::screenshot('backend-site-settings-theme.png', 'Theme preset selection in site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown.'),
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Benchmarks nobody ran, in a universe nobody visited',
                    'description' => 'Invented but plausible: what shipping velocity looks like when the website re-themes instead of rebuilding.',
                    'items' => [
                        ['value' => '6', 'label' => 'Product families, one install', 'description_text' => 'Each with its own preset and page subtree in this illustrative setup.'],
                        ['value' => '2 h', 'label' => 'Keynote to live launch page', 'description_text' => 'Hero, feature grid, pricing table, FAQ — composed from existing elements.'],
                        ['value' => '0', 'label' => 'Rebuilds per product launch', 'description_text' => 'Runtime token switching makes the imagined design refresh a dropdown choice.'],
                        ['value' => '244', 'label' => 'Elements on the shelf', 'description_text' => 'The one number in this story that is not illustrative.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We benchmark everything, so we benchmarked our website workflow. Re-theming beat rebuilding on every metric — tokens per launch went to zero, in the good way.',
                    'author' => 'Head of Web Experience',
                    'role' => 'invented persona — no real the AI research lab statement',
                    'variant' => 'large',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Ship your next launch page the illustrative-the AI research lab way',
                    'description' => 'Pricing tables, comparison matrices, heroes, and FAQs — finished, themed, and free. The only thing we cannot ship is the keynote.',
                    'cta_text' => 'Get the free package',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }


    /**
     * @return ShowcasePage
     */
    private static function successStorySpaceAgencyPage(): array
    {
        return [
            'title' => 'What if a national space agency ran on TYPO3?',
            'navTitle' => 'A space agency',
            'slug' => '/success-stories/a-space-agency',
            'abstract' => 'A clearly illustrative showcase scenario: a space agency with six decades of mission pages, press kits, and image archives moves them onto one TYPO3 install — and finally passes its own accessibility mandate.',
            'description' => 'Fictional showcase: the space agency mission archives on TYPO3 — accessible Desiderio elements, one subtree per mission, and editors who publish without a launch window.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-03 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Aerospace'],
                'tags' => ['Space agency', 'Mission archives', 'Accessibility', 'Open data'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Sixty years of missions, sixty years of microsites — each program its own themed subtree.</p><ul><li><strong>A preset per era</strong> — Apollo in archive beige, Artemis in Midnight.</li><li><strong>Stock storytelling</strong> — timelines, stats, and galleries out of the box.</li><li><strong>Audit-ready</strong> — accessible markup Section 508 auditors sign off without a meeting.</li></ul><p>The agency is illustrative; the WCAG 2.2-checked contrast on every preset is not.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '60+', 'label' => 'Mission subtrees migrated', 'description_text' => 'Each program keeps its own era-appropriate theme preset in this fiction.'],
                        ['value' => '508', 'label' => 'Compliance sections passed', 'description_text' => 'Accessible-by-default elements clear the imagined federal audit on the first run.'],
                        ['value' => '1 day', 'label' => 'Press kit to live page', 'description_text' => 'Editors compose galleries and stat boards without waiting on contractors.'],
                        ['value' => '0', 'label' => 'Vendor lock-ins', 'description_text' => 'GPL license and self-hosting keep the imagined procurement office calm.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We can land a rover on Mars, but updating the mission page used to take longer than the cruise phase. Now the page is live before the dust settles.',
                    'author' => 'Web Program Manager',
                    'role' => 'invented persona — no real the space agency statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryStreamingPlatformPage(): array
    {
        return [
            'title' => 'What if a streaming platform ran on TYPO3?',
            'navTitle' => 'A streaming platform',
            'slug' => '/success-stories/a-streaming-platform',
            'abstract' => 'A clearly illustrative showcase scenario: a streaming giant ships a themed landing page for every original series — from one TYPO3 install, with a preset per show and zero rebuilds between premieres.',
            'description' => 'Fictional showcase: the streaming platform series landing pages on TYPO3 — a Desiderio preset per show, countdown heroes for premieres, and editors who ship between episodes.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-07 10:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Streaming platform', 'Landing pages', 'Theme presets', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Every original gets a landing page, and every show its own mood — one install, many presets.</p><ul><li><strong>Mood per show</strong> — the dark thriller runs Midnight, the baking show runs Citrus.</li><li><strong>Stock elements</strong> — countdown heroes for premiere dates, FAQ for spoilers policy.</li><li><strong>One less tool</strong> — the marketing team retires its static-site generator.</li></ul><p>The company is borrowed; the per-subtree theme engine ships today.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '80', 'label' => 'Series pages, one install', 'description_text' => 'Each landing page carries the show\'s own preset in this scenario.'],
                        ['value' => '3 h', 'label' => 'Greenlight to teaser page', 'description_text' => 'Hero, trailer embed, countdown — composed from existing elements.'],
                        ['value' => '0', 'label' => 'Rebuilds between premieres', 'description_text' => 'Presets switch at runtime; the imagined release calendar never waits for CI.'],
                        ['value' => '2', 'label' => 'Modes shipped by default', 'description_text' => 'Light and dark, both WCAG-checked — binge-friendly at 2 a.m.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We used to A/B test thumbnails. Now we A/B test theme presets — same content pool, two dropdown choices, done before the credits roll.',
                    'author' => 'Director of Title Marketing',
                    'role' => 'invented persona — no real the streaming platform statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryGameStudioPage(): array
    {
        return [
            'title' => 'What if a game studio ran on TYPO3?',
            'navTitle' => 'A game studio',
            'slug' => '/success-stories/a-game-studio',
            'abstract' => 'A clearly illustrative showcase scenario: a games company where every franchise is its own visual world keeps its platformer worlds, its adventure saga, and the hardware store on one TYPO3 install — one preset per universe.',
            'description' => 'Fictional showcase: the game studio franchise pages on TYPO3 — a Desiderio brand world per franchise, launch pages from stock elements, and editors who ship at console speed.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-11 09:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Game studio', 'Product launches', 'Brand worlds', 'Per-page themes'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A plumber, a princess, and a console launch share nothing visually — except, in this fiction, a TYPO3 install.</p><ul><li><strong>A preset per franchise</strong> — warm reds for the platformer, sage greens for the adventure, clean neutrals for hardware.</li><li><strong>Launch in a click</strong> — pages assemble from countdown heroes, feature grids, and pricing tables.</li><li><strong>Legal-friendly</strong> — the GPL license keeps procurement happy.</li></ul><p>The franchises are real, the scenario is not, the elements ship today.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '12', 'label' => 'Franchise brand worlds', 'description_text' => 'One install, twelve visual universes via per-subtree presets in this fiction.'],
                        ['value' => '4 h', 'label' => 'Direct to launch page', 'description_text' => 'The imagined web team publishes while the presentation still streams.'],
                        ['value' => '0', 'label' => 'Style sheets hand-written', 'description_text' => 'Every world is a token set, not a fork of the frontend.'],
                        ['value' => '100%', 'label' => 'Editors in the backend', 'description_text' => 'Backend previews mean nobody publishes a hero blind.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Each of our worlds has its own physics. It turns out they can still share a CMS — the tokens change, the workflow doesn\'t.',
                    'author' => 'Head of Web Worlds',
                    'role' => 'invented persona — no real the game studio statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryToyMakerPage(): array
    {
        return [
            'title' => 'What if a toy maker ran on TYPO3?',
            'navTitle' => 'A toy maker',
            'slug' => '/success-stories/a-toy-maker',
            'abstract' => 'A clearly illustrative showcase scenario: a brick company that launches themed sets weekly builds its campaign pages the same way it builds everything else — from interchangeable, well-documented parts.',
            'description' => 'Fictional showcase: the toy maker campaign pages on TYPO3 — Desiderio elements as bricks, a preset per product line, and campaign pages clicked together in an afternoon.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-15 11:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Commerce'],
                'tags' => ['Toy maker', 'Campaign pages', 'Brand worlds', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company built on interlocking parts would recognise this instantly: <strong>244 elements as bricks.</strong></p><ul><li><strong>Studs that only fit one way</strong> — typed component contracts.</li><li><strong>Clicked together</strong> — a campaign page assembled in an afternoon.</li><li><strong>A preset per range</strong> — space sets run Midnight, botanical sets run Forest.</li></ul><p>The bricks are real and GPL-licensed; the company is on loan for the joke.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '244', 'label' => 'Bricks in the box', 'description_text' => 'Desiderio\'s element library, reused across every imagined campaign.'],
                        ['value' => '1', 'label' => 'Afternoon per campaign page', 'description_text' => 'Hero, story sections, product grid — assembled, previewed, published.'],
                        ['value' => '18', 'label' => 'Product-line worlds', 'description_text' => 'Each line carries its own preset in this illustrative setup.'],
                        ['value' => '0', 'label' => 'Instructions misread', 'description_text' => 'Typed f:argument contracts fail loudly when a brick is used wrong.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our motto is \'only the best is good enough\', and our web team\'s motto was \'the rebuild ships next quarter\'. Only one of those survived the migration.',
                    'author' => 'Digital Campaigns Lead',
                    'role' => 'invented persona — no real the toy maker statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryFurnitureRetailerPage(): array
    {
        return [
            'title' => 'What if a furniture retailer ran on TYPO3?',
            'navTitle' => 'A furniture retailer',
            'slug' => '/success-stories/a-furniture-retailer',
            'abstract' => 'A clearly illustrative showcase scenario: a furniture giant publishing in dozens of languages flat-packs its catalog pages — same parts everywhere, assembled locally, no agency hotline required.',
            'description' => 'Fictional showcase: the furniture retailer catalog pages on TYPO3 — multilanguage Desiderio elements, ICU plurals that survive every locale, and country teams who assemble pages themselves.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-19 10:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Commerce'],
                'tags' => ['Furniture retailer', 'Multilanguage', 'Catalog pages', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p><strong>Flat-pack logic applied to the web</strong> — every country site gets the same well-labelled parts and assembles them in its own language.</p><ul><li><strong>Shared parts</strong> — heroes, product grids, and FAQ elements.</li><li><strong>Correct everywhere</strong> — XLIFF and ICU MessageFormat keep plurals and dates right from Sweden to Japan.</li><li><strong>One update, not forty</strong> — the global team ships once.</li></ul><p>The meatballs are not included; the translation architecture ships in the free package.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '42', 'label' => 'Country sites, one toolkit', 'description_text' => 'Same elements, locally assembled, in this illustrative rollout.'],
                        ['value' => '100%', 'label' => 'Strings through XLIFF', 'description_text' => 'Screen-reader labels included — no hardcoded copy anywhere.'],
                        ['value' => '1', 'label' => 'Update for all locales', 'description_text' => 'Element fixes ship once and every market inherits them.'],
                        ['value' => '0', 'label' => 'Allen keys required', 'description_text' => 'Backend previews replace the instruction sheet.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our products ship as parts and instructions. Our website finally does too — and nobody has called the assembly hotline since.',
                    'author' => 'Global Web Coordinator',
                    'role' => 'invented persona — no real the furniture retailer statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryMusicServicePage(): array
    {
        return [
            'title' => 'What if a music-streaming service ran on TYPO3?',
            'navTitle' => 'A music service',
            'slug' => '/success-stories/a-music-service',
            'abstract' => 'A clearly illustrative showcase scenario: a streaming service whose editorial team publishes artist features and year-in-review pages at playlist speed — dark mode first, obviously.',
            'description' => 'Fictional showcase: the music service editorial pages on TYPO3 — dark-mode-first Desiderio presets, artist features from stock elements, and a wrapped campaign without a single rebuild.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-23 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Music service', 'Editorial workflow', 'Dark mode', 'Theme presets'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>An editorial team shipping artist features daily cannot file a ticket per page.</p><ul><li><strong>Compose, don\'t code</strong> — features built from quote blocks, stat boards, and gallery elements.</li><li><strong>Published fast</strong> — previewed in the backend, live before the song ends.</li><li><strong>Campaign in a switch</strong> — the year-end push is a preset change plus seeded pages, not a three-month project.</li></ul><p>Dark mode is no afterthought; every preset ships both modes with checked contrast.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '365', 'label' => 'Editorial pages a year', 'description_text' => 'One artist feature per day in this illustrative newsroom.'],
                        ['value' => '2', 'label' => 'Modes, both first-class', 'description_text' => 'Dark mode users get checked contrast, not an inverted afterthought.'],
                        ['value' => '45 min', 'label' => 'Brief to published feature', 'description_text' => 'Quote, stats, gallery, embed — stock elements all the way down.'],
                        ['value' => '1', 'label' => 'Preset switch for year-end', 'description_text' => 'The imagined wrapped campaign is a dropdown, not a deploy.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our app went dark-mode-first years ago; our CMS pages finally followed. The contrast checker has better ears than our mastering engineers.',
                    'author' => 'Editorial Platform Lead',
                    'role' => 'invented persona — no real the music service statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryPaymentsCompanyPage(): array
    {
        return [
            'title' => 'What if a payments company ran on TYPO3?',
            'navTitle' => 'A payments company',
            'slug' => '/success-stories/a-payments-company',
            'abstract' => 'A clearly illustrative showcase scenario: a payments company famous for its documentation discovers that marketing pages can be engineered with the same rigor — typed components, audited templates, zero drift.',
            'description' => 'Fictional showcase: the payments company marketing pages on TYPO3 — typed Fluid components, pricing tables editors update at announcement speed, and a template audit at zero findings.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-27 11:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Commerce'],
                'tags' => ['Payments company', 'Documentation', 'Pricing pages', 'Self-hosting'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company that treats documentation as a product would audit its marketing stack — and like what it finds.</p><ul><li><strong>Typed contracts</strong> — <code>f:argument</code> types on every component.</li><li><strong>Zero findings</strong> — an 11-category template check, PHPStan at level max underneath.</li><li><strong>Edits, not deploys</strong> — pricing changes land in a backend preview, not a pull request.</li></ul><p>The rigor is real and verifiable in the repository; the customer is invented.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '0', 'label' => 'Audit findings tolerated', 'description_text' => 'The 11-category template audit gates every commit in the real package.'],
                        ['value' => '15 min', 'label' => 'Pricing call to live table', 'description_text' => 'The imagined billing team edits tiers like records, because they are.'],
                        ['value' => '170+', 'label' => 'Tests on the stack', 'description_text' => 'Unit and functional, PHP 8.3 and 8.4 — the one stat here that is not fiction.'],
                        ['value' => '100%', 'label' => 'Infrastructure in-house', 'description_text' => 'Self-hosted open source clears the illustrative compliance review.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We measure API reliability in nines. Our marketing site used to be measured in apologies — the typed components fixed the gap.',
                    'author' => 'Head of Web Infrastructure',
                    'role' => 'invented persona — no real the payments company statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryLanguageAppPage(): array
    {
        return [
            'title' => 'What if a language-learning app ran on TYPO3?',
            'navTitle' => 'A language app',
            'slug' => '/success-stories/a-language-app',
            'abstract' => 'A clearly illustrative showcase scenario: a language-learning app that teaches forty languages finally gets a website that speaks all of them — with plurals, dates, and screen-reader labels done right.',
            'description' => 'Fictional showcase: the language app course pages on TYPO3 — ICU MessageFormat for every locale, streak-counter stats from stock elements, and an owl-approved publishing pace.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-01 09:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Education'],
                'tags' => ['Language app', 'Multilanguage', 'Gamification', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Teaching Japanese through Spanish breaks most translation layers — not this one.</p><ul><li><strong>Real i18n</strong> — XLIFF 2.0 and ICU MessageFormat handle plural rules from Polish to Arabic.</li><li><strong>Reusable pages</strong> — every course assembles from the same stat boards and timeline elements.</li><li><strong>Stock metrics</strong> — the homepage streak counter is a shipped element.</li></ul><p>The owl is pleased; the localization architecture is the real product here.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '40+', 'label' => 'Course locales served', 'description_text' => 'Each with correct plurals and date formats in this illustrative rollout.'],
                        ['value' => '100%', 'label' => 'ARIA labels translated', 'description_text' => 'Screen-reader strings ship through the same XLIFF pipeline.'],
                        ['value' => '365', 'label' => 'Day publishing streak', 'description_text' => 'The imagined content team never misses — the owl is watching.'],
                        ['value' => '0', 'label' => 'Hardcoded strings found', 'description_text' => 'Every label runs through f:translate, even the celebratory ones.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We gamified language learning. The CMS gamified itself — the team genuinely competes for the cleanest backend preview.',
                    'author' => 'Web Localization Lead',
                    'role' => 'invented persona — no real the language app statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryNonprofitEncyclopediaPage(): array
    {
        return [
            'title' => 'What if a nonprofit encyclopedia ran on TYPO3?',
            'navTitle' => 'A nonprofit encyclopedia',
            'slug' => '/success-stories/a-nonprofit-encyclopedia',
            'abstract' => 'A clearly illustrative showcase scenario: the free encyclopedia gives its campaign and fundraising pages the same treatment as its articles — open source, accessible, and owned by nobody\'s vendor.',
            'description' => 'Fictional showcase: the encyclopedia campaign pages on TYPO3 — GPL design system on GPL CMS, donation banners editors test themselves, and accessibility as policy, not promise.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-05 10:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Education'],
                'tags' => ['Encyclopedia', 'Open source', 'Accessibility', 'Self-hosting'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>An organization built on volunteers and GPL licenses would never accept a proprietary design system — and it doesn\'t have to.</p><ul><li><strong>Free all the way down</strong> — a GPL component library on a GPL CMS, self-hosted on its own metal.</li><li><strong>Citable accessibility</strong> — donation appeals built from banner and stat elements with policy-grade contrast.</li><li><strong>Inspectable</strong> — every template is open to the same community that edits the articles.</li></ul><p>The alignment of licenses is the entire joke — and entirely real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '2', 'label' => 'GPL licenses, aligned', 'description_text' => 'CMS and design system share the imagined foundation\'s values out of the box.'],
                        ['value' => '100%', 'label' => 'Templates publicly auditable', 'description_text' => 'The community reviews Fluid the way it reviews citations.'],
                        ['value' => '4.5:1', 'label' => 'Contrast, enforced', 'description_text' => 'WCAG ratios are solved by the build, not promised by a styleguide PDF.'],
                        ['value' => '0', 'label' => 'Vendors in the stack', 'description_text' => 'Self-hosted everything — the illustrative procurement page stays a stub.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Citation needed? The contrast solver ships its own proof. First design system our reviewers accepted without a talk page argument.',
                    'author' => 'Movement Web Lead',
                    'role' => 'invented persona — no real the encyclopedia statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryEnergyDrinkBrandPage(): array
    {
        return [
            'title' => 'What if an energy-drink brand ran on TYPO3?',
            'navTitle' => 'An energy-drink brand',
            'slug' => '/success-stories/an-energy-drink-brand',
            'abstract' => 'A clearly illustrative showcase scenario: an energy-drink empire that is secretly a media company spins up an event microsite per cliff dive, air race, and festival — caffeinated, themed, and on time.',
            'description' => 'Fictional showcase: the energy-drink brand event microsites on TYPO3 — a Desiderio subtree per event, countdown heroes for every start gate, and campaign pages with wings.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-09 11:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Sports'],
                'tags' => ['Energy-drink brand', 'Event microsites', 'Campaign pages', 'Countdown hero'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company that runs more events than some federations needs a <strong>microsite assembly line</strong> — and gets one.</p><ul><li><strong>A preset per event</strong> — Ember for the desert rally, Marine for the regatta.</li><li><strong>Stock race elements</strong> — countdown heroes toward start gates, stat boards for qualifying.</li><li><strong>Launch between espressos</strong> — the events team ships a site in minutes.</li></ul><p>The wings are marketing; the runtime theme switching is shipping code.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '30+', 'label' => 'Event microsites a season', 'description_text' => 'One subtree per event in this illustrative calendar, all one install.'],
                        ['value' => '90 min', 'label' => 'Announcement to live site', 'description_text' => 'Countdown hero, schedule timeline, ticket CTA — stock parts.'],
                        ['value' => '0', 'label' => 'Energy drinks required', 'description_text' => 'Editors publish calmly; the adrenaline stays in the footage.'],
                        ['value' => '14', 'label' => 'Presets in rotation', 'description_text' => 'Every event genre gets a matching visual world.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We give athletes wings and gave our web team three CMSes. Now it\'s one install per season and the only thing still freefalling is the cliff diver.',
                    'author' => 'Head of Event Digital',
                    'role' => 'invented persona — no real the energy-drink brand statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryPhysicsLabPage(): array
    {
        return [
            'title' => 'What if a physics research lab ran on TYPO3?',
            'navTitle' => 'A physics lab',
            'slug' => '/success-stories/a-physics-lab',
            'abstract' => 'A clearly illustrative showcase scenario: the birthplace of the web upgrades its experiment pages — open-source elements, accessible data tables, and physics results published faster than peer review.',
            'description' => 'Fictional showcase: the physics lab experiment pages on TYPO3 — accessible chart elements for collision data, a preset per experiment, and the web back where it was invented.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-13 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Science'],
                'tags' => ['Physics lab', 'Research publishing', 'Open data', 'Accessibility'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>The laboratory that invented the web deserves better than PDF press releases — so it publishes like it researches.</p><ul><li><strong>A preset per experiment</strong> — each one its own themed subtree.</li><li><strong>Real data markup</strong> — results land as accessible chart and table elements.</li><li><strong>Outreach, assembled</strong> — pages built from timelines and stat boards.</li></ul><p>Server-rendered, no hydration cost — appropriately fundamental. The physics is real, the scenario invented.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '17', 'label' => 'Experiment subtrees', 'description_text' => 'Each collaboration keeps its own visual identity in this fiction.'],
                        ['value' => '9', 'label' => 'Chart types, accessible', 'description_text' => 'Every visualization ships a screen-reader-friendly data table twin.'],
                        ['value' => '1 h', 'label' => 'Preprint to outreach page', 'description_text' => 'The imagined comms team publishes before the arXiv listing updates.'],
                        ['value' => '0', 'label' => 'JS frameworks colliding', 'description_text' => 'Server-rendered Fluid — the only collisions happen in the ring.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We built the web here and then spent thirty years fighting our own CMS. The standard model has fewer free parameters than our old templates did.',
                    'author' => 'Outreach Platform Physicist',
                    'role' => 'invented persona — no real the physics lab statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryAnimationStudioPage(): array
    {
        return [
            'title' => 'What if an animation studio ran on TYPO3?',
            'navTitle' => 'An animation studio',
            'slug' => '/success-stories/an-animation-studio',
            'abstract' => 'A clearly illustrative showcase scenario: a story-first animation studio gives every film its own web world — lamp included — without rendering a single page rebuild.',
            'description' => 'Fictional showcase: the animation studio film pages on TYPO3 — a Desiderio brand world per film, story-driven scroll pages from stock elements, and dark mode for the screening room.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-17 10:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Animation studio', 'Story pages', 'Brand worlds', 'Dark mode'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A studio that storyboards everything would storyboard its film pages too — and the boards map straight to elements.</p><ul><li><strong>Scene by scene</strong> — an article hero for the opening shot, alternating textmedia scenes, a stat board for the box office.</li><li><strong>A preset per film</strong> — ocean blues, desert ambers, monster pastels.</li><li><strong>Cut at runtime</strong> — themes switch like a scene change, no rebuild.</li></ul><p>The lamp hops in real life; the per-page theme engine ships in the package.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '28', 'label' => 'Film worlds, one install', 'description_text' => 'Every feature keeps its own palette in this illustrative archive.'],
                        ['value' => '1', 'label' => 'Storyboard per page', 'description_text' => 'Scenes map one-to-one onto stock content elements.'],
                        ['value' => '2', 'label' => 'Modes for the screening room', 'description_text' => 'Dark mode that respects the colorists, light mode for the lobby.'],
                        ['value' => '0', 'label' => 'Renders re-queued', 'description_text' => 'Pages are server-rendered Fluid — the render farm stays on the movie.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Story is king here, and our old website was a subplot nobody followed. Now every film page reads like a storyboard — and ships before the trailer drops.',
                    'author' => 'Studio Web Producer',
                    'role' => 'invented persona — no real the animation studio statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * Helper pages for the success-story blog section. Category and tag badges
     * on the posts link here (plugin.tx_blog.settings.categoryUid/tagUid), so
     * these pages only make sense — and only get seeded — when EXT:blog is
     * installed. They stay out of the navigation.
     *
     * @return array<int, ShowcasePage>
     */
    public static function blogSupportPages(): array
    {
        return [
            [
                'title' => 'Category',
                'navTitle' => 'Category',
                'slug' => '/success-stories/category',
                'abstract' => 'Success stories filtered by category.',
                'description' => 'All illustrative Desiderio success stories in a category.',
                'parentSlug' => 'success-stories',
                'hideInNav' => true,
                'content' => [
                    self::block('blog_category', []),
                ],
            ],
            [
                'title' => 'Tag',
                'navTitle' => 'Tag',
                'slug' => '/success-stories/tag',
                'abstract' => 'Success stories filtered by tag.',
                'description' => 'All illustrative Desiderio success stories carrying a tag.',
                'parentSlug' => 'success-stories',
                'hideInNav' => true,
                'content' => [
                    self::block('blog_tag', []),
                ],
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function imprintPage(): array
    {
        return [
            'title' => 'Imprint',
            'navTitle' => 'Imprint',
            'slug' => '/imprint',
            'abstract' => 'Demo imprint page seeded by the Desiderio styleguide: the desiderio_imprint element filled with clearly illustrative placeholder data, plus a note on replacing it before go-live.',
            'description' => 'Demo imprint built with the Desiderio imprint content element — illustrative placeholder company data showing the legal-page structure for TYPO3 sites.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_imprint', [
                    'header' => 'Imprint (demo data)',
                    'company_name' => 'webconsulting studio GmbH (illustrative demo company)',
                    'address' => "Lindengasse 12\n1070 Vienna\nAustria",
                    'contact_email' => 'legal@webconsulting.example',
                    'contact_phone' => '+43 1 555 0182',
                    'registry_info' => "Commercial register: FN 000000x (demo data)\nCommercial Court of Vienna",
                    'vat_id' => 'ATU00000000',
                    'additional_info' => '<p>Responsible for content under §25 MedienG: Mara Lindqvist, Managing Director (illustrative). Every value on this page is seeded placeholder data from the Desiderio styleguide.</p>',
                ]),
                self::block('desiderio_contenthighlight', [
                    'header' => 'This is a demo placeholder',
                    'content' => '<p>The desiderio_imprint element gives your legal page a finished, token-themed structure — but the law cares about the content. Replace the illustrative company, register, and contact data above with your real details (and have them reviewed) before this page goes anywhere near production.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '',
                    'link_text' => '',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function privacyPage(): array
    {
        return [
            'title' => 'Privacy notice',
            'navTitle' => 'Privacy',
            'slug' => '/privacy',
            'abstract' => 'Demo privacy page seeded by the Desiderio styleguide: a GDPR-style notice built with desiderio_privacynotice plus a working desiderio_datarequestform — all clearly placeholder content.',
            'description' => 'Demo GDPR-style privacy notice built with Desiderio elements, including a styled data-request form — placeholder structure to replace with your reviewed policy.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_privacynotice', [
                    'header' => 'Privacy notice (demo)',
                    'intro' => 'Seeded demo notice for the illustrative webconsulting studio site. It demonstrates the structure of a GDPR-style privacy page with the desiderio_privacynotice element — replace every section with your own reviewed text before go-live.',
                    'last_updated' => '12 May 2026',
                    'sections' => [
                        ['title' => 'What we collect', 'content' => '<p>This demo site collects nothing. A real deployment would describe here which personal data you process: contact details from forms, technical data such as IP addresses, and any analytics identifiers.</p>'],
                        ['title' => 'Why we process it', 'content' => '<p>List your purposes and legal bases per GDPR Art. 6 — contract fulfilment, legitimate interest, consent — one plain-language paragraph per purpose, no legalese required by the element.</p>'],
                        ['title' => 'Cookies and consent', 'content' => '<p>Desiderio ships consent-aware banners (GDPR banner, cookie banner elements); document here which categories exist and how visitors change their choice later.</p>'],
                        ['title' => 'Your rights', 'content' => '<p>Access, rectification, erasure, portability, objection. The data-request form below shows how Desiderio lets visitors exercise these rights without writing an email.</p>'],
                    ],
                ]),
                self::block('desiderio_datarequestform', [
                    'header' => 'Exercise your data rights (demo form)',
                    'description' => 'This is the desiderio_datarequestform element: a styled GDPR request form your visitors can use for access, export, or deletion requests. On this demo page it submits nowhere.',
                    'request_types' => [
                        ['label' => 'Access my data (Art. 15)', 'value' => 'access'],
                        ['label' => 'Export my data (Art. 20)', 'value' => 'export'],
                        ['label' => 'Delete my data (Art. 17)', 'value' => 'deletion'],
                        ['label' => 'Correct my data (Art. 16)', 'value' => 'rectification'],
                    ],
                    'privacy_text' => 'Demo placeholder: in production, explain here how request data is handled and within which deadline you respond (30 days under GDPR).',
                    'submit_text' => 'Send demo request',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function accessibilityPage(): array
    {
        return [
            'title' => 'Accessibility statement',
            'navTitle' => 'Accessibility',
            'slug' => '/accessibility',
            'abstract' => 'Demo accessibility statement seeded by the Desiderio styleguide, plus an engineering note on what the package contributes: a WCAG 2.2 contrast solver, translated ARIA labels, and audited templates.',
            'description' => 'Demo accessibility statement with Desiderio\'s a11y engineering explained: WCAG 2.2 contrast solving on every theme, translated screen-reader labels, audited markup.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_accessibilitystatement', [
                    'header' => 'Accessibility statement (demo)',
                    'conformance_level' => 'aa',
                    'content' => '<p>This demo statement ships with the Desiderio styleguide as a template — replace it with your own audited statement before go-live. Desiderio content elements are engineered against WCAG 2.2 Level AA.</p><h3>What the markup gives you</h3><ul><li>Every interactive component is keyboard reachable, with visible focus states on buttons, links, and form fields.</li><li>Semantic landmarks, native elements, and a logical heading order come baked into all 244 elements.</li><li>Image fields carry alternative-text inputs; decorative SVG icons are hidden from screen readers.</li><li>An 11-category template audit keeps inline styles and hardcoded colors out of every release.</li></ul><h3>What remains your job</h3><p>Editor-entered content, embedded media, and uploaded documents still need human review. This demo template does not replace a real conformance audit of your site.</p>',
                    'contact_email' => 'accessibility@desiderio.example',
                    'last_updated' => '12 May 2026',
                ]),
                self::block('desiderio_contenthighlight', [
                    'header' => 'How Desiderio engineers accessibility, not just claims it',
                    'content' => '<p>Three mechanisms do the heavy lifting. First, a <strong>WCAG 2.2 contrast solver</strong>: the theme generator solves accent lightness per hue against 4.5:1 text and 3:1 UI targets and refuses to emit failing CSS — and a unit test re-checks the shipped bundle for every preset, in light and dark mode. Second, <strong>translated assistive text</strong>: screen-reader labels, carousel controls, dismiss buttons, and pagination run through XLIFF catalogues, so ARIA speaks your visitor\'s language. Third, <strong>audits in CI</strong>: an 11-category template audit at zero tolerance plus 170+ tests keep landmarks, focus states, and heading order from regressing release after release.</p>',
                    'variant' => 'muted',
                    'alignment' => 'left',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'More engineering facts',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function notFoundPage(): array
    {
        return [
            'title' => 'Page not found',
            'navTitle' => '404',
            'slug' => '/404',
            'abstract' => 'The 404 page of the Desiderio styleguide: a short apology, a sitemap of the main sections, and a way back home — built entirely from Desiderio content elements.',
            'description' => 'Page not found — but 244 Desiderio content elements are exactly where they should be. Jump to the styleguide chapters, audience pages, or back to the homepage.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Error 404',
                    'header' => 'This page took the day off',
                    'subheadline' => 'The address you opened does not exist (anymore). The good news: everything worth seeing is one click away — and yes, even this error page is built from seeded Desiderio elements.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_contenthighlight', [
                    'header' => 'What probably happened',
                    'content' => '<p>A mistyped address, an outdated bookmark, or a link to content that moved when this styleguide was reseeded. Use the map below, or head straight back to the homepage.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '{{page:home}}',
                    'link_text' => 'Back to the homepage',
                ]),
                self::block('desiderio_sitemapgrid', [
                    'header' => 'Find your way from here',
                    'columns' => '4',
                    'groups' => [
                        [
                            'title' => 'Start here',
                            'pages' => [
                                ['label' => 'Homepage', 'link' => '{{page:home}}'],
                                ['label' => 'Technical features', 'link' => '{{page:technical-features}}'],
                                ['label' => 'GEO & AI search', 'link' => '{{page:geo-ai-search}}'],
                                ['label' => 'Success stories', 'link' => '{{page:success-stories}}'],
                            ],
                        ],
                        [
                            'title' => 'For your team',
                            'pages' => [
                                ['label' => 'Target groups overview', 'link' => '{{page:target-groups}}'],
                                ['label' => 'Agencies & integrators', 'link' => '{{page:target-groups/for-agencies}}'],
                                ['label' => 'In-house teams', 'link' => '{{page:target-groups/for-inhouse-teams}}'],
                                ['label' => 'Freelancers & solo devs', 'link' => '{{page:target-groups/for-freelancers}}'],
                            ],
                        ],
                        [
                            'title' => 'Element chapters',
                            'pages' => [
                                ['label' => 'Hero & Landing Intros', 'link' => '{{page:chapter-hero}}'],
                                ['label' => 'Features & Benefits', 'link' => '{{page:chapter-features}}'],
                                ['label' => 'Plans & Pricing', 'link' => '{{page:chapter-pricing}}'],
                                ['label' => 'Data & Dashboards', 'link' => '{{page:chapter-data}}'],
                                ['label' => 'Trust & Social Proof', 'link' => '{{page:chapter-social-proof}}'],
                            ],
                        ],
                        [
                            'title' => 'Legal & project',
                            'pages' => [
                                ['label' => 'Imprint', 'link' => '{{page:imprint}}'],
                                ['label' => 'Privacy notice', 'link' => '{{page:privacy}}'],
                                ['label' => 'Accessibility', 'link' => '{{page:accessibility}}'],
                                ['label' => 'GitHub repository', 'link' => self::REPO_URL],
                            ],
                        ],
                    ],
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Nothing here — everything there',
                    'description' => 'The homepage has the whole story: 244 elements, 15 themes, and the one command that seeded this site (404 page included).',
                    'cta_text' => 'Take me home',
                    'cta_link' => '{{page:home}}',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * The content-types hub: a catalog of the ten element chapters with counts,
     * one-line benefits, and links into each live chapter.
     *
     * @return ShowcasePage
     */
    private static function contentTypesHubPage(): array
    {
        return [
            'title' => 'Content types',
            'navTitle' => 'Content types',
            'slug' => '/content-types',
            'abstract' => 'All 244 Desiderio content elements in ten groups — every one finished, audited and editor-ready, with a backend preview, demo content and accessibility built in.',
            'description' => '244 content elements in ten groups: heroes, navigation, editorial, features, pricing, trust, team, data, conversion and footers. Browse each group live, rendered in its own theme preset.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Content types',
                    'header' => '244 elements. Ten groups. One design system.',
                    'subheadline' => 'Every element is finished, audited and editor-ready — with a backend preview, demo content and accessibility built into each. Browse by what you need to build.',
                    'variant' => 'left',
                ]),
                self::block('desiderio_benefitcards', [
                    'eyebrow' => 'The catalog',
                    'header' => 'Browse by what you are building',
                    'subheadline' => 'Ten groups, 244 elements — every one finished, audited and ready to drop in. Open a group to see it live.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'Hero & Landing Intros', 'description' => '21 heroes — split, video, countdown, stats. Make the first impression land.', 'link' => '{{page:chapter-hero}}'],
                        ['icon' => 'menu', 'title' => 'Navigation & Wayfinding', 'description' => '23 navbars, mega-menus and breadcrumbs that get people where they are going.', 'link' => '{{page:chapter-navigation}}'],
                        ['icon' => 'book-open', 'title' => 'Content & Editorial', 'description' => '24 ways to lay out an article — text, media, quotes, tabs, timelines.', 'link' => '{{page:chapter-content}}'],
                        ['icon' => 'blocks', 'title' => 'Features & Benefits', 'description' => '25 grids, bento layouts and comparisons to explain what you do.', 'link' => '{{page:chapter-features}}'],
                        ['icon' => 'tag', 'title' => 'Plans & Pricing', 'description' => '25 pricing tables, toggles and calculators — the blocks that close.', 'link' => '{{page:chapter-pricing}}'],
                        ['icon' => 'shield-check', 'title' => 'Trust & Social Proof', 'description' => '25 testimonial walls, logo clouds and case studies.', 'link' => '{{page:chapter-social-proof}}'],
                        ['icon' => 'users', 'title' => 'People & Team', 'description' => '24 team grids, org charts and founder stories.', 'link' => '{{page:chapter-team}}'],
                        ['icon' => 'chart', 'title' => 'Data & Dashboards', 'description' => '29 KPI cards, charts and status boards, with nine chart types.', 'link' => '{{page:chapter-data}}'],
                        ['icon' => 'send', 'title' => 'Leads & Conversion', 'description' => '25 forms, CTAs and lead magnets — each form a real ext:form.', 'link' => '{{page:chapter-conversion}}'],
                        ['icon' => 'layers', 'title' => 'Footers & Utility Areas', 'description' => '23 footers, cookie banners and legal blocks — the unglamorous 80%.', 'link' => '{{page:chapter-footer}}'],
                    ],
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Every one of these ships in the free package',
                    'description' => '244 elements, 15 themes and all integrations — GPL-2.0, no feature gates. Install it and start building today.',
                    'cta_text' => 'Get Desiderio free',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featuresOverviewPage(): array
    {
        return [
            'title' => 'The Desiderio ecosystem',
            'navTitle' => 'Features',
            'slug' => '/features',
            'abstract' => 'The design system is the front door. Behind it sits a coordinated set of TYPO3 14 extensions that reshape the backend, ship enterprise search and auth, monetize content, and open your site to AI agents — every one of them workspace-safe, theme-aware, and built on TYPO3 core concepts rather than a proprietary layer.',
            'description' => 'Desiderio is more than a design system. It is an ecosystem of TYPO3 14 extensions — records views, workspaces, blog, search, auth, forms, payments, and an agent-ready API layer — that all share one theme, one security model, and one set of core concepts.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Ecosystem',
                    'header' => 'Desiderio is more than the design system',
                    'subheadline' => '244 content elements were only the start. Around them sits an ecosystem of TYPO3 14 extensions — backend record views, one-click workspaces, a blog, enterprise search, enterprise SSO, accessible forms, stablecoin paywalls, and an agent-ready API layer. Same theme. Same security model. Same core concepts. Pick a card to go deeper.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'One stack, not thirteen plugins',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Every extension below is built to TYPO3 14.3 LTS, PHPStan level max, and the same workspace rules.',
                    'content' => '<p>Most TYPO3 sites become a pile of plugins, each with its own data model, styling, and security. <strong>The Desiderio ecosystem takes the opposite bet.</strong></p><ul><li><strong>Core concepts</strong> — content elements are Content Blocks, posts are pages, records are records, drafts are workspaces.</li><li><strong>One theme</strong> — search, forms, and login screens inherit the active preset instead of shipping their own CSS.</li><li><strong>On a policy leash</strong> — agents and APIs get declared, policy-controlled capabilities, never blank-cheque access to live data.</li></ul><p>One theme switch restyles everything; one workspace publish ships everything. The cards below go deeper on each extension.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/desiderio',
                    'media' => self::screenshot('feature-overview.png', 'The Desiderio ecosystem at a glance', 'TYPO3 14 backend showing the Desiderio design system alongside the Records, Workspaces, Skills, and API Core modules of the wider ecosystem.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'One ecosystem, many capabilities',
                    'eyebrow' => 'Ecosystem',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'layout-sidebar-right', 'title' => 'Record Lists', 'description' => 'Reshapes the TYPO3 Records module with Grid, Compact, Teaser, and your own custom view modes — added with TSconfig and a Fluid template, zero PHP.', 'link' => '{{page:features/records-list}}'],
                        ['icon' => 'settings', 'title' => 'MCP Server', 'description' => 'Gives AI assistants a clean, workspace-safe MCP surface over your pages, records, files, and editorial workflow — 44 bundled tools, every one mirrored on the TYPO3 CLI.', 'link' => '{{page:features/mcp-server}}'],
                        ['icon' => 'send', 'title' => 'Easy Workspace', 'description' => 'Turns workspace publishing into one button: editors review the page\'s pending changes, tick what to ship, and publish it together with every related record.', 'link' => '{{page:features/easy-workspace}}'],
                        ['icon' => 'book-open', 'title' => 'Blog', 'description' => 'A full publishing platform built entirely on core concepts — posts are pages, bodies are content elements, and workspaces stage everything, with no proprietary blog table.', 'link' => '{{page:features/blog}}'],
                        ['icon' => 'sparkles', 'title' => 'Desiderio + Innesto', 'description' => 'The design system itself: 244 content elements from 49 typed Fluid 5 components, runtime theming from site settings, and Innesto to graft shadcn registry components as new elements.', 'link' => '{{page:features/desiderio}}'],
                        ['icon' => 'search', 'title' => 'Solr Search', 'description' => 'Apache Solr\'s enterprise search speed, wrapped in shadcn components — results, facets, sorting, suggest, and accessible numbered pagination all inherit your active theme, light and dark.', 'link' => '{{page:features/solr}}'],
                        ['icon' => 'lock', 'title' => 'WorkOS Auth', 'description' => 'Enterprise single sign-on for both the TYPO3 frontend and backend, plus self-service B2B team management — one extension, two login surfaces, full AuthKit feature set.', 'link' => '{{page:features/workos}}'],
                        ['icon' => 'file', 'title' => 'Powermail Lab', 'description' => 'Editor-friendly, accessible multi-step forms in a complete shadcn design system, with Friendly Captcha bot protection that never phones home and a context-aware development bypass.', 'link' => '{{page:features/powermail}}'],
                        ['icon' => 'tag', 'title' => 'x402 Paywall', 'description' => 'Monetizes pages and API routes with the HTTP 402 standard — accept USDC micropayments from any wallet, measure revenue per page, and gate content for humans or AI agents.', 'link' => '{{page:features/x402-paywall}}'],
                        ['icon' => 'shield-check', 'title' => 'Capability Bridge', 'description' => 'Turns your CMS into a secure, machine-readable capability surface by registering policy-controlled CRUD resources for agents and external APIs from a declarative manifest.', 'link' => '{{page:features/api-capability-bridge}}'],
                        ['icon' => 'message-circle', 'title' => 'Agentation', 'description' => 'Brings visual annotation feedback into TYPO3 — point an AI agent at a page element with a selector, comment, and computed styles, then sync that context to Claude Code, Cursor, or any MCP agent.', 'link' => '{{page:features/agentation}}'],
                        ['icon' => 'monitor', 'title' => 'sg_apicore', 'description' => 'An attribute-driven API framework that turns content into structured data — REST endpoints, Auto-CRUD resources, generated OpenAPI specs, and token or session access, with no boilerplate.', 'link' => '{{page:features/sg-apicore}}'],
                        ['icon' => 'sparkles', 'title' => 'Skills', 'description' => 'Brings Anthropic-style agent skills into TYPO3 workspaces — define SKILL.md folders, import them from git, assign them to workspace stages for auto-review, and search them with Solr facets.', 'link' => '{{page:features/skillflow}}'],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'How the pieces fit together',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Built on core concepts, not a proprietary layer', 'content' => '<p>Every extension leans on TYPO3 primitives instead of inventing its own.</p><ul><li><strong>Posts are pages</strong> — a dedicated doktype with ordinary content elements.</li><li><strong>Elements are Content Blocks</strong> — real database columns and backend previews.</li><li><strong>Records stay records</strong> — Record Lists reshapes the existing module, not a replacement.</li></ul><p>Your TYPO3 skills transfer directly, with no parallel data model to migrate or untangle.</p>', 'open_by_default' => 1],
                        ['title' => 'One theme switch restyles the whole site', 'content' => '<p>Theming is OKLCH design tokens driven by site settings, switchable per site and subtree without a rebuild.</p><ul><li><strong>Everything opts in</strong> — Solr results, Powermail forms, and blog templates render through the same shadcn presets.</li><li><strong>Light and dark</strong> — change the preset once and both modes follow.</li><li><strong>Site-wide</strong> — search, forms, and articles all change together.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace-safe from top to bottom', 'content' => '<p>The whole stack uses TYPO3 Workspaces consistently, not ad-hoc \'draft\' flags.</p><ul><li><strong>Record Lists</strong> — overlays every row and colour-codes new, changed, moved, and deleted records.</li><li><strong>Easy Workspace</strong> — publishes a page\'s pending changes with their related records in one click.</li><li><strong>Blog</strong> — stages posts, tags, and authors.</li><li><strong>MCP &amp; API</strong> — stage agent writes by default, keeping live UIDs stable.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Agent-ready, but on a policy leash', 'content' => '<p>Built for AI agents without handing them the keys.</p><ul><li><strong>Structured tools</strong> — MCP Server, sg_apicore, and the Capability Bridge expose content as machine-readable tools.</li><li><strong>Declared limits</strong> — each declares the subsystems it may touch and defaults network access to the site itself.</li><li><strong>Workspace writes</strong> — agent changes route through workspaces.</li><li><strong>Human-in-the-loop</strong> — Agentation and Skillflow produce suggestions, never silent auto-applied changes.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Held to one engineering standard', 'content' => '<p>One stack with one quality bar, not a bag of unrelated plugins.</p><ul><li><strong>Modern target</strong> — TYPO3 14.3 LTS on PHP 8.3+, PHPStan at level max.</li><li><strong>Localised &amp; tested</strong> — English and German XLIFF, with unit and functional suites.</li><li><strong>Security as a feature</strong> — parameterised queries, CSRF-protected actions, redacted secrets, dev-only execution gates.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_headersection', [
                    'eyebrow' => 'With thanks',
                    'header' => 'Every one of these is open source we did not write',
                    'subheadline' => 'Desiderio is glue and taste on top of excellent work by other people. The ecosystem above leans on community extensions — and, for the entire AI layer, on Netresearch. We use their extensions because they are excellent, and each deserves a direct thank-you.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'The open-source shoulders Desiderio stands on',
                    'eyebrow' => 'With thanks',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'The AI layer — Netresearch', 'description' => 'nr_llm, nr_mcp_agent, nr_vault and t3_cowriter give TYPO3 a shared LLM foundation, a backend AI assistant, an encrypted secrets vault and an AI cowriter. The whole agentic story runs on their code. Thank you, Netresearch DTT GmbH.', 'link' => 'https://github.com/netresearch'],
                        ['icon' => 'book-open', 'title' => 'Content Blocks — TYPO3 Content Types Team', 'description' => 'Every one of the 244 elements is a Content Block: one declarative schema, automatic database columns, and a real backend preview. Thank you, Nikita Hovratov and the Content Types Team.', 'link' => 'https://github.com/nhovratov'],
                        ['icon' => 'file', 'title' => 'Powermail — in2code', 'description' => 'The proven form extension behind the Powermail Lab. Editors build multi-step forms in the backend; Desiderio only reskins them. Thank you, in2code — Stefan Busemann, Alex Kellner and Andreas Nedbal.', 'link' => 'https://github.com/in2code-de/powermail'],
                        ['icon' => 'menu', 'title' => 'News — Georg Ringer', 'description' => 'The definitive TYPO3 news extension drives the shadcn-styled teasers, detail views and archives. Thank you, Georg Ringer.', 'link' => 'https://github.com/georgringer/news'],
                        ['icon' => 'book-open', 'title' => 'Blog — TYPO3 GmbH', 'description' => 'Posts are pages, bodies are content elements, and workspaces stage everything. Desiderio just adds a themed skin on top. Thank you, TYPO3 GmbH.', 'link' => 'https://github.com/TYPO3GmbH/blog'],
                        ['icon' => 'search', 'title' => 'Apache Solr for TYPO3 — dkd', 'description' => 'Enterprise search speed with a mature TYPO3 integration, maintained for years by dkd Internet Service GmbH and the TYPO3-Solr team. Thank you.', 'link' => 'https://github.com/TYPO3-Solr/ext-solr'],
                        ['icon' => 'shield-check', 'title' => 'Friendly Captcha — Studio Mitte', 'description' => 'Privacy-first, proof-of-work bot protection that never phones home. Thank you to Studio Mitte for the TYPO3 extension, and to Friendly Captcha for the service.', 'link' => 'https://friendlycaptcha.com'],
                        ['icon' => 'monitor', 'title' => 'Visual Editor — friends of TYPO3', 'description' => 'Inline frontend editing across the content elements, powered by the community Visual Editor. Thank you to the friends of TYPO3 maintainers.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'shadcn/ui, Fluid & Tailwind', 'description' => 'The design language is shadcn/ui by shadcn; the template engine is Fluid 5 by Simon Praetorius; the utility CSS is Tailwind by Tailwind Labs. Open code, standing on open code.', 'link' => 'https://ui.shadcn.com'],
                    ],
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Start with the design system, grow into the ecosystem',
                    'description' => 'Install the free Desiderio core today, then add the pieces you need — records views, workspaces, search, auth, forms, payments, or the agent layer. Same theme, same rules, no rewrite.',
                    'cta_text' => 'Explore the source on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/desiderio',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureRecordsListPage(): array
    {
        return [
            'title' => 'Custom Record List View Types for TYPO3',
            'navTitle' => 'Record Lists',
            'slug' => '/features/records-list',
            'abstract' => 'Records List Types transforms the TYPO3 Records module with Grid, Compact, Teaser, and custom view modes. Editors browse the data the way it reads best — cards with thumbnails, dense tables, news-style teasers, or any layout you define in TSconfig. Configurable filters, drag-and-drop reordering, workspace-aware overlays, and accessible keyboard navigation are built in.',
            'description' => 'Grid, Compact, Teaser, and custom view types for the TYPO3 Records module. Filter records by field, drag-and-drop reorder, dark mode, workspace support.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Backend module enhancement',
                    'header' => 'Browse records the way they should be seen',
                    'subheadline' => 'Grid cards, compact tables, teaser listings, or custom layouts—all in the backend Records module, all configurable in TSconfig, none requiring PHP.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Records List Types: multiple views for the data editors work with every day',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Stop forcing editors into a one-size-fits-all table. Give them the view that matches the data.',
                    'content' => '<p>Stop forcing editors into one table. <strong>Records List Types</strong> adds four view modes to the TYPO3 Records module — pick the one that fits the data.</p><ul><li><strong>Grid</strong> — cards with thumbnails and field values, fully responsive.</li><li><strong>Compact</strong> — a dense, scrollable table for hundreds of records.</li><li><strong>Teaser</strong> — news-style cards with title, date, and excerpt.</li><li><strong>Custom</strong> — register your own views in TSconfig and Fluid, no PHP.</li></ul><p>Every view ships with configurable filters, drag-and-drop reordering, workspace-aware overlays, dark mode, and accessible keyboard navigation.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-records-list-types',
                    'media' => self::screenshot('feature-records-list.png', 'Records module with Grid and Teaser views in action', 'TYPO3 backend Records module showing three view mode buttons in the header and a grid of record cards below with thumbnails, field values, and action buttons.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Backend module enhancement',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'menu', 'title' => 'Four built-in view modes, plus custom views', 'description' => 'Grid for visual browsing with thumbnails and field cards, Compact for dense single-line tables built for hundreds of records, Teaser for news-style listings, and a base GenericView template that lets you register custom layouts in pure TSconfig and Fluid with zero PHP.', 'link' => ''],
                        ['icon' => 'search', 'title' => 'Field-level filtering without hand-coded SQL', 'description' => 'Editors toggle filters from the View menu to narrow records by title, hidden status, date ranges, categories, or any select field. TSconfig defines the filters; the extension handles persistence, workspace overlays, and post-overlay evaluation so staged edits remain searchable.', 'link' => ''],
                        ['icon' => 'arrow-right', 'title' => 'Drag-and-drop and keyboard reordering', 'description' => 'Mouse and keyboard support for reordering sortable records, with ARIA live regions and screen-reader announcements. The drag handle is keyboard-operable and WCAG 2.1 keyboard navigation is documented in the code—no users stranded on the List view fallback.', 'link' => ''],
                        ['icon' => 'moon', 'title' => 'Dark mode and workspace visibility out of the box', 'description' => 'All views render in both light and dark themes with token-based design. Workspace state (new, modified, moved, deleted) displays as color-coded header indicators; overlaid records stay visible and searchable in alternative views.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Grid View — visual browsing with thumbnails and field cards', 'content' => '<p>Each record becomes a design-system card: optional thumbnail, a title bar with drag handle and action menu, type-aware field values, and a UID/PID/language footer.</p><ul><li><strong>Responsive</strong> — one column on mobile, several on wide screens.</li><li><strong>Type-aware fields</strong> — booleans as badges, dates in monospace, long text full-width.</li><li><strong>Reorderable</strong> — keyboard and mouse drag-and-drop on any sortable table.</li><li><strong>State colours</strong> — hidden records muted; workspace states flagged new, modified, moved, or deleted.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Compact View — dense single-line tables with sticky columns and horizontal scroll', 'content' => '<p>A dense, responsive table built for scanning high-volume record sets.</p><ul><li><strong>Sticky columns</strong> — icon, UID, and title pinned left; actions pinned right.</li><li><strong>Horizontal scroll</strong> — extra fields scroll between the fixed columns, with scroll shadows.</li><li><strong>Sortable headers</strong> — click to sort ascending or descending.</li><li><strong>Readable rows</strong> — zebra striping; hidden records dimmed.</li></ul><p>Ideal for managing dozens to hundreds of records without filtering.</p>', 'open_by_default' => 0],
                        ['title' => 'Teaser View — news-style cards with title, date, and excerpt', 'content' => '<p>Minimal cards modelled on news and blog listings — perfect for tx_news or custom editorial tables.</p><ul><li><strong>At a glance</strong> — title, date with icon, and a two-line excerpt.</li><li><strong>Status cues</strong> — a UID pill plus a hidden/visible indicator.</li><li><strong>Quick actions</strong> — visibility, edit, and delete on every card.</li><li><strong>Theme-aware</strong> — adapts to light and dark mode automatically.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Custom view types — register layouts in TSconfig + Fluid, no PHP required', 'content' => '<p>Register your own view types with <strong>zero PHP</strong> — just TSconfig and an optional Fluid template.</p><ul><li><strong>Configure freely</strong> — label, icon, template, CSS, columns, and items-per-page.</li><li><strong>Reuse or build</strong> — extend Compact, Teaser, or Grid, or supply your own Fluid file.</li><li><strong>Scope by page</strong> — e.g. a Timeline only on Events, an Address Book only on Staff.</li><li><strong>Six ready examples</strong> — Timeline, Catalog, Address Book, Event List, Gallery, Dashboard.</li></ul><p>Custom views inherit the same sorting, pagination, selection, and actions as the built-ins.</p>', 'open_by_default' => 0],
                        ['title' => 'Record filters — configurable per table, workspace-aware, persisted to user preferences', 'content' => '<p>Field-level filtering in every view mode, opened from the View menu.</p><ul><li><strong>Filter by anything</strong> — title, hidden status, date range, categories, or any select field.</li><li><strong>Persisted</strong> — filter visibility is remembered per backend user.</li><li><strong>Workspace-aware</strong> — staged changes stay searchable before they publish.</li><li><strong>Zero-config defaults</strong> — built-in aliases, or add custom filters in TSconfig.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Drag-and-drop reordering with full keyboard support and ARIA announcements', 'content' => '<p>Any table with a TCA <code>sortby</code> field can be reordered by hand.</p><ul><li><strong>Mouse</strong> — grab the drag handle and drop.</li><li><strong>Keyboard</strong> — Space/Enter to grab, arrows to move, Enter to drop, Escape to cancel.</li><li><strong>Announced</strong> — ARIA live regions speak position and drop confirmation to screen readers.</li><li><strong>Two modes</strong> — switch between manual drag and field-based sorting.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace support with color-coded state indicators and post-overlay searching', 'content' => '<p>Fully workspace-aware on TYPO3 v14: every row is overlaid before search and filters run.</p><ul><li><strong>Colour-coded states</strong> — new (blue), modified (purple), moved (cyan), deleted (red).</li><li><strong>Search after overlay</strong> — draft rows replace live rows before filtering.</li><li><strong>Preview before publish</strong> — staged changes stay visible and searchable in every view.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Type-aware field display with configurable FAL thumbnails, language flags, and preview hints', 'content' => '<p>Every field type renders intelligently, configured per table in TSconfig.</p><ul><li><strong>Smart formatting</strong> — booleans as badges, dates in monospace, relations as counts, links clickable.</li><li><strong>Auto thumbnails</strong> — FAL references resolve to thumbnail URLs automatically.</li><li><strong>Context cues</strong> — language flags in the footer; a hint flags backend-only images.</li><li><strong>Per-table</strong> — Products show prices, Staff show portraits, News show feature images.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
composer require webconsulting/records-list-types:^1.0.3
./vendor/bin/typo3 extension:setup -e records_list_types
./vendor/bin/typo3 cache:flush',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Turn your Records module into a real editing experience',
                    'description' => 'Editors deserve to browse the data they manage in the view that matches it best. Grid cards for visual content, compact tables for dense data, teasers for editorial, and custom layouts for everything else — all configurable without touching PHP.',
                    'cta_text' => 'Install Records List Types',
                    'cta_link' => 'https://github.com/dirnbauer/typo3-records-list-types',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureMcpServerPage(): array
    {
        return [
            'title' => 'MCP Server for TYPO3 — AI-Ready Content API',
            'navTitle' => 'MCP Server',
            'slug' => '/features/mcp-server',
            'abstract' => 'An MCP server extension that gives AI assistants structured access to TYPO3 content, records, files, and workflows—without ever touching live data. Dozens of bundled tools plus a complete CLI mirror for shell scripts and CI pipelines.',
            'description' => 'Model Context Protocol server for TYPO3 14: workspace-safe tools for pages, records, files, and editorial workflow — over OAuth for any MCP client, or via CLI for scripts.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'AI & Automation',
                    'header' => 'Your LLM talks to TYPO3',
                    'subheadline' => 'Give Claude, Cursor, or any MCP client safe, structured access to pages, records, files, and publishing workflows—with workspace staging and full DataHandler safety baked in.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What is the Model Context Protocol?',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'It\'s Anthropic\'s open standard for how LLMs ask tools for information. MCP Server translates TYPO3 into that language.',
                    'content' => '<p><strong>MCP is Anthropic\'s open standard</strong> for how AI assistants call structured tools. This extension teaches TYPO3 to speak it.</p><ul><li><strong>Dozens of native tools</strong> — read page trees, search records, attach images, translate, audit metadata, publish workspaces.</li><li><strong>Workspace-safe</strong> — every write stages first, so live data stays untouched.</li><li><strong>Any client</strong> — OAuth 2.1 for remote clients, stdio for local dev.</li><li><strong>Same tools in CI</strong> — shell scripts and GitHub Actions use the identical CLI interface.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-mcp-server',
                    'media' => self::screenshot('feature-mcp-server.png', 'MCP Server backend module', 'TYPO3 User menu showing the MCP Server module with endpoint URL, OAuth discovery links, health checks, and token management interface.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'AI & Automation',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'lock', 'title' => 'Workspace-safe by default', 'description' => 'Every content write stages in a TYPO3 workspace first. Live content never changes until someone clicks publish. Your editors review before anything goes live.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'TCA-first tool design', 'description' => 'Tools read their field definitions straight from TYPO3 TCA, not handwritten adapters. News records, custom FlexForms, language overlays—all work without MCP-specific code.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Same tools everywhere', 'description' => 'Every MCP tool works in Claude Desktop, Cursor, n8n, or from the CLI via vendor/bin/typo3 mcp:command. One surface, many different clients.', 'link' => ''],
                        ['icon' => 'database', 'title' => 'No LLM gets lost in translation', 'description' => 'Schemas are crystal clear. Every tool has a description, a JSON Schema input definition, and MCP annotations that tell the AI whether it reads, writes, or deletes. Because it speaks standard MCP, any compliant client can drive it.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'A full toolbox of MCP tools across nine groups', 'content' => '<p>Tools span <strong>nine groups</strong>, each available as an MCP endpoint and a CLI shortcut.</p><ul><li><strong>Navigate &amp; inspect</strong> — GetCapabilities, ListTables, GetTableSchema, GetFlexFormSchema.</li><li><strong>Read &amp; write</strong> — record reads with TCA context, structured writes, bulk edits.</li><li><strong>Publish &amp; files</strong> — workspace review, sandboxed file handling, content audit.</li><li><strong>Operate</strong> — system diagnostics, site and extension admin, DDEV-only dev helpers.</li></ul><p>Tool names are PascalCase and mirror what editors already know from the backend.</p>', 'open_by_default' => 1],
                        ['title' => 'Workspace transparency and editorial safety', 'content' => '<p>Record writes go into a workspace by default — the extension picks or creates one automatically.</p><ul><li><strong>Live UIDs only</strong> — clients see the stable UID, never the internal workspace version.</li><li><strong>Dry-run first</strong> — publish and rollback preview what would happen before doing it.</li><li><strong>Strict in production</strong> — live edits need an explicit workspace_id and admin rights.</li><li><strong>Relaxed locally</strong> — opt into live edits on DDEV when you want speed.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'OAuth 2.1 + PKCE for remote clients, stdio for local', 'content' => '<p>Two authentication paths, both gated by TYPO3 permissions.</p><ul><li><strong>Remote</strong> — OAuth 2.1 with PKCE at <code>/mcp</code>; the first request logs in with your backend credentials.</li><li><strong>Local</strong> — clients like Cursor run <code>mcp:server</code> as a trusted subprocess, no OAuth.</li><li><strong>Auto-discovery</strong> — the OAuth server and protected resources are found automatically.</li><li><strong>Backend module</strong> — endpoint URL, one-click Cursor setup, Claude Desktop config, health checks, tokens.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Capability manifest: declare what your MCP server can do', 'content' => '<p>A YAML manifest (<code>Configuration/Capabilities.yaml</code>) declares what each tool may do.</p><ul><li><strong>Toggle subsystems</strong> — database:read/write, file:write, render:frontend, workspace:write, and more.</li><li><strong>Instant effect</strong> — remove database:write and every writing tool stops at once.</li><li><strong>Self-host by default</strong> — outbound HTTP stays internal until you opt in.</li><li><strong>Inspect live</strong> — check the active surface with <code>mcp:get-capabilities --json</code>.</li></ul><p>Not a security boundary — TYPO3 permissions still apply — but a simple way to harden without code.</p>', 'open_by_default' => 0],
                        ['title' => 'Complete CLI mirror: every tool from the shell', 'content' => '<p>Every MCP tool is also a TYPO3 console command.</p><ul><li><strong>Direct shortcuts</strong> — <code>mcp:read-table</code>, <code>mcp:write-table</code>, <code>mcp:search</code>, <code>mcp:list-workspaces</code>, and more.</li><li><strong>Generic runner</strong> — <code>mcp:tool &lt;ToolName&gt;</code> with <code>--param</code> and <code>--params</code> flags.</li><li><strong>Three output modes</strong> — pretty for humans, plain for logs, JSON for jq, agents, and CI.</li><li><strong>Script-safe</strong> — JSON carries an ok/error envelope; param files stay inside your project root.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'DDEV local-mode relaxations for faster feedback loops', 'content' => '<p>On DDEV or Development context, three safety nets relax for speed.</p><ul><li><strong>Live writes</strong> — record writes default to live instead of requiring a draft workspace_id.</li><li><strong>Any storage</strong> — file operations accept any path, not just fileadmin/mcp.</li><li><strong>Open outbound HTTP</strong> — UploadFileFromUrl and RenderRecord work on local and staging hosts.</li><li><strong>Production stays strict</strong> — force strict mode anywhere with the <code>mcpServer.strictSandbox</code> flag.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Content audit, preview rendering, and import tooling', 'content' => '<p>Tools that close the edit-check-iterate loop without a manual backend refresh.</p><ul><li><strong>ContentAudit</strong> — flags missing alt text, descriptions, and hidden slugs, sorted by severity.</li><li><strong>GetPreviewUrl</strong> — a signed workspace preview link without leaving the chat.</li><li><strong>RenderRecord</strong> — the real rendered HTML, so the AI sees frontend output before publishing.</li><li><strong>ImportContent / ImportFromUrl</strong> — turn text, Markdown, or HTML into content elements.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'File sandbox and secure uploads', 'content' => '<p>File tools are sandboxed to <code>fileadmin/mcp/</code> by default.</p><ul><li><strong>Path-traversal protection</strong> — stays on even in local mode.</li><li><strong>SSRF-checked uploads</strong> — UploadFileFromUrl validates the remote host against your outbound policy.</li><li><strong>Relaxable on DDEV</strong> — allow any host for staging and test servers.</li><li><strong>FAL-aware</strong> — attaching an image creates a real sys_file_reference, not a broken hardlink.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require hn/typo3-mcp-server
vendor/bin/typo3 extension:activate mcp_server
# Then in the TYPO3 backend, go to User > MCP Server to see your endpoint and set up clients.',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Ready to give your LLM a TYPO3 voice?',
                    'description' => 'Install the extension, open the MCP Server module in the TYPO3 backend, and paste the config into Claude Desktop, Cursor, or n8n. The endpoint is OAuth-protected, so your credentials log in automatically on first use.',
                    'cta_text' => 'Install from Composer',
                    'cta_link' => 'https://packagist.org/packages/hn/typo3-mcp-server',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureEasyWorkspacePage(): array
    {
        return [
            'title' => 'Easy Workspace — confident publishing from the TYPO3 toolbar',
            'navTitle' => 'Easy Workspace',
            'slug' => '/features/easy-workspace',
            'abstract' => 'Easy Workspace brings one-click publishing to TYPO3 workspaces. Editors review pending page and content-element changes in a friendly interface, select what to publish, and hit one button—together with all their related records. No custom versioning layer, no complicated workflows.',
            'description' => 'TYPO3 14 workspace publishing toolbar for confident editors. Review pending changes, select rows, publish together—no custom versioning layer.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Backend extension',
                    'header' => 'Editors see what they\'re about to publish',
                    'subheadline' => 'A toolbar button and a backend module let editors review pending workspace changes for the current page or news article, then publish everything together with one click—exactly as they built it.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Why workspace-based editing matters',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Staging in TYPO3 workspaces is how professional teams keep drafts safe while the live site runs. The problem: default TYPO3 workspace publishing is buried three screens deep and shows everything at once. Easy Workspace brings it to the top bar and shows only what\'s pending.',
                    'content' => '<p>Before hitting publish, editors need to see exactly what changed. <strong>Easy Workspace</strong> puts that review in the top-right corner — no hunting through nested modules.</p><ul><li><strong>Confirm readiness</strong> — every content element you added is staged and ready.</li><li><strong>Children included</strong> — inline children publish with their parents.</li><li><strong>No surprises</strong> — see exactly which changes go live together.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-webcon-easy-workspace',
                    'media' => self::screenshot('feature-easy-workspace.png', 'Pending changes review panel', 'TYPO3 backend with Easy Workspace toolbar dropdown showing pending page and content-element changes with publish and discard options per row.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Backend extension',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Publish is one click, not three screens', 'description' => 'The toolbar puts the pending-changes dropdown at the editor\'s fingertips. No hunting through modules, no re-learning which sub-tab shows what. Press the paper-plane icon and review happens immediately.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'Editors know what publishes together', 'description' => 'Easy Workspace shows the exact page and content elements that will publish as one batch. Inline children are collected and grouped automatically, so editors never discover after publish that a subelement got left behind.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Built on TYPO3\'s own versioning, not a black box', 'description' => 'The extension uses TYPO3\'s standard DataHandler and built-in workspace versioning—no custom publishing pipeline, no parallel content store, no opaque layer between the editor and their data.', 'link' => ''],
                        ['icon' => 'globe', 'title' => 'Works for pages and news articles alike', 'description' => 'The same toolbar and module support both page trees and georgringer/news article detail views. Editors switching between page editing and news management see the same publish interface.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Paper-plane toolbar dropdown with live change count', 'content' => '<p>A paper-plane icon in the top bar lights up the moment pending changes exist on the current page or article.</p><ul><li><strong>One-click review</strong> — opens a Lit-rendered dropdown listing every pending record over AJAX.</li><li><strong>Live count</strong> — a lightweight polling endpoint keeps the badge current.</li><li><strong>Out of the way</strong> — the toolbar hides itself entirely in the Live workspace.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Pending items review table with checkboxes and type labels', 'content' => '<p>Every changed record — page, content elements, and inline children — lands in one dense table.</p><ul><li><strong>TCA-driven labels</strong> — type names come from TCA, not hand-coded strings.</li><li><strong>Clear state</strong> — a changed-versus-live badge and title on every row.</li><li><strong>Selected by default</strong> — most editors publish everything; deselect any exploratory change.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Bulk publish with parent-before-child ordering', 'content' => '<p>Publish sends the selected records to TYPO3\'s DataHandler in a deliberate order.</p><ul><li><strong>Parents first</strong> — pages and top-level elements publish before their inline children.</li><li><strong>No broken links</strong> — foreign keys point at live records, not workspace placeholders.</li><li><strong>Predictable</strong> — each request runs in the active workspace and is capped server-side.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Per-row discard without touching the rest', 'content' => '<p>Not ready? Discard a single row and leave everything else staged.</p><ul><li><strong>Native discard</strong> — runs TYPO3 v14\'s own discard command on just that workspace version.</li><li><strong>No collateral</strong> — sibling records are untouched.</li><li><strong>Your call per row</strong> — publish or discard each record independently, never all-or-nothing.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Field-level diff modal with history timeline and rollback', 'content' => '<p>Click a row to see exactly what changed, old value beside new.</p><ul><li><strong>Inline diffs</strong> — longer text is diffed word by word.</li><li><strong>History timeline</strong> — edits to that record, read from sys_history via the RecordHistory service.</li><li><strong>Roll back</strong> — restore a single field or the whole record without disturbing the page.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Eye icon to locate and highlight a record in preview', 'content' => '<p>An eye icon on each row finds that record in the frontend preview.</p><ul><li><strong>Jump to it</strong> — scrolls to the element by its #c{uid} anchor and outlines it briefly.</li><li><strong>VE-aware</strong> — targets the Visual Editor iframe when present, else the standard Viewpage preview.</li><li><strong>Children resolve up</strong> — child rows highlight their parent element, so the target is always visible.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace chip and a module with three focused subviews', 'content' => '<p>A header chip shows the active workspace, so you never slip back to Live unawares.</p><ul><li><strong>Open items</strong> — the pending publish queue.</li><li><strong>All records</strong> — a read-only inventory of every scoped record on the page.</li><li><strong>Checks &amp; diagnostics</strong> — a workspace integrity scan plus a manual risk list.</li></ul><p>The module lives under Content, just below the standard Workspaces publish module.</p>', 'open_by_default' => 0],
                        ['title' => 'News article scope with a per-article publish queue', 'content' => '<p>Editing a georgringer/news article? Easy Workspace narrows the scope to that article.</p><ul><li><strong>Related content too</strong> — includes elements linked via tx_news_related_news.</li><li><strong>Just this article</strong> — the toolbar shows its pending changes, not the whole page tree.</li><li><strong>Jump straight in</strong> — pass a newsUid parameter to open a specific article\'s queue.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Database diagnostics with grouped health checks and a manual risk list', 'content' => '<p>The Checks and diagnostics view scans the workspace for integrity problems.</p><ul><li><strong>Catches the usual suspects</strong> — stale version fields, orphan versions, missing parents, ownerless file references, duplicates.</li><li><strong>Reports-style results</strong> — grouped into pass, warning, and error states.</li><li><strong>Manual risk list</strong> — flags edge cases the scanner cannot judge, like overwritten FAL files.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/webcon-easy-workspace
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Bring confident publishing to your TYPO3 editors',
                    'description' => 'Easy Workspace turns TYPO3 workspace publishing from an expert task into a one-button flow. Install it free on your next project—it ships ready to run on TYPO3 14.3, with sensible defaults out of the box.',
                    'cta_text' => 'Install for free from GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/typo3-webcon-easy-workspace',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureBlogPage(): array
    {
        return [
            'title' => 'Blog Extension Features — Pages as Posts, Workspaces, and Comments',
            'navTitle' => 'Blog',
            'slug' => '/features/blog',
            'abstract' => 'The TYPO3 Blog Extension transforms your site into a publishing platform built entirely on core concepts. Posts are pages, content elements are content elements, and workspaces let editors stage posts and authors before publishing — all without a single proprietary database table.',
            'description' => 'The TYPO3 Blog Extension: posts as pages, workspaces for staging, 20 plugins, moderated comments, and full compatibility with Desiderio.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Extensions',
                    'header' => 'Your blog, built on TYPO3 core',
                    'subheadline' => 'Posts are pages, content elements are content elements, and editors manage everything in the page module — just like the rest of your site.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'A blog for TYPO3, built entirely on core concepts',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Posts as pages, content elements as content elements, and editors who never leave the page module.',
                    'content' => '<p>One principle: <strong>if you know TYPO3, you already know how to run a blog.</strong> No proprietary editors, no custom tables, no workflow living outside your page tree.</p><ul><li><strong>Posts are pages</strong> — doktype 137; authors, tags, and categories are core concepts.</li><li><strong>Your full toolkit</strong> — every content element and backend layout works inside a post.</li><li><strong>Stage in workspaces</strong> — posts, tags, and authors; comments stay live-editable.</li><li><strong>Batteries included</strong> — backend modules plus 20 plugins for lists, filters, archives, sidebars, and RSS.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'See on GitHub',
                    'button_link' => 'https://github.com/TYPO3GmbH/blog',
                    'media' => self::screenshot('feature-blog.png', 'Blog post edit in the page module with workspace overlay', 'A blog post edit screen in the TYPO3 page module, showing the post title in the page header along with metadata badges for publish date, categories, tags, and author.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Extensions',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'file', 'title' => 'Posts are pages', 'description' => 'Create and manage posts in the page module with every content element. Workspace staging for posts, tags, and authors. Live-editable comments that never block on editorial coordination.', 'link' => ''],
                        ['icon' => 'menu', 'title' => '20 plugins for every layout', 'description' => 'All 20 Extbase plugins handle lists, filters, archives, sidebars, and RSS feeds. Filter by category, tag, author, or date. Paginated lists with clean semantics for search engines.', 'link' => ''],
                        ['icon' => 'history', 'title' => 'Workspace-ready editorial flow', 'description' => 'Posts, tags, and authors stage in workspaces before publishing. Comments stay live-editable. Moderation workflow with author and admin email notifications. Optional Google reCAPTCHA.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Posts as pages with a custom doktype', 'content' => '<p>Blog posts are not rows in a proprietary table — they are TYPO3 pages (doktype 137/138).</p><ul><li><strong>Edit like any page</strong> — create them in the page module and drag them into the tree.</li><li><strong>Same permissions</strong> — govern them with the access rules you already use.</li><li><strong>Full element library</strong> — every content element and backend layout works inside a post.</li><li><strong>Metadata up front</strong> — the page header surfaces publish date, tags, categories, and author.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'All your content elements, all your layouts', 'content' => '<p>Every piece of a post is a standard content element — no walled-garden editor to learn.</p><ul><li><strong>Familiar building blocks</strong> — an article hero for the header, then text, images, and quotes.</li><li><strong>Any custom element</strong> — anything registered on your site works inside a post.</li><li><strong>Backend layouts apply</strong> — exactly as they do on campaign or landing pages.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace-safe staging and publishing', 'content' => '<p>Posts, tags, and authors are workspace-aware: create, review in preview, then publish together.</p><ul><li><strong>Invisible until ready</strong> — staged changes never show on the live site.</li><li><strong>Comments excepted</strong> — visitor comments stay live-editable, so readers keep commenting.</li><li><strong>Clean separation</strong> — coordinate a publication day without drafts bleeding into production.</li></ul>', 'open_by_default' => 0],
                        ['title' => '20 Extbase plugins: list, filter, detail, sidebar, and feed', 'content' => '<p>20 Extbase plugins cover the whole blog journey, most right in the element wizard.</p><ul><li><strong>Lists</strong> — all posts, the latest N, or a paginated month-by-month archive.</li><li><strong>Filters</strong> — by category, tag, or author.</li><li><strong>Sidebars</strong> — related posts, tag clouds, category lists, recent posts, comment form.</li><li><strong>Feeds</strong> — RSS for subscribers.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Categories, tags, and authors with rich metadata', 'content' => '<p>Posts organise via system categories and custom blog tags, both versioned in workspaces.</p><ul><li><strong>Authors are records</strong> — avatar, social links, a bio, and an author detail page.</li><li><strong>Related posts</strong> — scored by shared categories and tags to keep readers moving.</li><li><strong>Reader filtering</strong> — by category, tag, author, or archive date.</li><li><strong>Staged together</strong> — introduce a new author in the same workspace as their first posts.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Moderated comments with reCAPTCHA and notifications', 'content' => '<p>A built-in comment system moves each comment through pending, approved, declined, or deleted.</p><ul><li><strong>Notifications</strong> — email the post author and a configured admin on every new comment.</li><li><strong>Spam control</strong> — optional Google reCAPTCHA per site.</li><li><strong>Always live</strong> — comments write to the live database, never hidden behind staging.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Site sets and Fluid templates for three setups', 'content' => '<p>Three public site sets cover any setup.</p><ul><li><strong>standalone</strong> — a dedicated blog.</li><li><strong>integration</strong> — layer a blog into an existing site.</li><li><strong>bootstrap-53</strong> — the shipped Bootstrap 5.3 frontend templates.</li></ul><p>Every template is plain Fluid — override it in your sitepackage. Desiderio adds shadcn-styled, dark-mode templates that match the active preset.</p>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'composer.json',
                    'code' => 'composer require t3g/blog',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Your blog is one Composer command away',
                    'description' => 'The Blog Extension is free under GPL-2.0, ships complete with backend modules, 20 Extbase plugins, and full Workspaces integration. Install it via Composer and use the setup module to create a fully configured blog in minutes — or customize every Fluid template in your sitepackage. Thank you to TYPO3 GmbH for the Blog Extension.',
                    'cta_text' => 'Get started on GitHub',
                    'cta_link' => 'https://github.com/TYPO3GmbH/blog',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureDesiderioPage(): array
    {
        return [
            'title' => 'Features: Desiderio + Innesto for TYPO3',
            'navTitle' => 'Desiderio + Innesto',
            'slug' => '/features/desiderio',
            'abstract' => 'Desiderio is a complete TYPO3 design system: 244 ready-to-use content elements built from 49 typed Fluid 5 components, a runtime theming layer driven by TYPO3 site settings, and open extensibility via Innesto—which grafts shadcn/ui registry components as new elements without a frontend build step on your site.',
            'description' => '244 shadcn/ui-styled content elements for TYPO3 14.3, extensible via Innesto. Runtime theming, Content Blocks 2.2, typed Fluid 5 components—all GPL-2.0.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Theme + Design System',
                    'header' => '244 elements. Extensible. Themed at runtime.',
                    'subheadline' => 'Desiderio brings a complete shadcn/ui-inspired design system to TYPO3 v14.3+: 244 finished content elements, 49 atomic Fluid 5 components, and an extensibility layer (Innesto) that grafts components from shadcn registries as new Content Blocks in one command.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What is Desiderio + Innesto',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'A complete, open-source design system for TYPO3 that you can extend in minutes.',
                    'content' => '<p><strong>Desiderio</strong> is a complete, working editorial system for TYPO3 v14.3+ — not a template kit you still have to build.</p><ul><li><strong>244 elements, no build step</strong> — page templates, optional Blog/News/Solr/Powermail overrides, and seeded demo content.</li><li><strong>Extensible via Innesto</strong> — graft a component from any shadcn registry (shadcn/ui, Magic UI, blocks.so) as a new Content Block.</li><li><strong>Yours to keep</strong> — both are free and open-source under GPL-2.0-or-later.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'Get Desiderio on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/desiderio',
                    'media' => self::screenshot('feature-desiderio.png', 'Desiderio in the TYPO3 Visual Editor', 'The TYPO3 Visual Editor showing a Desiderio hero element selected for inline editing, with the frontend rendering visible on the right side of the screen.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Theme + Design System',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => '244 elements, zero build step', 'description' => 'Every content element—heroes, pricing tables, testimonials, forms, charts, footers—ships finished and ready to use. No template work, no design system assembly. Commit to TYPO3, enable the site sets, and editors start composing pages right away.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'Runtime-switchable theme presets', 'description' => 'Pick a design on ui.shadcn.com/create, paste the preset into TYPO3 site settings, and the whole site repaints—colors, radius, density, focus rings, fonts—without a rebuild. Fifteen presets are bundled (five from the shadcn create page plus ten house designs), switchable per site or per page subtree. Multiple icon libraries ship in the box.', 'link' => ''],
                        ['icon' => 'menu', 'title' => 'Atomic components, typed contracts', 'description' => '17 atoms (button, badge, input, avatar), 28 molecules (card, accordion, form field), and 4 layout primitives compose into all 244 elements. Each is a Fluid 5 component with typed f:argument contracts, so a single audit can verify every element and CI can reject any template that breaks the API.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Extend it. In minutes. With AI.', 'description' => 'Innesto grafts a shadcn registry component as a new Content Block in one command. The CLI fetches the JSON schema, converts the styling to semantic tokens, and scaffolds the element; the optional --ai flag finishes the React-to-Fluid conversion. Your custom element inherits the active Desiderio preset automatically.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => '49 typed Fluid 5 components in atomic layers', 'content' => '<p>The component system follows atomic design at the Fluid 5 level.</p><ul><li><strong>17 atoms</strong> — button, badge, input, icon, avatar, link, image, label (<code>d:atom</code>).</li><li><strong>28 molecules</strong> — card, accordion, table, alert, form controls (<code>d:molecule</code>).</li><li><strong>4 layout primitives</strong> — under <code>d:layout</code>.</li><li><strong>Typed contracts</strong> — every component declares <code>f:argument</code> types: an enforced API, not a convention.</li></ul><p>All 244 elements build from these layers, so one audit can verify every element.</p>', 'open_by_default' => 1],
                        ['title' => '244 finished content elements across ten categories', 'content' => '<p>244 editor-facing elements, grouped into clear categories.</p><ul><li><strong>Marketing</strong> — heroes, feature blocks, pricing, trust and social proof.</li><li><strong>Data</strong> — dashboard elements with chart helpers.</li><li><strong>Structure</strong> — navigation, footers, legal pages, forms, editorial content.</li></ul><p>Each appears in the New Content Element wizard with a backend preview — production-styled and themed by the active preset, never a wireframe.</p>', 'open_by_default' => 0],
                        ['title' => 'Runtime theme presets driven by TYPO3 site settings', 'content' => '<p>Theming is pure CSS tokens applied at runtime, chosen in TYPO3 site settings.</p><ul><li><strong>Repaint, no rebuild</strong> — a preset change updates colours, radius, density, focus rings, and fonts instantly.</li><li><strong>15 presets bundled</strong> — five from ui.shadcn.com/create, ten house designs, plus a custom slot.</li><li><strong>Per subtree</strong> — run separate campaigns or brands in their own theme from one install.</li><li><strong>Icon-agnostic</strong> — semantic icon keys let the library change without rewriting records.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Innesto: graft shadcn registry components as Content Blocks', 'content' => '<p>Innesto makes Desiderio extensible — no frontend build step on your site.</p><ul><li><strong>One command</strong> — <code>innesto:add &lt;component&gt;</code> fetches the JSON, converts styling to tokens, and scaffolds the element.</li><li><strong>Any registry</strong> — shadcn/ui, Magic UI, blocks.so, or anything that publishes JSON.</li><li><strong>Finishing pass</strong> — translate markup to Fluid and model props by hand, or let <code>--ai</code> do it.</li><li><strong>On-theme</strong> — every graft uses the active Desiderio preset automatically.</li></ul><p>Presentational components like marquees, logo clouds, and bento grids are the natural fit.</p>', 'open_by_default' => 0],
                        ['title' => 'Content Blocks 2.2: schema-first elements with backend previews', 'content' => '<p>All 244 elements are Content Blocks (friendsoftypo3/content-blocks ^2.2), not traditional plugins.</p><ul><li><strong>Declarative schemas</strong> — <code>config.yaml</code> with automatic database columns.</li><li><strong>Backend previews</strong> — editors see the element before publishing.</li><li><strong>Explicit child tables</strong> — collection records map to named tables, no guessing.</li><li><strong>Portable</strong> — export an element with its records; the schema handles table creation elsewhere.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Integrations: News, Blog, Solr, and Powermail with Brevo + Friendly Captcha', 'content' => '<p>The extensions you already run get the same shadcn treatment, via opt-in site sets.</p><ul><li><strong>Auto-activating</strong> — templates for georgringer/news, t3g/blog, Solr, and in2code/powermail switch on when installed.</li><li><strong>Forms</strong> — Form Framework templates with Friendly Captcha and a Brevo double-opt-in finisher.</li><li><strong>Theme follows</strong> — switch the preset and news lists, search results, and forms switch with it.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Seeding and CLI for idempotent demo content', 'content' => '<p>Symfony console commands automate the heavy lifting.</p><ul><li><strong>styleguide:seed</strong> — builds the full 244-element demo site from YAML, idempotent and live-safe.</li><li><strong>starter:seed</strong> — a corporate starter site structure with demo content.</li><li><strong>blog:seed-pages</strong> — normalises an existing Blog tree to Desiderio layouts.</li><li><strong>Guard rails</strong> — seeders refuse to run in a workspace or in Production without <code>--allow-production</code>.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install-desiderio.sh',
                    'code' => 'composer require webconsulting/desiderio
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush

# Optional: seed the demo site with all 244 elements under page 1
vendor/bin/typo3 desiderio:styleguide:seed --parent=1

# Optional: graft a shadcn registry component as a new Content Block
composer require dirnbauer/innesto
vendor/bin/typo3 innesto:add magicui/marquee --ai',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Ready to extend your TYPO3 site?',
                    'description' => 'Desiderio is free and open-source under GPL-2.0-or-later. You get all 244 elements, the fifteen bundled theme presets, the optional Blog, News, Solr, and Powermail integration sets, and the seeding tools—no license gate, no build step on your site. Innesto is open-source too, so extending Desiderio costs nothing but a command.',
                    'cta_text' => 'Install for free, or explore GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/desiderio',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureSolrPage(): array
    {
        return [
            'title' => 'Apache Solr Search Integration for TYPO3',
            'navTitle' => 'Solr Search',
            'slug' => '/features/solr',
            'abstract' => 'Apache Solr brings enterprise search speed to TYPO3 while Desiderio wraps it in shadcn components. Results, facets, sorting, the suggest dropdown, and accessible numbered pagination all inherit your active theme preset automatically, light and dark mode included.',
            'description' => 'Enterprise search for TYPO3 with shadcn-styled results, faceting, numbered pagination, AJAX refinement, and zero styling overhead.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Integration',
                    'header' => 'Enterprise search that matches your design system',
                    'subheadline' => 'Solr results render in shadcn components that inherit your theme preset in real time, no template work required.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Search as part of your design system, not a plugin bolted on',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Apache Solr for TYPO3 — built and maintained for years by dkd Internet Service GmbH and the TYPO3-Solr team — is the production search engine with millisecond response times. Desiderio gives it a complete shadcn template set.',
                    'content' => '<p><strong>Solr finds the content; Desiderio styles the interface.</strong></p><ul><li><strong>Fully themed</strong> — results, facets, sort, per-page, and pagination follow your preset, light and dark.</li><li><strong>Ready to use</strong> — the template set ships complete, no CSS to write.</li><li><strong>AJAX refinement</strong> — faceting, sorting, and paging refresh without a full reload.</li><li><strong>Repaints with the site</strong> — switch from Lagoon to Midnight and the results page follows.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/TYPO3-Solr/ext-solr',
                    'media' => self::screenshot('feature-solr.png', 'Solr search results page in the Lagoon theme preset', 'Search results page showing a numbered pagination toolbar, result cards with title, URL and snippet excerpt, a per-page switcher dropdown, and a facets sidebar, all in the Lagoon theme palette with proper contrast and spacing.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Integration',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Zero styling overhead', 'description' => 'The entire search UI - results, facets, pagination, sorting, per-page - is pre-styled in shadcn components. Copy the template set, point your results page at the Desiderio Solr Results template in TypoScript, and you are live. No CSS writing, no class conflicts, no theme-switching bugs.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'One theme, everywhere', 'description' => 'Search results inherit the active page preset and dark-mode preference automatically. Switch the site to Midnight and the pagination, facet styling, card backgrounds, and button hovers switch with it. Everything is driven by the same shadcn tokens as the rest of the site.', 'link' => ''],
                        ['icon' => 'search', 'title' => 'Faceted refinement over AJAX', 'description' => 'Solr facets render as a themed sidebar of clickable options with live result counts. Selecting an option, removing a filter, sorting, or paging refreshes the results over AJAX without leaving the page. Configure which facets appear in TypoScript and the template follows.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Accessibility built in', 'description' => 'Numbered pagination is wrapped in a nav landmark and marks the current page with aria-current, each link carrying a descriptive aria-label. The active-filter chips, facet counts, and status messages expose screen-reader text, and focus rings are present on every interactive control.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Search form with live suggest dropdown', 'content' => '<p>A search input with icon and submit button opens a live suggest dropdown as you type.</p><ul><li><strong>Grouped suggestions</strong> — under translated labels for Pages, News, and Addresses.</li><li><strong>Configurable header</strong> — a \'Top Results\' label leads the list.</li><li><strong>Straight to results</strong> — submit or pick a suggestion, no custom code.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Styled result cards with snippet excerpt and type badge', 'content' => '<p>Each result is a card built entirely from Desiderio tokens.</p><ul><li><strong>Scannable</strong> — title link, result URL, and a snippet highlighted around your term.</li><li><strong>Type badge</strong> — labels the source as Pages, News, or Addresses; files list MIME type.</li><li><strong>Semantic markup</strong> — titles use heading tags; a token tweak reflows every card.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Numbered pagination with smart truncation', 'content' => '<p>Page numbers in order, with previous and next controls.</p><ul><li><strong>Smart truncation</strong> — long ranges collapse behind an ellipsis so the bar never overflows.</li><li><strong>Clear current page</strong> — solid primary background; the rest use the outline style.</li><li><strong>Accessible</strong> — a nav landmark with aria-label, aria-current, and a \'Go to page N\' label per link.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Sort dropdown and per-page switcher in the toolbar', 'content' => '<p>A toolbar above the results exposes sorting and a per-page switcher.</p><ul><li><strong>Sort menu</strong> — relevance, date, title, and anything Solr returns, with the active option and direction marked.</li><li><strong>Per-page switcher</strong> — a native select that resubmits on change.</li><li><strong>Config-driven</strong> — the options come from your Solr per-page settings, not hard-coded.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Active filters with one-click removal', 'content' => '<p>When facets narrow a search, a \'Narrowed by\' bar lists each applied filter.</p><ul><li><strong>Removable chips</strong> — each filter is a themed link with a clear affordance and screen-reader text.</li><li><strong>Reset in one step</strong> — a \'Remove all filters\' action clears everything.</li><li><strong>No full reload</strong> — removing a filter refreshes the results over AJAX.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Faceting sidebar with live result counts', 'content' => '<p>A sidebar lists the facets Solr is configured to return, each in its own section.</p><ul><li><strong>Live counts</strong> — every option shows its result count alongside the label.</li><li><strong>Show more</strong> — reveal options beyond the configured limit.</li><li><strong>AJAX refine</strong> — choosing an option adds it to the active filters and updates results.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Did-you-mean and auto-correct messaging', 'content' => '<p>Spelling help when a query comes up short.</p><ul><li><strong>Did you mean</strong> — each suggestion is a link that re-runs the search corrected.</li><li><strong>Auto-correct notice</strong> — explains when results are shown for a corrected term.</li><li><strong>Theme-aware</strong> — translated strings that stay readable in light and dark mode.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Frequent and recent searches', 'content' => '<p>Optional panels that surface past queries beside the results.</p><ul><li><strong>Frequent searches</strong> — styled to match the facet sidebar.</li><li><strong>Last searches</strong> — recent queries as themed links.</li><li><strong>Click to re-run</strong> — each starts a new search and refreshes over AJAX.</li><li><strong>Individually gated</strong> — each panel has its own TypoScript switch.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require apache-solr-for-typo3/solr:^14.0 webconsulting/desiderio:^2.0
# Configure a Solr connection in your site config
# Point your search results page to the EXT:desiderio Solr Results template
# Run the Solr indexing queue to populate the index',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Bring Solr search into your design system',
                    'description' => 'The template set ships with Desiderio. Solr itself is open source and runs locally or on a dedicated search node. The Desiderio layout, components, and theme integration do all the styling work. Thank you to dkd Internet Service GmbH and the TYPO3-Solr team for the extension.',
                    'cta_text' => 'See the technical facts',
                    'cta_link' => '{{page:technical-features}}',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureWorkosPage(): array
    {
        return [
            'title' => 'WorkOS Authentication for TYPO3 — Enterprise SSO for Your Backend and Frontend',
            'navTitle' => 'WorkOS Auth',
            'slug' => '/features/workos',
            'abstract' => 'WorkOS Auth brings enterprise single sign-on to both your TYPO3 frontend and backend. One extension, two login surfaces, full B2B team management—ideal for startups moving fast and enterprises that need security without friction.',
            'description' => 'Plug WorkOS enterprise SSO into TYPO3 frontend and backend. Email, passwordless magic links, social login, and B2B team management—all built in.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Authentication & SSO',
                    'header' => 'Enterprise login without the enterprise drama',
                    'subheadline' => 'WorkOS powers both your frontend and backend with passwordless magic links, social sign-in, and the team workspace that enterprise customers actually use.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What WorkOS does for your TYPO3 site',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'This is not another OAuth provider.',
                    'content' => '<p><strong>WorkOS is enterprise SSO with teeth</strong> — and workos_auth hooks all of it into TYPO3 at the auth level.</p><ul><li><strong>Every sign-in style</strong> — email + password, magic links, and OAuth for Google, Microsoft, GitHub, and Apple.</li><li><strong>B2B workspace layer</strong> — organizations, invitations, roles, and admin portals.</li><li><strong>One identity everywhere</strong> — backend login and frontend plugins share the same WorkOS credentials.</li><li><strong>Enterprise controls</strong> — your customers\' IT admins get the team and audit tools they expect.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/workos',
                    'media' => self::screenshot('feature-workos.png', 'Backend WorkOS Login and Frontend Login Plugin', 'Left: TYPO3 backend login page with a WorkOS tab showing \'Continue with WorkOS\', social buttons, and a magic auth email field. Right: A frontend page with the WorkOS Login card showing email + password form, magic auth option, social buttons, and a sign-up link.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Authentication & SSO',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'lock', 'title' => 'Enterprise SSO meets TYPO3', 'description' => 'Your backend and frontend speak the same WorkOS credentials. Customers log in to your portal with the same identity they use to manage their organization. No user sync headaches, no duplicate email addresses, one source of truth.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'B2B team workspace built in', 'description' => 'Invite teammates by email, set roles, manage sessions, and launch the WorkOS Admin Portal for SSO setup—all from a TYPO3 frontend plugin. Your enterprise customers self-serve their own team setup without ever contacting support.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Passwordless is the default', 'description' => 'Email + password, magic links, or social OAuth—users pick their flow. The magic-auth codes work on both frontend and backend. No account creation friction, no password-reset tickets, security that doesn\'t feel like punishment.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Made for startup scaling', 'description' => 'Start on the WorkOS free tier and grow into it. Enterprise customers want SSO and audit logs—WorkOS has both. One configuration page in TYPO3, zero custom code, and when your customer says \'we need SCIM\', the Admin Portal handles it.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Frontend Login Plugin: Email, Magic Auth, and Social Sign-In', 'content' => '<p>Drop the WorkOS Login element on any page for a ready-built sign-in card — zero template work.</p><ul><li><strong>Signed-out</strong> — email + password, a \'Send me a code\' magic-auth option, and social buttons for Google, Microsoft, GitHub, Apple.</li><li><strong>Verification handled</strong> — a friendly inline form with resend when WorkOS requires it.</li><li><strong>Signed-in</strong> — the user\'s WorkOS profile and custom metadata, plus Sign Out.</li><li><strong>Robust</strong> — CSRF-protected, and validation errors re-render with entered data preserved.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Backend Login: WorkOS Tab in TYPO3 Login', 'content' => '<p>TYPO3\'s backend login gains a WorkOS section, alongside the classic username + password switcher.</p><ul><li><strong>Hosted or direct</strong> — \'Continue with WorkOS\' launches AuthKit, or use direct social buttons and magic-auth by email.</li><li><strong>No leaks</strong> — magic-auth and verification state is stored server-side, bound to an HttpOnly cookie, never in the URL.</li><li><strong>Strict cookies still work</strong> — a same-origin continuation page sends the session cookie at the right moment.</li><li><strong>Native hand-off</strong> — TYPO3 writes its login logs, fires its events, applies session-fixation protection, and runs any backend MFA.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Account Center: Self-Service Profile, MFA, and Session Management', 'content' => '<p>Place the Account Center plugin on a private page for self-service cards, each backed by the WorkOS API and degrading gracefully on failure.</p><ul><li><strong>Profile</strong> — update first and last name, mirrored back to WorkOS.</li><li><strong>Password</strong> — change it on-site, with friendly errors for weak or breached passwords.</li><li><strong>Two-factor</strong> — enrol an authenticator app from an inline QR code, with a manual-secret fallback.</li><li><strong>Sessions</strong> — list every session with IP, device, and expiry, and revoke any one.</li><li><strong>Organizations</strong> — show memberships, roles, and a Directory Sync badge.</li></ul><p>Every state-changing action is CSRF-protected and ownership-checked.</p>', 'open_by_default' => 0],
                        ['title' => 'Team Plugin: Invite Teammates and Launch Admin Portals', 'content' => '<p>The Team plugin turns any frontend page into a workspace management console for org admins.</p><ul><li><strong>Org switcher</strong> — pick which organization to manage when a user belongs to several (sticky per session).</li><li><strong>Invite</strong> — send invitations by email with an optional role; WorkOS handles delivery and tracking.</li><li><strong>Track</strong> — pending invitations with state badges, expiry, and inline resend/revoke.</li><li><strong>Admin Portal</strong> — six signed links for SSO, Directory Sync (SCIM), Audit Logs, Log Streams, Domain Verification, and Certificate Renewal.</li></ul><p>Every action verifies the user is an active admin or owner, with CSRF and ownership checks.</p>', 'open_by_default' => 0],
                        ['title' => 'Provisioning and Identity Mapping: One Table, Full Profiles Stored', 'content' => '<p>Signing in via WorkOS creates or links the matching TYPO3 user automatically.</p><ul><li><strong>One mapping table</strong> — tx_workosauth_identity stores the WorkOS id, email, and full profile JSON.</li><li><strong>Safe storage</strong> — admin-only, hidden from the page tree, and pinned to non-versioning.</li><li><strong>No duplicates</strong> — later logins resolve the existing link.</li><li><strong>Controlled creation</strong> — fail unlinked logins, or auto-create when the email matches a domain allowlist.</li></ul><p>Backend and frontend users are provisioned separately through the same mechanism.</p>', 'open_by_default' => 0],
                        ['title' => 'Backend Modules: Setup Assistant, User Management Widget, and MCP Server Control', 'content' => '<p>A top-level WorkOS menu (admin only) adds three modules.</p><ul><li><strong>Setup Assistant</strong> — lists the redirect URIs to register, copies them in one click, and captures your API key, Client ID, and cookie password — no PHP editing.</li><li><strong>User Management</strong> — the official WorkOS widget to invite, re-role, and remove users, CSRF-protected.</li><li><strong>MCP Server</strong> — configure the optional MCP endpoint, auth mode, limits, logging, and run the schema migration.</li></ul><p>All three register as LIVE-workspace only.</p>', 'open_by_default' => 0],
                        ['title' => 'Dynamic Login URLs: Query Parameters for Custom Flows', 'content' => '<p>The frontend login URL accepts query parameters that customise AuthKit without config changes.</p><ul><li><strong>screen</strong> — open on sign-up or sign-in.</li><li><strong>provider</strong> — jump straight to Google, Microsoft, GitHub, or Apple.</li><li><strong>login_hint</strong> — pre-fill the email field.</li><li><strong>organization</strong> — scope the login to a specific org.</li><li><strong>returnTo</strong> — redirect after login (same-host only; cross-host values fall back, preventing open redirects).</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Start your enterprise identity layer today',
                    'description' => 'WorkOS is free to start, so you can wire up your first organization before you ever talk to billing. Enterprise features like SCIM, SSO configuration, and audit logs unlock as you grow—and the TYPO3 extension is open source under GPL-2.0-or-later.',
                    'cta_text' => 'Explore WorkOS + TYPO3',
                    'cta_link' => 'https://github.com/dirnbauer/workos',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featurePowermailPage(): array
    {
        return [
            'title' => 'Powermail Lab — Multi-step forms with shadcn styling and Friendly Captcha',
            'navTitle' => 'Powermail Lab',
            'slug' => '/features/powermail',
            'abstract' => 'Powermail + Desiderio gives you editor-friendly, accessible multi-step forms with a complete shadcn design system, Friendly Captcha bot protection that never phones home to Google, and a context-aware development bypass so local work stays fast.',
            'description' => 'Accessible, shadcn-styled Powermail forms in TYPO3 with Friendly Captcha bot protection, a context-aware dev bypass, and client plus server validation.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Extension Integration',
                    'header' => 'Forms your editors can build. Spam shields your developers understand.',
                    'subheadline' => 'Powermail by in2code is a proven form extension; Desiderio\'s shadcn partials and Friendly Captcha (Studio Mitte) complete the stack.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What Powermail + Desiderio does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The form extension that doesn\'t pretend to be a page builder.',
                    'content' => '<p><strong>Powermail builds the form; Desiderio styles it.</strong> Editors define everything in the backend — no HTML, PHP, or templates.</p><ul><li><strong>Every field reskinned</strong> — inputs, checkboxes, selects, radio groups, and textareas in shadcn partials.</li><li><strong>Private bot protection</strong> — Friendly Captcha sends no user IP to Google.</li><li><strong>Fast locally</strong> — a context-aware bypass flag skips the captcha in development when you opt in.</li><li><strong>Validated twice</strong> — multi-step forms check on both the client and the server.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/powermail',
                    'media' => self::screenshot('feature-powermail.png', 'Powermail multi-step form in the Visual Editor', 'TYPO3 backend showing a Powermail form definition with multiple pages and a field list, styled with Desiderio shadcn components.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Extension Integration',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'book-open', 'title' => 'Editor-friendly form builder', 'description' => 'Define pages, fields, and validation in the backend without writing templates or code. Editors control field labels, placeholders, required state, and form structure. Powermail stores submissions in the database and exports them to CSV from the backend module.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Spam prevention with developer comfort', 'description' => 'Friendly Captcha adds bot protection that does not phone home to Google. When the friendlycaptcha_skip_dev_validation flag is set, verification is skipped in Development context (including DDEV) so local testing stays fast. Everywhere else the captcha is enforced, and keys are configured per site without touching code.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Multi-step forms with client validation', 'description' => 'Split long forms across pages with a step indicator. Client-side validation checks each page in the browser with clear error messages before progression, then server-side validation confirms everything before storing. The seeder ships six demo forms, from a single-page contact form to a four-step project request wizard.', 'link' => ''],
                        ['icon' => 'globe', 'title' => 'Shadcn styling with your theme', 'description' => 'Every field type — text inputs, checkboxes, radio buttons, selects, textareas — renders through Desiderio\'s shadcn partials and follows your theme tokens. Light and dark mode both work. Step indicators, submit buttons, and validation messages all inherit your design system, so switching the site preset reflows the forms with no CSS changes.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Multi-step form pages with progress tracking', 'content' => '<p>Powermail splits complex forms across pages, each with a step indicator.</p><ul><li><strong>Back and forth</strong> — visitors move between pages; the client validates the current one before progressing.</li><li><strong>Server re-checks</strong> — every field is re-validated on submit before storage.</li><li><strong>Six seeded demos</strong> — from single-page Contact, Newsletter, and Callback to a four-step Project Request wizard.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'All field types restyled with Desiderio partials', 'content' => '<p>Every Powermail field type ships with a matching shadcn partial.</p><ul><li><strong>Full coverage</strong> — inputs, textareas, selects, radios, checkboxes, dates, country and file fields.</li><li><strong>Shared component</strong> — each wraps the same d:molecule.field used across the design system.</li><li><strong>State-aware</strong> — focus rings, disabled states, and a destructive border/ring on errors.</li><li><strong>Theme-driven</strong> — switch the preset and the form reflows instantly, light and dark, no CSS.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Client and server validation with clear error messages', 'content' => '<p>Validation runs in the browser and again on the server.</p><ul><li><strong>Inline errors</strong> — HTML5 constraints (required, email, configured validators) surface text at the field.</li><li><strong>Never trusted blindly</strong> — the server repeats every check after submission.</li><li><strong>No-code rules</strong> — editors mark fields required and attach validators per field.</li><li><strong>Extensible</strong> — a CustomValidatorEvent adds your own rules via a PSR-14 listener.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Friendly Captcha integration with a context-aware dev bypass', 'content' => '<p>Friendly Captcha installs alongside Powermail and is configured per site.</p><ul><li><strong>On every demo</strong> — each seeded form already carries a captcha field.</li><li><strong>Dev bypass</strong> — set friendlycaptcha_skip_dev_validation and Development/DDEV skips the token.</li><li><strong>Enforced elsewhere</strong> — every other context, or with the flag off, requires the captcha.</li><li><strong>GDPR-friendly</strong> — proof-of-work instead of tracking; no user IP sent to Google.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Database storage, CSV export, and finisher hooks', 'content' => '<p>Every submission is stored and exportable.</p><ul><li><strong>Stored &amp; searchable</strong> — saved to tx_powermail_domain_model_mail, listed in the backend module.</li><li><strong>CSV export</strong> — download results for analysis.</li><li><strong>Finishers</strong> — SendParameters, Redirect, SaveToAnyTable, RateLimit, plus a FinisherInterface for your own.</li><li><strong>DataProcessors</strong> — transform field data before it is persisted.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Receiver and sender mail with Fluid email templates', 'content' => '<p>On submit, Powermail can mail static receivers, a user group, or an address from the form.</p><ul><li><strong>Per-form addresses</strong> — each form defines its own receiver and sender mail.</li><li><strong>Fluid bodies</strong> — personalise emails with any submitted field value via markers.</li><li><strong>Confirmation flows</strong> — seeded forms send thank-you mails, e.g. a two-working-day response promise.</li><li><strong>Event hooks</strong> — refine recipients and bodies through PSR-14 events.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install-powermail.sh',
                    'code' => '# Powermail and the Friendly Captcha fork are TYPO3 v14 dev builds,
# so add their VCS repositories first:
composer config repositories.powermail vcs https://github.com/dirnbauer/powermail
composer config repositories.friendlycaptcha vcs https://github.com/dirnbauer/friendlycaptcha-typo3

composer require in2code/powermail:dev-typo3-v14
composer require studiomitte/friendlycaptcha:^14.0@dev

# Add the Desiderio Powermail site set to your TYPO3 site configuration,
# then seed the six demo forms with:
vendor/bin/typo3 desiderio:styleguide:seed',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Start building forms your editors will use',
                    'description' => 'Powermail + Desiderio ships with six seeded demo forms — contact, newsletter, callback, appointment, support, and a four-step project request — each with Friendly Captcha and English and German thank-you flows. Modify them, duplicate them, or build new ones from scratch in the backend. No template code needed. Thank you to in2code for Powermail, and to Studio Mitte for the Friendly Captcha extension.',
                    'cta_text' => 'Open Powermail on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/powermail',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureX402PaywallPage(): array
    {
        return [
            'title' => 'x402 Paywall for TYPO3 — HTTP 402 Micropayment Protocol',
            'navTitle' => 'x402 Paywall',
            'slug' => '/features/x402-paywall',
            'abstract' => 'Turn TYPO3 pages and API routes into monetized content with x402, the HTTP 402 Payment Required standard. Accept USDC stablecoin micropayments from any wallet, measure revenue per page, and gate content for humans or AI agents — no payment processor account required.',
            'description' => 'TYPO3 x402 paywall: charge for content with HTTP 402 micropayments. Middleware routing, wallet integration, backend dashboard, API gating.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Monetization & Payments',
                    'header' => 'Monetize TYPO3 content with HTTP 402 micropayments',
                    'subheadline' => 'Charge humans and AI agents for pages, routes, and API endpoints in seconds with the x402 payment protocol and stablecoin settlement.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What is x402?',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The HTTP 402 Payment Required standard for micropayments, now wired into TYPO3.',
                    'content' => '<p><strong>HTTP 402 is a dormant status code built exactly for this:</strong> request content, get payment terms, sign, settle.</p><ul><li><strong>Wired into TYPO3</strong> — x402-paywall handles the flow in middleware.</li><li><strong>Measured</strong> — a backend dashboard tracks revenue per page.</li><li><strong>Pages or APIs</strong> — gate frontend routes and headless APIs the same way.</li><li><strong>No middleman</strong> — no Stripe account, no processor fees, just USDC wallets talking to wallets.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-x402-paywall',
                    'media' => self::screenshot('feature-x402-paywall-backend-dashboard.png', 'x402 Paywall Backend Dashboard', 'TYPO3 backend showing x402 paywall dashboard with revenue cards, top monetized pages list, and recent transactions table.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Monetization & Payments',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'tag', 'title' => 'Earn stablecoin, not promises', 'description' => 'Every payment settles directly to your USDC wallet on Base, Polygon, or Ethereum. No middleman, no currency volatility — stablecoins stay at $1 USD.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Charge both browsers and bots', 'description' => 'Gate traditional HTML pages with a wallet-connect overlay, or gate API routes and AI-agent traffic with HTTP 402 headers. Same payment protocol, two workflows.', 'link' => ''],
                        ['icon' => 'chart', 'title' => 'Dashboard built in', 'description' => 'See revenue by page, by timeframe, and by transaction. Top performers and recent sales at a glance — no separate analytics tool.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Per-page control with no rebuild', 'description' => 'Toggle paywall on or off per page, override prices per page, and add payment descriptions — all in page properties. No middleware config rewrite needed.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'PSR-15 middleware payment enforcement', 'content' => '<p>The extension sits in the middleware stack and intercepts matching requests before they reach your pages or API.</p><ul><li><strong>Checks the signature</strong> — a verified PAYMENT-SIGNATURE header lets the request proceed.</li><li><strong>402 when absent</strong> — otherwise it returns HTTP 402 with payment terms.</li><li><strong>No code changes</strong> — you never touch page controllers or API code.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Per-page paywall configuration and price overrides', 'content' => '<p>An x402 Paywall tab in page properties puts pricing in editors\' hands.</p><ul><li><strong>Enable per page</strong> — turn the paywall on where you want it.</li><li><strong>Custom price</strong> — set a USDC amount and a description, e.g. \'Exclusive analysis: €0.02\'.</li><li><strong>Overrides defaults</strong> — price flagship pages higher and experiments lower, from the page tree.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Headless route and API gating with wildcard patterns', 'content' => '<p>Configure route patterns in site settings to gate entire API namespaces.</p><ul><li><strong>Wildcards</strong> — <code>/api/v1/content/*</code>, <code>/feed/*</code>, or a single specific route.</li><li><strong>402 without payment</strong> — unpaid requests to those routes get HTTP 402.</li><li><strong>Verified and logged</strong> — a valid PAYMENT-SIGNATURE proceeds and is recorded.</li><li><strong>Browser-free</strong> — AI agents, feed readers, and custom clients can pay for access.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Frontend overlay plugin with EIP-1193 wallet signing', 'content' => '<p>The overlay plugin guides visitors through a three-step wallet flow.</p><ul><li><strong>Connect</strong> — MetaMask, Coinbase Wallet, Rabby, or any EIP-1193 provider.</li><li><strong>Sign</strong> — approve a payment message in the wallet.</li><li><strong>Unlock</strong> — the signature settles and the content appears.</li><li><strong>Fast on return</strong> — a connected wallet with USDC moves through without leaving the page.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Backend dashboard with revenue, top pages, and transactions', 'content' => '<p>Open Web &gt; x402 Paywall to see how the paywall is performing.</p><ul><li><strong>Revenue</strong> — today, 7 days, 30 days, and all time.</li><li><strong>Top pages</strong> — a ranked list of the best earners over 30 days.</li><li><strong>Transactions</strong> — a paginated log of wallet, amount, date, and status.</li><li><strong>No extra tooling</strong> — it reads straight from your TYPO3 database.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Public simulator for testing payment flows', 'content' => '<p>A built-in simulator tests your paywall against public URLs without real transactions.</p><ul><li><strong>Real requests</strong> — enter a URL, pick a test network and price, and it makes a live x402 request.</li><li><strong>See the response</strong> — captures the headers and shows the exact payment requirement your gateway generates.</li><li><strong>Safe by design</strong> — private, local, and reserved-network targets are rejected.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP tools for agent discovery and monitoring', 'content' => '<p>Five MCP tools ship built-in for Claude Code and similar agents.</p><ul><li><strong>x402_gated_pages</strong> — lists every page and route configured for payment.</li><li><strong>x402_probe</strong> — tests the payment flow against a live URL.</li><li><strong>x402_stats / x402_transactions</strong> — revenue, counts, and payment lookups by address or date.</li><li><strong>x402_decode_header</strong> — parses PAYMENT-SIGNATURE headers for debugging.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'Bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/typo3-x402-paywall',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Ready to monetize your TYPO3 content?',
                    'description' => 'x402-paywall is GPL-2.0-or-later. Install from composer, configure your wallet address in site settings, and gate your first page in minutes. TYPO3 14.3+, PHP 8.2+.',
                    'cta_text' => 'Get started on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/typo3-x402-paywall',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureApiCapabilityBridgePage(): array
    {
        return [
            'title' => 'API Capability Bridge for TYPO3',
            'navTitle' => 'Capability Bridge',
            'slug' => '/features/api-capability-bridge',
            'abstract' => 'API Capability Bridge is a TYPO3 extension that bridges your CMS to intelligent agents and external APIs by registering structured, policy-controlled CRUD resources from a declarative capability manifest — turning your site\'s content and features into a secure, machine-readable capability surface.',
            'description' => 'Expose TYPO3 site capabilities to agents and APIs through a secure, declarative capability policy. Bridge your CMS to intelligent systems safely.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'API & Integration',
                    'header' => 'Make your TYPO3 site speak to agents',
                    'subheadline' => 'A capability policy turns your content, workflows, and permissions into structured API resources that agents and systems can understand and operate on safely.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What is a Capability Bridge?',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Intelligent systems need to know what your site can do.',
                    'content' => '<p><strong>Intelligent systems need to know what your site can do.</strong> The Bridge exposes TYPO3 capabilities as a structured, policy-controlled API surface.</p><ul><li><strong>You declare access</strong> — a single YAML capability policy defines what is reachable.</li><li><strong>Registered safely</strong> — matching CRUD resources are registered with sg_apicore.</li><li><strong>Authenticated</strong> — opaque backend bearer tokens, enforced at the resource level.</li><li><strong>Clean contract</strong> — agents see exactly what they may read, create, update, or delete.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/api-capability-bridge',
                    'media' => self::screenshot('feature-api-capability-bridge.png', 'Capability policy and API token records', 'TYPO3 backend showing the api-capability-bridge capability policy resources and the tx_apicore_token records used for agent authentication.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'API & Integration',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'shield-check', 'title' => 'Policy-controlled resources', 'description' => 'Declare which extensions, tables, and operations are accessible via a single config/capability-policy.yaml file — no per-endpoint coding required. The registrar checks each resource against the policy and its capability manifest before registering it, so nothing is exposed by default.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Backend token authentication', 'description' => 'Agents authenticate with opaque bearer tokens that map to backend users and scope lists. Tokens carry expirations, update a last-used timestamp on every successful call, and run inside the backend user context — so an agent sees exactly the records that user can see.', 'link' => ''],
                        ['icon' => 'globe', 'title' => 'News Studio endpoints ready', 'description' => 'Out-of-the-box REST endpoints for editorial work on news: user context, TCA-driven schema discovery, record search, file handling, and workspace submit, publish, and preview — everything an editorial agent needs to read and write news without the backend UI.', 'link' => ''],
                        ['icon' => 'book-open', 'title' => 'Manifest-driven security', 'description' => 'Built on typo3-capability-manifest, the bridge verifies that each extension declares an honest data contract. Risk scoring and policy rules block resources that exceed your max_risk_score or lack a manifest from ever being registered.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Declarative capability policy', 'content' => '<p>Instead of hardcoding routes, one <code>config/capability-policy.yaml</code> lists what is available.</p><ul><li><strong>Explicit entries</strong> — extension, table, base path, allowed operations, and required scopes per resource.</li><li><strong>Nothing automatic</strong> — the registrar registers only resources that pass its checks.</li><li><strong>Auditable</strong> — the contract lives in version control beside the rest of your config.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Secure backend bearer token provider', 'content' => '<p>Requests authenticate via opaque bearer tokens in the tx_apicore_token table.</p><ul><li><strong>Mapped to a user</strong> — each token maps to a backend user and a scope list (e.g. news:read).</li><li><strong>Expiring</strong> — rejected once its expires_at timestamp passes.</li><li><strong>Real permissions</strong> — queries run with that user\'s rights, not an elevated service account.</li><li><strong>Tracked</strong> — a last-used timestamp is recorded on every successful call.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'News Studio API endpoints', 'content' => '<p>The NewsStudioController exposes REST endpoints for news editorial work.</p><ul><li><strong>Context</strong> — <code>GET /studio/me</code> returns the current user and permissions.</li><li><strong>Schema</strong> — <code>GET /studio/schema/news</code> returns a TCA-generated form schema.</li><li><strong>Records &amp; files</strong> — a searchable record picker, plus <code>/studio/files</code> and upload.</li><li><strong>Guarded</strong> — each route checks a RequireScopes attribute and backend-user permissions.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace submit, publish, and preview', 'content' => '<p>The controller drives the full editorial lifecycle, not just reads and writes.</p><ul><li><strong>Submit</strong> — <code>POST /studio/news/{id}/submit</code> moves a record into review.</li><li><strong>Publish</strong> — <code>POST .../publish</code> releases it, gated by the workspace:publish scope.</li><li><strong>Preview</strong> — <code>GET .../preview</code> returns a workspace-aware rendering.</li><li><strong>Target a workspace</strong> — list and switch the workspace a token operates in.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Policy-based resource registration', 'content' => '<p>The bridge never blindly registers every resource.</p><ul><li><strong>Manifest-checked</strong> — it loads each extension\'s capability manifest and computes a risk score.</li><li><strong>Policy-checked</strong> — PolicyChecker applies deny lists, review flags, max_risk_score, and require_manifest.</li><li><strong>Refused if untrusted</strong> — no manifest or a failed audit means the resource is not exposed.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace and permission awareness', 'content' => '<p>Tokens operate inside the right workspace and permission scope.</p><ul><li><strong>Target a workspace</strong> — via the X-TYPO3-Workspace header or a query parameter.</li><li><strong>Safe queries</strong> — RootLevelRestriction and DeletedRestriction keep results appropriate and non-deleted.</li><li><strong>Real file rights</strong> — file capabilities derive from the user\'s file_permissions, nothing they couldn\'t do in the backend.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Full sg_apicore integration', 'content' => '<p>Built on sg_apicore\'s ApiRegistry and ResourceRegistry, reusing its proven patterns.</p><ul><li><strong>Standard REST</strong> — proper verbs, status codes, and per-definition CORS origins.</li><li><strong>Portable</strong> — works with any tool that speaks sg_apicore\'s format.</li><li><strong>Extensible</strong> — custom controllers add endpoints with the same ApiRoute and RequireScopes attributes.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Auditable token and usage tracking', 'content' => '<p>Every token record carries the metadata you need for compliance.</p><ul><li><strong>Full context</strong> — owning backend user, issued scopes, and an optional expiration.</li><li><strong>Last-used</strong> — refreshed on every authenticated call; expired tokens are rejected.</li><li><strong>Queryable trail</strong> — see which systems hold credentials, their scope, and when they last connected.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/api-capability-bridge
vendor/bin/typo3 database:updateschema
# Define your config/capability-policy.yaml with api_definitions and api_resources
# Create a token record in the tx_apicore_token table (List module):
#   assign a backend user, set scopes (e.g. news:read, files:write), and an expiry',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Connect your TYPO3 site to intelligent agents',
                    'description' => 'API Capability Bridge turns your CMS into a verifiable, policy-controlled capability provider that agents and external systems can trust and operate within safely.',
                    'cta_text' => 'Get started on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/api-capability-bridge',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureAgentationPage(): array
    {
        return [
            'title' => 'Agentation: Visual UI annotations for AI coding agents in TYPO3',
            'navTitle' => 'Agentation',
            'slug' => '/features/agentation',
            'abstract' => 'Agentation brings visual annotation feedback directly into TYPO3. Authenticated users point AI agents at page elements with selectors, comments, and computed styles — then synchronize that structured context to Claude Code, Cursor, or any MCP-capable agent.',
            'description' => 'Collect feedback from users and agents with visual UI annotations in TYPO3 frontend and backend. Guide AI coding agents directly to the elements you want changed.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Developer tools',
                    'header' => 'Point AI agents at your TYPO3 frontend and backend',
                    'subheadline' => 'Visual annotations let you collect structured feedback from users and pass it directly to coding agents.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Feedback collection that agents can act on',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Agentation is a visual feedback layer for TYPO3 where authenticated backend users annotate pages they see — then sync that context to AI coding agents over MCP.',
                    'content' => '<p><strong>Most feedback loops are broken</strong> — users describe, developers guess, agents work blind. Agentation closes the loop.</p><ul><li><strong>Point and note</strong> — click an element, write a note; the agent gets the selector, computed styles, and page context too.</li><li><strong>Two toolbars</strong> — one on the frontend for cross-browser testing, one in the backend for module tweaks.</li><li><strong>Never leaks</strong> — both stay behind a login gate and a context guard, away from production.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-agentation',
                    'media' => self::screenshot('feature-agentation.png', 'Agentation feedback panel in the TYPO3 frontend', 'A TYPO3 frontend page showing the Agentation toolbar on the right side with visual annotation controls and a list of stored annotations.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Developer tools',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'send', 'title' => 'Point-and-annotate UI', 'description' => 'Backend users click any element on the frontend or in backend modules to add visual feedback. The toolbar captures the DOM selector, computed styles, and page context automatically.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Agents understand the intent', 'description' => 'Unlike Slack messages, Agentation annotations include selector, styles, and page structure. Claude Code, Cursor, and Windsurf receive enough context to make precise changes.', 'link' => ''],
                        ['icon' => 'lock', 'title' => 'Safe by default', 'description' => 'Toolbar injection requires a backend user session, explicit Admin Panel opt-in on the frontend, and an application context gate that blocks production by default.', 'link' => ''],
                        ['icon' => 'handshake', 'title' => 'MCP sync to your agent', 'description' => 'Copy a ready-made MCP config block, a one-line Claude Code CLI command, or a Cursor deep link. Agentation acts as a same-origin proxy for local and cloud sync endpoints.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Frontend annotation toolbar', 'content' => '<p>A gated toolbar appears on the frontend for authenticated backend users.</p><ul><li><strong>Explicit, never automatic</strong> — it respects an Admin Panel section toggle.</li><li><strong>Click to annotate</strong> — highlight elements, add comments, and capture computed styles and selectors.</li><li><strong>Configurable</strong> — position and scope are set per request via Admin Panel settings.</li><li><strong>Persisted</strong> — annotations live in browser-local storage plus optional server endpoints.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Backend module frame annotation', 'content' => '<p>The same toolbar injects into TYPO3 module frames when both the global and per-user toggles are on.</p><ul><li><strong>Annotate inline</strong> — the page module, record forms, and third-party extensions.</li><li><strong>One trail</strong> — backend annotations flow into the same storage as frontend ones.</li><li><strong>For admins and devs</strong> — feedback stays in one place for agents to reference.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Admin-only System > Agentation module', 'content' => '<p>Only administrators see the module at System &gt; Agentation.</p><ul><li><strong>MCP config on tap</strong> — a copyable JSON block, a Claude Code CLI command, or a Cursor deep link.</li><li><strong>Status checks</strong> — verifies your API key and app context.</li><li><strong>Manage annotations</strong> — reload, delete individually, or clear all stored feedback.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP configuration and export', 'content' => '<p>The module assembles a ready-to-paste mcpServers config block.</p><ul><li><strong>Broad support</strong> — Claude Code, Cursor, Windsurf, Zed, Continue, and any MCP-capable agent.</li><li><strong>Pre-filled</strong> — your workspace ID and, when set, an API key (optional locally, required for server sync).</li><li><strong>One click</strong> — copy the JSON, grab a <code>claude mcp add</code> command, or use a Cursor install deep link.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Per-user frontend and backend settings', 'content' => '<p>Each backend user controls their own toolbars independently.</p><ul><li><strong>Two switches</strong> — frontend and backend toolbars, toggled separately in user settings.</li><li><strong>Admin default, user override</strong> — admins set the opt-in default; users can still flip each one.</li><li><strong>Role-fit</strong> — a designer may use only the frontend toolbar while a developer uses both.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Application context gate', 'content' => '<p>The toolbar respects TYPO3\'s application context.</p><ul><li><strong>Dev-only by default</strong> — it activates only in Development contexts.</li><li><strong>Widen if needed</strong> — opt into \'Development and Testing\' or \'All contexts\' per environment.</li><li><strong>Production-safe</strong> — the default makes shipping a toolbar to production nearly impossible.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Same-origin backend proxy for local and cloud sync', 'content' => '<p>When annotations sync to a server, TYPO3 makes the request, not the browser.</p><ul><li><strong>Server-side key</strong> — TYPO3 attaches the API key and keeps credentials out of browser storage.</li><li><strong>No CORS pain</strong> — cross-origin concerns are handled on the backend.</li><li><strong>Offline-first</strong> — local workflows keep annotations in browser storage until you sync.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/agentation',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Integrate Agentation into your TYPO3 workflow',
                    'description' => 'Install the extension, configure your coding agent, and start collecting structured feedback that AI agents can act on. Works with Claude Code, Cursor, Windsurf, and any MCP-compatible agent.',
                    'cta_text' => 'Get Agentation on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/typo3-agentation',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureSgApicorePage(): array
    {
        return [
            'title' => 'sg_apicore — A modern API framework for TYPO3',
            'navTitle' => 'sg_apicore',
            'slug' => '/features/sg-apicore',
            'abstract' => 'A modern, attribute-driven API framework for TYPO3 that turns content into structured data. Build REST endpoints, expose CRUD resources, generate OpenAPI specs, and control access with tokens or sessions — all without boilerplate code.',
            'description' => 'Expose TYPO3 content via REST API with OpenAPI docs, authentication, auto-CRUD resources, and MCP tool integration. Modern, performance-driven framework for TYPO3 14.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Backend API',
                    'header' => 'Turn your TYPO3 content into an API',
                    'subheadline' => 'One attribute per endpoint. OpenAPI docs automatic. Tokens, JWT, or session-based auth. Built for production, shipped as open source.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'The API framework TYPO3 was waiting for',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Stop hand-rolling REST endpoints. sg_apicore provides the plumbing.',
                    'content' => '<p><strong>Stop hand-rolling REST endpoints.</strong> sg_apicore is a modern API core for TYPO3, all in one lightweight package.</p><ul><li><strong>Built in</strong> — multi-API registration, versioning, tenant-aware routing, OpenAPI 3, and authentication.</li><li><strong>Just add attributes</strong> — write controller methods, annotate them, and get docs, auth, and scopes automatically.</li><li><strong>MCP-ready</strong> — the same endpoints are exposed as MCP tools for AI agents.</li><li><strong>No boilerplate</strong> — no config overhead, no response-wrapper conventions, no per-endpoint auth.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/sg_apicore',
                    'media' => self::screenshot('feature-sg-apicore.png', 'OpenAPI Swagger UI with auto-generated endpoint documentation', 'Swagger UI showing the /api/public/v1/docs/ui interface with a list of auto-registered endpoints, request/response models, and inline test execution'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Backend API',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Attribute-driven endpoints', 'description' => 'One PHP attribute per endpoint. The framework reads #[ApiRoute], #[RequireScopes], #[ApiResponse] and builds the spec, routing, and auth checks automatically.', 'link' => ''],
                        ['icon' => 'book-open', 'title' => 'OpenAPI 3.0 out of the box', 'description' => 'Every registered endpoint auto-generates into Swagger UI at /api/{apiId}/v{version}/docs/ui. Export specs to JSON for external tools or client-side codegen.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Multi-mode authentication', 'description' => 'Public, opaque bearer tokens, JWT user tokens, or backend session auth. Per-API defaults with per-endpoint overrides. Scope enforcement on demand.', 'link' => ''],
                        ['icon' => 'database', 'title' => 'Auto-CRUD resources without code', 'description' => 'Register a TYPO3 table and get full CRUD endpoints (list, get, create, update, delete) instantly. DataHandler integration means hooks, reference indexing, and history records all fire.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Multi-API and versioning in the same install', 'content' => '<p>Register multiple APIs — public, partner, internal — each with its own versions.</p><ul><li><strong>No conflicts</strong> — route <code>/api/public/v1/...</code> and <code>/api/partner/v2/...</code> to separate actions.</li><li><strong>Independent config</strong> — per-API auth mode, rate limits, CORS policy, and MCP settings.</li><li><strong>Scoped routes</strong> — restrict any endpoint by <code>apiId</code> and <code>version</code> on #[ApiRoute].</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Token management and scope-based access control', 'content' => '<p>Issue exactly the token type each consumer needs.</p><ul><li><strong>Machine tokens</strong> — opaque bearer tokens for machine-to-machine access.</li><li><strong>User tokens</strong> — JWT access tokens with opaque refresh tokens, or per-user API keys.</li><li><strong>Scoped</strong> — assign scopes and enforce them with #[RequireScopes] on any endpoint.</li><li><strong>Managed</strong> — a backend module lists machine, user, and refresh tokens with expiry and regeneration.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace-aware auto-CRUD resources with DataHandler', 'content' => '<p>Register a TYPO3 table as a REST resource with a single call.</p><ul><li><strong>Full CRUD</strong> — automatic list, get, create, update, and delete endpoints.</li><li><strong>DataHandler writes</strong> — hooks fire, reference indexing updates, history is written, just like the backend.</li><li><strong>Workspace-correct</strong> — live reads drop drafts; workspace reads overlay via workspaceOL().</li><li><strong>No raw SQL</strong> — and no consistency surprises.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'OpenAPI 3.0 specification generation and live testing', 'content' => '<p>Every endpoint exports to OpenAPI 3.0.3 JSON.</p><ul><li><strong>Described by attributes</strong> — #[ApiResponse], #[ApiBodyParam], and #[ApiQueryParam] define params, bodies, and responses.</li><li><strong>TCA-enriched</strong> — schemas pick up TCA field labels, so the spec mirrors your data model.</li><li><strong>Swagger UI</strong> — auto-mounted at <code>/api/{apiId}/v{version}/docs/ui</code> to try endpoints live.</li><li><strong>Exportable</strong> — generate the spec via <code>api:openapi:generate</code> for codegen.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP (Model Context Protocol) tool exposure from endpoints', 'content' => '<p>Expose existing endpoints as MCP tools — no duplication.</p><ul><li><strong>Discoverable</strong> — agents find tools via <code>POST .../mcp</code> (JSON-RPC) through the same auth and routing.</li><li><strong>Streaming</strong> — a companion <code>GET /mcp</code> supports SSE-style communication.</li><li><strong>Controlled</strong> — per-API denylists and #[ApiMcp] attributes hide sensitive endpoints.</li><li><strong>Previewable</strong> — <code>api:mcp:list</code> shows what is exposed before deployment.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Multi-tenancy and site-aware request context', 'content' => '<p>Every request runs in a TenantContext resolved from the TYPO3 Site.</p><ul><li><strong>Tenant-scoped</strong> — filter endpoints by the <code>tenants</code> property on #[ApiRoute].</li><li><strong>Context flows</strong> — downstream DataHandler ops and queries know which site owns the data.</li><li><strong>Multi-brand ready</strong> — the same endpoint serves different content per domain.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Rate limiting with burst and windowed enforcement', 'content' => '<p>Enable rate limits per API, per resource, or per endpoint.</p><ul><li><strong>Tunable</strong> — set a request limit, window size, and optional burst allowance.</li><li><strong>Transparent</strong> — X-RateLimit-Limit/Remaining/Reset/Burst headers tell clients where they stand.</li><li><strong>Enforced</strong> — exceeding the limit returns a 429.</li><li><strong>Granular or blanket</strong> — one rule per endpoint, or per whole API version.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Request/response logging and request tracing', 'content' => '<p>Structured logging with built-in redaction.</p><ul><li><strong>Traceable</strong> — every request gets an X-Request-ID you can follow through DataHandler and business logic.</li><li><strong>RFC 7807 errors</strong> — Problem JSON carries the same requestId for support teams.</li><li><strong>Safe by default</strong> — passwords, tokens, authorization, secrets, and cookies are redacted; the list is configurable.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require sgalinski/sg-apicore
vendor/bin/typo3 extension:activate sg_apicore
# Navigate to /api/{apiId}/v{version}/docs/ui to see the OpenAPI docs',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Make your TYPO3 content available as data',
                    'description' => 'Mobile apps, desktop editorial tools, partner integrations, AI agents — whatever consumes your TYPO3 content can now call a proper REST API with scopes, rate limits, OpenAPI docs, and request tracing built in.',
                    'cta_text' => 'Get sg_apicore on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/sg_apicore',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureSkillflowPage(): array
    {
        return [
            'title' => 'Agent Skills for TYPO3 — searchable QA & editorial workflows',
            'navTitle' => 'Skills',
            'slug' => '/features/skillflow',
            'abstract' => 'Skillflow brings Anthropic-style agent skills into TYPO3 workspaces. Define skills as SKILL.md folders with YAML frontmatter, import them from git repositories or local directories, assign them to workspace stages for auto-review workflows, and search them with Solr facets.',
            'description' => 'Bring Anthropic-style agent skills into TYPO3 workspaces: searchable skill library, Solr-backed faceted search, AI-powered content review automation.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Workspace Automation',
                    'header' => 'AI-powered skill management for TYPO3 editorial workflows',
                    'subheadline' => 'Define reusable agent skills, import them from repositories, run them against draft content in your workspace stage pipeline, and keep editorial quality consistent.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What skillflow does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Skills are how AI agents accomplish focused tasks — SEO optimization, tone-of-voice review, content QA, image analysis, or anything your team needs to automate.',
                    'content' => '<p><strong>Skillflow manages Anthropic-style agent skills inside TYPO3</strong> — editable records, importable from git or local folders as SKILL.md files.</p><ul><li><strong>Automate review</strong> — assign skills to workspace stages to check content as it is staged.</li><li><strong>Run on demand</strong> — trigger skills manually against pages from the module.</li><li><strong>Find fast</strong> — search the whole library with Solr-powered faceted search.</li><li><strong>Two runners</strong> — CLI adds whitelisted tools; API adds remote MCP servers.</li></ul><p>Reports are stored and never auto-applied — always suggestions for your team\'s review.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/skillflow',
                    'media' => self::screenshot('feature-skillflow.png', 'Skills module list with Solr search and facets', 'TYPO3 backend Content > Skills module showing a searchable list of skills with Solr facet filters and the Skills detail editor with SKILL.md structure visible.'),
                ]),
                self::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Workspace Automation',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'database', 'title' => 'Searchable skill library', 'description' => 'Your imported skills are indexed in Solr with faceted search across runner type, allowed tools, and categories. Find the exact skill you need without scrolling through long lists.', 'link' => ''],
                        ['icon' => 'history', 'title' => 'Git-backed skill imports', 'description' => 'Point to a GitHub, GitLab, or Gitea repository. Sync downloads the latest skills, updates existing ones in place with stable UIDs, and keeps workspace assignments across re-syncs.', 'link' => ''],
                        ['icon' => 'send', 'title' => 'Workspace stage automation', 'description' => 'Assign skills to custom workspace stages. Enable auto-run so every record sent to that stage automatically triggers the skill review; reports appear in the module and notify the editor.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Two runners, your choice', 'description' => 'The API runner connects to the Anthropic Messages API with remote MCP servers. The CLI runner executes Claude Code locally with tools from .mcp.json, filtered by the per-skill allowed-tools whitelist.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Skill records with SKILL.md structure', 'content' => '<p>Every skill is a backend record (tx_skillflow_skill) mirroring the Anthropic format.</p><ul><li><strong>Standard fields</strong> — name, identifier, description, and a markdown body of agent instructions.</li><li><strong>Code editor</strong> — the body edits inline; extra frontmatter (like allowed-tools) stays in a JSON field.</li><li><strong>Author or import</strong> — write skills in the backend or pull them from git.</li><li><strong>Bidirectional</strong> — export a skill to a folder and it is a valid Anthropic skill at once.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Supporting file attachments with text indexing', 'content' => '<p>Everything beside the SKILL.md — references, scripts, templates — imports as attachment records.</p><ul><li><strong>Under one tab</strong> — files (tx_skillflow_file) appear on the skill\'s Attachments tab.</li><li><strong>Indexed</strong> — text files up to 256 KB are searchable; binaries are counted but skipped.</li><li><strong>Self-healing</strong> — re-sync updates files in place and soft-deletes ones that disappear.</li><li><strong>Runtime-ready</strong> — the CLI runner materialises the folder; the API runner inlines files under a size budget.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Folder and repository imports with stable UIDs', 'content' => '<p>Import skills from a local folder or a remote repository.</p><ul><li><strong>Local</strong> — scan a configurable folder (default <code>&lt;project&gt;/skills/</code>), on demand or on a cron.</li><li><strong>Remote</strong> — point at a GitHub, GitLab, or Gitea URL, or a .zip, and sync all skills.</li><li><strong>Stable UIDs</strong> — re-syncs keep assignments and page links intact; no re-wiring.</li><li><strong>Secrets safe</strong> — private repos store only the env-var name; the token never hits the database.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Backend module for skill management and run reports', 'content' => '<p>The Content &gt; Skills module is mission control for your library.</p><ul><li><strong>Browse &amp; search</strong> — list all skills with Solr facets (runner type, allowed tools, categories).</li><li><strong>Manage repos</strong> — trigger imports and re-syncs from the UI.</li><li><strong>Run reports</strong> — see the prompt, AI response, extracted suggestions, and execution time.</li><li><strong>Stored</strong> — reports (tx_skillflow_run) sit alongside record history for team review.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace integration with stage-triggered auto-run', 'content' => '<p>Open the Skills tab on any custom workspace stage and enable auto-run.</p><ul><li><strong>On stage</strong> — every record sent to that stage runs the assigned skills in sequence.</li><li><strong>Reported</strong> — results are stored and the editor is notified.</li><li><strong>Closes the loop</strong> — new records get instant SEO or tone feedback before publishing.</li><li><strong>Auto-workflow</strong> — optionally send every new element to a configured stage automatically.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Page-level skill assignment and manual runs', 'content' => '<p>Any page can carry assigned QM skills via the Skills tab in page properties.</p><ul><li><strong>On demand</strong> — unlike stage skills, page skills run when you trigger them.</li><li><strong>Workspace-aware</strong> — they review draft content through workspace overlays.</li><li><strong>Final QA</strong> — perfect for landing pages and campaign microsites before go-live.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'CLI sync command for cron-scheduled imports', 'content' => '<p><code>skillflow:sync</code> refreshes the local folder and all repositories in one pass.</p><ul><li><strong>Cron-able</strong> — no web request or UI click needed.</li><li><strong>Scheduled</strong> — pair it with a TYPO3 scheduler task or a cron job.</li><li><strong>Separate indexing</strong> — <code>skillflow:solr:index</code> re-indexes skills for faceted search independently.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'Bash',
                    'filename' => 'composer-install.sh',
                    'code' => 'composer require webconsulting/skillflow:@dev
ddev exec vendor/bin/typo3 extension:setup
# Put your Anthropic API key into the DDEV web environment:
#   .ddev/config.local.yaml: web_environment: ["ANTHROPIC_API_KEY=sk-ant-..."]
ddev restart
ddev exec vendor/bin/typo3 skillflow:sync
# Then in the backend: Content > Skills — see imported skills, enable auto-run on workspace stages.',
                ]),
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Credit where it is due',
                    'header' => 'Part of a TYPO3 AI stack built on Netresearch',
                    'subheadline' => 'Skillflow sits alongside Netresearch\'s AI extensions for TYPO3 — nr_llm, nr_mcp_agent, nr_vault and t3_cowriter. Thank you, Netresearch DTT GmbH.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Turn your editorial pipeline into a quality checkpoint',
                    'description' => 'Skillflow brings focused AI review into TYPO3\'s workspace stages: consistent QA, faster drafts, and every team member\'s editorial eye without the manual review overhead.',
                    'cta_text' => 'Install skillflow on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/skillflow',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string}|null $media
     * @return StarterBlock
     */
    private static function v14StrategyTextmedia(string $header, string $layout, string $subheadline, string $content, ?array $media = null): array
    {
        $fields = [
            'header' => $header,
            'shadcn_layout' => $layout,
            'subheadline' => $subheadline,
            'content' => $content,
            'media_rounded' => 1,
        ];

        if ($media !== null) {
            $fields['media'] = $media;
        }

        return self::block('desiderio_textmedia', $fields);
    }

    /**
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    private static function mcpStrategyImage(string $filename, string $title, string $alternative): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Mcp/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Generated visual for the TYPO3 v14 agentic strategy page.',
            'source' => self::REPO_URL,
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
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    private static function screenshot(string $filename, string $title, string $alternative, string $description = 'Screenshot of a TYPO3 14 installation with Desiderio.'): array
    {
        $folder = str_starts_with($filename, 'frontend-') ? 'Frontend' : 'Backend';

        return [
            'file' => 'Resources/Public/Styleguide/' . $folder . '/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => $description,
            'source' => self::REPO_URL,
        ];
    }

    /**
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    private static function unsplash(string $filename, string $title, string $alternative): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Unsplash/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Photo from the seeded Unsplash demo collection.',
            'source' => 'https://unsplash.com/collections/25880',
        ];
    }

    /**
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    private static function portrait(string $filename, string $name): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Unsplash/' . $filename,
            'title' => $name . ' portrait',
            'alternative' => 'Portrait photo of ' . $name . '.',
            'description' => 'Portrait photo from the seeded Unsplash demo collection.',
            'source' => 'https://unsplash.com/collections/25880',
        ];
    }
}
