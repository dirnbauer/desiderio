#!/usr/bin/env php
<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$root = dirname(__DIR__, 2);
$contentBlocksDir = $root . '/ContentBlocks/ContentElements';
$styleguideGroupsFile = $root . '/Resources/Private/Data/styleguide-content-groups.json';
$styleguideSeedFile = $root . '/Resources/Private/Data/styleguide-page-seed.json';

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
    'faq' => 'FAQ',
    'cta-banner' => 'Call to Action Banner',
    'gdpr-consent' => 'GDPR Consent',
    'roi-calculator' => 'ROI Calculator',
];

$acronyms = ['api', 'cta', 'faq', 'gdpr', 'kpi', 'roi', 'seo'];

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

printf("Normalized %d Desiderio Content Blocks.\n", count($blocks));

/**
 * @param list<string> $acronyms
 */
function titleFromSlug(string $slug, string $group, array $acronyms): string
{
    $words = array_map(static function (string $word) use ($acronyms): string {
        return in_array($word, $acronyms, true) ? strtoupper($word) : ucfirst($word);
    }, explode('-', $slug));

    if ($words === ['Cta']) {
        return 'Call to Action';
    }

    $title = implode(' ', $words);
    $title = str_replace('Cta ', 'Call to Action ', $title);

    if ($group === 'hero' && !str_ends_with($title, 'Hero') && !str_starts_with($title, 'Hero ')) {
        return $title . ' Hero';
    }
    if ($group === 'footer' && !str_ends_with($title, 'Footer')) {
        return $title . ' Footer';
    }
    if ($group === 'navigation' && str_starts_with($title, 'Navbar')) {
        return trim(str_replace('Navbar', 'Navigation Bar', $title));
    }
    if ($group === 'pricing' && !str_contains($title, 'Pricing') && !str_contains($title, 'Plan')) {
        return $title . ' Pricing';
    }
    if ($group === 'data' && !preg_match('/(Chart|Table|Metric|KPI|Stats|Analytics|Leaderboard|Sparkline|Radar|Map)/', $title)) {
        return $title . ' Data View';
    }

    return $title;
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
  <style>.accent{stroke:var(--icon-color-accent,#ff8700);fill:none}.no-stroke{stroke:none;fill:var(--icon-color-accent,#ff8700)}</style>
  {$shape}
</svg>
SVG . "\n";
}
