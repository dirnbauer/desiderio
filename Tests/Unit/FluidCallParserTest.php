<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Templates\FluidCallParser;

/**
 * The tolerant Fluid scanner the linter reads templates with. It never parses
 * — it reports what a template declares, uses and passes, with line numbers,
 * even when the template is broken.
 */
final class FluidCallParserTest extends TestCase
{
    private FluidCallParser $parser;

    protected function setUp(): void
    {
        $this->parser = new FluidCallParser();
    }

    public function testCommentsAreBlankedWithoutMovingLineNumbers(): void
    {
        $source = "line1\n<f:comment>a\nb</f:comment>\n<!-- c -->\nlast\n";

        $stripped = $this->parser->stripComments($source);

        self::assertSame(substr_count($source, "\n"), substr_count($stripped, "\n"));
        self::assertStringNotContainsString('f:comment', $stripped);
        self::assertStringContainsString('last', $stripped);
    }

    public function testDeclaredNamespacesAreReadFromTheRootTagWithTheirLine(): void
    {
        $source = "\n<html xmlns:f=\"http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers\"\n      xmlns:d=\"http://typo3.org/ns/Webconsulting/Desiderio/ViewHelpers\"\n      xmlns:x=\"urn:custom\">\n</html>\n";

        $declared = $this->parser->declaredNamespaces($source);

        self::assertSame(['f', 'd', 'x'], array_keys($declared));
        self::assertSame('TYPO3\\CMS\\Fluid\\ViewHelpers', $declared['f']['php']);
        self::assertSame('Webconsulting\\Desiderio\\ViewHelpers', $declared['d']['php']);
        self::assertNull($declared['x']['php'], 'A non-typo3.org URI carries no PHP namespace');
        self::assertSame(2, $declared['f']['line']);
        self::assertSame(3, $declared['d']['line']);
    }

    public function testCurlyNamespaceDeclarationIsRecognised(): void
    {
        $declared = $this->parser->declaredNamespaces("{namespace d=Webconsulting\\Desiderio\\ViewHelpers}\n");

        self::assertSame('Webconsulting\\Desiderio\\ViewHelpers', $declared['d']['php']);
        self::assertSame(1, $declared['d']['line']);
    }

    public function testUsedPrefixesCoverTagInlineAndChainSyntaxAndKeepTheFirstLine(): void
    {
        $source = "<d:atom.icon name=\"x\"/>\n{a:helper(value: 1)}\n{value -> b:format.trim()}\n<d:atom.icon name=\"y\"/>\n";

        $used = $this->parser->usedPrefixes($source);

        self::assertSame(1, $used['d'], 'The first occurrence wins');
        self::assertSame(2, $used['a']);
        self::assertSame(3, $used['b']);
    }

    public function testTagCallArgumentsSurviveAngleBracketsInsideQuotedValues(): void
    {
        $source = '<d:atom.button label="a > b" class=\'c\' disabled/>';

        $calls = $this->parser->callsForPrefix($source, 'd');

        self::assertCount(1, $calls);
        self::assertSame('atom.button', $calls[0]['name']);
        self::assertSame(['label', 'class', 'disabled'], $calls[0]['arguments']);
    }

    public function testInlineCallReportsOnlyTopLevelArgumentKeys(): void
    {
        $source = '{d:molecule.card(title: "x", items: {a: 1, b: 2}, nested: d:atom.icon(name: "y"))}';

        $calls = $this->parser->callsForPrefix($source, 'd');

        self::assertSame('molecule.card', $calls[0]['name']);
        self::assertSame(['title', 'items', 'nested'], $calls[0]['arguments']);
    }

    public function testCallsOfAnotherPrefixAreIgnored(): void
    {
        self::assertSame([], $this->parser->callsForPrefix('<f:format.raw>x</f:format.raw>', 'd'));
    }

    public function testRenderedPartialsCollectTagAndInlineNamesAndSkipDynamicOnes(): void
    {
        $source = "<f:render partial=\"Pages/Header\" arguments=\"{_all}\"/>\n{f:render(partial: 'Pages/Footer')}\n<f:render partial=\"{dynamic}\"/>\n";

        $partials = $this->parser->renderedPartials($source);

        self::assertSame(
            [['name' => 'Pages/Header', 'line' => 1], ['name' => 'Pages/Footer', 'line' => 2]],
            $partials,
        );
    }
}
