<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * Finds every `<f:for>` over a Collection field in a Content Block's frontend
 * template, works out what one iteration renders, and reads the row markers
 * already in place.
 *
 * A loop is recognised when `each` is `{<var>.<field>}` and `<var>` is `data`
 * (the content element) or the `as` variable of an enclosing loop over a
 * Collection that declares `<field>` (nested collections), or `{<alias>}` for
 * an `<f:variable name="<alias>" value="{<var>.<field>}"/>`. A loop that names
 * a Collection in any other form is reported as unresolved, so `--check`
 * cannot pass while a loop escapes the rules.
 *
 * Root rules: f:if/f:then/f:else, f:switch/f:case/f:defaultCase and
 * f:spaceless are transparent (alternative branches count once), and so is an
 * inner `<f:for>` over a File field of the entry with `maxitems: 1` (it renders
 * its body at most once - the gallery thumbnails);
 * f:variable, f:asset.*, f:comment and HTML comments render nothing; any
 * other element is a root, non-blank text makes the body text-shaped.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class CollectionLoopAnalyzer
{
    public const string MARKER_PATTERN = '/\{\s*([A-Za-z_][A-Za-z0-9_]*)\s*->\s*f:mark\.item\(\s*collection:\s*\'([^\']*)\'/';

    private const array TRANSPARENT = ['f:then', 'f:else', 'f:spaceless', 'f:case', 'f:defaultCase'];

    private const array NO_OUTPUT = ['f:variable', 'f:comment', 'f:argument'];

    private readonly MarkupParser $parser;

    public function __construct(?MarkupParser $parser = null)
    {
        $this->parser = $parser ?? new MarkupParser();
    }

    public function analyzeBlock(string $blockDirectory, ?CollectionMarkerExceptions $exceptions = null): BlockAnalysis
    {
        $block = basename($blockDirectory);
        $template = $blockDirectory . '/templates/frontend.html';
        $fields = CollectionField::fromConfigFile($blockDirectory . '/config.yaml');

        return $this->analyze($block, (string)file_get_contents($template), $fields, $exceptions ?? new CollectionMarkerExceptions());
    }

    /**
     * @param list<CollectionField> $fields
     */
    public function analyze(string $block, string $source, array $fields, CollectionMarkerExceptions $exceptions): BlockAnalysis
    {
        $document = $this->parser->parse($source);
        $analysis = new BlockAnalysis($block, $document, $fields);
        $occurrences = [];
        $this->walk($document->root, ['data' => $fields], [], $analysis, $occurrences);

        foreach ($analysis->loops as $loop) {
            $loop->exception = $exceptions->reasonFor($loop);
        }
        $this->findStrayMarkers($analysis);

        return $analysis;
    }

    /**
     * @param array<string, list<CollectionField>> $scope variable => the Collections reachable as <variable>.<identifier>
     * @param array<string, CollectionField> $aliases variable => the Collection it holds as a list
     * @param array<string, int> $occurrences
     */
    private function walk(MarkupNode $node, array $scope, array $aliases, BlockAnalysis $analysis, array &$occurrences): void
    {
        foreach ($node->children as $child) {
            if ($child->type !== MarkupNode::ELEMENT) {
                continue;
            }
            if ($child->name === 'f:variable') {
                $name = $child->attribute('name');
                $field = $this->resolve($child->attribute('value'), $scope, $aliases);
                if ($name !== null && $field !== null) {
                    $aliases[$name] = $field;
                }
            }
            $childScope = $scope;
            if ($child->name === 'f:for') {
                $each = $child->attribute('each');
                $field = $this->resolve($each, $scope, $aliases);
                $as = $child->attribute('as') ?? '';
                if ($field !== null) {
                    $occurrence = $occurrences[$field->path] = ($occurrences[$field->path] ?? 0) + 1;
                    $loop = new CollectionLoop(
                        $analysis->block,
                        $field,
                        $child,
                        $as,
                        substr_count(substr($analysis->document->source, 0, $child->start), "\n") + 1,
                        $occurrence,
                    );
                    $this->inspectBody($loop, $analysis->document->source);
                    $analysis->loops[] = $loop;
                    $childScope[$as] = $field->children;
                } else {
                    $this->reportUnresolved($child, $each, $scope, $aliases, $analysis);
                    unset($childScope[$as]);
                }
            }
            $this->walk($child, $childScope, $aliases, $analysis, $occurrences);
        }
    }

    /**
     * @param array<string, list<CollectionField>> $scope
     * @param array<string, CollectionField> $aliases
     */
    private function resolve(?string $expression, array $scope, array $aliases): ?CollectionField
    {
        if ($expression === null || preg_match('/^\{\s*([A-Za-z_][A-Za-z0-9_]*)(?:\.([A-Za-z_][A-Za-z0-9_]*))?\s*\}$/', trim($expression), $match) !== 1) {
            return null;
        }
        if (!isset($match[2])) {
            return $aliases[$match[1]] ?? null;
        }
        foreach ($scope[$match[1]] ?? [] as $field) {
            if ($field->identifier === $match[2]) {
                return $field;
            }
        }
        return null;
    }

    /**
     * An f:for that names a Collection of this block in a form the rules do
     * not read (a filter chain, an unknown alias, an index) is surfaced.
     *
     * @param array<string, list<CollectionField>> $scope
     * @param array<string, CollectionField> $aliases
     */
    private function reportUnresolved(MarkupNode $loop, ?string $each, array $scope, array $aliases, BlockAnalysis $analysis): void
    {
        if ($each === null || preg_match_all('/\b([A-Za-z_][A-Za-z0-9_]*)\.([A-Za-z_][A-Za-z0-9_]*)/', $each, $matches, PREG_SET_ORDER) === 0) {
            return;
        }
        $identifiers = $analysis->collectionIdentifiers();
        foreach ($matches as $match) {
            if (!in_array($match[2], $identifiers, true)) {
                continue;
            }
            // {data.title} where title is no Collection of data, or a file
            // field named like a nested Collection: in scope and not a list.
            if (isset($scope[$match[1]]) && !in_array($match[2], array_map(static fn(CollectionField $field): string => $field->identifier, $scope[$match[1]]), true)) {
                continue;
            }
            $analysis->unresolved[] = 'line ' . (substr_count(substr($analysis->document->source, 0, $loop->start), "\n") + 1)
                . ': <f:for each="' . $each . '"> loops over Collection "' . $match[2] . '" in a form the codemod does not read';
            return;
        }
    }

    private function inspectBody(CollectionLoop $loop, string $source): void
    {
        [$count, $roots, $text] = $this->roots($loop->loop->children, $source, $loop);
        $loop->roots = $roots;
        $loop->shape = match (true) {
            $text || $count === 0 => CollectionLoop::SHAPE_NONE,
            $count === 1 => CollectionLoop::SHAPE_SINGLE,
            default => CollectionLoop::SHAPE_MULTI,
        };
        foreach ($roots as $root) {
            $marker = $this->markerOn($root, $source);
            if ($marker === null) {
                continue;
            }
            $loop->markers[$root->start] = $marker;
            if ($marker['variable'] !== $loop->as) {
                $loop->problems[] = 'marker on line ' . $this->line($source, $root->start) . ' marks "' . $marker['variable'] . '" instead of the loop variable "' . $loop->as . '"';
            }
            if ($marker['collection'] !== $loop->field->column) {
                $loop->problems[] = 'marker on line ' . $this->line($source, $root->start) . ' names collection "' . $marker['collection'] . '" instead of "' . $loop->field->column . '"';
            }
        }
    }

    /**
     * What a sequence of sibling nodes renders: the most roots any one render
     * path produces, every root element on any path, and whether non-blank
     * text is rendered next to them.
     *
     * @param list<MarkupNode> $nodes
     * @return array{0: int, 1: list<MarkupNode>, 2: bool}
     */
    private function roots(array $nodes, string $source, CollectionLoop $loop): array
    {
        $count = 0;
        $roots = [];
        $text = false;
        foreach ($nodes as $node) {
            if ($node->type === MarkupNode::TEXT) {
                $text = $text || trim(substr($source, $node->start, $node->end - $node->start)) !== '';
                continue;
            }
            if ($node->type === MarkupNode::COMMENT || in_array($node->name, self::NO_OUTPUT, true) || str_starts_with($node->name, 'f:asset.')) {
                continue;
            }
            $alternatives = match (true) {
                $node->name === 'f:if' => $this->ifBranches($node),
                $node->name === 'f:switch' => array_map(
                    static fn(MarkupNode $case): array => $case->children,
                    array_values(array_filter($node->children, static fn(MarkupNode $case): bool => in_array($case->name, ['f:case', 'f:defaultCase'], true))),
                ),
                in_array($node->name, self::TRANSPARENT, true), $this->isSingleFileLoop($node, $loop) => [$node->children],
                default => null,
            };
            if ($alternatives === null) {
                ++$count;
                $roots[] = $node;
                continue;
            }
            if ($node->name === 'f:if' && $node->selfClosing && ($node->attribute('then') !== null || $node->attribute('else') !== null)) {
                $text = true;
                continue;
            }
            $most = 0;
            foreach ($alternatives as $alternative) {
                [$branchCount, $branchRoots, $branchText] = $this->roots($alternative, $source, $loop);
                $most = max($most, $branchCount);
                $roots = [...$roots, ...$branchRoots];
                $text = $text || $branchText;
            }
            $count += $most;
        }
        return [$count, $roots, $text];
    }

    private function isSingleFileLoop(MarkupNode $node, CollectionLoop $loop): bool
    {
        if ($node->name !== 'f:for' || preg_match('/^\{\s*([A-Za-z_][A-Za-z0-9_]*)\.([A-Za-z_][A-Za-z0-9_]*)\s*\}$/', trim((string)$node->attribute('each')), $match) !== 1) {
            return false;
        }
        return $match[1] === $loop->as && in_array($match[2], $loop->field->singleFileFields, true);
    }

    /**
     * @return list<list<MarkupNode>>
     */
    private function ifBranches(MarkupNode $if): array
    {
        $branches = array_values(array_filter($if->children, static fn(MarkupNode $child): bool => in_array($child->name, ['f:then', 'f:else'], true)));
        if ($branches === []) {
            return [$if->children];
        }
        return array_map(static fn(MarkupNode $branch): array => $branch->children, $branches);
    }

    /**
     * @return array{variable: string, collection: string}|null
     */
    public function markerOn(MarkupNode $root, string $source): ?array
    {
        $carrier = match (CollectionLoop::ruleFor($root)) {
            CollectionLoop::RULE_HTML => implode(' ', array_map(
                static fn(array $attribute): string => (string)$attribute['value'],
                array_filter($root->attributes, static fn(array $attribute): bool => $attribute['name'] === ''),
            )),
            CollectionLoop::RULE_COMPONENT => (string)$root->attribute('attributes'),
            default => (string)$root->attribute('additionalAttributes'),
        };
        if (preg_match(self::MARKER_PATTERN, $carrier, $match) !== 1) {
            return null;
        }
        return ['variable' => $match[1], 'collection' => $match[2]];
    }

    /**
     * Every f:mark.item in the template must sit on the root of a recognised
     * Collection loop, in the place its rule puts it.
     */
    private function findStrayMarkers(BlockAnalysis $analysis): void
    {
        $source = $analysis->document->source;
        $accepted = [];
        foreach ($analysis->loops as $loop) {
            foreach ($loop->roots as $root) {
                if (isset($loop->markers[$root->start])) {
                    $accepted[] = [$root->start, $root->openEnd];
                }
            }
        }
        $comments = [];
        foreach ($analysis->document->elements() as $element) {
            if ($element->name === 'f:comment') {
                $comments[] = [$element->start, $element->end];
            }
        }
        if (preg_match_all('/f:mark\.item\b/', $source, $matches, PREG_OFFSET_CAPTURE) === 0) {
            return;
        }
        foreach ($matches[0] as [, $offset]) {
            foreach ([...$accepted, ...$comments] as [$start, $end]) {
                if ($offset >= $start && $offset < $end) {
                    continue 2;
                }
            }
            $analysis->strayMarkers[] = 'line ' . $this->line($source, $offset) . ': f:mark.item outside the root of a Collection loop';
        }
    }

    private function line(string $source, int $offset): int
    {
        return substr_count(substr($source, 0, $offset), "\n") + 1;
    }
}
