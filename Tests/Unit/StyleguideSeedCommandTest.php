<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;

final class StyleguideSeedCommandTest extends TestCase
{
    public function testCommandUsesConfiguredParentPageAndStyleguideFixtures(): void
    {
        $commandFile = __DIR__ . '/../../Classes/Command/SeedStyleguidePagesCommand.php';
        $source = (string) file_get_contents($commandFile);

        self::assertSame(505, SeedStyleguidePagesCommand::DEFAULT_PARENT_PID);
        self::assertStringContainsString("name: 'desiderio:styleguide:seed'", $source);
        self::assertStringContainsString("->addOption(\n                'dry-run'", $source);
        self::assertStringContainsString('StyleguideContentGroups::getGroupsWithFixtures()', $source);
        self::assertStringContainsString("->update('tt_content')", $source);
        self::assertStringContainsString("'desiderio_%'", $source);
        self::assertStringContainsString('deleteCollectionRowsForParentUids($existingContentUids);', $source);
    }

    public function testLegacyColumnsFixtureIsResolvedToCollectionData(): void
    {
        $command = $this->createCommand();
        $definition = [
            'fields' => [
                'copyright' => ['identifier' => 'copyright'],
            ],
            'collections' => [
                'column_items' => [
                    'table' => 'column_items',
                    'fields' => [
                        'title' => ['identifier' => 'title'],
                        'links' => ['identifier' => 'links'],
                    ],
                ],
            ],
        ];

        self::assertNull($this->invokeMethod($command, 'resolveScalarField', ['columns', $definition['fields']]));
        self::assertSame(
            'column_items',
            $this->invokeMethod($command, 'resolveCollectionField', [
                'columns',
                [
                    ['title' => 'Solutions', 'links' => ['Docs', 'API']],
                ],
                $definition,
            ])
        );
        self::assertSame(
            [
                ['title' => 'Solutions', 'links' => "Docs\nAPI"],
            ],
            $this->invokeMethod($command, 'normalizeCollectionItems', [
                [
                    ['title' => 'Solutions', 'links' => ['Docs', 'API']],
                ],
                $definition['collections']['column_items'],
            ])
        );
    }

    public function testPricingPlanAliasesAreNormalizedForCollectionRows(): void
    {
        $command = $this->createCommand();
        $plansCollection = [
            'table' => 'plans',
            'fields' => [
                'name' => ['identifier' => 'name'],
                'price' => ['identifier' => 'price'],
                'billing_period' => ['identifier' => 'billing_period'],
                'features_list' => ['identifier' => 'features_list'],
                'button_text' => ['identifier' => 'button_text'],
                'is_featured' => ['identifier' => 'is_featured'],
            ],
        ];

        self::assertSame(
            [[
                'name' => 'Professional',
                'price' => '$29',
                'billing_period' => '/user/month',
                'features_list' => "Unlimited users\nPriority support",
                'button_text' => 'Start Free Trial',
                'is_featured' => 1,
            ]],
            $this->invokeMethod($command, 'normalizeCollectionItems', [[
                [
                    'name' => 'Professional',
                    'price' => '$29',
                    'period' => '/user/month',
                    'features' => ['Unlimited users', 'Priority support'],
                    'button' => 'Start Free Trial',
                    'featured' => true,
                ],
            ], $plansCollection])
        );
    }

    public function testHeadersAreConvertedToColumnDefinitions(): void
    {
        $command = $this->createCommand();
        $collection = [
            'table' => 'column_definitions',
            'fields' => [
                'column_label' => ['identifier' => 'column_label'],
                'column_key' => ['identifier' => 'column_key'],
                'column_align' => ['identifier' => 'column_align'],
            ],
        ];

        self::assertSame(
            [
                ['column_label' => 'Service', 'column_key' => 'service', 'column_align' => 'left'],
                ['column_label' => 'Last Deploy', 'column_key' => 'last_deploy', 'column_align' => 'left'],
            ],
            $this->invokeMethod($command, 'normalizeCollectionItems', [['Service', 'Last Deploy'], $collection])
        );
    }

    public function testCollectionTableNamesAreDerivedUniquelyFromContentBlockDefinitions(): void
    {
        $command = $this->createCommand();
        $this->setProperty($command, 'contentBlockDefinitions', [
            'desiderio_footercolumns' => [
                'fields' => [],
                'collections' => [
                    'column_items' => [
                        'table' => 'column_items',
                        'fields' => [],
                    ],
                    'link_items' => [
                        'table' => 'link_items',
                        'fields' => [],
                    ],
                ],
            ],
            'desiderio_footerdark' => [
                'fields' => [],
                'collections' => [
                    'column_items' => [
                        'table' => 'column_items',
                        'fields' => [],
                    ],
                ],
            ],
        ]);

        self::assertSame(
            ['column_items', 'link_items'],
            $this->invokeMethod($command, 'getCollectionTableNames', [])
        );
    }

    private function createCommand(): SeedStyleguidePagesCommand
    {
        return new SeedStyleguidePagesCommand($this->createMock(ConnectionPool::class));
    }

    /**
     * @param list<mixed> $arguments
     */
    private function invokeMethod(object $object, string $method, array $arguments): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);

        return $reflection->invokeArgs($object, $arguments);
    }

    private function setProperty(object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($object, $property);
        $reflection->setValue($object, $value);
    }
}
