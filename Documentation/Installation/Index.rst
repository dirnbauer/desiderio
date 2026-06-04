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

#.  Enable the shadcn/ui theme base

    Open *Site Management → Sites*, edit the target site, and add
    ``Desiderio Base`` (``webconsulting/desiderio``).

#.  Enable the content-element configuration set

    Add ``Desiderio Content Elements``
    (``webconsulting/desiderio-content-elements``). This aggregate site set
    depends on the theme base and pulls in the generated Content Block sets.

#.  Pick the scenario template

    Add the Corporate scenario template set
    (``webconsulting/desiderio-preset-corporate``). Switching the scenario
    template never changes content, markup, or backend layouts — only the
    presentation.

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
          its inputs. For existing Blog page trees, run
          ``vendor/bin/typo3 desiderio:blog:seed-pages --root=<blog-root-uid>``
          after enabling the set so root, data-folder, list, translated,
          and post pages share the Desiderio Blog backend layout. The
          command exits without changes when ``t3g/blog`` is not loaded.
