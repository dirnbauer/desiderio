<?php

declare(strict_types=1);

/**
 * WCAG 2.2 AA contrast audit of every shadcn theme preset, in light and dark mode.
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
 * Three kinds of check:
 *
 *  - PAIRS: a token painted on a token (text 4.5:1, large text and non-text
 *    3:1). A translucent token (dark mode's `--input: oklch(1 0 0 / 15%)`) is
 *    composited onto the surface first, exactly as the browser paints it.
 *  - FRAMES: the copy of a painted section (Appearance > Frame) is the band's
 *    foreground at 86-88% over the band, from 07-content-frames.css.
 *  - PAGE: colours the base layer composes (22-modern-base.css) — the glass
 *    header over scrolling content and the chart-hued horizon behind the
 *    opening section — with their text on top.
 *
 * What this does NOT cover, and what the browser sweep is for (run.mjs
 * --tier contrast): colours composed inside elements, text over photographs,
 * and anything whose background depends on stacking.
 *
 * Usage: php Build/Scripts/audit-theme-contrast.php [--json] [--verbose]
 * Exit code 1 if any required check fails.
 */
const THEME_CSS = __DIR__ . '/../../Resources/Public/Css/shadcn-theme.css';
const FRAMES_CSS = __DIR__ . '/../../Resources/Private/Css/desiderio/07-content-frames.css';
const BASE_CSS = __DIR__ . '/../../Resources/Private/Css/desiderio/22-modern-base.css';

/**
 * `min` follows WCAG 2.2: 4.5 for body text (1.4.3), 3.0 for large text and
 * for non-text contrast (1.4.11: the boundary of a form field, a focus
 * indicator, the fill of a checked control).
 */
const REQUIRED = 'required';
const ADVISORY = 'advisory';

