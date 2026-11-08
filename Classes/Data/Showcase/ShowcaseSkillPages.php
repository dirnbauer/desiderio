<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The typo3-skills collection below /ai/agentic-skills: twelve group pages of
 * three or four skills, and the sections of the hub page itself. The copy
 * lives in ShowcaseSkillGroups; this class lays it out.
 *
 * Every group page has the same blocks in the same order, so the pages read
 * alike and a new group is data, not a layout:
 *
 *  1. a header section with the group's promise (the page film follows it,
 *     placed by the site package's films seeder)
 *  2. one card per skill: what you can do with it, linking to its section
 *  3. one section per skill, anchored by the skill's name (#typo3-upgrade-run):
 *     what you can do, what is cool about it and why to use it, with a
 *     request to try, how to call it, its SKILL.md and its place in the
 *     skill search beside it
 *  4. how to help improve the skills (vendored ones are improved upstream)
 *  5. a call to action to install them
 *
 * The skill search (/ai/agentic-skills/find) belongs to another package and
 * stays the hub's first child (leadingChildPages()); the ten pages of one
 * skill each that came before the groups redirect to their skill's anchor
 * (retiredPages()).
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcaseMedia from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 * @phpstan-import-type SkillEntry from ShowcaseSkillGroups
 * @phpstan-import-type SkillGroup from ShowcaseSkillGroups
 */
final class ShowcaseSkillPages
{
    public const string REPO_URL = ShowcaseSkillGroups::REPO_URL;

    /** The header of the hub's superfeature box; the hub's film follows it. */
    public const string SUPERFEATURE_HEADER = 'Start with the ten superfeature skills';

    /** The anchor of the hub's install section. */
    public const string INSTALL_ANCHOR = 'install';

    private const string SKILL_FILE_URL = 'https://github.com/dirnbauer/typo3-skills/blob/main/skills/';
    private const string DEV_COMPANION_URL = 'https://github.com/TYPO3/dev-companion';
    private const string DEV_COMPANION_ARTICLE = 'https://news.typo3.com/article/meet-the-typo3-dev-companion-typo3-knowledge-for-your-coding-agent';

    /**
     * The twelve group pages, in the order the hub and the menu list them.
     *
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        $pages = [];
        foreach (ShowcaseSkillGroups::groups() as $index => $group) {
            $pages[] = self::groupPage($group, $index + 1);
        }

        return $pages;
    }

    /**
     * The hub's own sections, between its hero and its questions: the
     * superfeatures first (the hub's film follows them), then what skills
     * are, the community talks they build on, how to install and call them,
     * the TYPO3 Dev Companion, the twelve groups and the other skills.
     *
     * @return list<ShowcaseBlock>
     */
    public static function hubSections(): array
    {
        return [
            self::superfeatureBox(),
            self::whatAndWhy(),
            self::communityTalks(),
            self::installSteps(),
            self::installCommands(),
            self::nameOrLink(),
            self::devCompanion(),
            self::groupCards(),
            self::otherSkills(),
        ];
    }

    /**
     * @return array<string, array{successor: string, anchor: string}>
     */
    public static function retiredPages(): array
    {
        return ShowcaseSkillGroups::retiredSkillPages();
    }

    /**
     * @return array<string, string>
     */
    public static function leadingChildPages(): array
    {
        return ['/' . ShowcaseSkillGroups::SEARCH => ShowcaseSkillGroups::HUB];
    }

    /**
     * A link field to the skill search with the skill already searched for.
     */
    public static function searchLink(string $skill): string
    {
        return '{{page:' . ShowcaseSkillGroups::SEARCH . '}}&tx_solr%5Bq%5D=' . rawurlencode($skill);
    }

    /**
     * A link field to the section of a skill on its group page.
     */
    public static function skillLink(string $skill): string
    {
        foreach (ShowcaseSkillGroups::groups() as $group) {
            foreach ($group['skills'] as $entry) {
                if ($entry['name'] === $skill) {
                    return self::groupLink($group['slug']) . '#' . $skill;
                }
            }
        }
        throw new \InvalidArgumentException(sprintf('No group page features the skill %s.', $skill), 1762588800);
    }

