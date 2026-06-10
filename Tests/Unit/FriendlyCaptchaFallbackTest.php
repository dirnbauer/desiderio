<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Domain\Validator\FriendlyCaptchaFallbackValidator;

final class FriendlyCaptchaFallbackTest extends TestCase
{
    private const PLACEHOLDER_MARKER = 'frc-captcha--placeholder';
    private const BUTTON_LABEL_KEY = 'labels.xlf:captcha.placeholder.button';

    public function testExtLocalconfRegistersFallbackOnlyWithoutFriendlyCaptcha(): void
    {
        $extLocalconf = (string)file_get_contents(__DIR__ . '/../../ext_localconf.php');

        self::assertStringContainsString("isLoaded('friendlycaptcha_official')", $extLocalconf);
        self::assertStringContainsString(
            'plugin.tx_form.settings.yamlConfigurations.1777100143 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml',
            $extLocalconf
        );
        self::assertStringContainsString(
            'module.tx_form.settings.yamlConfigurations.1777100143 = EXT:desiderio/Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml',
            $extLocalconf
        );
    }

    public function testFallbackFormSetupDefinesElementAndAlwaysPassingValidator(): void
    {
        $setup = Yaml::parseFile(__DIR__ . '/../../Configuration/Yaml/FriendlyCaptchaFallbackFormSetup.yaml');
        self::assertIsArray($setup);

        $prototype = self::assertArrayPath($setup, 'TYPO3', 'CMS', 'Form', 'prototypes', 'standard');

        $element = self::assertArrayPath($prototype, 'formElementsDefinition', 'Friendlycaptcha');
        self::assertSame(
            \TYPO3\CMS\Form\Domain\Model\FormElements\GenericFormElement::class,
            $element['implementationClassName'] ?? null
        );

        $partialRootPaths = self::assertArrayPath($prototype, 'formElementsDefinition', 'Form', 'renderingOptions', 'partialRootPaths');
        self::assertContains('EXT:desiderio/Resources/Private/Form/Partials', $partialRootPaths);

        $validator = self::assertArrayPath($prototype, 'validatorsDefinition', 'Friendlycaptcha');
        self::assertSame(FriendlyCaptchaFallbackValidator::class, $validator['implementationClassName'] ?? null);
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private static function assertArrayPath(array $data, string ...$keys): array
    {
        foreach ($keys as $key) {
            self::assertArrayHasKey($key, $data);
            $value = $data[$key];
            self::assertIsArray($value);
            $data = $value;
        }

        return $data;
    }

    public function testFallbackValidatorAcceptsAnyValue(): void
    {
        $validator = new FriendlyCaptchaFallbackValidator();

        self::assertFalse($validator->validate('')->hasErrors());
        self::assertFalse($validator->validate('1')->hasErrors());
        self::assertFalse($validator->validate(null)->hasErrors());
    }

    public function testFallbackPartialRendersInertPlaceholderButton(): void
    {
        $partial = (string)file_get_contents(__DIR__ . '/../../Resources/Private/Form/Partials/Friendlycaptcha.html');

        self::assertStringContainsString(self::PLACEHOLDER_MARKER, $partial);
        self::assertStringContainsString('<button type="button" disabled', $partial);
        self::assertStringContainsString(self::BUTTON_LABEL_KEY, $partial);
        self::assertStringContainsString('f:form.hidden', $partial);
        // The button is decorative: no JavaScript hooks of any kind.
        self::assertStringNotContainsString('onclick', $partial);
        self::assertStringNotContainsString('<script', $partial);
    }

    public function testPowermailAndBlogTestModeBranchesRenderPlaceholder(): void
    {
        $powermail = (string)file_get_contents(
            __DIR__ . '/../../Resources/Private/Extensions/Powermail/Partials/Form/Field/Friendlycaptcha.html'
        );
        $blog = (string)file_get_contents(
            __DIR__ . '/../../Resources/Private/Extensions/Blog/Partials/Form/Friendlycaptcha.html'
        );

        foreach ([$powermail, $blog] as $template) {
            self::assertStringContainsString(self::PLACEHOLDER_MARKER, $template);
            self::assertStringContainsString('<button type="button" disabled', $template);
            self::assertStringContainsString(self::BUTTON_LABEL_KEY, $template);
        }
    }
}
