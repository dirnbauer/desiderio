<!--
Paste the body below into a new issue at:
  https://github.com/FriendsOfTYPO3/content-blocks/issues/new

Once filed, replace the "TODO link to issue" placeholder in
composer.json (extra.patches) with the issue/PR URL.
-->

# SqlGenerator emits `KEY parent_uid (foreign_table_parent_uid)` without ever creating the column

## Summary

For every Content Block that defines a `Collection` field, `Generator/SqlGenerator::handleParentReferences()` emits a `CREATE TABLE … (KEY parent_uid (foreign_table_parent_uid))` statement for the child table, but never the column it indexes. The column is expected to be added by Core's `DefaultTcaSchema`, which works in some setups but silently fails in others — and when it fails, the Database Analyzer breaks with hundreds of identical errors and the backend Page module's navigation tree refuses to load (`Fehler beim Laden der Navigation` / "Error loading navigation").

This is asymmetric to how the same method already handles `foreign_match_fields` (lines 96-101): for those, both the index entry **and** a `CREATE TABLE … (column)` statement are emitted.

## Environment

- TYPO3: **14.3.x LTS**
- friendsoftypo3/content-blocks: **2.3.3** (also reproduces on 2.3.0 — 2.3.2; the relevant code in `SqlGenerator` has not changed since `2.0`)
- PHP: 8.3
- DB: MariaDB / MySQL 8

## Reproduction

1. Define a `tt_content` Content Block with a `Collection` field, e.g. `desiderio/accordion`:

   ```yaml
   name: desiderio/accordion
   typeName: desiderio_accordion
   prefixFields: false
   fields:
     - identifier: items
       type: Collection
       table: accordion_items
       fields:
         - { identifier: title,   type: Textarea, rows: 1 }
         - { identifier: content, type: Textarea, enableRichtext: true, rows: 5 }
   ```

2. Run the Database Analyzer in Install Tool / `vendor/bin/typo3 extension:setup` / `vendor/bin/typo3 database:updateschema`.

We hit this in a project with **250+** Content Blocks (many sharing the unprefixed `items` field name). Out of those, only a handful end up with the column auto-added by Core; the rest hit the error below.

## Expected behaviour

Either the column **and** the index get created, or neither.

## Actual behaviour

The Database Analyzer emits this error once per affected child table (often dozens to hundreds of times — which also crashes the Page tree AJAX response):

```
Error: An exception occurred while executing a query:
Key column 'foreign_table_parent_uid' doesn't exist in table
```

The proposed CREATE TABLE for each child looks like (note no `foreign_table_parent_uid` column declared, but the `INDEX parent_uid` references it):

```sql
CREATE TABLE `accordion_items` (
  `uid` INT UNSIGNED AUTO_INCREMENT NOT NULL,
  `pid` INT UNSIGNED DEFAULT 0 NOT NULL,
  …
  `title` LONGTEXT … DEFAULT NULL …,
  `content` LONGTEXT … DEFAULT NULL …,
  INDEX `parent_uid` (foreign_table_parent_uid),   -- ← references missing column
  INDEX `parent` (pid, deleted, hidden),
  …
  PRIMARY KEY (uid)
) ENGINE = InnoDB ROW_FORMAT = Dynamic;
```

## Why Core's auto-column logic isn't catching it

`TYPO3\CMS\Core\Database\Schema\DefaultTcaSchema::enrichSingleTableFieldsFromTcaColumns()` adds the `foreign_table_parent_uid` column to the child table when it iterates the **parent** TCA and finds an `InlineFieldType` field whose `foreign_field` config points at `foreign_table_parent_uid` (see `DefaultTcaSchema.php` ≈ line 808-877).

We believe this misses cases where many Content Blocks share the **same** parent column name (e.g. `items` on `tt_content`, with `prefixFields: false`). Core can only see one `foreign_table` value per `tt_content.columns.items` entry, so it adds the column to that one child table — not to the others. Whatever the precise failure mode is, the Content Blocks side already knows the full set of child tables it emits indexes for, so the safest fix is to make `SqlGenerator` defensive and stop relying on Core to fill the gap.

## Suggested fix

Mirror the existing `foreign_match_fields` handling: track each `foreign_field` name and emit a matching `CREATE TABLE … (column)` statement. Patch (also attached as `patches/content-blocks-add-foreign-field-column.patch` in our project repo):

```diff
--- a/Classes/Generator/SqlGenerator.php
+++ b/Classes/Generator/SqlGenerator.php
@@ -72,6 +72,7 @@
     {
         $indexes = [];
         $fields = [];
+        $foreignFields = [];
         $table = $tableDefinition->table;
         foreach ($tableDefinition->parentReferences as $parentReference) {
             $index = [];
@@ -89,6 +90,7 @@
             if (isset($parentTcaConfig['foreign_field'])) {
                 $foreignField = $parentTcaConfig['foreign_field'];
                 $index[] = $foreignField;
+                $foreignFields[] = $foreignField;
             }
             $indexes[] = $index;
         }
@@ -99,6 +101,12 @@
                 $sql[] = $sqlStatement;
             }
         }
+        foreach ($foreignFields as $foreignFieldName) {
+            $sqlStatement = 'CREATE TABLE `' . $table . '` (`' . $foreignFieldName . '` int(11) UNSIGNED DEFAULT \'0\' NOT NULL);';
+            if (!in_array($sqlStatement, $sql, true)) {
+                $sql[] = $sqlStatement;
+            }
+        }
         $indexes = array_map(fn(array $index): string => implode(', ', $index), $indexes);
         $indexes = array_unique($indexes);
         $indexes = array_values($indexes);
```

Column type/defaults match what Core uses in `DefaultTcaSchema` lines 814-822 (`INTEGER, default 0, notnull true, unsigned true`), so the column ends up identical no matter which side wins the schema merge.

If a Collection's child table TCA later defines `foreign_table_parent_uid` itself with a different shape, the user's `ext_tables.sql` already takes precedence over both Core and Content Blocks via `isColumnDefinedForTable()` (Core) and the per-statement `in_array` dedupe (above), so there should be no regression.

## Workaround until fixed

Apply the patch above via `cweagans/composer-patches` (or any other patching mechanism). Happy to open a PR with the same change if the fix direction is acceptable — let me know.
