#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Download portrait fixtures from an Unsplash collection into
 * Resources/Public/Styleguide/Unsplash/People/.
 *
 * Usage:
 *   php Build/Scripts/fetch-unsplash-portraits.php
 *   php Build/Scripts/fetch-unsplash-portraits.php --collection=25880 --count=40
 */

$options = getopt('', ['collection::', 'count::', 'prefix::', 'help']);

if (isset($options['help'])) {
    fwrite(STDOUT, "Download Unsplash portrait fixtures.\n\n");
    fwrite(STDOUT, "Options:\n");
    fwrite(STDOUT, "  --collection=ID   Unsplash collection id (default: 25880)\n");
    fwrite(STDOUT, "  --count=N         Number of portraits to download (default: 40)\n");
    fwrite(STDOUT, "  --prefix=NAME     Output filename prefix (default: team-grid)\n");
    exit(0);
}

$collectionId = (int)($options['collection'] ?? 25880);
$count = max(1, (int)($options['count'] ?? 40));
$prefix = trim((string)($options['prefix'] ?? 'team-grid'));
if ($prefix === '') {
    $prefix = 'team-grid';
}

$root = dirname(__DIR__, 2);
$targetDirectory = $root . '/Resources/Public/Styleguide/Unsplash/People';
if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0775, true) && !is_dir($targetDirectory)) {
    fwrite(STDERR, "Unable to create target directory: {$targetDirectory}\n");
    exit(1);
}

$photos = fetchCollectionPhotos($collectionId, $count);
if ($photos === []) {
    fwrite(STDERR, "Collection #{$collectionId} returned no photos. Trying portrait search fallback.\n");
    $photos = fetchPortraitSearchPhotos($count);
}

if ($photos === []) {
    fwrite(STDERR, "No portrait photos could be resolved.\n");
    exit(1);
}

$downloaded = 0;
foreach (array_slice($photos, 0, $count) as $index => $photo) {
    $number = str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT);
    $targetFile = $targetDirectory . '/' . $prefix . '-' . $number . '.jpg';
    $downloadUrl = portraitDownloadUrl($photo);
    if ($downloadUrl === '') {
        continue;
    }

    $bytes = @file_get_contents($downloadUrl);
    if ($bytes === false || $bytes === '') {
        fwrite(STDERR, "Failed to download {$downloadUrl}\n");
        continue;
    }

    file_put_contents($targetFile, $bytes);
    $downloaded++;
    fwrite(STDOUT, "Saved {$targetFile}\n");
}

fwrite(STDOUT, "Downloaded {$downloaded} portrait(s) from Unsplash collection #{$collectionId}.\n");
exit($downloaded > 0 ? 0 : 1);

/**
 * @return list<array<string, mixed>>
 */
function fetchCollectionPhotos(int $collectionId, int $count): array
{
    $photos = [];
    $perPage = min(30, $count);
    $pages = (int)ceil($count / $perPage);

    for ($page = 1; $page <= $pages; $page++) {
        $payload = fetchJson(
            sprintf(
                'https://unsplash.com/napi/collections/%d/photos?per_page=%d&page=%d',
                $collectionId,
                $perPage,
                $page
            )
        );
        if (!is_array($payload)) {
            break;
        }

        foreach ($payload as $photo) {
            if (is_array($photo)) {
                $photos[] = $photo;
            }
        }

        if (count($photos) >= $count) {
            break;
        }
    }

    return $photos;
}

/**
 * @return list<array<string, mixed>>
 */
function fetchPortraitSearchPhotos(int $count): array
{
    $payload = fetchJson(
        sprintf(
            'https://unsplash.com/napi/search/photos?query=professional%%20portrait%%20headshot&per_page=%d&orientation=portrait',
            min(30, $count)
        )
    );
    if (!is_array($payload)) {
        return [];
    }

    $results = $payload['results'] ?? null;
    if (!is_array($results)) {
        return [];
    }

    $photos = [];
    foreach ($results as $photo) {
        if (is_array($photo)) {
            $photos[] = $photo;
        }
    }

    return $photos;
}

/**
 * @param array<string, mixed> $photo
 */
function portraitDownloadUrl(array $photo): string
{
    $raw = $photo['urls']['raw'] ?? '';
    if (!is_string($raw) || $raw === '') {
        return '';
    }

    $separator = str_contains($raw, '?') ? '&' : '?';

    return $raw . $separator . 'auto=format&fit=crop&crop=faces&w=512&h=512&q=85';
}

/**
 * @return array<string, mixed>|list<array<string, mixed>>|null
 */
function fetchJson(string $url): array|null
{
    $context = stream_context_create([
        'http' => [
            'header' => "User-Agent: desiderio-portrait-fetch/1.0\r\nAccept: application/json\r\n",
            'timeout' => 30,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    if ($response === false || $response === '') {
        return null;
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return null;
    }

    return $decoded;
}
