# Contributing to Desiderio

Desiderio targets **TYPO3 14.3.6+ and PHP 8.4–8.5**. Use the
[DDEV setup](README.md#development) for a reproducible local environment.

## Workflow

1. Fork or create a feature branch from `main`.
2. **One-time setup**: enable the repo's git hooks so the pre-commit
   pipeline catches stale Tailwind bundles before they reach CI:

   ```bash
   Build/Scripts/setup-hooks.sh
   ```

   That sets `core.hooksPath = Build/Hooks`. When staged rendering code or
   build dependencies change, the hook checks the Tailwind rebuild and
   confirms that the resulting CSS is staged.
3. Run the full local check before pushing:

   ```bash
   ddev exec Build/Scripts/runTests.sh
   ```

   That runs PHPStan at `level: max`, the unit tests (including the strict
   content element audit), SQLite-backed functional tests, dependency checks,
   and Tailwind bundle verification. CI covers PHP 8.4 and 8.5.
4. For a focused Content Block check while editing, run:

   ```bash
   ddev exec Build/Scripts/runTests.sh -s audit
   ```

   Strict categories such as `template_undeclared_field`,
   `hardcoded_inline_style`, and `hardcoded_color` must stay at zero.
   This check already runs in the unit suite; it need not be repeated after
   the full gate passes.
5. If you edited any Fluid template, partial, layout, or component, the
   compiled Tailwind bundle must travel with the change:

   ```bash
   ddev exec npm run build:css
   git add Resources/Public/Css/desiderio-tailwind.css
   ```

   The pre-commit hook checks the rebuild and staging; it does not stage
   files for you. The `tailwind-bundle` CI job rejects stale committed CSS.
6. Open a PR against `main` with the behavior change and validation results.
   Follow the [maintainability rules](Documentation/Developer/Index.rst)
   when changing seed commands, shared services, or finishers. Update the
   relevant manual page when a command or configuration contract changes.

## Coding standards

- PHP 8.4+ with `declare(strict_types=1);` on every PHP file.
- Constructor DI for services — no `GeneralUtility::makeInstance()` for
  Symfony-injectable classes.
- Fluid 5 strict-typed `<f:argument>` on every component.
- Tailwind / shadcn tokens only — no `hsl()`, `rgb()`, `#hex` outside
  the icon `var(--token, fallback)` contract.
- PSR-12 / PSR-4 layout, one class per file, namespace matches the
  on-disk path.
- Content Block top-level `Collection` fields must use `prefixField: true`
  when the root block keeps `prefixFields: false`. Do not share collection
  child tables automatically by generic identifiers such as `items`; reuse a
  table only when the child rows are deliberately the same model and the TCA
  matching rules stay unambiguous.
- Sharing a child table means `foreign_table:` pointing at a Record Type in
  `ContentBlocks/RecordTypes/`, plus BOTH `shareAcrossTables: true` and
  `shareAcrossFields: true` on every sharer — omit one and each sharer's
  children appear in the others, silently. The Record Type must set
  `prefixFields: false` so its columns keep their plain names, and a sharing
  Collection must not keep its own `fields:` (they are inert). The audit gates
  all of this; the [collection guide](Documentation/Developer/CollectionTableConsolidation.rst)
  explains the shared models and upgrade procedure.
- Content Block image fields render through `<f:image>` or `f:uri.image()`.
  Pass custom `data-*` attributes through structured Fluid arguments such as
  `data="{d-gallery-main: 'true'}"`; do not hand-write literal `<img>` tags for
  FAL `FileReference` objects.

## Translation files

- TYPO3 v14 XLIFF 2.0 (`<unit>` + `<segment>`), 2-space indent.
- English label files use `srcLang="en"` only.
- Translated files use `srcLang="en" trgLang="<code>"` and
  `<segment state="final">` segments.
- Maintain the XLIFF 2.0 files directly. The completed source conversion
  is enforced by the structure tests; no conversion script is required.

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
- The relevant failing check or error output, if available.

## License

By contributing, you agree your work is licensed under
[GPL-2.0-or-later](LICENSE) on the same terms as the rest of the
project.
