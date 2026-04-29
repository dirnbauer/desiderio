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
$sharedLabelsFile = $root . '/Resources/Private/Language/labels.xlf';
$sharedGermanLabelsFile = $root . '/Resources/Private/Language/de.labels.xlf';
$contentElementGroupTcaFile = $root . '/Configuration/TCA/Overrides/tt_content.php';

$groupMeta = [
    'content' => [
        'title' => 'Content & Editorial',
        'deTitle' => 'Inhalte & Redaktion',
        'summary' => 'Text, media, stories, resources, and reusable editorial building blocks.',
        'deSummary' => 'Text, Medien, Storys, Ressourcen und wiederverwendbare redaktionelle Bausteine.',
    ],
    'conversion' => [
        'title' => 'Leads & Conversion',
        'deTitle' => 'Leads & Conversion',
        'summary' => 'Forms, offers, calls to action, and focused steps toward visitor intent.',
        'deSummary' => 'Formulare, Angebote, Call-to-Actions und fokussierte Schritte zur Besucheraktion.',
    ],
    'data' => [
        'title' => 'Data & Dashboards',
        'deTitle' => 'Daten & Dashboards',
        'summary' => 'Charts, metrics, tables, KPIs, and structured data displays.',
        'deSummary' => 'Diagramme, Kennzahlen, Tabellen, KPIs und strukturierte Datendarstellungen.',
    ],
    'features' => [
        'title' => 'Features & Benefits',
        'deTitle' => 'Funktionen & Vorteile',
        'summary' => 'Feature sections, benefit grids, product explanations, and service highlights.',
        'deSummary' => 'Feature-Sektionen, Vorteilsraster, Produkterklärungen und Service-Highlights.',
    ],
    'footer' => [
        'title' => 'Footers & Utility Areas',
        'deTitle' => 'Footer & Servicebereiche',
        'summary' => 'Footer layouts, legal links, contact routes, app links, and page-end utility content.',
        'deSummary' => 'Footer-Layouts, rechtliche Links, Kontaktwege, App-Links und Serviceinhalte am Seitenende.',
    ],
    'hero' => [
        'title' => 'Hero & Landing Intros',
        'deTitle' => 'Hero & Seiteneinstiege',
        'summary' => 'Opening sections for landing pages, products, campaigns, search, video, and launch moments.',
        'deSummary' => 'Einstiegssektionen für Landingpages, Produkte, Kampagnen, Suche, Video und Launch-Momente.',
    ],
    'navigation' => [
        'title' => 'Navigation & Wayfinding',
        'deTitle' => 'Navigation & Orientierung',
        'summary' => 'Menus, breadcrumbs, tabs, steps, pagination, headers, and other orientation patterns.',
        'deSummary' => 'Menüs, Breadcrumbs, Tabs, Schritte, Paginierung, Header und weitere Orientierungsmuster.',
    ],
    'pricing' => [
        'title' => 'Plans & Pricing',
        'deTitle' => 'Tarife & Preise',
        'summary' => 'Pricing tables, plan cards, calculators, package comparisons, and buying decision aids.',
        'deSummary' => 'Preistabellen, Tarifkarten, Rechner, Paketvergleiche und Entscheidungshilfen für den Kauf.',
    ],
    'social-proof' => [
        'title' => 'Trust & Social Proof',
        'deTitle' => 'Vertrauen & Referenzen',
        'summary' => 'Testimonials, reviews, ratings, customer logos, partner proof, and credibility signals.',
        'deSummary' => 'Testimonials, Bewertungen, Ratings, Kundenlogos, Partnernachweise und Vertrauenssignale.',
    ],
    'team' => [
        'title' => 'People & Team',
        'deTitle' => 'Menschen & Team',
        'summary' => 'Team profiles, departments, founders, advisors, company culture, and career content.',
        'deSummary' => 'Teamprofile, Abteilungen, Gründer, Beratung, Unternehmenskultur und Karriereinhalte.',
    ],
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
    $description = descriptionFor($slug, $title, $group, $config, 'en');
    $germanTitle = germanTitle($title, $germanWords);
    $germanDescription = descriptionFor($slug, $germanTitle, $group, $config, 'de');

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
    file_put_contents($block . '/assets/icon.svg', iconSvg($slug, $group, $title));
    file_put_contents($block . '/templates/backend-preview.fluid.html', backendPreviewTemplate($title, $config));

    $typeName = (string)($config['typeName'] ?? ('desiderio_' . str_replace('-', '', $slug)));
    $groups[$group][] = [
        'name' => $title,
        'ctype' => $typeName,
    ];
}