const PAIRS = [
    ['foreground', 'background', 4.5, REQUIRED, 'body text on the page'],
    ['foreground', 'muted', 4.5, REQUIRED, 'body text on a muted surface'],
    ['card-foreground', 'card', 4.5, REQUIRED, 'text on a card'],
    ['popover-foreground', 'popover', 4.5, REQUIRED, 'text in a popover'],
    ['muted-foreground', 'background', 4.5, REQUIRED, 'secondary text on the page'],
    ['muted-foreground', 'muted', 4.5, REQUIRED, 'secondary text on a muted surface'],
    ['muted-foreground', 'card', 4.5, REQUIRED, 'secondary text on a card'],
    ['muted-foreground', 'popover', 4.5, REQUIRED, 'secondary text in a popover'],
    ['primary-foreground', 'primary', 4.5, REQUIRED, 'label on a primary button'],
    ['secondary-foreground', 'secondary', 4.5, REQUIRED, 'label on a secondary button'],
    ['accent-foreground', 'accent', 4.5, REQUIRED, 'label on an accent surface'],
    ['destructive-foreground', 'destructive', 4.5, REQUIRED, 'label on a destructive button'],
    ['destructive', 'background', 4.5, REQUIRED, 'error text on the page'],
    ['destructive', 'card', 4.5, REQUIRED, 'error text on a card'],
    ['destructive', 'muted', 4.5, REQUIRED, 'error text on a muted surface'],
    ['sidebar-foreground', 'sidebar', 4.5, REQUIRED, 'text in the sidebar'],
    // Advisory: --sidebar-primary{,-foreground} are carried for shadcn parity
    // but nothing in this package paints with them (grep finds no use outside
    // the token file itself). Gating on a dead token would only tempt someone
    // to "fix" a colour no one can see. Promote to REQUIRED the moment a
    // component adopts it.
    ['sidebar-primary-foreground', 'sidebar-primary', 4.5, ADVISORY, 'label on a sidebar primary surface'],
    ['d-primary-text', 'background', 4.5, REQUIRED, 'brand-coloured text on the page'],
    ['d-primary-text', 'muted', 4.5, REQUIRED, 'brand-coloured text on a muted surface'],
    // --d-link is the brand ink for links and the section eyebrows the base
    // layer sets in the preset's mono face at 12px.
    ['d-link', 'background', 4.5, REQUIRED, 'links and eyebrows on the page'],
    ['d-link', 'card', 4.5, REQUIRED, 'links on a card'],
    ['d-link-on-muted', 'muted', 4.5, REQUIRED, 'links on a muted surface'],
    // Status text (alerts, badges, stat trends) on the page, on a card and on
    // the status colour's own tint.
    ['d-info-text', 'background', 4.5, REQUIRED, 'info text on the page'],
    ['d-info-text', 'd-info-muted', 4.5, REQUIRED, 'info text on its tint'],
    ['d-success-text', 'background', 4.5, REQUIRED, 'success text on the page'],
    ['d-success-text', 'd-success-muted', 4.5, REQUIRED, 'success text on its tint'],
    ['d-warning-text', 'background', 4.5, REQUIRED, 'warning text on the page'],
    ['d-warning-text', 'd-warning-muted', 4.5, REQUIRED, 'warning text on its tint'],
    ['d-danger-text', 'background', 4.5, REQUIRED, 'danger text on the page'],
    ['d-danger-text', 'd-danger-muted', 4.5, REQUIRED, 'danger text on its tint'],
    // WCAG 1.4.11 and 2.4.7: the focus indicator must be distinguishable, so
    // this one is a genuine gate — on the page, a card and a muted surface.
    ['ring', 'background', 3.0, REQUIRED, 'focus ring against the page'],
    ['ring', 'card', 3.0, REQUIRED, 'focus ring against a card'],
    ['ring', 'muted', 3.0, REQUIRED, 'focus ring against a muted surface'],
    // WCAG 1.4.11: a text field, select, textarea, checkbox and radio are
    // identified by their `border-input` boundary alone (their fill is
    // transparent), so that boundary needs 3:1 against every surface a form
    // sits on.
    ['input', 'background', 3.0, REQUIRED, 'form field boundary on the page'],
    ['input', 'card', 3.0, REQUIRED, 'form field boundary on a card'],
    ['input', 'muted', 3.0, REQUIRED, 'form field boundary on a muted surface'],
    ['input', 'popover', 3.0, REQUIRED, 'form field boundary in a popover'],
    // Advisory: shadcn's focus style turns a field's edge to --ring. When the
    // two are the same colour only the faint halo is left to show focus.
    ['ring', 'input', 1.5, ADVISORY, "a focused field's edge changes visibly"],
    // WCAG 1.4.11: the primary fill marks a checked checkbox, an active switch
    // and a progress value, and --primary also sets large numerals.
    ['primary', 'background', 3.0, REQUIRED, 'primary fill (checked state, large numerals) on the page'],
    ['primary', 'card', 3.0, REQUIRED, 'primary fill (checked state) on a card'],
    // Advisory on purpose. `--border` is upstream shadcn's hairline
    // (oklch(0.922 0 0) on white = 1.2:1) and is decorative: no component is
    // identified by it alone (form fields use --input), so 1.4.11 does not
    // apply. Reported so a deliberate darkening stays a deliberate decision.
    ['border', 'background', 3.0, ADVISORY, 'border against the page'],
    // Advisory: a chart series is a graphical object (1.4.11), but every
    // desiderio chart also states its values in text (labels, a summary and a
    // screen-reader table), so no series is required to understand the chart.
    ['chart-1', 'background', 3.0, ADVISORY, 'chart series 1 against the page'],
    ['chart-2', 'background', 3.0, ADVISORY, 'chart series 2 against the page'],
    ['chart-3', 'background', 3.0, ADVISORY, 'chart series 3 against the page'],
    ['chart-4', 'background', 3.0, ADVISORY, 'chart series 4 against the page'],
    ['chart-5', 'background', 3.0, ADVISORY, 'chart series 5 against the page'],
];

/**
 * Text on a translucent tint of a token over the page: the soft destructive
 * button and badge (`bg-destructive/10`, `dark:bg-destructive/20`) at rest
 * and on hover (`hover:bg-destructive/20`, `dark:hover:bg-destructive/30`).
 *
 * [ink token, ink alpha, tint token, tint alpha light, tint alpha dark, what]
 */
const TINTS = [
    ['d-danger-text', 1.0, 'destructive', 0.10, 0.20, 'destructive button and badge label on its tint'],
    ['d-danger-text', 1.0, 'destructive', 0.20, 0.30, 'destructive button label on its hover tint'],
    // --d-primary-text exists for brand text on brand tints: a search
    // highlight on bg-primary/10, a year or step badge on an 8-10% tint.
    ['d-primary-text', 1.0, 'primary', 0.10, 0.20, 'brand text on a primary tint'],
];

/**
 * Painted sections (Section component `frame` -> 07-content-frames.css): the
 * band sets its foreground, headings take it at 100%, running copy takes the
 * colour 07-content-frames.css gives `p, li, figcaption` in that band, and a
 * form field or focus ring inside the band is measured against the band with
 * whatever --input / --ring the band's own rule declares.
 *
 * [band token, text token]
 */
const FRAMES = [
    ['primary', 'primary-foreground'],
    ['secondary', 'secondary-foreground'],
    ['accent', 'accent-foreground'],
];

$options = array_slice($argv, 1);
$asJson = in_array('--json', $options, true);
$verbose = in_array('--verbose', $options, true);

