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

#.  Enable one Desiderio site package

    Open *Site Management → Sites*, edit the target site, and add **one** of
    the five visible site package sets:

    *   ``webconsulting/site-package-desiderio-saas``
    *   ``webconsulting/site-package-desiderio-corporate``
    *   ``webconsulting/site-package-desiderio-portfolio``
    *   ``webconsulting/site-package-desiderio-editorial``
    *   ``webconsulting/site-package-desiderio-dashboard``

    Each site package imports the hidden theme base, hidden content-element
    aggregate, backend layouts, production defaults, and one hidden scenario
    preset. Switching packages keeps page records and backend layout names
    stable, but intentionally changes the page shell templates and presentation.

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
        - ``webconsulting/desiderio-solr`` registers the working
          shadcn-styled Solr templates from ``Resources/Private/Solr/``
          and removes the default Solr frontend assets.
    *   - ``t3g/blog``
        - ``webconsulting/desiderio-blog`` (registers
          ``Resources/Private/Extensions/Blog/`` template paths so
          ``BlogList`` / ``BlogPost`` / ``Post/*`` / ``Widget/*`` /
          ``Comment/*`` render through shadcn ``<d:…>`` components).
          Every partial declares Fluid 5.3 typed ``<f:argument>`` for
          its inputs.
