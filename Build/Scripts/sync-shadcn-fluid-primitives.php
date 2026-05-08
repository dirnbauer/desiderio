#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Synchronize Fluid primitives with the official shadcn/ui registry recipe for
 * the configured shadcn/create preset.
 *
 * Usage:
 *   php Build/Scripts/sync-shadcn-fluid-primitives.php
 *   php Build/Scripts/sync-shadcn-fluid-primitives.php --preset=b0
 *   php Build/Scripts/sync-shadcn-fluid-primitives.php --style=radix-mira
 *   php Build/Scripts/sync-shadcn-fluid-primitives.php --check
 */

$root = dirname(__DIR__, 2);
$scriptRelativePath = 'Build/Scripts/sync-shadcn-fluid-primitives.php';

$options = parseOptions($argv);
$checkOnly = isset($options['check']);

$presetStyles = [
    'b0' => 'radix-nova',
    'b3IWPgRwnI' => 'radix-mira',
    'b4hb38Fyj' => 'radix-mira',
    'b6G5977cw' => 'radix-lyra',
];

$presetIconLibraries = [
    'b0' => 'lucide',
    'b3IWPgRwnI' => 'phosphor',
    'b4hb38Fyj' => 'phosphor',
    'b6G5977cw' => 'tabler',
];

$presetBaseColors = [
    'b0' => 'neutral',
    'b3IWPgRwnI' => 'mist',
    'b4hb38Fyj' => 'olive',
    'b6G5977cw' => 'olive',
];

$settingsPath = $root . '/Configuration/Sets/Desiderio/settings.yaml';
$componentsPath = $root . '/components.json';
$settingsYaml = readTextFile($settingsPath);
$componentsJson = decodeJsonFile($componentsPath);

$preset = $options['preset'] ?? readYamlValue($settingsYaml, 'shadcn', 'preset') ?? 'b6G5977cw';
$configuredStyle = readYamlValue($settingsYaml, 'shadcn', 'style') ?? ($componentsJson['style'] ?? null);
$style = $options['style'] ?? ($presetStyles[$preset] ?? $configuredStyle);

if (!is_string($style) || $style === '' || $style === 'custom') {
    fail('Unable to resolve a concrete shadcn style. Use --style=radix-nova, --style=radix-mira, or --style=radix-lyra.');
}

if (!preg_match('/^radix-[a-z0-9-]+$/', $style)) {
    fail(sprintf('Unsupported shadcn style "%s". This sync script expects the official radix registry style ids.', $style));
}

$iconLibrary = $presetIconLibraries[$preset] ?? ($componentsJson['iconLibrary'] ?? 'lucide');
$baseColor = $presetBaseColors[$preset] ?? ($componentsJson['tailwind']['baseColor'] ?? 'neutral');

$recipes = fetchRecipes($style);
$targets = renderTargets($recipes, $style, $preset, $scriptRelativePath);

$expectedComponentsJson = syncComponentsJson($componentsJson, $style, $iconLibrary, $baseColor);
$expectedSettingsYaml = syncSettingsYaml($settingsYaml, $preset, $style);

if ($checkOnly) {
    $errors = [];

    if (normalizeNewlines(encodeJsonFile($expectedComponentsJson)) !== normalizeNewlines(readTextFile($componentsPath))) {
        $errors[] = 'components.json is not synchronized with the configured shadcn preset/style.';
    }

    if (normalizeNewlines($expectedSettingsYaml) !== normalizeNewlines($settingsYaml)) {
        $errors[] = 'Configuration/Sets/Desiderio/settings.yaml is not synchronized with the configured shadcn preset/style.';
    }

    foreach ($targets as $relativePath => $content) {
        $absolutePath = $root . '/' . $relativePath;
        if (!is_file($absolutePath)) {
            $errors[] = $relativePath . ' is missing.';
            continue;
        }

        if (normalizeNewlines(readTextFile($absolutePath)) !== normalizeNewlines($content)) {
            $errors[] = $relativePath . ' is not synchronized with the official shadcn registry recipe.';
        }
    }

    if ($errors !== []) {
        fail("shadcn Fluid primitive sync check failed:\n- " . implode("\n- ", $errors));
    }

    echo sprintf("Fluid primitives are synchronized for preset %s (%s).\n", $preset, $style);
    exit(0);
}