$css = @file_get_contents(THEME_CSS);
$framesCss = @file_get_contents(FRAMES_CSS);
$baseCss = @file_get_contents(BASE_CSS);
if ($css === false || $framesCss === false || $baseCss === false) {
    fwrite(STDERR, "Cannot read the theme, frames or base stylesheet.\n");
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
    // Every top-level block with this selector counts, in source order, as
    // the cascade reads them (the rhythm tokens sit in a `body` block of their
    // own before the main one). Blocks nested in @media are indented and not
    // matched: they are responsive values, not colours.
    $pattern = '/^' . preg_quote($selector, '/') . '\s*\{(.*?)^\}/ms';
    if (preg_match_all($pattern, $css, $matches) < 1) {
        return [];
    }

    $declarations = [];
    foreach ($matches[1] as $body) {
        if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $body, $found, PREG_SET_ORDER) > 0) {
            foreach ($found as $declaration) {
                $declarations[$declaration[1]] = trim($declaration[2]);
            }
        }
    }

    return $declarations;
}

/**
 * The declarations of every top-level rule whose selector is exactly
 * $selector, in source order (a later one wins). Custom properties are keyed
 * without their leading dashes, like parseBlock(); `color` and
 * `background-color` are kept too, because 07-content-frames.css and
 * 22-modern-base.css paint text and glass with them.
 *
 * @return array<string, string>
 */
function ruleDeclarations(string $css, string $selector): array
{
    $pattern = '/^' . preg_quote($selector, '/') . '\s*\{([^{}]*)\}/m';
    if (preg_match_all($pattern, $css, $matches) < 1) {
        return [];
    }

    $declarations = [];
    foreach ($matches[1] as $body) {
        $body = preg_replace('#/\*.*?\*/#s', '', $body) ?? $body;
        if (preg_match_all('/(?<![\w-])(--[a-z0-9-]+|color|background-color)\s*:\s*([^;]+);/i', $body, $found, PREG_SET_ORDER) > 0) {
            foreach ($found as $declaration) {
                $name = str_starts_with($declaration[1], '--') ? substr($declaration[1], 2) : strtolower($declaration[1]);
                $declarations[$name] = trim($declaration[2]);
            }
        }
    }

    return $declarations;
}

/**
 * A resolved colour written back as CSS, so it can seed a scope in which a
 * token is redefined in terms of its own earlier value.
 *
 * @param array{0: float, 1: float, 2: float, 3: float} $color
 */
function literal(array $color): string
{
    return sprintf('oklch(%.6F %.6F %.6F / %.6F)', $color[0], $color[1], $color[2], $color[3]);
}

/** @return list<string> every preset id that has its own light block */
function presetIds(string $css): array
{
    preg_match_all('/body\[data-shadcn-preset="([^"]+)"\]\s*\{/', $css, $found);
    $ids = array_values(array_unique($found[1] ?? []));
    sort($ids);

    return $ids;
}

/**
 * A colour is [lightness, chroma, hue, alpha] in OKLCH.
 *
 * @return array{0: float, 1: float, 2: float, 3: float}|null
 */
function resolve(string $name, array $scope, int $depth = 0): ?array
{
    if ($depth > 8 || !isset($scope[$name])) {
        return null;
    }

    return resolveValue($scope[$name], $scope, $depth);
}

/** @return array{0: float, 1: float, 2: float, 3: float}|null */
function resolveValue(string $value, array $scope, int $depth = 0): ?array
{
    $value = trim($value);
    if ($depth > 8) {
        return null;
    }

    if (strcasecmp($value, 'transparent') === 0) {
        return [0.0, 0.0, 0.0, 0.0];
    }
    if (preg_match('/^var\(\s*--([a-z0-9-]+)/i', $value, $match) === 1) {
        return resolve($match[1], $scope, $depth + 1);
    }
    if (str_starts_with(strtolower($value), 'color-mix(')) {
        return resolveColorMix($value, $scope, $depth);
    }
    // `none` is a valid component keyword and means "missing", which behaves as
    // 0 for our purposes (it only ever appears as the hue of pure black/white).
    if (preg_match('/oklch\(\s*([0-9.]+%?)\s+([0-9.]+%?)\s+([0-9.]+|none)\s*(?:\/\s*([0-9.]+%?))?\s*\)/i', $value, $match) === 1) {
        $component = static fn(string $raw): float => str_ends_with($raw, '%') ? (float)rtrim($raw, '%') / 100 : (float)$raw;

        return [
            $component($match[1]),
            $component($match[2]),
            strcasecmp($match[3], 'none') === 0 ? 0.0 : (float)$match[3],
            isset($match[4]) && $match[4] !== '' ? $component($match[4]) : 1.0,
        ];
    }

    return null;
}

