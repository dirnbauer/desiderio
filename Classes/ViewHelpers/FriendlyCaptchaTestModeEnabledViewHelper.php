<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3\CMS\Core\Site\Entity\Site;
use Webconsulting\Desiderio\Utility\FriendlyCaptchaBypass;

final class FriendlyCaptchaTestModeEnabledViewHelper extends AbstractRequestAwareViewHelper
{
    public function render(): bool
    {
        $site = $this->request()?->getAttribute('site');
        if (!$site instanceof Site) {
            return false;
        }

        return FriendlyCaptchaBypass::isEnabled($site);
    }
}
