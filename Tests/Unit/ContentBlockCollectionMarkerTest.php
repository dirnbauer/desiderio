<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Templates\CollectionMarker\BlockAnalysis;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionField;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionLoop;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionLoopAnalyzer;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionMarkerExceptions;

/**
 * Row markers for the Visual Editor (f:mark.item, Visual Editor Enhancements
 * 1.6+): every <f:for> over a Collection field marks the root element of each
 * entry, or is a listed exception with a reason. This is the lint behind
 * `php Build/Scripts/codemod-collection-markers.php --check` and the source
 * of truth for the counts.
 */
final class ContentBlockCollectionMarkerTest extends AbstractContentBlockTestCase
{
    private const string COMPONENTS_DIR = __DIR__ . '/../../Resources/Private/Components';

    /** Collection loops, marked loops and listed exceptions in the shipped templates. */
    private const int EXPECTED_LOOPS = 189;
    private const int EXPECTED_MARKED_LOOPS = 185;
    private const int EXPECTED_EXCEPTIONS = 4;
    private const int EXPECTED_COLLECTIONS = 175;

    /** @var array<string, BlockAnalysis>|null */
    private static ?array $analyses = null;

    public function testEveryCollectionLoopCarriesRowMarkerOrIsListedException(): void
    {
        $loops = 0;
        $marked = 0;
        $exceptions = 0;
        $collections = 0;
        $usedExceptions = [];
        foreach (self::analyses() as $block => $analysis) {
            self::assertSame([], $analysis->failures(), $block . ' (run php Build/Scripts/codemod-collection-markers.php)');
            $looped = [];
            foreach ($analysis->loops as $loop) {
                ++$loops;
                $looped[$loop->field->path] = true;
                if ($loop->exception !== null) {
                    ++$exceptions;
                    $usedExceptions[$loop->key() . '#' . $loop->occurrence] = true;
                    $usedExceptions[$loop->key()] = true;
                    self::assertNotSame(CollectionLoop::SHAPE_SINGLE, $loop->shape, $loop->key() . '#' . $loop->occurrence . ' renders one root per entry: mark it instead of listing it');
                    continue;
                }
                self::assertTrue($loop->isMarked(), $loop->key() . '#' . $loop->occurrence . ' is not marked');
                ++$marked;
            }
            foreach ($analysis->allFields() as $field) {
                ++$collections;
                self::assertArrayHasKey($field->path, $looped, $block . '.' . $field->path . ' is never looped in frontend.html');
            }
        }
        foreach (CollectionMarkerExceptions::EXCEPTIONS as $key => $reason) {
            self::assertArrayHasKey($key, $usedExceptions, 'Stale exception ' . $key);
            self::assertGreaterThan(40, strlen($reason), 'Exception ' . $key . ' needs a reason');
        }
        self::assertSame(self::EXPECTED_COLLECTIONS, $collections);
        self::assertSame(self::EXPECTED_LOOPS, $loops);
        self::assertSame(self::EXPECTED_MARKED_LOOPS, $marked);
        self::assertSame(self::EXPECTED_EXCEPTIONS, $exceptions);
    }

    public function testNoMarkerOutsideCollectionLoops(): void
    {
        foreach (self::analyses() as $block => $analysis) {
            self::assertSame([], $analysis->strayMarkers, $block);
            $markers = preg_match_all('/f:mark\.item\(/', $analysis->document->source);
            $placed = array_sum(array_map(static fn(CollectionLoop $loop): int => count($loop->markers), $analysis->loops));
            self::assertSame($placed, $markers, $block . ': every f:mark.item sits on a loop root');
        }
        foreach (self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/backend-preview.fluid.html') as $preview) {
            self::assertStringNotContainsString('f:mark.item', (string)file_get_contents($preview), $preview . ': backend previews are not edited in the Visual Editor');
        }
        $components = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::COMPONENTS_DIR, \FilesystemIterator::SKIP_DOTS));
        foreach ($components as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                self::assertStringNotContainsString('f:mark.item', (string)file_get_contents($file->getPathname()), $file->getPathname() . ': components receive markers through attributes');
            }
        }
    }

    public function testRowRootComponentsAcceptAttributes(): void
    {
        $components = [];
        foreach (self::analyses() as $analysis) {
            foreach ($analysis->loops as $loop) {
                foreach ($loop->roots as $root) {
                    if (CollectionLoop::ruleFor($root) === CollectionLoop::RULE_COMPONENT && isset($loop->markers[$root->start])) {
                        $components[$root->name][] = $loop->key();
                    }
                }
            }
        }
        ksort($components);
        self::assertSame(
            ['d:atom.link', 'd:molecule.card', 'd:molecule.checkedListItem', 'd:molecule.featureItem', 'd:molecule.tableCell', 'd:molecule.tableRow', 'd:molecule.tabsContent', 'd:molecule.tabsTrigger'],
            array_keys($components),
        );
        foreach (array_keys($components) as $tag) {
            [, $name] = explode(':', $tag, 2);
            [$level, $component] = explode('.', $name, 2);
            $component = ucfirst($component);
            $file = self::COMPONENTS_DIR . '/' . ucfirst($level) . '/' . $component . '/' . $component . '.fluid.html';
            self::assertFileExists($file);
            $source = (string)file_get_contents($file);
            self::assertStringContainsString('<f:argument name="attributes" type="array" optional="{true}" default="{}" />', $source, $tag);
            self::assertStringContainsString('<f:for each="{attributes}" as="attributeValue" key="attributeName"> {attributeName}="{attributeValue}"</f:for>', $source, $tag . ' spreads attributes onto its root tag');
        }
    }

    public function testMarkerCollectionNameMatchesTheLoopedField(): void
    {
        foreach (self::analyses() as $block => $analysis) {
            $config = Yaml::parseFile(self::CONTENT_BLOCKS_DIR . '/' . $block . '/config.yaml');
            self::assertIsArray($config);
            $typeName = $config['typeName'] ?? null;
            self::assertIsString($typeName);
            foreach ($analysis->loops as $loop) {
                // Content Blocks: a root Collection with prefixField: true is
                // the column <typeName>_<identifier> (prefixFields is off for
                // every desiderio block); a nested one keeps its identifier.
                $expected = $loop->field->parent === null ? $typeName . '_' . $loop->field->identifier : $loop->field->identifier;
                self::assertSame($expected, $loop->field->column, $block . '.' . $loop->field->path);
                foreach ($loop->markers as $marker) {
                    self::assertSame($loop->as, $marker['variable'], $loop->key() . ': the marker marks the loop variable');
                    self::assertSame($expected, $marker['collection'], $loop->key() . ': the marker names the looped column');
                }
            }
            preg_match_all("/f:mark\\.item\\(collection: '([^']+)'/", $analysis->document->source, $matches);
            $columns = array_map(static fn(CollectionField $field): string => $field->column, $analysis->allFields());
            foreach ($matches[1] as $collection) {
                self::assertContains($collection, $columns, $block . ': "' . $collection . '" is no Collection column of this element');
            }
        }
    }

    /**
     * @return array<string, BlockAnalysis>
     */
    private static function analyses(): array
    {
        if (self::$analyses === null) {
            $analyzer = new CollectionLoopAnalyzer();
            self::$analyses = [];
            foreach (self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) as $directory) {
                $analysis = $analyzer->analyzeBlock($directory);
                if ($analysis->fields !== []) {
                    self::$analyses[basename($directory)] = $analysis;
                }
            }
        }
        return self::$analyses;
    }
}
