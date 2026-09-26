<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The /features section: a hub that lists every feature built in the lab, by
 * category, and one page per feature. Every feature page has the same seven
 * blocks, so the pages read alike and a new one is a definition, not a layout:
 *
 *  1. hero with the product badge, the outcome, two buttons and the best screenshot
 *  2. the problem the reader has today
 *  3. a tour: the other live screenshots in a gallery with a lightbox
 *  4. four benefits
 *  5. the questions people ask before they install
 *  6. the install commands
 *  7. a call to action
 *
 * The copy and the screenshots live in ShowcaseFeatureDefinitions.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcaseMedia from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 * @phpstan-import-type FeatureDefinition from ShowcaseFeatureDefinitions
 * @phpstan-import-type FeatureCategory from ShowcaseFeatureDefinitions
 */
final class ShowcaseFeaturePages
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        $pages = [self::hubPage()];
        foreach (ShowcaseFeatureDefinitions::categories() as $category) {
            foreach ($category['features'] as $feature) {
                $pages[] = self::featurePage($feature);
            }
        }

        return $pages;
    }

    /**
     * @return ShowcasePage
     */
    private static function hubPage(): array
    {
        $hub = ShowcaseFeatureDefinitions::hub();
        $content = [
            ShowcaseBlocks::block('desiderio_herosaas', [
                'badge_text' => $hub['badge'],
                'header' => $hub['header'],
                'subheadline' => $hub['subheadline'],
                'primary_button_text' => $hub['primaryButton']['text'],
                'primary_button_link' => $hub['primaryButton']['link'],
                'secondary_button_text' => $hub['secondaryButton']['text'],
                'secondary_button_link' => $hub['secondaryButton']['link'],
                'dashboard_image' => $hub['image'],
            ]),
        ];
        foreach (ShowcaseFeatureDefinitions::categories() as $category) {
            $content[] = self::categoryBlock($category);
        }
        $content[] = ShowcaseBlocks::block('desiderio_ctabanner', [
            'header' => $hub['cta']['header'],
            'description' => $hub['cta']['description'],
            'cta_text' => $hub['cta']['text'],
            'cta_link' => $hub['cta']['link'],
            'bg_style' => 'primary',
        ]);

        return [
            'title' => $hub['title'],
            'navTitle' => $hub['navTitle'],
            'slug' => '/features',
            // The page title is the SEO title; this clears older ones (the hub had one
            // from before the rework, the nr-llm page one from its old seeder).
            'seoTitle' => '',
            'abstract' => $hub['abstract'],
            'description' => $hub['description'],
            'parentSlug' => null,
            'content' => $content,
        ];
    }

    /**
     * One card per feature, each with the feature's hero screenshot.
     *
     * @param FeatureCategory $category
     * @return ShowcaseBlock
     */
    private static function categoryBlock(array $category): array
    {
        $posts = [];
        foreach ($category['features'] as $feature) {
            $posts[] = [
                'category' => $category['label'],
                'meta' => $feature['product'],
                'title' => $feature['navTitle'],
                'excerpt' => '<p>' . $feature['abstract'] . '</p>',
                'link' => '{{page:features/' . $feature['slug'] . '}}',
                'image' => $feature['hero']['image'],
            ];
        }

        return ShowcaseBlocks::block('desiderio_blogteasers', [
            'header' => $category['header'],
            'subheadline' => $category['subheadline'],
            'columns' => '3',
            'posts' => $posts,
        ]);
    }

    /**
     * @param FeatureDefinition $feature
     * @return ShowcasePage
     */
    private static function featurePage(array $feature): array
    {
        $content = [
            ShowcaseBlocks::block('desiderio_herosaas', [
                'badge_text' => $feature['badge'],
                'header' => $feature['hero']['header'],
                'subheadline' => $feature['hero']['subheadline'],
                'primary_button_text' => $feature['hero']['primaryButton']['text'],
                'primary_button_link' => $feature['hero']['primaryButton']['link'],
                'secondary_button_text' => $feature['hero']['secondaryButton']['text'],
                'secondary_button_link' => $feature['hero']['secondaryButton']['link'],
                'dashboard_image' => $feature['hero']['image'],
            ]),
            ShowcaseBlocks::block('desiderio_contenthighlight', [
                'header' => $feature['problem']['header'],
                'content' => $feature['problem']['content'],
                'variant' => 'muted',
                'alignment' => 'center',
                'link' => '',
                'link_text' => '',
            ]),
            // The hero shows one screenshot, the gallery the others: a large
            // featured image with its caption, thumbnails to switch, and a
            // lightbox with the full resolution.
            ShowcaseBlocks::block('desiderio_gallery', [
                'header' => $feature['tour']['header'],
                'subheadline' => $feature['tour']['subheadline'],
                'columns' => (string)min(4, max(2, count($feature['tour']['shots']))),
                'items' => array_map(
                    static fn (array $shot): array => [
                        'title' => $shot['title'],
                        // The gallery prints its captions as plain text.
                        'description' => strip_tags($shot['description']),
                        'link' => '',
                        'image' => $shot['image'],
                    ],
                    $feature['tour']['shots'],
                ),
            ]),
            ShowcaseBlocks::block('desiderio_benefitcards', [
                'header' => $feature['benefits']['header'],
                'eyebrow' => $feature['badge'],
                'columns' => '2',
                'items' => array_map(
                    static fn (array $benefit): array => [
                        'icon' => $benefit['icon'],
                        'title' => $benefit['title'],
                        'description' => $benefit['description'],
                        'link' => '',
                    ],
                    $feature['benefits']['items'],
                ),
            ]),
            ShowcaseBlocks::block('desiderio_faq', [
                'header' => $feature['faq']['header'],
                'subheadline' => $feature['faq']['subheadline'],
                'items' => array_map(
                    static fn (array $item): array => ['question' => $item['question'], 'answer' => '<p>' . $item['answer'] . '</p>'],
                    $feature['faq']['items'],
                ),
            ]),
        ];
        if ($feature['install'] !== null) {
            $content[] = ShowcaseBlocks::block('desiderio_codeblock', [
                'header' => $feature['install']['header'],
                'language' => 'bash',
                'filename' => 'terminal',
                'code' => $feature['install']['code'],
            ]);
        }
        $content[] = ShowcaseBlocks::block('desiderio_ctabanner', [
            'header' => $feature['cta']['header'],
            'description' => $feature['cta']['description'],
            'cta_text' => $feature['cta']['text'],
            'cta_link' => $feature['cta']['link'],
            'bg_style' => 'primary',
        ]);

        return [
            'title' => $feature['title'],
            'navTitle' => $feature['navTitle'],
            'slug' => '/features/' . $feature['slug'],
            'seoTitle' => '',
            'abstract' => $feature['abstract'],
            'description' => $feature['description'],
            'parentSlug' => 'features',
            'content' => $content,
        ];
    }
}
