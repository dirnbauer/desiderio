<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\ConsumableNonce;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Base for the Desiderio ViewHelpers that need the PSR-7 request or have to
 * coerce their own arguments.
 *
 * A ViewHelper can be rendered without a request (CLI, a standalone view, a
 * backend preview), so every access has to survive its absence; keeping that
 * in one place stops each ViewHelper from inventing its own guard.
 */
abstract class AbstractRequestAwareViewHelper extends AbstractViewHelper
{
    protected function request(): ?ServerRequestInterface
    {
        if ($this->renderingContext?->hasAttribute(ServerRequestInterface::class) !== true) {
            return null;
        }

        $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);

        return $request instanceof ServerRequestInterface ? $request : null;
    }

    /**
     * ` nonce="…"` when the request carries a TYPO3 14 ConsumableNonce, so an
     * inline <script> stays compatible with a strict `script-src 'nonce-…'`
     * policy. Empty when there is no nonce to consume — never a made-up one.
     */
    protected function nonceAttribute(): string
    {
        $nonce = $this->request()?->getAttribute('nonce');

        return $nonce instanceof ConsumableNonce
            ? ' nonce="' . htmlspecialchars($nonce->consume(), ENT_QUOTES | ENT_HTML5) . '"'
            : '';
    }

    protected function stringArgument(string $name, string $default = ''): string
    {
        $value = $this->arguments[$name] ?? null;

        return is_scalar($value) || $value instanceof \Stringable ? (string)$value : $default;
    }

    protected function intArgument(string $name, int $default): int
    {
        $value = $this->arguments[$name] ?? null;

        return is_numeric($value) ? (int)$value : $default;
    }
}
