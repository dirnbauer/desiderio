<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\DataHandling\ContentBlockSelectItemsProcessor;
use Webconsulting\Desiderio\EventListener\ContentBlockSelectItemOverrides;

final class ContentBlockSelectItemOverridesTest extends TestCase
{
    public function testStaticSelectOverridesUseExactItemsProcessor(): void
    {
        $listener = new ContentBlockSelectItemOverrides();
        $method = new \ReflectionMethod($listener, 'buildFieldOverride');

        $override = $method->invoke($listener, [
            'identifier' => 'variant',
            'type' => 'Select',
            'renderType' => 'selectSingle',
            'label' => 'Variante',
            'items' => [
                [
                    'label' => 'Mit Rahmen',
                    'value' => 'bordered',
                ],
                [
                    'label' => 'Groß',
                    'value' => 'large',
                ],
                [
                    'label' => 'Karte',
                    'value' => 'card',
                ],
            ],
            'default' => 'bordered',
        ]);

        self::assertIsArray($override);
        $config = $override['config'] ?? [];
        self::assertIsArray($config);
        self::assertSame('bordered', $config['default'] ?? null);

        $itemsProcessors = $config['itemsProcessors'] ?? [];
        self::assertIsArray($itemsProcessors);
        $exactItemsProcessor = $itemsProcessors[0] ?? null;
        self::assertIsArray($exactItemsProcessor);
        self::assertSame(ContentBlockSelectItemsProcessor::class, $exactItemsProcessor['class'] ?? null);
        self::assertSame(
            [
                [
                    'label' => 'Mit Rahmen',
                    'value' => 'bordered',
                ],
                [
                    'label' => 'Groß',
                    'value' => 'large',
                ],
                [
                    'label' => 'Karte',
                    'value' => 'card',
                ],
            ],
            $exactItemsProcessor['parameters']['items'] ?? null
        );
    }
}
