<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ContentElementCatalogConsistencyTest extends TestCase
{
    private const ROOT = __DIR__ . '/../..';
    private const CONTENT_BLOCKS_DIR = self::ROOT . '/ContentBlocks/ContentElements';

    public function testStyleguideManifestsMirrorContentBlockTitlesAndGroups(): void
    {
        $metadata = $this->collectContentBlockMetadata();
        $manifestFiles = [
            self::ROOT . '/Resources/Private/Data/styleguide-content-groups.json' => false,
            self::ROOT . '/Resources/Private/Data/styleguide-page-seed.json' => true,
        ];

        foreach ($manifestFiles as $manifestFile => $wrapped) {
            $document = json_decode(
                (string)file_get_contents($manifestFile),
                true,
                512,
                JSON_THROW_ON_ERROR,
            );
            self::assertIsArray($document);

            $groups = $wrapped ? ($document['groups'] ?? null) : $document;
            self::assertIsArray($groups, basename($manifestFile) . ' must contain groups');

            $seen = [];
            $seenGroups = [];
            foreach ($groups as $group) {
                self::assertIsArray($group);
                $groupId = $group['groupId'] ?? null;
                self::assertIsString($groupId, "{$manifestFile} contains a group without a string groupId");
                self::assertNotSame('', $groupId);
                self::assertArrayNotHasKey($groupId, $seenGroups, "{$manifestFile} lists group {$groupId} more than once");
                self::assertArrayHasKey('elements', $group, "{$manifestFile} group {$groupId} has no elements list");
                self::assertIsArray($group['elements'], "{$manifestFile} group {$groupId} must contain an array of elements");
                self::assertNotSame([], $group['elements'], "{$manifestFile} group {$groupId} must not be empty");
                $seenGroups[$groupId] = true;

                foreach ($group['elements'] as $element) {
                    self::assertIsArray($element);
                    $cType = $element['ctype'] ?? null;
                    self::assertIsString($cType, "{$manifestFile} group {$groupId} contains an element without a string CType");
                    self::assertArrayHasKey($cType, $metadata, "{$manifestFile} contains unknown CType {$cType}");
                    self::assertArrayNotHasKey($cType, $seen, "{$manifestFile} lists {$cType} more than once");
                    self::assertSame($metadata[$cType]['title'], $element['name'] ?? null, "{$manifestFile} has a stale title for {$cType}");
                    self::assertSame($metadata[$cType]['group'], $groupId, "{$manifestFile} puts {$cType} in the wrong group");
                    $seen[$cType] = true;
                }
            }

            $expectedTypes = array_keys($metadata);
            $actualTypes = array_keys($seen);
            sort($expectedTypes);
            sort($actualTypes);
            self::assertSame($expectedTypes, $actualTypes, "{$manifestFile} must list every Content Block exactly once");

            $expectedGroups = array_values(array_unique(array_column($metadata, 'group')));
            $actualGroups = array_keys($seenGroups);
            sort($expectedGroups);
            sort($actualGroups);
            self::assertSame($expectedGroups, $actualGroups, "{$manifestFile} must list every Content Block group exactly once");
        }
    }

    public function testPublicContentBlockCountsMatchTheFilesystem(): void
    {
        $count = count($this->collectContentBlockMetadata());
        $requiredClaims = [
            self::ROOT . '/composer.json' => "{$count} Desiderio Content Blocks",
            self::ROOT . '/ext_emconf.php' => "{$count} Desiderio Content Blocks",
            self::ROOT . '/README.md' => "{$count} Desiderio Content Blocks",
            self::ROOT . '/Documentation/Editor/Index.rst' => "{$count} editor-facing Desiderio Content Blocks",
            self::ROOT . '/Resources/Private/Templates/Pages/DesiderioStyleguide.fluid.html' => "{$count} production-ready Desiderio Content Blocks",
        ];

        foreach ($requiredClaims as $file => $claim) {
            self::assertStringContainsString($claim, (string)file_get_contents($file), "{$file} has a stale Content Block count");
        }

        $countPatterns = [
            '~\b(?:all\s+)?(\d+)\s+(?:(?:production-ready|editor-facing|ready-made|ready)\s+)?(?:Desiderio\s+)?(?:Content Blocks?|content elements?)\b~i',
            '~\ball\s+(\d+)\s+elements\b~i',
            '~\b(\d+)\s+(?:content\s+)?elements\b~i',
            '~\b(?:all\s+)?(\d+)\s+Desiderio\s+`config\.yaml`\s+files\b~i',
            '~\b(\d+)\s+(?:YAML parses|JSON file reads)\b~i',
        ];

        $matchesInspected = 0;
        foreach ($this->collectCatalogCopyFiles() as $file) {
            $source = (string)file_get_contents($file);
            foreach ($countPatterns as $pattern) {
                preg_match_all($pattern, $source, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $matchesInspected++;
                    $normalizedPhrase = preg_replace('/\s+/', ' ', $match[0]);
                    self::assertIsString($normalizedPhrase);
                    $phrase = trim($normalizedPhrase);
                    self::assertSame($count, (int)$match[1], "{$file} has a stale catalog count in: {$phrase}");
                }
            }
        }
        self::assertGreaterThan(20, $matchesInspected, 'Catalog count scan did not inspect the expected public copy');
    }

    public function testDesiderioLllReferencesResolveInBaseCatalogs(): void
    {
        $configFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/config.yaml');
        $templateFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/templates/*.html');
        self::assertIsArray($configFiles);
        self::assertIsArray($templateFiles);
        $sourceFiles = array_merge($configFiles, $templateFiles);
        $catalogIds = [];
        $failures = [];
        $dynamicChangelogReferenceFound = false;

        foreach ($sourceFiles as $sourceFile) {
            $source = (string)file_get_contents($sourceFile);
            preg_match_all(
                '~LLL:EXT:desiderio/([^:\s"\'<>]+):([A-Za-z0-9_.{}-]+)~',
                $source,
                $references,
                PREG_SET_ORDER,
            );

            foreach ($references as $reference) {
                $catalogPath = self::ROOT . '/' . $reference[1];
                $keys = [$reference[2]];
                if ($reference[2] === 'changelog.tag.{tagKey}') {
                    $dynamicChangelogReferenceFound = true;
                    $keys = array_map(
                        static fn(string $value): string => 'changelog.tag.' . $value,
                        $this->collectChangelogTagValues(),
                    );
                } elseif (str_contains($reference[2], '{')) {
                    $failures[] = "{$sourceFile}: unsupported dynamic LLL key {$reference[2]}";
                    continue;
                }

                if (!is_file($catalogPath)) {
                    $failures[] = "{$sourceFile}: missing catalog {$reference[1]}";
                    continue;
                }
                if (!isset($catalogIds[$catalogPath])) {
                    $catalog = (string)file_get_contents($catalogPath);
                    preg_match_all('/<unit\s+id="([^"]+)"/', $catalog, $units);
                    $catalogIds[$catalogPath] = array_fill_keys($units[1], true);
                }

                foreach ($keys as $key) {
                    if (!isset($catalogIds[$catalogPath][$key])) {
                        $failures[] = "{$sourceFile}: missing {$reference[1]}:{$key}";
                    }
                }
            }
        }

        sort($failures);
        self::assertTrue($dynamicChangelogReferenceFound, 'The dynamic changelog label contract was not inspected');
        self::assertSame([], $failures, "Unresolved Desiderio LLL references:\n" . implode("\n", $failures));
    }

    /**
     * @return array<string, array{title: string, group: string}>
     */
    private function collectContentBlockMetadata(): array
    {
        $metadata = [];
        $configFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/config.yaml');
        self::assertIsArray($configFiles);
        foreach ($configFiles as $configFile) {
            $config = Yaml::parseFile($configFile);
            self::assertIsArray($config);
            $cType = $config['typeName'] ?? null;
            $title = $config['title'] ?? null;
            $group = $config['group'] ?? null;
            self::assertIsString($cType);
            self::assertIsString($title);
            self::assertIsString($group);
            self::assertNotSame('', $cType, basename(dirname($configFile)) . ' has no typeName');
            $metadata[$cType] = [
                'title' => $title,
                'group' => $group,
            ];
        }

        return $metadata;
    }

    /**
     * @return list<string>
     */
    private function collectCatalogCopyFiles(): array
    {
        $fixtureFiles = glob(self::CONTENT_BLOCKS_DIR . '/*/fixture.json');
        self::assertIsArray($fixtureFiles);
        $files = [
            self::ROOT . '/composer.json',
            self::ROOT . '/ext_emconf.php',
            self::ROOT . '/README.md',
            self::ROOT . '/Resources/Private/Templates/Pages/DesiderioStyleguide.fluid.html',
            ...$fixtureFiles,
        ];

        $documentation = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(self::ROOT . '/Documentation', \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($documentation as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'rst') {
                $files[] = $file->getPathname();
            }
        }

        $files = array_values(array_unique($files));
        sort($files);

        return $files;
    }

    /**
     * @return list<string>
     */
    private function collectChangelogTagValues(): array
    {
        $config = Yaml::parseFile(self::CONTENT_BLOCKS_DIR . '/changelog/config.yaml');
        self::assertIsArray($config);

        $fields = $config['fields'] ?? null;
        self::assertIsArray($fields);
        foreach ($fields as $field) {
            if (!is_array($field) || ($field['identifier'] ?? null) !== 'items') {
                continue;
            }
            $childFields = $field['fields'] ?? null;
            self::assertIsArray($childFields);
            foreach ($childFields as $child) {
                if (!is_array($child) || ($child['identifier'] ?? null) !== 'tag' || ($child['type'] ?? null) !== 'Select') {
                    continue;
                }

                $values = [];
                $items = $child['items'] ?? null;
                self::assertIsArray($items);
                foreach ($items as $item) {
                    if (is_array($item) && is_string($item['value'] ?? null) && $item['value'] !== '') {
                        $values[] = $item['value'];
                    }
                }
                self::assertNotSame([], $values, 'changelog.tag Select must define values');
                return $values;
            }
        }

        self::fail('Could not find changelog.items.tag Select field');
    }
}
