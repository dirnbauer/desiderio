..  include:: /Includes.rst.txt

================
For editors
================

Desiderio ships **255 content elements** organised into ten wizard
groups. Most elements behave like familiar TYPO3 blocks — the
extension's value is consistency: every element uses the same shadcn
tokens, spacing scale, and dark-mode behaviour.

News & blog
===========

The shadcn-styled News templates are activated automatically when
``georgringer/news`` is installed. Pages built on the
``DesiderioBlog`` or ``DesiderioNews`` backend layouts default to the
**Load more** list mode.

..  list-table:: News list settings (TypoScript / plugin override)
    :header-rows: 1
    :widths: 40 25 35

    *   - Setting
        - Default
        - Purpose
    *   - ``plugin.tx_news.settings.list.useLoadMore``
        - ``0`` (forced on for ``DesiderioBlog`` / ``DesiderioNews``)
        - Switch the list partial from server-paginated to progressive
          load-more.
    *   - ``plugin.tx_news.settings.list.initialCount``
        - ``6``
        - How many cards are shown before the **Load more** button
          appears.
    *   - ``plugin.tx_news.settings.list.loadMoreCount``
        - ``3``
        - The "extra number to be loaded" each click.

The button degrades gracefully — without JavaScript, the entire list is
visible.

A magazine-style alternative list (``MagazineList.html``) features the
first article on top with the rest as the load-more secondary grid; set
``plugin.tx_news.settings.templateLayout`` to ``magazine`` on a plugin
instance to opt in.

Workspaces
==========

Desiderio is workspace-aware. Editing a content element from a
workspace shows the draft state in the backend preview. The
``desiderio:styleguide:seed`` CLI command writes styleguide fixture
records to the live workspace only and refuses to run from an offline
workspace. Its cleanup queries are scoped to live workspace rows so
draft overlays are not deleted while editors prepare staged content.
