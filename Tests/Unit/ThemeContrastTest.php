<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * WCAG 2.2 contrast guard for every shadcn preset, light and dark:
 * text on the accent needs 4.5:1, muted text on every surface it is rendered
 * on (background, card, muted) 4.5:1, the accent against the page background
 * 3:1. Lightness is solved per hue in Build/Scripts/generate-shadcn-presets.php;
 * this test keeps manual edits and upstream create-preset updates honest.
 */
final class ThemeContrastTest extends TestCase
{
    private const TEXT_MINIMUM = 4.5;
    private const UI_MINIMUM = 3.0;

    public function testEveryPresetMeetsWcagContrastInBothColorSchemes(): void
    {
        $css = (string)file_get_contents(__DIR__ . '/../../Resources/Public/Css/shadcn-theme.css');

        $root = $this->parseOklchVariables($this->extractBlock($css, ':root'));
        $dark = $this->parseOklchVariables($this->extractBlock($css, '.dark'));

        preg_match_all('/data-shadcn-preset="(\w+)"/', $css, $matches);
        $presets = array_values(array_unique($matches[1]));
        self::assertNotEmpty($presets);

        $failures = [];
        foreach ($presets as $preset) {
            if ($preset === 'custom') {
                continue;
            }
            $modes = [
                'light' => array_merge($root, $this->parseOklchVariables($this->extractBlock($css, 'body[data-shadcn-preset="' . $preset . '"]'))),
                'dark' => array_merge($dark, $this->parseOklchVariables($this->extractBlock($css, '.dark body[data-shadcn-preset="' . $preset . '"]'))),
            ];
            foreach ($modes as $mode => $variables) {
                foreach ([
                    ['primary-foreground', 'primary', self::TEXT_MINIMUM],
                    ['accent-foreground', 'accent', self::TEXT_MINIMUM],
                    ['muted-foreground', 'background', self::TEXT_MINIMUM],
                    ['muted-foreground', 'card', self::TEXT_MINIMUM],
                    ['muted-foreground', 'muted', self::TEXT_MINIMUM],
                    ['primary', 'background', self::UI_MINIMUM],
                ] as [$foreground, $background, $minimum]) {
                    if (!isset($variables[$foreground], $variables[$background])) {
                        continue;
                    }
                    $ratio = $this->contrast(
                        $this->oklchToSrgb(...$variables[$foreground]),
                        $this->oklchToSrgb(...$variables[$background])
                    );
                    if ($ratio < $minimum) {
                        $failures[] = sprintf('%s/%s %s on %s = %.2f (< %.1f)', $preset, $mode, $foreground, $background, $ratio, $minimum);
                    }
                }

                // Search-result highlight (.results-highlight): --d-primary-text
                // (primary pulled 35% towards the foreground, mixed in oklch) on
                // bg-primary/10 (primary/20 in dark), alpha-composited over the card.
                if (isset($variables['primary'], $variables['foreground'])) {
                    $surface = $variables['card'] ?? $variables['background'] ?? null;
                    if ($surface !== null) {
                        $text = $this->oklchToSrgb(...$this->mixOklch($variables['primary'], $variables['foreground'], 0.35));
                        $tint = $this->compositeSrgb(
                            $this->oklchToSrgb(...$variables['primary']),
                            $mode === 'dark' ? 0.2 : 0.1,
                            $this->oklchToSrgb(...$surface)
                        );
                        $ratio = $this->contrast($text, $tint);
                        if ($ratio < self::TEXT_MINIMUM) {
                            $failures[] = sprintf('%s/%s d-primary-text on primary tint = %.2f (< %.1f)', $preset, $mode, $ratio, self::TEXT_MINIMUM);
                        }
                    }
                }
            }
        }

        self::assertSame([], $failures, "WCAG 2.2 contrast failures:\n" . implode("\n", $failures));
    }

    private function extractBlock(string $css, string $selector): string
    {
        $pattern = '/(?<![\w.\]-])' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/';
        if ($selector === ':root' || $selector === '.dark') {
            $pattern = '/^' . preg_quote($selector, '/') . '\s*\{([^}]*)\}/m';
        }

        return preg_match($pattern, $css, $match) === 1 ? $match[1] : '';
    }

    /**
     * @return array<string, array{float, float, float}>
     */
    private function parseOklchVariables(string $block): array
    {
        preg_match_all('/--([\w-]+):\s*oklch\(([\d.]+)\s+([\d.]+)\s+([\d.]+)(?:\s*\/\s*[\d.%]+)?\)/', $block, $matches, PREG_SET_ORDER);
        $variables = [];
        foreach ($matches as $match) {
            $variables[$match[1]] = [(float)$match[2], (float)$match[3], (float)$match[4]];
        }

        return $variables;
    }

    /**
     * color-mix(in oklch, $base, $other $weight): linear on lightness and
     * chroma, shortest arc on hue. An achromatic color's hue (chroma 0) is
     * powerless per CSS Color 4 and treated as missing, so the other color's
     * hue carries through unrotated.
     *
     * @param array{float, float, float} $base
     * @param array{float, float, float} $other
     * @return array{float, float, float}
     */
    private function mixOklch(array $base, array $other, float $weight): array
    {
        if ($other[1] === 0.0) {
            $hue = $base[2];
        } elseif ($base[1] === 0.0) {
            $hue = $other[2];
        } else {
            $hueDelta = fmod($other[2] - $base[2] + 540.0, 360.0) - 180.0;
            $hue = fmod($base[2] + $hueDelta * $weight + 360.0, 360.0);
        }

        return [
            $base[0] + ($other[0] - $base[0]) * $weight,
            $base[1] + ($other[1] - $base[1]) * $weight,
            $hue,
        ];
    }

    /**
     * Simple alpha compositing on gamma-encoded sRGB, as the browser blends
     * an oklch(... / alpha) background over the surface beneath it.
     *
     * @param array{float, float, float} $foreground
     * @param array{float, float, float} $background
     * @return array{float, float, float}
     */
    private function compositeSrgb(array $foreground, float $alpha, array $background): array
    {
        return [
            $foreground[0] * $alpha + $background[0] * (1 - $alpha),
            $foreground[1] * $alpha + $background[1] * (1 - $alpha),
            $foreground[2] * $alpha + $background[2] * (1 - $alpha),
        ];
    }

    /**
     * @return array{float, float, float} Gamma-encoded sRGB, clamped to gamut.
     */
    private function oklchToSrgb(float $lightness, float $chroma, float $hue): array
    {
        $hr = deg2rad($hue);
        $a = $chroma * cos($hr);
        $b = $chroma * sin($hr);
        $l = ($lightness + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
        $m = ($lightness - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
        $s = ($lightness - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;
        $r = +4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
        $g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
        $blue = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;
        $gamma = static function (float $channel): float {
            $channel = max(0.0, min(1.0, $channel));

            return $channel <= 0.0031308 ? 12.92 * $channel : 1.055 * $channel ** (1 / 2.4) - 0.055;
        };

        return [$gamma($r), $gamma($g), $gamma($blue)];
    }

    /**
     * @param array{float, float, float} $first
     * @param array{float, float, float} $second
     */
    private function contrast(array $first, array $second): float
    {
        $lighter = $this->luminance(...$first);
        $darker = $this->luminance(...$second);
        if ($lighter < $darker) {
            [$lighter, $darker] = [$darker, $lighter];
        }

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    private function luminance(float $red, float $green, float $blue): float
    {
        $linear = static fn (float $channel): float => $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;

        return 0.2126 * $linear($red) + 0.7152 * $linear($green) + 0.0722 * $linear($blue);
    }
}
