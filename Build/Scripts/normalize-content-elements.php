#!/usr/bin/env php
<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$root = dirname(__DIR__, 2);
$contentBlocksDir = $root . '/ContentBlocks/ContentElements';
$styleguideGroupsFile = $root . '/Resources/Private/Data/styleguide-content-groups.json';
$styleguideSeedFile = $root . '/Resources/Private/Data/styleguide-page-seed.json';
$backendPreviewCssFile = $root . '/Resources/Public/Css/content-preview.css';

$groupTitles = [
    'content' => 'Content',
    'conversion' => 'Conversion',
    'data' => 'Data Visualization',
    'features' => 'Feature Sections',
    'footer' => 'Footers',
    'hero' => 'Hero Sections',
    'navigation' => 'Navigation',
    'pricing' => 'Pricing',
    'social-proof' => 'Social Proof',
    'team' => 'Team',
];

$groupDescriptions = [
    'content' => ['content presentation', 'Inhaltsdarstellung'],
    'conversion' => ['conversion flow', 'Conversion-Flow'],
    'data' => ['data visualization', 'Datenvisualisierung'],
    'features' => ['feature section', 'Feature-Sektion'],
    'footer' => ['footer area', 'Footer-Bereich'],
    'hero' => ['hero section', 'Hero-Bereich'],
    'navigation' => ['navigation pattern', 'Navigationsmuster'],
    'pricing' => ['pricing section', 'Preisbereich'],
    'social-proof' => ['social proof section', 'Social-Proof-Sektion'],
    'team' => ['team presentation', 'Teamdarstellung'],
];

