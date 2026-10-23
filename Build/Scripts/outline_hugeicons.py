"""Outline HugeIcons strokes and canonicalize contour order without changing geometry."""

import sys
from pathlib import Path

from lxml import etree
from picosvg.svg import SVG
from picosvg.svg_types import SVGPath


def canonicalize_svg(svg):
    tree = svg.toetree()
    for path in tree.iter("{http://www.w3.org/2000/svg}path"):
        subpaths = SVGPath(d=path.attrib["d"]).subpaths()
        # Skia can swap equally ranked contours across native builds. A fill's
        # contour order does not affect its area or winding rule. Preserve each
        # contour's coordinate sequence and winding, and the elements' paint order.
        if any(not contour.rstrip().upper().endswith("Z") for contour in subpaths):
            raise ValueError("Expected closed filled contours from picosvg")
        path.attrib["d"] = " ".join(sorted(subpaths))
    return etree.tostring(tree).decode("utf-8")


def main(src, dst):
    failed = []
    for svg_file in sorted(src.glob("*.svg")):
        try:
            pico = SVG.fromstring(svg_file.read_text()).topicosvg()
            (dst / svg_file.name).write_text(canonicalize_svg(pico))
        except Exception as exc:
            failed.append(f"{svg_file.stem}: {exc}")
    print(f'outlined {len(list(dst.glob("*.svg")))} icons, {len(failed)} failed')
    for line in failed:
        print("  FAILED", line)
    return 1 if failed else 0


if __name__ == "__main__":
    sys.exit(main(Path(sys.argv[1]), Path(sys.argv[2])))
