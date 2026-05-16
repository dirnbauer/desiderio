# TYPO3 Testing Audit

Date: 2026-05-16
Target: TYPO3 14.3 LTS only
Status: Green

## Summary

The extension is gated by PHPStan at `level: max`, PHPUnit 11.5, the
content-element audit, Composer validation/audit, and Tailwind bundle
sync. The current local PHPUnit suite contains 88 tests and 25,828
assertions.

## Coverage Added In This Pass

- Verified the unit test proving `desiderio:styleguide:seed` refuses to run
  in an offline workspace.
- Updated docs and README to the current 88-test count.

## Verification

- `Build/Scripts/runTests.sh phpstan`: passed.
- `Build/Scripts/runTests.sh phpunit`: passed.
- `composer validate --strict --no-check-publish`: passed.
- `composer audit --no-dev --abandoned=fail`: passed.
- `Build/Scripts/runTests.sh`: passed.
