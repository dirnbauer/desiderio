<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Editorial deep dives: the GEO / AI-search explainer and the agentic
 * TYPO3 v14 strategy long read.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseStrategyPages
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [
            self::geoAiSearchPage(),
            self::typo3V14StrategyPage(),
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
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'SEO, meet GEO',
                    'header' => 'When the search result is an answer, not a link',
                    'subheadline' => 'Google AI Overviews, ChatGPT, and Perplexity increasingly answer queries directly and cite the pages they pulled from. Generative Engine Optimization (GEO) is the craft of being the page that gets pulled — and this is what it means for a Desiderio site, without the snake oil.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'What actually changed',
                    'content' => '<p>AI Overviews and assistant answers sit above the classic result list, and they are volatile: SEO practitioners such as Lily Ray have documented repeatedly how often Google changes which queries trigger an overview and which sources it cites. Nobody can promise you a citation. What you can control is extraction-friendliness: machines quote pages whose structure makes the answer easy to lift — clear headings, semantic markup, fast responses, and self-contained passages that answer one question each.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '',
                    'link_text' => '',
                ]),
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'The chances',
                    'header' => 'Why structured sites win in generative search',
                    'subheadline' => 'Everything that helps an LLM extract your content is a property of markup and performance — exactly the layer Desiderio controls.',
                    'items' => [
                        ['title' => 'Semantic HTML, by construction', 'description' => 'Landmarks, native elements, and one logical heading hierarchy per page are baked into all 244 elements. Extractors do not have to guess where the answer starts.'],
                        ['title' => 'Question-shaped content elements', 'description' => 'FAQ, accordion, how-to steps, and definition lists map one to one onto the question-answering format generative engines assemble their responses from.'],
                        ['title' => 'Clean metadata out of the box', 'description' => 'Meta descriptions, Open Graph, and Twitter cards per page, plus schema-friendly markup — the signals engines use to title and attribute their citations.'],
                        ['title' => 'Fast, static pages', 'description' => 'No client-side rendering between a crawler and your content: server-rendered Fluid, static CSS tokens, no JS framework. What the bot fetches is what the user reads.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'The honest part: risks and open questions',
                    'allow_multiple' => 1,
                    'items' => [
                        [
                            'title' => 'Zero-click loss is real',
                            'content' => '<p><strong>Zero-click loss is real</strong> — when the answer shows in the overview, fewer people click through, and publishers report falling click-through rates on AI-answered queries.</p><ul><li><strong>Convert harder</strong> — make the pages that do get visited work harder.</li><li><strong>Own your channels</strong> — treat newsletters, communities, and direct traffic as first-class.</li></ul>',
                            'open_by_default' => 1,
                        ],
                        [
                            'title' => 'Attribution is uncertain',
                            'content' => '<p><strong>Attribution is uncertain</strong> — assistants cite inconsistently, sometimes paraphrase without a link, and analytics still struggle to separate AI referrals.</p><ul><li><strong>Measure what you can</strong> — referral domains like chatgpt.com and perplexity.ai.</li><li><strong>Stay skeptical</strong> — of anyone selling guaranteed AI rankings.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'AI Overviews are volatile',
                            'content' => '<p><strong>AI Overviews are volatile</strong> — which queries trigger one changes constantly; analyses like Lily Ray\'s show large swings within weeks.</p><ul><li><strong>Build for durability</strong> — invest in extraction quality, not individual snapshots.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'What Desiderio gives you out of the box',
                            'content' => '<p>GEO-readiness as a side effect of doing HTML properly.</p><ul><li><strong>Semantic structure</strong> — landmark markup and heading discipline in every element.</li><li><strong>Per-page meta</strong> — meta and Open Graph support.</li><li><strong>Question-shaped content</strong> — FAQ and how-to elements.</li><li><strong>Machine-readable</strong> — translated screen-reader labels, plus server-rendered performance.</li></ul>',
                            'open_by_default' => 0,
                        ],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Build pages machines can quote and humans enjoy',
                    'description' => 'The same markup discipline that earns citations earns accessibility audits and Core Web Vitals. Install Desiderio for free and get all three.',
                    'cta_text' => 'Get Desiderio on GitHub',
                    'cta_link' => ShowcaseBlocks::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    private static function typo3V14StrategyPage(): array
    {
        return [
            'title' => 'TYPO3 v14 Agentic Strategy',
            'navTitle' => 'TYPO3 v14 Strategy',
            'slug' => '/typo3-v14-strategy',
            'abstract' => 'The results of webconsulting\'s long-running TYPO3 v14 analysis: an agentic CMS platform blueprint for editors, agencies and enterprises, built on TYPO3 practice since 2002.',
            'description' => 'The results of webconsulting\'s long-running TYPO3 v14 analysis: an agentic CMS platform blueprint for editors, agencies and enterprises, built on TYPO3 practice since 2002.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'webconsulting lab results',
                    'header' => 'TYPO3 v14 Agentic Platform Strategy',
                    'subheadline' => 'One CMS for editors, developers and autonomous agents. Twenty-three pillars, tested in a running lab - this is the TYPO3 v14 blueprint webconsulting puts in front of decision makers, built on project practice since 2002.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    'From research to a sellable platform',
                    'media-above',
                    'The lab result: a sellable operating model, not a trend slide.',
                    '<p>Everything on this page runs in the lab behind it: Desiderio, the MCP server, Skillflow, Agentation, Agent Nexus, OpenTag Bridge, Flue, the Abilities registry, llms.txt and x402 - tested against real pages, records, files and translations. The market moved too: analysts now call the category the agentic DXP, and every major CMS vendor is racing toward it.</p><ul><li><strong>Already running</strong> - a TYPO3 stack agents can inspect, write to and review through governed tools.</li><li><strong>Already concrete</strong> - 68 MCP tools (verified live), 18 of them projected from the abilities registry, plus the wider protocol family: A2UI, AG-UI, A2A, UCP and AP2.</li><li><strong>Already sellable</strong> - every pillar maps to speed, control, lower delivery cost or safer automation.</li></ul><p>TYPO3 does not need to pretend it is an AI toy: permissions, records, workspaces and auditability are exactly what an agentic CMS needs.</p>',
                    self::mcpStrategyImage('v14-00-analysis-results-source-803d947909f14720.png', 'webconsulting TYPO3 v14 strategy analysis table with modular platform architecture', 'webconsulting TYPO3 v14 strategy analysis table with modular platform architecture')
                ),
                self::v14StrategyTextmedia(
                    'How to read this page',
                    'media-above',
                    'Three layers, 23 pillars, one argument: controlled automation.',
                    '<p>The strategy builds in three layers. Each pillar follows the same rhythm: what already exists, what comes next, and why a client would pay for it.</p><ul><li><strong>Part I - the platform (pillars 1-12)</strong> - make TYPO3 operable by agents: content model, identity, tools, skills, models, money, commands, APIs and interfaces.</li><li><strong>Part II - the operating layer (pillars 13-16)</strong> - make that operation manageable: traces, trusted context, governance and durable jobs.</li><li><strong>Part III - the agentic web (pillars 17-23)</strong> - turn the installation outward: protocols, discoverability, provenance, sovereignty and standards.</li></ul><p>In a hurry? Jump to the readiness check at the end - eleven questions that tell you where any installation stands.</p>'
                ),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Part I · Pillars 1-12',
                    'header' => 'The platform layer: make TYPO3 operable by agents',
                    'subheadline' => 'Everything an agent touches needs a contract: content, identity, tools, skills, models, money, commands, APIs and interfaces. These twelve pillars are that contract - and most of them already run in this lab.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '1. Dual-audience design',
                    'media-right',
                    'One source serves editors, APIs and agents.',
                    '<p>TYPO3 brings the hard foundation: governed content, page trees, records, files, translations and workspaces. Desiderio adds typed, semantic Content Blocks on top - the same element looks polished in the browser and stays clean for APIs, search and AI assistants.</p><ul><li><strong>Already here</strong> - backend previews and frontend markup come from one element definition.</li><li><strong>Cool detail</strong> - accessibility, semantic HTML and structured content improve human UX and machine extraction at once.</li><li><strong>Why it sells</strong> - one editorial workflow feeds websites, headless views, AI search and copilots.</li></ul><p>Stop building a human website first and a machine interface later - build one governed surface for both.</p>',
                    self::mcpStrategyImage('v14-01-dual-audience-source-9dd58581c0bb5a84.png', 'human editor and autonomous agent views connected to one CMS source', 'human editor and autonomous agent views connected to one CMS source')
                ),
                self::v14StrategyTextmedia(
                    '2. Identity management for agents',
                    'media-left',
                    'No shared admin logins. Every agent gets an owner, a scope and a trail.',
                    '<p>Automation becomes enterprise-ready when every automated action has an identity. The lab has moved this from concept to practice with real credentials for real agents.</p><ul><li><strong>Already here</strong> - backend permissions, workspace ownership, personal access tokens, OAuth with PKCE, scoped and expiring API tokens bound to real backend users, per-ability risk tiers and enterprise login via WorkOS.</li><li><strong>Next</strong> - first-class agent users with expiry, delegation records and consent trails, as auditable as an employee account.</li><li><strong>Why it sells</strong> - a client can approve a translation agent without handing over the whole backend.</li></ul><p>Explicit authority lets automation move faster - security becomes the reason TYPO3 is credible, not the blocker.</p>',
                    self::mcpStrategyImage('v14-02-agent-identity-source-c43c33de79ceada3.png', 'secure agent identity model with scoped permissions and audit trails', 'secure agent identity model with scoped permissions and audit trails')
                ),
                self::v14StrategyTextmedia(
                    '3. MCP: the right tools, not every endpoint',
                    'media-right',
                    'The live lab has 68 registered MCP tools - governed, named and inspectable.',
                    '<p>Do not expose every endpoint and hope the model behaves. The lab proves the better pattern: a deliberate MCP toolbox with narrow operations for pages, content, records, files, workspaces, Solr, payments and diagnostics - under a protocol that now lives at the Linux Foundation, with Tasks for long-running work in the 2026 spec.</p><ul><li><strong>Already here</strong> - 68 tools: 50 hand-built, from <code>GetPageTree</code> to <code>x402_stats</code>, plus 18 projected straight from the abilities registry.</li><li><strong>Next</strong> - execute the written 2026-spec adoption plan, list the server in the official MCP registry, and generate more tools from the capability registry (pillar 19).</li><li><strong>Why it sells</strong> - clients buy a controlled operating surface, not a chat box wired to production.</li></ul><p>The count matters less than the discipline: useful enough to ship work, small enough to govern.</p>',
                    self::mcpStrategyImage('v14-03-mcp-tools-source-7c8f3c4124bab359.png', 'compact governed MCP toolbox connected to a CMS core', 'compact governed MCP toolbox connected to a CMS core')
                ),
                self::v14StrategyTextmedia(
                    '4. Agentic skills',
                    'media-left',
                    'Turn senior TYPO3 delivery into reusable, versioned workflows.',
                    '<p>Prompts are not a process - skills are. The lab turns TYPO3 know-how into reusable instructions: create a landing page, review accessibility, prepare translations, validate a launch.</p><ul><li><strong>Already here</strong> - Skillflow manages skills as TYPO3 records, syncs them from repositories and makes them searchable.</li><li><strong>Cool detail</strong> - skills attach to workspace stages, so review becomes part of the editorial pipeline.</li><li><strong>Why it sells</strong> - agencies package their best delivery practice instead of depending on whoever remembers the checklist.</li></ul><p>This is the product layer above AI tooling: repeatable work that gets better over time.</p>',
                    self::mcpStrategyImage('v14-04-agentic-skills-source-e4920a8a296a4350.png', 'reusable agentic skill library for repeatable TYPO3 workflows', 'reusable agentic skill library for repeatable TYPO3 workflows')
                ),
                self::v14StrategyTextmedia(
                    '5. LLM-agnostic libraries',
                    'media-right',
                    'Use the best model for the job without rewiring the CMS.',
                    '<p>The model market keeps moving; the CMS integration must not. The lab runs one shared abstraction for generation, embeddings, tool use, cost metadata and provider configuration.</p><ul><li><strong>Already here</strong> - Netresearch\'s <strong>nr_llm</strong> as the shared LLM foundation, with <strong>nr_vault</strong> keeping credentials under envelope encryption with audit logs.</li><li><strong>Cool detail</strong> - premium models for high-value content, cheaper or local models for bulk and sensitive work - chosen per task, by configuration.</li><li><strong>Why it sells</strong> - procurement keeps leverage on price, data residency and compliance while models swap underneath.</li></ul><p>TYPO3 owns the workflow; models are replaceable engines behind it - and the swap layer is installed, not promised.</p>',
                    self::mcpStrategyImage('v14-05-llm-agnostic-source-8ce0578695dbc362.png', 'LLM-agnostic AI adapter connecting TYPO3 to multiple model engines', 'LLM-agnostic AI adapter connecting TYPO3 to multiple model engines')
                ),
                self::v14StrategyTextmedia(
                    '6. Token billing and usage economics',
                    'media-left',
                    'AI becomes a product when usage is visible, priced and capped.',
                    '<p>Every serious AI feature creates variable cost - and machine readership is becoming revenue. The lab measures costly operations instead of hiding them, and stays deliberately rail-agnostic: x402 leads in production traction while AP2, Stripe\'s agentic sessions and the retail checkout protocols compete.</p><ul><li><strong>Already here</strong> - x402 page and API gating, payment statistics, AP2 mandate demos in Agent Nexus and paid-content MCP tools.</li><li><strong>Next</strong> - token metering per user, model, task and customer - plus charging AI crawlers, the way Cloudflare\'s pay-per-crawl now normalizes.</li><li><strong>Why it sells</strong> - AI-assisted translation, QA and migration become priceable services instead of a mystery invoice.</li></ul><p>Buyers understand budgets. Metering turns AI from a cost center into a revenue line.</p>',
                    self::mcpStrategyImage('v14-06-token-billing-source-ba738535fee79b21.png', 'AI token billing meters with budget controls and usage flows', 'AI token billing meters with budget controls and usage flows')
                ),
                self::v14StrategyTextmedia(
                    '7. CLI: deterministic, agent-friendly and powerful',
                    'media-above',
                    'Agents trust commands that validate input and return stable output.',
                    '<p>Browser automation is fragile; deterministic commands are not. The lab drives seeding, diagnostics, indexing and MCP inspection through the CLI - JSON output, clear exit codes, idempotent operations.</p><ul><li><strong>Already here</strong> - commands for styleguide seeding, MCP tool inspection, Solr indexing, Skillflow sync and health checks.</li><li><strong>Next</strong> - promote the CLI as an official automation surface for content, schema, cache and workspace operations.</li><li><strong>Why it sells</strong> - a command that can be repeated, logged and rolled back is a product feature.</li></ul><p>Not glamorous - but this is what makes autonomous work reliable.</p>',
                    self::mcpStrategyImage('v14-07-deterministic-cli-source-24312cffc0f95704.png', 'deterministic command console for TYPO3 automation and agent operations', 'deterministic command console for TYPO3 automation and agent operations')
                ),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Proof point: CLI output an agent can trust',
                    'code' => "# Describe the installation as structured JSON\n$ typo3 describe --format-json\n{\n  \"site\": \"example\",\n  \"typo3_version\": \"14.3.2\",\n  \"extensions\": [ ... ],\n  \"sites\": [ ... ],\n  \"languages\": [ ... ],\n  \"workspaces\": [ ... ]\n}\n\n# Agent-ready operating commands\n$ typo3 schema:export      # full TCA and Content Blocks schema\n$ typo3 content:apply      # idempotent content changes from a file\n$ typo3 site:diagnose      # health and misconfiguration report\n$ typo3 workspace:preview  # shareable preview of staged changes",
                    'language' => 'bash',
                    'filename' => 'typo3-agent-ops.sh',
                ]),
                self::v14StrategyTextmedia(
                    '8. Backend: headless by design',
                    'media-right',
                    'Editors keep the UI. Agents get the same backend as typed operations.',
                    '<p>The backend stays the place where editors understand and control the site. The v14 shift happens underneath: pages, content, files, forms and workspaces as typed operations instead of screen scraping.</p><ul><li><strong>Already here</strong> - Content Blocks, backend previews, Visual Editor support, workspaces and command-oriented extension surfaces.</li><li><strong>Cool detail</strong> - Agentation points at a real page element and carries selector-level feedback into an AI workflow.</li><li><strong>Why it sells</strong> - human approval and programmable delivery share one content model.</li></ul><p>That is the difference between AI bolted onto a CMS and a CMS built for agentic operations.</p>',
                    self::mcpStrategyImage('v14-08-headless-backend-source-d0146a4caa5464a8.png', 'programmable headless TYPO3 backend with structured API lanes', 'programmable headless TYPO3 backend with structured API lanes')
                ),
                self::v14StrategyTextmedia(
                    '9. APIs like tRPC, built for PHP realities',
                    'media-left',
                    'One typed procedure serves PHP, REST, MCP and external clients.',
                    '<p>The abilities registry ships the whole idea: define an operation once - name, typed contract, scopes, risk tier, side effects - and every surface becomes a projection. MCP tools are generated by a compiler pass, the CLI runs the same pipeline, and sg_apicore serves the REST lane with backend-user-bound tokens.</p><ul><li><strong>Already here</strong> - the registry, the governed executor, an execution policy with human-in-the-loop review, traces per attempt, and MCP/CLI/REST/desktop projections.</li><li><strong>Cool detail</strong> - the same registration powers an AI agent over MCP and the desktop editor\'s publish button.</li><li><strong>Why it sells</strong> - fewer duplicate controllers, fewer inconsistent integrations, less hidden logic.</li></ul><p>WordPress proved the architecture with its Abilities API; pillar 19 completes it with governance TYPO3-style: one contract per operation, several safe ways to call it.</p>',
                    self::mcpStrategyImage('v14-09-typed-php-api-source-d6f9236e51205bc3.png', 'typed PHP procedure contracts connected to web, mobile and agent clients', 'typed PHP procedure contracts connected to web, mobile and agent clients')
                ),
                self::v14StrategyTextmedia(
                    '10. Simple interfaces for AI work',
                    'media-right',
                    'Point at the issue. Capture the context. Let the agent work precisely.',
                    '<p>The best AI interface for CMS work is often not a chat field - it is an annotation on the page itself: this headline is weak, this legal block must not change.</p><ul><li><strong>Already here</strong> - Agentation captures element-level feedback with selector, DOM context, styles and intent for Claude Code, Cursor or MCP agents.</li><li><strong>Next</strong> - close the loop: annotation to agent run to reviewable workspace diff.</li><li><strong>Why it sells</strong> - editors stay in the page; agents receive exactly the context they need.</li></ul><p>It removes the worst part of AI collaboration: explaining where the problem is.</p>',
                    self::mcpStrategyImage('v14-10-ai-feedback-source-a886d8e44543124f.png', 'in-context AI feedback interface with annotations on a web page preview', 'in-context AI feedback interface with annotations on a web page preview')
                ),
                self::v14StrategyTextmedia(
                    '11. MCP-based chatbot and editorial assistant',
                    'media-above',
                    'Chat is useful when it calls the same governed tools as everything else.',
                    '<p>A CMS assistant becomes serious when it is not a parallel system. The lab runs <strong>nr_mcp_agent</strong> - an AI chat assistant inside the TYPO3 backend that talks to the same MCP toolbox as every other agent.</p><ul><li><strong>Already here</strong> - the assistant reads pages, imports content, writes records, attaches media and reviews workspaces.</li><li><strong>Cool detail</strong> - permissions and preview workflows still apply; chat has no back door.</li><li><strong>Why it sells</strong> - chat feels fast while governance stays in TYPO3 instead of a black box.</li></ul><p>The assistant is not magic - it is a friendly command surface over a controlled operating layer.</p>',
                    self::mcpStrategyImage('v14-11-mcp-chatbot-source-4b1f82b53165e55e.png', 'MCP-based editorial assistant connected to governed CMS tools', 'MCP-based editorial assistant connected to governed CMS tools')
                ),
                self::v14StrategyTextmedia(
                    '12. AI-optimized codebase',
                    'media-left',
                    'Code that agents can understand is code teams can maintain.',
                    '<p>Desiderio demonstrates the discipline: typed Fluid components, focused data classes, tests, predictable templates and design tokens - easy for humans and LLMs to inspect.</p><ul><li><strong>Already here</strong> - typed template arguments, reusable components, unit tests, accessibility checks and deterministic seed data.</li><li><strong>Cool detail</strong> - the element library is structured enough for agents to pick the right block instead of guessing from screenshots.</li><li><strong>Why it sells</strong> - cleaner architecture lowers the cost of upgrades, automation and future features.</li></ul><p>Messy extensions can be prompted. Clear extensions can be operated, tested and sold.</p>',
                    self::mcpStrategyImage('v14-12-ai-codebase-source-5f36db22835849de.png', 'AI-optimized TYPO3 codebase shown as maintainable architecture modules', 'AI-optimized TYPO3 codebase shown as maintainable architecture modules')
                ),
                self::v14StrategyTextmedia(
                    'Installed in this lab: the TYPO3 AI stack already running',
                    'media-right',
                    'Not hypothetical: the pieces are installed and visible in the lab.',
                    '<p>The lab combines everything this page argues for. The foundation: Desiderio, MCP tools, sg_apicore, Skillflow, Agentation, x402, search, forms, enterprise auth and workspace review - with the AI layer resting on Netresearch\'s nr_llm, nr_mcp_agent, nr_vault and t3_cowriter. The newest wave widens tools into protocols and channels: <strong>Agent Nexus</strong> (five agent protocols, live), <strong>OpenTag Bridge</strong> (Slack control with approvals and ledger), <strong>Flue</strong> (durable runtime), <strong>Abilities</strong> (typed capability registry with four live projections) and <strong>llms.txt</strong> (machine-readable site surfaces).</p><ul><li><strong>Demo value</strong> - clients see real content, real modules and real operations - not architecture slides.</li><li><strong>Implementation value</strong> - everything uses TYPO3 concepts: pages, records, Content Blocks, FAL, site settings, workspaces.</li><li><strong>Roadmap value</strong> - what is installed makes the direction concrete enough to sell and refine.</li></ul><p>Buyers do not need another AI keynote. They need to see which parts install, govern and connect to real editorial work.</p>',
                    self::mcpStrategyImage('v14-13-installed-stack-source-d2f467651a18dbc9.png', 'Installed TYPO3 AI lab stack', 'Installed TYPO3 AI lab stack with LLM, MCP, vault and monitoring modules.')
                ),
                self::v14StrategyTextmedia(
                    'Built on Netresearch\'s AI foundation',
                    'media-above',
                    'Credit where it is due — four open-source extensions carry the AI layer.',
                    '<p>The AI layer of this lab runs on four open-source extensions by Netresearch DTT GmbH: <strong>nr_llm</strong> (the shared LLM foundation), <strong>nr_mcp_agent</strong> (the backend assistant), <strong>nr_vault</strong> (encrypted secrets) and <strong>t3_cowriter</strong> (an AI writing partner for editors). Thank you.</p>'
                ),
                self::v14StrategyTextmedia(
                    'The full MCP toolbox',
                    'media-above',
                    '68 registered tools prove the operating surface is real.',
                    '<p>The lab registry exposes 68 MCP tools - 50 hand-built plus 18 projected from the abilities registry - covering the lanes TYPO3 projects actually need: pages, content, records, files, schemas, imports, site settings, workspaces, logs, Solr, paid content and payments.</p><ul><li><strong>Content lane</strong> - <code>GetPage</code>, <code>GetPageTree</code>, <code>ImportContent</code>, <code>BulkWrite</code>, <code>AttachImage</code> and <code>RenderRecord</code>.</li><li><strong>Operations lane</strong> - <code>WorkspaceReview</code>, <code>PublishWorkspace</code>, <code>RollbackWorkspace</code>, <code>GetSystemLog</code>, <code>SafeCli</code> and <code>SolrIndexQueue</code>.</li><li><strong>Commercial lane</strong> - <code>GetPaidContent</code>, <code>GetPaymentStats</code>, <code>x402_stats</code>, <code>x402_transactions</code> and related probes.</li></ul><p>The point is not the count: TYPO3 work becomes a named toolbox - inspectable, permissionable, teachable.</p>',
                    self::mcpStrategyImage('v14-14-full-toolbox-source-ec3c4504e7475b2a.png', 'complete MCP toolbox matrix for structured TYPO3 content operations', 'complete MCP toolbox matrix for structured TYPO3 content operations')
                ),
                self::v14StrategyTextmedia(
                    'How the MCP stays secure',
                    'media-above',
                    'Powerful tools need deliberate fences, not wishful thinking.',
                    '<p>The MCP surface writes records, uploads files and touches caches - so it is treated like an operations interface, with the security implemented rather than promised.</p><ul><li><strong>Already here</strong> - OAuth with PKCE, personal access tokens, runtime-enforced ability policies with risk tiers and FAL-bounded file tooling.</li><li><strong>Cool detail</strong> - read, write, publish and payment tools carry different scopes and review rules per environment.</li><li><strong>Why it sells</strong> - webconsulting can define exactly which tools run in local dev, staging, production and managed operations.</li></ul><p>TYPO3 already thinks in permissions and workflows - MCP amplifies that discipline instead of bypassing it.</p>'
                ),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Part II · Pillars 13-16',
                    'header' => 'The operating layer: make agent work manageable',
                    'subheadline' => 'The platform makes agents able to work. Serious customers then ask how that work is observed, governed, resumed and reviewed. These four pillars answer them - and each already has its first bricks in the lab.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '13. AgentOps: traces, evals and rollback',
                    'media-right',
                    'Autonomy needs traces, evals, cost visibility and rollback.',
                    '<p>A production CMS cannot trust an agent because one demo looked good. Every meaningful run needs evidence: context, tool, input, output, changed records, cost, reviewer and rollback path.</p><ul><li><strong>Already here</strong> - OpenTag\'s audit ledger for channel runs, the abilities registry tracing every execution attempt on every surface including denials, and Skillflow run reports with verdict and score.</li><li><strong>Next</strong> - unify those lanes into one trace store with eval sets and regression checks across all agents.</li><li><strong>Why it sells</strong> - teams can prove where automation saves time, where it fails and what is safe to scale.</li></ul><p>The platform should know what happened, why it happened and how to undo it.</p>',
                    self::mcpStrategyImage('14-agentops-source-dd06b2ff43dcd398.png', 'AgentOps control room', 'Agent operations control room with evaluation checkpoints, traces, cost meters and approval signals for autonomous TYPO3 workflows.')
                ),
                self::v14StrategyTextmedia(
                    '14. Context fabric: trusted knowledge, not random retrieval',
                    'media-right',
                    'TYPO3 already holds the knowledge agents need. Package it safely.',
                    '<p>Page trees, records, TCA schemas, file metadata, redirects, access rules and editorial history - the raw material for a permission-aware context fabric. Honest status: this is the pillar with the least code behind it, and TYPO3\'s SEAL search abstraction is the natural vehicle.</p><ul><li><strong>Already here</strong> - structured records, FAL metadata, Solr indexing, table schemas and workspace overlays.</li><li><strong>Next</strong> - a semantic index that respects permissions at query time, so agents get source-backed context instead of random snippets.</li><li><strong>Why it sells</strong> - legacy TYPO3 knowledge becomes reusable AI infrastructure instead of tribal memory.</li></ul><p>Agent quality follows context quality - and TYPO3 knows where content lives, who may see it and how it relates.</p>',
                    self::mcpStrategyImage('15-context-fabric-source-3e3720be00f77da1.png', 'Context fabric knowledge graph', 'Permission-aware context fabric connecting CMS pages, records, files, vector search, provenance and access controls.')
                ),
                self::v14StrategyTextmedia(
                    '15. Governance: policy, consent and human review',
                    'media-right',
                    'Let agents move fast inside explicit boundaries.',
                    '<p>Identity answers who may act. Governance answers when the system may act alone, when it must ask, and what proof publication requires.</p><ul><li><strong>Already here</strong> - workspace review, page permissions, roles and history - plus OpenTag\'s policy gate with human approval and the abilities policy\'s deny and review rules per ability, proven live on a high-risk workspace publish.</li><li><strong>Next</strong> - promote policies from configuration files to governed TYPO3 records with risk tiers.</li><li><strong>Why it sells</strong> - a well-governed installation automates more, not less, because boundaries are explicit.</li></ul><p>Speed is easy to promise. Controlled speed is what enterprises buy.</p>',
                    self::mcpStrategyImage('16-governance-source-04c1d1026572f360.png', 'Governance approval gates', 'Human governance workflow with risk tiers, approval gates, policy cards, consent checkpoints and audit trail controls.')
                ),
                self::v14StrategyTextmedia(
                    '16. Durable runtime: jobs that survive real life',
                    'media-right',
                    'Real agent work needs state, queues, retries, approvals and handoffs.',
                    '<p>Useful agent tasks rarely finish in one reply - they import, enrich, wait for approval, retry and resume. That takes a durable runtime.</p><ul><li><strong>Already here</strong> - CLI commands, Scheduler patterns, workspace previews - and the Flue bridge running a real durable runtime with TYPO3 as control plane, runs mirrored into the backend.</li><li><strong>Next</strong> - every long job exposes owner, state, affected records, retry policy, cost and next action - aligned with the 2026 MCP Tasks standard.</li><li><strong>Why it sells</strong> - migrations, launches, localization and bulk QA become managed workflows instead of fragile prompt sessions.</li></ul><p>The platform wins when long-running work survives failures, waiting, humans and Monday-morning handoffs.</p>',
                    self::mcpStrategyImage('17-durable-runtime-source-620da710d6dd8b1f.png', 'Durable agent runtime', 'Durable agent runtime with queues, retries, workflow lanes, state hub and handoff stations for autonomous TYPO3 jobs.')
                ),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Part III · Pillars 17-23',
                    'header' => 'The agentic web: make the installation a citizen of it',
                    'subheadline' => 'Agents no longer just read the web - they visit, delegate, buy and cite. The last seven pillars turn a TYPO3 installation outward: multi-protocol, discoverable, monetizable, provable, sovereign and standardized.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '17. Agent protocols beyond MCP: A2UI, AG-UI, A2A, UCP and AP2',
                    'media-right',
                    'MCP connects tools. The agentic web also needs UI, delegation and commerce lanes.',
                    '<p>Browser agents went mainstream in 2026: software now visits sites to ask, delegate and buy. Beyond MCP\'s tool lane, that takes A2UI (agent to UI), AG-UI (agent to user, with approval gates), A2A (agent to agent), UCP (agent to merchant) and AP2 (signed payment mandates).</p><ul><li><strong>Already here</strong> - Agent Nexus demos all five protocols live: a backend field guide, five playgrounds and five frontend plugins, with human gates before every write and payment.</li><li><strong>Cool detail</strong> - every run carries a provenance label: real model answers are marked live, deterministic fallbacks as scripted.</li><li><strong>Why it sells</strong> - TYPO3 becomes a CMS that can receive agents - inquiries, delegated tasks, shopping carts - not just host content they scrape.</li></ul><p>Do not bet on one protocol. Keep the governed core and treat protocols as replaceable adapters.</p>',
                    self::mcpStrategyImage('v14-17-agent-protocols-source-1daaafd8039b3b94.png', 'five agent protocol lanes converging on one governed CMS hub', 'five agent protocol lanes converging on one governed CMS hub')
                ),
                self::v14StrategyTextmedia(
                    '18. Channel operations: steer TYPO3 from Slack and Co.',
                    'media-right',
                    'Editors delegate from the tools they already live in - with an approve tap.',
                    '<p>"Draft a teaser about the summer opening on page 12" - written in Slack, drafted by TYPO3, published only after an explicit approve tap. OpenTag Bridge makes it real, with the LLM, permissions, budgets and audit ledger all inside TYPO3.</p><ul><li><strong>Already here</strong> - a guarded pipeline per action: token guard, rate limiter, identity mapping to real backend users, policy gate, human approval, ledger entry.</li><li><strong>Next</strong> - Discord, Telegram and Teams connectors: the agent backend is TYPO3, not the messenger.</li><li><strong>Why it sells</strong> - the opposite of per-seat cloud bots that are blind to the CMS: your model, your permissions, your audit log.</li></ul><p>Channel operations meet editors where they already are - made safe by pillars 13 and 15.</p>',
                    self::mcpStrategyImage('v14-18-channel-operations-source-08ea54cb6f898602.png', 'chat channel steering a CMS through an approval gate', 'chat channel steering a CMS through an approval gate')
                ),
                self::v14StrategyTextmedia(
                    '19. A capability registry, not hand-rolled endpoints',
                    'media-right',
                    'One typed registry of what the CMS can do - live in this lab across four surfaces.',
                    '<p>WordPress proved the 2026 architecture lesson: one typed, permissioned registry of capabilities, projected into every protocol. This lab ships that bet: 18 abilities declare name, contract, scopes, risk tier and side effects once, every call runs through one governed pipeline - and the earlier capability manifests were retired into it.</p><ul><li><strong>Already here</strong> - all four projections verified live: CLI commands, 18 generated MCP tools, a REST endpoint on scoped backend-user tokens, and the desktop editor delegating its operations to the registry.</li><li><strong>Cool detail</strong> - the human gate is proven: a high-risk workspace publish is denied by policy and runs only with an explicit approval - and every attempt lands in the trace table, whatever the surface.</li><li><strong>Why it sells</strong> - clients stop paying for the same integration five times; audits cover one registry instead of five surfaces.</li></ul><p>Protocols churn. Capabilities do not. Next: policy records in TCA, the 2026 MCP-spec projection - and proposing the pattern to the TYPO3 AI initiative (pillar 23).</p>',
                    self::mcpStrategyImage('v14-19-capability-registry-source-d683bece12f4939b.png', 'central capability registry projecting into four protocol surfaces', 'central capability registry projecting into four protocol surfaces')
                ),
                self::v14StrategyTextmedia(
                    '20. The machine-readable, monetizable site',
                    'media-right',
                    'The zero-click web is measured reality. This lab publishes for machines by default.',
                    '<p>AI answers cut outbound clicks by roughly forty percent, and infrastructure providers now meter AI crawlers. The answer is not hiding - it is publishing deliberately, with a price tag where content has value.</p><ul><li><strong>Already here</strong> - schema.org JSON-LD, question-shaped elements, x402 gating - and llms.txt plus agents.md, generated per site from the page tree.</li><li><strong>Cool detail</strong> - only visible, indexable pages are listed; agents.md advertises the MCP endpoint, the abilities registry and the payment lane, detected at runtime.</li><li><strong>Why it sells</strong> - publishers turn lost clicks into licensed machine access; marketing gets cited instead of silently summarized.</li></ul><p>Pillar 1 was the inward half of this story. The outward half is live: a site agents can discover, quote - and pay for.</p>',
                    self::mcpStrategyImage('v14-20-machine-readable-site-source-74c606b3b658cdb7.png', 'website with human and machine views and a metered crawler gate', 'website with human and machine views and a metered crawler gate')
                ),
                self::v14StrategyTextmedia(
                    '21. Trust, provenance and AI-Act compliance',
                    'media-right',
                    'From August 2026, AI transparency is EU law - and the CMS is where it gets done.',
                    '<p>Article 50 of the EU AI Act applies from August 2026: disclose AI interaction, mark generative output machine-readably - with penalties in the millions. TYPO3\'s file and record architecture is the natural home for those duties.</p><ul><li><strong>Already here</strong> - inbound media forensics (is this asset manipulated?) and an agent audit trail (pillar 13) that doubles as the compliance log.</li><li><strong>Next</strong> - C2PA Content Credentials read and preserved in FAL metadata, AI-disclosure patterns and marking wired into the AI pipelines.</li><li><strong>Why it sells</strong> - compliance becomes a recurring service line: provenance audits, disclosure reviews and accessibility checks in one contract.</li></ul><p>Trust is the product. A CMS that can prove where content came from is worth more than one that merely publishes fast.</p>',
                    self::mcpStrategyImage('v14-21-provenance-compliance-source-7e28a10889754a33.png', 'media assets with provenance seals under robot inspection', 'media assets with provenance seals under robot inspection')
                ),
                self::v14StrategyTextmedia(
                    '22. European sovereignty as a product',
                    'media-right',
                    'Your models, your audit log, your data - a positioning US SaaS cannot copy.',
                    '<p>The biggest independent headless CMS now belongs to a US hyperscaler\'s agent platform, while the EU Data Act pushes switchability and protection from non-EU access. Where the agentic CMS runs is procurement policy now.</p><ul><li><strong>Already here</strong> - the whole stack self-hosts: TYPO3, MCP server, agent bridges, audit ledger and a swappable LLM layer - documented as a reference architecture with three trust tiers, from own inference to EU APIs to opted-in non-EU models.</li><li><strong>Cool detail</strong> - model choice per task: premium for high-value content, EU-hosted or local for sensitive workflows.</li><li><strong>Why it sells</strong> - "agentic CMS on EU-sovereign infrastructure" wins the deals where US SaaS is disqualified before the demo.</li></ul><p>TYPO3\'s European DNA used to be a footnote. In the agentic era it is a moat.</p>',
                    self::mcpStrategyImage('v14-22-eu-sovereignty-source-fd9c6345c397f780.png', 'sovereign EU data infrastructure under a protective dome', 'sovereign EU data infrastructure under a protective dome')
                ),
                self::v14StrategyTextmedia(
                    '23. Standardize and upstream: shape the TYPO3 AI initiative',
                    'media-right',
                    'A lead is worth most as ecosystem leadership, not as a private fork.',
                    '<p>Drupal institutionalized its AI push with dozens of backing organizations; TYPO3\'s initiative is younger and still interface-stage. That is the opportunity: patterns contributed now become the standard.</p><ul><li><strong>Already here</strong> - working reference implementations: the MCP server, the abilities registry, workspace-staged agent writes and the skills format.</li><li><strong>Next</strong> - TER releases, initiative participation, one public reference write-up per quarter.</li><li><strong>Why it sells</strong> - clients buy the agency that wrote the standard, and TYPO3 gets an answer to the agentic-DXP wave.</li></ul><p>The endgame is not a secret toolbox - it is TYPO3 competing credibly, with webconsulting\'s patterns at the center.</p>',
                    self::mcpStrategyImage('v14-23-standardize-upstream-source-eff9bd20130a7f66.png', 'lab patterns flowing upstream into a shared community platform', 'lab patterns flowing upstream into a shared community platform')
                ),
                self::v14StrategyTextmedia(
                    'Readiness check for TYPO3 v14+',
                    'media-right',
                    'Eleven questions that tell you where any installation stands.',
                    '<p>An installation is ready for agentic work when it answers these without hand-waving. First, the operating layer:</p><ul><li>Which agent changed which record, with which permission, and why?</li><li>Can a failed operation be rolled back or replayed safely?</li><li>Does the agent receive context that is current, scoped and source-backed?</li><li>Can humans review high-risk changes before publication?</li><li>Are AI costs visible enough to price, cap and report?</li><li>Can long-running work pause, resume and hand off?</li></ul><p>Then the agentic web:</p><ul><li>Does the site disclose AI interactions and mark generated output, as Article 50 demands?</li><li>Can media provenance be proven and manipulation detected?</li><li>Can an external agent discover what the site offers - and can you charge it for access?</li><li>Which agent protocols does the installation speak beyond MCP?</li><li>Can the whole stack run on EU-sovereign infrastructure with exchangeable models?</li></ul><p>TYPO3 already has the primitives: records, roles, workspaces, files, logs, scheduler patterns and extension APIs. The opportunity is connecting them into one operating model and selling it as controlled automation, not AI decoration.</p>'
                ),
                self::v14StrategyTextmedia(
                    'Conclusion: the platform webconsulting would sell',
                    'media-above',
                    'TYPO3 can become the governed operating system for content and agents.',
                    '<p>This is not a CMS with a few AI buttons. It is a governed operating layer where editors, APIs and agents work from one content model - with clear permissions, structured tools, workspace review and measurable economics, exactly as the market renames the category to agentic DXP and buyers start punishing decoration.</p><ul><li><strong>For agencies</strong> - strategy, implementation, migration, compliance and managed AI operations become billable packages.</li><li><strong>For enterprises</strong> - automation arrives without giving up governance, accountability or European sovereignty.</li><li><strong>For TYPO3</strong> - the platform competes by leaning into what it always did well, and by standardizing what this lab proves.</li></ul><p>Agentic CMS work needs exactly the boring-serious infrastructure TYPO3 already has. The years to the next LTS are the window.</p>',
                    self::mcpStrategyImage('v14-15-conclusion-source-6178b41759412e71.png', 'final TYPO3 v14 agentic platform operating model presented as analysis results', 'final TYPO3 v14 agentic platform operating model presented as analysis results')
                ),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Plan your TYPO3 v14 agentic platform with webconsulting',
                    'cta_text' => 'Start the TYPO3 v14 strategy',
                    'cta_link' => '/contact',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @param array{file: string, title: string, alternative: string, description: string, source: string}|null $media
     * @return ShowcaseBlock
     */
    private static function v14StrategyTextmedia(string $header, string $layout, string $subheadline, string $content, ?array $media = null): array
    {
        $fields = [
            'header' => $header,
            'shadcn_layout' => $layout,
            'subheadline' => $subheadline,
            'content' => $content,
            'media_rounded' => 1,
        ];

        if ($media !== null) {
            $fields['media'] = $media;
        }

        return ShowcaseBlocks::block('desiderio_textmedia', $fields);
    }

    /**
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    private static function mcpStrategyImage(string $filename, string $title, string $alternative): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Mcp/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Generated visual for the TYPO3 v14 agentic strategy page.',
            'source' => ShowcaseBlocks::REPO_URL,
        ];
    }
}
