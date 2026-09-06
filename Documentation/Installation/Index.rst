..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

Desiderio requires TYPO3 14.3.6 or newer on the 14.3 branch and PHP 8.4
or 8.5. Composer installs Content Blocks, the Form Framework, Workspaces,
Visual Editor, and the Vite asset collector as package dependencies.

..  rst-class:: bignums

#.  Install with Composer

    ..  code-block:: shell
        :caption: Install the extension

        composer require webconsulting/desiderio
        vendor/bin/typo3 extension:setup
        vendor/bin/typo3 cache:flush

#.  Enable the base set

    Open :guilabel:`Site Management > Sites`, edit the target site, and
    add ``Desiderio Base`` (``webconsulting/desiderio``).

#.  Enable the content elements

    Add ``Desiderio Content Elements``
    (``webconsulting/desiderio-content-elements``). This set registers
    the generated Content Blocks and their frontend rendering.

#.  Pick a site preset

    Add ``Desiderio Preset Corporate``
    (``webconsulting/desiderio-preset-corporate``) for the demo site
    structure, header, footer, and page templates.

#.  Flush caches

    ..  code-block:: shell
        :caption: Flush TYPO3 caches

        vendor/bin/typo3 cache:flush

..  _installation-upgrading:

Upgrading an existing installation
=================================

Back up the database and FAL files, then update the installed package and
TYPO3 dependencies from the application root:

..  code-block:: shell
    :caption: Update Desiderio and TYPO3 14.3 dependencies

    composer update webconsulting/desiderio 'typo3/cms-*' --with-all-dependencies
    vendor/bin/typo3 extension:setup
    vendor/bin/typo3 upgrade:list

Review pending upgrade wizards before clearing caches and checking the site.
When upgrading from before Desiderio 3.0, preserve the old collection tables
until the ``desiderioSharedCollectionTables`` wizard and its data comparison
have completed. The :doc:`collection migration guide
<../Developer/CollectionTableConsolidation>` describes the required snapshot,
copy, reference remapping, and verification order.

For older plain-text fields converted to RTE fields, review the dry-run output
of ``vendor/bin/typo3 desiderio:migrate-rte-content`` before adding ``--apply``.
This uses the committed conversion manifest and leaves existing HTML intact.

..  code-block:: shell
    :caption: Clear caches after successful migrations

    vendor/bin/typo3 cache:flush

Verify representative page layouts, changed content elements, form handling,
and any enabled Blog, News, Solr, or Powermail integration. Demo seeders replace
fixture content and are not a required upgrade step for an editorial site.

..  _installation-optional-integrations:

Optional integrations
=====================

Desiderio only activates extension-specific templates when the related
extension is installed and its site set is enabled.

..  list-table::
    :header-rows: 1
    :widths: 28 32 40

    *   - Extension
        - Site set
        - What is registered
    *   - ``t3g/blog``
        - ``webconsulting/desiderio-blog``
        - Blog list/detail templates, widgets, comments, RSS content type
          headers, and Blog page layouts from
          ``Resources/Private/Extensions/Blog/``.
    *   - ``georgringer/news``
        - ``webconsulting/desiderio-news``
        - News list/detail templates, image rendering, categories, tags,
          schema-oriented markup, and News page layouts from
          ``Resources/Private/Extensions/News/``.
    *   - ``apache-solr-for-typo3/solr``
        - ``webconsulting/desiderio-solr``
        - shadcn-styled search result templates, a JSON suggest endpoint,
          and TypoScript that removes the default Solr frontend assets.
    *   - ``in2code/powermail``
        - ``webconsulting/desiderio-powermail``
        - Powermail templates that use the shared shadcn form primitives,
          neutral borders, validation states, and Friendly Captcha support.
    *   - ``studiomitte/friendlycaptcha``
        - Used by form integrations
        - Friendly Captcha test-mode mapping for local development. Use
          real site keys and API keys in production.

..  _installation-seed-commands:

Seed and setup commands
=======================

Desiderio ships console commands for demo content and integration setup.
Commands live in ``Classes/Command/``; shared FAL import, collection
insert/cleanup, and fixture normalization live in ``Classes/Seeding/``.
Run the commands after the matching site sets are enabled.

..  list-table::
    :header-rows: 1
    :widths: 32 68

    *   - Command
        - Purpose
    *   - ``desiderio:styleguide:seed``
        - Create or update styleguide fixture pages below ``--parent``.
          Requires the live workspace. See :ref:`known-problems-seed-command`.
    *   - ``desiderio:starter:seed``
        - Create or update the corporate starter site structure and demo
          content.
    *   - ``desiderio:blog:seed-pages``
        - Normalize Blog page trees to Desiderio backend layouts. No-op
          when ``t3g/blog`` is not loaded.
    *   - ``desiderio:news:seed-taxonomy``
        - Assign default category/tag relations to visible News records
          without taxonomy. No-op when ``georgringer/news`` is not loaded.

..  code-block:: shell
    :caption: Common seed commands

    vendor/bin/typo3 desiderio:styleguide:seed --parent=<page-uid>
    vendor/bin/typo3 desiderio:starter:seed
    vendor/bin/typo3 desiderio:blog:seed-pages --root=<blog-root-uid>
    vendor/bin/typo3 desiderio:news:seed-taxonomy --storage-pid=<news-storage-pid>

The blog command updates Blog root, list, detail, translated, and
data-folder pages to use the Desiderio Blog backend layouts.

..  _installation-assets:

Frontend assets
===============

The extension ships committed CSS and lightweight JavaScript. The most
important runtime files are:

..  list-table::
    :header-rows: 1
    :widths: 30 70

    *   - Asset
        - Purpose
    *   - ``Resources/Public/Css/shadcn-theme.css``
        - ui.shadcn.com/create token blocks, house presets, dark mode, and theme
          variables.
    *   - ``Resources/Public/Js/desiderio.js``
        - Generic frontend behavior, search suggestions, interactions,
          and small UI enhancements.
    *   - ``Resources/Public/Js/astro.js``
        - Lightweight behavior for counters, reveal effects, carousels,
          countdowns, and progressive UI states.
    *   - ``Resources/Public/Js/prism-lite.js``
        - Lightweight syntax highlighting for code examples without using
          accent colours.
    *   - ``Resources/Public/Js/charts.js``
        - Accessible chart rendering helpers for Content Block chart
          elements.
