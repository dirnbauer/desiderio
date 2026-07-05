<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Library\CoreContentElements;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;
use Webconsulting\Desiderio\Seeding\BlogPageTreeSeeder;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\ContentElementSeeder;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\DesiderioContentCleaner;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\SeedPageUpserter;
use Webconsulting\Desiderio\Seeding\StarterContentBuilder;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;
use Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;

#[AsCommand(
    name: 'desiderio:styleguide:seed',
    description: 'Create or update shadcn styled Desiderio content element test pages below a parent page.'
)]
final class SeedStyleguidePagesCommand extends Command
{
    public const DEFAULT_PARENT_PID = 505;
    private const STYLEGUIDE_FAL_FOLDER = 'desiderio-styleguide';

    /**
     * Core CTypes additionally cleared on seeder-owned showcase subpages so
     * legacy non-desiderio demo content does not linger next to the seeded
     * blocks. Never applied to the root page, which may hold other content.
     * The blog plugins cover the seeded category/tag helper pages.
     */
    private const SHOWCASE_ADDITIONAL_CLEANUP_CTYPES = ['text', 'textmedia', 'html', 'bullets', 'blog_posts', 'blog_category', 'blog_tag'];

    /**
     * One house preset per styleguide page so the seeded tree doubles as a
     * live theme showcase. Applied via pages.tx_desiderio_shadcn_preset and
     * picked up by the body tag TypoScript (levelfield slide).
     */
    private const STYLEGUIDE_PAGE_PRESETS = [
        'aurora',
        'marine',
        'forest',
        'ember',
        'bloom',
        'lagoon',
        'gold',
        'midnight',
        'blossom',
        'citrus',
    ];

