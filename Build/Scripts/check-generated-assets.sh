#!/usr/bin/env bash

# Verify every committed asset under Resources/Public is what the generators
# produce from the current sources: the shadcn presets and preset samples, the
# Tailwind bundle (which depends on the utility classes used by templates,
# components and Content Blocks), the concatenated feature CSS, the syntax
# highlighters and the icon fonts.
#
# Runs the same `npm run build` a contributor runs, then fails when the
# working tree differs — that difference is exactly the stale asset.
#
# Exit codes:
#   0  every generated asset is current
#   1  an asset is stale; run `npm run build` and stage the result
#   2  prerequisite missing (npm, node_modules, php)

set -euo pipefail

cd "$(dirname "$0")/../.."
ROOT="$(pwd)"

command -v npm >/dev/null 2>&1 || { echo "ERROR: npm not found on PATH." >&2; exit 2; }
command -v php >/dev/null 2>&1 || { echo "ERROR: php not found on PATH (the preset generators are PHP)." >&2; exit 2; }

if [[ ! -d "$ROOT/node_modules" ]]; then
  echo "node_modules missing; running \`npm ci\` (one-time)..." >&2
  npm ci --silent >&2
fi

BUILD_LOG="$(mktemp -t desiderio-build.XXXXXX)"
trap 'rm -f "$BUILD_LOG"' EXIT

if ! npm run build --silent >"$BUILD_LOG" 2>&1; then
  echo "ERROR: \`npm run build\` failed." >&2
  cat "$BUILD_LOG" >&2
  exit 1
fi

if git diff --exit-code -- Resources/Public; then
  echo "OK: generated assets under Resources/Public are current."
  exit 0
fi

cat >&2 <<'EOF'

ERROR: generated assets under Resources/Public are stale.
  Sources changed but the committed output was not rebuilt, so the published
  CSS/JS is missing what the current templates rely on.

  Fix:
    npm run build
    git add Resources/Public
    # then re-commit
EOF
exit 1
