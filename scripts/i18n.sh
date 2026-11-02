#!/usr/bin/env bash
# Updates the theme translations after text changes in patterns, theme.json or styles/:
#   1. languages/bike-maintainer.pot from the code (English source texts)
#   2. merges new and changed texts into languages/de_DE.po (they appear with an empty msgstr – translate them)
#   3. compiles de_DE.mo and de_DE.l10n.php for the development setup (the image compiles its own)
#
# Run on the host with the development setup running (docker compose up -d):  scripts/i18n.sh
set -Eeuo pipefail
cd "$(dirname "$0")/.."

wp() {
    docker compose exec -T -u "$(id -u):$(id -g)" -e HOME=/tmp \
        -w /var/www/html/wp-content/themes/bike-maintainer wordpress wp "$@"
}

wp i18n make-pot . languages/bike-maintainer.pot \
    --slug=bike-maintainer --domain=bike-maintainer --exclude=assets --skip-audit
wp i18n update-po languages/bike-maintainer.pot languages/de_DE.po
wp i18n make-mo languages
wp i18n make-php languages

python3 scripts/check-translations.py
