#!/usr/bin/env node
/**
 * Captures the screenshots of the /features pages from the running lab.
 *
 *   node Build/Scripts/capture-feature-screenshots.mjs [--base <url>] [--shots <file>] [--only <name-prefix>] [--rewrite] [--headed] [--list]
 *   node Build/Scripts/capture-feature-screenshots.mjs --relink
 *
 * The shots are listed in Build/Data/feature-screenshots.json (or the file
 * --shots names). Each one names a
 * backend route or a frontend path, the steps that bring the screen into the
 * state worth showing (open a dropdown, switch a view) and the area to crop.
 *
 * Backend shots reuse the browser state in var/playwright-backend/backend-state.json
 * of the lab: a signed-in session this script never creates, because it never
 * handles a password. Run it with --headed once and sign in yourself in the
 * window that opens; the state (TYPO3's session cookie has no expiry date, so a
 * browser profile would lose it) is saved for the next runs. Frontend shots run
 * in a separate, anonymous context, so no admin panel or editing toolbar ends up
 * in them; "frontend-auth" shots open a frontend page in the signed-in context
 * (for the Admin Panel or Agentation).
 *
 * A shot: name (a "frontend-" prefix files it under Frontend, the rest under
 * Backend), kind (frontend | backend | frontend-auth), url, viewport ("WxH",
 * default 1440x900), colorScheme (frontend shots), css (top document),
 * frameCss (module iframe), steps, settle (ms before the shot, default 800),
 * clip, padding and aspect. A step is one of click, hover, fill [selector, value],
 * select [selector, value], check, press, waitFor, goto, evaluate or scroll,
 * with frame (true or an iframe chain), optional, wait and timeout. Selectors
 * are CSS or "text=<visible text>". A clip is "viewport", "module", a
 * selector, "frame:<selector>", {frame, selector}, or a list of them (the
 * union). Every crop comes out at 16:10 (`aspect`, "none" to keep the clip as
 * it is), because the hero, the gallery and the hub cards show screenshots in
 * 16:10 boxes with object-fit: cover: the crop grows sideways where the page
 * allows it and loses its bottom otherwise (`anchor: "bottom"` or "center"
 * keeps another part).
 *
 * Every shot is taken at twice the CSS resolution and saved as WebP (cwebp,
 * quality 92). File names carry a content hash, because the FAL seeders never
 * import a file name twice; --rewrite points Classes/Data/Showcase at the new
 * names and deletes the files they replace. --relink captures nothing: it
 * points every reference at the newest file of its name and deletes the older
 * ones, for captures that ran in parallel without --rewrite.
 */

