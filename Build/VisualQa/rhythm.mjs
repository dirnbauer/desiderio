#!/usr/bin/env node
/**
 * Vertical rhythm survey: how much space every element leaves before and after
 * its content, as a visitor sees it.
 *
 * The section's padding is only half of that. An intro with a top margin, a
 * wrapper with its own padding or a card grid that starts lower all push the
 * first visible pixel down, and two elements with identical padding can still
 * leave very different gaps. So this measures the INSET: from the section's
 * edge to the first painted thing inside it (a line of text, an image, a
 * surface with its own background, border or shadow), and from the last
 * painted thing to the section's bottom edge.
 *
 *   node Build/VisualQa/rhythm.mjs --urls /tmp/preview-urls.json [--widths 390,1440]
 *                                  [--only <cType substring>] [--out report/rhythm.json]
 *
 * Prints the distribution per width and every element whose inset leaves the
 * shared rhythm; exits 1 when there are such outliers (see RHYTHM_EXCEPTIONS).
 */

import { chromium } from 'playwright';
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { primeMode, settleFonts } from './lib/state.mjs';
import { HERO_RHYTHM, RHYTHM_EXCEPTIONS, RHYTHM_NOTES, collectRhythm } from './lib/rhythm.mjs';

const HERE = dirname(fileURLToPath(import.meta.url));
const argv = process.argv.slice(2);
const flag = (name, fallback = null) => {
    const i = argv.indexOf(`--${name}`);
    return i === -1 ? fallback : argv[i + 1];
};

const URLS = flag('urls');
if (!URLS) {
    console.error('Pass --urls <file>. Produce it with:\n  ddev typo3 desiderio:library:urls --site=desiderio --json > /tmp/preview-urls.json');
    process.exit(2);
}
const WIDTHS = (flag('widths', '390,1440') ?? '390,1440').split(',').map(Number);
const ONLY = flag('only');
const OUT = flag('out', join(HERE, 'report', 'rhythm.json'));
// Allowed distance between an element's inset and the shared rhythm, in px.
// Text line boxes carry half-leading and a card's first line sits a padding
// below its ring, so an exact match is neither possible nor wanted.
const TOLERANCE = Number(flag('tolerance', '12'));

let entries = JSON.parse(readFileSync(URLS, 'utf8'));
if (ONLY) entries = entries.filter((e) => e.cType.includes(ONLY));

const browser = await chromium.launch();
const rows = [];

for (const width of WIDTHS) {
    const context = await browser.newContext({
        viewport: { width, height: 1200 },
        ignoreHTTPSErrors: true,
        reducedMotion: 'reduce',
        deviceScaleFactor: 1,
    });
    await primeMode(context, 'light');
    const queue = [...entries];
    await Promise.all(Array.from({ length: 6 }, async () => {
        for (;;) {
            const entry = queue.shift();
            if (!entry) return;
            const page = await context.newPage();
            try {
                let response = null;
                // Under parallel load a stylesheet request can fail while the
                // page still fires `load`; the element then measures with the
                // browser's default margins. Every linked sheet must be there.
                for (let attempt = 0; attempt < 3; attempt++) {
                    response = await page.goto(entry.url, { waitUntil: 'load', timeout: 45_000 });
                    const complete = await page.evaluate(() => [...document.querySelectorAll('link[rel="stylesheet"]')]
                        .every((link) => { try { return link.sheet !== null && link.sheet.cssRules.length >= 0; } catch { return false; } }));
                    if (complete) break;
                    if (attempt === 2) throw new Error('a stylesheet did not load');
                }
                if (response && !response.ok()) {
                    rows.push({ cType: entry.cType, group: entry.group, width, error: `HTTP ${response.status()}` });
                    continue;
                }
                await settleFonts(page);
                // The theme script settles the scheme after `load`; for a few
                // frames the page can render without its sheets applied. Two
                // readings a moment apart, the later one wins when they differ.
                const settle = () => page.evaluate(() => new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(() => setTimeout(resolve, 150)))));
                await settle();
                let measured = await collectRhythm(page);
                await settle();
                const again = await collectRhythm(page);
                if (Math.abs(again.insetTop - measured.insetTop) > 1 || Math.abs(again.insetBottom - measured.insetBottom) > 1) measured = again;
                rows.push({ cType: entry.cType, group: entry.group, width, ...measured });
            } catch (error) {
                rows.push({ cType: entry.cType, group: entry.group, width, error: String(error.message ?? error).slice(0, 160) });
            } finally {
                await page.close();
            }
        }
    }));
    await context.close();
}
await browser.close();

