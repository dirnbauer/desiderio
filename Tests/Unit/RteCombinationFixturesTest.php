<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use DOMDocument;
use DOMElement;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Data\RteCombinationFixtures;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

final class RteCombinationFixturesTest extends TestCase
{
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

    public function testSequenceContainsOneHundredBlocksAndEveryOrderedPrimaryPair(): void
    {
        $sequence = RteCombinationFixtures::blockSequence();

        self::assertCount(100, $sequence);
        self::assertSame(self::PRIMARY_BLOCK_TYPES, array_values(array_unique(array_intersect(
            self::PRIMARY_BLOCK_TYPES,
            $sequence
        ))));

        $observedPairs = [];
        for ($index = 1; $index < count($sequence); $index++) {
            $observedPairs[$sequence[$index - 1] . '>' . $sequence[$index]] = true;
        }

        foreach (self::PRIMARY_BLOCK_TYPES as $from) {
            foreach (self::PRIMARY_BLOCK_TYPES as $to) {
                self::assertArrayHasKey($from . '>' . $to, $observedPairs);
            }
        }
    }

    public function testBodytextContainsVariedInlineStylesAndTextLengths(): void
    {
        [$document, $container] = $this->parseBodytext(RteCombinationFixtures::bodytext());
        $blocks = [];
        $shortestTextLength = PHP_INT_MAX;
        $longestTextLength = 0;
        foreach ($container->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }
            $blocks[] = strtolower($node->tagName);
            $text = preg_replace('/\s+/u', ' ', trim($node->textContent));
            self::assertIsString($text);
            $textLength = mb_strlen($text);
            $shortestTextLength = min($shortestTextLength, $textLength);
            $longestTextLength = max($longestTextLength, $textLength);
        }

        self::assertCount(100, $blocks);
        self::assertLessThanOrEqual(20, $shortestTextLength);
        self::assertGreaterThanOrEqual(300, $longestTextLength);

        foreach (['strong', 'em', 'a', 'abbr', 'sub', 'sup', 'mark', 'code', 'kbd', 'samp', 'small', 'del', 'ins', 'br', 'cite'] as $tag) {
            self::assertGreaterThan(0, $document->getElementsByTagName($tag)->length, $tag);
        }

