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
    *   - ``desiderio.theme.darkModeDefault``
        - ``system``
        - ``light``, ``dark``, ``system``
    *   - ``desiderio.theme.darkModeToggle``
        - ``true``
        - Boolean. Renders the header colour-scheme toggle.
    *   - ``desiderio.shadcn.preset``
        - ``b6G5977cw``
        - 5 create presets (``b0``, ``b4hb38Fyj``, ``b3IWPgRwnI``,
          ``b6G5977cw``, ``b27GcrRo``) plus 10 house presets (``aurora``,
          ``marine``, ``forest``, ``ember``, ``bloom``, ``lagoon``, ``gold``,
          ``midnight``, ``blossom``, ``citrus``) and ``custom``. See
          :ref:`configuration-theme-presets`.
    *   - ``desiderio.shadcn.style``
        - ``radix-lyra``
        - ``radix-vega``, ``radix-nova``, ``radix-maia``,
          ``radix-lyra``, ``radix-mira``, ``radix-luma``,
          ``radix-sera``, ``radix-rhea``, ``custom``
    *   - ``desiderio.shadcn.iconLibrary``
        - ``tabler``
        - ``lucide``, ``tabler``, ``hugeicons``, ``phosphor``,
          ``remixicon``. Stored content keeps semantic icon keys and the
          renderer resolves them at runtime.
    *   - ``desiderio.typography.fontSans``
        - ``preset``
        - ``preset``, ``inter``, ``geist``, ``system``, ``serif``
    *   - ``desiderio.styleguide.enabled``
        - ``false``
        - Boolean. Enables the Styleguide page template.
    *   - ``desiderio.forms.friendlyCaptchaTestMode``
        - ``false``
        - Boolean. Simulates a successful Friendly Captcha verification for
          Desiderio forms in TYPO3 Development context.

The base setup renders these settings into the ``<body>`` element as
``data-*`` attributes, including ``data-shadcn-preset``,
``data-shadcn-style``, ``data-icon-library``, ``data-density``,
``data-container``, ``data-radius``, ``data-font``,
``data-header-style``, and ``data-footer-style``.

Friendly Captcha testing
========================

Friendly Captcha does not provide a universal token or shared test key for
submitting protected forms. Production sites need a real Friendly Captcha
application with a site key and API key configured in *Site Management ->
Sites -> Friendly Captcha*.

For local form testing, enable ``desiderio.forms.friendlyCaptchaTestMode``.
During frontend requests Desiderio maps that setting to the Friendly Captcha
extension's ``friendlycaptcha_skip_dev_validation`` site configuration flag.
The extension only accepts that flag in TYPO3 Development context, so the
setting simulates a successful server-side verification without weakening a
Production context.

Automated end-to-end tests can also use Friendly Captcha's header bypass:
set an environment variable named ``FRIENDLYCAPTCHA_SKIP_HEADER_VALIDATION``
to a secret string of at least 30 characters and send the same value in the
``X-FriendlyCaptcha-Skip-Validation`` request header.

..  _configuration-theme-presets:

Theme presets
=============

A *theme preset* repaints the whole site — base and accent colour, corner
radius, fonts, icon family, control density, focus-ring width, and surface
elevation — at runtime. Choose one in *Site Management → Settings → Theme →
Theme preset* (the first settings group), save, and reload. The value is rendered onto
``<body data-shadcn-preset="…">`` and the matching variable block cascades from
:file:`Resources/Public/Css/shadcn-theme.css`. No rebuild is required, and a
site keeps its current look until an editor selects a different preset.

Five presets are full shadcn/create configurations (``b0``, ``b4hb38Fyj``,
``b3IWPgRwnI``, ``b6G5977cw``, ``b27GcrRo``). Ten *house* presets — ``aurora``,
``marine``, ``forest``, ``ember``, ``bloom``, ``lagoon``, ``gold``,
``midnight``, ``blossom``, ``citrus`` — inherit the neutral base and vary the
accent colour, radius, fonts, icon family, density, focus-ring width, and
surface elevation.

..  list-table:: What a preset controls
    :header-rows: 1
    :widths: 35 65

    *   - Dimension
        - Mechanism
    *   - Base and accent colour
        - Per-preset ``:root`` / ``.dark`` token block (light and dark)
    *   - Corner radius
        - ``--radius`` (atoms use the ``--radius``-following ``rounded-*``)
    *   - Control density
        - ``--d-control-h`` / ``--d-control-text`` / ``--d-control-px``
    *   - Focus-ring width
        - ``--d-ring-width``
    *   - Surface elevation
        - ``--d-surface-shadow``
    *   - Fonts
        - ``--d-font-sans`` / ``--d-font-heading`` / ``--d-font-mono``
    *   - Icon family
        - ``data-icon-library`` (semantic icon keys resolve at runtime)

The shape tokens are consumed by the generated Fluid atoms, so every component
reacts to the active preset without per-preset markup. Radio inputs stay
circular regardless of radius.

Adding a theme
--------------

Add one row to the ``$presets`` table in
:file:`Build/Scripts/generate-shadcn-presets.php`:

..  code-block:: php

    // id, label, hue, lightAccent, radius, font, icon, density, ring, shadow
    ['crimson', 'Crimson — red', 25, false, '0.5', 'geist', 'lucide', 'default', '2px', 'sm'],

..  list-table::
    :header-rows: 1
    :widths: 22 78

    *   - Field
        - Options
    *   - ``hue``
        - ``0``–``360`` oklch hue (≈12 rose, 25 red, 55 orange, 130 lime,
          160 emerald, 185 teal, 259 blue, 293 violet, 350 pink)
    *   - ``lightAccent``
        - ``true`` only for bright accents (amber/lime) that need dark text
    *   - ``radius``
        - rem string: ``'0'``, ``'0.5'``, ``'0.75'``, ``'1'``
    *   - ``font``
        - ``inter``, ``geist``, ``nunito``, ``jetbrains``
    *   - ``icon``
        - ``lucide``, ``tabler``, ``hugeicons``, ``phosphor``, ``remixicon``
    *   - ``density``
        - ``compact``, ``default``, ``comfortable``
    *   - ``ring``
        - ``'1px'``, ``'2px'``, ``'3px'``
    *   - ``shadow``
        - ``none``, ``sm``, ``md``

Then run the generator:

..  code-block:: bash

    php Build/Scripts/generate-shadcn-presets.php

It inserts the CSS block into :file:`Resources/Public/Css/shadcn-theme.css`
(idempotent) and prints three snippets to paste into
:file:`Configuration/Sets/Desiderio/settings.definitions.yaml` (the dropdown
enum — required), :file:`Build/Scripts/sync-shadcn-fluid-primitives.php` (the
``$knownPresets`` map), and :file:`Classes/Icon/IconRegistry.php` (the
``libraryForPreset`` arm). The CSS block and the enum entry are all that is
strictly required to make a theme selectable. Run ``composer test`` to verify.

To reproduce an exact configuration from `ui.shadcn.com/create
<https://ui.shadcn.com/create>`__, build it there, choose *Get Code*, and paste
its full ``:root`` / ``.dark`` tokens as a new preset block — the same shape as
the committed ``b0`` / ``b6G5977cw`` blocks.

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
site set. The theme base lists it as an optional dependency.
