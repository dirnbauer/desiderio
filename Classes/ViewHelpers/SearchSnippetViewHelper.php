<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class SearchSnippetViewHelper extends AbstractViewHelper
{
    /**
     * Upper bound for highlighted terms, keeps the alternation pattern in
     * termPattern() small even for hostile multi-term search queries.
     */
    private const MAX_TERMS = 10;

    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('text', 'mixed', 'Text to crop and highlight.', false, null);
        $this->registerArgument('query', 'string', 'Search query used for highlighting.', false, '');
        $this->registerArgument('maxCharacters', 'int', 'Maximum visible characters, including ellipses.', false, 200);
        $this->registerArgument('append', 'string', 'Ellipsis marker used when text is cropped.', false, '...');
        $this->registerArgument('highlightClass', 'string', 'CSS class for highlighted search terms.', false, 'results-highlight');
    }

    public function render(): string
    {
        $text = $this->normalizeText($this->arguments['text'] ?? $this->renderChildren());
        if ($text === '') {
            return '';
        }

        $query = is_string($this->arguments['query'] ?? null) ? $this->arguments['query'] : '';
        $terms = $this->extractSearchTerms($query);
        $maxCharacters = max(1, $this->intArgument('maxCharacters', 200));
        $append = $this->stringArgument('append', '...');

        $snippet = $this->cropText($text, $terms, $maxCharacters, $append);

        return $this->highlightTerms(
            $snippet,
            $terms,
            $this->stringArgument('highlightClass', 'results-highlight')
        );
    }

    private function stringArgument(string $name, string $default = ''): string
    {
        $value = $this->arguments[$name] ?? null;

        return is_scalar($value) || $value instanceof \Stringable ? (string)$value : $default;
    }

    private function intArgument(string $name, int $default): int
    {
        $value = $this->arguments[$name] ?? null;

        return is_numeric($value) ? (int)$value : $default;
    }

    private function normalizeText(mixed $text): string
    {
        if (!is_scalar($text) && !$text instanceof \Stringable) {
            return '';
        }

        $normalized = html_entity_decode(strip_tags((string)$text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = preg_replace('/\s+/u', ' ', $normalized) ?? '';

        return trim($normalized);
    }

    /**
     * @return list<string>
     */
    private function extractSearchTerms(string $query): array
    {
        preg_match_all('/[\p{L}\p{N}_-]+/u', html_entity_decode($query, ENT_QUOTES | ENT_HTML5, 'UTF-8'), $matches);

        $terms = [];
        foreach ($matches[0] as $term) {
            $term = trim($term, "*? \t\n\r\0\x0B");
            if ($term === '') {
                continue;
            }

            $lowerTerm = mb_strtolower($term, 'UTF-8');
            if (in_array($lowerTerm, ['and', 'or', 'not', 'to'], true)) {
                continue;
            }

            $terms[$lowerTerm] = $term;
        }

        usort($terms, static fn(string $left, string $right): int => mb_strlen($right, 'UTF-8') <=> mb_strlen($left, 'UTF-8'));

        return array_slice($terms, 0, self::MAX_TERMS);
    }

    /**
     * @param list<string> $terms
     */
    private function cropText(string $text, array $terms, int $maxCharacters, string $append): string
    {
        $textLength = mb_strlen($text, 'UTF-8');
        if ($textLength <= $maxCharacters) {
            return $text;
        }

        $appendLength = mb_strlen($append, 'UTF-8');
        $start = $this->findSnippetStart($text, $terms, $maxCharacters, $appendLength);
        $hasPrefix = $start > 0;
        $prefix = $hasPrefix ? $append : '';
        $suffixLength = $appendLength;
        $available = max(1, $maxCharacters - mb_strlen($prefix, 'UTF-8') - $suffixLength);
        $hasSuffix = ($start + $available) < $textLength;

        if (!$hasSuffix) {
            $available = max(1, $maxCharacters - mb_strlen($prefix, 'UTF-8'));
        }

        $snippet = mb_substr($text, $start, $available, 'UTF-8');
        if ($hasPrefix) {
            $snippet = $this->trimLeadingPartialWord($snippet);
        }
        if ($hasSuffix) {
            $snippet = $this->trimTrailingPartialWord($snippet);
        }

        return $prefix . trim($snippet) . ($hasSuffix ? $append : '');
    }

    /**
     * @param list<string> $terms
     */
    private function findSnippetStart(string $text, array $terms, int $maxCharacters, int $appendLength): int
    {
        if ($terms === []) {
            return 0;
        }

        $pattern = $this->termPattern($terms);
        if ($pattern === '' || preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE) !== 1) {
            return 0;
        }

        $matchOffset = $matches[0][1] ?? 0;
        $matchPosition = mb_strlen(substr($text, 0, $matchOffset), 'UTF-8');
        $contextBefore = max(20, (int)floor(($maxCharacters - ($appendLength * 2)) / 3));
        $start = max(0, $matchPosition - $contextBefore);
        if ($start === 0) {
            return 0;
        }

        $beforeStart = mb_substr($text, 0, $start, 'UTF-8');
        $lastSpace = mb_strrpos($beforeStart, ' ', 0, 'UTF-8');
        if ($lastSpace === false) {
            return $start;
        }

        return max(0, $lastSpace + 1);
    }

    private function trimLeadingPartialWord(string $text): string
    {
        $firstSpace = mb_strpos($text, ' ', 0, 'UTF-8');
        if ($firstSpace === false || $firstSpace > 24) {
            return $text;
        }

        return mb_substr($text, $firstSpace + 1, null, 'UTF-8');
    }

    private function trimTrailingPartialWord(string $text): string
    {
        $lastSpace = mb_strrpos($text, ' ', 0, 'UTF-8');
        if ($lastSpace === false || $lastSpace < 80) {
            return $text;
        }

        return mb_substr($text, 0, $lastSpace, 'UTF-8');
    }

    /**
     * @param list<string> $terms
     */
    private function highlightTerms(string $text, array $terms, string $highlightClass): string
    {
        $pattern = $this->termPattern($terms);
        $matchCount = $pattern !== '' ? preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE) : 0;
        if ($matchCount === false || $matchCount === 0) {
            return $this->escape($text);
        }

        $output = '';
        $offset = 0;
        $class = htmlspecialchars($highlightClass, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        foreach ($matches[0] as $match) {
            $term = $match[0];
            $position = $match[1];
            if ($position < $offset) {
                continue;
            }

            $output .= $this->escape(substr($text, $offset, $position - $offset));
            $output .= '<mark class="' . $class . '">' . $this->escape($term) . '</mark>';
            $offset = $position + strlen($term);
        }

        return $output . $this->escape(substr($text, $offset));
    }

    /**
     * @param list<string> $terms
     */
    private function termPattern(array $terms): string
    {
        if ($terms === []) {
            return '';
        }

        return '/(' . implode('|', array_map(
            static fn(string $term): string => preg_quote($term, '/'),
            $terms
        )) . ')/iu';
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
