# TYPO3 Workspaces Audit

Date: 2026-05-16
Target: TYPO3 14.3 LTS only
Status: Green with documented FAL limitation

## Summary

Desiderio requires `typo3/cms-workspaces ^14.3` and keeps the
styleguide seeder out of offline workspaces. The seeder writes fixture
records to the live workspace only, and destructive cleanup queries now
add explicit live workspace predicates when TYPO3 versioning columns
exist.

## Current Controls

- `composer.json` requires `typo3/cms-workspaces ^14.3`.
- `ext_emconf.php` depends on `workspaces` `14.3.0-14.99.99`.
- `SeedStyleguidePagesCommand` reads the TYPO3 `workspace` context
  aspect and returns failure for workspace IDs other than `0`.
- Cleanup queries for `tt_content`, Content Blocks collection tables,
  and `sys_file_reference` include `t3ver_wsid = 0` and `t3ver_oid = 0`
  when those columns exist.
- Existing styleguide page lookup is restricted to live page rows when
  versioning columns exist.

## Remaining Caveat

FAL files are not workspace-versioned in TYPO3. Files seeded under
`fileadmin/desiderio-styleguide/` are live files and must not be used as
a confidentiality boundary for staged editorial content.

## Verification

- `Build/Scripts/runTests.sh phpstan`: passed.
- `Build/Scripts/runTests.sh phpunit`: 88 tests passed.
- `Build/Scripts/runTests.sh`: passed.
