#!/usr/bin/env python3
"""App screenshots of the feature page from an Android emulator (see README.md).

Drives the installed app with adb and uiautomator: sets the server to the dev stack, signs in with the screenshot
account (after shoot.mjs has put the example vehicles there) and saves PNGs per language. The system language is
switched with root rights, so use an emulator image without Google Play ("default" or "google_apis").

Usage: app.py OUT_DIR   (environment: BM_SHOT_EMAIL, BM_SHOT_PASSWORD, optional ADB, BM_SHOT_APP_SERVER)
"""

import os
import re
import subprocess
import sys
import time
import xml.etree.ElementTree as ET

ADB = os.environ.get("ADB", os.path.expanduser("~/Android/Sdk/platform-tools/adb"))
PACKAGE = "de.bikemaintainer.app"
# The emulator reaches the host as 10.0.2.2; the dev stack publishes the app on port 8100.
SERVER = os.environ.get("BM_SHOT_APP_SERVER", "http://10.0.2.2:8100")
LOCALES = {"de": "de-DE", "en": "en-GB"}


def adb(*args, check=True):
    return subprocess.run([ADB, *args], check=check, capture_output=True, text=True).stdout


def nodes():
    adb("shell", "uiautomator", "dump", "/sdcard/bm-ui.xml")
    root = ET.fromstring(adb("exec-out", "cat", "/sdcard/bm-ui.xml"))
    return list(root.iter("node"))


def center(node):
    x1, y1, x2, y2 = map(int, re.findall(r"\d+", node.get("bounds")))
    return (x1 + x2) // 2, (y1 + y2) // 2


def find(pattern, timeout=20):
    """First node whose text or content description matches the regular expression; waits until it appears."""
    regex = re.compile(pattern)
    end = time.time() + timeout
    while time.time() < end:
        for node in nodes():
            if regex.search(node.get("text", "")) or regex.search(node.get("content-desc", "")):
                return node
        time.sleep(1)
    raise SystemExit(f"Not on screen: {pattern}")


def tap(pattern, timeout=20):
    x, y = center(find(pattern, timeout))
    adb("shell", "input", "tap", str(x), str(y))
    time.sleep(1.5)


def fill(node, text):
    x, y = center(node)
    adb("shell", "input", "tap", str(x), str(y))
    adb("shell", "input", "keyevent", "KEYCODE_MOVE_END")
    adb("shell", "input", "keyevent", "--longpress", *["KEYCODE_DEL"] * 60)
    adb("shell", "input", "text", text.replace(" ", "%s"))


def edits():
    return [n for n in nodes() if n.get("class") == "android.widget.EditText"]


def set_locale(locale):
    adb("root")
    adb("wait-for-device")
    adb("shell", "setprop", "persist.sys.locale", locale)
    adb("shell", "setprop", "ctl.restart", "zygote")
    time.sleep(5)
    adb("wait-for-device")
    while adb("shell", "getprop", "sys.boot_completed", check=False).strip() != "1":
        time.sleep(2)
    time.sleep(5)


def demo_status_bar():
    """Fixed clock, full battery and signal, no notification icons (Android's system UI demo mode)."""
    adb("shell", "settings", "put", "global", "sysui_demo_allowed", "1")
    demo = ["shell", "am", "broadcast", "-a", "com.android.systemui.demo", "-e", "command"]
    adb(*demo, "enter")
    adb(*demo, "clock", "-e", "hhmm", "1000")
    adb(*demo, "battery", "-e", "level", "100", "-e", "plugged", "false")
    adb(*demo, "network", "-e", "wifi", "show", "-e", "level", "4")
    adb(*demo, "network", "-e", "mobile", "show", "-e", "level", "4", "-e", "datatype", "none")
    adb(*demo, "notifications", "-e", "visible", "false")


def screenshot(path):
    time.sleep(1.5)
    with open(path, "wb") as file:
        file.write(subprocess.run([ADB, "exec-out", "screencap", "-p"], check=True, capture_output=True).stdout)
    print(f"  {path}")


def main():
    out = sys.argv[1]
    email, password = os.environ["BM_SHOT_EMAIL"], os.environ["BM_SHOT_PASSWORD"]
    for lang, locale in LOCALES.items():
        print(f"{lang}:")
        os.makedirs(os.path.join(out, lang), exist_ok=True)
        # The app shows the server's data: example vehicles in this language first.
        subprocess.run(["node", "shoot.mjs", "--seed", lang], cwd=os.path.dirname(os.path.abspath(__file__)), check=True)
        set_locale(locale)
        adb("shell", "pm", "clear", PACKAGE)
        adb("shell", "pm", "grant", PACKAGE, "android.permission.POST_NOTIFICATIONS", check=False)
        adb("shell", "monkey", "-p", PACKAGE, "-c", "android.intent.category.LAUNCHER", "1")
        fill(edits()[0], SERVER)
        tap(r"^(Continue|Weiter)$")
        # Plain HTTP to the dev stack: the app warns once.
        tap(r"^(Connect anyway|Trotzdem verbinden)$")
        fields = edits()
        fill(fields[0], email)
        fill(fields[1], password)
        tap(r"^(Sign in|Log in|Anmelden)$")
        # First start: the app asks for reminders (the permission is already granted above).
        tap(r"^(Allow|Erlauben)$", timeout=30)
        find(r"MT-07", timeout=30)
        demo_status_bar()
        screenshot(os.path.join(out, lang, "app-vehicles.png"))
        tap(r"^MT-07$")
        screenshot(os.path.join(out, lang, "app-vehicle.png"))


if __name__ == "__main__":
    main()
