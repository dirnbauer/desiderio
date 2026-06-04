<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use Webconsulting\Desiderio\ViewHelpers\FriendlyCaptchaTestModeEnabledViewHelper;

final class FriendlyCaptchaTestModeEnabledViewHelperTest extends TestCase
{
    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_REQUEST']);

        parent::tearDown();
    }

    public function testRenderReturnsTrueWhenSiteSettingIsEnabled(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())->withAttribute('site', $this->createSite(true));

        self::assertTrue((new FriendlyCaptchaTestModeEnabledViewHelper())->render());
    }

    public function testRenderReturnsFalseWhenSiteSettingIsDisabled(): void
    {
        $GLOBALS['TYPO3_REQUEST'] = (new ServerRequest())->withAttribute('site', $this->createSite(false));

        self::assertFalse((new FriendlyCaptchaTestModeEnabledViewHelper())->render());
    }

    public function testRenderReturnsFalseWithoutSiteRequest(): void
    {
        self::assertFalse((new FriendlyCaptchaTestModeEnabledViewHelper())->render());
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
