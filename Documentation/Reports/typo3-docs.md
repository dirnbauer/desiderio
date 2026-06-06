# TYPO3 Documentation Audit

Date: 2026-06-06
Target: TYPO3 14.3 LTS only
Status: Green

## Summary

README and Documentation now match the current component inventory (49 Fluid
components), console commands, Visual Editor middleware, and maintainability
guidance from the thermo-nuclear code quality review.

## Changes In This Pass

- README component counts corrected to 17 atoms, 28 molecules, 4 layouts.
- README documents all `desiderio:*` seed/setup commands plus FAL output
  folders and workspace guards.
- README adds Visual Editor compatibility notes (`ExtbasePluginRequestSanitizerMiddleware`,
  `<f:image>` convention).
- Developer docs add Console commands, Request middleware, and Maintainability
  sections with seed-command size guidance.
- New report: `Documentation/Reports/code-quality.md`.
- Reports index updated to reference the code quality review.

## Previous Pass (2026-05-16)

- README version badge/status updated to `2.2.0`.
- Documentation changelog page updated to `2.2.0`.
- Editor and Known Problems docs updated for live-workspace seeder
  behavior and live-row cleanup scoping.
- Developer docs and README updated to the current 101-test suite.
- Timestamped before/after reports were added for each requested
  agentic skill loop.
- README, contributing notes, specification, and shadcn upgrade guidance now
  document the `<f:image>` / `f:uri.image()` convention for Content Block media
  fields and structured Fluid `data` arguments.
- README, contributing notes, specification, migration notes, and shadcn
  guidance now document `Collection` field prefixing, nested collection seeding,
  and when collection table reuse is appropriate.

## Verification

- Search found no stale TYPO3 13 support claims outside explicit
  "v13 is not supported" wording.
- `validate_docs.sh`: passed.
- `render_docs.sh`: passed.
- `analyze-docs.sh`: blocked by macOS Bash 3 associative-array support
  after extraction completed.
- Full local gate: passed.
