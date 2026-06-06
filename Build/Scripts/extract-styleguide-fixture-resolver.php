<?php

declare(strict_types=1);

$sourcePath = __DIR__ . '/../../Classes/Command/SeedStyleguidePagesCommand.php';
$targetPath = __DIR__ . '/../../Classes/Seeding/StyleguideFixtureResolver.php';
$lines = file($sourcePath);
if ($lines === false) {
    throw new RuntimeException('Could not read source command file.');
}

$ranges = [
    [510, 1544],
    [1573, 1650],
    [1663, 1727],
];

$chunk = [];
foreach ($ranges as [$from, $to]) {
    $chunk = array_merge($chunk, array_slice($lines, $from - 1, $to - $from + 1));
}

$body = implode('', $chunk);
$body = str_replace('private function', 'public function', $body);

$header = <<<'PHP'
<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class StyleguideFixtureResolver
{
    public const FIELD_SKIP = '__skip__';

    public function __construct(
        private readonly DatabaseSchemaHelper $databaseSchema,
        private readonly StyleguideDemoValueGenerator $demoValueGenerator,
        private readonly StyleguideCollectionAliasPolicy $collectionAliasPolicy,
    ) {}

PHP;

file_put_contents($targetPath, $header . $body . "\n}\n");
echo 'Wrote ' . $targetPath . ' (' . count(file($targetPath)) . " lines)\n";
