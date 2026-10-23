"""Validate published codepoints against the actual WOFF2 consumer tables."""

import json
import pathlib
import sys

from fontTools.ttLib import TTFont


def check(font_directory, manifest):
    total = 0
    for shard in manifest["fonts"]:
        with TTFont(font_directory / (shard["name"] + ".woff2")) as font:
            cmap = font.getBestCmap()
            expected = set(range(0xE001, 0xE001 + len(shard["icons"])))
            if set(cmap) != expected:
                raise ValueError(f'{shard["name"]}: WOFF2 cmap does not match its manifest')
            for codepoint in expected:
                glyph_name = cmap[codepoint]
                if font["glyf"][glyph_name].numberOfContours == 0:
                    raise ValueError(f'{shard["name"]}: empty glyph at U+{codepoint:04X}')
                if font["hmtx"].metrics[glyph_name][0] <= 0:
                    raise ValueError(f'{shard["name"]}: zero-width glyph at U+{codepoint:04X}')
            total += len(expected)
    return total


if __name__ == "__main__":
    directory = pathlib.Path(sys.argv[1])
    manifest = json.loads(pathlib.Path(sys.argv[2]).read_text())
    print(json.dumps({"fonts": len(manifest["fonts"]), "mapped_nonempty_glyphs": check(directory, manifest)}))
