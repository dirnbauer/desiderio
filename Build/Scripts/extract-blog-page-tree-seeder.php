<?php

declare(strict_types=1);

$sourcePath = $argv[1] ?? __DIR__ . '/../../Classes/Command/SeedBlogPagesCommand.php';
$targetPath = __DIR__ . '/../../Classes/Seeding/BlogPageTreeSeeder.php';
$lines = file($sourcePath);
if ($lines === false) {
    throw new RuntimeException('Could not read blog command file.');
}

$keepRanges = [
    [25, 112],
    [245, 1203],
];

$chunk = '';
foreach ($keepRanges as [$from, $to]) {
    $chunk .= implode('', array_slice($lines, $from - 1, $to - $from + 1));
}

$header = <<<'PHP'
<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class BlogPageTreeSeeder
{
    public const DEFAULT_BACKEND_LAYOUT = 'pagets__DesiderioBlog';

PHP;

$body = preg_replace('/^    private function /m', '    public function ', $chunk);
$constructor = <<<'PHP'

    public function __construct(
        private readonly ConnectionPool $connectionPool,
    ) {}

PHP;

$body = preg_replace('/(\n    public function findBlogSetups)/', $constructor . '$1', $body, 1);

file_put_contents($targetPath, $header . $body . "}\n");
echo 'Wrote ' . count(file($targetPath)) . " lines to BlogPageTreeSeeder.php\n";
