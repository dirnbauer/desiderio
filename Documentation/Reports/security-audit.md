# Security Audit

Date: 2026-05-16
Scope: PHP/TYPO3 extension, Composer dependencies, repo TODO markers
Status: Green

## Summary

The generic security pass found no dependency advisories and no new
source TODO/FIXME markers. The only existing TODO marker remains in
`patches.lock.json` as upstream patch metadata and was not changed.

## Checks

- Composer dependency advisories: clean.
- Abandoned package policy: `fail`, clean.
- TYPO3 removed API scan: no `HashService` or Extbase magic finder usage
  in extension source.
- Service lookup scan: no new service `GeneralUtility::makeInstance()`
  usage was introduced.
- Workspace destructive query risk: fixed by live-row predicates.

## Verification

- `composer validate --strict --no-check-publish`: passed.
- `composer audit --no-dev --abandoned=fail`: passed.
- `Build/Scripts/runTests.sh`: passed.
