<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Command\PowermailDemoSeeder;
use Webconsulting\Desiderio\Data\PowermailDemoFormDefinitions;
use Webconsulting\Desiderio\Seeding\DatabaseSchemaHelper;

final class PowermailIntegrationTest extends TestCase
{
    public function testPowermailSiteSetExtendsBaseAndPointsToTemplateOverrides(): void
    {
        $config = (string)file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioPowermail/config.yaml');
        $setup = (string)file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioPowermail/setup.typoscript');
        $baseConfig = (string)file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');
        $contentTemplate = (string)file_get_contents(__DIR__ . '/../../Resources/Private/FluidStyledContent/Templates/PowermailPi1.fluid.html');

        self::assertStringContainsString('name: webconsulting/desiderio-powermail', $config);
        self::assertMatchesRegularExpression('/dependencies:\s+- webconsulting\/desiderio\s+- in2code\/powermail-main\s+- studiomitte\/friendlycaptcha/s', $config);
        self::assertStringNotContainsString('optionalDependencies:', $config);
        self::assertStringNotContainsString('webconsulting/desiderio-powermail', $baseConfig);
        self::assertStringContainsString('plugin.tx_powermail', $setup);
        self::assertStringContainsString('office@webconsulting.at', $setup);
        self::assertStringContainsString('htmlForHtmlFields = 1', $setup);
        self::assertStringContainsString('tt_content.powermail_pi1', $setup);
        self::assertStringContainsString('templateName = PowermailPi1', $setup);
        self::assertStringContainsString('20 = EXTBASEPLUGIN', $setup);
        self::assertStringContainsString('extensionName = Powermail', $setup);
        self::assertStringContainsString('pluginName = Pi1', $setup);
        self::assertStringNotContainsString('lib.desiderioPowermailPi1', $setup);
        self::assertStringNotContainsString('controller = Form', $setup);
        self::assertStringNotContainsString('action = form', $setup);
        self::assertStringContainsString('tt_content.{data.CType}.20', $contentTemplate);
        self::assertStringContainsString('ce-fsc-powermail', $contentTemplate);
        self::assertStringContainsString('partialRootPaths.1000000 = EXT:desiderio/Resources/Private/Extensions/Powermail/Partials/', $setup);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Extensions/Powermail/Templates/', $setup);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Extensions/Powermail/Partials/', $setup);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Extensions/Powermail/Layouts/', $setup);
    }

    public function testPowermailTemplatesUseFluidArgumentsAndDesiderioComponents(): void
    {
        $files = [
            'Layouts/Default.html',
            'Templates/Form/Form.html',
            'Templates/Form/Create.html',
            'Templates/Form/Confirmation.html',
            'Partials/Form/Page.html',
            'Partials/Form/ShadcnClass.html',
            'Partials/Form/FieldLabel.html',
            'Partials/Form/Field/Input.html',
            'Partials/Form/Field/Select.html',
            'Partials/Form/Field/Check.html',
            'Partials/Form/Field/Radio.html',
            'Partials/Form/Field/Textarea.html',
            'Partials/Form/Field/File.html',
            'Partials/Form/Field/Friendlycaptcha.html',
            'Partials/Form/Field/Submit.html',
        ];

        foreach ($files as $file) {
            $path = __DIR__ . '/../../Resources/Private/Extensions/Powermail/' . $file;
            self::assertFileExists($path);
            $content = (string)file_get_contents($path);
            self::assertStringContainsString('<f:argument', $content, "{$file} must declare typed Fluid arguments");
        }

        $form = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Templates/Form/Form.html');
        self::assertStringContainsString('Webconsulting/Desiderio/Components/ComponentCollection', $form);
        self::assertStringContainsString('<d:layout.container', $form);
        self::assertStringContainsString('<d:molecule.cardContent', $form);
        self::assertStringContainsString('<d:atom.controlClass slot="card"', $form);
        self::assertStringContainsString('data-slot="card-header"', $form);
        self::assertStringNotContainsString('powermail_form powermail_form_', $form);
        self::assertStringContainsString('data-powermail-morestep-show', $form);
        self::assertStringContainsString('data-state="{f:if(condition: iterationPages.isFirst', $form);
        self::assertStringContainsString('aria-current="{f:if(condition: iterationPages.isFirst', $form);

        $input = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Input.html');
        $fieldLabel = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/FieldLabel.html');
        self::assertStringContainsString('<d:molecule.field', $input);
        self::assertStringContainsString('<d:atom.controlClass slot="input"', $input);
        self::assertStringContainsString('border-destructive ring-1 ring-destructive/20', $input);
        self::assertStringNotContainsString('powermail_input', $input);
        self::assertStringContainsString('<d:molecule.fieldLabel', $fieldLabel);
        self::assertStringNotContainsString('ms-1 text-destructive', $fieldLabel);

        $select = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Select.html');
        self::assertStringContainsString('<d:molecule.selectNative', $select);
        self::assertStringContainsString('<di:icon name="chevron-down"', $select);
        self::assertStringContainsString('<d:atom.controlClass slot="selectNative"', $select);
        self::assertStringContainsString('<d:atom.controlClass slot="selectIcon"', $select);

        $checkbox = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Check.html');
        self::assertStringContainsString('<d:molecule.checkboxControl', $checkbox);
        self::assertStringContainsString('xmlns:di="http://typo3.org/ns/Webconsulting/Desiderio/ViewHelpers"', $checkbox);
        self::assertStringContainsString('<di:icon name="check"', $checkbox);
        self::assertStringContainsString('<d:atom.controlClass slot="checkboxInput"', $checkbox);
        self::assertStringContainsString('<d:molecule.optionLabel', $checkbox);
        self::assertStringContainsString('<d:molecule.fieldLegend', $checkbox);
        self::assertStringNotContainsString('ms-1 text-destructive', $checkbox);
        self::assertStringNotContainsString('powermail_checkbox', $checkbox);
        self::assertStringNotContainsString('<f:render partial="Form/FieldLabel"', $checkbox);

        $radio = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Radio.html');
        self::assertStringContainsString('<d:molecule.fieldLegend', $radio);
        self::assertStringNotContainsString('ms-1 text-destructive', $radio);

        $html = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Html.html');
        self::assertStringContainsString('settings.misc.htmlForHtmlFields', $html);
        self::assertStringContainsString('<f:sanitize.html>{field.text -> f:format.raw()}</f:sanitize.html>', $html);

        $controlClass = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Components/Atom/ControlClass/ControlClass.fluid.html');
        self::assertStringContainsString('Generated by Build/Scripts/sync-shadcn-fluid-primitives.php', $controlClass);
        self::assertStringContainsString('<f:argument name="slot" type="string" />', $controlClass);
        self::assertStringContainsString('<f:case value="checkboxInput">', $controlClass);
        self::assertStringContainsString('<f:case value="radioInput">', $controlClass);
        self::assertStringContainsString('<f:case value="selectNative">', $controlClass);
        self::assertStringContainsString('<f:case value="selectIcon">', $controlClass);
        self::assertStringContainsString('<f:case value="buttonDefault">', $controlClass);
        self::assertStringContainsString('checked:border-foreground', $controlClass);
        self::assertStringContainsString('checked:bg-foreground', $controlClass);
        self::assertStringContainsString('checked:text-background', $controlClass);
        self::assertStringContainsString('aria-invalid:checked:border-destructive', $controlClass);
        self::assertStringNotContainsString('checked:border-primary', $controlClass);
        self::assertStringNotContainsString('checked:bg-primary', $controlClass);
        self::assertStringNotContainsString('aria-invalid:checked:border-primary', $controlClass);
        self::assertStringNotContainsString('has-data-checked:border-primary', $controlClass);
        self::assertStringNotContainsString('has-data-checked:bg-primary', $controlClass);

        $shadcnClass = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/ShadcnClass.html');
        self::assertSame($controlClass, $shadcnClass, 'Powermail ShadcnClass partial must stay synchronized with d:atom.controlClass');

        $componentsCss = (string)file_get_contents(__DIR__ . '/../../Resources/Public/Css/components.css');
        self::assertStringContainsString('.d-powermail :where(input.d-shadcn-control, textarea.d-shadcn-control, select.d-shadcn-control)', $componentsCss);
        self::assertStringContainsString('border-color: var(--input);', $componentsCss);
        self::assertStringContainsString('border-color: var(--destructive);', $componentsCss);
        self::assertStringContainsString('accent-color: var(--foreground);', $componentsCss);
        self::assertStringContainsString('.d-powermail .powermail-errors-list', $componentsCss);
        self::assertStringContainsString('content: "!";', $componentsCss);

        $flashMessages = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Misc/FlashMessages.html');
        self::assertStringContainsString('as="flashMessages"', $flashMessages);
        self::assertStringNotContainsString('<f:flashMessages queueIdentifier="extbase.flashmessages.tx_powermail_pi1" class=', $flashMessages);

        $page = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Page.html');
        self::assertStringContainsString('<f:argument name="iterationPages" type="array"', $page);
        self::assertStringContainsString('powermail_fieldset_{page.uid}', $page);
        self::assertStringContainsString('data-powermail-morestep-show="{iterationPages.index - 1}"', $page);
        self::assertStringContainsString("{field.type} != 'submit'", $page);
        self::assertStringContainsString('{iterationPages.isLast}', $page);
        self::assertStringContainsString("class=\"ms-auto\"", $page);
        self::assertStringContainsString("{field.type} == 'submit'", $page);

        $javascript = (string)file_get_contents(__DIR__ . '/../../Resources/Public/Js/desiderio.js');
        self::assertStringContainsString('Powermail multi-step state', $javascript);
        self::assertStringContainsString('powermailErrorSelector', $javascript);
        self::assertStringContainsString('dataset.powermailMorestepCurrent', $javascript);
        self::assertStringContainsString('MutationObserver', $javascript);

        $friendlyCaptcha = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Friendlycaptcha.html');
        self::assertStringContainsString('friendlycaptcha:configuration()', $friendlyCaptcha);
        self::assertStringContainsString('di:friendlyCaptchaTestModeEnabled()', $friendlyCaptcha);
        self::assertStringContainsString('identifier="friendlycaptcha"', $friendlyCaptcha);
        self::assertStringContainsString('friendlyCaptchaTestMode', $friendlyCaptcha);
        self::assertStringContainsString('class="frc-captcha"', $friendlyCaptcha);
        self::assertStringContainsString('configuration_missing', $friendlyCaptcha);
    }

    public function testPowermailTemplatesDeclareDesiderioComponentNamespaceWhenUsingDViewHelpers(): void
    {
        $componentNamespace = 'xmlns:d="http://typo3.org/ns/Webconsulting/Desiderio/Components/ComponentCollection"';
        $root = __DIR__ . '/../../Resources/Private/Extensions/Powermail';
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $fileInfo) {
            if (!$fileInfo instanceof \SplFileInfo || $fileInfo->getExtension() !== 'html') {
                continue;
            }

            $path = $fileInfo->getPathname();
            $content = (string)file_get_contents($path);
            if (!str_contains($content, '<d:')) {
                continue;
            }

            self::assertStringContainsString(
                $componentNamespace,
                $content,
                $path . ' uses d: components but does not declare the Desiderio component namespace'
            );
        }
    }

    public function testPowermailTranslationsAreXliff20InEnglishAndGerman(): void
    {
        foreach (['powermail.xlf', 'de.powermail.xlf'] as $file) {
            $path = __DIR__ . '/../../Resources/Private/Language/' . $file;
            self::assertFileExists($path);
            $xml = simplexml_load_file($path);
            self::assertNotFalse($xml);
            self::assertSame('2.0', (string)$xml['version']);
            self::assertStringContainsString('powermail.form.requiredHint', (string)file_get_contents($path));
            self::assertStringContainsString('plural,', (string)file_get_contents($path));
        }
    }

    public function testPowermailDemoSeederDefinesFiveStandardFormsWithFriendlyCaptcha(): void
    {
        $seeder = new PowermailDemoSeeder(
            $this->createMock(ConnectionPool::class),
            new DatabaseSchemaHelper($this->createMock(ConnectionPool::class)),
        );
        $forms = $seeder->getDemoForms();

        self::assertCount(5, $forms);
        self::assertSame(['contact', 'newsletter', 'callback', 'appointment', 'support'], array_column($forms, 'slug'));
        self::assertFalse($forms[0]['moresteps']);
        self::assertFalse($forms[2]['moresteps']);
        self::assertTrue($forms[3]['moresteps']);
        self::assertIsArray($forms[4]['pages']);
        self::assertCount(2, $forms[4]['pages']);

        $captchaFields = 0;
        foreach ($forms as $form) {
            foreach ($form['pages'] as $page) {
                foreach ($page['fields'] as $field) {
                    if ($field['type'] === 'friendlycaptcha') {
                        $captchaFields++;
                    }
                }
            }
        }
        self::assertSame(5, $captchaFields);
        self::assertSame($forms, PowermailDemoFormDefinitions::demoForms());

        $definitionsSource = (string)file_get_contents(__DIR__ . '/../../Classes/Data/PowermailDemoFormDefinitions.php');
        self::assertStringContainsString("'slug' => 'support'", $definitionsSource);
        self::assertStringContainsString("'friendlycaptcha'", $definitionsSource);

        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/PowermailDemoSeeder.php');
        self::assertStringContainsString('office@webconsulting.at', $source);
        self::assertStringContainsString("'/desiderio-powermail/' . \$form['slug'] . '/thank-you'", $source);
        self::assertStringContainsString("'nav_hide' => (int)\$navHide", $source);
        self::assertStringContainsString('$this->hidePages($ownedPageUids, $now, $pageColumns);', $source);
    }
}