    /**
     * @param SkillGroup $group
     * @return ShowcasePage
     */
    private static function groupPage(array $group, int $number): array
    {
        $count = count($group['skills']);
        $content = [
            ShowcaseBlocks::block('desiderio_headersection', [
                'eyebrow' => sprintf('Skill group %d', $number),
                'header' => $group['header'],
                'subheadline' => $group['pitch'],
                'variant' => 'center',
            ]),
            ShowcaseBlocks::block('desiderio_benefitcards', [
                'frame_class' => 'muted',
                'eyebrow' => $count . ' skills',
                'header' => 'What you can do with these skills',
                'subheadline' => 'Each card leads to its skill further down this page.',
                'columns' => $count === 3 ? '3' : '2',
                'items' => array_map(
                    static fn(array $skill): array => [
                        'icon' => $skill['icon'],
                        'title' => $skill['title'],
                        'description' => '<p><code>' . $skill['name'] . '</code>: ' . self::text($skill['line']) . '</p>',
                        'link' => self::groupLink($group['slug']) . '#' . $skill['name'],
                    ],
                    $group['skills'],
                ),
            ]),
        ];
        foreach ($group['skills'] as $index => $skill) {
            $content[] = self::skillSection($skill, $index % 2 === 1 ? 'muted' : 'default');
        }
        $content[] = self::helpBlock($group);
        $content[] = ShowcaseBlocks::block('desiderio_ctabanner', [
            'header' => 'Add these skills to your agent',
            'description' => 'One clone and one install script add all 61 skills to Claude Code, Codex, Cursor, Gemini and Windsurf. A git pull keeps them current.',
            'cta_text' => 'Install the skills',
            'cta_link' => '{{page:' . ShowcaseSkillGroups::HUB . '}}#' . self::INSTALL_ANCHOR,
            'bg_style' => 'primary',
        ]);

        return [
            'title' => $group['title'],
            'navTitle' => $group['title'],
            'slug' => '/' . ShowcaseSkillGroups::HUB . '/' . $group['slug'],
            'seoTitle' => '',
            'abstract' => $group['abstract'],
            'description' => $group['description'],
            'parentSlug' => ShowcaseSkillGroups::HUB,
            'content' => $content,
        ];
    }

    /**
     * One skill: what it does in the main column, how to start it beside it.
     * The element's anchor is the skill's name, which the old one-skill
     * pages redirect to.
     *
     * @param SkillEntry $skill
     * @return ShowcaseBlock
     */
    private static function skillSection(array $skill, string $frame): array
    {
        $name = $skill['name'];
        $sidebar = '<p><strong>Say:</strong> “' . self::text($skill['say']) . '”</p>'
            . '<p><strong>Call it by name:</strong> <code>/' . $name . '</code> in Claude Code and Cursor, <code>$' . $name . '</code> in Codex, <code>@' . $name . '</code> in Windsurf.</p>'
            . '<ul>'
            . '<li><a href="' . self::SKILL_FILE_URL . $name . '/SKILL.md">Read its SKILL.md on GitHub</a></li>'
            . '<li><a href="' . self::searchHref($name) . '">See it in the skill search</a></li>'
            . '</ul>'
            . '<p>' . self::ownerLine($skill) . '</p>';

        return ShowcaseBlocks::block('desiderio_contentsidebar', [
            'tx_desiderio_anchor' => $name,
            'frame_class' => $frame,
            'header' => $skill['headline'],
            'content' => '<h3>What you can do</h3><p>' . self::text($skill['can']) . '</p>'
                . '<h3>What is cool about it</h3><p>' . self::text($skill['cool']) . '</p>'
                . '<h3>Why use it</h3><p>' . self::text($skill['why']) . '</p>',
            'sidebar_content' => $sidebar,
            'sidebar_position' => 'right',
        ]);
    }

    /**
     * @param SkillEntry $skill
     */
    private static function ownerLine(array $skill): string
    {
        if ($skill['upstream'] === '') {
            return ($skill['superfeature'] ? 'Superfeature, maintained' : 'Maintained') . ' in the collection by webconsulting.';
        }

        return sprintf('By %s, vendored unchanged. Changes belong in its <a href="%s">upstream repository</a>.', self::text($skill['owner']), $skill['upstream']);
    }

