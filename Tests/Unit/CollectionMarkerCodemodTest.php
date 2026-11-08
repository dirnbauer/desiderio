<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Templates\CollectionMarker\BlockAnalysis;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionField;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionLoop;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionLoopAnalyzer;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionMarkerExceptions;
use Webconsulting\Desiderio\Templates\CollectionMarker\CollectionMarkerRewriter;
use Webconsulting\Desiderio\Templates\CollectionMarker\MarkupParser;

/**
 * The rules of Build/Scripts/codemod-collection-markers.php on small
 * templates: what counts as one root, where the marker goes, how existing
 * component attributes are merged, and that a second run changes nothing.
 */
final class CollectionMarkerCodemodTest extends TestCase
{
    private const string CONFIG = <<<'YAML'
        name: desiderio/demo-block
        typeName: desiderio_demoblock
        prefixFields: false
        fields:
          - identifier: items
            type: Collection
            table: demo_items
            prefixField: true
            fields:
              - identifier: image
                type: File
                maxitems: 1
              - identifier: gallery
                type: File
              - identifier: features
                type: Collection
                table: demo_item_features
                fields:
                  - identifier: text
                    type: Text
          - identifier: links
            type: Collection
            foreign_table: demo_shared_links
            prefixField: true
        YAML;

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rewrites(): iterable
    {
        yield 'HTML root' => [
            '<f:for each="{data.items}" as="item"><li class="x">{item.title}</li></f:for>',
            '<f:for each="{data.items}" as="item"><li class="x"{item -> f:mark.item(collection: \'desiderio_demoblock_items\')}>{item.title}</li></f:for>',
        ];
        yield 'self-closing HTML root, attribute values holding ">" and inline syntax' => [
            '<f:for each="{data.items}" as="item"><img alt="{f:if(condition: \'{item.a} > 1\', then: \'>\')}" src="x" /></f:for>',
            '<f:for each="{data.items}" as="item"><img alt="{f:if(condition: \'{item.a} > 1\', then: \'>\')}" src="x" {item -> f:mark.item(collection: \'desiderio_demoblock_items\')}/></f:for>',
        ];
        yield 'transparent f:if, f:variable and comments' => [
            "<f:for each=\"{data.items}\" as=\"item\">\n  <f:variable name=\"v\" value=\"1\"/><f:comment>x</f:comment><!-- c -->\n  <f:if condition=\"{item.title}\"><div>{item.title}</div></f:if>\n</f:for>",
            "<f:for each=\"{data.items}\" as=\"item\">\n  <f:variable name=\"v\" value=\"1\"/><f:comment>x</f:comment><!-- c -->\n  <f:if condition=\"{item.title}\"><div{item -> f:mark.item(collection: 'desiderio_demoblock_items')}>{item.title}</div></f:if>\n</f:for>",
        ];
        yield 'then/else branches are alternatives: both roots marked' => [
            '<f:for each="{data.items}" as="item"><f:if condition="{item.link}"><f:then><a href="#">x</a></f:then><f:else><span>x</span></f:else></f:if></f:for>',
            '<f:for each="{data.items}" as="item"><f:if condition="{item.link}"><f:then><a href="#"{item -> f:mark.item(collection: \'desiderio_demoblock_items\')}>x</a></f:then><f:else><span{item -> f:mark.item(collection: \'desiderio_demoblock_items\')}>x</span></f:else></f:if></f:for>',
        ];
        yield 'component root without attributes' => [
            '<f:for each="{data.items}" as="item"><d:molecule.card class="c">x</d:molecule.card></f:for>',
            '<f:for each="{data.items}" as="item"><d:molecule.card class="c" attributes="{item -> f:mark.item(collection: \'desiderio_demoblock_items\', format: \'array\')}">x</d:molecule.card></f:for>',
        ];
        yield 'component root: array literal attributes are merged' => [
            '<f:for each="{data.items}" as="item"><d:molecule.card attributes="{role: \'listitem\'}">x</d:molecule.card></f:for>',
            '<f:for each="{data.items}" as="item"><d:molecule.card attributes="{item -> f:mark.item(collection: \'desiderio_demoblock_items\', format: \'array\') -> f:merge(with: {role: \'listitem\'})}">x</d:molecule.card></f:for>',
        ];
        yield 'component root: variable attributes are merged' => [
            '<f:for each="{data.items}" as="item"><d:atom.link href="#" attributes="{linkAttributes}" /></f:for>',
            '<f:for each="{data.items}" as="item"><d:atom.link href="#" attributes="{item -> f:mark.item(collection: \'desiderio_demoblock_items\', format: \'array\') -> f:merge(with: linkAttributes)}" /></f:for>',
        ];
        yield 'nested Collection through the outer loop variable' => [
            '<f:for each="{data.items}" as="plan"><div><f:for each="{plan.features}" as="feature"><p>{feature.text}</p></f:for></div></f:for>',
            '<f:for each="{data.items}" as="plan"><div{plan -> f:mark.item(collection: \'desiderio_demoblock_items\')}><f:for each="{plan.features}" as="feature"><p{feature -> f:mark.item(collection: \'features\')}>{feature.text}</p></f:for></div></f:for>',
        ];
        yield 'loop over a single-file field is transparent' => [
            '<f:for each="{data.items}" as="item"><f:for each="{item.image}" as="file"><button type="button">x</button></f:for></f:for>',
            '<f:for each="{data.items}" as="item"><f:for each="{item.image}" as="file"><button type="button"{item -> f:mark.item(collection: \'desiderio_demoblock_items\')}>x</button></f:for></f:for>',
        ];
        yield 'f:variable alias of a Collection' => [
            '<f:variable name="entries" value="{data.links}"/><f:for each="{entries}" as="link"><a href="#">x</a></f:for>',
            '<f:variable name="entries" value="{data.links}"/><f:for each="{entries}" as="link"><a href="#"{link -> f:mark.item(collection: \'desiderio_demoblock_links\')}>x</a></f:for>',
        ];
        yield 'HTML tag with a Fluid spread inside its open tag' => [
            '<f:for each="{data.items}" as="item"><div class="a"<f:for each="{attributes}" as="v" key="k"> {k}="{v}"</f:for>>x</div></f:for>',
            '<f:for each="{data.items}" as="item"><div class="a"<f:for each="{attributes}" as="v" key="k"> {k}="{v}"</f:for>{item -> f:mark.item(collection: \'desiderio_demoblock_items\')}>x</div></f:for>',
        ];
    }

