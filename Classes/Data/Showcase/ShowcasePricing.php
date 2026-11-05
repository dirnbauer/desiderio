<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * Every price the showcase states, in one place: the plans, the services,
 * Care, the Supporter, sponsored features, the Launch Pack and the founding
 * offer. The pricing page, the model page, the homepage and the target-group
 * pages all read from here, so a price changes once. The content canon
 * (facts.desiderio.pricing, .services, .model and .seller) mirrors it.
 *
 * The model (decided 2026-11-05): free software, paid care. Desiderio is
 * free with every release (GPL-2.0); no plan buys code or earlier releases.
 * Visitors relate to us in four ways (ways()): use it for free, support it,
 * ask for features (or sponsor one for a public release), or let us take
 * care (the support plans, priced by live sites, and Care, which runs the
 * site). Staging and local installations are free. Every fixed price earns
 * at least €130 an hour. webconsulting GmbH, Austria invoices (SELLER_NAME).
 *
 * @phpstan-type PricingPlan array{key: string, name: string, price: string, period: string, for: string, features: list<string>, button: array{text: string, link: string}, featured: bool}
 * @phpstan-type PricingService array{title: string, rate: string, unit: string, description: string}
 * @phpstan-type PricingButton array{text: string, link: string}
 * @phpstan-type PricingCare array{name: string, eyebrow: string, header: string, price: string, period: string, yearly: string, further_sites: string, description: string, features: list<string>, button: PricingButton}
 * @phpstan-type PricingOffer array{name: string, price: string, period: string, description: string, button: PricingButton}
 * @phpstan-type PricingWay array{icon: string, title: string, description: string, link: string}
 * @phpstan-type PricingQuestion array{question: string, answer: string}
 */
final class ShowcasePricing
{
    /** Where a plan is ordered and a demo booked. */
    public const string ORDER_LINK = '{{page:pricing/order}}';

    /** The party that invoices plans, services, Care, Supporters and sponsored features. */
    public const string SELLER_NAME = 'webconsulting GmbH, Austria';

    /** The seller's legal notice. */
    public const string SELLER_IMPRINT_URL = 'https://www.webconsulting.at/impressum';

    /** The seller's terms and conditions. */
    public const string SELLER_TERMS_URL = 'https://www.webconsulting.at/agb';

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
                    'Astryx and all our other extensions, every release the day it ships',
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
                'period' => 'per year, five partner places',
                'for' => 'For agencies that build most client sites on Desiderio.',
                'features' => [
                    'Unlimited live sites',
                    'Everything in Agency, with 6 named developers',
                    'Critical bugs fixed or worked around within 2 business days',
                    'A half-day workshop for your team every year',
                    'A roadmap call every quarter',
                    'We pass on enquiries from this site that fit your agency',
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
            ['icon' => 'handshake', 'label' => 'Your clients stay yours', 'description' => 'We never contact your clients. Services can run under your agency’s name.'],
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
            ['title' => 'Kickstart workshop', 'rate' => '€1,490', 'unit' => '', 'description' => '3 hours remote for up to 8 developers, a review of your first project and 30 days of priority questions.'],
            ['title' => 'Brand Theme', 'rate' => '€2,490', 'unit' => '', 'description' => 'A client’s brand as a Desiderio preset, checked for WCAG 2.2 AA contrast, ready 10 business days after the brand assets arrive. Two rounds of changes.'],
            ['title' => 'Custom element S', 'rate' => '€890', 'unit' => '', 'description' => 'Up to 8 fields and a fixed layout, with a backend preview and English and German labels.'],
            ['title' => 'Custom element M', 'rate' => '€1,690', 'unit' => '', 'description' => 'Repeating items, variants or interaction such as tabs, sliders or filters. Larger elements get a quote after a written spec.'],
            ['title' => 'Upgrade audit', 'rate' => '€690', 'unit' => '', 'description' => 'A client site on TYPO3 v10 to v13: extension inventory, risks and a fixed quote for v14. Credited in full if you book the upgrade within 60 days.'],
            ['title' => 'Editor training', 'rate' => '€790', 'unit' => '', 'description' => '3 hours remote for up to 12 editors, under your agency’s name if you like, with a recording and a handout.'],
            ['title' => 'Support hours', 'rate' => '€690', 'unit' => '5 hours', 'description' => 'Code reviews and project questions for plan holders, valid for 12 months.'],
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
            'savings' => 'Includes €790 training',
            'description' => 'The Agency plan for a year, the Kickstart workshop and one Brand Theme. The editor training for your first client is included.',
            'items' => [
                ['name' => 'Agency plan, one year', 'price' => '€1,990', 'description' => 'Support for up to 25 live sites, with answers by the next business day.'],
                ['name' => 'Kickstart workshop', 'price' => '€1,490', 'description' => 'Your developers learn the system in one session.'],
                ['name' => 'Brand Theme', 'price' => '€2,490', 'description' => 'Your first client’s brand as a preset.'],
                ['name' => 'Editor training', 'price' => '€790', 'description' => 'Included: 3 hours for your client’s editors.'],
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
            'header' => 'Founding offer: 20 places',
            'content' => '<p><strong>The first 20 agencies that choose a paid plan keep their price for as long as the plan runs.</strong> They also get a seat on the product council: one call a month and a vote on what we build next. A joint case study comes with it if they want one.</p><p>The offer ends when all 20 places are taken, or on 31 March 2027.</p>',
        ];
    }

