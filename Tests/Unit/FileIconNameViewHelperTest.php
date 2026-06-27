<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Icon\IconRegistry;
use Webconsulting\Desiderio\ViewHelpers\FileIconNameViewHelper;

final class FileIconNameViewHelperTest extends TestCase
{
    public function testMapsCommonExtensionsToRegisteredSemanticIcons(): void
    {
        $expected = [
            'PDF' => 'file-pdf',
            '.docx' => 'file-word',
            'xlsx' => 'file-spreadsheet',
            'ppt' => 'file-presentation',
            'jpg' => 'file-image',
            'mp4' => 'file-video',
            'mp3' => 'file-audio',
            'zip' => 'file-archive',
            'json' => 'file-code',
            'txt' => 'file-text',
            'unknown' => 'file',
        ];

        foreach ($expected as $extension => $iconName) {
            self::assertSame($iconName, FileIconNameViewHelper::iconNameForExtension($extension));
            self::assertContains($iconName, IconRegistry::keys());
        }
    }

    public function testDetectsImageExtensionsForThumbnailMode(): void
    {
        self::assertTrue(FileIconNameViewHelper::isImageExtension('webp'));
        self::assertTrue(FileIconNameViewHelper::isImageExtension('.SVG'));
        self::assertFalse(FileIconNameViewHelper::isImageExtension('pdf'));
    }
}
