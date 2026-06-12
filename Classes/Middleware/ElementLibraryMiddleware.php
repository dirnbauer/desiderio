<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\Library\ElementCatalog;
use Webconsulting\Desiderio\Library\PreviewUrlBuilder;
use Webconsulting\Desiderio\Library\PreviewWarmer;

/**
 * Frontend JSON endpoint for visual element pickers: ?elementLibrary=1
 * returns the full element catalog with titles/descriptions localized to
 * the backend user's interface language, category list, seeded demo uids
 * and prerendered preview URLs.
 *
 * Access requires a logged-in backend user AND the visual editor request
 * token (window.veInfo.token, scope visual_editor/save) in X-Request-Token,
 * so the endpoint is only reachable from an authenticated edit session.
 */
final class ElementLibraryMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Context $context,
        private readonly FormProtectionFactory $formProtectionFactory,
        private readonly ElementCatalog $elementCatalog,
        private readonly PreviewUrlBuilder $previewUrlBuilder,
        private readonly PreviewWarmer $previewWarmer,
        private readonly LanguageServiceFactory $languageServiceFactory,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!isset($request->getQueryParams()['elementLibrary'])) {
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

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return new JsonResponse(['error' => 'No site resolved for this request'], 404);
        }
        $configuredStoragePid = $site->getSettings()->get('elementLibrary.storagePid', 0);
        $storagePid = is_numeric($configuredStoragePid) ? (int)$configuredStoragePid : 0;
        if ($storagePid <= 0) {
            return new JsonResponse(
                ['error' => 'Element library not configured: run desiderio:library:seed and set elementLibrary.storagePid in the site settings'],
                404
            );
        }

        $backendUser = $GLOBALS['BE_USER'] ?? null;
        $languageService = $backendUser instanceof \TYPO3\CMS\Core\Authentication\AbstractUserAuthentication
            ? $this->languageServiceFactory->createFromUserPreferences($backendUser)
            : $this->languageServiceFactory->create('default');

        $seededRecords = $this->previewWarmer->getSeededRecords($storagePid);

        $elements = [];
        foreach ($this->elementCatalog->getElements() as $element) {
            $demoUid = $seededRecords[$element['cType']] ?? 0;
            $localized = $this->elementCatalog->localizeElement($element, $languageService);
            $elements[] = [
                'cType' => $element['cType'],
                'name' => $element['name'],
                'title' => $localized['title'],
                'description' => $localized['description'],
                'group' => $element['group'],
                'source' => $element['hostExtension'],
                'demoUid' => $demoUid,
                'previewUrl' => $demoUid > 0 ? $this->previewUrlBuilder->build($site, $demoUid) : '',
                'iconUrl' => $this->elementCatalog->getIconWebPath($element),
            ];
        }

        return new JsonResponse(
            [
                'categories' => $this->elementCatalog->getCategories($this->elementCatalog->getElements()),
                'elements' => $elements,
            ],
            200,
            ['Cache-Control' => 'private, no-store']
        );
    }
}
