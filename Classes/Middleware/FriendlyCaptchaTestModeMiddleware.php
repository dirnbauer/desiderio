<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

final class FriendlyCaptchaTestModeMiddleware implements MiddlewareInterface
{
    private const SETTING_IDENTIFIER = 'desiderio.forms.friendlyCaptchaTestMode';
    private const FRIENDLY_CAPTCHA_SKIP_KEY = 'friendlycaptcha_skip_dev_validation';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site || !$this->isTestModeEnabled($site)) {
            return $handler->handle($request);
        }

        $configuration = $site->getConfiguration();
        $configuration[self::FRIENDLY_CAPTCHA_SKIP_KEY] = true;

        $request = $request->withAttribute(
            'site',
            new Site(
                $site->getIdentifier(),
                $site->getRootPageId(),
                $configuration,
                $site->getSettings(),
                $site->getTypoScript(),
                $site->getTSconfig()
            )
        );

        return $handler->handle($request);
    }

    private function isTestModeEnabled(Site $site): bool
    {
        if ($site->getSettings()->isEmpty()) {
            return false;
        }

        $value = $site->getSettings()->get(self::SETTING_IDENTIFIER, false);
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
        }

        return false;
    }
}
