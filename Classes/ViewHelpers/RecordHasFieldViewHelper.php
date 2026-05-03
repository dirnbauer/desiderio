<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Psr\Container\ContainerInterface;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Renders children only if the given PSR-11 record reports the field via has() (TCA-aware).
 *
 * Usage:
 *   <d:recordHasField record="{record}" field="bodytext">
 *       <f:render.text record="{record}" field="bodytext"/>
 *   </d:recordHasField>
 */
final class RecordHasFieldViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('record', 'mixed', 'Record implementing ContainerInterface (e.g. TYPO3 Record)', true);
        $this->registerArgument('field', 'string', 'Field name to check', true);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $record = $arguments['record'] ?? null;
        $field = $arguments['field'] ?? '';
        if (!$record instanceof ContainerInterface || !is_string($field) || $field === '') {
            return false;
        }

        return $record->has($field);
    }
}
