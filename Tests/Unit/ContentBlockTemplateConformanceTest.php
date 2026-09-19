<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use Symfony\Component\Yaml\Yaml;

/**
 * What every shipped Content Block frontend template must do: declare the
 * Desiderio namespaces, render through the shared atoms and components rather
 * than hand-rolled markup, use every field its config.yaml declares, and keep
 * FAL references editable.
 */
final class ContentBlockTemplateConformanceTest extends AbstractContentBlockTestCase
{
    public function testEveryFrontendTemplateDeclaresDesiderioNamespace(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        foreach ($blocks as $block) {
            $template = (string)file_get_contents("{$block}/templates/frontend.html");
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

    public function testContentElementsDoNotUseTypolinkForButtons(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);

        foreach ($blocks as $block) {
            $name = basename($block);
            $template = (string)file_get_contents("{$block}/templates/frontend.html");

            self::assertDoesNotMatchRegularExpression(
                '/<f:link\\.typolink\\b[^>]*class="[^"]*__button/',
                $template,
                "{$name} must not style f:link.typolink as a button; use d:atom.button instead"
            );
            self::assertDoesNotMatchRegularExpression(
                '/<f:link\\.typolink\\b[^>]*>\\s*<d:atom\\.button\\b/',
                $template,
                "{$name} must not wrap d:atom.button in f:link.typolink; pass href/target on the atom"
            );
        }
    }

    public function testContentElementCssDoesNotDefineButtonVariants(): void
    {
        $files = self::globList(self::CONTENT_BLOCKS_DIR . '/*/assets/frontend.css');

        foreach ($files as $file) {
            $css = (string)file_get_contents($file);
            $slug = basename(dirname($file, 2));

            self::assertDoesNotMatchRegularExpression(
                '/__button--(?:primary|outline|secondary|ghost)/',
                $css,
                "{$slug} must not define button color variants in frontend.css; use d:atom.button variants"
            );
            self::assertDoesNotMatchRegularExpression(
                '/__button(?::hover|:focus-visible|:active)\s*\{/',
                $css,
                "{$slug} must not override atom button interaction states in frontend.css"
            );
        }
    }

    public function testAtomButtonsDoNotDuplicateTargetAttributes(): void
    {
        $templateFiles = self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html');

        foreach ($templateFiles as $templateFile) {
            $template = (string)file_get_contents($templateFile);
            $slug = basename(dirname($templateFile, 2));

            self::assertDoesNotMatchRegularExpression(
                '/<d:atom\\.button\\b[^>]*\\btarget="[^"]*"[^>]*\\btarget="/',
                $template,
                "{$slug} must not declare duplicate target attributes on d:atom.button"
            );
        }
    }

    public function testPricingContentElementsUseMoleculeCard(): void
    {
        $pricingSlugs = [
            'bundle-pricing',
            'pricing-annual-monthly',
            'pricing-three-tier',
            'pricing-four-tier',
            'pricing-two-tier',
            'pricing',
            'pricing-simple',
            'pricing-enterprise',
        ];

        foreach ($pricingSlugs as $slug) {
            $templatePath = self::CONTENT_BLOCKS_DIR . "/{$slug}/templates/frontend.html";
            self::assertFileExists($templatePath, "{$slug} frontend template must exist");
            $template = (string)file_get_contents($templatePath);

            self::assertStringContainsString(
                'd:molecule.card',
                $template,
                "{$slug} must compose d:molecule.card for plan shells"
            );
            self::assertStringContainsString(
                'd:atom.button',
                $template,
                "{$slug} must compose d:atom.button for pricing CTAs"
            );
        }
    }

    public function testIconFieldsRenderThroughSharedIconAtom(): void
    {
        $templateFiles = array_merge(
            self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html'),
            self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/backend-preview.fluid.html'),
        );

        foreach ($templateFiles as $templateFile) {
            $template = (string)file_get_contents($templateFile);

            self::assertDoesNotMatchRegularExpression(
                '/<span(?:\\s+[^>]*)?>\\s*\\{(?:data|item|feature|counter|perk|value)\\.(?:icon|icon_name|icon_style|tab_icon)\\}\\s*<\\/span>/',
                $template,
                basename(dirname($templateFile, 2)) . ' prints an icon field as text; render it through d:atom.icon instead'
            );
        }

        $styleguide = (string)file_get_contents(__DIR__ . '/../../Resources/Public/Js/styleguide.js');
        self::assertStringContainsString('function renderIcon', $styleguide);
        self::assertDoesNotMatchRegularExpression(
            '/(?:\\+\\s*(?:item|d)\\.icon\\b|\\b(?:item|d)\\.icon\\s*\\+)/',
            $styleguide,
            'styleguide.js must render icon fixture values through the allowlisted SVG renderer'
        );
    }

    public function testFrontendTemplatesDoNotUseBareBooleanAttributesOnFluidComponents(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        foreach ($blocks as $block) {
            $templateFile = "{$block}/templates/frontend.html";
            $template = (string)file_get_contents($templateFile);

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
            $template = (string)file_get_contents($templateFile);

            self::assertDoesNotMatchRegularExpression(
                '/<d:layout\\.section\\b(?=[^>]*\\s(?:data|aria)-[a-z0-9_-]+\\s*=)[^>]*>/i',
                $template,
                basename(dirname($templateFile, 2)) . ' passes HTML attributes directly to d:layout.section; use declared component arguments instead'
            );
        }
    }

    public function testTypolinkViewHelpersUseAdditionalAttributesForHtmlAttributes(): void
    {
        $typolinkCount = 0;
        $templateFiles = self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html');
        foreach ($templateFiles as $templateFile) {
            $lines = file($templateFile, FILE_IGNORE_NEW_LINES);
            self::assertIsArray($lines);
            foreach ($lines as $lineNumber => $line) {
                if (!str_contains($line, '<f:link.typolink')) {
                    continue;
                }

                ++$typolinkCount;
                self::assertDoesNotMatchRegularExpression(
                    '/<f:link\\.typolink\\b[^\\n]*(?:\\saria-[a-z0-9_-]+\\s*=|\\srole\\s*=|\\sdata-[a-z0-9_-]+\\s*=|\\srel\\s*=)/i',
                    $line,
                    sprintf('%s:%d passes HTML attributes directly to f:link.typolink; use additionalAttributes instead', basename(dirname($templateFile, 2)), $lineNumber + 1)
                );
            }
        }

        self::assertSame(0, $typolinkCount, 'frontend templates should use d:atom.button/link instead of f:link.typolink');
    }

    public function testSplitViewHelpersUseTagSyntaxForArrayResults(): void
    {
        $templateFiles = self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html');
        foreach ($templateFiles as $templateFile) {
            $template = (string)file_get_contents($templateFile);

            self::assertDoesNotMatchRegularExpression(
                '/->\\s*f:split\\(/i',
                $template,
                basename(dirname($templateFile, 2)) . ' uses inline f:split(); assign array results with the <f:split> tag syntax'
            );
        }
    }

    public function testFalFilesAreRenderedWithPublicUrlInsteadOfResourceViewHelper(): void
    {
        $templateFiles = self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html');
        foreach ($templateFiles as $templateFile) {
            $template = (string)file_get_contents($templateFile);

            self::assertStringNotContainsString(
                'f:uri.resource(path:',
                $template,
                basename(dirname($templateFile, 2)) . ' passes FAL identifiers to f:uri.resource; use the FileReference publicUrl instead'
            );
            self::assertStringNotContainsString(
                'originalFile.identifier',
                $template,
                basename(dirname($templateFile, 2)) . ' reads FAL identifiers for frontend URLs; use the FileReference publicUrl instead'
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
            $template = (string)file_get_contents("{$block}/templates/frontend.html");
            $templateForFields = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $template) ?? $template;

            $fieldTypes = [];
            $nestedFields = [];
            foreach (($config['fields'] ?? []) as $field) {
                if (!isset($field['identifier'])) {
                    continue;
                }

                $identifier = (string)$field['identifier'];
                $fieldTypes[$identifier] = $field['type'] ?? (($field['useExistingField'] ?? false) === true ? 'Existing' : null);

                // A collection using `foreign_table:` declares its children in
                // the shared record type, so that is where they must be read
                // from — its own `fields:` is inert and normally absent.
                $foreignTable = is_array($field) ? ($field['foreign_table'] ?? null) : null;
                $children = $field['fields'] ?? [];
                if (!is_array($children) || ($children === [] && is_string($foreignTable))) {
                    $children = is_string($foreignTable) ? self::recordTypeFields($foreignTable) : [];
                }
                foreach ($children as $child) {
                    if (isset($child['identifier'])) {
                        $nestedFields[$identifier][(string)$child['identifier']] = true;
                    }
                }
            }

            // Provided by the Desiderio/Appearance basic, consumed via
            // <d:layout.section frame=... spaceBefore=... spaceAfter=...>.
            foreach (['frame_class', 'space_before_class', 'space_after_class'] as $appearanceField) {
                $fieldTypes[$appearanceField] ??= 'Basic';
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
                array_values(array_filter(
                    array_diff(array_keys($fieldTypes), array_keys($usedTopFields)),
                    static fn(string $field): bool => ($fieldTypes[$field] ?? '') !== 'Basic'
                )),
                "{$name} has declared top-level fields that are not rendered"
            );
        }
    }

    public function testDateAndTimeFieldsAreFormattedInsteadOfRenderedAsText(): void
    {
        $blocks = self::globList(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
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
        $chartScript = (string)file_get_contents(__DIR__ . '/../../Resources/Public/Js/charts.js');
        $viteEntry = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Assets/Components.entry.js');

        self::assertStringContainsString('../../Public/Js/charts.js', $viteEntry);
        self::assertStringContainsString('data-chart-data', $chartScript);
        self::assertStringContainsString('data-chart-json', $chartScript);

        $templateFiles = self::globList(self::CONTENT_BLOCKS_DIR . '/*/templates/frontend.html');
        foreach ($templateFiles as $templateFile) {
            $template = (string)file_get_contents($templateFile);
            if (!str_contains($template, 'data-chart-data=') && !str_contains($template, 'data-chart-json=')) {
                continue;
            }

            self::assertStringNotContainsString('<script', $template, basename(dirname($templateFile, 2)) . ' must use Resources/Public/Js/charts.js instead of inline scripts');
        }
    }

    public function testFrontendImageTagsKeepFalReferencesEditable(): void
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);
        $blocks = $blocks === false ? [] : $blocks;
        foreach ($blocks as $block) {
            $name = basename($block);
            $template = (string)file_get_contents("{$block}/templates/frontend.html");

            self::assertDoesNotMatchRegularExpression(
                '/<img\s+[^>]*src="\{f:uri\.image\(/s',
                $template,
                "{$name} renders FAL images through raw URI output; use <f:image image=\"{fileReference}\"> so Visual Editor image edit overlays can attach."
            );
        }
    }

    /**
     * Field list of a shared record type, keyed by its table.
     *
     * @return list<array<string, mixed>>
     */
    private static function recordTypeFields(string $table): array
    {
        /** @var array<string, list<array<string, mixed>>>|null $byTable */
        static $byTable = null;
        if ($byTable === null) {
            $byTable = [];
            $paths = glob(__DIR__ . '/../../ContentBlocks/RecordTypes/*/config.yaml');
            foreach ($paths === false ? [] : $paths as $path) {
                $config = Yaml::parseFile($path);
                if (!is_array($config) || !is_string($config['table'] ?? null)) {
                    continue;
                }
                $fields = $config['fields'] ?? [];
                $normalized = [];
                foreach (is_array($fields) ? $fields : [] as $field) {
                    if (!is_array($field)) {
                        continue;
                    }
                    $stringKeyed = [];
                    foreach ($field as $key => $value) {
                        $stringKeyed[(string)$key] = $value;
                    }
                    $normalized[] = $stringKeyed;
                }
                $byTable[$config['table']] = $normalized;
            }
        }

        return $byTable[$table] ?? [];
    }
}
