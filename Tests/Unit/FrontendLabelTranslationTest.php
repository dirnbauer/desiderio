<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The German, Chinese and Hungarian label files of the frontend: every
 * translation keeps the placeholders, ICU arguments and markup of its source,
 * and the generated theme-preset overview is translated completely.
 */
final class FrontendLabelTranslationTest extends TestCase
{
    private const LANGUAGES = ['de', 'zh', 'hu'];

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function translationFiles(): iterable
    {
        $root = dirname(__DIR__, 2);
        $sources = [];
        foreach (['/Resources/Private/Language/*.xlf', '/ContentBlocks/ContentElements/*/language/labels.xlf'] as $pattern) {
            $found = glob($root . $pattern);
            $sources = [...$sources, ...($found === false ? [] : $found)];
        }
        foreach ($sources as $source) {
            if (preg_match('/^[a-z]{2}\./', basename($source)) === 1) {
                continue;
            }
            foreach (self::LANGUAGES as $language) {
                $translation = dirname($source) . '/' . $language . '.' . basename($source);
                if (is_file($translation)) {
                    yield substr($translation, strlen($root) + 1) => [$source, $translation];
                }
            }
        }
    }

    #[DataProvider('translationFiles')]
    public function testTranslationsKeepPlaceholdersAndMarkup(string $sourceFile, string $translationFile): void
    {
        $source = $this->units($sourceFile);
        $problems = [];
        foreach ($this->units($translationFile) as $id => [$unitSource, $target]) {
            if ($target === null || !isset($source[$id])) {
                continue;
            }
            // Compare against the current English text only where the
            // translation was made from it; older units are updated separately.
            if (trim($unitSource) !== trim($source[$id][0])) {
                continue;
            }
            if (trim($target) === '') {
                $problems[] = $id . ': empty target';
                continue;
            }
            foreach (['placeholders' => $this->placeholders(...), 'markup' => $this->tags(...)] as $what => $extract) {
                if ($extract($unitSource) !== $extract($target)) {
                    $problems[] = sprintf('%s: %s differ', $id, $what);
                }
            }
        }

        self::assertSame([], $problems);
    }

    public function testThePresetOverviewIsTranslatedCompletely(): void
    {
        $root = dirname(__DIR__, 2) . '/Resources/Private/Language/';
        $source = array_keys($this->units($root . 'presets.xlf'));
        self::assertNotEmpty($source);

        foreach (self::LANGUAGES as $language) {
            $translated = array_keys(array_filter(
                $this->units($root . $language . '.presets.xlf'),
                static fn(array $unit): bool => $unit[1] !== null && trim($unit[1]) !== ''
            ));
            self::assertSame([], array_values(array_diff($source, $translated)), $language . '.presets.xlf lacks units');
        }
    }

    /**
     * @return array<string, array{0: string, 1: ?string}> id => [source, target]
     */
    private function units(string $file): array
    {
        $xml = simplexml_load_file($file);
        self::assertNotFalse($xml, $file . ' is valid XML');
        $xml->registerXPathNamespace('x', 'urn:oasis:names:tc:xliff:document:2.0');
        $units = [];
        $found = $xml->xpath('//x:unit');
        foreach ($found === false || $found === null ? [] : $found as $unit) {
            $unit->registerXPathNamespace('x', 'urn:oasis:names:tc:xliff:document:2.0');
            $source = $unit->xpath('.//x:source');
            $target = $unit->xpath('.//x:target');
            $units[(string)$unit['id']] = [
                $source !== false && isset($source[0]) ? (string)$source[0] : '',
                $target !== false && isset($target[0]) ? (string)$target[0] : null,
            ];
        }

        return $units;
    }

    /**
     * sprintf placeholders, {name} arguments and the head of every ICU
     * argument ("{count, plural"): the text inside a branch may change.
     *
     * @return list<string>
     */
    private function placeholders(string $text): array
    {
        preg_match_all('/%(?:\d+\$)?[sdf]|\{\s*[A-Za-z_][A-Za-z0-9_]*\s*(?:\}|,\s*(?:plural|select|selectordinal|number|date|time)\b)|\{\d+\}/', $text, $matches);
        $found = array_map(static fn(string $match): string => (string)preg_replace('/\s+/', '', $match), $matches[0]);
        sort($found);

        return $found;
    }

    /**
     * @return list<string>
     */
    private function tags(string $text): array
    {
        preg_match_all('/<\s*\/?\s*([a-zA-Z][a-zA-Z0-9]*)[^>]*>/', $text, $matches);
        $tags = $matches[0];
        sort($tags);

        return $tags;
    }
}
