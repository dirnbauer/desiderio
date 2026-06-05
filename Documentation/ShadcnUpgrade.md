# shadcn/create and Fluid synchronization

Desiderio uses shadcn/create as a design source for TYPO3 Fluid
components. It does not ship React components in the frontend.

The current default is the `b6G5977cw` shadcn/create preset with
`radix-lyra`, olive tokens, square radius, and Tabler icons. Sites can
switch presets at runtime through TYPO3 site settings.

## Supported presets

The site setting `desiderio.shadcn.preset` supports these values:

- `b0`
- `b4hb38Fyj`
- `b3IWPgRwnI`
- `b6G5977cw`
- `b27GcrRo`
- `aurora`
- `marine`
- `forest`
- `ember`
- `bloom`
- `lagoon`
- `gold`
- `midnight`
- `blossom`
- `citrus`
- `custom`

The `desiderio.shadcn.style` setting stores the source style metadata.
The `desiderio.shadcn.iconLibrary` setting controls icon rendering and
currently supports Lucide, Tabler, HugeIcons, Phosphor, and Remix Icon.

## Runtime model

TYPO3 renders the selected values onto the `<body>` element as data
attributes. CSS token blocks in `Resources/Public/Css/shadcn-theme.css`
then control light mode, dark mode, borders, radius, charts, typography,
focus rings, and surface elevation.

Content records store semantic icon keys, not library-specific SVG
names. This keeps existing content stable when a site changes the icon
library.

## Updating a shadcn preset

Use this workflow when adding or refreshing a shadcn/create preset:

1. Inspect the preset in `https://ui.shadcn.com/create`.
2. Record the preset id, source style, base colour, radius, font, chart
   tokens, and icon library.
3. Copy the light `:root` and dark `.dark` token blocks into
   `Resources/Public/Css/shadcn-theme.css`.
4. Add the preset id to
   `Configuration/Sets/Desiderio/settings.definitions.yaml`.
5. Add or update the icon-library mapping in
   `Classes/Icon/IconRegistry.php` when needed.
6. Run `npm run shadcn:sync-fluid` if shared component class contracts
   changed.
7. Run the project checks.

Do not add one-off colours or component-specific style overrides to
content element templates. If a visual rule belongs to shadcn/ui, move it
into a shared Fluid component, CSS token, or generated class partial.

## Component ownership

Registry-backed primitives are synchronized into Fluid components under
`Resources/Private/Components`. Buttons, badges, labels, inputs, selects,
textareas, tabs, accordions, cards, and form classes belong there.

Semantic TYPO3 primitives stay local when shadcn/ui does not provide a
registry contract. Examples include page templates, TYPO3 extension
partials, Blog widgets, News metadata, Solr results, and Content Block
composition.

## Media rules

Content Block image fields must render through TYPO3 Fluid image
ViewHelpers. Prefer `<f:image>` for markup and `f:uri.image()` for
processed URLs in JavaScript data attributes. Avoid literal `<img>` tags
for FAL `FileReference` objects.

## Verification

After changing presets or shared primitives, verify:

- Light and dark mode.
- Button, badge, form, card, tab, accordion, and search states.
- Blog list/detail pages.
- News list/detail pages.
- Powermail and TYPO3 Form Framework forms.
- Solr result and suggest output.
- Content Block chart, code, image, carousel, and timeline elements.
- Backend previews and workspace previews.
