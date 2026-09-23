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
                'badge_text' => 'For TYPO3 v14',
                'header' => '244 ready-made content elements for TYPO3',
                'subheadline' => 'Desiderio brings shadcn/ui to TYPO3: 244 content elements, 62 Fluid components and 15 theme presets in one Composer package. Change the theme in the site settings, without a rebuild.',
                'primary_button_text' => 'Get started free',
                'primary_button_link' => ShowcaseBlocks::REPO_URL,
                'primary_button_variant' => 'default',
                'secondary_button_text' => 'See the features',
                'secondary_button_link' => '{{page:technical-features}}',
                'hero_image' => ShowcaseBlocks::screenshot('frontend-themes-overview-296b3997.png', 'Six of the 15 theme presets', 'Preset cards on the Desiderio themes page, each rendered live in its own theme preset.'),
                'image_position' => 'right',
                'overlay_opacity' => '0.5',
            ]),

            // ------------------------------------------------- the problem
            ShowcaseBlocks::block('desiderio_featurecards', [
                'eyebrow' => 'Common problems',
                'header' => 'Six problems Desiderio solves',
                'subheadline' => 'Each card names a problem that slows TYPO3 teams down, and what Desiderio does about it.',
                'items' => [
                    ['title' => 'Layout changes need a developer', 'description' => 'Editors build pages from finished elements. Each element has a backend preview and can be edited inline in the Visual Editor.'],
                    ['title' => 'One edit breaks the layout', 'description' => 'Every element is built from typed Fluid components that share one set of design tokens. Spacing, colour and type stay consistent.'],
                    ['title' => 'Too many plugins to maintain', 'description' => 'News, Solr, Blog, Powermail and the Form Framework use the same templates. You update one package instead of five.'],
                    ['title' => 'A new design means a rebuild', 'description' => 'Themes are colour tokens that switch at runtime. Change the look of a site, or of one page tree, in the site settings.'],
                    ['title' => 'Accessibility is now required', 'description' => 'The European Accessibility Act applies to many sites. The build checks WCAG 2.2 contrast for every preset, and the markup uses proper landmarks.'],
                    ['title' => 'AI search skips your content', 'description' => 'Semantic HTML, FAQ and how-to elements and clean page metadata make your content easy for AI search to quote.'],
                ],
            ]),

            // ------------------------------------- the create page on ui.shadcn.com
            ShowcaseBlocks::block('desiderio_featurealternating', [
                'header' => 'Change the theme in three steps',
                'subheadline' => 'Desiderio uses the same design tokens as shadcn/ui. A theme you design on the shadcn/ui create page works in TYPO3 as it is.',
                'items' => [
                    [
                        'title' => '1 · Design your theme',
                        'description' => 'On the shadcn/ui create page you choose colours, fonts, corner radius and a style. The result is a short preset code.',
                        'image' => ShowcaseBlocks::screenshot('frontend-shadcn-create.png', 'The create page on ui.shadcn.com', 'The shadcn/ui create page with controls for colours, fonts and style.', 'Screenshot of the shadcn/ui create page, where Desiderio presets come from.'),
                        'link' => ShowcaseBlocks::CREATE_URL,
                    ],
                    [
                        'title' => '2 · Paste the preset code',
                        'description' => 'Paste the code into the site settings and the whole site changes. No rebuild and no deployment. 15 presets are included.',
                        'image' => ShowcaseBlocks::screenshot('backend-page-properties-theme-12e5b64a.png', 'Theme preset in the page properties', 'TYPO3 page properties with the Desiderio theme preset field.'),
                        'link' => '{{page:technical-features}}',
                    ],
                    [
                        'title' => '3 · Give each page tree its own look',
                        'description' => 'Any page can have its own preset, and its subpages inherit it. This site shows it: each chapter page uses a different theme.',
                        'image' => ShowcaseBlocks::screenshot('frontend-pricing-midnight-dark-87c4160e.png', 'Pricing page in the Midnight preset', 'A Desiderio pricing page in the dark Midnight theme preset.'),
                        'link' => '{{page:themes}}',
                    ],
                ],
            ]),
            ShowcaseBlocks::block('desiderio_howtosteps', [
                'header' => 'Set up a themed site in four steps',
                'description' => 'Install, seed, pick a theme, publish. There is no licence key and no setup wizard.',
                'items' => [
                    [
                        'title' => 'composer require webconsulting/desiderio',
                        'content' => 'Installs the elements, the components and the themes. Needs TYPO3 v14.3 and PHP 8.4 or newer.',
                        'image' => ShowcaseBlocks::unsplash('desk-logan-weaver.jpg', 'Developer desk during installation', 'A tidy developer desk with a laptop.'),
                    ],
                    [
                        'title' => 'vendor/bin/typo3 desiderio:styleguide:seed',
                        'content' => 'Creates this demo site with all 244 elements, so you can test with real content.',
                        'image' => ShowcaseBlocks::screenshot('backend-page-module-hero-e23618aa.png', 'Seeded page in the page module', 'The TYPO3 page module with a seeded Desiderio hero element and its preview.'),
                    ],
                    [
                        'title' => 'Pick a theme preset',
                        'content' => 'Choose one of 15 presets in the site settings, or paste your own preset code.',
                        'image' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset in the site settings', 'TYPO3 site settings with the Desiderio theme preset list open.'),
                    ],
                    [
                        'title' => 'Publish',
                        'content' => 'Editors build pages in the content wizard, check the backend preview and edit inline in the Visual Editor.',
                        'image' => ShowcaseBlocks::screenshot('frontend-hero-lagoon-e69362e6.png', 'Published Desiderio site', 'The Desiderio demo site in the Lagoon theme preset.'),
                    ],
                ],
            ]),

            // ----------------------------------------------------- atomic design
            ShowcaseBlocks::block('desiderio_featurecards', [
                'eyebrow' => 'Architecture',
                'header' => 'Built from 62 typed components',
                'subheadline' => 'Every element is assembled from Fluid 5 components with typed arguments. Small parts make up larger ones, and nothing is styled twice.',
                'items' => [
                    ['title' => '17 atoms', 'description' => 'Button, badge, input, icon, avatar and text. They use only design tokens, so a new preset changes all of them.'],
                    ['title' => '37 molecules', 'description' => 'Cards, accordions, tabs, tables, alerts and form fields, built from atoms. Your own elements can use them too.'],
                    ['title' => '4 layouts', 'description' => 'Section, container, grid and stack handle spacing and container queries. No element needs its own margins.'],
                    ['title' => '4 organisms', 'description' => 'Site header, footer, breadcrumb and page header. Page templates only arrange organisms and content areas.'],
                    ['title' => '244 content elements', 'description' => 'Heroes, pricing tables, dashboards and footers use the same components. That is why one audit can check all of them on every commit.'],
                ],
            ]),

            // ----------------------------------------------------- speed
            ShowcaseBlocks::block('desiderio_featurestats', [
                'header' => 'Fast to run, edit and launch',
                'description' => 'Three kinds of speed: page performance, editing and project delivery.',
                'items' => [
                    ['value' => '0', 'label' => 'Rebuilds for a new design', 'description_text' => 'Themes switch through CSS tokens at runtime. No Tailwind build and no deployment.'],
                    ['value' => '100%', 'label' => 'Static CSS, no JS framework', 'description_text' => 'No React or Vue in the frontend. Charts render on the server.'],
                    ['value' => '1', 'label' => 'Command for the full demo site', 'description_text' => 'The seeder builds all 244 examples in seconds. You can run it as often as you like.'],
                    ['value' => '30', 'label' => 'Minutes from install to themed site', 'description_text' => 'Install, seed, pick a preset, publish. Or book the installation service for €890.'],
                ],
            ]),

            // ----------------------------------------------------- 244 elements
            ShowcaseBlocks::block('desiderio_featurecards', [
                'eyebrow' => 'The library',
                'header' => '244 content elements in 10 groups',
                'subheadline' => 'Every element has a preview in the content wizard. The chapter pages of this site show all of them, each chapter in its own theme.',
                'items' => [
                    ['title' => '21 heroes and page intros', 'description' => 'Split, animated, countdown, video and stats layouts for the first screen of a page.'],
                    ['title' => '25 feature blocks', 'description' => 'Grids, bento layouts, comparisons, timelines and tabs to explain a product.'],
                    ['title' => '25 pricing elements', 'description' => 'Plan tables, calculators, sliders and order summaries.'],
                    ['title' => '29 data elements', 'description' => 'Nine chart types, KPI cards, changelogs and status boards, rendered on the server with accessible tables.'],
                    ['title' => '49 trust and team elements', 'description' => 'Testimonials, case studies, logo walls and team grids.'],
                    ['title' => '95 more elements', 'description' => 'Navigation, footers, legal pages, forms and editorial content.'],
                ],
            ]),

            // ------------------------------------------------------------ forms
            ShowcaseBlocks::block('desiderio_featurecards', [
                'eyebrow' => 'Forms',
                'header' => 'Forms that work out of the box',
                'subheadline' => 'Contact, newsletter, demo and booking elements contain real TYPO3 Form Framework forms. Eight forms are ready to use.',
                'items' => [
                    ['title' => 'They match your theme', 'description' => 'Every form follows the active theme preset. There is no extra styling and no third-party widget.'],
                    ['title' => 'Leads go to your CRM', 'description' => 'Submissions reach Brevo through a built-in finisher. The newsletter uses double opt-in.'],
                    ['title' => 'Ready for GDPR', 'description' => 'Friendly Captcha works without tracking cookies. A data-request form handles exports, deletions and corrections.'],
                    ['title' => 'Standard Form Framework', 'description' => 'Validation, finishers, multi-step forms and file uploads come from TYPO3 core. There is nothing proprietary to learn.'],
                ],
            ]),

            // ---------------------------------------------------------- gallery
            ShowcaseBlocks::block('desiderio_gallery', [
                'header' => 'Five elements to start with',
                'subheadline' => 'Screenshots from this site. Select one to open its chapter.',
                'columns' => '3',
                'items' => [
                    ['title' => 'Parallax hero', 'description' => 'A hero that moves as you scroll. One of 21 hero layouts.', 'link' => '{{page:chapter-hero}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-parallax-hero-6e758c41.png', 'Parallax hero element', 'The Desiderio parallax hero element in the Forest theme preset.')],
                    ['title' => 'Bento feature grid', 'description' => 'Feature cards in mixed sizes, from the features group.', 'link' => '{{page:chapter-features}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-bento-features-56dcd554.png', 'Bento feature grid element', 'The Desiderio bento feature grid with feature tiles in mixed sizes.')],
                    ['title' => 'Toggle pricing', 'description' => 'Monthly and yearly prices with a highlighted plan.', 'link' => '{{page:chapter-pricing}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-toggle-pricing-8cab963a.png', 'Toggle pricing element', 'The Desiderio toggle pricing element with a monthly and yearly switch.')],
                    ['title' => 'Testimonial wall', 'description' => 'Many short quotes in a masonry grid.', 'link' => '{{page:chapter-social-proof}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-testimonial-wall-c7826ace.png', 'Testimonial wall element', 'The Desiderio testimonial wall with quotes in a masonry grid.')],
                    ['title' => 'Demo request form', 'description' => 'A lead form that renders a real TYPO3 form.', 'link' => '{{page:chapter-conversion}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-demo-request-f2614531.png', 'Demo request element', 'The Desiderio demo request element with a TYPO3 Form Framework form.')],
                ],
            ]),

            // ----------------------------------------------------- target groups
            ShowcaseBlocks::block('desiderio_usecasegrid', [
                'eyebrow' => 'Who it\'s for',
                'header' => 'Built for three kinds of teams',
                'subheadline' => 'Each group has its own page with details and a recommended plan.',
                'items' => [
                    [
                        'title' => 'Agencies and integrators',
                        'description' => 'Deliver fixed-price projects faster, with finished elements and a theme per client.',
                        'link' => '{{page:target-groups/for-agencies}}',
                    ],
                    [
                        'title' => 'In-house marketing and product teams',
                        'description' => 'Build campaign pages without a developer ticket. Each campaign can have its own theme.',
                        'link' => '{{page:target-groups/for-inhouse-teams}}',
                    ],
                    [
                        'title' => 'Freelancers and solo developers',
                        'description' => 'Get a complete design system with quality checks, for €0.',
                        'link' => '{{page:target-groups/for-freelancers}}',
                    ],
                ],
            ]),

            // ----------------------------------------------------- advantages
            ShowcaseBlocks::block('desiderio_featurechecklist', [
                'eyebrow' => 'Advantages',
                'header' => 'Why teams choose Desiderio',
                'items' => [
                    ['title' => 'A new design without a rebuild', 'description_text' => '15 presets, or your own from the shadcn/ui create page. Switch them per site or per page tree.'],
                    ['title' => 'Fewer tickets from editors', 'description_text' => 'Backend previews for all 244 elements, inline editing and a content wizard in ten groups.'],
                    ['title' => 'Accessibility you can prove', 'description_text' => 'The build checks WCAG 2.2 contrast for every preset, in light and dark mode.'],
                    ['title' => 'Measured quality', 'description_text' => 'PHPStan at level 8, unit and functional tests on PHP 8.4 and 8.5, and a template audit with zero findings.'],
                    ['title' => 'Free and open source', 'description_text' => 'GPL-2.0, with the full source on GitHub. Paid plans buy support, not features.'],
                ],
            ]),

            // ----------------------------------------------------- integrations
            ShowcaseBlocks::block('desiderio_featurecards', [
                'eyebrow' => 'Integrations',
                'header' => 'Templates for the extensions you use',
                'subheadline' => 'Each template set follows the active theme preset, in light and dark mode.',
                'items' => [
                    ['title' => 'Solr search', 'description' => 'Results, facets, sorting, suggestions in the header and accessible pagination.'],
                    ['title' => 'Powermail', 'description' => 'All field types restyled, six demo forms and Friendly Captcha.'],
                    ['title' => 'News', 'description' => 'Teaser cards, detail views, lists and pagination for the News extension.'],
                    ['title' => 'Blog', 'description' => 'Post lists, sidebars and a comment form for the Blog extension.'],
                    ['title' => 'Form Framework', 'description' => 'Eight ready forms, with double opt-in through Brevo.'],
                    ['title' => 'Visual Editor', 'description' => 'Inline editing in the frontend, plus styled core content elements.'],
                ],
            ]),

            // ----------------------------------------------------- pricing
            ShowcaseBlocks::block('desiderio_pricingthreetier', [
                'eyebrow' => 'Pricing',
                'header' => 'Free to use. Paid plans add support.',
                'subheadline' => 'The package is complete and free under GPL-2.0. Pro and Agency add support and guarantees, and yearly billing saves two months.',
                'plans' => [
                    ['name' => 'Community', 'price' => '€0', 'billing_period' => 'forever', 'description' => 'The full package: all elements, themes and integrations.', 'features' => ['All 244 content elements', '15 theme presets', 'Demo site in one command', 'Community support on GitHub'], 'is_recommended' => false, 'button_text' => 'Install for free', 'button_link' => ShowcaseBlocks::REPO_URL],
                    ['name' => 'Pro', 'price' => '€49', 'billing_period' => 'per month · €490 per year', 'description' => 'For teams that ship sites on a deadline.', 'features' => ['Email support, answer within 2 days', 'LTS compatibility updates', 'Early access to new elements', 'Help with minor upgrades'], 'is_recommended' => true, 'button_text' => 'Choose Pro', 'button_link' => ShowcaseBlocks::REPO_URL],
                    ['name' => 'Agency', 'price' => '€149', 'billing_period' => 'per month · €1,490 per year', 'description' => 'Unlimited projects and direct contact with the maintainers.', 'features' => ['Everything in Pro, unlimited projects', 'Answer within 4 business hours (CET)', 'Quarterly editor onboarding', 'Preset review by the maintainers'], 'is_recommended' => false, 'button_text' => 'Choose Agency', 'button_link' => ShowcaseBlocks::REPO_URL],
                ],
            ]),

            // ----------------------------------------------------- proof + CTA
            ShowcaseBlocks::block('desiderio_testimonialgrid', [
                'eyebrow' => 'Sample quotes',
                'header' => 'How teams could use Desiderio',
                'columns' => '3',
                'testimonials' => [
                    ['quote' => 'We showed the client three themes in the kickoff by switching presets live.', 'author_name' => 'Hannah Vogel', 'author_title' => 'Lead integrator (example)'],
                    ['quote' => 'Our editors now build campaign pages themselves. The backend previews show them what they publish.', 'author_name' => 'Jonas Klein', 'author_title' => 'Head of digital (example)'],
                    ['quote' => 'As a freelancer I can offer a full design system at a small studio\'s price.', 'author_name' => 'Lena Hoffmann', 'author_title' => 'Freelance developer (example)'],
                ],
            ]),
            ShowcaseBlocks::block('desiderio_ctabanner', [
                'header' => 'Build your next TYPO3 site with Desiderio',
                'description' => 'This demo site, with 10 themed chapters and 244 elements, was created with one command. Install Desiderio for free, or book the setup for €890.',
                'cta_text' => 'Get started free',
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
            'abstract' => 'All 15 Desiderio theme presets side by side. Each card is shown in its own preset, with its colours, fonts, corner radius, density, focus ring and icons.',
            'description' => 'Compare the 15 Desiderio theme presets: colours, fonts, corner radius and density. Switch them per site or per page tree, without a rebuild.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'A new theme needs no rebuild',
                    'content' => '<p>A preset is a set of OKLCH design tokens. Selecting one sets a single <code>data-shadcn-preset</code> attribute on the body tag. Every element then takes its colours from that preset, with no Tailwind build, no deployment and no change to content. The same tokens also work on a single card, which is how this page shows all 15 presets at once.</p><p>Every page has this field too. Set <strong>Theme preset</strong> in the page properties, and all pages below follow. A campaign site, a product section and the blog can each have their own look in one installation.</p>',
                    'variant' => 'muted',
                    'alignment' => 'left',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Design your own preset',
                    'description' => 'Choose colours, fonts, radius and a style on the shadcn/ui create page. Paste the generated tokens into shadcn-theme.css as the custom preset.',
                    'cta_text' => 'Design a preset',
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
            'abstract' => 'A test page with 100 rich-text blocks in a fixed order. Every block type follows every other at least once, with inline styles from the editor.',
            'description' => 'Visual regression test for Desiderio rich text. 100 fixed blocks combine p, h2–h6, ul, ol and blockquote in every order, with short, long, linked, bold and italic text.',
            'parentSlug' => 'content-types',
            'pageTsConfig' => 'RTE.config.tt_content.bodytext.types.text.preset = desiderio',
            'hideInNav' => true,
            'content' => [
                ShowcaseBlocks::block('text', [
                    'header' => 'Every pair of block types in 100 blocks',
                    'header_layout' => 2,
                    'subheader' => 'Each ordered pair of the nine main block types appears at least once. The order is fixed, so screenshots stay comparable between releases.',
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
            'abstract' => 'Every main technical feature of Desiderio in one list: Fluid 5.3 components, translations, the theme engine, CSS, integrations and quality checks.',
            'description' => 'How Desiderio works: typed Fluid 5.3 components, a runtime theme engine, Content Blocks 2.2 and PHPStan level 8. All of it is on GitHub.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For developers',
                    'header' => 'Every claim here can be checked on GitHub',
                    'subheadline' => 'This page explains how the features on the home page work. The full source code is public.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contentgrid', [
                    'header' => 'Components, themes and quality checks',
                    'columns' => '3',
                    'items' => [
                        ['title' => 'Fluid 5.3 components', 'content' => '62 typed Fluid 5.3 components make up the d: namespace. Arguments are declared with f:argument, so a wrong type fails at render time, not in production.', 'link' => ShowcaseBlocks::REPO_URL],
                        ['title' => 'Runtime theme engine', 'content' => '15 presets of OKLCH design tokens switch per site or per page tree, without a rebuild. A contrast solver meets WCAG 2.2 in light and dark mode.', 'link' => ''],
                        ['title' => 'Measured quality', 'content' => 'PHPStan level 8, unit and functional tests on PHP 8.4 and 8.5, and a template audit. CI fails when the generated CSS no longer matches the templates.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Credits',
                    'header' => 'Desiderio builds on two TYPO3 projects',
                    'subheadline' => 'Desiderio is a design system on top of Fluid and Content Blocks. We thank the people who built them.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contentgrid', [
                    'header' => 'Fluid and Content Blocks',
                    'columns' => '2',
                    'items' => [
                        ['title' => 'Fluid 5 — thank you, Simon Praetorius', 'content' => 'All 62 Desiderio components are built on Fluid 5. Simon Praetorius turned Fluid into a component-based template engine with typed arguments, which catch errors at render time.', 'link' => 'https://github.com/s2b'],
                        ['title' => 'Content Blocks — thank you, Nikita Hovratov', 'content' => 'All 244 Desiderio elements are Content Blocks. Nikita Hovratov made it possible to define an element in one schema file, with database columns and a backend preview created from it.', 'link' => 'https://github.com/nhovratov'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'More credits',
                    'header' => 'The tools and extensions we build on',
                    'subheadline' => 'The toolchain and the integrations around Fluid and Content Blocks are other people\'s work too. Thank you to each of them.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Toolchain, integrations and the AI layer',
                    'eyebrow' => 'Third-party projects',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'shadcn/ui — shadcn', 'description' => 'Components, design tokens, the OKLCH theme model and the preset format all come from shadcn/ui. Desiderio follows it one to one. Thank you.', 'link' => 'https://ui.shadcn.com'],
                        ['icon' => 'zap', 'title' => 'Tailwind CSS — Tailwind Labs', 'description' => 'Tailwind v4 compiles the whole CSS bundle with @source scanning and cascade layers. Nothing runs in the browser. Thank you, Tailwind Labs.', 'link' => 'https://tailwindcss.com'],
                        ['icon' => 'message-circle', 'title' => 'The AI layer — Netresearch', 'description' => 'nr_llm, nr_mcp_agent, nr_vault and t3_cowriter add LLM access, a backend agent, an encrypted vault and an AI cowriter. Thank you, Netresearch DTT GmbH.', 'link' => 'https://github.com/netresearch'],
                        ['icon' => 'book-open', 'title' => 'The integration extensions', 'description' => 'The template sets cover Powermail by in2code, News by Georg Ringer, Blog by TYPO3 GmbH and Apache Solr for TYPO3 by dkd. Thank you all.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Friendly Captcha — Studio Mitte', 'description' => 'Bot protection without tracking, based on proof of work. Studio Mitte made it available for TYPO3. Thank you, and thanks to Friendly Captcha for the service.', 'link' => 'https://friendlycaptcha.com'],
                        ['icon' => 'monitor', 'title' => 'Prism and highlight.js', 'description' => 'Prism, by Lea Verou and contributors, highlights code blocks. highlight.js detects the language of snippets without a label. Thank you to both projects.', 'link' => 'https://prismjs.com'],
                        ['icon' => 'settings', 'title' => 'Alpine.js — Caleb Porzio', 'description' => 'Early versions used Alpine.js for accordions, tabs and alerts. A few lines of plain JavaScript now do the same job. Thank you, Caleb Porzio.', 'link' => 'https://alpinejs.dev'],
                        ['icon' => 'file', 'title' => 'Fonts and icon sets', 'description' => 'Geist by Vercel, Inter by Rasmus Andersson and JetBrains Mono by JetBrains. Icons come from Lucide, Tabler, HugeIcons, Phosphor and Remix. Thank you to every maintainer.', 'link' => ''],
                        ['icon' => 'database', 'title' => 'TYPO3 — the community', 'description' => 'TYPO3 provides the permissions, records, workspaces, translations and file handling that everything above builds on. Thank you to the TYPO3 community.', 'link' => 'https://typo3.org'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'All technical features',
                    'allow_multiple' => 1,
                    'items' => [
                        [
                            'title' => 'Fluid 5.3: typed components and slots',
                            'content' => '<p>Components declare typed <code>f:argument</code> contracts and are combined through <code>f:slot</code>.</p><ul><li><strong>Typed arguments</strong>: string, bool and object, with defaults and optional values. A wrong type fails at render time.</li><li><strong>Own namespace</strong>: d:atom.button, d:molecule.card and d:layout.section, registered through a ComponentCollection.</li><li><strong>ICU built in</strong>: plurals and dates come out right, for example "Page 3 of 12".</li></ul>',
                            'open_by_default' => 1,
                        ],
                        [
                            'title' => 'Translations: XLIFF 2.0 in English and German',
                            'content' => '<p>Every visible string goes through <code>f:translate</code> with XLIFF 2.0 files.</p><ul><li><strong>Screen-reader text too</strong>: labels for carousel controls, close buttons and pagination are translated.</li><li><strong>Languages</strong>: English and German are complete. Element labels also exist in French, Spanish and Italian.</li><li><strong>Stable keys</strong>: switching icon libraries or themes never changes stored records.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Content Blocks 2.2: elements with previews',
                            'content' => '<p>All 244 elements are TYPO3 Content Blocks.</p><ul><li><strong>One schema per element</strong>: a config.yaml file, with the database columns created for you.</li><li><strong>Backend previews</strong>: every element has one. Collections use child records with explicit tables.</li><li><strong>Demo content</strong>: every element has a fixture, so one command builds this whole site.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Theme engine: OKLCH tokens and per-page presets',
                            'content' => '<p>15 presets (5 from ui.shadcn.com and 10 of our own) define the look as OKLCH custom properties.</p><ul><li><strong>Full token set</strong>: accent, radius, typography, density, focus ring width and shadows.</li><li><strong>Checked contrast</strong>: the generator meets WCAG 2.2 (4.5:1 for text, 3:1 for controls) and refuses CSS that fails. A unit test checks the bundle again.</li><li><strong>Per page</strong>: subpages inherit the preset of their parent page.</li><li><strong>Runtime switches</strong>: dark mode, 5 icon libraries, density, radius and font.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'CSS: Tailwind v4, container queries and BEM',
                            'content' => '<p>Tailwind v4 with <code>@source</code> scanning, cascade layers and tw-animate.</p><ul><li><strong>BEM per element</strong>: each element has its own BEM file, joined by a manifest.</li><li><strong>Container queries</strong>: elements respond to their own width, not only to the viewport.</li><li><strong>No runtime cost</strong>: no CSS-in-JS, no framework and no hydration.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Integrations: News, Powermail, Solr, Blog and forms',
                            'content' => '<p>Styled template sets for georgringer/news, in2code/powermail, Apache Solr and t3g/blog.</p><ul><li><strong>Forms</strong>: eight Form Framework forms with a Brevo finisher and double opt-in.</li><li><strong>Friendly Captcha</strong>: active in production and skipped in Development. A switch turns it on in DDEV.</li><li><strong>Visual Editor</strong>: inline editing works across the whole package.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Seeding and CLI: repeatable demo content',
                            'content' => '<p>Three commands, each safe to run again.</p><ul><li><strong>desiderio:styleguide:seed</strong>: this site.</li><li><strong>desiderio:starter:seed</strong>: a complete corporate starter site.</li><li><strong>desiderio:blog:seed-pages</strong>: applies the Desiderio page layouts to an existing Blog setup.</li></ul><p>A new run soft-deletes the previous content. None of them runs in a workspace or in Production without a flag.</p>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'Security and platform requirements',
                            'content' => '<p>Desiderio needs TYPO3 v14.3 and PHP 8.4 or newer.</p><ul><li><strong>Safe queries</strong>: strict types and QueryBuilder with named parameters.</li><li><strong>Ready for CSP</strong>: assets are rendered with nonces.</li><li><strong>Safe defaults</strong>: a middleware logs and blocks the captcha bypass in production. The seeder only writes columns that exist in the schema.</li></ul>',
                            'open_by_default' => 0,
                        ],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install in two commands',
                    'language' => 'Bash',
                    'filename' => 'install.sh',
                    'code' => "composer require webconsulting/desiderio\nvendor/bin/typo3 desiderio:styleguide:seed\n# then pick a theme preset in the site settings",
                ]),
                ShowcaseBlocks::block('desiderio_definitionlist', [
                    'header' => 'Five Desiderio terms',
                    'shadcn_layout' => 'default',
                    'items' => [
                        ['term' => 'Preset', 'definition' => 'A complete set of design decisions (colours, radius, fonts and density), stored as OKLCH tokens and switched at runtime. Design one on the shadcn/ui create page or pick a house preset.'],
                        ['term' => 'Token', 'definition' => 'A named CSS custom property, such as --primary or --radius, that every component uses. Change the token and every component follows.'],
                        ['term' => 'Atom, molecule, layout, organism', 'definition' => 'The four layers of Fluid 5 components (17 + 37 + 4 + 4). All 244 content elements are built from them, and they use only design tokens.'],
                        ['term' => 'Content Block', 'definition' => 'A TYPO3 content element defined in one schema file, with database columns and a backend preview created from it. Desiderio ships 244 of them.'],
                        ['term' => 'ICU MessageFormat', 'definition' => 'The translation syntax for plurals and dates. It makes "1 result" and "12 results" correct in every included language.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Install it yourself or book the setup',
                    'description' => 'The package is free. If you prefer, we install and configure it on your TYPO3 site for €890. Brand adaptation starts at €1,990.',
                    'cta_text' => 'Get started free',
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
            'abstract' => 'All 244 Desiderio content elements in 10 groups. Each one has a backend preview, demo content and accessible markup.',
            'description' => '244 content elements in 10 groups, from heroes and navigation to charts, forms and footers. Each group is shown live in its own theme preset.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Content types',
                    'header' => 'All 244 elements, sorted into 10 groups',
                    'subheadline' => 'Every element is finished and checked, with a backend preview, demo content and accessible markup. Choose a group by what you need to build.',
                    'variant' => 'left',
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'eyebrow' => 'Groups',
                    'header' => 'Find an element by what you need',
                    'subheadline' => 'Each group page shows all of its elements live, in its own theme preset.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'Heroes and page intros', 'description' => '21 heroes, including split, video, countdown and stats layouts for the first screen.', 'link' => '{{page:chapter-hero}}'],
                        ['icon' => 'menu', 'title' => 'Navigation', 'description' => '23 navigation bars, mega menus, breadcrumbs and pagination patterns.', 'link' => '{{page:chapter-navigation}}'],
                        ['icon' => 'book-open', 'title' => 'Content and editorial', 'description' => '24 layouts for articles, with text, media, quotes, tabs and timelines.', 'link' => '{{page:chapter-content}}'],
                        ['icon' => 'blocks', 'title' => 'Features and benefits', 'description' => '25 grids, bento layouts and comparisons to explain a product or service.', 'link' => '{{page:chapter-features}}'],
                        ['icon' => 'tag', 'title' => 'Plans and pricing', 'description' => '25 pricing tables, monthly and yearly toggles and calculators.', 'link' => '{{page:chapter-pricing}}'],
                        ['icon' => 'shield-check', 'title' => 'Trust and social proof', 'description' => '25 testimonial walls, logo clouds and case studies.', 'link' => '{{page:chapter-social-proof}}'],
                        ['icon' => 'users', 'title' => 'People and team', 'description' => '24 team grids, organisation charts and founder stories.', 'link' => '{{page:chapter-team}}'],
                        ['icon' => 'chart', 'title' => 'Data and dashboards', 'description' => '29 KPI cards, charts and status boards, with nine chart types.', 'link' => '{{page:chapter-data}}'],
                        ['icon' => 'send', 'title' => 'Leads and conversion', 'description' => '25 forms, calls to action and lead magnets. Every form is a real TYPO3 form.', 'link' => '{{page:chapter-conversion}}'],
                        ['icon' => 'layers', 'title' => 'Footers and utility areas', 'description' => '23 footers, cookie banners and legal sections.', 'link' => '{{page:chapter-footer}}'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'All 244 elements are in the free package',
                    'description' => '244 elements, 15 theme presets and all integrations, under GPL-2.0. Paid plans add support, not features.',
                    'cta_text' => 'Get started free',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
