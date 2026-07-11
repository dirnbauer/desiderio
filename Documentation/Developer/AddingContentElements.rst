..  include:: /Includes.rst.txt

..  _developer-adding-content-elements:

===========================================
Adding content elements from a shadcn block
===========================================

Desiderio ships 244 Desiderio Content Blocks, but the shadcn/ui ecosystem keeps
producing new ones — marquees, bento grids, animated terminals, globes. This
page is the complete, screenshot-by-screenshot manual for grafting one of those
components onto a Desiderio site as a first-class content element.

The graft is done with the companion extension
`Innesto <https://github.com/dirnbauer/innesto>`__ (``dirnbauer/innesto``). Its
site set depends on the Desiderio set, so grafted elements reuse the Desiderio
Fluid atoms (``d:`` components) and the shadcn semantic tokens, and they repaint
with every preset and dark mode automatically.

This walkthrough follows the exact graft that produced the shipped
``innesto/terminal`` element — the
`Magic UI terminal <https://magicui.design/docs/components/terminal>`__: an
animated terminal that types out commands and output line by line.

..  figure:: /Images/TerminalFrontendLive.png
    :alt: The finished Terminal element rendering on the frontend
    :zoom: lightbox
    :class: with-border with-shadow

    The finished graft on the frontend — themed entirely through the active
    preset's card, border, and primary tokens.

..  contents:: On this page
    :local:
    :depth: 2

..  _developer-adding-how-a-graft-works:

How a graft works
=================

Every registry on `registry.directory <https://registry.directory/>`__ —
shadcn/ui, Magic UI, Origin UI, Aceternity, … — serves its components as JSON
following the same ``registry-item`` schema: React/TSX sources, plain CSS, and
theme variables. Innesto splits the conversion into two phases:

#.  **The mechanical phase** (``innesto:add``, fully automatic): fetch the JSON,
    convert the CSS, scaffold a complete Content Blocks element folder, register
    the element in the site set, and write a tailored finishing prompt.
#.  **The finishing phase** (you, or an AI agent via ``--ai``): translate the
    React markup to Fluid, model the component props as editor fields, and port
    the styles onto the Desiderio tokens. React components are programs, not
    documents — this phase cannot be mechanical, which is why it is separated.

..  _developer-adding-prerequisites:

Prerequisites
=============

*   A Composer-based TYPO3 v14.3 installation with Desiderio set up.
*   Innesto installed and added to the site's ``config.yaml``
    (see the `Innesto README <https://github.com/dirnbauer/innesto>`__).
*   CLI access (``vendor/bin/typo3``); under ddev, prefix commands with
    ``ddev``.
*   Optional, for the AI finishing pass: the
    `claude CLI <https://claude.com/claude-code>`__.

..  _developer-adding-step1:

Step 1 — Pick a component
========================

Browse `registry.directory <https://registry.directory/>`__ and note the item
name. ``innesto:add`` accepts a registry shorthand or a full item-JSON URL:

..  list-table::
    :header-rows: 1
    :widths: 40 60

    *   - Form
        - Example
    *   - Shorthand ``<registry>/<item>`` (known registries)
        - ``magicui/terminal``, ``shadcn/button``, ``shadcnblocks/case-studies2``
    *   - Full item-JSON URL (everything else)
        - ``https://magicui.design/r/globe.json``

Good graft candidates are **documents with a little motion**: terminals,
marquees, logo clouds, bento grids, animated lists, badges. Components that are
mostly *state machines* — command palettes, comboboxes, drag-and-drop, anything
built on Radix with heavy keyboard interaction — do not graft well; pick
presentational components.

..  _developer-adding-step2:

Step 2 — Run ``innesto:add``
===========================

..  code-block:: shell
    :caption: Scaffold the element

    ddev typo3 innesto:add magicui/terminal

