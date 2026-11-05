<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * The pages a `desiderio:styleguide:seed --pages=…` run reseeds.
 *
 * A selector is a page uid or a slug as the seeder writes it. An operator
 * types slugs the way they read them, so "pricing/how-we-earn",
 * "/pricing/how-we-earn/", "home", "/" and a URL copied from the browser
 * ("https://example.org/desiderio/pricing/how-we-earn/", whose site base
 * path "/desiderio/" is stripped) all name a page of the plan. Every
 * selector must match a planned page; the selection keeps the plan order.
 */
final readonly class StyleguidePageFilter
{
    public const string HOME_SLUG = '/';

    /**
     * @param array<string, true> $selected Planned slugs, in plan order
     */
    private function __construct(
        private array $selected,
    ) {}

    /**
     * @param string $selectors Comma-separated page uids and slugs
     * @param list<string> $planSlugs Every slug the seeder plans, home as "/"
     * @param array<string, int|null> $existingUids Planned slug => uid of the live page, null when it does not exist yet
     * @param string $basePath The site base path ("/" or "/desiderio/"), stripped from slugs that carry it
     * @throws \InvalidArgumentException For an empty selection, a slug outside the plan or a uid of no planned page
     */
    public static function fromSelectors(string $selectors, array $planSlugs, array $existingUids, string $basePath = '/'): self
    {
        $planned = array_fill_keys($planSlugs, true);
        $slugsByUid = [];
        foreach ($planSlugs as $slug) {
            $uid = $existingUids[$slug] ?? null;
            if ($uid !== null && !isset($slugsByUid[$uid])) {
                $slugsByUid[$uid] = $slug;
            }
        }

        $chosen = [];
        $unknown = [];
        foreach (explode(',', $selectors) as $selector) {
            $selector = trim($selector);
            if ($selector === '') {
                continue;
            }
            if (ctype_digit($selector)) {
                $slug = $slugsByUid[(int)$selector] ?? null;
                if ($slug === null) {
                    $unknown[] = sprintf('uid %s', $selector);
                    continue;
                }
                $chosen[$slug] = true;
                continue;
            }
            $slug = self::matchSlug($selector, $planned, $basePath);
            if ($slug === null) {
                $unknown[] = $selector;
                continue;
            }
            $chosen[$slug] = true;
        }

        if ($unknown !== []) {
            throw new \InvalidArgumentException(sprintf(
                'No page of the styleguide plan matches %s. Give a uid of an existing seeded page or one of these slugs: %s',
                implode(', ', $unknown),
                implode(', ', $planSlugs)
            ), 1793875201);
        }
        if ($chosen === []) {
            throw new \InvalidArgumentException('The option --pages names no page. Give a comma-separated list of page uids or slugs, for example --pages=/pricing/how-we-earn.', 1793875202);
        }

        $selected = [];
        foreach ($planSlugs as $slug) {
            if (isset($chosen[$slug])) {
                $selected[$slug] = true;
            }
        }

        return new self($selected);
    }

    /**
     * The slug form the seeder writes: a leading slash, no trailing slash,
     * lower case, no scheme, host, query or fragment; "home" and "" are "/".
     */
    public static function normalizeSlug(string $selector): string
    {
        $path = strtolower(trim($selector));
        if (str_contains($path, '://')) {
            $path = (string)(parse_url($path, PHP_URL_PATH) ?? '');
        }
        $path = (string)preg_replace('/[?#].*$/', '', $path);
        $path = trim($path, '/');
        if ($path === '' || $path === 'home') {
            return self::HOME_SLUG;
        }

        return '/' . $path;
    }

    public function includes(string $slug): bool
    {
        return isset($this->selected[$slug]);
    }

    /**
     * @return list<string>
     */
    public function slugs(): array
    {
        return array_keys($this->selected);
    }

    /**
     * The planned pages the selection needs that do not exist and are not
     * selected (so this run would not create them): the parent of every
     * selected page, and every planned page the selected content links to.
     * Only pages the seeder creates count, the ones with a planned parent:
     * the parent page always exists, and a page another package owns (the
     * Download page) is linked by a full seed only once it exists, too.
     *
     * @param array<string, string|null> $parents Planned slug => slug of its planned parent page, null for the parent page and adopted pages
     * @param array<string, int|null> $existingUids Planned slug => uid of the live page, null when it does not exist yet
     * @param list<string> $linkedSlugs Planned slugs the selected content links to
     * @return list<string>
     */
    public function missingDependencies(array $parents, array $existingUids, array $linkedSlugs): array
    {
        $needed = [];
        foreach (array_keys($this->selected) as $slug) {
            $parent = $parents[$slug] ?? null;
            if ($parent !== null) {
                $needed[] = $parent;
            }
        }
        array_push($needed, ...$linkedSlugs);

        $missing = [];
        foreach ($needed as $slug) {
            if (($parents[$slug] ?? null) !== null && !$this->includes($slug) && ($existingUids[$slug] ?? null) === null) {
                $missing[$slug] = true;
            }
        }

        return array_keys($missing);
    }

    /**
     * @param array<string, true> $planned
     */
    private static function matchSlug(string $selector, array $planned, string $basePath): ?string
    {
        $slug = self::normalizeSlug($selector);
        if (isset($planned[$slug])) {
            return $slug;
        }
        // A path copied from the browser carries the site base path.
        $base = '/' . trim(strtolower($basePath), '/');
        if ($base !== '/' && ($slug === $base || str_starts_with($slug, $base . '/'))) {
            $slug = self::normalizeSlug(substr($slug, strlen($base)));
            if (isset($planned[$slug])) {
                return $slug;
            }
        }

        return null;
    }
}
