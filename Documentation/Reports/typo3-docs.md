# TYPO3 Documentation Audit (v14.3 LTS)

Date: 2026-05-03

## Summary
Documentation is README-driven only. There is no `Documentation/Index.rst`, no `guides.xml`, no `Configuration/Editor/Developer` ReST tree, and no CHANGELOG/LICENSE/CONTRIBUTING files — so the extension cannot render on docs.typo3.org. The README and SPECIFICATION are partially out of date for the v2.1.0 / TYPO3 v14.3 LTS bump: PHPStan is now `level: max` with `saschaegerer/phpstan-typo3`, but README still advertises "level 8"; the new `DesiderioNews` page template, News load-more partial, and `typo3/cms-workspaces` requirement are not documented for editors or integrators.

## Findings
- [HIGH] No TYPO3 ReST docs tree — extension cannot publish to docs.typo3.org. Path: /Users/dirnbauer/projects/desiderio/Documentation/ (only `ShadcnUpgrade.md`, `Images/`, `Reports/`).
- [HIGH] No `Documentation/guides.xml` — no project URL `docs.typo3.org/p/webconsulting/desiderio/...` configured. Path: /Users/dirnbauer/projects/desiderio/Documentation/ (missing).
- [HIGH] No `Index.rst`, no `Configuration/`, `Editor/`, `Developer/` sub-trees per TYPO3 docs convention. Path: /Users/dirnbauer/projects/desiderio/Documentation/ (missing).
- [HIGH] README never states the current version `2.1.0`, the `state = stable` flip from `beta`, or "v14.3 LTS" explicitly — only "TYPO3 14.3+". Path: /Users/dirnbauer/projects/desiderio/README.md:39.
- [HIGH] PHPStan section is stale: README claims "level 8, no errors" but `phpstan.neon.dist` is `level: max` with `saschaegerer/phpstan-typo3 ^3.0` and `phpstan-strict-rules ^2.0`. Path: /Users/dirnbauer/projects/desiderio/README.md:267 vs /Users/dirnbauer/projects/desiderio/phpstan.neon.dist:6.
- [HIGH] `typo3/cms-workspaces ^14.3` requirement is undocumented (added to `composer.json` and `ext_emconf.php` but not mentioned in README, SPECIFICATION, or MIGRATION-PLAN). Path: /Users/dirnbauer/projects/desiderio/composer.json:15, /Users/dirnbauer/projects/desiderio/ext_emconf.php:18.
- [HIGH] New `DesiderioNews` page template + backend layout not in README's page-templates table. Path: /Users/dirnbauer/projects/desiderio/README.md:82-90 (table missing the row); template at /Users/dirnbauer/projects/desiderio/Resources/Private/ShadcnUi/Templates/Pages/DesiderioNews.fluid.html.
- [HIGH] News load-more feature undocumented for editors. The `useLoadMore` / `loadMoreCount` settings auto-enable on the `pagets__DesiderioNews` backend layout but are not described anywhere in README/Documentation. Path: /Users/dirnbauer/projects/desiderio/Configuration/Sets/DesiderioNews/setup.typoscript:12-25; partial at /Users/dirnbauer/projects/desiderio/Resources/Private/Extensions/News/Partials/List/LoadMore.html.
- [MEDIUM] No `LICENSE` / `LICENSE.txt` file at repo root despite GPL-2.0-or-later declared in `composer.json`. Path: /Users/dirnbauer/projects/desiderio/ (missing); license stated only in /Users/dirnbauer/projects/desiderio/composer.json:5 and /Users/dirnbauer/projects/desiderio/README.md:272.
- [MEDIUM] No `CHANGELOG.md` / `RELEASE-NOTES.md` for the 2.0.0 → 2.1.0 transition. Path: /Users/dirnbauer/projects/desiderio/ (missing).
- [MEDIUM] No `CONTRIBUTING.md` or contribution guidance. Path: /Users/dirnbauer/projects/desiderio/ (missing).
- [MEDIUM] `SPECIFICATION.md` is stale: still says `Version: 2.0.0`, `State: beta`, and lists 4 backend layouts; reality has 6 backend layouts (incl. DesiderioBlog, DesiderioExtension, DesiderioNews) and 2.1.0/stable. Path: /Users/dirnbauer/projects/desiderio/SPECIFICATION.md:32-34, 152-159.
- [MEDIUM] `SPECIFICATION.md` still says "PHPStan level 8 with small baseline" and "PHP 8.3"; constraints now PHP `8.3.0-8.5.99` and PHPStan `level: max`. Path: /Users/dirnbauer/projects/desiderio/SPECIFICATION.md:342, 4-5.
- [MEDIUM] `MIGRATION-PLAN.md` is "Phase 1-8 big-bang" plan from the 1.x→2.0 rebuild — already executed; it does not cover the v14.3 LTS / 2.1.0 step and is now historical. Path: /Users/dirnbauer/projects/desiderio/MIGRATION-PLAN.md (entire file).
- [MEDIUM] README content-element count says "255" but SPECIFICATION says "250" / "255" inconsistently. Path: /Users/dirnbauer/projects/desiderio/README.md:4,29,205 vs /Users/dirnbauer/projects/desiderio/SPECIFICATION.md:137,168,175.
- [LOW] `ShadcnUpgrade.md` is the only existing Documentation/ markdown — useful but not linked into a TYPO3 docs tree. Path: /Users/dirnbauer/projects/desiderio/Documentation/ShadcnUpgrade.md.
- [LOW] README "Testing" block omits `composer phpstan` script (which exists). Path: /Users/dirnbauer/projects/desiderio/README.md:262-268 vs /Users/dirnbauer/projects/desiderio/composer.json:64-67.
- [LOW] `webconsulting/desiderio-news` row in README lists the path mapping but does not call out that it is hidden / pulled in as optional dep — minor copy clarification. Path: /Users/dirnbauer/projects/desiderio/README.md:223-232.

