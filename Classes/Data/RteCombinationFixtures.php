<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data;

/**
 * Deterministic rich-text stress fixture for the Desiderio styleguide.
 *
 * The first 82 blocks form an Eulerian circuit over the nine primary RTE
 * block types. That guarantees every ordered adjacency (including self-pairs)
 * appears at least once. A fixed pseudo-random tail brings the fixture to the
 * requested 100 blocks without making visual regressions flaky.
 */
final class RteCombinationFixtures
{
    /** @var list<string> */
    private const PRIMARY_BLOCK_TYPES = [
        'p',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'ul',
        'ol',
        'blockquote',
    ];

    private const BLOCK_COUNT = 100;

    public static function bodytext(): string
    {
        $blocks = [];
        foreach (self::blockSequence() as $index => $tag) {
            $blocks[] = self::renderBlock($tag, $index);
        }

        return implode("\n", $blocks);
    }

    /**
     * @return list<string>
     */
    public static function blockSequence(): array
    {
        /** @var array<string, list<string>> $adjacency */
        $adjacency = [];
        foreach (self::PRIMARY_BLOCK_TYPES as $from) {
            $destinations = self::PRIMARY_BLOCK_TYPES;
            usort(
                $destinations,
                static fn (string $left, string $right): int => strcmp(
                    hash('sha256', 'desiderio-rte|' . $from . '|' . $left),
                    hash('sha256', 'desiderio-rte|' . $from . '|' . $right)
                )
            );
            $adjacency[$from] = $destinations;
        }

        $stack = [self::PRIMARY_BLOCK_TYPES[0]];
        $circuit = [];
        while ($stack !== []) {
            $vertex = $stack[array_key_last($stack)];
            if ($adjacency[$vertex] !== []) {
                $next = array_pop($adjacency[$vertex]);
                if (is_string($next)) {
                    $stack[] = $next;
                }
                continue;
            }

            $completed = array_pop($stack);
            if (is_string($completed)) {
                $circuit[] = $completed;
            }
        }

        $sequence = array_reverse($circuit);
        for ($index = count($sequence); $index < self::BLOCK_COUNT; $index++) {
            $hash = hash('sha256', 'desiderio-rte-tail|' . $index);
            $typeIndex = (int)hexdec(substr($hash, 0, 4)) % count(self::PRIMARY_BLOCK_TYPES);
            $sequence[] = self::PRIMARY_BLOCK_TYPES[$typeIndex];
        }

        return array_slice($sequence, 0, self::BLOCK_COUNT);
    }

    private static function renderBlock(string $tag, int $index): string
    {
        if ($tag === 'ul' || $tag === 'ol') {
            return sprintf(
                '<%1$s%2$s><li>%3$s</li><li>%4$s</li><li>%5$s</li></%1$s>',
                $tag,
                self::blockAttributes($tag, $index),
                self::inlineFixture($index),
                self::inlineFixture($index + 5),
                self::inlineFixture($index + 11)
            );
        }

        if ($tag === 'blockquote') {
            return sprintf(
                '<blockquote%1$s><p>%2$s</p><p><cite>%3$s</cite></p></blockquote>',
                self::blockAttributes($tag, $index),
                self::inlineFixture($index),
                self::inlineFixture($index + 7)
            );
        }

        return sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $tag,
            self::blockAttributes($tag, $index),
            self::inlineFixture($index)
        );
    }

    private static function blockAttributes(string $tag, int $index): string
    {
        $classes = [];
        if ($tag === 'p' && $index % 3 === 0) {
            $classes[] = 'lead';
        }
        if ($tag !== 'ul' && $tag !== 'ol' && $tag !== 'blockquote') {
            if ($index % 17 === 0) {
                $classes[] = 'text-center';
            } elseif ($index % 19 === 0) {
                $classes[] = 'text-end';
            } elseif ($index % 23 === 0) {
                $classes[] = 'text-justify';
            }
        }

        return $classes === [] ? '' : ' class="' . implode(' ', $classes) . '"';
    }

    private static function inlineFixture(int $index): string
    {
        $fixtures = [
            'Brief note.',
            'A <strong>bold statement</strong> checks weight without changing the reading rhythm.',
            'This sentence uses <em>italic emphasis</em> for a quiet change of voice.',
            'Read the <a href="https://github.com/dirnbauer/desiderio">Desiderio repository</a> for the implementation details.',
            'The <abbr title="Rich Text Editor">RTE</abbr> abbreviation keeps its expansion available to readers.',
            '<span lang="de">Guten Tag</span> switches the text-part language while the surrounding sentence stays English.',
            'Chemical H<sub>2</sub>O and the expression x<sup>2</sup> exercise subscript and superscript alignment.',
            'Nested <strong><em>bold italic emphasis</em></strong> must remain legible in every heading size.',
            '<mark>Highlighted copy</mark> draws attention without relying on color alone.',
            'Use <code>inlineCode()</code>, press <kbd>Command</kbd> + <kbd>K</kbd>, and inspect the <samp>saved</samp> result.',
            '<small>Small print stays readable.</small> A <span class="text-muted">muted phrase</span> remains deliberately secondary.',
            '<del>Outdated wording</del> is followed by <ins>clear replacement copy</ins>.',
            'A soft&shy;hyphen gives an exceptionally long interoperability term a safe wrapping opportunity.',
            'The first thought ends here.<br>The second line checks an intentional soft return inside the same block.',
            'Special characters remain calm in the line: ©, €, →, ×, “quotes”, and an en dash – all belong to normal editorial copy.',
            'This deliberately longer passage combines <strong>strong importance</strong>, <em>gentle emphasis</em>, and a <a href="https://example.com/rte-link">linked phrase</a> inside one continuous piece of editorial text. It is long enough to wrap across several lines on narrow screens and expose awkward spacing, overflow, underline, or line-height behavior that a short placeholder would never reveal.',
        ];

        return $fixtures[$index % count($fixtures)];
    }
}
