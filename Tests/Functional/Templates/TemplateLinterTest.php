<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Templates;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Templates\LintFinding;
use Webconsulting\Desiderio\Templates\LintOptions;
use Webconsulting\Desiderio\Templates\LintReport;
use Webconsulting\Desiderio\Templates\TemplateLinter;

/**
 * Every rule of the Fluid 5 lint gate, exercised against one intentionally
 * broken fixture each. The linter parses with TYPO3's RenderingContextFactory,
 * so the test boots the extension (component collection, cb: namespace).
 */
final class TemplateLinterTest extends FunctionalTestCase
{
    private const string FIXTURES = __DIR__ . '/../Fixtures/Templates';

    protected array $coreExtensionsToLoad = ['form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'friendsoftypo3/visual-editor',
        'webconsulting/desiderio',
    ];

    #[Test]
    public function validTemplateHasNoFindings(): void
    {
        $report = $this->lint('valid.html');

        self::assertSame(1, $report->getFilesScanned());
        self::assertSame([], $this->messages($report), 'A valid template must produce no findings at all');
    }

    #[Test]
    public function unknownViewHelperIsAParseError(): void
    {
        $report = $this->lint('unknown-viewhelper.html');

        self::assertTrue($report->hasErrors());
        $errors = $report->getFindingsForRule(TemplateLinter::RULE_PARSE);
        self::assertCount(1, $errors);
        self::assertSame(LintFinding::SEVERITY_ERROR, $errors[0]->severity);
        self::assertStringContainsString('<f:format.doesNotExist>', $errors[0]->message);
    }

    #[Test]
    public function undeclaredViewHelperArgumentIsAParseError(): void
    {
        $report = $this->lint('unknown-argument.html');

        $errors = $report->getFindingsForRule(TemplateLinter::RULE_PARSE);
        self::assertNotSame([], $errors);
        self::assertStringContainsString('Undeclared ViewHelper argument', $errors[0]->message);
        self::assertStringContainsString('nope', $errors[0]->message);
    }

    #[Test]
    public function componentCallSitesAreCheckedAgainstTheirArgumentDeclarations(): void
    {
        $report = $this->lint('unknown-component-argument.html');

        $messages = array_map(
            static fn(LintFinding $finding): string => $finding->line . ': ' . $finding->message,
            $report->getFindingsForRule(TemplateLinter::RULE_COMPONENT_ARGUMENTS),
        );
        self::assertCount(3, $messages, implode("\n", $messages));
        self::assertStringContainsString('<d:atom.button> does not declare the argument(s) colour', $messages[0]);
        self::assertStringStartsWith('5:', $messages[0]);
        self::assertStringContainsString('<d:atom.icon> is called without the required argument(s) name', $messages[1]);
        self::assertStringContainsString('Unknown component <d:atom.doesNotExist>', $messages[2]);
        self::assertSame([], $report->getFindingsForRule(TemplateLinter::RULE_PARSE), 'The parser duplicates are folded into the component-arguments rule');
    }

    #[Test]
    public function undeclaredNamespaceIsReportedWithLineNumber(): void
    {
        $report = $this->lint('undeclared-namespace.html');

        $findings = $report->getFindingsForRule(TemplateLinter::RULE_NAMESPACE_USAGE);
        $errors = array_values(array_filter($findings, static fn(LintFinding $finding): bool => $finding->isError()));
        self::assertCount(1, $errors, implode("\n", $this->messages($report)));
        self::assertSame(5, $errors[0]->line);
        self::assertStringContainsString('Prefix "x" is used but neither declared', $errors[0]->message);

        $warnings = array_values(array_filter($findings, static fn(LintFinding $finding): bool => $finding->severity === LintFinding::SEVERITY_WARNING));
        self::assertCount(1, $warnings);
        self::assertStringContainsString('"cb" is declared but never used', $warnings[0]->message);
    }

    #[Test]
    public function orphanedPartialsAreErrors(): void
    {
        $report = $this->lint('orphan-partial.html');

        $errors = $report->getFindingsForRule(TemplateLinter::RULE_PARTIAL_RESOLVES);
        self::assertCount(2, $errors, implode("\n", $this->messages($report)));
        self::assertStringContainsString('Partial "Does/NotExist" does not resolve', $errors[0]->message);
        self::assertStringContainsString('Partial "Nor/DoesThis" does not resolve', $errors[1]->message);
        self::assertSame(3, $errors[0]->line);
        self::assertSame(4, $errors[1]->line);
    }

    #[Test]
    public function removedConstructsAreErrors(): void
    {
        $report = $this->lint('deprecated-constructs.html');

        $messages = array_map(static fn(LintFinding $finding): string => $finding->message, $report->getFindingsForRule(TemplateLinter::RULE_DEPRECATED_CONSTRUCTS));
        self::assertCount(2, $messages, implode("\n", $messages));
        self::assertStringContainsString('{namespace}', $messages[0]);
        self::assertStringContainsString('f:widget.*', $messages[1]);
    }

    #[Test]
    public function notInstalledThirdPartyNamespaceIsSkippedUnlessStrict(): void
    {
        $report = $this->lint('third-party-namespace.html');

        self::assertFalse($report->hasErrors(), implode("\n", $this->messages($report)));
        $skipped = $report->getSkipped();
        self::assertCount(1, $skipped);
        self::assertStringContainsString('"notinstalled" (Vendor\NotInstalledExtension\ViewHelpers) is not installed', $skipped[0]->message);

        $strict = $this->lint('third-party-namespace.html', strict: true);
        self::assertTrue($strict->hasErrors());
        self::assertSame([], $strict->getSkipped());
        self::assertStringContainsString('is not installed', $strict->getErrors()[0]->message);
    }

    #[Test]
    public function rulesCanBeSelected(): void
    {
        $report = $this->lint('orphan-partial.html', rules: [TemplateLinter::RULE_PARSE]);

        self::assertSame([], $report->getFindings(), 'Only the parse rule ran, so the missing partial is not reported');
    }

    #[Test]
    public function wholeDirectoryIsScanned(): void
    {
        $report = $this->get(TemplateLinter::class)->lint(new LintOptions([self::FIXTURES]));

        $fixtures = glob(self::FIXTURES . '/*.html');
        self::assertIsArray($fixtures);
        self::assertSame(count($fixtures), $report->getFilesScanned());
        self::assertTrue($report->hasErrors());
    }

    #[Test]
    public function unknownPathIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->get(TemplateLinter::class)->lint(new LintOptions([self::FIXTURES . '/does-not-exist']));
    }

    /**
     * @param list<string>|null $rules
     */
    private function lint(string $fixture, bool $strict = false, ?array $rules = null): LintReport
    {
        return $this->get(TemplateLinter::class)->lint(new LintOptions([self::FIXTURES . '/' . $fixture], $strict, $rules));
    }

    /**
     * @return list<string>
     */
    private function messages(LintReport $report): array
    {
        return array_map(
            static fn(LintFinding $finding): string => sprintf('%s:%s [%s/%s] %s', $finding->file, $finding->line ?? '-', $finding->rule, $finding->severity, $finding->message),
            $report->getFindings(),
        );
    }
}
