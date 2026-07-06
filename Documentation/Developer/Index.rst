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
    AddingContentElements

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

..  _developer-css-bundles:

The two compiled CSS bundles
----------------------------

Two committed stylesheets are generated from sources; keep the
distinction in mind when editing frontend code:

..  list-table::
    :header-rows: 1
    :widths: 35 25 40

    *   - Bundle
        - Built by
        - Source
    *   - :file:`Resources/Public/Css/desiderio-tailwind.css`
        - ``npm run build:css``
        - The Tailwind v4 entry point plus every utility class that
          Tailwind discovers by scanning templates, components, and
          Content Blocks via ``@source``.
    *   - :file:`Resources/Public/Css/desiderio.css`
        - ``npm run build:desiderio-css``
        - A plain concatenation of the unlayered feature partials in
          :file:`Resources/Private/Css/desiderio/` (ordered by
          :file:`manifest.txt`), minified.

Editing a template can silently change which utility classes the
Tailwind bundle must contain. Editing a partial only changes
``desiderio.css``. The ``pre-commit`` hook below guards the first case —
the one that is easy to forget.

..  _developer-git-hooks:

Git hooks and the Tailwind build guard
======================================

The repository ships its own git hooks under :file:`Build/Hooks/` so the
staleness guard travels with the checkout instead of living in each
developer's private :file:`.git/hooks/`. Enable them once per clone:

..  code-block:: shell
    :caption: One-time setup

    Build/Scripts/setup-hooks.sh

That script points git at the committed hooks by setting
``core.hooksPath = Build/Hooks`` (and marks the scripts executable). It is
idempotent and safe to re-run.

Because ``core.hooksPath`` *replaces* :file:`.git/hooks/` wholesale, the
standard Git LFS hooks have to live in :file:`Build/Hooks/` too — that is
all ``pre-push``, ``post-checkout``, ``post-commit``, and ``post-merge``
are: thin ``git lfs`` shims. The only project-specific hook is
``pre-commit``.

The ``pre-commit`` hook keeps the Tailwind bundle honest:

#.  It inspects the **staged** files. If none touch a path that can add or
    remove utility classes — :file:`Resources/Private/Tailwind/`,
    :file:`Resources/Private/Components/`, :file:`Resources/Private/Templates/`,
    :file:`Resources/Private/Extensions/`, :file:`Resources/Private/ShadcnUi/`,
    :file:`Resources/Private/FluidStyledContent/`, :file:`Resources/Private/Solr/`,
    :file:`Resources/Public/Js/`, or :file:`ContentBlocks/` — it exits
    immediately. Commits that only touch PHP, tests, or docs pay nothing.
#.  Otherwise it runs :file:`Build/Scripts/check-tailwind-built.sh`, which
    hashes :file:`Resources/Public/Css/desiderio-tailwind.css`, re-runs
    ``npm run build:css``, and re-hashes.
#.  **Identical hashes** → the committed bundle already matches the
    sources → the commit proceeds (you will see
    ``OK: … is in sync with Tailwind sources``).
#.  **Different hashes** → a template introduced classes the committed
    bundle is missing → the commit is **rejected**. The script has already
    rebuilt the bundle locally; stage it and re-commit:

    ..  code-block:: shell

        npm run build:css
        git add Resources/Public/Css/desiderio-tailwind.css

Without the rebuild the pushed CSS would be missing utility classes the
new templates rely on, and layout, spacing, card, or font styles silently
disappear in the frontend with no error.

The same :file:`check-tailwind-built.sh` runs from three call sites — the
``pre-commit`` hook, :file:`Build/Scripts/runTests.sh`, and the
``tailwind-bundle`` CI job — so a stale bundle is caught locally, in a
full test run, and in CI. :file:`CONTRIBUTING.md` documents the same steps
from the contributor's angle.

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

..  _developer-element-library:

Element library catalog cache
=============================

The visual element picker (the "Add content" panel that lists every content
element with a rendered preview) is filled by a single frontend request,
``?elementLibrary=1``, handled by ``ElementLibraryMiddleware``. That endpoint
returns the full catalog as JSON: one entry per content element with its
localized title and description, category, seeded demo ``uid``, icon, and a
cache-hash-signed preview URL.

The problem it solves
---------------------

