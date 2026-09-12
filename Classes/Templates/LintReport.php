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
     * @return list<LintFinding>
     */
    public function getFindings(): array
    {
        return $this->findings;
    }

    /**
     * @return list<LintFinding>
     */
    public function getErrors(): array
    {
        return $this->filterBySeverity(LintFinding::SEVERITY_ERROR);
    }

    /**
     * @return list<LintFinding>
     */
    public function getWarnings(): array
    {
        return $this->filterBySeverity(LintFinding::SEVERITY_WARNING);
    }

    /**
     * @return list<LintFinding>
     */
    public function getSkipped(): array
    {
        return $this->filterBySeverity(LintFinding::SEVERITY_SKIPPED);
    }

    public function hasErrors(): bool
    {
        return $this->getErrors() !== [];
    }

    /**
     * @return list<LintFinding>
     */
    public function getFindingsForRule(string $rule): array
    {
        return array_values(array_filter($this->findings, static fn(LintFinding $finding): bool => $finding->rule === $rule));
    }

    /**
     * @return array{filesScanned: int, errors: int, warnings: int, skipped: int, findings: list<array{file: string, line: int|null, rule: string, severity: string, message: string}>}
     */
    public function toArray(): array
    {
        return [
            'filesScanned' => $this->filesScanned,
            'errors' => count($this->getErrors()),
            'warnings' => count($this->getWarnings()),
            'skipped' => count($this->getSkipped()),
            'findings' => array_map(static fn(LintFinding $finding): array => $finding->toArray(), $this->findings),
        ];
    }

    /**
     * @return list<LintFinding>
     */
    private function filterBySeverity(string $severity): array
    {
        return array_values(array_filter($this->findings, static fn(LintFinding $finding): bool => $finding->severity === $severity));
    }
}
