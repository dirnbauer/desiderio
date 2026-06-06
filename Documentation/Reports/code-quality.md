# Code Quality Review (Thermo-Nuclear)

Date: 2026-06-06 (release `2.6.0` review)
Target: `v2.6.0` (`7402da09`)
Status: **Approved for minor release** — seed commands are thin orchestration shells; shared logic lives in `Classes/Seeding/`.

## Summary

The v2.6.0 decomposition resolves the primary maintainability blockers from the initial thermo-nuclear review. Styleguide, starter, and blog seed commands no longer carry thousand-line fixture implementations. Shared FAL import, collection insert/cleanup, fixture normalization, and Brevo configuration precedence are centralized in dedicated services.

Remaining debt is bounded and documented:

- `PowermailDemoSeeder` (~905 lines) — under the 1k threshold; split only if it grows further.
- `StyleguideFixtureResolver` (~1,104 lines) — acceptable as a focused fixture engine; do not add unrelated logic.
- `BlogPageTreeSeeder` (~991 lines) — acceptable; demo payloads moved to `BlogDemoPostDefinitions`.

The bar for approval on future changes:

- no new files crossing 1,000 lines without decomposition
- no ad-hoc branching bolted onto shared seed/fixture paths
- prefer extending `Classes/Seeding/` services over growing command classes

## Findings (prioritized)

### 1. Structural regressions / file-size blockers

| File | Lines | Verdict |
| --- | ---: | --- |
| `Classes/Command/SeedStyleguidePagesCommand.php` | ~612 | **Resolved** — orchestration + page CRUD; fixtures in `StyleguideFixtureResolver` |
| `Classes/Command/SeedStarterSitesCommand.php` | ~812 | **Resolved** — orchestration; content in `StarterContentBuilder` |
| `Classes/Command/SeedBlogPagesCommand.php` | ~158 | **Resolved** — orchestration; tree/demo seeding in `BlogPageTreeSeeder` |
| `Classes/Command/PowermailDemoSeeder.php` | ~905 | **Watch** — nested in styleguide flow; split if it crosses 1k |
| `Classes/Seeding/StyleguideFixtureResolver.php` | ~1,104 | **Acceptable** — single-purpose fixture resolver; keep scope tight |
| `Classes/Seeding/BlogPageTreeSeeder.php` | ~991 | **Acceptable** — blog-specific persistence; demo data externalized |
| `Classes/Domain/Finishers/BrevoContactFinisher.php` | ~419 | **Resolved** — config in `BrevoConfigurationResolver` |

No command file crosses 1,000 lines after v2.6.0.

### 2. Code-judo wins (keep this pattern)

Shared services extracted in phases 1–4:

| Service | Responsibility |
| --- | --- |
| `ExtensionFalSeeder` | FAL import + `sys_file_reference` writes |
| `CollectionRecordSeeder` | Recursive collection inserts |
| `CollectionCleanupService` | Live-workspace-scoped cleanup |
| `ContentBlockCollectionMap` | Collection table index |
| `LiveWorkspaceQueryHelper` | Workspace constraint helpers |
| `SeedingPayloadKeys` | `__fileReferences` / `__collections` constants |
| `StyleguideFixtureResolver` | Styleguide fixture normalization |
| `StarterContentBuilder` | Starter content block payloads |
| `BlogPageTreeSeeder` | Blog layout + demo post seeding |
| `BrevoConfigurationResolver` | Brevo finisher config precedence |
| `FixtureFieldNormalizer` | Shared scalar/file/checkbox/date normalization |
| `BlogDemoPostDefinitions` | Static blog demo post fixtures |

Commands delegate through lazy service getters. PHPUnit reflection tests keep thin command wrappers for fixture APIs used by `StyleguideSeedCommandTest`.

### 3. Spaghetti / branching growth

No acute spaghetti in the hot rendering path. Middleware and ViewHelpers remain narrow.

Residual watch areas:

- Styleguide-specific Select/icon normalization stays in `StyleguideFixtureResolver` (correct layer).
- `PowermailDemoSeeder` still embeds form/page seeding — isolate when it grows past 1k lines.

### 4. Boundary / abstraction / type contracts

**Good:**

- `ExtbasePluginRequestSanitizerMiddleware` — single responsibility, unit-tested.
- `ComponentStructureTest` — canonical component inventory (17 atoms, 28 molecules, 4 layouts).
- `FixtureFieldNormalizer` — deletes duplicate field-type branches between starter and styleguide seeders.
- `BrevoConfiguration` array shape on the finisher.

**Improve (phase 5+):**

- Ratchet PHPStan baseline entries out of `StyleguideFixtureResolver` as types tighten.
- Typed fixture DTOs per Content Block would further reduce `array<string, mixed>` guards.

### 5. Documentation alignment

README, Installation, Developer docs, and this report describe:

- all four `desiderio:*` console commands
- `Classes/Seeding/` service map
- Visual Editor middleware and `<f:image>` convention
- current component counts (49 Fluid components)

## Approval bar for future PRs

Do **not** approve when:

- a PR pushes any file from under 1,000 to over 1,000 lines
- seed/fixture logic is added inline to a command instead of `Classes/Seeding/`
- feature checks scatter across shared rendering paths
- a new wrapper duplicates `FixtureFieldNormalizer` or other canonical helpers

**Approve** when behavior is correct **and** complexity is reduced or held flat.

## Release assessment (`2.6.0`)

| Check | Result |
| --- | --- |
| Structural regression | None found |
| Missed dramatic simplification | Addressed in phases 1–4 |
| File-size explosion | None in commands |
| Spaghetti growth | None in changed hot paths |
| Tests | PHPUnit 153 tests green |
| Static analysis | PHPStan `level: max` green (baseline ratchet documented) |

**Ready to tag `v2.6.0`:** Yes.

## Verification

- Component inventory: `Tests/Unit/ComponentStructureTest.php`
- Styleguide fixtures: `Tests/Unit/StyleguideSeedCommandTest.php`
- Middleware: `Tests/Unit/ExtbasePluginRequestSanitizerMiddlewareTest.php`
- Full gate: `Build/Scripts/runTests.sh`
