<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * GitHub's "Sponsor" button reads .github/FUNDING.yml. It points to the page
 * that explains how we earn (Supporter, sponsored features, plans, Care).
 * GitHub Sponsors stays a comment until the owner has enrolled.
 */
final class FundingFileTest extends TestCase
{
    public function testTheSponsorButtonLeadsToTheHowWeEarnPage(): void
    {
        $file = dirname(__DIR__, 2) . '/.github/FUNDING.yml';
        self::assertFileExists($file);

        $funding = Yaml::parseFile($file);
        self::assertIsArray($funding);
        self::assertSame(['custom'], array_keys($funding), 'Only the custom link is active; github: waits for the Sponsors listing.');
        self::assertSame(['https://typo3-lab.webconsulting.at/pricing/how-we-earn'], $funding['custom']);
        self::assertStringContainsString('# github: [dirnbauer]', (string)file_get_contents($file));
    }
}
