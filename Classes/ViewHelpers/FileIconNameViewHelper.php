<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class FileIconNameViewHelper extends AbstractViewHelper
{
    /**
     * @var array<string, string>
     */
    private const EXTENSION_ICON_MAP = [
        'pdf' => 'file-pdf',
        'doc' => 'file-word',
        'docx' => 'file-word',
        'odt' => 'file-word',
        'rtf' => 'file-word',
        'xls' => 'file-spreadsheet',
        'xlsx' => 'file-spreadsheet',
        'ods' => 'file-spreadsheet',
        'csv' => 'file-spreadsheet',
        'ppt' => 'file-presentation',
        'pptx' => 'file-presentation',
        'odp' => 'file-presentation',
        'jpg' => 'file-image',
        'jpeg' => 'file-image',
        'png' => 'file-image',
        'gif' => 'file-image',
        'webp' => 'file-image',
        'avif' => 'file-image',
        'svg' => 'file-image',
        'bmp' => 'file-image',
        'tif' => 'file-image',
        'tiff' => 'file-image',
        'mp4' => 'file-video',
        'mov' => 'file-video',
        'm4v' => 'file-video',
        'webm' => 'file-video',
        'avi' => 'file-video',
        'mp3' => 'file-audio',
        'wav' => 'file-audio',
        'ogg' => 'file-audio',
        'm4a' => 'file-audio',
        'flac' => 'file-audio',
        'zip' => 'file-archive',
        'rar' => 'file-archive',
        '7z' => 'file-archive',
        'tar' => 'file-archive',
        'gz' => 'file-archive',
        'html' => 'file-code',
        'htm' => 'file-code',
        'css' => 'file-code',
        'js' => 'file-code',
        'json' => 'file-code',
        'xml' => 'file-code',
        'php' => 'file-code',
        'ts' => 'file-code',
        'tsx' => 'file-code',
        'md' => 'file-text',
        'txt' => 'file-text',
    ];

    /**
     * @var array<string, true>
     */
    private const IMAGE_EXTENSIONS = [
        'avif' => true,
        'bmp' => true,
        'gif' => true,
        'ico' => true,
        'jpeg' => true,
        'jpg' => true,
        'png' => true,
        'svg' => true,
        'tif' => true,
        'tiff' => true,
        'webp' => true,
    ];

    public function initializeArguments(): void
    {
        $this->registerArgument('extension', 'mixed', 'File extension, with or without leading dot.', true);
    }

    public function render(): string
    {
        return self::iconNameForExtension($this->arguments['extension'] ?? null);
    }

    public static function iconNameForExtension(mixed $extension): string
    {
        $extension = self::normalizeExtension($extension);

        return self::EXTENSION_ICON_MAP[$extension] ?? 'file';
    }

    public static function isImageExtension(mixed $extension): bool
    {
        return isset(self::IMAGE_EXTENSIONS[self::normalizeExtension($extension)]);
    }

    private static function normalizeExtension(mixed $extension): string
    {
        if (!is_scalar($extension) && !$extension instanceof \Stringable) {
            return '';
        }

        return strtolower(trim((string)$extension, ". \t\n\r\0\x0B"));
    }
}
