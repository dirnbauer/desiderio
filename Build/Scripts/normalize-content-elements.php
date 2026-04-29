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
    $keywords = strtolower(str_replace('-', ' ', $slug . ' ' . $group . ' ' . $title));
    $iconTitle = xml($title . ' icon');
    $shape = match (true) {
        containsAny($keywords, ['accordion', 'faq']) => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5 5.5h5.5"/><path d="M5 8h4"/><path d="m10.5 10 1.4 1.4 1.4-1.4" class="accent"/>',
        containsAny($keywords, ['alert', 'callout', 'notification', 'status', 'emergency']) => '<path d="M8 2.75 13.5 12H2.5z"/><path d="M8 6v2.5"/><circle cx="8" cy="10.5" r=".55" class="accent-fill"/>',
        containsAny($keywords, ['audio']) => '<path d="M4 9.5H2.75v-3H4l2.5-2v7z"/><path d="M9 5.5c.7.7.7 4.3 0 5"/><path d="M11 4c1.5 1.9 1.5 6.1 0 8" class="accent"/>',
        containsAny($keywords, ['award', 'certification', 'badge']) => '<circle cx="8" cy="6" r="3.25"/><path d="m6.25 9 1.75 4 1.75-4"/><path d="m6.35 6 1.1 1.05L9.7 4.9" class="accent"/>',
        containsAny($keywords, ['back to top']) => '<path d="M8 12.5v-9"/><path d="m4.75 6.75 3.25-3.25 3.25 3.25"/><path d="M3.25 12.75h9.5" class="accent"/>',
        containsAny($keywords, ['booking', 'calendar', 'event', 'office hours']) => '<rect x="3" y="4" width="10" height="9" rx="2"/><path d="M3 6.5h10"/><path d="M5.5 2.75v2.5"/><path d="M10.5 2.75v2.5"/><circle cx="10.75" cy="10.25" r="1.35" class="accent"/>',
        containsAny($keywords, ['breadcrumb', 'steps', 'timeline', 'process', 'how to', 'onboarding', 'progress']) => '<circle cx="3.75" cy="8" r="1.25"/><circle cx="8" cy="8" r="1.25" class="accent"/><circle cx="12.25" cy="8" r="1.25"/><path d="M5 8h1.75M9.25 8H11"/>',
        containsAny($keywords, ['calculator', 'roi']) => '<rect x="4" y="2.75" width="8" height="10.5" rx="1.75"/><path d="M5.75 5.25h4.5"/><path d="M6 8h.1M8 8h.1M10 8h.1M6 10.5h.1M8 10.5h.1"/><path d="M10 10.5h.1" class="accent"/>',
        containsAny($keywords, ['carousel', 'slider']) => '<rect x="2.75" y="4" width="5.25" height="7.75" rx="1.4"/><rect x="8" y="3" width="5.25" height="9.75" rx="1.4" class="accent"/><path d="m3 8-1 1 1 1M13 8l1 1-1 1"/>',
        containsAny($keywords, ['code']) => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="m6.2 6.5-1.5 1.5 1.5 1.5"/><path d="m9.8 6.5 1.5 1.5-1.5 1.5"/><path d="m8.7 5.75-1.4 4.5" class="accent"/>',
        containsAny($keywords, ['columns', 'split']) => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M8 3.25v9.5"/><path d="M5 6h1.4M9.6 6H11"/><path d="M5 8.5h1.4M9.6 8.5H11" class="accent"/>',
        containsAny($keywords, ['contact', 'mail', 'newsletter', 'callback']) => '<rect x="2.75" y="4.25" width="10.5" height="7.5" rx="1.75"/><path d="m3.5 5 4.5 3.5L12.5 5"/><circle cx="11.8" cy="10.8" r="1.5" class="accent"/>',
        containsAny($keywords, ['cookie']) => '<circle cx="8" cy="8" r="4.75"/><circle cx="6.5" cy="6.25" r=".55" class="accent-fill"/><circle cx="9.75" cy="7.25" r=".55" class="accent-fill"/><circle cx="7.8" cy="10" r=".55" class="accent-fill"/>',
        containsAny($keywords, ['countdown', 'timer']) => '<circle cx="8" cy="8.5" r="4.25"/><path d="M6.5 2.75h3"/><path d="M8 8.5V5.75"/><path d="m8 8.5 2.1 1.2" class="accent"/>',
        containsAny($keywords, ['cta', 'offer', 'signup', 'request', 'download form', 'demo']) => '<rect x="3" y="4.5" width="10" height="7" rx="2"/><path d="M5.25 8h4.5"/><path d="m9.5 6.25 2 1.75-2 1.75" class="accent"/>',
        containsAny($keywords, ['data table', 'table']) => '<rect x="3" y="3.5" width="10" height="9" rx="1.5"/><path d="M3 6.5h10M3 9.5h10M6.5 3.5v9"/><path d="M10 3.5v9" class="accent"/>',
        containsAny($keywords, ['donut']) => '<circle cx="8" cy="8" r="4.5"/><path d="M8 3.5a4.5 4.5 0 0 1 4.5 4.5H8z" class="accent-fill"/>',
        containsAny($keywords, ['pie']) => '<path d="M8 3.5v4.75h4.75A4.75 4.75 0 1 1 8 3.5z"/><path d="M8 3.5a4.75 4.75 0 0 1 4.75 4.75H8z" class="accent"/>',
        containsAny($keywords, ['line chart', 'sparkline']) => '<path d="M3.25 12.5h9.5"/><path d="M3.75 10.5 6 8.25l2 1.5 3.75-4.25" class="accent"/><circle cx="11.75" cy="5.5" r="1"/>',
        containsAny($keywords, ['bar chart', 'stacked bar', 'stats bar']) => '<path d="M3.5 12.5h9"/><rect x="4" y="8" width="1.75" height="4.5" rx=".5"/><rect x="7.1" y="5.5" width="1.75" height="7" rx=".5" class="accent"/><rect x="10.2" y="3.75" width="1.75" height="8.75" rx=".5"/>',
        containsAny($keywords, ['heatmap', 'contribution']) => '<rect x="3" y="3" width="2.1" height="2.1" rx=".45"/><rect x="6.1" y="3" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="9.2" y="3" width="2.1" height="2.1" rx=".45"/><rect x="3" y="6.1" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="6.1" y="6.1" width="2.1" height="2.1" rx=".45"/><rect x="9.2" y="6.1" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="3" y="9.2" width="2.1" height="2.1" rx=".45"/><rect x="6.1" y="9.2" width="2.1" height="2.1" rx=".45" class="accent-fill"/><rect x="9.2" y="9.2" width="2.1" height="2.1" rx=".45"/>',
        containsAny($keywords, ['radar']) => '<path d="M8 3.25 12.25 6v4L8 12.75 3.75 10V6z"/><path d="M8 3.25v9.5M3.75 6l8.5 4M12.25 6l-8.5 4" class="accent"/><path d="m8 5.75 2.2 1.4v2L8 10.25 5.8 9.15v-2z"/>',
        containsAny($keywords, ['chart', 'analytics', 'metric', 'stats', 'kpi', 'counter', 'dashboard', 'leaderboard']) => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5.25 10.75V8.5"/><path d="M8 10.75V5.75" class="accent"/><path d="M10.75 10.75V7.25"/><path d="M5 12h6"/>',
        containsAny($keywords, ['definition', 'changelog', 'resource', 'article', 'blog', 'textmedia', 'content']) => '<path d="M4 2.75h5.25L12 5.5v7.75H4z"/><path d="M9.25 2.75V5.5H12"/><path d="M5.75 7.5h4.5M5.75 9.75h3" class="accent"/>',
        containsAny($keywords, ['divider']) => '<path d="M2.75 8h10.5"/><circle cx="8" cy="8" r="1.35" class="accent"/><path d="M4.5 5.5h7M4.5 10.5h7"/>',
        containsAny($keywords, ['embed', 'map', 'directions', 'location']) => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="M6.25 5.25 4.75 12M9.75 4l-1.5 7.5M6.25 5.25l3.5-1.25 1.5 1.25v7l-3-1-3.5 1.25v-7z" class="accent"/>',
        containsAny($keywords, ['file download', 'download']) => '<path d="M4.5 2.75h5L12 5.25v8H4.5z"/><path d="M9.5 2.75v2.5H12"/><path d="M8 6.75v4"/><path d="m6.25 9.25 1.75 1.75 1.75-1.75" class="accent"/>',
        containsAny($keywords, ['filter']) => '<path d="M3.25 4h9.5L9 8.35v3.4l-2 1v-4.4z"/><path d="M5.25 4h5.5" class="accent"/>',
        containsAny($keywords, ['footer', 'copyright', 'legal links']) => '<rect x="3" y="3.5" width="10" height="9" rx="2"/><path d="M5 6h6"/><path d="M5 8.25h3"/><rect x="5" y="10.25" width="6" height="1.25" rx=".625" class="accent-fill"/>',
        containsAny($keywords, ['gallery', 'image', 'product gallery']) => '<rect x="3" y="3.25" width="10" height="9.5" rx="2"/><path d="m5 10 2-2 1.5 1.5 1-1 2 2.5"/><circle cx="10.75" cy="5.75" r="1" class="accent"/>',
        containsAny($keywords, ['hero']) => '<rect x="2.75" y="3.25" width="10.5" height="9.5" rx="2"/><path d="M5 6.25h5.5"/><path d="M5 8.5h3.25"/><circle cx="11.5" cy="5.25" r="1.25" class="accent"/>',
        containsAny($keywords, ['logo cloud', 'logo carousel', 'logo grid', 'partner', 'client']) => '<rect x="3" y="4" width="3" height="2" rx=".7"/><rect x="8" y="4" width="5" height="2" rx=".7" class="accent"/><rect x="3" y="8" width="5" height="2" rx=".7" class="accent"/><rect x="10" y="8" width="3" height="2" rx=".7"/>',
        containsAny($keywords, ['menu', 'navbar', 'navigation', 'toc', 'sitemap']) => '<path d="M3 4.5h10"/><path d="M3 8h10"/><path d="M3 11.5h7"/><circle cx="12.25" cy="11.5" r="1.25" class="accent"/>',
        containsAny($keywords, ['privacy', 'gdpr', 'security', 'compliance', 'imprint', 'terms', 'accessibility']) => '<path d="M8 2.75 12 4.5v3.25c0 2.65-1.5 4.35-4 5.5-2.5-1.15-4-2.85-4-5.5V4.5z"/><path d="m6.25 8 1.15 1.15L10 6.5" class="accent"/>',
        containsAny($keywords, ['pricing', 'plan', 'billing', 'bundle', 'order summary']) => '<rect x="3" y="3" width="10" height="10" rx="2"/><path d="M5.25 6h4.5"/><path d="M5.25 8.25h5.5"/><path d="M5.25 10.5h2.5"/><circle cx="10.75" cy="10.75" r="1.25" class="accent"/>',
        containsAny($keywords, ['product', 'card', 'grid', 'category', 'library']) => '<rect x="3" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1.25" class="accent"/><rect x="3" y="8.75" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="8.75" width="4.25" height="4.25" rx="1.25"/>',
        containsAny($keywords, ['quote', 'testimonial', 'review', 'rating']) => '<path d="M4 5.25h7.5a1.5 1.5 0 0 1 1.5 1.5v3.5a1.5 1.5 0 0 1-1.5 1.5H7l-3 2v-2.5a1.5 1.5 0 0 1-1.5-1.5v-3A1.5 1.5 0 0 1 4 5.25z"/><path d="m9.75 2.75.45.9 1 .15-.72.7.17 1-.9-.48-.9.48.17-1-.72-.7 1-.15z" class="accent"/>',
        containsAny($keywords, ['search']) => '<circle cx="7" cy="7" r="3.5"/><path d="m9.75 9.75 3 3"/><path d="M5.25 7h3.5" class="accent"/>',
        containsAny($keywords, ['social']) => '<circle cx="4" cy="8" r="1.5"/><circle cx="11.5" cy="4.5" r="1.5" class="accent"/><circle cx="11.5" cy="11.5" r="1.5"/><path d="m5.35 7.35 4.8-2.25M5.35 8.65l4.8 2.25"/>',
        containsAny($keywords, ['team', 'member', 'founder', 'advisor', 'board', 'profile', 'office', 'job', 'career', 'org chart']) => '<circle cx="6" cy="5.25" r="2"/><path d="M2.75 12.5c.55-2 1.7-3 3.25-3s2.7 1 3.25 3"/><circle cx="11.25" cy="6.5" r="1.5" class="accent"/><path d="M9.5 12.25c.35-1.2 1-1.8 2-1.8s1.65.6 2 1.8" class="accent"/>',
        containsAny($keywords, ['video']) => '<rect x="3" y="4" width="10" height="8" rx="2"/><path d="m7 6.5 3.25 1.5L7 9.5z" class="accent-fill"/>',
        default => '<rect x="3" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="3" width="4.25" height="4.25" rx="1.25"/><rect x="3" y="8.75" width="4.25" height="4.25" rx="1.25"/><rect x="8.75" y="8.75" width="4.25" height="4.25" rx="1.25" class="accent"/>',
    };

    return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round">
  <title>{$iconTitle}</title>
  <style>.accent{stroke:var(--icon-color-accent,currentColor);fill:none}.accent-fill,.no-stroke{stroke:none;fill:var(--icon-color-accent,currentColor)}</style>
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
