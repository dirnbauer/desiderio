<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Icon\IconRegistry;

/**
 * The two blog page templates, "Blog – Classic" and "Blog – Modern": each is
 * a backend layout, a PAGEVIEW template and a folder of EXT:blog plugin
 * templates that a TypoScript condition puts in front of the shared ones.
 */
final class BlogPageTemplatesTest extends TestCase
{
    private const string ROOT = __DIR__ . '/../../';

    /**
     * @return iterable<string, array{string}>
     */
    public static function templates(): iterable
    {
        yield 'Classic' => ['Classic'];
        yield 'Modern' => ['Modern'];
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function backendLayouts(): iterable
    {
        yield 'Classic' => ['Classic', 'desiderio_blog_classic'];
        yield 'Modern' => ['Modern', 'desiderio_blog_modern'];
    }

    #[DataProvider('backendLayouts')]
    public function testBackendLayoutIsRegisteredWithTitleAndIcon(string $variant, string $labelKey): void
    {
        $tsConfig = $this->read('Configuration/BackendLayouts/ShadcnUi/DesiderioBlog' . $variant . '.tsconfig');

        self::assertStringContainsString('DesiderioBlog' . $variant . ' {', $tsConfig);
        self::assertStringContainsString('LLL:desiderio.backend_layouts:backend_layout.' . $labelKey . '.title', $tsConfig);
        self::assertStringContainsString('identifier = main', $tsConfig);
        // Both keep "sidebar" for colPos 1, so content survives a switch between them.
        self::assertStringContainsString('identifier = sidebar', $tsConfig);

        self::assertSame(1, preg_match('#icon = EXT:desiderio/(Resources/Public/Icons/BackendLayouts/blog-[a-z]+\.svg)#', $tsConfig, $iconMatch));
        self::assertFileExists(self::ROOT . $iconMatch[1]);

        foreach (['', 'de.', 'es.', 'fr.', 'hu.'] as $prefix) {
            self::assertStringContainsString(
                'id="backend_layout.' . $labelKey . '.title"',
                $this->read('Resources/Private/Language/' . $prefix . 'backend_layouts.xlf'),
                "{$prefix}backend_layouts.xlf must name the {$variant} blog template"
            );
        }
    }

    #[DataProvider('templates')]
    public function testPageTemplateRendersThePostAndTheListWithShadcnComponents(string $variant): void
    {
        $template = $this->read('Resources/Private/ShadcnUi/Templates/Pages/DesiderioBlog' . $variant . '.fluid.html');

        self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template);
        self::assertStringContainsString('<d:layout.section', $template);
        self::assertStringContainsString('<d:layout.container', $template);
        self::assertStringContainsString('<d:layout.stack', $template);
        self::assertStringContainsString('desiderio-blog--' . strtolower($variant), $template);
        self::assertStringContainsString('{page.pageRecord.doktype} == 137', $template);
        self::assertStringContainsString('contentArea="{content.main}"', $template);
        self::assertStringContainsString('contentArea="{content.sidebar}"', $template);
        self::assertStringContainsString('itemprop="articleBody"', $template);
        self::assertStringContainsString('!{pageHeadingOwnedByContent}', $template, 'a news detail view in the blog tree owns the h1');
        self::assertStringNotContainsString('role="main"', $template, '<main> is the page layout\'s landmark');
        foreach (['blog_header', 'blog_footer', 'blog_authors', 'blog_comments', 'blog_commentform', 'blog_relatedposts'] as $plugin) {
            self::assertStringContainsString("tt_content.{$plugin}.20", $template, "{$variant} must render {$plugin}");
        }
    }

    #[DataProvider('templates')]
    public function testPluginTemplateFolderIsSwitchedOnByItsPageTemplate(string $variant): void
    {
        $setup = $this->read('Configuration/Sets/DesiderioBlog/setup.typoscript');
        $condition = '[tree.pagelayout == "pagets__DesiderioBlog' . $variant . '"]';
        self::assertStringContainsString($condition, $setup);

        $block = substr($setup, (int)strpos($setup, $condition));
        $block = substr($block, 0, (int)strpos($block, '[end]'));
        foreach (['templateRootPaths', 'partialRootPaths'] as $kind) {
            $folder = $kind === 'templateRootPaths' ? 'Templates' : 'Partials';
            $path = 'Resources/Private/Extensions/Blog/' . $variant . '/' . $folder . '/';
            self::assertStringContainsString("{$kind}.300 = EXT:desiderio/{$path}", $block);
            self::assertDirectoryExists(self::ROOT . $path);
        }
    }

