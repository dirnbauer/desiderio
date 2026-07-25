#!/usr/bin/env node
/**
 * Visual QA sweep over every element library record.
 *
 * Scope, and why it is not the full cross-product:
 * 244 elements x 15 presets x 2 modes x 3 widths is ~22 000 renders, and most
 * of it would re-measure the same token pairs. Build/Scripts/audit-theme-contrast.php
 * already proves every surface/text pair in all 15 presets and both modes
 * analytically, and content elements may not hardcode colour, so the render
 * sweep only has to cover what tokens cannot express: layout at real widths,
 * real font metrics, and composed colours.
 *
 *   T1  every element x b0            x {light,dark} x {390,768,1440}
 *   T2  every element x b6G5977cw     x light        x 390       (JetBrains Mono, widest glyphs)
 *   T3  a computed subset x 15 presets x {light,dark} x 1440
 *
 * 390 sits below the 480px breakpoint so all four max-width tiers fire at once.
 * 768 is the exact width where `max-width:768px` (71 rules) and `min-width:768px`
 * (22 rules) both match, which is the single most fragile width in the catalog.
 *
 * Usage:
 *   node Build/VisualQa/run.mjs --urls /tmp/preview-urls.json [--tier t1|t2|t3|all]
 *                               [--only <cType substring>] [--shots] [--concurrency 6]
 */

import { chromium } from 'playwright';
import AxeBuilder from '@axe-core/playwright';
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { applyPreset, assertThemeApplied, paintOpaqueBackground, primeMode, settleFonts, PRESET_DEFAULT } from './lib/state.mjs';
import { collectFindings, watchPage } from './lib/assertions.mjs';
import { writeReport } from './lib/report.mjs';

const HERE = dirname(fileURLToPath(import.meta.url));
const OUT = join(HERE, 'report');

const argv = process.argv.slice(2);
const flag = (name, fallback = null) => {
    const i = argv.indexOf(`--${name}`);
    return i === -1 ? fallback : argv[i + 1];
};
const has = (name) => argv.includes(`--${name}`);

const URLS = flag('urls');
const TIER = (flag('tier', 'all') ?? 'all').toLowerCase();
const ONLY = flag('only');
const SHOTS = has('shots');
const CONCURRENCY = Number(flag('concurrency', '6'));

if (!URLS) {
    console.error('Pass --urls <file>. Produce it with:\n  ddev typo3 desiderio:library:urls --site=desiderio --json > /tmp/preview-urls.json');
    process.exit(2);
}

/** The 14 presets that ship their own token block, plus the `:root` base. */
const PRESETS = [
    'b0', 'b4hb38Fyj', 'b3IWPgRwnI', 'b6G5977cw', 'b27GcrRo',
    'aurora', 'marine', 'forest', 'ember', 'bloom',
    'lagoon', 'gold', 'midnight', 'blossom', 'citrus',
];
const MONO_PRESET = 'b6G5977cw';

let entries = JSON.parse(readFileSync(URLS, 'utf8'));
if (ONLY) entries = entries.filter((e) => e.cType.includes(ONLY));
if (entries.length === 0) {
    console.error('No entries to check.');
    process.exit(2);
}

/**
 * T3 subset, computed rather than hand-picked so it tracks the catalog:
 * every element whose CSS composes a colour (color-mix over a preset-varying
 * token) plus every chart element (they stack --chart-1..5 on one surface).
 * Those are exactly the cases the analytical token audit cannot decide.
 */
function presetSubset(all) {
    const byCType = new Map(all.map((e) => [e.cType, e]));
    const picked = new Set();
    for (const [cType] of byCType) {
        if (/chart|stats|metric|gauge|progress/.test(cType)) picked.add(cType);
    }
    for (const [cType] of byCType) {
        if (picked.size >= 32) break;
        if (/hero|pricing|testimonial|feature|badge|alert|callout/.test(cType)) picked.add(cType);
    }
    return [...picked].slice(0, 32).map((cType) => byCType.get(cType)).filter(Boolean);
}

/** @returns {{entry:object,preset:string,mode:string,width:number,tier:string}[]} */
function buildMatrix() {
    const jobs = [];
    const want = (tier) => TIER === 'all' || TIER === tier;

    if (want('t1')) {
        for (const entry of entries) {
            for (const mode of ['light', 'dark']) {
                for (const width of [390, 768, 1440]) {
                    jobs.push({ entry, preset: PRESET_DEFAULT, mode, width, tier: 't1' });
                }
            }
        }
    }
    if (want('t2')) {
        for (const entry of entries) {
            jobs.push({ entry, preset: MONO_PRESET, mode: 'light', width: 390, tier: 't2' });
        }
    }
    if (want('t3')) {
        for (const entry of presetSubset(entries)) {
            for (const preset of PRESETS) {
                for (const mode of ['light', 'dark']) {
                    jobs.push({ entry, preset, mode, width: 1440, tier: 't3' });
                }
            }
        }
    }
    return jobs;
}

