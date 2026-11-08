<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseVisualEditorInlinePage;

final class SeedVisualEditorInlinePageFunctionalTest extends FunctionalTestCase
{
    use ShowcasePreviewRendering;

    protected array $coreExtensionsToLoad = ['fluid_styled_content', 'form', 'workspaces', 'rte_ckeditor'];
    protected array $testExtensionsToLoad = ['friendsoftypo3/content-blocks', 'friendsoftypo3/visual-editor', 'webconsulting/visual-editor-enhancements', 'praetorius/vite-asset-collector', 'webconsulting/desiderio'];

    protected array $configurationToUseInTestInstance = [
        // The preview page type is signed with a cHash in production; the
        // test builds its URLs by hand. Vite: no manifest in the test instance.
        'FE' => ['cacheHash' => ['enforceValidation' => false]],
        // Edit mode needs a real backend session (the Visual Editor creates
        // form tokens); the sub-request carries its cookie from no fixed IP.
        'BE' => ['lockIP' => 0, 'lockIPv6' => 0],
        'EXTENSIONS' => ['vite_asset_collector' => ['useDevServer' => '1']],
    ];

    public function testTargetedSeedAndReseedPreserveOtherPagesAndLeaveNoDescendants(): void
    {
        self::assertSame(2, $GLOBALS['TCA']['tt_content']['types']['desiderio_tabs']['columnsOverrides']['desiderio_tabs_items']['config']['minitems']);
        self::assertSame(3, $GLOBALS['TCA']['tt_content']['types']['desiderio_pricingthreetier']['columnsOverrides']['desiderio_pricingthreetier_plans']['config']['minitems']);
        self::assertSame(3, $GLOBALS['TCA']['tt_content']['types']['desiderio_pricingthreetier']['columnsOverrides']['desiderio_pricingthreetier_plans']['config']['maxitems']);
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SeedingBase.csv');
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        $connection->insert('pages', ['pid' => 1, 'title' => 'Content types', 'slug' => '/content-types', 'doktype' => 1]);
        $parentUid = (int)$connection->lastInsertId();
        $connection->insert('tt_content', ['pid' => $parentUid, 'CType' => 'text', 'header' => 'Keep me']);
        $foreignUid = (int)$connection->lastInsertId();
        foreach (['/content-types/rte-combinations', '/pricing/order', '/privacy', '/imprint'] as $slug) {
            $connection->insert('pages', ['pid' => 1, 'title' => $slug, 'slug' => $slug, 'doktype' => 1]);
        }
        $command = $this->get(SeedStyleguidePagesCommand::class);
        $tester = new CommandTester($command);
        $options = ['--parent' => '1', '--pages' => ShowcaseVisualEditorInlinePage::SLUG];
        self::assertSame(Command::SUCCESS, $tester->execute([...$options, '--dry-run' => true]), $tester->getDisplay());
        self::assertSame(6, $connection->count('*', 'pages', []));
        $old = [];
        for ($run = 0; $run < 2; ++$run) {
            self::assertSame(Command::SUCCESS, $tester->execute($options), $tester->getDisplay());
            $page = $connection->executeQuery('SELECT * FROM pages WHERE slug = ? AND deleted = 0', [ShowcaseVisualEditorInlinePage::SLUG])->fetchAssociative();
            self::assertIsArray($page);
            self::assertSame($parentUid, (int)$page['pid']);
            self::assertSame(1, (int)$page['nav_hide']);
            self::assertSame(1, (int)$page['no_search']);
            $uid = (int)$page['uid'];
            $content = $connection->executeQuery('SELECT * FROM tt_content WHERE pid = ? AND deleted = 0 ORDER BY sorting', [$uid])->fetchAllAssociative();
            self::assertSame(array_column(ShowcaseVisualEditorInlinePage::content(), 'ctype'), array_column($content, 'CType'));
            $this->assertPreviousChildrenRemoved($old);
            $old = [];
            foreach ($GLOBALS['TCA'] as $table => $tca) {
                if (!isset($tca['columns']['foreign_table_parent_uid'])) {
                    continue;
                }
                $children = $connection->executeQuery('SELECT * FROM ' . $connection->quoteIdentifier($table) . ' WHERE pid = ? AND deleted = 0', [$uid])->fetchAllAssociative();
                foreach ($children as $child) {
                    $old[$table][] = (int)$child['uid'];
                    $relations = [];
                    foreach ($GLOBALS['TCA'] as $owner => $ownerTca) {
                        foreach ($ownerTca['columns'] as $field => $column) {
                            $config = $column['config'] ?? [];
                            if (($config['type'] ?? '') === 'inline' && ($config['foreign_table'] ?? '') === $table) {
                                $relations[] = [$owner, $field, $config];
                            }
                        }
                    }
                    $matches = 0;
                    foreach ($relations as [$owner, $field, $config]) {
                        if (isset($config['foreign_table_field']) && $child[$config['foreign_table_field']] !== $owner) {
                            continue;
                        }
                        foreach ($config['foreign_match_fields'] ?? [] as $key => $value) {
                            if ($child[$key] !== $value) {
                                continue 2;
                            }
                        }
                        $matches += $connection->count('*', $owner, ['uid' => $child[$config['foreign_field']], 'deleted' => 0, 'pid' => $uid]);
                    }
                    self::assertGreaterThan(0, $matches, "$table has an orphan or incorrect shared owner");
                }
            }
            self::assertCount(6, $old['comparison_table_tier_values']);
            self::assertCount(9, $old['data_table_cells']);
            self::assertCount(3, $old['pricing_three_tier_plans']);
        }
        self::assertSame(1, $connection->count('*', 'tt_content', ['uid' => $foreignUid, 'header' => 'Keep me', 'deleted' => 0]));
        self::assertSame(7, $connection->count('*', 'pages', ['deleted' => 0]));
    }
    /**
     * The Visual Editor finds collection entries on the page through the
     * f:mark.item row markers: in edit mode every visible child of every
     * element on the showcase page is marked as one row (data-ve-item) under
     * its parent's inline column, including the nested data table cells and
     * the gallery thumbnails; for a visitor the page carries no data-ve-*
     * attribute at all.
     */
    public function testEditModeMarksEveryVisibleCollectionEntryAndVisitorsSeeNoMarkers(): void
    {
        $pageUid = $this->seedShowcaseWithPreviewSite();
        $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
        self::assertNotContains('Webconsulting\\Desiderio\\ViewHelpers\\RowMarkerFallback', $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['f'], 'with Visual Editor Enhancements loaded its f:mark.item is used');

        $elements = $connection->executeQuery('SELECT uid, CType FROM tt_content WHERE pid = ? AND deleted = 0 AND hidden = 0 ORDER BY sorting', [$pageUid])->fetchAllAssociative();
        $checked = [];
        foreach ($elements as $element) {
            $uid = (int)$element['uid'];
            $cType = (string)$element['CType'];
            $expected = $this->visibleChildren('tt_content', $uid);
            $anonymous = $this->renderElement($uid, false);
            self::assertStringContainsString('desiderio-element-preview', $anonymous, $cType . ' renders through the preview page type');
            self::assertStringNotContainsString('data-ve-', $anonymous, $cType . ': no Visual Editor attribute reaches visitors');
            if ($expected === []) {
                continue;
            }
            $edit = $this->renderElement($uid, true);
            preg_match_all('/data-ve-item="([a-z0-9_]+:\d+)" data-ve-item-live="\d+" data-ve-collection="([a-z0-9_]+)"/', $edit, $matches, PREG_SET_ORDER);
            $rows = [];
            foreach ($matches as [, $item, $collection]) {
                $rows[$collection][$item] = $item;
            }
            foreach ($rows as &$items) {
                $items = array_values($items);
                sort($items);
            }
            unset($items);
            ksort($rows);
            self::assertSame($expected, $rows, $cType . ': one marked row per visible child of every collection');
            $checked[$cType] = array_sum(array_map(count(...), $rows));
        }
        // The elements the plan names, with the seeded entry counts.
        foreach (['desiderio_featurecards', 'desiderio_productspecs', 'desiderio_definitionlist', 'desiderio_datatable', 'desiderio_gallery'] as $cType) {
            self::assertGreaterThan(0, $checked[$cType] ?? 0, $cType . ' was rendered with marked rows');
        }
        self::assertSame(3 + 3 + 9, $checked['desiderio_datatable'], 'data table: 3 column definitions, 3 rows and their 9 nested cells');
    }

    /** @param array<string, list<int>> $old */
    private function assertPreviousChildrenRemoved(array $old): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tt_content');
        foreach ($old as $table => $ids) {
            foreach ($ids as $id) {
                self::assertSame(0, $connection->count('*', $table, ['uid' => $id]), "$table:$id must be removed on reseed");
            }
        }
    }
}
