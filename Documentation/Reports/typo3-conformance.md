# TYPO3 Conformance Audit

Date: 2026-05-16
Target: TYPO3 14.3 LTS only
Status: Green

## Summary

Desiderio is now aligned to TYPO3 14.3 only across Composer,
`ext_emconf.php`, documentation, and tests. Runtime dependencies are on
the latest compatible 14.3 patch line and workspaces remain mandatory.

## Current Controls

- `composer.json` requires TYPO3 core/fluid/workspaces `^14.3`.
- `ext_emconf.php` depends on TYPO3/workspaces `14.3.0-14.99.99`.
- PHPStan runs at `level: max` with `saschaegerer/phpstan-typo3`,
  strict rules, and PHPUnit rules.
- Services use constructor injection and `Services.yaml` autowiring.
- No TYPO3 13 compatibility branch remains in runtime constraints.

## Changes In This Pass

- Updated Composer lock to TYPO3 14.3.1.
- Aligned extension version metadata to `2.2.0`.
- Verified workspace-safe cleanup in the styleguide seeder and refreshed
  the docs/reports around it.

## Verification

- PHPStan max: passed.
- PHPUnit: passed.
- Full local gate: passed.
