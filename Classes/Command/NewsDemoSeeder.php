<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\ContentElementSeeder;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\DesiderioContentCleaner;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\StarterContentBuilder;

/**
 * Seeds an optional news section for the Desiderio styleguide: a storage
 * folder with 22 Desiderio-themed demo news records (each carrying header,
 * teaser lead text, AND ~10 related content elements) plus a single /news
 * page whose paginated news_pi1 plugin serves both views — the route enhancer maps
 * article URLs to News::detail inside the tx_news_pi1 namespace, so detail
 * pages need no extra subpage and URLs stay short (/news/<article-slug>).
 *
 * Like PowermailDemoSeeder, this class avoids hard references to news PHP
 * classes so Desiderio stays installable without georgringer/news. When the
 * tx_news tables are absent the seeder skips silently.
 *
 * @phpstan-type DemoNews array{title: string, teaser: string, bodytext: string, daysAgo: int, istopnews: bool}
 * @phpstan-type ArticleBlock array{ctype: string, colPos: int, fields: array<string, mixed>}
 */
final class NewsDemoSeeder
{
    private const string IMPORT_SOURCE = 'desiderio_styleguide_seed';
    private const string NEWS_TABLE = 'tx_news_domain_model_news';
    private const string REPO_URL = 'https://github.com/dirnbauer/desiderio';

