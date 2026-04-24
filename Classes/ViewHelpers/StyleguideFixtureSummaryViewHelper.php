<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Extracts a compact human-readable summary from a styleguide fixture.
 */
final class StyleguideFixtureSummaryViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('fixture', 'array', 'Fixture data for a content element', true);
        $this->registerArgument('fallback', 'string', 'Fallback text when no fixture text exists', false, '');
    }

    public function render(): string
    {
        /** @var array<string, mixed> $fixture */
        $fixture = $this->arguments['fixture'];
        $fallback = (string)($this->arguments['fallback'] ?? '');

        $priorityFields = [
            'header',
            'headline',
            'title',
            'name',
            'description',
            'subheadline',
            'lead',
            'text',
            'bodytext',
            'caption',
            'eyebrow',
            'badge',
        ];

        foreach ($priorityFields as $field) {
            if (!array_key_exists($field, $fixture)) {
                continue;
            }
            $summary = $this->extractText($fixture[$field]);
            if ($summary !== '') {
                return $this->truncate($summary);
            }
        }

        $summary = $this->extractText($fixture);
        return $this->truncate($summary !== '' ? $summary : $fallback);
    }

    private function extractText(mixed $value): string
    {
        if (is_string($value) || is_int($value) || is_float($value)) {
            return trim(strip_tags(html_entity_decode((string)$value, ENT_QUOTES | ENT_HTML5)));
        }

        if (!is_array($value)) {
            return '';
        }

        foreach ($value as $item) {
            $text = $this->extractText($item);
            if ($text !== '') {
                return $text;
            }
        }

        return '';
    }

    private function truncate(string $text): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';
        if (strlen($text) <= 150) {
            return $text;
        }

        return rtrim(substr($text, 0, 147)) . '...';
    }
}
