<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\ContentBlocks\JsonSchemaValidation\JsonSchemaValidator;

final class ContentBlockLimitKeysTest extends TestCase
{
    public function testAllContentBlocksUseTheSupportedLowercaseLimitKeys(): void
    {
        $violations = [];
        $paths = glob(__DIR__ . '/../../ContentBlocks/*/*/config.yaml');
        self::assertIsArray($paths);
        foreach ($paths as $path) {
            if (preg_match('/^\s*(minItems|maxItems):/m', (string)file_get_contents($path)) === 1) {
                $violations[] = basename(dirname($path));
            }
        }
        self::assertSame([], $violations, 'Content Blocks only reads minitems/maxitems. Audit existing content before activating them.');
    }
    public function testContentElementsPassTheInstalledContentBlocksSchema(): void
    {
        $validator = new JsonSchemaValidator();
        $paths = glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/config.yaml');
        self::assertIsArray($paths);
        foreach ($paths as $path) {
            $yaml = Yaml::parseFile($path);
            $object = json_decode(json_encode($yaml, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
            self::assertIsObject($object);
            self::assertFalse($validator->validateContentElement($object)->hasError(), $path);
        }
    }
    public function testSharedRecordTypesPassTheInstalledContentBlocksSchema(): void
    {
        $validator = new JsonSchemaValidator();
        $paths = glob(__DIR__ . '/../../ContentBlocks/RecordTypes/*/config.yaml');
        self::assertIsArray($paths);
        foreach ($paths as $path) {
            $yaml = Yaml::parseFile($path);
            $object = json_decode(json_encode($yaml, JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
            self::assertIsObject($object);
            self::assertFalse($validator->validateRecordType($object)->hasError(), $path);
        }
    }

}
