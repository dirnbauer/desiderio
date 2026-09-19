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

    // -------------------------------------------------- seeder pools
    //
    // The pools desiderio:styleguide:seed draws from when no role-based
    // provider is configured. They are deliberately NOT the same lists as the
    // fixture pools above: the picker is `crc32(field:index) % count(pool)`,
    // so changing a list's length or order rewrites every committed
    // ContentBlocks/**/fixture.json. Keep the two sets apart until a fixture
    // regeneration is intended.
    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    public static function seederAudioAssets(): array
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
    public static function seederPortraitAssets(): array
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

        return $assets !== [] ? $assets : self::seederImageAssets();
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    public static function seederImageAssets(): array
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
                'file' => 'Resources/Public/Styleguide/Unsplash/forest-marvin-meyer.jpg',
                'title' => 'Forest path',
                'alt' => 'Tall green trees lining a quiet forest path in daylight.',
                'credit' => 'Copyright/credit: Photo by Marvin Meyer on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/green-trees-on-forest-during-daytime-qLTsA_plc1k',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/river-marvin-meyer.jpg',
                'title' => 'City river walk',
                'alt' => 'People walking beside a city river with buildings in the distance.',
                'credit' => 'Copyright/credit: Photo by Marvin Meyer on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/people-walking-beside-river-WpCviXDvoyQ',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/office-turquo-cabbit.jpg',
                'title' => 'Modern office atrium',
                'alt' => 'A modern multi-level office atrium with glass railings and warm light.',
                'credit' => 'Copyright/credit: Photo by Turquo Cabbit on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/modern-office-building-interior-with-multiple-floors-QkGDA4Q4Vdk',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/workspace-david-kristianto.jpg',
                'title' => 'Organized product workspace',
                'alt' => 'A modern organized workspace with a laptop, design tools, and warm task lighting.',
                'credit' => 'Copyright/credit: Photo by David Kristianto on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/a-modern-organized-workspace-with-a-laptop-aN8yRTfGYXY',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/dashboard-neil-fernandez.jpg',
                'title' => 'Dark product dashboard',
                'alt' => 'A laptop displaying a dark modern dashboard interface.',
                'credit' => 'Copyright/credit: Photo by Neil Fernandez on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/a-modern-laptop-displaying-a-dark-themed-dashboard-6-0ajRI1cgs',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/office-e-vos.jpg',
                'title' => 'Glass office walkways',
                'alt' => 'A modern office interior with glass walls, walkways, and open communal space.',
                'credit' => 'Copyright/credit: Photo by E Vos on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/modern-office-interior-with-glass-walls-and-walkways-V_yQ8IyCmYY',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/facade-fabian-kleiser.jpg',
                'title' => 'Geometric glass facade',
                'alt' => 'A blue glass office facade with geometric reflections and evening light.',
                'credit' => 'Copyright/credit: Photo by Fabian Kleiser on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/glass-facade-of-a-modern-office-building-V5vF94h52r0',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/office-deliberate-directions.jpg',
                'title' => 'Glass-walled modern office',
                'alt' => 'A bright modern office with glass walls, teal accents, and clean work areas.',
                'credit' => 'Copyright/credit: Photo by Deliberate Directions on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/modern-office-space-with-glass-walls-and-light-decor-wlHBYkK2y4k',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/whiteboard-vitaly-gariev.jpg',
                'title' => 'Strategy whiteboard session',
                'alt' => 'A modern team reviewing a whiteboard strategy session in a creative office.',
                'credit' => 'Copyright/credit: Photo by Vitaly Gariev on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/team-collaborating-around-a-whiteboard-in-an-office-CdTQI-Nh7J4',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/desk-logan-weaver.jpg',
                'title' => 'Minimal product desk',
                'alt' => 'A refined modern desk setup with laptop, keyboard, books, and warm task light.',
                'credit' => 'Copyright/credit: Photo by LOGAN WEAVER | @LGNWVR on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/a-modern-desk-setup-with-laptop-and-books-xjyHDnA93Pk',
            ],
            [
                'file' => 'Resources/Public/Styleguide/Unsplash/planning-blue-sky.jpg',
                'title' => 'Agile planning board',
                'alt' => 'A team discussing tasks at a whiteboard during an agile planning session.',
                'credit' => 'Copyright/credit: Photo by blue sky on Unsplash. Used as seeded demo imagery.',
                'source' => 'https://unsplash.com/photos/four-men-gathered-around-a-whiteboard-with-sticky-notes-MLWk6FFWURU',
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
