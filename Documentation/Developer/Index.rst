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
      244 Desiderio Content Blocks with editor-facing fields and fixtures

    Component layer
      Fluid 5 atoms, molecules, layouts, and organisms with typed arguments

The component layer currently contains 17 atoms, 35 molecules, 4 layout
primitives, and 4 site organisms (60 typed Fluid components in total). Content
elements compose those primitives instead of hardcoding one-off markup, which
``Tests/Unit/AtomicDesignConformanceTest`` enforces.

See :ref:`developer-atomic-design` for component conventions and reference
content elements.

..  toctree::
    :maxdepth: 1

    AtomicDesign
    AddingContentElements
    DesignPhilosophy
    ShadcnSync
    Commands
    Build
    Innesto
    CollectionTableConsolidation

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

CSS, Tailwind layers and the build
==================================

The committed CSS is produced by ``npm run build``. How the cascade layers are
used, why the feature partials are deliberately unlayered, and how the
pre-commit hook keeps the generated assets current is documented in
:doc:`Build`.

..  _developer-shadcn:

ui.shadcn.com/create sync
=========================

The project uses ``https://ui.shadcn.com/create`` as the visual source of
truth. React components are not copied into the TYPO3 frontend.

Use ``npm run shadcn:sync-fluid`` when upstream component class contracts
need to be synchronized into the shared Fluid primitives. Runtime theme
presets are stored in ``Resources/Public/Css/shadcn-theme.css`` and
selected with ``desiderio.shadcn.preset``. :doc:`ShadcnSync` documents the
full synchronization workflow.

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

Desiderio uses Symfony console commands for demo sites, integration setup and
the template lint gate. Commands live in ``Classes/Command/``; shared seeding
logic belongs in ``Classes/Seeding/`` — not inlined into command classes. The
command reference and the seeding service map are in :doc:`Commands`.

..  _developer-middleware:

Request middleware
======================

``ExtbasePluginRequestSanitizerMiddleware`` removes non-string or empty
Extbase ``controller`` and ``action`` values from frontend query and POST
arguments. Visual Editor persistence can otherwise leave those arguments
malformed; Extbase then throws while rendering News and other plugins on the
edited page. The middleware strips the invalid values before the frontend
stack runs.

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
every loaded host extension (``desiderio``, ``innesto`` when installed, and any
extension that :ref:`registered itself as a provider <element-library-hosts>`)
and, for each element, parses its ``config.yaml`` and reads its
``fixture.json``. With 244 Desiderio Content Block definitions (plus any installed Innesto
blocks), that starts at 244 YAML parses through Symfony's pure-PHP parser plus
244 JSON file reads on **every** picker open.
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

..  _element-library-hosts:

Hosting a second theme's elements
---------------------------------

The library is a service, not a Desiderio-only feature: any extension can put
its own Content Blocks into the picker. It registers itself in its
``ext_localconf.php``:

..  code-block:: php
    :caption: EXT:my_theme/ext_localconf.php

    $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['desiderio']['libraryHostExtensions'][] = 'my_theme';

Nothing else is required beyond the per-element file contract described in
:doc:`AddingContentElements` — most importantly an explicit ``typeName:`` in
every ``config.yaml`` (the catalog derives the cType from it; without it a
vendor that differs from the extension key produces a cType that does not match
the registered one), a ``library.json`` for the demo content an editor copies
in, and ``assets/icon.svg``. Keyword chips and card blurbs come from the
provider's own ``Resources/Private/Language/library_keywords.xlf`` and
``library_short.xlf``, keyed by cType.

Registration is deliberately opt-in rather than derived from the Content Blocks
registry: that registry's API is marked ``@internal``, and ingesting every
block-shipping extension automatically would fill the picker with elements that
carry no demo content, keywords or descriptions.

Because a provider's elements are only useful on sites that load its CSS, each
site declares which hosts its picker offers:

..  code-block:: yaml
    :caption: config/sites/<site>/settings.yaml

    elementLibrary.hosts: 'desiderio,innesto,core'

``core`` covers the native TYPO3 content types. An empty (or absent) setting
lists every installed provider, which is what sites configured before this
setting existed keep doing. The same list belongs on the seed command, so the
site's library folder holds matching demo records:

..  code-block:: bash

    ddev exec vendor/bin/typo3 desiderio:library:seed --parent=<root> --hosts=my_theme,core

Search, suggestions and "did you mean" honour the same restriction, so a site
never proposes an element it cannot render.

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

    The picker previews are rendered frontend documents loaded in iframes, not
    generated image thumbnails. Each iframe source is a standalone frontend
    request stored in the standard page cache and pre-rendered by
    ``desiderio:library:warm``. This catalog cache only covers the list/JSON
    metadata, not the iframe documents.

Warming rendered iframe previews
--------------------------------

Each iframe source renders one seeded ``tt_content`` record through a dedicated
``PAGE`` type. Its HTML response is stored in TYPO3's standard page cache per
URL. Crucially, **the URL includes the requesting site's base and cHash**, so
the same library record warmed for one site is a cache miss for another. A
library folder is commonly shared by several sites (each with a different
base), and a site's ``elementLibrary.storagePid`` may even differ from the
folder's owning site — so warming a single base leaves the picker previews cold
everywhere else.

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

    A normal "flush all caches" clears the page cache, so the iframe documents
    go cold again and re-render lazily on first view (four at a time). Re-run
    the warm command after a full flush if you want the previews to appear
    immediately.

..  _developer-element-library-search:

Element library search
======================

The picker's search box is **typo-tolerant** and runs entirely server-side, yet
needs no external search service. The browser already holds the 244 Desiderio
Content Blocks, the available native TYPO3 entries, and any Innesto additions,
so the endpoint returns only a *ranked list of cTypes* and the panel
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

Keep commands focused on orchestration. Fixture normalization, FAL writes,
collection persistence, and schema handling belong in ``Classes/Seeding/``.
Brevo configuration precedence belongs in ``BrevoConfigurationResolver``.

*   Reuse the existing schema, field-normalization, collection, and workspace
    services before adding another helper.
*   Keep each class responsible for one behavior. Split unrelated logic before
    a file grows beyond 1,000 lines; moving code alone is not a simplification.
*   Prefer TYPO3 APIs and constructor injection over custom infrastructure.
*   New code must pass PHPStan at ``level: 8``. There is no baseline file, so
    a new finding is fixed, not recorded.
*   Maintain generated assets through the commands in :file:`package.json`.
    Completed source-rewrite scripts are removed after their output becomes
    the maintained source. Database upgrade wizards and their verification
    tools remain available for existing installations.
*   Put durable behavior and operating instructions in this manual. Record
    change-specific test results in the pull request instead of maintaining
    duplicate dated audit reports.

..  _developer-quality-bar:

Quality bar
===========

The CI workflow checks Composer validity, dependency security, PHPStan level 8,
unit and functional tests, and the generated assets. The Content Block audit
keeps categories such as ``template_undeclared_field``,
``hardcoded_inline_style`` and ``hardcoded_color`` at zero, the template lint
gate keeps Fluid parse errors at zero, and the atomic-design conformance test
keeps the component layering intact.

:doc:`Build` lists the DDEV setup and every command that runs those checks
locally. CI covers PHP 8.4 and 8.5 on TYPO3 14.3.6 or newer.
