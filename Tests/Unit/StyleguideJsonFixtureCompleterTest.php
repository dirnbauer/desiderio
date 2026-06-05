<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Seeding\StyleguideJsonFixtureCompleter;

final class StyleguideJsonFixtureCompleterTest extends TestCase
{
    public function testCompletesMissingGalleryItemsAndImageFields(): void
    {
        $config = Yaml::parseFile(__DIR__ . '/../../ContentBlocks/ContentElements/gallery/config.yaml');
        self::assertIsArray($config);

        $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig(
            ContentBlockDefinitionRegistry::normalizeStringKeyedArray($config),
        );

        $fixture = [
            '_type' => 'section',
            'header' => 'Selected work',
            'columns' => '3',
        ];

        $completed = (new StyleguideJsonFixtureCompleter())->complete('desiderio_gallery', 'gallery', $definition, $fixture);

        self::assertArrayHasKey('items', $completed);
        self::assertIsArray($completed['items']);
        self::assertNotEmpty($completed['items']);
        /** @var list<array<string, mixed>> $items */
        $items = $completed['items'];
        self::assertArrayHasKey('image', $items[0]);
        self::assertIsArray($items[0]['image']);
        self::assertArrayHasKey('file', $items[0]['image']);
    }

    public function testCompletesMissingCollectionChildLinks(): void
    {
        $config = Yaml::parseFile(__DIR__ . '/../../ContentBlocks/ContentElements/badge-grid/config.yaml');
        self::assertIsArray($config);

        $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig(
            ContentBlockDefinitionRegistry::normalizeStringKeyedArray($config),
        );

        $fixture = [
            '_type' => 'grid',
            'header' => 'Badges',
            'items' => [
                ['label' => 'Launch ready'],
                ['label' => 'Editor friendly'],
            ],
        ];

        $completed = (new StyleguideJsonFixtureCompleter())->complete('desiderio_badgegrid', 'badge-grid', $definition, $fixture);

        self::assertIsArray($completed['items'] ?? null);
        /** @var list<array<string, mixed>> $items */
        $items = $completed['items'];
        self::assertSame('Launch ready', $items[0]['label']);
        self::assertNotSame('', $items[0]['link']);
        self::assertNotSame('', $items[1]['link']);
    }
}
