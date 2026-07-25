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

    public function testUpserterUsesTheLibraryFixtureForDesiderioButTheNativeFixtureForCore(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/LibraryElementUpserter.php');

        // Desiderio Content Blocks seed from library.json, never from the
        // promotional fixture.json. Anything library.json omits is completed by
        // the neutral demo value generator.
        self::assertStringContainsString("\$element['libraryFixture'] ?? [],\n                \$sorting", $source);
        // The old behaviour passed a hardcoded empty payload here, which is
        // what made every element share one vocabulary pool.
        self::assertStringNotContainsString("                [],\n                \$sorting", $source);
        self::assertStringContainsString('library.json, NOT fixture.json', $source);

        // Native core elements have no registry definition; their branch DOES
        // pass the manifest fixture straight into the native tt_content columns.
        self::assertStringContainsString('CoreContentElements::HOST', $source);
        self::assertStringContainsString("\$element['fixture'],\n                \$sorting", $source);
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
}
