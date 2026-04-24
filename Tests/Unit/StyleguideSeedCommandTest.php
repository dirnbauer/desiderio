<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Command\SeedStyleguidePagesCommand;

final class StyleguideSeedCommandTest extends TestCase
{
    public function testCommandUsesConfiguredParentPageAndStyleguideFixtures(): void
    {
        $commandFile = __DIR__ . '/../../Classes/Command/SeedStyleguidePagesCommand.php';
        $source = (string) file_get_contents($commandFile);

        self::assertSame(505, SeedStyleguidePagesCommand::DEFAULT_PARENT_PID);
        self::assertStringContainsString("name: 'desiderio:styleguide:seed'", $source);
        self::assertStringContainsString("->addOption(\n                'dry-run'", $source);
        self::assertStringContainsString('StyleguideContentGroups::getGroupsWithFixtures()', $source);
        self::assertStringContainsString("->update('tt_content')", $source);
        self::assertStringContainsString("'desiderio_%'", $source);
    }
}
