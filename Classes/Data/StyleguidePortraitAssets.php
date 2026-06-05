<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Portrait fixtures sourced from Unsplash collection #25880.
 *
 * Files live under Resources/Public/Styleguide/Unsplash/People/ and are
 * referenced by styleguide fixtures, starter definitions, and seed commands.
 */
final class StyleguidePortraitAssets
{
    public const UNSPLASH_COLLECTION_ID = 25880;

    private const RELATIVE_DIRECTORY = 'Resources/Public/Styleguide/Unsplash/People';

    private const TEAM_GRID_PREFIX = 'team-grid-';

    /**
     * @return list<string> Relative extension paths (without EXT:desiderio/ prefix).
     */
    public static function teamGridPortraitFiles(): array
    {
        $directory = self::absolutePortraitDirectory();
        if (!is_dir($directory)) {
            return [];
        }

        $files = glob($directory . '/' . self::TEAM_GRID_PREFIX . '*.jpg');
        if ($files === false) {
            return [];
        }

        natsort($files);

        return array_values(array_map(
            static fn (string $absolutePath): string => self::RELATIVE_DIRECTORY . '/' . basename($absolutePath),
            $files
        ));
    }

    /**
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    public static function fileReferenceForIndex(int $index, string $name = ''): array
    {
        $files = self::teamGridPortraitFiles();
        if ($files === []) {
            return self::emptyFileReference();
        }

        $file = $files[abs($index) % count($files)];
        $label = trim($name) !== '' ? trim($name) : self::readableLabelFromFile($file);

        return [
            'file' => $file,
            'title' => $label . ' portrait',
            'alternative' => 'Portrait photo of ' . $label . '.',
            'description' => 'Portrait-style photo from Unsplash collection #' . self::UNSPLASH_COLLECTION_ID . '.',
            'source' => 'https://unsplash.com/collections/' . self::UNSPLASH_COLLECTION_ID,
        ];
    }

    /**
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    public static function fileReferenceForMember(string $name, int $index): array
    {
        return self::fileReferenceForIndex($index, $name);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public static function isPortraitField(string $field, array $fieldConfig): bool
    {
        $identifier = is_scalar($fieldConfig['identifier'] ?? null) ? (string)$fieldConfig['identifier'] : '';
        $label = is_scalar($fieldConfig['label'] ?? null) ? (string)$fieldConfig['label'] : '';
        $haystack = strtolower($field . ' ' . $identifier . ' ' . $label);

        return str_contains($haystack, 'portrait')
            || str_contains($haystack, 'avatar')
            || $field === 'image';
    }

    /**
     * @return array{file: string, title: string, alternative: string, description: string, source: string}
     */
    private static function emptyFileReference(): array
    {
        return [
            'file' => '',
            'title' => '',
            'alternative' => '',
            'description' => '',
            'source' => '',
        ];
    }

    private static function readableLabelFromFile(string $file): string
    {
        $basename = pathinfo($file, PATHINFO_FILENAME);
        $basename = preg_replace('/^team-grid-\d+$/', 'Team member', $basename) ?? $basename;
        $basename = str_replace(['-', '_'], ' ', $basename);

        return ucwords(trim($basename));
    }

    private static function absolutePortraitDirectory(): string
    {
        return dirname(__DIR__, 2) . '/' . self::RELATIVE_DIRECTORY;
    }
}
