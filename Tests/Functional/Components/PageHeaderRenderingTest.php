<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Components;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * d:organism.pageHeader prints the page's single h1 unless the content renders
 * it: a news detail view (newsDetailUid) or any entry of the TypoScript
 * registry lib.pageHeadingOwnedByContent, which PAGEVIEW hands to the page
 * templates as pageHeadingOwnedByContent ("1" or "").
 */
final class PageHeaderRenderingTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content', 'form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'friendsoftypo3/visual-editor',
        'webconsulting/visual-editor-enhancements',
        'webconsulting/desiderio',
    ];

    /**
     * @return iterable<string, array{string, array<string, mixed>, int}>
     */
    public static function callProvider(): iterable
    {
        $both = '<d:organism.pageHeader page="{page}" newsDetailUid="{newsDetailUid}" headingOwnedByContent="{pageHeadingOwnedByContent}"/>';
        yield 'an ordinary page prints the title' => [$both, ['newsDetailUid' => '0', 'pageHeadingOwnedByContent' => ''], 1];
        yield 'a registry entry hands the h1 to the content' => [$both, ['newsDetailUid' => '0', 'pageHeadingOwnedByContent' => '1'], 0];
        yield 'a news detail view still stands the header down' => [$both, ['newsDetailUid' => '42', 'pageHeadingOwnedByContent' => '1'], 0];
        yield 'a template without the PAGEVIEW variables prints the title' => [$both, [], 1];
        yield 'the 4.3 call without the new argument keeps working' => ['<d:organism.pageHeader page="{page}" newsDetailUid="{newsDetailUid}"/>', ['newsDetailUid' => '0'], 1];
    }

    /**
     * @param array<string, mixed> $variables
     */
    #[Test]
    #[DataProvider('callProvider')]
    public function thePageTitleH1RendersOnlyWhenNoContentOwnsIt(string $call, array $variables, int $expectedH1): void
    {
        $html = $this->render($call, $variables);

        self::assertSame($expectedH1, substr_count($html, '<h1'));
        if ($expectedH1 === 1) {
            self::assertMatchesRegularExpression('/<h1\b[^>]*>Northstar Advisory<\/h1>/', $html);
        }
    }

    /**
     * @param array<string, mixed> $variables
     */
    private function render(string $call, array $variables): string
    {
        $source = '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection" data-namespace-typo3-fluid="true">'
            . $call . '</html>';
        $request = new ServerRequest('https://example.com/', 'GET')
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $view = new TemplateView($this->get(RenderingContextFactory::class)->create([], $request));
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assign('page', (object)['pageRecord' => ['uid' => 7, 'title' => 'Northstar Advisory', 'tx_desiderio_h1_sronly' => 0]]);
        $view->assignMultiple($variables);

        return (string)$view->render();
    }
}
