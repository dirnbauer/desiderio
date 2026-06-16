<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Library\ElementCatalog;
use Webconsulting\Desiderio\Library\PreviewWarmer;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\ElementCatalogDefinitions;
use Webconsulting\Desiderio\Seeding\ElementLibraryValueGenerator;
use Webconsulting\Desiderio\Seeding\LibraryElementUpserter;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\SeedPageUpserter;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;

#[AsCommand(
    name: 'desiderio:library:seed',
    description: 'Seed one demo record per content element (Desiderio + Innesto) into the "Element Library" sysfolder used by visual element pickers.'
)]
final class SeedElementLibraryCommand extends Command
{
    private const FOLDER_TITLE = 'Element Library';
    private const FOLDER_SLUG = '/element-library';
    private const DOKTYPE_SYSFOLDER = 254;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
        private readonly StorageRepository $storageRepository,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly ElementCatalog $elementCatalog,
        private readonly PreviewWarmer $previewWarmer,
        private readonly CacheManager $cacheManager,
        private readonly LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('parent', null, InputOption::VALUE_REQUIRED, 'Site root page uid the Element Library sysfolder is created below.')
            ->addOption('no-warm', null, InputOption::VALUE_NONE, 'Skip warming the preview page cache after seeding.')
            ->addOption('allow-production', null, InputOption::VALUE_NONE, 'Run even when Application Context is Production.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentOption = $input->getOption('parent');
        $parentPid = is_numeric($parentOption) ? (int)$parentOption : 0;
        if ($parentPid <= 0) {
            $io->error('Pass --parent=<site root page uid>.');
            return self::FAILURE;
        }

        $workspaceAspectId = $this->context->getPropertyFromAspect('workspace', 'id', 0);
        $workspaceId = is_numeric($workspaceAspectId) ? (int)$workspaceAspectId : 0;
        if ($workspaceId !== 0) {
            $io->error(sprintf('Refusing to seed inside workspace #%d. Switch to the live workspace first.', $workspaceId));
            return self::FAILURE;
        }
        if (!(bool)$input->getOption('allow-production') && Environment::getContext()->isProduction()) {
            $io->error('Refusing to run in Production application context. Pass --allow-production on a sandbox only.');
            return self::FAILURE;
        }

        $elements = $this->elementCatalog->getElements();
        if ($elements === []) {
            $io->error('No content elements found (is desiderio set up correctly?).');
            return self::FAILURE;
        }

        $now = time();
        $pageColumns = $this->databaseSchema->getColumnNames('pages');
        $pageUpserter = new SeedPageUpserter($this->connectionPool, $this->databaseSchema, $this->liveWorkspaceQueryHelper);

        $folderAttributes = ['doktype' => self::DOKTYPE_SYSFOLDER, 'nav_hide' => 1, 'no_index' => 1];
        $folderUid = $pageUpserter->findExistingPageUid($parentPid, self::FOLDER_TITLE, self::FOLDER_SLUG, $pageColumns);
        if ($folderUid === null) {
            $folderUid = $pageUpserter->create($parentPid, self::FOLDER_TITLE, self::FOLDER_SLUG, 999000, $now, $pageColumns, $folderAttributes);
            $io->writeln(sprintf('Created sysfolder "%s" (uid %d)', self::FOLDER_TITLE, $folderUid));
        } else {
            $pageUpserter->update($folderUid, self::FOLDER_TITLE, self::FOLDER_SLUG, 999000, $now, $pageColumns, $folderAttributes);
        }

        $upserter = new LibraryElementUpserter(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            new StyleguideFixtureResolver(
                $this->databaseSchema,
                new ElementLibraryValueGenerator(),
                new StyleguideCollectionAliasPolicy($this->databaseSchema)
            ),
            new CollectionCleanupService($this->connectionPool, $this->databaseSchema, $this->liveWorkspaceQueryHelper),
            new ElementCatalogDefinitions($this->elementCatalog),
        );

        $created = 0;
        $updated = 0;
        $errors = [];
        $io->progressStart(count($elements));
        foreach ($elements as $index => $element) {
            try {
                [$status] = $upserter->upsert($folderUid, $element, ($index + 1) * 16, $now);
                $status === 'created' ? $created++ : $updated++;
            } catch (\Throwable $e) {
                $errors[] = $element['cType'] . ': ' . $e->getMessage();
            }
            $io->progressAdvance();
        }
        $io->progressFinish();

        $removed = $upserter->removeObsolete($folderUid, array_column($elements, 'cType'), $now);

        // Drop stale cached previews so warming re-renders current content
        $this->cacheManager->getCache('pages')->flush();

        $io->section('Element library seed result');
        $io->writeln(sprintf(
            'Folder uid: %d | %d created, %d updated, %d obsolete removed, %d errors',
            $folderUid,
            $created,
            $updated,
            $removed,
            count($errors)
        ));
        $io->writeln(sprintf('Set site setting elementLibrary.storagePid: %d', $folderUid));
        foreach ($errors as $error) {
            $io->warning($error);
        }

        if (!(bool)$input->getOption('no-warm')) {
            $io->section('Warming preview cache');
            // Warm every site that shows this folder in its picker (each renders
            // the previews from its own base), not just the folder-owning site.
            $result = $this->previewWarmer->warm($folderUid, $this->previewWarmer->getSitesForLibraryFolder($folderUid));
            $io->writeln(sprintf('%d previews warmed, %d failed', $result['warmed'], count($result['failed'])));
            foreach (array_slice($result['failed'], 0, 10) as $failure) {
                $io->warning($failure);
            }
        }

        return $errors === [] ? self::SUCCESS : self::FAILURE;
    }
}
