<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Visual Editor persistence requests can carry malformed Extbase plugin
 * arguments where "controller" or "action" arrive as arrays. Extbase then
 * throws while rendering news and other plugins on the edited page.
 */
final class ExtbasePluginRequestSanitizerMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $queryParams = $this->sanitizeParameterBag($request->getQueryParams());
        $request = $request->withQueryParams($queryParams);

        if ($request->getMethod() === 'POST') {
            $parsedBody = $request->getParsedBody();
            if (is_array($parsedBody)) {
                $request = $request->withParsedBody($this->sanitizeParameterBag($parsedBody));
            }
        }

        return $handler->handle($request);
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    private function sanitizeParameterBag(array $parameters): array
    {
        foreach ($parameters as $key => $value) {
            if (!is_string($key) || !is_array($value) || !$this->isExtbasePluginNamespace($key)) {
                continue;
            }

            $parameters[$key] = $this->sanitizePluginArguments($value);
        }

        return $parameters;
    }

    private function isExtbasePluginNamespace(string $namespace): bool
    {
        return str_starts_with($namespace, 'tx_');
    }

    /**
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    private function sanitizePluginArguments(array $arguments): array
    {
        foreach (['controller', 'action'] as $argumentName) {
            if (!array_key_exists($argumentName, $arguments)) {
                continue;
            }

            if (!is_string($arguments[$argumentName]) || $arguments[$argumentName] === '') {
                unset($arguments[$argumentName]);
            }
        }

        return $arguments;
    }
}
