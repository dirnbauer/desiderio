<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * Typed reads of Doctrine result rows.
 *
 * A DBAL row is `array<string, mixed>`: the same column comes back as an int
 * on one driver and as a numeric string on another, and a missing column is
 * simply absent. Every seeder needs the same three coercions, so they live
 * here instead of once per seeder.
 */
final class DbRowValues
{
    /**
     * @param array<string, mixed> $row
     */
    public static function integer(array $row, string $key): int
    {
        return self::toInteger($row[$key] ?? null) ?? 0;
    }

    /**
     * Every numeric value of a fetched column, in order; anything that is not
     * a number is dropped rather than coerced to 0.
     *
     * @param array<mixed> $values
     * @return list<int>
     */
    public static function integers(array $values): array
    {
        $integers = [];
        foreach ($values as $value) {
            $integer = self::toInteger($value);
            if ($integer !== null) {
                $integers[] = $integer;
            }
        }

        return $integers;
    }

    public static function nonEmptyString(mixed $value, string $fallback): string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : $fallback;
    }

    private static function toInteger(mixed $value): ?int
    {
        return match (true) {
            is_int($value) => $value,
            is_string($value) && is_numeric($value) => (int)$value,
            default => null,
        };
    }
}
