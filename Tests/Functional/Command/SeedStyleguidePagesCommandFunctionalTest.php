<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

final class SeedStyleguidePagesCommandFunctionalTest extends FunctionalTestCase
{
    private const CONTENT_TYPE_GROUP_IDS = [
        'hero',
        'navigation',
        'content',
        'features',
        'pricing',
        'social-proof',
        'team',
        'data',
        'conversion',
        'footer',
    ];

    private const CONTENT_TYPE_PRESETS_BY_SLUG = [
        '/content-types/hero-landing-intros' => 'lagoon',
        '/content-types/navigation-wayfinding' => 'gold',
        '/content-types/content-editorial' => 'aurora',
        '/content-types/features-benefits' => 'ember',
        '/content-types/plans-pricing' => 'midnight',
        '/content-types/trust-social-proof' => 'blossom',
        '/content-types/people-team' => 'citrus',
        '/content-types/data-dashboards' => 'b27GcrRo',
        '/content-types/leads-conversion' => 'marine',
        '/content-types/footers-utility-areas' => 'bloom',
    ];

    private const CONTENT_TYPE_SUPPORT_PAGE_SLUGS = [
        '/content-types/navigation-wayfinding/wayfinding-patterns',
        '/content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails',
        '/content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour',
        '/content-types/navigation-wayfinding/wayfinding-patterns/breadcrumb-trails/truncation-behaviour/deeply-nested-example',
    ];

    private const LEGACY_ROOT_PAGE_SLUGS = [
        '/desiderio-content',
        '/desiderio-conversion',
        '/desiderio-data',
        '/desiderio-features',
        '/desiderio-footer',
        '/desiderio-hero',
        '/desiderio-navigation',
        '/desiderio-pricing',
        '/desiderio-social-proof',
        '/desiderio-team',
        '/desiderio-core',
        '/for-agencies',
        '/for-inhouse-teams',
        '/for-freelancers',
    ];

