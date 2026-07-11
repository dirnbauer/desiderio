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
 *   php Build/Scripts/sync-shadcn-fluid-primitives.php --style=maia
 *   php Build/Scripts/sync-shadcn-fluid-primitives.php --icon-library=remixicon
 *   php Build/Scripts/sync-shadcn-fluid-primitives.php --check
 */

$root = dirname(__DIR__, 2);
$scriptRelativePath = 'Build/Scripts/sync-shadcn-fluid-primitives.php';

$options = parseOptions($argv);
$checkOnly = isset($options['check']);

$settingsPath = $root . '/Configuration/Sets/Desiderio/settings.yaml';
$componentsPath = $root . '/components.json';
$settingsYaml = readTextFile($settingsPath);
$componentsJson = decodeJsonFile($componentsPath);

$preset = $options['preset'] ?? readYamlValue($settingsYaml, 'shadcn', 'preset') ?? 'b6G5977cw';
$presetMetadata = decodePreset($preset);
$configuredStyle = readYamlValue($settingsYaml, 'shadcn', 'style') ?? ($componentsJson['style'] ?? null);
$styleCandidate = $options['style'] ?? (presetStyle($presetMetadata) ?? $configuredStyle);

if (!is_string($styleCandidate) || $styleCandidate === '' || $styleCandidate === 'custom') {
    fail('Unable to resolve a concrete shadcn style. Use --style=vega, --style=nova, --style=maia, --style=lyra, --style=mira, --style=luma, --style=sera, or --style=rhea.');
}

$style = normalizeStyle($styleCandidate);

$iconLibraryCandidate = $options['icon-library'] ?? (presetValue($presetMetadata, 'iconLibrary') ?? ($componentsJson['iconLibrary'] ?? 'lucide'));
$iconLibrary = normalizeIconLibrary((string) $iconLibraryCandidate);
$baseColor = presetValue($presetMetadata, 'baseColor') ?? ($componentsJson['tailwind']['baseColor'] ?? 'neutral');

$recipes = fetchRecipes($style);
$recipes = tokenizeRecipes($recipes);
$targets = renderTargets($recipes, $style, $preset, $scriptRelativePath);

$expectedComponentsJson = syncComponentsJson($componentsJson, $style, $iconLibrary, $baseColor);
$expectedSettingsYaml = syncSettingsYaml($settingsYaml, $preset, $style, $iconLibrary);

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

        if (str_starts_with($argument, '--icon-library=')) {
            $options['icon-library'] = substr($argument, 15);
            continue;
        }

        fail(sprintf('Unknown argument "%s".', $argument));
    }

    return $options;
}

/**
 * @return array<string, mixed>|null
 */
function decodePreset(string $preset): ?array
{
    if ($preset === '' || $preset === 'custom') {
        return null;
    }

    $knownPresets = [
        'b0' => [
            'values' => [
                'style' => 'nova',
                'iconLibrary' => 'lucide',
                'baseColor' => 'neutral',
            ],
        ],
        'b3IWPgRwnI' => [
            'values' => [
                'style' => 'mira',
                'iconLibrary' => 'phosphor',
                'baseColor' => 'mist',
            ],
        ],
        'b4hb38Fyj' => [
            'values' => [
                'style' => 'mira',
                'iconLibrary' => 'phosphor',
                'baseColor' => 'olive',
            ],
        ],
        'b6G5977cw' => [
            'values' => [
                'style' => 'lyra',
                'iconLibrary' => 'tabler',
                'baseColor' => 'olive',
            ],
        ],
        'b1FSk5ls0' => [
            'values' => [
                'style' => 'nova',
                'iconLibrary' => 'phosphor',
                'baseColor' => 'taupe',
            ],
        ],
        'b27GcrRo' => [
            'values' => [
                'style' => 'rhea',
                'iconLibrary' => 'lucide',
                'baseColor' => 'neutral',
            ],
        ],
        // Desiderio house presets (see Build/Scripts/generate-shadcn-presets.php).
        // They inherit the neutral base and a shared structural style; only the
        // accent colour, radius, fonts, and density differ per preset.
        'aurora' => ['values' => ['style' => 'nova', 'iconLibrary' => 'lucide', 'baseColor' => 'neutral']],
        'marine' => ['values' => ['style' => 'nova', 'iconLibrary' => 'tabler', 'baseColor' => 'neutral']],
        'forest' => ['values' => ['style' => 'nova', 'iconLibrary' => 'phosphor', 'baseColor' => 'neutral']],
        'ember' => ['values' => ['style' => 'nova', 'iconLibrary' => 'hugeicons', 'baseColor' => 'neutral']],
        'bloom' => ['values' => ['style' => 'nova', 'iconLibrary' => 'lucide', 'baseColor' => 'neutral']],
        'lagoon' => ['values' => ['style' => 'nova', 'iconLibrary' => 'phosphor', 'baseColor' => 'neutral']],
        'gold' => ['values' => ['style' => 'nova', 'iconLibrary' => 'tabler', 'baseColor' => 'neutral']],
        'midnight' => ['values' => ['style' => 'nova', 'iconLibrary' => 'lucide', 'baseColor' => 'neutral']],
        'blossom' => ['values' => ['style' => 'nova', 'iconLibrary' => 'remixicon', 'baseColor' => 'neutral']],
        'citrus' => ['values' => ['style' => 'nova', 'iconLibrary' => 'tabler', 'baseColor' => 'neutral']],
    ];

    $command = 'npx shadcn@latest preset decode ' . escapeshellarg($preset) . ' --json 2>&1';
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);

    if ($exitCode === 0) {
        $json = trim(implode("\n", $output));
        try {
            $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (is_array($decoded) && isset($decoded['values']) && is_array($decoded['values'])) {
                return $decoded;
            }
        } catch (JsonException) {
            // Fall through to the committed fallback map below.
        }
    }

    if (isset($knownPresets[$preset])) {
        return $knownPresets[$preset];
    }

    fail(sprintf(
        'Could not decode shadcn/create preset "%s". Run `npx shadcn@latest preset decode %s --json` manually or use --style=vega|nova|maia|lyra|mira|luma|sera|rhea.',
        $preset,
        escapeshellarg($preset)
    ));
}

