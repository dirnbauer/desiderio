<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Feature pages for the extensions Desiderio styles: the features overview
 * plus one page per integration.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseIntegrationPages
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [
            self::featuresOverviewPage(),
            self::featureRecordsListPage(),
            self::featureMcpServerPage(),
            self::featureEasyWorkspacePage(),
            self::featureBlogPage(),
            self::featureDesiderioPage(),
            self::featureSolrPage(),
            self::featureWorkosPage(),
            self::featurePowermailPage(),
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Ecosystem',
                    'header' => 'Desiderio is more than the design system',
                    'subheadline' => '244 content elements were only the start. Around them sits an ecosystem of TYPO3 14 extensions — backend record views, one-click workspaces, a blog, enterprise search, enterprise SSO, accessible forms, stablecoin paywalls, and an agent-ready API layer. Same theme. Same security model. Same core concepts. Pick a card to go deeper.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'One stack, not thirteen plugins',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Every extension below is built to TYPO3 14.3 LTS, PHPStan level max, and the same workspace rules.',
                    'content' => '<p>Most TYPO3 sites become a pile of plugins, each with its own data model, styling, and security. <strong>The Desiderio ecosystem takes the opposite bet.</strong></p><ul><li><strong>Core concepts</strong> — content elements are Content Blocks, posts are pages, records are records, drafts are workspaces.</li><li><strong>One theme</strong> — search, forms, and login screens inherit the active preset instead of shipping their own CSS.</li><li><strong>On a policy leash</strong> — agents and APIs get declared, policy-controlled capabilities, never blank-cheque access to live data.</li></ul><p>One theme switch restyles everything; one workspace publish ships everything. The cards below go deeper on each extension.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/desiderio',
                    'media' => ShowcaseBlocks::screenshot('feature-overview.png', 'TYPO3 backend context for the Desiderio ecosystem', 'TYPO3 14 About dashboard with the backend module navigation visible. The ecosystem relationships described on this page are not shown in the screenshot.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'One ecosystem, many capabilities',
                    'eyebrow' => 'Ecosystem',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'layout-sidebar-right', 'title' => 'Record Lists', 'description' => 'Reshapes the TYPO3 Records module with Grid, Compact, Teaser, and your own custom view modes — added with TSconfig and a Fluid template, zero PHP.', 'link' => '{{page:features/records-list}}'],
                        ['icon' => 'settings', 'title' => 'MCP Server', 'description' => 'Gives AI assistants a clean, workspace-safe MCP surface over your pages, records, files, and editorial workflow — 44 bundled tools, every one mirrored on the TYPO3 CLI.', 'link' => '{{page:features/mcp-server}}'],
                        ['icon' => 'send', 'title' => 'Easy Workspace', 'description' => 'Turns workspace publishing into one button: editors review the page\'s pending changes, tick what to ship, and publish it together with every related record.', 'link' => '{{page:features/easy-workspace}}'],
                        ['icon' => 'book-open', 'title' => 'Blog', 'description' => 'A full publishing platform built entirely on core concepts — posts are pages, bodies are content elements, and workspaces stage everything, with no proprietary blog table.', 'link' => '{{page:features/blog}}'],
                        ['icon' => 'sparkles', 'title' => 'Desiderio + Innesto', 'description' => 'The design system itself: 244 content elements from 53 typed Fluid 5 components, runtime theming from site settings, and Innesto to graft shadcn registry components as new elements.', 'link' => '{{page:features/desiderio}}'],
                        ['icon' => 'search', 'title' => 'Solr Search', 'description' => 'Apache Solr\'s enterprise search speed, wrapped in shadcn components — results, facets, sorting, suggest, and accessible numbered pagination all inherit your active theme, light and dark.', 'link' => '{{page:features/solr}}'],
                        ['icon' => 'lock', 'title' => 'WorkOS Auth', 'description' => 'Enterprise single sign-on for both the TYPO3 frontend and backend, plus self-service B2B team management — one extension, two login surfaces, full AuthKit feature set.', 'link' => '{{page:features/workos}}'],
                        ['icon' => 'file', 'title' => 'Powermail Lab', 'description' => 'Editor-friendly, accessible multi-step forms in a complete shadcn design system, with Friendly Captcha bot protection that never phones home and a context-aware development bypass.', 'link' => '{{page:features/powermail}}'],
                        ['icon' => 'tag', 'title' => 'x402 Paywall', 'description' => 'Monetizes pages and API routes with the HTTP 402 standard — accept USDC micropayments from any wallet, measure revenue per page, and gate content for humans or AI agents.', 'link' => '{{page:features/x402-paywall}}'],
                        ['icon' => 'shield-check', 'title' => 'Abilities Registry', 'description' => 'One typed, permissioned registry of what the installation can do — MCP tools, CLI commands, REST endpoints and the desktop editor are generated projections, every run policy-gated and traced.', 'link' => '{{page:features/typo3-abilities}}'],
                        ['icon' => 'message-circle', 'title' => 'Agentation', 'description' => 'Brings visual annotation feedback into TYPO3 — point an AI agent at a page element with a selector, comment, and computed styles, then sync that context to Claude Code, Cursor, or any MCP agent.', 'link' => '{{page:features/agentation}}'],
                        ['icon' => 'monitor', 'title' => 'sg_apicore', 'description' => 'An attribute-driven API framework that turns content into structured data — REST endpoints, Auto-CRUD resources, generated OpenAPI specs, and token or session access, with no boilerplate.', 'link' => '{{page:features/sg-apicore}}'],
                        ['icon' => 'sparkles', 'title' => 'Skills', 'description' => 'Brings Anthropic-style agent skills into TYPO3 workspaces — define SKILL.md folders, import them from git, assign them to workspace stages for auto-review, and search them with Solr facets.', 'link' => '{{page:features/skillflow}}'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'How the pieces fit together',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Built on core concepts, not a proprietary layer', 'content' => '<p>Every extension leans on TYPO3 primitives instead of inventing its own.</p><ul><li><strong>Posts are pages</strong> — a dedicated doktype with ordinary content elements.</li><li><strong>Elements are Content Blocks</strong> — real database columns and backend previews.</li><li><strong>Records stay records</strong> — Record Lists reshapes the existing module, not a replacement.</li></ul><p>Your TYPO3 skills transfer directly, with no parallel data model to migrate or untangle.</p>', 'open_by_default' => 1],
                        ['title' => 'One theme switch restyles the whole site', 'content' => '<p>Theming is OKLCH design tokens driven by site settings, switchable per site and subtree without a rebuild.</p><ul><li><strong>Everything opts in</strong> — Solr results, Powermail forms, and blog templates render through the same shadcn presets.</li><li><strong>Light and dark</strong> — change the preset once and both modes follow.</li><li><strong>Site-wide</strong> — search, forms, and articles all change together.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace-safe from top to bottom', 'content' => '<p>The whole stack uses TYPO3 Workspaces consistently, not ad-hoc \'draft\' flags.</p><ul><li><strong>Record Lists</strong> — overlays every row and colour-codes new, changed, moved, and deleted records.</li><li><strong>Easy Workspace</strong> — publishes a page\'s pending changes with their related records in one click.</li><li><strong>Blog</strong> — stages posts, tags, and authors.</li><li><strong>MCP &amp; API</strong> — stage agent writes by default, keeping live UIDs stable.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Agent-ready, but on a policy leash', 'content' => '<p>Built for AI agents without handing them the keys.</p><ul><li><strong>Structured tools</strong> — MCP Server, sg_apicore, and the Abilities Registry expose content as machine-readable tools.</li><li><strong>Declared limits</strong> — each declares the subsystems it may touch and defaults network access to the site itself.</li><li><strong>Workspace writes</strong> — agent changes route through workspaces.</li><li><strong>Human-in-the-loop</strong> — Agentation and Skillflow produce suggestions, never silent auto-applied changes.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Held to one engineering standard', 'content' => '<p>One stack with one quality bar, not a bag of unrelated plugins.</p><ul><li><strong>Modern target</strong> — TYPO3 14.3 LTS on PHP 8.4+, PHPStan at level max.</li><li><strong>Localised &amp; tested</strong> — English and German XLIFF, with unit and functional suites.</li><li><strong>Security as a feature</strong> — parameterised queries, CSRF-protected actions, redacted secrets, dev-only execution gates.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'With thanks',
                    'header' => 'Every one of these is open source we did not write',
                    'subheadline' => 'Desiderio is glue and taste on top of excellent work by other people. The ecosystem above leans on community extensions — and, for the entire AI layer, on Netresearch. We use their extensions because they are excellent, and each deserves a direct thank-you.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Backend module enhancement',
                    'header' => 'Browse records the way they should be seen',
                    'subheadline' => 'Grid cards, compact tables, teaser listings, or custom layouts—all in the backend Records module, all configurable in TSconfig, none requiring PHP.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Records List Types: multiple views for the data editors work with every day',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Stop forcing editors into a one-size-fits-all table. Give them the view that matches the data.',
                    'content' => '<p>Stop forcing editors into one table. <strong>Records List Types</strong> adds four view modes to the TYPO3 Records module — pick the one that fits the data.</p><ul><li><strong>Grid</strong> — cards with thumbnails and field values, fully responsive.</li><li><strong>Compact</strong> — a dense, scrollable table for hundreds of records.</li><li><strong>Teaser</strong> — news-style cards with title, date, and excerpt.</li><li><strong>Custom</strong> — register your own views in TSconfig and Fluid, no PHP.</li></ul><p>Every view ships with configurable filters, drag-and-drop reordering, workspace-aware overlays, dark mode, and accessible keyboard navigation.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-records-list-types',
                    'media' => ShowcaseBlocks::screenshot('feature-records-list.png', 'TYPO3 Records module in list view', 'TYPO3 backend Records module showing a News storage folder, list rows, field values, and record actions. Grid, Compact, Teaser, filters, and workspace overlays are not visible in this screenshot.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
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
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer config repositories.records-list-types vcs https://github.com/dirnbauer/typo3-records-list-types.git
composer require webconsulting/records-list-types:^1.0.3
./vendor/bin/typo3 extension:setup -e records_list_types
./vendor/bin/typo3 cache:flush',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'AI & Automation',
                    'header' => 'Your LLM talks to TYPO3',
                    'subheadline' => 'Give Claude, Cursor, or any MCP client safe, structured access to pages, records, files, and publishing workflows—with workspace staging and full DataHandler safety baked in.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What is the Model Context Protocol?',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'It\'s Anthropic\'s open standard for how LLMs ask tools for information. MCP Server translates TYPO3 into that language.',
                    'content' => '<p><strong>MCP is Anthropic\'s open standard</strong> for how AI assistants call structured tools. This extension teaches TYPO3 to speak it.</p><ul><li><strong>Dozens of native tools</strong> — read page trees, search records, attach images, translate, audit metadata, publish workspaces.</li><li><strong>Workspace-safe</strong> — every write stages first, so live data stays untouched.</li><li><strong>Any client</strong> — OAuth 2.1 for remote clients, stdio for local dev.</li><li><strong>Same tools in CI</strong> — shell scripts and GitHub Actions use the identical CLI interface.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-mcp-server',
                    'media' => ShowcaseBlocks::screenshot('feature-mcp-server.png', 'MCP Server backend module', 'TYPO3 MCP Server connection setup showing the remote server URL, local Cursor configuration, stdio command, and active OAuth token controls.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
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
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require hn/typo3-mcp-server
vendor/bin/typo3 extension:activate mcp_server
# Then in the TYPO3 backend, go to User > MCP Server to see your endpoint and set up clients.',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Backend extension',
                    'header' => 'Editors see what they\'re about to publish',
                    'subheadline' => 'A toolbar button and a backend module let editors review pending workspace changes for the current page or news article, then publish everything together with one click—exactly as they built it.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Why workspace-based editing matters',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Staging in TYPO3 workspaces is how professional teams keep drafts safe while the live site runs. The problem: default TYPO3 workspace publishing is buried three screens deep and shows everything at once. Easy Workspace brings it to the top bar and shows only what\'s pending.',
                    'content' => '<p>Before hitting publish, editors need to see exactly what changed. <strong>Easy Workspace</strong> puts that review in the top-right corner — no hunting through nested modules.</p><ul><li><strong>Confirm readiness</strong> — every content element you added is staged and ready.</li><li><strong>Children included</strong> — inline children publish with their parents.</li><li><strong>No surprises</strong> — see exactly which changes go live together.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-webcon-easy-workspace',
                    'media' => ShowcaseBlocks::screenshot('feature-easy-workspace.png', 'Pending changes review panel', 'TYPO3 Easy Workspace staging review module showing two affected records, selectable rows, state chips, the selected count, and publishing controls.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
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
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/webcon-easy-workspace
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Extensions',
                    'header' => 'Your blog, built on TYPO3 core',
                    'subheadline' => 'Posts are pages, content elements are content elements, and editors manage everything in the page module — just like the rest of your site.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'A blog for TYPO3, built entirely on core concepts',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Posts as pages, content elements as content elements, and editors who never leave the page module.',
                    'content' => '<p>One principle: <strong>if you know TYPO3, you already know how to run a blog.</strong> No proprietary editors, no custom tables, no workflow living outside your page tree.</p><ul><li><strong>Posts are pages</strong> — doktype 137; authors, tags, and categories are core concepts.</li><li><strong>Your full toolkit</strong> — every content element and backend layout works inside a post.</li><li><strong>Stage in workspaces</strong> — posts, tags, and authors; comments stay live-editable.</li><li><strong>Batteries included</strong> — backend modules plus 20 plugins for lists, filters, archives, sidebars, and RSS.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'See on GitHub',
                    'button_link' => 'https://github.com/TYPO3GmbH/blog',
                    'media' => ShowcaseBlocks::screenshot('feature-blog.png', 'Blog post edit in the page module with workspace overlay', 'A blog post edit screen in the TYPO3 page module, showing the post title in the page header along with metadata badges for publish date, categories, tags, and author.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'Extensions',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'file', 'title' => 'Posts are pages', 'description' => 'Create and manage posts in the page module with every content element. Workspace staging for posts, tags, and authors. Live-editable comments that never block on editorial coordination.', 'link' => ''],
                        ['icon' => 'menu', 'title' => '20 plugins for every layout', 'description' => 'All 20 Extbase plugins handle lists, filters, archives, sidebars, and RSS feeds. Filter by category, tag, author, or date. Paginated lists with clean semantics for search engines.', 'link' => ''],
                        ['icon' => 'history', 'title' => 'Workspace-ready editorial flow', 'description' => 'Posts, tags, and authors stage in workspaces before publishing. Comments stay live-editable. Moderation workflow with author and admin email notifications. Optional Google reCAPTCHA.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
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
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'composer.json',
                    'code' => 'composer require t3g/blog',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
            'abstract' => 'Desiderio is a complete TYPO3 design system: 244 ready-to-use content elements built from 53 typed Fluid 5 components, a runtime theming layer driven by TYPO3 site settings, and open extensibility via Innesto—which grafts shadcn/ui registry components as new elements without a frontend build step on your site.',
            'description' => '244 shadcn/ui-styled content elements for TYPO3 14.3, extensible via Innesto. Runtime theming, Content Blocks 2.2, typed Fluid 5 components—all GPL-2.0.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Theme + Design System',
                    'header' => '244 elements. Extensible. Themed at runtime.',
                    'subheadline' => 'Desiderio brings a complete shadcn/ui-inspired design system to TYPO3 v14.3+: 244 finished content elements, 53 atomic Fluid 5 components, and an extensibility layer (Innesto) that grafts components from shadcn registries as new Content Blocks in one command.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What is Desiderio + Innesto',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'A complete, open-source design system for TYPO3 that you can extend in minutes.',
                    'content' => '<p><strong>Desiderio</strong> is a complete, working editorial system for TYPO3 v14.3+ — not a template kit you still have to build.</p><ul><li><strong>244 elements, no build step</strong> — page templates, optional Blog/News/Solr/Powermail overrides, and seeded demo content.</li><li><strong>Extensible via Innesto</strong> — graft a component from any shadcn registry (shadcn/ui, Magic UI, blocks.so) as a new Content Block.</li><li><strong>Yours to keep</strong> — both are free and open-source under GPL-2.0-or-later.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'Get Desiderio on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/desiderio',
                    'media' => ShowcaseBlocks::screenshot('feature-desiderio.png', 'Desiderio in the TYPO3 Visual Editor', 'The TYPO3 Visual Editor showing a Desiderio page and its frontend rendering inside the editor workspace.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => '53 typed Fluid 5 components in atomic layers', 'content' => '<p>The component system follows atomic design at the Fluid 5 level.</p><ul><li><strong>17 atoms</strong> — button, badge, input, icon, avatar, link, image, label (<code>d:atom</code>).</li><li><strong>28 molecules</strong> — card, accordion, table, alert, form controls (<code>d:molecule</code>).</li><li><strong>4 layout primitives</strong> — section, container, grid, and stack under <code>d:layout</code>.</li><li><strong>4 site organisms</strong> — header, footer, breadcrumb, and page header under <code>d:organism</code>.</li><li><strong>Typed contracts</strong> — every component declares <code>f:argument</code> types: an enforced API, not a convention.</li></ul><p>All 244 elements build from these layers, so one audit can verify every element.</p>', 'open_by_default' => 1],
                        ['title' => '244 finished content elements across ten categories', 'content' => '<p>244 editor-facing elements, grouped into clear categories.</p><ul><li><strong>Marketing</strong> — heroes, feature blocks, pricing, trust and social proof.</li><li><strong>Data</strong> — dashboard elements with chart helpers.</li><li><strong>Structure</strong> — navigation, footers, legal pages, forms, editorial content.</li></ul><p>Each appears in the New Content Element wizard with a backend preview — production-styled and themed by the active preset, never a wireframe.</p>', 'open_by_default' => 0],
                        ['title' => 'Runtime theme presets driven by TYPO3 site settings', 'content' => '<p>Theming is pure CSS tokens applied at runtime, chosen in TYPO3 site settings.</p><ul><li><strong>Repaint, no rebuild</strong> — a preset change updates colours, radius, density, focus rings, and fonts instantly.</li><li><strong>15 presets bundled</strong> — five from ui.shadcn.com/create, ten house designs, plus a custom slot.</li><li><strong>Per subtree</strong> — run separate campaigns or brands in their own theme from one install.</li><li><strong>Icon-agnostic</strong> — semantic icon keys let the library change without rewriting records.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Innesto: graft shadcn registry components as Content Blocks', 'content' => '<p>Innesto makes Desiderio extensible — no frontend build step on your site.</p><ul><li><strong>One command</strong> — <code>innesto:add &lt;component&gt;</code> fetches the JSON, converts styling to tokens, and scaffolds the element.</li><li><strong>Any registry</strong> — shadcn/ui, Magic UI, blocks.so, or anything that publishes JSON.</li><li><strong>Finishing pass</strong> — translate markup to Fluid and model props by hand, or let <code>--ai</code> do it.</li><li><strong>On-theme</strong> — every graft uses the active Desiderio preset automatically.</li></ul><p>Presentational components like marquees, logo clouds, and bento grids are the natural fit.</p>', 'open_by_default' => 0],
                        ['title' => 'Content Blocks 2.2: schema-first elements with backend previews', 'content' => '<p>All 244 elements are Content Blocks (friendsoftypo3/content-blocks ^2.2), not traditional plugins.</p><ul><li><strong>Declarative schemas</strong> — <code>config.yaml</code> with automatic database columns.</li><li><strong>Backend previews</strong> — editors see the element before publishing.</li><li><strong>Explicit child tables</strong> — collection records map to named tables, no guessing.</li><li><strong>Portable</strong> — export an element with its records; the schema handles table creation elsewhere.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Integrations: News, Blog, Solr, and Powermail with Brevo + Friendly Captcha', 'content' => '<p>The extensions you already run get the same shadcn treatment, via opt-in site sets.</p><ul><li><strong>Auto-activating</strong> — templates for georgringer/news, t3g/blog, Solr, and in2code/powermail switch on when installed.</li><li><strong>Forms</strong> — Form Framework templates with Friendly Captcha and a Brevo double-opt-in finisher.</li><li><strong>Theme follows</strong> — switch the preset and news lists, search results, and forms switch with it.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Seeding and CLI for idempotent demo content', 'content' => '<p>Symfony console commands automate the heavy lifting.</p><ul><li><strong>styleguide:seed</strong> — builds the full 244-element demo site from YAML, idempotent and live-safe.</li><li><strong>starter:seed</strong> — a corporate starter site structure with demo content.</li><li><strong>blog:seed-pages</strong> — normalises an existing Blog tree to Desiderio layouts.</li><li><strong>Guard rails</strong> — seeders refuse to run in a workspace or in Production without <code>--allow-production</code>.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
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
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Integration',
                    'header' => 'Enterprise search that matches your design system',
                    'subheadline' => 'Solr results render in shadcn components that inherit your theme preset in real time, no template work required.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Search as part of your design system, not a plugin bolted on',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Apache Solr for TYPO3 — built and maintained for years by dkd Internet Service GmbH and the TYPO3-Solr team — is the production search engine with millisecond response times. Desiderio gives it a complete shadcn template set.',
                    'content' => '<p><strong>Solr finds the content; Desiderio styles the interface.</strong></p><ul><li><strong>Fully themed</strong> — results, facets, sort, per-page, and pagination follow your preset, light and dark.</li><li><strong>Ready to use</strong> — the template set ships complete, no CSS to write.</li><li><strong>AJAX refinement</strong> — faceting, sorting, and paging refresh without a full reload.</li><li><strong>Repaints with the site</strong> — switch from Lagoon to Midnight and the results page follows.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/TYPO3-Solr/ext-solr',
                    'media' => ShowcaseBlocks::screenshot('feature-solr.png', 'Apache Solr Index Queue in TYPO3', 'TYPO3 backend Solr Index Queue showing index status counts and initialization controls. The themed frontend results, facets, sorting, and pagination described on this page are composed in the accompanying video rather than captured here.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
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
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require apache-solr-for-typo3/solr:^14.0 webconsulting/desiderio:^2.0
# Configure a Solr connection in your site config
# Point your search results page to the EXT:desiderio Solr Results template
# Run the Solr indexing queue to populate the index',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Authentication & SSO',
                    'header' => 'Enterprise login without the enterprise drama',
                    'subheadline' => 'WorkOS powers both your frontend and backend with passwordless magic links, social sign-in, and the team workspace that enterprise customers actually use.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What WorkOS does for your TYPO3 site',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'This is not another OAuth provider.',
                    'content' => '<p><strong>WorkOS is enterprise SSO with teeth</strong> — and workos_auth hooks all of it into TYPO3 at the auth level.</p><ul><li><strong>Every sign-in style</strong> — email + password, magic links, and OAuth for Google, Microsoft, GitHub, and Apple.</li><li><strong>B2B workspace layer</strong> — organizations, invitations, roles, and admin portals.</li><li><strong>One identity everywhere</strong> — backend login and frontend plugins share the same WorkOS credentials.</li><li><strong>Enterprise controls</strong> — your customers\' IT admins get the team and audit tools they expect.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/workos',
                    'media' => ShowcaseBlocks::screenshot('feature-workos.png', 'WorkOS-protected TYPO3 MCP configuration', 'TYPO3 MCP Server configuration showing WorkOS protection, endpoint settings, a placeholder AuthKit domain, and the current Development and anonymous state. Login and organization interfaces are not visible in this screenshot.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
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
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Extension Integration',
                    'header' => 'Forms your editors can build. Spam shields your developers understand.',
                    'subheadline' => 'Powermail by in2code is a proven form extension; Desiderio\'s shadcn partials and Friendly Captcha (Studio Mitte) complete the stack.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What Powermail + Desiderio does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The form extension that doesn\'t pretend to be a page builder.',
                    'content' => '<p><strong>Powermail builds the form; Desiderio styles it.</strong> Editors define everything in the backend — no HTML, PHP, or templates.</p><ul><li><strong>Every field reskinned</strong> — inputs, checkboxes, selects, radio groups, and textareas in shadcn partials.</li><li><strong>Private bot protection</strong> — Friendly Captcha sends no user IP to Google.</li><li><strong>Fast locally</strong> — a context-aware bypass flag skips the captcha in development when you opt in.</li><li><strong>Validated twice</strong> — multi-step forms check on both the client and the server.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/powermail',
                    'media' => ShowcaseBlocks::screenshot('feature-powermail.png', 'Powermail form overview in TYPO3', 'TYPO3 Powermail form overview listing six forms, their language, usage count, and available actions. The form builder, multi-step frontend, validation, and captcha states are not visible in this screenshot.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
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
                ShowcaseBlocks::block('desiderio_codeblock', [
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
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Start building forms your editors will use',
                    'description' => 'Powermail + Desiderio ships with six seeded demo forms — contact, newsletter, callback, appointment, support, and a four-step project request — each with Friendly Captcha and English and German thank-you flows. Modify them, duplicate them, or build new ones from scratch in the backend. No template code needed. Thank you to in2code for Powermail, and to Studio Mitte for the Friendly Captcha extension.',
                    'cta_text' => 'Open Powermail on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/powermail',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
