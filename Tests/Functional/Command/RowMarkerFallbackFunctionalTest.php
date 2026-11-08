<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Desiderio without Visual Editor Enhancements: the templates' f:mark.item
 * resolves to desiderio's no-output stand-in (ext_localconf.php), so marked
 * elements render for visitors and in edit mode, just without row markers.
 */
final class RowMarkerFallbackFunctionalTest extends FunctionalTestCase
{
    use ShowcasePreviewRendering;

    protected array $coreExtensionsToLoad = ['fluid_styled_content', 'form', 'workspaces', 'rte_ckeditor'];
    protected array $testExtensionsToLoad = ['friendsoftypo3/content-blocks', 'friendsoftypo3/visual-editor', 'praetorius/vite-asset-collector', 'webconsulting/desiderio'];

    protected array $configurationToUseInTestInstance = [
        'FE' => ['cacheHash' => ['enforceValidation' => false]],
        'BE' => ['lockIP' => 0, 'lockIPv6' => 0],
        'EXTENSIONS' => ['vite_asset_collector' => ['useDevServer' => '1']],
    ];

    public function testMarkedTemplatesRenderWithoutVisualEditorEnhancements(): void
    {
        self::assertContains('Webconsulting\\Desiderio\\ViewHelpers\\RowMarkerFallback', $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['f']);
        $pageUid = $this->seedShowcaseWithPreviewSite();
        $elements = $this->getConnectionPool()->getConnectionForTable('tt_content')
            ->executeQuery('SELECT uid, CType FROM tt_content WHERE pid = ? AND deleted = 0 AND CType IN (?, ?, ?)', [$pageUid, 'desiderio_featurecards', 'desiderio_datatable', 'desiderio_tabs'])
            ->fetchAllAssociative();
        self::assertCount(3, $elements);
        foreach ($elements as $element) {
            self::assertNotSame([], $this->visibleChildren('tt_content', (int)$element['uid']));
            foreach ([false, true] as $editMode) {
                $html = $this->renderElement((int)$element['uid'], $editMode);
                self::assertStringContainsString('desiderio-element-preview', $html, $element['CType']);
                self::assertStringNotContainsString('data-ve-item', $html, $element['CType'] . ($editMode ? ' (edit mode)' : ''));
            }
        }
    }
}
