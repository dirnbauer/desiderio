<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class PageTemplateCompatibilityTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';

    public function testLegacyBackendLayoutsAreRegistered(): void
    {
        $configuration = (string) file_get_contents(self::ROOT . '/Configuration/BackendLayouts/Shadcn2fluidLegacy.tsconfig');

        self::assertStringContainsString('shadcn2fluid_home {', $configuration);
        self::assertStringContainsString('shadcn2fluid_sub {', $configuration);
        self::assertStringContainsString('shadcn2fluid_sub_nav {', $configuration);
        self::assertStringContainsString('shadcn2fluid_news {', $configuration);
    }

    public function testGlobalPageTsconfigImportsLegacyLayouts(): void
    {
        $configuration = (string) file_get_contents(self::ROOT . '/Configuration/page.tsconfig');

        self::assertStringContainsString(
            "@import 'EXT:desiderio/Configuration/BackendLayouts/Shadcn2fluidLegacy.tsconfig'",
            $configuration
        );
    }

    public function testLegacyPageTemplatesExist(): void
    {
        $templates = [
            'shadcn2fluid_home',
            'shadcn2fluid_sub',
            'shadcn2fluid_sub_nav',
            'shadcn2fluid_news',
        ];

        foreach ($templates as $template) {
            self::assertFileExists(
                self::ROOT . '/Resources/Private/Templates/Pages/' . $template . '.fluid.html',
                $template . ' template alias is missing'
            );
        }
    }
}
