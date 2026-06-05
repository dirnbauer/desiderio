<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webconsulting\Desiderio\Middleware\ExtbasePluginRequestSanitizerMiddleware;

final class ExtbasePluginRequestSanitizerMiddlewareTest extends TestCase
{
    public function testSanitizesMalformedControllerAndActionArgumentsInPluginNamespaces(): void
    {
        $middleware = new ExtbasePluginRequestSanitizerMiddleware();
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $request->method('getMethod')->willReturn('POST');
        $request->method('getQueryParams')->willReturn([
            'tx_news_pi1' => [
                'controller' => ['News'],
                'action' => ['list'],
                'news' => '42',
            ],
        ]);
        $request->method('getParsedBody')->willReturn([
            'tx_news_pi1' => [
                'controller' => ['News'],
                'action' => ['detail'],
            ],
            'editMode' => '1',
        ]);

        $request->expects(self::once())
            ->method('withQueryParams')
            ->with([
                'tx_news_pi1' => [
                    'news' => '42',
                ],
            ])
            ->willReturnSelf();

        $request->expects(self::once())
            ->method('withParsedBody')
            ->with([
                'tx_news_pi1' => [],
                'editMode' => '1',
            ])
            ->willReturnSelf();

        $handler->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $middleware->process($request, $handler));
    }

    public function testLeavesValidPluginArgumentsUntouched(): void
    {
        $middleware = new ExtbasePluginRequestSanitizerMiddleware();
        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);
        $response = $this->createMock(ResponseInterface::class);

        $queryParams = [
            'tx_news_pi1' => [
                'controller' => 'News',
                'action' => 'list',
            ],
        ];

        $request->method('getMethod')->willReturn('GET');
        $request->method('getQueryParams')->willReturn($queryParams);

        $request->expects(self::once())
            ->method('withQueryParams')
            ->with($queryParams)
            ->willReturnSelf();

        $request->expects(self::never())->method('withParsedBody');

        $handler->expects(self::once())
            ->method('handle')
            ->with($request)
            ->willReturn($response);

        self::assertSame($response, $middleware->process($request, $handler));
    }
}
