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
        - Runtime token preset. Supports five ui.shadcn.com/create presets, ten
          house presets, and ``custom``.
    *   - ``desiderio.shadcn.style``
        - ``radix-lyra``
        - Source style metadata from the create page on ui.shadcn.com.
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

..  _configuration-presets-per-page:

Per-page preset override
------------------------

Every page record offers :guilabel:`Theme preset (this page and subpages)`
in the :guilabel:`Appearance` tab (``pages.tx_desiderio_shadcn_preset``).
A non-empty value wins over the site setting for that page and is inherited
by all subpages (TypoScript ``levelfield:-1, tx_desiderio_shadcn_preset,
slide``). Leave the field empty to fall back to the parent page or
``desiderio.shadcn.preset``. The styleguide seeder uses this field to give
each demo chapter its own theme.

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
    *   - ``desiderio.forms.brevo.doubleOptInTemplateId``
        - empty
        - Brevo DOI email template id; enables double opt-in for the
          newsletter form when set together with list ids.
    *   - ``desiderio.forms.brevo.doubleOptInRedirectUrl``
        - empty
        - URL shown after the subscriber confirms the DOI email.

Set ``BREVO_API_KEY`` outside the repository when Brevo is enabled.

Newsletter registration via Brevo
---------------------------------

The bundled ``DesiderioNewsletter`` form (rendered by the
``newsletter-signup``, ``newsletter-inline``, and ``footer-newsletter``
elements) carries the ``BrevoContact`` finisher with ``doubleOptIn: true``.
With ``desiderio.forms.brevo.enabled``, ``BREVO_API_KEY``, list ids, and a
DOI template id in place, signups go through Brevo's double opt-in flow
(``POST /v3/contacts/doubleOptinConfirmation``): the contact only joins the
configured lists after confirming the email. Without a DOI template the
finisher falls back to the direct contact sync, so signups are never lost.

..  _configuration-friendly-captcha:

Friendly Captcha
================

Desiderio integrates with the ``studiomitte/friendlycaptcha`` extension.
For TYPO3 14, install the maintained fork from
`github.com/dirnbauer/friendlycaptcha-typo3
<https://github.com/dirnbauer/friendlycaptcha-typo3>`__:

..  code-block:: json
    :caption: composer.json (root project)

    {
        "repositories": [
            { "type": "vcs", "url": "https://github.com/dirnbauer/friendlycaptcha-typo3.git" }
        ]
    }

..  code-block:: bash

    composer require "studiomitte/friendlycaptcha:^14.0@dev"

When the extension is installed and configured, the real Friendly Captcha
widget renders and validates as usual — Desiderio never removes it, it only
overrides the rendering in the two situations below.

Production sites need real Friendly Captcha site and API keys. Outside of
production the bypass works like this:

*   **Development context (ddev):** the captcha is bypassed automatically —
    forms work out of the box without keys. No setting needed.
*   ``desiderio.forms.friendlyCaptchaForceReal``: forces the real widget and
    validation even in Development, e.g. to test real keys on a ddev site.
    Wins over the automatic bypass and over test mode.
*   ``desiderio.forms.friendlyCaptchaTestMode``: explicit bypass for other
    non-production contexts (e.g. Testing).
*   **Production:** the real captcha always runs; a test-mode setting left
    on by mistake is ignored and logged.

While bypassed, the forms render a decorative, inert verification widget
(a disabled button that does nothing) instead of the real puzzle, and
validation always passes. The request middleware maps the bypass to the
Friendly Captcha skip flag used by TYPO3 Form Framework and Powermail.

When ``studiomitte/friendlycaptcha`` is not installed at all, Desiderio
registers a fallback form setup
(:file:`Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml`): the
``Friendlycaptcha`` element of the bundled forms then renders the same
placeholder widget and its validator always passes, so demo forms keep
working without the extension. Install and configure the real extension
before collecting production traffic.

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

..  _configuration-rss:

Blog RSS headers
================

The Blog set registers RSS page types with explicit
``Content-Type: application/rss+xml; charset=utf-8`` headers for recent
posts, categories, tags, archive, comments, and author feeds.
