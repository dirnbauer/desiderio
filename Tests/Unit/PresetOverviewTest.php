<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * The themes page is generated from shadcn-theme.css
 * (Build/Scripts/build-preset-overview.php). These tests are the gate that
 * keeps the two committed artefacts in step with the presets: a preset added,
 * renamed or recoloured without re-running the generator fails here instead of
 * quietly shipping a page that describes the previous release.
 */
final class PresetOverviewTest extends TestCase
{
    private const EXTENSION_ROOT = __DIR__ . '/../..';

    public function testEverySelectablePresetHasALiveSample(): void
    {
        $samples = self::sampleSelectors();

        foreach (self::presetIds() as $preset) {
            self::assertContains(
                $preset,
                $samples,
                sprintf(
                    'Preset "%s" has no element-scoped copy in preset-samples.css. Run: php Build/Scripts/build-preset-overview.php',
                    $preset
                )
            );
        }

        self::assertSame(
            [],
            array_diff($samples, self::presetIds()),
            'preset-samples.css carries a preset that no longer exists in shadcn-theme.css.'
        );
    }

    public function testSamplesCarryTheSurfacesOfTheirOwnPreset(): void
    {
        // A sample must be self-contained rather than a set of overrides: it
        // renders inside a page painted by a DIFFERENT preset, so anything it
        // fails to declare it inherits from the wrong theme.
        foreach (self::presetIds() as $preset) {
            $tokens = self::sampleTokens($preset, false);
            foreach (['background', 'foreground', 'primary', 'primary-foreground', 'muted', 'border', 'radius'] as $token) {
                self::assertArrayHasKey(
                    $token,
                    $tokens,
                    sprintf('The "%s" sample does not declare --%s and would inherit it from the site preset.', $preset, $token)
                );
            }

            $dark = self::sampleTokens($preset, true);
            foreach (['background', 'foreground', 'primary'] as $token) {
                self::assertArrayHasKey(
                    $token,
                    $dark,
                    sprintf('The "%s" sample keeps its light --%s in dark mode.', $preset, $token)
                );
            }
        }
    }

    public function testSampleColoursMatchTheThemeStylesheet(): void
    {
        $css = self::themeCss();

        foreach (self::presetIds() as $preset) {
            $source = $preset === 'b0'
                ? self::parseBlock($css, ':root')
                : self::parseBlock($css, sprintf('body[data-shadcn-preset="%s"]', $preset));
            $sample = self::sampleTokens($preset, false);

            foreach (['primary', 'radius', 'd-font-sans'] as $token) {
                if (!isset($source[$token])) {
                    continue;
                }
                self::assertSame(
                    $source[$token],
                    $sample[$token] ?? null,
                    sprintf(
                        'The "%s" sample renders --%s differently from shadcn-theme.css. Run: php Build/Scripts/build-preset-overview.php',
                        $preset,
                        $token
                    )
                );
            }
        }
    }

    public function testOverviewPartialShowsEveryPreset(): void
    {
        $partial = (string)file_get_contents(
            self::EXTENSION_ROOT . '/Resources/Private/Templates/Partials/Pages/PresetOverview.fluid.html'
        );

        foreach (self::presetIds() as $preset) {
            self::assertStringContainsString(
                sprintf('<article class="preset-card" data-shadcn-preset-sample="%s">', $preset),
                $partial,
                sprintf(
                    'The themes page has no card for preset "%s". Run: php Build/Scripts/build-preset-overview.php',
                    $preset
                )
            );
        }

        self::assertStringContainsString(
            'EXT:desiderio/Resources/Public/Css/preset-samples.css',
            $partial,
            'The overview partial no longer loads the element-scoped preset stylesheet, so every card would render in the site preset.'
        );
    }

    /** @return list<string> every preset id in shadcn-theme.css, b0 (the :root block) included */
    private static function presetIds(): array
    {
        preg_match_all('/^body\[data-shadcn-preset="([^"]+)"\]\s*\{/m', self::themeCss(), $found);

        return array_merge(['b0'], array_values(array_unique($found[1])));
    }

    /** @return list<string> every preset id with an element-scoped copy */
    private static function sampleSelectors(): array
    {
        preg_match_all('/^\[data-shadcn-preset-sample="([^"]+)"\]\s*\{/m', self::sampleCss(), $found);

        return array_values(array_unique($found[1]));
    }

    /** @return array<string, string> */
    private static function sampleTokens(string $preset, bool $dark): array
    {
        return self::parseBlock(
            self::sampleCss(),
            sprintf('%s[data-shadcn-preset-sample="%s"]', $dark ? '.dark ' : '', $preset)
        );
    }

    /** @return array<string, string> token name (without --) => raw value */
    private static function parseBlock(string $css, string $selector): array
    {
        $pattern = '/^' . preg_quote($selector, '/') . '\s*\{(.*?)^\}/ms';
        if (preg_match($pattern, $css, $match) !== 1) {
            return [];
        }

        $declarations = [];
        if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $match[1], $found, PREG_SET_ORDER) > 0) {
            foreach ($found as $declaration) {
                $declarations[$declaration[1]] = trim((string)preg_replace('#/\*.*?\*/#s', '', $declaration[2]));
            }
        }

        return $declarations;
    }

    private static function themeCss(): string
    {
        return (string)file_get_contents(self::EXTENSION_ROOT . '/Resources/Public/Css/shadcn-theme.css');
    }

    private static function sampleCss(): string
    {
        return (string)file_get_contents(self::EXTENSION_ROOT . '/Resources/Public/Css/preset-samples.css');
    }
}
