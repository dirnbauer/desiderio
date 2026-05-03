# Vendor patches

Patches in this directory are applied to `vendor/` packages by
[`cweagans/composer-patches`](https://github.com/cweagans/composer-patches)
on every `composer install` / `composer update`. Mappings live in
[`composer.json`](../composer.json) under `extra.patches`.

## Current patches

| File | Package | Upstream |
|------|---------|----------|
| `content-blocks-add-foreign-field-column.patch` | `friendsoftypo3/content-blocks` (2.3.x) | See [UPSTREAM_ISSUE.md](UPSTREAM_ISSUE.md) — **TODO: paste GitHub issue/PR link here once filed**, then update the description in `composer.json` |

## Workflow when a patch lands upstream

1. Bump the constraint in `composer.json` to a release that contains the fix.
2. Delete the `.patch` file from this directory.
3. Remove the corresponding entry from `extra.patches` in `composer.json`.
4. Run `composer update friendsoftypo3/content-blocks` to verify nothing else relied on the patch.

## Adding a new patch

1. Make the fix in the relevant `vendor/` file (only to generate the diff — never commit vendor changes).
2. `diff -u vendor/<pkg>/<file>.orig vendor/<pkg>/<file> > patches/<short-name>.patch`, then revert the vendor file.
3. Rewrite the `--- /+++` headers as `a/<path-inside-package>` and `b/<path-inside-package>`.
4. Sanity-check with `cd vendor/<pkg> && git apply --check ../../patches/<short-name>.patch` (or `patch --dry-run -p1 < …`).
5. Add an entry under `extra.patches.<package>` in `composer.json` and file an upstream issue documenting the fix.
