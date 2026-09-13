<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Components;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

/**
 * The facet checkbox is the control the whole search filter UI rests on, so
 * its two states are rendered here rather than asserted as template source: a
 * selected option must come back checked and pointing at the URL that REMOVES
 * it, an unselected one unchecked and pointing at the URL that ADDS it.
 *
 * The add/remove decision itself lives in
 * Resources/Private/Solr/Partials/Facets/Checkbox.html, because only EXT:solr
 * can build those URLs; SolrFacetTemplateTest pins that partial's contract.
 */
final class FacetCheckboxRenderingTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content', 'form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'friendsoftypo3/visual-editor',
        'webconsulting/visual-editor-enhancements',
        'webconsulting/desiderio',
    ];

    #[Test]
    public function selectedOptionRendersCheckedAndLinksToTheRemoveUrl(): void
    {
        $html = $this->renderOption([
            'id' => 'd-facet-type-pages',
            'name' => 'facet-type',
            'value' => 'pages',
            'label' => 'Pages',
            'targetUrl' => '/search?q=typo3&tx_solr%5Bfilter%5D%5B0%5D=',
            'count' => 69,
            'checked' => true,
            'countLabel' => '69 results',
        ]);

        self::assertMatchesRegularExpression('/<input\s+type="checkbox"[^>]*checked="checked"/s', $html);
        self::assertStringContainsString('value="pages"', $html);
        self::assertStringContainsString('data-d-facet-url="/search?q=typo3&amp;tx_solr%5Bfilter%5D%5B0%5D="', $html);
        self::assertStringContainsString('<label for="d-facet-type-pages"', $html);
        self::assertStringContainsString('>69<', $html);
        self::assertStringContainsString('69 results', $html, 'The count must be announced, not only shown');
        self::assertStringContainsString('<noscript>', $html, 'Without JavaScript the same URL must stay reachable as a link');
    }

    #[Test]
    public function unselectedOptionRendersUncheckedAndLinksToTheAddUrl(): void
    {
        $html = $this->renderOption([
            'id' => 'd-facet-type-news',
            'name' => 'facet-type',
            'value' => 'tx_news_domain_model_news',
            'label' => 'News',
            'targetUrl' => '/search?q=typo3&tx_solr%5Bfilter%5D%5B0%5D=type%3Atx_news_domain_model_news',
            'count' => 19,
            'checked' => false,
            'countLabel' => '19 results',
        ]);

        self::assertStringNotContainsString('checked="checked"', $html);
        self::assertStringContainsString('data-d-facet-url="/search?q=typo3&amp;tx_solr%5Bfilter%5D%5B0%5D=type%3Atx_news_domain_model_news"', $html);
        self::assertStringContainsString('value="tx_news_domain_model_news"', $html);
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function renderOption(array $arguments): string
    {
        $attributes = '';
        foreach (array_keys($arguments) as $name) {
            $attributes .= sprintf(' %s="{%s}"', $name, $name);
        }
        $source = sprintf(
            '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection" data-namespace-typo3-fluid="true">'
            . '<d:molecule.facetCheckbox%s /></html>',
            $attributes,
        );

        $request = new ServerRequest('https://example.com/search', 'GET')
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE);
        $view = new TemplateView($this->get(RenderingContextFactory::class)->create([], $request));
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assignMultiple($arguments);

        return (string)$view->render();
    }
}
