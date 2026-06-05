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

- **Components**: 16 atoms, 18 molecules, and 4 layout primitives using typed Fluid arguments.
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

## Blog

When `t3g/blog` is installed, `webconsulting/desiderio-blog` adds shadcn-styled Blog template paths. The templates cover list, detail, sidebar widgets, comments, author blocks, related posts, metadata badges, categories, tags, and RSS output headers.

Existing Blog page trees can be aligned with:

```bash
vendor/bin/typo3 desiderio:blog:seed-pages --root=<blog-root-uid>
```

The command exits without changes when `t3g/blog` is not loaded.

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
```

Focused checks:

```bash
Build/Scripts/runTests.sh phpstan
Build/Scripts/runTests.sh phpunit
Build/Scripts/runTests.sh audit
composer validate
composer audit --no-dev --abandoned=fail
```

The CI workflow runs PHPStan at max level, PHPUnit on supported PHP versions, composer validation/audit, and the strict Content Blocks audit.

## Documentation

Full documentation lives in `Documentation/`:

- `Documentation/Installation/Index.rst`
- `Documentation/Configuration/Index.rst`
- `Documentation/Editor/Index.rst`
- `Documentation/Developer/Index.rst`
- `Documentation/ShadcnUpgrade.md`

Visual reference images are intentionally not shipped in the documentation. The extension is preset-driven and the output changes by site setting, so static references become stale quickly.
