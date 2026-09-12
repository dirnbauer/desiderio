..  include:: /Includes.rst.txt

..  _developer-shadcn-sync:

===============================
ui.shadcn.com/create and Fluid
===============================

Desiderio uses the create page on ui.shadcn.com as its design source for
TYPO3 Fluid components. It does not ship React components in the frontend.

The default is the ``b6G5977cw`` preset with ``radix-lyra``, olive tokens,
square radius, and Tabler icons. Sites switch presets at runtime through
TYPO3 site settings.

..  _developer-shadcn-sync-presets:

Supported presets
=================

..  code-block:: text
    :caption: Values accepted by desiderio.shadcn.preset

    b0, b4hb38Fyj, b3IWPgRwnI, b6G5977cw, b27GcrRo,
    aurora, marine, forest, ember, bloom, lagoon, gold,
    midnight, blossom, citrus, custom

``desiderio.shadcn.style`` stores the source style metadata.
``desiderio.shadcn.iconLibrary`` controls icon rendering and supports Lucide,
Tabler, HugeIcons, Phosphor, and Remix Icon.

..  _developer-shadcn-sync-runtime:

Runtime model
=============

TYPO3 renders the selected values onto the ``<body>`` element as data
attributes. The token blocks in :file:`Resources/Public/Css/shadcn-theme.css`
then control light mode, dark mode, borders, radius, charts, typography,
focus rings, and surface elevation.

Content records store semantic icon keys, not library-specific SVG names, so
existing content stays stable when a site changes its icon library.

..  _developer-shadcn-sync-update:

Updating a preset
=================

#.  Inspect the preset on `ui.shadcn.com/create <https://ui.shadcn.com/create>`__.
#.  Record the preset id, source style, base colour, radius, font, chart
    tokens, and icon library.
#.  Add the light ``:root`` and dark ``.dark`` token blocks to the generator
    :file:`Build/Scripts/generate-shadcn-presets.php` and run ``npm run build``
    so :file:`Resources/Public/Css/shadcn-theme.css` and the preset samples are
    regenerated together.
#.  Add the preset id to
    :file:`Configuration/Sets/Desiderio/settings.definitions.yaml`.
#.  Add or update the icon-library mapping in
    :file:`Classes/Icon/IconRegistry.php` when needed.
#.  Run ``npm run shadcn:sync-fluid`` if shared component class contracts
    changed.
#.  Run the project checks (:ref:`developer-quality-bar`).

Do not add one-off colours or component-specific style overrides to content
element templates. A visual rule that belongs to shadcn/ui moves into a shared
Fluid component, a CSS token, or a generated class partial.

..  _developer-shadcn-sync-ownership:

Component ownership
===================

Registry-backed primitives are synchronized into the Fluid components under
:file:`Resources/Private/Components`. Buttons, badges, labels, inputs,
selects, textareas, tabs, accordions, cards, and form classes belong there.

Semantic TYPO3 primitives stay local where shadcn/ui provides no registry
contract: page templates, extension partials, Blog widgets, News metadata,
Solr results, and Content Block composition.

..  _developer-shadcn-sync-media:

Media rules
===========

Content Block image fields render through TYPO3 Fluid image ViewHelpers:
``<f:image>`` for markup and ``f:uri.image()`` for processed URLs in
JavaScript data attributes. Literal ``<img>`` tags for FAL ``FileReference``
objects are not allowed.

..  _developer-shadcn-sync-verification:

Verification
============

After changing presets or shared primitives, verify:

*   light and dark mode,
*   button, badge, form, card, tab, accordion, and search states,
*   Blog and News list/detail pages,
*   Powermail and TYPO3 Form Framework forms,
*   Solr result and suggest output,
*   Content Block chart, code, image, carousel, and timeline elements,
*   backend previews and workspace previews.
