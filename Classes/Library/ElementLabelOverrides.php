<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * One site's own words for catalog elements, from the site setting
 * `elementLibrary.labels`: an XLIFF file reference such as
 * `EXT:my_theme/Resources/Private/Language/Library/odera.xlf` (an `LLL:`
 * prefix is accepted too).
 *
 * The file holds units keyed `<cType>.<field>`:
 *
 *  - `<cType>.title`        card and preview title
 *  - `<cType>.description`  full description (enlarged preview)
 *  - `<cType>.short`        card blurb
 *  - `<cType>.keywords`     "primary || synonyms", each a " | "-separated list
 *                           (the library_keywords.xlf encoding)
 *  - `<cType>.group`        category; the category chips use it too
 *
 * Each unit that exists replaces the host's text; a missing or empty unit
 * keeps it. Lookups go through the caller's LanguageService, so translations
 * come from `<lang>.<file>` next to the file, exactly as for any XLIFF file.
 *
 * Pure apart from fingerprint(), which stats the file for the search index
 * cache key.
 */
final readonly class ElementLabelOverrides
{
    /** The unit suffixes, in the order relabel() applies them. */
    public const array FIELDS = ['title', 'description', 'short', 'keywords', 'group'];

    /** Normalized `EXT:…/<name>.xlf` reference, or '' when the site uses the host texts. */
    public string $file;

    public function __construct(string $file = '')
    {
        $this->file = self::normalizeFile($file);
    }

    public static function fromSite(Site $site): self
    {
        $configured = $site->getSettings()->get('elementLibrary.labels', '');

        return new self(is_string($configured) ? $configured : '');
    }

    public function isEmpty(): bool
    {
        return $this->file === '';
    }

    /**
     * The LLL reference of one override unit, or '' without a labels file.
     */
    public function reference(string $cType, string $field): string
    {
        return $this->file === '' ? '' : 'LLL:' . $this->file . ':' . $cType . '.' . $field;
    }

    /**
     * Replace the host's texts of one element with the site's own, unit by unit.
     *
     * @param array{title: string, description: string, short: string, keywords: list<string>, synonyms: list<string>, group: string} $texts the host's texts, already localized
     * @param \Closure(string): string $translate resolves an LLL reference in the editor's language; '' when the unit does not exist (LanguageService::sL())
     * @return array{title: string, description: string, short: string, keywords: list<string>, synonyms: list<string>, group: string}
     */
    public function relabel(string $cType, array $texts, \Closure $translate): array
    {
        if ($this->file === '') {
            return $texts;
        }

        foreach (self::FIELDS as $field) {
            $value = $translate($this->reference($cType, $field));
            if (trim($value) === '') {
                continue;
            }
            if ($field === 'keywords') {
                ['keywords' => $texts['keywords'], 'synonyms' => $texts['synonyms']] = ElementCatalog::parseKeywordUnit($value);
                continue;
            }
            $texts[$field] = trim($value);
        }

        return $texts;
    }

    /**
     * Cache-key part for anything built from these labels (the search index):
     * the file reference plus the modification time of the file and of its
     * translation in the given language, so two sites with different files
     * never share an entry and editing either file rebuilds it. '' without a
     * labels file, so sites on the host texts keep sharing one index.
     */
    public function fingerprint(string $langKey): string
    {
        if ($this->file === '') {
            return '';
        }

        return self::fingerprintFor($this->file, GeneralUtility::getFileAbsFileName($this->file), $langKey);
    }

    /**
     * fingerprint() for an already resolved absolute path; separate so it can
     * be tested without a booted extension registry.
     */
    public static function fingerprintFor(string $file, string $absolutePath, string $langKey): string
    {
        $paths = [$absolutePath];
        if ($absolutePath !== '' && $langKey !== '' && $langKey !== 'default') {
            $paths[] = dirname($absolutePath) . '/' . $langKey . '.' . basename($absolutePath);
        }

        $parts = ['labels:' . $file, 'lang:' . $langKey];
        foreach ($paths as $path) {
            $mtime = $path !== '' ? @filemtime($path) : false;
            $parts[] = $path . ':' . ($mtime === false ? 'missing' : (string)$mtime);
        }

        return md5(implode('|', $parts));
    }

    /**
     * Accepts `EXT:…/<name>.xlf` with or without the `LLL:` prefix and a
     * trailing colon; anything else (empty, a bare path, a non-XLIFF file)
     * means "no overrides".
     */
    public static function normalizeFile(string $file): string
    {
        $file = trim($file);
        if (str_starts_with($file, 'LLL:')) {
            $file = substr($file, 4);
        }
        $file = rtrim($file, ':');

        return str_starts_with($file, 'EXT:') && str_ends_with($file, '.xlf') ? $file : '';
    }
}