writeFile($componentsPath, encodeJsonFile($expectedComponentsJson));
writeFile($settingsPath, $expectedSettingsYaml);

foreach ($targets as $relativePath => $content) {
    writeFile($root . '/' . $relativePath, $content);
}

echo sprintf(
    "Synchronized %d Fluid primitives for preset %s (%s) from https://ui.shadcn.com/r/styles/%s/{component}.json.\n",
    count($targets),
    $preset,
    $style,
    $style
);

/**
 * @return array<string, string>
 */
function parseOptions(array $argv): array
{
    $options = [];

    foreach (array_slice($argv, 1) as $argument) {
        if ($argument === '--check') {
            $options['check'] = '1';
            continue;
        }

        if (str_starts_with($argument, '--preset=')) {
            $options['preset'] = substr($argument, 9);
            continue;
        }

        if (str_starts_with($argument, '--style=')) {
            $options['style'] = substr($argument, 8);
            continue;
        }

        fail(sprintf('Unknown argument "%s".', $argument));
    }

    return $options;
}

/**
 * @return array<string, mixed>
 */
function fetchRecipes(string $style): array
{
    $components = [
        'accordion',
        'badge',
        'button',
        'card',
        'input',
        'select',
        'tabs',
        'textarea',
    ];
    $files = [];

    foreach ($components as $component) {
        $files[$component] = fetchRegistryFile($style, $component);
    }

    $buttonIndex = strpos($files['button'], 'const buttonVariants');
    $badgeIndex = strpos($files['badge'], 'const badgeVariants');
    $tabsListIndex = strpos($files['tabs'], 'const tabsListVariants');
    if ($buttonIndex === false || $badgeIndex === false || $tabsListIndex === false) {
        fail('The shadcn registry response no longer contains the expected cva variant declarations.');
    }

    return [
        'button' => [
            'base' => extractCvaBase($files['button'], 'buttonVariants'),
            'variants' => extractObjectEntries(extractObjectBlock($files['button'], 'variant', $buttonIndex)),
            'sizes' => extractObjectEntries(extractObjectBlock($files['button'], 'size', $buttonIndex)),
        ],
        'badge' => [
            'base' => extractCvaBase($files['badge'], 'badgeVariants'),
            'variants' => extractObjectEntries(extractObjectBlock($files['badge'], 'variant', $badgeIndex)),
        ],
        'card' => [
            'root' => extractCnClassForSlot($files['card'], 'card'),
            'header' => extractCnClassForSlot($files['card'], 'card-header'),
            'title' => extractCnClassForSlot($files['card'], 'card-title'),
            'description' => extractCnClassForSlot($files['card'], 'card-description'),
            'content' => extractCnClassForSlot($files['card'], 'card-content'),
            'footer' => extractCnClassForSlot($files['card'], 'card-footer'),
        ],
        'tabs' => [
            'root' => extractCnClassForSlot($files['tabs'], 'tabs'),
            'listBase' => extractCvaBase($files['tabs'], 'tabsListVariants'),
            'listVariants' => extractObjectEntries(extractObjectBlock($files['tabs'], 'variant', $tabsListIndex)),
            'trigger' => extractCnClassForSlot($files['tabs'], 'tabs-trigger'),
            'content' => extractCnClassForSlot($files['tabs'], 'tabs-content'),
        ],
        'accordion' => [
            'root' => extractCnClassForSlot($files['accordion'], 'accordion'),
            'item' => extractCnClassForSlot($files['accordion'], 'accordion-item'),
            'trigger' => extractCnClassForSlot($files['accordion'], 'accordion-trigger'),
            'content' => extractDirectClassForSlot($files['accordion'], 'accordion-content'),
            'contentInner' => extractCnClassForSlot($files['accordion'], 'accordion-content'),
        ],
        'input' => extractCnClassForSlot($files['input'], 'input'),
        'select' => [
            'trigger' => extractCnClassForSlot($files['select'], 'select-trigger'),
        ],
        'textarea' => extractCnClassForSlot($files['textarea'], 'textarea'),
    ];
}

