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

    public function testCatalogPrefersALocalisedLibraryPayloadAndFallsBackToTheSourceLanguage(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Library/ElementCatalog.php');

        self::assertStringContainsString("'/library.' . \$locale . '.json'", $source);
        self::assertStringContainsString("'/library.json'", $source);
        // The fallback must be unconditional: a language that only translates
        // some elements still seeds a complete folder.
        self::assertStringContainsString("if (\$libraryFixture === []) {", $source);
    }

    public function testSeedCommandExposesTheLocaleOption(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/SeedElementLibraryCommand.php');

        self::assertStringContainsString("addOption('locale'", $source);
        self::assertStringContainsString('getElements($locale)', $source);
    }

    public function testDefaultSeedExcludesEveryPlayableVideoDemoType(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/SeedElementLibraryCommand.php');

        self::assertStringContainsString("str_contains(\$cType, 'video')", $source);
        self::assertStringContainsString("str_contains(\$cType, 'featuredemo')", $source);
        self::assertStringContainsString("getOption('include-video')", $source);
    }
}
