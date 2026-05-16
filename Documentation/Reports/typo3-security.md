# TYPO3 Security Audit

Date: 2026-05-16
Target: TYPO3 14.3 LTS only
Status: Green

## Summary

No Composer security advisories were reported. The extension keeps the
styleguide seeder behind Production and workspace guards, and the docs
now call out TYPO3's non-versioned FAL behavior explicitly.

## Current Controls

- `composer audit --no-dev --abandoned=fail` passes.
- Composer `secure-http` is enabled.
- The seeder refuses Production context unless `--allow-production` is
  passed.
- The seeder refuses offline workspaces.
- Destructive seeder cleanup is scoped to live workspace rows.
- CSP nonce handling remains in `FixtureJsonViewHelper`.

## Remaining Caveat

TYPO3 FAL files are not workspace-versioned. Seeded styleguide files are
public live files and must not be used for confidential staged content.

## Verification

- Composer audit: passed.
- Full local gate: passed.
