# syntax=docker/dockerfile:1

# Website of the Bike Maintainer portal: the official WordPress image plus our own code (child theme, must-use
# plugins). WordPress core, plugins and the parent theme update themselves at runtime and live in the volume at
# /var/www/html; the image only provides PHP/Apache and keeps our own code current (see bm-entrypoint.sh).

# WP-CLI is a single PHP archive; take it from the official CLI image instead of downloading it.
FROM wordpress:cli-2 AS wp-cli

FROM wordpress:6-php8.3-apache

# The official image often lags behind Debian's security fixes; pull them in at build time.
RUN apt-get update \
    && apt-get upgrade -y --no-install-recommends \
    && rm -rf /var/lib/apt/lists/*

COPY --from=wp-cli /usr/local/bin/wp /usr/local/bin/wp

# Behind the nginx-proxy-manager: take the client IP from X-Forwarded-For, but only from proxies in private networks
# (Docker networks). Without this, WordPress sees the proxy as the client of every request and the login limit
# (bm-hardening.php) would lock out everyone at once.
RUN a2enmod remoteip \
    && printf '%s\n' \
        'RemoteIPHeader X-Forwarded-For' \
        'RemoteIPInternalProxy 10.0.0.0/8 172.16.0.0/12 192.168.0.0/16 127.0.0.0/8' \
        > /etc/apache2/conf-available/remoteip.conf \
    && a2enconf remoteip

# Our own code, copied into the volume on every start.
COPY wp-content/themes/bike-maintainer /usr/src/bm/themes/bike-maintainer
COPY wp-content/mu-plugins /usr/src/bm/mu-plugins
# Compile the theme translations (.mo and the faster .l10n.php); only the .po files are in the repository.
RUN wp i18n make-mo /usr/src/bm/themes/bike-maintainer/languages --allow-root \
    && wp i18n make-php /usr/src/bm/themes/bike-maintainer/languages --allow-root
COPY --chmod=755 docker-entrypoint-bm.sh /usr/local/bin/docker-entrypoint-bm.sh
COPY --chmod=755 scripts/setup.sh /usr/local/bin/bm-setup
COPY scripts/setup-languages.php scripts/setup-forum.php /usr/src/bm/

ENTRYPOINT ["docker-entrypoint-bm.sh"]
CMD ["apache2-foreground"]
