<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Library;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Middleware\ElementLibraryMiddleware;

/**
 * Both picker endpoints of a site that offers ONLY its own elements in its
 * own words: elementLibrary.onlySeeded + elementLibrary.labels, resolved
 * through the real LanguageService (English and German backend users), the
 * real catalog and seeded tt_content records.
 */
final class ElementLibrarySiteScopeTest extends FunctionalTestCase
{
    private const int STORAGE_PID = 5;
    private const string LABELS = 'EXT:desiderio/Tests/Functional/Library/Fixtures/site-labels.xlf';

    protected array $coreExtensionsToLoad = [
        'fluid_styled_content',
        'form',
        'workspaces',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/desiderio',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $content = $this->get(ConnectionPool::class)->getConnectionForTable('tt_content');
        foreach (['desiderio_accordion' => 101, 'desiderio_alert' => 102, 'desiderio_quote' => 103, 'desiderio_retired' => 104] as $cType => $uid) {
            $content->insert('tt_content', ['uid' => $uid, 'pid' => self::STORAGE_PID, 'CType' => $cType, 'sorting' => $uid]);
        }
        // Hidden and other-folder records are not "seeded" for this site.
        $content->insert('tt_content', ['uid' => 105, 'pid' => self::STORAGE_PID, 'CType' => 'desiderio_hero', 'hidden' => 1]);
        $content->insert('tt_content', ['uid' => 106, 'pid' => 99, 'CType' => 'desiderio_stats']);

        $this->get(ConnectionPool::class)->getConnectionForTable('be_users')->insert('be_users', [
            'uid' => 1,
            'username' => 'library-editor',
            'admin' => 1,
        ]);
    }

