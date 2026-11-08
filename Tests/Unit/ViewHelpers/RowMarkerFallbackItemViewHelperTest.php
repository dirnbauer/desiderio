<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\ViewHelpers\RowMarkerFallback\Mark\ItemViewHelper;

/**
 * Without Visual Editor Enhancements the templates' f:mark.item resolves to
 * this stand-in, which renders what the real ViewHelper renders outside edit
 * mode: nothing.
 */
final class RowMarkerFallbackItemViewHelperTest extends TestCase
{
    public function testRendersNothingInEitherFormat(): void
    {
        $viewHelper = new ItemViewHelper();
        $viewHelper->initializeArguments();
        $arguments = $viewHelper->prepareArguments();
        self::assertSame(['record', 'collection', 'format'], array_keys($arguments));
        self::assertTrue($arguments['collection']->isRequired());
        self::assertSame('record', $viewHelper->getContentArgumentName());

        $viewHelper->setArguments(['record' => null, 'collection' => 'items', 'format' => 'attributes']);
        self::assertSame('', $viewHelper->render());
        $viewHelper->setArguments(['record' => null, 'collection' => 'items', 'format' => 'array']);
        self::assertSame([], $viewHelper->render());
    }

    public function testIsRegisteredOnlyWhenVisualEditorEnhancementsCannotServeTheMarker(): void
    {
        $localconf = (string)file_get_contents(__DIR__ . '/../../../ext_localconf.php');
        self::assertStringContainsString(
            "if (!ExtensionManagementUtility::isLoaded('visual_editor_enhancements')\n    || !class_exists('Webconsulting\\\\VisualEditorEnhancements\\\\ViewHelpers\\\\Mark\\\\ItemViewHelper')\n) {\n    \$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['f'][] = 'Webconsulting\\\\Desiderio\\\\ViewHelpers\\\\RowMarkerFallback';",
            $localconf,
        );
    }
}
