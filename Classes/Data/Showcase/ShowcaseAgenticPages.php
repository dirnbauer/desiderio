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
            'title' => 'x402 Paywall for TYPO3 — HTTP 402 Micropayment Protocol',
            'navTitle' => 'x402 Paywall',
            'slug' => '/features/x402-paywall',
            'abstract' => 'Turn TYPO3 pages and API routes into monetized content with x402, the HTTP 402 Payment Required standard. Accept USDC stablecoin micropayments from any wallet, measure revenue per page, and gate content for humans or AI agents — no payment processor account required.',
            'description' => 'TYPO3 x402 paywall: charge for content with HTTP 402 micropayments. Middleware routing, wallet integration, backend dashboard, API gating.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Monetization & Payments',
                    'header' => 'Monetize TYPO3 content with HTTP 402 micropayments',
                    'subheadline' => 'Charge humans and AI agents for pages, routes, and API endpoints in seconds with the x402 payment protocol and stablecoin settlement.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What is x402?',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'The HTTP 402 Payment Required standard for micropayments, now wired into TYPO3.',
                    'content' => '<p><strong>HTTP 402 is a dormant status code built exactly for this:</strong> request content, get payment terms, sign, settle.</p><ul><li><strong>Wired into TYPO3</strong> — x402-paywall handles the flow in middleware.</li><li><strong>Measured</strong> — a backend dashboard tracks revenue per page.</li><li><strong>Pages or APIs</strong> — gate frontend routes and headless APIs the same way.</li><li><strong>No middleman</strong> — no Stripe account, no processor fees, just USDC wallets talking to wallets.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-x402-paywall',
                    'media' => ShowcaseBlocks::screenshot('feature-x402-paywall-backend-dashboard.png', 'x402 Paywall Backend Dashboard', 'TYPO3 backend showing x402 paywall dashboard with revenue cards, top monetized pages list, and recent transactions table.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'PSR-15 middleware payment enforcement', 'content' => '<p>The extension sits in the middleware stack and intercepts matching requests before they reach your pages or API.</p><ul><li><strong>Checks the signature</strong> — a verified PAYMENT-SIGNATURE header lets the request proceed.</li><li><strong>402 when absent</strong> — otherwise it returns HTTP 402 with payment terms.</li><li><strong>No code changes</strong> — you never touch page controllers or API code.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Per-page paywall configuration and price overrides', 'content' => '<p>An x402 Paywall tab in page properties puts pricing in editors\' hands.</p><ul><li><strong>Enable per page</strong> — turn the paywall on where you want it.</li><li><strong>Custom price</strong> — set a USDC amount and a description, e.g. \'Exclusive analysis: €0.02\'.</li><li><strong>Overrides defaults</strong> — price flagship pages higher and experiments lower, from the page tree.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Headless route and API gating with wildcard patterns', 'content' => '<p>Configure route patterns in site settings to gate entire API namespaces.</p><ul><li><strong>Wildcards</strong> — <code>/api/v1/content/*</code>, <code>/feed/*</code>, or a single specific route.</li><li><strong>402 without payment</strong> — unpaid requests to those routes get HTTP 402.</li><li><strong>Verified and logged</strong> — a valid PAYMENT-SIGNATURE proceeds and is recorded.</li><li><strong>Browser-free</strong> — AI agents, feed readers, and custom clients can pay for access.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Frontend overlay plugin with EIP-1193 wallet signing', 'content' => '<p>The overlay plugin guides visitors through a three-step wallet flow.</p><ul><li><strong>Connect</strong> — MetaMask, Coinbase Wallet, Rabby, or any EIP-1193 provider.</li><li><strong>Sign</strong> — approve a payment message in the wallet.</li><li><strong>Unlock</strong> — the signature settles and the content appears.</li><li><strong>Fast on return</strong> — a connected wallet with USDC moves through without leaving the page.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Backend dashboard with revenue, top pages, and transactions', 'content' => '<p>Open Web &gt; x402 Paywall to see how the paywall is performing.</p><ul><li><strong>Revenue</strong> — today, 7 days, 30 days, and all time.</li><li><strong>Top pages</strong> — a ranked list of the best earners over 30 days.</li><li><strong>Transactions</strong> — a paginated log of wallet, amount, date, and status.</li><li><strong>No extra tooling</strong> — it reads straight from your TYPO3 database.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Public simulator for testing payment flows', 'content' => '<p>A built-in simulator tests your paywall against public URLs without real transactions.</p><ul><li><strong>Real requests</strong> — enter a URL, pick a test network and price, and it makes a live x402 request.</li><li><strong>See the response</strong> — captures the headers and shows the exact payment requirement your gateway generates.</li><li><strong>Safe by design</strong> — private, local, and reserved-network targets are rejected.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP tools for agent discovery and monitoring', 'content' => '<p>Five MCP tools ship built-in for Claude Code and similar agents.</p><ul><li><strong>x402_gated_pages</strong> — lists every page and route configured for payment.</li><li><strong>x402_probe</strong> — tests the payment flow against a live URL.</li><li><strong>x402_stats / x402_transactions</strong> — revenue, counts, and payment lookups by address or date.</li><li><strong>x402_decode_header</strong> — parses PAYMENT-SIGNATURE headers for debugging.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'Bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/typo3-x402-paywall',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
    private static function featureAbilitiesPage(): array
    {
        return [
            'title' => 'Abilities Registry for TYPO3 — one registry, many projections',
            'navTitle' => 'Abilities',
            'slug' => '/features/typo3-abilities',
            'abstract' => 'The Abilities Registry gives TYPO3 one typed, permissioned registry of what the installation can do. Every ability declares its contract, scopes, risk tier and side effects once — and MCP tools, CLI commands, REST endpoints and the desktop editor become projections of that registry, each run policy-gated and traced.',
            'description' => 'One typed, permissioned registry of what your TYPO3 installation can do. MCP, CLI, REST and desktop surfaces are generated projections — governed, traced, never hand-rolled.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'API & Integration',
                    'header' => 'One registry of what your site can do',
                    'subheadline' => 'Register an ability once — with typed contracts, scopes, risk tier and side effects — and every protocol surface becomes a governed projection of it.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What is the Abilities Registry?',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'WordPress proved the architecture with its Abilities API; this is the TYPO3 answer — with governance built in.',
                    'content' => '<p><strong>Agents need to know what your site can do — and you need to control it.</strong> The registry holds one entry per action, never one endpoint per protocol.</p><ul><li><strong>Typed</strong> — every ability carries JSON Schema input and output contracts.</li><li><strong>Permissioned</strong> — resource:operation scopes plus the acting backend user\'s real permissions.</li><li><strong>Governed</strong> — a risk tier and declared side effects feed a site-wide execution policy.</li><li><strong>Projected</strong> — MCP tools, CLI commands, REST endpoints and the desktop editor are generated from the registry.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-abilities',
                    'media' => ShowcaseBlocks::screenshot('feature-abilities.png', 'API token records in the TYPO3 backend', 'TYPO3 backend record list showing one API token record named news external read. The Abilities registry, typed contracts, projections, policy pipeline, and traces are composed in the accompanying video rather than captured here.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'header' => 'Why it matters',
                    'eyebrow' => 'API & Integration',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'shield-check', 'title' => 'One governed pipeline', 'description' => 'Every execution — from any surface — runs policy gate, input validation, scope check, permission check, execution and output validation in that order. Governance outranks contracts: a denied ability never runs, and the denial is recorded.', 'link' => ''],
                        ['icon' => 'zap', 'title' => 'Projections, not endpoints', 'description' => 'A compiler pass turns every exposed ability into an MCP tool (ability_*), the CLI ships abilities:list/describe/run, and the sg_apicore integration serves REST discovery and execution — all generated from the same registry entry.', 'link' => ''],
                        ['icon' => 'lock', 'title' => 'Human-in-the-loop by policy', 'description' => 'The abilities policy marks risk tiers or patterns as review_required. High-risk actions like workspace publishing then need an explicit human approval — a confirm click in the desktop editor, an --approve-review flag on the CLI — and bearer tokens can never approve themselves over REST.', 'link' => ''],
                        ['icon' => 'book-open', 'title' => 'Every attempt traced', 'description' => 'Allowed, denied or failed: each execution writes a trace record with the ability, surface (mcp, cli, rest, desktop), requested input, outcome, duration and acting backend user — the audit trail that answers who did what, and why it (did not) work.', 'link' => ''],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'The registry schema', 'content' => '<p>One PHP attribute declares everything a surface or a policy needs to know.</p><ul><li><strong>Name</strong> — namespace/ability-name, like <code>news/create-article</code>.</li><li><strong>Contract</strong> — JSON Schema for input and output, validated on every run.</li><li><strong>Scopes</strong> — resource:operation strings such as <code>news:write</code>.</li><li><strong>Risk tier &amp; side effects</strong> — low to critical, plus declared subsystems like <code>database:write</code>.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'The execution pipeline', 'content' => '<p>Every projection calls the same executor.</p><ul><li><strong>Policy first</strong> — deny rules, review requirements and risk caps from config/abilities-policy.yaml.</li><li><strong>Contracts enforced</strong> — invalid input never executes; invalid output is reported honestly.</li><li><strong>Real permissions</strong> — token scopes and the booted backend user\'s table permissions.</li><li><strong>Stable envelope</strong> — {ok, data} or {ok, errorCode, error} with machine-readable codes.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP projection', 'content' => '<p>When the MCP server is installed, a compiler pass generates one MCP tool per exposed ability.</p><ul><li><strong>Named predictably</strong> — <code>news/list-articles</code> becomes <code>ability_news_list-articles</code>.</li><li><strong>Honest annotations</strong> — readOnly, destructive and idempotent hints derive from the registry metadata.</li><li><strong>Zero glue code</strong> — registering the ability is the whole integration.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'REST projection on sg_apicore', 'content' => '<p>The sg_apicore integration serves the registry over HTTP with scoped tokens.</p><ul><li><strong>Discovery</strong> — <code>GET /api/abilities/v1/abilities</code> lists definitions; a describe endpoint adds the schemas.</li><li><strong>Execution</strong> — <code>POST .../{namespace}/{name}/run</code> returns the ability envelope; 400/403/404/500 map from the error codes.</li><li><strong>Backend-user tokens</strong> — opaque tokens bound to a backend user boot real permissions and workspace context.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'CLI projection', 'content' => '<p>The same registry drives the command line.</p><ul><li><strong>abilities:list</strong> — every ability with scopes, risk and side effects.</li><li><strong>abilities:describe</strong> — the full contract as JSON.</li><li><strong>abilities:run</strong> — execute through the governed pipeline; <code>--approve-review</code> is the operator\'s human-in-the-loop flag.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Desktop editor as a projection', 'content' => '<p>The TYPO3 Desktop Editor routes its news, pages, content and workspace operations through the registry.</p><ul><li><strong>Wire-identical</strong> — the app\'s endpoints stayed the same; the governance underneath changed.</li><li><strong>Publish with approval</strong> — the app\'s confirm dialog carries the review approval for high-risk publishing.</li><li><strong>Abilities panel</strong> — the app lists the registry it runs on, live from the connection.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Execution traces', 'content' => '<p>Every attempt lands in a trace table — the abilities lane of the agent audit trail.</p><ul><li><strong>Denials included</strong> — governance wants the blocked attempts most of all.</li><li><strong>Surface-tagged</strong> — cli, mcp, rest and desktop runs are distinguishable.</li><li><strong>Never a gate</strong> — tracing observes; a failed trace write cannot break an execution.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'The WordPress lesson, completed', 'content' => '<p>WordPress shipped the Abilities API in core 6.9 and an official MCP adapter — one registry, many projections.</p><ul><li><strong>Same architecture</strong> — registry entries project onto protocols instead of hand-rolled endpoints.</li><li><strong>More governance</strong> — scopes, risk tiers, side-effect declarations and execution policies are first-class here.</li><li><strong>Upstream ambition</strong> — dependency-free core, built to be proposed to the TYPO3 community.</li></ul>', 'open_by_default' => 0],
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
                    'header' => 'Give your installation one registry of what it can do',
                    'description' => 'Typed contracts, real permissions, execution policies and a full audit trail — with MCP, CLI, REST and desktop surfaces generated from a single registration.',
                    'cta_text' => 'Get started on GitHub',
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
            'title' => 'Agentation: Visual UI annotations for AI coding agents in TYPO3',
            'navTitle' => 'Agentation',
            'slug' => '/features/agentation',
            'abstract' => 'Agentation brings visual annotation feedback directly into TYPO3. Authenticated users point AI agents at page elements with selectors, comments, and computed styles — then synchronize that structured context to Claude Code, Cursor, or any MCP-capable agent.',
            'description' => 'Collect feedback from users and agents with visual UI annotations in TYPO3 frontend and backend. Guide AI coding agents directly to the elements you want changed.',
            'parentSlug' => 'features',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Developer tools',
                    'header' => 'Point AI agents at your TYPO3 frontend and backend',
                    'subheadline' => 'Visual annotations let you collect structured feedback from users and pass it directly to coding agents.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Feedback collection that agents can act on',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Agentation is a visual feedback layer for TYPO3 where authenticated backend users annotate pages they see — then sync that context to AI coding agents over MCP.',
                    'content' => '<p><strong>Most feedback loops are broken</strong> — users describe, developers guess, agents work blind. Agentation closes the loop.</p><ul><li><strong>Point and note</strong> — click an element, write a note; the agent gets the selector, computed styles, and page context too.</li><li><strong>Two toolbars</strong> — one on the frontend for cross-browser testing, one in the backend for module tweaks.</li><li><strong>Never leaks</strong> — both stay behind a login gate and a context guard, away from production.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/typo3-agentation',
                    'media' => ShowcaseBlocks::screenshot('feature-agentation.png', 'Agentation configuration and status module', 'TYPO3 Agentation backend module showing MCP configuration, environment gates, supported destinations, and a status in which the API key and sync endpoint are unavailable. No successful annotation sync is shown.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Frontend annotation toolbar', 'content' => '<p>A gated toolbar appears on the frontend for authenticated backend users.</p><ul><li><strong>Explicit, never automatic</strong> — it respects an Admin Panel section toggle.</li><li><strong>Click to annotate</strong> — highlight elements, add comments, and capture computed styles and selectors.</li><li><strong>Configurable</strong> — position and scope are set per request via Admin Panel settings.</li><li><strong>Persisted</strong> — annotations live in browser-local storage plus optional server endpoints.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Backend module frame annotation', 'content' => '<p>The same toolbar injects into TYPO3 module frames when both the global and per-user toggles are on.</p><ul><li><strong>Annotate inline</strong> — the page module, record forms, and third-party extensions.</li><li><strong>One trail</strong> — backend annotations flow into the same storage as frontend ones.</li><li><strong>For admins and devs</strong> — feedback stays in one place for agents to reference.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Admin-only System > Agentation module', 'content' => '<p>Only administrators see the module at System &gt; Agentation.</p><ul><li><strong>MCP config on tap</strong> — a copyable JSON block, a Claude Code CLI command, or a Cursor deep link.</li><li><strong>Status checks</strong> — verifies your API key and app context.</li><li><strong>Manage annotations</strong> — reload, delete individually, or clear all stored feedback.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP configuration and export', 'content' => '<p>The module assembles a ready-to-paste mcpServers config block.</p><ul><li><strong>Broad support</strong> — Claude Code, Cursor, Windsurf, Zed, Continue, and any MCP-capable agent.</li><li><strong>Pre-filled</strong> — your workspace ID and, when set, an API key (optional locally, required for server sync).</li><li><strong>One click</strong> — copy the JSON, grab a <code>claude mcp add</code> command, or use a Cursor install deep link.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Per-user frontend and backend settings', 'content' => '<p>Each backend user controls their own toolbars independently.</p><ul><li><strong>Two switches</strong> — frontend and backend toolbars, toggled separately in user settings.</li><li><strong>Admin default, user override</strong> — admins set the opt-in default; users can still flip each one.</li><li><strong>Role-fit</strong> — a designer may use only the frontend toolbar while a developer uses both.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Application context gate', 'content' => '<p>The toolbar respects TYPO3\'s application context.</p><ul><li><strong>Dev-only by default</strong> — it activates only in Development contexts.</li><li><strong>Widen if needed</strong> — opt into \'Development and Testing\' or \'All contexts\' per environment.</li><li><strong>Production-safe</strong> — the default makes shipping a toolbar to production nearly impossible.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Same-origin backend proxy for local and cloud sync', 'content' => '<p>When annotations sync to a server, TYPO3 makes the request, not the browser.</p><ul><li><strong>Server-side key</strong> — TYPO3 attaches the API key and keeps credentials out of browser storage.</li><li><strong>No CORS pain</strong> — cross-origin concerns are handled on the backend.</li><li><strong>Offline-first</strong> — local workflows keep annotations in browser storage until you sync.</li></ul>', 'open_by_default' => 0],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Install',
                    'language' => 'bash',
                    'filename' => 'install.sh',
                    'code' => 'composer require webconsulting/agentation',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Backend API',
                    'header' => 'Turn your TYPO3 content into an API',
                    'subheadline' => 'One attribute per endpoint. OpenAPI docs automatic. Tokens, JWT, or session-based auth. Built for production, shipped as open source.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'The API framework TYPO3 was waiting for',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Stop hand-rolling REST endpoints. sg_apicore provides the plumbing.',
                    'content' => '<p><strong>Stop hand-rolling REST endpoints.</strong> sg_apicore is a modern API core for TYPO3, all in one lightweight package.</p><ul><li><strong>Built in</strong> — multi-API registration, versioning, tenant-aware routing, OpenAPI 3, and authentication.</li><li><strong>Just add attributes</strong> — write controller methods, annotate them, and get docs, auth, and scopes automatically.</li><li><strong>MCP-ready</strong> — the same endpoints are exposed as MCP tools for AI agents.</li><li><strong>No boilerplate</strong> — no config overhead, no response-wrapper conventions, no per-endpoint auth.</li></ul>',
                    'media_rounded' => 1,
                    'button_text' => 'View on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/sg_apicore',
                    'media' => ShowcaseBlocks::screenshot('feature-sg-apicore.png', 'Registered APIs and versions', 'TYPO3 sg_apicore backend module showing registered API identifiers, versions, authentication modes, base paths, and available actions. Swagger UI and endpoint execution are not visible in this screenshot.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Multi-API and versioning in the same install', 'content' => '<p>Register multiple APIs — public, partner, internal — each with its own versions.</p><ul><li><strong>No conflicts</strong> — route <code>/api/public/v1/...</code> and <code>/api/partner/v2/...</code> to separate actions.</li><li><strong>Independent config</strong> — per-API auth mode, rate limits, CORS policy, and MCP settings.</li><li><strong>Scoped routes</strong> — restrict any endpoint by <code>apiId</code> and <code>version</code> on #[ApiRoute].</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Token management and scope-based access control', 'content' => '<p>Issue exactly the token type each consumer needs.</p><ul><li><strong>Machine tokens</strong> — opaque bearer tokens for machine-to-machine access.</li><li><strong>User tokens</strong> — JWT access tokens with opaque refresh tokens, or per-user API keys.</li><li><strong>Scoped</strong> — assign scopes and enforce them with #[RequireScopes] on any endpoint.</li><li><strong>Managed</strong> — a backend module lists machine, user, and refresh tokens with expiry and regeneration.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace-aware auto-CRUD resources with DataHandler', 'content' => '<p>Register a TYPO3 table as a REST resource with a single call.</p><ul><li><strong>Full CRUD</strong> — automatic list, get, create, update, and delete endpoints.</li><li><strong>DataHandler writes</strong> — hooks fire, reference indexing updates, history is written, just like the backend.</li><li><strong>Workspace-correct</strong> — live reads drop drafts; workspace reads overlay via workspaceOL().</li><li><strong>No raw SQL</strong> — and no consistency surprises.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'OpenAPI 3.0 specification generation and live testing', 'content' => '<p>Every endpoint exports to OpenAPI 3.0.3 JSON.</p><ul><li><strong>Described by attributes</strong> — #[ApiResponse], #[ApiBodyParam], and #[ApiQueryParam] define params, bodies, and responses.</li><li><strong>TCA-enriched</strong> — schemas pick up TCA field labels, so the spec mirrors your data model.</li><li><strong>Swagger UI</strong> — auto-mounted at <code>/api/{apiId}/v{version}/docs/ui</code> to try endpoints live.</li><li><strong>Exportable</strong> — generate the spec via <code>api:openapi:generate</code> for codegen.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'MCP (Model Context Protocol) tool exposure from endpoints', 'content' => '<p>Expose existing endpoints as MCP tools — no duplication.</p><ul><li><strong>Discoverable</strong> — agents find tools via <code>POST .../mcp</code> (JSON-RPC) through the same auth and routing.</li><li><strong>Streaming</strong> — a companion <code>GET /mcp</code> supports SSE-style communication.</li><li><strong>Controlled</strong> — per-API denylists and #[ApiMcp] attributes hide sensitive endpoints.</li><li><strong>Previewable</strong> — <code>api:mcp:list</code> shows what is exposed before deployment.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Multi-tenancy and site-aware request context', 'content' => '<p>Every request runs in a TenantContext resolved from the TYPO3 Site.</p><ul><li><strong>Tenant-scoped</strong> — filter endpoints by the <code>tenants</code> property on #[ApiRoute].</li><li><strong>Context flows</strong> — downstream DataHandler ops and queries know which site owns the data.</li><li><strong>Multi-brand ready</strong> — the same endpoint serves different content per domain.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Rate limiting with burst and windowed enforcement', 'content' => '<p>Enable rate limits per API, per resource, or per endpoint.</p><ul><li><strong>Tunable</strong> — set a request limit, window size, and optional burst allowance.</li><li><strong>Transparent</strong> — X-RateLimit-Limit/Remaining/Reset/Burst headers tell clients where they stand.</li><li><strong>Enforced</strong> — exceeding the limit returns a 429.</li><li><strong>Granular or blanket</strong> — one rule per endpoint, or per whole API version.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Request/response logging and request tracing', 'content' => '<p>Structured logging with built-in redaction.</p><ul><li><strong>Traceable</strong> — every request gets an X-Request-ID you can follow through DataHandler and business logic.</li><li><strong>RFC 7807 errors</strong> — Problem JSON carries the same requestId for support teams.</li><li><strong>Safe by default</strong> — passwords, tokens, authorization, secrets, and cookies are redacted; the list is configurable.</li></ul>', 'open_by_default' => 0],
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Workspace Automation',
                    'header' => 'AI-powered skill management for TYPO3 editorial workflows',
                    'subheadline' => 'Define reusable agent skills, import them from repositories, run them against draft content in your workspace stage pipeline, and keep editorial quality consistent.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'What skillflow does',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'Skills are how AI agents accomplish focused tasks — SEO optimization, tone-of-voice review, content QA, image analysis, or anything your team needs to automate.',
                    'content' => '<p><strong>Skillflow manages Anthropic-style agent skills inside TYPO3</strong> — editable records, importable from git or local folders as SKILL.md files.</p><ul><li><strong>Automate review</strong> — assign skills to workspace stages to check content as it is staged.</li><li><strong>Run on demand</strong> — trigger skills manually against pages from the module.</li><li><strong>Find fast</strong> — search the whole library with Solr-powered faceted search.</li><li><strong>Two runners</strong> — CLI adds whitelisted tools; API adds remote MCP servers.</li></ul><p>Reports are stored and never auto-applied — always suggestions for your team\'s review.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'See on GitHub',
                    'button_link' => 'https://github.com/dirnbauer/skillflow',
                    'media' => ShowcaseBlocks::screenshot('feature-skillflow.png', 'Skillflow manual-run panel', 'TYPO3 Content > Skills module showing an ab-testing skill, its engine and instructions, the Development guard, and a No page selected state. No skill run or automatic content change is shown.'),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
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
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Exactly what it does',
                    'allow_multiple' => 1,
                    'items' => [
                        ['title' => 'Skill records with SKILL.md structure', 'content' => '<p>Every skill is a backend record (tx_skillflow_skill) mirroring the Anthropic format.</p><ul><li><strong>Standard fields</strong> — name, identifier, description, and a markdown body of agent instructions.</li><li><strong>Code editor</strong> — the body edits inline; extra frontmatter (like allowed-tools) stays in a JSON field.</li><li><strong>Author or import</strong> — write skills in the backend or pull them from git.</li><li><strong>Bidirectional</strong> — export a skill to a folder and it is a valid Anthropic skill at once.</li></ul>', 'open_by_default' => 1],
                        ['title' => 'Supporting file attachments with text indexing', 'content' => '<p>Everything beside the SKILL.md — references, scripts, templates — imports as attachment records.</p><ul><li><strong>Under one tab</strong> — files (tx_skillflow_file) appear on the skill\'s Attachments tab.</li><li><strong>Indexed</strong> — text files up to 256 KB are searchable; binaries are counted but skipped.</li><li><strong>Self-healing</strong> — re-sync updates files in place and soft-deletes ones that disappear.</li><li><strong>Runtime-ready</strong> — the CLI runner materialises the folder; the API runner inlines files under a size budget.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Folder and repository imports with stable UIDs', 'content' => '<p>Import skills from a local folder or a remote repository.</p><ul><li><strong>Local</strong> — scan a configurable folder (default <code>&lt;project&gt;/skills/</code>), on demand or on a cron.</li><li><strong>Remote</strong> — point at a GitHub, GitLab, or Gitea URL, or a .zip, and sync all skills.</li><li><strong>Stable UIDs</strong> — re-syncs keep assignments and page links intact; no re-wiring.</li><li><strong>Secrets safe</strong> — private repos store only the env-var name; the token never hits the database.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Backend module for skill management and run reports', 'content' => '<p>The Content &gt; Skills module is mission control for your library.</p><ul><li><strong>Browse &amp; search</strong> — list all skills with Solr facets (runner type, allowed tools, categories).</li><li><strong>Manage repos</strong> — trigger imports and re-syncs from the UI.</li><li><strong>Run reports</strong> — see the prompt, AI response, extracted suggestions, and execution time.</li><li><strong>Stored</strong> — reports (tx_skillflow_run) sit alongside record history for team review.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Workspace integration with stage-triggered auto-run', 'content' => '<p>Open the Skills tab on any custom workspace stage and enable auto-run.</p><ul><li><strong>On stage</strong> — every record sent to that stage runs the assigned skills in sequence.</li><li><strong>Reported</strong> — results are stored and the editor is notified.</li><li><strong>Closes the loop</strong> — new records get instant SEO or tone feedback before publishing.</li><li><strong>Auto-workflow</strong> — optionally send every new element to a configured stage automatically.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'Page-level skill assignment and manual runs', 'content' => '<p>Any page can carry assigned QM skills via the Skills tab in page properties.</p><ul><li><strong>On demand</strong> — unlike stage skills, page skills run when you trigger them.</li><li><strong>Workspace-aware</strong> — they review draft content through workspace overlays.</li><li><strong>Final QA</strong> — perfect for landing pages and campaign microsites before go-live.</li></ul>', 'open_by_default' => 0],
                        ['title' => 'CLI sync command for cron-scheduled imports', 'content' => '<p><code>skillflow:sync</code> refreshes the local folder and all repositories in one pass.</p><ul><li><strong>Cron-able</strong> — no web request or UI click needed.</li><li><strong>Scheduled</strong> — pair it with a TYPO3 scheduler task or a cron job.</li><li><strong>Separate indexing</strong> — <code>skillflow:solr:index</code> re-indexes skills for faceted search independently.</li></ul>', 'open_by_default' => 0],
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
                    'eyebrow' => 'Credit where it is due',
                    'header' => 'Part of a TYPO3 AI stack built on Netresearch',
                    'subheadline' => 'Skillflow sits alongside Netresearch\'s AI extensions for TYPO3 — nr_llm, nr_mcp_agent, nr_vault and t3_cowriter. Thank you, Netresearch DTT GmbH.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Turn your editorial pipeline into a quality checkpoint',
                    'description' => 'Skillflow brings focused AI review into TYPO3\'s workspace stages: consistent QA, faster drafts, and every team member\'s editorial eye without the manual review overhead.',
                    'cta_text' => 'Install skillflow on GitHub',
                    'cta_link' => 'https://github.com/dirnbauer/skillflow',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
