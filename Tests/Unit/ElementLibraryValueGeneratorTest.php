<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;
use Webconsulting\Desiderio\Seeding\ElementLibraryValueGenerator;
use Webconsulting\Desiderio\Seeding\StyleguideCollectionAliasPolicy;
use Webconsulting\Desiderio\Seeding\StyleguideDemoValueGenerator;
use Webconsulting\Desiderio\Seeding\StyleguideFixtureResolver;

final class ElementLibraryValueGeneratorTest extends TestCase
{
    /**
     * Words that would make a seeded library record read as a design-system
     * advert instead of a neutral example the editor can keep or edit.
     */
    private const PROMOTIONAL_NEEDLES = [
        'shadcn',
        'Desiderio',
        'styleguide',
        'Content Blocks',
        'TYPO3 Form Framework',
        'Fluid 5',
        'token-driven',
        'pattern',
    ];

    public function testBodyCopyIsNeutralMarketingNotComponentTalk(): void
    {
        $generator = new ElementLibraryValueGenerator();

        $copy = $generator->buildDefaultFieldValue(
            'desiderio_hero',
            'Hero',
            'subheadline',
            ['identifier' => 'subheadline', 'type' => 'Textarea'],
            0,
        );

        self::assertIsString($copy);
        self::assertNotSame('', trim($copy));
        // The styleguide generator appends "Built for the hero pattern." — the
        // library generator must not.
        self::assertStringNotContainsStringIgnoringCase('pattern', $copy);
        self::assertStringNotContainsStringIgnoringCase('shadcn', $copy);
    }

    public function testQuoteIsACustomerTestimonialNotADescriptionOfTheElement(): void
    {
        $generator = new ElementLibraryValueGenerator();

        $quote = $generator->buildDefaultQuote('Testimonial');

        self::assertStringNotContainsStringIgnoringCase('element', $quote);
        self::assertStringNotContainsStringIgnoringCase('pattern', $quote);
        self::assertStringNotContainsStringIgnoringCase('editors', $quote);
    }

    public function testHeadlineHasNoElementLabelPrefix(): void
    {
        $generator = new ElementLibraryValueGenerator();

        $header = $generator->buildDefaultFieldValue(
            'desiderio_pricingthreetier',
            'Three Tier Pricing',
            'header',
            ['identifier' => 'header', 'type' => 'Textarea'],
            0,
        );

        self::assertIsString($header);
        // The styleguide generator returns "Three Tier Pricing: <subject>" at
        // index 0; the library wants a clean headline.
        self::assertStringNotContainsString(':', $header);
        self::assertStringNotContainsStringIgnoringCase('pricing', $header);
    }

    public function testEyebrowUsesNeutralBadgeVocabulary(): void
    {
        $generator = new ElementLibraryValueGenerator();

        $eyebrow = $generator->buildDefaultFieldValue(
            'desiderio_hero',
            'Hero',
            'eyebrow',
            ['identifier' => 'eyebrow', 'type' => 'Textarea'],
            0,
        );

        self::assertContains(
            $eyebrow,
            ['New', 'Popular', 'Featured', 'Now available', 'Customer favorite', 'Limited offer'],
        );
    }

    public function testFieldHeuristicsAreInheritedFromTheStyleguideGenerator(): void
    {
        $generator = new ElementLibraryValueGenerator();

        // Count heuristic (number formatting) is shared behaviour and must keep
        // working through the subclass.
        self::assertSame(
            '128',
            $generator->buildDefaultFieldValue(
                'desiderio_categorycards',
                'Category Cards',
                'count',
                ['identifier' => 'count', 'type' => 'Textarea'],
                0,
            )
        );
    }

