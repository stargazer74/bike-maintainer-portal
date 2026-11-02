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