Building that catalog means reading the on-disk Content Blocks definitions.
``ElementCatalog`` scans the ``ContentBlocks/ContentElements`` directory of
every loaded host extension (``desiderio`` and, when installed, ``innesto``)
and, for each element, parses its ``config.yaml`` and reads its
``fixture.json``. With ~255 elements that is ~255 YAML parses through
Symfony's pure-PHP parser plus ~255 JSON file reads on **every** picker open.
The demo fixtures are only needed by the seeder, never by the picker, so half
of that I/O was pure waste. Measured locally this was ~115 ms of work per
open (worse under a small PHP-FPM pool), paid again on every open because
nothing was cached.

Two catalog views
-----------------

``ElementCatalog`` now exposes two views over the same scan, so the hot path
only does the work it needs:

..  list-table::
    :header-rows: 1
    :widths: 34 66

    *   - Method
        - Use
    *   - ``getElementMetadata()``
        - Lightweight, **cached** view for the picker endpoint: cType, name,
          host extension, title, description, group, and a precomputed icon
          web path. No parsed ``config``, no ``fixture``.
    *   - ``getElements()``
        - Full, uncached view for the seeder commands: same metadata **plus**
          the parsed ``config`` array and the demo ``fixture``. Unchanged; the
          seeder is a cold CLI path where parsing every file is acceptable.

Both share a private ``scanContentElementConfigs()`` step (directory scan plus
``config.yaml`` parse); only ``getElements()`` additionally reads the fixture.

The cache
---------

``getElementMetadata()`` stores its built list in a dedicated cache,
registered in :file:`ext_localconf.php`:

..  code-block:: php
    :caption: ext_localconf.php — element library catalog cache

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['desiderio_library'] ??= [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
        'groups' => ['system'],
    ];

``SimpleFileBackend`` is deliberate: it needs no database table, so a fresh
deploy of the extension works without a schema migration, and reads are a
single file ``unserialize`` rather than a database round trip. The ``??=``
keeps any project-level override of the same identifier intact.

Invalidation
------------

The cache entry key is ``metadata-<fingerprint>``, where the fingerprint is an
MD5 of every ``config.yaml``'s path and modification time. Recomputing it only
stats files (no YAML parsing), so a cache hit is cheap. Two things therefore
invalidate the catalog:

..  list-table::
    :header-rows: 1
    :widths: 34 66

    *   - Trigger
        - Effect
    *   - Add, edit, or remove a content element
        - A ``config.yaml`` mtime changes, the fingerprint changes, the key
          changes, and the next open rebuilds automatically — no flush needed.
    *   - Flush all caches
        - The ``system`` cache group is cleared, dropping the stored entry.

..  note::

    The cache group ``system`` is **not** cleared by the frontend-only "flush
    frontend caches" action, so normal editing (which flushes page caches)
    leaves the catalog cached. Adding a content element is a developer action
    that changes a ``config.yaml`` on disk, which the fingerprint already
    detects.

Resilience and measured effect
------------------------------

Reading and writing the cache is best-effort: ``getElementMetadata()`` builds
the metadata outside the ``try`` block and catches any cache error (cache not
registered, an unwritable cache directory, …), degrading to an uncached build.
A cache problem can therefore only ever slow the picker, never break it; a
genuine build error still surfaces.

After the first build, opening the picker drops from ~115 ms of catalog work
to a ~2.5 ms cache hit (~50× faster) for 274 catalog elements. Localization
(``labels.xlf`` / ``library_short.xlf`` lookups, which depend on the backend
user's language) stays per-request and is served from TYPO3's own
localization cache.

..  tip::

    The rendered **preview** thumbnails inside the picker are a separate
    concern: each is a standalone frontend request stored in the standard page
    cache and pre-rendered by ``desiderio:library:warm``. This catalog cache
    only covers the list/JSON metadata, not the preview iframes.

Warming preview thumbnails
--------------------------

Each preview thumbnail is a standalone frontend request, page-cached per URL.
Crucially, **the URL includes the requesting site's base and cHash**, so the
same library record warmed for one site is a cache miss for another. A library
folder is commonly shared by several sites (each with a different base), and a
site's ``elementLibrary.storagePid`` may even differ from the folder's owning
site — so warming a single base leaves the picker's thumbnails cold everywhere
else.

``desiderio:library:warm`` therefore warms **every site that shows the
picker**, resolved from the live site settings (the same source the picker
reads):

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   - Invocation
        - Warms
    *   - ``desiderio:library:warm``
        - Every site's configured library, grouped by folder.
    *   - ``desiderio:library:warm --folder=<uid>``
        - That folder, for **every** site whose ``elementLibrary.storagePid``
          points at it — each from its own base.
    *   - ``desiderio:library:warm --site=<identifier>``
        - Only that site's library (optionally combined with ``--folder``).

Use ``-v`` for a per-record log and ``-vv`` to include the URLs. The command
reports a per-site ``warmed`` / ``failed`` breakdown. ``desiderio:library:seed``
runs the same multi-site warm after seeding (unless ``--no-warm`` is given).

..  note::

    A normal "flush all caches" clears the page cache, so the thumbnails go
    cold again and re-render lazily on first view (four at a time). Re-run the
    warm command after a full flush if you want them instant.

..  _developer-element-library-search:

Element library search
======================

The picker's search box is **typo-tolerant** and runs entirely server-side, yet
needs no external search service. The whole catalog (~255 elements) is already in
the browser, so the endpoint returns only a *ranked list of cTypes* and the panel
reorders the cards it already holds. It is a deliberately small "Solr without
Solr", implemented in ``Webconsulting\Desiderio\Library\ElementSearchService``
and reached through ``ElementLibraryMiddleware`` at ``?elementLibrarySearch=<term>``
(the same authenticated, backend-token-protected request as ``?elementLibrary=1``).

No external dependency
----------------------

The only fuzzy primitive is PHP's built-in ``levenshtein()`` (a core string
function). Matching also uses ``str_starts_with()`` / ``str_contains()`` and
``iconv()`` for accent folding. There is no Composer search package, no Solr, no
index server.

