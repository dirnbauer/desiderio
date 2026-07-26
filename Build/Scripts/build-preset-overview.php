#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Generate the theme-preset overview page from the presets themselves.
 *
 * Two artefacts come out of this script, both read from
 * Resources/Public/Css/shadcn-theme.css so neither can drift from the presets
 * it describes — a hand-written table of fifteen palettes is wrong within a
 * month:
 *
 *   1. Resources/Public/Css/preset-samples.css
 *      Every preset re-scoped from `body[data-shadcn-preset="…"]` to
 *      `[data-shadcn-preset-sample="…"]`, so ANY element can carry a preset,
 *      not just the body. That is what makes the overview page show fifteen
 *      LIVE samples instead of fifteen screenshots.
 *
 *      Each sample block is self-contained: it re-declares every token that
 *      varies between presets (plus everything `body` derives from those
 *      tokens), resolved against `:root` where the preset itself is silent.
 *      Inheriting would be wrong — a card must show ITS preset's surfaces, not
 *      the surfaces of whichever preset the surrounding site runs.
 *
 *   2. Resources/Private/Templates/Partials/Pages/PresetOverview.fluid.html
 *      The cards and the comparison matrix. Fonts, corner radius, control
 *      density, focus-ring width and elevation are read out of the tokens;
 *      the icon library comes from IconRegistry; only the prose sentences
 *      ("best for") are written by hand here.
 *
 * The script is a drift gate as much as a generator: it exits non-zero when
 * shadcn-theme.css, the settings enum and the descriptions below disagree
 * about which presets exist, so adding a preset cannot silently produce a page
 * that ignores it.
 *
 * Usage: php Build/Scripts/build-preset-overview.php [--quiet]
 */

$root = dirname(__DIR__, 2);
$themeCssPath = $root . '/Resources/Public/Css/shadcn-theme.css';
$iconRegistryPath = $root . '/Classes/Icon/IconRegistry.php';
$settingsPath = $root . '/Configuration/Sets/Desiderio/settings.definitions.yaml';
$cssOutPath = $root . '/Resources/Public/Css/preset-samples.css';
$partialOutPath = $root . '/Resources/Private/Templates/Partials/Pages/PresetOverview.fluid.html';

$quiet = in_array('--quiet', array_slice($argv, 1), true);

/**
 * The only hand-written part: what a preset is FOR. Everything measurable is
 * read from the stylesheet, so these sentences must stay qualitative — the
 * moment one of them states a number, it can go stale.
 *
 * Order is display order: the shipped default first, then the rest of the
 * create-page presets, then the ten house presets.
 *
 * @var array<string, array{name: string, origin: string, character: string, use: string}>
 */
