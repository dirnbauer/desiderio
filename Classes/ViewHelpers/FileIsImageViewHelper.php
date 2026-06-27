<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class FileIsImageViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('extension', 'mixed', 'File extension, with or without leading dot.', true);
    }

    public function render(): bool
    {
        return FileIconNameViewHelper::isImageExtension($this->arguments['extension'] ?? null);
    }
}