$germanWords = [
    'About' => 'Über',
    'Accessibility' => 'Barrierefreiheit',
    'Accordion' => 'Akkordeon',
    'Address' => 'Adresse',
    'Advisor' => 'Beratung',
    'Alert' => 'Hinweis',
    'Analytics' => 'Analyse',
    'Announcement' => 'Ankündigung',
    'Annual' => 'jährlich',
    'Article' => 'Artikel',
    'Audio' => 'Audio',
    'Awards' => 'Auszeichnungen',
    'Back' => 'Zurück',
    'Badge' => 'Badge',
    'Bar' => 'Balken',
    'Benefit' => 'Vorteils',
    'Billing' => 'Abrechnung',
    'Blog' => 'Blog',
    'Board' => 'Vorstand',
    'Booking' => 'Buchungs',
    'Brand' => 'Marken',
    'Breadcrumb' => 'Breadcrumb',
    'Bundle' => 'Paket',
    'Calculator' => 'Rechner',
    'Callback' => 'Rückruf',
    'Callout' => 'Callout',
    'Card' => 'Karten',
    'Carousel' => 'Karussell',
    'Case' => 'Case',
    'Category' => 'Kategorie',
    'Chart' => 'Diagramm',
    'Checklist' => 'Checklisten',
    'Clients' => 'Kunden',
    'Cloud' => 'Sammlung',
    'Code' => 'Code',
    'Columns' => 'Spalten',
    'Comparison' => 'Vergleich',
    'Contact' => 'Kontakt',
    'Content' => 'Inhalts',
    'Contribution' => 'Aktivitäts',
    'Cookie' => 'Cookie',
    'Countdown' => 'Countdown',
    'CTA' => 'Call-to-Action',
    'Customer' => 'Kunden',
    'Data' => 'Daten',
    'Definition' => 'Definitions',
    'Demo' => 'Demo',
    'Divider' => 'Trenner',
    'Download' => 'Download',
    'Embed' => 'Embed',
    'Event' => 'Event',
    'FAQ' => 'FAQ',
    'Feature' => 'Feature',
    'Filter' => 'Filter',
    'Footer' => 'Footer',
    'Form' => 'Formular',
    'Founder' => 'Gründer',
    'Gallery' => 'Galerie',
    'GDPR' => 'DSGVO',
    'Grid' => 'Raster',
    'Guide' => 'Guide',
    'Hero' => 'Hero',
    'Highlight' => 'Highlight',
    'How' => 'How-to',
    'Image' => 'Bild',
    'Inline' => 'Inline',
    'Insight' => 'Insight',
    'Interactive' => 'Interaktive',
    'KPI' => 'KPI',
    'Landing' => 'Landingpage',
    'Leaderboard' => 'Leaderboard',
    'Legal' => 'Rechtliche',
    'Line' => 'Linien',
    'Link' => 'Link',
    'List' => 'Liste',
    'Location' => 'Standort',
    'Logo' => 'Logo',
    'Map' => 'Karte',
    'Media' => 'Medien',
    'Member' => 'Mitglieder',
    'Menu' => 'Menü',
    'Metric' => 'Kennzahlen',
    'Minimal' => 'Minimal',
    'Milestone' => 'Meilenstein',
    'Newsletter' => 'Newsletter',
    'Office' => 'Büro',
    'Onboarding' => 'Onboarding',
    'Overlay' => 'Overlay',
    'Partner' => 'Partner',
    'Pie' => 'Kreis',
    'Plan' => 'Tarif',
    'Pricing' => 'Preis',
    'Privacy' => 'Datenschutz',
    'Process' => 'Prozess',
    'Product' => 'Produkt',
    'Profile' => 'Profil',
    'Progress' => 'Fortschritts',
    'Quote' => 'Zitat',
    'Radar' => 'Radar',
    'Rating' => 'Bewertungs',
    'Request' => 'Anfrage',
    'Review' => 'Bewertungs',
    'ROI' => 'ROI',
    'Search' => 'Such',
    'Security' => 'Sicherheits',
    'Selector' => 'Auswahl',
    'Service' => 'Service',
    'Sidebar' => 'Sidebar',
    'Signup' => 'Anmelde',
    'Social' => 'Social',
    'Sparkline' => 'Sparkline',
    'Stacked' => 'Gestapeltes',
    'Statement' => 'Erklärung',
    'Stats' => 'Kennzahlen',
    'Status' => 'Status',
    'Step' => 'Schritt',
    'Story' => 'Story',
    'Table' => 'Tabelle',
    'Tabs' => 'Tabs',
    'Team' => 'Team',
    'Teaser' => 'Teaser',
    'Testimonial' => 'Kundenstimme',
    'Textmedia' => 'Text mit Medien',
    'Timeline' => 'Timeline',
    'Top' => 'oben',
    'Trust' => 'Vertrauens',
    'Two' => 'Zwei',
    'Three' => 'Drei',
    'Four' => 'Vier',
    'To' => 'zu',
    'Us' => 'uns',
    'With' => 'mit',
    'Video' => 'Video',
    'Waitlist' => 'Wartelisten',
    'Wizard' => 'Wizard',
];

$manualTitles = [
    'about-us' => 'About the Company',
    'back-to-top' => 'Back to Top Button',
    'cta' => 'Call to Action',
    'cta-banner' => 'Banner Call to Action',
    'cta-card' => 'Card Call to Action',
    'cta-floating' => 'Floating Call to Action',
    'cta-gradient' => 'Gradient Call to Action',
    'cta-inline' => 'Inline Call to Action',
    'cta-minimal' => 'Minimal Call to Action',
    'cta-split' => 'Split Call to Action',
    'cta-with-image' => 'Image Call to Action',
    'faq' => 'FAQ',
    'gdpr-banner' => 'GDPR Banner',
    'hero-cta-only' => 'Call to Action Hero',
    'hero-dual-cta' => 'Dual Call to Action Hero',
    'hero-logo-cloud' => 'Logo Cloud Hero',
    'how-to-steps' => 'How-to Steps',
    'kpi-cards' => 'KPI Cards',
    'map-embed' => 'Map Embed',
    'mega-menu' => 'Mega Menu',
    'nav-toc' => 'Table of Contents Navigation',
    'org-chart' => 'Organization Chart',
    'roi-calculator' => 'ROI Calculator',
    'social-proof-counter' => 'Social Proof Counter',
    'textmedia' => 'Text & Media',
];

$acronyms = ['ai', 'api', 'cta', 'faq', 'gdpr', 'kpi', 'roi', 'seo', 'toc', 'ui', 'ux'];

