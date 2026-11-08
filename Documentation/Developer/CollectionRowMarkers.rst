..  include:: /Includes.rst.txt

..  _developer-collection-row-markers:

======================
Collection row markers
======================

Every entry of a Content Block ``Collection`` (an inline child record, a
"row") is marked on its root element so the Visual Editor can find it on the
page: the entries panel highlights the row, hidden entries are dimmed and
Visual Editor Enhancements 1.7 places its row controls there. Content Blocks
has no hook for this and child records have no wrapper of their own, so the
template announces the row with the ``f:mark.item`` ViewHelper of
`Visual Editor Enhancements <https://github.com/dirnbauer/typo3-visual-editor-enhancements>`__
(1.6 or later).

In edit mode the marker writes ``data-ve-item``, ``data-ve-item-live``,
``data-ve-collection`` and, for hidden entries, ``data-ve-item-hidden``.
Outside edit mode it renders nothing: the published markup is byte-identical
with and without markers.

Writing a marker
================

The ``collection`` argument is the parent's TCA column, not the template
identifier: a root Collection with ``prefixField: true`` is
``<typeName>_<identifier>`` (``desiderio_featurecards_items``), a nested
Collection keeps its identifier (``cells``). The ViewHelper throws in edit
mode for a name that is no inline column holding the entry's table.

..  code-block:: html

    <f:for each="{data.items}" as="item">
        <li class="x"{item -> f:mark.item(collection: 'desiderio_demo_items')}>…</li>
    </f:for>

    <f:for each="{data.items}" as="item">
        <d:molecule.card attributes="{item -> f:mark.item(collection: 'desiderio_demo_items', format: 'array')}">…</d:molecule.card>
    </f:for>

    <!-- existing attributes are merged; [] outside edit mode keeps them unchanged -->
    <d:molecule.card attributes="{item -> f:mark.item(collection: 'desiderio_demo_items', format: 'array') -> f:merge(with: {role: 'listitem'})}">

-   On an HTML root the marker sits directly before ``>`` (or ``/>``) with no
    space in front: its output starts with one.
-   A ``d:`` component root receives the marker array through its
    ``attributes`` argument. Components used as row roots declare
    ``<f:argument name="attributes" type="array" optional="{true}" default="{}" />``
    and spread it onto every root tag, like ``Card``
    (``ComponentStructureTest::testRowRootMoleculesDeclareAttributes``).
-   One entry rendered by several loops (tabs and panels, slides and dots) is
    marked in each loop; the Visual Editor joins them into one row.

The codemod
===========

``Build/Scripts/codemod-collection-markers.php`` adds every missing marker;
run it after adding or changing a Content Block template:

..  code-block:: shell

    php Build/Scripts/codemod-collection-markers.php --dry-run   # show the changed lines
    php Build/Scripts/codemod-collection-markers.php             # add missing markers
    php Build/Scripts/codemod-collection-markers.php --check     # exit 1 on unmarked, unlisted loops

It reads each ``config.yaml`` for its Collection fields (nested ones too) and
each ``templates/frontend.html`` with a tolerant Fluid tokenizer, then marks
every ``<f:for each="{data.<field>}">`` (or ``{<entry>.<field>}`` for a nested
Collection, or an ``f:variable`` alias of either) whose body renders exactly
one root element per entry:

-   ``f:if``/``f:then``/``f:else``, ``f:switch``/``f:case``/``f:defaultCase``
    and ``f:spaceless`` are transparent; alternative branches count once, and
    each branch root is marked.
-   ``f:variable``, ``f:asset.*``, ``f:comment`` and HTML comments render
    nothing.
-   An inner ``<f:for>`` over a File field of the entry with ``maxitems: 1``
    renders its body at most once and is transparent too (the gallery
    thumbnails).
-   A root that is a tag-based Fluid ViewHelper, an ``attributes`` value that
    is neither an array literal nor a variable, sibling roots or text-only
    bodies are not rewritten: mark them by hand or list the loop as an
    exception.

A second run changes nothing. The rules live in
``Classes/Templates/CollectionMarker`` and are shared with
``Tests/Unit/ContentBlockCollectionMarkerTest``, which fails on any unmarked,
unlisted or unreadable loop, on markers outside loop roots, on a marker that
names another column or variable, and on stale exceptions.

Exceptions
==========

``CollectionMarkerExceptions::EXCEPTIONS`` lists the loops without a marker,
each with its reason. A key is ``<block>.<collection path>`` (every loop over
that Collection) or ``<block>.<collection path>#<n>`` (only the n-th loop):

-   ``chart.data_points#1``: computes summary values only; the data table loop
    carries the markers.
-   ``chart-contribution.points#1`` and ``chart-donut.segments#1``: write the
    JSON the chart script draws from; the data table (and the donut legend)
    carry the markers.
-   ``gallery.items#1``: the featured view renders the first entry as two
    siblings; the thumbnail loop carries the markers.

Without a marker the entries still appear in the Visual Editor's entries
panel; only the on-page row highlight and controls are missing.

Proving the published markup unchanged
======================================

A template-wide marker change must not alter what visitors get. With the
disposable app of ``Build/CiApp`` serving the element library, capture every
preview before and after (flush caches in between) and compare:

..  code-block:: shell

    vendor/bin/typo3 desiderio:library:urls --site=main --json > urls.json
    php Build/Scripts/compare-element-renders.php capture urls.json var/renders-before
    php Build/Scripts/compare-element-renders.php capture urls.json var/renders-after
    php Build/Scripts/compare-element-renders.php compare var/renders-before var/renders-after

Only EXT:form's per-request honeypot and signed form state are masked. For
4.36.0 all 263 previews matched (246 byte for byte, 17 forms after masking).

Without Visual Editor Enhancements
==================================

Composer requires ``webconsulting/visual-editor-enhancements: ^1.6``. Where the
extension is not loaded anyway (a classic-mode install, a test or project
setup without it), ``ext_localconf.php`` registers
``Webconsulting\Desiderio\ViewHelpers\RowMarkerFallback`` on the ``f``
namespace: an ``f:mark.item`` with the same arguments that always renders
nothing, so no template fails on an unknown ViewHelper.
