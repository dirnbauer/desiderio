<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Webconsulting\Desiderio\Utility\MapLocation;

/**
 * The location behind a map embed URL, for the coordinates line and the
 * "open larger map" link:
 *
 *     <f:variable name="location" value="{dv:mapLocation(url: data.embed_url)}"/>
 *     {location.label} {location.openUrl} {location.exact}
 *
 * Returns an empty array when the URL names no location.
 */
final class MapLocationViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('url', 'string', 'Map embed or map page URL (OpenStreetMap or Google Maps).', false, '');
    }

    /**
     * @return array{lat: float, lng: float, zoom: int, exact: bool, label: string, openUrl: string}|array{}
     */
    public function render(): array
    {
        $location = MapLocation::fromUrl(is_string($this->arguments['url'] ?? null) ? $this->arguments['url'] : '');
        if ($location === null) {
            return [];
        }
        return $location + [
            'label' => MapLocation::label($location['lat'], $location['lng']),
            'openUrl' => MapLocation::openStreetMapUrl($location),
        ];
    }
}
