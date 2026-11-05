..  include:: /Includes.rst.txt

..  _developer-visual-editor-inline-showcase:

======================
Inline record showcase
======================

``/content-types/visual-editor-inline`` contains 23 blocks for exercising
collection editing: cards, compact and spacious lists, shared child tables,
rich text, two-level table/pricing relations and media fallbacks. It renders
anonymously without Visual Editor controls. The page is hidden in navigation
and has ``no_search=1``; ``ShowcasePage.noSearch`` persists that property.
Prices come from ``ShowcasePricing`` and images from existing fixtures.

The page sets ``tx_visualeditorenhancements.inlineRecords.enabled = 1``.
Users must also enable collection editing in their Visual Editor settings.
Structural changes save immediately, require a clean editor and are not undone
by Reset. New live entries remain hidden until enabled in the backend form.
Required media and connected translated collections use the backend form.
The actual editing actions belong to EXT:visual_editor_enhancements.

Targeted seeding
================

Run from the installed TYPO3 project, after making a database backup. The
parent and linked pages must already exist. Never replace this targeted run
with a complete site seed just to add the playground.

..  code-block:: shell

    vendor/bin/typo3 desiderio:styleguide:seed --pages=/content-types/visual-editor-inline --dry-run
    vendor/bin/typo3 desiderio:styleguide:seed --pages=/content-types/visual-editor-inline

The final ``Reseeded pages:`` line gives the UID for page-scoped translation.
Reseeding replaces this page's content and translations, including collection
descendants. Re-create its translations afterwards using the site's own
translation command and memories. Other pages are left alone.

Activate supported limit keys safely
===================================

Content Blocks reads lowercase ``minitems``/``maxitems``. Older Desiderio YAML
used camelCase, which was ignored. File fields also need ``minitems: 1``;
``required: true`` is not supported by the File parser. This change activates
the declared constraints and can expose existing incomplete records.

Before upgrading each installation, run the read-only audit against its actual
database and review every violation:

..  code-block:: shell

    php vendor/webconsulting/desiderio/Build/Scripts/audit-collection-limits.php > limits.json

Run the new script/YAML against the existing installation before activation
if working from a package clone. Its SQL uses installed TCA relations, including
shared-table ownership fields. It counts hidden and translated live parents,
nested children and required FAL references. Deleted rows and workspace versions
are excluded; draft validation remains an integration check. A violation returns
exit code 1. Resolve it through targeted editorial changes/migrations or explicitly
defer the affected constraint. Do not lower limits or invent content for a green
result. The audit does not modify records.