    /**
     * Issues, pull requests, evals and reviews; vendored skills go upstream.
     *
     * @param SkillGroup $group
     * @return ShowcaseBlock
     */
    private static function helpBlock(array $group): array
    {
        $content = '<p>If you find a wrong fact or a gap, open an issue or a pull request on <a href="' . ShowcaseSkillGroups::REPO_URL . '">GitHub</a>. '
            . 'Evals help most: a request where the skill should load, one where it should not, and what a good answer contains.</p>'
            . '<p>Reviews help as much. A generated eval stays a draft until a person signs it off.</p>';

        $byOwner = [];
        foreach ($group['skills'] as $skill) {
            if ($skill['upstream'] !== '') {
                $byOwner[$skill['owner']][] = '<a href="' . $skill['upstream'] . '">' . $skill['name'] . '</a>';
            }
        }
        if ($byOwner !== []) {
            $parts = [];
            $vendored = 0;
            foreach ($byOwner as $owner => $links) {
                $parts[] = self::listing($links) . (count($links) === 1 ? ' comes' : ' come') . ' unchanged from ' . self::text($owner);
                $vendored += count($links);
            }
            $content .= '<p>' . implode('; ', $parts) . '. '
                . ($vendored === 1
                    ? 'Suggest changes to it in its own repository, so its authors can take them up.'
                    : 'Suggest changes to them in their own repositories, so their authors can take them up.')
                . '</p>';
        }

        return ShowcaseBlocks::block('desiderio_contenthighlight', [
            'header' => 'Help improve these skills',
            'content' => $content,
            'variant' => 'bordered',
            'alignment' => 'center',
            'link' => ShowcaseSkillGroups::ISSUES_URL,
            'link_text' => 'Open an issue',
        ]);
    }

    /**
     * The hub's second element: the ten superfeatures, each opening the
     * skill search with the skill searched for, and a button to the search.
     *
     * @return ShowcaseBlock
     */
    private static function superfeatureBox(): array
    {
        $items = '';
        foreach (ShowcaseSkillGroups::groups() as $group) {
            foreach ($group['skills'] as $skill) {
                if ($skill['superfeature']) {
                    $items .= '<li><a href="' . self::searchHref($skill['name']) . '"><code>' . $skill['name'] . '</code></a>: ' . self::text($skill['line']) . '</li>';
                }
            }
        }

        return ShowcaseBlocks::block('desiderio_featurehighlight', [
            'eyebrow' => 'Superfeatures',
            'header' => self::SUPERFEATURE_HEADER,
            'description' => '<p>These ten take on whole jobs, from a proven upgrade to editor rights. Each link opens the skill in the search.</p><ul>' . $items . '</ul>',
            'image' => ShowcaseBlocks::screenshot(
                'frontend-feature-skills-catalogue-ce0b02d1.webp',
                'The skill search in TYPO3',
                'Skill catalogue on a TYPO3 page: a search for typo3-upgrade with 25 results and facets for source, tools and licence.',
                'Live screenshot from the TYPO3 v14 lab.'
            ),
            'link' => '{{page:' . ShowcaseSkillGroups::SEARCH . '}}',
            'link_text' => 'Search all skills',
            'variant' => 'muted',
        ]);
    }

