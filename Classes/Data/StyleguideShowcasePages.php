<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

use Webconsulting\Desiderio\Data\Showcase\ShowcaseAgenticPages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseAudiencePages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseIntegrationPages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseLegalPages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseStrategyPages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseSuccessStories;

/**
 * Marketing showcase seeded by desiderio:styleguide:seed in addition to the
 * element chapters: managed homepage content on the styleguide root page plus
 * subpages.
 *
 * This class owns the homepage and the four pages that frame the element
 * catalogue; the rest of the showcase lives in Data/Showcase/, one class per
 * chapter, and is stitched together by subpages() below.
 *
 * Internal links use the placeholder syntax {{page:<slug>}}; the seeder
 * replaces them with t3://page?uid=N once the target pages exist. The special
 * slug {{page:home}} resolves to the styleguide root page. Pages with a
 * parentSlug are created below that showcase page instead of the root.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class StyleguideShowcasePages
{
    /**
     * Every showcase page below the styleguide root, in seeding order: parents
     * come before the pages that name them as parentSlug.
     *
     * @return array<int, ShowcasePage>
     */
    public static function subpages(): array
    {
        return [
            self::technicalFeaturesPage(),
            self::themesPage(),
            self::contentTypesHubPage(),
            self::rteCombinationsPage(),
            ...ShowcaseIntegrationPages::pages(),
            ...ShowcaseAgenticPages::pages(),
            ...ShowcaseAudiencePages::pages(),
            ...ShowcaseStrategyPages::pages(),
            ...ShowcaseSuccessStories::pages(),
            ...ShowcaseLegalPages::pages(),
        ];
    }

    /**
     * @return array<int, ShowcasePage>
     */
    public static function blogSupportPages(): array
    {
        return ShowcaseLegalPages::blogSupportPages();
    }

    /**
     * @return array<int, ShowcaseBlock>
     */
    public static function homeContent(): array
    {
        return [
            ShowcaseBlocks::block('desiderio_hero', [
                'variant' => 'split',
                'badge_text' => 'shadcn/ui for TYPO3 14',
                'header' => 'The TYPO3 design system your editors will brag about',
                'subheadline' => 'Desiderio puts 244 shadcn-styled content elements, 53 typed Fluid components, and 15 runtime-switchable themes into one composer package. Install it before lunch. Re-theme it before the coffee gets cold.',
                'primary_button_text' => 'Start free — €0 forever',
                'primary_button_link' => ShowcaseBlocks::REPO_URL,
                'primary_button_variant' => 'default',
                'secondary_button_text' => 'See the technical facts',
                'secondary_button_link' => '{{page:technical-features}}',
                'hero_image' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Editing a page in the Visual Editor', 'TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
                'image_position' => 'right',
                'overlay_opacity' => '0.5',
            ]),

            // ------------------------------------------------- the problem
            ShowcaseBlocks::block('desiderio_featurecards', [
                'eyebrow' => 'The problem Desiderio solves',
                'header' => 'Running a TYPO3 site in 2026 shouldn\'t hurt this much',
                'subheadline' => 'Six things that slow every CMS team down — and the specific answer Desiderio ships for each. Written for the people who feel them: editors, developers, and whoever signs the invoice.',
                'items' => [
                    ['title' => 'Every layout change is a developer ticket', 'description' => 'Editors build finished pages from a ten-group wizard, with a real backend preview for all 244 elements and inline editing in the Visual Editor — the dev queue goes back to being about features, not landing pages.'],
                    ['title' => 'One edit and the whole layout breaks', 'description' => 'Every element is assembled from typed Fluid components on one token contract. Spacing, colour and type come from the design system, so off-brand pages and broken grids are impossible to create by accident.'],
                    ['title' => 'Your stack is fifteen plugins holding hands', 'description' => 'Desiderio is one Composer package. News, Solr, Blog, Powermail and the Form Framework all render through the same shadcn-styled templates — one design system, one security model, one thing to update.'],
                    ['title' => 'A redesign means a rebuild', 'description' => 'Theming is pure OKLCH tokens switched at runtime: zero theme rebuilds. Repaint the whole site, or just one page tree, from site settings — no deployment, no cache anxiety.'],
                    ['title' => 'The accessibility audit is coming — the EAA is now law', 'description' => 'WCAG 2.2 contrast is enforced by the build, markup is semantic with proper landmarks, and screen-reader labels are translated. You bring the report to legal instead of retrofitting 244 elements by hand.'],
                    ['title' => 'AI search quotes everyone but you', 'description' => 'Semantic HTML, question-shaped elements (FAQ, how-to, definition lists) and clean per-page metadata make your content extraction-friendly for AI Overviews, ChatGPT and Perplexity — GEO as a side effect of doing HTML properly.'],
                ],
            ]),

            // ------------------------------------- the create page on ui.shadcn.com
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => 'The principle behind the whole project',
                'header' => 'From ui.shadcn.com/create to a live TYPO3 theme',
                'subheadline' => 'Desiderio is built on one radical idea: you should design your site where the best design tooling lives — and run it where your content lives. Pick a design on the create page on ui.shadcn.com. Paste one preset code into TYPO3. Done.',
                'variant' => 'center',
            ]),
            ShowcaseBlocks::block('desiderio_featurealternating', [
                'header' => 'Three steps. No theme rebuild. No agency invoice.',
                'subheadline' => 'The create page on ui.shadcn.com is the official theme designer of the shadcn/ui ecosystem. Desiderio mirrors its token model one to one — which makes it your TYPO3 theme editor.',
                'items' => [
                    [
                        'title' => '1 · Design on ui.shadcn.com/create',
                        'description' => 'On the shadcn/ui create page you compose a complete design system in the browser: base color and neutral palette, font pairing, border radius, and one of eight structural style recipes — Vega, Nova, Maia, Lyra, Mira, Luma, Sera, or Rhea — that define focus rings, transitions, and control shapes. Every choice compiles down to design tokens, and the whole result exports as one small preset code.',
                        'image' => ShowcaseBlocks::screenshot('frontend-shadcn-create.png', 'The create page on ui.shadcn.com', 'The shadcn/ui create page on ui.shadcn.com with palette, font, and style recipe controls.', 'Screenshot of the shadcn/ui create page, the theme designer Desiderio imports presets from.'),
                        'link' => ShowcaseBlocks::CREATE_URL,
                    ],
                    [
                        'title' => '2 · Paste the preset into Desiderio',
                        'description' => 'Drop the preset code into the Desiderio site settings and the entire site repaints — colors, radius, typography, control density. Desiderio speaks the same OKLCH token language as shadcn/ui, so nothing gets lost in translation. Five curated create presets and ten house presets ship ready to use; switching needs no Tailwind rebuild, no deployment, no cache anxiety.',
                        'image' => ShowcaseBlocks::screenshot('backend-page-properties-theme.png', 'Per-page theme preset field', 'TYPO3 page properties with the Desiderio per-page theme preset selection.'),
                        'link' => '{{page:technical-features}}',
                    ],
                    [
                        'title' => '3 · Give every page tree its own look',
                        'description' => 'Since version 2.6 any page can carry its own preset, inherited down the whole subtree. Campaign microsite in Midnight, product pages in Marine, the blog in Forest — one TYPO3 install, one content pool, as many looks as your marketing calendar demands. This styleguide proves it: every chapter page below runs a different theme.',
                        'image' => ShowcaseBlocks::screenshot('frontend-pricing-midnight-dark.png', 'Dark pricing page in the Midnight preset', 'Desiderio pricing page rendered in the dark Midnight theme preset.'),
                        'link' => '{{page:themes}}',
                    ],
                ],
            ]),
            ShowcaseBlocks::block('desiderio_quote', [
                'header' => 'Why shadcn/ui, in their own words',
                'quote_text' => 'A set of beautifully designed components that you can customize, extend, and build on. Open Source. Open Code.',
                'author' => 'shadcn/ui',
                'role' => 'ui.shadcn.com',
                'variant' => 'large',
            ]),
            ShowcaseBlocks::block('desiderio_howtosteps', [
                'header' => 'Your first themed site in four commands',
                'description' => 'This is the whole onboarding. No starter kit purchase, no license key, no setup wizard with nine screens.',
                'items' => [
                    [
                        'title' => 'composer require webconsulting/desiderio',
                        'content' => 'One package brings the elements, the components, and the theme layer. TYPO3 14.3+, PHP 8.4+, done.',
                        'image' => ShowcaseBlocks::unsplash('desk-logan-weaver.jpg', 'Developer desk during installation', 'A tidy developer desk with a laptop, where a single composer command installs Desiderio.'),
                    ],
                    [
                        'title' => 'vendor/bin/typo3 desiderio:styleguide:seed',
                        'content' => 'Seeds 244 living examples — this very site — so you evaluate with real content instead of an empty page tree.',
                        'image' => ShowcaseBlocks::screenshot('backend-page-module-hero.png', 'Seeded page in the TYPO3 page module', 'TYPO3 page module showing a seeded Desiderio hero element with backend preview.'),
                    ],
                    [
                        'title' => 'Pick a theme preset',
                        'content' => 'Choose one of 15 presets in the site settings, or design your own on the create page on ui.shadcn.com and paste the code.',
                        'image' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset in the TYPO3 site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown opened.'),
                    ],
                    [
                        'title' => 'Publish',
                        'content' => 'Editors build pages from the wizard, preview every element in the backend, and edit inline in the Visual Editor.',
                        'image' => ShowcaseBlocks::screenshot('frontend-hero-lagoon.png', 'Published Desiderio frontend', 'The published Desiderio demo site rendered in the Lagoon theme preset.'),
                    ],
                ],
            ]),

            // ----------------------------------------------------- atomic design
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => 'Architecture',
                'header' => 'Atomic design, the way it was meant to work',
                'subheadline' => 'Brad Frost wrote the book; Desiderio wired it into Fluid 5. Small parts compose into bigger parts, and nothing is ever styled twice.',
                'variant' => 'center',
            ]),
            ShowcaseBlocks::block('desiderio_featurecards', [
                'eyebrow' => 'From atom to organism',
                'header' => 'One token contract from the smallest button to the biggest page',
                'subheadline' => 'Every layer is a real, typed Fluid 5 component in the d: namespace — not a naming convention, an enforced API.',
                'items' => [
                    ['title' => '17 atoms', 'description' => 'Button, Badge, Input, Icon, Avatar, Typography and friends. Each one token-only: change a preset and every atom follows. Typed f:argument contracts catch wrong usage at render time, not in production.'],
                    ['title' => '28 molecules', 'description' => 'Card, Accordion, Tabs, Table, Alert, form fields — atoms composed into reusable patterns with slots. Your custom elements get the same building blocks the 244 shipped ones use.'],
                    ['title' => '4 layout primitives', 'description' => 'Section, Container, Grid, Stack carry spacing, density, and container queries. Consistent rhythm across every element without a single hand-written margin.'],
                    ['title' => '4 site organisms', 'description' => 'Site header, footer, breadcrumb, and page header compose the lower layers behind typed d:organism contracts. Page templates only arrange organisms and content areas.'],
                    ['title' => '244 content patterns', 'description' => 'The editor-facing heroes, pricing tables, dashboards, and footers build on the same component vocabulary — which is why an 11-category audit can verify all of them, on every commit.'],
                ],
            ]),

            // ----------------------------------------------------- speed
            ShowcaseBlocks::block('desiderio_featurestats', [
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
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => 'The library',
                'header' => '244 content elements. Ready to use. Today.',
                'subheadline' => 'Not a UI kit you still have to assemble — finished, editor-friendly content elements with backend previews, demo fixtures, and accessibility built in.',
                'variant' => 'center',
            ]),
            ShowcaseBlocks::block('desiderio_featurecards', [
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
            ShowcaseBlocks::block('desiderio_featurecards', [
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
            ShowcaseBlocks::block('desiderio_gallery', [
                'header' => 'Five elements worth the detour',
                'subheadline' => '244 elements is a lot to scroll — so start with these five. Real screenshots from this very site, captured live. Click any to open its category and see it in motion.',
                'columns' => '3',
                'items' => [
                    ['title' => 'Parallax Hero', 'description' => 'A scroll-driven hero — the most cinematic first impression in the set, and one of 21 hero layouts.', 'link' => '{{page:chapter-hero}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-parallax-hero.png', 'Parallax Hero element', 'Desiderio Parallax Hero content element rendered with the Forest theme preset.')],
                    ['title' => 'Bento Feature Grid', 'description' => 'Mixed-size cards in the bento layout every modern product site wants, from the 25-strong features group.', 'link' => '{{page:chapter-features}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-bento-features.png', 'Bento Feature Grid element', 'Desiderio Bento Feature Grid content element with mixed-size feature tiles.')],
                    ['title' => 'Toggle Pricing', 'description' => 'Monthly and annual pricing with a highlighted plan — the block that quietly closes deals.', 'link' => '{{page:chapter-pricing}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-toggle-pricing.png', 'Toggle Pricing element', 'Desiderio Toggle Pricing content element with a monthly and yearly billing switch.')],
                    ['title' => 'Testimonial Wall', 'description' => 'A dense masonry wall of quotes: social proof you take in at a glance.', 'link' => '{{page:chapter-social-proof}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-testimonial-wall.png', 'Testimonial Wall element', 'Desiderio Testimonial Wall content element showing a masonry grid of customer quotes.')],
                    ['title' => 'Demo Request — a real ext:form', 'description' => 'A styled lead-capture section that renders a genuine TYPO3 form, where the gallery meets the forms story.', 'link' => '{{page:chapter-conversion}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-demo-request.png', 'Demo Request element', 'Desiderio Demo Request content element rendering a real TYPO3 Form Framework form.')],
                ],
            ]),

            // ----------------------------------------------------- target groups
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => 'Who it is for',
                'header' => 'Three teams, one unfair advantage',
                'subheadline' => 'Desiderio pays off differently depending on who you are. We wrote a page for each of you.',
                'variant' => 'center',
            ]),
            ShowcaseBlocks::block('desiderio_usecasegrid', [
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
            ShowcaseBlocks::block('desiderio_featurechecklist', [
                'eyebrow' => 'The advantages, in one list',
                'header' => 'Why teams pick Desiderio over building it themselves',
                'items' => [
                    ['title' => 'A redesign without the redesign budget', 'description_text' => '15 presets plus your own designs from the create page on ui.shadcn.com, switchable at runtime — per site or per page subtree.'],
                    ['title' => 'Editors who stop opening tickets', 'description_text' => 'Backend previews for all 244 elements, inline editing via Visual Editor, and a wizard organized in ten clear groups.'],
                    ['title' => 'Accessibility you can show the auditor', 'description_text' => 'WCAG 2.2-checked contrast on every preset (enforced by the build), landmarks, focus rings, and translated screen-reader labels.'],
                    ['title' => 'Quality that is measured, not promised', 'description_text' => 'PHPStan at level max, 170+ unit and functional tests on PHP 8.4/8.5, and an 11-category template audit at zero findings.'],
                    ['title' => 'Integrations already wired', 'description_text' => 'News, Powermail, Solr, Blog, Form Framework with 8 ready forms, Friendly Captcha with a dev-friendly bypass, Brevo double opt-in.'],
                    ['title' => 'Light and dark mode, both first-class', 'description_text' => 'Every preset ships a light and a dark token set with WCAG 2.2-checked contrast. Visitors get their system preference automatically; a header toggle lets them override it.'],
                    ['title' => 'A free core you can bet on', 'description_text' => 'GPL-2.0, full source on GitHub, no feature gates. Paid tiers buy speed and the creators\' time — never basic functionality.'],
                ],
            ]),

            // ----------------------------------------------------- integrations
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => 'Beyond content elements',
                'header' => 'Styled templates for the extensions you already run',
                'subheadline' => 'Desiderio does not stop at its own elements: the TYPO3 extensions your site depends on get the same shadcn treatment — light and dark mode included.',
                'variant' => 'center',
            ]),
            ShowcaseBlocks::block('desiderio_featurecards', [
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
            ShowcaseBlocks::block('desiderio_pricingthreetier', [
                'eyebrow' => 'Pricing',
                'header' => 'Free forever. Faster with the creators.',
                'subheadline' => 'The package is GPL and complete at €0. Pro and Agency buy guarantees, priority, and the people who built all 244 elements. Yearly billing adds two months free — and code DESIDERIO20 takes 20% off the first year.',
                'plans' => [
                    ['name' => 'Community', 'price' => '€0', 'billing_period' => 'forever', 'description' => 'The full package under GPL-2.0 — all elements, all themes, all integrations.', 'features' => ['All 244 content elements', '15 theme presets + per-page themes', 'One-command demo seeding', 'Community support on GitHub'], 'is_recommended' => false, 'button_text' => 'Install for free', 'button_link' => ShowcaseBlocks::REPO_URL],
                    ['name' => 'Pro', 'price' => '€49', 'billing_period' => 'per month · €490/year', 'description' => 'For teams shipping client or production sites on a deadline.', 'features' => ['Priority email support, 2-day response', 'Guaranteed LTS compatibility updates', 'Early access to element & preset drops', 'Minor-version upgrade assistance'], 'is_recommended' => true, 'button_text' => 'Go Pro', 'button_link' => ShowcaseBlocks::REPO_URL],
                    ['name' => 'Agency', 'price' => '€149', 'billing_period' => 'per month · €1,490/year', 'description' => 'Unlimited projects and a direct line to the maintainers.', 'features' => ['Everything in Pro, unlimited projects', 'Answers within 4 business hours (CET)', 'Quarterly editor onboarding session', 'Custom preset review by the creators'], 'is_recommended' => false, 'button_text' => 'Choose Agency', 'button_link' => ShowcaseBlocks::REPO_URL],
                ],
            ]),

            // ----------------------------------------------------- proof + CTA
            ShowcaseBlocks::block('desiderio_testimonialgrid', [
                'eyebrow' => 'Word gets around',
                'header' => 'Teams talk about Desiderio',
                'columns' => '3',
                'testimonials' => [
                    ['quote' => 'We demoed three themes in the kickoff meeting by switching presets live. The client signed that afternoon.', 'author_name' => 'Hannah Vogel', 'author_title' => 'Lead Integrator'],
                    ['quote' => 'Our editors build campaign pages themselves now. The backend previews mean nobody publishes blind anymore.', 'author_name' => 'Jonas Klein', 'author_title' => 'Head of Digital'],
                    ['quote' => 'As a freelancer I quote design-system quality at one-person prices. Desiderio is my unfair advantage.', 'author_name' => 'Lena Hoffmann', 'author_title' => 'Freelance TYPO3 Developer'],
                ],
            ]),
            ShowcaseBlocks::block('desiderio_ctabanner', [
                'header' => 'Your next TYPO3 site could look like this one',
                'description' => 'This whole demo — homepage, ten themed chapters, 244 elements — was seeded with one command. Install Desiderio for free, or have the creators set it up for €890.',
                'cta_text' => 'Get Desiderio on GitHub',
                'cta_link' => ShowcaseBlocks::REPO_URL,
                'bg_style' => 'primary',
            ]),
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
     * The theme presets, each rendered live in its own preset.
     *
     * The page body is generated from the compiled preset stylesheet
     * (Build/Scripts/build-preset-overview.php) and rendered by the
     * DesiderioThemes backend layout, so the cards and the comparison matrix
     * cannot drift from the presets. The content elements below it are the
     * editorial frame around that generated part.
     *
     * @return ShowcasePage
     */
    private static function themesPage(): array
    {
        return [
            'title' => 'Themes',
            'navTitle' => 'Themes',
            'slug' => '/themes',
            'backendLayout' => 'pagets__DesiderioThemes',
            'abstract' => 'All fifteen Desiderio theme presets side by side, each card rendered live in the preset it names — colours, fonts, corner radius, control density, focus ring and icon library.',
            'description' => 'Compare all 15 Desiderio theme presets: live samples of every palette, font pairing, corner radius and control density, switchable per site or per page tree without a rebuild.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Why switching a theme is not a project',
                    'content' => '<p>A preset is a set of OKLCH design tokens, nothing else. Selecting one writes a single <code>data-shadcn-preset</code> attribute onto the body tag, and every element repaints from the tokens that attribute pulls in — no Tailwind rebuild, no deployment, no touched content record. That is why the cards above can be live: the same tokens, scoped to a card instead of to the page, let fifteen presets render on one document.</p><p>The same field exists on every page. Set <strong>Theme preset</strong> in the page properties and the whole subtree below it follows, so a campaign microsite, a product section and the blog can each carry their own look inside one install and one content pool.</p>',
                    'variant' => 'muted',
                    'alignment' => 'left',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'How the theme layer is built',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'None of the fifteen is yours? Design one.',
                    'description' => 'Compose your own on the create page on ui.shadcn.com — palette, fonts, radius, style recipe — and paste the generated tokens into shadcn-theme.css as the custom preset. Desiderio speaks the same token language, so nothing is lost in translation.',
                    'cta_text' => 'Open the create page',
                    'cta_link' => ShowcaseBlocks::CREATE_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function rteCombinationsPage(): array
    {
        return [
            'title' => 'RTE combinations',
            'navTitle' => 'RTE combinations',
            'slug' => '/content-types/rte-combinations',
            'abstract' => 'A deterministic 100-block TYPO3 rich-text stress page covering every ordered adjacency across paragraphs, headings, lists, and quotations, plus inline editor styles.',
            'description' => 'Visual regression fixture for Desiderio rich text: 100 reproducible blocks cover all ordered p, h1-h5, ul, ol, and blockquote combinations with short, long, linked, bold, italic, and semantic inline content.',
            'parentSlug' => 'content-types',
            'pageTsConfig' => 'RTE.config.tt_content.bodytext.types.text.preset = desiderio',
            'hideInNav' => true,
            'content' => [
                ShowcaseBlocks::block('text', [
                    'header' => '100-block adjacency matrix',
                    'header_layout' => 2,
                    'subheader' => 'Every ordered pairing of the nine primary block types occurs at least once. The order is fixed so screenshots remain comparable between releases.',
                    'bodytext' => RteCombinationFixtures::bodytext(),
                ]),
            ],
        ];
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For the people who read changelogs',
                    'header' => 'Every claim, verifiable in the repository',
                    'subheadline' => 'The marketing said "magic". This page says how. Every claim below is verifiable in the public repository on GitHub.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contentgrid', [
                    'header' => 'The three pillars',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'Fluid 5.3 component system', 'content' => 'Forty-nine typed Fluid 5.3 components make up the d: namespace, registered through a ComponentCollection. Each one declares its arguments with f:argument contracts and composes through f:slot, so the wrong type fails loudly at render — never silently in production. Your templates get a real API, not a naming convention everyone has to remember.', 'link' => ShowcaseBlocks::REPO_URL],
                        ['title' => 'Runtime theme engine', 'content' => 'Fifteen shadcn presets, each a pure set of OKLCH design tokens on body[data-shadcn-preset], switch per site and per page subtree with no rebuild and no deployment. A contrast solver computes every palette to satisfy WCAG 2.2 in both light and dark mode — and refuses to emit CSS that would fail.', 'link' => ''],
                        ['title' => 'Measured quality pipeline', 'content' => 'Nothing here is promised, everything is enforced: PHPStan at level max, 170+ unit and functional tests on PHP 8.4 and 8.5, an eleven-category template audit at zero tolerance, and a CI job that fails the build the moment the Tailwind bundle drifts from the templates.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Credit where it is due',
                    'header' => 'Two upstream projects made all of this possible',
                    'subheadline' => 'Almost nothing on this page is our invention. Desiderio is a design system layered onto two remarkable pieces of TYPO3 engineering — and the people behind them have earned a direct thank-you.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contentgrid', [
                    'header' => 'Standing on excellent foundations',
                    'columns' => '2',
                    'items' => [
                        ['title' => 'Fluid 5 — thank you, Simon Praetorius', 'content' => 'Every one of the 53 Desiderio components is built on Fluid, and it is a genuine pleasure to work with. Simon Praetorius turned Fluid into a modern, component-based template engine where the (var-)typing is excellent: arguments are declared, checked, and the wrong type fails at render instead of in production. A fantastic template engine — we simply painted a design system on top of it.', 'link' => 'https://github.com/s2b'],
                        ['title' => 'Content Blocks — thank you, Nikita Hovratov', 'content' => 'All 244 Desiderio elements are Content Blocks, and describing one is a joy. Nikita Hovratov built a fantastic way to explain a content element to TYPO3 — a single declarative schema, automatic database columns, a backend preview — and it works flawlessly. It let us ship 244 elements instead of hand-writing TCA for each. Great work, and thank you.', 'link' => 'https://github.com/nhovratov'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'More gratitude',
                    'header' => 'And the rest of the stack we did not build',
                    'subheadline' => 'Fluid and Content Blocks are the foundation; the toolchain and integrations around them are just as much other people\'s excellent work. A direct thank-you to each — because none of it is ours.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
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
                            'title' => 'Security & platform — TYPO3 14.3+, PHP 8.4+, CSP-friendly',
                            'content' => '<p>Security treated as a platform feature on TYPO3 14.3+ / PHP 8.4+.</p><ul><li><strong>Hardened queries</strong> — strict types and QueryBuilder with named parameters.</li><li><strong>CSP-ready</strong> — nonce-aware asset rendering.</li><li><strong>Safe by default</strong> — middleware that logs and refuses the captcha bypass in production; schema-filtered seeder inserts.</li></ul>',
                            'open_by_default' => 0,
                        ],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'The entire installation',
                    'language' => 'Bash',
                    'filename' => 'install.sh',
                    'code' => "composer require webconsulting/desiderio\nvendor/bin/typo3 desiderio:styleguide:seed\n# pick a theme preset in the site settings — done.",
                ]),
                ShowcaseBlocks::block('desiderio_definitionlist', [
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
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Convinced by the facts?',
                    'description' => 'Install the free package, or skip the setup entirely: the creators install and configure Desiderio on your TYPO3 for €890 — brand adaptation from €1,990.',
                    'cta_text' => 'Start with the free core',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'muted',
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Content types',
                    'header' => '244 elements. Ten groups. One design system.',
                    'subheadline' => 'Every element is finished, audited and editor-ready — with a backend preview, demo content and accessibility built into each. Browse by what you need to build.',
                    'variant' => 'left',
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Every one of these ships in the free package',
                    'description' => '244 elements, 15 themes and all integrations — GPL-2.0, no feature gates. Install it and start building today.',
                    'cta_text' => 'Get Desiderio free',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
