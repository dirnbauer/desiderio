<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

final class StyleguideFeatureVideoTest extends TestCase
{
    public function testFeaturePagesDoNotSeedVideoContent(): void
    {
        $featurePages = array_filter(
            StyleguideShowcasePages::subpages(),
            static fn(array $page): bool => $page['slug'] === '/features'
                || str_starts_with($page['slug'], '/features/'),
        );

        self::assertNotEmpty($featurePages);
        foreach ($featurePages as $page) {
            foreach ($page['content'] as $block) {
                self::assertStringNotContainsString(
                    'video',
                    strtolower($block['ctype']),
                    sprintf('%s must not seed video content.', $page['slug']),
                );
            }
        }
    }

    public function testAccessibleVideoContentBlocksRemainAvailable(): void
    {
        $root = dirname(__DIR__, 2);

        self::assertFileExists($root . '/ContentBlocks/ContentElements/feature-video/config.yaml');
        self::assertFileExists($root . '/ContentBlocks/ContentElements/video-embed/config.yaml');
    }
}