..  code-block:: text
    :caption: Output

    Fetched "terminal" (registry:ui)
    --------------------------------

     * terminal/sources/terminal.tsx
     * terminal/assets/frontend.css
     * terminal/assets/icon.svg
     * terminal/config.yaml
     * terminal/language/labels.xlf
     * terminal/templates/frontend.html

     [OK] Element "innesto/terminal" scaffolded.

     Registered "innesto/terminal" in the Innesto site set.
     AI finishing prompt written to terminal/AI_PROMPT.md

The command creates a complete element folder under
``ContentBlocks/ContentElements/terminal/`` and — crucially — appends the new
block to the site set:

..  code-block:: yaml
    :caption: Configuration/Sets/Innesto/config.yaml

    optionalDependencies:
      - innesto/terminal

Content Blocks exposes every block as a virtual site set. Desiderio references
all of its elements that way, which switches the New Content Element wizard into
allow-list mode per site: any block **not** listed in a site set renders fine
but never appears in the wizard. This registration is the number-one
"why can't I add my element?" trap, so ``innesto:add`` does it for you.

..  _developer-adding-step3:

Step 3 — The finishing pass
==========================

The scaffold leaves three files to finish. You can run the finishing pass with
an agent (``innesto:add magicui/terminal --ai``) or do it by hand. Here is what
the Terminal graft needed.

Model the props as fields
-------------------------

The upstream component renders a window with traffic-light dots and a code area
of sequentially-animated lines (``TypingAnimation`` for commands, ``AnimatedSpan``
for output). That maps onto a Collection of lines plus a few options:

..  code-block:: yaml
    :caption: ContentBlocks/ContentElements/terminal/config.yaml

    fields:
      -
        identifier: header
        useExistingField: true
        label: 'Heading'
      -
        identifier: terminal_title
        type: Text
        label: 'Window title'
      -
        identifier: terminal_lines
        type: Collection
        table: innesto_terminal_lines   # ALWAYS give a Collection an explicit, prefixed table
        prefixField: true
        label: 'Lines'
        minItems: 2
        fields:
          -
            identifier: kind            # the line's role → a Select
            type: Select
            renderType: selectSingle
            items:
              - { label: 'Command (typed, with prompt)', value: command }
              - { label: 'Output', value: output }
              - { label: 'Success', value: success }
              - { label: 'Comment / muted', value: muted }
            default: command
          -
            identifier: text            # NEVER name a child field "label" — reserved!
            type: Textarea
            rows: 1
            required: true
      -
        identifier: speed               # reuse the shared enum already used by marquee/orbiting
        type: Select
        renderType: selectSingle
        items:
          - { label: 'Slow', value: slow }
          - { label: 'Normal', value: normal }
          - { label: 'Fast', value: fast }
        default: normal
      -
        identifier: animate             # boolean → checkbox toggle
        type: Checkbox
        renderType: checkboxToggle
        default: 1

Conventions: Select for enums, ``checkboxToggle`` for booleans, ``Textarea``
``rows: 1`` for short text, Collection for repeatable children. Give **every**
Collection an explicit, ``innesto_``-prefixed ``table:`` — without it, generic
identifiers like ``items`` silently share one table across elements. And never
name a child field ``label``: that identifier is reserved by Content Blocks and
breaks the generated table — use ``title`` (or here, ``text``).

..  figure:: /Images/TerminalContentBlockConfig.png
    :alt: Complete Terminal Content Blocks YAML with fields and collection table
    :zoom: lightbox
    :class: with-border with-shadow

    The complete Content Blocks configuration. The explicit
    ``innesto_terminal_lines`` table keeps the repeatable lines isolated from
    collections owned by other elements.

Translate the markup to Fluid
-----------------------------

Keep the generated ``d:layout.section`` / ``d:layout.container`` wrapper and the
``f:asset.css`` include. The React children become an ``f:for`` over the
Collection; the "wait for the previous line" sequencing becomes a per-line
``--index`` custom property that the CSS staggers off (the same technique the
``orbiting-circles`` graft uses for its orbit):

