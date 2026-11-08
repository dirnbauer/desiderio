<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;

/**
 * The contract of desiderio:styleguide:seed: its options, the guards it
 * refuses on, how it rewrites {{page:…}} placeholders, and the collection
 * tables it derives from the Content Block definitions.
 */
final class StyleguideSeedCommandTest extends AbstractStyleguideSeedingTestCase
{
    public function testCommandUsesConfiguredParentPageAndStyleguideFixtures(): void
    {
        $commandFile = __DIR__ . '/../../Classes/Command/SeedStyleguidePagesCommand.php';
        $source = (string)file_get_contents($commandFile);

        self::assertStringContainsString(
            'DEFAULT_PARENT_PID = ' . SeedStyleguidePagesCommand::DEFAULT_PARENT_PID . ';',
            $source,
            'The default parent page id is part of the command contract.'
        );
        self::assertStringContainsString("name: 'desiderio:styleguide:seed'", $source);
        self::assertStringContainsString("->addOption(\n                'dry-run'", $source);
        self::assertStringContainsString("'skip-powermail'", $source);
        self::assertStringContainsString('PowermailDemoSeeder', $source);
        self::assertStringContainsString("'skip-news'", $source);
        self::assertStringContainsString('NewsDemoSeeder', $source);
        self::assertStringContainsString('buildSeoPageAttributes', $source);
        self::assertStringContainsString('StyleguideContentGroups::getGroupsWithFixtures()', $source);
        self::assertStringContainsString("getPropertyFromAspect('workspace', 'id', 0)", $source);
        self::assertStringContainsString('getFixtureResolver()->buildContentInsert(', $source);
        self::assertStringContainsString('softDeleteSeededContent(', $source);

        $cleanerSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/DesiderioContentCleaner.php');
        self::assertStringContainsString("->update('tt_content')", $cleanerSource);
        self::assertStringContainsString("'desiderio_%'", $cleanerSource);
        self::assertStringContainsString('deleteCollectionRowsForParentUids(', $cleanerSource);
        self::assertStringContainsString("buildLiveWorkspaceConstraints(\$queryBuilder, 'tt_content')", $cleanerSource);

        $cleanupSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/CollectionCleanupService.php');
        self::assertStringContainsString("buildLiveWorkspaceConstraints(\$queryBuilder, 'sys_file_reference')", $cleanupSource);

        $elementSeederSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/ContentElementSeeder.php');
        self::assertStringContainsString('seedFileReferences(', $elementSeederSource);

        // The seeder draws its demo media from the pools in StyleguideDemoAssets;
        // the resolver only decides which pool a field belongs to.
        $demoAssetsSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/StyleguideDemoAssets.php');
        self::assertStringContainsString('Resources/Public/Styleguide/Unsplash', $demoAssetsSource);

        $fixtureResolverSource = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/StyleguideFixtureResolver.php');
        self::assertStringContainsString('StyleguideDemoAssets::seederImageAssets()', $fixtureResolverSource);

        $tcaOverride = (string)file_get_contents(__DIR__ . '/../../Configuration/TCA/Overrides/tt_content.php');
        self::assertStringNotContainsString('columnsOverrides', $tcaOverride);
        self::assertStringNotContainsString('Yaml::parseFile', $tcaOverride);
    }

    public function testNewsDemoQuoteSeedsUseSupportedQuoteVariants(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/NewsDemoSeeder.php');
        preg_match_all("/\\\$this->block\\('desiderio_quote', \\[(.*?)\\]\\)/s", $source, $matches);

        self::assertNotEmpty($matches[1]);
        foreach ($matches[1] as $quoteSeed) {
            self::assertStringNotContainsString("'variant' => 'default'", $quoteSeed);
        }
    }

    public function testCommandRefusesToSeedInOfflineWorkspace(): void
    {
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(42));
        $command = new SeedStyleguidePagesCommand(
            self::createStub(ConnectionPool::class),
            $context,
            self::createStub(StorageRepository::class),
            new DatabaseSchemaHelper(self::createStub(ConnectionPool::class)),
        );

