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
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'Language key of the demo content to seed, e.g. "de". Prefers each element\'s library.<locale>.json over library.json. The records are still written as language 0 - this picks the source language of a folder, it does not create translations.')
            ->addOption('hosts', null, InputOption::VALUE_REQUIRED, 'Comma-separated host extensions to seed, e.g. "desiderio,innesto,core" ("core" = native TYPO3 content types). Default: a folder that already has records keeps the hosts it has; a fresh folder gets every host. Records of other hosts already in the folder are removed, so this scopes a site\'s library folder to the theme it uses.')
            ->addOption('include-video', null, InputOption::VALUE_NONE, 'Opt in to seeding video content elements. Video components remain installed but are excluded from generated content by default.')
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

        $localeOption = $input->getOption('locale');
        $locale = is_string($localeOption) ? trim($localeOption) : '';
        if ($locale !== '' && preg_match('/^[A-Za-z]{2}(-[A-Za-z]{2})?$/', $locale) !== 1) {
            $io->error(sprintf('--locale must be a language key such as "de" or "pt-br", got "%s".', $locale));
            return self::FAILURE;
        }

        $elements = $this->elementCatalog->getElements($locale);

        $hostsOption = $input->getOption('hosts');
        $hosts = is_string($hostsOption) ? $hostsOption : '';
        $allowedHosts = array_values(array_unique(array_filter(
            array_map(static fn(string $host): string => strtolower(trim($host)), explode(',', $hosts)),
            static fn(string $host): bool => $host !== '',
        )));

        if ($elements === []) {
            $io->error('No content elements found (is desiderio set up correctly?).');
            return self::FAILURE;
        }
        if ($locale !== '') {
            $io->writeln(sprintf('Seeding demo content from library.%s.json (falling back to library.json).', strtolower($locale)));
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

        // Scope the folder to its theme. An explicit --hosts wins; without it,
        // a folder that already has records keeps the hosts it has — any host
        // extension registered later (desiderio_grande did this) would
        // otherwise silently flood every reseed of every existing folder with
        // its whole catalog. A fresh folder still gets every host.
        if ($allowedHosts === []) {
            $allowedHosts = $this->inferHostsFromFolder($folderUid, $elements);
            if ($allowedHosts !== []) {
                $io->writeln(sprintf(
                    'Keeping the folder\'s existing host scope: %s. Pass --hosts to change it.',
                    implode(', ', $allowedHosts)
                ));
            }
        } else {
            $io->writeln(sprintf('Seeding host(s): %s.', implode(', ', $allowedHosts)));
        }
        if ($allowedHosts !== []) {
            $elements = array_values(array_filter(
                $elements,
                static fn(array $element): bool => in_array(strtolower($element['hostExtension']), $allowedHosts, true),
            ));
            if ($elements === []) {
                $io->error(sprintf('No content elements found for host(s) "%s".', implode(', ', $allowedHosts)));
                return self::FAILURE;
            }
        }

        if (!(bool)$input->getOption('include-video')) {
            $elements = array_values(array_filter(
                $elements,
                static fn(array $element): bool => !str_contains(strtolower($element['cType']), 'video'),
            ));
        }

        // Role-based media: the library preview is copied into real pages, so a
        // video field must get a video and a logo field must get a logo. The
        // styleguide keeps the hash-over-one-pool behaviour.
        $fixtureResolver = new StyleguideFixtureResolver(
            $this->databaseSchema,
            new ElementLibraryValueGenerator(),
            new StyleguideCollectionAliasPolicy($this->databaseSchema)
        );
        $fixtureResolver->useRoleBasedImageAssets();

        $upserter = new LibraryElementUpserter(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            $fixtureResolver,
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
        $upserter->seedCategoryDemos($folderUid, $now);

        // Drop stale cached previews and picker metadata/search indexes so
        // warming and the next picker open both see the freshly seeded catalog.
        $this->cacheManager->getCache('pages')->flush();
        $this->cacheManager->getCache('desiderio_library')->flush();

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

    /**
     * The hosts already represented in a folder's live records, in catalog
     * order. CTypes the catalog no longer knows are ignored — removeObsolete
     * deals with those. An empty folder infers nothing (returns []).
     *
     * @param list<array{cType: string, hostExtension: string}> $elements
     * @return list<string> lowercase host extension keys
     */
    private function inferHostsFromFolder(int $folderUid, array $elements): array
    {
        $hostByCType = [];
        foreach ($elements as $element) {
            $hostByCType[$element['cType']] = strtolower($element['hostExtension']);
        }

        $connection = $this->connectionPool->getConnectionForTable('tt_content');
        $existingCTypes = $connection->fetchFirstColumn(
            'SELECT DISTINCT CType FROM tt_content WHERE pid = ? AND deleted = 0',
            [$folderUid]
        );

        $hosts = [];
        foreach ($existingCTypes as $cType) {
            if (is_string($cType) && isset($hostByCType[$cType])) {
                $hosts[$hostByCType[$cType]] = true;
            }
        }
        return array_keys($hosts);
    }
}