/**
 * `color-mix(in oklch, A, B P%)` — interpolation of the two colours in OKLCH
 * with B weighted P. A `transparent` operand keeps the other colour and scales
 * its alpha (premultiplied interpolation), which is how the stylesheets fade a
 * foreground: `color-mix(in oklch, var(--x) 88%, transparent)` is --x at 88%.
 * Only the oklch space and the two-colour form are supported, which is all
 * shadcn-theme.css uses; anything else returns null and shows up as a skip.
 *
 * @return array{0: float, 1: float, 2: float, 3: float}|null
 */
function resolveColorMix(string $value, array $scope, int $depth): ?array
{
    if (preg_match('/^color-mix\(\s*in\s+oklch\s*,\s*(.+)\)\s*$/is', $value, $match) !== 1) {
        return null;
    }

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
    $w0 = $weights[0] / $total;
    $w1 = $weights[1] / $total;

    // Premultiplied interpolation: a fully transparent operand contributes no
    // colour, only its (zero) alpha.
    $alpha = $colors[0][3] * $w0 + $colors[1][3] * $w1;
    if ($alpha <= 0) {
        return [0.0, 0.0, 0.0, 0.0];
    }
    $mix = static function (int $channel) use ($colors, $w0, $w1, $alpha): float {
        return ($colors[0][$channel] * $colors[0][3] * $w0 + $colors[1][$channel] * $colors[1][3] * $w1) / $alpha;
    };
    // Hue: a transparent or achromatic operand has no hue to pull towards.
    $hue = $colors[0][3] * $w0 * ($colors[0][1] > 0 ? 1 : 0) + $colors[1][3] * $w1 * ($colors[1][1] > 0 ? 1 : 0) > 0
        ? (($colors[0][1] > 0 ? $colors[0][2] * $colors[0][3] * $w0 : 0) + ($colors[1][1] > 0 ? $colors[1][2] * $colors[1][3] * $w1 : 0))
            / (($colors[0][1] > 0 ? $colors[0][3] * $w0 : 0) + ($colors[1][1] > 0 ? $colors[1][3] * $w1 : 0))
        : 0.0;

    return [$mix(0), $mix(1), $hue, min(1.0, $alpha)];
}

/**
 * OKLCH -> gamma-encoded sRGB, each channel clamped to [0, 1].
 *
 * @param array{0: float, 1: float, 2: float, 3?: float} $oklch
 * @return array{0: float, 1: float, 2: float}
 */
function toSrgb(array $oklch): array
{
    [$lightness, $chroma, $hueDegrees] = $oklch;
    $hue = deg2rad($hueDegrees);
    $a = $chroma * cos($hue);
    $b = $chroma * sin($hue);

    $l = ($lightness + 0.3963377774 * $a + 0.2158037573 * $b) ** 3;
    $m = ($lightness - 0.1055613458 * $a - 0.0638541728 * $b) ** 3;
    $s = ($lightness - 0.0894841775 * $a - 1.2914855480 * $b) ** 3;

    $linear = [
        4.0767416621 * $l - 3.3077115913 * $m + 0.2309699292 * $s,
        -1.2684380046 * $l + 2.6097574011 * $m - 0.3413193965 * $s,
        -0.0041960863 * $l - 0.7034186147 * $m + 1.7076147010 * $s,
    ];

    return array_map(static function (float $channel): float {
        $channel = max(0.0, min(1.0, $channel));

        return $channel <= 0.0031308 ? 12.92 * $channel : 1.055 * ($channel ** (1 / 2.4)) - 0.055;
    }, $linear);
}

/**
 * Paints a (possibly translucent) colour over an opaque sRGB backdrop — plain
 * source-over in gamma-encoded sRGB, as browsers composite.
 *
 * @param array{0: float, 1: float, 2: float, 3: float} $top
 * @param array{0: float, 1: float, 2: float} $backdrop
 * @return array{0: float, 1: float, 2: float}
 */
function paint(array $top, array $backdrop): array
{
    $rgb = toSrgb($top);
    $alpha = max(0.0, min(1.0, $top[3]));

    return [
        $rgb[0] * $alpha + $backdrop[0] * (1 - $alpha),
        $rgb[1] * $alpha + $backdrop[1] * (1 - $alpha),
        $rgb[2] * $alpha + $backdrop[2] * (1 - $alpha),
    ];
}

/** @param array{0: float, 1: float, 2: float} $rgb */
function luminance(array $rgb): float
{
    $linear = array_map(
        static fn(float $channel): float => $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4,
        $rgb,
    );

    return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
}

/**
 * @param array{0: float, 1: float, 2: float} $a
 * @param array{0: float, 1: float, 2: float} $b
 */
function contrastRatio(array $a, array $b): float
{
    $la = luminance($a);
    $lb = luminance($b);

    return (max($la, $lb) + 0.05) / (min($la, $lb) + 0.05);
}

