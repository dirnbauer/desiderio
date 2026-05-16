# TYPO3 Conformance Report - After

Timestamp: 2026-05-16 15:58:15 Europe/Vienna
Skill: typo3-conformance

## Changes

- Verified live workspace predicates on destructive seeder queries when
  TYPO3 versioning columns exist.
- Verified the PHPUnit guard test for refusing the seeder in an offline
  workspace.
- Updated README and documentation to reflect version `2.2.0`, TYPO3 14.3
  only, required workspaces, and the current 88-test suite.

## Verification

- PHPStan `level: max`: passed.
- PHPUnit: 88 tests, 25,828 assertions, passed.
