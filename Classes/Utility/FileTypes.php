<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Utility;

/**
 * What a file extension means for rendering: is it an image, and which icon
 * stands for it.
 *
 * Both answers come from the same normalized extension, so they live together
 * rather than in whichever ViewHelper happened to need one of them first.
 */
final class FileTypes
{
    /**
     * @var array<string, string>
     */
    private const array EXTENSION_ICONS = [
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
    private const array IMAGE_EXTENSIONS = [
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

    public static function iconName(mixed $extension): string
    {
        return self::EXTENSION_ICONS[self::normalize($extension)] ?? 'file';
    }

    public static function isImage(mixed $extension): bool
    {
        return isset(self::IMAGE_EXTENSIONS[self::normalize($extension)]);
    }

    /**
     * Extensions reach us from FAL, from a filename and from templates, so a
     * leading dot, surrounding whitespace and any casing are all expected.
     */
    private static function normalize(mixed $extension): string
    {
        if (!is_scalar($extension) && !$extension instanceof \Stringable) {
            return '';
        }

        return strtolower(trim((string)$extension, ". \t\n\r\0\x0B"));
    }
}
