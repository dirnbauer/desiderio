<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * One diagnostic of the template linter.
 */
final readonly class LintFinding
{
    public function __construct(
        public string $file,
        public LintRule $rule,
        public LintSeverity $severity,
        public string $message,
        public ?int $line = null,
    ) {}

    /**
     * @return array{file: string, line: int|null, rule: string, severity: string, message: string}
     */
    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'rule' => $this->rule->value,
            'severity' => $this->severity->value,
            'message' => $this->message,
        ];
    }
}
