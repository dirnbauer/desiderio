<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * One diagnostic of the template linter.
 *
 * `skipped` marks checks the linter could not perform because a third-party
 * ViewHelper namespace or partial root belongs to an extension that is not
 * installed; `--strict` turns those into errors.
 */
final readonly class LintFinding
{
    public const string SEVERITY_ERROR = 'error';
    public const string SEVERITY_WARNING = 'warning';
    public const string SEVERITY_SKIPPED = 'skipped';

    public function __construct(
        public string $file,
        public string $rule,
        public string $severity,
        public string $message,
        public ?int $line = null,
    ) {}

    public function isError(): bool
    {
        return $this->severity === self::SEVERITY_ERROR;
    }

    /**
     * @return array{file: string, line: int|null, rule: string, severity: string, message: string}
     */
    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'line' => $this->line,
            'rule' => $this->rule,
            'severity' => $this->severity,
            'message' => $this->message,
        ];
    }
}
