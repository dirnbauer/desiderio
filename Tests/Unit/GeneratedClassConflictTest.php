<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * React composes shadcn classes through `cn()`, whose tailwind-merge drops a
 * base token as soon as a variant sets the same CSS property. Fluid has no such
 * merge: base and variant end up in one class attribute, and then the COMPILED
 * stylesheet's order decides which wins — not the order in the attribute.
 *
 * That is invisible in review and silently broke the outline button and badge
 * (`border-transparent` from the shadcn base is emitted after `border-border`,
 * so every outline control rendered a transparent border). This test reads the
 * built Tailwind bundle and fails whenever a component's base token would
 * out-rank a variant token for the same property.
 *
 * Only unprefixed utilities are compared: a variant-prefixed utility
 * (`hover:`, `dark:`, `focus-visible:`) carries a pseudo-class or scope in its
 * selector and therefore never ties with an unprefixed one.
 */
final class GeneratedClassConflictTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../..';
    private const string BUNDLE = self::ROOT . '/Resources/Public/Css/desiderio-tailwind.css';

    #[Test]
    public function noBaseUtilityOutranksAVariantUtilityForTheSameProperty(): void
    {
        $bundle = $this->utilities();
        $conflicts = [];

        foreach ($this->componentTemplates() as $path => $source) {
            if (preg_match('/<f:variable name="base" value="([^"]*)"/', $source, $match) !== 1) {
                continue;
            }

            $baseTokens = self::tokens($match[1]);
            preg_match_all('/<f:(?:case value="[^"]*"|defaultCase)>([^<]*)<\/f:(?:case|defaultCase)>/', $source, $cases);

            foreach ($cases[1] as $case) {
                foreach (self::tokens($case) as $variantToken) {
                    $variant = $bundle[$variantToken] ?? null;
                    if ($variant === null) {
                        continue;
                    }

                    foreach ($baseTokens as $baseToken) {
                        $base = $bundle[$baseToken] ?? null;
                        // Equal offsets mean one grouped rule — the same declaration,
                        // so neither token can override the other.
                        if ($base === null || $baseToken === $variantToken || $base['offset'] <= $variant['offset']) {
                            continue;
                        }

                        $shared = array_intersect($base['properties'], $variant['properties']);
                        if ($shared !== []) {
                            $conflicts[] = sprintf(
                                '%s: base "%s" out-ranks variant "%s" for %s',
                                $path,
                                $baseToken,
                                $variantToken,
                                implode(', ', $shared)
                            );
                        }
                    }
                }
            }
        }

        self::assertSame(
            [],
            array_values(array_unique($conflicts)),
            'Move the losing base token into the variants that do not set the property '
            . '(see hoistBorderColorOutOfBase() in Build/Scripts/sync-shadcn-fluid-primitives.php).'
        );
    }

    /**
     * Unprefixed utility classes of the built bundle.
     *
     * @return array<string, array{offset: int, properties: list<string>}>
     */
    private function utilities(): array
    {
        self::assertFileExists(self::BUNDLE, 'Run `npm run build:css` first.');
        $css = (string)file_get_contents(self::BUNDLE);
        $utilities = [];

        preg_match_all('/([^{}@]+)\{([^{}]*)\}/', $css, $rules, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($rules as $rule) {
            $properties = [];
            foreach (explode(';', $rule[2][0]) as $declaration) {
                $property = trim(explode(':', $declaration, 2)[0]);
                if ($property !== '' && !str_starts_with($property, '--tw') && str_contains($declaration, ':')) {
                    $properties[$property] = true;
                }
            }

            if ($properties === []) {
                continue;
            }

            foreach (explode(',', $rule[1][0]) as $selector) {
                $selector = trim($selector);
                // Plain single-class selectors only: anything with a combinator,
                // pseudo-class or attribute test cannot tie with a bare utility.
                if (preg_match('/^\.((?:[^\s.:\[\]>+~()]|\\\\.)+)$/', $selector, $match) !== 1) {
                    continue;
                }

                $class = str_replace('\\', '', $match[1]);
                $utilities[$class] = [
                    'offset' => $rule[0][1],
                    'properties' => array_values(array_unique(array_merge(
                        $utilities[$class]['properties'] ?? [],
                        array_keys($properties)
                    ))),
                ];
            }
        }

        self::assertArrayHasKey('border-border', $utilities, 'Bundle looks unparsed.');

        return $utilities;
    }

    /** @return array<string, string> relative path => source */
    private function componentTemplates(): array
    {
        $templates = [];
        foreach ((array)glob(self::ROOT . '/Resources/Private/Components/*/*/*.fluid.html') as $file) {
            if (is_string($file)) {
                $templates[basename($file)] = (string)file_get_contents($file);
            }
        }

        self::assertNotSame([], $templates);

        return $templates;
    }

    /** @return list<string> */
    private static function tokens(string $class): array
    {
        $tokens = preg_split('/\s+/', trim($class));

        return array_values(array_filter(
            $tokens === false ? [] : $tokens,
            static fn(string $token): bool => $token !== '' && !str_contains($token, ':')
        ));
    }
}
