<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The typo3-skills collection in twelve groups of three or four skills, the
 * other skills of the collection, and the community talks the skills build
 * on. ShowcaseSkillPages turns this into the group pages below
 * /ai/agentic-skills and the sections of the hub.
 *
 * Every statement about a skill comes from the public repository
 * (github.com/dirnbauer/typo3-skills at c158583, 8 November 2026): its
 * README, AGENTS.md, VENDORED.md, install.sh, the skill's SKILL.md and its
 * evals. "say" is a request from the skill's evals (agents/openai.yaml where
 * that is the better one); a vendored skill without evals gets a request
 * built from its own "use when" lines. 20 skills are vendored unchanged from
 * their authors; "upstream" names their repository, and changes to them
 * belong there.
 *
 * The talks are other people's work: the speakers are named, and every
 * talk links its own materials. webconsulting gave none of them.
 *
 * @phpstan-type SkillEntry array{name: string, owner: string, upstream: string, superfeature: bool, icon: string, title: string, line: string, headline: string, can: string, cool: string, why: string, say: string}
 * @phpstan-type SkillGroup array{slug: string, title: string, abstract: string, description: string, header: string, pitch: string, skills: non-empty-list<SkillEntry>}
 * @phpstan-type OtherSkill array{name: string, owner: string, line: string, group: string}
 * @phpstan-type CommunityTalk array{date: string, topic: string, title: string, kind: string, speaker: string, at: string, event: string, eventUrl: string, materials: string, extra: array{text: string, url: string}|null, skills: non-empty-list<string>}
 */
final class ShowcaseSkillGroups
{
    public const string REPO_URL = 'https://github.com/dirnbauer/typo3-skills';
    public const string ISSUES_URL = 'https://github.com/dirnbauer/typo3-skills/issues';

    /** The hub's slug, without the leading slash, as {{page:}} placeholders name it. */
    public const string HUB = 'ai/agentic-skills';

    /** The skill search another package owns: the hub's first child. */
    public const string SEARCH = 'ai/agentic-skills/find';

    private const string NETRESEARCH = 'Netresearch DTT GmbH';

    /**
     * The twelve groups, in the order the hub and the menu list them.
     *
     * @return list<SkillGroup>
     */
    public static function groups(): array
    {
        return [
            self::provenSiteUpgrade(),
            self::upgradePlanning(),
            self::codeMigration(),
            self::contentModelling(),
            self::editorExperience(),
            self::designSystem(),
            self::v14Development(),
            self::accessibility(),
            self::searchSeoAgents(),
            self::securityOperations(),
            self::testingQuality(),
            self::releaseOpenSource(),
        ];
    }

    /**
     * The skills of the collection that no group page features, each next
     * to the group it belongs with.
     *
     * @return list<OtherSkill>
     */
    public static function otherSkills(): array
    {
        return [
            ['name' => 'typo3-powermail', 'owner' => 'webconsulting', 'line' => 'Builds and debugs Powermail forms, including conditional fields.', 'group' => 'content-modelling'],
            ['name' => 'typo3-jev', 'owner' => 'webconsulting', 'line' => 'Connects TypeSafe Jev decisions to forms, for example to route enquiries.', 'group' => 'content-modelling'],
            ['name' => 'php-modernization', 'owner' => self::NETRESEARCH, 'line' => 'Moves PHP code to modern PHP 8 features and tooling: PHPStan, Rector and PHP-CS-Fixer.', 'group' => 'code-migration'],
            ['name' => 'web-design-guidelines', 'owner' => 'Vercel', 'line' => 'Reviews user interfaces against the Web Interface Guidelines.', 'group' => 'design-system'],
            ['name' => 'web-platform-design', 'owner' => 'ehmo', 'line' => 'Web platform design, responsive layout and accessibility guidance.', 'group' => 'design-system'],
            ['name' => 'security-audit', 'owner' => self::NETRESEARCH, 'line' => 'Security assessments against OWASP, CWE and CVSS.', 'group' => 'security-operations'],
            ['name' => 'security-incident-reporting', 'owner' => 'webconsulting', 'line' => 'Incident reports, post-mortems and timelines after a security event.', 'group' => 'security-operations'],
            ['name' => 'thermo-nuclear-code-quality-review', 'owner' => 'Cursor', 'line' => 'A very strict, review-only audit of a branch, on request only.', 'group' => 'testing-quality'],
            ['name' => 'postgres-best-practices', 'owner' => 'Supabase', 'line' => 'Indexes, locks and schema for PostgreSQL databases.', 'group' => 'v14-development'],
            ['name' => 'architecture-decision-records', 'owner' => 'webconsulting', 'line' => 'Records what was decided, why, and what was rejected.', 'group' => 'release-open-source'],
            ['name' => 'webconsulting-create-documentation', 'owner' => 'webconsulting', 'line' => 'Builds product documentation with screenshots, tours and narration.', 'group' => 'release-open-source'],
            ['name' => 'typo3-idea-extension-blog', 'owner' => 'webconsulting', 'line' => 'Checks whether an idea is worth an extension, builds it and writes the blog post.', 'group' => 'release-open-source'],
        ];
    }

    /**
     * The two skills of the collection the page leaves out: they serve
     * webconsulting's own brand and Austrian legal notices.
     *
     * @return list<string>
     */
    public static function unlistedSkills(): array
    {
        return ['webconsulting-branding', 'legal-impressum'];
    }

