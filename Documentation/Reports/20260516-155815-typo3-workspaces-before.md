# TYPO3 Workspaces Report - Before

Timestamp: 2026-05-16 15:58:15 Europe/Vienna
Skill: typo3-workspaces.mdc

## Findings

- `typo3/cms-workspaces` was a hard dependency.
- The seeder refused non-live workspace contexts.
- Destructive cleanup queries still removed restrictions and did not add
  explicit live workspace predicates.

## Planned Changes

- Keep the non-live workspace refusal.
- Add explicit live workspace predicates to cleanup lookups and deletes.
- Document FAL files as non-versioned.
