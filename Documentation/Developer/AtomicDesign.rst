..  include:: /Includes.rst.txt

..  _developer-atomic-design:

Atomic design layers
====================

Desiderio composes typed Fluid components into Content Blocks and page shells:

..  code-block:: text
    :caption: Component composition

    Layouts        Section, Container, Grid, Stack
    Atoms          Button, Typography, Badge, Icon, Input, …
    Molecules      Card, Field, Alert, Tabs, Table, SectionIntro, ActionGroup,
                   Figure, FeatureItem, Stat, Pagination, CaptchaPlaceholder, …
    Organisms      SiteHeader, SiteFooter, PageHeader, Breadcrumb
    Content Blocks Editor-facing compositions and field wiring
    Page templates Page shells and extension integration layouts

Components live in :file:`Resources/Private/Components/<Layer>/<Name>/<Name>.fluid.html`
and are called through the ``d:`` namespace
(``xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"``),
which ``Classes/Components/ComponentCollection.php`` resolves.

..  _developer-atomic-design-graph:

The layer graph
---------------

The direction of composition is one-way, and
``Tests/Unit/AtomicDesignConformanceTest.php`` fails the build when it is not:

..  list-table::
    :header-rows: 1
    :widths: 24 76

    *   - Layer
        - May compose
    *   - ``Atom``
        - Nothing from the component library (ViewHelpers and markup only).
    *   - ``Molecule``
        - Atoms and other molecules — never an organism.
    *   - ``Layout``
        - Nothing from the component library.
    *   - ``Organism``
        - Atoms, molecules, layouts.
    *   - Content element
        - Atoms, molecules, layouts — never an organism, and no partials.
    *   - Page template / preset
        - Everything, including organisms.

The same test keeps raw atomic markup out of templates: a button-, card- or
badge-shaped ``class`` attribute, a styled heading, or an inline ``<svg>``
where ``d:atom.icon`` has the icon, is a finding. The reviewed exceptions live
in :file:`Tests/Unit/Fixtures/atomic-allowlist.php` with a reason each, and an
allowlist entry that no longer matches a finding fails the test too — so the
list can only shrink.

..  _developer-atomic-design-rules:

Component conventions
---------------------

*   Render shared controls through atoms such as ``<d:atom.button>`` and
    ``<d:atom.icon>``. Use a distinct control only when its behavior differs.
*   Compose cards through ``<d:molecule.card>`` and its slots. Reuse the
    shared border, color, typography, and state rules.
*   Use layouts for section and container structure; keep element-specific
    CSS focused on composition.
*   Select supported component variants before introducing another copy of
    a shared button, badge, card, or link.
*   Keep FAL image rendering and editable text in the TYPO3 ViewHelpers
    documented in :ref:`developer-adding-content-elements`.

The source migration to atomic primitives is complete. Edit the maintained
components and templates directly, then run the structural and audit checks.

..  _developer-atomic-design-references:

Reference implementations
-------------------------

*   ``ContentBlocks/ContentElements/pricing-simple/``
*   ``ContentBlocks/ContentElements/pricing-annual-monthly/``
*   ``ContentBlocks/ContentElements/pricing-three-tier/``
*   ``ContentBlocks/ContentElements/cta/``

..  _developer-atomic-design-testing:

Verification
------------

*   ``Tests/Unit/ComponentStructureTest.php`` checks the component inventory.
*   ``Tests/Unit/AtomicDesignConformanceTest.php`` checks the layer graph, the
    content element contract and raw atomic markup.
*   ``Tests/Functional/Components/ComponentRenderingTest.php`` renders every
    component once, so a renamed or newly required ``<f:argument>`` fails here
    instead of on a page.
*   ``Tests/Functional/Templates/ShippedTemplatesLintTest.php`` parses every
    shipped template (:ref:`developer-template-lint`).
*   ``Tests/Unit/ContentBlockStructureTest.php`` checks structural contracts.
*   ``Tests/Unit/ContentElementAuditTest.php`` checks fields, tokens, and styles.
*   ``Build/Scripts/report-atomic-usage.php`` prints a read-only component
    histogram and the class signatures that recur across elements — the input
    for deciding whether something deserves its own molecule.
*   Inspect changed elements in the styleguide at multiple viewport widths
    and in light and dark mode.