    public function testAWideSweepOfFieldsCarriesNoPromotionalCopy(): void
    {
        $generator = new ElementLibraryValueGenerator();
        $fields = [
            ['feature_list', 'Textarea'],
            ['description', 'Textarea'],
            ['text', 'Textarea'],
            ['cta_text', 'Textarea'],
            ['quote', 'Textarea'],
            ['tab_content', 'Textarea'],
            ['links', 'Textarea'],
            ['tier_values', 'Textarea'],
            ['row_data', 'Textarea'],
            ['topic', 'Textarea'],
            ['label', 'Textarea'],
            ['author_role', 'Textarea'],
        ];

        foreach ($fields as [$field, $type]) {
            for ($index = 0; $index < 4; $index++) {
                $value = $generator->buildDefaultFieldValue('desiderio_demo', 'Demo Element', $field, ['identifier' => $field, 'type' => $type], $index);
                if (!is_string($value)) {
                    continue;
                }
                foreach (self::PROMOTIONAL_NEEDLES as $needle) {
                    self::assertStringNotContainsStringIgnoringCase(
                        $needle,
                        $value,
                        sprintf('Field "%s" (index %d) leaked promotional copy: %s', $field, $index, $value)
                    );
                }
            }
        }
    }

    public function testFullElementIsSeededCompleteAndNeutralThroughTheResolver(): void
    {
        // Exercises the exact path the library seeder uses for desiderio
        // elements: an empty fixture run through the fixture resolver with the
        // neutral generator, which must fill every field and collection.
        $schema = new DatabaseSchemaHelper($this->createMock(ConnectionPool::class));
        $resolver = new StyleguideFixtureResolver(
            $schema,
            new ElementLibraryValueGenerator(),
            new StyleguideCollectionAliasPolicy($schema),
        );

        $definition = [
            'fields' => [
                'header' => ['identifier' => 'header', 'type' => 'Textarea'],
                'subheadline' => ['identifier' => 'subheadline', 'type' => 'Textarea'],
                'eyebrow' => ['identifier' => 'eyebrow', 'type' => 'Textarea'],
                'primary_button_text' => ['identifier' => 'primary_button_text', 'type' => 'Textarea'],
                'hero_image' => ['identifier' => 'hero_image', 'type' => 'File', 'maxitems' => 1],
            ],
            'collections' => [
                'items' => [
                    'table' => 'demo_items',
                    'minItems' => 2,
                    'maxItems' => 3,
                    'fields' => [
                        'title' => ['identifier' => 'title', 'type' => 'Textarea'],
                        'description' => ['identifier' => 'description', 'type' => 'Textarea'],
                    ],
                ],
            ],
        ];

        [$fields, $collections, $fileReferences] = $resolver->completeResolvedFixtureData(
            'desiderio_hero',
            'Hero',
            $definition,
            [],
            [],
            [],
        );

        // Every scalar field is populated.
        $header = $fields['header'] ?? null;
        self::assertIsString($header);
        self::assertNotSame('', trim($header));
        self::assertStringNotContainsString(':', $header);

        self::assertContains(
            $fields['eyebrow'] ?? null,
            ['New', 'Popular', 'Featured', 'Now available', 'Customer favorite', 'Limited offer'],
        );
        self::assertContains(
            $fields['primary_button_text'] ?? null,
            ['Learn more', 'Get started', 'Book a demo', 'See pricing', 'Contact sales'],
        );

        // The collection is filled to at least its minimum item count.
        $items = $collections['items']['items'] ?? null;
        self::assertIsArray($items);
        self::assertGreaterThanOrEqual(2, count($items));

        // The file field gets a demo image reference.
        self::assertArrayHasKey('hero_image', $fileReferences);
        self::assertNotEmpty($fileReferences['hero_image']);

        // Nothing in the whole record promotes the design system.
        $haystack = json_encode([$fields, $items], JSON_THROW_ON_ERROR);
        foreach (self::PROMOTIONAL_NEEDLES as $needle) {
            self::assertStringNotContainsStringIgnoringCase($needle, $haystack);
        }
    }

    public function testLibraryAndStyleguideGeneratorsDiverge(): void
    {
        $library = new ElementLibraryValueGenerator();
        $styleguide = new StyleguideDemoValueGenerator();

        $arguments = ['desiderio_hero', 'Hero', 'subheadline', ['identifier' => 'subheadline', 'type' => 'Textarea'], 0];

        self::assertNotSame(
            $styleguide->buildDefaultFieldValue(...$arguments),
            $library->buildDefaultFieldValue(...$arguments),
            'The library generator must produce different copy than the styleguide generator.'
        );
    }
}