$blocks = glob($contentBlocksDir . '/*', GLOB_ONLYDIR);
if ($blocks === false) {
    fwrite(STDERR, "Unable to read ContentBlocks directory.\n");
    exit(1);
}

sort($blocks);
$groups = [];
$seedGroups = [];

foreach ($blocks as $block) {
    $slug = basename($block);
    $configPath = $block . '/config.yaml';
    $config = Yaml::parseFile($configPath);
    if (!is_array($config)) {
        throw new RuntimeException(sprintf('Invalid config in %s', $configPath));
    }

    $group = (string)($config['group'] ?? 'content');
    $title = $manualTitles[$slug] ?? titleFromSlug($slug, $group, $acronyms);
    $description = descriptionFor($title, $group, $groupDescriptions, 'en');
    $germanTitle = germanTitle($title, $germanWords);
    $germanDescription = descriptionFor($germanTitle, $group, $groupDescriptions, 'de');

    $config['title'] = $title;
    $config['description'] = $description;

    $basics = $config['basics'] ?? [];
    if (!is_array($basics)) {
        $basics = [];
    }
    foreach (['TYPO3/Appearance', 'TYPO3/Links', 'TYPO3/Categories'] as $basic) {
        if (!in_array($basic, $basics, true)) {
            $basics[] = $basic;
        }
    }
    $config = moveKeyAfter($config, 'basics', $basics, 'prefixFields');

    file_put_contents($configPath, rtrim(Yaml::dump($config, 8, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK)) . "\n");
    file_put_contents($block . '/language/labels.xlf', xlf($slug, $title, $description));
    file_put_contents($block . '/language/de.labels.xlf', deXlf($slug, $title, $description, $germanTitle, $germanDescription));
    file_put_contents($block . '/assets/icon.svg', iconSvg($slug, $group));
    file_put_contents($block . '/templates/backend-preview.fluid.html', backendPreviewTemplate($title, $config));

    $typeName = (string)($config['typeName'] ?? ('desiderio_' . str_replace('-', '', $slug)));
    $groups[$group][] = [
        'name' => $title,
        'ctype' => $typeName,
    ];
}

foreach ($groupTitles as $groupId => $groupTitle) {
    $elements = $groups[$groupId] ?? [];
    usort($elements, static fn (array $a, array $b): int => strcmp((string)$a['name'], (string)$b['name']));
    $seedElements = [];
    foreach ($elements as $element) {
        $seedElements[] = [
            'name' => $element['name'],
            'ctype' => $element['ctype'],
        ];
    }

    $seedGroups[] = [
        'groupId' => $groupId,
        'groupTitle' => $groupTitle,
        'pageTitle' => 'Desiderio ' . $groupTitle,
        'elements' => $seedElements,
    ];
}

