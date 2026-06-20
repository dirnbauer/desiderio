<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Library;

use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Library\ElementCatalog;

/**
 * The classic TYPO3 core content elements must appear in Desiderio's element
 * catalog (picker + seeders) with a resolved icon, a localized title and
 * description, and search keywords — exactly like the Content Blocks.
 */
final class ElementCatalogCoreElementsTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = [
        'form',
        'workspaces',
    ];

    protected array $testExtensionsToLoad = [
        'webconsulting/desiderio',
    ];

    public function testPickerMetadataIncludesCoreElements(): void
    {
        $catalog = $this->get(ElementCatalog::class);
        self::assertInstanceOf(ElementCatalog::class, $catalog);

        $byCType = [];
        foreach ($catalog->getElementMetadata() as $entry) {
            $byCType[$entry['cType']] = $entry;
        }

        foreach (['bullets', 'table', 'uploads', 'menu_pages', 'div', 'html', 'textmedia'] as $cType) {
            self::assertArrayHasKey($cType, $byCType, $cType . ' must appear in the picker catalog');
            self::assertSame('core', $byCType[$cType]['hostExtension'], $cType . ' must be flagged as a core element');
            self::assertNotSame('', $byCType[$cType]['iconUrl'], $cType . ' must resolve a custom icon URL');
            self::assertStringContainsString('core-', $byCType[$cType]['iconUrl'], $cType . ' must use a desiderio core icon');
        }

        // The custom Content Block "textmedia" (desiderio_textmedia) and the
        // native core "textmedia" coexist as distinct catalog entries.
        self::assertArrayHasKey('desiderio_textmedia', $byCType);
    }

    public function testSeederCatalogCarriesNativeFixtures(): void
    {
        $catalog = $this->get(ElementCatalog::class);
        self::assertInstanceOf(ElementCatalog::class, $catalog);

        $byCType = [];
        foreach ($catalog->getElements() as $entry) {
            $byCType[$entry['cType']] = $entry;
        }

        self::assertArrayHasKey('bullets', $byCType);
        self::assertSame([], $byCType['bullets']['config'], 'Core elements carry no Content Block config');
        self::assertArrayHasKey('bodytext', $byCType['bullets']['fixture']);
        self::assertArrayHasKey('image', $byCType['textpic']['fixture'], 'textpic preview must seed a native image');
    }

    public function testWizardTcaCarriesCustomIconsAfterTcaCompilation(): void
    {
        // The AfterTcaCompilationEvent listener must have rewritten the CType
        // items + typeicon_classes in the fully-booted $GLOBALS['TCA'].
        $config = $this->readCTypeWizardConfig();

        foreach (['bullets', 'table', 'uploads', 'menu_pages', 'div', 'html', 'form_formframework'] as $cType) {
            self::assertArrayHasKey($cType, $config['icons'], $cType . ' must be a wizard item');
            self::assertStringStartsWith('desiderio-ce-', $config['icons'][$cType], $cType . ' wizard icon must be the custom one');
            self::assertSame(
                $config['icons'][$cType],
                $config['typeicons'][$cType] ?? '',
                $cType . ' page-module icon must match the wizard icon'
            );
        }

        self::assertStringContainsString(
            'library_core.xlf:bullets.description',
            $config['descriptions']['bullets'] ?? ''
        );
    }

    /**
     * Reads the compiled tt_content CType wizard config out of $GLOBALS['TCA']
     * as typed string maps, narrowing the untyped TCA array defensively.
     *
     * @return array{icons: array<string, string>, descriptions: array<string, string>, typeicons: array<string, string>}
     */
    private function readCTypeWizardConfig(): array
    {
        $icons = [];
        $descriptions = [];
        $typeicons = [];

        $tca = $GLOBALS['TCA'] ?? null;
        $ttContent = is_array($tca) ? ($tca['tt_content'] ?? null) : null;
        $columns = is_array($ttContent) ? ($ttContent['columns'] ?? null) : null;
        $cTypeField = is_array($columns) ? ($columns['CType'] ?? null) : null;
        $config = is_array($cTypeField) ? ($cTypeField['config'] ?? null) : null;
        $items = is_array($config) ? ($config['items'] ?? null) : null;

        if (is_array($items)) {
            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $value = $item['value'] ?? null;
                if (!is_string($value)) {
                    continue;
                }
                $icon = $item['icon'] ?? null;
                if (is_string($icon)) {
                    $icons[$value] = $icon;
                }
                $description = $item['description'] ?? null;
                if (is_string($description)) {
                    $descriptions[$value] = $description;
                }
            }
        }

        $ctrl = is_array($ttContent) ? ($ttContent['ctrl'] ?? null) : null;
        $typeiconClasses = is_array($ctrl) ? ($ctrl['typeicon_classes'] ?? null) : null;
        if (is_array($typeiconClasses)) {
            foreach ($typeiconClasses as $key => $icon) {
                if (is_string($key) && is_string($icon)) {
                    $typeicons[$key] = $icon;
                }
            }
        }

        return ['icons' => $icons, 'descriptions' => $descriptions, 'typeicons' => $typeicons];
    }

    public function testCoreElementsLocalizeTitleDescriptionAndKeywords(): void
    {
        $catalog = $this->get(ElementCatalog::class);
        self::assertInstanceOf(ElementCatalog::class, $catalog);
        $language = $this->get(LanguageServiceFactory::class)->create('default');

        $element = [
            'cType' => 'bullets',
            'name' => 'Bullet List',
            'hostExtension' => 'core',
            'title' => 'Bullet List',
            'description' => '',
        ];

        $localized = $catalog->localizeElement($element, $language);
        self::assertSame('Bullet List', $localized['title']);
        self::assertStringContainsString('bullet', strtolower($localized['description']));

        $keywords = $catalog->localizeKeywords($element, $language);
        self::assertNotEmpty($keywords['keywords'], 'Core elements must expose primary keywords');
        self::assertNotEmpty($keywords['synonyms'], 'Core elements must expose synonyms');
        self::assertContains('bullet list', array_map('strtolower', $keywords['keywords']));
    }
}
