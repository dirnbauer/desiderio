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
}
