<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Feature pages for the agentic side of the stack: machine payments,
 * abilities, agentation, the API core and skillflow.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseAgenticPages
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [
            self::featureX402PaywallPage(),
            self::featureAbilitiesPage(),
            self::featureAgentationPage(),
            self::featureSgApicorePage(),
            self::featureSkillflowPage(),
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureX402PaywallPage(): array
    {
        return [
            'title' => 'x402 Paywall: HTTP 402 payments for TYPO3',
            'navTitle' => 'x402 Paywall',
            'slug' => '/features/x402-paywall',
            'abstract' => 'x402 Paywall lets you charge for TYPO3 pages and API routes. Visitors and AI agents pay small amounts in USDC from their wallet, with no payment processor account.',
            'description' => 'Charge for TYPO3 pages and API routes with HTTP 402 micropayments: payment middleware, wallet payments, prices per page and a revenue dashboard.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Payments',
                    'header' => 'Charge for TYPO3 content with HTTP 402',
                    'subheadline' => 'Set a price for a page or an API route. People and AI agents pay it in USDC, and TYPO3 delivers the content.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'How x402 works',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'x402 uses the HTTP status code 402 Payment Required for small payments.',
                    'content' => '<p><strong>HTTP 402 is an unused status code meant for payments.</strong> The client requests content, gets the payment terms and signs, and the payment settles.</p><ul><li><strong>Middleware</strong> — the extension handles the flow in TYPO3 middleware.</li><li><strong>Dashboard</strong> — a backend module shows revenue per page.</li><li><strong>Pages and APIs</strong> — both work the same way.</li><li><strong>No payment provider</strong> — USDC goes from wallet to wallet, without a Stripe account.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-x402-paywall',
                    'media' => ShowcaseBlocks::screenshot('feature-x402-paywall-backend-dashboard.png', 'x402 Paywall dashboard in the backend', 'The x402 Paywall dashboard in the TYPO3 backend, with revenue cards, top pages and recent transactions.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Get paid by people and by agents',
                    'eyebrow' => 'Benefits',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'tag', 'title' => 'Payments in USDC', 'description' => 'Each payment goes straight to your USDC wallet on Base, Polygon or Ethereum. USDC is a stablecoin tied to the US dollar.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'One protocol for people and agents', 'description' => 'Visitors pay for HTML pages in a wallet overlay. API clients and AI agents pay through HTTP 402 headers. Both use the same protocol.', 'link' => ''],
                        ['icon' => 'chart', 'title' => 'A built-in revenue dashboard', 'description' => 'See revenue per page, per period and per transaction, with your top pages and recent sales. You need no separate analytics tool.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'A price for each page', 'description' => 'Turn the paywall on or off, set a price and add a payment description in the page properties. The middleware configuration stays as it is.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Payment check in PSR-15 middleware', 'content' => '<p>The middleware checks each matching request before it reaches your page or API.</p><ul><li><strong>Valid signature</strong> — a verified PAYMENT-SIGNATURE header lets the request through.</li><li><strong>No signature</strong> — the response is HTTP 402 with the payment terms.</li><li><strong>No code changes</strong> — your page controllers and API code stay as they are.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Paywall settings and prices per page', 'content' => '<p>Editors set prices on an x402 Paywall tab in the page properties.</p><ul><li><strong>On or off</strong> — turn the paywall on per page.</li><li><strong>Own price</strong> — set a USDC amount and a description, for example \'Exclusive analysis: €0.02\'.</li><li><strong>Overrides the default</strong> — charge more for key pages and less for tests.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'API routes with wildcard patterns', 'content' => '<p>Route patterns in the site settings put whole API sections behind the paywall.</p><ul><li><strong>Wildcards</strong> — <code>/api/v1/content/*</code>, <code>/feed/*</code> or a single route.</li><li><strong>No payment</strong> — the request gets HTTP 402.</li><li><strong>Valid payment</strong> — a valid PAYMENT-SIGNATURE goes through and is logged.</li><li><strong>No browser needed</strong> — AI agents, feed readers and other clients can pay.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Wallet overlay with EIP-1193 signing', 'content' => '<p>A frontend plugin leads visitors through three steps.</p><ul><li><strong>Connect</strong> — MetaMask, Coinbase Wallet, Rabby or any other EIP-1193 wallet.</li><li><strong>Sign</strong> — approve the payment message in the wallet.</li><li><strong>Read</strong> — the payment settles and the content appears.</li><li><strong>Same page</strong> — with a connected wallet and USDC, visitors pay without leaving the page.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Dashboard for revenue and transactions', 'content' => '<p>Find it under Web &gt; x402 Paywall.</p><ul><li><strong>Revenue</strong> — today, 7 days, 30 days and all time.</li><li><strong>Top pages</strong> — the best earners of the last 30 days.</li><li><strong>Transactions</strong> — a paged list with wallet, amount, date and status.</li><li><strong>No extra tools</strong> — the data comes from your TYPO3 database.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Simulator for test payments', 'content' => '<p>The simulator tests the paywall on public URLs without real transactions.</p><ul><li><strong>Live request</strong> — enter a URL, choose a test network and a price, and it sends an x402 request.</li><li><strong>Response</strong> — it shows the headers and the exact payment terms your site returns.</li><li><strong>Public targets only</strong> — private, local and reserved network addresses are rejected.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP tools for agents', 'content' => '<p>The extension includes five MCP tools for Claude Code and similar agents.</p><ul><li><strong>x402_gated_pages</strong> — lists all pages and routes that need payment.</li><li><strong>x402_probe</strong> — tests the payment flow on a live URL.</li><li><strong>x402_stats / x402_transactions</strong> — revenue, counts and payments by address or date.</li><li><strong>x402_decode_header</strong> — reads PAYMENT-SIGNATURE headers for debugging.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'Bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/typo3-x402-paywall',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Put your first page behind the paywall',
                    'description' => 'x402-paywall is GPL-2.0-or-later. Install it with Composer, add your wallet address in the site settings and choose a page. Needs TYPO3 v14.3 and PHP 8.4 or newer.',
                    'cta_text' => 'View on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/typo3-x402-paywall',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function featureAbilitiesPage(): array
    {
        return [
            'title' => 'Abilities Registry: what your site can do',
            'navTitle' => 'Abilities',
            'slug' => '/features/typo3-abilities',
            'abstract' => 'The Abilities Registry is one list of what your TYPO3 installation can do. MCP, the CLI, REST and the desktop editor use it, and every run is checked and logged.',
            'description' => 'One typed registry of what your TYPO3 installation can do. MCP, CLI, REST and the desktop editor use it, with permission checks and a trace of every run.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'API and integration',
                    'header' => 'One registry of what your site can do',
                    'subheadline' => 'You describe an action once: its input, output, permissions and risk. MCP, the CLI, REST and the desktop editor then offer it with the same rules.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What the registry does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The idea follows the WordPress Abilities API. The TYPO3 version adds scopes, risk tiers and execution policies.',
                    'content' => '<p><strong>Agents need to know what your site can do, and you need to control it.</strong> The registry has one entry per action, not one endpoint per protocol.</p><ul><li><strong>Typed</strong> — each ability has JSON Schema contracts for input and output.</li><li><strong>Permissions</strong> — resource:operation scopes plus the rights of the acting backend user.</li><li><strong>Policy</strong> — the risk tier and declared side effects feed one site-wide execution policy.</li><li><strong>Generated</strong> — MCP tools, CLI commands, REST endpoints and the desktop editor come from the registry.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-abilities',
                    'media' => ShowcaseBlocks::screenshot('feature-abilities.png', 'API token records in the backend', 'The TYPO3 record list with one API token record named news external read.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Every surface follows the same rules',
                    'eyebrow' => 'Benefits',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'shield-check', 'title' => 'One pipeline for every run', 'description' => 'Every run passes the same steps: policy, input check, scopes, permissions, execution and output check. A denied ability never runs, and the denial is logged.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Surfaces generated from one entry', 'description' => 'Each exposed ability becomes an MCP tool (ability_*). The CLI offers abilities:list, abilities:describe and abilities:run. sg_apicore serves the registry over REST.', 'link' => ''],
                        ['icon' => 'lock', 'title' => 'Human approval for risky actions', 'description' => 'The policy can mark risk tiers or patterns as review_required, for example workspace publishing. A person must then confirm. REST tokens can\'t approve themselves.', 'link' => ''],
                        ['icon' => 'book-open', 'title' => 'A trace for every attempt', 'description' => 'Allowed, denied or failed, each run writes a trace. It records the ability, the surface, the input, the result, the duration and the backend user.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'The registry schema', 'content' => '<p>One PHP attribute holds everything a surface or a policy needs.</p><ul><li><strong>Name</strong> — namespace/ability-name, for example <code>news/create-article</code>.</li><li><strong>Contract</strong> — JSON Schema for input and output, checked on every run.</li><li><strong>Scopes</strong> — resource:operation strings such as <code>news:write</code>.</li><li><strong>Risk tier and side effects</strong> — from low to critical, plus side effects such as <code>database:write</code>.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'The execution pipeline', 'content' => '<p>Every surface calls the same executor.</p><ul><li><strong>Policy first</strong> — deny rules, review rules and risk limits from config/abilities-policy.yaml.</li><li><strong>Contracts</strong> — invalid input never runs, and invalid output is reported.</li><li><strong>Permissions</strong> — token scopes and the backend user\'s table rights.</li><li><strong>One response shape</strong> — {ok, data} or {ok, errorCode, error}, with machine-readable codes.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP tools', 'content' => '<p>With the MCP server installed, each exposed ability becomes one MCP tool.</p><ul><li><strong>Clear names</strong> — <code>news/list-articles</code> becomes <code>ability_news_list-articles</code>.</li><li><strong>Hints from the registry</strong> — readOnly, destructive and idempotent come from the ability\'s metadata.</li><li><strong>No extra code</strong> — registering the ability is all you do.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'REST API on sg_apicore', 'content' => '<p>The sg_apicore integration serves the registry over HTTP, with scoped tokens.</p><ul><li><strong>Discovery</strong> — <code>GET /api/abilities/v1/abilities</code> lists the abilities. A describe endpoint adds the schemas.</li><li><strong>Execution</strong> — <code>POST .../{namespace}/{name}/run</code> returns the standard response. Error codes map to 400, 403, 404 or 500.</li><li><strong>Tokens per backend user</strong> — each token runs with that user\'s permissions and workspace.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Command line', 'content' => '<p>The same registry works on the command line.</p><ul><li><strong>abilities:list</strong> — all abilities with scopes, risk and side effects.</li><li><strong>abilities:describe</strong> — the full contract as JSON.</li><li><strong>abilities:run</strong> — runs an ability through the same pipeline. <code>--approve-review</code> gives the human approval.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'The desktop editor', 'content' => '<p>The TYPO3 Desktop Editor sends its news, page, content and workspace actions through the registry.</p><ul><li><strong>Same endpoints</strong> — the app\'s URLs stayed the same. Only the checks behind them changed.</li><li><strong>Publish with approval</strong> — the confirm dialog gives the approval for risky publishing.</li><li><strong>Abilities panel</strong> — the app lists the abilities it runs on, live.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Execution traces', 'content' => '<p>Every attempt goes to a trace table, part of the agent audit trail.</p><ul><li><strong>Denials too</strong> — blocked attempts matter most in an audit.</li><li><strong>Tagged by surface</strong> — cli, mcp, rest and desktop runs are kept apart.</li><li><strong>Never blocks</strong> — a failed trace write doesn\'t stop the run.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Comparison with WordPress', 'content' => '<p>WordPress added the Abilities API in core 6.9, with an official MCP adapter: one registry, many protocols.</p><ul><li><strong>Same idea</strong> — registry entries map to protocols instead of hand-written endpoints.</li><li><strong>More control</strong> — scopes, risk tiers, side effects and execution policies are built in.</li><li><strong>Made for core</strong> — it has no dependencies, so it can be proposed to the TYPO3 community.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Register an ability',
                    'language' => 'php',
                    'filename' => 'CreateArticleAbility.php',
                    'code' => '#[AsAbility(
    name: \'news/create-article\',
    title: \'Create news article\',
    description: \'Creates a news article as a workspace draft.\',
    category: \'news\',
    scopes: [\'news:write\'],
    riskTier: RiskTier::Medium,
    sideEffects: [\'database:write\'],
)]
final class CreateArticleAbility extends AbstractAbility
{
    // getInputSchema() + getOutputSchema() + checkPermission() + execute()
}

// That\'s the whole integration:
//   MCP tool  ability_news_create-article   (generated)
//   CLI       abilities:run news/create-article
//   REST      POST /api/abilities/v1/abilities/news/create-article/run',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'One registry for MCP, CLI, REST and desktop',
                    'description' => 'Typed contracts, real permissions, execution policies and a full audit trail. You register an ability once, and every surface uses it.',
                    'cta_text' => 'View on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/typo3-abilities',
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
            'title' => 'Agentation: visual feedback for coding agents',
            'navTitle' => 'Agentation',
            'slug' => '/features/agentation',
            'abstract' => 'With Agentation, backend users click an element on a TYPO3 page and add a note. Claude Code, Cursor or another MCP agent gets the note with the selector and styles.',
            'description' => 'Mark elements in the TYPO3 frontend and backend and add notes. AI coding agents get the selector and styles, so they know what to change.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Developer tools',
                    'header' => 'Show coding agents what to change',
                    'subheadline' => 'Click an element and write a note. A coding agent gets the note with the selector and the page context.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Feedback that agents can act on',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Agentation adds a feedback toolbar to TYPO3. Backend users annotate the pages they see, and the notes sync to coding agents over MCP.',
                    'content' => '<p><strong>Written feedback is often vague.</strong> Developers and agents have to guess which element a user means.</p><ul><li><strong>Point and note</strong> — click an element and write a note. The agent also gets the selector, computed styles and page context.</li><li><strong>Two toolbars</strong> — one on the frontend for cross-browser testing, one in the backend for modules.</li><li><strong>Kept private</strong> — both need a login and a context check, so they stay off production.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-agentation',
                    'media' => ShowcaseBlocks::screenshot('feature-agentation.png', 'Agentation settings and status module', 'The Agentation backend module with MCP settings, environment checks, supported agents and an unavailable sync status.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Precise notes, safe defaults',
                    'eyebrow' => 'Benefits',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'send', 'title' => 'Click to annotate', 'description' => 'Backend users click an element on the frontend or in a backend module and add a note. The toolbar saves the selector, styles and page context.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Context agents can use', 'description' => 'A chat message only describes the problem. An annotation adds the selector, styles and page structure, so Claude Code, Cursor or Windsurf can make precise changes.', 'link' => ''],
                        ['icon' => 'lock', 'title' => 'Safe by default', 'description' => 'The toolbar needs a backend login and, on the frontend, an opt-in in the Admin Panel. By default, it doesn\'t load in the Production context.', 'link' => ''],
                        ['icon' => 'handshake', 'title' => 'MCP sync to your agent', 'description' => 'Copy an MCP config block, a Claude Code command or a Cursor deep link. TYPO3 forwards sync requests to local and cloud endpoints from the same origin.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Frontend annotation toolbar', 'content' => '<p>Logged-in backend users see a toolbar on the frontend.</p><ul><li><strong>Opt-in</strong> — it appears only when its Admin Panel section is on.</li><li><strong>Click to annotate</strong> — highlight elements, add comments and capture selectors and computed styles.</li><li><strong>Settings</strong> — position and scope per request, in the Admin Panel.</li><li><strong>Storage</strong> — in the browser, and optionally on a server.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Annotations in backend modules', 'content' => '<p>The same toolbar appears in TYPO3 backend modules when both the global and the user setting are on.</p><ul><li><strong>Where</strong> — the page module, record forms and the modules of other extensions.</li><li><strong>One store</strong> — backend notes go to the same storage as frontend notes.</li><li><strong>One place</strong> — admins, developers and agents find all feedback together.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Admin module under System > Agentation', 'content' => '<p>Only administrators see the module under System &gt; Agentation.</p><ul><li><strong>MCP setup</strong> — a JSON block to copy, a Claude Code CLI command or a Cursor deep link.</li><li><strong>Status checks</strong> — checks your API key and application context.</li><li><strong>Manage notes</strong> — reload them, delete single notes or clear all of them.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP configuration and export', 'content' => '<p>The module builds an mcpServers config block to paste.</p><ul><li><strong>Agents</strong> — Claude Code, Cursor, Windsurf, Zed, Continue and any other MCP agent.</li><li><strong>Pre-filled</strong> — your workspace ID and, if set, an API key (optional locally, required for server sync).</li><li><strong>One click</strong> — copy the JSON or a <code>claude mcp add</code> command, or use a Cursor deep link.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Per-user frontend and backend settings', 'content' => '<p>Each backend user turns their own toolbars on or off.</p><ul><li><strong>Two switches</strong> — frontend and backend toolbar, set separately in the user settings.</li><li><strong>Admin default</strong> — admins set the default, and users can change each switch.</li><li><strong>By role</strong> — a designer may use only the frontend toolbar, a developer both.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Application context check', 'content' => '<p>The toolbar follows the TYPO3 application context.</p><ul><li><strong>Development only</strong> — by default, it only loads in Development contexts.</li><li><strong>More contexts</strong> — choose \'Development and Testing\' or \'All contexts\' per environment.</li><li><strong>Safe for production</strong> — the default keeps the toolbar off production sites.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Server-side proxy for sync', 'content' => '<p>When notes sync to a server, TYPO3 sends the request, not the browser.</p><ul><li><strong>Key on the server</strong> — TYPO3 adds the API key, so it stays out of the browser.</li><li><strong>No CORS issues</strong> — the backend handles cross-origin requests.</li><li><strong>Local first</strong> — without a server, notes stay in the browser until you sync.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/agentation',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Add Agentation to your TYPO3 workflow',
                    'description' => 'Install the extension, connect your coding agent and start collecting notes it can act on. It works with Claude Code, Cursor, Windsurf and other MCP agents.',
                    'cta_text' => 'View on GitHub',
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
            'title' => 'sg_apicore: a REST API framework for TYPO3',
            'navTitle' => 'sg_apicore',
            'slug' => '/features/sg-apicore',
            'abstract' => 'sg_apicore makes TYPO3 content available as a REST API. PHP attributes define the endpoints, CRUD resources, OpenAPI docs and access by token or session.',
            'description' => 'Offer TYPO3 content through a REST API with OpenAPI docs, token or session login, automatic CRUD resources and MCP tools. For TYPO3 v14.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Backend API',
                    'header' => 'Turn your TYPO3 content into an API',
                    'subheadline' => 'You add one PHP attribute per endpoint, and sg_apicore writes the OpenAPI docs. Clients log in with tokens, JWT or a backend session.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What sg_apicore does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'sg_apicore handles routing, versions, docs and login, so you only write the controller methods.',
                    'content' => '<p><strong>sg_apicore is an API core for TYPO3 in one small package.</strong></p><ul><li><strong>Included</strong> — several APIs, versions, routing per tenant, OpenAPI 3 and authentication.</li><li><strong>Attributes</strong> — add attributes to controller methods and get docs, login and scopes.</li><li><strong>MCP tools</strong> — AI agents can use the same endpoints as MCP tools.</li><li><strong>Less setup</strong> — no extra config, no response wrappers and no login code per endpoint.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/sg_apicore',
                    'media' => ShowcaseBlocks::screenshot('feature-sg-apicore.png', 'Registered APIs and versions', 'The sg_apicore backend module listing registered APIs with their versions, login modes, base paths and actions.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Less code for each endpoint',
                    'eyebrow' => 'Benefits',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'zap', 'title' => 'Endpoints from attributes', 'description' => 'Each endpoint needs one PHP attribute. The framework reads #[ApiRoute], #[RequireScopes] and #[ApiResponse] and builds the spec, routing and login checks.', 'link' => ''],
                        ['icon' => 'book-open', 'title' => 'OpenAPI 3.0 docs included', 'description' => 'Every endpoint appears in Swagger UI at /api/{apiId}/v{version}/docs/ui. Export the spec as JSON for other tools or for code generation.', 'link' => ''],
                        ['icon' => 'shield-check', 'title' => 'Several login modes', 'description' => 'Public, opaque bearer tokens, JWT user tokens or a backend session. Set defaults per API, change them per endpoint and require scopes where needed.', 'link' => ''],
                        ['icon' => 'database', 'title' => 'CRUD without code', 'description' => 'Register a TYPO3 table to get list, get, create, update and delete endpoints. Writes use DataHandler, so hooks, the reference index and history work as usual.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Several APIs and versions in one install', 'content' => '<p>Register several APIs, such as public, partner and internal, each with its own versions.</p><ul><li><strong>No conflicts</strong> — <code>/api/public/v1/...</code> and <code>/api/partner/v2/...</code> go to separate actions.</li><li><strong>Own settings</strong> — login mode, rate limits, CORS policy and MCP settings per API.</li><li><strong>Limited routes</strong> — restrict any endpoint by <code>apiId</code> and <code>version</code> on #[ApiRoute].</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Tokens and access by scope', 'content' => '<p>Give each client the token type it needs.</p><ul><li><strong>Machine tokens</strong> — opaque bearer tokens for access between systems.</li><li><strong>User tokens</strong> — JWT access tokens with opaque refresh tokens, or API keys per user.</li><li><strong>Scopes</strong> — assign them and require them with #[RequireScopes] on any endpoint.</li><li><strong>Backend module</strong> — lists machine, user and refresh tokens with expiry, and regenerates them.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'CRUD resources with DataHandler', 'content' => '<p>One call turns a TYPO3 table into a REST resource.</p><ul><li><strong>Full CRUD</strong> — list, get, create, update and delete endpoints.</li><li><strong>DataHandler writes</strong> — hooks run, and the reference index and history update, as in the backend.</li><li><strong>Workspaces</strong> — live reads skip drafts. Workspace reads use workspaceOL().</li><li><strong>No raw SQL</strong> — so the data stays consistent.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'OpenAPI 3.0 specs and live testing', 'content' => '<p>Every endpoint is exported as OpenAPI 3.0.3 JSON.</p><ul><li><strong>Attributes</strong> — #[ApiResponse], #[ApiBodyParam] and #[ApiQueryParam] describe parameters, bodies and responses.</li><li><strong>TCA labels</strong> — schemas use the TCA field labels, so the spec matches your data model.</li><li><strong>Swagger UI</strong> — at <code>/api/{apiId}/v{version}/docs/ui</code>, to try endpoints live.</li><li><strong>Export</strong> — <code>api:openapi:generate</code> writes the spec for code generation.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Endpoints as MCP tools', 'content' => '<p>Existing endpoints also work as MCP (Model Context Protocol) tools, without duplicate code.</p><ul><li><strong>Discovery</strong> — agents find tools at <code>POST .../mcp</code> (JSON-RPC), with the same login and routing.</li><li><strong>Streaming</strong> — <code>GET /mcp</code> supports SSE-style messages.</li><li><strong>Control</strong> — denylists per API and #[ApiMcp] attributes hide sensitive endpoints.</li><li><strong>Preview</strong> — <code>api:mcp:list</code> shows what is exposed before you deploy.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Tenants and site context', 'content' => '<p>Each request runs in a TenantContext, taken from the TYPO3 site.</p><ul><li><strong>Per tenant</strong> — limit endpoints with the <code>tenants</code> property on #[ApiRoute].</li><li><strong>Context passed on</strong> — DataHandler calls and queries know which site owns the data.</li><li><strong>Several brands</strong> — one endpoint serves different content per domain.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Rate limits with bursts and time windows', 'content' => '<p>Set rate limits per API, per resource or per endpoint.</p><ul><li><strong>Settings</strong> — request limit, time window and an optional burst allowance.</li><li><strong>Headers</strong> — X-RateLimit-Limit/Remaining/Reset/Burst tell clients where they stand.</li><li><strong>Limit reached</strong> — the API returns HTTP 429.</li><li><strong>Narrow or wide</strong> — one rule for an endpoint or for a whole API version.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Logging and request tracing', 'content' => '<p>Structured logs that hide sensitive values.</p><ul><li><strong>Request ID</strong> — every request gets an X-Request-ID to follow through DataHandler and your code.</li><li><strong>RFC 7807 errors</strong> — the Problem JSON carries the same requestId for support teams.</li><li><strong>Hidden by default</strong> — passwords, tokens, auth headers, secrets and cookies. You can change the list.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require sgalinski/sg-apicore
vendor/bin/typo3 extension:activate sg_apicore
# Navigate to /api/{apiId}/v{version}/docs/ui to see the OpenAPI docs',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Make your TYPO3 content available as data',
                    'description' => 'Mobile apps, desktop editing tools, partner systems and AI agents can all use the same REST API. Scopes, rate limits, OpenAPI docs and request tracing are included.',
                    'cta_text' => 'View on GitHub',
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
            'title' => 'Skillflow: agent skills for TYPO3',
            'navTitle' => 'Skills',
            'slug' => '/features/skillflow',
            'abstract' => 'Skillflow keeps agent skills in the Anthropic SKILL.md format as TYPO3 records. Import them from git or a folder, run them on workspace stages and search them with Solr.',
            'description' => 'Manage agent skills in TYPO3: import them from git, search them with Solr facets and let them review drafts in your workspace stages.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Workspace automation',
                    'header' => 'Agent skills that review drafts in TYPO3',
                    'subheadline' => 'A skill is a set of instructions for an AI agent, such as an SEO check. Skillflow runs skills on drafts in your workspace stages.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What skillflow does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Each skill handles one task, such as SEO optimisation, tone-of-voice review, content QA or image analysis.',
                    'content' => '<p><strong>Skillflow stores agent skills as TYPO3 records.</strong> Edit them in the backend or import SKILL.md files from git or a folder.</p><ul><li><strong>Review on staging</strong> — skills on a workspace stage check content when it reaches that stage.</li><li><strong>Run on demand</strong> — start a skill on a page from the module.</li><li><strong>Search</strong> — Solr facets across the whole library.</li><li><strong>Two runners</strong> — CLI with allow-listed tools, API with remote MCP servers.</li></ul><p>Reports are suggestions for your team. They are never applied automatically.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/skillflow',
                    'media' => ShowcaseBlocks::screenshot('feature-skillflow.png', 'Skillflow manual-run panel', 'The Content > Skills module with an ab-testing skill, its engine, its instructions and the Development guard.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Skills you can find, sync and run',
                    'eyebrow' => 'Benefits',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'database', 'title' => 'Searchable skill library', 'description' => 'Imported skills are indexed in Solr. Filter them by runner type, allowed tools and category instead of scrolling through long lists.', 'link' => ''],
                        ['icon' => 'history', 'title' => 'Skill imports from git', 'description' => 'Point to a GitHub, GitLab or Gitea repository. A sync fetches the latest skills and updates them in place, keeping UIDs and workspace assignments.', 'link' => ''],
                        ['icon' => 'send', 'title' => 'Reviews on workspace stages', 'description' => 'Assign skills to custom workspace stages and turn on auto-run. Every record sent to that stage is reviewed, and the editor gets the report.', 'link' => ''],
                        ['icon' => 'monitor', 'title' => 'Two runners to choose from', 'description' => 'The API runner uses the Anthropic Messages API with remote MCP servers. The CLI runner runs Claude Code locally with the .mcp.json tools each skill allows.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Features in detail',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Skill records with SKILL.md structure', 'content' => '<p>Each skill is a backend record (tx_skillflow_skill) in the Anthropic format.</p><ul><li><strong>Fields</strong> — name, identifier, description and a Markdown body with the agent\'s instructions.</li><li><strong>Code editor</strong> — edit the body inline. Extra frontmatter, such as allowed-tools, stays in a JSON field.</li><li><strong>Write or import</strong> — write skills in the backend or import them from git.</li><li><strong>Both ways</strong> — an exported skill folder is a valid Anthropic skill.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Attached files with text search', 'content' => '<p>Other files in the skill folder, such as references, scripts and templates, become attachments.</p><ul><li><strong>One tab</strong> — the files (tx_skillflow_file) are on the skill\'s Attachments tab.</li><li><strong>Search</strong> — text files up to 256 KB are indexed. Binary files are only counted.</li><li><strong>Sync</strong> — updates files in place and soft-deletes missing ones.</li><li><strong>At run time</strong> — the CLI runner writes the folder to disk. The API runner inlines files up to a size limit.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Imports from folders and repositories', 'content' => '<p>Import skills from a local folder or a remote repository.</p><ul><li><strong>Local</strong> — scan a folder (default <code>&lt;project&gt;/skills/</code>) on demand or by cron.</li><li><strong>Remote</strong> — sync all skills from a GitHub, GitLab or Gitea URL, or a .zip file.</li><li><strong>Stable UIDs</strong> — assignments and page links survive a sync.</li><li><strong>Secrets</strong> — private repositories store only the name of an environment variable, never the token.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Backend module and run reports', 'content' => '<p>The Content &gt; Skills module manages the library.</p><ul><li><strong>Browse and search</strong> — all skills, with Solr facets for runner type, allowed tools and category.</li><li><strong>Repositories</strong> — start imports and syncs.</li><li><strong>Run reports</strong> — the prompt, the AI response, the suggestions and the run time.</li><li><strong>Saved</strong> — reports (tx_skillflow_run) sit next to the record history.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Auto-run on workspace stages', 'content' => '<p>Turn on auto-run on the Skills tab of a custom workspace stage.</p><ul><li><strong>On each stage change</strong> — every record sent to the stage runs its skills in order.</li><li><strong>Reports</strong> — results are saved and the editor is notified.</li><li><strong>Before publishing</strong> — new records get SEO or tone feedback early.</li><li><strong>Automatic staging</strong> — optionally, new elements go to a set stage.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Skills per page, run by hand', 'content' => '<p>Assign quality skills to a page on the Skills tab of its page properties.</p><ul><li><strong>On demand</strong> — page skills run when you start them, not on a stage change.</li><li><strong>Workspaces</strong> — they review drafts through workspace overlays.</li><li><strong>Final check</strong> — for landing pages and campaign sites before they go live.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Sync command for scheduled imports', 'content' => '<p><code>skillflow:sync</code> updates the local folder and all repositories in one run.</p><ul><li><strong>No clicks</strong> — it needs no web request or backend.</li><li><strong>Scheduled</strong> — run it from a TYPO3 scheduler task or a cron job.</li><li><strong>Separate indexing</strong> — <code>skillflow:solr:index</code> re-indexes skills for search.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Credits',
                    'header' => 'Part of an AI stack built on Netresearch',
                    'subheadline' => 'Skillflow works alongside the TYPO3 AI extensions by Netresearch: nr_llm, nr_mcp_agent, nr_vault and t3_cowriter. Thank you, Netresearch DTT GmbH.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Add a quality check to your workspace stages',
                    'description' => 'Skillflow runs focused AI reviews in TYPO3 workspace stages. Drafts get the same checks every time, and your team decides what to change.',
                    'cta_text' => 'View on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/skillflow',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
