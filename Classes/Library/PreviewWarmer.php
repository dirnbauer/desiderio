<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;

/**
 * "Prerenders" the element library: requests every seeded record's preview
 * URL once so the rendered output sits in the TYPO3 page cache before any
 * editor opens the picker.
 */
final class PreviewWarmer
{
    public function __construct(
        private readonly ConnectionPool $connectionPool,
        private readonly RequestFactory $requestFactory,
        private readonly PreviewUrlBuilder $previewUrlBuilder,
        private readonly SiteFinder $siteFinder,
    ) {}

    public function resolveSite(int $storagePid): Site
    {
        try {
            return $this->siteFinder->getSiteByPageId($storagePid);
        } catch (SiteNotFoundException $e) {
            throw new \RuntimeException(
                'No site found for element library folder ' . $storagePid . '. Seed below a configured site root.',
                1777200003,
                $e
            );
        }
    }

    /**
     * Every site whose picker reads the given library folder, i.e. whose
     * elementLibrary.storagePid setting points at $storagePid. Each such site
     * renders the previews from its OWN base + cHash, so the editor's preview
     * URLs (and therefore the page-cache entries) differ per site - warming one
     * base does not warm the others. Falls back to the folder-owning site when
     * no site references the folder explicitly.
     *
     * @return list<Site>
     */
    public function getSitesForLibraryFolder(int $storagePid): array
    {
        $sites = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            if ($this->configuredStoragePid($site) === $storagePid) {
                $sites[$site->getIdentifier()] = $site;
            }
        }
        if ($sites === []) {
            $owner = $this->resolveSite($storagePid);
            $sites[$owner->getIdentifier()] = $owner;
        }
        return array_values($sites);
    }

    /**
     * Every configured element library across all sites: the site and the
     * folder uid its picker reads. One entry per site (a site has exactly one
     * library folder); several sites may share a folder.
     *
     * @return list<array{site: Site, storagePid: int}>
     */
    public function getConfiguredLibraries(): array
    {
        $libraries = [];
        foreach ($this->siteFinder->getAllSites() as $site) {
            $storagePid = $this->configuredStoragePid($site);
            if ($storagePid > 0) {
                $libraries[] = ['site' => $site, 'storagePid' => $storagePid];
            }
        }
        return $libraries;
    }

    private function configuredStoragePid(Site $site): int
    {
        $configured = $site->getSettings()->get('elementLibrary.storagePid', 0);
        return is_numeric($configured) ? (int)$configured : 0;
    }

    /**
     * @return array<string, int> CType => tt_content uid of all live library records
     */
    public function getSeededRecords(int $storagePid): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tt_content');
        $rows = $queryBuilder
            ->select('uid', 'CType')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($storagePid, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)),
                $queryBuilder->expr()->eq('t3ver_wsid', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER))
            )
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $records = [];
        foreach ($rows as $row) {
            $cType = $row['CType'] ?? null;
            $uid = $row['uid'] ?? null;
            if (is_string($cType) && is_numeric($uid)) {
                $records[$cType] = (int)$uid;
            }
        }
        return $records;
    }

    /**
     * Warms the previews of a folder's records for one or more site bases. Each
     * site renders the same records from its own base, so a shared library
     * folder must be warmed once per site that uses it (see
     * getSitesForLibraryFolder()).
     *
     * @param list<Site>|null $sites Bases to warm; null = the folder-owning site only.
     * @param callable(string $cType, string $url, bool $success, string $siteIdentifier): void|null $onResult
     * @return array{warmed: int, failed: list<string>, sites: array<string, array{base: string, warmed: int, failed: int}>}
     */
    public function warm(int $storagePid, ?array $sites = null, ?callable $onResult = null): array
    {
        if ($sites === null || $sites === []) {
            $sites = [$this->resolveSite($storagePid)];
        }

        $records = $this->getSeededRecords($storagePid);
        $warmed = 0;
        $failed = [];
        $perSite = [];

        foreach ($sites as $site) {
            $identifier = $site->getIdentifier();
            $siteWarmed = 0;
            $siteFailed = 0;
            foreach ($records as $cType => $uid) {
                $url = $this->previewUrlBuilder->build($site, $uid);
                $success = false;
                try {
                    $response = $this->requestFactory->request($url, 'GET', [
                        'timeout' => 60,
                        'http_errors' => false,
                        'verify' => false,
                    ]);
                    $success = $response->getStatusCode() === 200;
                } catch (\Throwable) {
                    $success = false;
                }
                if ($success) {
                    $warmed++;
                    $siteWarmed++;
                } else {
                    $failed[] = $identifier . '/' . $cType . ' (' . $url . ')';
                    $siteFailed++;
                }
                if ($onResult !== null) {
                    $onResult($cType, $url, $success, $identifier);
                }
            }
            $perSite[$identifier] = [
                'base' => rtrim((string)$site->getBase(), '/') . '/',
                'warmed' => $siteWarmed,
                'failed' => $siteFailed,
            ];
        }

        return ['warmed' => $warmed, 'failed' => $failed, 'sites' => $perSite];
    }
}