$rootLight = parseBlock($css, ':root');
$rootDark = parseBlock($css, '.dark');
// Desiderio's own token layer (--d-*) lives in a plain `body` block after the
// shadcn variables, so it has to be folded into the base scope or every
// --d-primary-text pair resolves to nothing and is silently skipped.
$baseBody = parseBlock($css, 'body');
$darkBody = parseBlock($css, '.dark body');
// b0 is the default preset: it ships no block of its own and IS the :root
// scope, so it is audited explicitly rather than by way of a token twin.
$presets = ['b0', ...array_values(array_diff(presetIds($css), ['b0']))];

if ($rootLight === [] || $rootDark === []) {
    fwrite(STDERR, "Could not parse the :root / .dark token blocks — has the file structure changed?\n");
    exit(2);
}

// The page composition is read from 22-modern-base.css rather than restated
// here, so a change to the glass, the nav ink or the horizon is measured the
// moment it is made.
$glassValue = ruleDeclarations($baseCss, '.desiderio-header')['background-color'] ?? null;
$navInkValue = ruleDeclarations($baseCss, '.desiderio-header__nav-link')['color'] ?? 'var(--muted-foreground)';
preg_match_all(
    '/radial-gradient\([^,]+,\s*oklch\(from var\(--(chart-\d)[^)]*\)\)\s*l c h\s*\/\s*clamp\(0,\s*c\s*\*\s*([0-9.]+),\s*([0-9.]+)\)\)/',
    $baseCss,
    $glows,
    PREG_SET_ORDER,
);
// The opening section (and breadcrumb, page title) pull their lead and
// eyebrow inks toward --foreground; without such a rule they use the plain
// tokens.
$openingMutedValue = preg_match('/--muted-foreground:\s*(color-mix\(in oklch,\s*var\(--d-muted-ink\)[^;]+);/', $baseCss, $match) === 1 ? $match[1] : 'var(--d-muted-ink)';
$openingLinkValue = preg_match('/--d-link:\s*(color-mix\(in oklch,\s*var\(--d-link-ink\)[^;]+);/', $baseCss, $match) === 1 ? $match[1] : 'var(--d-link-ink)';
// Panels (evidence and process sections, and every "muted" frame): the
// section repaints --background with --d-panel, a mix of --muted and the page
// captured on <body> as --d-page. One declaration per scheme; the dark one
// sits in a rule whose selector starts with `.dark`.
$panelValues = [];
foreach (preg_split('/\}/', preg_replace('#/\*.*?\*/#s', '', $baseCss) ?? $baseCss) ?: [] as $rule) {
    if (preg_match('/--d-panel:\s*([^;]+);/', $rule, $match) === 1) {
        $selector = trim(substr($rule, 0, (int)strpos($rule, '{')));
        $panelValues[str_starts_with($selector, '.dark') ? 'dark' : 'light'] = trim($match[1]);
    }
}
$baseLayerBody = ruleDeclarations($baseCss, 'body');

// Colour tokens redeclared by a stylesheet the audit reads are measured
// above; anywhere else a redeclaration would change what ships without being
// measured (a Powermail-only --input at 2.35:1 hid here once). Listed per
// file: the tokens that file may redeclare because this audit models them.
const AUDITED_REDECLARATIONS = [
    '07-content-frames.css' => '*',
    '22-modern-base.css' => ['muted-foreground', 'd-muted-foreground', 'd-link', 'background'],
];
$redeclared = [];
$sheets = array_merge(
    glob(__DIR__ . '/../../Resources/Private/Css/desiderio/*.css') ?: [],
    glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/assets/*.css') ?: [],
);
$colourTokens = 'background|foreground|card|card-foreground|popover|popover-foreground|primary|primary-foreground|secondary|secondary-foreground|muted|muted-foreground|accent|accent-foreground|destructive|destructive-foreground|border|input|ring|d-link|d-link-on-muted|d-muted-foreground|d-primary-text|d-danger-text|d-info-text|d-success-text|d-warning-text|d-input-subtle';
foreach ($sheets as $sheet) {
    $allowed = AUDITED_REDECLARATIONS[basename($sheet)] ?? [];
    if ($allowed === '*') {
        continue;
    }
    $source = preg_replace('#/\*.*?\*/#s', '', (string)file_get_contents($sheet)) ?? '';
    if (preg_match_all('/(?<![\w-])--(' . $colourTokens . ')\s*:/', $source, $found) > 0) {
        foreach (array_unique($found[1]) as $token) {
            if (!in_array($token, $allowed, true)) {
                $redeclared[] = sprintf('%s redeclares --%s', substr($sheet, strlen(__DIR__ . '/../../')), $token);
            }
        }
    }
}

