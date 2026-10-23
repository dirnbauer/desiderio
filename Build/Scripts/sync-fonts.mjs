import { copyFileSync, existsSync, mkdirSync, readFileSync } from 'node:fs';
import { basename, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

// Tailwind preserves Fontsource's relative font URLs. Copy every referenced
// face alongside the generated CSS, including subsets added by a font update.
const root = join(dirname(fileURLToPath(import.meta.url)), '../..');
const source = readFileSync(join(root, 'Resources/Private/Tailwind/desiderio.css'), 'utf8');
const css = readFileSync(join(root, 'Resources/Public/Css/desiderio-tailwind.css'), 'utf8');
const providers = [...source.matchAll(/@import\s+["'](@fontsource(?:-variable)?\/[^"']+)["']/g)]
  .map((match) => dirname(fileURLToPath(import.meta.resolve(match[1]))));
const fonts = new Set([...css.matchAll(/url\(\s*['"]?\.\/files\/([^)'"\s]+)['"]?\s*\)/g)]
  .map((match) => match[1]));
const target = join(root, 'Resources/Public/Css/files');
mkdirSync(target, { recursive: true });

for (const font of fonts) {
  if (font !== basename(font)) throw new Error(`Unexpected font path: ${font}`);
  const file = providers.map((provider) => join(provider, 'files', font)).find(existsSync);
  if (!file) throw new Error(`Fontsource does not provide the referenced font: ${font}`);
  copyFileSync(file, join(target, font));
}
console.log(`Synced ${fonts.size} local Fontsource faces.`);
