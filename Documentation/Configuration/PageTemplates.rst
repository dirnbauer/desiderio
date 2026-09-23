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

..  _configuration-page-heading:

One h1 per page
===============

Content page templates print the page title as the page's only
:html:`<h1>` through the page header organism
(:html:`<d:organism.pageHeader>`); the start page keeps it for screen readers
only. Rich text starts at :html:`<h2>`: the ``desiderio`` RTE preset offers
headings 2 to 6.

Some content renders the :html:`<h1>` itself, such as a news article or a
plugin detail view. On those pages the page header stands down. Desiderio
decides this with the TypoScript registry
:typoscript:`lib.pageHeadingOwnedByContent`, a :typoscript:`COA`: when any of
its entries renders something, :typoscript:`PAGEVIEW` sets the template
variable ``pageHeadingOwnedByContent`` to ``1`` and the page header (and the
blog archetype's list heading) is left out.

Desiderio registers the routed EXT:news detail view (key ``10``; keys 10 to
99 are reserved for Desiderio). EXT:skillflow 1.8.1 registers its
:guilabel:`Skill detail` plugin from its own :file:`ext_localconf.php`, without
depending on Desiderio.

Add your own entry from a site package or extension with a key of 100 or
higher that is unique to it. By page:

..  code-block:: typoscript
    :caption: EXT:site_package/Configuration/Sets/SitePackage/setup.typoscript

    lib.pageHeadingOwnedByContent.110 = TEXT
    lib.pageHeadingOwnedByContent.110 {
      value = 1
      if.value = 1066,1067
      if.isInList.data = page:uid
    }

By plugin argument, for a detail view that only owns the heading when it shows
a record:

..  code-block:: typoscript

    lib.pageHeadingOwnedByContent.120 = TEXT
    lib.pageHeadingOwnedByContent.120 {
      value = 1
      if.isTrue.data = GP:tx_myext_detail|record
    }

By content element, for a plugin that always renders an :html:`<h1>`:

..  code-block:: typoscript

    lib.pageHeadingOwnedByContent.130 = TEXT
    lib.pageHeadingOwnedByContent.130 {
      value = 1
      if.isTrue.numRows {
        table = tt_content
        select {
          pidInList = this
          where = {#CType} = 'myext_detail'
        }
      }
    }

Never clear the registry with ``lib.pageHeadingOwnedByContent >``: entries that
extensions add before the Desiderio set is loaded would be lost. Custom page
templates pass the variable on:

..  code-block:: html

    <d:organism.pageHeader page="{page}"
        newsDetailUid="{newsDetailUid}"
        headingOwnedByContent="{pageHeadingOwnedByContent}"/>

The content that owns the heading must then render exactly one :html:`<h1>`.
