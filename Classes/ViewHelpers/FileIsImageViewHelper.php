<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Utility\FileTypes;

/**
 * Whether a file extension names an image format the browser can render inline.
 */
final class FileIsImageViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('extension', 'mixed', 'File extension, with or without leading dot.', true);
    }

    public function render(): bool
    {
        return FileTypes::isImage($this->arguments['extension'] ?? null);
    }
}
