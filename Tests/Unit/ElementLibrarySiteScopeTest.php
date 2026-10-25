<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use Webconsulting\Desiderio\Library\ElementCatalog;
use Webconsulting\Desiderio\Library\ElementLabelOverrides;
use Webconsulting\Desiderio\Library\ElementLibraryScope;

/**
 * A site can offer ONLY its own elements (elementLibrary.onlySeeded), in its
 * own words (elementLibrary.labels). These tests pin the filter and the
 * override rules both picker endpoints share.
 */
final class ElementLibrarySiteScopeTest extends TestCase
{
    private const string LABELS = 'EXT:my_theme/Resources/Private/Language/Library/odera.xlf';

    /** @var list<string> */
    private array $temporaryDirectories = [];

    protected function tearDown(): void
    {
        foreach ($this->temporaryDirectories as $directory) {
            $files = glob($directory . '/*');
            foreach (is_array($files) ? $files : [] as $file) {
                @unlink($file);
            }
            @rmdir($directory);
        }
        parent::tearDown();
    }

    /**
     * @param array<string, mixed> $elementLibrarySettings
     */
    private function site(array $elementLibrarySettings): Site
    {
        return new Site(
            'test',
            1,
            ['base' => '/', 'languages' => [['languageId' => 0, 'title' => 'English', 'locale' => 'en_US.UTF-8', 'base' => '/']]],
            SiteSettings::createFromSettingsTree($elementLibrarySettings === [] ? [] : ['elementLibrary' => $elementLibrarySettings]),
        );
    }

    /**
     * @return list<array{cType: string, hostExtension: string, title: string}>
     */
    private function catalog(): array
    {
        return [
            ['cType' => 'my_theme_hero', 'hostExtension' => 'my_theme', 'title' => 'Hero'],
            ['cType' => 'my_theme_itemlist', 'hostExtension' => 'my_theme', 'title' => 'Item list'],
            ['cType' => 'my_theme_pricing', 'hostExtension' => 'my_theme', 'title' => 'Pricing'],
            ['cType' => 'desiderio_quote', 'hostExtension' => 'desiderio', 'title' => 'Quote'],
            ['cType' => 'header', 'hostExtension' => 'core', 'title' => 'Header'],
        ];
    }

    // ------------------------------------------------------- onlySeeded

    public function testOnlySeededListsOnlyElementsWithADemoRecord(): void
    {
        $scope = new ElementLibraryScope([], true);

        $listed = $scope->filter($this->catalog(), [
            'my_theme_itemlist' => 41,
            'header' => 42,
            // seeded records whose element left the catalog are ignored
            'my_theme_retired' => 43,
            // a demoUid of 0 is "not seeded"
            'my_theme_pricing' => 0,
        ]);

        self::assertSame(['my_theme_itemlist', 'header'], array_column($listed, 'cType'));
        // Entries pass through whole, in catalog order.
        self::assertSame('Item list', $listed[0]['title']);
    }

    public function testOnlySeededWithAnEmptyFolderListsNothing(): void
    {
        $scope = new ElementLibraryScope([], true);

        self::assertSame([], $scope->filter($this->catalog(), []));
        self::assertSame([], $scope->allowedCTypes($this->catalog(), []));
    }

    public function testOnlySeededAndHostsApplyTogether(): void
    {
        $scope = new ElementLibraryScope(['my_theme'], true);
        $seeded = ['my_theme_hero' => 7, 'desiderio_quote' => 8, 'header' => 9];

        self::assertSame(['my_theme_hero'], array_column($scope->filter($this->catalog(), $seeded), 'cType'));
        self::assertSame(['my_theme_hero'], $scope->allowedCTypes($this->catalog(), $seeded));
    }

    public function testTheSearchIsLimitedToTheSameSetAsTheList(): void
    {
        $scope = new ElementLibraryScope([], true);
        $seeded = ['my_theme_pricing' => 3, 'my_theme_hero' => 4];

        self::assertSame(
            array_column($scope->filter($this->catalog(), $seeded), 'cType'),
            $scope->allowedCTypes($this->catalog(), $seeded),
        );
        self::assertSame(['my_theme_hero', 'my_theme_pricing'], $scope->allowedCTypes($this->catalog(), $seeded));
    }

