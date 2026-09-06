<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Seeding;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedElementLibraryCommand;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Library\CoreContentElements;

final class LibraryElementUpserterFunctionalTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'webconsulting/desiderio',
        __DIR__ . '/Fixtures/Extensions/desiderio_test_provider',
    ];

    protected array $configurationToUseInTestInstance = [
        'EXTENSIONS' => ['desiderio' => ['libraryHostExtensions' => ['desiderio_test_provider']]],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        ContentBlockDefinitionRegistry::resetCache();
        $this->importCSVDataSet(__DIR__ . '/../Command/Fixtures/SeedingBase.csv');
    }

    protected function tearDown(): void
    {
        ContentBlockDefinitionRegistry::resetCache();
        parent::tearDown();
    }

    public function testRegisteredProviderReseedingReplacesNestedChildrenAndPreservesUnrelatedRecords(): void
    {
        $this->seedLibrary('desiderio_test_provider');

        $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
        $content = $connection->select(['uid', 'header'], 'tt_content', ['CType' => 'desideriotest_nested'])->fetchAssociative();
        self::assertIsArray($content);
        self::assertSame('Library provider demo', $content['header']);
        self::assertIsNumeric($content['uid']);
        $contentUid = (int)$content['uid'];

        $sections = $connection->select(['uid', 'title'], 'desiderio_test_sections', [])->fetchAllKeyValue();
        self::assertSame(['Provider section'], array_values($sections));
        $oldSectionUid = (int)array_key_first($sections);
        $links = $connection->select(['uid', 'label'], 'desiderio_test_links', [])->fetchAllKeyValue();
        self::assertSame(['Provider nested link'], array_values($links));
        $oldLinkUid = (int)array_key_first($links);

        // A second content record exercises the ownership boundary: reseeding
        // the library must replace its own children, never these neighbors.
        $connection->insert('tt_content', ['pid' => 1, 'CType' => 'desideriotest_nested', 'header' => 'Unrelated content']);
        $unrelatedContentUid = (int)$connection->lastInsertId();
        $connection->insert('desiderio_test_sections', ['foreign_table_parent_uid' => $unrelatedContentUid, 'title' => 'Unrelated section']);
        $unrelatedSectionUid = (int)$connection->lastInsertId();
        $connection->insert('desiderio_test_links', ['foreign_table_parent_uid' => $unrelatedSectionUid, 'label' => 'Unrelated link']);
        $unrelatedLinkUid = (int)$connection->lastInsertId();
        $connection->insert('desiderio_test_links', ['foreign_table_parent_uid' => $oldSectionUid, 'label' => 'Obsolete nested link']);

        $this->seedLibrary('desiderio_test_provider');

        self::assertSame('Library provider demo', $connection->select(['header'], 'tt_content', ['uid' => $contentUid])->fetchOne());
        self::assertSame(2, $connection->count('*', 'tt_content', []));
        self::assertSame(0, $connection->count('*', 'desiderio_test_sections', ['uid' => $oldSectionUid]));
        self::assertSame(0, $connection->count('*', 'desiderio_test_links', ['uid' => $oldLinkUid]));
        self::assertSame(0, $connection->count('*', 'desiderio_test_links', ['foreign_table_parent_uid' => $oldSectionUid]));
        self::assertSame(2, $connection->count('*', 'desiderio_test_sections', []));
        self::assertSame(2, $connection->count('*', 'desiderio_test_links', []));
        self::assertSame('Unrelated section', $connection->select(['title'], 'desiderio_test_sections', ['uid' => $unrelatedSectionUid])->fetchOne());
        self::assertSame('Unrelated link', $connection->select(['label'], 'desiderio_test_links', ['uid' => $unrelatedLinkUid])->fetchOne());
        self::assertSame('Provider section', $connection->select(['title'], 'desiderio_test_sections', ['foreign_table_parent_uid' => $contentUid])->fetchOne());
        self::assertSame(1, $connection->count('*', 'desiderio_test_links', ['label' => 'Provider nested link']));
    }

    public function testNativeElementsUseTheirManifestFixture(): void
    {
        $this->seedLibrary('core');
        $bullets = array_find(CoreContentElements::all(), static fn(array $element): bool => $element['cType'] === 'bullets');
        self::assertIsArray($bullets);

        self::assertSame(
            $bullets['fixture']['bodytext'],
            $this->getConnectionPool()->getConnectionForTable('tt_content')->select(['bodytext'], 'tt_content', ['CType' => 'bullets'])->fetchOne(),
        );
    }

    private function seedLibrary(string $host): void
    {
        $command = $this->get(SeedElementLibraryCommand::class);
        self::assertInstanceOf(SeedElementLibraryCommand::class, $command);
        $tester = new CommandTester($command);

        self::assertSame(Command::SUCCESS, $tester->execute([
            '--parent' => '1',
            '--hosts' => $host,
            '--no-warm' => true,
        ]), $tester->getDisplay());
    }
}
