import { execFileSync } from 'node:child_process';
import { createHash } from 'node:crypto';
import { copyFileSync, existsSync, mkdirSync, readFileSync, readdirSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { FIRST_CODEPOINT, planIconFonts } from './lib/hugeicons-font-plan.mjs';

/**
 * Generates a self-hosted HugeIcons webfont from the MIT-licensed SVG data in
 * @hugeicons/core-free-icons. The official hugeicons font may NOT be
 * redistributed (https://hugeicons.com/license-agreement), but the free SVG
 * shapes are MIT, so a font compiled from them is redistributable.
 *
 * Pipeline: icon data -> stroke SVGs -> picosvg (stroke-to-fill outlining,
 * runs in a local venv under var/) -> fantasticon -> woff2 + css in
 * Resources/Public/IconFonts/hugeicons/.
 */
const root = join(dirname(fileURLToPath(import.meta.url)), '../..');
const workDir = join(root, 'var/hugeicons-font');
const svgDir = join(workDir, 'svg');
const outlinedDir = join(workDir, 'outlined');
const targetDir = join(root, 'Resources/Public/IconFonts/hugeicons');
const venvDir = join(root, 'var/picosvg-venv');
const manifestPath = join(root, 'Build/hugeicons/codepoints.json');

const icons = await import('@hugeicons/core-free-icons');

const kebab = (name) =>
  name
    .replace(/([A-Z])([A-Z][a-z])/g, '$1-$2')
    .replace(/([a-z])([A-Z])/g, '$1-$2')
    .replace(/([A-Za-z])(\d)/g, '$1-$2')
    .replace(/(\d)([A-Za-z])/g, '$1-$2')
    .toLowerCase();

const attributeName = (key) => key.replace(/[A-Z]/g, (c) => '-' + c.toLowerCase());

const renderSvg = (icon) => {
  const body = icon
    .map(([tag, attrs]) => {
      const rendered = Object.entries(attrs)
        .filter(([key]) => key !== 'key')
        .map(([key, value]) => `${attributeName(key)}="${value}"`)
        .join(' ');
      return `<${tag} ${rendered}/>`;
    })
    .join('');

  return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">${body}</svg>`;
};

// 1. Export every canonical <Name>Icon entry as a stroke SVG.
const names = new Map();
for (const exportName of Object.keys(icons)) {
  if (!exportName.endsWith('Icon')) {
    continue;
  }
  const slug = kebab(exportName.slice(0, -'Icon'.length));
  if (!names.has(slug)) {
    names.set(slug, icons[exportName]);
  }
}

// Keep removed public names with their original MIT artwork. Never reassign
// their font/codepoint pair to another glyph when an upstream name disappears.
const legacy = JSON.parse(readFileSync(join(root, 'Build/hugeicons/legacy-icons.json'), 'utf8'));
for (const [slug, icon] of Object.entries(legacy.icons)) {
  if (!names.has(slug)) names.set(slug, icon);
}
const plan = planIconFonts([...names.keys()], JSON.parse(readFileSync(manifestPath, 'utf8')));
for (const font of plan.fonts) {
  for (const slug of font.icons) {
    if (!names.has(slug)) throw new Error(`Missing source for published icon ${slug}; preserve its artwork first`);
  }
}

const pythonVersion = readFileSync(join(root, '.python-version'), 'utf8').trim();
const versionOf = (executable) => execFileSync(executable, ['-c', 'import platform; print(platform.python_version())'], { encoding: 'utf8' }).trim();
if (versionOf('python3') !== pythonVersion) {
  throw new Error(`Use Python ${pythonVersion} from .python-version for reproducible font builds`);
}
if (existsSync(join(venvDir, 'bin/python')) && versionOf(join(venvDir, 'bin/python')) !== pythonVersion) {
  rmSync(venvDir, { recursive: true, force: true });
}
if (!existsSync(join(venvDir, 'bin/python'))) {
  execFileSync('python3', ['-m', 'venv', venvDir], { stdio: 'inherit' });
}
const outlineScript = join(root, 'Build/Scripts/outline_hugeicons.py');
const requirements = join(root, 'Build/hugeicons/requirements.txt');
const requirementsHash = createHash('sha256').update(readFileSync(requirements)).digest('hex');
const installedRequirements = join(venvDir, 'requirements.sha256');
if (!existsSync(installedRequirements) || readFileSync(installedRequirements, 'utf8') !== requirementsHash) {
  execFileSync(join(venvDir, 'bin/python'), ['-m', 'pip', 'install', '--quiet', '-r', requirements], { stdio: 'inherit' });
  writeFileSync(installedRequirements, requirementsHash);
}

// A count alone accepts stale artwork whenever a package changes existing
// shapes. Include every rendered source, build code and pinned tool version.
const sourceHash = createHash('sha256')
  .update(readFileSync(fileURLToPath(import.meta.url)))
  .update(requirementsHash)
  .update(pythonVersion)
  .update(readFileSync(outlineScript));
for (const slug of [...names.keys()].sort()) sourceHash.update(slug).update(renderSvg(names.get(slug)));
const fingerprint = sourceHash.digest('hex');
const fingerprintPath = join(workDir, 'source.sha256');
const outlinedIsComplete =
  existsSync(outlinedDir) &&
  existsSync(fingerprintPath) && readFileSync(fingerprintPath, 'utf8') === fingerprint &&
  readdirSync(outlinedDir).filter((f) => f.endsWith('.svg')).length === names.size &&
  [...names.keys()].every(slug => existsSync(join(outlinedDir, `${slug}.svg`)));

if (!outlinedIsComplete) {
  rmSync(workDir, { recursive: true, force: true });
  mkdirSync(svgDir, { recursive: true });
  mkdirSync(outlinedDir, { recursive: true });
  for (const [slug, icon] of names) {
    writeFileSync(join(svgDir, `${slug}.svg`), renderSvg(icon));
  }
  console.log(`Exported ${names.size} stroke SVGs.`);
}

// 2. Outline strokes to fills with picosvg (icon fonts cannot render strokes).
if (!outlinedIsComplete) {
  execFileSync(join(venvDir, 'bin/python'), [outlineScript, svgDir, outlinedDir], {
    stdio: 'inherit',
  });
  writeFileSync(fingerprintPath, fingerprint);
}

// 3. Each shard uses only U+E001–U+F8FF (6399 slots). Its family name and
// append-only manifest disambiguate repeated codepoints across font files.
const { generateFonts } = await import('fantasticon');
const staged = join(workDir, 'output');
mkdirSync(staged, { recursive: true });
const stagedManifest = join(workDir, 'codepoints.json');
writeFileSync(stagedManifest, JSON.stringify(plan, null, 2) + '\n');
let css = '';
for (const font of plan.fonts) {
  const inputDir = join(workDir, font.name);
  rmSync(inputDir, { recursive: true, force: true });
  mkdirSync(inputDir, { recursive: true });
  const codepoints = {};
  font.icons.forEach((slug, index) => {
    codepoints[slug] = FIRST_CODEPOINT + index;
    copyFileSync(join(outlinedDir, `${slug}.svg`), join(inputDir, `${slug}.svg`));
  });
  await generateFonts({
    inputDir,
    outputDir: staged,
    name: font.name,
    fontTypes: ['woff2'],
    assetTypes: [],
    normalize: true,
    fontHeight: 1000,
    codepoints,
  });
  if (font.name === 'hugeicons') {
    execFileSync(join(venvDir, 'bin/python'), [
      join(root, 'Build/Scripts/preserve_hugeicons_glyphs.py'),
      join(staged, `${font.name}.woff2`),
      join(root, 'Build/hugeicons/published-4.2.0.woff2'),
      join(root, 'Build/hugeicons/published-4.2.0.json'),
      stagedManifest,
    ], { stdio: 'inherit' });
  }
  const hash = createHash('sha256').update(readFileSync(join(staged, `${font.name}.woff2`))).digest('hex').slice(0, 16);
  css += `@font-face {\n    font-family: "${font.name}";\n    font-display: block;\n    src: url("./${font.name}.woff2?${hash}") format("woff2");\n}\n\n`;
}
css += `.hgi:before {\n    font-family: hugeicons !important;\n    font-style: normal;\n    font-weight: normal !important;\n    font-variant: normal;\n    text-transform: none;\n    line-height: 1;\n    -webkit-font-smoothing: antialiased;\n    -moz-osx-font-smoothing: grayscale;\n}\n\n`;
for (const font of plan.fonts) {
  font.icons.forEach((slug, index) => {
    css += `.hgi.hgi-${slug}:before {\n    content: "\\${(FIRST_CODEPOINT + index).toString(16)}";\n`;
    if (font.name !== 'hugeicons') css += `    font-family: "${font.name}" !important;\n`;
    css += '}\n';
  });
}
writeFileSync(join(staged, 'hugeicons.css'), css);

execFileSync(join(venvDir, 'bin/python'), [join(root, 'Build/Scripts/check-hugeicons-fonts.py'), staged, stagedManifest], { stdio: 'inherit' });
mkdirSync(targetDir, { recursive: true });
for (const font of plan.fonts) copyFileSync(join(staged, `${font.name}.woff2`), join(targetDir, `${font.name}.woff2`));
copyFileSync(join(staged, 'hugeicons.css'), join(targetDir, 'hugeicons.css'));
writeFileSync(manifestPath, JSON.stringify(plan, null, 2) + '\n');

// 4. Provenance: MIT license text plus a note about how the font was built.
const version = JSON.parse(
  readFileSync(join(root, 'node_modules/@hugeicons/core-free-icons/package.json'), 'utf8')
).version;
writeFileSync(
  join(targetDir, 'LICENSE-MIT.txt'),
  `This webfont was generated by the Desiderio build (Build/Scripts/build-hugeicons-font.mjs)
from the MIT-licensed SVG icon data in @hugeicons/core-free-icons v${version}
(https://www.npmjs.com/package/@hugeicons/core-free-icons).
The ${Object.keys(legacy.icons).length} removed names in Build/hugeicons/legacy-icons.json retain
their MIT-licensed v${legacy.source.version} artwork and original published codepoints.

It is NOT the official hugeicons webfont, which must not be redistributed
per https://hugeicons.com/license-agreement.

MIT License

Copyright (c) Hugeicons

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
`
);

console.log(`Built ${join(targetDir, 'hugeicons.woff2')} from ${names.size} MIT-licensed SVGs.`);
