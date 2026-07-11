<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

final class StyleguideFeatureVideoTest extends TestCase
{
    private const FEATURE_ASSET_SLUGS = [
        '/features' => 'overview',
        '/features/records-list' => 'records-list',
        '/features/mcp-server' => 'mcp-server',
        '/features/easy-workspace' => 'easy-workspace',
        '/features/blog' => 'blog',
        '/features/desiderio' => 'desiderio',
        '/features/solr' => 'solr',
        '/features/workos' => 'workos',
        '/features/powermail' => 'powermail',
        '/features/x402-paywall' => 'x402-paywall',
        '/features/typo3-abilities' => 'typo3-abilities',
        '/features/agentation' => 'agentation',
        '/features/sg-apicore' => 'sg-apicore',
        '/features/skillflow' => 'skillflow',
    ];

    public function testEveryFeaturePageSeedsExactlyOneNarratedLocalVideo(): void
    {
        $featurePages = [];
        foreach (StyleguideShowcasePages::subpages() as $candidate) {
            $slug = $candidate['slug'] ?? null;
            if (is_string($slug) && ($slug === '/features' || str_starts_with($slug, '/features/'))) {
                $featurePages[$slug] = $candidate;
            }
        }

        self::assertCount(count(self::FEATURE_ASSET_SLUGS), $featurePages);
        self::assertEqualsCanonicalizing(array_keys(self::FEATURE_ASSET_SLUGS), array_keys($featurePages));

        foreach (self::FEATURE_ASSET_SLUGS as $pageSlug => $assetSlug) {
            $page = $featurePages[$pageSlug];
            $content = $page['content'] ?? null;
            self::assertIsArray($content, $pageSlug . ' must seed content blocks.');

            $videoBlocks = array_values(array_filter(
                $content,
                static fn (array $block): bool => ($block['ctype'] ?? null) === 'desiderio_featurevideo'
            ));
            self::assertCount(1, $videoBlocks, $pageSlug . ' must seed exactly one feature video.');

            $contentTypes = array_column($content, 'ctype');
            $textmediaIndex = array_search('desiderio_textmedia', $contentTypes, true);
            $videoIndex = array_search('desiderio_featurevideo', $contentTypes, true);
            $benefitCardsIndex = array_search('desiderio_benefitcards', $contentTypes, true);
            self::assertIsInt($textmediaIndex);
            self::assertIsInt($videoIndex);
            self::assertIsInt($benefitCardsIndex);
            self::assertGreaterThan($textmediaIndex, $videoIndex, $pageSlug . ' video must follow its explanatory media block.');
            self::assertLessThan($benefitCardsIndex, $videoIndex, $pageSlug . ' video must precede its benefit cards.');

            $fields = $videoBlocks[0]['fields'];
            self::assertIsArray($fields);
            self::assertArrayNotHasKey('video_url', $fields, 'A video URL would hide the bundled narrated MP4.');

            $header = $fields['header'] ?? null;
            self::assertIsString($header);
            self::assertNotSame('', trim($header));

            $description = $fields['description'] ?? null;
            self::assertIsString($description);
            self::assertNotSame('', trim($description));

            $expectedFiles = [
                'video_file' => 'Resources/Public/Styleguide/Video/' . $assetSlug . '-feature-video.mp4',
                'captions_file' => 'Resources/Public/Styleguide/Video/' . $assetSlug . '-feature-video.en.vtt',
                'poster' => 'Resources/Public/Styleguide/Video/' . $assetSlug . '-feature-video-poster.webp',
            ];

            foreach ($expectedFiles as $fieldName => $relativePath) {
                $fixture = $fields[$fieldName] ?? null;
                self::assertIsArray($fixture, $pageSlug . ' ' . $fieldName . ' must use an explicit FAL fixture.');
                self::assertSame($relativePath, $fixture['file'] ?? null);
                self::assertFileExists(dirname(__DIR__, 2) . '/' . $relativePath);
            }
        }
    }
}
