# Desiderio

Desiderio is a TYPO3 v14.3 theme extension with a shadcn/ui-inspired Fluid 5 component library, 255 Content Blocks, page templates, optional Blog/News/Solr/Powermail overrides, and a runtime theme system driven by TYPO3 site settings.

It is built for TYPO3 installations that need a complete editorial and marketing component set without a frontend build step on the target site. The committed assets include the Tailwind v4/shadcn CSS theme, lightweight interaction JavaScript, a small Prism syntax-highlighting bundle, and chart helpers.

## Requirements

- TYPO3 CMS `^14.3`
- PHP `^8.3`
- `friendsoftypo3/content-blocks` `^2.2`
- Composer-based TYPO3 installation

Optional integrations are activated through separate site sets and only apply when the matching extension is installed:

- `georgringer/news`
- `t3g/blog`
- `apache-solr-for-typo3/solr`
- `in2code/powermail`
- `studiomitte/friendlycaptcha`

## Installation

```bash
composer require webconsulting/desiderio
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

Enable the site sets in this order:

1. `Desiderio Base` (`webconsulting/desiderio`)
2. `Desiderio Content Elements` (`webconsulting/desiderio-content-elements`)
3. A scenario package, for example `webconsulting/desiderio-preset-corporate`
4. Optional integration sets such as Blog, News, Solr, or Powermail

## What Is Included

Desiderio provides three layers:

- **Components**: 17 atoms, 28 molecules, and 4 layout primitives (49 typed Fluid components total).
- **Content Blocks**: 255 editor-facing content elements grouped for heroes, features, data, conversion, editorial, media, social proof, navigation, forms, and footer patterns.
- **Theme**: backend layouts, page templates, header/footer templates, CSS variables, JavaScript interactions, and site settings.

The current shadcn base is `radix-lyra` with the `b6G5977cw` mono olive preset and Tabler icons. The runtime preset selector also includes four other `ui.shadcn.com/create` presets, ten bundled house presets, and `custom`.

## Runtime Theming

The active look is selected in TYPO3 site settings and rendered as `data-*` attributes on the `<body>` element:

- `desiderio.shadcn.preset`
- `desiderio.shadcn.style`
- `desiderio.shadcn.iconLibrary`
- `desiderio.layout.density`
- `desiderio.layout.container`
- `desiderio.layout.radius`
- `desiderio.typography.fontSans`
- `desiderio.theme.darkModeDefault`
- `desiderio.theme.darkModeToggle`

Preset changes repaint colors, radius, density, focus rings, fonts, and icon library behavior at runtime. Stored content uses semantic icon keys, so the icon library can be changed without rewriting records.

## Icon Fonts

All icon webfonts are **self-hosted inside the package** — no CDN requests, no
external dependencies, no GDPR exposure. Content icons themselves render as
inline SVGs (`IconViewHelper` emits one `<svg>` per library; CSS reveals the
one matching `body[data-icon-library]`), so the webfonts are an additional
offering for editors and custom markup, loaded per configured library.

### How loading works

The page layout resolves the configured library to a bundled stylesheet:

```html
<f:asset.css
    identifier="desiderioIconFont"
    href="{di:iconFont(library: site.configuration.settings.desiderio.shadcn.iconLibrary)}"
    priority="1"
