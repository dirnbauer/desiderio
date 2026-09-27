<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
use TYPO3\CMS\Core\Configuration\Tca\TcaMigration;
use TYPO3\CMS\Core\Configuration\Tca\TcaPreparation;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use Webconsulting\Desiderio\Command\PowermailDemoSeeder;

/**
 * Reseeding the Powermail demo replaces its forms with new records. Content
 * outside the demo pages that used one (the element library's preview, a page
 * an editor built) must follow to the new uid, or Powermail answers it with
 * "choose a form".
 */
final class PowermailDemoFormRepointTest extends TestCase
{
    private const string FLEXFORM = <<<'XML'
        <?xml version="1.0" encoding="utf-8" standalone="yes" ?>
        <T3FlexForms>
            <data>
                <sheet index="main">
                    <language index="lDEF">
                        <field index="settings.flexform.main.form"><value index="vDEF">%d</value></field>
                        <field index="settings.flexform.main.pid"><value index="vDEF">712</value></field>
                    </language>
                </sheet>
            </data>
        </T3FlexForms>
        XML;

    public static function setUpBeforeClass(): void
    {
        // FlexFormTools writes line breaks as LF, which TYPO3's bootstrap defines; this suite has none.
        if (!defined('LF')) {
            define('LF', chr(10));
        }
    }

    public function testAReplacedFormIsSwappedForItsSuccessor(): void
    {
        $repointed = PowermailDemoSeeder::repointFlexform(
            sprintf(self::FLEXFORM, 978),
            [978 => 'appointment:default', 979 => 'appointment:german'],
            ['appointment:default' => 1203, 'appointment:german' => 1204],
        );

        self::assertSame(sprintf(self::FLEXFORM, 1203), $repointed);
        self::assertStringContainsString('<value index="vDEF">712</value>', $repointed, 'Other fields stay as they are');
    }

    /**
     * The seeder now writes its plugins as the backend does, with each value on a line of its
     * own, and so does every plugin an editor saved.
     */
    public function testAPluginInTheBackendLayoutIsRepointedToo(): void
    {
        $flexFormTools = new FlexFormTools(new NoopEventDispatcher(), new TcaMigration(), new TcaPreparation());
        $flexform = static fn(int $form): string => $flexFormTools->flexArray2Xml(['data' => ['main' => ['lDEF' => [
            'settings.flexform.main.form' => ['vDEF' => (string)$form],
            'settings.flexform.main.pid' => ['vDEF' => '712'],
        ]]]]);

        self::assertSame(
            $flexform(1203),
            PowermailDemoSeeder::repointFlexform($flexform(978), [978 => 'appointment:default'], ['appointment:default' => 1203]),
        );
    }

    public function testAFormTheRunDidNotReplaceIsLeftAlone(): void
    {
        $flexform = sprintf(self::FLEXFORM, 42);

        self::assertSame($flexform, PowermailDemoSeeder::repointFlexform($flexform, [978 => 'contact:default'], ['contact:default' => 1203]));
        self::assertSame($flexform, PowermailDemoSeeder::repointFlexform($flexform, [], []));
    }

    public function testAReplacedFormWithoutSuccessorIsLeftAlone(): void
    {
        $flexform = sprintf(self::FLEXFORM, 978);

        self::assertSame($flexform, PowermailDemoSeeder::repointFlexform($flexform, [978 => 'retired:default'], ['contact:default' => 1203]));
    }
}
