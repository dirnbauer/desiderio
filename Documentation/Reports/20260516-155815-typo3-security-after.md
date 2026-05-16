# TYPO3 Security Report - After

Timestamp: 2026-05-16 15:58:15 Europe/Vienna
Skill: typo3-security

## Changes

- Seeder cleanup now scopes deletes/updates to live workspace rows where
  `t3ver_wsid` and `t3ver_oid` are available.
- Documentation warns that files under
  `fileadmin/desiderio-styleguide/` are not workspace-versioned.
- No new security-sensitive dependencies were introduced.

## Verification

- `composer audit --no-dev --abandoned=fail`: passed.
- Full local gate: passed.
