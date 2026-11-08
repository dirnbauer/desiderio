<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * One node of a Fluid template as MarkupParser reads it: an element (HTML tag,
 * Fluid ViewHelper tag, component tag), a run of text, or a comment. Offsets
 * are byte offsets into the source, so a rewrite can splice at exact places.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class MarkupNode
{
    public const string ELEMENT = 'element';
    public const string TEXT = 'text';
    public const string COMMENT = 'comment';

    /** @var list<MarkupNode> */
    public array $children = [];

    /**
     * Attributes of an element's open tag in source order. A bare inline
     * expression between attributes ({row -> f:mark.item(...)}) or a nested
     * Fluid tag inside an HTML open tag is recorded with an empty name.
     *
     * @var list<array{name: string, value: string|null, start: int, end: int, valueStart: int, valueEnd: int}>
     */
    public array $attributes = [];

    public ?MarkupNode $parent = null;

    /** Offset of the open tag's closing ">" (or "/" of "/>"); where an attribute is inserted. */
    public int $insertAt = 0;

    /** Offset just past the open tag. */
    public int $openEnd = 0;

    /** Offset of the close tag's "<" (or $end for void and self-closing elements). */
    public int $closeStart = 0;

    public bool $selfClosing = false;

    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly int $start,
        public int $end,
    ) {}

    public function isFluidTag(): bool
    {
        return $this->type === self::ELEMENT && str_contains($this->name, ':');
    }

    public function prefix(): string
    {
        return $this->isFluidTag() ? explode(':', $this->name, 2)[0] : '';
    }

    public function attribute(string $name): ?string
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute['name'] === $name) {
                return $attribute['value'];
            }
        }
        return null;
    }

    /**
     * @return array{name: string, value: string|null, start: int, end: int, valueStart: int, valueEnd: int}|null
     */
    public function attributeSpan(string $name): ?array
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute['name'] === $name) {
                return $attribute;
            }
        }
        return null;
    }

    public function openTag(string $source): string
    {
        return substr($source, $this->start, $this->openEnd - $this->start);
    }
}
