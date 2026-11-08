<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Templates\CollectionMarker;

/**
 * The Collection loops that carry no row marker, in one place, each with the
 * reason. Keys are `<block>.<collection path>` (every loop over that
 * Collection) or `<block>.<collection path>#<n>` (only the n-th loop over it in
 * the template). Without a marker the Visual Editor still lists the entries in
 * its panel; only the on-page row controls are missing for these rows.
 *
 * @internal used by the collection marker codemod and its lint test only
 */
final class CollectionMarkerExceptions
{
    public const array EXCEPTIONS = [
        'chart.data_points#1' => 'Computes the summary values only (f:variable, no output); the bars are drawn by charts.js. The data table loop (#2) carries the markers.',
        'chart-contribution.points#1' => 'Writes the JSON array charts.js draws from (f:format.json inside an f:variable); no element per entry. The data table loop (#2) carries the markers.',
        'chart-donut.segments#1' => 'Writes the JSON array charts.js draws the ring from (f:format.json inside an f:variable); no element per entry. The legend (#2) and the data table (#3) carry the markers.',
        'gallery.items#1' => 'The featured view renders only the first entry, as two siblings (lightbox image and caption) the gallery script swaps; no single root. The thumbnail loop (#2) carries the markers.',
    ];

    /**
     * @param array<string, string>|null $exceptions
     */
    public function __construct(
        private readonly ?array $exceptions = null,
    ) {}

    public function reasonFor(CollectionLoop $loop): ?string
    {
        $exceptions = $this->exceptions ?? self::EXCEPTIONS;
        return $exceptions[$loop->key() . '#' . $loop->occurrence] ?? $exceptions[$loop->key()] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->exceptions ?? self::EXCEPTIONS;
    }
}
