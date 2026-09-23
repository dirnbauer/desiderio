<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Seeding\StyleguideJsonFixtureCompleter;

/**
 * How a committed fixture.json becomes seeded rows: alias resolution, legacy
 * shapes, completion of absent fields, and the per-field-type normalisation
 * the demo value generator contributes.
 */
final class StyleguideFixtureResolverTest extends AbstractStyleguideSeedingTestCase
{
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

        self::assertNull($this->invokeMethod($this->createFixtureResolver(), 'resolveScalarField', ['columns', $definition['fields']]));
        self::assertSame(
            'column_items',
            $this->invokeMethod($this->createFixtureResolver(), 'resolveCollectionField', [
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
            $this->invokeMethod($this->createFixtureResolver(), 'normalizeCollectionItems', [
                [
                    ['title' => 'Solutions', 'links' => ['Docs', 'API']],
                ],
                $definition['collections']['column_items'],
            ])
        );
    }

    public function testCollectionItemFileFixturesKeepTheirArrayShape(): void
    {
        $collection = [
            'table' => 'press_mentions_mentions',
            'fields' => [
                'publication' => ['type' => 'Text'],
                'logo' => ['type' => 'File'],
            ],
        ];
        $logo = [
            'file' => 'Resources/Public/Styleguide/Library/lib-logo-signal-bureau-ab0de907.webp',
            'title' => 'Signal Bureau logo',
            'alternative' => 'Logo of Signal Bureau.',
            'description' => 'Fictional demo brand from the Desiderio styleguide.',
        ];

        $normalized = $this->invokeMethod($this->createFixtureResolver(), 'normalizeCollectionItems', [
            [
                ['publication' => 'Signal Bureau', 'logo' => $logo],
            ],
            $collection,
        ]);

        // The explicit file fixture must survive normalization as an array;
        // collapsing it to '' silently replaces it with demo pool images.
        self::assertIsArray($normalized);
        $firstItem = $normalized[0] ?? null;
        self::assertIsArray($firstItem);
        self::assertSame('Signal Bureau', $firstItem['publication'] ?? null);
        self::assertSame($logo, $firstItem['logo'] ?? null);
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
            $this->invokeMethod($this->createFixtureResolver(), 'normalizeCollectionItems', [[
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

    public function testPricingPlanLinkAndRecommendationAliasesAreNormalized(): void
    {
        $command = $this->createCommand();
        $plansCollection = [
            'table' => 'plans',
            'minItems' => 1,
            'maxItems' => null,
            'fields' => [
                'name' => ['identifier' => 'name'],
                'price' => ['identifier' => 'price'],
                'button_text' => ['identifier' => 'button_text'],
                'button_link' => ['identifier' => 'button_link', 'type' => 'Link'],
                'is_recommended' => ['identifier' => 'is_recommended', 'type' => 'Checkbox'],
            ],
            'collections' => [],
        ];

        self::assertSame(
            [[
                'name' => 'Growth',
                'price' => '$49',
                'button_text' => 'Choose Growth',
                'button_link' => 'https://example.com/growth',
                'is_recommended' => 1,
            ]],
            $this->invokeMethod($this->createFixtureResolver(), 'normalizeCollectionItems', [[
                [
                    'name' => 'Growth',
                    'price' => '$49',
                    'button' => 'Choose Growth',
                    'url' => 'https://example.com/growth',
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

        $resolvedFixtureData = $this->invokeMethod($this->createFixtureResolver(), 'completeResolvedFixtureData', [
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
        self::assertStringContainsString('Keyboard focus built in', $firstTierIncludedFeatures);
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
            $this->invokeMethod($this->createFixtureResolver(), 'normalizeCollectionItems', [['Service', 'Last Deploy'], $collection])
        );

        unset($collection['fields']['column_key']);
        self::assertSame(
            [
                ['column_label' => 'Service', 'column_align' => 'left'],
            ],
            $this->invokeMethod($this->createFixtureResolver(), 'normalizeCollectionItems', [['Service'], $collection])
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

        $resolvedFixtureData = $this->invokeMethod($this->createFixtureResolver(), 'completeResolvedFixtureData', [
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

        self::assertContains($fields['eyebrow'], ['shadcn/ui', 'TYPO3 Form Framework', 'Fluid 5.3', 'Responsive by default', 'Editor-ready', 'Modern TYPO3']);
        self::assertStringContainsString('demo element', strtolower($description));
        self::assertStringContainsString('pattern', strtolower($description));
        self::assertStringNotContainsString('Complete demo content', $description);
        self::assertStringNotContainsString('spacing, hierarchy, and responsive behavior', $description);
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

    /**
     * Shape of astryx_typo3's feature-callouts and 21 siblings: one collection
     * and a root File field listed after it in the JSON. The file objects carry
     * a `title` key the collection also has, so the old fuzzy collection match
     * sent the image into the collection, overwrote the real items with one
     * empty row, and completion padded that with generated filler.
     */
    public function testRootFileFieldFixtureDoesNotReplaceCollectionItems(): void
    {
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'astryx_typo3_featurecallouts' => ContentBlockDefinitionRegistry::buildDefinitionFromConfig([
                'name' => 'astryx-typo3/feature-callouts',
                'prefixFields' => false,
                'fields' => [
                    ['identifier' => 'header', 'useExistingField' => true],
                    ['identifier' => 'image', 'type' => 'File', 'maxitems' => 1, 'allowed' => 'common-image-types'],
                    [
                        'identifier' => 'callouts',
                        'type' => 'Collection',
                        'prefixField' => true,
                        'minitems' => 2,
                        'maxitems' => 8,
                        'fields' => [
                            ['identifier' => 'title', 'type' => 'Text'],
                            ['identifier' => 'text', 'type' => 'Textarea'],
                        ],
                    ],
                ],
            ]),
        ]);
        $callouts = [
            ['title' => 'Markers are part of the image', 'text' => 'One.'],
            ['title' => 'The legend is an ordered list', 'text' => 'Two.'],
            ['title' => 'Reverse swaps the columns', 'text' => 'Three.'],
            ['title' => 'The image gets the wider column', 'text' => 'Four.'],
        ];

        [, $collections, $fileReferences] = $this->createFixtureResolver()->resolveFixtureFields(
            'astryx_typo3_featurecallouts',
            [
                'header' => 'An image with a numbered legend',
                'callouts' => $callouts,
                'image' => [
                    ['file' => 'EXT:astryx_typo3/Resources/Public/Images/scene/substation.jpg', 'alternative' => 'A substation.', 'title' => ''],
                ],
            ],
            'feature-callouts',
        );

        $collection = $collections['callouts'] ?? null;
        self::assertIsArray($collection);
        self::assertSame('astryxtypo3_featurecallouts_callouts', $collection['column']);
        self::assertSame(
            array_column($callouts, 'title'),
            array_column($collection['items'], 'title'),
        );
        self::assertSame(
            array_column($callouts, 'text'),
            array_column($collection['items'], 'text'),
        );
        self::assertSame(
            'EXT:astryx_typo3/Resources/Public/Images/scene/substation.jpg',
            $fileReferences['image'][0]['file'] ?? null,
        );
    }

    public function testFuzzyCollectionMatchDoesNotOverwriteItemsFromTheCollectionsOwnKey(): void
    {
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'desiderio_demo' => [
                'fields' => [],
                'collections' => [
                    'items' => [
                        'table' => 'demo_items',
                        'minItems' => 1,
                        'maxItems' => null,
                        'fields' => [
                            'title' => ['identifier' => 'title', 'type' => 'Text'],
                        ],
                    ],
                ],
            ],
        ]);

        [, $collections] = $this->createFixtureResolver()->resolveFixtureFields(
            'desiderio_demo',
            [
                'items' => [['title' => 'One'], ['title' => 'Two'], ['title' => 'Three']],
                // Unknown legacy key whose objects share the `title` column.
                'entries' => [['title' => 'Legacy']],
            ],
            'Demo',
        );

        self::assertSame(['One', 'Two', 'Three'], array_column($collections['items']['items'] ?? [], 'title'));
    }

    public function testHeaderFixtureIsIgnoredForHeaderlessContentBlocks(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
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

        $result = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
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
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
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

        [$fields] = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
            'desiderio_tabs',
            ['variant' => 'underline'],
            'Tabs',
        ]);

        self::assertSame('default', $fields['variant']);

        [$fields] = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
            'desiderio_tabs',
            ['variant' => 'line'],
            'Tabs',
        ]);

        self::assertSame('line', $fields['variant']);
    }

    public function testPrefixedScalarFieldsAreSeededIntoStorageColumns(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'desiderio_headersection' => ContentBlockDefinitionRegistry::buildDefinitionFromConfig([
                'name' => 'desiderio/header-section',
                'prefixFields' => false,
                'fields' => [
                    [
                        'identifier' => 'header',
                        'useExistingField' => true,
                        'type' => 'Textarea',
                    ],
                    [
                        'identifier' => 'variant',
                        'prefixField' => true,
                        'type' => 'Select',
                        'items' => [
                            ['label' => 'Left', 'value' => 'left'],
                            ['label' => 'Center', 'value' => 'center'],
                        ],
                        'default' => 'center',
                    ],
                ],
            ]),
        ]);

        [$fields] = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
            'desiderio_headersection',
            [
                'header' => 'Pricing',
                'variant' => 'left',
            ],
            'Section Header',
        ]);

        self::assertSame('Pricing', $fields['header']);
        self::assertArrayNotHasKey('variant', $fields);
        self::assertSame('left', $fields['desiderio_headersection_variant']);
    }

    public function testDateTimeFixtureValuesAreNormalizedToTimestamps(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'desiderio_herocountdown' => [
                'fields' => [
                    'header' => [
                        'identifier' => 'header',
                        'type' => 'Textarea',
                    ],
                    'target_date' => [
                        'identifier' => 'target_date',
                        'type' => 'DateTime',
                    ],
                ],
                'collections' => [],
            ],
        ]);

        $resolvedFixtureData = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
            'desiderio_herocountdown',
            [
                'header' => 'Desiderio 2.0 goes live in',
                'target_date' => '2026-08-06T09:00:00+00:00',
            ],
            'Countdown Hero',
        ]);
        self::assertIsArray($resolvedFixtureData);
        $fields = $resolvedFixtureData[0] ?? null;
        self::assertIsArray($fields);

