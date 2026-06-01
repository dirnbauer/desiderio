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

        self::assertStringContainsString('name: webconsulting/desiderio-powermail', $config);
        self::assertStringContainsString('webconsulting/desiderio', $config);
        self::assertStringContainsString('in2code/powermail-main', $config);
        self::assertStringNotContainsString('webconsulting/desiderio-powermail', $baseConfig);
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
        ];

        foreach ($files as $file) {
            $path = __DIR__ . '/../../Resources/Private/Extensions/Powermail/' . $file;
            self::assertFileExists($path);
            $content = (string)file_get_contents($path);
            self::assertStringContainsString('<f:argument', $content, "{$file} must declare typed Fluid arguments");
        }

        $form = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Templates/Form/Form.html');
        // Flat radix-lyra card surface (ring, not border+shadow) — mirrors the generated Card atom.
        self::assertStringContainsString('rounded-none bg-card', $form);
        self::assertStringContainsString('ring-1 ring-foreground/10', $form);
        self::assertStringNotContainsString('rounded-md', $form);
        self::assertStringNotContainsString('shadow-sm', $form);
        self::assertStringContainsString('data-powermail-morestep-show', $form);

        $input = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Input.html');
        // Inputs mirror the generated Input atom (radix-lyra): flat, compact, ring-1, light/dark capable.
        self::assertStringContainsString('rounded-none border border-input bg-transparent', $input);
        self::assertStringContainsString('focus-visible:ring-1 focus-visible:ring-ring/50', $input);
        self::assertStringContainsString('dark:bg-input/30', $input);
        self::assertStringContainsString('aria-invalid:ring-destructive/20', $input);
        self::assertStringNotContainsString('focus-visible:ring-2', $input);
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
