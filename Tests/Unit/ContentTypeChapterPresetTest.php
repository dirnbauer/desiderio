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
}
