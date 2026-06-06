# Code Quality Review (Thermo-Nuclear)

Date: 2026-06-06 (updated after phase-2 decomposition)
Target: `main`
Status: **Phase 4 complete** — shared `FixtureFieldNormalizer` deduplicates starter/styleguide field normalization; blog demo data lives in `BlogDemoPostDefinitions`.

## Summary

Desiderio's rendering layers (Fluid components → Content Blocks → theme) are cohesive and well guarded by unit tests. Recent extractions (`StyleguideDemoValueGenerator`, `StyleguideCollectionAliasPolicy`) show the right direction. The remaining structural debt is concentrated in three oversized seed commands and one large finisher that mix orchestration with low-level persistence.

The bar for approval on future changes:

- no new files crossing 1,000 lines without decomposition
- no ad-hoc branching bolted onto shared seed/fixture paths
- prefer extracting seeding services over growing command classes

## Findings (prioritized)

### 1. Structural regressions / file-size blockers

| File | Lines | Verdict |
| --- | ---: | --- |
| `Classes/Command/SeedStyleguidePagesCommand.php` | ~612 | **Resolved (phase 2)** — orchestration + page CRUD; fixture logic in `StyleguideFixtureResolver` |
| `Classes/Command/SeedStarterSitesCommand.php` | ~812 | **Resolved (phase 2)** — orchestration + page CRUD; content building in `StarterContentBuilder` |
| `Classes/Command/SeedBlogPagesCommand.php` | ~158 | **Resolved (phase 3)** — orchestration only; tree/layout/demo seeding in `BlogPageTreeSeeder` |
| `Classes/Command/PowermailDemoSeeder.php` | 905 | **High** — nested inside styleguide seeding; own module |
| `Classes/Domain/Finishers/BrevoContactFinisher.php` | ~419 | **Improved (phase 2)** — configuration cascade moved to `BrevoConfigurationResolver` |

`SeedStyleguidePagesCommand` alone exposes ~60 private methods spanning page CRUD, workspace constraints, collection normalization, FAL metadata, and fixture completion. That is not a command; it is a seeding subsystem wearing a Symfony attribute.

**Preferred code-judo move:** split along natural seams that already exist as partial extractions:

```
Classes/Seeding/
  StyleguidePageSeeder.php       # page find/create/update + live-workspace guards
  StyleguideContentSeeder.php    # tt_content insert + collection/file-reference writes
  StyleguideFalSeeder.php        # FAL folder, metadata upsert, asset resolution
  StarterSiteSeeder.php          # corporate starter orchestration (thin command wrapper)
  BlogPageTreeSeeder.php         # blog layout normalization
```

Commands become orchestration shells (`configure()`, `execute()`, IO) delegating to these services. `DatabaseSchemaHelper`, `StyleguideDemoValueGenerator`, and `StyleguideCollectionAliasPolicy` stay canonical — do not duplicate them in starter/blog seeders.

### 2. Missed simplification / duplication

`SeedStarterSitesCommand` and `SeedStyleguidePagesCommand` share nearly identical private flows:

- `__fileReferences` / `__collections` payload keys
- FAL folder bootstrap (`desiderio-styleguide` vs `desiderio-starter`)
- collection row delete/insert cycles
- live-workspace query constraints

This is complexity that was **moved** into two files, not **deleted**. A shared `ContentRecordSeeder` (or trait-free service) for collection + FAL writes would remove a whole category of copy-paste drift.

`BrevoConfigurationResolver` (phase 2) now owns the five-source precedence stack (`finisher option → extension config → site setting → env → default`). The finisher delegates HTTP and form mapping only.

### 3. Spaghetti / branching growth

No acute spaghetti in the hot rendering path. Middleware and ViewHelpers are appropriately narrow.

Watch areas:

- Seed commands accumulate field-type `if` chains (`isFileField`, `normalizeSelectValue`, `normalizeDateTimeFieldValue`, …). These belong in a `FixtureFieldNormalizer` policy class keyed by Content Blocks field type — not inlined in the command.
- `BrevoContactFinisher` silently skips on missing API key/email. Acceptable for production, but the precedence stack makes "why was sync skipped?" harder to trace than necessary.

### 4. Boundary / abstraction / type contracts

**Good:**

- `ExtbasePluginRequestSanitizerMiddleware` — 99 lines, single responsibility, unit-tested. Correct layer for Visual Editor malformed Extbase arguments.
- `ComponentStructureTest` — canonical component inventory (17 atoms, 28 molecules, 4 layouts).
- PHPStan `BrevoConfiguration` array shape on the finisher.

**Improve:**

- `SeedStyleguidePagesCommand` still carries PHPStan baseline entries (documented in Known Problems). New `Classes/` code must not extend the baseline.
- Seed commands use `array<string, mixed>` fixture bags extensively. A typed `StyleguideFixture` value object (or per-ctype DTOs generated from `ContentBlockDefinitionRegistry`) would let normalization delete runtime `is_array` guards.

### 5. Modularity wins (keep doing this)