The weighted token index
------------------------

For each element the service tokenizes several fields and remembers, per token,
the *highest* field weight under which it appears:

..  list-table::
    :header-rows: 1

    *   - Field
        - Weight
    *   - Title
        - 10
    *   - Keyword
        - 6
    *   - Synonym
        - 3
    *   - Group
        - 2
    *   - Description
        - 1

Tokenizing lowercases the text, folds German umlauts (``ä → ae`` …) and
transliterates remaining diacritics to ASCII **before** any byte-based
``levenshtein()`` / ``strlen()`` runs, drops a short EN+DE stop-word list, and
splits on non-alphanumerics. The keyword and synonym sets come from
:file:`Resources/Private/Language/library_keywords.xlf` (plus its ``de.``
variant), so editors can broaden what an element matches without touching code.

Scoring a query
---------------

Every query token is compared against every element token, and the best hit
counts:

..  list-table::
    :header-rows: 1

    *   - Match
        - Contribution
    *   - exact
        - ``weight × 1.0``
    *   - prefix (token starts with the query)
        - ``weight × 0.85``
    *   - substring (query ≥ 3 chars)
        - ``weight × 0.55``
    *   - fuzzy (Levenshtein within budget)
        - ``weight × (0.7 − 0.18 × distance)``

The Levenshtein edit budget grows with query length (0 edits up to 3 chars,
1 edit up to 6, 2 edits beyond) and is gated by a length pre-filter, so only
plausibly-close tokens are ever compared.

The per-element score is the sum of its token contributions, then:

*   multiplied by a **coverage** factor ``0.5 + 0.5 × (matched ÷ query words)`` —
    matching every query word ranks highest, but a strong hit on one word of a
    multi-word query still surfaces;
*   boosted ``× 1.25`` when a query word hit the title;
*   dropped if it stays below the **score floor** (``0.3``).

Matches are sorted by score, ties broken alphabetically by title.

..  note::

    The score floor and coverage factor are tuned for **recall** — the picker is
    a browse-and-discover surface, so showing one extra near-match beats hiding a
    relevant element. Both knobs only ever *add* matches; neither can reorder a
    full-coverage hit below a partial one.

Suggestions and "did you mean"
------------------------------

Alongside the ranked matches the endpoint returns:

*   **autocomplete** completions for the last (partial) query word — vocabulary
    tokens it prefixes, or that are within one edit, ranked prefix-before-fuzzy
    then by field weight;
*   a **"did you mean"** rewrite when a query word matched nothing solid (only
    fuzzily, or not at all): each such word is swapped for its closest vocabulary
    token.

Caching and fallback
--------------------

The per-language weighted index is built once and stored in the same
``desiderio_library`` cache as the catalog (``SimpleFileBackend``, group
``system``). Its key includes the keyword-file fingerprint, so editing a keyword
self-invalidates it; "flush all caches" clears it too. Cache reads and writes are
best-effort — a cache failure only ever slows a search, never breaks it.

While the first request is in flight, or whenever the endpoint is unreachable, the
panel falls back to a pure client-side substring filter over the catalog it
already holds, so the search box always does *something*.

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
