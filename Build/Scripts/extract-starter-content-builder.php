<?php

declare(strict_types=1);

$sourcePath = __DIR__ . '/../../Classes/Command/SeedStarterSitesCommand.php';
$targetPath = __DIR__ . '/../../Classes/Seeding/StarterContentBuilder.php';
$lines = file($sourcePath);
if ($lines === false) {
    throw new RuntimeException('Could not read starter command file.');
}

$methodRanges = [
    [381, 394],
    [822, 1219],
];

$methods = '';
foreach ($methodRanges as [$from, $to]) {
    $chunk = array_slice($lines, $from - 1, $to - $from + 1);
    $methods .= preg_replace('/^    private function /m', '    public function ', implode('', $chunk));
}

$header = <<<'PHP'
<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;

/**
 * @phpstan-import-type StarterBlock from StarterSiteDefinitions
 */
final class StarterContentBuilder
{
    public function __construct(
        private readonly DatabaseSchemaHelper $databaseSchema,
    ) {}

PHP;

file_put_contents($targetPath, $header . $methods . "}\n");
echo 'Wrote ' . count(file($targetPath)) . " lines to StarterContentBuilder.php\n";
