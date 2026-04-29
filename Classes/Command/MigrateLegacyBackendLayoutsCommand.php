<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;

#[AsCommand(
    name: 'desiderio:backend-layout:migrate-legacy',
    description: 'Migrate legacy shadcn2fluid backend layout identifiers on pages to current Desiderio layouts.'
)]
final class MigrateLegacyBackendLayoutsCommand extends Command
{
    private const string PAGE_TS_CONFIG_PREFIX = 'pagets__';

    private const array LEGACY_LAYOUT_MAP = [
        'shadcn2fluid_home' => 'DesiderioStartpage',
        'shadcn2fluid_sub' => 'DesiderioContentpage',
        'shadcn2fluid_sub_nav' => 'DesiderioContentpageSidebar',
        'shadcn2fluid_news' => 'DesiderioContentpageSidebar',
    ];

    private const array LAYOUT_FIELDS = [
        'backend_layout',
        'backend_layout_next_level',
    ];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Show affected page records without updating them.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool)$input->getOption('dry-run');
        $affectedRows = $this->findAffectedPages();

        if ($affectedRows === []) {
            $io->success('No legacy shadcn2fluid backend layout identifiers found on pages.');
            return self::SUCCESS;
        }

        $io->title('Legacy backend layout migration');
        $legacyStorageLayoutMap = self::getLegacyStorageLayoutMap();
        $io->definitionList(
            ...array_map(
                static fn(string $from, string $to): array => [$from => $to],
                array_keys($legacyStorageLayoutMap),
                array_values($legacyStorageLayoutMap),
            )
        );

        $io->table(
            ['uid', 'pid', 'title', 'field', 'from', 'to'],
            array_map(
                static fn(array $row): array => [
                    $row['uid'],
                    $row['pid'],
                    $row['title'],
                    $row['fieldName'],
                    $row['fromLayout'],
                    $row['toLayout'],
                ],
                $affectedRows,
            )
        );

        if ($dryRun) {
            $io->success(sprintf('Dry run complete. %d page field value(s) would be updated.', count($affectedRows)));
            return self::SUCCESS;
        }

        $updatedRows = $this->migrateAffectedPages($affectedRows);
        $io->success(sprintf('Updated %d page field value(s) to current Desiderio backend layouts.', $updatedRows));

        return self::SUCCESS;
    }

    /**
     * @return list<array{
     *     uid: int,
     *     pid: int,
     *     title: string,
     *     fieldName: string,
     *     fromLayout: string,
     *     toLayout: string
     * }>
     */
    private function findAffectedPages(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $expressionBuilder = $queryBuilder->expr();
        $legacyStorageLayoutMap = self::getLegacyStorageLayoutMap();
        $legacyIdentifiers = array_keys($legacyStorageLayoutMap);
        $rows = $queryBuilder
            ->select('uid', 'pid', 'title', ...self::LAYOUT_FIELDS)
            ->from('pages')
            ->where(
                $expressionBuilder->eq('deleted', $queryBuilder->createNamedParameter(0)),
                $expressionBuilder->or(
                    ...array_map(
                        static fn(string $fieldName): string => $expressionBuilder->in(
                            $fieldName,
                            $queryBuilder->createNamedParameter($legacyIdentifiers, \Doctrine\DBAL\ArrayParameterType::STRING),
                        ),
                        self::LAYOUT_FIELDS,
                    ),
                ),
            )
            ->orderBy('pid')
            ->addOrderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $affectedRows = [];
        foreach ($rows as $row) {
            foreach (self::LAYOUT_FIELDS as $fieldName) {
                $value = $row[$fieldName] ?? null;
                if (!is_string($value) || !isset($legacyStorageLayoutMap[$value])) {
                    continue;
                }
                $affectedRows[] = [
                    'uid' => (int)$row['uid'],
                    'pid' => (int)$row['pid'],
                    'title' => is_string($row['title'] ?? null) ? $row['title'] : '',
                    'fieldName' => $fieldName,
                    'fromLayout' => $value,
                    'toLayout' => $legacyStorageLayoutMap[$value],
                ];
            }
        }

        return $affectedRows;
    }

    /**
     * @param list<array{
     *     uid: int,
     *     pid: int,
     *     title: string,
     *     fieldName: string,
     *     fromLayout: string,
     *     toLayout: string
     * }> $affectedRows
     */
    private function migrateAffectedPages(array $affectedRows): int
    {
        $updatedRows = 0;
        $now = time();

        foreach ($affectedRows as $row) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $updatedRows += $queryBuilder
                ->update('pages')
                ->set($row['fieldName'], $queryBuilder->createNamedParameter($row['toLayout']))
                ->set('tstamp', $queryBuilder->createNamedParameter($now))
                ->where(
                    $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($row['uid'])),
                    $queryBuilder->expr()->eq($row['fieldName'], $queryBuilder->createNamedParameter($row['fromLayout'])),
                )
                ->executeStatement();
        }

        return $updatedRows;
    }

    /**
     * @return array<string, string>
     */
    private static function getLegacyStorageLayoutMap(): array
    {
        $map = self::LEGACY_LAYOUT_MAP;

        foreach (self::LEGACY_LAYOUT_MAP as $fromLayout => $toLayout) {
            $map[self::PAGE_TS_CONFIG_PREFIX . $fromLayout] = self::PAGE_TS_CONFIG_PREFIX . $toLayout;
        }

        return $map;
    }
}
