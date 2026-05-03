# TYPO3 Testing Audit (v14.3 LTS)

Date: 2026-05-03
Coverage estimate: Low
Total tests: 62 (20617 assertions, 0.885s, all passing — PHPUnit 11.5.55, PHP 8.5.4)

## Summary
The suite is structural/convention-focused: 7 unit-only test classes assert content-block layout, fixtures, theme tokens, icon registry, audit-script cleanliness, and parts of the styleguide seed command. None of the four `Classes/ViewHelpers/*.php` and none of `Classes/Components/ComponentCollection.php` execute under tests, the `typo3/testing-framework` dependency is declared but unused (no `UnitTestCase`/`FunctionalTestCase`), and there is no functional, integration, E2E, mutation, runTests.sh, or CI pipeline. PHPUnit 11.5 is configured but tests still rely on the legacy `testFooBar` naming convention rather than `#[Test]` / `#[DataProvider]` attributes.

## Findings (severity-tagged)

- [HIGH] No CI pipeline — repo has no `.github/workflows/`, no `.gitlab-ci.yml`, and no `Build/Scripts/runTests.sh`. Path: repository root. Fix: add `Build/Scripts/runTests.sh` plus a GitHub Actions matrix (PHP 8.3-8.5 x TYPO3 14.3) running phpunit + phpstan.
- [HIGH] Functional layer absent — `typo3/testing-framework ^9.0` is declared in `composer.json` but never used; no `Tests/Functional/` directory exists. Path: `composer.json:46`, `Tests/`. Fix: bootstrap `Tests/Functional/` with a `FunctionalTestCase` covering `SeedStyleguidePagesCommand` against an in-memory SQLite TYPO3 instance.
- [HIGH] ViewHelpers untested — 4 ViewHelper classes have zero coverage; `Tests/Unit/ViewHelpers/` exists but is empty. Path: `Classes/ViewHelpers/{FixtureJsonViewHelper,RecordHasFieldViewHelper,StyleguideFixtureSummaryViewHelper,StyleguideGroupsViewHelper}.php`, `Tests/Unit/ViewHelpers/` (empty dir). Fix: add a `ViewHelperBaseTestcase` per VH testing render output; matches existing PHPUnit 11 setup.
- [HIGH] Seed command tested via source-string introspection only — `StyleguideSeedCommandTest` mostly does `assertStringContainsString` against the file source (e.g. `Tests/Unit/StyleguideSeedCommandTest.php:19-27`), exercising no actual DB write paths. Path: `Tests/Unit/StyleguideSeedCommandTest.php`. Fix: replace string-match assertions with a functional test that runs the command against a TYPO3 SQLite fixture and verifies tt_content rows.
- [MEDIUM] Legacy test naming, no PHPUnit 11 attributes — every test method uses convention-based `testFooBar` naming; no `#[Test]`, `#[DataProvider]`, `#[CoversClass]`, `#[TestDox]`, `#[Group]` attributes anywhere. Path: all 7 files in `Tests/Unit/`. Fix: add `#[Test]` (and `#[DataProvider]` to the few that could use parameterization in `ContentBlockStructureTest`) — required posture for PHPUnit 12/13.
- [MEDIUM] No coverage exclusions and no coverage report wiring — `phpunit.xml.dist` has `<source>` but no `<exclude>` block, no `<coverage>` element, no Clover/HTML report destination. Path: `phpunit.xml.dist:12-19`. Fix: add `<coverage>` with HTML+Clover output and `<exclude>` for `ext_emconf.php`, `ext_localconf.php`, `Configuration/`.
- [MEDIUM] No mutation testing — Infection is not present in `composer.json` or `composer.lock`. Path: `composer.json:39-47`. Fix: add `infection/infection ^0.29` and a `Build/infection.json` with `minMsi=70`.
- [MEDIUM] `ContentElementAuditTest` shells out via `shell_exec` — invokes `scripts/audit-content-elements.php` through a child PHP process at `Tests/Unit/ContentElementAuditTest.php:38`, making the test slow and non-deterministic on CI. Path: `Tests/Unit/ContentElementAuditTest.php:33-68`. Fix: refactor `audit-content-elements.php` so its core returns an array, then call it directly in-process (as a class) — the script remains executable as a CLI wrapper.
- [LOW] `phpunit.xml.dist` lacks `displayDetailsOnTestsThatTriggerDeprecations` / strict modes — no `failOnWarning`, `failOnRisky`, or `beStrictAboutOutputDuringTests` flags set. Path: `phpunit.xml.dist:1-20`. Fix: add `failOnRisky="true" failOnWarning="true" beStrictAboutOutputDuringTests="true"` to the `<phpunit>` element.
- [LOW] Hardcoded magic numbers in structural tests — e.g. `EXPECTED_COUNT = 255` in `Tests/Unit/ContentBlockStructureTest.php:12` will need touching every time a content block is added/removed. Path: `Tests/Unit/ContentBlockStructureTest.php:12`. Fix: derive expected count from a manifest file or relax to `assertGreaterThanOrEqual` with a documented minimum.
- [LOW] No `phpat`/architecture tests — extension has clear layer boundaries (Command, ViewHelpers, Data, Components) but no enforcement. Path: `Classes/`. Fix: add `carlosas/phpat ^3.0` with rules forbidding ViewHelpers from depending on Command or DB.
- [LOW] No JS / E2E tests for the 255 content elements — they render Fluid + Tailwind but have no Playwright smoke or accessibility test. Path: `ContentBlocks/ContentElements/`. Fix: add Playwright with one happy-path render assertion per wizard category (~12 groups).
- [LOW] Audit script is correctly wired — `Tests/Unit/ContentElementAuditTest.php:33-68` already gates `scripts/audit-content-elements.php` via the strict categories list at `Tests/Unit/ContentElementAuditTest.php:21-31`. Verified working — no fix required (informational).

