<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The /features and /ai sections: two hubs that list the features built in
 * the lab, by category, and one page per feature. Every feature page has the same
 * blocks, so the pages read alike and a new one is a definition, not a layout:
 *
 *  1. hero with the product badge, the outcome, two buttons and the best screenshot
 *  2. the problem the reader has today
 *  3. a tour: the other live screenshots in a gallery with a lightbox
 *  4. four benefits
 *  5. the questions people ask before they install
 *  6. the install commands
 *  7. for an extension by other people, a thank-you to them (the definition's credits)
 *  8. a call to action
 *
 * The copy and the screenshots live in ShowcaseFeatureDefinitions.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcaseMedia from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 * @phpstan-import-type FeatureDefinition from ShowcaseFeatureDefinitions
 * @phpstan-import-type FeatureCategory from ShowcaseFeatureDefinitions
 * @phpstan-import-type FeatureHub from ShowcaseFeatureDefinitions
 */
final class ShowcaseFeaturePages
{
    /**
     * Both sections: Features first, then AI.
     *
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [...self::websitePages(), ...self::aiPages()];
    }

    /**
     * The Features hub and the pages of the website tools.
     *
     * @return array<int, ShowcasePage>
     */
    public static function websitePages(): array
    {
        return self::section(ShowcaseFeatureDefinitions::hub(), ShowcaseFeatureDefinitions::categories(), 'features', null);
    }

    /**
     * The AI hub and the pages of the AI tools. They lived below /features
     * until the menu split, so the seeder moves them and redirects the old
     * URLs.
     *
     * @return array<int, ShowcasePage>
     */
    public static function aiPages(): array
    {
        return self::section(ShowcaseFeatureDefinitions::aiHub(), ShowcaseFeatureDefinitions::aiCategories(), 'ai', 'features');
    }

    /**
     * @param FeatureHub $hub
     * @param list<FeatureCategory> $categories
     * @return array<int, ShowcasePage>
     */
    private static function section(array $hub, array $categories, string $section, ?string $formerSection): array
    {
        $pages = [self::hubPage($hub, $categories, $section)];
        foreach ($categories as $category) {
            foreach ($category['features'] as $feature) {
                $pages[] = self::featurePage($feature, $section, $formerSection);
            }
        }

        return $pages;
    }

    /**
     * @param FeatureHub $hub
     * @param list<FeatureCategory> $categories
     * @return ShowcasePage
     */
    private static function hubPage(array $hub, array $categories, string $section): array
    {
        $content = [
            ShowcaseBlocks::salesHero(
                $hub['badge'],
                $hub['header'],
                $hub['subheadline'],
                $hub['primaryButton'],
                $hub['secondaryButton'],
                $hub['image'],
            ),
        ];
        foreach ($categories as $category) {
            $content[] = self::categoryBlock($category, $section);
        }
        if ($section === 'ai') {
            $content[] = ShowcaseBlocks::block('desiderio_benefitcards', [
                'eyebrow' => 'Strategy',
                'header' => 'AI search and our TYPO3 v14 plan',
                'subheadline' => 'How your content gets quoted by AI search, and how we build for AI agents.',
                'columns' => '2',
                'items' => [
                    ['icon' => 'search', 'title' => 'GEO and AI search', 'description' => 'What makes AI search quote a page, and how Desiderio helps.', 'link' => '{{page:geo-ai-search}}'],
                    ['icon' => 'trending-up', 'title' => 'TYPO3 v14 and AI agents', 'description' => 'Our strategy for TYPO3 v14, AI agents and the next LTS.', 'link' => '{{page:typo3-v14-strategy}}'],
                ],
            ]);
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
            'slug' => '/' . $section,
            'seoTitle' => $hub['seoTitle'],
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
    private static function categoryBlock(array $category, string $section): array
    {
        $posts = [];
        foreach ($category['features'] as $feature) {
            $posts[] = [
                'category' => $category['label'],
                'meta' => $feature['product'],
                'title' => $feature['navTitle'],
                'excerpt' => '<p>' . $feature['abstract'] . '</p>',
                'link' => '{{page:' . $section . '/' . $feature['slug'] . '}}',
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
    private static function featurePage(array $feature, string $section, ?string $formerSection): array
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
                    static fn(array $shot): array => [
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
                    static fn(array $benefit): array => [
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
                    static fn(array $item): array => ['question' => $item['question'], 'answer' => '<p>' . $item['answer'] . '</p>'],
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
        // Below the main content and above the closing call to action: the
        // page presents other people's work, so it thanks them.
        if (isset($feature['credits'])) {
            $content[] = ShowcaseBlocks::thankYou($feature['credits']);
        }
        $content[] = ShowcaseBlocks::block('desiderio_ctabanner', [
            'header' => $feature['cta']['header'],
            'description' => $feature['cta']['description'],
            'cta_text' => $feature['cta']['text'],
            'cta_link' => $feature['cta']['link'],
            'bg_style' => 'primary',
        ]);

        $page = [
            'title' => $feature['title'],
            'navTitle' => $feature['navTitle'],
            'slug' => '/' . $section . '/' . $feature['slug'],
            'seoTitle' => '',
            'abstract' => $feature['abstract'],
            'description' => $feature['description'],
            'parentSlug' => $section,
            'content' => $content,
        ];
        if ($formerSection !== null) {
            $page['formerSlugs'] = ['/' . $formerSection . '/' . $feature['slug']];
        }

        return $page;
    }
}
