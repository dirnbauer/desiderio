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
                'hero_image' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Editing a page in the Visual Editor', 'The TYPO3 Visual Editor with a Desiderio hero element selected for inline editing.'),
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
                        'image' => ShowcaseBlocks::screenshot('backend-page-properties-theme.png', 'Theme preset in the page properties', 'TYPO3 page properties with the Desiderio theme preset field.'),
                        'link' => '{{page:technical-features}}',
                    ],
                    [
                        'title' => '3 · Give each page tree its own look',
                        'description' => 'Any page can have its own preset, and its subpages inherit it. This site shows it: each chapter page uses a different theme.',
                        'image' => ShowcaseBlocks::screenshot('frontend-pricing-midnight-dark.png', 'Pricing page in the Midnight preset', 'A Desiderio pricing page in the dark Midnight theme preset.'),
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
                        'image' => ShowcaseBlocks::screenshot('backend-page-module-hero.png', 'Seeded page in the page module', 'The TYPO3 page module with a seeded Desiderio hero element and its preview.'),
                    ],
                    [
                        'title' => 'Pick a theme preset',
                        'content' => 'Choose one of 15 presets in the site settings, or paste your own preset code.',
                        'image' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset in the site settings', 'TYPO3 site settings with the Desiderio theme preset list open.'),
                    ],
                    [
                        'title' => 'Publish',
                        'content' => 'Editors build pages in the content wizard, check the backend preview and edit inline in the Visual Editor.',
                        'image' => ShowcaseBlocks::screenshot('frontend-hero-lagoon.png', 'Published Desiderio site', 'The Desiderio demo site in the Lagoon theme preset.'),
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
                    ['title' => 'Parallax hero', 'description' => 'A hero that moves as you scroll. One of 21 hero layouts.', 'link' => '{{page:chapter-hero}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-parallax-hero.png', 'Parallax hero element', 'The Desiderio parallax hero element in the Forest theme preset.')],
                    ['title' => 'Bento feature grid', 'description' => 'Feature cards in mixed sizes, from the features group.', 'link' => '{{page:chapter-features}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-bento-features.png', 'Bento feature grid element', 'The Desiderio bento feature grid with feature tiles in mixed sizes.')],
                    ['title' => 'Toggle pricing', 'description' => 'Monthly and yearly prices with a highlighted plan.', 'link' => '{{page:chapter-pricing}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-toggle-pricing.png', 'Toggle pricing element', 'The Desiderio toggle pricing element with a monthly and yearly switch.')],
                    ['title' => 'Testimonial wall', 'description' => 'Many short quotes in a masonry grid.', 'link' => '{{page:chapter-social-proof}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-testimonial-wall.png', 'Testimonial wall element', 'The Desiderio testimonial wall with quotes in a masonry grid.')],
                    ['title' => 'Demo request form', 'description' => 'A lead form that renders a real TYPO3 form.', 'link' => '{{page:chapter-conversion}}', 'image' => ShowcaseBlocks::screenshot('frontend-gallery-demo-request.png', 'Demo request element', 'The Desiderio demo request element with a TYPO3 Form Framework form.')],
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
                    ['title' => 'Measured quality', 'description_text' => 'PHPStan at its strictest level, unit and functional tests on PHP 8.4 and 8.5, and a template audit with zero findings.'],
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
