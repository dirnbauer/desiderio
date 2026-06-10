..  include:: /Includes.rst.txt

..  _developer:

==============
For developers
==============

..  _developer-architecture:

Architecture
============

Desiderio is intentionally layered:

..  code-block:: text
    :caption: Rendering layers

    Theme layer
      Page templates, backend layouts, header, footer, site settings

    Content element layer
      255 Content Blocks with editor-facing fields and fixtures

    Component layer
      Fluid 5 atoms, molecules, and layouts with typed arguments

The component layer currently contains 17 atoms, 28 molecules, and 4
layout primitives (49 typed Fluid components in total). Content elements
compose those primitives instead of hardcoding one-off markup.

See :ref:`developer-atomic-design` for the layer rules, migration order,
and reference content elements.

..  toctree::
    :maxdepth: 1

    AtomicDesign

..  _developer-sets:

Site sets
=========

The extension ships separate TYPO3 site sets so optional integrations can
be enabled only when needed:

..  list-table::
    :header-rows: 1
    :widths: 35 65

    *   - Set
        - Purpose
    *   - ``webconsulting/desiderio``
        - Base theme, tokens, TypoScript, page rendering, assets.
    *   - ``webconsulting/desiderio-content-elements``
        - Generated Content Block registration.
    *   - ``webconsulting/desiderio-preset-corporate``
        - Corporate demo/site preset.
    *   - ``webconsulting/desiderio-shadcnui-templates``
        - Shared page templates and backend layouts.
    *   - ``webconsulting/desiderio-blog``
        - Blog templates, RSS headers, and Blog TypoScript.
    *   - ``webconsulting/desiderio-news``
        - News templates and TypoScript.
    *   - ``webconsulting/desiderio-powermail``
        - Powermail templates and shadcn form classes.
    *   - ``webconsulting/desiderio-solr``
        - Solr result templates and suggest endpoint.

..  _developer-assets:

Frontend assets
===============

Desiderio avoids heavy runtime dependencies. The committed frontend
assets live in ``Resources/Public``:

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   - File
        - Responsibility
    *   - ``Css/shadcn-theme.css``
        - ui.shadcn.com/create tokens, house presets, dark mode, chart tokens,
          layout variables, and global component classes.
    *   - ``Js/desiderio.js``
        - Generic interactions, Solr suggestions, search controls, and
          small UI behavior.
    *   - ``Js/astro.js``
        - Lightweight progressive behavior for counters, carousels,
          reveals, countdowns, galleries, and timelines.
    *   - ``Js/prism-lite.js``
        - Syntax highlighting for code examples.
    *   - ``Js/charts.js``
        - Chart rendering helpers.

..  _developer-css-layers:

CSS cascade layers
==================

The Tailwind v4 entry point
:file:`Resources/Private/Tailwind/desiderio.css` uses native CSS cascade
layers. ``@import "tailwindcss"`` declares the layer order ``theme, base,
components, utilities`` as real ``@layer`` rules in the browser — not the
build-time ``@layer`` directives Tailwind v3 used. The compiled output is
committed to :file:`Resources/Public/Css/desiderio-tailwind.css` via
``npm run build:css``.

The layering conventions are:

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   - Bucket
        - Contents and rules
    *   - ``@layer base``
        - Element defaults only: global border/outline colors, ``html``
          font stack, ``body`` background and foreground tokens.
    *   - ``@layer components``
        - Shared component classes such as ``.frame``, ``.ce-frame``,
          ``.desiderio-section``, and card surface defaults. Utility
          classes always win against this layer because ``utilities``
          comes later in the layer order — that is intentional and lets
          content elements override component defaults per instance.
    *   - ``@utility``
        - Custom utilities (``d-control-h``, ``d-control-text``,
          ``d-control-px``). Never write ``@layer utilities { ... }`` in
          Tailwind v4; ``@utility`` is the replacement and additionally
          makes the class variant-aware (``hover:``, ``md:``, ...).
    *   - Unlayered CSS
        - The per-feature stylesheets in
          :file:`Resources/Private/Css/desiderio/` (header, footer, Solr,
          content frames, presets, ...) deliberately use **no**
          ``@layer`` at all.

