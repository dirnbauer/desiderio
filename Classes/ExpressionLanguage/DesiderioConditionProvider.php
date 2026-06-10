<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ExpressionLanguage;

use TYPO3\CMS\Core\ExpressionLanguage\AbstractProvider;

/**
 * Exposes extensionLoaded("<key>") to TypoScript conditions. Needed because
 * site-set TypoScript cannot otherwise react to optional extensions — the
 * Friendly Captcha form-setup registration depends on it.
 */
final class DesiderioConditionProvider extends AbstractProvider
{
    public function __construct()
    {
        $this->expressionLanguageProviders = [DesiderioConditionFunctionsProvider::class];
    }
}
