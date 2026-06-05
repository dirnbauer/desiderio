..  include:: /Includes.rst.txt

..  _developer-atomic-design:

Atomic design layers
====================

Desiderio follows atomic design with these Fluid layers:

..  code-block:: text

    Layouts     Section, Container, Grid, Stack
    Atoms       Button, Typography, Badge, Icon, Input, …
    Molecules   Card, Field, Alert, Tabs, Table, …
    Organisms   Content Blocks (255 CE templates)
    Templates   Page shells, extension overrides

Rules
-----

1. **Atoms first.** Buttons always render through ``<d:atom.button>`` unless a
   genuinely different control is required (for example icon-only toolbar
   actions). Typography uses ``<d:atom.typography>``; icons use
   ``<d:atom.icon>``.
2. **Molecules compose atoms.** Plan cards use ``<d:molecule.card>`` with
   ``cardHeader``, ``cardContent``, and ``cardFooter`` — not hand-rolled
   ``<article>`` markup with duplicated border/shadow CSS.
3. **Organisms compose molecules.** Content element templates only contain
   layout (grid, spacing) and field wiring. Per-element CSS covers layout
   only — not button colors, card borders, or typography scale.
4. **Layouts wrap sections.** Every content element starts with
   ``<d:layout.section>`` and ``<d:layout.container>``.
5. **Variants, not duplicates.** Use atom ``variant`` props (``default``,
   ``outline``, ``secondary``) instead of BEM modifiers like
   ``__button--primary``.

Reference implementations
-------------------------

*   **Gold standard:** ``ContentBlocks/ContentElements/card-pricing/``
*   **Pilot migrations:** ``bundle-pricing``, ``pricing-annual-monthly``

Migration order for pricing family
----------------------------------

1. Replace ``f:link.typolink`` CTAs with ``<d:atom.button href="…">``
2. Replace plan/article shells with ``<d:molecule.card>``
3. Replace ``<h2>``/``<p>`` headings with ``<d:atom.typography>``
4. Replace inline SVG checks with ``<d:atom.icon name="check">``
5. Replace custom badge spans with ``<d:atom.badge>``
6. Delete duplicated button/card CSS from ``assets/frontend.css``
7. Add the slug to ``ContentBlockStructureTest::testAtomComposedContentElements``
8. Verify in the styleguide pricing group

Testing
-------

*   ``Tests/Unit/ComponentStructureTest.php`` — component inventory
*   ``Tests/Unit/ContentBlockStructureTest.php`` — token policy + atom composition registry
*   ``Tests/Unit/ContentElementAuditTest.php`` — no hardcoded colors/styles
*   Manual: Desiderio styleguide → Plans & Pricing group at multiple breakpoints