function fetchRegistryFile(string $style, string $component): string
{
    $url = sprintf('https://ui.shadcn.com/r/styles/%s/%s.json', rawurlencode($style), rawurlencode($component));
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'header' => "User-Agent: desiderio-shadcn-fluid-sync\r\n",
        ],
    ]);
    $json = @file_get_contents($url, false, $context);

    if ($json === false) {
        fail(sprintf('Could not fetch official shadcn registry item: %s', $url));
    }

    $registryItem = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($registryItem) || !isset($registryItem['files']) || !is_array($registryItem['files'])) {
        fail(sprintf('Unexpected shadcn registry item shape for %s.', $url));
    }

    foreach ($registryItem['files'] as $file) {
        if (!is_array($file)) {
            continue;
        }

        $path = $file['path'] ?? '';
        $content = $file['content'] ?? null;
        if (is_string($path) && str_ends_with($path, '/ui/' . $component . '.tsx') && is_string($content)) {
            return $content;
        }
    }

    fail(sprintf('Could not find /ui/%s.tsx in official shadcn registry item %s.', $component, $url));
}

/**
 * @return array<string, string>
 */
function renderTargets(array $recipes, string $style, string $preset, string $scriptRelativePath): array
{
    $header = renderGeneratedHeader($style, $preset, $scriptRelativePath);

    return [
        'Resources/Private/Components/Atom/Badge/Badge.fluid.html' => renderBadge($recipes['badge'], $header),
        'Resources/Private/Components/Atom/Button/Button.fluid.html' => renderButton($recipes['button'], $header),
        'Resources/Private/Components/Atom/Input/Input.fluid.html' => renderInput($recipes['input'], $header),
        'Resources/Private/Components/Atom/Select/Select.fluid.html' => renderSelect($recipes['select'], $header),
        'Resources/Private/Components/Atom/Textarea/Textarea.fluid.html' => renderTextarea($recipes['textarea'], $header),
        'Resources/Private/Components/Molecule/AccordionItem/AccordionItem.fluid.html' => renderAccordionItem($recipes['accordion'], $header),
        'Resources/Private/Components/Molecule/Card/Card.fluid.html' => renderCard($recipes['card'], $header),
        'Resources/Private/Components/Molecule/CardFooter/CardFooter.fluid.html' => renderCardFooter($recipes['card'], $header),
        'Resources/Private/Components/Molecule/CardHeader/CardHeader.fluid.html' => renderCardHeader($recipes['card'], $header),
        'Resources/Private/Components/Molecule/Tabs/Tabs.fluid.html' => renderTabs($recipes['tabs'], $header),
        'Resources/Private/Components/Molecule/TabsContent/TabsContent.fluid.html' => renderTabsContent($recipes['tabs'], $header),
        'Resources/Private/Components/Molecule/TabsList/TabsList.fluid.html' => renderTabsList($recipes['tabs'], $header),
        'Resources/Private/Components/Molecule/TabsTrigger/TabsTrigger.fluid.html' => renderTabsTrigger($recipes['tabs'], $header),
    ];
}

function renderGeneratedHeader(string $style, string $preset, string $scriptRelativePath): string
{
    return sprintf(
        "<f:comment>\n  Generated by %s.\n  shadcn preset: %s | style: %s\n  Source: https://ui.shadcn.com/r/styles/%s/{component}.json\n  Local Fluid compatibility only appends direct-child card padding and native select width constraints.\n</f:comment>\n",
        $scriptRelativePath,
        $preset,
        $style,
        $style
    );
}

function renderBadge(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="variant" type="string" optional="{true}" default="default" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<f:variable name="base" value="%s" />', attr($recipe['base'])) . "\n\n"
        . renderSwitch('variant', $recipe['variants'])
        . "\n"
        . '<span data-slot="badge" data-variant="{variant}" class="{base} {variantClass -> f:format.trim()} {class}">' . "\n"
        . '    <f:slot />' . "\n"
        . '</span>' . "\n";
}

