<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;

/**
 * `desiderio:styleguide:seed --pages=…` reseeds the selected pages only: a
 * production runner reseeds the pages whose fixtures changed instead of
 * the whole tree, then re-translates those pages.
 */
final class SeedStyleguidePagesCommandPageFilterFunctionalTest extends FunctionalTestCase
{
    private const int OLD_TSTAMP = 1000;

    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
        'form',
        'workspaces',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/desiderio',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SeedingBase.csv');
    }

    public function testASlugSelectorReseedsOnlyThatPage(): void
    {
        $this->seedFully();
        // Pages a full seed would move or delete, to prove a filtered run
        // leaves the global steps alone.
        $newsUid = $this->insertPage(1, 'News', '/news');
        $legacyUid = $this->insertPage(1, 'Legacy core', '/desiderio-core');
        $this->ageAllRows();
        $pagesBefore = $this->countRows('pages', 'deleted = 0');
        $contentBefore = $this->liveContentUidsByPid();
        $selectedUid = $this->pageUid('/pricing/how-we-earn');

        // Powermail is not installed here: a run that tried to seed its demo
        // forms would fail on the missing tables, so success proves it is
        // skipped without --skip-powermail.
        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--pages' => 'pricing/how-we-earn/']);
        $display = $tester->getDisplay();

        self::assertSame(Command::SUCCESS, $exitCode, $display);
        self::assertStringContainsString('Reseeded pages: ' . $selectedUid . "\n", $display);
        self::assertStringContainsString('sitepackage:content:translate --pages=' . $selectedUid, $this->squash($display));
        self::assertStringNotContainsString('news demo', $display);
        self::assertSame($pagesBefore, $this->countRows('pages', 'deleted = 0'));

        $contentAfter = $this->liveContentUidsByPid();
        self::assertNotSame([], $contentBefore[$selectedUid] ?? []);
        self::assertCount(count($contentBefore[$selectedUid]), $contentAfter[$selectedUid] ?? []);
        self::assertSame([], array_intersect($contentBefore[$selectedUid], $contentAfter[$selectedUid] ?? []), 'The selected page gets new content elements.');
        self::assertSame(
            count($contentBefore[$selectedUid]),
            $this->countRows('tt_content', 'deleted = 1 AND pid = ' . $selectedUid),
            'The previous content of the selected page is soft-deleted.'
        );
        unset($contentBefore[$selectedUid], $contentAfter[$selectedUid]);
        self::assertSame($contentBefore, $contentAfter, 'Every other page keeps its content elements.');

        self::assertSame(1, $this->countRows('pages', 'tstamp > ' . self::OLD_TSTAMP), 'Only the selected page is written.');
        self::assertGreaterThan(self::OLD_TSTAMP, $this->intValue($this->fetchPage($selectedUid), 'tstamp'));
        self::assertSame(0, $this->countRows('tt_content', 'deleted = 0 AND tstamp > ' . self::OLD_TSTAMP . ' AND pid <> ' . $selectedUid));
        self::assertSame(1, $this->intValue($this->fetchPage($newsUid), 'pid'), 'Menu placement is skipped.');
        self::assertSame(0, $this->intValue($this->fetchPage($legacyUid), 'deleted'), 'The legacy page cleanup is skipped.');
    }

    public function testUidAndHomeSelectorsReseedTheirPages(): void
    {
        $this->seedFully();
        $this->ageAllRows();
        $chapterUid = $this->pageUid('/content-types/hero-landing-intros');
        $contentBefore = $this->liveContentUidsByPid();

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--pages' => $chapterUid . ',home']);

        self::assertSame(Command::SUCCESS, $exitCode, $tester->getDisplay());
        // Ascending uids.
        self::assertStringContainsString('Reseeded pages: 1,' . $chapterUid . "\n", $tester->getDisplay());
        $contentAfter = $this->liveContentUidsByPid();
        foreach ([1, $chapterUid] as $pageUid) {
            self::assertCount(count($contentBefore[$pageUid]), $contentAfter[$pageUid] ?? [], 'page ' . $pageUid);
            self::assertSame([], array_intersect($contentBefore[$pageUid], $contentAfter[$pageUid] ?? []), 'page ' . $pageUid);
            unset($contentBefore[$pageUid], $contentAfter[$pageUid]);
        }
        self::assertSame($contentBefore, $contentAfter);
        // The chapter page is written; the parent page itself is not.
        self::assertSame([$chapterUid], $this->pageUidsWrittenAfterAgeing());
    }

    public function testAnUnknownSelectorFailsWithoutWriting(): void
    {
        $this->seedFully();
        $this->ageAllRows();

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--pages' => '/pricing,/no-such-page,999999']);
        $display = $this->squash($tester->getDisplay());

        self::assertSame(Command::FAILURE, $exitCode, $display);
        self::assertStringContainsString('No page of the styleguide plan matches /no-such-page, uid 999999.', $display);
        self::assertStringContainsString('/pricing/how-we-earn', $display);
        self::assertSame(0, $this->countRows('pages', 'tstamp > ' . self::OLD_TSTAMP));
        self::assertSame(0, $this->countRows('tt_content', 'tstamp > ' . self::OLD_TSTAMP));
    }

    public function testAMissingParentPageFailsAndAsksForAFullSeed(): void
    {
        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--pages' => '/pricing/how-we-earn']);
        $display = $this->squash($tester->getDisplay());

        self::assertSame(Command::FAILURE, $exitCode, $display);
        self::assertStringContainsString('which do not exist yet: /pricing', $display);
        self::assertStringContainsString('Run a full seed first', $display);
        self::assertSame(1, $this->countRows('pages'));
        self::assertSame(0, $this->countRows('tt_content'));
    }

    public function testDryRunListsOnlyTheSelectedPages(): void
    {
        $this->seedFully();
        $this->ageAllRows();
        $chapterUid = $this->pageUid('/content-types/hero-landing-intros');
        $howWeEarnUid = $this->pageUid('/pricing/how-we-earn');

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--dry-run' => true, '--pages' => '/pricing/how-we-earn,content-types/hero-landing-intros']);
        $display = $tester->getDisplay();

        self::assertSame(Command::SUCCESS, $exitCode, $display);
        $items = array_values(array_filter(
            array_map('trim', explode("\n", $display)),
            static fn(string $line): bool => str_starts_with($line, '* ')
        ));
        self::assertCount(2, $items, $display);
        self::assertStringContainsString('(/content-types/hero-landing-intros, uid ' . $chapterUid . '): ', $items[0]);
        self::assertStringContainsString('(/pricing/how-we-earn, uid ' . $howWeEarnUid . '): ', $items[1]);
        self::assertSame(
            $this->countRows('tt_content', 'deleted = 0 AND pid = ' . $chapterUid),
            (int)substr($items[0], (int)strrpos($items[0], ': ') + 2),
            'The listed element count matches what the page holds.'
        );
        self::assertStringContainsString('Would reseed 2 selected pages', $this->squash($display));
        self::assertSame(0, $this->countRows('pages', 'tstamp > ' . self::OLD_TSTAMP));
        self::assertSame(0, $this->countRows('tt_content', 'tstamp > ' . self::OLD_TSTAMP));
    }

    private function seedFully(): void
    {
        $tester = $this->createCommandTester();
        self::assertSame(
            Command::SUCCESS,
            $tester->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]),
            $tester->getDisplay()
        );
    }

    /**
     * Sets every timestamp back, so a write of the next run shows.
     */
    private function ageAllRows(): void
    {
        foreach (['pages', 'tt_content'] as $table) {
            $this->getConnectionPool()->getConnectionForTable($table)->executeStatement('UPDATE ' . $table . ' SET tstamp = ' . self::OLD_TSTAMP);
        }
    }

    /**
     * @return list<int>
     */
    private function pageUidsWrittenAfterAgeing(): array
    {
        $uids = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT uid FROM pages WHERE tstamp > ' . self::OLD_TSTAMP . ' ORDER BY uid')
            ->fetchFirstColumn();

        return array_map(static fn(mixed $uid): int => is_numeric($uid) ? (int)$uid : 0, $uids);
    }

    /**
     * @return array<int, list<int>>
     */
    private function liveContentUidsByPid(): array
    {
        $rows = $this->getConnectionPool()
            ->getConnectionForTable('tt_content')
            ->executeQuery('SELECT pid, uid FROM tt_content WHERE deleted = 0 ORDER BY pid, uid')
            ->fetchAllAssociative();
        $byPid = [];
        foreach ($rows as $row) {
            $byPid[$this->intValue($row, 'pid')][] = $this->intValue($row, 'uid');
        }

        return $byPid;
    }

    private function pageUid(string $slug): int
    {
        $uid = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT uid FROM pages WHERE deleted = 0 AND slug = ? LIMIT 1', [$slug])
            ->fetchOne();
        self::assertTrue(is_numeric($uid), 'Missing page with slug ' . $slug);

        return (int)$uid;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPage(int $uid): array
    {
        $row = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT * FROM pages WHERE uid = ?', [$uid])
            ->fetchAssociative();
        self::assertIsArray($row);

        return $row;
    }

    private function insertPage(int $pid, string $title, string $slug): int
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        $connection->insert('pages', ['pid' => $pid, 'title' => $title, 'slug' => $slug, 'doktype' => 1]);
        $uid = $connection->lastInsertId();
        self::assertTrue(is_numeric($uid));

        return (int)$uid;
    }

    private function createCommandTester(): CommandTester
    {
        $command = $this->get(SeedStyleguidePagesCommand::class);
        self::assertInstanceOf(SeedStyleguidePagesCommand::class, $command);

        return new CommandTester($command);
    }

    /**
     * The console wraps long messages; one line with single spaces reads
     * the same.
     */
    private function squash(string $display): string
    {
        return (string)preg_replace('/\s+/', ' ', str_replace([' ! ', '[ERROR]', '[OK]'], ' ', $display));
    }

    /**
     * @param array<string, mixed> $row
     */
    private function intValue(array $row, string $key): int
    {
        $value = $row[$key] ?? null;

        return is_numeric($value) ? (int)$value : 0;
    }

    private function countRows(string $table, string $where = '1=1'): int
    {
        $count = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }
}