    /**
     * What a skill is, beside the SKILL.md of one, and why skills help.
     *
     * @return ShowcaseBlock
     */
    private static function whatAndWhy(): array
    {
        return ShowcaseBlocks::block('desiderio_featuresplit', [
            'header' => 'What a skill is, and why it helps',
            'description' => '<p>A skill is a folder with a SKILL.md file: written instructions, references and scripts for one kind of task. '
                . 'Your agent reads the short description of every installed skill and loads the full skill only when your request matches it.</p>',
            'image' => ShowcaseBlocks::screenshot(
                'frontend-feature-skills-typo3-upgrade-run-803722c7.webp',
                'A SKILL.md on GitHub',
                'The SKILL.md of typo3-upgrade-run on GitHub: its name, description and metadata, then the first rules.',
                'Screenshot of the typo3-skills repository, 4 November 2026.'
            ),
            'items' => [
                ['title' => 'Steps instead of improvising', 'description_text' => 'Ask for an upgrade, and the agent follows the upgrade skill step by step.'],
                ['title' => 'TYPO3 v14, not TYPO3 in general', 'description_text' => 'The skills are written against the installed v14 source, not from memory.'],
                ['title' => 'Rules the agent keeps', 'description_text' => '22 always-on rules, three of them for the agent itself, such as no claim without evidence.'],
                ['title' => 'Tested before they ship', 'description_text' => 'The 41 skills maintained here have evals: requests where they must load, and where they must not.'],
            ],
        ]);
    }

    /**
     * Five community talks the skills build on, oldest first, each with its
     * speaker, its event, its materials and the skills it relates to.
     *
     * @return ShowcaseBlock
     */
    private static function communityTalks(): array
    {
        $items = [];
        foreach (ShowcaseSkillGroups::talks() as $talk) {
            $related = array_map(
                static fn(string $skill): string => '<a href="' . self::skillLink($skill) . '">' . $skill . '</a>',
                $talk['skills'],
            );
            $extra = $talk['extra'] === null ? '' : ', with the <a href="' . $talk['extra']['url'] . '">' . self::text($talk['extra']['text']) . '</a>';
            $items[] = [
                'step' => $talk['date'],
                'title' => $talk['topic'],
                'content' => '<p><span lang="de">' . self::text($talk['title']) . '</span>, a ' . $talk['kind'] . ' by ' . self::text($talk['speaker'])
                    . ' at ' . ($talk['at'] === '' ? '' : $talk['at'] . ' ') . '<a href="' . $talk['eventUrl'] . '">' . self::text($talk['event']) . '</a>' . $extra . '.</p>'
                    . '<p>Related skills: ' . implode(', ', $related) . '</p>',
                'link_text' => 'Open the slides',
                'link' => $talk['materials'],
            ];
        }

        return ShowcaseBlocks::block('desiderio_timeline', [
            'header' => 'Built on talks from the TYPO3 community',
            'subheadline' => 'MTUG, the Munich TYPO3 User Group, has met for expert talks since 2009, now mostly online. Our skills build on community talks like these, at MTUG and TYPO3camp München.',
            'items' => $items,
        ]);
    }

    /**
     * The three ways in, anchored #install for the hero and the group pages.
     *
     * @return ShowcaseBlock
     */
    private static function installSteps(): array
    {
        return ShowcaseBlocks::block('desiderio_featurenumbered', [
            'tx_desiderio_anchor' => self::INSTALL_ANCHOR,
            'header' => 'Install in three ways',
            'subheadline' => 'The install script covers every client at once. A copied folder is enough for one skill, and you can call any skill by name.',
            'items' => [
                [
                    'title' => 'Run the install script',
                    'description' => '<p>Clone the repository and run <code>./install.sh</code>. It links every skill folder into Claude Code, Cursor, Codex, Gemini and Windsurf, for your user and for the current project. '
                        . 'It never overwrites your own folders, and <code>git pull</code> updates them all.</p>',
                ],
                [
                    'title' => 'Copy or link one folder',
                    'description' => '<p>Copy or link the whole folder <code>skills/&lt;name&gt;</code>, not only its SKILL.md, to <code>~/.claude/skills/&lt;name&gt;</code> or <code>&lt;project&gt;/.claude/skills/&lt;name&gt;</code>. '
                        . 'Codex, Cursor, Gemini and Windsurf have folders of their own.</p>',
                ],
                [
                    'title' => 'Call a skill directly',
                    'description' => '<p>Describe the task, and your agent picks the skill by its description. '
                        . 'Or call it by name: <code>/typo3-upgrade-run</code> in Claude Code and in Cursor’s agent chat, <code>$typo3-upgrade-run</code> in Codex, <code>@typo3-upgrade-run</code> in Windsurf.</p>',
                ],
            ],
        ]);
    }

