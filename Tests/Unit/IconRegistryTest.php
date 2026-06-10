<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class IconRegistryTest extends TestCase
{
    public function testRegistrySupportsConfiguredLibrariesAndPresetDefaults(): void
    {
        self::assertSame(['lucide', 'tabler', 'hugeicons', 'phosphor', 'remixicon'], IconRegistry::supportedLibraries());
        self::assertSame('lucide', IconRegistry::libraryForPreset('b0'));
        self::assertSame('phosphor', IconRegistry::libraryForPreset('b4hb38Fyj'));
        self::assertSame('phosphor', IconRegistry::libraryForPreset('b3IWPgRwnI'));
        self::assertSame('tabler', IconRegistry::libraryForPreset('b6G5977cw'));
        self::assertSame('lucide', IconRegistry::libraryForPreset('b27GcrRo'));
        self::assertSame('hugeicons', IconRegistry::libraryForPreset('ember'));
        self::assertSame('phosphor', IconRegistry::libraryForPreset('forest'));
        self::assertSame('remixicon', IconRegistry::libraryForPreset('blossom'));
        self::assertSame('tabler', IconRegistry::libraryForPreset('citrus'));
        self::assertNull(IconRegistry::libraryForPreset('custom'));
    }

    public function testEveryLibraryResolvesToABundledIconFontStylesheet(): void
    {
        $publicRoot = dirname(__DIR__, 2) . '/Resources/Public';

        foreach (IconRegistry::supportedLibraries() as $library) {
            $stylesheet = IconRegistry::fontStylesheet($library);
            self::assertStringStartsWith('EXT:desiderio/Resources/Public/IconFonts/', $stylesheet);

            $relativePath = substr($stylesheet, strlen('EXT:desiderio/Resources/Public'));
            $cssFile = $publicRoot . $relativePath;
            self::assertFileExists($cssFile, $library . ' icon font stylesheet must ship with the package');

            $css = (string) file_get_contents($cssFile);
            self::assertStringNotContainsString('url("http', $css, $library . ' stylesheet must not load external resources');
            self::assertStringNotContainsString("url('http", $css, $library . ' stylesheet must not load external resources');

            $woff2 = preg_replace('/\.css$/', '.woff2', $cssFile);
            self::assertFileExists((string) $woff2, $library . ' woff2 font must ship with the package');
        }

        // The HugeIcons license forbids redistributing their icon font, so it
        // must fall back to the bundled Lucide font instead of a CDN.
        self::assertSame(IconRegistry::fontStylesheet('lucide'), IconRegistry::fontStylesheet('hugeicons'));
        self::assertSame(IconRegistry::fontStylesheet(IconRegistry::DEFAULT_LIBRARY), IconRegistry::fontStylesheet('unknown'));
    }

    public function testBundledIconFontsShipTheirLicenses(): void
    {
        $iconFontRoot = dirname(__DIR__, 2) . '/Resources/Public/IconFonts';

        foreach (['lucide', 'tabler', 'phosphor', 'remixicon'] as $library) {
            $licenses = glob($iconFontRoot . '/' . $library . '/LICENSE-*.txt');
            self::assertNotEmpty($licenses === false ? [] : $licenses, $library . ' must ship its license file');
        }

        self::assertDirectoryDoesNotExist(
            $iconFontRoot . '/hugeicons',
            'The HugeIcons font must not be bundled — its license forbids redistribution'
        );
    }

    public function testPageLayoutLoadsNoExternalIconFontCdn(): void
    {
        $layout = (string) file_get_contents(
            dirname(__DIR__, 2) . '/Resources/Private/Templates/Layouts/Pages/Default.fluid.html'
        );

        self::assertStringNotContainsString('cdn.hugeicons.com', $layout);
        self::assertStringContainsString('di:iconFont', $layout);
    }

    public function testRegistryNormalizesAliasesWithoutChangingStoredMeaning(): void
    {
        self::assertSame('sparkles', IconRegistry::normalizeKey('default'));
        self::assertSame('x-circle', IconRegistry::normalizeKey('destructive'));
        self::assertSame('check-circle', IconRegistry::normalizeKey('success'));
        self::assertSame('alert-triangle', IconRegistry::normalizeKey('warning'));
        self::assertSame('chevron-down', IconRegistry::normalizeKey('chevron-down'));
        self::assertSame('sparkles', IconRegistry::normalizeKey('unknown-icon'));
    }

    public function testEverySelectableIconHasRenderablePaths(): void
    {
        foreach (IconRegistry::selectItems() as $item) {
            if ($item['value'] === 'none') {
                continue;
            }

            foreach (IconRegistry::supportedLibraries() as $library) {
                self::assertNotSame('', IconRegistry::paths($item['value'], $library), $item['value'] . ' must render for ' . $library);
            }
        }
    }
}
