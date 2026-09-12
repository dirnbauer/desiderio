..  include:: /Includes.rst.txt

..  _configuration-icon-fonts:

==========
Icon fonts
==========

All icon webfonts are **self-hosted inside the package**: no CDN requests, no
external dependencies, no GDPR exposure. Content icons themselves render as
inline SVG (``IconViewHelper`` emits one ``<svg>`` per library and CSS reveals
the one matching ``body[data-icon-library]``), so the webfonts are an
additional offering for editors and custom markup, loaded per configured
library.

..  _configuration-icon-fonts-loading:

How loading works
=================

The page layout resolves the configured library to a bundled stylesheet:

..  code-block:: html
    :caption: Resources/Private/Templates/Layouts/Pages/Default.fluid.html

    <f:asset.css
        identifier="desiderioIconFont"
        href="{di:iconFont(library: site.configuration.settings.desiderio.shadcn.iconLibrary)}"
        priority="1"
    />

``di:iconFont`` calls ``IconRegistry::fontStylesheet()``, which maps every
supported library to
:file:`EXT:desiderio/Resources/Public/IconFonts/<library>/<library>.css`. Each
directory contains exactly three files: the stylesheet (rewritten to a single
relative ``woff2`` source), the ``woff2`` font, and the upstream license.

..  _configuration-icon-fonts-licensing:

Licensing
=========

Every bundled font was license-checked for redistribution inside a
distributable TYPO3 package (verified June 2026):

..  list-table::
    :header-rows: 1
    :widths: 20 15 35 30

    *   - Library
        - License
        - Bundled from
        - Redistribution
    *   - Lucide
        - ISC
        - ``lucide-static``
        - allowed
    *   - Tabler Icons
        - MIT
        - ``@tabler/icons-webfont``
        - allowed
    *   - Phosphor Icons
        - MIT
        - ``@phosphor-icons/web`` (regular weight)
        - allowed
    *   - Remix Icon
        - Apache-2.0
        - ``remixicon``
        - allowed
    *   - HugeIcons
        - proprietary font / MIT SVG data
        - generated in-house, see below
        - official font **forbidden**, own build allowed

..  warning::

    **HugeIcons is the special case.** The official hugeicons webfont must not
    be redistributed — the `license agreement
    <https://hugeicons.com/license-agreement>`__ explicitly covers the free
    versions and forbids shipping their icon fonts in downloadable packages.
    Their SVG icon *data* (``@hugeicons/core-free-icons``) is MIT, so Desiderio
    compiles its own webfont from it:

    #.  all 6,156 free icons are exported as stroke SVGs from the MIT data,
    #.  strokes are outlined into filled paths with ``picosvg`` (icon fonts
        cannot render strokes; the venv lives in :file:`var/picosvg-venv`),
    #.  ``fantasticon`` compiles ``hugeicons.woff2`` and ``hugeicons.css`` with
        explicit codepoints in the Unicode Private Use Area (U+E001 ff. — the
        default numbering would overflow past U+FFFF and silently drop glyphs).

    The result is Desiderio's own MIT-licensed build, *not* the official
    hugeicons webfont, and
    :file:`Resources/Public/IconFonts/hugeicons/LICENSE-MIT.txt` documents that
    provenance. Never re-add the hugeicons CDN link or copy their official font
    files into the package; ``IconRegistryTest`` pins both rules.

..  _configuration-icon-fonts-updating:

Updating the fonts
==================

..  code-block:: bash

    npm run build:iconfonts       # re-sync Lucide/Tabler/Phosphor/Remix from node_modules
    npm run build:hugeicons-font  # regenerate the HugeIcons font from MIT SVG data

Run these after bumping the corresponding npm packages. The HugeIcons build
caches its outlining step in :file:`var/hugeicons-font/`; delete that directory
to force a full rebuild (~10 minutes for all icons).

..  _configuration-icon-fonts-usage:

Usage
=====

Font classes follow each library's upstream conventions, for example
``<i class="hgi hgi-home-01"></i>`` (HugeIcons build), ``.icon-*`` (Lucide),
``.ti ti-*`` (Tabler), ``.ph ph-*`` (Phosphor) and ``.ri-*`` (Remix Icon).

For content elements prefer the semantic ``d:atom.icon`` component: it stays
library-agnostic and switches with the preset.
