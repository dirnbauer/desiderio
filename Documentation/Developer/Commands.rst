..  include:: /Includes.rst.txt

..  _developer-commands:

================
Console commands
================

Desiderio ships Symfony console commands for demo content, integration setup
and quality gates. Commands are thin orchestration shells; shared seeding logic
belongs in ``Classes/Seeding/``.

..  list-table::
    :header-rows: 1
    :widths: 34 66

    *   - Command
        - Purpose
    *   - ``desiderio:templates:lint``
        - Lint every shipped Fluid template against the Fluid 5 parser. See
          :ref:`developer-template-lint`.
    *   - ``desiderio:styleguide:seed``
        - Create or update styleguide fixture pages below a parent page.
          Requires the live workspace; refuses Production without
          ``--allow-production``.
    *   - ``desiderio:starter:seed``
        - Create or update the corporate starter site structure and content.
    *   - ``desiderio:blog:seed-pages``
        - Normalize an existing Blog page tree to Desiderio backend layouts.
          No-op when ``t3g/blog`` is not loaded.
    *   - ``desiderio:news:seed-taxonomy``
        - Assign default category/tag relations to visible News records that
          have none. No-op when ``georgringer/news`` is not loaded.
    *   - ``desiderio:library:seed``
        - Create or update the element library records used by the picker.
    *   - ``desiderio:library:urls``
        - List isolated element preview URLs, optionally as JSON.
    *   - ``desiderio:library:warm``
        - Warm rendered element previews in the TYPO3 page cache.
    *   - ``desiderio:migrate-rte-content``
        - Report legacy plain-text fields that need RTE markup; ``--apply``
          writes the conversion.

..  code-block:: bash
    :caption: Typical invocations

    vendor/bin/typo3 desiderio:templates:lint
    vendor/bin/typo3 desiderio:styleguide:seed --parent=<page-uid>
    vendor/bin/typo3 desiderio:starter:seed
    vendor/bin/typo3 desiderio:blog:seed-pages --root=<blog-root-uid>
    vendor/bin/typo3 desiderio:news:seed-taxonomy --storage-pid=<news-storage-pid>

Seed commands write FAL assets under :file:`fileadmin/desiderio-styleguide/` or
:file:`fileadmin/desiderio-starter/`. Re-running a seeder overwrites
live-workspace fixture metadata in place. Every seeder refuses to run in the
Production application context unless ``--allow-production`` is passed
(``ProductionContextGuard``).

..  _developer-template-lint:

The template lint gate
======================

``desiderio:templates:lint`` parses every template with the same
``RenderingContext`` TYPO3 uses at runtime and reports what the Fluid 5 parser
would throw, plus static checks the parser cannot do:

..  list-table::
    :header-rows: 1
    :widths: 28 72

    *   - Rule
        - Checks
    *   - ``parse``
        - Unknown ViewHelpers, unknown or missing arguments, unknown
          namespaces, unknown components.
    *   - ``namespace-usage``
        - A prefix used without a declaration, a declaration that is never
          used, and a non-canonical ``xmlns:f`` URI.
    *   - ``partial-resolves``
        - Every ``f:render partial="…"`` resolves inside the partial roots that
          apply to the template's context.
    *   - ``deprecated-constructs``
        - ``{namespace}``, ``f:widget.*``, ``f:be.*``, ``f:security.*`` and
          ``f:base`` must not appear.
    *   - ``component-arguments``
        - Every attribute at a component call site is declared with
          ``<f:argument>``, and every required argument is passed.

..  code-block:: bash
    :caption: Options

    --path=EXT:desiderio          # repeatable: EXT:key, a directory or one file
    --strict                      # fail on namespaces/partials of extensions that are not installed
    --format=text|json
    --rule=parse                  # repeatable: run only these rules

Templates whose ViewHelper namespace belongs to an extension that is not
installed are reported as "skipped" instead of failing, so the gate is usable
on an installation without EXT:news, EXT:solr, EXT:powermail or
EXT:friendlycaptcha. ``Tests/Functional/Templates/ShippedTemplatesLintTest.php``
runs the same gate over the whole extension and requires zero errors.

..  _developer-commands-seeding-services:

Shared seeding services
=======================

..  list-table::
    :header-rows: 1
    :widths: 34 66

    *   - Service
        - Responsibility
    *   - ``ExtensionFalSeeder``
        - FAL import and ``sys_file_reference`` writes.
    *   - ``CollectionRecordSeeder``
        - Recursive Content Blocks collection inserts.
    *   - ``CollectionCleanupService``
        - Live-workspace-scoped collection and FAL cleanup.
    *   - ``ContentBlockCollectionMap``
        - Parent-table to collection-table lookup from block definitions.
    *   - ``LiveWorkspaceQueryHelper``
        - ``t3ver_wsid`` / ``t3ver_oid`` constraints for destructive queries.
    *   - ``StyleguideFixtureResolver``
        - Styleguide YAML fixture normalization.
    *   - ``StarterContentBuilder``
        - Starter-site content block payloads.
    *   - ``BlogPageTreeSeeder``
        - Blog layout alignment and demo post seeding.
    *   - ``FixtureFieldNormalizer``
        - Shared scalar, file, checkbox and date field normalization.

``BrevoConfigurationResolver`` centralizes Brevo finisher configuration
precedence; the finisher itself only handles HTTP and form value mapping.
