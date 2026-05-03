..  include:: /Includes.rst.txt

================
For developers
================

Architecture
============

Three layers. Each composes the layer below and never reaches around
it::

    ┌────────────────────────────────────────────────────────────┐
    │ Layer 3 — THEME                                            │
    │ Page templates · Backend layouts · Header/Footer · Presets │
    └────────────────────────────────────────────────────────────┘
                              ▲ renders via PAGEVIEW
    ┌────────────────────────────────────────────────────────────┐
    │ Layer 2 — CONTENT ELEMENTS (Content Blocks)                │
    │ 255 editor-facing elements in 10 wizard groups             │
    └────────────────────────────────────────────────────────────┘
                              ▲ composes via <d:…>
    ┌────────────────────────────────────────────────────────────┐
    │ Layer 1 — COMPONENTS (Fluid 5)                             │
    │ 16 atoms · 17 molecules · 4 layouts (typed <f:argument>)   │
    └────────────────────────────────────────────────────────────┘

Quality bar
===========

Every PR is gated by the GitHub Actions workflow at
``.github/workflows/ci.yml``:

*   **PHPStan** runs at ``level: max`` with
    :doc:`saschaegerer/phpstan-typo3 <t3coreapi:Index>`,
    ``phpstan-strict-rules``, and ``phpstan-phpunit``. The legacy
    seed-command type drift is captured in
    ``phpstan-baseline.neon`` as a documented ratchet target.
*   **PHPUnit ^11.5** across PHP 8.3 + 8.4 against TYPO3 ^14.3.
*   **Content element audit** (``scripts/audit-content-elements.php``)
    gating ``template_undeclared_field``, ``hardcoded_inline_style``,
    ``hardcoded_color``, and the other strict categories at zero.
*   ``composer audit`` with ``abandoned: fail`` and
    ``composer validate``.

Cleanup-loop reports
====================

``Documentation/Reports/`` contains the latest audit results from the
six agentic skills the project runs at every release:

*   ``typo3-conformance.md``
*   ``typo3-security.md``
*   ``typo3-workspaces.md``
*   ``typo3-testing.md``
*   ``typo3-docs.md``
*   ``security-audit.md``

Use these as the entry point when changing TCA, Fluid templates, or the
seed command.

Local development
=================

..  code-block:: shell

    composer install
    npm install
    npm run build:css

    # All checks
    Build/Scripts/runTests.sh

    # À la carte
    Build/Scripts/runTests.sh phpstan
    Build/Scripts/runTests.sh phpunit
    Build/Scripts/runTests.sh audit
