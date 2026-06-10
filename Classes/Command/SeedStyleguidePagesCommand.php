<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\ContentElementSeeder;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\DesiderioContentCleaner;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\SeedPageUpserter;
use Webconsulting\Desiderio\Seeding\StarterContentBuilder;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;
use Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;

#[AsCommand(
    name: 'desiderio:styleguide:seed',
    description: 'Create or update shadcn styled Desiderio content element test pages below a parent page.'
)]
final class SeedStyleguidePagesCommand extends Command
{
    public const DEFAULT_PARENT_PID = 505;
    private const STYLEGUIDE_FAL_FOLDER = 'desiderio-styleguide';

    /**
     * One house preset per styleguide page so the seeded tree doubles as a
     * live theme showcase. Applied via pages.tx_desiderio_shadcn_preset and
     * picked up by the body tag TypoScript (levelfield slide).
     */
    private const STYLEGUIDE_PAGE_PRESETS = [
        'aurora',
        'marine',
        'forest',
        'ember',
        'bloom',
        'lagoon',
        'gold',
        'midnight',
        'blossom',
        'citrus',
    ];

    private readonly StyleguideCollectionAliasPolicy $collectionAliasPolicy;
    private ?SeedPageUpserter $pageUpserter = null;
    private ?DesiderioContentCleaner $contentCleaner = null;
    private ?ContentElementSeeder $contentElementSeeder = null;
    private ?StyleguideFixtureResolver $fixtureResolver = null;
    private ?StarterContentBuilder $starterContentBuilder = null;

    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly Context $context,
        private readonly StorageRepository $storageRepository,
        private readonly DatabaseSchemaHelper $databaseSchema,
        private ?PowermailDemoSeeder $powermailDemoSeeder = null,
        private readonly StyleguideDemoValueGenerator $demoValueGenerator = new StyleguideDemoValueGenerator(),
        ?StyleguideCollectionAliasPolicy $collectionAliasPolicy = null,
    ) {
        parent::__construct();
        $this->collectionAliasPolicy = $collectionAliasPolicy ?? new StyleguideCollectionAliasPolicy($this->databaseSchema);
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
            )
            ->addOption(
                'allow-production',
                null,
                InputOption::VALUE_NONE,
                'Run even when Application Context is Production. Required to seed against production data.'
            )
            ->addOption(
                'skip-powermail',
                null,
                InputOption::VALUE_NONE,
                'Do not create the optional powermail demo form pages, even when powermail is installed.'
            )
            ->addOption(
                'powermail-storage-pid',
                null,
                InputOption::VALUE_REQUIRED,
                'Storage page uid for generated powermail form records. Defaults to the generated Desiderio Powermail Lab page.',
                '0'
            )
            ->addOption(
                'powermail-german-language',
                null,
                InputOption::VALUE_REQUIRED,
                'sys_language_uid used for German powermail demo translations.',
                '1'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $parentPid = (int)$input->getOption('parent');
        $dryRun = (bool)$input->getOption('dry-run');
        $allowProduction = (bool)$input->getOption('allow-production');
        $skipPowermail = (bool)$input->getOption('skip-powermail');
        $powermailStoragePid = $this->getIntegerInputOption($input, 'powermail-storage-pid');
        $powermailGermanLanguageUid = $this->getIntegerInputOption($input, 'powermail-german-language');

        $workspaceId = (int)$this->context->getPropertyFromAspect('workspace', 'id', 0);
        if ($workspaceId !== 0) {
            $io->error(sprintf(
                'Refusing to seed inside workspace #%d. The seeder writes live records and bypasses workspace overlays. Switch to the live workspace before running this command.',
                $workspaceId
            ));

            return self::FAILURE;
        }

        if (!$allowProduction && Environment::getContext()->isProduction()) {
            $io->error('Refusing to run in Production application context. Pass --allow-production to override (and only do so on a sandbox).');

            return self::FAILURE;
        }
        $groups = StyleguideContentGroups::getGroupsWithFixtures();
        $showcasePages = StyleguideShowcasePages::subpages();
        $totalElements = array_sum(array_map(
            static fn (array $group): int => count($group['elements']),
            $groups
        )) + StyleguideShowcasePages::contentElementCount();

        if ($dryRun) {
            $io->title('Desiderio styleguide seed dry run');
            $listing = [];
            $listing[] = sprintf('Homepage (on parent page): %d marketing elements', count(StyleguideShowcasePages::homeContent()));
            foreach ($showcasePages as $page) {
                $listing[] = sprintf('%s: %d elements', $page['title'], count($page['content']));
            }
            foreach ($groups as $index => $group) {
                $listing[] = sprintf(
                    '%s: %d elements — theme "%s"',
                    $group['groupTitle'],
                    count($group['elements']),
                    $this->presetForPageIndex($index)
                );
            }
            $io->listing($listing);
            if (!$skipPowermail) {
                $powermailForms = $this->getPowermailDemoSeeder()->getDemoForms();
                $io->listing(array_map(
                    static fn (array $form): string => sprintf('Powermail demo: %s', $form['pageTitleEn']),
                    $powermailForms
                ));
            }
            $io->success(sprintf(
                'Would create or update %d styleguide pages and %d content elements below page uid %d%s.',
                count($groups) + count($showcasePages),
                $totalElements,
                $parentPid,
                $skipPowermail ? '' : sprintf(', plus %d powermail demo forms with EN/DE pages if powermail tables are available', count($this->getPowermailDemoSeeder()->getDemoForms()))
            ));

            return self::SUCCESS;
        }

        $pageColumns = $this->databaseSchema->getColumnNames('pages');
        $contentColumns = $this->databaseSchema->getColumnNames('tt_content');
        $createdPages = 0;
        $createdContentElements = 0;
        $now = time();

        $pageUpserter = $this->getPageUpserter();
        $contentCleaner = $this->getContentCleaner();
        $contentElementSeeder = $this->getContentElementSeeder();

        $linkTargets = [];

        foreach ($groups as $index => $group) {
            $title = (string)$group['groupTitle'];
            $slug = '/desiderio-' . (string)$group['groupId'];
            $sorting = ($index + 1) * 256;

            $pageAttributes = ['tx_desiderio_shadcn_preset' => $this->presetForPageIndex($index)];

            $pageUid = $pageUpserter->findExistingPageUid($parentPid, $title, $slug, $pageColumns);
            if ($pageUid === null) {
                $pageUid = $pageUpserter->create($parentPid, $title, $slug, $sorting, $now, $pageColumns, $pageAttributes);
                $createdPages++;
            } else {
                $pageUpserter->update($pageUid, $title, $slug, $sorting, $now, $pageColumns, $pageAttributes);
            }

            $linkTargets['chapter-' . (string)$group['groupId']] = $pageUid;

            $contentCleaner->softDeleteSeededContent($pageUid, $now, [], true);

            foreach ($group['elements'] as $elementIndex => $element) {
                $contentData = $this->getFixtureResolver()->buildContentInsert(
                    $pageUid,
                    (string)$element['ctype'],
                    (string)$element['name'],
                    $element['fixture'],
                    ($elementIndex + 1) * 256,
                    $now,
                    $contentColumns
                );

                $contentElementSeeder->insert($pageUid, $now, $contentData);
                $createdContentElements++;
            }
        }

        // Marketing showcase: subpages first (so internal links resolve), then
        // homepage content on the parent page itself, then subpage content.
        $showcaseBlocks = [];
        foreach ($showcasePages as $index => $page) {
            $sorting = ($index + 1) * 16;
            $pageAttributes = ['nav_title' => $page['navTitle'], 'abstract' => $page['abstract']];

            $pageUid = $pageUpserter->findExistingPageUid($parentPid, $page['title'], $page['slug'], $pageColumns);
            if ($pageUid === null) {
                $pageUid = $pageUpserter->create($parentPid, $page['title'], $page['slug'], $sorting, $now, $pageColumns, $pageAttributes);
                $createdPages++;
            } else {
                $pageUpserter->update($pageUid, $page['title'], $page['slug'], $sorting, $now, $pageColumns, $pageAttributes);
            }

            $linkTargets[ltrim($page['slug'], '/')] = $pageUid;
            $showcaseBlocks[$pageUid] = $page['content'];
        }

        $showcaseBlocks[$parentPid] = StyleguideShowcasePages::homeContent();

        foreach ($showcaseBlocks as $pageUid => $blocks) {
            $contentCleaner->softDeleteSeededContent($pageUid, $now, [], true);
            foreach ($blocks as $blockIndex => $block) {
                $block = $this->substituteLinkPlaceholders($block, $linkTargets);
                $contentData = $this->getStarterContentBuilder()->buildContentInsert(
                    $pageUid,
                    $block,
                    ($blockIndex + 1) * 256,
                    $now,
                    $contentColumns
                );

                $contentElementSeeder->insert($pageUid, $now, $contentData);
                $createdContentElements++;
            }
        }

        $powermailSummary = ['pages' => 0, 'forms' => 0, 'skipped' => true];
        if (!$skipPowermail) {
            $powermailSummary = $this->getPowermailDemoSeeder()->seed(
                $parentPid,
                $powermailStoragePid,
                $powermailGermanLanguageUid,
                $now,
                $io
            );
        }

        $io->success(sprintf(
            'Created or updated %d styleguide pages (%d new) and inserted %d Desiderio content elements below page uid %d%s.',
            count($groups) + count($showcasePages),
            $createdPages,
            $createdContentElements,
            $parentPid,
            $powermailSummary['skipped'] ? '' : sprintf(' Added %d powermail demo forms across %d EN/DE pages.', $powermailSummary['forms'], $powermailSummary['pages'])
        ));

        return self::SUCCESS;
    }

    private function presetForPageIndex(int $index): string
    {
        return self::STYLEGUIDE_PAGE_PRESETS[$index % count(self::STYLEGUIDE_PAGE_PRESETS)];
    }

    /**
     * Replaces {{page:<slug>}} placeholders in showcase block fields with
     * t3://page links once the target pages exist.
     *
     * @param array{ctype: string, colPos: int, fields: array<string, mixed>} $block
     * @param array<string, int> $linkTargets
     * @return array{ctype: string, colPos: int, fields: array<string, mixed>}
     */
    private function substituteLinkPlaceholders(array $block, array $linkTargets): array
    {
        $fields = [];
        foreach ($this->substituteLinkPlaceholdersInValue($block['fields'], $linkTargets) as $key => $value) {
            if (is_string($key)) {
                $fields[$key] = $value;
            }
        }
        $block['fields'] = $fields;

        return $block;
    }

    /**
     * @param array<array-key, mixed> $values
     * @param array<string, int> $linkTargets
     * @return array<array-key, mixed>
     */
    private function substituteLinkPlaceholdersInValue(array $values, array $linkTargets): array
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $values[$key] = $this->substituteLinkPlaceholdersInValue($value, $linkTargets);
                continue;
            }
            if (!is_string($value) || !str_starts_with($value, '{{page:')) {
                continue;
            }

            $slug = substr($value, 7, -2);
            $values[$key] = isset($linkTargets[$slug])
                ? 't3://page?uid=' . $linkTargets[$slug]
                : 'https://github.com/dirnbauer/desiderio';
        }

        return $values;
    }

    private function getStarterContentBuilder(): StarterContentBuilder
    {
        return $this->starterContentBuilder ??= new StarterContentBuilder($this->databaseSchema);
    }

    private function getPowermailDemoSeeder(): PowermailDemoSeeder
    {
        // DI provides the seeder; the fallback only serves direct instantiation in tests.
        return $this->powermailDemoSeeder ??= new PowermailDemoSeeder($this->connectionPool, $this->databaseSchema);
    }

    private function getIntegerInputOption(InputInterface $input, string $name): int
    {
        $value = $input->getOption($name);
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int)$value;
        }

        return 0;
    }

    private function getPageUpserter(): SeedPageUpserter
    {
        return $this->pageUpserter ??= new SeedPageUpserter(
            $this->connectionPool,
            $this->databaseSchema,
            new LiveWorkspaceQueryHelper($this->databaseSchema),
        );
    }

    private function getContentCleaner(): DesiderioContentCleaner
    {
        if ($this->contentCleaner === null) {
            $liveWorkspaceQueryHelper = new LiveWorkspaceQueryHelper($this->databaseSchema);
            $this->contentCleaner = new DesiderioContentCleaner(
                $this->connectionPool,
                $liveWorkspaceQueryHelper,
                new CollectionCleanupService($this->connectionPool, $this->databaseSchema, $liveWorkspaceQueryHelper),
                new ContentBlockCollectionMap(),
            );
        }

        return $this->contentCleaner;
    }

    private function getContentElementSeeder(): ContentElementSeeder
    {
        return $this->contentElementSeeder ??= new ContentElementSeeder(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            self::STYLEGUIDE_FAL_FOLDER,
            1777100143,
        );
    }

    private function getFixtureResolver(): StyleguideFixtureResolver
    {
        return $this->fixtureResolver ??= new StyleguideFixtureResolver(
            $this->databaseSchema,
            $this->demoValueGenerator,
            $this->collectionAliasPolicy,
        );
    }
}
