#!/usr/bin/env node
/**
 * Captures screenshots of an external web app (T3 Ops, Ledger, ...) for the
 * /features and /software pages: the sibling of capture-feature-screenshots.mjs
 * for apps that are not the TYPO3 lab.
 *
 *   node Build/Scripts/capture-app-screenshots.mjs --base <url> --shots <file> [--state <file>] [--only <name-prefix>] [--list]
 *
 * capture-feature-screenshots.mjs cannot do this: its signed-in context probes
 * `<base>/typo3/` for a TYPO3 backend session, and its frontend context is
 * always anonymous. Here every shot gets its own context with the storage
 * state of --state (a signed-in session of the app), unless the shot says
 * `"auth": false`.
 *
 * The state file is made outside this script. With `--login`, the script
 * makes it itself: it opens `login.url` of the shots file, fills
 * `login.username` and `login.password` (selectors) from the environment
 * variables APP_USERNAME and APP_PASSWORD, clicks `login.submit`, waits until
 * the URL no longer contains `login.url`, and saves the state to --state.
 * Credentials never come from arguments or the shots file, and the script
 * never prints them.
 *
 * The shots file: {"shots": [...]}, optionally "css" (added to every shot)
 * and "login" (see --login).
 *
 * A shot: name (a "frontend-" prefix files it under Frontend, the rest under
 * Backend), url (a path below --base, or an absolute URL), auth (default
 * true), viewport ("WxH", default 1440x900), colorScheme (light or dark),
 * locale (context locale for Intl formats, e.g. "de-AT"; a top-level "locale"
 * in the shots file also starts Chromium with --lang, which native date
 * inputs need),
 * localStorage ({key: value}, written before the page's own scripts run, for
 * apps that keep their theme there), css, steps, settle (ms, default 800),
 * clip ("viewport", a selector or a list of selectors), padding, aspect
 * ("16:10" default, "none") and anchor (top, center, bottom). A step is one of
 * click, hover, fill [selector, value], select [selector, value], check,
 * press, waitFor, goto, evaluate, scroll or scrollTo (a selector brought to
 * the top of the viewport, with `offset` px of room above it), with optional,
 * wait and timeout. Selectors are CSS or "text=<visible text>".
 *
 * Crop, 16:10 fitting, twice the CSS resolution, WebP via cwebp (quality 92)
 * and the content-hashed file name follow capture-feature-screenshots.mjs, so
 * its --relink picks the files up like its own.
 */

import { chromium } from 'playwright';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');

const args = process.argv.slice(2);
const flag = (name, fallback = null) => {
    const index = args.indexOf(`--${name}`);
    return index === -1 ? fallback : (args[index + 1] ?? fallback);
};
const base = (flag('base', '') ?? '').replace(/\/$/, '');
const only = flag('only');
const state = flag('state');
const shotList = flag('shots');
if (!shotList) {
    throw new Error('--shots <file> is required.');
}
const definition = JSON.parse(readFileSync(shotList, 'utf8'));
const shots = definition.shots.filter((shot) => only === null || shot.name.startsWith(only));
if (args.includes('--list')) {
    shots.forEach((shot) => console.log(`${shot.name}\t${shot.url}`));
    process.exit(0);
}

/** Freeze motion, so a shot never catches an element halfway through its entrance. */
const STILL = '*,*::before,*::after{animation:none!important;transition:none!important;caret-color:transparent!important}'
    // The Next.js development indicator.
    + 'nextjs-portal{display:none!important}';