    /**
     * @param array<string, mixed> $elementLibrary
     * @param array<string, string> $query
     * @return array<string, mixed>
     */
    private function call(array $elementLibrary, array $query, string $backendLanguage = ''): array
    {
        $this->get(ConnectionPool::class)->getConnectionForTable('be_users')->update('be_users', ['lang' => $backendLanguage], ['uid' => 1]);
        $this->setUpBackendUser(1);
        $token = $this->get(FormProtectionFactory::class)->createForType('backend')->generateToken('visual_editor', 'save');

        $site = new Site(
            'restaurant',
            1,
            ['base' => 'https://restaurant.example/', 'languages' => [['languageId' => 0, 'title' => 'English', 'locale' => 'en_US.UTF-8', 'base' => '/']]],
            SiteSettings::createFromSettingsTree(['elementLibrary' => ['storagePid' => self::STORAGE_PID] + $elementLibrary]),
        );
        $request = new ServerRequest('https://restaurant.example/', 'GET')
            ->withAttribute('site', $site)
            ->withQueryParams($query)
            ->withHeader('X-Request-Token', $token);

        $handler = new class implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
        $response = $this->get(ElementLibraryMiddleware::class)->process($request, $handler);
        self::assertSame(200, $response->getStatusCode(), (string)$response->getBody());

        $decoded = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @param array<string, mixed> $response
     * @return array<string, array<string, mixed>> cType => element
     */
    private function elementsByCType(array $response): array
    {
        self::assertIsArray($response['elements'] ?? null);
        $byCType = [];
        foreach ($response['elements'] as $element) {
            self::assertIsArray($element);
            self::assertIsString($element['cType'] ?? null);
            $byCType[$element['cType']] = $element;
        }
        return $byCType;
    }

    /**
     * @param array<string, mixed> $response
     * @return list<string>
     */
    private function matchedCTypes(array $response): array
    {
        self::assertIsArray($response['matches'] ?? null);
        $cTypes = [];
        foreach ($response['matches'] as $match) {
            self::assertIsArray($match);
            self::assertIsString($match['cType'] ?? null);
            $cTypes[] = $match['cType'];
        }
        return $cTypes;
    }

    public function testOnlySeededListsTheSitesElementsInItsOwnWords(): void
    {
        $response = $this->call(['onlySeeded' => true, 'labels' => self::LABELS], ['elementLibrary' => '1']);
        $elements = $this->elementsByCType($response);

        // Only the live, default-language records in the site's folder; a
        // record whose element left the catalog lists nothing.
        self::assertSame(['desiderio_alert', 'desiderio_quote', 'desiderio_accordion'], array_keys($elements));

        $accordion = $elements['desiderio_accordion'];
        self::assertSame('Tasting menu', $accordion['title']);
        self::assertSame('The courses of the evening, one panel per course.', $accordion['description']);
        self::assertSame('The **evening menu**, course by course.', $accordion['shortDescription']);
        self::assertSame(['tasting menu', 'courses', 'degustation'], $accordion['keywords']);
        self::assertSame(['wine pairing', "chef's table"], $accordion['synonyms']);
        self::assertSame('Restaurant', $accordion['group']);
        self::assertSame(101, $accordion['demoUid']);
        self::assertStringContainsString('elPreview=101', (string)$accordion['previewUrl']);

        // Only the title and group are the site's; the rest falls back.
        $alert = $elements['desiderio_alert'];
        self::assertSame('Kitchen notice', $alert['title']);
        self::assertSame('Restaurant', $alert['group']);
        self::assertStringContainsString('color-coded notice box', (string)$alert['description']);
        self::assertNotSame([], $alert['keywords']);

        // No units at all: the host's texts, untouched - its group by the
        // title the host registered for it, not by the key.
        self::assertSame('Quote', $elements['desiderio_quote']['title']);
        self::assertSame('Content & Editorial', $elements['desiderio_quote']['group']);

        // Sorted by the names the site shows, categories from its own groups.
        self::assertSame(['Content & Editorial', 'Restaurant'], $response['categories']);
    }

    public function testTheSitesLabelsFollowTheBackendUsersLanguage(): void
    {
        $elements = $this->elementsByCType($this->call(['onlySeeded' => true, 'labels' => self::LABELS], ['elementLibrary' => '1'], 'de'));

        // German from de.site-labels.xlf ...
        self::assertSame('Degustationsmenü', $elements['desiderio_accordion']['title']);
        self::assertSame('Die Gänge des Abends, ein Panel pro Gang.', $elements['desiderio_accordion']['description']);
        self::assertSame(['degustationsmenü', 'gänge'], $elements['desiderio_accordion']['keywords']);
        self::assertSame(['weinbegleitung'], $elements['desiderio_accordion']['synonyms']);
        // ... a unit the translation lacks keeps the site's source text, which
        // still beats the host's (the site renamed the element) ...
        self::assertSame('Kitchen notice', $elements['desiderio_alert']['title']);
        // ... and an element without units stays the host's German.
        self::assertSame('Zitat', $elements['desiderio_quote']['title']);
    }

    public function testTheSearchIsLimitedToTheSeededElementsAndUsesTheSitesWords(): void
    {
        $settings = ['onlySeeded' => true, 'labels' => self::LABELS];

        self::assertSame(['desiderio_accordion'], $this->matchedCTypes($this->call($settings, ['elementLibrarySearch' => 'tasting menu'])));
        self::assertContains('desiderio_accordion', $this->matchedCTypes($this->call($settings, ['elementLibrarySearch' => 'wine pairing'])));
        self::assertContains('desiderio_alert', $this->matchedCTypes($this->call($settings, ['elementLibrarySearch' => 'kitchen'])));
    }

    public function testTheSearchNeverOffersAnElementOutsideTheSeededSet(): void
    {
        // "hero" and "stats" are in the catalog, but not seeded live in this folder.
        $settings = ['onlySeeded' => true];

        foreach (['hero', 'stats', 'carousel'] as $query) {
            $matches = $this->matchedCTypes($this->call($settings, ['elementLibrarySearch' => $query]));
            self::assertSame([], array_diff($matches, ['desiderio_accordion', 'desiderio_alert', 'desiderio_quote']), $query);
        }
    }

    public function testDifferentLabelsFilesDoNotShareASearchIndex(): void
    {
        // Same language, same catalog: the site on the host texts is searched
        // first and caches its index; the relabelled site must not reuse it.
        self::assertNotContains('desiderio_accordion', $this->matchedCTypes($this->call(['onlySeeded' => true], ['elementLibrarySearch' => 'tasting'])));
        self::assertSame(['desiderio_accordion'], $this->matchedCTypes($this->call(['onlySeeded' => true, 'labels' => self::LABELS], ['elementLibrarySearch' => 'tasting'])));
    }

    public function testWithoutTheNewSettingsTheWholeCatalogKeepsTheHostTexts(): void
    {
        $response = $this->call([], ['elementLibrary' => '1']);
        $elements = $this->elementsByCType($response);

        self::assertGreaterThan(200, count($elements));
        self::assertSame('Accordion', $elements['desiderio_accordion']['title']);
        self::assertSame(101, $elements['desiderio_accordion']['demoUid']);
        self::assertSame(0, $elements['desiderio_hero']['demoUid']);
        self::assertNotContains('Restaurant', $response['categories']);
    }
}
