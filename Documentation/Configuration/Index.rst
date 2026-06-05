..  include:: /Includes.rst.txt

..  _configuration:

=============
Configuration
=============

Desiderio is configured through TYPO3 v14 site settings and optional site
sets. Edit the values in :guilabel:`Site Management > Settings`.

..  _configuration-site-settings:

Core site settings
==================

..  list-table::
    :header-rows: 1
    :widths: 36 24 40

    *   - Setting
        - Default
        - Purpose
    *   - ``desiderio.shadcn.preset``
        - ``b6G5977cw``
        - Runtime token preset. Supports five shadcn/create presets, ten
          house presets, and ``custom``.
    *   - ``desiderio.shadcn.style``
        - ``radix-lyra``
        - Source style metadata from shadcn/create.
    *   - ``desiderio.shadcn.iconLibrary``
        - ``tabler``
        - Runtime icon library. Existing records keep semantic icon keys.
    *   - ``desiderio.layout.density``
        - ``comfortable``
        - ``compact``, ``comfortable``, or ``spacious``.
    *   - ``desiderio.layout.container``
        - ``wide``
        - ``narrow``, ``wide``, or ``full``.
    *   - ``desiderio.layout.radius``
        - ``preset``
        - ``preset``, ``none``, ``sm``, ``md``, ``lg``, or ``full``.
    *   - ``desiderio.typography.fontSans``
        - ``preset``
        - Uses the selected preset font unless overridden.
    *   - ``desiderio.theme.darkModeDefault``
        - ``system``
        - ``light``, ``dark``, or ``system``.
    *   - ``desiderio.theme.darkModeToggle``
        - ``true``
        - Renders the header colour-scheme toggle.
    *   - ``desiderio.header.style``
        - ``solid``
        - ``solid``, ``transparent``, ``glass``, or ``sticky``.
    *   - ``desiderio.header.fixedPosition``
        - ``false``
        - Keeps the header pinned while scrolling.
    *   - ``desiderio.footer.style``
        - ``columns``
        - ``columns``, ``centered``, ``minimal``, or ``mega``.
    *   - ``desiderio.styleguide.enabled``
        - ``false``
        - Enables the styleguide page template.

The base TypoScript renders these values as ``data-*`` attributes on the
``<body>`` element. CSS in ``Resources/Public/Css/shadcn-theme.css`` uses
those attributes to apply the active preset.

..  _configuration-presets:

shadcn presets
==============

``desiderio.shadcn.preset`` supports:

..  code-block:: text
    :caption: Supported preset ids

    b0, b4hb38Fyj, b3IWPgRwnI, b6G5977cw, b27GcrRo,
    aurora, marine, forest, ember, bloom, lagoon, gold,
    midnight, blossom, citrus, custom

The create presets are copied from ``https://ui.shadcn.com/create``. The
house presets reuse the same token model and vary accent, radius,
typography, density, focus ring, and icon library.

..  _configuration-search:

Search
======

..  list-table::
    :header-rows: 1
    :widths: 36 24 40

    *   - Setting
        - Default
        - Purpose
    *   - ``desiderio.search.enabled``
        - ``true``
        - Shows the header search form.
    *   - ``desiderio.search.targetPageId``
        - empty
        - Page uid of the search result page.
    *   - ``desiderio.search.queryParameter``
        - ``q``
        - Query parameter used by the search form.

When ``webconsulting/desiderio-solr`` is enabled, Desiderio also
registers a JSON suggest page type and shadcn-styled Solr result
templates.

..  _configuration-forms:

Forms
=====

..  list-table::
    :header-rows: 1
    :widths: 38 22 40

    *   - Setting
        - Default
        - Purpose
    *   - ``desiderio.forms.receiverAddress``
        - ``hello@example.com``
        - Default receiver for generated form fixtures.
    *   - ``desiderio.forms.receiverName``
        - ``Desiderio``
        - Receiver name.
    *   - ``desiderio.forms.senderAddress``
        - ``no-reply@example.com``
        - Sender address.
    *   - ``desiderio.forms.senderName``
        - ``Desiderio``
        - Sender name.
    *   - ``desiderio.forms.friendlyCaptchaTestMode``
        - ``false``
        - Simulates successful Friendly Captcha validation in TYPO3
          Development context.
    *   - ``desiderio.forms.brevo.enabled``
        - ``false``
        - Enables the Brevo contact finisher.
    *   - ``desiderio.forms.brevo.listIds``
        - empty
        - Comma-separated Brevo list ids.
    *   - ``desiderio.forms.brevo.strict``
        - ``false``
        - Fails the form when Brevo synchronization fails.
    *   - ``desiderio.forms.brevo.trackEvent``
        - ``false``
        - Sends a sanitized Brevo event after contact sync.
    *   - ``desiderio.forms.brevo.eventName``
        - ``form_submit``
        - Event name for Brevo tracking.

Set ``BREVO_API_KEY`` outside the repository when Brevo is enabled.

..  _configuration-friendly-captcha:

Friendly Captcha
================

Production sites need real Friendly Captcha site and API keys. For local
development, enable ``desiderio.forms.friendlyCaptchaTestMode`` in TYPO3
Development context. The request middleware maps that setting to the
Friendly Captcha skip flag used by TYPO3 Form Framework and Powermail.

..  _configuration-page-layouts:

Backend layouts
===============

..  list-table::
    :header-rows: 1
    :widths: 30 30 40

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
    *   - ``DesiderioNews``
        - ``stage``, ``main``, ``sidebar``
        - ``Pages/DesiderioNews.fluid.html``
    *   - ``DesiderioExtension``
        - ``stage``, ``sidebar``, ``main``
        - ``Pages/DesiderioExtension.fluid.html``
    *   - Fallback
        - ``stage``, ``main``
        - ``Pages/Default.fluid.html``

..  _configuration-rss:

Blog RSS headers
================

The Blog set registers RSS page types with explicit
``Content-Type: application/rss+xml; charset=utf-8`` headers for recent
posts, categories, tags, archive, comments, and author feeds.
