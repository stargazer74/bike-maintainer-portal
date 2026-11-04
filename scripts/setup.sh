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
    polylang        # German and English (Epic #48)
    asgaros-forum   # Forum (/forum/, /en/community/)
    antispam-bee    # Spam filter for comments
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
for slug in hello-world sample-page; do
    for type in post page; do
        id="$(wp post list --post_type="$type" --name="$slug" --field=ID)"
        [ -z "$id" ] || wp post delete "$id" --force
    done
done

# Start page built from the landing page pattern of the theme (editable in the block editor afterwards).
start_id="$(wp post list --post_type=page --name=start --post_status=any --field=ID)"
if [ -z "$start_id" ]; then
    start_id="$(wp post create --post_type=page --post_status=publish --post_title='Start' --post_name=start \
        --post_content='<!-- wp:pattern {"slug":"bike-maintainer/landing"} /-->' --porcelain)"
fi
wp option update show_on_front page
wp option update page_on_front "$start_id"

# Languages (Polylang): needs the start page above and the plugins below, so it runs at the end.

for plugin in "${PLUGINS[@]}"; do
    if ! wp plugin is-installed "$plugin"; then
        wp plugin install "$plugin"
    fi
    # Not loaded while being activated: Asgaros Forum expects its instance in a global variable, which does not exist
    # when WP-CLI loads WordPress (activation would end in a fatal error).
    wp plugin activate "$plugin" --skip-plugins="$plugin"
    # Some plugins (Asgaros Forum) finish their setup – tables, first page – on the next web request, and WP-CLI
    # commands fail until then; so make that request right away.
    curl -s -o /dev/null http://localhost/ || true
done
# One plugin per call: "wp language plugin install a b de_DE" would take b as a language.
for plugin in "${PLUGINS[@]}"; do
    wp language plugin install "$plugin" de_DE
done

wp eval-file /usr/src/bm/setup-languages.php
wp eval-file /usr/src/bm/setup-forum.php

echo "Setup complete: $(wp option get siteurl)"
