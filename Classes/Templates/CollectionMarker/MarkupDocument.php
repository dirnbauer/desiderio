<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * A parsed template: the source, its node tree and what the parser could not
 * read cleanly.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final readonly class MarkupDocument
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        public string $source,
        public MarkupNode $root,
        public array $errors,
    ) {}

    /**
     * Every element in document order.
     *
     * @return \Generator<int, MarkupNode>
     */
    public function elements(?MarkupNode $node = null): \Generator
    {
        foreach (($node ?? $this->root)->children as $child) {
            if ($child->type === MarkupNode::ELEMENT) {
                yield $child;
                yield from $this->elements($child);
            }
        }
    }
}