    /**
     * Five community talks the skills build on, oldest first: expert talks
     * at the Munich TYPO3 User Group (MTUG) and sessions of TYPO3camp
     * München. The titles stay in German, as the speakers gave them.
     *
     * @return list<CommunityTalk>
     */
    public static function talks(): array
    {
        return [
            [
                'date' => '9 October 2023',
                'topic' => 'Content types with Content Blocks',
                'title' => 'Inhaltstypen mit Content Blocks in TYPO3 v12 erstellen',
                'kind' => 'talk',
                'speaker' => 'Nikita Hovratov',
                'at' => 'an',
                'event' => 'MTUG meeting',
                'eventUrl' => 'https://www.mtug.de/treffen/veranstaltungen-2023/oktober.html',
                'materials' => 'https://docs.google.com/presentation/d/1QZPMNXPM5zyvI7NEMbJfU9NV26PZpzYLFmYSmg9Iz-Y/edit',
                'extra' => ['text' => 'demo extension from the talk', 'url' => 'https://github.com/nhovratov/content-blocks-mtug'],
                'skills' => ['typo3-content-blocks'],
            ],
            [
                'date' => '13 October 2025',
                'topic' => 'Upgrade strategy and tools',
                'title' => 'TYPO3 Upgrades – Strategie und Tools',
                'kind' => 'talk',
                'speaker' => 'Marcus Schwemer',
                'at' => 'an',
                'event' => 'MTUG meeting',
                'eventUrl' => 'https://www.mtug.de/treffen/veranstaltungen-2025/oktober.html',
                'materials' => 'https://www.mtug.de/fileadmin/files/2025/MTUG_2025_10_TYPO3_Upgrades_Marcus_Schwemer.pdf',
                'extra' => null,
                'skills' => ['typo3-upgrade-run', 'typo3-upgrade-intake', 'typo3-project-upgrade', 'typo3-upgrade-migration', 'typo3-rector', 'typo3-fractor'],
            ],
            [
                'date' => '13 April 2026',
                'topic' => 'What TYPO3 v14 brings',
                'title' => 'TYPO3 v14: Vorstellung der neuen Features und Highlights',
                'kind' => 'talk',
                'speaker' => 'Garvin Hicking',
                'at' => 'an',
                'event' => 'MTUG meeting',
                'eventUrl' => 'https://www.mtug.de/treffen/veranstaltungen-2026/april.html',
                'materials' => 'https://garv.in/typo3/mtug-v14/',
                'extra' => null,
                'skills' => ['typo3-v14-reference', 'typo3-extension-upgrade', 'typo3-upgrade-run', 'typo3-visual-editor'],
            ],
            [
                'date' => '12 September 2026',
                'topic' => 'Testing accessibility',
                'title' => 'Barrierefreiheit testen',
                'kind' => 'session',
                'speaker' => 'Lukas Fritze',
                'at' => '',
                'event' => 'TYPO3camp München 2026',
                'eventUrl' => 'https://www.typo3camp-muenchen.de/session/barrierefreiheit-testen-93',
                'materials' => 'https://lukasfritze.dev/assets/barrierefreiheit-testen.pdf',
                'extra' => null,
                'skills' => ['typo3-wcag22-aa-agentic', 'typo3-accessibility', 'typo3-a11y'],
            ],
            [
                'date' => '13 September 2026',
                'topic' => 'SEO and GEO with Schema.org',
                'title' => 'SEO und GEO mit Schema.org',
                'kind' => 'session',
                'speaker' => 'Chris Müller',
                'at' => '',
                'event' => 'TYPO3camp München 2026',
                'eventUrl' => 'https://www.typo3camp-muenchen.de/session/geo-und-seo-mit-schemaorg-106',
                'materials' => 'https://brotkrueml.dev/presentations/t3cm26/schema.html',
                'extra' => null,
                'skills' => ['typo3-structured-data', 'typo3-seo'],
            ],
        ];
    }

    /**
     * The ten skill pages that gave way to the group pages, by their slug:
     * the group page that holds the skill now and its anchor there.
     *
     * @return array<string, array{successor: string, anchor: string}>
     */
    public static function retiredSkillPages(): array
    {
        $groups = [
            'typo3-upgrade-run' => 'proven-site-upgrade',
            'typo3-wcag22-aa-agentic' => 'accessibility',
            'typo3-webmcp' => 'search-seo-agents',
            'typo3-structured-data' => 'search-seo-agents',
            'typo3-backend-rights' => 'security-operations',
            'typo3-content-blocks' => 'content-modelling',
            'typo3-shadcn-content-elements' => 'design-system',
            'typo3-visual-editor' => 'editor-experience',
            'typo3-rector' => 'code-migration',
            'typo3-records-list-types' => 'editor-experience',
        ];
        $retired = [];
        foreach ($groups as $skill => $group) {
            $retired['/' . self::HUB . '/' . $skill] = ['successor' => '/' . self::HUB . '/' . $group, 'anchor' => $skill];
        }

        return $retired;
    }

