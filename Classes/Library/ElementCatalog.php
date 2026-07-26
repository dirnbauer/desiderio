<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * Catalog of all Content Blocks content elements shipped by Desiderio and,
 * when installed, Innesto. Source of truth for the element library seeder
 * and the JSON endpoint consumed by frontend pickers (visual editor).
 *
 * Title and description come from each element's config.yaml and are
 * localized through the element's language/labels.xlf when a translation
 * for the requested language exists.
 *
 * Two views exist on the same catalog:
 *  - getElements() returns the FULL records (parsed config + demo fixture) and
 *    is only used by the CLI seeder, where parsing every file is acceptable.
 *  - getElementMetadata() returns the LIGHT records the frontend picker needs
 *    (title/description/group/icon/config keywords, no config, no fixture) and
 *    is persistently cached, because building it parses ~244 config.yaml files
 *    and that ran on every picker open. See getElementMetadata() for the
 *    cache/invalidation.
 */
final class ElementCatalog
{
    /**
     * Host extensions shipped by us. Further providers register themselves in
     * their ext_localconf.php; see getHostExtensions().
     */
    private const HOST_EXTENSIONS = ['desiderio', 'innesto'];

    /**
     * Cache holding the built picker metadata. Registered in ext_localconf.php
     * (group "system"), so a normal "flush all caches" clears it; the cache key
     * additionally fingerprints every config.yaml mtime, so edits self-invalidate.
     */
    private const METADATA_CACHE_IDENTIFIER = 'desiderio_library';
    private const METADATA_CACHE_VERSION = 'metadata-v3';
    private const SEARCH_FINGERPRINT_VERSION = 'config-keywords-v1';

    /** @var array<string, list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, keywords: list<string>, vendor: string, config: array<string, mixed>, fixture: array<string, mixed>, libraryFixture: array<string, mixed>}>> keyed by locale ('' = source language) */
    private array $elements = [];

    /** @var list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, keywords: list<string>, iconUrl: string}>|null */
    private ?array $metadata = null;

    public function __construct(
        private readonly CacheManager $cacheManager,
    ) {}

    /**
     * Full catalog records including the parsed config, the styleguide fixture
     * and the element library's own demo content. Used by the seeder; reads
     * three files per element, so do not call it on a hot path - the frontend
     * picker uses getElementMetadata() instead.
     *
     * `fixture` is the styleguide payload (fixture.json): Desiderio's own
     * marketing copy, used to build the styleguide and starter sites.
     * `libraryFixture` is the element library payload (library.json): the demo
     * content an editor sees in the picker and copies into their page. The two
     * have different jobs and are deliberately separate files.
     *
     * @param string $locale Optional language key ('de', 'es', …). When set, a
     *                       `library.<locale>.json` is preferred over
     *                       `library.json`; missing files fall back to the
     *                       source language. Only the library payload is
     *                       localised - the styleguide fixture is not.
     * @return list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, keywords: list<string>, vendor: string, config: array<string, mixed>, fixture: array<string, mixed>, libraryFixture: array<string, mixed>}>
     */
    public function getElements(string $locale = ''): array
    {
        $locale = $this->normalizeLocale($locale);
        if (isset($this->elements[$locale])) {
            return $this->elements[$locale];
        }

        $elements = [];
        foreach ($this->scanContentElementConfigs() as $entry) {
            $config = $entry['config'];
            $directory = dirname($entry['configPath']);

            $fixture = $this->readJsonFile($directory . '/fixture.json');

            // Prefer the localised library payload, fall back to the source
            // language. A partial payload is fine: StyleguideFixtureResolver
            // completes every absent or empty field from the demo generator.
            $libraryFixture = [];
            if ($locale !== '') {
                $libraryFixture = $this->readJsonFile($directory . '/library.' . $locale . '.json');
            }
            if ($libraryFixture === []) {
                $libraryFixture = $this->readJsonFile($directory . '/library.json');
            }

            $title = $config['title'] ?? null;
            $description = $config['description'] ?? null;
            $group = $config['group'] ?? null;
            $elements[] = [
                'cType' => $this->resolveCType($entry['hostExtension'], $entry['name'], $config),
                'name' => $entry['name'],
                'hostExtension' => $entry['hostExtension'],
                'title' => is_string($title) && $title !== '' ? $title : $entry['name'],
                'description' => is_string($description) ? $description : '',
                'group' => is_string($group) && $group !== '' ? $group : 'default',
                'keywords' => $this->normalizeStringList($config['keywords'] ?? []),
                'vendor' => $entry['vendor'],
                'config' => $config,
                'fixture' => $fixture,
                'libraryFixture' => $libraryFixture,
            ];
        }

        foreach (CoreContentElements::available() as $core) {
            $elements[] = [
                'cType' => $core['cType'],
                'name' => $core['name'],
                'hostExtension' => CoreContentElements::HOST,
                'title' => $core['name'],
                'description' => '',
                'group' => $core['group'],
                'keywords' => [],
                'vendor' => CoreContentElements::HOST,
                'config' => [],
                'fixture' => $core['fixture'],
                // Native CTypes seed from their manifest fixture, which already
                // carries purpose-written demo content.
                'libraryFixture' => [],
            ];
        }

        usort($elements, static fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

        $this->elements[$locale] = $elements;
        return $elements;
    }

    /**
     * A locale key names a `library.<locale>.json` sibling, so keep it to the
     * shape TYPO3 language keys use and reject anything that could escape the
     * element directory.
     */
    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(trim($locale));
        return preg_match('/^[a-z]{2}(-[a-z]{2})?$/', $locale) === 1 ? $locale : '';
    }

