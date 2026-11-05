<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Screenshots that show a retired offer stay retired: the file is gone and no
 * fixture, seed class, capture script or configuration names it again.
 *
 * frontend-pricing-midnight-dark-87c4160e.png showed the plans before
 * 2026-09-27 (Pro €49, Agency €149); the current dark pricing screenshot is
 * frontend-feature-pricing-dark-dcf6dbfd.webp.
 */
final class RetiredScreenshotTest extends TestCase
{
    /**
     * Names without the hash, so a recapture under a new hash is caught too.
     */
    private const RETIRED = [
        'frontend-pricing-midnight-dark',
    ];

    /** Folders that quote screenshot file names. */
    private const SOURCES = ['Build/Scripts', 'Classes', 'Configuration', 'ContentBlocks', 'Resources/Private', 'Resources/Public'];

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function retiredScreenshots(): iterable
    {
        foreach (self::RETIRED as $name) {
            yield $name => [$name];
        }
    }

    #[DataProvider('retiredScreenshots')]
    public function testTheRetiredScreenshotIsGone(string $name): void
    {
        $files = glob(self::root() . '/Resources/Public/Styleguide/*/' . $name . '-*');
        self::assertSame([], $files === false ? [] : $files);
    }

    #[DataProvider('retiredScreenshots')]
    public function testNoSourceNamesTheRetiredScreenshot(string $name): void
    {
        $hits = [];
        foreach (self::SOURCES as $folder) {
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(self::root() . '/' . $folder, \FilesystemIterator::SKIP_DOTS));
            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !in_array($file->getExtension(), ['php', 'json', 'mjs', 'js', 'yaml', 'typoscript', 'html', 'xlf'], true)) {
                    continue;
                }
                if (str_contains((string)file_get_contents($file->getPathname()), $name)) {
                    $hits[] = substr($file->getPathname(), strlen(self::root()) + 1);
                }
            }
        }

        self::assertSame([], $hits, $name . ' shows a retired offer; use frontend-feature-pricing-dark-dcf6dbfd.webp or another current screenshot.');
    }

    private static function root(): string
    {
        return dirname(__DIR__, 2);
    }
}
