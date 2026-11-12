#!/usr/bin/env bash
# Takes the screenshots of the feature page: web app in portal mode (dev stack), profile selection in household mode
# (short-lived stack from household.compose.yaml), converts them to WebP and puts them into the theme
# (assets/screenshots/de|en/). App screenshots come from app.py when an emulator is connected. See README.md.
#
# Usage: ./run.sh [shot ...]   e.g. ./run.sh dashboard history   (default: all)
set -Eeuo pipefail

here="$(cd "$(dirname "$0")" && pwd)"
theme="$here/../../wp-content/themes/bike-maintainer/assets/screenshots"
dev_env="$here/../../../bike-maintainer-deploy/dev/.env"

# Account on the dev stack; default: its initial admin from bike-maintainer-deploy/dev/.env.
if [ -z "${BM_SHOT_EMAIL:-}" ] && [ -f "$dev_env" ]; then
    BM_SHOT_EMAIL="$(sed -n 's/^BM_INITIAL_ADMIN_EMAIL=//p' "$dev_env")"
    BM_SHOT_PASSWORD="$(sed -n 's/^BM_INITIAL_ADMIN_PASSWORD=//p' "$dev_env")"
fi
export BM_SHOT_EMAIL BM_SHOT_PASSWORD
export BM_SHOT_URL="${BM_SHOT_URL:-http://localhost:8100}"
export BM_SHOT_HOUSEHOLD_PORT="${BM_SHOT_HOUSEHOLD_PORT:-8110}"
export BM_SHOT_HOUSEHOLD_URL="http://localhost:$BM_SHOT_HOUSEHOLD_PORT"
BM_SHOT_OUT="$(mktemp -d)"
export BM_SHOT_OUT

curl -fsS -o /dev/null "$BM_SHOT_URL/api/v1/config" \
    || { echo "No app at $BM_SHOT_URL – start bike-maintainer-deploy/dev first." >&2; exit 1; }

household=false
if [ "$#" -eq 0 ] || [[ " $* " == *" household "* ]]; then
    household=true
fi
cleanup() {
    rm -rf "$BM_SHOT_OUT"
    if $household; then
        docker compose -f "$here/household.compose.yaml" down --volumes --remove-orphans >/dev/null 2>&1 || true
    fi
}
trap cleanup EXIT

if $household; then
    echo "== Household stack"
    docker compose -f "$here/household.compose.yaml" up -d --quiet-pull
    for _ in $(seq 1 60); do
        curl -fsS -o /dev/null "$BM_SHOT_HOUSEHOLD_URL/api/v1/config" 2>/dev/null && break
        sleep 3
    done
fi

echo "== Screenshots"
(cd "$here" && [ -d node_modules ] || npm install --no-audit --no-fund --silent)
(cd "$here" && node shoot.mjs "$@")

# App screenshots from a running Android emulator (app.py), when one is connected and the app is wanted.
adb="${ADB:-$HOME/Android/Sdk/platform-tools/adb}"
if { [ "$#" -eq 0 ] || [[ " $* " == *" app "* ]]; }; then
    if [ -x "$adb" ] && "$adb" get-state >/dev/null 2>&1; then
        echo "== App (emulator)"
        python3 "$here/app.py" "$BM_SHOT_OUT"
        # Both phone screens side by side with rounded corners, one picture per language.
        for dir in "$BM_SHOT_OUT"/*/; do
            [ -f "$dir/app-vehicles.png" ] || continue
            for name in app-vehicles app-vehicle; do
                magick "$dir/$name.png" -resize 400x "$dir/$name-small.png"
                size="$(magick identify -format '%wx%h' "$dir/$name-small.png")"
                magick "$dir/$name-small.png" \( -size "$size" xc:none -fill white \
                    -draw "roundrectangle 0,0,$(( ${size%x*} - 1 )),$(( ${size#*x} - 1 )),32,32" \) \
                    -compose DstIn -composite "$dir/$name-round.png"
            done
            magick -background none "$dir/app-vehicles-round.png" -size 40x1 xc:none "$dir/app-vehicle-round.png" \
                +append "$dir/app.png"
            rm "$dir"/app-vehicle*.png
        done
    else
        echo "== App: no emulator connected, skipped (see README.md)"
    fi
fi

echo "== WebP"
for png in "$BM_SHOT_OUT"/*/*.png; do
    lang="$(basename "$(dirname "$png")")"
    mkdir -p "$theme/$lang"
    magick "$png" -strip -resize '1280x>' -quality 82 "$theme/$lang/$(basename "${png%.png}").webp"
done
du -ch "$theme"/*/*.webp | tail -1