/**
 * @return array<string, string>
 */
function supportedStyles(): array
{
    return [
        'radix-vega' => 'Vega',
        'radix-nova' => 'Nova',
        'radix-maia' => 'Maia',
        'radix-lyra' => 'Lyra',
        'radix-mira' => 'Mira',
        'radix-luma' => 'Luma',
        'radix-sera' => 'Sera',
        'radix-rhea' => 'Rhea',
    ];
}

function normalizeStyle(string $style): string
{
    $style = strtolower(trim($style));
    if ($style !== '' && !str_starts_with($style, 'radix-')) {
        $style = 'radix-' . $style;
    }

    if (!array_key_exists($style, supportedStyles())) {
        fail(sprintf(
            'Unsupported shadcn style "%s". Supported styles are: %s.',
            $style,
            implode(', ', array_keys(supportedStyles()))
        ));
    }

    return $style;
}

/**
 * @return list<string>
 */
function supportedIconLibraries(): array
{
    return ['lucide', 'tabler', 'hugeicons', 'phosphor', 'remixicon'];
}

function normalizeIconLibrary(string $library): string
{
    $library = strtolower(trim(str_replace(['_', ' '], '-', $library)));
    $aliases = [
        'huge' => 'hugeicons',
        'huge-icon' => 'hugeicons',
        'huge-icons' => 'hugeicons',
        'hugeicons' => 'hugeicons',
        'lucide-icons' => 'lucide',
        'phosphor-icon' => 'phosphor',
        'phosphor-icons' => 'phosphor',
        'remix' => 'remixicon',
        'remix-icon' => 'remixicon',
        'remix-icons' => 'remixicon',
        'remixicon' => 'remixicon',
        'tabler-icon' => 'tabler',
        'tabler-icons' => 'tabler',
    ];
    $library = $aliases[$library] ?? $library;

    if (!in_array($library, supportedIconLibraries(), true)) {
        fail(sprintf(
            'Unsupported icon library "%s". Supported icon libraries are: %s.',
            $library,
            implode(', ', supportedIconLibraries())
        ));
    }

    return $library;
}

function presetStyle(?array $presetMetadata): ?string
{
    $style = presetValue($presetMetadata, 'style');
    if ($style === null || $style === '') {
        return null;
    }

    return str_starts_with($style, 'radix-') ? $style : 'radix-' . $style;
}

