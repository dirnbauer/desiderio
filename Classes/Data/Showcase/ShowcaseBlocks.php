<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Content-element and media factories shared by every showcase page class, plus
 * the two external URLs the showcase copy links to.
 *
 * A showcase block is the same shape the starter-site seeder consumes, so the
 * pages built here feed straight into the page seeder without translation.
 *
 * @phpstan-type ShowcaseBlock array{ctype: string, colPos: int, fields: array<string, mixed>}
 * @phpstan-type ShowcaseMedia array{file: string, title: string, alternative: string, description: string, source: string, autoplay?: bool}
 * @phpstan-type ShowcaseBlogMeta array{publishDate: string, categories: list<string>, tags: list<string>}
 * @phpstan-type ShowcasePage array{title: string, navTitle: string, slug: string, abstract: string, description: string, parentSlug: string|null, seoTitle?: string, subtitle?: string, pageTsConfig?: string, backendLayout?: string, blogList?: bool, blog?: ShowcaseBlogMeta, hideInNav?: bool, formerSlugs?: list<string>, content: array<int, ShowcaseBlock>}
 * @phpstan-type ShowcaseCredits array{names: non-empty-list<string>, project: string, link: string}
 */
final class ShowcaseBlocks
{
    public const string REPO_URL = 'https://github.com/dirnbauer/desiderio';
    public const string CREATE_URL = 'https://ui.shadcn.com/create';

    /**
     * @param array<string, mixed> $fields
     * @return ShowcaseBlock
     */
    public static function block(string $ctype, array $fields, int $colPos = 0): array
    {
        return [
            'ctype' => $ctype,
            'colPos' => $colPos,
            'fields' => $fields,
        ];
    }

    /**
     * @return ShowcaseMedia
     */
    public static function screenshot(string $filename, string $title, string $alternative, string $description = 'Screenshot of a TYPO3 v14 site with Desiderio.'): array
    {
        $folder = str_starts_with($filename, 'frontend-') ? 'Frontend' : 'Backend';

        return [
            'file' => 'Resources/Public/Styleguide/' . $folder . '/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => $description,
            'source' => self::REPO_URL,
        ];
    }

    /**
     * The sales hero every first-level page and the homepage open with: the
     * same split hero, a photo on the right in 16:10 (the element crops to
     * that ratio and renders up to 720 px wide, 1440 px on sharp screens).
     *
     * @param array{text: string, link: string} $primary
     * @param array{text: string, link: string} $secondary
     * @param ShowcaseMedia $image
     * @return ShowcaseBlock
     */
    public static function salesHero(string $badge, string $header, string $subheadline, array $primary, array $secondary, array $image): array
    {
        return self::block('desiderio_hero', [
            'variant' => 'split',
            'badge_text' => $badge,
            'header' => $header,
            'subheadline' => $subheadline,
            'primary_button_text' => $primary['text'],
            'primary_button_link' => $primary['link'],
            'primary_button_variant' => 'default',
            'secondary_button_text' => $secondary['text'],
            'secondary_button_link' => $secondary['link'],
            'hero_image' => $image,
            'image_position' => 'right',
            'overlay_opacity' => '0.5',
        ]);
    }

    /**
     * The thank-you to the people behind a third-party extension that a page
     * presents: a bordered, centred highlight that names them in its heading
     * and links the project in its text. Pages put it below their main
     * content, never first. The names come from the extension's own
     * composer.json or ext_emconf.php; webconsulting never thanks itself.
     *
     * @param ShowcaseCredits $credits
     * @return ShowcaseBlock
     */
    public static function thankYou(array $credits): array
    {
        return self::block('desiderio_contenthighlight', [
            'header' => 'Thank you, ' . self::listing($credits['names']),
            'content' => sprintf(
                '<p><a href="%s">%s</a> is the work of %s. Thank you so much for building and maintaining it.</p>',
                htmlspecialchars($credits['link']),
                htmlspecialchars($credits['project']),
                htmlspecialchars(self::listing([...$credits['names'], 'its contributors'])),
            ),
            'variant' => 'bordered',
            'alignment' => 'center',
            'link' => '',
            'link_text' => '',
        ]);
    }

    /**
     * "A", "A and B", "A, B and C": the copy uses no serial comma.
     *
     * @param non-empty-list<string> $items
     */
    private static function listing(array $items): string
    {
        $last = array_pop($items);

        return $items === [] ? $last : implode(', ', $items) . ' and ' . $last;
    }

    /**
     * The product film from Resources/Public/Styleguide/Video/, rendered by
     * Build/Scripts/render-film.mjs from Build/Film/film.html, which also
     * keeps the hashed file names below current. The reference's autoplay
     * flag lets it play muted while it is on screen (desiderio.js), never for
     * visitors who ask for reduced motion; its controls stay.
     *
     * @return ShowcaseBlock
     */
    public static function productFilm(string $eyebrow, string $header, string $description): array
    {
        return self::block('desiderio_featurevideo', [
            'eyebrow' => $eyebrow,
            'header' => $header,
            'description' => $description,
            'video_file' => [
                'file' => 'Resources/Public/Styleguide/Video/desiderio-film-db360725.mp4',
                'title' => 'Desiderio in 26 seconds',
                'alternative' => 'A short film without sound: one page in 15 theme presets, light and dark mode, four languages and the editor tools.',
                'description' => 'Rendered from code over screens of this site.',
                'source' => '',
                'autoplay' => true,
            ],
            'poster' => [
                'file' => 'Resources/Public/Styleguide/Video/desiderio-film-poster-db360725.jpg',
                'title' => 'Desiderio in 26 seconds',
                'alternative' => 'The seed-runs chart in the Blossom preset, one of 15.',
                'description' => '',
                'source' => '',
            ],
        ]);
    }

    /**
     * A hero photo from Resources/Public/Styleguide/Heroes/. The photos are
     * generated with AI and show people who do not exist, which the site's
     * AI notice (desiderio.footer.aiNotice) discloses.
     *
     * @return ShowcaseMedia
     */
    public static function heroPhoto(string $filename, string $title, string $alternative): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Heroes/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'AI-generated image; the people shown do not exist.',
            'source' => '',
        ];
    }

    /**
     * @return ShowcaseMedia
     */
    public static function unsplash(string $filename, string $title, string $alternative): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Unsplash/' . $filename,
            'title' => $title,
            'alternative' => $alternative,
            'description' => 'Photo from the seeded Unsplash demo collection.',
            'source' => 'https://unsplash.com/collections/25880',
        ];
    }

    /**
     * A portrait from the shared cast of fictional Desiderio people in
     * Resources/Public/Styleguide/People/, so a name keeps the same face on
     * every page.
     *
     * @return ShowcaseMedia
     */
    public static function person(string $filename, string $name, string $alternative, string $description = 'Illustrative portrait, generated.'): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/People/' . $filename,
            'title' => $name . ' portrait',
            'alternative' => $alternative,
            'description' => $description,
            'source' => '',
        ];
    }

    /**
     * @return ShowcaseMedia
     */
    public static function portrait(string $filename, string $name): array
    {
        return [
            'file' => 'Resources/Public/Styleguide/Unsplash/' . $filename,
            'title' => $name . ' portrait',
            'alternative' => 'Portrait photo of ' . $name . '.',
            'description' => 'Portrait photo from the seeded Unsplash demo collection.',
            'source' => 'https://unsplash.com/collections/25880',
        ];
    }
}