        $tester = new CommandTester($command);

        self::assertSame(Command::FAILURE, $tester->execute([]));
        self::assertStringContainsString('Refusing to seed inside workspace #42', $tester->getDisplay());
    }

    public function testPagePlaceholdersAreReplacedInsideRichText(): void
    {
        $block = [
            'ctype' => 'text',
            'colPos' => 0,
            'fields' => [
                'bodytext' => '<p><a href="{{page:content-types/navigation-wayfinding}}">Navigation</a></p>',
                'button_link' => '{{page:chapter-hero}}',
                'fallback_link' => '{{page:missing}}',
            ],
        ];

        $resolved = $this->invokeMethod($this->createCommand(), 'substituteLinkPlaceholders', [
            $block,
            [
                'content-types/navigation-wayfinding' => 670,
                'chapter-hero' => 669,
            ],
        ]);

        self::assertIsArray($resolved);
        $fields = $resolved['fields'] ?? null;
        self::assertIsArray($fields);
        self::assertSame(
            '<p><a href="t3://page?uid=670">Navigation</a></p>',
            $fields['bodytext'] ?? null
        );
        self::assertSame('t3://page?uid=669', $fields['button_link'] ?? null);
        self::assertSame('https://github.com/dirnbauer/desiderio', $fields['fallback_link'] ?? null);
    }

    public function testSitePlaceholdersLinkRootPagesAndDropMissingSites(): void
    {
        $block = [
            'ctype' => 'desiderio_categorycards',
            'colPos' => 0,
            'fields' => [
                'header' => 'More sites',
                'items' => [
                    ['title' => 'Astryx', 'link' => '{{site:astryx-typo3}}'],
                    ['title' => 'Gone', 'link' => '{{site:not-here}}'],
                    ['title' => 'Plain', 'link' => 'https://typo3.org'],
                ],
            ],
        ];

        $resolved = $this->invokeMethod($this->createCommand(), 'resolveSitePlaceholders', [$block, ['astryx-typo3' => 1290]]);

        self::assertIsArray($resolved);
        $fields = $resolved['fields'] ?? null;
        self::assertIsArray($fields);
        self::assertSame(
            [
                ['title' => 'Astryx', 'link' => 't3://page?uid=1290'],
                ['title' => 'Plain', 'link' => 'https://typo3.org'],
            ],
            $fields['items'] ?? null
        );
    }

    public function testABlockOfMissingSitesIsLeftOut(): void
    {
        $block = [
            'ctype' => 'desiderio_categorycards',
            'colPos' => 0,
            'fields' => ['items' => [['title' => 'Gone', 'link' => '{{site:not-here}}']]],
        ];

        self::assertNull($this->invokeMethod($this->createCommand(), 'resolveSitePlaceholders', [$block, []]));
    }

    public function testEveryShowcaseSitePlaceholderNamesALabSite(): void
    {
        $known = [];
        $configs = glob(__DIR__ . '/../../../../config/sites/*/config.yaml');
        foreach (is_array($configs) ? $configs : [] as $config) {
            $known[basename(dirname($config))] = true;
        }
        if ($known === []) {
            self::markTestSkipped('Only meaningful inside the lab, which has config/sites.');
        }

        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Data/StyleguideShowcasePages.php')
            . (string)file_get_contents(__DIR__ . '/../../Classes/Data/Showcase/ShowcaseFeatureDefinitions.php');
        // {{site:<identifier>}} or {{site:<identifier>/<slug>}}: the identifier is what must exist.
        preg_match_all('/\{\{site:([a-z0-9_-]+)(?:\/[^}]*)?\}\}/', $source, $matches);
        self::assertNotSame([], $matches[1]);
        self::assertSame([], array_values(array_filter($matches[1], static fn(string $identifier): bool => !isset($known[$identifier]))));
    }

    public function testSitePlaceholdersResolveAnywhereInAField(): void
    {
        $block = [
            'ctype' => 'desiderio_herosaas',
            'colPos' => 0,
            'fields' => [
                'primary_link' => '{{site:astryx-typo3}}',
                'secondary_link' => '{{site:astryx-typo3/components}}',
                'search_link' => '{{page:search}}&tx_solr%5Bq%5D=Desiderio',
                'gone_link' => '{{site:not-here}}',
            ],
        ];

        $resolved = $this->invokeMethod($this->createCommand(), 'substituteLinkPlaceholders', [
            $block,
            ['site:astryx-typo3' => 1290, 'site:astryx-typo3/components' => 1302, 'search' => 738],
        ]);

        self::assertIsArray($resolved);
        $fields = $resolved['fields'] ?? null;
        self::assertIsArray($fields);
        self::assertSame('t3://page?uid=1290', $fields['primary_link'] ?? null);
        self::assertSame('t3://page?uid=1302', $fields['secondary_link'] ?? null);
        self::assertSame('t3://page?uid=738&tx_solr%5Bq%5D=Desiderio', $fields['search_link'] ?? null);
        self::assertSame('https://github.com/dirnbauer/desiderio', $fields['gone_link'] ?? null);
    }

    /**
     * Links into a page section keep their anchor, and rich text may carry a
     * query string as &amp; (parseFunc decodes the href before typolink).
     */
    public function testPagePlaceholdersKeepAnchorsAndQueryStrings(): void
    {
        $block = [
            'ctype' => 'desiderio_contentsidebar',
            'colPos' => 0,
            'fields' => [
                'sidebar_content' => '<p><a href="{{page:ai/agentic-skills/find}}&amp;tx_solr%5Bq%5D=typo3-upgrade-run">Search</a></p>',
                'items' => [['link' => '{{page:ai/agentic-skills/proven-site-upgrade}}#typo3-upgrade-run']],
            ],
        ];

        $resolved = $this->invokeMethod($this->createCommand(), 'substituteLinkPlaceholders', [
            $block,
            ['ai/agentic-skills/find' => 1030, 'ai/agentic-skills/proven-site-upgrade' => 9001],
        ]);

        self::assertIsArray($resolved);
        $fields = $resolved['fields'] ?? null;
        self::assertIsArray($fields);
        self::assertSame('<p><a href="t3://page?uid=1030&amp;tx_solr%5Bq%5D=typo3-upgrade-run">Search</a></p>', $fields['sidebar_content'] ?? null);
        self::assertSame([['link' => 't3://page?uid=9001#typo3-upgrade-run']], $fields['items'] ?? null);
    }

    public function testARetiredPageRedirectsToItsSuccessorsAnchorInEveryLanguage(): void
    {
        $row = $this->invokeMethod($this->createCommand(), 'slugRedirectRow', [
            '/ai/agentic-skills/typo3-upgrade-run',
            'ai/agentic-skills/proven-site-upgrade#typo3-upgrade-run',
            'Desiderio showcase: retired.',
            '/',
            ['de/', 'zh/', 'hu/'],
        ]);

        self::assertIsArray($row);
        self::assertSame('#^/((?:de/|zh/|hu/)?)ai/agentic\-skills/typo3\-upgrade\-run/?$#', $row['source_path'] ?? null);
        self::assertSame('/$1ai/agentic-skills/proven-site-upgrade#typo3-upgrade-run', $row['target'] ?? null);
        self::assertSame(1, $row['is_regexp'] ?? null);
        self::assertSame(301, $row['target_statuscode'] ?? null);
        self::assertSame('*', $row['source_host'] ?? null);

        $sourcePath = $row['source_path'] ?? '';
        self::assertIsString($sourcePath);
        foreach (['/ai/agentic-skills/typo3-upgrade-run' => '', '/de/ai/agentic-skills/typo3-upgrade-run/' => 'de/', '/hu/ai/agentic-skills/typo3-upgrade-run' => 'hu/'] as $path => $prefix) {
            self::assertSame(1, preg_match($sourcePath, $path, $match), $path);
            self::assertSame($prefix, $match[1]);
        }
        self::assertSame(0, preg_match($sourcePath, '/ai/agentic-skills/typo3-upgrade-run-x'));
        self::assertSame(0, preg_match($sourcePath, '/ai/agentic-skills/find'));
    }

    public function testRedirectsAtARetiredPageMoveToTheSuccessorsAnchor(): void
    {
        $command = $this->createCommand();
        $repoint = fn(string $target): mixed => $this->invokeMethod($command, 'repointedRedirectTarget', [$target, 8244, 9001, 'typo3-upgrade-run']);

        self::assertSame('t3://page?uid=9001#typo3-upgrade-run', $repoint('t3://page?uid=8244'));
        self::assertSame('t3://page?uid=9001&_language=1#typo3-upgrade-run', $repoint('t3://page?uid=8244&_language=1'));
        self::assertSame('t3://page?uid=9001&_language=3#typo3-upgrade-run', $repoint('t3://page?uid=8244&_language=3#c12'));
        self::assertNull($repoint('t3://page?uid=82440&_language=1'), 'Another page whose uid starts the same.');
        self::assertNull($repoint('t3://page?uid=8243&_language=1'));
        self::assertNull($repoint('/ai/agentic-skills/typo3-upgrade-run'));
    }

    public function testSeededPagesFollowAPageAnotherPackagePutFirst(): void
    {
        $command = $this->createCommand();
        // The skill search sits first below the hub at sorting 304; the
        // seeded group pages planned at 608, 624 … keep their order after it.
        self::assertSame(912, $this->invokeMethod($command, 'sortingAfterLeadingChild', [608, 304]));
        self::assertSame(928, $this->invokeMethod($command, 'sortingAfterLeadingChild', [624, 304]));
        self::assertGreaterThan(5000, $this->invokeMethod($command, 'sortingAfterLeadingChild', [16, 5000]));
    }

    public function testUnresolvedPagePlaceholdersAreNamed(): void
    {
        $unresolved = $this->invokeMethod($this->createCommand(), 'unresolvedPagePlaceholders', [
            [[['fields' => ['link' => '{{page:ai/agentic-skills/find}}&tx_solr%5Bq%5D=x', 'other' => '{{page:pricing}}', 'site' => '{{site:astryx-typo3}}']]]],
            ['pricing' => 12],
        ]);

        self::assertSame(['ai/agentic-skills/find'], $unresolved);
    }

    public function testADryRunListsTheRetiredPagesOfTheSelectedSuccessors(): void
    {
        $listing = $this->invokeMethod($this->createCommand(), 'retirementListing', [
            [
                '/ai/agentic-skills/typo3-webmcp' => ['successor' => '/ai/agentic-skills/search-seo-agents', 'anchor' => 'typo3-webmcp'],
                '/ai/agentic-skills/typo3-rector' => ['successor' => '/ai/agentic-skills/code-migration', 'anchor' => 'typo3-rector'],
            ],
            static fn(string $slug): bool => $slug === '/ai/agentic-skills/code-migration',
        ]);

        self::assertSame(['Retires /ai/agentic-skills/typo3-rector (page, translations, content) and redirects it to /ai/agentic-skills/code-migration#typo3-rector'], $listing);
    }

    public function testNoShowcaseDefinitionLinksAPlainPath(): void
    {
        // A plain "/path" never gets a language prefix: from /de/ it opened
        // the English page. Showcase links are placeholders or full URLs;
        // files (/llms.txt) and the API's routes have no language.
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Data/Showcase/ShowcaseFeatureDefinitions.php');
        preg_match_all("/'link' => '(\/(?!api\/)[^'.]*)'/", $source, $matches);
        self::assertSame([], $matches[1]);
    }

    public function testEveryPagePlaceholderInAnElementFixtureNamesASeededPage(): void
    {
        $known = ['home' => true];
        foreach (array_keys(new \ReflectionClassConstant(SeedStyleguidePagesCommand::class, 'CONTENT_TYPE_GROUP_SLUGS')->getValue()) as $groupId) {
            $known['chapter-' . $groupId] = true;
        }
        foreach ([...StyleguideShowcasePages::subpages(), ...StyleguideShowcasePages::blogSupportPages()] as $page) {
            $known[ltrim($page['slug'], '/')] = true;
        }

        $fixtures = self::elementFixtures();
        self::assertNotSame([], $fixtures);
        $unknown = [];
        foreach ($fixtures as $fixture) {
            preg_match_all('/\{\{page:([^}]+)\}\}/', (string)file_get_contents($fixture), $matches);
            foreach ($matches[1] as $slug) {
                if (!isset($known[$slug])) {
                    $unknown[] = basename(dirname($fixture)) . ': ' . $slug;
                }
            }
        }

        self::assertSame([], $unknown, 'Placeholders the styleguide seeder cannot resolve');
    }

    public function testNoElementFixtureLinksAPathThatDoesNotExist(): void
    {
        // Internal links in fixtures are {{page:<slug>}} placeholders: a
        // hard-coded path such as "/pricing" is a 404 on every installation.
        $paths = [];
        foreach (self::elementFixtures() as $fixture) {
            if (preg_match_all('/"(?:[a-z_]*link[a-z_]*|url|href)"\s*:\s*"(\/[^"]*)"/', (string)file_get_contents($fixture), $matches) > 0) {
                foreach ($matches[1] as $path) {
                    $paths[] = basename(dirname($fixture)) . ': ' . $path;
                }
            }
        }

        self::assertSame([], $paths);
    }

    /**
     * @return list<string>
     */
    private static function elementFixtures(): array
    {
        $fixtures = glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/fixture.json');

        return $fixtures === false ? [] : $fixtures;
    }

    public function testCollectionTableNamesAreDerivedUniquelyFromContentBlockDefinitions(): void
    {
        $command = $this->createCommand();
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'desiderio_footercolumns' => [
                'fields' => [],
                'collections' => [
                    'column_items' => [
                        'table' => 'column_items',
                        'fields' => [],
                        'minItems' => 1,
                        'maxItems' => null,
                    ],
                    'link_items' => [
                        'table' => 'link_items',
                        'fields' => [],
                        'minItems' => 1,
                        'maxItems' => null,
                    ],
                ],
            ],
            'desiderio_footerdark' => [
                'fields' => [],
                'collections' => [
                    'column_items' => [
                        'table' => 'column_items',
                        'fields' => [],
                        'minItems' => 1,
                        'maxItems' => null,
                    ],
                ],
            ],
        ]);

        self::assertSame(
            ['column_items', 'link_items'],
            new ContentBlockCollectionMap()->getCollectionTableNames()
        );
    }

    public function testContentBlockDefinitionKeepsCollectionItemLimits(): void
    {
        $command = $this->createCommand();

        $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig([
            'name' => 'desiderio/demo',
            'prefixFields' => false,
            'fields' => [
                [
                    'identifier' => 'items',
                    'type' => 'Collection',
                    'table' => 'demo_items',
                    'prefixField' => true,
                    'minItems' => 2,
                    'maxItems' => 4,
                    'fields' => [
                        [
                            'identifier' => 'title',
                            'type' => 'Textarea',
                        ],
                    ],
                ],
            ],
        ]);
        self::assertIsArray($definition);
        $collections = $definition['collections'] ?? null;
        self::assertIsArray($collections);
        $items = $collections['items'] ?? null;
        self::assertIsArray($items);

        self::assertSame(2, $items['minItems']);
        self::assertSame(4, $items['maxItems']);
        self::assertSame('desiderio_demo_items', $items['column']);
    }
}
