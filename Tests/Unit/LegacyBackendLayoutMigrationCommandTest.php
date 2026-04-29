<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Webconsulting\Desiderio\Command\MigrateLegacyBackendLayoutsCommand;

final class LegacyBackendLayoutMigrationCommandTest extends TestCase
{
    public function testLegacyLayoutsAreMappedToCurrentDesiderioIdentifiers(): void
    {
        $reflection = new ReflectionClass(MigrateLegacyBackendLayoutsCommand::class);
        $map = $reflection->getConstant('LEGACY_LAYOUT_MAP');

        self::assertSame([
            'shadcn2fluid_home' => 'DesiderioStartpage',
            'shadcn2fluid_sub' => 'DesiderioContentpage',
            'shadcn2fluid_sub_nav' => 'DesiderioContentpageSidebar',
            'shadcn2fluid_news' => 'DesiderioContentpageSidebar',
        ], $map);
    }

    public function testPageTsConfigStorageValuesAreMappedWithPrefix(): void
    {
        $reflection = new ReflectionClass(MigrateLegacyBackendLayoutsCommand::class);
        $method = $reflection->getMethod('getLegacyStorageLayoutMap');
        $map = $method->invoke(null);

        self::assertIsArray($map);
        self::assertSame('DesiderioStartpage', $map['shadcn2fluid_home']);
        self::assertSame('pagets__DesiderioStartpage', $map['pagets__shadcn2fluid_home']);
        self::assertSame('pagets__DesiderioContentpage', $map['pagets__shadcn2fluid_sub']);
        self::assertSame('pagets__DesiderioContentpageSidebar', $map['pagets__shadcn2fluid_sub_nav']);
    }

    public function testCommandMetadataAndPageFieldsAreDeclared(): void
    {
        $commandFile = __DIR__ . '/../../Classes/Command/MigrateLegacyBackendLayoutsCommand.php';
        $source = (string) file_get_contents($commandFile);

        self::assertStringContainsString("name: 'desiderio:backend-layout:migrate-legacy'", $source);
        self::assertStringContainsString("'backend_layout'", $source);
        self::assertStringContainsString("'backend_layout_next_level'", $source);
        self::assertStringContainsString("'dry-run'", $source);
    }
}
