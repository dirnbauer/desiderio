<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * The Collection loops of one Content Block template and everything the lint
 * has to say about them.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class BlockAnalysis
{
    /** @var list<CollectionLoop> */
    public array $loops = [];

    /** @var list<string> */
    public array $unresolved = [];

    /** @var list<string> */
    public array $strayMarkers = [];

    /**
     * @param list<CollectionField> $fields
     */
    public function __construct(
        public readonly string $block,
        public readonly MarkupDocument $document,
        public readonly array $fields,
    ) {}

    /**
     * @return list<string>
     */
    public function collectionIdentifiers(): array
    {
        return array_values(array_unique(array_map(static fn(CollectionField $field): string => $field->identifier, $this->allFields())));
    }

    /**
     * Root and nested Collections, depth first.
     *
     * @param list<CollectionField>|null $fields
     * @return list<CollectionField>
     */
    public function allFields(?array $fields = null): array
    {
        $all = [];
        foreach ($fields ?? $this->fields as $field) {
            $all[] = $field;
            $all = [...$all, ...$this->allFields($field->children)];
        }
        return $all;
    }

    /**
     * What `--check` and the lint test fail on.
     *
     * @return list<string>
     */
    public function failures(): array
    {
        $failures = [...$this->document->errors, ...$this->unresolved, ...$this->strayMarkers];
        foreach ($this->loops as $loop) {
            foreach ($loop->problems as $problem) {
                $failures[] = $problem;
            }
            if ($loop->exception === null && !$loop->isMarked()) {
                $failures[] = 'line ' . $loop->line . ': <f:for each="' . $loop->loop->attribute('each') . '"> over ' . $loop->field->column
                    . ' is neither marked nor a listed exception (' . $loop->shape . ' root)';
            }
            if ($loop->exception !== null && $loop->markers !== []) {
                $failures[] = 'line ' . $loop->line . ': ' . $loop->key() . ' is a listed exception but carries a marker';
            }
        }
        return $failures;
    }
}
