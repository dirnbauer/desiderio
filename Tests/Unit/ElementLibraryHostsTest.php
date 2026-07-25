<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Cache\CacheManager;
use Webconsulting\Desiderio\Library\ElementCatalog;
use Webconsulting\Desiderio\Library\ElementSearchService;
use Webconsulting\Desiderio\Middleware\ElementLibraryMiddleware;

/**
 * The element library serves more than one provider extension: Desiderio and
 * Innesto ship with it, further themes register themselves, and each site picks
 * which of them its picker offers. These tests pin the three seams that makes
 * possible - host registration, per-site host filtering and search scoping.
 */
final class ElementLibraryHostsTest extends TestCase
{
    private mixed $confVarsBackup = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->confVarsBackup = $GLOBALS['TYPO3_CONF_VARS'] ?? null;
    }

    protected function tearDown(): void
    {
        if ($this->confVarsBackup === null) {
            unset($GLOBALS['TYPO3_CONF_VARS']);
        } else {
            $GLOBALS['TYPO3_CONF_VARS'] = $this->confVarsBackup;
        }
        parent::tearDown();
    }

    /**
     * Resolve the catalog's host list with the given provider registration -
     * what a provider extension writes in its ext_localconf.php.
     *
     * @return list<string>
     */
    private function hostExtensionsWith(mixed $registration): array
    {
        $GLOBALS['TYPO3_CONF_VARS'] = $registration === null
            ? []
            : ['EXTENSIONS' => ['desiderio' => ['libraryHostExtensions' => $registration]]];

        $catalog = new ElementCatalog($this->createMock(CacheManager::class));
        $method = new \ReflectionMethod($catalog, 'getHostExtensions');

        /** @var list<string> $hosts */
        $hosts = $method->invoke($catalog);
        return $hosts;
    }

    public function testTheShippedHostsAreListedWhenNoProviderRegistered(): void
    {
        self::assertSame(['desiderio', 'innesto'], $this->hostExtensionsWith(null));
    }

    public function testAProviderExtensionRegistersItselfThroughExtensionConfiguration(): void
    {
        self::assertSame(
            ['desiderio', 'innesto', 'desiderio_grande'],
            $this->hostExtensionsWith(['desiderio_grande']),
        );
    }

    public function testRegisteredHostsAreDeduplicatedTrimmedAndTypeChecked(): void
    {
        self::assertSame(
            ['desiderio', 'innesto', 'desiderio_grande'],
            $this->hostExtensionsWith([' desiderio_grande ', 'desiderio_grande', 'desiderio', '', null, 42]),
        );
    }

    public function testAMalformedRegistrationIsIgnoredRatherThanFatal(): void
    {
        self::assertSame(['desiderio', 'innesto'], $this->hostExtensionsWith('desiderio_grande'));
    }

    public function testTheHostListIsPartOfTheCacheFingerprint(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Library/ElementCatalog.php');

        // Registering a provider adds elements without touching any existing
        // config.yaml mtime, so the host list has to key the cache itself.
        self::assertStringContainsString("\$parts = ['hosts:' . implode(',', \$hostExtensions)];", $source);
        self::assertStringContainsString("METADATA_CACHE_VERSION = 'metadata-v3'", $source);
    }

    /**
     * @param list<string> $allowedHosts
     * @param list<array{cType: string, hostExtension: string}> $catalog
     * @return list<string>
     */
    private function filterHosts(array $catalog, array $allowedHosts): array
    {
        $middleware = (new \ReflectionClass(ElementLibraryMiddleware::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($middleware, 'filterByHosts');

        /** @var list<array{cType: string}> $filtered */
        $filtered = $method->invoke($middleware, $catalog, $allowedHosts);
        return array_column($filtered, 'cType');
    }

    public function testASiteListsOnlyTheHostsItConfigured(): void
    {
        $catalog = [
            ['cType' => 'desiderio_quote', 'hostExtension' => 'desiderio'],
            ['cType' => 'desiderio_grande_quote', 'hostExtension' => 'desiderio_grande'],
            ['cType' => 'header', 'hostExtension' => 'core'],
        ];

        self::assertSame(
            ['desiderio_grande_quote', 'header'],
            $this->filterHosts($catalog, ['desiderio_grande', 'core']),
        );
    }

    public function testAnEmptyHostSettingKeepsEveryElement(): void
    {
        $catalog = [
            ['cType' => 'desiderio_quote', 'hostExtension' => 'desiderio'],
            ['cType' => 'desiderio_grande_quote', 'hostExtension' => 'desiderio_grande'],
        ];

        self::assertSame(
            ['desiderio_quote', 'desiderio_grande_quote'],
            $this->filterHosts($catalog, []),
        );
    }

    public function testTheSearchIndexIsNarrowedToTheAllowedElements(): void
    {
        $service = (new \ReflectionClass(ElementSearchService::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($service, 'restrictIndex');

        $index = [
            'elements' => [
                'desiderio_quote' => ['title' => 'Quote', 'tokens' => ['quote' => 10, 'pullquote' => 6]],
                'desiderio_grande_quote' => ['title' => 'Quote', 'tokens' => ['quote' => 10, 'blockquote' => 6]],
            ],
            'vocab' => ['quote' => 10, 'pullquote' => 6, 'blockquote' => 6],
        ];

        /** @var array{elements: array<string, mixed>, vocab: array<string, int>} $restricted */
        $restricted = $method->invoke($service, $index, ['desiderio_grande_quote']);

        self::assertSame(['desiderio_grande_quote'], array_keys($restricted['elements']));
        // "Did you mean" must never suggest a term that only exists in an
        // element this site cannot insert.
        self::assertArrayNotHasKey('pullquote', $restricted['vocab']);
        self::assertSame(['quote' => 10, 'blockquote' => 6], $restricted['vocab']);
    }

    public function testAnUnrestrictedSearchKeepsTheSharedIndexUntouched(): void
    {
        $service = (new \ReflectionClass(ElementSearchService::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod($service, 'restrictIndex');

        $index = [
            'elements' => ['desiderio_quote' => ['title' => 'Quote', 'tokens' => ['quote' => 10]]],
            'vocab' => ['quote' => 10],
        ];

        self::assertSame($index, $method->invoke($service, $index, null));
    }

    public function testTheSeedCommandCanScopeAFolderToOneHost(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/SeedElementLibraryCommand.php');

        self::assertStringContainsString("->addOption('hosts'", $source);
        // Scoping happens before the upsert, so removeObsolete() prunes the
        // records of hosts this folder no longer serves.
        self::assertStringContainsString('$element[\'hostExtension\']', $source);
    }

    public function testAProviderCanShipItsOwnLibraryAssets(): void
    {
        $falSeeder = (string)file_get_contents(__DIR__ . '/../../Classes/Seeding/ExtensionFalSeeder.php');

        // Bare paths stay relative to Desiderio (every fixture we ship), while a
        // provider addresses its own files with a full EXT: reference.
        self::assertStringContainsString("str_starts_with(\$relativeFilePath, 'EXT:')", $falSeeder);
        self::assertStringContainsString("'EXT:desiderio/' . \$relativeFilePath", $falSeeder);
    }

    public function testTheWizardIconResolvesThroughTheContentBlockVendorSegment(): void
    {
        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Library/ElementCatalog.php');

        // content-blocks publishes to Resources/Public/ContentBlocks/<vendor>/<name>/,
        // so the vendor-less path alone never found an icon.
        self::assertStringContainsString(
            "'EXT:' . \$hostExtension . '/Resources/Public/ContentBlocks/' . \$vendor . '/' . \$name . '/icon.svg'",
            $source,
        );
        self::assertStringContainsString('private function resolveVendor(', $source);
    }
}
