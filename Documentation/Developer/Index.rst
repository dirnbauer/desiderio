..  include:: /Includes.rst.txt

..  _developer:

==============
For developers
==============

..  _developer-architecture:

Architecture
============

Desiderio is intentionally layered:

..  code-block:: text
    :caption: Rendering layers

    Theme layer
      Page templates, backend layouts, header, footer, site settings

    Content element layer
      255 Content Blocks with editor-facing fields and fixtures

    Component layer
      Fluid 5 atoms, molecules, and layouts with typed arguments

The component layer currently contains 16 atoms, 18 molecules, and 4
layout primitives. Content elements compose those primitives instead of
hardcoding one-off markup.

..  _developer-sets:

Site sets
=========

The extension ships separate TYPO3 site sets so optional integrations can
be enabled only when needed:

..  list-table::
    :header-rows: 1
    :widths: 35 65

    *   - Set
        - Purpose
    *   - ``webconsulting/desiderio``
        - Base theme, tokens, TypoScript, page rendering, assets.
    *   - ``webconsulting/desiderio-content-elements``
        - Generated Content Block registration.
    *   - ``webconsulting/desiderio-preset-corporate``
        - Corporate demo/site preset.
    *   - ``webconsulting/desiderio-shadcnui-templates``
        - Shared page templates and backend layouts.
    *   - ``webconsulting/desiderio-blog``
        - Blog templates, RSS headers, and Blog TypoScript.
    *   - ``webconsulting/desiderio-news``
        - News templates and TypoScript.
    *   - ``webconsulting/desiderio-powermail``
        - Powermail templates and shadcn form classes.
    *   - ``webconsulting/desiderio-solr``
        - Solr result templates and suggest endpoint.

..  _developer-assets:

Frontend assets
===============

Desiderio avoids heavy runtime dependencies. The committed frontend
assets live in ``Resources/Public``:

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   - File
        - Responsibility
    *   - ``Css/shadcn-theme.css``
        - shadcn/create tokens, house presets, dark mode, chart tokens,
          layout variables, and global component classes.
    *   - ``Js/desiderio.js``
        - Generic interactions, Solr suggestions, search controls, and
          small UI behavior.
    *   - ``Js/astro.js``
        - Lightweight progressive behavior for counters, carousels,
          reveals, countdowns, galleries, and timelines.
    *   - ``Js/prism-lite.js``
        - Syntax highlighting for code examples.
    *   - ``Js/charts.js``
        - Chart rendering helpers.
    *   - ``Js/alpine.min.js``
        - Alpine runtime where existing blocks need it.

..  _developer-shadcn:

shadcn/create sync
==================

The project uses ``https://ui.shadcn.com/create`` as the visual source of
truth. React components are not copied into the TYPO3 frontend.

Use ``npm run shadcn:sync-fluid`` when upstream component class contracts
need to be synchronized into the shared Fluid primitives. Runtime theme
presets are stored in ``Resources/Public/Css/shadcn-theme.css`` and
selected with ``desiderio.shadcn.preset``.

..  _developer-integrations:

Extension templates
===================

Optional extension templates are kept in dedicated folders:

..  list-table::
    :header-rows: 1
    :widths: 35 65

    *   - Folder
        - Contains
    *   - ``Resources/Private/Extensions/Blog``
        - Blog templates, layouts, partials, comments, widgets, metadata,
          author, related posts, and RSS rendering.
    *   - ``Resources/Private/Extensions/News``
        - News list/detail templates, image partials, categories, tags,
          metadata, pagination, and schema-oriented markup.
    *   - ``Resources/Private/Extensions/Powermail``
        - Powermail form and field templates with shared shadcn classes.
    *   - ``Resources/Private/Solr``
        - Search result templates and partials.
    *   - ``Resources/Private/Form``
        - TYPO3 Form Framework form definitions and templates.

..  _developer-quality-bar:

Quality bar
===========

The CI workflow checks Composer validity, dependency security, PHPStan,
PHPUnit, and the Content Block audit. The audit keeps categories such as
``template_undeclared_field``, ``hardcoded_inline_style``, and
``hardcoded_color`` at zero.

Run the local checks before shipping code changes:

..  code-block:: shell
    :caption: Local checks

    composer validate
    Build/Scripts/runTests.sh

For targeted checks:

..  code-block:: shell
    :caption: Targeted checks

    Build/Scripts/runTests.sh phpstan
    Build/Scripts/runTests.sh phpunit
    Build/Scripts/runTests.sh audit

..  _developer-reports:

Reports
-------

``Documentation/Reports/`` contains the latest project audit reports for
TYPO3 conformance, security, workspaces, testing, docs, and broader
security review. Use those reports as context before changing TCA,
Fluid, TypoScript, or seed scripts.