import { chromium } from 'playwright';
import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { existsSync, mkdtempSync, readdirSync, readFileSync, rmSync, statSync, unlinkSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const LAB = join(ROOT, '..', '..');
const STATE = join(LAB, 'var', 'playwright-backend', 'backend-state.json');
const REFERENCES = [join(ROOT, 'Classes', 'Data', 'Showcase')];
const FOLDERS = ['Frontend', 'Backend'].map((folder) => join(ROOT, 'Resources', 'Public', 'Styleguide', folder));

const args = process.argv.slice(2);
const flag = (name, fallback = null) => {
    const index = args.indexOf(`--${name}`);
    return index === -1 ? fallback : (args[index + 1] ?? fallback);
};
const base = (flag('base', 'https://webconsulting-typo3-lab.ddev.site') ?? '').replace(/\/$/, '');
const only = flag('only');
const rewrite = args.includes('--rewrite');
const headed = args.includes('--headed');
const shotList = flag('shots', join(ROOT, 'Build', 'Data', 'feature-screenshots.json'));

if (args.includes('--relink')) {
    relink();
    process.exit(0);
}

const shots = JSON.parse(readFileSync(shotList, 'utf8')).shots.filter((shot) => only === null || shot.name.startsWith(only));
if (args.includes('--list')) {
    shots.forEach((shot) => console.log(`${shot.name}\t${shot.kind}\t${shot.url}`));
    process.exit(0);
}

/** Freeze motion, so a shot never catches an element halfway through its entrance. */
const STILL = '*,*::before,*::after{animation:none!important;transition:none!important;animation-timeline:auto!important;caret-color:transparent!important}'
    + '[class*=reveal],[class*=fade],section{opacity:1!important;transform:none!important}';
/** The backend module's iframe: page tree and module menu stay outside it. */
const MODULE_FRAME = 'iframe#typo3-contentIframe';
/** The rounded corner the backend draws over the module's top-left edge would show as a dark wedge. */
const MODULE_CORNER = '.scaffold-content::before{display:none!important}';
/** The frontend header is sticky; in an element crop it would cover the element. */
const UNSTICK = 'header,.desiderio-header,[class*=site-header]{position:relative!important;top:auto!important}';

const workDir = mkdtempSync(join(tmpdir(), 'feature-shots-'));
const renamed = [];

const browser = await chromium.launch({ headless: !headed });
const backend = shots.some((shot) => shot.kind !== 'frontend')
    ? await browser.newContext({
        ignoreHTTPSErrors: true,
        deviceScaleFactor: 2,
        colorScheme: 'light',
        storageState: existsSync(STATE) ? STATE : undefined,
    })
    : null;

try {
    if (backend !== null) {
        const page = await backend.newPage();
        await page.goto(`${base}/typo3/`, { waitUntil: 'domcontentloaded', timeout: 120000 });
        if (!/\/typo3\/(main|module)/.test(page.url())) {
            if (!headed) {
                throw new Error('No signed-in backend session. Run once with --headed and sign in in the window that opens.');
            }
            console.log('Sign in to the TYPO3 backend in the window that opened; the capture starts afterwards.');
            await page.waitForURL(/\/typo3\/(main|module)/, { timeout: 30 * 60 * 1000 });
            await backend.storageState({ path: STATE });
        }
    }

    for (const shot of shots) {
        const [width, height] = (shot.viewport ?? '1440x900').split('x').map(Number);
        const context = shot.kind !== 'frontend'
            ? backend
            : await browser.newContext({ ignoreHTTPSErrors: true, deviceScaleFactor: 2, colorScheme: shot.colorScheme ?? 'light', viewport: { width, height } });
        const page = shot.kind !== 'frontend' ? (backend.pages()[0] ?? (await backend.newPage())) : await context.newPage();
        await page.setViewportSize({ width, height });
        // Backend modules poll (dashboards, badges), so they never go network-idle.
        await page.goto(base + shot.url, { waitUntil: shot.waitUntil ?? (shot.kind === 'backend' ? 'domcontentloaded' : 'networkidle'), timeout: 120000 });
        await page.addStyleTag({ content: STILL + (shot.kind !== 'backend' ? UNSTICK : MODULE_CORNER) + (shot.css ?? '') });
        if (shot.frameCss) {
            await page.frameLocator(MODULE_FRAME).first().locator('.module').first().waitFor({ timeout: 120000 });
            await (await moduleFrame(page))?.addStyleTag({ content: STILL + shot.frameCss });
        }
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
        const file = save(shot.name, png);
        if (shot.kind === 'frontend') {
            await context.close();
        }
        console.log(`${shot.name}: ${file} (${Math.round(clip.width)}x${Math.round(clip.height)} CSS px)`);
    }
} finally {
    await backend?.close();
    await browser.close();
    rmSync(workDir, { recursive: true, force: true });
}

if (rewrite) {
    rewriteReferences(renamed);
}

/**
 * Points the references in Classes/Data/Showcase at the given files and
 * deletes every other file of the same name.
 */
function rewriteReferences(files) {
    for (const directory of REFERENCES) {
        for (const entry of readdirSync(directory)) {
            const path = join(directory, entry);
            if (!statSync(path).isFile() || !path.endsWith('.php')) {
                continue;
            }
            let content = readFileSync(path, 'utf8');
            let changed = false;
            for (const { name, file } of files) {
                const pattern = new RegExp(`${name}(?:-[0-9a-f]{8})?\\.(?:png|webp)`, 'g');
                const next = content.replace(pattern, file);
                changed ||= next !== content;
                content = next;
            }
            if (changed) {
                writeFileSync(path, content);
                console.log(`updated ${path.slice(ROOT.length + 1)}`);
            }
        }
    }
    for (const { folder, name, file } of files) {
        for (const old of readdirSync(folder)) {
            if (old !== file && new RegExp(`^${name}(?:-[0-9a-f]{8})?\\.(?:png|webp)$`).test(old)) {
                unlinkSync(join(folder, old));
                console.log(`deleted ${old}`);
            }
        }
    }
}

/** The newest capture of every referenced shot becomes the one the pages use. */
function relink() {
    const names = new Set();
    for (const directory of REFERENCES) {
        for (const entry of readdirSync(directory).filter((entry) => entry.endsWith('.php'))) {
            for (const match of readFileSync(join(directory, entry), 'utf8').matchAll(/((?:frontend-)?feature-[a-z0-9-]+?)(?:-[0-9a-f]{8})?\.(?:png|webp)/g)) {
                names.add(match[1]);
            }
        }
    }
    const files = [];
    const missing = [];
    for (const name of names) {
        const candidates = FOLDERS.flatMap((folder) => readdirSync(folder)
            .filter((entry) => new RegExp(`^${name}-[0-9a-f]{8}\\.webp$`).test(entry))
            .map((entry) => ({ folder, name, file: entry, time: statSync(join(folder, entry)).mtimeMs })));
        if (candidates.length === 0) {
            missing.push(name);
            continue;
        }
        files.push(candidates.sort((a, b) => b.time - a.time)[0]);
    }
    rewriteReferences(files);
    console.log(`${files.length} shots linked` + (missing.length > 0 ? `, not captured yet: ${missing.join(', ')}` : ''));
}

/** The content frame of the backend module (null on frontend pages). */
async function moduleFrame(page) {
    return nestedFrame(page, true);
}

/**
 * `frame: true` means the backend module's iframe; a string names a chain of
 * iframes from the top document, separated by " >> " (the Visual Editor shows
 * the page in an iframe inside the module's iframe).
 */
function frameChain(frame) {
    return frame === true ? [MODULE_FRAME] : String(frame).split(' >> ');
}

async function nestedFrame(page, frame) {
    let current = page.mainFrame();
    for (const selector of frameChain(frame)) {
        const handle = await current.locator(selector).first().elementHandle();
        current = handle === null ? null : await handle.contentFrame();
        if (current === null) {
            return null;
        }
    }
    return current;
}

/**
 * One step towards the state a shot shows. `frame: true` runs it inside the
 * backend module's iframe.
 */
async function runStep(page, step) {
    const scope = step.frame ? frameChain(step.frame).reduce((parent, selector) => parent.frameLocator(selector).first(), page) : page;
    const locate = (selector) => (selector.startsWith('text=') ? scope.getByText(selector.slice(5), { exact: false }).first() : scope.locator(selector).first());
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
        await page.goto(base + step.goto, { waitUntil: 'networkidle', timeout: 120000 });
    } else if (step.evaluate) {
        if (step.frame) {
            await (await nestedFrame(page, step.frame))?.evaluate(step.evaluate);
        } else {
            await page.evaluate(step.evaluate);
        }
    } else if (step.scroll) {
        await locate(step.scroll).scrollIntoViewIfNeeded();
    }
    page.setDefaultTimeout(60000);
    await page.waitForTimeout(step.wait ?? 300);
}

