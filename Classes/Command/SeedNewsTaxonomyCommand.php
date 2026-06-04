<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

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
    name: 'desiderio:news:seed-taxonomy',
    description: 'Assign default category and tag relations to visible EXT:news records that have none.'
)]
final class SeedNewsTaxonomyCommand extends Command
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('storage-pid', null, InputOption::VALUE_REQUIRED, 'Optional tx_news storage pid to limit updates.')
            ->addOption('category', null, InputOption::VALUE_REQUIRED, 'Default category title.', 'News')
            ->addOption('tags', null, InputOption::VALUE_REQUIRED, 'Comma-separated default tag titles.', 'News,shadcn UI')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only print the detected updates.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!ExtensionManagementUtility::isLoaded('news')) {
            $io->warning('EXT:news is not loaded. No News taxonomy was updated.');
            return self::SUCCESS;
        }

        $categoryTitle = trim((string)$input->getOption('category'));
        $tagTitles = array_values(array_filter(array_map('trim', explode(',', (string)$input->getOption('tags')))));
        if ($categoryTitle === '' && $tagTitles === []) {
            $io->error('Provide at least one category or tag title.');
            return self::FAILURE;
        }

        $storagePid = $this->optionalPositiveInteger($input->getOption('storage-pid'));
        $dryRun = (bool)$input->getOption('dry-run');
        $rows = $this->findNewsRows($storagePid);
        if ($rows === []) {
            $io->warning('No visible default-language EXT:news records were found.');
            return self::SUCCESS;
        }

        $changedCategories = 0;
        $changedTags = 0;
        foreach ($rows as $row) {
            $newsUid = (int)$row['uid'];
            $pid = (int)$row['pid'];
            if ($newsUid <= 0 || $pid <= 0) {
                continue;
            }

            if ((int)($row['categories'] ?? 0) <= 0 && $categoryTitle !== '') {
                ++$changedCategories;
                if (!$dryRun) {
                    $this->addNewsCategory($newsUid, $this->ensureCategory($pid, $categoryTitle));
                }
            }

            if ((int)($row['tags'] ?? 0) <= 0 && $tagTitles !== []) {
                ++$changedTags;
                if (!$dryRun) {
                    foreach ($tagTitles as $index => $tagTitle) {
                        $this->addNewsTag($newsUid, $this->ensureTag($pid, $tagTitle), $index + 1);
                    }
                }
            }

            if (!$dryRun) {
                $this->refreshNewsRelationCounts($newsUid);
            }
        }

        $io->success(sprintf(
            '%s %d News category relation(s) and %d News tag group(s).',
            $dryRun ? 'Would update' : 'Updated',
            $changedCategories,
            $changedTags
        ));

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findNewsRows(?int $storagePid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_news_domain_model_news');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->select('uid', 'pid', 'categories', 'tags')
            ->from('tx_news_domain_model_news')
            ->where(
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('pid')
            ->addOrderBy('datetime', 'DESC');

        if ($storagePid !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($storagePid, ParameterType::INTEGER)));
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    private function ensureCategory(int $pid, string $title): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('sys_category');
        $queryBuilder->getRestrictions()->removeAll();
        $uid = $queryBuilder
            ->select('uid')
            ->from('sys_category')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        if (is_numeric($uid)) {
            return (int)$uid;
        }

        $now = time();
        $connection = $this->connectionPool->getConnectionForTable('sys_category');
        $connection->insert('sys_category', [
            'pid' => $pid,
            'tstamp' => $now,
            'crdate' => $now,
            'title' => $title,
            'parent' => 0,
            'sorting' => $this->nextSorting('sys_category', $pid),
        ]);

        return (int)$connection->lastInsertId();
    }

    private function ensureTag(int $pid, string $title): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_news_domain_model_tag');
        $queryBuilder->getRestrictions()->removeAll();
        $uid = $queryBuilder
            ->select('uid')
            ->from('tx_news_domain_model_tag')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();

        if (is_numeric($uid)) {
            return (int)$uid;
        }

        $now = time();
        $connection = $this->connectionPool->getConnectionForTable('tx_news_domain_model_tag');
        $connection->insert('tx_news_domain_model_tag', [
            'pid' => $pid,
            'tstamp' => $now,
            'crdate' => $now,
            'title' => $title,
            'slug' => strtolower((string)preg_replace('/[^a-z0-9]+/i', '-', trim($title, ' -'))),
            'hidden' => 0,
            'deleted' => 0,
            'sys_language_uid' => 0,
        ]);

        return (int)$connection->lastInsertId();
    }

    private function addNewsCategory(int $newsUid, int $categoryUid): void
    {
        if ($this->relationExists('sys_category_record_mm', [
            'uid_local' => $categoryUid,
            'uid_foreign' => $newsUid,
            'tablenames' => 'tx_news_domain_model_news',
            'fieldname' => 'categories',
        ])) {
            return;
        }

        $this->connectionPool->getConnectionForTable('sys_category_record_mm')->insert('sys_category_record_mm', [
            'uid_local' => $categoryUid,
            'uid_foreign' => $newsUid,
            'sorting' => 1,
            'sorting_foreign' => 0,
            'tablenames' => 'tx_news_domain_model_news',
            'fieldname' => 'categories',
        ]);
    }

    private function addNewsTag(int $newsUid, int $tagUid, int $sorting): void
    {
        if ($this->relationExists('tx_news_domain_model_news_tag_mm', [
            'uid_local' => $newsUid,
            'uid_foreign' => $tagUid,
        ])) {
            return;
        }

        $this->connectionPool->getConnectionForTable('tx_news_domain_model_news_tag_mm')->insert('tx_news_domain_model_news_tag_mm', [
            'uid_local' => $newsUid,
            'uid_foreign' => $tagUid,
            'sorting' => $sorting,
            'sorting_foreign' => 0,
        ]);
    }

    private function refreshNewsRelationCounts(int $newsUid): void
    {
        $this->connectionPool->getConnectionForTable('tx_news_domain_model_news')->update('tx_news_domain_model_news', [
            'categories' => $this->countRows('sys_category_record_mm', [
                'uid_foreign' => $newsUid,
                'tablenames' => 'tx_news_domain_model_news',
                'fieldname' => 'categories',
            ]),
            'tags' => $this->countRows('tx_news_domain_model_news_tag_mm', ['uid_local' => $newsUid]),
            'tstamp' => time(),
        ], ['uid' => $newsUid]);
    }

    /**
     * @param array<string, mixed> $where
     */
    private function relationExists(string $table, array $where): bool
    {
        return $this->countRows($table, $where) > 0;
    }

    /**
     * @param array<string, mixed> $where
     */
    private function countRows(string $table, array $where): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->count('*')->from($table);
        foreach ($where as $field => $value) {
            $type = is_int($value) ? ParameterType::INTEGER : ParameterType::STRING;
            $queryBuilder->andWhere($queryBuilder->expr()->eq($field, $queryBuilder->createNamedParameter($value, $type)));
        }

        return (int)$queryBuilder->executeQuery()->fetchOne();
    }

    private function nextSorting(string $table, int $pid): int
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll();
        $maxSorting = $queryBuilder
            ->selectLiteral('MAX(sorting)')
            ->from($table)
            ->where($queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pid, ParameterType::INTEGER)))
            ->executeQuery()
            ->fetchOne();

        return (is_numeric($maxSorting) ? (int)$maxSorting : 0) + 256;
    }

    private function optionalPositiveInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $integer = (int)$value;
        return $integer > 0 ? $integer : null;
    }
}