const jobs = buildMatrix();
// Group by (mode, width) so a browser context is created once per combination
// and each page load is reused across every preset at that geometry.
const buckets = new Map();
for (const job of jobs) {
    const key = `${job.mode}|${job.width}`;
    if (!buckets.has(key)) buckets.set(key, []);
    buckets.get(key).push(job);
}

console.log(`${entries.length} elements, ${jobs.length} renders across ${buckets.size} viewport/mode combinations`);
mkdirSync(OUT, { recursive: true });
if (SHOTS) mkdirSync(join(OUT, 'shots'), { recursive: true });

const results = [];
const browser = await chromium.launch();
let done = 0;

for (const [key, bucketJobs] of buckets) {
    const [mode, width] = key.split('|');
    const context = await browser.newContext({
        viewport: { width: Number(width), height: 1200 },
        ignoreHTTPSErrors: true,
        reducedMotion: 'reduce',
        deviceScaleFactor: 1,
    });
    await primeMode(context, mode);

    // Same URL may appear with several presets; render it once and re-check.
    const byUrl = new Map();
    for (const job of bucketJobs) {
        if (!byUrl.has(job.entry.url)) byUrl.set(job.entry.url, []);
        byUrl.get(job.entry.url).push(job);
    }

    const urlList = [...byUrl.entries()];
    const workers = Array.from({ length: Math.min(CONCURRENCY, urlList.length) }, async () => {
        for (;;) {
            const next = urlList.shift();
            if (!next) return;
            const [url, urlJobs] = next;
            const page = await context.newPage();
            const pageErrors = watchPage(page);

            try {
                await page.goto(url, { waitUntil: 'load', timeout: 45_000 });
                await paintOpaqueBackground(page);
                await settleFonts(page);
                await assertThemeApplied(page);

                for (const job of urlJobs) {
                    // Always set it, including for b0: b0 ships no token block
                    // of its own, so the attribute simply lets :root through -
                    // and setting it unconditionally means a page reused across
                    // presets never keeps the previous one.
                    await applyPreset(page, job.preset);
                    const findings = await collectFindings(page);

                    let axeViolations = [];
                    try {
                        const axe = await new AxeBuilder({ page })
                            // Third-party embeds (YouTube, maps) render markup we
                            // neither own nor can fix, and axe reports their
                            // internals as our violations.
                            .exclude('iframe')
                            .withRules(['color-contrast', 'image-alt', 'heading-order', 'link-name', 'button-name'])
                            .analyze();
                        axeViolations = axe.violations.flatMap((violation) =>
                            violation.nodes.slice(0, 3).map((node) => ({
                                check: `a11y:${violation.id}`,
                                selector: Array.isArray(node.target) ? node.target.join(' ') : String(node.target),
                                detail: (node.failureSummary ?? violation.help).split('\n').slice(0, 2).join(' ').slice(0, 220),
                            })),
                        );
                    } catch (error) {
                        axeViolations = [{ check: 'a11y:error', selector: 'page', detail: String(error.message ?? error).slice(0, 160) }];
                    }

                    if (SHOTS) {
                        await page.screenshot({
                            path: join(OUT, 'shots', `${job.entry.cType}__${job.preset}__${job.mode}__${job.width}.png`),
                            fullPage: false,
                        });
                    }

                    results.push({
                        cType: job.entry.cType,
                        group: job.entry.group,
                        uid: job.entry.uid,
                        url,
                        preset: job.preset,
                        mode: job.mode,
                        width: job.width,
                        tier: job.tier,
                        findings: [...findings, ...axeViolations, ...pageErrors.splice(0)],
                    });
                    done++;
                    if (done % 100 === 0) process.stdout.write(`  ${done}/${jobs.length}\n`);
                }
            } catch (error) {
                results.push({
                    cType: urlJobs[0].entry.cType,
                    group: urlJobs[0].entry.group,
                    uid: urlJobs[0].entry.uid,
                    url,
                    preset: urlJobs[0].preset,
                    mode: urlJobs[0].mode,
                    width: urlJobs[0].width,
                    tier: urlJobs[0].tier,
                    findings: [{ check: 'render-failed', selector: 'page', detail: String(error.message ?? error).slice(0, 220) }],
                });
                done += urlJobs.length;
            } finally {
                await page.close();
            }
        }
    });

    await Promise.all(workers);
    await context.close();
}

await browser.close();

const summary = writeReport(OUT, results, { elements: entries.length, renders: jobs.length });
writeFileSync(join(OUT, 'summary.json'), JSON.stringify(summary, null, 2));

console.log(`\n${summary.totals.renders} renders, ${summary.totals.findings} findings across ${summary.totals.elementsWithFindings} elements`);
for (const [check, count] of Object.entries(summary.byCheck).sort((a, b) => b[1] - a[1])) {
    console.log(`  ${String(count).padStart(5)}  ${check}`);
}
console.log(`\nReport: ${join(OUT, 'index.html')}`);

process.exit(summary.totals.findings === 0 ? 0 : 1);
