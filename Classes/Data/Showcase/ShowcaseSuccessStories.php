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
            'abstract' => 'Fifteen clearly illustrative enterprise scenarios that ask one question with a straight face: what would happen if the most ambitious organizations on the planet ran their websites on TYPO3 with Desiderio?',
            'description' => 'What if an AI lab, a space company, or a toy maker ran on TYPO3? Fifteen playful what-if Desiderio showcase scenarios about multi-brand theming and editor velocity.',
            'parentSlug' => null,
            'subtitle' => 'Fifteen what-if scenarios, played straight — what would happen if the most ambitious kinds of organisation ran their whole web presence on TYPO3 and Desiderio?',
            'blogList' => true,
            // The seeder prepends a paginated blog_posts list plugin when
            // EXT:blog is installed; the highlight stays below the list.
            'content' => [
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'Why these scenarios work',
                    'content' => '<p><strong>Swap the famous logos for your clients and every argument still holds.</strong></p><ul><li><strong>Multi-brand</strong> — many sites from one install via per-page themes.</li><li><strong>No tickets</strong> — editors publish without waiting on developers.</li><li><strong>Procurement-proof</strong> — open-source licensing and self-hosting.</li></ul><p>The companies are illustrative guests — the capabilities are shipping today.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => ShowcaseBlocks::REPO_URL,
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
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: research velocity without a web team in the loop',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why our imagined AI lab picks TYPO3 + Desiderio',
                    'content' => '<p>In this scenario the lab\'s researchers write, and the CMS keeps up.</p><ul><li><strong>Fast publishing</strong> — every paper lands as article hero + FAQ + charts, previewed before publish.</li><li><strong>A preset per team</strong> — the safety team runs calm Lagoon, product pages run Midnight.</li><li><strong>Culture-fit</strong> — a GPL design system, self-hosted under the lab\'s own keys.</li></ul><p>The company is invented; every capability ships in Desiderio today.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'The real technical facts',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('backend-visual-editor.png', 'Inline editing in the Visual Editor', 'TYPO3 Visual Editor editing a Desiderio element inline.'),
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics, plausible physics: what a research-heavy site gains when publishing stops depending on deployments.',
                    'items' => [
                        ['value' => '57 min', 'label' => 'Paper to published page', 'description_text' => 'Fictional median — article hero, key-findings FAQ, and charts composed from existing elements.'],
                        ['value' => '3', 'label' => 'Brand worlds, one install', 'description_text' => 'Research, product, and policy subtrees each carry their own theme preset in this scenario.'],
                        ['value' => '0', 'label' => 'Deploys per publication', 'description_text' => 'Editors compose and publish; the imagined web platform team reviews tokens, not tickets.'],
                        ['value' => '100%', 'label' => 'Self-hosted', 'description_text' => 'Open-source CMS, open-source design system, infrastructure under the lab\'s own control.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We interpret neural networks for a living. Our CMS should not be the most mysterious system in the building — with the token layer, at least the website is fully interpretable.',
                    'author' => 'Head of Web Platform',
                    'role' => 'invented persona — no real the AI lab statement',
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Your research team is real. The workflow can be too.',
                    'description' => 'Everything in this story except the company ships in the free package: article elements, per-page themes, backend previews, one-command seeding.',
                    'cta_text' => 'Install Desiderio for free',
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
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: a microsite per mission, a preset per brand',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'Why our imagined space company picks TYPO3 + Desiderio',
                    'content' => '<p>A microsite per mission, a preset per brand — one install, one content pool.</p><ul><li><strong>Themed subtrees</strong> — flagship missions in Ember, crewed flights in Marine, night launches in Midnight.</li><li><strong>Mission elements</strong> — countdown heroes for T-minus, stats for the booster fleet, a status board for range weather.</li><li><strong>No redeploys</strong> — when a launch scrubs, an editor just reschedules the countdown.</li></ul><p>The company is fiction; the per-page theme engine and countdown and dashboard elements are stock Desiderio.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See per-page themes explained',
                    'button_link' => '{{page:technical-features}}',
                    'media' => ShowcaseBlocks::screenshot('frontend-pricing-midnight-dark.png', 'Dark Midnight preset page', 'A Desiderio page rendered in the dark Midnight theme preset.'),
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Telemetry from the alternate timeline',
                    'description' => 'Made-up numbers with believable trajectories: what launch-cadence publishing looks like when the CMS is not the bottleneck.',
                    'items' => [
                        ['value' => '14', 'label' => 'Mission microsites live', 'description_text' => 'One TYPO3 install, fourteen themed subtrees in this illustrative fleet.'],
                        ['value' => '45 min', 'label' => 'Scrub to rescheduled site', 'description_text' => 'Countdown retargeted, status board updated, hero re-published — editors only.'],
                        ['value' => '0', 'label' => 'Rebuilds between launches', 'description_text' => 'Theme presets switch at runtime; the imagined launch tempo never waits for CI.'],
                        ['value' => '99.9%', 'label' => 'Uptime target met', 'description_text' => 'Server-rendered pages with no JS framework survive every illustrative traffic spike.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We reuse boosters because rebuilding them for every flight would be absurd. Rebuilding the website for every mission was the same absurdity — so we stopped.',
                    'author' => 'Director of Mission Communications',
                    'role' => 'invented persona — no real the space company statement',
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Launch cadence for your content team',
                    'description' => 'Countdown heroes, status boards, per-subtree themes, and a seeder that builds the whole demo in one command — all in the free core.',
                    'cta_text' => 'Start your countdown on GitHub',
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
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'The imagined setup: launch pages at model speed',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Why our imagined AI research lab picks TYPO3 + Desiderio',
                    'content' => '<p>Each product family runs its own preset, inherited down its subtree from a single install.</p><ul><li><strong>On-brand families</strong> — a calm neutral for the platform, something warmer for consumer apps.</li><li><strong>Stock monetization</strong> — pricing tables, comparison matrices, and usage calculators ship ready.</li><li><strong>Minutes, not tickets</strong> — marketing updates tiers right after a pricing call.</li><li><strong>No lock-in</strong> — the procurement answer is a GPL license and a composer.json.</li></ul><p>The company is borrowed; the elements and theme engine are real.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'Browse the pricing elements',
                    'button_link' => '{{page:chapter-pricing}}',
                    'media' => ShowcaseBlocks::screenshot('backend-site-settings-theme.png', 'Theme preset selection in site settings', 'TYPO3 site settings with the Desiderio theme preset dropdown.'),
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Benchmarks nobody ran, in a universe nobody visited',
                    'description' => 'Invented but plausible: what shipping velocity looks like when the website re-themes instead of rebuilding.',
                    'items' => [
                        ['value' => '6', 'label' => 'Product families, one install', 'description_text' => 'Each with its own preset and page subtree in this illustrative setup.'],
                        ['value' => '2 h', 'label' => 'Keynote to live launch page', 'description_text' => 'Hero, feature grid, pricing table, FAQ — composed from existing elements.'],
                        ['value' => '0', 'label' => 'Rebuilds per product launch', 'description_text' => 'Runtime token switching makes the imagined design refresh a dropdown choice.'],
                        ['value' => '244', 'label' => 'Elements on the shelf', 'description_text' => 'The one number in this story that is not illustrative.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'We benchmark everything, so we benchmarked our website workflow. Re-theming beat rebuilding on every metric — tokens per launch went to zero, in the good way.',
                    'author' => 'Head of Web Experience',
                    'role' => 'invented persona — no real the AI research lab statement',
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Ship your next launch page the illustrative-the AI research lab way',
                    'description' => 'Pricing tables, comparison matrices, heroes, and FAQs — finished, themed, and free. The only thing we cannot ship is the keynote.',
                    'cta_text' => 'Get the free package',
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Sixty years of missions, sixty years of microsites — each program its own themed subtree.</p><ul><li><strong>A preset per era</strong> — Apollo in archive beige, Artemis in Midnight.</li><li><strong>Stock storytelling</strong> — timelines, stats, and galleries out of the box.</li><li><strong>Audit-ready</strong> — accessible markup Section 508 auditors sign off without a meeting.</li></ul><p>The agency is illustrative; the WCAG 2.2-checked contrast on every preset is not.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '60+', 'label' => 'Mission subtrees migrated', 'description_text' => 'Each program keeps its own era-appropriate theme preset in this fiction.'],
                        ['value' => '508', 'label' => 'Compliance sections passed', 'description_text' => 'Accessible-by-default elements clear the imagined federal audit on the first run.'],
                        ['value' => '1 day', 'label' => 'Press kit to live page', 'description_text' => 'Editors compose galleries and stat boards without waiting on contractors.'],
                        ['value' => '0', 'label' => 'Vendor lock-ins', 'description_text' => 'GPL license and self-hosting keep the imagined procurement office calm.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Every original gets a landing page, and every show its own mood — one install, many presets.</p><ul><li><strong>Mood per show</strong> — the dark thriller runs Midnight, the baking show runs Citrus.</li><li><strong>Stock elements</strong> — countdown heroes for premiere dates, FAQ for spoilers policy.</li><li><strong>One less tool</strong> — the marketing team retires its static-site generator.</li></ul><p>The company is borrowed; the per-subtree theme engine ships today.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '80', 'label' => 'Series pages, one install', 'description_text' => 'Each landing page carries the show\'s own preset in this scenario.'],
                        ['value' => '3 h', 'label' => 'Greenlight to teaser page', 'description_text' => 'Hero, trailer embed, countdown — composed from existing elements.'],
                        ['value' => '0', 'label' => 'Rebuilds between premieres', 'description_text' => 'Presets switch at runtime; the imagined release calendar never waits for CI.'],
                        ['value' => '2', 'label' => 'Modes shipped by default', 'description_text' => 'Light and dark, both WCAG-checked — binge-friendly at 2 a.m.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A plumber, a princess, and a console launch share nothing visually — except, in this fiction, a TYPO3 install.</p><ul><li><strong>A preset per franchise</strong> — warm reds for the platformer, sage greens for the adventure, clean neutrals for hardware.</li><li><strong>Launch in a click</strong> — pages assemble from countdown heroes, feature grids, and pricing tables.</li><li><strong>Legal-friendly</strong> — the GPL license keeps procurement happy.</li></ul><p>The franchises are real, the scenario is not, the elements ship today.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '12', 'label' => 'Franchise brand worlds', 'description_text' => 'One install, twelve visual universes via per-subtree presets in this fiction.'],
                        ['value' => '4 h', 'label' => 'Direct to launch page', 'description_text' => 'The imagined web team publishes while the presentation still streams.'],
                        ['value' => '0', 'label' => 'Style sheets hand-written', 'description_text' => 'Every world is a token set, not a fork of the frontend.'],
                        ['value' => '100%', 'label' => 'Editors in the backend', 'description_text' => 'Backend previews mean nobody publishes a hero blind.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company built on interlocking parts would recognise this instantly: <strong>244 elements as bricks.</strong></p><ul><li><strong>Studs that only fit one way</strong> — typed component contracts.</li><li><strong>Clicked together</strong> — a campaign page assembled in an afternoon.</li><li><strong>A preset per range</strong> — space sets run Midnight, botanical sets run Forest.</li></ul><p>The bricks are real and GPL-licensed; the company is on loan for the joke.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '244', 'label' => 'Bricks in the box', 'description_text' => 'Desiderio\'s element library, reused across every imagined campaign.'],
                        ['value' => '1', 'label' => 'Afternoon per campaign page', 'description_text' => 'Hero, story sections, product grid — assembled, previewed, published.'],
                        ['value' => '18', 'label' => 'Product-line worlds', 'description_text' => 'Each line carries its own preset in this illustrative setup.'],
                        ['value' => '0', 'label' => 'Instructions misread', 'description_text' => 'Typed f:argument contracts fail loudly when a brick is used wrong.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p><strong>Flat-pack logic applied to the web</strong> — every country site gets the same well-labelled parts and assembles them in its own language.</p><ul><li><strong>Shared parts</strong> — heroes, product grids, and FAQ elements.</li><li><strong>Correct everywhere</strong> — XLIFF and ICU MessageFormat keep plurals and dates right from Sweden to Japan.</li><li><strong>One update, not forty</strong> — the global team ships once.</li></ul><p>The meatballs are not included; the translation architecture ships in the free package.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '42', 'label' => 'Country sites, one toolkit', 'description_text' => 'Same elements, locally assembled, in this illustrative rollout.'],
                        ['value' => '100%', 'label' => 'Strings through XLIFF', 'description_text' => 'Screen-reader labels included — no hardcoded copy anywhere.'],
                        ['value' => '1', 'label' => 'Update for all locales', 'description_text' => 'Element fixes ship once and every market inherits them.'],
                        ['value' => '0', 'label' => 'Allen keys required', 'description_text' => 'Backend previews replace the instruction sheet.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>An editorial team shipping artist features daily cannot file a ticket per page.</p><ul><li><strong>Compose, don\'t code</strong> — features built from quote blocks, stat boards, and gallery elements.</li><li><strong>Published fast</strong> — previewed in the backend, live before the song ends.</li><li><strong>Campaign in a switch</strong> — the year-end push is a preset change plus seeded pages, not a three-month project.</li></ul><p>Dark mode is no afterthought; every preset ships both modes with checked contrast.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '365', 'label' => 'Editorial pages a year', 'description_text' => 'One artist feature per day in this illustrative newsroom.'],
                        ['value' => '2', 'label' => 'Modes, both first-class', 'description_text' => 'Dark mode users get checked contrast, not an inverted afterthought.'],
                        ['value' => '45 min', 'label' => 'Brief to published feature', 'description_text' => 'Quote, stats, gallery, embed — stock elements all the way down.'],
                        ['value' => '1', 'label' => 'Preset switch for year-end', 'description_text' => 'The imagined wrapped campaign is a dropdown, not a deploy.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company that treats documentation as a product would audit its marketing stack — and like what it finds.</p><ul><li><strong>Typed contracts</strong> — <code>f:argument</code> types on every component.</li><li><strong>Zero findings</strong> — an 11-category template check, PHPStan at level max underneath.</li><li><strong>Edits, not deploys</strong> — pricing changes land in a backend preview, not a pull request.</li></ul><p>The rigor is real and verifiable in the repository; the customer is invented.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '0', 'label' => 'Audit findings tolerated', 'description_text' => 'The 11-category template audit gates every commit in the real package.'],
                        ['value' => '15 min', 'label' => 'Pricing call to live table', 'description_text' => 'The imagined billing team edits tiers like records, because they are.'],
                        ['value' => '170+', 'label' => 'Tests on the stack', 'description_text' => 'Unit and functional, PHP 8.4 and 8.5 — the one stat here that is not fiction.'],
                        ['value' => '100%', 'label' => 'Infrastructure in-house', 'description_text' => 'Self-hosted open source clears the illustrative compliance review.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>Teaching Japanese through Spanish breaks most translation layers — not this one.</p><ul><li><strong>Real i18n</strong> — XLIFF 2.0 and ICU MessageFormat handle plural rules from Polish to Arabic.</li><li><strong>Reusable pages</strong> — every course assembles from the same stat boards and timeline elements.</li><li><strong>Stock metrics</strong> — the homepage streak counter is a shipped element.</li></ul><p>The owl is pleased; the localization architecture is the real product here.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '40+', 'label' => 'Course locales served', 'description_text' => 'Each with correct plurals and date formats in this illustrative rollout.'],
                        ['value' => '100%', 'label' => 'ARIA labels translated', 'description_text' => 'Screen-reader strings ship through the same XLIFF pipeline.'],
                        ['value' => '365', 'label' => 'Day publishing streak', 'description_text' => 'The imagined content team never misses — the owl is watching.'],
                        ['value' => '0', 'label' => 'Hardcoded strings found', 'description_text' => 'Every label runs through f:translate, even the celebratory ones.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>An organization built on volunteers and GPL licenses would never accept a proprietary design system — and it doesn\'t have to.</p><ul><li><strong>Free all the way down</strong> — a GPL component library on a GPL CMS, self-hosted on its own metal.</li><li><strong>Citable accessibility</strong> — donation appeals built from banner and stat elements with policy-grade contrast.</li><li><strong>Inspectable</strong> — every template is open to the same community that edits the articles.</li></ul><p>The alignment of licenses is the entire joke — and entirely real.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '2', 'label' => 'GPL licenses, aligned', 'description_text' => 'CMS and design system share the imagined foundation\'s values out of the box.'],
                        ['value' => '100%', 'label' => 'Templates publicly auditable', 'description_text' => 'The community reviews Fluid the way it reviews citations.'],
                        ['value' => '4.5:1', 'label' => 'Contrast, enforced', 'description_text' => 'WCAG ratios are solved by the build, not promised by a styleguide PDF.'],
                        ['value' => '0', 'label' => 'Vendors in the stack', 'description_text' => 'Self-hosted everything — the illustrative procurement page stays a stub.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A company that runs more events than some federations needs a <strong>microsite assembly line</strong> — and gets one.</p><ul><li><strong>A preset per event</strong> — Ember for the desert rally, Marine for the regatta.</li><li><strong>Stock race elements</strong> — countdown heroes toward start gates, stat boards for qualifying.</li><li><strong>Launch between espressos</strong> — the events team ships a site in minutes.</li></ul><p>The wings are marketing; the runtime theme switching is shipping code.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '30+', 'label' => 'Event microsites a season', 'description_text' => 'One subtree per event in this illustrative calendar, all one install.'],
                        ['value' => '90 min', 'label' => 'Announcement to live site', 'description_text' => 'Countdown hero, schedule timeline, ticket CTA — stock parts.'],
                        ['value' => '0', 'label' => 'Energy drinks required', 'description_text' => 'Editors publish calmly; the adrenaline stays in the footage.'],
                        ['value' => '14', 'label' => 'Presets in rotation', 'description_text' => 'Every event genre gets a matching visual world.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>The laboratory that invented the web deserves better than PDF press releases — so it publishes like it researches.</p><ul><li><strong>A preset per experiment</strong> — each one its own themed subtree.</li><li><strong>Real data markup</strong> — results land as accessible chart and table elements.</li><li><strong>Outreach, assembled</strong> — pages built from timelines and stat boards.</li></ul><p>Server-rendered, no hydration cost — appropriately fundamental. The physics is real, the scenario invented.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '17', 'label' => 'Experiment subtrees', 'description_text' => 'Each collaboration keeps its own visual identity in this fiction.'],
                        ['value' => '9', 'label' => 'Chart types, accessible', 'description_text' => 'Every visualization ships a screen-reader-friendly data table twin.'],
                        ['value' => '1 h', 'label' => 'Preprint to outreach page', 'description_text' => 'The imagined comms team publishes before the arXiv listing updates.'],
                        ['value' => '0', 'label' => 'JS frameworks colliding', 'description_text' => 'Server-rendered Fluid — the only collisions happen in the ring.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
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
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'The imagined setup',
                    'content' => '<p>A studio that storyboards everything would storyboard its film pages too — and the boards map straight to elements.</p><ul><li><strong>Scene by scene</strong> — an article hero for the opening shot, alternating textmedia scenes, a stat board for the box office.</li><li><strong>A preset per film</strong> — ocean blues, desert ambers, monster pastels.</li><li><strong>Cut at runtime</strong> — themes switch like a scene change, no rebuild.</li></ul><p>The lamp hops in real life; the per-page theme engine ships in the package.</p>',
                    'variant' => 'muted',
                    'alignment' => 'start',
                    'link' => '{{page:technical-features}}',
                    'link_text' => 'The real technical facts',
                ]),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'Numbers from a universe next door',
                    'description' => 'Invented metrics with believable physics — every capability behind them ships in the free package.',
                    'items' => [
                        ['value' => '28', 'label' => 'Film worlds, one install', 'description_text' => 'Every feature keeps its own palette in this illustrative archive.'],
                        ['value' => '1', 'label' => 'Storyboard per page', 'description_text' => 'Scenes map one-to-one onto stock content elements.'],
                        ['value' => '2', 'label' => 'Modes for the screening room', 'description_text' => 'Dark mode that respects the colorists, light mode for the lobby.'],
                        ['value' => '0', 'label' => 'Renders re-queued', 'description_text' => 'Pages are server-rendered Fluid — the render farm stays on the movie.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => 'Story is king here, and our old website was a subplot nobody followed. Now every film page reads like a storyboard — and ships before the trailer drops.',
                    'author' => 'Studio Web Producer',
                    'role' => 'invented persona — no real the animation studio statement',
                    'variant' => 'large',
                ]),
            ],
        ];
    }
}
