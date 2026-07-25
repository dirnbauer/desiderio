<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Seeding;

/**
 * Picks demo media for the element library by what a field is FOR, not by a
 * hash of its name.
 *
 * The styleguide seeder draws every file field from one pool of office photos
 * and one pool of portraits, chosen by `crc32(field . ':' . index)`. On the
 * element library that produced visibly wrong previews - a `<video>` element
 * pointing at a JPEG, a QR code slot showing a whiteboard, partner logo strips
 * built from stock photography of laptops. Editors copy these records into
 * their own pages, so the demo has to be right, not merely present.
 *
 * This provider resolves a field to a semantic role first and then picks
 * within that role's pool. Selection stays deterministic (same catalog, same
 * seed order, same output) so reseeding is idempotent, but portraits are keyed
 * on the collection item index rather than a hash: the demo person's name is
 * generated from the same index, so face and name stay in sync across every
 * element that shows the same cast.
 */
final class LibraryImageAssetProvider
{
    private const STYLEGUIDE = 'Resources/Public/Styleguide';
    private const LIBRARY = self::STYLEGUIDE . '/Library';

    /**
     * Roles in resolution order. The first matching rule wins, so the narrow
     * technical formats (video, audio, captions, documents) are tested before
     * the broad visual ones - a `poster_image` must not be caught by `image`.
     *
     * @var list<array{role: string, needles: list<string>}>
     */
    private const ROLE_RULES = [
        ['role' => 'captions', 'needles' => ['captions', 'subtitle', 'vtt', 'transcript']],
        ['role' => 'video', 'needles' => ['video', 'movie', 'clip']],
        ['role' => 'audio', 'needles' => ['audio', 'podcast', 'sound', 'track']],
        ['role' => 'poster', 'needles' => ['poster', 'thumbnail']],
        ['role' => 'document', 'needles' => ['document', 'download', 'attachment', 'pdf', 'datasheet', 'whitepaper']],
        ['role' => 'qr', 'needles' => ['qr']],
        ['role' => 'portrait', 'needles' => ['portrait', 'avatar', 'headshot', 'author', 'reviewer', 'member', 'person', 'speaker', 'advisor', 'investor', 'founder', 'employee', 'testimonial']],
        ['role' => 'logo', 'needles' => ['logo', 'brand', 'client', 'partner', 'sponsor', 'vendor', 'integration']],
        ['role' => 'badge', 'needles' => ['badge', 'award', 'certification', 'certificate', 'seal', 'trust', 'compliance', 'accreditation']],
        ['role' => 'illustration', 'needles' => ['illustration', 'artwork', 'graphic', 'drawing']],
        ['role' => 'product-ui', 'needles' => ['app', 'dashboard', 'screenshot', 'product', 'interface', 'ui', 'preview']],
        ['role' => 'hero', 'needles' => ['hero', 'background', 'cover', 'banner', 'masthead']],
    ];

    /** @var array<string, list<array{file: string, title: string, alt: string, credit: string, source: string}>>|null */
    private ?array $pools = null;

    public function __construct(
        private readonly StyleguideFixtureResolver $fallbackAssets,
    ) {}

    /**
     * @param array<string, mixed> $fieldConfig
     * @return list<array{file: string, title: string, alternative: string, description: string, source: string}>
     */
    public function references(string $field, array $fieldConfig, int $index, int $count): array
    {
        $role = $this->resolveRole($field, $fieldConfig);
        $pool = $this->pool($role);
        if ($pool === []) {
            return [];
        }

        // A field that can only ever hold one file (a hero image, a logo, a
        // video) must not be handed three near-identical assets, so `$count`
        // is already capped by the caller; here we only decide WHICH.
        $references = [];
        for ($offset = 0; $offset < $count; $offset++) {
            // Portraits are index-keyed so the face matches the name the demo
            // value generator produced for the same collection item. Everything
            // else stays hash-keyed within its role, which keeps neighbouring
            // cards from repeating one asset while remaining reproducible.
            $position = $role === 'portrait'
                ? ($index + $offset)
                : (int)abs(crc32($role . ':' . $field . ':' . ($index + $offset)));
            $asset = $pool[$position % count($pool)];

            $references[] = [
                'file' => $asset['file'],
                'title' => $asset['title'],
                'alternative' => $asset['alt'],
                'description' => $asset['credit'],
                'source' => $asset['source'],
            ];
        }

        return $references;
    }

