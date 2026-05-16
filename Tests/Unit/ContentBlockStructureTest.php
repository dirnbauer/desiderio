<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\DataHandling\IconItemsProcessor;
use Webconsulting\Desiderio\Icon\IconRegistry;

final class ContentBlockStructureTest extends TestCase
{
    private const EXPECTED_COUNT = 255;
    private const CONTENT_BLOCKS_DIR = __DIR__ . '/../../ContentBlocks/ContentElements';

    public function testExpectedNumberOfContentBlocks(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        self::assertCount(self::EXPECTED_COUNT, $blocks, 'Content block count mismatch');
    }

    public function testEveryContentBlockHasRequiredFiles(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
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
            'textmedia' => 'Text & Media',
        ];

        foreach ($expectedTitles as $slug => $expectedTitle) {
            $config = Yaml::parseFile(self::CONTENT_BLOCKS_DIR . "/{$slug}/config.yaml");
            self::assertSame($expectedTitle, $config['title'] ?? null, "{$slug} should use the improved content element name");
        }
    }

    public function testEveryContentBlockUsesDesiderioVendor(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $name = basename($block);
            $config = Yaml::parseFile("{$block}/config.yaml");
            self::assertArrayHasKey('name', $config, "{$name} missing 'name'");
            self::assertStringStartsWith('desiderio/', (string) $config['name'], "{$name} must use desiderio/ vendor prefix");
            self::assertArrayHasKey('typeName', $config, "{$name} missing 'typeName'");
            self::assertStringStartsWith('desiderio_', (string) $config['typeName'], "{$name} typeName must start with desiderio_");
        }
    }

    public function testEveryContentBlockDeclaresSharedTypo3Basics(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $config = Yaml::parseFile("{$block}/config.yaml");
            $basics = $config['basics'] ?? [];
            self::assertIsArray($basics, basename($block) . ' basics must be a list');
            foreach (['TYPO3/Appearance', 'TYPO3/Links', 'TYPO3/Categories'] as $basic) {
                self::assertContains($basic, $basics, basename($block) . " must include {$basic}");
            }
        }
    }

    public function testEveryContentBlockHasEnglishAndGermanWizardLabels(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $name = basename($block);
            $english = (string) file_get_contents("{$block}/language/labels.xlf");
            $german = (string) file_get_contents("{$block}/language/de.labels.xlf");

            self::assertStringContainsString('<xliff version="2.0"', $english, "{$name} must use TYPO3 XLIFF 2.0 for English labels");
            self::assertStringContainsString('srcLang="en"', $english, "{$name} must declare English as source language");
            self::assertMatchesRegularExpression('/<unit id="title">\\s*<segment>\\s*<source>[^<]+<\\/source>/s', $english, "{$name} needs an English title");
            self::assertMatchesRegularExpression('/<unit id="description">\\s*<segment>\\s*<source>[^<]+Editors (?:can manage|get)[^<]+<\\/source>/s', $english, "{$name} needs a self-explanatory English description");
            self::assertStringNotContainsString('A shadcn/ui styled TYPO3 content element', $english, "{$name} still uses the old generic English description");

            self::assertStringContainsString('<xliff version="2.0"', $german, "{$name} must use TYPO3 XLIFF 2.0 for German labels");
            self::assertStringContainsString('trgLang="de"', $german, "{$name} must declare German as target language");
            self::assertMatchesRegularExpression('/<unit id="title">\\s*<segment state="final">\\s*<source>[^<]+<\\/source>\\s*<target>[^<]+<\\/target>/s', $german, "{$name} needs a German title target");
            self::assertMatchesRegularExpression('/<unit id="description">\\s*<segment state="final">\\s*<source>[^<]+<\\/source>\\s*<target>[^<]+Redakteure (?:pflegen|erhalten)[^<]+<\\/target>/s', $german, "{$name} needs a self-explanatory German description");
        }
    }

    public function testContentBlockTitlesAndDescriptionsAreDistinct(): void
    {
        $titles = [];
        $descriptions = [];
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];

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
            if (isset($descriptions[$description])) {
                self::fail("{$name} duplicates the description used by {$descriptions[$description]}");
            }
            self::assertStringNotContainsString('A shadcn/ui styled TYPO3 content element', $description, "{$name} still uses a generic description");

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
        $englishXliff = simplexml_load_string($englishLabels) ?: self::fail('labels.xlf must be valid XML');
        $germanXliff = simplexml_load_string($germanLabels) ?: self::fail('de.labels.xlf must be valid XML');

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
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        $normalizedIcons = [];
        foreach ($blocks as $block) {
            $name = basename($block);
            $icon = (string) file_get_contents("{$block}/assets/icon.svg");

            self::assertStringContainsString('viewBox="0 0 16 16"', $icon, "{$name} icon should use TYPO3 backend icon dimensions");
            self::assertStringContainsString('<title>', $icon, "{$name} icon should name the element for SVG consumers");
            self::assertStringContainsString('icon-root', $icon, "{$name} icon should declare the adaptive icon root class");
            self::assertStringContainsString('currentColor', $icon, "{$name} icon should inherit backend icon color");
            self::assertStringContainsString('color:var(--icon-color-primary,currentColor)', $icon, "{$name} icon should expose the TYPO3 primary icon variable without fixed fallbacks");
            self::assertStringContainsString('--icon-color-accent', $icon, "{$name} icon should expose the TYPO3 accent variable");
            self::assertStringContainsString('icon-signature', $icon, "{$name} icon should include a visible per-element signature mark");
            self::assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $icon, "{$name} icon must not hard-code color fallbacks");
            self::assertStringNotContainsString('#000', strtolower($icon), "{$name} icon must not hard-code black");
            self::assertStringNotContainsString('#fff', strtolower($icon), "{$name} icon must not hard-code white");

            $normalizedIcons[$name] = (string)preg_replace('#<title>.*?</title>\s*#s', '', $icon);
        }

        self::assertSame(
            count($blocks),
            count(array_unique($normalizedIcons)),
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
            self::assertStringContainsString('color:var(--icon-color-primary,currentColor)', $icon, "{$relative} should expose the TYPO3 primary icon variable without fixed fallbacks");
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

        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
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
        $files = glob(self::CONTENT_BLOCKS_DIR . '/*/assets/frontend.css') ?: [];
        self::assertCount(self::EXPECTED_COUNT, $files);

        foreach ($files as $file) {
            $css = (string) file_get_contents($file);
            self::assertStringNotContainsString('hsl(', $css, "{$file} must use shadcn CSS variables instead of local HSL colors");
            self::assertStringNotContainsString('#fff', strtolower($css), "{$file} must not hard-code white");
            self::assertStringNotContainsString('#000', strtolower($css), "{$file} must not hard-code black");
            self::assertStringNotContainsString('rgb(', $css, "{$file} must not hard-code rgb colors");
        }
    }

    public function testEveryFrontendTemplateDeclaresDesiderioNamespace(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $template = (string) file_get_contents("{$block}/templates/frontend.html");
            // Skip blocks that never use a d: component (allowed for trivial blocks)
            if (!str_contains($template, '<d:')) {
                continue;
            }
            self::assertStringContainsString(
                'xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"',
                $template,
                basename($block) . ' uses <d:> but does not declare xmlns:d'
            );
        }
    }

    public function testIconFieldsRenderThroughSharedIconAtom(): void
    {
        $templateFiles = array_merge(
            glob(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html') ?: [],
            glob(self::CONTENT_BLOCKS_DIR . '/*/templates/backend-preview.fluid.html') ?: [],
        );

        foreach ($templateFiles as $templateFile) {
            $template = (string) file_get_contents($templateFile);

            self::assertDoesNotMatchRegularExpression(
                '/<span(?:\\s+[^>]*)?>\\s*\\{(?:data|item|feature|counter|perk|value)\\.(?:icon|icon_name|icon_style|tab_icon)\\}\\s*<\\/span>/',
                $template,
                basename(dirname(dirname($templateFile))) . ' prints an icon field as text; render it through d:atom.icon instead'
            );
        }

        $styleguide = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/styleguide.js');
        self::assertStringContainsString('function renderIcon', $styleguide);
        self::assertDoesNotMatchRegularExpression(
            '/(?:\\+\\s*(?:item|d)\\.icon\\b|\\b(?:item|d)\\.icon\\s*\\+)/',
            $styleguide,
            'styleguide.js must render icon fixture values through the allowlisted SVG renderer'
        );
    }

    public function testIconFieldsUseSharedSelectableRegistry(): void
    {
        $iconFieldCount = 0;
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];

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

        self::assertSame(16, $iconFieldCount);
    }

    public function testFixtureIconValuesUseIconNames(): void
    {
        $fixtureFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/fixture.json') ?: [];

        foreach ($fixtureFiles as $fixtureFile) {
            $data = json_decode((string) file_get_contents($fixtureFile), true, 512, JSON_THROW_ON_ERROR);
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
            $data = json_decode((string) file_get_contents($fixtureFile), true, 512, JSON_THROW_ON_ERROR);
            self::assertIsArray($data);
            self::assertFixtureIframeUrlsAreEmbeddable($data, basename(dirname($fixtureFile)));
        }
    }

    public function testFrontendTemplatesDoNotUseBareBooleanAttributesOnFluidComponents(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        foreach ($blocks as $block) {
            $templateFile = "{$block}/templates/frontend.html";
            $template = (string) file_get_contents($templateFile);

            self::assertDoesNotMatchRegularExpression(
                '/<d:[^>]*\\s(?:itemscope|disabled|checked|selected|autofocus|required|readonly|multiple)(?:\\s|\\/?>)/',
                $template,
                basename($block) . ' uses a bare boolean HTML attribute on a Fluid component tag'
            );
        }
    }

    public function testLayoutSectionUsesDeclaredComponentArgumentsOnly(): void
    {
        $templateFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html');
        $templateFiles = $templateFiles === false ? [] : $templateFiles;
        foreach ($templateFiles as $templateFile) {
            $template = (string) file_get_contents($templateFile);

            self::assertDoesNotMatchRegularExpression(
                '/<d:layout\\.section\\b(?=[^>]*\\s(?:data|aria)-[a-z0-9_-]+\\s*=)[^>]*>/i',
                $template,
                basename(dirname(dirname($templateFile))) . ' passes HTML attributes directly to d:layout.section; use declared component arguments instead'
            );
        }
    }

    public function testTypolinkViewHelpersUseAdditionalAttributesForHtmlAttributes(): void
    {
        $templateFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html') ?: [];
        foreach ($templateFiles as $templateFile) {
            $lines = file($templateFile, FILE_IGNORE_NEW_LINES) ?: [];
            foreach ($lines as $lineNumber => $line) {
                if (!str_contains($line, '<f:link.typolink')) {
                    continue;
                }

                self::assertDoesNotMatchRegularExpression(
                    '/<f:link\\.typolink\\b[^\\n]*(?:\\saria-[a-z0-9_-]+\\s*=|\\srole\\s*=|\\sdata-[a-z0-9_-]+\\s*=)/i',
                    $line,
                    sprintf('%s:%d passes HTML attributes directly to f:link.typolink; use additionalAttributes instead', basename(dirname(dirname($templateFile))), $lineNumber + 1)
                );
            }
        }
    }

    public function testFieldBackedSplitViewHelpersProvideStringFallback(): void
    {
        $templateFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html') ?: [];
        foreach ($templateFiles as $templateFile) {
            $template = (string) file_get_contents($templateFile);

            self::assertDoesNotMatchRegularExpression(
                '/\\{[a-z][a-z0-9_]*(?:\\.[a-z0-9_]+)+\\s*->\\s*f:split\\(/i',
                $template,
                basename(dirname(dirname($templateFile))) . ' splits a nullable field directly; add f:or(alternative: \'\') before f:split'
            );
        }
    }

    public function testFalFilesAreRenderedWithPublicUrlInsteadOfResourceViewHelper(): void
    {
        $templateFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html') ?: [];
        foreach ($templateFiles as $templateFile) {
            $template = (string) file_get_contents($templateFile);

            self::assertStringNotContainsString(
                'f:uri.resource(path:',
                $template,
                basename(dirname(dirname($templateFile))) . ' passes FAL identifiers to f:uri.resource; use the FileReference publicUrl instead'
            );
            self::assertStringNotContainsString(
                'originalFile.identifier',
                $template,
                basename(dirname(dirname($templateFile))) . ' reads FAL identifiers for frontend URLs; use the FileReference publicUrl instead'
            );
        }
    }

    public function testFrontendTemplatesUseEveryDeclaredContentBlockField(): void
    {
        $systemFields = [
            'uid' => true,
            'pid' => true,
            'CType' => true,
            'colPos' => true,
            'sys_language_uid' => true,
            'relations' => true,
            'systemProperties' => true,
        ];

        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        foreach ($blocks as $block) {
            $name = basename($block);
            $config = Yaml::parseFile("{$block}/config.yaml");
            $template = (string) file_get_contents("{$block}/templates/frontend.html");
            $templateForFields = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $template) ?? $template;

            $fieldTypes = [];
            $nestedFields = [];
            foreach (($config['fields'] ?? []) as $field) {
                if (!isset($field['identifier'])) {
                    continue;
                }

                $identifier = (string)$field['identifier'];
                $fieldTypes[$identifier] = $field['type'] ?? (($field['useExistingField'] ?? false) ? 'Existing' : null);

                foreach (($field['fields'] ?? []) as $child) {
                    if (isset($child['identifier'])) {
                        $nestedFields[$identifier][(string)$child['identifier']] = true;
                    }
                }
            }

            $usedTopFields = [];
            preg_match_all('/data\.([A-Za-z_][A-Za-z0-9_]*)/', $templateForFields, $matches);
            foreach ($matches[1] as $field) {
                $usedTopFields[$field] = true;
            }

            preg_match_all('/\{data\s*->\s*f:render\.text\(field:\s*[\'"]([A-Za-z_][A-Za-z0-9_]*)[\'"]/', $templateForFields, $matches);
            foreach ($matches[1] as $field) {
                $usedTopFields[$field] = true;
            }

            preg_match_all('/each="\{data\.([A-Za-z_][A-Za-z0-9_]*)\}"\s+as="([A-Za-z_][A-Za-z0-9_]*)"/', $templateForFields, $loops, PREG_SET_ORDER);
            foreach ($loops as $loop) {
                [, $field, $variable] = $loop;
                $usedTopFields[$field] = true;

                if (($fieldTypes[$field] ?? null) === 'File') {
                    continue;
                }

                self::assertSame('Collection', $fieldTypes[$field] ?? null, "{$name}.{$field} is looped in Fluid but is not a Collection");

                $usedNestedFields = [];
                preg_match_all('/' . preg_quote($variable, '/') . '\.([A-Za-z_][A-Za-z0-9_]*)/', $templateForFields, $nestedMatches);
                foreach ($nestedMatches[1] as $nestedField) {
                    $usedNestedFields[$nestedField] = true;
                }

                preg_match_all('/\{' . preg_quote($variable, '/') . '\s*->\s*f:render\.text\(field:\s*[\'"]([A-Za-z_][A-Za-z0-9_]*)[\'"]/', $templateForFields, $renderMatches);
                foreach ($renderMatches[1] as $nestedField) {
                    $usedNestedFields[$nestedField] = true;
                }

                self::assertSame(
                    [],
                    array_values(array_diff(array_keys($usedNestedFields), array_keys($nestedFields[$field] ?? []))),
                    "{$name}.{$field} renders nested fields that are not declared"
                );
                self::assertSame(
                    [],
                    array_values(array_diff(array_keys($nestedFields[$field] ?? []), array_keys($usedNestedFields))),
                    "{$name}.{$field} has declared nested fields that are not rendered"
                );
            }

            self::assertSame(
                [],
                array_values(array_filter(
                    array_diff(array_keys($usedTopFields), array_keys($fieldTypes)),
                    static fn(string $field): bool => !isset($systemFields[$field])
                )),
                "{$name} renders top-level fields that are not declared"
            );
            self::assertSame(
                [],
                array_values(array_diff(array_keys($fieldTypes), array_keys($usedTopFields))),
                "{$name} has declared top-level fields that are not rendered"
            );
        }
    }

    public function testDateAndTimeFieldsAreFormattedInsteadOfRenderedAsText(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $config = Yaml::parseFile("{$block}/config.yaml");
            $dateFieldPaths = [];

            foreach (($config['fields'] ?? []) as $field) {
                $identifier = (string)($field['identifier'] ?? '');
                if ($identifier === '') {
                    continue;
                }

                if (in_array($field['type'] ?? null, ['Date', 'DateTime', 'Time'], true)) {
                    $dateFieldPaths[] = ['data', $identifier];
                }

                foreach (($field['fields'] ?? []) as $child) {
                    $childIdentifier = (string)($child['identifier'] ?? '');
                    if ($childIdentifier === '') {
                        continue;
                    }

                    if (in_array($child['type'] ?? null, ['Date', 'DateTime', 'Time'], true)) {
                        $dateFieldPaths[] = ['nested', $childIdentifier];
                    }
                }
            }

            if ($dateFieldPaths === []) {
                continue;
            }

            $name = basename($block);
            $templates = [
                "{$block}/templates/frontend.html",
                "{$block}/templates/backend-preview.fluid.html",
            ];

            foreach ($templates as $templateFile) {
                $template = (string)file_get_contents($templateFile);
                foreach ($dateFieldPaths as [$scope, $field]) {
                    if ($scope === 'data') {
                        self::assertStringNotContainsString(
                            "f:render.text(field: '{$field}')",
                            $template,
                            "{$name} renders {$field} through f:render.text(), but Date/DateTime values are objects in Visual Editor"
                        );
                        self::assertDoesNotMatchRegularExpression(
                            '/>\\s*\\{data\\.' . preg_quote($field, '/') . '\\}\\s*</',
                            $template,
                            "{$name} renders {$field} without f:format.date()"
                        );
                    } else {
                        self::assertStringNotContainsString(
                            "f:render.text(field: '{$field}')",
                            $template,
                            "{$name} renders nested {$field} through f:render.text(), but Date/DateTime values are objects in Visual Editor"
                        );
                    }
                }
            }
        }
    }

    public function testChartDataTemplatesHaveFrontendRenderer(): void
    {
        $chartScript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/charts.js');
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/setup.typoscript');

        self::assertStringContainsString('Resources/Public/Js/charts.js', $typoScript);
        self::assertStringContainsString('data-chart-data', $chartScript);
        self::assertStringContainsString('data-chart-json', $chartScript);

        $templateFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html') ?: [];
        foreach ($templateFiles as $templateFile) {
            $template = (string) file_get_contents($templateFile);
            if (!str_contains($template, 'data-chart-data=') && !str_contains($template, 'data-chart-json=')) {
                continue;
            }

            self::assertStringNotContainsString('<script', $template, basename(dirname(dirname($templateFile))) . ' must use Resources/Public/Js/charts.js instead of inline scripts');
        }
    }

    public function testNoShadcn2fluidLeftovers(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $name = basename($block);
            $files = [
                "{$block}/config.yaml",
                "{$block}/templates/frontend.html",
            ];
            foreach ($files as $file) {
                $content = (string) file_get_contents($file);
                self::assertStringNotContainsString('shadcn2fluid', $content, "{$name}:{$file} still references shadcn2fluid");
                self::assertStringNotContainsString('<s2f:', $content, "{$name}:{$file} still uses <s2f: namespace");
                self::assertStringNotContainsString('</s2f:', $content, "{$name}:{$file} still uses </s2f: namespace");
            }
        }
    }

    public function testStyleguideGroupsReferenceEveryContentBlockTypeName(): void
    {
        $groupsFile = __DIR__ . '/../../Resources/Private/Data/styleguide-content-groups.json';
        $groups = json_decode((string) file_get_contents($groupsFile), true);
        self::assertIsArray($groups);

        $typeNames = [];
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $config = Yaml::parseFile("{$block}/config.yaml");
            $typeName = (string) ($config['typeName'] ?? '');
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
                $ctype = (string) ($element['ctype'] ?? '');
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
        self::assertStringNotContainsString('shadcn2fluid', (string) file_get_contents($groupsFile));
    }

    public function testStyleguideSeedCreatesOnePagePerWizardCategoryBelowParent505(): void
    {
        $seedFile = __DIR__ . '/../../Resources/Private/Data/styleguide-page-seed.json';
        self::assertFileExists($seedFile);

        $seed = json_decode((string) file_get_contents($seedFile), true);
        self::assertIsArray($seed);
        self::assertSame(505, $seed['parentPid'] ?? null);
        self::assertCount(10, $seed['groups'] ?? []);

        $elementCount = 0;
        foreach ($seed['groups'] as $group) {
            self::assertIsArray($group);
            self::assertArrayHasKey('pageTitle', $group);
            $groupElementCount = count($group['elements'] ?? []);
            self::assertGreaterThanOrEqual(20, $groupElementCount, (string)($group['groupId'] ?? 'group') . ' should seed about 25 elements');
            self::assertLessThanOrEqual(30, $groupElementCount, (string)($group['groupId'] ?? 'group') . ' should seed about 25 elements');
            $elementCount += count($group['elements']);
        }

        self::assertSame(self::EXPECTED_COUNT, $elementCount);
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
