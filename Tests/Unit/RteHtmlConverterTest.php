<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Rte\RteHtmlConverter;

final class RteHtmlConverterTest extends TestCase
{
    public static function plainTextProvider(): array
    {
        return [
            'single paragraph' => [
                'Per-page theme presets have landed.',
                '<p>Per-page theme presets have landed.</p>',
            ],
            'two paragraphs from blank line' => [
                "First block.\n\nSecond block.",
                "<p>First block.</p>\n<p>Second block.</p>",
            ],
            'single newline becomes br' => [
                "Line one\nLine two",
                '<p>Line one<br />Line two</p>',
            ],
            'special characters are escaped, quotes stay literal' => [
                'Tokens & "contrast" <safe> it\'s',
                '<p>Tokens &amp; "contrast" &lt;safe&gt; it\'s</p>',
            ],
            'windows line endings normalized' => [
                "A\r\n\r\nB",
                "<p>A</p>\n<p>B</p>",
            ],
            'empty value stays empty' => ['', ''],
            'whitespace-only stays empty' => ["  \n  ", ''],
        ];
    }

    #[DataProvider('plainTextProvider')]
    public function testConvertWrapsPlainTextAsRteHtml(string $input, string $expected): void
    {
        self::assertSame($expected, RteHtmlConverter::convert($input));
    }

    public function testLooksLikeHtmlDetectsMarkup(): void
    {
        self::assertTrue(RteHtmlConverter::looksLikeHtml('<p>Already converted.</p>'));
        self::assertTrue(RteHtmlConverter::looksLikeHtml('Mixed <strong>markup</strong> inside.'));
        self::assertFalse(RteHtmlConverter::looksLikeHtml('Plain text, even with a < b comparison.'));
        self::assertFalse(RteHtmlConverter::looksLikeHtml("Multi\nline plain text"));
    }
}
