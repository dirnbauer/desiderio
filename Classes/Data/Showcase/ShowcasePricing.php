<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Every price the showcase states, in one place: the plans, the services,
 * the Launch Pack and the founding offer. The pricing page, the homepage
 * and the target-group pages all read from here, so a price changes once.
 * The content canon (facts.desiderio.pricing and .services) mirrors it.
 *
 * Desiderio is free with every release; the paid plans are support plans
 * (sales model decided 2026-09-27): no plan buys code or earlier releases.
 * Plans are priced by live sites; staging and local installations are free.
 * Every service is a fixed price that earns at least €125 an hour.
 *
 * @phpstan-type PricingPlan array{key: string, name: string, price: string, period: string, for: string, features: list<string>, button: array{text: string, link: string}, featured: bool}
 * @phpstan-type PricingService array{title: string, rate: string, unit: string, description: string}
 */
final class ShowcasePricing
{
    /** Where a plan is ordered and a demo booked. */
    public const string ORDER_LINK = '{{page:pricing/order}}';

    /**
     * @return list<PricingPlan>
     */
    public static function plans(): array
    {
        return [
            [
                'key' => 'community',
                'name' => 'Community',
                'price' => '€0',
                'period' => 'forever, GPL-2.0',
                'for' => 'For anyone trying Desiderio, and teams that support themselves.',
                'features' => [
                    'All 244 content elements, 62 components and 15 theme presets',
                    'Every extension of the suite, every release the day it ships',
                    'Unlimited sites, commercial use included',
                    'Every bug and security fix, for everyone',
                    'Help from the community on GitHub',
                ],
                'button' => ['text' => 'Install for free', 'link' => ShowcaseBlocks::REPO_URL],
                'featured' => false,
            ],
            [
                'key' => 'studio',
                'name' => 'Studio',
                'price' => '€590',
                'period' => 'per year, or €59 a month',
                'for' => 'For freelancers and small studios.',
                'features' => [
                    'Up to 5 live sites; staging and local installs are free',
                    'The maintenance promise',
                    'Email answers within 2 business days',
                    '1 named developer',
                ],
                'button' => ['text' => 'Choose Studio', 'link' => self::ORDER_LINK],
                'featured' => false,
            ],
            [
                'key' => 'agency',
                'name' => 'Agency',
                'price' => '€1,990',
                'period' => 'per year, or €199 a month',
                'for' => 'For TYPO3 agencies with a development team.',
                'features' => [
                    'Up to 25 live sites, 10 more for €490 a year',
                    'Everything in Studio',
                    'An answer by the next business day (CET)',
                    '3 named developers',
                    'A 90-minute onboarding call for your team every year',
                    'Your bug reports go first in the queue',
                ],
                'button' => ['text' => 'Choose Agency', 'link' => self::ORDER_LINK],
                'featured' => true,
            ],
            [
                'key' => 'partner',
                'name' => 'Partner',
                'price' => '€4,900',
                'period' => 'per year, five places',
                'for' => 'For agencies that build most client sites on Desiderio.',
                'features' => [
                    'Unlimited live sites',
                    'Everything in Agency, with 6 named developers',
                    'Critical bugs fixed or worked around within 2 business days',
                    'A half-day workshop for your team every year',
                    'A roadmap call every quarter',
                    'Enquiries from this site passed on when they fit',
                ],
                'button' => ['text' => 'Apply as partner', 'link' => self::ORDER_LINK],
                'featured' => false,
            ],
        ];
    }

    /**
     * Rows of the plan comparison: a feature and its value per plan, in the
     * order of plans().
     *
     * @return list<array{feature: string, values: array{0: string, 1: string, 2: string, 3: string}}>
     */
    public static function comparison(): array
    {
        return [
            ['feature' => 'Price', 'values' => ['€0', '€590 a year', '€1,990 a year', '€4,900 a year']],
            ['feature' => 'Live sites', 'values' => ['Unlimited', 'Up to 5', 'Up to 25', 'Unlimited']],
            ['feature' => 'Staging and local installs', 'values' => ['Free', 'Free', 'Free', 'Free']],
            ['feature' => 'Every release, free for everyone', 'values' => ['Yes', 'Yes', 'Yes', 'Yes']],
            ['feature' => 'The maintenance promise', 'values' => ['–', 'Yes', 'Yes', 'Yes']],
            ['feature' => 'Answers from the maintainers', 'values' => ['GitHub issues', 'Within 2 business days', 'Next business day', 'Next business day']],
            ['feature' => 'Named developers', 'values' => ['–', '1', '3', '6']],
            ['feature' => 'Onboarding', 'values' => ['–', '–', '90-minute call a year', 'Half-day workshop a year']],
            ['feature' => 'Bug reports', 'values' => ['Public queue', 'Public queue', 'First in the queue', 'Critical fixes in 2 business days']],
            ['feature' => 'Roadmap call', 'values' => ['–', '–', '–', 'Every quarter']],
            ['feature' => 'Enquiries passed on', 'values' => ['–', '–', '–', 'Yes']],
        ];
    }

    /**
     * The promises behind every paid plan.
     *
     * @return list<array{icon: string, label: string, description: string}>
     */
    public static function promises(): array
    {
        return [
            ['icon' => 'shield-check', 'label' => 'The maintenance promise', 'description' => 'Every TYPO3 v14 patch release supported within 10 business days, confirmed security issues fixed within 5. Miss one and your plan runs a month longer.'],
            ['icon' => 'check-circle', 'label' => '30 days money back', 'description' => 'On the first yearly payment, no questions asked.'],
            ['icon' => 'git-branch', 'label' => 'Your code, your sites', 'description' => 'GPL-2.0: you keep every version, and your sites keep working if you stop paying.'],
            ['icon' => 'handshake', 'label' => 'Your clients stay yours', 'description' => 'We never contact your clients. Services can run under your agency\'s name.'],
        ];
    }

