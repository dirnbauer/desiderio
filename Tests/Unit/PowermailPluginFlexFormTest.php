<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Configuration\Tca\TcaMigration;
use TYPO3\CMS\Core\Configuration\Tca\TcaPreparation;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Command\PowermailDemoSeeder;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\LibraryElementUpserter;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;
use Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;

/**
 * Seeded Powermail plugins carry their FlexForm as the backend stores it. Written by hand, each
 * value sat on its field's line: TYPO3 read that the same way, but Powermail's form overview
 * matched the backend's layout as text and left "Used on Page" empty for every seeded form.
 */
final class PowermailPluginFlexFormTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // FlexFormTools writes line breaks as LF, which TYPO3's bootstrap defines; this suite has none.
        if (!defined('LF')) {
            define('LF', chr(10));
        }
    }

    public function testTheDemoPluginIsStoredAsTheBackendStoresIt(): void
    {
        $connectionPool = self::createStub(ConnectionPool::class);
        $seeder = new PowermailDemoSeeder($connectionPool, new DatabaseSchemaHelper($connectionPool), self::flexFormTools());

        $flexform = self::invoke($seeder, 'buildPowermailFlexform', [1250, 712, 794, true]);
        $sheets = self::assertStoredAsTheBackendStoresIt($flexform);

        self::assertSame('1250', $sheets['main']['lDEF']['settings.flexform.main.form']['vDEF'] ?? null);
        self::assertSame('712', $sheets['main']['lDEF']['settings.flexform.main.pid']['vDEF'] ?? null);
        self::assertSame('1', $sheets['main']['lDEF']['settings.flexform.main.moresteps']['vDEF'] ?? null);
        self::assertSame('{powermail_all}', $sheets['receiver']['lDEF']['settings.flexform.receiver.body']['vDEF'] ?? null);
        self::assertSame('794', $sheets['thx']['lDEF']['settings.flexform.thx.redirect']['vDEF'] ?? null);
    }

    public function testTheLibraryPreviewPluginIsStoredAsTheBackendStoresIt(): void
    {
        $connectionPool = self::createStub(ConnectionPool::class);
        $schema = new DatabaseSchemaHelper($connectionPool);
        $upserter = new LibraryElementUpserter(
            $connectionPool,
            self::createStub(StorageRepository::class),
            $schema,
            new StyleguideFixtureResolver($schema, new StyleguideDemoValueGenerator(), new StyleguideCollectionAliasPolicy($schema)),
            new CollectionCleanupService($connectionPool, $schema, new LiveWorkspaceQueryHelper($schema)),
            new ContentBlockCollectionMap(),
            self::flexFormTools(),
        );

        $sheets = self::assertStoredAsTheBackendStoresIt(self::invoke($upserter, 'powermailFlexform', [1254]));

        self::assertSame('1254', $sheets['main']['lDEF']['settings.flexform.main.form']['vDEF'] ?? null);
        self::assertSame('0', $sheets['main']['lDEF']['settings.flexform.main.moresteps']['vDEF'] ?? null);
    }

    /**
     * An editor opening the plugin and saving it unchanged would store exactly this.
     *
     * @return array<string, mixed> The sheets of the FlexForm
     */
    private static function assertStoredAsTheBackendStoresIt(string $flexform): array
    {
        $parsed = GeneralUtility::xml2arrayProcess($flexform);
        self::assertIsArray($parsed, 'The plugin FlexForm must be valid XML');
        self::assertSame(self::flexFormTools()->flexArray2Xml($parsed), $flexform);
        self::assertIsArray($parsed['data'] ?? null);

        return $parsed['data'];
    }

    private static function flexFormTools(): FlexFormTools
    {
        return new FlexFormTools(new NoopEventDispatcher(), new TcaMigration(), new TcaPreparation());
    }

    /**
     * @param list<mixed> $arguments
     */
    private static function invoke(object $object, string $method, array $arguments): string
    {
        $result = (new \ReflectionMethod($object, $method))->invokeArgs($object, $arguments);
        self::assertIsString($result);

        return $result;
    }
}