    #[DataProvider('rewrites')]
    public function testRewriteAddsTheMarkerAndASecondRunChangesNothing(string $template, string $expected): void
    {
        $analysis = $this->analyze($template);
        $result = (new CollectionMarkerRewriter())->rewrite($analysis);

        self::assertSame([], $result['errors']);
        self::assertSame($expected, $result['source']);

        $again = $this->analyze($result['source']);
        self::assertSame([], $again->failures());
        $second = (new CollectionMarkerRewriter())->rewrite($again);
        self::assertSame($result['source'], $second['source']);
        self::assertSame([], $second['edits']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function unmarkableLoops(): iterable
    {
        yield 'two sibling roots' => ['<f:for each="{data.items}" as="item"><dt>a</dt><dd>b</dd></f:for>', CollectionLoop::SHAPE_MULTI];
        yield 'text only (JSON)' => ['[<f:for each="{data.items}" as="item">{"a":<f:format.json value="{item.a}"/>}</f:for>]', CollectionLoop::SHAPE_NONE];
        yield 'nothing rendered' => ['<f:for each="{data.items}" as="item"><f:variable name="x" value="{item.a}"/></f:for>', CollectionLoop::SHAPE_NONE];
        yield 'loop over a multi-file field is a root' => ['<f:for each="{data.items}" as="item"><f:for each="{item.gallery}" as="f"><img src="x"/></f:for></f:for>', CollectionLoop::SHAPE_SINGLE];
    }

    #[DataProvider('unmarkableLoops')]
    public function testLoopsWithoutOneHtmlOrComponentRootAreLeftAloneAndFailTheCheck(string $template, string $shape): void
    {
        $analysis = $this->analyze($template);
        self::assertSame($shape, $analysis->loops[0]->shape);
        $result = (new CollectionMarkerRewriter())->rewrite($analysis);
        self::assertStringNotContainsString('f:mark.item', $result['source']);
        self::assertNotSame([], $analysis->failures(), 'unmarked and unlisted');
    }

    public function testListedExceptionsPassTheCheckAndMayNotCarryAMarker(): void
    {
        $template = '<f:for each="{data.items}" as="item"><dt>a</dt><dd>b</dd></f:for><f:for each="{data.items}" as="item"><li>x</li></f:for>';
        $exceptions = new CollectionMarkerExceptions(['demo-block.items#1' => 'Term and description are siblings.']);
        $analysis = $this->analyze($template, $exceptions);
        self::assertSame('Term and description are siblings.', $analysis->loops[0]->exception);
        self::assertNull($analysis->loops[1]->exception, '#1 lists only the first loop over the field');

        $result = (new CollectionMarkerRewriter())->rewrite($analysis);
        self::assertSame([], $this->analyze($result['source'], $exceptions)->failures());

        $marked = '<f:for each="{data.items}" as="item"><li{item -> f:mark.item(collection: \'desiderio_demoblock_items\')}>x</li></f:for>';
        self::assertNotSame([], $this->analyze($marked, new CollectionMarkerExceptions(['demo-block.items' => 'listed']))->failures());
    }

    public function testWrongMarkersAndStrayMarkersFailTheCheck(): void
    {
        $wrongCollection = $this->analyze('<f:for each="{data.items}" as="item"><li{item -> f:mark.item(collection: \'items\')}>x</li></f:for>');
        self::assertStringContainsString('names collection "items" instead of "desiderio_demoblock_items"', implode("\n", $wrongCollection->failures()));

        $wrongVariable = $this->analyze('<f:for each="{data.items}" as="item"><li{data -> f:mark.item(collection: \'desiderio_demoblock_items\')}>x</li></f:for>');
        self::assertStringContainsString('marks "data" instead of the loop variable "item"', implode("\n", $wrongVariable->failures()));

        $stray = $this->analyze('<div{data -> f:mark.item(collection: \'desiderio_demoblock_items\')}></div>');
        self::assertStringContainsString('f:mark.item outside the root of a Collection loop', implode("\n", $stray->failures()));
    }

    public function testCollectionLoopsInFormsTheRulesDoNotReadAreReported(): void
    {
        $analysis = $this->analyze('<f:for each="{data.items -> f:slice(length: 2)}" as="item"><li>x</li></f:for><f:for each="{data.title}" as="t">x</f:for>');
        self::assertCount(1, $analysis->unresolved);
        self::assertStringContainsString('Collection "items"', $analysis->unresolved[0]);
    }

    public function testUnmergeableAttributesAndFluidViewHelperRootsAreNotRewritten(): void
    {
        $analysis = $this->analyze('<f:for each="{data.items}" as="item"><d:molecule.card attributes="{f:render(section: \'x\')}">x</d:molecule.card></f:for><f:for each="{data.links}" as="link"><f:link.typolink parameter="{link.url}">x</f:link.typolink></f:for>');
        $result = (new CollectionMarkerRewriter())->rewrite($analysis);
        self::assertCount(2, $result['errors']);
        self::assertSame($analysis->document->source, $result['source']);
    }

    public function testCollectionColumnsFollowContentBlocksPrefixing(): void
    {
        $fields = $this->fields();
        self::assertSame('desiderio_demoblock_items', $fields[0]->column);
        self::assertSame('demo_items', $fields[0]->table);
        self::assertSame(['image'], $fields[0]->singleFileFields);
        self::assertSame('features', $fields[0]->children[0]->column);
        self::assertSame('items.features', $fields[0]->children[0]->path);
        self::assertSame('demo_shared_links', $fields[1]->table);
    }

    public function testParserReadsBrokenFluidAsErrorsInsteadOfGuessing(): void
    {
        $parser = new MarkupParser();
        self::assertSame([], $parser->parse('<script>if (a<b && c>d) {}</script><f:for each="{x}" as="y"><br></f:for>')->errors);
        self::assertNotSame([], $parser->parse('<f:for each="{x}" as="y"><div></f:if></f:for>')->errors);
        self::assertNotSame([], $parser->parse('<f:for each="{x}" as="y"><div>')->errors);
    }

    private function analyze(string $template, ?CollectionMarkerExceptions $exceptions = null): BlockAnalysis
    {
        return (new CollectionLoopAnalyzer())->analyze('demo-block', $template, $this->fields(), $exceptions ?? new CollectionMarkerExceptions([]));
    }

    /**
     * @return list<CollectionField>
     */
    private function fields(): array
    {
        $file = tempnam(sys_get_temp_dir(), 'cb-config');
        self::assertIsString($file);
        file_put_contents($file, self::CONFIG);
        try {
            return CollectionField::fromConfigFile($file);
        } finally {
            unlink($file);
        }
    }
}
