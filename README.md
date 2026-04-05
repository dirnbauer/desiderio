# Desiderio

A modern TYPO3 v14 theme extension that brings [shadcn/ui](https://ui.shadcn.com) design quality to TYPO3 — powered by 250+ content elements from [shadcn2fluid-templates](https://github.com/dirnbauer/shadcn2fluid-templates) and inspired by the architecture of TYPO3's official [Camino](https://docs.typo3.org/) theme.

## What is Desiderio?

Desiderio is a **theme layer** for TYPO3 14 that gives you production-ready page templates with the visual editor, dark mode, and 5 swappable design styles — all without writing a single line of PHP.

It combines two things:

1. **The rendering architecture of Camino** — TYPO3's official theme — using `PAGEVIEW`, backend layouts with named content areas, and Fluid 5 typed parameters. This means full support for the TYPO3 visual editor out of the box.

2. **The design system of shadcn/ui** — 250+ accessible, beautifully crafted content elements provided by the [shadcn2fluid-templates](https://github.com/dirnbauer/shadcn2fluid-templates) extension. Buttons, cards, accordions, heroes, pricing tables, testimonials, navigation bars, footers — everything you need to build real websites.

## What does it do?

### Page Templates

Desiderio provides **5 page templates**, each with visual-editor content areas:

| Template | Content Areas | Use Case |
|----------|--------------|----------|
| **Startpage** | Stage + Content | Home pages with a hero section and full-width content |
| **Contentpage** | Stage + Content | Subpages — articles, about pages, service pages |
| **Contentpage with Sidebar** | Stage + Content + Sidebar | Pages with aside content — blog posts, docs, filtered listings |
| **Styleguide** | Content | Auto-generated overview of all 250 content elements, grouped by category |
| **Default** | Stage + Content | Fallback for pages without a specific layout |

Every content area works with the TYPO3 visual editor — drag, drop, and arrange your content elements directly in the page preview.

**No footer content areas.** The footer is a static partial. This keeps the backend clean and the editing experience focused on what matters — your content.

### Dark Mode

A sun/moon toggle button in the header switches between light and dark themes instantly. The preference is saved in `localStorage`, so visitors get their chosen mode on every visit. The toggle uses smooth CSS transitions and works with the full shadcn/ui color system.

### 250 Content Elements

Through its dependency on [shadcn2fluid-templates](https://github.com/dirnbauer/shadcn2fluid-templates), Desiderio gives you access to **250 content elements** across 10 categories:

| Category | Examples | Count |
|----------|----------|-------|
| **Hero** | Centered, Split, Gradient, Full Image, SaaS, Startup, With Form | 25 |
| **Features** | Grid, Alternating, Bento, Carousel, Comparison, Tabs, Showcase | 25 |
| **Content** | Accordion, Carousel, Columns, FAQ, Gallery, Rich Text, Timeline | 25 |
| **Conversion** | CTA variants, Newsletter, Contact, Login, Search, Waitlist | 25 |
| **Pricing** | Cards, Comparison, Feature Matrix, Slider, Toggle, Product Grid | 25 |
| **Social Proof** | Testimonials, Reviews, Logos, Case Studies, Stats, Awards | 25 |
| **Team** | Grid, Carousel, Founders, Careers, Job Board, Office Locations | 25 |
| **Navigation** | Navbar variants, Breadcrumb, Sidebar, Pagination, Mobile Menu | 25 |
| **Data** | Tables, Charts, Cards, Changelog, KPI Dashboard, Roadmap | 25 |
| **Footer** | Columns, Mega, Newsletter, Social, Sitemap, Legal Links | 25 |

All content elements are pure Fluid 5 components — no JavaScript frameworks, no build step, no npm. They render server-side and work with TYPO3's caching out of the box.

### The Styleguide

The Styleguide page template automatically displays all 250 content elements grouped by their wizard category. Use it during development to browse available elements, check naming conventions, and verify your theme's styling covers every component.

## 5 Template Designs

Desiderio separates **infrastructure** from **visual design**. The base provides TYPO3 configuration and page templates. Each template design is a separate TYPO3 site set that adds its own CSS layer on top:

| Site Set | Design | Character |
|----------|--------|-----------|
| `webconsulting/desiderio` | **Base** | Neutral foundation — header, footer, content frames, dark mode toggle |
| `webconsulting/desiderio-template1` | **SaaS Landing** | Glassmorphic header, spacious vertical rhythm, modern marketing feel |
| `webconsulting/desiderio-template2` | **Mainline Corporate** | Minimal and professional, generous whitespace, centered footer |
| `webconsulting/desiderio-template3` | **Portfolio** | Transparent header overlay, bold typography, accent-tinted backgrounds |
| `webconsulting/desiderio-template4` | **Blog & Magazine** | Serif typography, reading-width constraint, editorial border accents |
| `webconsulting/desiderio-template5` | **Dashboard App** | Compact toolbar header, monospace accents, card-panel content frames |

**Switching templates is a one-click operation** — just change which site set is active in your site configuration. The page structure stays the same; only the visual presentation changes.

### What “template” means here

In this project, a **template design** is **not** a second copy of the Fluid page templates or backend layouts. Those live once in the base site set (`webconsulting/desiderio`): `PAGEVIEW` wiring, page templates, header/footer partials, TypoScript, and shared `desiderio.css` / `desiderio.js`.

Each of the five **look** packages (`webconsulting/desiderio-template1` … `template5`) is a small TYPO3 **site set** that **only** depends on the base set and loads **one extra stylesheet** — `Resources/Public/Css/template1.css` through `template5.css` — on top of the base assets. That file overrides the same shared classes (header, main, content frames, sidebar layout) so each design reads as a different visual style while the HTML structure and content areas stay identical.

So: **one infrastructure, five CSS personalities.** If you need a wholly different markup or content-element tree per brand, that would be a separate extension or custom Fluid — not what these five sets are for.

## Why is this cool?

- **Visual editor first.** Every page template uses `PAGEVIEW` with named content areas, so the TYPO3 visual editor works perfectly. No workarounds, no hacks.

- **250 content elements, zero build step.** Pure Fluid 5 server-side components. No React, no npm, no Webpack. Just Fluid templates that TYPO3 renders and caches natively.

- **Dark mode that actually works.** Not a gimmick — a proper implementation with CSS custom properties, the full shadcn/ui color system, and `localStorage` persistence.

- **Swap designs without touching content.** The 5 template site sets are pure CSS layers. Switch from "SaaS Landing" to "Blog & Magazine" and your content, your layouts, your structure — everything stays intact.

- **shadcn/ui design quality in TYPO3.** The same design system trusted by thousands of React developers, now available as native TYPO3 content elements. Accessible, consistent, beautiful.

- **Camino's proven architecture.** TYPO3's official theme team designed a rendering pipeline that works. Desiderio builds on it — same `PAGEVIEW`, same data processors, same content area pattern.

- **Themeable with shadcn/ui.** Generate a custom color scheme at [ui.shadcn.com/create](https://ui.shadcn.com/create), export the CSS, and drop it into your site configuration. Every content element automatically picks up your colors.

## Requirements

- TYPO3 v14.3+
- PHP 8.2+
- [shadcn2fluid-templates](https://github.com/dirnbauer/shadcn2fluid-templates) v3.0+ (installed automatically as a Composer dependency)

## Installation

```bash
composer require webconsulting/desiderio
```

Then in your TYPO3 site configuration, add the site sets you want:

1. **Required:** `webconsulting/desiderio` (base theme)
2. **Pick one template:** e.g. `webconsulting/desiderio-template1` (SaaS Landing)

That's it. Your site now has page templates, 250 content elements, dark mode, and a styleguide.

### Custom Theme Colors

1. Go to [ui.shadcn.com/create](https://ui.shadcn.com/create)
2. Customize your colors, radius, fonts
3. Copy the generated CSS (`:root` and `.dark` blocks)
4. Save it as a CSS file in your site package
5. Set the path in your site configuration: `shadcn2fluid.themeCss = EXT:your_site/Resources/Public/Css/theme.css`

## Architecture

```
desiderio/
├── Configuration/
│   ├── BackendLayouts/           # 4 backend layouts (no footer areas)
│   └── Sets/
│       ├── Desiderio/            # Base site set (PAGEVIEW, TypoScript, TSconfig)
│       ├── DesiderioTemplate1/   # SaaS Landing CSS
│       ├── DesiderioTemplate2/   # Mainline Corporate CSS
│       ├── DesiderioTemplate3/   # Portfolio CSS
│       ├── DesiderioTemplate4/   # Blog & Magazine CSS
│       └── DesiderioTemplate5/   # Dashboard App CSS
├── Resources/
│   ├── Private/Templates/        # Fluid 5 page templates, layouts, partials
│   └── Public/Css/               # Base CSS + 5 template CSS files
├── composer.json                 # Depends on shadcn2fluid-templates
└── ext_emconf.php
```

## Thank You

This extension would not exist without the **TYPO3 Core Team** and all contributors who created the [Camino theme](https://docs.typo3.org/). Camino's architecture — `PAGEVIEW` rendering, visual-editor content areas, Fluid 5 typed parameters, and clean backend layouts — is the foundation Desiderio builds on.

Thank you for making TYPO3 theming modern, accessible, and delightful.

## License

GPL-2.0-or-later