$descriptions = [
    'b6G5977cw' => [
        'name' => 'Lyra mono olive',
        'origin' => 'create',
        'character' => 'Monospace from the headings down to the body copy, over olive-warm greys and a near-black primary. The tightest controls and the thinnest focus ring of the create presets.',
        'use' => 'Documentation and technical writing that wants density without raising its voice.',
    ],
    'b0' => [
        'name' => 'Default neutral',
        'origin' => 'create',
        'character' => 'shadcn/ui exactly as it ships: pure greyscale, an ink primary, no hue anywhere. This is the baseline every other preset is a departure from.',
        'use' => 'Admin tools and internal apps that should look like the framework rather than like a brand.',
    ],
    'b27GcrRo' => [
        'name' => 'Rhea modern neutral',
        'origin' => 'create',
        'character' => 'Token for token identical to b0 in both colour schemes — it is the create-page id that produces the neutral default. Both are listed because both are selectable.',
        'use' => 'The same as b0. Keep this id if you want the create page to be able to reopen your theme.',
    ],
    'b4hb38Fyj' => [
        'name' => 'Olive product system',
        'origin' => 'create',
        'character' => 'Olive-tinted greys under a muted teal primary, set in Nunito Sans. The accent reads as a material rather than a highlight.',
        'use' => 'Product and SaaS sites that want to look established instead of loud.',
    ],
    'b3IWPgRwnI' => [
        'name' => 'Mist dashboard',
        'origin' => 'create',
        'character' => 'The only preset that sets its headings in monospace over a sans body, on cool mist greys with a true blue primary.',
        'use' => 'Dashboards and developer products, where the interface should read as instrumentation.',
    ],
    'aurora' => [
        'name' => 'Aurora',
        'origin' => 'house',
        'character' => 'A violet primary on the neutral base, with a soft elevation under every surface. The most saturated accent in the house set.',
        'use' => 'Software and creative-agency sites that want one confident colour.',
    ],
    'marine' => [
        'name' => 'Marine',
        'origin' => 'house',
        'character' => 'Geist over a plain blue, with the roomiest controls and the deepest card elevation. Everything sits a little further apart than elsewhere.',
        'use' => 'Corporate sites and services where trust matters more than personality.',
    ],
    'forest' => [
        'name' => 'Forest',
        'origin' => 'house',
        'character' => 'Emerald on neutral greys with noticeably squarer corners. Sober rather than organic.',
        'use' => 'Sustainability, health and public-sector sites.',
    ],
    'ember' => [
        'name' => 'Ember',
        'origin' => 'house',
        'character' => 'Warm orange in Nunito Sans, with generous corners and roomy controls. Its brand text colour is solved a shade darker than the button fill, because orange at button lightness cannot carry small text.',
        'use' => 'Food, events and consumer brands that want warmth.',
    ],
    'bloom' => [
        'name' => 'Bloom',
        'origin' => 'house',
        'character' => 'Rose on white with the softest corners in the entire set — the pill end of the radius scale.',
        'use' => 'Lifestyle, beauty and community products.',
    ],
    'lagoon' => [
        'name' => 'Lagoon',
        'origin' => 'house',
        'character' => 'Teal, compact controls, a hairline focus ring and no elevation at all: the flattest house preset.',
        'use' => 'Dense interfaces — portals, catalogues, anything with a lot of rows.',
    ],
    'gold' => [
        'name' => 'Gold',
        'origin' => 'house',
        'character' => 'Amber held back from its full chroma so it stays readable, over squared corners.',
        'use' => 'Premium, hospitality and membership sites.',
    ],
    'midnight' => [
        'name' => 'Midnight',
        'origin' => 'house',
        'character' => 'Indigo with roomy controls and raised surfaces. Built to be looked at in dark mode, where the accent lifts and the surfaces separate.',
        'use' => 'Media, gaming and night-first products.',
    ],
    'blossom' => [
        'name' => 'Blossom',
        'origin' => 'house',
        'character' => 'Pink in Nunito Sans with generous corners — the friendliest thing in the set without becoming a toy.',
        'use' => 'Education, non-profits and family-facing brands.',
    ],
    'citrus' => [
        'name' => 'Citrus',
        'origin' => 'house',
        'character' => 'Lime in monospace, compact, hairline ring, flat surfaces and the sharpest corners of all fifteen.',
        'use' => 'Developer tools and technical products with an opinion.',
    ],
];

// ---------------------------------------------------------------- reading

$css = @file_get_contents($themeCssPath);
if ($css === false) {
    fwrite(STDERR, "Cannot read {$themeCssPath}\n");
    exit(2);
}

/**
 * @return array<string, string> token name (without --) => raw value
 */
