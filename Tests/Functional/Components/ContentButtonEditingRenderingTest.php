<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Components;

use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Domain\RecordFactory;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextFactory;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use TYPO3Fluid\Fluid\View\TemplateView;

final class ContentButtonEditingRenderingTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['fluid_styled_content', 'form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'friendsoftypo3/visual-editor',
        'webconsulting/visual-editor-enhancements',
        'webconsulting/desiderio',
    ];

    /**
     * @return iterable<string, array{string, string, string, string, string, string, bool}>
     */
    public static function buttonProvider(): iterable
    {
        foreach ([false, true] as $editMode) {
            $mode = $editMode ? 'editor' : 'visitor';
            yield 'content record, ' . $mode => ['feature-highlight', 'data', 'tt_content', 'desiderio_featurehighlight', 'link_text', 'link', $editMode];
            yield 'collection record, ' . $mode => ['hero-carousel', 'slide', 'hero_carousel_slides', '', 'button_text', 'button_link', $editMode];
        }
    }

    #[DataProvider('buttonProvider')]
    public function testButtonKeepsItsDestinationAndExposesEditorsForTheCorrectRecord(
        string $element,
        string $variable,
        string $table,
        string $type,
        string $textField,
        string $linkField,
        bool $editMode,
    ): void {
        $this->get(ConnectionPool::class)->getConnectionForTable('be_users')->insert('be_users', [
            'uid' => 1,
            'username' => 'button-editor',
            'admin' => 1,
        ]);
        $this->setUpBackendUser(1);

        $request = $this->frontendRequest($editMode);
        $GLOBALS['TYPO3_REQUEST'] = $request;
        // Page-level initialization is outside this component test. Its presence
        // lets the real text and link ViewHelpers evaluate field permissions.
        $this->get(AssetCollector::class)->addInlineJavaScript('veLangInfo', 'window.veInfo = {};');

        $row = [
            'uid' => 23,
            'pid' => 1,
            'sys_language_uid' => 0,
            $textField => 'Explore the collection',
            $linkField => 'https://example.org/explore',
        ];
        if ($type !== '') {
            $row['CType'] = $type;
            $row['variant'] = 'muted';
        }
        $connection = $this->get(ConnectionPool::class)->getConnectionForTable($table);
        $connection->insert($table, $row);
        $row = $connection->select(['*'], $table, ['uid' => 23])->fetchAssociative();
        self::assertIsArray($row);
        $record = $this->get(RecordFactory::class)->createResolvedRecordFromDatabaseRow($table, $row);

        // Render the actual shipped CTA fragment, including its sibling link
        // editor, rather than copying its markup into a test fixture.
        $template = (string)file_get_contents(__DIR__ . '/../../../ContentBlocks/ContentElements/' . $element . '/templates/frontend.html');
        self::assertSame(1, preg_match('/<d:atom\.button\b.*?<\/d:atom\.button>(?:\{[^}]*f:render\.link[^}]*\})?/s', $template, $fragment));
        $source = '<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers" xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection" data-namespace-typo3-fluid="true">'
            . $fragment[0] . '</html>';
        $view = new TemplateView($this->get(RenderingContextFactory::class)->create([], $request));
        $view->getRenderingContext()->getTemplatePaths()->setTemplateSource($source);
        $view->assign($variable, $record);
        $html = (string)$view->render();

        $document = new \DOMDocument();
        self::assertTrue($document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING));
        $xpath = new \DOMXPath($document);
        self::assertSame('https://example.org/explore', $xpath->evaluate('string(//a[@data-slot="button"]/@href)'));
        self::assertSame('Explore the collection', trim((string)$xpath->evaluate('string(//a[@data-slot="button"])')));

        if ($editMode) {
            self::assertSame(1.0, $xpath->evaluate('count(//a[@data-slot="button"]//ve-editable-text[@table="' . $table . '"][@uid="23"][@field="' . $textField . '"])'));
            self::assertSame(1.0, $xpath->evaluate('count(//a[@data-slot="button"]/following-sibling::*[1][self::ve-editable-link][@table="' . $table . '"][@uid="23"][@field="' . $linkField . '"])'));
            self::assertStringContainsString('wizard', (string)$xpath->evaluate('string(//ve-editable-link/@linkbrowserurl)'));
        } else {
            self::assertStringNotContainsString('<ve-editable-', $html);
        }
    }

    private function frontendRequest(bool $editMode): ServerRequest
    {
        $site = new Site('main', 1, [
            'base' => 'https://example.com/',
            'languages' => [['languageId' => 0, 'title' => 'English', 'locale' => 'en_US.UTF-8', 'base' => '/']],
        ]);
        $request = new ServerRequest('https://example.com/', 'GET')
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site)
            ->withAttribute('language', $site->getDefaultLanguage())
            ->withQueryParams($editMode ? ['editMode' => 1] : []);

        return $request->withAttribute('normalizedParams', NormalizedParams::createFromRequest($request));
    }
}
