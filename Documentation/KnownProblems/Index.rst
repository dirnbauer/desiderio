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

..  _known-problems-phpstan-baseline:

PHPStan baseline
================

Legacy type drift in ``Classes/Seeding/StyleguideFixtureResolver.php``
and related seeding services is captured in ``phpstan-baseline.neon`` as
a ratchet target. Seed commands are thin orchestration shells; do not
move fixture logic back into ``Classes/Command/``. New code in
``Classes/`` must pass at ``level: max`` without extending the baseline.
