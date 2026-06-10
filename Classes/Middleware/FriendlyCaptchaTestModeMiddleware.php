<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Core\ApplicationContext;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\Utility\FriendlyCaptchaBypass;
use Webconsulting\Desiderio\Utility\SiteSettingsBoolean;

final class FriendlyCaptchaTestModeMiddleware implements MiddlewareInterface
{
    private const FRIENDLY_CAPTCHA_SKIP_KEY = 'friendlycaptcha_skip_dev_validation';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ?ApplicationContext $applicationContext = null,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return $handler->handle($request);
        }

        // CAPTCHA validation must never be bypassed on production systems,
        // even if the site setting is enabled by mistake.
        $context = $this->applicationContext ?? Environment::getContext();
        if ($context->isProduction()) {
            if (SiteSettingsBoolean::isEnabled($site, FriendlyCaptchaBypass::TEST_MODE_SETTING)) {
                $this->logger->warning(
                    'FriendlyCaptcha test mode is enabled via site settings but ignored in Production context.',
                    ['site' => $site->getIdentifier(), 'setting' => FriendlyCaptchaBypass::TEST_MODE_SETTING]
                );
            }

            return $handler->handle($request);
        }

        if (!FriendlyCaptchaBypass::isEnabled($site, $context)) {
            return $handler->handle($request);
        }

        $site = $this->enableFriendlyCaptchaSkipValidation($site);
        $request = $request->withAttribute('site', $site);

        // Keep the global request in sync so code resolving the site from
        // $GLOBALS['TYPO3_REQUEST'] (e.g. friendlycaptcha) sees the same flag.
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