const url = (path) => (/^https?:\/\//.test(path) ? path : base + path);
const workDir = mkdtempSync(join(tmpdir(), 'app-shots-'));
// Native date and month inputs follow the browser's UI language, not the context locale.
const browser = await chromium.launch({ args: definition.locale ? [`--lang=${definition.locale}`] : [] });

try {
    if (args.includes('--login')) {
        await login();
    }
    for (const shot of shots) {
        const [width, height] = (shot.viewport ?? '1440x900').split('x').map(Number);
        const auth = shot.auth !== false;
        if (auth && (!state || !existsSync(state))) {
            throw new Error(`${shot.name}: needs a signed-in state; pass --state <file> (or --login).`);
        }
        const context = await browser.newContext({
            deviceScaleFactor: 2,
            viewport: { width, height },
            colorScheme: shot.colorScheme ?? 'light',
            locale: shot.locale ?? definition.locale,
            reducedMotion: 'reduce',
            storageState: auth ? state : undefined,
        });
        if (shot.localStorage) {
            await context.addInitScript((entries) => {
                for (const [key, value] of Object.entries(entries)) {
                    window.localStorage.setItem(key, value);
                }
            }, shot.localStorage);
        }
        const page = await context.newPage();
        page.setDefaultTimeout(60000);
        await page.goto(url(shot.url), { waitUntil: shot.waitUntil ?? 'networkidle', timeout: 120000 });
        if (auth && /\/login\b/.test(new URL(page.url()).pathname)) {
            throw new Error(`${shot.name}: the session in ${state} is no longer signed in.`);
        }
        await page.addStyleTag({ content: STILL + (definition.css ?? '') + (shot.css ?? '') });
        for (const step of shot.steps ?? []) {
            try {
                await runStep(page, step);
            } catch (error) {
                if (!step.optional) {
                    throw error;
                }
            }
        }
        await page.waitForTimeout(shot.settle ?? 800);
        const clip = await resolveClip(page, shot);
        const png = await page.screenshot({ type: 'png', clip, animations: 'disabled' });
        const file = save(shot, png);
        await context.close();
        console.log(`${shot.name}: ${file} (${Math.round(clip.width)}x${Math.round(clip.height)} CSS px)`);
    }
} finally {
    await browser.close();
    rmSync(workDir, { recursive: true, force: true });
}

/** Signs in with APP_USERNAME / APP_PASSWORD and saves the session to --state. */
async function login() {
    const form = definition.login;
    const username = process.env.APP_USERNAME;
    const password = process.env.APP_PASSWORD;
    if (!form || !state || !username || !password) {
        throw new Error('--login needs "login" in the shots file, --state, and APP_USERNAME / APP_PASSWORD in the environment.');
    }
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.goto(url(form.url), { waitUntil: 'networkidle', timeout: 120000 });
    await page.locator(form.username).fill(username);
    await page.locator(form.password).fill(password);
    await page.locator(form.submit).click();
    await page.waitForURL((current) => !current.pathname.startsWith(form.url), { timeout: 60000 });
    await context.storageState({ path: state });
    await context.close();
    console.log(`signed in; state saved to ${state}`);
}

/** One step towards the state a shot shows. */
async function runStep(page, step) {
    const locate = (selector) => (selector.startsWith('text=') ? page.getByText(selector.slice(5), { exact: false }).first() : page.locator(selector).first());
    if (step.optional) {
        page.setDefaultTimeout(4000);
    }
    if (step.click) {
        await locate(step.click).click();
    } else if (step.hover) {
        await locate(step.hover).hover();
    } else if (step.fill) {
        await locate(step.fill[0]).fill(step.fill[1]);
    } else if (step.select) {
        await locate(step.select[0]).selectOption(step.select[1]);
    } else if (step.check) {
        await locate(step.check).check();
    } else if (step.press) {
        await page.keyboard.press(step.press);
    } else if (step.waitFor) {
        await locate(step.waitFor).waitFor({ state: 'visible', timeout: step.timeout ?? 30000 });
    } else if (step.goto) {
        await page.goto(url(step.goto), { waitUntil: 'networkidle', timeout: 120000 });
    } else if (step.evaluate) {
        await page.evaluate(step.evaluate);
    } else if (step.scroll) {
        await locate(step.scroll).scrollIntoViewIfNeeded();
    } else if (step.scrollTo) {
        const box = await locate(step.scrollTo).boundingBox();
        if (box === null) {
            throw new Error(`nothing visible matches "${step.scrollTo}"`);
        }
        await page.evaluate((top) => window.scrollBy(0, top), box.y - (step.offset ?? 0));
    }
    page.setDefaultTimeout(60000);
    await page.waitForTimeout(step.wait ?? 300);
}

/** The area to keep: "viewport", one selector or the union of several, with padding. */
async function resolveClip(page, shot) {
    const viewport = page.viewportSize();
    const clip = shot.clip ?? 'viewport';
    if (clip === 'viewport') {
        return fitAspect({ x: 0, y: 0, width: viewport.width, height: viewport.height }, shot, viewport);
    }
    const rects = [];
    for (const selector of [].concat(clip)) {
        const rect = await page.locator(selector).first().boundingBox();
        if (rect === null) {
            throw new Error(`${shot.name}: nothing matches "${selector}" on ${page.url()}`);
        }
        rects.push(rect);
    }
    const pad = shot.padding ?? 16;
    const x = Math.max(0, Math.min(...rects.map((r) => r.x)) - pad);
    const y = Math.max(0, Math.min(...rects.map((r) => r.y)) - pad);
    const right = Math.min(viewport.width, Math.max(...rects.map((r) => r.x + r.width)) + pad);
    const bottom = Math.min(viewport.height, Math.max(...rects.map((r) => r.y + r.height)) + pad);
    return fitAspect({ x, y, width: right - x, height: bottom - y }, shot, viewport);
}

/**
 * Brings a crop to the shot's aspect ratio (16:10 unless it says otherwise),
 * as capture-feature-screenshots.mjs does: a wide crop grows downwards, then
 * upwards; a tall one grows sideways, and whatever is still missing is cut
 * from the bottom (or the top, or both, as `anchor` says).
 */
function fitAspect(rect, shot, viewport) {
    if (shot.aspect === 'none') {
        return rect;
    }
    const [w, h] = String(shot.aspect ?? '16:10').split(':').map(Number);
    const ratio = w / h;
    let { x, y, width, height } = rect;
    if (width / height > ratio) {
        const wanted = width / ratio;
        const below = Math.min(viewport.height - (y + height), wanted - height);
        height += below;
        const above = Math.min(y, wanted - height);
        y -= above;
        height += above;
        if (width / height > ratio + 0.001) {
            const narrower = height * ratio;
            x += (width - narrower) / 2;
            width = narrower;
        }
    } else if (width / height < ratio) {
        const wanted = height * ratio;
        const grow = Math.min(wanted - width, viewport.width - width);
        if (grow > 0) {
            x = Math.max(0, Math.min(x - grow / 2, viewport.width - (width + grow)));
            width += grow;
        }
        if (width / height < ratio - 0.001) {
            const shorter = width / ratio;
            const anchor = shot.anchor ?? 'top';
            y += anchor === 'bottom' ? height - shorter : (anchor === 'center' ? (height - shorter) / 2 : 0);
            height = shorter;
        }
    }
    const left = Math.round(x);
    const top = Math.round(y);
    let finalWidth = Math.floor(width);
    let finalHeight = Math.floor(finalWidth / ratio);
    if (top + finalHeight > viewport.height) {
        finalHeight = viewport.height - top;
        finalWidth = Math.floor(finalHeight * ratio);
    }
    return { x: left, y: top, width: finalWidth, height: finalHeight };
}

/** PNG in, content-hashed WebP out, in the folder ShowcaseBlocks::screenshot() reads. */
function save({ name }, png) {
    const folder = join(ROOT, 'Resources', 'Public', 'Styleguide', name.startsWith('frontend-') ? 'Frontend' : 'Backend');
    const source = join(workDir, `${name}.png`);
    const target = join(workDir, `${name}.webp`);
    writeFileSync(source, png);
    execFileSync('cwebp', ['-quiet', '-q', '92', '-m', '6', '-sharp_yuv', source, '-o', target]);
    const webp = readFileSync(target);
    const hash = createHash('sha256').update(webp).digest('hex').slice(0, 8);
    const file = `${name}-${hash}.webp`;
    writeFileSync(join(folder, file), webp);
    return file;
}
