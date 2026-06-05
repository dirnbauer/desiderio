<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;

final class StyleguideCollectionAliasPolicyTest extends TestCase
{
    public function testResolveChildFieldMapsTitleToLabel(): void
    {
        $policy = new StyleguideCollectionAliasPolicy(
            new DatabaseSchemaHelper($this->createMock(ConnectionPool::class))
        );

        $collection = [
            'table' => 'demo_items',
            'fields' => [
                'label' => ['identifier' => 'label'],
            ],
        ];

        self::assertSame('label', $policy->resolveChildField('title', 'Example', $collection));
    }

    public function testNormalizeCollectionSourceItemsSplitsPipeSeparatedCells(): void
    {
        $policy = new StyleguideCollectionAliasPolicy(
            new DatabaseSchemaHelper($this->createMock(ConnectionPool::class))
        );

        self::assertSame(
            ['A', 'B', 'C'],
            $policy->normalizeCollectionSourceItems('A | B | C', 'cells')
        );
    }
}
