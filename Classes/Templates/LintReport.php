<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

/**
 * Findings of one linter run plus the number of templates it looked at.
 */
final class LintReport
{
    /** @var list<LintFinding> */
    private array $findings = [];

    private int $filesScanned = 0;

    public function addFinding(LintFinding $finding): void
    {
        $this->findings[] = $finding;
    }

    public function countFile(): void
    {
        $this->filesScanned++;
    }

    public function getFilesScanned(): int
    {
        return $this->filesScanned;
    }

    /**
     * Every finding, or only those of one severity.
     *
     * @return list<LintFinding>
     */
    public function findings(?LintSeverity $severity = null): array
    {
        if ($severity === null) {
            return $this->findings;
        }

        return array_values(array_filter($this->findings, static fn(LintFinding $finding): bool => $finding->severity === $severity));
    }

    /**
     * @return list<LintFinding>
     */
    public function findingsForRule(LintRule $rule): array
    {
        return array_values(array_filter($this->findings, static fn(LintFinding $finding): bool => $finding->rule === $rule));
    }

    public function hasErrors(): bool
    {
        return array_any($this->findings, static fn(LintFinding $finding): bool => $finding->severity === LintSeverity::Error);
    }

    /**
     * @return array{filesScanned: int, errors: int, warnings: int, skipped: int, findings: list<array{file: string, line: int|null, rule: string, severity: string, message: string}>}
     */
    public function toArray(): array
    {
        return [
            'filesScanned' => $this->filesScanned,
            'errors' => count($this->findings(LintSeverity::Error)),
            'warnings' => count($this->findings(LintSeverity::Warning)),
            'skipped' => count($this->findings(LintSeverity::Skipped)),
            'findings' => array_map(static fn(LintFinding $finding): array => $finding->toArray(), $this->findings),
        ];
    }
}
