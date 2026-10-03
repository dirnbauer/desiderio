<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class FeatureVideoAccessibilityTest extends TestCase
{
    private const string ELEMENT_DIR = __DIR__ . '/../../ContentBlocks/ContentElements/feature-video';

    public function testNativeVideoSupportsCaptionsAndFallbackPlayback(): void
    {
        $config = Yaml::parseFile(self::ELEMENT_DIR . '/config.yaml');
        self::assertIsArray($config);

        $fields = $config['fields'] ?? null;
        self::assertIsArray($fields);
        $captionsField = array_find($fields, fn($field) => is_array($field) && ($field['identifier'] ?? null) === 'captions_file');

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
        // The opening tag runs to the first <source>: a Fluid "->" inside it ends a [^>]* match early.
        self::assertSame(1, preg_match('/<video\b.*?(?=<source)/s', $template, $videoTag));
        // No native autoplay attribute: it would ignore reduced motion. A
        // reference flagged "autoplay" gets muted looping that desiderio.js
        // starts in view, never under prefers-reduced-motion.
        self::assertDoesNotMatchRegularExpression('/\sautoplay(?=[\s=>])/', $videoTag[0]);
        self::assertStringContainsString("{f:if(condition: data.video_file.0.properties.autoplay, then: 'muted loop data-d-autoplay')}", $videoTag[0]);
    }

    /**
     * The video content elements are editor features. The one video the
     * extension ships is the product film (Build/Film, rendered by
     * Build/Scripts/render-film.mjs): one MP4 and its poster, kept small, and
     * named exactly as the showcase references them. The feature-video render
     * scripts removed with 4.1.0 stay gone.
     */
    public function testOnlyTheProductFilmShips(): void
    {
        $directory = __DIR__ . '/../../Resources/Public/Styleguide/Video';
        $files = glob($directory . '/*');
        self::assertIsArray($files);
        $names = array_map('basename', $files);
        sort($names);

        self::assertCount(2, $names);
        self::assertMatchesRegularExpression('/^desiderio-film-[0-9a-f]{8}\.mp4$/', $names[0]);
        self::assertMatchesRegularExpression('/^desiderio-film-poster-[0-9a-f]{8}\.jpg$/', $names[1]);
        foreach ($files as $file) {
            self::assertLessThan(4 * 1024 * 1024, (int)filesize($file), basename($file));
        }

        $blocks = (string)file_get_contents(__DIR__ . '/../../Classes/Data/Showcase/ShowcaseBlocks.php');
        foreach ($names as $name) {
            self::assertStringContainsString('Resources/Public/Styleguide/Video/' . $name, $blocks);
        }

        self::assertFileDoesNotExist(__DIR__ . '/../../Build/Scripts/render-feature-videos.sh');
        self::assertFileDoesNotExist(__DIR__ . '/../../Build/Scripts/verify-feature-videos.sh');
    }
}