function parseBlock(string $css, string $selector): array
{
    // Selectors contain [] and . so they must be quoted; block bodies are flat,
    // so a non-greedy match to a closing brace at line start is exact (and
    // keeps `.dark { … }` from matching inside `.dark body[data-…] { … }`).
    $pattern = '/^' . preg_quote($selector, '/') . '\s*\{(.*?)^\}/ms';
    if (preg_match($pattern, $css, $match) !== 1) {
        return [];
    }

    $declarations = [];
    if (preg_match_all('/--([a-z0-9-]+)\s*:\s*([^;]+);/i', $match[1], $found, PREG_SET_ORDER) > 0) {
        foreach ($found as $declaration) {
            // Trailing comments explain a value ("was 0.737 = 2.32:1 …") and
            // are part of neither the value nor this page.
            $declarations[$declaration[1]] = trim(preg_replace('#/\*.*?\*/#s', '', $declaration[2]) ?? '');
        }
    }

    return $declarations;
}

/** @return list<string> preset ids in stylesheet order */
function presetIdsInCssOrder(string $css): array
{
    preg_match_all('/^body\[data-shadcn-preset="([^"]+)"\]\s*\{/m', $css, $found);

    return array_values(array_unique($found[1] ?? []));
}

$rootTokens = parseBlock($css, ':root');
$darkTokens = parseBlock($css, '.dark');
$bodyTokens = parseBlock($css, 'body');
$darkBodyTokens = parseBlock($css, '.dark body');

// b0 has no block of its own: it IS `:root`, which is why it is the only id
// that never appears in a selector.
$cssPresetIds = array_merge(['b0'], presetIdsInCssOrder($css));

$light = [];
$dark = [];
foreach ($cssPresetIds as $id) {
    $light[$id] = $id === 'b0' ? [] : parseBlock($css, sprintf('body[data-shadcn-preset="%s"]', $id));
    $dark[$id] = $id === 'b0' ? [] : parseBlock($css, sprintf('.dark body[data-shadcn-preset="%s"]', $id));
}

// ------------------------------------------------------------ drift gates

$settings = @file_get_contents($settingsPath);
if ($settings === false) {
    fwrite(STDERR, "Cannot read {$settingsPath}\n");
    exit(2);
}

$enumIds = [];
$defaultPreset = '';
if (preg_match('/^  desiderio\.shadcn\.preset:\n(.*?)^  [a-z]/ms', $settings . "\n  x", $match) === 1) {
    if (preg_match('/enum:\n((?:      \S+:.*\n)+)/', $match[1], $enumMatch) === 1) {
        preg_match_all('/^      (\S+):/m', $enumMatch[1], $found);
        $enumIds = $found[1];
    }
    if (preg_match('/^    default: (\S+)/m', $match[1], $defaultMatch) === 1) {
        $defaultPreset = $defaultMatch[1];
    }
}

$problems = [];
// `custom` is the escape hatch for a preset the site adds itself, so it is in
// the enum on purpose and has no block here.
$expected = array_values(array_diff($enumIds, ['custom']));
foreach (array_diff($cssPresetIds, $expected) as $orphan) {
    $problems[] = sprintf('%s has a block in shadcn-theme.css but is missing from the settings enum.', $orphan);
}
foreach (array_diff($expected, $cssPresetIds) as $orphan) {
    $problems[] = sprintf('%s is selectable in the settings enum but has no block in shadcn-theme.css.', $orphan);
}
foreach (array_diff($cssPresetIds, array_keys($descriptions)) as $orphan) {
    $problems[] = sprintf('%s has no description in %s — add one and re-run.', $orphan, basename(__FILE__));
}
foreach (array_diff(array_keys($descriptions), $cssPresetIds) as $orphan) {
    $problems[] = sprintf('%s is described in %s but no longer exists in shadcn-theme.css.', $orphan, basename(__FILE__));
}
if ($enumIds === [] || $defaultPreset === '') {
    $problems[] = 'Could not read the desiderio.shadcn.preset enum or its default from the settings definitions.';
}
if ($problems !== []) {
    fwrite(STDERR, "Presets disagree between files:\n  - " . implode("\n  - ", $problems) . "\n");
    exit(1);
}

$iconRegistry = @file_get_contents($iconRegistryPath);
if ($iconRegistry === false) {
    fwrite(STDERR, "Cannot read {$iconRegistryPath}\n");
    exit(2);
}

