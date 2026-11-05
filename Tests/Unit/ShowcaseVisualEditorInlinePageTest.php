<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Data\Showcase\ShowcasePricing;
use Webconsulting\Desiderio\Data\Showcase\ShowcaseVisualEditorInlinePage;
use Webconsulting\Desiderio\Data\StyleguideShowcasePages;

final class ShowcaseVisualEditorInlinePageTest extends TestCase
{
    public function testThePageIsRegisteredHiddenAndNotSearchable(): void
    {
        $page = ShowcaseVisualEditorInlinePage::page();
        self::assertContains($page, StyleguideShowcasePages::subpages());
        self::assertSame('content-types', $page['parentSlug']);
        self::assertTrue($page['hideInNav'] ?? false);
        self::assertTrue($page['noSearch'] ?? false);
        self::assertCount(23, $page['content']);
    }

    public function testDeclaredFieldsLimitsNestedCollectionsAndMedia(): void
    {
        $this->configureRegistry();
        foreach (ShowcaseVisualEditorInlinePage::content() as $block) {
            if ($block['ctype'] === 'text') {
                continue;
            }
            $definition = ContentBlockDefinitionRegistry::getDefinition($block['ctype']);
            self::assertNotNull($definition);
            $this->validateFields($block['fields'], $definition, $block['ctype']);
        }
        ContentBlockDefinitionRegistry::resetCache();
    }

    public function testTheMatrixRetainsBothSharedOwnersAndCurrentPrices(): void
    {
        $blocks = ShowcaseVisualEditorInlinePage::content();
        self::assertSame(['desiderio_faq', 'desiderio_pricingfaq'], [$blocks[3]['ctype'], $blocks[4]['ctype']]);
        self::assertSame(['desiderio_statsinline', 'desiderio_productspecs'], [$blocks[8]['ctype'], $blocks[9]['ctype']]);
        self::assertCount(3, $blocks[14]['fields']['rows']);
        self::assertCount(3, $blocks[14]['fields']['rows'][0]['cells']);
        self::assertCount(2, $blocks[15]['fields']['column_items']);
        self::assertCount(2, $blocks[15]['fields']['comparison_feature_items'][0]['tier_values']);
        self::assertSame(array_column(array_slice(ShowcasePricing::plans(), 0, 3), 'price'), array_column($blocks[16]['fields']['plans'], 'price'));
        self::assertCount(5, $blocks[22]['fields']['hours_items']);
    }

    private function configureRegistry(): void
    {
        $definitions = [];
        $paths = glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/config.yaml');
        self::assertIsArray($paths);
        foreach ($paths as $path) {
            $config = Yaml::parseFile($path);
            $ctype = $config['typeName'] ?? 'desiderio_' . str_replace('-', '', basename(dirname($path)));
            $definitions[$ctype] = ContentBlockDefinitionRegistry::buildDefinitionFromConfig($config);
        }
        ContentBlockDefinitionRegistry::setDefinitionsForTesting($definitions);
    }

    /**
     * @param array<string, mixed> $fields
     * @param array<string, mixed> $definition
     */
    private function validateFields(array $fields, array $definition, string $context): void
    {
        foreach ($fields as $key => $value) {
            self::assertTrue(isset($definition['fields'][$key]) || isset($definition['collections'][$key]), "$context.$key must be declared");
            if (isset($definition['collections'][$key])) {
                $collection = $definition['collections'][$key];
                self::assertIsArray($value);
                self::assertGreaterThanOrEqual($collection['minItems'], count($value), "$context.$key minimum");
                if ($collection['maxItems'] > 0) {
                    self::assertLessThanOrEqual($collection['maxItems'], count($value), "$context.$key maximum");
                }
                foreach ($value as $child) {
                    $this->validateFields($child, $collection, "$context.$key");
                }
            } elseif (is_array($value) && isset($value['file'])) {
                self::assertFileExists(__DIR__ . '/../../' . $value['file']);
            }
        }
    }
}
