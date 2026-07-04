# Changelog

All notable changes to **webconsulting/desiderio** are documented in this
file. The format follows [Keep a Changelog](https://keepachangelog.com/)
and the project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## [2.13.0] — 2026-07-04

### Changed

- Testimonials are stored inline again. The experimental `desiderio/testimonial-pool`
  RecordType added in 2.12.0 is removed: it was the only RecordType in the
  catalog, its table name (`desiderio_testimonial`) clashed with the base
  `testimonial` element's CType, and every other element already keeps its
  repeating content in an inline Collection. `testimonial-grid` and
  `testimonial-carousel` return to their own `testimonial_grid_testimonials`
  / `testimonial_carousel_testimonials` collections; the Relation-field
  support in the definition registry and seeders is removed. Editors see no
  change to the fields; the shared `desiderio_testimonial` pool table is no
  longer created.

## [2.12.0] — 2026-07-04

### Added

- Shared record pool for testimonials: the new `desiderio/testimonial-pool`
  RecordType (table `desiderio_testimonial`) is referenced from
  `testimonial-grid` and `testimonial-carousel` via `type: Relation` fields.
  The seeders upsert pool records by natural key and store a uid CSV on the
  parent column, so identical testimonials are stored once, reused across
  elements, and reseeding stays idempotent. Rendering uses Content Blocks'
  native relation resolution; the custom collection processor and cleanup
  service skip pool tables via their `foreign_table_parent_uid` guards.
- Two new strict audit categories in `scripts/audit-content-elements.php`:
  `commented_only_field` (a field whose only template reference sits inside
  `<f:comment>` — dead at render time) and `inline_edit_gap` (a Text/Textarea
  printed raw instead of through `f:render.text`, so the visual editor cannot
  inline-edit it). A documented `INLINE_EDIT_ALLOWLIST` covers the two
  intentional exceptions.
- Two demo form definitions ported from EXT:styleguide so the desiderio site
  ships ready-made examples for the Form module: `desiderio-simpleform`
  ("Desiderio simple form", a two-page contact form) and `desiderio-allfields`
  ("Desiderio all fields", a four-page tour of every form element). They live
  in `Resources/Private/Forms/` and are picked up by the existing
  `Configuration/Form/Desiderio/config.yaml` extension path. Identifiers are
  prefixed `desiderio-` so they don't collide with the styleguide originals
  when that extension is active.

### Changed

- **The content element catalog shrank from 255 to 244.** Eleven elements that
  were fully covered by a sibling's existing variant were removed:
  navbar-sticky, navbar-minimal, footer-centered, copyright-bar,
  hero-animated, hero-cta-only, hero-fullscreen, hero-gradient, card-pricing,
  table-content and company-stats — including their set registrations,
  `user.tsconfig` entries, library catalog texts, seed data and RTE manifest
  rows.
- 34 dead legacy Form Framework fields (`form_action`, `submit_text`,
  `placeholder`, …) were removed from 14 form elements together with the
  decoy `f:comment` references that had kept them invisible to the audit;
  the TYPO3 Form Framework owns those forms end to end.
- Every editor-visible text output is now wrapped with `f:render.text`
  (19 remaining gaps closed), so inline editing works across the catalog;
  translated fallbacks moved to explicit `f:if`/`f:else` branches.
- Design consistency pass across all elements: spacing and font-size
  literals now use the `--d-spacing-*`/`--d-text-*` scales, Tailwind utility
  classes in templates were replaced with BEM classes and token CSS, and
  media-query breakpoints were normalized to 480/640/768/1024px (exact
  complement boundaries like `max-width: 639px` are kept on purpose).
  51 below-par elements received an additional shadcn-token polish.
- Brand links and brand-colored text meet WCAG 2.2 on every surface: each
  preset ships hue-solved `--d-link` and `--d-primary-text` overrides, all
  element link styles use `var(--d-link, var(--primary))`, and
  `ThemeContrastTest` guards the combinations per preset and color scheme.
- EXT:styleguide's own example forms ("simpleform", "All fields") are now
  hidden from the Form module so editors only see the curated Desiderio forms.
  `ext_localconf.php` adds the styleguide set (`typo3/styleguide-form-set`) to
  EXT:form's `disabledSets`, which skips it in the TYPO3 v14 `FormYamlCollector`
  without deactivating the styleguide extension itself. Guarded on styleguide
  being loaded, so it is a no-op otherwise.

### Fixed

- Element-library picker previews are now served from the warmed page cache
  even when the editor is working inside a workspace. The preview iframes
  carry the backend session, so inside a workspace they rendered as a
  workspace preview — which never reads the live page cache — and re-rendered
  on every open (~1.1 s each, throttled four at a time, so a picker reload
  took ~16 s and thumbnails flickered in and out). `ElementPreviewCacheableMiddleware`
  now pins `?elPreview` requests to the live workspace (the Context workspace
  aspect and the backend user's temporary workspace) before EXT:workspaces'
  preview middleware reads the offline state, so previews hit the same cache
  `desiderio:library:warm` fills regardless of which workspace the editor is in.
- `WarmElementPreviewsCommand` no longer casts the raw `--folder` option
  (mixed) straight to string, and the `desiderio_library` cache registration's
  `$GLOBALS` writes are baselined, so PHPStan (level max) is green again.

## [2.11.0] — 2026-06-16

### Changed

- **`desiderio:library:warm` now warms every site that shows the picker, not
  just the folder-owning site.** Each site renders the element previews from
  its own base (and cHash), so a library folder shared by several sites — or a
  site whose `elementLibrary.storagePid` differs from the folder's owner — left
  the picker's preview thumbnails cold on the bases that were never requested.
  - `--folder=<uid>` now warms that folder for **all** sites whose
    `elementLibrary.storagePid` points at it (resolved from live site settings,
    the same source the picker uses), each from its own base.
  - `--folder` is now **optional**: with no folder, the command warms **every**
    site's configured library, grouped by folder.
  - New `--site=<identifier>` option restricts warming to one site.
  - Output now reports a per-site breakdown (base, warmed, failed).
  - `desiderio:library:seed` likewise warms every site sharing the seeded
    folder after a seed (skip with `--no-warm`).
  - `PreviewWarmer` gains `getSitesForLibraryFolder()` and
    `getConfiguredLibraries()`; `warm()` takes an optional list of sites.

## [2.10.1] — 2026-06-16

### Performance

- **Element library picker catalog is now cached.** The frontend element
  picker endpoint (`?elementLibrary=1`, served by `ElementLibraryMiddleware`)
  rebuilt its whole catalog on every open by parsing one `config.yaml` per
  Content Block (~255 files) through Symfony's pure-PHP YAML parser **and**
  reading + JSON-decoding one `fixture.json` per element — fixtures that the
  endpoint never used (they are seeder-only). That work (~115 ms per open,
  more under load) ran uncached on every picker open.
  - `ElementCatalog::getElementMetadata()` is a new lightweight catalog view
    (cType, name, host extension, title, description, group, and a
    precomputed icon URL — no parsed config, no fixture) used by the picker
    endpoint. Its result is stored in a new `desiderio_library` cache
    (`SimpleFileBackend`, group `system`).
  - The cache key fingerprints every `config.yaml`'s path and mtime, so
    adding, editing, or removing a Content Block self-invalidates the entry
    with no manual flush. “Flush all caches” also clears it.
  - Cache reads and writes are best-effort: any cache failure (not
    registered, unwritable directory, …) falls back to an uncached build, so
    a cache problem can only ever slow the picker, never break it.
  - `ElementCatalog::getElements()` (full records with parsed config and
    fixture) is unchanged and still used by the seeder commands.
  - Measured locally: cold build ~115 ms → warm cache hit ~2.5 ms (~50×) for
    274 catalog elements. See `Documentation/Developer/Index.rst`, section
    “Element library catalog cache”.

## [2.10.0] — 2026-06-15

### Added

- Code-block syntax highlighting now auto-detects the snippet language,
  understands bash/shell, and paints each token group with a subtle
  multi-hue palette that stays readable in light and dark across every
  theme preset (named colours mixed toward `--foreground`, so the element
  audit's no-raw-colour rule still holds).
- Element-library previews are cacheable inside an authenticated edit
  session: a middleware turns the admin panel off for `?elPreview`
  requests so each preview renders from — and is served by — the standard
  page cache instead of `no_cache`. Picker cards also gained short
  German/English blurbs.
- New "Features" showcase section seeded by the styleguide seeder — a
  `/features` hub plus 13 verbose per-extension subpages — together with
  backend-module and frontend screenshots.

### Changed

- All 255 content-element titles and descriptions were enriched in German
  and English; timeline and pricing fixtures corrected.
- Added a developer manual on adding a shadcn block as a content element.

### Fixed

- Visual editor: dropped the testimonial card shadow and skipped the astro
  count-up animation while editing.
- Typed the showcase page builders (`StyleguideShowcasePages::block()` and
  the `featureXxxPage()` / `featuresOverviewPage()` builders) and the
  element-preview middleware's `uc` access so PHPStan passes cleanly
  (47 errors resolved).

## [2.9.5] — 2026-06-13

_Consolidated notes for the 2.9.x line. The changelog and the `version`
fields in `ext_emconf.php` / `composer.json` were not updated between
2.6.2 and the same-day 2.9.x tags (v2.7.0–v2.9.4); this release reconciles
both and resumes per-release versioning._

### Security

- FriendlyCaptcha test mode is now refused (and logged) in the Production
  application context, so CAPTCHA validation can no longer be disabled on
  live systems via a site setting.
- `StructuredDataViewHelper` validates URLs and rejects non-http(s) schemes
  before emitting JSON-LD.
- `SearchSnippetViewHelper` caps the number of highlighted search terms to
  keep hostile multi-term queries from building oversized regex patterns.
- The styleguide browser escapes location-hash input with `CSS.escape()`
  before using it in DOM selectors.

### Changed

- The starter and styleguide seeder commands were split into reusable
  services: `SeedPageUpserter` (find-or-create/update of seeded pages,
  hiding unmanaged children), `DesiderioContentCleaner` (live-workspace-safe
  soft-deletion of seeded content including file references and collection
  rows), and `ContentElementSeeder` (tt_content insert plus file/collection
  wiring bound to a FAL folder). The commands shrank to option parsing and
  orchestration (812 → 531 and 546 → 282 lines).
- CI runs the functional test suite on the same PHP matrix (8.3/8.4/8.5)
  as the unit tests, with composer caching.
- New functional test suites cover both seeder commands end to end
  (dry-run, full seed, idempotent re-run, root-map with hidden unmanaged
  children, invalid preset) against a real database.
- Removed the Alpine.js runtime. Accordion, tabs, alert, and notification
  elements are now driven by the existing vanilla glue code in
  `desiderio.js` (per the specification: no React/Alpine/Livewire).
- Blog and countdown templates use ICU date patterns (Fluid 5.3) instead of
  deprecated strftime formats.
- Pagination and structured-data strings are translatable via
  `labels.xlf` (en/de/es/fr/it/hu) instead of being hardcoded.
- `BlogCommentFormFactory` receives its services via dependency injection
  and no longer carries a dead pre-v12 compatibility branch.
- Brevo finisher and FriendlyCaptcha ViewHelper resolve the current request
  via the form runtime/rendering context instead of `$GLOBALS['TYPO3_REQUEST']`.

### Fixed

- Content elements that paint a section band
  (`content-highlight--muted` / `--primary` / `--bordered`) no longer
  render their copy flush against the left edge inside blog/article
  (`.prose`) bodies. The inline-padding exemption that previously covered
  only `cta-banner` bands now also includes the content-highlight
  variants, so banded copy keeps symmetric insets.
- `ExtensionFalSeeder` now imports a temporary copy of bundled assets.
  Previously `Folder::addFile()` moved the source file, so the first seeder
  run deleted the original asset from the extension directory.

### Performance

- `ContentBlockCollectionProcessor` skips `sys_file_reference` lookups for
  empty file fields, removing one query per row and empty field.

## [2.6.2] — 2026-06-08

### Fixed

- Feature timeline connectors now start closer to the numbered markers and meet
  the card notch more cleanly.
- Feature tab media/copy spacing and system page panel padding have been
  tightened for more balanced rendered layouts.

## [2.6.1] — 2026-06-08

### Fixed

- Fluid component templates and the Powermail shadcn class map now keep
  Tailwind arbitrary selectors readable in source (`[&_a]`, `[&>svg]`,
  `has-[>...]`) instead of HTML-entity encoded. Tailwind scans the Fluid source
  before TYPO3 renders it, so encoded selectors could render in HTML without
  generating the matching CSS.
- The generated Tailwind bundle now includes the descendant, direct-child, and
  `:has()` arbitrary selector utilities used by the Fluid components.

### Changed

- `Build/Scripts/sync-shadcn-fluid-primitives.php` preserves Tailwind selector
  characters when regenerating Fluid primitives, preventing future shadcn syncs
  from reintroducing encoded arbitrary selector classes.

## [2.6.0] — 2026-06-06

### Added

- Shared seeding services under `Classes/Seeding/`:
  `ExtensionFalSeeder`, `CollectionRecordSeeder`, `CollectionCleanupService`,
  `ContentBlockCollectionMap`, `LiveWorkspaceQueryHelper`, and
  `SeedingPayloadKeys`.
- `StyleguideFixtureResolver`, `StarterContentBuilder`, and
  `BlogPageTreeSeeder` — extracted fixture, starter, and blog-tree logic from
  the Symfony seed commands.
- `FixtureFieldNormalizer` — shared scalar/file/checkbox/date normalization for
  styleguide and starter seeders.
- `BrevoConfigurationResolver` — centralizes Brevo finisher configuration
  precedence (finisher option → extension config → site setting → env).
- `BlogDemoPostDefinitions` — static demo Blog post payloads for
  `BlogPageTreeSeeder`.
- `ExtbasePluginRequestSanitizerMiddleware` — strips malformed Extbase plugin
  arguments from Visual Editor persistence requests.
- `Documentation/Reports/code-quality.md` — thermo-nuclear maintainability
  review and seed-command decomposition record.

### Changed

- `SeedStyleguidePagesCommand` (~612 lines), `SeedStarterSitesCommand`
  (~812 lines), and `SeedBlogPagesCommand` (~158 lines) are orchestration
  shells that delegate to `Classes/Seeding/` services.
- `BrevoContactFinisher` delegates configuration resolution to
  `BrevoConfigurationResolver`.
- README, Installation, Developer, and Reports docs describe the seeding
  service map, all `desiderio:*` commands, and Visual Editor compatibility.

### Fixed

- Visual Editor TypeError when rendering News and other Extbase plugins with
  malformed `controller` / `action` request arguments.
- PHPStan type contracts in `ExtbasePluginRequestSanitizerMiddleware`.

## [2.5.0] — 2026-06-05

### Added

- `StyleguideDemoValueGenerator` — extracted deterministic styleguide demo
  field defaults from `SeedStyleguidePagesCommand` (~600 lines).
- `StyleguideCollectionAliasPolicy` — shared alias maps and field resolution
  for styleguide collection seeding (link slots, nested collections, cells/
  row_data fallbacks).
- `Resources/Private/Css/desiderio/` partials plus `npm run build:desiderio-css`
  to concatenate the hand-written theme CSS from source sections.
- `initLineChartRoots()` helper in `charts.js` so `.chart-area` and
  `.chart-line` CE templates reuse the same line-chart bootstrap path as the
  generic `.chart` renderer (BEM class names unchanged).

### Changed

- `SeedStyleguidePagesCommand` delegates demo values and collection alias
  resolution to the new seeding classes (~800 lines removed from the command).
- Styleguide seed unit tests invoke `StyleguideDemoValueGenerator` directly;
  `ExtensionMetadataTest` now compares `ext_emconf.php` against
  `composer.json` instead of a hard-coded version string.

## [2.4.0] — 2026-06-05

### Added

- Ten selectable **house presets** (Aurora, Marine, Forest, Ember, Bloom,
  Lagoon, Gold, Midnight, Blossom, Citrus) in `desiderio.shadcn.preset`. Each
  inherits the authentic neutral base and overrides its accent colour, radius,
  fonts, icon library, control density, focus-ring width, and surface elevation
  — so editors can switch the whole site theme from the site configuration with
  no rebuild.
  Generated by `Build/Scripts/generate-shadcn-presets.php`. Existing sites are
  unaffected until a new preset is selected.
- shadcn/ui component **shape now switches per preset at runtime**. Corner
  radius follows each preset's `--radius` scale and form-control height, text
  size, and inline padding follow new `--d-control-*` tokens (consumed via
  `d-control-h` / `d-control-text` / `d-control-px` utilities). Selecting a
  different `desiderio.shadcn.preset` in the site configuration re-themes
  component shape — flat/compact radix-lyra vs. rounded/roomier presets —
  through the `data-shadcn-preset` body attribute with no rebuild. Radio
  inputs stay circular. Focus-ring width (`--d-ring-width`) and card surface
  elevation (`--d-surface-shadow`) are tokenized too, so presets can differ in
  focus treatment and depth as well.

### Changed

- Made the theme picker discoverable in the site-settings editor. The
  `desiderio.shadcn` category is now labelled **Theme** (with a description) and
  ordered first under *Desiderio*, ahead of the *Appearance*, *Typography*,
  *Layout*, and *Brand* groups, and `desiderio.shadcn.preset` is now labelled
  **Theme preset** with a benefit-led description. Setting keys, stored values,
  and runtime behaviour are unchanged.
- The selected preset is now the single source of truth for the look. Removed
  the inert `desiderio.theme.accent` setting (the preset owns the accent),
  marked `desiderio.shadcn.style` read-only/advanced (it is the build-time
  structural base — no runtime effect; the preset drives the live style),
  relabelled the `desiderio.theme` settings category to **Appearance**
  (dark-mode only), and stopped emitting the now-unused `data-accent` body
  attribute.

### Fixed

- Gallery featured images now keep using `<f:image>` with structured Fluid
  `data` arguments, preventing `FileReference` string-conversion errors in
  Visual Editor rendering.
- Content Blocks collection fields now use per-field prefixing instead of a
  generated TCA override patch, so reused `items`-style identifiers no longer
  collapse into one shared `tt_content` column.
- Powermail form templates now render through a generated shadcn class partial
  sourced from the selected registry style, so form controls, cards, labels,
  buttons, and inline checkbox rows no longer hardcode one create style.
- shadcn style and icon settings now cover the create UI options
  (`Vega`, `Nova`, `Maia`, `Lyra`, `Mira`, `Luma`, `Sera`, `Rhea`; Lucide,
  Tabler Icons, HugeIcons, Phosphor Icons, Remix Icon) instead of only the
  original subset.

### Documentation

- Documented the Content Block media-rendering convention in README,
  contribution notes, shadcn upgrade guidance, and audit reports.
- Documented the collection table policy: per-collection tables are the safe
  generated default; table reuse is explicit modeling for identical stable
  child rows and mainly reduces schema noise, not physical database size.
- Refreshed maintained markdown references to the current 101-test local
  PHPUnit suite.
- Extracted `ContentBlockDefinitionRegistry` and `DatabaseSchemaHelper`
  so styleguide/starter seed commands and the collection processor share
  one Content Block YAML loader and database schema helper instead of
  maintaining three copies.
- Switched code-block highlighting to **Prism-only** output and removed
  the duplicate regex highlighter path from `astro.js`.
- Centralized Friendly Captcha test-mode parsing and Desiderio form
  identifier checks in shared utility classes.
- Removed the redundant `pageTitle` field from styleguide seed manifests;
  page titles now derive from `groupTitle`.

## [2.2.0] — 2026-05-03

### Added

- **WCAG 2.2 AA accessibility primitives** in the page chrome:
  - Skip-to-content link as the first focusable element of every page,
    targeting a focusable `<main id="main-content" tabindex="-1">`.
  - `prefers-reduced-motion` global rule that neutralises animations
    and transitions for users that opt out of motion (WCAG 2.3.3).
  - `.d-skip-link`, `.sr-only`, `.d-sr-only-focusable` utilities in
    `Resources/Public/Css/components.css`.
  - `aria-current="page"` on active nav and subnav links, `aria-controls`
    + `aria-pressed` on the menu / theme toggle, `aria-hidden` +
    `focusable="false"` on every decorative SVG icon, and a real
    `<nav aria-label>` around the language switcher (with `lang`
    attribute on each native-language label).
  - `role="list"` patched into 31 `<ul>` elements across News, Solr,
    Blog, and Pagination overrides where Tailwind utilities strip the
    native list role in Safari/VoiceOver.
  - Seven new `a11y.*` units in `locallang.xlf` / `de.locallang.xlf`
    (`a11y.skipToContent`, `a11y.nav.main`, `a11y.nav.footer`,
    `a11y.nav.language`, `a11y.menu.toggle`, `a11y.theme.toggle`,
    `a11y.share.label`).
- Two new structural tests assert the page-layout primitives, the
  required a11y locallang units, and the `<ul role="list">` patches
  across the override surface.
- `Build/Scripts/inject-role-list.php` helper that idempotently adds
  `role="list"` to `<ul>` tags whose visual style strips list
  semantics (Tailwind flex/grid/divide).

- **`webconsulting/desiderio-blog` site set** that replaces the upstream
  `t3g/blog` Bootstrap markup with shadcn-only templates: layouts
  (`Default`, `Post`, `Widget`), `BlogList` + `BlogPost` page templates,
  every post / list / widget / comment / page-layout template, and
  30+ partials in `Resources/Private/Extensions/Blog/`.
- **Fluid 5.3 strong typing** extended across the entire override
  surface: News, Solr, FluidStyledContent, the shared Pagination
  partials, and every blog partial now declare typed
  `<f:argument name="…" type="…"/>` blocks. Types pin to concrete
  domain models where applicable
  (`GeorgRinger\News\Domain\Model\News`,
  `T3G\AgencyPack\Blog\Domain\Model\{Post,Author,Category,Tag,Comment}`)
  with `iterable` / `array` / `object` / `string` for collections,
  settings bags, paginators, and identifiers.
- **ICU MessageFormat plural rules** in `locallang.xlf` /
  `de.locallang.xlf` for `news.loadMore.status`,
  `news.magazine.items`, `news.comments.count`,
  `news.tags.count`, `news.categories.count`, and
  `news.entries.count`. The `LoadMore` partial now passes
  `{visible: …, total: …}` named arguments to drive the rule.
- Backend page-layout previews for the seven `blog_*` plugin list types
  via `Configuration/Sets/DesiderioBlog/page.tsconfig`.
- `t3g/blog` added to `composer.json` `suggest` and
  `ext_emconf.php` `suggests`.

### Changed

- Base set `webconsulting/desiderio` adds `webconsulting/desiderio-blog`
  to its optional dependency list.
- TYPO3 runtime packages refreshed to the latest `14.3.x` patch line,
  including `typo3/cms-workspaces 14.3.1`.
- `SeedStyleguidePagesCommand` cleanup queries now add explicit live
  workspace predicates (`t3ver_wsid = 0`, `t3ver_oid = 0` when present)
  before deleting file references or collection rows, so staged workspace
  overlays are not removed by a live styleguide reseed.
- Documentation reflected the live-workspace seeder guard and the
  then-current 88-test PHPUnit suite.
- Test suite grew by four structural tests asserting:
  shadcn `<d:…>` usage in blog templates,
  typed `<f:argument>` declarations across News/Solr/FSC/Pagination,
  XLIFF 2.0 across every label file (Resources + ContentBlocks),
  and ICU plural rules on the news labels.

## [2.1.0] — 2026-05-03

The "v14.3 LTS only" release. The composer constraints already pinned
TYPO3 v14.3 — this cut aligns toolchain, content-element catalogue, and
documentation with the LTS commitment, then runs a six-skill cleanup loop
on the result.

### Added

- **Page templates and backend layout `DesiderioNews`** for dedicated
  news landing pages (stage + main + sidebar layout, mirroring
  `DesiderioBlog`).
- **News magazine list (`MagazineList.html`)** — featured article on top,
  load-more secondary grid below.
- **News load-more partial (`Partials/List/LoadMore.html`)** with three
  configurable settings (`useLoadMore`, `initialCount`, `loadMoreCount`)
  and a progressive-enhancement script that focuses the first newly
  revealed item for screen readers.
- **Detail/Opengraph and Detail/Shariff partials** — both were referenced
  by `Detail.html` but missing from disk.
- **`typo3/cms-workspaces ^14.3`** is now a hard dependency so workspace
  preview/staging is guaranteed to be available.
- **PHPStan level `max`** with `phpstan/extension-installer`,
  `saschaegerer/phpstan-typo3`, and `phpstan/phpstan-strict-rules`.
- **GitHub Actions CI** workflow (`.github/workflows/ci.yml`) running
  PHPStan + PHPUnit + audit across PHP 8.3 / 8.4 × TYPO3 ^14.3.
- **`Build/Scripts/runTests.sh`** local test runner mirroring CI.
- **`Build/Scripts/convert-xliff-1-2-to-2-0.php`** migrator that ports
  the 765 it/fr/es Content Block label files to XLIFF 2.0.
- **Documentation/guides.xml + Index.rst** ReST scaffold so the docs can
  be rendered with the official TYPO3 docs container.
- **Documentation/Reports/** with six agentic-skill audit reports
  (conformance, security, workspaces, testing, docs, security-audit).

### Changed

- **Extension state `stable`** (was `beta`); version bumped to **2.1.0**.
- `ext_emconf.php` now declares `php` and `workspaces` constraints
  alongside `content_blocks` and `vite_asset_collector`.
- `composer.json` `minimum-stability: stable` (was `dev`), `secure-http`
  enabled, `audit.abandoned: fail`.
- `map-embed.height` switched to TCA `Number` and coerced through
  `f:format.number()` before it lands in the iframe wrapper's `style`.
- `hero-gradient.gradient_from/to` switched to TCA `Color` so values are
  validated server-side.
- Solr "explain" debug raw output is gated behind
  `settings.logging.debugOutput` in both Solr partials.
- `FixtureJsonViewHelper` emits a CSP nonce attribute when the request
  carries a TYPO3 14 `ConsumableNonce` so a strict `script-src 'self'
  'nonce-…'` policy stays compatible.
- The Hugeicons CDN CSS is now pinned to `v1.0.7` with SRI integrity +
  `crossorigin` so the third-party origin can no longer push arbitrary
  CSS.
- `SeedStyleguidePagesCommand` injects `Context` + `StorageRepository`
  via constructor DI (drops one `GeneralUtility::makeInstance` service
  lookup) and refuses to run from a non-live workspace or in Production
  context unless `--allow-production` is passed.
- All 765 it/fr/es Content Block label files migrated from XLIFF 1.2 to
  TYPO3 v14 XLIFF 2.0.

### Fixed

- ViewHelpers (`RecordHasFieldViewHelper`, `StyleguideGroupsViewHelper`,
  `StyleguideFixtureSummaryViewHelper`) tightened against PHPStan max +
  strict-rules inference.

## [2.0.0] — 2026-04-21

Initial v14-targeting release. Replaces `webconsulting/desiderio 1.x`
and `webconsulting/shadcn2fluid-templates 3.x` with a clean rewrite:
255 Content Blocks, a 37-component shadcn/ui Fluid 5 library, six page
templates, and five swappable visual presets. See `MIGRATION-PLAN.md`
for the migration notes from the old extensions.

[2.6.2]: https://github.com/dirnbauer/desiderio/releases/tag/v2.6.2
[2.6.1]: https://github.com/dirnbauer/desiderio/releases/tag/v2.6.1
[2.6.0]: https://github.com/dirnbauer/desiderio/releases/tag/v2.6.0
[2.5.0]: https://github.com/dirnbauer/desiderio/releases/tag/v2.5.0
[2.4.0]: https://github.com/dirnbauer/desiderio/releases/tag/v2.4.0
[2.3.0]: https://github.com/dirnbauer/desiderio/releases/tag/v2.3.0
[2.2.0]: https://github.com/dirnbauer/desiderio/releases/tag/v2.2.0
[2.1.0]: https://github.com/dirnbauer/desiderio/releases/tag/v2.1.0
[2.0.0]: https://github.com/dirnbauer/desiderio/releases/tag/v2.0.0
[Unreleased]: https://github.com/dirnbauer/desiderio/compare/v2.6.2...HEAD
