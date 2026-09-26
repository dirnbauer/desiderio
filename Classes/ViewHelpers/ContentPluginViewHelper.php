<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * True when the content type is an Extbase plugin. TYPO3 registers every
 * content-element plugin as `tt_content.<CType>` with `templateName = Generic`
 * and the plugin itself at `.20`. Desiderio's Generic template replaces
 * fluid_styled_content's, so it has to render that object itself, or a form,
 * a login form or any third-party plugin shows its heading and nothing else.
 *
 * Usage:
 *   <dv:contentPlugin cType="{data.CType}">
 *       <f:then><f:cObject typoscriptObjectPath="tt_content.{data.CType}.20" data="{data}" table="tt_content"/></f:then>
 *       <f:else>…</f:else>
 *   </dv:contentPlugin>
 */
final class ContentPluginViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('cType', 'string', 'The content type (tt_content.CType)', true);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext): bool
    {
        $cType = $arguments['cType'] ?? '';
        if (!is_string($cType) || $cType === '' || !$renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return false;
        }
        $request = $renderingContext->getAttribute(ServerRequestInterface::class);
        $typoScript = $request instanceof ServerRequestInterface ? $request->getAttribute('frontend.typoscript') : null;
        if (!$typoScript instanceof FrontendTypoScript) {
            return false;
        }
        try {
            $setup = $typoScript->getSetupArray();
        } catch (\RuntimeException) {
            // Cached frontend scope without the setup array: no plugin to render.
            return false;
        }
        $contentTypes = $setup['tt_content.'] ?? null;
        $contentType = is_array($contentTypes) ? ($contentTypes[$cType . '.'] ?? null) : null;
        $plugin = is_array($contentType) ? ($contentType['20'] ?? null) : null;

        return is_string($plugin) && $plugin !== '';
    }
}
