import { execFileSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

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
    names.set(slug, exportName);
  }
}

// The slow outlining step is skipped when var/ already holds a complete run.
const outlinedIsComplete =
  existsSync(outlinedDir) &&
  (await import('node:fs')).readdirSync(outlinedDir).filter((f) => f.endsWith('.svg')).length === names.size;

if (!outlinedIsComplete) {
  rmSync(workDir, { recursive: true, force: true });
  mkdirSync(svgDir, { recursive: true });
  mkdirSync(outlinedDir, { recursive: true });
  for (const [slug, exportName] of names) {
    writeFileSync(join(svgDir, `${slug}.svg`), renderSvg(icons[exportName]));
  }
  console.log(`Exported ${names.size} stroke SVGs.`);
}

// 2. Outline strokes to fills with picosvg (icon fonts cannot render strokes).
if (!existsSync(join(venvDir, 'bin/python'))) {
  execFileSync('python3', ['-m', 'venv', venvDir], { stdio: 'inherit' });
  execFileSync(join(venvDir, 'bin/pip'), ['install', '--quiet', 'picosvg'], { stdio: 'inherit' });
}

const outlineScript = `
import sys, pathlib
from picosvg.svg import SVG

src, dst = pathlib.Path(sys.argv[1]), pathlib.Path(sys.argv[2])
failed = []
for svg_file in sorted(src.glob('*.svg')):
    try:
        pico = SVG.fromstring(svg_file.read_text()).topicosvg()
        (dst / svg_file.name).write_text(pico.tostring())
    except Exception as exc:
        failed.append(f'{svg_file.stem}: {exc}')
print(f'outlined {len(list(dst.glob("*.svg")))} icons, {len(failed)} failed')
for line in failed:
    print('  FAILED', line)
`;
if (!outlinedIsComplete) {
  writeFileSync(join(workDir, 'outline.py'), outlineScript);
  execFileSync(join(venvDir, 'bin/python'), [join(workDir, 'outline.py'), svgDir, outlinedDir], {
    stdio: 'inherit',
  });
}

// 3. Compile the woff2 font + css. Codepoints are assigned explicitly inside
// the BMP Private Use Area (U+E001–U+F8FF, 6400 slots): fantasticon's default
// numbering overflows past U+FFFF for this many icons, and the generated
// cmap (format 4) silently drops supplementary-plane codepoints.
const sortedSlugs = [...names.keys()].sort();
const FIRST_CODEPOINT = 0xe001;
if (FIRST_CODEPOINT + sortedSlugs.length - 1 > 0xf8ff) {
  throw new Error(`${sortedSlugs.length} icons no longer fit into the BMP Private Use Area`);
}
const codepoints = {};
sortedSlugs.forEach((slug, index) => {
  codepoints[slug] = FIRST_CODEPOINT + index;
});

const { generateFonts } = await import('fantasticon');
mkdirSync(targetDir, { recursive: true });
await generateFonts({
  inputDir: outlinedDir,
  outputDir: targetDir,
  name: 'hugeicons',
  prefix: 'hgi',
  selector: '.hgi',
  fontTypes: ['woff2'],
  assetTypes: ['css'],
  normalize: true,
  fontHeight: 1000,
  codepoints,
});

// 4. Provenance: MIT license text plus a note about how the font was built.
const version = JSON.parse(
  readFileSync(join(root, 'node_modules/@hugeicons/core-free-icons/package.json'), 'utf8')
).version;
writeFileSync(
  join(targetDir, 'LICENSE-MIT.txt'),
  `This webfont was generated by the Desiderio build (Build/Scripts/build-hugeicons-font.mjs)
from the MIT-licensed SVG icon data in @hugeicons/core-free-icons v${version}
(https://www.npmjs.com/package/@hugeicons/core-free-icons).

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
