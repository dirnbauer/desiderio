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

writeFileSync(outputPath, css);
console.log(`Built ${outputPath} from ${manifest.length} partials.`);
