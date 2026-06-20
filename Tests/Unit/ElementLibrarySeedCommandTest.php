<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ElementLibrarySeedCommandTest extends TestCase
{
    public function testCommandSeedsTheLibraryWithTheNeutralValueGenerator(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/SeedElementLibraryCommand.php');

        self::assertStringContainsString("name: 'desiderio:library:seed'", $source);
        // The picker preview must use neutral demo content, not the promotional
        // styleguide vocabulary.
        self::assertStringContainsString('new ElementLibraryValueGenerator()', $source);
        self::assertStringNotContainsString('new StyleguideDemoValueGenerator()', $source);
    }

    public function testUpserterUsesEmptyFixtureForDesiderioButNativeFixtureForCore(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/LibraryElementUpserter.php');

        // Desiderio Content Blocks: the neutral generator fills every field, so
        // the branch passes an EMPTY fixture instead of the promotional one.
        self::assertStringContainsString('completes every field from', $source);
        self::assertStringContainsString("[],\n                \$sorting", $source);

        // Native core elements have no registry definition; their branch DOES
        // pass the manifest fixture straight into the native tt_content columns.
        self::assertStringContainsString('CoreContentElements::HOST', $source);
        self::assertStringContainsString("\$element['fixture'],\n                \$sorting", $source);
    }
}
