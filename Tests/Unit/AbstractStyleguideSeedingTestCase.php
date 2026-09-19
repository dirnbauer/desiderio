<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;
use Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;

/**
 * Collaborators the styleguide seeding tests build the same way.
 *
 * None of them touches a database: DatabaseSchemaHelper gets a stubbed
 * connection pool, so `tableHasColumn()` answers "no" and only the Content
 * Block definitions decide what is seeded.
 */
abstract class AbstractStyleguideSeedingTestCase extends TestCase
{
    protected function tearDown(): void
    {
        ContentBlockDefinitionRegistry::resetCache();
        parent::tearDown();
    }

    protected function createDemoValueGenerator(): StyleguideDemoValueGenerator
    {
        return new StyleguideDemoValueGenerator();
    }

    protected function createFixtureResolver(): StyleguideFixtureResolver
    {
        $databaseSchema = new DatabaseSchemaHelper(self::createStub(ConnectionPool::class));

        return new StyleguideFixtureResolver(
            $databaseSchema,
            new StyleguideDemoValueGenerator(),
            new StyleguideCollectionAliasPolicy($databaseSchema),
        );
    }

    protected function createCommand(): SeedStyleguidePagesCommand
    {
        return new SeedStyleguidePagesCommand(
            self::createStub(ConnectionPool::class),
            self::createStub(Context::class),
            self::createStub(StorageRepository::class),
            new DatabaseSchemaHelper(self::createStub(ConnectionPool::class)),
        );
    }

    /**
     * Seeding logic lives in private methods by design; the tests drive them
     * directly rather than through a database round trip.
     *
     * @param list<mixed> $arguments
     */
    protected function invokeMethod(object $object, string $method, array $arguments): mixed
    {
        $reflection = new \ReflectionMethod($object, $method);

        return $reflection->invokeArgs($object, $arguments);
    }
}
