#!/usr/bin/env node
// npm ci && npm run build:hugeicons-font && node Tests/JavaScript/HugeiconsFont.browser.mjs
// Exercises shipped CSS/WOFF2, including repeated codepoints in different shards.
import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { spawnSync } from 'node:child_process';
import { readFileSync, mkdirSync, writeFileSync } from 'node:fs';
import { createServer } from 'node:http';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { chromium } from '@playwright/test';

const root = join(dirname(fileURLToPath(import.meta.url)), '../..');
const json = path => JSON.parse(readFileSync(join(root, path), 'utf8'));
const sha256 = value => createHash('sha256').update(value).digest('hex');
const first = 0xe001;
const publishedCount = 6156;
// Canonical JSON of sorted [slug, codepoint] pairs from the published 4.2.0 CSS.
const publishedMappingHash = '63e18db9bdadd94d19b1b9924a2f6d742d7ac4137c28d1ea12a0ba88d44f7355';
const manifest = json('Build/hugeicons/codepoints.json');
const legacy = json('Build/hugeicons/legacy-icons.json');
const provenance = json('Tests/Fixtures/Hugeicons/provenance.json');
const fontRoot = join(root, 'Resources/Public/IconFonts/hugeicons');
const css = readFileSync(join(fontRoot, 'hugeicons.css'), 'utf8');
const entries = manifest.fonts.flatMap(font => font.icons.map((slug, index) => ({ slug, font: font.name, codepoint: first + index })));
assert.equal(new Set(entries.map(entry => entry.slug)).size, entries.length, 'No class may occur in two shards');
assert(manifest.fonts.every(font => font.icons.length > 0 && font.icons.length <= 6399));
const published = manifest.fonts[0].icons.slice(0, publishedCount).map((slug, index) => [slug, first + index]);
published.sort(([a], [b]) => a < b ? -1 : a > b ? 1 : 0);
assert.equal(sha256(JSON.stringify(published)), publishedMappingHash, 'All 6156 published codepoints must remain frozen');
assert.equal(provenance.publishedMappingSha256, publishedMappingHash);
assert.deepEqual([...provenance.legacySlugs].sort(), Object.keys(legacy.icons).sort());
assert.equal(provenance.legacySlugs.length, 32);
const subset = readFileSync(join(root, 'Tests/Fixtures/Hugeicons/published-4.2.0-subset.woff2'));
assert.equal(sha256(subset), provenance.subsetFontSha256);

const declarations = [...css.matchAll(/\.hgi\.hgi-([a-z0-9-]+):before\s*\{([^}]+)\}/g)];
assert.equal(declarations.length, entries.length, 'Every class needs exactly one CSS rule');
const cssEntries = new Map();
for (const [, slug, block] of declarations) {
  assert(!cssEntries.has(slug), `Duplicate CSS class ${slug}`);
  const codepoint = Number.parseInt(block.match(/content:\s*"\\([0-9a-f]+)"/)?.[1], 16);
  const font = block.match(/font-family:\s*"([^"]+)"\s*!important/)?.[1] ?? 'hugeicons';
  cssEntries.set(slug, { font, codepoint });
}
for (const { slug, font, codepoint } of entries) assert.deepEqual(cssEntries.get(slug), { font, codepoint }, slug);
for (const [slug, codepoint] of Object.entries(provenance.codepoints)) {
  assert.deepEqual(cssEntries.get(slug), { font: 'hugeicons', codepoint }, `Baseline fixture mapping ${slug}`);
}

