<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Contract of the Solr facet templates and the one-click filter script.
 *
 * The rendered markup of a single option is covered by
 * Tests/Functional/Components/FacetCheckboxRenderingTest; what can only be
 * asserted here is the part that needs EXT:solr to execute: which URL an
 * option points at, and that the TypoScript keeps multi-select semantics.
 */
final class SolrFacetTemplateTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../..';

    #[Test]
    public function selectedOptionRemovesAndUnselectedOptionAddsTheFacetItem(): void
    {
        $checkbox = (string)file_get_contents(self::ROOT . '/Resources/Private/Solr/Partials/Facets/Checkbox.html');

        self::assertMatchesRegularExpression(
            '/<f:if condition="\{item\.selected\}">\s*<f:then><f:variable name="targetUrl" value="\{s:uri\.facet\.removeFacetItem\(/',
            $checkbox,
            'A checked option must lead to the URL that removes it'
        );
        self::assertMatchesRegularExpression(
            '/<f:else><f:variable name="targetUrl" value="\{s:uri\.facet\.addFacetItem\(/',
            $checkbox,
            'An unchecked option must lead to the URL that adds it'
        );
        self::assertStringContainsString('<d:molecule.facetCheckbox', $checkbox);
        self::assertStringContainsString('checked="{item.selected}"', $checkbox);
        self::assertStringContainsString("key: 'solr.resultCount.aria'", $checkbox);
    }

    #[Test]
    public function facetGroupIsAFieldsetOfCheckboxesAndKeepsTheShowMoreHooks(): void
    {
        $options = (string)file_get_contents(self::ROOT . '/Resources/Private/Solr/Partials/Facets/Options.html');

        self::assertStringContainsString('<fieldset class="d-facet-group"', $options);
        self::assertStringContainsString('<legend class="d-facet-group__legend">{facet.label}</legend>', $options);
        self::assertStringContainsString('<ul role="list"', $options);
        self::assertStringContainsString('partial="Facets/Checkbox"', $options);
        // EXT:solr's facet_options_controller.js keys on exactly these.
        self::assertStringContainsString('tx-solr-facet-hidden', $options);
        self::assertStringContainsString('tx-solr-facet-show-all', $options);
        self::assertStringContainsString('data-facet-name="{facet.name}"', $options);
        self::assertStringContainsString('data-facet-item-value="{option.value}"', $options);
    }

    #[Test]
    public function resetLinkAppearsOnlyWhenSomethingIsFiltered(): void
    {
        $facets = (string)file_get_contents(self::ROOT . '/Resources/Private/Solr/Partials/Result/Facets.html');

        self::assertStringContainsString('<f:if condition="{resultSet.facets.used -> f:count()}">', $facets);
        self::assertStringContainsString('{s:uri.facet.removeAllFacets()}', $facets);
        self::assertStringContainsString('key="solr.facet.reset"', $facets);
    }

    #[Test]
    public function facetsAreConfiguredForMultiSelect(): void
    {
        $setup = (string)file_get_contents(self::ROOT . '/Configuration/Sets/SolrDefaults/setup.typoscript');

        foreach (['type', 'category'] as $facet) {
            self::assertSame(
                1,
                preg_match(
                    '/^    ' . $facet . ' \{$(?<body>.*?)^    \}$/ms',
                    $setup,
                    $match
                ),
                sprintf('The %s facet must be configured', $facet)
            );
            self::assertStringContainsString('operator = OR', $match['body'], sprintf('The %s facet must use OR semantics', $facet));
            self::assertStringContainsString('keepAllOptionsOnSelection = 1', $match['body'], sprintf('The %s facet must keep every option selectable', $facet));
            self::assertStringContainsString('minimumCount = 0', $match['body'], sprintf('The %s facet must keep zero-count options visible', $facet));
        }
        // category_stringM is filled by the imported IndexQueueNews configuration.
        self::assertStringContainsString('field = category_stringM', $setup);
        self::assertStringContainsString("@import 'EXT:solr/Configuration/TypoScript/Examples/IndexQueueNews/setup.typoscript'", $setup);
    }

    #[Test]
    public function oneClickFilteringIsProgressiveEnhancementWithinTheSameOrigin(): void
    {
        $javaScript = (string)file_get_contents(self::ROOT . '/Resources/Public/Js/solr-facets.js');
        $setup = (string)file_get_contents(self::ROOT . '/Configuration/Sets/SolrDefaults/setup.typoscript');

        self::assertStringContainsString('.d-facet-option__checkbox[data-d-facet-url]', $javaScript);
        self::assertStringContainsString('target.origin !== window.location.origin', $javaScript);
        self::assertStringContainsString('control.disabled = true', $javaScript, 'A second click during navigation must not queue a stale filter');
        self::assertStringContainsString('window.location.assign', $javaScript);
        self::assertStringNotContainsString('fetch(', $javaScript, 'Plain navigation, not an AJAX result swapper');
        self::assertStringContainsString('desiderio-solr-facets = EXT:desiderio/Resources/Public/Js/solr-facets.js', $setup);
    }

    #[Test]
    public function newFacetLabelsExistInEveryShippedLanguage(): void
    {
        foreach (['labels', 'de.labels', 'es.labels', 'fr.labels', 'hu.labels', 'it.labels'] as $file) {
            $xliff = (string)file_get_contents(self::ROOT . '/Resources/Private/Language/' . $file . '.xlf');
            foreach (['solr.facet.reset', 'solr.facet.category', 'solr.facet.group.aria'] as $unit) {
                self::assertStringContainsString('<unit id="' . $unit . '">', $xliff, $file . '.xlf is missing ' . $unit);
            }
        }
    }
}
