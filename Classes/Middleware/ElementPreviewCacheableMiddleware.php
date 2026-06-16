<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;

/**
 * Makes element library previews (?elPreview=<uid>) cacheable in an authenticated
 * edit session.
 *
 * The previews render one tt_content record standalone for the visual-editor
 * picker, but the iframe requests carry the editor's backend session. Two things
 * about that session would otherwise force a no_cache re-render on every open, so
 * each preview misses the page cache that desiderio:library:warm fills:
 *
 * 1. Admin panel. With the admin panel open EXT:adminpanel's preview module sets
 *    the frontend.preview aspect (show hidden / simulate), which forces no_cache.
 *    We do not want the admin panel inside these previews anyway, so for elPreview
 *    requests we switch it off for THIS request only - flip the in-memory uc flag
 *    StateUtility::isOpen() reads (uc['AdminPanel']['display_top']), never written
 *    back - and run BEFORE EXT:adminpanel's initiator.
 *
 * 2. Workspace. If the editor is working inside a workspace, the frontend renders
 *    that workspace's preview, which never reads from the live page cache (draft
 *    content must not be served from it) - so the warmed live thumbnails are never
 *    hit and every preview re-renders at full cost. The element-library demo
 *    records live in the LIVE workspace and are never workspace-specific, so for
 *    elPreview requests we pin the rendering to live (0): reset the Context
 *    workspace aspect and the backend user's temporary workspace BEFORE
 *    EXT:workspaces' preview middleware reads the offline state.
 *
 * The preview then renders like an anonymous live request and is written to /
 * served from the standard TYPO3 page cache, which any normal "flush caches"
 * clears (cache tags pages_<rootId> / tt_content_<uid>).
 */
final class ElementPreviewCacheableMiddleware implements MiddlewareInterface
{
    public function __construct(
        private readonly Context $context,
    ) {}

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

                $backendUser->setTemporaryWorkspace(0);
            }

            $this->context->setAspect('workspace', new WorkspaceAspect(0));
        }

        return $handler->handle($request);
    }
}
