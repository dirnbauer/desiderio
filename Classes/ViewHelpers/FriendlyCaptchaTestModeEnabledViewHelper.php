<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Utility\FriendlyCaptchaBypass;

final class FriendlyCaptchaTestModeEnabledViewHelper extends AbstractViewHelper
{
    public function render(): bool
    {
        if ($this->renderingContext?->hasAttribute(ServerRequestInterface::class) !== true) {
            return false;
        }

        $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);

        $site = $request->getAttribute('site');
        if (!$site instanceof Site) {
            return false;
        }

        return FriendlyCaptchaBypass::isEnabled($site);
    }
}
