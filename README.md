# Desiderio

[![CI](https://github.com/dirnbauer/desiderio/actions/workflows/ci.yml/badge.svg)](https://github.com/dirnbauer/desiderio/actions/workflows/ci.yml)
![TYPO3](https://img.shields.io/badge/TYPO3-v14.3%20LTS-orange)
![PHP](https://img.shields.io/badge/PHP-8.3%20%E2%80%93%208.5-blue)
![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)
![Version](https://img.shields.io/badge/version-2.2.0-blue)

A self-contained TYPO3 v14.3 LTS theme extension that bundles a
[shadcn/ui](https://ui.shadcn.com)-inspired Fluid 5 component library, **255
content elements**, backend layouts, page templates, and five swappable
visual presets with a committed Tailwind v4/shadcn CSS build.

**Status:** stable · **Version:** 2.2.0 · **TYPO3:** v14.3 LTS only ·
**PHP:** 8.3 — 8.5 · **License:** GPL-2.0-or-later

> Desiderio 2.0 replaces both `webconsulting/desiderio 1.x` and
> `webconsulting/shadcn2fluid-templates 3.x`. No backward compatibility; clean
> installs only. See [SPECIFICATION.md](SPECIFICATION.md) and
> [MIGRATION-PLAN.md](MIGRATION-PLAN.md) for the rewrite rationale.
>
> The old `shadcn2fluid_*` fixture mapping is not used at runtime or by the
> styleguide seed. Demo content now lives beside each Content Block in its own
> `fixture.json`, keyed by the current `desiderio_*` CType generated from the
> Content Block folder.

## Three-layer architecture

```
┌──────────────────────────────────────────────────────────┐
│ Layer 3 — THEME                                          │
│ Page templates · Backend layouts · Header/Footer · Presets │
└──────────────────────────────────────────────────────────┘
                          ▲ renders via PAGEVIEW
┌──────────────────────────────────────────────────────────┐
│ Layer 2 — CONTENT ELEMENTS (Content Blocks)              │
│ 255 editor-facing elements in 10 wizard groups           │
└──────────────────────────────────────────────────────────┘
                          ▲ mostly composes via <d:…>
┌──────────────────────────────────────────────────────────┐
│ Layer 1 — COMPONENTS (Fluid 5)                           │
│ 16 atoms · 17 molecules · 4 layouts (typed <f:argument>) │
└──────────────────────────────────────────────────────────┘
```

## Screenshots

The screenshots below are captured from the Desiderio styleguide app using the
committed `b6G5977cw` shadcn/create preset and real Content Block fixture data.

| Light mode | Dark mode |
| --- | --- |
| ![Desiderio styleguide overview in light mode](Documentation/Images/desiderio-styleguide-overview-light.png) | ![Desiderio styleguide overview in dark mode](Documentation/Images/desiderio-styleguide-overview-dark.png) |
| The styleguide overview shows the searchable content element catalog, group navigation, and the first generated cards for the 255 shipped elements. | The same overview in dark mode verifies the token-driven surface, sidebar, badges, cards, and text contrast. |
| ![Desiderio pricing preview in light mode](Documentation/Images/desiderio-styleguide-preview-light.png) | ![Desiderio pricing preview in dark mode](Documentation/Images/desiderio-styleguide-preview-dark.png) |
| A selected `Three Tier Pricing` element shows the component inspector with the preview tab, viewport controls, and rendered pricing fixture. | The dark-mode version shows the same element preview after the theme tokens switch, including borders, foreground text, and muted labels. |

## Installation

Requires TYPO3 14.3 LTS (no v13 fallback) and PHP 8.3 – 8.5. The
`webconsulting/desiderio` package pulls in `typo3/cms-workspaces ^14.3` so
draft/preview workflows are available out of the box.

```bash
composer require webconsulting/desiderio
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

Then enable exactly one Desiderio site package. Each package pulls in the
theme base, content-element catalog, backend layouts, page chrome, production
defaults, and one website archetype.

1. Site Management → Sites → edit the target site
2. Add one `Site Package: Desiderio …` set, for example
   `webconsulting/site-package-desiderio-corporate`
3. Save and flush caches

### Tooling baseline

| Tool | Version pin | Why |
| --- | --- | --- |
| TYPO3 | `^14.3` | LTS, only supported branch — v13 is **not** supported. |
| PHP | `^8.3` (8.3 – 8.5) | Matches TYPO3 v14.3 LTS support matrix. |
| Workspaces | `^14.3` | Required, not optional, for editorial preview. |
| PHPStan | `^2.1`, **level max** | Plus `saschaegerer/phpstan-typo3` and `phpstan-strict-rules`. |
| PHPUnit | `^11.5` | All 101 unit tests pass via `Build/Scripts/runTests.sh`. |
| Content Blocks | `^2.2` | Drives every one of the 255 content elements. |

The base set, content-element aggregate, raw preset sets, and individual
generated `desiderio/*` content block set names are hidden from the backend
picker. Normal installs choose one site package; custom distribution packages
can still depend on the hidden lower-level sets directly.

## Cursor MCP (optional)

[Cursor](https://cursor.com) can load [MCP](https://modelcontextprotocol.io) servers from a
project-local `.mcp.json`. **That file is gitignored** so machine-specific URLs and any future
secrets stay out of the repository.

1. Copy the example file and adjust it:

   ```bash
   cp .mcp.json.example .mcp.json
   ```

2. **`shadcn`:** runs the shadcn MCP via `npx shadcn@latest mcp` (useful for UI work aligned with
   this theme).

3. **`my-typo3-site`:** set `url` to your TYPO3 site’s MCP endpoint (for example
   `https://<ddev-project-name>.ddev.site/mcp` when a TYPO3 MCP server is mounted under `/mcp`).
   Remove the entry or leave it out if you do not use server-side MCP.

Restart Cursor (or reload the window) after changing `.mcp.json`.

## Page templates

| Backend layout                | Content areas              | Page template                         |
| ----------------------------- | -------------------------- | ------------------------------------- |
| `DesiderioStartpage`          | `stage`, `main`            | `Pages/DesiderioStartpage.fluid.html` |
| `DesiderioContentpage`        | `stage`, `main`            | `Pages/DesiderioContentpage.fluid.html` |
| `DesiderioContentpageSidebar` | `stage`, `main`, `sidebar` | `Pages/DesiderioContentpageSidebar.fluid.html` |
| `DesiderioStyleguide`         | `main`                     | `Pages/DesiderioStyleguide.fluid.html` |
| `DesiderioBlog`               | `stage`, `main`, `sidebar` | `Pages/DesiderioBlog.fluid.html` |
| `DesiderioExtension`          | `stage`, `sidebar`, `main` | `Pages/DesiderioExtension.fluid.html` |
| `DesiderioNews`               | `stage`, `main`, `sidebar` | `Pages/DesiderioNews.fluid.html` |
| _(fallback)_                  | `stage`, `main`            | `Pages/Default.fluid.html` |

Every content area works with the TYPO3 visual editor. Headers and footers
are static partials, not content areas — the editing surface stays focused
on the content that matters.

`DesiderioBlog`, `DesiderioExtension`, and `DesiderioNews` are shipped by the
hidden `webconsulting/desiderio-shadcnui-templates` site set. The theme base
lists it as an optional dependency, so these shadcn/ui-oriented structures
are available by default while their PAGEVIEW template root remains isolated
at `Resources/Private/ShadcnUi/Templates/`.

### t3g/blog: full shadcn override

The hidden site set `webconsulting/desiderio-blog` (auto-pulled by the theme
base when [`t3g/blog`](https://extensions.typo3.org/extension/blog/) is
installed) replaces the upstream Bootstrap markup with **shadcn-only**
templates: cards, badges, pagination, alerts, and the post / comments /
widget chrome all render through `<d:atom.…>` / `<d:molecule.…>` /
`<d:layout.…>` components.

Every partial under `Resources/Private/Extensions/Blog/Partials/`
declares its inputs with **Fluid 5.3 typed `<f:argument>`** blocks
(`type="T3G\AgencyPack\Blog\Domain\Model\Post"`, `type="iterable"`,
`type="array"`, …). That gives editors and integrators strict typing all
the way through the override chain.

The set is shipped *hidden* from the Site Management picker.

### News: shadcn-styled list, magazine view, and load-more

Drop a `News` plugin onto a `DesiderioBlog` or `DesiderioNews` page and the
list renders as a 3-column shadcn card grid with a `Detail` view that
includes the `Detail/Opengraph` (Open Graph + Twitter card meta) and
`Detail/Shariff` partials.

The list view supports a configurable **"Load more"** mode driven by three
plugin / TypoScript settings:

| Setting | Default | Purpose |
| --- | --- | --- |
| `plugin.tx_news.settings.list.useLoadMore` | `0` (auto on `DesiderioBlog` + `DesiderioNews`) | Switch the list partial from server-paginated to progressive load-more. |
| `plugin.tx_news.settings.list.initialCount` | `6` | How many cards are shown before the button appears. |
| `plugin.tx_news.settings.list.loadMoreCount` | `3` | The "extra number to be loaded" each click. |

The button is rendered with a tiny inline JS asset that hides overflow
items, reveals `loadMoreCount` more on each click, focuses the first newly
revealed item for screen readers, and degrades to "show everything" when
JavaScript is disabled. There is also a `MagazineList.html` template that
features the first article on top with the rest as the load-more secondary
grid.

## Site Packages and Scenario Templates

Normal installs import **one** visible site set. The five visible site packages
wrap the hidden base set, the hidden content-element aggregate, and exactly one
hidden scenario preset with strong defaults.

| Visible set | Character |
| --- | --- |
| `webconsulting/site-package-desiderio-saas` | SaaS Landing |
| `webconsulting/site-package-desiderio-corporate` | Mainline Corporate |
| `webconsulting/site-package-desiderio-portfolio` | Portfolio |
| `webconsulting/site-package-desiderio-editorial` | Blog & Magazine |
| `webconsulting/site-package-desiderio-dashboard` | Dashboard App |

Internally, the hidden `webconsulting/desiderio-preset-*` sets provide the
archetype-specific page templates, CSS file, and setting defaults. Switching
site packages keeps page records and backend layout identifiers stable, but it
does intentionally change the page shell templates and presentation.

The base set also exposes shadcn/create preset support. The committed default
is `b27GcrRo` (radix-rhea, modern neutral); `b6G5977cw`, `b4hb38Fyj`, `b0`, and
`b3IWPgRwnI` ship as alternate light/dark token sets, and the sync generates a
token block for any other preset on demand (see below).

### Switching site packages

There are two different switches:

1. **Desiderio site packages** choose a complete website archetype. Enable or
   replace one of the `webconsulting/site-package-desiderio-*` site sets in
   **Site Management → Sites**.
2. **shadcn/create preset ids** change the design tokens used by buttons,
   cards, borders, charts, typography, radius, and dark mode. Change
   `desiderio.shadcn.preset` in **Site Management → Settings** when the id is
   already supported by committed CSS.

#### Two ways to switch

The current default is `b27GcrRo` (radix-rhea — modern, rounded, neutral). One
command re-skins the whole site — content elements **and** powermail forms,
shapes **and** colours — then rebuild CSS.

**A — a real preset id** from
[ui.shadcn.com/create](https://ui.shadcn.com/create): copy the `b…` code from
the share button / URL (`?preset=b…`).

```bash
php Build/Scripts/sync-shadcn-fluid-primitives.php --preset=b1FSk5ls0
npm run build:css
```

The id decodes into a style + base colour + icon library, so this re-renders
the Fluid primitive contracts, fetches the matching palette from
`https://ui.shadcn.com/r/colors/{baseColor}.json`, writes the
`body[data-shadcn-preset="<id>"]` light + dark blocks into
`Resources/Public/Css/shadcn-theme.css`, and updates `components.json` + Site
Settings. Presets that already ship a committed block are left untouched
(idempotent).

**B — style + base colour directly** (no preset id needed):

```bash
php Build/Scripts/sync-shadcn-fluid-primitives.php --style=radix-nova --baseColor=taupe
npm run build:css
```

- `--style`: `radix-rhea` (modern, current), `radix-lyra` (square/flat),
  `radix-mira` (soft), or `radix-nova` (rounded).
- `--baseColor`: `olive` · `mist` · `taupe` · `neutral` · `stone` · `zinc` ·
  `slate` · `gray` — recolours the active preset's theme tokens.

To make a choice the default for new installs, set `desiderio.shadcn.preset`
(and `desiderio.shadcn.style`) in `Configuration/Sets/Desiderio/settings.yaml`
or **Site Management → Settings** — a new id must also be listed in the matching
`enum` in `settings.definitions.yaml`. Keep `desiderio.layout.radius = preset`
and `desiderio.typography.fontSans = preset` so the preset owns radius and font.
`--check` fails when the configured preset has no colour block. Flush TYPO3
caches and verify light and dark mode.

For shadcn-aware tooling, this repository keeps a valid `components.json`,
scratch TypeScript aliases, and a local registry. Run:

```bash
npm run shadcn:info
npm run registry:build
```

The generated registry JSON is written to `Resources/Public/ShadcnRegistry`.
It packages the Desiderio theme token contract and shared runtime include
assets for other shadcn-capable projects without moving TYPO3 rendering out of
Fluid.

Fluid primitives are updated from the selected shadcn/create preset with:

```bash
npm run shadcn:sync-fluid
```

That command decodes the preset id, updates `components.json` with the matching
registry style, icon library, and base color, synchronizes registry-backed
Fluid primitives from `https://ui.shadcn.com/r/styles/{style}/{name}.json`, and
regenerates the preset's `data-shadcn-preset` color tokens in
`Resources/Public/Css/shadcn-theme.css` (skipping presets that already ship a
committed block). `--check` fails when the configured preset has no color block.
It also updates the default `desiderio.shadcn.iconLibrary` value so new installs
render semantic icon fields with the icon family from the selected preset.
Local semantic primitives, especially Typography, stay token-driven because
shadcn Typography is example documentation rather than a registry component.

Changing only `settings.yaml` or Site Settings to an unsupported id will write
the body attribute, but no visual change will happen because no matching token
block exists.

The shadcn/create left navigation has values that are preset metadata in
Desiderio, not all independent runtime switches. `Style`, `Base Color`,
`Theme`, `Chart Color`, `Heading`, `Font`, and `Radius` are represented through
the committed preset tokens. `Icon Library` is a separate TYPO3 setting that
renders stable semantic icon keys as Lucide, Tabler, or Phosphor SVGs. `Menu`
and `Menu Accent` are documented from the preset, but are not separate TYPO3
switches yet. See
`Documentation/ShadcnUpgrade.md` for the exact support matrix and step-by-step
workflow.

## Settings

Every site configures Desiderio through typed settings exposed in
**Site Management → Settings** (no TypoScript required). The base set
declares the full schema; presets ship different defaults.

| Setting                              | Values                                               |
| ------------------------------------ | ---------------------------------------------------- |
| `desiderio.layout.density`           | `compact`, `comfortable`, `spacious`                 |
| `desiderio.layout.container`         | `narrow`, `wide`, `full`                             |
| `desiderio.layout.radius`            | `preset`, `none`, `sm`, `md`, `lg`, `full`           |
| `desiderio.header.style`             | `solid`, `transparent`, `glass`, `sticky`            |
| `desiderio.header.fixedPosition`     | `true`, `false`                                      |
| `desiderio.footer.style`             | `columns`, `centered`, `minimal`, `mega`             |
| `desiderio.theme.accent`             | `slate`, `rose`, `blue`, `emerald`, `amber`, `violet`, `custom` |
| `desiderio.theme.darkModeDefault`    | `light`, `dark`, `system`                            |
| `desiderio.theme.darkModeToggle`     | `true`, `false`                                      |
| `desiderio.shadcn.preset`            | `b4hb38Fyj`, `b0`, `b3IWPgRwnI`, `b6G5977cw`, `custom` |
| `desiderio.shadcn.style`             | `radix-nova`, `radix-mira`, `radix-lyra`, `custom`   |
| `desiderio.shadcn.iconLibrary`       | `lucide`, `tabler`, `phosphor`                        |
| `desiderio.typography.fontSans`      | `preset`, `inter`, `geist`, `system`, `serif`        |
| `desiderio.styleguide.enabled`       | `true`, `false`                                      |

Settings are rendered into `<body data-*>` attributes so hand-written CSS
can react to them without runtime JavaScript.

Settings are defined in:

- `Configuration/Sets/Desiderio/settings.definitions.yaml` — selectable values
  in TYPO3 Site Settings.
- `Configuration/Sets/Desiderio/settings.yaml` — default values shipped by the
  base set.
- `Configuration/Sets/Desiderio/setup.typoscript` — renders the values as
  `<body data-*>` attributes.
- `Resources/Public/Css/shadcn-theme.css` — committed token blocks for each
  supported shadcn/create preset id.

## Content elements

255 content blocks, organised in 10 wizard categories. The templates are
token-driven Fluid templates; 250 of the 255 shipped frontend templates use the
`<d:…>` Fluid component namespace directly, while the small utility/chart
exceptions still share the same CSS variables and runtime assets. See
`ContentBlocks/ContentElements/` for the full list.

The full catalog is wired through the hidden
`webconsulting/desiderio-content-elements` site set. This set depends on
`webconsulting/desiderio` and lists the individual `desiderio/*` block sets as
optional dependencies. The visible `webconsulting/site-package-desiderio-*`
sets depend on it, so a normal site import gets the whole editor catalog without
selecting block sets one by one.

Classic TYPO3 Fluid Styled Content elements are overridden from
`Resources/Private/FluidStyledContent/` and use the same shadcn preset tokens,
Fluid 5 components, and Tailwind source build as the Content Blocks catalog.

### Collection fields and generated tables

Desiderio keeps `prefixFields: false` at the content-element root so editor
field names stay readable, but every top-level Content Blocks `Collection`
field uses `prefixField: true`. That gives each `tt_content` collection count
its own TCA column, even when many content elements use identifiers such as
`items`, `links`, or `features`.

The generated default is one child table per collection. This creates more
tables, but it keeps schemas, labels, migrations, fixtures, and styleguide seed
logic local to the content element that owns them. Reusing a collection table is
allowed only as an explicit modeling decision, not automatically because field
identifiers match.

Reuse a child table only when the child rows are intentionally the same model
across all parents, for example a simple `label` + `link` list or repeated
`label` + `value` metric rows. Avoid reuse when schemas can evolve
independently, when editor labels or validation differ, or when two collection
fields on the same parent would point at the same child table without a
separate match field such as Content Blocks `shareAcrossFields`.

The practical benefit is fewer tables and less schema noise. Do not expect a
large physical database-size reduction unless table overhead in the database
engine is the actual bottleneck.

Nested collections are supported when each level declares its own stable
`table`. The styleguide seed command walks those structures recursively and
writes child rows below the current parent row.

### Media rendering in Content Blocks

Image and media fields must stay on TYPO3 Fluid ViewHelpers. Use `<f:image>`
for rendered images and `f:uri.image()` when a JavaScript data attribute needs a
processed image URL. Do not replace a TYPO3 file reference with a literal
`<img>` tag in `templates/frontend.html`.

Custom attributes on `<f:image>` should use Fluid's structured attribute
arguments, especially for `data-*` values:

```html
<f:image image="{fileReference}" maxWidth="1440" alt="{item.title}"
         class="gallery__featured-image"
         data="{d-gallery-main: 'true'}"/>
```

This keeps Visual Editor image decoration active and avoids Fluid trying to
convert `TYPO3\CMS\Core\Resource\FileReference` objects to strings while
compiling non-standard ViewHelper attributes.

## Integration templates

The theme base also references hidden optional integration sets for common
extensions:

| Set | Extension | Template paths |
| --- | --- | --- |
| `webconsulting/desiderio-solr` | `apache-solr-for-typo3/solr` | `Resources/Private/Solr/` |
| `webconsulting/desiderio-news` | `georgringer/news` | `Resources/Private/Extensions/News/` |
| `webconsulting/desiderio-blog` | `t3g/blog` | `Resources/Private/Extensions/Blog/` |

The Solr, News, and Blog sets are hidden optional dependencies of
`webconsulting/desiderio`, so sites using the theme base get the Desiderio
template paths when the matching third-party extension is installed.
The Solr override follows the shadcn/create surface contract: a tokenized
create-style rail for facets and recent searches, a command-palette search
input, `data-slot` markers for input/button/card/badge semantics, and radius,
font, colour, sidebar, and focus states inherited from the active preset.

## Fluid 5 components

- **Atoms (16):** AspectRatio, Avatar, Badge, Button, Icon, Image, Input,
  Label, Link, Progress, ScrollArea, Select, Separator, Skeleton, Textarea,
  Typography
- **Molecules (17):** Accordion, AccordionItem, Alert, AlertTitle,
  AlertDescription, Card, CardHeader, CardContent, CardFooter, Table,
  TableHeader, TableRow, TableCell, Tabs, TabsList, TabsTrigger, TabsContent
- **Layout (4):** Container, Grid, Section, Stack

Available in any Fluid template via
`xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"`.

## Frontend Build

Fluid remains server-side rendered, but shadcn/Tailwind utility classes are
compiled into a committed CSS file:

```bash
npm install
npm run build:css
```

The frontend runtime is plain CSS plus Alpine/vanilla JS for dark mode,
accordion, tabs, counters, and component interactions.

## Testing & quality

```bash
composer install
npm install
npm run build:css

# Unit tests + PHPStan max + content element audit in one command
Build/Scripts/runTests.sh

# Or à la carte
Build/Scripts/runTests.sh phpunit
Build/Scripts/runTests.sh phpstan
Build/Scripts/runTests.sh audit
```

GitHub Actions runs the same matrix on every push and pull request:

- **PHPStan** at `level: max` with `phpstan/extension-installer`,
  `saschaegerer/phpstan-typo3`, `phpstan/phpstan-strict-rules`, and
  `phpstan/phpstan-phpunit`. The legacy seed-command type drift is
  documented in `phpstan-baseline.neon` as a ratchet target.
- **PHPUnit** ^11.5 across PHP 8.3 + 8.4 against TYPO3 ^14.3.
- **Content element audit** (`scripts/audit-content-elements.php`) gating
  the strict categories — `template_undeclared_field`,
  `hardcoded_inline_style`, `hardcoded_color`, etc. — at zero.
- **`composer audit`** with `abandoned: fail` and **`composer validate`**.

## Cleanup-loop reports

Every release cuts a fresh round of agentic-skill audits and stores them
under `Documentation/Reports/`:

- `typo3-conformance.md` — code conventions, v14 deprecations, XLIFF
  hygiene.
- `typo3-security.md` — TYPO3-specific XSS / CSP / iframe surface.
- `typo3-workspaces.md` — workspace overlay correctness, seed-command
  guards.
- `typo3-testing.md` — coverage estimate, CI parity.
- `typo3-docs.md` — documentation freshness vs. shipped behaviour.
- `security-audit.md` — generic OWASP / dependency / supply chain.

Use these as the entry point when you want to know what the codebase
already expects to handle.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the issue, branch, and PR
workflow. The short form: open a branch off `main`, run
`Build/Scripts/runTests.sh`, attach the relevant Documentation/Reports/
findings to your PR.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## License

[GPL-2.0-or-later](LICENSE).
