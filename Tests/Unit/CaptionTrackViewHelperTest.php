<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\ViewHelpers\CaptionTrackViewHelper;

final class CaptionTrackViewHelperTest extends TestCase
{
    public function testTrackLanguageAndDefaultFollowTheFilenameAndPageLanguage(): void
    {
        self::assertSame(['language' => 'de', 'label' => 'Deutsch', 'selected' => true], CaptionTrackViewHelper::metadata('visual-editor-ab1234.de.vtt', 'de'));
        self::assertSame(['language' => 'en', 'label' => 'English', 'selected' => false], CaptionTrackViewHelper::metadata('visual-editor-ab1234.en.vtt', 'de'));
        self::assertSame(['language' => 'zh', 'label' => '中文', 'selected' => true], CaptionTrackViewHelper::metadata('visual-editor-ab1234.zh.vtt', 'zh'));
        self::assertSame(['language' => 'hu', 'label' => 'Magyar', 'selected' => true], CaptionTrackViewHelper::metadata('visual-editor-ab1234.hu.vtt', 'hu'));
    }

    public function testExistingTracksWithoutLanguageSuffixStayEnglish(): void
    {
        self::assertSame(['language' => 'en', 'label' => 'English', 'selected' => true], CaptionTrackViewHelper::metadata('legacy-captions.vtt'));
    }
}