..  code-block:: html
    :caption: templates/frontend.html (excerpt)

    <div class="innesto-terminal innesto-terminal--speed-{data.speed -> f:or(alternative: 'normal')}{f:if(condition: data.animate, else: ' innesto-terminal--static')}"
         role="group" aria-label="{data.terminal_title -> f:or(alternative: 'Terminal')}"
         style="--total: {f:count(subject: data.terminal_lines)};">
        <div class="innesto-terminal__bar">
            <span class="innesto-terminal__dots" aria-hidden="true"><span class="innesto-terminal__dot"></span><span class="innesto-terminal__dot"></span><span class="innesto-terminal__dot"></span></span>
            <f:if condition="{data.terminal_title}"><span class="innesto-terminal__title">{data.terminal_title}</span></f:if>
        </div>
        <div class="innesto-terminal__body"><div class="innesto-terminal__code"><f:for each="{data.terminal_lines}" as="entry" iteration="iterator"><span class="innesto-terminal__line innesto-terminal__line--{entry.kind -> f:or(alternative: 'output')}" style="--index: {iterator.index};"><f:if condition="{entry.kind} == 'command'"><span class="innesto-terminal__prompt" aria-hidden="true">$ </span></f:if><span class="innesto-terminal__text">{entry.text}</span></span></f:for></div></div>
    </div>

State-free motion (the typewriter, the staggered reveal) becomes CSS. Real state
(toggles, tabs) would go to Alpine.js ``x-data`` — never an inline ``<script>``.
The ``animate`` toggle simply adds an ``--static`` modifier class.

..  figure:: /Images/TerminalFluidTemplate.png
    :alt: Complete Fluid template rendering the Terminal Content Block fields
    :zoom: lightbox
    :class: with-border with-shadow

    The complete Fluid template maps editor fields to semantic markup and the
    shared Desiderio layout components. The source view is soft-wrapped in the
    screenshot so the long collection-rendering line remains readable.

Port the styles onto tokens
---------------------------

Use **only** the semantic tokens — never a raw color — and the element follows
every preset, including dark mode. Even the macOS window dots use
``color-mix`` over ``--muted-foreground`` so they stay theme-aware:

..  code-block:: css
    :caption: assets/frontend.css (the typewriter)

    .innesto-terminal:not(.innesto-terminal--static) .innesto-terminal__line {
        opacity: 0;
        animation: innesto-terminal-reveal 0.32s ease forwards;
        animation-delay: calc(var(--index, 0) * var(--innesto-terminal-step));
    }
    /* Command lines type out: clip the inner text left-to-right in steps. */
    .innesto-terminal:not(.innesto-terminal--static) .innesto-terminal__line--command .innesto-terminal__text {
        clip-path: inset(0 100% 0 0);
        animation: innesto-terminal-type var(--innesto-terminal-type) steps(24, jump-none) forwards;
        animation-delay: calc(var(--index, 0) * var(--innesto-terminal-step));
    }
    @keyframes innesto-terminal-type { from { clip-path: inset(0 100% 0 0); } to { clip-path: inset(0 0 0 0); } }

    @media (prefers-reduced-motion: reduce) {
        .innesto-terminal .innesto-terminal__line { opacity: 1; animation: none; }
        .innesto-terminal .innesto-terminal__line--command .innesto-terminal__text { clip-path: none; animation: none; }
    }

..  tip::

    Use ``steps(N, jump-none)``, not ``steps(N, end)``, for a clip-path
    typewriter. With ``end`` the last keyframe is never held, so a long line
    stays one character short (``…magicui/termin`` instead of
    ``…magicui/terminal``). ``jump-none`` includes both endpoints and reveals
    the whole line.

Prefix every class with ``.innesto-<key>`` and honor
``prefers-reduced-motion`` for anything that moves.

Add a backend preview
---------------------

A ``templates/backend-preview.fluid.html`` modeled on the Desiderio previews
(``f:layout name="Preview"``, the ``d-ce-preview`` card,
``EXT:desiderio/Resources/Public/Css/content-preview.css``) gives editors a
real preview card in the page module.

