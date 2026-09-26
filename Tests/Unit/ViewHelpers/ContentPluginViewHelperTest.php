<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit\ViewHelpers;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\TypoScript\AST\Node\RootNode;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContext;
use Webconsulting\Desiderio\ViewHelpers\ContentPluginViewHelper;

/**
 * Desiderio's Generic template replaces fluid_styled_content's, which is
 * what core hands every Extbase content-element plugin. It must recognise a
 * plugin by its object at tt_content.<CType>.20, or a form or login form
 * renders its heading and nothing else.
 */
final class ContentPluginViewHelperTest extends TestCase
{
    public function testAnExtbasePluginIsRecognisedByItsObjectAt20(): void
    {
        $context = $this->context(['form_formframework.' => ['templateName' => 'Generic', '20' => 'EXTBASEPLUGIN']]);

        self::assertTrue(ContentPluginViewHelper::verdict(['cType' => 'form_formframework'], $context));
    }

    public function testAContentTypeWithoutAPluginIsNot(): void
    {
        $context = $this->context(['text.' => ['templateName' => 'Text']]);

        self::assertFalse(ContentPluginViewHelper::verdict(['cType' => 'text'], $context));
        self::assertFalse(ContentPluginViewHelper::verdict(['cType' => 'unknown_type'], $context));
        self::assertFalse(ContentPluginViewHelper::verdict(['cType' => ''], $context));
    }

    public function testWithoutFrontendTypoScriptNothingIsAPlugin(): void
    {
        $context = new RenderingContext();
        self::assertFalse(ContentPluginViewHelper::verdict(['cType' => 'form_formframework'], $context));

        $context->setAttribute(ServerRequestInterface::class, new ServerRequest('https://example.test/'));
        self::assertFalse(ContentPluginViewHelper::verdict(['cType' => 'form_formframework'], $context));

        // Cached frontend scope: TypoScript present, setup array not built.
        $context->setAttribute(
            ServerRequestInterface::class,
            (new ServerRequest('https://example.test/'))->withAttribute('frontend.typoscript', new FrontendTypoScript(new RootNode(), [], [], []))
        );
        self::assertFalse(ContentPluginViewHelper::verdict(['cType' => 'form_formframework'], $context));
    }

    /**
     * @param array<string, mixed> $contentTypes the tt_content. branch of the setup array
     */
    private function context(array $contentTypes): RenderingContext
    {
        $typoScript = new FrontendTypoScript(new RootNode(), [], [], []);
        $typoScript->setSetupArray(['tt_content.' => $contentTypes]);
        $context = new RenderingContext();
        $context->setAttribute(
            ServerRequestInterface::class,
            (new ServerRequest('https://example.test/'))->withAttribute('frontend.typoscript', $typoScript)
        );

        return $context;
    }
}
