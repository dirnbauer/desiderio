<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * One `<f:for>` over a Collection field in a Content Block template and what
 * the codemod makes of it.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class CollectionLoop
{
    /** The loop body renders exactly one root element per entry (per render path). */
    public const string SHAPE_SINGLE = 'single';
    /** The body renders several sibling roots per entry. */
    public const string SHAPE_MULTI = 'multi';
    /** The body renders text only (JSON, inline values) or nothing. */
    public const string SHAPE_NONE = 'none';

    /** Plain HTML root: an inline marker before the open tag's ">". */
    public const string RULE_HTML = 'html';
    /** `d:` component root: the marker array goes into its `attributes` argument. */
    public const string RULE_COMPONENT = 'component';
    /** Tag-based Fluid ViewHelper root: the marker array goes into `additionalAttributes`. */
    public const string RULE_FLUID_TAG = 'fluid-tag';

    /** @var list<MarkupNode> */
    public array $roots = [];

    public string $shape = self::SHAPE_NONE;

    /**
     * Root offset => the marker found on it: [variable, collection].
     *
     * @var array<int, array{variable: string, collection: string}>
     */
    public array $markers = [];

    /** @var list<string> */
    public array $problems = [];

    public ?string $exception = null;

    public function __construct(
        public readonly string $block,
        public readonly CollectionField $field,
        public readonly MarkupNode $loop,
        public readonly string $as,
        public readonly int $line,
        /** 1-based position among the loops over the same field in this template */
        public readonly int $occurrence,
    ) {}

    public function key(): string
    {
        return $this->block . '.' . $this->field->path;
    }

    public static function ruleFor(MarkupNode $root): string
    {
        if (!$root->isFluidTag()) {
            return self::RULE_HTML;
        }
        return $root->prefix() === 'd' ? self::RULE_COMPONENT : self::RULE_FLUID_TAG;
    }

    public function isMarked(): bool
    {
        return $this->roots !== [] && count($this->markers) === count($this->roots);
    }
}
