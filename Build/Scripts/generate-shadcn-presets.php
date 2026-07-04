#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generate the Desiderio "house" shadcn presets.
 *
 * Each house preset inherits the authentic neutral base palette from :root and
 * overrides only the distinguishing tokens (accent colour, radius, fonts, and
 * optionally compact control density). Because the values cascade from the
 * data-shadcn-preset body attribute, selecting one in the site configuration
 * re-themes the site at runtime with no rebuild — exactly like the curated
 * create presets.
 *
 * Besides the primary/accent pair, each preset gets solved brand link/text
 * tokens: --d-primary-text (readable on muted/secondary/accent boxes and
 * primary tints, 4.5:1) and, where the primary cannot serve as small text on
 * the plain background/card, a --d-link override. Solving per hue keeps the
 * maximum brand chroma instead of the washed-out flat 35% color-mix fallback
 * defined in shadcn-theme.css.
 *
 * The CSS is written into Resources/Public/Css/shadcn-theme.css between marker
 * comments (idempotent: re-running replaces the block). The matching settings
 * enum, sync-script preset map, and IconRegistry arms are printed to stdout.
 *
 * Usage: php Build/Scripts/generate-shadcn-presets.php
 */

$root = dirname(__DIR__, 2);
$themePath = $root . '/Resources/Public/Css/shadcn-theme.css';

$fonts = [
    'inter' => '"Inter Variable", ui-sans-serif, system-ui, sans-serif',
    'geist' => '"Geist Variable", ui-sans-serif, system-ui, sans-serif',
    'nunito' => '"Nunito Sans Variable", ui-sans-serif, system-ui, sans-serif',
    'jetbrains' => '"JetBrains Mono Variable", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace',
];
$mono = '"JetBrains Mono Variable", ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace';

// id, label, hue, lightAccent (needs dark foreground), radius rem, font key, icon,
// density profile (compact|default|comfortable), focus-ring width, surface shadow (none|sm|md)
$presets = [
    ['aurora',   'Aurora — violet',   293, false, '0.625', 'inter',     'lucide',    'default',     '3px', 'sm'],
    ['marine',   'Marine — blue',     259, false, '0.5',   'geist',     'tabler',    'comfortable', '2px', 'md'],
    ['forest',   'Forest — emerald',  158, false, '0.375', 'inter',     'phosphor',  'default',     '2px', 'sm'],
    ['ember',    'Ember — orange',     55, false, '0.75',  'nunito',    'hugeicons', 'comfortable', '3px', 'md'],
    ['bloom',    'Bloom — rose',       12, false, '1',     'inter',     'lucide',    'default',     '3px', 'sm'],
    ['lagoon',   'Lagoon — teal',     185, false, '0.5',   'geist',     'phosphor',  'compact',     '1px', 'none'],
    ['gold',     'Gold — amber',       85, true,  '0.375', 'inter',     'tabler',    'default',     '2px', 'sm'],
    ['midnight', 'Midnight — indigo', 275, false, '0.625', 'inter',     'lucide',    'comfortable', '3px', 'md'],
    ['blossom',  'Blossom — pink',    350, false, '0.75',  'nunito',    'remixicon', 'default',     '2px', 'sm'],
    ['citrus',   'Citrus — lime mono',130, true,  '0.3',   'jetbrains', 'tabler',    'compact',     '1px', 'none'],
];

// Control density profiles (default inherits :root). Empty array = no override.
$densityProfiles = [
    'compact' => ['--d-control-h' => '2rem', '--d-control-text' => '0.75rem', '--d-control-leading' => '1rem', '--d-control-px' => '0.625rem'],
    'default' => [],
    'comfortable' => ['--d-control-h' => '2.5rem', '--d-control-text' => '0.875rem', '--d-control-leading' => '1.5rem', '--d-control-px' => '0.875rem'],
];

// Surface elevation tokens (none is a valid transparent shadow, safe inside the box-shadow list).
$shadowTokens = [
    'none' => '0 0 #0000',
    'sm' => 'var(--shadow-sm)',
    'md' => 'var(--shadow-md)',
];

// --------------------------------------------------------------------------
// WCAG 2.2 contrast solver. OKLCH lightness is perceptual, but WCAG contrast
// works on sRGB relative luminance, which shifts with hue and chroma — a
// violet and an emerald at the same OKLCH L have very different ratios
// against white. Lightness is therefore solved per hue so every preset meets
// 4.5:1 for text on the accent and 3:1 for the accent against the page
// background, in both color schemes.
// --------------------------------------------------------------------------