..  figure:: /Images/TerminalBackendPreview.png
    :alt: TYPO3 page module showing the Terminal backend preview
    :zoom: lightbox
    :class: with-border with-shadow

    The backend preview identifies ``innesto/terminal``, its record UID, the
    heading, line count, speed, and window title without opening the form.

..  _developer-adding-step4:

Step 4 — Activate and seed
=========================

..  code-block:: shell
    :caption: Activate the element

    ddev typo3 cache:flush       # BEFORE setup — a stale Content Blocks cache makes setup fail
    ddev typo3 extension:setup   # creates the innesto_terminal_lines collection table
    ddev typo3 cache:flush

..  warning::

    For a **new** element, flush caches *before* ``extension:setup``. A stale
    Content Blocks cache otherwise makes setup die with a "zero columns" error
    on the new collection table.

Seed a demo record (values come from the element's ``fixture.json`` when
present):

..  code-block:: shell

    ddev typo3 innesto:seed <page-uid> -e terminal

..  _developer-adding-step5:

Step 5 — Use it in the backend
=============================

The element appears in the **New Content Element wizard** with the icon, title,
and description taken from the registry item:

..  figure:: /Images/terminal-wizard.png
    :alt: The New Content Element wizard, filtered to "terminal"
    :zoom: lightbox
    :class: with-border with-shadow

    The grafted Terminal in the New Content Element wizard.

The edit form shows the fields exactly as modeled — heading, window title, and
the Lines collection:

..  figure:: /Images/TerminalBackendEditForm.png
    :alt: Terminal edit form with heading, window title, and eight line records
    :zoom: lightbox
    :class: with-border with-shadow

    The Terminal edit form: resolved type, heading, window title, and the Lines
    collection.

..  _developer-adding-step6:

Step 6 — Check the frontend
==========================

Open the page. The terminal types its commands, reveals output line by line,
shows a blinking cursor, and uses the active preset's tokens — switch the
Desiderio preset in the site settings and the graft repaints with it
(see the :ref:`frontend screenshot above <developer-adding-content-elements>`).

..  _developer-adding-what-converts:

What converts automatically — and what doesn't
=============================================

..  list-table::
    :header-rows: 1
    :widths: 45 55

    *   - Registry item part
        - Conversion
    *   - ``cssVars`` (theme/light/dark tokens)
        - ✅ automatic — Desiderio uses the same shadcn variable names, 1:1
    *   - ``css`` / keyframes
        - ✅ automatic — serialized into ``assets/frontend.css``
    *   - Tailwind ``@theme`` animation entries
        - ✅ automatic — custom property + matching utility class
    *   - Site-set registration
        - ✅ automatic (default target)
    *   - React/TSX markup
        - ⚠️ finishing pass — structural markup translates quickly; hooks/state
          need Alpine.js or CSS
    *   - Component props
        - ⚠️ finishing pass — modeled as Content Blocks fields
    *   - npm ``dependencies`` / ``registryDependencies``
        - ❌ not fetched — listed as a warning; resolve manually

The Terminal item is a good illustration: it ships **no** ``cssVars`` or
keyframes because its animation is driven by ``motion/react`` in the browser.
The finishing pass re-created that motion in pure CSS, so the element ships zero
JavaScript.

..  _developer-adding-troubleshooting:

Troubleshooting
==============

The element does not appear in the wizard
    The block is not a site-set dependency, or caches are stale. Check
    ``Configuration/Sets/Innesto/config.yaml`` lists ``innesto/<key>`` under
    ``optionalDependencies``, then ``cache:flush``.

``SQL error`` / fields missing after editing ``config.yaml``
    Run ``extension:setup`` to create new columns/tables, then ``cache:flush``.

A Collection child field named ``label`` breaks the backend
    ``label`` is reserved by Content Blocks — rename it (for example ``text``).

For the command reference, more worked examples (``marquee``, ``case-studies``),
and the full troubleshooting list, see the
`Innesto manual <https://github.com/dirnbauer/innesto/blob/main/Documentation/AddingContentElements.md>`__.
