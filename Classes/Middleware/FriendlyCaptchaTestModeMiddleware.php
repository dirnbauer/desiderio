<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\Utility\SiteSettingsBoolean;

final class FriendlyCaptchaTestModeMiddleware implements MiddlewareInterface
{
    private const SETTING_IDENTIFIER = 'desiderio.forms.friendlyCaptchaTestMode';
    private const FRIENDLY_CAPTCHA_SKIP_KEY = 'friendlycaptcha_skip_dev_validation';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site || !SiteSettingsBoolean::isEnabled($site, self::SETTING_IDENTIFIER)) {
            return $handler->handle($request);
        }

        $site = $this->enableFriendlyCaptchaSkipValidation($site);
        $request = $request->withAttribute('site', $site);

        $globalRequest = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($globalRequest instanceof ServerRequestInterface) {
            $GLOBALS['TYPO3_REQUEST'] = $globalRequest->withAttribute('site', $site);
        }

        return $handler->handle($request);
    }

    private function enableFriendlyCaptchaSkipValidation(Site $site): Site
    {
        $configuration = $site->getConfiguration();
        $configuration[self::FRIENDLY_CAPTCHA_SKIP_KEY] = true;

        return new Site(
            $site->getIdentifier(),
            $site->getRootPageId(),
            $configuration,
            $site->getSettings(),
            $site->getTypoScript(),
            $site->getTSconfig()
        );
    }
}
