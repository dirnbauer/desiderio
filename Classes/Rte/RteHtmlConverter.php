<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Rte;

/**
 * Converts plain textarea values into the HTML shape CKEditor produces,
 * so records converted to enableRichtext open cleanly in the RTE and
 * render identically before an editor ever touches them.
 *
 * Blank-line separated blocks become <p> paragraphs, single newlines
 * inside a block become <br />. Values that already contain markup are
 * left alone (the caller decides whether to flag them for review).
 */
final class RteHtmlConverter
{
    public static function looksLikeHtml(string $value): bool
    {
        return (bool)preg_match('/<[a-z][a-z0-9-]*(\s[^>]*)?>/i', $value);
    }

    public static function convert(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", trim($value));
        if ($value === '') {
            return '';
        }
        $paragraphs = preg_split('/\n{2,}/', $value) ?: [];
        $html = [];
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if ($paragraph === '') {
                continue;
            }
            // ENT_NOQUOTES: quotes/apostrophes are fine in element content and
            // CKEditor keeps them literal — only &, <, > need escaping.
            $escaped = htmlspecialchars($paragraph, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
            $html[] = '<p>' . str_replace("\n", '<br />', $escaped) . '</p>';
        }
        return implode("\n", $html);
    }
}
