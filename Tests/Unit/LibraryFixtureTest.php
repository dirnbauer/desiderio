<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Data\ContentBlockDefinitionRegistry;
use Webconsulting\Desiderio\Icon\IconRegistry;

/**
 * Guards the element library's demo content (library.json per content element).
 *
 * These records are what an editor sees in the picker and copies into their own
 * page, so the failure mode this suite exists to prevent is the one the library
 * shipped with for months: every element drawing its headline from one shared
 * vocabulary pool, so "Support that scales with you" appeared on dozens of
 * unrelated elements and told the editor nothing about what they were choosing.
 *
 * The uniqueness and banned-token rules are therefore not style policing - they
 * are the only thing that keeps that regression from creeping back in one
 * copy-pasted file at a time.
 */
final class LibraryFixtureTest extends TestCase
{
    private const CONTENT_BLOCKS_DIR = __DIR__ . '/../../ContentBlocks/ContentElements';
    private const LIBRARY_ASSET_DIR = __DIR__ . '/../../Resources/Public/Styleguide/Library';

    /** Fields whose value is the element's visible headline. */
    private const HEADLINE_FIELDS = ['header', 'headline', 'title'];

    /**
     * The generic subjects the shared vocabulary pool used to emit, plus the
     * self-referential vocabulary that betrays demo content as marketing for
     * the design system rather than for the editor's own site.
     *
     * @var list<string>
     */
    private const BANNED_SUBSTRINGS = [
        'Support that scales with you',
        'Simple, powerful, reliable',
        'Built for growing teams',
        'Made for modern teams',
        'Everything in one place',
        'Designed around your workflow',
        'Work smarter, not harder',
        'Results you can measure',
        'Desiderio',
        'shadcn',
        'composer require',
        'Content Block',
        'TYPO3',
    ];

    /**
     * Yaml::parseFile() is typed array<mixed, mixed>; the definition registry
     * needs string keys, which every config.yaml has by construction.
     *
     * @return array<string, mixed>
     */
    private function parseConfig(string $path): array
    {
        $config = Yaml::parseFile($path);
        self::assertIsArray($config);

        $normalized = [];
        foreach ($config as $key => $value) {
            $normalized[(string)$key] = $value;
        }

        return $normalized;
    }

    /** @return list<string> */
    private function blockDirectories(): array
    {
        $blocks = glob(self::CONTENT_BLOCKS_DIR . '/*', GLOB_ONLYDIR);

        return $blocks === false ? [] : $blocks;
    }

    /** @return array<string, array<string, mixed>> keyed by block name */
    private function libraryFixtures(string $suffix = ''): array
    {
        $fixtures = [];
        foreach ($this->blockDirectories() as $block) {
            $path = $block . '/library' . ($suffix === '' ? '' : '.' . $suffix) . '.json';
            if (!is_readable($path)) {
                continue;
            }
            $decoded = json_decode((string)file_get_contents($path), true);
            self::assertIsArray($decoded, 'library.json is not valid JSON in ' . basename($block));
            /** @var array<string, mixed> $decoded */
            $fixtures[basename($block)] = $decoded;
        }

        return $fixtures;
    }

    public function testEveryContentBlockShipsLibraryDemoContent(): void
    {
        $missing = [];
        foreach ($this->blockDirectories() as $block) {
            if (!is_readable($block . '/library.json')) {
                $missing[] = basename($block);
            }
        }

        self::assertSame([], $missing, 'Content elements without library.json: ' . implode(', ', $missing));
    }

    public function testEveryTopLevelKeyResolvesToADeclaredField(): void
    {
        $unknown = [];
        foreach ($this->libraryFixtures() as $name => $fixture) {
            $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig(
                $this->parseConfig(self::CONTENT_BLOCKS_DIR . '/' . $name . '/config.yaml')
            );

            foreach (array_keys($fixture) as $key) {
                if (str_starts_with((string)$key, '_')) {
                    continue;
                }
                if (isset($definition['fields'][$key]) || isset($definition['collections'][$key])) {
                    continue;
                }
                $unknown[] = $name . '.' . $key;
            }
        }

        // A key the resolver cannot map is silently dropped, so the element
        // would seed generated filler and nobody would notice.
        self::assertSame([], $unknown, 'library.json keys that match no field: ' . implode(', ', $unknown));
    }