## Recommended improvements (ranked)
1. Add `Build/Scripts/runTests.sh` and a GitHub Actions workflow (PHP 8.3/8.4/8.5 x TYPO3 14.3) running phpunit + phpstan + cgl, so the green local run is reproduced on PRs.
2. Bootstrap `Tests/Functional/` with `typo3/testing-framework`'s `FunctionalTestCase`; rewrite `StyleguideSeedCommandTest` to drive the actual command end-to-end and add a functional test for the icon registry.
3. Cover the four ViewHelpers in `Classes/ViewHelpers/` with proper render-tests (extend `TYPO3\TestingFramework\Fluid\Unit\ViewHelpers\ViewHelperBaseTestcase`).
4. Migrate every test method to PHPUnit 11 attributes (`#[Test]`, `#[DataProvider]`, `#[CoversClass]`) — required before bumping to PHPUnit 12+.
5. Refactor `scripts/audit-content-elements.php` into a callable class so `ContentElementAuditTest` no longer shells out via `shell_exec` (`Tests/Unit/ContentElementAuditTest.php:38`).
6. Enable coverage in `phpunit.xml.dist` (`<coverage>` + `<exclude>` for `ext_*`/`Configuration/`) and add `failOnRisky`, `failOnWarning`, `beStrictAboutOutputDuringTests`.
7. Add Infection (`Build/infection.json`, `minMsi=70`) once functional tests exist — current unit-only suite is too thin to mutate meaningfully.
8. Add `carlosas/phpat` architecture tests to lock the Command -> Data, ViewHelpers -> Data layering.
9. Replace the hardcoded `EXPECTED_COUNT = 255` in `Tests/Unit/ContentBlockStructureTest.php:12` with a manifest-driven assertion to reduce churn.
10. Add a Playwright smoke suite (one render-and-axe-check per wizard category) to catch frontend regressions across the 255 content elements.
