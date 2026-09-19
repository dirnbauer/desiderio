<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Seeding\CollectionSchema;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\FixtureFieldNormalizer;
use Webconsulting\Desiderio\Seeding\FixtureLinkSlots;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;

/**
 * The two collaborators the styleguide fixture resolver delegates collection
 * shape and link-slot filling to.
 *
 * Both used to live inside the resolver, where only an end-to-end seed run
 * exercised them; the schema stub here keeps the database out of it.
 */
final class SeedingCollectionSchemaTest extends TestCase
{
    private function schema(): CollectionSchema
    {
        // No database in a unit test: every tableHasColumn() probe answers "no",
        // so the declared Content Block fields alone decide.
        return new CollectionSchema(new DatabaseSchemaHelper(self::createStub(ConnectionPool::class)));
    }

    public function testTableFallsBackToAnEmptyStringWhenTheCollectionNamesNone(): void
    {
        $schema = $this->schema();

        self::assertSame('tx_desiderio_item', $schema->table(['table' => 'tx_desiderio_item']));
        self::assertSame('', $schema->table([]));
        self::assertSame('', $schema->table(['table' => 42]));
    }

    public function testDeclaredFieldsAreFound(): void
    {
        $collection = ['table' => 'tx_desiderio_item', 'fields' => ['title' => ['type' => 'Text']]];
        $schema = $this->schema();

        self::assertTrue($schema->hasField($collection, 'title'));
        self::assertFalse($schema->hasField($collection, 'subtitle'));
    }

    public function testFieldConfigIsNormalisedAndNullWhenAbsent(): void
    {
        $collection = ['fields' => ['title' => ['type' => 'Text', 'identifier' => 'title'], 'broken' => 'not-an-array']];
        $schema = $this->schema();

        self::assertSame(['type' => 'Text', 'identifier' => 'title'], $schema->fieldConfig($collection, 'title'));
        self::assertNull($schema->fieldConfig($collection, 'broken'));
        self::assertNull($schema->fieldConfig($collection, 'missing'));
        self::assertNull($schema->fieldConfig(['fields' => 'nope'], 'title'));
    }

    private function linkSlots(): FixtureLinkSlots
    {
        return new FixtureLinkSlots($this->schema(), new FixtureFieldNormalizer(), new StyleguideDemoValueGenerator());
    }

    public function testLinkListIsSpreadOverTheDeclaredNumberedSlots(): void
    {
        $collection = ['fields' => [
            'link_1_label' => ['type' => 'Text'], 'link_1' => ['type' => 'Link'],
            'link_2_label' => ['type' => 'Text'], 'link_2' => ['type' => 'Link'],
        ]];

        $item = $this->linkSlots()->populate([], ['links' => ['Docs|/docs', 'Blog|/blog']], $collection);

        self::assertSame('Docs', $item['link_1_label']);
        self::assertSame('/docs', $item['link_1']);
        self::assertSame('Blog', $item['link_2_label']);
        self::assertSame('/blog', $item['link_2']);
    }

    public function testFillingStopsAtTheFirstSlotTheCollectionDoesNotDeclare(): void
    {
        $collection = ['fields' => ['link_1_label' => ['type' => 'Text'], 'link_1' => ['type' => 'Link']]];

        $item = $this->linkSlots()->populate([], ['links' => ['Docs|/docs', 'Blog|/blog']], $collection);

        self::assertSame(['link_1_label' => 'Docs', 'link_1' => '/docs'], $item);
    }

    public function testValuesAlreadyPresentAreNeverOverwritten(): void
    {
        $collection = ['fields' => ['link_1_label' => ['type' => 'Text'], 'link_1' => ['type' => 'Link']]];

        $item = $this->linkSlots()->populate(['link_1_label' => 'Kept'], ['links' => ['Docs|/docs']], $collection);

        self::assertSame('Kept', $item['link_1_label']);
        self::assertSame('/docs', $item['link_1']);
    }

    public function testALinkWithoutATargetGetsAGeneratedDemoUrl(): void
    {
        $collection = ['fields' => ['link_1_label' => ['type' => 'Text'], 'link_1' => ['type' => 'Link']]];

        $item = $this->linkSlots()->populate([], ['links' => ['Pricing']], $collection);

        self::assertSame('Pricing', $item['link_1_label']);
        self::assertIsString($item['link_1']);
        self::assertNotSame('', $item['link_1'], 'A dead anchor is never an acceptable demo value');
    }

    public function testChildrenFillTheChildSlotsAndAcceptMapsAndNewlineLists(): void
    {
        $collection = ['fields' => [
            'child_1_label' => ['type' => 'Text'], 'child_1_link' => ['type' => 'Link'],
            'child_2_label' => ['type' => 'Text'], 'child_2_link' => ['type' => 'Link'],
        ]];

        $item = $this->linkSlots()->populate([], ['children' => "Alpha|/a\nBeta|/b"], $collection);
        self::assertSame('Alpha', $item['child_1_label']);
        self::assertSame('/b', $item['child_2_link']);

        $mapped = $this->linkSlots()->populate([], ['children' => [['title' => 'Gamma', 'url' => '/g']]], $collection);
        self::assertSame('Gamma', $mapped['child_1_label']);
        self::assertSame('/g', $mapped['child_1_link']);
    }

    public function testAnItemWithoutLinksIsReturnedUntouched(): void
    {
        $collection = ['fields' => ['link_1_label' => ['type' => 'Text']]];

        self::assertSame(['a' => 1], $this->linkSlots()->populate(['a' => 1], [], $collection));
        self::assertSame(['a' => 1], $this->linkSlots()->populate(['a' => 1], ['links' => ''], $collection));
        self::assertSame(['a' => 1], $this->linkSlots()->populate(['a' => 1], ['links' => 42], $collection));
    }
}
