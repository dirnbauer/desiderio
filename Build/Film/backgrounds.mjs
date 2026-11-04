// Copied from packages/site_package/Build/Film/lib/backgrounds.mjs of the webconsulting TYPO3 lab
// (github.com/dirnbauer/webconsulting-typo3-lab); keep in sync: change both together.
// The product film (film.html) loads it with fetch + a Blob URL, because
// Build/Scripts/render-film.mjs serves .mjs files without a JavaScript MIME type;
// so this module must stay free of imports.

/**
 * Generative film backgrounds, drawn on one 2D canvas behind every scene.
 *
 *   import { createBackground } from './lib/backgrounds.mjs';
 *   const draw = createBackground(canvas, { style: 'grid', palette: ['#c7863f', '#4a57ff', '#2c5a52'], seed: 1 });
 *   draw(tMs, strength);                        // on every seek
 *   draw(tMs, strength, { focus: { x, y, k } }); // optional: where the active frame is
 *
 * Every style is a pure function of the time: no Math.random, no Date, no
 * state carried from one frame to the next. Random layouts come from a seeded
 * generator at creation, so seeking to the same time paints the same pixels
 * on every run, and the renderers may step through a film in any order.
 * No dependencies, no WebGL; the canvas keeps its own size (1920 × 1080 in
 * the lab films, 1600 × 900 in the product film), and every style scales with it.
 *
 * `strength` (0 to 1) is the scene's light: film.html eases it between scenes
 * (`auraAt(t)`), so a hook is dim and a title or end card is bright.
 * `extra.focus` ({x, y} in canvas fractions, k = weight 0 to 1) is the centre
 * of the frame on screen; `grid` lights up near it, the other styles ignore it.
 * `extra.colours` ([[r, g, b], ...]) replaces the palette for this frame (the
 * product film's aurora follows its story's colours).
 *
 * The styles and the films that use them (packages/site_package/Build/Film/films/*.mjs, `background`):
 *   aurora  three soft radial lights on slow sine paths, added with `lighter`.
 *           The look of every film before 2026-11; the default, so a spec without
 *           `background` renders exactly as it always did.
 *   mesh    six palette blobs on slow Lissajous paths, warped by two octaves of
 *           value noise, added with `lighter`, under a faint vignette. Product film,
 *           lab overview, the shadcn skill film.
 *   grid    a perspective floor grid under a dark sky, dollying slowly towards the
 *           horizon; its intersections glow in the first palette colour near the
 *           active frame. T3 Ops, CapexOne Plus.
 *   orbits  40 particles on tilted elliptical orbits around three slowly drifting
 *           centres, joined by faint lines when two come close. Agentic skills, the
 *           skill films, the strategy film.
 *   pulse   soft rings expanding from an off-centre point every 4 s, tinted with the
 *           palette in turn, low contrast. MedAtlas.
 *   bands   soft horizontal bands whose highlights drift at different speeds over
 *           faint ledger rules and a margin line. Ledger.
 *
 * This file is copied verbatim to packages/desiderio/Build/Film/backgrounds.mjs
 * (the product film); change both together.
 */

const TAU = Math.PI * 2;
const clamp = (v, a = 0, b = 1) => Math.min(b, Math.max(a, v));
const lerp = (a, b, p) => a + (b - a) * p;
const mod = (v, n) => ((v % n) + n) % n;

