..  include:: /Includes.rst.txt

=============
Configuration
=============

Site settings
=============

Desiderio exposes its design knobs as TYPO3 v14 *site settings*. Edit
them in *Site Management → Settings* on each site.

..  list-table::
    :header-rows: 1
    :widths: 35 25 40

    *   - Setting
        - Type
        - Purpose
    *   - ``desiderio.shadcn.preset``
        - String
        - Active shadcn/create preset id. Supported out of the box:
          ``b6G5977cw`` (default), ``b4hb38Fyj``, ``b0``,
          ``b3IWPgRwnI``.
    *   - ``desiderio.shadcn.style``
        - String
        - Style preset (``radix-lyra`` etc.) — must match the chosen
          preset id.
    *   - ``desiderio.layout.radius``
        - String
        - ``preset`` keeps the preset's `--radius`; otherwise pick a
          token from the design system.
    *   - ``desiderio.typography.fontSans``
        - String
        - ``preset`` keeps the preset font; otherwise pick from the
          token list.
    *   - ``desiderio.header.fixedPosition``
        - Boolean
        - Sticky header when truthy.

Backend layouts
===============

..  list-table::
    :header-rows: 1
    :widths: 30 25 45

    *   - Layout
        - Content areas
        - Page template
    *   - ``DesiderioStartpage``
        - ``stage``, ``main``
        - ``Pages/DesiderioStartpage.fluid.html``
    *   - ``DesiderioContentpage``
        - ``stage``, ``main``
        - ``Pages/DesiderioContentpage.fluid.html``
    *   - ``DesiderioContentpageSidebar``
        - ``stage``, ``main``, ``sidebar``
        - ``Pages/DesiderioContentpageSidebar.fluid.html``
    *   - ``DesiderioStyleguide``
        - ``main``
        - ``Pages/DesiderioStyleguide.fluid.html``
    *   - ``DesiderioBlog``
        - ``stage``, ``main``, ``sidebar``
        - ``Pages/DesiderioBlog.fluid.html``
    *   - ``DesiderioExtension``
        - ``stage``, ``sidebar``, ``main``
        - ``Pages/DesiderioExtension.fluid.html``
    *   - ``DesiderioNews``
        - ``stage``, ``main``, ``sidebar``
        - ``Pages/DesiderioNews.fluid.html``
