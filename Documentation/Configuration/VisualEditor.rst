..  include:: /Includes.rst.txt

..  _configuration-visual-editor:

==============
Visual Editor
==============

Desiderio is built for inline frontend editing with
``friendsoftypo3/visual-editor`` and the Visual Editor enhancements package.

..  _configuration-visual-editor-middleware:

Plugin request sanitizing
=========================

Desiderio registers ``ExtbasePluginRequestSanitizerMiddleware`` to remove
non-string or empty Extbase ``controller`` and ``action`` values from frontend
query and POST arguments. Visual Editor persistence requests can otherwise
leave those arguments malformed, which makes News and other Extbase plugins
throw while the edited page renders.

..  _configuration-visual-editor-images:

Images in edit mode
===================

Content Block image fields pass each FAL ``FileReference`` directly to
``<f:image image="{fileReference}">``. In edit mode, Visual Editor decorates
that output with the ``data-veedit`` metadata its image overlay needs. Custom
``data-*`` attributes are a separate concern and belong in Fluid's structured
``data="{...}"`` argument.

..  _configuration-visual-editor-picker:

The element picker
==================

The "Add content" panel receives its catalog from the ``?elementLibrary=1``
endpoint: the 244 Desiderio Content Blocks, the supported native TYPO3 content
types whose provider extensions are loaded, and the blocks contributed by
Innesto when that extension is installed.

The catalog metadata derived from the on-disk definitions is cached as
``desiderio_library`` (``SimpleFileBackend`` by default), so opening the picker
does not re-parse 244 ``config.yaml`` files each time. The cache key
fingerprints definition paths and modification times, so adding, editing or
removing an element self-invalidates it; "flush all caches" also clears it.

Previews are rendered frontend documents in ``<iframe>`` elements, not
generated thumbnails. Each iframe URL renders one seeded ``tt_content`` record
through a dedicated ``PAGE`` type, stored in the standard page cache with a
separate entry per site base. Pre-render them with
``vendor/bin/typo3 desiderio:library:warm``.

The picker's search box is typo-tolerant and runs server-side
(``?elementLibrarySearch=<term>``): a cached, weighted token index over each
element's title, keywords, synonyms, group and description with
``levenshtein()``-based fuzzy matching, autocomplete and "did you mean"
suggestions — pure PHP, no external search service.

Implementation details: :ref:`developer-element-library`,
:ref:`developer-element-library-search`.
