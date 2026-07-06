<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\DataHandling;

use TYPO3\CMS\Core\DataHandling\ItemsProcessorContext;
use TYPO3\CMS\Core\DataHandling\ItemsProcessorInterface;
use TYPO3\CMS\Core\Schema\Struct\SelectItem;
use TYPO3\CMS\Core\Schema\Struct\SelectItemCollection;

final class ContentBlockSelectItemsProcessor implements ItemsProcessorInterface
{
    public function processItems(
        SelectItemCollection $items,
        ItemsProcessorContext $context,
    ): SelectItemCollection {
        $configuredItems = $context->processorParameters['items'] ?? null;
        if (!is_array($configuredItems)) {
            return $items;
        }

        $type = $context->fieldConfiguration['type'] ?? 'select';
        $type = is_string($type) ? $type : 'select';

        $items->removeAll();
        foreach ($configuredItems as $configuredItem) {
            if (!is_array($configuredItem)) {
                continue;
            }

            $items->add(SelectItem::fromTcaItemArray($configuredItem, $type));
        }

        return $items;
    }
}
