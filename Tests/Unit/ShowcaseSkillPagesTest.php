<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeaturePages;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseSkillGroups;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseSkillPages;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

/**
 * /ai/agentic-skills and its twelve group pages: the groups of the plan of
 * 8 November 2026, one outline for every group page (the films seeder
 * places each page film after the header section), every skill anchored by
 * its name, and the ten one-skill pages before them redirected to those
 * anchors. The skill search belongs to another package and stays the hub's
 * first child.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseSkillPagesTest extends TestCase
{
    private const string HUB = '/ai/agentic-skills';

    /**
     * Group page slug => its skills, superfeatures marked with "*".
     */
    private const array GROUPS = [
        'proven-site-upgrade' => ['typo3-upgrade-run*', 'typo3-upgrade-intake', 'typo3-upgrade-baseline', 'typo3-upgrade-closure'],
        'upgrade-planning' => ['typo3-upgrade-effort-model', 'typo3-project-upgrade', 'typo3-upgrade-migration', 'typo3-upgrade-retrospective'],
        'code-migration' => ['typo3-extension-upgrade', 'typo3-rector', 'typo3-fractor', 'typo3-batch'],
        'content-modelling' => ['typo3-content-blocks*', 'typo3-datahandler', 'typo3-translations', 'typo3-news-tags'],
        'editor-experience' => ['typo3-visual-editor*', 'typo3-workspaces', 'typo3-records-list-types*', 'typo3-ckeditor5'],
        'design-system' => ['typo3-shadcn-content-elements*', 'typo3-design-system-page*', 'typo3-vite', 'typo3-icon14'],
        'v14-development' => ['typo3-v14-reference', 'typo3-typoscript-ref', 'typo3-webcomponents', 'typo3-ddev'],
        'accessibility' => ['typo3-wcag22-aa-agentic*', 'typo3-accessibility', 'typo3-a11y'],
        'search-seo-agents' => ['typo3-seo', 'typo3-structured-data*', 'typo3-solr', 'typo3-webmcp*'],
        'security-operations' => ['typo3-security', 'typo3-backend-rights*', 'typo3-scheduler-jobs', 'typo3-site-conformance'],
        'testing-quality' => ['typo3-testing', 'typo3-playwright', 'typo3-conformance', 'typo3-simplify'],
        'release-open-source' => ['typo3-initial-release', 'typo3-docs', 'typo3-core-contributions', 'enterprise-readiness'],
    ];

    /** The other skills of the collection => the group they belong with. */
    private const array OTHERS = [
        'typo3-powermail' => 'content-modelling',
        'typo3-jev' => 'content-modelling',
        'php-modernization' => 'code-migration',
        'web-design-guidelines' => 'design-system',
        'web-platform-design' => 'design-system',
        'security-audit' => 'security-operations',
        'security-incident-reporting' => 'security-operations',
        'thermo-nuclear-code-quality-review' => 'testing-quality',
        'postgres-best-practices' => 'v14-development',
        'architecture-decision-records' => 'release-open-source',
        'webconsulting-create-documentation' => 'release-open-source',
        'typo3-idea-extension-blog' => 'release-open-source',
    ];

    /** ls skills/ in github.com/dirnbauer/typo3-skills on 8 November 2026. */
    private const array COLLECTION = [
        'architecture-decision-records', 'enterprise-readiness', 'legal-impressum', 'php-modernization', 'postgres-best-practices',
        'security-audit', 'security-incident-reporting', 'thermo-nuclear-code-quality-review', 'typo3-a11y', 'typo3-accessibility',
        'typo3-backend-rights', 'typo3-batch', 'typo3-ckeditor5', 'typo3-conformance', 'typo3-content-blocks', 'typo3-core-contributions',
        'typo3-datahandler', 'typo3-ddev', 'typo3-design-system-page', 'typo3-docs', 'typo3-extension-upgrade', 'typo3-fractor',
        'typo3-icon14', 'typo3-idea-extension-blog', 'typo3-initial-release', 'typo3-jev', 'typo3-news-tags', 'typo3-playwright',
        'typo3-powermail', 'typo3-project-upgrade', 'typo3-records-list-types', 'typo3-rector', 'typo3-scheduler-jobs', 'typo3-security',
        'typo3-seo', 'typo3-shadcn-content-elements', 'typo3-simplify', 'typo3-site-conformance', 'typo3-solr', 'typo3-structured-data',
        'typo3-testing', 'typo3-translations', 'typo3-typoscript-ref', 'typo3-upgrade-baseline', 'typo3-upgrade-closure',
        'typo3-upgrade-effort-model', 'typo3-upgrade-intake', 'typo3-upgrade-migration', 'typo3-upgrade-retrospective', 'typo3-upgrade-run',
        'typo3-v14-reference', 'typo3-visual-editor', 'typo3-vite', 'typo3-wcag22-aa-agentic', 'typo3-webcomponents', 'typo3-webmcp',
        'typo3-workspaces', 'web-design-guidelines', 'web-platform-design', 'webconsulting-branding', 'webconsulting-create-documentation',
    ];

    /** VENDORED.md: unchanged copies of other people's skills. */
    private const array VENDORED = [
        'enterprise-readiness', 'php-modernization', 'postgres-best-practices', 'security-audit', 'typo3-a11y', 'typo3-ckeditor5',
        'typo3-conformance', 'typo3-core-contributions', 'typo3-ddev', 'typo3-docs', 'typo3-extension-upgrade', 'typo3-project-upgrade',
        'typo3-simplify', 'typo3-site-conformance', 'typo3-testing', 'typo3-typoscript-ref', 'typo3-upgrade-effort-model', 'typo3-vite',
        'web-design-guidelines', 'web-platform-design',
    ];

    /** The plan's redirect targets of the ten one-skill pages. */
    private const array RETIRED = [
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

    /** The coordinator's alt text, in sentences of at most 25 words (canon). */
    private const string DIAGRAM_ALT = 'Diagram of the TYPO3 Dev Companion. On your machine, your coding agent asks the TYPO3 Dev Companion, which reads bundled knowledge and your installation and writes nothing. Outside, docs.typo3.org and TYPO3 services are read-only, and the new typo3-skills collection gives the agent 61 open TYPO3 skills.';

    public function testTwelveGroupPagesSitBelowTheHub(): void
    {
        $pages = ShowcaseSkillPages::pages();

        self::assertSame(
            array_map(static fn(string $group): string => self::HUB . '/' . $group, array_keys(self::GROUPS)),
            array_column($pages, 'slug'),
        );
        foreach ($pages as $page) {
            self::assertSame(ltrim(self::HUB, '/'), $page['parentSlug'], $page['slug']);
            self::assertArrayNotHasKey('formerSlugs', $page, $page['slug'] . ' is new; the old pages redirect through retiredPages().');
            self::assertNotSame('Find a skill', $page['title'], 'The search page belongs to another package.');
            self::assertNotSame(self::HUB . '/find', $page['slug']);
        }
    }

    public function testTheShowcaseSeedsThemAfterTheHub(): void
    {
        $slugs = array_column(StyleguideShowcasePages::subpages(), 'slug');
        $hub = array_search(self::HUB, $slugs, true);

        self::assertIsInt($hub, 'The Agentic skills page is seeded.');
        $previous = $hub;
        foreach (ShowcaseSkillPages::pages() as $page) {
            $position = array_search($page['slug'], $slugs, true);
            self::assertIsInt($position, $page['slug']);
            self::assertGreaterThan($previous, $position, $page['slug'] . ' comes after the hub and the groups before it.');
            $previous = $position;
        }
        self::assertContains(self::HUB, array_column(ShowcaseFeaturePages::aiPages(), 'slug'));
    }

    public function testTheGroupsHoldTheSkillsOfThePlan(): void
    {
        $actual = [];
        foreach (ShowcaseSkillGroups::groups() as $group) {
            $actual[$group['slug']] = array_map(
                static fn(array $skill): string => $skill['name'] . ($skill['superfeature'] ? '*' : ''),
                $group['skills'],
            );
        }

        self::assertSame(self::GROUPS, $actual);
        self::assertCount(10, array_filter(array_merge(...array_values(self::GROUPS)), static fn(string $skill): bool => str_ends_with($skill, '*')));
    }

    public function testThePagesCoverTheWholeCollection(): void
    {
        $featured = [];
        foreach (ShowcaseSkillGroups::groups() as $group) {
            foreach ($group['skills'] as $skill) {
                $featured[] = $skill['name'];
            }
        }
        $others = [];
        foreach (ShowcaseSkillGroups::otherSkills() as $skill) {
            $others[$skill['name']] = $skill['group'];
        }
        $all = [...$featured, ...array_keys($others), ...ShowcaseSkillGroups::unlistedSkills()];

        self::assertCount(47, $featured);
        self::assertSame(self::OTHERS, $others);
        self::assertCount(61, $all);
        self::assertCount(count($all), array_unique($all), 'Every skill appears once.');
        $sorted = $all;
        sort($sorted);
        self::assertSame(self::COLLECTION, $sorted);
    }

    public function testVendoredSkillsNameTheirAuthorsAndUpstream(): void
    {
        $vendored = [];
        foreach (ShowcaseSkillGroups::groups() as $group) {
            foreach ($group['skills'] as $skill) {
                self::assertSame($skill['owner'] !== 'webconsulting', $skill['upstream'] !== '', $skill['name']);
                if ($skill['upstream'] !== '') {
                    self::assertStringStartsWith('https://github.com/', $skill['upstream'], $skill['name']);
                    self::assertFalse($skill['superfeature'], $skill['name'] . ': superfeatures are maintained here.');
                    $vendored[] = $skill['name'];
                }
            }
        }
        foreach (ShowcaseSkillGroups::otherSkills() as $skill) {
            if (!in_array($skill['owner'], ['webconsulting', 'Cursor'], true)) {
                $vendored[] = $skill['name'];
            }
        }
        sort($vendored);

        self::assertSame(self::VENDORED, $vendored);
    }

    public function testEveryGroupPageFollowsTheSameOutline(): void
    {
        foreach (ShowcaseSkillGroups::groups() as $index => $group) {
            $page = self::groupPage($group['slug']);
            $count = count($group['skills']);
            $ctypes = array_column($page['content'], 'ctype');

            self::assertSame(
                ['desiderio_headersection', 'desiderio_benefitcards', ...array_fill(0, $count, 'desiderio_contentsidebar'), 'desiderio_contenthighlight', 'desiderio_ctabanner'],
                $ctypes,
                $page['slug']
            );

            $header = $page['content'][0]['fields'];
            self::assertSame(sprintf('Skill group %d', $index + 1), $header['eyebrow'] ?? null);
            self::assertSame($group['header'], $header['header'] ?? null);

            $cards = $page['content'][1]['fields']['items'] ?? null;
            self::assertIsArray($cards);
            self::assertCount($count, $cards, $page['slug']);
            self::assertSame($count === 3 ? '3' : '2', $page['content'][1]['fields']['columns'] ?? null, $page['slug'] . ': no empty grid cell.');

            foreach ($group['skills'] as $position => $skill) {
                $card = $cards[$position];
                self::assertIsArray($card);
                self::assertSame('{{page:' . ltrim($page['slug'], '/') . '}}#' . $skill['name'], $card['link'] ?? null, $skill['name']);

                $section = $page['content'][2 + $position]['fields'];
                self::assertSame($skill['name'], $section['tx_desiderio_anchor'] ?? null, $skill['name'] . ' is the anchor the old pages redirect to.');
                self::assertStringStartsWith($skill['name'] . ': ', (string)($section['header'] ?? ''));
                $main = (string)($section['content'] ?? '');
                foreach (['What you can do', 'What is cool about it', 'Why use it'] as $heading) {
                    self::assertStringContainsString('<h3>' . $heading . '</h3>', $main, $skill['name']);
                }
                $sidebar = (string)($section['sidebar_content'] ?? '');
                self::assertStringContainsString('<strong>Say:</strong> “', $sidebar, $skill['name']);
                self::assertStringContainsString('<code>/' . $skill['name'] . '</code>', $sidebar);
                self::assertStringContainsString('<code>$' . $skill['name'] . '</code>', $sidebar);
                self::assertStringContainsString('<code>@' . $skill['name'] . '</code>', $sidebar);
                self::assertStringContainsString('href="https://github.com/dirnbauer/typo3-skills/blob/main/skills/' . $skill['name'] . '/SKILL.md"', $sidebar);
                self::assertStringContainsString('href="{{page:ai/agentic-skills/find}}&amp;tx_solr%5Bq%5D=' . $skill['name'] . '"', $sidebar);
                if ($skill['upstream'] !== '') {
                    self::assertStringContainsString('href="' . $skill['upstream'] . '"', $sidebar, $skill['name'] . ' names its upstream.');
                }
            }

            $help = $page['content'][2 + $count]['fields'];
            self::assertSame('Help improve these skills', $help['header'] ?? null);
            self::assertSame(ShowcaseSkillGroups::ISSUES_URL, $help['link'] ?? null);
            self::assertSame('{{page:ai/agentic-skills}}#install', $page['content'][3 + $count]['fields']['cta_link'] ?? null);
        }
    }

    public function testTheHubLeadsWithTheSuperfeatures(): void
    {
        $hub = self::hub();
        $hero = $hub['content'][0]['fields'];
        self::assertSame('desiderio_herosaas', $hub['content'][0]['ctype']);
        self::assertSame('{{page:ai/agentic-skills/find}}', $hero['primary_button_link'] ?? null);
        self::assertSame('{{page:ai/agentic-skills}}#install', $hero['secondary_button_link'] ?? null);

        // The second element; the hub's film follows it.
        $box = $hub['content'][1];
        self::assertSame('desiderio_featurehighlight', $box['ctype']);
        self::assertSame(ShowcaseSkillPages::SUPERFEATURE_HEADER, $box['fields']['header'] ?? null);
        self::assertSame('{{page:ai/agentic-skills/find}}', $box['fields']['link'] ?? null);
        preg_match_all('/<a href="\{\{page:ai\/agentic-skills\/find\}\}&amp;tx_solr%5Bq%5D=([a-z0-9-]+)">/', (string)($box['fields']['description'] ?? ''), $matches);
        $superfeatures = [];
        foreach (self::GROUPS as $skills) {
            foreach ($skills as $skill) {
                if (str_ends_with($skill, '*')) {
                    $superfeatures[] = rtrim($skill, '*');
                }
            }
        }
        self::assertSame($superfeatures, $matches[1]);

        $install = array_values(array_filter($hub['content'], static fn(array $block): bool => ($block['fields']['tx_desiderio_anchor'] ?? null) === 'install'));
        self::assertCount(1, $install, 'The hero and the group pages link #install.');
        self::assertSame('desiderio_featurenumbered', $install[0]['ctype']);
        self::assertCount(3, self::items($install[0]['fields']), 'Install script, a copied folder, a call by name.');
    }

    public function testTheHubLinksEveryGroupPageOnceInOrder(): void
    {
        $cards = self::block(self::hub(), 'desiderio_categorycards');
        $links = array_map(static fn(mixed $item): mixed => is_array($item) ? ($item['link'] ?? null) : null, self::items($cards));

        self::assertSame(
            array_map(static fn(string $group): string => '{{page:ai/agentic-skills/' . $group . '}}', array_keys(self::GROUPS)),
            $links
        );
    }

    public function testTheOtherSkillsLinkTheirGroup(): void
    {
        $strip = array_values(array_filter(self::hub()['content'], static fn(array $block): bool => ($block['fields']['header'] ?? null) === 'Also in the collection'));
        self::assertCount(1, $strip);

        $links = [];
        foreach (self::items($strip[0]['fields']) as $item) {
            self::assertIsArray($item);
            $links[(string)($item['title'] ?? '')] = $item['link'] ?? null;
        }
        self::assertSame(
            array_map(static fn(string $group): string => '{{page:ai/agentic-skills/' . $group . '}}', self::OTHERS),
            $links
        );
    }

    /**
     * Community talks by other people: each names its speaker, links its
     * materials and its event, keeps its German title marked as German, and
     * leads to the sections of the skills it relates to.
     */
    public function testTheCommunityTalksCreditTheirSpeakersAndLinkTheirMaterials(): void
    {
        $hub = self::hub();
        $ctypes = array_column($hub['content'], 'ctype');
        $talks = array_search('desiderio_timeline', $ctypes, true);
        $whatAndWhy = array_search('desiderio_featuresplit', $ctypes, true);
        self::assertIsInt($talks);
        self::assertIsInt($whatAndWhy);
        self::assertSame($whatAndWhy + 1, $talks, 'Right after what skills are and why they help.');
        $timeline = $hub['content'][$talks];
        self::assertStringContainsString('MTUG, the Munich TYPO3 User Group', (string)($timeline['fields']['subheadline'] ?? ''));

        $anchors = [];
        foreach (ShowcaseSkillGroups::groups() as $group) {
            foreach ($group['skills'] as $skill) {
                $anchors['{{page:ai/agentic-skills/' . $group['slug'] . '}}#' . $skill['name']] = true;
            }
        }
        $items = self::items($timeline['fields']);
        self::assertCount(5, $items);
        foreach (ShowcaseSkillGroups::talks() as $index => $talk) {
            $item = $items[$index];
            self::assertIsArray($item);
            self::assertSame($talk['materials'], $item['link'] ?? null);
            self::assertStringStartsWith('https://', $talk['materials']);
            self::assertStringStartsWith('https://', $talk['eventUrl']);
            $content = (string)($item['content'] ?? '');
            self::assertStringContainsString('<span lang="de">' . htmlspecialchars($talk['title'], ENT_NOQUOTES) . '</span>', $content);
            self::assertStringContainsString($talk['speaker'], $content);
            self::assertStringContainsString('href="' . $talk['eventUrl'] . '"', $content);
            preg_match_all('/href="(\{\{page:[^"]+)"/', $content, $matches);
            self::assertCount(count($talk['skills']), $matches[1]);
            foreach ($matches[1] as $link) {
                self::assertArrayHasKey($link, $anchors, $link . ' leads to a skill section.');
            }
        }
    }

    public function testTheDevCompanionBlockShowsTheDiagram(): void
    {
        $block = self::block(self::hub(), 'desiderio_textmedia');

        self::assertSame('The TYPO3 Dev Companion and our skills', $block['header'] ?? null);
        self::assertSame('https://github.com/TYPO3/dev-companion', $block['button_link'] ?? null);
        self::assertStringContainsString('Benjamin Kott', (string)($block['content'] ?? ''));
        self::assertStringContainsString('https://news.typo3.com/article/meet-the-typo3-dev-companion-typo3-knowledge-for-your-coding-agent', (string)($block['content'] ?? ''));
        $media = $block['media'] ?? null;
        self::assertIsArray($media);
        self::assertSame('Resources/Public/Styleguide/Frontend/frontend-dev-companion-skills-8f5c759d.webp', $media['file'] ?? null);
        self::assertSame('TYPO3 Dev Companion with typo3-skills', $media['title'] ?? null);
        self::assertSame(self::DIAGRAM_ALT, $media['alternative'] ?? null);
    }

    public function testTheOldSkillPagesRedirectToTheirSection(): void
    {
        $expected = [];
        foreach (self::RETIRED as $skill => $group) {
            $expected[self::HUB . '/' . $skill] = ['successor' => self::HUB . '/' . $group, 'anchor' => $skill];
        }

        self::assertSame($expected, ShowcaseSkillPages::retiredPages());
        self::assertSame($expected, StyleguideShowcasePages::retiredPages());
        $slugs = array_column(StyleguideShowcasePages::subpages(), 'slug');
        foreach ($expected as $slug => $retirement) {
            self::assertNotContains($slug, $slugs, $slug . ' is no page of its own any more.');
            $anchors = array_map(static fn(string $skill): string => rtrim($skill, '*'), self::GROUPS[basename($retirement['successor'])]);
            self::assertContains($retirement['anchor'], $anchors, $slug . ' redirects to an anchor its group page has.');
        }
    }

    public function testTheSkillSearchStaysTheHubsFirstChild(): void
    {
        self::assertSame(['/ai/agentic-skills/find' => 'ai/agentic-skills'], StyleguideShowcasePages::leadingChildPages());
    }

    public function testMenuTextsFitTheMenu(): void
    {
        foreach ([self::hub(), ...ShowcaseSkillPages::pages()] as $page) {
            self::assertLessThanOrEqual(50, mb_strlen($page['title']), $page['slug']);
            self::assertNotSame('', trim($page['navTitle']), $page['slug']);
            self::assertLessThanOrEqual(42, mb_strlen($page['navTitle']), $page['slug']);
            self::assertNotSame('', trim($page['abstract']), $page['slug']);
            self::assertLessThanOrEqual(160, mb_strlen($page['abstract']), $page['slug']);
            self::assertLessThanOrEqual(160, mb_strlen($page['description']), $page['slug']);
        }
    }

    /**
     * The hard limits of the content canon: headlines up to 60 characters
     * and 8 words, eyebrows and buttons up to 24 characters and 3 words,
     * sentences up to 25 words; "TYPO3 v14", never "TYPO3 14".
     */
    public function testTheCopyKeepsTheCanonLimits(): void
    {
        $problems = [];
        foreach ([self::hub(), ...ShowcaseSkillPages::pages()] as $page) {
            foreach ($page['content'] as $index => $block) {
                $where = $page['slug'] . ' #' . ($index + 1) . ' ' . $block['ctype'];
                $fields = $block['fields'];
                foreach (['header' => [60, 8], 'eyebrow' => [24, 3], 'badge_text' => [24, 3], 'link_text' => [24, 3], 'cta_text' => [24, 3], 'button_text' => [24, 3], 'primary_button_text' => [24, 3], 'secondary_button_text' => [24, 3]] as $field => [$chars, $words]) {
                    $value = $fields[$field] ?? null;
                    if (is_string($value) && (mb_strlen($value) > $chars || count(self::words($value)) > $words)) {
                        $problems[] = sprintf('%s: %s "%s" is over %d characters or %d words', $where, $field, $value, $chars, $words);
                    }
                }
                array_walk_recursive($fields, static function (mixed $value, int|string $key) use (&$problems, $where): void {
                    // Code is no prose.
                    if (!is_string($value) || str_starts_with($value, 'Resources/') || $key === 'code') {
                        return;
                    }
                    if (preg_match('/\bTYPO3 14\b/', $value) === 1) {
                        $problems[] = $where . ': "TYPO3 14" (write TYPO3 v14)';
                    }
                    foreach (self::sentences(self::plainText($value)) as $sentence) {
                        if (count(self::words($sentence)) > 25) {
                            $problems[] = $where . ': sentence over 25 words: ' . $sentence;
                        }
                    }
                });
            }
        }

        self::assertSame([], $problems);
    }

    /**
     * Links are placeholders, anchors on them, absolute URLs or nothing: a
     * plain "/path" gets no language prefix.
     */
    public function testNoLinkIsAPlainPath(): void
    {
        $problems = [];
        foreach ([self::hub(), ...ShowcaseSkillPages::pages()] as $page) {
            array_walk_recursive($page['content'], static function (mixed $value, int|string $key) use (&$problems, $page): void {
                if (!is_string($value)) {
                    return;
                }
                $links = [];
                if (is_string($key) && str_ends_with($key, 'link')) {
                    $links[] = $value;
                }
                preg_match_all('/href="([^"]*)"/', $value, $matches);
                array_push($links, ...$matches[1]);
                foreach ($links as $link) {
                    if ($link !== '' && preg_match('/^(\{\{(page|site):[^}]+\}\}([#&].*)?|https:\/\/\S+)$/', $link) !== 1) {
                        $problems[] = $page['slug'] . ': ' . $link;
                    }
                }
            });
        }

        self::assertSame([], $problems);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function screenshots(): iterable
    {
        $files = [];
        $content = [ShowcaseSkillPages::hubSections()];
        foreach (ShowcaseSkillPages::pages() as $page) {
            $content[] = $page['content'];
        }
        array_walk_recursive($content, static function (mixed $value, int|string $key) use (&$files): void {
            if ($key === 'file' && is_string($value)) {
                $files[$value] = true;
            }
        });
        foreach (array_keys($files) as $file) {
            yield basename($file) => [$file, basename($file)];
        }
    }

    #[DataProvider('screenshots')]
    public function testEveryScreenshotExists(string $file, string $name): void
    {
        self::assertFileExists(dirname(__DIR__, 2) . '/' . $file);
        self::assertMatchesRegularExpression('/^frontend-(feature-skills-[a-z0-9-]+|dev-companion-skills)-[0-9a-f]{8}\.webp$/', $name);
        // The name carries the start of the file's SHA-256, so a new picture
        // gets a new name (the FAL seeders never re-import a known name).
        $hash = hash_file('sha256', dirname(__DIR__, 2) . '/' . $file);
        self::assertIsString($hash);
        self::assertStringEndsWith(substr($hash, 0, 8) . '.webp', $name);
    }

    /**
     * The canon's own counting (site_package ContentAudit\CopyMetrics): inline
     * code is one word, so is a number with separators; sentences end at
     * punctuation and at the end of a paragraph or list item.
     */
    private static function plainText(string $value): string
    {
        $value = (string)preg_replace('#<code[^>]*>(.*?)</code>#is', '`$1`', $value);
        $value = (string)preg_replace('#</(p|li|h[1-6]|dt|dd|td|th|blockquote|div|pre)>|<br\s*/?>#i', "\n", $value);
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string)preg_replace('/\s*\n\s*/u', "\n", (string)preg_replace('/[ \t]+/u', ' ', $value)));
    }

    /**
     * @return list<string>
     */
    private static function words(string $text): array
    {
        $text = (string)preg_replace('/`[^`\n]+`/u', 'code', $text);
        preg_match_all('/\p{N}+(?:[.,]\p{N}+)+|[\p{L}\p{N}][\p{L}\p{N}\'’\-]*/u', $text, $matches);

        return array_values(array_filter(
            $matches[0],
            static fn(string $word): bool => preg_match('/\p{L}/u', $word) === 1 || preg_match('/^\p{N}+(?:[.,]\p{N}+)*$/u', $word) === 1
        ));
    }

    /**
     * @return list<string>
     */
    private static function sentences(string $text): array
    {
        $protected = (string)preg_replace('/\b(e\.g|i\.e|etc|e\.V)\./u', '$1§', $text);
        $protected = (string)preg_replace('/(\d)\.(?=\d)/u', '$1¤', $protected);
        $parts = preg_split('/(?<=[.!?…])\s+|(?<=[.!?…][”“"’»])\s+(?=[\p{Lu}\p{N}„“"«])|\n+/u', $protected);
        if ($parts === false) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $parts), static fn(string $part): bool => self::words($part) !== []));
    }

    /**
     * @return ShowcasePage
     */
    private static function hub(): array
    {
        foreach (ShowcaseFeaturePages::aiPages() as $page) {
            if ($page['slug'] === self::HUB) {
                return $page;
            }
        }
        self::fail('No hub page.');
    }

    /**
     * @return ShowcasePage
     */
    private static function groupPage(string $group): array
    {
        foreach (ShowcaseSkillPages::pages() as $page) {
            if ($page['slug'] === self::HUB . '/' . $group) {
                return $page;
            }
        }
        self::fail('No group page ' . $group);
    }

    /**
     * @param ShowcasePage $page
     * @return array<string, mixed>
     */
    private static function block(array $page, string $ctype): array
    {
        foreach ($page['content'] as $block) {
            if ($block['ctype'] === $ctype) {
                return $block['fields'];
            }
        }
        self::fail($page['slug'] . ' has no ' . $ctype);
    }

    /**
     * @param array<string, mixed> $fields
     * @return array<mixed>
     */
    private static function items(array $fields): array
    {
        $items = $fields['items'] ?? null;
        self::assertIsArray($items);

        return $items;
    }
}
