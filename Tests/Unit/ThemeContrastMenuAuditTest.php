<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * menuColor "inverted" re-points the colour tokens of the menu surfaces in
 * 26-menu-surface-tokens.css. The theme contrast audit may only accept those
 * redeclarations because it measures every preset's inverted menu as a theme
 * of its own; these tests prove the menus are measured, not exempted.
 */
final class ThemeContrastMenuAuditTest extends TestCase
{
    private const SCRIPT = __DIR__ . '/../../Build/Scripts/audit-theme-contrast.php';
    private const MENU_CSS = __DIR__ . '/../../Resources/Private/Css/desiderio/26-menu-surface-tokens.css';

    public function testEveryPresetsInvertedMenuIsMeasuredAndPasses(): void
    {
        [$exitCode, $report] = $this->audit();

        self::assertSame(0, $exitCode, implode("\n", array_map(
            static fn(array $failure): string => (string)$failure['preset'] . ' ' . (string)$failure['pair'],
            $report['failures'],
        )));
        self::assertSame([], $report['redeclared']);
        // 15 presets x 2 menu rules (narrow, wide) x the pairs the menu re-points.
        self::assertGreaterThanOrEqual(15 * 2 * 20, $report['menuChecks']);
    }

    public function testAnUnreadableMenuInkFailsTheAudit(): void
    {
        $css = (string)file_get_contents(self::MENU_CSS);
        $broken = str_replace(
            '--d-menu-inv-muted-foreground: oklch(0.708 0 0);',
            '--d-menu-inv-muted-foreground: oklch(0.4 0 0);',
            $css,
        );
        self::assertNotSame($css, $broken, 'The generic inverted block no longer declares the expected muted ink.');
        $file = tempnam(sys_get_temp_dir(), 'menu-css');
        self::assertIsString($file);
        file_put_contents($file, $broken);

        try {
            [$exitCode, $report] = $this->audit('--menu-css=' . $file);
        } finally {
            unlink($file);
        }

        self::assertSame(1, $exitCode);
        $pairs = array_map(static fn(array $failure): string => (string)$failure['pair'], $report['failures']);
        self::assertContains('--muted-foreground on --popover in the inverted menu', $pairs);
        self::assertContains('--muted-foreground on --popover in the wide inverted menu', $pairs);
    }

    /**
     * @return array{0: int, 1: array{failures: list<array<string, mixed>>, redeclared: list<string>, menuChecks: int}}
     */
    private function audit(string ...$arguments): array
    {
        $command = PHP_BINARY . ' ' . escapeshellarg(self::SCRIPT) . ' --json';
        foreach ($arguments as $argument) {
            $command .= ' ' . escapeshellarg($argument);
        }
        exec($command . ' 2>&1', $output, $exitCode);
        $report = json_decode(implode("\n", $output), true);
        self::assertIsArray($report, implode("\n", $output));

        /** @var array{failures: list<array<string, mixed>>, redeclared: list<string>, menuChecks: int} $report */
        return [$exitCode, $report];
    }
}
