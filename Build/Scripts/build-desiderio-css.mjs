import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '../..');
const partialsDir = join(root, 'Resources/Private/Css/desiderio');
const manifestPath = join(partialsDir, 'manifest.txt');
const outputPath = join(root, 'Resources/Public/Css/desiderio.css');

const manifest = readFileSync(manifestPath, 'utf8')
  .split('\n')
  .map((line) => line.trim())
  .filter(Boolean);

const css = manifest
  .map((file) => readFileSync(join(partialsDir, file), 'utf8').trimEnd())
  .join('\n\n')
  .concat('\n');

/**
 * Conservative dependency-free minification: strips comments and collapses
 * structural whitespace. Quoted strings (content:, url()) survive because the
 * transforms only touch whitespace adjacent to syntax characters; the CSS
 * partials do not use multi-word quoted strings with significant spacing
 * around braces/colons.
 */
function minifyCss(input) {
  return input
    .replace(/\/\*[\s\S]*?\*\//g, '')
    .replace(/\s+/g, ' ')
    // Only braces, semicolons, and commas — ':' must stay untouched because
    // descendant selectors like ".frame :where(h2)" change meaning when the
    // space is removed.
    .replace(/\s*([{};,])\s*/g, '$1')
    .replace(/;}/g, '}')
    .trim()
    .concat('\n');
}

writeFileSync(outputPath, minifyCss(css));
console.log(`Built ${outputPath} (minified) from ${manifest.length} partials.`);
