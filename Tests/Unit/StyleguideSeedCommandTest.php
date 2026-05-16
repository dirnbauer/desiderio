<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
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
        self::assertStringContainsString("getPropertyFromAspect('workspace', 'id', 0)", $source);
        self::assertStringContainsString('buildLiveWorkspaceConstraints($queryBuilder, \'tt_content\')', $source);
        self::assertStringContainsString('buildLiveWorkspaceConstraints($queryBuilder, \'sys_file_reference\')', $source);
        self::assertStringContainsString('completeResolvedFixtureData(', $source);
        self::assertStringContainsString('seedFileReferences(', $source);
        self::assertStringContainsString('Resources/Public/Styleguide/Unsplash', $source);
    }

    public function testCommandRefusesToSeedInOfflineWorkspace(): void
    {
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(42));
        $command = new SeedStyleguidePagesCommand(
            $this->createMock(ConnectionPool::class),
            $context,
            $this->createMock(StorageRepository::class),
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertStringContainsString('Refusing to seed inside workspace #42', $tester->getDisplay());
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

    public function testPricingSliderMissingTierFieldsUsePricingDefaults(): void
    {
        $command = $this->createCommand();
        $definition = [
            'fields' => [
                'header' => ['identifier' => 'header', 'type' => 'Textarea'],
                'unit_label' => ['identifier' => 'unit_label', 'type' => 'Textarea'],
            ],
            'collections' => [
                'tiers' => [
                    'table' => 'pricing_slider_tiers',
                    'minItems' => 2,
                    'maxItems' => null,
                    'fields' => [
                        'volume' => ['identifier' => 'volume', 'type' => 'Textarea'],
                        'price' => ['identifier' => 'price', 'type' => 'Textarea'],
                        'included_features' => ['identifier' => 'included_features', 'type' => 'Textarea'],
                    ],
                ],
            ],
        ];

        $resolvedFixtureData = $this->invokeMethod($command, 'completeResolvedFixtureData', [
            'desiderio_pricingslider',
            'Slider Pricing',
            $definition,
            ['header' => 'Pay As You Grow'],
            [
                'tiers' => [
                    'table' => 'pricing_slider_tiers',
                    'items' => [
                        ['price' => '$19'],
                        ['price' => '$79'],
                        ['price' => '$299'],
                    ],
                ],
            ],
            [],
        ]);

        self::assertIsArray($resolvedFixtureData);
        $fields = $resolvedFixtureData[0] ?? null;
        $collections = $resolvedFixtureData[1] ?? null;
        self::assertIsArray($fields);
        self::assertIsArray($collections);

        $tiers = $collections['tiers'] ?? null;
        self::assertIsArray($tiers);
        $tierItems = $tiers['items'] ?? null;
        self::assertIsArray($tierItems);

        $firstTier = $tierItems[0] ?? null;
        self::assertIsArray($firstTier);
        $secondTier = $tierItems[1] ?? null;
        self::assertIsArray($secondTier);
        $thirdTier = $tierItems[2] ?? null;
        self::assertIsArray($thirdTier);

        $firstTierVolume = $firstTier['volume'] ?? null;
        self::assertIsString($firstTierVolume);
        $firstTierIncludedFeatures = $firstTier['included_features'] ?? null;
        self::assertIsString($firstTierIncludedFeatures);
        $secondTierVolume = $secondTier['volume'] ?? null;
        self::assertIsString($secondTierVolume);
        $secondTierPrice = $secondTier['price'] ?? null;
        self::assertIsString($secondTierPrice);
        $thirdTierVolume = $thirdTier['volume'] ?? null;
        self::assertIsString($thirdTierVolume);

        self::assertSame('requests', $fields['unit_label']);
        self::assertSame('1K', $firstTierVolume);
        self::assertSame('10K', $secondTierVolume);
        self::assertSame('100K', $thirdTierVolume);
        self::assertSame('$79', $secondTierPrice);
        self::assertStringContainsString('Accessible keyboard focus', $firstTierIncludedFeatures);
        self::assertStringNotContainsString('Volume for', $firstTierVolume);
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
                'gradient_from' => ['identifier' => 'gradient_from', 'type' => 'Textarea'],
                'gradient_to' => ['identifier' => 'gradient_to', 'type' => 'Textarea'],
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

        self::assertContains($fields['eyebrow'], ['shadcn/ui system', 'Token driven', 'Production polish', 'A11y ready', 'Radix pattern', 'Modern TYPO3']);
        self::assertStringContainsString('shadcn', strtolower($description));
        self::assertStringContainsString('demo element', strtolower($description));
        self::assertStringNotContainsString('Complete demo content', $description);
        self::assertStringContainsString('Accessible demo image', $imageAlt);
        self::assertStringContainsString('Unsplash demo image', $imageSource);
        self::assertSame('https://example.com/desiderio/cta_link_1', $fields['cta_link']);
        self::assertSame('primary', $fields['color']);
        self::assertSame('primary', $fields['gradient_from']);
        self::assertSame('accent', $fields['gradient_to']);
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

        $result = $this->invokeMethod($command, 'resolveFixtureFields', [
            'desiderio_contentdivider',
            [
                'header' => 'Ready to Get Started?',
                'description' => 'This legacy fixture field must not leak into a headerless TCA schema.',
            ],
            'Content Divider',
        ]);

        self::assertIsArray($result);
        $fields = $result[0] ?? null;
        self::assertIsArray($fields);
        $dividerText = $fields['divider_text'] ?? null;
        self::assertIsString($dividerText);

        self::assertArrayNotHasKey('header', $fields);
        self::assertSame('horizontal', $fields['variant']);
        self::assertStringContainsString('Content Divider', $dividerText);
        self::assertStringNotContainsString('Ready to Get Started?', $dividerText);
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

    public function testCountFieldsReceiveCompactSeedValues(): void
    {
        $command = $this->createCommand();

        self::assertSame(
            '128',
            $this->invokeMethod($command, 'buildDefaultFieldValue', [
                'desiderio_categorycards',
                'Category Cards Pricing',
                'count',
                ['identifier' => 'count', 'type' => 'Textarea'],
                0,
            ])
        );
        self::assertSame(
            '2.4K',
            $this->invokeMethod($command, 'buildDefaultFieldValue', [
                'desiderio_ratingdisplay',
                'Rating Display',
                'review_count',
                ['identifier' => 'review_count', 'type' => 'Textarea'],
                1,
            ])
        );
        self::assertSame(
            '86',
            $this->invokeMethod($command, 'buildDefaultFieldValue', [
                'desiderio_statscounter',
                'Stats Counter',
                'counter_target',
                ['identifier' => 'counter_target', 'type' => 'Textarea'],
                2,
            ])
        );
        self::assertSame(
            86,
            $this->invokeMethod($command, 'buildDefaultFieldValue', [
                'desiderio_demo',
                'Demo Element',
                'total',
                ['identifier' => 'total', 'type' => 'Number'],
                2,
            ])
        );
        self::assertSame(
            'Mara Weiss',
            $this->invokeMethod($command, 'buildDefaultFieldValue', [
                'desiderio_demo',
                'Demo Element',
                'account_name',
                ['identifier' => 'account_name', 'type' => 'Textarea'],
                0,
            ])
        );
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

    public function testSparseChartFixturesReceiveModernDiagramControls(): void
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
                    'chart_type' => [
                        'identifier' => 'chart_type',
                        'type' => 'Select',
                        'items' => [
                            ['label' => 'Area', 'value' => 'area'],
                            ['label' => 'Line', 'value' => 'line'],
                            ['label' => 'Bar', 'value' => 'bar'],
                            ['label' => 'Horizontal Bar', 'value' => 'horizontal_bar'],
                        ],
                        'default' => 'area',
                    ],
                    'color_variant' => [
                        'identifier' => 'color_variant',
                        'type' => 'Select',
                        'items' => [
                            ['label' => 'Primary', 'value' => 'primary'],
                            ['label' => 'Blue', 'value' => 'blue'],
                            ['label' => 'Green', 'value' => 'green'],
                            ['label' => 'Orange', 'value' => 'orange'],
                            ['label' => 'Red', 'value' => 'red'],
                        ],
                        'default' => 'primary',
                    ],
                    'show_grid' => [
                        'identifier' => 'show_grid',
                        'type' => 'Checkbox',
                    ],
                    'show_legend' => [
                        'identifier' => 'show_legend',
                        'type' => 'Checkbox',
                    ],
                    'legend_position' => [
                        'identifier' => 'legend_position',
                        'type' => 'Select',
                        'items' => [
                            ['label' => 'Bottom', 'value' => 'bottom'],
                            ['label' => 'Right', 'value' => 'right'],
                        ],
                        'default' => 'bottom',
                    ],
                    'show_values' => [
                        'identifier' => 'show_values',
                        'type' => 'Checkbox',
                    ],
                    'fill_type' => [
                        'identifier' => 'fill_type',
                        'type' => 'Select',
                        'items' => [
                            ['label' => 'Gradient', 'value' => 'gradient'],
                            ['label' => 'Solid', 'value' => 'solid'],
                        ],
                        'default' => 'gradient',
                    ],
                    'chart_height' => [
                        'identifier' => 'chart_height',
                        'type' => 'Select',
                        'items' => [
                            ['label' => 'Small', 'value' => 'small'],
                            ['label' => 'Medium', 'value' => 'medium'],
                            ['label' => 'Large', 'value' => 'large'],
                        ],
                        'default' => 'medium',
                    ],
                ],
                'collections' => [],
            ],
        ]);

        $resolvedFixtureData = $this->invokeMethod($command, 'resolveFixtureFields', [
            'desiderio_chart',
            [
                '_type' => 'stats',
                'header' => 'Modern Diagram',
            ],
            'Chart',
        ]);
        self::assertIsArray($resolvedFixtureData);
        $fields = $resolvedFixtureData[0] ?? null;
        self::assertIsArray($fields);

        self::assertSame('Modern Diagram', $fields['header']);
        self::assertSame('area', $fields['chart_type']);
        self::assertSame('primary', $fields['color_variant']);
        self::assertSame(1, $fields['show_grid']);
        self::assertSame(1, $fields['show_legend']);
        self::assertSame('bottom', $fields['legend_position']);
        self::assertSame(1, $fields['show_values']);
        self::assertSame('gradient', $fields['fill_type']);
        self::assertSame('medium', $fields['chart_height']);

        $chartDataJson = $fields['chart_data'] ?? null;
        self::assertIsString($chartDataJson);
        $chartData = json_decode($chartDataJson, true);
        self::assertSame([
            ['label' => 'Discover', 'value' => 42],
            ['label' => 'Evaluate', 'value' => 68],
            ['label' => 'Adopt', 'value' => 91],
            ['label' => 'Retain', 'value' => 117],
        ], $chartData);
    }

    public function testMapEmbedDefaultsUseEmbeddableMapUrl(): void
    {
        $command = $this->createCommand();
        $this->setProperty($command, 'contentBlockDefinitions', [
            'desiderio_mapembed' => [
                'fields' => [
                    'header' => [
                        'identifier' => 'header',
                        'type' => 'Textarea',
                    ],
                    'embed_url' => [
                        'identifier' => 'embed_url',
                        'type' => 'Text',
                    ],
                    'address' => [
                        'identifier' => 'address',
                        'type' => 'Textarea',
                    ],
                    'height' => [
                        'identifier' => 'height',
                        'type' => 'Number',
                        'default' => 400,
                    ],
                ],
                'collections' => [],
            ],
        ]);

        $resolvedFixtureData = $this->invokeMethod($command, 'resolveFixtureFields', [
            'desiderio_mapembed',
            [
                '_type' => 'card',
                'header' => 'Our Headquarters',
                'embed_url' => 'https://ui.shadcn.com/docs/map-embed',
            ],
            'Map Embed',
        ]);
        self::assertIsArray($resolvedFixtureData);
        $fields = $resolvedFixtureData[0] ?? null;
        self::assertIsArray($fields);

        $embedUrl = $fields['embed_url'] ?? null;
        self::assertIsString($embedUrl);

        self::assertSame('Our Headquarters', $fields['header']);
        self::assertSame(400, $fields['height']);
        self::assertSame('Mariahilfer Strasse 42, 1070 Vienna', $fields['address']);
        self::assertStringStartsWith('https://www.openstreetmap.org/export/embed.html?', $embedUrl);
        self::assertStringNotContainsString('ui.shadcn.com/docs', $embedUrl);
        self::assertStringNotContainsString('Embed Url for', $embedUrl);
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
        self::assertIsArray($definition);
        $collections = $definition['collections'] ?? null;
        self::assertIsArray($collections);
        $items = $collections['items'] ?? null;
        self::assertIsArray($items);

        self::assertSame(2, $items['minItems']);
        self::assertSame(4, $items['maxItems']);
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
