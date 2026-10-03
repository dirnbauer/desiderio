<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Editorial deep dives: the GEO / AI-search explainer and the TYPO3 v14
 * strategy for AI agents.
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
            'parentSlug' => 'ai',
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
                            'content' => '<p><strong>AI Overviews change often.</strong> Which queries show one changes all the time, and analyses such as Lily Ray’s show large swings within weeks.</p><ul><li><strong>Build for the long term</strong>: invest in pages that are easy to extract, not in single snapshots.</li></ul>',
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
     * The TYPO3 v14 strategy in plain language: a summary first, then one
     * section per question a CTO or agency lead asks (why now, what is the
     * plan, what runs today, what comes next, where do we stand, how to
     * start). The long-form source is docs/typo3-agentic-strategy-2026.md.
     *
     * @return ShowcasePage
     */
    private static function typo3V14StrategyPage(): array
    {
        return [
            'title' => 'TYPO3 v14 and AI agents: our strategy',
            'navTitle' => 'TYPO3 v14 strategy',
            'slug' => '/typo3-v14-strategy',
            'abstract' => 'A plan by webconsulting for TYPO3 v14 as a CMS that AI agents can use safely. It shows what runs today, what comes next and how to start.',
            'description' => 'The TYPO3 v14 strategy for AI agents by webconsulting: what runs in the lab today, the roadmap to 2030, a readiness check and first steps.',
            'parentSlug' => 'ai',
            'content' => [
                // ------------------------------------------------ the answer first
                ShowcaseBlocks::block('desiderio_herostats', [
                    'eyebrow' => 'Strategy 2026–2030',
                    'header' => 'AI agents that work within TYPO3 permissions',
                    'subheadline' => 'This is webconsulting’s plan for TYPO3 v14 as a platform that agents can use safely. Large parts of it already run in this lab.',
                    'primary_button_text' => 'Book a call',
                    'primary_button_link' => '{{page:desiderio-powermail/callback}}',
                    'stats' => [
                        ['value' => '5', 'label' => 'Agent protocols running', 'stat_description' => 'A2UI, AG-UI, A2A, UCP and AP2, live in Agent Nexus.'],
                        ['value' => '0', 'label' => 'Shared admin logins', 'stat_description' => 'Every agent acts as a real backend user with its own scope.'],
                        ['value' => '11', 'label' => 'Readiness questions', 'stat_description' => 'They show where your installation stands today.'],
                        ['value' => '2029', 'label' => 'End of TYPO3 v14 support', 'stat_description' => 'The LTS release from April 2026 is supported until then.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featurechecklist', [
                    'eyebrow' => 'Summary',
                    'header' => 'The strategy in five points',
                    'items' => [
                        ['title' => 'Agents use governed tools', 'description_text' => 'They work through MCP tools, CLI commands and APIs, never through a shared admin login.'],
                        ['title' => 'People approve what goes live', 'description_text' => 'Workspaces, permissions and approval steps apply to agents as they do to editors.'],
                        ['title' => 'One registry defines each action', 'description_text' => 'An ability is described once, with its scopes and risk level, and every surface uses it.'],
                        ['title' => 'You choose the AI model', 'description_text' => 'The model layer connects many providers, so price, location and data policy stay your decision.'],
                        ['title' => 'Governance is what clients buy', 'description_text' => 'Buyers pay for audit trails, approvals and EU hosting, not for AI decoration.'],
                    ],
                ]),

                // ------------------------------------------------------ why now
                self::v14StrategyTextmedia(
                    'Why TYPO3 needs a plan for agents now',
                    'media-right',
                    'Software agents now read, edit and buy on websites. Tenders for a CMS ask how agents can use it.',
                    '<p>Four changes make this urgent.</p><ul><li><strong>TYPO3 v14 is the new LTS release.</strong> It came out in April 2026 and sets the base for the next three years.</li><li><strong>MCP is the standard for agent tools.</strong> The Model Context Protocol now belongs to the Linux Foundation.</li><li><strong>Other CMSs moved first.</strong> WordPress added a typed abilities API, and Drupal runs an AI initiative backed by many organisations.</li><li><strong>The EU AI Act applies.</strong> Since 2 August 2026, sites must disclose AI interaction and mark generated content.</li></ul>',
                    self::strategyImage('strategy-why-now-e77b0626.webp', 'Page proofs on an editorial desk', 'Printed page layouts, a pencil and a closed laptop on a desk, standing for web work that is about to change.')
                ),

                // ------------------------------------------------------ the plan
                ShowcaseBlocks::block('desiderio_featurealternating', [
                    'header' => 'The plan has three layers',
                    'subheadline' => 'Each layer builds on the one before. Agents get safe tools first, then their work is controlled, then the site opens to outside agents.',
                    'items' => [
                        [
                            'title' => '1 · Give agents safe tools',
                            'description' => '<p>Agents need clear tools, not a chat box wired to production.</p><ul><li><strong>Tools</strong>: an MCP server for pages, content, files, workspaces and search, plus a typed abilities registry.</li><li><strong>Access</strong>: OAuth, personal access tokens and WorkOS login, each bound to a real backend user.</li><li><strong>Skills</strong>: Skillflow stores tested workflows as TYPO3 records.</li></ul>',
                            'image' => self::strategyImage('strategy-layer-tools-31203f33.webp', 'Tools in their outlines on a pegboard', 'Hand tools hanging in painted outlines on a pegboard, like a toolbox where every tool has its place.'),
                        ],
                        [
                            'title' => '2 · Keep agent work under control',
                            'description' => '<p>Every agent action needs an owner, a record and a way back.</p><ul><li><strong>Running</strong>: workspace review, a policy gate that asks a person before risky writes, and a trace of every ability call.</li><li><strong>Next</strong>: one trace store for all agents, with costs, changes and rollback.</li><li><strong>Next</strong>: policies stored as TYPO3 records instead of configuration files.</li></ul>',
                            'image' => self::strategyImage('strategy-layer-review-8b07a8c0.webp', 'Page proofs under review', 'Hands ticking printed page proofs next to a wooden stamp, standing for human review before anything goes live.'),
                        ],
                        [
                            'title' => '3 · Open the site to outside agents',
                            'description' => '<p>Agents now visit sites to ask, delegate and buy. Your TYPO3 site can answer them.</p><ul><li><strong>Running</strong>: llms.txt and agents.md per site, schema.org data, paid access with x402 and five agent protocols in Agent Nexus.</li><li><strong>Next</strong>: content credentials (C2PA) for media and labels for AI output, as the EU AI Act requires.</li></ul>',
                            'image' => self::strategyImage('strategy-layer-open-web-5a95aec9.webp', 'An open reception counter', 'An open reception counter with a service bell and a card terminal, standing for a site that receives outside agents.'),
                        ],
                    ],
                ]),

                // ------------------------------------------------ what runs today
                ShowcaseBlocks::block('desiderio_comparisontable', [
                    'header' => 'Where each part stands today',
                    'description' => '<p>This lab is a working TYPO3 v14 installation. The first column shows what runs there now.</p>',
                    'column_items' => [
                        ['name' => 'In the lab now', 'highlighted' => 1],
                        ['name' => 'Next step', 'highlighted' => 0],
                    ],
                    'comparison_feature_items' => [
                        ['name' => 'Agent access', 'tier_values' => [['value' => 'OAuth, access tokens and WorkOS login'], ['value' => 'Agent users with delegation records']]],
                        ['name' => 'Tools for agents', 'tier_values' => [['value' => 'MCP server and abilities registry'], ['value' => 'Listing in the official MCP registry']]],
                        ['name' => 'Reusable workflows', 'tier_values' => [['value' => 'Skillflow skills as TYPO3 records'], ['value' => 'Test sets built on run results']]],
                        ['name' => 'AI models and keys', 'tier_values' => [['value' => 'nr_llm and nr_vault by Netresearch'], ['value' => 'EU and local model profiles']]],
                        ['name' => 'Review before publishing', 'tier_values' => [['value' => 'Workspaces and a human approval gate'], ['value' => 'Policies stored as TYPO3 records']]],
                        ['name' => 'Traces and costs', 'tier_values' => [['value' => 'Every ability call is logged'], ['value' => 'One trace store with costs and rollback']]],
                        ['name' => 'Knowledge for agents', 'tier_values' => [['value' => 'Solr search and typed records'], ['value' => 'Semantic search that respects permissions']]],
                        ['name' => 'Site for machines', 'tier_values' => [['value' => 'llms.txt, agents.md, JSON-LD and x402'], ['value' => 'Paid access tiers for AI crawlers']]],
                        ['name' => 'Agent protocols', 'tier_values' => [['value' => 'Five protocols live in Agent Nexus'], ['value' => 'Two of them ready for production']]],
                        ['name' => 'Media provenance', 'tier_values' => [['value' => 'Not in the lab yet'], ['value' => 'C2PA credentials in file metadata']]],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_callout', [
                    'header' => 'What is not built yet',
                    'content' => '<p>Three parts are still plans: semantic search that respects permissions, one trace store for all agents, and C2PA credentials for media. We show them as next steps, not as features.</p>',
                    'variant' => 'note',
                ]),

                // ------------------------------------------------- what comes next
                ShowcaseBlocks::block('desiderio_timeline', [
                    'header' => 'The roadmap to 2030',
                    'subheadline' => 'The dates come from TYPO3 and the EU. The work items are ours.',
                    'items' => [
                        ['step' => 'Since August 2026', 'title' => 'AI Act transparency rules apply', 'content' => '<p>Sites must disclose AI interaction and mark generated content. We add labels and marking to the AI workflows.</p>'],
                        ['step' => 'Late 2026', 'title' => 'One trace store and policy records', 'content' => '<p>All agent runs land in one log with costs and rollback paths. Policies move from configuration files into TYPO3 records.</p>'],
                        ['step' => '2027', 'title' => 'Long jobs and a proposal to TYPO3', 'content' => '<p>Long agent jobs follow the MCP Tasks standard. We propose the abilities registry to the TYPO3 AI initiative before the next LTS release.</p>'],
                        ['step' => 'December 2027', 'title' => 'High-risk AI rules take effect', 'content' => '<p>The high-risk duties of the EU AI Act begin. Some clients then need the governance layer by law.</p>'],
                        ['step' => '2028–2030', 'title' => 'Many sites move to TYPO3 v14', 'content' => '<p>TYPO3 v14 is supported until 2029, so many older sites move in these years. Each relaunch can include the agent layer from the start.</p>'],
                    ],
                ]),

                // --------------------------------------------- why self-hosted
                self::v14StrategyTextmedia(
                    'Run the whole stack on EU infrastructure',
                    'media-left',
                    'Many European clients can’t move content work to US cloud services. A self-hosted TYPO3 stack solves that.',
                    '<ul><li><strong>Self-hosted</strong>: TYPO3, the MCP server, the agent tools and the audit log run on your servers.</li><li><strong>Model choice</strong>: use your own models, EU-hosted APIs or, if you opt in, models outside the EU.</li><li><strong>Regulation</strong>: the EU Data Act asks for providers you can switch and protection from non-EU access.</li></ul>',
                    self::strategyImage('strategy-own-infrastructure-b72491fc.webp', 'A server cabinet in a bright office', 'A small server cabinet with its door open in a bright office, standing for infrastructure you run yourself.')
                ),

                // ----------------------------------------------- where do we stand
                ShowcaseBlocks::block('desiderio_featurenumbered', [
                    'header' => 'Readiness check: 11 questions',
                    'subheadline' => 'If you can’t answer a question clearly, you have found a gap to plan for.',
                    'items' => [
                        ['title' => 'Which agent changed which record, and why?', 'description' => '<p>You need a trace with the agent, its permission and the reason.</p>'],
                        ['title' => 'Can you undo or replay a failed run?', 'description' => '<p>Rollback and safe retries keep automation from becoming a risk.</p>'],
                        ['title' => 'Is the agent’s context current and sourced?', 'description' => '<p>Agents should work from scoped, current records with sources.</p>'],
                        ['title' => 'Do people review risky changes first?', 'description' => '<p>High-risk writes should wait for a human decision.</p>'],
                        ['title' => 'Can you price and cap AI costs?', 'description' => '<p>Visible costs per task let you sell AI work at a fixed price.</p>'],
                        ['title' => 'Can long jobs pause and resume?', 'description' => '<p>Imports, translations and launches must survive waits and handovers.</p>'],
                        ['title' => 'Do you disclose and mark AI output?', 'description' => '<p>Article 50 of the EU AI Act requires both.</p>'],
                        ['title' => 'Can you prove where media comes from?', 'description' => '<p>Content credentials and checks for manipulated media build trust.</p>'],
                        ['title' => 'Can outside agents see what you offer?', 'description' => '<p>llms.txt, agents.md and structured data tell them, and you can charge for access.</p>'],
                        ['title' => 'Which protocols besides MCP do you support?', 'description' => '<p>A2UI, AG-UI, A2A, UCP and AP2 cover interfaces, delegation and commerce.</p>'],
                        ['title' => 'Can the stack run on EU infrastructure?', 'description' => '<p>That includes models you can swap without rebuilding the site.</p>'],
                    ],
                ]),

                // ------------------------------------------------------ how to start
                // A numbered timeline, not how-to-steps: how-to-steps has an image
                // per step, and the seeder fills an empty image field with a demo
                // photo, which put random portraits next to these steps.
                ShowcaseBlocks::block('desiderio_featuretimeline', [
                    'header' => 'Start with one workflow in four steps',
                    'subheadline' => 'You don’t need the whole platform at once. Start small and grow from measured results.',
                    'items' => [
                        ['step' => '1', 'title' => 'Run the readiness check', 'description' => '<p>Answer the 11 questions with your team. The gaps show where to start.</p>'],
                        ['step' => '2', 'title' => 'Pick one repeated task', 'description' => '<p>Good first tasks are translations, content checks or accessibility reviews. They repeat often and are easy to measure.</p>'],
                        ['step' => '3', 'title' => 'Run it in a workspace', 'description' => '<p>The agent works through scoped tools, and every change waits for review in a TYPO3 workspace.</p>'],
                        ['step' => '4', 'title' => 'Measure, then widen the scope', 'description' => '<p>Compare time and cost with the manual process. Then add the next task or move to production.</p>'],
                    ],
                ]),

                // ------------------------------------------------------------ FAQ
                ShowcaseBlocks::block('desiderio_faq', [
                    'header' => 'Questions from CTOs and agency leads',
                    'subheadline' => 'Short answers to the questions that come up first.',
                    'items' => [
                        ['question' => 'Is this part of TYPO3 core?', 'answer' => '<p>No. It is a set of TYPO3 v14 extensions that run in this lab. We plan to propose the proven parts, such as the abilities registry, to the TYPO3 AI initiative.</p>'],
                        ['question' => 'Which AI models can we use?', 'answer' => '<p>Any provider that nr_llm supports: your own models, EU-hosted APIs or, if you opt in, others. nr_llm and nr_vault, which stores API keys encrypted, are open-source extensions by Netresearch DTT GmbH.</p>'],
                        ['question' => 'Can an agent publish without approval?', 'answer' => '<p>Only if your policy allows it. Agents write into workspaces, and the policy gate can ask a person to approve risky actions. In the backend AI assistant, every write waits for your confirmation.</p>'],
                        ['question' => 'Does this replace our editors?', 'answer' => '<p>No. Editors keep the TYPO3 backend and decide what goes live. Agents take on repeated tasks such as translations and checks, and their work waits for review.</p>'],
                        ['question' => 'Do we have to upgrade to TYPO3 v14?', 'answer' => '<p>Yes. The extensions are built for TYPO3 v14, the LTS release that is supported until 2029. We can plan the upgrade and the agent layer together.</p>'],
                    ],
                ]),

                // ------------------------------------------------------------ CTA
                ShowcaseBlocks::block('desiderio_ctawithimage', [
                    'header' => 'Plan your TYPO3 v14 platform with webconsulting',
                    'description' => '<p>In a call, we go through the readiness check with you and pick a first workflow for a pilot.</p>',
                    'cta_text' => 'Book a call',
                    'cta_link' => '{{page:desiderio-powermail/callback}}',
                    'image_position' => 'right',
                    'image' => self::strategyImage('strategy-planning-call-7fe89208.webp', 'Two people planning at a table', 'Two people planning at a table with a notebook sketch and a laptop, as in a first strategy call.'),
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
     * One of the generated editorial photos in Resources/Public/Styleguide/Strategy/.
     *
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    private static function strategyImage(string $filename, string $title, string $alternative): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Strategy/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Generated image for the TYPO3 v14 strategy page.',
            'source' => ShowcaseBlocks::REPO_URL,
        ];
    }
}
