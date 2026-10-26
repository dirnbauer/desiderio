<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class FluidStyledContentIntegrationTest extends TestCase
{
    public function testBaseSetLoadsFluidStyledContentBeforeDesiderioOverrides(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');

        self::assertIsArray($baseSet);
        self::assertContains('typo3/fluid-styled-content', $baseSet['dependencies'] ?? []);
        self::assertFileExists(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');
    }

    public function testBlogStandaloneCanResolveItsFluidStyledContentDependency(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $blogStandaloneSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioBlogStandalone/config.yaml');

        self::assertIsArray($baseSet);
        self::assertIsArray($blogStandaloneSet);
        self::assertContains('typo3/fluid-styled-content', $baseSet['dependencies'] ?? []);
        self::assertContains('webconsulting/desiderio', $blogStandaloneSet['dependencies'] ?? []);
        self::assertContains('blog/standalone', $blogStandaloneSet['dependencies'] ?? []);
    }
}
