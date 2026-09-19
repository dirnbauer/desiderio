<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;

/**
 * The contract of desiderio:styleguide:seed: its options, the guards it
 * refuses on, how it rewrites {{page:…}} placeholders, and the collection
 * tables it derives from the Content Block definitions.
 */
final class StyleguideSeedCommandTest extends AbstractStyleguideSeedingTestCase
{
    public function testCommandUsesConfiguredParentPageAndStyleguideFixtures(): void
    {
        $commandFile = __DIR__ . '/../../Classes/Command/SeedStyleguidePagesCommand.php';
        $source = (string)file_get_contents($commandFile);

        self::assertStringContainsString(
            'DEFAULT_PARENT_PID = ' . SeedStyleguidePagesCommand::DEFAULT_PARENT_PID . ';',
            $source,
            'The default parent page id is part of the command contract.'
        );
        self::assertStringContainsString("name: 'desiderio:styleguide:seed'", $source);
        self::assertStringContainsString("->addOption(\n                'dry-run'", $source);
        self::assertStringContainsString("'skip-powermail'", $source);
        self::assertStringContainsString('PowermailDemoSeeder', $source);
        self::assertStringContainsString("'skip-news'", $source);
        self::assertStringContainsString('NewsDemoSeeder', $source);
        self::assertStringContainsString('buildSeoPageAttributes', $source);
        self::assertStringContainsString('StyleguideContentGroups::getGroupsWithFixtures()', $source);
        self::assertStringContainsString("getPropertyFromAspect('workspace', 'id', 0)", $source);
        self::assertStringContainsString('getFixtureResolver()->buildContentInsert(', $source);
        self::assertStringContainsString('softDeleteSeededContent(', $source);

        $cleanerSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/DesiderioContentCleaner.php');
        self::assertStringContainsString("->update('tt_content')", $cleanerSource);
        self::assertStringContainsString("'desiderio_%'", $cleanerSource);
        self::assertStringContainsString('deleteCollectionRowsForParentUids(', $cleanerSource);
        self::assertStringContainsString("buildLiveWorkspaceConstraints(\$queryBuilder, 'tt_content')", $cleanerSource);

        $cleanupSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/CollectionCleanupService.php');
        self::assertStringContainsString("buildLiveWorkspaceConstraints(\$queryBuilder, 'sys_file_reference')", $cleanupSource);

        $elementSeederSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/ContentElementSeeder.php');
        self::assertStringContainsString('seedFileReferences(', $elementSeederSource);

        // The seeder draws its demo media from the pools in StyleguideDemoAssets;
        // the resolver only decides which pool a field belongs to.
        $demoAssetsSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/StyleguideDemoAssets.php');
        self::assertStringContainsString('Resources/Public/Styleguide/Unsplash', $demoAssetsSource);

        $fixtureResolverSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/StyleguideFixtureResolver.php');
        self::assertStringContainsString('StyleguideDemoAssets::seederImageAssets()', $fixtureResolverSource);

        $tcaOverride = (string)file_get_contents(__DIR__ . '/../../Configuration/TCA/Overrides/tt_content.php');
        self::assertStringNotContainsString('columnsOverrides', $tcaOverride);
        self::assertStringNotContainsString('Yaml::parseFile', $tcaOverride);
    }

    public function testNewsDemoQuoteSeedsUseSupportedQuoteVariants(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/NewsDemoSeeder.php');
        preg_match_all("/\\\$this->block\\('desiderio_quote', \\[(.*?)\\]\\)/s", $source, $matches);

        self::assertNotEmpty($matches[1]);
        foreach ($matches[1] as $quoteSeed) {
            self::assertStringNotContainsString("'variant' => 'default'", $quoteSeed);
        }
    }

    public function testCommandRefusesToSeedInOfflineWorkspace(): void
    {
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(42));
        $command = new SeedStyleguidePagesCommand(
            self::createStub(ConnectionPool::class),
            $context,
            self::createStub(StorageRepository::class),
            new DatabaseSchemaHelper(self::createStub(ConnectionPool::class)),
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertStringContainsString('Refusing to seed inside workspace #42', $tester->getDisplay());
    }

    public function testPagePlaceholdersAreReplacedInsideRichText(): void
    {
        $block = [
            'ctype' => 'text',
            'colPos' => 0,
            'fields' => [
                'bodytext' => '<p><a href="{{page:content-types/navigation-wayfinding}}">Navigation</a></p>',
                'button_link' => '{{page:chapter-hero}}',
                'fallback_link' => '{{page:missing}}',
            ],
        ];

        $resolved = $this->invokeMethod($this->createCommand(), 'substituteLinkPlaceholders', [
            $block,
            [
                'content-types/navigation-wayfinding' => 670,
                'chapter-hero' => 669,
            ],
        ]);

        self::assertIsArray($resolved);
        $fields = $resolved['fields'] ?? null;
        self::assertIsArray($fields);
        self::assertSame(
            '<p><a href="t3://page?uid=670">Navigation</a></p>',
            $fields['bodytext'] ?? null
        );
        self::assertSame('t3://page?uid=669', $fields['button_link'] ?? null);
        self::assertSame('https://github.com/dirnbauer/desiderio', $fields['fallback_link'] ?? null);
    }

    public function testCollectionTableNamesAreDerivedUniquelyFromContentBlockDefinitions(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'desiderio_footercolumns' => [
                'fields' => [],
                'collections' => [
                    'column_items' => [
                        'table' => 'column_items',
                        'fields' => [],
                        'minItems' => 1,
                        'maxItems' => null,
                    ],
                    'link_items' => [
                        'table' => 'link_items',
                        'fields' => [],
                        'minItems' => 1,
                        'maxItems' => null,
                    ],
                ],
            ],
            'desiderio_footerdark' => [
                'fields' => [],
                'collections' => [
                    'column_items' => [
                        'table' => 'column_items',
                        'fields' => [],
                        'minItems' => 1,
                        'maxItems' => null,
                    ],
                ],
            ],
        ]);

        self::assertSame(
            ['column_items', 'link_items'],
            new ContentBlockCollectionMap()->getCollectionTableNames()
        );
    }

    public function testContentBlockDefinitionKeepsCollectionItemLimits(): void
    {
        $command = $this->createCommand();

        $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig([
            'name' => 'desiderio/demo',
            'prefixFields' => false,
            'fields' => [
                [
                    'identifier' => 'items',
                    'type' => 'Collection',
                    'table' => 'demo_items',
                    'prefixField' => true,
                    'minItems' => 2,
                    'maxItems' => 4,
                    'fields' => [
                        [
                            'identifier' => 'title',
                            'type' => 'Textarea',
                        ],
                    ],
                ],
            ],
        ]);
        self::assertIsArray($definition);
        $collections = $definition['collections'] ?? null;
        self::assertIsArray($collections);
        $items = $collections['items'] ?? null;
        self::assertIsArray($items);

        self::assertSame(2, $items['minItems']);
        self::assertSame(4, $items['maxItems']);
        self::assertSame('desiderio_demo_items', $items['column']);
    }
}
