<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use Webconsulting\Desiderio\Middleware\FriendlyCaptchaTestModeMiddleware;

final class FriendlyCaptchaTestModeMiddlewareTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        parent::tearDown();
    }

    public function testEnabledSiteSettingExposesSkipFlagToDownstreamAndGlobalRequest(): void
    {
        $site = $this->createSite(true);
        $request = (new ServerRequest())->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $handler = new class implements RequestHandlerInterface {
            public ?ServerRequestInterface $request = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->request = $request;

                return new Response();
            }
        };

        (new FriendlyCaptchaTestModeMiddleware())->process($request, $handler);

        self::assertInstanceOf(ServerRequestInterface::class, $handler->request);
        self::assertTrue($this->requestSiteConfiguration($handler->request)['friendlycaptcha_skip_dev_validation'] ?? false);

        self::assertTrue($this->requestSiteConfiguration($GLOBALS['TYPO3_REQUEST'])['friendlycaptcha_skip_dev_validation'] ?? false);
    }

    public function testDisabledSiteSettingKeepsFriendlyCaptchaValidationConfigurationUnchanged(): void
    {
        $site = $this->createSite(false);
        $request = (new ServerRequest())->withAttribute('site', $site);
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $handler = new class implements RequestHandlerInterface {
            public ?ServerRequestInterface $request = null;

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->request = $request;

                return new Response();
            }
        };

        (new FriendlyCaptchaTestModeMiddleware())->process($request, $handler);

        self::assertInstanceOf(ServerRequestInterface::class, $handler->request);
        self::assertFalse($this->requestSiteConfiguration($handler->request)['friendlycaptcha_skip_dev_validation'] ?? false);

        self::assertSame($request, $GLOBALS['TYPO3_REQUEST']);
    }

    /**
     * @return array<string, mixed>
     */
    private function requestSiteConfiguration(ServerRequestInterface $request): array
    {
        $site = $request->getAttribute('site');
        self::assertInstanceOf(Site::class, $site);

        $configuration = [];
        foreach ($site->getConfiguration() as $key => $value) {
            if (is_string($key)) {
                $configuration[$key] = $value;
            }
        }

        return $configuration;
    }

    private function createSite(bool $testMode): Site
    {
        return new Site(
            'test',
            1,
            [
                'base' => '/',
                'languages' => [
                    [
                        'languageId' => 0,
                        'title' => 'English',
                        'locale' => 'en_US.UTF-8',
                        'base' => '/',
                    ],
                ],
            ],
            SiteSettings::createFromSettingsTree([
                'desiderio' => [
                    'forms' => [
                        'friendlyCaptchaTestMode' => $testMode,
                    ],
                ],
            ])
        );
    }
}
