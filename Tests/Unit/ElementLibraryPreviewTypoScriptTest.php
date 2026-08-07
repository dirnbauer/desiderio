<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ElementLibraryPreviewTypoScriptTest extends TestCase
{
    #[Test]
    public function previewFluidTemplateUsesAResolvableTemplateFile(): void
    {
        $typoScript = (string) file_get_contents(
            __DIR__ . '/../../Configuration/Sets/Desiderio/setup.typoscript',
        );

        self::assertStringContainsString(
            'file = EXT:desiderio/Resources/Private/Templates/Pages/ElementPreview.fluid.html',
            $typoScript,
        );
        self::assertStringNotContainsString(
            'template = EXT:desiderio/Resources/Private/Templates/Pages/ElementPreview.fluid.html',
            $typoScript,
        );
    }
}
