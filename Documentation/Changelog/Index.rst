..  include:: /Includes.rst.txt

..  _changelog:

=========
Changelog
=========

The authoritative release notes live in :file:`CHANGELOG.md` at the
repository root and follow `Keep a Changelog
<https://keepachangelog.com/>`__. The current release is **2.11.0**.

Highlights:

*   ``desiderio:library:warm`` warms the picker preview thumbnails for every
    site that shows them (per-site base), with ``--folder`` and ``--site``
    options. See :ref:`developer-element-library`.
*   The element library picker (``?elementLibrary=1``) caches its catalog in
    the ``desiderio_library`` cache instead of parsing 244 Desiderio Content Block
    ``config.yaml`` files on every open. See :ref:`developer-element-library`.
*   The ``code-block`` element gains content- and filename-aware syntax
    highlighting with a highlight.js-lite autodetect fallback.
*   ``ExtbasePluginRequestSanitizerMiddleware`` fixes Visual Editor
    rendering for News and other Extbase plugins.
*   Seed commands are thin orchestration shells; shared FAL, collection,
    and fixture logic lives in ``Classes/Seeding/``.
*   ``StyleguideFixtureResolver``, ``StarterContentBuilder``, and
    ``BlogPageTreeSeeder`` replace thousand-line command implementations.
*   Thermo-nuclear maintainability review recorded in
    :file:`Documentation/Reports/code-quality.md`.

For the full notes including migration guidance, open
:file:`CHANGELOG.md`.
