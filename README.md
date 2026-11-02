# Bike Maintainer Portal – Website

WordPress-Website des Portals **bike-maintainer.de**: Landing Page, später redaktionelle Artikel und ein Forum. Die
App selbst läuft getrennt unter `app.bike-maintainer.de` (Repos `bike-maintainer-backend` und
`bike-maintainer-frontend`); im Haushaltsmodus gibt es keine Website. Hintergrund und Entscheidungen:
Epic [stargazer74/bike-maintainer-backend#48](https://github.com/stargazer74/bike-maintainer-backend/issues/48).

## Aufbau

Dieses Repository enthält nur, was wir selbst schreiben oder festlegen:

| Pfad | Inhalt |
|---|---|
| `wp-content/themes/bike-maintainer/` | Block-Child-Theme von Twenty Twenty-Five |
| `wp-content/mu-plugins/` | eigene Must-use-Plugins (`bm-*.php`) |
| `Dockerfile`, `docker-entrypoint-bm.sh` | Image `ghcr.io/stargazer74/bike-maintainer-portal` |
| `scripts/setup.sh` | Ersteinrichtung per WP-CLI (im Image als `bm-setup`) |
| `compose.yaml` | lokale Entwicklung: WordPress, MariaDB, Mailpit |
| `composer.json` | nur Entwicklungswerkzeuge (PHPCS, WordPress Coding Standards) |

Seiten, Beiträge, Forum-Inhalte und Uploads liegen in der Datenbank bzw. im Volume, nicht im Repository.

### Updates: WordPress aktualisiert sich selbst

Kern, Plugins und das Eltern-Theme aktualisiert WordPress im Betrieb selbst; sie liegen zusammen mit Uploads und
`wp-config.php` im Volume unter `/var/www/html`. Das offizielle Image kopiert WordPress nur beim **ersten** Start
dorthin. Deshalb muss im Betrieb ganz `/var/www/html` ein Volume sein – sonst gingen die Updates beim Neuerstellen
des Containers verloren.

Das Image bringt neben PHP und Apache nur unseren eigenen Code mit. `docker-entrypoint-bm.sh` ersetzt bei jedem
Start das Child-Theme und unsere `bm-*.php` im Volume durch die Fassung aus dem Image; alles andere in `wp-content`
bleibt unangetastet. Ein neues Image bringt so neue Theme-Versionen ins Portal, ohne installierte Plugins
anzufassen. Mit `BM_SYNC_OWN_CODE=false` (Entwicklung) entfällt der Abgleich; eingebundene Verzeichnisse
(Bind-Mounts) werden nie überschrieben.

Das Image läuft nur auf `linux/amd64`: Die Website gibt es nur im Portal, nie auf dem Raspberry Pi.

## Must-use-Plugins

Immer aktiv, im Admin nicht abschaltbar (`wp-content/mu-plugins/`):

| Datei | Wirkung |
|---|---|
| `bm-updates.php` | automatische Updates für Kern (auch Major-Versionen), Plugins, Themes und Übersetzungen. Unser Child-Theme ist ausgenommen und wird gar nicht erst an wordpress.org gemeldet – ein gleichnamiges fremdes Theme dort könnte es sonst als „Update“ überschreiben. |
| `bm-polylang.php` | Polylang setzt kein Sprach-Cookie; Weiterleitung nach Browsersprache ohne Cookie (siehe „Sprachen“) |
| `bm-hardening.php` | kein XML-RPC (`xmlrpc.php` → 403), keine Anwendungspasswörter, keine Nutzer-Aufzählung für Gäste (`/wp-json/wp/v2/users`, `?author=N`, Nutzer-Sitemap), keine Versionsangabe, kein Datei-Editor im Admin, Login-Sperre pro IP |
| `bm-smtp.php` | Mailversand über SMTP aus der Umgebung (siehe unten); ohne `BM_SMTP_HOST` werden **keine** Mails versendet und Admins sehen einen Hinweis |

### Mailversand

| Variable | Bedeutung |
|---|---|
| `BM_SMTP_HOST` | SMTP-Server, z. B. `smtp.ionos.de`; leer = kein Versand |
| `BM_SMTP_PORT` | Standard `587` (STARTTLS); `465` = TLS; andere Ports (Mailpit `1025`) unverschlüsselt |
| `BM_SMTP_SECURE` | optional: `tls`, `ssl` oder `none` statt der Ableitung aus dem Port |
| `BM_SMTP_USER`, `BM_SMTP_PASSWORD` | Zugang; ohne Nutzer keine Anmeldung am Server |
| `BM_SMTP_FROM` | Absender, Standard `noreply@bike-maintainer.de` (Name „Bike Maintainer“) |

### Login-Schutz

Der nginx-proxy-manager vor dem Portal begrenzt keine einzelnen Pfade, deshalb sperrt WordPress selbst: nach
**10 fehlgeschlagenen Anmeldungen** einer IP werden weitere Versuche dieser IP abgewiesen, auch mit richtigem
Passwort; jeder weitere Fehlversuch verlängert die Sperre um **15 Minuten** (gleiche Werte wie in der App). XML-RPC und
Anwendungspasswörter sind aus, es bleibt nur das Login-Formular.

Damit WordPress die echte Client-IP sieht, übernimmt Apache (`mod_remoteip`, siehe `Dockerfile`) die Adresse aus
`X-Forwarded-For` – aber nur von Proxys in privaten Netzen (Docker-Netze). Der nginx-proxy-manager setzt den Header
von sich aus.

## Theme „bike-maintainer“

Block-Child-Theme von Twenty Twenty-Five im dunklen Design der App (Entwurf: Landing-Page-Showcase vom 30.10.2026).

| Was | Wo |
|---|---|
| Farben, Schriften, Abstände, Grundstile | `theme.json` – die Farb-Slugs von Twenty Twenty-Five (`base`, `contrast`, `accent-1` …) sind mit unseren Farben belegt, so passen auch geerbte Vorlagen (Blog, Suche, 404) |
| Stil-Varianten (im Editor unter „Stile“) | `styles/*.json`: Karte, Raster mit Glow, Badge, Überzeile, Button „Sekundär“, Icon-Kachel, App-Vorschau, Häkchen-Liste |
| Kopf- und Fußzeile | `parts/` → Patterns `header.php`, `footer.php` |
| Abschnitte der Startseite | `patterns/`: Hero, Funktionen, „Portal oder selbst betreiben“, FAQ; `landing.php` setzt sie zusammen |
| Startseite | `templates/front-page.html`; `bm-setup` legt die Seite „Start“ aus dem Pattern an und setzt sie als Startseite |

- **Links zur App** stehen in Patterns und Inhalten als `#bm-app` und werden beim Ausliefern durch `BM_APP_URL`
  ersetzt (Standard `https://app.bike-maintainer.de`; in der Entwicklung `http://localhost:8100`).
- **Favicon und App-Icons** wie in der App (orange mit Motorrad): `assets/images/favicon.svg`, PNG in 32/192/512 px und
  ein randloses Apple-Touch-Icon (180 px). Sie gelten, solange im Admin kein eigenes Website-Icon gesetzt ist; WordPress
  gibt sie dann auf Website, Admin und Login-Seite aus, `/favicon.ico` leitet auf das 32-px-Icon um.
- **Keine externen Requests:** Schriften (Inter, JetBrains Mono) liegen als WOFF2 in `assets/fonts/`, Icons
  (Lucide) als SVG in `assets/icons/`, keine CDNs.
- **App-Vorschau** (`assets/images/app-preview.webp`) ist ein echter Screenshot der Fahrzeugseite mit Beispieldaten
  (1100 × 870 CSS-Pixel, 1,5-fach). Bei größeren Änderungen an der App neu aufnehmen.
- **Texte** beschreiben den tatsächlichen Stand der App – keine Preise, keine Funktionen, die es noch nicht gibt.
- Lizenzen: Inter und JetBrains Mono SIL Open Font License (`assets/fonts/*-LICENSE.txt`), Lucide ISC
  (`assets/icons/LICENSE.txt`), Logo mit Material Symbol „two_wheeler“ (Apache 2.0).

Geprüft mit Lighthouse (mobil, Dev-Setup): Barrierefreiheit 100, Best Practices 100, Performance 82.

## Sprachen: Deutsch und Englisch

Die Website gibt es auf Deutsch (`/`) und Englisch (`/en/`), mit dem Plugin **Polylang**. In der Kopfzeile schaltet
`DE | EN` um; die Startseite gibt es als „Start“ (de) und „Home“ (en), beide aus dem Landing-Page-Pattern und in
Polylang miteinander verknüpft. `bm-setup` richtet das ein (`scripts/setup-languages.php`).

- **Browsersprache ohne Cookie:** Wer von außen auf die deutsche Startseite `/` kommt (Adresse getippt, Suchmaschine,
  fremder Link) und dessen Browser Englisch vor Deutsch bevorzugt, wird nach `/en/` geleitet. Klicks von unseren
  eigenen Seiten – etwa „DE“ im Umschalter – werden nie umgeleitet; so bleibt eine manuelle Wahl bestehen, ohne dass
  etwas gespeichert wird (`bm-polylang.php`). Polylangs eigene Erkennung und sein Sprach-Cookie sind abgeschaltet.
  Suchmaschinen finden die passende Sprache über `hreflang`.
- **Texte im Theme** sind englische Quelltexte und werden über `wp-content/themes/bike-maintainer/languages/de_DE.po`
  ins Deutsche übersetzt. Nach Textänderungen bei laufendem Dev-Setup:
  ```bash
  scripts/i18n.sh      # Vorlage aus dem Code, neue Texte in de_DE.po, kompilieren, prüfen
  ```
  Neue oder geänderte Texte erscheinen in `de_DE.po` mit leerem `msgstr` – übersetzen und `scripts/i18n.sh` erneut
  ausführen. Die CI schlägt fehl, solange ein Text keine deutsche Übersetzung hat. Im Repository liegen nur `.pot`
  und `.po`; `.mo` und `.l10n.php` erzeugt der Image-Build.
- **Inhalte** (Seiten, Beiträge) übersetzt man im Editor über Polylang: je Sprache eine eigene, verknüpfte Seite.
- **Impressum und Datenschutz:** `/impressum/` und `/datenschutz/` bzw. `/en/legal-notice/` und `/en/privacy/` – die
  Fußzeile verlinkt je nach Sprache dorthin; die Seiten legt der Betreiber an.

## Lokale Entwicklung

```bash
cp .env.example .env            # Passwörter anpassen
docker compose up -d --build
docker compose exec -u www-data wordpress bm-setup
```

- Website: <http://localhost:8095> (Admin: `/wp-admin`, Zugang aus `.env`)
- Mailpit (alle ausgehenden Mails): <http://localhost:8025>

Theme und Must-use-Plugins sind aus dem Repository eingebunden; Änderungen sind ohne Neubau sichtbar. Zurücksetzen:
`docker compose down -v`.

Prüfungen wie in der CI:

```bash
shellcheck docker-entrypoint-bm.sh scripts/*.sh
docker run --rm -u "$(id -u):$(id -g)" -v "$PWD":/app -w /app -e COMPOSER_HOME=/tmp/composer composer:2 \
  sh -c 'composer install && composer lint && vendor/bin/phpcs'
```

## Betrieb

Die Compose-Datei für das Portal liegt in `bike-maintainer-deploy/portal/` und zieht das veröffentlichte Image. Nach
dem ersten Start einmal `bm-setup` ausführen (mit `BM_SITE_URL`, `BM_ADMIN_USER`, `BM_ADMIN_EMAIL`,
`BM_ADMIN_PASSWORD`).

## Release

Eigene Versionsnummer, unabhängig von Backend und Frontend. `main` trägt die kommende Version als
`X.Y.Z-SNAPSHOT` im Theme-Header (`wp-content/themes/bike-maintainer/style.css`). Ein Tag `vX.Y.Z` muss dazu passen;
die CI baut dann das Image und veröffentlicht es als `X.Y.Z`, `X.Y`, `X` und `latest`. Danach in einem PR auf die
nächste `-SNAPSHOT`-Version erhöhen.

## Lizenz

MIT, siehe [LICENSE](LICENSE). Twenty Twenty-Five (Eltern-Theme) und WordPress stehen unter der GPL.
