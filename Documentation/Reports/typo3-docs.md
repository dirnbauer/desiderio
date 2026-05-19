# TYPO3 Documentation Audit

Date: 2026-05-16
Target: TYPO3 14.3 LTS only
Status: Green

## Summary

README and Documentation now match the current release metadata,
workspace behavior, Content Blocks collection modeling, and local test suite.

## Changes In This Pass

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
