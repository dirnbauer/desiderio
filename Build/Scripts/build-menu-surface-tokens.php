#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generate the "inverted" menu colours of every preset.
 *
 * The create page on ui.shadcn.com offers menuColor "inverted": shadcn puts
 * the class `dark` on every menu (design-system-provider.tsx), so menus show
 * the dark scheme on a light page and stay dark on a dark page. A `.dark`
 * class on a Desiderio menu would do nothing: the presets declare their
 * tokens on <body> (`body[data-shadcn-preset="…"]`, `.dark body[…]`), so a
 * menu inherits the light values whatever class it carries.
 *
 * This script resolves, for every preset in shadcn-theme.css, the values the
 * page would have in dark mode and writes them to
 * Resources/Private/Css/desiderio/26-menu-surface-tokens.css as
 * `--d-menu-inv-*` on <body> (light mode only), plus one rule that maps them
 * onto the menu surfaces:
 *
 *   :root:not(.dark) body[data-menu-color^="inverted"] .d-menu-surface {
 *     --popover: var(--d-menu-inv-popover); …
 *   }
 *
 * `.d-menu-surface--wide` gets the same mapping from 1025px up only: the
 * header's dropdowns are popovers there, but expand
 * inline in the burger panel, where dark tokens on the page background would
 * be light text on a light panel.
 *
 * Dark mode needs nothing: an inverted menu on a dark page is the page's own
 * dark scheme, as on the create page.
 *
 * The cascade that decides a token's dark value on <body> (most specific
 * first): `.dark body[data-shadcn-preset]`, `.dark body` (it follows the
 * preset blocks at equal specificity), `body[data-shadcn-preset]`, `body`,
 * then what <html> inherits: `.dark`, `:root`.
 *
 * Usage: php Build/Scripts/build-menu-surface-tokens.php [--check]
 *   --check  exit 1 when the committed file differs from the generated one.
 */

$root = dirname(__DIR__, 2);
$themeCssPath = $root . '/Resources/Public/Css/shadcn-theme.css';
$outPath = $root . '/Resources/Private/Css/desiderio/26-menu-surface-tokens.css';
$check = in_array('--check', array_slice($argv, 1), true);

$css = @file_get_contents($themeCssPath);
if ($css === false) {
    fwrite(STDERR, "Cannot read {$themeCssPath}\n");
    exit(2);
}

/**
 * Colour tokens a menu reads, directly or through its descendants, plus the
 * body-level aliases derived from them. Spacing, type, radius, density and
 * the status/chart/sidebar colours are not menu business and stay with the
 * page.
 */
const MENU_TOKENS = [
    'background', 'foreground', 'card', 'card-foreground', 'popover', 'popover-foreground',
    'primary', 'primary-foreground', 'secondary', 'secondary-foreground', 'muted', 'muted-foreground',
    'accent', 'accent-foreground', 'destructive', 'destructive-foreground', 'border', 'input', 'ring',
    'd-shadow-sm', 'd-shadow-md', 'd-shadow-lg', 'shadow-sm', 'shadow-md', 'shadow-lg',
    'd-background', 'd-foreground', 'd-card', 'd-popover', 'd-primary', 'd-primary-foreground',
    'd-accent', 'd-accent-foreground', 'd-muted', 'd-muted-foreground', 'd-border', 'd-ring',
    'd-primary-text', 'd-link-on-muted', 'd-link', 'd-input-subtle', 'd-primary-hover',
];

/**
 * @return array<string, string> token name (without --) => raw value
 */
function parseBlock(string $css, string $selector): array
{
    // Block bodies are flat, so a non-greedy match to a closing brace at line
    // start is exact (and keeps `.dark { … }` from matching inside
    // `.dark body[data-…] { … }`). Same parser as build-preset-overview.php.
    $pattern = '/^' . preg_quote($selector, '/') . '\s*\{(.*?)^\}/ms';
    if (preg_match($pattern, $css, $match) !== 1) {
        return [];
    }

    // Comments go first: they quote token names ("the main job of
    // --destructive: …"), which would otherwise read as declarations.
    $body = preg_replace('#/\*.*?\*/#s', '', $match[1]) ?? '';
    $declarations = [];
    if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $body, $found, PREG_SET_ORDER) > 0) {
        foreach ($found as $declaration) {
            $declarations[$declaration[1]] = trim($declaration[2]);
        }
    }

    return $declarations;
}

$rootTokens = parseBlock($css, ':root');
$darkTokens = parseBlock($css, '.dark');
$bodyTokens = parseBlock($css, 'body');
$darkBodyTokens = parseBlock($css, '.dark body');

preg_match_all('/^body\[data-shadcn-preset="([^"]+)"\]\s*\{/m', $css, $found);
$presetIds = array_values(array_unique($found[1]));

/**
 * @param array<string, string> $light
 * @param array<string, string> $dark
 * @return array<string, string>
 */
