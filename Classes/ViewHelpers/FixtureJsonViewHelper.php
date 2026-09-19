<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use Webconsulting\Desiderio\Data\StyleguideContentGroups;

/**
 * Outputs the styleguide fixture data as a JSON <script> block for JS consumption.
 *
 * Usage:
 *   <d:fixtureJson />
 *
 * Emits a CSP nonce attribute when the request carries a TYPO3 14 ConsumableNonce
 * so a strict `script-src 'self' 'nonce-…'` policy stays compatible.
 */
final class FixtureJsonViewHelper extends AbstractRequestAwareViewHelper
{
    protected $escapeOutput = false;

    public function render(): string
    {
        $fixtures = StyleguideContentGroups::getFixtures();
        if ($fixtures === []) {
            return '';
        }

        $json = json_encode($fixtures, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
        if ($json === false) {
            return '';
        }

        return '<script id="styleguide-fixtures" type="application/json"' . $this->nonceAttribute() . '>' . $json . '</script>';
    }
}
