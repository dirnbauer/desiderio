<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * The checks the template linter can run. The backed value is what the
 * `--rule` option accepts and what the JSON report prints.
 */
enum LintRule: string
{
    case Parse = 'parse';
    case NamespaceUsage = 'namespace-usage';
    case PartialResolves = 'partial-resolves';
    case DeprecatedConstructs = 'deprecated-constructs';
    case ComponentArguments = 'component-arguments';

    /**
     * @return list<string>
     */
    public static function names(): array
    {
        return array_map(static fn(self $rule): string => $rule->value, self::cases());
    }
}
