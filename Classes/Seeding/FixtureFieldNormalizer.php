<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

/**
 * Shared Content Blocks fixture field normalization for seed commands.
 */
final class FixtureFieldNormalizer
{
    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function isFileField(array $fieldConfig): bool
    {
        return ($fieldConfig['type'] ?? '') === 'File';
    }

    public function isEmptySeedValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    public function stringFromMixed(mixed $value): string
    {
        return is_scalar($value) ? trim((string)$value) : '';
    }

    public function normalizeScalarValue(mixed $value): int|string
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_int($value)) {
            return $value;
        }
        if (is_float($value)) {
            return (string)$value;
        }
        if ($value === null) {
            return '';
        }
        if (is_string($value)) {
            return $value;
        }

        return '';
    }

    public function normalizeBooleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value)) {
            return $value !== 0;
        }
        if (is_string($value)) {
            return !in_array(strtolower(trim($value)), ['', '0', 'false', 'no'], true);
        }

        return false;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function normalizeStarterFieldValue(mixed $value, array $fieldConfig): int|string
    {
        $type = is_string($fieldConfig['type'] ?? null) ? $fieldConfig['type'] : '';
        if ($type === 'Checkbox') {
            return $this->normalizeBooleanValue($value) ? 1 : 0;
        }
        if (in_array($type, ['Date', 'DateTime'], true)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->getTimestamp();
            }
            if (is_scalar($value)) {
                $timestamp = strtotime((string)$value);
                return $timestamp === false ? (string)$value : $timestamp;
            }
        }

        return $this->normalizeScalarValue($value);
    }

    public function normalizeDateTimeFieldValue(mixed $value): int|string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int)$value;
        }

        if (is_string($value)) {
            $trimmedValue = trim($value);
            if ($trimmedValue === '') {
                return '';
            }

            if (preg_match('/^-?\d+$/', $trimmedValue) === 1) {
                return (int)$trimmedValue;
            }

            $timestamp = strtotime($trimmedValue);
            if ($timestamp !== false) {
                return $timestamp;
            }
        }

        return '';
    }

    /**
     * @param array<int|string, mixed> $value
     */
    public function normalizeStarterArrayForScalarField(array $value): string
    {
        if ($value === []) {
            return '';
        }

        $scalars = [];
        foreach ($value as $item) {
            if (is_scalar($item)) {
                $scalars[] = trim((string)$item);
            }
        }

        return implode("\n", array_filter($scalars, static fn (string $item): bool => $item !== ''));
    }

    /**
     * @param array<int, mixed> $values
     */
    public function formatFlatScalarList(array $values, string $separator): string
    {
        $items = [];
        foreach ($values as $value) {
            $normalized = $this->normalizeScalarValue($value);
            $items[] = trim((string)$normalized);
        }

        return implode($separator, array_filter($items, static fn (string $item): bool => $item !== ''));
    }

    /**
     * @param array<int, mixed> $values
     */
    public function containsNestedArray(array $values): bool
    {
        foreach ($values as $value) {
            if (is_array($value)) {
                return true;
            }
        }

        return false;
    }

    public function buildReadableFileTitle(string $value, string $emptyFallback = 'Asset'): string
    {
        $value = preg_replace('/[^a-zA-Z0-9]+/', ' ', $value) ?? $value;
        $value = trim($value);

        return $value !== '' ? ucwords(strtolower($value)) : $emptyFallback;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    public function buildFileReferenceFixturesFromFixtureValue(
        mixed $value,
        array $fieldConfig = [],
        string $titleFallback = 'Asset',
    ): array {
        if ($this->isEmptySeedValue($value)) {
            return [];
        }

        $items = [];

        if (is_string($value)) {
            $items[] = ['file' => $value];
        } elseif (is_array($value) && isset($value['file'])) {
            $items[] = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($value);
        } elseif (is_array($value)) {
            foreach ($value as $item) {
                if (is_string($item)) {
                    $items[] = ['file' => $item];
                    continue;
                }
                if (is_array($item) && isset($item['file'])) {
                    $items[] = ContentBlockDefinitionRegistry::normalizeStringKeyedArray($item);
                }
            }
        }

        if ($fieldConfig !== []) {
            $maxItems = ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxitems')
                ?? ContentBlockDefinitionRegistry::getConfiguredInteger($fieldConfig, 'maxItems')
                ?? 1;
            $items = array_slice($items, 0, max(1, $maxItems));
        }

        $references = [];
        foreach ($items as $item) {
            $file = $this->stringFromMixed($item['file'] ?? '');
            if ($file === '') {
                continue;
            }
            if (str_starts_with($file, 'EXT:desiderio/')) {
                $file = substr($file, strlen('EXT:desiderio/'));
            }

            $fallbackTitle = $this->buildReadableFileTitle(pathinfo($file, PATHINFO_FILENAME), $titleFallback);
            $title = $this->stringFromMixed($item['title'] ?? '');
            if ($title === '') {
                $title = $fallbackTitle;
            }
            $source = $this->stringFromMixed($item['source'] ?? $item['link'] ?? '');

            $references[] = [
                'file' => $file,
                'title' => $title,
                'alternative' => $this->stringFromMixed($item['alternative'] ?? $item['alt'] ?? $title),
                'description' => $this->stringFromMixed($item['description'] ?? $item['credit'] ?? $source),
                'source' => $source,
            ];
        }

        return $references;
    }
}
