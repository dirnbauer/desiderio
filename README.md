# Desiderio

A TYPO3 v14.3 theme extension: a shadcn/ui-inspired **Fluid 5 component
library** (17 atoms, 37 molecules, 4 layouts, 4 organisms), 244 Desiderio Content Blocks,
page templates, optional Blog/News/Solr/Powermail overrides, and a runtime
theme system driven by TYPO3 site settings.

No JavaScript component runtime: the committed assets are the Tailwind v4 /
shadcn CSS theme, lightweight progressive JavaScript, a small syntax
highlighter and chart helpers. Asset delivery uses Simon Praetorius' Vite
integration.

## Requirements

- TYPO3 CMS `^14.3.6`, PHP `^8.4`, Composer-based installation
- `friendsoftypo3/content-blocks` `^2.2`
- `friendsoftypo3/visual-editor` `^1.8` plus the Visual Editor enhancements package
- `praetorius/vite-asset-collector` `^1.18`

Optional, activated by their own site sets when installed: `georgringer/news`,
`t3g/blog`, `apache-solr-for-typo3/solr`, `in2code/powermail`,
`studiomitte/friendlycaptcha`.

## Install

```bash
composer require webconsulting/desiderio
vendor/bin/typo3 extension:setup
vendor/bin/typo3 cache:flush
```

Enable the site sets in this order:

1. `Desiderio Base` (`webconsulting/desiderio`)
2. `Desiderio Content Elements` (`webconsulting/desiderio-content-elements`)
3. a scenario package, e.g. `webconsulting/desiderio-preset-corporate`
4. optional integration sets: Blog, News, Solr, Powermail

## Configure

The look is selected in **Site Management → Settings** and rendered as `data-*`
attributes on `<body>`, so a preset switch repaints colors, radius, density,
focus rings, fonts and icons at runtime — content stores semantic icon keys and
stays untouched:

`desiderio.shadcn.preset` · `desiderio.shadcn.style` ·
`desiderio.shadcn.iconLibrary` · `desiderio.layout.density` ·
`desiderio.layout.container` · `desiderio.layout.radius` ·
`desiderio.typography.fontSans` · `desiderio.theme.darkModeDefault` ·
`desiderio.theme.darkModeToggle`

Every setting, the form/search/Blog/News integrations, the self-hosted icon
fonts and the Visual Editor behaviour are documented in
[Documentation/Configuration](Documentation/Configuration/Index.rst).

## Use

Editors pick elements in the "Add content" panel, which shows a rendered
preview per element and a typo-tolerant search. Content elements compose the
Fluid components instead of one-off markup, so they follow the active preset
and dark mode automatically.

Seed demo content and the element library with the console commands:

```bash
vendor/bin/typo3 desiderio:library:seed --parent=<page-uid>
vendor/bin/typo3 desiderio:styleguide:seed --parent=<page-uid>
vendor/bin/typo3 desiderio:starter:seed
```

See [Documentation/Developer/Commands.rst](Documentation/Developer/Commands.rst)
for the full list, and
[Innesto](Documentation/Developer/Innesto.rst) for grafting elements from other
shadcn registries.

## Develop

```bash
ddev start
ddev exec composer install
ddev exec npm ci
ddev exec npm run build              # every generator, in order
ddev exec Build/CiApp/bootstrap.sh   # disposable SQLite app with seeded content
ddev launch
```

```bash
ddev exec Build/Scripts/runTests.sh            # phpstan, unit, functional, validate, assets
ddev exec Build/Scripts/runTests.sh -s phpstan # one suite
vendor/bin/typo3 desiderio:templates:lint      # Fluid 5 lint gate
```

The quality bar is PHPStan level 8 without a baseline, PHPUnit unit and
functional suites, the Content Blocks audit, the Fluid template lint gate, the
atomic-design conformance test, and a generated-assets check that rebuilds
every asset and fails on a diff. Details:
[Documentation/Developer/Build.rst](Documentation/Developer/Build.rst).

## Docs

- [Introduction](Documentation/Introduction/Index.rst)
- [Installation](Documentation/Installation/Index.rst)
- [Configuration](Documentation/Configuration/Index.rst)
- [For editors](Documentation/Editor/Index.rst)
- [For developers](Documentation/Developer/Index.rst)
- [Changelog](Documentation/Changelog/Index.rst)
- [Contributing](CONTRIBUTING.md)

## License

GPL-2.0-or-later. See [LICENSE](LICENSE). Bundled icon webfonts keep their own
licenses; see
[Documentation/Configuration/IconFonts.rst](Documentation/Configuration/IconFonts.rst).
