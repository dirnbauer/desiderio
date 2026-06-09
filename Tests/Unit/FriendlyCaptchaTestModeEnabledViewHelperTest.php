<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use Webconsulting\Desiderio\ViewHelpers\FriendlyCaptchaTestModeEnabledViewHelper;

final class FriendlyCaptchaTestModeEnabledViewHelperTest extends TestCase
{
    public function testRenderReturnsTrueWhenSiteSettingIsEnabled(): void
    {
        self::assertTrue($this->renderWithSite($this->createSite(true)));
    }

    public function testRenderReturnsFalseWhenSiteSettingIsDisabled(): void
    {
        self::assertFalse($this->renderWithSite($this->createSite(false)));
    }

    public function testRenderReturnsFalseWithoutSiteRequest(): void
    {
        $viewHelper = new FriendlyCaptchaTestModeEnabledViewHelper();
        $viewHelper->setRenderingContext(new RenderingContext());

        self::assertFalse($viewHelper->render());
    }

    private function renderWithSite(Site $site): bool
    {
        $renderingContext = new RenderingContext();
        $renderingContext->setAttribute(
            ServerRequestInterface::class,
            (new ServerRequest())->withAttribute('site', $site)
        );

        $viewHelper = new FriendlyCaptchaTestModeEnabledViewHelper();
        $viewHelper->setRenderingContext($renderingContext);

        return $viewHelper->render();
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
