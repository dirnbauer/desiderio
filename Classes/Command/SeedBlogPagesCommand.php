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
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Webconsulting\Desiderio\Seeding\BlogPageTreeLocator;
use Webconsulting\Desiderio\Seeding\BlogPageTreeSeeder;

#[AsCommand(
    name: 'desiderio:blog:seed-pages',
    description: 'Apply Desiderio shadcn Blog page layouts to existing EXT:blog setups.'
)]
final class SeedBlogPagesCommand extends Command
{
    private ?BlogPageTreeSeeder $blogPageTreeSeeder = null;

    private ?BlogPageTreeLocator $blogPageTreeLocator = null;

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
                'Blog root page uid to seed. Required unless --all-blogs is given.'
            )
            ->addOption(
                'all-blogs',
                null,
                InputOption::VALUE_NONE,
                'Seed the English demo posts into every EXT:blog setup of the installation.'
            )
            ->addOption(
                'layout',
                null,
                InputOption::VALUE_REQUIRED,
                'Backend layout identifier to apply to Blog root, list, and post pages.',
                BlogPageTreeSeeder::DEFAULT_BACKEND_LAYOUT
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
        // The demo posts are English. Seeding every blog by default put them
        // into German blogs next to their own posts, so a blog is named.
        if ($rootFilter === null && $input->getOption('all-blogs') !== true) {
            $io->error('Name the blog with --root=<uid>, or pass --all-blogs to seed every EXT:blog setup.');
            return self::FAILURE;
        }
        $dryRun = (bool)$input->getOption('dry-run');
        $locator = $this->getBlogPageTreeLocator();
        $setups = $locator->findBlogSetups($rootFilter);

        if ($setups === []) {
            $io->warning($rootFilter === null ? 'No EXT:blog setup folders were found.' : sprintf('No EXT:blog setup was found for root page uid %d.', $rootFilter));
            return self::SUCCESS;
        }

        $changedPages = 0;
        $seededPosts = 0;
        $seededContentElements = 0;
        $plannedRows = [];

        foreach ($setups as $setup) {
            $pageUids = $locator->findLayoutPageUids((int)$setup['rootUid'], (int)$setup['folderUid']);
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
                $changedPages += $locator->applyBackendLayout($pageUids, $layout);
                $seedResult = $this->getBlogPageTreeSeeder()->seedDemoContent((int)$setup['folderUid'], $layout);
                $seededPosts += $seedResult['posts'];
                $seededContentElements += $seedResult['contentElements'];
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
        if (!$dryRun) {
            $io->success(sprintf(
                'Seeded %d Blog post records and %d Blog content elements.',
                $seededPosts,
                $seededContentElements
            ));
        }

        return self::SUCCESS;
    }

    private function getBlogPageTreeSeeder(): BlogPageTreeSeeder
    {
        return $this->blogPageTreeSeeder ??= new BlogPageTreeSeeder($this->connectionPool);
    }

    private function getBlogPageTreeLocator(): BlogPageTreeLocator
    {
        return $this->blogPageTreeLocator ??= new BlogPageTreeLocator($this->connectionPool);
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
}
