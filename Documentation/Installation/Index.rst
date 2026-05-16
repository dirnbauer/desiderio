..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

Desiderio targets **TYPO3 v14.3 LTS only** and **PHP 8.3 – 8.5**. Older
TYPO3 branches are not supported.

..  rst-class:: bignums

#.  Install with Composer

    ..  code-block:: shell

        composer require webconsulting/desiderio
        vendor/bin/typo3 extension:setup
        vendor/bin/typo3 cache:flush

    The package pulls in :t3:`typo3/cms-workspaces` ``^14.3`` so
    editorial draft / preview workflows are available out of the box.

#.  Enable the base site set

    Open *Site Management → Sites*, edit the target site, and add
    ``Desiderio Base`` (``webconsulting/desiderio``).

#.  Pick a preset

    Add **one** of the five preset sets (``…/desiderio-preset-saas``,
    ``…/desiderio-preset-corporate``, ``…/desiderio-preset-portfolio``,
    ``…/desiderio-preset-editorial``, ``…/desiderio-preset-dashboard``).
    Switching presets never changes content, markup, or backend
    layouts — only the presentation.

#.  Flush caches and reload the backend

    ..  code-block:: shell

        vendor/bin/typo3 cache:flush

..  _installation-optional-integrations:

Optional integrations
=====================

Desiderio ships site sets that enable shadcn-styled overrides for
optional TYPO3 ecosystem extensions:

..  list-table::
    :header-rows: 1
    :widths: 35 65

    *   - Extension
        - Site set wired by Desiderio
    *   - ``georgringer/news``
        - ``webconsulting/desiderio-news`` (registers
          ``Resources/Private/Extensions/News/`` template paths and
          turns on the **Load more** list mode for ``DesiderioBlog``
          and ``DesiderioNews`` page layouts).
    *   - ``apache-solr-for-typo3/solr``
        - The base set registers the working shadcn-styled Solr
          templates from ``Resources/Private/Solr/`` and removes the
          default Solr frontend assets. The hidden
          ``webconsulting/desiderio-solr`` set is currently a no-op;
          ``Resources/Private/Extensions/Solr/`` stays on disk for
          typed-template coverage until that parallel tree is ready for
          runtime registration.
    *   - ``t3g/blog``
        - ``webconsulting/desiderio-blog`` (registers
          ``Resources/Private/Extensions/Blog/`` template paths so
          ``BlogList`` / ``BlogPost`` / ``Post/*`` / ``Widget/*`` /
          ``Comment/*`` render through shadcn ``<d:…>`` components).
          Every partial declares Fluid 5.3 typed ``<f:argument>`` for
          its inputs.