function renderButton(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="variant" type="string" optional="{true}" default="default" />' . "\n"
        . '<f:argument name="size" type="string" optional="{true}" default="default" />' . "\n"
        . '<f:argument name="href" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="target" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="disabled" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="type" type="string" optional="{true}" default="button" />' . "\n\n"
        . sprintf('<f:variable name="base" value="%s" />', attr($recipe['base'])) . "\n\n"
        . renderSwitch('variant', $recipe['variants'])
        . "\n"
        . renderSwitch('size', $recipe['sizes'])
        . "\n"
        . '<f:variable name="btnClass" value="{base} {variantClass -> f:format.trim()} {sizeClass -> f:format.trim()} {class}" />' . "\n\n"
        . '<f:if condition="{href}">' . "\n"
        . '    <f:then>' . "\n"
        . '        <f:variable name="rel" value="{f:if(condition: \'{target} == \\\'_blank\\\'\', then: \'noopener noreferrer\', else: \'\')}" />' . "\n"
        . '        <a href="{href}" class="{btnClass -> f:format.trim()}" target="{target}" rel="{rel}" data-slot="button" data-variant="{variant}" data-size="{size}">' . "\n"
        . '            <f:slot />' . "\n"
        . '        </a>' . "\n"
        . '    </f:then>' . "\n"
        . '    <f:else>' . "\n"
        . '        <button type="{type}" class="{btnClass -> f:format.trim()}" disabled="{disabled}" data-slot="button" data-variant="{variant}" data-size="{size}">' . "\n"
        . '            <f:slot />' . "\n"
        . '        </button>' . "\n"
        . '    </f:else>' . "\n"
        . '</f:if>' . "\n";
}

function renderInput(string $class, string $header): string
{
    return $header
        . '<f:argument name="type" type="string" optional="{true}" default="text" />' . "\n"
        . '<f:argument name="name" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="placeholder" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="value" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="disabled" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="required" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<input' . "\n"
        . '    type="{type}"' . "\n"
        . '    name="{name}"' . "\n"
        . '    placeholder="{placeholder}"' . "\n"
        . '    value="{value}"' . "\n"
        . '    disabled="{disabled}"' . "\n"
        . '    required="{required}"' . "\n"
        . '    data-slot="input"' . "\n"
        . sprintf('    class="%s {class}"', attr($class)) . "\n"
        . '/>' . "\n";
}

function renderSelect(array $recipe, string $header): string
{
    $class = normalizeClass($recipe['trigger'] . ' max-w-full');

    return $header
        . '<f:argument name="name" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="disabled" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="required" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<select' . "\n"
        . '    name="{name}"' . "\n"
        . '    disabled="{disabled}"' . "\n"
        . '    required="{required}"' . "\n"
        . '    data-slot="select-trigger"' . "\n"
        . '    data-size="default"' . "\n"
        . sprintf('    class="%s {class}"', attr($class)) . "\n"
        . '>' . "\n"
        . '    <f:slot />' . "\n"
        . '</select>' . "\n";
}

function renderTextarea(string $class, string $header): string
{
    return $header
        . '<f:argument name="name" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="placeholder" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="rows" type="integer" optional="{true}" default="4" />' . "\n"
        . '<f:argument name="disabled" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="required" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<textarea' . "\n"
        . '    name="{name}"' . "\n"
        . '    placeholder="{placeholder}"' . "\n"
        . '    rows="{rows}"' . "\n"
        . '    disabled="{disabled}"' . "\n"
        . '    required="{required}"' . "\n"
        . '    data-slot="textarea"' . "\n"
        . sprintf('    class="%s {class}"', attr($class)) . "\n"
        . '><f:slot /></textarea>' . "\n";
}