        $xpath = new DOMXPath($document);
        self::assertGreaterThan(0, $this->countXpath($xpath, '//*[@lang]'));
        self::assertGreaterThan(0, $this->countXpath($xpath, '//*[contains(concat(" ", normalize-space(@class), " "), " lead ")]'));
        self::assertGreaterThan(0, $this->countXpath($xpath, '//*[contains(concat(" ", normalize-space(@class), " "), " text-muted ")]'));
        self::assertGreaterThan(0, $this->countXpath($xpath, '//*[contains(concat(" ", normalize-space(@class), " "), " text-center ")]'));
        self::assertGreaterThan(0, $this->countXpath($xpath, '//*[contains(concat(" ", normalize-space(@class), " "), " text-end ")]'));
        self::assertGreaterThan(0, $this->countXpath($xpath, '//*[contains(concat(" ", normalize-space(@class), " "), " text-justify ")]'));
    }

    public function testStyleguidePageWiresTheFixtureToAnEditableCoreTextElement(): void
    {
        $page = null;
        foreach (StyleguideShowcasePages::subpages() as $candidate) {
            if ($candidate['slug'] === '/content-types/rte-combinations') {
                $page = $candidate;
                break;
            }
        }

        self::assertIsArray($page);
        self::assertSame('content-types', $page['parentSlug']);
        self::assertSame('RTE.config.tt_content.bodytext.types.text.preset = desiderio', $page['pageTsConfig'] ?? null);
        self::assertTrue($page['hideInNav'] ?? false);
        self::assertCount(1, $page['content']);
        self::assertSame('text', $page['content'][0]['ctype']);
        self::assertSame(RteCombinationFixtures::bodytext(), $page['content'][0]['fields']['bodytext']);

        $preset = self::requireArray(Yaml::parseFile(__DIR__ . '/../../Configuration/RTE/Desiderio.yaml'));
        $editor = self::requireArray($preset['editor'] ?? null);
        $editorConfig = self::requireArray($editor['config'] ?? null);

        $heading = self::requireArray($editorConfig['heading'] ?? null);
        $headingOptions = self::requireArray($heading['options'] ?? null);
        $headingViews = array_column($headingOptions, 'view');
        foreach (['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre'] as $view) {
            self::assertContains($view, $headingViews);
        }

        $style = self::requireArray($editorConfig['style'] ?? null);
        $styleDefinitions = self::requireArray($style['definitions'] ?? null);
        $styleClassesByElement = array_column($styleDefinitions, 'classes', 'element');
        foreach ([
            'p' => ['lead'],
            'small' => ['rte-small'],
            'span' => ['text-muted'],
            'mark' => ['rte-highlight'],
            'kbd' => ['rte-keyboard'],
            'samp' => ['rte-sample'],
            'cite' => ['rte-citation'],
            'del' => ['rte-deleted'],
            'ins' => ['rte-inserted'],
        ] as $element => $classes) {
            self::assertSame($classes, $styleClassesByElement[$element] ?? null, $element);
        }

        $toolbar = self::requireArray($editorConfig['toolbar'] ?? null);
        $toolbarItems = self::requireArray($toolbar['items'] ?? null);
        foreach ([
            'style',
            'heading',
            'bold',
            'italic',
            'code',
            'subscript',
            'superscript',
            'softhyphen',
            'textPartLanguage',
            'abbreviation',
            'bulletedList',
            'numberedList',
            'blockQuote',
            'alignment',
            'link',
            'specialCharacters',
            'sourceEditing',
        ] as $toolbarItem) {
            self::assertContains($toolbarItem, $toolbarItems);
        }

        $htmlSupportConfig = self::requireArray($editorConfig['htmlSupport'] ?? null);
        $htmlSupport = self::requireArray($htmlSupportConfig['allow'] ?? null);
        $htmlSupportElements = array_column($htmlSupport, 'name');
        foreach (['small', 'mark', 'cite', 'kbd', 'samp', 'del', 'ins'] as $element) {
            self::assertContains($element, $htmlSupportElements);
        }

        $importModules = self::requireArray($editorConfig['importModules'] ?? null);
        $codePluginImported = false;
        foreach ($importModules as $importModule) {
            $importModuleConfig = self::requireArray($importModule);
            if (($importModuleConfig['module'] ?? null) !== '@ckeditor/ckeditor5-basic-styles') {
                continue;
            }
            $exports = self::requireArray($importModuleConfig['exports'] ?? null);
            $codePluginImported = in_array('Code', $exports, true);
        }
        self::assertTrue($codePluginImported);

        $removedModules = self::requireArray($editorConfig['removeImportModules'] ?? null);
        $strikethroughRemoved = false;
        foreach ($removedModules as $removedModule) {
            $removedModuleConfig = self::requireArray($removedModule);
            if (($removedModuleConfig['module'] ?? null) !== '@ckeditor/ckeditor5-basic-styles') {
                continue;
            }
            $exports = self::requireArray($removedModuleConfig['exports'] ?? null);
            $strikethroughRemoved = in_array('Strikethrough', $exports, true);
        }
        self::assertTrue($strikethroughRemoved);

        $alignment = self::requireArray($editorConfig['alignment'] ?? null);
        $alignmentOptions = self::requireArray($alignment['options'] ?? null);
        self::assertSame(
            ['text-start', 'text-center', 'text-end', 'text-justify'],
            array_column($alignmentOptions, 'className')
        );

        $testPreset = Yaml::parseFile(__DIR__ . '/../../Configuration/RTE/DesiderioTest.yaml');
        self::assertSame([
            'imports' => [
                ['resource' => 'EXT:desiderio/Configuration/RTE/Desiderio.yaml'],
            ],
        ], $testPreset);

        $extLocalconf = (string)file_get_contents(__DIR__ . '/../../ext_localconf.php');
        self::assertStringContainsString("['desiderio_test']", $extLocalconf);

        $setup = (string)file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/setup.typoscript');
        self::assertSame(1, preg_match('/allowTags\\s*=\\s*([^\\r\\n]+)/', $setup, $allowTagsMatch));
        $allowTags = $allowTagsMatch[1];
        self::assertIsString($allowTags);
        $frontendAllowedTags = array_map('trim', explode(',', $allowTags));
        preg_match_all('/<([a-z][a-z0-9]*)\\b/i', RteCombinationFixtures::bodytext(), $fixtureTagMatches);
        $fixtureTags = $fixtureTagMatches[1];
        self::assertIsArray($fixtureTags);
        foreach ($fixtureTags as $index => $fixtureTag) {
            self::assertIsString($fixtureTag, (string)$index);
            $fixtureTags[$index] = strtolower($fixtureTag);
        }
        foreach (array_unique($fixtureTags) as $fixtureTag) {
            self::assertContains($fixtureTag, $frontendAllowedTags, $fixtureTag);
        }
    }

    /**
     * @return array{DOMDocument, DOMElement}
     */
    private function parseBodytext(string $bodytext): array
    {
        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument('1.0', 'UTF-8');
        $loaded = $document->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><div id="rte-fixture">'
            . $bodytext
            . '</div></body></html>',
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        self::assertTrue($loaded);
        $container = $document->getElementById('rte-fixture');
        self::assertInstanceOf(DOMElement::class, $container);

        return [$document, $container];
    }

    private function countXpath(DOMXPath $xpath, string $expression): int
    {
        $nodes = $xpath->query($expression);
        if ($nodes === false) {
            self::fail('Invalid XPath expression: ' . $expression);
        }

        return $nodes->length;
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function requireArray(mixed $value): array
    {
        self::assertIsArray($value);

        return $value;
    }
}