/** @return array{float, float, float} Gamma-encoded sRGB, clamped to gamut. */
function oklchToSrgb(float $L, float $C, float $H): array
{
    $hr = deg2rad($H);
    $a = $C * cos($hr);
    $b = $C * sin($hr);
    $l_ = $L + 0.3963377774 * $a + 0.2158037573 * $b;
    $m_ = $L - 0.1055613458 * $a - 0.0638541728 * $b;
    $s_ = $L - 0.0894841775 * $a - 1.2914855480 * $b;
    $l = $l_ ** 3;
    $m = $m_ ** 3;
    $s = $s_ ** 3;
    $r = +4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
    $g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
    $bl = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;
    $gam = static function (float $c): float {
        $c = max(0.0, min(1.0, $c));

        return $c <= 0.0031308 ? 12.92 * $c : 1.055 * $c ** (1 / 2.4) - 0.055;
    };

    return [$gam($r), $gam($g), $gam($bl)];
}

/** @param array{float, float, float} $rgb */
function relativeLuminance(array $rgb): float
{
    $lin = static fn (float $c): float => $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;

    return 0.2126 * $lin($rgb[0]) + 0.7152 * $lin($rgb[1]) + 0.0722 * $lin($rgb[2]);
}

/**
 * @param array{float, float, float} $a
 * @param array{float, float, float} $b
 */
function contrastRatio(array $a, array $b): float
{
    $l1 = relativeLuminance($a);
    $l2 = relativeLuminance($b);
    if ($l1 < $l2) {
        [$l1, $l2] = [$l2, $l1];
    }

    return ($l1 + 0.05) / ($l2 + 0.05);
}

/**
 * Alpha-composite a foreground over a surface on gamma-encoded sRGB, the way
 * the browser blends an oklch(... / alpha) tint over what lies beneath it.
 *
 * @param array{float, float, float} $foreground
 * @param array{float, float, float} $background
 * @return array{float, float, float}
 */
function compositeSrgb(array $foreground, float $alpha, array $background): array
{
    return [
        $foreground[0] * $alpha + $background[0] * (1 - $alpha),
        $foreground[1] * $alpha + $background[1] * (1 - $alpha),
        $foreground[2] * $alpha + $background[2] * (1 - $alpha),
    ];
}

