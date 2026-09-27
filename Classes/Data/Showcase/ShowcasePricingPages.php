<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * The pricing page and the order page below it. Every number comes from
 * ShowcasePricing.
 *
 * The page follows the buyer's questions in order: what it costs, what the
 * paid plans add, how the plans differ, what we build for you, why now, what
 * if it goes wrong, and the remaining questions.
 *
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcasePricingPages
{
    /**
     * @return array<int, ShowcasePage>
     */
    public static function pages(): array
    {
        return [self::pricingPage(), self::orderPage()];
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
            'navTitle' => 'Pricing',
            'slug' => '/pricing',
            'seoTitle' => 'Desiderio pricing: plans and services for TYPO3 agencies',
            'abstract' => 'Desiderio is free under GPL-2.0. Studio, Agency and Partner add early access, the maintenance promise and answers from the maintainers; services have fixed prices.',
            'description' => 'Desiderio is free. Studio €590, Agency €1,990 and Partner €4,900 a year add early access and support; services have fixed prices.',
            'parentSlug' => null,
            'content' => [
                ShowcaseBlocks::salesHero(
                    'Free to use',
                    'Free to use. Pay for certainty.',
                    'The code is open source and stays that way. Paid plans add early access, the maintenance promise and answers from the people who wrote it.',
                    ['text' => 'Choose a plan', 'link' => ShowcasePricing::ORDER_LINK],
                    ['text' => 'Download the demo', 'link' => '{{page:downloads}}'],
                    ShowcaseHeroPhotos::for('pricing'),
                ),
                ShowcaseBlocks::block('desiderio_pricingfourtier', [
                    'eyebrow' => 'Plans',
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
                    'header' => 'What every paid plan promises',
                    'subheadline' => 'In the terms, not only on this page.',
                    'badge_items' => array_map(
                        static fn(array $promise): array => [
                            'label' => $promise['label'],
                            'icon_name' => $promise['icon'],
                            'description_text' => $promise['description'],
                        ],
                        ShowcasePricing::promises(),
                    ),
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
                ShowcaseBlocks::block('desiderio_pricingusage', [
                    'eyebrow' => 'Services',
                    'header' => 'Fixed prices for the work around it',
                    'subheadline' => 'Written scope, fixed price, and delivered under your agency\'s name if you like.',
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
                    'question_items' => array_map(
                        static fn(array $item): array => ['question' => $item['question'], 'answer' => '<p>' . $item['answer'] . '</p>'],
                        ShowcasePricing::faq(),
                    ),
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
                        ['title' => 'Get the invoice', 'content' => 'We send the invoice within one business day. Yearly plans are paid in advance.'],
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
