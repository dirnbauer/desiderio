# shadcn/ui Update Strategy

Desiderio uses shadcn/ui as a design source for TYPO3 Fluid components and Content Blocks. React components from the registry are not shipped in the frontend.

## Preset Updates

1. Commit the current TYPO3 state.
2. Change `desiderio.shadcn.preset` in Site Settings when the preset is already supported by committed CSS.
3. For a new preset, generate a scratch shadcn project, for example `npx shadcn@latest init --template vite --preset <id> --cwd /tmp/shadcn-presets --yes`.
4. Port the generated `:root`, `.dark`, font imports, radius, chart, and sidebar tokens into `Resources/Public/Css/shadcn-theme.css`.
5. Run `npm run build:css`.
6. Run PHPUnit, PHPStan, and `git diff --check`.

## Component Updates

Use `npx shadcn@latest view <component>` or a temporary scratch app to inspect upstream class recipes. Port the class strings, slots, `data-state`, and ARIA behavior into Fluid components under `Resources/Private/Components`.

Keep Content Block `config.yaml`, field identifiers, fixtures, and template filenames stable unless the content model is intentionally changing.

## Visual QA

Use the Desiderio styleguide page as the all-content-elements overview. Check both light and dark mode, and verify that cards, buttons, forms, tabs, accordions, tables, and charts use shadcn tokens instead of one-off colors.
