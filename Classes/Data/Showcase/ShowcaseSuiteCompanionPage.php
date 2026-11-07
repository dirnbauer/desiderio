<?php

declare(strict_types=1);

namespace Webconsulting\Desiderio\Data\Showcase;

/**
 * An independent add-on for purchasers of the official TYPO3 Suite.
 * Code stays public; the paid offer is the existing annual Studio plan.
 * Only the scheduled block advertises the one-off onboarding campaign.
 *
 * @phpstan-import-type ShowcaseBlock from ShowcaseBlocks
 * @phpstan-import-type ShowcasePage from ShowcaseBlocks
 */
final class ShowcaseSuiteCompanionPage
{
    public const string SLUG = '/target-groups/for-typo3-suite-buyers';
    public const string LINK = '{{page:target-groups/for-typo3-suite-buyers}}';
    public const string OFFICIAL_SUITE_URL = 'https://suite.typo3.com/de';

    /** @return ShowcasePage */
    public static function page(): array
    {
        $plan = ShowcasePricing::suiteCompanion();

        return [
            'title' => 'Suite Companion by webconsulting',
            'navTitle' => 'For TYPO3 Suite buyers',
            'slug' => self::SLUG,
            'seoTitle' => 'Suite Companion: independent webconsulting support',
            'abstract' => 'An independent add-on for TYPO3 Suite buyers: free webconsulting software and optional Studio support. The official Suite is purchased separately.',
            'description' => sprintf('For TYPO3 Suite buyers: free webconsulting tools and optional Studio support at %s a year plus VAT. Not an official TYPO3 product.', $plan['price']),
            'parentSlug' => 'target-groups',
            'content' => [
                ShowcaseBlocks::block('desiderio_headersection', [
                    'eyebrow' => 'For Suite buyers',
                    'header' => 'Your Suite. Our tools. Personal support.',
                    'subheadline' => 'Build your pages with free webconsulting tools. Suite Companion adds Studio support for your team, alongside the official TYPO3 Suite.',
                    'variant' => 'center',
                ]),
                self::independenceNotice(),
                ShowcaseBlocks::block('desiderio_pricingtwotier', [
                    'header' => 'Choose the help you need',
                    'subheadline' => 'The software is free for everyone. The paid package adds answers from the people who maintain it.',
                    'plans' => [
                        [
                            'title' => 'Use our software',
                            'price' => ShowcasePricing::plans()[0]['price'],
                            'description' => 'Free, with or without the official TYPO3 Suite.',
                            'features' => array_map(static fn(string $text): array => ['text' => $text], [
                                'Desiderio: 244 elements and 15 theme presets',
                                'Astryx and our public extensions',
                                'Every release and fix, with commercial use included',
                                'No licence key or paid access gate',
                                'Community help on GitHub',
                            ]),
                            'featured' => false,
                            'button_text' => 'Install for free',
                            'button_link' => ShowcaseBlocks::REPO_URL,
                        ],
                        [
                            'title' => 'Suite Companion',
                            'price' => $plan['price'] . ' a year',
                            'description' => 'Plus VAT. The annual Studio support plan from webconsulting.',
                            'features' => array_map(static fn(string $text): array => ['text' => $text], $plan['features']),
                            'featured' => true,
                            'button_text' => $plan['button']['text'],
                            'button_link' => $plan['button']['link'],
                        ],
                    ],
                ]),
                self::promotion(),
                ShowcaseBlocks::block('desiderio_textmedia', [
                    'header' => 'Build pages. Edit them where you see them.',
                    'shadcn_layout' => 'media-right',
                    'subheadline' => 'A design system and editing tools for your team.',
                    'content' => '<p>Desiderio brings 244 content elements and 15 theme presets. Editors assemble pages and use our Visual Editor tools to change fields and manage entries on the page.</p><p>Use the public releases yourself, or bring your questions to us with Studio. We help you choose the right elements and understand the next steps for your project.</p>',
                    'media_rounded' => 1,
                    'button_text' => 'Explore the editor',
                    'button_link' => '{{page:features/visual-editor}}',
                    'media' => ShowcaseBlocks::screenshot('feature-visual-editor-entries-936063d6.webp', 'Entries in the Visual Editor', 'The Visual Editor entry list with compact controls for editing and adding accordion items.'),
                ]),
                ShowcaseBlocks::block('desiderio_howtosteps', [
                    'header' => 'From enquiry to your first project',
                    'description' => 'Your official TYPO3 partner remains your contact for the Suite. We look after our own tools.',
                    'items' => [
                        ['title' => 'Tell us about your project', 'content' => 'Request Suite Companion and name your TYPO3 partner, your live sites and the developer who needs support.'],
                        ['title' => 'Confirm the scope', 'content' => 'We confirm the annual Studio plan and the extension combination for your project. Custom development and migrations receive a separate quote.'],
                        ['title' => 'Start with a direct contact', 'content' => 'Your named developer gets the support contact. The software is already public; there is no licence key to unlock.'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_faq', [
                    'header' => 'Before you choose',
                    'items' => [
                        ['question' => 'Is this an official TYPO3 product?', 'answer' => '<p>' . ShowcasePricing::INDEPENDENCE_NOTICE . '</p><p>The official TYPO3 Suite is a separate product. <a href="' . self::OFFICIAL_SUITE_URL . '">Read the official Suite offer</a>.</p>'],
                        ['question' => 'Is your software free without the Suite?', 'answer' => '<p>No purchase is required. Our public software is free for everyone, including commercial projects. You need neither a Suite purchase nor a paid support plan to download it.</p>'],
                        ['question' => 'What does the annual fee cover?', 'answer' => '<p>Studio support for our tools on up to five live sites, one named developer and email answers within two business days. Staging and local installations are free.</p><p>The maintenance promise covers TYPO3 v14 patches within ten business days and confirmed security issues within five. <a href="{{page:pricing}}">See the full plan terms</a>.</p>'],
                        ['question' => 'Does this include the official Suite licence?', 'answer' => '<p>No. Buy the official TYPO3 Suite separately through an official TYPO3 partner. Its licence, manufacturer responsibility and support are governed by that separate agreement.</p>'],
                        ['question' => 'What needs a separate agreement?', 'answer' => '<p>Hosting, implementation, migration and custom development are outside Studio. Official Suite support stays with your TYPO3 partner. Combining extensions requires a project review; we do not claim official certification.</p>'],
                        ['question' => 'What happens if I stop paying?', 'answer' => '<p>Your sites keep working and you retain the public software under its open-source licence. The paid support ends according to your agreement. You can still use community support.</p>'],
                    ],
                ]),
                ShowcaseBlocks::block('desiderio_ctabanner', [
                    'header' => 'Give your team a direct contact',
                    'description' => sprintf('Suite Companion by webconsulting: annual Studio support for %s plus VAT. Tell us about your project before you commit.', $plan['price']),
                    'cta_text' => $plan['button']['text'],
                    'cta_link' => $plan['button']['link'],
                    'bg_style' => 'primary',
                ]),
            ],
        ];
    }

    /** @return ShowcaseBlock */
    public static function independenceNotice(): array
    {
        return ShowcaseBlocks::block('desiderio_contenthighlight', [
            'header' => 'Independent webconsulting packages',
            'content' => '<p>' . ShowcasePricing::INDEPENDENCE_NOTICE . '</p><p>The official TYPO3 Suite is purchased separately through an official TYPO3 partner.</p>',
            'variant' => 'bordered',
            'alignment' => 'center',
            'link' => self::OFFICIAL_SUITE_URL,
            'link_text' => 'Official Suite offer',
        ]);
    }

    /**
     * One scheduled record on each page. Native endtime also limits the
     * frontend cache lifetime, and translations inherit the same deadline.
     *
     * @return ShowcaseBlock
     */
    public static function promotion(): array
    {
        return ShowcaseBlocks::block('desiderio_contenthighlight', [
            'header' => 'Your first hour, included',
            'content' => '<p><strong>For official TYPO3 Suite buyers: one 60-minute remote onboarding session with annual Studio, at no extra charge.</strong></p>'
                . '<p>Send your request by <strong>31 December 2026, 23:59 Vienna time</strong>. Existing annual Studio customers qualify too, without paying for Studio twice.</p>'
                . '<p>One session per company, held by 31 March 2027. We review one project and agree the next steps for our tools. Implementation and migration are quoted separately.</p>'
                . '<p>Mention your TYPO3 partner. We may ask for a redacted proof of purchase. Your annual plan and Suite purchase must be verified before the session.</p>',
            'variant' => 'primary',
            'alignment' => 'center',
            'link' => ShowcasePricing::ORDER_LINK,
            'link_text' => 'Request an introduction',
            'endtime' => ShowcasePricing::suitePromotionEnd(),
        ]);
    }
}
