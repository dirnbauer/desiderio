"""Keep published raster order without freezing changed artwork or merging aliases."""

import copy
import importlib.util
import io
import sys
import shutil
import tempfile
import unittest
from pathlib import Path

from fontTools.fontBuilder import FontBuilder
from fontTools.pens.ttGlyphPen import TTGlyphPen
from fontTools.ttLib import TTFont

sys.dont_write_bytecode = True
ROOT = Path(__file__).resolve().parents[2]
SPEC = importlib.util.spec_from_file_location('preserve_hugeicons', ROOT / 'Build/Scripts/preserve_hugeicons_glyphs.py')
PRESERVE = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(PRESERVE)


def rectangles(reverse=False, changed=False):
    contours = [[(0, 0), (100, 0), (100, 100), (0, 100)],
                [(200, 0), (300, 0), (300, 100), (200, 100)]]
    if changed:
        contours[0][1] = (110, 0)
    pen = TTGlyphPen(None)
    for points in reversed(contours) if reverse else contours:
        pen.moveTo(points[0])
        for point in points[1:]:
            pen.lineTo(point)
        pen.closePath()
    return pen.glyph()


def font(glyphs, cmap):
    builder = FontBuilder(1000, isTTF=True)
    order = ['.notdef', *glyphs]
    builder.setupGlyphOrder(order)
    builder.setupCharacterMap(cmap)
    builder.setupGlyf({'.notdef': TTGlyphPen(None).glyph(), **glyphs})
    builder.setupHorizontalMetrics({name: (1000, 0) for name in order})
    builder.setupHorizontalHeader(ascent=1000, descent=0)
    builder.setupNameTable({'familyName': 'Test', 'styleName': 'Regular', 'uniqueFontIdentifier': 'Test',
                            'fullName': 'Test', 'psName': 'Test'})
    builder.setupOS2(sTypoAscender=1000, sTypoDescender=0, usWinAscent=1000, usWinDescent=0)
    builder.setupPost()
    builder.setupMaxp()
    return builder.font


