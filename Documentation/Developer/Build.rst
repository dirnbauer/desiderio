..  include:: /Includes.rst.txt

..  _developer-build:

=======================
Local setup and builds
=======================

..  _developer-build-ddev:

DDEV environment
================

DDEV provides the supported PHP 8.4 and Node.js 24 environment. Start the local
TYPO3 application, install dependencies and build both the package assets and
the application manifest:

..  code-block:: bash

    ddev start
    ddev exec composer install
    ddev exec npm ci
    ddev exec npm run build
    ddev exec Build/CiApp/bootstrap.sh
    ddev exec -d /var/www/html/Build/CiApp npm ci
    ddev exec -d /var/www/html/Build/CiApp npm run build
    ddev launch

The disposable SQLite application lives in :file:`Build/CiApp`. Its bootstrap
seeds the element library and picks up the DDEV site URL automatically; its
Vite build clears TYPO3 caches so repeated builds keep asset URLs valid.

With PHP 8.4 or 8.5 and Node.js installed directly, the same commands run
without ``ddev exec``.

..  _developer-build-assets:

One build command
=================

``npm run build`` runs every generator in dependency order:

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   - Step
        - Output
    *   - ``build:presets``
        - :file:`Resources/Public/Css/shadcn-theme.css` (token blocks of all
          presets, from :file:`Build/Scripts/generate-shadcn-presets.php`).
    *   - ``build:preset-overview``
        - :file:`Resources/Public/Css/preset-samples.css` and the generated
          ``Partials/Pages/PresetOverview.fluid.html``.
    *   - ``build:css``
        - :file:`Resources/Public/Css/desiderio-tailwind.css` (Tailwind v4 over
          the ``@source`` list).
    *   - ``build:desiderio-css``
        - :file:`Resources/Public/Css/desiderio.css` — the partials of
          :file:`Resources/Private/Css/desiderio/` concatenated in
          :file:`manifest.txt` order and minified.
    *   - ``build:prism`` / ``build:hljs``
        - the two lightweight syntax highlighters.
    *   - ``build:iconfonts``
        - :file:`Resources/Public/IconFonts/` (see
          :ref:`configuration-icon-fonts`).

``Build/Scripts/check-generated-assets.sh`` runs that build and fails when the
working tree differs, which is exactly the "someone edited a template but did
not rebuild" case. The same script backs the ``assets`` suite of
:file:`Build/Scripts/runTests.sh`, the ``pre-commit`` hook and the CI job.

..  _developer-build-css-layers:

CSS cascade layers
==================

The Tailwind v4 entry point :file:`Resources/Private/Tailwind/desiderio.css`
uses native CSS cascade layers: ``@import "tailwindcss"`` declares the order
``theme, base, components, utilities`` as real ``@layer`` rules in the browser.

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   - Bucket
        - Contents and rules
    *   - ``@layer base``
        - Element defaults only: global border/outline colors, ``html`` font
          stack, ``body`` background and foreground tokens.
    *   - ``@layer components``
        - Shared component classes such as ``.frame``, ``.ce-frame`` and
          ``.desiderio-section``. Utility classes always win against this layer
          because ``utilities`` comes later — that is intentional and lets
          content elements override component defaults per instance.
    *   - ``@utility``
        - Custom utilities (``d-control-h``, ``d-control-text``,
          ``d-control-px``). Never write ``@layer utilities { … }`` in Tailwind
          v4; ``@utility`` is the replacement and makes the class
          variant-aware.
    *   - Unlayered CSS
        - The per-feature stylesheets in
          :file:`Resources/Private/Css/desiderio/` deliberately use **no**
          ``@layer`` at all.

The unlayered files are a feature, not an omission: under native cascade
layers, unlayered CSS always beats layered CSS regardless of specificity. That
is what lets the feature stylesheets reliably override Tailwind utilities
without specificity hacks or ``!important``. Do not wrap them in
``@layer components`` — utility classes would suddenly win against them.

The first four manifest entries (``components-01-base.css`` …
``components-04-layout.css``) are the former
:file:`Resources/Public/Css/components.css`. They come first so the feature
partials that were written to override them still do.

When adding styles, pick the bucket by intent: element default →
``@layer base``; reusable, utility-overridable class → ``@layer components``;
new variant-aware utility → ``@utility``; feature or preset styling that must
win over Tailwind → an unlayered file in
:file:`Resources/Private/Css/desiderio/`.

..  _developer-build-hooks:

Git hooks
=========

The repository ships its hooks under :file:`Build/Hooks/`, so the staleness
guard travels with the checkout. Enable them once per clone:

..  code-block:: bash

    Build/Scripts/setup-hooks.sh

The script points git at the committed hooks (``core.hooksPath =
Build/Hooks``) and is safe to re-run. Because ``core.hooksPath`` *replaces*
:file:`.git/hooks/`, the Git LFS hooks live there too — ``pre-push``,
``post-checkout``, ``post-commit`` and ``post-merge`` are thin ``git lfs``
shims. The only project-specific hook is ``pre-commit``, which rebuilds the
generated assets for commits that touch :file:`Classes/`,
:file:`Configuration/`, :file:`ContentBlocks/`, :file:`Resources/` or the npm
manifests.

..  _developer-build-tests:

Running the checks
==================

..  code-block:: bash
    :caption: Everything

    ddev exec Build/Scripts/runTests.sh

..  code-block:: bash
    :caption: One suite (positional or -s)

    ddev exec Build/Scripts/runTests.sh -s phpstan
    ddev exec Build/Scripts/runTests.sh unit
    ddev exec Build/Scripts/runTests.sh -s functional
    ddev exec Build/Scripts/runTests.sh -s audit
    ddev exec Build/Scripts/runTests.sh -s assets
    ddev exec Build/Scripts/runTests.sh -p 8.5 -s functional

Directly with the tools:

..  code-block:: bash

    vendor/bin/phpstan analyse
    vendor/bin/phpunit -c Build/phpunit/UnitTests.xml
    Build/Scripts/runFunctionalTests.sh
    vendor/bin/php-cs-fixer fix --dry-run --diff
    vendor/bin/rector process --dry-run

CI covers PHP 8.4 and 8.5 on TYPO3 14.3.6 or newer: composer validity and
security audit, PHPStan level 8, unit and functional tests, and the generated
assets check. The `browser QA workflow
<https://github.com/webconsulting/desiderio/blob/main/.github/workflows/browser-qa.yml>`__
additionally checks the seeded previews at three widths in light and dark mode.
