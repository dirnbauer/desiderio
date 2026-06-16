<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\WorkspaceAspect;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use Webconsulting\Desiderio\Middleware\ElementPreviewCacheableMiddleware;

final class ElementPreviewCacheableMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['BE_USER']);

        parent::tearDown();
    }

    public function testPreviewRequestPinsTheWorkspaceToLive(): void
    {
        // An editor working inside an offline workspace (e.g. "Staging").
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(1));

        $request = (new ServerRequest())->withQueryParams(['elPreview' => '42']);

        (new ElementPreviewCacheableMiddleware($context))->process($request, $this->passThroughHandler());

        // The preview must render in live (0): a workspace preview never reads the
        // warmed live page cache, so it would re-render on every open instead.
        self::assertSame(0, $context->getPropertyFromAspect('workspace', 'id'));
        self::assertFalse($context->getPropertyFromAspect('workspace', 'isOffline'));
    }

    public function testNonPreviewRequestLeavesTheWorkspaceUntouched(): void
    {
        $context = new Context();
        $context->setAspect('workspace', new WorkspaceAspect(1));

        $request = new ServerRequest(); // no elPreview parameter

        (new ElementPreviewCacheableMiddleware($context))->process($request, $this->passThroughHandler());

        self::assertSame(1, $context->getPropertyFromAspect('workspace', 'id'));
        self::assertTrue($context->getPropertyFromAspect('workspace', 'isOffline'));
    }

    private function passThroughHandler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }
}
