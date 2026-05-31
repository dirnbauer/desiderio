<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\ViewHelpers\SearchSnippetViewHelper;

final class SearchSnippetViewHelperTest extends TestCase
{
    public function testSnippetIsCroppedToMaximumVisibleLengthWithEllipsis(): void
    {
        $html = $this->renderSnippet(str_repeat('Search result text ', 20), '*');
        $visibleText = $this->visibleText($html);

        self::assertLessThanOrEqual(200, mb_strlen($visibleText, 'UTF-8'));
        self::assertStringEndsWith('...', $visibleText);
        self::assertStringNotContainsString('<mark', $html);
    }

    public function testSnippetHighlightsQueryTermsAndEscapesOriginalText(): void
    {
        $html = $this->renderSnippet(
            str_repeat('Intro copy ', 30) . '<script>alert("x")</script> Dashboard result text with more context.',
            'dashboard'
        );
        $visibleText = $this->visibleText($html);

        self::assertLessThanOrEqual(200, mb_strlen($visibleText, 'UTF-8'));
        self::assertStringStartsWith('...', $visibleText);
        self::assertStringContainsString('<mark class="results-highlight">Dashboard</mark>', $html);
        self::assertStringNotContainsString('<script>', $html);
    }

    public function testSnippetHighlightsTitleWithoutCroppingWhenShortEnough(): void
    {
        $html = $this->renderSnippet('Desiderio Navigation & Wayfinding', 'desiderio navigation', 140);

        self::assertSame(
            '<mark class="results-highlight">Desiderio</mark> <mark class="results-highlight">Navigation</mark> &amp; Wayfinding',
            $html
        );
    }

    private function renderSnippet(string $text, string $query, int $maxCharacters = 200): string
    {
        $viewHelper = new SearchSnippetViewHelper();
        $viewHelper->setArguments([
            'text' => $text,
            'query' => $query,
            'maxCharacters' => $maxCharacters,
            'append' => '...',
            'highlightClass' => 'results-highlight',
        ]);

        return $viewHelper->render();
    }

    private function visibleText(string $html): string
    {
        return html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
