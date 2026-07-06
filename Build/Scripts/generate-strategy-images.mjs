#!/usr/bin/env node
/**
 * Generate the pillar 17-23 illustrations for the TYPO3 v14 agentic strategy
 * page in the style of the existing Resources/Public/Styleguide/Mcp images
 * (bright office, floating translucent panels, teal/orange accents, small
 * white robots, photorealistic 3D render, no text).
 *
 * Usage:
 *   GEMINI_API_KEY=... node Build/Scripts/generate-strategy-images.mjs
 *   (or put the key in ~/.gemini/api_key)
 *
 * Optional: GEMINI_IMAGE_MODEL (default: gemini-2.5-flash-image)
 *
 * Writes v14-<slug>-source-<sha256-16>.png into Resources/Public/Styleguide/Mcp/
 * and prints the mcpStrategyImage() lines to paste into
 * Classes/Data/StyleguideShowcasePages.php (typo3V14StrategyPage()).
 */
import { createHash } from 'node:crypto';
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { homedir } from 'node:os';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const outDir = join(dirname(fileURLToPath(import.meta.url)), '..', '..', 'Resources', 'Public', 'Styleguide', 'Mcp');

let apiKey = process.env.GEMINI_API_KEY ?? '';
const keyFile = join(homedir(), '.gemini', 'api_key');
if (!apiKey && existsSync(keyFile)) {
    apiKey = readFileSync(keyFile, 'utf8').trim();
}
if (!apiKey) {
    console.error('No GEMINI_API_KEY in the environment and no ~/.gemini/api_key file.');
    process.exit(1);
}

const model = process.env.GEMINI_IMAGE_MODEL ?? 'gemini-2.5-flash-image';

const STYLE = 'Photorealistic 3D render, bright modern Scandinavian office interior with soft natural daylight, '
    + 'floating translucent frosted-glass UI panels, mint green and teal accent colors with warm orange highlights, '
    + 'small friendly white robot assistants, potted plants, clean minimal composition, shallow depth of field, '
    + 'wide 16:9 image, strictly no text, no letters, no words, no logos.';

const IMAGES = [
    ['v14-17-agent-protocols', '17. Agent protocols beyond MCP',
        'Five distinct glowing connection lanes in different pastel colors converging from small robot agents onto one central CMS control hub on a desk, each lane passing through its own translucent gate panel.',
        'five agent protocol lanes converging on one governed CMS hub'],
    ['v14-18-channel-operations', '18. Channel operations from Slack and Co.',
        'A smartphone showing an abstract chat thread panel on a desk, connected by a glowing line through a translucent approval gate with a large green check button to a CMS operations desk with screens behind it.',
        'chat channel steering a CMS through an approval gate'],
    ['v14-19-capability-registry', '19. A capability registry',
        'One large central frosted-glass registry board holding neatly ordered typed capability cards, projecting light beams outward into several smaller floating interface panels around it.',
        'central capability registry projecting into many protocol surfaces'],
    ['v14-20-machine-readable-site', '20. The machine-readable, monetizable site',
        'A website page splitting into two mirrored views, one human-friendly and one structured wireframe view, with small crawler robots reading the structured side and a small translucent toll gate with a coin in front of them.',
        'website with human and machine views and a metered crawler gate'],
    ['v14-21-provenance-compliance', '21. Trust, provenance and AI-Act compliance',
        'Floating photo and video asset cards each carrying a glowing verification seal, linked by a chain of light, while a small white robot inspects one card with a magnifying glass showing a green check.',
        'media assets with provenance seals under robot inspection'],
    ['v14-22-eu-sovereignty', '22. European sovereignty',
        'A compact server rack under a large protective glass dome with subtle blue accents and a circle of small gold stars floating above it, connected by soft light lines to an office CMS workstation nearby.',
        'sovereign EU data infrastructure under a protective dome'],
    ['v14-23-standardize-upstream', '23. Standardize and upstream',
        'Small glowing building blocks flowing upward from a single lab workbench into a large shared open community structure being assembled by many small white robots together.',
        'lab patterns flowing upstream into a shared community platform'],
];

async function generate(prompt) {
    const res = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'x-goog-api-key': apiKey },
        body: JSON.stringify({
            contents: [{ parts: [{ text: prompt }] }],
            generationConfig: { responseModalities: ['IMAGE'], imageConfig: { aspectRatio: '16:9' } },
        }),
    });
    if (!res.ok) {
        throw new Error(`HTTP ${res.status}: ${(await res.text()).slice(0, 300)}`);
    }
    const json = await res.json();
    const part = json.candidates?.[0]?.content?.parts?.find((p) => p.inlineData?.data);
    if (!part) {
        throw new Error(`No image in response: ${JSON.stringify(json).slice(0, 300)}`);
    }
    return Buffer.from(part.inlineData.data, 'base64');
}

const phpLines = [];
for (const [slug, title, subject, alt] of IMAGES) {
    process.stdout.write(`${slug} ... `);
    try {
        const png = await generate(`${subject} ${STYLE}`);
        const hash = createHash('sha256').update(png).digest('hex').slice(0, 16);
        const filename = `${slug}-source-${hash}.png`;
        writeFileSync(join(outDir, filename), png);
        console.log(`${filename} (${Math.round(png.length / 1024)} KB)`);
        phpLines.push(`    self::mcpStrategyImage('${filename}', '${alt}', '${alt}') // ${title}`);
    } catch (e) {
        console.log(`FAILED: ${e.message}`);
    }
}

if (phpLines.length > 0) {
    console.log('\nPaste as 5th argument of the matching v14StrategyTextmedia() calls');
    console.log('(and switch their layout from media-above to media-right):\n');
    console.log(phpLines.join('\n'));
}
