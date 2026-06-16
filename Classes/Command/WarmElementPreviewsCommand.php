<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\Library\PreviewWarmer;

#[AsCommand(
    name: 'desiderio:library:warm',
    description: 'Prerender all element library records into the TYPO3 page cache (fast picker previews).'
)]
final class WarmElementPreviewsCommand extends Command
{
    public function __construct(
        private readonly PreviewWarmer $previewWarmer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'folder',
                null,
                InputOption::VALUE_REQUIRED,
                'Element Library sysfolder uid (printed by desiderio:library:seed). Default: every folder configured by any site.'
            )
            ->addOption(
                'site',
                null,
                InputOption::VALUE_REQUIRED,
                'Warm only this site identifier. Each site renders previews from its own base, so omit it to warm every site that shows the picker.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $siteOption = $input->getOption('site');
        $siteFilter = is_string($siteOption) && $siteOption !== '' ? $siteOption : null;

        // A job = one library folder + the site bases to warm it for. A folder
        // shared by several sites is warmed once per site (different base/cHash).
        $jobs = $this->resolveJobs($input, $io);
        if ($jobs === null) {
            return self::FAILURE;
        }
        if ($siteFilter !== null) {
            $jobs = $this->filterJobsBySite($jobs, $siteFilter);
        }
        $jobs = array_values(array_filter($jobs, static fn(array $job): bool => $job['sites'] !== []));
        if ($jobs === []) {
            $folderOption = $input->getOption('folder');
            if ($siteFilter === null) {
                $io->error('No site configures an element library (elementLibrary.storagePid).');
            } elseif ($folderOption !== null) {
                $io->error(sprintf('Site "%s" does not use element library folder %s.', $siteFilter, is_scalar($folderOption) ? (string)$folderOption : ''));
            } else {
                $io->error(sprintf('No element library is configured for site "%s".', $siteFilter));
            }
            return self::FAILURE;
        }

        foreach ($jobs as $job) {
            $names = implode(', ', array_map(static fn(Site $s): string => $s->getIdentifier(), $job['sites']));
            $io->writeln(sprintf('Folder <info>%d</info> → %s', $job['storagePid'], $names));
        }

        $totalWarmed = 0;
        $totalFailed = [];
        $perSite = [];
        foreach ($jobs as $job) {
            $result = $this->previewWarmer->warm(
                $job['storagePid'],
                $job['sites'],
                function (string $cType, string $url, bool $success, string $site) use ($output): void {
                    if ($output->isVerbose()) {
                        $output->writeln(sprintf(
                            '%s %s %s %s',
                            $success ? '<info>OK</info>  ' : '<error>FAIL</error>',
                            $site,
                            $cType,
                            $output->isVeryVerbose() ? $url : ''
                        ));
                    }
                }
            );
            $totalWarmed += $result['warmed'];
            $totalFailed = array_merge($totalFailed, $result['failed']);
            foreach ($result['sites'] as $identifier => $info) {
                $perSite[$identifier] = $info;
            }
        }

        $io->newLine();
        $io->writeln(sprintf('<info>%d</info> previews warmed across %d site(s), <comment>%d</comment> failed', $totalWarmed, count($perSite), count($totalFailed)));
        foreach ($perSite as $identifier => $info) {
            $io->writeln(sprintf('  %s (%s): %d warmed, %d failed', $identifier, $info['base'], $info['warmed'], $info['failed']));
        }
        foreach (array_slice($totalFailed, 0, 10) as $failure) {
            $io->warning($failure);
        }

        return $totalFailed === [] ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Builds the list of warm jobs from the options. With --folder, a single
     * folder warmed for every site that uses it; without it, every configured
     * library grouped by folder.
     *
     * @return list<array{storagePid: int, sites: list<Site>}>|null null on error
     */
    private function resolveJobs(InputInterface $input, SymfonyStyle $io): ?array
    {
        $folderOption = $input->getOption('folder');
        if ($folderOption !== null) {
            $folderUid = is_numeric($folderOption) ? (int)$folderOption : 0;
            if ($folderUid <= 0) {
                $io->error('Pass --folder=<element library sysfolder uid> (a positive integer).');
                return null;
            }
            return [['storagePid' => $folderUid, 'sites' => $this->previewWarmer->getSitesForLibraryFolder($folderUid)]];
        }

        // Default: warm every site's configured library, grouped by folder so a
        // shared folder fetches its records only once.
        $grouped = [];
        foreach ($this->previewWarmer->getConfiguredLibraries() as $library) {
            $grouped[$library['storagePid']]['storagePid'] = $library['storagePid'];
            $grouped[$library['storagePid']]['sites'][] = $library['site'];
        }
        return array_values($grouped);
    }

    /**
     * @param list<array{storagePid: int, sites: list<Site>}> $jobs
     * @return list<array{storagePid: int, sites: list<Site>}>
     */
    private function filterJobsBySite(array $jobs, string $siteIdentifier): array
    {
        return array_map(
            static function (array $job) use ($siteIdentifier): array {
                $job['sites'] = array_values(array_filter(
                    $job['sites'],
                    static fn(Site $s): bool => $s->getIdentifier() === $siteIdentifier
                ));
                return $job;
            },
            $jobs
        );
    }
}