    /** @return array<string, mixed> */
    private function readJsonFile(string $path): array
    {
        if (!is_readable($path)) {
            return [];
        }
        $decoded = json_decode((string)file_get_contents($path), true);
        if (!is_array($decoded)) {
            return [];
        }
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * Lightweight catalog metadata for the frontend element picker: one entry
     * per element with title/description/group/icon and config keywords, but
     * WITHOUT the parsed config or demo fixture (which only the seeder needs and
     * which made the per-request build read ~244 extra JSON files for nothing).
     *
     * Persistently cached: building this list parses ~244 config.yaml files,
     * which dominated the picker endpoint's response time when it ran on every
     * open. The cache key fingerprints every config.yaml's path + mtime, so
     * adding, removing or editing an element rebuilds it automatically; a normal
     * "flush all caches" (cache group "system") also clears it.
     *
     * @return list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, keywords: list<string>, iconUrl: string}>
     */
    public function getElementMetadata(): array
    {
        if ($this->metadata !== null) {
            return $this->metadata;
        }

        // A cache misconfiguration (not registered, missing table, unwritable
        // dir, …) must only ever slow the picker, never break it: read and write
        // are best-effort, and a cache failure falls through to an uncached build.
        // buildMetadata() runs outside the try so its own errors still surface.
        $cache = null;
        $cacheKey = '';
        try {
            $cache = $this->cacheManager->getCache(self::METADATA_CACHE_IDENTIFIER);
            $cacheKey = self::METADATA_CACHE_VERSION . '-' . $this->computeFingerprint();
            $cached = $cache->get($cacheKey);
            if (is_array($cached)) {
                /** @var list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, keywords: list<string>, iconUrl: string}> $cached */
                return $this->metadata = $cached;
            }
        } catch (\Throwable) {
            $cache = null;
        }

        $metadata = $this->buildMetadata();

        if ($cache !== null) {
            try {
                // lifetime 0 = keep until the cache group is flushed; the
                // fingerprint key already self-invalidates on any config change.
                $cache->set($cacheKey, $metadata, [], 0);
            } catch (\Throwable) {
                // best-effort write; serving uncached this once is fine
            }
        }

        return $this->metadata = $metadata;
    }