/** True when oklch(L C H) converts to sRGB without any channel clamping. */
function inSrgbGamut(float $L, float $C, float $H): bool
{
    $hr = deg2rad($H);
    $a = $C * cos($hr);
    $b = $C * sin($hr);
    $l = ($L + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
    $m = ($L - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
    $s = ($L - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;
    $r = +4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
    $g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
    $bl = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;

    return $r >= 0.0 && $r <= 1.0 && $g >= 0.0 && $g <= 1.0 && $bl >= 0.0 && $bl <= 1.0;
}

/**
 * Largest chroma <= $cap keeping oklch(L C H) inside the sRGB gamut, so the
 * emitted value is exactly what browsers render (no gamut-mapping drift).
 */
function maxInGamutChroma(float $L, float $H, float $cap): float
{
    if (inSrgbGamut($L, $cap, $H)) {
        return $cap;
    }
    $min = 0.0;
    $max = $cap;
    for ($i = 0; $i < 30; $i++) {
        $mid = ($min + $max) / 2;
        if (inSrgbGamut($L, $mid, $H)) {
            $min = $mid;
        } else {
            $max = $mid;
        }
    }

    return floor($min * 1000) / 1000;
}

/**
 * Brand-hued text color for LIGHT surfaces: the highest lightness (at the
 * largest in-gamut chroma up to $chromaCap) that still meets $target against
 * every reference surface. Contrast against lighter references shrinks as L
 * grows, so bisect from above; chroma is re-capped per candidate lightness.
 *
 * @param array<array{float, float, float}> $references
 * @return array{float, float} [lightness, chroma]
 */
function solveLightTextColor(float $chromaCap, int $hue, array $references, float $target): array
{
    $meets = static function (float $L) use ($chromaCap, $hue, $references, $target): bool {
        $C = maxInGamutChroma($L, (float)$hue, $chromaCap);
        $rgb = oklchToSrgb($L, $C, (float)$hue);
        foreach ($references as $reference) {
            if (contrastRatio($rgb, $reference) < $target) {
                return false;
            }
        }

        return true;
    };
    $min = 0.2;
    $max = 0.75;
    for ($i = 0; $i < 40; $i++) {
        $mid = ($min + $max) / 2;
        if ($meets($mid)) {
            $min = $mid;
        } else {
            $max = $mid;
        }
    }
    $L = round($min, 3);

    return [$L, maxInGamutChroma($L, (float)$hue, $chromaCap)];
}

/**
 * Brand-hued text color for DARK surfaces: the lowest lightness meeting
 * $target against every reference (contrast grows with L — bisect from below).
 *
 * @param array<array{float, float, float}> $references
 * @return array{float, float} [lightness, chroma]
 */
function solveDarkTextColor(float $chromaCap, int $hue, array $references, float $target): array
{
    $meets = static function (float $L) use ($chromaCap, $hue, $references, $target): bool {
        $C = maxInGamutChroma($L, (float)$hue, $chromaCap);
        $rgb = oklchToSrgb($L, $C, (float)$hue);
        foreach ($references as $reference) {
            if (contrastRatio($rgb, $reference) < $target) {
                return false;
            }
        }

        return true;
    };
    $min = 0.5;
    $max = 0.95;
    for ($i = 0; $i < 40; $i++) {
        $mid = ($min + $max) / 2;
        if ($meets($mid)) {
            $max = $mid;
        } else {
            $min = $mid;
        }
    }
    $L = round($max, 3);

    return [$L, maxInGamutChroma($L, (float)$hue, $chromaCap)];
}

/**
 * Largest accent lightness in [$min, $max] whose contrast against $reference
 * still meets $target. Contrast against a LIGHTER reference shrinks as L
 * grows, so the boundary is found by bisection from above.
 *
 * @param array{float, float, float} $reference
 */
function solveMaxLightness(float $min, float $max, float $chroma, int $hue, array $reference, float $target): float
{
    for ($i = 0; $i < 40; $i++) {
        $mid = ($min + $max) / 2;
        if (contrastRatio(oklchToSrgb($mid, $chroma, (float)$hue), $reference) >= $target) {
            $min = $mid;
        } else {
            $max = $mid;
        }
    }

    return round($min, 3);
}

/**
 * @return array{light: array<string,string>, dark: array<string,string>}
 */
function accentTokens(int $hue, bool $lightAccent): array
{
    $h = (string) $hue;
    // Solver targets carry a small margin above the WCAG 2.2 minima
    // (4.5:1 text, 3:1 non-text) to absorb browser gamut-mapping drift.
    $textTarget = 4.55;
    $uiTarget = 3.05;
    $whiteFg = oklchToSrgb(0.985, 0.0, 0.0);

    // Neutral base surfaces the presets inherit, plus the preset's own tinted
    // accent surface — the strictest light surface the brand color must hold
    // 3:1 (UI) and, as link text, 4.5:1 against.
    $lightAccentSurface = oklchToSrgb(0.95, 0.03, (float)$hue);
    $lightMuted = oklchToSrgb(0.97, 0.0, 0.0);
    $darkAccentSurface = oklchToSrgb(0.33, 0.045, (float)$hue);
    $darkMuted = oklchToSrgb(0.269, 0.0, 0.0);
    $darkCard = oklchToSrgb(0.205, 0.0, 0.0);
    $lightCard = oklchToSrgb(1.0, 0.0, 0.0);

    if ($lightAccent) {
        // Bright accents (amber/lime): dark text on the accent. The light-mode
        // accent must hold the 3:1 UI floor on every light surface it sits on;
        // its own tinted accent box (0.95) is the strictest, so solve there.
        $lpL = solveMaxLightness(0.4, 0.9, 0.16, $hue, $lightAccentSurface, $uiTarget);
        $lpC = 0.16;
        $lp = "oklch({$lpL} 0.16 {$h})";
        $lpFgL = solveMaxLightness(0.1, 0.45, 0.05, $hue, oklchToSrgb($lpL, 0.16, (float)$hue), $textTarget);
        $lpFg = "oklch({$lpFgL} 0.05 {$h})";
        $dpL = 0.84;
        $dpC = 0.17;
        $dp = "oklch(0.84 0.17 {$h})";
        $dpFgL = solveMaxLightness(0.1, 0.45, 0.05, $hue, oklchToSrgb(0.84, 0.17, (float)$hue), $textTarget);
        $dpFg = "oklch({$dpFgL} 0.05 {$h})";
    } else {
        // Saturated accents: white text in light mode (solve the accent), a
        // brighter accent with dark text in dark mode (shadcn convention).
        $lpL = solveMaxLightness(0.3, 0.65, 0.2, $hue, $whiteFg, $textTarget);
        $lpC = 0.2;
        $lp = "oklch({$lpL} 0.2 {$h})";
        $lpFg = 'oklch(0.985 0 0)';
        $dpL = 0.66;
        $dpC = 0.18;
        $dp = "oklch(0.66 0.18 {$h})";
        $dpFgL = solveMaxLightness(0.1, 0.4, 0.035, $hue, oklchToSrgb(0.66, 0.18, (float)$hue), $textTarget);
        $dpFg = "oklch({$dpFgL} 0.035 {$h})";
    }

    // Solved brand link/text colors for the lighter content surfaces (muted /
    // secondary boxes, tinted accent cards, primary-tinted highlights). The
    // flat 35% color-mix fallback in shadcn-theme.css passes everywhere but
    // overshoots to 7-18:1, washing the brand out of links; solving per hue
    // keeps maximum chroma at just above 4.5:1. When the primary itself already
    // clears the text minimum on a surface set, it is emitted as
    // var(--primary) so links stay exactly on-brand.
    $lightPrimaryRgb = oklchToSrgb($lpL, $lpC, (float)$hue);
    $darkPrimaryRgb = oklchToSrgb($dpL, $dpC, (float)$hue);
    $lightTint = compositeSrgb($lightPrimaryRgb, 0.1, $lightCard);
    $darkTint = compositeSrgb($darkPrimaryRgb, 0.2, $darkCard);
    $lightLinkSurfaces = [$lightMuted, $lightAccentSurface, $lightTint];
    $darkLinkSurfaces = [$darkMuted, $darkAccentSurface, $darkTint];

    $meetsAll = static function (array $rgb, array $surfaces, float $target): bool {
        foreach ($surfaces as $surface) {
            if (contrastRatio($rgb, $surface) < $target) {
                return false;
            }
        }

        return true;
    };

    if ($meetsAll($lightPrimaryRgb, $lightLinkSurfaces, $textTarget)) {
        $lightPrimaryText = 'var(--primary)';
    } else {
        [$lptL, $lptC] = solveLightTextColor($lpC, $hue, $lightLinkSurfaces, $textTarget);
        $lightPrimaryText = "oklch({$lptL} {$lptC} {$h})";
    }
    if ($meetsAll($darkPrimaryRgb, $darkLinkSurfaces, $textTarget)) {
        $darkPrimaryText = 'var(--primary)';
    } else {
        [$dptL, $dptC] = solveDarkTextColor($dpC, $hue, $darkLinkSurfaces, $textTarget);
        $darkPrimaryText = "oklch({$dptL} {$dptC} {$h})";
    }

    $light = [
        '--primary' => $lp,
        '--primary-foreground' => $lpFg,
        '--accent' => "oklch(0.95 0.03 {$h})",
        '--accent-foreground' => "oklch(0.32 0.07 {$h})",
        '--ring' => $lp,
        '--sidebar-primary' => $lp,
        '--sidebar-primary-foreground' => $lpFg,
        '--sidebar-ring' => $lp,
        '--chart-1' => $lp,
        '--d-primary-text' => $lightPrimaryText,
    ];
    $dark = [
        '--primary' => $dp,
        '--primary-foreground' => $dpFg,
        '--accent' => "oklch(0.33 0.045 {$h})",
        '--accent-foreground' => "oklch(0.96 0.02 {$h})",
        '--ring' => $dp,
        '--sidebar-primary' => $dp,
        '--sidebar-primary-foreground' => $dpFg,
        '--sidebar-ring' => $dp,
        '--chart-1' => $dp,
        '--d-primary-text' => $darkPrimaryText,
    ];

    // --d-link colors links on the default background/card surfaces. The base
    // body block aliases it to var(--primary); an override is only needed when
    // the primary cannot serve as small text there (bright amber/lime). A
    // light-block literal would also cascade into dark mode past the plain
    // `.dark body` mask only when the dark block stays silent, so the dark
    // side always re-declares alongside a light override.
    if (contrastRatio($lightPrimaryRgb, $lightCard) < $textTarget) {
        $light['--d-link'] = $lightPrimaryText === 'var(--primary)' ? $lp : $lightPrimaryText;
        $dark['--d-link'] = contrastRatio($darkPrimaryRgb, $darkCard) < $textTarget
            ? ($darkPrimaryText === 'var(--primary)' ? $dp : $darkPrimaryText)
            : 'var(--primary)';
    } elseif (contrastRatio($darkPrimaryRgb, $darkCard) < $textTarget) {
        $dark['--d-link'] = $darkPrimaryText === 'var(--primary)' ? $dp : $darkPrimaryText;
    }

    return ['light' => $light, 'dark' => $dark];
}

$css = "/* === BEGIN Desiderio house presets (generated by Build/Scripts/generate-shadcn-presets.php) === */\n";
$enumLines = [];
$mapLines = [];
$iconLines = [];

$contrastFailures = [];
$parseOklch = static function (string $value): array {
    preg_match('/oklch\(([\d.]+)\s+([\d.]+)\s+([\d.]+)\)/', $value, $m);

    return oklchToSrgb((float)$m[1], (float)$m[2], (float)$m[3]);
};

// Neutral base surfaces every house preset inherits from :root / .dark in
// shadcn-theme.css (kept in sync manually — the generator never writes them).
// Muted text is verified against all surfaces it is rendered on.
$baseSurfaces = [
    'light' => [
        'background' => oklchToSrgb(1.0, 0.0, 0.0),
        'card' => oklchToSrgb(1.0, 0.0, 0.0),
        'muted' => oklchToSrgb(0.97, 0.0, 0.0),
        'secondary' => oklchToSrgb(0.97, 0.0, 0.0),
        'muted-foreground' => oklchToSrgb(0.542, 0.0, 0.0),
    ],
    'dark' => [
        'background' => oklchToSrgb(0.145, 0.0, 0.0),
        'card' => oklchToSrgb(0.205, 0.0, 0.0),
        'muted' => oklchToSrgb(0.269, 0.0, 0.0),
        'secondary' => oklchToSrgb(0.269, 0.0, 0.0),
        'muted-foreground' => oklchToSrgb(0.708, 0.0, 0.0),
    ],
];

foreach ($presets as [$id, $label, $hue, $lightAccent, $radius, $fontKey, $icon, $density, $ringWidth, $shadow]) {
    $tokens = accentTokens($hue, $lightAccent);

    // WCAG 2.2 verification: text on accent 4.5:1, muted text on its surfaces
    // 4.5:1, the accent 3:1 (UI floor) on every surface it sits on, and the
    // solved brand link/text tokens 4.5:1 on theirs.
    foreach ($baseSurfaces as $mode => $surfaces) {
        $background = $surfaces['background'];
        $primary = $parseOklch($tokens[$mode]['--primary']);
        $primaryFg = $parseOklch($tokens[$mode]['--primary-foreground']);
        $accent = $parseOklch($tokens[$mode]['--accent']);
        $accentFg = $parseOklch($tokens[$mode]['--accent-foreground']);
        $mutedFg = $surfaces['muted-foreground'];
        $resolve = static fn (string $value): array => $value === 'var(--primary)' ? $primary : $parseOklch($value);
        $primaryText = $resolve($tokens[$mode]['--d-primary-text']);
        $link = isset($tokens[$mode]['--d-link']) ? $resolve($tokens[$mode]['--d-link']) : $primary;
        $tint = $mode === 'dark'
            ? compositeSrgb($primary, 0.2, $surfaces['card'])
            : compositeSrgb($primary, 0.1, $surfaces['card']);
        foreach ([
            ['primary-foreground/primary', contrastRatio($primaryFg, $primary), 4.5],
            ['accent-foreground/accent', contrastRatio($accentFg, $accent), 4.5],
            ['muted-foreground/background', contrastRatio($mutedFg, $background), 4.5],
            ['muted-foreground/card', contrastRatio($mutedFg, $surfaces['card']), 4.5],
            ['muted-foreground/muted', contrastRatio($mutedFg, $surfaces['muted']), 4.5],
            ['primary/background', contrastRatio($primary, $background), 3.0],
            ['primary/muted', contrastRatio($primary, $surfaces['muted']), 3.0],
            ['primary/secondary', contrastRatio($primary, $surfaces['secondary']), 3.0],
            ['primary/accent', contrastRatio($primary, $accent), 3.0],
            ['d-primary-text/muted', contrastRatio($primaryText, $surfaces['muted']), 4.5],
            ['d-primary-text/secondary', contrastRatio($primaryText, $surfaces['secondary']), 4.5],
            ['d-primary-text/accent', contrastRatio($primaryText, $accent), 4.5],
            ['d-primary-text/primary-tint', contrastRatio($primaryText, $tint), 4.5],
            ['d-link/background', contrastRatio($link, $background), 4.5],
            ['d-link/card', contrastRatio($link, $surfaces['card']), 4.5],
        ] as [$pair, $ratio, $minimum]) {
            if ($ratio < $minimum) {
                $contrastFailures[] = sprintf('%s/%s %s = %.2f (< %.1f)', $id, $mode, $pair, $ratio, $minimum);
            }
        }
    }

    // Light block: accent + radius + fonts + density + focus-ring + elevation.
    $css .= "\nbody[data-shadcn-preset=\"{$id}\"] {\n";
    foreach ($tokens['light'] as $name => $value) {
        $css .= "  {$name}: {$value};\n";
    }
    $css .= "  --radius: {$radius}rem;\n";
    $css .= "  --d-font-sans: {$fonts[$fontKey]};\n";
    $css .= "  --d-font-heading: var(--d-font-sans);\n";
    $css .= "  --d-font-mono: {$mono};\n";
    foreach ($densityProfiles[$density] as $name => $value) {
        $css .= "  {$name}: {$value};\n";
    }
    $css .= "  --d-ring-width: {$ringWidth};\n";
    $css .= "  --d-surface-shadow: {$shadowTokens[$shadow]};\n";
    $css .= "}\n";

    // Dark block: accent overrides only (neutral dark base inherited from .dark).
    $css .= "\n.dark body[data-shadcn-preset=\"{$id}\"] {\n";
    foreach ($tokens['dark'] as $name => $value) {
        $css .= "  {$name}: {$value};\n";
    }
    $css .= "}\n";

    $enumLines[] = sprintf('      %s: \'%s\'', $id, $label);
    $mapLines[] = sprintf(
        "        '%s' => ['values' => ['style' => 'nova', 'iconLibrary' => '%s', 'baseColor' => 'neutral']],",
        $id,
        $icon
    );
    $iconLines[] = sprintf("            '%s' => '%s',", $id, $icon);
}

$css .= "\n/* === END Desiderio house presets === */\n";

if ($contrastFailures !== []) {
    fwrite(STDERR, "WCAG contrast verification failed:\n" . implode("\n", $contrastFailures) . "\n");
    exit(1);
}

// Idempotent insert/replace into shadcn-theme.css, before the [data-font] override section.
$theme = (string) file_get_contents($themePath);
$beginMarker = '/* === BEGIN Desiderio house presets';
$endMarker = "/* === END Desiderio house presets === */\n";

if (str_contains($theme, $beginMarker)) {
    $start = strpos($theme, $beginMarker);
    $end = strpos($theme, $endMarker) + strlen($endMarker);
    $theme = substr($theme, 0, $start) . $css . substr($theme, $end);
} else {
    $anchor = 'body[data-font="inter"] {';
    $pos = strpos($theme, $anchor);
    if ($pos === false) {
        fwrite(STDERR, "Could not find insertion anchor in shadcn-theme.css\n");
        exit(1);
    }
    $theme = substr($theme, 0, $pos) . $css . "\n\n" . substr($theme, $pos);
}

file_put_contents($themePath, $theme);

echo "Wrote " . count($presets) . " house presets into Resources/Public/Css/shadcn-theme.css\n\n";
echo "--- settings.definitions.yaml enum entries (desiderio.shadcn.preset) ---\n";
echo implode("\n", $enumLines) . "\n\n";
echo "--- sync script decodePreset() \$knownPresets entries ---\n";
echo implode("\n", $mapLines) . "\n\n";
echo "--- IconRegistry::libraryForPreset() match arms ---\n";
echo implode("\n", $iconLines) . "\n";
