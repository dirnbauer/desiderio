import { build } from 'esbuild';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

/*
 * Builds a slim highlight.js bundle used ONLY as a language *detector* for code
 * blocks that ship without a language label and without a recognizable filename
 * extension. Rendering still happens through prism-lite.js, so the languages
 * registered here mirror the Prism grammars we can actually colour. The bundle
 * is lazy-loaded by astro.js on first need (see autoDetectAndHighlight), so most
 * pages never download it.
 */
const root = dirname(dirname(dirname(fileURLToPath(import.meta.url))));

// Keep in sync with the Prism grammars in build-prism-lite.mjs and the
// HLJS_TO_PRISM map in Resources/Public/Js/astro.js.
const languages = ['javascript', 'typescript', 'css', 'php', 'yaml', 'xml', 'bash'];

const entry = [
  "import hljs from '@highlightjs/cdn-assets/es/core.min.js';",
  ...languages.map((lang, index) => `import lang_${index} from '@highlightjs/cdn-assets/es/languages/${lang}.min.js';`),
  ...languages.map((lang, index) => `hljs.registerLanguage('${lang}', lang_${index});`),
  'window.hljs = hljs;',
].join('\n');

await build({
  stdin: {
    contents: entry,
    resolveDir: root,
    sourcefile: 'hljs-lite-entry.mjs',
  },
  bundle: true,
  minify: true,
  format: 'iife',
  target: 'es2019',
  legalComments: 'none',
  banner: {
    js: '/*! highlight.js lite (detector only) for Desiderio code blocks. Built from @highlightjs/cdn-assets. */',
  },
  outfile: join(root, 'Resources', 'Public', 'Js', 'hljs-lite.js'),
});
