<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Theme;

/**
 * Reads the preset codes of the create page on ui.shadcn.com ("b0",
 * "b4hb38Fyj"): a version letter followed by one base62 number whose bit
 * fields hold the position of every option in its list.
 *
 * This is a port of encodePreset()/decodePreset() of shadcn's registry
 * package (shadcn 4.22, `@shadcn/registry/internal/preset/preset`). The lists
 * and the bit widths below must stay in the order shadcn encodes them: a new
 * value appended upstream decodes here as soon as it is appended here too,
 * and an index this list does not know falls back to the field's first value,
 * as it does upstream.
 *
 * Version "a" carries the first eight fields; version "b" adds the chart
 * colour and the heading font.
 */
final class ShadcnPresetCode
{
    private const string ALPHABET = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    private const array FONTS = [
        'inter', 'noto-sans', 'nunito-sans', 'figtree', 'roboto', 'raleway', 'dm-sans', 'public-sans',
        'outfit', 'jetbrains-mono', 'geist', 'geist-mono', 'lora', 'merriweather', 'playfair-display',
        'noto-serif', 'roboto-slab', 'oxanium', 'manrope', 'space-grotesk', 'montserrat', 'ibm-plex-sans',
        'source-sans-3', 'instrument-sans', 'eb-garamond', 'instrument-serif',
    ];

    private const array THEMES = [
        'neutral', 'stone', 'zinc', 'gray', 'amber', 'blue', 'cyan', 'emerald', 'fuchsia', 'green',
        'indigo', 'lime', 'orange', 'pink', 'purple', 'red', 'rose', 'sky', 'teal', 'violet', 'yellow',
        'mauve', 'olive', 'mist', 'taupe',
    ];

    public const array MENU_COLORS = ['default', 'inverted', 'default-translucent', 'inverted-translucent'];

    public const array MENU_ACCENTS = ['subtle', 'bold'];

    /**
     * @return list<array{0: string, 1: list<string>, 2: int}> field, values, bits — in encoding order
     */
    private static function fields(string $version): array
    {
        $fields = [
            ['menuColor', self::MENU_COLORS, 3],
            ['menuAccent', self::MENU_ACCENTS, 3],
            ['radius', ['default', 'none', 'small', 'medium', 'large'], 4],
            ['font', self::FONTS, 6],
            ['iconLibrary', ['lucide', 'hugeicons', 'tabler', 'phosphor', 'remixicon'], 6],
            ['theme', self::THEMES, 6],
            ['baseColor', ['neutral', 'stone', 'zinc', 'gray', 'mauve', 'olive', 'mist', 'taupe'], 6],
            ['style', ['nova', 'vega', 'maia', 'lyra', 'mira', 'luma', 'sera', 'rhea'], 6],
        ];
        if ($version === 'b') {
            $fields[] = ['chartColor', self::THEMES, 6];
            $fields[] = ['fontHeading', ['inherit', ...self::FONTS], 5];
        }

        return $fields;
    }

    /**
     * Same test as shadcn's isPresetCode(): "a" or "b", then one to nine
     * base62 digits. House preset ids ("lagoon") and "custom" are not codes.
     */
    public static function isCode(string $code): bool
    {
        $length = strlen($code);
        if ($length < 2 || $length > 10 || !in_array($code[0], ['a', 'b'], true)) {
            return false;
        }

        return strspn($code, self::ALPHABET, 1) === $length - 1;
    }

    /**
     * @return array<string, string>|null every field of the code, or null when it is not a preset code
     */
    public static function decode(string $code): ?array
    {
        if (!self::isCode($code)) {
            return null;
        }

        $number = 0;
        foreach (str_split(substr($code, 1)) as $digit) {
            $number = $number * 62 + (int)strpos(self::ALPHABET, $digit);
        }

        $values = [];
        $offset = 0;
        foreach (self::fields($code[0]) as [$field, $options, $bits]) {
            $index = intdiv($number, 2 ** $offset) % (2 ** $bits);
            $values[$field] = $options[$index] ?? $options[0];
            $offset += $bits;
        }
        if ($code[0] === 'a') {
            $values['fontHeading'] = 'inherit';
        }

        return $values;
    }
}
