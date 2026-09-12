..  include:: /Includes.rst.txt

..  _configuration-page-templates:

==============
Page templates
==============

Every backend layout maps to one page template under
:file:`Resources/Private/Templates/Pages/`; a site preset can override the
template of the same name from its own ``lib.fluidPage.paths`` entry.

..  list-table::
    :header-rows: 1
    :widths: 32 22 46

    *   - Backend layout
        - Content areas
        - Page template
    *   - ``DesiderioStartpage``
        - ``main``
        - ``Pages/DesiderioStartpage.fluid.html``
    *   - ``DesiderioContentpage``
        - ``main``
        - ``Pages/DesiderioContentpage.fluid.html``
    *   - ``DesiderioContentpageSidebar``
        - ``main``, ``sidebar``
        - ``Pages/DesiderioContentpageSidebar.fluid.html``
    *   - ``DesiderioStyleguide``
        - ``main``
        - ``Pages/DesiderioStyleguide.fluid.html``
    *   - ``DesiderioBlog``
        - ``main``, ``sidebar``
        - ``Pages/DesiderioBlog.fluid.html``
    *   - ``DesiderioNews``
        - ``main``, ``sidebar``
        - ``Pages/DesiderioNews.fluid.html``
    *   - ``DesiderioExtension``
        - ``sidebar``, ``main``
        - ``Pages/DesiderioExtension.fluid.html``
    *   - Fallback
        - ``main``
        - ``Pages/Default.fluid.html``

The compact page title header is full width, uses a subtle themed background,
and inherits the active shadcn preset.

Page templates are the only layer that composes organisms
(:ref:`developer-atomic-design`): the site header, the site footer, the page
header and the breadcrumb.