function presetValue(?array $presetMetadata, string $key): ?string
{
    $value = $presetMetadata['values'][$key] ?? null;

    return is_string($value) && $value !== '' ? $value : null;
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
        'checkbox',
        'field',
        'input',
        'label',
        'radio-group',
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
    $fieldIndex = strpos($files['field'], 'const fieldVariants');
    if ($buttonIndex === false || $badgeIndex === false || $tabsListIndex === false || $fieldIndex === false) {
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
        'checkbox' => [
            'root' => extractCnClassForSlot($files['checkbox'], 'checkbox'),
            'indicator' => extractDirectClassForSlot($files['checkbox'], 'checkbox-indicator'),
        ],
        'field' => [
            'set' => extractCnClassForSlot($files['field'], 'field-set'),
            'legend' => extractCnClassForSlot($files['field'], 'field-legend'),
            'group' => extractCnClassForSlot($files['field'], 'field-group'),
            'base' => extractCvaBase($files['field'], 'fieldVariants'),
            'orientations' => extractObjectEntries(extractObjectBlock($files['field'], 'orientation', $fieldIndex)),
            'content' => extractCnClassForSlot($files['field'], 'field-content'),
            'label' => extractCnClassForSlot($files['field'], 'field-label'),
            'description' => extractCnClassForSlot($files['field'], 'field-description'),
            'error' => extractCnClassForSlot($files['field'], 'field-error'),
        ],
        'label' => extractCnClassForSlot($files['label'], 'label'),
        'radio' => [
            'group' => extractCnClassForSlot($files['radio-group'], 'radio-group'),
            'item' => extractCnClassForSlot($files['radio-group'], 'radio-group-item'),
            'indicator' => extractDirectClassForSlot($files['radio-group'], 'radio-group-indicator'),
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
    $localHeader = renderLocalGeneratedHeader($style, $preset, $scriptRelativePath);

    return [
        'Resources/Private/Components/Atom/Badge/Badge.fluid.html' => renderBadge($recipes['badge'], $header),
        'Resources/Private/Components/Atom/Button/Button.fluid.html' => renderButton($recipes['button'], $header),
        'Resources/Private/Components/Atom/Input/Input.fluid.html' => renderInput($recipes['input'], $header),
        'Resources/Private/Components/Atom/Label/Label.fluid.html' => renderLabel($recipes['label'], $header),
        'Resources/Private/Components/Atom/Select/Select.fluid.html' => renderSelect($recipes['select'], $header),
        'Resources/Private/Components/Atom/Textarea/Textarea.fluid.html' => renderTextarea($recipes['textarea'], $header),
        'Resources/Private/Components/Atom/Typography/Typography.fluid.html' => renderTypography($localHeader),
        'Resources/Private/Components/Atom/ControlClass/ControlClass.fluid.html' => renderControlClass($recipes, $header),
        'Resources/Private/Extensions/Powermail/Partials/Form/ShadcnClass.html' => renderPowermailShadcnClass($recipes, $header),
        'Resources/Private/Components/Molecule/AccordionItem/AccordionItem.fluid.html' => renderAccordionItem($recipes['accordion'], $header),
        'Resources/Private/Components/Molecule/Card/Card.fluid.html' => renderCard($recipes['card'], $header),
        'Resources/Private/Components/Molecule/CardContent/CardContent.fluid.html' => renderCardContent($recipes['card'], $header),
        'Resources/Private/Components/Molecule/CardFooter/CardFooter.fluid.html' => renderCardFooter($recipes['card'], $header),
        'Resources/Private/Components/Molecule/CardHeader/CardHeader.fluid.html' => renderCardHeader($recipes['card'], $header),
        'Resources/Private/Components/Molecule/CheckboxControl/CheckboxControl.fluid.html' => renderCheckboxControl($header),
        'Resources/Private/Components/Molecule/Field/Field.fluid.html' => renderField($recipes, $header),
        'Resources/Private/Components/Molecule/FieldGroup/FieldGroup.fluid.html' => renderFieldGroup($recipes, $header),
        'Resources/Private/Components/Molecule/FieldLabel/FieldLabel.fluid.html' => renderFieldLabel($recipes, $header),
        'Resources/Private/Components/Molecule/FieldLegend/FieldLegend.fluid.html' => renderFieldLegend($header),
        'Resources/Private/Components/Molecule/FieldSet/FieldSet.fluid.html' => renderFieldSet($recipes, $header),
        'Resources/Private/Components/Molecule/OptionLabel/OptionLabel.fluid.html' => renderOptionLabel($recipes, $header),
        'Resources/Private/Components/Molecule/RadioControl/RadioControl.fluid.html' => renderRadioControl($recipes, $header),
        'Resources/Private/Components/Molecule/SelectNative/SelectNative.fluid.html' => renderSelectNative($recipes, $header),
        'Resources/Private/Components/Molecule/Tabs/Tabs.fluid.html' => renderTabs($recipes['tabs'], $header),
        'Resources/Private/Components/Molecule/TabsContent/TabsContent.fluid.html' => renderTabsContent($recipes['tabs'], $header),
        'Resources/Private/Components/Molecule/TabsList/TabsList.fluid.html' => renderTabsList($recipes['tabs'], $header),
        'Resources/Private/Components/Molecule/TabsTrigger/TabsTrigger.fluid.html' => renderTabsTrigger($recipes['tabs'], $header),
    ];
}

function renderGeneratedHeader(string $style, string $preset, string $scriptRelativePath): string
{
    return sprintf(
        "<f:comment>\n  Generated by %s.\n  shadcn preset: %s | style: %s\n  Source: https://ui.shadcn.com/r/styles/%s/{component}.json\n  Control shape is tokenized (radius via --radius, height/text/padding via --d-control-*, focus ring via --d-ring-width, card elevation via --d-surface-shadow), so shape switches per preset.\n</f:comment>\n",
        $scriptRelativePath,
        $preset,
        $style,
        $style
    );
}

function renderLocalGeneratedHeader(string $style, string $preset, string $scriptRelativePath): string
{
    return sprintf(
        "<f:comment>\n  Generated by %s.\n  shadcn preset: %s | style: %s\n  Local semantic primitive. shadcn/ui Typography is documentation example code, not a registry component contract.\n  Visual changes should come from shadcn/create tokens, shared CSS, or caller class overrides.\n</f:comment>\n",
        $scriptRelativePath,
        $preset,
        $style
    );
}

function renderTypography(string $header): string
{
    return $header
        . '<f:argument name="tag" type="string" optional="{true}" default="p" />' . "\n"
        . '<f:argument name="variant" type="string" optional="{true}" default="p" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="id" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="itemprop" type="string" optional="{true}" />' . "\n\n"
        . '<f:variable name="variantClass">' . "\n"
        . '    <f:switch expression="{variant}">' . "\n"
        . '        <f:case value="h1">scroll-m-20 text-4xl font-extrabold tracking-tight lg:text-5xl</f:case>' . "\n"
        . '        <f:case value="h2">scroll-m-20 text-3xl font-semibold tracking-tight first:mt-0</f:case>' . "\n"
        . '        <f:case value="h3">scroll-m-20 text-2xl font-semibold tracking-tight</f:case>' . "\n"
        . '        <f:case value="h4">scroll-m-20 text-xl font-semibold tracking-tight</f:case>' . "\n"
        . '        <f:case value="lead">text-xl text-muted-foreground</f:case>' . "\n"
        . '        <f:case value="large">text-lg font-semibold</f:case>' . "\n"
        . '        <f:case value="small">text-sm font-medium leading-none</f:case>' . "\n"
        . '        <f:case value="muted">text-sm text-muted-foreground</f:case>' . "\n"
        . '        <f:case value="blockquote">mt-6 border-l-2 pl-6 italic</f:case>' . "\n"
        . '        <f:case value="code">relative rounded bg-muted px-[0.3rem] py-[0.2rem] font-mono text-sm font-semibold</f:case>' . "\n"
        . '        <f:defaultCase>leading-7</f:defaultCase>' . "\n"
        . '    </f:switch>' . "\n"
        . '</f:variable>' . "\n\n"
        . '<f:variable name="combinedClass" value="{variantClass -> f:format.trim()} {class}" />' . "\n\n"
        . '<f:switch expression="{tag}">' . "\n"
        . '    <f:case value="h1"><h1 data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></h1></f:case>' . "\n"
        . '    <f:case value="h2"><h2 data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></h2></f:case>' . "\n"
        . '    <f:case value="h3"><h3 data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></h3></f:case>' . "\n"
        . '    <f:case value="h4"><h4 data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></h4></f:case>' . "\n"
        . '    <f:case value="blockquote"><blockquote data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></blockquote></f:case>' . "\n"
        . '    <f:case value="span"><span data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></span></f:case>' . "\n"
        . '    <f:case value="div"><div data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></div></f:case>' . "\n"
        . '    <f:defaultCase><p data-slot="typography" data-variant="{variant}" class="{combinedClass -> f:format.trim()}" id="{id}" itemprop="{itemprop}"><f:slot /></p></f:defaultCase>' . "\n"
        . '</f:switch>' . "\n";
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
        . '        <f:if condition="{disabled}">' . "\n"
        . '            <f:then>' . "\n"
        . '                <button type="{type}" class="{btnClass -> f:format.trim()}" disabled="disabled" data-slot="button" data-variant="{variant}" data-size="{size}">' . "\n"
        . '                    <f:slot />' . "\n"
        . '                </button>' . "\n"
        . '            </f:then>' . "\n"
        . '            <f:else>' . "\n"
        . '                <button type="{type}" class="{btnClass -> f:format.trim()}" data-slot="button" data-variant="{variant}" data-size="{size}">' . "\n"
        . '                    <f:slot />' . "\n"
        . '                </button>' . "\n"
        . '            </f:else>' . "\n"
        . '        </f:if>' . "\n"
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
        . sprintf('<f:variable name="inputClass" value="%s {class}" />', attr($class)) . "\n\n"
        . '<f:if condition="{disabled}">' . "\n"
        . '    <f:then>' . "\n"
        . '        <f:if condition="{required}">' . "\n"
        . '            <f:then>' . "\n"
        . '                <input type="{type}" name="{name}" placeholder="{placeholder}" value="{value}" disabled="disabled" required="required" data-slot="input" class="{inputClass}" />' . "\n"
        . '            </f:then>' . "\n"
        . '            <f:else>' . "\n"
        . '                <input type="{type}" name="{name}" placeholder="{placeholder}" value="{value}" disabled="disabled" data-slot="input" class="{inputClass}" />' . "\n"
        . '            </f:else>' . "\n"
        . '        </f:if>' . "\n"
        . '    </f:then>' . "\n"
        . '    <f:else>' . "\n"
        . '        <f:if condition="{required}">' . "\n"
        . '            <f:then>' . "\n"
        . '                <input type="{type}" name="{name}" placeholder="{placeholder}" value="{value}" required="required" data-slot="input" class="{inputClass}" />' . "\n"
        . '            </f:then>' . "\n"
        . '            <f:else>' . "\n"
        . '                <input type="{type}" name="{name}" placeholder="{placeholder}" value="{value}" data-slot="input" class="{inputClass}" />' . "\n"
        . '            </f:else>' . "\n"
        . '        </f:if>' . "\n"
        . '    </f:else>' . "\n"
        . '</f:if>' . "\n";
}

function renderLabel(string $class, string $header): string
{
    return $header
        . '<f:argument name="for" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<label for="{for}" data-slot="label" class="%s {class}">', attr($class)) . "\n"
        . '    <f:slot />' . "\n"
        . '</label>' . "\n";
}

function renderSelect(array $recipe, string $header): string
{
    $class = normalizeClass($recipe['trigger'] . ' d-shadcn-control max-w-full appearance-none pr-8');
    $wrapperClass = 'relative block w-full';
    $iconClass = 'pointer-events-none absolute end-2 top-1/2 size-4 -translate-y-1/2 text-muted-foreground opacity-70';

    return '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" xmlns:di="http://typo3.org/ns/Webconsulting/Desiderio/ViewHelpers" data-namespace-typo3-fluid="true">' . "\n"
        . $header
        . '<f:argument name="name" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="disabled" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="required" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<f:variable name="selectClass" value="%s {class}" />', attr($class)) . "\n\n"
        . sprintf('<span data-slot="native-select" class="%s">', attr($wrapperClass)) . "\n"
        . '<f:if condition="{disabled}">' . "\n"
        . '    <f:then>' . "\n"
        . '        <f:if condition="{required}">' . "\n"
        . '            <f:then>' . "\n"
        . '                <select name="{name}" disabled="disabled" required="required" data-slot="select-trigger" data-size="default" class="{selectClass}"><f:slot /></select>' . "\n"
        . '            </f:then>' . "\n"
        . '            <f:else>' . "\n"
        . '                <select name="{name}" disabled="disabled" data-slot="select-trigger" data-size="default" class="{selectClass}"><f:slot /></select>' . "\n"
        . '            </f:else>' . "\n"
        . '        </f:if>' . "\n"
        . '    </f:then>' . "\n"
        . '    <f:else>' . "\n"
        . '        <f:if condition="{required}">' . "\n"
        . '            <f:then>' . "\n"
        . '                <select name="{name}" required="required" data-slot="select-trigger" data-size="default" class="{selectClass}"><f:slot /></select>' . "\n"
        . '            </f:then>' . "\n"
        . '            <f:else>' . "\n"
        . '                <select name="{name}" data-slot="select-trigger" data-size="default" class="{selectClass}"><f:slot /></select>' . "\n"
        . '            </f:else>' . "\n"
        . '        </f:if>' . "\n"
        . '    </f:else>' . "\n"
        . '</f:if>' . "\n"
        . sprintf('    <di:icon name="chevron-down" size="sm" class="%s" />', attr($iconClass)) . "\n"
        . '</span>' . "\n"
        . '</html>' . "\n";
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
        . sprintf('<f:variable name="textareaClass" value="%s {class}" />', attr($class)) . "\n\n"
        . '<f:if condition="{disabled}">' . "\n"
        . '    <f:then>' . "\n"
        . '        <f:if condition="{required}">' . "\n"
        . '            <f:then>' . "\n"
        . '                <textarea name="{name}" placeholder="{placeholder}" rows="{rows}" disabled="disabled" required="required" data-slot="textarea" class="{textareaClass}"><f:slot /></textarea>' . "\n"
        . '            </f:then>' . "\n"
        . '            <f:else>' . "\n"
        . '                <textarea name="{name}" placeholder="{placeholder}" rows="{rows}" disabled="disabled" data-slot="textarea" class="{textareaClass}"><f:slot /></textarea>' . "\n"
        . '            </f:else>' . "\n"
        . '        </f:if>' . "\n"
        . '    </f:then>' . "\n"
        . '    <f:else>' . "\n"
        . '        <f:if condition="{required}">' . "\n"
        . '            <f:then>' . "\n"
        . '                <textarea name="{name}" placeholder="{placeholder}" rows="{rows}" required="required" data-slot="textarea" class="{textareaClass}"><f:slot /></textarea>' . "\n"
        . '            </f:then>' . "\n"
        . '            <f:else>' . "\n"
        . '                <textarea name="{name}" placeholder="{placeholder}" rows="{rows}" data-slot="textarea" class="{textareaClass}"><f:slot /></textarea>' . "\n"
        . '            </f:else>' . "\n"
        . '        </f:if>' . "\n"
        . '    </f:else>' . "\n"
        . '</f:if>' . "\n";
}

