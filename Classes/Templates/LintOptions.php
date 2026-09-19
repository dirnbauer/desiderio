<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * Immutable input of one linter run.
 *
 * Paths accept `EXT:key`, `EXT:key/sub/dir`, absolute or relative directories
 * and single template files. An empty rule list means "every rule", which the
 * constructor expands, so consumers never have to special-case it.
 */
final readonly class LintOptions
{
    /** @var list<LintRule> */
    public array $rules;

    /**
     * @param list<string> $paths
     * @param list<LintRule> $rules
     */
    public function __construct(
        public array $paths = ['EXT:desiderio'],
        public bool $strict = false,
        array $rules = [],
    ) {
        $this->rules = $rules === [] ? LintRule::cases() : $rules;
    }

    public function isRuleEnabled(LintRule $rule): bool
    {
        return in_array($rule, $this->rules, true);
    }
}
