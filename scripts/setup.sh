#!/usr/bin/env bash
# One-time setup of a fresh website with WP-CLI. Safe to run again: every step checks its state first.
#
#   docker compose exec -u www-data wordpress bm-setup
#
# Needs BM_SITE_URL, BM_ADMIN_USER, BM_ADMIN_EMAIL and BM_ADMIN_PASSWORD for the first installation;
# BM_SITE_TITLE is optional.
set -Eeuo pipefail

# Plugins installed from wordpress.org on setup; afterwards WordPress keeps them up to date itself.
PLUGINS=(
)

cd /var/www/html

if ! wp core is-installed 2>/dev/null; then
    : "${BM_SITE_URL:?BM_SITE_URL is required for the first installation}"
    : "${BM_ADMIN_USER:?BM_ADMIN_USER is required for the first installation}"
    : "${BM_ADMIN_EMAIL:?BM_ADMIN_EMAIL is required for the first installation}"
    : "${BM_ADMIN_PASSWORD:?BM_ADMIN_PASSWORD is required for the first installation}"
    wp core install \
        --url="$BM_SITE_URL" \
        --title="${BM_SITE_TITLE:-Bike Maintainer}" \
        --admin_user="$BM_ADMIN_USER" \
        --admin_email="$BM_ADMIN_EMAIL" \
        --admin_password="$BM_ADMIN_PASSWORD" \
        --skip-email
fi

# German only for now (Epic #48); English may follow later.
wp language core install de_DE --activate

# The .htaccess with the rewrite rules comes with the image; only the structure is set here.
wp rewrite structure '/%postname%/'

wp theme activate bike-maintainer

# Samples shipped with WordPress that we do not use.
wp plugin delete hello akismet 2>/dev/null || true

for plugin in "${PLUGINS[@]}"; do
    if ! wp plugin is-installed "$plugin"; then
        wp plugin install "$plugin"
    fi
    wp plugin activate "$plugin"
done
if [ "${#PLUGINS[@]}" -gt 0 ]; then
    wp language plugin install de_DE "${PLUGINS[@]}"
fi

echo "Setup complete: $(wp option get siteurl)"
