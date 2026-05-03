# Changelog

All notable changes to **webconsulting/desiderio** are documented in this
file. The format follows [Keep a Changelog](https://keepachangelog.com/)
and the project adheres to [Semantic Versioning](https://semver.org/).

## [2.2.0] — 2026-05-03

### Added

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

[2.1.0]: https://github.com/webconsulting/desiderio/releases/tag/v2.1.0
[2.0.0]: https://github.com/webconsulting/desiderio/releases/tag/v2.0.0