if ($glassValue === null || $glows === []) {
    fwrite(STDERR, "Could not find the glass header or the horizon in 22-modern-base.css — has the file structure changed?\n");
    exit(2);
}

$failures = [];
$advisories = [];
$checked = 0;
$skipped = [];
$white = [1.0, 1.0, 1.0];
$black = [0.0, 0.0, 0.0];

/**
 * Records one measured check.
 *
 * @param array<string, mixed> $entry
 */
$record = static function (array $entry, float $ratio, float $minimum, string $severity) use (&$failures, &$advisories, &$checked, $verbose): void {
    $checked++;
    $entry['ratio'] = round($ratio, 2);
    $entry['required'] = $minimum;
    if ($ratio + 0.005 < $minimum) {
        if ($severity === REQUIRED) {
            $failures[] = $entry;
        } else {
            $advisories[] = $entry;
        }
    } elseif ($verbose) {
        printf("  ok  %-12s %-5s %-58s %5.2f:1\n", $entry['preset'], $entry['mode'], $entry['pair'], $ratio);
    }
};

foreach ($presets as $preset) {
    foreach (['light', 'dark'] as $mode) {
        // Later declarations win, exactly as the cascade resolves them. On
        // <body>: the base token layer (`body`, specificity 0,0,1), then the
        // preset's light block (0,1,1), then `.dark body` (also 0,1,1, but
        // later in the file, so it beats the light preset block in dark
        // mode), then the preset's dark block (0,2,1). `:root` and `.dark`
        // sit on <html> and only reach <body> by inheritance, so they come
        // first.
        $presetLight = parseBlock($css, sprintf('body[data-shadcn-preset="%s"]', $preset));
        $scope = $mode === 'light'
            ? array_merge($rootLight, $baseBody, $presetLight)
            : array_merge(
                $rootLight,
                $rootDark,
                $baseBody,
                $presetLight,
                $darkBody,
                parseBlock($css, sprintf('.dark body[data-shadcn-preset="%s"]', $preset)),
            );

        // The page itself is opaque: whatever `--background` says is painted
        // over the canvas (white in light mode, black in dark mode).
        $canvas = $mode === 'light' ? $white : $black;
        $page = ($color = resolve('background', $scope)) !== null ? paint($color, $canvas) : null;

        /**
         * Every PAIR and TINT in one token scope. $beneath is what the scope's
         * own --background is painted over: the canvas for the page, the page
         * for a band.
         */
        $measure = static function (array $in, array $beneath, string $where, array $tintOverrides = []) use ($record, &$skipped, $preset, $mode): void {
            $ground = ($color = resolve('background', $in)) !== null ? paint($color, $beneath) : null;
            $surface = static function (string $token) use ($in, $ground): ?array {
                $color = resolve($token, $in);

                return $color === null || $ground === null ? null : paint($color, $ground);
            };
            foreach (PAIRS as [$foreground, $background, $minimum, $severity, $description]) {
                // Inside a band only the gates count: the advisories (hairlines,
                // chart series) would repeat the page's list once per band.
                if ($where !== '' && $severity === ADVISORY) {
                    continue;
                }
                $bg = $surface($background);
                $fg = resolve($foreground, $in);
                if ($fg === null || $bg === null) {
                    $skipped[] = sprintf('%s/%s%s: --%s on --%s', $preset, $mode, $where, $foreground, $background);
                    continue;
                }
                $record(
                    ['preset' => $preset, 'mode' => $mode, 'pair' => sprintf('--%s on --%s%s', $foreground, $background, $where), 'what' => $description],
                    contrastRatio(paint($fg, $bg), $bg),
                    $minimum,
                    $severity,
                );
            }
            foreach (TINTS as [$inkToken, $inkAlpha, $tintToken, $lightAlpha, $darkAlpha, $description]) {
                $ink = resolve($inkToken, $in);
                $tint = resolve($tintToken, $in);
                if ($ink === null || $tint === null || $ground === null) {
                    $skipped[] = sprintf('%s/%s%s: --%s on --%s tint', $preset, $mode, $where, $inkToken, $tintToken);
                    continue;
                }
                $tintAlpha = $mode === 'light' ? $lightAlpha : $darkAlpha;
                $bg = isset($tintOverrides[$tintToken])
                    ? paint($tintOverrides[$tintToken], $ground)
                    : paint([$tint[0], $tint[1], $tint[2], $tint[3] * $tintAlpha], $ground);
                $fg = [$ink[0], $ink[1], $ink[2], $ink[3] * $inkAlpha];
                $record(
                    [
                        'preset' => $preset,
                        'mode' => $mode,
                        'pair' => sprintf(
                            '--%s%s on --%s%s%s',
                            $inkToken,
                            $inkAlpha < 1 ? sprintf(' at %d%%', (int)round($inkAlpha * 100)) : '',
                            $tintToken,
                            $tintAlpha < 1 ? sprintf(' at %d%%', (int)round($tintAlpha * 100)) : '',
                            $where,
                        ),
                        'what' => $description,
                    ],
                    contrastRatio(paint($fg, $bg), $bg),
                    4.5,
                    REQUIRED,
                );
            }
        };

        $measure($scope, $canvas, '');

        // Painted sections. A band whose rule re-points the token set for its
        // children (the primary band) is a theme of its own: every pair is
        // measured again inside it. The band's captured colours (--d-band…)
        // resolve on the section, in the page's scope, before the children's
        // tokens refer to them. Every band is also checked for its running
        // copy, a form field boundary and the focus ring against the band.
        $genericCopy = ruleDeclarations($framesCss, '.desiderio-content-element :where(p, li, figcaption)')['color'] ?? null;
        foreach (FRAMES as [$band, $ink]) {
            $sectionRule = array_merge(
                ruleDeclarations($framesCss, '.desiderio-section.bg-' . $band),
                $mode === 'dark' ? ruleDeclarations($framesCss, '.dark .desiderio-section.bg-' . $band) : [],
            );
            $section = array_filter(
                $sectionRule,
                static fn (string $name): bool => $name !== 'color' && $name !== 'background-color',
                ARRAY_FILTER_USE_KEY,
            );
            // The section's own tokens may refer to each other (the band is
            // the brand colour mixed toward the band's shade), so they resolve
            // in the page's scope plus their own.
            $sectionScope = array_merge($scope, $section);
            $captured = [];
            foreach ($section as $name => $value) {
                $color = resolveValue($value, $sectionScope);
                if ($color !== null) {
                    $captured[$name] = literal($color);
                }
            }
            $children = array_filter(
                ruleDeclarations($framesCss, sprintf('.desiderio-section.bg-%s > *', $band)),
                static fn (string $name): bool => $name !== 'color' && $name !== 'background-color',
                ARRAY_FILTER_USE_KEY,
            );
            $inBand = array_merge($scope, $captured, $children);
            // What the section paints: its own background-color when the band
            // rule sets one, else the frame's utility colour.
            $bandColor = isset($sectionRule['background-color'])
                ? resolveValue($sectionRule['background-color'], array_merge($scope, $captured))
                : resolve($band, $scope);
            if ($bandColor === null || $page === null) {
                $skipped[] = sprintf('%s/%s: frame %s', $preset, $mode, $band);
                continue;
            }
            $bg = paint($bandColor, $page);
            if ($children !== []) {
                // The band may repaint the soft destructive variants, whose
                // utility tint would otherwise lean toward the band's ink.
                $softDestructive = ruleDeclarations(
                    $framesCss,
                    sprintf('.desiderio-section.bg-%s :is([data-slot="button"], [data-slot="badge"])[data-variant="destructive"]', $band),
                )['background-color'] ?? null;
                $overrides = [];
                if ($softDestructive !== null && ($color = resolveValue($softDestructive, $inBand)) !== null) {
                    $overrides['destructive'] = $color;
                }
                $measure($inBand, $page, sprintf(' in the %s band', $band), $overrides);
            } else {
                // A band that keeps the page's tokens is measured as if it were
                // the page: everything an element paints with page tokens now
                // sits on the band.
                $measure(array_merge($inBand, ['background' => literal($bandColor)]), $page, sprintf(' on the %s band', $band));
            }
            $copyValue = ruleDeclarations($framesCss, sprintf(':where(.desiderio-section.bg-%s) :where(p, li, figcaption)', $band))['color']
                ?? ruleDeclarations($framesCss, sprintf('.desiderio-section.bg-%s :where(p, li, figcaption)', $band))['color']
                ?? $genericCopy
                ?? sprintf('var(--%s)', $ink);
            $copy = resolveValue($copyValue, $inBand);
            if ($copy === null) {
                $skipped[] = sprintf('%s/%s: frame %s copy', $preset, $mode, $band);
                continue;
            }
            $record(
                ['preset' => $preset, 'mode' => $mode, 'pair' => sprintf('running copy on the %s frame', $band), 'what' => sprintf('p, li, figcaption in a painted section (%s)', $copyValue)],
                contrastRatio(paint($copy, $bg), $bg),
                4.5,
                REQUIRED,
            );
            foreach ([['input', 'form field boundary'], ['ring', 'focus ring'], ['d-link', 'link and eyebrow ink']] as [$token, $role]) {
                $color = resolve($token, $inBand);
                if ($color === null) {
                    $skipped[] = sprintf('%s/%s: --%s on the %s frame', $preset, $mode, $token, $band);
                    continue;
                }
                $record(
                    ['preset' => $preset, 'mode' => $mode, 'pair' => sprintf('--%s on the %s frame', $token, $band), 'what' => sprintf('%s in a painted section', $role)],
                    contrastRatio(paint($color, $bg), $bg),
                    $token === 'd-link' ? 4.5 : 3.0,
                    REQUIRED,
                );
            }
        }

        // Panels: everything an element paints with page tokens, on the panel.
        if (isset($panelValues[$mode]) && $page !== null) {
            $panel = resolveValue($panelValues[$mode], array_merge($scope, $baseLayerBody));
            if ($panel === null) {
                $skipped[] = sprintf('%s/%s: panel', $preset, $mode);
            } else {
                $measure(array_merge($scope, ['background' => literal($panel)]), $page, ' on a panel');
            }
        }

        $foreground = resolve('foreground', $scope);
        $background = resolve('background', $scope);
        $navInk = resolveValue($navInkValue, $scope);
        $mutedInk = resolve('muted-foreground', $scope);
        $linkInk = resolve('d-link', $scope);
        $glassColor = resolveValue($glassValue, $scope);
        if ($page === null || $foreground === null || $background === null || $navInk === null || $mutedInk === null || $linkInk === null || $glassColor === null) {
            $skipped[] = sprintf('%s/%s: page composition', $preset, $mode);
            continue;
        }

        // Glass header: its translucent page colour over whatever scrolls
        // beneath it. The worst case beneath is the ink that contrasts least
        // with the nav links: black in light mode, white in dark mode.
        $glass = paint($glassColor, $mode === 'light' ? $black : $white);
        $record(
            ['preset' => $preset, 'mode' => $mode, 'pair' => 'nav link ink on the glass header', 'what' => sprintf('navigation links over scrolling content (%s)', $navInkValue)],
            contrastRatio(paint($navInk, $glass), $glass),
            4.5,
            REQUIRED,
        );

        // The horizon behind the opening section: radial glows of chart hues
        // whose alpha is the hue's chroma times a factor, capped. Upper bound:
        // all of them at full strength on the same spot.
        $horizon = $page;
        foreach ($glows as [, $token, $factor, $cap]) {
            $glow = resolve($token, $scope) ?? resolve('primary', $scope);
            if ($glow !== null) {
                $horizon = paint([$glow[0], $glow[1], $glow[2], max(0.0, min((float)$cap, $glow[1] * (float)$factor))], $horizon);
            }
        }
        $opening = array_merge($scope, ['d-muted-ink' => literal($mutedInk), 'd-link-ink' => literal($linkInk)]);
        $inks = [
            [$foreground, 'foreground', 'headline'],
            [resolveValue($openingMutedValue, $opening), 'muted-foreground', 'lead'],
            [resolveValue($openingLinkValue, $opening), 'd-link', 'eyebrow'],
        ];
        foreach ($inks as [$ink, $name, $role]) {
            if ($ink === null) {
                $skipped[] = sprintf('%s/%s: --%s on the horizon', $preset, $mode, $name);
                continue;
            }
            $record(
                ['preset' => $preset, 'mode' => $mode, 'pair' => sprintf('--%s on the horizon', $name), 'what' => sprintf('opening section %s over the chart glow', $role)],
                contrastRatio(paint($ink, $horizon), $horizon),
                4.5,
                REQUIRED,
            );
        }
    }
}

