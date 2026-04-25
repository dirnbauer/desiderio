<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ContentBlockStructureTest extends TestCase
{
    private const EXPECTED_COUNT = 255;
    private const CONTENT_BLOCKS_DIR = __DIR__ . '/../../ContentBlocks/ContentElements';

    public function testExpectedNumberOfContentBlocks(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        self::assertCount(self::EXPECTED_COUNT, $blocks, 'Content block count mismatch');
    }

    public function testEveryContentBlockHasRequiredFiles(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $name = basename($block);
            self::assertFileExists("{$block}/config.yaml", "Missing config.yaml in {$name}");
            self::assertFileExists("{$block}/templates/frontend.html", "Missing frontend.html in {$name}");
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

            self::assertMatchesRegularExpression('/<trans-unit id="title">\\s*<source>[^<]+<\\/source>/s', $english, "{$name} needs an English title");
            self::assertStringContainsString('A shadcn/ui styled TYPO3 content element', $english, "{$name} needs the standardized English description");
            self::assertMatchesRegularExpression('/<trans-unit id="title">\\s*<source>[^<]+<\\/source>\\s*<target>[^<]+<\\/target>/s', $german, "{$name} needs a German title target");
            self::assertStringContainsString('Ein shadcn/ui gestaltetes TYPO3 Inhaltselement', $german, "{$name} needs the standardized German description");
        }
    }

    public function testEveryContentBlockWizardIconUsesTypo3V14SvgStyle(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
        foreach ($blocks as $block) {
            $name = basename($block);
            $icon = (string) file_get_contents("{$block}/assets/icon.svg");

            self::assertStringContainsString('viewBox="0 0 16 16"', $icon, "{$name} icon should use TYPO3 backend icon dimensions");
            self::assertStringContainsString('currentColor', $icon, "{$name} icon should inherit backend icon color");
            self::assertStringContainsString('--icon-color-accent', $icon, "{$name} icon should expose the TYPO3 accent variable");
            self::assertStringNotContainsString('#000', strtolower($icon), "{$name} icon must not hard-code black");
            self::assertStringNotContainsString('#fff', strtolower($icon), "{$name} icon must not hard-code white");
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

    public function testFrontendTemplatesDoNotUseBareBooleanAttributesOnFluidComponents(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR) ?: [];
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
}
