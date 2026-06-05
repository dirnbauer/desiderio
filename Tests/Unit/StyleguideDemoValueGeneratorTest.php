<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;

final class StyleguideDemoValueGeneratorTest extends TestCase
{
    public function testBuildDefaultFieldValueUsesCompactCountText(): void
    {
        $generator = new StyleguideDemoValueGenerator();

        self::assertSame(
            '128',
            $generator->buildDefaultFieldValue(
                'desiderio_categorycards',
                'Category Cards Pricing',
                'count',
                ['identifier' => 'count', 'type' => 'Textarea'],
                0,
            )
        );
    }

    public function testBuildDefaultFieldValueUsesPhpCodeBlockExample(): void
    {
        $generator = new StyleguideDemoValueGenerator();

        $codeBlockValue = $generator->buildDefaultFieldValue(
            'desiderio_codeblock',
            'Code Block',
            'code',
            ['identifier' => 'code', 'type' => 'Textarea'],
            0,
        );
        self::assertIsString($codeBlockValue);
        self::assertStringContainsString(
            'ArticleTeaserRenderer',
            $codeBlockValue,
        );
    }

    public function testNormalizeResolvedFixtureFieldValueRewritesShadcnMapUrls(): void
    {
        $generator = new StyleguideDemoValueGenerator();

        $embedUrl = $generator->normalizeResolvedFixtureFieldValue(
            'desiderio_mapembed',
            'embed_url',
            'https://ui.shadcn.com/docs/components/map',
        );
        self::assertIsString($embedUrl);
        self::assertStringContainsString(
            'openstreetmap.org',
            $embedUrl,
        );
    }
}
