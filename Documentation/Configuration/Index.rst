..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

..  _configuration-site-settings:

Site settings
=============

Desiderio exposes its design knobs as TYPO3 v14 *site settings*. Edit
them in *Site Management → Settings* on each site.

..  list-table::
    :header-rows: 1
    :widths: 32 20 48

    *   - Setting
        - Default
        - Values
    *   - ``desiderio.layout.density``
        - ``comfortable``
        - ``compact``, ``comfortable``, ``spacious``
    *   - ``desiderio.layout.container``
        - ``wide``
        - ``narrow``, ``wide``, ``full``
    *   - ``desiderio.layout.radius``
        - ``preset``
        - ``preset``, ``none``, ``sm``, ``md``, ``lg``, ``full``
    *   - ``desiderio.header.style``
        - ``solid``
        - ``solid``, ``transparent``, ``glass``, ``sticky``
    *   - ``desiderio.header.fixedPosition``
        - ``false``
        - Boolean. Keeps the site header pinned while scrolling.
    *   - ``desiderio.footer.style``
        - ``columns``
        - ``columns``, ``centered``, ``minimal``, ``mega``
    *   - ``desiderio.theme.accent``
        - ``slate``
        - ``slate``, ``rose``, ``blue``, ``emerald``, ``amber``,
          ``violet``, ``custom``
    *   - ``desiderio.theme.darkModeDefault``
        - ``system``
        - ``light``, ``dark``, ``system``
    *   - ``desiderio.theme.darkModeToggle``
        - ``true``
        - Boolean. Renders the header colour-scheme toggle.
    *   - ``desiderio.shadcn.preset``
        - ``b6G5977cw``
        - ``b4hb38Fyj``, ``b0``, ``b3IWPgRwnI``, ``b6G5977cw``,
          ``custom``
    *   - ``desiderio.shadcn.style``
        - ``radix-lyra``
        - ``radix-nova``, ``radix-mira``, ``radix-lyra``, ``custom``
    *   - ``desiderio.shadcn.iconLibrary``
        - ``tabler``
        - ``lucide``, ``tabler``, ``phosphor``. Stored content keeps
          semantic icon keys and the renderer resolves them at runtime.
    *   - ``desiderio.typography.fontSans``
        - ``preset``
        - ``preset``, ``inter``, ``geist``, ``system``, ``serif``
    *   - ``desiderio.styleguide.enabled``
        - ``false``
        - Boolean. Enables the Styleguide page template.

The base setup renders these settings into the ``<body>`` element as
``data-*`` attributes, including ``data-shadcn-preset``,
``data-shadcn-style``, ``data-icon-library``, ``data-density``,
``data-container``, ``data-radius``, ``data-accent``, ``data-font``,
``data-header-style``, and ``data-footer-style``.

..  _configuration-backend-layouts:

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
    *   - Fallback
        - ``stage``, ``main``
        - ``Pages/Default.fluid.html``

``DesiderioBlog``, ``DesiderioExtension``, and ``DesiderioNews`` are
registered by the hidden ``webconsulting/desiderio-shadcnui-templates``
site set. The base set lists it as an optional dependency.
