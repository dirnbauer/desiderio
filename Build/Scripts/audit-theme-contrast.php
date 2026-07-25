<?php

declare(strict_types=1);

/**
 * WCAG contrast audit of every shadcn theme preset, in light and dark mode.
 *
 * Why this exists rather than a screenshot sweep: rendering 244 elements across
 * 15 presets and 2 modes is ~7 000 screenshots, and almost all of it would be
 * re-measuring the same handful of token pairs. Content elements are forbidden
 * from hardcoding colour (scripts/audit-content-elements.php keeps
 * `hardcoded_color` at zero), so surface/text contrast is a property of the
 * TOKEN FILE, not of the element. Ten of the fifteen presets only override the
 * accent family and inherit every surface from `:root` / `.dark`, which makes
 * the whole matrix provable here in under a second.
 *
 * What this does NOT cover, and what the browser sweep is still for: composed
 * colours (color-mix, gradients), text over photographs, and anything where the
 * effective background depends on stacking.
 *
 * Usage: php Build/Scripts/audit-theme-contrast.php [--json] [--verbose]
 * Exit code 1 if any required pair fails.
 */

const THEME_CSS = __DIR__ . '/../../Resources/Public/Css/shadcn-theme.css';

/**
 * Pairs that must hold in every preset and mode.
 *
 * `min` follows WCAG 2.2: 4.5 for body text, 3.0 for large text and for
 * non-text boundaries such as borders. `--border` against `--background` is
 * checked at 3.0 as a UI component boundary, not as text.
 */
const REQUIRED = 'required';
const ADVISORY = 'advisory';

const PAIRS = [
    ['foreground', 'background', 4.5, REQUIRED, 'body text on the page'],
    ['card-foreground', 'card', 4.5, REQUIRED, 'text on a card'],
    ['popover-foreground', 'popover', 4.5, REQUIRED, 'text in a popover'],
    ['muted-foreground', 'background', 4.5, REQUIRED, 'secondary text on the page'],
    ['muted-foreground', 'muted', 4.5, REQUIRED, 'secondary text on a muted surface'],
    ['primary-foreground', 'primary', 4.5, REQUIRED, 'label on a primary button'],
    ['secondary-foreground', 'secondary', 4.5, REQUIRED, 'label on a secondary button'],
    ['accent-foreground', 'accent', 4.5, REQUIRED, 'label on an accent surface'],
    ['destructive-foreground', 'destructive', 4.5, REQUIRED, 'label on a destructive button'],
    ['sidebar-foreground', 'sidebar', 4.5, REQUIRED, 'text in the sidebar'],
    // Advisory: --sidebar-primary{,-foreground} are carried for shadcn parity
    // but nothing in this package paints with them (grep finds no use outside
    // the token file itself). Gating on a dead token would only tempt someone
    // to "fix" a colour no one can see. Promote to REQUIRED the moment a
    // component adopts it.
    ['sidebar-primary-foreground', 'sidebar-primary', 4.5, ADVISORY, 'label on a sidebar primary surface'],
    ['d-primary-text', 'background', 4.5, REQUIRED, 'brand-coloured text on the page'],
    ['d-primary-text', 'muted', 4.5, REQUIRED, 'brand-coloured text on a muted surface'],
    // WCAG 1.4.11 / 2.4.11: the focus indicator must be distinguishable, so
    // this one is a genuine gate.
    ['ring', 'background', 3.0, REQUIRED, 'focus ring against the page'],
    // Advisory on purpose. `--border` is upstream shadcn's hairline
    // (oklch(0.922 0 0) on white = 1.2:1) and is decorative: no component is
    // identified by it alone, so 1.4.11 does not apply. Reported so a
    // deliberate darkening stays a deliberate decision, not gated so that
    // tracking upstream does not turn the suite red.
    ['border', 'background', 3.0, ADVISORY, 'border against the page'],
];

$options = array_slice($argv, 1);
$asJson = in_array('--json', $options, true);
$verbose = in_array('--verbose', $options, true);

$css = @file_get_contents(THEME_CSS);
if ($css === false) {
    fwrite(STDERR, "Cannot read " . THEME_CSS . "\n");
    exit(2);
}

/**
 * @return array<string, string> variable name (without --) => raw value
 */
function parseBlock(string $css, string $selector): array
{
    // Selectors contain [] and . so they must be quoted, and the block body is
    // flat (no nested rules), so a non-greedy match to the first } is exact.
    // Anchored to a line start (/m) so that `.dark { … }` cannot be matched
    // inside `.dark body[data-shadcn-preset="…"] { … }`.
    $pattern = '/^' . preg_quote($selector, '/') . '\s*\{(.*?)^\}/ms';
    if (preg_match($pattern, $css, $match) !== 1) {
        return [];
    }

    $declarations = [];
    if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $match[1], $found, PREG_SET_ORDER) > 0) {
        foreach ($found as $declaration) {
            $declarations[$declaration[1]] = trim($declaration[2]);
        }
    }

    return $declarations;
}

