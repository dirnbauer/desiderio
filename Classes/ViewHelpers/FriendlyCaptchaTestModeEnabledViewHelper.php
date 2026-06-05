<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Utility\SiteSettingsBoolean;

final class FriendlyCaptchaTestModeEnabledViewHelper extends AbstractViewHelper
{
    private const SETTING_IDENTIFIER = 'desiderio.forms.friendlyCaptchaTestMode';

    public function render(): bool
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if (!$request instanceof ServerRequestInterface) {
            return false;
        }

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return false;
        }

        return SiteSettingsBoolean::isEnabled($site, self::SETTING_IDENTIFIER);
    }
}