    private readonly StyleguideCollectionAliasPolicy $collectionAliasPolicy;
    private ?BlogPageTreeSeeder $blogPageTreeSeeder = null;
    private ?SeedPageUpserter $pageUpserter = null;
    private ?DesiderioContentCleaner $contentCleaner = null;
    private ?ContentElementSeeder $contentElementSeeder = null;
    private ?StyleguideFixtureResolver $fixtureResolver = null;
    private ?StarterContentBuilder $starterContentBuilder = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
        private readonly StorageRepository $storageRepository,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private ?PowermailDemoSeeder $powermailDemoSeeder = null,
        private ?NewsDemoSeeder $newsDemoSeeder = null,
        private readonly StyleguideDemoValueGenerator $demoValueGenerator = new StyleguideDemoValueGenerator(),
        ?StyleguideCollectionAliasPolicy $collectionAliasPolicy = null,
    ) {
        parent::__construct();
        $this->collectionAliasPolicy = $collectionAliasPolicy ?? new StyleguideCollectionAliasPolicy($this->databaseSchema);
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'Parent page uid for the generated content element test pages.',
                (string)self::DEFAULT_PARENT_PID
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Only print the planned pages and content element count.'
            )
            ->addOption(
                'allow-production',
                null,
                InputOption::VALUE_NONE,
                'Run even when Application Context is Production. Required to seed against production data.'
            )
            ->addOption(
                'skip-powermail',
                null,
                InputOption::VALUE_NONE,
                'Do not create the optional powermail demo form pages, even when powermail is installed.'
            )
            ->addOption(
                'skip-news',
                null,
                InputOption::VALUE_NONE,
                'Do not create the optional news demo section, even when georgringer/news is installed.'
            )
            ->addOption(
                'powermail-storage-pid',
                null,
                InputOption::VALUE_REQUIRED,
                'Storage page uid for generated powermail form records. Defaults to the generated Desiderio Powermail Lab page.',
                '0'
            )
            ->addOption(
                'powermail-german-language',
                null,
                InputOption::VALUE_REQUIRED,
                'sys_language_uid used for German powermail demo translations.',
                '1'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentPid = (int)$input->getOption('parent');
        $dryRun = (bool)$input->getOption('dry-run');
        $allowProduction = (bool)$input->getOption('allow-production');
        $skipPowermail = (bool)$input->getOption('skip-powermail');
        $skipNews = (bool)$input->getOption('skip-news');
        $powermailStoragePid = $this->getIntegerInputOption($input, 'powermail-storage-pid');
        $powermailGermanLanguageUid = $this->getIntegerInputOption($input, 'powermail-german-language');

        $workspaceId = (int)$this->context->getPropertyFromAspect('workspace', 'id', 0);
        if ($workspaceId !== 0) {
            $io->error(sprintf(
                'Refusing to seed inside workspace #%d. The seeder writes live records and bypasses workspace overlays. Switch to the live workspace before running this command.',
                $workspaceId
            ));

            return self::FAILURE;
        }

        if (!$allowProduction && Environment::getContext()->isProduction()) {
            $io->error('Refusing to run in Production application context. Pass --allow-production to override (and only do so on a sandbox).');

            return self::FAILURE;
        }
        $groups = StyleguideContentGroups::getGroupsWithFixtures();
        // With EXT:blog installed the success stories seed as real blog posts
        // and get hidden category/tag listing pages for their metadata badges.
        $blogAvailable = $this->isBlogSchemaAvailable();
        $showcasePages = StyleguideShowcasePages::subpages();
        if ($blogAvailable) {
            $showcasePages = array_merge($showcasePages, StyleguideShowcasePages::blogSupportPages());
        }
        $totalElements = array_sum(array_map(
            static fn (array $group): int => count($group['elements']),
            $groups
        )) + StyleguideShowcasePages::contentElementCount();

        if ($dryRun) {
            $io->title('Desiderio styleguide seed dry run');
            $listing = [];
            $listing[] = sprintf('Homepage (on parent page): %d marketing elements', count(StyleguideShowcasePages::homeContent()));
            foreach ($showcasePages as $page) {
                $listing[] = sprintf('%s: %d elements', $page['title'], count($page['content']));
            }
            foreach ($groups as $index => $group) {
                $listing[] = sprintf(
                    '%s: %d elements — theme "%s"',
                    $group['groupTitle'],
                    count($group['elements']),
                    $this->presetForPageIndex($index)
                );
            }
            $io->listing($listing);
            if (!$skipPowermail) {
                $powermailForms = $this->getPowermailDemoSeeder()->getDemoForms();
                $io->listing(array_map(
                    static fn (array $form): string => sprintf('Powermail demo: %s', $form['pageTitleEn']),
                    $powermailForms
                ));
            }
            if (!$skipNews) {
                $io->listing(array_map(
                    static fn (array $news): string => sprintf('News demo: %s', $news['title']),
                    $this->getNewsDemoSeeder()->getDemoNews()
                ));
            }
            $io->success(sprintf(
                'Would create or update %d styleguide pages and %d content elements below page uid %d%s.',
                count($groups) + count($showcasePages),
                $totalElements,
                $parentPid,
                $skipPowermail ? '' : sprintf(', plus %d powermail demo forms with EN/DE pages if powermail tables are available', count($this->getPowermailDemoSeeder()->getDemoForms()))
            ));

            return self::SUCCESS;
        }

        $pageColumns = $this->databaseSchema->getColumnNames('pages');
        $contentColumns = $this->databaseSchema->getColumnNames('tt_content');
        $createdPages = 0;
        $createdContentElements = 0;
        $now = time();

        $pageUpserter = $this->getPageUpserter();
        $contentCleaner = $this->getContentCleaner();
        $contentElementSeeder = $this->getContentElementSeeder();

        $linkTargets = [];

        foreach ($groups as $index => $group) {
            $title = (string)$group['groupTitle'];
            $slug = '/desiderio-' . (string)$group['groupId'];
            $sorting = ($index + 1) * 256;

            $preset = $this->presetForPageIndex($index);
            $pageAttributes = [
                'tx_desiderio_shadcn_preset' => $preset,
                ...$this->buildSeoPageAttributes($title, sprintf(
                    '%s: %d Desiderio content elements for TYPO3 14 with live demo content — rendered in the "%s" theme preset of the shadcn/ui design system.',
                    $title,
                    count($group['elements']),
                    ucfirst($preset)
                )),
            ];

            $pageUid = $pageUpserter->findExistingPageUid($parentPid, $title, $slug, $pageColumns);
            if ($pageUid === null) {
                $pageUid = $pageUpserter->create($parentPid, $title, $slug, $sorting, $now, $pageColumns, $pageAttributes);
                $createdPages++;
            } else {
                $pageUpserter->update($pageUid, $title, $slug, $sorting, $now, $pageColumns, $pageAttributes);
            }

            $linkTargets['chapter-' . (string)$group['groupId']] = $pageUid;

            // The core-elements page seeds native CTypes (text, table, menu_*, …),
            // which the default cleaner (desiderio_% only) would not soft-delete on
            // a re-seed — pass them explicitly so re-runs stay idempotent.
            $additionalCleanupCTypes = (string)$group['groupId'] === 'core' ? CoreContentElements::cTypes() : [];
            $contentCleaner->softDeleteSeededContent($pageUid, $now, $additionalCleanupCTypes, true);

            // Benefit-led chapter intro above the element demos (sorting 128
            // slots it before the first demo at 256).
            $chapterIntro = StyleguideContentGroups::chapterIntro((string)$group['groupId']);
            if ($chapterIntro !== null) {
                $contentElementSeeder->insert($pageUid, $now, $this->getFixtureResolver()->buildContentInsert(
                    $pageUid,
                    'desiderio_headersection',
                    'Chapter intro',
                    $chapterIntro,
                    128,
                    $now,
                    $contentColumns
                ));
                $createdContentElements++;
            }

            foreach ($group['elements'] as $elementIndex => $element) {
                $contentData = $this->getFixtureResolver()->buildContentInsert(
                    $pageUid,
                    (string)$element['ctype'],
                    (string)$element['name'],
                    $element['fixture'],
                    ($elementIndex + 1) * 256,
                    $now,
                    $contentColumns
                );

                $contentElementSeeder->insert($pageUid, $now, $contentData);
                $createdContentElements++;
            }

            // Closing conversion banner below the demos.
            $chapterCta = StyleguideContentGroups::chapterCta((string)$group['groupId']);
            if ($chapterCta !== null) {
                $contentElementSeeder->insert($pageUid, $now, $this->getFixtureResolver()->buildContentInsert(
                    $pageUid,
                    'desiderio_ctabanner',
                    'Chapter CTA',
                    $chapterCta,
                    (count($group['elements']) + 1) * 256 + 128,
                    $now,
                    $contentColumns
                ));
                $createdContentElements++;
            }
        }

        // Marketing showcase: subpages first (so internal links resolve), then
        // homepage content on the parent page itself, then subpage content.
        $linkTargets['home'] = $parentPid;
        $showcaseBlocks = [];
        $blogPostsToRelate = [];
        foreach ($showcasePages as $index => $page) {
            $sorting = ($index + 1) * 16;
            $pageAttributes = [
                'nav_title' => $page['navTitle'],
                'abstract' => $page['abstract'],
                ...$this->buildSeoPageAttributes($page['title'], $page['description']),
            ];
            if (isset($page['subtitle'])) {
                $pageAttributes['subtitle'] = $page['subtitle'];
            }
            if ($page['hideInNav'] ?? false) {
                $pageAttributes['nav_hide'] = 1;
            }

            $blogMeta = $page['blog'] ?? null;
            if ($blogAvailable && (($page['blogList'] ?? false) || is_array($blogMeta))) {
                $pageAttributes['backend_layout'] = BlogPageTreeSeeder::DEFAULT_BACKEND_LAYOUT;
                $pageAttributes['backend_layout_next_level'] = BlogPageTreeSeeder::DEFAULT_BACKEND_LAYOUT;
            }
            if ($blogAvailable && is_array($blogMeta)) {
                $publishDate = $this->getBlogPageTreeSeeder()->timestampFromDate($blogMeta['publishDate']);
                $pageAttributes['doktype'] = BlogPageTreeSeeder::BLOG_POST_DOKTYPE;
                $pageAttributes['publish_date'] = $publishDate;
                $pageAttributes['crdate_month'] = (int)date('n', $publishDate);
                $pageAttributes['crdate_year'] = (int)date('Y', $publishDate);
                $pageAttributes['comments_active'] = 1;
            }

            // Child pages (e.g. the success stories) live below their parent
            // showcase page; the parent is defined earlier in the list.
            $pagePid = $parentPid;
            $parentSlug = $page['parentSlug'] ?? null;
            if (is_string($parentSlug) && isset($linkTargets[$parentSlug])) {
                $pagePid = $linkTargets[$parentSlug];
            }

            $pageUid = $pageUpserter->findExistingPageUid($pagePid, $page['title'], $page['slug'], $pageColumns);
            if ($pageUid === null) {
                $pageUid = $pageUpserter->create($pagePid, $page['title'], $page['slug'], $sorting, $now, $pageColumns, $pageAttributes);
                $createdPages++;
            } else {
                $pageUpserter->update($pageUid, $page['title'], $page['slug'], $sorting, $now, $pageColumns, $pageAttributes);
            }

            if ($blogAvailable && is_array($blogMeta)) {
                $blogPostsToRelate[] = ['postUid' => $pageUid, 'storagePid' => $pagePid, 'meta' => $blogMeta];
            }

            $linkTargets[ltrim($page['slug'], '/')] = $pageUid;
            $showcaseBlocks[$pageUid] = $page['content'];
            if ($blogAvailable && ($page['blogList'] ?? false)) {
                // The list page leads with the paginated post list.
                array_unshift($showcaseBlocks[$pageUid], ['ctype' => 'blog_posts', 'colPos' => 0, 'fields' => []]);
            }
        }

        // Categories and tags live next to the posts on their list page so the
        // whole blog section stays inside the seeded subtree.
        foreach ($blogPostsToRelate as $blogPost) {
            $seeder = $this->getBlogPageTreeSeeder();
            $categoryUids = $seeder->ensureCategories($blogPost['storagePid'], $blogPost['meta']['categories']);
            $tagUids = $seeder->ensureTags($blogPost['storagePid'], $blogPost['meta']['tags']);
            $seeder->replaceCategoryRelations(
                $blogPost['postUid'],
                $seeder->mapTitlesToUids($blogPost['meta']['categories'], $categoryUids)
            );
            $seeder->replaceTagRelations(
                $blogPost['postUid'],
                $seeder->mapTitlesToUids($blogPost['meta']['tags'], $tagUids)
            );
        }

        $showcaseBlocks[$parentPid] = StyleguideShowcasePages::homeContent();

        foreach ($showcaseBlocks as $pageUid => $blocks) {
            // Showcase subpages are fully seeder-owned: clear leftover core
            // demo content (e.g. legacy text/textmedia articles) as well. The
            // root page may carry other site content, so it only gets the
            // regular desiderio_% cleanup.
            $additionalCTypes = $pageUid === $parentPid ? [] : self::SHOWCASE_ADDITIONAL_CLEANUP_CTYPES;
            $contentCleaner->softDeleteSeededContent($pageUid, $now, $additionalCTypes, true);
            foreach ($blocks as $blockIndex => $block) {
                $block = $this->substituteLinkPlaceholders($block, $linkTargets);
                $contentData = $this->getStarterContentBuilder()->buildContentInsert(
                    $pageUid,
                    $block,
                    ($blockIndex + 1) * 256,
                    $now,
                    $contentColumns
                );

                $contentElementSeeder->insert($pageUid, $now, $contentData);
                $createdContentElements++;
            }
        }

        $powermailSummary = ['pages' => 0, 'forms' => 0, 'skipped' => true];
        if (!$skipPowermail) {
            $powermailSummary = $this->getPowermailDemoSeeder()->seed(
                $parentPid,
                $powermailStoragePid,
                $powermailGermanLanguageUid,
                $now,
                $io
            );
        }

        $newsSummary = ['pages' => 0, 'records' => 0, 'contentElements' => 0, 'skipped' => true];
        if (!$skipNews) {
            $newsSummary = $this->getNewsDemoSeeder()->seed(
                $parentPid,
                $now,
                $io,
                $this->getStarterContentBuilder(),
                $this->getContentElementSeeder()
            );
        }

        $io->success(sprintf(
            'Created or updated %d styleguide pages (%d new) and inserted %d Desiderio content elements below page uid %d%s%s.',
            count($groups) + count($showcasePages),
            $createdPages,
            $createdContentElements,
            $parentPid,
            $powermailSummary['skipped'] ? '' : sprintf(' Added %d powermail demo forms across %d EN/DE pages.', $powermailSummary['forms'], $powermailSummary['pages']),
            $newsSummary['skipped'] ? '' : sprintf(' Added %d news demo records (%d article content elements) across %d news pages.', $newsSummary['records'], $newsSummary['contentElements'], $newsSummary['pages'])
        ));

        return self::SUCCESS;
    }

    private function presetForPageIndex(int $index): string
    {
        return self::STYLEGUIDE_PAGE_PRESETS[$index % count(self::STYLEGUIDE_PAGE_PRESETS)];
    }

    /**
     * SEO meta columns for a seeded page. The columns ship with EXT:seo;
     * SeedPageUpserter filters unknown columns, so this stays safe on
     * installations without the seo system extension.
     *
     * @return array<string, string>
     */
    private function buildSeoPageAttributes(string $title, string $description): array
    {
        return [
            'description' => $description,
            'og_title' => $title,
            'og_description' => $description,
            'twitter_title' => $title,
            'twitter_description' => $description,
        ];
    }

    /**
     * Replaces {{page:<slug>}} placeholders in showcase block fields with
     * t3://page links once the target pages exist.
     *
     * @param array{ctype: string, colPos: int, fields: array<string, mixed>} $block
     * @param array<string, int> $linkTargets
     * @return array{ctype: string, colPos: int, fields: array<string, mixed>}
     */
    private function substituteLinkPlaceholders(array $block, array $linkTargets): array
    {
        $fields = [];
        foreach ($this->substituteLinkPlaceholdersInValue($block['fields'], $linkTargets) as $key => $value) {
            if (is_string($key)) {
                $fields[$key] = $value;
            }
        }
        $block['fields'] = $fields;

        return $block;
    }

    /**
     * @param array<array-key, mixed> $values
     * @param array<string, int> $linkTargets
     * @return array<array-key, mixed>
     */
    private function substituteLinkPlaceholdersInValue(array $values, array $linkTargets): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->substituteLinkPlaceholdersInValue($value, $linkTargets);
                continue;
            }
            if (!is_string($value) || !str_starts_with($value, '{{page:')) {
                continue;
            }

            $slug = substr($value, 7, -2);
            $values[$key] = isset($linkTargets[$slug])
                ? 't3://page?uid=' . $linkTargets[$slug]
                : 'https://github.com/dirnbauer/desiderio';
        }

        return $values;
    }

    private function getStarterContentBuilder(): StarterContentBuilder
    {
        return $this->starterContentBuilder ??= new StarterContentBuilder($this->databaseSchema);
    }

    private function getPowermailDemoSeeder(): PowermailDemoSeeder
    {
        // DI provides the seeder; the fallback only serves direct instantiation in tests.
        return $this->powermailDemoSeeder ??= new PowermailDemoSeeder($this->connectionPool, $this->databaseSchema);
    }

    private function getNewsDemoSeeder(): NewsDemoSeeder
    {
        // DI provides the seeder; the fallback only serves direct instantiation in tests.
        return $this->newsDemoSeeder ??= new NewsDemoSeeder($this->connectionPool, $this->databaseSchema);
    }

    private function getIntegerInputOption(InputInterface $input, string $name): int
    {
        $value = $input->getOption($name);
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * EXT:blog is optional: the conversion of the success stories into blog
     * posts only happens when its schema (tag table plus the page columns it
     * adds) is present.
     */
    private function isBlogSchemaAvailable(): bool
    {
        return $this->databaseSchema->getColumnNames('tx_blog_domain_model_tag') !== []
            && $this->databaseSchema->tableHasColumn('pages', 'publish_date')
            && $this->databaseSchema->tableHasColumn('pages', 'comments_active');
    }

    private function getBlogPageTreeSeeder(): BlogPageTreeSeeder
    {
        return $this->blogPageTreeSeeder ??= new BlogPageTreeSeeder($this->connectionPool);
    }

    private function getPageUpserter(): SeedPageUpserter
    {
        return $this->pageUpserter ??= new SeedPageUpserter(
            $this->connectionPool,
            $this->databaseSchema,
            new LiveWorkspaceQueryHelper($this->databaseSchema),
        );
    }

    private function getContentCleaner(): DesiderioContentCleaner
    {
        if ($this->contentCleaner === null) {
            $liveWorkspaceQueryHelper = new LiveWorkspaceQueryHelper($this->databaseSchema);
            $this->contentCleaner = new DesiderioContentCleaner(
                $this->connectionPool,
                $liveWorkspaceQueryHelper,
                new CollectionCleanupService($this->connectionPool, $this->databaseSchema, $liveWorkspaceQueryHelper),
                new ContentBlockCollectionMap(),
            );
        }

        return $this->contentCleaner;
    }

    private function getContentElementSeeder(): ContentElementSeeder
    {
        return $this->contentElementSeeder ??= new ContentElementSeeder(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            self::STYLEGUIDE_FAL_FOLDER,
            1777100143,
        );
    }

    private function getFixtureResolver(): StyleguideFixtureResolver
    {
        return $this->fixtureResolver ??= new StyleguideFixtureResolver(
            $this->databaseSchema,
            $this->demoValueGenerator,
            $this->collectionAliasPolicy,
        );
    }
}
