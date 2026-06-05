<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;

/**
 * Demo media paths for styleguide fixtures and seed commands.
 */
final class StyleguideDemoAssets
{
    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    public static function audioAssets(): array
    {
        return [
            [
                'file' => 'Resources/Public/Styleguide/Audio/editorial-brief.wav',
                'title' => 'Editorial brief audio',
                'alt' => 'Short generated audio tone for the Audio Player styleguide fixture.',
                'credit' => 'Generated demo audio for Desiderio styleguide seeding.',
                'source' => 'EXT:desiderio/Resources/Public/Styleguide/Audio/editorial-brief.wav',
            ],
        ];
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    public static function portraitAssets(): array
    {
        $assets = [];
        foreach (StyleguidePortraitAssets::teamGridPortraitFiles() as $index => $file) {
            $reference = StyleguidePortraitAssets::fileReferenceForIndex($index);
            if ($reference['file'] === '') {
                continue;
            }

            $assets[] = [
                'file' => $reference['file'],
                'title' => $reference['title'],
                'alt' => $reference['alternative'],
                'credit' => $reference['description'],
                'source' => $reference['source'],
            ];
        }

        return $assets !== [] ? $assets : self::imageAssets();
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    public static function imageAssets(): array
    {
        return [
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/workspace-marvin-meyer.jpg',
                'title' => 'Collaborative workspace',
                'alt' => 'People working together around laptops in a collaborative workspace.',
                'credit' => 'Copyright/credit: Photo by Marvin Meyer on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/people-sitting-down-near-table-with-assorted-laptop-computers-SYTO3xs06fU',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/laptop-mimi-thian.jpg',
                'title' => 'Laptop work session',
                'alt' => 'A laptop open on a person\'s lap during a focused work session.',
                'credit' => 'Copyright/credit: Photo by Mimi Thian on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/macbook-on-womans-lap-i5cd_SlY8XY',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/laptop-glenn-carstens-peters.jpg',
                'title' => 'Planning on a laptop',
                'alt' => 'Hands using a laptop while planning work on a wooden desk.',
                'credit' => 'Copyright/credit: Photo by Glenn Carstens-Peters on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/person-using-macbook-pro-npxXWgQ33ZQ',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/customer-enterprise.jpg',
                'title' => 'Enterprise customer workspace',
                'alt' => 'A large retail distribution facility with loading bays.',
                'credit' => 'Unsplash image used as demo cover photography.',
                'source' => 'https://unsplash.com/',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/office-turquo-cabbit.jpg',
                'title' => 'Modern office atrium',
                'alt' => 'A modern multi-level office atrium with glass railings and warm light.',
                'credit' => 'Copyright/credit: Photo by Turquo Cabbit on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/modern-office-building-interior-with-multiple-floors-QkGDA4Q4Vdk',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/dashboard-neil-fernandez.jpg',
                'title' => 'Dark product dashboard',
                'alt' => 'A laptop displaying a dark modern dashboard interface.',
                'credit' => 'Copyright/credit: Photo by Neil Fernandez on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/a-modern-laptop-displaying-a-dark-themed-dashboard-6-0ajRI1cgs',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return array<string, string>|list<array<string, string>>
     */
    public static function buildFileFixtureValue(
        string $field,
        array $fieldConfig,
        int $index,
        StyleguideDemoValueGenerator $demoValueGenerator,
    ): array {
        $maxItems = ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxitems')
            ?? ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxItems')
            ?? 1;
        $maxItems = max(1, $maxItems);
        $count = $maxItems === 1 ? 1 : min(3, $maxItems);

        $assets = self::isAudioFileField($field, $fieldConfig)
            ? self::audioAssets()
            : (StyleguidePortraitAssets::isPortraitField($field, $fieldConfig)
                ? self::portraitAssets()
                : self::imageAssets());

        $references = [];
        for ($offset = 0; $offset < $count; $offset++) {
            $assetIndex = abs(crc32($field . ':' . ($index + $offset))) % count($assets);
            $asset = $assets[$assetIndex];
            $references[] = [
                'file' => $asset['file'],
                'title' => $asset['title'],
                'alternative' => $asset['alt'],
                'description' => $asset['credit'],
                'source' => $asset['source'],
            ];
        }

        if ($count === 1) {
            return $references[0];
        }

        return $references;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public static function isAudioFileField(string $field, array $fieldConfig): bool
    {
        $identifier = $fieldConfig['identifier'] ?? '';
        $label = $fieldConfig['label'] ?? '';
        $allowed = $fieldConfig['allowed'] ?? '';
        $haystack = $field
            . ' ' . (is_scalar($identifier) ? (string)$identifier : '')
            . ' ' . (is_scalar($label) ? (string)$label : '');
        $normalized = strtolower(str_replace(['-', '_', ' '], '', $haystack));
        $allowedTypes = is_scalar($allowed) ? strtolower((string)$allowed) : '';

        return str_contains($normalized, 'audio')
            || str_contains($allowedTypes, 'audio');
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public static function isIconSelectField(string $field, array $fieldConfig): bool
    {
        if (($fieldConfig['type'] ?? '') !== 'Select') {
            return false;
        }

        $processors = $fieldConfig['itemsProcessors'] ?? [];
        if (is_array($processors)) {
            foreach ($processors as $processor) {
                if (!is_array($processor)) {
                    continue;
                }
                $class = $processor['class'] ?? '';
                if (is_string($class) && str_contains($class, 'IconItemsProcessor')) {
                    return true;
                }
            }
        }

        return str_contains(strtolower($field), 'icon');
    }
}
