<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class ContentButtonEditingTemplateTest extends TestCase
{
    public function testEveryEditorManagedButtonLinkHasItsOwnLinkEditor(): void
    {
        $failures = [];
        $templates = glob(__DIR__ . '/../../ContentBlocks/ContentElements/*/templates/frontend.html');
        self::assertIsArray($templates);
        self::assertNotEmpty($templates);

        foreach ($templates as $templatePath) {
            $template = (string)file_get_contents($templatePath);
            preg_match_all('/<d:atom\.(button|link)\b.*?<\/d:atom\.\1>/s', $template, $buttons, PREG_OFFSET_CAPTURE);

            foreach ($buttons[0] as [$button, $offset]) {
                if (str_starts_with($button, '<d:atom.link')
                    && preg_match('/class="[^"]*(?:__button|__cta|__action|cta-minimal)/', $button) !== 1
                ) {
                    continue;
                }
                if (preg_match('/href="\{f:uri\.typolink\(parameter: (\w+)\.(\w+)\.url\)\}"/', $button, $fieldMatch) === 1) {
                    [, $record, $field] = $fieldMatch;
                } elseif (str_contains($button, 'href="{mapLink}"')) {
                    // The map URL has a computed fallback, but editors can override it.
                    $record = 'data';
                    $field = 'primary_button_link';
                } else {
                    continue;
                }

                $expectedEditor = '{' . $record . " -> f:render.link(field: '" . $field . "')}";
                $followingMarkup = ltrim(substr($template, $offset + strlen($button)));
                if (!str_starts_with($followingMarkup, $expectedEditor)) {
                    $element = basename(dirname($templatePath, 2));
                    $line = substr_count(substr($template, 0, $offset), "\n") + 1;
                    $failures[] = $element . ':' . $line . ' must expose ' . $record . '.' . $field;
                }
            }
        }

        self::assertSame([], $failures, "Button destinations must be editable beside their labels:\n" . implode("\n", $failures));
    }
}
