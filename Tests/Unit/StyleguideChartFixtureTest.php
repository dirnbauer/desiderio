<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

/**
 * Chart elements are the one place where a fixture is not simply copied into a
 * column: legacy stats lists are converted to chart_data JSON, and a sparse
 * fixture has to come out with usable diagram controls rather than empty
 * selects.
 */
final class StyleguideChartFixtureTest extends AbstractStyleguideSeedingTestCase
{
    public function testLegacyStatsFixturesAreConvertedToChartDataJson(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
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

        [$fields] = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
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
        self::assertIsString($fields['chart_data']);

        $chartData = json_decode($fields['chart_data'], true);
        self::assertSame([
            ['label' => 'Uptime', 'value' => 99.97],
            ['label' => 'Requests Processed', 'value' => 1.2],
            ['label' => 'Active Users', 'value' => 12400],
        ], $chartData);
    }

    public function testSparseChartFixturesReceiveModernDiagramControls(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
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

        $resolvedFixtureData = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
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
}
