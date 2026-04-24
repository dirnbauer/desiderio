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
use Webconsulting\Desiderio\Data\StyleguideContentGroups;

#[AsCommand(
    name: 'desiderio:styleguide:seed',
    description: 'Create or update shadcn styled Desiderio content element test pages below a parent page.'
)]
final class SeedStyleguidePagesCommand extends Command
{
    public const DEFAULT_PARENT_PID = 505;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'Parent page uid for the generated content element test pages.',
                (string)self::DEFAULT_PARENT_PID
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Only print the planned pages and content element count.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentPid = (int)$input->getOption('parent');
        $dryRun = (bool)$input->getOption('dry-run');
        $groups = StyleguideContentGroups::getGroupsWithFixtures();
        $totalElements = array_sum(array_map(
            static fn (array $group): int => count($group['elements']),
            $groups
        ));

        if ($dryRun) {
            $io->title('Desiderio styleguide seed dry run');
            $io->listing(array_map(
                static fn (array $group): string => sprintf('%s: %d elements', $group['groupTitle'], count($group['elements'])),
                $groups
            ));
            $io->success(sprintf(
                'Would create or update %d pages and %d content elements below page uid %d.',
                count($groups),
                $totalElements,
                $parentPid
            ));

            return self::SUCCESS;
        }

        $pageColumns = $this->getColumnNames('pages');
        $contentColumns = $this->getColumnNames('tt_content');
        $createdPages = 0;
        $createdContentElements = 0;
        $now = time();

        foreach ($groups as $index => $group) {
            $pageUid = $this->findOrCreatePage(
                $parentPid,
                (string)$group['groupTitle'],
                (string)$group['groupId'],
                ($index + 1) * 256,
                $now,
                $pageColumns,
                $createdPages
            );

            $this->markExistingDesiderioContentAsDeleted($pageUid, $now);

            foreach ($group['elements'] as $elementIndex => $element) {
                $row = $this->buildContentRow(
                    $pageUid,
                    (string)$element['ctype'],
                    (string)$element['name'],
                    $element['fixture'],
                    ($elementIndex + 1) * 256,
                    $now,
                    $contentColumns
                );
                $this->connectionPool->getConnectionForTable('tt_content')->insert('tt_content', $row);
                $createdContentElements++;
            }
        }

        $io->success(sprintf(
            'Created or updated %d styleguide pages (%d new) and inserted %d Desiderio content elements below page uid %d.',
            count($groups),
            $createdPages,
            $createdContentElements,
            $parentPid
        ));

        return self::SUCCESS;
    }

    /**
     * @return array<string, true>
     */
    private function getColumnNames(string $table): array
    {
        $columns = [];
        foreach ($this->connectionPool->getConnectionForTable($table)->createSchemaManager()->listTableColumns($table) as $column) {
            $columns[$column->getName()] = true;
        }

        return $columns;
    }

    /**
     * @param array<string, true> $columns
     */
    private function findOrCreatePage(
        int $parentPid,
        string $groupTitle,
        string $groupId,
        int $sorting,
        int $now,
        array $columns,
        int &$createdPages,
    ): int {
        $title = 'Desiderio ' . $groupTitle;
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $existing = $queryBuilder
            ->select('uid')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($parentPid)),
                $queryBuilder->expr()->eq('title', $queryBuilder->createNamedParameter($title)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();

        if (is_array($existing) && isset($existing['uid'])) {
            return (int)$existing['uid'];
        }

        $row = $this->filterRow([
            'pid' => $parentPid,
            'title' => $title,
            'doktype' => 1,
            'slug' => '/desiderio-' . $groupId,
            'hidden' => 0,
            'sorting' => $sorting,
            'crdate' => $now,
            'tstamp' => $now,
        ], $columns);

        $connection = $this->connectionPool->getConnectionForTable('pages');
        $connection->insert('pages', $row);
        $createdPages++;

        return (int)$connection->lastInsertId();
    }

    private function markExistingDesiderioContentAsDeleted(int $pageUid, int $now): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder
            ->update('tt_content')
            ->set('deleted', (string)1)
            ->set('tstamp', (string)$now)
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid)),
                $queryBuilder->expr()->like('CType', $queryBuilder->createNamedParameter('desiderio_%'))
            )
            ->executeStatement();
    }

    /**
     * @param array<string, mixed> $fixture
     * @param array<string, true> $columns
     * @return array<string, mixed>
     */
    private function buildContentRow(
        int $pid,
        string $ctype,
        string $name,
        array $fixture,
        int $sorting,
        int $now,
        array $columns,
    ): array {
        $row = [
            'pid' => $pid,
            'CType' => $ctype,
            'colPos' => 0,
            'header' => $fixture['header'] ?? $name,
            'sorting' => $sorting,
            'hidden' => 0,
            'sys_language_uid' => 0,
            'crdate' => $now,
            'tstamp' => $now,
        ];

        foreach ($fixture as $field => $value) {
            if ($field === '_type' || $field === 'CType' || $field === 'ctype') {
                continue;
            }
            $row[(string)$field] = $this->normalizeValue($value);
        }

        return $this->filterRow($row, $columns);
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        if ($value === null) {
            return '';
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, true> $columns
     * @return array<string, mixed>
     */
    private function filterRow(array $row, array $columns): array
    {
        return array_filter(
            $row,
            static fn (string $column): bool => isset($columns[$column]),
            ARRAY_FILTER_USE_KEY
        );
    }
}