    #[DataProvider('templates')]
    public function testOverridesUseShadcnComponentsTypedArgumentsAndKnownIcons(string $variant): void
    {
        $knownIcons = array_flip(IconRegistry::keys());
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(
            self::ROOT . 'Resources/Private/Extensions/Blog/' . $variant,
            \FilesystemIterator::SKIP_DOTS
        ));
        $count = 0;
        foreach ($files as $file) {
            if (!$file instanceof \SplFileInfo || $file->getExtension() !== 'html') {
                continue;
            }
            ++$count;
            $relative = str_replace(self::ROOT, '', $file->getPathname());
            $template = (string)file_get_contents($file->getPathname());

            self::assertStringContainsString('<f:comment>', $template, "{$relative} says what it changes");
            if (str_contains($template, 'blogvh:')) {
                self::assertStringContainsString('xmlns:blogvh="http://typo3.org/ns/T3G/AgencyPack/Blog/ViewHelpers"', $template, $relative);
            }
            if (str_contains($template, '<d:')) {
                self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template, $relative);
            }
            if (str_contains($relative, '/Partials/') && str_contains($template, '{post')) {
                self::assertMatchesRegularExpression('/<f:argument\s+name="[^"]+"\s+type="[^"]+"/', $template, "{$relative} declares typed arguments");
            }
            // The legacy .grid block class (components-04-layout.css) wins over
            // Tailwind's gap utilities, so blog templates use flex columns.
            self::assertDoesNotMatchRegularExpression('/class="(?:[^"]*\s)?grid(?:\s[^"]*)?"/', $template, "{$relative} must not use the bare grid utility");
            preg_match_all('/<d:atom\.icon\b[^>]*\bname="([^"{]+)"/', $template, $icons);
            foreach ($icons[1] as $icon) {
                self::assertArrayHasKey($icon, $knownIcons, "{$relative} uses an unknown icon: {$icon}");
            }
        }
        self::assertGreaterThan(3, $count);
    }

    public function testModernListFeaturesTheNewestPostAboveABalancedGrid(): void
    {
        $list = $this->read('Resources/Private/Extensions/Blog/Modern/Partials/List.html');
        self::assertStringContainsString('desiderio-blog-modern-featured', $list);
        self::assertStringContainsString('desiderio-blog-modern-posts', $list);
        self::assertStringContainsString('featured: 1', $list);

        $css = $this->read('Resources/Private/Css/desiderio/23-blog.css');
        self::assertStringContainsString('.desiderio-blog-modern-posts > :last-child:nth-child(3n + 1)', $css, 'a lone last card takes the whole row');
        self::assertStringContainsString('@container blog-card', $css);
    }

    public function testClassicListSeparatesPostsWithTheDinkusNotRules(): void
    {
        $list = $this->read('Resources/Private/Extensions/Blog/Classic/Partials/List.html');
        self::assertStringContainsString('desiderio-blog-dinkus', $list);
        self::assertStringNotContainsString('border-b', $list);
        self::assertStringContainsString('partial="Post/DateLeaf"', $this->read('Resources/Private/Extensions/Blog/Classic/Partials/List/Post.html'));
    }

    public function testBlogStylesAreBuiltAndLabelsTranslated(): void
    {
        $lines = file(self::ROOT . 'Resources/Private/Css/desiderio/manifest.txt');
        $manifest = array_map('trim', $lines === false ? [] : $lines);
        self::assertContains('23-blog.css', $manifest);
        self::assertStringContainsString('desiderio-blog-modern-card', $this->read('Resources/Public/Css/desiderio.css'));

        foreach (['blog.readingTime', 'blog.continueReading', 'blog.latest', 'blog.onThisPage', 'blog.allPosts', 'blog.topics.aria', 'blog.explore', 'blog.share', 'blog.share.copyLink', 'blog.share.copied', 'blog.readingProgress'] as $key) {
            foreach (['', 'de.', 'zh.', 'hu.'] as $prefix) {
                self::assertStringContainsString('id="' . $key . '"', $this->read('Resources/Private/Language/' . $prefix . 'locallang.xlf'), "{$prefix}locallang.xlf lacks {$key}");
            }
        }
    }

    private function read(string $relativePath): string
    {
        $path = self::ROOT . $relativePath;
        self::assertFileExists($path);

        return (string)file_get_contents($path);
    }
}
