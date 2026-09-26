<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webconsulting\Desiderio\Utility\MapLocation;

final class MapLocationTest extends TestCase
{
    /**
     * @return iterable<string, array{string, float, float, bool}>
     */
    public static function urls(): iterable
    {
        yield 'OpenStreetMap embed with marker' => [
            'https://www.openstreetmap.org/export/embed.html?bbox=13.3750%2C52.5225%2C13.4050%2C52.5375&layer=mapnik&marker=52.53000%2C13.39000',
            52.53, 13.39, true,
        ];
        yield 'OpenStreetMap embed, HTML-escaped ampersands' => [
            'https://www.openstreetmap.org/export/embed.html?bbox=16.40%2C47.73%2C16.42%2C47.75&amp;layer=mapnik&amp;marker=47.74294%2C16.41356',
            47.74294, 16.41356, true,
        ];
        yield 'OpenStreetMap embed without marker: centre of the box' => [
            'https://www.openstreetmap.org/export/embed.html?bbox=13.3750%2C52.5225%2C13.4050%2C52.5375&layer=mapnik',
            52.53, 13.39, false,
        ];
        yield 'OpenStreetMap page with marker' => [
            'https://www.openstreetmap.org/?mlat=48.20849&mlon=16.37208#map=17/48.20849/16.37208',
            48.20849, 16.37208, true,
        ];
        yield 'OpenStreetMap page view' => [
            'https://www.openstreetmap.org/#map=15/-33.86882/151.20930',
            -33.86882, 151.2093, false,
        ];
        yield 'Google Maps embed pb' => [
            'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2660.1!2d16.41356!3d47.74294!2m3!1f0!2f0!3f0',
            47.74294, 16.41356, true,
        ];
        yield 'Google Maps q' => [
            'https://maps.google.com/maps?q=40.71280,-74.00600&z=14&output=embed',
            40.7128, -74.006, true,
        ];
    }

    #[Test]
    #[DataProvider('urls')]
    public function readsTheLocationFromAMapUrl(string $url, float $lat, float $lng, bool $exact): void
    {
        $location = MapLocation::fromUrl($url);

        self::assertNotNull($location);
        self::assertEqualsWithDelta($lat, $location['lat'], 0.000001);
        self::assertEqualsWithDelta($lng, $location['lng'], 0.000001);
        self::assertSame($exact, $location['exact']);
        self::assertGreaterThanOrEqual(2, $location['zoom']);
        self::assertLessThanOrEqual(19, $location['zoom']);
    }

    /**
     * @return iterable<string, array{?string}>
     */
    public static function urlsWithoutLocation(): iterable
    {
        yield 'empty' => [''];
        yield 'null' => [null];
        yield 'plain page' => ['https://example.com/contact'];
        yield 'latitude out of range' => ['https://www.openstreetmap.org/export/embed.html?marker=95.0%2C10.0'];
        yield 'broken box' => ['https://www.openstreetmap.org/export/embed.html?bbox=a%2Cb%2Cc%2Cd'];
    }

    #[Test]
    #[DataProvider('urlsWithoutLocation')]
    public function returnsNullWithoutALocation(?string $url): void
    {
        self::assertNull(MapLocation::fromUrl($url));
    }

    #[Test]
    public function labelsTheHemispheres(): void
    {
        self::assertSame('47.74294° N · 16.41356° E', MapLocation::label(47.74294, 16.41356));
        self::assertSame('33.86882° S · 74.00600° W', MapLocation::label(-33.86882, -74.006));
    }

    #[Test]
    public function linksToOpenStreetMapWithAMarkerOnlyForAnExactPoint(): void
    {
        self::assertSame(
            'https://www.openstreetmap.org/?mlat=52.53000&mlon=13.39000#map=16/52.53000/13.39000',
            MapLocation::openStreetMapUrl(['lat' => 52.53, 'lng' => 13.39, 'zoom' => 16, 'exact' => true]),
        );
        self::assertSame(
            'https://www.openstreetmap.org/#map=14/52.53000/13.39000',
            MapLocation::openStreetMapUrl(['lat' => 52.53, 'lng' => 13.39, 'zoom' => 14, 'exact' => false]),
        );
    }
}
