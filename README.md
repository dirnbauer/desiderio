# Desiderio

A camino-inspired TYPO3 v14 theme built on [shadcn2fluid](https://github.com/webconsulting/shadcn2fluid-templates) components.

## Thank You

This theme would not exist without the outstanding work of the **TYPO3 Core Team** on the [Camino theme](https://docs.typo3.org/). Camino's architecture — PAGEVIEW rendering, visual-editor content areas, Fluid 5 typed parameters, and clean backend layouts — served as the foundation and inspiration for Desiderio.

Special thanks to all contributors who designed, developed, and refined Camino. Your commitment to modern, accessible TYPO3 theming benefits the entire community.

## Architecture

Desiderio separates concerns into a **base site set** (TYPO3 infrastructure) and **template site sets** (visual design):

- **Base (`webconsulting/desiderio`)** — Backend layouts, PAGEVIEW configuration, data processors, page templates with content areas for the visual editor
- **Template 1 (`webconsulting/desiderio-template1`)** — SaaS Landing: clean, modern CSS for marketing pages

### Backend Layouts

| Layout | Description |
|--------|-------------|
| Startpage | Stage + Content (full width) |
| Contentpage | Stage + Content (full width) |
| Contentpage with Sidebar | Stage + Content + Sidebar |
| Styleguide | Content only (auto-displays all content elements) |

No footer content areas — the footer is rendered as a static partial.

## Requirements

- TYPO3 v14.3+
- PHP 8.2+
- [shadcn2fluid-templates](https://github.com/webconsulting/shadcn2fluid-templates) v3.0+

## Installation

```bash
composer require webconsulting/desiderio
```

Then add the Desiderio site set (and optionally a template set) to your site configuration.

## License

GPL-2.0-or-later
