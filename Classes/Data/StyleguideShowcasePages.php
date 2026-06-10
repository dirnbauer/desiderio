<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Marketing showcase seeded by desiderio:styleguide:seed in addition to the
 * element chapters: managed homepage content on the styleguide root page plus
 * subpages (technical deep dive, one page per target audience, legal demo
 * pages, a 404 page, a GEO explainer, and fictional success stories).
 *
 * Internal links use the placeholder syntax {{page:<slug>}}; the seeder
 * replaces them with t3://page?uid=N once the target pages exist. The special
 * slug {{page:home}} resolves to the styleguide root page. Pages with a
 * parentSlug are created below that showcase page instead of the root.
 *
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 * @phpstan-type ShowcasePage array{title: string, navTitle: string, slug: string, abstract: string, description: string, parentSlug: string|null, content: array<int, StarterBlock>}
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
                'subheadline' => 'Desiderio puts 255 shadcn-styled content elements, 49 typed Fluid components, and 15 runtime-switchable themes into one composer package. Install it before lunch. Re-theme it before the coffee gets cold.',
                'primary_button_text' => 'Start free — €0 forever',
                'primary_button_link' => self::REPO_URL,
                'primary_button_variant' => 'default',
                'secondary_button_text' => 'See the technical facts',
                'secondary_button_link' => '{{page:technical-features}}',
                'hero_image' => self::screenshot('backend-visual-editor.png', 'Editing a page in the Visual Editor', 'TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
                'image_position' => 'right',
                'overlay_opacity' => '0.5',
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
                        'content' => 'Seeds 255 living examples — this very site — so you evaluate with real content instead of an empty page tree.',
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
                    ['title' => '28 molecules', 'description' => 'Card, Accordion, Tabs, Table, Alert, form fields — atoms composed into reusable patterns with slots. Your custom elements get the same building blocks the 255 shipped ones use.'],
                    ['title' => '4 layout primitives', 'description' => 'Section, Container, Grid, Stack carry spacing, density, and container queries. Consistent rhythm across every element without a single hand-written margin.'],
                    ['title' => '255 organisms', 'description' => 'The content elements themselves: heroes, pricing tables, dashboards, footers. Built exclusively from the layers below — which is why an 11-category audit can verify all of them, on every commit.'],
                ],
            ]),

            // ----------------------------------------------------- speed
            self::block('desiderio_featurestats', [
                'header' => 'Fast where it counts: your site, your team, your timeline',
                'description' => 'Speed is not one number. Desiderio is engineered for three: runtime performance, editorial velocity, and project delivery time.',
                'items' => [
                    ['value' => '0', 'label' => 'Rebuilds for a redesign', 'description_text' => 'Theme switching is pure CSS tokens at runtime. No Tailwind rebuild, no deployment, no release window.'],
                    ['value' => '100%', 'label' => 'Static CSS, no JS framework', 'description_text' => 'No React, no Vue runtime in the frontend. Charts render server-side with a slim vanilla enhancement layer.'],
                    ['value' => '1', 'label' => 'Command to a full demo site', 'description_text' => 'The seeder builds 255 living examples in seconds — workspaces-safe and idempotent, run it as often as you like.'],
                    ['value' => '30', 'label' => 'Minutes from install to themed', 'description_text' => 'Composer install, seed, pick a preset, publish. Or book the creators and skip even that: installation service €890.'],
                ],
            ]),

            // ----------------------------------------------------- 255 elements
            self::block('desiderio_headersection', [
                'eyebrow' => 'The library',
                'header' => '255 content elements. Ready to use. Today.',
                'subheadline' => 'Not a UI kit you still have to assemble — finished, editor-friendly content elements with backend previews, demo fixtures, and accessibility built in.',
                'variant' => 'center',
            ]),
            self::block('desiderio_featurecards', [
                'eyebrow' => 'Ten groups, zero gaps',
                'header' => 'Whatever the page needs, the wizard already has it',
                'subheadline' => 'Every element appears in the new-content wizard with a real preview — browse the chapters of this styleguide to see all of them live, each chapter in its own theme.',
                'items' => [
                    ['title' => '25 heroes & landing intros', 'description' => 'Split, animated, countdown, video, stats, product — the first screen of every campaign, ready in minutes.'],
                    ['title' => '25 feature & benefit blocks', 'description' => 'Grids, bento layouts, comparisons, timelines, tabs. Explain any product without briefing a designer.'],
                    ['title' => '25 pricing & product elements', 'description' => 'Tier tables, calculators, sliders, order summaries — monetization patterns that usually cost a sprint.'],
                    ['title' => '32 data & dashboard elements', 'description' => 'Nine chart types, KPI cards, changelogs, status boards — all server-rendered with accessible data tables.'],
                    ['title' => '49 trust & people elements', 'description' => 'Testimonials, case studies, logo walls, team grids — social proof in every shape your sales team can dream up.'],
                    ['title' => '99 more for everything else', 'description' => 'Navigation, footers, legal, forms, editorial content. The unglamorous 80% of every site — already done.'],
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
                        'description' => 'Quote less, deliver more. 255 finished elements and per-page themes turn every fixed-price project into margin — and every client review into a yes.',
                        'link' => '{{page:for-agencies}}',
                    ],
                    [
                        'title' => 'In-house marketing & product teams',
                        'description' => 'Ship campaigns without filing a dev ticket. Editors compose pages from previews, switch themes per campaign, and stay on brand automatically.',
                        'link' => '{{page:for-inhouse-teams}}',
                    ],
                    [
                        'title' => 'Freelancers & solo developers',
                        'description' => 'Look like a team of ten. A complete design system, quality pipeline included, for €0 — so your one-person studio ships agency-grade sites.',
                        'link' => '{{page:for-freelancers}}',
                    ],
                ],
            ]),

            // ----------------------------------------------------- advantages
            self::block('desiderio_featurechecklist', [
                'eyebrow' => 'The advantages, in one list',
                'header' => 'Why teams pick Desiderio over building it themselves',
                'items' => [
                    ['title' => 'A redesign without the redesign budget', 'description_text' => '15 presets plus your own designs from the create page on ui.shadcn.com, switchable at runtime — per site or per page subtree.'],
                    ['title' => 'Editors who stop opening tickets', 'description_text' => 'Backend previews for all 255 elements, inline editing via Visual Editor, and a wizard organized in ten clear groups.'],
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
                    ['title' => 'Powermail', 'description' => 'All field types restyled with the Desiderio form partials, five seeded demo forms with thank-you flows, and Friendly Captcha wired in.'],
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
                'subheadline' => 'The package is GPL and complete at €0. Pro and Agency buy guarantees, priority, and the people who built all 255 elements. Yearly billing adds two months free — and code DESIDERIO20 takes 20% off the first year.',
                'plans' => [
                    ['name' => 'Community', 'price' => '€0', 'billing_period' => 'forever', 'description' => 'The full package under GPL-2.0 — all elements, all themes, all integrations.', 'features' => ['All 255 content elements', '15 theme presets + per-page themes', 'One-command demo seeding', 'Community support on GitHub'], 'is_recommended' => false, 'button_text' => 'Install for free', 'button_link' => self::REPO_URL],
                    ['name' => 'Pro', 'price' => '€49', 'billing_period' => 'per month · €490/year', 'description' => 'For teams shipping client or production sites on a deadline.', 'features' => ['Priority email support, 2-day response', 'Guaranteed LTS compatibility updates', 'Early access to element & preset drops', 'Minor-version upgrade assistance'], 'is_recommended' => true, 'button_text' => 'Go Pro', 'button_link' => self::REPO_URL],
                    ['name' => 'Agency', 'price' => '€149', 'billing_period' => 'per month · €1,490/year', 'description' => 'Unlimited projects and a direct line to the maintainers.', 'features' => ['Everything in Pro, unlimited projects', '4-hour priority response', 'Quarterly editor onboarding session', 'Custom preset review by the creators'], 'is_recommended' => false, 'button_text' => 'Choose Agency', 'button_link' => self::REPO_URL],
                ],
            ]),

            // ----------------------------------------------------- proof + CTA
            self::block('desiderio_testimonialgrid', [
                'eyebrow' => 'Word gets around',
                'header' => 'Teams talk about Desiderio',
                'columns' => '3',
                'testimonials' => [
                    ['quote' => 'We demoed three themes in the kickoff meeting by switching presets live. The client signed that afternoon.', 'author_name' => 'Hannah Vogel', 'author_title' => 'Lead Integrator, fictional agency'],
                    ['quote' => 'Our editors build campaign pages themselves now. The backend previews mean nobody publishes blind anymore.', 'author_name' => 'Jonas Klein', 'author_title' => 'Head of Digital, fictional brand team'],
                    ['quote' => 'As a freelancer I quote design-system quality at one-person prices. Desiderio is my unfair advantage.', 'author_name' => 'Lena Hoffmann', 'author_title' => 'Freelance TYPO3 Developer'],
                ],
            ]),
            self::block('desiderio_ctabanner', [
                'header' => 'Your next TYPO3 site could look like this one',
                'description' => 'This whole demo — homepage, ten themed chapters, 255 elements — was seeded with one command. Install Desiderio for free, or have the creators set it up for €890.',
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
            self::agencyPage(),
            self::inhousePage(),
            self::freelancerPage(),
            self::geoAiSearchPage(),
            self::successStoriesPage(),
            self::successStoryAnthropicPage(),
            self::successStorySpacexPage(),
            self::successStoryOpenaiPage(),
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
                        ['title' => 'Fluid 5.3 component system', 'content' => '49 components with typed f:argument contracts and f:slot composition, registered as the d: namespace via a ComponentCollection. Wrong argument types fail loudly at render time — your templates get an API, not a convention.', 'link' => self::REPO_URL],
                        ['title' => 'Runtime theme engine', 'content' => '15 shadcn presets as pure OKLCH token sets on body[data-shadcn-preset], switchable per site and per page subtree without any rebuild. A contrast solver guarantees WCAG 2.2 ratios on every preset, in light and dark mode.', 'link' => ''],
                        ['title' => 'Measured quality pipeline', 'content' => 'PHPStan level max, 170+ unit and functional tests across PHP 8.3/8.4, an 11-category template audit with zero tolerance, and a CI job that fails when the Tailwind bundle drifts from the templates.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'The complete technical feature list',
                    'allow_multiple' => 1,
                    'items' => [
                        [
                            'title' => 'Fluid 5.3 — typed components, slots, ICU',
                            'content' => 'Components declare typed f:argument contracts (string, bool, object, with defaults and optionality) and compose via f:slot. The d: namespace resolves through a ComponentCollection class, so atoms, molecules, and layout primitives are first-class citizens: d:atom.button, d:molecule.card, d:layout.section. Dates and plural-sensitive strings render through ICU MessageFormat — pagination like "Page 3 of 12" and date patterns localize correctly in every language.',
                            'open_by_default' => 1,
                        ],
                        [
                            'title' => 'Translations — XLIFF 2.0, English + German, translated ARIA',
                            'content' => 'Every user-facing string — including screen-reader labels, carousel controls, dismiss buttons, and pagination — runs through f:translate with XLIFF 2.0 catalogues. English and German ship complete; element-local label files cover per-element strings in five locales. Stored content keeps stable icon and preset keys, so switching icon libraries or themes never rewrites records.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Content Blocks 2.2 — schema-first elements with previews',
                            'content' => 'All 255 elements are TYPO3 Content Blocks: declarative config.yaml schemas, automatic database columns, backend preview templates, and collection child records with explicit table mappings. Demo fixtures ship per element, which is how the seeder builds this entire styleguide in one command.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Theme engine — OKLCH tokens, per-page presets, solved contrast',
                            'content' => 'Five presets from the create page on ui.shadcn.com and ten generated house presets define accent, radius, typography, density, focus-ring width, and elevation as OKLCH custom properties. The generator solves accent lightness per hue against WCAG 2.2 targets (4.5:1 text, 3:1 UI) and refuses to emit failing CSS; a unit test re-checks the shipped bundle. Per-page presets inherit down the rootline via TypoScript levelfield slide. Dark mode, five icon libraries, and density/radius/font settings are runtime switches.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'CSS architecture — Tailwind v4, container queries, BEM',
                            'content' => 'Tailwind v4 with @source scanning over templates and components, cascade layers, and tw-animate. Element-specific styles live in per-element BEM files concatenated by manifest. Sections establish container queries, so elements respond to their actual width — not just the viewport. No CSS-in-JS, no runtime framework, no hydration cost.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Integrations — News, Powermail, Solr, Blog, Forms, Captcha, Brevo',
                            'content' => 'shadcn-styled template sets for georgringer/news, in2code/powermail, Apache Solr, and t3g/blog. Eight ready Form Framework definitions with a Brevo finisher that supports double opt-in (contacts join lists only after e-mail confirmation). Friendly Captcha integration with a production-safe bypass matrix: real captcha in production, automatic bypass in Development context, and a force-real switch for testing keys on ddev. Visual Editor inline editing supported throughout.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Seeding & CLI — idempotent, workspaces-safe demo content',
                            'content' => 'Three commands: desiderio:styleguide:seed (this site), desiderio:starter:seed (a complete corporate starter), desiderio:blog:seed-pages. All idempotent — reseeding soft-deletes the previous generation — and all refuse to run inside workspaces or in Production context without an explicit flag.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Security & platform — TYPO3 14.3+, PHP 8.3+, CSP-friendly',
                            'content' => 'Strict types everywhere, QueryBuilder with named parameters, nonce-aware asset rendering for CSP, hardened middleware (the captcha bypass logs and refuses in production), and schema-filtered inserts in the seeder. Requires TYPO3 14.3+ on PHP 8.3 or 8.4.',
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
                        ['term' => 'Atom / Molecule / Layout primitive', 'definition' => 'The three Fluid 5 component layers (17 + 28 + 4) that all 255 content elements are built from — typed, slotted, token-only.'],
                        ['term' => 'Content Block', 'definition' => 'A TYPO3 content element defined by a declarative schema with automatic database columns and a backend preview. Desiderio ships 255 of them.'],
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
     * @return ShowcasePage
     */
    private static function agencyPage(): array
    {
        return [
            'title' => 'For agencies & integrators',
            'navTitle' => 'For agencies',
            'slug' => '/for-agencies',
            'abstract' => 'Why TYPO3 agencies quote less, deliver faster, and win more pitches with Desiderio: reusable elements, live theme demos, multi-brand per-page themes, and an Agency tier with a direct line to the maintainers.',
            'description' => 'Win TYPO3 pitches with live theme demos and 255 finished elements. Desiderio deletes template hours from every fixed-price quote — Agency tier from €149/month.',
            'parentSlug' => null,
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
                    'content' => 'Switch theme presets live while the client watches. Lagoon for the calm corporate look, Midnight for the product launch, their own design from the shadcn/ui create page for the brand pitch — same content, three designs, zero preparation. Then set a different preset per page subtree and run every sub-brand from one install. Multi-brand used to be a budget line; now it is a dropdown.',
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
                        ['icon' => 'zap', 'title' => 'Quote with confidence', 'description' => '255 finished, audited elements mean your estimate covers content modeling and integration — not weeks of template construction.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'Hand over without fear', 'description' => 'Editors get previews, a clean wizard, and inline editing. Your support inbox notices the difference in week one.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'White-label adaptation', 'description' => 'Need a fully custom preset for a flagship client? Brand adaptation from €1,990, custom elements from €390 — built by the creators, delivered as your work.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'A direct line when it matters', 'description' => 'The Agency tier (€149/month or €1,490/year, unlimited projects) includes 4-hour priority response and quarterly editor onboarding for your clients.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'We demoed three themes in the kickoff by switching presets live. The client signed that afternoon — and the project closed 30% under our usual template budget.',
                    'author_name' => 'Hannah Vogel',
                    'author_title' => 'Lead Integrator',
                    'author_company' => 'fictional agency, seeded demo',
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
            'slug' => '/for-inhouse-teams',
            'abstract' => 'Why in-house teams ship campaigns without dev tickets: editor previews, per-campaign themes, brand governance through tokens, managed hosting, and Pro support with guaranteed LTS updates.',
            'description' => 'Ship campaign pages without dev tickets: backend previews for all 255 Desiderio elements, per-campaign themes, and brand governance enforced by design tokens.',
            'parentSlug' => null,
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
                    'content' => 'Every one of the 255 elements shows a real preview in the page module, the new-content wizard sorts them into ten plain-language groups, and the Visual Editor lets editors change text right on the page. Campaign microsites get their own theme preset per page tree — Midnight for the launch, your house preset for everything else — while the token system makes off-brand colors literally impossible.',
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
                self::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'Our editors build campaign pages themselves now. The backend previews mean nobody publishes blind anymore — and our brand team finally sleeps at night.',
                    'author_name' => 'Jonas Klein',
                    'author_title' => 'Head of Digital',
                    'author_company' => 'fictional brand team, seeded demo',
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
            'slug' => '/for-freelancers',
            'abstract' => 'Why solo developers deliver agency-grade TYPO3 sites with Desiderio: a complete free design system, quality pipeline included, fixed-price projects that stay profitable, and Pro support as the safety net.',
            'description' => 'Agency-grade TYPO3 sites from a studio of one: Desiderio gives freelancers a complete free design system, 255 elements, and a CI-grade quality pipeline for €0.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Target group 3 — freelancers & solo devs',
                    'header' => 'Look like a team of ten. Bill like one.',
                    'subheadline' => 'A complete design system, 255 elements, and a CI-grade quality pipeline — for exactly €0. Your one-person studio just got an unfair advantage.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Fixed-price projects that stay profitable',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The solo math',
                    'content' => 'The dangerous part of every fixed-price quote is the design-and-template phase — open-ended, opinion-driven, unbillable when it overruns. Desiderio closes it: pick a preset (or let the client pick on ui.shadcn.com/create), seed the demo, and walk the client through 255 real elements instead of wireframes. What used to take three weeks of template work is an afternoon of content modeling.',
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
                self::block('desiderio_testimonial', [
                    'variant' => 'large',
                    'quote' => 'As a freelancer I quote design-system quality at one-person prices. Clients compare my demos with agency pitches — and I win.',
                    'author_name' => 'Lena Hoffmann',
                    'author_title' => 'Freelance TYPO3 Developer',
                    'author_company' => 'seeded demo persona',
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
                        ['title' => 'Semantic HTML, by construction', 'description' => 'Landmarks, native elements, and one logical heading hierarchy per page are baked into all 255 elements. Extractors do not have to guess where the answer starts.'],
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
                            'content' => 'When the answer appears in the overview, fewer people click through — publishers across industries report falling click-through rates on queries with AI answers. Plan for it: make the pages that do get visited convert better, and treat newsletters, communities, and direct traffic as first-class channels.',
                            'open_by_default' => 1,
                        ],
                        [
                            'title' => 'Attribution is uncertain',
                            'content' => 'Assistants cite inconsistently, sometimes paraphrase without a link, and analytics tools are still learning to separate AI referrals from the rest. Measure what you can (referral domains like chatgpt.com and perplexity.ai), and be skeptical of anyone selling guaranteed AI rankings.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'AI Overviews are volatile',
                            'content' => 'Which queries trigger an overview changes constantly — industry analyses, including Lily Ray\'s ongoing coverage of AI Overview volatility, show large swings within weeks. Build for durable extraction quality instead of chasing individual snapshots.',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'What Desiderio gives you out of the box',
                            'content' => 'Semantic landmark markup and heading discipline in every element, per-page meta and Open Graph support, FAQ and how-to elements for question-shaped content, translated screen-reader labels that double as machine-readable structure, and server-rendered performance. GEO-readiness as a side effect of doing HTML properly.',
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
    private static function successStoriesPage(): array
    {
        return [
            'title' => 'Success stories',
            'navTitle' => 'Success stories',
            'slug' => '/success-stories',
            'abstract' => 'Three clearly fictional enterprise scenarios that ask one question with a straight face: what would happen if the most ambitious companies on the planet ran their websites on TYPO3 with Desiderio?',
            'description' => 'What if Anthropic, SpaceX, and OpenAI ran on TYPO3? Three clearly fictional, deliberately fun Desiderio showcase scenarios about multi-brand theming and editor velocity.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Clearly fictional. Deliberately fun.',
                    'header' => 'What if the world\'s most ambitious companies ran on TYPO3?',
                    'subheadline' => 'None of the companies below use Desiderio (yet). These are invented showcase scenarios — written to demonstrate, with a wink, how per-page themes, editor previews, and open-source alignment would play out at enterprise scale.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_casestudygrid', [
                    'eyebrow' => 'Three thought experiments',
                    'header' => 'Pick your favorite alternate universe',
                    'columns' => '3',
                    'cases' => [
                        [
                            'client_name' => 'Anthropic (fictional scenario)',
                            'summary' => 'A safety-first AI lab with a research blog that publishes faster than most newsrooms. In our invented universe, Claude\'s makers seed their publication pipeline with one CLI command.',
                            'result' => '"Research to live in 1h"',
                            'image' => self::unsplash('customer-engineering.jpg', 'Engineering team at work', 'An engineering team collaborating in front of large screens.'),
                            'link' => '{{page:success-stories/anthropic}}',
                        ],
                        [
                            'client_name' => 'SpaceX (fictional scenario)',
                            'summary' => 'Rockets, satellites, and a launch calendar that never sleeps. In this fiction, every mission microsite is a TYPO3 subtree with its own Desiderio preset — Midnight for night launches, obviously.',
                            'result' => '"1 CMS, 14 missions"',
                            'image' => self::unsplash('planning-blue-sky.jpg', 'Planning under a blue sky', 'People planning a project outdoors under a clear blue sky.'),
                            'link' => '{{page:success-stories/spacex}}',
                        ],
                        [
                            'client_name' => 'OpenAI (fictional scenario)',
                            'summary' => 'Product lines multiplying faster than pricing pages can keep up. Our invented OpenAI web team stops rebuilding and starts re-theming: one content pool, one preset per product family.',
                            'result' => '"0 rebuilds per launch"',
                            'image' => self::unsplash('dashboard-neil-fernandez.jpg', 'Analytics dashboard on a screen', 'A laptop showing a product analytics dashboard.'),
                            'link' => '{{page:success-stories/openai}}',
                        ],
                    ],
                ]),
                self::block('desiderio_contenthighlight', [
                    'header' => 'Why the joke works',
                    'content' => '<p>Swap the famous logos for your clients and every argument still holds: multi-brand sites from one install via per-page themes, editors who publish without tickets, open-source licensing that survives procurement, and self-hosting for teams that keep their infrastructure close. The companies are fictional guests — the capabilities are shipping today.</p>',
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
    private static function successStoryAnthropicPage(): array
    {
        return [
            'title' => 'Anthropic on TYPO3 — a fictional scenario',
            'navTitle' => 'Anthropic (fictional)',
            'slug' => '/success-stories/anthropic',
            'abstract' => 'A clearly fictional showcase scenario: how an AI safety lab like Anthropic would run its research publishing, product pages, and trust center on TYPO3 with Desiderio.',
            'description' => 'Fictional showcase: Anthropic\'s research blog on TYPO3 — Desiderio editor previews for fast publishing, per-page themes per product line, and self-hosted open source.',
            'parentSlug' => 'success-stories',
            'content' => [
                self::block('desiderio_articlehero', [
                    'eyebrow' => 'Fictional showcase scenario — invented by the Desiderio team',
                    'header' => 'What if Anthropic ran on TYPO3?',
                    'dek' => 'An AI safety lab publishes interpretability papers, model cards, and policy posts at a pace that breaks most CMS workflows. In this invented universe, the Claude makers solve it the boring way: open-source TYPO3, Desiderio elements, and editors who never wait for a deploy.',
                    'topic' => 'Fiction',
                    'publish_date' => 'April 1, 2026',
                    'reading_time' => '4 min read',
                    'author_name' => 'The Desiderio storytellers',
                    'cover_image' => self::unsplash('customer-engineering.jpg', 'Research engineering team', 'An engineering team reviewing research output on large displays.'),
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: research velocity without a web team in the loop',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why our fictional Anthropic picks TYPO3 + Desiderio',
                    'content' => 'In this scenario the lab\'s researchers write, and the CMS keeps up: every paper lands as an article hero plus FAQ plus chart elements, previewed in the backend before anyone hits publish. The safety team gets its own page subtree in a calm Lagoon preset; product pages for Claude run Midnight. Open weights, open source — a GPL design system matches the culture, and self-hosting keeps infrastructure under the lab\'s own keys. Every claim about the fictional company is invented; every capability mentioned ships in Desiderio today.',
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
                    'author' => 'Fictional Head of Web Platform',
                    'role' => 'invented persona — no real Anthropic statement',
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
    private static function successStorySpacexPage(): array
    {
        return [
            'title' => 'SpaceX on TYPO3 — a fictional scenario',
            'navTitle' => 'SpaceX (fictional)',
            'slug' => '/success-stories/spacex',
            'abstract' => 'A clearly fictional showcase scenario: how a launch provider like SpaceX would run mission microsites, countdowns, and status dashboards on TYPO3 with Desiderio per-page themes.',
            'description' => 'Fictional showcase: SpaceX mission microsites on TYPO3 — every launch a Desiderio-themed page subtree with countdown heroes, status boards, and zero rebuilds.',
            'parentSlug' => 'success-stories',
            'content' => [
                self::block('desiderio_articlehero', [
                    'eyebrow' => 'Fictional showcase scenario — invented by the Desiderio team',
                    'header' => 'What if SpaceX ran on TYPO3?',
                    'dek' => 'A company that launches rockets weekly cannot wait for a website rebuild between missions. In this invented universe, every mission gets its own TYPO3 subtree, its own Desiderio preset, and a countdown hero that the comms team configures over coffee.',
                    'topic' => 'Fiction',
                    'publish_date' => 'April 1, 2026',
                    'reading_time' => '4 min read',
                    'author_name' => 'The Desiderio storytellers',
                    'cover_image' => self::unsplash('planning-blue-sky.jpg', 'Mission planning under open sky', 'A team planning outdoors under a wide blue sky.'),
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: a microsite per mission, a preset per brand',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Why our fictional SpaceX picks TYPO3 + Desiderio',
                    'content' => 'Starship in Ember, crewed flights in Marine, night launches in Midnight — in this scenario each mission microsite is a page subtree that inherits its own theme preset, while the content pool, the editors, and the install stay singular. Countdown heroes handle T-minus, stats elements track the booster fleet, and the status board element mirrors range weather. When a launch scrubs, an editor reschedules the countdown — nobody redeploys anything. The company is fiction; the per-page theme engine, countdown and dashboard elements are stock Desiderio.',
                    'media_rounded' => 1,
                    'button_text' => 'See per-page themes explained',
                    'button_link' => '{{page:technical-features}}',
                    'media' => self::screenshot('frontend-pricing-midnight-dark.png', 'Dark Midnight preset page', 'A Desiderio page rendered in the dark Midnight theme preset.'),
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Telemetry from the alternate timeline',
                    'description' => 'Made-up numbers with believable trajectories: what launch-cadence publishing looks like when the CMS is not the bottleneck.',
                    'items' => [
                        ['value' => '14', 'label' => 'Mission microsites live', 'description_text' => 'One TYPO3 install, fourteen themed subtrees in this fictional fleet.'],
                        ['value' => '45 min', 'label' => 'Scrub to rescheduled site', 'description_text' => 'Countdown retargeted, status board updated, hero re-published — editors only.'],
                        ['value' => '0', 'label' => 'Rebuilds between launches', 'description_text' => 'Theme presets switch at runtime; the imagined launch tempo never waits for CI.'],
                        ['value' => '99.9%', 'label' => 'Uptime target met', 'description_text' => 'Server-rendered pages with no JS framework survive every fictional traffic spike.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We reuse boosters because rebuilding them for every flight would be absurd. Rebuilding the website for every mission was the same absurdity — so we stopped.',
                    'author' => 'Fictional Director of Mission Communications',
                    'role' => 'invented persona — no real SpaceX statement',
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
    private static function successStoryOpenaiPage(): array
    {
        return [
            'title' => 'OpenAI on TYPO3 — a fictional scenario',
            'navTitle' => 'OpenAI (fictional)',
            'slug' => '/success-stories/openai',
            'abstract' => 'A clearly fictional showcase scenario: how a fast-shipping AI company like OpenAI would keep product launches, pricing pages, and developer docs on brand with TYPO3 and Desiderio.',
            'description' => 'Fictional showcase: OpenAI product launches on TYPO3 — one Desiderio content pool, a theme preset per product family, and pricing pages editors update themselves.',
            'parentSlug' => 'success-stories',
            'content' => [
                self::block('desiderio_articlehero', [
                    'eyebrow' => 'Fictional showcase scenario — invented by the Desiderio team',
                    'header' => 'What if OpenAI ran on TYPO3?',
                    'dek' => 'When a company ships a new product line every quarter, the website becomes the slowest model in the lineup. In this invented universe, the web team trades rebuilds for re-theming: one content pool, one Desiderio preset per product family, launch pages assembled before the keynote ends.',
                    'topic' => 'Fiction',
                    'publish_date' => 'April 1, 2026',
                    'reading_time' => '4 min read',
                    'author_name' => 'The Desiderio storytellers',
                    'cover_image' => self::unsplash('dashboard-neil-fernandez.jpg', 'Product dashboard on a laptop', 'A laptop screen showing a product analytics dashboard.'),
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: launch pages at model speed',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why our fictional OpenAI picks TYPO3 + Desiderio',
                    'content' => 'Each product family runs its own preset in this scenario — a calm neutral for the platform, something warmer for consumer apps — inherited down each page subtree from a single install. Pricing tables, comparison matrices, and usage calculators are stock Desiderio elements, so the imagined marketing team updates tiers minutes after a pricing call instead of filing a ticket. And when procurement asks about vendor lock-in, the answer is a GPL license and a composer.json. The company is borrowed for the story; the elements and the theme engine are real.',
                    'media_rounded' => 1,
                    'button_text' => 'Browse the pricing elements',
                    'button_link' => '{{page:chapter-pricing}}',
                    'media' => self::screenshot('backend-site-settings-theme.png', 'Theme preset selection in site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown.'),
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Benchmarks nobody ran, in a universe nobody visited',
                    'description' => 'Invented but plausible: what shipping velocity looks like when the website re-themes instead of rebuilding.',
                    'items' => [
                        ['value' => '6', 'label' => 'Product families, one install', 'description_text' => 'Each with its own preset and page subtree in this fictional setup.'],
                        ['value' => '2 h', 'label' => 'Keynote to live launch page', 'description_text' => 'Hero, feature grid, pricing table, FAQ — composed from existing elements.'],
                        ['value' => '0', 'label' => 'Rebuilds per product launch', 'description_text' => 'Runtime token switching makes the imagined design refresh a dropdown choice.'],
                        ['value' => '255', 'label' => 'Elements on the shelf', 'description_text' => 'The one number in this story that is not fictional.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We benchmark everything, so we benchmarked our website workflow. Re-theming beat rebuilding on every metric — tokens per launch went to zero, in the good way.',
                    'author' => 'Fictional Head of Web Experience',
                    'role' => 'invented persona — no real OpenAI statement',
                    'variant' => 'large',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Ship your next launch page the fictional-OpenAI way',
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
    private static function imprintPage(): array
    {
        return [
            'title' => 'Imprint',
            'navTitle' => 'Imprint',
            'slug' => '/imprint',
            'abstract' => 'Demo imprint page seeded by the Desiderio styleguide: the desiderio_imprint element filled with clearly fictional placeholder data, plus a note on replacing it before go-live.',
            'description' => 'Demo imprint built with the Desiderio imprint content element — fictional placeholder company data showing the legal-page structure for TYPO3 sites.',
            'parentSlug' => null,
            'content' => [
                self::block('desiderio_imprint', [
                    'header' => 'Imprint (demo data)',
                    'company_name' => 'webconsulting studio GmbH (fictional demo company)',
                    'address' => "Lindengasse 12\n1070 Vienna\nAustria",
                    'contact_email' => 'legal@webconsulting.example',
                    'contact_phone' => '+43 1 555 0182',
                    'registry_info' => "Commercial register: FN 000000x (demo data)\nCommercial Court of Vienna",
                    'vat_id' => 'ATU00000000',
                    'additional_info' => '<p>Responsible for content under §25 MedienG: Mara Lindqvist, Managing Director (fictional). Every value on this page is seeded placeholder data from the Desiderio styleguide.</p>',
                ]),
                self::block('desiderio_contenthighlight', [
                    'header' => 'This is a demo placeholder',
                    'content' => '<p>The desiderio_imprint element gives your legal page a finished, token-themed structure — but the law cares about the content. Replace the fictional company, register, and contact data above with your real details (and have them reviewed) before this page goes anywhere near production.</p>',
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
                    'intro' => 'Seeded demo notice for the fictional webconsulting studio site. It demonstrates the structure of a GDPR-style privacy page with the desiderio_privacynotice element — replace every section with your own reviewed text before go-live.',
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
                    'content' => '<p>This demo statement ships with the Desiderio styleguide as a template — replace it with your own audited statement before go-live. Desiderio content elements are engineered against WCAG 2.2 Level AA.</p><h3>What the markup gives you</h3><ul><li>Every interactive component is keyboard reachable, with visible focus states on buttons, links, and form fields.</li><li>Semantic landmarks, native elements, and a logical heading order come baked into all 255 elements.</li><li>Image fields carry alternative-text inputs; decorative SVG icons are hidden from screen readers.</li><li>An 11-category template audit keeps inline styles and hardcoded colors out of every release.</li></ul><h3>What remains your job</h3><p>Editor-entered content, embedded media, and uploaded documents still need human review. This demo template does not replace a real conformance audit of your site.</p>',
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
            'description' => 'Page not found — but 255 Desiderio content elements are exactly where they should be. Jump to the styleguide chapters, audience pages, or back to the homepage.',
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
                                ['label' => 'Agencies & integrators', 'link' => '{{page:for-agencies}}'],
                                ['label' => 'In-house teams', 'link' => '{{page:for-inhouse-teams}}'],
                                ['label' => 'Freelancers & solo devs', 'link' => '{{page:for-freelancers}}'],
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
                    'description' => 'The homepage has the whole story: 255 elements, 15 themes, and the one command that seeded this site (404 page included).',
                    'cta_text' => 'Take me home',
                    'cta_link' => '{{page:home}}',
                    'bg_style' => 'primary',
                ]),
            ],
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
