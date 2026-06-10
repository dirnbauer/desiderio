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
    $lightBg = oklchToSrgb(1.0, 0.0, 0.0);

    if ($lightAccent) {
        // Bright accents (amber/lime): dark text on the accent. The light-mode
        // accent must also hold 3:1 against the white page background.
        $lpL = solveMaxLightness(0.4, 0.9, 0.16, $hue, $lightBg, $uiTarget);
        $lp = "oklch({$lpL} 0.16 {$h})";
        $lpFgL = solveMaxLightness(0.1, 0.45, 0.05, $hue, oklchToSrgb($lpL, 0.16, (float)$hue), $textTarget);
        $lpFg = "oklch({$lpFgL} 0.05 {$h})";
        $dp = "oklch(0.84 0.17 {$h})";
        $dpFgL = solveMaxLightness(0.1, 0.45, 0.05, $hue, oklchToSrgb(0.84, 0.17, (float)$hue), $textTarget);
        $dpFg = "oklch({$dpFgL} 0.05 {$h})";
    } else {
        // Saturated accents: white text in light mode (solve the accent), a
        // brighter accent with dark text in dark mode (shadcn convention).
        $lpL = solveMaxLightness(0.3, 0.65, 0.2, $hue, $whiteFg, $textTarget);
        $lp = "oklch({$lpL} 0.2 {$h})";
        $lpFg = 'oklch(0.985 0 0)';
        $dp = "oklch(0.66 0.18 {$h})";
        $dpFgL = solveMaxLightness(0.1, 0.4, 0.035, $hue, oklchToSrgb(0.66, 0.18, (float)$hue), $textTarget);
        $dpFg = "oklch({$dpFgL} 0.035 {$h})";
    }

    return [
        'light' => [
            '--primary' => $lp,
            '--primary-foreground' => $lpFg,
            '--accent' => "oklch(0.95 0.03 {$h})",
            '--accent-foreground' => "oklch(0.32 0.07 {$h})",
            '--ring' => $lp,
            '--sidebar-primary' => $lp,
            '--sidebar-primary-foreground' => $lpFg,
            '--sidebar-ring' => $lp,
            '--chart-1' => $lp,
        ],
        'dark' => [
            '--primary' => $dp,
            '--primary-foreground' => $dpFg,
            '--accent' => "oklch(0.33 0.045 {$h})",
            '--accent-foreground' => "oklch(0.96 0.02 {$h})",
            '--ring' => $dp,
            '--sidebar-primary' => $dp,
            '--sidebar-primary-foreground' => $dpFg,
            '--sidebar-ring' => $dp,
            '--chart-1' => $dp,
        ],
    ];
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

foreach ($presets as [$id, $label, $hue, $lightAccent, $radius, $fontKey, $icon, $density, $ringWidth, $shadow]) {
    $tokens = accentTokens($hue, $lightAccent);

    // WCAG 2.2 verification: text on accent 4.5:1, accent on page background 3:1.
    foreach (['light' => oklchToSrgb(1.0, 0.0, 0.0), 'dark' => oklchToSrgb(0.145, 0.0, 0.0)] as $mode => $background) {
        $primary = $parseOklch($tokens[$mode]['--primary']);
        $primaryFg = $parseOklch($tokens[$mode]['--primary-foreground']);
        $accent = $parseOklch($tokens[$mode]['--accent']);
        $accentFg = $parseOklch($tokens[$mode]['--accent-foreground']);
        foreach ([
            ['primary-foreground/primary', contrastRatio($primaryFg, $primary), 4.5],
            ['accent-foreground/accent', contrastRatio($accentFg, $accent), 4.5],
            ['primary/background', contrastRatio($primary, $background), 3.0],
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
