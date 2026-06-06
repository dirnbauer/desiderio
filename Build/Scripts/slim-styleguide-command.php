<?php

declare(strict_types=1);

$sourcePath = __DIR__ . '/../../Classes/Command/SeedStyleguidePagesCommand.php';
$targetPath = $sourcePath;
$lines = file($sourcePath);
if ($lines === false) {
    throw new RuntimeException('Could not read command file.');
}

$keepRanges = [
    [1, 504],
    [1546, 1552],
    [1655, 1658],
    [1729, 1729],
];

$chunk = [];
foreach ($keepRanges as [$from, $to]) {
    $chunk = array_merge($chunk, array_slice($lines, $from - 1, $to - $from + 1));
}

$insertAfter = <<<'PHP'

    private ?StyleguideFixtureResolver $fixtureResolver = null;

    private function getFixtureResolver(): StyleguideFixtureResolver
    {
        return $this->fixtureResolver ??= new StyleguideFixtureResolver(
            $this->databaseSchema,
            $this->demoValueGenerator,
            $this->collectionAliasPolicy,
        );
    }

PHP;

$body = implode('', $chunk);
$body = str_replace(
    "use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;\n",
    "use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;\nuse Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;\n",
    $body,
);
$body = str_replace(
    "    private const FIELD_SKIP = '__skip__';\n    private const STYLEGUIDE_FAL_FOLDER",
    "    private const STYLEGUIDE_FAL_FOLDER",
    $body,
);
$needle = '        return $this->collectionCleanupService ??= new CollectionCleanupService(
            $this->connectionPool,
            $this->databaseSchema,
            $this->getLiveWorkspaceQueryHelper(),
        );
    }

    private function getIntegerInputOption';
$replacement = '        return $this->collectionCleanupService ??= new CollectionCleanupService(
            $this->connectionPool,
            $this->databaseSchema,
            $this->getLiveWorkspaceQueryHelper(),
        );
    }
' . $insertAfter . '
    private function getIntegerInputOption';
$body = str_replace($needle, $replacement, $body);

// Replace buildContentInsert usage in execute - already calls methods we need to add wrapper
$wrapper = <<<'PHP'

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

PHP;

$body = preg_replace(
    '/\n    \/\*\*[\s\S]*?private function getCollectionTableNames[\s\S]*?\n    \}\n/s',
    $wrapper,
    $body,
    1
) ?? $body;

file_put_contents($targetPath, $body);
echo 'Slimmed command to ' . count(file($targetPath)) . " lines\n";