    /**
     * @return ShowcaseBlock
     */
    private static function installCommands(): array
    {
        return ShowcaseBlocks::block('desiderio_codeblock', [
            'header' => 'The commands',
            'language' => 'bash',
            'filename' => 'terminal',
            'code' => implode("\n", [
                '# 1. The install script: every client it finds, for your user and this project',
                'git clone https://github.com/dirnbauer/typo3-skills.git && cd typo3-skills && ./install.sh',
                './install.sh --client claude   # one client: claude, codex, cursor, gemini, windsurf or chatgpt',
                './install.sh --user-only       # only for your user; --project-only for this project',
                'git pull                       # updates every linked skill',
                '',
                '# 2. By hand: link or copy the whole folder, not only SKILL.md',
                'mkdir -p ~/.claude/skills',
                'ln -s "$PWD/skills/typo3-upgrade-run" ~/.claude/skills/typo3-upgrade-run',
                '',
                '# 3. Then call it by name in a new agent session:',
                '#    Claude Code, Cursor:  /typo3-upgrade-run',
                '#    Codex:                $typo3-upgrade-run',
                '#    Windsurf:             @typo3-upgrade-run',
            ]),
        ]);
    }

    /**
     * Calling an installed skill by name, or pasting a link to its SKILL.md.
     *
     * @return ShowcaseBlock
     */
    private static function nameOrLink(): array
    {
        return ShowcaseBlocks::block('desiderio_benefitcards', [
            'eyebrow' => 'Name or link',
            'header' => 'Call by name, not by link',
            'subheadline' => 'A pasted SKILL.md link gives the model one text. A call by name gives it the whole installed folder.',
            'columns' => '2',
            'items' => [
                [
                    'icon' => 'folder',
                    'title' => 'Call it: /typo3-upgrade-run',
                    'description' => '<p>The agent uses the installed folder with everything in it: the SKILL.md, its references, scripts and evals.</p>',
                ],
                [
                    'icon' => 'link',
                    'title' => 'Paste the SKILL.md link',
                    'description' => '<p>The model gets that one text, without scripts, references or evals. That is enough to try a text-only skill. '
                        . 'Skills with scripts break, such as typo3-upgrade-run, typo3-wcag22-aa-agentic and typo3-backend-rights.</p>',
                ],
            ],
        ]);
    }

    /**
     * The TYPO3 project's Dev Companion, with the article's diagram redrawn
     * and the skills added beside it.
     *
     * @return ShowcaseBlock
     */
    private static function devCompanion(): array
    {
        return ShowcaseBlocks::block('desiderio_textmedia', [
            'frame_class' => 'muted',
            'header' => 'The TYPO3 Dev Companion and our skills',
            'shadcn_layout' => 'media-above',
            'subheadline' => 'A local, read-only MCP server from the TYPO3 project, introduced by Benjamin Kott on 5 October 2026.',
            // The diagram is Benjamin Kott's, redrawn: the credit sits in the image and opens the text below it.
            'content' => '<p><strong>Diagram © Benjamin Kott.</strong> The original is part of his article <a href="' . self::DEV_COMPANION_ARTICLE . '">“Meet the TYPO3 Dev Companion”</a> on news.typo3.com (5 October 2026). '
                . 'We redrew it in TYPO3 colours and added one card for typo3-skills. The drawing, the idea and the Dev Companion itself are the work of Benjamin Kott and the TYPO3 project.</p>'
                . '<p>As <a href="' . self::DEV_COMPANION_ARTICLE . '">Benjamin Kott’s article on news.typo3.com</a> puts it, coding agents read a TYPO3 project well but know TYPO3 badly. '
                . 'The TYPO3 Dev Companion gives Claude Code, Cursor, GitHub Copilot, Codex and other MCP clients that knowledge. '
                . 'It covers TYPO3 12.4, 13.4, 14.3 and main, answers from your own installation and guides tasks step by step. Every answer names its source.</p>'
                . '<p>It runs on your machine and writes nothing into your installation. Its only outside connection is a read-only lookup in docs.typo3.org and the official TYPO3 services: Forge, Gerrit and the TER. '
                . 'It is experimental, MIT-licensed and well worth trying. Thank you to Benjamin Kott and everyone in the TYPO3 project who built it.</p>'
                . '<p>Our skills work next to it. The Dev Companion knows the facts: what your TYPO3 version offers and what your installation contains. '
                . 'A skill knows the procedure for a whole job, such as a proven upgrade or an accessibility scan with a CI gate. '
                . 'Your agent can use both: the Dev Companion to look things up, a skill to finish the job.</p>',
            'media' => [
                'file' => 'Resources/Public/Styleguide/Frontend/frontend-dev-companion-skills-34483469.webp',
                'title' => 'TYPO3 Dev Companion with typo3-skills. Original diagram © Benjamin Kott',
                'alternative' => 'Diagram of the TYPO3 Dev Companion. On your machine, your coding agent asks the TYPO3 Dev Companion, which reads bundled knowledge and your installation and writes nothing. Outside, docs.typo3.org and TYPO3 services are read-only, and the new typo3-skills collection gives the agent 61 open TYPO3 skills. Original diagram © Benjamin Kott, redrawn by webconsulting.',
                'description' => 'Original diagram © Benjamin Kott, from his article on news.typo3.com (5 October 2026). Redrawn in TYPO3 colours with AI by webconsulting, with the typo3-skills card added.',
                'source' => self::DEV_COMPANION_ARTICLE,
            ],
            'media_rounded' => 1,
            'button_text' => 'Try Dev Companion',
            'button_link' => self::DEV_COMPANION_URL,
        ]);
    }

