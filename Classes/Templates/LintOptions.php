<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * Immutable input of one linter run.
 *
 * Paths accept `EXT:key`, `EXT:key/sub/dir`, absolute or relative directories
 * and single template files. A `null` rule list means "every rule".
 */
final readonly class LintOptions
{
    /**
     * @param list<string> $paths
     * @param list<string>|null $rules
     */
    public function __construct(
        public array $paths = ['EXT:desiderio'],
        public bool $strict = false,
        public ?array $rules = null,
    ) {}

    public function isRuleEnabled(string $rule): bool
    {
        return $this->rules === null || in_array($rule, $this->rules, true);
    }
}
