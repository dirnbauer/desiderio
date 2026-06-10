import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = dirname(dirname(dirname(fileURLToPath(import.meta.url))));
const components = [
  'prism-core.min.js',
  'prism-markup.min.js',
  'prism-clike.min.js',
  'prism-javascript.min.js',
  'prism-typescript.min.js',
  'prism-css.min.js',
  'prism-markup-templating.min.js',
  'prism-php.min.js',
  'prism-yaml.min.js',
];

const sourceDir = join(root, 'node_modules', 'prismjs', 'components');
const target = join(root, 'Resources', 'Public', 'Js', 'prism-lite.js');
const banner = `/*! PrismJS lite bundle for Desiderio code blocks. Built from prismjs/components. */\nwindow.Prism = window.Prism || {};\nwindow.Prism.manual = true;\n`;

// Local grammars without an upstream Prism component (Fluid, TypoScript).
const extras = readFileSync(join(root, 'Build', 'Scripts', 'prism-lite-extras.js'), 'utf8');

const body = components
  .map((component) => readFileSync(join(sourceDir, component), 'utf8'))
  .concat(extras)
  .join('\n');

mkdirSync(dirname(target), { recursive: true });
writeFileSync(target, `${banner}${body}\n`, 'utf8');
