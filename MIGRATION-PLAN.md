# Big-Bang Migration Plan

Companion to [SPECIFICATION.md](SPECIFICATION.md). Executed in order. **No backward compatibility.** No existing websites need to keep working.

---

## Phase 0 — Snapshot (safety net, not for compatibility)

- [ ] Tag both extensions at HEAD before touching anything
  - `cd /Users/dirnbauer/projects/desiderio && git tag pre-bigbang-v1 && git push --tags`
  - `cd /Users/dirnbauer/projects/shadcn2fluid-templates && git tag pre-bigbang-v3 && git push --tags`
- [ ] Confirm both working trees are clean (`git status`)
- [ ] Branch: `git checkout -b v2-bigbang` in desiderio

_Tags let us come back. No other goal._

---

## Phase 1 — Scorched Earth (delete everything)

Inside `/Users/dirnbauer/projects/desiderio` on branch `v2-bigbang`:

- [ ] Delete everything except:
  - `.git/`
  - `SPECIFICATION.md`
  - `MIGRATION-PLAN.md`
  - `README.md` (will be rewritten)
  - `LICENSE` (if present)

```bash
git rm -rf Classes Configuration Resources Documentation \
           node_modules package.json package-lock.json \
           composer.json ext_emconf.php ext_localconf.php
rm -rf node_modules
```

Inside `/Users/dirnbauer/projects/shadcn2fluid-templates`:

- [ ] Leave untouched for now. After Phase 5 passes, the **repo itself is archived and the local directory deleted** (see Phase 6).

---

## Phase 2 — Skeleton (new composer identity)

- [ ] Create new `composer.json` (`webconsulting/desiderio` 2.0.0, PHP 8.3, TYPO3 14.3, content-blocks ^2.2, vite-asset-collector ^1.0, no dep on shadcn2fluid-templates)
- [ ] Create new `ext_emconf.php` (v2.0.0, beta, no deps on shadcn2fluid_templates)
- [ ] Create `ext_localconf.php` (comment-only)
- [ ] Create `phpstan.neon.dist` (level 8, empty baseline)
- [ ] Create `phpunit.xml.dist` (Tests/Unit)
- [ ] Create `Build/` dir placeholder
- [ ] Create `Configuration/Services.yaml` with PSR-4 autoconfigure
- [ ] Create `Configuration/Icons.php` (empty array to start)
- [ ] Create `Configuration/page.tsconfig` (backend layout selector)

---

## Phase 3 — Port components & content blocks from shadcn2fluid-templates

Copy from `/Users/dirnbauer/projects/shadcn2fluid-templates` → `/Users/dirnbauer/projects/desiderio`, **renaming as we go**:

- [ ] `Resources/Private/Components/` → `Resources/Private/Components/`
  - Replace all `<s2f:…>` → `<d:…>` in component templates (global sed)
- [ ] `Classes/Components/ComponentCollection.php` → `Classes/Components/ComponentCollection.php`
  - Change namespace `Webconsulting\Shadcn2fluidTemplates\Components` → `Webconsulting\Desiderio\Components`
- [ ] `ContentBlocks/ContentElements/` → `ContentBlocks/ContentElements/`
  - Update vendor prefix: all `shadcn2fluid-templates/*` slugs → `desiderio/*`
  - Replace `<s2f:…>` → `<d:…>` in all `Frontend.html`
  - Update labels.xlf if they reference the old name
- [ ] `Resources/Public/Css/components.css` → `Resources/Public/Css/components.css`
- [ ] `Resources/Public/Js/s2f.js` → `Resources/Public/Js/desiderio.js`
- [ ] `Resources/Public/Icons/` → `Resources/Public/Icons/`
- [ ] `Tests/Unit/ExtensionMetadataTest.php` + `ContentBlockStructureTest.php` → adapt expected extension key to `desiderio`

---

## Phase 4 — Port theme from old desiderio (pre-bigbang-v1 tag)