file_put_contents($styleguideGroupsFile, json_encode($seedGroups, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
file_put_contents($styleguideSeedFile, json_encode([
    'parentPid' => 505,
    'description' => 'Styleguide seed manifest for creating one TYPO3 test page per content element wizard category below page id 505.',
    'groups' => $seedGroups,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
file_put_contents($backendPreviewCssFile, backendPreviewCss());

printf("Normalized %d Desiderio Content Blocks.\n", count($blocks));

/**
 * @param list<string> $acronyms
 */
function titleFromSlug(string $slug, string $group, array $acronyms): string
{
    $parts = explode('-', $slug);
    $first = $parts[0] ?? '';
    $tail = array_slice($parts, 1);

    if ($first === 'hero') {
        return $tail === [] ? 'Hero Section' : humanTitle($tail, $acronyms) . ' Hero';
    }
    if ($first === 'footer') {
        return $tail === [] ? 'Footer' : humanTitle($tail, $acronyms) . ' Footer';
    }
    if ($first === 'navbar') {
        return $tail === [] ? 'Navigation Bar' : humanTitle($tail, $acronyms) . ' Navigation Bar';
    }
    if ($first === 'nav') {
        return $tail === [] ? 'Navigation' : humanTitle($tail, $acronyms) . ' Navigation';
    }
    if ($first === 'chart') {
        return $tail === [] ? 'Chart' : humanTitle($tail, $acronyms) . ' Chart';
    }
    if ($first === 'pricing') {
        return $tail === [] ? 'Pricing Section' : humanTitle($tail, $acronyms) . ' Pricing';
    }
    if ($first === 'feature') {
        if (($tail[0] ?? '') === 'grid' && isset($tail[1]) && is_numeric($tail[1])) {
            return $tail[1] . '-Column Feature Grid';
        }

        return $tail === [] ? 'Feature Section' : humanTitle($tail, $acronyms) . ' Feature Section';
    }
    if ($first === 'testimonial') {
        return $tail === [] ? 'Testimonial' : humanTitle($tail, $acronyms) . ' Testimonial';
    }
    if ($first === 'team') {
        return $tail === [] ? 'Team Section' : humanTitle($tail, $acronyms) . ' Team Section';
    }
    if ($first === 'stats') {
        return $tail === [] ? 'Stats Section' : humanTitle($tail, $acronyms) . ' Stats';
    }
    if ($first === 'content') {
        return $tail === [] ? 'Content Section' : 'Content ' . humanTitle($tail, $acronyms);
    }

    $title = humanTitle($parts, $acronyms);

    if ($group === 'pricing' && !str_contains($title, 'Pricing') && !str_contains($title, 'Plan')) {
        return $title . ' Pricing';
    }
    if ($group === 'data' && !preg_match('/(Chart|Table|Metric|KPI|Stats|Analytics|Leaderboard|Sparkline|Radar|Map|Counter)/', $title)) {
        return $title . ' Data View';
    }

    return $title;
}

/**
 * @param list<string> $parts
 * @param list<string> $acronyms
 */
function humanTitle(array $parts, array $acronyms): string
{
    $smallWords = ['and', 'as', 'for', 'in', 'of', 'on', 'or', 'to', 'with'];
    $words = [];

    foreach ($parts as $index => $part) {
        if ($part === '') {
            continue;
        }

        if (in_array($part, $acronyms, true)) {
            $words[] = strtoupper($part);
            continue;
        }

        if (is_numeric($part)) {
            $words[] = $part;
            continue;
        }

        if ($index > 0 && in_array($part, $smallWords, true)) {
            $words[] = $part;
            continue;
        }

        $words[] = ucfirst($part);
    }

    return implode(' ', $words);
}

/**
 * @param array<string, array{0: string, 1: string}> $groupDescriptions
 */
function descriptionFor(string $title, string $group, array $groupDescriptions, string $language): string
{
    [$englishGroup, $germanGroup] = $groupDescriptions[$group] ?? $groupDescriptions['content'];
    if ($language === 'de') {
        return sprintf('Ein shadcn/ui gestaltetes TYPO3 Inhaltselement für %s mit Theme-Tokens für Hell- und Dunkelmodus.', $germanGroup);
    }

    return sprintf('A shadcn/ui styled TYPO3 content element for %s, using theme tokens for light and dark mode.', $englishGroup);
}

/**
 * @param array<string, string> $dictionary
 */
function germanTitle(string $title, array $dictionary): string
{
    $parts = explode(' ', $title);
    $translated = [];
    foreach ($parts as $part) {
        $translated[] = $dictionary[$part] ?? $part;
    }

    return trim(implode(' ', $translated));
}

/**
 * @param array<string, mixed> $config
 * @return array<string, mixed>
 */
function moveKeyAfter(array $config, string $key, mixed $value, string $after): array
{
    unset($config[$key]);
    $result = [];
    foreach ($config as $currentKey => $currentValue) {
        $result[$currentKey] = $currentValue;
        if ($currentKey === $after) {
            $result[$key] = $value;
        }
    }
    if (!array_key_exists($key, $result)) {
        $result[$key] = $value;
    }

    return $result;
}

/**
 * @param array<string, mixed> $config
 */
function backendPreviewTemplate(string $title, array $config): string
{
    $fields = previewFields($config['fields'] ?? []);
    $titleField = preferredField($fields, ['header', 'headline', 'title', 'name', 'label', 'brand_name']);
    $scalarFields = previewScalarFields($fields, $titleField);
    $fileFields = previewFieldsOfType($fields, 'File');
    $collectionFields = previewFieldsOfType($fields, 'Collection');

    $lines = [
        '<html',
        '    xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"',
        '    xmlns:cb="http://typo3.org/ns/TYPO3/CMS/ContentBlocks/ViewHelpers"',
        '    data-namespace-typo3-fluid="true"',
        '>',
        '',
        '<f:layout name="Preview"/>',
        '',
        '<f:section name="Header">',
    ];

    if ($titleField !== null) {
        $lines[] = '    <f:if condition="{data.' . $titleField . '}">';
        $lines[] = '        <f:then><cb:link.editRecord uid="{data.uid}" table="{data.mainType}">{data.' . $titleField . '}</cb:link.editRecord></f:then>';
        $lines[] = '        <f:else><cb:link.editRecord uid="{data.uid}" table="{data.mainType}">' . xml($title) . '</cb:link.editRecord></f:else>';
        $lines[] = '    </f:if>';
    } else {
        $lines[] = '    <cb:link.editRecord uid="{data.uid}" table="{data.mainType}">' . xml($title) . '</cb:link.editRecord>';
    }

    $lines = [
        ...$lines,
        '</f:section>',
        '',
        '<f:section name="Content">',
        '    <f:asset.css identifier="desiderio-content-preview" href="EXT:desiderio/Resources/Public/Css/content-preview.css" />',
        '    <div class="d-ce-preview" data-slot="card">',
        '        <div class="d-ce-preview__meta">',
        '            <span class="d-ce-preview__type" data-slot="badge">' . xml($title) . '</span>',
        '            <f:if condition="{settings._content_block_name}"><span class="d-ce-preview__ctype">{settings._content_block_name}</span></f:if>',
        '        </div>',
    ];

    if ($titleField !== null) {
        $lines[] = '        <f:if condition="{data.' . $titleField . '}">';
        $lines[] = '            <h3 class="d-ce-preview__title">{data.' . $titleField . '}</h3>';
        $lines[] = '        </f:if>';
    }

    foreach ($scalarFields as $field) {
        $identifier = (string)$field['identifier'];
        $label = readableIdentifier($identifier);
        $lines[] = '        <f:if condition="{data.' . $identifier . '}">';
        $lines[] = '            <div class="d-ce-preview__field">';
        $lines[] = '                <span class="d-ce-preview__label">' . xml($label) . '</span>';
        $lines[] = '                <span class="d-ce-preview__value">{data.' . $identifier . '}</span>';
        $lines[] = '            </div>';
        $lines[] = '        </f:if>';
    }

    foreach ($fileFields as $field) {
        $identifier = (string)$field['identifier'];
        $lines[] = '        <f:if condition="{data.' . $identifier . '}">';
        $lines[] = '            <div class="d-ce-preview__thumbs" aria-label="' . xml(readableIdentifier($identifier)) . '">';
        $lines[] = '                <f:for each="{data.' . $identifier . '}" as="file">';
        $lines[] = '                    <f:if condition="{file.publicUrl}">';
        $lines[] = '                        <img src="{file.publicUrl}" alt="{file.alternative}" class="d-ce-preview__thumb" loading="lazy" />';
        $lines[] = '                    </f:if>';
        $lines[] = '                </f:for>';
        $lines[] = '            </div>';
        $lines[] = '        </f:if>';
    }

    foreach ($collectionFields as $field) {
        $identifier = (string)$field['identifier'];
        $children = previewScalarFields(previewFields($field['fields'] ?? []), null);
        $children = array_slice($children, 0, 3);
        $lines[] = '        <f:if condition="{data.' . $identifier . '}">';
        $lines[] = '            <div class="d-ce-preview__collection">';
        $lines[] = '                <span class="d-ce-preview__label">' . xml(readableIdentifier($identifier)) . '</span>';
        $lines[] = '                <ul class="d-ce-preview__list">';
        $lines[] = '                    <f:for each="{data.' . $identifier . '}" as="item">';
        $lines[] = '                        <li>';

        if ($children === []) {
            $lines[] = '                            <span>' . xml(readableIdentifier($identifier)) . ' item</span>';
        } else {
            foreach ($children as $child) {
                $childIdentifier = (string)$child['identifier'];
                $lines[] = '                            <f:if condition="{item.' . $childIdentifier . '}"><span>{item.' . $childIdentifier . '}</span></f:if>';
            }
        }

        $lines[] = '                        </li>';
        $lines[] = '                    </f:for>';
        $lines[] = '                </ul>';
        $lines[] = '            </div>';
        $lines[] = '        </f:if>';
    }

    if ($titleField === null && $scalarFields === [] && $fileFields === [] && $collectionFields === []) {
        $lines[] = '        <p class="d-ce-preview__empty">Configured content element preview.</p>';
    }

    $lines = [
        ...$lines,
        '    </div>',
        '</f:section>',
        '',
        '</html>',
    ];

    return implode("\n", $lines) . "\n";
}

/**
 * @param mixed $fields
 * @return list<array<string, mixed>>
 */
function previewFields(mixed $fields): array
{
    if (!is_array($fields)) {
        return [];
    }

    $result = [];
    foreach ($fields as $field) {
        if (!is_array($field) || !isset($field['identifier']) || !is_string($field['identifier'])) {
            continue;
        }
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $field['identifier'])) {
            continue;
        }
        $result[] = $field;
    }

    return $result;
}

/**
 * @param list<array<string, mixed>> $fields
 * @param list<string> $candidates
 */
function preferredField(array $fields, array $candidates): ?string
{
    $indexed = [];
    foreach ($fields as $field) {
        $indexed[(string)$field['identifier']] = true;
    }
    foreach ($candidates as $candidate) {
        if (isset($indexed[$candidate])) {
            return $candidate;
        }
    }

    return null;
}

/**
 * @param list<array<string, mixed>> $fields
 * @return list<array<string, mixed>>
 */
function previewFieldsOfType(array $fields, string $type): array
{
    return array_values(array_filter(
        $fields,
        static fn (array $field): bool => (string)($field['type'] ?? '') === $type
    ));
}

/**
 * @param list<array<string, mixed>> $fields
 * @return list<array<string, mixed>>
 */
function previewScalarFields(array $fields, ?string $titleField): array
{
    $scalars = array_filter($fields, static function (array $field) use ($titleField): bool {
        $identifier = (string)$field['identifier'];
        $type = (string)($field['type'] ?? 'Textarea');
        if ($identifier === $titleField) {
            return false;
        }
        if (in_array($type, ['Collection', 'File', 'Link', 'Checkbox'], true)) {
            return false;
        }

        return true;
    });

    usort($scalars, static fn (array $a, array $b): int => previewFieldPriority((string)$a['identifier']) <=> previewFieldPriority((string)$b['identifier']));

    return array_slice(array_values($scalars), 0, 6);
}

function previewFieldPriority(string $identifier): int
{
    $normalized = str_replace('_', '', strtolower($identifier));

    return match (true) {
        str_contains($normalized, 'eyebrow') || str_contains($normalized, 'badge') || str_contains($normalized, 'kicker') => 10,
        str_contains($normalized, 'subheadline') || str_contains($normalized, 'description') || str_contains($normalized, 'summary') || str_contains($normalized, 'intro') || str_contains($normalized, 'lead') => 20,
        str_contains($normalized, 'quote') || str_contains($normalized, 'content') || str_contains($normalized, 'body') || str_contains($normalized, 'copy') => 30,
        str_contains($normalized, 'value') || str_contains($normalized, 'price') || str_contains($normalized, 'count') || str_contains($normalized, 'rating') => 40,
        str_contains($normalized, 'chartdata') || str_contains($normalized, 'rowdata') || str_contains($normalized, 'period') || str_contains($normalized, 'date') => 50,
        str_contains($normalized, 'buttontext') || str_contains($normalized, 'ctatext') || str_contains($normalized, 'label') => 60,
        default => 90,
    };
}

function readableIdentifier(string $identifier): string
{
    $label = ucwords(str_replace('_', ' ', $identifier));
    $label = str_replace(
        ['Cta', 'Kpi', 'Seo', 'Gdpr', 'Faq', 'Url'],
        ['Call to Action', 'KPI', 'SEO', 'GDPR', 'FAQ', 'URL'],
        $label
    );

    return $label;
}

function xlf(string $productName, string $title, string $description): string
{
    return sprintf(
        <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" datatype="plaintext" original="messages" product-name="%s">
        <header/>
        <body>
            <trans-unit id="title">
                <source>%s</source>
            </trans-unit>
            <trans-unit id="description">
                <source>%s</source>
            </trans-unit>
        </body>
    </file>
</xliff>
XML,
        xml($productName),
        xml($title),
        xml($description)
    ) . "\n";
}

function deXlf(string $productName, string $title, string $description, string $germanTitle, string $germanDescription): string
{
    return sprintf(
        <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<xliff version="1.2" xmlns="urn:oasis:names:tc:xliff:document:1.2">
    <file source-language="en" target-language="de" datatype="plaintext" original="messages" product-name="%s">
        <header/>
        <body>
            <trans-unit id="title">
                <source>%s</source>
                <target>%s</target>
            </trans-unit>
            <trans-unit id="description">
                <source>%s</source>
                <target>%s</target>
            </trans-unit>
        </body>
    </file>
</xliff>
XML,
        xml($productName),
        xml($title),
        xml($germanTitle),
        xml($description),
        xml($germanDescription)
    ) . "\n";
}

function xml(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function iconSvg(string $slug, string $group): string
{
    $keywords = $slug . ' ' . $group;
    $shape = match (true) {
        str_contains($keywords, 'chart') || str_contains($keywords, 'data') || str_contains($keywords, 'metric') || str_contains($keywords, 'stats') => '<path d="M3.5 12.5h9"/><path d="M4.5 10v2.5"/><path d="M8 7.5v5"/><path d="M11.5 5v7.5"/><circle cx="12.5" cy="3.5" r="1.5" class="accent"/>',
        str_contains($keywords, 'form') || str_contains($keywords, 'newsletter') || str_contains($keywords, 'contact') || str_contains($keywords, 'booking') || str_contains($keywords, 'request') => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5.25 6h5.5"/><path d="M5.25 8.5h3.5"/><circle cx="11.5" cy="11.5" r="1.5" class="accent"/>',
        str_contains($keywords, 'navigation') || str_contains($keywords, 'navbar') || str_contains($keywords, 'menu') || str_contains($keywords, 'breadcrumb') => '<path d="M3 4.5h10"/><path d="M3 8h10"/><path d="M3 11.5h7"/><circle cx="12.25" cy="11.5" r="1.25" class="accent"/>',
        str_contains($keywords, 'hero') => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="M5 6.25h5.5"/><path d="M5 8.5h3.25"/><circle cx="11.5" cy="5.25" r="1.25" class="accent"/>',
        str_contains($keywords, 'footer') => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5 6h6"/><path d="M5 8.25h3"/><rect x="5" y="10.25" width="6" height="1.25" rx=".625" class="accent no-stroke"/>',
        str_contains($keywords, 'pricing') || str_contains($keywords, 'plan') || str_contains($keywords, 'billing') => '<rect x="3" y="2.75" width="10" height="10.5" rx="2"/><path d="M5.5 6h4"/><path d="M5.5 8h5"/><circle cx="10.75" cy="10.75" r="1.25" class="accent"/>',
        str_contains($keywords, 'team') || str_contains($keywords, 'member') || str_contains($keywords, 'profile') || str_contains($keywords, 'founder') => '<circle cx="6" cy="5.25" r="2"/><path d="M2.75 12.5c.55-2 1.7-3 3.25-3s2.7 1 3.25 3"/><circle cx="11.25" cy="6.5" r="1.5" class="accent"/>',
        str_contains($keywords, 'testimonial') || str_contains($keywords, 'review') || str_contains($keywords, 'quote') || str_contains($keywords, 'social-proof') || str_contains($keywords, 'award') => '<path d="M4 5.25h7.5a1.5 1.5 0 0 1 1.5 1.5v3.5a1.5 1.5 0 0 1-1.5 1.5H7l-3 2v-2.5a1.5 1.5 0 0 1-1.5-1.5v-3A1.5 1.5 0 0 1 4 5.25z"/><path d="m9.75 2.75.45.9 1 .15-.72.7.17 1-.9-.48-.9.48.17-1-.72-.7 1-.15z" class="accent"/>',
        str_contains($keywords, 'media') || str_contains($keywords, 'image') || str_contains($keywords, 'gallery') || str_contains($keywords, 'video') || str_contains($keywords, 'audio') => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="m5.25 10 2-2 1.5 1.5 1-1 2 2.5"/><circle cx="10.75" cy="5.75" r="1" class="accent"/>',
        str_contains($keywords, 'alert') || str_contains($keywords, 'callout') || str_contains($keywords, 'status') || str_contains($keywords, 'security') || str_contains($keywords, 'legal') || str_contains($keywords, 'privacy') || str_contains($keywords, 'gdpr') || str_contains($keywords, 'accessibility') => '<path d="M8 2.75 13.5 12H2.5z"/><path d="M8 6v2.5"/><circle cx="8" cy="10.5" r=".55" class="accent no-stroke"/>',
        default => '<rect x="3" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="3" y="8.75" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="8.75" width="4.25" height="4.25" rx="1.25" class="accent"/>',
    };

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
  <style>.accent{stroke:var(--icon-color-accent,currentColor);fill:none}.no-stroke{stroke:none;fill:var(--icon-color-accent,currentColor)}</style>
  {$shape}
</svg>
SVG . "\n";
}

function backendPreviewCss(): string
{
    return <<<'CSS'
.d-ce-preview {
  display: flex;
  flex-direction: column;
  gap: .75rem;
  color: var(--typo3-component-color, inherit);
  background: var(--typo3-component-bg, transparent);
  --d-ce-preview-border: var(--typo3-component-border-color, var(--border, currentColor));
  --d-ce-preview-border-muted: color-mix(in oklch, var(--d-ce-preview-border) 70%, transparent);
  border: 1px solid var(--d-ce-preview-border);
  border-radius: .5rem;
  padding: .875rem;
  box-shadow: var(--typo3-component-box-shadow, none);
}

.d-ce-preview__meta,
.d-ce-preview__thumbs {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
  align-items: center;
}

.d-ce-preview__type,
.d-ce-preview__ctype {
  display: inline-flex;
  align-items: center;
  max-width: 100%;
  border: 1px solid var(--d-ce-preview-border);
  border-radius: 999px;
  padding: .125rem .5rem;
  font-size: .6875rem;
  font-weight: 700;
  line-height: 1.4;
}

.d-ce-preview__type {
  color: var(--typo3-state-info-color, currentColor);
  background: var(--typo3-state-info-bg, transparent);
  text-transform: uppercase;
}

.d-ce-preview__ctype {
  color: var(--typo3-component-color-muted, currentColor);
  font-family: var(--typo3-font-family-monospace, monospace);
  font-weight: 500;
}

.d-ce-preview__title {
  margin: 0;
  color: var(--typo3-component-color, inherit);
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.35;
}

.d-ce-preview__field,
.d-ce-preview__collection {
  display: grid;
  gap: .25rem;
}

.d-ce-preview__label {
  color: var(--typo3-component-color-muted, currentColor);
  font-size: .6875rem;
  font-weight: 700;
  letter-spacing: 0;
  text-transform: uppercase;
}

.d-ce-preview__value {
  color: var(--typo3-component-color, inherit);
  line-height: 1.45;
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

.d-ce-preview__thumb {
  width: 4.25rem;
  height: 4.25rem;
  object-fit: cover;
  border: 1px solid var(--d-ce-preview-border);
  border-radius: .375rem;
}

.d-ce-preview__list {
  display: grid;
  gap: .375rem;
  margin: 0;
  padding: 0;
  list-style: none;
}

.d-ce-preview__list li {
  display: flex;
  flex-wrap: wrap;
  gap: .35rem .5rem;
  align-items: baseline;
  border: 1px solid var(--d-ce-preview-border-muted);
  border-radius: .375rem;
  padding: .5rem .625rem;
}

.d-ce-preview__list span:first-child {
  font-weight: 700;
}

.d-ce-preview__empty {
  margin: 0;
  color: var(--typo3-component-color-muted, currentColor);
}
CSS . "\n";
}
