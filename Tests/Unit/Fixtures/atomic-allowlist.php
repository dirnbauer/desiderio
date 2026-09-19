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
];
