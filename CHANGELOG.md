# Changelog

All notable changes to **webconsulting/desiderio** are documented in this
file. The format follows [Keep a Changelog](https://keepachangelog.com/)
and the project adheres to [Semantic Versioning](https://semver.org/).

## [4.0.6] — 2026-08-27

### Security

- Raised the TYPO3 runtime floor to 14.3.6 and refreshed the complete Composer
  dependency graph so the extension cannot install with the superseded core
  patch levels.
- Updated the frontend toolchain lockfile to remove the published `nanoid`
  denial-of-service advisory.

### Changed

- The development integration now tests against the v14-ready Blog 15.0.3
  release from the maintained fork.
- Regenerated the checked-in Fluid control primitives from the current pinned
  shadcn preset recipe, including its improved focus-visible field states.
- Raised the local functional-test memory limit to 1 GiB for TYPO3's compiled
  dependency-injection container on the current dependency set.

## [4.0.5] — 2026-08-07

### Fixed

- The shared page header now uses the native shadcn H1 typography variant:
  36 px on compact screens and 48 px on desktop. This keeps inner-page titles
  clearly hierarchical without restoring the oversized legacy masthead.

## [4.0.4] — 2026-08-07

### Changed

- Corporate content, sidebar and search pages now reuse the shared atomic
  PageHeader Organism for one consistent, compact H1 treatment. News detail
  views continue to suppress the page header in favour of the article H1.

### Fixed

- Restored the standalone element-library preview on TYPO3 14 by configuring
  its `FLUIDTEMPLATE` through the supported `file` property.
- Added the disposable TYPO3/Vite browser harness and made it exercise built
  production assets. The release passes 263 anatomy previews and 1,578
  responsive, theme and accessibility renders without findings.
- Restored the PHP 8.4/8.5 unit, functional and PHPStan quality gates.

## [4.0.3] — 2026-08-06

### Fixed

- Header, breadcrumb and footer Organisms now own their complete Fluid markup.
  Component rendering no longer delegates to page partials whose template
  paths are unavailable inside an isolated Fluid component context.

## [4.0.2] — 2026-08-06

### Fixed

- Default element-library seeds now exclude `featuredemo` as well as every
  `video` CType. Video-capable components remain available behind the explicit
  `--include-video` option.

## [4.0.1] — 2026-08-06

### Fixed

- Added the official `Configuration/ViteEntrypoints.json` declaration required
  by `vite-plugin-typo3`, so project builds discover both Desiderio entries.

## [4.0.0] — 2026-08-06

### Changed

- Replaced direct TypoScript asset lists with the official
  `praetorius/vite-asset-collector` integration and benchmark-compatible Vite
  entries. No custom manifest reader, dev-server detector or asset ViewHelper
  is part of Desiderio.
- Replaced the inherited Fluid Styled Content dependency with Desiderio's own
  `ClassicContent` TypoScript, Fluid layouts, templates and partials.
- Raised the runtime floor to PHP 8.4 and the CI matrix to PHP 8.4/8.5.
- Introduced a typed organism layer for the site header, page header,
  breadcrumb and footer while keeping independent page and content trees.
- Made video demos explicitly opt-in. Default seeds create no video content or
  media; the accessible video Content Blocks and generation tools remain.

### Removed

- Removed all generated demo video files and existing video seed payloads.

## [3.8.2] — 2026-07-26

### Fixed

- **The footer's legal nav can no longer wrap raggedly.** The 1024 px stacking
  breakpoint was tuned to this site's own strings; it held for three short
  links and a short copyright and nothing else. Measured on the built site: add
  two more legal links, or a copyright with an address in it, and "Imprint
  Privacy" sat on one line with "Accessibility" ragged underneath again — from
  1025 px up, where no breakpoint was watching. The legal row is now `nowrap`
  (and each label `white-space: nowrap`) while the bar is a row, so its
  min-content width becomes the grid track's floor and the copyright — the one
  part that loses a line gracefully — takes the squeeze instead. Wrapping is
  restored inside the stacked block, where the nav is centred and an extra line
  is the right answer rather than a defect. Verified at 1025/1100/1200/1280/1440
  with five links, a long copyright and German labels: one row, no overflow, in
  every combination.

## [3.8.1] — 2026-07-26

### Fixed

- **The corporate preset no longer cancels the footer's responsive stacking.**
  `preset-corporate.css` re-declared `.desiderio-footer__inner` with the same
  `1fr auto 1fr` grid the base theme already has; loading after
  `desiderio.css`, that duplicate silently overrode the 1024 px stacking rule,
  so sites on the corporate preset kept the ragged three-column footer at
  every narrow width. Presets style look, not structure — the duplicate is
  gone.

## [3.8.0] — 2026-07-26

### Fixed

- **The definition registry sees every host's elements, not only this one's.**
  `getDefinitions()` scanned `EXT:desiderio/ContentBlocks/ContentElements` and
  nothing else. The seeding cleanup derives its list of collection child tables
  from those definitions, so a downstream extension's child rows were never
  deleted: every reseed left the previous run's rows behind, still `deleted=0`,
  pointing at content elements that no longer existed. One lab install had
  1,501 live person rows of which 162 were reachable. The registry now scans
  every host returned by `ElementCatalog::hostExtensions()` — the same list the
  element library already uses, so registering `libraryHostExtensions` is all a
  provider needs. Elements from other extensions must declare an explicit
  `typeName:`; guessing one from a directory name would invent a wrong CType.

## [3.7.0] — 2026-07-26

### Fixed

- **Shared RecordTypes are found in downstream extensions.** `getRecordTypeFields()`
  resolved its search path relative to this package, so a Collection declaring
  `foreign_table:` in another extension resolved to an empty field list. The
  seeders then wrote child rows whose File fields were silently left at `0` —
  every portrait, logo and avatar inside a shared collection came out blank,
  with no error anywhere. Extensions now add their own directory by appending an
  absolute path to
  `$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['desiderio']['recordTypePaths']`,
  the same convention as `libraryHostExtensions`. Paths stay absolute because
  the method is pure and is called from unit tests with no TYPO3 bootstrap.
- **Generated demo values respect a field's `max:`.** A field declaring
  `max: 24` is a short column, and generated copy is written for readability
  rather than width, so an INSERT could fail with "Data too long for column"
  and abort an entire seeding run. Defaults are now cut to fit. This surfaced
  the moment the fix above made shared RecordType fields visible for the first
  time.

## [3.6.0] — 2026-07-26

### Added

- **A themes page that shows all fifteen presets at once, live.** "What
  changes if I pick a different preset" is a question a table answers badly
  and a sample answers immediately. The seeded `/themes` page carries one
  card per preset — real buttons, badges, fonts and corner radius, painted by
  that preset's own tokens — followed by a matrix of everything that is not
  colour: heading, body and code faces, radius, control density, focus-ring
  width, elevation and icon library.

  This works because `Build/Scripts/build-preset-overview.php` re-scopes every
  preset from `body[data-shadcn-preset="…"]` to
  `[data-shadcn-preset-sample="…"]` in a generated
  `Resources/Public/Css/preset-samples.css`, so a card can carry a preset and
  fifteen of them can paint themselves on one document. Nothing on the page is
  a screenshot, and nothing is written by hand: the same script generates the
  cards and the matrix from `shadcn-theme.css` and `IconRegistry`, so the page
  cannot describe a preset differently from how the preset renders.
  `Tests/Unit/PresetOverviewTest.php` fails if the committed artefacts fall
  behind the stylesheet.

  New `DesiderioThemes` backend layout (EN/DE/ES/FR/HU labels); showcase pages
  may now request their own layout via `backendLayout`.

- **Library folders keep their host scope on reseed.** `desiderio_grande`
  registering itself as a library host made every plain
  `desiderio:library:seed` of an existing folder pull in its whole 250-element
  catalog. Without `--hosts`, the seed now infers the allowed hosts from the
  records already in the folder (a fresh folder still gets every registered
  host) — a host extension installed later has to be opted in explicitly.

### Fixed

- **The page footer stacks before it wraps.** The brand / copyright / legal
  bar is a `1fr auto 1fr` grid that needs roughly 1000 px; between 768 px and
  that width the legal links wrapped into a ragged second line. The footer
  now stacks — centered, as one unit — at the shared 1024 px breakpoint.

## [3.5.2] — 2026-07-26

### Fixed

- **The categorized-content menu renders its records.** Desiderio's template
  handed the Menu partial `{menu}`, but core's processor exposes the matched
  rows as `{content}` — the override had never listed anything. The template
  now builds the same card grid from the rows, each card linking to the
  record on its page; the library demo tags the table + textmedia demos (the
  quote CType assumed before does not exist), so the demo shows a real
  two-item menu.

## [3.5.1] — 2026-07-26

### Fixed

- **The categorized-menu demos no longer 500 — and neither does the element.**
  Core's `menu_categorized_content` TypoScript inlines
  `field:selected_categories` into `IN(|)`; with no categories selected that
  is `IN()` — a SQL syntax error the moment an editor inserts the element
  without picking categories. Desiderio's set now skips the query processor
  when the field is empty (the template renders an empty menu instead). The
  library seed additionally gives each folder two demo categories, points
  both categorized-menu records at them and tags the quote + table demos, so
  the menu demo lists real records instead of nothing.

## [3.5.0] — 2026-07-26

### Added

- **The browser harnesses run in CI.** `Build/CiApp/` is a disposable TYPO3
  application — SQLite, no services; `bootstrap.sh` goes from nothing to a
  seeded element library and `php -S` serves it. The new `browser-qa`
  workflow boots it on every push and pull request, renders every seeded
  element and fails on findings: the anatomy survey plus the tier‑1 sweep
  (base preset, light + dark, 390/768/1440), with the full tier set one
  `workflow_dispatch` away and the HTML report attached as an artifact.
  Elements gated on extensions the app does not install (powermail, innesto)
  are covered by the lab sweep instead.
- **Spacing is enforced, not aspirational.** The last ~79 margin/padding/gap
  literals moved onto the `--d-spacing-*` ramp; list-marker indents and one
  optical baseline nudge became `em`, because they must scale with their
  font. The new zero-tolerance audit category `css_untokenised_spacing`
  allows `0`, `auto`, percentages, negative values and `em` — a positive
  `px`/`rem` literal on a spacing property now fails CI.
- **`css_static_multicolumn` replaces the `css_no_responsive_rule` advisory.**
  Classifying all 99 flagged elements showed every one is a single-column
  stack, an icon-gutter grid or a wrapping flex row — none needs a
  breakpoint, and the 390 px sweep proves each render. What the advisory was
  actually for is now sharp enough to gate: two or more content-sized grid
  tracks (or `column-count ≥ 2`) with no width media query fail CI;
  `auto-fit`/`auto-fill` grids reflow on their own and are exempt.
- **The anatomy survey can no longer sleep through a broken render.** A 500
  delivers a perfectly loadable error page, which used to survey as "no
  anatomy" and stay green; non-2xx previews are now `render-failed` findings
  and a non-empty finding list sets the exit code.

### Changed

- **The template dependencies are declared.** The first install without the
  lab's stack (the CI app) turned up 58 elements failing on `f:render.link`,
  which only `webconsulting/visual-editor-enhancements` provides (TYPO3 v14
  core covers `f:render.text`). `composer.json` now requires
  `friendsoftypo3/visual-editor` `^1.8` and
  `webconsulting/visual-editor-enhancements` `^0.8 || dev-main`. The latter
  is distributed via Git, not Packagist — projects installing desiderio add
  `https://github.com/dirnbauer/typo3-visual-editor-enhancements.git` as a
  VCS repository.
- The pie chart's element-library demo shows the **large** variant, matching
  the size at which the other chart demos present themselves.

### Fixed

- **Destructive text on its own wash meets WCAG AA.** The shadcn registry
  recipe pairs `text-destructive` with `bg-destructive/10`, which measures
  4.0:1 in light mode — the sweep's last seven findings, all on the Powermail
  alert box. `alertDestructive`, `flashDestructive`, `buttonDestructive` and
  the server error-summary heading now use `--d-danger-text` (the hue pulled
  30 % toward `--foreground`), applied in the primitive generator so the
  synced recipes stay reproducible. The full-catalog sweep is at **zero
  findings** for the first time.

## [3.4.0] — 2026-07-25

### Changed

- **The Layout field returns to the Appearance tab, marked as reserved.**
  3.3.0 removed it because it rendered nothing; keeping the field visible with
  an honest in-form description ("Reserved for a future version — currently
  has no visual effect", EN + DE) preserves stored values and lets a later
  version give it meaning without any migration. Frame and the space fields
  remain fully wired and audit-enforced; `layout` is exempt from
  `appearance_field_unwired` until it gains its mapping.


## [3.3.0] — 2026-07-25

### Changed

- **The Appearance tab now does what it says.** Core's `TYPO3/Appearance`
  basic put four controls (Layout, Frame, Space Before/After) into all 244
  element forms, and none of them rendered anywhere — editors changed a
  dropdown and nothing happened. It is replaced by `Desiderio/Appearance`:

  - **Frame** is the Section surface picker (page background, muted, card,
    accent, primary, secondary), feeding the Section component's existing
    surface system with correct foreground pairing. An editor's choice
    overrides a template's own background default (only `cta` sets one).
  - **Space before/after** adds outer section margin on the spacing ramp,
    keeping core's value set (`extra-small…extra-large`) so stored content
    stays portable. Classes: `frame-space-before-*` / `frame-space-after-*`.
  - **Layout is gone from the form.** Core's "Layout 1…3" has no meaning in
    this design system; stored values remain in the database untouched.

  All 227 section-rendering templates pass the fields to
  `<d:layout.section>`; the 17 utility elements (footers, floating banners,
  dividers) carry no appearance tab, because a section surface has no meaning
  there. Guarded twice: the audit's `appearance_field_unwired`
  (zero-tolerance) fails any element whose template does not consume the
  fields, and the structure test bans `TYPO3/Appearance` outright.

### Fixed

- The new basic's palette initially reused core's `frames_palette`
  identifier. tt_content palettes are GLOBAL, so core's palette (with
  `layout`) was overwritten for every type still using it — all 19
  EXT:innesto elements threw `RecordPropertyNotFoundException` on render,
  because their compiled definitions still declared `layout` while the record
  no longer carried it. The palette is now `desiderio_frames_palette`. Caught
  by the render sweep before release.


## [3.2.0] — 2026-07-25

The element library becomes a service any theme extension can plug into,
instead of a fixed list of two providers. Desiderio still ships the elements
and the seeding, previewing and search machinery; a second theme now supplies
its own elements and its sites list only those.

### Added

- **Provider registration.** An extension joins the element library catalog
  with one line in its `ext_localconf.php`:

  ```php
  $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['desiderio']['libraryHostExtensions'][] = 'my_theme';
  ```

  It then needs only the documented per-element file contract. Registration
  stays opt-in rather than being derived from the Content Blocks registry:
  that API is `@internal`, and auto-ingesting every block-shipping extension
  would fill the picker with elements that carry no demo content, keywords or
  descriptions. The resolved host list is part of the catalog cache
  fingerprint, so installing a provider invalidates the cache on its own.
- **`elementLibrary.hosts`** site setting — a comma-separated list of the hosts
  a site's picker offers (`"desiderio,innesto,core"`; `core` means the native
  TYPO3 content types). Empty keeps the previous behaviour of listing every
  installed provider. Search results, suggestions and "did you mean" are
  scoped to the same list, so a site never proposes an element it cannot style.
- **`desiderio:library:seed --hosts=…`** — seeds a site's library folder with
  the matching subset. Records of other hosts are pruned from that folder as
  usual, so each site's demo records mirror its own theme.

### Fixed

- **Wizard icons never appeared in the picker.** Content Blocks publishes an
  element's assets under the block's *vendor* segment
  (`Resources/Public/ContentBlocks/<vendor>/<element>/`), but the catalog built
  the path without it, so `is_file()` always missed and every card fell back to
  its title. Icons now resolve through the vendor segment, with the old path
  kept as a fallback.
- **A provider's `library.json` could not reference its own assets.** Every
  file reference was resolved against `EXT:desiderio/`. A full `EXT:<key>/…`
  reference is now honoured; bare paths still resolve against Desiderio, which
  is how all of our own fixtures are written.

## [3.1.0] — 2026-07-25

### Added

- **`Documentation/Developer/DesignPhilosophy.md`** — the design system's
  conventions, written from measurement rather than taste, with every rule
  naming the check that enforces it.
- **Type scale tokens.** `--d-leading-tight/snug/normal/relaxed` (leading is a
  function of SIZE), `--d-weight-light…display` (`display` is reserved for
  numerals that carry the message — prices, metrics, counters) and
  `--d-tracking-tight/normal/wide` (0.05em is the uppercase-eyebrow
  convention). Before these existed the catalog used 16 distinct line-heights,
  and heading-scale leadings overlapped body-scale leadings almost completely.
  692 declarations across 214 elements now use the scale; the audit gates
  `css_untokenised_typography` at zero (`line-height: 0/1` stay literal — they
  are layout resets, not typography).
- **`--d-hero-y`** — one knob for hero vertical presence,
  `clamp(4rem, 6vw + 2rem, 6rem)`. All eight heroes padded at 4–5rem while
  ordinary sections breathe 6rem at desktop: the biggest moment on the page
  had the least air. The catalog's vertical rhythm is now 96/48/24/0.
- **`18-section-anatomy.css`** — shared, zero-specificity (`:where()`) layout
  conventions: reading measure capped at 70ch (section intros) / 75ch (rich
  text, `.ce-bodytext`, content slots, legal documents), and header-unit
  alignment inherited so eyebrow, heading and subheadline cannot drift apart.
  The survey found 41 elements running paragraphs to 137ch; the finding count
  is now zero. Elements rendering outside `.desiderio-section`
  (footer-newsletter, cookie-banner) are named explicitly; the announcement
  bar is deliberately exempt (a banner line is full-width by design).
- **`Build/VisualQa/anatomy.mjs`** — renders all 288 seeded elements and
  reports padding, alignment, heading-size and reading-width distributions
  plus outliers. This survey is where the conventions came from; target after
  any element batch: 0 findings.

### Changed

- Centered section intros put `margin-inline: auto` on their capped paragraph,
  next to the `text-align: center` it belongs to — a capped block box would
  otherwise hug the start edge under a centered heading.
- Document-like elements (imprint, privacy-notice, legal-disclaimer,
  accessibility-statement, changelog) cap their measure per text node, so it
  holds in the paragraph's own font.
- The visual harness ignores console noise from third-party embeds (YouTube's
  own permissions-policy complaints), which could fail a 2,976-render sweep on
  one flaky line.

### Fixed

- `special-offer` used `font-weight: 900` (heavier than anything else in the
  catalog) and a 0.1em eyebrow tracking; both now sit on the scale.

## [3.0.1] — 2026-07-25

### Fixed

- **Chart plots were stranded inside oversized boxes.** A `max-height` on an
  `aspect-ratio` box overrides the ratio, and the SVG's default
  `xMidYMid meet` then scales the drawing to the short side and centres it. On
  a Data & Dashboards page that left `chart-sparkline` drawing 403px wide
  inside a 1166px box (763px of dead space), `chart-line` 483px short and
  `chart-stacked-bar` 391px short. All three now cap with `max-width` derived
  from the viewBox aspect, so the box always equals the artwork. Dead space
  across every chart on the page is now zero.
- `chart-sparkline` drew into a bespoke 420×150 viewBox. Because SVG font sizes
  are user units that scale with the viewBox, giving it a full-height box
  rendered its axis labels at 2.5×. It now uses the same 640×300 geometry as
  `chart-area` and `chart-line`, so its plot and its type match the neighbouring
  charts exactly (802×376, 15px labels).
- `chart-stacked-bar` declared `aspect-ratio: 16 / 9` while its viewBox is
  720×390, and its axis labels used a bespoke `0.6rem` that rendered around 9px.
  Both now follow the real viewBox and the shared type token.

### Added

- `--d-chart-plot-height`, the drawn height every wide chart settles at, so a
  page stacking several of them reads as one family. Chart plots must cap with
  `max-width: calc(var(--d-chart-plot-height) * <vbW> / <vbH>)` — never
  `max-height`, which is what broke the aspect ratio in the first place.


## [3.0.0] — 2026-07-25

### Changed (BREAKING)

- **29 collection child tables are now 12 shared record types.** Collections
  whose child field definitions were byte-identical point at a Record Type in
  `ContentBlocks/RecordTypes/` via `foreign_table:` +
  `shareAcrossTables`/`shareAcrossFields` instead of owning a table each.
  Existing installations MUST run the upgrade wizard:

      vendor/bin/typo3 upgrade:run desiderioSharedCollectionTables

  The wizard only INSERTs. Source tables are left in place — rename them to
  `zzz_deleted_<table>` once you are satisfied, and drop them later. Every
  (old table, old uid) → new uid pair is recorded in
  `tx_desiderio_collection_uid_map`, which makes the move auditable, reversible
  and re-runnable.

  Scope was set by measurement, not by column names: grouping tables by SQL
  columns suggests 83 are mergeable, but `foreign_table:` makes each sharer's
  own `fields:` inert, so only collections with identical field DEFINITIONS
  qualify. 11 groups covering 54 tables diverge behaviourally — in 10 of them on
  a single `required` flag — and were deliberately left alone. See
  `Documentation/Developer/CollectionTableConsolidation.md`.

  Templates, fixtures and `library.json` are unaffected: field identifiers and
  the `tt_content` counter columns are unchanged by construction.

      desiderio_icon_card_link  ← benefit_cards_items, feature_carousel_items, feature_grid_3_items
      desiderio_label_value     ← case_study_featured_metrics, product_specs_specs, stats_inline_stats
      desiderio_logo_item       ← logo_carousel_logo_items, logo_cloud_logo_items, logo_grid_logo_items
      desiderio_metric_item     ← analytics_overview_items, metric_dashboard_items, stat_cards_items
      desiderio_nav_item        ← nav_pagination_items, nav_tabs_tabs, navbar_tabbed_tabs
      desiderio_icon_lead       ← feature_grid_4_items, feature_icons_items
      desiderio_label_item      ← hero_pricing_feature_items, hero_product_feature_items
      desiderio_name_item       ← hero_logo_cloud_logo_items, hero_saas_logo_items
      desiderio_person_min      ← org_chart_people, team_department_members
      desiderio_qa_item         ← faq_items, pricing_faq_question_items
      desiderio_quote_item      ← testimonial_grid_testimonials, testimonial_wall_testimonials
      desiderio_title_content   ← feature_accordion_items, privacy_notice_sections

### Fixed

- **`CollectionCleanupService` could delete a sibling collection's rows.** It
  filtered children on `foreign_table_parent_uid` alone; on a shared table two
  fields of one record produce rows with the same parent uid, so reseeding one
  field would have taken the other's content with it. It now also matches
  `tablenames` and `fieldname`, and deletes by the uids it resolved rather than
  re-running the predicate, so lookup and delete cannot drift apart. Latent
  before this release; a data-loss bug the moment any table is shared.
- `ContentBlockDefinitionRegistry` resolves `foreign_table:` to the record
  type's field list. Without it every seeder saw an empty child definition and
  would have silently seeded nothing. It also no longer needs a booted TYPO3 to
  build a definition from config.
- `CollectionRecordSeeder` writes `tablenames`/`fieldname`, taking a nested
  collection's identifier from its payload key — nested collections carry no
  explicit `column`, and without the fallback their rows were written with an
  empty `fieldname` that the TCA cannot match and no cleanup can find. Writing
  an empty `fieldname` into a shared table is now a hard error.

### Added

- `Build/Scripts/derive-collection-merge-map.php` derives the consolidation map
  from the configs, and `Build/Data/collection-merge-map.json` freezes it.
- `Build/Scripts/verify-collection-merge.php` proves zero loss by comparing a
  multiset of per-row payload hashes (payload, parent, language, sorting)
  before and after, plus file-reference resolvability. Run `compare` before any
  reseed. Reference run: 722 rows, 90 file references, PASS.
- `Tests/Unit/SharedCollectionRecordTypeTest` and two audit categories
  (`shared_collection_missing_flag`, `shared_collection_inert_fields`), gated at
  zero, covering the silent failure modes of table sharing.


## [2.14.0] — 2026-07-25

### Added

- **Per-element demo content for the element library.** Every content element
  now ships a `library.json` (and a German `library.de.json`) next to its
  `fixture.json`. The two are deliberately separate: `fixture.json` is the
  styleguide's content and sells Desiderio itself, while `library.json` has to
  look like a page the editor could keep. Payloads may be partial — anything
  omitted is completed by the demo value generator as before.
- `desiderio:library:seed --locale=de` seeds a folder from the German source
  copy, with an unconditional per-element fallback to English. This picks the
  source language of a folder; records are still written as language 0.
- `desiderio:library:urls` prints the signed preview URL of every library
  record, so visual-QA tooling never has to reimplement cHash generation.
- `Build/Scripts/audit-theme-contrast.php` proves 420 WCAG token pairs across
  all 14 presets and both schemes analytically (OKLCH → sRGB, including
  `color-mix`), which makes "check every colour scheme" a one-second check
  instead of a screenshot sweep.
- `Build/VisualQa/` renders every element at 390/768/1440 in light and dark and
  asserts overflow, text clipping, empty renders, text overlap, broken images,
  video sources that are not videos, console errors and axe colour-contrast.
- Four CSS hygiene checks in the element audit, gated at zero: non-standard
  breakpoints, `prefers-color-scheme` blocks (which would ignore the `.dark`
  toggle), raw `font-family` (which would defeat the preset font swap), and
  fixed widths that overflow a 375px phone.
- 49 demo assets under `Resources/Public/Styleguide/Library/`, generated from
  the committed prompt manifest `Build/Data/library-image-prompts.json`.

### Changed

- **Element library media is chosen by semantic role, not by hashing the field
  name.** `LibraryImageAssetProvider` maps a field to video / audio / captions /
  portrait / logo / badge / QR / product-UI / illustration / hero / editorial /
  document and picks within that role. Roles with no sane substitute return
  nothing rather than a wrong-format file. The styleguide seeder is untouched.
- The demo cast is 12 people, indexed with the same modulus as the portrait
  pool, so the face always belongs to the name.
- `resolveFixtureFields()` and `buildContentInsert()` accept an optional
  definition, letting a foreign host extension (innesto) hand in the one it
  built from its own `config.yaml`. Its elements can now carry authored demo
  content instead of falling back to the generator.
- `demoSubjects()` no longer returns eight interchangeable slogans. Those were
  the reason the picker was unreadable — the same headline appeared on dozens
  of unrelated elements.

### Fixed

- White `--destructive-foreground` on `--destructive` measured **2.89:1** in
  dark mode in every preset, and `.btn--destructive` / `.badge--destructive`
  both paint label text with it.
- `--ring` measured **2.32–2.59:1** on white in the four imported shadcn
  presets, below the 3:1 WCAG 2.4.11 focus-indicator minimum.
- `feature-numbered`'s step numeral was `color-mix(--primary 25%, transparent)`,
  which composites to **1.7:1** in both schemes — the only sequence cue a
  sighted reader gets.
- A field declared `common-media-types` and labelled "Image or Video" resolved
  to video and was handed an `.mp4` that its template rendered inside an
  `<img>`. `allowed` now only settles the role when it excludes images.
- `leaderboard`'s optional `avatar_url` takes precedence over the uploaded
  portrait and was filled with a fabricated `example.com` URL, so it both 404'd
  and hid the real image.
- Generated asset credits are no longer emitted: they land in
  `sys_file_reference.description`, which templates render as a visible
  caption. Unsplash assets keep their attribution.


## [2.13.1] — 2026-07-12

### Fixed

- Content Block collections now use Content Blocks' native relation resolution
  instead of Desiderio's custom collection query. Workspace previews no longer
  render both the live and draft versions of inline collection items.

### Changed

- Content overhaul — attribution sweep across the seeded styleguide. Every
  external extension and library Desiderio builds on now gets a direct
  thank-you, and **Netresearch's AI suite is featured throughout the agentic
  story**. New credit sections on the ecosystem (`/features`),
  technical-features, TYPO3 v14 strategy, and Skills pages name and link
  Netresearch DTT GmbH's `nr_llm` (shared LLM foundation), `nr_mcp_agent`
  (backend AI assistant), `nr_vault` (encrypted secrets), and `t3_cowriter`
  (AI cowriter) — the four extensions the lab actually runs — alongside
  in2code (Powermail), Georg Ringer (News), TYPO3 GmbH (Blog), dkd Internet
  Service GmbH (Apache Solr for TYPO3), Studio Mitte (Friendly Captcha), the
  friends of TYPO3 Visual Editor and Content Blocks teams, shadcn/ui,
  Tailwind, Prism, Alpine.js, and the bundled font and icon sets. The demo
  news seeder gained matching upstream-author credits. Content only — no
  schema, count, or behaviour changes.

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
[4.0.6]: https://github.com/dirnbauer/desiderio/releases/tag/v4.0.6
[4.0.5]: https://github.com/dirnbauer/desiderio/releases/tag/v4.0.5
[Unreleased]: https://github.com/dirnbauer/desiderio/compare/v4.0.6...HEAD
