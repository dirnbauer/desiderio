<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Webconsulting\Desiderio\Command\PowermailDemoSeeder;

final class PowermailIntegrationTest extends TestCase
{
    public function testPowermailSiteSetIsOptionalAndPointsToTemplateOverrides(): void
    {
        $config = (string)file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioPowermail/config.yaml');
        $setup = (string)file_get_contents(__DIR__ . '/../../Configuration/Sets/DesiderioPowermail/setup.typoscript');
        $baseConfig = (string)file_get_contents(__DIR__ . '/../../Configuration/Sets/Desiderio/config.yaml');

        self::assertStringContainsString('name: webconsulting/desiderio-powermail', $config);
        self::assertStringContainsString('in2code/powermail-main', $config);
        self::assertStringContainsString('webconsulting/desiderio-powermail', $baseConfig);
        self::assertStringContainsString('plugin.tx_powermail', $setup);
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
            'Partials/Form/FieldLabel.html',
            'Partials/Form/Field/Input.html',
            'Partials/Form/Field/Select.html',
            'Partials/Form/Field/Check.html',
            'Partials/Form/Field/Radio.html',
            'Partials/Form/Field/Textarea.html',
            'Partials/Form/Field/File.html',
            'Partials/Form/Field/Submit.html',
            'Partials/Form/ShadcnClass.html',
        ];

        foreach ($files as $file) {
            $path = __DIR__ . '/../../Resources/Private/Extensions/Powermail/' . $file;
            self::assertFileExists($path);
            $content = (string)file_get_contents($path);
            self::assertStringContainsString('<f:argument', $content, "{$file} must declare typed Fluid arguments");
        }

        // Preset-dependent control classes live in the generated shared partial
        // (Form/ShadcnClass.html, kept in sync with the shadcn recipe); the field
        // templates reference it instead of hardcoding radix-lyra classes.
        $registry = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/ShadcnClass.html');
        self::assertStringContainsString('rounded-none border border-input bg-transparent', $registry);
        self::assertStringContainsString('focus-visible:ring-1 focus-visible:ring-ring/50', $registry);
        self::assertStringContainsString('dark:bg-input/30', $registry);
        self::assertStringContainsString('aria-invalid:ring-destructive/20', $registry);
        self::assertStringContainsString('<f:case value="radius">rounded-none</f:case>', $registry);
        self::assertStringNotContainsString('focus-visible:ring-2', $registry);
        self::assertStringNotContainsString('rounded-md', $registry);

        $input = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Input.html');
        self::assertStringContainsString("f:render(partial: 'Form/ShadcnClass', arguments: {name: 'input'})", $input);

        // Form surface keeps the flat radix-lyra card (ring, not border+shadow) and pulls its radius from the shared partial.
        $form = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Templates/Form/Form.html');
        self::assertStringContainsString('ring-1 ring-foreground/10', $form);
        self::assertStringNotContainsString('shadow-sm', $form);
        self::assertStringContainsString('data-powermail-morestep-show', $form);
        self::assertStringContainsString("f:render(partial: 'Form/ShadcnClass'", $form);
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
        }
    }

    public function testPowermailDemoSeederDefinesFiveProgressiveForms(): void
    {
        $seeder = new PowermailDemoSeeder($this->createMock(ConnectionPool::class));
        $forms = $seeder->getDemoForms();

        self::assertCount(5, $forms);
        self::assertSame('newsletter', $forms[0]['slug']);
        self::assertSame('project-intake', $forms[4]['slug']);
        self::assertFalse($forms[0]['moresteps']);
        self::assertTrue($forms[4]['moresteps']);
        self::assertIsArray($forms[4]['pages']);
        self::assertCount(3, $forms[4]['pages']);
    }
}
