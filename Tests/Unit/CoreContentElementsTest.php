<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Library\CoreContentElements;

/**
 * Guards that every core content element promoted by Desiderio ships the full
 * set of assets the picker and wizard rely on: a custom icon, a registered icon
 * identifier, and bilingual title/description/short/keyword catalog entries.
 */
final class CoreContentElementsTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';
    private const ALLOWED_GROUPS = ['content', 'navigation', 'conversion'];

    public function testManifestEntriesAreWellFormedAndUnique(): void
    {
        $cTypes = [];
        foreach (CoreContentElements::all() as $element) {
            self::assertNotSame('', $element['cType']);
            self::assertNotSame('', $element['name']);
            self::assertNotSame('', $element['iconSlug']);
            self::assertContains($element['group'], self::ALLOWED_GROUPS, $element['cType'] . ' uses an unknown picker group');
            self::assertIsArray($element['fixture']);
            $cTypes[] = $element['cType'];
        }

        self::assertSame($cTypes, array_unique($cTypes), 'Core CTypes must be unique');
        self::assertGreaterThanOrEqual(24, count($cTypes), 'All in-scope legacy elements must be present');
    }

    public function testEveryCoreElementShipsACustomIcon(): void
    {
        $iconsSource = (string) file_get_contents(self::ROOT . '/Configuration/Icons.php');

        foreach (CoreContentElements::all() as $element) {
            $slug = $element['iconSlug'];
            $iconFile = self::ROOT . '/Resources/Public/Icons/ContentElements/core-' . $slug . '.svg';
            self::assertFileExists($iconFile, $element['cType'] . ' must ship a custom icon');

            $svg = (string) file_get_contents($iconFile);
            self::assertStringContainsString('viewBox="0 0 16 16"', $svg, $slug . ' icon must use the 16x16 viewBox');
            self::assertStringContainsString('currentColor', $svg, $slug . ' icon must use currentColor');
            self::assertStringNotContainsString('prefers-color-scheme', $svg, $slug . ' must not depend on prefers-color-scheme');

            self::assertStringContainsString("'" . $slug . "'", $iconsSource, 'desiderio-ce-' . $slug . ' must be registered in Icons.php');
        }
    }

    public function testEveryCoreElementHasBilingualCatalogText(): void
    {
        $files = [
            'core' => $this->load('library_core.xlf'),
            'core_de' => $this->load('de.library_core.xlf'),
            'short' => $this->load('library_short.xlf'),
            'short_de' => $this->load('de.library_short.xlf'),
            'keywords' => $this->load('library_keywords.xlf'),
            'keywords_de' => $this->load('de.library_keywords.xlf'),
        ];

        foreach (CoreContentElements::all() as $element) {
            $cType = $element['cType'];

            self::assertStringContainsString('id="' . $cType . '.title"', $files['core'], $cType . ' missing EN title');
            self::assertStringContainsString('id="' . $cType . '.description"', $files['core'], $cType . ' missing EN description');
            self::assertStringContainsString('id="' . $cType . '.title"', $files['core_de'], $cType . ' missing DE title');
            self::assertStringContainsString('id="' . $cType . '.description"', $files['core_de'], $cType . ' missing DE description');

            self::assertStringContainsString('id="' . $cType . '"', $files['short'], $cType . ' missing EN short blurb');
            self::assertStringContainsString('id="' . $cType . '"', $files['short_de'], $cType . ' missing DE short blurb');

            self::assertStringContainsString('id="' . $cType . '"', $files['keywords'], $cType . ' missing EN keywords');
            self::assertStringContainsString('id="' . $cType . '"', $files['keywords_de'], $cType . ' missing DE keywords');
        }
    }

    public function testCoreWizardIconsAreAppliedAfterTcaCompilation(): void
    {
        // The icon/description override must run on AfterTcaCompilationEvent (after
        // ALL extensions' TCA), not in Overrides/tt_content.php — otherwise CType
        // items registered later than Desiderio (e.g. felogin's "login") are missed.
        $listener = (string) file_get_contents(self::ROOT . '/Classes/EventListener/CoreContentElementIcons.php');
        self::assertStringContainsString('AfterTcaCompilationEvent', $listener);
        self::assertStringContainsString('CoreContentElements::all()', $listener);
        self::assertStringContainsString("'desiderio-ce-'", $listener);
        self::assertStringContainsString('library_core.xlf:', $listener);
        self::assertStringContainsString('typeicon_classes', $listener);

        // ...and must NOT be duplicated in the TCA/Overrides pass.
        $tca = (string) file_get_contents(self::ROOT . '/Configuration/TCA/Overrides/tt_content.php');
        self::assertStringNotContainsString("typeicon_classes", $tca);
    }

    public function testPluginsAreGatedAndNativeElementsAreNot(): void
    {
        $gateByCType = [];
        foreach (CoreContentElements::all() as $element) {
            $gateByCType[$element['cType']] = $element['gateExtension'];
        }

        // Plugins must declare their gate extension so they never surface when
        // the extension is absent (and so styleguideElements() excludes them).
        self::assertSame('form', $gateByCType['form_formframework']);
        self::assertSame('felogin', $gateByCType['felogin_login']);
        self::assertSame('powermail', $gateByCType['powermail_pi1']);

        // Native frontend elements are always available (no gate).
        foreach (['header', 'text', 'bullets', 'table', 'uploads', 'div', 'menu_pages'] as $native) {
            self::assertNull($gateByCType[$native], $native . ' is a core element and must not be gated');
        }
    }

    private function load(string $file): string
    {
        return (string) file_get_contents(self::ROOT . '/Resources/Private/Language/' . $file);
    }
}
