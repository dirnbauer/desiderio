<?php

declare(strict_types=1);

/**
 * Byte comparison of every element preview between two template states — the
 * "anonymous render byte-identical" gate for template-wide changes such as
 * the collection row markers (Build/Scripts/codemod-collection-markers.php).
 *
 *   php Build/Scripts/compare-element-renders.php capture <urls.json> <dir> [--edit-mode --cookie=<be_typo_user>]
 *   php Build/Scripts/compare-element-renders.php compare <dirA> <dirB>
 *
 * <urls.json> is the output of `vendor/bin/typo3 desiderio:library:urls --json`
 * (for example in the disposable app of Build/CiApp). Capture the old state,
 * flush caches, capture the new one, compare. EXT:form renders a random
 * honeypot field and signed form state on every request; those values are
 * masked, everything else must match byte for byte. Two captures of the same
 * state compare identical, which shows the masking hides no real difference.
 */

$command = $argv[1] ?? '';
if ($command === 'capture' && isset($argv[2], $argv[3])) {
    $options = getopt('', ['edit-mode', 'cookie:'], $restIndex);
    exit(capture($argv[2], $argv[3], isset($options['edit-mode']), is_string($options['cookie'] ?? null) ? $options['cookie'] : null));
}
if ($command === 'compare' && isset($argv[2], $argv[3])) {
    exit(compare($argv[2], $argv[3]));
}
fwrite(STDERR, "usage: capture <urls.json> <dir> [--edit-mode --cookie=<be_typo_user>] | compare <dirA> <dirB>\n");
exit(2);

function capture(string $urlsFile, string $directory, bool $editMode, ?string $cookie): int
{
    $decoded = json_decode((string)file_get_contents($urlsFile), true);
    $elements = is_array($decoded) ? ($decoded['elements'] ?? $decoded) : [];
    if (!is_dir($directory) && !mkdir($directory, 0777, true)) {
        fwrite(STDERR, "cannot create $directory\n");
        return 2;
    }
    $failed = 0;
    foreach ($elements as $element) {
        $url = (string)$element['url'] . ($editMode ? '&editMode=1' : '');
        $handle = curl_init($url);
        curl_setopt_array($handle, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 120]);
        if ($cookie !== null) {
            curl_setopt($handle, CURLOPT_COOKIE, 'be_typo_user=' . $cookie);
        }
        $body = (string)curl_exec($handle);
        $status = curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        if ($status !== 200) {
            ++$failed;
            fwrite(STDERR, $element['cType'] . ": HTTP $status\n");
        }
        file_put_contents($directory . '/' . $element['cType'] . '-' . $element['uid'] . '.html', $body);
    }
    fwrite(STDOUT, count($elements) . " previews captured, $failed not HTTP 200\n");
    return $failed === 0 ? 0 : 1;
}

function compare(string $left, string $right): int
{
    $files = array_map(basename(...), glob($left . '/*.html') ?: []);
    $identical = $masked = 0;
    $different = [];
    foreach ($files as $file) {
        $a = (string)file_get_contents($left . '/' . $file);
        $b = @file_get_contents($right . '/' . $file);
        if ($b === false) {
            $different[] = $file . ' (missing)';
        } elseif ($a === $b) {
            ++$identical;
        } elseif (maskFormTokens($a) === maskFormTokens($b)) {
            ++$masked;
        } else {
            $different[] = $file;
        }
    }
    fwrite(STDOUT, sprintf("%d previews: %d byte-identical, %d identical after masking EXT:form tokens, %d different\n", count($files), $identical, $masked, count($different)));
    foreach ($different as $file) {
        fwrite(STDOUT, '  ' . $file . "\n");
    }
    return $different === [] ? 0 : 1;
}

function maskFormTokens(string $html): string
{
    if (preg_match_all('/<input aria-hidden="true" data-id="([A-Za-z0-9]+)"/', $html, $matches) > 0) {
        foreach ($matches[1] as $index => $honeypot) {
            $html = str_replace($honeypot, 'HONEYPOT' . $index, $html);
        }
    }
    return (string)preg_replace('/(name="[^"]*(?:__state|__trustedProperties|__referrer\]\[[^"]*|__session)[^"]*" value=")[^"]*"/', '$1MASKED"', $html);
}
