# Screenshots of the feature page

Makes the pictures of the feature page (`/funktionen/`, `/en/features/`, pattern `bike-maintainer/feature-tour`) in
German and English and puts them as WebP into `wp-content/themes/bike-maintainer/assets/screenshots/de|en/`. Run it
again after visible changes to the app, then commit the changed `.webp` files.

All pictures show example vehicles from `seed.mjs` (a motorcycle with target values and valve clearance, a family car,
an e-bike), with dates relative to today, so the "due" states stay realistic.

## Requirements

- The local stack `bike-maintainer-deploy/dev` is running (`./build.sh && docker compose up -d`, app on :8100). The
  screenshots are taken with its initial admin from `bike-maintainer-deploy/dev/.env` (or `BM_SHOT_EMAIL` /
  `BM_SHOT_PASSWORD`). **Every run deletes all vehicles of that account** and imports the examples.
- Node.js 22+ with Playwright's Chromium (`npm install && npx playwright install chromium` once in this folder),
  ImageMagick 7 (`magick`), `pdftoppm` (poppler-utils), Docker.
- For the app picture (optional): an Android emulator **without Google Play** (system image `default` or
  `google_apis`, needed for root to switch the system language), started and visible to `adb`
  (`~/Android/Sdk/platform-tools/adb` or `ADB`), with the app installed (APK from the
  [app releases](https://github.com/stargazer74/bike-maintainer-app/releases)). Without an emulator the app picture is
  skipped and the existing one stays.

## Usage

```bash
./run.sh                    # everything
./run.sh dashboard history  # only these shots
```

What happens:
1. `shoot.mjs` signs in per language, sets the account language, replaces the vehicles with the examples and takes the
   web shots with Playwright (1280 px wide); report and worksheet are rendered from the PDFs of the API.
2. For `household`, `run.sh` starts a short-lived stack in household mode (`household.compose.yaml`, images of the dev
   stack, database in tmpfs, port 8110) and removes it afterwards.
3. `app.py` drives the app in the emulator with adb/uiautomator (server `http://10.0.2.2:8100`, demo status bar) and
   `run.sh` puts both phone screens side by side into `app.webp`.
4. Everything is converted to WebP (max. 1280 px wide).

The backend allows 10 logins per 15 minutes and IP; a full run needs 6. If `shoot.mjs` reports the rate limit, wait
15 minutes.
