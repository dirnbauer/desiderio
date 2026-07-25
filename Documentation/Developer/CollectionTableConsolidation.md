# Collection table consolidation

Status: **implemented in 3.0.0** for the 12 clean groups (29 tables → 12).
The 54 tables that need `overrideChildTca` scaffolding were deliberately left
alone; the measurement below is why. Regenerate every number with:

```
php Build/Scripts/derive-collection-merge-map.php
```

## The situation

244 content elements declare **175 collection tables**. Each carries the same 21
TYPO3 system columns (`uid`, `pid`, `deleted`, `sorting`, `l10n_*`, `t3ver_*`,
`foreign_table_parent_uid`, …) and, on average, **3.3 payload columns**. Across
all 175 tables that is 4,258 columns of which only 583 are payload. The
duplication is real and it is the reason this was investigated.

Content Blocks offers `shareAcrossTables` + `shareAcrossFields` on a Collection
declared with `foreign_table:`, which is the supported way to point several
elements at one child table.

## What the measurement changed

The obvious way to find merge candidates is to group tables by their SQL column
signature. **That answer is wrong, and wrong in the optimistic direction.**

`foreign_table:` makes each sharer's own `fields:` block inert — exactly one
field definition wins for every element that shares the table. So two
collections may only merge when their child field *definitions* match, not
merely their columns. SQL types cannot see `required`, `enableRichtext`,
`richtextConfiguration`, Select `items`, `rows` or labels.

| Grouped by | Mergeable tables | Net table reduction |
|---|---:|---:|
| SQL column signature | 83 | −60 |
| **Content Blocks field definitions** | **29** | **−17** |

Of the 23 identical-column groups, **11 groups covering 54 tables** diverge
behaviourally. In 10 of those 11 the blocker is a single `required` flag: some
elements make the field mandatory, others do not. Merging forces one answer on
all of them — either loosening validation where content relies on it, or
invalidating existing rows.

## What is genuinely mergeable

12 groups, 29 source tables → 12 shared tables. Frozen in
`Build/Data/collection-merge-map.json` with, per source table, its target, the
Content Blocks `fieldname` (the uniqueIdentifier, verified against live
`tt_content` column names) and its parent `tablenames`.

```
desiderio_icon_card_link  ← benefit_cards_items, feature_carousel_items, feature_grid_3_items
desiderio_logo_item       ← logo_carousel_logo_items, logo_cloud_logo_items, logo_grid_logo_items
desiderio_metric_item     ← analytics_overview_items, metric_dashboard_items, stat_cards_items
desiderio_nav_item        ← nav_pagination_items, nav_tabs_tabs, navbar_tabbed_tabs
desiderio_label_value     ← case_study_featured_metrics, product_specs_specs, stats_inline_stats
desiderio_qa_item         ← faq_items, pricing_faq_question_items
desiderio_quote_item      ← testimonial_grid_testimonials, testimonial_wall_testimonials
…and 5 more two-table groups
```

Migration volume for exactly this set:

- **722 rows** to move
- **90** `sys_file_reference` rows to remap (`uid_foreign` + `tablenames`)
- **39** translated rows
- **0** rows with `l10n_parent` — no connected-mode translation remapping
- **0** rows with `t3ver_wsid` — no workspace versioning to preserve

The two genuinely hard cases are absent from this subset. They live in the
*excluded* groups: `sitemap_grid_pages` (96 rows, 51 connected translations) is
in the 16-table `label/link` group, which is blocked on `label.required`.

## What the scope buys, honestly

**−17 tables of 441 (−3.9 %)**, and none of the real cost centres move:
`tt_content` keeps all 579 columns and its 80 MB `TcaSchema` block, the 162
counter columns stay, and `sys_history` (137 MB, 57 % of the database) is
untouched. The 175 collection tables hold 16.8 MB, almost entirely InnoDB
per-table overhead for 3,807 small rows.

So this is a maintainability change, not a performance one: one "link item"
model instead of many, and 29 fewer near-identical hidden tables to scroll past
in the Install Tool. It is bundled into a major version rather than motivating
one.

The 54 excluded tables would only qualify if every sharer carried an
`overrideChildTca` block restoring its own label and `required` — which
re-creates the divergence the merge was supposed to remove, and is what
`CONTRIBUTING.md` means by "the TCA matching rules stay unambiguous".

## What had to be fixed to make it safe

1. `CollectionCleanupService::deleteCollectionRowsForParentUids()` filtered only
   on `foreign_table_parent_uid`. On a shared table that deletes a *sibling*
   field's rows on the same parent. It now also matches `tablenames` and
   `fieldname`, and deletes by the uids it resolved rather than re-running the
   predicate, so lookup and delete cannot drift apart.
2. `ContentBlockDefinitionRegistry::buildCollectionDefinition()` did not resolve
   `foreign_table:` to the target's field list, so every seeder saw an empty
   child definition and would have silently seeded nothing.
3. `CollectionRecordSeeder` writes `tablenames` and `fieldname`. Nested
   collections carry no explicit `column`, so their uniqueIdentifier is taken
   from the payload key — without that fallback they were written with an empty
   `fieldname`, which the TCA cannot match and no later cleanup can find. The
   seeder now refuses to write an empty `fieldname` into a shared table.
4. Record types set `prefixFields: false`. With the default, `question` would
   become `desiderio_qaitem_question`: every column renamed and every template
   reading `{item.question}` broken.

## Verification

`Build/Scripts/verify-collection-merge.php` compares a MULTISET of per-row
payload hashes — payload, parent uid, language and sorting — before and after.
Row counts cannot see a row that survived with blanked content, and uids
necessarily change when rows move, so the multiset is the only honest question:
does every payload that existed before still exist afterwards, exactly once.

    ddev exec php Build/Scripts/verify-collection-merge.php snapshot before.json
    ddev typo3 upgrade:run desiderioSharedCollectionTables
    ddev exec php Build/Scripts/verify-collection-merge.php compare before.json

Run `compare` immediately after the migration and **before any reseed**: a
reseed hard-deletes and recreates collection children, so comparing a reseeded
database against a pre-migration baseline reports normal seeding as loss.

Result on the reference installation: 722 rows, 90 file references, PASS — plus
zero dangling file references and every one of the 29 collections verified to
still render its own content in the frontend.

## Rollback

The migration only ever INSERTs. Source tables are renamed to
`zzz_deleted_<table>` rather than dropped, and `tx_desiderio_collection_uid_map`
records every (old table, old uid) → new uid pair, so the move is auditable and
reversible until the operator drops those tables by hand.
