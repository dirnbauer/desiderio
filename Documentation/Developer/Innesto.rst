..  include:: /Includes.rst.txt

..  _developer-innesto:

==========================================
Grafting elements from shadcn registries
==========================================

`Innesto <https://github.com/dirnbauer/innesto>`__ is a companion extension
that grafts components from any `shadcn/ui registry
<https://registry.directory/>`__ — shadcn/ui, Magic UI, Origin UI, Aceternity
UI, … — onto Desiderio as additional Content Blocks elements:

..  code-block:: bash

    vendor/bin/typo3 innesto:add magicui/marquee --ai

Why it works well here:

*   One command fetches the registry item, converts its CSS and theme
    variables, scaffolds a complete element and registers it in the
    :guilabel:`New Content Element` wizard.
*   Grafted elements use the Desiderio semantic tokens, so they follow the
    active theme preset and dark mode automatically — no frontend build step.
*   The React-to-Fluid finishing pass is prompt-assisted (``--ai``) and
    reproducible.

What might not work: React markup and props always need a finishing pass
(automated or manual), npm and registry dependencies are not resolved, and
heavily interactive components — comboboxes, command palettes, drag-and-drop —
do not graft well, because they are state machines rather than documents. The
best results come from presentational components: marquees, logo clouds, bento
grids, animated lists.

Innesto registers itself as an element library provider, so its blocks appear
in the picker next to Desiderio's own (:ref:`element-library-hosts`).

See the `step-by-step manual with a worked example and screenshots
<https://github.com/dirnbauer/innesto/blob/main/Documentation/AddingContentElements.md>`__.