foreach ($groupMeta as $groupId => $meta) {
    $groupTitle = $meta['title'];
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
writeSharedLabelFiles($sharedLabelsFile, $sharedGermanLabelsFile, $groupMeta);
writeContentElementGroupTca($contentElementGroupTcaFile, array_keys($groupMeta));

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
 * @param array<string, mixed> $config
 */
function descriptionFor(string $slug, string $title, string $group, array $config, string $language): string
{
    $purpose = purposeSentence($slug, $title, $group, $language);
    $capabilities = capabilitySentence($config, $language);

    return trim($purpose . ' ' . $capabilities);
}

function purposeSentence(string $slug, string $title, string $group, string $language): string
{
    $keywords = strtolower(str_replace('-', ' ', $slug . ' ' . $group . ' ' . $title));

    if ($language === 'de') {
        return match (true) {
            containsAny($keywords, ['accordion', 'faq']) => sprintf('%s bündelt Antworten oder gegliederte Informationen in einem kompakten aufklappbaren Bereich.', $title),
            containsAny($keywords, ['alert', 'notification', 'callout', 'status', 'emergency']) => sprintf('%s hebt dringende Hinweise, Statusmeldungen oder wichtige Kontextinformationen sichtbar hervor.', $title),
            containsAny($keywords, ['analytics', 'kpi', 'metric', 'stats', 'counter', 'leaderboard', 'dashboard']) => sprintf('%s zeigt Kennzahlen und Leistungsdaten als schnell erfassbaren Dashboard-Baustein.', $title),
            containsAny($keywords, ['area chart', 'bar chart', 'donut chart', 'heatmap', 'line chart', 'pie chart', 'radar', 'sparkline', 'stacked bar']) => sprintf('%s übersetzt Daten in eine konkrete Diagrammform mit klarem visuellen Schwerpunkt.', $title),
            containsAny($keywords, ['table', 'comparison', 'compare']) => sprintf('%s strukturiert Inhalte für klare Vergleiche, Listen, Zeilen oder tabellarische Details.', $title),
            containsAny($keywords, ['form', 'signup', 'request', 'booking', 'contact', 'waitlist', 'newsletter', 'demo', 'callback', 'download form']) => sprintf('%s sammelt Besucherinteresse mit fokussierter Formular- oder Anfragekommunikation.', $title),
            containsAny($keywords, ['pricing', 'plan', 'billing', 'bundle', 'calculator', 'order summary']) => sprintf('%s unterstützt Kaufentscheidungen mit Tarifen, Preisen, Paketen oder berechneten Optionen.', $title),
            containsAny($keywords, ['testimonial', 'review', 'quote', 'rating', 'award', 'trust', 'client', 'customer', 'logo', 'partner', 'certification', 'press']) => sprintf('%s baut Vertrauen über Stimmen, Logos, Bewertungen, Auszeichnungen oder Referenzen auf.', $title),
            containsAny($keywords, ['team', 'member', 'founder', 'advisor', 'board', 'profile', 'culture', 'career', 'job', 'office', 'org chart', 'company values', 'company perks']) => sprintf('%s stellt Menschen, Rollen, Kultur oder Karriereinformationen nachvollziehbar vor.', $title),
            containsAny($keywords, ['navigation', 'navbar', 'menu', 'breadcrumb', 'toc', 'pagination', 'tabs', 'steps', 'search', 'back to top', 'utility bar']) => sprintf('%s hilft Besucherinnen und Besuchern, sich mit Links, Menüs, Ankern oder Suchzugängen zu orientieren.', $title),
            containsAny($keywords, ['footer', 'copyright', 'legal', 'privacy', 'cookie', 'gdpr', 'accessibility', 'imprint', 'terms']) => sprintf('%s schließt Seiten mit Serviceinformationen, rechtlichen Links oder Kontaktwegen ab.', $title),
            containsAny($keywords, ['hero']) => sprintf('%s eröffnet Landingpages, Kampagnen oder Produktseiten mit einer starken ersten Botschaft.', $title),
            containsAny($keywords, ['feature', 'benefit', 'service', 'product', 'use case']) => sprintf('%s erklärt Produktnutzen, Funktionen oder Anwendungsfälle in einer klaren Inhaltsstruktur.', $title),
            containsAny($keywords, ['gallery', 'image', 'video', 'audio', 'media', 'embed', 'map']) => sprintf('%s kombiniert Medien oder eingebettete Inhalte mit begleitender redaktioneller Aussage.', $title),
            containsAny($keywords, ['timeline', 'milestone', 'history', 'process', 'how to', 'onboarding', 'progress', 'changelog']) => sprintf('%s macht Abfolgen, Fortschritt, Historie oder Prozessschritte leichter verständlich.', $title),
            containsAny($keywords, ['card', 'grid', 'list', 'library', 'sitemap', 'category']) => sprintf('%s ordnet mehrere Inhalte als scanbare Karten, Raster oder Bibliothek.', $title),
            default => sprintf('%s liefert einen eigenständigen redaktionellen Baustein für eine klar erkennbare Seitenaufgabe.', $title),
        };
    }

    return match (true) {
        containsAny($keywords, ['accordion', 'faq']) => sprintf('%s turns layered answers or grouped information into a compact expandable block.', $title),
        containsAny($keywords, ['alert', 'notification', 'callout', 'status', 'emergency']) => sprintf('%s highlights urgent notices, status updates, or important contextual messages.', $title),
        containsAny($keywords, ['analytics', 'kpi', 'metric', 'stats', 'counter', 'leaderboard', 'dashboard']) => sprintf('%s presents performance numbers as a quick-read dashboard block.', $title),
        containsAny($keywords, ['area chart', 'bar chart', 'donut chart', 'heatmap', 'line chart', 'pie chart', 'radar', 'sparkline', 'stacked bar']) => sprintf('%s turns data into a specific chart treatment with a clear visual focus.', $title),
        containsAny($keywords, ['table', 'comparison', 'compare']) => sprintf('%s organizes rows, options, and details so visitors can compare information quickly.', $title),
        containsAny($keywords, ['form', 'signup', 'request', 'booking', 'contact', 'waitlist', 'newsletter', 'demo', 'callback', 'download form']) => sprintf('%s captures visitor intent with focused form or request messaging.', $title),
        containsAny($keywords, ['pricing', 'plan', 'billing', 'bundle', 'calculator', 'order summary']) => sprintf('%s supports buying decisions with prices, plans, packages, or calculated options.', $title),
        containsAny($keywords, ['testimonial', 'review', 'quote', 'rating', 'award', 'trust', 'client', 'customer', 'logo', 'partner', 'certification', 'press']) => sprintf('%s builds credibility through voices, logos, ratings, awards, or references.', $title),
        containsAny($keywords, ['team', 'member', 'founder', 'advisor', 'board', 'profile', 'culture', 'career', 'job', 'office', 'org chart', 'company values', 'company perks']) => sprintf('%s introduces people, roles, culture, or hiring context with clear editorial structure.', $title),
        containsAny($keywords, ['navigation', 'navbar', 'menu', 'breadcrumb', 'toc', 'pagination', 'tabs', 'steps', 'search', 'back to top', 'utility bar']) => sprintf('%s helps visitors find their way through links, menus, anchors, or search entry points.', $title),
        containsAny($keywords, ['footer', 'copyright', 'legal', 'privacy', 'cookie', 'gdpr', 'accessibility', 'imprint', 'terms']) => sprintf('%s closes a page with utility information, legal links, contact routes, or service details.', $title),
        containsAny($keywords, ['hero']) => sprintf('%s opens landing pages, campaigns, or product pages with a strong first message.', $title),
        containsAny($keywords, ['feature', 'benefit', 'service', 'product', 'use case']) => sprintf('%s explains product value, features, or use cases in a clear content structure.', $title),
        containsAny($keywords, ['gallery', 'image', 'video', 'audio', 'media', 'embed', 'map']) => sprintf('%s pairs media or embedded content with supporting editorial context.', $title),
        containsAny($keywords, ['timeline', 'milestone', 'history', 'process', 'how to', 'onboarding', 'progress', 'changelog']) => sprintf('%s makes sequences, progress, history, or process steps easier to understand.', $title),
        containsAny($keywords, ['card', 'grid', 'list', 'library', 'sitemap', 'category']) => sprintf('%s arranges multiple pieces of content as scannable cards, grids, or libraries.', $title),
        default => sprintf('%s provides a focused editorial building block for a recognizable page task.', $title),
    };
}

/**
 * @param array<string, mixed> $config
 */
function capabilitySentence(array $config, string $language): string
{
    $fields = previewFields($config['fields'] ?? []);
    $fileCount = 0;
    $linkCount = 0;
    $collectionCount = 0;
    $selectCount = 0;
    $textCount = 0;

    foreach ($fields as $field) {
        $type = (string)($field['type'] ?? (($field['useExistingField'] ?? false) ? 'Existing' : 'Textarea'));
        $identifier = (string)$field['identifier'];
        if ($type === 'File') {
            $fileCount++;
        } elseif ($type === 'Link' || str_contains($identifier, 'link')) {
            $linkCount++;
        } elseif ($type === 'Collection') {
            $collectionCount++;
        } elseif (in_array($type, ['Select', 'Checkbox', 'Radio'], true)) {
            $selectCount++;
        } elseif (!in_array($type, ['Palette'], true)) {
            $textCount++;
        }
    }

    $parts = [];
    if ($textCount > 0) {
        $parts[] = $language === 'de' ? 'Text- und Überschriftenfelder' : 'headline and copy fields';
    }
    if ($fileCount > 0) {
        $parts[] = $language === 'de' ? 'Medienauswahl' : 'media pickers';
    }
    if ($collectionCount > 0) {
        $parts[] = $language === 'de' ? 'wiederholbare Einträge' : 'repeatable entries';
    }
    if ($linkCount > 0) {
        $parts[] = $language === 'de' ? 'Link- und CTA-Steuerung' : 'link and CTA controls';
    }
    if ($selectCount > 0) {
        $parts[] = $language === 'de' ? 'Layout- oder Variantenoptionen' : 'layout or variant options';
    }

    if ($parts === []) {
        return $language === 'de'
            ? 'Redakteure erhalten eine schlanke Konfiguration ohne unnötige Felder.'
            : 'Editors get a lean configuration without unnecessary fields.';
    }

    return $language === 'de'
        ? 'Redakteure pflegen ' . naturalList($parts, 'de') . '.'
        : 'Editors can manage ' . naturalList($parts, 'en') . '.';
}

/**
 * @param list<string> $needles
 */
function containsAny(string $haystack, array $needles): bool
{
    foreach ($needles as $needle) {
        if (str_contains($haystack, $needle)) {
            return true;
        }
    }

    return false;
}

/**
 * @param list<string> $items
 */
function naturalList(array $items, string $language): string
{
    $items = array_values(array_filter($items));
    if (count($items) <= 1) {
        return $items[0] ?? '';
    }

    $last = array_pop($items);
    $joiner = $language === 'de' ? ' und ' : ' and ';

    return implode(', ', $items) . $joiner . $last;
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
        $value = previewValueExpression('data.' . $identifier, $field);
        $lines[] = '        <f:if condition="{data.' . $identifier . '}">';
        $lines[] = '            <div class="d-ce-preview__field">';
        $lines[] = '                <span class="d-ce-preview__label">' . xml($label) . '</span>';
        $lines[] = '                <span class="d-ce-preview__value">' . $value . '</span>';
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
                $value = previewValueExpression('item.' . $childIdentifier, $child);
                $lines[] = '                            <f:if condition="{item.' . $childIdentifier . '}"><span>' . $value . '</span></f:if>';
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

/**
 * @param array<string, mixed> $field
 */
function previewValueExpression(string $path, array $field): string
{
    return match ((string)($field['type'] ?? '')) {
        'Date' => '{' . $path . ' -> f:format.date(format: \'Y-m-d\')}',
        'DateTime' => '{' . $path . ' -> f:format.date(format: \'Y-m-d H:i\')}',
        'Time' => '{' . $path . ' -> f:format.date(format: \'H:i\')}',
        default => '{' . $path . '}',
    };
}

function xlf(string $productName, string $title, string $description): string
{
    return xlf20($productName, [
        'title' => $title,
        'description' => $description,
    ]);
}

function deXlf(string $productName, string $title, string $description, string $germanTitle, string $germanDescription): string
{
    return xlf20($productName, [
        'title' => $title,
        'description' => $description,
    ], [
        'title' => $germanTitle,
        'description' => $germanDescription,
    ], 'de');
}

/**
 * @param array<string, string> $sources
 * @param array<string, string>|null $targets
 */
function xlf20(string $productName, array $sources, ?array $targets = null, ?string $targetLanguage = null): string
{
    $targetAttribute = $targetLanguage !== null ? ' trgLang="' . xml($targetLanguage) . '"' : '';
    $lines = [
        '<?xml version="1.0" encoding="utf-8"?>',
        '<xliff version="2.0" xmlns="urn:oasis:names:tc:xliff:document:2.0" srcLang="en"' . $targetAttribute . '>',
        '  <file id="' . xml($productName) . '">',
    ];

    foreach ($sources as $id => $source) {
        $segmentState = $targetLanguage !== null ? ' state="final"' : '';
        $lines[] = '    <unit id="' . xml((string)$id) . '">';
        $lines[] = '      <segment' . $segmentState . '>';
        $lines[] = '        <source>' . xml($source) . '</source>';
        if ($targetLanguage !== null) {
            $lines[] = '        <target>' . xml($targets[$id] ?? $source) . '</target>';
        }
        $lines[] = '      </segment>';
        $lines[] = '    </unit>';
    }

    $lines[] = '  </file>';
    $lines[] = '</xliff>';

    return implode("\n", $lines) . "\n";
}

/**
 * @param array<string, array{title: string, deTitle: string, summary: string, deSummary: string}> $groupMeta
 */
function writeSharedLabelFiles(string $englishFile, string $germanFile, array $groupMeta): void
{
    $sources = readXlfSources($englishFile);
    $targets = readXlfTargets($germanFile);

    foreach ($groupMeta as $group => $meta) {
        $labelId = contentElementGroupLabelId($group);
        $descriptionId = $labelId . '.description';
        $sources[$labelId] = $meta['title'];
        $sources[$descriptionId] = $meta['summary'];
        $targets[$labelId] = $meta['deTitle'];
        $targets[$descriptionId] = $meta['deSummary'];
    }

    file_put_contents($englishFile, xlf20('desiderio', $sources));
    file_put_contents($germanFile, xlf20('desiderio', $sources, $targets, 'de'));
}

/**
 * @return array<string, string>
 */
function readXlfSources(string $file): array
{
    return readXlfUnits($file, false);
}

/**
 * @return array<string, string>
 */
function readXlfTargets(string $file): array
{
    return readXlfUnits($file, true);
}

/**
 * @return array<string, string>
 */
function readXlfUnits(string $file, bool $target): array
{
    $document = new DOMDocument();
    $document->load($file);
    $xpath = new DOMXPath($document);
    $units = [];

    foreach ($xpath->query('//*[local-name() = "unit" or local-name() = "trans-unit"]') ?: [] as $unit) {
        if (!$unit instanceof DOMElement) {
            continue;
        }
        $id = $unit->getAttribute('id');
        if ($id === '') {
            continue;
        }
        $nodeName = $target ? 'target' : 'source';
        $node = $xpath->query('.//*[local-name() = "' . $nodeName . '"]', $unit)?->item(0);
        if (!$node instanceof DOMNode && $target) {
            $node = $xpath->query('.//*[local-name() = "source"]', $unit)?->item(0);
        }
        if (!$node instanceof DOMNode) {
            continue;
        }
        $units[$id] = trim($node->textContent);
    }

    return $units;
}

function contentElementGroupLabelId(string $group): string
{
    $parts = explode('-', $group);
    $camel = array_shift($parts) ?: $group;
    foreach ($parts as $part) {
        $camel .= ucfirst($part);
    }

    return 'contentElementGroup.' . $camel;
}

/**
 * @param list<string> $groups
 */
function writeContentElementGroupTca(string $file, array $groups): void
{
    $lines = [
        '<?php',
        '',
        'declare(strict_types=1);',
        '',
        'use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;',
        '',
        '$position = \'before:default\';',
        '',
        'foreach ([',
    ];

    foreach ($groups as $group) {
        $lines[] = '    \'' . $group . '\' => \'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:' . contentElementGroupLabelId($group) . '\',';
    }

    $lines = [
        ...$lines,
        '] as $group => $label) {',
        '    ExtensionManagementUtility::addTcaSelectItemGroup(',
        '        \'tt_content\',',
        '        \'CType\',',
        '        $group,',
        '        $label,',
        '        $position,',
        '    );',
        '    $position = \'after:\' . $group;',
        '}',
        '',
    ];

    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0775, true);
    }
    file_put_contents($file, implode("\n", $lines));
}

