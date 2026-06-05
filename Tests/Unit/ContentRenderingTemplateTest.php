<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Icon\IconRegistry;

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
        self::assertStringContainsString('dataProcessing.10 = files', $typoScript);
        self::assertStringContainsString('dataProcessing.10.references.fieldName = assets', $typoScript);
        self::assertStringContainsString('dataProcessing.10.references.fieldName = image', $typoScript);
        self::assertStringContainsString('references.fieldName = media', $typoScript);
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

    public function testFluidStyledContentMediaTemplatesUseFilesProcessorFileObjects(): void
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
        self::assertStringNotContainsString('<img ', $partial);
        self::assertStringNotContainsString('src="{file.', $partial);
        self::assertStringNotContainsString('treatIdAsReference', $partial);
        self::assertStringNotContainsString('name="images"', $partial);
        self::assertStringContainsString('files: files', $textmediaTemplate);
        self::assertStringContainsString('files: files', $textpicTemplate);
        self::assertStringContainsString('files: files', $imageTemplate);
        self::assertStringContainsString('files: files', $uploadsTemplate);
        self::assertStringNotContainsString('files: record.', $textmediaTemplate . $textpicTemplate . $imageTemplate . $uploadsTemplate);
    }

    public function testEditableTextViewHelperIsNotRenderedInsideHtmlAttributes(): void
    {
        $templateRoots = [
            'ContentBlocks',
            'Resources/Private',
        ];
        $invalidAttributes = [];

        foreach ($templateRoots as $templateRoot) {
            $directory = realpath(__DIR__ . '/../../' . $templateRoot);
            self::assertIsString($directory);

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                if (!str_ends_with($path, '.html') && !str_ends_with($path, '.fluid.html')) {
                    continue;
                }

                $template = (string) file_get_contents($path);
                if (preg_match_all('/(?:alt|src|href|title|aria-label)="[^"\n]*\{[^"\n]*->\s*f:render\.text\(/', $template, $matches, PREG_OFFSET_CAPTURE) === false) {
                    continue;
                }

                foreach ($matches[0] as $match) {
                    $line = substr_count(substr($template, 0, $match[1]), "\n") + 1;
                    $invalidAttributes[] = str_replace(__DIR__ . '/../../', '', $path) . ':' . $line;
                }
            }
        }

        self::assertSame([], $invalidAttributes);
    }

    public function testTypolinkViewHelperDoesNotUseUnsupportedRelArgument(): void
    {
        $templateRoots = [
            'ContentBlocks',
            'Resources/Private',
        ];
        $invalidLinks = [];

        foreach ($templateRoots as $templateRoot) {
            $directory = realpath(__DIR__ . '/../../' . $templateRoot);
            self::assertIsString($directory);

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
            foreach ($files as $file) {
                if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                if (!str_ends_with($path, '.html') && !str_ends_with($path, '.fluid.html')) {
                    continue;
                }

                $template = (string) file_get_contents($path);
                if (preg_match_all('/<f:link\\.typolink\\b[^>]*\\brel\\s*=/s', $template, $matches, PREG_OFFSET_CAPTURE) === false) {
                    continue;
                }

                foreach ($matches[0] as $match) {
                    $line = substr_count(substr($template, 0, $match[1]), "\n") + 1;
                    $invalidLinks[] = str_replace(__DIR__ . '/../../', '', $path) . ':' . $line;
                }
            }
        }

        self::assertSame([], $invalidLinks, 'Use additionalAttributes for rel on f:link.typolink.');
    }

    public function testPricingSliderTemplateIsConnectedToSharedRuntime(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/pricing-slider/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/desiderio.js');

        self::assertStringContainsString('data-d-pricing-slider', $template);
        self::assertStringContainsString('data-d-pricing-slider-range', $template);
        self::assertStringContainsString('data-d-pricing-slider-tier', $template);
        self::assertStringContainsString('value="0"', $template);
        self::assertStringContainsString("document.querySelectorAll('[data-d-pricing-slider]')", $javascript);
        self::assertStringContainsString("range.addEventListener('input', activate)", $javascript);
        self::assertStringContainsString('pricing-slider__tier--active', $javascript);
        self::assertStringContainsString("range.setAttribute('aria-valuetext'", $javascript);
    }

    public function testTypo3FormBridgeUsesNeutralBordersAndPowermailStyleErrors(): void
    {
        $css = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Css/components.css');

        self::assertStringContainsString('.desiderio-form .form-control:focus', $css);
        self::assertStringContainsString('.desiderio-form .form-control:focus-visible', $css);
        self::assertStringContainsString('border-color: var(--input);', $css);
        self::assertStringContainsString('accent-color: var(--foreground);', $css);
        self::assertStringContainsString('.desiderio-form .form-control[aria-invalid="true"]', $css);
        self::assertStringContainsString('.desiderio-form textarea.is-invalid', $css);
        self::assertStringContainsString('border-color: var(--destructive);', $css);
        self::assertStringContainsString('.desiderio-form .invalid-feedback::before', $css);
        self::assertStringContainsString('content: "!";', $css);
    }

    public function testCounterTemplatesAreConnectedToSharedRuntime(): void
    {
        $counterTemplate = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/counter/templates/frontend.html');
        $statsCounterTemplate = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/stats-counter/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/astro.js');

        self::assertStringContainsString('data-astro-counter', $counterTemplate);
        self::assertStringContainsString('data-astro-target="{item.target_value}"', $counterTemplate);
        self::assertStringContainsString('{item.target_value}</span>', $counterTemplate);
        self::assertStringContainsString('data-astro-counter', $statsCounterTemplate);
        self::assertStringContainsString("scope.querySelectorAll('[data-astro-counter], [data-d-counter]')", $javascript);
        self::assertStringContainsString('window.requestAnimationFrame(step)', $javascript);
    }

    public function testCodeBlockTemplateIsConnectedToAstroHighlightRuntime(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/code-block/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/astro.js');
        $css = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/code-block/assets/frontend.css');
        $setup = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/setup.typoscript');
        $prism = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/prism-lite.js');

        self::assertStringContainsString('data-astro-highlight', $template);
        self::assertStringContainsString('data-astro-language="{data.language}"', $template);
        self::assertStringContainsString('data-astro-copy', $template);
        self::assertStringContainsString('desiderioPrism = EXT:desiderio/Resources/Public/Js/prism-lite.js', $setup);
        self::assertStringContainsString('window.Prism.manual = true', $prism);
        self::assertStringContainsString('languages.php', $prism);
        self::assertStringContainsString('AstroRuntime.prototype.initHighlight', $javascript);
        self::assertStringContainsString('window.Prism.highlight', $javascript);
        self::assertStringContainsString('.code-block .token.keyword', $css);
        self::assertStringContainsString('.code-block .token.string', $css);
        self::assertStringNotContainsString('.astro-token--keyword', $css);
        self::assertStringNotContainsString('var(--primary)', $css);
        self::assertStringNotContainsString('var(--accent)', $css);
    }

    public function testGenericChartTemplateIsConnectedToLegendAndAnimationRuntime(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/chart/templates/frontend.html');
        $javascript = (string) file_get_contents(__DIR__ . '/../../Resources/Public/Js/charts.js');

        self::assertStringContainsString('data-chart-type', $template);
        self::assertStringContainsString('data-show-legend', $template);
        self::assertStringContainsString('data-legend-position', $template);
        self::assertStringContainsString('data-show-values', $template);
        self::assertStringContainsString('chart__legend', $template);
        self::assertStringContainsString('renderLegend(root, values)', $javascript);
        self::assertStringContainsString('drawGenericBarChart(svg, values', $javascript);
        self::assertStringContainsString('animateChart(svg)', $javascript);
    }

    public function testTabsTemplateRendersCollectionPanelContentInline(): void
    {
        $template = (string) file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/tabs/templates/frontend.html');

        self::assertStringContainsString('data-d-tabs-content', $template);
        self::assertStringContainsString('data-value="tab-{data.uid}-{iter.index}"', $template);
        self::assertStringContainsString("{item -> f:render.text(field: 'tab_content')}", $template);
        self::assertStringContainsString('{data.items.0.tab_content}', $template);
        self::assertStringNotContainsString('<d:molecule.tabsContent', $template);
    }

    public function testIntroHeadingSpacingOnlyAppliesWhenMutedTextFollows(): void
    {
        $timelineCss = (string)file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/timeline/assets/frontend.css');
        $textmediaCss = (string)file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/textmedia/assets/frontend.css');

        self::assertStringContainsString(
            '.timeline__intro [data-variant="h2"] + [data-variant="muted"]',
            $timelineCss
        );
        self::assertStringContainsString('margin-block-start: var(--d-spacing-sm);', $timelineCss);
        self::assertStringContainsString(
            '.textmedia__content [data-variant="h2"] + [data-variant="muted"]',
            $textmediaCss
        );
        self::assertStringContainsString('margin-block-start: var(--d-spacing-sm);', $textmediaCss);
    }

    public function testDesiderioMailFormsUseFriendlyCaptcha(): void
    {
        $formFiles = [
            'DesiderioBooking.form.yaml',
            'DesiderioCallback.form.yaml',
            'DesiderioContact.form.yaml',
            'DesiderioDataRequest.form.yaml',
            'DesiderioDownload.form.yaml',
            'DesiderioFeedback.form.yaml',
            'DesiderioLead.form.yaml',
            'DesiderioNewsletter.form.yaml',
        ];

        foreach ($formFiles as $formFile) {
            $form = self::parseYamlArray(__DIR__ . '/../../Resources/Private/Forms/' . $formFile);
            $finishers = self::requireArray($form['finishers'] ?? null);
            self::assertContains(
                'EmailToReceiver',
                array_column($finishers, 'identifier'),
                $formFile . ' must still send mail'
            );

            $renderables = self::requireArray($form['renderables'] ?? null);
            $page = self::requireArray($renderables[0] ?? null);
            $fields = self::requireArray($page['renderables'] ?? null);
            $friendlyCaptchaFields = array_values(array_filter(
                $fields,
                static fn (mixed $field): bool => is_array($field)
                    && ($field['identifier'] ?? '') === 'friendlycaptcha'
                    && ($field['type'] ?? '') === 'Friendlycaptcha'
            ));

            self::assertCount(1, $friendlyCaptchaFields, $formFile . ' must contain one Friendly Captcha element');
            $friendlyCaptchaField = self::requireArray($friendlyCaptchaFields[0]);
            self::assertSame('Spam protection', $friendlyCaptchaField['label'] ?? null, $formFile);
            $friendlyCaptchaValidators = self::requireArray($friendlyCaptchaField['validators'] ?? null);
            self::assertContains(
                'Friendlycaptcha',
                array_column($friendlyCaptchaValidators, 'identifier'),
                $formFile . ' must validate Friendly Captcha server-side'
            );
        }

        $composer = json_decode((string)file_get_contents(__DIR__ . '/../../composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $composer = self::requireArray($composer);
        $composerSuggest = self::requireArray($composer['suggest'] ?? null);
        self::assertArrayHasKey('studiomitte/friendlycaptcha', $composerSuggest);

        $baseSet = self::parseYamlArray(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $optionalDependencies = self::requireArray($baseSet['optionalDependencies'] ?? null);
        self::assertContains('studiomitte/friendlycaptcha', $optionalDependencies);
    }

    public function testDesiderioMailFormsUseBrandedEmailTemplates(): void
    {
        $listener = (string)file_get_contents(__DIR__ . '/../../Classes/EventListener/ConfigureDesiderioFormEmailFinisher.php');
        $htmlLayout = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Form/Email/Layouts/SystemEmail.fluid.html');
        $textLayout = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Form/Email/Layouts/SystemEmail.fluid.txt');
        $htmlTemplate = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Form/Email/Templates/Default.fluid.html');
        $textTemplate = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Form/Email/Templates/Default.fluid.txt');
        $english = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Language/labels.xlf');
        $german = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Language/de.labels.xlf');
        $labelPath = 'LLL:EXT:desiderio/Resources/Private/Language/labels.xlf:';

        self::assertFileExists(__DIR__ . '/../../Resources/Public/Images/webconsulting-logo.svg');
        self::assertStringContainsString('EMAIL_TEMPLATE_ROOT_PATH', $listener);
        self::assertStringContainsString('EMAIL_LAYOUT_ROOT_PATH', $listener);
        self::assertStringContainsString('sourcePageTitle', $listener);
        self::assertStringContainsString('sourcePageUrl', $listener);

        self::assertStringContainsString('webconsulting-logo.svg', $htmlLayout);
        self::assertStringContainsString('#ff8700', $htmlLayout);
        self::assertStringContainsString('color: #ffffff', $htmlLayout);
        self::assertStringContainsString('&copy;', $htmlLayout);
        self::assertStringContainsString('webconsulting gmbh', $htmlLayout);
        self::assertStringContainsString('Desiderio', $htmlLayout);
        self::assertStringContainsString('This email was sent by', $htmlLayout);
        self::assertStringContainsString('Copyright (c)', $textLayout);
        self::assertStringContainsString('key="' . $labelPath . 'email.footer.notice"', $htmlLayout);
        self::assertStringContainsString('key="' . $labelPath . 'email.footer.notice"', $textLayout);

        self::assertStringContainsString('sourcePageTitle', $htmlTemplate);
        self::assertStringContainsString('sourcePageUrl', $htmlTemplate);
        self::assertStringContainsString('formvh:renderAllFormValues', $htmlTemplate);
        self::assertStringContainsString('Best regards', $htmlTemplate);
        self::assertStringContainsString('sourcePageTitle', $textTemplate);
        self::assertStringContainsString('Best regards', $textTemplate);
        self::assertStringContainsString('key="' . $labelPath . 'email.form.intro"', $htmlTemplate);
        self::assertStringContainsString('key="' . $labelPath . 'email.form.goodbye"', $htmlTemplate);
        self::assertStringContainsString('key="' . $labelPath . 'email.form.intro"', $textTemplate);

        foreach ([
            'email.form.intro',
            'email.form.goodbye',
            'email.form.signature',
            'email.footer.notice',
            'email.footer.copyright.webconsulting',
            'email.footer.copyright.desiderio',
            'email.footer.copyright.webconsultingText',
            'email.footer.copyright.desiderioText',
        ] as $unitId) {
            self::assertStringContainsString('<unit id="' . $unitId . '">', $english);
            self::assertStringContainsString('<unit id="' . $unitId . '">', $german);
        }
        self::assertStringContainsString('{sourceType, select, page', $english);
        self::assertStringContainsString('{sourceType, select, page', $german);
        self::assertStringContainsString('{siteName}', $english);
        self::assertStringContainsString('{siteUrl}', $english);
        self::assertStringContainsString('{year}', $english);
    }

    public function testDesiderioFormsRegisterAndUseBrevoContactFinisher(): void
    {
        $formConfiguration = self::parseYamlArray(__DIR__ . '/../../Configuration/Form/Desiderio/config.yaml');
        $prototypes = self::requireArray($formConfiguration['prototypes'] ?? null);
        $standardPrototype = self::requireArray($prototypes['standard'] ?? null);
        $finisherDefinitions = self::requireArray($standardPrototype['finishersDefinition'] ?? null);
        $brevoFinisher = self::requireArray($finisherDefinitions['BrevoContact'] ?? null);
        self::assertSame(
            'Webconsulting\Desiderio\Domain\Finishers\BrevoContactFinisher',
            $brevoFinisher['implementationClassName'] ?? null
        );

        $settings = self::parseYamlArray(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.yaml');
        $desiderioSettings = self::requireArray($settings['desiderio'] ?? null);
        $formSettings = self::requireArray($desiderioSettings['forms'] ?? null);
        $brevoSettings = self::requireArray($formSettings['brevo'] ?? null);
        self::assertFalse($formSettings['friendlyCaptchaTestMode'] ?? true);
        self::assertFalse($brevoSettings['enabled'] ?? true);
        self::assertTrue($brevoSettings['trackEvent'] ?? false);
        self::assertSame('desiderio_form_submit', $brevoSettings['eventName'] ?? null);

        $settingDefinitions = self::parseYamlArray(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.definitions.yaml');
        $definedSettings = self::requireArray($settingDefinitions['settings'] ?? null);
        $friendlyCaptchaTestMode = self::requireArray($definedSettings['desiderio.forms.friendlyCaptchaTestMode'] ?? null);
        self::assertSame('bool', $friendlyCaptchaTestMode['type'] ?? null);
        self::assertFalse($friendlyCaptchaTestMode['default'] ?? true);

        $middleware = (string)file_get_contents(__DIR__ . '/../../Classes/Middleware/FriendlyCaptchaTestModeMiddleware.php');
        $middlewareRegistration = (string)file_get_contents(__DIR__ . '/../../Configuration/RequestMiddlewares.php');
        self::assertStringContainsString('desiderio.forms.friendlyCaptchaTestMode', $middleware);
        self::assertStringContainsString('friendlycaptcha_skip_dev_validation', $middleware);
        self::assertStringContainsString('webconsulting/desiderio-friendlycaptcha-test-mode', $middlewareRegistration);

        $formFiles = [
            'DesiderioBooking.form.yaml',
            'DesiderioCallback.form.yaml',
            'DesiderioContact.form.yaml',
            'DesiderioDataRequest.form.yaml',
            'DesiderioDownload.form.yaml',
            'DesiderioFeedback.form.yaml',
            'DesiderioLead.form.yaml',
            'DesiderioNewsletter.form.yaml',
        ];

        foreach ($formFiles as $formFile) {
            $form = self::parseYamlArray(__DIR__ . '/../../Resources/Private/Forms/' . $formFile);
            $configuredFinishers = self::requireArray($form['finishers'] ?? null);
            $finishers = array_column($configuredFinishers, 'identifier');
            $emailFinisherPosition = array_search('EmailToReceiver', $finishers, true);
            $brevoFinisherPosition = array_search('BrevoContact', $finishers, true);
            $confirmationFinisherPosition = array_search('Confirmation', $finishers, true);

            self::assertContains('BrevoContact', $finishers, $formFile);
            self::assertIsInt($emailFinisherPosition);
            self::assertIsInt($brevoFinisherPosition);
            self::assertIsInt($confirmationFinisherPosition);
            self::assertGreaterThan(
                $emailFinisherPosition,
                $brevoFinisherPosition,
                $formFile . ' must sync with Brevo after mail has been prepared'
            );
            self::assertLessThan(
                $confirmationFinisherPosition,
                $brevoFinisherPosition,
                $formFile . ' must sync with Brevo before the success response is rendered'
            );
        }
    }

    public function testFeedbackFormUsesSixDescriptiveRatingOptions(): void
    {
        $form = self::parseYamlArray(__DIR__ . '/../../Resources/Private/Forms/DesiderioFeedback.form.yaml');
        $renderables = self::requireArray($form['renderables'] ?? null);
        $page = self::requireArray($renderables[0] ?? null);
        $fields = self::requireArray($page['renderables'] ?? null);
        $ratingFields = array_values(array_filter(
            $fields,
            static fn (mixed $field): bool => is_array($field)
                && ($field['identifier'] ?? '') === 'rating'
                && ($field['type'] ?? '') === 'RadioButton'
        ));

        self::assertCount(1, $ratingFields);

        $ratingField = self::requireArray($ratingFields[0]);
        $properties = self::requireArray($ratingField['properties'] ?? null);
        $options = self::requireArray($properties['options'] ?? null);
        self::assertSame([1, 2, 3, 4, 5, 6], array_keys($options));

        foreach ($options as $value => $label) {
            self::assertIsString($label);
            self::assertStringStartsWith((string)$value . ' - ', $label);
            self::assertGreaterThan(55, strlen($label));
        }
    }

    public function testTimelineListUsesContinuousRailAndConnectedCards(): void
    {
        $timelineCss = (string)file_get_contents(__DIR__ . '/../../ContentBlocks/ContentElements/timeline/assets/frontend.css');

        self::assertStringContainsString('.timeline__list::before', $timelineCss);
        self::assertStringContainsString('gap: 0;', $timelineCss);
        self::assertStringContainsString('.timeline__marker::after', $timelineCss);
        self::assertStringContainsString('width: calc(var(--d-spacing-md) + 0.5625rem);', $timelineCss);
        self::assertStringContainsString('.timeline__content', $timelineCss);
        self::assertStringContainsString('position: relative;', $timelineCss);
        self::assertStringContainsString('border-radius: var(--d-radius-lg);', $timelineCss);
        self::assertStringContainsString('padding: var(--d-spacing-lg);', $timelineCss);
    }

    public function testExtensionIntegrationSiteSetsAreBundledWithBaseSet(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $solrSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioSolr/config.yaml');
        $newsSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioNews/config.yaml');
        $blogSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/config.yaml');
        $blogStandaloneSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioBlogStandalone/config.yaml');
        $solrTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioSolr/setup.typoscript');
        $newsTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioNews/setup.typoscript');
        $blogTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/setup.typoscript');

        self::assertIsArray($baseSet);
        self::assertIsArray($solrSet);
        self::assertIsArray($newsSet);
        self::assertIsArray($blogSet);
        self::assertIsArray($blogStandaloneSet);

        $baseOptionalDependencies = $baseSet['optionalDependencies'] ?? [];
        $solrOptionalDependencies = $solrSet['optionalDependencies'] ?? [];
        $newsDependencies = $newsSet['dependencies'] ?? [];
        $blogOptionalDependencies = $blogSet['optionalDependencies'] ?? [];
        $blogStandaloneOptionalDependencies = $blogStandaloneSet['optionalDependencies'] ?? [];
        self::assertIsArray($baseOptionalDependencies);
        self::assertIsArray($solrOptionalDependencies);
        self::assertIsArray($newsDependencies);
        self::assertIsArray($blogOptionalDependencies);
        self::assertIsArray($blogStandaloneOptionalDependencies);

        self::assertContains('webconsulting/desiderio-solr', $baseOptionalDependencies);
        self::assertContains('webconsulting/desiderio-news', $baseOptionalDependencies);
        self::assertContains('webconsulting/desiderio-blog', $baseOptionalDependencies);

        self::assertSame('webconsulting/desiderio-solr', $solrSet['name']);
        self::assertTrue($solrSet['hidden']);
        self::assertArrayNotHasKey('dependencies', $solrSet);
        self::assertContains('apache-solr-for-typo3/solr', $solrOptionalDependencies);
        self::assertStringContainsString('plugin.tx_solr', $solrTypoScript);
        self::assertStringContainsString('templateRootPaths.100 = EXT:desiderio/Resources/Private/Solr/Templates/', $solrTypoScript);
        self::assertStringContainsString('partialRootPaths.100 = EXT:desiderio/Resources/Private/Solr/Partials/', $solrTypoScript);
        self::assertStringContainsString('layoutRootPaths.100 = EXT:desiderio/Resources/Private/Solr/Layouts/', $solrTypoScript);

        self::assertSame('webconsulting/desiderio-news', $newsSet['name']);
        self::assertTrue($newsSet['hidden']);
        self::assertContains('georgringer/news', $newsDependencies);
        self::assertStringContainsString('plugin.tx_news', $newsTypoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Templates/', $newsTypoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Partials/', $newsTypoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/News/Layouts/', $newsTypoScript);
        self::assertStringContainsString('detailPid = {$news.pages.detail}', $newsTypoScript);
        self::assertStringContainsString('defaultDetailPid = {$news.pages.detail}', $newsTypoScript);
        self::assertStringContainsString('tt_content.news_pi1', $newsTypoScript);
        self::assertStringContainsString('tt_content.news_newsdetail', $newsTypoScript);
        self::assertStringContainsString('controller = News', $newsTypoScript);
        self::assertStringContainsString('action = list', $newsTypoScript);
        self::assertStringContainsString('action = detail', $newsTypoScript);

        self::assertSame('webconsulting/desiderio-blog', $blogSet['name']);
        self::assertTrue($blogSet['hidden']);
        self::assertContains('blog/integration', $blogOptionalDependencies);
        self::assertStringContainsString('plugin.tx_blog', $blogTypoScript);
        self::assertStringContainsString('templateRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Templates/', $blogTypoScript);
        self::assertStringContainsString('partialRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Partials/', $blogTypoScript);
        self::assertStringContainsString('layoutRootPaths.200 = EXT:desiderio/Resources/Private/Extensions/Blog/Layouts/', $blogTypoScript);
        foreach (['posts', 'category', 'tag', 'archive', 'comments', 'author'] as $blogFeedName) {
            self::assertStringContainsString(
                'blog_rss_' . $blogFeedName . '.config.additionalHeaders.10.header = Content-Type: application/rss+xml; charset=utf-8',
                $blogTypoScript,
            );
        }
        self::assertStringNotContainsString('lib.dynamicContent', $blogTypoScript);

        self::assertSame('webconsulting/desiderio-blog-standalone', $blogStandaloneSet['name']);
        self::assertContains('webconsulting/desiderio-news', $blogStandaloneOptionalDependencies);
    }

    public function testDesiderioPowermailOptionGroupsExposeVisibleLegend(): void
    {
        $shadcnClasses = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/ShadcnClass.html');
        $groupTemplates = [
            'Resources/Private/Extensions/Powermail/Partials/Form/Field/Check.html',
            'Resources/Private/Extensions/Powermail/Partials/Form/Field/Radio.html',
        ];

        self::assertStringContainsString('<f:case value="fieldLegend">mb-2 flex', $shadcnClasses);

        foreach ($groupTemplates as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);

            self::assertStringContainsString('<fieldset data-slot="field-set"', $template);
            self::assertStringContainsString('data-slot="field-legend"', $template);
            self::assertStringContainsString("slot: 'fieldLegend'", $template);
            self::assertStringNotContainsString('<legend class="sr-only">', $template);
            self::assertSame(1, substr_count($template, '<f:if condition="{field.mandatory}">'), "{$relativePath} should render the required marker once on the group legend");
        }
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

        $blogTemplateDirectory = __DIR__ . '/../../Resources/Private/Extensions/Blog';
        $blogTemplateFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($blogTemplateDirectory, \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($blogTemplateFiles as $templateFile) {
            if (!$templateFile instanceof \SplFileInfo) {
                continue;
            }

            if (!$templateFile->isFile() || $templateFile->getExtension() !== 'html') {
                continue;
            }

            $template = (string) file_get_contents($templateFile->getPathname());
            if (!str_contains($template, 'blogvh:')) {
                continue;
            }

            $relativePath = str_replace(__DIR__ . '/../../', '', $templateFile->getPathname());
            self::assertStringContainsString('xmlns:blogvh="http://typo3.org/ns/T3G/AgencyPack/Blog/ViewHelpers"', $template, "{$relativePath} must declare the Blog view helper namespace");
        }

        $knownIconKeys = array_flip(IconRegistry::keys());
        $blogTemplateFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($blogTemplateDirectory, \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($blogTemplateFiles as $templateFile) {
            if (!$templateFile instanceof \SplFileInfo || !$templateFile->isFile() || $templateFile->getExtension() !== 'html') {
                continue;
            }

            $template = (string) file_get_contents($templateFile->getPathname());
            if (preg_match_all('/<d:atom\.icon\b[^>]*\bname="([^"{]+)"/', $template, $iconMatches) === false) {
                continue;
            }

            $relativePath = str_replace(__DIR__ . '/../../', '', $templateFile->getPathname());
            foreach ($iconMatches[1] as $iconKey) {
                self::assertArrayHasKey($iconKey, $knownIconKeys, "{$relativePath} uses an unknown Desiderio icon key: {$iconKey}");
            }
        }

        $blogPageTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/page.tsconfig');
        self::assertStringContainsString('mod.web_layout.tt_content.preview', $blogPageTsConfig);

        $listPostPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Blog/Partials/List/Post.html');
        self::assertStringContainsString('partial="Meta/ListHeader"', $listPostPartial);
        self::assertStringNotContainsString('flex min-w-0 flex-wrap items-start gap-x-3 gap-y-2', $listPostPartial);
        self::assertStringNotContainsString('partial="Meta/Rendering/Group" arguments="{metatype: \'listheader\'}"', $listPostPartial);

        $teaserPostPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Blog/Partials/Teaser/Post.html');
        self::assertStringContainsString('partial="Meta/TeaserHeader"', $teaserPostPartial);
        self::assertStringNotContainsString('flex min-w-0 flex-wrap items-start gap-x-3 gap-y-2', $teaserPostPartial);
        self::assertStringNotContainsString('partial="Meta/Rendering/Group" arguments="{metatype: \'teaserheader\'}"', $teaserPostPartial);

        $metaAuthorsPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Blog/Partials/Meta/Elements/Authors.html');
        self::assertStringContainsString('<d:atom.icon name="user" size="xs"/>', $metaAuthorsPartial);
        self::assertStringNotContainsString('blogvh:uri.avatar', $metaAuthorsPartial);
        self::assertStringNotContainsString('<d:atom.avatar', $metaAuthorsPartial);

        $metaRenderingSection = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Blog/Partials/Meta/Rendering/Section.html');
        self::assertStringNotContainsString('avatarSettings', $metaRenderingSection);

        $blogSettings = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/settings.yaml');
        self::assertIsArray($blogSettings);
        $blogPlugin = $blogSettings['plugin'] ?? [];
        self::assertIsArray($blogPlugin);
        $blogTxBlog = $blogPlugin['tx_blog'] ?? [];
        self::assertIsArray($blogTxBlog);
        $blogPluginSettings = $blogTxBlog['settings'] ?? [];
        self::assertIsArray($blogPluginSettings);
        $blogMetaSettings = $blogPluginSettings['meta'] ?? [];
        self::assertIsArray($blogMetaSettings);
        $blogListHeaderMeta = $blogMetaSettings['listheader'] ?? [];
        $blogTeaserHeaderMeta = $blogMetaSettings['teaserheader'] ?? [];
        self::assertIsArray($blogListHeaderMeta);
        self::assertIsArray($blogTeaserHeaderMeta);
        $blogListHeaderElements = $blogListHeaderMeta['elements'] ?? [];
        $blogTeaserHeaderElements = $blogTeaserHeaderMeta['elements'] ?? [];
        self::assertIsArray($blogListHeaderElements);
        self::assertIsArray($blogTeaserHeaderElements);
        $blogListHeaderTags = $blogListHeaderElements['tags'] ?? [];
        $blogTeaserHeaderTags = $blogTeaserHeaderElements['tags'] ?? [];
        self::assertIsArray($blogListHeaderTags);
        self::assertIsArray($blogTeaserHeaderTags);
        self::assertTrue($blogListHeaderTags['enable'] ?? false);
        self::assertTrue($blogTeaserHeaderTags['enable'] ?? false);

        $blogTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioBlog/setup.typoscript');
        self::assertStringContainsString("listheader {\n      elements {\n        authors.enable = 1\n        categories.enable = 1\n        tags.enable = 1", $blogTypoScript);
        self::assertStringContainsString("teaserheader {\n      elements {\n        authors.enable = 1\n        categories.enable = 1\n        tags.enable = 1", $blogTypoScript);

        foreach (['Page/BlogList.html', 'Page/BlogPost.html'] as $relativeTemplate) {
            $template = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Blog/Templates/' . $relativeTemplate);
            self::assertStringContainsString('lg:sticky lg:top-24', $template, "{$relativeTemplate} must keep the desktop sidebar sticky");
            self::assertStringContainsString('lg:max-h-[calc(100dvh-7rem)]', $template, "{$relativeTemplate} must cap the sidebar to the viewport");
            self::assertStringContainsString('lg:overflow-y-auto', $template, "{$relativeTemplate} must let long sidebar widget stacks scroll");
            self::assertStringContainsString('lg:overscroll-contain', $template, "{$relativeTemplate} must avoid pulling the page scroll while the sidebar is scrolling");
        }
    }

    public function testDesiderioBlogPageTemplatesAreVisualEditorReady(): void
    {
        $blogPageTemplates = [
            'Resources/Private/Extensions/Blog/Templates/Page/BlogList.html',
            'Resources/Private/Extensions/Blog/Templates/Page/BlogPost.html',
        ];

        foreach ($blogPageTemplates as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);

            self::assertStringContainsString(
                '<f:render.contentArea contentArea="{blogContentAreas.content}"',
                $template,
                "{$relativePath} must expose the Blog PAGEVIEW content area for Visual Editor add/move/delete support",
            );
            self::assertStringContainsString(
                'tt_content.{listType}.20',
                $template,
                "{$relativePath} must render blog plugin slots directly through the Extbase plugin path",
            );
            self::assertStringNotContainsString('lib.dynamicContent', $template, "{$relativePath} must not render legacy dynamic content");
            self::assertStringNotContainsString('contentListOptions', $template, "{$relativePath} must not build synthetic tt_content rows");
            self::assertStringNotContainsString('contentObjectData', $template, "{$relativePath} must not pass synthetic tt_content data");
            self::assertStringNotContainsString('table="tt_content"', $template, "{$relativePath} must not trigger record transformation for synthetic tt_content rows");
        }
    }

    public function testContentBlockSiteSetsAreBundledBehindSingleDesiderioSet(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $contentElementsSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioContentElements/config.yaml');
        $userTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/user.tsconfig');
        self::assertIsArray($baseSet);
        self::assertIsArray($contentElementsSet);

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

        $baseOptionalDependencies = $baseSet['optionalDependencies'] ?? [];
        $contentElementsDependencies = $contentElementsSet['dependencies'] ?? [];
        self::assertIsArray($baseOptionalDependencies);
        self::assertIsArray($contentElementsDependencies);

        self::assertNotContains('webconsulting/desiderio-content-elements', $baseOptionalDependencies);
        self::assertSame('webconsulting/desiderio-content-elements', $contentElementsSet['name']);
        self::assertSame('Desiderio Content Elements', $contentElementsSet['label']);
        self::assertContains('webconsulting/desiderio', $contentElementsDependencies);
        self::assertSame($contentBlockNames, $setDependencies);
        self::assertSame($contentBlockNames, $hiddenSetNames);
    }

    public function testScenarioPresetSiteSetsOverridePageviewTemplates(): void
    {
        $presets = [
            'Corporate' => 'DesiderioPresetCorporate',
        ];
        $expectedWordmarks = [
            'Corporate' => 'Desiderio',
        ];
        $sharedPartials = [
            'ContentArea' => 'contentArea="{content}"',
            'Stage' => 'contentArea="{content}"',
            'SystemHeader' => '<f:argument name="summaryTag"',
        ];
        $templateNames = [
            'DesiderioStartpage',
            'DesiderioContentpage',
            'DesiderioContentpageSidebar',
            'DesiderioSearch',
            'DesiderioError',
        ];

        foreach ($sharedPartials as $partialName => $expectedMarkup) {
            $partialPath = 'Resources/Private/Templates/Partials/Presets/' . $partialName . '.fluid.html';
            $absolutePartialPath = __DIR__ . '/../../' . $partialPath;

            self::assertFileExists($absolutePartialPath, "{$partialPath} must exist for reusable preset chrome");
            self::assertStringContainsString($expectedMarkup, (string) file_get_contents($absolutePartialPath));
        }

        foreach ($presets as $presetDirectory => $setDirectory) {
            $presetSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/' . $setDirectory . '/config.yaml');
            $presetSettings = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/' . $setDirectory . '/settings.yaml');
            $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/' . $setDirectory . '/setup.typoscript');
            $cssName = strtolower($presetDirectory);
            $cssPath = __DIR__ . '/../../Resources/Public/Css/preset-' . $cssName . '.css';

            self::assertIsArray($presetSet);
            self::assertIsArray($presetSettings);
            self::assertTrue($presetSet['hidden']);
            $presetDesiderioSettings = $presetSettings['desiderio'] ?? null;
            self::assertIsArray($presetDesiderioSettings);
            $presetBrandSettings = $presetDesiderioSettings['brand'] ?? null;
            self::assertIsArray($presetBrandSettings);
            self::assertSame($expectedWordmarks[$presetDirectory], $presetBrandSettings['wordmark'] ?? null);
            self::assertIsString($presetBrandSettings['tagline'] ?? null);
            self::assertFileExists($cssPath);
            self::assertStringContainsString(
                'lib.fluidPage.paths.30 = EXT:desiderio/Resources/Private/Presets/' . $presetDirectory . '/Templates/',
                $typoScript,
                $setDirectory . ' must register a PAGEVIEW override path',
            );

            foreach ($templateNames as $templateName) {
                $templatePath = 'Resources/Private/Presets/' . $presetDirectory . '/Templates/Pages/' . $templateName . '.fluid.html';
                $absoluteTemplatePath = __DIR__ . '/../../' . $templatePath;

                self::assertFileExists($absoluteTemplatePath, "{$templatePath} must exist so {$setDirectory} is a complete page archetype");

                $template = (string) file_get_contents($absoluteTemplatePath);
                self::assertStringContainsString('Pages/Default', $template);
                self::assertStringContainsString('partial="Presets/ContentArea"', $template);
                self::assertStringNotContainsString(
                    "arguments=\"{\n",
                    $template,
                    "{$templatePath} must not use multi-line inline f:render arguments; Fluid 5/Admin Panel treats them as strings."
                );
            }

            $sidebarTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Presets/' . $presetDirectory . '/Templates/Pages/DesiderioContentpageSidebar.fluid.html');
            self::assertStringContainsString('content: content.sidebar', $sidebarTemplate);
        }
    }

    public function testShadcnUiPageTemplateSiteSetRegistersBlogAndExtensionTemplates(): void
    {
        $baseSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $templateSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/config.yaml');
        $typoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/setup.typoscript');
        $pageTsConfig = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioShadcnUiTemplates/page.tsconfig');
        $backendLayoutLabels = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Language/backend_layouts.xlf');

        self::assertIsArray($baseSet);
        self::assertIsArray($templateSet);

        $baseOptionalDependencies = $baseSet['optionalDependencies'] ?? [];
        self::assertIsArray($baseOptionalDependencies);

        self::assertNotContains('webconsulting/desiderio-shadcnui-templates', $baseOptionalDependencies);
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

        foreach (['Corporate'] as $preset) {
            $presetSet = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/DesiderioPreset' . $preset . '/config.yaml');
            self::assertIsArray($presetSet);
            $presetDependencies = $presetSet['dependencies'] ?? [];
            self::assertIsArray($presetDependencies);
            self::assertContains('webconsulting/desiderio-shadcnui-templates', $presetDependencies);
        }

        foreach ([
            'Resources/Private/ShadcnUi/Templates/Pages/BlogList.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/BlogPost.fluid.html',
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

        foreach ([
            'Resources/Private/ShadcnUi/Templates/Pages/BlogList.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/BlogPost.fluid.html',
            'Resources/Private/ShadcnUi/Templates/Pages/DesiderioBlog.fluid.html',
        ] as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertStringContainsString('lg:sticky lg:top-24', $template, "{$relativePath} must keep the desktop sidebar sticky");
            self::assertStringContainsString('lg:max-h-[calc(100dvh-7rem)]', $template, "{$relativePath} must cap the sidebar to the viewport");
            self::assertStringContainsString('lg:overflow-y-auto', $template, "{$relativePath} must let long sidebar widget stacks scroll");
            self::assertStringContainsString('lg:overscroll-contain', $template, "{$relativePath} must avoid pulling the page scroll while the sidebar is scrolling");
        }
    }

    public function testSolrAndNewsOverrideTemplatesFollowUpstreamStructureAndUseDesiderioComponents(): void
    {
        $requiredFiles = [
            'Resources/Private/Solr/Layouts/Split.html',
            'Resources/Private/Solr/Templates/Search/Results.html',
            'Resources/Private/Solr/Partials/Search/Form.html',
            'Resources/Private/Solr/Partials/Result/Document.html',
            'Resources/Private/Solr/Partials/Facets/Options.html',
            'Resources/Private/Extensions/News/Layouts/General.html',
            'Resources/Private/Extensions/News/Layouts/Detail.html',
            'Resources/Private/Extensions/News/Templates/News/List.html',
            'Resources/Private/Extensions/News/Templates/News/Detail.html',
            'Resources/Private/Extensions/News/Templates/News/MagazineList.html',
            'Resources/Private/Extensions/News/Partials/List/Item.html',
            'Resources/Private/Extensions/News/Partials/List/Pagination.html',
            'Resources/Private/Extensions/News/Partials/List/LoadMore.html',
            'Resources/Private/Extensions/News/Partials/Taxonomy.html',
            'Resources/Private/Extensions/News/Partials/General/NewsIcons.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaContainer.html',
            'Resources/Private/Extensions/News/Partials/Detail/Opengraph.html',
            'Resources/Private/Extensions/News/Partials/Detail/StructuredData.html',
            'Resources/Private/Extensions/News/Partials/Detail/Shariff.html',
        ];

        foreach ($requiredFiles as $relativePath) {
            $path = __DIR__ . '/../../' . $relativePath;
            self::assertFileExists($path, "{$relativePath} must exist");
        }

        foreach ([
            'Resources/Private/Extensions/News/Templates/News/List.html',
            'Resources/Private/Extensions/News/Templates/News/Detail.html',
            'Resources/Private/Extensions/News/Partials/List/Item.html',
        ] as $relativePath) {
            $template = (string) file_get_contents(__DIR__ . '/../../' . $relativePath);
            self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $template, "{$relativePath} should use Desiderio Fluid components");
        }

        $newsTypoScript = (string) file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioNews/setup.typoscript');
        self::assertStringContainsString('structuredData {', $newsTypoScript);
        self::assertStringContainsString('enabled = {$desiderio.seo.structuredDataEnabled}', $newsTypoScript);
        self::assertStringContainsString('publisherName = {$desiderio.brand.wordmark}', $newsTypoScript);
        self::assertStringContainsString('publisherLogo = {$desiderio.seo.defaultImage}', $newsTypoScript);

        $detailLayout = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Layouts/Detail.html');
        $detailTemplate = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Templates/News/Detail.html');
        $listItem = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Partials/List/Item.html');
        $magazineList = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Templates/News/MagazineList.html');
        $taxonomy = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Partials/Taxonomy.html');
        $newsIcons = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Partials/General/NewsIcons.html');
        $structuredData = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Partials/Detail/StructuredData.html');

        self::assertStringContainsString('https://schema.org/NewsArticle', $detailLayout);
        self::assertStringContainsString('partial="Detail/StructuredData"', $detailTemplate);
        self::assertStringContainsString('partial="Taxonomy"', $detailTemplate);
        self::assertStringContainsString('itemprop="dateModified"', $detailTemplate);
        self::assertStringContainsString('partial="Taxonomy"', $listItem);
        self::assertStringContainsString('partial="Taxonomy"', $magazineList);
        self::assertStringContainsString('partial="General/NewsIcons" section="Category"', $taxonomy);
        self::assertStringContainsString('partial="General/NewsIcons" section="Tag"', $taxonomy);
        foreach (['Author', 'Category', 'Tag', 'Published', 'Comment'] as $iconSection) {
            self::assertStringContainsString('<f:section name="' . $iconSection . '">', $newsIcons);
        }
        foreach (['name="user"', 'name="folder"', 'name="tag"', 'name="calendar"', 'name="message-circle"'] as $iconName) {
            self::assertStringContainsString($iconName, $newsIcons);
        }
        self::assertStringContainsString('https://schema.org/NewsArticle', $listItem);
        self::assertStringContainsString('https://schema.org/NewsArticle', $magazineList);
        self::assertStringContainsString('value="{newsItem.falMedia.0}"', $listItem);
        self::assertStringContainsString('<f:link.action action="detail" controller="News" extensionName="News" pluginName="Pi1"', $listItem);
        self::assertStringContainsString('aspect-[4/3]', $listItem);
        self::assertStringContainsString('width="900"', $listItem);
        self::assertStringNotContainsString('settings.list.media.image.maxHeight', $listItem);
        self::assertStringNotContainsString('dummyImage', $listItem);
        self::assertStringContainsString('lg:grid-cols-3', (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Partials/List/LoadMore.html'));
        self::assertStringContainsString('lg:grid-cols-3', (string) file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/News/Partials/Detail/MediaContainer.html'));
        self::assertStringContainsString('<n:headerData>', $structuredData);
        self::assertStringContainsString('type="application/ld+json"', $structuredData);
        self::assertStringContainsString('"@type": "NewsArticle"', $structuredData);
        self::assertStringContainsString('"publisher"', $structuredData);
        self::assertStringContainsString('newsItem.falMedia.0', $structuredData);
        self::assertStringNotContainsString('fallbackImage', $structuredData);
        self::assertStringNotContainsString('-> f:format.json()', $structuredData);
        self::assertStringNotContainsString('{f:format.date(date:', $structuredData);
        self::assertStringContainsString('f:format.json', $structuredData);
        self::assertStringContainsString('<f:format.raw>', $structuredData);

        $newsTemplateDirectory = __DIR__ . '/../../Resources/Private/Extensions/News';
        $newsTemplateFiles = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($newsTemplateDirectory, \FilesystemIterator::SKIP_DOTS),
        );
        foreach ($newsTemplateFiles as $templateFile) {
            if (!$templateFile instanceof \SplFileInfo) {
                continue;
            }

            if (!$templateFile->isFile() || $templateFile->getExtension() !== 'html') {
                continue;
            }

            $template = (string) file_get_contents($templateFile->getPathname());
            if (!str_contains($template, '<n:') && !str_contains($template, '{n:')) {
                continue;
            }

            $relativePath = str_replace(__DIR__ . '/../../', '', $templateFile->getPathname());
            self::assertStringContainsString('xmlns:n="http://typo3.org/ns/GeorgRinger/News/ViewHelpers"', $template, "{$relativePath} must declare the News view helper namespace");
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

        foreach (['news.loadMore.status', 'news.magazine.items', 'news.comments.count', 'news.tags.count', 'news.categories.count', 'blog.comments.count'] as $unitId) {
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
            'Resources/Private/Extensions/News/Partials/Taxonomy.html',
            'Resources/Private/Extensions/News/Partials/Category/Items.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaContainer.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaImage.html',
            'Resources/Private/Extensions/News/Partials/Detail/MediaVideo.html',
            'Resources/Private/Extensions/News/Partials/Detail/Opengraph.html',
            'Resources/Private/Extensions/News/Partials/Detail/StructuredData.html',
            'Resources/Private/Extensions/News/Partials/Detail/Shariff.html',
            'Resources/Private/Solr/Partials/Search/Form.html',
            'Resources/Private/Solr/Partials/Search/FrequentlySearched.html',
            'Resources/Private/Solr/Partials/Search/LastSearches.html',
            'Resources/Private/Solr/Partials/Result/Document.html',
            'Resources/Private/Solr/Partials/Result/Pagination.html',
            'Resources/Private/Solr/Partials/Result/Facets.html',
            'Resources/Private/Solr/Partials/Result/FacetsActive.html',
            'Resources/Private/Solr/Partials/Result/Sorting.html',
            'Resources/Private/Solr/Partials/Result/PerPage.html',
            'Resources/Private/Solr/Partials/Facets/Options.html',
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
        $footerPartial = (string) file_get_contents(__DIR__ . '/../../Resources/Private/Templates/Partials/Pages/Footer.fluid.html');
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
        self::assertStringContainsString('desiderio.brand.wordmark', $headerPartial);
        self::assertStringContainsString('desiderio-header__brand-mark', $headerPartial);
        self::assertStringContainsString('desiderio-header__logo-text', $headerPartial);
        self::assertStringContainsString('aria-controls="desiderio-main-nav"', $headerPartial);
        self::assertStringContainsString("aria-current: \\'page\\'", $headerPartial, 'Active nav links must declare aria-current="page" via additionalAttributes.');
        self::assertStringContainsString('aria-pressed="false"', $headerPartial);
        self::assertStringContainsString('data-d-theme-switch', $headerPartial);
        self::assertStringContainsString('theme.system', $headerPartial);
        self::assertStringContainsString('<d:atom.icon', $headerPartial);
        self::assertStringContainsString('a11y.menu.toggle', $headerPartial);
        self::assertStringContainsString('a11y.theme.switch', $headerPartial);
        self::assertStringContainsString('a11y.nav.language', $headerPartial);
        self::assertStringContainsString('desiderio-header__lang', $headerPartial);
        self::assertMatchesRegularExpression(
            '/<ul[^>]*role="list"/',
            $headerPartial,
            'Main nav <ul> must carry role="list" for VoiceOver/Safari list semantics.'
        );

        // Footer must share the same preset-aware brand and keep navigational chrome.
        self::assertStringContainsString('desiderio.brand.wordmark', $footerPartial);
        self::assertStringContainsString('desiderio.brand.tagline', $footerPartial);
        self::assertStringContainsString('desiderio-footer__wordmark desiderio-footer__title', $footerPartial);
        self::assertStringContainsString('desiderio-footer__tagline', $footerPartial);
        self::assertStringContainsString('a11y.nav.footer', $footerPartial);
        self::assertStringContainsString('a11y.nav.legal', $footerPartial);

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
            'a11y.theme.switch',
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
            'Resources/Private/Solr/Partials/Result/Pagination.html',
            'Resources/Private/Solr/Partials/Search/FrequentlySearched.html',
            'Resources/Private/Solr/Partials/Search/LastSearches.html',
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

    /**
     * @return array<mixed>
     */
    private static function parseYamlArray(string $path): array
    {
        $data = Yaml::parseFile($path);

        return self::requireArray($data);
    }

    /**
     * @return array<mixed>
     */
    private static function requireArray(mixed $value): array
    {
        self::assertIsArray($value);

        return $value;
    }
}