/** @return list<string> every preset id that has its own light block */
function presetIds(string $css): array
{
    preg_match_all('/body\[data-shadcn-preset="([^"]+)"\]\s*\{/', $css, $found);
    $ids = array_values(array_unique($found[1] ?? []));
    sort($ids);

    return $ids;
}

/** Resolve `var(--x)` chains, `color-mix()` and literals to an oklch() triple. */
function resolve(string $name, array $scope, int $depth = 0): ?array
{
    if ($depth > 8 || !isset($scope[$name])) {
        return null;
    }

    return resolveValue($scope[$name], $scope, $depth);
}

function resolveValue(string $value, array $scope, int $depth = 0): ?array
{
    $value = trim($value);
    if ($depth > 8) {
        return null;
    }

    if (preg_match('/^var\(\s*--([a-z0-9-]+)/i', $value, $match) === 1) {
        return resolve($match[1], $scope, $depth + 1);
    }
    if (str_starts_with(strtolower($value), 'color-mix(')) {
        return resolveColorMix($value, $scope, $depth);
    }
    // `none` is a valid component keyword and means "missing", which behaves as
    // 0 for our purposes (it only ever appears as the hue of pure black/white).
    if (preg_match('/oklch\(\s*([0-9.]+%?)\s+([0-9.]+%?)\s+([0-9.]+|none)/i', $value, $match) === 1) {
        return [
            str_ends_with($match[1], '%') ? (float)rtrim($match[1], '%') / 100 : (float)$match[1],
            str_ends_with($match[2], '%') ? (float)rtrim($match[2], '%') / 100 : (float)$match[2],
            strcasecmp($match[3], 'none') === 0 ? 0.0 : (float)$match[3],
        ];
    }

    return null;
}

/**
 * `color-mix(in oklch, A, B P%)` — linear interpolation of the two colours in
 * OKLCH with B weighted P. Only the oklch colour space and the two-colour form
 * are supported, which is all shadcn-theme.css uses; anything else returns null
 * and shows up as a reported skip rather than a wrong number.
 */
function resolveColorMix(string $value, array $scope, int $depth): ?array
{
    if (preg_match('/^color-mix\(\s*in\s+oklch\s*,\s*(.+)\)\s*$/is', $value, $match) !== 1) {
        return null;
    }

    // Split on top-level commas only: both operands may themselves be
    // `var(--x)` or `oklch(...)` calls containing no commas, but being
    // depth-aware keeps this correct if that ever changes.
    $parts = [];
    $buffer = '';
    $nesting = 0;
    foreach (str_split($match[1]) as $character) {
        if ($character === '(') {
            $nesting++;
        } elseif ($character === ')') {
            $nesting--;
        }
        if ($character === ',' && $nesting === 0) {
            $parts[] = $buffer;
            $buffer = '';
            continue;
        }
        $buffer .= $character;
    }
    $parts[] = $buffer;

    if (count($parts) !== 2) {
        return null;
    }

    $weights = [];
    $colors = [];
    foreach ($parts as $part) {
        $part = trim($part);
        $weight = null;
        if (preg_match('/\s([0-9.]+)%$/', $part, $percentage) === 1) {
            $weight = (float)$percentage[1] / 100;
            $part = trim(substr($part, 0, -strlen($percentage[0])));
        }
        $color = resolveValue($part, $scope, $depth + 1);
        if ($color === null) {
            return null;
        }
        $colors[] = $color;
        $weights[] = $weight;
    }

    // An omitted percentage takes the remainder; both omitted means 50/50.
    if ($weights[0] === null && $weights[1] === null) {
        $weights = [0.5, 0.5];
    } elseif ($weights[0] === null) {
        $weights[0] = 1 - $weights[1];
    } elseif ($weights[1] === null) {
        $weights[1] = 1 - $weights[0];
    }
    $total = $weights[0] + $weights[1];
    if ($total <= 0) {
        return null;
    }

    return [
        ($colors[0][0] * $weights[0] + $colors[1][0] * $weights[1]) / $total,
        ($colors[0][1] * $weights[0] + $colors[1][1] * $weights[1]) / $total,
        // Hue is interpolated linearly rather than around the shorter arc.
        // Every mix in this file pairs a hue with an achromatic black or white
        // whose hue is `none`, so the shorter-arc distinction never bites.
        ($colors[0][2] * $weights[0] + $colors[1][2] * $weights[1]) / $total,
    ];
}

