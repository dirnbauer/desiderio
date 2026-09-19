<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\DataHandling\IconItemsProcessor;
use Webconsulting\Desiderio\Icon\IconRegistry;

/**
 * What every shipped Content Block must declare: the files it consists of,
 * its vendor and CType, its editor-facing labels in both languages, its
 * wizard icon, its backend preview and the shape of its fields.
 */
final class ContentBlockStructureTest extends AbstractContentBlockTestCase
{
    private const int EXPECTED_COUNT = 244;

    public function testExpectedNumberOfContentBlocks(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        self::assertCount(self::EXPECTED_COUNT, $blocks, 'Content block count mismatch');
    }

    public function testEveryContentBlockHasRequiredFiles(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $name = basename($block);
            self::assertFileExists("{$block}/config.yaml", "Missing config.yaml in {$name}");
            self::assertFileExists("{$block}/templates/frontend.html", "Missing frontend.html in {$name}");
            self::assertFileExists("{$block}/templates/backend-preview.fluid.html", "Missing backend-preview.fluid.html in {$name}");
        }
    }

    public function testContentBlockDisplayNamesUseEditorFriendlyLabels(): void
    {
        $expectedTitles = [
            'cta' => 'Call to Action',
            'cta-with-image' => 'Image Call to Action',
            'hero-logo-cloud' => 'Logo Cloud Hero',
            'nav-toc' => 'Table of Contents Navigation',
            // Renamed from 'Text & Media': it duplicated the classic core
            // textmedia element's title in English and German.
            'textmedia' => 'Split Text & Media',
        ];

        foreach ($expectedTitles as $slug => $expectedTitle) {
            $config = Yaml::parseFile(self::CONTENT_BLOCKS_DIR . "/{$slug}/config.yaml");
            self::assertSame($expectedTitle, $config['title'] ?? null, "{$slug} should use the improved content element name");
        }
    }

    public function testQuoteVariantFieldUsesDedicatedBackendLabel(): void
    {
        $config = Yaml::parseFile(self::CONTENT_BLOCKS_DIR . '/quote/config.yaml');
        self::assertIsArray($config);

        $variantField = null;
        foreach ($config['fields'] ?? [] as $field) {
            if (is_array($field) && ($field['identifier'] ?? null) === 'variant') {
                $variantField = $field;
                break;
            }
        }

        self::assertIsArray($variantField);
        self::assertSame(
            'LLL:EXT:desiderio/ContentBlocks/ContentElements/quote/language/labels.xlf:field.variant',
            $variantField['label'] ?? null
        );
        self::assertTrue(
            $variantField['prefixField'] ?? false,
            'Quote must use an isolated variant column so shared tt_content.variant items cannot leak into the backend dropdown.'
        );

        $englishLabels = (string)file_get_contents(self::CONTENT_BLOCKS_DIR . '/quote/language/labels.xlf');
        $germanLabels = (string)file_get_contents(self::CONTENT_BLOCKS_DIR . '/quote/language/de.labels.xlf');
        self::assertStringContainsString('<unit id="field.variant">', $englishLabels);
        self::assertStringContainsString('<source>Variant</source>', $englishLabels);
        self::assertStringContainsString('<unit id="field.variant">', $germanLabels);
        self::assertStringContainsString('<target>Variante</target>', $germanLabels);
    }

    public function testEveryContentBlockUsesDesiderioVendor(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $name = basename($block);
            $config = Yaml::parseFile("{$block}/config.yaml");
            self::assertArrayHasKey('name', $config, "{$name} missing 'name'");
            self::assertStringStartsWith('desiderio/', (string)$config['name'], "{$name} must use desiderio/ vendor prefix");
            self::assertArrayHasKey('typeName', $config, "{$name} missing 'typeName'");
            self::assertStringStartsWith('desiderio_', (string)$config['typeName'], "{$name} typeName must start with desiderio_");
        }
    }

    /**
     * Elements rendered through the Section component carry
     * Desiderio/Appearance (frame + spacing, all consumed by the template —
     * the audit's appearance_field_unwired enforces that). The 17 utility
     * elements (footers, floating banners, dividers) render no section, so a
     * surface or section-margin control would be a dead knob for them; they
     * deliberately carry NO appearance basic. Core's TYPO3/Appearance is
     * banned outright: its four controls were consumed by nothing, which is
     * exactly the situation this arrangement replaced.
     */
    public function testEveryContentBlockDeclaresSharedTypo3Basics(): void
    {
        $noSection = [
            'back-to-top', 'content-divider', 'cookie-banner', 'cta-floating',
            'footer', 'footer-app-links', 'footer-brand', 'footer-columns',
            'footer-contact', 'footer-dark', 'footer-mega', 'footer-minimal',
            'footer-newsletter', 'footer-social', 'footer-split',
            'gdpr-banner', 'legal-links',
        ];

        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $name = basename($block);
            $config = Yaml::parseFile("{$block}/config.yaml");
            $basics = $config['basics'] ?? [];
            self::assertIsArray($basics, $name . ' basics must be a list');

            self::assertNotContains('TYPO3/Appearance', $basics, $name . ' must not use the core Appearance basic — its controls render nowhere; use Desiderio/Appearance');
            foreach (['TYPO3/Links', 'TYPO3/Categories'] as $basic) {
                self::assertContains($basic, $basics, $name . " must include {$basic}");
            }

            if (in_array($name, $noSection, true)) {
                self::assertNotContains('Desiderio/Appearance', $basics, $name . ' renders no section; an appearance tab would be dead controls');
            } else {
                self::assertContains('Desiderio/Appearance', $basics, $name . ' must include Desiderio/Appearance');
            }
        }
    }

    public function testTopLevelCollectionFieldsArePrefixed(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        foreach ($blocks as $block) {
            $config = Yaml::parseFile("{$block}/config.yaml");
            self::assertIsArray($config);
            $fields = $config['fields'] ?? [];
            self::assertIsArray($fields, basename($block) . ' fields must be a list');

            foreach ($fields as $field) {
                if (!is_array($field) || ($field['type'] ?? '') !== 'Collection') {
                    continue;
                }

                $identifier = $field['identifier'] ?? 'collection';
                $identifier = is_scalar($identifier) ? (string)$identifier : 'collection';
                self::assertTrue(
                    $field['prefixField'] ?? false,
                    basename($block) . '.' . $identifier . ' must enable prefixField so reused tt_content Collection identifiers do not share one TCA column.'
                );
            }
        }
    }

    public function testSharedSingleLineTextFieldsUseCompatibleTextareaSchema(): void
    {
        $expectedTextareaFields = [
            'badge_text' => true,
            'primary_button_text' => true,
            'series_name' => true,
        ];

        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        foreach ($blocks as $block) {
            $config = Yaml::parseFile("{$block}/config.yaml");
            self::assertIsArray($config);
            $fields = $config['fields'] ?? [];
            self::assertIsArray($fields, basename($block) . ' fields must be a list');

            foreach ($fields as $field) {
                if (!is_array($field)) {
                    continue;
                }

                $identifier = $field['identifier'] ?? null;
                if (!is_string($identifier) || !isset($expectedTextareaFields[$identifier])) {
                    continue;
                }

                self::assertSame('Textarea', $field['type'] ?? null, basename($block) . '.' . $identifier . ' must compile to the shared Textarea tt_content column type');
                self::assertSame(1, $field['rows'] ?? null, basename($block) . '.' . $identifier . ' should stay a single-line editor field');
            }
        }
    }

    public function testEveryContentBlockHasEnglishAndGermanWizardLabels(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $name = basename($block);
            $english = (string)file_get_contents("{$block}/language/labels.xlf");
            $german = (string)file_get_contents("{$block}/language/de.labels.xlf");

            self::assertStringContainsString('<xliff version="2.0"', $english, "{$name} must use TYPO3 XLIFF 2.0 for English labels");
            self::assertStringContainsString('srcLang="en"', $english, "{$name} must declare English as source language");
            self::assertMatchesRegularExpression('/<unit id="title">\\s*<segment>\\s*<source>[^<]+<\\/source>/s', $english, "{$name} needs an English title");
            self::assertMatchesRegularExpression('/<unit id="description">\\s*<segment>\\s*<source>[A-Z][^<]+\\.<\\/source>/s', $english, "{$name} needs a concise imperative English use-case description");
            self::assertDoesNotMatchRegularExpression('/<unit id="description">\\s*<segment>\\s*<source>Use when /s', $english, "{$name} English description must be imperative, not a 'Use when …' sentence");
            self::assertStringNotContainsString('A shadcn/ui styled TYPO3 content element', $english, "{$name} still uses the old generic English description");
            self::assertStringNotContainsString('Editors can manage', $english, "{$name} still describes editor fields instead of usage");

            self::assertStringContainsString('<xliff version="2.0"', $german, "{$name} must use TYPO3 XLIFF 2.0 for German labels");
            self::assertStringContainsString('trgLang="de"', $german, "{$name} must declare German as target language");
            self::assertMatchesRegularExpression('/<unit id="title">\\s*<segment state="final">\\s*<source>[^<]+<\\/source>\\s*<target>[^<]+<\\/target>/s', $german, "{$name} needs a German title target");
            self::assertMatchesRegularExpression('/<unit id="description">\\s*<segment state="final">\\s*<source>[A-Z][^<]+\\.<\\/source>\\s*<target>\\p{Lu}[^<]+\\.<\\/target>/su', $german, "{$name} needs a concise imperative German use-case description");
            self::assertStringNotContainsString('<target>Einsetzen, wenn ', $german, "{$name} German description must be imperative, not an 'Einsetzen, wenn …' sentence");
            self::assertStringNotContainsString('Redakteure pflegen', $german, "{$name} still describes editor fields instead of usage");
        }
    }

    public function testContentBlockTitlesAndDescriptionsAreUseful(): void
    {
        $titles = [];
        $descriptions = [];
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);

        foreach ($blocks as $block) {
            $name = basename($block);
            $config = Yaml::parseFile("{$block}/config.yaml");
            $title = (string)($config['title'] ?? '');
            $description = (string)($config['description'] ?? '');

            self::assertNotSame('', $title, "{$name} needs a title");
            self::assertNotSame('', $description, "{$name} needs a description");
            if (isset($titles[$title])) {
                self::fail("{$name} duplicates the title used by {$titles[$title]}");
            }
            self::assertStringNotContainsString('A shadcn/ui styled TYPO3 content element', $description, "{$name} still uses a generic description");
            self::assertMatchesRegularExpression('/^[A-Z].*\\.$/su', $description, "{$name} description should be a concise imperative phrase: capitalized and ending with a period");
            self::assertFalse(str_starts_with($description, 'Use when '), "{$name} description must be imperative, not a 'Use when …' sentence");
            self::assertStringNotContainsString('give one focused content task', $description, "{$name} still uses the generic fallback description");
            // Descriptions are written LLM-first (what source content maps to
            // the element, and when to prefer a sibling) so agents selecting
            // elements through the TYPO3 MCP can choose correctly; the core
            // elements in the same wizard carry equally long descriptions.
            self::assertGreaterThanOrEqual(100, strlen($description), "{$name} description should carry enough signal for element selection");
            self::assertLessThanOrEqual(650, strlen($description), "{$name} description should stay scannable");
            if (isset($descriptions[$description])) {
                self::fail("{$name} duplicates the description used by {$descriptions[$description]}");
            }

            $titles[$title] = $name;
            $descriptions[$description] = $name;
        }
    }

    public function testContentElementWizardGroupsUseLocalizedEditorNames(): void
    {
        $expectedGroups = [
            'content' => ['contentElementGroup.content', 'Content & Editorial', 'Inhalte & Redaktion'],
            'conversion' => ['contentElementGroup.conversion', 'Leads & Conversion', 'Leads & Conversion'],
            'data' => ['contentElementGroup.data', 'Data & Dashboards', 'Daten & Dashboards'],
            'features' => ['contentElementGroup.features', 'Features & Benefits', 'Funktionen & Vorteile'],
            'footer' => ['contentElementGroup.footer', 'Footers & Utility Areas', 'Footer & Servicebereiche'],
            'hero' => ['contentElementGroup.hero', 'Hero & Landing Intros', 'Hero & Seiteneinstiege'],
            'navigation' => ['contentElementGroup.navigation', 'Navigation & Wayfinding', 'Navigation & Orientierung'],
            'pricing' => ['contentElementGroup.pricing', 'Plans & Pricing', 'Tarife & Preise'],
            'social-proof' => ['contentElementGroup.socialProof', 'Trust & Social Proof', 'Vertrauen & Referenzen'],
            'team' => ['contentElementGroup.team', 'People & Team', 'Menschen & Team'],
        ];

        $tcaOverride = (string)file_get_contents(__DIR__ . '/../../Configuration/TCA/Overrides/tt_content.php');
        $englishLabels = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Language/labels.xlf');
        $germanLabels = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Language/de.labels.xlf');
        $styleguideGroups = json_decode((string)file_get_contents(__DIR__ . '/../../Resources/Private/Data/styleguide-content-groups.json'), true, 512, JSON_THROW_ON_ERROR);
        $englishXliff = simplexml_load_string($englishLabels);
        $germanXliff = simplexml_load_string($germanLabels);
        self::assertInstanceOf(\SimpleXMLElement::class, $englishXliff, 'labels.xlf must be valid XML');
        self::assertInstanceOf(\SimpleXMLElement::class, $germanXliff, 'de.labels.xlf must be valid XML');

        self::assertStringContainsString('addTcaSelectItemGroup', $tcaOverride);
        self::assertSame('2.0', (string)$englishXliff['version']);
        self::assertSame('2.0', (string)$germanXliff['version']);

        $styleguideTitles = [];
        foreach ($styleguideGroups as $group) {
            $styleguideTitles[$group['groupId']] = $group['groupTitle'];
        }

        foreach ($expectedGroups as $group => [$labelId, $englishTitle, $germanTitle]) {
            self::assertStringContainsString("'{$group}' => 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:{$labelId}'", $tcaOverride);
            self::assertStringContainsString('<unit id="' . $labelId . '">', $englishLabels);
            self::assertStringContainsString('<source>' . htmlspecialchars($englishTitle, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</source>', $englishLabels);
            self::assertStringContainsString('<target>' . htmlspecialchars($germanTitle, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</target>', $germanLabels);
            self::assertSame($englishTitle, $styleguideTitles[$group] ?? null, "{$group} should use the improved styleguide group title");
        }
    }

    public function testEveryContentBlockWizardIconUsesTypo3V14SvgStyle(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $normalizedIcons = [];
        foreach ($blocks as $block) {
            $name = basename($block);
            $icon = (string)file_get_contents("{$block}/assets/icon.svg");

            self::assertStringContainsString('viewBox="0 0 16 16"', $icon, "{$name} icon should use TYPO3 backend icon dimensions");
            self::assertStringContainsString('<title>', $icon, "{$name} icon should name the element for SVG consumers");
            self::assertStringContainsString('icon-root', $icon, "{$name} icon should declare the adaptive icon root class");
            self::assertStringContainsString('currentColor', $icon, "{$name} icon should inherit backend icon color");
            self::assertStringContainsString('color-scheme:light dark', $icon, "{$name} icon should declare color-scheme:light dark so currentColor stays scheme-aware when rendered as an <img> in backend dark mode");
            self::assertStringContainsString('--icon-color-accent', $icon, "{$name} icon should expose the TYPO3 accent variable");
            self::assertStringContainsString('icon-signature', $icon, "{$name} icon should include a visible per-element signature mark");
            self::assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $icon, "{$name} icon must not hard-code color fallbacks");
            self::assertStringNotContainsString('#000', strtolower($icon), "{$name} icon must not hard-code black");
            self::assertStringNotContainsString('#fff', strtolower($icon), "{$name} icon must not hard-code white");

            $normalizedIcons[$name] = (string)preg_replace('#<title>.*?</title>\s*#s', '', $icon);
        }

        self::assertCount(
            count($blocks),
            array_unique($normalizedIcons),
            'Every content element wizard icon should have distinct SVG geometry, not just a different title.'
        );
    }

    public function testAllSvgIconsUseTokenDrivenPaint(): void
    {
        foreach (self::collectSvgIconFiles() as $file) {
            $relative = str_replace(dirname(__DIR__, 2) . '/', '', $file);
            $icon = (string)file_get_contents($file);
            $lower = strtolower($icon);

            self::assertStringContainsString('icon-root', $icon, "{$relative} should declare the adaptive icon root class");
            self::assertStringContainsString('color-scheme:light dark', $icon, "{$relative} should declare color-scheme:light dark so currentColor stays scheme-aware when rendered as an <img> in backend dark mode");
            self::assertStringContainsString('--icon-color-accent', $icon, "{$relative} should expose the TYPO3 accent variable");
            self::assertStringContainsString('currentColor', $icon, "{$relative} should preserve currentColor inheritance");
            self::assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $icon, "{$relative} must not hard-code color fallbacks");
            self::assertStringNotContainsString('#000', $lower, "{$relative} must not hard-code black");
            self::assertStringNotContainsString('#fff', $lower, "{$relative} must not hard-code white");
            self::assertStringNotContainsString('black', $lower, "{$relative} must not hard-code black");
            self::assertStringNotContainsString('white', $lower, "{$relative} must not hard-code white");
            self::assertDoesNotMatchRegularExpression('/(?:fill|stroke)="#[0-9a-fA-F]{3,8}"/', $icon, "{$relative} must not hard-code SVG paint colors");
        }
    }

    public function testEveryContentBlockHasUsefulBackendPreview(): void
    {
        $previewCss = __DIR__ . '/../../Resources/Public/Css/content-preview.css';
        self::assertFileExists($previewCss, 'Shared backend preview CSS is missing');
        self::assertStringContainsString('.d-ce-preview', (string)file_get_contents($previewCss));

        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $name = basename($block);
            $template = (string)file_get_contents("{$block}/templates/backend-preview.fluid.html");

            self::assertStringContainsString('<f:layout name="Preview"/>', $template, "{$name} preview must use the Content Blocks Preview layout");
            self::assertStringContainsString('<f:section name="Header">', $template, "{$name} preview must define a Header section");
            self::assertStringContainsString('<f:section name="Content">', $template, "{$name} preview must define a Content section");
            self::assertStringContainsString('desiderio-content-preview', $template, "{$name} preview must load the shared preview CSS");
            self::assertStringContainsString('d-ce-preview', $template, "{$name} preview must render the useful preview card");
            self::assertStringNotContainsString('Preview for Content Block', $template, "{$name} still uses the generated fallback preview text");
        }
    }

    public function testContentBlockCssUsesShadcnThemeTokens(): void
    {
        $files = self::globList(self::CONTENT_BLOCKS_DIR . '/*/assets/frontend.css');
        self::assertCount(self::EXPECTED_COUNT, $files);

        foreach ($files as $file) {
            $css = (string)file_get_contents($file);
            self::assertStringNotContainsString('hsl(', $css, "{$file} must use shadcn CSS variables instead of local HSL colors");
            self::assertStringNotContainsString('#fff', strtolower($css), "{$file} must not hard-code white");
            self::assertStringNotContainsString('#000', strtolower($css), "{$file} must not hard-code black");
            self::assertStringNotContainsString('rgb(', $css, "{$file} must not hard-code rgb colors");
        }
    }

    public function testIconFieldsUseSharedSelectableRegistry(): void
    {
        $iconFieldCount = 0;
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);

        foreach ($blocks as $block) {
            $config = Yaml::parseFile("{$block}/config.yaml");
            self::assertIsArray($config);

            $fields = $config['fields'] ?? [];
            self::assertIsArray($fields);

            foreach (self::collectIconFieldConfigs($fields) as $path => $field) {
                $iconFieldCount++;
                self::assertSame('Select', $field['type'] ?? null, basename($block) . " {$path} must use a select field");
                self::assertSame('selectSingle', $field['renderType'] ?? null, basename($block) . " {$path} must use selectSingle");
                $itemsProcessors = $field['itemsProcessors'] ?? [];
                self::assertIsArray($itemsProcessors, basename($block) . " {$path} must define item processors");
                $iconItemsProcessor = $itemsProcessors[10] ?? [];
                self::assertIsArray($iconItemsProcessor, basename($block) . " {$path} must define icon item processor 10");
                self::assertSame(
                    IconItemsProcessor::class,
                    $iconItemsProcessor['class'] ?? null,
                    basename($block) . " {$path} must use the shared icon item processor"
                );
                self::assertArrayNotHasKey('items', $field, basename($block) . " {$path} must not define a divergent local icon list");
            }
        }

        // Shared record types hold icon fields too, and they are just as much
        // part of the catalog as the ones still declared inline.
        $recordTypePaths = glob(__DIR__ . '/../../ContentBlocks/RecordTypes/*/config.yaml');
        foreach ($recordTypePaths === false ? [] : $recordTypePaths as $recordTypePath) {
            $recordType = Yaml::parseFile($recordTypePath);
            self::assertIsArray($recordType);
            $recordTypeFields = $recordType['fields'] ?? [];
            self::assertIsArray($recordTypeFields);

            foreach (self::collectIconFieldConfigs($recordTypeFields) as $path => $field) {
                $iconFieldCount++;
                $label = basename(dirname($recordTypePath)) . " {$path}";
                self::assertSame('Select', $field['type'] ?? null, "{$label} must use a select field");
                $processors = $field['itemsProcessors'] ?? [];
                self::assertIsArray($processors, "{$label} must define item processors");
                $processor = $processors[10] ?? [];
                self::assertIsArray($processor, "{$label} must define icon item processor 10");
                self::assertSame(IconItemsProcessor::class, $processor['class'] ?? null, "{$label} must use the shared icon item processor");
            }
        }

        // 14, not the historical 17: five icon-field definitions across
        // benefit-cards, feature-carousel, feature-grid-3, feature-grid-4 and
        // feature-icons collapsed into two shared record types
        // (desiderio_icon_card_link, desiderio_icon_lead). 17 - 5 + 2 = 14.
        self::assertSame(14, $iconFieldCount);
    }

    public function testFixtureIconValuesUseIconNames(): void
    {
        $fixtureFiles = self::globList(self::CONTENT_BLOCKS_DIR . '/*/fixture.json');

        foreach ($fixtureFiles as $fixtureFile) {
            $data = json_decode((string)file_get_contents($fixtureFile), true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($data);
            self::assertFixtureIconValuesAreKeys($data, basename(dirname($fixtureFile)));
        }
    }

    public function testIframeFixtureUrlsDoNotUseShadcnDocumentationPages(): void
    {
        $fixtureFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/fixture.json');
        if ($fixtureFiles === false) {
            $fixtureFiles = [];
        }

        foreach ($fixtureFiles as $fixtureFile) {
            $data = json_decode((string)file_get_contents($fixtureFile), true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($data);
            self::assertFixtureIframeUrlsAreEmbeddable($data, basename(dirname($fixtureFile)));
        }
    }

    public function testNoShadcn2fluidLeftovers(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $name = basename($block);
            $files = [
                "{$block}/config.yaml",
                "{$block}/templates/frontend.html",
            ];
            foreach ($files as $file) {
                $content = (string)file_get_contents($file);
                self::assertStringNotContainsString('shadcn2fluid', $content, "{$name}:{$file} still references shadcn2fluid");
                self::assertStringNotContainsString('<s2f:', $content, "{$name}:{$file} still uses <s2f: namespace");
                self::assertStringNotContainsString('</s2f:', $content, "{$name}:{$file} still uses </s2f: namespace");
            }
        }
    }

    public function testStyleguideGroupsReferenceEveryContentBlockTypeName(): void
    {
        $groupsFile = __DIR__ . '/../../Resources/Private/Data/styleguide-content-groups.json';
        $groups = json_decode((string)file_get_contents($groupsFile), true);
        self::assertIsArray($groups);

        $typeNames = [];
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $config = Yaml::parseFile("{$block}/config.yaml");
            $typeName = (string)($config['typeName'] ?? '');
            self::assertNotSame('', $typeName, basename($block) . ' has no typeName');
            self::assertFileExists("{$block}/fixture.json", basename($block) . ' has no styleguide fixture');
            $typeNames[$typeName] = true;
        }

        $listedTypeNames = [];
        foreach ($groups as $group) {
            self::assertIsArray($group);
            self::assertArrayHasKey('elements', $group);
            self::assertIsArray($group['elements']);
            foreach ($group['elements'] as $element) {
                self::assertIsArray($element);
                $ctype = (string)($element['ctype'] ?? '');
                self::assertStringStartsWith('desiderio_', $ctype);
                self::assertArrayHasKey($ctype, $typeNames, "{$ctype} is listed in the styleguide but has no Content Block");
                $listedTypeNames[$ctype] = true;
            }
        }

        $expected = array_keys($typeNames);
        $actual = array_keys($listedTypeNames);
        sort($expected);
        sort($actual);

        self::assertCount(self::EXPECTED_COUNT, $listedTypeNames);
        self::assertSame($expected, $actual);
        self::assertStringNotContainsString('shadcn2fluid', (string)file_get_contents($groupsFile));
    }

    public function testStyleguideSeedCreatesOnePagePerWizardCategoryBelowParent505(): void
    {
        $seedFile = __DIR__ . '/../../Resources/Private/Data/styleguide-page-seed.json';
        self::assertFileExists($seedFile);

        $seed = json_decode((string)file_get_contents($seedFile), true);
        self::assertIsArray($seed);
        self::assertSame(505, $seed['parentPid'] ?? null);
        self::assertCount(10, $seed['groups'] ?? []);

        $elementCount = 0;
        foreach ($seed['groups'] as $group) {
            self::assertIsArray($group);
            self::assertArrayHasKey('groupTitle', $group);
            $groupElementCount = count($group['elements'] ?? []);
            self::assertGreaterThanOrEqual(20, $groupElementCount, (string)($group['groupId'] ?? 'group') . ' should seed about 25 elements');
            self::assertLessThanOrEqual(30, $groupElementCount, (string)($group['groupId'] ?? 'group') . ' should seed about 25 elements');
            $elementCount += count($group['elements']);
        }

        self::assertSame(self::EXPECTED_COUNT, $elementCount);
    }

    public function testFixtureFillerDryRunUsesProjectRootWhenOnlyOptionIsProvided(): void
    {
        $script = dirname(__DIR__, 2) . '/scripts/fill-content-element-fixtures.php';
        self::assertFileExists($script);

        $output = shell_exec(escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($script) . ' --dry-run');
        self::assertIsString($output);

        $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame([
            'dryRun' => true,
            'updated' => 0,
            'skipped' => 0,
        ], $result);
    }

    /**
     * @return list<string>
     */
    private static function collectSvgIconFiles(): array
    {
        $root = dirname(__DIR__, 2);
        $files = glob($root . '/ContentBlocks/ContentElements/*/assets/icon.svg');
        $files = $files === false ? [] : $files;
        $publicIconDirectory = $root . '/Resources/Public/Icons';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($publicIconDirectory));

        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'svg') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);
        return array_values(array_unique($files));
    }

    /**
     * @param array<string|int, mixed> $data
     * @param list<string> $path
     */
    private static function assertFixtureIconValuesAreKeys(array $data, string $blockName, array $path = []): void
    {
        $iconFields = ['icon' => true, 'icon_name' => true, 'icon_style' => true, 'tab_icon' => true];

        foreach ($data as $key => $value) {
            $segment = is_int($key) ? '[' . $key . ']' : $key;
            $nextPath = [...$path, $segment];

            if (is_string($key) && isset($iconFields[$key]) && is_string($value) && $value !== '' && $value !== 'none') {
                self::assertMatchesRegularExpression(
                    '/^[a-z0-9-]+$/',
                    $value,
                    sprintf('%s fixture icon field %s must use an icon key, not rendered text or emoji', $blockName, implode('.', $nextPath))
                );
                self::assertContains(
                    $value,
                    IconRegistry::keys(),
                    sprintf('%s fixture icon field %s must use a key from IconRegistry', $blockName, implode('.', $nextPath))
                );
            }

            if (is_array($value)) {
                self::assertFixtureIconValuesAreKeys($value, $blockName, $nextPath);
            }
        }
    }

    /**
     * @param array<string|int, mixed> $data
     * @param list<string> $path
     */
    private static function assertFixtureIframeUrlsAreEmbeddable(array $data, string $blockName, array $path = []): void
    {
        foreach ($data as $key => $value) {
            $segment = is_int($key) ? '[' . $key . ']' : $key;
            $nextPath = [...$path, $segment];

            if (is_string($key) && is_string($value) && self::isIframeUrlField($key)) {
                self::assertStringNotContainsString(
                    'https://ui.shadcn.com/docs/',
                    $value,
                    sprintf('%s fixture iframe field %s must use an embeddable URL, not a shadcn documentation page', $blockName, implode('.', $nextPath))
                );
            }

            if (is_array($value)) {
                self::assertFixtureIframeUrlsAreEmbeddable($value, $blockName, $nextPath);
            }
        }
    }

    private static function isIframeUrlField(string $field): bool
    {
        $normalized = strtolower(str_replace(['-', '_'], '', $field));

        return str_contains($normalized, 'embedurl') || str_contains($normalized, 'videourl');
    }

    /**
     * @param array<int|string, mixed> $fields
     * @param list<string> $path
     * @return array<string, array<string, mixed>>
     */
    private static function collectIconFieldConfigs(array $fields, array $path = []): array
    {
        $iconFields = ['icon' => true, 'icon_name' => true, 'icon_style' => true, 'tab_icon' => true];
        $result = [];

        foreach ($fields as $field) {
            if (!is_array($field)) {
                continue;
            }
            $fieldConfig = [];
            foreach ($field as $key => $value) {
                if (is_string($key)) {
                    $fieldConfig[$key] = $value;
                }
            }
            $identifier = $fieldConfig['identifier'] ?? '';
            if (!is_string($identifier)) {
                continue;
            }
            $nextPath = $path;
            if ($identifier !== '') {
                $nextPath[] = $identifier;
            }

            if (isset($iconFields[$identifier])) {
                $result[implode('.', $nextPath)] = $fieldConfig;
            }

            if (isset($fieldConfig['fields']) && is_array($fieldConfig['fields'])) {
                $result = array_merge($result, self::collectIconFieldConfigs($fieldConfig['fields'], $nextPath));
            }
        }

        return $result;
    }
}
