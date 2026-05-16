#!/usr/bin/env bash

# Verify Resources/Public/Css/desiderio-tailwind.css is in sync with the
# Tailwind source + @source directives in Resources/Private/Tailwind/.
#
# Run `npm run build:css`, then compare the rebuilt bundle against what's
# tracked in git. Fail loudly when they differ — that means a contributor
# edited a template (Components, Solr, Templates, Extensions, FluidStyledContent,
# ContentBlocks, Public/Js) that introduced new utility classes but forgot
# to rebuild and stage the bundle.
#
# Designed to be portable: pre-commit hook + runTests.sh + GitHub Actions
# all call this same script.
#
# Exit codes:
#   0  bundle is in sync with sources
#   1  bundle is stale; contributor must run `npm run build:css` and stage the result
#   2  prerequisite missing (node_modules, npm, etc.)

set -euo pipefail

cd "$(dirname "$0")/../.."
ROOT="$(pwd)"
TARGET="Resources/Public/Css/desiderio-tailwind.css"
TARGET_PATH="$ROOT/$TARGET"

if [[ ! -f "$TARGET_PATH" ]]; then
  echo "ERROR: $TARGET not found." >&2
  exit 2
fi

if ! command -v npm >/dev/null 2>&1; then
  echo "ERROR: npm not found on PATH; cannot rebuild Tailwind bundle." >&2
  echo "  Install Node.js (>=20) and run \`npm install\` once before committing CSS-affecting changes." >&2
  exit 2
fi

if [[ ! -d "$ROOT/node_modules" ]]; then
  echo "node_modules missing; running \`npm ci\` (one-time)..." >&2
  (cd "$ROOT" && npm ci --silent) >&2
fi

BEFORE_HASH="$(shasum -a 256 "$TARGET_PATH" | awk '{print $1}')"

# Rebuild quietly. Capture stderr only on failure.
BUILD_LOG="$(mktemp -t desiderio-tailwind-build.XXXXXX)"
trap 'rm -f "$BUILD_LOG"' EXIT

if ! (cd "$ROOT" && npm run build:css --silent >"$BUILD_LOG" 2>&1); then
  echo "ERROR: \`npm run build:css\` failed." >&2
  cat "$BUILD_LOG" >&2
  exit 1
fi

AFTER_HASH="$(shasum -a 256 "$TARGET_PATH" | awk '{print $1}')"

if [[ "$BEFORE_HASH" == "$AFTER_HASH" ]]; then
  echo "OK: $TARGET is in sync with Tailwind sources."
  exit 0
fi

cat >&2 <<EOF

ERROR: $TARGET is stale.
  Tailwind sources or templates changed but the bundle was not rebuilt.
  The pushed CSS is missing utility classes that the new templates rely on,
  which causes layout/card/spacing/font styles to silently disappear in the frontend.

  Fix:
    npm run build:css
    git add $TARGET
    # then re-commit

  (This script just regenerated the bundle locally; stage it to resolve.)
EOF
exit 1