The unlayered files are a feature, not an omission: under native cascade
layers, unlayered CSS always beats layered CSS regardless of specificity.
That is what lets the feature stylesheets reliably override Tailwind
utilities and component styles without specificity hacks or
``!important``. Do not wrap them in ``@layer components`` — utility
classes would suddenly win against them and break existing overrides.

When adding styles, pick the bucket by intent: element default →
``@layer base``; reusable, utility-overridable class →
``@layer components``; new variant-aware utility → ``@utility``; feature
or preset styling that must win over Tailwind → an unlayered file in
:file:`Resources/Private/Css/desiderio/`.

..  _developer-shadcn:

ui.shadcn.com/create sync
=========================

The project uses ``https://ui.shadcn.com/create`` as the visual source of
truth. React components are not copied into the TYPO3 frontend.

Use ``npm run shadcn:sync-fluid`` when upstream component class contracts
need to be synchronized into the shared Fluid primitives. Runtime theme
presets are stored in ``Resources/Public/Css/shadcn-theme.css`` and
selected with ``desiderio.shadcn.preset``.

..  _developer-integrations:

Extension templates
===================

Optional extension templates are kept in dedicated folders:

..  list-table::
    :header-rows: 1
    :widths: 35 65

    *   - Folder
        - Contains
    *   - ``Resources/Private/Extensions/Blog``
        - Blog templates, layouts, partials, comments, widgets, metadata,
          author, related posts, and RSS rendering.
    *   - ``Resources/Private/Extensions/News``
        - News list/detail templates, image partials, categories, tags,
          metadata, pagination, and schema-oriented markup.
    *   - ``Resources/Private/Extensions/Powermail``
        - Powermail form and field templates with shared shadcn classes.
    *   - ``Resources/Private/Solr``
        - Search result templates and partials.
    *   - ``Resources/Private/Form``
        - TYPO3 Form Framework form definitions and templates.

..  _developer-console-commands:

Console commands
================

Desiderio uses Symfony console commands for demo sites and integration
setup. Commands live in ``Classes/Command/``; shared seeding logic
belongs in ``Classes/Seeding/`` — not inlined into command classes.

Shared seeding services:

..  list-table::
    :header-rows: 1
    :widths: 34 66

    *   - Class
        - Responsibility
    *   - ``ExtensionFalSeeder``
        - Import bundled assets and write ``sys_file_reference`` rows.
    *   - ``CollectionRecordSeeder``
        - Insert nested Content Blocks collection rows recursively.
    *   - ``CollectionCleanupService``
        - Delete collection rows and file references on live workspace
          rows only.
    *   - ``ContentBlockCollectionMap``
        - Build the parent-table → collection-table lookup from block
          definitions.
    *   - ``LiveWorkspaceQueryHelper``
        - Add ``t3ver_wsid`` / ``t3ver_oid`` constraints to destructive
          seed queries.
    *   - ``StyleguideFixtureResolver``
        - Resolve styleguide YAML fixtures into ``tt_content`` rows,
          collections, and FAL references.
    *   - ``StarterContentBuilder``
        - Build starter-site content inserts from ``StarterSiteDefinitions``
          block payloads.
    *   - ``BlogPageTreeSeeder``
        - Discover EXT:blog setups, apply Desiderio backend layouts, and
          seed demo posts with categories, tags, authors, and comments.
    *   - ``FixtureFieldNormalizer``
        - Shared Content Blocks field normalization (scalars, files,
          checkboxes, dates) for styleguide and starter seeders.

Static demo payloads also live under ``Classes/Data/``:
``BlogDemoPostDefinitions`` and ``PowermailDemoFormDefinitions``.

``BrevoConfigurationResolver`` (under ``Classes/Domain/Finishers/``)
centralizes Brevo finisher configuration precedence; the finisher itself
handles HTTP and form value mapping only.

