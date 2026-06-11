<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\Exception as DBALException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Rte\RteHtmlConverter;

/**
 * Converts existing plain-text DB values of fields that were switched to
 * enableRichtext into proper RTE HTML (<p>/<br />), so CKEditor opens them
 * without collapsing newlines and saved records stay consistent.
 *
 * Driven by the manifest emitted by scripts/convert-textarea-to-rte.php
 * --apply. tt_content columns are shared across CTypes (prefixFields: false),
 * so tt_content entries are always CType-filtered. Values that already
 * contain markup are skipped and reported for manual review.
 */
#[AsCommand(
    name: 'desiderio:migrate-rte-content',
    description: 'Wrap plain-text values of converted RTE fields into RTE HTML (dry-run by default, use --apply to write)',
)]
final class MigrateRteContentCommand extends Command
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Write changes (without this flag only reports)')
            ->addOption(
                'manifest',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to the conversion manifest',
                'EXT:desiderio/scripts/rte-conversion-manifest.json'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $apply = (bool)$input->getOption('apply');

        $manifestPath = (string)$input->getOption('manifest');
        $absolutePath = str_starts_with($manifestPath, 'EXT:')
            ? GeneralUtility::getFileAbsFileName($manifestPath)
            : $manifestPath;
        if ($absolutePath === '' || !is_file($absolutePath)) {
            $io->error('Manifest not found: ' . $manifestPath . ' — run scripts/convert-textarea-to-rte.php --apply first.');
            return Command::FAILURE;
        }
        $manifest = json_decode((string)file_get_contents($absolutePath), true);
        $fields = $manifest['fields'] ?? null;
        if (!is_array($fields) || $fields === []) {
            $io->warning('Manifest contains no fields, nothing to do.');
            return Command::SUCCESS;
        }

        $converted = 0;
        $skippedHtml = 0;

        foreach ($fields as $entry) {
            $table = $entry['table'] ?? null;
            $column = $entry['column'] ?? null;
            $ctype = $entry['ctype'] ?? null;
            if (!is_string($table) || !is_string($column)) {
                continue;
            }
            if ($table === 'tt_content' && !is_string($ctype)) {
                $io->warning(sprintf('Skipping tt_content.%s without CType filter (shared column).', $column));
                continue;
            }

            try {
                $connection = $this->connectionPool->getConnectionForTable($table);
                $queryBuilder = $connection->createQueryBuilder();
                $queryBuilder->getRestrictions()->removeAll();
                $queryBuilder
                    ->select('uid', $column)
                    ->from($table)
                    ->where(
                        $queryBuilder->expr()->neq($column, $queryBuilder->createNamedParameter('')),
                    );
                if ($ctype !== null) {
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter($ctype)),
                    );
                }
                $rows = $queryBuilder->executeQuery()->fetchAllAssociative();
            } catch (DBALException $exception) {
                $io->warning(sprintf('Skipping %s.%s: %s', $table, $column, $exception->getMessage()));
                continue;
            }

            foreach ($rows as $row) {
                $value = (string)$row[$column];
                if (RteHtmlConverter::looksLikeHtml($value)) {
                    $skippedHtml++;
                    if ($output->isVerbose()) {
                        $io->writeln(sprintf(
                            '  skip (already HTML) %s.%s uid=%d',
                            $table,
                            $column,
                            (int)$row['uid'],
                        ));
                    }
                    continue;
                }
                $html = RteHtmlConverter::convert($value);
                if ($html === $value) {
                    continue;
                }
                $converted++;
                if ($output->isVerbose()) {
                    $io->writeln(sprintf('  convert %s.%s uid=%d', $table, $column, (int)$row['uid']));
                }
                if ($apply) {
                    $connection->update($table, [$column => $html], ['uid' => (int)$row['uid']]);
                }
            }
        }

        $io->success(sprintf(
            '%s%d value(s) converted, %d skipped (already contain HTML).',
            $apply ? '' : '[dry-run] ',
            $converted,
            $skippedHtml,
        ));
        if (!$apply && $converted > 0) {
            $io->note('Re-run with --apply to write the changes, then flush frontend caches.');
        }
        return Command::SUCCESS;
    }
}