    public function testNoTwoElementsShareAHeadline(): void
    {
        $byHeadline = [];
        foreach ($this->libraryFixtures() as $name => $fixture) {
            foreach (self::HEADLINE_FIELDS as $field) {
                $value = $fixture[$field] ?? null;
                if (is_string($value) && trim($value) !== '') {
                    $byHeadline[trim($value)][] = $name;
                    break;
                }
            }
        }

        $duplicates = [];
        foreach ($byHeadline as $headline => $elements) {
            if (count($elements) > 1) {
                $duplicates[] = sprintf('"%s" (%s)', $headline, implode(', ', $elements));
            }
        }

        self::assertSame([], $duplicates, 'Headlines reused across elements: ' . implode(' | ', $duplicates));
    }

    public function testDemoContentNeverMentionsTheDesignSystemOrTheRetiredFillerCopy(): void
    {
        $offenders = [];
        foreach ($this->libraryFixtures() as $name => $fixture) {
            // Values only, never keys. `feature-list` legitimately declares a
            // field called `shadcn_layout`, and scanning the encoded document
            // would ban authors from setting a field the element actually has.
            $values = [];
            array_walk_recursive($fixture, static function (mixed $value) use (&$values): void {
                if (is_string($value)) {
                    $values[] = $value;
                }
            });
            $haystack = implode("\n", $values);

            foreach (self::BANNED_SUBSTRINGS as $banned) {
                if (stripos($haystack, $banned) !== false) {
                    $offenders[] = $name . ': ' . $banned;
                }
            }
        }

        self::assertSame([], $offenders, 'Banned demo copy: ' . implode(', ', $offenders));
    }

    public function testReferencedFilesExistAndHaveGloballyUniqueBasenames(): void
    {
        $missing = [];
        $byBasename = [];

        foreach ($this->libraryFixtures() as $name => $fixture) {
            foreach ($this->collectFileReferences($fixture) as $file) {
                $absolute = __DIR__ . '/../../' . $file;
                if (!is_readable($absolute)) {
                    $missing[] = $name . ': ' . $file;
                    continue;
                }
                $byBasename[basename($file)][$file] = true;
            }
        }

        self::assertSame([], $missing, 'library.json references files that do not exist: ' . implode(', ', $missing));

        // ExtensionFalSeeder imports every asset by basename into ONE flat
        // fileadmin folder and short-circuits on a name it already imported, so
        // two different files sharing a basename would silently collapse into
        // whichever was seeded first.
        $collisions = [];
        foreach ($byBasename as $basename => $paths) {
            if (count($paths) > 1) {
                $collisions[] = $basename . ' <- ' . implode(', ', array_keys($paths));
            }
        }
        self::assertSame([], $collisions, 'Basename collisions in the flat FAL folder: ' . implode(' | ', $collisions));
    }

    public function testSelectValuesAndIconsAreDeclared(): void
    {
        $invalid = [];
        foreach ($this->libraryFixtures() as $name => $fixture) {
            $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig(
                $this->parseConfig(self::CONTENT_BLOCKS_DIR . '/' . $name . '/config.yaml')
            );

            foreach ($fixture as $key => $value) {
                $field = $definition['fields'][(string)$key] ?? null;
                if ($field === null || !is_string($value)) {
                    continue;
                }

                if (($field['type'] ?? '') === 'Select') {
                    $allowed = $this->selectValues($field);
                    if ($allowed !== [] && !in_array($value, $allowed, true)) {
                        $invalid[] = sprintf('%s.%s = "%s" (allowed: %s)', $name, $key, $value, implode('|', $allowed));
                    }
                    continue;
                }

                if (str_contains((string)$key, 'icon') && $value !== '' && !in_array($value, IconRegistry::keys(), true)) {
                    $invalid[] = sprintf('%s.%s = "%s" is not a registered icon', $name, $key, $value);
                }
            }
        }

        self::assertSame([], $invalid, implode(' | ', $invalid));
    }

    public function testCollectionsStayWithinTheirDeclaredMaximum(): void
    {
        $overflow = [];
        foreach ($this->libraryFixtures() as $name => $fixture) {
            $definition = ContentBlockDefinitionRegistry::buildDefinitionFromConfig(
                $this->parseConfig(self::CONTENT_BLOCKS_DIR . '/' . $name . '/config.yaml')
            );

            foreach ($definition['collections'] as $identifier => $collection) {
                $items = $fixture[$identifier] ?? null;
                $maximum = $collection['maxItems'] ?? null;
                if (!is_array($items) || !is_int($maximum) || count($items) <= $maximum) {
                    continue;
                }
                $overflow[] = sprintf('%s.%s has %d items, max %d', $name, $identifier, count($items), $maximum);
            }
        }

        self::assertSame([], $overflow, implode(' | ', $overflow));
    }