    /**
     * @param array<string, mixed> $fieldConfig
     */
    public function resolveRole(string $field, array $fieldConfig): string
    {
        $identifier = is_scalar($fieldConfig['identifier'] ?? null) ? (string)$fieldConfig['identifier'] : '';
        $label = is_scalar($fieldConfig['label'] ?? null) ? (string)$fieldConfig['label'] : '';
        $allowed = is_scalar($fieldConfig['allowed'] ?? null) ? (string)$fieldConfig['allowed'] : '';

        // The LEAF identifier decides the role, never the collection path.
        // `$field` can arrive as "testimonials.company_logo", and matching the
        // whole string made the parent collection win: "testimonials" hits the
        // portrait rule, so a company logo slot was handed a person's face.
        if ($identifier !== '') {
            $leaf = $identifier;
        } else {
            $lastSegment = strrchr($field, '.');
            $leaf = $lastSegment === false ? $field : substr($lastSegment, 1);
        }
        $allowed = strtolower($allowed);

        // `allowed` only settles the role when it EXCLUDES images. A field
        // declared `common-media-types` accepts both, and `textmedia.media`
        // ("Image or Video") is rendered by an <img> in the image branch of its
        // template - handing it an .mp4 produced a literally broken image. When
        // either is acceptable, an image is the safe demo: it always renders.
        $excludesImages = $allowed !== '' && !str_contains($allowed, 'image') && !str_contains($allowed, 'common-media');
        if ($excludesImages && str_contains($allowed, 'video')) {
            return 'video';
        }
        if ($excludesImages && str_contains($allowed, 'audio')) {
            return 'audio';
        }

        // The label is deliberately excluded from the haystack for the same
        // reason: labels like "Image or Video" name every accepted format, so
        // matching them makes an either-or field resolve to the riskier one.
        $haystack = strtolower($leaf) . ' ' . ($excludesImages ? $allowed : '');

        foreach (self::ROLE_RULES as $rule) {
            foreach ($rule['needles'] as $needle) {
                if (str_contains($haystack, $needle)) {
                    return $rule['role'];
                }
            }
        }

        // A generically named `image` inside a people collection is a portrait.
        // The leaf alone cannot know this - team-grid, advisor-grid and
        // board-members all just call it `image` - so for this one case the
        // surrounding path is the evidence. `$field` arrives from the resolver
        // as "<element>-<collection>-<field>", which carries it.
        if (in_array($leaf, ['image', 'photo', 'picture'], true)) {
            $context = strtolower($field);
            foreach (['member', 'people', 'person', 'team', 'advisor', 'investor', 'speaker', 'author', 'staff', 'employee', 'testimonial', 'quote'] as $needle) {
                if (str_contains($context, $needle)) {
                    return 'portrait';
                }
            }
        }

        return 'editorial';
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function pool(string $role): array
    {
        $this->pools ??= $this->buildPools();

        // An unfilled role must never silently produce a wrong-format file, so
        // only the visual roles fall back to editorial photography. video,
        // audio, captions and document have no sane substitute: returning an
        // empty list leaves the field unset, which reads as "nothing here" in
        // the preview instead of as a broken player.
        $pool = $this->pools[$role] ?? [];
        if ($pool !== [] || in_array($role, ['video', 'audio', 'captions', 'document', 'poster'], true)) {
            return $pool;
        }

        return $this->pools['editorial'] ?? [];
    }

    /**
     * @return array<string, list<array{file: string, title: string, alt: string, credit: string, source: string}>>
     */
    private function buildPools(): array
    {
        return [
            'video' => $this->mediaPool('Video', '*-feature-video.mp4', 'Feature video'),
            'poster' => $this->mediaPool('Video', '*-feature-video-poster.webp', 'Video poster frame'),
            'captions' => $this->mediaPool('Video', '*-feature-video.en.vtt', 'English captions'),
            'audio' => $this->fallbackAssets->getStyleguideAudioAssets(),
            // Cast portraits ONLY, never mixed with the generic Unsplash pool:
            // the pool is indexed with the same modulus as the demo cast, so
            // adding overflow faces would silently desynchronise name and face
            // for every collection item past the twelfth.
            'portrait' => $this->castPortraitPool(),
            'logo' => $this->libraryPool('logo', 'Partner logo'),
            'badge' => $this->libraryPool('badge', 'Certification badge'),
            'qr' => $this->libraryPool('qr', 'QR code'),
            'illustration' => $this->libraryPool('illustration', 'Illustration'),
            'hero' => $this->libraryPool('hero', 'Hero image'),
            'product-ui' => $this->productUiPool(),
            'document' => $this->libraryPool('doc', 'Demo document'),
            'editorial' => array_merge(
                $this->libraryPool('editorial', 'Editorial photo'),
                $this->fallbackAssets->getStyleguideImageAssets(),
            ),
        ];
    }

    /**
     * The demo cast: one portrait per person in ElementLibraryValueGenerator's
     * demoPeople(), in the same order, so index N always yields the face that
     * belongs to name N. Filenames are numbered (`lib-portrait-01-…`) because
     * that order IS the contract - see LibraryCastPortraitsTest.
     *
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function castPortraitPool(): array
    {
        $pool = $this->libraryPool('portrait', 'Portrait');

        return $pool !== [] ? $pool : $this->fallbackAssets->getStyleguidePortraitAssets();
    }

    /**
     * Real screenshots of Desiderio-built frontends beat anything a model can
     * invent for a "product UI" slot: they contain a coherent interface rather
     * than plausible-looking nonsense.
     *
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function productUiPool(): array
    {
        return $this->mediaPool('Frontend', 'frontend-*.png', 'Product interface');
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function libraryPool(string $rolePrefix, string $title): array
    {
        // No credit: it is written to sys_file_reference.description, which the
        // templates render as a visible caption. These assets are generated for
        // this package and need no attribution, so anything here would show up
        // under every image as text the editor did not write.
        return $this->globPool(self::LIBRARY, 'lib-' . $rolePrefix . '-*', $title, '', '');
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function mediaPool(string $subdirectory, string $pattern, string $title): array
    {
        return $this->globPool(self::STYLEGUIDE . '/' . $subdirectory, $pattern, $title, '', '');
    }

    /**
     * @return list<array{file: string, title: string, alt: string, credit: string, source: string}>
     */
    private function globPool(string $relativeDirectory, string $pattern, string $title, string $credit, string $source): array
    {
        $absolute = dirname(__DIR__, 2) . '/' . $relativeDirectory;
        $matches = glob($absolute . '/' . $pattern);
        if ($matches === false || $matches === []) {
            return [];
        }
        natsort($matches);

        $assets = [];
        foreach ($matches as $path) {
            $basename = basename($path);
            $assets[] = [
                'file' => $relativeDirectory . '/' . $basename,
                'title' => $title,
                'alt' => $this->altTextFromFilename($basename, $title),
                'credit' => $credit,
                'source' => $source,
            ];
        }

        return $assets;
    }

    /**
     * Alt text has to describe the picture, not the slot it sits in - an
     * editor who keeps the demo image inherits this string verbatim. The
     * filename carries the subject (`lib-hero-onboarding-a3f19c22.webp`), so
     * strip the role prefix and the content hash and read the rest.
     */
    private function altTextFromFilename(string $basename, string $fallback): string
    {
        $stem = pathinfo($basename, PATHINFO_FILENAME);
        $stem = preg_replace('/^lib-[a-z0-9]+-/', '', $stem) ?? $stem;
        $stem = preg_replace('/-[0-9a-f]{8}$/', '', $stem) ?? $stem;
        $stem = preg_replace('/^frontend-/', '', $stem) ?? $stem;
        $stem = trim(str_replace('-', ' ', $stem));

        return $stem === '' ? $fallback . '.' : ucfirst($stem) . '.';
    }
}
