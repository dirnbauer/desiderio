<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Library;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Frontend\Page\CacheHashCalculator;

/**
 * Builds cache-hash-protected frontend URLs that render exactly one element
 * library record standalone (custom typeNum PAGE object, see the
 * ElementLibrary set). The cHash signs the parameters, so only seeded
 * records below the configured storage folder are renderable.
 */
final class PreviewUrlBuilder
{
    public const DEFAULT_TYPE_NUM = 1777200001;

    public function __construct(
        private readonly CacheHashCalculator $cacheHashCalculator,
    ) {}

    public function build(Site $site, int $contentUid): string
    {
        $configuredTypeNum = $site->getSettings()->get('elementLibrary.previewTypeNum', self::DEFAULT_TYPE_NUM);
        $typeNum = is_numeric($configuredTypeNum) ? (int)$configuredTypeNum : self::DEFAULT_TYPE_NUM;
        $parameters = [
            'type' => $typeNum,
            'elPreview' => $contentUid,
        ];

        $queryString = http_build_query(
            ['id' => $site->getRootPageId()] + $parameters,
            '',
            '&',
            PHP_QUERY_RFC3986
        );
        $cacheHash = $this->cacheHashCalculator->generateForParameters($queryString);
        if ($cacheHash !== '') {
            $parameters['cHash'] = $cacheHash;
        }

        return rtrim((string)$site->getBase(), '/') . '/?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
}
