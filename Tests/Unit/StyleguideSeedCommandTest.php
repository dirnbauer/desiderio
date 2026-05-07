<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
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
        self::assertStringContainsString("deleteCollectionRowsForParentUids(\$existingContentUids, 'tt_content');", $source);
        self::assertStringContainsString('completeResolvedFixtureData(', $source);
        self::assertStringContainsString('seedFileReferences(', $source);
        self::assertStringContainsString('Resources/Public/Styleguide/Unsplash', $source);
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
                    'minItems' => 1,
                    'maxItems' => null,
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
            'minItems' => 1,
            'maxItems' => null,
            'fields' => [
                'name' => ['identifier' => 'name'],
                'price' => ['identifier' => 'price'],
                'billing_period' => ['identifier' => 'billing_period'],
                'button_text' => ['identifier' => 'button_text'],
                'is_featured' => ['identifier' => 'is_featured'],
            ],
            'collections' => [
                'features' => [
                    'table' => 'plan_features',
                    'minItems' => 1,
                    'maxItems' => null,
                    'fields' => [
                        'text' => ['identifier' => 'text'],
                    ],
                    'collections' => [],
                ],
            ],
        ];

        self::assertSame(
            [[
                'name' => 'Professional',
                'price' => '$29',
                'billing_period' => '/user/month',
                '__collections' => [
                    'features' => [
                        'table' => 'plan_features',
                        'items' => [
                            ['text' => 'Unlimited users'],
                            ['text' => 'Priority support'],
                        ],
                    ],
                ],
                'features' => 2,
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
            'minItems' => 1,
            'maxItems' => null,
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

        unset($collection['fields']['column_key']);
        self::assertSame(
            [
                ['column_label' => 'Service', 'column_align' => 'left'],
            ],
            $this->invokeMethod($command, 'normalizeCollectionItems', [['Service'], $collection])
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
            $this->invokeMethod($command, 'getCollectionTableNames', [])
        );
    }

    public function testMissingFieldsAndRepeatableItemsAreCompleted(): void
    {
        $command = $this->createCommand();
        $definition = [
            'fields' => [
                'header' => ['identifier' => 'header'],
                'eyebrow' => ['identifier' => 'eyebrow', 'type' => 'Textarea'],
                'description' => ['identifier' => 'description', 'type' => 'Textarea'],
                'image_alt' => ['identifier' => 'image_alt', 'type' => 'Textarea'],
                'image_source' => ['identifier' => 'image_source', 'type' => 'Textarea'],
                'image' => ['identifier' => 'image', 'type' => 'File', 'maxitems' => 1],
                'cta_link' => ['identifier' => 'cta_link', 'type' => 'Link'],
                'color' => ['identifier' => 'color', 'type' => 'Textarea'],
            ],
            'collections' => [
                'items' => [
                    'table' => 'demo_items',
                    'minItems' => 2,
                    'maxItems' => 3,
                    'fields' => [
                        'title' => ['identifier' => 'title', 'type' => 'Textarea'],
                        'description' => ['identifier' => 'description', 'type' => 'Textarea'],
                        'image' => ['identifier' => 'image', 'type' => 'File', 'maxitems' => 1],
                    ],
                ],
            ],
        ];

        $resolvedFixtureData = $this->invokeMethod($command, 'completeResolvedFixtureData', [
            'desiderio_demo',
            'Demo Element',
            $definition,
            ['header' => 'Demo Element'],
            [],
        ]);
        self::assertIsArray($resolvedFixtureData);
        self::assertCount(3, $resolvedFixtureData);
        [$fields, $collections, $fileReferences] = $resolvedFixtureData;
        self::assertIsArray($fields);
        self::assertIsArray($collections);
        self::assertIsArray($fileReferences);

        $description = $fields['description'] ?? null;
        self::assertIsString($description);
        $imageAlt = $fields['image_alt'] ?? null;
        self::assertIsString($imageAlt);
        $imageSource = $fields['image_source'] ?? null;
        self::assertIsString($imageSource);

        self::assertSame('Pattern Library', $fields['eyebrow']);
        self::assertStringContainsString('polished demo element pattern', strtolower($description));
        self::assertStringNotContainsString('Complete demo content', $description);
        self::assertStringContainsString('Accessible demo image', $imageAlt);
        self::assertStringContainsString('Unsplash demo image', $imageSource);
        self::assertSame('https://example.com/desiderio/cta_link_1', $fields['cta_link']);
        self::assertSame('primary', $fields['color']);
        self::assertArrayHasKey('image', $fileReferences);
        $imageReferences = $fileReferences['image'] ?? null;
        self::assertIsArray($imageReferences);
        $imageReference = $imageReferences[0] ?? null;
        self::assertIsArray($imageReference);
        $imageReferenceDescription = $imageReference['description'] ?? null;
        self::assertIsString($imageReferenceDescription);
        self::assertStringContainsString('Unsplash', $imageReferenceDescription);
        self::assertSame(1, $collections['items']['items'][0]['image']);
        self::assertCount(3, $collections['items']['items']);
        self::assertArrayHasKey('__fileReferences', $collections['items']['items'][0]);
        $collectionFileReferences = $collections['items']['items'][0]['__fileReferences']['image'][0] ?? null;
        self::assertIsArray($collectionFileReferences);
        $collectionImageDescription = $collectionFileReferences['description'] ?? null;
        self::assertIsString($collectionImageDescription);
        self::assertStringContainsString('Unsplash', $collectionImageDescription);
    }

    public function testHeaderFixtureIsIgnoredForHeaderlessContentBlocks(): void
    {
        $command = $this->createCommand();
        $this->setProperty($command, 'contentBlockDefinitions', [
            'desiderio_contentdivider' => [
                'fields' => [
                    'variant' => [
                        'identifier' => 'variant',
                        'type' => 'Select',
                        'default' => 'horizontal',
                    ],
                    'divider_text' => [
                        'identifier' => 'divider_text',
                        'type' => 'Textarea',
                    ],
                ],
                'collections' => [],
            ],
        ]);

        [$fields] = $this->invokeMethod($command, 'resolveFixtureFields', [
            'desiderio_contentdivider',
            [
                'header' => 'Ready to Get Started?',
                'description' => 'This legacy fixture field must not leak into a headerless TCA schema.',
            ],
            'Content Divider',
        ]);

        self::assertArrayNotHasKey('header', $fields);
        self::assertSame('horizontal', $fields['variant']);
        self::assertSame('Divider Text for Content Divider', $fields['divider_text']);
    }

    public function testInvalidSelectFixtureValuesFallBackToConfiguredDefaults(): void
    {
        $command = $this->createCommand();
        $this->setProperty($command, 'contentBlockDefinitions', [
            'desiderio_tabs' => [
                'fields' => [
                    'variant' => [
                        'identifier' => 'variant',
                        'type' => 'Select',
                        'items' => [
                            ['label' => 'Default', 'value' => 'default'],
                            ['label' => 'Line', 'value' => 'line'],
                        ],
                        'default' => 'default',
                    ],
                ],
                'collections' => [],
            ],
        ]);

        [$fields] = $this->invokeMethod($command, 'resolveFixtureFields', [
            'desiderio_tabs',
            ['variant' => 'underline'],
            'Tabs',
        ]);

        self::assertSame('default', $fields['variant']);

        [$fields] = $this->invokeMethod($command, 'resolveFixtureFields', [
            'desiderio_tabs',
            ['variant' => 'line'],
            'Tabs',
        ]);

        self::assertSame('line', $fields['variant']);
    }

    public function testLegacyStatsFixturesAreConvertedToChartDataJson(): void
    {
        $command = $this->createCommand();
        $this->setProperty($command, 'contentBlockDefinitions', [
            'desiderio_chart' => [
                'fields' => [
                    'header' => [
                        'identifier' => 'header',
                        'type' => 'Textarea',
                    ],
                    'chart_data' => [
                        'identifier' => 'chart_data',
                        'type' => 'Textarea',
                    ],
                ],
                'collections' => [],
            ],
        ]);

        [$fields] = $this->invokeMethod($command, 'resolveFixtureFields', [
            'desiderio_chart',
            [
                '_type' => 'stats',
                'header' => 'Performance Metrics',
                'stats' => [
                    ['value' => '99.97%', 'label' => 'Uptime'],
                    ['value' => '1.2B', 'label' => 'Requests Processed'],
                    ['value' => '12,400', 'label' => 'Active Users'],
                ],
            ],
            'Chart',
        ]);

        self::assertSame('Performance Metrics', $fields['header']);
        self::assertArrayHasKey('chart_data', $fields);
        self::assertNotSame('Chart Data for Chart', $fields['chart_data']);

        $chartData = json_decode((string)$fields['chart_data'], true);
        self::assertSame([
            ['label' => 'Uptime', 'value' => 99.97],
            ['label' => 'Requests Processed', 'value' => 1.2],
            ['label' => 'Active Users', 'value' => 12400],
        ], $chartData);
    }

    public function testContentBlockDefinitionKeepsCollectionItemLimits(): void
    {
        $command = $this->createCommand();

        $definition = $this->invokeMethod($command, 'buildContentBlockDefinition', [[
            'fields' => [
                [
                    'identifier' => 'items',
                    'type' => 'Collection',
                    'table' => 'demo_items',
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
        ]]);

        self::assertSame(2, $definition['collections']['items']['minItems']);
        self::assertSame(4, $definition['collections']['items']['maxItems']);
    }

    private function createCommand(): SeedStyleguidePagesCommand
    {
        return new SeedStyleguidePagesCommand(
            $this->createMock(ConnectionPool::class),
            $this->createMock(Context::class),
            $this->createMock(StorageRepository::class),
        );
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
