# Collection table consolidation — measurement and decision

Status: **analysis complete, migration not implemented.** This document records
what was measured, so the decision can be revisited without redoing the work.
Regenerate every number with:

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

- **707 rows** to move
- **90** `sys_file_reference` rows to remap (`uid_foreign` + `tablenames`)
- **39** translated rows
- **0** rows with `l10n_parent` — no connected-mode translation remapping
- **0** rows with `t3ver_wsid` — no workspace versioning to preserve

The two genuinely hard cases are absent from this subset. They live in the
*excluded* groups: `sitemap_grid_pages` (96 rows, 51 connected translations) is
in the 16-table `label/link` group, which is blocked on `label.required`.

## Why it was not implemented

The benefit is **−17 tables of 441 (−3.9 %)**, and none of the real cost centres
move: `tt_content` keeps all 579 columns and its 80 MB `TcaSchema` block, the
162 counter columns stay, and `sys_history` (137 MB, 57 % of the database) is
untouched. The 175 collection tables hold 16.8 MB, almost entirely InnoDB
per-table overhead for 3,807 small rows.

Against that, shipping it costs a **breaking major version** with an upgrade
wizard for every installation, 12 new record types, 29 config rewrites, changes
to the definition registry and three seeders, and a uid remap across four
reference surfaces.

There is also precedent: a shared `desiderio/testimonial-pool` RecordType was
added in **2.12.0** and removed in **2.13.0** the same day. And `CONTRIBUTING.md`
already states the rule — reuse a table "only when the child rows are
deliberately the same model and the TCA matching rules stay unambiguous". The
12 clean groups satisfy that. The 54 other tables only satisfy it if each sharer
carries an `overrideChildTca` block restoring its own label and `required`,
which re-creates the divergence the merge was supposed to remove.

## If it is revisited

The clean 12-group merge is the only defensible scope, and it is low risk
(no `l10n_parent`, no workspaces). It should ride along with a major version
that is happening anyway, not motivate one. Two things must be fixed as part of
it:

1. `CollectionCleanupService::deleteCollectionRowsForParentUids()` filters only
   on `foreign_table_parent_uid`. On a shared table that deletes a *sibling*
   field's rows on the same parent. It needs `tablenames` and `fieldname` too.
   This is latent today and becomes a live data-loss bug the moment any table is
   shared.
2. `ContentBlockDefinitionRegistry::buildCollectionDefinition()` does not resolve
   `foreign_table:` to the target's field list, so every seeder would see an
   empty child definition and silently seed nothing.
