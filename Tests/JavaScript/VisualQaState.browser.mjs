import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';
import { chromium } from 'playwright';
import { applyPreset, paintOpaqueBackground } from '../../Build/VisualQa/lib/state.mjs';

const root = new URL('../../', import.meta.url);
const files = [
    'Resources/Public/Css/shadcn-theme.css',
    'Resources/Public/Css/desiderio-tailwind.css',
    'Resources/Public/Css/desiderio.css',
    'ContentBlocks/ContentElements/announcement-bar/assets/frontend.css',
];
const styles = await Promise.all(files.map(async (file) => `<style>${await readFile(new URL(file, root), 'utf8')}</style>`));
const html = `<!doctype html><html><head><meta charset="utf-8">${styles.join('')}</head>
<body data-shadcn-preset="b6G5977cw"><section class="desiderio-section bg-background announcement-bar announcement-bar--accent" data-d-section="true">
<div class="desiderio-container"><div class="announcement-bar__inner"><p class="announcement-bar__message"><span>Announcement contrast regression</span></p></div></div>
</section></body></html>`;

test('QA preset measurements never retain inherited ink from the previous scheme', async () => {
    const browser = await chromium.launch();
    try {
        const page = await browser.newPage({ viewport: { width: 1440, height: 1200 }, reducedMotion: 'reduce' });
        await page.setContent(html);
        // Establish the same light olive ink the preview paints before its
        // DOMContentLoaded theme script switches to dark. Reduced-motion CSS
        // leaves .01ms transitions on every ancestor, including this span.
        await page.evaluate(() => getComputedStyle(document.querySelector('p > span')).color);
        await paintOpaqueBackground(page);
        await page.evaluate(() => document.documentElement.classList.add('dark'));
        await applyPreset(page, 'b0');

        const frames = await page.evaluate(async () => {
            const span = document.querySelector('p > span');
            const section = document.querySelector('section');
            const samples = [];
            for (let i = 0; i < 9; i++) {
                samples.push({ color: getComputedStyle(span).color, background: getComputedStyle(section).backgroundColor });
                await new Promise(requestAnimationFrame);
            }
            return samples;
        });
        for (const [frame, sample] of frames.entries()) {
            assert.equal(sample.color, 'oklch(0.985 0 0)', `frame ${frame} retained stale light-scheme ink`);
            assert.equal(sample.background, 'oklch(0.269 0 0)', `frame ${frame} retained the previous surface`);
        }
    } finally {
        await browser.close();
    }
});
