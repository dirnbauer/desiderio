..  include:: /Includes.rst.txt

..  _changelog:

=========
Changelog
=========

The authoritative release notes live in :file:`CHANGELOG.md` at the
repository root and follow `Keep a Changelog
<https://keepachangelog.com/>`__. The current release is **2.6.0**.

Highlights:

*   Seed commands are thin orchestration shells; shared FAL, collection,
    and fixture logic lives in ``Classes/Seeding/``.
*   ``StyleguideFixtureResolver``, ``StarterContentBuilder``, and
    ``BlogPageTreeSeeder`` replace thousand-line command implementations.
*   ``FixtureFieldNormalizer`` and ``BrevoConfigurationResolver`` remove
    duplicated field and finisher configuration branches.
*   ``ExtbasePluginRequestSanitizerMiddleware`` fixes Visual Editor
    rendering for News and other Extbase plugins.
*   Thermo-nuclear maintainability review recorded in
    :file:`Documentation/Reports/code-quality.md`.

For the full notes including migration guidance, open
:file:`CHANGELOG.md`.
