<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ComponentStructureTest extends TestCase
{
    private const EXPECTED_TOTAL = 49;
    private const COMPONENTS_DIR = __DIR__ . '/../../Resources/Private/Components';
    private const EXPECTED_ATOMS = [
        'AspectRatio', 'Avatar', 'Badge', 'Button', 'ControlClass', 'Icon', 'Image', 'Input',
        'Label', 'Link', 'Progress', 'ScrollArea', 'Select', 'Separator',
        'Skeleton', 'Textarea', 'Typography',
    ];
    private const EXPECTED_MOLECULES = [
        'Accordion', 'AccordionItem', 'Alert', 'AlertDescription', 'AlertTitle',
        'Card', 'CardContent', 'CardFooter', 'CardHeader', 'CheckboxControl', 'CheckedListItem', 'Field',
        'FieldGroup', 'FieldLabel', 'FieldLegend', 'FieldSet', 'FormRenderer', 'OptionLabel',
        'RadioControl', 'SelectNative', 'Table', 'TableCell', 'TableHeader', 'TableRow',
        'Tabs', 'TabsContent', 'TabsList', 'TabsTrigger',
    ];
    private const EXPECTED_LAYOUTS = ['Container', 'Grid', 'Section', 'Stack'];

    public function testExpectedNumberOfComponents(): void
    {
        $atoms = glob(self::COMPONENTS_DIR . '/Atom/*', GLOB_ONLYDIR) ?: [];
        $molecules = glob(self::COMPONENTS_DIR . '/Molecule/*', GLOB_ONLYDIR) ?: [];
        $layouts = glob(self::COMPONENTS_DIR . '/Layout/*', GLOB_ONLYDIR) ?: [];

        self::assertCount(17, $atoms, 'Expected 17 atoms');
        self::assertCount(28, $molecules, 'Expected 28 molecules');
        self::assertCount(4, $layouts, 'Expected 4 layouts');
        self::assertSame(self::EXPECTED_TOTAL, count($atoms) + count($molecules) + count($layouts));
    }

    public function testExpectedAtomsArePresent(): void
    {
        foreach (self::EXPECTED_ATOMS as $name) {
            self::assertFileExists(self::COMPONENTS_DIR . "/Atom/{$name}/{$name}.fluid.html");
        }
    }

    public function testExpectedMoleculesArePresent(): void
    {
        foreach (self::EXPECTED_MOLECULES as $name) {
            self::assertFileExists(self::COMPONENTS_DIR . "/Molecule/{$name}/{$name}.fluid.html");
        }
    }

    public function testExpectedLayoutsArePresent(): void
    {
        foreach (self::EXPECTED_LAYOUTS as $name) {
            self::assertFileExists(self::COMPONENTS_DIR . "/Layout/{$name}/{$name}.fluid.html");
        }
    }

    public function testComponentTemplatesDeclareFluidArgument(): void
    {
        $all = array_merge(
            array_map(fn($n) => "Atom/{$n}", self::EXPECTED_ATOMS),
            array_map(fn($n) => "Molecule/{$n}", self::EXPECTED_MOLECULES),
            array_map(fn($n) => "Layout/{$n}", self::EXPECTED_LAYOUTS),
        );
        foreach ($all as $path) {
            $name = basename($path);
            $file = self::COMPONENTS_DIR . "/{$path}/{$name}.fluid.html";
            $content = (string) file_get_contents($file);
            self::assertStringContainsString('<f:argument', $content, "{$path} lacks typed <f:argument>");
        }
    }

    public function testCardRootUsesPresetRingAndDirectChildPaddingFallback(): void
    {
        $card = (string) file_get_contents(self::COMPONENTS_DIR . '/Molecule/Card/Card.fluid.html');

        // Radius is tokenized to the shadcn --radius scale (rounded-xl), so it switches
        // per preset; for the flat radix-lyra preset (--radius: 0) it still renders square.
        foreach (['rounded-xl', 'ring-1 ring-foreground/10', 'px-4', 'data-[size=sm]:px-3'] as $class) {
            self::assertStringContainsString($class, $card);
        }

        foreach ([
            'has-data-[slot=card-header]:px-0',
            'has-data-[slot=card-content]:px-0',
            'has-data-[slot=card-footer]:px-0',
            'has-[>img:first-child]:px-0',
        ] as $class) {
            self::assertStringContainsString($class, $card);
        }
    }

    public function testTailwindArbitrarySelectorsAreNotHtmlEntityEncoded(): void
    {
        $roots = [
            __DIR__ . '/../../Resources/Private',
            __DIR__ . '/../../ContentBlocks',
        ];
        $invalid = [];

        foreach ($roots as $root) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                if (!str_ends_with($path, '.html') && !str_ends_with($path, '.fluid.html')) {
                    continue;
                }

                $template = (string) file_get_contents($path);
                if (preg_match_all('/(?:class|value)="[^"\n]*(?:\[&amp;|&amp;&gt;|has-\[&gt;|group-has-\[&gt;)[^"\n]*"/', $template, $matches, PREG_OFFSET_CAPTURE) === false) {
                    continue;
                }

                foreach ($matches[0] as $match) {
                    $line = substr_count(substr($template, 0, $match[1]), "\n") + 1;
                    $invalid[] = str_replace(__DIR__ . '/../../', '', $path) . ':' . $line;
                }
            }
        }

        self::assertSame([], $invalid);
    }

    public function testTypographySupportsStableElementIds(): void
    {
        $typography = (string) file_get_contents(self::COMPONENTS_DIR . '/Atom/Typography/Typography.fluid.html');

        self::assertStringContainsString('<f:argument name="id" type="string" optional="{true}" />', $typography);
        self::assertStringContainsString('id="{id}"', $typography);
    }

    public function testFluidPrimitiveRecipesAreGeneratedFromConfiguredShadcnPreset(): void
    {
        $script = __DIR__ . '/../../Build/Scripts/sync-shadcn-fluid-primitives.php';
        self::assertFileExists($script);

        exec(PHP_BINARY . ' ' . escapeshellarg($script) . ' --check 2>&1', $output, $exitCode);

        self::assertSame(0, $exitCode, implode("\n", $output));

        $badge = (string) file_get_contents(self::COMPONENTS_DIR . '/Atom/Badge/Badge.fluid.html');
        self::assertStringContainsString('Generated by Build/Scripts/sync-shadcn-fluid-primitives.php.', $badge);
        self::assertStringContainsString('shadcn preset: b6G5977cw | style: radix-lyra', $badge);
    }
}
