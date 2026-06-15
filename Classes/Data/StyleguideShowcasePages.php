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
            self::agencyPage(),
            self::inhousePage(),
            self::freelancerPage(),
            self::geoAiSearchPage(),
            self::successStoriesPage(),
            self::successStoryAnthropicPage(),
            self::successStorySpacexPage(),
            self::successStoryOpenaiPage(),
            self::successStoryNasaPage(),
            self::successStoryNetflixPage(),
            self::successStoryNintendoPage(),
            self::successStoryLegoPage(),
            self::successStoryIkeaPage(),
            self::successStorySpotifyPage(),
            self::successStoryStripePage(),
            self::successStoryDuolingoPage(),
            self::successStoryWikipediaPage(),
            self::successStoryRedBullPage(),
            self::successStoryCernPage(),
            self::successStoryPixarPage(),
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
            'abstract' => 'Fifteen clearly fictional enterprise scenarios that ask one question with a straight face: what would happen if the most ambitious organizations on the planet ran their websites on TYPO3 with Desiderio?',
            'description' => 'What if Anthropic, SpaceX, OpenAI, NASA, or LEGO ran on TYPO3? Fifteen clearly fictional, deliberately fun Desiderio showcase scenarios about multi-brand theming and editor velocity.',
            'parentSlug' => null,
            'subtitle' => 'Clearly fictional. Deliberately fun. None of the companies below use Desiderio (yet) — these invented scenarios show how per-page themes, editor previews, and open source would play out at enterprise scale.',
            'blogList' => true,
            // The seeder prepends a paginated blog_posts list plugin when
            // EXT:blog is installed; the highlight stays below the list.
            'content' => [
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
            'title' => 'What if Anthropic ran on TYPO3?',
            'navTitle' => 'Anthropic (fictional)',
            'slug' => '/success-stories/anthropic',
            'abstract' => 'A clearly fictional showcase scenario: an AI safety lab publishes interpretability papers, model cards, and policy posts at a pace that breaks most CMS workflows. In this invented universe, the Claude makers solve it the boring way — open-source TYPO3, Desiderio elements, and editors who never wait for a deploy.',
            'description' => 'Fictional showcase: Anthropic\'s research blog on TYPO3 — Desiderio editor previews for fast publishing, per-page themes per product line, and self-hosted open source.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 09:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'AI & Research'],
                'tags' => ['Anthropic', 'AI research', 'Editorial workflow', 'Self-hosting'],
            ],
            'content' => [
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
            'title' => 'What if SpaceX ran on TYPO3?',
            'navTitle' => 'SpaceX (fictional)',
            'slug' => '/success-stories/spacex',
            'abstract' => 'A clearly fictional showcase scenario: a company that launches rockets weekly cannot wait for a website rebuild between missions. In this invented universe, every mission gets its own TYPO3 subtree, its own Desiderio preset, and a countdown hero that the comms team configures over coffee.',
            'description' => 'Fictional showcase: SpaceX mission microsites on TYPO3 — every launch a Desiderio-themed page subtree with countdown heroes, status boards, and zero rebuilds.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 10:30:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Aerospace'],
                'tags' => ['SpaceX', 'Mission microsites', 'Per-page themes', 'Countdown hero'],
            ],
            'content' => [
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
            'title' => 'What if OpenAI ran on TYPO3?',
            'navTitle' => 'OpenAI (fictional)',
            'slug' => '/success-stories/openai',
            'abstract' => 'A clearly fictional showcase scenario: when a company ships a new product line every quarter, the website becomes the slowest model in the lineup. In this invented universe, the web team trades rebuilds for re-theming — one content pool, one Desiderio preset per product family, launch pages assembled before the keynote ends.',
            'description' => 'Fictional showcase: OpenAI product launches on TYPO3 — one Desiderio content pool, a theme preset per product family, and pricing pages editors update themselves.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 12:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'AI & Research'],
                'tags' => ['OpenAI', 'Product launches', 'Pricing pages', 'Theme presets'],
            ],
            'content' => [
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
    private static function successStoryNasaPage(): array
    {
        return [
            'title' => 'What if NASA ran on TYPO3?',
            'navTitle' => 'NASA (fictional)',
            'slug' => '/success-stories/nasa',
            'abstract' => 'A clearly fictional showcase scenario: a space agency with six decades of mission pages, press kits, and image archives moves them onto one TYPO3 install — and finally passes its own accessibility mandate.',
            'description' => 'Fictional showcase: NASA mission archives on TYPO3 — accessible Desiderio elements, one subtree per mission, and editors who publish without a launch window.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-03 09:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Aerospace'],
                'tags' => ['NASA', 'Mission archives', 'Accessibility', 'Open data'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Sixty years of missions means sixty years of microsites in this scenario — Apollo in archive beige, Artemis in Midnight. Each program is a page subtree with its own preset; timelines, stats, and galleries are stock Desiderio elements with accessible markup the agency\'s own Section 508 auditors sign off without a meeting. The agency is fictional here; the WCAG 2.2-checked contrast on every preset is not.</p>',
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
                    'author' => 'Fictional Web Program Manager',
                    'role' => 'invented persona — no real NASA statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryNetflixPage(): array
    {
        return [
            'title' => 'What if Netflix ran on TYPO3?',
            'navTitle' => 'Netflix (fictional)',
            'slug' => '/success-stories/netflix',
            'abstract' => 'A clearly fictional showcase scenario: a streaming giant ships a themed landing page for every original series — from one TYPO3 install, with a preset per show and zero rebuilds between premieres.',
            'description' => 'Fictional showcase: Netflix series landing pages on TYPO3 — a Desiderio preset per show, countdown heroes for premieres, and editors who ship between episodes.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-07 10:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Entertainment'],
                'tags' => ['Netflix', 'Landing pages', 'Theme presets', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Every original gets a landing page, and every show has its own mood — in this fiction the dark thriller runs Midnight, the baking show runs Citrus, and both are the same install with a different preset on the subtree. Countdown heroes handle premiere dates, FAQ elements handle spoilers policy, and the imagined marketing team retires its static-site generator. The company is borrowed; the per-subtree theme engine ships today.</p>',
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
                    'author' => 'Fictional Director of Title Marketing',
                    'role' => 'invented persona — no real Netflix statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryNintendoPage(): array
    {
        return [
            'title' => 'What if Nintendo ran on TYPO3?',
            'navTitle' => 'Nintendo (fictional)',
            'slug' => '/success-stories/nintendo',
            'abstract' => 'A clearly fictional showcase scenario: a games company where every franchise is its own visual world keeps Mario, Zelda, and the hardware store on one TYPO3 install — one preset per universe.',
            'description' => 'Fictional showcase: Nintendo franchise pages on TYPO3 — a Desiderio brand world per franchise, launch pages from stock elements, and editors who ship at console speed.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-11 09:30:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Entertainment'],
                'tags' => ['Nintendo', 'Product launches', 'Brand worlds', 'Per-page themes'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A plumber, a princess, and a console launch share nothing visually — except, in this fiction, a TYPO3 install. Each franchise subtree carries its own preset: warm reds for the platformer, sage greens for the adventure, clean neutrals for hardware. Launch pages assemble from countdown heroes, feature grids, and pricing tables while the imagined legal team admires the GPL license. The franchises are real, the scenario is not, the elements ship today.</p>',
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
                    'author' => 'Fictional Head of Web Worlds',
                    'role' => 'invented persona — no real Nintendo statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryLegoPage(): array
    {
        return [
            'title' => 'What if LEGO ran on TYPO3?',
            'navTitle' => 'LEGO (fictional)',
            'slug' => '/success-stories/lego',
            'abstract' => 'A clearly fictional showcase scenario: a brick company that launches themed sets weekly builds its campaign pages the same way it builds everything else — from interchangeable, well-documented parts.',
            'description' => 'Fictional showcase: LEGO campaign pages on TYPO3 — Desiderio elements as bricks, a preset per product line, and campaign pages clicked together in an afternoon.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-15 11:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Commerce'],
                'tags' => ['LEGO', 'Campaign pages', 'Brand worlds', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company that turned interlocking parts into an empire would recognize this scenario instantly: 255 content elements as bricks, typed component contracts as the studs that only fit one way, and a campaign page clicked together in an afternoon. Space sets run Midnight, botanical sets run Forest, and the imagined brand team stops briefing agencies for every seasonal push. The bricks are real and GPL-licensed; the company is on loan for the joke.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '255', 'label' => 'Bricks in the box', 'description_text' => 'Desiderio\'s element library, reused across every imagined campaign.'],
                        ['value' => '1', 'label' => 'Afternoon per campaign page', 'description_text' => 'Hero, story sections, product grid — assembled, previewed, published.'],
                        ['value' => '18', 'label' => 'Product-line worlds', 'description_text' => 'Each line carries its own preset in this fictional setup.'],
                        ['value' => '0', 'label' => 'Instructions misread', 'description_text' => 'Typed f:argument contracts fail loudly when a brick is used wrong.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our motto is \'only the best is good enough\', and our web team\'s motto was \'the rebuild ships next quarter\'. Only one of those survived the migration.',
                    'author' => 'Fictional Digital Campaigns Lead',
                    'role' => 'invented persona — no real LEGO statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryIkeaPage(): array
    {
        return [
            'title' => 'What if IKEA ran on TYPO3?',
            'navTitle' => 'IKEA (fictional)',
            'slug' => '/success-stories/ikea',
            'abstract' => 'A clearly fictional showcase scenario: a furniture giant publishing in dozens of languages flat-packs its catalog pages — same parts everywhere, assembled locally, no agency hotline required.',
            'description' => 'Fictional showcase: IKEA catalog pages on TYPO3 — multilanguage Desiderio elements, ICU plurals that survive every locale, and country teams who assemble pages themselves.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-19 10:30:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Commerce'],
                'tags' => ['IKEA', 'Multilanguage', 'Catalog pages', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Flat-pack logic applied to the web: in this fiction every country site receives the same well-labeled parts — heroes, product grids, FAQ elements — and assembles them in its own language. XLIFF catalogues and ICU MessageFormat keep plurals and dates correct from Sweden to Japan, while the imagined global team ships one update instead of forty. The meatballs are not included; the translation architecture ships in the free package.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '42', 'label' => 'Country sites, one toolkit', 'description_text' => 'Same elements, locally assembled, in this fictional rollout.'],
                        ['value' => '100%', 'label' => 'Strings through XLIFF', 'description_text' => 'Screen-reader labels included — no hardcoded copy anywhere.'],
                        ['value' => '1', 'label' => 'Update for all locales', 'description_text' => 'Element fixes ship once and every market inherits them.'],
                        ['value' => '0', 'label' => 'Allen keys required', 'description_text' => 'Backend previews replace the instruction sheet.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our products ship as parts and instructions. Our website finally does too — and nobody has called the assembly hotline since.',
                    'author' => 'Fictional Global Web Coordinator',
                    'role' => 'invented persona — no real IKEA statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStorySpotifyPage(): array
    {
        return [
            'title' => 'What if Spotify ran on TYPO3?',
            'navTitle' => 'Spotify (fictional)',
            'slug' => '/success-stories/spotify',
            'abstract' => 'A clearly fictional showcase scenario: a streaming service whose editorial team publishes artist features and year-in-review pages at playlist speed — dark mode first, obviously.',
            'description' => 'Fictional showcase: Spotify editorial pages on TYPO3 — dark-mode-first Desiderio presets, artist features from stock elements, and a wrapped campaign without a single rebuild.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-23 09:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Entertainment'],
                'tags' => ['Spotify', 'Editorial workflow', 'Dark mode', 'Theme presets'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>An editorial team that ships artist features daily cannot file a ticket per page. In this scenario the music editors compose features from quote blocks, stat boards, and gallery elements — previewed in the backend, published before the song ends. The year-end campaign is a preset switch plus seeded pages, not a three-month engineering project. Dark mode is not an afterthought here; every preset ships both modes with checked contrast.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '365', 'label' => 'Editorial pages a year', 'description_text' => 'One artist feature per day in this fictional newsroom.'],
                        ['value' => '2', 'label' => 'Modes, both first-class', 'description_text' => 'Dark mode users get checked contrast, not an inverted afterthought.'],
                        ['value' => '45 min', 'label' => 'Brief to published feature', 'description_text' => 'Quote, stats, gallery, embed — stock elements all the way down.'],
                        ['value' => '1', 'label' => 'Preset switch for year-end', 'description_text' => 'The imagined wrapped campaign is a dropdown, not a deploy.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our app went dark-mode-first years ago; our CMS pages finally followed. The contrast checker has better ears than our mastering engineers.',
                    'author' => 'Fictional Editorial Platform Lead',
                    'role' => 'invented persona — no real Spotify statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryStripePage(): array
    {
        return [
            'title' => 'What if Stripe ran on TYPO3?',
            'navTitle' => 'Stripe (fictional)',
            'slug' => '/success-stories/stripe',
            'abstract' => 'A clearly fictional showcase scenario: a payments company famous for its documentation discovers that marketing pages can be engineered with the same rigor — typed components, audited templates, zero drift.',
            'description' => 'Fictional showcase: Stripe marketing pages on TYPO3 — typed Fluid components, pricing tables editors update at announcement speed, and a template audit at zero findings.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-27 11:30:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Commerce'],
                'tags' => ['Stripe', 'Documentation', 'Pricing pages', 'Self-hosting'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company that treats documentation as a product would audit its marketing stack, and in this fiction the audit finds: typed f:argument contracts on every component, an 11-category template check at zero findings, and PHPStan at level max on the PHP underneath. Pricing changes land as editor edits, not deploys — reviewed in a backend preview instead of a pull request. The rigor is real and verifiable in the repository; the customer is invented.</p>',
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
                        ['value' => '100%', 'label' => 'Infrastructure in-house', 'description_text' => 'Self-hosted open source clears the fictional compliance review.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We measure API reliability in nines. Our marketing site used to be measured in apologies — the typed components fixed the gap.',
                    'author' => 'Fictional Head of Web Infrastructure',
                    'role' => 'invented persona — no real Stripe statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryDuolingoPage(): array
    {
        return [
            'title' => 'What if Duolingo ran on TYPO3?',
            'navTitle' => 'Duolingo (fictional)',
            'slug' => '/success-stories/duolingo',
            'abstract' => 'A clearly fictional showcase scenario: a language-learning app that teaches forty languages finally gets a website that speaks all of them — with plurals, dates, and screen-reader labels done right.',
            'description' => 'Fictional showcase: Duolingo course pages on TYPO3 — ICU MessageFormat for every locale, streak-counter stats from stock elements, and an owl-approved publishing pace.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-01 09:30:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Education'],
                'tags' => ['Duolingo', 'Multilanguage', 'Gamification', 'Editor velocity'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Teaching Japanese through Spanish breaks most translation layers, and in this fiction the web team finally stops apologizing for theirs: XLIFF 2.0 catalogues with ICU MessageFormat handle plural rules from Polish to Arabic, and every course page assembles from the same stat boards and timeline elements. The streak counter on the homepage is a stock metric element; the imagined owl is pleased with the daily publishing streak. The localization architecture is the real product here.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '40+', 'label' => 'Course locales served', 'description_text' => 'Each with correct plurals and date formats in this fictional rollout.'],
                        ['value' => '100%', 'label' => 'ARIA labels translated', 'description_text' => 'Screen-reader strings ship through the same XLIFF pipeline.'],
                        ['value' => '365', 'label' => 'Day publishing streak', 'description_text' => 'The imagined content team never misses — the owl is watching.'],
                        ['value' => '0', 'label' => 'Hardcoded strings found', 'description_text' => 'Every label runs through f:translate, even the celebratory ones.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We gamified language learning. The CMS gamified itself — the team genuinely competes for the cleanest backend preview.',
                    'author' => 'Fictional Web Localization Lead',
                    'role' => 'invented persona — no real Duolingo statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryWikipediaPage(): array
    {
        return [
            'title' => 'What if Wikipedia ran on TYPO3?',
            'navTitle' => 'Wikipedia (fictional)',
            'slug' => '/success-stories/wikipedia',
            'abstract' => 'A clearly fictional showcase scenario: the free encyclopedia gives its campaign and fundraising pages the same treatment as its articles — open source, accessible, and owned by nobody\'s vendor.',
            'description' => 'Fictional showcase: Wikipedia campaign pages on TYPO3 — GPL design system on GPL CMS, donation banners editors test themselves, and accessibility as policy, not promise.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-05 10:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Education'],
                'tags' => ['Wikipedia', 'Open source', 'Accessibility', 'Self-hosting'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>An organization that runs on volunteers and GPL licenses would never accept a proprietary design system for its fundraising pages, and in this fiction it does not have to: a GPL component library on a GPL CMS, self-hosted on the foundation\'s own metal. Donation appeals assemble from banner and stat elements with contrast the accessibility policy can cite, and every template is inspectable by the same community that edits the articles. The alignment of licenses is the entire joke — and entirely real.</p>',
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
                        ['value' => '0', 'label' => 'Vendors in the stack', 'description_text' => 'Self-hosted everything — the fictional procurement page stays a stub.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Citation needed? The contrast solver ships its own proof. First design system our reviewers accepted without a talk page argument.',
                    'author' => 'Fictional Movement Web Lead',
                    'role' => 'invented persona — no real Wikipedia statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryRedBullPage(): array
    {
        return [
            'title' => 'What if Red Bull ran on TYPO3?',
            'navTitle' => 'Red Bull (fictional)',
            'slug' => '/success-stories/red-bull',
            'abstract' => 'A clearly fictional showcase scenario: an energy-drink empire that is secretly a media company spins up an event microsite per cliff dive, air race, and festival — caffeinated, themed, and on time.',
            'description' => 'Fictional showcase: Red Bull event microsites on TYPO3 — a Desiderio subtree per event, countdown heroes for every start gate, and campaign pages with wings.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-09 11:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Sports'],
                'tags' => ['Red Bull', 'Event microsites', 'Campaign pages', 'Countdown hero'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company that runs more events than some federations needs a microsite assembly line, and in this fiction it gets one: every cliff dive, air race, and music festival is a page subtree with its own preset — Ember for the desert rally, Marine for the regatta. Countdown heroes tick toward start gates, stat boards track qualifying, and the imagined events team launches a site between espresso shots. The wings are marketing; the runtime theme switching is shipping code.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '30+', 'label' => 'Event microsites a season', 'description_text' => 'One subtree per event in this fictional calendar, all one install.'],
                        ['value' => '90 min', 'label' => 'Announcement to live site', 'description_text' => 'Countdown hero, schedule timeline, ticket CTA — stock parts.'],
                        ['value' => '0', 'label' => 'Energy drinks required', 'description_text' => 'Editors publish calmly; the adrenaline stays in the footage.'],
                        ['value' => '14', 'label' => 'Presets in rotation', 'description_text' => 'Every event genre gets a matching visual world.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We give athletes wings and gave our web team three CMSes. Now it\'s one install per season and the only thing still freefalling is the cliff diver.',
                    'author' => 'Fictional Head of Event Digital',
                    'role' => 'invented persona — no real Red Bull statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryCernPage(): array
    {
        return [
            'title' => 'What if CERN ran on TYPO3?',
            'navTitle' => 'CERN (fictional)',
            'slug' => '/success-stories/cern',
            'abstract' => 'A clearly fictional showcase scenario: the birthplace of the web upgrades its experiment pages — open-source elements, accessible data tables, and physics results published faster than peer review.',
            'description' => 'Fictional showcase: CERN experiment pages on TYPO3 — accessible chart elements for collision data, a preset per experiment, and the web back where it was invented.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-13 09:00:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Science'],
                'tags' => ['CERN', 'Research publishing', 'Open data', 'Accessibility'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>The laboratory that invented the web deserves better than PDF press releases, and in this fiction it publishes like it researches: every experiment is a subtree with its own preset, results land as accessible chart and table elements with real data markup, and outreach pages assemble from timelines and stat boards. Server-rendered pages with no hydration cost feel appropriately fundamental. The physics is real, the scenario is invented, and the web returns home either way.</p>',
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
                    'author' => 'Fictional Outreach Platform Physicist',
                    'role' => 'invented persona — no real CERN statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function successStoryPixarPage(): array
    {
        return [
            'title' => 'What if Pixar ran on TYPO3?',
            'navTitle' => 'Pixar (fictional)',
            'slug' => '/success-stories/pixar',
            'abstract' => 'A clearly fictional showcase scenario: a story-first animation studio gives every film its own web world — lamp included — without rendering a single page rebuild.',
            'description' => 'Fictional showcase: Pixar film pages on TYPO3 — a Desiderio brand world per film, story-driven scroll pages from stock elements, and dark mode for the screening room.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-17 10:30:00',
                'categories' => ['Success stories', 'Fictional scenarios', 'Entertainment'],
                'tags' => ['Pixar', 'Story pages', 'Brand worlds', 'Dark mode'],
            ],
            'content' => [
                self::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A studio that storyboards everything would storyboard its film pages too, and in this fiction the boards map straight to elements: an article hero for the opening shot, alternating textmedia scenes, a stat board for the box office, a quote from the imagined director. Each film subtree carries its own preset — ocean blues, desert ambers, monster pastels — switched at runtime like a scene cut. The lamp hops in real life; the per-page theme engine ships in the package.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                self::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '28', 'label' => 'Film worlds, one install', 'description_text' => 'Every feature keeps its own palette in this fictional archive.'],
                        ['value' => '1', 'label' => 'Storyboard per page', 'description_text' => 'Scenes map one-to-one onto stock content elements.'],
                        ['value' => '2', 'label' => 'Modes for the screening room', 'description_text' => 'Dark mode that respects the colorists, light mode for the lobby.'],
                        ['value' => '0', 'label' => 'Renders re-queued', 'description_text' => 'Pages are server-rendered Fluid — the render farm stays on the movie.'],
                    ],
                ]),
                self::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Story is king here, and our old website was a subplot nobody followed. Now every film page reads like a storyboard — and ships before the trailer drops.',
                    'author' => 'Fictional Studio Web Producer',
                    'role' => 'invented persona — no real Pixar statement',
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
                'description' => 'All fictional Desiderio success stories in a category.',
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
                'description' => 'All fictional Desiderio success stories carrying a tag.',
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
                    'subheadline' => '255 content elements were only the start. Around them sits an ecosystem of TYPO3 14 extensions — backend record views, one-click workspaces, a blog, enterprise search, enterprise SSO, accessible forms, stablecoin paywalls, and an agent-ready API layer. Same theme. Same security model. Same core concepts. Pick a card to go deeper.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'One stack, not thirteen plugins',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Every extension below is built to TYPO3 14.3 LTS, PHPStan level max, and the same workspace rules.',
                    'content' => 'Most TYPO3 sites end up as a pile of plugins that each invent their own data model, their own styling, and their own idea of security. The Desiderio ecosystem takes the opposite bet. Content elements are Content Blocks. Blog posts are pages. Records are records. Drafts are workspaces. Search results, forms, and login screens inherit the active shadcn theme preset instead of shipping their own CSS. And where the stack talks to AI agents or external APIs, it does so through declared, policy-controlled capabilities — never a blank-cheque connection to live data. The result is a system where one theme switch restyles everything, one workspace publish ships everything, and one mental model carries across the whole site. The cards below link to a dedicated page for each extension, with exactly what it does and how it fits.',
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
                        ['icon' => 'sparkles', 'title' => 'Desiderio + Innesto', 'description' => 'The design system itself: 255 content elements from 49 typed Fluid 5 components, runtime theming from site settings, and Innesto to graft shadcn registry components as new elements.', 'link' => '{{page:features/desiderio}}'],
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
                        ['title' => 'Built on core concepts, not a proprietary layer', 'content' => 'Every extension in the ecosystem leans on TYPO3 primitives instead of inventing its own. Blog posts are pages with a dedicated doktype and ordinary content elements; Desiderio\'s 255 elements are TYPO3 Content Blocks with real database columns and backend previews; Record Lists reshapes the existing Records module rather than replacing it. The payoff is that any skill you already have in TYPO3 transfers directly, and there is no parallel data model to migrate, back up, or untangle later.', 'open_by_default' => 1],
                        ['title' => 'One theme switch restyles the whole site', 'content' => 'Desiderio\'s theming layer is a set of OKLCH design tokens driven by TYPO3 site settings, switchable per site and per page subtree without a rebuild. Crucially, the surrounding extensions opt into it instead of shipping their own look: Solr search results, facets and pagination, Powermail\'s multi-step forms, and the blog templates all render through the same shadcn component presets. Change the preset once — including light and dark mode — and search, forms, and articles change with it.', 'open_by_default' => 0],
                        ['title' => 'Workspace-safe from top to bottom', 'content' => 'Drafting and publishing use TYPO3 Workspaces consistently across the stack rather than ad-hoc "draft" flags. Record Lists overlays every fetched row with the active workspace version and colour-codes new, changed, moved, and deleted records; Easy Workspace publishes a page\'s pending changes together with their related records in one click; the blog stages posts, tags, and authors; and the MCP and API layers stage agent writes into a workspace by default, keeping live UIDs stable so live content is never edited blind.', 'open_by_default' => 0],
                        ['title' => 'Agent-ready, but on a policy leash', 'content' => 'The ecosystem is designed for AI agents without handing them the keys. The MCP Server, sg_apicore, and the Capability Bridge expose content and workflows as structured, machine-readable tools — yet each one declares the subsystems it is allowed to touch in a capability manifest, defaults outbound network access to the site itself, and routes writes through workspaces. Agentation and Skillflow add the human-in-the-loop side: visual annotations and SKILL.md review steps that produce suggestions, never silent, auto-applied changes.', 'open_by_default' => 0],
                        ['title' => 'Held to one engineering standard', 'content' => 'This is one stack with one quality bar, not a bag of unrelated plugins. The webconsulting-built extensions target TYPO3 14.3 LTS on PHP 8.3 and up, run PHPStan at level max, ship English and German XLIFF translations, and carry their own unit and functional test suites. Security is treated as a feature rather than an afterthought — parameterized QueryBuilder queries, CSRF-protected backend actions, redacted secrets, and execution gates that keep risky operations on local development environments only.', 'open_by_default' => 0],
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
                    'content' => 'Records List Types adds four built-in view modes (Grid, Compact, Teaser, and a generic base for custom types) to TYPO3\'s backend Records module, letting editors choose how they browse the records they manage. Grid view displays cards with thumbnails and field values in a responsive layout; Compact view is a dense, horizontally-scrolling table for high-volume record sets; Teaser view shows news-style cards with title, date, and excerpt; and the base GenericView lets you register additional custom views purely through TSconfig and Fluid templates. Every view supports configurable filters (by title, hidden status, date ranges, categories, and select fields), drag-and-drop reordering on sortable tables, and workspace-aware overlays so edits staged in workspaces stay searchable before publication. All views honor dark mode, support accessible keyboard navigation, and persist user preferences per backend user.',
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
                        ['title' => 'Grid View — visual browsing with thumbnails and field cards', 'content' => 'Grid View transforms record browsing into a visual card-based layout where each record appears as a design-system card with an optional thumbnail image (resolved from FAL fields you configure per table), a title bar with drag handle and action menu, field values displayed in type-aware format (booleans as badges, dates in monospace, text with ellipsis truncation), and a footer showing UID, PID, and language flag. The grid is responsive, scaling from one column on mobile to multiple columns on wide screens, and supports keyboard and mouse drag-and-drop reordering for any table with a TCA sortby field. Hidden records render with muted headers; workspace records show color-coded indicators for new (blue), modified (purple), moved (cyan), or deleted (red) states. All field display is type-aware: rich text and multi-line text span full card width, while short boolean and select fields sit side-by-side to keep cards compact.', 'open_by_default' => 1],
                        ['title' => 'Compact View — dense single-line tables with sticky columns and horizontal scroll', 'content' => 'Compact View packs records into a dense, responsive table layout designed for efficient scanning of high-volume record sets. Icon, UID, and title columns are pinned on the left; visibility toggle, edit, and delete actions are pinned on the right; additional configured fields scroll horizontally between the fixed columns with visual scroll-shadow indicators to show when content extends beyond the viewport. Column headers are sortable: click to sort ascending or descending via the native TYPO3 dropdown API. Rows alternate with zebra striping for readability, and hidden records display with muted background and dimmed title text. This view is ideal for managing dozens to hundreds of records at once without filtering — the sticky columns keep context while you scroll.', 'open_by_default' => 0],
                        ['title' => 'Teaser View — news-style cards with title, date, and excerpt', 'content' => 'Teaser View renders records as minimal cards modeled after news and blog listing styles, perfect for editorial content like tx_news articles or custom news tables. Each card shows the record title, a date field with calendar icon (configurable per table), a two-line-clamp description or excerpt field (also configurable), a UID status pill, and a hidden/visible indicator. Action buttons for visibility toggle, edit, and delete sit at the bottom. Color and styling adapt to light and dark themes via CSS light-dark() functions. This view concentrates on content substance — editors see headlines and lead paragraphs at a glance, which speeds scanning large editorial queues.', 'open_by_default' => 0],
                        ['title' => 'Custom view types — register layouts in TSconfig + Fluid, no PHP required', 'content' => 'Beyond the four built-in views, you can register custom view types with zero PHP using only TSconfig and an optional Fluid template. In page.tsconfig, define a view type with a label, icon, template name (reuse CompactView, TeaserView, or GridView for instant custom variants, or name your own Fluid file), template root path, optional CSS file, display columns, and items-per-page. You can also restrict views to specific pages using TSconfig conditions, e.g., a Timeline view only on the Events page, an Address Book only on the Staff page tree. The companion Records List Examples extension ships six ready-to-use custom views (Timeline, Catalog, Address Book, Event List, Gallery, Dashboard) as real-world templates; Timeline and Catalog have custom Fluid and CSS, while the others reuse built-in templates with different column configs. All custom views inherit the same sorting, pagination, multi-record-selection, and action behavior as the built-in types.', 'open_by_default' => 0],
                        ['title' => 'Record filters — configurable per table, workspace-aware, persisted to user preferences', 'content' => 'Record filters bring field-level filtering to the Records module in all view modes. Editors access filters via View > Show filters after selecting a table; the panel displays toggle-able filters (text search, hidden yes/no, date range, category selection, any configured select field) and persists visibility state in the user\'s module data, just like the Core search toggle. In LIVE and in the classic List View, filters apply directly in the query layer. In workspace mode and in alternative view modes (Grid, Compact, Teaser, custom), the extension fetches candidate rows, applies BackendUtility::workspaceOL() overlay, and then evaluates filters and search against the effective workspace row — so workspace-only changes to visibility, dates, select values, and category assignments remain searchable and filterable before they are published. TSconfig defines which filters appear per table: use built-in aliases (title, hidden, date, categories) for zero-config defaults, or add custom filters with field mapping and label overrides.', 'open_by_default' => 0],
                        ['title' => 'Drag-and-drop reordering with full keyboard support and ARIA announcements', 'content' => 'Any table with a TCA sortby field supports manual drag-and-drop reordering. Mouse users grab the drag handle with a left-click and drag; keyboard users press Space or Enter to grab, arrow keys to move (up/down or left/right in grid), Space or Enter to drop, and Escape to cancel. All actions announce their state via ARIA live regions — screen readers hear position and drop-confirmation updates — and the interface maintains proper focus throughout. The drag handle is marked role=button with an aria-grabbed state, cards carry role=option within a role=listbox parent, and instructions are exposed semantically for screen readers. The sorting mode toggle lets editors switch between manual (drag) and field-based sorting; when in field-sorting mode, a dropdown lets them pick which column to sort by.', 'open_by_default' => 0],
                        ['title' => 'Workspace support with color-coded state indicators and post-overlay searching', 'content' => 'The extension is workspace-aware on TYPO3 v14: queries use WorkspaceRestriction keyed to the current workspace, every fetched row is overlaid with BackendUtility::workspaceOL() before enrichment, and search and filters evaluate after the workspace overlay so live rows can be replaced by draft rows before filtering. Workspace state (t3ver_state) maps to visual indicators: new records show a blue header with left border, modified records show purple, moved records show cyan, and deleted records show red with a strikethrough title. This means when an editor stages a record change in a workspace, the change is immediately visible and searchable in alternative view modes — the editor can preview what they are publishing before they go live.', 'open_by_default' => 0],
                        ['title' => 'Type-aware field display with configurable FAL thumbnails, language flags, and preview hints', 'content' => 'Every field type displays intelligently: boolean fields render as badges (Yes/No), dates render in monospace for easy scanning, relations show count indicators, select fields display their label, links become clickable, and text fields truncate with ellipsis. In Grid View, you configure which fields appear per table via TSconfig (titleField, descriptionField, imageField, preview flag); the extension resolves FAL references and generates thumbnail URLs automatically. Language flags appear in the card footer, and a subtle preview hint below thumbnails reminds editors that images configured in the backend may not appear on the frontend for certain record types — preventing surprise when an image field does not render in the frontend template. All display decisions are table-specific, so a Products table shows product images and price fields, a Staff table shows portrait photos and job titles, and a News table shows feature images and article dates.', 'open_by_default' => 0],
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
                    'content' => 'The Model Context Protocol is a wire protocol that lets AI assistants (like Claude inside Cursor or Claude Desktop) call structured tools on your server. This extension ships dozens of TYPO3-native tools: read page trees, search records, attach images, translate content, browse files, audit for missing metadata, create sites, and publish workspaces. Every write stages in a TYPO3 workspace first—live data stays safe. Calls come over OAuth 2.1 for remote clients or stdio for trusted development environments. The same tools work from the TYPO3 CLI, so shell scripts, GitHub Actions, and your own automation can use the exact same interface.',
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
                        ['title' => 'A full toolbox of MCP tools across nine groups', 'content' => 'The extension ships tools for navigation and schema inspection (GetCapabilities, ListTables, GetTableSchema, GetFlexFormSchema), page and record reads with TCA context, structured writes and bulk edits, workspace review and publishing, sandboxed file handling, content audit, system diagnostics, site and extension administration, and development-only helpers on DDEV. Every tool is available both as an MCP endpoint (for any compliant client) and as a TYPO3 CLI shortcut (vendor/bin/typo3 mcp:command). Tool names follow PascalCase and resemble what TYPO3 editors already know from the backend UI.', 'open_by_default' => 1],
                        ['title' => 'Workspace transparency and editorial safety', 'content' => 'Record writes go into a TYPO3 workspace by default—the extension picks an existing draft or creates an MCP workspace automatically. MCP clients see the stable live UID, not the internal workspace version ID, so from the AI\'s perspective, workspaces are invisible. Publishing and rollback operations default to dry-run mode, so the AI shows you what would happen before it happens. In local development (DDEV or Development context), you can relax to live edits with an explicit setting, but production stays strict: live workspace access requires both an explicit workspace_id parameter and admin permission.', 'open_by_default' => 0],
                        ['title' => 'OAuth 2.1 + PKCE for remote clients, stdio for local', 'content' => 'Remote MCP clients authenticate via OAuth 2.1 with PKCE at the /mcp endpoint. The first request triggers a login with your existing TYPO3 backend credentials, and the resulting token is stored on the client side. The extension discovers the OAuth server and protected resources automatically. For local development, a client like Cursor can run vendor/bin/typo3 mcp:server as a trusted subprocess without OAuth—TYPO3 gates the actual operations, and the host OS boundary is the only network boundary. A backend User > MCP Server module shows your MCP endpoint URL, one-click Cursor setup, copy-paste config for Claude Desktop, health checks, and token management.', 'open_by_default' => 0],
                        ['title' => 'Capability manifest: declare what your MCP server can do', 'content' => 'A YAML file (Configuration/Capabilities.yaml) declares which subsystems each tool needs: database:read, database:write, file:write, render:frontend, workspace:write, and others. Remove database:write and every tool that writes to the database stops working immediately. Outbound HTTP defaults to self-hosted sites only; uncomment a line to allow public web downloads. This is not a security boundary—TYPO3 permissions still apply—but it gives operations teams a simple, declarative way to harden the surface without changing code. Check what\'s live right now with vendor/bin/typo3 mcp:get-capabilities --json.', 'open_by_default' => 0],
                        ['title' => 'Complete CLI mirror: every tool from the shell', 'content' => 'Every MCP tool is also available as a TYPO3 console command. vendor/bin/typo3 mcp:read-table, vendor/bin/typo3 mcp:write-table, vendor/bin/typo3 mcp:search, vendor/bin/typo3 mcp:list-workspaces—every shipped tool has a shortcut. Need something more generic? Use vendor/bin/typo3 mcp:tool <ToolName> with --param and --params flags. Output comes in three modes: pretty (humans), plain text (logs), and JSON (jq, agents, CI). JSON output includes an ok/error envelope so scripts know whether to continue. Parameter files like --param data=@payload.json stay constrained to your project root for safety.', 'open_by_default' => 0],
                        ['title' => 'DDEV local-mode relaxations for faster feedback loops', 'content' => 'On DDEV or in a Development application context, the extension relaxes three safety nets: record writes default to the live workspace (instead of requiring an explicit draft workspace_id), file operations accept any storage and path (not just fileadmin/mcp), and outbound HTTP is fully allowed (so UploadFileFromUrl and RenderRecord work on staging and local hosts). A LocalModeService detects DDEV via environment variables and handles auto, on, and off modes. Per-user TSconfig lets integrators opt individual developers in or out. Production stays strict by default. Force strict mode even in DDEV with the mcpServer.strictSandbox feature flag.', 'open_by_default' => 0],
                        ['title' => 'Content audit, preview rendering, and import tooling', 'content' => 'ContentAudit checks all records of a table for missing metadata (alt text on images, descriptions, hidden slugs) and returns a severity-sorted report. GetPreviewUrl generates a signed link to a workspace preview without leaving the chat. RenderRecord fetches the actual rendered HTML so the AI can see what the content looks like in the frontend before publishing. ImportContent and ImportFromUrl accept text, Markdown, or HTML and propose or create content elements. These tools close the feedback loop: the AI can edit, check what it built, and iterate—all without the human having to manually refresh the backend.', 'open_by_default' => 0],
                        ['title' => 'File sandbox and secure uploads', 'content' => 'File tools (BrowseFiles, WriteFile, UploadFile, SearchFile) are restricted to fileadmin/mcp/ by default, keeping MCP uploads separate from sensitive document collections. Path-traversal protection stays on even in local mode. UploadFileFromUrl validates the remote host against your outbound policy and checks for SSRF—on production, it only talks to your own domain unless you explicitly allow it. In DDEV, you can relax to any host (staging, test servers) for easier workflows. FAL-aware tools understand media, storages, and file references, so attaching an image to a record actually creates a sys_file_reference, not a broken hardlink.', 'open_by_default' => 0],
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
                    'content' => 'When a content editor finishes building a page, they need to see exactly what they changed before hitting publish. They need to check that all the content elements they added are ready, that inline children publish with their parents, and that nothing got accidentally left in draft. Easy Workspace puts that review step in their face in the top-right corner—no hunting through nested modules, no guessing which changes will go live together.',
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
                        ['title' => 'Paper-plane toolbar dropdown with live change count', 'content' => 'A custom item in the TYPO3 backend top bar shows a paper-plane icon that lights up with an orange dot the moment pending workspace changes exist on the current page or article. Clicking it opens a Lit-rendered dropdown—light DOM, no shadow-root complications—that fetches the complete list of pending records over AJAX. A lightweight polling endpoint refreshes the count, so editors always know at a glance whether they have unpublished changes waiting. The toolbar hides itself entirely in the Live workspace.', 'open_by_default' => 1],
                        ['title' => 'Pending items review table with checkboxes and type labels', 'content' => 'The dropdown and the full module display all changed records in a dense table: the page row, content elements, and inline children like text & media or accordion items. Each row shows a type label generated from TCA rather than hand-coded, a changed-versus-live workspace state badge, a title, and a checkbox. All changed rows are selected by default because most editors want to publish everything they just built—but any row can be deselected if a change was exploratory and not ready.', 'open_by_default' => 0],
                        ['title' => 'Bulk publish with parent-before-child ordering', 'content' => 'When an editor presses Publish to live, Easy Workspace sends the selected records to TYPO3\'s DataHandler as a deliberately ordered cmdmap: parents (pages and top-level content elements) publish first, then their inline children, following a fixed table-priority policy. This order prevents versioning conflicts and keeps foreign keys pointing at live records rather than workspace placeholders. Each request is processed inside the active workspace and capped server-side to stay predictable on large pages.', 'open_by_default' => 0],
                        ['title' => 'Per-row discard without touching the rest', 'content' => 'If a single content element is not ready, the editor can discard just that row and leave every other change staged. The module exposes a per-row discard action that runs TYPO3 v14\'s native discard command, removing that workspace version without affecting its siblings. There is no all-or-nothing decision—each record can be published or discarded independently.', 'open_by_default' => 0],
                        ['title' => 'Field-level diff modal with history timeline and rollback', 'content' => 'Clicking a row opens a modal that shows exactly what changed in each field, old value beside new value, with inline diffs for longer text. Below the diff sits a timeline of edits to that record, read from TYPO3\'s sys_history via the core RecordHistory service. The editor can roll back a single field or the whole record to an earlier state through TYPO3\'s RecordHistoryRollback, without disturbing the rest of the page.', 'open_by_default' => 0],
                        ['title' => 'Eye icon to locate and highlight a record in preview', 'content' => 'Each row carries a small eye icon that finds that record in the frontend preview. Clicking it scrolls the preview to the matching content element by its #c{uid} anchor and draws a temporary outline around it, so the editor can see how the change renders without leaving the backend. It targets the Visual Editor iframe when that extension is present and falls back to the standard Viewpage preview when it is not; child rows resolve to their parent content element so the highlight always lands on something visible.', 'open_by_default' => 0],
                        ['title' => 'Workspace chip and a module with three focused subviews', 'content' => 'A chip in the header shows which workspace is active, so an editor never silently slips back to Live and wonders where their changes went. The Easy Workspace module lives under Content, directly below the standard TYPO3 Workspaces publish module. It offers three subviews: Open items (the pending publish queue), All records (a read-only inventory of every scoped record on the page in the current workspace), and Checks and diagnostics (a workspace integrity scan plus a manual risk list).', 'open_by_default' => 0],
                        ['title' => 'News article scope with a per-article publish queue', 'content' => 'When the editor is viewing or editing a georgringer/news article, Easy Workspace detects the context and narrows the publish scope to that article plus its related content elements via tx_news_related_news. The toolbar then shows only the article\'s pending changes instead of the whole page tree. The module and AJAX routes also accept an explicit newsUid parameter, so an editor can jump straight to a specific article\'s queue without locating it in the page tree first.', 'open_by_default' => 0],
                        ['title' => 'Database diagnostics with grouped health checks and a manual risk list', 'content' => 'The Checks and diagnostics subview runs a workspace integrity scan that looks for live rows carrying stale version fields, unsupported version states, orphan workspace versions whose live parent is gone, duplicate workspace versions, inline children whose parent is missing, and file references without an owner. Results group into Reports-style health checks with pass, warning, and error states. A separate manual risk list flags edge cases the scanner cannot judge automatically—such as overwritten FAL files or editorial-intent conflicts—so the editor can investigate them before publishing.', 'open_by_default' => 0],
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
                    'content' => 'The TYPO3 Blog Extension is built on one principle: if you know TYPO3, you already know how to run a blog. There are no proprietary post editors, no custom record tables, no workflow system living outside your page tree. Posts are pages (doktype 137), authors are records, tags and categories are TYPO3 core concepts, and every content element and backend layout you already use works inside a blog post. The extension adds backend modules for post overview, comment moderation, and setup, plus 20 Extbase plugins for listing, filtering, archives, sidebars, and RSS feeds. Full TYPO3 Workspaces integration lets you stage posts, tags, and authors before publishing, while comments stay live-editable throughout. Whether you build a standalone blog or layer publishing into an existing site, the Blog Extension keeps you inside the TYPO3 platform you already know.',
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
                        ['title' => 'Posts as pages with a custom doktype', 'content' => 'Blog posts are not records in a proprietary table — they are TYPO3 pages with doktype 137 (blog post) or 138 (blog page). You create them in the page module just like any other page, drag them into the tree, and govern them with the same permissions you already use. Because they are core pages, every content element and backend layout that works elsewhere works inside a blog post. The page header surfaces publish date, tags, categories, and author, so editors find a post\'s metadata without switching modules.', 'open_by_default' => 1],
                        ['title' => 'All your content elements, all your layouts', 'content' => 'Every piece of a blog post is a standard TYPO3 content element. The post header is typically an article hero element from Desiderio or your own theme, while the body mixes text, images, quotes, and any custom element already registered on your site. Backend layouts apply to blog posts exactly as they do to campaign or landing pages. Your blog is not a separate system — it is simply another page tree using the full element library, with no walled-garden post editor to learn.', 'open_by_default' => 0],
                        ['title' => 'Workspace-safe staging and publishing', 'content' => 'Posts, tags, and authors are workspace-aware: editors create and modify them in a workspace, review the result in preview mode, and publish the whole workspace when the team is ready. Until then, every staged change stays invisible on the live site. Comments are the deliberate exception — they are visitor-generated and stay live-editable even during workspace editing, so readers can keep commenting on published posts regardless of editorial staging. The result is a clean separation: a content team can coordinate a publication day without draft changes bleeding into production.', 'open_by_default' => 0],
                        ['title' => '20 Extbase plugins: list, filter, detail, sidebar, and feed', 'content' => 'The extension ships 20 Extbase plugins, most of them available directly in the content-element wizard for editors. List all posts, show the latest N, filter by category, tag, or author, or render a paginated month-by-month archive. Sidebar plugins add related posts, tag clouds, category lists, recent posts, and a comment form, while feed plugins render RSS for subscribers. Together they cover the full blog journey — from landing-page lists through detail pages, sidebars, and feeds — without ever leaving the TYPO3 plugin architecture.', 'open_by_default' => 0],
                        ['title' => 'Categories, tags, and authors with rich metadata', 'content' => 'Posts organize via TYPO3 system categories and custom blog tags, both of which version in workspaces alongside the posts they describe. Authors are full records: each carries an avatar (Gravatar or a custom provider), Twitter, LinkedIn and Instagram links, a bio, and its own author detail page. A related-posts service scores other posts by shared categories and tags, so readers flow naturally from one article to the next. Visitors filter by category, tag, author, or archive date using the provided plugins, and because tags and authors are versioned too, a team can stage a new author introduction in the same workspace as their first posts.', 'open_by_default' => 0],
                        ['title' => 'Moderated comments with reCAPTCHA and notifications', 'content' => 'A built-in comment system lets visitors respond to posts and moves each comment through an editorial workflow: pending, approved, declined, or deleted. When a new comment arrives, the extension can notify both the post\'s author and a configured admin address by email. Google reCAPTCHA can be enabled per site to keep spam out of the queue. Comments stay live-editable even during workspace editing — they are always written to the live database — because the discussion on a published post is conversation, not editorial content, and should never be hidden behind a staging step.', 'open_by_default' => 0],
                        ['title' => 'Site sets and Fluid templates for three setups', 'content' => 'The Blog Extension ships three public site sets: blog/standalone for a dedicated blog, blog/integration to layer a blog into an existing site, and blog/bootstrap-53 for the shipped Bootstrap 5.3 frontend templates. Every template is a standard Fluid template — override it in your sitepackage exactly as you would any TYPO3 theme. Desiderio adds shadcn-styled blog templates with dark mode, accessible form fields, and a comment form that matches the active preset. Use those out of the box, or replace them with your own design language; nothing about the markup is locked down.', 'open_by_default' => 0],
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
                    'description' => 'The Blog Extension is free under GPL-2.0, ships complete with backend modules, 20 Extbase plugins, and full Workspaces integration. Install it via Composer and use the setup module to create a fully configured blog in minutes — or customize every Fluid template in your sitepackage.',
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
            'abstract' => 'Desiderio is a complete TYPO3 design system: 255 ready-to-use content elements built from 49 typed Fluid 5 components, a runtime theming layer driven by TYPO3 site settings, and open extensibility via Innesto—which grafts shadcn/ui registry components as new elements without a frontend build step on your site.',
            'description' => '255 shadcn/ui-styled content elements for TYPO3 14.3, extensible via Innesto. Runtime theming, Content Blocks 2.2, typed Fluid 5 components—all GPL-2.0.',
            'parentSlug' => 'features',
            'content' => [
                self::block('desiderio_headersection', [
                    'eyebrow' => 'Theme + Design System',
                    'header' => '255 elements. Extensible. Themed at runtime.',
                    'subheadline' => 'Desiderio brings a complete shadcn/ui-inspired design system to TYPO3 v14.3+: 255 finished content elements, 49 atomic Fluid 5 components, and an extensibility layer (Innesto) that grafts components from shadcn registries as new Content Blocks in one command.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What is Desiderio + Innesto',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'A complete, open-source design system for TYPO3 that you can extend in minutes.',
                    'content' => 'Desiderio is a TYPO3 v14.3+ extension that ships 255 shadcn/ui-styled content elements—not as a template kit you still have to build, but as a live, working editorial system: page templates, optional Blog, News, Solr, and Powermail overrides, and seeded demo content included, with no frontend build step on the target site. Innesto is its companion extension that makes Desiderio extensible: graft a component from a shadcn registry (shadcn/ui, Magic UI, blocks.so, and others that publish JSON) directly onto Desiderio as a new Content Block. Both extensions are free and open-source under GPL-2.0-or-later, so you own every line.',
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
                        ['icon' => 'zap', 'title' => '255 elements, zero build step', 'description' => 'Every content element—heroes, pricing tables, testimonials, forms, charts, footers—ships finished and ready to use. No template work, no design system assembly. Commit to TYPO3, enable the site sets, and editors start composing pages right away.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'Runtime-switchable theme presets', 'description' => 'Pick a design on ui.shadcn.com/create, paste the preset into TYPO3 site settings, and the whole site repaints—colors, radius, density, focus rings, fonts—without a rebuild. Fifteen presets are bundled (five from the shadcn create page plus ten house designs), switchable per site or per page subtree. Multiple icon libraries ship in the box.', 'link' => ''],
                        ['icon' => 'menu', 'title' => 'Atomic components, typed contracts', 'description' => '17 atoms (button, badge, input, avatar), 28 molecules (card, accordion, form field), and 4 layout primitives compose into all 255 elements. Each is a Fluid 5 component with typed f:argument contracts, so a single audit can verify every element and CI can reject any template that breaks the API.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Extend it. In minutes. With AI.', 'description' => 'Innesto grafts a shadcn registry component as a new Content Block in one command. The CLI fetches the JSON schema, converts the styling to semantic tokens, and scaffolds the element; the optional --ai flag finishes the React-to-Fluid conversion. Your custom element inherits the active Desiderio preset automatically.', 'link' => ''],
                    ],
                ]),
                self::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => '49 typed Fluid 5 components in atomic layers', 'content' => 'Desiderio\'s component system follows atomic design at the Fluid 5 level: 17 atoms (button, badge, input, icon, avatar, link, image, label) registered under the d:atom namespace; 28 molecules (card, accordion, table, alert, form controls) under d:molecule; and 4 layout primitives under d:layout. Every component declares typed f:argument contracts with defaults and optionality—not a naming convention, an enforced API that catches mistakes at render time. All 255 content elements are built exclusively from these layers, which is why one audit can verify every element and CI can reject any template that breaks the contract.', 'open_by_default' => 1],
                        ['title' => '255 finished content elements across ten categories', 'content' => 'The extension ships 255 ready-to-use, editor-facing content elements grouped into clear categories: heroes and landing intros, feature blocks, pricing and product elements, data and dashboard elements with chart helpers, trust and social-proof elements, plus navigation, footers, legal pages, forms, and editorial content. Every element appears in the New Content Element wizard with a backend preview, so editors see exactly what they are placing without opening a design file. Nothing here is a wireframe waiting to be skinned—each element is production-styled and themed by the active preset.', 'open_by_default' => 0],
                        ['title' => 'Runtime theme presets driven by TYPO3 site settings', 'content' => 'Desiderio\'s theming layer is pure CSS tokens applied at runtime, not a build-time decision baked into your deployment. The active preset is chosen in TYPO3 site settings and rendered as attributes on the page (data-shadcn-preset, data-icon-library, data-theme), which the committed Tailwind v4/shadcn CSS then consumes. Fifteen presets are bundled—five from the ui.shadcn.com/create page and ten house designs, plus a custom slot—and a preset change repaints colors, radius, density, focus rings, fonts, and icon-library behavior with no rebuild and no deployment. Because the preset lives in the rootline, you can run separate campaigns, product lines, or brands in their own theme from a single install, and stored content uses semantic icon keys so the icon library can change without rewriting records.', 'open_by_default' => 0],
                        ['title' => 'Innesto: graft shadcn registry components as Content Blocks', 'content' => 'Innesto is a companion extension that makes Desiderio extensible without a frontend build step on your site. Pick a component from a shadcn registry (shadcn/ui, Magic UI, blocks.so, or any registry that publishes JSON), run vendor/bin/typo3 innesto:add <component-name>, and Innesto fetches the JSON schema, converts the styling to semantic tokens, scaffolds a complete Content Blocks element folder with config.yaml and a Fluid template, and registers it in the New Content Element wizard. The mechanical phase is automatic; the finishing phase translates the markup to Fluid and models props as editor fields, which the CLI can handle with AI via the --ai flag or you can do by hand. Presentational components like marquees, logo clouds, and bento grids are the natural fit, and every graft automatically uses the active Desiderio preset.', 'open_by_default' => 0],
                        ['title' => 'Content Blocks 2.2: schema-first elements with backend previews', 'content' => 'Every one of the 255 elements is a TYPO3 Content Block built on friendsoftypo3/content-blocks ^2.2, not a traditional plugin. That means declarative config.yaml schemas with automatic database columns, backend preview templates that show editors what their content will look like before publish, and collection child records with explicit table mappings so there is no guessing which table a child lives in. Content Blocks also make content portable: export an element with its records, copy them to another site, and the schema handles the table creation and field mapping. Demo fixtures accompany the elements, which is how the seeder can build the entire demo site in one command.', 'open_by_default' => 0],
                        ['title' => 'Integrations: News, Blog, Solr, and Powermail with Brevo + Friendly Captcha', 'content' => 'Desiderio does not stop at its own elements. The TYPO3 extensions your site already runs get the same shadcn treatment through optional site sets that activate only when the matching extension is installed: shadcn-styled templates for georgringer/news, t3g/blog, Apache Solr, and in2code/powermail. The bundled TYPO3 Form Framework templates use a shared FormRenderer molecule, switching controls to destructive styling only on invalid states, and ship with Friendly Captcha integration plus a BrevoContactFinisher that synchronizes contacts to Brevo with double opt-in. The active preset and dark mode follow everywhere: switch the site theme and your news lists, search results, and forms switch with it.', 'open_by_default' => 0],
                        ['title' => 'Seeding and CLI for idempotent demo content', 'content' => 'Symfony console commands automate the heavy lifting: vendor/bin/typo3 desiderio:styleguide:seed creates or updates the full demo site (255 elements across a page tree) from YAML fixtures, idempotent and live-workspace-safe, with a --parent option to target where it lands; vendor/bin/typo3 desiderio:starter:seed creates a corporate starter site structure with demo content; and desiderio:blog:seed-pages normalizes an existing Blog page tree to Desiderio backend layouts. The styleguide and starter seeders refuse to run inside a workspace and refuse to run in Production context without an explicit --allow-production flag, so a reseed cannot silently overwrite a live site. There is also desiderio:library:warm to pre-render element previews for the backend.', 'open_by_default' => 0],
                    ],
                ]),
                self::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install-desiderio.sh',
                    'code' => 'composer require webconsulting/desiderio
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush

# Optional: seed the demo site with all 255 elements under page 1
vendor/bin/typo3 desiderio:styleguide:seed --parent=1

# Optional: graft a shadcn registry component as a new Content Block
composer require dirnbauer/innesto
vendor/bin/typo3 innesto:add magicui/marquee --ai',
                ]),
                self::block('desiderio_ctabanner', [
                    'header' => 'Ready to extend your TYPO3 site?',
                    'description' => 'Desiderio is free and open-source under GPL-2.0-or-later. You get all 255 elements, the fifteen bundled theme presets, the optional Blog, News, Solr, and Powermail integration sets, and the seeding tools—no license gate, no build step on your site. Innesto is open-source too, so extending Desiderio costs nothing but a command.',
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
                    'subheadline' => 'Apache Solr for TYPO3 is the production search engine with millisecond response times. Desiderio gives it a complete shadcn template set.',
                    'content' => 'Solr finds the content; Desiderio styles the interface. Every search result, facet, sort control, per-page switcher, and pagination button follows your active theme preset and respects light and dark mode. The template set ships ready to use, and refinements like faceting, sorting, and paging refresh over AJAX without a full page reload. When you switch the site theme from Lagoon to Midnight, the search results page repaints alongside it.',
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
                        ['title' => 'Search form with live suggest dropdown', 'content' => 'The search form pairs a search icon and submit button with a search input that opens a live suggest dropdown as you type. Suggestions are grouped under translated type labels for Pages, News, and Addresses, headed by a configurable \'Top Results\' label. Suggest requests run against the Solr backend, and submitting the form or choosing a suggestion takes you to the results page with no custom code.', 'open_by_default' => 1],
                        ['title' => 'Styled result cards with snippet excerpt and type badge', 'content' => 'Each result renders as a card with the document title as a link, the result URL, and a snippet excerpt highlighted around your search term. A content-type badge labels the source - Pages, News, or Addresses - and file results additionally list the MIME type and referenced files. Titles use semantic heading markup and every card is styled entirely from Desiderio tokens, so adjusting a radius or color token reflows every card in the next request.', 'open_by_default' => 0],
                        ['title' => 'Numbered pagination with smart truncation', 'content' => 'The pagination control lays out page numbers in order with previous and next controls on either side. A truncation algorithm collapses long ranges behind an ellipsis so the bar never overflows, and the current page renders with a solid primary background while the rest use the default outline style. The whole control is a nav landmark with an aria-label, the current page carries aria-current, and each link includes a \'Go to page N\' aria-label for screen readers.', 'open_by_default' => 0],
                        ['title' => 'Sort dropdown and per-page switcher in the toolbar', 'content' => 'A toolbar above the result list exposes a sort menu and a results-per-page switcher. Sort options render as a themed dropdown of links - relevance, date, title, and any others Solr returns - with the active option marked and its direction shown. The per-page switcher is a native select that resubmits on change, and the options offered come straight from your Solr per-page configuration rather than being hard-coded.', 'open_by_default' => 0],
                        ['title' => 'Active filters with one-click removal', 'content' => 'When a search is narrowed by facets, an \'Narrowed by\' bar appears above the results listing each applied filter as a removable chip. Every chip is a themed link with a clear-filter affordance and screen-reader text, plus a \'Remove all filters\' action to reset in one step. Removing a filter refines the search and refreshes the results over AJAX, so the page never fully reloads.', 'open_by_default' => 0],
                        ['title' => 'Faceting sidebar with live result counts', 'content' => 'The sidebar lists the facets Solr is configured to return - content type, categories, and any custom facets - each as its own labeled section. Options show their result count alongside the label, selected options are marked active, and a \'Show more\' toggle reveals options beyond the configured limit. Choosing an option adds it to the active filters and refreshes results over AJAX, and the counts reflect the current query.', 'open_by_default' => 0],
                        ['title' => 'Did-you-mean and auto-correct messaging', 'content' => 'When Solr returns spelling suggestions, the results page shows a \'Did you mean\' line with each suggestion as a link that re-runs the search with the corrected term. When Solr auto-corrects, a status line reads \'Nothing found for the original term. Showing results for the corrected term.\' Both use translated strings and theme-aware text colors so they stay readable in light and dark mode.', 'open_by_default' => 0],
                        ['title' => 'Frequent and recent searches', 'content' => 'Alongside the results, the template can surface a \'Frequent searches\' panel and a \'Last searches\' panel, each styled to match the facet sidebar. Both list past queries as themed links that start a new search on click and refresh over AJAX. Each panel is gated behind its own TypoScript switch, so you show only the ones you want.', 'open_by_default' => 0],
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
                    'description' => 'The template set ships with Desiderio. Solr itself is open source and runs locally or on a dedicated search node. The Desiderio layout, components, and theme integration do all the styling work.',
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
                    'content' => 'WorkOS is enterprise SSO with teeth: it handles email-and-password sign-in, magic links (zero password to remember), OAuth for Google, Microsoft, GitHub and Apple, and a full B2B workspace layer with organizations, invitations, roles, and admin portals. The workos_auth extension hooks all of it into TYPO3 at the auth level. That means your backend login and your frontend plugins share the same WorkOS credentials—and your customer IT admins get the same team and audit controls they expect from enterprise SaaS.',
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
                        ['title' => 'Frontend Login Plugin: Email, Magic Auth, and Social Sign-In', 'content' => 'Drop the WorkOS Login content element on any TYPO3 page and editors get a ready-built sign-in card with zero template work. The signed-out state shows an email and password field, a \'Send me a code\' magic-auth option with a dedicated code-entry step, and one-tap social buttons for Google, Microsoft, GitHub, and Apple. Users who need email verification (when WorkOS requires it) see a friendly inline form with resend support. Once signed in, the card displays the user\'s WorkOS profile, including any custom metadata stored on the WorkOS user record, plus a Sign Out button. The entire form is CSRF-protected and handles validation errors by re-rendering with previously entered data preserved, so sign-up feels like a native flow instead of a series of redirects.', 'open_by_default' => 1],
                        ['title' => 'Backend Login: WorkOS Tab in TYPO3 Login', 'content' => 'TYPO3\'s standard backend login gains a new WorkOS section—so users can either sign in with username + password the old way via the \'Login with username and password\' switcher, or click to WorkOS for the full enterprise experience. The WorkOS tab offers a \'Continue with WorkOS\' button that launches AuthKit (WorkOS\'s hosted login UI where users can choose password, magic, or social), direct social-sign-in buttons to skip the menu, an email field for magic-auth code delivery with a visible code-entry step, and an inline email-verification screen with one-click resend when WorkOS requires it. Backend magic-auth and email-verification state between send and verify is stored server-side, bound to an HttpOnly cookie, so the email address and WorkOS tokens never leak into the URL. TYPO3\'s default strict same-site cookies (BE.cookieSameSite = strict) keep working—after the WorkOS callback, the extension uses a same-origin continuation page before entering /typo3/main, so the backend session cookie is sent at the right moment. The hand-off goes through TYPO3\'s own auth service, so TYPO3 still writes its login logs, dispatches its login events, applies session-fixation protection, and continues into any backend MFA providers you have configured.', 'open_by_default' => 0],
                        ['title' => 'Account Center: Self-Service Profile, MFA, and Session Management', 'content' => 'Place the WorkOS Account Center plugin on a private page (typically a \'My account\' page) that only signed-in users can reach. It renders self-service cards, each backed by the WorkOS API and each gracefully degrading if a WorkOS call fails so one broken card never takes down the rest. The Profile card lets users update their first and last name, mirrored back to WorkOS via UserManagement::updateUser. The Password card lets them change their WorkOS password without leaving the site, with WorkOS-friendly error messages for too-short, too-weak, or breached passwords. The Two-Factor Authentication card enrolls an authenticator app (TOTP) by generating a QR code from WorkOS and displaying it inline; if the user can\'t scan, a fallback manual secret is shown. The Active Sessions card lists every WorkOS session for this user with IP address, browser/OS summary, and expiry date, plus a one-click revoke to sign out of individual devices or other devices. The Organizations card shows every WorkOS organization this user belongs to, their role in each, and a \'Directory Sync\' badge for external-IdP-managed memberships. Every state-changing action—password change, MFA enrollment, factor deletion, session revocation—requires a CSRF token and verifies that the user owns the resource before calling WorkOS.', 'open_by_default' => 0],
                        ['title' => 'Team Plugin: Invite Teammates and Launch Admin Portals', 'content' => 'For organization admins, the WorkOS Team plugin turns any TYPO3 frontend page into a workspace management console. An organization switcher appears at the top if the logged-in user belongs to more than one active WorkOS organization, letting them pick which one to manage (sticky per session). The Send Invitation section has an email field and optional role-slug dropdown; when submitted, WorkOS dispatches the invitation email and starts tracking acceptance. The Pending Invitations list shows every recent invite with its state badge (Pending / Accepted / Expired / Revoked), expiry date, accept link, and inline Resend and Revoke buttons. The Admin Portal launcher section exposes six one-time-link buttons that mint signed, organization-scoped links into WorkOS\'s Admin Portal for SSO configuration, Directory Sync (SCIM) setup, Audit Logs, Log Streams, Domain Verification, and Certificate Renewal—this is the exact flow WorkOS recommends for letting customer IT admins self-serve enterprise setup without you ever handling credentials. Every action is guarded: the plugin verifies the signed-in WorkOS user is an active organization admin or owner before calling the SDK, all POST requests require a CSRF token, and object-ownership checks confirm the user owns the organization before any state change. If the user is not yet a member of any active organization, a friendly empty-state card explains what to do next.', 'open_by_default' => 0],
                        ['title' => 'Provisioning and Identity Mapping: One Table, Full Profiles Stored', 'content' => 'When a user signs in via WorkOS (frontend or backend), workos_auth automatically creates or links their TYPO3 user. An identity mapping table (tx_workosauth_identity) stores the WorkOS user id, email, and the entire WorkOS profile JSON—every field returned by the API including custom metadata. This table is admin-only, hidden from the page tree, and pinned to non-versioning (versioningWS=false) so workspace drafts never mutate live authentication state. On subsequent logins, the extension resolves the existing link—no duplicate user records, no stale data. You can control auto-creation: if a WorkOS user signs in but no matching TYPO3 user exists, either fail the login with a friendly \'not linked\' message, or auto-create a TYPO3 user when their email matches a domain allowlist you configure. Backend users and frontend users are provisioned separately through the same mechanism, so your backend admin, your frontend customer, and your API can all be different people with separate accounts in WorkOS but the same underlying TYPO3 database.', 'open_by_default' => 0],
                        ['title' => 'Backend Modules: Setup Assistant, User Management Widget, and MCP Server Control', 'content' => 'A new top-level WorkOS menu appears in the TYPO3 backend (positioned after System, admin only). The Setup Assistant module displays every redirect URI that must be registered in the WorkOS Dashboard, offers a one-click copy-all action, and walks you through entering your API key, Client ID, and a cookie password of at least 32 characters—no PHP editing required. The User Management module embeds the official WorkOS User Management widget—admins can invite teammates, change roles, and remove users without leaving TYPO3, all bound to the signed-in backend user\'s WorkOS session and CSRF-protected. If the user is not yet part of any organization, the module presents a join-or-create flow first, and it can register configured WorkOS widget CORS origins from extension configuration. A third MCP Server module lets you configure the optional TYPO3 MCP (Model Context Protocol) endpoint: set the endpoint URL and AuthKit domain, choose the auth mode (auto / anonymous development / bearer-protected production), adjust the server limit, toggle verbose MCP logging, and run the WorkOS database schema migration through TYPO3\'s own schema migrator. All three modules register with workspaces => \'live\' and only appear in the LIVE workspace.', 'open_by_default' => 0],
                        ['title' => 'Dynamic Login URLs: Query Parameters for Custom Flows', 'content' => 'The frontend login URL accepts optional query parameters that customize the AuthKit experience without changing TYPO3 configuration. Pass screen=sign-up or screen=sign-in to open AuthKit on the desired screen. Pass provider=GoogleOAuth (or MicrosoftOAuth, GitHubOAuth, AppleOAuth) to jump straight to a social provider. Pass login_hint=email@example.com to pre-fill the email field. Pass organization=org-id to scope the login to a specific WorkOS organization. Pass returnTo with a relative path or a same-host absolute URL to redirect the user after login. Sanitization is strict: protocol-relative and cross-host values fall back to the configured default, preventing open-redirect vulnerabilities. This makes it trivial to build email-specific sign-in links for campaigns, or to scope team sign-up flows to a single organization.', 'open_by_default' => 0],
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
                    'subheadline' => 'Powermail is a proven form extension; Desiderio\'s shadcn partials and Friendly Captcha complete the stack.',
                    'variant' => 'center',
                ]),
                self::block('desiderio_textmedia', [
                    'header' => 'What Powermail + Desiderio does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The form extension that doesn\'t pretend to be a page builder.',
                    'content' => 'Powermail lets editors define forms in the backend — pages, fields, validation, receivers — without touching HTML, PHP, or templates. Desiderio reskins every field type with shadcn partials: inputs, checkboxes, selects, radio groups, and textareas, all themed to match your site. Friendly Captcha adds bot protection that sends no user IP to Google, and a context-aware bypass flag keeps local development fast when you opt in. The result: multi-step forms that look native to your design system and validate on both the client and the server.',
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
                        ['title' => 'Multi-step form pages with progress tracking', 'content' => 'Powermail splits complex forms across multiple pages, each shown in sequence with a step indicator. Visitors move forward and back between pages while client-side validation checks the current page before allowing progression, and the server re-validates every field on submit before storage. The seeded demos cover the full range: single-page forms like Contact, Newsletter Signup, and Callback Request; a two-step Appointment Request that separates contact details from scheduling; and a four-step Project Request wizard spanning contact details, project scope, budget and timing, and a closing consent step.', 'open_by_default' => 1],
                        ['title' => 'All field types restyled with Desiderio partials', 'content' => 'Powermail supports input fields, textareas, select dropdowns, radio groups, checkboxes, date fields, country selectors, file uploads, and more. Desiderio ships a shadcn partial for each type, so a Powermail text input wraps the same d:molecule.field component used across the design system and renders through f:form.textfield with theme-aware classes. The styling respects focus rings, disabled states, and error states, and form errors mark fields with a destructive border and ring. Label weight, spacing, and button styling all track the active theme tokens — switch the site preset and the form fields instantly reflow without any CSS modification, in both light and dark mode.', 'open_by_default' => 0],
                        ['title' => 'Client and server validation with clear error messages', 'content' => 'Powermail validates HTML5 constraint attributes in the browser (required, type=email, and the field\'s configured validators) and surfaces inline error text directly at the field when a check fails. Server-side validation repeats everything after submission so a bypassed client check never reaches storage. Editors configure validation rules per field without code — mark a field required, choose a validation type, attach a validator. For custom logic, Powermail dispatches a CustomValidatorEvent that lets developers add their own validation rules through a PSR-14 listener.', 'open_by_default' => 0],
                        ['title' => 'Friendly Captcha integration with a context-aware dev bypass', 'content' => 'The Friendly Captcha integration is installed alongside Powermail and configured per site in the TYPO3 site configuration. Every seeded demo form already carries a Friendly Captcha spam-protection field. The integration reads the TYPO3 application context: when you set friendlycaptcha_skip_dev_validation in the site configuration, verification is skipped in Development context — including DDEV — so local testing never waits on a captcha token. In every other context, and whenever that flag is off, the captcha is enforced. The widget is compact and GDPR-friendly, inherits its field styling from Desiderio, and sends no user IP to Google because Friendly Captcha uses a proof-of-work architecture instead.', 'open_by_default' => 0],
                        ['title' => 'Database storage, CSV export, and finisher hooks', 'content' => 'Powermail stores every submission in the database table tx_powermail_domain_model_mail, and the backend module lists submissions with search. Admins export results to CSV for analysis and can manage records from the module. For custom behaviour on submit, developers register Powermail finishers — the extension ships SendParameters, Redirect, SaveToAnyTable, and RateLimit finishers and a FinisherInterface to add your own, so you can send extra mail, write to an external API, or trigger downstream logic. A DataProcessor layer lets developers transform field data before it is persisted.', 'open_by_default' => 0],
                        ['title' => 'Receiver and sender mail with Fluid email templates', 'content' => 'On submission Powermail can mail static receivers, a frontend user group, or an address harvested from the form itself, and each form can define its own receiver and sender mail. Email bodies are Fluid templates with access to every submitted field value, so editors personalise them with markers such as the visitor\'s first name. The seeded forms use this for confirmation flows: each demo defines a thank-you message — for example, the Project Request confirms receipt and states a two-working-day response — and the receiver and sender addresses are configured per form. Developers can refine recipients and bodies through PSR-14 events like SenderMailPropertiesGetSenderEmailEvent and SendMailServiceCreateEmailBodyEvent.', 'open_by_default' => 0],
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
                    'description' => 'Powermail + Desiderio ships with six seeded demo forms — contact, newsletter, callback, appointment, support, and a four-step project request — each with Friendly Captcha and English and German thank-you flows. Modify them, duplicate them, or build new ones from scratch in the backend. No template code needed.',
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
                    'content' => 'HTTP 402 is a dormant HTTP status code designed exactly for this: a client requests content, your server responds with payment terms, the client signs the payment, and you settle it. x402-paywall wires this flow into TYPO3 middleware, adds a backend dashboard to measure revenue per page, and lets you gate traditional frontend routes and headless APIs the same way. No Stripe account, no payment processor fees, just USDC stablecoin wallets talking to wallets.',
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
                        ['title' => 'PSR-15 middleware payment enforcement', 'content' => 'The extension sits in the TYPO3 middleware stack and intercepts all matching requests before they reach your pages or API endpoints. For configured pages or route patterns, it checks for a valid PAYMENT-SIGNATURE header; if present and verified, the request proceeds; if absent, the middleware returns HTTP 402 with payment terms. This means you pay nothing for invalid requests and never have to modify page controllers or API code.', 'open_by_default' => 1],
                        ['title' => 'Per-page paywall configuration and price overrides', 'content' => 'An x402 Paywall tab in page properties lets editors enable the paywall, set a custom price in USDC, and describe the content (e.g. \'Exclusive analysis: €0.02\'). These settings override site defaults, so you can price flagship pages higher and experimental content lower — all from the page tree without touching configuration files or restarting services.', 'open_by_default' => 0],
                        ['title' => 'Headless route and API gating with wildcard patterns', 'content' => 'Configure route patterns in site settings to gate entire API namespaces or feed endpoints: /api/v1/content/*, /feed/*, or individual /some/specific/route. Requests to those routes without valid payments get HTTP 402; requests with a valid PAYMENT-SIGNATURE header (verified against the x402 facilitator) proceed and get logged. This lets AI agents, feed readers, and custom clients pay for access without touching a browser.', 'open_by_default' => 0],
                        ['title' => 'Frontend overlay plugin with EIP-1193 wallet signing', 'content' => 'The x402 paywall overlay plugin renders on gated pages and guides visitors through a three-step wallet flow: connect via MetaMask, Coinbase Wallet, Rabby, or any EIP-1193 provider; sign a payment message in the wallet; and send that signature back to the server. Once the signature is verified, the payment settles and the page content appears. Returning visitors who already have a connected wallet and a USDC balance move through the steps without leaving the page.', 'open_by_default' => 0],
                        ['title' => 'Backend dashboard with revenue, top pages, and transactions', 'content' => 'Open Web > x402 Paywall in the TYPO3 backend to see revenue totaled for today, seven days, thirty days, and all time; a ranked list of top monetized pages in the last 30 days; and a paginated transaction log showing wallet address, amount, date, and verification status for every settled payment. This dashboard requires no external analytics tool and reads straight from your TYPO3 database, so the numbers reflect the latest settled payments.', 'open_by_default' => 0],
                        ['title' => 'Public simulator for testing payment flows', 'content' => 'The backend dashboard includes a simulator that lets you test your paywall against public HTTPS URLs without risking real transactions. Enter a URL, select a test network and price, and the simulator makes a real x402 request to that URL, captures the response headers, and shows you the exact payment requirement your gateway generates. The simulator rejects private, local, and reserved-network targets to keep testing safe.', 'open_by_default' => 0],
                        ['title' => 'MCP tools for agent discovery and monitoring', 'content' => 'Five MCP (Model Context Protocol) tools ship built-in: x402_gated_pages lists all pages and routes configured for payment, x402_probe tests the payment flow against a live URL, x402_stats retrieves revenue and transaction counts, x402_transactions looks up specific payments by address or date, and x402_decode_header parses PAYMENT-SIGNATURE headers for debugging. These tools are designed for Claude Code and similar AI integrations so agents can audit your paywall setup or troubleshoot blocked requests.', 'open_by_default' => 0],
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
                    'content' => 'API Capability Bridge exposes your TYPO3 site\'s capabilities — news articles, file handling, permissions, workspace state — as a structured, policy-controlled API surface. You define what is accessible via a declarative YAML capability policy; the extension registers the corresponding CRUD resources with sg_apicore, authenticates requests through opaque backend bearer tokens, and enforces your policies at the resource level. Agents and external systems see a clean, verified contract of what they can read, create, update, or delete — safely isolated from the rest of your TYPO3 install.',
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
                        ['title' => 'Declarative capability policy', 'content' => 'Instead of hardcoding API routes, you define a single config/capability-policy.yaml file listing the api_definitions and api_resources that are available — which extension, table, base path, allowed operations, and required scopes apply to each. The CapabilityResourceRegistrar reads this policy and registers only the resources that pass its checks; no resource registration is automatic. This keeps the contract between your site and external agents explicit and auditable, living in version control alongside the rest of your configuration.', 'open_by_default' => 1],
                        ['title' => 'Secure backend bearer token provider', 'content' => 'The BackendBearerOpaqueTokenProvider implements sg_apicore\'s LoginProviderInterface to authenticate requests via opaque bearer tokens stored in the tx_apicore_token table. Each token maps to a TYPO3 backend user, carries a scope list (such as news:read or files:write), and is rejected once its expires_at timestamp passes. On every successful authentication the provider records a last-used timestamp and initializes the backend user context — so downstream queries run with that user\'s real permissions rather than an elevated service account.', 'open_by_default' => 0],
                        ['title' => 'News Studio API endpoints', 'content' => 'The NewsStudioController exposes REST endpoints for news editorial workflows: GET /studio/me (current user context and permissions), GET /studio/schema/news (TCA-generated form schema), GET /studio/records/{table} (searchable record picker for relations), plus /studio/files and /studio/files/upload for assets. Each route is guarded by a RequireScopes attribute and checks backend-user permissions before returning data. This lets external news tools, agents, and editorial frontends introspect and operate on your news content without touching TYPO3\'s admin interface.', 'open_by_default' => 0],
                        ['title' => 'Workspace submit, publish, and preview', 'content' => 'Beyond reading and writing records, the controller drives the editorial lifecycle: POST /studio/news/{id}/submit moves a record into review, POST /studio/news/{id}/publish releases it, and GET /studio/news/{id}/preview returns a workspace-aware rendering. The publish route additionally requires the workspace:publish scope, so an agent can draft and submit content while a human (or a more privileged token) retains the authority to actually go live. GET /studio/workspaces and POST /studio/workspaces/switch let a token enumerate and target the workspace it operates in.', 'open_by_default' => 0],
                        ['title' => 'Policy-based resource registration', 'content' => 'The bridge does not blindly register every available resource. For each entry in your policy the registrar loads the extension\'s capability manifest, computes a risk score from its declared risk level, and runs PolicyChecker against your policy rules — deny lists, review-required flags, max_risk_score, and require_manifest. If an extension lacks a manifest or fails the audit, the resource is refused. This prevents accidental over-exposure of untrusted or poorly documented extensions and keeps your API surface intentional.', 'open_by_default' => 0],
                        ['title' => 'Workspace and permission awareness', 'content' => 'Tokens can target a specific workspace via the X-TYPO3-Workspace header (or a workspace query parameter), and the provider applies it through setTemporaryWorkspace on the backend user. Record queries add RootLevelRestriction and DeletedRestriction so agents only retrieve appropriate, non-deleted records. File capabilities are derived from the backend user\'s groupData file_permissions — readFile and addFile rights — meaning an agent can never read or write files the authenticated user could not handle in the backend itself.', 'open_by_default' => 0],
                        ['title' => 'Full sg_apicore integration', 'content' => 'Built on sg_apicore\'s ApiRegistry and ResourceRegistry, the bridge reuses existing CRUD resource patterns, scoped token handling, and response serialization. Your agents get standard REST semantics — proper HTTP verbs, status codes, and the CORS allowed-origins you configure per API definition — and your setup works with any tool that speaks sg_apicore\'s API format. Custom controllers extend the surface using the same ApiRoute and RequireScopes attributes, so new endpoints inherit the same scope guards and token enforcement.', 'open_by_default' => 0],
                        ['title' => 'Auditable token and usage tracking', 'content' => 'Every token record carries metadata: the backend user it belongs to, its issued scopes, an optional expiration, and a last-used timestamp that the provider refreshes on each authenticated call. Expired tokens are rejected outright. Because the records live in the database, you get a queryable trail of which external systems hold credentials, what they are scoped to do, and when they last connected — useful for compliance reviews and for debugging agent behavior.', 'open_by_default' => 0],
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
                    'content' => 'Most feedback loops are broken: users describe what they want, developers guess what they meant, agents work blind. Agentation closes that loop. A backend user clicks an element, writes a note, and the agent receives not just the comment but the selector, computed styles, and page context it needs to make the change. Two toolbars — one on the frontend for cross-browser testing, one in the backend for module tweaks — stay behind a login gate and a context guard so they never leak to production.',
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
                        ['title' => 'Frontend annotation toolbar', 'content' => 'A gated toolbar appears on your frontend when an authenticated backend user visits. The toolbar respects an Admin Panel section toggle — meaning frontend annotation is always explicit, never automatic. Users click elements to highlight them, add comments, and optionally capture computed styles and DOM selectors. The toolbar position and scope are configurable per request through Admin Panel settings, and annotations persist in both browser-local storage and optional server endpoints.', 'open_by_default' => 1],
                        ['title' => 'Backend module frame annotation', 'content' => 'The same toolbar injects into TYPO3 module content frames when the global backend toggle and the current user\'s backend toolbar setting are both enabled. This means TYPO3 administrators and developers can annotate the page module, record forms, and third-party extensions inline. Annotations from the backend flow into the same storage system as frontend annotations, so the entire feedback trail stays in one place for agents to reference.', 'open_by_default' => 0],
                        ['title' => 'Admin-only System > Agentation module', 'content' => 'Only administrators see the backend module at System > Agentation. The module displays the generated MCP configuration (as a copyable JSON block, a Claude Code CLI command, or a Cursor deep link), shows status checks for your API key and app context, and provides a full list of stored annotations. Users can reload annotations, delete individual ones, or clear all stored feedback from the module UI. The same module also links to agentation.com for upstream documentation.', 'open_by_default' => 0],
                        ['title' => 'MCP configuration and export', 'content' => 'The System > Agentation module assembles a ready-to-paste mcpServers config block that Claude Code, Cursor, Windsurf, Zed, Continue, and any MCP-capable agent can consume. The block is pre-filled with your workspace ID and — when set — an API key (optional for local workflows, required for server sync). A one-click button copies the JSON, or you can grab a pre-built \'claude mcp add\' command for Claude Code or a one-click install deep link for Cursor.', 'open_by_default' => 0],
                        ['title' => 'Per-user frontend and backend settings', 'content' => 'Each TYPO3 backend user controls whether they see the frontend toolbar and backend toolbar independently via their user settings in the backend. Administrators set the default opt-in behavior, while individual users can still flip each toolbar on or off for themselves. This means a designer may only want frontend annotations while a developer uses both.', 'open_by_default' => 0],
                        ['title' => 'Application context gate', 'content' => 'The extension respects TYPO3\'s application context. By default, the toolbar only activates in Development contexts — preventing accidental exposure in Staging or Production. A configuration option lets administrators widen this to \'Development and Testing\' or \'All contexts\' per environment, but the safe default makes it nearly impossible to ship a toolbar to production unintentionally.', 'open_by_default' => 0],
                        ['title' => 'Same-origin backend proxy for local and cloud sync', 'content' => 'When annotations need to sync to a server (e.g., agentation.com or a self-hosted endpoint), the TYPO3 backend handles the HTTP request instead of the browser. This lets TYPO3 attach the API key server-side, handle cross-origin concerns, and keep credentials out of browser storage. Local workflows work offline — annotations stay in browser storage until explicitly synced.', 'open_by_default' => 0],
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
                    'content' => 'sg_apicore is a modern API core for TYPO3 that handles multi-API registration, versioning, tenant-aware routing, OpenAPI 3 generation, and authentication in one lightweight package. Write your business logic in controller methods, annotate them with attributes, and the framework generates documentation, handles auth, enforces scopes, and exposes the same endpoints as MCP tools for AI agents. No configuration overhead. No response wrapper conventions to learn. No per-endpoint auth reimplementation.',
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
                        ['title' => 'Multi-API and versioning in the same install', 'content' => 'Register multiple APIs (public, partner, internal) and assign different versions to each one. Route `/api/public/v1/...` and `/api/partner/v2/...` to separate controller actions or implementations without name conflicts. Each API can have its own authentication mode, rate limits, CORS policy, and MCP exposure settings. Restrict any endpoint to specific APIs or versions with the `apiId` and `version` properties on #[ApiRoute], so the same controller can serve several APIs at once.', 'open_by_default' => 1],
                        ['title' => 'Token management and scope-based access control', 'content' => 'Create opaque bearer tokens for machine-to-machine access, issue JWT access tokens with opaque refresh tokens for frontend users, or bind tokens to a specific frontend user for per-user API key flows. Assign scopes to tokens and enforce them with the #[RequireScopes] attribute on any endpoint. The backend token module provides a filterable token list, split into machine, user, and refresh categories, with expiration handling and token regeneration.', 'open_by_default' => 0],
                        ['title' => 'Workspace-aware auto-CRUD resources with DataHandler', 'content' => 'Register TYPO3 tables as REST resources with a single call. Get automatic list, get, create, update, and delete endpoints. All writes go through TYPO3\'s DataHandler, so hooks fire, reference indexing updates, and history records are written — exactly like the backend would. Auto-CRUD also respects TYPO3 workspace visibility: live reads drop draft and version rows, workspace reads overlay the current draft via workspaceOL(), and delete placeholders stay hidden. No raw SQL, no consistency surprises.', 'open_by_default' => 0],
                        ['title' => 'OpenAPI 3.0 specification generation and live testing', 'content' => 'Every endpoint exports to OpenAPI 3.0.3 JSON. Attributes like #[ApiResponse], #[ApiBodyParam], and #[ApiQueryParam] describe parameters, bodies, and responses; the framework enriches the resulting schemas with TCA field labels so the spec mirrors your real data model. Swagger UI appears automatically at `/api/{apiId}/v{version}/docs/ui` so clients can see what the API does, try endpoints live, and copy requests without separate documentation. Export the spec via CLI (`api:openapi:generate`) for code generators or API portals.', 'open_by_default' => 0],
                        ['title' => 'MCP (Model Context Protocol) tool exposure from endpoints', 'content' => 'Expose existing endpoints as MCP tools without duplication. Claude, Cursor, and other AI agents discover tools via `POST /api/{apiId}/v{version}/mcp` (JSON-RPC), call them through the same auth and routing layer, and receive results in standard MCP format. A companion `GET /mcp` endpoint supports SSE-style server-to-client communication. Per-API denylists and endpoint-level #[ApiMcp] attributes let you hide sensitive endpoints, and the `api:mcp:list` CLI command previews which endpoints are exposed before deployment.', 'open_by_default' => 0],
                        ['title' => 'Multi-tenancy and site-aware request context', 'content' => 'Every API request runs in a TenantContext resolved from the TYPO3 Site. Endpoints can filter by tenant via the `tenants` property on #[ApiRoute], ensuring a partner API only answers from specific sites. Tenant data flows through the request lifecycle so downstream code (DataHandler operations, custom queries) knows which site owns the data. Ideal for multi-brand or multi-client installs where the same endpoint serves different content per domain.', 'open_by_default' => 0],
                        ['title' => 'Rate limiting with burst and windowed enforcement', 'content' => 'Enable rate limits per API, per resource, or per endpoint. Specify a request limit, window size, and optional burst allowance. Responses carry X-RateLimit-Limit, X-RateLimit-Remaining, X-RateLimit-Reset, and X-RateLimit-Burst headers so clients always know where they stand. When a client exceeds the limit, the framework returns a 429 response. Configuration can be granular (one rule per endpoint) or blanket (apply to an entire API version).', 'open_by_default' => 0],
                        ['title' => 'Request/response logging and request tracing', 'content' => 'API calls log through a structured logger with configurable redaction. Every request gets a unique Request ID returned in the X-Request-ID header, so you can trace a single action from the API call through DataHandler updates and any dependent business logic. Error responses follow RFC 7807 Problem JSON and include the same requestId, so support teams can find the exact event in logs. Sensitive keys — password, token, authorization, secrets, and cookies by default — are redacted before anything is written, and the redaction list is fully configurable.', 'open_by_default' => 0],
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
                    'content' => 'Skillflow is a TYPO3 extension that manages Anthropic-style agent skills: editable records in the backend, importable from git repositories or local folders (each as a SKILL.md file with YAML frontmatter). You assign them to workspace stages so they review content automatically when records are staged; run them manually from the module against pages; and search your whole skill library with Solr-powered faceted search. The skill body becomes the system prompt; with the CLI runner, whitelisted tools become available to the agent; with the API runner, remote MCP servers extend the agent\'s reach. Reports are stored and never auto-applied — always suggestions for your team\'s review.',
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
                        ['title' => 'Skill records with SKILL.md structure', 'content' => 'Every skill is a backend record (tx_skillflow_skill) that mirrors the Anthropic skill format: a name, identifier, description, and markdown body containing the agent instructions. The record editor shows the body in a code editor, and extra frontmatter (like allowed-tools) stays in a JSON field so you can author skills entirely in the backend or import them from git. The structure is fully bidirectional: edit a skill in TYPO3, export it to a folder, and it becomes a valid Anthropic skill immediately.', 'open_by_default' => 1],
                        ['title' => 'Supporting file attachments with text indexing', 'content' => 'Everything that lives next to the SKILL.md — reference documents in a references/ folder, helper scripts, templates — is imported as attachment records (tx_skillflow_file) and appears under the skill\'s Attachments tab. Text files up to 256 KB are indexed for search; binaries are counted but skipped. When you re-sync a repository, the attachments update in place and soft-delete ones whose source files disappeared. At skill run time, the CLI runner materializes the full folder into var/transient/ so progressive disclosure works as intended; the API runner inlines referenced files into the system prompt under a size budget.', 'open_by_default' => 0],
                        ['title' => 'Folder and repository imports with stable UIDs', 'content' => 'Scan a configurable local folder (default <project>/skills/, each subfolder a SKILL.md) and sync them into TYPO3 on demand or on a cron schedule. For remote skills, point a repository record at a GitHub, GitLab, or Gitea URL (or a direct .zip file); sync downloads the archive and imports all skills. Critically, UIDs stay stable across re-syncs, so workspace stage assignments and page skill links survive every refresh — you never have to re-wire. Private repositories store only the name of an environment variable holding the token; the secret never touches the database.', 'open_by_default' => 0],
                        ['title' => 'Backend module for skill management and run reports', 'content' => 'The Content > Skills module lists all skills with edit/delete actions, shows a Solr-powered search with facet filters (runner type, allowed tools, categories), and lets you manage repository records from one place. Trigger imports and re-syncs directly in the UI. When you run a skill against a record or page, the execution report appears in the module: the skill\'s prompt, the AI response, extracted suggestions, and execution time. Reports are stored server-side (tx_skillflow_run) and shown alongside the record history so your team can review what the agent found before applying any changes.', 'open_by_default' => 0],
                        ['title' => 'Workspace integration with stage-triggered auto-run', 'content' => 'Navigate to any custom workspace stage (Content > Workspace) and open the Skills tab. Assign one or more skills and enable auto-run. Now when any record is sent to that stage, skillflow runs all assigned skills in sequence, stores the reports, and shows a notification to the editor. This closes the loop between workflow and quality: new records enter the review stage, skills immediately report SEO gaps or tone issues, and the editor refines before publishing. Per-workspace, you can also enable auto-workflow for new elements so every freshly created record is automatically sent to a configured stage and its skills.', 'open_by_default' => 0],
                        ['title' => 'Page-level skill assignment and manual runs', 'content' => 'Any page can carry assigned QM skills (SEO, tone of voice, content QA, image alt-text analysis) via the Skills tab in page properties. Unlike stage skills that run on save, page skills run when you explicitly trigger them from the module or via an action button. They review the page in your current workspace (draft content is accessed via workspace overlays) and return reports. This is how you run focused editorial reviews on landing pages, campaign microsites, or anything that needs a final QA pass before going live, without waiting for a stage transition.', 'open_by_default' => 0],
                        ['title' => 'CLI sync command for cron-scheduled imports', 'content' => 'vendor/bin/typo3 skillflow:sync is a simple, cron-able command that refreshes the folder (local <project>/skills) and all repository records in one pass. No web request, no UI click needed. Pair it with a TYPO3 scheduler task or a cron job to keep imported skills up to date automatically. When you need to refresh the search index, a separate skillflow:solr:index command re-indexes all skills into Solr for faceted search, so you can schedule import and indexing independently.', 'open_by_default' => 0],
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
