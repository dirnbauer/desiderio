# Desiderio v2 — Specification

**Status:** Draft for big-bang rebuild
**Target TYPO3:** `^14.3`
**Target PHP:** `^8.3`
**Philosophy:** One extension. No backward compatibility. No legacy cruft. Start clean.

---

## 1. Goal

A single TYPO3 v14 extension that ships:

1. A **component library** (Fluid 5 atoms / molecules / layouts)
2. A **content element library** (250 Content Blocks composing those components)
3. A **theme layer** (backend layouts, page templates, partials, dark mode, styleguide)
4. **Settings** per page template (density, container width, header style, footer style, accent, radius, font pair)
5. A catalog of **visual variants** ("skins") applied via CSS layers

`shadcn2fluid-templates` is **absorbed and deleted**. `desiderio` becomes self-contained.

---

## 2. Extension Identity

| Key | Value |
| --- | --- |
| Composer package | `webconsulting/desiderio` |
| Extension key | `desiderio` |
| PHP namespace | `Webconsulting\Desiderio\` |
| Fluid prefix | `<d:…>` (global), `<desiderio:…>` (explicit) |
| Version | `2.0.0` |
| State | `beta` |
| Dependencies | `friendsoftypo3/content-blocks ^2.2`, `praetorius/vite-asset-collector ^1.0` |

The old `s2f:` prefix is **gone**. No alias. No deprecation. New installs only.

---

## 3. Directory Layout

```
desiderio/
├── composer.json
├── ext_emconf.php
├── ext_localconf.php                  # comment-only, assets live in site set
├── README.md
├── SPECIFICATION.md                   # this file
├── LICENSE
├── phpstan.neon.dist
├── phpunit.xml.dist
├── Build/
│   └── phpstan-baseline.neon
├── scripts/
│   ├── fix-contentblocks.php          # codemod / linter
│   └── generate-styleguide.php        # regenerate styleguide page
├── Classes/
│   ├── Components/
│   │   └── ComponentCollection.php    # Fluid 5 component registration
│   ├── Data/
│   │   └── StyleguideContentGroups.php
│   └── ViewHelpers/
│       ├── StyleguideGroupsViewHelper.php
│       ├── FixtureJsonViewHelper.php
│       └── RecordHasFieldViewHelper.php
├── Configuration/
│   ├── Services.yaml                  # ViewHelper DI
│   ├── Icons.php                      # icon registry
│   ├── page.tsconfig                  # backend layout selector
│   ├── BackendLayouts/                # 4 backend layouts (see §5)
│   │   ├── DesiderioStartpage.tsconfig
│   │   ├── DesiderioContentpage.tsconfig
│   │   ├── DesiderioContentpageSidebar.tsconfig
│   │   └── DesiderioStyleguide.tsconfig
│   └── Sets/
│       ├── Desiderio/                  # base site set (components + theme)
│       │   ├── config.yaml
│       │   ├── settings.definitions.yaml   # NEW — see §7
│       │   ├── settings.yaml
│       │   ├── setup.typoscript
│       │   ├── page.tsconfig
│       │   └── ...
│       ├── DesiderioSkin1/             # SaaS Landing
│       ├── DesiderioSkin2/             # Mainline Corporate
│       ├── DesiderioSkin3/             # Portfolio
│       ├── DesiderioSkin4/             # Blog & Magazine
│       └── DesiderioSkin5/             # Dashboard App
├── ContentBlocks/
│   └── ContentElements/                # 250 content elements (see §6)
│       ├── hero-centered/
│       ├── feature-bento/
│       └── ...
├── Resources/
│   ├── Private/
│   │   ├── Components/                 # Fluid 5 components
│   │   │   ├── Atom/      (16)
│   │   │   ├── Molecule/  (17)
│   │   │   └── Layout/    (4)
│   │   ├── Templates/
│   │   │   └── Pages/                  # 5 page templates
│   │   ├── Layouts/                    # shared page shell
│   │   ├── Partials/                   # Header, Footer, DarkModeToggle, etc.
│   │   └── Language/                   # locallang*.xlf
│   └── Public/
│       ├── Css/
│       │   ├── desiderio.css           # base + tokens + reset
│       │   ├── components.css          # BEM styles for 37 components
│       │   ├── skin1.css … skin5.css   # variant layers
│       │   └── theme.css               # shadcn OKLCH tokens
│       ├── Js/
│       │   └── desiderio.js            # dark mode, accordion, tabs, counters
│       ├── Icons/                      # SVGs for backend wizard
│       └── Vite/                       # vite-asset-collector entrypoints
└── Tests/
    └── Unit/
        ├── ExtensionMetadataTest.php
        ├── ContentBlockStructureTest.php
        ├── ComponentStructureTest.php   # NEW
        └── ViewHelpers/                 # NEW — unit tests for 3 ViewHelpers