const median = (values) => {
    const sorted = [...values].sort((a, b) => a - b);
    return sorted.length === 0 ? 0 : sorted[Math.floor(sorted.length / 2)];
};
const dist = (values) => {
    const counter = new Map();
    for (const value of values) counter.set(value, (counter.get(value) ?? 0) + 1);
    return [...counter.entries()].sort((a, b) => b[1] - a[1]).slice(0, 10).map(([v, n]) => `${v}×${n}`).join('  ');
};

const outliers = [];
for (const width of WIDTHS) {
    const at = rows.filter((r) => r.width === width && !r.error && !RHYTHM_EXCEPTIONS.has(r.cType));
    const pads = at.filter((r) => !HERO_RHYTHM(r.cType)).map((r) => Math.round(r.padTop));
    const insetTops = at.map((r) => Math.round(r.insetTop));
    const insetBottoms = at.map((r) => Math.round(r.insetBottom));
    const target = median(pads);
    const tokens = at.find((r) => r.sectionY > 0);
    console.log(`\n@${width}px  (${at.length} elements, exceptions excluded; --d-section-y ${tokens?.sectionY ?? '?'}px, --d-hero-y ${tokens?.heroY ?? '?'}px)`);
    console.log(`  padding-top   ${dist(pads)}`);
    console.log(`  padding-bottom ${dist(at.map((r) => Math.round(r.padBottom)))}`);
    console.log(`  inset top     ${dist(insetTops.map((v) => Math.round(v / 4) * 4))}`);
    console.log(`  inset bottom  ${dist(insetBottoms.map((v) => Math.round(v / 4) * 4))}`);
    for (const row of at) {
        const off = [];
        // Sections breathe with --d-section-y, heroes and page headers with
        // --d-hero-y; the median stands in where the page has no token.
        const expected = HERO_RHYTHM(row.cType) ? (row.heroY || target) : (row.sectionY || target);
        const note = RHYTHM_NOTES[row.cType] ?? {};
        if (!note.top && Math.abs(row.insetTop - expected) > TOLERANCE) off.push(`top ${Math.round(row.insetTop)}`);
        if (!note.bottom && Math.abs(row.insetBottom - expected) > TOLERANCE) off.push(`bottom ${Math.round(row.insetBottom)}`);
        if (off.length > 0) {
            outliers.push({ cType: row.cType, width, target: expected, padTop: row.padTop, padBottom: row.padBottom, insetTop: row.insetTop, insetBottom: row.insetBottom, first: row.first, last: row.last, whyTop: row.whyTop, whyBottom: row.whyBottom, detail: off.join(', ') });
        }
    }
}

const errors = rows.filter((r) => r.error);
console.log(`\n${errors.length} render errors`);
for (const row of errors) console.log(`  ${row.cType} @${row.width}: ${row.error}`);
console.log(`\n${outliers.length} inset outliers (target = --d-section-y, or --d-hero-y for heroes and page headers; tolerance ${TOLERANCE}px)`);
for (const o of outliers.sort((a, b) => a.cType.localeCompare(b.cType) || a.width - b.width)) {
    console.log(`  ${o.cType.padEnd(40)} @${String(o.width).padEnd(5)} pad ${Math.round(o.padTop)}/${Math.round(o.padBottom)}  ${o.detail}   first: ${o.first}   last: ${o.last}`);
    if (o.detail.includes('top') && o.whyTop) console.log(`      ↳ top: ${o.whyTop}`);
    if (o.detail.includes('bottom') && o.whyBottom) console.log(`      ↳ bottom: ${o.whyBottom}`);
}

mkdirSync(dirname(OUT), { recursive: true });
writeFileSync(OUT, JSON.stringify({ rows, outliers }, null, 2));
console.log(`\nDetail: ${OUT}`);
process.exitCode = outliers.length === 0 && errors.length === 0 ? 0 : 1;
