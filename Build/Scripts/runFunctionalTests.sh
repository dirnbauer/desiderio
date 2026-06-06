#!/usr/bin/env bash

set -euo pipefail

cd "$(dirname "$0")/../.."
ROOT="$(pwd)"

export TYPO3_CONTEXT=Testing
export TYPO3_PATH_ROOT="$ROOT/Build/Functional/document-root"
export typo3DatabaseDriver=pdo_sqlite
export typo3DatabaseName=desiderio_functional

PHP="${PHP:-php}"
"$PHP" "$ROOT/vendor/bin/phpunit" -c "$ROOT/Build/FunctionalTests.xml" --testdox "$@"