/**
 * @return array<string, string>
 */
function composeControlClassMap(array $recipes): array
{
    $cardRootCompatibility = 'px-4 has-data-[slot=card-header]:px-0 has-data-[slot=card-content]:px-0 has-data-[slot=card-footer]:px-0 has-[>img:first-child]:px-0 data-[size=sm]:px-3 data-[size=sm]:has-data-[slot=card-header]:px-0 data-[size=sm]:has-data-[slot=card-content]:px-0 data-[size=sm]:has-data-[slot=card-footer]:px-0';
    $controlMarker = 'd-shadcn-control';
    $fieldVertical = normalizeClass($recipes['field']['base'] . ' ' . ($recipes['field']['orientations']['vertical'] ?? ''));
    $fieldHorizontal = normalizeClass($recipes['field']['base'] . ' ' . ($recipes['field']['orientations']['horizontal'] ?? ''));
    $fieldLabel = removeClassTokens(
        normalizeClass($recipes['label'] . ' ' . $recipes['field']['label'] . ' font-medium text-foreground'),
        [
            'has-data-checked:border-primary/30',
            'has-data-checked:bg-primary/5',
            'dark:has-data-checked:border-primary/20',
            'dark:has-data-checked:bg-primary/10',
        ]
    );
    $input = normalizeClass($recipes['input'] . ' ' . $controlMarker . ' min-w-0');
    $select = normalizeClass($recipes['select']['trigger'] . ' ' . $controlMarker . ' w-full min-w-0 max-w-full');
    $nativeSelect = normalizeClass($select . ' appearance-none pr-8');
    $textarea = normalizeClass($recipes['textarea'] . ' ' . $controlMarker . ' min-w-0');
    $checkbox = normalizeClass(nativeCheckedClass($recipes['checkbox']['root']) . ' ' . $controlMarker . ' peer appearance-none size-4! min-h-4!');
    $radio = normalizeClass(nativeCheckedClass($recipes['radio']['item']) . ' ' . $controlMarker . ' peer appearance-none size-4! min-h-4!');

    return [
        'alertDestructive' => 'rounded-md border border-destructive/30 bg-destructive/10 p-4 text-sm text-destructive',
        'buttonDefault' => normalizeClass(composeButtonClass($recipes['button'], 'default', 'default') . ' ' . $controlMarker),
        'buttonDestructive' => normalizeClass(composeButtonClass($recipes['button'], 'destructive', 'default') . ' ' . $controlMarker),
        'buttonOutline' => normalizeClass(composeButtonClass($recipes['button'], 'outline', 'default') . ' ' . $controlMarker),
        'captchaImage' => 'mt-3 rounded-md border border-border',
        'card' => normalizeClass($recipes['card']['root'] . ' ' . $cardRootCompatibility),
        'cardCompact' => normalizeClass($recipes['card']['root'] . ' ' . $cardRootCompatibility . ' p-4'),
        'cardContent' => normalizeClass($recipes['card']['content'] . ' space-y-6'),
        'cardDescription' => $recipes['card']['description'],
        'cardHeaderBordered' => normalizeClass($recipes['card']['header'] . ' border-b'),
        'cardTitle' => $recipes['card']['title'],
        'checkboxIcon' => normalizeClass($recipes['checkbox']['indicator'] . ' pointer-events-none absolute left-1/2 top-1/2 size-3.5 -translate-x-1/2 -translate-y-1/2 text-background opacity-0 transition-opacity peer-checked:opacity-100'),
        'checkboxInput' => $checkbox,
        'field' => $fieldVertical,
        'fieldHorizontal' => $fieldHorizontal,
        'fieldError' => $recipes['field']['error'],
        'fieldGroup' => $recipes['field']['group'],
        'fieldLabel' => $fieldLabel,
        'fieldLegend' => 'mb-2 flex w-fit items-center gap-2 text-xs font-medium leading-snug text-foreground',
        'fieldSet' => $recipes['field']['set'],
        'fileInput' => normalizeClass($input . ' h-auto min-h-24 cursor-pointer items-center border-dashed bg-muted/30 px-3 py-3 text-muted-foreground hover:bg-muted/50'),
        'flashDestructive' => 'rounded-md border border-destructive/30 bg-destructive/10 p-3 text-sm text-destructive',
        'input' => $input,
        'optionLabel' => normalizeClass($fieldHorizontal . ' cursor-pointer items-center gap-3'),
        'optionText' => normalizeClass($recipes['label'] . ' min-w-0 text-foreground'),
        'panel' => 'rounded-md border border-border bg-muted/30 p-3 text-sm text-muted-foreground',
        'panelContent' => 'rounded-md border border-border bg-muted/30 p-4 text-xs/relaxed text-muted-foreground',
        'radioDot' => 'pointer-events-none absolute left-1/2 top-1/2 size-2 -translate-x-1/2 -translate-y-1/2 rounded-full bg-background opacity-0 transition-opacity peer-checked:opacity-100',
        'radioInput' => $radio,
        'select' => $select,
        'selectIcon' => 'pointer-events-none absolute end-2 top-1/2 size-4 -translate-y-1/2 text-muted-foreground opacity-70',
        'selectNative' => $nativeSelect,
        'selectWrapper' => 'relative block w-full',
        'stepIndex' => composeBadgeClass($recipes['badge'], 'outline', 'me-2 size-5 px-0 text-[11px]'),
        'tabsList' => composeTabsListClass($recipes['tabs'], 'default'),
        'tabsTrigger' => $recipes['tabs']['trigger'],
        'textarea' => $textarea,
    ];
}

