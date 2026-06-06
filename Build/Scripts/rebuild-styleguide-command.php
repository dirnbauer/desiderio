<?php

declare(strict_types=1);

$sourcePath = __DIR__ . '/../../Classes/Command/SeedStyleguidePagesCommand.php';
$targetPath = $sourcePath;
$lines = file($sourcePath);
if ($lines === false) {
    throw new RuntimeException('Could not read command file.');
}

$keepRanges = [
    [1, 36],
    [53, 66],
    [68, 249],
    [251, 382],
    [384, 404],
    [430, 450],
];

$chunk = [];
foreach ($keepRanges as [$from, $to]) {
    $chunk = array_merge($chunk, array_slice($lines, $from - 1, $to - $from + 1));
}

$header = <<<'PHP'
<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Command;

use Doctrine\DBAL\ParameterType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Webconsulting\Desiderio\Data\StyleguideContentGroups;
use Webconsulting\Desiderio\Seeding\CollectionCleanupService;
use Webconsulting\Desiderio\Seeding\CollectionRecordSeeder;
use Webconsulting\Desiderio\Seeding\ContentBlockCollectionMap;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\ExtensionFalSeeder;
use Webconsulting\Desiderio\Seeding\LiveWorkspaceQueryHelper;
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

    private ?LiveWorkspaceQueryHelper $liveWorkspaceQueryHelper = null;
    private ?ContentBlockCollectionMap $contentBlockCollectionMap = null;
    private ?ExtensionFalSeeder $styleguideFalSeeder = null;
    private ?CollectionRecordSeeder $collectionRecordSeeder = null;
    private ?CollectionCleanupService $collectionCleanupService = null;
    private ?StyleguideFixtureResolver $fixtureResolver = null;

    private readonly StyleguideCollectionAliasPolicy $collectionAliasPolicy;

PHP;

