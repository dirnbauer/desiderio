..  include:: /Includes.rst.txt

..  _changelog:

=========
Changelog
=========

The authoritative release notes live in :file:`CHANGELOG.md` at the
repository root and follow `Keep a Changelog
<https://keepachangelog.com/>`__. The current release is **2.4.0**.

Highlights:

*   WCAG 2.2 AA page-chrome primitives, including the skip link,
    reduced-motion rules, accessible navigation labels, and
    ``role="list"`` patches for list-like Tailwind layouts.
*   ``webconsulting/desiderio-blog`` hidden site set with shadcn-only
    ``t3g/blog`` layouts, templates, and partials.
*   Fluid 5.3 typed ``<f:argument>`` declarations across News, Solr,
    Fluid Styled Content, shared Pagination, and Blog overrides.
*   New ``DesiderioNews`` page template + backend layout.
*   News magazine list and progressive **Load more** partial driven
    by ``settings.list.useLoadMore`` / ``initialCount`` /
    ``loadMoreCount``.
*   ``typo3/cms-workspaces ^14.3`` is now a hard dependency.
*   PHPStan ``level: max`` with ``saschaegerer/phpstan-typo3`` and
    ``phpstan-strict-rules``.
*   765 it/fr/es Content Block label files migrated to XLIFF 2.0.
*   GitHub Actions CI matrix (PHP 8.3 + 8.4 × TYPO3 ^14.3).

For the full notes including migration guidance, open
:file:`CHANGELOG.md`.
