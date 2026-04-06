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
        $variableProvider = $this->renderingContext->getVariableProvider();
        $name = $this->arguments['as'];
        if (method_exists($variableProvider, 'exists') && $variableProvider->exists($name)) {
            $variableProvider->remove($name);
        }
        $variableProvider->add($name, StyleguideContentGroups::getGroups());

        return $this->renderChildren();
    }
}
