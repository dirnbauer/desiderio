<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Templates\LintFinding;
use Webconsulting\Desiderio\Templates\LintOptions;
use Webconsulting\Desiderio\Templates\LintReport;
use Webconsulting\Desiderio\Templates\LintRule;
use Webconsulting\Desiderio\Templates\LintSeverity;

/**
 * The typed model behind the template linter: which rules exist, what a
 * severity prints as, and how a report answers the questions the CLI asks.
 */
final class TemplateLintModelTest extends TestCase
{
    public function testRuleNamesAreTheDocumentedCliValues(): void
    {
        self::assertSame(
            ['parse', 'namespace-usage', 'partial-resolves', 'deprecated-constructs', 'component-arguments'],
            LintRule::names(),
        );
        self::assertSame(LintRule::Parse, LintRule::tryFrom('parse'));
        self::assertNull(LintRule::tryFrom('does-not-exist'));
    }

    public function testSeverityCarriesItsConsoleStyle(): void
    {
        self::assertSame('fg=red', LintSeverity::Error->consoleStyle());
        self::assertSame('fg=yellow', LintSeverity::Warning->consoleStyle());
        self::assertSame('fg=gray', LintSeverity::Skipped->consoleStyle());
        self::assertSame(['error', 'warning', 'skipped'], array_map(
            static fn(LintSeverity $severity): string => $severity->value,
            LintSeverity::cases(),
        ));
    }

    public function testEmptyRuleSelectionMeansEveryRule(): void
    {
        $options = new LintOptions();

        self::assertSame(LintRule::cases(), $options->rules);
        foreach (LintRule::cases() as $rule) {
            self::assertTrue($options->isRuleEnabled($rule));
        }
    }

    public function testExplicitRuleSelectionDisablesEveryOtherRule(): void
    {
        $options = new LintOptions(rules: [LintRule::Parse]);

        self::assertTrue($options->isRuleEnabled(LintRule::Parse));
        self::assertFalse($options->isRuleEnabled(LintRule::PartialResolves));
    }

    public function testReportFiltersBySeverityAndByRule(): void
    {
        $report = new LintReport();
        $report->countFile();
        $report->addFinding(new LintFinding('a.html', LintRule::Parse, LintSeverity::Error, 'broken', 12));
        $report->addFinding(new LintFinding('a.html', LintRule::NamespaceUsage, LintSeverity::Warning, 'unused'));
        $report->addFinding(new LintFinding('b.html', LintRule::PartialResolves, LintSeverity::Skipped, 'not installed'));

        self::assertCount(3, $report->findings());
        self::assertCount(1, $report->findings(LintSeverity::Error));
        self::assertCount(1, $report->findings(LintSeverity::Warning));
        self::assertCount(1, $report->findings(LintSeverity::Skipped));
        self::assertSame('broken', $report->findingsForRule(LintRule::Parse)[0]->message);
        self::assertSame([], $report->findingsForRule(LintRule::ComponentArguments));
        self::assertTrue($report->hasErrors());
        self::assertSame(1, $report->getFilesScanned());
    }

    public function testReportWithoutErrorsDoesNotFailTheGate(): void
    {
        $report = new LintReport();
        $report->addFinding(new LintFinding('a.html', LintRule::NamespaceUsage, LintSeverity::Warning, 'unused'));

        self::assertFalse($report->hasErrors());
    }

    public function testJsonPayloadKeepsTheStringValuesOfTheEnums(): void
    {
        $report = new LintReport();
        $report->countFile();
        $report->addFinding(new LintFinding('a.html', LintRule::Parse, LintSeverity::Error, 'broken', 12));

        self::assertSame([
            'filesScanned' => 1,
            'errors' => 1,
            'warnings' => 0,
            'skipped' => 0,
            'findings' => [[
                'file' => 'a.html',
                'line' => 12,
                'rule' => 'parse',
                'severity' => 'error',
                'message' => 'broken',
            ]],
        ], $report->toArray());
    }
}
