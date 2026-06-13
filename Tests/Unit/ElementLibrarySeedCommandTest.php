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

    public function testUpserterIgnoresPromotionalFixturesForDesiderioElements(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/LibraryElementUpserter.php');

        // The desiderio branch must pass an empty fixture so the neutral
        // generator fills every field instead of the fixture.json copy.
        self::assertStringNotContainsString("\$element['fixture'],\n                \$sorting", $source);
        self::assertStringContainsString('completes every field from', $source);
    }
}
