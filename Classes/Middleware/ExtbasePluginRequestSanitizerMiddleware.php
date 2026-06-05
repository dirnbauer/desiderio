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
        $request = $request->withQueryParams(
            $this->sanitizeParameterBag($this->normalizeParameterBag($request->getQueryParams()))
        );

        if ($request->getMethod() === 'POST') {
            $parsedBody = $this->normalizeParameterBag($request->getParsedBody());
            $request = $request->withParsedBody($this->sanitizeParameterBag($parsedBody));
        }

        return $handler->handle($request);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeParameterBag(mixed $parameters): array
    {
        if (!is_array($parameters)) {
            return [];
        }

        $normalized = [];
        foreach ($parameters as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $parameters
     * @return array<string, mixed>
     */
    private function sanitizeParameterBag(array $parameters): array
    {
        foreach ($parameters as $key => $value) {
            if (!is_array($value) || !$this->isExtbasePluginNamespace($key)) {
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
     * @param array<mixed, mixed> $arguments
     * @return array<string, mixed>
     */
    private function sanitizePluginArguments(array $arguments): array
    {
        $sanitized = [];
        foreach ($arguments as $argumentKey => $argumentValue) {
            if (is_string($argumentKey)) {
                $sanitized[$argumentKey] = $argumentValue;
            }
        }

        foreach (['controller', 'action'] as $argumentName) {
            if (!array_key_exists($argumentName, $sanitized)) {
                continue;
            }

            if (!is_string($sanitized[$argumentName]) || $sanitized[$argumentName] === '') {
                unset($sanitized[$argumentName]);
            }
        }

        return $sanitized;
    }
}