    /**
     * Localized title/description for one catalog element.
     *
     * @param array{cType: string, name: string, hostExtension: string, title: string, description: string} $element
     * @return array{title: string, description: string}
     */
    public function localizeElement(array $element, LanguageService $languageService): array
    {
        // Core (native) elements have no Content Block labels.xlf; their title and
        // description live in the shared library_core.xlf, keyed by bare cType.
        if ($element['hostExtension'] === CoreContentElements::HOST) {
            $coreFile = 'LLL:EXT:desiderio/Resources/Private/Language/library_core.xlf:';
            $title = $languageService->sL($coreFile . $element['cType'] . '.title');
            $description = $languageService->sL($coreFile . $element['cType'] . '.description');

            return [
                'title' => $title !== '' ? $title : $element['title'],
                'description' => $description !== '' ? $description : $element['description'],
            ];
        }

        $labelsFile = 'LLL:EXT:' . $element['hostExtension'] . '/ContentBlocks/ContentElements/'
            . $element['name'] . '/language/labels.xlf:';
        $title = $languageService->sL($labelsFile . 'title');
        $description = $languageService->sL($labelsFile . 'description');

        return [
            'title' => $title !== '' ? $title : $element['title'],
            'description' => $description !== '' ? $description : $element['description'],
        ];
    }

    /**
     * Localized search/display keywords for one catalog element. The keyword file
     * mirrors library_short.xlf (XLIFF 2.0, unit id = cType), but its value packs
     * two delimited groups: "primary || synonyms", each a " | "-separated list.
     * The first ~10 primary terms are shown on the card (ranked, most important
     * first); the synonyms are extra search terms shown only in the detail view.
     * Falls back to config.yaml keywords or, for older external Content Blocks,
     * derived keywords when an element has no authored XLF keyword unit.
     *
     * @param array{cType: string, hostExtension: string, name?: string, title?: string, description?: string, group?: string, keywords?: list<string>} $element
     * @return array{keywords: list<string>, synonyms: list<string>}
     */
    public function localizeKeywords(array $element, LanguageService $languageService): array
    {
        // Core elements carry no own extension; their keyword units live in
        // Desiderio's library_keywords.xlf, keyed by the bare core cType.
        $hostExtension = $element['hostExtension'] === CoreContentElements::HOST
            ? 'desiderio'
            : $element['hostExtension'];
        $file = 'LLL:EXT:' . $hostExtension . '/Resources/Private/Language/library_keywords.xlf:';
        $raw = $languageService->sL($file . $element['cType']);
        if ($raw === '') {
            return [
                'keywords' => $this->fallbackKeywords($element),
                'synonyms' => [],
            ];
        }
        [$primary, $synonyms] = array_pad(explode(' || ', $raw, 2), 2, '');
        $split = static function (string $value): array {
            if (trim($value) === '') {
                return [];
            }
            return array_values(array_filter(
                array_map('trim', explode(' | ', $value)),
                static fn(string $term): bool => $term !== '',
            ));
        };
        return ['keywords' => $split($primary), 'synonyms' => $split($synonyms)];
    }

    /**
     * @param list<array{group: string}> $elements
     * @return list<string>
     */
    public function getCategories(array $elements): array
    {
        $categories = [];
        foreach ($elements as $element) {
            $categories[$element['group']] = true;
        }
        $categories = array_keys($categories);
        sort($categories);
        return $categories;
    }

    /**
     * Web path of the published wizard icon (content-blocks publishes each
     * element's assets to EXT:<host>/Resources/Public/ContentBlocks/<vendor>/<name>).
     *
     * @param array{name: string, hostExtension: string, vendor?: string} $element
     */
    public function getIconWebPath(array $element): string
    {
        return $this->resolveIconWebPath(
            $element['hostExtension'],
            is_string($element['vendor'] ?? null) ? $element['vendor'] : '',
            $element['name'],
        );
    }

    /**
     * Every extension whose ContentBlocks/ContentElements directory feeds the
     * catalog: the ones we ship plus any provider that registered itself with
     *
     *   $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['desiderio']['libraryHostExtensions'][] = 'my_ext';
     *
     * in its ext_localconf.php. A provider only needs the per-element file
     * contract (config.yaml with an explicit typeName, library.json, labels.xlf,
     * assets/icon.svg) — see Documentation/Developer/AddingContentElements.rst.
     *
     * Deliberately not derived from the Content Blocks registry: that API is
     * marked @internal, and auto-ingesting every content-block-shipping
     * extension would fill the picker with elements that carry no demo content,
     * keywords or descriptions. Hosting stays opt-in.
     *
     * @return list<string>
     */
    private function getHostExtensions(): array
    {
        return self::hostExtensions();
    }

