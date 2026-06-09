<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\ConsumableNonce;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class StructuredDataViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('enabled', 'bool', 'Whether JSON-LD should be emitted.', false, true);
        $this->registerArgument('name', 'string', 'Website name.', true);
        $this->registerArgument('url', 'string', 'Website URL.', true);
        $this->registerArgument('searchUrl', 'string', 'Search results URL.', false, '');
        $this->registerArgument('queryParameter', 'string', 'Search query parameter.', false, 'q');
        $this->registerArgument('searchEnabled', 'bool', 'Whether to include SearchAction.', false, true);
    }

    public function render(): string
    {
        if (($this->arguments['enabled'] ?? true) === false) {
            return '';
        }

        $name = trim($this->stringArgument('name'));
        $url = $this->absoluteUrl($this->stringArgument('url'));
        if ($name === '' || $url === '') {
            return '';
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $url,
        ];

        $searchUrl = $this->absoluteUrl($this->stringArgument('searchUrl'));
        if (($this->arguments['searchEnabled'] ?? true) !== false && $searchUrl !== '') {
            $queryParameter = $this->normalizeQueryParameter($this->stringArgument('queryParameter', 'q'));
            $data['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => $searchUrl . (str_contains($searchUrl, '?') ? '&' : '?') . rawurlencode($queryParameter) . '={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ];
        }

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        if ($json === false) {
            return '';
        }

        return '<script type="application/ld+json" data-desiderio-structured-data' . $this->nonceAttribute() . '>' . $json . '</script>';
    }

    private function stringArgument(string $name, string $default = ''): string
    {
        $value = $this->arguments[$name] ?? null;

        return is_scalar($value) || $value instanceof \Stringable ? (string)$value : $default;
    }

    private function absoluteUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $url) === 1) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false ? $url : '';
        }
        // Only http(s) or scheme-less (relative) URLs may end up in the JSON-LD output.
        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $url) === 1) {
            return '';
        }

        $request = $this->renderingContext?->hasAttribute(ServerRequestInterface::class) === true
            ? $this->renderingContext->getAttribute(ServerRequestInterface::class)
            : null;
        $uri = $request instanceof ServerRequestInterface ? $request->getUri() : null;
        if ($uri === null) {
            return $url;
        }

        $origin = $uri->getScheme() . '://' . $uri->getHost();
        if ($uri->getPort() !== null && !in_array($uri->getPort(), [80, 443], true)) {
            $origin .= ':' . $uri->getPort();
        }

        return $origin . '/' . ltrim($url, '/');
    }

    private function normalizeQueryParameter(string $queryParameter): string
    {
        $queryParameter = trim($queryParameter);
        if ($queryParameter === '') {
            return 'q';
        }

        $normalized = preg_replace('/[^A-Za-z0-9_\\[\\]-]/', '', $queryParameter);

        return $normalized === null || $normalized === '' ? 'q' : $normalized;
    }

    private function nonceAttribute(): string
    {
        $request = $this->renderingContext?->hasAttribute(ServerRequestInterface::class) === true
            ? $this->renderingContext->getAttribute(ServerRequestInterface::class)
            : null;
        if (!$request instanceof ServerRequestInterface) {
            return '';
        }

        $nonce = $request->getAttribute('nonce');
        if (!$nonce instanceof ConsumableNonce) {
            return '';
        }

        return ' nonce="' . htmlspecialchars($nonce->consume(), ENT_QUOTES | ENT_HTML5) . '"';
    }
}
