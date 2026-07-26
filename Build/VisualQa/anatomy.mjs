#!/usr/bin/env node
/**
 * One-shot anatomy survey: renders every element once at 1440/light and
 * reports layout outliers against the catalog's own dominant conventions.
 *
 * Separate from run.mjs on purpose — this is a survey (what IS the
 * distribution, who deviates), not a regression gate. Its findings feed
 * fixes; once a convention is worth enforcing it graduates into
 * assertions.mjs or the static audit.
 *
 *   node Build/VisualQa/anatomy.mjs --urls /tmp/preview-urls.json
 */

import { chromium } from 'playwright';
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { paintOpaqueBackground, primeMode, settleFonts } from './lib/state.mjs';
import { collectAnatomy } from './lib/anatomy.mjs';

const HERE = dirname(fileURLToPath(import.meta.url));
const argv = process.argv.slice(2);
const URLS = argv[argv.indexOf('--urls') + 1];
if (!URLS || argv.indexOf('--urls') === -1) {
    console.error('Pass --urls <file>');
    process.exit(2);
}

const entries = JSON.parse(readFileSync(URLS, 'utf8'));
const browser = await chromium.launch();
const context = await browser.newContext({
    viewport: { width: 1440, height: 1400 },
    ignoreHTTPSErrors: true,
    reducedMotion: 'reduce',
});
await primeMode(context, 'light');

const rows = [];
const queue = [...entries];
await Promise.all(Array.from({ length: 6 }, async () => {
    for (;;) {
        const entry = queue.shift();
        if (!entry) return;
        const page = await context.newPage();
        try {
            const response = await page.goto(entry.url, { waitUntil: 'load', timeout: 45_000 });
            // A 500 renders a perfectly loadable error page — without this
            // guard the survey counts it as "no anatomy" and stays green.
            if (response && !response.ok()) {
                rows.push({ cType: entry.cType, group: entry.group, error: `HTTP ${response.status()}` });
                continue;
            }
            await paintOpaqueBackground(page);
            await settleFonts(page);
            const anatomy = await collectAnatomy(page);
            rows.push({ cType: entry.cType, group: entry.group, ...anatomy });
        } catch (error) {
            rows.push({ cType: entry.cType, group: entry.group, error: String(error.message ?? error).slice(0, 120) });
        } finally {
            await page.close();
        }
    }
}));
await browser.close();

// ---- distributions ------------------------------------------------------
const dist = (values) => {
    const counter = new Map();
    for (const value of values) counter.set(value, (counter.get(value) ?? 0) + 1);
    return [...counter.entries()].sort((a, b) => b[1] - a[1]);
};

const padTops = dist(rows.filter((r) => r.padBlock).map((r) => Math.round(r.padBlock[0])));
const headings = dist(rows.filter((r) => r.headingPx).map((r) => Math.round(r.headingPx)));
const aligns = dist(rows.filter((r) => r.headerAlign).map((r) => r.headerAlign));

console.log(`\n${rows.length} elements surveyed`);
console.log('\nsection padding-top px:', padTops.slice(0, 8).map(([v, n]) => `${v}×${n}`).join('  '));
console.log('heading px:            ', headings.slice(0, 8).map(([v, n]) => `${v}×${n}`).join('  '));
console.log('header alignment:      ', aligns.map(([v, n]) => `${v}×${n}`).join('  '));

const findings = [];
for (const row of rows) {
    if (row.error) {
        findings.push({ cType: row.cType, check: 'render-failed', detail: row.error });
    }
    for (const check of row.checks ?? []) {
        findings.push({ cType: row.cType, ...check });
    }
}
console.log(`\n${findings.length} anatomy findings`);
const byCheck = dist(findings.map((f) => f.check));
for (const [check, count] of byCheck) console.log(`  ${String(count).padStart(4)}  ${check}`);

writeFileSync(join(HERE, 'report', 'anatomy.json'), JSON.stringify({ rows, findings }, null, 2));
console.log(`\nDetail: ${join(HERE, 'report', 'anatomy.json')}`);
process.exitCode = findings.length === 0 ? 0 : 1;
