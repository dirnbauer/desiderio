# Contributing to Desiderio

Thanks for considering a contribution. Desiderio targets **TYPO3 v14.3
LTS only** — no v13 fallback — so every PR must keep the LTS commitment
intact.

## Workflow

1. Fork or create a feature branch from `main`.
2. **One-time setup**: enable the repo's git hooks so the pre-commit
   pipeline catches stale Tailwind bundles before they reach CI:

   ```bash
   Build/Scripts/setup-hooks.sh
   ```

   That sets `core.hooksPath = Build/Hooks` and rebuilds `desiderio-tailwind.css`
   automatically when a commit touches templates, components, or content
   blocks. If the bundle would change, the commit is rejected with a
   single-line fix instruction.
3. Run the full local check before pushing:

   ```bash
   Build/Scripts/runTests.sh
   ```

   That runs PHPStan at `level: max`, the 62 unit tests, the deep
   content element audit, and verifies the Tailwind bundle is in sync.
   CI re-runs the same matrix across PHP 8.3 + 8.4.
4. If you touched a Content Block, also run:

   ```bash
   php scripts/audit-content-elements.php > /tmp/audit.json
   ```

   The strict categories (`template_undeclared_field`,
   `hardcoded_inline_style`, `hardcoded_color`, etc.) must stay at zero.
5. If you edited any Fluid template, partial, layout, or component, the
   compiled Tailwind bundle must travel with the change:

   ```bash
   npm run build:css
   git add Resources/Public/Css/desiderio-tailwind.css
   ```

   The pre-commit hook does this automatically when hooks are enabled.
   The `tailwind-bundle` CI job rejects PRs where the committed bundle
   is out of date.
6. Open a PR against `main`. Reference any
   `Documentation/Reports/*.md` finding your change addresses.

## Coding standards

- PHP 8.3+ with `declare(strict_types=1);` on every PHP file (except
  `ext_emconf.php`).
- Constructor DI for services — no `GeneralUtility::makeInstance()` for
  Symfony-injectable classes.
- Fluid 5 strict-typed `<f:argument>` on every component.
- Tailwind / shadcn tokens only — no `hsl()`, `rgb()`, `#hex` outside
  the icon `var(--token, fallback)` contract.
- PSR-12 / PSR-4 layout, one class per file, namespace matches the
  on-disk path.

## Translation files

- TYPO3 v14 XLIFF 2.0 (`<unit>` + `<segment>`), 2-space indent.
- English label files use `srcLang="en"` only.
- Translated files use `srcLang="en" trgLang="<code>"` and
  `<segment state="final">` segments.
- The `Build/Scripts/convert-xliff-1-2-to-2-0.php` helper rewrites
  legacy 1.2 documents in place; idempotent on already-2.0 files.

## Commit style

- Imperative subject line, ≤ 72 characters.
- Body paragraphs explain *why*, not *what*. The diff already shows
  *what*.
- Co-author footer is optional but encouraged when pairing with an
  agent.

## Reporting issues

Open an issue with:

- Affected TYPO3 version (must be 14.3.x — older is out of scope).
- Affected PHP version.
- Reproduction steps or a failing test case.
- A link to the relevant `Documentation/Reports/*.md` finding if
  applicable.

## License

By contributing, you agree your work is licensed under
[GPL-2.0-or-later](LICENSE) on the same terms as the rest of the
project.
