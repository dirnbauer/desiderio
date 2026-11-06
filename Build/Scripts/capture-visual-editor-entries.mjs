#!/usr/bin/env node
/**
 * Product screenshots of the real Entries workflow; opens forms without saving.
 * FILM_SESSION points to a disposable English backend session with collection
 * editing enabled. No credentials or session state are written to the package.
 * node Build/Scripts/capture-visual-editor-entries.mjs
 */
import {chromium} from 'playwright';
import {createHash} from 'node:crypto';
import {execFileSync} from 'node:child_process';
import {readFileSync, writeFileSync, mkdtempSync} from 'node:fs';
import {tmpdir} from 'node:os';
import {dirname, join, resolve} from 'node:path';
import {fileURLToPath} from 'node:url';
const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const base = process.env.LAB_BASE ?? 'https://webconsulting-typo3-lab.ddev.site';
const session = readFileSync(process.env.FILM_SESSION, 'utf8').trim();
const temporary = mkdtempSync(join(tmpdir(), 've-product-shots-'));
const browser = await chromium.launch();
const outputs = {};
try {
  const context = await browser.newContext({ignoreHTTPSErrors: true, viewport: {width: 1600, height: 1100}, deviceScaleFactor: 2, colorScheme: 'dark'});
  await context.addCookies([{name: 'be_typo_user', value: session, domain: new URL(base).hostname, path: '/', secure: true, httpOnly: true, sameSite: 'Strict'}]);
  const page = await context.newPage();
  await page.goto(`${base}/typo3/module/web/edit?id=8430`, {waitUntil: 'load'});
  const module = page.frameLocator('#typo3-contentIframe');
  const frame = module.frameLocator('iframe.visual-editor-iframe');
  const element = frame.locator('ve-content-element[uid="200211"]');
  await element.waitFor({timeout: 60000});
  await element.evaluate(el => window.scrollTo(0, el.getBoundingClientRect().top + window.scrollY - 140));
  await element.hover();
  const entries = element.locator('[data-ve-enhancement="inline-records"]');
  await entries.waitFor({state: 'visible'});
  await entries.click();
  await frame.locator('ve-field-chooser .entryItem').first().waitFor();
  async function capture(name, form = false) {
    await page.waitForTimeout(350);
    const box = await module.locator('iframe.visual-editor-iframe').boundingBox();
    const width = Math.floor(box.width / 8) * 8;
    const clip = {x: box.x, y: form ? 0 : box.y, width, height: width * 5 / 8};
    const png = join(temporary, `${name}.png`), webp = join(temporary, `${name}.webp`);
    await page.screenshot({path: png, clip, animations: 'disabled'});
    execFileSync('cwebp', ['-quiet', '-q', '92', png, '-o', webp]);
    const bytes = readFileSync(webp), hash = createHash('sha256').update(bytes).digest('hex').slice(0, 8);
    const file = `${name}-${hash}.webp`;
    writeFileSync(join(root, 'Resources/Public/Styleguide/Backend', file), bytes);
    outputs[name] = file;
  }
  await capture('feature-visual-editor-entries');
  await frame.locator('ve-field-chooser [data-child-edit]').first().click();
  const modal = page.locator('typo3-backend-modal').last();
  await modal.frameLocator('iframe').locator('.t3js-contextual-close').waitFor();
  await capture('feature-visual-editor-entry-form', true);
  await modal.frameLocator('iframe').locator('.t3js-contextual-close').click();
  await modal.waitFor({state: 'detached'});
  await element.hover();
  await element.locator('[data-ve-enhancement="field-chooser"]').click();
  await frame.locator('ve-field-chooser .collectionShortcut').waitFor();
  await capture('feature-visual-editor-collection-settings');
  console.log(JSON.stringify(outputs, null, 2));
} finally {
  await browser.close();
}
