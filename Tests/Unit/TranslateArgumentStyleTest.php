<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * TYPO3 v14 formats a label by the shape of the arguments f:translate gets:
 * a list ({0: …, 1: …}) goes through vsprintf(), named arguments
 * ({count: …}) through ICU MessageFormat. A label written as an ICU message
 * but called with a list is printed as it is written, braces and all: every
 * chart summary on the Data & Dashboards chapter read
 * "{0}: area chart trending across {1, plural, …}" in every language.
 *
 * This test reads every f:translate call on a Desiderio label and checks that
 * the argument style matches the label: ICU placeholders need named
 * arguments, %s placeholders need a list.
 */
final class TranslateArgumentStyleTest extends TestCase
{
    private const ICU_PLACEHOLDER = '/\{\s*[A-Za-z0-9_]+\s*(?:\}|,)/';
    private const SPRINTF_PLACEHOLDER = '/%(?:\d+\$)?[sdf]/';

    public function testEveryTranslateCallPassesArgumentsInTheStyleOfItsLabel(): void
    {
        $root = dirname(__DIR__, 2);
        $templates = [];
        foreach (['/ContentBlocks/ContentElements/*/templates/*.html', '/Resources/Private/*/*.html', '/Resources/Private/*/*/*.html', '/Resources/Private/*/*/*/*.html', '/Resources/Private/*/*/*/*/*.html'] as $pattern) {
            $found = glob($root . $pattern);
            $templates = [...$templates, ...($found === false ? [] : $found)];
        }
        self::assertNotSame([], $templates);

        $checked = 0;
        $problems = [];
        foreach (array_unique($templates) as $template) {
            $html = (string)file_get_contents($template);
            preg_match_all('#key(?:="|:\s*\')LLL:EXT:desiderio/([^:"\']+\.xlf):([^"\']+)["\']#', $html, $calls, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($calls as $call) {
                // The arguments of this call sit between its key and the end of
                // the tag or inline call, and before the next translate call.
                $rest = substr($html, $call[0][1] + strlen($call[0][0]), 400);
                $end = preg_match('#>|\)\}|key(?:="|:\s*\')#', $rest, $stop, PREG_OFFSET_CAPTURE) === 1 ? $stop[0][1] : strlen($rest);
                if (preg_match('#arguments(?:="|:\s*\'?)\{\s*([A-Za-z0-9_]+)\s*:#', substr($rest, 0, $end), $argument) !== 1) {
                    continue;
                }
                $label = $this->source($root . '/' . $call[1][0], $call[2][0]);
                if ($label === null) {
                    continue;
                }
                $checked++;
                $where = substr($template, strlen($root) + 1) . ' → ' . $call[2][0];
                $positional = ctype_digit($argument[1]);
                if ($positional && preg_match(self::ICU_PLACEHOLDER, $label) === 1) {
                    $problems[] = $where . ': an ICU message called with a list; pass named arguments';
                }
                if (!$positional && preg_match(self::SPRINTF_PLACEHOLDER, $label) === 1) {
                    $problems[] = $where . ': a sprintf label called with named arguments; pass a list';
                }
            }
        }

        self::assertGreaterThan(20, $checked, 'Too few translate calls with arguments were found; the pattern no longer matches the templates.');
        self::assertSame([], $problems, implode("\n", $problems));
    }

    /** @var array<string, array<string, string>> */
    private array $labels = [];

    private function source(string $file, string $id): ?string
    {
        if (!isset($this->labels[$file])) {
            $this->labels[$file] = [];
            if (is_file($file)) {
                $xml = simplexml_load_file($file);
                if ($xml !== false) {
                    $xml->registerXPathNamespace('x', 'urn:oasis:names:tc:xliff:document:2.0');
                    $units = $xml->xpath('//x:unit');
                    foreach (is_array($units) ? $units : [] as $unit) {
                        $unit->registerXPathNamespace('x', 'urn:oasis:names:tc:xliff:document:2.0');
                        $source = $unit->xpath('x:segment/x:source');
                        $this->labels[$file][(string)$unit['id']] = isset($source[0]) ? (string)$source[0] : '';
                    }
                }
            }
        }

        return $this->labels[$file][$id] ?? null;
    }
}
