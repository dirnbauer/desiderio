#!/usr/bin/env node
// Exercises the shipped Vite module, including the lazy grammar chunk.
// Usage: node Build/VisualQa/code-highlighting.mjs --urls /tmp/preview-urls.json
import { readFileSync } from 'node:fs';
import { chromium, expect } from '@playwright/test';

const index = process.argv.indexOf('--urls');
if (index === -1 || !process.argv[index + 1]) throw new Error('Pass --urls <file>');
const entries = JSON.parse(readFileSync(process.argv[index + 1], 'utf8'));
const entry = entries.find(({ cType }) => cType === 'desiderio_codeblock');
if (!entry) throw new Error('The code-block preview is missing');
const browser = await chromium.launch();
try {
    const page = await browser.newPage({ ignoreHTTPSErrors: true });
    const response = await page.goto(entry.url, { waitUntil: 'networkidle' });
    expect(response.status()).toBe(200);
    await page.waitForFunction(() => !!window.DesiderioAstro);
    expect(await page.evaluate(() => !!window.hljs)).toBe(false);

    const source = 'function greet(name) {\n  const message = "Hello, " + name;\n  console.log(message);\n  return message;\n}\ngreet("world");';
    await page.evaluate((text) => {
        const code = document.createElement('code');
        code.id = 'autodetect-check';
        code.setAttribute('data-astro-highlight', '');
        code.textContent = text;
        document.body.append(code);
        window.DesiderioAstro.init(document);
    }, source);
    const code = page.locator('#autodetect-check');
    await expect(code).toHaveAttribute('data-astro-language-normalized', 'javascript');
    await expect(code.locator('.token')).not.toHaveCount(0);
    expect(await code.textContent()).toBe(source);

    const unsafe = '<img src=x onerror="window.highlightInjected=true">';
    await page.evaluate((text) => {
        const code = document.createElement('code');
        code.id = 'escaping-check';
        code.setAttribute('data-astro-highlight', '');
        code.textContent = text;
        document.body.append(code);
        window.DesiderioAstro.init(document);
    }, unsafe);
    expect(await page.locator('#escaping-check').textContent()).toBe(unsafe);
    await expect(page.locator('#escaping-check img')).toHaveCount(0);
    expect(await page.evaluate(() => window.highlightInjected)).toBeUndefined();
    console.log('Code highlighting: lazy module loading, detection, tokens, and escaping passed.');
} finally {
    await browser.close();
}
