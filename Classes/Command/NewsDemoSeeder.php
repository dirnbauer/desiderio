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
    private const IMPORT_SOURCE = 'desiderio_styleguide_seed';
    private const NEWS_TABLE = 'tx_news_domain_model_news';
    private const REPO_URL = 'https://github.com/dirnbauer/desiderio';

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
                'title' => 'Desiderio 2.6 released: per-page theme presets',
                'teaser' => 'Any page subtree can now carry its own shadcn preset, inherited down the rootline and switched at runtime — campaign microsites without a second install.',
                'bodytext' => '<p>Version 2.6 introduces the per-page theme preset: set <code>tx_desiderio_shadcn_preset</code> on any page and the whole subtree repaints — colors, radius, typography, density — with no rebuild and no deployment. The styleguide seeder demonstrates it by rendering every element chapter in a different house preset.</p><p>Existing sites upgrade with a composer update; content records are untouched because themes live entirely in the OKLCH token layer.</p>',
                'daysAgo' => 3,
                'istopnews' => true,
            ],
            [
                'title' => 'WCAG 2.2 contrast solver lands in the theme generator',
                'teaser' => 'The preset generator now solves accent lightness per hue against 4.5:1 text and 3:1 UI targets — and refuses to emit CSS that fails.',
                'bodytext' => '<p>Accessibility moved from checklist to compiler: the theme generator solves each accent color against WCAG 2.2 contrast targets in light and dark mode before a preset ships. A unit test re-checks the committed bundle, so a failing combination cannot reach a release.</p><p>All fifteen presets pass — including the dark Midnight preset, where contrast bugs traditionally hide.</p>',
                'daysAgo' => 9,
                'istopnews' => false,
            ],
            [
                'title' => 'Friendly Captcha dev bypass: real protection, calm development',
                'teaser' => 'Production keeps the real captcha; Development context gets an automatic, logged bypass — plus a force-real switch for testing keys on ddev.',
                'bodytext' => '<p>The Friendly Captcha integration now ships a bypass matrix: real widget in Production, an automatic placeholder in Development context, and an explicit switch to test real keys locally. The bypass logs every use and refuses to activate in production, so the convenience cannot become a hole.</p><p>All eight bundled Form Framework definitions and the seeded Powermail demo forms use it out of the box.</p>',
                'daysAgo' => 16,
                'istopnews' => false,
            ],
            [
                'title' => 'The 255th content element has shipped',
                'teaser' => 'Ten groups, 244 elements, zero gaps: the library that started as a hero section now covers everything from KPI dashboards to GDPR request forms.',
                'bodytext' => '<p>With the latest additions the Desiderio library reaches 244 content elements across ten curated groups — every one with a backend preview, a demo fixture, and audited markup. The new-content wizard sorts them into plain-language groups, and the styleguide seeder builds a living example of each in seconds.</p><p>Still counted in the repository, still all in the free tier.</p>',
                'daysAgo' => 24,
                'istopnews' => false,
            ],
            [
                'title' => 'Creator Care launches: the maintainers run your updates',
                'teaser' => 'A €490/month retainer puts the people who built Desiderio in charge of your updates, upgrades, and uptime — so your team ships content, not patches.',
                'bodytext' => '<p>Creator Care is the new service tier for teams that want the stack maintained by its makers: TYPO3 and Desiderio updates, LTS upgrades, monitoring, and a direct line for questions — for €490 per month. It pairs with managed hosting from €99/month for a fully handed-off platform.</p><p>The free GPL core stays free; Creator Care buys time and guarantees, never features.</p>',
                'daysAgo' => 31,
                'istopnews' => false,
            ],
            [
                'title' => 'Solr search templates: facets, suggest, zero layout shift',
                'teaser' => 'The shadcn-styled Apache Solr UI ships complete: results, options facet, sorting, per-page switcher, and a header suggest dropdown that never shifts the layout.',
                'bodytext' => '<p>Search is the page visitors judge hardest, so the Solr template set covers all of it: result cards with stable truncation, an accessible facet sidebar, sort and per-page controls, and a suggest dropdown in the header that reserves its own space.</p><p>Every piece follows the active theme preset, including dark mode, with no extra styling work.</p>',
                'daysAgo' => 5,
                'istopnews' => false,
            ],
            [
                'title' => 'Visual Editor inline editing works across the whole library',
                'teaser' => 'Every Desiderio content element can be edited inline with TYPO3\'s Visual Editor — click the text on the page, type, done.',
                'bodytext' => '<p>The Visual Editor integration maps editable fields on all elements, so editors fix typos where they see them instead of hunting through backend forms.</p><p>Structured fields like collection items still open the full record for safety; plain text edits stay on the page.</p>',
                'daysAgo' => 7,
                'istopnews' => false,
            ],
            [
                'title' => 'Blog integration: t3g/blog posts in shadcn clothes',
                'teaser' => 'Post lists, metadata badges, sidebar widgets, comments, and a captcha-protected comment form — the t3g/blog overrides are complete and dark-mode ready.',
                'bodytext' => '<p>The blog template set styles the full t3g/blog surface: paginated post lists, category and tag badges near the title, a sticky sidebar with recent posts and tag cloud, and a comment form wired to Friendly Captcha.</p><p>The demo styleguide ships fifteen seeded fictional success stories to show it off.</p>',
                'daysAgo' => 11,
                'istopnews' => true,
            ],
            [
                'title' => 'Five icon libraries, one stable key',
                'teaser' => 'Lucide, Tabler, Phosphor, Remix, and a self-built HugeIcons set — switchable per site while stored content keeps stable icon keys.',
                'bodytext' => '<p>Icons are referenced by stable keys, so switching the site-wide icon library never rewrites content records. The HugeIcons set is generated from MIT-licensed SVG data and self-hosted — no CDN, no license surprises.</p><p>A sync script keeps the bundled webfonts reproducible from source.</p>',
                'daysAgo' => 13,
                'istopnews' => false,
            ],
            [
                'title' => 'Dark mode is not a filter, it is a second design',
                'teaser' => 'Every preset ships a hand-tuned dark token set with WCAG 2.2-checked contrast — visitors get their system preference, a header toggle lets them override.',
                'bodytext' => '<p>Dark mode in Desiderio is a first-class token set per preset, not an inverted afterthought. The contrast solver checks both modes against WCAG 2.2 targets before any preset ships.</p><p>The header toggle stores the visitor\'s override and respects the system preference by default.</p>',
                'daysAgo' => 18,
                'istopnews' => false,
            ],
            [
                'title' => 'Powermail, restyled: six demo flows seeded',
                'teaser' => 'All powermail field types wear the Desiderio form partials, and the seeder ships six complete demo forms with thank-you flows in English and German.',
                'bodytext' => '<p>The powermail integration restyles every field type with the same form partials the Form Framework bridge uses, so mixed installations look consistent.</p><p>Six seeded demo forms — contact to multi-step — double as living documentation, captcha included.</p>',
                'daysAgo' => 21,
                'istopnews' => false,
            ],
            [
                'title' => 'Eight Form Framework definitions, Brevo double opt-in included',
                'teaser' => 'Contact, newsletter, booking, download and more — ready YAML form definitions with a Brevo finisher that only subscribes confirmed addresses.',
                'bodytext' => '<p>The bundled form definitions cover the requests every site gets. The Brevo finisher implements proper double opt-in: contacts only join a list after clicking the confirmation mail.</p><p>All eight forms run the Friendly Captcha bypass matrix for calm local development.</p>',
                'daysAgo' => 26,
                'istopnews' => false,
            ],
            [
                'title' => 'Content Blocks 2.2 under the hood',
                'teaser' => 'All elements are declarative Content Blocks: schema in YAML, automatic columns, backend previews, and per-element demo fixtures the seeder feeds on.',
                'bodytext' => '<p>Content Blocks keep the element library honest: one config.yaml per element declares fields, collections, and previews — no hand-written TCA drift.</p><p>The demo fixtures double as seed data, which is how this entire demo site builds in one command.</p>',
                'daysAgo' => 29,
                'istopnews' => false,
            ],
            [
                'title' => 'Pagination that speaks your language',
                'teaser' => 'Page 3 of 12, Seite 3 von 12, 第3页 — ICU MessageFormat renders pagination, dates, and plural-sensitive copy correctly in every locale.',
                'bodytext' => '<p>Plural rules break naive translations, so every plural-sensitive string runs through ICU MessageFormat. Pagination, date lines, and counters localize correctly from German to Chinese.</p><p>Screen-reader labels travel through the same XLIFF pipeline as visible copy.</p>',
                'daysAgo' => 33,
                'istopnews' => false,
            ],
            [
                'title' => 'The template audit: eleven categories, zero tolerance',
                'teaser' => 'Hardcoded colors, missing ARIA, broken heading order — an 11-category audit runs across all 244 elements on every commit and fails the build on findings.',
                'bodytext' => '<p>Quality that is promised decays; quality that is measured survives. The template audit checks tokens-only styling, accessibility patterns, heading discipline, and translation coverage across the whole library.</p><p>The current finding count is zero, and CI keeps it there.</p>',
                'daysAgo' => 37,
                'istopnews' => false,
            ],
            [
                'title' => 'PHPStan level max, and staying there',
                'teaser' => 'The PHP underneath the elements — seeders, ViewHelpers, commands — analyses clean at PHPStan\'s strictest level with strict rules enabled.',
                'bodytext' => '<p>Level max with strict rules means no mixed left unchecked and no silent type coercion. The seeding infrastructure, icon registry, and CLI commands all pass.</p><p>Combined with 170+ tests on PHP 8.3 and 8.4, refactoring stays boring — the way it should be.</p>',
                'daysAgo' => 41,
                'istopnews' => false,
            ],
            [
                'title' => 'Element CSS, minified and manifest-driven',
                'teaser' => 'Per-element BEM files concatenate by manifest and minify at build time — styles ship exactly once and tree-shake with the elements you use.',
                'bodytext' => '<p>Every element owns its BEM stylesheet; a manifest concatenates and minifies the bundle at build time. A CI job fails when the Tailwind bundle drifts from the templates.</p><p>No CSS-in-JS, no runtime style computation, no hydration cost.</p>',
                'daysAgo' => 44,
                'istopnews' => false,
            ],
            [
                'title' => 'Corporate starter: a complete site in one command',
                'teaser' => 'desiderio:starter:seed builds a corporate site — pages, navigation, forms, legal pages — ready to retheme and rewrite.',
                'bodytext' => '<p>The starter seeder creates a complete corporate site with realistic structure: products, team, contact with working forms, and legal pages.</p><p>Like all Desiderio seeders it is idempotent and refuses to run in production without an explicit flag.</p>',
                'daysAgo' => 48,
                'istopnews' => false,
            ],
            [
                'title' => 'GEO: write pages machines can quote',
                'teaser' => 'Semantic landmarks, real data tables under every chart, and stable heading order make Desiderio pages quotable by AI search engines.',
                'bodytext' => '<p>Generative engines cite pages they can parse. Desiderio\'s server-rendered markup, accessible data tables, and disciplined heading hierarchy give them clean material to quote.</p><p>The GEO explainer page in this demo documents the patterns.</p>',
                'daysAgo' => 52,
                'istopnews' => false,
            ],
            [
                'title' => 'Team workshop: a day with the maintainers for €690',
                'teaser' => 'One day, your integrators and editors, the people who built the system — hands-on enablement for teams adopting Desiderio.',
                'bodytext' => '<p>The workshop covers element composition, theme presets, accessibility patterns, and the seeding workflow — tailored to your team\'s project.</p><p>It pairs well with the €890 installation service for teams that want a running start.</p>',
                'daysAgo' => 57,
                'istopnews' => false,
            ],
            [
                'title' => 'LTS upgrades from €2,400: the calm path to TYPO3 14',
                'teaser' => 'The maintainers move your Desiderio installation across LTS versions — scanner, Rector, tests, and a verified handover.',
                'bodytext' => '<p>Major upgrades are routine when the people who wrote the templates run them. The LTS service covers extension scanning, automated rector migrations, and a full test pass.</p><p>Fixed scope, fixed price, starting at €2,400 per installation.</p>',
                'daysAgo' => 63,
                'istopnews' => false,
            ],
            [
                'title' => 'Free, GPL, and on GitHub — that stays the deal',
                'teaser' => 'The core package remains GPL-2.0 with full source on GitHub. Paid tiers buy the maintainers\' time — never basic functionality.',
                'bodytext' => '<p>No feature gates, no open core bait: every element, preset, and integration ships in the free package. Paid offerings — Pro, Agency, services — buy speed, support, and the creators\' time.</p><p>Star it, fork it, audit it. That is the point.</p>',
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
            'abstract' => 'Desiderio project news, seeded as a demo of the shadcn-styled georgringer/news templates.',
            'description' => 'Desiderio project news: releases, accessibility engineering, and services — rendered with the shadcn-styled news templates that ship in the package.',
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
            'Twenty-two seeded articles with lead text and full content-element bodies, rendered by the shadcn-styled news templates — list, detail, and pagination follow the active theme preset automatically.',
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
            ['value' => '244', 'label' => 'Content elements', 'description_text' => 'Every one with a backend preview, demo fixture, and audited markup.'],
            ['value' => '15', 'label' => 'Theme presets', 'description_text' => 'Switchable at runtime, per site or per page subtree.'],
            ['value' => '170+', 'label' => 'Automated tests', 'description_text' => 'Unit and functional, on PHP 8.3 and 8.4.'],
            ['value' => '49', 'label' => 'Fluid components', 'description_text' => 'Typed f:argument contracts on every atom and molecule.'],
            ['value' => '11', 'label' => 'Audit categories', 'description_text' => 'Template quality is measured on every commit, at zero tolerance.'],
            ['value' => '2', 'label' => 'Color modes', 'description_text' => 'Light and dark ship together, both contrast-checked.'],
            ['value' => '5', 'label' => 'Icon libraries', 'description_text' => 'Swappable site-wide while content keeps stable icon keys.'],
            ['value' => '0', 'label' => 'Feature gates', 'description_text' => 'The GPL core is complete; paid tiers buy time, not switches.'],
        ];
        $quotePool = [
            ['quote_text' => 'We seed the whole demo site before the second coffee. The fact that this article exists is itself the feature demo.', 'author' => 'Desiderio maintainers', 'role' => 'release notes voice'],
            ['quote_text' => 'Editors stopped asking how something will look. The backend preview answers before the question forms.', 'author' => 'Desiderio maintainers', 'role' => 'from the field notes'],
            ['quote_text' => 'Accessibility is not a sprint at the end. It is the reason the build goes red in the middle.', 'author' => 'Desiderio maintainers', 'role' => 'engineering principles'],
        ];
        $faqPool = [
            ['title' => 'Does this work with my existing preset?', 'content' => 'Yes. Features land token-aware, so every shipped preset — and presets you generate on ui.shadcn.com/create — picks them up without changes.'],
            ['title' => 'Do I need to migrate content?', 'content' => 'No. Stored records keep stable keys for icons, presets, and element fields; updates never rewrite editorial content.'],
            ['title' => 'Is this in the free package?', 'content' => 'Yes. Everything described here ships in the GPL core on GitHub. Paid tiers buy the maintainers\' time, never the feature.'],
            ['title' => 'How do I try it locally?', 'content' => 'composer require webconsulting/desiderio, then run the styleguide seeder on a sandbox — the demo content shows every capability in minutes.'],
        ];
        $adoptionPool = [
            ['title' => 'Start on a sandbox', 'content' => 'Run the seeders on a ddev instance first; they are idempotent and refuse production without an explicit flag.'],
            ['title' => 'Check the styleguide page', 'content' => 'Every element involved ships a live example in the seeded styleguide chapters, themed in all house presets.'],
            ['title' => 'Read the audit output', 'content' => 'The 11-category template audit documents the markup contract this change honours — useful for custom elements too.'],
            ['title' => 'Mind the changelog', 'content' => 'Upgrades are composer updates; the changelog flags anything an integrator should review before deploying.'],
            ['title' => 'Pair it with presets', 'content' => 'Most features compose with per-page theme presets — test a campaign subtree before rolling out site-wide.'],
            ['title' => 'Lean on the tests', 'content' => 'The functional test suite covers the seeded structures, so CI tells you before an editor would.'],
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
                'link_text' => 'View the source on GitHub',
            ]),
            $this->block('desiderio_contentgrid', [
                'header' => 'The change in three points',
                'columns' => '3',
                'items' => [
                    ['title' => 'What shipped', 'content' => $news['teaser'], 'link' => ''],
                    ['title' => 'Why it matters', 'content' => 'Editors and integrators get the improvement without migrations: stored content keeps stable keys and the theme layer keeps its contract.', 'link' => ''],
                    ['title' => 'Where to see it', 'content' => 'The seeded styleguide renders a live example of every element involved — in all fifteen presets, light and dark.', 'link' => ''],
                ],
            ]),
            $this->block('desiderio_featurestats', [
                'header' => 'The package behind the headline',
                'description' => 'Context for this article: the numbers that stay true across releases.',
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
                'header' => 'Rolling it out',
                'subheadline' => 'What to check before this lands on a production site.',
                'variant' => 'left',
            ]),
            $this->block('desiderio_contentgrid', [
                'header' => 'Adoption notes',
                'columns' => '3',
                'items' => $adoption,
            ]),
            $this->block('desiderio_contenthighlight', [
                'header' => 'Try it in ten minutes',
                'content' => '<p>Install the free package with <code>composer require webconsulting/desiderio</code>, seed the styleguide on a sandbox, and open this article\'s topic in the element chapters. The demo you are reading was built exactly that way.</p>',
                'variant' => 'default',
                'alignment' => 'start',
                'link' => '',
                'link_text' => '',
            ]),
            $this->block('desiderio_quote', [
                'header' => '',
                'quote_text' => 'Demo content should make the same promises as the documentation — and then keep them in front of your eyes.',
                'author' => 'Desiderio maintainers',
                'role' => 'why these articles exist',
                'variant' => 'bordered',
            ]),
            $this->block('desiderio_ctabanner', [
                'header' => 'Ship your next site on the free core',
                'description' => 'Everything in this article is in the GPL package — and the maintainers install it for €890 if you want a running start.',
                'cta_text' => 'Get Desiderio on GitHub',
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
