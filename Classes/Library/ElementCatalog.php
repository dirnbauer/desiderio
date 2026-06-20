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
 *    (title/description/group/icon, no config, no fixture) and is persistently
 *    cached, because building it parses ~255 config.yaml files and that ran on
 *    every picker open. See getElementMetadata() for the cache/invalidation.
 */
final class ElementCatalog
{
    private const HOST_EXTENSIONS = ['desiderio', 'innesto'];

    /**
     * Cache holding the built picker metadata. Registered in ext_localconf.php
     * (group "system"), so a normal "flush all caches" clears it; the cache key
     * additionally fingerprints every config.yaml mtime, so edits self-invalidate.
     */
    private const METADATA_CACHE_IDENTIFIER = 'desiderio_library';

    /** @var list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, config: array<string, mixed>, fixture: array<string, mixed>}>|null */
    private ?array $elements = null;

    /** @var list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, iconUrl: string}>|null */
    private ?array $metadata = null;

    public function __construct(
        private readonly CacheManager $cacheManager,
    ) {}

    /**
     * Full catalog records including the parsed config and demo fixture. Used by
     * the seeder; reads two files per element, so do not call it on a hot path -
     * the frontend picker uses getElementMetadata() instead.
     *
     * @return list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, config: array<string, mixed>, fixture: array<string, mixed>}>
     */
    public function getElements(): array
    {
        if ($this->elements !== null) {
            return $this->elements;
        }

        $elements = [];
        foreach ($this->scanContentElementConfigs() as $entry) {
            $config = $entry['config'];

            $fixture = [];
            $fixturePath = dirname($entry['configPath']) . '/fixture.json';
            if (is_readable($fixturePath)) {
                $decoded = json_decode((string)file_get_contents($fixturePath), true);
                if (is_array($decoded)) {
                    /** @var array<string, mixed> $decoded */
                    $fixture = $decoded;
                }
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
                'config' => $config,
                'fixture' => $fixture,
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
                'config' => [],
                'fixture' => $core['fixture'],
            ];
        }

        usort($elements, static fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

        $this->elements = $elements;
        return $elements;
    }

    /**
     * Lightweight catalog metadata for the frontend element picker: one entry
     * per element with title/description/group/icon, but WITHOUT the parsed
     * config or demo fixture (which only the seeder needs and which made the
     * per-request build read ~255 extra JSON files for nothing).
     *
     * Persistently cached: building this list parses ~255 config.yaml files,
     * which dominated the picker endpoint's response time when it ran on every
     * open. The cache key fingerprints every config.yaml's path + mtime, so
     * adding, removing or editing an element rebuilds it automatically; a normal
     * "flush all caches" (cache group "system") also clears it.
     *
     * @return list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, iconUrl: string}>
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
            $cacheKey = 'metadata-' . $this->computeFingerprint();
            $cached = $cache->get($cacheKey);
            if (is_array($cached)) {
                /** @var list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, iconUrl: string}> $cached */
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
     * Returns empty lists when an element has no authored keywords.
     *
     * @param array{cType: string, hostExtension: string} $element
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
            return ['keywords' => [], 'synonyms' => []];
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
     * element's assets to EXT:<host>/Resources/Public/ContentBlocks/<name>).
     *
     * @param array{name: string, hostExtension: string} $element
     */
    public function getIconWebPath(array $element): string
    {
        return $this->resolveIconWebPath($element['hostExtension'], $element['name']);
    }

    /**
     * Scans the content element directories of every loaded host extension and
     * parses each config.yaml. This is the expensive step (YAML parsing) shared
     * by both catalog views; callers add what they need (fixture, icon, …).
     *
     * @return list<array{name: string, hostExtension: string, configPath: string, config: array<string, mixed>}>
     */
    private function scanContentElementConfigs(): array
    {
        $entries = [];
        foreach (self::HOST_EXTENSIONS as $hostExtension) {
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
     * @return list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, iconUrl: string}>
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
                'iconUrl' => $this->resolveIconWebPath($entry['hostExtension'], $entry['name']),
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
        $parts = [];
        foreach (self::HOST_EXTENSIONS as $hostExtension) {
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
        $parts = [$this->computeFingerprint()];
        foreach (self::HOST_EXTENSIONS as $hostExtension) {
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

    private function resolveIconWebPath(string $hostExtension, string $name): string
    {
        $publicPath = 'EXT:' . $hostExtension . '/Resources/Public/ContentBlocks/' . $name . '/icon.svg';
        if (!is_file(GeneralUtility::getFileAbsFileName($publicPath))) {
            return '';
        }
        try {
            return PathUtility::getPublicResourceWebPath($publicPath);
        } catch (\Throwable) {
            return '';
        }
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
