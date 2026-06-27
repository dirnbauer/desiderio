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
        self::assertStringContainsString('<f:switch expression="{uploadsType}">', $partial);
        self::assertStringContainsString('<f:case value="0"></f:case>', $partial);
        self::assertStringContainsString('<f:case value="2">', $partial);
        self::assertStringContainsString('di:fileIconName(extension: file.extension)', $partial);
        self::assertStringContainsString('di:fileIsImage(extension: file.extension)', $partial);
    }

    public function testFileListPartialUsesShadcnAttachmentSlots(): void
    {
        $partial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/FileList.fluid.html');

        self::assertStringContainsString('data-slot="attachment-group"', $partial);
        self::assertStringContainsString('data-slot="attachment"', $partial);
        self::assertStringContainsString('data-slot="attachment-media"', $partial);
        self::assertStringContainsString('data-slot="attachment-content"', $partial);
        self::assertStringContainsString('data-slot="attachment-title"', $partial);
        self::assertStringContainsString('data-slot="attachment-description"', $partial);
        self::assertStringContainsString('data-slot="attachment-metadata"', $partial);
        self::assertStringContainsString('data-slot="attachment-actions"', $partial);
        self::assertStringContainsString('data-slot="attachment-action"', $partial);
        self::assertStringContainsString('data-slot="attachment-trigger"', $partial);
        self::assertStringNotContainsString('dc:molecule.card', $partial);
    }

    public function testFileListCssStylesAttachmentGroupInsteadOfCards(): void
    {
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Css/desiderio/10-fluid-styled-content.css');

        self::assertStringContainsString('.ce-fsc-files__list', $css);
        self::assertStringContainsString('.ce-fsc-files__item', $css);
        self::assertStringContainsString('.ce-fsc-files__media--thumbnail', $css);
        self::assertStringContainsString('.ce-fsc-files__item--type-0', $css);
        self::assertStringContainsString('.ce-fsc-files__trigger', $css);
        self::assertStringNotContainsString('.ce-fsc-files__content', $css);
    }
}
