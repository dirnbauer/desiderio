<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\DataHandling;

use TYPO3\CMS\Core\DataHandling\ItemsProcessorContext;
use TYPO3\CMS\Core\DataHandling\ItemsProcessorInterface;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Schema\Struct\SelectItemCollection;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class IconItemsProcessor implements ItemsProcessorInterface
{
    public function processItems(
        SelectItemCollection $items,
        ItemsProcessorContext $context,
    ): SelectItemCollection {
        $items->removeAll();

        foreach (IconRegistry::selectItems() as $item) {
            $items->add(new SelectItem(
                type: 'select',
                label: $item['label'],
                value: $item['value'],
                group: $item['group'],
            ));
        }

        return $items;
    }
}