```

> The word **"template"** in old docs is replaced by **"skin"** throughout the new codebase to stop the clash with "page template" (Fluid). A skin is a CSS layer. A page template is a Fluid file.

---

## 4. Three-Layer Architecture

```
┌──────────────────────────────────────────────────────────┐
│ Layer 3 — THEME                                          │
│ Page templates · Backend layouts · Header/Footer · Skins │
└──────────────────────────────────────────────────────────┘
                          ▲ renders via PAGEVIEW
┌──────────────────────────────────────────────────────────┐
│ Layer 2 — CONTENT ELEMENTS (Content Blocks)              │
│ 250 editor-facing elements in 10 wizard groups           │
└──────────────────────────────────────────────────────────┘
                          ▲ composes via <d:…>
┌──────────────────────────────────────────────────────────┐
│ Layer 1 — COMPONENTS (Fluid 5)                           │
│ 16 atoms · 17 molecules · 4 layouts (typed <f:argument>) │
└──────────────────────────────────────────────────────────┘
```

A content element **never** writes raw HTML. It composes components. A page template **never** writes card/hero markup. It renders content areas that render content elements.

---

## 5. Backend Layouts & Page Templates

Four backend layouts, five page templates. Default falls through to Contentpage.

| Backend layout | Content areas | Page template | Use case |
| --- | --- | --- | --- |
| `DesiderioStartpage` | `stage`, `main` | `Startpage.html` | Home / landing |
| `DesiderioContentpage` | `stage`, `main` | `Contentpage.html` | Standard subpage |
| `DesiderioContentpageSidebar` | `stage`, `main`, `sidebar` | `Sidebar.html` | Blog, docs |
| `DesiderioStyleguide` | `main` | `Styleguide.html` | Component browser |
| _(fallback)_ | `stage`, `main` | `Default.html` | Unset pages |

**Footer** is always a static partial (`Partials/Footer.html`). No footer content area.
**Header** is always a static partial (`Partials/Header.html`) with a `DarkModeToggle` partial.

---

## 6. Content Elements

- **255** elements, grouped into **10 wizard groups** (see `Classes/Data/StyleguideContentGroups.php`).
- Each lives at `ContentBlocks/ContentElements/<slug>/` with the standard Content Blocks layout:
  - `config.yaml` · `src/Frontend.html` · `assets/icon.svg` · `language/labels.xlf` · `fixtures/default.json`
- Naming: lowercase kebab (`hero-centered`, not `HeroCentered`).
- `ContentBlockStructureTest` asserts:
  - All 250 slugs exist · each has all required files · YAML parses · wizard group is one of the 10 · Fluid parses · fixture JSON parses.

_List frozen in `SPECIFICATION-content-elements.md` (to be generated from current shadcn2fluid inventory)._

---

## 7. Settings (NEW)

Each skin is **more than CSS**. A set of typed settings is exposed via `Configuration/Sets/*/settings.definitions.yaml` and consumable in Fluid via `{settings.desiderio.*}`. Editors change them in the Site Management UI — no code changes.

```yaml
# Configuration/Sets/Desiderio/settings.definitions.yaml  (excerpt)
settings:
  desiderio.density:
    type: string
    default: comfortable
    enum: [compact, comfortable, spacious]
    label: "Vertical density"

  desiderio.container:
    type: string
    default: wide
    enum: [narrow, wide, full]
    label: "Container width"

  desiderio.header.style:
    type: string
    default: solid
    enum: [solid, transparent, glass, sticky]
    label: "Header style"

  desiderio.footer.style:
    type: string
    default: columns
    enum: [columns, centered, minimal, mega]
    label: "Footer style"

  desiderio.radius:
    type: string
    default: md
    enum: [none, sm, md, lg, full]
    label: "Border radius"

  desiderio.accent:
    type: string
    default: slate
    enum: [slate, rose, blue, emerald, amber, violet, custom]
    label: "Accent palette"

  desiderio.font.sans:
    type: string
    default: inter
    enum: [inter, geist, system, serif]
    label: "Sans font"

  desiderio.darkModeDefault:
    type: string
    default: system
    enum: [light, dark, system]
    label: "Default color scheme"

  desiderio.darkModeToggle:
    type: bool
    default: true
    label: "Show dark-mode toggle"

  desiderio.showStyleguide:
    type: bool
    default: false
    label: "Render Styleguide template"
```

Skins override defaults (e.g. `DesiderioSkin4` sets `desiderio.font.sans: serif`, `desiderio.container: narrow`). TypoScript and Fluid read these values through `{settings.desiderio.*}`. CSS reads them via a `<style>` block injected once in `<head>` that maps settings → CSS custom properties:

```html
<style>
  :root {
    --d-radius: var(--d-radius-{settings.desiderio.radius});
    --d-container: var(--d-container-{settings.desiderio.container});
    --d-accent: var(--d-accent-{settings.desiderio.accent});
  }
</style>
```

No build step. No SCSS. Pure CSS custom properties.

---

## 8. Skins (formerly "templates")

Five site sets extend the base. Each ships **one CSS file + settings overrides**:

| Set | Slug | Character | Overrides |
| --- | --- | --- | --- |
| Skin 1 | `desiderio-skin1` | SaaS Landing | glass header, spacious density, `blue` accent |
| Skin 2 | `desiderio-skin2` | Mainline Corporate | solid header, centered footer, `slate`, minimal radius |
| Skin 3 | `desiderio-skin3` | Portfolio | transparent header overlay, `rose`, `full` radius |
| Skin 4 | `desiderio-skin4` | Blog & Magazine | serif font, `narrow` container, columns footer |
| Skin 5 | `desiderio-skin5` | Dashboard App | sticky header, `compact` density, monospace accents |

A skin **cannot** change markup, backend layouts, or component structure. It only changes:
1. CSS (one `skin*.css` file loaded after `desiderio.css`)
2. Setting defaults (in `settings.yaml`)

Switching skins is always safe — never breaks content.

---

## 9. Frontend Assets

- **No npm build.** No Vite bundling of Fluid output.
- `vite-asset-collector` is used only to register entrypoints for dev ergonomics (HMR for CSS in DDEV).
- All CSS is hand-authored in `Resources/Public/Css/`.
- JS is vanilla ES module (`desiderio.js`). No React, no Alpine, no jQuery.
- Dark mode: `data-theme="dark"` on `<html>`, persisted in `localStorage`, respects `prefers-color-scheme` on first visit.

---

## 10. Fluid 5 Components

16 atoms, 17 molecules, 4 layouts (= 37 components). Each component uses typed `<f:argument>` and `<f:slot>`.

```
Atom:      AspectRatio Avatar Badge Button Icon Image Input Label
           Link Progress ScrollArea Select Separator Skeleton
           Textarea Typography
Molecule:  Accordion AccordionItem Alert AlertTitle AlertDescription
           Card CardHeader CardContent CardFooter
           Table TableHeader TableRow TableCell
           Tabs TabsList TabsTrigger TabsContent
Layout:    Container Grid Section Stack
```

Registered in `Classes/Components/ComponentCollection.php`. Accessible in any Fluid template via `xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"`.

---

## 11. ViewHelpers

| ViewHelper | Purpose |
| --- | --- |
| `<d:styleguideGroups />` | Returns the 10 wizard groups + content element slugs, for the Styleguide page |
| `<d:fixtureJson element="hero-centered" />` | Loads `ContentBlocks/ContentElements/<slug>/fixtures/default.json` |
| `<d:recordHas field="media" record="{data}" />` | Safe check for record field presence, used by visual editor rendering |

All three have dedicated unit tests.

---

## 12. Styleguide

A self-rendering page template. Iterates over the 10 groups × 25 elements, renders each with its fixture. Used for:

- Design review
- Visual regression
- Onboarding ("what does this site have?")

Gated by `settings.desiderio.showStyleguide`. Off by default in production.

---

## 13. Testing

- PHPUnit `^11.5` + TYPO3 Testing Framework `^9.0`
- Unit tests:
  - `ExtensionMetadataTest` — composer.json, ext_emconf, icon registry agree
  - `ContentBlockStructureTest` — all 250 blocks valid
  - `ComponentStructureTest` — all 37 components have `<f:argument>` definitions
  - `ViewHelpers/*Test` — 3 ViewHelpers covered
- Static analysis: PHPStan level 8 with small baseline
- CI: GitHub Actions matrix {PHP 8.3, 8.4} × {TYPO3 14.3}

---

## 14. Out of Scope

- No React / Alpine / Livewire
- No bundler (no Vite bundle, no Webpack)
- No shadcn CLI dependency at runtime (it may be used manually to regenerate theme tokens into `theme.css`)
- No fallback for PHP < 8.3 or TYPO3 < 14.3
- No migration path from `s2f:` namespace — fresh installs only

---

## 15. Acceptance Criteria

The rebuild is done when:

1. `composer require webconsulting/desiderio` pulls **one** extension.
2. `shadcn2fluid-templates` no longer exists on disk or in Packagist mirrors used by our projects.
3. A fresh TYPO3 14.3 site + Desiderio base set renders a Startpage with a hero, features, and footer — zero manual TypoScript.
4. Switching to any of Skin 1-5 via Site Management changes only visuals.
5. Settings UI in Site Management lists all keys from §7.
6. `composer test` passes (phpstan + phpunit).
7. Styleguide renders all 250 elements without errors.
8. Dark mode toggle works on all 5 skins.
9. README.md describes the single extension with no reference to `shadcn2fluid_templates`.