    /**
     * The host list, reachable without an instance.
     *
     * ContentBlockDefinitionRegistry needs the same list to know which
     * extensions' elements exist at all, and duplicating the lookup is how the
     * two drift apart.
     *
     * @return list<string>
     */
    public static function hostExtensions(): array
    {
        $registered = $GLOBALS['TYPO3_CONF_VARS'] ?? null;
        foreach (['EXTENSIONS', 'desiderio', 'libraryHostExtensions'] as $key) {
            $registered = is_array($registered) ? ($registered[$key] ?? null) : null;
        }

        $hosts = self::HOST_EXTENSIONS;
        foreach (is_array($registered) ? $registered : [] as $host) {
            if (is_string($host) && trim($host) !== '') {
                $hosts[] = trim($host);
            }
        }

        return array_values(array_unique($hosts));
    }

    /**
     * Scans the content element directories of every loaded host extension and
     * parses each config.yaml. This is the expensive step (YAML parsing) shared
     * by both catalog views; callers add what they need (fixture, icon, …).
     *
     * @return list<array{name: string, hostExtension: string, vendor: string, configPath: string, config: array<string, mixed>}>
     */
    private function scanContentElementConfigs(): array
    {
        $entries = [];
        foreach ($this->getHostExtensions() as $hostExtension) {
            if (!ExtensionManagementUtility::isLoaded($hostExtension)) {
                continue;
            }
            $basePath = GeneralUtility::getFileAbsFileName('EXT:' . $hostExtension . '/ContentBlocks/ContentElements');
            if ($basePath === '' || !is_dir($basePath)) {
                continue;
            }
            $directories = scandir($basePath);
            if ($directories === false) {
                continue;
            }
            foreach ($directories as $directory) {
                if ($directory === '.' || $directory === '..') {
                    continue;
                }
                $configPath = $basePath . '/' . $directory . '/config.yaml';
                if (!is_readable($configPath)) {
                    continue;
                }
                $config = Yaml::parseFile($configPath);
                if (!is_array($config)) {
                    continue;
                }
                /** @var array<string, mixed> $config */
                $entries[] = [
                    'name' => $directory,
                    'hostExtension' => $hostExtension,
                    'vendor' => $this->resolveVendor($hostExtension, $config),
                    'configPath' => $configPath,
                    'config' => $config,
                ];
            }
        }
        return $entries;
    }

