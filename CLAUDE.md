# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WordPress website of the Bike Maintainer **portal** (`bike-maintainer.de`): landing page, later articles and a forum
(Asgaros Forum). The app itself (backend + frontend) is separate; the website only links to it. Decisions are in
Epic stargazer74/bike-maintainer-backend#48. Note the naming: "portal" is also the operating mode of the app
(`BM_MODE=portal`); this repo is only the website.

German-only UI for now; text in templates and patterns is German. Code, comments and commit messages are English.

## What lives where

- `wp-content/themes/bike-maintainer/` – block child theme of Twenty Twenty-Five (`theme.json`, templates, parts,
  patterns). No build toolchain, no Tailwind, no page builder.
- `wp-content/mu-plugins/bm-*.php` – our must-use plugins. Only `bm-*.php` files are ours; the entrypoint replaces
  exactly these in the volume.
- Must-use plugins: `bm-updates.php` (auto-updates; our theme excluded from wordpress.org update checks),
  `bm-hardening.php` (XML-RPC off, no app passwords, no user enumeration for guests, no version, no file editor,
  per-IP login limit 10/15 min), `bm-smtp.php` (SMTP from `BM_SMTP_*`; no host = no mail). The client IP comes from
  Apache `mod_remoteip` (X-Forwarded-For from private networks only).
- `docker-entrypoint-bm.sh` – runs the official `docker-ensure-installed.sh`, then syncs theme and `bm-*.php` from
  `/usr/src/bm` into `/var/www/html/wp-content`, then hands over to `docker-entrypoint.sh`.
- `scripts/setup.sh` – idempotent WP-CLI setup (`bm-setup` in the image). Plugins to install go into `PLUGINS`.
- Content (pages, posts, forum, uploads) lives in the database/volume, never in the repo.

## Update model (decided 2026-11-02, "variant B")

WordPress updates core, plugins and the parent theme itself at runtime. In operation the whole `/var/www/html` is a
volume (the official image copies core only on the first start). The image only provides PHP/Apache and our own
code. Consequences:
- Never bake third-party plugins into the image; add them to `PLUGINS` in `setup.sh` instead.
- Our code must work across WordPress minor/major updates (`Requires at least` in `style.css`).
- Trivy scans the image, not runtime-installed plugins.

## Rules

- No external requests from the browser: fonts are local in the theme (no Google Fonts), icons inline SVG, no CDNs.
- PHP follows the WordPress Coding Standards (`phpcs.xml.dist`, text domain `bike-maintainer`); escape output, check
  capabilities and nonces.
- Image is `linux/amd64` only – the website never runs on the Raspberry Pi.
- Texts must match the real state of the app (no prices/subscription, no features that do not exist yet).

## Commands

```bash
cp .env.example .env && docker compose up -d --build     # http://localhost:8095, Mailpit :8025
docker compose exec -u www-data wordpress bm-setup        # first setup (idempotent)
docker compose exec -u www-data wordpress wp <command>    # WP-CLI
shellcheck docker-entrypoint-bm.sh scripts/*.sh
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD":/app -w /app -e COMPOSER_HOME=/tmp/composer composer:2 \
  sh -c 'composer install && composer lint && vendor/bin/phpcs'
```

## Releases

Version in the theme header (`style.css`, `Version: X.Y.Z-SNAPSHOT` on main). Tag `vX.Y.Z` (set by the user) must
match; CI publishes `ghcr.io/stargazer74/bike-maintainer-portal` as X.Y.Z/X.Y/X/latest. Independent of the app
version.