/**
 * The area to keep: "viewport", "module" (the backend module, without the top
 * bar and the module menu), one selector, or the union of several, with padding.
 */
async function resolveClip(page, shot) {
    const viewport = page.viewportSize();
    const clip = shot.clip ?? 'viewport';
    if (clip === 'viewport') {
        return fitAspect({ x: 0, y: 0, width: viewport.width, height: viewport.height }, shot, viewport, 0);
    }
    const selectors = clip === 'module' ? ['.scaffold-content'] : [].concat(clip);
    const rects = [];
    for (const entry of selectors) {
        // "frame:<css>" is an element in the module's iframe; {frame, selector}
        // one in any iframe chain (see frameChain()).
        const selector = typeof entry === 'string' ? entry : `${entry.frame} >> ${entry.selector}`;
        const chain = typeof entry === 'string' ? (entry.startsWith('frame:') ? true : null) : entry.frame;
        const css = typeof entry === 'string' ? (entry.startsWith('frame:') ? entry.slice(6) : entry) : entry.selector;
        let rect = null;
        if (chain !== null) {
            const frame = await nestedFrame(page, chain);
            rect = (await frame?.locator(css).first().boundingBox()) ?? null;
        } else {
            rect = await page.locator(css).first().boundingBox();
        }
        if (rect === null) {
            throw new Error(`${shot.name}: nothing matches "${selector}" on ${page.url()}`);
        }
        rects.push(rect);
    }
    const pad = shot.padding ?? (clip === 'module' ? 0 : 16);
    const x = Math.max(0, Math.min(...rects.map((r) => r.x)) - pad);
    const y = Math.max(0, Math.min(...rects.map((r) => r.y)) - pad);
    const right = Math.min(viewport.width, Math.max(...rects.map((r) => r.x + r.width)) + pad);
    const bottom = Math.min(viewport.height, Math.max(...rects.map((r) => r.y + r.height)) + pad);
    // Backend crops never grow into the page tree or the module menu: the
    // module iframe is where the module starts.
    const minX = shot.kind === 'backend' ? ((await page.locator(MODULE_FRAME).first().boundingBox())?.x ?? 0) : 0;
    return fitAspect({ x, y, width: right - x, height: bottom - y }, shot, viewport, Math.min(minX, x));
}

/**
 * Brings a crop to the shot's aspect ratio (16:10 unless it says otherwise):
 * a wide crop grows downwards, then upwards; a tall one grows sideways between
 * minX and the viewport edge, and whatever is still missing is cut from the
 * bottom (or the top, or both, as `anchor` says).
 */
function fitAspect(rect, shot, viewport, minX) {
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
        const grow = Math.min(wanted - width, (viewport.width - minX) - width);
        if (grow > 0) {
            x = Math.max(minX, Math.min(x - grow / 2, viewport.width - (width + grow)));
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
function save(name, png) {
    const folder = join(ROOT, 'Resources', 'Public', 'Styleguide', name.startsWith('frontend-') ? 'Frontend' : 'Backend');
    const source = join(workDir, `${name}.png`);
    const target = join(workDir, `${name}.webp`);
    writeFileSync(source, png);
    execFileSync('cwebp', ['-quiet', '-q', '92', '-m', '6', '-sharp_yuv', source, '-o', target]);
    const webp = readFileSync(target);
    const hash = createHash('sha256').update(webp).digest('hex').slice(0, 8);
    const file = `${name}-${hash}.webp`;
    writeFileSync(join(folder, file), webp);
    renamed.push({ folder, name, file });
    return file;
}