/** Seeded generator (mulberry32), the same one film.html uses for its grain. */
export function rng(seed) {
    return () => {
        seed = (seed + 0x6d2b79f5) | 0;
        let t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
        t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
        return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
}

/** A stable value in [0, 1) for an integer lattice point. */
function hash(seed, i) {
    let h = Math.imul(i | 0, 0x27d4eb2d) ^ Math.imul((seed | 0) + 0x9e3779b9, 0x165667b1);
    h = Math.imul(h ^ (h >>> 15), 0x85ebca77);
    h = Math.imul(h ^ (h >>> 13), 0xc2b2ae3d);
    return ((h ^ (h >>> 16)) >>> 0) / 4294967296;
}

/** One-dimensional value noise in [-1, 1], smooth in x. */
function noise(seed, x) {
    const i = Math.floor(x);
    const f = x - i;
    return lerp(hash(seed, i), hash(seed, i + 1), f * f * (3 - 2 * f)) * 2 - 1;
}

/** Two octaves of value noise. */
const fbm = (seed, x) => noise(seed, x) * 0.66 + noise(seed + 101, x * 2.07 + 17.3) * 0.34;

/** '#rrggbb' (or [r, g, b]) to [r, g, b]. */
export function rgbOf(colour) {
    if (Array.isArray(colour)) return colour;
    return [1, 3, 5].map((i) => parseInt(colour.slice(i, i + 2), 16));
}

const rgba = ([r, g, b], a) => `rgba(${Math.round(r)},${Math.round(g)},${Math.round(b)},${a})`;
/** A colour moved towards white by p. */
const lift = (rgb, p) => rgb.map((v) => lerp(v, 255, p));

/** A soft round light at (x, y), added to what is there. */
function glow(ctx, x, y, r, rgb, inner, middle = inner * 0.4) {
    const gradient = ctx.createRadialGradient(x, y, 0, x, y, r);
    gradient.addColorStop(0, rgba(rgb, inner));
    gradient.addColorStop(0.45, rgba(rgb, middle));
    gradient.addColorStop(1, rgba(rgb, 0));
    ctx.fillStyle = gradient;
    ctx.fillRect(x - r, y - r, r * 2, r * 2);
}

const STYLES = {
    /**
     * The original aura, call for call: the same three lights, the same
     * gradients, full-canvas fills, so existing films stay pixel-identical.
     * `radii` are the lights' radii in px (1920 wide: 940, 840, 1030).
     */
    aurora: ({ ctx, W, H, colours, radii = [940, 840, 1030] }) => (t, strength, extra) => {
        const palette = extra.colours ?? colours;
        const s = t / 1000;
        ctx.globalCompositeOperation = 'lighter';
        [
            [0.28 + 0.1 * Math.sin(s * 0.21), 0.36 + 0.1 * Math.cos(s * 0.17), radii[0]],
            [0.74 + 0.09 * Math.cos(s * 0.19 + 1), 0.3 + 0.12 * Math.sin(s * 0.15 + 2), radii[1]],
            [0.55 + 0.14 * Math.sin(s * 0.13 + 4), 0.82 + 0.08 * Math.cos(s * 0.23 + 1), radii[2]],
        ].forEach(([x, y, r], i) => {
            const [R, G, B] = palette[i % palette.length].map(Math.round);
            const gradient = ctx.createRadialGradient(x * W, y * H, 0, x * W, y * H, r);
            gradient.addColorStop(0, `rgba(${R},${G},${B},${0.5 * strength})`);
            gradient.addColorStop(0.45, `rgba(${R},${G},${B},${0.2 * strength})`);
            gradient.addColorStop(1, `rgba(${R},${G},${B},0)`);
            ctx.fillStyle = gradient;
            ctx.fillRect(0, 0, W, H);
        });
    },

    mesh: ({ ctx, W, H, colours, random, seed }) => {
        const blobs = Array.from({ length: 6 }, (_, i) => ({
            rgb: colours[i % colours.length],
            cx: 0.18 + random() * 0.64,
            cy: 0.2 + random() * 0.6,
            ax: 0.12 + random() * 0.14,
            ay: 0.1 + random() * 0.12,
            fx: 0.07 + random() * 0.1,
            fy: 0.06 + random() * 0.1,
            px: random() * TAU,
            py: random() * TAU,
            r: 0.3 + random() * 0.16,
            k: 0.6 + random() * 0.3,
        }));
        return (t, strength, extra) => {
            const palette = extra.colours ?? null;
            const s = t / 1000;
            ctx.globalCompositeOperation = 'lighter';
            blobs.forEach((b, i) => {
                const warpX = fbm(seed + i * 13, s * 0.07) * 0.09;
                const warpY = fbm(seed + i * 13 + 5, s * 0.07 + 9.1) * 0.08;
                const x = (b.cx + b.ax * Math.sin(s * b.fx + b.px) + warpX) * W;
                const y = (b.cy + b.ay * Math.sin(s * b.fy * 1.31 + b.py) + warpY) * H;
                const r = b.r * W * (1 + 0.14 * fbm(seed + i * 13 + 9, s * 0.05 + 3.3));
                const rgb = palette ? palette[i % palette.length] : b.rgb;
                glow(ctx, x, y, r, rgb, 0.34 * b.k * strength, 0.12 * b.k * strength);
            });
            ctx.globalCompositeOperation = 'source-over';
            const vignette = ctx.createRadialGradient(W / 2, H * 0.48, Math.min(W, H) * 0.35, W / 2, H * 0.48, Math.hypot(W, H) * 0.62);
            vignette.addColorStop(0, 'rgba(0,0,0,0)');
            vignette.addColorStop(1, 'rgba(0,0,0,0.38)');
            ctx.fillStyle = vignette;
            ctx.fillRect(0, 0, W, H);
        };
    },

    grid: ({ ctx, W, H, colours }) => {
        const horizon = H * 0.46;
        const F = H * 0.62;
        const spacing = 1.15;
        const near = 0.55;
        const far = 46;
        const reach = Math.ceil(((W / 2) * far) / F / spacing);
        const line = lift(colours[0], 0.35);
        return (t, strength, extra) => {
            const s = t / 1000;
            const dolly = mod(s * 0.32, spacing);
            const vx = W / 2 + Math.sin(s * 0.05) * W * 0.03;
            // Sky: a low light on the horizon, a second colour high up.
            ctx.globalCompositeOperation = 'lighter';
            glow(ctx, W * (0.5 + 0.18 * Math.sin(s * 0.06)), horizon, W * 0.55, colours[1 % colours.length], 0.2 * strength, 0.07 * strength);
            glow(ctx, W * (0.22 + 0.06 * Math.cos(s * 0.08)), H * 0.12, W * 0.4, colours[2 % colours.length], 0.14 * strength, 0.05 * strength);
            const project = (X, Z) => [vx + (X * F) / Z, horizon + F / Z];
            // Depth lines towards the vanishing point.
            const fade = ctx.createLinearGradient(0, horizon, 0, H);
            fade.addColorStop(0, rgba(line, 0));
            fade.addColorStop(0.25, rgba(line, 0.12 * strength));
            fade.addColorStop(1, rgba(line, 0.32 * strength));
            ctx.globalCompositeOperation = 'source-over';
            ctx.strokeStyle = fade;
            ctx.lineWidth = 1.2;
            ctx.beginPath();
            for (let j = -reach; j <= reach; j++) {
                const [x0, y0] = project(j * spacing, near);
                const [x1, y1] = project(j * spacing, far);
                ctx.moveTo(x0, y0);
                ctx.lineTo(x1, y1);
            }
            ctx.stroke();
            // Cross lines, moving towards the camera.
            const rows = [];
            for (let k = 0; ; k++) {
                const Z = near + spacing - dolly + k * spacing;
                if (Z > far) break;
                rows.push(Z);
            }
            for (const Z of rows) {
                const y = horizon + F / Z;
                if (y > H + 2) continue;
                const depth = clamp((y - horizon) / (H - horizon));
                ctx.strokeStyle = rgba(line, (0.05 + 0.27 * depth) * strength * clamp((y - horizon) / 40));
                ctx.beginPath();
                ctx.moveTo(0, y);
                ctx.lineTo(W, y);
                ctx.stroke();
            }
            // Intersections: faint everywhere, lit near the active frame.
            const focus = extra.focus;
            const fx = (focus ? focus.x : 0.5 + 0.28 * Math.sin(s * 0.09)) * W;
            const fy = clamp(focus ? focus.y * H + H * 0.24 : H * 0.8, horizon + 40, H);
            const fk = focus ? clamp(focus.k ?? 1) : 0.35;
            ctx.globalCompositeOperation = 'lighter';
            for (const Z of rows) {
                const y = horizon + F / Z;
                if (y > H + 4 || y < horizon + 6) continue;
                const depth = clamp((y - horizon) / (H - horizon));
                for (let j = -reach; j <= reach; j++) {
                    const x = vx + (j * spacing * F) / Z;
                    if (x < -10) continue;
                    if (x > W + 10) break;
                    const d = ((x - fx) / (W * 0.2)) ** 2 + ((y - fy) / (H * 0.2)) ** 2;
                    const lit = fk * Math.exp(-d);
                    const a = (0.1 + 0.75 * lit) * depth * strength;
                    if (a < 0.008) continue;
                    const r = 1.2 + 3 * depth;
                    if (lit > 0.06) glow(ctx, x, y, r * 8, colours[0], a * 0.7, a * 0.2);
                    ctx.fillStyle = rgba(lift(colours[0], 0.5 - 0.3 * lit), Math.min(1, a * 1.6));
                    ctx.fillRect(x - r / 2, y - r / 2, r, r);
                }
            }
        };
    },

    orbits: ({ ctx, W, H, colours, random }) => {
        const centres = [[0.5, 0.47], [0.27, 0.36], [0.75, 0.6]].map(([x, y]) => [x + (random() - 0.5) * 0.06, y + (random() - 0.5) * 0.06, random() * TAU]);
        const unit = Math.min(W, H);
        const particles = Array.from({ length: 40 }, (_, i) => ({
            c: i % centres.length,
            r: unit * (0.1 + random() * 0.42),
            e: 0.42 + random() * 0.4,
            tilt: random() * Math.PI,
            w: (0.05 + random() * 0.09) * (random() < 0.5 ? -1 : 1),
            phase: random() * TAU,
            size: 1.4 + random() * 2.2,
            rgb: colours[i % colours.length],
        }));
        const reach = unit * 0.2;
        return (t, strength) => {
            const s = t / 1000;
            const centre = centres.map(([x, y, p], i) => [(x + 0.025 * Math.sin(s * 0.05 + p)) * W, (y + 0.02 * Math.cos(s * 0.04 + p + i)) * H]);
            ctx.globalCompositeOperation = 'lighter';
            centre.forEach(([x, y], i) => glow(ctx, x, y, unit * 0.62, colours[i % colours.length], 0.16 * strength, 0.06 * strength));
            const points = particles.map((p) => {
                const a = p.phase + p.w * s;
                const ex = p.r * Math.cos(a);
                const ey = p.r * p.e * Math.sin(a);
                const [cx, cy] = centre[p.c];
                return [cx + ex * Math.cos(p.tilt) - ey * Math.sin(p.tilt), cy + ex * Math.sin(p.tilt) + ey * Math.cos(p.tilt)];
            });
            // A few of the orbits themselves, very faint.
            ctx.globalCompositeOperation = 'source-over';
            ctx.lineWidth = 1;
            particles.slice(0, 6).forEach((p) => {
                const [cx, cy] = centre[p.c];
                ctx.strokeStyle = rgba(lift(p.rgb, 0.4), 0.05 * strength);
                ctx.beginPath();
                ctx.ellipse(cx, cy, p.r, p.r * p.e, p.tilt, 0, TAU);
                ctx.stroke();
            });
            for (let i = 0; i < points.length; i++) {
                for (let j = i + 1; j < points.length; j++) {
                    const d = Math.hypot(points[i][0] - points[j][0], points[i][1] - points[j][1]);
                    if (d >= reach) continue;
                    ctx.strokeStyle = rgba(lift(particles[i].rgb, 0.55), (1 - d / reach) ** 2 * 0.3 * strength);
                    ctx.beginPath();
                    ctx.moveTo(points[i][0], points[i][1]);
                    ctx.lineTo(points[j][0], points[j][1]);
                    ctx.stroke();
                }
            }
            ctx.globalCompositeOperation = 'lighter';
            points.forEach(([x, y], i) => {
                const p = particles[i];
                glow(ctx, x, y, p.size * 6, p.rgb, 0.4 * strength, 0.12 * strength);
                ctx.fillStyle = rgba(lift(p.rgb, 0.6), 0.85 * strength);
                ctx.beginPath();
                ctx.arc(x, y, p.size, 0, TAU);
                ctx.fill();
            });
        };
    },

    pulse: ({ ctx, W, H, colours, random }) => {
        const ox = W * (0.66 + (random() - 0.5) * 0.04);
        const oy = H * (0.42 + (random() - 0.5) * 0.04);
        const period = 4000;
        const life = 14000;
        const reach = Math.hypot(Math.max(ox, W - ox), Math.max(oy, H - oy)) * 1.05;
        return (t, strength) => {
            const s = t / 1000;
            ctx.globalCompositeOperation = 'lighter';
            glow(ctx, ox, oy, W * 0.3, colours[0], 0.24 * strength, 0.08 * strength);
            glow(ctx, W * (0.18 + 0.04 * Math.sin(s * 0.07)), H * (0.8 + 0.04 * Math.cos(s * 0.09)), W * 0.42, colours[1 % colours.length], 0.12 * strength, 0.04 * strength);
            for (let k = Math.floor((t - life) / period) + 1; k <= Math.floor(t / period); k++) {
                const age = t - k * period;
                const p = age / life;
                const r = W * 0.03 + p * reach;
                const a = (1 - p) ** 1.6 * clamp(age / 700) * strength;
                const rgb = colours[mod(k, colours.length)];
                const band = W * (0.02 + 0.07 * p);
                const ring = ctx.createRadialGradient(ox, oy, Math.max(0, r - band), ox, oy, r + band);
                ring.addColorStop(0, rgba(rgb, 0));
                ring.addColorStop(0.5, rgba(rgb, 0.13 * a));
                ring.addColorStop(1, rgba(rgb, 0));
                ctx.fillStyle = ring;
                ctx.fillRect(ox - r - band, oy - r - band, (r + band) * 2, (r + band) * 2);
                ctx.strokeStyle = rgba(lift(rgb, 0.45), 0.09 * a);
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.arc(ox, oy, r, 0, TAU);
                ctx.stroke();
            }
        };
    },

    bands: ({ ctx, W, H, colours, random }) => {
        const count = 7;
        const bands = Array.from({ length: count }, (_, i) => ({
            y: ((i + 0.5) / count + (random() - 0.5) * 0.05) * H,
            h: H * (0.05 + random() * 0.07),
            rgb: colours[i % colours.length],
            speed: (0.012 + random() * 0.03) * W * (i % 2 ? -1 : 1),
            x0: random() * W * 2,
            stretch: 3.2 + random() * 2.4,
            k: 0.55 + random() * 0.45,
        }));
        const rule = Math.round(H / 20);
        return (t, strength) => {
            const s = t / 1000;
            ctx.globalCompositeOperation = 'lighter';
            for (const b of bands) {
                // The band: a soft strip across the whole width.
                const strip = ctx.createLinearGradient(0, b.y - b.h, 0, b.y + b.h);
                strip.addColorStop(0, rgba(b.rgb, 0));
                strip.addColorStop(0.5, rgba(b.rgb, 0.05 * b.k * strength));
                strip.addColorStop(1, rgba(b.rgb, 0));
                ctx.fillStyle = strip;
                ctx.fillRect(0, b.y - b.h, W, b.h * 2);
                // Its highlight, travelling along it; it wraps where it cannot be seen.
                const r = b.h * 1.6;
                const span = W + 2 * r * b.stretch;
                const x = -r * b.stretch + mod(b.x0 + b.speed * s, span);
                ctx.save();
                ctx.translate(x, b.y);
                ctx.scale(b.stretch, 1);
                glow(ctx, 0, 0, r, b.rgb, 0.3 * b.k * strength, 0.1 * b.k * strength);
                ctx.restore();
            }
            // Ledger rules drifting up, and the margin line.
            ctx.globalCompositeOperation = 'source-over';
            ctx.lineWidth = 1;
            ctx.strokeStyle = `rgba(255,255,255,${0.035 * strength})`;
            ctx.beginPath();
            for (let y = rule - mod(s * 7, rule); y < H; y += rule) {
                ctx.moveTo(0, Math.round(y) + 0.5);
                ctx.lineTo(W, Math.round(y) + 0.5);
            }
            ctx.stroke();
            ctx.strokeStyle = rgba(lift(colours[0], 0.2), 0.1 * strength);
            ctx.beginPath();
            ctx.moveTo(Math.round(W * 0.075) + 0.5, 0);
            ctx.lineTo(Math.round(W * 0.075) + 0.5, H);
            ctx.stroke();
        };
    },
};

export const STYLE_NAMES = Object.keys(STYLES);

/**
 * @param {HTMLCanvasElement} canvas
 * @param {{style?: string, palette?: Array<string|number[]>, seed?: number, base?: string, radii?: number[]}} options
 *   base: the stage colour under everything; radii: the aurora's light radii in px.
 * @returns {(tMs: number, strength?: number, extra?: {focus?: {x: number, y: number, k?: number}|null, colours?: number[][]}) => void}
 */
export function createBackground(canvas, { style = 'aurora', palette = ['#c7863f', '#4a57ff', '#2c5a52'], seed = 1, base = '#070709', radii } = {}) {
    const make = STYLES[style];
    if (!make) throw new Error(`Unknown background style "${style}" (${STYLE_NAMES.join(', ')})`);
    const ctx = canvas.getContext('2d');
    const W = canvas.width;
    const H = canvas.height;
    const colours = palette.map(rgbOf);
    const paint = make({ ctx, W, H, colours, random: rng(seed), seed, radii });
    return (t, strength = 1, extra = {}) => {
        ctx.globalCompositeOperation = 'source-over';
        ctx.fillStyle = base;
        ctx.fillRect(0, 0, W, H);
        paint(t, strength, extra ?? {});
        ctx.globalCompositeOperation = 'source-over';
    };
}
