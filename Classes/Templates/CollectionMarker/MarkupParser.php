<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * Reads a Fluid template into a tree of elements, text and comments with
 * exact byte offsets, tolerant of everything Fluid itself tolerates:
 *
 * - quoted attribute values hold inline expressions with ">" and quotes
 *   (`condition="{a} > 1"`, `"{f:translate(key: 'x')}"`); a value ends at the
 *   first unescaped matching quote, exactly as Fluid's tag pattern reads it;
 * - bare inline expressions between attributes
 *   (`<div class="x"{row -> f:mark.item(collection: 'c')}>`), brace-balanced
 *   with quoted strings skipped;
 * - Fluid tags nested inside an HTML open tag (desiderio's attribute spread:
 *   `<div class="x"<f:for each="{attributes}" ...> {k}="{v}"</f:for>>`);
 * - `<f:comment>` bodies, HTML comments and CDATA are opaque;
 * - `<script>`/`<style>` bodies are raw text in which only Fluid tags count;
 * - HTML void elements, self-closing tags, and HTML elements left open (their
 *   close is implied by the parent's close tag, as browsers do).
 *
 * Unbalanced Fluid tags and stray close tags are reported as errors instead
 * of guessed around: a codemod must never splice into a misread tree.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class MarkupParser
{
    private const array VOID_ELEMENTS = [
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr',
    ];

    private const array RAW_TEXT_ELEMENTS = ['script', 'style'];

    private string $source = '';

    private int $length = 0;

    /** @var list<string> */
    private array $errors = [];

    public function parse(string $source): MarkupDocument
    {
        $this->source = $source;
        $this->length = strlen($source);
        $this->errors = [];

        $root = new MarkupNode(MarkupNode::ELEMENT, '#document', 0, $this->length);
        $root->openEnd = 0;
        $root->closeStart = $this->length;
        /** @var list<MarkupNode> $stack */
        $stack = [$root];
        $position = 0;
        $textStart = 0;

        while ($position < $this->length) {
            $lt = strpos($source, '<', $position);
            if ($lt === false) {
                break;
            }
            $top = $stack[count($stack) - 1];
            $inRawText = !$top->isFluidTag() && in_array(strtolower($top->name), self::RAW_TEXT_ELEMENTS, true);

            if (!$inRawText && str_starts_with(substr($source, $lt, 4), '<!--')) {
                $close = strpos($source, '-->', $lt + 4);
                $end = $close === false ? $this->length : $close + 3;
                if ($close === false) {
                    $this->error($lt, 'unterminated HTML comment');
                }
                $this->appendText($top, $textStart, $lt);
                $this->appendChild($top, new MarkupNode(MarkupNode::COMMENT, '#comment', $lt, $end));
                $position = $textStart = $end;
                continue;
            }
            if (!$inRawText && str_starts_with(substr($source, $lt, 9), '<![CDATA[')) {
                $close = strpos($source, ']]>', $lt + 9);
                $position = $close === false ? $this->length : $close + 3;
                continue;
            }

            $isClose = ($source[$lt + 1] ?? '') === '/';
            $name = $this->nameAt($lt + ($isClose ? 2 : 1));
            if ($name === '' || ($inRawText && !str_contains($name, ':') && !($isClose && strcasecmp($name, $top->name) === 0))) {
                $position = $lt + 1;
                continue;
            }

            $this->appendText($top, $textStart, $lt);
            if ($isClose) {
                $gt = strpos($source, '>', $lt);
                $end = $gt === false ? $this->length : $gt + 1;
                $this->closeElement($stack, $name, $lt, $end);
                $position = $textStart = $end;
                continue;
            }

            $element = $this->readOpenTag($lt, $name);
            $this->appendChild($top, $element);
            $position = $textStart = $element->openEnd;
            if ($element->selfClosing || (!$element->isFluidTag() && in_array(strtolower($name), self::VOID_ELEMENTS, true))) {
                $element->end = $element->closeStart = $element->openEnd;
                continue;
            }
            if ($name === 'f:comment') {
                $close = strpos($source, '</f:comment>', $element->openEnd);
                if ($close === false) {
                    $this->error($lt, 'unterminated f:comment');
                    $close = $this->length;
                }
                $element->closeStart = $close;
                $element->end = min($this->length, $close + strlen('</f:comment>'));
                $position = $textStart = $element->end;
                continue;
            }
            $stack[] = $element;
        }

        $top = $stack[count($stack) - 1];
        $this->appendText($top, $textStart, $this->length);
        foreach (array_slice($stack, 1) as $open) {
            if ($open->isFluidTag()) {
                $this->error($open->start, 'unclosed <' . $open->name . '>');
            }
            $open->closeStart = $open->end = $this->length;
        }

        return new MarkupDocument($source, $root, $this->errors);
    }

    /**
     * @param list<MarkupNode> $stack
     */
    private function closeElement(array &$stack, string $name, int $start, int $end): void
    {
        for ($index = count($stack) - 1; $index > 0; --$index) {
            if ($this->sameName($stack[$index]->name, $name)) {
                foreach (array_slice($stack, $index + 1) as $implied) {
                    if ($implied->isFluidTag()) {
                        $this->error($implied->start, '<' . $implied->name . '> is closed by </' . $name . '>');
                    }
                    $implied->closeStart = $implied->end = $start;
                }
                $stack[$index]->closeStart = $start;
                $stack[$index]->end = $end;
                $stack = array_slice($stack, 0, $index);
                return;
            }
        }
        $this->error($start, 'stray </' . $name . '>');
    }

    private function sameName(string $open, string $close): bool
    {
        return str_contains($open, ':') ? $open === $close : strcasecmp($open, $close) === 0;
    }

    private function readOpenTag(int $start, string $name): MarkupNode
    {
        $element = new MarkupNode(MarkupNode::ELEMENT, $name, $start, $this->length);
        $position = $start + 1 + strlen($name);
        while (true) {
            $position = $this->skipWhitespace($position);
            if ($position >= $this->length) {
                $this->error($start, 'unterminated <' . $name . '> open tag');
                $element->insertAt = $element->openEnd = $this->length;
                return $element;
            }
            $char = $this->source[$position];
            if ($char === '>') {
                $element->insertAt = $position;
                $element->openEnd = $position + 1;
                return $element;
            }
            if ($char === '/' && ($this->source[$position + 1] ?? '') === '>') {
                $element->insertAt = $position;
                $element->openEnd = $position + 2;
                $element->selfClosing = true;
                return $element;
            }
            if ($char === '{') {
                $end = $this->skipBraces($position);
                $element->attributes[] = $this->attribute('', substr($this->source, $position, $end - $position), $position, $end, $position, $end);
                $position = $end;
                continue;
            }
            if ($char === '<' && !$element->isFluidTag()) {
                $end = $this->skipNestedTag($position);
                $element->attributes[] = $this->attribute('', substr($this->source, $position, $end - $position), $position, $end, $position, $end);
                $position = $end;
                continue;
            }
            if (preg_match('/\G[^\s=>\/{<"\']+/', $this->source, $match, 0, $position) !== 1) {
                ++$position;
                continue;
            }
            $attributeName = $match[0];
            $attributeStart = $position;
            $position = $this->skipWhitespace($position + strlen($attributeName));
            if (($this->source[$position] ?? '') !== '=') {
                $element->attributes[] = $this->attribute($attributeName, null, $attributeStart, $attributeStart + strlen($attributeName), $position, $position);
                continue;
            }
            $position = $this->skipWhitespace($position + 1);
            $quote = $this->source[$position] ?? '';
            if ($quote === '"' || $quote === '\'') {
                $valueEnd = $this->skipQuoted($position);
                $element->attributes[] = $this->attribute(
                    $attributeName,
                    substr($this->source, $position + 1, $valueEnd - $position - 2),
                    $attributeStart,
                    $valueEnd,
                    $position + 1,
                    $valueEnd - 1,
                );
                $position = $valueEnd;
                continue;
            }
            preg_match('/\G[^\s>]*/', $this->source, $match, 0, $position);
            $value = $match[0] ?? '';
            $element->attributes[] = $this->attribute($attributeName, $value, $attributeStart, $position + strlen($value), $position, $position + strlen($value));
            $position += strlen($value);
        }
    }

    /**
     * @return array{name: string, value: string|null, start: int, end: int, valueStart: int, valueEnd: int}
     */
    private function attribute(string $name, ?string $value, int $start, int $end, int $valueStart, int $valueEnd): array
    {
        return ['name' => $name, 'value' => $value, 'start' => $start, 'end' => $end, 'valueStart' => $valueStart, 'valueEnd' => $valueEnd];
    }

    /**
     * A Fluid element written inside an HTML open tag; returns the offset past
     * its close tag (nested Fluid tags of any name are balanced).
     */
    private function skipNestedTag(int $start): int
    {
        $depth = 0;
        $position = $start;
        while ($position < $this->length) {
            $lt = strpos($this->source, '<', $position);
            if ($lt === false) {
                break;
            }
            $isClose = ($this->source[$lt + 1] ?? '') === '/';
            $name = $this->nameAt($lt + ($isClose ? 2 : 1));
            if ($name === '' || !str_contains($name, ':')) {
                if ($depth === 0) {
                    break;
                }
                $position = $lt + 1;
                continue;
            }
            if ($isClose) {
                $gt = strpos($this->source, '>', $lt);
                $position = $gt === false ? $this->length : $gt + 1;
                if (--$depth <= 0) {
                    return $position;
                }
                continue;
            }
            $tag = $this->readOpenTag($lt, $name);
            $position = $tag->openEnd;
            if (!$tag->selfClosing) {
                ++$depth;
            } elseif ($depth === 0) {
                return $position;
            }
        }
        $this->error($start, 'unbalanced Fluid tag inside an HTML open tag');
        return max($start + 1, $position);
    }

    /**
     * Offset past a brace-balanced inline expression starting at "{".
     */
    private function skipBraces(int $start): int
    {
        $depth = 0;
        for ($position = $start; $position < $this->length; ++$position) {
            $char = $this->source[$position];
            if ($char === '\'' || $char === '"') {
                $position = $this->skipQuoted($position) - 1;
                continue;
            }
            if ($char === '{') {
                ++$depth;
            } elseif ($char === '}' && --$depth === 0) {
                return $position + 1;
            }
        }
        $this->error($start, 'unbalanced inline expression');
        return $this->length;
    }

    /**
     * Offset past a quoted string starting at its opening quote; a backslash
     * escapes the next character.
     */
    private function skipQuoted(int $start): int
    {
        $quote = $this->source[$start];
        for ($position = $start + 1; $position < $this->length; ++$position) {
            $char = $this->source[$position];
            if ($char === '\\') {
                ++$position;
                continue;
            }
            if ($char === $quote) {
                return $position + 1;
            }
        }
        $this->error($start, 'unterminated quoted value');
        return $this->length;
    }

    private function skipWhitespace(int $position): int
    {
        return $position + strspn($this->source, " \t\r\n", $position);
    }

    private function nameAt(int $position): string
    {
        return preg_match('/\G[A-Za-z][A-Za-z0-9._:-]*/', $this->source, $match, 0, $position) === 1 ? $match[0] : '';
    }

    private function appendText(MarkupNode $parent, int $start, int $end): void
    {
        if ($end > $start) {
            $this->appendChild($parent, new MarkupNode(MarkupNode::TEXT, '#text', $start, $end));
        }
    }

    private function appendChild(MarkupNode $parent, MarkupNode $child): void
    {
        $child->parent = $parent;
        $parent->children[] = $child;
    }

    private function error(int $offset, string $message): void
    {
        $this->errors[] = 'line ' . (substr_count(substr($this->source, 0, $offset), "\n") + 1) . ': ' . $message;
    }
}
