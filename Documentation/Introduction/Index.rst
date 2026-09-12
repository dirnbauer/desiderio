..  include:: /Includes.rst.txt

..  _introduction:

============
Introduction
============

Desiderio is a TYPO3 v14.3 theme extension with a shadcn/ui-inspired Fluid 5
component library, 244 Desiderio Content Blocks, page templates, optional
Blog/News/Solr/Powermail overrides, and a runtime theme system driven by TYPO3
site settings.

It is built for installations that need a complete editorial and marketing
component set without a JavaScript component runtime. The committed assets are
the Tailwind v4 and shadcn CSS theme, lightweight progressive JavaScript, a
small syntax-highlighting bundle and chart helpers. Asset delivery uses Simon
Praetorius' Vite integration; Desiderio contains no manifest reader or
dev-server detection of its own.

..  _introduction-layers:

What is included
================

..  list-table::
    :header-rows: 1
    :widths: 26 74

    *   - Layer
        - Contents
    *   - Components
        - 17 atoms, 35 molecules, 4 layout primitives and 4 organisms —
          60 typed Fluid components (:ref:`developer-atomic-design`).
    *   - Content Blocks
        - 244 editor-facing elements grouped for heroes, features, data,
          conversion, editorial, media, social proof, navigation, forms and
          footer patterns.
    *   - Theme
        - Backend layouts, page templates, header and footer, CSS variables,
          JavaScript interactions and site settings.

Images are rendered through TYPO3 FAL and Content Blocks fields. Media
templates use stable aspect-ratio wrappers and ``object-fit`` rules, so images
are never stretched.

..  _introduction-theming:

Runtime theming
===============

The active look is selected in site settings and rendered as ``data-*``
attributes on the ``<body>`` element, so changing a preset repaints colors,
radius, density, focus rings, fonts and icon library behaviour at runtime
without touching content. Stored content uses semantic icon keys, so the icon
library can change without rewriting records.

The shadcn base is ``radix-lyra`` with the ``b6G5977cw`` mono olive preset and
Tabler icons; the selector also offers four other ui.shadcn.com/create presets,
ten bundled house presets and ``custom``. The settings are listed under
:ref:`configuration-site-settings`.

..  _introduction-screenshots:

..  figure:: /Images/TerminalFrontendLive.png
    :alt: A Desiderio page rendered in the frontend

    A seeded page rendered with the default preset.
