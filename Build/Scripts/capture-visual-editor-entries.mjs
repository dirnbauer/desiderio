#!/usr/bin/env node
/**
 * Product screenshots of the Visual Editor Enhancements 1.6 entries workflow on
 * the showcase page /content-types/visual-editor-inline (lab uid 8430). Only
 * hovers and opens panels and forms; it never saves, adds or removes an entry.
 *
 * Session, never a password: either
 *   VE_STATE=<Playwright storageState>  (the lab runtime suite's disposable admin,
 *                                        Build/Scripts/ve-runtime.sh fixture → var/ve-integration/state-admin.json)
 *   FILM_SESSION=<file with a be_typo_user JWT> (a disposable English capture account)
 * The account needs Edit collection entries on, action labels `caption`, the
 * entries layout `inspector` and the colour scheme `auto` (the browser is dark).
 * The tray shot switches the layout through VE_SETTINGS_CMD, a command with a
 * `{layout}` placeholder that sets the account's user setting, e.g.
 *   VE_SETTINGS_CMD='Build/Scripts/ve-runtime.sh helper settings admin tabs 0 caption {layout} auto'
 * and switches back to `inspector` afterwards.
 *
 *   node Build/Scripts/capture-visual-editor-entries.mjs [--only=entries,add-entry,...]
 *
 * Shots (16:10, DSF 2, dark): entries, add-entry, entry-form, translation,
 * collection-settings, tray. Prints {name: hashed file} for the PHP references
 * and writes Resources/Public/Styleguide/Backend/<name>-<hash8>.webp.
 */
