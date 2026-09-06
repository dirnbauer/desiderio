..  include:: /Includes.rst.txt

..  _developer-atomic-design:

Atomic design layers
====================

Desiderio composes typed Fluid components into Content Blocks and page shells:

..  code-block:: text
    :caption: Component composition

    Layouts        Section, Container, Grid, Stack
    Atoms          Button, Typography, Badge, Icon, Input, …
    Molecules      Card, Field, Alert, Tabs, Table, …
    Organisms      Shared site-level component groups
    Content Blocks Editor-facing compositions and field wiring
    Page templates Page shells and extension integration layouts

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
*   ``Tests/Unit/ContentBlockStructureTest.php`` checks structural contracts.
*   ``Tests/Unit/ContentElementAuditTest.php`` checks fields, tokens, and styles.
*   Inspect changed elements in the styleguide at multiple viewport widths
    and in light and dark mode.
