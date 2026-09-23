#!/usr/bin/env node
/**
 * Re-captures the frontend screenshots the showcase pages show.
 *
 *   node Build/Scripts/capture-showcase-screenshots.mjs --base https://webconsulting-typo3-lab.ddev.site [--only <name>] [--dry-run]
 *
 * The showcase quotes its own pages in screenshots (the home gallery, the
 * theme steps, the feature tiles), so after the copy changes the pictures
 * must be taken again from the seeded site, or they show the old text.
 *
 * Each shot gets a new file name with a content hash
 * (`frontend-gallery-parallax-hero-3f2a9c1b.png`), because ExtensionFalSeeder
 * imports by file name and never re-imports a name it already has. The
 * script prints the old → new names; `--rewrite` also replaces them in the
 * showcase data classes, the fixtures and the og:image TypoScript, and
 * deletes the old files.
 *
 * Presets and dark mode are applied the way Build/VisualQa does it
 * (lib/state.mjs); animations are stopped and scroll-reveal content forced
 * visible, or a parallax hero is captured half faded.
 */

import { chromium } from '@playwright/test';
import { createHash } from 'node:crypto';
import { existsSync, readdirSync, readFileSync, statSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

import { applyPreset, primeMode, settleFonts } from '../VisualQa/lib/state.mjs';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const OUT = join(ROOT, 'Resources/Public/Styleguide/Frontend');

/**
 * What each screenshot shows. `selector` crops to one element; without it
 * the shot is a viewport of the page: the first one, or the one that starts
 * just above the `scrollTo` element.
 */
const SHOTS = [
    { name: 'frontend-gallery-parallax-hero', path: '/content-types/hero-landing-intros', selector: '.hero-parallax', preset: 'forest', width: 1440 },
    { name: 'frontend-gallery-bento-features', path: '/content-types/features-benefits', selector: '.feature-bento', width: 1440 },
    { name: 'frontend-gallery-toggle-pricing', path: '/content-types/plans-pricing', selector: '.pricing-toggle', width: 1440 },
    { name: 'frontend-gallery-testimonial-wall', path: '/content-types/trust-social-proof', selector: '.testimonial-wall', width: 1440 },
    { name: 'frontend-gallery-demo-request', path: '/content-types/leads-conversion', selector: '.demo-request', width: 1440 },
    { name: 'frontend-hero-lagoon', path: '/', preset: 'lagoon', width: 1600, height: 1000 },
    { name: 'frontend-dashboards-forest', path: '/content-types/data-dashboards', preset: 'forest', width: 1600, height: 1000 },
    { name: 'frontend-features-ember-mobile', path: '/content-types/features-benefits', preset: 'ember', width: 390, height: 1000 },
    { name: 'frontend-pricing-midnight-dark', path: '/content-types/plans-pricing', preset: 'midnight', mode: 'dark', width: 1600, height: 1000 },
    { name: 'frontend-themes-overview', path: '/themes', scrollTo: '.preset-grid', width: 1600, height: 1000 },
];

/** Files that quote screenshot names. */
const REFERENCES = [
    'Classes/Data/StyleguideShowcasePages.php',
    'Classes/Data/Showcase',
    'ContentBlocks/ContentElements',
    // The site-wide og:image, and the registry's copy of that TypoScript.
    'Configuration/Sets/Desiderio/setup.typoscript',
    'Resources/Public/ShadcnRegistry',
];

const STILL = `
    *, *::before, *::after { animation: none !important; transition: none !important; }
    [class*="reveal"], [class*="fade"], .hero-parallax * { opacity: 1 !important; transform: none !important; }
`;

const args = process.argv.slice(2);
const flag = (name, fallback = null) => {
    const index = args.indexOf(`--${name}`);
    return index === -1 ? fallback : (args[index + 1] ?? fallback);
};
const base = (flag('base', 'https://webconsulting-typo3-lab.ddev.site') ?? '').replace(/\/$/, '');
const only = flag('only');
const dryRun = args.includes('--dry-run');
const rewrite = args.includes('--rewrite');

const browser = await chromium.launch();
const renamed = [];
try {
    for (const shot of SHOTS.filter((s) => only === null || s.name === only)) {
        const context = await browser.newContext({
            ignoreHTTPSErrors: true,
            viewport: { width: shot.width, height: shot.height ?? 1000 },
            deviceScaleFactor: 2,
            reducedMotion: 'reduce',
        });
        await primeMode(context, shot.mode ?? 'light');
        const page = await context.newPage();
        const response = await page.goto(base + shot.path, { waitUntil: 'networkidle', timeout: 90000 });
        if (!response || response.status() !== 200) {
            throw new Error(`${shot.name}: ${base + shot.path} answered ${response?.status() ?? 'nothing'}`);
        }
        await page.addStyleTag({ content: STILL });
        if (shot.preset) {
            await applyPreset(page, shot.preset);
        } else {
            await settleFonts(page);
        }
        // Scroll once through the page so lazy images load, then back to the top.
        await page.evaluate(async () => {
            for (let y = 0; y < document.body.scrollHeight; y += 600) {
                window.scrollTo(0, y);
                await new Promise((resolve) => setTimeout(resolve, 40));
            }
            window.scrollTo(0, 0);
        });
        if (shot.scrollTo) {
            await page.locator(shot.scrollTo).first().evaluate((el) => window.scrollTo(0, el.getBoundingClientRect().top + window.scrollY - 140));
        }
        await page.waitForTimeout(300);

        const png = shot.selector
            ? await page.locator(shot.selector).first().screenshot({ type: 'png' })
            : await page.screenshot({ type: 'png', fullPage: false });
        const hash = createHash('sha256').update(png).digest('hex').slice(0, 8);
        const file = `${shot.name}-${hash}.png`;
        if (!dryRun) {
            writeFileSync(join(OUT, file), png);
        }
        const previous = readdirSync(OUT).filter((f) => f !== file && (f === `${shot.name}.png` || new RegExp(`^${shot.name}-[0-9a-f]{8}\\.png$`).test(f)));
        renamed.push({ from: previous, to: file });
        console.log(`${shot.name}: ${file}${previous.length ? ` (replaces ${previous.join(', ')})` : ''}`);
        await context.close();
    }
} finally {
    await browser.close();
}

if (rewrite && !dryRun) {
    const files = [];
    const walk = (path) => {
        const absolute = join(ROOT, path);
        if (!existsSync(absolute)) {
            return;
        }
        if (!statSync(absolute).isDirectory()) {
            if (/\.(php|json|typoscript)$/.test(absolute)) {
                files.push(absolute);
            }
            return;
        }
        for (const entry of readdirSync(absolute, { withFileTypes: true })) {
            walk(join(path, entry.name));
        }
    };
    REFERENCES.forEach(walk);
    for (const file of files) {
        let content = readFileSync(file, 'utf8');
        let changed = false;
        for (const { from, to } of renamed) {
            for (const old of from) {
                if (content.includes(old)) {
                    content = content.split(old).join(to);
                    changed = true;
                }
            }
        }
        if (changed) {
            writeFileSync(file, content);
            console.log(`updated ${file.slice(ROOT.length + 1)}`);
        }
    }
    for (const { from } of renamed) {
        for (const old of from) {
            unlinkSync(join(OUT, old));
        }
    }
}
