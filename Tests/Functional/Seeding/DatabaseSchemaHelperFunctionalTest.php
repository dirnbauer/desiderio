<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Seeding;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;

final class DatabaseSchemaHelperFunctionalTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
        'workspaces',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/desiderio',
    ];

    public function testPagesTableExposesCoreColumns(): void
    {
        $helper = $this->get(DatabaseSchemaHelper::class);

        self::assertTrue($helper->tableHasColumn('pages', 'uid'));
        self::assertTrue($helper->tableHasColumn('pages', 'pid'));
        self::assertTrue($helper->tableHasColumn('pages', 'backend_layout'));
    }

    public function testFilterRowKeepsOnlyExistingColumns(): void
    {
        $helper = $this->get(DatabaseSchemaHelper::class);
        $columns = $helper->getColumnNames('pages');

        $filtered = $helper->filterRow([
            'uid' => 1,
            'pid' => 0,
            'title' => 'Desiderio functional smoke',
            'non_existing_column' => 'ignored',
        ], $columns);

        self::assertSame(1, $filtered['uid']);
        self::assertSame(0, $filtered['pid']);
        self::assertSame('Desiderio functional smoke', $filtered['title']);
        self::assertArrayNotHasKey('non_existing_column', $filtered);
    }
}