- `StyleguideDemoValueGenerator` and `StyleguideCollectionAliasPolicy` extractions in v2.5.0 — correct decomposition.
- `StarterSiteDefinitions` / `ContentBlockDefinitionRegistry` — data separated from imperative seeding.
- Seeding helpers colocated under `Classes/Seeding/` — extend this namespace rather than growing commands.

### 6. Documentation drift (fixed in this pass)

| Source | Was | Now |
| --- | --- | --- |
| `README.md` | 16 atoms, 18 molecules | 17 atoms, 28 molecules, 49 components |
| `Documentation/Developer/Index.rst` | 27 molecules | 28 molecules |
| CLI reference | partial (blog seed only) | all four `desiderio:*` commands documented |
| Visual Editor middleware | undocumented | documented in Developer docs |

## Approval bar for future PRs

Do **not** approve when:

- a PR pushes any file from &lt;1,000 to &gt;1,000 lines
- seed/fixture logic is added inline to an existing 1k+ command instead of a `Classes/Seeding/` service
- feature checks scatter across shared rendering paths
- a new wrapper/helper duplicates an existing canonical utility

**Approve** when behavior is correct **and** the change reduces or holds complexity — especially in seed commands and finishers.

## Phase 1 completed (2026-06-06)

Extracted shared seeding services under `Classes/Seeding/`:

| Service | Responsibility |
| --- | --- |
| `ExtensionFalSeeder` | Import extension assets into `fileadmin/desiderio-*` and write `sys_file_reference` rows |
| `CollectionRecordSeeder` | Recursive collection insert + nested FAL references |
| `CollectionCleanupService` | Live-workspace-scoped collection/file-reference cleanup |
| `ContentBlockCollectionMap` | Collection table index from `ContentBlockDefinitionRegistry` |
| `LiveWorkspaceQueryHelper` | `t3ver_wsid` / `t3ver_oid` constraints for destructive queries |
| `SeedingPayloadKeys` | `__fileReferences` / `__collections` payload constants |

Line counts after wiring:

| File | Before | After |
| --- | ---: | ---: |
| `SeedStyleguidePagesCommand.php` | 2,135 | ~1,730 |
| `SeedStarterSitesCommand.php` | 1,583 | ~1,220 |

PHPUnit (153 tests) and PHPStan remain green.

## Phase 2 completed (2026-06-06)

| Service | Responsibility |
| --- | --- |
| `StyleguideFixtureResolver` | Styleguide fixture field normalization, collection aliasing, FAL fixture building |
| `StarterContentBuilder` | Starter-site content block → `tt_content` row + collection/FAL payloads |
| `BrevoConfigurationResolver` | Brevo finisher configuration precedence (options → extension → site → env) |

Line counts after wiring:

| File | Before (phase 1) | After (phase 2) |
| --- | ---: | ---: |
| `SeedStyleguidePagesCommand.php` | ~1,730 | ~612 |
| `SeedStarterSitesCommand.php` | ~1,220 | ~812 |
| `BrevoContactFinisher.php` | 603 | ~419 |

PHPUnit (153 tests) and PHPStan remain green.

## Phase 3 completed (2026-06-06)

| Service | Responsibility |
| --- | --- |
| `BlogPageTreeSeeder` | Discover EXT:blog setups, apply backend layouts, seed demo posts/tags/categories/comments |

Line counts after wiring:

| File | Before | After |
| --- | ---: | ---: |
| `SeedBlogPagesCommand.php` | 1,204 | ~158 |
| `BlogPageTreeSeeder.php` | — | ~1,065 |

PHPUnit (153 tests) and PHPStan remain green.

## Phase 4 completed (2026-06-06)

| Service / data | Responsibility |
| --- | --- |
| `FixtureFieldNormalizer` | Shared scalar/file/checkbox/date field normalization and FAL fixture building |
| `BlogDemoPostDefinitions` | Static demo Blog post payloads for `BlogPageTreeSeeder` |

Line counts after wiring:

| File | Before | After |
| --- | ---: | ---: |
| `StarterContentBuilder.php` | ~431 | ~313 |
| `StyleguideFixtureResolver.php` | ~1,199 | ~1,104 |
| `BlogPageTreeSeeder.php` | ~1,065 | ~991 |

PHPUnit (153 tests) and PHPStan remain green.

## Suggested next refactor (phase 5)

1. Split `PowermailDemoSeeder` if it grows past 1,000 lines (currently ~905).
2. Move styleguide-specific select/icon normalization into `FixtureFieldNormalizer` only if a third consumer appears.
3. Ratchet PHPStan baseline entries out of `StyleguideFixtureResolver` as types tighten.

## Verification

- Component inventory: `Tests/Unit/ComponentStructureTest.php` (49 components)
- Middleware: `Tests/Unit/ExtbasePluginRequestSanitizerMiddlewareTest.php`
- PHPStan: `Build/Scripts/runTests.sh phpstan` (baseline ratchet on styleguide seeder only)
- Full gate: `Build/Scripts/runTests.sh`
