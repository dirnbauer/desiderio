<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Marketing showcase seeded by desiderio:styleguide:seed in addition to the
 * element chapters: managed homepage content on the styleguide root page plus
 * four subpages (technical deep dive and one page per target audience).
 *
 * Internal links use the placeholder syntax {{page:<slug>}}; the seeder
 * replaces them with t3://page?uid=N once the target pages exist.
 *
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 * @phpstan-type ShowcasePage array{title: string, navTitle: string, slug: string, abstract: string, content: array<int, StarterBlock>}
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

            // ----------------------------------------------------- shadcn/create
            self::block('desiderio_headersection', [
                'eyebrow' => 'The principle behind the whole project',
                'header' => 'From ui.shadcn.com/create to a live TYPO3 theme',
                'subheadline' => 'Desiderio is built on one radical idea: you should design your site where the best design tooling lives — and run it where your content lives. Pick a design on shadcn/create. Paste one preset code into TYPO3. Done.',
                'variant' => 'center',
            ]),
            self::block('desiderio_featurealternating', [
                'header' => 'Three steps. No build pipeline. No agency invoice.',
                'subheadline' => 'shadcn/create is the official theme designer of the shadcn/ui ecosystem. Desiderio mirrors its token model one to one — which makes it your TYPO3 theme editor.',
                'items' => [
                    [
                        'title' => '1 · Design on ui.shadcn.com/create',
                        'description' => 'On shadcn/create you compose a complete design system in the browser: base color and neutral palette, font pairing, border radius, and one of eight structural style recipes — Vega, Nova, Maia, Lyra, Mira, Luma, Sera, or Rhea — that define focus rings, transitions, and control shapes. Every choice compiles down to design tokens, and the whole result exports as one small preset code.',
                        'image' => self::screenshot('backend-site-settings-theme.png', 'Theme settings in the TYPO3 backend', 'TYPO3 site settings showing the Desiderio theme preset selection.'),
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
            self::block('desiderio_howtosteps', [
                'header' => 'Your first themed site in four commands',
                'description' => 'This is the whole onboarding. No starter kit purchase, no license key, no setup wizard with nine screens.',
                'items' => [
                    ['title' => 'composer require webconsulting/desiderio', 'content' => 'One package brings the elements, the components, and the theme layer. TYPO3 14.3+, PHP 8.3+, done.'],
                    ['title' => 'vendor/bin/typo3 desiderio:styleguide:seed', 'content' => 'Seeds 255 living examples — this very site — so you evaluate with real content instead of an empty page tree.'],
                    ['title' => 'Pick a theme preset', 'content' => 'Choose one of 15 presets in the site settings, or design your own on ui.shadcn.com/create and paste the code.'],
                    ['title' => 'Publish', 'content' => 'Editors build pages from the wizard, preview every element in the backend, and edit inline in the Visual Editor.'],
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
                    ['title' => 'A redesign without the redesign budget', 'description_text' => '15 presets plus your own shadcn/create designs, switchable at runtime — per site or per page subtree.'],
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
            'content' => [
                self::block('desiderio_headerpage', [
                    'eyebrow' => 'For the people who read changelogs',
                    'header' => 'Technical features',
                    'subheadline' => 'The marketing said "magic". This page says how. Every claim below is verifiable in the public repository.',
                    'variant' => 'center',
                    'button_text' => 'Read the source',
                    'button_link' => self::REPO_URL,
                ]),
                self::block('desiderio_contentgrid', [
                    'header' => 'The three pillars',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'Fluid 5.3 component system', 'content' => '49 components with typed f:argument contracts and f:slot composition, registered as the d: namespace via a ComponentCollection. Wrong argument types fail loudly at render time — your templates get an API, not a convention.', 'link' => ''],
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
                            'content' => 'Five shadcn/create presets and ten generated house presets define accent, radius, typography, density, focus-ring width, and elevation as OKLCH custom properties. The generator solves accent lightness per hue against WCAG 2.2 targets (4.5:1 text, 3:1 UI) and refuses to emit failing CSS; a unit test re-checks the shipped bundle. Per-page presets inherit down the rootline via TypoScript levelfield slide. Dark mode, five icon libraries, and density/radius/font settings are runtime switches.',
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
            'content' => [
                self::block('desiderio_headerpage', [
                    'eyebrow' => 'Target group 1 — agencies & integrators',
                    'header' => 'Win the pitch. Keep the margin.',
                    'subheadline' => 'Every fixed-price TYPO3 project has the same enemy: template hours. Desiderio deletes them.',
                    'variant' => 'center',
                    'button_text' => 'See Agency pricing',
                    'button_link' => self::REPO_URL,
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'Demo three designs in the kickoff meeting',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Your new sales superpower',
                    'content' => 'Switch theme presets live while the client watches. Lagoon for the calm corporate look, Midnight for the product launch, their own shadcn/create design for the brand pitch — same content, three designs, zero preparation. Then set a different preset per page subtree and run every sub-brand from one install. Multi-brand used to be a budget line; now it is a dropdown.',
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
            'content' => [
                self::block('desiderio_headerpage', [
                    'eyebrow' => 'Target group 2 — in-house teams',
                    'header' => 'Ship the campaign. Skip the ticket queue.',
                    'subheadline' => 'Your developers built the site once. With Desiderio, marketing runs it every day after — on brand, on time, without touching code.',
                    'variant' => 'center',
                    'button_text' => 'See what editors see',
                    'button_link' => '{{page:chapter-hero}}',
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
                        ['icon' => 'rocket', 'title' => 'Campaign pages in hours', 'description' => 'Compose from finished elements with live previews. The dev queue is for features again, not for landing pages.', 'link' => ''],
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
            'content' => [
                self::block('desiderio_headerpage', [
                    'eyebrow' => 'Target group 3 — freelancers & solo devs',
                    'header' => 'Look like a team of ten. Bill like one.',
                    'subheadline' => 'A complete design system, 255 elements, and a CI-grade quality pipeline — for exactly €0. Your one-person studio just got an unfair advantage.',
                    'variant' => 'center',
                    'button_text' => 'Install free today',
                    'button_link' => self::REPO_URL,
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
    private static function screenshot(string $filename, string $title, string $alternative): array
    {
        $folder = str_starts_with($filename, 'frontend-') ? 'Frontend' : 'Backend';

        return [
            'file' => 'Resources/Public/Styleguide/' . $folder . '/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Screenshot of a TYPO3 14 installation with Desiderio.',
            'source' => self::REPO_URL,
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
