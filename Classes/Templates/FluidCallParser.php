<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * A minimal scanner over Fluid source: which namespace prefixes are declared,
 * which are used, and which arguments a call site passes.
 *
 * Fluid's own parser throws on the first unresolvable tag, so the linter needs
 * a tolerant reader that keeps going and reports line numbers. This is that
 * reader — deliberately regex/offset based, never a full parse.
 *
 * @internal used by TemplateLinter only
 */
final class FluidCallParser
{
    private const string NAMESPACE_URI_PREFIX = 'http://typo3.org/ns/';

    /**
     * Prefixes that appear in tag syntax, inline syntax or inline chains.
     */
    private const array USED_PREFIX_PATTERNS = [
        '/<\/?([a-z][a-zA-Z0-9]*):[a-zA-Z][a-zA-Z0-9.]*(?=[\s\/>])/',
        '/\{\s*([a-z][a-zA-Z0-9]*):[a-zA-Z][a-zA-Z0-9.]*\s*\(/',
        '/->\s*([a-z][a-zA-Z0-9]*):[a-zA-Z][a-zA-Z0-9.]*\s*\(/',
    ];

    /**
     * Blank out Fluid and HTML comments while keeping line numbers intact.
     */
    public function stripComments(string $source): string
    {
        return (string)preg_replace_callback(
            '/<f:comment>.*?<\/f:comment>|<!--.*?-->/s',
            static fn(array $match): string => (string)preg_replace('/[^\n]/', ' ', $match[0]),
            $source,
        );
    }

    public function lineAt(string $source, int $offset): int
    {
        return substr_count(substr($source, 0, $offset), "\n") + 1;
    }

    /**
     * Namespaces declared on the root tag (xmlns:) or via {namespace}.
     *
     * @return array<string, array{uri: string, php: string|null, line: int}>
     */
    public function declaredNamespaces(string $source): array
    {
        $declared = [];
        if (preg_match('/<([a-z0-9]+)(?:[^>]*?)\s+xmlns:([a-zA-Z0-9.]+)=("[^"]+"|\'[^\']+\')[^>]*>/', $source, $tagMatch, PREG_OFFSET_CAPTURE) === 1) {
            $tag = $tagMatch[0][0];
            $tagOffset = $tagMatch[0][1];
            preg_match_all('/xmlns:([a-zA-Z0-9.]+)=("[^"]+"|\'[^\']+\')/', $tag, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
            foreach ($matches as $match) {
                $prefix = $match[1][0];
                $uri = trim($match[2][0], '"\'');
                $php = str_starts_with($uri, self::NAMESPACE_URI_PREFIX)
                    ? str_replace('/', '\\', substr($uri, strlen(self::NAMESPACE_URI_PREFIX)))
                    : null;
                $declared[$prefix] = ['uri' => $uri, 'php' => $php, 'line' => $this->lineAt($source, $tagOffset + $match[0][1])];
            }
        }
        if (preg_match_all('/(?<!\\\\)\{namespace\s*(?P<identifier>[a-zA-Z*]+[a-zA-Z0-9.*]*)\s*(?:=\s*(?P<php>[A-Za-z0-9\\\\]+))?\s*}/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches as $match) {
                $prefix = $match['identifier'][0];
                $php = isset($match['php']) ? $match['php'][0] : null;
                $declared[$prefix] = ['uri' => (string)$php, 'php' => $php, 'line' => $this->lineAt($source, $match[0][1])];
            }
        }

        return $declared;
    }

    /**
     * @return array<string, int> prefix => first line
     */
    public function usedPrefixes(string $source): array
    {
        $used = [];
        foreach (self::USED_PREFIX_PATTERNS as $pattern) {
            if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) < 1) {
                continue;
            }
            foreach ($matches as $match) {
                $prefix = $match[1][0];
                $line = $this->lineAt($source, $match[0][1]);
                $used[$prefix] = isset($used[$prefix]) ? min($used[$prefix], $line) : $line;
            }
        }

