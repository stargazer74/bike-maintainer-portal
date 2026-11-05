# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WordPress website of the Bike Maintainer **portal** (`bike-maintainer.de`): landing page, later articles and a forum
(Asgaros Forum). The app itself (backend + frontend) is separate; the website only links to it. Decisions are in
Epic stargazer74/bike-maintainer-backend#48. Note the naming: "portal" is also the operating mode of the app
(`BM_MODE=portal`); this repo is only the website.

German and English (Polylang): German at `/`, English at `/en/`, switcher `DE | EN` in the header. Theme texts are
English source strings (`__()`/`esc_html_e()` with text domain `bike-maintainer`) translated in
`wp-content/themes/bike-maintainer/languages/de_DE.po`; after changing texts run `scripts/i18n.sh` and fill in the
empty `msgstr` entries – CI fails on missing German translations. Only `.pot`/`.po` are committed; `.mo`/`.l10n.php` are
compiled in the image (and by `scripts/i18n.sh` for development). Polylang sets no cookie and its own browser
detection is off (`bm-polylang.php`, privacy); instead `bm-polylang.php` redirects visitors from outside on `/` to `/en/`
if their Accept-Language prefers English – never when the referrer is our own host, so a manual switch to German
sticks without a cookie. Code, comments and commit
messages are English.

## What lives where

- `wp-content/themes/bike-maintainer/` – block child theme of Twenty Twenty-Five (`theme.json`, templates, parts,
  patterns). No build toolchain, no Tailwind, no page builder. Palette reuses TT5's slugs (`base` = background,
  `contrast` = text, `accent-1` = orange, `accent-2` = card, `accent-4` = muted text, `accent-6` = border) so inherited
  templates match. Style variations live in `styles/*.json`. Buttons use dark text on orange (white fails WCAG AA).
  App links are written `#bm-app` and replaced with `BM_APP_URL` in `functions.php`. Pattern markup must match what
  the block editor would save, or the editor flags the block as invalid – check new patterns in the editor.
- `wp-content/mu-plugins/bm-*.php` – our must-use plugins. Only `bm-*.php` files are ours; the entrypoint replaces
  exactly these in the volume.
- Patterns link within the site via `bike_maintainer_home_url()` / `bike_maintainer_legal_url()` (language-aware);
  shortcode blocks inside patterns are rendered by the `render_block_core/shortcode` filter (templates would leave them
  raw otherwise).
- Must-use plugins: `bm-altcha.php` (self-hosted ALTCHA on registration and lost password, needs `BM_ALTCHA_HMAC_KEY`;
  widget file vendored in `bm-altcha/`), `bm-privacy.php` (local avatars, never Gravatar), `bm-polylang.php` (no Polylang cookie), `bm-updates.php` (auto-updates; our theme excluded from wordpress.org update checks),
  `bm-hardening.php` (XML-RPC off, no app passwords, no user enumeration for guests, no version, no file editor,
  per-IP login limit 10/15 min), `bm-smtp.php` (SMTP from `BM_SMTP_*`; no host = no mail). The client IP comes from
  Apache `mod_remoteip` (X-Forwarded-For from private networks only).
- `docker-entrypoint-bm.sh` – runs the official `docker-ensure-installed.sh`, then syncs theme and `bm-*.php` from
  `/usr/src/bm` into `/var/www/html/wp-content`, then hands over to `docker-entrypoint.sh`.
- Forum: Asgaros Forum on `/forum/` (de) and `/en/community/` (en), set up by `scripts/setup-forum.php`. Asgaros
  crashes when WP-CLI loads it right after activation (it expects a global instance); `setup.sh` therefore activates
  plugins with `--skip-plugins` and makes a web request after each activation. Asgaros caches date formats before
  Polylang knows the language – `functions.php` re-reads them on `wp`.
- `scripts/setup.sh` – idempotent WP-CLI setup (`bm-setup` in the image). Plugins to install go into `PLUGINS`.
  Polylang (languages, options, English start page linked to the German one) is set up by
  `scripts/setup-languages.php` via `wp eval-file`, as the free Polylang has no WP-CLI commands.
- Legal pages (`/impressum/`, `/datenschutz/`, `/en/legal-notice/`, `/en/privacy/`, linked by the footer and the
  app): `scripts/setup-legal.php` creates missing ones as drafts with a placeholder, links the languages and sets
  WordPress' privacy page. It never touches existing pages – their text is the operator's.
- Content (pages, posts, forum, uploads) lives in the database/volume, never in the repo.

## Update model (decided 2026-11-02, "variant B")

WordPress updates core, plugins and the parent theme itself at runtime. In operation the whole `/var/www/html` is a
volume (the official image copies core only on the first start). The image only provides PHP/Apache and our own
code. Consequences:
- Never bake third-party plugins into the image; add them to `PLUGINS` in `setup.sh` instead.
- Our code must work across WordPress minor/major updates (`Requires at least` in `style.css`).
- Trivy scans the image, not runtime-installed plugins.

## Rules

- Development setups run with `WP_DEVELOPMENT_MODE=theme` (via `WORDPRESS_CONFIG_EXTRA`). Without it WordPress caches
  the pattern list per theme *version* in a site transient; the version stays `X.Y.Z-SNAPSHOT` during development, so
  new patterns would not appear. Stale cache: `wp transient delete --all --network`. Releases change the version, so
  operation needs no development mode.

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
