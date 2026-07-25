/**
 * Puts a preview page into a specific (preset, mode, viewport) state.
 *
 * Three things here are load-bearing and were each a source of false results
 * before they were handled:
 *
 * 1. Dark mode is applied by Resources/Public/Js/desiderio.js reading
 *    localStorage['d-theme'], so the value must exist BEFORE the document
 *    loads. Toggling the .dark class afterwards fights the script.
 * 2. Presets carry four different font families (Inter, Nunito Sans, Geist,
 *    JetBrains Mono), all font-display:swap. Measuring before the swapped face
 *    has loaded records fallback metrics, which turns a fine layout into a
 *    phantom overflow.
 * 3. The preview PAGE object sets `body { background: transparent }` so
 *    thumbnails composite nicely. axe's color-contrast rule cannot resolve a
 *    transparent ancestor and returns "incomplete" for the entire page, so a
 *    solid token background has to be painted in first.
 */

export const PRESET_DEFAULT = 'b0';

/** Call before the first navigation on a context. */
export async function primeMode(context, mode) {
    await context.addInitScript((value) => {
        try {
            window.localStorage.setItem('d-theme', value);
        } catch {
            // Storage can be unavailable; the data-theme fallback still applies.
        }
    }, mode);
}

export async function applyPreset(page, preset) {
    await page.evaluate((value) => {
        document.body.dataset.shadcnPreset = value;
    }, preset);
    await settleFonts(page);
}

export async function settleFonts(page) {
    await page.evaluate(async () => {
        const family = getComputedStyle(document.body).getPropertyValue('--d-font-sans').trim();
        if (family !== '') {
            try {
                await document.fonts.load(`16px ${family}`);
                await document.fonts.load(`700 16px ${family}`);
            } catch {
                // An unparseable family is not worth failing a render over.
            }
        }
        await document.fonts.ready;
    });
}

export async function paintOpaqueBackground(page) {
    await page.addStyleTag({
        content: 'body{background:var(--background)!important;color:var(--foreground)}',
    });
}

/** Fails loudly rather than screenshotting an unstyled frame. */
export async function assertThemeApplied(page) {
    const state = await page.evaluate(() => ({
        background: getComputedStyle(document.body).getPropertyValue('--background').trim(),
        fonts: document.fonts.status,
        preset: document.body.dataset.shadcnPreset ?? '',
        dark: document.documentElement.classList.contains('dark'),
    }));

    if (state.background === '') {
        throw new Error('Theme tokens are not applied — shadcn-theme.css did not load.');
    }

    return state;
}
