<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedStarterSitesCommand;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;

final class SeedStarterSitesCommandFunctionalTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
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

    public function testDryRunWritesNothing(): void
    {
        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--dry-run' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('dry run', $tester->getDisplay());
        self::assertSame(1, $this->countRows('pages'));
        self::assertSame(0, $this->countRows('tt_content'));
    }

    public function testSeedCreatesCorporateStarterTreeWithContent(): void
    {
        $corporate = StarterSiteDefinitions::all()['corporate'];
        $expectedElements = count($corporate['home']['content']);
        foreach ($corporate['subpages'] as $subpage) {
            $expectedElements += count($subpage['content']);
        }

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--preset' => 'corporate']);

        self::assertSame(Command::SUCCESS, $exitCode, $tester->getDisplay());

        $rootUid = $this->fetchPageUidBySlug($corporate['rootSlug']);
        self::assertGreaterThan(0, $rootUid, 'Starter root page must exist');
        self::assertSame(
            count($corporate['subpages']),
            $this->countRows('pages', 'pid = ' . $rootUid . ' AND deleted = 0')
        );
        self::assertSame(
            $expectedElements,
            $this->countRows('tt_content', "deleted = 0 AND CType LIKE 'desiderio_%'")
        );
    }

    public function testRerunUpdatesPagesInsteadOfDuplicating(): void
    {
        $firstRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $firstRun->execute(['--parent' => '1', '--preset' => 'corporate']));
        $pagesAfterFirstRun = $this->countRows('pages', 'deleted = 0');

        $secondRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $secondRun->execute(['--parent' => '1', '--preset' => 'corporate']));

        self::assertStringContainsString('Created 0', $secondRun->getDisplay());
        self::assertSame($pagesAfterFirstRun, $this->countRows('pages', 'deleted = 0'));
    }

    public function testUnknownPresetFails(): void
    {
        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--preset' => 'does-not-exist']);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('Unknown starter preset', $tester->getDisplay());
    }

    public function testRootMapSeedsIntoExistingRootAndHidesUnmanagedChildren(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        $connection->insert('pages', [
            'uid' => 100,
            'pid' => 1,
            'title' => 'Existing root',
            'slug' => '/existing-root',
            'doktype' => 1,
        ]);
        $connection->insert('pages', [
            'uid' => 101,
            'pid' => 100,
            'title' => 'Unmanaged child',
            'slug' => '/existing-root/unmanaged',
            'doktype' => 1,
        ]);

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute([
            '--parent' => '1',
            '--preset' => 'corporate',
            '--root-map' => 'corporate:100',
            '--hide-unmanaged-children' => true,
        ]);

        self::assertSame(Command::SUCCESS, $exitCode, $tester->getDisplay());

        $corporate = StarterSiteDefinitions::all()['corporate'];
        $root = $connection
            ->executeQuery('SELECT title, slug FROM pages WHERE uid = 100')
            ->fetchAssociative();
        self::assertIsArray($root);
        self::assertSame($corporate['rootTitle'], $root['title']);
        self::assertSame('/', $root['slug']);

        $unmanagedHidden = $connection
            ->executeQuery('SELECT hidden FROM pages WHERE uid = 101')
            ->fetchOne();
        self::assertIsNumeric($unmanagedHidden);
        self::assertSame(1, (int)$unmanagedHidden, 'Unmanaged child page must be hidden');
    }

    private function createCommandTester(): CommandTester
    {
        $command = $this->get(SeedStarterSitesCommand::class);
        self::assertInstanceOf(SeedStarterSitesCommand::class, $command);

        return new CommandTester($command);
    }

    private function countRows(string $table, string $where = '1=1'): int
    {
        $count = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }

    private function fetchPageUidBySlug(string $slug): int
    {
        $uid = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT uid FROM pages WHERE slug = :slug AND deleted = 0', ['slug' => $slug])
            ->fetchOne();

        return is_numeric($uid) ? (int)$uid : 0;
    }
}
