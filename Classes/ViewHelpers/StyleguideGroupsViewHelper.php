<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;

/**
 * Exposes styleguide group data as a template variable (see styleguide-content-groups.json).
 *
 * Usage:
 * <d:styleguideGroups as="styleguideGroups">
 *     <f:for each="{styleguideGroups}" as="group">...</f:for>
 * </d:styleguideGroups>
 */
final class StyleguideGroupsViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('as', 'string', 'Template variable name for the groups array', true);
    }

    public function render(): string
    {
        $asArgument = $this->arguments['as'] ?? '';
        if (!is_string($asArgument) || $asArgument === '') {
            return '';
        }
        $variableProvider = $this->renderingContext->getVariableProvider();
        if ($variableProvider->exists($asArgument)) {
            $variableProvider->remove($asArgument);
        }
        $variableProvider->add($asArgument, StyleguideContentGroups::getGroupsWithFixtures());

        $rendered = $this->renderChildren();

        return is_string($rendered) ? $rendered : '';
    }
}
