# TYPO3 Testing Audit

Date: 2026-06-06
Target: release `v2.6.0`, TYPO3 14.3 LTS only
Status: Green

## Summary

The extension is gated by PHPStan at `level: max`, PHPUnit 11.5, the
content-element audit, Composer validation/audit, and Tailwind bundle
sync. The current local PHPUnit suite contains 153 unit tests and 31,801
assertions, plus 2 functional tests for `DatabaseSchemaHelper`.

## Coverage Added In This Pass

- Added `Build/Scripts/runFunctionalTests.sh` with SQLite-backed TYPO3
  functional tests for seeding schema helpers.
- `DatabaseSchemaHelperFunctionalTest` verifies real `pages` table column
  discovery and `filterRow()` behavior against a bootstrapped TYPO3 instance.
- Content-element audit now enforces `fixture_missing_field` and
  `collection_child_seed_gap` at zero.

## Verification

- `Build/Scripts/runTests.sh phpstan`: passed.
- `Build/Scripts/runTests.sh phpunit`: passed.
- `Build/Scripts/runFunctionalTests.sh`: passed.
- `composer validate --strict --no-check-publish`: passed.
- `composer audit --no-dev --abandoned=fail`: passed.
- `Build/Scripts/runTests.sh`: passed.
