<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ComponentStructureTest extends TestCase
{
    private const EXPECTED_TOTAL = 37;
    private const COMPONENTS_DIR = __DIR__ . '/../../Resources/Private/Components';
    private const EXPECTED_ATOMS = [
        'AspectRatio', 'Avatar', 'Badge', 'Button', 'Icon', 'Image', 'Input',
        'Label', 'Link', 'Progress', 'ScrollArea', 'Select', 'Separator',
        'Skeleton', 'Textarea', 'Typography',
    ];
    private const EXPECTED_MOLECULES = [
        'Accordion', 'AccordionItem', 'Alert', 'AlertDescription', 'AlertTitle',
        'Card', 'CardContent', 'CardFooter', 'CardHeader', 'Table', 'TableCell',
        'TableHeader', 'TableRow', 'Tabs', 'TabsContent', 'TabsList', 'TabsTrigger',
    ];
    private const EXPECTED_LAYOUTS = ['Container', 'Grid', 'Section', 'Stack'];

    public function testExpectedNumberOfComponents(): void
    {
        $atoms = glob(self::COMPONENTS_DIR . '/Atom/*', GLOB_ONLYDIR) ?: [];
        $molecules = glob(self::COMPONENTS_DIR . '/Molecule/*', GLOB_ONLYDIR) ?: [];
        $layouts = glob(self::COMPONENTS_DIR . '/Layout/*', GLOB_ONLYDIR) ?: [];

        self::assertCount(16, $atoms, 'Expected 16 atoms');
        self::assertCount(17, $molecules, 'Expected 17 molecules');
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
}
