..  include:: /Includes.rst.txt

..  _developer-collection-consolidation:

Collection table consolidation
==============================

Desiderio 3.0 introduced 12 shared Record Types for 29 collections whose child
field definitions match. The upgrade wizard ``desiderioSharedCollectionTables``
remains available for older installations. Its source-to-target mapping is
committed in ``Build/Data/collection-merge-map.json``.

..  _developer-collection-contract:

Shared-table contract
---------------------

Matching SQL column types alone is insufficient: fields must also agree on
required flags, rich-text settings, select items, labels, and other editor
behavior. Collections with different field definitions keep separate tables.
``Build/Scripts/derive-collection-merge-map.php`` can inspect the current
Content Blocks field definitions when reviewing future candidates.

Every shared collection declares ``foreign_table`` and both
``shareAcrossTables: true`` and ``shareAcrossFields: true``. Its Record Type
uses ``prefixFields: false``; the referencing Collection does not define a
duplicate ``fields`` block. Parent records are matched by UID, ``tablenames``,
and ``fieldname``. The structural audit and seeding tests enforce this contract.

..  _developer-collection-upgrade:

Upgrade procedure
-----------------

Back up the application database and FAL files. Install the new package and
apply additive schema changes so the target Record Type tables exist. Keep the
source tables and their original names until migration verification is complete.

Run these commands from the TYPO3 application root; the example assumes
Desiderio is installed under ``vendor/webconsulting/desiderio``:

..  code-block:: shell
    :caption: Compare collection data around the upgrade

    TYPO3_PATH_APP="$PWD" TYPO3_PATH_ROOT="$PWD/public" \
      php vendor/webconsulting/desiderio/Build/Scripts/verify-collection-merge.php \
      snapshot before.json
    vendor/bin/typo3 upgrade:run desiderioSharedCollectionTables
    TYPO3_PATH_APP="$PWD" TYPO3_PATH_ROOT="$PWD/public" \
      php vendor/webconsulting/desiderio/Build/Scripts/verify-collection-merge.php \
      compare before.json

Set ``TYPO3_PATH_ROOT`` to the application’s actual public directory if it has
another name. ``TYPO3_PATH_APP`` also selects the application’s Composer loader,
avoiding a separate extension-development vendor tree.

The wizard copies child rows into the shared tables, records each old/new UID
pair in ``tx_desiderio_collection_uid_map``, and remaps file references,
translation parents, and workspace originals. Already mapped rows are skipped on
subsequent runs. The wizard neither renames nor drops source tables.

The comparison checks the multiset of payload hashes, ownership, sorting,
language, and reference integrity. Run it immediately after the wizard and
before any demo reseed: seeders legitimately replace fixture rows, so their
output is not the pre-migration baseline. Then inspect affected content
collections and their images in the backend and frontend.

..  _developer-collection-recovery:

Cleanup and recovery
--------------------

Only remove obsolete source tables after comparison passes and the migrated site
has been reviewed. Keep the backup and UID mapping with the deployment record.
The preserved source rows help diagnose a failed migration, but do not by
themselves undo remapped references; use the database backup to restore the
complete pre-upgrade state when needed.
