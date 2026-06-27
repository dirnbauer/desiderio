<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class UploadsFileListTemplateTest extends TestCase
{
    public function testUploadsTemplatePassesCoreDisplaySettingsToFileListPartial(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Uploads.fluid.html');

        self::assertStringContainsString('displayFileSize: record.filelink_size', $template);
        self::assertStringContainsString('displayDescription: record.uploads_description', $template);
        self::assertStringContainsString('uploadsType: record.uploads_type', $template);
    }

    public function testFileListPartialRespectsCoreDisplaySettings(): void
    {
        $partial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/FileList.fluid.html');

        self::assertStringContainsString('<f:argument name="displayFileSize" type="boolean" optional="true" default="false"/>', $partial);
        self::assertStringContainsString('<f:argument name="displayDescription" type="boolean" optional="true" default="false"/>', $partial);
        self::assertStringContainsString('<f:argument name="uploadsType" type="integer" optional="true" default="1"/>', $partial);
        self::assertStringContainsString('<f:if condition="{displayDescription}">', $partial);
        self::assertStringContainsString('<f:if condition="{displayFileSize}">', $partial);
        self::assertStringContainsString('<f:if condition="{uploadsType} != 0">', $partial);
        self::assertStringContainsString('<f:if condition="{uploadsType} == 2">', $partial);
        self::assertStringContainsString('di:fileIconName(extension: file.extension)', $partial);
        self::assertStringContainsString('di:fileIsImage(extension: file.extension)', $partial);
    }
}