function renderControlClassSwitch(array $classes, string $header): string
{
    $lines = [
        $header,
        '<f:argument name="slot" type="string" />',
        '',
        '<f:switch expression="{slot}">',
    ];

    foreach ($classes as $slot => $class) {
        $lines[] = sprintf('    <f:case value="%s">%s</f:case>', attr($slot), text($class));
    }

    $lines[] = '    <f:defaultCase></f:defaultCase>';
    $lines[] = '</f:switch>';

    return implode("\n", $lines) . "\n";
}

function renderControlClass(array $recipes, string $header): string
{
    return renderControlClassSwitch(composeControlClassMap($recipes), $header);
}

function renderPowermailShadcnClass(array $recipes, string $header): string
{
    return renderControlClass($recipes, $header);
}

function renderField(array $recipes, string $header): string
{
    $classes = composeControlClassMap($recipes);

    return $header
        . '<f:argument name="orientation" type="string" optional="{true}" default="vertical" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="role" type="string" optional="{true}" default="group" />' . "\n\n"
        . '<f:variable name="orientationClass">' . "\n"
        . '    <f:switch expression="{orientation}">' . "\n"
        . sprintf('        <f:case value="horizontal">%s</f:case>', text($classes['fieldHorizontal'])) . "\n"
        . sprintf('        <f:defaultCase>%s</f:defaultCase>', text($classes['field'])) . "\n"
        . '    </f:switch>' . "\n"
        . '</f:variable>' . "\n\n"
        . '<div {f:if(condition: role, then: \'role="{role}"\')} data-slot="field" data-orientation="{orientation}" class="{orientationClass -> f:format.trim()} {class}">' . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderFieldLabel(array $recipes, string $header): string
{
    $classes = composeControlClassMap($recipes);

    return $header
        . '<f:argument name="for" type="string" />' . "\n"
        . '<f:argument name="mandatory" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="title" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<label for="{for}" data-slot="field-label" class="%s {class}" title="{title}">', attr($classes['fieldLabel'])) . "\n"
        . '    <span class="inline-flex items-baseline gap-1">' . "\n"
        . '        <span><f:slot /></span>' . "\n"
        . '        <f:if condition="{mandatory}">' . "\n"
        . '            <span class="text-destructive" aria-hidden="true">*</span>' . "\n"
        . '        </f:if>' . "\n"
        . '    </span>' . "\n"
        . '</label>' . "\n";
}

