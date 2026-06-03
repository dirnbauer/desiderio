<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

#[AsCommand(
    name: 'desiderio:blog:seed-pages',
    description: 'Apply Desiderio shadcn Blog page layouts to existing EXT:blog setups.'
)]
final class SeedBlogPagesCommand extends Command
{
    private const DEFAULT_BACKEND_LAYOUT = 'pagets__DesiderioBlog';

    private const BLOG_LIST_CTYPES = [
        'blog_posts',
        'blog_category',
        'blog_tag',
        'blog_authorposts',
        'blog_archive',
        'blog_demandedposts',
    ];

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'root',
                null,
                InputOption::VALUE_REQUIRED,
                'Optional Blog root page uid. If omitted, all EXT:blog setups are updated.'
            )
            ->addOption(
                'layout',
                null,
                InputOption::VALUE_REQUIRED,
                'Backend layout identifier to apply to Blog root, list, and post pages.',
                self::DEFAULT_BACKEND_LAYOUT
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Only print the detected page changes.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!ExtensionManagementUtility::isLoaded('blog')) {
            $io->warning('EXT:blog is not loaded. No Blog pages were updated.');
            return self::SUCCESS;
        }

        $layout = $this->getStringInputOption($input, 'layout');
        if ($layout === '') {
            $io->error('The --layout option must not be empty.');
            return self::FAILURE;
        }

        $rootFilter = $this->getRootFilter($input->getOption('root'));
        $dryRun = (bool)$input->getOption('dry-run');
        $setups = $this->findBlogSetups($rootFilter);

        if ($setups === []) {
            $io->warning($rootFilter === null ? 'No EXT:blog setup folders were found.' : sprintf('No EXT:blog setup was found for root page uid %d.', $rootFilter));
            return self::SUCCESS;
        }

        $changedPages = 0;
        $plannedRows = [];

        foreach ($setups as $setup) {
            $pageUids = $this->findLayoutPageUids((int)$setup['rootUid'], (int)$setup['folderUid']);
            if ($pageUids === []) {
                continue;
            }

            $plannedRows[] = [
                (string)$setup['rootUid'],
                (string)$setup['folderUid'],
                (string)count($pageUids),
                $layout,
            ];

            if (!$dryRun) {
                $changedPages += $this->applyBackendLayout($pageUids, $layout);
                $this->applyDataFolderNextLevelLayout((int)$setup['rootUid'], $layout);
            } else {
                $changedPages += count($pageUids);
            }
        }

        if ($plannedRows !== []) {
            $io->table(['Blog root', 'Data folder', 'Pages', 'Backend layout'], $plannedRows);
        }

        $io->success(sprintf(
            $dryRun ? 'Would update %d Blog page records.' : 'Updated %d Blog page records.',
            $changedPages
        ));

        return self::SUCCESS;
    }

    private function getRootFilter(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return max(0, $value);
        }

        if (!is_string($value)) {
            return null;
        }

        return max(0, (int)$value);
    }

    private function getStringInputOption(InputInterface $input, string $name): string
    {
        $value = $input->getOption($name);
        if (is_string($value)) {
            return trim($value);
        }

        return '';
    }

    /**
     * @return list<array{rootUid: int, folderUid: int}>
     */
    private function findBlogSetups(?int $rootFilter): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $conditions = [
            $queryBuilder->expr()->eq('module', $queryBuilder->createNamedParameter('blog')),
            $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
            $queryBuilder->expr()->gt('pid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
        ];

        if ($rootFilter !== null) {
            $conditions[] = $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($rootFilter, ParameterType::INTEGER));
        }

        $rows = $queryBuilder
            ->select('uid', 'pid')
            ->from('pages')
            ->where(...$conditions)
            ->orderBy('pid')
            ->addOrderBy('uid')
            ->executeQuery()
            ->fetchAllAssociative();

        $setups = [];
        foreach ($rows as $row) {
            $rootUid = $this->getIntegerRowValue($row, 'pid');
            $folderUid = $this->getIntegerRowValue($row, 'uid');
            if ($rootUid <= 0 || $folderUid <= 0) {
                continue;
            }

            if (isset($setups[$rootUid])) {
                continue;
            }
            $setups[$rootUid] = [
                'rootUid' => $rootUid,
                'folderUid' => $folderUid,
            ];
        }

        return array_values($setups);
    }

    /**
     * @return list<int>
     */
    private function findLayoutPageUids(int $rootUid, int $folderUid): array
    {
        $folderPages = $this->findBlogFolderPageUids($rootUid);
        $rootAndListPages = $this->findRootAndListPageUids($rootUid);
        $postPages = $this->findPostPageUids($folderUid);

        $pageUids = $this->normalizePageUids(array_merge([$rootUid, $folderUid], $folderPages, $rootAndListPages, $postPages));
        return $this->normalizePageUids(array_merge($pageUids, $this->findTranslationPageUids($pageUids)));
    }

    /**
     * @return list<int>
     */
    private function findBlogFolderPageUids(int $rootUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('module', $queryBuilder->createNamedParameter('blog')),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @return list<int>
     */
    private function findRootAndListPageUids(int $rootUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('pages.uid')
            ->from('pages')
            ->join(
                'pages',
                'tt_content',
                'content',
                (string)$queryBuilder->expr()->and(
                    $queryBuilder->expr()->eq('content.pid', $queryBuilder->quoteIdentifier('pages.uid')),
                    $queryBuilder->expr()->eq('content.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                    $queryBuilder->expr()->in(
                        'content.CType',
                        $queryBuilder->createNamedParameter(self::BLOG_LIST_CTYPES, ArrayParameterType::STRING)
                    )
                )
            )
            ->where(
                $queryBuilder->expr()->eq('pages.deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->or(
                    $queryBuilder->expr()->eq('pages.uid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER)),
                    $queryBuilder->expr()->eq('pages.pid', $queryBuilder->createNamedParameter($rootUid, ParameterType::INTEGER))
                )
            )
            ->groupBy('pages.uid')
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @param list<int> $pageUids
     * @return list<int>
     */
    private function findTranslationPageUids(array $pageUids): array
    {
        if ($pageUids === []) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->in(
                    'l10n_parent',
                    $queryBuilder->createNamedParameter($pageUids, ArrayParameterType::INTEGER)
                ),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @return list<int>
     */
    private function findPostPageUids(int $folderUid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($folderUid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->executeQuery()
            ->fetchFirstColumn();

        return $this->mapIntegerColumn($rows);
    }

    /**
     * @param list<int> $pageUids
     */
    private function applyBackendLayout(array $pageUids, string $layout): int
    {
        if ($pageUids === []) {
            return 0;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->update('pages')
            ->set('backend_layout', $layout)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($pageUids, ArrayParameterType::INTEGER)
                )
            )
            ->executeStatement();
    }

    private function applyDataFolderNextLevelLayout(int $rootUid, string $layout): void
    {
        $folderUids = $this->findBlogFolderPageUids($rootUid);
        $folderUids = $this->normalizePageUids(array_merge($folderUids, $this->findTranslationPageUids($folderUids)));
        if ($folderUids === []) {
            return;
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $queryBuilder->getRestrictions()->removeAll();

        $queryBuilder
            ->update('pages')
            ->set('backend_layout_next_level', $layout)
            ->where(
                $queryBuilder->expr()->in(
                    'uid',
                    $queryBuilder->createNamedParameter($folderUids, ArrayParameterType::INTEGER)
                )
            )
            ->executeStatement();
    }

    /**
     * @param array<string, mixed> $row
     */
    private function getIntegerRowValue(array $row, string $key): int
    {
        $value = $row[$key] ?? null;
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    /**
     * @param array<mixed> $values
     * @return list<int>
     */
    private function mapIntegerColumn(array $values): array
    {
        $integers = [];
        foreach ($values as $value) {
            if (is_int($value)) {
                $integers[] = $value;
                continue;
            }

            if (is_string($value) && is_numeric($value)) {
                $integers[] = (int)$value;
            }
        }

        return $integers;
    }

    /**
     * @param array<int> $pageUids
     * @return list<int>
     */
    private function normalizePageUids(array $pageUids): array
    {
        $pageUids = array_values(array_unique(array_filter(
            $pageUids,
            static fn (int $pageUid): bool => $pageUid > 0
        )));
        sort($pageUids);

        return $pageUids;
    }
}
