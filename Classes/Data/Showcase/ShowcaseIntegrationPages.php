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
            'abstract' => 'Desiderio is the design system. Around it are TYPO3 v14 extensions for the backend, search, login, forms, payments and AI agents, built on TYPO3 core concepts.',
            'description' => 'Desiderio and the TYPO3 v14 extensions around it: record views, workspaces, blog, search, login, forms, payments and an API layer for AI agents.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Ecosystem',
                    'header' => 'Desiderio is more than a design system',
                    'subheadline' => 'Around the 244 content elements sits a set of TYPO3 v14 extensions. They share one theme, one security model and the same core concepts.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'One stack instead of 13 separate plugins',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Every extension below targets TYPO3 v14.3 LTS, runs PHPStan at level 8 and follows the same workspace rules.',
                    'content' => '<p>Many TYPO3 sites collect plugins, each with its own data model, styling and security. <strong>The Desiderio ecosystem does the opposite.</strong></p><ul><li><strong>Core concepts</strong> — content elements are Content Blocks, posts are pages, records stay records and drafts live in workspaces.</li><li><strong>One theme</strong> — search, forms and login screens use the active preset instead of their own CSS.</li><li><strong>Controlled access</strong> — agents and APIs get declared capabilities under a policy, never open access to live data.</li></ul><p>One theme switch restyles everything, and one workspace publish releases everything. The cards below describe each extension.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/desiderio',
                    'media' => ShowcaseBlocks::screenshot('feature-overview.png', 'TYPO3 backend with the About dashboard', 'The TYPO3 v14 backend with the About dashboard and the module menu.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'The extensions at a glance',
                    'eyebrow' => 'Extensions',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'layout-sidebar-right', 'title' => 'Record Lists', 'description' => 'Adds grid, compact, teaser and custom views to the TYPO3 Records module. You add a view with TSconfig and a Fluid template, without PHP.', 'link' => '{{page:features/records-list}}'],
                        ['icon' => 'settings', 'title' => 'MCP Server', 'description' => 'Gives AI assistants workspace-safe access to pages, records, files and the editorial workflow. It bundles 44 tools, and each one also runs on the TYPO3 CLI.', 'link' => '{{page:features/mcp-server}}'],
                        ['icon' => 'send', 'title' => 'Easy Workspace', 'description' => 'Publishes workspace changes with one button. Editors review the pending changes of a page, select what to publish and publish it with all related records.', 'link' => '{{page:features/easy-workspace}}'],
                        ['icon' => 'book-open', 'title' => 'Blog', 'description' => 'A blog built on core concepts. Posts are pages, their content is ordinary content elements, and workspaces stage everything.', 'link' => '{{page:features/blog}}'],
                        ['icon' => 'sparkles', 'title' => 'Desiderio + Innesto', 'description' => 'The design system: 244 content elements built from 62 typed Fluid 5 components, with themes set in the site settings. Innesto adds shadcn registry components as new elements.', 'link' => '{{page:features/desiderio}}'],
                        ['icon' => 'search', 'title' => 'Solr Search', 'description' => 'Apache Solr search with shadcn templates. Results, facets, sorting, suggestions and accessible pagination follow the active theme, in light and dark mode.', 'link' => '{{page:features/solr}}'],
                        ['icon' => 'lock', 'title' => 'WorkOS Auth', 'description' => 'Single sign-on for the TYPO3 frontend and backend, plus self-service team management for B2B customers. One extension covers both login screens.', 'link' => '{{page:features/workos}}'],
                        ['icon' => 'file', 'title' => 'Powermail Lab', 'description' => 'Accessible multi-step forms in the Desiderio design system. Friendly Captcha blocks bots without tracking, and a development bypass keeps local testing fast.', 'link' => '{{page:features/powermail}}'],
                        ['icon' => 'tag', 'title' => 'x402 Paywall', 'description' => 'Charges for pages and API routes with the HTTP 402 standard. People and AI agents pay in USDC from any wallet, and you see the revenue per page.', 'link' => '{{page:features/x402-paywall}}'],
                        ['icon' => 'shield-check', 'title' => 'Abilities Registry', 'description' => 'One typed registry of what the installation can do, with permissions. MCP tools, CLI commands, REST endpoints and the desktop editor are generated from it, and every run is checked and traced.', 'link' => '{{page:features/typo3-abilities}}'],
                        ['icon' => 'message-circle', 'title' => 'Agentation', 'description' => 'Visual feedback for AI agents. Mark a page element, add a comment and send it with its selector and styles to Claude Code, Cursor or another MCP agent.', 'link' => '{{page:features/agentation}}'],
                        ['icon' => 'monitor', 'title' => 'sg_apicore', 'description' => 'An API framework driven by PHP attributes that turns content into structured data. It provides REST endpoints, Auto-CRUD resources, generated OpenAPI specs and token or session access.', 'link' => '{{page:features/sg-apicore}}'],
                        ['icon' => 'sparkles', 'title' => 'Skills', 'description' => 'Brings Anthropic-style agent skills into TYPO3 workspaces. Define SKILL.md folders or import them from git, assign them to workspace stages for automatic review, and search them with Solr facets.', 'link' => '{{page:features/skillflow}}'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'How the pieces fit together',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Built on TYPO3 core concepts', 'content' => '<p>Every extension uses TYPO3 building blocks instead of its own.</p><ul><li><strong>Posts are pages</strong> — with their own page type and ordinary content elements.</li><li><strong>Elements are Content Blocks</strong> — with real database columns and backend previews.</li><li><strong>Records stay records</strong> — Record Lists changes the existing module instead of replacing it.</li></ul><p>What you know about TYPO3 still applies. There is no second data model to migrate.</p>', 'open_by_default' => 1],
                        ['title' => 'One theme switch restyles the whole site', 'content' => '<p>Themes are OKLCH design tokens set in the site settings. You can switch them per site or per page tree, without a rebuild.</p><ul><li><strong>Everything follows</strong> — Solr results, Powermail forms and blog templates use the same presets.</li><li><strong>Light and dark</strong> — change the preset once and both modes follow.</li><li><strong>Site-wide</strong> — search, forms and articles change together.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Every extension respects workspaces', 'content' => '<p>The whole stack uses TYPO3 workspaces, not its own draft flags.</p><ul><li><strong>Record Lists</strong> — overlays every row and colour-codes new, changed, moved and deleted records.</li><li><strong>Easy Workspace</strong> — publishes the pending changes of a page with their related records in one click.</li><li><strong>Blog</strong> — stages posts, tags and authors.</li><li><strong>MCP and API</strong> — stage agent writes by default and keep live UIDs stable.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Ready for AI agents, within set limits', 'content' => '<p>AI agents can work with the site, but they don\'t get full control.</p><ul><li><strong>Structured tools</strong> — MCP Server, sg_apicore and the Abilities Registry offer content as tools that machines can read.</li><li><strong>Declared limits</strong> — each one declares which subsystems it may use, and network access defaults to the site itself.</li><li><strong>Workspace writes</strong> — agent changes go through workspaces.</li><li><strong>A person decides</strong> — Agentation and Skillflow make suggestions and never apply changes on their own.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'One engineering standard for every extension', 'content' => '<p>The extensions share one quality bar.</p><ul><li><strong>Current target</strong> — TYPO3 v14.3 LTS on PHP 8.4 or newer, with PHPStan at level 8.</li><li><strong>Translated and tested</strong> — English and German XLIFF, with unit and functional tests.</li><li><strong>Security built in</strong> — parameterised queries, CSRF-protected actions, redacted secrets and execution gates that only open in development.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'With thanks',
                    'header' => 'Built on other people\'s open source',
                    'subheadline' => 'Desiderio builds on work by other people. The extensions above rely on community projects, and the whole AI layer runs on code by Netresearch.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Projects Desiderio builds on',
                    'eyebrow' => 'With thanks',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'sparkles', 'title' => 'The AI layer — Netresearch', 'description' => 'nr_llm, nr_mcp_agent, nr_vault and t3_cowriter provide LLM access, a backend AI assistant, encrypted secrets and an AI cowriter. Thank you, Netresearch DTT GmbH.', 'link' => 'https://github.com/netresearch'],
                        ['icon' => 'book-open', 'title' => 'Content Blocks — TYPO3 Content Types Team', 'description' => 'All 244 elements are Content Blocks: one schema per element, database columns created automatically and a real backend preview. Thank you, Nikita Hovratov and the Content Types Team.', 'link' => 'https://github.com/nhovratov'],
                        ['icon' => 'file', 'title' => 'Powermail — in2code', 'description' => 'The form extension behind the Powermail Lab. Editors build multi-step forms in the backend, and Desiderio only restyles them. Thank you, in2code: Stefan Busemann, Alex Kellner and Andreas Nedbal.', 'link' => 'https://github.com/in2code-de/powermail'],
                        ['icon' => 'menu', 'title' => 'News — Georg Ringer', 'description' => 'The TYPO3 news extension behind the styled teasers, detail views and archives. Thank you, Georg Ringer.', 'link' => 'https://github.com/georgringer/news'],
                        ['icon' => 'book-open', 'title' => 'Blog — TYPO3 GmbH', 'description' => 'Posts are pages, their content is content elements, and workspaces stage everything. Desiderio adds a theme on top. Thank you, TYPO3 GmbH.', 'link' => 'https://github.com/TYPO3GmbH/blog'],
                        ['icon' => 'search', 'title' => 'Apache Solr for TYPO3 — dkd', 'description' => 'Fast search with a mature TYPO3 integration, maintained for years by dkd Internet Service GmbH and the TYPO3-Solr team. Thank you.', 'link' => 'https://github.com/TYPO3-Solr/ext-solr'],
                        ['icon' => 'shield-check', 'title' => 'Friendly Captcha — Studio Mitte', 'description' => 'Bot protection by proof of work, without tracking. Thank you to Studio Mitte for the TYPO3 extension and to Friendly Captcha for the service.', 'link' => 'https://friendlycaptcha.com'],
                        ['icon' => 'monitor', 'title' => 'Visual Editor — friends of TYPO3', 'description' => 'Inline frontend editing for the content elements, from the community Visual Editor. Thank you to the friends of TYPO3 maintainers.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'shadcn/ui, Fluid & Tailwind', 'description' => 'The design language is shadcn/ui by shadcn. The template engine is Fluid 5 by Simon Praetorius. The utility CSS is Tailwind by Tailwind Labs.', 'link' => 'https://ui.shadcn.com'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Start with the design system, add more later',
                    'description' => 'Install the free Desiderio core first, then add the extensions you need. They share the theme and the rules, so nothing needs rewriting.',
                    'cta_text' => 'Get started free',
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
            'title' => 'New views for the TYPO3 Records module',
            'navTitle' => 'Record Lists',
            'slug' => '/features/records-list',
            'abstract' => 'Records List Types adds grid, compact, teaser and custom views to the TYPO3 Records module. Editors see cards with thumbnails, dense tables or news-style teasers instead of one table.',
            'description' => 'Grid, compact, teaser and custom views for the TYPO3 Records module, with filters, drag-and-drop sorting, dark mode and workspace support.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Backend module',
                    'header' => 'Show records as cards, tables or teasers',
                    'subheadline' => 'Grid cards, compact tables, teaser lists and your own layouts, all in the backend Records module. You configure them in TSconfig, without PHP.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Four views instead of one table',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Editors get the view that matches their data, not one table for everything.',
                    'content' => '<p><strong>Records List Types</strong> adds four view modes to the TYPO3 Records module. Pick the one that fits the data.</p><ul><li><strong>Grid</strong> — responsive cards with thumbnails and field values.</li><li><strong>Compact</strong> — a dense, scrollable table for hundreds of records.</li><li><strong>Teaser</strong> — news-style cards with title, date and excerpt.</li><li><strong>Custom</strong> — your own views in TSconfig and Fluid, without PHP.</li></ul><p>Every view has filters, drag-and-drop sorting, workspace overlays, dark mode and keyboard navigation.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-records-list-types',
                    'media' => ShowcaseBlocks::screenshot('feature-records-list.png', 'TYPO3 Records module in list view', 'The TYPO3 Records module with a News folder in the standard list view, showing rows, field values and record actions.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What editors get',
                    'eyebrow' => 'Backend module',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'menu', 'title' => 'Four view modes, plus your own', 'description' => 'Grid for thumbnails, Compact for hundreds of records and Teaser for news-style lists. The GenericView template lets you add your own layouts in TSconfig and Fluid, without PHP.', 'link' => ''],
                        ['icon' => 'search', 'title' => 'Filters without hand-written SQL', 'description' => 'Editors switch on filters in the View menu to narrow records by title, hidden status, date, category or any select field. TSconfig defines the filters, and staged edits stay searchable.', 'link' => ''],
                        ['icon' => 'arrow-right', 'title' => 'Drag-and-drop and keyboard reordering', 'description' => 'Sort records with the mouse or the keyboard. The drag handle works with the keyboard, and screen readers announce each move through ARIA live regions.', 'link' => ''],
                        ['icon' => 'moon', 'title' => 'Dark mode and workspace states included', 'description' => 'All views work in light and dark mode. Workspace states (new, modified, moved, deleted) show as coloured markers, and overlaid records stay visible and searchable.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Grid view: cards with thumbnails', 'content' => '<p>Each record becomes a card. It has an optional thumbnail, a title bar with drag handle and action menu, field values and a footer with UID, PID and language.</p><ul><li><strong>Responsive</strong> — one column on mobile, several on wide screens.</li><li><strong>Fields by type</strong> — booleans as badges, dates in monospace, long text at full width.</li><li><strong>Sortable</strong> — drag and drop with keyboard or mouse on any sortable table.</li><li><strong>State colours</strong> — hidden records are muted, and workspace states are marked as new, modified, moved or deleted.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Compact view: dense tables with fixed columns', 'content' => '<p>A dense table for scanning many records.</p><ul><li><strong>Fixed columns</strong> — icon, UID and title stay on the left, actions on the right.</li><li><strong>Horizontal scroll</strong> — other fields scroll between the fixed columns, with scroll shadows.</li><li><strong>Sortable headers</strong> — click to sort ascending or descending.</li><li><strong>Readable rows</strong> — zebra stripes, and hidden records are dimmed.</li></ul><p>Useful for dozens to hundreds of records without filtering.</p>', 'open_by_default' => 0],
                        ['title' => 'Teaser view: news-style cards', 'content' => '<p>Simple cards modelled on news and blog lists, for tx_news or your own editorial tables.</p><ul><li><strong>At a glance</strong> — title, date with icon and a two-line excerpt.</li><li><strong>Status</strong> — a UID label and a hidden or visible marker.</li><li><strong>Quick actions</strong> — show or hide, edit and delete on every card.</li><li><strong>Light and dark</strong> — works in both modes.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Custom views without PHP', 'content' => '<p>Add your own view types with TSconfig and an optional Fluid template. <strong>No PHP</strong> is needed.</p><ul><li><strong>Configurable</strong> — label, icon, template, CSS, columns and items per page.</li><li><strong>Reuse or build</strong> — extend Compact, Teaser or Grid, or use your own Fluid file.</li><li><strong>Per page</strong> — for example a timeline only on Events, an address book only on Staff.</li><li><strong>Six examples included</strong> — timeline, catalogue, address book, event list, gallery and dashboard.</li></ul><p>Custom views get the same sorting, pagination, selection and actions as the built-in ones.</p>', 'open_by_default' => 0],
                        ['title' => 'Record filters per table', 'content' => '<p>Every view mode has field filters, opened from the View menu.</p><ul><li><strong>Filter by field</strong> — title, hidden status, date range, category or any select field.</li><li><strong>Remembered</strong> — filter visibility is saved for each backend user.</li><li><strong>Workspace-aware</strong> — staged changes stay searchable before they are published.</li><li><strong>Defaults included</strong> — built-in aliases, or your own filters in TSconfig.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Drag-and-drop sorting with keyboard support', 'content' => '<p>Any table with a TCA <code>sortby</code> field can be sorted by hand.</p><ul><li><strong>Mouse</strong> — grab the drag handle and drop.</li><li><strong>Keyboard</strong> — Space or Enter to grab, arrow keys to move, Enter to drop, Escape to cancel.</li><li><strong>Announced</strong> — ARIA live regions tell screen readers the position and confirm the drop.</li><li><strong>Two modes</strong> — switch between manual sorting and sorting by field.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace states in colour', 'content' => '<p>On TYPO3 v14 every row gets its workspace overlay before search and filters run.</p><ul><li><strong>Colour-coded states</strong> — new (blue), modified (purple), moved (cyan), deleted (red).</li><li><strong>Search after overlay</strong> — draft rows replace live rows before filtering.</li><li><strong>Check before publishing</strong> — staged changes are visible and searchable in every view.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Field display by type, with thumbnails', 'content' => '<p>Each field type has its own display, set per table in TSconfig.</p><ul><li><strong>Formatting</strong> — booleans as badges, dates in monospace, relations as counts and links you can click.</li><li><strong>Thumbnails</strong> — FAL references turn into thumbnail URLs automatically.</li><li><strong>Context</strong> — language flags in the footer, and a hint marks images that only show in the backend.</li><li><strong>Per table</strong> — products show prices, staff show portraits, news show feature images.</li></ul>', 'open_by_default' => 0],
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
                    'header' => 'Give editors better views of their records',
                    'description' => 'Grid cards for visual content, compact tables for large data sets, teasers for editorial content and your own layouts for the rest. You configure all of them without PHP.',
                    'cta_text' => 'View on GitHub',
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
            'title' => 'MCP Server: TYPO3 content for AI assistants',
            'navTitle' => 'MCP Server',
            'slug' => '/features/mcp-server',
            'abstract' => 'An MCP server that gives AI assistants structured access to TYPO3 content, records, files and workflows, without touching live data. The same tools run on the CLI.',
            'description' => 'Model Context Protocol server for TYPO3 v14. Workspace-safe tools for pages, records, files and editorial work, over OAuth for MCP clients or on the CLI.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'AI and automation',
                    'header' => 'Connect AI assistants to TYPO3',
                    'subheadline' => 'Claude, Cursor and other MCP clients get structured access to pages, records, files and publishing. Every write goes through workspaces and the DataHandler.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What the Model Context Protocol does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'MCP is an open standard from Anthropic for how AI assistants call tools. This extension makes TYPO3 one of those tools.',
                    'content' => '<p><strong>MCP Server</strong> turns TYPO3 functions into MCP tools that any compatible assistant can call.</p><ul><li><strong>Dozens of tools</strong> — read page trees, search records, attach images, translate, check metadata and publish workspaces.</li><li><strong>Workspace-safe</strong> — every write is staged first, so live data stays unchanged.</li><li><strong>Any client</strong> — OAuth 2.1 for remote clients, stdio for local development.</li><li><strong>Same tools in CI</strong> — shell scripts and GitHub Actions use the same CLI commands.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-mcp-server',
                    'media' => ShowcaseBlocks::screenshot('feature-mcp-server.png', 'MCP Server backend module', 'MCP Server connection setup with the server URL, Cursor configuration, stdio command and OAuth tokens.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What you get',
                    'eyebrow' => 'AI and automation',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'lock', 'title' => 'Workspace-safe by default', 'description' => 'Every content write is staged in a TYPO3 workspace first. Live content changes only when someone publishes, so editors review everything before it goes live.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Tools read TCA directly', 'description' => 'Tools take their field definitions from TYPO3 TCA, not from hand-written adapters. News records, custom FlexForms and language overlays work without MCP-specific code.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Same tools everywhere', 'description' => 'Every MCP tool works in Claude Desktop, Cursor, n8n or on the CLI with vendor/bin/typo3 mcp:command. One set of tools serves many clients.', 'link' => ''],
                        ['icon' => 'database', 'title' => 'Clear schemas for every tool', 'description' => 'Every tool has a description, a JSON Schema for its input and MCP annotations that say whether it reads, writes or deletes. Any MCP client can use it.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'MCP tools in nine groups', 'content' => '<p>The tools fall into <strong>nine groups</strong>. Each tool is an MCP endpoint and a CLI shortcut.</p><ul><li><strong>Navigate and inspect</strong> — GetCapabilities, ListTables, GetTableSchema, GetFlexFormSchema.</li><li><strong>Read and write</strong> — record reads with TCA context, structured writes and bulk edits.</li><li><strong>Publish and files</strong> — workspace review, sandboxed file handling and content audits.</li><li><strong>Operate</strong> — system diagnostics, site and extension admin, and helpers that only run on DDEV.</li></ul><p>Tool names use PascalCase and match what editors know from the backend.</p>', 'open_by_default' => 1],
                        ['title' => 'Writes go to a workspace first', 'content' => '<p>Record writes go into a workspace by default. The extension picks or creates one automatically.</p><ul><li><strong>Live UIDs only</strong> — clients see the stable UID, never the internal workspace version.</li><li><strong>Dry run first</strong> — publish and rollback show what would happen before they run.</li><li><strong>Strict in production</strong> — live edits need an explicit workspace_id and admin rights.</li><li><strong>Relaxed locally</strong> — on DDEV you can allow live edits for speed.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'OAuth for remote clients, stdio for local', 'content' => '<p>There are two ways to connect, and TYPO3 permissions apply to both.</p><ul><li><strong>Remote</strong> — OAuth 2.1 with PKCE at <code>/mcp</code>. The first request logs in with your backend credentials.</li><li><strong>Local</strong> — clients like Cursor run <code>mcp:server</code> as a trusted subprocess, without OAuth.</li><li><strong>Discovery</strong> — clients find the OAuth server and protected resources automatically.</li><li><strong>Backend module</strong> — endpoint URL, one-click Cursor setup, Claude Desktop config, health checks and tokens.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'A manifest declares what tools may do', 'content' => '<p>A YAML manifest (<code>Configuration/Capabilities.yaml</code>) declares what each tool may do.</p><ul><li><strong>Switch subsystems</strong> — database:read/write, file:write, render:frontend, workspace:write and more.</li><li><strong>Immediate effect</strong> — remove database:write and every writing tool stops.</li><li><strong>Internal by default</strong> — outbound HTTP stays internal until you allow it.</li><li><strong>Check it live</strong> — see the active capabilities with <code>mcp:get-capabilities --json</code>.</li></ul><p>It is not a security boundary, because TYPO3 permissions still apply. It is a simple way to harden a setup without code.</p>', 'open_by_default' => 0],
                        ['title' => 'Every tool also runs on the CLI', 'content' => '<p>Every MCP tool is also a TYPO3 console command.</p><ul><li><strong>Shortcuts</strong> — <code>mcp:read-table</code>, <code>mcp:write-table</code>, <code>mcp:search</code>, <code>mcp:list-workspaces</code> and more.</li><li><strong>Generic runner</strong> — <code>mcp:tool &lt;ToolName&gt;</code> with the <code>--param</code> and <code>--params</code> options.</li><li><strong>Three output modes</strong> — pretty for people, plain for logs, JSON for jq, agents and CI.</li><li><strong>Safe in scripts</strong> — JSON output has an ok/error envelope, and parameter files must stay inside the project root.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Relaxed rules for local development', 'content' => '<p>On DDEV or in the Development context, three safety rules relax for speed.</p><ul><li><strong>Live writes</strong> — record writes go live by default instead of needing a draft workspace_id.</li><li><strong>Any storage</strong> — file operations accept any path, not only fileadmin/mcp.</li><li><strong>Open outbound HTTP</strong> — UploadFileFromUrl and RenderRecord work on local and staging hosts.</li><li><strong>Production stays strict</strong> — the <code>mcpServer.strictSandbox</code> flag forces strict mode anywhere.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Audit, preview and import tools', 'content' => '<p>These tools let an assistant edit, check and repeat without a manual backend refresh.</p><ul><li><strong>ContentAudit</strong> — lists missing alt texts, missing descriptions and hidden slugs, sorted by severity.</li><li><strong>GetPreviewUrl</strong> — a signed workspace preview link, right in the chat.</li><li><strong>RenderRecord</strong> — the real rendered HTML, so the AI sees the frontend output before publishing.</li><li><strong>ImportContent / ImportFromUrl</strong> — turn text, Markdown or HTML into content elements.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'File sandbox and secure uploads', 'content' => '<p>File tools are limited to <code>fileadmin/mcp/</code> by default.</p><ul><li><strong>Path traversal protection</strong> — stays on, even in local mode.</li><li><strong>SSRF-checked uploads</strong> — UploadFileFromUrl checks the remote host against your outbound policy.</li><li><strong>Relaxed on DDEV</strong> — allow any host for staging and test servers.</li><li><strong>FAL-aware</strong> — attaching an image creates a real sys_file_reference, not a broken hard link.</li></ul>', 'open_by_default' => 0],
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
                    'header' => 'Connect your AI assistant to TYPO3',
                    'description' => 'Install the extension, open the MCP Server module and paste the configuration into Claude Desktop, Cursor or n8n. The first request logs you in through OAuth.',
                    'cta_text' => 'View on Packagist',
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
            'title' => 'Easy Workspace: publish from the TYPO3 toolbar',
            'navTitle' => 'Easy Workspace',
            'slug' => '/features/easy-workspace',
            'abstract' => 'Easy Workspace adds one-click publishing to TYPO3 workspaces. Editors review the pending changes of a page, select what to publish and publish it with all related records.',
            'description' => 'A publishing toolbar for TYPO3 v14 workspaces. Review pending changes, select rows and publish them together, using TYPO3\'s own versioning.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Backend extension',
                    'header' => 'Editors see what they\'re about to publish',
                    'subheadline' => 'A toolbar button and a backend module show the pending workspace changes of the current page or news article. Editors publish them together in one click.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Workspace publishing in the top bar',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'In standard TYPO3, workspace publishing sits three screens deep and lists everything at once. Easy Workspace puts it in the top bar and shows only what is pending.',
                    'content' => '<p>Before publishing, editors need to see what changed. <strong>Easy Workspace</strong> shows that review in the top-right corner, without searching through modules.</p><ul><li><strong>Check readiness</strong> — every content element you added is staged and ready.</li><li><strong>Children included</strong> — inline children publish with their parents.</li><li><strong>No surprises</strong> — you see which changes go live together.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-webcon-easy-workspace',
                    'media' => ShowcaseBlocks::screenshot('feature-easy-workspace.png', 'Pending changes review panel', 'The Easy Workspace review module with two changed records, checkboxes, state labels and publish controls.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What editors get',
                    'eyebrow' => 'Backend extension',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Publishing takes one click', 'description' => 'The list of pending changes opens from the toolbar. Editors don\'t search through modules or sub-tabs. They click the paper-plane icon and the review opens.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'Editors know what publishes together', 'description' => 'Easy Workspace lists the page and content elements that publish as one batch. It collects inline children automatically, so no child element gets left behind.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Uses TYPO3\'s own versioning', 'description' => 'The extension uses the standard DataHandler and built-in workspace versioning. There is no custom publishing pipeline and no separate content store.', 'link' => ''],
                        ['icon' => 'globe', 'title' => 'Works for pages and news articles', 'description' => 'The same toolbar and module work for page trees and georgringer/news detail views. Editors see one publish interface for pages and news.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Toolbar dropdown with a live change count', 'content' => '<p>A paper-plane icon in the top bar lights up as soon as the current page or article has pending changes.</p><ul><li><strong>One-click review</strong> — opens a Lit-rendered dropdown that loads every pending record over AJAX.</li><li><strong>Live count</strong> — a lightweight polling endpoint keeps the badge current.</li><li><strong>Out of the way</strong> — the toolbar hides itself in the Live workspace.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Review table with checkboxes and type labels', 'content' => '<p>Every changed record appears in one dense table: the page, its content elements and inline children.</p><ul><li><strong>Labels from TCA</strong> — type names come from TCA, not hard-coded strings.</li><li><strong>Clear state</strong> — every row shows its title and a changed-versus-live badge.</li><li><strong>Selected by default</strong> — most editors publish everything, and you can deselect any change.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Bulk publish, parents before children', 'content' => '<p>Publishing sends the selected records to the TYPO3 DataHandler in a set order.</p><ul><li><strong>Parents first</strong> — pages and top-level elements publish before their inline children.</li><li><strong>No broken links</strong> — foreign keys point at live records, not workspace placeholders.</li><li><strong>Predictable</strong> — each request runs in the active workspace and has a server-side limit.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Discard one row, keep the rest', 'content' => '<p>If a change isn\'t ready, discard that row and leave everything else staged.</p><ul><li><strong>Native discard</strong> — runs the TYPO3 v14 discard command on that workspace version only.</li><li><strong>Nothing else changes</strong> — sibling records stay as they are.</li><li><strong>Decide per row</strong> — publish or discard each record on its own.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Field diff with history and rollback', 'content' => '<p>Click a row to see what changed, with the old value next to the new one.</p><ul><li><strong>Inline diffs</strong> — longer text is compared word by word.</li><li><strong>History timeline</strong> — edits to the record, read from sys_history via the RecordHistory service.</li><li><strong>Rollback</strong> — restore one field or the whole record without changing the rest of the page.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Find a record in the preview', 'content' => '<p>An eye icon on each row finds that record in the frontend preview.</p><ul><li><strong>Jump to it</strong> — scrolls to the element by its #c{uid} anchor and outlines it briefly.</li><li><strong>Visual Editor support</strong> — uses the Visual Editor iframe if there is one, otherwise the standard Viewpage preview.</li><li><strong>Children point to parents</strong> — child rows highlight their parent element, so the target is always visible.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'A workspace chip and three module views', 'content' => '<p>A chip in the header shows the active workspace, so you always know when you are working in Live.</p><ul><li><strong>Open items</strong> — the changes waiting to be published.</li><li><strong>All records</strong> — a read-only list of every record in scope on the page.</li><li><strong>Checks and diagnostics</strong> — a workspace integrity scan and a manual risk list.</li></ul><p>The module sits under Content, just below the standard Workspaces module.</p>', 'open_by_default' => 0],
                        ['title' => 'A publish queue for each news article', 'content' => '<p>When you edit a georgringer/news article, Easy Workspace limits the scope to that article.</p><ul><li><strong>Related content</strong> — includes elements linked through tx_news_related_news.</li><li><strong>Only this article</strong> — the toolbar shows its pending changes, not the whole page tree.</li><li><strong>Direct link</strong> — pass a newsUid parameter to open the queue of a specific article.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Database checks and a manual risk list', 'content' => '<p>The Checks and diagnostics view scans the workspace for integrity problems.</p><ul><li><strong>Common problems</strong> — stale version fields, orphaned versions, missing parents, file references without an owner, duplicates.</li><li><strong>Grouped results</strong> — pass, warning and error, as in the Reports module.</li><li><strong>Manual risk list</strong> — edge cases the scanner can\'t judge, such as overwritten FAL files.</li></ul>', 'open_by_default' => 0],
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
                    'header' => 'One-button publishing for your editors',
                    'description' => 'Easy Workspace turns workspace publishing from an expert task into one button. It is free, runs on TYPO3 v14.3 and works with its default settings.',
                    'cta_text' => 'View on GitHub',
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
            'title' => 'The TYPO3 Blog extension: posts are pages',
            'navTitle' => 'Blog',
            'slug' => '/features/blog',
            'abstract' => 'The TYPO3 Blog extension builds a blog from core concepts. Posts are pages, their content is ordinary content elements, and editors stage posts and authors in workspaces.',
            'description' => 'The TYPO3 Blog extension: posts as pages, workspace staging, 20 plugins and moderated comments, with templates from Desiderio.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Blog extension',
                    'header' => 'Your blog, built on TYPO3 core',
                    'subheadline' => 'Posts are pages and their content is ordinary content elements. Editors manage everything in the page module, like the rest of the site.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Nothing new to learn for editors',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Editors write posts in the page module, with the content elements they already use.',
                    'content' => '<p>One principle: <strong>if you know TYPO3, you already know how to run the blog.</strong> There is no separate editor, no custom tables and no workflow outside your page tree.</p><ul><li><strong>Posts are pages</strong> — doktype 137. Authors, tags and categories use core concepts.</li><li><strong>All your elements</strong> — every content element and backend layout works inside a post.</li><li><strong>Staged in workspaces</strong> — posts, tags and authors. Comments stay live.</li><li><strong>Included</strong> — backend modules and 20 plugins for lists, filters, archives, sidebars and RSS.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/TYPO3GmbH/blog',
                    'media' => ShowcaseBlocks::screenshot('feature-blog.png', 'Blog post in the page module', 'A blog post in the TYPO3 page module, with the title in the page header and badges for date, categories, tags and author.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What you get',
                    'eyebrow' => 'Blog extension',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'file', 'title' => 'Posts are pages', 'description' => 'Create and manage posts in the page module with any content element. Posts, tags and authors are staged in workspaces, and comments stay live.', 'link' => ''],
                        ['icon' => 'menu', 'title' => '20 plugins for every layout', 'description' => '20 Extbase plugins cover lists, filters, archives, sidebars and RSS feeds. Readers filter by category, tag, author or date, and lists are paginated with clean markup.', 'link' => ''],
                        ['icon' => 'history', 'title' => 'Editorial workflow with workspaces', 'description' => 'Posts, tags and authors are staged before publishing. Comments are moderated, with email notifications to the author and an admin. Google reCAPTCHA is optional.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Posts are pages with their own doktype', 'content' => '<p>Blog posts are TYPO3 pages (doktype 137/138), not rows in a separate table.</p><ul><li><strong>Edit like any page</strong> — create posts in the page module and drag them in the tree.</li><li><strong>Same permissions</strong> — the access rules you already use apply.</li><li><strong>All elements</strong> — every content element and backend layout works inside a post.</li><li><strong>Metadata at the top</strong> — the page header shows date, tags, categories and author.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'All your content elements, all your layouts', 'content' => '<p>Every part of a post is a standard content element. There is no separate editor to learn.</p><ul><li><strong>Familiar elements</strong> — an article hero at the top, then text, images and quotes.</li><li><strong>Custom elements</strong> — anything registered on your site works inside a post.</li><li><strong>Backend layouts</strong> — they apply exactly as on campaign or landing pages.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace-safe staging and publishing', 'content' => '<p>Posts, tags and authors work with workspaces. Create them, check them in the preview, then publish them together.</p><ul><li><strong>Hidden until ready</strong> — staged changes never show on the live site.</li><li><strong>Except comments</strong> — visitor comments stay live, so readers can keep commenting.</li><li><strong>Clear separation</strong> — plan a publication day without drafts appearing in production.</li></ul>', 'open_by_default' => 0],
                        ['title' => '20 plugins for lists, sidebars and feeds', 'content' => '<p>20 Extbase plugins cover the whole blog, most of them in the content element wizard.</p><ul><li><strong>Lists</strong> — all posts, the latest N, or an archive by month with pagination.</li><li><strong>Filters</strong> — by category, tag or author.</li><li><strong>Sidebars</strong> — related posts, tag clouds, category lists, recent posts and the comment form.</li><li><strong>Feeds</strong> — RSS for subscribers.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Categories, tags and authors with metadata', 'content' => '<p>Posts are organised with system categories and blog tags, and workspaces version both.</p><ul><li><strong>Authors are records</strong> — with avatar, social links, a bio and an author page.</li><li><strong>Related posts</strong> — ranked by shared categories and tags.</li><li><strong>Reader filters</strong> — by category, tag, author or archive date.</li><li><strong>Staged together</strong> — add a new author in the same workspace as their first posts.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Comment moderation and reCAPTCHA', 'content' => '<p>The built-in comment system moves each comment through pending, approved, declined or deleted.</p><ul><li><strong>Notifications</strong> — an email to the post author and to a set admin for every new comment.</li><li><strong>Spam control</strong> — optional Google reCAPTCHA per site.</li><li><strong>Always live</strong> — comments are written to the live database, never held in a workspace.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Three site sets and plain Fluid templates', 'content' => '<p>Three public site sets cover the common setups.</p><ul><li><strong>standalone</strong> — a site that is only a blog.</li><li><strong>integration</strong> — a blog inside an existing site.</li><li><strong>bootstrap-53</strong> — the included Bootstrap 5.3 frontend templates.</li></ul><p>Every template is plain Fluid, so you can override it in your site package. Desiderio adds shadcn-styled templates with dark mode that follow the active preset.</p>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'composer.json',
                    'code' => 'composer require t3g/blog',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Install the blog with one Composer command',
                    'description' => 'The Blog extension is free under GPL-2.0, with backend modules, 20 Extbase plugins and workspace support. Set up a blog in the setup module, or customise the Fluid templates in your site package. Thank you to TYPO3 GmbH.',
                    'cta_text' => 'View on GitHub',
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
            'title' => 'Desiderio and Innesto for TYPO3',
            'navTitle' => 'Desiderio + Innesto',
            'slug' => '/features/desiderio',
            'abstract' => 'Desiderio gives TYPO3 244 content elements built from 62 typed Fluid 5 components, themed in the site settings. Innesto adds shadcn/ui registry components as new elements.',
            'description' => '244 shadcn/ui-styled content elements for TYPO3 v14.3: runtime themes, typed Fluid 5 components, Content Blocks 2.2 and Innesto to extend it. GPL-2.0.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Design system',
                    'header' => '244 content elements you can extend',
                    'subheadline' => 'Desiderio brings shadcn/ui to TYPO3 v14.3 or newer, with 244 content elements and 62 Fluid 5 components. Innesto adds registry components as new Content Blocks in one command.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What Desiderio and Innesto do',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'An open-source design system for TYPO3, and a tool to extend it.',
                    'content' => '<p><strong>Desiderio</strong> is a finished editorial system for TYPO3 v14.3 or newer, not a template kit you still have to build.</p><ul><li><strong>244 elements, no build step</strong> — page templates, optional Blog, News, Solr and Powermail overrides, and seeded demo content.</li><li><strong>Extensible with Innesto</strong> — add a component from any shadcn registry (shadcn/ui, Magic UI, blocks.so) as a new Content Block.</li><li><strong>Yours to keep</strong> — both are free and open source under GPL-2.0-or-later.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/desiderio',
                    'media' => ShowcaseBlocks::screenshot('feature-desiderio.png', 'Desiderio in the TYPO3 Visual Editor', 'The TYPO3 Visual Editor with a Desiderio page open for editing.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What you get',
                    'eyebrow' => 'Design system',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => '244 elements, no build step', 'description' => 'Heroes, pricing tables, testimonials, forms, charts and footers are finished and ready to use. Install the package, enable the site sets, and editors can start building pages.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'Theme presets that switch at runtime', 'description' => 'Paste a preset from ui.shadcn.com/create into the site settings, and the whole site changes without a rebuild. 15 presets and 5 icon libraries are included.', 'link' => ''],
                        ['icon' => 'menu', 'title' => 'Atomic components, typed contracts', 'description' => '17 atoms, 37 molecules, 4 layouts and 4 organisms make up all 244 elements. Typed f:argument contracts let one audit check every element and CI reject broken templates.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Extend it with Innesto', 'description' => 'Innesto adds a shadcn registry component as a new Content Block in one command. It converts the styling to semantic tokens, and the optional --ai flag converts React to Fluid.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => '62 typed Fluid 5 components in layers', 'content' => '<p>The components follow atomic design in Fluid 5.</p><ul><li><strong>17 atoms</strong> — button, badge, input, icon, avatar, link, image and label (<code>d:atom</code>).</li><li><strong>37 molecules</strong> — card, accordion, table, alert and form controls (<code>d:molecule</code>).</li><li><strong>4 layouts</strong> — section, container, grid and stack (<code>d:layout</code>).</li><li><strong>4 organisms</strong> — site header, footer, breadcrumb and page header (<code>d:organism</code>).</li><li><strong>Typed contracts</strong> — every component declares its <code>f:argument</code> types. The API is enforced, not a convention.</li></ul><p>All 244 elements are built from these layers, so one audit can check every element.</p>', 'open_by_default' => 1],
                        ['title' => '244 content elements in 10 groups', 'content' => '<p>244 elements for editors, in clear groups.</p><ul><li><strong>Marketing</strong> — heroes, feature blocks, pricing, trust and social proof.</li><li><strong>Data</strong> — dashboard elements with chart helpers.</li><li><strong>Structure</strong> — navigation, footers, legal pages, forms and editorial content.</li></ul><p>Each element appears in the New Content Element wizard with a backend preview. The preview uses the active preset, so it looks like the real page, not a wireframe.</p>', 'open_by_default' => 0],
                        ['title' => 'Theme presets set in the site settings', 'content' => '<p>Themes are CSS tokens applied at runtime and chosen in the TYPO3 site settings.</p><ul><li><strong>No rebuild</strong> — a new preset changes colours, radius, density, focus rings and fonts at once.</li><li><strong>15 presets</strong> — 5 from ui.shadcn.com/create, 10 of our own, plus a custom slot.</li><li><strong>Per page tree</strong> — run campaigns or brands in their own theme from one installation.</li><li><strong>Icon keys</strong> — semantic icon keys let you switch the icon library without editing records.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Innesto adds registry components', 'content' => '<p>Innesto makes Desiderio extensible, without a frontend build step on your site.</p><ul><li><strong>One command</strong> — <code>innesto:add &lt;component&gt;</code> fetches the JSON, converts the styling to tokens and scaffolds the element.</li><li><strong>Any registry</strong> — shadcn/ui, Magic UI, blocks.so or any other registry that publishes JSON.</li><li><strong>Finishing step</strong> — convert the markup to Fluid and model the props by hand, or let <code>--ai</code> do it.</li><li><strong>On theme</strong> — every added element uses the active Desiderio preset.</li></ul><p>Presentational components such as marquees, logo clouds and bento grids fit best.</p>', 'open_by_default' => 0],
                        ['title' => 'Content Blocks 2.2 with backend previews', 'content' => '<p>All 244 elements are Content Blocks (friendsoftypo3/content-blocks ^2.2), not classic plugins.</p><ul><li><strong>Declarative schemas</strong> — one <code>config.yaml</code> per element, with database columns created automatically.</li><li><strong>Backend previews</strong> — editors see the element before publishing.</li><li><strong>Named child tables</strong> — collection records map to named tables.</li><li><strong>Portable</strong> — export an element with its records, and the schema creates the tables elsewhere.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Templates for News, Blog, Solr and Powermail', 'content' => '<p>The extensions you already use get the same shadcn look through opt-in site sets.</p><ul><li><strong>Active when installed</strong> — templates for georgringer/news, t3g/blog, Solr and in2code/powermail switch on when the extension is present.</li><li><strong>Forms</strong> — Form Framework templates with Friendly Captcha and a Brevo double opt-in finisher.</li><li><strong>Theme follows</strong> — switch the preset, and news lists, search results and forms change with it.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Commands that seed demo content', 'content' => '<p>Symfony console commands create the demo content for you.</p><ul><li><strong>styleguide:seed</strong> — builds the full demo site with 244 elements from YAML. It is idempotent and live-safe.</li><li><strong>starter:seed</strong> — creates a corporate starter site with demo content.</li><li><strong>blog:seed-pages</strong> — switches an existing Blog tree to Desiderio layouts.</li><li><strong>Safeguards</strong> — seeders refuse to run in a workspace, or in Production without <code>--allow-production</code>.</li></ul>', 'open_by_default' => 0],
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
                    'header' => 'Extend your TYPO3 site with Desiderio',
                    'description' => 'Desiderio and Innesto are free and open source under GPL-2.0-or-later. You get all 244 elements, 15 theme presets, the integration sets and the seeding tools, with no licence key.',
                    'cta_text' => 'Get started free',
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
            'title' => 'Apache Solr search for TYPO3',
            'navTitle' => 'Solr Search',
            'slug' => '/features/solr',
            'abstract' => 'Apache Solr provides fast search for TYPO3, and Desiderio styles it with shadcn components. Results, facets, sorting, suggestions and pagination follow the active theme, in light and dark mode.',
            'description' => 'Search for TYPO3 with Apache Solr: shadcn-styled results, facets, numbered pagination and AJAX filtering, with no styling work of your own.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Integration',
                    'header' => 'Solr search in your design system',
                    'subheadline' => 'Solr results render in shadcn components that follow your theme preset. No template work is needed.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Styled search results, ready to use',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Apache Solr for TYPO3 is maintained by dkd Internet Service GmbH and the TYPO3-Solr team. Desiderio adds a complete shadcn template set.',
                    'content' => '<p><strong>Solr finds the content, and Desiderio styles the interface.</strong></p><ul><li><strong>Fully themed</strong> — results, facets, sorting, results per page and pagination follow your preset, in light and dark mode.</li><li><strong>Ready to use</strong> — the template set is complete, with no CSS to write.</li><li><strong>AJAX filtering</strong> — facets, sorting and paging refresh without a full page reload.</li><li><strong>Follows the site</strong> — switch from Lagoon to Midnight and the results page changes too.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/TYPO3-Solr/ext-solr',
                    'media' => ShowcaseBlocks::screenshot('feature-solr.png', 'Apache Solr Index Queue in TYPO3', 'The Solr Index Queue in the TYPO3 backend, with index status counts and controls to initialise the queue.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What you get',
                    'eyebrow' => 'Integration',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'No styling work', 'description' => 'Results, facets, pagination, sorting and results per page come styled. Copy the template set, point your results page to the Desiderio Solr Results template in TypoScript, and search is ready.', 'link' => ''],
                        ['icon' => 'sparkles', 'title' => 'Search follows the theme', 'description' => 'Search results use the active preset and the dark-mode setting. Switch the site to Midnight, and pagination, facets, cards and buttons change with it.', 'link' => ''],
                        ['icon' => 'search', 'title' => 'Facet filtering over AJAX', 'description' => 'Facets appear as a themed sidebar with live result counts. Selecting, removing, sorting or paging refreshes the results over AJAX. TypoScript decides which facets appear.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Accessibility built in', 'description' => 'Pagination sits in a nav landmark, with aria-current and a descriptive aria-label on each link. Filters, facet counts and status messages have screen-reader text. Every control has a focus ring.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Search form with live suggestions', 'content' => '<p>A search field with icon and submit button shows live suggestions as you type.</p><ul><li><strong>Grouped suggestions</strong> — under translated labels for Pages, News and Addresses.</li><li><strong>Configurable header</strong> — a \'Top Results\' label heads the list.</li><li><strong>Straight to results</strong> — submit or pick a suggestion, with no custom code.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Result cards with excerpt and type badge', 'content' => '<p>Each result is a card built from Desiderio tokens.</p><ul><li><strong>Easy to scan</strong> — title link, result URL and an excerpt with your search term highlighted.</li><li><strong>Type badge</strong> — shows the source as Pages, News or Addresses. Files show their MIME type.</li><li><strong>Semantic markup</strong> — titles use heading tags, and one token change restyles every card.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Numbered pagination with truncation', 'content' => '<p>Page numbers in order, with previous and next buttons.</p><ul><li><strong>Truncation</strong> — long ranges collapse behind an ellipsis, so the bar never overflows.</li><li><strong>Clear current page</strong> — it has a solid primary background, and the others use the outline style.</li><li><strong>Accessible</strong> — a nav landmark with aria-label, aria-current, and a \'Go to page N\' label on each link.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Toolbar with sorting and page size', 'content' => '<p>A toolbar above the results offers sorting and a results-per-page switch.</p><ul><li><strong>Sort menu</strong> — relevance, date, title and any field Solr returns, with the active option and direction marked.</li><li><strong>Results per page</strong> — a native select that submits again on change.</li><li><strong>From configuration</strong> — the options come from your Solr per-page settings, not from hard-coded values.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Active filters with one-click removal', 'content' => '<p>When facets narrow a search, a \'Narrowed by\' bar lists each active filter.</p><ul><li><strong>Removable chips</strong> — each filter is a themed link with a clear remove control and screen-reader text.</li><li><strong>Reset in one step</strong> — a \'Remove all filters\' action clears everything.</li><li><strong>No full reload</strong> — removing a filter refreshes the results over AJAX.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Facet sidebar with live result counts', 'content' => '<p>A sidebar lists the facets Solr is configured to return, each in its own section.</p><ul><li><strong>Live counts</strong> — every option shows its number of results next to the label.</li><li><strong>Show more</strong> — shows options beyond the configured limit.</li><li><strong>AJAX filtering</strong> — choosing an option adds it to the active filters and updates the results.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Did-you-mean and auto-correct messages', 'content' => '<p>Spelling help when a search finds little.</p><ul><li><strong>Did you mean</strong> — each suggestion is a link that runs the corrected search.</li><li><strong>Auto-correct notice</strong> — explains when results are shown for a corrected term.</li><li><strong>Readable in both modes</strong> — translated texts that stay readable in light and dark mode.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Frequent and recent searches', 'content' => '<p>Optional panels show past searches next to the results.</p><ul><li><strong>Frequent searches</strong> — styled like the facet sidebar.</li><li><strong>Last searches</strong> — recent searches as themed links.</li><li><strong>Click to search again</strong> — each link starts a new search and refreshes over AJAX.</li><li><strong>Separate switches</strong> — each panel has its own TypoScript setting.</li></ul>', 'open_by_default' => 0],
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
                    'header' => 'Add Solr search to your design system',
                    'description' => 'The template set comes with Desiderio, and Solr is open source. Thank you to dkd Internet Service GmbH and the TYPO3-Solr team for the extension.',
                    'cta_text' => 'See technical details',
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
            'title' => 'WorkOS single sign-on for TYPO3',
            'navTitle' => 'WorkOS Auth',
            'slug' => '/features/workos',
            'abstract' => 'WorkOS Auth adds single sign-on to the TYPO3 frontend and backend. One extension covers both login screens and team management for business customers.',
            'description' => 'WorkOS single sign-on for the TYPO3 frontend and backend: email and password, magic links, social login and B2B team management.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Login and SSO',
                    'header' => 'One login for frontend and backend',
                    'subheadline' => 'WorkOS handles login for your frontend and backend, with magic links, social sign-in and team management for your business customers.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What WorkOS does for your TYPO3 site',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'More than an OAuth provider: WorkOS also manages organisations and teams.',
                    'content' => '<p><strong>WorkOS provides enterprise single sign-on</strong>, and workos_auth connects it to TYPO3 at the authentication level.</p><ul><li><strong>Every sign-in type</strong> — email and password, magic links, and OAuth for Google, Microsoft, GitHub and Apple.</li><li><strong>B2B layer</strong> — organisations, invitations, roles and admin portals.</li><li><strong>One identity</strong> — backend login and frontend plugins use the same WorkOS account.</li><li><strong>Enterprise controls</strong> — your customers\' IT admins get the team and audit tools they expect.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/workos',
                    'media' => ShowcaseBlocks::screenshot('feature-workos.png', 'MCP settings protected by WorkOS', 'MCP Server settings in TYPO3 with WorkOS protection, endpoint settings and a placeholder AuthKit domain.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What you get',
                    'eyebrow' => 'Login and SSO',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'lock', 'title' => 'One identity for backend and frontend', 'description' => 'Backend and frontend use the same WorkOS account. Customers log in with the identity they use for their organisation, so there is no user sync and no duplicate address.', 'link' => ''],
                        ['icon' => 'users', 'title' => 'B2B team workspace built in', 'description' => 'Invite teammates by email, set roles, manage sessions and open the WorkOS Admin Portal from a frontend plugin. Customers set up their teams without contacting support.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Passwordless sign-in included', 'description' => 'Users choose email and password, magic links or social OAuth. Magic-auth codes work in the frontend and the backend, so fewer people need a password reset.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Start on the free tier', 'description' => 'Start on the WorkOS free tier. Enterprise customers expect SSO, audit logs and SCIM, and the Admin Portal covers them. Setup is one page in TYPO3, with no custom code.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Frontend login: email, magic auth and social', 'content' => '<p>Place the WorkOS Login element on any page to get a finished sign-in card, with no template work.</p><ul><li><strong>Signed out</strong> — email and password, a \'Send me a code\' option for magic auth, and buttons for Google, Microsoft, GitHub and Apple.</li><li><strong>Verification</strong> — an inline form with a resend option when WorkOS asks for it.</li><li><strong>Signed in</strong> — the user\'s WorkOS profile and custom metadata, plus Sign Out.</li><li><strong>Safe</strong> — CSRF-protected, and after a validation error the form keeps the entered data.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Backend login with a WorkOS option', 'content' => '<p>The TYPO3 backend login gets a WorkOS section next to the classic username and password form.</p><ul><li><strong>Hosted or direct</strong> — \'Continue with WorkOS\' opens AuthKit, or use social buttons and magic auth by email directly.</li><li><strong>No leaks</strong> — magic-auth and verification state is stored on the server, bound to an HttpOnly cookie, never in the URL.</li><li><strong>Works with strict cookies</strong> — a same-origin continuation page sends the session cookie at the right moment.</li><li><strong>Native hand-off</strong> — TYPO3 writes its login logs, fires its events, protects against session fixation and runs any backend MFA.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Self-service profile, MFA and sessions', 'content' => '<p>Place the WorkOS account plugin on a private page for self-service cards. Each card uses the WorkOS API and degrades gracefully if a call fails.</p><ul><li><strong>Profile</strong> — change first and last name, synced back to WorkOS.</li><li><strong>Password</strong> — change it on the site, with clear errors for weak or breached passwords.</li><li><strong>Two-factor</strong> — add an authenticator app with an inline QR code, or enter the secret by hand.</li><li><strong>Sessions</strong> — list every session with IP, device and expiry, and end any of them.</li><li><strong>Organisations</strong> — memberships, roles and a Directory Sync badge.</li></ul><p>Every action that changes data is CSRF-protected and checks ownership.</p>', 'open_by_default' => 0],
                        ['title' => 'Team plugin: invitations and admin portals', 'content' => '<p>The Team plugin turns a frontend page into a team management console for organisation admins.</p><ul><li><strong>Organisation switcher</strong> — choose which organisation to manage when a user belongs to several. The choice is kept for the session.</li><li><strong>Invite</strong> — send invitations by email with an optional role. WorkOS handles delivery and tracking.</li><li><strong>Track</strong> — pending invitations with state badges, expiry, and resend or revoke buttons.</li><li><strong>Admin Portal</strong> — six signed links for SSO, Directory Sync (SCIM), Audit Logs, Log Streams, Domain Verification and Certificate Renewal.</li></ul><p>Every action checks that the user is an active admin or owner, with CSRF and ownership checks.</p>', 'open_by_default' => 0],
                        ['title' => 'Provisioning and identity mapping', 'content' => '<p>Signing in with WorkOS creates or links the matching TYPO3 user automatically.</p><ul><li><strong>One mapping table</strong> — tx_workosauth_identity stores the WorkOS ID, email and full profile JSON.</li><li><strong>Safe storage</strong> — admin-only, hidden from the page tree and excluded from versioning.</li><li><strong>No duplicates</strong> — later logins use the existing link.</li><li><strong>Controlled creation</strong> — reject unlinked logins, or create users automatically when the email domain is on an allowlist.</li></ul><p>Backend and frontend users are provisioned separately with the same mechanism.</p>', 'open_by_default' => 0],
                        ['title' => 'Three backend modules for admins', 'content' => '<p>A WorkOS menu at the top level, for admins only, adds three modules.</p><ul><li><strong>Setup Assistant</strong> — lists the redirect URIs to register and copies them in one click. It stores your API key, Client ID and cookie password, with no PHP editing.</li><li><strong>User Management</strong> — the official WorkOS widget to invite users, change roles and remove users, with CSRF protection.</li><li><strong>MCP Server</strong> — set up the optional MCP endpoint, auth mode, limits and logging, and run the schema migration.</li></ul><p>All three are registered for the LIVE workspace only.</p>', 'open_by_default' => 0],
                        ['title' => 'Login URLs with query parameters', 'content' => '<p>The frontend login URL accepts query parameters that change AuthKit without configuration changes.</p><ul><li><strong>screen=</strong> — open on sign-up or sign-in.</li><li><strong>provider=</strong> — go straight to Google, Microsoft, GitHub or Apple.</li><li><strong>login_hint=</strong> — fill in the email field.</li><li><strong>organization=</strong> — limit the login to one organisation.</li><li><strong>returnTo=</strong> — redirect after login. Only the same host is allowed, and other hosts fall back, which prevents open redirects.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Add single sign-on to TYPO3',
                    'description' => 'WorkOS has a free tier, and SCIM, SSO configuration and audit logs become available as you grow. The TYPO3 extension is open source under GPL-2.0-or-later.',
                    'cta_text' => 'View on GitHub',
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
            'title' => 'Powermail Lab: styled multi-step forms',
            'navTitle' => 'Powermail Lab',
            'slug' => '/features/powermail',
            'abstract' => 'Powermail and Desiderio give editors accessible multi-step forms in the Desiderio design system. Friendly Captcha blocks bots without sending data to Google, and a development bypass keeps local work fast.',
            'description' => 'Accessible, shadcn-styled Powermail forms for TYPO3 with Friendly Captcha, a development bypass, and validation in the browser and on the server.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Integration',
                    'header' => 'Forms that editors build themselves',
                    'subheadline' => 'Powermail by in2code handles the forms. Desiderio adds the shadcn styling, and Friendly Captcha by Studio Mitte adds spam protection.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What Powermail and Desiderio do',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Powermail is a form extension, not a page builder.',
                    'content' => '<p><strong>Powermail builds the form, and Desiderio styles it.</strong> Editors define everything in the backend, without HTML, PHP or templates.</p><ul><li><strong>Every field restyled</strong> — inputs, checkboxes, selects, radio groups and textareas use shadcn partials.</li><li><strong>Private bot protection</strong> — Friendly Captcha sends no user IP to Google.</li><li><strong>Fast locally</strong> — an opt-in bypass flag skips the captcha in development.</li><li><strong>Checked twice</strong> — multi-step forms are validated in the browser and on the server.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/powermail',
                    'media' => ShowcaseBlocks::screenshot('feature-powermail.png', 'Powermail form overview in TYPO3', 'The Powermail form overview in TYPO3, listing the demo forms with their language, usage count and actions.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'What you get',
                    'eyebrow' => 'Integration',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'book-open', 'title' => 'Editors build the forms', 'description' => 'Editors set up pages, fields, labels and validation in the backend, without templates or code. Powermail stores submissions in the database and exports them to CSV.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Spam protection with a development bypass', 'description' => 'Friendly Captcha blocks bots without sending data to Google. The friendlycaptcha_skip_dev_validation flag skips the check in the Development context, including DDEV. Everywhere else it is required.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Multi-step forms with client validation', 'description' => 'Long forms split into pages with a step indicator. The browser checks each page before the next one opens, and the server checks everything again before saving.', 'link' => ''],
                        ['icon' => 'globe', 'title' => 'Styled with your theme', 'description' => 'Every field type uses Desiderio\'s shadcn partials and your theme tokens, in light and dark mode. A new preset restyles fields, step indicators, buttons and messages without CSS changes.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Multi-step forms with a step indicator', 'content' => '<p>Powermail splits long forms into pages, each with a step indicator.</p><ul><li><strong>Back and forth</strong> — visitors move between pages, and the browser checks the current page before the next one opens.</li><li><strong>Server check</strong> — every field is validated again on submit, before it is stored.</li><li><strong>Demo forms</strong> — from one-page Contact, Newsletter and Callback forms to a four-step Project Request wizard.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'All field types in Desiderio styling', 'content' => '<p>Every Powermail field type has a matching shadcn partial.</p><ul><li><strong>All fields</strong> — inputs, textareas, selects, radios, checkboxes, dates, country and file fields.</li><li><strong>One shared component</strong> — each uses the same d:molecule.field as the rest of the design system.</li><li><strong>States</strong> — focus rings, disabled states, and an error-coloured border and ring on errors.</li><li><strong>Theme-driven</strong> — switch the preset and the form changes at once, in light and dark mode, without CSS.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Browser and server validation', 'content' => '<p>Validation runs in the browser and again on the server.</p><ul><li><strong>Inline errors</strong> — HTML5 rules (required, email, configured validators) show a message at the field.</li><li><strong>Checked again</strong> — the server repeats every check after submission.</li><li><strong>Rules without code</strong> — editors mark fields as required and add validators per field.</li><li><strong>Extensible</strong> — a CustomValidatorEvent adds your own rules through a PSR-14 listener.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Friendly Captcha with a development bypass', 'content' => '<p>Friendly Captcha is installed next to Powermail and configured per site.</p><ul><li><strong>On every demo</strong> — each seeded form already has a captcha field.</li><li><strong>Development bypass</strong> — set friendlycaptcha_skip_dev_validation, and Development or DDEV skips the token check.</li><li><strong>Required elsewhere</strong> — in every other context, or with the flag off, the captcha is required.</li><li><strong>GDPR-friendly</strong> — proof of work instead of tracking, and no user IP goes to Google.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Storage, CSV export and finishers', 'content' => '<p>Every submission is stored and can be exported.</p><ul><li><strong>Stored and searchable</strong> — saved to tx_powermail_domain_model_mail and listed in the backend module.</li><li><strong>CSV export</strong> — download the results for analysis.</li><li><strong>Finishers</strong> — SendParameters, Redirect, SaveToAnyTable, RateLimit, and a FinisherInterface for your own.</li><li><strong>DataProcessors</strong> — change field data before it is saved.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Emails with Fluid templates', 'content' => '<p>On submit, Powermail can send email to fixed receivers, a user group or an address from the form.</p><ul><li><strong>Per form</strong> — each form has its own receiver and sender email.</li><li><strong>Fluid templates</strong> — markers add any submitted field value to the email.</li><li><strong>Confirmation emails</strong> — the seeded forms send thank-you emails, for example with a promise to reply within two working days.</li><li><strong>Events</strong> — change recipients and email bodies through PSR-14 events.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install-powermail.sh',
                    'code' => '# Powermail and Friendly Captcha come from their TYPO3 v14 forks,
# so add the VCS repositories first:
composer config repositories.powermail vcs https://github.com/dirnbauer/powermail
composer config repositories.friendlycaptcha vcs https://github.com/dirnbauer/friendlycaptcha-typo3

composer require "in2code/powermail:~14.0.3.2"
composer require "studiomitte/friendlycaptcha:~2.3.0.1"

# Add the Desiderio Powermail site set to your TYPO3 site configuration,
# then seed the demo forms with:
vendor/bin/typo3 desiderio:styleguide:seed',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Demo forms to start from',
                    'description' => 'Contact, newsletter, callback, appointment, support and a four-step project request. Each has Friendly Captcha and thank-you pages in English and German, and you change them in the backend without template code. Thank you to in2code and Studio Mitte.',
                    'cta_text' => 'View on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/powermail',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
