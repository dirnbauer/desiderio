#!/usr/bin/env php
<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$files = glob($root . '/ContentBlocks/ContentElements/*/assets/frontend.css');
if ($files === false) {
    fwrite(STDERR, "Unable to read content CSS assets.\n");
    exit(1);
}

$replacements = [
    'hsl(210 100% 50%)' => 'var(--d-info)',
    'hsl(210 100% 40%)' => 'var(--d-info)',
    'hsl(210 100% 93%)' => 'var(--d-info-muted)',
    'hsl(210 100% 97%)' => 'var(--d-info-muted)',
    'hsl(142 70% 45%)' => 'var(--d-success)',
    'hsl(142 70% 40%)' => 'var(--d-success)',
    'hsl(142 70% 30%)' => 'var(--d-success)',
    'hsl(142 70% 93%)' => 'var(--d-success-muted)',
    'hsl(142 70% 96%)' => 'var(--d-success-muted)',
    'hsl(45 100% 50%)' => 'var(--d-warning)',
    'hsl(45 100% 42%)' => 'var(--d-warning)',
    'hsl(45 100% 30%)' => 'var(--d-warning)',
    'hsl(45 100% 90%)' => 'var(--d-warning-muted)',
    'hsl(45 100% 96%)' => 'var(--d-warning-muted)',
    'hsl(25 95% 53%)' => 'var(--d-warning)',
    'hsl(25 60% 50%)' => 'var(--d-warning)',
    'hsl(0 80% 50%)' => 'var(--destructive)',
    'hsl(0 80% 40%)' => 'var(--destructive)',
    'hsl(0 80% 93%)' => 'var(--d-danger-muted)',
    'hsl(0 80% 97%)' => 'var(--d-danger-muted)',
    'hsl(0 84% 40%)' => 'var(--destructive)',
    'hsl(0 84% 60% / 0.2)' => 'color-mix(in oklch, var(--destructive) 20%, transparent)',
    'hsl(0 84% 60% / 0.05)' => 'color-mix(in oklch, var(--destructive) 5%, transparent)',
    'hsl(0 0% 10%)' => 'var(--foreground)',
    'hsl(0 0% 75%)' => 'var(--muted-foreground)',
    'hsl(222 47% 11%)' => 'var(--card)',
    'hsl(210 40% 96%)' => 'var(--card-foreground)',
    'hsl(210 40% 80%)' => 'var(--muted-foreground)',
    'hsl(215 20% 25%)' => 'var(--border)',
    'hsl(220, 70%, 50%)' => 'var(--primary)',
    'hsl(280, 70%, 50%)' => 'var(--accent)',
    '#fff' => 'var(--primary-foreground)',
    '#FFF' => 'var(--primary-foreground)',
    '#ffffff' => 'var(--primary-foreground)',
    '#FFFFFF' => 'var(--primary-foreground)',
    '#000' => 'var(--foreground)',
    '#000000' => 'var(--foreground)',
];

$changed = 0;
foreach ($files as $file) {
    $css = file_get_contents($file);
    if ($css === false) {
        throw new RuntimeException(sprintf('Could not read %s', $file));
    }

    $normalized = preg_replace(
        '/hsl\(color-mix\(([^;{}]+)\);?/',
        'color-mix($1);',
        $css
    );
    if (!is_string($normalized)) {
        throw new RuntimeException(sprintf('Could not normalize color-mix in %s', $file));
    }

    $normalized = str_replace(array_keys($replacements), array_values($replacements), $normalized);
    $normalized = preg_replace('/\s+$/m', '', $normalized);
    if (!is_string($normalized)) {
        throw new RuntimeException(sprintf('Could not trim whitespace in %s', $file));
    }

    if ($normalized !== $css) {
        file_put_contents($file, $normalized);
        $changed++;
    }
}

printf("Normalized shadcn token usage in %d content CSS assets.\n", $changed);
