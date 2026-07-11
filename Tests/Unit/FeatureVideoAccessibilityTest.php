<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class FeatureVideoAccessibilityTest extends TestCase
{
    private const ELEMENT_DIR = __DIR__ . '/../../ContentBlocks/ContentElements/feature-video';

    public function testNativeVideoSupportsCaptionsAndFallbackPlayback(): void
    {
        $config = Yaml::parseFile(self::ELEMENT_DIR . '/config.yaml');
        self::assertIsArray($config);

        $fields = $config['fields'] ?? null;
        self::assertIsArray($fields);

        $captionsField = null;
        foreach ($fields as $field) {
            if (is_array($field) && ($field['identifier'] ?? null) === 'captions_file') {
                $captionsField = $field;
                break;
            }
        }

        self::assertIsArray($captionsField);
        self::assertSame('File', $captionsField['type'] ?? null);
        self::assertSame('vtt', $captionsField['allowed'] ?? null);
        self::assertSame(1, $captionsField['maxitems'] ?? null);

        $template = (string)file_get_contents(self::ELEMENT_DIR . '/templates/frontend.html');
        self::assertStringContainsString('controls playsinline preload="metadata"', $template);
        self::assertStringContainsString('aria-label="{videoTitle -> f:format.trim()}"', $template);
        self::assertStringContainsString('<track kind="captions"', $template);
        self::assertStringContainsString('src="{data.captions_file.0.publicUrl}"', $template);
        self::assertStringContainsString('srclang="en" label="English"', $template);
        self::assertStringContainsString('<a href="{data.video_file.0.publicUrl}">', $template);
        self::assertSame(1, preg_match('/<video\b[^>]*>/s', $template, $videoTag));
        self::assertStringNotContainsString('autoplay', $videoTag[0]);
    }

    public function testBundledEnglishCaptionsUseWebVtt(): void
    {
        $captions = __DIR__ . '/../../Resources/Public/Styleguide/Video/desiderio-feature-video.en.vtt';
        self::assertFileExists($captions);

        $contents = (string)file_get_contents($captions);
        self::assertStringStartsWith("WEBVTT\n", $contents);
        self::assertMatchesRegularExpression(
            '/00:00:00\.110 --> 00:00:02\.820/',
            $contents
        );
        self::assertStringContainsString('Innesto then turns compatible shadcn', $contents);
    }
}
