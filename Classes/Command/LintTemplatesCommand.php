<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webconsulting\Desiderio\Templates\LintFinding;
use Webconsulting\Desiderio\Templates\LintOptions;
use Webconsulting\Desiderio\Templates\LintReport;
use Webconsulting\Desiderio\Templates\TemplateLinter;

/**
 * Fluid 5 lint gate: parses every shipped template with the runtime
 * RenderingContext and fails on unknown ViewHelpers, arguments, namespaces,
 * components, orphaned partials and removed constructs.
 *
 * Exit codes: 0 clean, 1 findings with severity "error", 2 invalid invocation.
 */
#[AsCommand(
    name: 'desiderio:templates:lint',
    description: 'Lint Fluid templates against the Fluid 5 parser: unknown ViewHelpers, arguments, namespaces, components and partials.'
)]
final class LintTemplatesCommand extends Command
{
    public function __construct(
        private readonly TemplateLinter $templateLinter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('path', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'EXT:key, EXT:key/sub/dir, directory or single template file (repeatable).', ['EXT:desiderio'])
            ->addOption('strict', null, InputOption::VALUE_NONE, 'Treat namespaces and partials of extensions that are not installed as errors instead of skipping them.')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'Output format: text or json.', 'text')
            ->addOption('rule', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Only run these rules (repeatable): ' . implode(', ', TemplateLinter::RULES) . '.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $format = $input->getOption('format');
        if (!in_array($format, ['text', 'json'], true)) {
            $io->error('Unknown format; use text or json.');
            return self::INVALID;
        }
        $rules = $this->stringList($input->getOption('rule'));
        $unknownRules = array_diff($rules, TemplateLinter::RULES);
        if ($unknownRules !== []) {
            $io->error('Unknown rule(s): ' . implode(', ', $unknownRules) . '. Available: ' . implode(', ', TemplateLinter::RULES));
            return self::INVALID;
        }

        $options = new LintOptions(
            paths: $this->stringList($input->getOption('path')),
            strict: (bool)$input->getOption('strict'),
            rules: $rules === [] ? null : $rules,
        );
        try {
            $report = $this->templateLinter->lint($options);
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());
            return self::INVALID;
        }

        if ($format === 'json') {
            $output->writeln((string)json_encode($report->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $report->hasErrors() ? self::FAILURE : self::SUCCESS;
        }

        $this->renderText($io, $report, $output->isVerbose());
        return $report->hasErrors() ? self::FAILURE : self::SUCCESS;
    }

    private function renderText(SymfonyStyle $io, LintReport $report, bool $verbose): void
    {
        $byFile = [];
        foreach ($report->getFindings() as $finding) {
            if ($finding->severity === LintFinding::SEVERITY_SKIPPED && !$verbose) {
                continue;
            }
            $byFile[$finding->file][] = $finding;
        }
        foreach ($byFile as $file => $findings) {
            $io->section($file);
            foreach ($findings as $finding) {
                $io->writeln(sprintf(
                    '  <%s>%-7s</> %s%s [%s]',
                    match ($finding->severity) {
                        LintFinding::SEVERITY_ERROR => 'fg=red',
                        LintFinding::SEVERITY_WARNING => 'fg=yellow',
                        default => 'fg=gray',
                    },
                    $finding->severity,
                    $finding->line !== null ? 'line ' . $finding->line . ': ' : '',
                    $finding->message,
                    $finding->rule,
                ));
            }
        }
        $summary = sprintf(
            '%d template(s), %d error(s), %d warning(s), %d skipped%s',
            $report->getFilesScanned(),
            count($report->getErrors()),
            count($report->getWarnings()),
            count($report->getSkipped()),
            $verbose || $report->getSkipped() === [] ? '' : ' (run with -v to list skipped checks)',
        );
        if ($report->hasErrors()) {
            $io->error($summary);
        } else {
            $io->success($summary);
        }
    }

    /**
     * @return list<string>
     */
    private function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        return array_values(array_filter(array_map(static fn(mixed $item): string => is_string($item) ? trim($item) : '', $value), static fn(string $item): bool => $item !== ''));
    }
}
