/*
 * Lightweight TYPO3 grammars appended to the prism-lite bundle by
 * Build/Scripts/build-prism-lite.mjs (not loaded standalone).
 *
 * - fluid: HTML plus {…} inline expressions and <f:…>/<d:…> ViewHelper tags
 *   (markup grammar already tokenises namespaced tags).
 * - typoscript / tsconfig: comments (#, // and block), conditions [ … ],
 *   constants {$…}, lhs = rhs assignments, =< references and < copies.
 */
(function (Prism) {
  if (!Prism || !Prism.languages) {
    return;
  }

  if (Prism.languages.markup) {
    Prism.languages.fluid = Prism.languages.extend('markup', {});
    Prism.languages.insertBefore('fluid', 'tag', {
      'fluid-expression': {
        // {data.header}, {item -> f:render.text(field: 'label')}, {f:if(...)}
        pattern: /\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/,
        greedy: true,
        alias: 'function',
        inside: {
          'string': {
            pattern: /(["'])(?:\\.|(?!\1)[^\\])*\1/,
            greedy: true
          },
          'function': /\b[a-z][\w]*(?:\.[\w]+)*(?=\()/i,
          'operator': /->|:|,/,
          'punctuation': /[{}()]/
        }
      }
    });
  }

  Prism.languages.typoscript = {
    'comment': [
      // Block comments /* … */
      /\/\*[\s\S]*?(?:\*\/|$)/,
      // Line comments starting with # or //
      {
        pattern: /(^|\s)(?:#|\/\/).*/m,
        lookbehind: true
      }
    ],
    // [frontend.user.isLoggedIn], [GLOBAL], [END]
    'condition': {
      pattern: /^[ \t]*\[.*\][ \t]*$/m,
      alias: 'keyword'
    },
    // {$styles.content.textmedia.maxW} constants
    'constant': {
      pattern: /\{\$[^}]+\}/,
      alias: 'variable'
    },
    'string': {
      pattern: /(["'])(?:\\.|(?!\1)[^\\\r\n])*\1/,
      greedy: true
    },
    // Left-hand side of assignments, copies and references
    'property': {
      pattern: /^[ \t]*[\w\-.\\]+(?=[ \t]*(?:=<|:=|[=<>{]))/m,
      alias: 'attr-name'
    },
    'number': /\b\d+\b/,
    // =< reference, := modifier, = assignment, < copy, > unset
    'operator': /=<|:=|[=<>]/,
    'punctuation': /[{}(),.]/
  };

  Prism.languages.tsconfig = Prism.languages.typoscript;
})(window.Prism);
