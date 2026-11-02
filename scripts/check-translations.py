#!/usr/bin/env python3
"""Fails if a text of the theme has no German translation (empty or missing msgstr in de_DE.po).

Proper names (author, font names, URLs) may stay untranslated; plugin headers of the must-use plugins are skipped.
Used by scripts/i18n.sh and the CI.
"""
import re
import sys
from pathlib import Path

LANGUAGES = Path(__file__).resolve().parent.parent / "wp-content/themes/bike-maintainer/languages"
UNTRANSLATED_OK = {"Chris Wohlbrecht", "Inter", "JetBrains Mono", "https://github.com/stargazer74/bike-maintainer-portal"}

ENTRY = re.compile(r'(?:^msgctxt "((?:[^"\\]|\\.)*)"\n)?^msgid "((?:[^"\\]|\\.)*)"\n^msgstr "((?:[^"\\]|\\.)*)"', re.M)


def entries(path):
    """(context, msgid) -> msgstr. Skips plugin headers: WP-CLI extracts the header of one of the must-use plugins
    (which one depends on the file system order); it only shows in the admin's list of must-use plugins."""
    result = {}
    for block in path.read_text(encoding="utf-8").split("\n\n"):
        if "of the plugin" in block and block.lstrip().startswith("#."):
            continue
        for ctx, msgid, msgstr in ENTRY.findall(block):
            if msgid:
                result[(ctx, msgid)] = msgstr
    return result


template = entries(LANGUAGES / "bike-maintainer.pot")
german = entries(LANGUAGES / "de_DE.po")
missing = [msgid for key in template if (msgid := key[1]) not in UNTRANSLATED_OK and not german.get(key)]
if missing:
    print("Missing German translations in languages/de_DE.po (run scripts/i18n.sh and translate):")
    for msgid in missing:
        print(f"  - {msgid}")
    sys.exit(1)
print(f"All {len(template)} texts have a German translation.")
