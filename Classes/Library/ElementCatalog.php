<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use Symfony\Component\Yaml\Yaml;
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
 */
final class ElementCatalog
{
    private const HOST_EXTENSIONS = ['desiderio', 'innesto'];

    /** @var list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, config: array<string, mixed>, fixture: array<string, mixed>}>|null */
    private ?array $elements = null;

    /**
     * @return list<array{cType: string, name: string, hostExtension: string, title: string, description: string, group: string, config: array<string, mixed>, fixture: array<string, mixed>}>
     */
    public function getElements(): array
    {
        if ($this->elements !== null) {
            return $this->elements;
        }

        $elements = [];
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
                $configuredTypeName = $config['typeName'] ?? null;
                $cType = is_string($configuredTypeName) && $configuredTypeName !== ''
                    ? $configuredTypeName
                    : $hostExtension . '_' . str_replace('-', '', $directory);

                $fixture = [];
                $fixturePath = $basePath . '/' . $directory . '/fixture.json';
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
                    'cType' => $cType,
                    'name' => $directory,
                    'hostExtension' => $hostExtension,
                    'title' => is_string($title) && $title !== '' ? $title : $directory,
                    'description' => is_string($description) ? $description : '',
                    'group' => is_string($group) && $group !== '' ? $group : 'default',
                    'config' => $config,
                    'fixture' => $fixture,
                ];
            }
        }

        usort($elements, static fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));

        $this->elements = $elements;
        return $elements;
    }

    /**
     * Localized title/description for one catalog element.
     *
     * @param array{name: string, hostExtension: string, title: string, description: string} $element
     * @return array{title: string, description: string}
     */
    public function localizeElement(array $element, LanguageService $languageService): array
    {
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
        $publicPath = 'EXT:' . $element['hostExtension'] . '/Resources/Public/ContentBlocks/' . $element['name'] . '/icon.svg';
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
