<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Partial root paths that apply to a template, derived from its location.
 *
 * Generic rule: every ancestor directory that has a `Partials` sibling folder
 * (Templates/, Layouts/, Partials/ live side by side) contributes it.
 * Desiderio-specific contexts add the roots TypoScript configures on top:
 * content elements and classic content share the page partials, extension
 * template sets fall back to the partials of the extension they override.
 *
 * @internal used by TemplateLinter only
 */
final class PartialRootResolver
{
    /**
     * Template location prefix (relative to the extension root) => the local
     * partial directories it adds, plus the provider extensions whose partials
     * complete the set. Most specific prefix first: the first match wins.
     *
     * @var array<string, array{local: list<string>, external: array<string, string>}>
     */
    private const array CONTEXTS = [
        'Resources/Private/ClassicContent/' => ['local' => ['ClassicContent/Partials', 'Templates/Partials'], 'external' => []],
        'Resources/Private/Templates/' => ['local' => ['Templates/Partials'], 'external' => []],
        'Resources/Private/Presets/' => ['local' => ['Templates/Partials'], 'external' => []],
        'Resources/Private/ShadcnUi/' => ['local' => ['Templates/Partials'], 'external' => []],
        'Resources/Private/Extensions/News/' => ['local' => ['Extensions/News/Partials'], 'external' => ['news' => 'Resources/Private/Partials']],
        // Blog comment form: TYPO3 Form Framework partials (BlogCommentFormFactory).
        'Resources/Private/Extensions/Blog/Partials/Form/' => ['local' => ['Extensions/Blog/Partials/Form'], 'external' => ['form' => 'Resources/Private/Frontend/Partials']],
        'Resources/Private/Extensions/Blog/' => ['local' => ['Extensions/Blog/Partials'], 'external' => ['blog' => 'Resources/Private/Partials']],
        'Resources/Private/Extensions/Powermail/' => ['local' => ['Extensions/Powermail/Partials'], 'external' => ['powermail' => 'Resources/Private/Partials']],
        'Resources/Private/Solr/' => ['local' => ['Solr/Partials'], 'external' => ['solr' => 'Resources/Private/Partials']],
        'Resources/Private/Form/' => ['local' => ['Form/Partials', 'Form/CaptchaOverride/Partials'], 'external' => ['form' => 'Resources/Private/Frontend/Partials']],
        'ContentBlocks/' => ['local' => ['Templates/Partials', 'ClassicContent/Partials'], 'external' => []],
    ];

    private const array PARTIAL_SUFFIXES = ['.html', '.fluid.html', '.txt', '.fluid.txt', '.xml'];

    /**
     * @return array{roots: list<string>, missingExtensions: list<string>}
     */
    public function resolve(string $file): array
    {
        $roots = [];
        $missing = [];
        $directory = dirname($file);
        $extensionRoot = $this->findExtensionRoot($directory);
        for ($current = $directory; $current !== '' && $current !== '/' && $current !== dirname($current); $current = dirname($current)) {
            if ($extensionRoot !== null && !str_starts_with($current, $extensionRoot)) {
                break;
            }
            if (is_dir($current . '/Partials')) {
                $roots[] = $current . '/Partials';
            }
        }
        if ($extensionRoot !== null) {
            $private = $extensionRoot . '/Resources/Private/';
            $relative = substr($file, strlen($extensionRoot) + 1);
            foreach (self::CONTEXTS as $prefix => $context) {
                if (!str_starts_with($relative, $prefix)) {
                    continue;
                }
                foreach ($context['local'] as $localRoot) {
                    $roots[] = $private . $localRoot;
                }
                foreach ($context['external'] as $extensionKey => $subPath) {
                    if (ExtensionManagementUtility::isLoaded($extensionKey)) {
                        $roots[] = ExtensionManagementUtility::extPath($extensionKey) . $subPath;
                    } else {
                        $missing[] = $extensionKey;
                    }
                }
                break;
            }
        }

        return [
            'roots' => array_values(array_unique(array_filter($roots, is_dir(...)))),
            'missingExtensions' => $missing,
        ];
    }

    /**
     * @param list<string> $roots
     */
    public function partialExists(string $name, array $roots): bool
    {
        foreach ($roots as $root) {
            foreach (self::PARTIAL_SUFFIXES as $suffix) {
                if (is_file(rtrim($root, '/') . '/' . $name . $suffix)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A path relative to the extension it belongs to, for readable messages.
     */
    public function shortenPath(string $path): string
    {
        $extensionRoot = $this->findExtensionRoot($path);

        return $extensionRoot === null
            ? $path
            : basename($extensionRoot) . '/' . ltrim(substr($path, strlen($extensionRoot)), '/');
    }

    public function findExtensionRoot(string $directory): ?string
    {
        for ($current = $directory; $current !== '' && $current !== '/' && $current !== dirname($current); $current = dirname($current)) {
            if (is_file($current . '/ext_emconf.php') || (is_file($current . '/composer.json') && is_dir($current . '/Resources'))) {
                return $current;
            }
        }

        return null;
    }
}
