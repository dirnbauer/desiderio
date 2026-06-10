<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

final class SeedStyleguidePagesCommandFunctionalTest extends FunctionalTestCase
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
        $exitCode = $tester->execute(['--parent' => '1', '--dry-run' => true, '--skip-powermail' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(1, $this->countRows('pages'));
        self::assertSame(0, $this->countRows('tt_content'));
    }

    public function testSeedCreatesStyleguidePagesAndContentElements(): void
    {
        $groups = StyleguideContentGroups::getGroupsWithFixtures();
        $expectedElements = array_sum(array_map(
            static fn (array $group): int => count($group['elements']),
            $groups
        )) + StyleguideShowcasePages::contentElementCount();
        $expectedPages = count($groups) + count(StyleguideShowcasePages::subpages());

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--skip-powermail' => true]);

        self::assertSame(Command::SUCCESS, $exitCode, $tester->getDisplay());
        self::assertSame($expectedPages, $this->countRows('pages', 'pid = 1 AND deleted = 0'));
        self::assertSame(
            $expectedElements,
            $this->countRows('tt_content', "deleted = 0 AND CType LIKE 'desiderio_%'")
        );
        // Styleguide pages must only carry Desiderio content elements.
        self::assertSame(
            0,
            $this->countRows('tt_content', "deleted = 0 AND CType NOT LIKE 'desiderio_%'")
        );
    }

    public function testSeedAssignsADistinctThemePresetPerPage(): void
    {
        $groups = StyleguideContentGroups::getGroupsWithFixtures();

        $tester = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $tester->execute(['--parent' => '1', '--skip-powermail' => true]), $tester->getDisplay());

        // Only the element chapter pages carry a theme preset; the showcase
        // pages (slug without the /desiderio- prefix) inherit the site setting.
        $presets = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery("SELECT tx_desiderio_shadcn_preset FROM pages WHERE pid = 1 AND deleted = 0 AND slug LIKE '/desiderio-%' ORDER BY sorting")
            ->fetchFirstColumn();

        self::assertCount(count($groups), $presets);
        $presetStrings = [];
        foreach ($presets as $preset) {
            self::assertIsString($preset);
            self::assertNotSame('', $preset, 'Every styleguide page must carry a theme preset.');
            $presetStrings[] = $preset;
        }
        // Up to ten pages every preset is unique; afterwards the cycle repeats.
        $expectedDistinct = min(count($presetStrings), 10);
        self::assertCount($expectedDistinct, array_unique(array_slice($presetStrings, 0, 10)));
    }

    public function testReseedingIsIdempotentForPagesAndLiveContent(): void
    {
        $groups = StyleguideContentGroups::getGroupsWithFixtures();
        $expectedElements = array_sum(array_map(
            static fn (array $group): int => count($group['elements']),
            $groups
        )) + StyleguideShowcasePages::contentElementCount();
        $expectedPages = count($groups) + count(StyleguideShowcasePages::subpages());

        $firstRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $firstRun->execute(['--parent' => '1', '--skip-powermail' => true]));

        $secondRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $secondRun->execute(['--parent' => '1', '--skip-powermail' => true]));

        self::assertStringContainsString('(0 new)', $secondRun->getDisplay());
        self::assertSame($expectedPages, $this->countRows('pages', 'pid = 1 AND deleted = 0'));
        // The previous generation is soft-deleted, the live set stays constant.
        self::assertSame(
            $expectedElements,
            $this->countRows('tt_content', "deleted = 0 AND CType LIKE 'desiderio_%'")
        );
        self::assertSame(
            $expectedElements,
            $this->countRows('tt_content', "deleted = 1 AND CType LIKE 'desiderio_%'")
        );
    }

    private function createCommandTester(): CommandTester
    {
        $command = $this->get(SeedStyleguidePagesCommand::class);
        self::assertInstanceOf(SeedStyleguidePagesCommand::class, $command);

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
}