    /**
     * Fixed-price services.
     *
     * @return list<PricingService>
     */
    public static function services(): array
    {
        return [
            ['title' => 'Kickstart workshop', 'rate' => '€1,490', 'unit' => 'fixed price', 'description' => '3 hours remote for up to 8 developers, a review of your first project and 30 days of priority questions.'],
            ['title' => 'Brand Theme', 'rate' => '€2,490', 'unit' => 'fixed price', 'description' => 'A client\'s brand as a Desiderio preset, checked for WCAG 2.2 AA contrast, ready 10 business days after the brand assets arrive. Two rounds of changes.'],
            ['title' => 'Custom element S', 'rate' => '€890', 'unit' => 'fixed price', 'description' => 'Up to 8 fields and a fixed layout, with a backend preview and English and German labels.'],
            ['title' => 'Custom element M', 'rate' => '€1,690', 'unit' => 'fixed price', 'description' => 'Repeating items, variants or interaction such as tabs, sliders or filters. Larger elements get a quote after a written spec.'],
            ['title' => 'Upgrade audit', 'rate' => '€690', 'unit' => 'fixed price', 'description' => 'A client site on TYPO3 v10 to v13: extension inventory, risks and a fixed quote for v14. Credited in full if you book the upgrade within 60 days.'],
            ['title' => 'Editor training', 'rate' => '€790', 'unit' => 'fixed price', 'description' => '3 hours remote for up to 12 editors, under your agency\'s name if you like, with a recording and a handout.'],
            ['title' => 'Support hours', 'rate' => '€650', 'unit' => 'for 5 hours', 'description' => 'Code reviews and project questions for plan holders, valid for 12 months.'],
        ];
    }

    /**
     * The Launch Pack: the Agency plan with the two services every first
     * project needs, and the editor training included.
     *
     * @return array{header: string, eyebrow: string, price: string, savings: string, description: string, items: list<array{name: string, price: string, description: string}>}
     */
    public static function launchPack(): array
    {
        return [
            'header' => 'Launch Pack',
            'eyebrow' => 'First project',
            'price' => '€5,970',
            'savings' => 'Editor training included (€790)',
            'description' => 'The Agency plan for a year, the Kickstart workshop and one Brand Theme. The editor training for your first client is included.',
            'items' => [
                ['name' => 'Agency plan, one year', 'price' => '€1,990', 'description' => 'Support for up to 25 live sites, with answers by the next business day.'],
                ['name' => 'Kickstart workshop', 'price' => '€1,490', 'description' => 'Your developers learn the system in one session.'],
                ['name' => 'Brand Theme', 'price' => '€2,490', 'description' => 'Your first client\'s brand as a preset.'],
                ['name' => 'Editor training', 'price' => '€790', 'description' => 'Included: 3 hours for your client\'s editors.'],
            ],
        ];
    }

    /**
     * The founding offer: real limits, no discount code.
     *
     * @return array{header: string, content: string}
     */
    public static function foundingOffer(): array
    {
        return [
            'header' => 'Founding partners: 20 places',
            'content' => '<p><strong>The first 20 agencies that choose a paid plan keep their price for as long as the plan runs.</strong> They also get a seat on the product council: one call a month and a vote on what we build next. A joint case study comes with it if they want one.</p><p>The offer ends when all 20 places are taken, or on 31 March 2027.</p>',
        ];
    }

    /**
     * Questions buyers ask before they choose a plan.
     *
     * @return list<array{question: string, answer: string}>
     */
    public static function faq(): array
    {
        return [
            ['question' => 'Is Desiderio really free?', 'answer' => 'Yes, with every release. It is GPL-2.0: unlimited sites, commercial use included, and the full source is on GitHub.'],
            ['question' => 'What do paid plans add?', 'answer' => 'Desiderio and every release are free for everyone. The support plans add guaranteed answer times, named developers and the maintenance promise; Agency and Partner add onboarding, priority and enquiries we pass on.'],
            ['question' => 'What counts as a live site?', 'answer' => 'A TYPO3 site with its own public domain in production. Development, staging and local installations are free.'],
            ['question' => 'Can we move a site to another client?', 'answer' => 'Yes. Swap sites whenever you like; only the number of live sites counts.'],
            ['question' => 'Can we charge our clients for it?', 'answer' => 'Yes. Many agencies add it to their maintenance contracts. We never contact your clients.'],
            ['question' => 'What happens if we cancel?', 'answer' => 'Nothing changes on your sites, and every release stays free. The guaranteed answers and the maintenance promise end.'],
            ['question' => 'Which TYPO3 versions do you support?', 'answer' => 'TYPO3 v14 LTS, which gets security fixes until 30 June 2029. Support for the next LTS comes as a free release, like every other.'],
            ['question' => 'Do the AI features cost extra?', 'answer' => 'Not from us. You add your own provider key and pay the provider directly.'],
            ['question' => 'How do we pay?', 'answer' => 'Yearly by invoice, or monthly. Prices are net; businesses in the EU get a reverse-charge invoice.'],
            ['question' => 'Can we get our money back?', 'answer' => 'Yes, within 30 days of the first yearly payment, no questions asked.'],
        ];
    }
}
