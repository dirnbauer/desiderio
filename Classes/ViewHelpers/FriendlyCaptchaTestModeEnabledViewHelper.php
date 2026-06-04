<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

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
        if (!$site instanceof Site || $site->getSettings()->isEmpty()) {
            return false;
        }

        return $this->isTruthy($site->getSettings()->get(self::SETTING_IDENTIFIER, false));
    }

    private function isTruthy(mixed $value): bool
    {
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
