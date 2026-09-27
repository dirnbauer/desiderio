<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The hero photos of the homepage and the first-level pages: one style, real
 * working situations, a diverse cast. They are generated with AI (the people
 * do not exist), 16:10 at 1440 x 900 like the split hero shows them, and
 * named by content hash so a new photo never hides behind an old cache.
 *
 * @phpstan-import-type ShowcaseMedia from ShowcaseBlocks
 */
final class ShowcaseHeroPhotos
{
    /**
     * @var array<string, array{file: string, title: string, alternative: string}>
     */
    private const array PHOTOS = [
        'home' => [
            'file' => 'hero-home-e44c53bc.webp',
            'title' => 'An agency team reviews a website design',
            'alternative' => 'Four colleagues of different ages and backgrounds gather around a large monitor and review a website design together.',
        ],
        'product' => [
            'file' => 'hero-product-74e1a392.webp',
            'title' => 'Comparing colours and interface sketches',
            'alternative' => 'A woman wearing a hijab and an older man with a grey beard compare a tablet with colour swatches and interface sketches.',
        ],
        'features' => [
            'file' => 'hero-features-a2db80eb.webp',
            'title' => 'Editing a web page together',
            'alternative' => 'An editor works on a web page on her laptop while a colleague next to her points at the screen.',
        ],
        'ai' => [
            'file' => 'hero-ai-46291510.webp',
            'title' => 'Two developers look at an AI assistant',
            'alternative' => 'A young developer and an older colleague with glasses look at a chat panel on a laptop screen.',
        ],
        'solutions' => [
            'file' => 'hero-solutions-8e3c28e0.webp',
            'title' => 'A client meeting in a bright room',
            'alternative' => 'Three professionals at a meeting table with laptops; a man in a wheelchair presents while two colleagues listen.',
        ],
        'pricing' => [
            'file' => 'hero-pricing-08a48e08.webp',
            'title' => 'Two agency founders review a proposal',
            'alternative' => 'A woman with curly red hair and a man with grey hair review a proposal on a laptop, with a printed page beside it.',
        ],
        'resources' => [
            'file' => 'hero-resources-a6eff766.webp',
            'title' => 'Reading the documentation',
            'alternative' => 'A woman with silver hair and reading glasses reads on a tablet in a quiet co-working space with bookshelves.',
        ],
        'downloads' => [
            'file' => 'hero-downloads-179cee0f.webp',
            'title' => 'A developer sets up the lab',
            'alternative' => 'A developer at a standing desk with two monitors smiles as her setup finishes, in a bright home office.',
        ],
    ];

    /**
     * @return ShowcaseMedia
     */
    public static function for(string $page): array
    {
        $photo = self::PHOTOS[$page] ?? throw new \InvalidArgumentException(sprintf('No hero photo for "%s".', $page), 1790500000);

        return ShowcaseBlocks::heroPhoto($photo['file'], $photo['title'], $photo['alternative']);
    }

    /**
     * @return list<string>
     */
    public static function pages(): array
    {
        return array_keys(self::PHOTOS);
    }
}
