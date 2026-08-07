#!/usr/bin/env sh
# Boots the disposable QA app from nothing to a seeded element library:
#   composer install -> TYPO3 setup (SQLite, site "main" on port 8080)
#   -> extension:setup -> desiderio:library:seed.
# Idempotent: an existing installation is kept, the seed upserts.
# Build the Vite manifest afterwards with:  (cd Build/CiApp && npm ci && npm run build)
# Then serve with:  TYPO3_CONTEXT=Production php -S 127.0.0.1:8080 -t Build/CiApp/public
set -eu
cd "$(dirname "$0")"

# A QA sandbox: Development context (the seeder refuses Production, and the
# server should show real errors when a render breaks).
export TYPO3_CONTEXT=Development

# Content Blocks TCA compilation for 244 elements outgrows a 128M default.
TYPO3="php -d memory_limit=768M vendor/bin/typo3"

composer install --no-interaction --no-progress

if [ ! -f config/system/settings.php ]; then
    TYPO3_DB_DRIVER=sqlite \
    TYPO3_SETUP_ADMIN_USERNAME=admin \
    TYPO3_SETUP_ADMIN_PASSWORD='Qa!Harness2026' \
    TYPO3_SETUP_ADMIN_EMAIL=qa@example.invalid \
    TYPO3_PROJECT_NAME='Desiderio QA' \
    TYPO3_SERVER_TYPE=other \
    TYPO3_SETUP_CREATE_SITE='http://127.0.0.1:8080/' \
    $TYPO3 setup --force --no-interaction
fi

# The site must load desiderio's TypoScript (preview page type, element
# rendering). Setup wrote a bare FSC site; replace it with the known-good
# config for this disposable app. Classic content rendering is owned by
# Desiderio itself; fluid_styled_content is intentionally not installed.
cat > config/sites/main/config.yaml <<'YAML'
base: 'http://127.0.0.1:8080/'
rootPageId: 1
dependencies:
  - webconsulting/desiderio-content-elements
errorHandling: {  }
routes: {  }
languages:
  -
    title: English
    enabled: true
    languageId: 0
    base: /
    locale: en_US.UTF-8
    navigationTitle: English
    flag: us
YAML

$TYPO3 extension:setup

# The seed prints the folder uid; the picker and the urls command read it from
# the site setting elementLibrary.storagePid, which nothing writes for us.
SEED_OUT=$($TYPO3 desiderio:library:seed --parent=1 --no-warm)
echo "$SEED_OUT"
FOLDER_UID=$(printf '%s' "$SEED_OUT" | sed -n 's/.*Folder uid: \([0-9][0-9]*\).*/\1/p' | head -1)
[ -n "$FOLDER_UID" ] || { echo "could not determine library folder uid" >&2; exit 1; }
printf 'elementLibrary:\n  storagePid: %s\n' "$FOLDER_UID" > config/sites/main/settings.yaml

$TYPO3 cache:flush

echo "Bootstrap done. Serve with:"
echo "  (cd Build/CiApp && npm ci && npm run build)"
echo "  TYPO3_CONTEXT=Production php -d memory_limit=768M -S 127.0.0.1:8080 -t Build/CiApp/public"
echo "Preview URLs: vendor/bin/typo3 desiderio:library:urls --site=main --json"