    /**
     * Questions buyers ask before they choose a plan.
     *
     * @return list<PricingQuestion>
     */
    public static function faq(): array
    {
        return [
            ['question' => 'Is Desiderio really free?', 'answer' => 'Yes, with every release. It is GPL-2.0: unlimited sites, commercial use included, and the full source is on GitHub.'],
            ['question' => 'What do paid plans add?', 'answer' => 'Desiderio and every release are free for everyone. The support plans add guaranteed answer times, named developers and the maintenance promise. Agency and Partner add onboarding and priority; Partner also gets enquiries we pass on.'],
            ['question' => 'What counts as a live site?', 'answer' => 'A TYPO3 site with its own public domain in production. Development, staging and local installations are free.'],
            ['question' => 'Can we move a site to another client?', 'answer' => 'Yes. Swap sites whenever you like; only the number of live sites counts.'],
            ['question' => 'Can we charge our clients for it?', 'answer' => 'Yes. You can include it in your maintenance contracts. We never contact your clients.'],
            ['question' => 'What happens if we cancel?', 'answer' => 'Nothing changes on your sites, and every release stays free. The guaranteed answer times and the maintenance promise end.'],
            ['question' => 'Which TYPO3 versions do you support?', 'answer' => 'TYPO3 v14 LTS, which gets security fixes until 30 June 2029. Support for the next LTS comes as a free release, like every other.'],
            ['question' => 'Do the AI features cost extra?', 'answer' => 'Not from us. You add your own provider key and pay the provider directly.'],
            ['question' => 'How do we pay?', 'answer' => 'Yearly by invoice, or monthly. Prices are net; businesses in the EU get a reverse-charge invoice.'],
            ['question' => 'Can we get our money back?', 'answer' => 'Yes, within 30 days of the first yearly payment, no questions asked.'],
        ];
    }

    /**
     * Care: we run the site. Not a fifth plan column: the plans sell answers
     * and the maintenance promise, Care sells running a live site (hosting on
     * the customer's own Coolify instance, T3 Ops updates, monitoring and
     * backups). Priced per live site like the plans.
     *
     * @return PricingCare
     */
    public static function care(): array
    {
        return [
            'name' => 'Care',
            'eyebrow' => 'We take care',
            'header' => 'Care: we run your site',
            'price' => '€390',
            'period' => 'a month per live site',
            'yearly' => '€3,900 a year, two months free',
            'further_sites' => 'Every further live site of the same customer: €290 a month',
            'description' => 'For teams that want one partner for hosting, updates and support: your site runs on its own Coolify instance in the EU. T3 Ops applies TYPO3 and extension updates within the maintenance promise and checks security four times a day.',
            'features' => [
                'Hosting on your own Coolify instance in the EU',
                'Updates and security fixes applied, with screenshots as evidence',
                'Monitoring and daily backups',
                'Answers by the next business day (CET)',
                'Monthly billing, cancel to the end of a year',
                'The maintenance promise',
            ],
            'button' => ['text' => 'Ask for Care', 'link' => self::ORDER_LINK],
        ];
    }

    /**
     * The Supporter: recognition only, no support included.
     *
     * @return PricingOffer
     */
    public static function supporter(): array
    {
        return [
            'name' => 'Supporter',
            'price' => '€190',
            'period' => 'a year',
            'description' => 'No support included, on purpose. Your name or logo on our How we earn page and a thank-you in the release notes. For companies that earn with Desiderio and want to say so.',
            'button' => ['text' => 'Become a Supporter', 'link' => self::ORDER_LINK],
        ];
    }

