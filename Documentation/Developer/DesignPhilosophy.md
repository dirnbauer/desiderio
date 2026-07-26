# Design philosophy

How 244 content elements stay one design system instead of 244 opinions.
Everything in this document is enforced — by tokens, by the static audit
(`scripts/audit-content-elements.php`, gated in
`Tests/Unit/ContentElementAuditTest`), by the analytical contrast audit
(`Build/Scripts/audit-theme-contrast.php`) or by the browser harness
(`Build/VisualQa/`). A convention that nothing checks is a hope, not a
philosophy; each rule below names its check.

The conventions were **measured out of the catalog, not invented**: the
anatomy survey (`Build/VisualQa/anatomy.mjs`) renders all 288 seeded elements
and reports the distribution of paddings, alignments, heading sizes and
reading widths. The dominant behaviour became the rule; the outliers became
the fix list. Re-run it after any batch of element work — its finding count
should be zero.

## 1. Color is always a token

An element never names a colour. It names a **role** — `--background`,
`--card`, `--muted-foreground`, `--primary`, `--destructive`, `--chart-1..5` —
and the active shadcn preset decides what that role looks like, in light and
dark. This is the contract that makes 15 presets × 2 schemes possible at all.

- Enforced: `hardcoded_color` (audit, zero-tolerance) forbids raw hex/oklch/
  rgb/hsl in element CSS and templates.
- Proven: `audit-theme-contrast.php` resolves 420 role pairs (including
  `color-mix`) across every preset × scheme against WCAG 2.2 AA in under a
  second. The browser sweep covers what tokens cannot see: composed colours
  and text over images.
- Corollary: status text on a tint of its own colour uses the solved
  `--d-success-text` / `--d-warning-text` / `--d-danger-text`, never the raw
  status hue (raw measures 3.8–4.1:1 on its own wash).
- Dark mode is the `.dark` **class**, toggled by the user's stored preference.
  A `prefers-color-scheme` media query in an element would ignore the toggle —
  forbidden (`css_hardcoded_dark_selector`).

## 2. Type comes from the scale

Four sizes tell you almost everything about a page's discipline. The catalog
converged on the Tailwind ramp (`--d-text-xs` … `--d-text-6xl`); the survey
shows headings settling at 30px for sections and 48px for heroes — one h-level
per slot.

The part that was missing until 3.1: **leading, weight and tracking**. The
catalog used 16 distinct line-heights, and heading-scale leadings (1.0–1.6)
overlapped body-scale leadings (1.0–1.7) almost completely — the same size
breathed differently in every element. Now:

| token | value | role |
|---|---|---|
| `--d-leading-tight` | 1.15 | display, hero and section headings (xl+) |
| `--d-leading-snug` | 1.3 | card titles, small headings, table heads |
| `--d-leading-normal` | 1.5 | UI text, dense lists, captions |
| `--d-leading-relaxed` | 1.65 | body copy, anything paragraph-length |
| `--d-weight-light/normal/medium/semibold/bold` | 300–700 | the working range |
| `--d-weight-display` | 800 | **numerals that carry the message only** — prices, metrics, counters. Named separately so it cannot drift onto ordinary headings |
| `--d-tracking-wide` | 0.05em | the uppercase-eyebrow convention |
| `--d-tracking-tight` | −0.015em | display sizes, so large type does not look loose |

`line-height: 0` and `1` stay literal — they are layout resets for icons and
SVG, not typography.

- Enforced: `css_untokenised_typography` (audit, zero-tolerance). A literal
  line-height, font-weight or letter-spacing fails CI.
- Fonts: never a literal `font-family` — presets swap between four families
  through `--d-font-sans/heading/mono` (`css_raw_font_family`, zero-tolerance).
- Inside SVG, font sizes are **user units scaled by the viewBox**. Matching
  another chart's rendered type means sharing its viewBox geometry, not its
  rem value (see §5).

## 3. Space is a rhythm, not a guess

The survey found the catalog's real vertical rhythm and the outliers hiding in
it:

| band | value | who |
|---|---|---|
| section | 96px desktop (`py-16 md:py-24`, the Section component default) | 212 of 288 elements |
| compact | 48px | self-contained cards and mid-weight elements |
| utility | 24px | bars, breadcrumbs, alerts |
| flush | 0 | elements that manage their own chrome |

Heroes were the bug: all eight padded at 64–80px — **the biggest moment on the
page breathed less than an ordinary section**. They now share
`--d-hero-y: clamp(4rem, 6vw + 2rem, 6rem)`, which lands on the section
rhythm at desktop and relaxes on small screens. Hero presence is one knob,
not eight guesses.

Within an element, spacing uses the `--d-spacing-*` ramp. Gap and padding are
~96% tokenised; margins carry the remaining literals and get cleaned
opportunistically — new code should not add any.