    public function testWithoutOnlySeededTheSeededRecordsDoNotMatter(): void
    {
        $scope = new ElementLibraryScope();

        self::assertFalse($scope->isRestricted());
        self::assertFalse($scope->needsSeededRecords());
        self::assertSame($this->catalog(), $scope->filter($this->catalog(), ['header' => 1]));
        // null = search the whole shared index, no narrowing
        self::assertNull($scope->allowedCTypes($this->catalog(), ['header' => 1]));
    }

    public function testHostsAloneStillRestrictWithoutSeededRecords(): void
    {
        $scope = new ElementLibraryScope([' My_Theme ', 'core', '', 'core']);

        self::assertSame(['my_theme', 'core'], $scope->hosts);
        self::assertTrue($scope->isRestricted());
        self::assertFalse($scope->needsSeededRecords());
        self::assertSame(
            ['my_theme_hero', 'my_theme_itemlist', 'my_theme_pricing', 'header'],
            $scope->allowedCTypes($this->catalog()),
        );
    }

    /**
     * @return iterable<string, array{array<string, mixed>, list<string>, bool}>
     */
    public static function scopeSettingsProvider(): iterable
    {
        yield 'no settings' => [[], [], false];
        yield 'defaults' => [['hosts' => '', 'onlySeeded' => false], [], false];
        yield 'bool true' => [['onlySeeded' => true], [], true];
        yield 'string "1" from the site module' => [['onlySeeded' => '1'], [], true];
        yield 'string "0"' => [['onlySeeded' => '0'], [], false];
        yield 'hosts list' => [['hosts' => 'my_theme, Core,'], ['my_theme', 'core'], false];
        yield 'hosts not a string, onlySeeded as int' => [['hosts' => 42, 'onlySeeded' => 1], [], true];
    }

    /**
     * @param array<string, mixed> $settings
     * @param list<string> $expectedHosts
     */
    #[DataProvider('scopeSettingsProvider')]
    public function testTheScopeIsReadFromTheSiteSettings(array $settings, array $expectedHosts, bool $expectedOnlySeeded): void
    {
        $scope = ElementLibraryScope::fromSite($this->site($settings));

        self::assertSame($expectedHosts, $scope->hosts);
        self::assertSame($expectedOnlySeeded, $scope->onlySeeded);
    }

    // ------------------------------------------------------- labels

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function labelsFileProvider(): iterable
    {
        yield 'EXT: reference' => [self::LABELS, self::LABELS];
        yield 'LLL: prefix' => ['LLL:' . self::LABELS, self::LABELS];
        yield 'LLL: prefix with trailing colon' => [' LLL:' . self::LABELS . ': ', self::LABELS];
        yield 'empty' => ['', ''];
        yield 'blank' => ['   ', ''];
        yield 'bare path' => ['Resources/Private/Language/Library/odera.xlf', ''];
        yield 'not an XLIFF file' => ['EXT:my_theme/Resources/Private/Language/odera.json', ''];
    }

    #[DataProvider('labelsFileProvider')]
    public function testTheLabelsFileIsNormalized(string $configured, string $expected): void
    {
        self::assertSame($expected, new ElementLabelOverrides($configured)->file);
        self::assertSame($expected === '', new ElementLabelOverrides($configured)->isEmpty());
    }

    public function testTheLabelsFileIsReadFromTheSiteSettings(): void
    {
        self::assertSame(self::LABELS, ElementLabelOverrides::fromSite($this->site(['labels' => self::LABELS]))->file);
        self::assertTrue(ElementLabelOverrides::fromSite($this->site([]))->isEmpty());
        self::assertTrue(ElementLabelOverrides::fromSite($this->site(['labels' => ['not', 'a', 'string']]))->isEmpty());
    }

    public function testUnitsAreKeyedByCTypeAndField(): void
    {
        $labels = new ElementLabelOverrides(self::LABELS);

        self::assertSame('LLL:' . self::LABELS . ':my_theme_itemlist.title', $labels->reference('my_theme_itemlist', 'title'));
        self::assertSame('', new ElementLabelOverrides()->reference('my_theme_itemlist', 'title'));
    }

    /**
     * @return array{title: string, description: string, short: string, keywords: list<string>, synonyms: list<string>, group: string}
     */
    private function hostTexts(): array
    {
        return [
            'title' => 'Item list',
            'description' => 'A list of items with prices.',
            'short' => 'A **list** of items.',
            'keywords' => ['items', 'list'],
            'synonyms' => ['menu'],
            'group' => 'content',
        ];
    }