function xml(string $value): string
{
    return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function iconSvg(string $slug, string $group, string $title): string
{
    $iconTitle = xml($title . ' icon');
    $shape = iconShapeFor($slug, $group, $title) . "\n  " . iconIdentityMark($slug);

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
  <title>{$iconTitle}</title>
  <style>.accent{stroke:var(--icon-color-accent,currentColor);fill:none}.accent-fill,.no-stroke{stroke:none;fill:var(--icon-color-accent,currentColor)}</style>
  {$shape}
</svg>
SVG . "\n";
}

function iconShapeFor(string $slug, string $group, string $title): string
{
    $keywords = strtolower(str_replace('-', ' ', $slug . ' ' . $group . ' ' . $title));

    if ($slug === 'hero' || str_starts_with($slug, 'hero-')) {
        return heroIconShape($slug);
    }
    if ($slug === 'navbar' || str_starts_with($slug, 'navbar-') || str_starts_with($slug, 'nav-') || in_array($slug, ['announcement-bar', 'breadcrumb', 'header-banner', 'header-page', 'header-profile', 'header-section', 'mega-menu', 'search-header', 'sitemap-grid', 'utility-bar'], true)) {
        return navigationIconShape($slug);
    }
    if ($slug === 'footer' || str_starts_with($slug, 'footer-') || in_array($slug, ['copyright-bar', 'legal-links'], true)) {
        return footerIconShape($slug);
    }
    if ($slug === 'pricing' || str_starts_with($slug, 'pricing-') || containsAny($keywords, ['pricing', 'plan', 'billing', 'bundle', 'order summary'])) {
        return pricingIconShape($slug);
    }
    if ($slug === 'features' || str_starts_with($slug, 'feature-') || in_array($slug, ['benefit-cards', 'use-case-grid'], true)) {
        return featureIconShape($slug);
    }
    if (str_starts_with($slug, 'chart-') || containsAny($keywords, ['analytics', 'chart', 'counter', 'dashboard', 'infographic', 'kpi', 'leaderboard', 'metric', 'stats'])) {
        return dataIconShape($slug);
    }
    if ($slug === 'team' || str_starts_with($slug, 'team-') || containsAny($keywords, ['about', 'advisor', 'board', 'career', 'company', 'founder', 'job', 'mission', 'office', 'org chart', 'profile'])) {
        return peopleIconShape($slug);
    }
    if ($slug === 'testimonial' || str_starts_with($slug, 'testimonial-') || containsAny($keywords, ['award', 'badge', 'certification', 'client', 'logo', 'partner', 'press', 'quote', 'rating', 'review', 'social proof', 'trust'])) {
        return proofIconShape($slug);
    }
    if (containsAny($keywords, ['cta', 'offer', 'signup', 'request', 'demo', 'waitlist', 'callback', 'download form'])) {
        return conversionIconShape($slug);
    }
    if (containsAny($keywords, ['contact', 'mail', 'newsletter', 'emergency'])) {
        return contactIconShape($slug);
    }
    if (containsAny($keywords, ['privacy', 'gdpr', 'security', 'compliance', 'imprint', 'terms', 'accessibility', 'disclaimer'])) {
        return legalIconShape($slug);
    }
    if (containsAny($keywords, ['audio', 'video', 'gallery', 'image', 'embed', 'map', 'directions', 'location'])) {
        return mediaIconShape($slug);
    }
    if (containsAny($keywords, ['accordion', 'faq'])) {
        return '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5 5.5h5.5"/><path d="M5 8h4"/><path d="m10.5 10 1.4 1.4 1.4-1.4" class="accent"/>';
    }
    if (containsAny($keywords, ['alert', 'callout', 'notification', 'status', 'emergency'])) {
        return '<path d="M8 2.75 13.5 12H2.5z"/><path d="M8 6v2.5"/><circle cx="8" cy="10.5" r=".55" class="accent-fill"/>';
    }
    if (containsAny($keywords, ['back to top'])) {
        return '<path d="M8 12.5v-9"/><path d="m4.75 6.75 3.25-3.25 3.25 3.25"/><path d="M3.25 12.75h9.5" class="accent"/>';
    }
    if (containsAny($keywords, ['booking', 'calendar', 'event', 'office hours'])) {
        return '<rect x="3" y="4" width="10" height="9" rx="2"/><path d="M3 6.5h10"/><path d="M5.5 2.75v2.5"/><path d="M10.5 2.75v2.5"/><circle cx="10.75" cy="10.25" r="1.35" class="accent"/>';
    }
    if (containsAny($keywords, ['breadcrumb', 'steps', 'timeline', 'process', 'how to', 'onboarding', 'progress'])) {
        return '<circle cx="3.75" cy="8" r="1.25"/><circle cx="8" cy="8" r="1.25" class="accent"/><circle cx="12.25" cy="8" r="1.25"/><path d="M5 8h1.75M9.25 8H11"/>';
    }
    if (containsAny($keywords, ['calculator', 'roi'])) {
        return '<rect x="4" y="2.75" width="8" height="10.5" rx="1.75"/><path d="M5.75 5.25h4.5"/><path d="M6 8h.1M8 8h.1M10 8h.1M6 10.5h.1M8 10.5h.1"/><path d="M10 10.5h.1" class="accent"/>';
    }
    if (containsAny($keywords, ['carousel', 'slider'])) {
        return '<rect x="2.75" y="4" width="5.25" height="7.75" rx="1.4"/><rect x="8" y="3" width="5.25" height="9.75" rx="1.4" class="accent"/><path d="m3 8-1 1 1 1M13 8l1 1-1 1"/>';
    }
    if (containsAny($keywords, ['code'])) {
        return '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="m6.2 6.5-1.5 1.5 1.5 1.5"/><path d="m9.8 6.5 1.5 1.5-1.5 1.5"/><path d="m8.7 5.75-1.4 4.5" class="accent"/>';
    }
    if (containsAny($keywords, ['columns', 'split'])) {
        return '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M8 3.25v9.5"/><path d="M5 6h1.4M9.6 6H11"/><path d="M5 8.5h1.4M9.6 8.5H11" class="accent"/>';
    }
    if (containsAny($keywords, ['cookie'])) {
        return '<circle cx="8" cy="8" r="4.75"/><circle cx="6.5" cy="6.25" r=".55" class="accent-fill"/><circle cx="9.75" cy="7.25" r=".55" class="accent-fill"/><circle cx="7.8" cy="10" r=".55" class="accent-fill"/>';
    }
    if (containsAny($keywords, ['definition', 'changelog', 'resource', 'article', 'blog', 'textmedia', 'content'])) {
        return contentIconShape($slug);
    }
    if (containsAny($keywords, ['divider'])) {
        return '<path d="M2.75 8h10.5"/><circle cx="8" cy="8" r="1.35" class="accent"/><path d="M4.5 5.5h7M4.5 10.5h7"/>';
    }
    if (containsAny($keywords, ['product', 'card', 'grid', 'category', 'library'])) {
        return productIconShape($slug);
    }
    if (containsAny($keywords, ['search'])) {
        return '<circle cx="7" cy="7" r="3.5"/><path d="m9.75 9.75 3 3"/><path d="M5.25 7h3.5" class="accent"/>';
    }
    if (containsAny($keywords, ['social'])) {
        return '<circle cx="4" cy="8" r="1.5"/><circle cx="11.5" cy="4.5" r="1.5" class="accent"/><circle cx="11.5" cy="11.5" r="1.5"/><path d="m5.35 7.35 4.8-2.25M5.35 8.65l4.8 2.25"/>';
    }

    return productIconShape($slug);
}

function heroIconShape(string $slug): string
{
    return match ($slug) {
        'hero-animated' => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="M5 8c1.2-2 2.5 2 3.7 0s2.2-1.4 3 .2" class="accent"/><path d="M5 5.75h3.75"/>',
        'hero-announcement' => '<rect x="2.75" y="4" width="10.5" height="8" rx="2"/><path d="M5 8h3.25"/><path d="m9 6.75 3-1.25v5L9 9.25z" class="accent"/>',
        'hero-app' => '<rect x="5" y="2.75" width="6" height="10.5" rx="1.75"/><path d="M6.75 5.25h2.5M6.75 7.5h2.5"/><circle cx="8" cy="11.25" r=".45" class="accent-fill"/>',
        'hero-asymmetric' => '<path d="M2.75 3.25h10.5v9.5H2.75z"/><path d="M2.75 12.75 10.5 3.25"/><path d="M5 5.25h2.2M9.6 10.5h1.9" class="accent"/>',
        'hero-carousel' => '<rect x="2.75" y="4.25" width="4.5" height="7.5" rx="1.4"/><rect x="7.25" y="3.25" width="6" height="9.5" rx="1.4" class="accent"/><path d="M4 8h.1M11.8 8h.1"/>',
        'hero-countdown' => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><circle cx="8" cy="8.25" r="2.7" class="accent"/><path d="M8 8.25V6.5M8 8.25l1.35.8"/>',
        'hero-cta-only' => '<rect x="3" y="5" width="10" height="6" rx="3"/><path d="M5.5 8h4.25"/><path d="m9.5 6.6 1.8 1.4-1.8 1.4" class="accent"/>',
        'hero-dual-cta' => '<rect x="2.75" y="4" width="10.5" height="8" rx="2"/><rect x="4.5" y="8.5" width="3" height="1.6" rx=".8" class="accent"/><rect x="8.5" y="8.5" width="3" height="1.6" rx=".8"/><path d="M5 6.25h6"/>',
        'hero-form' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M5 5.75h3.25"/><rect x="5" y="7.5" width="6" height="1.4" rx=".7" class="accent"/><rect x="5" y="10" width="4" height="1.4" rx=".7"/>',
        'hero-fullscreen' => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5.25 6V5.25H6M10 5.25h.75V6M10.75 10v.75H10M6 10.75h-.75V10" class="accent"/>',
        'hero-gradient' => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="M3.5 11.75 12.5 3.8M5 11.75l7.5-6.1M7 11.75l5.5-4.3" class="accent"/>',
        'hero-illustration' => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="m4.75 10 2-2 1.5 1.5 1-1 2 2.5"/><circle cx="10.75" cy="5.75" r="1" class="accent"/>',
        'hero-logo-cloud' => '<rect x="3" y="4" width="3" height="2" rx=".7"/><rect x="8" y="4" width="5" height="2" rx=".7" class="accent"/><rect x="4.5" y="8.5" width="3.5" height="2" rx=".7" class="accent"/><rect x="9.5" y="8.5" width="2.5" height="2" rx=".7"/>',
        'hero-minimal' => '<path d="M4.25 5.25h7.5"/><path d="M5.5 8h5"/><path d="M6.75 10.75h2.5" class="accent"/>',
        'hero-parallax' => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="M4 10.75 6.5 8l2 2 1.5-1.5 2 2.25" class="accent"/><path d="M4.5 6.25h4"/>',
        'hero-pricing' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M5 6h5.5"/><path d="M5 8.5h3"/><path d="M10.5 8.5c-1.6 0-1.6 2.25 0 2.25s1.6-2.25 0-2.25" class="accent"/>',
        'hero-product' => '<rect x="2.75" y="3.5" width="10.5" height="9" rx="2"/><path d="m8 5 3 1.6v3.2L8 11.4 5 9.8V6.6z" class="accent"/><path d="M5 6.6 8 8.2l3-1.6M8 8.2v3.2"/>',
        'hero-saas' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="M5 7.2h2.5M5 9.5h5.5"/><path d="M9.3 6.3c.3-1 1.9-1 2.2.1.8.1 1.2.7 1.2 1.3 0 .8-.6 1.3-1.5 1.3H9.7" class="accent"/>',
        'hero-search' => '<rect x="3" y="4" width="10" height="8" rx="2"/><circle cx="7.25" cy="8" r="2.1"/><path d="m8.75 9.5 2 2" class="accent"/>',
        'hero-stacked' => '<rect x="4.5" y="3" width="7" height="3" rx="1"/><rect x="3.5" y="6.5" width="9" height="3" rx="1" class="accent"/><rect x="2.5" y="10" width="11" height="3" rx="1"/>',
        'hero-startup' => '<path d="M8 2.75c2 1.25 3 3 3 5.25l2 1-2.2 1.1L9.7 13H6.3l-1.1-2.9L3 9l2-1c0-2.25 1-4 3-5.25z"/><path d="M8 5.75h.1M6.5 13l-1 1M9.5 13l1 1" class="accent"/>',
        'hero-stats' => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="M5 10.5V8.75M8 10.5v-4M11 10.5V7.5" class="accent"/><path d="M5 6h2.5"/>',
        'hero-testimonial' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="M5 7h4.5M5 9h3"/><path d="m10 9.25 1.4 1.4V9.25H12" class="accent"/>',
        'hero-video' => '<rect x="2.75" y="3.75" width="10.5" height="8.5" rx="2"/><path d="m7 6.3 3.5 1.7L7 9.7z" class="accent-fill"/>',
        default => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="M5 6.25h5.5"/><path d="M5 8.5h3.25"/><circle cx="11.5" cy="5.25" r="1.25" class="accent"/>',
    };
}

function navigationIconShape(string $slug): string
{
    return match ($slug) {
        'announcement-bar' => '<rect x="2.5" y="4.5" width="11" height="7" rx="1.5"/><path d="m5 8 3.1-1.6v3.2z" class="accent"/><path d="M8.1 6.4h3M8.1 9.6h2"/>',
        'breadcrumb' => '<path d="M3 8h2.75"/><path d="m5.5 5.75 2.25 2.25-2.25 2.25"/><path d="M8.25 8H13" class="accent"/>',
        'header-banner' => '<rect x="3" y="3.5" width="10" height="9" rx="1.5"/><path d="M3 6.25h10"/><path d="M5 9h6" class="accent"/>',
        'header-page' => '<path d="M4 2.75h5.25L12 5.5v7.75H4z"/><path d="M4 6.25h8"/><path d="M6 9h4" class="accent"/>',
        'header-profile' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><circle cx="6.25" cy="7" r="1.35" class="accent"/><path d="M4.75 10.5c.35-1 1-1.5 1.5-1.5s1.15.5 1.5 1.5M9 6.25h2"/>',
        'header-section' => '<path d="M3.5 4.25h9"/><path d="M4.5 8h7"/><path d="M6 11.25h4" class="accent"/>',
        'mega-menu' => '<rect x="2.75" y="4" width="10.5" height="8.5" rx="1.5"/><path d="M2.75 6.25h10.5"/><path d="M5 8h2M5 10h1.5M9 8h2M9 10h1.5" class="accent"/>',
        'nav-pagination' => '<path d="m5.25 5.25-2.5 2.75 2.5 2.75"/><path d="m10.75 5.25 2.5 2.75-2.5 2.75"/><rect x="6.25" y="6.25" width="3.5" height="3.5" rx=".9" class="accent"/>',
        'nav-steps' => '<circle cx="4" cy="8" r="1.35"/><circle cx="8" cy="8" r="1.35" class="accent"/><circle cx="12" cy="8" r="1.35"/><path d="M5.35 8h1.3M9.35 8h1.3"/>',
        'nav-tabs', 'navbar-tabbed' => '<path d="M3 12.25h10"/><path d="M3.5 12.25v-6h3.5l1 1.5h4.5v4.5"/><path d="M4 5.25h3" class="accent"/>',
        'nav-toc' => '<path d="M4 4.5h8"/><path d="M4 8h6"/><path d="M4 11.5h4"/><circle cx="2.75" cy="4.5" r=".45" class="accent-fill"/><circle cx="2.75" cy="8" r=".45" class="accent-fill"/><circle cx="2.75" cy="11.5" r=".45" class="accent-fill"/>',
        'navbar-centered' => '<path d="M3 5h10"/><path d="M5.5 8h5" class="accent"/><path d="M3 11h10"/>',
        'navbar-dropdown' => '<path d="M3 5h10"/><rect x="5" y="7" width="6" height="5" rx="1.25" class="accent"/><path d="m7 9 1 1 1-1"/>',
        'navbar-icon' => '<circle cx="4" cy="5" r="1"/><circle cx="8" cy="5" r="1" class="accent"/><circle cx="12" cy="5" r="1"/><path d="M3 10.5h10"/>',
        'navbar-minimal' => '<path d="M4.5 5.25h7"/><path d="M5.75 8h4.5" class="accent"/><path d="M6.75 10.75h2.5"/>',
        'navbar-mobile' => '<rect x="5" y="2.75" width="6" height="10.5" rx="1.75"/><path d="M6.75 6h2.5M6.75 8h2.5M6.75 10h2.5" class="accent"/>',
        'navbar-sidebar' => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M6.5 3v10"/><path d="M4.5 5.5h.1M4.5 8h.1M4.5 10.5h.1" class="accent"/>',
        'navbar-split' => '<path d="M3 5h4"/><path d="M9 5h4"/><path d="M3 10.5h4M9 10.5h4" class="accent"/><circle cx="8" cy="7.75" r="1.15"/>',
        'navbar-stacked' => '<rect x="3.5" y="3.5" width="9" height="2.25" rx="1"/><rect x="3.5" y="6.85" width="9" height="2.25" rx="1" class="accent"/><rect x="3.5" y="10.2" width="9" height="2.25" rx="1"/>',
        'navbar-sticky' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M3 5.75h10"/><path d="m8 6 1.5 2.25H6.5z" class="accent"/>',
        'navbar-transparent' => '<rect x="3" y="4" width="10" height="8" rx="2" stroke-dasharray="1.2 1.2"/><path d="M5 7h6M5 9.5h4" class="accent"/>',
        'search-header' => '<rect x="3" y="4" width="10" height="8" rx="2"/><circle cx="7.25" cy="8" r="2.1"/><path d="m8.75 9.5 2 2" class="accent"/>',
        'sitemap-grid' => '<rect x="6.5" y="2.75" width="3" height="2.2" rx=".7"/><rect x="3" y="10.5" width="3" height="2.2" rx=".7"/><rect x="10" y="10.5" width="3" height="2.2" rx=".7"/><path d="M8 5v2.5M4.5 10.5V7.5h7v3" class="accent"/>',
        'utility-bar' => '<rect x="3" y="3.5" width="10" height="2.5" rx="1"/><path d="M4.5 9h3M9 9h2.5M4.5 11.5h5" class="accent"/>',
        default => '<path d="M3 4.5h10"/><path d="M3 8h10"/><path d="M3 11.5h7"/><circle cx="12.25" cy="11.5" r="1.25" class="accent"/>',
    };
}

function footerIconShape(string $slug): string
{
    return match ($slug) {
        'footer-app-links' => '<rect x="4.5" y="3" width="7" height="10" rx="1.75"/><path d="M6.25 5.5h3.5M6.25 8h3.5"/><path d="M6.5 11h3" class="accent"/>',
        'footer-brand' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="m6.75 6.25 1.25-1 1.25 1v3.5l-1.25 1-1.25-1z" class="accent"/><path d="M4.75 11.75h6.5"/>',
        'footer-centered' => '<path d="M4 5.25h8"/><path d="M5.25 8h5.5" class="accent"/><path d="M3.25 11h9.5"/>',
        'footer-columns' => '<rect x="3" y="4" width="10" height="8" rx="1.5"/><path d="M6.35 4v8M9.65 4v8"/><path d="M4.5 6.5h.9M7.55 6.5h.9M10.7 6.5h.9" class="accent"/>',
        'footer-contact' => '<rect x="3" y="4.5" width="10" height="7" rx="1.5"/><path d="m3.8 5.25 4.2 3.2 4.2-3.2"/><circle cx="11.25" cy="11.25" r="1.25" class="accent"/>',
        'footer-dark' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="M10.5 5.5a3.2 3.2 0 1 0 0 5.1 4 4 0 1 1 0-5.1z" class="accent"/>',
        'footer-mega' => '<rect x="2.75" y="4" width="10.5" height="8" rx="1.5"/><path d="M2.75 6.25h10.5"/><path d="M5 8h1.5M5 10h1.5M8.25 8h1.5M8.25 10h1.5" class="accent"/>',
        'footer-minimal' => '<path d="M4.25 6h7.5"/><path d="M5.75 8.5h4.5" class="accent"/><path d="M7 11h2"/>',
        'footer-newsletter' => '<rect x="3" y="4.25" width="10" height="7.5" rx="1.5"/><path d="m3.75 5 4.25 3.25L12.25 5"/><path d="M5 10.5h6" class="accent"/>',
        'footer-social' => '<circle cx="4.5" cy="8" r="1.25"/><circle cx="8" cy="8" r="1.25" class="accent"/><circle cx="11.5" cy="8" r="1.25"/><path d="M5.75 8h1M9.25 8h1"/>',
        'footer-split' => '<rect x="3" y="4" width="10" height="8" rx="1.5"/><path d="M8 4v8"/><path d="M4.75 7h1.75M9.5 7h1.75M4.75 9.5h1.25" class="accent"/>',
        'copyright-bar' => '<rect x="3" y="5" width="10" height="6" rx="1.5"/><circle cx="6.25" cy="8" r="1.4"/><path d="M6.9 7.4c-.55-.45-1.5-.1-1.5.6s.95 1.05 1.5.6" class="accent"/><path d="M9 8h2.25"/>',
        'legal-links' => '<rect x="4" y="3" width="8" height="10" rx="1.4"/><path d="M6 6h4M6 8.5h4M6 11h2.5" class="accent"/>',
        default => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5 6h6"/><path d="M5 8.25h3"/><rect x="5" y="10.25" width="6" height="1.25" rx=".625" class="accent-fill"/>',
    };
}

function pricingIconShape(string $slug): string
{
    return match ($slug) {
        'bundle-pricing' => '<path d="M4 5.5 8 3l4 2.5v5L8 13l-4-2.5z"/><path d="M4 5.5 8 8l4-2.5M8 8v5" class="accent"/>',
        'card-pricing' => '<rect x="3.5" y="3" width="9" height="10" rx="2"/><path d="M5.5 6h5M5.5 8.5h3"/><circle cx="10.5" cy="10.5" r="1.25" class="accent"/>',
        'order-summary' => '<rect x="4" y="3" width="8" height="10" rx="1.4"/><path d="M6 6h4M6 8.25h3M6 10.5h2"/><path d="M10.5 10.5h.1" class="accent"/>',
        'pricing-annual-monthly', 'pricing-toggle' => '<rect x="3" y="4.25" width="10" height="7.5" rx="3.75"/><circle cx="6.75" cy="8" r="2" class="accent"/><path d="M9.5 8h1.5"/>',
        'pricing-calculator' => '<rect x="4" y="2.75" width="8" height="10.5" rx="1.75"/><path d="M5.75 5.25h4.5"/><path d="M6 8h.1M8 8h.1M10 8h.1M6 10.5h.1M8 10.5h.1"/><path d="M10 10.5h.1" class="accent"/>',
        'pricing-comparison' => '<rect x="3" y="3" width="4" height="10" rx="1.2"/><rect x="9" y="3" width="4" height="10" rx="1.2" class="accent"/><path d="m4.4 8 1 1 1.6-2M10.4 8h1.8"/>',
        'pricing-enterprise' => '<rect x="4" y="3" width="8" height="10" rx="1"/><path d="M6 5.5h.1M8 5.5h.1M10 5.5h.1M6 8h.1M8 8h.1M10 8h.1"/><path d="M7 13v-2h2v2" class="accent"/>',
        'pricing-faq' => '<path d="M4 5.25h7.5a1.5 1.5 0 0 1 1.5 1.5v3.5a1.5 1.5 0 0 1-1.5 1.5H7l-3 2v-2.5a1.5 1.5 0 0 1-1.5-1.5v-3A1.5 1.5 0 0 1 4 5.25z"/><path d="M8 9.4v-.2c0-.8 1.4-.8 1.4-1.8 0-.7-.55-1.15-1.35-1.15-.7 0-1.2.3-1.45.85" class="accent"/><path d="M8 11h.1"/>',
        'pricing-four-tier' => '<rect x="2.75" y="4" width="2.5" height="8" rx=".8"/><rect x="5.75" y="3.25" width="2.5" height="8.75" rx=".8" class="accent"/><rect x="8.75" y="4" width="2.5" height="8" rx=".8"/><rect x="11.75" y="5" width="2" height="7" rx=".8"/>',
        'pricing-simple' => '<rect x="4" y="3.5" width="8" height="9" rx="2"/><path d="M6 6.25h4M6 8.5h3"/><path d="M8 11h.1" class="accent"/>',
        'pricing-slider' => '<path d="M3.5 5h9M3.5 8h9M3.5 11h9"/><circle cx="6" cy="5" r="1.1" class="accent"/><circle cx="10" cy="8" r="1.1"/><circle cx="8" cy="11" r="1.1" class="accent"/>',
        'pricing-three-tier' => '<rect x="3" y="5" width="3" height="7" rx="1"/><rect x="6.5" y="3.5" width="3" height="8.5" rx="1" class="accent"/><rect x="10" y="5" width="3" height="7" rx="1"/>',
        'pricing-two-tier' => '<rect x="3.5" y="4" width="4" height="8" rx="1.2"/><rect x="8.5" y="4" width="4" height="8" rx="1.2" class="accent"/>',
        'pricing-usage' => '<path d="M3.5 11.5a4.5 4.5 0 0 1 9 0"/><path d="M8 11.5 10.5 7" class="accent"/><path d="M5 11.5h6"/>',
        default => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5.25 6h4.5"/><path d="M5.25 8.25h5.5"/><path d="M5.25 10.5h2.5"/><circle cx="10.75" cy="10.75" r="1.25" class="accent"/>',
    };
}

function featureIconShape(string $slug): string
{
    return match ($slug) {
        'feature-accordion' => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5 5.5h5.5M5 8h5.5M5 10.5h3"/><path d="m10.2 9.7 1 1 1-1" class="accent"/>',
        'feature-alternating' => '<rect x="3" y="3.5" width="4" height="3" rx="1"/><path d="M8.5 5h4"/><path d="M3.5 10.5h4"/><rect x="9" y="9" width="4" height="3" rx="1" class="accent"/>',
        'feature-bento' => '<rect x="3" y="3" width="5" height="6" rx="1.2"/><rect x="9" y="3" width="4" height="3" rx="1.2" class="accent"/><rect x="9" y="7" width="4" height="6" rx="1.2"/><rect x="3" y="10" width="5" height="3" rx="1.2" class="accent"/>',
        'feature-cards' => '<rect x="3" y="4" width="4" height="8" rx="1.2"/><rect x="9" y="4" width="4" height="8" rx="1.2" class="accent"/><path d="M4.5 7h1M10.5 7h1"/>',
        'feature-carousel' => '<rect x="2.75" y="4" width="5.25" height="7.75" rx="1.4"/><rect x="8" y="3" width="5.25" height="9.75" rx="1.4" class="accent"/><path d="m3 8-1 1 1 1M13 8l1 1-1 1"/>',
        'feature-centered' => '<circle cx="8" cy="5.25" r="1.7" class="accent"/><path d="M4.5 8.5h7M5.75 11h4.5"/>',
        'feature-checklist' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="m5 6.3.8.8L7.3 5.5M5 9.3l.8.8 1.5-1.6" class="accent"/><path d="M8.5 6.3H11M8.5 9.3H11"/>',
        'feature-comparison' => '<rect x="3" y="3.5" width="4.5" height="9" rx="1.2"/><rect x="8.5" y="3.5" width="4.5" height="9" rx="1.2" class="accent"/><path d="m4.3 7 1 1 1.4-1.7M10 8h1.5"/>',
        'feature-grid-3' => '<rect x="3" y="4" width="2.8" height="8" rx=".9"/><rect x="6.6" y="4" width="2.8" height="8" rx=".9" class="accent"/><rect x="10.2" y="4" width="2.8" height="8" rx=".9"/>',
        'feature-grid-4' => '<rect x="3" y="3" width="4.2" height="4.2" rx="1"/><rect x="8.8" y="3" width="4.2" height="4.2" rx="1" class="accent"/><rect x="3" y="8.8" width="4.2" height="4.2" rx="1" class="accent"/><rect x="8.8" y="8.8" width="4.2" height="4.2" rx="1"/>',
        'feature-highlight' => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="m8 5.2.7 1.4 1.55.25-1.1 1.08.25 1.55L8 8.75l-1.4.73.25-1.55-1.1-1.08 1.55-.25z" class="accent"/>',
        'feature-icons' => '<circle cx="4.5" cy="5" r="1.3"/><rect x="7" y="3.7" width="2.6" height="2.6" rx=".7" class="accent"/><path d="M11.5 3.8 13 6.2h-3z"/><path d="M3.5 10h9"/>',
        'feature-image' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="m5 10 2-2 1.5 1.5 1-1 2 2.5"/><circle cx="10.75" cy="5.75" r="1" class="accent"/>',
        'feature-list' => '<path d="M5.25 5h7"/><path d="M5.25 8h7"/><path d="M5.25 11h7"/><circle cx="3.5" cy="5" r=".5" class="accent-fill"/><circle cx="3.5" cy="8" r=".5" class="accent-fill"/><circle cx="3.5" cy="11" r=".5" class="accent-fill"/>',
        'feature-matrix' => '<rect x="3" y="3" width="10" height="10" rx="1.5"/><path d="M3 6.33h10M3 9.66h10M6.33 3v10M9.66 3v10" class="accent"/>',
        'feature-minimal' => '<path d="M4.5 5.25h7"/><path d="M5.75 8h4.5" class="accent"/><path d="M7 10.75h2"/>',
        'feature-numbered' => '<circle cx="4.5" cy="5" r="1.25" class="accent"/><path d="M7 5h5M4.5 9.5h.1M7 9.5h4"/>',
        'feature-split' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M8 3.25v9.5"/><path d="M5 6h1.4M9.6 6H11"/><path d="M5 8.5h1.4M9.6 8.5H11" class="accent"/>',
        'feature-stats' => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5.25 10.75V8.5"/><path d="M8 10.75V5.75" class="accent"/><path d="M10.75 10.75V7.25"/>',
        'feature-tabs' => '<path d="M3 12.25h10"/><path d="M3.5 12.25v-6h3.5l1 1.5h4.5v4.5"/><path d="M4 5.25h3" class="accent"/>',
        'feature-timeline' => '<path d="M5 3.5v9"/><circle cx="5" cy="5" r="1.1" class="accent"/><circle cx="5" cy="8" r="1.1"/><circle cx="5" cy="11" r="1.1" class="accent"/><path d="M7 5h5M7 8h3M7 11h4"/>',
        'feature-video' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="m7 6.5 3.25 1.5L7 9.5z" class="accent-fill"/>',
        default => '<rect x="3" y="3" width="4.25" height="4.25" rx="1.25"/><path d="m4.25 5.1.55.55 1.1-1.35" class="accent"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="3" y="8.75" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="8.75" width="4.25" height="4.25" rx="1.25" class="accent"/>',
    };
}

function dataIconShape(string $slug): string
{
    return match ($slug) {
        'chart-area' => '<path d="M3.25 12.5h9.5"/><path d="M4 10.75 6.5 8l2 1.35 3.5-4.1v7.25H4z" class="accent"/><path d="M4 10.75 6.5 8l2 1.35 3.5-4.1"/>',
        'chart-bar' => '<path d="M3.5 12.5h9"/><rect x="4" y="8" width="1.75" height="4.5" rx=".5"/><rect x="7.1" y="5.5" width="1.75" height="7" rx=".5" class="accent"/><rect x="10.2" y="3.75" width="1.75" height="8.75" rx=".5"/>',
        'chart-contribution', 'chart-heatmap' => '<rect x="3" y="3" width="2.1" height="2.1" rx=".45"/><rect x="6.1" y="3" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="9.2" y="3" width="2.1" height="2.1" rx=".45"/><rect x="3" y="6.1" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="6.1" y="6.1" width="2.1" height="2.1" rx=".45"/><rect x="9.2" y="6.1" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="3" y="9.2" width="2.1" height="2.1" rx=".45"/><rect x="6.1" y="9.2" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="9.2" y="9.2" width="2.1" height="2.1" rx=".45"/>',
        'chart-donut' => '<circle cx="8" cy="8" r="4.5"/><circle cx="8" cy="8" r="2"/><path d="M8 3.5a4.5 4.5 0 0 1 4.5 4.5H8z" class="accent-fill"/>',
        'chart-line', 'chart-sparkline' => '<path d="M3.25 12.5h9.5"/><path d="M3.75 10.5 6 8.25l2 1.5 3.75-4.25" class="accent"/><circle cx="11.75" cy="5.5" r="1"/>',
        'chart-pie' => '<path d="M8 3.5v4.75h4.75A4.75 4.75 0 1 1 8 3.5z"/><path d="M8 3.5a4.75 4.75 0 0 1 4.75 4.75H8z" class="accent"/>',
        'chart-radar' => '<path d="M8 3.25 12.25 6v4L8 12.75 3.75 10V6z"/><path d="M8 3.25v9.5M3.75 6l8.5 4M12.25 6l-8.5 4" class="accent"/><path d="m8 5.75 2.2 1.4v2L8 10.25 5.8 9.15v-2z"/>',
        'chart-stacked-bar' => '<path d="M3.5 12.5h9"/><rect x="4" y="7" width="1.75" height="5.5" rx=".5"/><path d="M4 9.5h1.75" class="accent"/><rect x="7.1" y="4.5" width="1.75" height="8" rx=".5" class="accent"/><rect x="10.2" y="6" width="1.75" height="6.5" rx=".5"/>',
        'data-table', 'table-content' => '<rect x="3" y="3.5" width="10" height="9" rx="1.5"/><path d="M3 6.5h10M3 9.5h10M6.5 3.5v9"/><path d="M10 3.5v9" class="accent"/>',
        'infographic' => '<circle cx="5.25" cy="5.25" r="2"/><path d="M9 4.25h3M9 6.25h2"/><path d="M4 10.75h8M4 12.75h5" class="accent"/>',
        'leaderboard' => '<rect x="3" y="7" width="2.5" height="5.5" rx=".8"/><rect x="6.75" y="4.5" width="2.5" height="8" rx=".8" class="accent"/><rect x="10.5" y="6" width="2.5" height="6.5" rx=".8"/><path d="M8 2.5v2"/>',
        'metric-dashboard' => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5 6h2.5M5 9h1.5"/><path d="M9 10.5a2.5 2.5 0 0 1 4 0" class="accent"/>',
        default => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5.25 10.75V8.5"/><path d="M8 10.75V5.75" class="accent"/><path d="M10.75 10.75V7.25"/><path d="M5 12h6"/>',
    };
}

function peopleIconShape(string $slug): string
{
    return match ($slug) {
        'about-us' => '<circle cx="6" cy="5.25" r="2"/><path d="M2.75 12.5c.55-2 1.7-3 3.25-3s2.7 1 3.25 3"/><path d="M10 5.25h3M10 8h2M10 10.75h3" class="accent"/>',
        'company-culture' => '<circle cx="5" cy="6" r="1.5"/><circle cx="11" cy="6" r="1.5" class="accent"/><path d="M3.5 11.5c.35-1.4 1.1-2.1 2.2-2.1s1.85.7 2.2 2.1M8.1 11.5c.35-1.4 1.1-2.1 2.2-2.1s1.85.7 2.2 2.1"/>',
        'company-history' => '<circle cx="8" cy="8" r="4.5"/><path d="M8 8V5.5"/><path d="m8 8 2 1.25" class="accent"/><path d="M4.5 3.5 3.25 5"/>',
        'company-milestones' => '<path d="M5 3.5v9"/><circle cx="5" cy="5" r="1.1" class="accent"/><circle cx="5" cy="8" r="1.1"/><circle cx="5" cy="11" r="1.1" class="accent"/><path d="M7 5h5M7 8h3M7 11h4"/>',
        'company-values' => '<path d="M8 3.25 12 5v3.25c0 2.2-1.35 3.6-4 4.75-2.65-1.15-4-2.55-4-4.75V5z"/><path d="M8 6.2v4" class="accent"/><path d="M6.3 8.2h3.4" class="accent"/>',
        'founder-quote' => '<circle cx="5.75" cy="5.75" r="1.8"/><path d="M3 12c.45-1.7 1.35-2.55 2.75-2.55s2.3.85 2.75 2.55"/><path d="M9.5 5h3.25v3h-1.5l-1.75 1.5z" class="accent"/>',
        'job-card' => '<rect x="3" y="5" width="10" height="7.5" rx="1.5"/><path d="M6.25 5V3.75h3.5V5"/><path d="M3 8.25h10" class="accent"/>',
        'mission-statement' => '<circle cx="8" cy="8" r="4.5"/><circle cx="8" cy="8" r="2.4" class="accent"/><circle cx="8" cy="8" r=".55" class="accent-fill"/>',
        'office-locations' => '<path d="M8 13s4-3.2 4-6.25a4 4 0 0 0-8 0C4 9.8 8 13 8 13z"/><circle cx="8" cy="6.75" r="1.25" class="accent"/>',
        'org-chart' => '<rect x="6.5" y="2.75" width="3" height="2.2" rx=".7"/><rect x="3" y="10.5" width="3" height="2.2" rx=".7"/><rect x="10" y="10.5" width="3" height="2.2" rx=".7"/><path d="M8 5v2.5M4.5 10.5V7.5h7v3" class="accent"/>',
        'team-carousel' => '<circle cx="5" cy="6" r="1.6"/><circle cx="10.5" cy="6" r="1.6" class="accent"/><path d="M3.25 12c.45-1.7 1.35-2.55 2.75-2.55s2.3.85 2.75 2.55M8.5 12c.35-1.35 1.1-2.05 2.15-2.05s1.8.7 2.15 2.05"/><path d="m2.5 8-.9 1 .9 1M13.5 8l.9 1-.9 1"/>',
        'team-cta' => '<circle cx="5.5" cy="5.5" r="1.7"/><path d="M3 12c.4-1.7 1.2-2.55 2.5-2.55s2.1.85 2.5 2.55"/><rect x="8.75" y="6" width="4" height="4" rx="1" class="accent"/><path d="m10.25 7.4 1.3.6-1.3.6"/>',
        'team-department' => '<circle cx="8" cy="4.75" r="1.45" class="accent"/><circle cx="4.5" cy="9.5" r="1.3"/><circle cx="11.5" cy="9.5" r="1.3"/><path d="M8 6.2v1.3M5.8 8.7 8 7.5l2.2 1.2"/>',
        'team-grid-minimal' => '<circle cx="5" cy="5" r="1.2"/><circle cx="11" cy="5" r="1.2" class="accent"/><circle cx="5" cy="11" r="1.2" class="accent"/><circle cx="11" cy="11" r="1.2"/>',
        'team-member' => '<circle cx="8" cy="5.25" r="2.25"/><path d="M3.5 13c.7-2.35 2.2-3.5 4.5-3.5s3.8 1.15 4.5 3.5" class="accent"/>',
        'team-with-bio' => '<circle cx="5.5" cy="5.25" r="1.8"/><path d="M3 12.5c.4-1.8 1.25-2.7 2.5-2.7s2.1.9 2.5 2.7"/><path d="M9 5h3.5M9 7.5h2.5M9 10h3" class="accent"/>',
        default => '<circle cx="6" cy="5.25" r="2"/><path d="M2.75 12.5c.55-2 1.7-3 3.25-3s2.7 1 3.25 3"/><circle cx="11.25" cy="6.5" r="1.5" class="accent"/><path d="M9.5 12.25c.35-1.2 1-1.8 2-1.8s1.65.6 2 1.8" class="accent"/>',
    };
}

function proofIconShape(string $slug): string
{
    return match ($slug) {
        'awards' => '<circle cx="8" cy="6" r="3.25"/><path d="m6.25 9 1.75 4 1.75-4"/><path d="m6.35 6 1.1 1.05L9.7 4.9" class="accent"/>',
        'badge-grid', 'compliance-badges', 'trust-badges' => '<rect x="3" y="3" width="4" height="4" rx="1"/><rect x="9" y="3" width="4" height="4" rx="1" class="accent"/><rect x="3" y="9" width="4" height="4" rx="1" class="accent"/><rect x="9" y="9" width="4" height="4" rx="1"/><path d="m4.1 5 .6.6 1.2-1.4" class="accent"/>',
        'certifications' => '<path d="M8 2.75 12 5v4.5L8 13.25 4 9.5V5z"/><path d="m6.25 8 1.15 1.15L10 6.5" class="accent"/>',
        'client-list', 'logo-cloud', 'logo-grid', 'logo-carousel', 'partner-grid' => '<rect x="3" y="4" width="3" height="2" rx=".7"/><rect x="8" y="4" width="5" height="2" rx=".7" class="accent"/><rect x="3" y="8" width="5" height="2" rx=".7" class="accent"/><rect x="10" y="8" width="3" height="2" rx=".7"/>',
        'press-mentions' => '<path d="M4 3.25h8v9.5H4z"/><path d="M5.5 5.5h5M5.5 7.75h4M5.5 10h2.5"/><path d="M3 5.25h1M12 5.25h1" class="accent"/>',
        'quote' => '<path d="M5.25 5.25h2.4v2.4c0 1.9-.9 3.05-2.7 3.45"/><path d="M9.25 5.25h2.4v2.4c0 1.9-.9 3.05-2.7 3.45" class="accent"/>',
        'rating-display' => '<path d="m8 3.25 1.35 2.75 3 .45-2.18 2.1.52 3L8 10.1l-2.69 1.45.52-3-2.18-2.1 3-.45z" class="accent"/>',
        'review-carousel' => '<rect x="2.75" y="4" width="5.25" height="7.75" rx="1.4"/><rect x="8" y="3" width="5.25" height="9.75" rx="1.4" class="accent"/><path d="m10.2 6.5.45.9 1 .15-.72.7.17 1-.9-.48-.9.48.17-1-.72-.7 1-.15z"/>',
        'testimonial-carousel' => '<rect x="2.75" y="4" width="5.25" height="7.75" rx="1.4"/><rect x="8" y="3" width="5.25" height="9.75" rx="1.4" class="accent"/><path d="M10.25 7h1.5M10.25 9h1"/>',
        'testimonial-featured' => '<path d="M4 5.25h7.5a1.5 1.5 0 0 1 1.5 1.5v3.5a1.5 1.5 0 0 1-1.5 1.5H7l-3 2v-2.5a1.5 1.5 0 0 1-1.5-1.5v-3A1.5 1.5 0 0 1 4 5.25z"/><path d="m10 3.25.45.9 1 .15-.72.7.17 1-.9-.48-.9.48.17-1-.72-.7 1-.15z" class="accent"/>',
        'testimonial-grid', 'testimonial-wall' => '<rect x="3" y="3.5" width="4.25" height="3.5" rx="1"/><rect x="8.75" y="3.5" width="4.25" height="3.5" rx="1" class="accent"/><rect x="3" y="9" width="4.25" height="3.5" rx="1" class="accent"/><rect x="8.75" y="9" width="4.25" height="3.5" rx="1"/>',
        'testimonial-video' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="m7 6.5 3.25 1.5L7 9.5z" class="accent-fill"/><path d="M4.75 11.25h3"/>',
        default => '<path d="M4 5.25h7.5a1.5 1.5 0 0 1 1.5 1.5v3.5a1.5 1.5 0 0 1-1.5 1.5H7l-3 2v-2.5a1.5 1.5 0 0 1-1.5-1.5v-3A1.5 1.5 0 0 1 4 5.25z"/><path d="m9.75 2.75.45.9 1 .15-.72.7.17 1-.9-.48-.9.48.17-1-.72-.7 1-.15z" class="accent"/>',
    };
}

function conversionIconShape(string $slug): string
{
    return match ($slug) {
        'callback-request' => '<path d="M5 4.5c0 3.8 2.7 6.5 6.5 6.5l1-2.1-2.2-1.1-1.1 1.1c-1.2-.5-2.1-1.4-2.6-2.6l1.1-1.1L6.6 3z"/><path d="M10.5 3.5h2v2" class="accent"/>',
        'cta-banner' => '<rect x="2.75" y="5" width="10.5" height="6" rx="1.5"/><path d="M5 8h4.25"/><path d="m9 6.5 2 1.5-2 1.5" class="accent"/>',
        'cta-card' => '<rect x="3.5" y="3.5" width="9" height="9" rx="2"/><path d="M5.5 7h5M5.5 9.5h2"/><path d="m9.25 8.5 1.75 1.4-1.75 1.4" class="accent"/>',
        'cta-floating' => '<rect x="4" y="4" width="8" height="5.5" rx="2.75"/><path d="M5.5 6.75h3"/><path d="m9 5.6 1.5 1.15L9 7.9" class="accent"/><path d="M6 12.25h4"/>',
        'cta-gradient' => '<rect x="3" y="4.5" width="10" height="7" rx="2"/><path d="M4 10.75 12 5.1M6 10.75l6-4.1" class="accent"/><path d="M5.25 8h3.5"/>',
        'cta-inline' => '<path d="M3.25 6h5"/><path d="M3.25 10h4"/><rect x="8.5" y="6.5" width="4.25" height="3" rx="1.5" class="accent"/>',
        'cta-minimal' => '<path d="M4.25 6.25h6.5"/><path d="M5.25 9.75h4"/><path d="m10 8 2 1.5-2 1.5" class="accent"/>',
        'cta-split' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="M8 4v8"/><path d="M4.75 7h1.5M9.5 7h1.75"/><path d="m10 9 1.3 1-1.3 1" class="accent"/>',
        'cta-with-image' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="M8 4v8"/><path d="m4.5 10 1.2-1.4 1.3 1.4"/><path d="m9.5 8 1.75 1.25L9.5 10.5" class="accent"/>',
        'demo-request' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="m7 6.5 3.25 1.5L7 9.5z" class="accent-fill"/><path d="M4.75 11h3"/>',
        'download-form' => '<path d="M4.5 2.75h5L12 5.25v8H4.5z"/><path d="M9.5 2.75v2.5H12"/><path d="M8 6.75v4"/><path d="m6.25 9.25 1.75 1.75 1.75-1.75" class="accent"/>',
        'waitlist-signup' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="M5 7h4M5 9.5h3"/><path d="M11 6.5v3M9.5 8h3" class="accent"/>',
        default => '<rect x="3" y="4.5" width="10" height="7" rx="2"/><path d="M5.25 8h4.5"/><path d="m9.5 6.25 2 1.75-2 1.75" class="accent"/>',
    };
}

function contactIconShape(string $slug): string
{
    return match ($slug) {
        'contact-info' => '<circle cx="5.75" cy="5.5" r="1.75"/><path d="M3.25 12c.45-1.8 1.3-2.7 2.5-2.7s2.05.9 2.5 2.7"/><path d="M9.5 5h3M9.5 8h2M9.5 11h3" class="accent"/>',
        'contact-split' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="M8 4v8"/><path d="m4 5 2 2 2-2"/><path d="M9.5 6.5h2M9.5 9h1.5" class="accent"/>',
        'emergency-contact' => '<path d="M5 4.5c0 3.8 2.7 6.5 6.5 6.5l1-2.1-2.2-1.1-1.1 1.1c-1.2-.5-2.1-1.4-2.6-2.6l1.1-1.1L6.6 3z"/><path d="M11.25 3.25v3M9.75 4.75h3" class="accent"/>',
        'feedback-form' => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5 6.25h6M5 8.5h4"/><path d="m10.25 10.1.7.7 1.3-1.7" class="accent"/>',
        'newsletter-inline' => '<rect x="3" y="5" width="10" height="6" rx="1.5"/><path d="m3.75 5.75 4.25 3L12.25 5.75"/><path d="M5 11.75h6" class="accent"/>',
        'newsletter-signup' => '<rect x="2.75" y="4.25" width="10.5" height="7.5" rx="1.75"/><path d="m3.5 5 4.5 3.5L12.5 5"/><path d="M11.8 9.5v3M10.3 11h3" class="accent"/>',
        default => '<rect x="2.75" y="4.25" width="10.5" height="7.5" rx="1.75"/><path d="m3.5 5 4.5 3.5L12.5 5"/><circle cx="11.8" cy="10.8" r="1.5" class="accent"/>',
    };
}

function legalIconShape(string $slug): string
{
    return match ($slug) {
        'accessibility-statement' => '<circle cx="8" cy="4" r="1.1" class="accent"/><path d="M4.5 6h7"/><path d="M8 5.1v3.15M5.5 12.5l1.4-4.25h2.2l1.4 4.25"/>',
        'gdpr-banner', 'privacy-notice' => '<path d="M8 2.75 12 4.5v3.25c0 2.65-1.5 4.35-4 5.5-2.5-1.15-4-2.85-4-5.5V4.5z"/><path d="M6.5 7.75V6.6A1.5 1.5 0 0 1 8 5.1a1.5 1.5 0 0 1 1.5 1.5v1.15"/><rect x="6" y="7.75" width="4" height="3" rx=".8" class="accent"/>',
        'imprint' => '<rect x="4" y="3" width="8" height="10" rx="1.4"/><path d="M6 6h4M6 8.5h4M6 11h2.5"/><path d="M10.5 3v2.5H12" class="accent"/>',
        'legal-disclaimer' => '<path d="M8 2.75 13.5 12H2.5z"/><path d="M8 6v2.5"/><circle cx="8" cy="10.5" r=".55" class="accent-fill"/>',
        'terms-summary' => '<rect x="4" y="3" width="8" height="10" rx="1.4"/><path d="M6 6h4M6 8.25h4M6 10.5h2"/><path d="m9.5 10.3.65.65 1.25-1.55" class="accent"/>',
        default => '<path d="M8 2.75 12 4.5v3.25c0 2.65-1.5 4.35-4 5.5-2.5-1.15-4-2.85-4-5.5V4.5z"/><path d="m6.25 8 1.15 1.15L10 6.5" class="accent"/>',
    };
}

function mediaIconShape(string $slug): string
{
    return match ($slug) {
        'audio-player' => '<path d="M4 9.5H2.75v-3H4l2.5-2v7z"/><path d="M9 5.5c.7.7.7 4.3 0 5"/><path d="M11 4c1.5 1.9 1.5 6.1 0 8" class="accent"/>',
        'directions' => '<path d="M8 2.75 12.5 13 8 10.75 3.5 13z"/><path d="M8 2.75v8" class="accent"/>',
        'gallery', 'product-gallery' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="m5 10 2-2 1.5 1.5 1-1 2 2.5"/><circle cx="10.75" cy="5.75" r="1" class="accent"/>',
        'map-embed' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M6.25 5.25 4.75 12M9.75 4l-1.5 7.5M6.25 5.25l3.5-1.25 1.5 1.25v7l-3-1-3.5 1.25v-7z" class="accent"/>',
        'video-embed' => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="m7 6.5 3.25 1.5L7 9.5z" class="accent-fill"/>',
        default => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="m5 10 2-2 1.5 1.5 1-1 2 2.5"/><circle cx="10.75" cy="5.75" r="1" class="accent"/>',
    };
}

function contentIconShape(string $slug): string
{
    return match ($slug) {
        'article-grid', 'blog-teasers' => '<rect x="3" y="3" width="4.25" height="4.25" rx="1"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1" class="accent"/><path d="M3.5 9.5h9M3.5 12h5"/>',
        'article-hero' => '<path d="M4 2.75h5.25L12 5.5v7.75H4z"/><path d="M9.25 2.75V5.5H12"/><path d="M5.75 7.5h4.5M5.75 9.75h3" class="accent"/>',
        'brand-story' => '<path d="M4 3.25h8v9.5H4z"/><path d="M6 5.5h4M6 8h3M6 10.5h4"/><path d="m8 3.25 1.1 1.1L8 5.45 6.9 4.35z" class="accent"/>',
        'case-study-card', 'case-study-featured', 'case-study-grid' => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5 6h4M5 8.25h6"/><path d="m9.5 10.7.85.85 1.65-2" class="accent"/>',
        'content-carousel' => '<rect x="2.75" y="4" width="5.25" height="7.75" rx="1.4"/><rect x="8" y="3" width="5.25" height="9.75" rx="1.4" class="accent"/><path d="M4.25 7h2M9.5 7h2"/>',
        'content-highlight' => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5 6.5h6"/><path d="m8 8.25.55 1.1 1.2.18-.87.85.2 1.2L8 11l-1.08.58.2-1.2-.87-.85 1.2-.18z" class="accent"/>',
        'resource-library' => '<path d="M4 3.25h3.25L8.5 5H12v7.75H4z"/><path d="M5.5 7.5h5M5.5 10h3" class="accent"/>',
        'textmedia' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M5 6h3.5M5 8.25h2.5M5 10.5h3"/><circle cx="10.5" cy="7" r="1.2" class="accent"/>',
        default => '<path d="M4 2.75h5.25L12 5.5v7.75H4z"/><path d="M9.25 2.75V5.5H12"/><path d="M5.75 7.5h4.5M5.75 9.75h3" class="accent"/>',
    };
}

function productIconShape(string $slug): string
{
    return match ($slug) {
        'card-interactive' => '<rect x="3.5" y="3.5" width="9" height="9" rx="2"/><path d="M5.5 7h5M5.5 9.5h2"/><path d="m9 9.5 2.5 1-1.2.45.7 1.25-.9.5-.7-1.25-1 .95z" class="accent"/>',
        'card-overlay' => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="m5 10 2-2 1.5 1.5 1-1 2 2.5"/><rect x="4.5" y="8.75" width="7" height="2.25" rx=".8" class="accent"/>',
        'category-cards' => '<rect x="3" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1.25" class="accent"/><path d="M4.25 10h7.5M4.25 12h4"/>',
        'product-comparison' => '<rect x="3" y="3.5" width="4.5" height="9" rx="1.2"/><rect x="8.5" y="3.5" width="4.5" height="9" rx="1.2" class="accent"/><path d="m4.3 7 1 1 1.4-1.7M10 8h1.5"/>',
        'product-feature' => '<path d="m8 3 4 2.25v5.5L8 13l-4-2.25v-5.5z"/><path d="M4 5.25 8 7.5l4-2.25M8 7.5V13" class="accent"/>',
        'product-grid' => '<rect x="3" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1.25" class="accent"/><rect x="3" y="8.75" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="8.75" width="4.25" height="4.25" rx="1.25"/>',
        'product-hero' => '<rect x="2.75" y="3.5" width="10.5" height="9" rx="2"/><path d="m8 5 3 1.6v3.2L8 11.4 5 9.8V6.6z" class="accent"/><path d="M5 6.6 8 8.2l3-1.6M8 8.2v3.2"/>',
        'product-reviews' => '<path d="M4 5.25h7.5a1.5 1.5 0 0 1 1.5 1.5v3.5a1.5 1.5 0 0 1-1.5 1.5H7l-3 2v-2.5a1.5 1.5 0 0 1-1.5-1.5v-3A1.5 1.5 0 0 1 4 5.25z"/><path d="m9.75 2.75.45.9 1 .15-.72.7.17 1-.9-.48-.9.48.17-1-.72-.7 1-.15z" class="accent"/>',
        'product-specs' => '<rect x="4" y="3" width="8" height="10" rx="1.4"/><path d="M6 6h4M6 8.25h4M6 10.5h2.5"/><path d="M4 6h-1M4 10.5h-1" class="accent"/>',
        default => '<rect x="3" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1.25" class="accent"/><rect x="3" y="8.75" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="8.75" width="4.25" height="4.25" rx="1.25"/>',
    };
}

function iconIdentityMark(string $slug): string
{
    $hash = crc32($slug);
    $points = [
        [12.05, 2.25],
        [13.45, 2.25],
        [12.05, 3.65],
        [13.45, 3.65],
        [12.05, 5.05],
        [13.45, 5.05],
        [12.75, 6.45],
    ];
    $marks = [];
    foreach ($points as $index => [$x, $y]) {
        if ((($hash >> $index) & 1) === 1) {
            $marks[] = sprintf('<circle cx="%.2F" cy="%.2F" r=".24" class="accent-fill"/>', $x, $y);
        }
    }
    if ($marks === []) {
        $marks[] = '<circle cx="12.75" cy="3.65" r=".24" class="accent-fill"/>';
    }

    $variant = ($hash >> 7) & 3;
    $marks[] = match ($variant) {
        0 => '<path d="M12.05 6.9h1.65" class="accent"/>',
        1 => '<path d="M12.05 6.9l1.65 1.15" class="accent"/>',
        2 => '<path d="M13.7 6.9l-1.65 1.15" class="accent"/>',
        default => '<path d="M12.05 7.75h1.65" class="accent"/>',
    };
    $marks[] = match (strlen($slug) % 5) {
        0 => '<path d="M12.2 8.8h.9" class="accent"/>',
        1 => '<path d="M13.2 8.4v.9" class="accent"/>',
        2 => '<path d="M12.15 8.35l1 .95" class="accent"/>',
        3 => '<path d="M13.15 8.35l-1 .95" class="accent"/>',
        default => '<circle cx="12.75" cy="8.85" r=".2" class="accent-fill"/>',
    };

    return '<g class="icon-signature">' . implode('', $marks) . '</g>';
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