/**
 * Icon library per preset, read from the match arms in IconRegistry so the
 * page states what the code does rather than what someone remembers.
 *
 * @return array<string, string>
 */
function iconLibraries(string $php): array
{
    if (preg_match('/function libraryForPreset\(.*?return match \(\$preset\) \{(.*?)\};/s', $php, $match) !== 1) {
        return [];
    }

    $libraries = [];
    preg_match_all("/^\s*((?:'[^']+',?\s*)+)=>\s*'([a-z]+)'/m", $match[1], $arms, PREG_SET_ORDER);
    foreach ($arms as $arm) {
        preg_match_all("/'([^']+)'/", $arm[1], $ids);
        foreach ($ids[1] as $id) {
            $libraries[$id] = $arm[2];
        }
    }

    return $libraries;
}

$iconLibraries = iconLibraries($iconRegistry);
$missingIcons = array_diff($cssPresetIds, array_keys($iconLibraries));
if ($missingIcons !== []) {
    fwrite(STDERR, sprintf(
        "IconRegistry::libraryForPreset() has no arm for: %s\n",
        implode(', ', $missingIcons)
    ));
    exit(1);
}

// ------------------------------------------------- the element-scoped CSS

/**
 * Which tokens a sample has to carry.
 *
 * Anything a preset overrides, obviously — but also everything `body` derives
 * from those overrides. `--d-radius-md: calc(var(--radius) * 0.8)` and the
 * `--d-*` colour aliases are declared on the body element, so they compute
 * once, there, against the SITE's preset; a card that only re-declared
 * `--radius` would still hand its children the site's derived radius. Copying
 * the derived declarations into the sample scope makes them recompute against
 * the sample's own tokens.
 *
 * Tokens that are pure constants (spacing, weights, text sizes) are left
 * behind: they cannot vary, so re-declaring them would only add noise.
 *
 * @param array<string, string> $bodyTokens
 * @param list<string> $varying
 * @return list<string>
 */
function derivedFrom(array $bodyTokens, array $varying): array
{
    $set = array_fill_keys($varying, true);
    // Iterate to a fixed point: a derived token may itself be referenced by
    // another derived token (--d-link-on-muted → --d-primary-text → --primary).
    do {
        $grew = false;
        foreach ($bodyTokens as $name => $value) {
            if (isset($set[$name])) {
                continue;
            }
            preg_match_all('/var\(\s*--([a-z0-9-]+)/i', $value, $references);
            foreach ($references[1] as $reference) {
                if (isset($set[$reference])) {
                    $set[$name] = true;
                    $grew = true;
                    break;
                }
            }
        }
    } while ($grew);

    return array_keys($set);
}

$varying = [];
foreach ($cssPresetIds as $id) {
    $varying = array_merge($varying, array_keys($light[$id]), array_keys($dark[$id]));
}
$varying = array_merge($varying, array_keys($darkTokens), array_keys($darkBodyTokens));
$varying = array_values(array_unique($varying));

// `:root` order keeps the generated blocks readable and diffable; tokens that
// only exist further down (the `--d-*` derivations) follow in body order.
$order = array_merge(array_keys($rootTokens), array_keys($bodyTokens));
$sampleTokens = derivedFrom($bodyTokens, $varying);
usort($sampleTokens, static function (string $a, string $b) use ($order): int {
    $indexA = array_search($a, $order, true);
    $indexB = array_search($b, $order, true);

    return ($indexA === false ? PHP_INT_MAX : $indexA) <=> ($indexB === false ? PHP_INT_MAX : $indexB);
});

$displayIds = array_keys($descriptions);

