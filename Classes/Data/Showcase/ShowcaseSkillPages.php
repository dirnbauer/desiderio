<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Ten skills of the typo3-skills collection, one page each below
 * /ai/agentic-skills. Every page has the same blocks in the same order, so
 * the pages read alike and a new skill is data, not a layout:
 *
 *  1. header section: the skill's name and what it is for (the page film
 *     follows it, placed by the site package's films seeder)
 *  2. three cards: why use it
 *  3. a checklist: what it can do
 *  4. four numbered steps: install, ask, prerequisites, what comes back
 *  5. the commands
 *  6. one sentence quoted from the skill's SKILL.md, with its line
 *  7. a gallery: the SKILL.md on GitHub and, where one exists, a real output
 *  8. a call to action
 *
 * The copy quotes the skills' own SKILL.md files and the facts of the public
 * repository as of 4 November 2026; nothing is promised beyond them.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcaseMedia from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 * @phpstan-type SkillShot array{file: string, title: string, alternative: string, caption: string}
 * @phpstan-type SkillDefinition array{slug: string, icon: string, title: string, navTitle: string, abstract: string, description: string, card: string, sub: string, cards: array{header: string, items: list<array{title: string, description: string}>}, checklist: array{header: string, items: list<array{title: string, text: string}>}, trigger: string, prerequisites: string, returns: string, language: string, code: string, quote: array{text: string, line: string}, shots: non-empty-list<SkillShot>}
 */
final class ShowcaseSkillPages
{
    public const string REPO_URL = 'https://github.com/dirnbauer/typo3-skills';
    private const string INSTALL = 'git clone https://github.com/dirnbauer/typo3-skills.git && cd typo3-skills && ./install.sh';

    /**
     * The ten pages, in the order the overview lists them.
     *
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return array_map(self::page(...), self::definitions());
    }

    /**
     * One card per skill for the overview on /ai/agentic-skills.
     *
     * @return list<array{slug: string, icon: string, title: string, description: string}>
     */
    public static function cards(): array
    {
        return array_map(
            static fn(array $skill): array => ['slug' => $skill['slug'], 'icon' => $skill['icon'], 'title' => $skill['navTitle'], 'description' => $skill['card']],
            self::definitions(),
        );
    }

