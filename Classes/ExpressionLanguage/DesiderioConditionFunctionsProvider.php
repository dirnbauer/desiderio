<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

final class DesiderioConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return list<ExpressionFunction>
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'extensionLoaded',
                static fn (): string => 'null',
                static fn (array $arguments, mixed $extensionKey): bool => is_string($extensionKey)
                    && $extensionKey !== ''
                    && ExtensionManagementUtility::isLoaded($extensionKey)
            ),
        ];
    }
}