$blocks = [];
foreach ($displayIds as $id) {
    $lightDeclarations = [];
    $darkDeclarations = [];
    foreach ($sampleTokens as $token) {
        $lightValue = $light[$id][$token] ?? $bodyTokens[$token] ?? $rootTokens[$token] ?? null;
        if ($lightValue !== null) {
            $lightDeclarations[] = sprintf('  --%s: %s;', $token, $lightValue);
        }
        // The dark block only carries what actually changes in dark mode; the
        // rest keeps the sample's own light declaration, exactly as `.dark`
        // works against `:root` upstream.
        $darkValue = $dark[$id][$token] ?? $darkBodyTokens[$token] ?? $darkTokens[$token] ?? null;
        if ($darkValue !== null) {
            $darkDeclarations[] = sprintf('  --%s: %s;', $token, $darkValue);
        }
    }

    $blocks[] = sprintf(
        "/* %s — %s */\n[data-shadcn-preset-sample=\"%s\"] {\n%s\n}\n\n.dark [data-shadcn-preset-sample=\"%s\"] {\n%s\n}",
        $descriptions[$id]['name'],
        $id,
        $id,
        implode("\n", $lightDeclarations),
        $id,
        implode("\n", $darkDeclarations)
    );
}

$sampleCss = <<<CSS
/*
 * Element-scoped theme presets — GENERATED by Build/Scripts/build-preset-overview.php.
 * Do not edit: every value is copied from shadcn-theme.css.
 *
 * The site-wide presets are scoped to `body[data-shadcn-preset="…"]`, which
 * allows exactly one preset per document. These copies are scoped to the
 * attribute alone, so any element can carry one and fifteen presets can paint
 * themselves on the same page — which is how the themes page shows live
 * samples instead of screenshots.
 *
 * Each block is self-contained rather than a set of overrides, because a
 * sample must show ITS preset's surfaces no matter which preset the
 * surrounding site runs. Site-level overrides (data-radius, data-font,
 * data-density) deliberately do not reach into a sample: a sample answers
 * "what does this preset look like", not "what does this site look like".
 *
 * Loaded only where it is used (the PresetOverview partial), never site-wide.
 */

CSS . implode("\n\n", $blocks) . "\n";

file_put_contents($cssOutPath, $sampleCss);

// ------------------------------------------------------------ the partial

/** First family in a font stack, resolved one level through var(). */
function fontFamily(string $token, array $scope): string
{
    $value = $scope[$token] ?? '';
    if (preg_match('/^var\(\s*--([a-z0-9-]+)\s*\)$/i', trim($value), $match) === 1) {
        $value = $scope[$match[1]] ?? '';
    }
    $first = trim(explode(',', $value)[0]);
    $first = trim(str_replace(['"', "'"], '', $first));

    // "Inter Variable" is the family name fontsource ships the variable font
    // under; the typeface is Inter, and that is what a reader is looking for.
    return preg_replace('/ Variable$/', '', $first) ?? $first;
}

/** Control density, named by the height the profile sets. */
function densityLabel(string $height): string
{
    return match ($height) {
        '2rem' => 'Compact',
        '2.5rem' => 'Comfortable',
        default => 'Default',
    };
}

/** How each icon library writes its own name. */
function iconLabel(string $library): string
{
    return match ($library) {
        'hugeicons' => 'HugeIcons',
        'remixicon' => 'Remix Icon',
        default => ucfirst($library),
    };
}