..  list-table::
    :header-rows: 1
    :widths: 32 68

    *   - Command
        - Purpose
    *   - ``desiderio:styleguide:seed``
        - Create or update styleguide fixture pages. Requires the live
          workspace; refuses Production without ``--allow-production``.
          Writes FAL assets to ``fileadmin/desiderio-styleguide/``.
    *   - ``desiderio:starter:seed``
        - Create or update the corporate starter site. Writes FAL assets
          to ``fileadmin/desiderio-starter/``.
    *   - ``desiderio:blog:seed-pages``
        - Normalize Blog page trees to Desiderio backend layouts. No-op
          when ``t3g/blog`` is not loaded.
    *   - ``desiderio:news:seed-taxonomy``
        - Assign default category/tag relations to visible News records
          without taxonomy. No-op when ``georgringer/news`` is not loaded.

See :ref:`known-problems-seed-command` for workspace and Production
guards on the styleguide seeder.

..  _developer-middleware:

Request middleware
======================

``ExtbasePluginRequestSanitizerMiddleware`` sanitizes malformed Extbase
plugin arguments on incoming requests. Visual Editor persistence can
send ``controller`` or ``action`` as arrays; Extbase then throws while
rendering News and other plugins on the edited page. The middleware
strips invalid values before the frontend stack runs.

Registered in ``Configuration/RequestMiddlewares.php``. Covered by
``Tests/Unit/ExtbasePluginRequestSanitizerMiddlewareTest.php``.

..  _developer-maintainability:

Maintainability
===============

The thermo-nuclear code quality review
(:file:`Documentation/Reports/code-quality.md`) tracks structural debt.
The primary risk is oversized seed commands:

..  list-table::
    :header-rows: 1
    :widths: 45 15 40

    *   - File
        - Lines
        - Guidance
    *   - ``SeedStyleguidePagesCommand.php``
        - ~610
        - Thin orchestration shell. Add fixture logic to
          ``StyleguideFixtureResolver``, not the command.
    *   - ``SeedStarterSitesCommand.php``
        - ~810
        - Thin orchestration shell. Add content-building logic to
          ``StarterContentBuilder``.
    *   - ``SeedBlogPagesCommand.php``
        - ~160
        - Thin orchestration shell. Add blog tree logic to
          ``BlogPageTreeSeeder``, not the command.
    *   - ``BrevoContactFinisher.php``
        - ~420
        - Configuration precedence lives in ``BrevoConfigurationResolver``.
          Extend the resolver, not inline ``resolveBoolean`` chains.

Rules for new PHP in ``Classes/``:

- Do not push any file from under 1,000 lines to over 1,000 lines.
- Put seeding and fixture normalization in ``Classes/Seeding/``, not in
  command classes.
- Reuse ``DatabaseSchemaHelper``, ``StyleguideDemoValueGenerator``, and
  ``StyleguideCollectionAliasPolicy`` instead of bespoke copies.
- New code must pass PHPStan at ``level: max`` without extending
  ``Build/phpstan-baseline.neon``.

..  _developer-quality-bar:

Quality bar
===========

The CI workflow checks Composer validity, dependency security, PHPStan,
PHPUnit, and the Content Block audit. The audit keeps categories such as
``template_undeclared_field``, ``hardcoded_inline_style``, and
``hardcoded_color`` at zero.

Run the local checks before shipping code changes:

..  code-block:: shell
    :caption: Local checks

    composer validate
    Build/Scripts/runTests.sh

For targeted checks:

..  code-block:: shell
    :caption: Targeted checks

    Build/Scripts/runTests.sh phpstan
    Build/Scripts/runTests.sh phpunit
    Build/Scripts/runTests.sh audit
    Build/Scripts/runFunctionalTests.sh

..  _developer-reports:

Reports
-------

``Documentation/Reports/`` contains the latest project audit reports for
TYPO3 conformance, security, workspaces, testing, docs, code quality,
and broader security review. Use those reports as context before changing
TCA, Fluid, TypoScript, or seed scripts.
