# TYPO3 Extension Upgrade Report - Before

Timestamp: 2026-05-16 15:58:15 Europe/Vienna
Skill: typo3-extension-upgrade

## Findings

- `composer.json` and `ext_emconf.php` already targeted TYPO3 14.3 only.
- `typo3/cms-workspaces` was present as a hard runtime dependency.
- Installed TYPO3 packages were still on 14.3.0 while 14.3.1 was available.
- `ext_emconf.php` still declared extension version `2.1.0` while
  `CHANGELOG.md` documented `2.2.0`.

## Planned Changes

- Refresh TYPO3 14.3 runtime packages to the latest patch line.
- Keep v13 support dropped in Composer and `ext_emconf.php`.
- Align the extension metadata with the current changelog release.
