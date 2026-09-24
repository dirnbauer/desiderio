<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;

/**
 * Every content-type chapter the seeder creates must name a theme preset.
 *
 * presetForContentTypeGroup() reads CONTENT_TYPE_GROUP_PRESETS directly, with
 * no fallback: a chapter added to CONTENT_TYPE_GROUP_SLUGS without a preset
 * would be a runtime error instead of a silently wrong-looking page, and this
 * test is what turns that into a failing build.
 */
final class ContentTypeChapterPresetTest extends TestCase
{
    public function testEveryChapterSlugHasAPreset(): void
    {
        $reflection = new \ReflectionClass(SeedStyleguidePagesCommand::class);
        $slugs = $reflection->getConstant('CONTENT_TYPE_GROUP_SLUGS');
        $presets = $reflection->getConstant('CONTENT_TYPE_GROUP_PRESETS');

        self::assertIsArray($slugs);
        self::assertIsArray($presets);
        self::assertNotSame([], $slugs);
        self::assertSame(
            array_keys($slugs),
            array_keys($presets),
            'CONTENT_TYPE_GROUP_SLUGS and CONTENT_TYPE_GROUP_PRESETS must list the same chapters, in the same order.'
        );

        foreach ($presets as $groupId => $preset) {
            self::assertIsString($preset, 'Preset of chapter ' . (string)$groupId . ' must be a preset identifier.');
            self::assertNotSame('', $preset);
        }
    }

    /**
     * The chapter meta description names the chapter's preset. Readers get the
     * name the themes page gives that preset, never its identifier.
     */
    public function testEveryChapterPresetHasTheNameOfItsThemesPageCard(): void
    {
        $reflection = new \ReflectionClass(SeedStyleguidePagesCommand::class);
        $presets = $reflection->getConstant('CONTENT_TYPE_GROUP_PRESETS');
        $names = $reflection->getConstant('PRESET_NAMES');
        self::assertIsArray($presets);
        self::assertIsArray($names);

        $overview = (string)file_get_contents(dirname(__DIR__, 2) . '/Resources/Private/Templates/Partials/Pages/PresetOverview.fluid.html');
        preg_match_all(
            '#data-shadcn-preset-sample="([^"]+)">\s*<header class="preset-card__head">\s*<h3 class="preset-card__name">([^<]+)</h3>#',
            $overview,
            $cards,
            PREG_SET_ORDER
        );
        $cardNames = [];
        foreach ($cards as $card) {
            $cardNames[$card[1]] = $card[2];
        }
        self::assertNotSame([], $cardNames, 'The themes page partial has no preset cards to compare with.');

        foreach ($presets as $groupId => $preset) {
            self::assertIsString($preset);
            self::assertArrayHasKey($preset, $names, sprintf('Chapter %s uses preset "%s", which has no name in PRESET_NAMES.', (string)$groupId, $preset));
            self::assertArrayHasKey($preset, $cardNames, sprintf('The themes page has no card for preset "%s".', $preset));
            self::assertSame($cardNames[$preset], $names[$preset], sprintf('Preset "%s" must be called what its themes page card calls it.', $preset));
        }
    }
}
