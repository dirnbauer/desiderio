<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Seeding\FixtureFieldNormalizer;

/**
 * The title a seeded image falls back to when its fixture gives none. It is
 * the image's hover text on the frontend, so it must read as words.
 */
final class FixtureFieldNormalizerTest extends TestCase
{
    /**
     * @return iterable<string, array{string, string}>
     */
    public static function fileNameTitleProvider(): iterable
    {
        yield 'library asset: role prefix and hash dropped' => ['lib-scene-week-planner-083f6674', 'Week Planner'];
        yield 'library logo' => ['lib-logo-signal-bureau-ab0de907', 'Signal Bureau'];
        yield 'hashed screenshot without lib prefix' => ['frontend-hero-lagoon-e69362e6', 'Frontend Hero Lagoon'];
        yield 'plain file name unchanged' => ['substation', 'Substation'];
        yield 'underscores and dashes' => ['team_photo-berlin', 'Team Photo Berlin'];
        yield 'nothing left but the hash keeps the name' => ['083f6674', '083f6674'];
    }

    #[DataProvider('fileNameTitleProvider')]
    public function testReadableFileTitleDropsLibraryPrefixAndContentHash(string $fileName, string $expected): void
    {
        self::assertSame($expected, (new FixtureFieldNormalizer())->buildReadableFileTitle($fileName));
    }

    public function testEmptyFileNameUsesFallback(): void
    {
        self::assertSame('Asset', (new FixtureFieldNormalizer())->buildReadableFileTitle(''));
    }

    public function testFileReferenceWithoutTitleGetsReadableTitle(): void
    {
        $references = (new FixtureFieldNormalizer())->buildFileReferenceFixturesFromFixtureValue(
            [
                [
                    'file' => 'EXT:astryx_typo3/Resources/Public/Images/scene/lib-scene-week-planner-083f6674.webp',
                    'alternative' => 'A laptop shows a weekly shift plan.',
                    'title' => '',
                ],
            ],
            ['type' => 'File', 'maxitems' => 1],
        );

        self::assertCount(1, $references);
        self::assertSame('Week Planner', $references[0]['title']);
        self::assertSame('A laptop shows a weekly shift plan.', $references[0]['alternative']);
    }

    public function testExplicitFileTitleIsKept(): void
    {
        $references = (new FixtureFieldNormalizer())->buildFileReferenceFixturesFromFixtureValue(
            ['file' => 'Resources/Public/Styleguide/Library/lib-logo-signal-bureau-ab0de907.webp', 'title' => 'Signal Bureau logo'],
        );

        self::assertSame('Signal Bureau logo', $references[0]['title'] ?? null);
    }
}