    /**
     * @return SkillGroup
     */
    private static function provenSiteUpgrade(): array
    {
        return [
            'slug' => 'proven-site-upgrade',
            'title' => 'Proven site upgrade',
            'abstract' => 'Four skills upgrade a TYPO3 v12 or v13 site to 14.3 LTS in local DDEV and prove that visitors see no difference.',
            'description' => 'Upgrade a TYPO3 v12 or v13 site to 14.3 LTS in local DDEV with typo3-upgrade-run, intake, baseline and closure, and prove nothing visible changed.',
            'header' => 'Upgrade a whole site and prove nothing changed',
            'pitch' => 'Upgrade a whole site from TYPO3 v12 or v13 to 14.3 LTS in a local DDEV copy, and prove that visitors see no difference.',
            'skills' => [
                [
                    'name' => 'typo3-upgrade-run',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'rocket',
                    'title' => 'Run the whole upgrade',
                    'line' => 'Upgrades a site from v12 or v13 to 14.3 LTS and proves that visitors see no difference.',
                    'headline' => 'typo3-upgrade-run: the whole upgrade, proven',
                    'can' => 'Upgrade a whole site from TYPO3 v12 or v13 to 14.3 LTS in a local DDEV copy. The run plans the work, migrates in rungs and closes only when visitors see no difference.',
                    'cool' => 'A sealed graph of 72 nodes and 206 edges decides each next step, and every retry has a limit. Steps that do not depend on each other run in parallel.',
                    'why' => 'It keeps two promises apart: first the same site on the new version, then improvements such as structured data and WebMCP. It never deploys to staging or live.',
                    'say' => 'Update this TYPO3 12 project to 14.3 in DDEV and make sure the site looks exactly the same afterwards.',
                ],
                [
                    'name' => 'typo3-upgrade-intake',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'list-checks',
                    'title' => 'Check the project before any change',
                    'line' => 'A read-only preflight of repository, runtime, database, files, extensions and risks.',
                    'headline' => 'typo3-upgrade-intake: know what you upgrade',
                    'can' => 'Prove which repository, DDEV project, database, files and site configuration the upgrade is about, and check every installed extension for v14.',
                    'cool' => 'It never changes the site. A wrong project, a stale dataset or a missing sitemap becomes a named finding with its own recovery step.',
                    'why' => 'An upgrade that starts from the wrong database proves nothing. Intake settles that once, before the first change.',
                    'say' => 'Start the discovery for our v12-to-v14 site update and tell me which local extensions, sites and missing sitemaps change the plan.',
                ],
                [
                    'name' => 'typo3-upgrade-baseline',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'history',
                    'title' => 'Freeze the site before the upgrade',
                    'line' => 'Captures responses, HTML and screenshots of the untouched site and seals them as Baseline A.',
                    'headline' => 'typo3-upgrade-baseline: the site as it was',
                    'can' => 'Capture the HTTP responses, the cleaned HTML and screenshots of every URL before the first change, then seal them as Baseline A.',
                    'cool' => 'Fresh browsers capture the untouched site twice, and both captures must match at zero differences. Carousels, lazy images and cookie banners are stabilised first.',
                    'why' => 'A comparison is only as good as its reference. A baseline that already flickers would hide real regressions later.',
                    'say' => 'Intake passed for the TYPO3 update. Build the untouched before-state proof, including our cookie banner and homepage carousel.',
                ],
                [
                    'name' => 'typo3-upgrade-closure',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'badge-check',
                    'title' => 'Prove that nothing changed',
                    'line' => 'Compares the upgraded site with the baseline and closes only when nothing unexplained changed.',
                    'headline' => 'typo3-upgrade-closure: proof before sign-off',
                    'can' => 'Compare every URL over HTTP, markup and pixels, check the interactions and the backend, and write the closure certificate a person accepts.',
                    'cool' => '13 closure checks, from pixels at strict zero to Lighthouse and axe. Green screenshots do not count when nobody operated the cookie dialog or the carousel.',
                    'why' => 'It never accepts an unexplained difference and never refreshes the baseline to make one disappear.',
                    'say' => 'The TYPO3 v14 target is stable. Prove HTTP, markup, pixels, backend operations and our key interactions against the frozen source.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function upgradePlanning(): array
    {
        return [
            'slug' => 'upgrade-planning',
            'title' => 'Upgrade planning and migration',
            'abstract' => 'Estimate the effort of a TYPO3 upgrade, migrate step by step with a rollback point, and learn from every run.',
            'description' => 'Four skills for TYPO3 upgrades: an effort model, the upgrade of a deployed project, migration steps with rollback points and reviews of past runs.',
            'header' => 'Know the effort, then migrate step by step',
            'pitch' => 'Estimate the effort before you start, migrate one cause at a time with a rollback point, and learn from every run.',
            'skills' => [
                [
                    'name' => 'typo3-upgrade-effort-model',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-upgrade-effort-model-skill',
                    'superfeature' => false,
                    'icon' => 'chart',
                    'title' => 'Estimate the effort',
                    'line' => 'Estimates an LTS upgrade per extension, with risk multipliers for the v14 breaking changes.',
                    'headline' => 'typo3-upgrade-effort-model: estimate first',
                    'can' => 'Estimate the hours of an upgrade to 14.3 LTS per extension, from baselines per version path and a seven-phase assessment.',
                    'cool' => 'It names what makes v14 hard: the removed TypoScriptFrontendController counts × 1.3, the stricter ViewHelpers of Fluid 5 × 1.2. Your own calibration factor stays yours.',
                    'why' => 'A number per extension is easier to plan with than a gut feeling, and it shows how much Rector saves: 15 to 20 per cent.',
                    'say' => 'Estimate the effort to upgrade this project from TYPO3 v13 to 14.3 LTS, extension by extension.',
                ],
                [
                    'name' => 'typo3-project-upgrade',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-project-upgrade-skill',
                    'superfeature' => false,
                    'icon' => 'settings',
                    'title' => 'Upgrade a deployed project',
                    'line' => 'Moves a deployed project to a new LTS: site configuration, TypoScript, templates, Docker and database.',
                    'headline' => 'typo3-project-upgrade: the project around the code',
                    'can' => 'Move a deployed project to 14.3 LTS in phases: inventory, infrastructure, site sets instead of sys_template, visual parity and a review.',
                    'cool' => 'It knows the traps after the switch: a leftover sys_template record that overrides site sets, or a container without ImageMagick serving full-size images.',
                    'why' => 'It covers the project; typo3-extension-upgrade covers extension code. typo3-upgrade-run asks it for advice during a whole-site run.',
                    'say' => 'After migrating to site sets in TYPO3 v13, I still get “No page configured for type=0”. What’s wrong?',
                ],
                [
                    'name' => 'typo3-upgrade-migration',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'git-branch',
                    'title' => 'Migrate one step at a time',
                    'line' => 'Carries out one migration step behind a rollback point: Composer, Rector, Fractor, database or data.',
                    'headline' => 'typo3-upgrade-migration: one cause per step',
                    'can' => 'Carry out one step of the upgrade graph: the 13.4 rung, a Composer blocker, a Rector or Fractor pass, or a data migration.',
                    'cool' => 'Each step changes one cause and starts from a rollback anchor or a snapshot. A Rector pass is done only when a second pass changes nothing.',
                    'why' => 'Small, reversible steps make a failed attempt cheap, and the graph decides how often a step may be retried.',
                    'say' => 'Handle the next authorised data-migration node for the old list_type records before the new CType registration lands.',
                ],
                [
                    'name' => 'typo3-upgrade-retrospective',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'book-open',
                    'title' => 'Learn from every run',
                    'line' => 'Reviews past or unfinished upgrade runs: what was done, what is open and what went wrong.',
                    'headline' => 'typo3-upgrade-retrospective: lessons from real runs',
                    'can' => 'Review past or unfinished upgrade runs across projects: what was asked, changed, solved, left open or too slow.',
                    'cool' => 'Each finding becomes a row of problem, cause, fix and control, with proposed skill and eval changes. It only reads, unless you ask for more.',
                    'why' => 'Git may say a branch is done while the run says otherwise. The retrospective shows what is actually proven, and never rebuilds a pass from Git history.',
                    'say' => 'Review our last six TYPO3 upgrade tasks: what was requested, what took too long, and which failures belong in the process.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function codeMigration(): array
    {
        return [
            'slug' => 'code-migration',
            'title' => 'Automated code migration',
            'abstract' => 'Tools move extension code to TYPO3 v14: Rector for PHP, Fractor for TypoScript, Fluid and FlexForms, rolled out in batches.',
            'description' => 'Four skills move TYPO3 extension code to v14 with tools: one extension at a time, Rector for PHP, Fractor for the other files, and batches.',
            'header' => 'Let the tools move your extension code',
            'pitch' => 'Rector rewrites the PHP, Fractor the TypoScript, Fluid and FlexForms, and one change rolls out to many extensions in reviewable batches.',
            'skills' => [
                [
                    'name' => 'typo3-extension-upgrade',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-extension-upgrade-skill',
                    'superfeature' => false,
                    'icon' => 'component',
                    'title' => 'Bring one extension to v14',
                    'line' => 'Makes one extension work with a newer TYPO3 LTS, with Extension Scanner, Rector, Fractor and PHPStan.',
                    'headline' => 'typo3-extension-upgrade: one extension, done properly',
                    'can' => 'Upgrade one extension to the current LTS, or keep one codebase working on two TYPO3 versions.',
                    'cool' => 'Done means exit code 0 on every version line the constraint names. Skipped tests and a commit with --no-verify do not count.',
                    'why' => 'It treats a failing commit hook as a check to fix, not an obstacle to skip.',
                    'say' => 'Make this extension work with TYPO3 v14.3 and keep it running on v13.4.',
                ],
                [
                    'name' => 'typo3-rector',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'code',
                    'title' => 'Fix deprecated PHP with Rector',
                    'line' => 'Fixes deprecated PHP calls with Rector: dry run, review the diff, apply, dry run again.',
                    'headline' => 'typo3-rector: deprecated PHP, fixed by rules',
                    'can' => 'Set up Rector for the v12 or v13 to v14 path and apply it: ViewFactory, Extbase responses, PSR-14 events, backend modules and TCA.',
                    'cool' => 'Dry run, review the diff, apply, dry run again. It stops after three applied passes, or earlier when two passes change nothing.',
                    'why' => 'Rules catch namespace moves and signature changes that hand edits miss, and the second pass catches code the first one generated.',
                    'say' => 'Automatically fix the deprecated PHP calls in Classes/ for TYPO3 v14.',
                ],
                [
                    'name' => 'typo3-fractor',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'file-code',
                    'title' => 'Fix TypoScript, Fluid and FlexForms',
                    'line' => 'Rewrites TypoScript, FlexForms, Fluid, YAML and XLIFF towards v14 with Fractor.',
                    'headline' => 'typo3-fractor: the files Rector leaves alone',
                    'can' => 'Rewrite TypoScript conditions, FlexForm XML, Fluid templates, YAML, XLIFF and .htaccess files towards v14.',
                    'cool' => 'The cumulative UP_TO_TYPO3_14 set applies every older rule set on the way to v14.',
                    'why' => 'FlexForm, TypoScript and Fluid patterns are easy to miss in a review. Fractor runs on a checkout under version control, so every change can be reverted.',
                    'say' => 'I ran Rector and it left every .yaml and .typoscript file untouched. What handles those?',
                ],
                [
                    'name' => 'typo3-batch',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'layers',
                    'title' => 'Roll one change out everywhere',
                    'line' => 'Applies one repeated change to many extensions at once, in reviewable batches.',
                    'headline' => 'typo3-batch: one change, many extensions',
                    'can' => 'Find every extension in packages/ that still calls a removed API, such as HashService, and fix them together.',
                    'cool' => 'Adapted from Boris Cherny’s /batch skill for Claude Code, it splits the work by root cause into units you can test on their own. You approve the numbered plan first.',
                    'why' => 'A 500-file commit cannot be reviewed. Batches can, and each one is verified before the next.',
                    'say' => 'Inventory which of our extensions still use the removed HashService and fix them together.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function contentModelling(): array
    {
        return [
            'slug' => 'content-modelling',
            'title' => 'Content modelling and data',
            'abstract' => 'Content types in YAML, records written safely from PHP, labels and translations that work, and news tagged in bulk.',
            'description' => 'Four TYPO3 skills for content and data: Content Blocks in YAML, DataHandler imports, XLIFF and ICU labels, and bulk tags for EXT:news.',
            'header' => 'Content types in YAML, records written safely',
            'pitch' => 'Define content types in one YAML file each, import and translate records the way TYPO3 expects, and tag news in bulk.',
            'skills' => [
                [
                    'name' => 'typo3-content-blocks',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'component',
                    'title' => 'Define content types in YAML',
                    'line' => 'Defines content elements, records and page types in one config.yaml each.',
                    'headline' => 'typo3-content-blocks: one file per content type',
                    'can' => 'Create content elements, records, page types and file types, each from one config.yaml, and migrate Mask or fluid_styled_content with the records kept.',
                    'cool' => 'Content Blocks generates TCA, SQL, TypoScript, backend forms and labels from that one file. The skill was spot-checked on Content Blocks 2.4.10 with TYPO3 v14.3.9.',
                    'why' => 'Identifiers stay stable through a migration, because a renamed identifier leaves records that look empty.',
                    'say' => 'I need a teaser element with a headline, an image and a link. Set up the definition for it.',
                ],
                [
                    'name' => 'typo3-datahandler',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'database',
                    'title' => 'Write records from PHP safely',
                    'line' => 'Creates, changes, translates and deletes records from PHP through DataHandler.',
                    'headline' => 'typo3-datahandler: imports that keep references intact',
                    'can' => 'Create hundreds of pages or records from JSON, CSV or another system, and move, localise or delete them from PHP.',
                    'cool' => 'Everything goes through DataHandler, never raw SQL, so the reference index, history, workspace versions and caches stay right.',
                    'why' => 'Raw INSERTs look fine until the reference index is stale or imported records belong to nobody. The TYPO3 way avoids both.',
                    'say' => 'I need to create 500 pages programmatically from a JSON file with the parent references intact.',
                ],
                [
                    'name' => 'typo3-translations',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'globe',
                    'title' => 'Fix labels and translation files',
                    'line' => 'Fixes labels and XLIFF files, including plural forms with ICU MessageFormat.',
                    'headline' => 'typo3-translations: labels that show the right text',
                    'can' => 'Fix label files in XLIFF 1.2 and 2.0, plural forms with ICU MessageFormat, LLL references and the translation domains of v14.',
                    'cool' => 'It starts from a layout that works on v13 and v14, and moves to XLIFF 2.0 only when the extension is v14-only.',
                    'why' => 'A raw label key on screen or duplicate ids after a merge are small bugs that editors see first.',
                    'say' => 'The backend shows the raw label key instead of the actual text.',
                ],
                [
                    'name' => 'typo3-news-tags',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'tag',
                    'title' => 'Tag thousands of news at once',
                    'line' => 'Generates thematic tags for EXT:news and assigns them to existing articles by keyword.',
                    'headline' => 'typo3-news-tags: a tag catalogue for your archive',
                    'can' => 'Design a generic tag catalogue and assign tags to hundreds or tens of thousands of existing news records by keyword.',
                    'cool' => 'It builds the console command for the assignment, and can add b13/tag as a separate tag system for the whole site.',
                    'why' => 'It is made for archives too large to tag by hand, on TYPO3 v14.3 with georgringer/news 14.',
                    'say' => 'We have 8,000 news articles with no tags at all. Can we generate and assign them automatically?',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function editorExperience(): array
    {
        return [
            'slug' => 'editor-experience',
            'title' => 'Editor experience',
            'abstract' => 'Inline editing on the page, publishing when you are ready, card and board views of records, and a well-configured rich-text editor.',
            'description' => 'Four TYPO3 skills for editors: the Visual Editor, workspaces, card and board views in the Records module, and CKEditor 5 presets and plugins.',
            'header' => 'Editing that fits how editors work',
            'pitch' => 'Editors type on the page, publish when they are ready, see records as cards and boards, and get a well-configured rich-text editor.',
            'skills' => [
                [
                    'name' => 'typo3-visual-editor',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'monitor',
                    'title' => 'Let editors type on the page',
                    'line' => 'Prepares templates so editors click into the page and type, with every output traceable to its record.',
                    'headline' => 'typo3-visual-editor: editing on the page',
                    'can' => 'Install the Visual Editor, move page columns to content areas and make text fields editable on the page, field by field.',
                    'cool' => 'It uses the core ViewHelpers f:render.text and f:render.contentArea, which stay useful even without the extension.',
                    'why' => 'Readiness is tested as a restricted editor, with translations and a workspace, never as an administrator.',
                    'say' => 'Editors want to click into the page and type, instead of opening backend forms.',
                ],
                [
                    'name' => 'typo3-workspaces',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'calendar',
                    'title' => 'Hold finished work until launch',
                    'line' => 'Prepares content in a workspace, then reviews and publishes it, with the file traps named.',
                    'headline' => 'typo3-workspaces: publish when you are ready',
                    'can' => 'Prepare content for a launch date in a workspace, review and publish it in stages, and query records so drafts resolve in your code.',
                    'cool' => 'It says plainly what workspaces do not cover: files are not versioned, so an overwritten file is live everywhere at once.',
                    'why' => 'A preview that shows the live page, or images that lose their references on publishing, are the usual surprises. The skill covers both.',
                    'say' => 'Editors need to prepare content for a launch date without it going live early.',
                ],
                [
                    'name' => 'typo3-records-list-types',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'layout-grid',
                    'title' => 'Show records as cards and boards',
                    'line' => 'Shows records as grid, teaser, timeline or board views in the Records module, set in TSconfig.',
                    'headline' => 'typo3-records-list-types: cards instead of rows',
                    'can' => 'Give each record type a grid, compact, teaser, timeline or board view in the Records module, with its own fields and two to six columns.',
                    'cool' => 'Views, fields and templates live in the Page TSconfig, Fluid and CSS of your sitepackage. Nothing is patched.',
                    'why' => 'Editors find records by their thumbnail, not by a row of text. Keyboard drag and drop is tested by hand and by tools.',
                    'say' => 'The Records module shows a plain table. Can I get cards with thumbnails instead?',
                ],
                [
                    'name' => 'typo3-ckeditor5',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-ckeditor5-skill',
                    'superfeature' => false,
                    'icon' => 'file-text',
                    'title' => 'Set up the rich-text editor',
                    'line' => 'Configures RTE presets and builds CKEditor 5 plugins for TYPO3.',
                    'headline' => 'typo3-ckeditor5: the rich-text editor, set up well',
                    'can' => 'Configure RTE presets and toolbars in YAML, build custom CKEditor 5 plugins and migrate from CKEditor 4.',
                    'cool' => 'It knows the versions: TYPO3 v14.3 bundles CKEditor 5 v47, which follows light and dark mode by default.',
                    'why' => 'CKEditor 5 is a complete rewrite with no compatibility layer for version 4, so old plugins need a plan.',
                    'say' => 'Migrate our CKEditor 4 plugin to CKEditor 5 and add it to the toolbar of our RTE preset.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function designSystem(): array
    {
        return [
            'slug' => 'design-system',
            'title' => 'Design system and frontend',
            'abstract' => 'Design tokens become Fluid content elements, the site gets a living style guide, a Vite build and icons in the v14 style.',
            'description' => 'Four TYPO3 skills for the frontend: shadcn/ui content elements, a living design-system page, a Vite build and extension icons in the v14 style.',
            'header' => 'From design tokens to finished Fluid elements',
            'pitch' => 'Design tokens become Fluid content elements, the site gets a living style guide, and assets and icons are built the v14 way.',
            'skills' => [
                [
                    'name' => 'typo3-shadcn-content-elements',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'layout-grid',
                    'title' => 'Turn shadcn/ui into elements',
                    'line' => 'Turns shadcn/ui presets into Fluid content elements with semantic tokens, previews and seed data.',
                    'headline' => 'typo3-shadcn-content-elements: shadcn/ui as Fluid',
                    'can' => 'Audit your elements, turn a shadcn/ui preset into theme tokens, and move templates from fixed colours to semantic tokens, with backend previews and icons.',
                    'cool' => 'No React at runtime: tokens and class contracts are ported to Fluid, and small vanilla controllers handle the interaction.',
                    'why' => 'Elements without raw colour values follow every preset and dark mode, and editors recognise them by their previews.',
                    'say' => 'These elements have hardcoded hex colours everywhere. Move them to semantic tokens.',
                ],
                [
                    'name' => 'typo3-design-system-page',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'book-open',
                    'title' => 'Publish a living style guide',
                    'line' => 'Builds an accessible design-system page in your site, with design.md, tokens.json and a one-page PDF.',
                    'headline' => 'typo3-design-system-page: the style guide, live',
                    'can' => 'Audit the site and its brand assets, then build an overview of colours, type, components and imagery. It also writes design.md, tokens.json and a one-page PDF.',
                    'cool' => 'The values come from the live site, with evidence for every contrast claim. The page, the source files and the PDF stay in sync.',
                    'why' => 'It asks which installation and which page to use before it writes, so nothing lands on a site you did not choose.',
                    'say' => 'Build a living style-guide page in our TYPO3 site with the real colours, ramps, fonts, spacing and component examples.',
                ],
                [
                    'name' => 'typo3-vite',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-vite-skill',
                    'superfeature' => false,
                    'icon' => 'zap',
                    'title' => 'Build the assets with Vite',
                    'line' => 'Sets up Vite 7 for TYPO3 sitepackages, with an entry per content element.',
                    'headline' => 'typo3-vite: a frontend build for v14',
                    'can' => 'Configure Vite 7 with praetorius/vite-asset-collector: SCSS with Bootstrap 5.3 theming, local fonts, SVG optimisation and PostCSS.',
                    'cool' => 'Each content element gets its own entrypoint, so a page loads only the CSS and JavaScript of the elements it shows.',
                    'why' => 'TYPO3 v14 removed the core’s own concatenation and compression of frontend assets, so a build tool now does that work.',
                    'say' => 'Set up Vite for our TYPO3 v14 sitepackage with one entrypoint per content element and local fonts.',
                ],
                [
                    'name' => 'typo3-icon14',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'file-image',
                    'title' => 'Draw icons in the v14 style',
                    'line' => 'Redraws and migrates extension icons to the v14 line style, for light and dark mode.',
                    'headline' => 'typo3-icon14: icons that fit the v14 backend',
                    'can' => 'Migrate old extension icons, design new ones for the v14 backend, and update Configuration/Icons.php and every place that uses them.',
                    'cool' => 'It starts from meaning, not shapes, and keeps module, plugin, record and action icons distinct.',
                    'why' => 'Heavy, filled icons look foreign next to the v14 core, and a module icon that vanishes in dark mode is a bug.',
                    'say' => 'Our extension icons look heavy and filled next to the v14 core ones. Can you redraw them?',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function v14Development(): array
    {
        return [
            'slug' => 'v14-development',
            'title' => 'TYPO3 v14 development',
            'abstract' => 'Correct TYPO3 v14 code, TypoScript and backend Web Components, built and checked on a local DDEV instance.',
            'description' => 'Four skills for TYPO3 v14 development: the v14 API reference, TypoScript and TSconfig, backend Web Components with Lit, and DDEV.',
            'header' => 'Write TYPO3 v14 code the way v14 expects',
            'pitch' => 'Look up the v14 way before writing it, check TypoScript against its reference, build backend components with Lit, and run it all in DDEV.',
            'skills' => [
                [
                    'name' => 'typo3-v14-reference',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'book-open',
                    'title' => 'Look up the v14 way',
                    'line' => 'Shows how v14 code is written: controllers, ViewFactory, PSR-14 events, TCA, QueryBuilder and CLI.',
                    'headline' => 'typo3-v14-reference: the API as v14 has it',
                    'can' => 'Look up how v14 code is written: controllers, views with ViewFactory, the PSR-14 event that replaced a hook, backend modules, TCA, QueryBuilder and CLI commands.',
                    'cool' => 'It checks every class, method, event and configuration key against the installed v14 source, because memory is often wrong after the v14 removals.',
                    'why' => 'Agents often mix up versions. This skill gives them the v14 answer, and leaves whole upgrades to typo3-upgrade-run.',
                    'say' => 'Which PSR-14 event replaces the old post-processing hook in v14, and what is its namespace?',
                ],
                [
                    'name' => 'typo3-typoscript-ref',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-typoscript-ref-skill',
                    'superfeature' => false,
                    'icon' => 'file-code',
                    'title' => 'Write and review TypoScript',
                    'line' => 'A version-aware reference for TypoScript, TSconfig and Fluid, with a local lookup.',
                    'headline' => 'typo3-typoscript-ref: TypoScript, looked up locally',
                    'can' => 'Write, review and debug TypoScript, TSconfig and Fluid templates, including the changes from v13 to v14 such as @import and the removed getTSFE() condition.',
                    'cool' => 'A local lookup script answers from the reference and adds deprecation warnings in review mode.',
                    'why' => 'TypoScript keys are easy to misremember. A lookup beats a guess, and the review names what v14 removed.',
                    'say' => 'How do I create a navigation menu in TypoScript? Show me the recipe.',
                ],
                [
                    'name' => 'typo3-webcomponents',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'component',
                    'title' => 'Build backend components with Lit',
                    'line' => 'Builds v14 backend Web Components with Lit, import maps and FormEngine integration.',
                    'headline' => 'typo3-webcomponents: backend JavaScript, the v14 way',
                    'can' => 'Build, review or migrate backend Web Components with Lit: ES module import maps, f:asset.module, backend modules, FormEngine and AJAX routes.',
                    'cool' => 'The v14 APIs it names were checked against TYPO3 v14.3.9, and it adds fallbacks for v12 or v13 only when you ask.',
                    'why' => 'It turns jQuery-era backend code into standard Web Components.',
                    'say' => 'Build a reusable widget for the backend with Lit.',
                ],
                [
                    'name' => 'typo3-ddev',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-ddev-skill',
                    'superfeature' => false,
                    'icon' => 'settings',
                    'title' => 'Run TYPO3 locally with DDEV',
                    'line' => 'Starts and reaches a local TYPO3 instance with DDEV, for one version or several side by side.',
                    'headline' => 'typo3-ddev: a running TYPO3, locally',
                    'can' => 'Set up DDEV for an extension, install TYPO3 v14.3 or several versions side by side, and choose MariaDB, PostgreSQL, MySQL or SQLite.',
                    'cool' => 'It never guesses a URL: it reads the project name from .ddev/config.yaml and builds the address from it.',
                    'why' => 'Most other skills need a running instance to check their work. This one provides it, and also covers containers without DDEV.',
                    'say' => 'I need a TYPO3 v14 instance for this extension. Set it up with DDEV.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function accessibility(): array
    {
        return [
            'slug' => 'accessibility',
            'title' => 'Accessibility',
            'abstract' => 'WCAG 2.2 AA scans traced to the templates behind them, accessible markup patterns, and a draft statement for people to check.',
            'description' => 'Three TYPO3 skills for accessibility: WCAG 2.2 AA scans with Playwright and axe-core, accessible Fluid markup, and tested frontend patterns.',
            'header' => 'Find accessibility barriers and fix them in templates',
            'pitch' => 'Scan against WCAG 2.2 AA with findings traced to their templates, fix markup at the source, and draft a statement for review.',
            'skills' => [
                [
                    'name' => 'typo3-wcag22-aa-agentic',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'accessibility',
                    'title' => 'Scan against WCAG 2.2 AA',
                    'line' => 'Scans with Playwright and axe-core and names the Fluid template behind each finding.',
                    'headline' => 'typo3-wcag22-aa-agentic: scans that name the template',
                    'can' => 'Find every URL, scan it in report-only mode first, write a report per page and draft an accessibility statement for a person to check.',
                    'cool' => 'Each violation is mapped to the Fluid template that produced it, and a CI baseline fails pull requests that add new ones.',
                    'why' => 'Automated scans are evidence, not conformance. Alt text, keyboard logic and screen-reader clarity go to a short review by people.',
                    'say' => 'Scan our DDEV site at https://acme.ddev.site and tell me everything that fails WCAG 2.2 AA, page by page.',
                ],
                [
                    'name' => 'typo3-accessibility',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'check-circle',
                    'title' => 'Write accessible markup',
                    'line' => 'Accessible patterns for Fluid, PHP and JavaScript, with a checklist to verify by hand.',
                    'headline' => 'typo3-accessibility: the markup, fixed at the source',
                    'can' => 'Fix skip links, landmarks, headings, focus, ARIA for accordions and modals, form labels and error messages in Fluid templates, PHP helpers and JavaScript.',
                    'cool' => 'It prefers native HTML over ARIA and uses TYPO3’s own ViewHelpers first. A go-live checklist covers what only a person can verify.',
                    'why' => 'When a screen reader does not announce an accordion as expandable, this skill gives you the markup that fixes it.',
                    'say' => 'Our accordion content element is just nested divs with a click handler. NVDA does not announce it as expandable. What markup should it be?',
                ],
                [
                    'name' => 'typo3-a11y',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-a11y-skill',
                    'superfeature' => false,
                    'icon' => 'users',
                    'title' => 'Use proven frontend patterns',
                    'line' => 'Accessible patterns for navigation, filters, tables, sticky headers and language switchers.',
                    'headline' => 'typo3-a11y: patterns for common components',
                    'can' => 'Build accessible navigation, forms, filters, tables, skip links and disclosure widgets, from the sticky header to the language switcher.',
                    'cool' => 'It names what v14 already does better, such as the backend modal on the native dialog element, with a focus trap and Escape to close.',
                    'why' => 'It works to WCAG 2.2 AA for sitepackage frontends, with a checklist for every content element.',
                    'say' => 'Review the main navigation and the filter form of our sitepackage for accessibility.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function searchSeoAgents(): array
    {
        return [
            'slug' => 'search-seo-agents',
            'title' => 'Search, SEO and agents',
            'abstract' => 'Be found by Google, by your own site search and by AI agents: SEO, structured data, Apache Solr and WebMCP.',
            'description' => 'Four TYPO3 skills for being found: sitemaps and hreflang, schema.org JSON-LD, Apache Solr site search and typed WebMCP tools for browser agents.',
            'header' => 'Found by search engines, site search and agents',
            'pitch' => 'Be found by Google, by your own site search and by AI agents, with every statement in the markup taken from the visible page.',
            'skills' => [
                [
                    'name' => 'typo3-seo',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'search',
                    'title' => 'Get the right pages indexed',
                    'line' => 'Sitemaps, canonical URLs, hreflang, meta tags, Open Graph and robots rules with EXT:seo.',
                    'headline' => 'typo3-seo: the right page in the right language',
                    'can' => 'Fix sitemaps, canonical URLs, hreflang across languages, meta tags, social previews and robots rules, and keep a staging site out of the index.',
                    'cool' => 'It uses EXT:seo and core features before custom output, checked against typo3/cms-seo 14.3.9.',
                    'why' => 'It starts where the symptoms show: language versions mixed up in Google, or a sitemap with only the home page in it.',
                    'say' => 'Google is mixing up our German and Italian pages in the results. Is hreflang set up right?',
                ],
                [
                    'name' => 'typo3-structured-data',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'code',
                    'title' => 'Describe pages with JSON-LD',
                    'line' => 'Builds schema.org JSON-LD from the records that render the page, and keeps it through an upgrade.',
                    'headline' => 'typo3-structured-data: JSON-LD from visible content',
                    'can' => 'Audit the existing markup, then describe businesses, articles, products, events and jobs where the page shows them, EXT:news detail pages included.',
                    'cool' => 'One connected graph per page with stable @id values, built only from visible, current content. It never invents a price or an FAQ answer.',
                    'why' => 'Valid markup makes a page eligible for search features; whether they appear is the search engine’s decision. The skill says so.',
                    'say' => 'Add LocalBusiness and opening-hours structured data to this TYPO3 company website.',
                ],
                [
                    'name' => 'typo3-solr',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'database',
                    'title' => 'Make the site search work',
                    'line' => 'Runs and debugs Apache Solr search: indexing, facets, suggest, PDFs and stuck queues.',
                    'headline' => 'typo3-solr: site search that finds things',
                    'can' => 'Find out why a new page type gets no results, add facets for topic and year, set up suggest, and search PDFs with Tika.',
                    'cool' => 'It treats EXT:solr, the Solr server, the configset and Tika as one stack. For TYPO3 v14.3, that is EXT:solr 14.0.x with Solr 10.0.0.',
                    'why' => 'A Solr server that accepts documents can still drop unknown fields. The skill checks compatibility instead of assuming it.',
                    'say' => 'The indexing queue has been stuck at 12,000 items since yesterday.',
                ],
                [
                    'name' => 'typo3-webmcp',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'globe',
                    'title' => 'Offer typed tools to agents',
                    'line' => 'Makes search, navigation and forms usable as typed WebMCP tools for browser agents.',
                    'headline' => 'typo3-webmcp: typed browser tools for agents',
                    'can' => 'Offer search, navigation and the facts of the current page as small, typed tools, and prepare forms that a person then submits.',
                    'cool' => 'A browser without WebMCP gets the same working site. Tools are same-origin by default and never accept arbitrary URLs.',
                    'why' => 'WebMCP is a proposal, not yet a standard. The skill adds it as progressive enhancement, so nothing breaks for people.',
                    'say' => 'Make the search and the contact form on this TYPO3 v14 website available to browser agents through WebMCP.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function securityOperations(): array
    {
        return [
            'slug' => 'security-operations',
            'title' => 'Security and operations',
            'abstract' => 'Harden the installation, give editors exactly the rights they need, plan the scheduled jobs and check the project setup.',
            'description' => 'Four TYPO3 skills for running a site: security hardening, verified editor rights, the right Scheduler tasks and a conformance check of the project.',
            'header' => 'A hardened site with exact editor rights',
            'pitch' => 'Harden the installation, give every editor exactly the rights they need, run the right scheduled jobs and check the project repository.',
            'skills' => [
                [
                    'name' => 'typo3-security',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'shield-check',
                    'title' => 'Harden the installation',
                    'line' => 'Hardens a v14 installation: trusted hosts, the Install Tool, two-factor sign-in, CSP and safe queries.',
                    'headline' => 'typo3-security: the must-dos before go-live',
                    'can' => 'Lock down the configuration, trusted hosts, file permissions and the Install Tool, require two-factor authentication, add a CSP and fix unsafe queries.',
                    'cool' => 'For an assessment it inspects and reports without changing anything. Its configuration keys were checked against TYPO3 v14.3.9.',
                    'why' => 'It fixes the classic findings the TYPO3 way: user input concatenated into SQL becomes a QueryBuilder query with bound values.',
                    'say' => 'We go live next week. Harden the installation: what are the must-dos?',
                ],
                [
                    'name' => 'typo3-backend-rights',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => true,
                    'icon' => 'lock',
                    'title' => 'Give editors exact rights',
                    'line' => 'Builds and audits editor groups, page and language rights, MFA and login branding.',
                    'headline' => 'typo3-backend-rights: editor roles you can verify',
                    'can' => 'Audit the live rights, then build editor groups with content types, page and language rights, modules, mounts, MFA and login branding.',
                    'cool' => 'A separate, working administrator is kept before anything changes, and group structure, membership and page ownership are three separate approvals.',
                    'why' => 'An empty allow-list for content types is a defect, not “allow everything”. The skill writes rights through scripts, dry run first.',
                    'say' => 'Create one main backend editor role with simple subgroups that can manage all normal content, news records, and files.',
                ],
                [
                    'name' => 'typo3-scheduler-jobs',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'clock',
                    'title' => 'Plan the scheduled jobs',
                    'line' => 'Plans and checks the Scheduler tasks an installation needs, with groups and safe intervals.',
                    'headline' => 'typo3-scheduler-jobs: the jobs your site needs',
                    'can' => 'Audit an installation, configure the maintenance tasks it needs, group them, set safe intervals, and add a Solr queue worker for each site root.',
                    'cool' => 'It derives the smallest complete set of tasks from the installation itself, never from another site’s rows. Running them automatically stays your choice.',
                    'why' => 'It finds jobs that are missing, late or ungrouped, and repairs task rows left without a type after a v14 update.',
                    'say' => 'Audit this TYPO3 v14 installation and add the maintenance jobs it actually needs to the backend Scheduler.',
                ],
                [
                    'name' => 'typo3-site-conformance',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-site-conformance-skill',
                    'superfeature' => false,
                    'icon' => 'list-checks',
                    'title' => 'Check the project repository',
                    'line' => 'Scores a deployable project with Docker or Compose against a gold standard.',
                    'headline' => 'typo3-site-conformance: the project, scored',
                    'can' => 'Score and harden a deployable site repository: structure, containers, CI, deployment, dependencies, security and documentation.',
                    'cool' => 'Seven rule families, from images pinned by digest and an SBOM in CI to settings files without secrets.',
                    'why' => 'It is the project counterpart to typo3-conformance, which checks single extensions.',
                    'say' => 'Check this TYPO3 project repository with its compose.yaml against the site conformance gold standard.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function testingQuality(): array
    {
        return [
            'slug' => 'testing-quality',
            'title' => 'Testing and code quality',
            'abstract' => 'Bugs become failing tests, journeys run in a real browser, and extension code is scored and cleaned up.',
            'description' => 'Four TYPO3 skills for quality: tests that reproduce a bug first, Playwright journeys, an extension conformance score and a clean-up pass.',
            'header' => 'Turn bugs into tests and keep code clean',
            'pitch' => 'A bug report becomes a failing test, journeys run in a real browser, and extension code is checked and cleaned without changing what it does.',
            'skills' => [
                [
                    'name' => 'typo3-testing',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-testing-skill',
                    'superfeature' => false,
                    'icon' => 'badge-check',
                    'title' => 'Prove a bug with a test',
                    'line' => 'Unit, functional and end-to-end tests, and a reported bug reproduced as a failing test first.',
                    'headline' => 'typo3-testing: the failing test comes first',
                    'can' => 'Set up the test infrastructure and write unit, functional, architecture, end-to-end and mutation tests, with a CI matrix across TYPO3 versions.',
                    'cool' => 'A bug report is a test that does not exist yet: the skill writes it from the report and watches it fail for that reason.',
                    'why' => 'A test written after the fix proves only that the code does what it does.',
                    'say' => 'Reproduce this reported bug as a failing functional test before we fix it.',
                ],
                [
                    'name' => 'typo3-playwright',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'monitor',
                    'title' => 'Test journeys in a browser',
                    'line' => 'Builds and repairs Playwright tests for visitor and editor journeys, with isolated fixtures.',
                    'headline' => 'typo3-playwright: journeys in a real browser',
                    'can' => 'Turn a component inventory into Playwright tests for visitor journeys, AJAX widgets, CKEditor dialogs and editor save-and-reopen flows.',
                    'cool' => 'It reuses the project’s Playwright setup instead of installing a second framework, and counts which components are covered.',
                    'why' => 'A unit test cannot click a filter or save a link dialog. A browser test can, and its trace shows where it failed.',
                    'say' => 'Our TYPO3 editors cannot insert record links. Add a browser test that opens the dialog and saves the edited text.',
                ],
                [
                    'name' => 'typo3-conformance',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-conformance-skill',
                    'superfeature' => false,
                    'icon' => 'list-checks',
                    'title' => 'Score an extension',
                    'line' => 'Checks an extension against TYPO3 standards: versions, coding standards, deprecations and TER readiness.',
                    'headline' => 'typo3-conformance: an extension, scored',
                    'can' => 'Check which TYPO3 versions an extension claims, whether composer.json and ext_emconf.php agree, and what needs work before a release.',
                    'cool' => 'A score out of 100 in five areas of 20 points each, and critical issues block a release whatever the score.',
                    'why' => 'A version bump that leaves half the files behind is easy to miss. The scored report shows it.',
                    'say' => 'Review this TYPO3 extension and tell me what needs attention before it supports v14.3.',
                ],
                [
                    'name' => 'typo3-simplify',
                    'owner' => 'Anthropic',
                    'upstream' => 'https://github.com/anthropics/claude-plugins-official/tree/main/plugins/code-simplifier',
                    'superfeature' => false,
                    'icon' => 'sparkles',
                    'title' => 'Clean up, same behaviour',
                    'line' => 'Cleans up extension code for clarity and v14 patterns without changing what it does.',
                    'headline' => 'typo3-simplify: clearer code, same behaviour',
                    'can' => 'Review recently changed PHP, Fluid, TCA, Services.yaml and ext_localconf.php for clarity, consistency and v14 practice.',
                    'cool' => 'Three review passes run in parallel: reuse of TYPO3 APIs, code quality and efficiency. It is adapted from Boris Cherny’s code-simplifier agent.',
                    'why' => 'It replaces custom code with what TYPO3 already provides, and checks that the behaviour stays the same.',
                    'say' => 'Simplify the code I changed in this branch before we merge it.',
                ],
            ],
        ];
    }

    /**
     * @return SkillGroup
     */
    private static function releaseOpenSource(): array
    {
        return [
            'slug' => 'release-open-source',
            'title' => 'Release and open source',
            'abstract' => 'A first public release, documentation on docs.typo3.org, a hardened supply chain and contributions to the TYPO3 Core.',
            'description' => 'Four skills for publishing TYPO3 work: a first 1.0.0 release, docs for docs.typo3.org, supply-chain security and patches for the TYPO3 Core.',
            'header' => 'From first release to TYPO3 Core patches',
            'pitch' => 'Ship a first release that keeps its promises, write docs that render on docs.typo3.org, harden the supply chain and contribute to the Core.',
            'skills' => [
                [
                    'name' => 'typo3-initial-release',
                    'owner' => 'webconsulting',
                    'upstream' => '',
                    'superfeature' => false,
                    'icon' => 'download',
                    'title' => 'Ship version 1.0.0',
                    'line' => 'Prepares the first public 1.0.0 release of an extension for TER or Packagist.',
                    'headline' => 'typo3-initial-release: a 1.0.0 that keeps its promise',
                    'can' => 'Check the Composer metadata, the PHP and TYPO3 CI matrix, docs, labels and changelog, then tag the first release.',
                    'cool' => 'The tag comes last, only after metadata, CI, documentation, labels and a final review all agree.',
                    'why' => 'Updating version strings is the smallest part of a release. The skill checks the rest and publishes only with your explicit approval.',
                    'say' => 'We are ready to ship. What has to be in place before I tag 1.0.0?',
                ],
                [
                    'name' => 'typo3-docs',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-docs-skill',
                    'superfeature' => false,
                    'icon' => 'book-open',
                    'title' => 'Write docs for docs.typo3.org',
                    'line' => 'Writes extension documentation that renders on docs.typo3.org, from guides.xml to screenshots.',
                    'headline' => 'typo3-docs: documentation that renders',
                    'can' => 'Create documentation from scratch, add configuration sections and screenshots, then validate and render it with Docker.',
                    'cool' => 'It follows the rules of docs.typo3.org: guides.xml instead of Settings.cfg, a permalink anchor before every heading, sentence case.',
                    'why' => 'A wrong namespace in guides.xml stops a manual from rendering, so the skill generates that file instead of typing it.',
                    'say' => 'Create documentation for this TYPO3 extension from scratch. It has PHP classes, TCA configuration, and a backend module but no Documentation/ directory yet.',
                ],
                [
                    'name' => 'typo3-core-contributions',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/typo3-core-contributions-skill',
                    'superfeature' => false,
                    'icon' => 'handshake',
                    'title' => 'Contribute to the TYPO3 Core',
                    'line' => 'Helps contribute to TYPO3 Core: Forge issues, Gerrit patches, cherry-picks and CI logs.',
                    'headline' => 'typo3-core-contributions: patches for the Core',
                    'can' => 'Set up a Core development environment, analyse an issue, write the fix with tests and push it to Gerrit in the right commit format.',
                    'cool' => 'It proves each test fails without the fix, and reads the actual CI logs before the next push.',
                    'why' => 'Gerrit has its own rules, from work-in-progress pushes to keeping the Change-Id. The skill knows them.',
                    'say' => 'Prepare a patch for this TYPO3 Core bug from Forge and push it to Gerrit as work in progress.',
                ],
                [
                    'name' => 'enterprise-readiness',
                    'owner' => self::NETRESEARCH,
                    'upstream' => 'https://github.com/netresearch/enterprise-readiness-skill',
                    'superfeature' => false,
                    'icon' => 'shield',
                    'title' => 'Harden the supply chain',
                    'line' => 'Assesses production readiness: supply-chain security, CI/CD hardening and quality gates.',
                    'headline' => 'enterprise-readiness: a supply chain you can show',
                    'can' => 'Assess a project for production: SLSA provenance, cosign signatures, SBOMs, pinned GitHub Actions and the OpenSSF badges.',
                    'cool' => 'It scores, lists the gaps by severity, applies the fixes and scores again, so the difference is visible.',
                    'why' => 'It makes the build and signing of a release traceable, from SHA-pinned actions to signed artefacts.',
                    'say' => 'Assess this project for enterprise readiness and identify gaps.',
                ],
            ],
        ];
    }
}