## 4. Layout anatomy: measure, alignment, the header unit

Three conventions, all enforced by the shared zero-specificity layer
`Resources/Private/Css/desiderio/18-section-anatomy.css` (every rule wrapped
in `:where()`, so any element overrides any of it with one plain class):

**Reading measure.** Running text caps at 70ch (section intros) / 75ch (rich
text, `.ce-bodytext`, content slots, legal documents). The survey found 41
elements letting paragraphs run to 137ch at desktop; after the shared caps the
finding count is zero. Document-like elements (imprint, privacy) cap **per
text node**, so the measure holds in the paragraph's own font. The
announcement bar is deliberately exempt: a banner line is a single full-width
line by design. Elements rendering outside `.desiderio-section`
(footer-newsletter, cookie-banner) are named in the layer explicitly.

**The header is a unit.** Eyebrow, heading and subheadline share one
alignment, inherited through the intro block. A centered heading over a
start-aligned *content* block (an accordion list, a form, a video grid) is a
legitimate composition — the unit ends at the first structural block.

**Centered means centered.** 108 elements center their header, 52 start it;
both are conventions, chosen per element's job (heroes and standalone intros
center; content-heavy and list-like elements start). What is not allowed is
drift within the unit. A capped intro paragraph in a centered header carries
`margin-inline: auto` **next to the element's own `text-align: center`** —
the two declarations only make sense together, so they live together.

## 5. Charts are one family

Nine chart types render as one family because they share geometry, not
because anyone checked screenshots:

- Wide (cartesian) charts draw into the shared **640×300 viewBox** with the
  standard padding (padX 90, padY 30, bottom 44). Sharing the viewBox is what
  makes their axis type render at the same visual size.
- The plot's height settles at `--d-chart-plot-height` (23.5rem), matching the
  radial charts' 360px band.
- A chart plot caps its box with **max-width derived from the viewBox aspect**
  — never `max-height`. A max-height on an aspect-ratio box breaks the ratio,
  and the SVG's `xMidYMid meet` then strands the drawing in dead space (the
  sparkline once drew 403px wide inside a 1166px box). Rule documented at the
  token; measured dead space across the Data & Dashboards page: 0px.

## 6. Editor controls do what they say

Every control an editor sees must change the output. The core
`TYPO3/Appearance` basic put four dropdowns (Layout, Frame, Space Before/After)
into all 244 forms and none of them rendered anywhere — the definition of a
broken promise. It is replaced by `Desiderio/Appearance`:

- **Frame** is the Section surface picker — page background, muted, card,
  accent, primary, secondary — the same roles as everywhere else, with
  foreground pairing handled by the Section component and legibility on tinted
  surfaces by `07-content-frames.css`.
- **Space before/after** adds outer margin on the spacing ramp, keeping core's
  value set (`extra-small…extra-large`) so stored content stays portable.
- **Layout is gone.** Core's "Layout 1…3" has no meaning in this design
  system; a dropdown that does nothing does not get shown.
- The 17 elements that render no section (footers, floating banners, dividers)
  carry no appearance tab at all, for the same reason.
- An editor's Frame choice **overrides** a template's background default
  (only `cta` sets one), so elements may ship an opinion and editors may still
  have the last word.

Enforced twice: `appearance_field_unwired` (audit, zero-tolerance) fails any
element whose template does not pass the fields to `<d:layout.section>`, and
the structure test bans `TYPO3/Appearance` outright.

## 7. Structure rules that keep the rest honest

Long-standing zero-tolerance audit categories, unchanged by this document but
part of the same philosophy: no inline styles, Select fields have defaults,
`variant` fields must actually vary the output, every declared field is
rendered and every rendered field is declared, links resolve through
`f:uri.typolink`, text fields are inline-editable (`f:render.text`), and
collections either own a `table:` or share a Record Type with both share
flags set.

Breakpoints come from the shared set (480/640/768/1024, exact complements
allowed); one-off widths reflow alone and read as bugs
(`css_nonstandard_breakpoint`).

## Working on an element — the loop

```
1. edit ContentBlocks/ContentElements/<name>/…
2. php scripts/audit-content-elements.php           # structure + CSS hygiene, must stay clean
3. php Build/Scripts/audit-theme-contrast.php       # only if you touched theme tokens
4. npm run build:css && node Build/Scripts/build-desiderio-css.mjs
5. node Build/VisualQa/anatomy.mjs --urls …         # layout conventions, target: 0 findings
6. node Build/VisualQa/run.mjs --urls … --tier all  # 2,976-render regression sweep
```

The harness URLs come from `ddev typo3 desiderio:library:urls --site=<site> --json`.
