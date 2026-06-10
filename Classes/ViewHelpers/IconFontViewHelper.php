<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Icon\IconRegistry;

/**
 * Resolves the self-hosted icon font stylesheet for the configured icon
 * library, e.g. {di:iconFont(library: site.configuration.settings.desiderio.shadcn.iconLibrary)}.
 */
final class IconFontViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('library', 'string', 'Desiderio icon library key', false, IconRegistry::DEFAULT_LIBRARY);
    }

    public function render(): string
    {
        $library = is_string($this->arguments['library'] ?? null) ? $this->arguments['library'] : '';

        return IconRegistry::fontStylesheet($library);
    }
}
