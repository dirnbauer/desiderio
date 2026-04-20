<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Components;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Component\AbstractComponentCollection;
use TYPO3Fluid\Fluid\View\TemplatePaths;

final class ComponentCollection extends AbstractComponentCollection
{
    public function getTemplatePaths(): TemplatePaths
    {
        $templatePaths = new TemplatePaths();
        $templatePaths->setTemplateRootPaths([
            GeneralUtility::getFileAbsFileName(
                'EXT:desiderio/Resources/Private/Components/'
            ),
        ]);
        return $templatePaths;
    }
}