        self::assertSame(strtotime('2026-08-06T09:00:00+00:00'), $fields['target_date']);

        $resolvedFixtureData = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
            'desiderio_herocountdown',
            [
                'header' => 'Desiderio 2.0 goes live in',
                'target_date' => '1786006800',
            ],
            'Countdown Hero',
        ]);
        self::assertIsArray($resolvedFixtureData);
        $fields = $resolvedFixtureData[0] ?? null;
        self::assertIsArray($fields);

        self::assertSame(1786006800, $fields['target_date']);
    }

    public function testCountFieldsReceiveCompactSeedValues(): void
    {
        $command = $this->createCommand();

        self::assertSame(
            '128',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_categorycards',
                'Category Cards Pricing',
                'count',
                ['identifier' => 'count', 'type' => 'Textarea'],
                0,
            ])
        );
        self::assertSame(
            '2.4K',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_ratingdisplay',
                'Rating Display',
                'review_count',
                ['identifier' => 'review_count', 'type' => 'Textarea'],
                1,
            ])
        );
        self::assertSame(
            '86',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_statscounter',
                'Stats Counter',
                'counter_target',
                ['identifier' => 'counter_target', 'type' => 'Textarea'],
                2,
            ])
        );
        self::assertSame(
            86,
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_demo',
                'Demo Element',
                'total',
                ['identifier' => 'total', 'type' => 'Number'],
                2,
            ])
        );
        self::assertSame(
            'Mara Weiss',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_demo',
                'Demo Element',
                'account_name',
                ['identifier' => 'account_name', 'type' => 'Textarea'],
                0,
            ])
        );
    }

    public function testArticleHeroMetadataDefaultsStayCompact(): void
    {
        $command = $this->createCommand();

        self::assertSame(
            '5 min read',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_articlehero',
                'Article Hero',
                'reading_time',
                ['identifier' => 'reading_time', 'type' => 'Textarea'],
                0,
            ])
        );

        $topic = $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
            'desiderio_articlehero',
            'Article Hero',
            'topic',
            ['identifier' => 'topic', 'type' => 'Textarea'],
            0,
        ]);

        self::assertIsString($topic);
        self::assertLessThanOrEqual(24, strlen($topic));
        self::assertStringNotContainsString('for Reusable Section Blueprint', $topic);
    }

    public function testAudioPlayerFileFieldUsesAudioFixture(): void
    {
        $command = $this->createCommand();

        $references = $this->invokeMethod($this->createFixtureResolver(), 'buildFileReferenceFixtures', [
            'Audio Player-audio_file',
            [
                'identifier' => 'audio_file',
                'type' => 'File',
                'label' => 'Audio File',
                'allowed' => 'common-media-types',
                'maxitems' => 1,
            ],
            0,
        ]);

        self::assertIsArray($references);
        self::assertArrayHasKey(0, $references);
        $reference = $references[0];
        self::assertIsArray($reference);
        self::assertSame('Resources/Public/Styleguide/Audio/editorial-brief.wav', $reference['file'] ?? null);
        self::assertSame('Editorial brief audio', $reference['title'] ?? null);
    }

    public function testBlogTeaserMetaDefaultsToReadingTime(): void
    {
        $command = $this->createCommand();

        self::assertSame(
            '5 min read',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_blogteasers',
                'Blog Teasers',
                'meta',
                ['identifier' => 'meta', 'type' => 'Textarea'],
                0,
            ])
        );
    }

    public function testCodeBlockDefaultsUsePhpExample(): void
    {
        $command = $this->createCommand();

        self::assertSame(
            'PHP',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_codeblock',
                'Code Block',
                'language',
                ['identifier' => 'language', 'type' => 'Text'],
                0,
            ])
        );
        self::assertSame(
            'ArticleTeaserRenderer.php',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_codeblock',
                'Code Block',
                'filename',
                ['identifier' => 'filename', 'type' => 'Text'],
                0,
            ])
        );

        $code = $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
            'desiderio_codeblock',
            'Code Block',
            'code',
            ['identifier' => 'code', 'type' => 'Textarea'],
            0,
        ]);

        self::assertIsString($code);
        self::assertStringContainsString('final class ArticleTeaserRenderer', $code);
        self::assertStringContainsString('5min read', $code);
    }

    public function testTimelineStepDefaultsStayCompact(): void
    {
        $command = $this->createCommand();

        self::assertSame(
            'Plan',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_timeline',
                'Timeline',
                'step',
                ['identifier' => 'step', 'type' => 'Textarea'],
                0,
            ])
        );
        self::assertSame(
            'Build',
            $this->invokeMethod($this->createDemoValueGenerator(), 'buildDefaultFieldValue', [
                'desiderio_timeline',
                'Timeline',
                'step',
                ['identifier' => 'step', 'type' => 'Textarea'],
                1,
            ])
        );
    }

    public function testTabsFixtureProvidesTabContentForEveryPanel(): void
    {
        $fixture = json_decode(
            (string)file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/tabs/fixture.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($fixture);
        self::assertSame('default', $fixture['variant'] ?? null);
        self::assertSame(0, $fixture['default_tab'] ?? null);
        $items = $fixture['items'] ?? null;
        self::assertIsArray($items);
        self::assertCount(3, $items);

        foreach ($items as $item) {
            self::assertIsArray($item);
            $label = $item['tab_label'] ?? null;
            $content = $item['tab_content'] ?? null;
            self::assertIsString($label);
            self::assertIsString($content);
            self::assertNotSame('', trim($label));
            self::assertNotSame('', trim($content));
            self::assertGreaterThanOrEqual(170, strlen($content));
            self::assertLessThanOrEqual(230, strlen($content));
        }
    }

    public function testTabsFixtureNormalizesInvalidDefaultTabIndex(): void
    {
        $completer = new StyleguideJsonFixtureCompleter();
        $tabsConfig = Yaml::parseFile(
            __DIR__ . '/../../ContentBlocks/ContentElements/tabs/config.yaml'
        );
        self::assertIsArray($tabsConfig);
        $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig(
            ContentBlockDefinitionRegistry::normalizeStringKeyedArray($tabsConfig),
        );

        $completed = $completer->complete(
            'desiderio_tabs',
            'tabs',
            $definition,
            [
                '_type' => 'list',
                'default_tab' => 'Default Tab for Tabs: Customer Evidence Hub',
                'items' => [
                    ['tab_label' => 'One', 'tab_content' => str_repeat('a', 200)],
                    ['tab_label' => 'Two', 'tab_content' => str_repeat('b', 200)],
                ],
            ],
        );

        self::assertSame(0, $completed['default_tab'] ?? null);
    }

    public function testMapEmbedDefaultsUseEmbeddableMapUrl(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
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

        $resolvedFixtureData = $this->invokeMethod($this->createFixtureResolver(), 'resolveFixtureFields', [
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

    public function testListedItemsKeepTheirCountAndOnlyEmptyCollectionsArePadded(): void
    {
        $resolver = $this->createFixtureResolver();

        self::assertSame(2, $resolver->getTargetCollectionItemCount(['maxItems' => 4], 2), 'two listed app badges stay two');
        self::assertSame(3, $resolver->getTargetCollectionItemCount(['maxItems' => 4], 0), 'an empty collection is filled to three');
        self::assertSame(2, $resolver->getTargetCollectionItemCount(['minItems' => 2, 'maxItems' => 6], 1), 'minItems still applies');
        self::assertSame(4, $resolver->getTargetCollectionItemCount(['maxItems' => 4], 5), 'maxItems still caps');
    }
}
