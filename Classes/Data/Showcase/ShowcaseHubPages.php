<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The first-level hubs of the menu that have no chapter of their own: Product
 * (the elements, themes, tech facts and Innesto below it) and Resources (news,
 * the Powermail Lab, the download and the documentation). Features and AI are
 * built in ShowcaseFeaturePages, Solutions in ShowcaseAudiencePages, Pricing
 * in ShowcasePricingPages.
 *
 * Every hub opens with the same sales hero and a photo in the same style.
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseHubPages
{
    /**
     * @return ShowcasePage
     */
    public static function productPage(): array
    {
        return [
            'title' => 'Product',
            'navTitle' => 'Product',
            'slug' => '/product',
            'seoTitle' => 'Desiderio: the design system for TYPO3 v14',
            'abstract' => 'What is in Desiderio: 244 content elements, 62 typed components, 15 theme presets and templates for the extensions you already use.',
            'description' => 'Desiderio for TYPO3 v14: 244 content elements, 62 typed components, 15 runtime theme presets and templates for Solr, News, Blog and Powermail.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::salesHero(
                    'One Composer package',
                    'Everything a TYPO3 client site needs',
                    'Elements, components, themes and templates for the extensions you already use, tested together and themed at runtime.',
                    ['text' => 'Browse the elements', 'link' => '{{page:content-types}}'],
                    ['text' => 'Compare the themes', 'link' => '{{page:themes}}'],
                    ShowcaseHeroPhotos::for('product'),
                ),
                ShowcaseBlocks::block('desiderio_featurestats', [
                    'header' => 'What is in the package',
                    'description' => 'Numbers you can check in the code on GitHub.',
                    'items' => [
                        ['value' => '244', 'label' => 'Content elements', 'description_text' => 'In 10 groups, each with a backend preview and a live demo page.'],
                        ['value' => '62', 'label' => 'Typed components', 'description_text' => '17 atoms, 37 molecules, 4 layouts and 4 organisms in Fluid 5.'],
                        ['value' => '15', 'label' => 'Theme presets', 'description_text' => 'Switch at runtime, per site or per page tree, in light and dark mode.'],
                        ['value' => '8', 'label' => 'PHPStan level', 'description_text' => 'With unit and functional tests on PHP 8.4 and 8.5.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'eyebrow' => 'Look closer',
                    'header' => 'Four ways into the product',
                    'subheadline' => 'Start where your project starts: the elements, the look, the code or your own components.',
                    'columns' => '2',
                    'items' => [
                        ['icon' => 'layout-grid', 'title' => 'Content types', 'description' => 'All 244 elements in 10 chapters, each chapter in its own theme preset.', 'link' => '{{page:content-types}}'],
                        ['icon' => 'sun', 'title' => 'Themes', 'description' => 'All 15 presets side by side, rendered live, with the fonts and colours of each.', 'link' => '{{page:themes}}'],
                        ['icon' => 'code', 'title' => 'Tech facts', 'description' => 'Typed components, design tokens, accessibility checks and the quality gates of the build.', 'link' => '{{page:technical-features}}'],
                        ['icon' => 'component', 'title' => 'Innesto', 'description' => 'Turn a shadcn/ui registry component into a new Desiderio element with one command.', 'link' => '{{page:innesto-demo}}'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featurechecklist', [
                    'eyebrow' => 'Already built',
                    'header' => 'What your team no longer builds',
                    'items' => [
                        ['title' => 'Headers, footers and menus', 'description_text' => '24 navigation and 23 footer elements, with mega menus and language switchers.'],
                        ['title' => 'Pricing tables and calculators', 'description_text' => '25 pricing elements, from plan tables to sliders and order summaries.'],
                        ['title' => 'Forms that reach your CRM', 'description_text' => 'Real TYPO3 forms with Brevo, double opt-in and Friendly Captcha.'],
                        ['title' => 'Search results in your design', 'description_text' => 'Templates for Apache Solr with facets, suggestions and accessible pagination.'],
                        ['title' => 'News and blog templates', 'description_text' => 'Lists, detail views and comment forms that follow the active theme.'],
                        ['title' => 'Dark mode and contrast checks', 'description_text' => 'Every preset in light and dark, checked for WCAG 2.2 AA contrast.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Try every element on your own machine',
                    'description' => 'Download this complete demo site and run it in DDEV, with all 244 elements and every extension.',
                    'cta_text' => 'Download the demo',
                    'cta_link' => '{{page:downloads}}',
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * @return ShowcasePage
     */
    public static function resourcesPage(): array
    {
        return [
            'title' => 'Resources',
            'navTitle' => 'Resources',
            'slug' => '/resources',
            'seoTitle' => 'Desiderio resources: news, demos and documentation',
            'abstract' => 'News about Desiderio, the Powermail Lab with every demo form, the complete demo site to download and the documentation.',
            'description' => 'Learn and try Desiderio: read the news, test every form in the Powermail Lab, download the demo site and read the documentation.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::salesHero(
                    'Learn and try',
                    'Everything to learn and try Desiderio',
                    'Read the news, test every form in the Powermail Lab, download the complete demo and read the documentation.',
                    ['text' => 'Read the news', 'link' => '{{page:news}}'],
                    ['text' => 'Download the demo', 'link' => '{{page:downloads}}'],
                    ShowcaseHeroPhotos::for('resources'),
                ),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'eyebrow' => 'Resources',
                    'header' => 'Where to go next',
                    'subheadline' => 'Everything here is free to read and to try.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'rss', 'title' => 'News', 'description' => 'Releases, new elements and what changes for your projects.', 'link' => '{{page:news}}'],
                        ['icon' => 'mail', 'title' => 'Powermail Lab', 'description' => 'Every demo form, with validation, conditions and spam protection.', 'link' => '{{page:desiderio-powermail-lab}}'],
                        ['icon' => 'download', 'title' => 'Download the demo', 'description' => 'This whole site with its database and files, set up in DDEV by one script.', 'link' => '{{page:downloads}}'],
                        ['icon' => 'book-open', 'title' => 'Documentation', 'description' => 'Installation, site sets, theming and seeding, on GitHub.', 'link' => ShowcaseBlocks::REPO_URL],
                        ['icon' => 'quote', 'title' => 'Example stories', 'description' => 'Invented organisations that show what the features do in practice.', 'link' => '{{page:success-stories}}'],
                        ['icon' => 'trending-up', 'title' => 'Our TYPO3 v14 strategy', 'description' => 'How we build for TYPO3 v14 and AI agents, and why.', 'link' => '{{page:typo3-v14-strategy}}'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Questions before you start?',
                    'description' => 'Book 20 minutes with a maintainer. We show the parts that matter for your projects.',
                    'cta_text' => 'Book a demo',
                    'cta_link' => ShowcasePricing::ORDER_LINK,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }
}
