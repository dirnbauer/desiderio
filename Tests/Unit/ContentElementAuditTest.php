<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Guards the strict standards categories surfaced by
 * scripts/audit-content-elements.php. These categories should always be 0
 * once the catalog is in shape; a non-zero count means a regression that
 * needs fixing in source rather than silently growing technical debt.
 *
 * Soft signals (fixture_missing_field, collection_child_seed_gap) are not
 * asserted here because the seed command auto-fills missing fields with
 * sensible defaults — they are useful as a developer report, not a contract.
 */
final class ContentElementAuditTest extends TestCase
{
    private const STRICT_CATEGORIES = [
        'missing_table_key',
        'fixture_extra_field',
        'template_unused_field',
        'template_undeclared_field',
        'variant_field_inert',
        'hardcoded_inline_style',
        'missing_default_select',
        'hardcoded_color',
        'no_template_at_all',
    ];

    public function testStrictAuditCategoriesAreClean(): void
    {
        $script = dirname(__DIR__, 2) . '/scripts/audit-content-elements.php';
        self::assertFileExists($script);

        $output = shell_exec(escapeshellcmd(PHP_BINARY) . ' ' . escapeshellarg($script));
        self::assertIsString($output);

        $report = json_decode((string) $output, true);
        self::assertIsArray($report);
        self::assertArrayHasKey('summary', $report);
        self::assertArrayHasKey('problems', $report);

        $summary = $report['summary'];
        $problems = $report['problems'];

        $failures = [];
        foreach (self::STRICT_CATEGORIES as $category) {
            self::assertArrayHasKey($category, $summary, "Audit summary missing $category");
            if ($summary[$category] === 0) {
                continue;
            }
            $samples = [];
            foreach ($problems as $element => $issues) {
                foreach ($issues as $issue) {
                    if (($issue['type'] ?? null) === $category) {
                        $samples[] = "$element: " . json_encode(array_diff_key($issue, ['type' => true]));
                        if (count($samples) >= 5) break 2;
                    }
                }
            }
            $failures[] = sprintf('%s: %d (samples: %s)', $category, $summary[$category], implode(' | ', $samples));
        }

        self::assertSame([], $failures, "Strict audit regressions:\n" . implode("\n", $failures));
    }
}
