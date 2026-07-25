<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webconsulting\Desiderio\Library\ElementCatalog;
use Webconsulting\Desiderio\Library\PreviewUrlBuilder;
use Webconsulting\Desiderio\Library\PreviewWarmer;

/**
 * Emits one isolated preview URL per element library record.
 *
 * Exists so the visual QA harness does not have to reimplement cHash
 * generation in JavaScript: the URL is signed with the install's encryption
 * key, and getting that wrong silently yields a 404 page that screenshots as
 * "the element renders blank". Everything here is a composition of
 * PreviewWarmer and PreviewUrlBuilder - no new logic, just a readable output
 * format.
 *
 * The URLs need no authentication (the preview PAGE object restricts rendering
 * to records inside the configured library folder), so a headless browser can
 * consume them directly.
 */
#[AsCommand(
    name: 'desiderio:library:urls',
    description: 'Print the isolated preview URL of every element library record (JSON or plain), for visual QA tooling.'
)]
final class ListElementPreviewUrlsCommand extends Command
{
    public function __construct(
        private readonly PreviewWarmer $previewWarmer,
        private readonly PreviewUrlBuilder $previewUrlBuilder,
        private readonly ElementCatalog $elementCatalog,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('folder', null, InputOption::VALUE_REQUIRED, 'Element Library sysfolder uid. Default: every folder any site configures.')
            ->addOption('site', null, InputOption::VALUE_REQUIRED, 'Only this site identifier. Preview URLs are per-site (own base + cHash).')
            ->addOption('json', null, InputOption::VALUE_NONE, 'Emit JSON instead of one URL per line.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $asJson = (bool)$input->getOption('json');

        $folderOption = $input->getOption('folder');
        $siteOption = $input->getOption('site');
        $siteFilter = is_string($siteOption) && $siteOption !== '' ? $siteOption : null;

        $folders = [];
        if (is_numeric($folderOption) && (int)$folderOption > 0) {
            $folders[] = (int)$folderOption;
        } else {
            foreach ($this->previewWarmer->getConfiguredLibraries() as $library) {
                $folders[] = (int)$library['storagePid'];
            }
            $folders = array_values(array_unique(array_filter($folders, static fn (int $pid): bool => $pid > 0)));
        }

        if ($folders === []) {
            $io->error('No element library folder configured. Run desiderio:library:seed and set elementLibrary.storagePid.');
            return self::FAILURE;
        }

        // Group is metadata, not identity: the harness uses it to bucket the
        // report, so resolve it once rather than per record.
        $groupByCType = [];
        foreach ($this->elementCatalog->getElementMetadata() as $element) {
            $groupByCType[$element['cType']] = $element['group'];
        }

        $entries = [];
        foreach ($folders as $storagePid) {
            $records = $this->previewWarmer->getSeededRecords($storagePid);
            if ($records === []) {
                continue;
            }

            foreach ($this->previewWarmer->getSitesForLibraryFolder($storagePid) as $site) {
                $identifier = $site->getIdentifier();
                if ($siteFilter !== null && $identifier !== $siteFilter) {
                    continue;
                }

                foreach ($records as $cType => $uid) {
                    $entries[] = [
                        'cType' => $cType,
                        'uid' => $uid,
                        'group' => $groupByCType[$cType] ?? 'default',
                        'site' => $identifier,
                        'storagePid' => $storagePid,
                        'url' => $this->previewUrlBuilder->build($site, $uid),
                    ];
                }
            }
        }

        if ($entries === []) {
            $io->error($siteFilter !== null
                ? sprintf('No library records reachable for site "%s".', $siteFilter)
                : 'No library records found. Run desiderio:library:seed first.');
            return self::FAILURE;
        }

        if ($asJson) {
            $output->writeln((string)json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        foreach ($entries as $entry) {
            $output->writeln($entry['url']);
        }

        return self::SUCCESS;
    }
}
