<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\DataHandling\ItemsProcessorContext;
use TYPO3\CMS\Core\Schema\Struct\SelectItemCollection;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\DataHandling\ContentBlockSelectItemsProcessor;

final class ContentBlockSelectItemsProcessorTest extends TestCase
{
    public function testProcessorReplacesLeakedSharedSelectItemsWithConfiguredItems(): void
    {
        $items = SelectItemCollection::createFromArray([
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
            [
                'label' => 'Cards',
                'value' => 'cards',
            ],
            [
                'label' => 'Warnung',
                'value' => 'warning',
            ],
        ], 'select');

        $context = new ItemsProcessorContext(
            table: 'tt_content',
            field: 'variant',
            row: [
                'CType' => 'desiderio_quote',
            ],
            fieldConfiguration: [
                'type' => 'select',
            ],
            processorParameters: [
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
            ],
            realPid: 1,
            site: new Site('test', 1, [
                'base' => '/',
            ]),
        );

        $processedItems = (new ContentBlockSelectItemsProcessor())->processItems($items, $context);
        $processedValues = array_map(
            static fn ($item): int|string|null => $item->getValue(),
            $processedItems->toArray()
        );

        self::assertSame(['bordered', 'large', 'card'], $processedValues);
    }
}