Check out specific files from the tag:

- [ ] `git show pre-bigbang-v1:Resources/Private/Templates/Pages/…` → copy each page template
- [ ] `git show pre-bigbang-v1:Resources/Private/Partials/…` → copy Header, Footer, DarkModeToggle
- [ ] `git show pre-bigbang-v1:Resources/Private/Layouts/…` → copy Page layout
- [ ] `git show pre-bigbang-v1:Resources/Public/Css/desiderio.css`
- [ ] `git show pre-bigbang-v1:Configuration/BackendLayouts/…`
- [ ] `git show pre-bigbang-v1:Classes/Data/StyleguideContentGroups.php`
- [ ] `git show pre-bigbang-v1:Classes/ViewHelpers/…` (3 ViewHelpers)

Rename: any mention of `shadcn2fluid` / `s2f:` / `Shadcn2fluidTemplates` → `desiderio` / `d:` / `Desiderio`.

---

## Phase 5 — Site sets with settings (NEW work)

For each of `Desiderio`, `DesiderioSkin1`…`DesiderioSkin5`:

- [ ] `Configuration/Sets/<name>/config.yaml` (name, label, dependencies)
- [ ] `Configuration/Sets/<name>/settings.definitions.yaml` (per §7 of spec, base set defines all; skins just override defaults)
- [ ] `Configuration/Sets/<name>/settings.yaml` (overrides only)
- [ ] `Configuration/Sets/<name>/setup.typoscript` (PAGEVIEW wiring, asset inclusion keyed by settings)
- [ ] `Configuration/Sets/<name>/page.tsconfig` (only in base)
- [ ] `Resources/Public/Css/skinN.css` for N=1..5

Render settings → CSS custom properties via a one-time `<style>` block in `Partials/Head.html`.

---

## Phase 6 — Retire shadcn2fluid-templates

After Phase 5 is green locally:

- [ ] In shadcn2fluid-templates repo: add `ARCHIVED.md` pointing to desiderio
- [ ] Tag final version: `v3.0.0-archived`
- [ ] Push tag, then **archive the GitHub repo** (UI setting — needs user confirmation)
- [ ] Remove Packagist package (needs user confirmation — irreversible)
- [ ] Delete local directory `/Users/dirnbauer/projects/shadcn2fluid-templates` (needs user confirmation)

---

## Phase 7 — Verification

- [ ] `composer validate`
- [ ] `vendor/bin/phpstan analyse` passes
- [ ] `vendor/bin/phpunit` passes
- [ ] Spin up a DDEV TYPO3 14.3 site, install desiderio, render each page template
- [ ] Switch through all 5 skins, confirm only visuals change
- [ ] Change each setting in Site Management UI, confirm effect in frontend
- [ ] Styleguide renders all 250 elements

---

## Phase 8 — Merge & release

- [ ] Rewrite `README.md` (single extension, describe all three layers + skins + settings)
- [ ] Merge `v2-bigbang` → `main`
- [ ] Tag `v2.0.0`
- [ ] Publish to Packagist

---

## Risks & explicit non-risks

**Non-risks (stated by user):**
- Existing sites using `shadcn2fluid_templates` or old `desiderio 1.x`: **not supported**.
- The `s2f:` Fluid namespace: **gone forever**, no alias.
- DB upgrade wizards for old content: **not provided**.

**Real risks:**
- The `<s2f:…>` → `<d:…>` rename must be exhaustive across ~250 `Frontend.html` files. Plan: codemod + `ContentBlockStructureTest` to catch stragglers.
- 250 content block YAMLs may reference each other by vendor prefix. Plan: grep for `shadcn2fluid-templates/` before Phase 5 sign-off.
- Settings-driven CSS needs care to stay cache-friendly. Plan: emit one `<style>` block in `<head>` derived from `{settings.desiderio.*}`; nothing dynamic per-request beyond that.
