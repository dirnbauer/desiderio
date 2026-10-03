<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Command\NewsDemoSeeder;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseBlocks;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeatureDefinitions;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseFeaturePages;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;

/**
 * A page that presents other people's extension thanks them: one thank-you
 * per page, below the main content and never first. The feature pages take
 * it from the credits of their definition, the /news page from the news
 * seeder. Nobody thanks webconsulting, which built the rest.
 *
 * @phpstan-import-type ShowcaseCredits from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseCreditsTest extends AbstractStyleguideSeedingTestCase
{
    /**
     * Every feature definition with credits, and the page built from it.
     *
     * @return iterable<string, array{ShowcaseCredits, ShowcasePage|null}>
     */
    public static function creditedFeaturePages(): iterable
    {
        $pages = [];
        foreach (ShowcaseFeaturePages::pages() as $page) {
            $pages[$page['slug']] = $page;
        }
        foreach (['features' => ShowcaseFeatureDefinitions::categories(), 'ai' => ShowcaseFeatureDefinitions::aiCategories()] as $section => $categories) {
            foreach ($categories as $category) {
                foreach ($category['features'] as $feature) {
                    $slug = '/' . $section . '/' . $feature['slug'];
                    if (isset($feature['credits'])) {
                        yield $slug => [$feature['credits'], $pages[$slug] ?? null];
                    }
                }
            }
        }
    }

    /**
     * @param ShowcaseCredits $credits
     * @param ShowcasePage|null $page
     */
    #[DataProvider('creditedFeaturePages')]
    public function testEveryCreditsEntryRendersOneThankYouBelowTheMainContent(array $credits, ?array $page): void
    {
        self::assertNotNull($page, 'Every credited feature has a page.');
        $content = array_values($page['content']);
        $positions = array_keys($content, ShowcaseBlocks::thankYou($credits), true);

        self::assertCount(1, $positions, $page['slug'] . ' thanks the people behind the extension once.');
        $position = $positions[0];
        self::assertGreaterThan(0, $position, $page['slug'] . ': the thank-you is never the first block.');
        self::assertSame(count($content) - 2, $position, $page['slug'] . ': the thank-you sits below the main content, right above the closing call to action.');
        self::assertSame('desiderio_ctabanner', $content[count($content) - 1]['ctype'] ?? null);
    }

    /**
     * @return iterable<string, array{ShowcaseCredits}>
     */
    public static function credits(): iterable
    {
        foreach (self::creditedFeaturePages() as $slug => [$credits]) {
            yield $slug => [$credits];
        }
    }

    /**
     * @param ShowcaseCredits $credits
     */
    #[DataProvider('credits')]
    public function testCreditsNameOtherPeopleAndLinkTheirProject(array $credits): void
    {
        foreach ($credits['names'] as $name) {
            self::assertDoesNotMatchRegularExpression('/webconsulting|dirnbauer/i', $name, 'We never thank ourselves.');
            self::assertStringNotContainsString("'", $name, 'Apostrophes are typographic.');
        }
        self::assertStringNotContainsString("'", $credits['project'], 'Apostrophes are typographic.');
        self::assertStringStartsWith('https://', $credits['link']);
        self::assertStringNotContainsString('github.com/dirnbauer/', $credits['link'], 'The link goes to the upstream project, not to a fork of ours.');
    }

    public function testTheThankYouNamesEveryoneAndLinksTheProject(): void
    {
        $block = ShowcaseBlocks::thankYou([
            'names' => ['Matthis Vogel', 'anders und sehr'],
            'project' => 'The Visual Editor',
            'link' => 'https://github.com/FriendsOfTYPO3/visual_editor',
        ]);

        self::assertSame('desiderio_contenthighlight', $block['ctype']);
        self::assertSame('Thank you, Matthis Vogel and anders und sehr', $block['fields']['header'] ?? null);
        self::assertSame(
            '<p><a href="https://github.com/FriendsOfTYPO3/visual_editor">The Visual Editor</a> is the work of Matthis Vogel, anders und sehr and its contributors. Thank you so much for building and maintaining it.</p>',
            $block['fields']['content'] ?? null
        );
        self::assertSame('', $block['fields']['link'] ?? null, 'The project link sits in the text; no second button competes with the call to action.');
    }

    public function testTheNewsPageThanksGeorgRingerBelowTheList(): void
    {
        $seeder = new NewsDemoSeeder(self::createStub(ConnectionPool::class), new DatabaseSchemaHelper(self::createStub(ConnectionPool::class)));
        $rows = $this->invokeMethod($seeder, 'listPageRows', [992, 991, 1_790_000_000]);
        self::assertIsArray($rows);

        $sortings = [];
        $thankYou = null;
        foreach ($rows as $row) {
            self::assertIsArray($row);
            self::assertSame(992, $row['pid'] ?? null, 'Every element sits on the /news page.');
            $ctype = $row['CType'] ?? null;
            $sorting = $row['sorting'] ?? null;
            self::assertIsString($ctype);
            self::assertIsInt($sorting);
            $sortings[$ctype] = $sorting;
            if (($row['header'] ?? null) === 'Thank you, Georg Ringer') {
                $thankYou = $row;
            }
        }

        self::assertIsArray($thankYou, 'The /news page thanks Georg Ringer.');
        self::assertSame('desiderio_contenthighlight', $thankYou['CType'] ?? null);
        self::assertSame(
            '<p><a href="https://github.com/georgringer/news">EXT:news</a> is the work of Georg Ringer and its contributors. Thank you so much for building and maintaining it.</p>',
            $thankYou['content'] ?? null
        );
        asort($sortings);
        self::assertSame(
            ['desiderio_headersection', 'news_pi1', 'desiderio_contenthighlight'],
            array_keys($sortings),
            'Top to bottom: the header, the news list, then the thank-you, never first.'
        );
    }

    /**
     * The news seeder writes the thank-you's fields as tt_content columns of
     * the same name, which holds while the element does not prefix them.
     */
    public function testTheThankYouFieldsAreColumnsOfTheirOwn(): void
    {
        $config = Yaml::parseFile(dirname(__DIR__, 2) . '/ContentBlocks/ContentElements/content-highlight/config.yaml');
        self::assertIsArray($config);
        self::assertSame('desiderio_contenthighlight', $config['typeName'] ?? null);
        self::assertFalse($config['prefixFields'] ?? null);

        $fields = [];
        $configFields = $config['fields'] ?? null;
        self::assertIsArray($configFields);
        foreach ($configFields as $field) {
            self::assertIsArray($field);
            $identifier = $field['identifier'] ?? null;
            self::assertIsString($identifier);
            $fields[$identifier] = $field;
        }
        $block = ShowcaseBlocks::thankYou(['names' => ['Georg Ringer'], 'project' => 'EXT:news', 'link' => 'https://github.com/georgringer/news']);
        foreach (array_keys($block['fields']) as $identifier) {
            self::assertArrayHasKey($identifier, $fields, 'The content highlight has a field ' . $identifier . '.');
            self::assertNotTrue($fields[$identifier]['prefixField'] ?? false, $identifier . ' is not prefixed.');
            self::assertArrayNotHasKey('storageIdentifier', $fields[$identifier], $identifier . ' keeps its column name.');
        }
    }
}