// Read actual font tables independently of the generator/planner. The pinned
// fontTools runtime is provisioned by build:hugeicons-font; no private fixtures.
const cmapCheck = spawnSync(process.env.HUGEICONS_PYTHON ?? join(root, 'var/picosvg-venv/bin/python'), ['-c', `
import json,sys,pathlib
from fontTools.ttLib import TTFont
root=pathlib.Path(sys.argv[1]); plan=json.loads((root/'Build/hugeicons/codepoints.json').read_text()); result={}
for shard in plan['fonts']:
    font=TTFont(root/'Resources/Public/IconFonts/hugeicons'/f"{shard['name']}.woff2")
    cmap=font.getBestCmap(); bad=[]
    for cp,name in cmap.items():
        glyph=font['glyf'][name]
        if font.getGlyphID(name)==0 or glyph.numberOfContours==0 or font['hmtx'].metrics[name][0]<=0: bad.append(cp)
    result[shard['name']]={'codepoints':sorted(cmap),'emptyGlyphs':bad}
print(json.dumps(result))
`, root], { encoding: 'utf8', maxBuffer: 2 * 1024 * 1024 });
assert.equal(cmapCheck.status, 0, cmapCheck.stderr || 'Run build:hugeicons-font to provision the pinned fontTools runtime');
const cmaps = JSON.parse(cmapCheck.stdout);
for (const font of manifest.fonts) {
  assert.deepEqual(cmaps[font.name].codepoints, font.icons.map((_, index) => first + index), `Actual cmap ${font.name}`);
  assert.deepEqual(cmaps[font.name].emptyGlyphs, [], `Every mapped glyph must have ink and a positive advance: ${font.name}`);
}

