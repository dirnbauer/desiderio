"""Real cold-build regressions: Skia may return identical contours in a different order."""

import importlib.util
import unittest
import sys
from pathlib import Path

from lxml import etree
from picosvg.svg import SVG
from picosvg.svg_types import SVGPath

sys.dont_write_bytecode = True

ROOT = Path(__file__).resolve().parents[2]
SPEC = importlib.util.spec_from_file_location("outline_hugeicons", ROOT / "Build/Scripts/outline_hugeicons.py")
OUTLINE = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(OUTLINE)
FIXTURES = ROOT / "Tests/Fixtures/Hugeicons/contour-order"


class HugeiconsOutlineTest(unittest.TestCase):
    def test_real_linux_and_macos_cold_builds_have_identical_canonical_paths(self):
        for icon in ("apartment", "panel-left-dashed", "panel-top-bottom-dashed", "thread"):
            with self.subTest(icon=icon):
                mac = SVG.fromstring((FIXTURES / f"{icon}.mac.svg").read_text())
                linux = SVG.fromstring((FIXTURES / f"{icon}.linux.svg").read_text())
                self.assertEqual(OUTLINE.canonicalize_svg(mac), OUTLINE.canonicalize_svg(linux))

    def test_open_contours_are_rejected(self):
        svg = SVG.fromstring('<svg xmlns="http://www.w3.org/2000/svg"><path d="M0,0 L10,10"/></svg>')
        with self.assertRaisesRegex(ValueError, "closed filled contours"):
            OUTLINE.canonicalize_svg(svg)

    def test_compound_hole_retains_its_opposite_winding_and_paint_order(self):
        outer = "M0,0 L10,0 L10,10 L0,10 Z"
        hole = "M2,2 L2,8 L8,8 L8,2 Z"
        svg = f'<svg xmlns="http://www.w3.org/2000/svg"><path fill-rule="nonzero" d="{hole} {outer}"/><path fill="red" d="M20,0 L21,0 L21,1 Z"/></svg>'
        tree = etree.fromstring(OUTLINE.canonicalize_svg(SVG.fromstring(svg)).encode())
        paths = list(tree)
        self.assertEqual(paths[0].attrib["fill-rule"], "nonzero")
        self.assertEqual(paths[1].attrib["fill"], "red")
        self.assertCountEqual(SVGPath(d=paths[0].attrib["d"]).subpaths(), (outer, hole))
        self.assertEqual(paths[1].attrib["d"], "M20,0 L21,0 L21,1 Z")

    def test_canonicalization_preserves_coordinates_winding_and_element_order(self):
        for fixture in sorted(FIXTURES.glob("*.svg")):
            with self.subTest(fixture=fixture.name):
                before = etree.fromstring(fixture.read_bytes())
                canonical = OUTLINE.canonicalize_svg(SVG.fromstring(fixture.read_text()))
                after = etree.fromstring(canonical.encode())
                self.assertEqual(len(list(before.iter())), len(list(after.iter())))
                for original, result in zip(before.iter(), after.iter()):
                    self.assertEqual(original.tag, result.tag)
                    self.assertEqual({k: v for k, v in original.attrib.items() if k != "d"},
                                     {k: v for k, v in result.attrib.items() if k != "d"})
                    if "d" in original.attrib:
                        self.assertCountEqual(SVGPath(d=original.attrib["d"]).subpaths(),
                                              SVGPath(d=result.attrib["d"]).subpaths())
                self.assertEqual(canonical, OUTLINE.canonicalize_svg(SVG.fromstring(canonical)))


if __name__ == "__main__":
    unittest.main()