    /**
     * One card per group page.
     *
     * @return ShowcaseBlock
     */
    private static function groupCards(): array
    {
        return ShowcaseBlocks::block('desiderio_categorycards', [
            'eyebrow' => 'Skill groups',
            'header' => '12 groups, 3 or 4 skills each',
            'subheadline' => 'Each group page shows what its skills do, why they help and how to call them.',
            'columns' => '3',
            'items' => array_map(
                static fn(array $group): array => [
                    'title' => $group['title'],
                    'count' => count($group['skills']) . ' skills',
                    'description' => '<p>' . self::text($group['abstract']) . '</p>',
                    'link' => self::groupLink($group['slug']),
                ],
                ShowcaseSkillGroups::groups(),
            ),
        ]);
    }

    /**
     * The skills no group page features, each linking the group it belongs
     * with.
     *
     * @return ShowcaseBlock
     */
    private static function otherSkills(): array
    {
        $titles = [];
        foreach (ShowcaseSkillGroups::groups() as $group) {
            $titles[$group['slug']] = $group['title'];
        }

        return ShowcaseBlocks::block('desiderio_benefitcards', [
            'eyebrow' => 'More skills',
            'header' => 'Also in the collection',
            'subheadline' => '12 more skills, each next to the group it belongs with. Two more, not listed here, cover webconsulting’s own branding and Austrian legal notices.',
            'columns' => '3',
            'items' => array_map(
                static fn(array $skill): array => [
                    'title' => $skill['name'],
                    'description' => '<p>' . self::text($skill['line']) . '</p><p>Belongs with: ' . self::text($titles[$skill['group']] ?? '') . '</p>',
                    'link' => self::groupLink($skill['group']),
                ],
                ShowcaseSkillGroups::otherSkills(),
            ),
        ]);
    }

    private static function groupLink(string $groupSlug): string
    {
        return '{{page:' . ShowcaseSkillGroups::HUB . '/' . $groupSlug . '}}';
    }

    /**
     * The search link inside rich text, where the query separator is &amp;
     * (parseFunc decodes the href before it builds the link).
     */
    private static function searchHref(string $skill): string
    {
        return htmlspecialchars(self::searchLink($skill), ENT_QUOTES);
    }

    private static function text(string $value): string
    {
        return htmlspecialchars($value, ENT_NOQUOTES);
    }

    /**
     * "A", "A and B", "A, B and C": the copy uses no serial comma.
     *
     * @param non-empty-list<string> $items
     */
    private static function listing(array $items): string
    {
        $last = array_pop($items);

        return $items === [] ? $last : implode(', ', $items) . ' and ' . $last;
    }
}