function darkScheme(array $light, array $dark, array $rootTokens, array $darkTokens, array $bodyTokens, array $darkBodyTokens): array
{
    $values = [];
    foreach (MENU_TOKENS as $token) {
        $value = $dark[$token] ?? $darkBodyTokens[$token] ?? $light[$token] ?? $bodyTokens[$token] ?? $darkTokens[$token] ?? $rootTokens[$token] ?? null;
        if ($value === null) {
            continue;
        }
        // References to another menu token follow it into the inverted set,
        // so --d-primary-text mixes the dark primary, not the page's.
        $values[$token] = preg_replace_callback(
            '/var\(\s*--([a-z0-9-]+)/i',
            static fn(array $match): string => in_array($match[1], MENU_TOKENS, true)
                ? 'var(--d-menu-inv-' . $match[1]
                : $match[0],
            $value,
        ) ?? $value;
    }

    return $values;
}

/**
 * @param array<string, string> $values
 */
function declarations(array $values): string
{
    $lines = [];
    foreach ($values as $token => $value) {
        $lines[] = sprintf('    --d-menu-inv-%s: %s;', $token, $value);
    }

    return implode("\n", $lines);
}

$generic = darkScheme([], [], $rootTokens, $darkTokens, $bodyTokens, $darkBodyTokens);
$missing = array_diff(MENU_TOKENS, array_keys($generic));
if ($missing !== []) {
    fwrite(STDERR, 'shadcn-theme.css lacks menu tokens: ' . implode(', ', $missing) . "\n");
    exit(2);
}

$blocks = [];
$blocks[] = sprintf(
    "/* b0, \"custom\" and every preset without a block of its own: the :root / .dark scheme. */\n:root:not(.dark) body[data-menu-color^=\"inverted\"] {\n%s\n}",
    declarations($generic),
);

foreach ($presetIds as $id) {
    $values = darkScheme(
        parseBlock($css, sprintf('body[data-shadcn-preset="%s"]', $id)),
        parseBlock($css, sprintf('.dark body[data-shadcn-preset="%s"]', $id)),
        $rootTokens,
        $darkTokens,
        $bodyTokens,
        $darkBodyTokens,
    );
    // Only what differs from the generic block: the generic block matches
    // too, one attribute less specific, and supplies the rest.
    $values = array_filter($values, static fn(string $value, string $token): bool => $value !== $generic[$token], ARRAY_FILTER_USE_BOTH);
    if ($values === []) {
        $blocks[] = sprintf('/* %s: identical to the generic dark scheme. */', $id);
        continue;
    }
    $blocks[] = sprintf(
        "/* %s */\n:root:not(.dark) body[data-shadcn-preset=\"%s\"][data-menu-color^=\"inverted\"] {\n%s\n}",
        $id,
        $id,
        declarations($values),
    );
}

$mapping = [];
foreach (MENU_TOKENS as $token) {
    $mapping[] = sprintf('    --%s: var(--d-menu-inv-%s);', $token, $token);
}
$mapping[] = '    color-scheme: dark;';
$mappingBody = implode("\n", $mapping);
$mappingBodyIndented = implode("\n", array_map(static fn(string $line): string => '    ' . $line, $mapping));

$output = <<<CSS
/*
 * Inverted menu colours — GENERATED by Build/Scripts/build-menu-surface-tokens.php.
 * Do not edit: every value is resolved from Resources/Public/Css/shadcn-theme.css.
 * Run `npm run build:desiderio-css` (or the script) after changing a preset.
 *
 * menuColor "inverted" / "inverted-translucent" (create page on
 * ui.shadcn.com, site setting desiderio.shadcn.menuColor): on a light page the
 * menus take the preset's dark scheme. The values sit on <body> as
 * --d-menu-inv-*, the mapping at the end hands them to the menu surfaces.
 * Dark mode needs nothing: the menus already show the dark scheme there.
 */


CSS
    . implode("\n\n", $blocks)
    . "\n\n/* The menu surfaces: language and colour-scheme menus at every width. */\n"
    . ":root:not(.dark) body[data-menu-color^=\"inverted\"] .d-menu-surface {\n{$mappingBody}\n}\n\n"
    . "/* Header dropdowns and flyouts: popovers from 1025px up,\n   inline on the page below that. */\n"
    . "@media (min-width: 1025px) {\n    :root:not(.dark) body[data-menu-color^=\"inverted\"] .d-menu-surface--wide {\n{$mappingBodyIndented}\n    }\n}\n";

if ($check) {
    $current = @file_get_contents($outPath);
    if ($current !== $output) {
        fwrite(STDERR, "26-menu-surface-tokens.css is out of date: run php Build/Scripts/build-menu-surface-tokens.php\n");
        exit(1);
    }
    exit(0);
}

file_put_contents($outPath, $output);
fwrite(STDOUT, sprintf("Wrote %s (%d presets).\n", $outPath, count($presetIds)));
