<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class IconRegistryTest extends TestCase
{
    public function testRegistrySupportsConfiguredLibrariesAndPresetDefaults(): void
    {
        self::assertSame(['lucide', 'tabler', 'phosphor'], IconRegistry::supportedLibraries());
        self::assertSame('lucide', IconRegistry::libraryForPreset('b0'));
        self::assertSame('phosphor', IconRegistry::libraryForPreset('b4hb38Fyj'));
        self::assertSame('phosphor', IconRegistry::libraryForPreset('b3IWPgRwnI'));
        self::assertSame('tabler', IconRegistry::libraryForPreset('b6G5977cw'));
        self::assertNull(IconRegistry::libraryForPreset('custom'));
    }

    public function testRegistryNormalizesAliasesWithoutChangingStoredMeaning(): void
    {
        self::assertSame('sparkles', IconRegistry::normalizeKey('default'));
        self::assertSame('x-circle', IconRegistry::normalizeKey('destructive'));
        self::assertSame('check-circle', IconRegistry::normalizeKey('success'));
        self::assertSame('alert-triangle', IconRegistry::normalizeKey('warning'));
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
