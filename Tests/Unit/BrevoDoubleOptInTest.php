<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;
use Webconsulting\Desiderio\Domain\Finishers\BrevoConfigurationResolver;

final class BrevoDoubleOptInTest extends TestCase
{
    public function testResolverExposesDoubleOptInConfiguration(): void
    {
        $resolver = new BrevoConfigurationResolver();
        $configuration = $resolver->resolve([
            'doubleOptInTemplateId' => '42',
            'doubleOptInRedirectUrl' => 'https://example.com/welcome',
        ], null);

        self::assertSame(42, $configuration['doubleOptInTemplateId']);
        self::assertSame('https://example.com/welcome', $configuration['doubleOptInRedirectUrl']);
    }

    public function testResolverDefaultsDoubleOptInToDisabled(): void
    {
        $resolver = new BrevoConfigurationResolver();
        $configuration = $resolver->resolve([], null);

        self::assertSame(0, $configuration['doubleOptInTemplateId']);
        self::assertSame('', $configuration['doubleOptInRedirectUrl']);
    }

    public function testParsePositiveIntRejectsInvalidValues(): void
    {
        $resolver = new BrevoConfigurationResolver();

        self::assertSame(7, $resolver->parsePositiveInt(7));
        self::assertSame(7, $resolver->parsePositiveInt(' 7 '));
        self::assertSame(0, $resolver->parsePositiveInt(-1));
        self::assertSame(0, $resolver->parsePositiveInt('abc'));
        self::assertSame(0, $resolver->parsePositiveInt(null));
        self::assertSame(0, $resolver->parsePositiveInt('7.5'));
    }

    public function testNewsletterFormOptsIntoDoubleOptIn(): void
    {
        $form = Yaml::parseFile(__DIR__ . '/../../Resources/Private/Forms/DesiderioNewsletter.form.yaml');
        self::assertIsArray($form);
        $finishers = $form['finishers'] ?? null;
        self::assertIsArray($finishers);

        $brevoOptions = null;
        foreach ($finishers as $finisher) {
            if (is_array($finisher) && ($finisher['identifier'] ?? null) === 'BrevoContact') {
                $brevoOptions = $finisher['options'] ?? null;
            }
        }

        self::assertIsArray($brevoOptions);
        self::assertTrue($brevoOptions['doubleOptIn'] ?? null, 'Newsletter signups must request the Brevo double opt-in flow.');
    }

    public function testSiteSettingsDefineDoubleOptInKeys(): void
    {
        $definitions = Yaml::parseFile(__DIR__ . '/../../Configuration/Sets/Desiderio/settings.definitions.yaml');
        self::assertIsArray($definitions);
        $settings = $definitions['settings'] ?? null;
        self::assertIsArray($settings);

        self::assertArrayHasKey('desiderio.forms.brevo.doubleOptInTemplateId', $settings);
        self::assertArrayHasKey('desiderio.forms.brevo.doubleOptInRedirectUrl', $settings);
    }
}