    /**
     * Headroom here is genuinely thin — the longest German headline is 56 of 60
     * characters and the longest eyebrow 23 of 24 — so the next edit in either
     * language breaks a layout unless something checks. Both languages are
     * covered because German runs ~15% longer than English and is where this
     * fails first.
     */
    public function testHeadlinesAndEyebrowsStayWithinTheirLayoutBudget(): void
    {
        $overLong = [];
        foreach (['', 'de'] as $locale) {
            foreach ($this->libraryFixtures($locale) as $name => $fixture) {
                $label = $name . ($locale === '' ? '' : ' [' . $locale . ']');

                foreach (self::HEADLINE_FIELDS as $field) {
                    $value = $fixture[$field] ?? null;
                    if (is_string($value) && mb_strlen($value) > 60) {
                        $overLong[] = sprintf('%s.%s is %d chars (max 60)', $label, $field, mb_strlen($value));
                    }
                }
                $eyebrow = $fixture['eyebrow'] ?? null;
                if (is_string($eyebrow) && mb_strlen($eyebrow) > 24) {
                    $overLong[] = sprintf('%s.eyebrow is %d chars (max 24)', $label, mb_strlen($eyebrow));
                }
            }
        }

        self::assertSame([], $overLong, implode(' | ', $overLong));
    }

    public function testGermanVariantsCoverTheSameFieldsAsTheSourceLanguage(): void
    {
        $german = $this->libraryFixtures('de');
        if ($german === []) {
            self::markTestSkipped('No library.de.json files present yet.');
        }

        $source = $this->libraryFixtures();
        $drift = [];
        foreach ($german as $name => $fixture) {
            $expected = array_keys($source[$name] ?? []);
            $actual = array_keys($fixture);
            sort($expected);
            sort($actual);
            if ($expected !== $actual) {
                $drift[] = $name . ' (+' . implode(',', array_diff($actual, $expected)) . ' -' . implode(',', array_diff($expected, $actual)) . ')';
            }
        }

        // A German file that omits a field silently falls back to nothing for
        // that field - the whole file is chosen or not, there is no per-key
        // merge - so the shapes have to match.
        self::assertSame([], $drift, 'library.de.json field drift: ' . implode(' | ', $drift));
    }

    /**
     * @param array<string, mixed> $fixture
     * @return list<string>
     */
    private function collectFileReferences(array $fixture): array
    {
        $files = [];
        array_walk_recursive($fixture, static function (mixed $value, int|string $key) use (&$files): void {
            if ($key === 'file' && is_string($value) && $value !== '') {
                $files[] = $value;
            }
        });

        return $files;
    }

    /**
     * @param array<string, mixed> $field
     * @return list<string>
     */
    private function selectValues(array $field): array
    {
        $items = $field['items'] ?? [];
        if (!is_array($items)) {
            return [];
        }

        $values = [];
        foreach ($items as $item) {
            if (is_string($item)) {
                $values[] = $item;
            } elseif (is_array($item) && isset($item['value']) && is_scalar($item['value'])) {
                $values[] = (string)$item['value'];
            }
        }

        return $values;
    }

    public function testEveryDemoCastMemberHasAMatchingPortrait(): void
    {
        $generator = new \Webconsulting\Desiderio\Seeding\ElementLibraryValueGenerator();
        $people = (new \ReflectionClass($generator))->getMethod('demoPeople')->invoke($generator);
        self::assertIsArray($people);

        $found = glob(self::LIBRARY_ASSET_DIR . '/lib-portrait-*');
        $portraits = $found === false ? [] : $found;
        natsort($portraits);
        $portraits = array_values($portraits);

        // The generator picks a name with `$index % count($people)` and
        // LibraryImageAssetProvider picks a face with `$index % count($pool)`.
        // Different counts desynchronise name and face, which is exactly the
        // "man's name over a woman's photo" defect this pairing prevents.
        self::assertCount(
            count($people),
            $portraits,
            'The demo cast and the portrait pool must be the same size, or names and faces drift apart.'
        );

        foreach ($people as $index => $person) {
            self::assertIsArray($person);
            $fullName = is_string($person[0] ?? null) ? $person[0] : '';
            $slug = strtolower((string)preg_replace('/[^a-z]+/i', '-', $fullName));
            $expected = sprintf('lib-portrait-%02d-%s-', $index + 1, $slug);
            self::assertStringStartsWith(
                $expected,
                basename($portraits[$index]),
                sprintf('Portrait %d does not belong to %s.', $index + 1, $fullName)
            );
        }
    }
}