import {chromium} from 'playwright';
import {createHash} from 'node:crypto';
import {execFileSync, execSync} from 'node:child_process';
import {readFileSync, writeFileSync, mkdtempSync, rmSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {dirname, join, resolve} from 'node:path';
import {fileURLToPath} from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const base = process.env.LAB_BASE ?? 'https://webconsulting-typo3-lab.ddev.site';
const PAGE = Number(process.env.VE_PAGE ?? 8430);
const ACCORDION = 'desiderio_accordion';
const only = (process.argv.find((arg) => arg.startsWith('--only=')) ?? '').slice(7).split(',').filter(Boolean);
const wanted = (shot) => only.length === 0 || only.includes(shot);
if (!process.env.VE_STATE && !process.env.FILM_SESSION) {
  throw new Error('Set VE_STATE (Playwright storageState) or FILM_SESSION (JWT file) of a disposable account.');
}
if (wanted('tray') && !process.env.VE_SETTINGS_CMD) {
  throw new Error('The tray shot needs VE_SETTINGS_CMD to switch the entries layout.');
}
const temporary = mkdtempSync(join(tmpdir(), 've-product-shots-'));
const browser = await chromium.launch();
const outputs = {};

function settings(layout) {
  execSync(process.env.VE_SETTINGS_CMD.replaceAll('{layout}', layout), {stdio: 'inherit'});
}

async function newPage() {
  const options = {ignoreHTTPSErrors: true, viewport: {width: 1600, height: 1100}, deviceScaleFactor: 2, colorScheme: 'dark'};
  if (process.env.VE_STATE) {
    return (await browser.newContext({...options, storageState: process.env.VE_STATE})).newPage();
  }
  const context = await browser.newContext(options);
  const session = readFileSync(process.env.FILM_SESSION, 'utf8').trim();
  await context.addCookies([{name: 'be_typo_user', value: session, domain: new URL(base).hostname, path: '/', secure: true, httpOnly: true, sameSite: 'Strict'}]);
  return context.newPage();
}

/** Opens the Visual Editor module of the showcase page in one language; returns the edit frame. */
async function open(page, language = 0) {
  await page.goto(`${base}/typo3/module/web/edit?id=${PAGE}&languages[0]=${language}`, {waitUntil: 'domcontentloaded'});
  if (page.url().includes('/typo3/login')) {
    throw new Error('The capture session is not signed in.');
  }
  const deadline = Date.now() + 90000;
  let frame;
  while (!(frame = page.frames().find((f) => f.url().includes('editMode=1')))) {
    if (Date.now() > deadline) { throw new Error('The edit frame did not load.'); }
    await page.waitForTimeout(250);
  }
  await frame.waitForFunction(() => window.visualEditorEnhancements?.inlineRecordsEnabled, null, {timeout: 60000});
  return frame;
}

/** The accordion, resolved by its CType (its uid changes on every reseed); bar revealed. */
async function accordion(frame) {
  const element = frame.locator(`ve-content-element[ctype="${ACCORDION}"]`).first();
  await element.waitFor({timeout: 60000});
  // Keep the bar clear of desiderio's sticky header.
  await element.evaluate((el) => window.scrollBy(0, el.getBoundingClientRect().top - 300));
  await element.hover({position: {x: 40, y: 20}});
  await element.locator('.action-bar').waitFor({state: 'visible'});
  return element;
}

/** The inspector is idle (it re-renders asynchronously after it opens). */
async function settle(chooser) {
  await chooser.evaluate(async (x) => { await x.updateComplete; });
  for (let i = 0; i < 100; i++) {
    if (await chooser.evaluate((x) => x.open && !x.loading && !x.busy && x.fields.some((f) => f.type === 'inline'))) { break; }
    await chooser.page().waitForTimeout(100);
  }
  await chooser.evaluate(() => new Promise((done) => requestAnimationFrame(() => requestAnimationFrame(done))));
}

async function openEntries(frame, element) {
  await element.locator('[data-ve-enhancement="inline-records"]').click();
  const chooser = frame.locator('ve-field-chooser');
  await chooser.locator('.entryItem').first().waitFor();
  await settle(chooser);
  // No focus ring on the first control in a product shot.
  await chooser.evaluate((x) => x.renderRoot.activeElement?.blur?.());
  return chooser;
}

/** The edit frame's box in top-page coordinates. */
async function frameBox(page) {
  return page.frameLocator('#typo3-contentIframe').locator('iframe.visual-editor-iframe').boundingBox();
}

/**
 * 16:10 clip. `align`: 'top' (frame top), 'bottom' (frame bottom), 'page'
 * (top of the window, for modals), or a box to centre a smaller crop on.
 */
async function capture(page, name, align = 'top', cropWidth = 0) {
  await page.waitForTimeout(400);
  const box = await frameBox(page);
  let clip;
  if (typeof align === 'object') {
    const width = Math.floor(cropWidth / 8) * 8, height = width * 5 / 8;
    const x = Math.min(Math.max(box.x, align.x + align.width / 2 - width / 2), box.x + box.width - width);
    const y = Math.min(Math.max(box.y, align.y + align.height / 2 - height / 2), box.y + box.height - height);
    clip = {x, y, width, height};
  } else {
    const width = Math.floor(box.width / 8) * 8, height = width * 5 / 8;
    const y = align === 'page' ? 0 : align === 'bottom' ? box.y + box.height - height : box.y;
    clip = {x: box.x, y, width, height};
  }
  const png = join(temporary, `${name}.png`), webp = join(temporary, `${name}.webp`);
  await page.screenshot({path: png, clip, animations: 'disabled'});
  execFileSync('cwebp', ['-quiet', '-q', '92', png, '-o', webp]);
  const bytes = readFileSync(webp), hash = createHash('sha256').update(bytes).digest('hex').slice(0, 8);
  const file = `${name}-${hash}.webp`;
  writeFileSync(join(root, 'Resources/Public/Styleguide/Backend', file), bytes);
  outputs[name] = file;
}

try {
  const page = await newPage();
  let frame = await open(page);
  let element = await accordion(frame);

  if (wanted('add-entry')) {
    // The caption label of the Add entry button, shown on hover (label mode `caption`).
    const add = element.locator('[data-ve-enhancement="inline-add"]');
    await add.hover();
    await add.evaluate((button) => new Promise((done) => {
      const check = () => (button.hasAttribute('data-ve-label-current') ? done() : requestAnimationFrame(check));
      check();
    }));
    const bar = await element.locator('.action-bar').boundingBox();
    await capture(page, 'feature-visual-editor-add-entry', bar, 560);
  }

  if (wanted('entries') || wanted('entry-form')) {
    const chooser = await openEntries(frame, element);
    if (wanted('entries')) {
      await capture(page, 'feature-visual-editor-entries');
    }
    if (wanted('entry-form')) {
      const row = chooser.locator('li.entryItem').first();
      await row.locator('ve-row-menu').locator('button.trigger').click();
      await row.locator('ve-row-menu typo3-backend-contextual-record-edit-trigger').click();
      const modal = page.locator('typo3-backend-modal').last();
      await modal.frameLocator('iframe').locator('.t3js-contextual-close').waitFor();
      await page.waitForTimeout(800);
      await capture(page, 'feature-visual-editor-entry-form', 'page');
      await modal.frameLocator('iframe').locator('.t3js-contextual-close').click();
      await modal.waitFor({state: 'detached'});
    }
    await page.keyboard.press('Escape');
  }

  if (wanted('collection-settings')) {
    frame = await open(page);
    element = await accordion(frame);
    await element.locator('[data-ve-enhancement="field-chooser"]').click();
    await frame.locator('ve-field-chooser .collectionShortcut').waitFor();
    await frame.locator('ve-field-chooser').evaluate((x) => x.renderRoot.activeElement?.blur?.());
    await page.mouse.move(5, 5);
    await capture(page, 'feature-visual-editor-collection-settings');
    await page.keyboard.press('Escape');
  }

  if (wanted('translation')) {
    // The German frame: a connected translation keeps the structure of the source language.
    frame = await open(page, 1);
    element = await accordion(frame);
    await openEntries(frame, element);
    await page.mouse.move(5, 5);
    await capture(page, 'feature-visual-editor-entries-translation');
    await page.keyboard.press('Escape');
  }

  if (wanted('tray')) {
    settings('tray');
    try {
      // A taller window: the tray takes a share of the frame height, so its last hint line stays clear of its footer.
      await page.setViewportSize({width: 1600, height: 1400});
      frame = await open(page);
      element = await accordion(frame);
      await openEntries(frame, element);
      await page.mouse.move(5, 5);
      await capture(page, 'feature-visual-editor-entries-tray', 'bottom');
    } finally {
      settings('inspector');
    }
  }
  console.log(JSON.stringify(outputs, null, 2));
} finally {
  await browser.close();
  rmSync(temporary, {recursive: true, force: true});
}