class HugeiconsPublishedGlyphTest(unittest.TestCase):
    def test_real_legacy_icon_retains_complete_published_point_sequence(self):
        with TTFont(ROOT / 'Build/hugeicons/published-4.2.0.woff2') as published, \
                TTFont(ROOT / 'Resources/Public/IconFonts/hugeicons/hugeicons.woff2') as current:
            expected = PRESERVE.glyph_state(published, published.getBestCmap()[0xE8D6])
            PRESERVE.preserve_published_glyphs(current, published)
            self.assertEqual(expected, PRESERVE.glyph_state(current, current.getBestCmap()[0xE8D6]))

    def test_deduplicated_aliases_with_different_published_orders_are_split(self):
        published = font({'old-a': rectangles(True), 'old-b': rectangles()}, {0xE001: 'old-a', 0xE002: 'old-b'})
        current = font({'shared': rectangles()}, {0xE001: 'shared', 0xE002: 'shared'})
        PRESERVE.preserve_published_glyphs(current, published)
        self.assertNotEqual(current.getBestCmap()[0xE001], current.getBestCmap()[0xE002])
        for cp in (0xE001, 0xE002):
            self.assertEqual(PRESERVE.glyph_state(published, published.getBestCmap()[cp]),
                             PRESERVE.glyph_state(current, current.getBestCmap()[cp]))
        data = io.BytesIO()
        current.save(data)
        data.seek(0)
        with TTFont(data) as reloaded:
            self.assertEqual(reloaded.getBestCmap(), current.getBestCmap())

    def test_new_alias_keeps_its_canonical_order(self):
        published = font({'old': rectangles(True)}, {0xE001: 'old'})
        current = font({'shared': rectangles()}, {0xE001: 'shared', 0xE002: 'shared'})
        original = PRESERVE.glyph_state(current, 'shared')
        PRESERVE.preserve_published_glyphs(current, published)
        self.assertEqual(original, PRESERVE.glyph_state(current, current.getBestCmap()[0xE002]))
        self.assertEqual(PRESERVE.glyph_state(published, 'old'),
                         PRESERVE.glyph_state(current, current.getBestCmap()[0xE001]))

    def test_changed_artwork_remains_latest(self):
        published = font({'old': rectangles(True)}, {0xE001: 'old'})
        current = font({'new': rectangles(changed=True)}, {0xE001: 'new'})
        original = PRESERVE.glyph_state(current, 'new')
        PRESERVE.preserve_published_glyphs(current, published)
        self.assertEqual(original, PRESERVE.glyph_state(current, 'new'))
        self.assertEqual(['.notdef', 'new'], current.getGlyphOrder())

    def test_changed_published_alias_keeps_latest_canonical_artwork(self):
        published = font({'old': rectangles(True), 'changed': rectangles(changed=True)},
                         {0xE001: 'old', 0xE002: 'changed'})
        current = font({'shared': rectangles()}, {0xE001: 'shared', 0xE002: 'shared'})
        original = PRESERVE.glyph_state(current, 'shared')
        PRESERVE.preserve_published_glyphs(current, published)
        self.assertEqual(original, PRESERVE.glyph_state(current, current.getBestCmap()[0xE002]))
        for table in current['cmap'].tables:
            if table.isUnicode():
                self.assertEqual(table.cmap, current.getBestCmap())

    def test_global_hinting_layout_and_component_references_are_rejected(self):
        from fontTools.ttLib import newTable
        from fontTools.ttLib.tables._g_l_y_f import Glyph, GlyphComponent
        published = font({'old': rectangles(True)}, {0xE001: 'old'})
        for table in ('prep', 'GSUB', 'component'):
            with self.subTest(table=table):
                current = font({'shared': rectangles()}, {0xE001: 'shared'})
                if table == 'component':
                    glyph = Glyph()
                    glyph.numberOfContours = -1
                    component = GlyphComponent()
                    component.glyphName = 'shared'
                    component.x = component.y = 0
                    component.flags = 0
                    glyph.components = [component]
                    current['glyf'].glyphs['unmapped-component'] = glyph
                    current.setGlyphOrder([*current.getGlyphOrder(), 'unmapped-component'])
                else:
                    current[table] = newTable(table)
                with self.assertRaises(ValueError):
                    PRESERVE.preserve_published_glyphs(current, published)

    def test_inconsistent_unicode_subtables_are_rejected(self):
        published = font({'old': rectangles(True)}, {0xE001: 'old'})
        for missing in (True, False):
            with self.subTest(missing=missing):
                current = font({'new': rectangles()}, {0xE001: 'new'})
                tables = [table for table in current['cmap'].tables if table.isUnicode()]
                if missing:
                    tables[0].cmap = {}
                else:
                    tables[0].cmap = {0xE001: '.notdef'}
                with self.assertRaisesRegex(ValueError, 'cmap subtables disagree'):
                    PRESERVE.preserve_published_glyphs(current, published)

    def test_real_font_roundtrip_preserves_all_eligible_and_changed_glyphs(self):
        metadata = ROOT / 'Build/hugeicons/published-4.2.0.json'
        reference = ROOT / 'Build/hugeicons/published-4.2.0.woff2'
        source = ROOT / 'Resources/Public/IconFonts/hugeicons/hugeicons.woff2'
        with TTFont(source, recalcTimestamp=False, recalcBBoxes=False) as before, \
                TTFont(reference, recalcTimestamp=False, recalcBBoxes=False) as published, \
                tempfile.TemporaryDirectory() as directory:
            target = Path(directory) / 'hugeicons.woff2'
            shutil.copyfile(source, target)
            PRESERVE.preserve_font_file(target, reference, metadata, ROOT / 'Build/hugeicons/codepoints.json')
            with TTFont(target, recalcTimestamp=False, recalcBBoxes=False) as after:
                self.assertEqual(set(before.getBestCmap()), set(after.getBestCmap()))
                for field in ('flags', 'created', 'modified', 'unitsPerEm'):
                    self.assertEqual(getattr(before['head'], field), getattr(after['head'], field), field)
                for cp, name in before.getBestCmap().items():
                    expected = PRESERVE.glyph_state(before, name)
                    if cp in published.getBestCmap():
                        old = PRESERVE.glyph_state(published, published.getBestCmap()[cp])
                        if PRESERVE.equivalent(expected, old):
                            expected = old
                    self.assertEqual(expected, PRESERVE.glyph_state(after, after.getBestCmap()[cp]), hex(cp))
            data = target.read_bytes()
            PRESERVE.preserve_font_file(target, reference, metadata, ROOT / 'Build/hugeicons/codepoints.json')
            self.assertEqual(data, target.read_bytes(), 'Preservation must be byte-idempotent')
            with self.assertRaisesRegex(ValueError, 'original font family'):
                wrong = target.with_name('hugeicons-2.woff2')
                shutil.copyfile(target, wrong)
                PRESERVE.preserve_font_file(wrong, reference, metadata, ROOT / 'Build/hugeicons/codepoints.json')

    def test_flags_metrics_bbox_and_instructions_must_also_match(self):
        published = font({'old': rectangles(True)}, {0xE001: 'old'})
        for field in ('flags', 'metrics', 'bbox', 'instructions'):
            with self.subTest(field=field):
                current = font({'new': rectangles()}, {0xE001: 'new'})
                if field == 'flags':
                    current['glyf']['new'].flags[0] ^= 1
                elif field == 'metrics':
                    current['hmtx'].metrics['new'] = (1001, 0)
                elif field == 'bbox':
                    current['glyf']['new'].xMin -= 1
                else:
                    current['glyf']['new'].program.fromBytecode([0])
                before = PRESERVE.glyph_state(current, 'new')
                PRESERVE.preserve_published_glyphs(current, published)
                self.assertEqual(before, PRESERVE.glyph_state(current, 'new'))


if __name__ == '__main__':
    unittest.main()