## Recommended updates (ranked)
1. Add a TYPO3 ReST docs skeleton: `Documentation/guides.xml` (with `project-home` set to `https://docs.typo3.org/p/webconsulting/desiderio/main/en-us/`), `Documentation/Index.rst`, and `Configuration/`, `Editor/`, `Developer/`, `Changelog/` sub-trees so docs render on docs.typo3.org.
2. Update README prominently: state `Version 2.1.0 (stable)` and `TYPO3 v14.3 LTS`; add the new `DesiderioNews` row to the page-templates table; document the News load-more feature for editors (auto-enabled on `DesiderioNews` backend layout, configurable via `plugin.tx_news.settings.list.useLoadMore` / `loadMoreCount`).
3. Fix the README "Testing" block: replace `phpstan analyse # level 8, no errors` with `composer phpstan` (level max with `saschaegerer/phpstan-typo3` strict rules) and mention `phpstan-baseline.neon`.
4. Add `composer require typo3/cms-workspaces` (or note it ships as a hard dependency) to README installation/dependencies, and explain why (workspace-safe `f:render.text` and visual-editor support).
5. Add `CHANGELOG.md` (or `Documentation/Changelog/2.1.0.rst`) covering: TYPO3 v14.3 LTS bump, `state = stable`, `cms-workspaces` requirement, shadcn-styled News templates + load-more partial, `DesiderioNews` page template, PHPStan level max + saschaegerer rules.
6. Add a `LICENSE` file at repo root (GPL-2.0-or-later) — currently only declared in metadata.
7. Add `CONTRIBUTING.md` (or `Documentation/Contribution/Index.rst`) with the test/lint workflow and PR conventions.
8. Refresh `SPECIFICATION.md`: bump version/state, list all 6 backend layouts (Startpage, Contentpage, ContentpageSidebar, Styleguide, Blog, Extension, News), update PHP constraint to 8.3-8.5 and PHPStan to level max.
9. Mark `MIGRATION-PLAN.md` as historical (add header noting "Executed for v2.0.0; retained for context") or move it under `Documentation/Archive/`.
10. Reconcile the 250 vs 255 content-element count between README and SPECIFICATION; pick one source of truth (likely the structure test) and reference it.
11. Move `Documentation/ShadcnUpgrade.md` into the new ReST tree as `Documentation/Developer/ShadcnPresets.rst` so it is reachable from the rendered docs.
12. Document optional integration sets (`desiderio-news`, `desiderio-solr`) in a dedicated `Documentation/Editor/Integrations.rst` page with screenshots from `Documentation/Images/`.