const baselineNames = new Set(manifest.fonts[0].icons.slice(0, publishedCount));
const additions = entries.filter(entry => !baselineNames.has(entry.slug));
const selected = new Set([
  ...Object.keys(provenance.codepoints),
  ...additions.slice(0, 3).map(entry => entry.slug),
  ...additions.slice(-3).map(entry => entry.slug),
  ...manifest.fonts.flatMap(font => [...font.icons.slice(0, 2), ...font.icons.slice(-2)]),
]);
const samples = entries.filter(entry => selected.has(entry.slug));
const resources = new Map([
  ['/hugeicons.css', { type: 'text/css', data: Buffer.from(css) }],
  ['/published-subset.woff2', { type: 'font/woff2', data: subset }],
  ...manifest.fonts.map(font => [`/${font.name}.woff2`, { type: 'font/woff2', data: readFileSync(join(fontRoot, `${font.name}.woff2`)) }]),
]);
const html = `<!doctype html><html lang="en"><meta charset="utf-8"><title>HugeIcons font acceptance</title><link rel="stylesheet" href="/hugeicons.css"><style>
@font-face{font-family:published-subset;src:url(/published-subset.woff2) format('woff2')}
body{font:14px sans-serif;background:white;color:black;margin:24px}#gallery{display:grid;grid-template-columns:repeat(5,1fr);gap:16px}.sample{border:1px solid #ccc;padding:8px}.glyph{display:inline-block;font-size:64px;line-height:1;min-width:64px;min-height:64px}.baseline{font-family:published-subset}code{display:block;font-size:11px;overflow-wrap:anywhere}#mapping-proof{display:none}
</style><h1>HugeIcons: current / published baseline</h1><div id="gallery"></div><div id="mapping-proof"></div></html>`;
const server = createServer((request, response) => {
  const path = new URL(request.url, 'http://localhost').pathname;
  const resource = path === '/' ? { type: 'text/html', data: Buffer.from(html) } : resources.get(path);
  response.writeHead(resource ? 200 : 404, { 'Content-Type': resource?.type ?? 'text/plain', 'Cache-Control': 'no-store' });
  response.end(resource?.data ?? 'Not found');
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const output = join(root, 'var/hugeicons-browser');
mkdirSync(output, { recursive: true });
let browser;
try {
  browser = await chromium.launch();
  const context = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  await context.addInitScript(() => {
    window.__blockedExternal = 0;
    for (const type of ['click', 'auxclick']) window.addEventListener(type, event => {
      const link = event.composedPath().find(node => node?.localName === 'a' || node?.localName === 'area');
      if (link && (link.hasAttribute('data-mailto-token') || !['http:', 'https:'].includes(new URL(link.getAttribute('href') ?? '', location.href).protocol))) {
        event.preventDefault(); event.stopImmediatePropagation(); window.__blockedExternal++;
      }
    }, true);
  });
  const page = await context.newPage();
  const errors = [], failures = [], loadedFonts = new Set();
  page.on('pageerror', error => errors.push(error.message));
  page.on('response', response => {
    if (response.status() >= 400) failures.push(`${response.status()} ${response.url()}`);
    if (response.request().resourceType() === 'font' && response.status() === 200) loadedFonts.add(new URL(response.url()).pathname);
  });
  assert.equal((await page.goto(`http://127.0.0.1:${server.address().port}/`, { waitUntil: 'domcontentloaded' })).status(), 200);
  const proof = await page.evaluate(async ({ entries, samples, baseline }) => {
    await Promise.all([...new Set(entries.map(entry => entry.font)), 'published-subset']
      .map(font => document.fonts.load(`96px "${font}"`, String.fromCodePoint(0xe001))));
    const wrongStyles = [];
    const mapping = document.querySelector('#mapping-proof');
    for (const entry of entries) {
      const icon = document.createElement('span');
      icon.className = `hgi hgi-${entry.slug}`;
      mapping.append(icon);
      const style = getComputedStyle(icon, '::before');
      if (style.content.codePointAt(1) !== entry.codepoint || style.fontFamily.replace(/["']/g, '') !== entry.font) wrongStyles.push(entry.slug);
    }
    const canvas = document.createElement('canvas');
    canvas.width = canvas.height = 160;
    const ctx = canvas.getContext('2d', { willReadFrequently: true });
    const draw = (font, codepoint) => {
      ctx.clearRect(0, 0, 160, 160); ctx.font = `96px "${font}"`; ctx.fillStyle = '#000'; ctx.fillText(String.fromCodePoint(codepoint), 16, 112);
      return ctx.getImageData(0, 0, 160, 160).data.slice();
    };
    const rasters = [];
    for (const entry of samples) {
      const cell = document.createElement('div'); cell.className = 'sample';
      const icon = document.createElement('span'); icon.className = `glyph hgi hgi-${entry.slug}`; cell.append(icon);
      const label = document.createElement('code'); label.textContent = `${entry.slug} (${entry.font})`; cell.append(label);
      const pixels = draw(entry.font, entry.codepoint);
      const signature = canvas.toDataURL();
      const ink = pixels.filter((_, i) => i % 4 === 3 && pixels[i] > 0).length;
      const missing = draw(entry.font, 0xe000);
      let changedPixels = null;
      if (Object.hasOwn(baseline, entry.slug)) {
        const old = document.createElement('span'); old.className = 'glyph baseline'; old.textContent = String.fromCodePoint(baseline[entry.slug]); cell.append(old);
        const previous = draw('published-subset', baseline[entry.slug]);
        changedPixels = pixels.filter((value, index) => index % 4 === 3 && value !== previous[index]).length;
      }
      document.querySelector('#gallery').append(cell);
      rasters.push({ ...entry, ink, equalsMissing: pixels.every((value, index) => value === missing[index]), changedPixels, signature });
    }
    return { wrongStyles, rasters, externalGuard: window.__blockedExternal };
  }, { entries, samples, baseline: provenance.codepoints });
  await page.screenshot({ path: join(output, 'samples.png'), fullPage: true });
  const report = { classes: entries.length, publishedMappings: publishedCount, legacy: provenance.legacySlugs.length,
    fonts: manifest.fonts.map(font => ({ name: font.name, glyphs: font.icons.length })), wrongStyles: proof.wrongStyles,
    rasters: proof.rasters.map(({ signature, ...raster }) => ({ ...raster, sha256: sha256(signature) })), errors, failures, loadedFonts: [...loadedFonts], externalGuard: proof.externalGuard };
  writeFileSync(join(output, 'report.json'), JSON.stringify(report, null, 2) + '\n');
  assert.deepEqual(proof.wrongStyles, [], 'Every real pseudo-element must select its intended character and font shard');
  assert.deepEqual(proof.rasters.filter(raster => raster.ink === 0 || raster.equalsMissing).map(raster => raster.slug), [], 'Samples must render real, nonempty glyphs');
  assert.deepEqual(proof.rasters.filter(raster => raster.changedPixels > 0).map(({ slug, changedPixels }) => ({ slug, changedPixels })), [], 'Legacy and unchanged sample rasters must match the published font');
  assert.deepEqual(errors, []); assert.deepEqual(failures, []); assert.equal(proof.externalGuard, 0);
  for (const font of manifest.fonts) assert(loadedFonts.has(`/${font.name}.woff2`), `The browser must fetch ${font.name}`);
  console.log(`HugeIcons: ${entries.length} cmap/CSS/browser mappings, ${publishedCount} frozen mappings, ${provenance.legacySlugs.length} legacy icons and ${samples.length} raster samples passed.`);
} finally {
  await browser?.close();
  await new Promise(resolve => server.close(resolve));
}
