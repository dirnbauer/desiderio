<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ExtensionMetadataTest extends TestCase
{
    public function testComposerJsonHasExpectedIdentity(): void
    {
        $composer = json_decode((string) file_get_contents(__DIR__ . '/../../composer.json'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('webconsulting/desiderio', $composer['name']);
        self::assertSame('typo3-cms-extension', $composer['type']);
        self::assertSame('desiderio', $composer['extra']['typo3/cms']['extension-key']);
        self::assertArrayHasKey('Webconsulting\\Desiderio\\', $composer['autoload']['psr-4']);
        self::assertSame('*', $composer['conflict']['webconsulting/shadcn2fluid-templates'] ?? null);
        self::assertSame('^8.3', $composer['require']['php'] ?? null);
        self::assertSame('^14.3', $composer['require']['typo3/cms-core'] ?? null);
        self::assertSame('^14.3', $composer['require']['typo3/cms-fluid'] ?? null);
    }

    public function testExtEmconfMatchesComposer(): void
    {
        $_EXTKEY = 'desiderio';
        require __DIR__ . '/../../ext_emconf.php';
        $conf = $EM_CONF[$_EXTKEY];

        self::assertSame('2.2.0', $conf['version']);
        self::assertSame('stable', $conf['state']);
        self::assertArrayHasKey('php', $conf['constraints']['depends']);
        self::assertArrayHasKey('workspaces', $conf['constraints']['depends']);
        self::assertArrayHasKey('content_blocks', $conf['constraints']['depends']);
        self::assertArrayHasKey('vite_asset_collector', $conf['constraints']['depends']);
        self::assertArrayHasKey('shadcn2fluid_templates', $conf['constraints']['conflicts']);
        self::assertArrayHasKey('solr', $conf['constraints']['suggests']);
        self::assertArrayHasKey('news', $conf['constraints']['suggests']);
        self::assertArrayHasKey('blog', $conf['constraints']['suggests']);
    }

    public function testIconsRegistryIsReadable(): void
    {
        $icons = require __DIR__ . '/../../Configuration/Icons.php';

        self::assertIsArray($icons);
        self::assertNotEmpty($icons);
        foreach ($icons as $key => $config) {
            self::assertStringStartsWith('desiderio-', $key, "Icon key {$key} must use desiderio- prefix");
            self::assertArrayHasKey('provider', $config);
            self::assertArrayHasKey('source', $config);
            self::assertStringStartsWith('EXT:desiderio/', $config['source']);
        }
    }

    public function testSolrDefaultsSetIsOwnedByDesiderio(): void
    {
        $config = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/SolrDefaults/config.yaml');
        $setup = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/SolrDefaults/setup.typoscript');

        self::assertStringContainsString('name: dirnbauer/solr-defaults', $config);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Solr/Templates/', $setup);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Solr/Partials/', $setup);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Solr/Layouts/', $setup);
    }

    public function testSharedPaginationPartialsAreAvailableFromDesiderio(): void
    {
        $pagination = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Partials/Pagination.html');

        self::assertFileExists(__DIR__ . '/../../Resources/Private/Partials/List/Pagination.html');
        self::assertFileExists(__DIR__ . '/../../Resources/Private/Partials/Pagination/Pagination.html');
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Language/locallang.xlf', $pagination);
    }
}
