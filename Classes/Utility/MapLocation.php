<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Utility;

/**
 * The location a map embed URL points at, so a map element can show the
 * coordinates and link to a larger map without extra fields.
 *
 * Editors paste the embed URL from OpenStreetMap's share dialog ("Include
 * marker" adds `marker=lat,lng`) or from Google Maps. The exact point wins
 * where the URL has one; otherwise the centre of the shown area is used and
 * reported as approximate.
 */
final class MapLocation
{
    /**
     * @return array{lat: float, lng: float, zoom: int, exact: bool}|null
     */
    public static function fromUrl(?string $url): ?array
    {
        $url = trim(html_entity_decode((string)$url, ENT_QUOTES | ENT_HTML5));
        if ($url === '') {
            return null;
        }
        $parts = parse_url($url);
        if ($parts === false) {
            return null;
        }
        parse_str($parts['query'] ?? '', $query);
        $fragment = $parts['fragment'] ?? '';
        $bbox = isset($query['bbox']) && is_string($query['bbox']) ? self::bbox($query['bbox']) : null;

        // OpenStreetMap embed with "Include marker".
        if (isset($query['marker']) && is_string($query['marker']) && ($point = self::pair($query['marker'])) !== null) {
            return self::location($point, $bbox !== null ? $bbox['zoom'] : 16, true);
        }
        // OpenStreetMap page URL with a marker.
        if (isset($query['mlat'], $query['mlon']) && is_string($query['mlat']) && is_string($query['mlon'])
            && ($point = self::pair($query['mlat'] . ',' . $query['mlon'])) !== null) {
            $zoom = preg_match('~(?:^|&)map=(\d{1,2})/~', $fragment, $m) === 1 ? (int)$m[1] : 16;
            return self::location($point, $zoom, true);
        }
        // Google Maps embed: the place's own coordinates in the pb parameter.
        if (isset($query['pb']) && is_string($query['pb'])
            && preg_match('~!2d(-?\d{1,3}(?:\.\d+)?)!3d(-?\d{1,2}(?:\.\d+)?)~', $query['pb'], $m) === 1
            && ($point = self::pair($m[2] . ',' . $m[1])) !== null) {
            return self::location($point, 16, true);
        }
        // Google Maps with q=lat,lng (a place) or ll/center (a view).
        foreach (['q' => true, 'll' => false, 'center' => false] as $key => $exact) {
            if (isset($query[$key]) && is_string($query[$key]) && ($point = self::pair($query[$key])) !== null) {
                $zoom = isset($query['z']) && is_numeric($query['z']) ? (int)$query['z'] : 16;
                return self::location($point, $zoom, $exact);
            }
        }
        // OpenStreetMap page URL showing a view: #map=zoom/lat/lng.
        if (preg_match('~(?:^|&)map=(\d{1,2})/(-?\d{1,2}(?:\.\d+)?)/(-?\d{1,3}(?:\.\d+)?)~', $fragment, $m) === 1
            && ($point = self::pair($m[2] . ',' . $m[3])) !== null) {
            return self::location($point, (int)$m[1], false);
        }
        // OpenStreetMap embed without a marker: the centre of the box.
        if ($bbox !== null) {
            return self::location([$bbox['lat'], $bbox['lng']], $bbox['zoom'], false);
        }
        return null;
    }

    /**
     * "47.74294° N · 16.41356° E"
     */
    public static function label(float $lat, float $lng): string
    {
        return sprintf(
            '%.5f° %s · %.5f° %s',
            abs($lat),
            $lat < 0 ? 'S' : 'N',
            abs($lng),
            $lng < 0 ? 'W' : 'E',
        );
    }

    /**
     * A full OpenStreetMap page for the location, with a marker when the
     * point is exact.
     *
     * @param array{lat: float, lng: float, zoom: int, exact: bool} $location
     */
    public static function openStreetMapUrl(array $location): string
    {
        $lat = sprintf('%.5f', $location['lat']);
        $lng = sprintf('%.5f', $location['lng']);
        $marker = $location['exact'] ? sprintf('?mlat=%s&mlon=%s', $lat, $lng) : '';
        return sprintf('https://www.openstreetmap.org/%s#map=%d/%s/%s', $marker, $location['zoom'], $lat, $lng);
    }

    /**
     * @return array{0: float, 1: float}|null latitude, longitude
     */
    private static function pair(string $value): ?array
    {
        if (preg_match('~^\s*(-?\d{1,2}(?:\.\d+)?)\s*,\s*(-?\d{1,3}(?:\.\d+)?)\s*$~', rawurldecode($value), $m) !== 1) {
            return null;
        }
        $lat = (float)$m[1];
        $lng = (float)$m[2];
        if ($lat < -90.0 || $lat > 90.0 || $lng < -180.0 || $lng > 180.0) {
            return null;
        }
        return [$lat, $lng];
    }

    /**
     * @return array{lat: float, lng: float, zoom: int}|null
     */
    private static function bbox(string $value): ?array
    {
        $values = array_map('trim', explode(',', rawurldecode($value)));
        if (count($values) !== 4 || array_filter($values, static fn(string $v): bool => !is_numeric($v)) !== []) {
            return null;
        }
        [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', $values);
        $span = abs($maxLng - $minLng);
        if ($span <= 0.0 || self::pair(($minLat + $maxLat) / 2 . ',' . ($minLng + $maxLng) / 2) === null) {
            return null;
        }
        return [
            'lat' => ($minLat + $maxLat) / 2,
            'lng' => ($minLng + $maxLng) / 2,
            'zoom' => max(2, min(18, (int)round(log(360 / $span, 2)))),
        ];
    }

    /**
     * @param array{0: float, 1: float} $point
     * @return array{lat: float, lng: float, zoom: int, exact: bool}
     */
    private static function location(array $point, int $zoom, bool $exact): array
    {
        return ['lat' => $point[0], 'lng' => $point[1], 'zoom' => max(2, min(19, $zoom)), 'exact' => $exact];
    }
}