/** OKLCH -> linear sRGB -> WCAG relative luminance. */
function relativeLuminance(array $oklch): float
{
    [$lightness, $chroma, $hueDegrees] = $oklch;
    $hue = deg2rad($hueDegrees);
    $a = $chroma * cos($hue);
    $b = $chroma * sin($hue);

    $l = ($lightness + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
    $m = ($lightness - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
    $s = ($lightness - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

    // Linear sRGB. WCAG's luminance coefficients apply to exactly these
    // linear values, so no gamma round-trip is needed.
    $r = 4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s;
    $g = -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s;
    $bl = -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s;

    $clamp = static fn (float $channel): float => max(0.0, min(1.0, $channel));

    return 0.2126 * $clamp($r) + 0.7152 * $clamp($g) + 0.0722 * $clamp($bl);
}

function contrastRatio(array $a, array $b): float
{
    $la = relativeLuminance($a);
    $lb = relativeLuminance($b);
    $lighter = max($la, $lb);
    $darker = min($la, $lb);

    return ($lighter + 0.05) / ($darker + 0.05);
}

$rootLight = parseBlock($css, ':root');
$rootDark = parseBlock($css, '.dark');
// Desiderio's own token layer (--d-*) lives in a plain `body` block after the
// shadcn variables, so it has to be folded into the base scope or every
// --d-primary-text pair resolves to nothing and is silently skipped.
$baseBody = parseBlock($css, 'body');
$darkBody = parseBlock($css, '.dark body');
$presets = presetIds($css);

if ($rootLight === [] || $rootDark === []) {
    fwrite(STDERR, "Could not parse the :root / .dark token blocks — has the file structure changed?\n");
    exit(2);
}

$failures = [];
$advisories = [];
$checked = 0;
$skipped = [];

foreach ($presets as $preset) {
    foreach (['light', 'dark'] as $mode) {
        // Later declarations win, exactly as the cascade resolves them: the
        // base layer, then the mode layer, then the preset's own overrides.
        $scope = $mode === 'light'
            ? array_merge($rootLight, $baseBody, parseBlock($css, sprintf('body[data-shadcn-preset="%s"]', $preset)))
            : array_merge(
                $rootLight,
                $rootDark,
                $baseBody,
                $darkBody,
                parseBlock($css, sprintf('body[data-shadcn-preset="%s"]', $preset)),
                parseBlock($css, sprintf('.dark body[data-shadcn-preset="%s"]', $preset)),
            );

        foreach (PAIRS as [$foreground, $background, $minimum, $severity, $description]) {
            $fg = resolve($foreground, $scope);
            $bg = resolve($background, $scope);
            if ($fg === null || $bg === null) {
                $skipped[] = sprintf('%s/%s: --%s on --%s', $preset, $mode, $foreground, $background);
                continue;
            }

            $checked++;
            $ratio = contrastRatio($fg, $bg);
            if ($ratio + 0.005 < $minimum) {
                $entry = [
                    'preset' => $preset,
                    'mode' => $mode,
                    'pair' => sprintf('--%s on --%s', $foreground, $background),
                    'what' => $description,
                    'ratio' => round($ratio, 2),
                    'required' => $minimum,
                ];
                if ($severity === REQUIRED) {
                    $failures[] = $entry;
                } else {
                    $advisories[] = $entry;
                }
            } elseif ($verbose) {
                printf("  ok  %-12s %-5s %-46s %5.2f:1\n", $preset, $mode, sprintf('--%s on --%s', $foreground, $background), $ratio);
            }
        }
    }
}

if ($asJson) {
    echo json_encode([
        'presets' => count($presets),
        'checked' => $checked,
        'skipped' => $skipped,
        'failures' => $failures,
        'advisories' => $advisories,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    exit($failures === [] ? 0 : 1);
}

printf("Theme contrast: %d presets x 2 modes, %d pairs checked\n", count($presets), $checked);

if ($skipped !== []) {
    printf("\n%d pairs skipped (token not defined in that scope):\n", count($skipped));
    foreach (array_slice($skipped, 0, 10) as $entry) {
        echo "  - {$entry}\n";
    }
    if (count($skipped) > 10) {
        printf("  ... and %d more\n", count($skipped) - 10);
    }
}

if ($advisories !== []) {
    printf("\n%d advisory (reported, not gated):\n", count($advisories));
    $grouped = [];
    foreach ($advisories as $advisory) {
        $grouped[$advisory['pair']][] = sprintf('%s/%s %.2f:1', $advisory['preset'], $advisory['mode'], $advisory['ratio']);
    }
    foreach ($grouped as $pair => $where) {
        printf("  %-46s %d scopes, e.g. %s\n", $pair, count($where), $where[0]);
    }
}

if ($failures === []) {
    echo "\nAll required pairs pass WCAG 2.2 AA.\n";
    exit(0);
}

printf("\n%d FAILURES:\n\n", count($failures));
foreach ($failures as $failure) {
    printf(
        "  %-12s %-5s  %-46s %5.2f:1  (need %.1f)  %s\n",
        $failure['preset'],
        $failure['mode'],
        $failure['pair'],
        $failure['ratio'],
        $failure['required'],
        $failure['what'],
    );
}
exit(1);
