#!/usr/bin/env bash

# One-time setup for Desiderio's git hooks.
#
# Points git at Build/Hooks/ via core.hooksPath so contributors get the
# pre-commit Tailwind-staleness check without symlinking files into
# .git/hooks/ manually.
#
# Re-runnable; idempotent.

set -euo pipefail

cd "$(dirname "$0")/../.."

if ! git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  echo "ERROR: not inside a git working tree." >&2
  exit 1
fi

git config core.hooksPath Build/Hooks

# Ensure committed hook scripts are executable on systems that ignore
# the +x bit on checkout.
chmod +x Build/Hooks/* 2>/dev/null || true

echo "git hooks enabled. core.hooksPath = $(git config --get core.hooksPath)"
echo "Active hooks:"
ls -1 Build/Hooks | sed 's/^/  - /'
