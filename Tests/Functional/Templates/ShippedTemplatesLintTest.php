<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Functional\Templates;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use Webconsulting\Desiderio\Templates\LintFinding;
use Webconsulting\Desiderio\Templates\LintOptions;
use Webconsulting\Desiderio\Templates\TemplateLinter;

/**
 * The lint gate over every template Desiderio ships: components, content
 * elements, page templates, classic content, extension template sets and
 * form templates. Zero errors is the bar; checks that need EXT:news, EXT:solr,
 * EXT:powermail or EXT:friendlycaptcha_official are skipped because those
 * extensions are optional and not part of the test install. Content Blocks,
 * Visual Editor (+ enhancements) and the Vite asset collector are hard
 * requirements (cb:, f:render.* / f:editMode and vite: ViewHelpers), EXT:blog
 * is a dev dependency; all of them are loaded so every template set is linted
 * in full.
 */
final class ShippedTemplatesLintTest extends FunctionalTestCase
{
    protected array $coreExtensionsToLoad = ['form', 'workspaces'];

    protected array $testExtensionsToLoad = [
        'friendsoftypo3/content-blocks',
        'friendsoftypo3/visual-editor',
        'webconsulting/visual-editor-enhancements',
        'praetorius/vite-asset-collector',
        'webconsulting/desiderio',
        't3g/blog',
    ];

    #[Test]
    public function everyShippedTemplateParsesWithoutErrors(): void
    {
        $report = $this->get(TemplateLinter::class)->lint(new LintOptions(['EXT:desiderio']));

        self::assertGreaterThan(300, $report->getFilesScanned(), 'Content elements, components and template sets must all be scanned');
        self::assertSame([], array_map(
            static fn(LintFinding $finding): string => sprintf('%s:%s [%s] %s', $finding->file, $finding->line ?? '-', $finding->rule, $finding->message),
            $report->getErrors(),
        ));
    }

    #[Test]
    public function skippedChecksOnlyConcernOptionalExtensions(): void
    {
        $report = $this->get(TemplateLinter::class)->lint(new LintOptions(['EXT:desiderio']));

        $unexpected = [];
        foreach ($report->getSkipped() as $finding) {
            if (preg_match('/GeorgRinger\\\\News|In2code\\\\Powermail|ApacheSolrForTypo3\\\\Solr|StudioMitte\\\\FriendlyCaptcha|EXT:(?:news|powermail|solr)\b/', $finding->message) !== 1) {
                $unexpected[] = $finding->file . ': ' . $finding->message;
            }
        }
        self::assertSame([], $unexpected, 'Only the optional integrations may leave unchecked templates behind');
    }
}
