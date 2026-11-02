#!/usr/bin/env bash
# Starts the website. Before Apache starts:
#  1. The official script prepares /var/www/html (first start: copy WordPress, create wp-config.php from the
#     WORDPRESS_* variables). Later starts leave everything alone, so updates WordPress made to itself are kept.
#  2. Our own code from the image replaces its copy in the volume: the child theme and our must-use plugins (bm-*.php).
#     Nothing else in wp-content is touched – plugins, uploads and the parent theme belong to WordPress.
set -Eeuo pipefail

if [[ "${1-}" == apache2* ]]; then
    docker-ensure-installed.sh

    if [ "${BM_SYNC_OWN_CODE:-true}" = 'true' ]; then
        content=/var/www/html/wp-content
        theme="$content/themes/bike-maintainer"
        mu="$content/mu-plugins"

        # Never delete a bind mount (development: the repository is mounted there).
        if mountpoint -q "$theme" || mountpoint -q "$mu"; then
            echo >&2 "bike-maintainer: theme or mu-plugins are mounted, not syncing own code"
        else
            mkdir -p "$content/themes" "$mu"
            rm -rf "$theme"
            cp -a /usr/src/bm/themes/bike-maintainer "$theme"
            find "$mu" -maxdepth 1 -name 'bm-*.php' -delete
            find /usr/src/bm/mu-plugins -maxdepth 1 -name 'bm-*.php' -exec cp -a {} "$mu"/ \;
            chown -R www-data:www-data "$theme" "$mu"
            echo >&2 "bike-maintainer: synced child theme and must-use plugins"
        fi
    fi
fi

exec docker-entrypoint.sh "$@"
