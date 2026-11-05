<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * GitHub's "Sponsor" button reads .github/FUNDING.yml. It points to the public
 * contact page of webconsulting GmbH (Supporter, sponsored features, plans,
 * Care): the lab site with "How we earn" sits behind basic auth, so a public
 * button must never link there. GitHub Sponsors stays a comment until the
 * owner has enrolled.
 */
final class FundingFileTest extends TestCase
{
    public function testTheSponsorButtonLeadsToThePublicContactPage(): void
    {
        $file = dirname(__DIR__, 2) . '/.github/FUNDING.yml';
        self::assertFileExists($file);

        $funding = Yaml::parseFile($file);
        self::assertIsArray($funding);
        self::assertSame(['custom'], array_keys($funding), 'Only the custom link is active; github: waits for the Sponsors listing.');
        self::assertSame(['https://www.webconsulting.at/kontakt'], $funding['custom']);
        self::assertStringContainsString('# github: [dirnbauer]', (string)file_get_contents($file));
    }
}
