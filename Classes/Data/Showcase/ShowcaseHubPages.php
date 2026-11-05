<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The first-level hubs of the menu that have no chapter of their own: Product
 * (the elements, themes, tech facts and Innesto below it) and Lab and
 * resources (the lab overview, the other sites of the installation, news, the
 * Powermail Lab, the usability talk, the download and the documentation).
 * Features and AI are built in ShowcaseFeaturePages, Solutions in
 * ShowcaseAudiencePages, Pricing in ShowcasePricingPages.
 *
 * Every hub opens with the same sales hero and a photo in the same style.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
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
            'navTitle' => 'Elements and themes',
            'slug' => '/product',
            'seoTitle' => 'The design system for TYPO3 v14',
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
                        ['title' => 'Forms that send leads to Brevo', 'description_text' => 'Real TYPO3 forms with Brevo, double opt-in and Friendly Captcha.'],
                        ['title' => 'Search results in your design', 'description_text' => 'Templates for Apache Solr with facets, suggestions and accessible pagination.'],
                        ['title' => 'News and blog templates', 'description_text' => 'Lists, detail views and comment forms that follow the active theme.'],
                        ['title' => 'Dark mode and contrast checks', 'description_text' => 'Every preset in light and dark, checked for WCAG 2.2 AA contrast.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featurecards', [
                    'eyebrow' => 'Integrations',
                    'header' => 'Templates for the extensions you use',
                    'subheadline' => 'Each template set follows the active theme preset, in light and dark mode.',
                    'items' => [
                        ['title' => 'Solr search', 'description' => 'Results, facets, sorting, suggestions in the header and accessible pagination.'],
                        ['title' => 'Powermail', 'description' => 'All field types restyled, with demo forms and Friendly Captcha.'],
                        ['title' => 'News', 'description' => 'Teaser cards, detail views, lists and pagination for the News extension.'],
                        ['title' => 'Blog', 'description' => 'Post lists, sidebars and a comment form for the Blog extension.'],
                        ['title' => 'Form Framework', 'description' => 'Eight ready forms, with double opt-in through Brevo.'],
                        ['title' => 'Visual Editor', 'description' => 'Inline editing in the frontend, plus styled core content elements.'],
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
     * The lab: what this TYPO3 installation runs, with the lab overview film
     * after the hero (the films seeder places it there, so the hero stays
     * first), the other sites and the usability talk below it.
     *
     * @return ShowcasePage
     */
    public static function resourcesPage(): array
    {
        return [
            'title' => 'Lab and resources',
            'navTitle' => 'Lab and resources',
            'slug' => '/resources',
            'seoTitle' => 'The webconsulting TYPO3 lab and resources',
            'abstract' => 'What this TYPO3 v14 lab runs: Desiderio, Astryx, our software and ten template sites, plus news, demo forms, a usability talk and the download.',
            'description' => 'One TYPO3 v14 installation, live and free: Desiderio, Astryx, our software, template sites, news, the Powermail Lab and the demo to download.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::salesHero(
                    'The lab',
                    'The webconsulting TYPO3 lab',
                    'One TYPO3 v14 installation runs Desiderio, Astryx, our software and ten template sites. Everything here is live, free and seeded by command.',
                    ['text' => 'See everything', 'link' => '{{page:features}}'],
                    ['text' => 'Download the demo', 'link' => '{{page:downloads}}'],
                    ShowcaseHeroPhotos::for('resources'),
                ),
                self::labOverviewCards('Four groups, each with an overview page that leads to every tool, product and template.'),
                self::moreSitesCards(),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'eyebrow' => 'Resources',
                    'header' => 'Where to go next',
                    'subheadline' => 'Everything here is free to read and to try.',
                    'columns' => '3',
                    'items' => [
                        ['icon' => 'rss', 'title' => 'News', 'description' => 'Releases, new elements and what changes for your projects.', 'link' => '{{page:news}}'],
                        ['icon' => 'mail', 'title' => 'Powermail Lab', 'description' => 'Every demo form, with validation, conditions and spam protection.', 'link' => '{{page:desiderio-powermail-lab}}'],
                        ['icon' => 'book-open', 'title' => 'Documentation', 'description' => 'Installation, site sets, theming and seeding, on GitHub.', 'link' => ShowcaseBlocks::REPO_URL],
                        ['icon' => 'quote', 'title' => 'Example stories', 'description' => 'Invented organisations that show what the features do in practice.', 'link' => '{{page:success-stories}}'],
                        ['icon' => 'trending-up', 'title' => 'Our TYPO3 v14 strategy', 'description' => 'How we build for TYPO3 v14 and AI agents, and why.', 'link' => '{{page:typo3-v14-strategy}}'],
                        ['icon' => 'monitor', 'title' => 'Kurt’s usability talk', 'description' => 'Ten usability slogans after Jakob Nielsen, five minutes, with transcript.', 'link' => '{{page:usability-slogans}}'],
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

    /**
     * "Everything in this lab": the four groups of the lab with computed
     * counts. The home page and the lab page show the same cards, each with
     * its own subheadline.
     *
     * @return ShowcaseBlock
     */
    public static function labOverviewCards(string $subheadline): array
    {
        return ShowcaseBlocks::block('desiderio_categorycards', [
            'eyebrow' => 'Overview',
            'header' => 'Everything in this lab',
            'subheadline' => $subheadline,
            'columns' => '4',
            'items' => [
                ['title' => 'Website tools', 'count' => sprintf('%d tools', count(ShowcaseFeaturePages::websitePages()) - 1), 'description' => 'Themes, editing, search, forms, login and paid access: the extensions this site runs.', 'link' => '{{page:features}}'],
                ['title' => 'AI and agents', 'count' => sprintf('%d tools', count(ShowcaseFeaturePages::aiPages()) - 1), 'description' => 'AI for editors, skills with safety checks, and tools that open a site to outside agents.', 'link' => '{{page:ai}}'],
                ['title' => 'Software', 'count' => sprintf('%d products', count(ShowcaseFeaturePages::softwarePages()) - 1), 'description' => 'T3 Ops, Ledger, MedAtlas Austria and CapexOne Plus, built by webconsulting.', 'link' => '{{page:software}}'],
                ['title' => 'Website templates', 'count' => '10 templates', 'description' => 'Ten complete websites for TYPO3 v14, free under the MIT licence and live in this lab.', 'link' => '{{page:templates}}'],
            ],
        ]);
    }

    /**
     * One card per other site of the installation. The seeder links
     * {{site:<identifier>}} to that site's root page and leaves out the cards
     * (and the block) for sites an installation lacks.
     *
     * @return ShowcaseBlock
     */
    private static function moreSitesCards(): array
    {
        return ShowcaseBlocks::block('desiderio_categorycards', [
            'eyebrow' => 'This lab',
            'header' => 'More sites on this TYPO3 installation',
            'subheadline' => 'The same TYPO3 v14 installation runs these sites too, each with another theme, extension or language setup.',
            'columns' => '4',
            'items' => [
                ['title' => 'Astryx', 'count' => 'Second theme', 'description' => 'Meta’s Astryx design system for TYPO3: 250 server-rendered elements and 25 themes, without React.', 'link' => '{{site:astryx-typo3}}'],
                ['title' => 'Agent Nexus', 'count' => 'AI agents', 'description' => 'Agent protocols in TYPO3: A2UI, AG-UI, A2A, UCP and AP2, each with a demo, plus a playground.', 'link' => '{{site:agent-nexus}}'],
                ['title' => 'Northstar Advisory Group', 'count' => 'Starter site', 'description' => 'A complete company website from Desiderio’s corporate starter, seeded with one command.', 'link' => '{{site:desiderio-corporate-starter}}'],
                ['title' => 'TYPO3 Camp München 2026', 'count' => 'Event site', 'description' => 'A barcamp site in German, English, Chinese and Hungarian, with Solr search.', 'link' => '{{site:mtug-camp-munich-2026}}'],
                ['title' => 'The TYPO3 blog', 'count' => 'Blog', 'description' => 'Practical posts about TYPO3 in Desiderio’s Classic blog template.', 'link' => '{{site:typo3-blog}}'],
                ['title' => 'TYPO3 v14 blog', 'count' => 'Blog', 'description' => 'A demo blog in the Modern template, with an archive by category, tag, author and month.', 'link' => '{{site:14lts}}'],
                ['title' => 'Blog classico', 'count' => 'Blog in German', 'description' => 'A German lifestyle blog in the Classic template, about spring, Easter and family days.', 'link' => '{{site:blog}}'],
                ['title' => 'Camino', 'count' => 'Own site package', 'description' => 'A guide to the Way of St James, built with the Camino site package and its own content elements.', 'link' => '{{site:camino}}'],
            ],
        ]);
    }
}