        return $used;
    }

    /**
     * Tag and inline call sites of a prefix with their argument names.
     *
     * @return list<array{name: string, arguments: list<string>, line: int}>
     */
    public function callsForPrefix(string $source, string $prefix): array
    {
        $calls = [];
        $quoted = preg_quote($prefix, '/');
        if (preg_match_all('/<' . $quoted . ':([a-zA-Z][a-zA-Z0-9.]*)(?=[\s\/>])/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches as $match) {
                $calls[] = [
                    'name' => $match[1][0],
                    'arguments' => $this->tagAttributeNames($source, $match[0][1] + strlen($match[0][0])),
                    'line' => $this->lineAt($source, $match[0][1]),
                ];
            }
        }
        if (preg_match_all('/(?:\{\s*|->\s*)' . $quoted . ':([a-zA-Z][a-zA-Z0-9.]*)\s*\(/', $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) > 0) {
            foreach ($matches as $match) {
                $calls[] = [
                    'name' => $match[1][0],
                    'arguments' => $this->inlineArgumentNames($source, $match[0][1] + strlen($match[0][0])),
                    'line' => $this->lineAt($source, $match[0][1]),
                ];
            }
        }

        return $calls;
    }

    /**
     * Partial names of every `f:render partial="…"` with a literal name.
     *
     * @return list<array{name: string, line: int}>
     */
    public function renderedPartials(string $source): array
    {
        $partials = [];
        foreach (['/<f:render\b[^>]*?\bpartial\s*=\s*["\']([^"\']*)["\']/' => false, '/f:render\s*\(([^)]*)\)/' => true] as $pattern => $isInline) {
            if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) < 1) {
                continue;
            }
            foreach ($matches as $match) {
                $name = $match[1][0];
                if ($isInline) {
                    if (preg_match('/\bpartial\s*:\s*["\']([^"\']*)["\']/', $name, $inline) !== 1) {
                        continue;
                    }
                    $name = $inline[1];
                }
                if ($name === '' || str_contains($name, '{')) {
                    continue;
                }
                $partials[] = ['name' => $name, 'line' => $this->lineAt($source, $match[0][1])];
            }
        }

        return $partials;
    }

    /**
     * Attribute names of a tag, starting right after the tag name. Values may
     * contain ">" inside quotes, so this walks quoted strings instead of
     * matching up to the next ">".
     *
     * @return list<string>
     */
    private function tagAttributeNames(string $source, int $offset): array
    {
        $names = [];
        $length = strlen($source);
        while ($offset < $length) {
            $offset = $this->skipWhitespace($source, $offset);
            if ($offset >= $length || $source[$offset] === '>' || $source[$offset] === '/') {
                break;
            }
            if (preg_match('/\G([a-zA-Z_][a-zA-Z0-9_.:-]*)/', $source, $nameMatch, 0, $offset) !== 1) {
                break;
            }
            $names[] = $nameMatch[1];
            $offset = $this->skipWhitespace($source, $offset + strlen($nameMatch[0]));
            if ($offset >= $length || $source[$offset] !== '=') {
                continue;
            }
            $offset = $this->skipWhitespace($source, $offset + 1);
            if ($offset < $length && ($source[$offset] === '"' || $source[$offset] === "'")) {
                $offset = $this->skipQuotedString($source, $offset);
                continue;
            }
            while ($offset < $length && !ctype_space($source[$offset]) && $source[$offset] !== '>') {
                $offset++;
            }
        }

        return $names;
    }

    /**
     * Top-level argument keys of an inline ViewHelper call, starting right
     * after the opening parenthesis.
     *
     * @return list<string>
     */
    private function inlineArgumentNames(string $source, int $offset): array
    {
        $names = [];
        $depth = 0;
        $length = strlen($source);
        $expectKey = true;
        while ($offset < $length) {
            $char = $source[$offset];
            if ($char === '"' || $char === "'") {
                $offset = $this->skipQuotedString($source, $offset);
                continue;
            }
            if ($char === '(' || $char === '{' || $char === '[') {
                $depth++;
            } elseif ($char === ')' || $char === '}' || $char === ']') {
                if ($depth === 0) {
                    break;
                }
                $depth--;
            } elseif ($depth === 0 && $char === ',') {
                $expectKey = true;
            } elseif ($depth === 0 && $expectKey && preg_match('/\G\s*([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', $source, $keyMatch, 0, $offset) === 1) {
                $names[] = $keyMatch[1];
                $expectKey = false;
                $offset += strlen($keyMatch[0]);
                continue;
            }
            $offset++;
        }

        return $names;
    }

    private function skipWhitespace(string $source, int $offset): int
    {
        $length = strlen($source);
        while ($offset < $length && ctype_space($source[$offset])) {
            $offset++;
        }

        return $offset;
    }

    private function skipQuotedString(string $source, int $offset): int
    {
        $quote = $source[$offset];
        $length = strlen($source);
        $offset++;
        while ($offset < $length) {
            if ($source[$offset] === '\\') {
                $offset += 2;
                continue;
            }
            if ($source[$offset] === $quote) {
                return $offset + 1;
            }
            $offset++;
        }

        return $length;
    }
}
