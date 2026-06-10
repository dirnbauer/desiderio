<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;

/**
 * Seeds an optional news section for the Desiderio styleguide: a storage
 * folder with five Desiderio-themed demo news records, a /news listing page
 * with the news list plugin, and a hidden detail page with the news detail
 * plugin.
 *
 * Like PowermailDemoSeeder, this class avoids hard references to news PHP
 * classes so Desiderio stays installable without georgringer/news. When the
 * tx_news tables are absent the seeder skips silently.
 *
 * @phpstan-type DemoNews array{title: string, teaser: string, bodytext: string, daysAgo: int, istopnews: bool}
 */
final class NewsDemoSeeder
{
    private const IMPORT_SOURCE = 'desiderio_styleguide_seed';
    private const NEWS_TABLE = 'tx_news_domain_model_news';

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
                'teaser' => 'Ten groups, 255 elements, zero gaps: the library that started as a hero section now covers everything from KPI dashboards to GDPR request forms.',
                'bodytext' => '<p>With the latest additions the Desiderio library reaches 255 content elements across ten curated groups — every one with a backend preview, a demo fixture, and audited markup. The new-content wizard sorts them into plain-language groups, and the styleguide seeder builds a living example of each in seconds.</p><p>Still counted in the repository, still all in the free tier.</p>',
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
        ];
    }

    /**
     * @return array{pages: int, records: int, skipped: bool}
     */
    public function seed(int $parentPid, int $now, SymfonyStyle $io): array
    {
        if (!$this->canSeed()) {
            $io->note('Skipping Desiderio news demo because the tx_news tables are not available.');
            return ['pages' => 0, 'records' => 0, 'skipped' => true];
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
        $detailUid = $this->upsertPage($listUid, 'News article', '/news/article', 256, $now, $pageColumns, [
            'nav_hide' => 1,
        ]);

        $this->softDeleteOwnedNewsRecords($now);
        $this->softDeleteSeededContent([$listUid, $detailUid], $now);

        $records = 0;
        foreach ($this->getDemoNews() as $index => $news) {
            $this->insertRow(self::NEWS_TABLE, [
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
        }

        $this->insertHeaderSection(
            $listUid,
            'Desiderio news (demo)',
            'Five seeded articles rendered by the shadcn-styled news templates — list, detail, and pagination follow the active theme preset automatically.',
            256,
            $now,
            $contentColumns
        );
        $this->insertNewsPlugin($listUid, 'news_pi1', 'News list', $storageUid, $listUid, $detailUid, 512, $now, $contentColumns);
        $this->insertNewsPlugin($detailUid, 'news_newsdetail', 'News detail', $storageUid, $listUid, $detailUid, 256, $now, $contentColumns);

        return ['pages' => 3, 'records' => $records, 'skipped' => false];
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
     * Soft-deletes the seeder-owned content on the news pages: the previous
     * generation of news plugins and Desiderio elements.
     *
     * @param list<int> $pageUids
     */
    private function softDeleteSeededContent(array $pageUids, int $now): void
    {
        if ($pageUids === []) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter($pageUids, ArrayParameterType::INTEGER)
                ),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('desiderio\\_%')),
                    $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('news\\_%'))
                )
            )
            ->executeStatement();
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
