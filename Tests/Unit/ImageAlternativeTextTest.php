<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * An f:image rendered with a literal alt="" throws away whatever alternative
 * text the editor typed on the file reference, silently: the field is offered
 * in the backend, translated with the rest of the page, and then never reaches
 * the markup. Thirty-one content elements did that until 4.2.1.
 *
 * Passing the reference's own alternative keeps the decorative case intact —
 * an empty alternative still renders alt="" — while an image that carries
 * meaning says so.
 */
final class ImageAlternativeTextTest extends TestCase
{
    #[Test]
    public function noContentElementRendersAnImageWithALiteralEmptyAlt(): void
    {
        $offenders = [];
        foreach ($this->frontendTemplates() as $path) {
            $markup = (string)file_get_contents($path);
            preg_match_all('/<f:image\b[^>]*>/s', $markup, $matches);
            foreach ($matches[0] as $tag) {
                if (str_contains($tag, 'alt=""')) {
                    $offenders[] = substr($path, (int)strpos($path, 'ContentElements'));
                }
            }
        }

        $unique = array_values(array_unique($offenders));

        self::assertSame([], $unique, sprintf(
            "These templates pass a literal alt=\"\" to f:image, which discards the editor's "
            . "alternative text. Pass the reference's own, for example alt=\"{file.alternative}\":\n%s",
            implode("\n", $unique)
        ));
    }

    /**
     * The headline as alt text repeats what a screen reader has just read and
     * hides the description the editor gave the photo. It stays as the
     * fallback for a reference without an alternative, until 4.13.1 it was the
     * only alt of seven hero elements.
     */
    #[Test]
    public function noContentElementReplacesTheImageAlternativeWithTheHeadline(): void
    {
        $offenders = [];
        foreach ($this->frontendTemplates() as $path) {
            $markup = (string)file_get_contents($path);
            preg_match_all('/<f:image\b[^>]*>/s', $markup, $matches);
            foreach ($matches[0] as $tag) {
                if (str_contains($tag, 'alt="{data.header}"')) {
                    $offenders[] = substr($path, (int)strpos($path, 'ContentElements'));
                }
            }
        }

        $unique = array_values(array_unique($offenders));

        self::assertSame([], $unique, sprintf(
            "These templates pass the headline as alt to f:image and drop the editor's alternative text. "
            . "Prefer the reference's own, for example alt=\"{f:if(condition: image.alternative, then: image.alternative, else: data.header)}\":\n%s",
            implode("\n", $unique)
        ));
    }

    /**
     * @return list<string>
     */
    private function frontendTemplates(): array
    {
        $root = dirname(__DIR__, 2) . '/ContentBlocks/ContentElements';
        $found = glob($root . '/*/templates/frontend.html');
        $found = $found === false ? [] : $found;
        self::assertNotEmpty($found, 'No content element templates were found to check.');

        return $found;
    }
}
