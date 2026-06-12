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
     * @param callable(string $cType, string $url, bool $success): void|null $onResult
     * @return array{warmed: int, failed: list<string>}
     */
    public function warm(int $storagePid, ?callable $onResult = null): array
    {
        $site = $this->resolveSite($storagePid);
        $warmed = 0;
        $failed = [];

        foreach ($this->getSeededRecords($storagePid) as $cType => $uid) {
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
            } else {
                $failed[] = $cType . ' (' . $url . ')';
            }
            if ($onResult !== null) {
                $onResult($cType, $url, $success);
            }
        }

        return ['warmed' => $warmed, 'failed' => $failed];
    }
}
