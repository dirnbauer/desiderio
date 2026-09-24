<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;
use Webconsulting\Desiderio\Library\CoreContentElements;
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
    public const int DEFAULT_PARENT_PID = 505;
    private const string STYLEGUIDE_FAL_FOLDER = 'desiderio-styleguide';

    /**
     * Core CTypes additionally cleared on seeder-owned showcase subpages so
     * legacy non-desiderio demo content does not linger next to the seeded
     * blocks. Never applied to the root page, which may hold other content.
     * The blog plugins cover the seeded category/tag helper pages.
     */
    private const array SHOWCASE_ADDITIONAL_CLEANUP_CTYPES = ['text', 'textmedia', 'html', 'bullets', 'blog_posts', 'blog_category', 'blog_tag'];

    private const string CONTENT_TYPES_PAGE_TITLE = 'Content types';
    private const string CONTENT_TYPES_PAGE_SLUG = '/content-types';
    private const int CONTENT_TYPES_PAGE_SORTING = 258;

    /**
     * Canonical public content-type chapters, in the same order as the cleaned
     * live tree below /content-types. TYPO3 core elements are deliberately not
     * part of this public menu.
     */
    private const array CONTENT_TYPE_GROUP_SLUGS = [
        'hero' => 'hero-landing-intros',
        'navigation' => 'navigation-wayfinding',
        'content' => 'content-editorial',
        'features' => 'features-benefits',
        'pricing' => 'plans-pricing',
        'social-proof' => 'trust-social-proof',
        'team' => 'people-team',
        'data' => 'data-dashboards',
        'conversion' => 'leads-conversion',
        'footer' => 'footers-utility-areas',
    ];

    /**
     * One house preset per content-type chapter so the seeded tree doubles as
     * a live theme showcase. Applied via pages.tx_desiderio_shadcn_preset and
     * picked up by the body tag TypoScript (levelfield slide). Every key of
     * CONTENT_TYPE_GROUP_SLUGS must appear here — ContentTypeChapterPresetTest
     * enforces it, which is what makes presetForContentTypeGroup() total.
     */
    private const array CONTENT_TYPE_GROUP_PRESETS = [
        'hero' => 'lagoon',
        'navigation' => 'gold',
        'content' => 'aurora',
        'features' => 'ember',
        'pricing' => 'midnight',
        'social-proof' => 'blossom',
        'team' => 'citrus',
        'data' => 'b27GcrRo',
        'conversion' => 'marine',
        'footer' => 'bloom',
    ];

    /**
     * What visitors read for a chapter preset: the name its card has on the
     * themes page, never the identifier (a create-page id such as b27GcrRo
     * means nothing to a reader). ContentTypeChapterPresetTest checks that
     * every chapter preset has a name and that the themes page uses it.
     */
    private const array PRESET_NAMES = [
        'b6G5977cw' => 'Lyra mono olive',
        'b0' => 'Default neutral',
        'b27GcrRo' => 'Rhea modern neutral',
        'b4hb38Fyj' => 'Olive product system',
        'b3IWPgRwnI' => 'Mist dashboard',
        'aurora' => 'Aurora',
        'marine' => 'Marine',
        'forest' => 'Forest',
        'ember' => 'Ember',
        'bloom' => 'Bloom',
        'lagoon' => 'Lagoon',
        'gold' => 'Gold',
        'midnight' => 'Midnight',
        'blossom' => 'Blossom',
        'citrus' => 'Citrus',
    ];

    private const array LEGACY_ROOT_PAGE_SLUGS = [
        '/desiderio-content',
        '/desiderio-conversion',
        '/desiderio-data',
        '/desiderio-features',
        '/desiderio-footer',
        '/desiderio-hero',
        '/desiderio-navigation',
        '/desiderio-pricing',
        '/desiderio-social-proof',
        '/desiderio-team',
        '/desiderio-core',
        '/for-agencies',
        '/for-inhouse-teams',
        '/for-freelancers',
    ];

    /**
     * @var list<array{title: string, slug: string, parentTarget: string, linkTarget: string, header: string, bodytext: string}>
     */
    private const array CONTENT_TYPE_SUPPORT_PAGES = [
        [
            'title' => 'Wayfinding patterns',
            'slug' => '/content-types/navigation-wayfinding/wayfinding-patterns',
            'parentTarget' => 'chapter-navigation',
            'linkTarget' => 'content-types/navigation-wayfinding/wayfinding-patterns',
            'header' => 'Breadcrumb demo: level 1 of 4',
            'bodytext' => '<p>These pages show the page breadcrumb. The trail above has <strong>three crumbs</strong>: the home icon, the parent chapter and this page. A short trail is shown in full.</p><p>Go one level deeper: <a href="{{page:content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails}}">Breadcrumb trails</a>.</p>',
        ],
        [
            'title' => 'Breadcrumb trails',
            'slug' => '/content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails',
            'parentTarget' => 'content-types/navigation-wayfinding/wayfinding-patterns',
            'linkTarget' => 'content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails',
            'header' => 'Breadcrumb demo: level 2 of 4',
            'bodytext' => '<p>The trail above now has <strong>four crumbs</strong>. This is the longest trail that is still shown in full.</p><p>Go one level deeper: <a href="{{page:content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour}}">Truncation behaviour</a>.</p>',
        ],
        [
            'title' => 'Truncation behaviour',
            'slug' => '/content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour',
            'parentTarget' => 'content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails',
            'linkTarget' => 'content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour',
            'header' => 'Breadcrumb demo: level 3 of 4',
            'bodytext' => '<p>Five crumbs are over the limit, so the trail above is shortened for the first time: <strong>home icon / &hellip; / parent / current page</strong>. Screen readers announce the ellipsis with a translated label (&ldquo;More pages&rdquo; / &ldquo;Weitere Seiten&rdquo;). The current page is plain text, never a link.</p><p>Go one level deeper: <a href="{{page:content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour/deeply-nested-example}}">Deeply nested example</a>.</p>',
        ],
        [
            'title' => 'Deeply nested example',
            'slug' => '/content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour/deeply-nested-example',
            'parentTarget' => 'content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour',
            'linkTarget' => 'content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour/deeply-nested-example',
            'header' => 'Breadcrumb demo: level 4 of 4',
            'bodytext' => '<p>This is the deepest page of the demo. The rootline has <strong>six crumbs</strong>, and the ellipsis now hides three pages at once. However deep the tree grows, the trail always shows four items: home icon, ellipsis, parent and current page.</p>',
        ],
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
                'include-video',
                null,
                InputOption::VALUE_NONE,
                'Opt in to seeding video content elements. Video components and generation tools remain available.'
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
        ProductionContextGuard::addOption($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentPid = (int)$input->getOption('parent');
        $dryRun = (bool)$input->getOption('dry-run');
        $skipPowermail = (bool)$input->getOption('skip-powermail');
        $skipNews = (bool)$input->getOption('skip-news');
        $includeVideo = (bool)$input->getOption('include-video');
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
        if (!ProductionContextGuard::allows($input, $io)) {

            return self::FAILURE;
        }
        $groups = $this->contentTypeGroups(StyleguideContentGroups::getGroupsWithFixtures());
        // A chapter's meta description states how many elements the group has,
        // counted before the video filter below: the video elements belong to
        // the group whether or not this run seeds a demo of them, and the
        // chapter intro and the content-types hub state the same number.
        $groupSizes = [];
        foreach ($groups as $group) {
            $groupSizes[(string)$group['groupId']] = count($group['elements']);
        }
        // With EXT:blog installed the success stories seed as real blog posts
        // and get hidden category/tag listing pages for their metadata badges.
        $blogAvailable = $this->isBlogSchemaAvailable();
        $showcasePages = StyleguideShowcasePages::subpages();
        if ($blogAvailable) {
            $showcasePages = array_merge($showcasePages, StyleguideShowcasePages::blogSupportPages());
        }
        if (!$includeVideo) {
            foreach ($groups as &$group) {
                $group['elements'] = array_values(array_filter(
                    $group['elements'],
                    static fn(array $element): bool => !str_contains(strtolower((string)$element['ctype']), 'video'),
                ));
            }
            unset($group);
            foreach ($showcasePages as &$showcasePage) {
                $showcasePage['content'] = array_values(array_filter(
                    $showcasePage['content'],
                    static fn(array $block): bool => !str_contains(strtolower((string)$block['ctype']), 'video'),
                ));
            }
            unset($showcasePage);
        }
        $totalElements = array_sum(array_map(
            static fn(array $group): int => count($group['elements']),
            $groups
        )) + $this->countChapterFramingElements($groups) + count(self::CONTENT_TYPE_SUPPORT_PAGES) + StyleguideShowcasePages::contentElementCount();

        if ($dryRun) {
            $io->title('Desiderio styleguide seed dry run');
            $listing = [];
            $listing[] = sprintf('Homepage (on parent page): %d marketing elements', count(StyleguideShowcasePages::homeContent()));
            $listing[] = sprintf('%s overview: %d content-type chapters', self::CONTENT_TYPES_PAGE_TITLE, count($groups));
            foreach ($showcasePages as $page) {
                $listing[] = sprintf('%s: %d elements', $page['title'], count($page['content']));
            }
            foreach ($groups as $index => $group) {
                $listing[] = sprintf(
                    '%s: %d elements — theme "%s"',
                    $group['groupTitle'],
                    count($group['elements']),
                    $this->presetForContentTypeGroup((string)$group['groupId'])
                );
            }
            foreach (self::CONTENT_TYPE_SUPPORT_PAGES as $page) {
                $listing[] = sprintf('%s: 1 breadcrumb demo element', $page['title']);
            }
            $io->listing($listing);
            if (!$skipPowermail) {
                $powermailForms = $this->getPowermailDemoSeeder()->getDemoForms();
                $io->listing(array_map(
                    static fn(array $form): string => sprintf('Powermail demo: %s', $form['pageTitleEn']),
                    $powermailForms
                ));
            }
            if (!$skipNews) {
                $io->listing(array_map(
                    static fn(array $news): string => sprintf('News demo: %s', $news['title']),
                    $this->getNewsDemoSeeder()->getDemoNews()
                ));
            }
            $io->success(sprintf(
                'Would create or update %d styleguide pages and %d content elements below page uid %d%s.',
                count($groups) + count(self::CONTENT_TYPE_SUPPORT_PAGES) + count($showcasePages),
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
        /** @var list<array{pageUid: int, ctype: string, name: string, fixture: array<string, mixed>, sorting: int}> $chapterContent */
        $chapterContent = [];

        $contentTypesPageAttributes = [
            'nav_title' => self::CONTENT_TYPES_PAGE_TITLE,
            ...$this->buildSeoPageAttributes(
                self::CONTENT_TYPES_PAGE_TITLE,
                'Desiderio content elements in 10 chapters: heroes, navigation, editorial content, features, pricing, trust, team, data, conversion and footers.'
            ),
        ];
        $contentTypesPageUid = $pageUpserter->findExistingPageUid(
            $parentPid,
            self::CONTENT_TYPES_PAGE_TITLE,
            self::CONTENT_TYPES_PAGE_SLUG,
            $pageColumns
        );
        if ($contentTypesPageUid === null) {
            $contentTypesPageUid = $pageUpserter->create(
                $parentPid,
                self::CONTENT_TYPES_PAGE_TITLE,
                self::CONTENT_TYPES_PAGE_SLUG,
                self::CONTENT_TYPES_PAGE_SORTING,
                $now,
                $pageColumns,
                $contentTypesPageAttributes
            );
            $createdPages++;
        } else {
            $pageUpserter->update(
                $contentTypesPageUid,
                self::CONTENT_TYPES_PAGE_TITLE,
                self::CONTENT_TYPES_PAGE_SLUG,
                self::CONTENT_TYPES_PAGE_SORTING,
                $now,
                $pageColumns,
                $contentTypesPageAttributes
            );
        }
        $linkTargets['content-types'] = $contentTypesPageUid;

        foreach ($groups as $index => $group) {
            $title = (string)$group['groupTitle'];
            $groupId = (string)$group['groupId'];
            $slug = $this->contentTypeSlugForGroup($groupId);
            $sorting = ($index + 1) * 256;

            $preset = $this->presetForContentTypeGroup($groupId);
            $pageAttributes = [
                'tx_desiderio_shadcn_preset' => $preset,
                ...$this->buildSeoPageAttributes($title, sprintf(
                    '%s: %d Desiderio content elements for TYPO3 v14, each with demo content. This chapter uses the %s theme preset.',
                    $title,
                    $groupSizes[$groupId] ?? count($group['elements']),
                    $this->presetName($preset)
                )),
            ];

            $pageUid = $pageUpserter->findExistingPageUid($contentTypesPageUid, $title, $slug, $pageColumns);
            if ($pageUid === null) {
                $pageUid = $pageUpserter->create($contentTypesPageUid, $title, $slug, $sorting, $now, $pageColumns, $pageAttributes);
                $createdPages++;
            } else {
                $pageUpserter->update($pageUid, $title, $slug, $sorting, $now, $pageColumns, $pageAttributes);
            }

            $linkTargets['chapter-' . $groupId] = $pageUid;

            $contentCleaner->softDeleteSeededContent($pageUid, $now, [], true);

            // The chapter content is inserted once every seeded page exists,
            // so {{page:<slug>}} placeholders in fixtures resolve to any of
            // them (a sitemap demo links pages created further down).
            // Benefit-led chapter intro above the element demos (sorting 128
            // slots it before the first demo at 256).
            $chapterIntro = StyleguideContentGroups::chapterIntro($groupId);
            if ($chapterIntro !== null) {
                $chapterContent[] = ['pageUid' => $pageUid, 'ctype' => 'desiderio_headersection', 'name' => 'Chapter intro', 'fixture' => $chapterIntro, 'sorting' => 128];
            }

            foreach ($group['elements'] as $elementIndex => $element) {
                $chapterContent[] = [
                    'pageUid' => $pageUid,
                    'ctype' => (string)$element['ctype'],
                    'name' => (string)$element['name'],
                    'fixture' => $element['fixture'],
                    'sorting' => ($elementIndex + 1) * 256,
                ];
            }

            // Closing conversion banner below the demos.
            $chapterCta = StyleguideContentGroups::chapterCta($groupId);
            if ($chapterCta !== null) {
                $chapterContent[] = [
                    'pageUid' => $pageUid,
                    'ctype' => 'desiderio_ctabanner',
                    'name' => 'Chapter CTA',
                    'fixture' => $chapterCta,
                    'sorting' => (count($group['elements']) + 1) * 256 + 128,
                ];
            }
        }

        $supportPageBlocks = $this->seedContentTypeSupportPages(
            $contentTypesPageUid,
            $linkTargets,
            $pageColumns,
            $pageUpserter,
            $now,
            $createdPages
        );

        // Marketing showcase: subpages first (so internal links resolve), then
        // homepage content on the parent page itself, then subpage content.
        $linkTargets['home'] = $parentPid;
        $showcaseBlocks = [];
        $blogPostsToRelate = [];
        foreach ($showcasePages as $index => $page) {
            $sorting = $page['slug'] === self::CONTENT_TYPES_PAGE_SLUG
                ? self::CONTENT_TYPES_PAGE_SORTING
                : ($index + 1) * 16;
            $pageAttributes = [
                'nav_title' => $page['navTitle'],
                'abstract' => $page['abstract'],
                ...$this->buildSeoPageAttributes($page['title'], $page['description']),
            ];
            if (isset($page['subtitle'])) {
                $pageAttributes['subtitle'] = $page['subtitle'];
            }
            if (isset($page['pageTsConfig'])) {
                $pageAttributes['TSconfig'] = $page['pageTsConfig'];
            }
            if ($page['hideInNav'] ?? false) {
                $pageAttributes['nav_hide'] = 1;
            }
            // A page that needs its own template says so (the themes page
            // renders a generated overview above the content area). Only
            // backend_layout is set, never backend_layout_next_level: the
            // special template belongs to this page, not to its subtree.
            if (isset($page['backendLayout'])) {
                $pageAttributes['backend_layout'] = $page['backendLayout'];
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

        $deletedLegacyPages = $this->softDeleteLegacyRootPages($parentPid, self::LEGACY_ROOT_PAGE_SLUGS, $now, $pageColumns, $contentCleaner);

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

        // The Powermail demo pages come before any content, so showcase links
        // to them (the strategy page's "Book a call") resolve on the first run.
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

        $showcaseBlocks[$parentPid] = StyleguideShowcasePages::homeContent();
        $linkTargets = $this->addSeededPageLinkTargets(
            $linkTargets,
            $parentPid,
            [$chapterContent, $supportPageBlocks, $showcaseBlocks]
        );

        foreach ($chapterContent as $content) {
            $contentElementSeeder->insert($content['pageUid'], $now, $this->getFixtureResolver()->buildContentInsert(
                $content['pageUid'],
                $content['ctype'],
                $content['name'],
                $this->substituteLinkPlaceholdersInFields($content['fixture'], $linkTargets),
                $content['sorting'],
                $now,
                $contentColumns
            ));
            $createdContentElements++;
        }

        foreach ($supportPageBlocks as $pageUid => $blocks) {
            $contentCleaner->softDeleteSeededContent($pageUid, $now, ['text'], true);
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
            'Created or updated %d styleguide pages (%d new) and inserted %d content elements below page uid %d%s%s%s.',
            count($groups) + count(self::CONTENT_TYPE_SUPPORT_PAGES) + count($showcasePages),
            $createdPages,
            $createdContentElements,
            $parentPid,
            $powermailSummary['skipped'] ? '' : sprintf(' Added %d powermail demo forms across %d EN/DE pages.', $powermailSummary['forms'], $powermailSummary['pages']),
            $newsSummary['skipped'] ? '' : sprintf(' Added %d news demo records (%d article content elements) across %d news pages.', $newsSummary['records'], $newsSummary['contentElements'], $newsSummary['pages']),
            $deletedLegacyPages > 0 ? sprintf(' Deleted %d legacy root pages.', $deletedLegacyPages) : ''
        ));

        return self::SUCCESS;
    }

    /**
     * @param list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, fixture: array<string, mixed>}>}> $groups
     * @return list<array{groupId: string, groupTitle: string, elements: list<array{name: string, ctype: string, fixture: array<string, mixed>}>}>
     */
    private function contentTypeGroups(array $groups): array
    {
        $groupsById = [];
        foreach ($groups as $group) {
            $groupId = (string)$group['groupId'];
            if (isset(self::CONTENT_TYPE_GROUP_SLUGS[$groupId])) {
                $groupsById[$groupId] = $group;
            }
        }

        $orderedGroups = [];
        foreach (array_keys(self::CONTENT_TYPE_GROUP_SLUGS) as $groupId) {
            if (isset($groupsById[$groupId])) {
                $orderedGroups[] = $groupsById[$groupId];
            }
        }

        return $orderedGroups;
    }

    /**
     * @param list<array{groupId: string, elements: list<array<string, mixed>>}> $groups
     */
    private function countChapterFramingElements(array $groups): int
    {
        $count = 0;
        foreach ($groups as $group) {
            $groupId = (string)$group['groupId'];
            if (StyleguideContentGroups::chapterIntro($groupId) !== null) {
                $count++;
            }
            if (StyleguideContentGroups::chapterCta($groupId) !== null) {
                $count++;
            }
        }

        return $count;
    }

    private function contentTypeSlugForGroup(string $groupId): string
    {
        return self::CONTENT_TYPES_PAGE_SLUG . '/' . self::CONTENT_TYPE_GROUP_SLUGS[$groupId];
    }

    private function presetForContentTypeGroup(string $groupId): string
    {
        return self::CONTENT_TYPE_GROUP_PRESETS[$groupId];
    }

    private function presetName(string $preset): string
    {
        return self::PRESET_NAMES[$preset] ?? ucfirst($preset);
    }

    /**
     * @param array<string, int> $linkTargets
     * @param array<string, true> $pageColumns
     * @param int $createdPages
     * @return array<int, list<array{ctype: string, colPos: int, fields: array<string, mixed>}>>
     */
    private function seedContentTypeSupportPages(
        int $contentTypesPageUid,
        array &$linkTargets,
        array $pageColumns,
        SeedPageUpserter $pageUpserter,
        int $now,
        int &$createdPages,
    ): array {
        $blocksByPageUid = [];
        foreach (self::CONTENT_TYPE_SUPPORT_PAGES as $page) {
            $pagePid = $linkTargets[$page['parentTarget']] ?? $contentTypesPageUid;
            $pageUid = $pageUpserter->findExistingPageUid($pagePid, $page['title'], $page['slug'], $pageColumns);
            $pageAttributes = $this->buildSeoPageAttributes(
                $page['title'],
                sprintf('A demo page for the Desiderio breadcrumb in the %s section. Together, these pages show how a long trail is shortened.', self::CONTENT_TYPES_PAGE_TITLE)
            );
            if ($pageUid === null) {
                $pageUid = $pageUpserter->create($pagePid, $page['title'], $page['slug'], 256, $now, $pageColumns, $pageAttributes);
                $createdPages++;
            } else {
                $pageUpserter->update($pageUid, $page['title'], $page['slug'], 256, $now, $pageColumns, $pageAttributes);
            }

            $linkTargets[$page['linkTarget']] = $pageUid;
            $blocksByPageUid[$pageUid] = [[
                'ctype' => 'text',
                'colPos' => 0,
                'fields' => [
                    'header' => $page['header'],
                    'bodytext' => $page['bodytext'],
                ],
            ]];
        }

        return $blocksByPageUid;
    }

    /**
     * @param list<string> $slugs
     * @param array<string, true> $pageColumns
     */
    private function softDeleteLegacyRootPages(
        int $parentPid,
        array $slugs,
        int $now,
        array $pageColumns,
        DesiderioContentCleaner $contentCleaner,
    ): int {
        if ($slugs === []) {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $rows = $queryBuilder
            ->select('uid', 'slug')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentPid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->in('slug', $queryBuilder->createNamedParameter($slugs, ArrayParameterType::STRING)),
                ...new LiveWorkspaceQueryHelper($this->databaseSchema)->buildLiveWorkspaceConstraints($queryBuilder, 'pages')
            )
            ->executeQuery()
            ->fetchAllAssociative();

        $deleted = 0;
        $connection = $this->connectionPool->getConnectionForTable('pages');
        foreach ($rows as $row) {
            $pageUid = is_numeric($row['uid'] ?? null) ? (int)$row['uid'] : 0;
            if ($pageUid <= 0) {
                continue;
            }

            $legacySlug = is_string($row['slug'] ?? null) ? $row['slug'] : '';
            $additionalCleanupCTypes = $legacySlug === '/desiderio-core'
                ? array_values(array_unique([...CoreContentElements::cTypes(), ...self::SHOWCASE_ADDITIONAL_CLEANUP_CTYPES]))
                : self::SHOWCASE_ADDITIONAL_CLEANUP_CTYPES;
            $contentCleaner->softDeleteSeededContent($pageUid, $now, $additionalCleanupCTypes, true);
            $connection->update(
                'pages',
                $this->databaseSchema->filterRow([
                    'deleted' => 1,
                    'tstamp' => $now,
                ], $pageColumns),
                ['uid' => $pageUid]
            );
            $deleted++;
        }

        return $deleted;
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
     * Replaces {{page:<slug>}} placeholders in showcase block fields and
     * element fixtures with t3://page links once the target pages exist.
     *
     * @param array{ctype: string, colPos: int, fields: array<string, mixed>} $block
     * @param array<string, int> $linkTargets
     * @return array{ctype: string, colPos: int, fields: array<string, mixed>}
     */
    private function substituteLinkPlaceholders(array $block, array $linkTargets): array
    {
        $block['fields'] = $this->substituteLinkPlaceholdersInFields($block['fields'], $linkTargets);

        return $block;
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, int> $linkTargets
     * @return array<string, mixed>
     */
    private function substituteLinkPlaceholdersInFields(array $fields, array $linkTargets): array
    {
        $resolved = [];
        foreach ($this->substituteLinkPlaceholdersInValue($fields, $linkTargets) as $key => $value) {
            if (is_string($key)) {
                $resolved[$key] = $value;
            }
        }

        return $resolved;
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
            if (!is_string($value) || !str_contains($value, '{{page:')) {
                continue;
            }

            $values[$key] = (string)preg_replace_callback(
                '/\{\{page:([^}]+)\}\}/',
                static fn(array $matches): string => isset($linkTargets[$matches[1]])
                    ? 't3://page?uid=' . $linkTargets[$matches[1]]
                    : 'https://github.com/dirnbauer/desiderio',
                $value
            );
        }

        return $values;
    }

    /**
     * Adds the pages that placeholders name but this command does not track
     * itself, such as the Powermail demo pages: found by slug (without the
     * leading slash) among the live default-language pages below the root.
     *
     * @param array<string, int> $linkTargets
     * @param list<array<array-key, mixed>> $valueSets
     * @return array<string, int>
     */
    private function addSeededPageLinkTargets(array $linkTargets, int $rootPid, array $valueSets): array
    {
        $slugs = [];
        array_walk_recursive($valueSets, static function (mixed $value) use (&$slugs): void {
            if (is_string($value) && preg_match_all('/\{\{page:([^}]+)\}\}/', $value, $matches) > 0) {
                foreach ($matches[1] as $slug) {
                    $slugs[$slug] = true;
                }
            }
        });

        foreach (array_keys($slugs) as $slug) {
            $slug = (string)$slug;
            if (isset($linkTargets[$slug])) {
                continue;
            }
            $pageUid = $this->findLivePageUidBelow($rootPid, '/' . $slug);
            if ($pageUid !== null) {
                $linkTargets[$slug] = $pageUid;
            }
        }

        return $linkTargets;
    }

    private function findLivePageUidBelow(int $rootPid, string $slug): ?int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();
        $constraints = [
            $queryBuilder->expr()->eq('slug', $queryBuilder->createNamedParameter($slug)),
            $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            ...new LiveWorkspaceQueryHelper($this->databaseSchema)->buildLiveWorkspaceConstraints($queryBuilder, 'pages'),
        ];
        if ($this->databaseSchema->tableHasColumn('pages', 'sys_language_uid')) {
            $constraints[] = $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER));
        }
        $rows = $queryBuilder
            ->select('uid', 'pid')
            ->from('pages')
            ->where(...$constraints)
            ->orderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $pageUid = is_numeric($row['uid'] ?? null) ? (int)$row['uid'] : 0;
            $pid = is_numeric($row['pid'] ?? null) ? (int)$row['pid'] : 0;
            if ($pageUid > 0 && $this->isPageBelow($pid, $rootPid)) {
                return $pageUid;
            }
        }

        return null;
    }

    /**
     * Whether $pid is $rootPid or one of its descendants (walks up at most
     * 20 levels, the depth of any seeded tree).
     */
    private function isPageBelow(int $pid, int $rootPid): bool
    {
        for ($level = 0; $pid > 0 && $level < 20; $level++) {
            if ($pid === $rootPid) {
                return true;
            }
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $queryBuilder->getRestrictions()->removeAll();
            $parent = $queryBuilder
                ->select('pid')
                ->from('pages')
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($pid, ParameterType::INTEGER)))
                ->executeQuery()
                ->fetchOne();
            $pid = is_numeric($parent) ? (int)$parent : 0;
        }

        return false;
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
