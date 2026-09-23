<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Illustrative success stories. The parent page is the blog list and every
 * story carries blog metadata, so they seed as doktype 137 posts when
 * EXT:blog is installed.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseSuccessStories
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [
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
            'abstract' => 'Fifteen example stories show what Desiderio changes for TYPO3 teams. The organisations, people and figures in them are invented.',
            'description' => 'Fifteen example stories, from an AI lab to a toy maker, show how Desiderio helps TYPO3 teams run many brands and publish faster.',
            'parentSlug' => null,
            'subtitle' => 'Fifteen example stories about organisations that run their websites on TYPO3 with Desiderio. Each shows one before and after.',
            'blogList' => true,
            // The seeder prepends a paginated blog_posts list plugin when
            // EXT:blog is installed; the highlight stays below the list.
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'What the stories have in common',
                    'content' => '<p><strong>The stories are fictional, but every feature in them is real.</strong></p><ul><li><strong>Many brands.</strong> One installation, with a theme preset per page tree.</li><li><strong>No tickets.</strong> Editors publish without a developer.</li><li><strong>Easy to approve.</strong> Open-source licence and self-hosting.</li></ul><p>Put your clients in place of the invented organisations, and the points still apply.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => ShowcaseBlocks::REPO_URL,
                    'link_text' => 'View on GitHub',
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
            'abstract' => 'Example story: an AI lab publishes papers, model cards and policy posts every week. A paper page now goes live 57 minutes after the paper is final.',
            'description' => 'Example story: an AI lab runs its research blog on TYPO3. Editors preview and publish papers themselves, with a theme preset per product line.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'AI & Research'],
                'tags' => ['AI lab', 'AI research', 'Editorial workflow', 'Self-hosting'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Example story: papers live in under an hour',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why the lab chose TYPO3 and Desiderio',
                    'content' => '<p><strong>Before:</strong> each paper page waited for a developer and a deployment. <strong>After:</strong> researchers publish it themselves in 57 minutes.</p><ul><li><strong>Ready elements.</strong> Article hero, FAQ and charts, checked in the preview.</li><li><strong>A theme per team.</strong> Lagoon for safety research, Midnight for products.</li><li><strong>Self-hosted.</strong> A GPL design system on the lab\'s own servers.</li></ul><p>The lab is invented. The features are real.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See the features',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Inline editing in the Visual Editor', 'The TYPO3 Visual Editor with a Desiderio element open for inline editing.'),
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '57 min', 'label' => 'Paper to published page', 'description_text' => 'An invented median, built from existing elements.'],
                        ['value' => '3', 'label' => 'Brands, one installation', 'description_text' => 'Research, product and policy, each with its own preset.'],
                        ['value' => '0', 'label' => 'Deployments per paper', 'description_text' => 'The web team reviews design tokens, not tickets.'],
                        ['value' => '100%', 'label' => 'Self-hosted', 'description_text' => 'Open-source CMS and design system on the lab\'s servers.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We study complex systems for a living. With design tokens, our website is no longer one of them.',
                    'author' => 'Head of Web Platform',
                    'role' => 'Example story, invented quote',
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Use the same setup for your team',
                    'description' => 'Everything here except the lab is free: article elements, per-page themes, backend previews and one-command seeding.',
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
    private static function successStorySpaceCompanyPage(): array
    {
        return [
            'title' => 'What if a space company ran on TYPO3?',
            'navTitle' => 'A space company',
            'slug' => '/success-stories/a-space-company',
            'abstract' => 'Example story: a space company launches rockets every week. Each mission now gets its own page tree, theme preset and countdown, with no rebuild.',
            'description' => 'Example story: a space company builds a TYPO3 microsite per mission, with its own preset, countdown hero and status board, and no rebuild.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 10:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Aerospace'],
                'tags' => ['Space company', 'Mission microsites', 'Per-page themes', 'Countdown hero'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Example story: a microsite for every mission',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Why the company chose TYPO3 and Desiderio',
                    'content' => '<p><strong>Before:</strong> a called-off launch meant a developer rebuilt the mission site. <strong>After:</strong> an editor moves the countdown and republishes in 45 minutes.</p><ul><li><strong>A theme per mission.</strong> Ember for flagship missions, Marine for crewed flights, Midnight for night launches.</li><li><strong>Mission elements.</strong> Countdown heroes, fleet stats and a launch weather board.</li><li><strong>No redeployment.</strong> Editors change dates and status themselves.</li></ul><p>The company is invented. The features are real.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See the features',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('frontend-pricing-midnight-dark.png', 'Page in the Midnight preset', 'A Desiderio page in the dark Midnight theme preset.'),
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '14', 'label' => 'Mission microsites live', 'description_text' => 'One installation, 14 themed page trees.'],
                        ['value' => '45 min', 'label' => 'Cancelled launch to updated site', 'description_text' => 'Countdown, status board and hero, updated by an editor.'],
                        ['value' => '0', 'label' => 'Rebuilds between launches', 'description_text' => 'Presets switch at runtime, without CI.'],
                        ['value' => '99.9%', 'label' => 'Uptime target met', 'description_text' => 'Server-rendered pages without a JavaScript framework.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We reuse our rockets instead of building new ones. Now we treat our website the same way.',
                    'author' => 'Director of Mission Communications',
                    'role' => 'Example story, invented quote',
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Countdowns and status boards, ready to use',
                    'description' => 'Countdown heroes, status boards, themes per page tree and a one-command demo seeder, all in the free package.',
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
    private static function successStoryAiResearchLabPage(): array
    {
        return [
            'title' => 'What if an AI research lab used TYPO3?',
            'navTitle' => 'An AI research lab',
            'slug' => '/success-stories/an-ai-research-lab',
            'abstract' => 'Example story: an AI research lab ships a product line every quarter. Its launch page now goes live 2 hours after the keynote, without a rebuild.',
            'description' => 'Example story: an AI research lab launches products on TYPO3, with a theme preset per product family and pricing pages its editors update.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-01 12:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'AI & Research'],
                'tags' => ['AI research lab', 'Product launches', 'Pricing pages', 'Theme presets'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Example story: launch pages on keynote day',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why the lab chose TYPO3 and Desiderio',
                    'content' => '<p><strong>Before:</strong> each product launch meant a website rebuild. <strong>After:</strong> the launch page is live 2 hours after the keynote.</p><ul><li><strong>A theme per product family.</strong> Calm neutrals for the platform, warmer colours for consumer apps.</li><li><strong>Ready pricing elements.</strong> Pricing tables, comparisons and usage calculators.</li><li><strong>Quick price changes.</strong> Marketing updates plans right after a pricing meeting.</li><li><strong>No lock-in.</strong> A GPL licence and a composer.json file.</li></ul><p>The lab is invented. The features are real.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See pricing elements',
                    'button_link' => '{{page:chapter-pricing}}',
                    'media' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset in the site settings', 'TYPO3 site settings with the Desiderio theme preset list.'),
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example, except the last one. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '6', 'label' => 'Product families, one installation', 'description_text' => 'Each with its own preset and page tree.'],
                        ['value' => '2 h', 'label' => 'Keynote to live launch page', 'description_text' => 'Hero, features, pricing and FAQ from existing elements.'],
                        ['value' => '0', 'label' => 'Rebuilds per product launch', 'description_text' => 'A new look is a setting, not a rebuild.'],
                        ['value' => '244', 'label' => 'Content elements available', 'description_text' => 'The one figure here that is not invented.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We measure everything, including our website process. Changing the theme beat a rebuild on every measure.',
                    'author' => 'Head of Web Experience',
                    'role' => 'Example story, invented quote',
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Build launch pages from ready elements',
                    'description' => 'Pricing tables, comparisons, heroes and FAQs: finished, themed and free to use.',
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
    private static function successStorySpaceAgencyPage(): array
    {
        return [
            'title' => 'What if a space agency ran on TYPO3?',
            'navTitle' => 'A space agency',
            'slug' => '/success-stories/a-space-agency',
            'abstract' => 'Example story: a national space agency moves six decades of mission pages onto one TYPO3 installation. It now meets its own accessibility rules.',
            'description' => 'Example story: a space agency moves its mission archive to TYPO3, with accessible elements, a page tree per mission and editors who publish themselves.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-03 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Aerospace'],
                'tags' => ['Space agency', 'Mission archives', 'Accessibility', 'Open data'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: 60 years of missions, one site',
                    'content' => '<p><strong>Before:</strong> each mission had its own microsite. <strong>After:</strong> one installation holds them all, and a press kit is live in a day.</p><ul><li><strong>A theme per era.</strong> Beige for early missions, Midnight for current ones.</li><li><strong>Ready elements.</strong> Timelines, stats and galleries.</li><li><strong>Ready for audits.</strong> Section 508 reviewers approve the markup.</li></ul><p>The agency is invented. The contrast check on every preset is real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '60+', 'label' => 'Mission page trees moved', 'description_text' => 'Each programme keeps a preset that suits its era.'],
                        ['value' => '508', 'label' => 'Section 508 audit passed', 'description_text' => 'Passed the invented federal audit at the first attempt.'],
                        ['value' => '1 day', 'label' => 'Press kit to live page', 'description_text' => 'Galleries and stat boards, without contractors.'],
                        ['value' => '0', 'label' => 'Vendor lock-ins', 'description_text' => 'GPL licence and self-hosting.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Updating a mission page used to take longer than the flight to Mars. Now the page is live on landing day.',
                    'author' => 'Web Programme Manager',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a streaming platform builds a themed landing page for every original series, all from one installation and with no rebuilds.',
            'description' => 'Example story: a streaming platform runs its series pages on TYPO3, with a theme preset per show and countdown heroes for each premiere.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-07 10:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Streaming platform', 'Landing pages', 'Theme presets', 'Editor velocity'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: a landing page per series',
                    'content' => '<p><strong>Before:</strong> series pages needed a separate static-site generator. <strong>After:</strong> a teaser page is live 3 hours after a series is approved.</p><ul><li><strong>A look per show.</strong> Midnight for the thriller, Citrus for the baking show.</li><li><strong>Ready elements.</strong> Countdown heroes for premieres, an FAQ for the spoiler policy.</li><li><strong>One tool less.</strong> The static-site generator is retired.</li></ul><p>The platform is invented. The features are real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '80', 'label' => 'Series pages, one installation', 'description_text' => 'Each with the show\'s own preset.'],
                        ['value' => '3 h', 'label' => 'Approval to teaser page', 'description_text' => 'Hero, trailer and countdown from existing elements.'],
                        ['value' => '0', 'label' => 'Rebuilds between premieres', 'description_text' => 'Presets switch at runtime.'],
                        ['value' => '2', 'label' => 'Colour modes included', 'description_text' => 'Light and dark, both checked for WCAG contrast.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We used to A/B test thumbnails. Now we also test theme presets, with the same content.',
                    'author' => 'Director of Title Marketing',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a game studio gives every game series its own look. All series now share one TYPO3 installation, with a preset each.',
            'description' => 'Example story: a game studio runs its game series pages on TYPO3, with a theme preset per series and launch pages built from ready elements.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-11 09:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Game studio', 'Product launches', 'Brand worlds', 'Per-page themes'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: 12 game worlds, one site',
                    'content' => '<p><strong>Before:</strong> each game series had its own site and stylesheets. <strong>After:</strong> all series share one installation, and launch pages go live in 4 hours.</p><ul><li><strong>A preset per series.</strong> Warm reds, sage greens or plain neutrals.</li><li><strong>Ready launch parts.</strong> Countdown heroes, feature grids and pricing tables.</li><li><strong>Easy for legal.</strong> The GPL licence keeps procurement simple.</li></ul><p>The studio and its games are invented. The elements are real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '12', 'label' => 'Game worlds', 'description_text' => 'Twelve page trees, each with its own preset.'],
                        ['value' => '4 h', 'label' => 'Announcement to launch page', 'description_text' => 'Published while the announcement stream still runs.'],
                        ['value' => '0', 'label' => 'Hand-written stylesheets', 'description_text' => 'Each world is a set of design tokens.'],
                        ['value' => '100%', 'label' => 'Editors in the backend', 'description_text' => 'Every hero is checked in the preview first.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Each of our games has its own rules and look. They still share one CMS: the tokens change, the workflow stays.',
                    'author' => 'Head of Web',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a toy maker launches new sets every week. It now builds campaign pages the way it builds toys, from documented parts.',
            'description' => 'Example story: a toy maker builds each campaign page on TYPO3 in one afternoon, from Desiderio elements with a preset per product line.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-15 11:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Commerce'],
                'tags' => ['Toy maker', 'Campaign pages', 'Brand worlds', 'Editor velocity'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: campaign pages in an afternoon',
                    'content' => '<p><strong>Before:</strong> campaign pages waited for the web team. <strong>After:</strong> the campaign team builds one in an afternoon from <strong>244 ready elements.</strong></p><ul><li><strong>Parts that fit one way.</strong> Typed arguments catch wrong use.</li><li><strong>Checked first.</strong> Every page is reviewed in the preview.</li><li><strong>A preset per range.</strong> Midnight for space sets, Forest for botanical sets.</li></ul><p>The toy maker is invented. The elements are real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '244', 'label' => 'Elements in the library', 'description_text' => 'The real Desiderio library, reused in every campaign.'],
                        ['value' => '1', 'label' => 'Afternoon per campaign page', 'description_text' => 'Built, previewed and published.'],
                        ['value' => '18', 'label' => 'Product-line themes', 'description_text' => 'One preset per product line.'],
                        ['value' => '0', 'label' => 'Components used wrongly', 'description_text' => 'Typed f:argument contracts catch wrong input.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our toys are built from parts that fit together. Now our campaign pages are too, and they no longer wait a quarter.',
                    'author' => 'Digital Campaigns Lead',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a furniture retailer publishes its catalogue in dozens of languages. Each country team now builds its own pages from the same elements.',
            'description' => 'Example story: a furniture retailer runs 42 country sites on TYPO3. Plurals and dates are right in every language, and local teams build the pages.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-19 10:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Commerce'],
                'tags' => ['Furniture retailer', 'Multilanguage', 'Catalogue pages', 'Editor velocity'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: one toolkit for 42 countries',
                    'content' => '<p><strong>Before:</strong> country teams waited for an agency to build local pages. <strong>After:</strong> they build them themselves, in their own language.</p><ul><li><strong>Shared parts.</strong> Heroes, product grids and FAQ elements.</li><li><strong>Correct in every language.</strong> XLIFF and ICU MessageFormat get plurals and dates right.</li><li><strong>One update, not forty.</strong> A fix ships once.</li></ul><p>The retailer is invented. The translation setup is real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '42', 'label' => 'Country sites, one toolkit', 'description_text' => 'The same elements, assembled locally.'],
                        ['value' => '100%', 'label' => 'Text through XLIFF', 'description_text' => 'Screen-reader labels included. Nothing is hard-coded.'],
                        ['value' => '1', 'label' => 'Update for all languages', 'description_text' => 'Every market gets each fix.'],
                        ['value' => '0', 'label' => 'Editor manuals needed', 'description_text' => 'Backend previews show each element first.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We sell furniture as parts with instructions. Now our website works the same way.',
                    'author' => 'Global Web Coordinator',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a music-streaming service publishes an artist feature every day. Each takes 45 minutes, built from ready elements in dark mode.',
            'description' => 'Example story: a music service builds artist features on TYPO3 from ready elements, in dark mode, with a year-end campaign that needs no rebuild.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-23 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Music service', 'Editorial workflow', 'Dark mode', 'Theme presets'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: a new artist feature every day',
                    'content' => '<p><strong>Before:</strong> the year-end campaign was a three-month project. <strong>After:</strong> it is one preset switch plus seeded pages.</p><ul><li><strong>Built, not coded.</strong> Quote, stat board and gallery elements.</li><li><strong>Checked first.</strong> Every feature is reviewed in the backend preview.</li><li><strong>Dark mode first.</strong> Every preset has light and dark modes with checked contrast.</li></ul><p>The service is invented. The features are real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '365', 'label' => 'Editorial pages a year', 'description_text' => 'One artist feature per day.'],
                        ['value' => '2', 'label' => 'Colour modes, both complete', 'description_text' => 'Dark mode has its own checked contrast.'],
                        ['value' => '45 min', 'label' => 'Brief to published feature', 'description_text' => 'Quote, stats, gallery and embed from ready elements.'],
                        ['value' => '1', 'label' => 'Preset switch at year end', 'description_text' => 'The campaign is a setting, not a deployment.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our app has put dark mode first for years. Now our website does too, and the contrast check catches what we miss.',
                    'author' => 'Editorial Platform Lead',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a payments company with strict engineering rules checks its marketing site. It finds typed components, audited templates and no drift.',
            'description' => 'Example story: a payments company runs its marketing pages on TYPO3, with typed Fluid components, a clean template audit and pricing tables editors update.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-04-27 11:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Commerce'],
                'tags' => ['Payments company', 'Documentation', 'Pricing pages', 'Self-hosting'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: new prices live in 15 minutes',
                    'content' => '<p><strong>Before:</strong> price changes needed a pull request and a deployment. <strong>After:</strong> the billing team edits prices in the backend.</p><ul><li><strong>Typed contracts.</strong> Every component declares <code>f:argument</code> types.</li><li><strong>Zero findings.</strong> A template audit on every commit, with PHPStan level 8.</li><li><strong>Self-hosted.</strong> Open source on the company\'s own infrastructure.</li></ul><p>The company is invented. The rigour is real, and the repository shows it.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example, except the tests. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '0', 'label' => 'Audit findings allowed', 'description_text' => 'The real template audit checks every commit.'],
                        ['value' => '15 min', 'label' => 'Pricing meeting to live table', 'description_text' => 'Plans are records, so the billing team edits them.'],
                        ['value' => '170+', 'label' => 'Tests on the stack', 'description_text' => 'Unit and functional, on PHP 8.4 and 8.5. This figure is real.'],
                        ['value' => '100%', 'label' => 'Infrastructure in-house', 'description_text' => 'Self-hosted open source passes compliance review.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We measure API reliability in nines. Our marketing site used to fall short, and typed components closed the gap.',
                    'author' => 'Head of Web Infrastructure',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a language-learning app teaches forty languages. Its website now speaks all of them, with correct plurals, dates and screen-reader labels.',
            'description' => 'Example story: a language app runs its course pages on TYPO3, with ICU MessageFormat for every language and streak counters from ready elements.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-01 09:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Education'],
                'tags' => ['Language app', 'Multilanguage', 'Gamification', 'Editor velocity'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: one website in forty languages',
                    'content' => '<p><strong>Before:</strong> a Japanese course for Spanish speakers broke the old translation layer. <strong>After:</strong> plurals, dates and labels are right in every language.</p><ul><li><strong>Real translation support.</strong> XLIFF 2.0 and ICU MessageFormat handle plural rules.</li><li><strong>Reusable pages.</strong> Every course uses the same elements.</li><li><strong>Ready metrics.</strong> The homepage streak counter is a standard element.</li></ul><p>The app is invented. The translation setup is real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '40+', 'label' => 'Course languages', 'description_text' => 'Each with correct plurals and date formats.'],
                        ['value' => '100%', 'label' => 'ARIA labels translated', 'description_text' => 'Through the same XLIFF files.'],
                        ['value' => '365', 'label' => 'Days in a row with new content', 'description_text' => 'A new page every day for a year.'],
                        ['value' => '0', 'label' => 'Hard-coded strings', 'description_text' => 'Every label goes through f:translate.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We turned language learning into a game. Now our editors compete for the cleanest backend preview.',
                    'author' => 'Web Localisation Lead',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: a nonprofit encyclopedia treats its fundraising pages like its articles: open source, accessible and free of vendor lock-in.',
            'description' => 'Example story: a nonprofit encyclopedia runs its campaign pages on TYPO3 and Desiderio, both under the GPL, with donation banners its editors test.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-05 10:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Education'],
                'tags' => ['Encyclopedia', 'Open source', 'Accessibility', 'Self-hosting'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: open source from top to bottom',
                    'content' => '<p><strong>Before:</strong> campaign pages ran on a closed tool that volunteers could not inspect. <strong>After:</strong> a GPL component library runs on a GPL CMS, on the organisation\'s own servers.</p><ul><li><strong>Accessible appeals.</strong> Donation banners with strict contrast.</li><li><strong>Open templates.</strong> The community that edits the articles can review every template.</li><li><strong>Self-hosted.</strong> No vendor between the organisation and its readers.</li></ul><p>The encyclopedia is invented. The matching licences are real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '2', 'label' => 'GPL licences', 'description_text' => 'The CMS and the design system are both GPL.'],
                        ['value' => '100%', 'label' => 'Templates open for review', 'description_text' => 'The community reviews templates like sources.'],
                        ['value' => '4.5:1', 'label' => 'Minimum text contrast', 'description_text' => 'Enforced by the build, not by a PDF.'],
                        ['value' => '0', 'label' => 'Vendors in the stack', 'description_text' => 'Everything is self-hosted.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our reviewers want a source for every claim. The contrast check brings its own proof, so they accepted it quickly.',
                    'author' => 'Web Lead',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: an energy-drink brand runs more events than some sports federations. Each event now gets a themed microsite in 90 minutes.',
            'description' => 'Example story: an energy-drink brand builds event microsites on TYPO3, with a page tree per event and a countdown hero for every start.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-09 11:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Sports'],
                'tags' => ['Energy-drink brand', 'Event microsites', 'Campaign pages', 'Countdown hero'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: an event site in 90 minutes',
                    'content' => '<p><strong>Before:</strong> the events team worked in three content management systems. <strong>After:</strong> one installation per season, and event sites go live in 90 minutes.</p><ul><li><strong>A preset per event.</strong> Ember for the desert rally, Marine for the regatta.</li><li><strong>Race elements.</strong> Countdowns for the start, stat boards for qualifying.</li><li><strong>Fast launches.</strong> The events team publishes without developers.</li></ul><p>The brand is invented. Runtime theme switching is real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '30+', 'label' => 'Event microsites a season', 'description_text' => 'One page tree per event, one installation.'],
                        ['value' => '90 min', 'label' => 'Announcement to live site', 'description_text' => 'Countdown, timeline and ticket button from ready parts.'],
                        ['value' => '0', 'label' => 'Last-minute deployments', 'description_text' => 'Editors publish the pages themselves.'],
                        ['value' => '14', 'label' => 'Presets in use', 'description_text' => '14 of the 15 presets, one look per type of event.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We used to run three systems for our events. Now we run one, and the pages are ready before the first start.',
                    'author' => 'Head of Event Digital',
                    'role' => 'Example story, invented quote',
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
            'title' => 'What if a physics lab ran on TYPO3?',
            'navTitle' => 'A physics lab',
            'slug' => '/success-stories/a-physics-lab',
            'abstract' => 'Example story: a physics research lab replaces PDF press releases with experiment pages. Results appear as accessible charts an hour after the preprint.',
            'description' => 'Example story: a physics lab publishes experiment pages on TYPO3, with accessible chart elements for its data and a theme preset per experiment.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-13 09:00:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Science'],
                'tags' => ['Physics lab', 'Research publishing', 'Open data', 'Accessibility'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: results online within an hour',
                    'content' => '<p><strong>Before:</strong> results went out as PDF press releases. <strong>After:</strong> an outreach page with accessible charts is live an hour after the preprint.</p><ul><li><strong>A preset per experiment.</strong> Each has its own themed page tree.</li><li><strong>Real data markup.</strong> Accessible chart and table elements.</li><li><strong>Ready outreach parts.</strong> Timelines and stat boards.</li></ul><p>Pages render on the server, without a JavaScript framework. The lab is invented.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '17', 'label' => 'Experiment page trees', 'description_text' => 'Each collaboration keeps its own look.'],
                        ['value' => '9', 'label' => 'Accessible chart types', 'description_text' => 'Each chart has a data table for screen readers.'],
                        ['value' => '1 h', 'label' => 'Preprint to outreach page', 'description_text' => 'Published before the preprint server updates.'],
                        ['value' => '0', 'label' => 'JavaScript frameworks', 'description_text' => 'Pages are server-rendered Fluid.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our old templates had more free parameters than our physics models. The new ones are much easier to explain.',
                    'author' => 'Head of Outreach',
                    'role' => 'Example story, invented quote',
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
            'abstract' => 'Example story: an animation studio gives every film its own web look. Each film page follows the storyboard and needs no rebuild.',
            'description' => 'Example story: an animation studio builds film pages on TYPO3, with a theme preset per film, scroll pages from ready elements and a dark mode.',
            'parentSlug' => 'success-stories',
            'blog' => [
                'publishDate' => '2026-05-17 10:30:00',
                'categories' => ['Success stories', 'What-if scenarios', 'Entertainment'],
                'tags' => ['Animation studio', 'Story pages', 'Brand worlds', 'Dark mode'],
            ],
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Example story: a storyboard for every film',
                    'content' => '<p><strong>Before:</strong> film pages were a side project and went live after the trailer. <strong>After:</strong> each page follows the storyboard and is live before the trailer.</p><ul><li><strong>Scene by scene.</strong> Article hero for the opening, text and media for scenes, stats for the box office.</li><li><strong>A preset per film.</strong> Ocean blues, desert ambers or soft pastels.</li><li><strong>Runtime themes.</strong> A new look needs no rebuild.</li></ul><p>The studio is invented. The theme engine is real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'See the features',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'The example in figures',
                    'description' => 'These figures are part of the example. The features behind them are in the free package.',
                    'items' => [
                        ['value' => '28', 'label' => 'Film looks, one installation', 'description_text' => 'Every film keeps its own colours.'],
                        ['value' => '1', 'label' => 'Storyboard per page', 'description_text' => 'Scenes map onto standard elements.'],
                        ['value' => '2', 'label' => 'Colour modes', 'description_text' => 'Dark for the screening room, light for the lobby.'],
                        ['value' => '0', 'label' => 'Render jobs added', 'description_text' => 'Server-rendered Fluid leaves the render farm to the film.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Our old website was a side story nobody followed. Now every film page reads like a storyboard.',
                    'author' => 'Studio Web Producer',
                    'role' => 'Example story, invented quote',
                    'variant' => 'large',
                ]),
            ],
        ];
    }
}