function renderAccordionItem(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="trigger" type="string" />' . "\n"
        . '<f:argument name="open" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<div' . "\n"
        . '    x-data="{ open: {f:if(condition: open, then: \'true\', else: \'false\')} }"' . "\n"
        . '    x-bind:data-state="open ? \'open\' : \'closed\'"' . "\n"
        . sprintf('    class="%s {class}"', attr($recipe['item'])) . "\n"
        . '    data-slot="accordion-item"' . "\n"
        . '    data-d-accordion-item' . "\n"
        . '>' . "\n"
        . '    <button' . "\n"
        . '        type="button"' . "\n"
        . '        x-on:click="open = !open"' . "\n"
        . '        x-bind:aria-expanded="open ? \'true\' : \'false\'"' . "\n"
        . '        x-bind:data-state="open ? \'open\' : \'closed\'"' . "\n"
        . sprintf('        class="%s"', attr($recipe['trigger'])) . "\n"
        . '        data-slot="accordion-trigger"' . "\n"
        . '        data-d-accordion-trigger' . "\n"
        . '    >' . "\n"
        . '        <span>{trigger}</span>' . "\n"
        . '        <svg data-slot="accordion-trigger-icon" class="pointer-events-none shrink-0 transition-transform duration-200 group-aria-expanded/accordion-trigger:rotate-180" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>' . "\n"
        . '    </button>' . "\n"
        . '    <div' . "\n"
        . '        x-show="open"' . "\n"
        . '        x-collapse' . "\n"
        . '        x-bind:data-state="open ? \'open\' : \'closed\'"' . "\n"
        . sprintf('        class="%s"', attr($recipe['content'])) . "\n"
        . '        data-slot="accordion-content"' . "\n"
        . '        data-d-accordion-content' . "\n"
        . '    >' . "\n"
        . sprintf('        <div class="%s">', attr($recipe['contentInner'])) . "\n"
        . '            <f:slot />' . "\n"
        . '        </div>' . "\n"
        . '    </div>' . "\n"
        . '</div>' . "\n";
}

