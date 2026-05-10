<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class ContentRenderingTemplateTest extends TestCase
{
    public function testCoreContentTemplatesRequiredByTypoScriptConventionExist(): void
    {
        $templateDirectory = __DIR__ . '/../../Resources/Private/FluidStyledContent/Templates';
        $partialDirectory = __DIR__ . '/../../Resources/Private/FluidStyledContent/Partials';
        $layoutDirectory = __DIR__ . '/../../Resources/Private/FluidStyledContent/Layouts';
        $classicTemplates = [
            'Bullets',
            'Div',
            'Generic',
            'Header',
            'Html',
            'Image',
            'Shortcut',
            'Table',
            'Text',
            'Textmedia',
            'Textpic',
            'Uploads',
        ];
        $coreMenuTemplates = [
            'MenuAbstract',
            'MenuCategorizedContent',
            'MenuCategorizedPages',
            'MenuPages',
            'MenuRecentlyUpdated',
            'MenuRelatedPages',
            'MenuSection',
            'MenuSectionPages',
            'MenuSitemap',
            'MenuSitemapPages',
            'MenuSubpages',
        ];

        self::assertFileExists($layoutDirectory . '/Default.fluid.html');
        foreach (['Header', 'RichText', 'Media', 'Menu', 'FileList'] as $partialName) {
            self::assertFileExists($partialDirectory . '/' . $partialName . '.fluid.html');
        }

        foreach ($classicTemplates as $templateName) {
            self::assertFileExists($templateDirectory . '/' . $templateName . '.fluid.html');
        }

        foreach ($coreMenuTemplates as $templateName) {
            self::assertFileExists($templateDirectory . '/' . $templateName . '.fluid.html');
        }
    }

    public function testContentTypoScriptUsesCTypeBasedTemplateResolution(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');

        self::assertStringContainsString('field = CType', $typoScript);
        self::assertStringContainsString('case = uppercamelcase', $typoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/FluidStyledContent/Templates/', $typoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/FluidStyledContent/Partials/', $typoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/FluidStyledContent/Layouts/', $typoScript);
        self::assertStringContainsString('dataProcessing.1421884800 = record-transformation', $typoScript);
        self::assertStringContainsString('tt_content.default =< lib.contentElement', $typoScript);
        self::assertStringContainsString('tt_content.stdWrap.wrapContentElementsWithVeWrapper = 1', $typoScript);
        self::assertStringContainsString('tt_content.textmedia =< lib.contentElement', $typoScript);
        self::assertStringContainsString('tt_content.uploads =< lib.contentElement', $typoScript);
        self::assertStringContainsString('tt_content.bullets =< lib.desiderioContentWithBullets', $typoScript);
        self::assertStringContainsString('tt_content.table =< lib.desiderioContentWithTable', $typoScript);
        self::assertStringContainsString('lib.desiderioShortcutRecords = RECORDS', $typoScript);
        self::assertStringNotContainsString('dataProcessing.20 = files', $typoScript);
        self::assertStringNotContainsString('lib.desiderioContentWithImages', $typoScript);
        self::assertStringNotContainsString('lib.desiderioContentWithFiles', $typoScript);
        self::assertStringContainsString('dataProcessing.20 = split', $typoScript);
        self::assertStringContainsString('dataProcessing.20 = comma-separated-value', $typoScript);
    }

    public function testMenuPagesUsesCoreMenuProcessorAndShadcnStyleClasses(): void
    {
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/TypoScript/content.typoscript');
        $template = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/MenuPages.fluid.html');
        $partial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/Menu.fluid.html');
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/desiderio.css');

        self::assertStringContainsString('tt_content.menu_pages =< lib.desiderioMenuSelectedPages', $typoScript);
        self::assertStringContainsString('dataProcessing.20 = menu', $typoScript);
        self::assertStringContainsString('special = list', $typoScript);
        self::assertStringContainsString('special.value.field = pages', $typoScript);
        self::assertStringContainsString('arguments="{record: record, items: menu, fallbackPages: record.pages}"', $template);

        foreach (['ce-fsc-menu', 'ce-fsc-menu__grid', 'ce-fsc-menu__link', 'ce-fsc-menu__title'] as $className) {
            self::assertStringContainsString($className, $partial);
            self::assertStringContainsString('.' . $className, $css);
        }

        foreach (['<dc:layout.grid', '<dc:molecule.card', '<dc:molecule.cardContent'] as $componentTag) {
            self::assertStringContainsString($componentTag, $partial);
        }

        foreach (['var(--card)', 'var(--border)', 'var(--ring)', 'var(--muted-foreground)'] as $token) {
            self::assertStringContainsString($token, $css);
        }
    }

    public function testFluidStyledContentUsesPresetAwareShadcnSources(): void
    {
        $layout = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Layouts/Default.fluid.html');
        $header = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/Header.fluid.html');
        $tailwind = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Tailwind/desiderio.css');
        $settings = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');

        self::assertStringContainsString('xmlns:dc="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"', $layout);
        self::assertStringContainsString('dc:layout.section', $layout);
        self::assertStringContainsString('dc:atom.typography', $header);
        self::assertStringContainsString('@source "../FluidStyledContent";', $tailwind);
        self::assertStringContainsString('templateRootPath: EXT:desiderio/Resources/Private/FluidStyledContent/Templates/', $settings);
    }

    public function testFluidStyledContentMediaTemplatesUseRecordTransformationFileFields(): void
    {
        $partial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Partials/Media.fluid.html');
        $textmediaTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Textmedia.fluid.html');
        $textpicTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Textpic.fluid.html');
        $imageTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Image.fluid.html');
        $uploadsTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/Uploads.fluid.html');

        self::assertStringContainsString('<f:argument name="files" type="iterable" optional="true"/>', $partial);
        self::assertStringContainsString('<f:argument name="position" type="string" optional="true"/>', $partial);
        self::assertStringContainsString('<f:argument name="maxWidth" type="integer" optional="true" default="1200"/>', $partial);
        self::assertStringContainsString('<f:image image="{file}"', $partial);
        self::assertStringNotContainsString('src="{file.uid}"', $partial);
        self::assertStringNotContainsString('treatIdAsReference', $partial);
        self::assertStringNotContainsString('name="images"', $partial);
        self::assertStringContainsString('files: record.assets', $textmediaTemplate);
        self::assertStringContainsString('files: record.image', $textpicTemplate);
        self::assertStringContainsString('files: record.image', $imageTemplate);
        self::assertStringContainsString('files: record.media', $uploadsTemplate);
        self::assertStringNotContainsString('files: files', $textmediaTemplate . $textpicTemplate . $imageTemplate . $uploadsTemplate);
    }

    public function testExtensionIntegrationSiteSetsAreBundledWithBaseSet(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $solrSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioSolr/config.yaml');
        $newsSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioNews/config.yaml');
        $blogSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/config.yaml');
        $solrTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioSolr/setup.typoscript');
        $newsTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioNews/setup.typoscript');
        $blogTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/setup.typoscript');

        self::assertContains('webconsulting/desiderio-solr', $baseSet['optionalDependencies']);
        self::assertContains('webconsulting/desiderio-news', $baseSet['optionalDependencies']);
        self::assertContains('webconsulting/desiderio-blog', $baseSet['optionalDependencies']);

        self::assertSame('webconsulting/desiderio-solr', $solrSet['name']);
        self::assertTrue($solrSet['hidden']);
        self::assertContains('apache-solr-for-typo3/solr', $solrSet['optionalDependencies']);
        self::assertStringContainsString('plugin.tx_solr', $solrTypoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Solr/Templates/', $solrTypoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Solr/Partials/', $solrTypoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Solr/Layouts/', $solrTypoScript);

        self::assertSame('webconsulting/desiderio-news', $newsSet['name']);
        self::assertTrue($newsSet['hidden']);
        self::assertContains('georgringer/news', $newsSet['optionalDependencies']);
        self::assertStringContainsString('plugin.tx_news', $newsTypoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Templates/', $newsTypoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Partials/', $newsTypoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Layouts/', $newsTypoScript);

        self::assertSame('webconsulting/desiderio-blog', $blogSet['name']);
        self::assertTrue($blogSet['hidden']);
        self::assertContains('t3g/blog', $blogSet['optionalDependencies']);
        self::assertStringContainsString('plugin.tx_blog', $blogTypoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Templates/', $blogTypoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Partials/', $blogTypoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Layouts/', $blogTypoScript);
    }

    public function testDesiderioBlogTemplatesUseShadcnComponentsAndTypedFluidArguments(): void
    {
        $requiredFiles = [
            'Resources/Private/Extensions/Blog/Layouts/Default.html',
            'Resources/Private/Extensions/Blog/Layouts/Post.html',
            'Resources/Private/Extensions/Blog/Layouts/Widget.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogList.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogPost.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Header.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Footer.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Authors.html',
            'Resources/Private/Extensions/Blog/Templates/Post/RelatedPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListByDemand.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListLatestPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListRecentPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByAuthor.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByCategory.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByDate.html',
            'Resources/Private/Extensions/Blog/Templates/Post/ListPostsByTag.html',
            'Resources/Private/Extensions/Blog/Templates/Comment/Comments.html',
            'Resources/Private/Extensions/Blog/Templates/Comment/Form.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/RecentPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Categories.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Tags.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Archive.html',
            'Resources/Private/Extensions/Blog/Partials/List.html',
            'Resources/Private/Extensions/Blog/Partials/TeaserList.html',
            'Resources/Private/Extensions/Blog/Partials/List/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Teaser/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Pagination/Pagination.html',
            'Resources/Private/Extensions/Blog/Partials/Comments/Comment.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Author.html',
            'Resources/Private/Extensions/Blog/Partials/General/FeaturedImage.html',
            'Resources/Private/Extensions/Blog/Partials/General/SocialIcons.html',
        ];

        foreach ($requiredFiles as $relativePath) {
            self::assertFileExists(__DIR__ . '/../../' . $relativePath, "{$relativePath} must exist");
        }

        $shadcnBackedTemplates = [
            'Resources/Private/Extensions/Blog/Layouts/Post.html',
            'Resources/Private/Extensions/Blog/Layouts/Widget.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogList.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogPost.html',
            'Resources/Private/Extensions/Blog/Templates/Post/Header.html',
            'Resources/Private/Extensions/Blog/Templates/Comment/Comments.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Categories.html',
            'Resources/Private/Extensions/Blog/Partials/List.html',
            'Resources/Private/Extensions/Blog/Partials/List/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Teaser/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Comments/Comment.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Author.html',
        ];
        foreach ($shadcnBackedTemplates as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template, "{$relativePath} should declare the Desiderio component namespace");
            self::assertMatchesRegularExpression('/<d:(atom|molecule|layout)\\./', $template, "{$relativePath} should render with shadcn <d:…> components");
        }

        $typedPartials = [
            'Resources/Private/Extensions/Blog/Partials/List.html',
            'Resources/Private/Extensions/Blog/Partials/TeaserList.html',
            'Resources/Private/Extensions/Blog/Partials/SimpleList.html',
            'Resources/Private/Extensions/Blog/Partials/List/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Teaser/Post.html',
            'Resources/Private/Extensions/Blog/Partials/Pagination/Pagination.html',
            'Resources/Private/Extensions/Blog/Partials/Meta/Rendering/Group.html',
            'Resources/Private/Extensions/Blog/Partials/Meta/Rendering/Item.html',
            'Resources/Private/Extensions/Blog/Partials/Comments/Comment.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Author.html',
            'Resources/Private/Extensions/Blog/Partials/Post/Meta.html',
            'Resources/Private/Extensions/Blog/Partials/General/FeaturedImage.html',
            'Resources/Private/Extensions/Blog/Partials/List/Author.html',
            'Resources/Private/Extensions/Blog/Partials/List/Category.html',
            'Resources/Private/Extensions/Blog/Partials/List/Tag.html',
            'Resources/Private/Extensions/Blog/Partials/List/Archive.html',
        ];
        foreach ($typedPartials as $relativePath) {
            $partial = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertMatchesRegularExpression('/<f:argument\\s+name="[^"]+"\\s+type="[^"]+"/', $partial, "{$relativePath} must declare typed <f:argument> for Fluid 5.3 strict typing");
        }

        $blogPageTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/page.tsconfig');
        self::assertStringContainsString('mod.web_layout.tt_content.preview', $blogPageTsConfig);
    }

    public function testContentBlockSiteSetsAreBundledBehindSingleDesiderioSet(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $contentElementsSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioContentElements/config.yaml');
        $userTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/user.tsconfig');

        $contentBlockNames = [];
        foreach (glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/config.yaml') ?: [] as $configFile) {
            $contentBlock = Yaml::parseFile($configFile);
            $contentBlockNames[] = (string) $contentBlock['name'];
        }
        sort($contentBlockNames);

        $setDependencies = $contentElementsSet['optionalDependencies'] ?? [];
        sort($setDependencies);

        preg_match_all('/options\\.sites\\.hideSets := addToList\\(([^)]+)\\)/', $userTsConfig, $matches);
        $hiddenSetNames = [];
        foreach ($matches[1] as $setList) {
            $hiddenSetNames = array_merge($hiddenSetNames, explode(',', $setList));
        }
        sort($hiddenSetNames);

        self::assertContains('webconsulting/desiderio-content-elements', $baseSet['optionalDependencies']);
        self::assertSame('webconsulting/desiderio-content-elements', $contentElementsSet['name']);
        self::assertSame('Desiderio Content Elements', $contentElementsSet['label']);
        self::assertSame($contentBlockNames, $setDependencies);
        self::assertSame($contentBlockNames, $hiddenSetNames);
    }

    public function testShadcnUiPageTemplateSiteSetRegistersBlogAndExtensionTemplates(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $templateSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/config.yaml');
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/setup.typoscript');
        $pageTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/page.tsconfig');
        $backendLayoutLabels = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/backend_layouts.xlf');

        self::assertContains('webconsulting/desiderio-shadcnui-templates', $baseSet['optionalDependencies']);
        self::assertSame('webconsulting/desiderio-shadcnui-templates', $templateSet['name']);
        self::assertTrue($templateSet['hidden']);
        self::assertStringContainsString('paths.20 = EXT:desiderio/Resources/Private/ShadcnUi/Templates/', $typoScript);
        self::assertStringContainsString("EXT:desiderio/Configuration/BackendLayouts/ShadcnUi/*.tsconfig", $pageTsConfig);
        self::assertStringContainsString('backend_layout.desiderio_blog.title', $backendLayoutLabels);
        self::assertStringContainsString('backend_layout.desiderio_extension.title', $backendLayoutLabels);
        self::assertStringContainsString('backend_layout.desiderio_news.title', $backendLayoutLabels);

        $requiredFiles = [
            'Configuration/BackendLayouts/ShadcnUi/DesiderioBlog.tsconfig',
            'Configuration/BackendLayouts/ShadcnUi/DesiderioExtension.tsconfig',
            'Configuration/BackendLayouts/ShadcnUi/DesiderioNews.tsconfig',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioBlog.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioExtension.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioNews.fluid.html',
        ];

        foreach ($requiredFiles as $relativePath) {
            self::assertFileExists(__DIR__ . '/../../' . $relativePath, "{$relativePath} must exist");
        }

        foreach ([
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioBlog.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioExtension.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioNews.fluid.html',
        ] as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);

            self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template);
            self::assertStringContainsString('<d:layout.section', $template);
            self::assertStringContainsString('<d:layout.container', $template);
            self::assertStringContainsString('<d:layout.stack', $template);
            self::assertStringContainsString('contentArea="{content.stage}"', $template);
            self::assertStringContainsString('contentArea="{content.main}"', $template);
            self::assertStringContainsString('contentArea="{content.sidebar}"', $template);
        }
    }

    public function testSolrAndNewsOverrideTemplatesFollowUpstreamStructureAndUseDesiderioComponents(): void
    {
        $requiredFiles = [
            'Resources/Private/Extensions/Solr/Layouts/Fullwidth.html',
            'Resources/Private/Extensions/Solr/Layouts/Split.html',
            'Resources/Private/Extensions/Solr/Templates/Search/Results.html',
            'Resources/Private/Extensions/Solr/Templates/Search/Form.html',
            'Resources/Private/Extensions/Solr/Partials/Search/Form.html',
            'Resources/Private/Extensions/Solr/Partials/Result/Document.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/Options.html',
            'Resources/Private/Extensions/News/Layouts/General.html',
            'Resources/Private/Extensions/News/Layouts/Detail.html',
            'Resources/Private/Extensions/News/Templates/News/List.html',
            'Resources/Private/Extensions/News/Templates/News/Detail.html',
            'Resources/Private/Extensions/News/Templates/News/MagazineList.html',
            'Resources/Private/Extensions/News/Partials/List/Item.html',
            'Resources/Private/Extensions/News/Partials/List/Pagination.html',
            'Resources/Private/Extensions/News/Partials/List/LoadMore.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaContainer.html',
            'Resources/Private/Extensions/News/Partials/Detail/Opengraph.html',
            'Resources/Private/Extensions/News/Partials/Detail/Shariff.html',
        ];

        foreach ($requiredFiles as $relativePath) {
            $path = __DIR__ . '/../../' . $relativePath;
            self::assertFileExists($path, "{$relativePath} must exist");
        }

        foreach ([
            'Resources/Private/Extensions/Solr/Templates/Search/Results.html',
            'Resources/Private/Extensions/Solr/Partials/Search/Form.html',
            'Resources/Private/Extensions/Solr/Partials/Result/Document.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/Options.html',
            'Resources/Private/Extensions/News/Templates/News/List.html',
            'Resources/Private/Extensions/News/Templates/News/Detail.html',
            'Resources/Private/Extensions/News/Partials/List/Item.html',
        ] as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template, "{$relativePath} should use Desiderio Fluid components");
        }
    }

    public function testEveryLabelFileIsXliff20(): void
    {
        $directories = [
            __DIR__ . '/../../Resources/Private/Language',
            __DIR__ . '/../../ContentBlocks/ContentElements',
        ];

        $files = [];
        foreach ($directories as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($iterator as $file) {
                if ($file instanceof \SplFileInfo && $file->isFile() && $file->getExtension() === 'xlf') {
                    $files[] = $file->getPathname();
                }
            }
        }

        self::assertNotEmpty($files);
        foreach ($files as $file) {
            $contents = (string) file_get_contents($file);
            $relative = str_replace(dirname(__DIR__, 2) . '/', '', $file);
            self::assertStringContainsString('urn:oasis:names:tc:xliff:document:2.0', $contents, "{$relative} must be XLIFF 2.0");
            self::assertStringContainsString('<unit ', $contents, "{$relative} must use XLIFF 2.0 <unit> elements");
            self::assertStringNotContainsString('<trans-unit ', $contents, "{$relative} must not use legacy <trans-unit> elements");
        }
    }

    public function testNewsLocallangUsesIcuMessageFormatForPlurals(): void
    {
        $english = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/locallang.xlf');
        $german = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/de.locallang.xlf');

        self::assertStringContainsString('plural,', $english, 'locallang.xlf must use ICU MessageFormat plural rules');
        self::assertStringContainsString('plural,', $german, 'de.locallang.xlf must use ICU MessageFormat plural rules');

        foreach (['news.loadMore.status', 'news.magazine.items', 'news.comments.count', 'news.tags.count', 'news.categories.count'] as $unitId) {
            self::assertStringContainsString('<unit id="' . $unitId . '">', $english, "{$unitId} must exist in locallang.xlf");
            self::assertStringContainsString('<unit id="' . $unitId . '">', $german, "{$unitId} must exist in de.locallang.xlf");
        }
    }

    public function testNewsAndSolrAndFluidStyledContentPartialsDeclareTypedFluidArguments(): void
    {
        $partials = [
            'Resources/Private/Extensions/News/Partials/List/Item.html',
            'Resources/Private/Extensions/News/Partials/List/Pagination.html',
            'Resources/Private/Extensions/News/Partials/List/LoadMore.html',
            'Resources/Private/Extensions/News/Partials/Category/Items.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaContainer.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaImage.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaVideo.html',
            'Resources/Private/Extensions/News/Partials/Detail/Opengraph.html',
            'Resources/Private/Extensions/News/Partials/Detail/Shariff.html',
            'Resources/Private/Extensions/Solr/Partials/Search/Form.html',
            'Resources/Private/Extensions/Solr/Partials/Search/FrequentlySearched.html',
            'Resources/Private/Extensions/Solr/Partials/Search/LastSearches.html',
            'Resources/Private/Extensions/Solr/Partials/Result/Document.html',
            'Resources/Private/Extensions/Solr/Partials/Result/Pagination.html',
            'Resources/Private/Extensions/Solr/Partials/Result/Facets.html',
            'Resources/Private/Extensions/Solr/Partials/Result/FacetsActive.html',
            'Resources/Private/Extensions/Solr/Partials/Result/Sorting.html',
            'Resources/Private/Extensions/Solr/Partials/Result/PerPage.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/Default.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/Hierarchy.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/Options.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/OptionsFiltered.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/OptionsPrefixGrouped.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/OptionsSinglemode.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/OptionsToggle.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/RangeDate.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/RangeNumeric.html',
            'Resources/Private/Extensions/Solr/Partials/Facets/Rootline.html',
            'Resources/Private/FluidStyledContent/Partials/Header.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/RichText.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/Media.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/Menu.fluid.html',
            'Resources/Private/FluidStyledContent/Partials/FileList.fluid.html',
            'Resources/Private/Partials/List/Pagination.html',
            'Resources/Private/Partials/Pagination/Pagination.html',
            'Resources/Private/Partials/Pagination.html',
        ];

        foreach ($partials as $relativePath) {
            $partial = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertMatchesRegularExpression(
                '/<f:argument\\s+name="[^"]+"\\s+type="[^"]+"/',
                $partial,
                "{$relativePath} must declare typed <f:argument> for Fluid 5.3 strict typing"
            );
        }
    }

    public function testPageLayoutShipsAccessibilityPrimitives(): void
    {
        $defaultLayout = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Layouts/Pages/Default.fluid.html');
        $headerPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/Header.fluid.html');
        $componentsCss = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/components.css');
        $english = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/locallang.xlf');
        $german = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/de.locallang.xlf');

        // Page layout must expose a skip link, a #main-content target, and a focusable <main>.
        self::assertStringContainsString('class="d-skip-link"', $defaultLayout);
        self::assertStringContainsString('href="#main-content"', $defaultLayout);
        self::assertStringContainsString('id="main-content"', $defaultLayout);
        self::assertStringContainsString('tabindex="-1"', $defaultLayout);
        self::assertStringContainsString('a11y.skipToContent', $defaultLayout);

        // Header must announce active nav links + interactive controls.
        self::assertStringContainsString('aria-controls="desiderio-main-nav"', $headerPartial);
        self::assertStringContainsString("aria-current: \\'page\\'", $headerPartial, 'Active nav links must declare aria-current="page" via additionalAttributes.');
        self::assertStringContainsString('aria-pressed="false"', $headerPartial);
        self::assertStringContainsString('focusable="false"', $headerPartial);
        self::assertStringContainsString('a11y.menu.toggle', $headerPartial);
        self::assertStringContainsString('a11y.theme.toggle', $headerPartial);
        self::assertStringContainsString('a11y.nav.language', $headerPartial);
        self::assertMatchesRegularExpression(
            '/<ul[^>]*role="list"/',
            $headerPartial,
            'Main nav <ul> must carry role="list" for VoiceOver/Safari list semantics.'
        );

        // Accessibility CSS primitives must be in components.css.
        self::assertStringContainsString('.d-skip-link', $componentsCss);
        self::assertStringContainsString('.sr-only', $componentsCss);
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', $componentsCss);

        // a11y.* labels must exist in both locallang files.
        $a11yLabels = [
            'a11y.skipToContent',
            'a11y.nav.main',
            'a11y.nav.footer',
            'a11y.nav.language',
            'a11y.menu.toggle',
            'a11y.theme.toggle',
        ];
        foreach ($a11yLabels as $unitId) {
            self::assertStringContainsString('<unit id="' . $unitId . '">', $english, "{$unitId} must exist in locallang.xlf");
            self::assertStringContainsString('<unit id="' . $unitId . '">', $german, "{$unitId} must exist in de.locallang.xlf");
        }
    }

    public function testStrippedListSemanticsAreRestoredAcrossOverrides(): void
    {
        $files = [
            'Resources/Private/Extensions/News/Partials/List/Pagination.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/RecentPosts.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Categories.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Tags.html',
            'Resources/Private/Extensions/Blog/Templates/Widget/Archive.html',
            'Resources/Private/Extensions/Solr/Partials/Result/Pagination.html',
            'Resources/Private/Extensions/Solr/Partials/Search/FrequentlySearched.html',
            'Resources/Private/Extensions/Solr/Partials/Search/LastSearches.html',
        ];
        foreach ($files as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertMatchesRegularExpression(
                '/<ul\\b[^>]*\\brole="list"/',
                $template,
                "{$relativePath} must add role=\"list\" to <ul> elements that strip native list semantics via Tailwind."
            );
        }
    }
}
