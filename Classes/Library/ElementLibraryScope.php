<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\Utility\SiteSettings;

/**
 * Which catalog elements one site's picker offers, from its site settings:
 *
 *  - `elementLibrary.hosts` (comma separated extension keys, "core" = native
 *    TYPO3 content types): only elements of these providers. Empty = every
 *    installed provider.
 *  - `elementLibrary.onlySeeded` (bool): only elements that have a demo record
 *    in the site's library folder (`elementLibrary.storagePid`).
 *
 * Both restrictions apply together. The list endpoint filters its catalog with
 * filter(); the search endpoint passes allowedCTypes() to the search, so both
 * always offer the same set. Pure: the caller supplies the catalog and the
 * seeded records (PreviewWarmer::getSeededRecords()).
 */
final readonly class ElementLibraryScope
{
    /** @var list<string> lowercased extension keys; empty = every host */
    public array $hosts;

    /**
     * @param list<string> $hosts
     */
    public function __construct(array $hosts = [], public bool $onlySeeded = false)
    {
        $this->hosts = self::normalizeHosts($hosts);
    }

    public static function fromSite(Site $site): self
    {
        $configured = $site->getSettings()->get('elementLibrary.hosts', '');

        return new self(
            is_string($configured) ? explode(',', $configured) : [],
            SiteSettings::isEnabled($site, 'elementLibrary.onlySeeded'),
        );
    }

    /**
     * True when this site sees fewer elements than the whole catalog.
     */
    public function isRestricted(): bool
    {
        return $this->hosts !== [] || $this->onlySeeded;
    }

    /**
     * True when filter() needs the site's seeded records to decide.
     */
    public function needsSeededRecords(): bool
    {
        return $this->onlySeeded;
    }

    /**
     * The catalog entries this site lists, in catalog order.
     *
     * @template T of array{cType: string, hostExtension: string}
     * @param list<T> $catalog
     * @param array<string, int> $seededRecords cType => demo record uid in the site's library folder
     * @return list<T>
     */
    public function filter(array $catalog, array $seededRecords = []): array
    {
        if (!$this->isRestricted()) {
            return $catalog;
        }

        return array_values(array_filter(
            $catalog,
            fn(array $element): bool => $this->allows($element, $seededRecords),
        ));
    }

    /**
     * The cTypes the search may return for this site, or null when the site
     * sees the whole catalog (the search then skips narrowing its index).
     *
     * @param list<array{cType: string, hostExtension: string}> $catalog
     * @param array<string, int> $seededRecords cType => demo record uid in the site's library folder
     * @return list<string>|null
     */
    public function allowedCTypes(array $catalog, array $seededRecords = []): ?array
    {
        if (!$this->isRestricted()) {
            return null;
        }

        return array_map(
            static fn(array $element): string => $element['cType'],
            $this->filter($catalog, $seededRecords),
        );
    }

    /**
     * @param array{cType: string, hostExtension: string} $element
     * @param array<string, int> $seededRecords
     */
    private function allows(array $element, array $seededRecords): bool
    {
        if ($this->hosts !== [] && !in_array(strtolower($element['hostExtension']), $this->hosts, true)) {
            return false;
        }

        return !$this->onlySeeded || ($seededRecords[$element['cType']] ?? 0) > 0;
    }

    /**
     * @param list<string> $hosts
     * @return list<string>
     */
    private static function normalizeHosts(array $hosts): array
    {
        return array_values(array_unique(array_filter(
            array_map(static fn(string $host): string => strtolower(trim($host)), $hosts),
            static fn(string $host): bool => $host !== '',
        )));
    }
}