    /**
     * Builds the lightweight picker metadata (no config, no fixture) from the
     * parsed configs. Sorted by the default-language title, matching getElements().
     *
     * @return list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, keywords: list<string>, iconUrl: string}>
     */
    private function buildMetadata(): array
    {
        $metadata = [];
        foreach ($this->scanContentElementConfigs() as $entry) {
            $config = $entry['config'];
            $title = $config['title'] ?? null;
            $description = $config['description'] ?? null;
            $group = $config['group'] ?? null;
            $metadata[] = [
                'cType' => $this->resolveCType($entry['hostExtension'], $entry['name'], $config),
                'name' => $entry['name'],
                'hostExtension' => $entry['hostExtension'],
                'title' => is_string($title) && $title !== '' ? $title : $entry['name'],
                'description' => is_string($description) ? $description : '',
                'group' => is_string($group) && $group !== '' ? $group : 'default',
                'keywords' => $this->normalizeStringList($config['keywords'] ?? []),
                'iconUrl' => $this->resolveIconWebPath($entry['hostExtension'], $entry['vendor'], $entry['name']),
            ];
        }

        foreach (CoreContentElements::available() as $core) {
            $metadata[] = [
                'cType' => $core['cType'],
                'name' => $core['name'],
                'hostExtension' => CoreContentElements::HOST,
                'title' => $core['name'],
                'description' => '',
                'group' => $core['group'],
                'keywords' => [],
                'iconUrl' => $this->resolveCoreIconWebPath($core['iconSlug']),
            ];
        }

        usort($metadata, static fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

        return $metadata;
    }

    /**
     * Cheap content-of-the-catalog fingerprint: every config.yaml's path + mtime.
     * Only stats files (no YAML parsing), so it is fast to recompute on a cache
     * hit; the result keys the metadata cache so any edit/add/remove of an
     * element rebuilds the metadata without a manual flush.
     */
    private function computeFingerprint(): string
    {
        $hostExtensions = $this->getHostExtensions();
        // Part of the fingerprint itself: registering or removing a provider
        // changes the catalog even when no config.yaml mtime moved.
        $parts = ['hosts:' . implode(',', $hostExtensions)];
        foreach ($hostExtensions as $hostExtension) {
            if (!ExtensionManagementUtility::isLoaded($hostExtension)) {
                continue;
            }
            $basePath = GeneralUtility::getFileAbsFileName('EXT:' . $hostExtension . '/ContentBlocks/ContentElements');
            if ($basePath === '' || !is_dir($basePath)) {
                continue;
            }
            $directories = scandir($basePath);
            if ($directories === false) {
                continue;
            }
            foreach ($directories as $directory) {
                if ($directory === '.' || $directory === '..') {
                    continue;
                }
                $configPath = $basePath . '/' . $directory . '/config.yaml';
                $mtime = @filemtime($configPath);
                if ($mtime !== false) {
                    $parts[] = $configPath . ':' . $mtime;
                }
            }
        }
        // Core elements are defined in PHP + library_core.xlf, not config.yaml, so
        // include their mtimes too — editing a core definition or its labels must
        // self-invalidate the cached picker metadata just like a Content Block edit.
        foreach ([
            'EXT:desiderio/Classes/Library/CoreContentElements.php',
            'EXT:desiderio/Resources/Private/Language/library_core.xlf',
            'EXT:desiderio/Resources/Private/Language/de.library_core.xlf',
        ] as $corePath) {
            $mtime = @filemtime(GeneralUtility::getFileAbsFileName($corePath));
            if ($mtime !== false) {
                $parts[] = $corePath . ':' . $mtime;
            }
        }
        sort($parts);
        return md5(implode('|', $parts));
    }

    /**
     * Fingerprint for the per-language search index cache: the catalog config
     * fingerprint plus every library_keywords.xlf path + mtime. Editing a keyword
     * file (not just a config.yaml) thus self-invalidates the cached search index,
     * exactly as computeFingerprint() does for the picker metadata.
     */
    public function getSearchFingerprint(): string
    {
        $parts = [self::SEARCH_FINGERPRINT_VERSION, $this->computeFingerprint()];
        foreach ($this->getHostExtensions() as $hostExtension) {
            if (!ExtensionManagementUtility::isLoaded($hostExtension)) {
                continue;
            }
            foreach (['library_keywords.xlf', 'de.library_keywords.xlf'] as $file) {
                $path = GeneralUtility::getFileAbsFileName(
                    'EXT:' . $hostExtension . '/Resources/Private/Language/' . $file,
                );
                $mtime = @filemtime($path);
                if ($mtime !== false) {
                    $parts[] = $path . ':' . $mtime;
                }
            }
        }
        return md5(implode('|', $parts));
    }

    /**
     * @param array<string, mixed> $config
     */
    private function resolveCType(string $hostExtension, string $directory, array $config): string
    {
        $configuredTypeName = $config['typeName'] ?? null;
        return is_string($configuredTypeName) && $configuredTypeName !== ''
            ? $configuredTypeName
            : $hostExtension . '_' . str_replace('-', '', $directory);
    }

    /**
     * @param array{cType: string, name?: string, title?: string, description?: string, group?: string, keywords?: list<string>} $element
     * @return list<string>
     */
    private function fallbackKeywords(array $element): array
    {
        $keywords = $this->normalizeStringList($element['keywords'] ?? []);
        if ($keywords !== []) {
            return $keywords;
        }

        $terms = [];
        $add = static function (string $term) use (&$terms): void {
            $term = trim(strtolower(str_replace(['_', '-'], ' ', $term)));
            $term = (string)preg_replace('/\s+/', ' ', $term);
            if ($term === '' || isset($terms[$term])) {
                return;
            }
            $terms[$term] = true;
        };

        $title = $element['title'] ?? '';
        $add($title);

        $cType = $element['cType'];
        $identifier = $element['name'] ?? (string)preg_replace('/^[a-z0-9]+_/', '', $cType);
        $add($identifier);

        $group = $element['group'] ?? '';
        if ($group !== '' && $group !== 'default') {
            $add($group);
        }

        $description = $element['description'] ?? '';
        $context = strtolower(implode(' ', array_filter([
            $title,
            $description,
            $cType,
            $group,
        ], static fn(string $value): bool => $value !== '')));

        foreach ($this->keywordHintsForContext($context) as $hint) {
            $add($hint);
        }

        return array_slice(array_keys($terms), 0, 10);
    }

    /**
     * @return list<string>
     */
    private function keywordHintsForContext(string $context): array
    {
        $hints = [];

        foreach ([
            [str_contains($context, 'case stud'), ['case studies', 'customer success', 'social proof', 'metrics']],
            [str_contains($context, 'marquee'), ['marquee', 'ticker', 'scrolling', 'motion']],
            [str_contains($context, 'orbit'), ['orbiting circles', 'radial', 'animation', 'motion']],
            [str_contains($context, 'terminal'), ['terminal', 'console', 'command line', 'code']],
            [str_contains($context, 'stats') || str_contains($context, 'metric'), ['stats', 'metrics', 'kpi']],
            [str_contains($context, 'dashboard'), ['dashboard', 'overview']],
            [str_contains($context, 'progress'), ['progress', 'percentage', 'target']],
            [str_contains($context, 'usage'), ['usage', 'limits', 'resources']],
            [str_contains($context, 'breakdown'), ['breakdown', 'comparison']],
            [str_contains($context, 'badge'), ['badges', 'status']],
            [str_contains($context, 'link'), ['links', 'navigation']],
            [str_contains($context, 'trend'), ['trend', 'change']],
            [str_contains($context, 'area chart'), ['area chart', 'chart', 'data']],
            [str_contains($context, 'chart'), ['chart', 'data visualization']],
            [str_contains($context, 'card'), ['cards', 'grid']],
            [str_contains($context, 'border'), ['borders', 'comparison']],
        ] as [$condition, $terms]) {
            if (!$condition) {
                continue;
            }
            foreach ($terms as $term) {
                $hints[] = $term;
            }
        }

        return $hints;
    }

    /**
     * @param mixed $value
     * @return list<string>
     */
    private function normalizeStringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_values(array_filter(
            array_map(
                static fn(mixed $item): string => is_string($item) ? trim($item) : '',
                $value,
            ),
            static fn(string $item): bool => $item !== '',
        ));
    }

