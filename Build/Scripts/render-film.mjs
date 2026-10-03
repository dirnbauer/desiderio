#!/usr/bin/env node
/**
 * Renders the Desiderio product film (Build/Film/film.html) to video.
 *
 *   node Build/Scripts/render-film.mjs                 # MP4 + poster into Resources/Public/Styleguide/Video
 *   node Build/Scripts/render-film.mjs --stills=1500,6000,12500   # PNG stills into Build/Film/out, for checking
 *
 * Every motion in the film is a paused Web Animation, so each frame is
 * rendered by seeking the timeline, never by waiting: the output is the same
 * on every machine and every run. Frames go straight into ffmpeg through a
 * pipe. The file names carry a content hash, because ExtensionFalSeeder
 * imports by file name and never re-imports a name it already has; the
 * script points the showcase data at the new names.
 *
 * Needs ffmpeg with libx264, and the assets from
 * Build/Scripts/capture-film-assets.mjs.
 */

import { chromium } from '@playwright/test';
import { spawn } from 'node:child_process';
import { createHash } from 'node:crypto';
import { createReadStream, existsSync, mkdirSync, readFileSync, readdirSync, renameSync, statSync, unlinkSync, writeFileSync } from 'node:fs';
import { createServer } from 'node:http';
import { dirname, extname, join, normalize } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const OUT = join(ROOT, 'Build/Film/out');
const TARGET = join(ROOT, 'Resources/Public/Styleguide/Video');
/** Files that name the film. */
const REFERENCES = ['Classes/Data/Showcase/ShowcaseBlocks.php'];
const FPS = 30;
const SIZE = { width: 1600, height: 900 };

const argument = (name) => process.argv.find((arg) => arg.startsWith(`--${name}=`))?.split('=')[1];
const stills = argument('stills')?.split(',').map(Number);

const TYPES = { '.html': 'text/html', '.json': 'application/json', '.png': 'image/png', '.jpg': 'image/jpeg', '.webp': 'image/webp', '.woff2': 'font/woff2', '.css': 'text/css', '.js': 'text/javascript' };
const server = createServer((request, response) => {
    const path = normalize(decodeURIComponent(new URL(request.url, 'http://localhost').pathname));
    const file = join(ROOT, path);
    if (!file.startsWith(ROOT) || !existsSync(file) || statSync(file).isDirectory()) {
        response.writeHead(404).end();
        return;
    }
    response.writeHead(200, { 'Content-Type': TYPES[extname(file)] ?? 'application/octet-stream' });
    createReadStream(file).pipe(response);
});
await new Promise((resolve) => server.listen(0, '127.0.0.1', resolve));
const base = `http://127.0.0.1:${server.address().port}`;

function ffmpeg(args, input) {
    return new Promise((resolve, reject) => {
        const process = spawn('ffmpeg', ['-hide_banner', '-loglevel', 'error', '-y', ...args], { stdio: [input ? 'pipe' : 'ignore', 'inherit', 'inherit'] });
        process.on('error', reject);
        process.on('close', (code) => (code === 0 ? resolve() : reject(new Error(`ffmpeg exited with ${code}`))));
        if (input) input(process.stdin);
    });
}

mkdirSync(OUT, { recursive: true });
const browser = await chromium.launch();
const page = await browser.newPage({ viewport: SIZE, deviceScaleFactor: 1 });
await page.goto(`${base}/Build/Film/film.html`, { waitUntil: 'networkidle' });
const duration = await page.evaluate(() => window.ready);

if (stills) {
    for (const t of stills) {
        await page.evaluate((time) => window.seek(time), t);
        await page.screenshot({ path: join(OUT, `still-${t}.png`) });
        console.log(`still-${t}.png`);
    }
} else {
    const master = join(OUT, 'master.mp4');
    const frames = Math.round((duration / 1000) * FPS);
    // A near-lossless master first; the web encodes are made from it.
    await ffmpeg(['-f', 'image2pipe', '-framerate', String(FPS), '-c:v', 'png', '-i', '-', '-c:v', 'libx264', '-preset', 'veryfast', '-crf', '8', '-pix_fmt', 'yuv420p', master], async (stdin) => {
        for (let frame = 0; frame < frames; frame++) {
            await page.evaluate((time) => window.seek(time), (frame * 1000) / FPS);
            const png = await page.screenshot({ type: 'png' });
            if (!stdin.write(png)) await new Promise((resolve) => stdin.once('drain', resolve));
            if (frame % 60 === 0) console.log(`frame ${frame} / ${frames}`);
        }
        stdin.end();
    });

    const mp4 = join(OUT, 'film.mp4');
    const poster = join(OUT, 'poster.jpg');
    await ffmpeg(['-i', master, '-c:v', 'libx264', '-preset', 'slow', '-tune', 'animation', '-crf', '23', '-profile:v', 'high', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', '-an', mp4]);
    // The poster is the preset beat, the film's most telling frame.
    await page.evaluate((time) => window.seek(time), 6400);
    await page.screenshot({ path: poster, type: 'jpeg', quality: 86 });

    mkdirSync(TARGET, { recursive: true });
    const hash = createHash('sha256').update(readFileSync(mp4)).digest('hex').slice(0, 8);
    for (const old of readdirSync(TARGET).filter((name) => name.startsWith('desiderio-film-'))) {
        unlinkSync(join(TARGET, old));
    }
    for (const [file, name] of [[mp4, `desiderio-film-${hash}.mp4`], [poster, `desiderio-film-poster-${hash}.jpg`]]) {
        renameSync(file, join(TARGET, name));
        console.log(`${name}  ${(statSync(join(TARGET, name)).size / 1024).toFixed(0)} KB`);
    }
    // The showcase names the film by its hash; point it at the new one.
    for (const reference of REFERENCES) {
        const path = join(ROOT, reference);
        const source = readFileSync(path, 'utf8');
        const updated = source.replace(/(desiderio-film-(?:poster-)?)[0-9a-f]{8}/g, `$1${hash}`);
        if (updated !== source) {
            writeFileSync(path, updated);
            console.log(`updated ${reference}`);
        }
    }
}

await browser.close();
server.close();
