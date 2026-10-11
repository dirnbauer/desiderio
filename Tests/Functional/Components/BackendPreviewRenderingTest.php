<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Components;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Domain\RawRecord;
use TYPO3\CMS\Core\Domain\Record;
use TYPO3\CMS\Core\Domain\Record\ComputedProperties;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;

final class BackendPreviewRenderingTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content', 'form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'friendsoftypo3/visual-editor',
        'webconsulting/visual-editor-enhancements',
        'webconsulting/desiderio',
    ];

    public function testEveryPreviewRendersWithRecordCollectionsAndResolvedMetadata(): void
    {
        $configFiles = glob(__DIR__ . '/../../../ContentBlocks/ContentElements/*/config.yaml');
        self::assertIsArray($configFiles);
        self::assertGreaterThan(200, count($configFiles));
        foreach ($configFiles as $configFile) {
            $element = basename(dirname($configFile));
            $config = Yaml::parseFile($configFile);
            self::assertIsArray($config);
            /** @var array<string, mixed> $config */
            $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig($config);
            $properties = $this->fixtureProperties($definition);
            $properties['header'] = 'Preview ' . $element;

            $html = $this->renderPreview($element, $properties);

            self::assertStringContainsString('class="d-ce-preview"', $html, $element);
            if (isset($definition['fields']['header'])) {
                self::assertStringContainsString('Preview ' . $element, $html, $element);
            }
            self::assertStringContainsString('UID: 42', $html, $element);
            self::assertStringContainsString('Page: 7', $html, $element);
            self::assertStringContainsString('desiderio/' . $element, $html, $element);
            self::assertDoesNotMatchRegularExpression('/<(?:f|di|d):|\{(?:data|item|settings)\.|\{f:/', $html, $element);
            self::assertStringNotContainsString('>Array<', $html, $element);
        }
    }

    public function testFeatureGridShowsOneIconAndReadableTitlePerItem(): void
    {
        $html = $this->renderPreview('feature-grid-3', [
            'header' => 'Everything in one package',
            'items' => [
                $this->record(['icon' => 'book-open', 'title' => '244 content elements', 'description' => '<p>Ready to use <strong>content</strong>.</p>']),
                $this->record(['icon' => 'zap', 'title' => '62 Fluid components', 'description' => '<p>Build your own layouts.</p>']),
                $this->record(['icon' => 'sparkles', 'title' => '15 theme presets', 'description' => '<p>Switch the visual style.</p>']),
            ],
        ]);

        self::assertSame(3, substr_count($html, '<svg '));
        self::assertSame(3, substr_count($html, 'data-icon-library="lucide"'));
        self::assertStringContainsString('244 content elements', $html);
        self::assertStringContainsString('Ready to use content.', $html);
        self::assertStringNotContainsString('<strong>', $html);
        $metadataPosition = strpos($html, 'd-ce-preview__meta');
        $titlePosition = strpos($html, '244 content elements');
        self::assertIsInt($metadataPosition);
        self::assertIsInt($titlePosition);
        self::assertLessThan($metadataPosition, $titlePosition);
    }

    public function testPricingShowsPlanNamesPricesAndDescriptionsFromRecords(): void
    {
        $html = $this->renderPreview('pricing', [
            'header' => 'Choose your plan',
            'plans' => [
                $this->record([
                    'name' => 'Studio',
                    'price' => '49 EUR',
                    'billing_period' => 'per month',
                    'description' => '<p>For small teams.</p>',
                    'features' => [$this->record(['text' => 'All components'])],
                ]),
            ],
        ]);

        self::assertStringContainsString('Studio', $html);
        self::assertStringContainsString('49 EUR', $html);
        self::assertStringContainsString('per month', $html);
        self::assertStringContainsString('For small teams.', $html);
        self::assertStringNotContainsString('>Array<', $html);
    }

    public function testStatisticsKeepZeroValuesVisible(): void
    {
        $html = $this->renderPreview('feature-stats', [
            'items' => [
                $this->record(['label' => 'Zero as a number', 'value' => 0]),
                $this->record(['label' => 'Zero as text', 'value' => '0']),
                $this->record(['label' => 'Missing value', 'value' => '']),
            ],
        ]);

        $document = new \DOMDocument();
        self::assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $xpath = new \DOMXPath($document);
        self::assertSame(2.0, $xpath->evaluate('count(//span[contains(@class,"d-ce-preview__item-value")][text()="0"])'));
        self::assertSame(2.0, $xpath->evaluate('count(//span[contains(@class,"d-ce-preview__item-value")])'));
    }

    /**
     * @return iterable<string, array{string, string, array<string, string>, list<string>}>
     */
    public static function meaningfulCollectionFieldsProvider(): iterable
    {
        yield 'annual and monthly prices' => ['pricing-annual-monthly', 'plans', ['name' => 'Studio', 'monthly_price' => '49 EUR', 'annual_price' => '490 EUR'], ['Studio', '49 EUR', '490 EUR']];
        yield 'usage rate' => ['pricing-usage', 'items', ['title' => 'Storage', 'rate' => '0.25 EUR'], ['Storage', '0.25 EUR']];
        yield 'person and role' => ['board-members', 'members', ['name' => 'Alex Morgan', 'title' => 'Chairperson'], ['Alex Morgan', 'Chairperson']];
        yield 'comparison values' => ['feature-matrix', 'rows', ['label' => 'Storage', 'left_value' => '10 GB', 'right_value' => '100 GB'], ['Storage', '10 GB', '100 GB']];
        yield 'counter target' => ['counter', 'items', ['label' => 'Projects', 'target_value' => '73', 'prefix' => '+'], ['Projects', '73', '+']];
    }

    /**
     * @param array<string, string> $properties
     * @param list<string> $expectedValues
     */
    #[DataProvider('meaningfulCollectionFieldsProvider')]
    public function testCollectionSummaryKeepsItsMeaningfulContent(string $element, string $collection, array $properties, array $expectedValues): void
    {
        $html = $this->renderPreview($element, [$collection => [$this->record($properties)]]);

        $document = new \DOMDocument();
        self::assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $xpath = new \DOMXPath($document);
        $summary = $xpath->evaluate('string(//div[@class="d-ce-preview__item-content"])');
        self::assertIsString($summary);
        foreach ($expectedValues as $value) {
            self::assertStringContainsString($value, $summary);
        }
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function collectionLimitProvider(): iterable
    {
        foreach (['en' => 'First 4 shown', 'de' => 'Die ersten 4 Einträge'] as $locale => $label) {
            yield $locale . ' exactly four rows' => [$locale, $label, 4];
            yield $locale . ' more than four rows' => [$locale, $label, 6];
        }
    }

    #[DataProvider('collectionLimitProvider')]
    public function testCollectionPreviewKeepsTotalCountAndLocalizesItsLimit(string $locale, string $label, int $count): void
    {
        $items = [];
        for ($index = 1; $index <= $count; $index++) {
            $items[] = $this->record(['icon' => 'book-open', 'title' => 'Feature ' . $index]);
        }

        $html = $this->renderPreview('feature-grid-3', ['items' => $items], $locale);

        $document = new \DOMDocument();
        self::assertTrue($document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $xpath = new \DOMXPath($document);
        self::assertSame(4.0, $xpath->evaluate('count(//ul[@class="d-ce-preview__list"]/li)'));
        self::assertSame((string)$count, $xpath->evaluate('string(//span[@class="d-ce-preview__count"])'));
        self::assertSame(4, substr_count($html, '<svg '));
        self::assertStringContainsString('Feature 1', $html);
        self::assertStringContainsString('Feature 4', $html);
        self::assertStringNotContainsString('Feature 5', $html);
        self::assertStringNotContainsString('{count}', $html);
        if ($count > 4) {
            self::assertSame($label, $xpath->evaluate('string(//p[@class="d-ce-preview__more"])'));
        } else {
            self::assertSame(0.0, $xpath->evaluate('count(//p[@class="d-ce-preview__more"])'));
        }
    }

    /**
     * @param array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $definition
     * @return array<string, mixed>
     */
    private function fixtureProperties(array $definition): array
    {
        $properties = [];
        foreach ($definition['fields'] as $identifier => $field) {
            $properties[$identifier] = match ($field['type'] ?? 'Text') {
                'File', 'Folder', 'Category', 'Relation' => [],
                'Checkbox', 'Number', 'Select' => '3',
                'DateTime' => new \DateTimeImmutable('2026-10-11 12:00:00'),
                default => $identifier === 'icon' ? 'book-open' : 'Preview ' . $identifier,
            };
        }
        foreach ($definition['collections'] as $identifier => $collection) {
            /** @var array{fields: array<string, array<string, mixed>>, collections: array<string, array<string, mixed>>} $collection */
            $properties[$identifier] = [$this->record($this->fixtureProperties($collection))];
        }
        return $properties;
    }

    /**
     * Real Record objects deliberately have no string conversion: a preview
     * accidentally printing a relation itself must fail during rendering.
     *
     * @param array<string, mixed> $properties
     */
    private function record(array $properties): Record
    {
        return new Record(new RawRecord(42, 7, ['uid' => 42, 'pid' => 7], new ComputedProperties(), 'tt_content'), $properties);
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function renderPreview(string $element, array $properties, string $locale = 'en'): string
    {
        $backendUser = new BackendUserAuthentication();
        $backendUser->user = ['uid' => 1, 'lang' => $locale];
        $GLOBALS['BE_USER'] = $backendUser;
        $request = new ServerRequest('https://example.com/typo3/module/web/layout')
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_BE);
        $view = $this->get(ViewFactoryInterface::class)->create(new ViewFactoryData(
            templateRootPaths: ['EXT:desiderio/ContentBlocks/ContentElements/' . $element . '/templates/'],
            layoutRootPaths: ['EXT:content_blocks/Resources/Private/Layouts/Preview/Content/'],
            request: $request,
        ));
        $view->assignMultiple([
            'data' => $this->record($properties),
            'settings' => ['_content_block_name' => 'desiderio/' . $element],
        ]);
        return $view->render('backend-preview');
    }
}
