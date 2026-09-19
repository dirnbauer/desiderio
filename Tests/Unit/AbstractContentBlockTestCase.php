<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Base for the conformance suites that read the shipped Content Blocks
 * straight from disk.
 */
abstract class AbstractContentBlockTestCase extends TestCase
{
    protected const string CONTENT_BLOCKS_DIR = __DIR__ . '/../../ContentBlocks/ContentElements';

    /**
     * glob() returns false on failure; the tests always expect a list.
     *
     * @return list<string>
     */
    protected static function globList(string $pattern, int $flags = 0): array
    {
        $matches = glob($pattern, $flags);
        self::assertIsArray($matches, 'glob() failed for ' . $pattern);
        return $matches;
    }
}
