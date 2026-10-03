#!/usr/bin/env node
/**
 * Renders the Desiderio product film (Build/Film/film.html) to video.
 *
 *   node Build/Scripts/render-film.mjs                 # MP4 + poster into Resources/Public/Styleguide/Video
 *   node Build/Scripts/render-film.mjs --stills=1500,6000,12500   # PNG stills into Build/Film/out, for checking
 *   node Build/Scripts/render-film.mjs --remix                     # new soundtrack on the current picture
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
 *
 * With Build/Film/audio/ in place (Build/Scripts/generate-film-audio.mjs)
 * the film gets its soundtrack: each narration line at its scene, the music
 * under it, ducked while the voice speaks, faded in and out and normalised
 * to -16 LUFS. The captions in film.html come from the same narration.json.
 */

import { chromium } from '@playwright/test';
import { spawn } from 'node:child_process';
import { createHash } from 'node:crypto';
import { copyFileSync, createReadStream, existsSync, mkdirSync, readFileSync, readdirSync, renameSync, statSync, unlinkSync, writeFileSync } from 'node:fs';
import { createServer } from 'node:http';
import { dirname, extname, join, normalize } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const OUT = join(ROOT, 'Build/Film/out');
const TARGET = join(ROOT, 'Resources/Public/Styleguide/Video');
const AUDIO = join(ROOT, 'Build/Film/audio');
/** Files that name the film. */
const REFERENCES = ['Classes/Data/Showcase/ShowcaseBlocks.php'];
const FPS = 30;
const SIZE = { width: 1600, height: 900 };

const argument = (name) => process.argv.find((arg) => arg.startsWith(`--${name}=`))?.split('=')[1];
const stills = argument('stills')?.split(',').map(Number);
const remix = process.argv.includes('--remix');

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

/**
 * Scores the film (soundtrack from Build/Film/audio/, when it is there),
 * names it and its poster by the film's hash and points the showcase at them.
 */
async function finish(mp4, poster) {
    const narrationFile = join(AUDIO, 'narration.json');
    if (existsSync(narrationFile) && existsSync(join(AUDIO, 'music.mp3'))) {
        const lines = JSON.parse(readFileSync(narrationFile, 'utf8')).lines;
        const end = duration / 1000;
        const inputs = ['-i', mp4, '-i', join(AUDIO, 'music.mp3'), ...lines.flatMap((line) => ['-i', join(AUDIO, line.file)])];
        const voices = lines.map((line, i) => `[${i + 2}:a]aformat=sample_rates=44100:channel_layouts=stereo,adelay=${line.at}|${line.at}[v${i}]`);
        const graph = [
            ...voices,
            `${lines.map((_, i) => `[v${i}]`).join('')}amix=inputs=${lines.length}:normalize=0,asplit=2[voice][key]`,
            `[1:a]aformat=sample_rates=44100:channel_layouts=stereo,volume=0.32,afade=t=in:st=0:d=1.2,afade=t=out:st=${end - 2.2}:d=2.2[music]`,
            '[music][key]sidechaincompress=threshold=0.02:ratio=8:attack=15:release=500[ducked]',
            `[ducked][voice]amix=inputs=2:normalize=0,atrim=0:${end},loudnorm=I=-16:TP=-1.5:LRA=11[mix]`,
        ].join(';');
        const scored = join(OUT, 'film-scored.mp4');
        await ffmpeg([...inputs, '-filter_complex', graph, '-map', '0:v', '-map', '[mix]', '-c:v', 'copy', '-c:a', 'aac', '-b:a', '160k', '-ar', '44100', '-movflags', '+faststart', '-t', String(end), scored]);
        renameSync(scored, mp4);
        console.log(`soundtrack: ${lines.length} narration lines + music`);
    }

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

mkdirSync(OUT, { recursive: true });
const browser = await chromium.launch();
const page = await browser.newPage({ viewport: SIZE, deviceScaleFactor: 1 });
await page.goto(`${base}/Build/Film/film.html`, { waitUntil: 'networkidle' });
const duration = await page.evaluate(() => window.ready);

if (remix) {
    // The picture of the current film, with a new soundtrack: copy its video
    // stream into film.mp4 and let the normal path below score and rename it.
    const current = readdirSync(TARGET).find((name) => /^desiderio-film-[0-9a-f]{8}\.mp4$/.test(name));
    if (!current) throw new Error('No rendered film to remix.');
    await ffmpeg(['-i', join(TARGET, current), '-map', '0:v', '-c:v', 'copy', '-an', join(OUT, 'film.mp4')]);
    const poster = join(OUT, 'poster.jpg');
    copyFileSync(join(TARGET, current.replace('desiderio-film-', 'desiderio-film-poster-').replace('.mp4', '.jpg')), poster);
    await finish(join(OUT, 'film.mp4'), poster);
} else if (stills) {
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

    await finish(mp4, poster);
}

await browser.close();
server.close();
