<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * WCAG 2.2 contrast guard for every shadcn preset, light and dark:
 * text on the accent needs 4.5:1, muted text on every surface it is rendered
 * on (background, card, muted) 4.5:1, the accent against the page background
 * AND the lighter box surfaces (muted, secondary, accent) 3:1. The brand
 * link/text tokens are guarded at 4.5:1 on every surface they are used on:
 * --d-link (links on background/card) and --d-primary-text (links/text on
 * muted, secondary, accent boxes and primary tints) — per-preset solved
 * overrides when declared, else the generic 35% color-mix pull. Lightness is
 * solved per hue in Build/Scripts/generate-shadcn-presets.php; this test
 * keeps manual edits and upstream create-preset updates honest.
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
            $blocks = [
                'light' => $this->extractBlock($css, 'body[data-shadcn-preset="' . $preset . '"]'),
                'dark' => $this->extractBlock($css, '.dark body[data-shadcn-preset="' . $preset . '"]'),
            ];
            $modes = [
                'light' => array_merge($root, $this->parseOklchVariables($blocks['light'])),
                'dark' => array_merge($dark, $this->parseOklchVariables($blocks['dark'])),
            ];
            foreach ($modes as $mode => $variables) {
                foreach ([
                    ['primary-foreground', 'primary', self::TEXT_MINIMUM],
                    ['accent-foreground', 'accent', self::TEXT_MINIMUM],
                    ['muted-foreground', 'background', self::TEXT_MINIMUM],
                    ['muted-foreground', 'card', self::TEXT_MINIMUM],
                    ['muted-foreground', 'muted', self::TEXT_MINIMUM],
                    ['primary', 'background', self::UI_MINIMUM],
                    ['primary', 'muted', self::UI_MINIMUM],
                    ['primary', 'secondary', self::UI_MINIMUM],
                    ['primary', 'accent', self::UI_MINIMUM],
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

                if (!isset($variables['primary'])) {
                    continue;
                }
                $surface = $variables['card'] ?? $variables['background'] ?? null;
                if ($surface === null) {
                    continue;
                }
                $primary = $this->oklchToSrgb(...$variables['primary']);
                $aliases = $this->parseVarAliases($blocks[$mode]);

                // --d-primary-text: a preset block may declare a solved literal
                // or alias it back to var(--primary); without a declaration the
                // generic 35% pull towards hue-less black/white from the base
                // body / .dark body blocks applies. (A light-block declaration
                // never leaks into dark mode: the later `.dark body` base block
                // masks it at equal specificity.)
                if (isset($variables['d-primary-text'])) {
                    $text = $this->oklchToSrgb(...$variables['d-primary-text']);
                } elseif (($aliases['d-primary-text'] ?? null) === 'primary') {
                    $text = $primary;
                } else {
                    $target = [$mode === 'dark' ? 1.0 : 0.0, 0.0, $variables['primary'][2]];
                    $text = $this->oklchToSrgb(...$this->mixOklch($variables['primary'], $target, 0.35));
                }

                // --d-link colors links on the default background/card surfaces
                // (.link, the text-link utility, prose anchors). The base blocks
                // alias it to var(--primary); presets whose primary cannot serve
                // as small text there (bright amber/lime) override it.
                $link = isset($variables['d-link']) ? $this->oklchToSrgb(...$variables['d-link']) : $primary;
                foreach (['background', 'card'] as $linkSurface) {
                    if (!isset($variables[$linkSurface])) {
                        continue;
                    }
                    $ratio = $this->contrast($link, $this->oklchToSrgb(...$variables[$linkSurface]));
                    if ($ratio < self::TEXT_MINIMUM) {
                        $failures[] = sprintf('%s/%s d-link on %s = %.2f (< %.1f)', $preset, $mode, $linkSurface, $ratio, self::TEXT_MINIMUM);
                    }
                }

                // Search-result highlight (.results-highlight): --d-primary-text
                // on bg-primary/10 (primary/20 in dark), composited over the card.
                $tint = $this->compositeSrgb(
                    $primary,
                    $mode === 'dark' ? 0.2 : 0.1,
                    $this->oklchToSrgb(...$surface)
                );
                $ratio = $this->contrast($text, $tint);
                if ($ratio < self::TEXT_MINIMUM) {
                    $failures[] = sprintf('%s/%s d-primary-text on primary tint = %.2f (< %.1f)', $preset, $mode, $ratio, self::TEXT_MINIMUM);
                }

                // --d-link-on-muted (= --d-primary-text) colors links inside the
                // .ce-frame--bg-10/80 boxes (solid --muted / --secondary) and on
                // the tinted --accent cards. Guard those at 4.5:1 so a
                // brand-colored link never collides with its box.
                foreach (['muted', 'secondary', 'accent'] as $linkSurface) {
                    if (!isset($variables[$linkSurface])) {
                        continue;
                    }
                    $ratio = $this->contrast($text, $this->oklchToSrgb(...$variables[$linkSurface]));
                    if ($ratio < self::TEXT_MINIMUM) {
                        $failures[] = sprintf('%s/%s d-link-on-muted on %s = %.2f (< %.1f)', $preset, $mode, $linkSurface, $ratio, self::TEXT_MINIMUM);
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
     * Custom-property aliases within a block, e.g. `--d-primary-text: var(--primary);`
     * becomes ['d-primary-text' => 'primary'].
     *
     * @return array<string, string>
     */
    private function parseVarAliases(string $block): array
    {
        preg_match_all('/--([\w-]+):\s*var\(--([\w-]+)\)\s*;/', $block, $matches, PREG_SET_ORDER);
        $aliases = [];
        foreach ($matches as $match) {
            $aliases[$match[1]] = $match[2];
        }

        return $aliases;
    }

    /**
     * color-mix(in oklch, $base, $other $weight): linear on lightness and
     * chroma, shortest arc on hue. Note: a color *specified* in oklch keeps
     * its hue in interpolation even at chroma 0 — pass the carried hue in
     * $other explicitly when modelling a hue-less (`none`) mix target.
     *
     * @param array{float, float, float} $base
     * @param array{float, float, float} $other
     * @return array{float, float, float}
     */
    private function mixOklch(array $base, array $other, float $weight): array
    {
        $hueDelta = fmod($other[2] - $base[2] + 540.0, 360.0) - 180.0;

        return [
            $base[0] + ($other[0] - $base[0]) * $weight,
            $base[1] + ($other[1] - $base[1]) * $weight,
            fmod($base[2] + $hueDelta * $weight + 360.0, 360.0),
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
