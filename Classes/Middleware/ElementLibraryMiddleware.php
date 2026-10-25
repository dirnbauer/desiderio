<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\AbstractUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\Library\ElementCatalog;
use Webconsulting\Desiderio\Library\ElementLabelOverrides;
use Webconsulting\Desiderio\Library\ElementLibraryScope;
use Webconsulting\Desiderio\Library\ElementSearchService;
use Webconsulting\Desiderio\Library\PreviewUrlBuilder;
use Webconsulting\Desiderio\Library\PreviewWarmer;
use Webconsulting\Desiderio\Utility\SiteSettings;

/**
 * Frontend JSON endpoint for visual element pickers: ?elementLibrary=1
 * returns the full element catalog with titles/descriptions localized to
 * the backend user's interface language, category list, seeded demo uids
 * and prerendered preview URLs.
 *
 * Access requires a logged-in backend user AND the visual editor request
 * token (window.veInfo.token, scope visual_editor/save) in X-Request-Token,
 * so the endpoint is only reachable from an authenticated edit session.
 *
 * ?elementLibrarySearch=<term> ranks the same catalog (ElementSearchService).
 * Both endpoints honour the site's scope (elementLibrary.hosts,
 * elementLibrary.onlySeeded) and its own labels (elementLibrary.labels).
 */
final readonly class ElementLibraryMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Context $context,
        private FormProtectionFactory $formProtectionFactory,
        private ElementCatalog $elementCatalog,
        private ElementSearchService $elementSearchService,
        private PreviewUrlBuilder $previewUrlBuilder,
        private PreviewWarmer $previewWarmer,
        private LanguageServiceFactory $languageServiceFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $request->getQueryParams();
        $isList = isset($queryParams['elementLibrary']);
        $isSearch = isset($queryParams['elementLibrarySearch']);
        if (!$isList && !$isSearch) {
            return $handler->handle($request);
        }

        if (!(bool)$this->context->getPropertyFromAspect('backend.user', 'isLoggedIn', false)) {
            return new JsonResponse(['error' => 'Backend login required'], 401);
        }
        $token = $request->getHeaderLine('X-Request-Token');
        if ($token === ''
            || !$this->formProtectionFactory->createForType('backend')->validateToken($token, 'visual_editor', 'save')
        ) {
            return new JsonResponse(['error' => 'Invalid or missing request token'], 403);
        }

        $backendUser = $GLOBALS['BE_USER'] ?? null;
        $languageService = $backendUser instanceof AbstractUserAuthentication
            ? $this->languageServiceFactory->createFromUserPreferences($backendUser)
            : $this->languageServiceFactory->create('default');

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return new JsonResponse(['error' => 'No site resolved for this request'], 404);
        }

        // Per-site scope (ElementLibraryScope): elementLibrary.hosts limits the
        // picker to the providers the site themes ("desiderio,innesto,core";
        // empty = every host), elementLibrary.onlySeeded to the elements with a
        // demo record in the site's library folder. elementLibrary.labels
        // (ElementLabelOverrides) renames them in the site's own words.
        $scope = ElementLibraryScope::fromSite($site);
        $labels = ElementLabelOverrides::fromSite($site);
        $storagePid = SiteSettings::integer($site, 'elementLibrary.storagePid');

        // Typo-tolerant search + suggestions. The panel already holds the whole
        // catalog, so we only return a ranked cType list (+ suggest / did-you-mean)
        // and it reorders the items it has. Scoped to the same elements as the
        // list, so suggestions never name an element this site cannot insert,
        // and indexed with the same (overridden) texts the cards show.
        if ($isSearch) {
            $rawQuery = $queryParams['elementLibrarySearch'] ?? '';
            $query = is_string($rawQuery) ? $rawQuery : '';
            $langKey = 'default';
            if ($backendUser instanceof AbstractUserAuthentication && is_array($backendUser->user)) {
                $lang = $backendUser->user['lang'] ?? null;
                $langKey = is_string($lang) && $lang !== '' ? $lang : 'default';
            }
            $allowedCTypes = null;
            if ($scope->isRestricted()) {
                $seededRecords = $scope->needsSeededRecords() && $storagePid > 0
                    ? $this->previewWarmer->getSeededRecords($storagePid)
                    : [];
                $allowedCTypes = $scope->allowedCTypes($this->elementCatalog->getElementMetadata(), $seededRecords);
            }
            return new JsonResponse(
                $this->elementSearchService->search(
                    $query,
                    $languageService,
                    $langKey,
                    $allowedCTypes,
                    $labels,
                ),
                200,
                ['Cache-Control' => 'private, no-store'],
            );
        }

        if ($storagePid <= 0) {
            return new JsonResponse(
                ['error' => 'Element library not configured: run desiderio:library:seed and set elementLibrary.storagePid in the site settings'],
                404
            );
        }

        $seededRecords = $this->previewWarmer->getSeededRecords($storagePid);

        // Light, persistently cached catalog: no config/fixture parsing per
        // request (the seeder-only data) - just the fields the picker renders.
        $catalog = $scope->filter($this->elementCatalog->getElementMetadata(), $seededRecords);

        $elements = [];
        foreach ($catalog as $element) {
            $demoUid = $seededRecords[$element['cType']] ?? 0;
            // Title, description, card blurb (library_short.xlf, with
            // **bold**/*italic* emphasis; falls back to the description),
            // keyword chips + synonyms (library_keywords.xlf; cards show the
            // first ~10, the detail view all of them, and both feed the
            // client-side search fallback) and group - each replaced by the
            // site's own unit when elementLibrary.labels provides one.
            $texts = $this->elementCatalog->pickerTexts($element, $languageService, $labels);
            $elements[] = [
                'cType' => $element['cType'],
                'name' => $element['name'],
                'title' => $texts['title'],
                'description' => $texts['description'],
                'shortDescription' => $texts['short'],
                'keywords' => $texts['keywords'],
                'synonyms' => $texts['synonyms'],
                'group' => $texts['group'],
                'source' => $element['hostExtension'],
                'demoUid' => $demoUid,
                'previewUrl' => $demoUid > 0 ? $this->previewUrlBuilder->build($site, $demoUid) : '',
                'iconUrl' => $element['iconUrl'],
            ];
        }

        // The catalog is sorted by the hosts' titles. A site that renames its
        // elements lists them by the names it shows instead.
        if (!$labels->isEmpty()) {
            usort($elements, static fn(array $a, array $b): int => strcasecmp($a['title'], $b['title']));
        }

        return new JsonResponse(
            [
                // From the listed elements, so the chips show the site's own
                // groups and never a category with nothing in it.
                'categories' => $this->elementCatalog->getCategories($elements),
                'elements' => $elements,
            ],
            200,
            ['Cache-Control' => 'private, no-store']
        );
    }
}