$servicesAndCleanup = <<<'PHP'

    private function getLiveWorkspaceQueryHelper(): LiveWorkspaceQueryHelper
    {
        return $this->liveWorkspaceQueryHelper ??= new LiveWorkspaceQueryHelper($this->databaseSchema);
    }

    private function getContentBlockCollectionMap(): ContentBlockCollectionMap
    {
        return $this->contentBlockCollectionMap ??= new ContentBlockCollectionMap();
    }

    private function getStyleguideFalSeeder(): ExtensionFalSeeder
    {
        return $this->styleguideFalSeeder ??= new ExtensionFalSeeder(
            $this->connectionPool,
            $this->storageRepository,
            $this->databaseSchema,
            self::STYLEGUIDE_FAL_FOLDER,
            1777100143,
        );
    }

    private function getCollectionRecordSeeder(): CollectionRecordSeeder
    {
        return $this->collectionRecordSeeder ??= new CollectionRecordSeeder(
            $this->connectionPool,
            $this->databaseSchema,
            $this->getStyleguideFalSeeder(),
        );
    }

    private function getCollectionCleanupService(): CollectionCleanupService
    {
        return $this->collectionCleanupService ??= new CollectionCleanupService(
            $this->connectionPool,
            $this->databaseSchema,
            $this->getLiveWorkspaceQueryHelper(),
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

    private function deleteCollectionRowsForPage(int $pageUid): void
    {
        $this->getCollectionCleanupService()->deleteCollectionRowsForPage(
            $pageUid,
            $this->getCollectionTableNames(),
        );
    }

    /**
     * @param list<int> $parentUids
     */
    private function deleteCollectionRowsForParentUids(array $parentUids, string $parentTable): void
    {
        $this->getCollectionCleanupService()->deleteCollectionRowsForParentUids(
            $parentUids,
            $parentTable,
            $this->getContentBlockCollectionMap()->getCollectionsByParentTable(),
        );
    }

    /**
     * @param list<int> $recordUids
     */
    private function deleteFileReferencesForRecords(string $table, array $recordUids): void
    {
        $this->getCollectionCleanupService()->deleteFileReferencesForRecords($table, $recordUids);
    }

    /**
     * Restrict destructive styleguide cleanup to live rows. TYPO3 stores
     * workspace versions in the same table, so queries with restrictions
     * removed must add the live workspace predicates explicitly.
     *
     * @return list<string>
     */
    private function buildLiveWorkspaceConstraints(QueryBuilder $queryBuilder, string $table): array
    {
        return $this->getLiveWorkspaceQueryHelper()->buildLiveWorkspaceConstraints($queryBuilder, $table);
    }

    /**
     * @param array<string, mixed> $fixture
     * @param array<string, true> $columns
     * @return array{row: array<string, mixed>, collections: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, fileReferences: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function buildContentInsert(
        int $pid,
        string $ctype,
        string $name,
        array $fixture,
        int $sorting,
        int $now,
        array $columns,
    ): array {
        return $this->getFixtureResolver()->buildContentInsert($pid, $ctype, $name, $fixture, $sorting, $now, $columns);
    }

    /**
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, 2: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function resolveFixtureFields(string $ctype, array $fixture, string $name = ''): array
    {
        return $this->getFixtureResolver()->resolveFixtureFields($ctype, $fixture, $name);
    }

    /**
     * @param array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $definition
     * @param array<string, mixed> $resolvedFields
     * @param array<string, array{table: string, column: string, items: list<array<string, mixed>>}> $collections
     * @param array<string, mixed> $fixture
     * @return array{0: array<string, mixed>, 1: array<string, array{table: string, column: string, items: list<array<string, mixed>>}>, 2: array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>>}
     */
    private function completeResolvedFixtureData(
        string $ctype,
        string $name,
        array $definition,
        array $resolvedFields,
        array $collections,
        array $fixture = [],
    ): array {
        return $this->getFixtureResolver()->completeResolvedFixtureData($ctype, $name, $definition, $resolvedFields, $collections, $fixture);
    }

    /**
     * @param array<string, array<string, mixed>> $fields
     */
    private function resolveScalarField(string $field, array $fields): ?string
    {
        return $this->getFixtureResolver()->resolveScalarField($field, $fields);
    }

    /**
     * @param array<int|string, mixed> $value
     * @param array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $definition
     */
    private function resolveCollectionField(string $field, array $value, array $definition): ?string
    {
        return $this->getFixtureResolver()->resolveCollectionField($field, $value, $definition);
    }

    /**
     * @param array<int|string, mixed> $items
     * @param array<string, mixed> $collection
     * @return list<array<string, mixed>>
     */
    private function normalizeCollectionItems(array $items, array $collection): array
    {
        return $this->getFixtureResolver()->normalizeCollectionItems($items, $collection);
    }

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    private function buildFileReferenceFixtures(string $field, array $fieldConfig, int $index): array
    {
        return $this->getFixtureResolver()->buildFileReferenceFixtures($field, $fieldConfig, $index);
    }

    /**
     * @return list<string>
     */
    private function getCollectionTableNames(): array
    {
        return $this->getContentBlockCollectionMap()->getCollectionTableNames();
    }

    /**
     * @param array<string, array{table: string, column?: string, items: list<array<string, mixed>>}> $collections
     */
    private function seedCollectionRecords(int $contentUid, int $pageUid, int $now, array $collections): void
    {
        $this->getCollectionRecordSeeder()->seed($contentUid, $pageUid, $now, $collections);
    }

    /**
     * @param array<string, list<array{file: string, title: string, alternative: string, description: string, source: string}>> $fileReferences
     */
    private function seedFileReferences(string $table, int $uid, int $pid, int $now, array $fileReferences): void
    {
        $this->getStyleguideFalSeeder()->seedFileReferences($table, $uid, $pid, $now, $fileReferences);
    }

}
PHP;

// Drop duplicate opening from kept chunk (lines 53-66 are constructor, 68+ is configure/execute)
$body = implode('', array_slice($chunk, 36)); // skip first 36 lines of chunk which duplicate header parts

// chunk starts with: lines 53-66 constructor, 68-249, 251-382, 384-404, 430-450
// Line 53 in original is `private readonly StyleguideCollectionAliasPolicy` - already in header
// Actually keepRanges [53,66] is constructor continuation - we have collectionAliasPolicy in header, need constructor

$constructorAndRest = implode('', array_merge(
    array_slice($lines, 54, 13), // constructor body from line 55-67
    array_slice($lines, 67, 183), // configure + execute + getPowermail + getIntegerInputOption (68-250)
    array_slice($lines, 250, 133), // findOrCreate through createStyleguidePage (251-382)
    array_slice($lines, 383, 22), // markExisting (384-404)
    array_slice($lines, 429, 21), // findExistingDesiderioContentUids (430-450)
));

file_put_contents($targetPath, $header . $constructorAndRest . $servicesAndCleanup);
echo 'Rebuilt command to ' . count(file($targetPath)) . " lines\n";
