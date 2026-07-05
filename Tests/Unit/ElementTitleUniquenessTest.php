<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Every element the picker offers must have a unique display title in
 * English AND German — across all Content Blocks and the classic core
 * elements. Duplicate or near-identical names ("Text & Media" twice,
 * two elements both called "Produkthero") make it impossible for editors
 * and for LLM agents selecting elements via the TYPO3 MCP to pick the
 * right one.
 */
final class ElementTitleUniquenessTest extends TestCase
{
    private const CONTENT_BLOCKS_DIR = __DIR__ . '/../../ContentBlocks/ContentElements';
    private const LANGUAGE_DIR = __DIR__ . '/../../Resources/Private/Language';

    public function testEnglishTitlesAreUnique(): void
    {
        $this->assertTitlesUnique($this->collectTitles('en'), 'English');
    }

    public function testGermanTitlesAreUnique(): void
    {
        $this->assertTitlesUnique($this->collectTitles('de'), 'German');
    }

    /**
     * @param array<string, list<string>> $byTitle normalized title => owners
     */
    private function assertTitlesUnique(array $byTitle, string $language): void
    {
        $duplicates = [];
        foreach ($byTitle as $title => $owners) {
            if (count($owners) > 1) {
                $duplicates[] = $title . ' => ' . implode(', ', $owners);
            }
        }

        self::assertSame(
            [],
            $duplicates,
            "Duplicate {$language} element titles:\n" . implode("\n", $duplicates)
        );
    }

    /**
     * @return array<string, list<string>>
     */
    private function collectTitles(string $language): array
    {
        $byTitle = [];

        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks === false ? [] : $blocks as $block) {
            $name = basename($block);
            $file = $language === 'en'
                ? "{$block}/language/labels.xlf"
                : "{$block}/language/de.labels.xlf";
            $title = $this->extractUnitText((string)file_get_contents($file), 'title', $language);
            if ($title !== '') {
                $byTitle[$this->normalize($title)][] = $name;
            }
        }

        $coreFile = $language === 'en'
            ? self::LANGUAGE_DIR . '/library_core.xlf'
            : self::LANGUAGE_DIR . '/de.library_core.xlf';
        $core = (string)file_get_contents($coreFile);
        preg_match_all('/<unit id="([a-z0-9_]+)\.title">.*?<\/unit>/s', $core, $units, PREG_SET_ORDER);
        foreach ($units as $unit) {
            $title = $this->extractUnitText($unit[0], $unit[1] . '.title', $language);
            if ($title !== '') {
                $byTitle[$this->normalize($title)][] = 'core:' . $unit[1];
            }
        }

        return $byTitle;
    }

    private function extractUnitText(string $xml, string $unitId, string $language): string
    {
        $pattern = '/<unit id="' . preg_quote($unitId, '/') . '">.*?<\/unit>/s';
        if (preg_match($pattern, $xml, $unit) !== 1) {
            return '';
        }
        $tag = $language === 'en' ? 'source' : 'target';
        if (preg_match("/<{$tag}>(.*?)<\/{$tag}>/s", $unit[0], $text) !== 1) {
            return '';
        }

        return html_entity_decode(trim($text[1]), ENT_QUOTES | ENT_XML1);
    }

    private function normalize(string $title): string
    {
        $lower = mb_strtolower($title);
        $clean = preg_replace('/[^a-z0-9äöüß ]/u', ' ', $lower) ?? $lower;
        $collapsed = preg_replace('/\s+/', ' ', $clean) ?? $clean;

        return trim($collapsed);
    }
}
