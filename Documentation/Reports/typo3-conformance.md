# TYPO3 Conformance Audit (v14.3 LTS)

Date: 2026-05-03
Score: 84/100

## Summary
Extension is solidly v14-aligned: strict_types everywhere, attribute-based command, full constructor DI, no v14 deprecations (HashService, magic finders, hmac, ext_tables.php, removed PageRenderer methods), proper Sets/TCA layout, and correct XLIFF 2.0 in Resources/Private/Language. Score is held below 90 by 765 lingering XLIFF 1.2 files in ContentBlocks/ContentElements/*/language/ (it/fr/es), one stray `GeneralUtility::makeInstance(StorageRepository::class)` inside the seed command, and static caches on `StyleguideContentGroups` instead of a DI-managed cache.

## Findings (severity-tagged)
- [HIGH] XLIFF 1.2 leftovers in ContentBlocks — 765 of 1275 ContentBlock translation files still use XLIFF 1.2 (`<trans-unit>`/`xliff version="1.2"`), v14 standard is XLIFF 2.0. Path: ContentBlocks/ContentElements/*/language/{it,fr,es}.labels.xlf (255 each). Fix: bulk-convert to XLIFF 2.0 via codemod or by re-exporting with TYPO3 translation tooling.
- [MEDIUM] makeInstance for service-shaped class — `StorageRepository` resolved via `GeneralUtility::makeInstance` in a constructor-DI command. Path: Classes/Command/SeedStyleguidePagesCommand.php:1634. Fix: inject `StorageRepository` via the existing constructor (Symfony console + autowire handles it).
- [MEDIUM] Static state in service class — `private static ?array $cache` and `$fixtureCache` cause cross-request leakage in long-running CLI/processes and break testability. Path: Classes/Data/StyleguideContentGroups.php:19,22. Fix: convert to a non-static service registered via Services.yaml and inject where needed; or back with `Core\Cache\CacheManager` (`desiderio` runtime cache).
- [MEDIUM] ComponentCollection uses static path resolver — instantiates `TemplatePaths` and resolves a constant path each call instead of caching/injecting paths. Path: Classes/Components/ComponentCollection.php:13-22. Fix: cache the resolved `TemplatePaths` in a property (the collection is a service) to avoid repeated EXT path resolution per render.
- [LOW] ext_emconf state vs. composer minimum-stability — `state => 'stable'` while composer.json sets `minimum-stability: dev`. Path: composer.json:19, ext_emconf.php:11. Fix: switch composer to `stable` (with explicit dev requires) or document why dev is required.
- [LOW] Suggests/conflicts mismatch composer.json vs. ext_emconf.php — composer suggests `news`, `solr`, `visual-editor`; ext_emconf only suggests `news`, `solr`. Path: composer.json:21-25, ext_emconf.php:25-28. Fix: add `visual_editor` to ext_emconf `suggests` so EM and composer agree.
- [LOW] Static facade pattern in ViewHelpers — `FixtureJsonViewHelper` and `StyleguideGroupsViewHelper` call `StyleguideContentGroups::getFixtures()` statically, hiding the dependency. Path: Classes/ViewHelpers/FixtureJsonViewHelper.php:22, StyleguideGroupsViewHelper.php:37. Fix: once the data class is a real service, inject it via `injectStyleguideContentGroups()` (Fluid VHs support setter injection).
- [LOW] Public `$escapeOutput` instead of typed property — uses old-style `protected $escapeOutput = false;` without type. Path: Classes/ViewHelpers/FixtureJsonViewHelper.php:18, StyleguideGroupsViewHelper.php:20. Fix: declare `protected bool $escapeOutput = false;` for PHP 8.4 strictness alignment.
- [LOW] Custom data-attribute could be misread as Bootstrap — `data-toggle-group` (custom JS hook) is fine but visually overlaps with deprecated BS4 `data-toggle`. Path: Resources/Private/Templates/Pages/DesiderioStyleguide.fluid.html:27. Fix: rename to `data-d-toggle-group` or `data-group-toggle` to remove ambiguity.
- [LOW] ext_emconf author_email empty — TER guideline expects a contact email. Path: ext_emconf.php:10. Fix: set `'author_email' => 'studio@webconsulting.at'`.
- [LOW] Command class size — 2126-line `SeedStyleguidePagesCommand` mixes orchestration, FAL handling, TCA introspection, DataHandler-style upserts. Path: Classes/Command/SeedStyleguidePagesCommand.php. Fix: extract `StyleguideFalSeeder`, `ContentBlockDefinitionLoader`, and `RecordUpserter` services.

## Recommended fixes (ranked)
1. Bulk-migrate the 765 XLIFF 1.2 files in `ContentBlocks/ContentElements/*/language/{it,fr,es}.labels.xlf` to XLIFF 2.0 (2-space indent, `<unit>`/`<segment>`).
2. Inject `StorageRepository` via the constructor of `SeedStyleguidePagesCommand` and drop the `GeneralUtility::makeInstance` call at line 1634.
3. Convert `Webconsulting\Desiderio\Data\StyleguideContentGroups` from a static facade to a DI-registered service; inject it into the three styleguide ViewHelpers via setter/constructor.
4. Cache `TemplatePaths` inside `ComponentCollection` so EXT path resolution runs once per request lifecycle.
5. Reconcile composer.json and ext_emconf.php metadata: align `suggests` (add `visual_editor`), set `author_email`, and either drop `minimum-stability: dev` or document the reason.
6. Type the `$escapeOutput` properties (`protected bool $escapeOutput = false;`) across all four ViewHelpers for PHP 8.4 hygiene.
7. Rename the styleguide DOM hook from `data-toggle-group` to `data-d-toggle-group` to avoid Bootstrap-4 visual collision in audits.
8. Split `SeedStyleguidePagesCommand` into smaller services (FAL seeder, definition loader, record upserter) to reduce its 2k-line footprint.