function renderFieldGroup(array $recipes, string $header): string
{
    $classes = composeControlClassMap($recipes);

    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<div data-slot="field-group" class="%s {class}">', attr($classes['fieldGroup'])) . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderFieldSet(array $recipes, string $header): string
{
    $classes = composeControlClassMap($recipes);

    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<fieldset data-slot="field-set" class="%s {class}">', attr($classes['fieldSet'])) . "\n"
        . '    <f:slot />' . "\n"
        . '</fieldset>' . "\n";
}

function renderFieldLegend(string $header): string
{
    return $header
        . '<f:argument name="mandatory" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="title" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<legend data-slot="field-legend" class="mb-2 flex w-fit items-center gap-2 text-xs font-medium leading-snug text-foreground {class}" title="{title}">' . "\n"
        . '    <span class="inline-flex items-baseline gap-1">' . "\n"
        . '        <span><f:slot /></span>' . "\n"
        . '        <f:if condition="{mandatory}">' . "\n"
        . '            <span class="text-destructive" aria-hidden="true">*</span>' . "\n"
        . '        </f:if>' . "\n"
        . '    </span>' . "\n"
        . '</legend>' . "\n";
}

function renderOptionLabel(array $recipes, string $header): string
{
    $classes = composeControlClassMap($recipes);

    return $header
        . '<f:argument name="for" type="string" optional="{true}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<label for="{for}" data-slot="field" data-orientation="horizontal" class="%s {class}">', attr($classes['optionLabel'])) . "\n"
        . '    <f:slot />' . "\n"
        . '</label>' . "\n";
}

function renderCheckboxControl(string $header): string
{
    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<span data-slot="checkbox" class="relative inline-flex size-4 shrink-0 items-center justify-center {class}">' . "\n"
        . '    <f:slot />' . "\n"
        . '</span>' . "\n";
}

function renderRadioControl(array $recipes, string $header): string
{
    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<span data-slot="radio-group-item" class="relative inline-flex size-4 shrink-0 items-center justify-center {class}">' . "\n"
        . '    <f:slot />' . "\n"
        . '</span>' . "\n";
}

function renderSelectNative(array $recipes, string $header): string
{
    $classes = composeControlClassMap($recipes);

    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<span data-slot="native-select" class="%s {class}">', attr($classes['selectWrapper'])) . "\n"
        . '    <f:slot />' . "\n"
        . '</span>' . "\n";
}

