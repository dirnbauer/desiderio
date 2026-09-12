#!/usr/bin/env bash
# Run the same checks locally and in CI. The default suite runs every gate.
# Usage: Build/Scripts/runTests.sh [-s SUITE] [-p 8.4|8.5] [SUITE]
# Suites: all, phpstan, unit (or phpunit), functional, audit, validate, assets
set -euo pipefail
cd "$(dirname "$0")/../.."

# Use the project's PHP and Node versions when DDEV is available locally.
if [[ -z "${IS_DDEV_PROJECT:-}" && -f .ddev/config.yaml ]] && command -v ddev >/dev/null 2>&1; then
    exec ddev exec Build/Scripts/runTests.sh "$@"
fi

SUITE=all
PHP="${PHP:-php}"
while [[ $# -gt 0 ]]; do
    case "$1" in
        -s) SUITE="${2:?Missing suite after -s}"; shift 2 ;;
        -p) PHP="php${2:?Missing PHP version after -p}"; shift 2 ;;
        -h|--help|help) sed -n '2,4p' "$0"; exit 0 ;;
        -*) echo "Unknown option: $1" >&2; exit 2 ;;
        *) SUITE="$1"; shift ;;
    esac
done
command -v "$PHP" >/dev/null || { echo "PHP executable unavailable: $PHP" >&2; exit 2; }
"$PHP" -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' || { echo 'PHP 8.4 or newer is required.' >&2; exit 2; }
export PHP
echo "Using $("$PHP" -r 'echo PHP_VERSION;')"

SUITES=("$SUITE")
[[ "$SUITE" != all ]] || SUITES=(phpstan unit functional validate assets)
for suite in "${SUITES[@]}"; do
    case "$suite" in
        phpstan) "$PHP" -d memory_limit=2G vendor/bin/phpstan analyse --no-progress ;;
        phpunit|unit) "$PHP" vendor/bin/phpunit ;;
        functional) Build/Scripts/runFunctionalTests.sh ;;
        audit) "$PHP" vendor/bin/phpunit --filter ContentElementAuditTest ;;
        validate)
            "$PHP" "$(command -v composer)" validate --strict --no-check-publish
            "$PHP" "$(command -v composer)" audit --abandoned=fail
            ;;
        assets|tailwind|css) Build/Scripts/check-generated-assets.sh ;;
        *) echo "Unknown suite: $suite" >&2; exit 2 ;;
    esac
done
