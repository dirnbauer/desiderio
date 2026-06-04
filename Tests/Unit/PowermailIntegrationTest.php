<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Command\PowermailDemoSeeder;

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
        self::assertStringContainsString('lib.desiderioPowermailPi1 = EXTBASEPLUGIN', $setup);
        self::assertStringContainsString('controller = Form', $setup);
        self::assertStringContainsString('action = form', $setup);
        self::assertStringContainsString('lib.desiderioPowermailPi1', $contentTemplate);
        self::assertStringContainsString('ce-fsc-powermail', $contentTemplate);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Extensions/Powermail/Templates/', $setup);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Extensions/Powermail/Partials/', $setup);
        self::assertStringContainsString('EXT:desiderio/Resources/Private/Extensions/Powermail/Layouts/', $setup);
    }

    public function testPowermailTemplatesUseFluidArgumentsAndShadcnClasses(): void
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
        self::assertStringContainsString('mt-8 w-full max-w-2xl', $form);
        self::assertStringContainsString("slot: 'card'", $form);
        self::assertStringContainsString("slot: 'cardHeaderBordered'", $form);
        self::assertStringContainsString("slot: 'tabsList'", $form);
        self::assertStringContainsString('data-slot="card-header"', $form);
        self::assertStringNotContainsString('powermail_form powermail_form_', $form);
        self::assertStringContainsString('data-powermail-morestep-show', $form);
        self::assertStringContainsString('data-state="{f:if(condition: iterationPages.isFirst', $form);
        self::assertStringContainsString('aria-current="{f:if(condition: iterationPages.isFirst', $form);

        $input = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Input.html');
        $fieldLabel = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/FieldLabel.html');
        self::assertStringContainsString('data-slot="field"', $input);
        self::assertStringContainsString("slot: 'field'", $input);
        self::assertStringContainsString("slot: 'input'", $input);
        self::assertStringContainsString('border-destructive ring-1 ring-destructive/20', $input);
        self::assertStringNotContainsString('powermail_input', $input);
        self::assertStringContainsString('inline-flex items-baseline gap-1', $fieldLabel);
        self::assertStringNotContainsString('ms-1 text-destructive', $fieldLabel);

        $select = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Select.html');
        self::assertStringContainsString('data-slot="native-select"', $select);
        self::assertStringContainsString('<di:icon name="chevron-down"', $select);
        self::assertStringContainsString("slot: 'selectNative'", $select);
        self::assertStringContainsString("slot: 'selectIcon'", $select);
        self::assertStringContainsString("slot: 'selectWrapper'", $select);

        $checkbox = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Check.html');
        self::assertStringContainsString('data-slot="checkbox"', $checkbox);
        self::assertStringContainsString('xmlns:di="http://typo3.org/ns/Webconsulting/Desiderio/ViewHelpers"', $checkbox);
        self::assertStringContainsString('<di:icon name="check"', $checkbox);
        self::assertStringContainsString("slot: 'checkboxInput'", $checkbox);
        self::assertStringContainsString("slot: 'checkboxIcon'", $checkbox);
        self::assertStringContainsString("slot: 'optionLabel'", $checkbox);
        self::assertStringContainsString('data-slot="field-legend"', $checkbox);
        self::assertStringContainsString("slot: 'fieldLegend'", $checkbox);
        self::assertStringContainsString('inline-flex items-baseline gap-1', $checkbox);
        self::assertStringNotContainsString('ms-1 text-destructive', $checkbox);
        self::assertStringNotContainsString('powermail_checkbox', $checkbox);
        self::assertStringNotContainsString('<f:render partial="Form/FieldLabel"', $checkbox);

        $radio = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Radio.html');
        self::assertStringContainsString('inline-flex items-baseline gap-1', $radio);
        self::assertStringNotContainsString('ms-1 text-destructive', $radio);

        $html = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Html.html');
        self::assertStringContainsString('settings.misc.htmlForHtmlFields', $html);
        self::assertStringContainsString('<f:sanitize.html>{field.text -> f:format.raw()}</f:sanitize.html>', $html);

        $shadcnClass = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/ShadcnClass.html');
        self::assertStringContainsString('Generated by Build/Scripts/sync-shadcn-fluid-primitives.php', $shadcnClass);
        self::assertStringContainsString('<f:argument name="slot" type="string" />', $shadcnClass);
        self::assertStringContainsString('<f:case value="checkboxInput">', $shadcnClass);
        self::assertStringContainsString('<f:case value="radioInput">', $shadcnClass);
        self::assertStringContainsString('<f:case value="selectNative">', $shadcnClass);
        self::assertStringContainsString('<f:case value="selectIcon">', $shadcnClass);
        self::assertStringContainsString('<f:case value="buttonDefault">', $shadcnClass);
        self::assertStringContainsString('checked:border-foreground', $shadcnClass);
        self::assertStringContainsString('checked:bg-foreground', $shadcnClass);
        self::assertStringContainsString('checked:text-background', $shadcnClass);
        self::assertStringContainsString('aria-invalid:checked:border-destructive', $shadcnClass);
        self::assertStringContainsString('<f:case value="fieldLegend">', $shadcnClass);
        self::assertStringNotContainsString('checked:border-primary', $shadcnClass);
        self::assertStringNotContainsString('checked:bg-primary', $shadcnClass);
        self::assertStringNotContainsString('aria-invalid:checked:border-primary', $shadcnClass);
        self::assertStringNotContainsString('has-data-checked:border-primary', $shadcnClass);
        self::assertStringNotContainsString('has-data-checked:bg-primary', $shadcnClass);

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
        self::assertStringContainsString('identifier="friendlycaptcha"', $friendlyCaptcha);
        self::assertStringContainsString('class="frc-captcha"', $friendlyCaptcha);
        self::assertStringContainsString('configuration_missing', $friendlyCaptcha);
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
        $seeder = new PowermailDemoSeeder($this->createMock(ConnectionPool::class));
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

        $source = (string)file_get_contents(__DIR__ . '/../../Classes/Command/PowermailDemoSeeder.php');
        self::assertStringContainsString('office@webconsulting.at', $source);
        self::assertStringContainsString("'/desiderio-powermail/' . \$form['slug'] . '/thank-you'", $source);
        self::assertStringContainsString("'nav_hide' => (int)\$navHide", $source);
        self::assertStringContainsString('$this->hidePages($ownedPageUids, $now, $pageColumns);', $source);
    }
}
