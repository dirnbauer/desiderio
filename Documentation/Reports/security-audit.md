# Security Audit

Date: 2026-05-16
Scope: PHP/TYPO3 extension, Composer dependencies, repo TODO markers
Status: Green

## Summary

The generic security pass found no dependency advisories and no new
source TODO/FIXME markers. The obsolete Content Blocks patch metadata has
been removed after replacing the workaround with per-field Collection
prefixing. Collection table reuse remains an explicit modeling decision rather
than an automatic schema compaction step.

## Checks

- Composer dependency advisories: clean.
- Abandoned package policy: `fail`, clean.
- TYPO3 removed API scan: no `HashService` or Extbase magic finder usage
  in extension source.
- Service lookup scan: no new service `GeneralUtility::makeInstance()`
  usage was introduced.
- Workspace destructive query risk: fixed by live-row predicates.
- Collection table policy: default generated per-collection tables are kept for
  clear ownership; reuse is reserved for intentionally identical child models
  and mainly reduces schema noise.

## Verification

- `composer validate --strict --no-check-publish`: passed.
- `composer audit --no-dev --abandoned=fail`: passed.
- `Build/Scripts/runTests.sh`: passed.
