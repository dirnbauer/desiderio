..  include:: /Includes.rst.txt

..  _known-problems:

================
Known problems
================

..  _known-problems-seed-command:

Seed command requires the live workspace
========================================

``vendor/bin/typo3 desiderio:styleguide:seed`` writes styleguide
fixture records to the live workspace. The command refuses to run when:

*   The active workspace is not the live workspace (returns failure
    with a clear error).
*   ``Environment::getContext()`` is ``Production`` and
    ``--allow-production`` is **not** passed.

If you need to seed against a Production sandbox, switch to the live
workspace first and call:

..  code-block:: shell

    vendor/bin/typo3 desiderio:styleguide:seed --allow-production

Files written by the command live in ``fileadmin/desiderio-styleguide/``
and are **not** workspace-versioned. Re-running the seeder overwrites
metadata in place; do not point it at editor-curated FAL folders.
Cleanup queries are explicitly scoped to live workspace rows when TYPO3
versioning columns exist, so existing draft overlays are left alone.

..  _known-problems-seeding-types:

Legacy type drift in the seeding services
=========================================

``Classes/Seeding/StyleguideFixtureResolver.php`` and the related seeding
services grew around loosely typed fixture payloads. The PHPStan baseline that
used to hide the resulting findings is gone (4.1.0): the code is analysed at
``level: 8`` like everything else, and new findings are fixed rather than
recorded. Seed commands stay thin orchestration shells — do not move fixture
logic back into ``Classes/Command/``.
