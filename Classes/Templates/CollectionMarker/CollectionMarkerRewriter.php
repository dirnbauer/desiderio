<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * Adds the missing row markers to an analysed template. Only single-root
 * loops that are not listed exceptions are touched, and only roots without a
 * marker, so a second run changes nothing.
 *
 * - HTML root: `{<as> -> f:mark.item(collection: '<column>')}` right before
 *   the open tag's ">" (or "/>"). No space is written in front of it: the
 *   ViewHelper's output starts with one, and outside edit mode it renders
 *   '' - the published markup stays byte-identical.
 * - `d:` component root: `attributes="{<as> -> f:mark.item(collection:
 *   '<column>', format: 'array')}"` after the last argument. An existing
 *   `attributes` value (an array literal or a variable) is kept and merged:
 *   `{<as> -> f:mark.item(...) -> f:merge(with: <existing>)}` - outside edit
 *   mode the marker is [] and the merge returns the existing array unchanged.
 * - Any other root (a tag-based Fluid ViewHelper) is not rewritten; it needs
 *   a listed exception or a hand-written marker.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class CollectionMarkerRewriter
{
    /**
     * @return array{source: string, edits: list<string>, errors: list<string>}
     */
    public function rewrite(BlockAnalysis $analysis): array
    {
        $source = $analysis->document->source;
        /** @var array<int, array{start: int, end: int, text: string, note: string}> $edits */
        $edits = [];
        $errors = [];
        foreach ($analysis->loops as $loop) {
            if ($loop->exception !== null || $loop->shape !== CollectionLoop::SHAPE_SINGLE) {
                continue;
            }
            foreach ($loop->roots as $root) {
                if (isset($loop->markers[$root->start])) {
                    continue;
                }
                $line = substr_count(substr($source, 0, $root->start), "\n") + 1;
                $edit = $this->editFor($loop, $root);
                if (is_string($edit)) {
                    $errors[] = 'line ' . $line . ': ' . $edit;
                    continue;
                }
                $edit['note'] = 'line ' . $line . ': ' . $loop->key() . ' <' . $root->name . '> (' . CollectionLoop::ruleFor($root) . ')';
                $edits[$edit['start']] = $edit;
            }
        }
        krsort($edits);
        foreach ($edits as $edit) {
            $source = substr_replace($source, $edit['text'], $edit['start'], $edit['end'] - $edit['start']);
        }

        return ['source' => $source, 'edits' => array_reverse(array_column($edits, 'note')), 'errors' => $errors];
    }

    /**
     * @return array{start: int, end: int, text: string}|string the splice, or why there is none
     */
    private function editFor(CollectionLoop $loop, MarkupNode $root): array|string
    {
        $collection = $loop->field->column;
        switch (CollectionLoop::ruleFor($root)) {
            case CollectionLoop::RULE_HTML:
                return [
                    'start' => $root->insertAt,
                    'end' => $root->insertAt,
                    'text' => '{' . $loop->as . ' -> f:mark.item(collection: \'' . $collection . '\')}',
                ];
            case CollectionLoop::RULE_COMPONENT:
                $marker = $loop->as . ' -> f:mark.item(collection: \'' . $collection . '\', format: \'array\')';
                $existing = $root->attributeSpan('attributes');
                if ($existing === null) {
                    $after = $root->attributes === [] ? $root->start + 1 + strlen($root->name) : max(array_column($root->attributes, 'end'));
                    return ['start' => $after, 'end' => $after, 'text' => ' attributes="{' . $marker . '}"'];
                }
                $with = $this->mergeOperand((string)$existing['value']);
                if ($with === null) {
                    return '<' . $root->name . ' attributes="' . $existing['value'] . '"> cannot be merged with the marker (expected an array literal or a variable)';
                }
                return [
                    'start' => $existing['valueStart'],
                    'end' => $existing['valueEnd'],
                    'text' => '{' . $marker . ' -> f:merge(with: ' . $with . ')}',
                ];
            default:
                return '<' . $root->name . '> is a Fluid ViewHelper root; mark it by hand or list the loop as an exception';
        }
    }

    /**
     * The existing `attributes` value as an f:merge `with:` operand: a
     * variable path stays a path, an array literal stays the literal.
     */
    private function mergeOperand(string $value): ?string
    {
        $value = trim($value);
        if (preg_match('/^\{\s*([A-Za-z_][A-Za-z0-9_.]*)\s*\}$/', $value, $match) === 1) {
            return $match[1];
        }
        if (str_starts_with($value, '{') && str_ends_with($value, '}') && !str_contains($value, '->') && preg_match('/^\{\s*[\'"]?[A-Za-z_-][A-Za-z0-9_-]*[\'"]?\s*:/', $value) === 1) {
            return $value;
        }
        return null;
    }
}
