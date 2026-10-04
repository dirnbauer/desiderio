<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Domain\RecordInterface;
use Webconsulting\Desiderio\Icon\IconRegistry;
use Webconsulting\Desiderio\ViewHelpers\OpensPageViewHelper;

/**
 * KPI cards may show no trend at all and show "steady" as a level line; the
 * Split Text & Media element loads its picture at once when it opens the page.
 */
final class TrendAndImagePriorityTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../../';

    public function testKpiCardsOfferNoTrendAndDrawNoArrowForIt(): void
    {
        $config = (string)file_get_contents(self::ROOT . 'ContentBlocks/ContentElements/kpi-cards/config.yaml');
        $template = (string)file_get_contents(self::ROOT . 'ContentBlocks/ContentElements/kpi-cards/templates/frontend.html');

        self::assertStringContainsString("labels.xlf:item.no_trend'\n            value: ''", $config);
        self::assertStringContainsString("default: ''", $config);
        self::assertStringNotContainsString('label: Positive', $config);
        self::assertStringNotContainsString('label: Caution', $config);
        // An empty trend reaches the stat molecule as it is: no arrow, no tint.
        self::assertStringNotContainsString("alternative: 'positive'", $template);
        self::assertStringContainsString('trend="{item.trend}"', $template);
        self::assertStringContainsString("{f:if(condition: item.trend, then: ' kpi-cards__item--{item.trend}')}", $template);

        foreach (['labels.xlf', 'de.labels.xlf'] as $file) {
            $labels = (string)file_get_contents(self::ROOT . 'Resources/Private/Language/' . $file);
            foreach (['item.no_trend', 'item.positive', 'item.caution'] as $id) {
                self::assertStringContainsString('<unit id="' . $id . '">', $labels, $file . ': ' . $id);
            }
        }
    }

    public function testSteadyTrendIsALevelLineNotAMinus(): void
    {
        $stat = (string)file_get_contents(self::ROOT . 'Resources/Private/Components/Molecule/Stat/Stat.fluid.html');

        self::assertStringContainsString('<d:atom.icon name="trending-flat" size="sm" />', $stat);
        self::assertStringNotContainsString('name="minus"', $stat);
        self::assertContains('trending-flat', IconRegistry::keys());
        self::assertSame('Status', IconRegistry::icon('trending-flat')['group']);
    }

    public function testSplitTextMediaLoadsThePictureThatOpensThePageAtOnce(): void
    {
        $template = (string)file_get_contents(self::ROOT . 'ContentBlocks/ContentElements/textmedia/templates/frontend.html');

        self::assertStringContainsString('xmlns:dv="http://typo3.org/ns/Webconsulting/Desiderio/ViewHelpers"', $template);
        self::assertStringContainsString('<f:variable name="imageLoading" value="lazy"/>', $template);
        self::assertStringContainsString('<f:if condition="{dv:opensPage(record: data)}">', $template);
        self::assertStringContainsString("{decoding: 'async', fetchpriority: 'high'}", $template);
        self::assertStringContainsString('loading="{imageLoading}" additionalAttributes="{imageAttributes}"', $template);
        self::assertStringNotContainsString('loading="lazy"', $template);
    }

    public function testOpensPageIsFalseWithoutARequestOrOutsideTheMainColumn(): void
    {
        $mainColumn = $this->contentElement(0);
        $sidebar = $this->contentElement(1);

        // No rendering context, hence no request: a backend preview or CLI.
        self::assertFalse($this->opensPage($mainColumn));
        self::assertFalse($this->opensPage($sidebar));
    }

    private function opensPage(RecordInterface $record): bool
    {
        $viewHelper = new OpensPageViewHelper();
        $viewHelper->setArguments(['record' => $record]);

        return $viewHelper->render();
    }

    private function contentElement(int $colPos): RecordInterface
    {
        $record = self::createStub(RecordInterface::class);
        $record->method('getMainType')->willReturn('tt_content');
        $record->method('getUid')->willReturn(42);
        $record->method('has')->willReturnCallback(static fn(string $field): bool => $field === 'colPos');
        $record->method('get')->willReturnCallback(static fn(string $field): mixed => $field === 'colPos' ? $colPos : null);

        return $record;
    }
}
