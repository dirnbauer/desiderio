#!/usr/bin/env bash

# Local test runner for the Desiderio TYPO3 v14.3 LTS extension.
#
# Mirrors the .github/workflows/ci.yml jobs so contributors can reproduce CI
# results locally on their workstation. Picks the newest Homebrew PHP that
# satisfies the >=8.3 constraint, falls back to whichever `php` is on PATH.
#
# Usage:
#   Build/Scripts/runTests.sh                    # PHPStan + PHPUnit + audit + tailwind
#   Build/Scripts/runTests.sh phpstan            # PHPStan only
#   Build/Scripts/runTests.sh phpunit            # PHPUnit only
#   Build/Scripts/runTests.sh audit              # Content element audit
#   Build/Scripts/runTests.sh validate           # composer validate + audit
#   Build/Scripts/runTests.sh tailwind           # Verify Tailwind bundle is in sync
#   Build/Scripts/runTests.sh -h                 # show help

set -euo pipefail

cd "$(dirname "$0")/../.."
ROOT="$(pwd)"

usage() {
  sed -n '3,20p' "$0"
}

resolve_php() {
  for candidate in /opt/homebrew/Cellar/php/*/bin/php /opt/homebrew/Cellar/php@8.5/*/bin/php /opt/homebrew/Cellar/php@8.4/*/bin/php /opt/homebrew/Cellar/php@8.3/*/bin/php; do
    if [[ -x "$candidate" ]]; then
      version="$($candidate -r 'echo PHP_MAJOR_VERSION."".PHP_MINOR_VERSION;')"
      if [[ "$version" -ge 83 ]]; then
        echo "$candidate"
        return
      fi
    fi
  done
  command -v php
}

PHP="$(resolve_php)"
echo "Using $($PHP -v | head -1)" >&2

TARGET="${1:-all}"

case "$TARGET" in
  -h|--help|help)
    usage
    exit 0
    ;;
  phpstan)
    "$PHP" -d memory_limit=2G "$ROOT/vendor/bin/phpstan" analyse --no-progress
    ;;
  phpunit|unit)
    "$PHP" "$ROOT/vendor/bin/phpunit" --testdox
    ;;
  audit)
    "$PHP" "$ROOT/scripts/audit-content-elements.php" | "$PHP" -r '$d=json_decode(stream_get_contents(STDIN), true); print_r($d["summary"] ?? []);'
    ;;
  validate)
    composer validate --strict --no-check-publish
    composer audit --no-dev --abandoned=fail
    ;;
  tailwind|css)
    "$ROOT/Build/Scripts/check-tailwind-built.sh"
    ;;
  all|"")
    "$PHP" -d memory_limit=2G "$ROOT/vendor/bin/phpstan" analyse --no-progress
    "$PHP" "$ROOT/vendor/bin/phpunit" --testdox
    "$PHP" "$ROOT/scripts/audit-content-elements.php" > /tmp/desiderio-audit.json
    "$PHP" -r '$d=json_decode(file_get_contents("/tmp/desiderio-audit.json"), true); foreach (($d["summary"] ?? []) as $k => $v) { if ($v > 0) printf("%s: %d\n", $k, $v); }'
    "$ROOT/Build/Scripts/check-tailwind-built.sh"
    ;;
  *)
    echo "Unknown target: $TARGET" >&2
    usage
    exit 1
    ;;
esac
