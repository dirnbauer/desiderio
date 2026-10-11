#!/usr/bin/env node
/**
 * Generates the product film's soundtrack through fal.ai: one narration line
 * per scene (ElevenLabs text to speech) and an instrumental underscore
 * (ElevenLabs Music). Everything lands in Build/Film/audio/, together with
 * narration.json — the lines, where they start and how long they really are —
 * which film.html reads for the captions and render-film.mjs for the mix.
 *
 *   FAL_KEY=… node Build/Scripts/generate-film-audio.mjs           # voice + music
 *   FAL_KEY=… node Build/Scripts/generate-film-audio.mjs --voice   # narration only
 *   FAL_KEY=… node Build/Scripts/generate-film-audio.mjs --music   # music only
 *   FAL_KEY=… node Build/Scripts/generate-film-audio.mjs --voice --line=7   # one line again
 *
 * The voice is one of ElevenLabs' own library voices; it does not imitate a
 * real person. The music prompt describes a style, never an artist.
 */

import { execFileSync } from 'node:child_process';
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const AUDIO = join(ROOT, 'Build/Film/audio');
const KEY = process.env.FAL_KEY;
if (!KEY) {
    console.error('FAL_KEY is not set.');
    process.exit(1);
}

const VOICE = 'George';
/**
 * One line per beat of film.html. `at` is where the line starts in
 * milliseconds; `say` is what the voice reads, `show` what the caption prints
 * (digits where the voice spells a number out). Each line has to end before
 * the next one starts: the script warns when one runs over.
 */
const LINES = [
    { at: 700, say: 'Every relaunch starts the same way.', show: 'Every relaunch starts the same way.' },
    { at: 4300, say: 'The same heroes, pricing tables and footers, built again from nothing.', show: 'The same heroes, pricing tables and footers, built again from nothing.' },
    { at: 10200, say: 'So we built them once, with care. This is Desiderio.', show: 'So we built them once, with care. This is Desiderio.' },
    { at: 16200, say: 'Six things, in one package.', show: 'Six things, in one package.' },
    { at: 21400, say: 'Two hundred and forty-four content elements, in ten groups. Each one finished, with a backend preview.', show: '244 content elements, in ten groups. Each one finished, with a backend preview.' },
    { at: 30100, say: 'Fifteen theme presets. One setting restyles the whole site.', show: '15 theme presets. One setting restyles the whole site.' },
    { at: 36000, say: 'And every preset comes in light and dark.', show: 'And every preset comes in light and dark.' },
    { at: 40900, say: 'Editors work on the page itself. They see each element before they add it, and change it where it sits.', show: 'Editors work on the page itself. They see each element before they add it, and change it where it sits.' },
    { at: 49800, say: 'Forms, search, news and the blog follow the same theme, from one package.', show: 'Forms, search, news and the blog follow the same theme, from one package.' },
    { at: 57600, say: 'Every preset is checked for accessible contrast before it ships.', show: 'Every preset is checked for accessible contrast before it ships.' },
    { at: 62700, say: 'Sixty-two typed components keep every page consistent.', show: '62 typed components keep every page consistent.' },
    { at: 68400, say: 'The demo site speaks four languages, in markup that AI search can read and quote.', show: 'The demo site speaks four languages, in markup that AI search can read and quote.' },
    { at: 75800, say: 'Open source, and made for TYPO3 version fourteen.', show: 'Open source, and made for TYPO3 v14.' },
    { at: 81400, say: 'Desiderio. Free to use. Paid plans add support.', show: 'Desiderio. Free to use. Paid plans add support.' },
];
/** The voice: unhurried, soft and close, like a designer talking about a product he cares about. */
const DELIVERY = { stability: 0.62, similarity_boost: 0.78, style: 0.18, speed: 0.9 };
/**
 * The score. `at` is where it enters the film: the generator lets its music
 * resolve a few seconds before the requested length, so the cue starts late
 * and its final chord lands under the last line.
 */
