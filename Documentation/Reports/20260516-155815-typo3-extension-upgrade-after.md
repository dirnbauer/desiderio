# TYPO3 Extension Upgrade Report - After

Timestamp: 2026-05-16 15:58:15 Europe/Vienna
Skill: typo3-extension-upgrade

## Changes

- Updated lockfile packages to TYPO3 14.3.1, including
  `typo3/cms-core`, `typo3/cms-fluid`, `typo3/cms-frontend`,
  `typo3/cms-backend`, `typo3/cms-extbase`, and `typo3/cms-workspaces`.
- Updated direct extension dependencies:
  `friendsoftypo3/content-blocks` 2.3.4 and
  `praetorius/vite-asset-collector` 1.17.0.
- Aligned `ext_emconf.php`, README badges/status, and metadata tests to
  extension version `2.2.0`.

## Verification

- `composer validate --strict --no-check-publish`: passed.
- `composer audit --no-dev --abandoned=fail`: passed.
- `Build/Scripts/runTests.sh`: passed.
