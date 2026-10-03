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
 * One line per scene of film.html. `at` is the scene's start in milliseconds;
 * `say` is what the voice reads, `show` what the caption prints (digits where
 * the voice spells a number out).
 */
const LINES = [
    { at: 300, say: 'Meet Desiderio, for TYPO3.', show: 'Meet Desiderio, for TYPO3.' },
    { at: 3300, say: 'Fifteen presets, one switch. Every content element follows the theme you pick.', show: '15 presets, one switch. Every content element follows the theme you pick.' },
    { at: 8850, say: 'Light and dark, from the same tokens.', show: 'Light and dark, from the same tokens.' },
    { at: 11650, say: 'Two hundred and forty-four content elements, free and open source.', show: '244 content elements, free and open source.' },
    { at: 16100, say: 'The demo site speaks four languages.', show: 'The demo site speaks four languages.' },
    { at: 19500, say: 'And your editors keep the tools they already like.', show: 'And your editors keep the tools they already like.' },
    { at: 22700, say: 'Free to use. Paid plans add support.', show: 'Free to use. Paid plans add support.', speed: 1.1 },
];
const MUSIC = {
    prompt: 'Uplifting modern film-score cue for a bright product film, major key, warm and optimistic. Pulsing string '
        + 'ostinato, rising synth arpeggios, warm French horns and a big hopeful build to a soaring peak around 20 seconds, '
        + 'then a confident, smiling resolve on the final chord at 26 seconds. Epic but light, cinematic, joyful. '
        + 'Instrumental only, no vocals.',
    length: 27000,
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
            stability: 0.6,
            similarity_boost: 0.75,
            style: 0.15,
            speed: line.speed ?? 0.95,
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
    writeFileSync(join(AUDIO, 'narration.json'), JSON.stringify({ voice: VOICE, lines }, null, 2) + '\n');
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