function renderCardContent(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . sprintf('<div data-slot="card-content" class="%s space-y-6 {class}">', attr($recipe['content'])) . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderAccordionItem(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="trigger" type="string" />' . "\n"
        . '<f:argument name="open" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<div' . "\n"
        . '    data-state="{f:if(condition: open, then: \'open\', else: \'closed\')}"' . "\n"
        . sprintf('    class="%s {class}"', attr($recipe['item'])) . "\n"
        . '    data-slot="accordion-item"' . "\n"
        . '    data-d-accordion-item' . "\n"
        . '>' . "\n"
        . '    <button' . "\n"
        . '        type="button"' . "\n"
        . '        aria-expanded="{f:if(condition: open, then: \'true\', else: \'false\')}"' . "\n"
        . '        data-state="{f:if(condition: open, then: \'open\', else: \'closed\')}"' . "\n"
        . sprintf('        class="%s"', attr($recipe['trigger'])) . "\n"
        . '        data-slot="accordion-trigger"' . "\n"
        . '        data-d-accordion-trigger' . "\n"
        . '    >' . "\n"
        . '        <span>{trigger}</span>' . "\n"
        . '        <svg data-slot="accordion-trigger-icon" class="pointer-events-none shrink-0 transition-transform duration-200 group-aria-expanded/accordion-trigger:rotate-180" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>' . "\n"
        . '    </button>' . "\n"
        . '    <div' . "\n"
        . '        data-state="{f:if(condition: open, then: \'open\', else: \'closed\')}"' . "\n"
        . '        {f:if(condition: open, then: \'\', else: \'hidden\')}' . "\n"
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
    $listBase = normalizeClass(
        removeClassTokens($recipe['listBase'], ['justify-center'])
        . ' max-w-full justify-start overflow-x-auto overscroll-x-contain'
    );

    return $header
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n"
        . '<f:argument name="variant" type="string" optional="{true}" default="default" />' . "\n\n"
        . '<f:argument name="orientation" type="string" optional="{true}" default="horizontal" />' . "\n\n"
        . sprintf('<f:variable name="base" value="%s" />', attr($listBase)) . "\n\n"
        . renderSwitch('variant', $listRecipe)
        . "\n"
        . '<div role="tablist" aria-orientation="{orientation}" data-slot="tabs-list" data-variant="{variant}" class="{base} {variantClass -> f:format.trim()} {class}" data-d-tabs-list>' . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function renderTabsTrigger(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="value" type="string" />' . "\n"
        . '<f:argument name="id" type="string" />' . "\n"
        . '<f:argument name="controls" type="string" />' . "\n"
        . '<f:argument name="active" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<button' . "\n"
        . '    type="button"' . "\n"
        . '    role="tab"' . "\n"
        . '    id="{id}"' . "\n"
        . '    aria-controls="{controls}"' . "\n"
        . '    aria-selected="{f:if(condition: active, then: \'true\', else: \'false\')}"' . "\n"
        . '    tabindex="{f:if(condition: active, then: \'0\', else: \'-1\')}"' . "\n"
        . '    data-state="{f:if(condition: active, then: \'active\', else: \'inactive\')}"' . "\n"
        . sprintf('    class="%s {class}"', attr($recipe['trigger'])) . "\n"
        . '    data-slot="tabs-trigger"' . "\n"
        . '    data-d-tabs-trigger' . "\n"
        . '    data-value="{value}"' . "\n"
        . '    {f:if(condition: active, then: \'data-active\')}' . "\n"
        . '>' . "\n"
        . '    <f:slot />' . "\n"
        . '</button>' . "\n";
}

function renderTabsContent(array $recipe, string $header): string
{
    return $header
        . '<f:argument name="value" type="string" />' . "\n"
        . '<f:argument name="id" type="string" />' . "\n"
        . '<f:argument name="labelledBy" type="string" />' . "\n"
        . '<f:argument name="active" type="bool" optional="{true}" default="{false}" />' . "\n"
        . '<f:argument name="class" type="string" optional="{true}" default="" />' . "\n\n"
        . '<div' . "\n"
        . '    role="tabpanel"' . "\n"
        . '    id="{id}"' . "\n"
        . '    aria-labelledby="{labelledBy}"' . "\n"
        . '    data-state="{f:if(condition: active, then: \'active\', else: \'inactive\')}"' . "\n"
        . '    {f:if(condition: active, then: \'\', else: \'hidden\')}' . "\n"
        . '    tabindex="0"' . "\n"
        . sprintf('    class="%s {class}"', attr($recipe['content'])) . "\n"
        . '    data-slot="tabs-content"' . "\n"
        . '    data-d-tabs-content' . "\n"
        . '    data-value="{value}"' . "\n"
        . '>' . "\n"
        . '    <f:slot />' . "\n"
        . '</div>' . "\n";
}

function composeButtonClass(array $recipe, string $variant, string $size): string
{
    return normalizeClass(
        $recipe['base'] . ' '
        . ($recipe['variants'][$variant] ?? $recipe['variants']['default'] ?? '') . ' '
        . ($recipe['sizes'][$size] ?? $recipe['sizes']['default'] ?? '')
    );
}

function composeBadgeClass(array $recipe, string $variant, string $append = ''): string
{
    return normalizeClass(
        $recipe['base'] . ' '
        . ($recipe['variants'][$variant] ?? $recipe['variants']['default'] ?? '') . ' '
        . $append
    );
}

function composeTabsListClass(array $recipe, string $variant): string
{
    return normalizeClass(
        $recipe['listBase'] . ' '
        . ($recipe['listVariants'][$variant] ?? $recipe['listVariants']['default'] ?? '')
    );
}

function nativeCheckedClass(string $class): string
{
    return neutralizeNativeCheckedState(normalizeClass(strtr($class, [
        'aria-invalid:aria-checked:' => 'aria-invalid:checked:',
        'dark:data-checked:' => 'dark:checked:',
        'data-checked:' => 'checked:',
        'aria-checked:' => 'checked:',
    ])));
}

function neutralizeNativeCheckedState(string $class): string
{
    $map = [
        'aria-invalid:checked:border-primary' => 'aria-invalid:checked:border-destructive',
        'checked:border-primary' => 'checked:border-foreground',
        'checked:bg-primary' => 'checked:bg-foreground',
        'checked:text-primary-foreground' => 'checked:text-background',
        'dark:checked:bg-primary' => 'dark:checked:bg-foreground',
    ];

    $tokens = preg_split('/\s+/', trim($class)) ?: [];
    foreach ($tokens as $index => $token) {
        if (isset($map[$token])) {
            $tokens[$index] = $map[$token];
        }
    }

    return normalizeClass(implode(' ', $tokens));
}

/**
 * @param list<string> $tokensToRemove
 */
function removeClassTokens(string $class, array $tokensToRemove): string
{
    $tokens = preg_split('/\s+/', trim($class)) ?: [];
    $remove = array_flip($tokensToRemove);
    $tokens = array_values(array_filter($tokens, static fn (string $token): bool => !isset($remove[$token])));

    return normalizeClass(implode(' ', $tokens));
}

/**
 * Rewrite recipe class strings so component shape follows preset-switchable
 * tokens instead of the fetched style's frozen literals:
 *   - corner radius -> shadcn's --radius scale (rounded-md / -xl / -sm), which
 *     every preset already declares in shadcn-theme.css (radio stays
 *     rounded-full);
 *   - form-control height / text / inline padding -> the --d-control-* tokens;
 *   - focus-ring width -> the --d-ring-width token;
 *   - card surface elevation -> the --d-surface-shadow token.
 * Because the token VALUES live per preset in shadcn-theme.css, switching the
 * shadcn preset re-cascades component shape at runtime with no rebuild.
 *
 * @param array<string, mixed> $recipes
 * @return array<string, mixed>
 */
function tokenizeRecipes(array $recipes): array
{
    // (class, radiusKind, density, ring)
    $recipes['input'] = applyShapeTokens($recipes['input'], 'control', true, true);
    $recipes['textarea'] = applyShapeTokens($recipes['textarea'], 'control', true, true);
    $recipes['select']['trigger'] = applyShapeTokens($recipes['select']['trigger'], 'control', true, true);

    $recipes['button']['base'] = applyShapeTokens($recipes['button']['base'], 'control', true, true);
    foreach ($recipes['button']['sizes'] as $size => $value) {
        $recipes['button']['sizes'][$size] = applyShapeTokens($value, 'control', true, false);
    }

    $recipes['badge']['base'] = applyShapeTokens($recipes['badge']['base'], 'badge', false, false);

    foreach (['root', 'header', 'title', 'description', 'content', 'footer'] as $slot) {
        $recipes['card'][$slot] = applyShapeTokens($recipes['card'][$slot], 'card', false, false);
    }
    // Tokenized surface elevation: transparent by default, set per preset.
    $recipes['card']['root'] = normalizeClass($recipes['card']['root'] . ' shadow-[var(--d-surface-shadow)]');

    $recipes['checkbox']['root'] = applyShapeTokens($recipes['checkbox']['root'], 'checkbox', false, true);
    // Radio keeps rounded-full (no radius kind) but its focus ring is tokenized.
    $recipes['radio']['item'] = applyShapeTokens($recipes['radio']['item'], null, false, true);

    $recipes['tabs']['listBase'] = applyShapeTokens($recipes['tabs']['listBase'], 'control', false, false);
    foreach ($recipes['tabs']['listVariants'] as $variant => $value) {
        $recipes['tabs']['listVariants'][$variant] = applyShapeTokens($value, 'control', false, false);
    }
    $recipes['tabs']['trigger'] = applyShapeTokens($recipes['tabs']['trigger'], 'control', false, false);

    return $recipes;
}

/**
 * Token-aware shape rewrite for a single space-joined class string. Variant
 * prefixes (dark:, data-[size=default]:, *:…:) are preserved; arbitrary values
 * such as text-[length:var(--x)] are left intact.
 */
function applyShapeTokens(string $class, ?string $radiusKind, bool $density, bool $ring = false): string
{
    $canonicalRadius = [
        'control' => 'rounded-md',
        'card' => 'rounded-xl',
        'badge' => 'rounded-md',
        'checkbox' => 'rounded-sm',
    ][$radiusKind ?? ''] ?? null;

    $densityMap = [
        'h-8' => 'd-control-h',
        'text-xs' => 'd-control-text',
        'px-2.5' => 'd-control-px',
    ];

    // Focus-ring width literals -> preset-switchable token (ring colour is kept).
    $ringMap = [
        'ring-1' => 'ring-[var(--d-ring-width)]',
        'ring-2' => 'ring-[var(--d-ring-width)]',
        'ring-3' => 'ring-[var(--d-ring-width)]',
        'ring-[3px]' => 'ring-[var(--d-ring-width)]',
    ];

    $tokens = preg_split('/\s+/', trim($class)) ?: [];
    $out = [];

    foreach ($tokens as $token) {
        if ($token === '') {
            continue;
        }

        [$prefix, $base] = splitVariantPrefix($token);

        if (
            $canonicalRadius !== null
            && $base !== 'rounded-full'
            && preg_match('/^rounded(-(?:[a-z0-9]+|\[[^\]]+\]))?$/', $base) === 1
        ) {
            $out[] = $prefix . $canonicalRadius;
            continue;
        }

        if ($density && isset($densityMap[$base])) {
            $out[] = $prefix . $densityMap[$base];
            continue;
        }

        if ($ring && isset($ringMap[$base])) {
            $out[] = $prefix . $ringMap[$base];
            continue;
        }

        $out[] = $token;
    }

    return normalizeClass(implode(' ', $out));
}

/**
 * Split a Tailwind token into its variant prefix (up to and including the last
 * top-level ':') and the base utility. Colons inside [] or () are ignored so
 * arbitrary values are not mistaken for variant separators.
 *
 * @return array{0: string, 1: string}
 */
function splitVariantPrefix(string $token): array
{
    $depth = 0;
    $lastColon = -1;
    $length = strlen($token);

    for ($index = 0; $index < $length; ++$index) {
        $character = $token[$index];
        if ($character === '[' || $character === '(') {
            ++$depth;
        } elseif ($character === ']' || $character === ')') {
            --$depth;
        } elseif ($character === ':' && $depth === 0) {
            $lastColon = $index;
        }
    }

    if ($lastColon === -1) {
        return ['', $token];
    }

    return [substr($token, 0, $lastColon + 1), substr($token, $lastColon + 1)];
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
        '@desiderio' => $desiderioRegistry,
    ];

    return $componentsJson;
}

function syncSettingsYaml(string $settingsYaml, string $preset, string $style, string $iconLibrary): string
{
    $updated = replaceYamlSectionValue($settingsYaml, 'shadcn', 'preset', $preset);
    $updated = replaceYamlSectionValue($updated, 'shadcn', 'style', $style);
    $updated = replaceYamlSectionValue($updated, 'shadcn', 'iconLibrary', $iconLibrary);

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
    return keepTailwindSelectorsReadable(
        htmlspecialchars($value, ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8')
    );
}

function text(string $value): string
{
    return keepTailwindSelectorsReadable(
        htmlspecialchars($value, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8')
    );
}

function keepTailwindSelectorsReadable(string $value): string
{
    // Tailwind scans Fluid source, so encoded arbitrary selectors would not generate CSS.
    return str_replace(['&amp;', '&gt;'], ['&', '>'], $value);
}

function fail(string $message): never
{
    fwrite(STDERR, $message . "\n");
    exit(1);
}