const MUSIC = {
    prompt: 'Elegant, modern, minimal electronic score for an 89-second design-led product film. Warm felt piano and '
        + 'soft synth pads open quietly; from 16 seconds a gentle pulsing beat, light percussion and airy strings carry '
        + 'a steady, optimistic momentum that keeps building. A bright, hopeful swell at 76 seconds, sustained at full '
        + 'warmth through 84 seconds, then one long, resolved final chord that rings to the very last second at 89 '
        + 'seconds. The music never stops or fades before 86 seconds. Calm, refined, confident. Instrumental only, no vocals.',
    length: 89000,
    at: 4000,
};

async function fal(endpoint, input) {
    const response = await fetch(`https://fal.run/${endpoint}`, {
        method: 'POST',
        headers: { Authorization: `Key ${KEY}`, 'Content-Type': 'application/json' },
        body: JSON.stringify(input),
    });
    if (!response.ok) {
        throw new Error(`${endpoint}: ${response.status} ${(await response.text()).slice(0, 300)}`);
    }
    return response.json();
}

async function download(url, file) {
    const response = await fetch(url);
    if (!response.ok) throw new Error(`download ${url}: ${response.status}`);
    writeFileSync(file, Buffer.from(await response.arrayBuffer()));
}

const seconds = (file) => Number(execFileSync('ffprobe', ['-v', 'error', '-show_entries', 'format=duration', '-of', 'csv=p=0', file]).toString().trim());

mkdirSync(AUDIO, { recursive: true });
const only = process.argv.find((arg) => arg === '--voice' || arg === '--music');

const lineArg = process.argv.find((arg) => arg.startsWith('--line='));
const onlyLine = lineArg ? Number(lineArg.split('=')[1]) : null;
const previous = onlyLine ? JSON.parse(readFileSync(join(AUDIO, 'narration.json'), 'utf8')).lines : [];

if (only !== '--music') {
    const lines = [];
    for (const [index, line] of LINES.entries()) {
        if (onlyLine && index + 1 !== onlyLine) {
            const kept = previous[index];
            lines.push({ ...line, file: kept.file, duration: kept.duration });
            continue;
        }
        const result = await fal('fal-ai/elevenlabs/tts/multilingual-v2', {
            text: line.say,
            voice: VOICE,
            ...DELIVERY,
            speed: line.speed ?? DELIVERY.speed,
            language_code: 'en',
            previous_text: LINES[index - 1]?.say ?? null,
            next_text: LINES[index + 1]?.say ?? null,
        });
        const file = `voice-${String(index + 1).padStart(2, '0')}.mp3`;
        await download(result.audio.url, join(AUDIO, file));
        const duration = Math.round(seconds(join(AUDIO, file)) * 1000);
        lines.push({ ...line, file, duration });
        console.log(`${file}  ${duration} ms  ${line.say}`);
    }
    writeFileSync(join(AUDIO, 'narration.json'), JSON.stringify({ voice: VOICE, musicAt: MUSIC.at, lines }, null, 2) + '\n');
    for (const [index, line] of lines.entries()) {
        const next = lines[index + 1];
        if (next && line.at + line.duration > next.at - 150) {
            console.warn(`line ${index + 1} runs to ${line.at + line.duration} ms, into line ${index + 2} at ${next.at} ms`);
        }
    }
}

if (only !== '--voice') {
    const result = await fal('fal-ai/elevenlabs/music', {
        prompt: MUSIC.prompt,
        music_length_ms: MUSIC.length,
        force_instrumental: true,
        output_format: 'mp3_44100_192',
    });
    await download(result.audio.url, join(AUDIO, 'music.mp3'));
    console.log(`music.mp3  ${Math.round(seconds(join(AUDIO, 'music.mp3')) * 1000)} ms`);
}

// Keep the generator honest about what it wrote.
console.log(readFileSync(join(AUDIO, 'narration.json'), 'utf8').length > 0 ? 'done' : 'no narration');
