<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\StarterContentBuilder;

/**
 * A showcase definition may put a section on another surface through the
 * shared Appearance fields (frame_class and the spacing), which no block
 * declares in its own config.yaml. Fields outside that short list and the
 * block's own fields are still dropped.
 */
final class StarterContentBuilderAppearanceTest extends TestCase
{
    protected function tearDown(): void
    {
        ContentBlockDefinitionRegistry::resetCache();
        parent::tearDown();
    }

    #[Test]
    public function sharedAppearanceFieldsSurviveAlthoughTheBlockDoesNotDeclareThem(): void
    {
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'desiderio_statscounter' => [
                'fields' => [
                    'header' => ['identifier' => 'header', 'type' => 'Textarea'],
                ],
                'collections' => [],
            ],
        ]);
        $builder = new StarterContentBuilder(new DatabaseSchemaHelper(self::createStub(ConnectionPool::class)));

        $insert = $builder->buildContentInsert(
            12,
            [
                'ctype' => 'desiderio_statscounter',
                'colPos' => 0,
                'fields' => [
                    'header' => 'The situation on one day',
                    'frame_class' => 'primary',
                    'space_before_class' => 'large',
                    'layout' => ['not', 'a', 'scalar'],
                    'not_a_column_of_this_block' => 'dropped',
                ],
            ],
            256,
            1_762_250_000,
            ['pid' => true, 'CType' => true, 'colPos' => true, 'header' => true, 'frame_class' => true, 'space_before_class' => true, 'layout' => true, 'not_a_column_of_this_block' => true],
        );

        self::assertSame('The situation on one day', $insert['row']['header']);
        self::assertSame('primary', $insert['row']['frame_class']);
        self::assertSame('large', $insert['row']['space_before_class']);
        self::assertArrayNotHasKey('layout', $insert['row'], 'an array is no value for an appearance field');
        self::assertArrayNotHasKey('not_a_column_of_this_block', $insert['row'], 'fields outside the block and the appearance list stay dropped');
    }

    #[Test]
    public function nativeScheduleFieldsSurviveWithoutAllowingOtherAccessOverrides(): void
    {
        ContentBlockDefinitionRegistry::setDefinitionsForTesting([
            'desiderio_contenthighlight' => ['fields' => [], 'collections' => []],
        ]);
        $builder = new StarterContentBuilder(new DatabaseSchemaHelper(self::createStub(ConnectionPool::class)));
        $insert = $builder->buildContentInsert(12, [
            'ctype' => 'desiderio_contenthighlight',
            'colPos' => 0,
            'fields' => ['starttime' => 0, 'endtime' => 1_798_758_000, 'fe_group' => '1'],
        ], 256, 1_762_250_000, ['starttime' => true, 'endtime' => true, 'fe_group' => true]);

        self::assertSame(['starttime' => 0, 'endtime' => 1_798_758_000], $insert['row']);

        foreach ([-1, '2026-12-31', [1_798_758_000]] as $invalid) {
            $insert = $builder->buildContentInsert(12, [
                'ctype' => 'desiderio_contenthighlight',
                'colPos' => 0,
                'fields' => ['endtime' => $invalid],
            ], 256, 1_762_250_000, ['endtime' => true]);
            self::assertArrayNotHasKey('endtime', $insert['row']);
        }
    }
}
