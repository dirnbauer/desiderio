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

/**
 * Every partial must open and close its own comments. A partial that ends
 * inside a comment does not fail here on its own — the stripper below is a
 * non-greedy regex over the concatenated text, so it would swallow everything
 * up to the next partial's first closer and leave the remains of that comment
 * standing as raw text between two rules. The result is still written out and
 * only fails much later, in whatever build consumes desiderio.css. The 4.1.0
 * split of components.css into four partials cut two section headers exactly
 * that way.
 */
function assertBalancedComments(file, source) {
  let depth = 0;
  for (let i = 0; i < source.length - 1; i++) {
    if (source[i] === '/' && source[i + 1] === '*') {
      depth++;
      i++;
    } else if (source[i] === '*' && source[i + 1] === '/') {
      depth--;
      i++;
      if (depth < 0) {
        throw new Error(`${file} closes a comment it never opened.`);
      }
    }
  }
  if (depth > 0) {
    throw new Error(`${file} ends inside a comment; close it before the file ends.`);
  }
}

const css = manifest
  .map((file) => {
    const source = readFileSync(join(partialsDir, file), 'utf8').trimEnd();
    assertBalancedComments(file, source);

    return source;
  })
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