    private ?DesiderioContentCleaner $contentCleaner = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly DatabaseSchemaHelper $databaseSchema,
    ) {}

    public function canSeed(): bool
    {
        return $this->databaseSchema->getColumnNames(self::NEWS_TABLE) !== [];
    }

    /**
     * @return list<DemoNews>
     */
    public function getDemoNews(): array
    {
        return [
            [
                'title' => 'Desiderio 2.6 adds theme presets per page',
                'teaser' => 'Any page tree can now have its own theme preset, and its subpages inherit it. A campaign site no longer needs a second installation.',
                'bodytext' => '<p>Version 2.6 adds a theme preset per page. Set <code>tx_desiderio_shadcn_preset</code> on a page, and its whole subtree changes colours, radius, typography and density. There is no rebuild and no deployment. The styleguide seeder shows it: each element chapter uses a different preset.</p><p>Existing sites upgrade with a Composer update. Content records stay as they are, because themes live only in the design token layer.</p>',
                'daysAgo' => 3,
                'istopnews' => true,
            ],
            [
                'title' => 'The theme generator checks WCAG 2.2 contrast',
                'teaser' => 'The preset generator sets each accent colour to reach 4.5:1 for text and 3:1 for controls. It never writes CSS that fails.',
                'bodytext' => '<p>The theme generator checks each accent colour against the WCAG 2.2 contrast targets, in light and dark mode, before a preset ships. A unit test checks the committed bundle again, so a failing combination cannot reach a release.</p><p>All 15 presets pass, including the dark Midnight preset, where contrast problems are most common.</p>',
                'daysAgo' => 9,
                'istopnews' => false,
            ],
            [
                'title' => 'A logged captcha bypass for local development',
                'teaser' => 'Production keeps the real Friendly Captcha. The Development context gets an automatic, logged bypass, and a switch lets you test real keys on DDEV.',
                'bodytext' => '<p>The Friendly Captcha integration now has three modes. Production shows the real widget, and the Development context shows an automatic placeholder. A switch lets you test real keys locally. Every bypass is logged, and the bypass never runs in Production.</p><p>All 8 ready Form Framework forms and the 6 Powermail demo forms use it by default.</p>',
                'daysAgo' => 16,
                'istopnews' => false,
            ],
            [
                'title' => 'The library now has 244 content elements',
                'teaser' => 'The library started with a hero section. Its 244 elements now cover 10 groups, from KPI dashboards to GDPR data-request forms.',
                'bodytext' => '<p>With the latest additions, the Desiderio library has 244 content elements in 10 groups. Each one has a backend preview, demo content and audited markup. The content wizard sorts them into groups with plain names, and the styleguide seeder builds an example of each in seconds.</p><p>The number is counted in the repository, and every element is in the free package.</p>',
                'daysAgo' => 24,
                'istopnews' => false,
            ],
            [
                'title' => 'Creator Care: maintainers run your updates',
                'teaser' => 'For €490 a month, the people who built Desiderio look after your updates, upgrades and uptime. Your team works on content, not patches.',
                'bodytext' => '<p>Creator Care is a new service for teams that want the makers to maintain their stack. It covers TYPO3 and Desiderio updates, LTS upgrades, monitoring and direct answers to your questions. It costs €490 per month for 4 hours of work.</p><p>Add managed hosting from €99 per month to hand off the whole platform. The GPL package stays free: Creator Care buys time and guarantees, never features.</p>',
                'daysAgo' => 31,
                'istopnews' => false,
            ],
            [
                'title' => 'Solr search templates with facets and suggest',
                'teaser' => 'The Apache Solr templates are complete: results, facets, sorting, page size and a suggest dropdown in the header that keeps the layout stable.',
                'bodytext' => '<p>The Solr templates cover the whole search page. Result cards shorten long text cleanly, the facet sidebar is accessible, and there are controls for sorting and page size. The suggest dropdown in the header reserves its own space.</p><p>Every part follows the active theme preset, including dark mode, with no extra styling. The search engine underneath is Apache Solr for TYPO3. Thanks to dkd Internet Service GmbH and the TYPO3-Solr team.</p>',
                'daysAgo' => 5,
                'istopnews' => false,
            ],
            [
                'title' => 'Inline editing for every element',
                'teaser' => 'You can edit every Desiderio content element inline in the TYPO3 Visual Editor. Click the text on the page and type.',
                'bodytext' => '<p>The Visual Editor integration maps the editable fields of every element. Editors fix a typo where they see it, instead of searching through backend forms.</p><p>Structured fields such as collection items still open the full record, to keep the data safe. Plain text changes stay on the page. Inline editing comes from the community Visual Editor. Thanks to its FriendsOfTYPO3 maintainers.</p>',
                'daysAgo' => 7,
                'istopnews' => false,
            ],
            [
                'title' => 't3g/blog posts in the Desiderio design',
                'teaser' => 'Post lists, metadata badges, sidebar widgets, comments and a comment form with captcha. The t3g/blog templates are complete and work in dark mode.',
                'bodytext' => '<p>The blog templates style all of t3g/blog. That includes paginated post lists, category and tag badges near the title, and a sticky sidebar with recent posts and a tag cloud. The comment form uses Friendly Captcha.</p><p>This demo site seeds 15 fictional success stories to show it. The Blog extension itself comes from TYPO3 GmbH. Thank you.</p>',
                'daysAgo' => 11,
                'istopnews' => true,
            ],
            [
                'title' => 'Five icon libraries with stable icon keys',
                'teaser' => 'Lucide, Tabler, Phosphor, Remix and a self-built HugeIcons set. You choose the library per site, and stored content keeps its icon keys.',
                'bodytext' => '<p>Content refers to icons by stable keys, so changing the icon library for a site never changes content records. The HugeIcons set is generated from MIT-licensed SVG data and hosted with the package. There is no CDN and no licence surprise.</p><p>A sync script rebuilds the bundled icon fonts from source.</p>',
                'daysAgo' => 13,
                'istopnews' => false,
            ],
            [
                'title' => 'Every preset has its own dark mode',
                'teaser' => 'Each preset has its own dark token set with WCAG 2.2 contrast checks. Visitors get their system setting, and a header toggle changes it.',
                'bodytext' => '<p>In Desiderio, dark mode is a full token set for each preset, not an inverted light theme. The contrast solver checks both modes against WCAG 2.2 before a preset ships.</p><p>The header toggle stores the visitor\'s choice. Until then, the page follows the system setting.</p>',
                'daysAgo' => 18,
                'istopnews' => false,
            ],
            [
                'title' => 'Powermail restyled, with 6 demo forms',
                'teaser' => 'Every Powermail field type uses the Desiderio form partials. The seeder adds 6 complete demo forms with thank-you pages in English and German.',
                'bodytext' => '<p>The Powermail integration styles every field type with the same form partials as the Form Framework bridge. Sites that use both look consistent.</p><p>The 6 demo forms, from a contact form to a multi-step request, also serve as documentation, with captcha included. Powermail is made by in2code and the Friendly Captcha extension by Studio Mitte. Thank you both.</p>',
                'daysAgo' => 21,
                'istopnews' => false,
            ],
            [
                'title' => '8 ready forms with Brevo double opt-in',
                'teaser' => 'Contact, newsletter, booking, download and more: ready form definitions in YAML. The Brevo finisher only subscribes confirmed addresses.',
                'bodytext' => '<p>The bundled forms cover the requests every site gets. The Brevo finisher uses real double opt-in: contacts join a list only after they click the link in the confirmation email.</p><p>All 8 forms use the Friendly Captcha bypass for local development.</p>',
                'daysAgo' => 26,
                'istopnews' => false,
            ],
            [
                'title' => 'Built on Content Blocks 2.2',
                'teaser' => 'Every element is a Content Block: fields in YAML, automatic database columns, backend previews and demo content that the seeder uses.',
                'bodytext' => '<p>Content Blocks keep the element library consistent. One config.yaml per element declares its fields, collections and preview, so there is no hand-written TCA to drift.</p><p>The demo content doubles as seed data. That is how this whole demo site is built with one command.</p>',
                'daysAgo' => 29,
                'istopnews' => false,
            ],
            [
                'title' => 'Pagination and dates in every language',
                'teaser' => 'Page 3 of 12, Seite 3 von 12, 第3页: ICU MessageFormat renders pagination, dates and plural forms correctly in every language.',
                'bodytext' => '<p>Plural rules break simple translations, so every text with a plural form goes through ICU MessageFormat. Pagination, date lines and counters are correct in every language, from German to Chinese.</p><p>Screen-reader labels use the same XLIFF files as the visible text.</p>',
                'daysAgo' => 33,
                'istopnews' => false,
            ],
            [
                'title' => 'A template audit that allows zero findings',
                'teaser' => 'Hard-coded colours, inline styles, unused fields: the template audit checks all 244 elements on every commit and fails the build on any finding.',
                'bodytext' => '<p>The template audit checks the whole library. It looks for hard-coded colours and styles, unused or undeclared fields, gaps in inline editing and CSS that ignores the design tokens.</p><p>The current number of findings is zero, and CI keeps it there.</p>',
                'daysAgo' => 37,
                'istopnews' => false,
            ],
            [
                'title' => 'PHPStan level 8, and it stays there',
                'teaser' => 'The PHP behind the elements, including seeders, ViewHelpers and commands, passes PHPStan level 8 with strict rules.',
                'bodytext' => '<p>Level 8 with strict rules catches null errors, loose comparisons and silent type conversion. The seeding code, the icon registry and the CLI commands all pass.</p><p>Unit and functional tests run on PHP 8.4 and 8.5, so a refactoring rarely brings surprises.</p>',
                'daysAgo' => 41,
                'istopnews' => false,
            ],
            [
                'title' => 'Element CSS is built from a manifest',
                'teaser' => 'Each element has its own BEM stylesheet. A manifest joins them and the build minifies the result, so every style ships once.',
                'bodytext' => '<p>Every element owns its BEM stylesheet, and a manifest joins and minifies the bundle at build time. A CI job fails when the Tailwind bundle no longer matches the templates.</p><p>There is no CSS-in-JS, no style calculation at runtime and no hydration cost.</p>',
                'daysAgo' => 44,
                'istopnews' => false,
            ],
            [
                'title' => 'A complete corporate site from one command',
                'teaser' => 'The command desiderio:starter:seed builds a corporate site with pages, navigation, forms and legal pages. You then change the theme and the text.',
                'bodytext' => '<p>The starter seeder creates a complete corporate site with a realistic structure: products, team, a contact page with working forms, and legal pages.</p><p>Like all Desiderio seeders, it can run again safely and refuses to run in Production without an explicit flag.</p>',
                'daysAgo' => 48,
                'istopnews' => false,
            ],
            [
                'title' => 'GEO: pages that AI search can quote',
                'teaser' => 'Semantic landmarks, real data tables under every chart and a clean heading order make Desiderio pages easy for AI search engines to quote.',
                'bodytext' => '<p>AI search engines quote pages they can parse. Desiderio renders its markup on the server, with accessible data tables and a clean heading order, so they get clear text to quote.</p><p>The GEO page on this demo site explains the patterns.</p>',
                'daysAgo' => 52,
                'istopnews' => false,
            ],
            [
                'title' => 'A workshop with the maintainers for €690',
                'teaser' => 'One day with your integrators, your editors and the people who built the system. A hands-on workshop for teams that start with Desiderio.',
                'bodytext' => '<p>The workshop covers building pages from elements, theme presets, accessibility patterns and the seeding workflow, all based on your own project.</p><p>It goes well with the €890 installation service if you want a fast start.</p>',
                'daysAgo' => 57,
                'istopnews' => false,
            ],
            [
                'title' => 'LTS upgrades to TYPO3 v14 from €2,400',
                'teaser' => 'The maintainers move your Desiderio installation to the next LTS version, with the Extension Scanner, Rector, tests and a checked handover.',
                'bodytext' => '<p>Major upgrades are routine when the people who wrote the templates run them. The LTS service covers the Extension Scanner, automated Rector migrations and a full test run.</p><p>Fixed scope and fixed price, from €2,400 per installation.</p>',
                'daysAgo' => 63,
                'istopnews' => false,
            ],
            [
                'title' => 'Desiderio stays free and open source',
                'teaser' => 'The core package stays GPL-2.0, with the full source on GitHub. Paid plans buy the maintainers\' time, never basic features.',
                'bodytext' => '<p>There are no feature gates and no paid-only core. Every element, preset and integration is in the free package. The paid offers, Pro, Agency and the services, buy speed, support and the makers\' time.</p><p>You can star it, fork it and audit it. That is the idea.</p>',
                'daysAgo' => 70,
                'istopnews' => false,
            ],
        ];
    }

    /**
     * @return array{pages: int, records: int, contentElements: int, skipped: bool}
     */
    public function seed(
        int $parentPid,
        int $now,
        SymfonyStyle $io,
        ?StarterContentBuilder $contentBuilder = null,
        ?ContentElementSeeder $elementSeeder = null,
    ): array {
        if (!$this->canSeed()) {
            $io->note('Skipping Desiderio news demo because the tx_news tables are not available.');
            return ['pages' => 0, 'records' => 0, 'contentElements' => 0, 'skipped' => true];
        }

        $pageColumns = $this->databaseSchema->getColumnNames('pages');
        $contentColumns = $this->databaseSchema->getColumnNames('tt_content');
        $newsColumns = $this->databaseSchema->getColumnNames(self::NEWS_TABLE);

        $storageUid = $this->upsertPage($parentPid, 'News storage (Desiderio demo)', '/desiderio-news-storage', 8448, $now, $pageColumns, [
            'doktype' => 254,
            'module' => 'news',
            'nav_hide' => 1,
        ]);
        $listUid = $this->upsertPage($parentPid, 'News', '/news', 8449, $now, $pageColumns, [
            'nav_title' => 'News',
            'abstract' => 'Desiderio project news, shown with the templates for the News extension that come with the package.',
            'description' => 'News about Desiderio: releases, accessibility work and services. The pages use the News templates that come with the Desiderio package.',
        ]);
        $this->softDeleteLegacyDetailPage($listUid, $now, $pageColumns);

        $this->softDeleteOwnedNewsRecords($now);
        $this->softDeleteSeededContent([$listUid, $storageUid], $now);

        $records = 0;
        $contentElements = 0;
        foreach ($this->getDemoNews() as $index => $news) {
            $newsUid = $this->insertRow(self::NEWS_TABLE, [
                'pid' => $storageUid,
                'type' => '0',
                'title' => $news['title'],
                'teaser' => $news['teaser'],
                'bodytext' => $news['bodytext'],
                'datetime' => $now - $news['daysAgo'] * 86400,
                'istopnews' => (int)$news['istopnews'],
                'path_segment' => $this->slugify($news['title']),
                'import_source' => self::IMPORT_SOURCE,
                'import_id' => 'desiderio-demo-' . ($index + 1),
                'sys_language_uid' => 0,
                'crdate' => $now,
                'tstamp' => $now,
            ], $newsColumns);
            $records++;

            $contentElements += $this->seedArticleElements(
                $newsUid,
                $storageUid,
                $index,
                $news,
                $now,
                $contentColumns,
                $newsColumns,
                $contentBuilder,
                $elementSeeder
            );
        }

        $this->insertHeaderSection(
            $listUid,
            'Desiderio news (demo)',
            '22 demo articles, each with a lead and a body made of content elements. The list, the detail view and the pagination follow the active theme preset.',
            256,
            $now,
            $contentColumns
        );
        $this->insertNewsPlugin($listUid, 'news_pi1', 'News', $storageUid, $listUid, $listUid, 512, $now, $contentColumns);

        return ['pages' => 2, 'records' => $records, 'contentElements' => $contentElements, 'skipped' => false];
    }

    /**
     * Inserts the ~10 related content elements that form an article body.
     * Requires the styleguide seeding helpers; without them (legacy callers)
     * the news records keep header and lead text only.
     *
     * @param DemoNews $news
     * @param array<string, true> $contentColumns
     * @param array<string, true> $newsColumns
     */
    private function seedArticleElements(
        int $newsUid,
        int $storageUid,
        int $newsIndex,
        array $news,
        int $now,
        array $contentColumns,
        array $newsColumns,
        ?StarterContentBuilder $contentBuilder,
        ?ContentElementSeeder $elementSeeder,
    ): int {
        if ($newsUid <= 0 || $contentBuilder === null || $elementSeeder === null) {
            return 0;
        }
        if (!isset($contentColumns['tx_news_related_news'])) {
            return 0;
        }

        $count = 0;
        foreach ($this->buildArticleBlocks($newsIndex, $news) as $blockIndex => $block) {
            $contentData = $contentBuilder->buildContentInsert(
                $storageUid,
                $block,
                ($blockIndex + 1) * 32,
                $now,
                $contentColumns
            );
            $contentData['row']['tx_news_related_news'] = $newsUid;
            $elementSeeder->insert($storageUid, $now, $contentData);
            $count++;
        }

        if ($count > 0 && isset($newsColumns['content_elements'])) {
            $this->connectionPool->getConnectionForTable(self::NEWS_TABLE)->update(
                self::NEWS_TABLE,
                ['content_elements' => $count],
                ['uid' => $newsUid]
            );
        }

        return $count;
    }

    /**
     * Standard editorial scaffold for a demo article: lead-in highlight,
     * three-point grid, stats, quote, FAQ accordion, divider, adoption notes,
     * compatibility accordion, try-it highlight, CTA — ten elements. Copy is
     * derived from the article plus rotating pools so neighbouring articles
     * do not read identically.
     *
     * @param DemoNews $news
     * @return list<ArticleBlock>
     */
    private function buildArticleBlocks(int $newsIndex, array $news): array
    {
        $statsPool = [
            ['value' => '244', 'label' => 'Content elements', 'description_text' => 'Each one has a backend preview, demo content and audited markup.'],
            ['value' => '15', 'label' => 'Theme presets', 'description_text' => 'They switch at runtime, for a whole site or for one page tree.'],
            ['value' => '8', 'label' => 'PHPStan level', 'description_text' => 'Static analysis with strict rules. CI runs on PHP 8.4 and 8.5.'],
            ['value' => '62', 'label' => 'Fluid components', 'description_text' => '17 atoms, 37 molecules, 4 layouts and 4 organisms, all with typed arguments.'],
            ['value' => '0', 'label' => 'Audit findings', 'description_text' => 'The template audit checks every element on each commit.'],
            ['value' => '2', 'label' => 'Colour modes', 'description_text' => 'Light and dark mode ship together, and both pass the contrast check.'],
            ['value' => '5', 'label' => 'Icon libraries', 'description_text' => 'You switch the library for the whole site. Content keeps its icon keys.'],
            ['value' => '0', 'label' => 'Feature gates', 'description_text' => 'The GPL package has every feature. Paid plans buy time, not features.'],
        ];
        $quotePool = [
            ['quote_text' => 'The seeder builds this whole demo site in one run. This article exists because of that feature.', 'author' => 'Desiderio maintainers', 'role' => 'Release notes'],
            ['quote_text' => 'Editors no longer ask how an element will look. The backend preview shows them first.', 'author' => 'Desiderio maintainers', 'role' => 'Project notes'],
            ['quote_text' => 'We check accessibility during development, not at the end. A failed contrast check stops the build.', 'author' => 'Desiderio maintainers', 'role' => 'Engineering principles'],
        ];
        $faqPool = [
            ['title' => 'Does this work with my current preset?', 'content' => 'Yes. New features use the design tokens, so every included preset picks them up. So do presets you create on ui.shadcn.com/create.'],
            ['title' => 'Do I need to migrate content?', 'content' => 'No. Stored records keep stable keys for icons, presets and element fields. Updates never change editorial content.'],
            ['title' => 'Is this in the free package?', 'content' => 'Yes. Everything in this article is in the GPL package on GitHub. Paid plans buy the maintainers\' time, never features.'],
            ['title' => 'How do I try it locally?', 'content' => 'Run composer require webconsulting/desiderio, then run the styleguide seeder on a test installation. The demo content shows every feature within minutes.'],
        ];
        $adoptionPool = [
            ['title' => 'Start on a test site', 'content' => 'Run the seeders on a DDEV instance first. They can run again safely and refuse to run in Production without an explicit flag.'],
            ['title' => 'Check the chapter pages', 'content' => 'Every element involved has a live example on the seeded chapter pages, and each chapter uses its own preset.'],
            ['title' => 'Read the audit report', 'content' => 'The template audit describes the markup rules this change follows. They help with custom elements too.'],
            ['title' => 'Read the changelog', 'content' => 'Upgrades are a Composer update. The changelog lists anything an integrator should review before deploying.'],
            ['title' => 'Combine it with presets', 'content' => 'Most features work with theme presets per page. Test them on a campaign page tree before you use them on the whole site.'],
            ['title' => 'Rely on the tests', 'content' => 'The functional tests cover the seeded structures, so CI finds a problem before an editor does.'],
        ];

        $stats = [];
        for ($statIndex = 0; $statIndex < 4; $statIndex++) {
            $stats[] = $statsPool[($newsIndex + $statIndex) % count($statsPool)];
        }
        $faqs = [];
        for ($faqIndex = 0; $faqIndex < 3; $faqIndex++) {
            $faq = $faqPool[($newsIndex + $faqIndex) % count($faqPool)];
            $faq['open_by_default'] = $faqIndex === 0 ? 1 : 0;
            $faqs[] = $faq;
        }
        $adoption = [];
        for ($noteIndex = 0; $noteIndex < 3; $noteIndex++) {
            $adoption[] = $adoptionPool[($newsIndex + $noteIndex) % count($adoptionPool)];
        }
        $quote = $quotePool[$newsIndex % count($quotePool)];

        return [
            $this->block('desiderio_contenthighlight', [
                'header' => 'At a glance',
                // First body paragraph, not the teaser — the article header
                // already renders the teaser directly above this highlight.
                'content' => substr($news['bodytext'], 0, ((int)strpos($news['bodytext'], '</p>')) + 4),
                'variant' => 'muted',
                'alignment' => 'start',
                'link' => self::REPO_URL,
                'link_text' => 'View on GitHub',
            ]),
            $this->block('desiderio_contentgrid', [
                'header' => 'The change in brief',
                'columns' => '3',
                'items' => [
                    ['title' => 'What shipped', 'content' => $news['teaser'], 'link' => ''],
                    ['title' => 'Why it matters', 'content' => 'You get the improvement without a migration. Stored content keeps its keys, and the theme layer works as before.', 'link' => ''],
                    ['title' => 'Where to see it', 'content' => 'The seeded chapter pages show a live example of each element involved, in light and dark mode.', 'link' => ''],
                ],
            ]),
            $this->block('desiderio_featurestats', [
                'header' => 'Desiderio in numbers',
                'description' => 'The facts behind this article, from the current release.',
                'items' => $stats,
            ]),
            $this->block('desiderio_quote', [
                'header' => '',
                'quote_text' => $quote['quote_text'],
                'author' => $quote['author'],
                'role' => $quote['role'],
                'variant' => 'large',
            ]),
            $this->block('desiderio_accordion', [
                'header' => 'Questions teams ask',
                'allow_multiple' => 1,
                'items' => $faqs,
            ]),
            $this->block('desiderio_headersection', [
                'eyebrow' => 'For integrators',
                'header' => 'Before you roll it out',
                'subheadline' => 'What to check before you use this on a live site.',
                'variant' => 'left',
            ]),
            $this->block('desiderio_contentgrid', [
                'header' => 'Rollout notes',
                'columns' => '3',
                'items' => $adoption,
            ]),
            $this->block('desiderio_contenthighlight', [
                'header' => 'Try it in 10 minutes',
                'content' => '<p>Install the free package with <code>composer require webconsulting/desiderio</code> and seed the demo site. The site you are reading was built the same way.</p>',
                'variant' => 'default',
                'alignment' => 'start',
                'link' => '',
                'link_text' => '',
            ]),
            $this->block('desiderio_quote', [
                'header' => '',
                'quote_text' => 'Demo content should make the same promises as the documentation, and show that they hold.',
                'author' => 'Desiderio maintainers',
                'role' => 'Why these articles exist',
                'variant' => 'bordered',
            ]),
            $this->block('desiderio_ctabanner', [
                'header' => 'Build your next site on the free package',
                'description' => 'Everything in this article is in the GPL package. For a fast start, the maintainers install it for €890.',
                'cta_text' => 'View on GitHub',
                'cta_link' => self::REPO_URL,
                'bg_style' => 'primary',
            ]),
        ];
    }

    /**
     * @param array<string, mixed> $fields
     * @return ArticleBlock
     */
    private function block(string $ctype, array $fields): array
    {
        return ['ctype' => $ctype, 'colPos' => 0, 'fields' => $fields];
    }

    /**
     * @param array<string, true> $columns
     */
    private function insertHeaderSection(int $pid, string $header, string $subheadline, int $sorting, int $now, array $columns): void
    {
        $this->insertRow('tt_content', [
            'pid' => $pid,
            'CType' => 'desiderio_headersection',
            'header' => $header,
            'subheadline' => $subheadline,
            'variant' => 'center',
            'colPos' => 0,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);
    }

    /**
     * @param array<string, true> $columns
     */
    private function insertNewsPlugin(
        int $pid,
        string $ctype,
        string $header,
        int $storageUid,
        int $listUid,
        int $detailUid,
        int $sorting,
        int $now,
        array $columns,
    ): void {
        $this->insertRow('tt_content', [
            'pid' => $pid,
            'CType' => $ctype,
            'header' => $header,
            'header_layout' => 100,
            'pi_flexform' => $this->buildNewsFlexform($storageUid, $listUid, $detailUid),
            'colPos' => 0,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);
    }

    private function buildNewsFlexform(int $storageUid, int $listUid, int $detailUid): string
    {
        $values = [
            'sDEF' => [
                'settings.orderBy' => 'datetime',
                'settings.orderDirection' => 'desc',
                'settings.startingpoint' => (string)$storageUid,
                'settings.recursive' => '0',
            ],
            'additional' => [
                'settings.detailPid' => (string)$detailUid,
                'settings.listPid' => (string)$listUid,
            ],
        ];

        $xml = "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?>\n<T3FlexForms>\n    <data>\n";
        foreach ($values as $sheet => $fields) {
            $xml .= '        <sheet index="' . $sheet . "\">\n            <language index=\"lDEF\">\n";
            foreach ($fields as $field => $value) {
                $xml .= '                <field index="' . htmlspecialchars($field, ENT_XML1) . '"><value index="vDEF">' . htmlspecialchars($value, ENT_XML1) . "</value></field>\n";
            }
            $xml .= "            </language>\n        </sheet>\n";
        }
        $xml .= "    </data>\n</T3FlexForms>";

        return $xml;
    }

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, true> $columns
     */
    private function upsertPage(int $pid, string $title, string $slug, int $sorting, int $now, array $columns, array $attributes = []): int
    {
        $existingUid = $this->findExistingPageUid($pid, $title, $slug, $columns);
        $row = $this->databaseSchema->filterRow([
            'pid' => $pid,
            'title' => $title,
            'doktype' => 1,
            'slug' => $slug,
            'hidden' => 0,
            'sorting' => $sorting,
            'crdate' => $now,
            'tstamp' => $now,
            ...$attributes,
        ], $columns);

        $connection = $this->connectionPool->getConnectionForTable('pages');
        if ($existingUid !== null) {
            unset($row['pid'], $row['crdate']);
            $connection->update('pages', $row, ['uid' => $existingUid]);
            return $existingUid;
        }

        $connection->insert('pages', $row);
        return $this->normalizeInteger($connection->lastInsertId());
    }

    /**
     * @param array<string, true> $columns
     */
    private function findExistingPageUid(int $pid, string $title, string $slug, array $columns): ?int
    {
        $where = ['pid = :pid', 'deleted = 0', '(title = :title OR slug = :slug)'];
        $parameters = ['pid' => $pid, 'title' => $title, 'slug' => $slug];
        $types = ['pid' => ParameterType::INTEGER, 'title' => ParameterType::STRING, 'slug' => ParameterType::STRING];

        if (isset($columns['sys_language_uid'])) {
            $where[] = 'sys_language_uid = 0';
        }
        if (isset($columns['t3ver_wsid'])) {
            $where[] = 't3ver_wsid = 0';
        }

        $uid = $this->connectionPool
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT uid FROM pages WHERE ' . implode(' AND ', $where) . ' ORDER BY uid DESC LIMIT 1', $parameters, $types)
            ->fetchOne();

        return is_numeric($uid) ? (int)$uid : null;
    }

    /**
     * Earlier seeder generations rendered the detail view on a hidden
     * /news/article subpage; detail now lives on the /news page itself, so a
     * leftover detail page (and its plugin) would keep the long URLs alive.
     *
     * @param array<string, true> $pageColumns
     */
    private function softDeleteLegacyDetailPage(int $listUid, int $now, array $pageColumns): void
    {
        $legacyUid = $this->findExistingPageUid($listUid, 'News article', '/news/article', $pageColumns);
        if ($legacyUid === null) {
            return;
        }

        $this->softDeleteSeededContent([$legacyUid], $now);
        $this->connectionPool
            ->getConnectionForTable('pages')
            ->update('pages', ['deleted' => 1, 'tstamp' => $now], ['uid' => $legacyUid]);
    }

    private function softDeleteOwnedNewsRecords(int $now): void
    {
        if (!$this->databaseSchema->tableHasColumn(self::NEWS_TABLE, 'import_source')) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::NEWS_TABLE);
        $queryBuilder
            ->update(self::NEWS_TABLE)
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq(
                    'import_source',
                    $queryBuilder->createNamedParameter(self::IMPORT_SOURCE)
                )
            )
            ->executeStatement();
    }

    /**
     * Soft-deletes the seeder-owned content on the news pages — the previous
     * generation of news plugins, Desiderio elements, and their collection
     * child records and file references.
     *
     * @param list<int> $pageUids
     */
    private function softDeleteSeededContent(array $pageUids, int $now): void
    {
        foreach ($pageUids as $pageUid) {
            $this->getContentCleaner()->softDeleteSeededContent($pageUid, $now, ['news_pi1'], true);
        }
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

    private function slugify(string $value): string
    {
        $slug = strtolower(trim((string)preg_replace('/[^A-Za-z0-9]+/', '-', $value), '-'));

        return $slug === '' ? 'desiderio-news' : $slug;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, true> $columns
     */
    private function insertRow(string $table, array $row, array $columns): int
    {
        $connection = $this->connectionPool->getConnectionForTable($table);
        $connection->insert($table, $this->databaseSchema->filterRow($row, $columns));

        return $this->normalizeInteger($connection->lastInsertId());
    }

    private function normalizeInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }
}
