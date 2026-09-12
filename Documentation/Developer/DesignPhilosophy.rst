..  include:: /Includes.rst.txt

..  _developer-design-philosophy:

Design philosophy
=================

..  _developer-design-layers:

The three rendering layers
--------------------------

..  list-table::
    :header-rows: 1
    :widths: 20 34 46

    *   - Layer
        - Source
        - Contract
    *   - Components
        - :file:`Resources/Private/Components/`
        - Fluid 5 atoms, molecules, layouts and organisms with typed
          arguments, called through the ``d:`` component namespace.
    *   - Content elements
        - :file:`ContentBlocks/ContentElements/`
        - 244 editor-facing Content Blocks that compose those components and
          expose their fields through TCA.
    *   - Theme
        - :file:`Configuration/Sets/`, :file:`Resources/Private/Templates/Pages/`
        - Site settings, backend layouts, page shells, header and footer, and
          the optional integration templates.

The component inventory is enforced by ``Tests/Unit/ComponentStructureTest``
and the layering by ``Tests/Unit/AtomicDesignConformanceTest``. Content Blocks
use :file:`config.yaml`, :file:`templates/frontend.html`,
:file:`templates/backend-preview.fluid.html`, :file:`language/labels.xlf` and
:file:`fixture.json`; the structural tests and the content element audit
enforce those contracts.

The base and content-element site sets provide the shared rendering. Scenario
presets select defaults; Blog, News, Solr and Powermail activate through their
own site sets when the matching extension is installed
(:ref:`configuration-extensions`).

Collection fields have distinct parent-field identifiers. Shared child tables
use explicit Record Types with matching field definitions and both
``shareAcrossTables`` and ``shareAcrossFields``; existing content is migrated
by the ``desiderioSharedCollectionTables`` upgrade wizard
(:ref:`developer-collection-contract`).

..  _developer-design-contracts:

Element contracts
-----------------

The 244 Desiderio Content Blocks share component and token contracts.
Structural checks live in ``scripts/audit-content-elements.php`` and the
unit tests; theme contrast checks live in
``Build/Scripts/audit-theme-contrast.php``. The browser harness under
``Build/VisualQa/`` checks the rendered compositions.

..  _developer-design-color:

Semantic colors
---------------

Elements use roles such as ``--background``, ``--card``,
``--muted-foreground``, ``--primary``, ``--destructive``, and chart tokens.
The active preset defines their light and dark values.

*   Use the shared tokens rather than raw colors in element CSS and templates.
*   Status text uses the corresponding ``--d-success-text``,
    ``--d-warning-text``, or ``--d-danger-text`` token on tinted surfaces.
*   Follow the page's ``.dark`` class; an element-specific
    ``prefers-color-scheme`` query would bypass the visitor's theme choice.
*   Check composed colors and text over images in the browser. Token contrast
    checks alone cannot establish a complete site's accessibility.

..  _developer-design-type:

Typography and spacing
----------------------

Use the ``--d-text-*`` scale, semantic font families, and the shared
leading, weight, and tracking tokens from
``Resources/Public/Css/shadcn-theme.css``.

..  list-table::
    :header-rows: 1

    *   - Token
        - Role
    *   - ``--d-leading-tight`` / ``--d-leading-snug``
        - Display headings / card titles and compact headings.
    *   - ``--d-leading-normal`` / ``--d-leading-relaxed``
        - UI text / paragraph-length copy.
    *   - ``--d-weight-display``
        - Prominent numerals, prices, and counters.
    *   - ``--d-tracking-wide`` / ``--d-tracking-tight``
        - Eyebrows / large display text.

Spacing follows the ``--d-spacing-*`` ramp. Heroes share ``--d-hero-y``;
section composition uses the shared Section component. The audit permits
layout-specific exceptions such as zero values, percentages, and optical
``em`` offsets while rejecting unapproved raw typography and spacing values.

..  _developer-design-layout:

Layout and chart geometry
-------------------------

``Resources/Private/Css/desiderio/18-section-anatomy.css`` defines shared
reading measures and section anatomy with low-specificity selectors.
Section intros cap at 70ch; running text caps at 75ch. Keep an intro's
eyebrow, heading, and subheadline aligned as one unit. A centered, capped
paragraph also needs automatic inline margins.

Use the shared breakpoint set and stack content-sized grid columns on small
screens. Charts share SVG geometry and ``--d-chart-plot-height``; derive a
chart's maximum width from its viewBox ratio instead of constraining height
in a way that distorts the drawing or leaves unused space.

..  _developer-design-editor:

Editor controls
---------------

``Desiderio/Appearance`` connects frame and outer-spacing controls to
``<d:layout.section>``. An editor's frame choice overrides the template's
default surface. Elements without a section wrapper do not expose those
controls. The legacy ``layout`` field remains explicitly marked as reserved
and currently has no visual effect.

Declared fields must affect rendering, selected variants must change their
output, and shared Collection models must preserve their ownership fields.
Render editable text through ``f:render.text`` and FAL images through TYPO3
image ViewHelpers. See :ref:`developer-atomic-design` and
:ref:`developer-collection-contract` for the component and collection rules.

..  _developer-design-verification:

Verification after an element change
------------------------------------

..  code-block:: shell
    :caption: Check structure, contrast, and generated CSS in DDEV

    ddev exec Build/Scripts/runTests.sh -s audit
    ddev exec php Build/Scripts/audit-theme-contrast.php
    ddev exec npm run build

Run the contrast check when changing theme tokens. For browser checks, obtain
the current preview URLs from ``desiderio:library:urls --json`` and use them
with ``Build/VisualQa/anatomy.mjs`` and ``Build/VisualQa/run.mjs``. Inspect the
changed elements at desktop and mobile widths, in both light and dark mode.
