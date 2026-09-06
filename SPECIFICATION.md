# Desiderio architecture

Desiderio is the `webconsulting/desiderio` theme extension for **TYPO3
14.3.6+ and PHP 8.4–8.5**. The installed versions are recorded in
`composer.lock`; requirements and extension metadata are defined in
`composer.json` and `ext_emconf.php`.

This document describes the current architecture. Installation, configuration,
and contributor workflows live in the [manual](Documentation/Index.rst) and
[contributing guide](CONTRIBUTING.md).

## Rendering layers

| Layer | Source | Contract |
| --- | --- | --- |
| Components | `Resources/Private/Components/` | Fluid 5 atoms, molecules, layouts, and organisms with typed arguments; use the `d:` component namespace. |
| Content elements | `ContentBlocks/ContentElements/` | 244 editor-facing Content Blocks compose shared components and expose fields through TCA. |
| Theme | `Configuration/Sets/`, `Resources/Private/Templates/Pages/` | Site settings, backend layouts, page shells, headers, footers, and optional integration templates. |

The component inventory is enforced by
`Tests/Unit/ComponentStructureTest.php`. Content Blocks use `config.yaml`,
`templates/frontend.html`, `templates/backend-preview.fluid.html`,
`language/labels.xlf`, and `fixture.json`; the structural tests and content
element audit enforce their contracts.

The base and content-element site sets provide shared rendering. Scenario
presets select defaults; Blog, News, Solr, and Powermail integrations activate
through their own site sets when the matching extension is installed.

## Content and themes

Site settings select semantic colors, typography, spacing, radius, icon
library, and light/dark behavior. Page templates expose those choices as
`data-*` attributes; committed CSS implements the matching rules. The actual
setting names and defaults live in
`Configuration/Sets/Desiderio/settings.definitions.yaml` and the
[configuration manual](Documentation/Configuration/Index.rst).

Content stores semantic icon keys and TYPO3 FAL references. Render FAL images
through `<f:image>` or `f:uri.image()` so processing and Visual Editor image
decoration remain available. Render editable rich text with `f:render.text`.

Collection fields have distinct parent-field identifiers. Shared child tables
use explicit Record Types with matching field definitions and both
`shareAcrossTables` and `shareAcrossFields`. Existing content is migrated by
the retained `desiderioSharedCollectionTables` upgrade wizard; see
[collection migration](Documentation/Developer/CollectionTableConsolidation.rst).

## Assets and services

Tailwind v4 compiles utility CSS; the feature CSS bundle is built from
`Resources/Private/Css/desiderio/`. `npm run build:assets` also builds the
syntax highlighters and synchronizes icon fonts. The output is committed so
installed sites can use the package without building its source assets.
Applications using Vite provide their own manifest through
`praetorius/vite-asset-collector`; `Build/CiApp` is the local example.

PHP classes use the `Webconsulting\Desiderio\` namespace and Symfony
service registration. Console commands orchestrate seeding; shared FAL,
collection, schema, and fixture behavior belongs in `Classes/Seeding/`.
Middleware provides the element catalog, previews, search, and request
normalization. Existing content migrations stay available independently of
demo seeders.

## Verification

`Build/Scripts/runTests.sh` is the local quality gate. The DDEV setup and
focused checks are documented in [README.md](README.md#development).
PHPStan runs at `level: max`; PHPUnit, functional tests, the Content Blocks
audit, dependency validation, and asset checks cover the package. Browser
checks use the seeded application in `Build/CiApp`.

Prefer core APIs and shared components. Remove completed source codemods
once their output is maintained directly, but retain migrations that existing
installations still need. Review implementation and documentation together.
