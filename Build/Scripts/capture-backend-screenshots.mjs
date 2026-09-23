#!/usr/bin/env node
/**
 * Re-captures the backend screenshots the showcase shows (page module,
 * Visual Editor) from the seeded lab.
 *
 *   node Build/Scripts/capture-backend-screenshots.mjs [--base https://webconsulting-typo3-lab.ddev.site] [--only <name>] [--rewrite]
 *
 * A backend needs a login, and this script never handles a password: it opens
 * a visible browser window at the backend login and waits until you have
 * signed in there yourself. The session is kept in var/playwright-backend, so
 * a second run starts signed in.
 *
 * Shots are cropped to the module area (no top bar, no module menu), so no
 * user name or workspace badge ends up in them. File names carry a content
 * hash like the frontend shots (see capture-showcase-screenshots.mjs);
 * --rewrite points the showcase data and fixtures at the new names and
 * deletes the old files.
 */

import { chromium } from '@playwright/test';
import { createHash } from 'node:crypto';
import { existsSync, mkdirSync, readdirSync, readFileSync, statSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const OUT = join(ROOT, 'Resources/Public/Styleguide/Backend');
const PROFILE = join(ROOT, '..', '..', 'var', 'playwright-backend');

/** What each shot shows; `settle` is extra time for the module to finish. */
const SHOTS = [
    { name: 'backend-page-module-hero', path: '/typo3/module/web/layout?id=669', settle: 3000 },
    { name: 'backend-visual-editor', path: '/typo3/module/web/edit?id=669', settle: 9000 },
];

const REFERENCES = ['Classes/Data/StyleguideShowcasePages.php', 'Classes/Data/Showcase', 'ContentBlocks/ContentElements'];

const args = process.argv.slice(2);
const flag = (name, fallback = null) => {
    const index = args.indexOf(`--${name}`);
    return index === -1 ? fallback : (args[index + 1] ?? fallback);
};
const base = (flag('base', 'https://webconsulting-typo3-lab.ddev.site') ?? '').replace(/\/$/, '');
const only = flag('only');
const rewrite = args.includes('--rewrite');

mkdirSync(PROFILE, { recursive: true });
const context = await chromium.launchPersistentContext(PROFILE, {
    headless: false,
    ignoreHTTPSErrors: true,
    viewport: { width: 1600, height: 1000 },
    deviceScaleFactor: 2,
});
const page = context.pages()[0] ?? (await context.newPage());

await page.goto(`${base}/typo3/`, { waitUntil: 'domcontentloaded' });
if (!/\/typo3\/(main|module)/.test(page.url())) {
    console.log('Sign in to the TYPO3 backend in the window that opened; the capture starts afterwards.');
    await page.waitForURL(/\/typo3\/(main|module)/, { timeout: 10 * 60 * 1000 });
}

const renamed = [];
try {
    for (const shot of SHOTS.filter((s) => only === null || s.name === only)) {
        await page.goto(base + shot.path, { waitUntil: 'networkidle', timeout: 120000 });
        await page.waitForTimeout(shot.settle);
        // The module area: everything right of the module menu, below the top bar.
        const clip = await page.evaluate(() => {
            const area = document.querySelector('.scaffold-content, .t3js-scaffold-content');
            const rect = area?.getBoundingClientRect();
            return rect && rect.width > 0 ? { x: rect.x, y: rect.y, width: rect.width, height: rect.height } : null;
        });
        if (clip === null) {
            throw new Error(`${shot.name}: no module area found on ${page.url()}`);
        }
        const png = await page.screenshot({ type: 'png', clip });
        const hash = createHash('sha256').update(png).digest('hex').slice(0, 8);
        const file = `${shot.name}-${hash}.png`;
        writeFileSync(join(OUT, file), png);
        const previous = readdirSync(OUT).filter((f) => f !== file && (f === `${shot.name}.png` || new RegExp(`^${shot.name}-[0-9a-f]{8}\\.png$`).test(f)));
        renamed.push({ from: previous, to: file });
        console.log(`${shot.name}: ${file}${previous.length ? ` (replaces ${previous.join(', ')})` : ''}`);
    }
} finally {
    await context.close();
}

if (rewrite) {
    const files = [];
    const walk = (path) => {
        const absolute = join(ROOT, path);
        if (!existsSync(absolute)) {
            return;
        }
        if (!statSync(absolute).isDirectory()) {
            if (/\.(php|json)$/.test(absolute)) {
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