/>
```

`di:iconFont` calls `IconRegistry::fontStylesheet()`, which maps every
supported library to `EXT:desiderio/Resources/Public/IconFonts/<library>/<library>.css`.
Each directory contains exactly three files: the stylesheet (rewritten to a
single relative `woff2` source), the `woff2` font, and the upstream license.

### Licensing

Every bundled font was license-checked for redistribution inside a
distributable TYPO3 package (verified June 2026):

| Library | License | Bundled from | Redistribution |
| --- | --- | --- | --- |
| Lucide | ISC | `lucide-static` | allowed |
| Tabler Icons | MIT | `@tabler/icons-webfont` | allowed |
| Phosphor Icons | MIT | `@phosphor-icons/web` (regular weight) | allowed |
| Remix Icon | Apache-2.0 | `remixicon` | allowed |
| HugeIcons | proprietary font / MIT SVG data | generated in-house, see below | official font **forbidden**, own build allowed |

**HugeIcons is the special case.** The official hugeicons webfont (formerly
loaded from `cdn.hugeicons.com`) must not be redistributed — the
[license agreement](https://hugeicons.com/license-agreement) explicitly covers
the free versions and forbids shipping their icon fonts in downloadable
packages. Their SVG icon *data* (`@hugeicons/core-free-icons`) is MIT,
however, so Desiderio compiles its own webfont from it:

1. all 6,156 free icons are exported as stroke SVGs from the MIT data,
2. strokes are outlined into filled paths with `picosvg` (icon fonts cannot
   render strokes; the venv lives in `var/picosvg-venv`),
3. `fantasticon` compiles `hugeicons.woff2` + `hugeicons.css` with explicit
   codepoints in the Unicode Private Use Area (U+E001 ff. — the default
   numbering would overflow past U+FFFF and silently drop glyphs).

The resulting font is Desiderio's own MIT-licensed build — it is *not* the
official hugeicons webfont, and `Resources/Public/IconFonts/hugeicons/LICENSE-MIT.txt`
documents that provenance. Never re-add the hugeicons CDN link or copy their
official font files into the package; `IconRegistryTest` pins both rules.

### Updating the fonts

```bash
npm run build:iconfonts       # re-sync Lucide/Tabler/Phosphor/Remix from node_modules
npm run build:hugeicons-font  # regenerate the HugeIcons font from MIT SVG data
```

Run these after bumping the corresponding npm packages. The HugeIcons build
caches its outlining step in `var/hugeicons-font/`; delete that directory to
force a full rebuild (~10 minutes for all icons).

### Usage

Font classes follow each library's upstream conventions, e.g.
`<i class="hgi hgi-home-01"></i>` (HugeIcons build), `.icon-*` (Lucide),
`.ti ti-*` (Tabler), `.ph ph-*` (Phosphor), `.ri-*` (Remix Icon). For content
elements prefer the semantic `d:icon` component — it stays library-agnostic
and switches with the preset.

## Content Elements

The content-element set includes, among others:

- Hero and landing intro sections
- Feature grids, feature sliders, timelines, tabs, accordions, and comparison blocks
- Data visualizations with axes, units, legends, multiple colors, and accessible summaries
- Code examples using a lightweight Prism/Astro highlighter instead of accent-colored pseudo highlighting
- Testimonial, logo, team, advisor, review, and case-study sections
- Form, newsletter, download, lead, conversion, and feedback blocks
- Header, search, navigation, sitemap, and footer sections

Images are rendered through TYPO3 FAL and Content Blocks fields. Media templates use stable aspect-ratio wrappers and `object-fit` rules to avoid stretched images.

## Grafting Elements from shadcn Registries (Innesto)

[Innesto](https://github.com/dirnbauer/innesto) is a companion extension that
grafts components from any [shadcn/ui registry](https://registry.directory/)
— shadcn/ui, Magic UI, Origin UI, Aceternity UI, … — onto Desiderio as
additional Content Blocks elements:

```bash
vendor/bin/typo3 innesto:add magicui/marquee --ai
```

**Why it works well here:**

- One command fetches the registry item, converts its CSS and theme variables,
  scaffolds a complete element, and registers it in the New Content Element wizard.
- Grafted elements use the Desiderio semantic tokens, so they follow the active
  theme preset and dark mode automatically — no frontend build step.
- The React→Fluid finishing pass is prompt-assisted (`--ai`) and reproducible.

**What might not work:** React markup and props always need a finishing pass
(automated or manual); npm/registry dependencies are not resolved; and heavily
interactive components — comboboxes, command palettes, drag-and-drop — don't
graft well, since they are state machines rather than documents. Best results
come from presentational components: marquees, logo clouds, bento grids,
animated lists.

See the [step-by-step manual with a worked example and screenshots](https://github.com/dirnbauer/innesto/blob/main/Documentation/AddingContentElements.md).

## Forms

Desiderio ships shadcn-styled TYPO3 Form Framework templates and a shared `FormRenderer` molecule. Form controls use neutral theme borders by default and switch to destructive/red styling only for invalid states.

Supported form features:

- TYPO3 Form Framework finishers
- Friendly Captcha integration and local Development-context test mode
- Brevo contact synchronization through `BrevoContactFinisher`
- Sanitized Brevo event tracking
- DDEV/Mailpit-friendly local mail configuration
- Styled validation messages and accessible required-field indicators

Brevo is configured through site settings plus an external API key:

- `desiderio.forms.brevo.enabled`
- `desiderio.forms.brevo.listIds`
- `desiderio.forms.brevo.strict`
- `desiderio.forms.brevo.trackEvent`
- `desiderio.forms.brevo.eventName`
- `BREVO_API_KEY`

## Search

The Solr integration set registers shadcn-styled search templates, result cards, facets, suggestions, and a suggest endpoint. The site header search can be enabled through settings and pointed at an existing search result page:

- `desiderio.search.enabled`
- `desiderio.search.targetPageId`
- `desiderio.search.queryParameter`

The frontend JavaScript enhances compatible Solr forms with debounced suggestions and keyboard-accessible result options.

## Console commands

Desiderio ships Symfony console commands for demo content and integration setup:

| Command | Purpose |
| --- | --- |
| `desiderio:styleguide:seed` | Create or update styleguide fixture pages below a parent page. Requires the live workspace; refuses Production without `--allow-production`. |
| `desiderio:starter:seed` | Create or update the corporate starter site structure and demo content. |
| `desiderio:blog:seed-pages` | Normalize an existing Blog page tree to Desiderio backend layouts. No-op when `t3g/blog` is not loaded. |
| `desiderio:news:seed-taxonomy` | Assign default category/tag relations to visible News records that have none. No-op when `georgringer/news` is not loaded. |

Examples:

```bash
vendor/bin/typo3 desiderio:styleguide:seed --parent=<page-uid>
vendor/bin/typo3 desiderio:starter:seed
vendor/bin/typo3 desiderio:blog:seed-pages --root=<blog-root-uid>
vendor/bin/typo3 desiderio:news:seed-taxonomy --storage-pid=<news-storage-pid>
```

Seed commands write FAL assets under `fileadmin/desiderio-styleguide/` or `fileadmin/desiderio-starter/`. Re-running a seeder overwrites live-workspace fixture metadata in place.

Commands are thin orchestration shells. Shared seeding logic lives in `Classes/Seeding/`:

| Service | Responsibility |
| --- | --- |
| `ExtensionFalSeeder` | FAL import and `sys_file_reference` writes |
| `CollectionRecordSeeder` | Recursive Content Blocks collection inserts |
| `CollectionCleanupService` | Live-workspace-scoped collection/FAL cleanup |
| `StyleguideFixtureResolver` | Styleguide YAML fixture normalization |
| `StarterContentBuilder` | Starter-site content block payloads |
| `BlogPageTreeSeeder` | Blog layout alignment and demo post seeding |
| `FixtureFieldNormalizer` | Shared scalar/file/checkbox/date field normalization |

`BrevoConfigurationResolver` centralizes Brevo finisher configuration precedence. See `Documentation/Developer/Index.rst` and `Documentation/Reports/code-quality.md` for the full service map and maintainability rules.

## Blog

When `t3g/blog` is installed, `webconsulting/desiderio-blog` adds shadcn-styled Blog template paths. The templates cover list, detail, sidebar widgets, comments, author blocks, related posts, metadata badges, categories, tags, and RSS output headers.

Existing Blog page trees can be aligned with `desiderio:blog:seed-pages` (see Console commands above).

## News

When `georgringer/news` is installed, `webconsulting/desiderio-news` adds shadcn-styled News templates for list and detail views. The templates use available news images, category/tag badges, responsive grids, metadata, and `NewsArticle` structured data.

The News set supports the `DesiderioNews` backend layout and progressive load-more list mode.

## Page Templates

The extension provides these page templates/backend layouts:

- `DesiderioStartpage`
- `DesiderioContentpage`
- `DesiderioContentpageSidebar`
- `DesiderioStyleguide`
- `DesiderioBlog`
- `DesiderioNews`
- `DesiderioExtension`
- fallback `Default`

The compact page title header is full width, uses a subtle themed background, and inherits the active shadcn preset.

## Development

Install dependencies and run checks:

```bash
composer install
npm install
npm run build:css
Build/Scripts/runTests.sh
Build/Scripts/runFunctionalTests.sh
```

Focused checks:

```bash
Build/Scripts/runTests.sh phpstan
Build/Scripts/runTests.sh phpunit
Build/Scripts/runFunctionalTests.sh
Build/Scripts/runTests.sh audit
composer validate
composer audit --no-dev --abandoned=fail
```

The CI workflow runs PHPStan at max level, PHPUnit on PHP 8.3–8.5, SQLite-backed functional tests, composer validation/audit, and the strict Content Blocks audit.

### CSS cascade layers

The Tailwind v4 entry point (`Resources/Private/Tailwind/desiderio.css`) uses native CSS cascade layers (`theme, base, components, utilities`). Element defaults go in `@layer base`, shared component classes in `@layer components` (so utility classes can override them), and custom utilities are declared with `@utility` — never `@layer utilities`, which is Tailwind v3 syntax. The per-feature stylesheets in `Resources/Private/Css/desiderio/` stay **unlayered** on purpose: unlayered CSS always beats layered CSS, so they override Tailwind without specificity hacks. Details in `Documentation/Developer/Index.rst` (section "CSS cascade layers").

## Visual Editor compatibility

Desiderio registers `ExtbasePluginRequestSanitizerMiddleware` to strip malformed Extbase `controller` / `action` arguments from Visual Editor persistence requests. Without it, News and other Extbase plugins can throw while rendering an edited page.

Content Block media fields should use `<f:image image="{fileReference}">` with structured Fluid `data` arguments so Visual Editor image overlays attach correctly.

The element picker ("Add content" panel) is filled by the `?elementLibrary=1` endpoint, which lists every content element. Its catalog is built from the on-disk Content Blocks definitions and **cached** (`desiderio_library`, `SimpleFileBackend`): opening the picker no longer re-parses ~255 `config.yaml` files each time. The cache key fingerprints every `config.yaml` mtime, so adding or editing an element self-invalidates it; "flush all caches" also clears it.

The preview thumbnails inside the picker are page-cached per site base. Warm them with `vendor/bin/typo3 desiderio:library:warm` — with no arguments it warms every site's library; `--folder=<uid>` warms one folder across all sites that use it; `--site=<id>` restricts to one site. Details in `Documentation/Developer/Index.rst` (sections "Element library catalog cache" and "Warming preview thumbnails").

## Documentation

Full documentation lives in `Documentation/`:

- `Documentation/Installation/Index.rst`
- `Documentation/Configuration/Index.rst`
- `Documentation/Editor/Index.rst`
- `Documentation/Developer/Index.rst`
- `Documentation/ShadcnUpgrade.md`
- `Documentation/Reports/code-quality.md` — thermo-nuclear maintainability review (v2.6.0)
