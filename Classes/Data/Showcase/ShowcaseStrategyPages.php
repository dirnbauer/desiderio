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
            'title' => 'GEO: visibility in AI search',
            'navTitle' => 'GEO and AI search',
            'slug' => '/geo-ai-search',
            'abstract' => 'What Generative Engine Optimisation (GEO) means for a Desiderio site. The page covers what AI answers change, the chances, the risks and what the package includes.',
            'description' => 'How semantic markup, a clean heading order, FAQ elements and page metadata in Desiderio prepare a TYPO3 site for AI Overviews and AI citations.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'SEO and GEO',
                    'header' => 'When search shows an answer, not a link',
                    'subheadline' => 'Google AI Overviews, ChatGPT and Perplexity answer many queries directly and cite the pages they used. Generative Engine Optimisation (GEO) is the work of becoming one of those pages.',
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => 'What has changed',
                    'content' => '<p>AI Overviews and assistant answers appear above the classic result list, and they change often. SEO practitioners such as Lily Ray have documented how often Google changes which queries show an overview and which sources it cites. Nobody can promise you a citation. What you can control is how easy your pages are to extract: clear headings, semantic markup, fast responses and passages that each answer one question.</p>',
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => '',
                    'link_text' => '',
                ]),
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'The chances',
                    'header' => 'What helps AI search use your content',
                    'subheadline' => 'What helps a language model extract your content is markup and performance. Desiderio controls exactly that layer.',
                    'items' => [
                        ['title' => 'Semantic HTML in every element', 'description' => 'All 244 elements use landmarks, native HTML elements and one logical heading order per page. Extractors can see where the answer starts.'],
                        ['title' => 'Elements shaped like questions', 'description' => 'FAQ, accordion, how-to steps and definition lists match the question-and-answer format that AI answers are built from.'],
                        ['title' => 'Clean metadata on every page', 'description' => 'Each page has a meta description, Open Graph and Twitter cards, plus schema-friendly markup. Engines use these to title and attribute their citations.'],
                        ['title' => 'Fast, static pages', 'description' => 'Fluid renders pages on the server with static CSS tokens and no JavaScript framework. The crawler gets the same content as the reader.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_accordion', [
                    'header' => 'Risks and open questions',
                    'allow_multiple' => 1,
                    'items' => [
                        [
                            'title' => 'Fewer people click through',
                            'content' => '<p><strong>Zero-click loss is real.</strong> When the answer appears in the overview, fewer people visit the page. Publishers report falling click-through rates on queries that AI answers.</p><ul><li><strong>Convert better</strong>: improve the pages that people still visit.</li><li><strong>Own your channels</strong>: give newsletters, communities and direct traffic the same weight as search.</li></ul>',
                            'open_by_default' => 1,
                        ],
                        [
                            'title' => 'Attribution is uncertain',
                            'content' => '<p><strong>Attribution is uncertain.</strong> Assistants cite sources inconsistently and sometimes paraphrase without a link. Analytics tools still struggle to separate AI referrals.</p><ul><li><strong>Measure what you can</strong>: referral domains such as chatgpt.com and perplexity.ai.</li><li><strong>Stay sceptical</strong> of anyone who sells guaranteed AI rankings.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'AI Overviews change often',
                            'content' => '<p><strong>AI Overviews change often.</strong> Which queries show one changes all the time, and analyses such as Lily Ray\'s show large swings within weeks.</p><ul><li><strong>Build for the long term</strong>: invest in pages that are easy to extract, not in single snapshots.</li></ul>',
                            'open_by_default' => 0,
                        ],
                        [
                            'title' => 'What Desiderio includes',
                            'content' => '<p>You get GEO readiness because the HTML is built properly.</p><ul><li><strong>Semantic structure</strong>: landmarks and a clean heading order in every element.</li><li><strong>Metadata per page</strong>: meta and Open Graph support.</li><li><strong>Question-shaped content</strong>: FAQ and how-to elements.</li><li><strong>Machine-readable pages</strong>: translated screen-reader labels and fast server-rendered output.</li></ul>',
                            'open_by_default' => 0,
                        ],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Build pages people and machines can read',
                    'description' => 'The markup that earns citations also helps with accessibility audits and Core Web Vitals. Install Desiderio for free and get all three.',
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
    private static function typo3V14StrategyPage(): array
    {
        return [
            'title' => 'TYPO3 v14 agentic strategy',
            'navTitle' => 'TYPO3 v14 strategy',
            'slug' => '/typo3-v14-strategy',
            'abstract' => 'Results of webconsulting\'s long-running TYPO3 v14 analysis: a plan for an agentic CMS platform for editors, agencies and enterprises. It builds on TYPO3 project work since 2002.',
            'description' => 'Results of webconsulting\'s TYPO3 v14 analysis: a plan for an agentic CMS platform for editors, agencies and enterprises, based on TYPO3 work since 2002.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Lab results',
                    'header' => 'TYPO3 v14 agentic platform strategy',
                    'subheadline' => 'One CMS for editors, developers and autonomous agents. This TYPO3 v14 plan by webconsulting has 23 pillars, tested in a running lab and based on project work since 2002.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    'From research to a sellable platform',
                    'media-above',
                    'An operating model you can sell, not a trend slide.',
                    '<p>Everything here runs in the lab: Desiderio, the MCP server, Skillflow, Agentation, Agent Nexus, OpenTag Bridge, Flue, the Abilities registry, llms.txt and x402. Analysts now call the category the agentic DXP, and every major CMS vendor is moving towards it.</p><ul><li><strong>Running</strong>: agents inspect, write and review through governed tools, on real pages, records, files and translations.</li><li><strong>Concrete</strong>: 68 MCP tools (verified live), 18 of them projected from the abilities registry, plus A2UI, AG-UI, A2A, UCP and AP2.</li><li><strong>Sellable</strong>: each pillar maps to speed, control, lower delivery cost or safer automation.</li></ul>',
                    self::mcpStrategyImage('v14-00-analysis-results-source-803d947909f14720.png', 'Analysis results of the TYPO3 v14 strategy', 'A table of the TYPO3 v14 strategy analysis next to a modular platform architecture.')
                ),
                self::v14StrategyTextmedia(
                    'How to read this page',
                    'media-above',
                    'Three layers, 23 pillars and one argument: controlled automation.',
                    '<p>The strategy has three layers. Each pillar says what exists, what comes next and why a client pays for it.</p><ul><li><strong>Part I (pillars 1–12)</strong>: the platform. Make TYPO3 operable by agents.</li><li><strong>Part II (pillars 13–16)</strong>: the operating layer. Make that work manageable.</li><li><strong>Part III (pillars 17–23)</strong>: the agentic web. Open the installation outwards.</li></ul><p>Short on time: start with the eleven-question readiness check at the end.</p>'
                ),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Pillars 1–12',
                    'header' => 'Part I: make TYPO3 operable by agents',
                    'subheadline' => 'Everything an agent touches needs a contract: content, identity, tools, skills, models, money, commands, APIs and interfaces. Most of these twelve pillars already run in this lab.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '1. Dual-audience design',
                    'media-right',
                    'One source serves editors, APIs and agents.',
                    '<p>TYPO3 provides governed content, page trees, records, files, translations and workspaces. Desiderio adds typed, semantic Content Blocks for browsers, APIs, search and AI assistants.</p><ul><li><strong>Already here</strong>: backend previews and frontend markup come from one element definition.</li><li><strong>Detail</strong>: accessible, semantic markup helps people and machine extraction at once.</li><li><strong>Why it sells</strong>: one workflow feeds websites, headless views, AI search and copilots.</li></ul>',
                    self::mcpStrategyImage('v14-01-dual-audience-source-9dd58581c0bb5a84.png', 'Editor and agent views from one CMS source', 'An editor view and an agent view connected to one CMS source.')
                ),
                self::v14StrategyTextmedia(
                    '2. Identity management for agents',
                    'media-left',
                    'No shared admin logins. Every agent has an owner, a scope and a trail.',
                    '<p>Every automated action needs an identity. The lab uses real credentials for real agents.</p><ul><li><strong>Already here</strong>: backend permissions, workspace ownership, personal access tokens, OAuth with PKCE, WorkOS enterprise login and risk tiers per ability. Scoped, expiring API tokens belong to real backend users.</li><li><strong>Next</strong>: first-class agent users with expiry, delegation records and consent trails.</li><li><strong>Why it sells</strong>: a client approves a translation agent without handing over the backend.</li></ul>',
                    self::mcpStrategyImage('v14-02-agent-identity-source-c43c33de79ceada3.png', 'Agent identity with scopes and audit trails', 'An agent identity model with scoped permissions and audit trails.')
                ),
                self::v14StrategyTextmedia(
                    '3. MCP: the right tools, not every endpoint',
                    'media-right',
                    '68 registered MCP tools: useful enough to ship work, small enough to govern.',
                    '<p>The lab exposes narrow operations, not every endpoint: pages, content, records, files, workspaces, Solr, payments and diagnostics. MCP now lives at the Linux Foundation, and the 2026 spec adds Tasks for long-running work.</p><ul><li><strong>Already here</strong>: 50 hand-built tools, from <code>GetPageTree</code> to <code>x402_stats</code>, plus 18 projected from the abilities registry.</li><li><strong>Next</strong>: carry out the written 2026-spec adoption plan and list the server in the official MCP registry. Generate more tools from the capability registry (pillar 19).</li><li><strong>Why it sells</strong>: a controlled operating surface, not a chat box wired to production.</li></ul>',
                    self::mcpStrategyImage('v14-03-mcp-tools-source-7c8f3c4124bab359.png', 'A governed MCP toolbox connected to a CMS', 'A compact, governed MCP toolbox connected to a CMS core.')
                ),
                self::v14StrategyTextmedia(
                    '4. Agentic skills',
                    'media-left',
                    'Turn senior TYPO3 delivery work into reusable, versioned workflows.',
                    '<p>Prompts are not a process; skills are. They turn TYPO3 know-how into reusable instructions: create a landing page, review accessibility, prepare translations, check a launch.</p><ul><li><strong>Already here</strong>: Skillflow stores skills as TYPO3 records, syncs them from repositories and makes them searchable.</li><li><strong>Detail</strong>: skills attach to workspace stages, so review is part of the editorial pipeline.</li><li><strong>Why it sells</strong>: agencies package their delivery practice instead of relying on memory.</li></ul>',
                    self::mcpStrategyImage('v14-04-agentic-skills-source-e4920a8a296a4350.png', 'Skill library for repeatable TYPO3 workflows', 'A library of reusable agent skills for repeatable TYPO3 workflows.')
                ),
                self::v14StrategyTextmedia(
                    '5. LLM-agnostic libraries',
                    'media-right',
                    'Use the right model for each job without rewiring the CMS.',
                    '<p>Models change; the CMS integration should not. One shared layer handles generation, embeddings, tool use, cost metadata and provider configuration.</p><ul><li><strong>Already here</strong>: Netresearch\'s <strong>nr_llm</strong> as the shared LLM foundation, and <strong>nr_vault</strong> for credentials under envelope encryption with audit logs.</li><li><strong>Detail</strong>: premium models for high-value content, cheaper or local ones for bulk and sensitive work, set per task.</li><li><strong>Why it sells</strong>: procurement keeps control of price, data residency and compliance.</li></ul>',
                    self::mcpStrategyImage('v14-05-llm-agnostic-source-8ce0578695dbc362.png', 'One AI adapter for several model engines', 'An LLM-agnostic adapter that connects TYPO3 to several model engines.')
                ),
                self::v14StrategyTextmedia(
                    '6. Token billing and usage economics',
                    'media-left',
                    'AI becomes a product when usage is visible, priced and capped.',
                    '<p>AI features have variable costs, and machine readers are becoming revenue. The lab measures costly operations and supports several payment rails. x402 leads in production; AP2, Stripe\'s agentic sessions and retail checkout protocols compete.</p><ul><li><strong>Already here</strong>: x402 page and API gating, payment statistics, AP2 mandate demos in Agent Nexus and paid-content MCP tools.</li><li><strong>Next</strong>: token metering per user, model, task and customer, and charging AI crawlers, as Cloudflare\'s pay-per-crawl is making normal.</li><li><strong>Why it sells</strong>: AI-assisted translation, QA and migration get a price instead of a surprise invoice.</li></ul>',
                    self::mcpStrategyImage('v14-06-token-billing-source-ba738535fee79b21.png', 'Token meters with budget controls', 'AI token billing meters with budget controls and usage flows.')
                ),
                self::v14StrategyTextmedia(
                    '7. CLI: deterministic and agent-friendly',
                    'media-above',
                    'Agents trust commands that validate input and return stable output.',
                    '<p>The lab runs seeding, diagnostics, indexing and MCP inspection through the CLI, with JSON output, clear exit codes and idempotent operations.</p><ul><li><strong>Already here</strong>: commands for styleguide seeding, MCP tool inspection, Solr indexing, Skillflow sync and health checks.</li><li><strong>Next</strong>: make the CLI an official automation surface for content, schema, cache and workspace operations.</li><li><strong>Why it sells</strong>: a command you can repeat, log and roll back is a product feature.</li></ul>',
                    self::mcpStrategyImage('v14-07-deterministic-cli-source-24312cffc0f95704.png', 'A command console for TYPO3 automation', 'A deterministic command console for TYPO3 automation and agent operations.')
                ),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'Example: CLI output an agent can trust',
                    'code' => "# Describe the installation as structured JSON\n$ typo3 describe --format-json\n{\n  \"site\": \"example\",\n  \"typo3_version\": \"14.3.2\",\n  \"extensions\": [ ... ],\n  \"sites\": [ ... ],\n  \"languages\": [ ... ],\n  \"workspaces\": [ ... ]\n}\n\n# Agent-ready operating commands\n$ typo3 schema:export      # full TCA and Content Blocks schema\n$ typo3 content:apply      # idempotent content changes from a file\n$ typo3 site:diagnose      # health and misconfiguration report\n$ typo3 workspace:preview  # shareable preview of staged changes",
                    'language' => 'bash',
                    'filename' => 'typo3-agent-ops.sh',
                ]),
                self::v14StrategyTextmedia(
                    '8. Backend: headless by design',
                    'media-right',
                    'Editors keep the UI. Agents get the same backend as typed operations.',
                    '<p>Editors keep the backend. The v14 shift happens underneath: pages, content, files, forms and workspaces become typed operations instead of screen scraping.</p><ul><li><strong>Already here</strong>: Content Blocks, backend previews, Visual Editor support, workspaces and command-based extension surfaces.</li><li><strong>Detail</strong>: Agentation points at a page element and passes selector-level feedback to an AI workflow.</li><li><strong>Why it sells</strong>: human approval and programmable delivery share one content model.</li></ul>',
                    self::mcpStrategyImage('v14-08-headless-backend-source-d0146a4caa5464a8.png', 'A headless TYPO3 backend with API lanes', 'A programmable headless TYPO3 backend with structured API lanes.')
                ),
                self::v14StrategyTextmedia(
                    '9. APIs like tRPC, built for PHP realities',
                    'media-left',
                    'One typed procedure serves PHP, REST, MCP and external clients.',
                    '<p>Define an operation once in the abilities registry, with name, typed contract, scopes, risk tier and side effects. Every surface is a projection of it. A compiler pass generates the MCP tools, the CLI runs the same pipeline, and sg_apicore serves REST with backend-user-bound tokens.</p><ul><li><strong>Already here</strong>: the registry, a governed executor, an execution policy with human-in-the-loop review, per-attempt traces, and MCP, CLI, REST and desktop projections.</li><li><strong>Detail</strong>: one registration powers an MCP agent and the desktop editor\'s publish button.</li><li><strong>Why it sells</strong>: no duplicate controllers, inconsistent integrations or hidden logic.</li></ul><p>WordPress proved this architecture with its Abilities API; pillar 19 adds governance.</p>',
                    self::mcpStrategyImage('v14-09-typed-php-api-source-d6f9236e51205bc3.png', 'PHP contracts for web, mobile and agents', 'Typed PHP procedure contracts connected to web, mobile and agent clients.')
                ),
                self::v14StrategyTextmedia(
                    '10. Simple interfaces for AI work',
                    'media-right',
                    'Point at the problem, capture the context, and let the agent work precisely.',
                    '<p>For CMS work, a note on the page often works better than a chat field: this headline is weak, this legal block must not change.</p><ul><li><strong>Already here</strong>: Agentation captures element feedback with selector, DOM context, styles and intent for Claude Code, Cursor or MCP agents.</li><li><strong>Next</strong>: connect the annotation, the agent run and a workspace diff you can review.</li><li><strong>Why it sells</strong>: editors stay on the page, and agents get exactly the context they need.</li></ul>',
                    self::mcpStrategyImage('v14-10-ai-feedback-source-a886d8e44543124f.png', 'Annotations on a web page preview', 'An in-context AI feedback interface with annotations on a web page preview.')
                ),
                self::v14StrategyTextmedia(
                    '11. MCP-based chatbot and editorial assistant',
                    'media-above',
                    'Chat is useful when it calls the same governed tools as everything else.',
                    '<p>The lab runs <strong>nr_mcp_agent</strong>, an AI chat assistant in the TYPO3 backend. It uses the same MCP toolbox as every other agent.</p><ul><li><strong>Already here</strong>: it reads pages, imports content, writes records, attaches media and reviews workspaces.</li><li><strong>Detail</strong>: permissions and preview workflows still apply. Chat has no back door.</li><li><strong>Why it sells</strong>: chat is fast, and governance stays in TYPO3.</li></ul>',
                    self::mcpStrategyImage('v14-11-mcp-chatbot-source-4b1f82b53165e55e.png', 'Editorial assistant using governed MCP tools', 'An MCP-based editorial assistant connected to governed CMS tools.')
                ),
                self::v14StrategyTextmedia(
                    '12. AI-optimised codebase',
                    'media-left',
                    'Code that agents can understand is code teams can maintain.',
                    '<p>Desiderio shows the discipline: typed Fluid components, focused data classes, tests, predictable templates and design tokens.</p><ul><li><strong>Already here</strong>: typed template arguments, reusable components, unit tests, accessibility checks and deterministic seed data.</li><li><strong>Detail</strong>: agents pick the right element from the structured library instead of guessing from screenshots.</li><li><strong>Why it sells</strong>: clean architecture makes upgrades, automation and new features cheaper.</li></ul>',
                    self::mcpStrategyImage('v14-12-ai-codebase-source-5f36db22835849de.png', 'A TYPO3 codebase as maintainable modules', 'An AI-optimised TYPO3 codebase shown as maintainable architecture modules.')
                ),
                self::v14StrategyTextmedia(
                    'The TYPO3 AI stack running in this lab',
                    'media-right',
                    'These parts are installed and visible in the lab.',
                    '<p>The foundation: Desiderio, MCP tools, sg_apicore, Skillflow, Agentation, x402, search, forms, enterprise auth and workspace review. The AI layer uses Netresearch\'s nr_llm, nr_mcp_agent, nr_vault and t3_cowriter. Newer parts add protocols and channels:</p><ul><li><strong>Agent Nexus</strong>: five agent protocols, live.</li><li><strong>OpenTag Bridge</strong>: Slack control with approvals and a ledger.</li><li><strong>Flue</strong>: a durable runtime.</li><li><strong>Abilities</strong>: a typed capability registry with four live projections.</li><li><strong>llms.txt</strong>: machine-readable site surfaces.</li></ul><p>Everything uses TYPO3 concepts: pages, records, Content Blocks, FAL, site settings and workspaces.</p>',
                    self::mcpStrategyImage('v14-13-installed-stack-source-d2f467651a18dbc9.png', 'The installed TYPO3 AI lab stack', 'The installed TYPO3 AI lab stack with LLM, MCP, vault and monitoring modules.')
                ),
                self::v14StrategyTextmedia(
                    'Built on Netresearch\'s AI foundation',
                    'media-above',
                    'Four open-source extensions by Netresearch carry the AI layer.',
                    '<p>The AI layer runs on four open-source extensions by Netresearch DTT GmbH. <strong>nr_llm</strong> is the shared LLM foundation, and <strong>nr_mcp_agent</strong> the backend assistant. <strong>nr_vault</strong> stores encrypted secrets, and <strong>t3_cowriter</strong> is an AI writing partner for editors. Thank you.</p>'
                ),
                self::v14StrategyTextmedia(
                    'The full MCP toolbox',
                    'media-above',
                    '68 registered tools show that the operating surface is real.',
                    '<p>68 MCP tools: 50 hand-built and 18 projected from the abilities registry. They cover pages, content, records, files, schemas, imports, site settings, workspaces, logs, Solr, paid content and payments.</p><ul><li><strong>Content lane</strong>: <code>GetPage</code>, <code>GetPageTree</code>, <code>ImportContent</code>, <code>BulkWrite</code>, <code>AttachImage</code> and <code>RenderRecord</code>.</li><li><strong>Operations lane</strong>: <code>WorkspaceReview</code>, <code>PublishWorkspace</code>, <code>RollbackWorkspace</code>, <code>GetSystemLog</code>, <code>SafeCli</code> and <code>SolrIndexQueue</code>.</li><li><strong>Commercial lane</strong>: <code>GetPaidContent</code>, <code>GetPaymentStats</code>, <code>x402_stats</code>, <code>x402_transactions</code> and related probes.</li></ul><p>The point is a named toolbox you can inspect, restrict and teach.</p>',
                    self::mcpStrategyImage('v14-14-full-toolbox-source-ec3c4504e7475b2a.png', 'The MCP toolbox for TYPO3 content operations', 'A matrix of the complete MCP toolbox for structured TYPO3 content operations.')
                ),
                self::v14StrategyTextmedia(
                    'How the MCP stays secure',
                    'media-above',
                    'Tools that write data need deliberate limits.',
                    '<p>The MCP surface writes records, uploads files and touches caches, so it is treated as an operations interface.</p><ul><li><strong>Already here</strong>: OAuth with PKCE, personal access tokens, runtime-enforced ability policies with risk tiers, and file tools limited to FAL.</li><li><strong>Detail</strong>: read, write, publish and payment tools have their own scopes and review rules per environment.</li><li><strong>Why it sells</strong>: webconsulting sets which tools run in each environment.</li></ul>'
                ),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Pillars 13–16',
                    'header' => 'Part II: make agent work manageable',
                    'subheadline' => 'Once agents can work, customers ask how that work is observed, governed, resumed and reviewed. Each of these four pillars has first parts running in the lab.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '13. AgentOps: traces, evals and rollback',
                    'media-right',
                    'Autonomy needs traces, evals, cost visibility and rollback.',
                    '<p>A production CMS cannot trust an agent after one good demo. Every run needs evidence: context, tool, input, output, changed records, cost, reviewer and rollback path.</p><ul><li><strong>Already here</strong>: OpenTag\'s audit ledger for channel runs and Skillflow run reports with verdict and score. The abilities registry traces every attempt on every surface, including denials.</li><li><strong>Next</strong>: one trace store with eval sets and regression checks across all agents.</li><li><strong>Why it sells</strong>: proof of where automation saves time and what is safe to scale.</li></ul>',
                    self::mcpStrategyImage('14-agentops-source-dd06b2ff43dcd398.png', 'AgentOps control room', 'An agent operations control room with evaluation checkpoints, traces, cost meters and approval signals.')
                ),
                self::v14StrategyTextmedia(
                    '14. Context fabric: trusted knowledge',
                    'media-right',
                    'TYPO3 already holds the knowledge agents need. Package it safely.',
                    '<p>Page trees, records, TCA schemas, file metadata, redirects, access rules and editorial history feed a permission-aware context fabric. This pillar has the least code so far; TYPO3\'s SEAL search abstraction is the natural vehicle.</p><ul><li><strong>Already here</strong>: structured records, FAL metadata, Solr indexing, table schemas and workspace overlays.</li><li><strong>Next</strong>: a semantic index that checks permissions at query time and returns context with sources.</li><li><strong>Why it sells</strong>: knowledge in older TYPO3 sites becomes reusable AI infrastructure.</li></ul>',
                    self::mcpStrategyImage('15-context-fabric-source-3e3720be00f77da1.png', 'Context fabric knowledge graph', 'A permission-aware context fabric connecting CMS pages, records, files, vector search, provenance and access controls.')
                ),
                self::v14StrategyTextmedia(
                    '15. Governance: policy, consent and human review',
                    'media-right',
                    'Let agents move fast inside explicit boundaries.',
                    '<p>Identity says who may act. Governance says when the system acts alone, when it asks, and what proof publication needs.</p><ul><li><strong>Already here</strong>: workspace review, page permissions, roles, history and OpenTag\'s policy gate with human approval. Abilities have deny and review rules, proven live on a high-risk workspace publish.</li><li><strong>Next</strong>: policies as governed TYPO3 records with risk tiers, not configuration files.</li><li><strong>Why it sells</strong>: clear boundaries let an installation automate more, not less.</li></ul>',
                    self::mcpStrategyImage('16-governance-source-04c1d1026572f360.png', 'Governance approval gates', 'A human governance workflow with risk tiers, approval gates, policy cards, consent checkpoints and audit trail controls.')
                ),
                self::v14StrategyTextmedia(
                    '16. Durable runtime for long-running jobs',
                    'media-right',
                    'Real agent work needs state, queues, retries, approvals and handoffs.',
                    '<p>Agent tasks import, enrich, wait for approval, retry and resume. That needs a durable runtime.</p><ul><li><strong>Already here</strong>: CLI commands, Scheduler patterns and workspace previews. The Flue bridge runs a real durable runtime with TYPO3 as control plane and mirrors runs into the backend.</li><li><strong>Next</strong>: each long job shows owner, state, affected records, retry policy, cost and next action, in line with the 2026 MCP Tasks standard.</li><li><strong>Why it sells</strong>: migrations, launches, localisation and bulk QA become managed workflows.</li></ul>',
                    self::mcpStrategyImage('17-durable-runtime-source-620da710d6dd8b1f.png', 'Durable agent runtime', 'A durable agent runtime with queues, retries, workflow lanes, a state hub and handoff stations for autonomous TYPO3 jobs.')
                ),
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Pillars 17–23',
                    'header' => 'Part III: TYPO3 on the agentic web',
                    'subheadline' => 'Agents now visit, delegate, buy and cite, not only read. The last seven pillars make a TYPO3 installation multi-protocol, discoverable, monetisable, provable, sovereign and standardised.',
                    'variant' => 'center',
                ]),
                self::v14StrategyTextmedia(
                    '17. Agent protocols beyond MCP',
                    'media-right',
                    'MCP connects tools. The agentic web also needs UI, delegation and commerce lanes.',
                    '<p>Browser agents went mainstream in 2026: software visits sites to ask, delegate and buy. Beyond MCP\'s tools, that takes A2UI (agent to UI), AG-UI (agent to user, with approval gates) and A2A (agent to agent). UCP connects agents to merchants, and AP2 carries signed payment mandates.</p><ul><li><strong>Already here</strong>: Agent Nexus demos all five live: a backend field guide, five playgrounds, five frontend plugins and human gates before every write and payment.</li><li><strong>Detail</strong>: each run is labelled live (real model answer) or scripted (deterministic fallback).</li><li><strong>Why it sells</strong>: TYPO3 can receive agents with inquiries, delegated tasks and carts, not only host content they scrape.</li></ul>',
                    self::mcpStrategyImage('v14-17-agent-protocols-source-1daaafd8039b3b94.png', 'Five protocol lanes meeting at one CMS hub', 'Five agent protocol lanes converging on one governed CMS hub.')
                ),
                self::v14StrategyTextmedia(
                    '18. Channel operations: TYPO3 from Slack',
                    'media-right',
                    'Editors delegate work from the tools they already use, with an approve tap.',
                    '<p>"Draft a teaser about the summer opening on page 12": typed in Slack, drafted by TYPO3, published only after an explicit approve tap. OpenTag Bridge keeps the LLM, permissions, budgets and audit ledger inside TYPO3.</p><ul><li><strong>Already here</strong>: every action passes token guard, rate limiter, mapping to real backend users, policy gate, human approval and ledger entry.</li><li><strong>Next</strong>: connectors for Discord, Telegram and Teams. The agent backend stays TYPO3, not the messenger.</li><li><strong>Why it sells</strong>: your model, permissions and audit log, not a per-seat cloud bot.</li></ul>',
                    self::mcpStrategyImage('v14-18-channel-operations-source-08ea54cb6f898602.png', 'Steering a CMS from chat, with approval', 'A chat channel that steers a CMS through an approval gate.')
                ),
                self::v14StrategyTextmedia(
                    '19. One capability registry for all surfaces',
                    'media-right',
                    'One typed registry of what the CMS can do, live in this lab on four surfaces.',
                    '<p>WordPress proved the 2026 lesson: one typed, permissioned registry of capabilities, projected into every protocol. Here, 18 abilities declare name, contract, scopes, risk tier and side effects once, and every call runs through one governed pipeline. The earlier capability manifests were retired into it.</p><ul><li><strong>Already here</strong>: four projections verified live: CLI commands, 18 generated MCP tools, a REST endpoint on scoped backend-user tokens and the desktop editor.</li><li><strong>Detail</strong>: policy denies a high-risk workspace publish without explicit approval, and every attempt lands in the trace table.</li><li><strong>Why it sells</strong>: one integration instead of five, and one registry to audit.</li></ul><p>Next: policy records in TCA, the 2026 MCP-spec projection and a proposal to the TYPO3 AI initiative (pillar 23).</p>',
                    self::mcpStrategyImage('v14-19-capability-registry-source-d683bece12f4939b.png', 'Capability registry with four surfaces', 'A central capability registry that projects into four protocol surfaces.')
                ),
                self::v14StrategyTextmedia(
                    '20. The machine-readable, monetisable site',
                    'media-right',
                    'The zero-click web is measured reality. This lab publishes for machines by default.',
                    '<p>AI answers cut outbound clicks by roughly 40%, and infrastructure providers now meter AI crawlers. The answer is to publish deliberately, with a price where content has value.</p><ul><li><strong>Already here</strong>: schema.org JSON-LD, question-shaped elements, x402 gating, and llms.txt and agents.md generated per site from the page tree.</li><li><strong>Detail</strong>: only visible, indexable pages are listed. agents.md names the MCP endpoint, abilities registry and payment lane, detected at runtime.</li><li><strong>Why it sells</strong>: lost clicks become licensed machine access.</li></ul>',
                    self::mcpStrategyImage('v14-20-machine-readable-site-source-74c606b3b658cdb7.png', 'Human and machine views with a crawler gate', 'A website with human and machine views and a metered gate for crawlers.')
                ),
                self::v14StrategyTextmedia(
                    '21. Trust, provenance and AI-Act compliance',
                    'media-right',
                    'From August 2026, AI transparency is EU law, and the CMS is where it gets done.',
                    '<p>Article 50 of the EU AI Act applies from August 2026: disclose AI interaction and mark generated output machine-readably. Penalties run into the millions. TYPO3\'s file and record architecture is the natural place for these duties.</p><ul><li><strong>Already here</strong>: forensics that check incoming media for manipulation, and the agent audit trail (pillar 13) as the compliance log.</li><li><strong>Next</strong>: C2PA Content Credentials read and kept in FAL metadata, plus AI disclosure and marking in the AI pipelines.</li><li><strong>Why it sells</strong>: provenance audits, disclosure reviews and accessibility checks as one recurring service.</li></ul>',
                    self::mcpStrategyImage('v14-21-provenance-compliance-source-7e28a10889754a33.png', 'Media assets with provenance seals', 'Media assets with provenance seals, inspected by a robot.')
                ),
                self::v14StrategyTextmedia(
                    '22. European sovereignty as a product',
                    'media-right',
                    'Your models, your audit log, your data: a position US SaaS cannot copy.',
                    '<p>The biggest independent headless CMS now belongs to a US hyperscaler\'s agent platform. The EU Data Act pushes switchability and protection from non-EU access. Where the agentic CMS runs is now procurement policy.</p><ul><li><strong>Already here</strong>: the whole stack self-hosts: TYPO3, MCP server, agent bridges, audit ledger and a swappable LLM layer. A reference architecture defines three trust tiers: own inference, EU APIs, opted-in non-EU models.</li><li><strong>Detail</strong>: premium models for high-value content, EU-hosted or local ones for sensitive work.</li><li><strong>Why it sells</strong>: it wins deals where US SaaS is disqualified before the demo.</li></ul>',
                    self::mcpStrategyImage('v14-22-eu-sovereignty-source-fd9c6345c397f780.png', 'Sovereign EU data infrastructure', 'Sovereign EU data infrastructure under a protective dome.')
                ),
                self::v14StrategyTextmedia(
                    '23. Standardise with the TYPO3 AI initiative',
                    'media-right',
                    'A head start counts for more as shared leadership than as a private fork.',
                    '<p>Drupal formalised its AI push with dozens of backing organisations. TYPO3\'s initiative is younger and still at the interface stage, so patterns contributed now can become the standard.</p><ul><li><strong>Already here</strong>: reference implementations of the MCP server, the abilities registry, workspace-staged agent writes and the skills format.</li><li><strong>Next</strong>: TER releases, work in the initiative and one public reference write-up per quarter.</li><li><strong>Why it sells</strong>: clients hire the agency that wrote the standard.</li></ul>',
                    self::mcpStrategyImage('v14-23-standardize-upstream-source-eff9bd20130a7f66.png', 'Lab patterns flowing upstream', 'Patterns from the lab flowing upstream into a shared community platform.')
                ),
                self::v14StrategyTextmedia(
                    'Readiness check for TYPO3 v14+',
                    'media-right',
                    'Eleven questions that show where an installation stands.',
                    '<p>First, the operating layer:</p><ul><li>Which agent changed which record, with which permission, and why?</li><li>Can a failed operation be rolled back or replayed safely?</li><li>Is the agent\'s context current, scoped and backed by sources?</li><li>Can people review high-risk changes before publication?</li><li>Can you price, cap and report AI costs?</li><li>Can long-running work pause, resume and hand off?</li></ul><p>Then the agentic web:</p><ul><li>Does the site disclose AI interaction and mark generated output, as Article 50 requires?</li><li>Can you prove media provenance and detect manipulation?</li><li>Can external agents discover what the site offers, and can you charge them for access?</li><li>Which agent protocols beyond MCP does the installation speak?</li><li>Can the whole stack run on EU-sovereign infrastructure with exchangeable models?</li></ul><p>TYPO3 has the building blocks: records, roles, workspaces, files, logs, scheduler patterns and extension APIs.</p>'
                ),
                self::v14StrategyTextmedia(
                    'Conclusion: the platform webconsulting would sell',
                    'media-above',
                    'TYPO3 can become the governed operating system for content and agents.',
                    '<p>This is a governed operating layer, not a CMS with a few AI buttons. Editors, APIs and agents share one content model, with clear permissions, structured tools, workspace review and measurable economics.</p><ul><li><strong>For agencies</strong>: strategy, implementation, migration, compliance and managed AI operations become billable packages.</li><li><strong>For enterprises</strong>: automation with governance, accountability and European sovereignty.</li><li><strong>For TYPO3</strong>: it competes on its strengths and standardises what this lab proves.</li></ul><p>The time to build this is before the next LTS.</p>',
                    self::mcpStrategyImage('v14-15-conclusion-source-6178b41759412e71.png', 'The TYPO3 v14 agentic operating model', 'The final TYPO3 v14 agentic platform operating model, presented as analysis results.')
                ),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Plan your TYPO3 v14 platform with webconsulting',
                    'cta_text' => 'Book a call',
                    'cta_link' => '{{page:desiderio-powermail/callback}}',
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
