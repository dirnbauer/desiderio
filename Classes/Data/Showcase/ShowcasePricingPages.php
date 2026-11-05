<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The pricing page, the model page ("How we earn") and the order page below
 * it. Every number comes from ShowcasePricing.
 *
 * The page follows the buyer's questions in order: what it costs, what the
 * paid plans add, how the plans differ, what we build for you, why now, what
 * if it goes wrong, and the remaining questions. The model page explains why
 * the software is free and what costs money; both pages show the same Care
 * block (careBlock()).
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 */
final class ShowcasePricingPages
{
    /** The model page, which every sales page links once. */
    private const string MODEL_PAGE_LINK = '{{page:pricing/how-we-earn}}';

    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [self::pricingPage(), self::howWeEarnPage(), self::orderPage()];
    }

    /**
     * @return ShowcasePage
     */
    private static function pricingPage(): array
    {
        $plans = ShowcasePricing::plans();
        $launchPack = ShowcasePricing::launchPack();
        $founding = ShowcasePricing::foundingOffer();

        return [
            'title' => 'Pricing',
            'navTitle' => 'Pricing and plans',
            'slug' => '/pricing',
            'seoTitle' => 'Prices for support plans and services',
            'abstract' => 'Desiderio is free under GPL-2.0, with every release. The Studio, Agency and Partner support plans add guaranteed answer times and the maintenance promise; services have fixed prices.',
            'description' => 'Desiderio is free. Support plans: Studio €590, Agency €1,990, Partner €4,900 a year, with guaranteed answer times; services at fixed prices.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::salesHero(
                    'Plans and services',
                    'Free to use. Paid plans add support.',
                    'Every element and every release is free and open source. Plans add guaranteed answer times and the maintenance promise; Care runs your site for you.',
                    ['text' => 'Choose a plan', 'link' => ShowcasePricing::ORDER_LINK],
                    ['text' => 'How we earn', 'link' => self::MODEL_PAGE_LINK],
                    ShowcaseHeroPhotos::for('pricing'),
                ),
                ShowcaseBlocks::block('desiderio_pricingfourtier', [
                    'eyebrow' => 'Support plans',
                    'header' => 'One price per year, by live sites',
                    'subheadline' => 'Prices are net. Staging and local installations are always free, and yearly billing saves two months.',
                    'plans' => array_map(
                        static fn(array $plan): array => [
                            'title' => $plan['name'],
                            'price' => $plan['price'],
                            'description' => ucfirst($plan['period']) . '. ' . $plan['for'],
                            'features' => array_map(static fn(string $feature): array => ['text' => $feature], $plan['features']),
                            'featured' => $plan['featured'],
                            'button_text' => $plan['button']['text'],
                            'button_link' => $plan['button']['link'],
                        ],
                        $plans,
                    ),
                ]),
                ShowcaseBlocks::block('desiderio_trustbadges', [
                    'header' => 'What every support plan promises',
                    'subheadline' => 'In the terms, not only on this page.',
                    'badge_items' => self::promiseBadges(),
                ]),
                ShowcaseBlocks::block('desiderio_pricingcomparison', [
                    'eyebrow' => 'Compare',
                    'header' => 'The plans side by side',
                    'plans' => array_map(
                        static fn(array $plan): array => ['name' => $plan['name'], 'price' => $plan['price']],
                        $plans,
                    ),
                    'pricing_feature_items' => array_map(
                        static fn(array $row): array => [
                            'feature_name' => $row['feature'],
                            'tier_values' => array_map(static fn(string $value): array => ['value' => $value], $row['values']),
                        ],
                        ShowcasePricing::comparison(),
                    ),
                ]),
                self::careBlock(),
                ShowcaseBlocks::block('desiderio_pricingusage', [
                    'eyebrow' => 'Services',
                    'header' => 'Fixed-price services for your projects',
                    'subheadline' => 'Written scope, fixed price, and delivered under your agency’s name if you like.',
                    'items' => array_map(
                        static fn(array $service): array => [
                            'title' => $service['title'],
                            'rate' => $service['rate'],
                            'unit' => $service['unit'],
                            'description' => $service['description'],
                            'link' => ShowcasePricing::ORDER_LINK,
                        ],
                        ShowcasePricing::services(),
                    ),
                ]),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'eyebrow' => 'Thank you',
                    'header' => 'Other ways to support',
                    'subheadline' => 'For teams that earn with Desiderio and want it to stay free and current.',
                    'columns' => '3',
                    'items' => [
                        ...self::supportCards(),
                        [
                            'icon' => 'star',
                            'title' => 'Stars, issues and articles',
                            'description' => 'Free. Star the repository, report what breaks or write about Desiderio. It helps other teams find it.',
                            'link' => ShowcaseBlocks::REPO_URL,
                        ],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_bundlepricing', [
                    'header' => $launchPack['header'],
                    'eyebrow' => $launchPack['eyebrow'],
                    'bundle_price' => $launchPack['price'],
                    'savings_text' => $launchPack['savings'],
                    'description' => $launchPack['description'],
                    'items' => array_map(
                        static fn(array $item): array => [
                            'name' => $item['name'],
                            'individual_price' => $item['price'],
                            'description_text' => $item['description'],
                        ],
                        $launchPack['items'],
                    ),
                    'button_text' => 'Order Launch Pack',
                    'button_link' => ShowcasePricing::ORDER_LINK,
                ]),
                ShowcaseBlocks::block('desiderio_contenthighlight', [
                    'header' => $founding['header'],
                    'content' => $founding['content'],
                    'variant' => 'muted',
                    'alignment' => 'center',
                    'link' => ShowcasePricing::ORDER_LINK,
                    'link_text' => 'Claim a place',
                ]),
                ShowcaseBlocks::block('desiderio_pricingfaq', [
                    'eyebrow' => 'Questions',
                    'header' => 'Before you choose a plan',
                    'subheadline' => 'The questions agencies ask us most.',
                    'question_items' => self::questions(self::pricingFaq()),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Talk to a maintainer first',
                    'description' => '20 minutes: we look at your projects and show the parts that matter for them.',
                    'cta_text' => 'Book a demo',
                    'cta_link' => ShowcasePricing::ORDER_LINK,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * The model page: free software, paid care. It answers why the software
     * is free, what is always free, what costs money, where the money goes
     * and who invoices, in the calm tone of the sales pages: one price per
     * fact, every price from ShowcasePricing. The films seeder places the
     * model film after the hero.
     *
     * @return ShowcasePage
     */
    private static function howWeEarnPage(): array
    {
        $plans = ShowcasePricing::plans();
        $studio = $plans[1];
        $care = ShowcasePricing::care();
        $rates = array_column(ShowcasePricing::services(), 'rate', 'title');

        return [
            'title' => 'Free software, paid care',
            'navTitle' => 'How we earn',
            'slug' => '/pricing/how-we-earn',
            'seoTitle' => 'How we earn with free TYPO3 software',
            'abstract' => 'Desiderio is free with every release. This page says why, what is always free, what costs money and where the money goes.',
            'description' => sprintf('%s Support plans from %s a year, Care at %s %s.', ShowcasePricing::modelSentence(), $studio['price'], $care['price'], $care['period']),
            'parentSlug' => 'pricing',
            'content' => [
                ShowcaseBlocks::salesHero(
                    'Open source',
                    'Free software, paid care',
                    'Desiderio and every tool in this lab are free, with every release. You pay when you want our time: answers, work by a date, or running your site.',
                    ['text' => 'See the plans', 'link' => '{{page:pricing}}'],
                    ['text' => 'Download the demo', 'link' => '{{page:downloads}}'],
                    ShowcaseHeroPhotos::for('how-we-earn'),
                ),
                // Benefit cards, not feature cards: they carry the icon and the
                // link of each way, and two columns hold four cards without an
                // orphan (the feature-card grid has three).
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'eyebrow' => 'The model',
                    'header' => 'Four ways to use Desiderio',
                    'subheadline' => ShowcasePricing::modelSentence(),
                    'columns' => '2',
                    'items' => array_map(
                        static fn(array $way): array => [
                            'icon' => $way['icon'],
                            'title' => $way['title'],
                            'description' => $way['description'],
                            'link' => $way['link'],
                        ],
                        ShowcasePricing::ways(),
                    ),
                ]),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Why the software is free',
                    'shadcn_layout' => 'media-left',
                    'subheadline' => 'One public codebase for every customer',
                    'content' => '<p><strong>TYPO3 is GPL, and so is everything built on it.</strong> A paid version would need a private copy, licence keys and a second codebase. One public codebase is cheaper for us and safer for you.</p>'
                        . '<p>Free use is also how we find our customers. When a client site goes live and a TYPO3 patch lands, its builders want answers and the maintenance promise. That is what the plans sell.</p>'
                        . '<p>Some vendors sell early access to their extensions. We release everything the day it ships, because a release that waits for payment is a security fix that waits.</p>',
                    'media_rounded' => 1,
                    'media' => ShowcaseHeroPhotos::for('pricing'),
                ]),
                ShowcaseBlocks::block('desiderio_compare', [
                    'eyebrow' => 'In one look',
                    'header' => 'What is always free, and what costs money',
                    'left_title' => 'Always free',
                    'left_description' => '<p>For everyone, on every site, without registration.</p>',
                    'left_points' => array_map(static fn(string $point): array => ['text' => $point], ShowcasePricing::alwaysFree()),
                    'left_button_text' => 'Install for free',
                    'left_button_link' => ShowcaseBlocks::REPO_URL,
                    'right_badge' => 'Our time',
                    'right_title' => 'What costs money',
                    'right_description' => '<p>Everything we charge for is our time, at a fixed price.</p>',
                    'right_points' => array_map(static fn(string $point): array => ['text' => $point], ShowcasePricing::costsMoney()),
                    'right_button_text' => 'See the plans',
                    'right_button_link' => '{{page:pricing}}',
                ]),
                // Numbers from the content canon (facts.desiderio.elements,
                // .static_analysis) and the maintenance promise.
                ShowcaseBlocks::block('desiderio_stats', [
                    'header' => 'Where the money goes',
                    'subheadline' => 'Plan fees pay for the work every user shares, paying or not.',
                    'columns' => '4',
                    'stats' => [
                        ['value' => '244', 'label' => 'Content elements', 'description' => '<p>Kept current with every TYPO3 release</p>'],
                        ['value' => '10', 'label' => 'Business days per patch', 'description' => '<p>Every TYPO3 v14 patch release supported</p>'],
                        ['value' => '5', 'label' => 'Business days for security', 'description' => '<p>Confirmed security issues fixed, for everyone</p>'],
                        ['value' => 'Level 8', 'label' => 'PHPStan on every change', 'description' => '<p>Static analysis before every release</p>'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_featuretimeline', [
                    'header' => 'A typical path',
                    'subheadline' => 'Most teams start for free and pay when a live site needs answers.',
                    'items' => [
                        ['step' => '1', 'title' => 'Day one: install and build', 'description' => '<p>Free: every element, every preset and every release, on as many sites as you like.</p>'],
                        ['step' => '2', 'title' => 'The first live client site', 'description' => sprintf('<p>Choose %s: %s %s. It adds answers and the maintenance promise.</p>', $studio['name'], $studio['price'], $studio['period'])],
                        ['step' => '3', 'title' => 'Your brand or a new element', 'description' => sprintf('<p>A Brand Theme for %s or a custom element from %s, at a fixed price with a written scope.</p>', $rates['Brand Theme'], $rates['Custom element S'])],
                        ['step' => '4', 'title' => 'One partner for everything', 'description' => sprintf('<p>%s runs your site: hosting, updates and monitoring, %s %s.</p>', $care['name'], $care['price'], $care['period'])],
                    ],
                ]),
                self::careBlock(),
                ShowcaseBlocks::block('desiderio_benefitcards', [
                    'eyebrow' => 'Thank you',
                    'header' => 'Two more ways to support the work',
                    'subheadline' => 'Stars, bug reports and articles help too, and cost nothing.',
                    'columns' => '2',
                    'items' => self::supportCards(),
                ]),
                ShowcaseBlocks::block('desiderio_trustbadges', [
                    'header' => 'What every paid plan promises',
                    'subheadline' => 'In the terms, not only on this page.',
                    'badge_items' => self::promiseBadges(),
                ]),
                ShowcaseBlocks::block('desiderio_faq', [
                    'header' => 'Honest questions',
                    'subheadline' => 'What buyers ask about free software from a small team.',
                    'items' => self::questions(ShowcasePricing::modelFaq()),
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Use it today. Call us for help.',
                    'description' => 'Install Desiderio for free, or tell us the plan and the number of live sites. A maintainer answers within one business day.',
                    'cta_text' => 'Ask a maintainer',
                    'cta_link' => ShowcasePricing::ORDER_LINK,
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /**
     * Care, the offer that runs a live site: one block, shown on the pricing
     * page after the plan comparison and on the model page. Not a plan
     * column: the plans sell answers, Care sells running the site.
     *
     * @return ShowcaseBlock
     */
    private static function careBlock(): array
    {
        $care = ShowcasePricing::care();

        return ShowcaseBlocks::block('desiderio_pricingsimple', [
            'header' => $care['header'],
            'price' => $care['price'],
            'billing_period' => $care['period'],
            'description' => '<p>' . $care['description'] . '</p>',
            'features' => array_map(
                static fn(string $feature): array => ['text' => $feature],
                [...$care['features'], $care['yearly'], $care['further_sites']],
            ),
            'button_text' => $care['button']['text'],
            'button_link' => $care['button']['link'],
        ]);
    }

    /**
     * The Supporter and the sponsored feature as cards: the two ways to pay
     * without buying support.
     *
     * @return list<array{icon: string, title: string, description: string, link: string}>
     */
    private static function supportCards(): array
    {
        $cards = [];
        foreach ([['heart', ShowcasePricing::supporter()], ['code', ShowcasePricing::sponsoredFeature()]] as [$icon, $offer]) {
            $cards[] = [
                'icon' => $icon,
                'title' => sprintf('%s, %s %s', $offer['name'], $offer['price'], $offer['period']),
                'description' => $offer['description'],
                'link' => $offer['button']['link'],
            ];
        }

        return $cards;
    }

    /**
     * The promises behind every paid plan, as trust badges.
     *
     * @return list<array{label: string, icon_name: string, description_text: string}>
     */
    private static function promiseBadges(): array
    {
        return array_map(
            static fn(array $promise): array => [
                'label' => $promise['label'],
                'icon_name' => $promise['icon'],
                'description_text' => $promise['description'],
            ],
            ShowcasePricing::promises(),
        );
    }

    /**
     * The pricing FAQ: the buyers' questions, with why the software is free
     * after "Is Desiderio really free?" and who invoices after "How do we
     * pay?" (the model page's answer, so it is written once).
     *
     * @return list<array{question: string, answer: string}>
     */
    private static function pricingFaq(): array
    {
        $invoice = array_column(ShowcasePricing::modelFaq(), 'answer', 'question')['Who sends the invoice?'];
        $questions = [];
        foreach (ShowcasePricing::faq() as $item) {
            $questions[] = $item;
            if ($item['question'] === 'Is Desiderio really free?') {
                $questions[] = [
                    'question' => 'Why is Desiderio free?',
                    'answer' => sprintf('One public codebase for every customer is cheaper for us and safer for you. %s <a href="%s">How we earn</a> explains the model.', ShowcasePricing::modelSentence(), self::MODEL_PAGE_LINK),
                ];
            }
            if ($item['question'] === 'How do we pay?') {
                $questions[] = ['question' => 'Who sends the invoice?', 'answer' => $invoice];
            }
        }

        return $questions;
    }

    /**
     * FAQ items with the inline answers wrapped in a paragraph.
     *
     * @param list<array{question: string, answer: string}> $items
     * @return list<array{question: string, answer: string}>
     */
    private static function questions(array $items): array
    {
        return array_map(
            static fn(array $item): array => ['question' => $item['question'], 'answer' => '<p>' . $item['answer'] . '</p>'],
            $items,
        );
    }

    /**
     * Where a plan is ordered and a demo booked, until the shop has a home of
     * its own: the demo request form, which reaches the maintainers.
     *
     * @return ShowcasePage
     */
    private static function orderPage(): array
    {
        return [
            'title' => 'Order a plan or book a demo',
            'navTitle' => 'Order',
            'slug' => '/pricing/order',
            'abstract' => 'Tell us the plan and the number of live sites, or ask for a 20-minute demo. A maintainer answers within one business day.',
            'description' => 'Order Studio, Agency or Partner, or book a 20-minute demo with a maintainer. We answer within one business day.',
            'parentSlug' => 'pricing',
            'hideInNav' => true,
            'content' => [
                ShowcaseBlocks::block('desiderio_howtosteps', [
                    'header' => 'How ordering works',
                    'description' => 'No shop account, no card needed for yearly plans.',
                    'items' => [
                        ['title' => 'Tell us the plan', 'content' => 'Name the plan, yearly or monthly, the number of live sites and your VAT ID.'],
                        ['title' => 'Get the invoice', 'content' => sprintf(
                            'We send the invoice within one business day. The invoice comes from %s (<a href="%s">legal notice</a>, <a href="%s">terms</a>). Yearly plans are paid in advance.',
                            ShowcasePricing::SELLER_NAME,
                            ShowcasePricing::SELLER_IMPRINT_URL,
                            ShowcasePricing::SELLER_TERMS_URL,
                        )],
                        ['title' => 'Start working', 'content' => 'Your named developers get their access and their contact at the maintainers.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_demorequest', [
                    'eyebrow' => 'Order or demo',
                    'header' => 'Send us a message',
                    'description' => '<p>Write the plan and the number of live sites, or ask for a <strong>20-minute demo</strong>. A maintainer answers within one business day.</p>',
                    'reassurance' => 'No commitment until you confirm the invoice.',
                ]),
            ],
        ];
    }
}