    /**
     * @param SkillDefinition $skill
     * @return ShowcasePage
     */
    private static function page(array $skill): array
    {
        $steps = [
            ['title' => 'Install the collection', 'description' => '<p>Clone typo3-skills and run ./install.sh. The skill then loads in Claude Code, Codex, Cursor, Gemini and Windsurf.</p>'],
            ['title' => 'Ask in your own words', 'description' => '<p>For example: “' . htmlspecialchars($skill['trigger']) . '”</p>'],
            ['title' => 'Check the prerequisites', 'description' => '<p>' . htmlspecialchars($skill['prerequisites']) . '</p>'],
            ['title' => 'What comes back', 'description' => '<p>' . htmlspecialchars($skill['returns']) . '</p>'],
        ];

        return [
            'title' => $skill['title'],
            'navTitle' => $skill['navTitle'],
            'slug' => '/ai/agentic-skills/' . $skill['slug'],
            'seoTitle' => '',
            'abstract' => $skill['abstract'],
            'description' => $skill['description'],
            'parentSlug' => 'ai/agentic-skills',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'Skill',
                    'header' => $skill['slug'],
                    'subheadline' => $skill['sub'],
                    'variant' => 'center',
                ]),
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Why use it',
                    'header' => $skill['cards']['header'],
                    'items' => array_map(
                        static fn(array $card): array => ['title' => $card['title'], 'description' => '<p>' . $card['description'] . '</p>'],
                        $skill['cards']['items'],
                    ),
                ]),
                ShowcaseBlocks::block('desiderio_featurechecklist', [
                    'eyebrow' => 'What it does',
                    'header' => $skill['checklist']['header'],
                    'items' => array_map(
                        static fn(array $item): array => ['title' => $item['title'], 'description_text' => $item['text']],
                        $skill['checklist']['items'],
                    ),
                ]),
                ShowcaseBlocks::block('desiderio_featurenumbered', [
                    'header' => 'How to use it',
                    'subheadline' => 'Four steps from the install to the first result.',
                    'items' => $steps,
                ]),
                ShowcaseBlocks::block('desiderio_codeblock', [
                    'header' => 'The commands',
                    'language' => $skill['language'],
                    'filename' => $skill['language'] === 'bash' ? 'terminal' : 'template.html',
                    'code' => $skill['code'],
                ]),
                ShowcaseBlocks::block('desiderio_quote', [
                    'header' => '',
                    'quote_text' => $skill['quote']['text'],
                    'author' => $skill['slug'],
                    'role' => 'SKILL.md, ' . $skill['quote']['line'],
                    'variant' => 'large',
                ]),
                ShowcaseBlocks::block('desiderio_gallery', [
                    'header' => 'See it in the repository',
                    'subheadline' => 'Screens of the public repository, 4 November 2026.',
                    'columns' => '2',
                    'items' => array_map(
                        static fn(array $shot): array => [
                            'title' => $shot['title'],
                            'description' => $shot['caption'],
                            'link' => '',
                            'image' => ShowcaseBlocks::screenshot($shot['file'], $shot['title'], $shot['alternative'], 'Screenshot of the typo3-skills repository, 4 November 2026.'),
                        ],
                        $skill['shots'],
                    ),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Install the skills',
                    'description' => 'One clone and one install script add ' . $skill['slug'] . ' and the other 60 skills to your coding agent. Code MIT, content CC BY-SA 4.0.',
                    'cta_text' => 'Get the skills',
                    'cta_link' => self::REPO_URL,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * The SKILL.md of one skill on GitHub.
     *
     * @return SkillShot
     */
    private static function skillFile(string $slug, string $file, string $title): array
    {
        return [
            'file' => $file,
            'title' => $title,
            'alternative' => 'The SKILL.md of ' . $slug . ' on GitHub: its name, description and metadata, then the first rules.',
            'caption' => 'The skill as the agent reads it, in the public repository.',
        ];
    }

    /**
     * @return list<SkillDefinition>
     */
    private static function definitions(): array
    {
        return [
            [
                'slug' => 'typo3-upgrade-run',
                'icon' => 'rocket',
                'title' => 'typo3-upgrade-run: upgrades as a sealed graph',
                'navTitle' => 'Upgrade run',
                'abstract' => 'Orchestrates a 12/13 → 14.3 upgrade as 72 graph nodes with bounded retries and a strict-zero visual proof.',
                'description' => 'Upgrades TYPO3 v12 or v13 to v14.3 in DDEV as a sealed graph of 72 nodes, with bounded retries and a strict-zero visual proof.',
                'card' => 'A 12 or 13 to 14.3 upgrade as a sealed graph, with a strict-zero visual proof.',
                'sub' => 'Upgrades a TYPO3 v12 or v13 site to v14.3 LTS in DDEV and proves that visitors see no difference.',
                'cards' => [
                    'header' => 'An upgrade you can prove',
                    'items' => [
                        ['title' => 'The graph decides the next step', 'description' => '72 nodes and 206 edges, sealed before the first change. Small workers do one node each and return evidence.'],
                        ['title' => 'Retries have limits', 'description' => '37 bounded retry edges, three attempts per node and twelve retries per run. A recovery may touch 10 files or 400 lines.'],
                        ['title' => 'Strict zero, every time', 'description' => 'It never raises thresholds, shrinks samples or refreshes the baseline to make a difference disappear.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'From TYPO3 12 or 13 to 14.3 LTS',
                    'items' => [
                        ['title' => 'Upgrade in rungs', 'text' => 'The 13.4 rung, Rector and Fractor, data migrations, then the 14.3 rung with 15 known breakers fixed up front.'],
                        ['title' => 'Prove visual and behavioural parity', 'text' => 'HTTP, DOM and pixels on every URL, plus consent, sliders, forms, search and backend checks.'],
                        ['title' => 'Capture quickly', 'text' => 'On a 60-URL site, one full capture of 360 screenshots took 6.3 to 6.5 minutes with four workers.'],
                        ['title' => 'Plan from measured minutes', 'text' => 'Each run stores the minutes per node. On that site the migration nodes took about 3 h 45 min.'],
                        ['title' => 'Add structured data and WebMCP', 'text' => 'Once Contract A is signed off, Contract B adds JSON-LD and browser tools in every run.'],
                    ],
                ],
                'trigger' => 'Update this TYPO3 12 project to 14.3 in DDEV and make sure the site looks exactly the same afterwards.',
                'prerequisites' => 'Node 22.19 or later, DDEV and Git, a dated database dump with fileadmin, and an owner who answers the intake questions.',
                'returns' => 'Evidence for every node, exit codes from 0 to 5 and a closure certificate that a person accepts. It never deploys to staging or live.',
                'language' => 'bash',
                'code' => implode("\n", [
                    'cd skills/typo3-upgrade-run/scripts && npm ci && npm test',
                    '',
                    'cd /path/to/the-selected-project',
                    't3u init --base-url "https://acme.ddev.site" --ddev-project acme --languages de,en',
                    't3u doctor',
                    't3u graph-init',
                    '',
                    '# Repeat until graph-status reports complete or blocked',
                    't3u graph-next',
                    't3u node-brief --node <id>',
                    '',
                    '# Final proof',
                    't3u closure-start',
                    't3u closure-check',
                    't3u closure-verify',
                ]),
                'quote' => ['text' => 'Determinism uses strict zero. Never raise thresholds, shrink samples, quarantine pages, or refresh the baseline to make a difference disappear.', 'line' => 'lines 251–252'],
                'shots' => [
                    self::skillFile('typo3-upgrade-run', 'frontend-feature-skills-typo3-upgrade-run-803722c7.webp', 'The controller of a sealed graph'),
                    ['file' => 'frontend-feature-skills-t3u-help-e9baae69.webp', 'title' => 'The t3u harness', 'alternative' => 'Terminal with the output of node t3u.mjs --help: the commands of the upgrade harness and the exit codes 0 to 5.', 'caption' => 'Every command of the harness, from init to closure-verify, and its exit codes.'],
                ],
            ],
            [
                'slug' => 'typo3-wcag22-aa-agentic',
                'icon' => 'users',
                'title' => 'typo3-wcag22-aa-agentic: WCAG 2.2 AA scans',
                'navTitle' => 'WCAG 2.2 AA scans',
                'abstract' => 'Playwright and axe scans that name the Fluid template behind each finding, with a CI gate and a draft accessibility statement.',
                'description' => 'WCAG 2.2 AA scans of a TYPO3 site with Playwright and axe-core, mapped to Fluid templates, with a CI gate and a review by people.',
                'card' => 'Playwright and axe scans mapped to Fluid templates, with a CI gate.',
                'sub' => 'Scans a TYPO3 site against WCAG 2.2 AA and names the Fluid template behind each finding.',
                'cards' => [
                    'header' => 'Accessibility work you can hand over',
                    'items' => [
                        ['title' => 'Findings point at templates', 'description' => 'Each violation is mapped back to the Fluid template that produced it, so a developer knows where to fix it.'],
                        ['title' => 'New violations fail the build', 'description' => 'A CI baseline makes a pull request fail when it adds violations, while the known ones are worked off.'],
                        ['title' => 'People review what scanners miss', 'description' => 'Alt text, keyboard logic, screen-reader clarity, PDFs and videos go to a short manual review.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'From the first scan to the statement',
                    'items' => [
                        ['title' => 'Find every URL', 'text' => 'npm run a11y:discover builds the list of pages to scan.'],
                        ['title' => 'Scan in report-only mode first', 'text' => 'The first run records the current state without failing anything.'],
                        ['title' => 'Write a report per page', 'text' => 'npm run a11y:report lists each finding with the template behind it.'],
                        ['title' => 'Draft the accessibility statement', 'text' => 'For Austria, Germany and Switzerland, as a draft for a person to check, never as legal advice.'],
                    ],
                ],
                'trigger' => 'Scan our DDEV site at https://acme.ddev.site and tell me everything that fails WCAG 2.2 AA, page by page.',
                'prerequisites' => 'Node.js 20 or later, npm and the Playwright browsers. The axe MCP server is optional.',
                'returns' => 'A report per page with template paths, a CI baseline and a draft accessibility statement for review.',
                'language' => 'bash',
                'code' => implode("\n", [
                    './Skills.sh scaffold --project /path/to/typo3-project',
                    'cd /path/to/typo3-project',
                    'cp .a11y/a11y.config.example.yml .a11y/a11y.config.yml',
                    'npm install',
                    'npx playwright install --with-deps chromium',
                    'npm run a11y:all',
                ]),
                'quote' => ['text' => 'Never claim complete WCAG conformance from automated scans alone. Automated scans are evidence.', 'line' => 'line 27'],
                'shots' => [self::skillFile('typo3-wcag22-aa-agentic', 'frontend-feature-skills-typo3-wcag22-aa-agentic-49f27ed9.webp', 'Scans first, people decide')],
            ],
            [
                'slug' => 'typo3-webmcp',
                'icon' => 'bot',
                'title' => 'typo3-webmcp: typed browser tools for agents',
                'navTitle' => 'WebMCP tools',
                'abstract' => 'Makes search, navigation and forms of a TYPO3 site available to browser agents as typed tools, with people still submitting.',
                'description' => 'Search, navigation and forms of a TYPO3 v14.3 site as typed WebMCP tools for browser agents, as progressive enhancement and with people submitting.',
                'card' => 'Typed browser tools for search, navigation and forms; people still submit.',
                'sub' => 'Makes search, navigation and forms of a TYPO3 site available to browser agents as typed tools.',
                'cards' => [
                    'header' => 'Ready for agents, still built for people',
                    'items' => [
                        ['title' => 'Small, typed tools', 'description' => 'search_site, navigate_site and get_current_page give agents the same results and targets as the page.'],
                        ['title' => 'Progressive enhancement', 'description' => 'A browser without WebMCP gets the same working website, without errors.'],
                        ['title' => 'People still submit', 'description' => 'Form tools prepare the input and the visitor confirms it. A schema or an agent’s promise is not consent.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'Visitor journeys as browser tools',
                    'items' => [
                        ['title' => 'Search as a read-only tool', 'text' => 'Same result order and access filters as the search page.'],
                        ['title' => 'Navigation to fixed targets', 'text' => 'Canonical targets that TYPO3 generates, never arbitrary URLs.'],
                        ['title' => 'Facts of the current page', 'text' => 'Only visible fields; user content is marked as untrusted.'],
                        ['title' => 'Forms prepared, not sent', 'text' => 'Declarative annotations fill a form, and a person submits it.'],
                        ['title' => 'Same origin by default', 'text' => 'Cross-origin use only when it is named, approved and tested.'],
                    ],
                ],
                'trigger' => 'Make the search and the contact form on this TYPO3 v14 website available to browser agents through WebMCP.',
                'prerequisites' => 'A TYPO3 v14.3 sitepackage with a Vite entry, HTTPS, and Chrome with the WebMCP testing flag or the origin trial from Chrome 149.',
                'returns' => 'Tools you can inspect in a WebMCP-enabled Chrome profile, with their names, descriptions and schemas, and the evidence of each test.',
                'language' => 'bash',
                'code' => implode("\n", [
                    self::INSTALL,
                    '',
                    '# Test profile in Chrome (references/current-api.md):',
                    '# chrome://flags/#enable-webmcp-testing',
                ]),
                'quote' => ['text' => 'Make useful visitor journeys discoverable as small, typed browser tools without weakening the human interface, TYPO3 permissions, privacy, or server-side validation.', 'line' => 'lines 19–20'],
                'shots' => [self::skillFile('typo3-webmcp', 'frontend-feature-skills-typo3-webmcp-3b02875a.webp', 'Tools for the open page only')],
            ],
            [
                'slug' => 'typo3-structured-data',
                'icon' => 'code',
                'title' => 'typo3-structured-data: JSON-LD per page',
                'navTitle' => 'Structured data',
                'abstract' => 'Builds schema.org JSON-LD from the records that render the page, keeps it identical through an upgrade, enriches it afterwards.',
                'description' => 'Schema.org JSON-LD for TYPO3 v14.3, built from the records that render the page, kept identical through an upgrade and enriched afterwards.',
                'card' => 'One JSON-LD graph per page, built from the records that render it.',
                'sub' => 'Builds schema.org JSON-LD from the records that render the page and keeps it identical through an upgrade.',
                'cards' => [
                    'header' => 'Markup that matches the page',
                    'items' => [
                        ['title' => 'No second truth', 'description' => 'Types and properties come only from visible, current content. It never invents a price, an opening time or an FAQ answer.'],
                        ['title' => 'One connected graph', 'description' => 'Organisation, website, page and main entity share stable @id values instead of contradicting duplicates.'],
                        ['title' => 'Honest about results', 'description' => 'Valid markup makes a page eligible for search features. Whether they appear is the search engine’s decision.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'From audit to validated graph',
                    'items' => [
                        ['title' => 'Audit what is there', 'text' => 'Every JSON-LD block, Microdata fragment, schema extension and producer, before anything changes.'],
                        ['title' => 'Describe businesses, articles and events', 'text' => 'LocalBusiness, Article, Product, Event, JobPosting and more, where the page shows them.'],
                        ['title' => 'Map news records to articles', 'text' => 'EXT:news detail pages describe the article they show.'],
                        ['title' => 'Keep upgrade parity', 'text' => 'The upgrade keeps the markup identical; enrichment follows afterwards, once approved.'],
                        ['title' => 'Validate the whole graph', 'text' => 'With the Schema.org Validator, and with Google’s Rich Results Test for the types Google supports.'],
                    ],
                ],
                'trigger' => 'Add LocalBusiness and opening-hours structured data to this TYPO3 company website.',
                'prerequisites' => 'TYPO3 v14.3 in DDEV, optionally brotkrueml/schema, and the Schema.org Validator.',
                'returns' => 'Server-rendered JSON-LD per page type, with the parsed output and the validator result as evidence.',
                'language' => 'bash',
                'code' => implode("\n", [
                    self::INSTALL,
                    '',
                    '# Then ask your agent:',
                    '# Add LocalBusiness and opening-hours structured data to this TYPO3 company website.',
                ]),
                'quote' => ['text' => 'Make a TYPO3 page describe what it visibly is.', 'line' => 'line 20'],
                'shots' => [self::skillFile('typo3-structured-data', 'frontend-feature-skills-typo3-structured-data-19745232.webp', 'One graph, from visible content')],
            ],
            [
                'slug' => 'typo3-backend-rights',
                'icon' => 'key-round',
                'title' => 'typo3-backend-rights: verified editor roles',
                'navTitle' => 'Backend rights',
                'abstract' => 'Audits and builds editor groups, page and language rights, MFA and login branding without ever locking out the admin.',
                'description' => 'TYPO3 backend rights as a verified model: editor groups, content types, page and language rights, MFA and login branding, with the admin kept.',
                'card' => 'Editor roles you can verify, without ever locking out the admin.',
                'sub' => 'Audits and builds editor groups, page and language rights, MFA and login branding without locking out the admin.',
                'cards' => [
                    'header' => 'Rights as a verified model',
                    'items' => [
                        ['title' => 'One main group, or your roles', 'description' => 'You choose: one user-facing group with simple internal leaves, or the distinct roles you already run.'],
                        ['title' => 'The administrator stays', 'description' => 'A separate, working administrator is kept before anything changes. A lockout costs more than any permission gain.'],
                        ['title' => 'Every change approved', 'description' => 'Group structure, each user’s membership and page ownership are three separate approvals.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'Complete rights for every editor',
                    'items' => [
                        ['title' => 'Audit the live rights', 'text' => 'A read-only script reports groups, users and their rights as JSON.'],
                        ['title' => 'Allow every content type in use', 'text' => 'An empty allow-list for content types is a defect, not “allow everything”.'],
                        ['title' => 'Cover the rest of the backend', 'text' => 'Languages, modules, mounts, MFA, forms, Workspaces, the Visual Editor and Redirects.'],
                        ['title' => 'Brand the login per environment', 'text' => 'Live, staging and DDEV each get a fixed colour from the Application Context.'],
                        ['title' => 'Write through scripts, dry run first', 'text' => 'No hand-written SQL for pages, users or Workspaces.'],
                    ],
                ],
                'trigger' => 'Create one main backend editor role with simple subgroups that can manage all normal content, news records, and files.',
                'prerequisites' => 'A TYPO3 v14.3 installation in DDEV, a separate working administrator and your answer to the group-model question.',
                'returns' => 'An audit, a dry-run plan, the affected group and user IDs, and a login and editing test by an editor without admin rights.',
                'language' => 'bash',
                'code' => implode("\n", [
                    'ddev exec php /dev/stdin --pretty \\',
                    '  < /absolute/path/to/typo3-backend-rights/scripts/audit-backend-rights.php',
                ]),
                'quote' => ['text' => 'Treat permissions as a verified model, not a copied backend record.', 'line' => 'lines 12–13'],
                'shots' => [self::skillFile('typo3-backend-rights', 'frontend-feature-skills-typo3-backend-rights-b8e0281c.webp', 'Rules that always hold')],
            ],
            [
                'slug' => 'typo3-content-blocks',
                'icon' => 'component',
                'title' => 'typo3-content-blocks: elements in YAML',
                'navTitle' => 'Content Blocks',
                'abstract' => 'Defines content elements, records and page types in one config.yaml each, and migrates Mask without losing records.',
                'description' => 'TYPO3 Content Blocks 2.x for v14.3: elements, records and page types defined in one config.yaml each, and Mask migrated without losing records.',
                'card' => 'Elements, records and page types, defined in one config.yaml each.',
                'sub' => 'Defines content elements, records and page types in one config.yaml each, and migrates Mask without losing records.',
                'cards' => [
                    'header' => 'One file per block',
                    'items' => [
                        ['title' => 'YAML is the source of truth', 'description' => 'Content Blocks generates TCA, SQL, TypoScript, backend forms and labels from config.yaml.'],
                        ['title' => 'Records survive a migration', 'description' => 'Identifiers, prefixes and tables stay stable, because a renamed identifier leaves records that look empty.'],
                        ['title' => 'Checked against the real version', 'description' => 'Spot-checked on Content Blocks 2.4.10 with TYPO3 v14.3.9, and verified against vendor/, not memory.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'Blocks from first field to migration',
                    'items' => [
                        ['title' => 'Create elements, records and page types', 'text' => 'File types too, each with its own config.yaml.'],
                        ['title' => 'Nest collections safely', 'text' => 'Every nested collection gets an explicit table name, so none collide.'],
                        ['title' => 'Migrate Mask and fluid_styled_content', 'text' => 'With the existing records kept.'],
                        ['title' => 'Fix blank backend previews', 'text' => 'Including child rows that render through the parent’s preview on 14.3.'],
                    ],
                ],
                'trigger' => 'I need a teaser element with a headline, an image and a link. Set up the definition for it.',
                'prerequisites' => 'TYPO3 v14.3 or later with friendsoftypo3/content-blocks 2.x; release 2.4.10 needs TYPO3 v14.3.7.',
                'returns' => 'A block with config.yaml, templates and labels, then a schema update and a check of the form, the preview and the frontend.',
                'language' => 'bash',
                'code' => implode("\n", [
                    'ddev composer require friendsoftypo3/content-blocks',
                    'ddev typo3 make:content-block',
                    'ddev typo3 cache:flush -g system',
                    'ddev typo3 extension:setup --extension=<ext_key>',
                ]),
                'quote' => ['text' => 'One config.yaml per block is the source of truth.', 'line' => 'line 25'],
                'shots' => [self::skillFile('typo3-content-blocks', 'frontend-feature-skills-typo3-content-blocks-8c9d7ba2.webp', 'Target v14.3 with Content Blocks 2.x')],
            ],
            [
                'slug' => 'typo3-shadcn-content-elements',
                'icon' => 'layout-grid',
                'title' => 'typo3-shadcn-content-elements: shadcn/ui as Fluid',
                'navTitle' => 'shadcn elements',
                'abstract' => 'Turns shadcn/ui presets into Fluid content elements with semantic tokens, dark mode, backend previews and seed data.',
                'description' => 'shadcn/ui presets as TYPO3 Content Blocks elements in Fluid: semantic tokens, dark mode, backend previews, icons and seed data, without React.',
                'card' => 'shadcn/ui presets as Fluid elements with tokens, previews and seed data.',
                'sub' => 'Turns shadcn/ui presets into Fluid content elements with semantic tokens, dark mode, backend previews and seed data.',
                'cards' => [
                    'header' => 'shadcn/ui, rendered by TYPO3',
                    'items' => [
                        ['title' => 'No React at runtime', 'description' => 'Tokens and class contracts are ported into Fluid. Small vanilla controllers handle interaction, with ARIA.'],
                        ['title' => 'Tokens, not colours', 'description' => 'Elements hold no raw colour values, so a preset switch and dark mode reach every one of them.'],
                        ['title' => 'Previews editors recognise', 'description' => 'Each element gets a backend preview, so editors know the content without opening the form.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'A design system as content elements',
                    'items' => [
                        ['title' => 'Audit before editing', 'text' => 'A script lists every element’s schema, template, token and preview work.'],
                        ['title' => 'Turn a preset into tokens', 'text' => 'extract-shadcn-preset.mjs writes the theme CSS for a shadcn/ui preset.'],
                        ['title' => 'Fix the shared components first', 'text' => 'Atoms and molecules follow shadcn class contracts before single templates change.'],
                        ['title' => 'Keep the local registry valid', 'text' => 'registry.json and shadcn build after every token change.'],
                    ],
                ],
                'trigger' => 'These elements have hardcoded hex colours everywhere. Move them to semantic tokens.',
                'prerequisites' => 'Content Blocks, Tailwind CSS v4 and the shadcn CLI in a TYPO3 v14.3 project.',
                'returns' => 'An audit of the elements, then templates and CSS on semantic tokens, with previews and icons checked in light and dark.',
                'language' => 'bash',
                'code' => implode("\n", [
                    'php <skill>/scripts/audit-content-elements.php <repo-root>',
                    'node scripts/extract-shadcn-preset.mjs --scratch <dir> --preset <id> --out <css> --meta <json>',
                    'shadcn build --output <dir>',
                ]),
                'quote' => ['text' => 'Never install React, Radix or Astro components as TYPO3 runtime code.', 'line' => 'line 25'],
                'shots' => [self::skillFile('typo3-shadcn-content-elements', 'frontend-feature-skills-typo3-shadcn-content-elements-f4d8fb3c.webp', 'One coherent shadcn/ui system')],
            ],
            [
                'slug' => 'typo3-visual-editor',
                'icon' => 'monitor',
                'title' => 'typo3-visual-editor: editing on the page',
                'navTitle' => 'Visual Editor',
                'abstract' => 'Prepares templates so editors click into the page and type, with every output traceable to its record and field.',
                'description' => 'Templates ready for the TYPO3 Visual Editor: editors click into the page and type. Every output traces back to its record, field and content area.',
                'card' => 'Editors type on the page; every output traces back to its record.',
                'sub' => 'Prepares templates so editors click into the page and type, with every output traceable to its record and field.',
                'cards' => [
                    'header' => 'Editing on the page, done properly',
                    'items' => [
                        ['title' => 'Templates first', 'description' => 'Installing the extension is the small part. Every output must trace back to a record, a field and a content area.'],
                        ['title' => 'Core ViewHelpers', 'description' => 'f:render.text for text fields and f:render.contentArea for page areas. Both stay useful without the extension.'],
                        ['title' => 'Tested as an editor', 'description' => 'Readiness is never judged as an administrator. Restricted editors, translations and workspaces count.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'A sitepackage ready for inline editing',
                    'items' => [
                        ['title' => 'Install the Visual Editor', 'text' => 'The newest release that fits the project, checked against its own constraints.'],
                        ['title' => 'Move page columns to content areas', 'text' => 'f:render.contentArea instead of custom rendering.'],
                        ['title' => 'Make text fields editable', 'text' => 'f:render.text, field by field.'],
                        ['title' => 'Keep escaping intact', 'text' => 'No raw output as a shortcut to make editing work.'],
                        ['title' => 'Remove it cleanly', 'text' => 'The Core ViewHelpers stay in place.'],
                    ],
                ],
                'trigger' => 'Editors want to click into the page and type, instead of opening backend forms.',
                'prerequisites' => 'TYPO3 v13.4.22 or v14.3 and later with PHP 8.2 or later; release 1.10.4 was checked on 31 October 2026.',
                'returns' => 'Templates whose text fields and content areas are editable inline, checked with a restricted editor, translations and a workspace.',
                'language' => 'html',
                'code' => implode("\n", [
                    '<!-- ddev composer require friendsoftypo3/visual-editor -->',
                    '<!-- Before -->',
                    '<h1>{record.header}</h1>',
                    '',
                    '<!-- After -->',
                    "<h1>{record -> f:render.text(field: 'header')}</h1>",
                    "<div class=\"prose\">{record -> f:render.text(field: 'bodytext')}</div>",
                ]),
                'quote' => ['text' => 'Installing the extension is the small part: the frontend output must be traceable back to records, fields and content areas.', 'line' => 'lines 16–17'],
                'shots' => [self::skillFile('typo3-visual-editor', 'frontend-feature-skills-typo3-visual-editor-12983246.webp', 'Traceable to records and fields')],
            ],
            [
                'slug' => 'typo3-rector',
                'icon' => 'git-branch',
                'title' => 'typo3-rector and typo3-fractor: automated fixes',
                'navTitle' => 'Rector and Fractor',
                'abstract' => 'Dry run, review, apply, dry run again: PHP with Rector, TypoScript, Fluid and FlexForm with Fractor.',
                'description' => 'Deprecated TYPO3 code fixed by tools for v14: PHP with Rector, TypoScript, Fluid, FlexForm and YAML with Fractor, with a second dry run.',
                'card' => 'Dry run, review, apply, dry run again: Rector for PHP, Fractor for the rest.',
                'sub' => 'Fixes deprecated TYPO3 code with tools: PHP with Rector, TypoScript, Fluid, FlexForm and YAML with Fractor.',
                'cards' => [
                    'header' => 'Let the tools do the rewriting',
                    'items' => [
                        ['title' => 'Rules, not hand edits', 'description' => 'Manual replacements miss namespace moves, signature changes and edge cases the rules already cover.'],
                        ['title' => 'Always a second pass', 'description' => 'A rule can generate code that another rule only sees on the next pass, so one applied pass is not completion.'],
                        ['title' => 'Passes with a limit', 'description' => 'At most three apply passes on their own, and it stops earlier when two passes change nothing.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'From deprecated to v14',
                    'items' => [
                        ['title' => 'Rule sets for the v14 path', 'text' => 'From v12 or v13: ViewFactory, Extbase responses, PSR-14 events, backend modules and TCA.'],
                        ['title' => 'Rewrite the other files with Fractor', 'text' => 'TypoScript, FlexForms, Fluid, YAML and XLIFF.'],
                        ['title' => 'Skip destructive rules for now', 'text' => 'Until their replacement exists, with the manual follow-up recorded.'],
                        ['title' => 'Run on the project’s PHP', 'text' => '8.4 for project work, inside DDEV when the host differs.'],
                    ],
                ],
                'trigger' => 'Automatically fix the deprecated PHP calls in Classes/ for TYPO3 v14.',
                'prerequisites' => 'The project’s own PHP version and the dev packages ssch/typo3-rector and a9f/typo3-fractor.',
                'returns' => 'A reviewed diff, the applied changes and a second dry run that reports no further changes.',
                'language' => 'bash',
                'code' => implode("\n", [
                    'ddev composer require --dev ssch/typo3-rector',
                    'ddev exec vendor/bin/rector process --dry-run',
                    'ddev exec vendor/bin/rector process packages/my_extension',
                    '',
                    'ddev composer require --dev a9f/typo3-fractor',
                    'vendor/bin/fractor process --dry-run',
                    'vendor/bin/fractor process',
                ]),
                'quote' => ['text' => 'Dry run → review the diff → apply → dry run again.', 'line' => 'line 26'],
                'shots' => [self::skillFile('typo3-rector', 'frontend-feature-skills-typo3-rector-bd985e9e.webp', 'Rector for the PHP code')],
            ],
            [
                'slug' => 'typo3-records-list-types',
                'icon' => 'layout-sidebar-right',
                'title' => 'typo3-records-list-types: cards and boards',
                'navTitle' => 'Records list types',
                'abstract' => 'Configures grid, teaser, timeline and board views of the v14 Records module through TSconfig, no patches.',
                'description' => 'Grid, compact, teaser, timeline and board views for the TYPO3 v14 Records module, configured per record type through TSconfig and Fluid.',
                'card' => 'Grid, teaser, timeline and board views of the Records module, set in TSconfig.',
                'sub' => 'Configures grid, teaser, timeline and board views of the TYPO3 v14 Records module through TSconfig, without patches.',
                'cards' => [
                    'header' => 'Records you recognise at a glance',
                    'items' => [
                        ['title' => 'Cards with thumbnails', 'description' => 'A grid, a compact list, teasers, a timeline or a board, instead of a plain table of rows.'],
                        ['title' => 'Configured, never patched', 'description' => 'Views, fields and templates live in Page TSconfig, Fluid and CSS of your sitepackage.'],
                        ['title' => 'Accessibility is evidenced', 'description' => 'Keyboard and ARIA patterns are no certificate. The installed release is tested by hand and by tools, keyboard drag and drop included.'],
                    ],
                ],
                'checklist' => [
                    'header' => 'The Records module, your way',
                    'items' => [
                        ['title' => 'Set the default view', 'text' => 'In TSconfig; a URL parameter and each user’s choice come first.'],
                        ['title' => 'Allow the views you want', 'text' => 'List, grid, compact and teaser, in the toggle of the module header.'],
                        ['title' => 'Choose the fields per table', 'text' => 'Which fields each record type shows on its cards.'],
                        ['title' => 'Set the grid columns', 'text' => 'From two to six columns, four by default.'],
                        ['title' => 'Test workspaces first', 'text' => 'Workspace support is experimental, so drag and drop is tested there before editors rely on it.'],
                    ],
                ],
                'trigger' => 'The Records module shows a plain table. Can I get cards with thumbnails instead?',
                'prerequisites' => 'TYPO3 v14.3.7 or later and PHP 8.4, plus a VCS repository entry, because the package is not on Packagist.',
                'returns' => 'TSconfig and templates in your sitepackage, and a Records module with the views you allowed.',
                'language' => 'bash',
                'code' => implode("\n", [
                    '# composer.json: add the VCS repository https://github.com/dirnbauer/typo3-records-list-types.git',
                    'composer require webconsulting/records-list-types:^2.0',
                    './vendor/bin/typo3 extension:setup -e records_list_types',
                    '',
                    '# Page TSconfig',
                    '# mod.web_list.viewMode.default = grid',
                    '# mod.web_list.viewMode.allowed = list,grid,compact,teaser',
                ]),
                'quote' => ['text' => 'Configure, do not patch.', 'line' => 'line 25'],
                'shots' => [self::skillFile('typo3-records-list-types', 'frontend-feature-skills-typo3-records-list-types-2f7132c5.webp', 'Views set through TSconfig')],
            ],
        ];
    }
}
