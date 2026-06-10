import { copyFileSync, mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

/**
 * Copies the redistributable icon webfonts from node_modules into
 * Resources/Public/IconFonts/<library>/<library>.css|.woff2 so every
 * theme preset loads its icon font from the Desiderio package instead
 * of an external CDN.
 *
 * HugeIcons is intentionally absent here: the Hugeicons license agreement
 * (https://hugeicons.com/license-agreement) covers the free versions and
 * forbids redistributing their icon font as part of downloadable packages.
 * Instead, Build/Scripts/build-hugeicons-font.mjs generates an own webfont
 * from the MIT-licensed @hugeicons/core-free-icons SVG data.
 */
const root = join(dirname(fileURLToPath(import.meta.url)), '../..');
const targetRoot = join(root, 'Resources/Public/IconFonts');

const LIBRARIES = [
  {
    key: 'lucide',
    css: 'node_modules/lucide-static/font/lucide.css',
    woff2: 'node_modules/lucide-static/font/lucide.woff2',
    license: 'node_modules/lucide-static/LICENSE',
    licenseName: 'LICENSE-ISC.txt',
  },
  {
    key: 'tabler',
    css: 'node_modules/@tabler/icons-webfont/dist/tabler-icons.min.css',
    woff2: 'node_modules/@tabler/icons-webfont/dist/fonts/tabler-icons.woff2',
    license: 'node_modules/@tabler/icons-webfont/LICENSE',
    licenseName: 'LICENSE-MIT.txt',
  },
  {
    key: 'phosphor',
    css: 'node_modules/@phosphor-icons/web/src/regular/style.css',
    woff2: 'node_modules/@phosphor-icons/web/src/regular/Phosphor.woff2',
    license: 'node_modules/@phosphor-icons/web/LICENSE',
    licenseName: 'LICENSE-MIT.txt',
  },
  {
    key: 'remixicon',
    css: 'node_modules/remixicon/fonts/remixicon.css',
    woff2: 'node_modules/remixicon/fonts/remixicon.woff2',
    license: 'node_modules/remixicon/License',
    licenseName: 'LICENSE-APACHE-2.0.txt',
  },
];

const FONT_FACE_BLOCK = /@font-face\s*\{[^}]*\}/g;
const FONT_FAMILY = /font-family\s*:\s*["']?([^"';}]+)["']?/;

for (const library of LIBRARIES) {
  const targetDir = join(targetRoot, library.key);
  mkdirSync(targetDir, { recursive: true });

  const css = readFileSync(join(root, library.css), 'utf8');
  const rewritten = css.replace(FONT_FACE_BLOCK, (block) => {
    const family = block.match(FONT_FAMILY)?.[1].trim();
    if (!family) {
      throw new Error(`No font-family found in @font-face of ${library.css}`);
    }
    // Single self-hosted woff2 source; eot/woff/ttf/svg fallbacks are dropped.
    return `@font-face{font-family:"${family}";font-style:normal;font-weight:400;font-display:block;src:url("${library.key}.woff2") format("woff2")}`;
  });

  writeFileSync(join(targetDir, `${library.key}.css`), rewritten);
  copyFileSync(join(root, library.woff2), join(targetDir, `${library.key}.woff2`));
  copyFileSync(join(root, library.license), join(targetDir, library.licenseName));
  console.log(`Synced ${library.key} icon font into Resources/Public/IconFonts/${library.key}/`);
}