foreach ($redeclared as $where) {
    $failures[] = ['preset' => '-', 'mode' => '-', 'pair' => $where, 'what' => 'a colour token redeclared where this audit does not measure it', 'ratio' => 0.0, 'required' => 0.0];
}

if ($asJson) {
    echo json_encode([
        'presets' => count($presets),
        'checked' => $checked,
        'skipped' => $skipped,
        'failures' => $failures,
        'redeclared' => $redeclared,
        'advisories' => $advisories,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
    exit($failures === [] ? 0 : 1);
}

printf("Theme contrast: %d presets x 2 modes, %d checks\n", count($presets), $checked);

if ($skipped !== []) {
    printf("\n%d checks skipped (token not defined in that scope):\n", count($skipped));
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
        printf("  %-58s %d scopes, e.g. %s\n", $pair, count($where), $where[0]);
    }
}

if ($failures === []) {
    echo "\nAll required checks pass WCAG 2.2 AA.\n";
    exit(0);
}

printf("\n%d FAILURES:\n\n", count($failures));
foreach ($failures as $failure) {
    if ($failure['preset'] === '-') {
        printf("  %s: %s\n", $failure['pair'], $failure['what']);
        continue;
    }
    printf(
        "  %-12s %-5s  %-58s %5.2f:1  (need %.1f)  %s\n",
        $failure['preset'],
        $failure['mode'],
        $failure['pair'],
        $failure['ratio'],
        $failure['required'],
        $failure['what'],
    );
}
exit(1);