function renderCard(array $recipe, string $header): string
{
    $cardRootCompatibility = 'px-4 has-data-[slot=card-header]:px-0 has-data-[slot=card-content]:px-0 has-data-[slot=card-footer]:px-0 has-[>img:first-child]:px-0 data-[size=sm]:px-3 data-[size=sm]:has-data-[slot=card-header]:px-0 data-[size=sm]:has-data-[slot=card-content]:px-0 data-[size=sm]:has-data-[slot=card-footer]:px-0';
    $class = normalizeClass($recipe['root'] . ' ' . $cardRootCompatibility);

    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="role" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="size" type="string" optional="{true}" default="default" />' . "\n\n"
        . sprintf('<div data-slot="card" data-size="{size}" class="%s {class}" role="{role}">', attr($class)) . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderCardHeader(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="title" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="description" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<div data-slot="card-header" class="%s {class}">', attr($recipe['header'])) . "\n"
        . '    <f:if condition="{title}">' . "\n"
        . sprintf('        <h3 data-slot="card-title" class="%s">{title}</h3>', attr($recipe['title'])) . "\n"
        . '    </f:if>' . "\n"
        . '    <f:if condition="{description}">' . "\n"
        . sprintf('        <p data-slot="card-description" class="%s">{description}</p>', attr($recipe['description'])) . "\n"
        . '    </f:if>' . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderCardFooter(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<div data-slot="card-footer" class="%s {class}">', attr($recipe['footer'])) . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderTabs(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="defaultValue" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="orientation" type="string" optional="{true}" default="horizontal" />' . "\n\n"
        . '<div' . "\n"
        . '    x-data="{ active: \'{defaultValue}\' }"' . "\n"
        . sprintf('    class="%s {class}"', attr($recipe['root'])) . "\n"
        . '    data-slot="tabs"' . "\n"
        . '    data-orientation="{orientation}"' . "\n"
        . '    data-d-tabs' . "\n"
        . '    data-default="{defaultValue}"' . "\n"
        . '>' . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderTabsList(array $recipe, string $header): string
{
    $listRecipe = [
        'default' => $recipe['listVariants']['default'] ?? '',
        'line' => $recipe['listVariants']['line'] ?? '',
    ];

    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="variant" type="string" optional="{true}" default="default" />' . "\n\n"
        . sprintf('<f:variable name="base" value="%s" />', attr($recipe['listBase'])) . "\n\n"
        . renderSwitch('variant', $listRecipe)
        . "\n"
        . '<div role="tablist" data-slot="tabs-list" data-variant="{variant}" class="{base} {variantClass -> f:format.trim()} {class}" data-d-tabs-list>' . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderTabsTrigger(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="value" type="string" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<button' . "\n"
        . '    type="button"' . "\n"
        . '    role="tab"' . "\n"
        . '    x-on:click="active = \'{value}\'"' . "\n"
        . '    x-bind:aria-selected="active === \'{value}\'"' . "\n"
        . '    x-bind:data-state="active === \'{value}\' ? \'active\' : \'inactive\'"' . "\n"
        . '    x-bind:data-active="active === \'{value}\' ? \'\' : null"' . "\n"
        . sprintf('    class="%s {class}"', attr($recipe['trigger'])) . "\n"
        . '    data-slot="tabs-trigger"' . "\n"
        . '    data-d-tabs-trigger' . "\n"
        . '    data-value="{value}"' . "\n"
        . '>' . "\n"
        . '    <f:slot />' . "\n"
        . '</button>' . "\n";
}

function renderTabsContent(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="value" type="string" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<div' . "\n"
        . '    role="tabpanel"' . "\n"
        . '    x-show="active === \'{value}\'"' . "\n"
        . '    x-bind:data-state="active === \'{value}\' ? \'active\' : \'inactive\'"' . "\n"
        . sprintf('    class="%s {class}"', attr($recipe['content'])) . "\n"
        . '    data-slot="tabs-content"' . "\n"
        . '    data-d-tabs-content' . "\n"
        . '    data-value="{value}"' . "\n"
        . '>' . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderSwitch(string $argumentName, array $classes): string
{
    $variableName = $argumentName === 'size' ? 'sizeClass' : 'variantClass';
    $lines = [
        sprintf('<f:variable name="%s">', $variableName),
        sprintf('    <f:switch expression="{%s}">', $argumentName),
    ];

    foreach ($classes as $name => $class) {
        if ($name === 'default') {
            continue;
        }

        $lines[] = sprintf('        <f:case value="%s">%s</f:case>', attr((string) $name), text((string) $class));
    }

    $lines[] = sprintf('        <f:defaultCase>%s</f:defaultCase>', text((string) ($classes['default'] ?? '')));
    $lines[] = '    </f:switch>';
    $lines[] = '</f:variable>';

    return implode("\n", $lines) . "\n";
}

function extractCvaBase(string $content, string $constName): string
{
    $index = strpos($content, 'const ' . $constName . ' = cva(');
    if ($index === false) {
        fail(sprintf('Could not find cva declaration %s.', $constName));
    }

    $quoteIndex = strpos($content, '"', $index);
    if ($quoteIndex === false) {
        fail(sprintf('Could not find cva base class for %s.', $constName));
    }

    return normalizeClass(extractStringAt($content, $quoteIndex)[0]);
}

function extractObjectBlock(string $content, string $key, int $fromIndex): string
{
    $keyIndex = strpos($content, $key . ': {', $fromIndex);
    if ($keyIndex === false) {
        fail(sprintf('Could not find object block "%s".', $key));
    }

    $openIndex = strpos($content, '{', $keyIndex);
    if ($openIndex === false) {
        fail(sprintf('Could not find opening brace for object block "%s".', $key));
    }

    $closeIndex = findMatching($content, $openIndex, '{', '}');

    return substr($content, $openIndex + 1, $closeIndex - $openIndex - 1);
}

/**
 * @return array<string, string>
 */
function extractObjectEntries(string $block): array
{
    $entries = [];
    $offset = 0;
    $length = strlen($block);

    while ($offset < $length && preg_match('/\G[\s,]*(["\']?[A-Za-z0-9_-]+["\']?)\s*:/', $block, $match, 0, $offset)) {
        $key = trim($match[1], '"\'');
        $offset += strlen($match[0]);

        while ($offset < $length && ctype_space($block[$offset])) {
            ++$offset;
        }

        if (($block[$offset] ?? null) !== '"') {
            fail(sprintf('Expected a string literal for object entry "%s".', $key));
        }

        [$value, $nextOffset] = extractStringAt($block, $offset);
        $entries[$key] = normalizeClass($value);
        $offset = $nextOffset;
    }

    if ($entries === []) {
        fail('Could not extract any object entries from registry recipe.');
    }

    return $entries;
}

function extractCnClassForSlot(string $content, string $slot): string
{
    $slotIndex = strpos($content, 'data-slot="' . $slot . '"');
    if ($slotIndex === false) {
        fail(sprintf('Could not find data-slot="%s".', $slot));
    }

    $classIndex = strpos($content, 'className={cn(', $slotIndex);
    if ($classIndex === false) {
        return extractDirectClassForSlot($content, $slot);
    }

    $openIndex = strpos($content, '(', $classIndex);
    if ($openIndex === false) {
        fail(sprintf('Could not find cn( call for data-slot="%s".', $slot));
    }

    $closeIndex = findMatching($content, $openIndex, '(', ')');
    $fragment = substr($content, $openIndex + 1, $closeIndex - $openIndex - 1);
    $strings = extractStringLiterals($fragment);

    if ($strings === []) {
        fail(sprintf('Could not extract cn() class strings for data-slot="%s".', $slot));
    }

    return normalizeClass(implode(' ', $strings));
}

function extractDirectClassForSlot(string $content, string $slot): string
{
    $slotIndex = strpos($content, 'data-slot="' . $slot . '"');
    if ($slotIndex === false) {
        fail(sprintf('Could not find data-slot="%s".', $slot));
    }

    $classIndex = strpos($content, 'className="', $slotIndex);
    if ($classIndex === false) {
        fail(sprintf('Could not find direct className for data-slot="%s".', $slot));
    }

    return normalizeClass(extractStringAt($content, $classIndex + strlen('className='))[0]);
}

/**
 * @return array{0: string, 1: int}
 */
function extractStringAt(string $content, int $startIndex): array
{
    $quote = $content[$startIndex] ?? null;
    if ($quote !== '"' && $quote !== "'") {
        fail('Internal parser error: expected a string literal.');
    }

    $value = '';
    $length = strlen($content);
    for ($index = $startIndex + 1; $index < $length; ++$index) {
        $character = $content[$index];
        if ($character === '\\') {
            $value .= $content[$index + 1] ?? '';
            ++$index;
            continue;
        }

        if ($character === $quote) {
            return [$value, $index + 1];
        }

        $value .= $character;
    }

    fail('Internal parser error: unterminated string literal.');
}

function findMatching(string $content, int $openIndex, string $openCharacter, string $closeCharacter): int
{
    $depth = 0;
    $quote = null;
    $length = strlen($content);

    for ($index = $openIndex; $index < $length; ++$index) {
        $character = $content[$index];

        if ($quote !== null) {
            if ($character === '\\') {
                ++$index;
                continue;
            }

            if ($character === $quote) {
                $quote = null;
            }

            continue;
        }

        if ($character === '"' || $character === "'") {
            $quote = $character;
            continue;
        }

        if ($character === $openCharacter) {
            ++$depth;
            continue;
        }

        if ($character === $closeCharacter) {
            --$depth;
            if ($depth === 0) {
                return $index;
            }
        }
    }

    fail(sprintf('Internal parser error: could not match %s.', $openCharacter));
}

/**
 * @return list<string>
 */
function extractStringLiterals(string $content): array
{
    $strings = [];
    $length = strlen($content);

    for ($index = 0; $index < $length; ++$index) {
        if ($content[$index] !== '"') {
            continue;
        }

        [$value, $nextIndex] = extractStringAt($content, $index);
        $strings[] = normalizeClass($value);
        $index = $nextIndex - 1;
    }

    return $strings;
}

/**
 * @return array<string, mixed>
 */
function syncComponentsJson(array $componentsJson, string $style, string $iconLibrary, string $baseColor): array
{
    $componentsJson['style'] = $style;
    $componentsJson['iconLibrary'] = $iconLibrary;

    if (!isset($componentsJson['tailwind']) || !is_array($componentsJson['tailwind'])) {
        $componentsJson['tailwind'] = [];
    }
    $componentsJson['tailwind']['baseColor'] = $baseColor;

    $desiderioRegistry = 'Resources/Public/ShadcnRegistry/{name}.json';
    if (isset($componentsJson['registries']) && is_array($componentsJson['registries']) && isset($componentsJson['registries']['@desiderio'])) {
        $desiderioRegistry = (string) $componentsJson['registries']['@desiderio'];
    }
    $componentsJson['registries'] = [
        '@shadcn' => 'https://ui.shadcn.com/r/styles/{style}/{name}.json',
        '@desiderio' => $desiderioRegistry,
    ];

    return $componentsJson;
}

function syncSettingsYaml(string $settingsYaml, string $preset, string $style): string
{
    $updated = replaceYamlSectionValue($settingsYaml, 'shadcn', 'preset', $preset);
    $updated = replaceYamlSectionValue($updated, 'shadcn', 'style', $style);

    return str_ends_with($updated, "\n") ? $updated : $updated . "\n";
}

function replaceYamlSectionValue(string $yaml, string $section, string $key, string $value): string
{
    if (!preg_match('/(^  ' . preg_quote($section, '/') . ':\s*$)([\s\S]*?)(?=^  [A-Za-z]|\z)/m', $yaml, $sectionMatch, PREG_OFFSET_CAPTURE)) {
        fail(sprintf('Could not find desiderio.%s section in settings.yaml.', $section));
    }

    $block = $sectionMatch[2][0];
    $blockStart = $sectionMatch[2][1];
    $updatedBlock = preg_replace('/(^    ' . preg_quote($key, '/') . ':\s*).+$/m', '${1}' . $value, $block, 1, $replacementCount);
    if (!is_string($updatedBlock) || $replacementCount !== 1) {
        fail(sprintf('Could not update desiderio.%s.%s in settings.yaml.', $section, $key));
    }

    return substr($yaml, 0, $blockStart) . $updatedBlock . substr($yaml, $blockStart + strlen($block));
}

function readYamlValue(string $yaml, string $section, string $key): ?string
{
    if (!preg_match('/^  ' . preg_quote($section, '/') . ':\s*$([\s\S]*?)(?=^  [A-Za-z]|\z)/m', $yaml, $sectionMatch)) {
        return null;
    }

    if (!preg_match('/^    ' . preg_quote($key, '/') . ':\s*([^\s#]+)/m', $sectionMatch[1], $valueMatch)) {
        return null;
    }

    return trim($valueMatch[1], '"\'');
}

/**
 * @return array<string, mixed>
 */
function decodeJsonFile(string $path): array
{
    $content = readTextFile($path);
    $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($data)) {
        fail(sprintf('%s did not decode to a JSON object.', $path));
    }

    return $data;
}

/**
 * @param array<string, mixed> $data
 */
function encodeJsonFile(array $data): string
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        fail('Could not encode JSON.');
    }

    $json = preg_replace_callback(
        '/^( +)/m',
        static fn(array $match): string => str_repeat(' ', (int) (strlen($match[1]) / 2)),
        $json
    );

    if (!is_string($json)) {
        fail('Could not normalize JSON indentation.');
    }

    return $json . "\n";
}

function readTextFile(string $path): string
{
    $content = @file_get_contents($path);
    if ($content === false) {
        fail(sprintf('Could not read %s.', $path));
    }

    return $content;
}

function writeFile(string $path, string $content): void
{
    $directory = dirname($path);
    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        fail(sprintf('Could not create directory %s.', $directory));
    }

    if (@file_put_contents($path, $content) === false) {
        fail(sprintf('Could not write %s.', $path));
    }
}

function normalizeClass(string $class): string
{
    return trim((string) preg_replace('/\s+/', ' ', $class));
}

function normalizeNewlines(string $content): string
{
    return str_replace("\r\n", "\n", $content);
}

function attr(string $value): string
{
    return htmlspecialchars($value, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
}

function text(string $value): string
{
    return htmlspecialchars($value, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function fail(string $message): never
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
