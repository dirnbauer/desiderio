<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Icon\IconRegistry;
use Webconsulting\Desiderio\ViewHelpers\IconViewHelper;

final class IconViewHelperTest extends TestCase
{
    #[DataProvider('singleLibraryProvider')]
    public function testExplicitLibraryRendersOneIconWithoutFrontendThemeCss(string $library, string $expectedLibrary): void
    {
        $viewHelper = new IconViewHelper();
        $viewHelper->setArguments(['name' => 'book-open', 'library' => $library]);

        $output = $viewHelper->render();

        self::assertSame(1, substr_count($output, '<svg '));
        self::assertStringContainsString('data-icon-library="' . $expectedLibrary . '"', $output);
        self::assertStringContainsString(IconRegistry::paths('book-open', $expectedLibrary), $output);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function singleLibraryProvider(): iterable
    {
        foreach (IconRegistry::supportedLibraries() as $library) {
            yield $library => [$library, $library];
        }
        yield 'normalized' => [' Lucide ', 'lucide'];
        yield 'unknown falls back to default' => ['unknown', IconRegistry::DEFAULT_LIBRARY];
    }

    public function testOmittedLibraryPreservesFrontendThemeSwitching(): void
    {
        $viewHelper = new IconViewHelper();
        $viewHelper->setArguments(['name' => 'book-open']);

        $output = $viewHelper->render();

        self::assertSame(count(IconRegistry::supportedLibraries()), substr_count($output, '<svg '));
        foreach (IconRegistry::supportedLibraries() as $library) {
            self::assertStringContainsString('data-icon-library="' . $library . '"', $output);
        }
    }

    public function testNoIconRemainsEmptyWhenLibraryIsExplicit(): void
    {
        $viewHelper = new IconViewHelper();
        $viewHelper->setArguments(['name' => 'none', 'library' => 'lucide']);

        self::assertSame('', $viewHelper->render());
    }
}
