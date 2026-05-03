..  include:: /Includes.rst.txt

=========
Changelog
=========

The authoritative release notes live in :file:`CHANGELOG.md` at the
repository root and follow `Keep a Changelog
<https://keepachangelog.com/>`__. The current release is **2.1.0** —
*v14.3 LTS only, six-skill cleanup loop applied*.

Highlights:

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