    /**
     * A translator backed by a unit map, as LanguageService::sL() would
     * resolve the site's file: '' for a unit that does not exist.
     *
     * @param array<string, string> $units unit id => text
     * @param list<string> $asked receives every reference looked up
     * @return \Closure(string): string
     */
    private function translator(array $units, array &$asked = []): \Closure
    {
        return static function (string $reference) use ($units, &$asked): string {
            $asked[] = $reference;
            $prefix = 'LLL:' . self::LABELS . ':';
            if (!str_starts_with($reference, $prefix)) {
                return '';
            }
            return $units[substr($reference, strlen($prefix))] ?? '';
        };
    }

    public function testEveryExistingUnitReplacesTheHostText(): void
    {
        $texts = new ElementLabelOverrides(self::LABELS)->relabel('my_theme_itemlist', $this->hostTexts(), $this->translator([
            'my_theme_itemlist.title' => 'Tasting menu',
            'my_theme_itemlist.description' => 'Courses of the evening menu with wine pairings.',
            'my_theme_itemlist.short' => 'The **evening menu**, course by course.',
            'my_theme_itemlist.keywords' => 'menu | courses | tasting || degustation | wine pairing',
            'my_theme_itemlist.group' => 'Restaurant',
        ]));

        self::assertSame([
            'title' => 'Tasting menu',
            'description' => 'Courses of the evening menu with wine pairings.',
            'short' => 'The **evening menu**, course by course.',
            'keywords' => ['menu', 'courses', 'tasting'],
            'synonyms' => ['degustation', 'wine pairing'],
            'group' => 'Restaurant',
        ], $texts);
    }

    public function testMissingUnitsFallBackToTheHostText(): void
    {
        $texts = new ElementLabelOverrides(self::LABELS)->relabel('my_theme_itemlist', $this->hostTexts(), $this->translator([
            'my_theme_itemlist.title' => 'Tasting menu',
            // another element's unit never leaks into this one
            'my_theme_hero.description' => 'Not for the item list.',
        ]));

        self::assertSame(['title' => 'Tasting menu'] + $this->hostTexts(), $texts);
    }

    public function testABlankUnitCountsAsMissing(): void
    {
        $texts = new ElementLabelOverrides(self::LABELS)->relabel('my_theme_itemlist', $this->hostTexts(), $this->translator([
            'my_theme_itemlist.title' => "  \n ",
            'my_theme_itemlist.keywords' => ' ',
            'my_theme_itemlist.group' => '  Restaurant ',
        ]));

        self::assertSame('Item list', $texts['title']);
        self::assertSame(['items', 'list'], $texts['keywords']);
        self::assertSame(['menu'], $texts['synonyms']);
        self::assertSame('Restaurant', $texts['group']);
    }

    public function testAKeywordUnitReplacesKeywordsAndSynonymsTogether(): void
    {
        $texts = new ElementLabelOverrides(self::LABELS)->relabel('my_theme_itemlist', $this->hostTexts(), $this->translator([
            'my_theme_itemlist.keywords' => 'menu | courses',
        ]));

        // Without "||" the unit has no synonyms - the host's are not kept, the
        // unit is the site's whole vocabulary for the element.
        self::assertSame(['menu', 'courses'], $texts['keywords']);
        self::assertSame([], $texts['synonyms']);
    }

    public function testWithoutALabelsFileNothingIsLookedUp(): void
    {
        $asked = [];
        $texts = new ElementLabelOverrides()->relabel('my_theme_itemlist', $this->hostTexts(), $this->translator([], $asked));

        self::assertSame($this->hostTexts(), $texts);
        self::assertSame([], $asked);
    }

    public function testEachFieldIsLookedUpOnceInTheSitesFile(): void
    {
        $asked = [];
        new ElementLabelOverrides(self::LABELS)->relabel('my_theme_itemlist', $this->hostTexts(), $this->translator([], $asked));

        self::assertSame([
            'LLL:' . self::LABELS . ':my_theme_itemlist.title',
            'LLL:' . self::LABELS . ':my_theme_itemlist.description',
            'LLL:' . self::LABELS . ':my_theme_itemlist.short',
            'LLL:' . self::LABELS . ':my_theme_itemlist.keywords',
            'LLL:' . self::LABELS . ':my_theme_itemlist.group',
        ], $asked);
    }

