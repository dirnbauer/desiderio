#!/usr/bin/env node
/**
 * Captures the real screens the product film shows (Build/Film/film.html).
 *
 *   node Build/Scripts/capture-film-assets.mjs --base https://webconsulting-typo3-lab.ddev.site
 *
 * Writes into Build/Film/assets/ (git ignores it; the film is rendered from
 * it by Build/Scripts/render-film.mjs):
 *   charts/charts-10-<mode>-<preset>.png   the seed-runs chart in every preset, and dark mode for four of them
 *   preset-colors.json                     each preset's --primary, for the swatch next to its name
 *   lang/home-<lang>.png                   the homepage in English, German, Chinese and Hungarian
 *   wall/NN.jpg                            48 content elements from the catalog, as 480 × 300 tiles
 *
 * Presets and dark mode are applied the way Build/VisualQa does it; motion is
 * stopped and scroll-reveal content forced visible, so nothing is caught
 * half faded. Needs a seeded showcase site.
 */

import { chromium } from '@playwright/test';
import { mkdirSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { applyPreset, primeMode, settleFonts } from '../VisualQa/lib/state.mjs';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const OUT = join(ROOT, 'Build/Film/assets');
const base = process.argv.find((arg) => arg.startsWith('--base='))?.split('=')[1]
    ?? (process.argv.includes('--base') ? process.argv[process.argv.indexOf('--base') + 1] : 'https://webconsulting-typo3-lab.ddev.site');

const PRESETS = ['b0', 'b4hb38Fyj', 'b3IWPgRwnI', 'b6G5977cw', 'b27GcrRo', 'aurora', 'marine', 'forest', 'ember', 'bloom', 'lagoon', 'gold', 'midnight', 'blossom', 'citrus'];
const DARK = ['b0', 'aurora', 'midnight', 'ember'];
const LANGUAGES = { en: '/', de: '/de/', zh: '/zh/', hu: '/hu/' };
const CATALOG = ['hero-landing-intros', 'features-benefits', 'data-dashboards', 'plans-pricing', 'trust-social-proof', 'people-team', 'leads-conversion', 'content-editorial', 'navigation-wayfinding', 'footers-utility-areas'];
const CALM = '*,*::before,*::after{animation:none!important;transition:none!important;caret-color:transparent!important}'
    + '[class*="reveal"],[data-astro-reveal]{opacity:1!important;transform:none!important}';
const VIEWPORT = { width: 1600, height: 1000 };

for (const dir of ['charts', 'lang', 'wall']) {
    mkdirSync(join(OUT, dir), { recursive: true });
}
const browser = await chromium.launch();

async function context(mode, scale = 1) {
    const created = await browser.newContext({ viewport: VIEWPORT, ignoreHTTPSErrors: true, deviceScaleFactor: scale });
    await primeMode(created, mode);
    return created;
}

async function open(page, path, { calm = true } = {}) {
    await page.goto(base + path, { waitUntil: 'networkidle', timeout: 120000 });
    if (calm) {
        await page.addStyleTag({ content: CALM });
    }
    await settleFonts(page);
    // Lazy images load on the way down.
    await page.evaluate(async () => {
        for (let y = 0; y < Math.min(document.body.scrollHeight, 8000); y += 600) {
            window.scrollTo(0, y);
            await new Promise((resolve) => setTimeout(resolve, 40));
        }
        window.scrollTo(0, 0);
    });
}

// 1. The chart in every preset, light and (for four) dark. The chart draws in,
//    so this page keeps its motion and waits for it.
const colors = {};
for (const mode of ['light', 'dark']) {
    const ctx = await context(mode);
    const page = await ctx.newPage();
    await open(page, '/content-types/data-dashboards/', { calm: false });
    for (const preset of mode === 'light' ? PRESETS : DARK) {
        await applyPreset(page, preset);
        await page.evaluate(() => {
            const chart = document.querySelector('section.chart-section');
            window.scrollTo(0, chart.getBoundingClientRect().top + window.scrollY - 70);
        });
        await page.waitForTimeout(1600);
        await page.screenshot({ path: join(OUT, `charts/charts-10-${mode}-${preset}.png`) });
        if (mode === 'light') {
            colors[preset] = await page.evaluate(() => getComputedStyle(document.body).getPropertyValue('--primary').trim());
        }
    }
    console.log(`charts ${mode}`);
    await ctx.close();
}
writeFileSync(join(OUT, 'preset-colors.json'), `${JSON.stringify(colors, null, 1)}\n`);

// 2. The homepage in four languages.
{
    const ctx = await context('light');
    const page = await ctx.newPage();
    for (const [code, path] of Object.entries(LANGUAGES)) {
        await open(page, path);
        await page.screenshot({ path: join(OUT, `lang/home-${code}.png`) });
    }
    console.log('languages');
    await ctx.close();
}

// 3. The element wall: per catalog page the six elements with the most
//    pictures, charts and icons, shot at 0.3 scale (480 × 300 tiles).
{
    const ctx = await context('light', 0.3);
    const page = await ctx.newPage();
    let tile = 0;
    for (const category of CATALOG) {
        await open(page, `/content-types/${category}/`);
        const picks = await page.evaluate(() => [...document.querySelectorAll('a[id^="c"]')]
            .map((anchor, index) => {
                const element = anchor.nextElementSibling;
                const box = element?.getBoundingClientRect();
                const richness = element ? element.querySelectorAll('img, svg, canvas, h2, h3').length : 0;
                return { index, richness, height: box?.height ?? 0, width: box?.width ?? 0 };
            })
            .filter((item) => item.height >= 260 && item.width >= 900)
            .sort((a, b) => b.richness - a.richness)
            .slice(0, 6)
            .map((item) => item.index));
        for (const index of picks) {
            await page.evaluate((i) => {
                const element = document.querySelectorAll('a[id^="c"]')[i].nextElementSibling;
                window.scrollTo(0, element.getBoundingClientRect().top + window.scrollY - 78);
            }, index);
            await page.waitForTimeout(120);
            await page.screenshot({ path: join(OUT, `wall/${String(tile).padStart(2, '0')}.jpg`), type: 'jpeg', quality: 86, clip: { x: 0, y: 78, width: 1600, height: 1000 - 78 } });
            tile++;
        }
    }
    console.log(`wall: ${tile} tiles`);
    await ctx.close();
}

await browser.close();