    protected array $coreExtensionsToLoad = [
        'form',
        'workspaces',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/desiderio',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/SeedingBase.csv');
    }

    public function testDryRunWritesNothing(): void
    {
        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--dry-run' => true, '--skip-powermail' => true]);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertSame(1, $this->countRows('pages'));
        self::assertSame(0, $this->countRows('tt_content'));
    }

    public function testSeedCreatesStyleguidePagesAndContentElements(): void
    {
        $groups = $this->seededContentTypeGroups(StyleguideContentGroups::getGroupsWithFixtures());
        $expectedDesiderioElements = $this->countGroupElements($groups)
            + StyleguideShowcasePages::contentElementCount()
            + $this->countChapterFramingElements($groups);
        $expectedSupportElements = count(self::CONTENT_TYPE_SUPPORT_PAGE_SLUGS);
        $expectedTopLevelPages = $this->countTopLevelShowcasePages();
        $expectedPages = count($groups) + $expectedSupportElements + count(StyleguideShowcasePages::subpages());

        $tester = $this->createCommandTester();
        $exitCode = $tester->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]);

        self::assertSame(Command::SUCCESS, $exitCode, $tester->getDisplay());
        self::assertSame($expectedTopLevelPages, $this->countRows('pages', 'pid = 1 AND deleted = 0'));
        self::assertSame($expectedPages, $this->countRows('pages', 'uid <> 1 AND deleted = 0'));
        self::assertSame(
            array_keys(self::CONTENT_TYPE_PRESETS_BY_SLUG),
            $this->fetchChildSlugs($this->intValue($this->fetchPageBySlug('/content-types'), 'uid'))
        );
        foreach (self::CONTENT_TYPE_SUPPORT_PAGE_SLUGS as $slug) {
            self::assertSame(1, $this->countRows('pages', "deleted = 0 AND slug = '" . $slug . "'"), $slug);
        }
        self::assertSame(
            $expectedDesiderioElements,
            $this->countRows('tt_content', "deleted = 0 AND CType LIKE 'desiderio_%'")
        );
        self::assertSame($expectedSupportElements, $this->countRows('tt_content', "deleted = 0 AND CType = 'text'"));
        self::assertSame(
            $expectedSupportElements,
            $this->countRows('tt_content', "deleted = 0 AND CType NOT LIKE 'desiderio_%'")
        );
    }

    public function testSeedAssignsADistinctThemePresetPerPage(): void
    {
        $tester = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $tester->execute(['--parent' => '1', '--skip-powermail' => true]), $tester->getDisplay());

        $contentTypesUid = $this->intValue($this->fetchPageBySlug('/content-types'), 'uid');
        $presetsBySlug = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery(
                'SELECT slug, tx_desiderio_shadcn_preset FROM pages WHERE pid = ? AND deleted = 0 ORDER BY sorting',
                [$contentTypesUid]
            )
            ->fetchAllKeyValue();

        self::assertSame(self::CONTENT_TYPE_PRESETS_BY_SLUG, $presetsBySlug);
    }

    public function testReseedingIsIdempotentForPagesAndLiveContent(): void
    {
        $groups = $this->seededContentTypeGroups(StyleguideContentGroups::getGroupsWithFixtures());
        $expectedDesiderioElements = $this->countGroupElements($groups)
            + StyleguideShowcasePages::contentElementCount()
            + $this->countChapterFramingElements($groups);
        $expectedSupportElements = count(self::CONTENT_TYPE_SUPPORT_PAGE_SLUGS);
        $expectedTopLevelPages = $this->countTopLevelShowcasePages();
        $expectedPages = count($groups) + $expectedSupportElements + count(StyleguideShowcasePages::subpages());

        $firstRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $firstRun->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]));

        $secondRun = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $secondRun->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]));

        self::assertStringContainsString('(0 new)', $secondRun->getDisplay());
        self::assertSame($expectedTopLevelPages, $this->countRows('pages', 'pid = 1 AND deleted = 0'));
        self::assertSame($expectedPages, $this->countRows('pages', 'uid <> 1 AND deleted = 0'));
        // The previous generation is soft-deleted, the live set stays constant.
        self::assertSame(
            $expectedDesiderioElements,
            $this->countRows('tt_content', "deleted = 0 AND CType LIKE 'desiderio_%'")
        );
        self::assertSame(
            $expectedDesiderioElements,
            $this->countRows('tt_content', "deleted = 1 AND CType LIKE 'desiderio_%'")
        );
        self::assertSame($expectedSupportElements, $this->countRows('tt_content', "deleted = 0 AND CType = 'text'"));
        self::assertSame(
            $expectedSupportElements,
            $this->countRows('tt_content', "deleted = 1 AND CType = 'text'")
        );
        self::assertSame(
            0,
            $this->countRows('pages', "deleted = 0 AND slug = '/desiderio-core'")
        );
    }

    public function testSeedSoftDeletesLegacyRootPagesAfterCanonicalTreeExists(): void
    {
        $legacyCorePageUid = null;
        foreach (self::LEGACY_ROOT_PAGE_SLUGS as $index => $slug) {
            $uid = $this->insertLegacyRootPage($slug, $index);
            if ($slug === '/desiderio-core') {
                $legacyCorePageUid = $uid;
            }
        }
        self::assertIsInt($legacyCorePageUid);
        $this->getConnectionPool()->getConnectionForTable('tt_content')->insert('tt_content', [
            'pid' => $legacyCorePageUid,
            'CType' => 'text',
            'header' => 'Legacy core demo',
            'deleted' => 0,
        ]);

        $tester = $this->createCommandTester();
        self::assertSame(Command::SUCCESS, $tester->execute(['--parent' => '1', '--skip-powermail' => true, '--skip-news' => true]), $tester->getDisplay());

        foreach (self::LEGACY_ROOT_PAGE_SLUGS as $slug) {
            self::assertSame(0, $this->countRows('pages', "deleted = 0 AND slug = '" . $slug . "'"), $slug);
            self::assertSame(1, $this->countRows('pages', "deleted = 1 AND slug = '" . $slug . "'"), $slug);
        }
        self::assertSame(0, $this->countRows('tt_content', 'deleted = 0 AND pid = ' . $legacyCorePageUid));
        self::assertSame(1, $this->countRows('tt_content', 'deleted = 1 AND pid = ' . $legacyCorePageUid));
    }

    /**
     * @param list<array{groupId: string, elements: list<array<string, mixed>>}> $groups
     */
    private function countGroupElements(array $groups): int
    {
        $count = 0;
        foreach ($groups as $group) {
            $count += count($group['elements']);
        }

        return $count;
    }

    /**
     * @param list<array{groupId: string, groupTitle: string, elements: list<array<string, mixed>>}> $groups
     * @return list<array{groupId: string, groupTitle: string, elements: list<array<string, mixed>>}>
     */
    private function seededContentTypeGroups(array $groups): array
    {
        $groupsById = [];
        foreach ($groups as $group) {
            $groupsById[(string)$group['groupId']] = $group;
        }

        $seededGroups = [];
        foreach (self::CONTENT_TYPE_GROUP_IDS as $groupId) {
            if (isset($groupsById[$groupId])) {
                $seededGroups[] = $groupsById[$groupId];
            }
        }

        return $seededGroups;
    }

    /**
     * Mirrors the seeder: every chapter page gets a benefit-led intro
     * (desiderio_headersection) and a closing CTA where the group defines one.
     *
     * @param list<array{groupId: string|int}> $groups
     */
    private function countChapterFramingElements(array $groups): int
    {
        $count = 0;
        foreach ($groups as $group) {
            if (StyleguideContentGroups::chapterIntro((string)$group['groupId']) !== null) {
                $count++;
            }
            if (StyleguideContentGroups::chapterCta((string)$group['groupId']) !== null) {
                $count++;
            }
        }

        return $count;
    }

    private function countTopLevelShowcasePages(): int
    {
        return count(array_filter(
            StyleguideShowcasePages::subpages(),
            static fn (array $page): bool => ($page['parentSlug'] ?? null) === null
        ));
    }

    private function createCommandTester(): CommandTester
    {
        $command = $this->get(SeedStyleguidePagesCommand::class);
        self::assertInstanceOf(SeedStyleguidePagesCommand::class, $command);

        return new CommandTester($command);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchPageBySlug(string $slug): array
    {
        $row = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT * FROM pages WHERE deleted = 0 AND slug = ? LIMIT 1', [$slug])
            ->fetchAssociative();

        self::assertIsArray($row, 'Missing page with slug ' . $slug);

        return $row;
    }

    /**
     * @return list<string>
     */
    private function fetchChildSlugs(int $pageUid): array
    {
        $values = $this->getConnectionPool()
            ->getConnectionForTable('pages')
            ->executeQuery('SELECT slug FROM pages WHERE pid = ? AND deleted = 0 ORDER BY sorting', [$pageUid])
            ->fetchFirstColumn();

        $slugs = [];
        foreach ($values as $value) {
            if (is_string($value)) {
                $slugs[] = $value;
            }
        }

        return $slugs;
    }

    private function insertLegacyRootPage(string $slug, int $index): int
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        $connection->insert('pages', [
            'pid' => 1,
            'title' => 'Legacy ' . ltrim($slug, '/'),
            'slug' => $slug,
            'doktype' => 1,
            'deleted' => 0,
            'hidden' => 0,
            'sorting' => ($index + 1) * 16,
        ]);

        $uid = $connection->lastInsertId();
        self::assertTrue(is_numeric($uid));

        return (int)$uid;
    }

    /**
     * @param array<string, mixed> $row
     */
    private function intValue(array $row, string $key): int
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

    private function countRows(string $table, string $where = '1=1'): int
    {
        $count = $this->getConnectionPool()
            ->getConnectionForTable($table)
            ->executeQuery('SELECT COUNT(*) FROM ' . $table . ' WHERE ' . $where)
            ->fetchOne();

        return is_numeric($count) ? (int)$count : 0;
    }
}