    /**
     * Content Blocks publishes an element's assets under the block's VENDOR
     * segment (the part before the slash in config.yaml's `name`), which is not
     * always the extension key. Fall back to the vendor-less path for exotic
     * publishing layouts.
     */
    private function resolveIconWebPath(string $hostExtension, string $vendor, string $name): string
    {
        $candidates = [];
        if ($vendor !== '') {
            $candidates[] = 'EXT:' . $hostExtension . '/Resources/Public/ContentBlocks/' . $vendor . '/' . $name . '/icon.svg';
        }
        $candidates[] = 'EXT:' . $hostExtension . '/Resources/Public/ContentBlocks/' . $name . '/icon.svg';

        foreach ($candidates as $publicPath) {
            if (!is_file(GeneralUtility::getFileAbsFileName($publicPath))) {
                continue;
            }
            try {
                return PathUtility::getPublicResourceWebPath($publicPath);
            } catch (\Throwable) {
                return '';
            }
        }

        return '';
    }

    /**
     * Vendor segment of a Content Block name (`<vendor>/<element>`), used for the
     * published asset path. Falls back to the extension key, which is what the
     * blocks we ship use.
     *
     * @param array<string, mixed> $config
     */
    private function resolveVendor(string $hostExtension, array $config): string
    {
        $name = $config['name'] ?? null;
        if (is_string($name) && str_contains($name, '/')) {
            return explode('/', $name, 2)[0];
        }
        return $hostExtension;
    }

    /**
     * Web path of a core element's Desiderio-shipped wizard icon
     * (Resources/Public/Icons/ContentElements/core-<slug>.svg). Mirrors
     * resolveIconWebPath() but for the icons we author for native CTypes.
     */
    private function resolveCoreIconWebPath(string $iconSlug): string
    {
        $publicPath = 'EXT:desiderio/Resources/Public/Icons/ContentElements/core-' . $iconSlug . '.svg';
        if (!is_file(GeneralUtility::getFileAbsFileName($publicPath))) {
            return '';
        }
        try {
            return PathUtility::getPublicResourceWebPath($publicPath);
        } catch (\Throwable) {
            return '';
        }
    }
}