/** Elevation, named by the shadow the preset puts under a surface. */
function elevationLabel(string $shadow): string
{
    return match (true) {
        str_contains($shadow, 'shadow-md') => 'Raised',
        str_contains($shadow, 'shadow-sm') => 'Subtle',
        default => 'Flat',
    };
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$rows = [];
foreach ($displayIds as $id) {
    $scope = array_merge($rootTokens, $light[$id]);
    $rows[] = [
        'id' => $id,
        'name' => $descriptions[$id]['name'],
        'origin' => $descriptions[$id]['origin'],
        'character' => $descriptions[$id]['character'],
        'use' => $descriptions[$id]['use'],
        'heading' => fontFamily('d-font-heading', $scope),
        'body' => fontFamily('d-font-sans', $scope),
        'mono' => fontFamily('d-font-mono', $scope),
        'radius' => $scope['radius'] ?? '',
        'density' => densityLabel($scope['d-control-h'] ?? ''),
        'ring' => $scope['d-ring-width'] ?? '',
        'elevation' => elevationLabel($scope['d-surface-shadow'] ?? ''),
        'icons' => iconLabel($iconLibraries[$id]),
        'isDefault' => $id === $defaultPreset,
    ];
}

$cards = '';
foreach ($rows as $row) {
    $badge = $row['isDefault']
        ? "\n                            <span class=\"badge badge--secondary preset-card__flag\">Shipped default</span>"
        : '';

    $cards .= sprintf(
        <<<'HTML'

                    <article class="preset-card" data-shadcn-preset-sample="%1$s">
                        <header class="preset-card__head">
                            <h3 class="preset-card__name">%2$s</h3>
                            <code class="preset-card__id">%1$s</code>%4$s
                        </header>

                        <p class="preset-card__character">%3$s</p>

                        <div class="preset-card__swatches" role="img" aria-label="Primary, accent and muted colours of the %2$s preset">
                            <span class="preset-card__swatch preset-card__swatch--primary"></span>
                            <span class="preset-card__swatch preset-card__swatch--accent"></span>
                            <span class="preset-card__swatch preset-card__swatch--muted"></span>
                        </div>

                        <div class="preset-card__sample">
                            <button type="button" class="btn btn--default btn--sm" tabindex="-1" aria-hidden="true">Primary</button>
                            <button type="button" class="btn btn--outline btn--sm" tabindex="-1" aria-hidden="true">Outline</button>
                            <span class="badge badge--default">Badge</span>
                            <span class="badge badge--secondary">Secondary</span>
                        </div>

                        <dl class="preset-card__facts">
                            <dt>Headings</dt><dd class="preset-card__font preset-card__font--heading">%5$s</dd>
                            <dt>Body</dt><dd class="preset-card__font preset-card__font--body">%6$s</dd>
                            <dt>Corners</dt><dd>%7$s</dd>
                            <dt>Controls</dt><dd>%8$s</dd>
                            <dt>Icons</dt><dd>%9$s</dd>
                            <dt>Best for</dt><dd>%10$s</dd>
                        </dl>
                    </article>
HTML,
        escape($row['id']),
        escape($row['name']),
        escape($row['character']),
        $badge,
        escape($row['heading']),
        escape($row['body']),
        escape($row['radius']),
        escape($row['density']),
        escape($row['icons']),
        escape($row['use'])
    );
}

$matrixRows = '';
foreach ($rows as $row) {
    $matrixRows .= sprintf(
        <<<'HTML'

                            <tr class="table__row">
                                <th scope="row" class="table__cell preset-matrix__name">
                                    <span class="preset-matrix__dot" data-shadcn-preset-sample="%1$s"></span>
                                    %2$s
                                </th>
                                <td class="table__cell"><code>%1$s</code></td>
                                <td class="table__cell">%3$s</td>
                                <td class="table__cell">%4$s</td>
                                <td class="table__cell">%5$s</td>
                                <td class="table__cell">%6$s</td>
                                <td class="table__cell">%7$s</td>
                                <td class="table__cell">%8$s</td>
                                <td class="table__cell">%9$s</td>
                                <td class="table__cell">%10$s</td>
                            </tr>
HTML,
        escape($row['id']),
        escape($row['name']),
        escape($row['heading']),
        escape($row['body']),
        escape($row['mono']),
        escape($row['radius']),
        escape($row['density']),
        escape($row['ring']),
        escape($row['elevation']),
        escape($row['icons'])
    );
}

$createPresets = count(array_filter($rows, static fn (array $row): bool => $row['origin'] === 'create'));
$housePresets = count($rows) - $createPresets;
$total = count($rows);

$partial = <<<HTML
<html
    xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
    xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"
    data-namespace-typo3-fluid="true"
>

<f:comment>
    GENERATED by Build/Scripts/build-preset-overview.php — do not edit.

    Every fact below is read from Resources/Public/Css/shadcn-theme.css, so the
    page cannot describe a preset differently from how the preset renders.

    Each card carries its own data-shadcn-preset-sample, which is why the
    samples are live rather than pictures: preset-samples.css scopes the tokens
    to that attribute instead of to the body, so {$total} presets can paint
    themselves on one page. Change a preset and the cards change with it.
</f:comment>

<f:asset.css identifier="desiderioPresetSamples" href="EXT:desiderio/Resources/Public/Css/preset-samples.css"/>

<d:layout.section background="muted" spacing="lg" class="preset-overview">
    <d:layout.container>
        <header class="preset-overview__head">
            <p class="preset-overview__eyebrow">Theme presets</p>
            <h2 class="typography typography--h2">{$total} presets, one set of content</h2>
            <p class="typography typography--lead preset-overview__lead">
                Every card below is rendered live in the preset it names — same components,
                same markup, only different tokens. Switching a preset repaints the site and
                never touches a word of content. Switch this page to dark mode and all
                {$total} cards switch with it: each preset ships its own dark token set.
            </p>
        </header>

        <div class="preset-grid">{$cards}
        </div>
    </d:layout.container>
</d:layout.section>

<d:layout.section spacing="lg" class="preset-matrix-section">
    <d:layout.container>
        <header class="preset-matrix__head">
            <h2 class="typography typography--h2">What actually differs</h2>
            <p class="typography typography--lead preset-overview__lead">
                Type, corner radius, control density, focus-ring width and elevation.
                Colour is only part of what a preset decides.
            </p>
        </header>

        <div class="table-wrapper">
            <table class="table preset-matrix">
                <caption class="sr-only">
                    Comparison of the {$total} Desiderio theme presets by heading font, body font,
                    monospace font, corner radius, control density, focus-ring width, elevation
                    and icon library.
                </caption>
                <thead class="table__header">
                    <tr class="table__row">
                        <th scope="col" class="table__head">Preset</th>
                        <th scope="col" class="table__head">Key</th>
                        <th scope="col" class="table__head">Headings</th>
                        <th scope="col" class="table__head">Body</th>
                        <th scope="col" class="table__head">Code</th>
                        <th scope="col" class="table__head">Corners</th>
                        <th scope="col" class="table__head">Controls</th>
                        <th scope="col" class="table__head">Focus ring</th>
                        <th scope="col" class="table__head">Elevation</th>
                        <th scope="col" class="table__head">Icons</th>
                    </tr>
                </thead>
                <tbody>{$matrixRows}
                </tbody>
            </table>
        </div>

        <p class="typography typography--muted preset-note">
            {$createPresets} of the presets come from the
            <a class="d-link" href="https://ui.shadcn.com/create" rel="noreferrer noopener">create page on ui.shadcn.com</a>
            and {$housePresets} are house presets generated against WCAG 2.2 contrast targets.
            Set one for a whole site with <code>desiderio.shadcn.preset</code> in the site settings,
            or for one page and everything below it with the <strong>Theme preset</strong> field in
            the page properties — it is inherited down the rootline, so a campaign subtree can run a
            different look inside the same install. Neither needs a rebuild: the tokens are runtime
            CSS custom properties. Anything you design on the create page yourself goes in as
            <code>custom</code>.
        </p>
    </d:layout.container>
</d:layout.section>

</html>

HTML;

file_put_contents($partialOutPath, $partial);

if (!$quiet) {
    printf(
        "preset-samples.css — %d presets, %d tokens each, %.1f kB\n",
        count($rows),
        count($sampleTokens),
        strlen($sampleCss) / 1024
    );
    printf(
        "PresetOverview.fluid.html — %d cards, %.1f kB\n",
        count($rows),
        strlen($partial) / 1024
    );
}
