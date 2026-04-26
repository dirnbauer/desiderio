# Desiderio

A self-contained TYPO3 v14 theme extension that bundles a
[shadcn/ui](https://ui.shadcn.com)-inspired Fluid 5 component library, **255
content elements**, backend layouts, page templates, and five swappable
visual presets with a committed Tailwind v4/shadcn CSS build.

> Desiderio 2.0 replaces both `webconsulting/desiderio 1.x` and
> `webconsulting/shadcn2fluid-templates 3.x`. No backward compatibility; clean
> installs only. See [SPECIFICATION.md](SPECIFICATION.md) and
> [MIGRATION-PLAN.md](MIGRATION-PLAN.md) for the rewrite rationale.

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

Then enable the base site set plus one of the five presets:

1. Site Management → Sites → edit the target site
2. Add `Desiderio Base` (`webconsulting/desiderio`)
3. Add one of the five preset sets (see below)
4. Save and flush caches

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

## Presets

Five site sets that depend on the base set. Each ships a single CSS file and
overrides base-set setting defaults. Switching presets **never** changes your
content, markup, or backend layouts — only the presentation.

| Set                                    | Character             |
| -------------------------------------- | --------------------- |
| `webconsulting/desiderio-preset-saas`        | SaaS Landing          |
| `webconsulting/desiderio-preset-corporate`        | Mainline Corporate    |
| `webconsulting/desiderio-preset-portfolio`        | Portfolio             |
| `webconsulting/desiderio-preset-editorial`        | Blog & Magazine       |
| `webconsulting/desiderio-preset-dashboard`        | Dashboard App         |

The base set also exposes shadcn/create preset support. The committed theme
CSS currently supports `b0` as the default and `b3IWPgRwnI` as an alternate
light/dark token set.

## Settings

Every site configures Desiderio through typed settings exposed in
**Site Management → Settings** (no TypoScript required). The base set
declares the full schema; presets ship different defaults.

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
| `desiderio.shadcn.preset`            | `b0`, `b3IWPgRwnI`, `custom`                         |
| `desiderio.shadcn.style`             | `radix-nova`, `radix-mira`, `custom`                 |
| `desiderio.typography.fontSans`      | `preset`, `inter`, `geist`, `system`, `serif`        |
| `desiderio.styleguide.enabled`       | `true`, `false`                                      |

Settings are rendered into `<body data-*>` attributes so hand-written CSS
can react to them without runtime JavaScript.

## Content elements

255 content blocks, organised in 10 wizard categories. Every block composes
Fluid 5 components — no block writes raw HTML. See
`ContentBlocks/ContentElements/` for the full list.

Classic TYPO3 Fluid Styled Content elements are overridden from
`Resources/Private/FluidStyledContent/` and use the same shadcn preset tokens,
Fluid 5 components, and Tailwind source build as the Content Blocks catalog.

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

## Testing

```bash
composer install
npm install
npm run build:css
vendor/bin/phpunit --testsuite=Unit
vendor/bin/phpstan analyse            # level 8, no errors
```

## License

GPL-2.0-or-later.
