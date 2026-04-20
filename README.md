# Desiderio

A self-contained TYPO3 v14 theme extension that bundles a
[shadcn/ui](https://ui.shadcn.com)-inspired Fluid 5 component library, **255
content elements**, backend layouts, page templates, and five swappable
visual skins — without a frontend build step.

> Desiderio 2.0 replaces both `webconsulting/desiderio 1.x` and
> `webconsulting/shadcn2fluid-templates 3.x`. No backward compatibility; clean
> installs only. See [SPECIFICATION.md](SPECIFICATION.md) and
> [MIGRATION-PLAN.md](MIGRATION-PLAN.md) for the rewrite rationale.

## Three-layer architecture

```
┌──────────────────────────────────────────────────────────┐
│ Layer 3 — THEME                                          │
│ Page templates · Backend layouts · Header/Footer · Skins │
└──────────────────────────────────────────────────────────┘
                          ▲ renders via PAGEVIEW
┌──────────────────────────────────────────────────────────┐
│ Layer 2 — CONTENT ELEMENTS (Content Blocks)              │
│ 255 editor-facing elements in 10 wizard groups           │
└──────────────────────────────────────────────────────────┘
                          ▲ composes via <d:…>
┌──────────────────────────────────────────────────────────┐
│ Layer 1 — COMPONENTS (Fluid 5)                           │
│ 16 atoms · 17 molecules · 4 layouts (typed <f:argument>) │
└──────────────────────────────────────────────────────────┘
```

## Installation

Requires TYPO3 14.3+ with PHP 8.3+:

```bash
composer require webconsulting/desiderio
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

Then enable the base site set plus one of the five skins:

1. Site Management → Sites → edit the target site
2. Add `Desiderio — Base` (`webconsulting/desiderio`)
3. Add one of the five skin sets (see below)
4. Save and flush caches

## Page templates

| Backend layout               | Content areas             | Page template        |
| ---------------------------- | ------------------------- | -------------------- |
| `DesiderioStartpage`         | `stage`, `main`           | `Startpage.html`     |
| `DesiderioContentpage`       | `stage`, `main`           | `Contentpage.html`   |
| `DesiderioContentpageSidebar`| `stage`, `main`, `sidebar`| `Sidebar.html`       |
| `DesiderioStyleguide`        | `main`                    | `Styleguide.html`    |
| _(fallback)_                 | `stage`, `main`           | `Default.html`       |

Every content area works with the TYPO3 visual editor. Headers and footers
are static partials, not content areas — the editing surface stays focused
on the content that matters.

## Skins

Five site sets that depend on the base set. Each ships a single CSS file and
overrides base-set setting defaults. Switching skins **never** changes your
content, markup, or backend layouts — only the presentation.

| Set                                    | Character             |
| -------------------------------------- | --------------------- |
| `webconsulting/desiderio-skin1`        | SaaS Landing          |
| `webconsulting/desiderio-skin2`        | Mainline Corporate    |
| `webconsulting/desiderio-skin3`        | Portfolio             |
| `webconsulting/desiderio-skin4`        | Blog & Magazine       |
| `webconsulting/desiderio-skin5`        | Dashboard App         |

## Settings

Every site configures Desiderio through typed settings exposed in
**Site Management → Settings** (no TypoScript required). The base set
declares the full schema; skins ship different defaults.

| Setting                              | Values                                               |
| ------------------------------------ | ---------------------------------------------------- |
| `desiderio.layout.density`           | `compact`, `comfortable`, `spacious`                 |
| `desiderio.layout.container`         | `narrow`, `wide`, `full`                             |
| `desiderio.layout.radius`            | `none`, `sm`, `md`, `lg`, `full`                     |
| `desiderio.header.style`             | `solid`, `transparent`, `glass`, `sticky`            |
| `desiderio.header.fixedPosition`     | `true`, `false`                                      |
| `desiderio.footer.style`             | `columns`, `centered`, `minimal`, `mega`             |
| `desiderio.theme.accent`             | `slate`, `rose`, `blue`, `emerald`, `amber`, `violet`, `custom` |
| `desiderio.theme.darkModeDefault`    | `light`, `dark`, `system`                            |
| `desiderio.theme.darkModeToggle`     | `true`, `false`                                      |
| `desiderio.typography.fontSans`      | `inter`, `geist`, `system`, `serif`                  |
| `desiderio.styleguide.enabled`       | `true`, `false`                                      |

Settings are rendered into `<body data-*>` attributes so hand-written CSS
can react to them without runtime JavaScript.

## Content elements

255 content blocks, organised in 10 wizard categories. Every block composes
Fluid 5 components — no block writes raw HTML. See
`ContentBlocks/ContentElements/` for the full list.

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

## Frontend build

**None.** Pure Fluid 5 server-side rendering. Pure CSS. Vanilla JS for
dark-mode toggle, accordion, tabs, counters. No bundler, no npm build step.

## Testing

```bash
composer install
vendor/bin/phpunit --testsuite=Unit   # 13 tests, 3444 assertions
vendor/bin/phpstan analyse            # level 8, no errors
```

## License

GPL-2.0-or-later.
