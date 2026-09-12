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