    public function testTheCategoriesUseTheOverriddenGroups(): void
    {
        $labels = new ElementLabelOverrides(self::LABELS);
        $translate = $this->translator([
            'my_theme_itemlist.group' => 'Restaurant',
            'my_theme_hero.group' => 'Restaurant',
        ]);

        $elements = [];
        foreach (['my_theme_itemlist' => 'content', 'my_theme_hero' => 'hero', 'my_theme_pricing' => 'commerce'] as $cType => $group) {
            $elements[] = $labels->relabel($cType, ['group' => $group] + $this->hostTexts(), $translate);
        }

        $catalog = new \ReflectionClass(ElementCatalog::class)->newInstanceWithoutConstructor();
        self::assertSame(['Restaurant', 'commerce'], $catalog->getCategories($elements));
    }

    // ------------------------------------------------------- keyword encoding

    /**
     * @return iterable<string, array{string, list<string>, list<string>}>
     */
    public static function keywordUnitProvider(): iterable
    {
        yield 'primary and synonyms' => ['a | b || c | d', ['a', 'b'], ['c', 'd']];
        yield 'primary only' => ['a | b', ['a', 'b'], []];
        yield 'synonyms only' => [' || c', [], ['c']];
        yield 'stray separators and spaces' => [' a |  | b  || ', ['a', 'b'], []];
        yield 'one term' => ['tasting menu', ['tasting menu'], []];
    }

    /**
     * @param list<string> $keywords
     * @param list<string> $synonyms
     */
    #[DataProvider('keywordUnitProvider')]
    public function testTheKeywordEncodingIsTheLibraryKeywordsOne(string $raw, array $keywords, array $synonyms): void
    {
        self::assertSame(['keywords' => $keywords, 'synonyms' => $synonyms], ElementCatalog::parseKeywordUnit($raw));
    }

    // ------------------------------------------------------- search cache key

    private function temporaryXliff(string $name): string
    {
        $directory = sys_get_temp_dir() . '/desiderio-labels-' . bin2hex(random_bytes(4));
        mkdir($directory);
        $this->temporaryDirectories[] = $directory;
        $path = $directory . '/' . $name;
        file_put_contents($path, '<xliff/>');
        return $path;
    }

    public function testTheSearchCacheKeyDiffersPerLabelsFile(): void
    {
        $odera = $this->temporaryXliff('odera.xlf');
        $ombra = $this->temporaryXliff('ombra.xlf');

        self::assertNotSame(
            ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $odera, 'default'),
            ElementLabelOverrides::fingerprintFor('EXT:my_theme/ombra.xlf', $ombra, 'default'),
        );
        self::assertSame('', new ElementLabelOverrides()->fingerprint('default'), 'Sites without labels keep the shared index');
    }

    public function testEditingTheLabelsFileChangesTheSearchCacheKey(): void
    {
        $odera = $this->temporaryXliff('odera.xlf');
        touch($odera, 1_700_000_000);
        clearstatcache();
        $before = ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $odera, 'default');

        touch($odera, 1_700_000_100);
        clearstatcache();

        self::assertNotSame($before, ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $odera, 'default'));
    }

    public function testEditingTheTranslationChangesTheKeyOfThatLanguageOnly(): void
    {
        $odera = $this->temporaryXliff('odera.xlf');
        $german = dirname($odera) . '/de.odera.xlf';
        file_put_contents($german, '<xliff/>');
        touch($german, 1_700_000_000);
        clearstatcache();
        $germanBefore = ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $odera, 'de');
        $englishBefore = ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $odera, 'default');

        touch($german, 1_700_000_100);
        clearstatcache();

        self::assertNotSame($germanBefore, ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $odera, 'de'));
        self::assertSame($englishBefore, ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $odera, 'default'));
        // The language itself is part of the key, so German and English
        // indexes of one file never collide.
        self::assertNotSame($englishBefore, $germanBefore);
    }

    public function testAMissingLabelsFileStillYieldsAStableKey(): void
    {
        $missing = sys_get_temp_dir() . '/desiderio-labels-missing-' . bin2hex(random_bytes(4)) . '/odera.xlf';

        self::assertSame(
            ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $missing, 'de'),
            ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', $missing, 'de'),
        );
        self::assertSame(32, strlen(ElementLabelOverrides::fingerprintFor('EXT:my_theme/odera.xlf', '', 'de')));
    }

    public function testTheSearchIndexKeyCarriesTheLabelsFingerprint(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Library/ElementSearchService.php');

        // Sites on the host texts share the plain per-language index; a site
        // with its own labels gets a key of its own.
        self::assertStringContainsString("\$key .= '-labels-' . \$labels->fingerprint(\$langKey);", $source);
        self::assertStringContainsString('$this->elementCatalog->pickerTexts($element, $languageService, $labels)', $source);
    }
}
