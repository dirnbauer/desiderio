<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;

/**
 * The ten one-skill pages below /ai/agentic-skills gave way to twelve group
 * pages. A reseed deletes an old page with its translation and content, and
 * redirects its URL and the redirects TYPO3 made for it to the skill's
 * anchor on its group page. The skill search /ai/agentic-skills/find belongs
 * to another package: the seeder leaves it where it is and sorts the group
 * pages after it.
 */
final class SeedStyleguidePagesCommandRetiredPagesFunctionalTest extends FunctionalTestCase
{
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
        // EXT:redirects is no dependency of Desiderio; its table, as far as
        // the seeder writes it, before the first seed reads the schema. Built
        // with the schema API so it works on SQLite, MariaDB and PostgreSQL;
        // MariaDB and PostgreSQL keep tables between the tests of a class
        // (they are only truncated), so it is created once.
        $schemaManager = $this->getConnectionPool()->getConnectionForTable('pages')->createSchemaManager();
        if (!$schemaManager->tablesExist(['sys_redirect'])) {
            $table = new Table('sys_redirect');
            $table->addColumn('uid', Types::INTEGER, ['autoincrement' => true, 'unsigned' => true]);
            foreach (['pid' => 0, 'deleted' => 0, 'createdon' => 0, 'updatedon' => 0, 'is_regexp' => 0, 'target_statuscode' => 307] as $name => $default) {
                $table->addColumn($name, Types::INTEGER, ['default' => $default]);
            }
            foreach (['source_host' => 255, 'source_path' => 2048, 'target' => 2048] as $name => $length) {
                $table->addColumn($name, Types::STRING, ['length' => $length, 'default' => '']);
            }
            $table->addColumn('description', Types::TEXT, ['notnull' => false]);
            $table->setPrimaryKey(['uid']);
            $schemaManager->createTable($table);
        }
    }

    public function testAFullSeedRetiresAnOldSkillPageAndKeepsTheSearchFirst(): void
    {
        $this->seed([]);
        $hubUid = $this->pageUid('/ai/agentic-skills');
        $groupUid = $this->pageUid('/ai/agentic-skills/code-migration');

        // The state before the regrouping: an old page with a translation, a
        // film and a text element, and the redirects TYPO3 made for it.
        $oldUid = $this->insertPage($hubUid, 'typo3-rector and typo3-fractor: automated fixes', '/ai/agentic-skills/typo3-rector', 736);
        $translationUid = $this->insertPage($hubUid, 'typo3-rector und typo3-fractor: automatische Korrekturen', '/ai/agentic-skills/typo3-rector', 736, ['sys_language_uid' => 1, 'l10n_parent' => $oldUid]);
        foreach (['desiderio_featurevideo', 'desiderio_ctabanner', 'text'] as $ctype) {
            $this->insertRow('tt_content', ['pid' => $oldUid, 'CType' => $ctype, 'header' => $ctype]);
        }
        $this->insertRow('tt_content', ['pid' => $oldUid, 'CType' => 'desiderio_ctabanner', 'header' => 'Übersetzt', 'sys_language_uid' => 1]);
        $languageRedirect = $this->insertRow('sys_redirect', ['source_host' => 'example.org', 'source_path' => '/de/ai/agentic-skills/typo3-rector-and-typo3-fractor-automated-fixes', 'target' => 't3://page?uid=' . $oldUid . '&_language=1']);
        $otherRedirect = $this->insertRow('sys_redirect', ['source_host' => 'example.org', 'source_path' => '/elsewhere', 'target' => 't3://page?uid=' . $oldUid . '0']);
        // The search, moved in by the site package behind the group pages.
        $searchUid = $this->insertPage($hubUid, 'Find a skill', '/ai/agentic-skills/find', 50000);

        $display = $this->seed([]);

        self::assertStringContainsString('Retired 1 replaced pages', $display);
        self::assertSame(1, $this->value('pages', 'deleted', $oldUid));
        self::assertSame(1, $this->value('pages', 'deleted', $translationUid), 'The translation goes with it.');
        self::assertSame(0, $this->countRows('tt_content', 'deleted = 0 AND pid = ' . $oldUid), 'Its content goes in every language.');

        $retired = $this->rowsWhere('sys_redirect', "description LIKE 'Desiderio showcase: /ai/agentic-skills/typo3-rector became%'");
        self::assertCount(1, $retired);
        self::assertSame('#^/((?:)?)ai/agentic\-skills/typo3\-rector/?$#', $retired[0]['source_path'] ?? null);
        self::assertSame('/$1ai/agentic-skills/code-migration#typo3-rector', $retired[0]['target'] ?? null);
        self::assertSame(301, (int)($retired[0]['target_statuscode'] ?? 0));
        self::assertSame('t3://page?uid=' . $groupUid . '&_language=1#typo3-rector', $this->stringValue('sys_redirect', 'target', $languageRedirect));
        self::assertSame('t3://page?uid=' . $oldUid . '0', $this->stringValue('sys_redirect', 'target', $otherRedirect), 'A redirect to another page stays.');

        // The search stays as it was, and every group page sorts after it.
        self::assertSame(0, $this->value('pages', 'deleted', $searchUid));
        self::assertSame(0, $this->value('pages', 'hidden', $searchUid));
        self::assertSame($hubUid, $this->value('pages', 'pid', $searchUid));
        self::assertSame(50000, $this->value('pages', 'sorting', $searchUid));
        $children = $this->rowsWhere('pages', 'deleted = 0 AND sys_language_uid = 0 AND pid = ' . $hubUid . ' ORDER BY sorting');
        self::assertSame(
            [
                '/ai/agentic-skills/find',
                '/ai/agentic-skills/proven-site-upgrade',
                '/ai/agentic-skills/upgrade-planning',
                '/ai/agentic-skills/code-migration',
                '/ai/agentic-skills/content-modelling',
                '/ai/agentic-skills/editor-experience',
                '/ai/agentic-skills/design-system',
                '/ai/agentic-skills/v14-development',
                '/ai/agentic-skills/accessibility',
                '/ai/agentic-skills/search-seo-agents',
                '/ai/agentic-skills/security-operations',
                '/ai/agentic-skills/testing-quality',
                '/ai/agentic-skills/release-open-source',
            ],
            array_column($children, 'slug')
        );

        // The skill sections carry the anchors the redirects point at.
        self::assertSame(
            ['typo3-extension-upgrade', 'typo3-rector', 'typo3-fractor', 'typo3-batch'],
            array_column($this->rowsWhere('tt_content', "deleted = 0 AND CType = 'desiderio_contentsidebar' AND pid = " . $groupUid . ' ORDER BY sorting'), 'tx_desiderio_anchor')
        );

        // A rerun finds the deleted page and only refreshes its redirects.
        $display = $this->seed([]);
        self::assertStringNotContainsString('Retired 1 replaced pages', $display);
        self::assertCount(1, $this->rowsWhere('sys_redirect', "description LIKE 'Desiderio showcase: /ai/agentic-skills/typo3-rector became%'"));
    }

    public function testASelectedGroupPageRetiresOnlyItsOwnOldPages(): void
    {
        $this->seed([]);
        $hubUid = $this->pageUid('/ai/agentic-skills');
        $webmcpUid = $this->insertPage($hubUid, 'typo3-webmcp: typed browser tools for agents', '/ai/agentic-skills/typo3-webmcp', 640);
        $rectorUid = $this->insertPage($hubUid, 'typo3-rector and typo3-fractor: automated fixes', '/ai/agentic-skills/typo3-rector', 736);

        $this->seed(['--pages' => '/pricing']);
        self::assertSame(0, $this->value('pages', 'deleted', $webmcpUid), 'A run that writes no group page retires nothing.');

        $display = $this->seed(['--pages' => '/ai/agentic-skills/search-seo-agents']);
        self::assertStringContainsString('Retired 1 replaced pages', $this->squash($display));
        self::assertSame(1, $this->value('pages', 'deleted', $webmcpUid));
        self::assertSame(0, $this->value('pages', 'deleted', $rectorUid), 'Its group page was not written.');
    }

    /**
     * @param array<string, mixed> $options
     */
    private function seed(array $options): string
    {
        $command = $this->get(SeedStyleguidePagesCommand::class);
        self::assertInstanceOf(SeedStyleguidePagesCommand::class, $command);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true, ...$options]);
        self::assertSame(Command::SUCCESS, $exitCode, $tester->getDisplay());

        return $this->squash($tester->getDisplay());
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function insertPage(int $pid, string $title, string $slug, int $sorting, array $fields = []): int
    {
        return $this->insertRow('pages', ['pid' => $pid, 'title' => $title, 'slug' => $slug, 'doktype' => 1, 'sorting' => $sorting, ...$fields]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function insertRow(string $table, array $row): int
    {
        $connection = $this->getConnectionPool()->getConnectionForTable($table);
        $connection->insert($table, $row);
        $uid = $connection->lastInsertId();
        self::assertTrue(is_numeric($uid));

        return (int)$uid;
    }

    private function pageUid(string $slug): int
    {
        $uid = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT uid FROM pages WHERE deleted = 0 AND sys_language_uid = 0 AND slug = ? LIMIT 1', [$slug])
            ->fetchOne();
        self::assertTrue(is_numeric($uid), 'Missing page with slug ' . $slug);

        return (int)$uid;
    }

    private function value(string $table, string $field, int $uid): int
    {
        $value = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT ' . $field . ' FROM ' . $table . ' WHERE uid = ?', [$uid])
            ->fetchOne();

        return is_numeric($value) ? (int)$value : -1;
    }

    private function stringValue(string $table, string $field, int $uid): string
    {
        $value = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT ' . $field . ' FROM ' . $table . ' WHERE uid = ?', [$uid])
            ->fetchOne();

        return is_string($value) ? $value : '';
    }

    private function countRows(string $table, string $where): int
    {
        $count = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function rowsWhere(string $table, string $where): array
    {
        return array_values($this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT * FROM ' . $table . ' WHERE ' . $where)
            ->fetchAllAssociative());
    }

    /**
     * The console wraps long messages; one line with single spaces reads
     * the same.
     */
    private function squash(string $display): string
    {
        return (string)preg_replace('/\s+/', ' ', str_replace([' ! ', '[ERROR]', '[OK]'], ' ', $display));
    }
}
