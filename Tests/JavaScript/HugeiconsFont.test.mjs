import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { FIRST_CODEPOINT, FONT_CAPACITY, planIconFonts } from '../../Build/Scripts/lib/hugeicons-font-plan.mjs';

const published = JSON.parse(readFileSync(new URL('../../Build/hugeicons/codepoints.json', import.meta.url), 'utf8'));
const assignments = (plan) => Object.fromEntries(plan.fonts.flatMap(font =>
  font.icons.map((slug, index) => [slug, { font: font.name, codepoint: FIRST_CODEPOINT + index }])
));

test('a growing icon set keeps every glyph inside the BMP private-use area', () => {
  const slugs = Array.from({ length: FONT_CAPACITY + 400 }, (_, i) => `icon-${i.toString().padStart(5, '0')}`);
  const plan = planIconFonts(slugs, { version: 1, fonts: [] });
  assert.equal(plan.fonts.length, 2);
  assert.equal(Object.keys(assignments(plan)).length, slugs.length);
  assert.ok(plan.fonts.every(font => font.icons.length <= FONT_CAPACITY));
});

test('new alphabetically earlier names cannot renumber published icon classes', () => {
  const slugs = published.fonts.flatMap(font => font.icons);
  const before = assignments(published);
  const after = assignments(planIconFonts([...slugs, 'a-a-new-icon'], published));
  for (const [slug, assignment] of Object.entries(before)) {
    assert.deepEqual(after[slug], assignment, slug);
  }
});

test('removed names keep their reserved slots and cannot be reassigned', () => {
  const previous = { version: 1, fonts: [{ name: 'hugeicons', icons: ['old-icon', 'retired-icon'] }] };
  const result = assignments(planIconFonts(['old-icon', 'new-icon'], previous));
  assert.deepEqual(result['retired-icon'], { font: 'hugeicons', codepoint: FIRST_CODEPOINT + 1 });
  assert.deepEqual(result['new-icon'], { font: 'hugeicons', codepoint: FIRST_CODEPOINT + 2 });
});

test('a rebuild and reordered package exports produce the same manifest', () => {
  const slugs = ['last-icon', 'first-icon'];
  const first = planIconFonts(slugs, { version: 1, fonts: [] });
  assert.deepEqual(planIconFonts([...slugs].reverse(), first), first);
});

test('duplicate manifest entries are rejected instead of changing published mappings', () => {
  const duplicate = { version: 1, fonts: [{ name: 'hugeicons', icons: ['same', 'same'] }] };
  assert.throws(() => planIconFonts([], duplicate), /duplicate/);
});
