<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Makes element library previews (?elPreview=<uid>) cacheable in an authenticated
 * edit session.
 *
 * The previews render one tt_content record standalone for the visual-editor
 * picker, but the iframe requests carry the editor's backend session. With the
 * admin panel open EXT:adminpanel's preview module sets the frontend.preview
 * aspect (show hidden / simulate), which forces no_cache - so every preview is
 * re-rendered on every open instead of being served from the page cache that
 * desiderio:library:warm fills.
 *
 * We do not want the admin panel inside these previews anyway. For elPreview
 * requests this middleware therefore switches the admin panel off for THIS
 * request only - it flips the in-memory uc flag StateUtility::isOpen() reads
 * (uc['AdminPanel']['display_top']) and never writes it back - and runs BEFORE
 * EXT:adminpanel's initiator. The preview then renders like an anonymous request
 * and is written to / served from the standard TYPO3 page cache, which any normal
 * "flush caches" clears (cache tags pages_<rootId> / tt_content_<uid>).
 */
final class ElementPreviewCacheableMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (isset($request->getQueryParams()['elPreview'])) {
            $backendUser = $GLOBALS['BE_USER'] ?? null;
            if ($backendUser instanceof BackendUserAuthentication) {
                $adminPanel = $backendUser->uc['AdminPanel'] ?? [];
                if (!is_array($adminPanel)) {
                    $adminPanel = [];
                }
                $adminPanel['display_top'] = false;
                $backendUser->uc['AdminPanel'] = $adminPanel;
            }
        }

        return $handler->handle($request);
    }
}
