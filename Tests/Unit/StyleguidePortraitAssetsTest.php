<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Data\StarterSiteDefinitions;
use Webconsulting\Desiderio\Data\StyleguidePortraitAssets;

final class StyleguidePortraitAssetsTest extends TestCase
{
    public function testTeamGridPortraitFilesAreAvailable(): void
    {
        $files = StyleguidePortraitAssets::teamGridPortraitFiles();

        self::assertGreaterThanOrEqual(4, count($files));
        self::assertStringContainsString('Resources/Public/Styleguide/Unsplash/People/team-grid-', $files[0]);
    }

    public function testFileReferenceForMemberUsesUnsplashCollectionMetadata(): void
    {
        $reference = StyleguidePortraitAssets::fileReferenceForMember('Mara Stein', 0);

        self::assertNotSame('', $reference['file']);
        self::assertStringContainsString('team-grid-', $reference['file']);
        self::assertStringContainsString('Mara Stein', $reference['title']);
        self::assertStringContainsString('Mara Stein', $reference['alternative']);
        self::assertStringContainsString((string)StyleguidePortraitAssets::UNSPLASH_COLLECTION_ID, $reference['description']);
    }

    public function testCorporateStarterTeamMembersShipPortraitFixtures(): void
    {
        $starter = StarterSiteDefinitions::get('corporate');
        self::assertIsArray($starter);

        $teamBlock = null;
        foreach ($starter['home']['content'] as $block) {
            if (($block['ctype'] ?? '') === 'desiderio_teamgridminimal') {
                $teamBlock = $block;
                break;
            }
        }

        self::assertIsArray($teamBlock);
        $members = $teamBlock['fields']['members'] ?? null;
        self::assertIsArray($members);
        self::assertCount(4, $members);

        foreach ($members as $member) {
            self::assertIsArray($member);
            self::assertIsString($member['name'] ?? null);
            self::assertIsArray($member['image'] ?? null);
            self::assertNotSame('', $member['image']['file'] ?? '');
        }
    }
}
