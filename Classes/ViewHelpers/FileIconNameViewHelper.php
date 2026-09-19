<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Utility\FileTypes;

/**
 * The icon name that stands for a file extension, e.g. "file-pdf".
 */
final class FileIconNameViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('extension', 'mixed', 'File extension, with or without leading dot.', true);
    }

    public function render(): string
    {
        return FileTypes::iconName($this->arguments['extension'] ?? null);
    }
}
