<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Content-element and media factories shared by every showcase page class, plus
 * the two external URLs the showcase copy links to.
 *
 * A showcase block is the same shape the starter-site seeder consumes, so the
 * pages built here feed straight into the page seeder without translation.
 *
 * @phpstan-type ShowcaseBlock array{ctype: string, colPos: int, fields: array<string, mixed>}
 * @phpstan-type ShowcaseMedia array{file: string, title: string, alternative: string, description: string, source: string}
 * @phpstan-type ShowcaseBlogMeta array{publishDate: string, categories: list<string>, tags: list<string>}
 * @phpstan-type ShowcasePage array{title: string, navTitle: string, slug: string, abstract: string, description: string, parentSlug: string|null, subtitle?: string, pageTsConfig?: string, backendLayout?: string, blogList?: bool, blog?: ShowcaseBlogMeta, hideInNav?: bool, content: array<int, ShowcaseBlock>}
 */
final class ShowcaseBlocks
{
    public const string REPO_URL = 'https://github.com/dirnbauer/desiderio';
    public const string CREATE_URL = 'https://ui.shadcn.com/create';

    /**
     * @param array<string, mixed> $fields
     * @return ShowcaseBlock
     */
    public static function block(string $ctype, array $fields, int $colPos = 0): array
    {
        return [
            'ctype' => $ctype,
            'colPos' => $colPos,
            'fields' => $fields,
        ];
    }

    /**
     * @return ShowcaseMedia
     */
    public static function screenshot(string $filename, string $title, string $alternative, string $description = 'Screenshot of a TYPO3 14 installation with Desiderio.'): array
    {
        $folder = str_starts_with($filename, 'frontend-') ? 'Frontend' : 'Backend';

        return [
            'file' => 'Resources/Public/Styleguide/' . $folder . '/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => $description,
            'source' => self::REPO_URL,
        ];
    }

    /**
     * @return ShowcaseMedia
     */
    public static function unsplash(string $filename, string $title, string $alternative): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Unsplash/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Photo from the seeded Unsplash demo collection.',
            'source' => 'https://unsplash.com/collections/25880',
        ];
    }

    /**
     * @return ShowcaseMedia
     */
    public static function portrait(string $filename, string $name): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Unsplash/' . $filename,
            'title' => $name . ' portrait',
            'alternative' => 'Portrait photo of ' . $name . '.',
            'description' => 'Portrait photo from the seeded Unsplash demo collection.',
            'source' => 'https://unsplash.com/collections/25880',
        ];
    }
}
