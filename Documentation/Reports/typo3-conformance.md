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
- Content Block media templates render FAL references through TYPO3 Fluid
  image APIs (`<f:image>` / `f:uri.image()`), keeping Visual Editor decoration
  and avoiding direct `FileReference` string conversion.
- Top-level Content Blocks `Collection` fields use `prefixField: true` so
  common field identifiers do not share one `tt_content` TCA column.
- Collection child tables stay per collection by default; explicit table reuse
  is limited to identical stable child schemas and unambiguous matching.

## Changes In This Pass

- Updated Composer lock to TYPO3 14.3.1.
- Aligned extension version metadata to `2.2.0`.
- Verified workspace-safe cleanup in the styleguide seeder and refreshed
  the docs/reports around it.
- Replaced the obsolete Content Blocks TCA workaround with upstream-supported
  per-field Collection prefixing and documented the table-reuse tradeoff.

## Verification

- PHPStan max: passed.
- PHPUnit: passed.
- Full local gate: passed.
