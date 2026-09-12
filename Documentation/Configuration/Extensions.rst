..  include:: /Includes.rst.txt

..  _configuration-extensions:

=====================
Optional integrations
=====================

Blog, News, Solr and Powermail support is delivered as separate site sets that
only do something when the matching extension is installed. Enable the set in
:guilabel:`Site Management > Sites` after installing the extension.

..  _configuration-extensions-forms:

Forms
=====

Desiderio ships shadcn-styled TYPO3 Form Framework templates and a shared
``d:molecule.formRenderer`` component. Form controls use neutral theme borders
by default and switch to destructive styling only for invalid states.

*   TYPO3 Form Framework finishers
*   Friendly Captcha integration with a Development-context bypass
    (:ref:`configuration-friendly-captcha`)
*   Brevo contact synchronization through ``BrevoContactFinisher``, including
    double opt-in
*   sanitized Brevo event tracking
*   DDEV/Mailpit-friendly local mail configuration
*   styled validation messages and accessible required-field indicators

The ``desiderio.forms.*`` settings are listed under
:ref:`configuration-forms`; the Brevo API key comes from the environment
(``BREVO_API_KEY``), never from site settings.

..  _configuration-extensions-search:

Search (EXT:solr)
=================

``webconsulting/desiderio-solr`` registers shadcn-styled search templates,
result cards, facets, suggestions and a JSON suggest page type. The header
search form is enabled and pointed at a result page with the
``desiderio.search.*`` settings (:ref:`configuration-search`).

The frontend JavaScript enhances compatible Solr forms with debounced
suggestions and keyboard-accessible result options.

..  _configuration-extensions-facets:

Filters are checkboxes
----------------------

Each facet group renders as a ``<fieldset>`` of checkboxes with a result count
per option, because several options of one group can be active at once — a
list of links cannot say that. The configured facets are:

..  list-table::
    :header-rows: 1
    :widths: 24 26 50

    *   - Facet
        - Solr field
        - Notes
    *   - Content type
        - ``type``
        - Pages, News and any other indexed record type, relabelled through
          the ``renderingInstruction`` of the set.
    *   - Category
        - ``category_stringM``
        - Filled by the ``IndexQueueNews`` configuration the set imports
          (``SOLR_RELATION`` over ``categories``). The group only appears once
          categorized records are indexed.

Both use ``operator = OR`` with ``keepAllOptionsOnSelection = 1`` and
``minimumCount = 0``: ticking a second option widens the result set, and every
option keeps the count it would have on its own, so the numbers do not
collapse to zero while filtering.

Checking a box navigates to the URL that adds the filter; unchecking it to the
URL that removes it. The 40-line
:file:`Resources/Public/Js/solr-facets.js` only saves the extra click —
it validates the same origin, disables the group while the page loads, and
never swaps results by AJAX. Without JavaScript every option is still a plain
link inside ``<noscript>``, and a :guilabel:`Reset filters` link appears as
soon as something is filtered.

To add another facet, point it at an indexed field and reuse the ``Options``
partial:

..  code-block:: typoscript
    :caption: config/sites/<site>/setup.typoscript

    plugin.tx_solr.search.faceting.facets.author {
        label = LLL:EXT:my_sitepackage/Resources/Private/Language/labels.xlf:facet.author
        field = author
        partialName = Options
        operator = OR
        keepAllOptionsOnSelection = 1
        minimumCount = 0
    }

..  _configuration-extensions-blog:

Blog (EXT:blog)
===============

With ``t3g/blog`` installed, ``webconsulting/desiderio-blog`` adds shadcn-styled
Blog template paths covering list, detail, sidebar widgets, comments, author
blocks, related posts, metadata badges, categories, tags and RSS output
headers.

Existing Blog page trees are aligned with ``desiderio:blog:seed-pages``
(:ref:`developer-commands`).

..  _configuration-extensions-news:

News (EXT:news)
===============

With ``georgringer/news`` installed, ``webconsulting/desiderio-news`` adds
shadcn-styled list and detail templates that use the available news images,
category and tag badges, responsive grids, metadata and ``NewsArticle``
structured data.

The set supports the ``DesiderioNews`` backend layout and the progressive
load-more list mode (``desiderio.news.useLoadMore``).
