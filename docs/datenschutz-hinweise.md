# Hinweise für die Datenschutzerklärung (Website und Forum)

Technische Fakten zur Website `bike-maintainer.de`, als Grundlage für die Datenschutzerklärung (`/datenschutz/`,
`/en/privacy/`). Keine Rechtsberatung – die Formulierung der Erklärung bleibt beim Betreiber. Stand: Forum mit
Asgaros (portal#3). Die App (`app.bike-maintainer.de`) hat eigene Fakten, siehe stargazer74/bike-maintainer-frontend#17.

## Was die Website **nicht** tut

- Keine Anfragen an Dritte aus dem Browser: Schriften (Inter, JetBrains Mono), Icons, Skripte und das ALTCHA-Widget
  liefert der eigene Server aus. Keine Google Fonts, keine CDNs, keine eingebetteten Karten oder Videos.
- Keine Avatare von Gravatar: alle Profilbilder sind ein lokales Platzhalterbild (`bm-privacy.php`).
- Kein Tracking, keine Analyse, keine Werbung.
- Kein Sprach-Cookie: Die Sprache ergibt sich aus der Adresse (`/` Deutsch, `/en/` Englisch). Die Weiterleitung nach
  Browsersprache wertet nur den `Accept-Language`-Header der jeweiligen Anfrage aus und speichert nichts.

## Cookies

Nur technisch notwendige Cookies von WordPress, und nur für angemeldete Mitglieder bzw. auf der Anmeldeseite:
`wordpress_test_cookie` (prüft, ob Cookies möglich sind), `wordpress_logged_in_*` und `wordpress_sec_*` (Anmeldung),
`wp-settings-*` (nur im Admin-Bereich). Kein Cookie für Besucher, die sich nicht anmelden.

## Forum und Konten

| Daten | Zweck | Wo / wie lange |
|---|---|---|
| Benutzername, E-Mail-Adresse, Passwort (nur als Hash) | Konto im Forum; die E-Mail dient der Bestätigung (Link zum Setzen des Passworts), Passwort-Zurücksetzen und optionalen Benachrichtigungen | Datenbank, bis zur Löschung des Kontos |
| Beiträge und Themen, mit Benutzername und Zeitpunkt | Forum | **öffentlich sichtbar**, bis zur Löschung |
| Profil und Mitgliederliste | – | nur für angemeldete Mitglieder sichtbar |
| Reaktionen (Daumen hoch/runter) | Forum | mit Benutzernamen sichtbar |
| Abonnements | Benachrichtigung per E-Mail über neue Beiträge, nur wenn das Mitglied es einschaltet | Datenbank |
| Online-Status, „zuletzt gesehen“, neuestes Mitglied | – | **abgeschaltet** |
| Meldungen von Beiträgen | Moderation | Datenbank, bis zur Bearbeitung |

Rechtsgrundlage für Konto und Beiträge: Nutzungsverhältnis (Art. 6 Abs. 1 lit. b DSGVO). Mails gehen über den
SMTP-Server von IONOS (Absender `noreply@bike-maintainer.de`) – Auftragsverarbeitung.

## Abo (Paddle)

Die Website selbst verarbeitet keine Zahlungsdaten und lädt nichts von Paddle. „Abo abschließen“ führt in die App; dort
öffnet die Bezahlseite `/abo/bezahlen` das Bezahlfenster von Paddle (Paddle.com Market Ltd, London, Vereinigtes
Königreich, als Verkäufer/Merchant of Record). Die Bezahlseite ist die einzige Seite der App, die ein Skript eines
Dritten lädt (Paddle.js von `cdn.paddle.com`, ausdrückliche Ausnahme). Paddle Retain ist ausgeschaltet. Fakten aus dem
Backend (stargazer74/bike-maintainer-backend#155):

| Daten | Empfänger | Zweck |
|---|---|---|
| E-Mail-Adresse, zufällige Kunden-Kennung (keine Konto-ID) | Paddle (vom Backend) | Checkout, Rechnung, Kundenportal |
| Zahlungsdaten, Rechnungsadresse, Land | nur Paddle – gibt die Person im Bezahlfenster selbst ein | Zahlung, Umsatzsteuer |
| IP-Adresse, Browser- und Gerätedaten, Cookies/Local Storage von Paddle.js | Paddle (direkt vom Browser, nur auf der Bezahlseite) | Bezahlfenster, Betrugsprävention |
| Abo-ID, Status, bezahlt bis | unsere Datenbank (App) | Freischaltung |

Keine Nutzungsdaten von uns an Paddle. Rechtsgrundlage: Vertrag (Art. 6 Abs. 1 lit. b DSGVO), für die
Betrugsprävention berechtigtes Interesse (Art. 6 Abs. 1 lit. f DSGVO); Speichern auf dem Gerät durch Paddle.js
unbedingt erforderlich (§ 25 Abs. 2 Nr. 2 TDDDG). Paddle ist eigenständig Verantwortlicher (kein
Auftragsverarbeitungsvertrag). Drittland Vereinigtes Königreich: Angemessenheitsbeschluss; weitere Übermittlungen
durch Paddle (z. B. Zahlungsdienstleister) nach Paddles Datenschutzerklärung. Beim Löschen des Kontos wird das Abo bei
Paddle beendet; Rechnungen bewahrt Paddle nach eigenen Aufbewahrungspflichten auf. Textentwurf für die
Datenschutzerklärung liegt außerhalb des Repos.

## Kündigungsformular

`/kuendigen/` (`bm-cancel.php`): Name, E-Mail-Adresse, Art und Zeitpunkt der Kündigung, optional ein Grund. Die Angaben
gehen per Mail an die Person (Bestätigung) und an den Betreiber und werden **nicht** in der Datenbank gespeichert. Gegen
Missbrauch zählt die Website Absendungen pro IP-Adresse (als Hash, verfällt nach einer Stunde). Rechtsgrundlage:
rechtliche Pflicht (§ 312k BGB, Art. 6 Abs. 1 lit. c DSGVO); die Mails bewahrt der Betreiber als Nachweis auf.

## Schutz vor Missbrauch

- **ALTCHA** bei Registrierung und „Passwort vergessen“: Der Browser löst eine kleine Rechenaufgabe; kein externer
  Dienst, kein Cookie, keine Merkmale des Geräts. Gespeichert wird nur die Signatur einer gelösten Aufgabe (als Hash),
  höchstens 5 Minuten, damit sie nicht zweimal verwendet werden kann.
- **Login-Sperre:** Fehlgeschlagene Anmeldungen werden pro IP-Adresse gezählt (gespeichert als Hash der IP), der Zähler
  verfällt 15 Minuten nach dem letzten Fehlversuch.
- **Server-Logs:** Der Webserver protokolliert Zugriffe mit IP-Adresse (Container-Log); Aufbewahrung je nach Betrieb
  (siehe stargazer74/bike-maintainer-deploy#9).

## Löschung auf Wunsch

Ein Administrator löscht das Konto unter *Benutzer → Löschen*. Asgaros fragt dabei, was mit den Beiträgen geschieht:
**löschen**, einem anderen Konto zuordnen oder behalten. Auf Wunsch der Person: löschen. Datenexport auf Anfrage über
*Werkzeuge → Personenbezogene Daten exportieren* (umfasst die WordPress-Kontodaten; Forenbeiträge sind öffentlich
einsehbar).