    /**
     * A sponsored feature: built by a date, released free for everyone, the
     * sponsor named. A custom element for private use is a service (€890).
     *
     * @return PricingOffer
     */
    public static function sponsoredFeature(): array
    {
        return [
            'name' => 'Sponsored feature',
            'price' => 'from €1,290',
            'period' => 'per feature',
            'description' => 'You name the feature and the date. We build it and release it free for everyone, with tests, documentation and translations, and your name in the release notes. A custom element for your own use stays €890.',
            'button' => ['text' => 'Sponsor a feature', 'link' => self::ORDER_LINK],
        ];
    }

    /**
     * The four ways a visitor can relate to us, as cards: use it, support it,
     * ask for features, let us take care. Prices come from the methods above.
     *
     * @return list<PricingWay>
     */
    public static function ways(): array
    {
        $supporter = self::supporter();
        $care = self::care();

        return [
            [
                'icon' => 'download',
                'title' => 'Use it',
                'description' => 'Install it, build client sites, sell your work. Every release and every fix is free under GPL-2.0. No registration, no licence key.',
                'link' => ShowcaseBlocks::REPO_URL,
            ],
            [
                'icon' => 'heart',
                'title' => 'Support it',
                'description' => sprintf('Star the repository, report what breaks, write about it. Or become a Supporter for %s %s and be thanked by name.', $supporter['price'], $supporter['period']),
                'link' => '{{page:pricing/how-we-earn}}',
            ],
            [
                'icon' => 'lightbulb',
                'title' => 'Ask for features',
                'description' => sprintf('Open an issue; good ideas go on the public roadmap. Sponsor one %s to have it by a date, released for everyone.', self::sponsoredFeature()['price']),
                'link' => '{{page:pricing}}',
            ],
            [
                'icon' => 'shield-check',
                'title' => 'Let us take care',
                'description' => sprintf('Plans from %s a year add answers and the maintenance promise. Care runs your site: hosting, updates, monitoring, %s %s.', self::plans()[1]['price'], $care['price'], $care['period']),
                'link' => '{{page:pricing}}',
            ],
        ];
    }

    /**
     * What never costs money: the left column of the free-or-paid comparison.
     *
     * @return list<string>
     */
    public static function alwaysFree(): array
    {
        return [
            'Every release, the day it ships',
            'Every bug and security fix',
            'The demo site and its download',
            'Documentation and GitHub issues',
            'Commercial use on unlimited sites',
        ];
    }

    /**
     * What costs money, all of it our time: the right column of the
     * free-or-paid comparison.
     *
     * @return list<string>
     */
    public static function costsMoney(): array
    {
        return [
            'Guaranteed answer times and named developers (plans)',
            'The maintenance promise in the terms (plans)',
            'Workshops, Brand Themes and custom elements (fixed prices)',
            'A feature by a date, released for everyone (sponsored feature)',
            'Running your site: hosting, updates, monitoring (Care)',
        ];
    }

    /**
     * Honest questions about the model, for the model page. Answers are
     * inline HTML (the FAQ answer is a rich-text field): wrap them in <p>.
     *
     * @return list<PricingQuestion>
     */
    public static function modelFaq(): array
    {
        $supporter = self::supporter();

        return [
            ['question' => 'What is the catch?', 'answer' => 'There is none we know of. The software is free because that is cheaper and safer for everyone, and we earn when you want our time.'],
            ['question' => 'Do I have to pay if I earn money with Desiderio?', 'answer' => 'No. GPL-2.0 allows commercial use on unlimited sites. Pay when you want answers, work or Care.'],
            ['question' => 'What if you stop maintaining it?', 'answer' => 'You keep every version and your sites keep working. Plan holders have the maintenance promise in their terms, and a missed patch extends the plan by a month.'],
            ['question' => 'Why no paid early access?', 'answer' => 'A release that waits for payment is a security fix that waits. Everyone gets every release on the same day.'],
            ['question' => 'Can I pay without needing support?', 'answer' => sprintf('Yes. Become a Supporter for %s %s, or sponsor a feature %s.', $supporter['price'], $supporter['period'], self::sponsoredFeature()['price'])],
            ['question' => 'Who sends the invoice?', 'answer' => sprintf('%s sends every invoice. Its <a href="%s">legal notice</a> and <a href="%s">terms</a> are on webconsulting.at.', self::SELLER_NAME, self::SELLER_IMPRINT_URL, self::SELLER_TERMS_URL)],
        ];
    }

    /**
     * The one calm sentence every page uses about the model.
     */
    public static function modelSentence(): string
    {
        return 'Desiderio is free with every release. Paid plans add our time.';
    }
}
