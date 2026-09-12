<?php

declare(strict_types=1);

/**
 * Known, reviewed exceptions of the atomic-design conformance test.
 *
 * Keyed by path relative to the extension root; the value maps the rule id
 * (A1 … A6, see AtomicDesignConformanceTest) to the reason the exception is
 * acceptable. An entry whose path or rule no longer matches a finding FAILS
 * the test, so this list can only shrink by fixing the template and deleting
 * the entry — never silently.
 */
return [
    // Data visualisations: the SVG *is* the content (bars, lines, arcs, heatmap
    // cells drawn from editor data), not an icon the atom could provide.
    'ContentBlocks/ContentElements/chart/templates/frontend.html' => ['A4' => 'chart drawing (data visualisation, not an icon)'],
    'ContentBlocks/ContentElements/chart-area/templates/frontend.html' => ['A4' => 'chart drawing (data visualisation, not an icon)'],
    'ContentBlocks/ContentElements/chart-bar/templates/frontend.html' => ['A4' => 'chart drawing (data visualisation, not an icon)'],
    'ContentBlocks/ContentElements/chart-contribution/templates/frontend.html' => ['A4' => 'contribution heatmap cells (data visualisation, not an icon)'],
    'ContentBlocks/ContentElements/chart-line/templates/frontend.html' => ['A4' => 'chart drawing (data visualisation, not an icon)'],
    'ContentBlocks/ContentElements/chart-pie/templates/frontend.html' => ['A4' => 'chart drawing (data visualisation, not an icon)'],
    'ContentBlocks/ContentElements/chart-sparkline/templates/frontend.html' => ['A4' => 'sparkline drawing (data visualisation, not an icon)'],
    'ContentBlocks/ContentElements/metric-dashboard/templates/frontend.html' => ['A4' => 'sparkline chart with role="img" (data visualisation, not an icon)'],
    // Brand marks the icon registry does not carry.
    'ContentBlocks/ContentElements/footer-app-links/templates/frontend.html' => ['A4' => 'App Store / Google Play store badges are brand marks, not registry icons'],
    // Image-backed overlay surface: min-height, items-end and overflow clip contradict the Card padding contract.
    'ContentBlocks/ContentElements/card-overlay/templates/frontend.html' => ['A4' => 'overlay card: image-backed surface whose min-height/items-end layout the Card molecule does not model'],
    // Extbase link ViewHelpers must own the anchor; the Card molecule renders a div.
    'Resources/Private/Extensions/News/Templates/News/Detail.html' => ['A4' => 'prev/next f:link.action anchors styled as cards; the link ViewHelper owns the element'],
    // EXT:solr hooks its AJAX behaviour on these exact elements (solr-ajaxified anchors, ids read by facet/sorting scripts).
    'Resources/Private/Solr/Partials/Result/FacetsActive.html' => ['A4' => 'solr-ajaxified remove-facet anchors: the active filters must be links (also the no-JS fallback), and the Badge atom has no href'],
    'Resources/Private/Solr/Partials/Result/Sorting.html' => ['A4' => 'sorting dropdown panel toggled by EXT:solr JS, not a card surface'],
    'Resources/Private/Solr/Partials/Search/LastSearches.html' => ['A4' => 'section landmark with the id EXT:solr expects (tx-solr-lastsearches)'],
    // Styleguide-only viewport switcher glyphs (desktop/tablet/phone) that no icon library ships consistently.
    'Resources/Private/Templates/Pages/DesiderioStyleguide.fluid.html' => ['A4' => 'viewport switcher device glyphs on the styleguide page'],
];
